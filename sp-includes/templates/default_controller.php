<?php 

class default_controller extends sp_base_controller
{
	public function __construct($iSP, $iName)
	{
		parent::__construct($iSP, $iName);
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
	
	public function execute($iData)
	{
		$datas = array();
		
		// Adding the menu
		$this -> gather_common_datas($datas);
		
		// Adding the content
		$resource = $iData[0];
		$file =  $this -> get_root() -> basedir()."/".sp_StaticProjector::data_dir."/".$resource;
		if(file_exists($file))
		{
			$info = sp_FileReader::get_file_info($file);
			if( "md" == $info -> extension)
			{
				$datas["type"] = "markdown";
				$datas["content"] = file_get_contents($file);
			}
		}

		return $datas;
	}
}