<?php

class sp_FileInfo extends sp_ArrayConvertible
{
	// Basic
	public $absolute_path;
	public $relative_path; // Relative to the dirctory from which the reader has been ran
	public $name;
	public $basename;
	public $extension;
	public $is_dir;
	
	// Rich
	public $last_modified_timestamp;
	public $last_modified_date; // Format YYYY-MM-DD-hh:mm:ss
	
	// Images
	public $exif_datetime;
	public $exif_title;
	public $exif_comment;
	public $exif_tags;	
}

abstract class sp_FileReaderVisitor
{
	private $processed = false;
	
	protected $basedir = ".";
	protected $is_recursive = true;
	protected $with_details = true;
	//protected $path_excludes = array(); // List or regex pocessed on full path
	//protected $file_excludes = array(); // List of regex processed on basename
	//protected $filter = "";
	
	public function basedir() { return $this -> basedir; }
	public function is_recursive() { return $this -> is_recursive; }
	public function with_details() { return $this -> with_details; }
	public function is_processed() { return $this -> processed; }
	//public function path_excludes() { return $this -> path_excludes; }
	//public function file_excludes() { return $this -> file_excludes; }
	
	public function execute()
	{
		if(! $this->processed)
		{
			$local_reader = new sp_FileReader($this);
		 	$local_reader -> run();
		 	$this->processed = true;
		}
	}
	
	abstract public function process(sp_FileInfo $iFileInfo);
}

class sp_SimpleDirectoryContentList extends sp_FileReaderVisitor
{
	private $name_list;
	
	public function __construct($iDirectory)
	{
		$this -> basedir = $iDirectory;
		$this -> is_recursive = false;
		$this -> with_details = false;
		$this -> name_list = array();
	}

	public function process(sp_FileInfo $iReader)
	{
		array_push($this -> name_list, $iReader -> name);
	}
	
	public function execute()
	{
		if(! $this -> is_processed())
		{
			parent::execute();
			array_shift($this->name_list);
		}
	}
	
	public function get_list()
	{
		$this -> execute();
		return $this->name_list;
	}
}

class sp_RecursiveCopier extends sp_FileReaderVisitor
{
	private $dst;
	
	public function __construct($iSrc, $iDst)
	{
		$this -> basedir = $iSrc;
		$this -> dst = $iDst;		
		$this -> with_details = false;
	}
	
	public function execute()
	{
		if(!file_exists($this->dst) && is_dir($this->basedir))
			mkdir($this->dst);
		parent::execute();
	}

	public function process(sp_FileInfo $iReader)
	{
		if($iReader -> is_dir)
			@mkdir($this->dst.$iReader->relative_path);
		else
			@copy($this->basedir.$iReader->relative_path , $this->dst.$iReader->relative_path);
	}
}

class sp_RecursiveDeleter extends sp_FileReaderVisitor
{
	private $name_list;
	
	public function __construct($iFile)
	{
		$this -> basedir = $iFile;
		$this -> with_details = false;
		$this -> name_list = array();
	}
	
	public function process(sp_FileInfo $iReader)
	{
		if($iReader -> is_dir)
			array_push($this -> name_list, $iReader->absolute_path);
		else
			unlink($iReader->absolute_path);
	}
	
	public function execute()
	{
		if(! $this -> is_processed())
		{
			parent::execute();
			$list_to_delete = array_reverse($this -> name_list);
			foreach($list_to_delete as $path)
			{
				closedir(opendir($path)); // Windows issue with folders staying open
				rmdir($path);
			}
		}
	}
}

class sp_FileReader
{
	private $visitor;
	private $details;
	
	/**
	 * Cretes a FileReader instance
	 * 
	 * Needs a visitor which contains the parameters
	 * and processes the result
	 * 
	 * @param FileReaderVisitor $iVisitor
	 */
	public function __construct(sp_FileReaderVisitor $iVisitor)
	{
		$this -> visitor = $iVisitor;
	}
	
	static public function get_file_info($path, $details = false)
	{
		if(!file_exists($path))
			return null;
		
		$local_info = new sp_FileInfo();
		$local_info -> is_dir = is_dir($path);
		$local_info -> relative_path = null;
		
		if($details)
		{
			$local_info -> last_modified_timestamp = filemtime($path);
			$local_info -> last_modified_date = date("Y-m-d-H:i:s", $local_info -> last_modified_timestamp);
		}
		
		if($local_info -> is_dir)
		{
			$local_path = $path;
			$last_char = substr($path,strlen($path)-1);
			if("/" == $last_char || "\\" == $last_char)
				$local_path = substr($path,0,strlen($path)-1);
			
			$local_info -> name = basename($local_path);
			$local_info -> basename = basename($local_path);
			$local_info -> absolute_path = $local_path;
		}
		else
		{
			$local_info -> absolute_path = $path;
			$local_info -> name = basename($path);
			
			preg_match_all("/^(.+)\.([a-zA-Z0-9]+)$/", $local_info -> name, $matches);
			$local_info -> basename = $matches[1][0];
			$local_info -> extension = $matches[2][0];
			
			if($details)
			{
				// JPEG image got exifs info
				if(preg_match("/^[jJ][pP][eE]?[gG]$/", $local_info -> extension))
				{
					$exif_data =  exif_read_data($path,null,false);
					$local_info -> exif_datetime = $exif_data["FileDateTime"];
					$local_info -> exif_title = $exif_data["ImageDescription"];
					$local_info -> exif_comment = $exif_data["Comments"];
					$local_info -> exif_tags = explode(";", $exif_data["Keywords"]);
				}
			}
			
		}
		
		return $local_info;
	}
	
	private function recursive_read($path, $max_iter)
	{
		$local_info = self::get_file_info($path, $this -> visitor -> with_details());
		$local_info -> relative_path = str_replace($this->visitor->basedir(), "", $local_info -> absolute_path);
		$this -> visitor -> process($local_info);
		if($local_info -> is_dir && 0 < $max_iter)
		{			
			$dir = dir($local_info -> absolute_path);
			while (false !== ($entry = $dir->read()))
			{
				if("." !== $entry && ".." !== $entry)
				{
					$next_path = $local_info -> absolute_path."/".$entry;
					$this -> recursive_read($next_path, $max_iter - 1);
				}

			}
		}		
	}
	
	public function run()
	{
		$this -> recursive_read($this -> visitor -> basedir(), $this->visitor->is_recursive() ? 100 : 1);
	}
	
	
}