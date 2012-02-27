<?php

class FR01_SimpleVisitor extends sp_FileReaderVisitor
{
	private $test = null;
	
	public function __construct($iTestObj, $iRepo)
	{
		$this -> test = $iTestObj;
		$this -> basedir = $iRepo;
	}
	
	public function process(sp_FileInfo $iReader)
	{
		$info_array = $iReader -> as_array();
		
		// Deleting values which depend of the local computer
		unset($info_array["absolute_path"]);
		unset($info_array["last_modified_timestamp"]);
		unset($info_array["last_modified_date"]);
		unset($info_array["exif_datetime"]);
		
		$this -> test -> write(json_encode($info_array)."\n");
	}
}

class sp_file_reader_01 extends sp_test
{
	protected function private_run(array $iParameters)
	{
		$vis = new FR01_SimpleVisitor($this, $iParameters["repo"]);
		$vis -> execute();
		$status = true;
		
		return $status;
	}

}