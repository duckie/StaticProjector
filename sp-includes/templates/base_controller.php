<?php 

abstract class sp_base_controller
{
	private $sp;
	private $name;
	
	public function __construct($iSP,$iName)
	{
		$this -> sp = $iSP;
		$this -> name = $iName;
	}
	
	protected function get_root()
	{
		return $this -> sp;
	}
	
	protected function get_name()
	{
		return $this -> name;
	}
	
	abstract public function execute($iData);
}