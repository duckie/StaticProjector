<?php

class sp_utils_arrays_01 extends sp_test
{
	protected function private_run(array $iParameters)
	{
		$array1 = array(array("roger" => 3, "marcel" => array(0,3,4)),"lulu" => true);
		$this -> write(sp_ArrayUtils::compute_array_depth($array1));
		return true;
	}
}