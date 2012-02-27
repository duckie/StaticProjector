<?php

class CacheGenerator 
{
	private $sp;
	
	public function __construct(StaticProjector $iSP)
	{
		$this -> sp = $iSP;
	}
	
	private function update_user_cache()
	{
		
	}
	
	private function generate_cache()
	{
		
	}
	
	public function run()
	{
		$this -> update_user_cache();
		$this -> generate_cache();
	}
}