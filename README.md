xmlquery
========

The XMLQuery class parses and manipulates XML or HTML documents in similar way as jQuery does. It uses CSS selectors instead of XPath.


I. Creating an instances
```
// creates an instance from a string
$xml = new XMLQuery('<root>hello there</root>');

// creates an instance from an url
$xml = new XMLQuery('http://www.php.net');

// creates an instance from a filename
$xml = new XMLQuery('/home/username/my-file.xml');
```

II. Traversing nodes:
```
$xml = new XMLQuery('<root><books><item id="1" title="One" /><item id="2" title="Two" /></books></root>');

// use the 'select' function to get nodes from a CSS expression
// use the 'attr' function to get attributes from a node
$items = $xml->select('books item');
foreach ($items as $item) {
    echo "Id: " . $item->attr("id") . ", Title: " . $item->attr("title") . "\n";
}

// the previous example can also be written in the following modern way
// the functions 'select' and 'attr' are called internally
$items = $xml('books item');
foreach ($items as $item) {
    echo "Id: {$item->id}, Title: {$item->title}\n";
}

// gets the number of items
echo "Number of items: " . count($items);
```

III. Manipulation:
```
$xml = new XMLQuery('<root><books><item id="1" title="One" /><item id="2" title="Two" /></books></root>');
$books = $xml->select('books');

// changes an attribute and adds a new one
$item = $books->select('item[id=2]');
$item->attr('title', 'Twenty Thousand Leagues Under the Sea');
$item->attr('author', 'Jules Verne');

// changes the contents of an item
$item->text('Look at my horse, my horse is amazing');

// inserts a new item to the end of the books node
$books->append('<item id="3" title="Three" />');

// inserts a new item to the beginning of the books node
$books->prepend('<item id="0" title="Zero" />');

// removes an item
$item = $books->select('item[id=2]');
$item->remove();

// removes all content under the books node
$books->clear();
```

IV. Printing nodes
```
$xml = new XMLQuery('<root><books><item id="1" title="One" /><item id="2" title="Two" /></books></root>');
$item = $xml->select('books item[id=2]');

// prints the string representation of a node
echo $item->html();
```
