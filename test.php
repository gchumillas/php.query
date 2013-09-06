<?php
header("Content-Type: text/plain; charset=UTF-8");
require_once "classes/xml-query/xml-query.php";

$root = new XMLQuery("test.xml");

/*
 * retrieves books and prints info
 */
echo "Printing books ...\n";
$books = $root->select("books item");
foreach ($books as $book) {
	echo $book->attr("id") . ", " . $book->attr("title") . ", " . $book->attr("author") . "\n";
}
echo "\n";

/*
 * alternative and modern way
 */
echo "Printing books again ...\n";
$books = $root("books item");  // no "select" needed here
foreach ($books as $book) {
	echo "{$book->id}, {$book->title}, {$book->author}\n";  // no "attr" needed here
}
echo "\n";

/*
 * getting single node
 */
echo "Printing a single book\n";
$item = $root("books item[id=book-3]");
if (count($item) > 0) {
	echo "{$item->id}, {$item->title}, {$item->author}\n";
} else {
	echo "No items were found\n";
}
echo "\n";

/*
 * manipulates the XML document
 */

// appends a new book to the books node
$books = $root("books");
$books->append('<item id="book-5" title="Book 5" author="Author 5" />');

// prepends a new book to the books node
$books->prepend('<item id="book-0" title="Book 0" author="Author 0" />');

// removes a book
$item = $books("item[id=book-3]");
$item->remove();

// and finally prints the XML representation of the books node.
echo "Printing the books node\n";
echo $books->html();  // this is equivalent to $books->xml();
echo "\n\n";

// and now removes all books nodes and prints again the XML representation
echo "Clearing the books node and printing again (as you can see, the 'books' node is now empty)\n";
$books->clear();
echo $books->html();
echo "\n\n";
