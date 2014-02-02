<?php
/**
 * This file contains the SysFfmpegHelper class.
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
use com\soloproyectos\core\sys\cmd\SysCmdHelper;
use com\soloproyectos\core\sys\ffmpeg\exception\SysFfmpegException;

/**
 * Class SysFfmpegHelper.
 * 
 * This is a helper class.
 * 
 * @category System
 * @package  SysFfmpeg
 * @author   Gonzalo Chumillas <gonzalo@soloproyectos.com>
 * @license  https://raw.github.com/soloproyectos/core/master/LICENSE BSD 2-Clause License
 * @link     https://github.com/soloproyectos/core
 */
class SysFfmpegHelper
{
    
    /**
     * Gets video info.
     * 
     * @param string $video Video path
     * 
     * @return array (width, height, duration, fps, size)
     */
    public static function getVideoInfo($video)
    {
        $ret = array(0, 0, 0, 0, 0);
        
        if (!is_file($video)) {
            throw new SysFfmpegException("Video not found: $video");
        }
        
        // retrieves video info
        $command = "ffmpeg -i " . SysCmdHelper::escape($video);
        exec("$command 2>&1", $output, $state);
        $result = implode("\n", $output);

        // dimension
        $result = substr($result, strpos($result, "Input #0"));
        if (preg_match("/, (\d+)x(\d+)/", $result, $matches)) {
            $ret[0] = $matches[1];
            $ret[1] = $matches[2];
        }

        // duration in seconds
        if (preg_match("/duration: ([\d\:\.]+)/i", $result, $matches)) {
            $duration = $matches[1];
            list($hh, $mm, $ss) = explode(":", $duration);
            $ret[2] = 3600 * $hh + 60 * $mm + $ss;
        }
        
        // frames per second
        if (preg_match("/(\d+) fps/", $result, $matches)) {
            $ret[3] = $matches[1];
        }

        // filesize
        $ret[4] = filesize($video);
        
        // probably not a video
        if (!$ret[0] && !$ret[1] && !$ret[2] && !$ret[3]) {
            throw new SysFfmpegException($result);
        }

        return $ret;
    }
}
