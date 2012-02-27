<?php

class FR01_SimpleVisitor extends FileReaderVisitor
{
	private $test = null;
	
	public function __construct($iTestObj, $iRepo)
	{
		$this -> test = $iTestObj;
		$this -> basedir = $iRepo;
	}
	
	public function process(FileInfo $iReader)
	{
		$info_array = $iReader -> as_array();
		
		// Deleting value depending on the local image
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
		//$info1 = FileReader::get_file_info(__DIR__."/testdata/repository1/images/list1/image_01.jpg", true);
		//$test = $info1 -> as_array();

		$vis = new FR01_SimpleVisitor($this, $iParameters["repo"]);
		$reader = new FileReader($vis);
		$reader -> run();
		$status = true;
		
		return $status;
	}

}