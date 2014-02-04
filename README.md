phpQuery
========

The phpQuery class is inspired by the glorified [jQuery](http://jquery.com/) library. The purpose of this class is to simplify the access and manipulation of XML documents. It represents, in most cases, a replacement of the built-in DOM library.

Instead of the XPath query language, this class uses CSS selectors. This is an advantage for those who are not familiar with XPath . In any case, it is still possible to use XPath: just use the 'xpath' method instead of 'query'.

Another added advantage is that we no longer need to handle different classes as DOMNode, DOMElement, DOMNodeList, etc ... In our case, all nodes are represented by the same class: phpQuery, which simplifies the code.

Installation
------------

Copy and paste the `classes` folder into your application and include the file 'classes/php-query.php'. That is:

```php
use com\soloproyectos\core\xml\phpQuery;
require_once "classes/php-query.php";
```

And that's all. You are ready to use the phpQuery class.

Basic Examples
--------------

### Creating instances:
```php
// loads an XML document from a string
$query = new phpQuery('<root><item id="101" /><item id="102" /><item id="103" /></root>');

// loads an HTML document from a url
$query = new phpQuery('http://www.php.net');

// loads an XML document from a file
$query = new phpQuery('/home/username/my-file.xml');

// loads an XML document from a specific DOMNode object
$doc = new DOMDocument("1.0", "UTF-8");
$doc->loadXML('<root><item id="101" /><item id="102" /><item id="103" /></root>');
$query = new phpQuery(doc);
```

### Traversing nodes:
```php
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
echo "Number of items: " . count($items);
```
