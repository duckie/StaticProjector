<?php

abstract class sp_test
{
	private $ref_name = null;
	private $out_name = null;
	private $out_fp = null;
	
	abstract protected function private_run(array $iParameters);
	
	/**
	 * Writes in the reference file for later comparison
	 * 
	 * @param unknown_type $iString
	 */
	public function write($iString)
	{
		fwrite($this -> out_fp, $iString);
	}
	
	/**
	 * Run the test with given parameters
	 * 
	 * @param array $iParameters
	 */
	public function run(array $iParameters)
	{
		$success = false;
		$check_ref = false;
		
		// Preparing output
		if(array_key_exists("ref", $iParameters))
		{
			$check_ref = true;
			$output_dir = __DIR__."/output";
			$ref_dir = __DIR__."/ref";
			if(!file_exists($output_dir))
				mkdir($output_dir);
			
			$this -> ref_name = $ref_dir."/".$iParameters["ref"];
			$this -> out_name = $output_dir."/".$iParameters["ref"];
			$this -> out_fp = fopen($this -> out_name,'w');
		}
		
		$test_success = $this -> private_run($iParameters);
		
		$success = $test_success;
		if($check_ref && $test_success)
		{
			fclose($this -> out_fp);
			$success = false;
			if(file_exists($this -> out_name) && file_exists($this -> ref_name))
			{
				$out_array = file($this -> out_name, FILE_IGNORE_NEW_LINES);
				$ref_array = file($this -> ref_name, FILE_IGNORE_NEW_LINES);
				$diff1 = array_diff($out_array,$ref_array);
				$diff2 = array_diff($ref_array,$out_array);
				$success = (0 == count($diff1) && 0 == count($diff2));
			}
		}
		
		return $success;
	}
	
	public function output_dir()
	{
		return __DIR__."/output";
	}
}