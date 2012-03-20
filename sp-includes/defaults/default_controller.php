<?php 

class default_controller extends sp_base_controller
{
	public function __construct($iSP, $iName)
	{
		parent::__construct($iSP, $iName);
	}
	
	public function get_menu()
	{
		$menu_data = null;
		$menu_file = sp_get_resource_path("menu.txt");
		if(file_exists($menu_file))
		{
			$menu_data = sp_ArrayUtils::parse_menu($menu_file);
		}
	}
	
	public function execute($iData)
	{
		$datas = array();
		
		// Adding the menu
		$datas["menu"] = $this -> get_menu();
		
		// Adding the content
		$resource = $iData[0];
		$file =  $this -> get_root() -> basedir()."/".sp_StaticProjector::data_dir."/".$resource;
		$info = sp_FileReader::get_file_info($file);
		if( "md" == $info -> extension)
		{
			$datas["type"] = "markdown";
			$datas["content"] = file_get_contents($file);
		}

		return $datas;
	}
}