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
require_once('credentials.php');
require_once('config.php');

$ffdb = new FFDB();

$sources = $ffdb->get_sources();
$out_dir = Config::json_out_dir;

//$online = false;
$online = true;

foreach($sources as $source)
{
	$ffdb->set_source_id($source['id']);
	if(!file_exists($out_dir . $source['type']))
	{
		mkdir($out_dir . $source['type'], 0777, true);
	}

	$fetch_again = true;
	$fetch_size = 100; // Because of API limitations maximum is 100!
	$start = 0;

	$ffdb->begin();

	while($fetch_again)
	{
		$fetch_again = false;
		if($online)
		{
			$u = "https://$user:$pwd@friendfeed-api.com/v2/" . $source['address'] . "?pretty=1&num=$fetch_size&start=$start&raw=1&maxcomments=500&maxlikes=500";
			$c = file_get_contents($u);
			file_put_contents($out_dir . $source['address'] . "_$start" . "_$fetch_size.json", $c);
		}
		else
		{
			$u = "file://" . $out_dir . $source['address'] . "_$start" . "_$fetch_size.json";
			$c = file_get_contents($u);
		}
		echo("Fetching... source:" . $source['address'] . ", start: $start, size: $fetch_size .\n");

		$ff = json_decode($c);

		if(!isset($ff->entries) || sizeof($ff->entries) == 0)
		{
			$fetch_again = false;
			break;
		}

		$num_of_changed = $ffdb->insert_if_changed($ff->entries);

		echo("Number of changed entries: $num_of_changed \n");
		if($num_of_changed > $fetch_size/4)
		{
			$fetch_again = true;
		}
		$start += $fetch_size;
	}

	$num_of_entries = $ffdb->end();
}


