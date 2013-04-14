<?php

require_once('FFDB.php');
require_once('credentials.php');

$ffdb = new FFDB();
$ffdb->initialize();

$fetchAgain = true;
$fetchSize = 100;
$feed='ffebookpaylasimalan';
$start = 0;

while($fetchAgain)
{
	$fetchAgain = false;
	$u = "https://$user:$pwd@friendfeed-api.com/v2/feed/$feed?pretty=1&num=$fetchSize&start=$start&raw=1&maxcomments=500&maxlikes=500";
	echo("Fetching... feed:$feed, start: $start, size: $fetchSize .\n");

	//$u = "file://" . $argv[1];
	$c = file_get_contents($u);
	$ff = json_decode($c);

	$num_of_changed = $ffdb->insert_if_changed($ff->entries);

	echo("Number of changed entries: $num_of_changed \n");
	if($num_of_changed > $fetchSize/2)
	{
		$fetchAgain = true;
	}
	$start += $fetchSize;
}


