<?php 

class default_controller extends sp_base_controller
{
	public function __construct($iSP, $iName)
	{
		parent::__construct($iSP, $iName);
	}
	
	public function execute($iData)
	{
		$datas = array();
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