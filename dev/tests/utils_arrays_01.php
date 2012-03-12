<?php

class sp_utils_arrays_01 extends sp_test
{
	protected function private_run(array $iParameters)
	{
		$array1 = array(array("roger" => 3, "marcel" => array(0,3,4)),"lulu" => true);
		$array2 = array("roger" => "martine","robert" => "ursula", "gilbert" => "nicole");
		$array3 = array("elem1","elem3","elem2"); 
		
		$this -> write("compute_array_depth(Array1)=".sp_ArrayUtils::compute_array_depth($array1)."\n");
		$this -> write("compute_array_depth(Array2)=".sp_ArrayUtils::compute_array_depth($array2)."\n");
		$this -> write("is_multidimensional_array(Array1)=".(sp_ArrayUtils::is_multidimensional_array($array1) ? 1 : 0)."\n");
		$this -> write("is_multidimensional_array(Array2)=".(sp_ArrayUtils::is_multidimensional_array($array2) ? 1 : 0)."\n");
		
		return true;
	}
}