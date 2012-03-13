<?php

class sp_utils_arrays_01 extends sp_test
{
	protected function private_run(array $iParameters)
	{
		$success = true;
		
		$array1 = array("elem1","elem3","elem2");
		$array2 = array("roger-maurice_le_sage.interets" => "\"martine et ses amis.\"","robert" => "ursula !!!", "gilbert" => "nicole|la\$garce");
		$array3 = array(array("roger" => 3, "marcel" => array(0,3,4)),"lulu" => true);

		$file_test = $this -> create_ref_to_check();
		$fp = fopen($file_test,'w');
		fwrite($fp, "compute_array_depth(Array3)=".sp_ArrayUtils::compute_array_depth($array3)."\n");
		fwrite($fp, "compute_array_depth(Array2)=".sp_ArrayUtils::compute_array_depth($array2)."\n");
		fwrite($fp, "is_multidimensional_array(Array3)=".(sp_ArrayUtils::is_multidimensional_array($array3) ? 1 : 0)."\n");
		fwrite($fp, "is_multidimensional_array(Array2)=".(sp_ArrayUtils::is_multidimensional_array($array2) ? 1 : 0)."\n");
		fclose($fp);
		
		$file_test = $this -> create_ref_to_check();
		sp_ArrayUtils::store_config($array1, $file_test);
		$array_result = sp_ArrayUtils::load_config($file_test);
		$success = $success && ($array_result == $array1);
		
		$file_test = $this -> create_ref_to_check();
		sp_ArrayUtils::store_config($array2, $file_test);
		$array_result = sp_ArrayUtils::load_config($file_test);
		$success = $success && ($array_result == $array2);
		
		$file_test = $this -> create_ref_to_check();
		$array_config = sp_ArrayUtils::load_config($this -> root_dir()."/sp-includes/defaults/".sp_StaticProjector::config_file);
		sp_ArrayUtils::store_config($array_config, $file_test);

		return $success;
	}
}