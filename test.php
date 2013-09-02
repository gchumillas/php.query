<?php
header("Content-Type: text/plain; charset=UTF-8");
require_once "classes/xml-query.php";

$root = new XMLQuery("test.xml");

// retrieves books and prints info
echo "Printing books ...\n";
$books = $root->select("books item");
foreach ($books as $book) {
	echo $book->attr("id") . ", " . $book->attr("title") . ", " . $book->attr("author") . "\n";
}
echo "\n";

// alternative and modern way
echo "Printing books again ...\n";
$books = $root("books item");  // no "select" needed here
foreach ($books as $book) {
	echo "{$book->id}, {$book->title}, {$book->author}\n";  // no "attr" needed here
}
echo "\n";

// getting single node
echo "Printing a single book\n";
$item = $root("books item[id=book-3]");
if (count($item) > 0) {
	echo "{$item->id}, {$item->title}, {$item->author}\n";
} else {
	echo "No items were found\n";
}