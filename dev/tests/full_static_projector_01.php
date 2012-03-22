<?php



class sp_full_static_projector_01 extends sp_test
{
	protected function private_run(array $iParameters)
	{
		$output = $this -> output_dir();
		
		$target_dir = "$output/sp_target_01/";
		if(file_exists($target_dir))
		{
			$cleaner = new sp_RecursiveDeleter($target_dir);
			$cleaner -> execute();
		}
		@mkdir($target_dir,null,true);
		
		
		ob_start();
		$sp = new sp_StaticProjector(__DIR__."/data/repository4", $target_dir, "http://localhost", "/");
		$sp -> run();
		$html = ob_get_clean();
		
		file_put_contents($this -> create_ref_to_check(), $html);
		
		return true;
	}

}