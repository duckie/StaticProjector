<?php

class sp_file_utils_01 extends sp_test
{
	protected function private_run(array $iParameters)
	{
		$success = false;
		
		$od = $this -> output_dir();
		
		mkdir("$od/test_timestamp/lvl1_dir1/lvl2_dir1",null,true);
		mkdir("$od/test_timestamp/lvl1_dir1/lvl2_dir2",null,true);
		mkdir("$od/test_timestamp/lvl1_dir2/lvl2_dir1",null,true);
		mkdir("$od/test_timestamp/lvl1_dir2/lvl2_dir2",null,true);
		
		$timestamp1 = sp_FileReader::get_directory_last_modified("$od/test_timestamp");
		//sleep(1);
		touch("$od/test_timestamp/lvl1_dir2/lvl2_dir1/troll_face.txt");
		$timestamp2 = sp_FileReader::get_directory_last_modified("$od/test_timestamp");
		
		$success = $timestamp1  <= $timestamp2;
		
		//Cleaning
		$deleter = new sp_RecursiveDeleter("$od/test_timestamp");
		$deleter -> execute();
		
		return $success;
	}
}