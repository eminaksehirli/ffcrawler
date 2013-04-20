<?php

require_once('FFDB.php');
require_once('credentials.php');

$ffdb = new FFDB();

$resume_data = $ffdb->get_worker_data('file_downloader', 'last_id');

$files = $ffdb->get_files($resume_data);
$out_dir = "ff-book/";

$ffdb->insert_worker_data('file_downloader', 'start_id', $resume_data + 1);

for($i=0; $i < sizeof($files); $i++)
{
	$file = $files[$i];
	$u = $file['url'];
	$c = file_get_contents($u);
	echo("Downloading... ");

	$file_name = $out_dir . $file['name'];

	while(file_exists($file_name))
	{
		$p = pathinfo($file_name);
		$file_name =  $p['dirname'] . '/' .  $p['filename'] . "_copy." .  $p['extension'];
	}

	file_put_contents($file_name, $c); 
	echo("Saved to $file_name \n");
	
}

$last_id = $ffdb->get_last_id_of_files();
$ffdb->insert_worker_data('file_downloader', 'last_id', $last_id);
