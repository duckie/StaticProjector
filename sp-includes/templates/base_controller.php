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
	
	protected function gather_common_datas(array &$iArrayToStore)
	{
		$menu_file = sp_get_resource_path("menu.txt");
		if(file_exists($menu_file))
		{
			$iArrayToStore["menu"] = sp_ArrayUtils::parse_menu($menu_file);
		}
		
		$iArrayToStore["type"] = null;
		$iArrayToStore["content"] = null;
	}
	
	protected function query(sp_Criterion $iCriterion)
	{
		return $this -> get_root() -> resources() -> query_resources($iCriterion);
	}
	
	abstract public function execute($iData);
}