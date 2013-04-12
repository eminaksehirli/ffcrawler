<?php

$ent_ins_qr = 'INSERT INTO entries VALUES (NULL, :ff_id, :body, :rawBody, :rawLink, :date, :via, :user_id);';
$comment_ins_qr = "INSERT OR IGNORE INTO comments VALUES (NULL, :ff_id, :body, :rawBody, :entry_id, :user_id, :date, :via);";
$file_ins_qr = "INSERT OR IGNORE INTO files VALUES (NULL, :url, :name, :entry_id, :type, :size);";
$thumbnails_ins_qr = "INSERT OR IGNORE INTO thumbnails VALUES (NULL, :entry_id, :url, :link);";
$user_ins_qr = "INSERT INTO users VALUES (NULL, :ff_id, :name, :private);";
$like_ins_qr = "INSERT OR IGNORE INTO likes VALUES (:entry_id, :user_id, :date);";

$db = new SQLite3("ffebook.dat");

$entry_cache = array();
$user_cache = array();

update_entry_cache();
update_user_cache();


//$u = "file:///tmp/ff_file_ffebookpaylasimalan_0.json";
$u = "file://" . $argv[1];

$c = file_get_contents($u);

$ff = json_decode($c);

foreach($ff->entries as $f)
{
	if(!isset($entry_cache[$f->id]))
	{
		$user = $f->from;
		check_user_and_add($user);

		$stat = $db->prepare($ent_ins_qr);

		$stat->bindParam(':ff_id', $f->id);
		$stat->bindParam(':body', $f->body);
		$stat->bindParam(':rawBody', $f->rawBody);
		$stat->bindParam(':rawLink', $f->rawLink);
		$stat->bindParam(':date', format_date($f->date));
		$stat->bindParam(':via', $f->via->name);
		$stat->bindParam(':user_id', $user_cache[$f->from->id]);

		$r = $stat->execute();

		if(!$r)
		{
			echo("Problem while inserting the entry id " . $f-> id . "\n");
		}
		update_entry_cache();
	}

	if(isset($f->comments))
	{
		foreach($f->comments as $c)
		{
			check_user_and_add($c->from);
			$stat = $db->prepare($comment_ins_qr);
			$stat->bindParam(':ff_id', substr($c->id, strlen($f->id)));
			$stat->bindParam(':body', $c->body);
			$stat->bindParam(':rawBody', $c->rawBody);
			$stat->bindParam(':entry_id', $entry_cache[$f->id]);
			$stat->bindParam(':user_id', $user_cache[$c->from->id]);
			$stat->bindParam(':date', format_date($c->date));
			$stat->bindParam(':via', $c->via->name);

			$stat->execute();
		}
	}

	if(isset($f->likes))
	{
		foreach($f->likes as $l)
		{
			check_user_and_add($l->from);

			$stat = $db->prepare($like_ins_qr);

			$stat->bindParam(':entry_id', $entry_cache[$f->id]);
			$stat->bindParam(':user_id', $user_cache[$l->from->id]);
			$stat->bindParam(':date', format_date($l->date));

			$stat->execute();
		}
	}

	if(isset($f->files))
	{
		foreach($f->files as $file)
		{
			$stat = $db->prepare($file_ins_qr);

			$stat->bindParam(':url', $file->url);
			$stat->bindParam(':name', $file->name);
			$stat->bindParam(':entry_id', $entry_cache[$f->id]);
			$stat->bindParam(':type', $file->type);
			$stat->bindParam(':size', $file->size);

			$stat->execute();
		}
	}

	if(isset($f->thumbnails))
	{
		foreach($f->thumbnails as $t)
		{
			# = "INSERT INTO thumbnails VALUES (NULL, :entry_id, :url, :link);";
			$stat = $db->prepare($thumbnails_ins_qr);

			$stat->bindParam(':entry_id', $entry_cache[$f->id]);
			$stat->bindParam(':url', $t->url);
			$stat->bindParam(':link', $t->link);

			$stat->execute();
		}
	}
}


function update_user_cache()
{
	global $db, $user_cache;
	$qr = 'SELECT ff_id, id FROM users;';
	$results = $db->query($qr);
	while($row = $results->fetchArray()) {
		$user_cache[$row['ff_id']] = $row['id'];
	}
}

function update_entry_cache()
{
	global $db, $entry_cache;
	$qr = 'SELECT ff_id, id FROM entries;';
	$results = $db->query($qr);
	while($row = $results->fetchArray())
	{
		$entry_cache[$row['ff_id']] = $row['id'];
	}
}
function check_user_and_add($user)
{
	global $db, $user_cache, $user_ins_qr;
	if(!isset($user_cache[$user->id]))
	{
		$stat = $db->prepare($user_ins_qr);

		$stat->bindParam(':ff_id', $user->id, SQLITE3_TEXT);
		$stat->bindParam(':name', $user->name, SQLITE3_TEXT);
		$stat->bindValue(':private', isset($user->private), SQLITE3_INTEGER);

		$result = $stat->execute();
		update_user_cache();
	}
}

function format_date($str)
{
	$trans = array("T" => " ", "Z" => "");
	return trim(strtr(format($str), $trans));
}
function format($str)
{
	$trans = array("\n" => " ", "\r" => " ");
	return trim(strtr($str, $trans));
}

