<?php

class sp_file_reader_03 extends sp_test
{
	protected function private_run(array $iParameters)
	{
		$success = false;
		$copier = new sp_RecursiveCopier($iParameters["src"], $iParameters["dst"]);
		$copier->execute();
		if(file_exists($iParameters["dst"]))
		{
			$deleter = new sp_RecursiveDeleter($iParameters["dst"]);
			$deleter->execute();
			$success = ! file_exists($iParameters["dst"]);
		}
		return $success;
	}
}