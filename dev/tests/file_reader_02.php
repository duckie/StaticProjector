<?php

class sp_file_reader_02 extends sp_test
{
	protected function private_run(array $iParameters)
	{
		$vis = new sp_SimpleDirectoryContentList($iParameters["dir"]);
		//$reader = new FileReader($vis);
		//$reader -> run();
		$file_list = $vis -> get_list();
		foreach($file_list as $file)
		{
			$this -> write($file."\n");
		}
		
		return true;
	}
}