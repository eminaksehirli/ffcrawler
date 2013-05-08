<?php

require_once('BookDB.php');
require_once('FFDB.php');

$ffdb = new FFDB();
$bookdb = new BookDB();

$ffurls = $ffdb->get_url_to_entry();

$books = $bookdb->get_formatted_info();

$title = implode(array("id", "Yazar", "Kitap Adı", "Dosya Adı", "Uzantı", "Dil", "Tamam mı?", "Notlar", "Dosya URL", "Entry", "Entry URL"), "\t");

$out_file = @fopen('/tmp/book_db_1.csv', "w");

fputs($out_file, $title . "\n");

$book_counter = 0;
//for($i=0; $i< 20; $i++)
foreach($books as $book_arr)
{
	//$book_arr = $books[$i];
	$book_info = array();

	$book_info[] = $book_arr['id'];
	$book_info[] = $book_arr['author'];
	$book_info[] = $book_arr['title'];
	$book_info[] = $book_arr['filename'];
	$book_info[] = $book_arr['type'];
	$book_info[] = $book_arr['language'];
	$book_info[] = ""; // Tamam mi?
	$book_info[] = ""; // Notlar
	$book_info[] = remove_link($book_arr['link']);

	$ffurl = $ffurls[$book_arr['link']];

	$book_info[] = $ffurl['body'];
        $book_info[] = remove_link($ffurl['link']); // . "\n";

	for($i = 0; $i < sizeof($book_info); $i++)
	{
		$book_info[$i] = strtr($book_info[$i], "\n\r\t", '   ' );
	}

	$book_str = implode("\t",$book_info) . "\n";

	fputs($out_file, $book_str);

	$book_counter++;

	if($book_counter % 500 == 0)
	{
		fclose($out_file);
		$out_file = @fopen('/tmp/book_db_' . ($book_counter + 1) . '.csv', "w");
		fputs($out_file, $title . "\n");
	}
}

function remove_link($str)
{
	return str_replace('http://', '', $str);
}
