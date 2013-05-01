<?php

require_once('config.php');

class BookDB
{
	protected $db;
	public $types;

	function __construct()
	{
		$dbName = Config::bookdb_file;
		$this->db = new SQLite3($dbName);

		$this->types = $this->get_types();
	}

	function insert_a_book($author, $title, $filename, $type, $link, $source)
	{
		$qr = 'INSERT INTO books VALUES (NULL, :author, :title, :filename, :type, :link, :source, 1)';
		$stat = $this->db->prepare($qr);

		foreach(Array('author', 'title', 'filename', 'type', 'link', 'source') as $param)
		{
			$stat->bindParam(':' . $param, $$param);
		}

		$stat->execute();
	}

	function get_types()
	{
		$qr = 'SELECT * FROM types;';

		$res = $this->db->query($qr);

		$types = array();

		while($row = $res->fetchArray())
		{
			$types[$row['type']] = array(
				'id' => $row['id'],
				'humane' => $row['humane'],
				'extension' => $row['extension']);
		}
		return $types;
	}

	function get_languages()
	{
		$qr = 'SELECT * FROM languages;';

		$res = $this->db->query($qr);

		$languages = array();

		while($row = $res->fetchArray())
		{
			$languages[$row['short']] = $row['id'];
		}
		return $languages;
	}

	function get_filenames()
	{
		$qr = 'SELECT id, filename FROM books;';

		$res = $this->db->query($qr);

		$files = array();

		while($row = $res->fetchArray())
		{
			$files[$row['filename']] = $row['id'];
		}

		return $files;
	}

	function update_language($id, $language_id)
	{
		$qr = 'UPDATE books SET language_id = :language_id WHERE id = :id;';

		$stat = $this->db->prepare($qr);

		$stat->bindParam(':id', $id);
		$stat->bindParam(':language_id', $language_id);

		$stat->execute();
	}


	function begin()
	{
		$qr = 'BEGIN';
		$this->db->query($qr);
	}

	function end()
	{
		$qr = 'END';
		$update_count = $this->db->query($qr);
		return $update_count;

	}
}
