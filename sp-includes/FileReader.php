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
	public $timestamp_modified;
	public $date_modified; // Format YYYY-MM-DD-hh:mm:ss
	
	// Images
	public $exif_datetime;
	public $exif_title;
	public $exif_comment;
	public $exif_tags;
  public $width;
  public $height;	
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
	
	public function enter_directory(sp_FileInfo $iFileInfo)
	{}
	
	public function exit_directory(sp_FileInfo $iFileInfo)
	{}
	
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
		if( ! $iReader -> is_dir)
			unlink($iReader->absolute_path);
	}
	
	public function exit_directory(sp_FileInfo $info)
	{
		closedir(opendir($info->absolute_path)); // Windows issue with folders staying open
		rmdir($info->absolute_path);
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
	
	/**
	 * Get the most recent timestamp of all the directories contained into the given one
	 * 
	 * This function cound have been writtent with the generic file reader system
	 * (as for the recursive deleter for instance) but is independant to get the best
	 * performances, since it is heavily used when the cache generation is set
	 * to "auto"
	 * 
	 * @param string $iDirectory Path to the directory
	 * @param int $iRecurseLevel Recursion limit, default 100
	 * @param int $start Minimal timestamp : internal, not meant to be used
	 * @return int Timestamp
	 */
	static public function get_directory_last_modified($iDirectory, $iRecurseLevel = 100, $start = 0)
	{
		if(0 == $iRecurseLevel) return 0;
		$start = filemtime($iDirectory);
		if(is_dir($iDirectory))
		{
			$dp = opendir($iDirectory);
			readdir($dp); // "."
			readdir($dp); // '.."
			while($elem = readdir($dp))
			{
				$next = self::get_directory_last_modified("$iDirectory/$elem",$iRecurseLevel-1,$start);
				if($start < $next) $start = $next;
			}
			closedir($dp);
		}
		
		return $start;
	}
	
	/**
	 * Extracts some info about a file
	 * 
	 * 
	 * @param string $path
	 * @param bool $details Activate advanced info (currently: exif data)
	 */
	static public function get_file_info($path, $details = false)
	{
		if(!file_exists($path))
			return null;
		
		$local_info = new sp_FileInfo();
		$local_info -> is_dir = is_dir($path);
		$local_info -> relative_path = null;
		$local_info -> timestamp_modified = filemtime($path);
		
		if($details)
		{
			$local_info -> date_modified = date("Y-m-d-H:i:s", $local_info -> timestamp_modified);
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
			$local_info -> basename = isset($matches[1][0]) ? $matches[1][0] : "";
			$local_info -> extension = isset($matches[2][0]) ? $matches[2][0] : "";
			
      if(preg_match("/^[jJ][pP][eE]?[gG]$/", $local_info -> extension))
			{
        list($width_orig, $height_orig) = getimagesize($local_info->absolute_path);
        $local_info->width = $width_orig;
        $local_info->height = $height_orig;			

				// JPEG image got exifs info
        if($details)
				{
					$exif_data = exif_read_data($path,null,false);
					$local_info->exif_datetime = @$exif_data["FileDateTime"];
					$local_info->exif_title = @$exif_data["ImageDescription"];
					$local_info->exif_comment = @$exif_data["Comments"];
					$local_info->exif_tags =  @explode(";", $exif_data["Keywords"]);
				}
			}
			
		}
		
		return $local_info;
	}
	
	private function recursive_read($path, $max_iter)
	{ 
		$local_info = self::get_file_info($path, $this -> visitor -> with_details());
		if(null == $local_info) throw new ErrorException("The path ".$this->visitor->basedir()."has not been found.");
		$local_info -> relative_path = str_replace($this->visitor->basedir(), "", $local_info -> absolute_path);
		$this -> visitor -> process($local_info);
		if($local_info -> is_dir && 0 < $max_iter)
		{			
			$dir = dir($local_info -> absolute_path);
			$this -> visitor -> enter_directory($local_info);
			while (false !== ($entry = $dir->read()))
			{
				if("." !== $entry && ".." !== $entry)
				{
					$next_path = $local_info -> absolute_path."/".$entry;
          # Do what you want with this in your templates
					$this -> recursive_read($next_path, $max_iter - 1);
				}
			}
			$this -> visitor -> exit_directory($local_info);
		}		
	}
	
	public function run()
	{
		$this -> recursive_read($this -> visitor -> basedir(), $this->visitor->is_recursive() ? 100 : 1);
	}
	
	
}
