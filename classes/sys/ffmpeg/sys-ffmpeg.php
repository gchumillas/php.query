<?php
/**
 * This file contains the SysFfmpeg class.
 * 
 * PHP Version 5.3
 * 
 * @category System
 * @package  SysFfmpeg
 * @author   Gonzalo Chumillas <gonzalo@soloproyectos.com>
 * @license  https://raw.github.com/soloproyectos/core/master/LICENSE BSD 2-Clause License
 * @link     https://github.com/soloproyectos/core
 */
namespace com\soloproyectos\core\sys\ffmpeg;
use InvalidArgumentException;
use com\soloproyectos\core\sys\cmd\SysCmd;
use com\soloproyectos\core\sys\cmd\SysCmdArgument;
use com\soloproyectos\core\sys\file\SysFile;
use com\soloproyectos\core\sys\ffmpeg\SysFfmpegHelper;
use com\soloproyectos\core\sys\ffmpeg\exception\SysFfmpegException;
use com\soloproyectos\core\text\Text;

/**
 * Class SysFfmpeg.
 * 
 * This class represents the 'ffmpeg' command line.
 * 
 * @category System
 * @package  SysFfmpeg
 * @author   Gonzalo Chumillas <gonzalo@soloproyectos.com>
 * @license  https://raw.github.com/soloproyectos/core/master/LICENSE BSD 2-Clause License
 * @link     https://github.com/soloproyectos/core
 */
class SysFfmpeg extends SysCmd
{
    /**
     * Filename of the output video.
     * @var string
     */
    private $_output;
    
    /**
     * Filename of the input video.
     * @var string
     */
    private $_input;
    
    /**
     * Input width.
     * @var integer
     */
    private $_inputWidth = 0;
    
    /**
     * Input height.
     * @var integer
     */
    private $_inputHeight = 0;
    
    /**
     * Input duration in seconds.
     * @var float
     */
    private $_inputDuration = 0;
    
    /**
     * Input frames per second.
     * @var integer
     */
    private $_inputFps = 0;
    
    /**
     * Input size in bytes.
     * @var integer
     */
    private $_inputSize = 0;
    
    /**
     * Video scale argument.
     * @var SysCmdArgument
     */
    private $_scale;
    
    /**
     * Constructor.
     * 
     * @param string $input  Input video (default is "")
     * @param string $output Output video (default is "")
     */
    public function __construct($input = "", $output = "")
    {
        $this->_input = $input;
        $this->_output = $output;
        
        if (!Text::isEmpty($this->_input)) {
            list(
                $this->_inputWidth,
                $this->_->_inputHeight,
                $this->_inputDuration,
                $this->_inputFps,
                $this->_inputSize
            ) = SysFfmpegHelper::getVideoInfo($this->_input);
        }
        
        parent::__construct("ffmpeg");
    }
    
    /**
     * Gets the input video width.
     * 
     * @return integer
     */
    public function getWidth()
    {
        return $this->_inputWidth;
    }
    
    /**
     * Gets the input video height.
     * 
     * @return integer
     */
    public function getHeight()
    {
        return $this->_->_inputHeight;
    }
    
    /**
     * Gets the input video duration in seconds.
     * 
     * @return float
     */
    public function getDuration()
    {
        return $this->_inputDuration;
    }
    
    /**
     * Gets the input video frames per second.
     * 
     * @return integer
     */
    public function getFramesPerSecond()
    {
        return $this->_inputFps;
    }
    
    /**
     * Gets the input video size in bytes.
     * 
     * @return integer
     */
    public function getSize()
    {
        return $this->_inputSize;
    }
    
    /**
     * Gets the nearest even number of a given number.
     * 
     * <p> This function gets the greatest even number lower than a given number.
     * The ffmpeg command only accepts even sizes. This function 'fixes' odd
     * numbers, returning even numbers instead. For example:</p>
     * 
     * <code>// prints even numbers
     * echo $this->_getEvenNumber(2.5);  // prints 2
     * echo $this->_getEvenNumber(7);    // prints 6
     * echo $this->_getEvenNumber(4);    // prints 4, as it is already an even number
     * </code>
     * 
     * @param float $number A number
     * 
     * @return float
     */
    private function _getEvenNumber($number)
    {
        $ret = floor($number);
        $ret -= $number % 2;
        return $ret;
    }
    
    /**
     * Gets the inside scaled size.
     * 
     * This function scales the input video inside a box, preserving the aspect
     * ratio, and returns an array with the following info:
     * &lt;width&gt;, &gt;height&gt;, &lt;padding width&gt;, &lt;padding height&gt;,
     * &lt;padding offset x&gt;, &lt;padding offset y&gt;
     * 
     * @param integer $width  Rectangle width
     * @param integer $height Rectangle height
     * 
     * @return array (width, height,
     *               padding width, padding width,
     *               padding offset x, padding offset y)
     */
    private function _getInsideScale($width, $height)
    {
        // fixes size
        $width = $this->_getEvenNumber($width);
        $height = $this->_getEvenNumber($height);
        
        if (!($width > 0) && !($height > 0)) {
            throw new InvalidArgumentException(
                "Invalid size. " .
                "At least the width or the height must be greater than zero"
            );
        }
        
        if (!($this->_inputWidth > 0) || !($this->_->_inputHeight > 0)) {
            throw new SysFfmpegException(
                "Invalid input size: " .
                "{$this->_inputWidth} x {$this->_->_inputHeight}"
            );
        }
        
        // padding rectandle
        $pwidth = ($width > 0) ? $width : INF;
        $pheight = ($height > 0) ? $height : INF;
        
        // tangents
        $ptan = $pheight / $pwidth;
        $itan = $this->_->_inputHeight / $this->_inputWidth;
        
        // scaled rectangle
        $swidth = 0;
        $sheight = 0;
        if ($ptan < $itan) {
            $sheight = $pheight;
            $swidth = $this->_getEvenNumber($pheight / $itan);
        } else {
            $sheight = $this->_getEvenNumber($pwidth * $itan);
            $swidth = $pwidth;
        }
        
        // padding offset
        $px = round(($pwidth - $swidth) / 2);
        $py = round(($pheight - $sheight) / 2);
        
        return array($swidth, $sheight, $pwidth, $pheight, $px, $py);
    }
    
    /**
     * Gets the outside scaled size.
     * 
     * This function scales the input video outside a box, preserving the aspect
     * ratio, and returns an array with the following info:
     * &lt;width&gt;, &lt;height&gt;
     * 
     * @param integer $width  Rectangle width
     * @param integer $height Rectangle height
     * 
     * @return array (width, height)
     */
    private function _getOutsideScale($width, $height)
    {
        // fixes size
        $width = $this->_getEvenNumber($width);
        $height = $this->_getEvenNumber($height);
        
        if (!($width > 0) && !($height > 0)) {
            throw new InvalidArgumentException(
                "Invalid size. " .
                "At least the width or the height must be greater than zero"
            );
        }
        
        if (!($this->_inputWidth > 0) || !($this->_->_inputHeight > 0)) {
            throw new SysFfmpegException(
                "Invalid input size: " .
                "{$this->_inputWidth} x {$this->_->_inputHeight}"
            );
        }
        
        // master rectandle
        $w0 = $width;
        $h0 = $height;
        $t0 = ($width > 0) ? $height / $width : $t0 = INF;
        
        // video rectangle
        $w1 = $this->_inputWidth;
        $h1 = $this->_->_inputHeight;
        $t1 = $this->_->_inputHeight / $this->_inputWidth;
        
        // scaled rectangle
        $swidth = 0;
        $sheight = 0;
        if ($t0 < $t1) {
            $sheight = $this->_getEvenNumber($w0 * $t1);
            $swidth = $w0;
        } else {
            $sheight = $h0;
            $swidth = $this->_getEvenNumber($h0 / $t1);
        }
        
        return array($swidth, $sheight);
    }
    
    /**
     * Scales the input video inside a box.
     * 
     * This function scales the input video inside a box, preserving the aspect
     * ratio or not.
     * 
     * @param float   $width       Rectangle width
     * @param float   $height      Rectangle height
     * @param boolean $aspectRatio Preserves aspect ratio
     * 
     * @return void
     */
    public function scale($width, $height, $aspectRatio = true)
    {
        list($swidth, $sheight) = $aspectRatio
            ? $this->_getInsideScale($width, $height)
            : array($this->_getEvenNumber($width), $this->_getEvenNumber($height));
        
        $this->_scale = new SysCmdArgument();
        $this->_scale->setName("vf");
        $this->_scale->setValue("scale=$swidth:$sheight");
    }
    
    /**
     * Scales the input video inside a box.
     * 
     * <p>This function scales the input video inside a box, preserving the aspect
     * ratio. You can set the width or the height to zero. In that case, the
     * rectangle is considered as an infinite band. For example:</p>
     * 
     * <code>// scales the video input
     * $f->scaleInside(320, 0);   // fits the video into an infinite vertical band
     * $f->scaleInside(0, 240);   // fits the video into an infinite horizontal band
     * $f->scaleInside(320, 240); // fits the video into a rectangle
     * </code>
     * 
     * @param float $width  Rectangle width
     * @param float $height Rectangle height
     * 
     * @return void
     */
    public function scaleInside($width, $height)
    {
        list(
            $swidth,
            $sheight,
            $pwidth,
            $pheight,
            $px,
            $py
        ) = $this->_getInsideScale($width, $height);
        $scale = "scale=$swidth:$sheight";
        
        if (!is_infinite($pwidth) && !is_infinite($pheight)) {
            $scale .= ",pad=$pwidth:$pheight:$px:$py:000000";
        }
        
        $this->_scale = new SysCmdArgument();
        $this->_scale->setName("vf");
        $this->_scale->setValue($scale);
    }
    
    /**
     * Scales the input video outside a box.
     * 
     * This function scales the input video outside a box, preserving the aspect
     * ratio.
     * 
     * @param float $width  Rectangle width
     * @param float $height Rectangle height
     * 
     * @return void
     */
    public function scaleOutside($width, $height)
    {
        list($swidth, $sheight) = $this->_getOutsideScale($width, $height);
        
        $this->_scale = new SysCmdArgument();
        $this->_scale->setName("vf");
        $this->_scale->setValue("scale=$swidth:$sheight");
    }
    
    /**
     * Takes an snapshot.
     * 
     * <p>This function takes a snapshot of the video. If the $destination argument
     * is a directory, it saves the snapshot in an available file inside that
     * directory and returns the filename. For example:<p>
     * 
     * <code>// takes a snapshot at the second 10
     * $filename = $ffmpeg->snapshot("path/to/my/folder", 10);
     * echo "Your snapshot was saved in $filename";
     * </code>
     * 
     * @param string  $destination A directory or a file
     * @param integer $time        Time in seconds
     * 
     * @return string Destination filename
     */
    public function snapshot($destination, $time)
    {
        $filename = is_dir($destination)
            ? SysFile::getAvailName($destination, $this->_input, "jpg")
            : $destination;
        
        $cmd = new SysCmd("ffmpeg");
        $cmd->appendFlag("y");
        $cmd->appendFlag("i", $this->_input);
        $cmd->appendFlag("ss", gmdate("H:i:s", $time));
        $cmd->appendArgList("-f image2 -vcodec mjpeg -vframes 1");
        $cmd->appendArg($filename);
        $cmd->exec();
        
        return $filename;
    }
    
    /**
     * Gets the string representation of the instance.
     * 
     * This function overrides the SysCmd::toString() method.
     * 
     * @return string
     */
    public function toString()
    {
        $cmd = new SysCmd("ffmpeg");
        $cmd->appendFlag("y");
        
        $cmd->appendFlag("i", $this->_input);
        
        foreach ($this->args as $arg) {
            $cmd->appendArg($arg);
        }
        
        if ($this->_scale != null) {
            $cmd->appendArg($this->_scale);
        }
        
        if (!Text::isEmpty($this->_output)) {
            $cmd->appendArg($this->_output);
        }
        
        return $cmd->toString();
    }
}
