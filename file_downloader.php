<?php

require_once('FFDB.php');
require_once('credentials.php');

$ffdb = new FFDB();
$ffdb->initialize();

$files = $ffdb->get_files();

$out_dir = "ff-book/";

for($i=100; $i < sizeof($files); $i++)
{
	$file = $files[$i];
	$u = $file['url'];

	$c = file_get_contents($u);

	$file_name = $out_dir . $file['name'];

	if(file_exists($file_name))
	{
		$file_name = $file_name . "_copy";
	}

	file_put_contents($file_name, $c); 
}
