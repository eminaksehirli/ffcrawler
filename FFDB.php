<?php

class FFDB
{
	const ent_ins_qr = 'INSERT INTO entries VALUES (NULL, :ff_id, :body, :rawBody, :rawLink, :date, :via, :user_id, :source_id);';
	const comment_ins_qr = 'INSERT OR IGNORE INTO comments VALUES (NULL, :ff_id, :body, :rawBody, :entry_id, :user_id, :date, :via);';
	const file_ins_qr = 'INSERT OR IGNORE INTO files VALUES (NULL, :url, :name, :entry_id, :type, :size);';
	const thumbnails_ins_qr = 'INSERT OR IGNORE INTO thumbnails VALUES (NULL, :entry_id, :url, :link);';
	const user_ins_qr = 'INSERT INTO users VALUES (NULL, :ff_id, :name, :private);';
	const like_ins_qr = 'INSERT OR IGNORE INTO likes VALUES (:entry_id, :user_id, :date);';

	public $entry_cache = array();
	public $user_cache = array();
	private $source_id;
	protected $db;

	function initialize()
	{
		$dbName = "ffebook.dat";
		$this->db = new SQLite3($dbName);

		$this->update_entry_cache();
		$this->update_user_cache();
	}

	function insert_entry($entry)
	{
		if(!isset($this->entry_cache[$entry->id]))
		{
			$user = $entry->from;
			$this->check_user_and_add($user);

			$stat = $this->db->prepare(self::ent_ins_qr);

			$stat->bindParam(':ff_id', $entry->id);
			$stat->bindParam(':body', $entry->body);
			$stat->bindParam(':rawBody', $entry->rawBody);
			$stat->bindParam(':rawLink', $entry->rawLink);
			$stat->bindParam(':date', $this->format_date($entry->date));
			$stat->bindParam(':via', $entry->via->name);
			$stat->bindParam(':user_id', $this->user_cache[$entry->from->id]);
			$stat->bindParam(':source_id', $this->source_id);

			$r = $stat->execute();

			if(!$r)
			{
				echo("Problem while inserting the entry id " . $entry-> id . "\n");
			}
			$this->update_entry_cache();
		}
	}

	function insert_comments($entry)
	{
		if(isset($entry->comments))
		{
			foreach($entry->comments as $c)
			{
				$this->check_user_and_add($c->from);
				$stat = $this->db->prepare(self::comment_ins_qr);
				$stat->bindParam(':ff_id', substr($c->id, strlen($entry->id)));
				$stat->bindParam(':body', $c->body);
				$stat->bindParam(':rawBody', $c->rawBody);
				$stat->bindParam(':entry_id', $this->entry_cache[$entry->id]);
				$stat->bindParam(':user_id', $this->user_cache[$c->from->id]);
				$stat->bindParam(':date', $this->format_date($c->date));
				$stat->bindParam(':via', $c->via->name);

				$stat->execute();
			}
		}
	}

	function insert_likes($entry)
	{
		if(isset($entry->likes))
		{
			foreach($entry->likes as $l)
			{
				$this->check_user_and_add($l->from);

				$stat = $this->db->prepare(self::like_ins_qr);

				$stat->bindParam(':entry_id', $this->entry_cache[$entry->id]);
				$stat->bindParam(':user_id', $this->user_cache[$l->from->id]);
				$stat->bindParam(':date', $this->format_date($l->date));

				$stat->execute();
			}
		}
	}

	function insert_files($entry)
	{
		if(isset($entry->files))
		{
			foreach($entry->files as $file)
			{
				$stat = $this->db->prepare(self::file_ins_qr);

				$stat->bindParam(':url', $file->url);
				$stat->bindParam(':name', $file->name);
				$stat->bindParam(':entry_id', $this->entry_cache[$entry->id]);
				$stat->bindParam(':type', $file->type);
				$stat->bindParam(':size', $file->size);

				$stat->execute();
			}
		}
	}

	function insert_thumbnails($entry)
	{
		if(isset($entry->thumbnails))
		{
			foreach($entry->thumbnails as $t)
			{
				$stat = $this->db->prepare(self::thumbnails_ins_qr);

				$stat->bindParam(':entry_id', $this->entry_cache[$entry->id]);
				$stat->bindParam(':url', $t->url);
				$stat->bindParam(':link', $t->link);

				$stat->execute();
			}
		}
	}

	function insert_all($entry)
	{
		$this->insert_entry($entry);
		$this->insert_comments($entry);
		$this->insert_files($entry);
		$this->insert_thumbnails($entry);
		$this->insert_likes($entry);
	}

	function insert_if_changed($entries)
	{
		$num_of_changed = 0;
		foreach($entries as $f)
		{
			if(!isset($this->entry_cache[$f->id]))
			{
				$this->insert_all($f);
				$num_of_changed++;
			}
			else
			{
				//echo("$f->id - $f->rawLink is already in the DB: entry_id - " . $this->entry_cache[$f->id] . "\n");
				$is_changed = false;


				if(isset($f->comments))
				{
					$comments = $this->get_comments_of($f);

					if(sizeof($comments) < sizeof($f->comments))
					{
						// Only insert comments on change do not delete them
						$is_changed = true;
						echo("$f->id comms: " . sizeof($comments) . " != " . sizeof($f->comments) . "\n");
					}
				}

				if(!$is_changed && isset($f->likes))
				{
					$likes = $this->get_likes_of($f);

					if(sizeof($likes) < sizeof($f->likes))
					{
						// Only insert likes on change do not delete them
						$is_changed = true;
						echo("$f->id likes: " . sizeof($likes) . " != " . sizeof($f->likes) . "\n");
					}
				}

				if($is_changed)
				{
					$this->insert_all($f);
					$num_of_changed++;
				}
			}
		}
		return $num_of_changed;
	}

	function get_comments_of($entry)
	{
		$qr = 'SELECT * FROM comments WHERE entry_id = :entry_id;';

		$stat = $this->db->prepare($qr);
		$stat->bindParam(':entry_id', $this->entry_cache[$entry->id]);
		//$stat->bindParam(':entry_id', $this->entry_cache[$entry['id']]);

		$results = $stat->execute();

		return $this->convert_to_array($results);
	}

	function get_likes_of($entry)
	{
		$qr = 'SELECT * FROM likes WHERE entry_id = :entry_id;';

		$stat = $this->db->prepare($qr);
		$stat->bindParam(':entry_id', $this->entry_cache[$entry->id]);

		$results = $stat->execute();

		return $this->convert_to_array($results);
	}

	function get_sources()
	{
		$qr = 'SELECT * from sources';

		return $this->convert_to_array($this->db->query($qr));
	}

	function get_files()
	{
		$qr = 'SELECT * from files;';

		return $this->convert_to_array($this->db->query($qr));
	}

	private function convert_to_array($results)
	{
		$data = array();

		while($row = $results->fetchArray())
		{
			$data[] = $row;
		}

		return $data;
	}

	function set_source_id($source_id)
	{
		$this->source_id = $source_id;
	}

	function update_user_cache()
	{
		$qr = 'SELECT ff_id, id FROM users;';
		$results = $this->db->query($qr);
		while($row = $results->fetchArray()) {
			$this->user_cache[$row['ff_id']] = $row['id'];
		}
	}

	function update_entry_cache()
	{
		$qr = 'SELECT ff_id, id FROM entries;';
		$results = $this->db->query($qr);
		while($row = $results->fetchArray())
		{
			$this->entry_cache[$row['ff_id']] = $row['id'];
		}
	}
	function check_user_and_add($user)
	{
		if(!isset($this->user_cache[$user->id]))
		{
			$stat = $this->db->prepare(self::user_ins_qr);

			$stat->bindParam(':ff_id', $user->id, SQLITE3_TEXT);
			$stat->bindParam(':name', $user->name, SQLITE3_TEXT);
			$stat->bindValue(':private', isset($user->private), SQLITE3_INTEGER);

			$result = $stat->execute();
			$this->update_user_cache();
		}
	}

	function format_date($str)
	{
		$trans = array("T" => " ", "Z" => "");
		return trim(strtr($this->format($str), $trans));
	}
	function format($str)
	{
		$trans = array("\n" => " ", "\r" => " ");
		return trim(strtr($str, $trans));
	}
}


