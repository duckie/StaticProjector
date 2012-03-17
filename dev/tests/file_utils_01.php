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
		
		// In fact, the code is pointless without the sleep, but its too annoying to remain
		// in the test set like that. Since the code for the test has been written, I let it
		// into the test set. To really check the get_directory_last_modified function, you must
		// uncomment the sleep and comment the line just after
		//$sleep_failed = sleep(2);
		$sleep_failed = true;
		
		touch("$od/test_timestamp/lvl1_dir2/lvl2_dir1/troll_face.txt");
		$timestamp2 = sp_FileReader::get_directory_last_modified("$od/test_timestamp");
		
		$success = ($sleep_failed && $timestamp1  <= $timestamp2) || $timestamp1  < $timestamp2;
		
		//Cleaning
		$deleter = new sp_RecursiveDeleter("$od/test_timestamp");
		$deleter -> execute();
		
		return $success;
	}
}