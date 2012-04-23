<?php 

abstract class sp_base_controller
{
	private $sp;
	private $name;
	private $redirect = null;
	
	public function __construct(sp_StaticProjector $iSP,$iName)
	{
		$this -> sp = $iSP;
		$this -> name = $iName;
	}
	
	public function get_redirect()
	{
		return $this -> redirect;
	}
	
	protected function get_root()
	{
		return $this -> sp;
	}
	
	protected function get_name()
	{
		return $this -> name;
	}
	
	protected function redirect($iRequest)
	{
		$this -> redirect = $iRequest;
	}
	
	protected function redirect_to_notfound()
	{
		$this -> redirect = $this -> sp -> get_config() -> get_fail_route();
	}
	
	protected function query(sp_Criterion $iCriterion)
	{
		return $this -> get_root() -> resources() -> query_resources($iCriterion);
	}
	
	abstract public function execute($iData);
}