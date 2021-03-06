<?php
/**
 * This file is part of ffcrawler.
 *
 *  Copyright 2013 by Emin Aksehirli <emin.aksehirli@gmail.com>
 *
 *  Licensed under GNU Affero General Public License 1.0 or later.
 *  Some rights reserved. See COPYING.
 *
 * @license AGPL-1.0+ <http://spdx.org/licenses/AGPL-1.0>
 */

require_once('FFDB.php');
require_once('BookDB.php');
require_once('credentials.php');
require_once('config.php');

$ffdb = new FFDB();
$bookdb = new BookDB();

$resume_data = $ffdb->get_worker_data('file_downloader', 'last_id');

$files = $ffdb->get_files($resume_data);

$types = $bookdb->types;
$finfo = finfo_open(FILEINFO_MIME_TYPE); // return mime type aka mimetype extension

$out_dir = Config::book_download_dir;

$ffdb->insert_worker_data('file_downloader', 'start_id', $resume_data + 1);

$bookdb->begin();

$last_successful_id = $resume_data;
for($i=0; $i < sizeof($files); $i++)
{
	$file = $files[$i];
	$u = $file['url'];

	echo("Downloading... ");
	$c = file_get_contents($u);

	if(!$c)
	{
		echo("Cannot open '$u' . Aborting!\n");
		break;
	}

	$file_name = $out_dir . $file['name'];

	while(file_exists($file_name))
	{
		$p = pathinfo($file_name);
		$file_name =  $p['dirname'] . '/' .  $p['filename'] . "_copy." .  $p['extension'];
	}

	if(!file_put_contents($file_name, $c))
	{
		echo("Cannot write the file '$file_name' . Aborting!\n");
		break;
	}
	
	echo("Saved to $file_name \n");

	// Insert into booksDb
	$n = pathinfo($file_name, PATHINFO_BASENAME);
	$type = type_of($file_name);
	$p = pathinfo($n);
	$basename =  $p['filename'];
	$a = $t = '';
	if($d = strrpos($basename, "-")){
		$a = trim(substr($basename, 0, $d));
		$t = trim(substr($basename, $d + 1));
	}

	$bookdb->insert_a_book($a, $t, $n, $type['id'], $file['url'], 1);

	$last_successful_id = $file['id'];
}

$num_of_entries = $bookdb->end();
//echo("$num_of_entries books are added to database.");

$last_id = $last_successful_id;
$ffdb->insert_worker_data('file_downloader', 'last_id', $last_id);

finfo_close($finfo);

function type_of($f)
{
	global $types, $finfo;
	$type = finfo_file($finfo, $f);
	if(isset($types[$type]))
		return $types[$type];

	$type = system("file -b --mime-type \"$f\"");
	if(isset($types[$type]))
		return $types[$type];
	$type = system("file -b \"$f\"");
	$a = explode(" ", $type);
	if(isset($types[$a[0]]))
		return $types[$a[0]];

	return $types['bad'];
}
