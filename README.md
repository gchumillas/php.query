phpQuery
========

The phpQuery class is inspired by the glorified [jQuery](http://jquery.com/) library. The purpose of this class is to simplify the access and manipulation of XML documents. It represents, in most cases, a replacement of the built-in DOM library.

Instead of the XPath query language, this class uses CSS selectors. This is an advantage for those who are not familiar with XPath . In any case, it is still possible to use XPath: just use the 'xpath' method instead of 'query'.

Another added advantage is that we no longer need to handle different classes as DOMNode, DOMElement, DOMNodeList, etc ... In our case, all nodes are represented by the same class: phpQuery, which simplifies the code.

Installation
------------

Copy and paste the `classes` folder into your application and include the file 'classes/php-query.php'. That is:

```PHP
use com\soloproyectos\core\xml\phpQuery;
require_once "classes/php-query.php";
```

And that's all. You are ready to use the phpQuery class.

Basic Examples
--------------

The most important methods are:

1. `query(<css selectors>)` or `xpath(<xpath expression>)` for getting nodes from a document
2. `attr(<attribute name>, <optional value>)` for getting or setings attribute values
3. `text(<optional text>)` for getting or settings node texts
4. `html()` for getting or settings the inner contents
5. `prepend(<new node>)` and `append(<new node>)` for inserting nodes
6. `remove()` for removing a specific node
7. `clear()` for removing all child nodes of a given node


#### Creating instances:

You can create instances from different sources.

```PHP
// loads an XML document from a string
$xml = new phpQuery('<root><item id="101" /><item id="102" /><item id="103" /></root>');

// loads an HTML document from a url
$xml = new phpQuery('http://www.php.net');

// loads an XML document from a file
$xml = new phpQuery('/home/username/my-file.xml');

// loads an XML document from a specific DOMDocument object
$doc = new DOMDocument("1.0", "UTF-8");
$doc->loadXML('<root><item id="101" /><item id="102" /><item id="103" /></root>');
$xml = new phpQuery($doc);

// loads an XML document from a specif DOMNode object
// $domNode can be any DOMNode object and represents the `root` node
$xml = new phpQuery($domNode);
```

#### Using the `query` method

Note that you can use the same `query` function to select a single node or multiple nodes. If you feel more comfortable with the XPath language, you can use the `xpath` method instead. That's your choise :)

```PHP
$xml = new phpQuery('<root><item id="101" /><item id="102" /><item id="103" /></root>');

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
$xml = new phpQuery("test.xml");

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
$xml = new phpQuery('<root><item id="101" /><item id="102" /><item id="103" /></root>');

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

You can use the `prepend` and `append` functions in two ways:

1. `append(<string representation of the node>)`
2. `append(<node name>, <optional list of attributes>, <optional inner texts>, <optional callback function>)`

```PHP
$xml = new phpQuery('<root><item id="101" /><item id="102" /><item id="103" /></root>');

// inserts a new child node at the end
$item = $xml->query("item[id = 102]");
$item->append('<subitem id="102.1" title="Subitem title">Some text here ...</subitem>');
echo $xml;

// this is another way to insert a node
$item->append("subitem", array("id" => "102.1", "title" => "Subitem title"), "Some text here ...");
echo $xml;

// and this is another way
$item->prepend('subitem', array("id" => "102.2", "title" => "Subitem title"), function ($subitem) {
    $subitem->text("I'm the first child node ...");
});
echo $xml;
```

#### Using the `remove` and `clear` methods:

```PHP
$xml = new phpQuery('<root><item id="101" /><item id="102" /><item id="103" /></root>');

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
$xml = new phpQuery('<root><item id="101" /><item id="102" /><item id="103" /></root>');
// changes and prints the node in the same line
echo $xml->query("item[id = 102]")->attr("title", "Item 101")->text("Some text...")->append("subitem");
```

#### Building XML documents from scratch

You can use phpQuery to create XML documents from scratch. This is a very nice feature if you want to create arbitrary XML documents and want to ensure that the created documents are well formed:

```PHP
$xml = new phpQuery('root', function ($target) {
    // adding some items to the root node
    for ($i = 0; $i < 3; $i++) {
        $target->append("item", array("id" => $i, "title" => "Item $i"), function ($target) use ($i) {
            $target->text("This is the item $i");
        });
    }
    
    // prepends a node
    $target->prepend("title", "This is the main title ...");
    
    // appends a complex node
    $target->append("node", array("title" => "Complex node"), function ($node) {
        $node->append("item", array("id" => 1, "title" => "Subitem 1"), function ($target) {
            $target->text("I'm on the subway");
        });
    });
});
echo $xml;
```
