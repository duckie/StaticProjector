<?php

abstract class sp_test
{
	private $files_to_check = array();
	
	abstract protected function private_run(array $iParameters);
	
	/**
	 * References a new file to be checked into the reference directory
	 * 
	 * @param string $iFileName To be valued to force the file name, which is not recommended
	 */
	public function create_ref_to_check($iFileName = "")
	{
		if(empty($iFileName))
			$iFileName = get_class($this)."_d".(count($this->files_to_check)+1).".txt";
			
		if( ! in_array($iFileName, $this -> files_to_check))
			array_push($this -> files_to_check, $iFileName);
		
		return $this -> output_dir()."/$iFileName";
	}
	
	/**
	 * Run the test with given parameters
	 * 
	 * @param array $iParameters
	 */
	public function run(array $iParameters)
	{
		$success = false;

		$output_dir = __DIR__."/output";
		if(!file_exists($output_dir))
			mkdir($output_dir);

		$success = $this -> private_run($iParameters);		
		if($success)
		{
			foreach($this -> files_to_check as $file)
			{
				$local_success = false;
				$ref = __DIR__."/ref/$file";
				$out = __DIR__."/output/$file";
				if(file_exists($ref) && file_exists($out))
				{
					$out_array = file($out, FILE_IGNORE_NEW_LINES);
					$ref_array = file($ref, FILE_IGNORE_NEW_LINES);
					$diff1 = array_diff($out_array,$ref_array);
					$diff2 = array_diff($ref_array,$out_array);
					$local_success = ($out_array == $ref_array);
				}
				
				$success =  $success && $local_success;
			}
		}
		
		return $success;
	}
	
	public function output_dir()
	{
		return __DIR__."/output";
	}
	
	public function root_dir()
	{
		return __DIR__."/../..";
	}
}