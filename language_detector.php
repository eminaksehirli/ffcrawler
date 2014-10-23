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

require_once('BookDB.php');
require_once('config.php');

$bookdb = new BookDB();

$db_files = $bookdb->get_filenames();
$langs = $bookdb->get_languages();


$dir = Config::book_download_dir . "/text/";

$files = scandir($dir);

foreach($files as $file)
{
	if(strcmp('.', substr($file, 0, 1)) == 0)
		continue;

	$handle = @fopen($dir . $file, "r");

	$tr = $en = 0;
	$lineCount = 0;
	if ($handle)
	{
		while (($buffer = fgets($handle, 4096)) !== false)
		{
			$lineCount++;
			if($arr = split(" ", $buffer))
			{
				foreach($arr as $word)
				{
					if(strcmp(trim($word), "ve") == 0)
					    $tr++;
					if(strcmp(trim($word), "and") == 0)
					    $en++;
				}
			}
		}
		if (!feof($handle)) {
		    echo "Error: unexpected fgets() fail\n";
		}
		fclose($handle);

		$s = substr($file, 0, -4);
		$id = $db_files[$s];

		if(!isset($id))
		    continue;

		//echo("$id, " . substr($file, 0, 10) . " : ");

		if($en > 50 && $tr < 50)
			$bookdb->update_language($id, $langs['en']);
		if($tr > 50 && $en < 50)
			$bookdb->update_language($id, $langs['tr']);

		//echo("\n");
	}
}
