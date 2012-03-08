<?php 

class sp_Commands
{
	private $sp;
	
	public function  __construct(sp_StaticProjector $iSp)
	{
		$this -> sp = $iSp;
	}
	
	public function parse_request($iRequest)
	{
		$routes = file($this -> sp -> basedir()."/".sp_StaticProjector::config_dir."/".sp_StaticProjector::routes_file, FILE_IGNORE_NEW_LINES);
		foreach ($routes as $route_pattern)
		{
			echo($route_pattern."\n");
			if(preg_match("#^([^>]+)\s*->\s*([a-zA-Z0-9_-]+)\s*\((.*)\)\s*$#",$route_pattern,$matches))
			{
				print_r($matches);
			}
		}
	}
}