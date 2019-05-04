phpQuery
========

A library to manipulate and parse DOM documents in an easy and intuitive way. This library was inspired by the jQuery library and borrows some interesting ideas, like chaining.

Installation
------------

Download the project:
```bash
git clone https://github.com/soloproyectos/phpquery
```

and copy the `classes` folder in your preferred location (optionally, rename it). Finally, copy and paste the following PHP code:
```PHP
require_once "< YOUR PREFERRED LOCATION >/classes/autoload.php";
use com\soloproyectos\common\dom\node\DomNode;
```

And that's all. You are ready to use this library.

Methods
-------

#### Create nodes from a given source:
  * `DomNode::createFromElement($element)`: creates an instance from a DOMElement object
  * `DomNode::createFromNode($node)`: creates an instance from a DomNode object
  * `DomNode::createFromString($string)`: creates an instance from a string

#### Basic methods:
  * `DomNode::document()`: gets the internal DOMDocument instance
  * `DomNode::elements()`: gets internal DOM elements
  * `DomNode::name()`: gets the node name
  * `DomNode::parent()`: gets the parent node or a `null` value
  * `DomNode::root()`: gets the root node
  * `DomNode::query($cssSelectors)`: finds nodes using CSS selectors
  * `DomNode::xpath($expression)`: finds nodes using XPath expressions
  * `DomNode::remove()`: removes the node from the document
  * `DomNode::clear()`: removes all child nodes
  * `DomNode::data($name, [$value])`: gets or sets arbitrary data
  * `DomNode::append($string)`: appends inner XML text
  * `DomNode::prepend($string)`: prepends inner XML text
  * `DomNode::html([$string])`: gets or sets inner XML text
  * `DomNode::text([$string])`: gets or sets inner text

#### Attributes:
  * `DomNode::attr($name, [$value])`: gets or sets an attribute
  * `DomNode::hasAttr($name)`: checks if a node has an attribute

#### CSS attributes:
  * `DomNode::css($name, [$value])`: gets or sets a CSS attribute
  * `DomNode::hasCss($name)`: checks if a node has a CSS attribute

#### Classes:
  * `DomNode::addClass($className)`: adds a class to the node
  * `DomNode::hasClass($className)`: checks if a node has a class
  * `DomNode::removeClass($className)`: removes a class from the node

Basic Examples
--------------

#### Create instances

Create a simple node:
```PHP
// creates a simple node with two attributes and inner text
$item = new DomNode("item", array("id" => 101, "title" => "Title 101"), "Inner text here...");
echo $item;
```

Create a complex node:
```PHP
// in this case we use a callback function to add complex structures into the node
$root = new DomNode("root", function ($target) {
    // adds three subnodes
    for ($i = 0; $i < 3; $i++) {
        $target->append(new DomNode("item", array("id" => $i, "title" => "Title $i"), "This is the item $i"));
    }
    
    // appends some XML code
    $target->append("<text>This text is added to the end.</text>");
    
    // prepends some XML code
    $target->prepend("<text>This text is added to the beginning</text>");
});
echo $root;
```

#### Create instances from a given source:

```PHP
// creates an instance from a string
$xml = DomNode::createFromString('<root><item id="101" /><item id="102" /><item id="103" /></root>');

// creates an instance from a given DOMElement
$doc = new DOMDocument("1.0", "UTF-8");
$doc->loadXML('<root><item id="101" /><item id="102" /><item id="103" /></root>');
$xml = DomNode::createFromElement($doc->documentElement);
```

#### Use the `query` method

You can use the same `query` function to retrieve either single or multiple nodes.

```PHP
$xml = DomNode::createFromString('<root><item id="101" /><item id="102" /><item id="103" /></root>');

// selects and prints all items
$items = $xml->query("item");
foreach ($items as $item) {
    echo $item . "\n";
}

// select and prints a single item
$item = $xml->query("item[id = 102]");
echo $item;
```

#### Use the `attr`, `text` and `html` methods:
```PHP
$xml = DomNode::createFromString(file_get_contents("test.xml"));

// prints books info
$books = $xml->query("books item");
foreach ($books as $book) {
    echo "Title: " . $book->attr("title") . "\n";
    echo "Author: " . $book->attr("author_id") . "\n";
    echo "ISBN: " . $book->query("isbn")->text() . "\n";
    echo "Available: " . $book->query("available")->text() . "\n";
    echo "Description: " . $book->query("description")->text() . "\n";
    echo "---\n";
}

// gets the number of items
echo "Number of items: " . count($books);

// prints inner XML text
$genres = $xml->query("genres");
echo $genres->html();
```

#### Use the `attr`, `text` and `html` methods to change contents:

In the previous example we used `attr`, `text` and `html` for getting contents. In this example we are use the same methods to change the document.

```PHP
$xml = DomNode::createFromString('<root><item id="101" /><item id="102" /><item id="103" /></root>');

// changes or adds attributes and inner texts
$item = $xml->query("item[id = 102]");
$item->attr("id", 666);
$item->attr("title", "Item 666");
$item->text("I'm an inner text");
echo $item;

// changes inner contents
$item = $xml->query("item[id = 103]");
$item->html('<subitem>I am a subitem</subitem>');
echo $item;
```

#### Use `prepend` and `append` methods:

```PHP
$xml = DomNode::createFromString('<root><item id="101" /><item id="102" /><item id="103" /></root>');

// appends contents
$item = $xml->query("item[id = 102]");
$item->append('<subitem id="102.1" title="Subitem title">This text goes to the end...</subitem>');
echo $xml;

// appends a DomNode object
$item->append(new DomNode("subitem", array("id" => "102.1", "title" => "Subitem title"), "Some inner text here ..."));
echo $xml;

// appends a DomNode object and calls the `callback` function
$item->prepend(new DomNode("subitem", array("id" => "102.2", "title" => "Subitem title"), function ($target) {
    $target->text("I'm the first child node ...");
}));
echo $xml;
```

#### Use the `remove` and `clear` methods:

```PHP
$xml = DomNode::createFromString('<root><item id="101" /><item id="102" /><item id="103" /></root>');

// removes a single item
$item = $xml->query("item[id = 103]");
$item->remove();
echo $xml;

// removes a list of items
$items = $xml->query("item:even");
$items->remove();
echo $xml;

// removes all chid nodes
$xml->clear();
echo $xml;
```

#### Chaining

You can concatenate multiple methods in the same line:

```PHP
$xml = DomNode::createFromString('<root><item id="101" /><item id="102" /><item id="103" /></root>');

// changes and prints the node in the same line
echo $xml->query("item[id = 102]")->attr("title", "Item 102")->text("Some text...")->append("<subitem />");
```
