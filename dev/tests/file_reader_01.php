<?php

class FR01_SimpleVisitor extends sp_FileReaderVisitor
{
	private $fp = null;
	
	public function __construct($iFPOut, $iRepo)
	{
		$this -> fp = $iFPOut;
		$this -> basedir = $iRepo;
	}
	
	public function process(sp_FileInfo $iReader)
	{
		$info_array = $iReader -> as_array();
		
		// Deleting values which depend of the local computer
		unset($info_array['absolute_path']);
		unset($info_array['timestamp_modified']);
		unset($info_array['date_modified']);
		unset($info_array['exif_datetime']);
		
		fwrite($this -> fp, json_encode($info_array).'\n');
	}
}

class sp_file_reader_01 extends sp_test
{
	protected function private_run(array $iParameters)
	{
		$fp = fopen($this -> create_ref_to_check(),'w');
		$vis = new FR01_SimpleVisitor($fp, $iParameters["repo"]);
		$vis -> execute();
		fclose($fp);
		$status = true;
		
		return $status;
	}

}