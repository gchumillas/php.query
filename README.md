phpQuery
========

A library for manipulating and traversing XML documents in an easy and intuitive way. This library is inspired by the jQuery library and borrows some interesting ideas, like chaining.

Installation
------------

Download the project:
```bash
git clone https://github.com/soloproyectos/phpquery
```

and copy the `classes` folder in your preferred location (optionally, rename it). Finally, copy and paste the following PHP code:
```PHP
require_once "< YOUR PREFERRED LOCATION >/classes/autoload.php";
use com\soloproyectos\common\dom\DomNode;
```

And that's all. You are ready to use this library.

Basic Examples
--------------

The most important methods are:

1. `query(<css selectors>)` or `xpath(<xpath expression>)`: finds nodes
2. `attr(<attribute name>, <optional value>)`: gets or sets attributes
3. `text(<optional text>)`: gets or sets inner texts
4. `html(<optional contents>)`: gets or sets inner XML code
5. `prepend(<string>)` and `append(<string>)`: prepends and appends contents
6. `remove()`: removes the node from the document
7. `clear()`: removes all child nodes


#### Creating instances:

You can either create an instance from scratch or from a given source:

```PHP
// creates an instance from scratch
// the following code creates an `<item />` node with the attributes `id` and `title`
$root = new DomNode("item", array("id" => 1, "title" => "Item 1"));

// creates an instance from a string
$xml = DomNode::createFromString('<root><item id="101" /><item id="102" /><item id="103" /></root>');

// creates an instance from a document
$doc = new DOMDocument("1.0", "UTF-8");
$doc->loadXML('<root><item id="101" /><item id="102" /><item id="103" /></root>');
$xml = DomNode::createFromDocument($doc);

// creates an instance from a given DOMElement
// $element is a DOMElement object
$xml = DomNode::createFromElement($element);
```

#### Using the `query` method

You can use the same `query` function to traverse either single or multiple nodes.

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

#### Using the `attr`, `text` and `html` methods:
```PHP
$xml = DomNode::createFromString(file_get_contents("test.xml"));

// prints books info
$books = $xml->query("books item");
foreach ($books as $book) {
    echo "Title: " . $book->attr("title") . "\n";
    echo "Author: " . $book->attr("author_id") . "\n";
    echo "ISBN: " . $book->query("isbn")->text() . "\n";
    echo "Available: " . $book->query("available")->text() . "\n";
    echo "Description: " . trim($book->query("description")->text()) . "\n";
    echo "---\n";
}

// gets the number of items
echo "Number of items: " . count($books);

// prints the node contents
$genres = $xml->query("genres");
echo $genres->html();
```

#### Using the `attr`, `text` and `html` methods to change nodes:

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

#### Using `prepend` and `append` methods:

```PHP
$xml = DomNode::createFromString('<root><item id="101" /><item id="102" /><item id="103" /></root>');

// appends contents
$item = $xml->query("item[id = 102]");
$item->append('<subitem id="102.1" title="Subitem title">Some text here ...</subitem>');
echo $xml;

// appends a DomNode object
$item->append(new DomNode("subitem", array("id" => "102.1", "title" => "Subitem title"), "Some text here ..."));
echo $xml;

// appends a DomNode object and calls the `callback` function
$item->prepend(new DomNode('subitem', array("id" => "102.2", "title" => "Subitem title"), function (target) {
    target->text("I'm the first child node ...");
}));
echo $xml;
```

#### Using the `remove` and `clear` methods:

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

You can concatenate multiple methods in the same command:

```PHP
$xml = DomNode::createFromString('<root><item id="101" /><item id="102" /><item id="103" /></root>');

// changes and prints the node in the same line
echo $xml->query("item[id = 102]")->attr("title", "Item 101")->text("Some text...")->append("<subitem />");
```

#### Creating documents from scratch

You can use the `DomNode` class to create XML documents from scratch. This is a very nice feature if you want to create arbitrary XML documents and want to ensure that they are well formed:

```PHP
$xml = new DomNode('root', function ($target) {
    // adding some items to the root node
    for ($i = 0; $i < 3; $i++) {
        $target->append(new DomNode("item", array("id" => $i, "title" => "Item $i"), function ($target) use ($i) {
            $target->text("This is the item $i");
        }));
    }
    
    // prepends a node
    $target->prepend(new DomNode("title", "This is the main title ..."));
    
    // appends a complex node
    $target->append(new DomNode("node", array("title" => "Complex node"), function ($node) {
        $node->append(new Domnode("item", array("id" => 1, "title" => "Subitem 1"), function ($target) {
            $target->text("I'm on the subway");
        }));
    }));
});
echo $xml;
```
