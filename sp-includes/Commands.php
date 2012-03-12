<?php 

class sp_Commands
{
	private $sp;
	private $success = false;
	private $route_data = null;
	private $template_name = null;
	
	public function  __construct(sp_StaticProjector $iSp)
	{
		$this -> sp = $iSp;
	}
	
	public function parse_request($iRequest)
	{
		$this -> success = false;
		$this -> route_data = null;
		$this -> template_name = null;
		
		// Remodeling the request : add "/" to the beginning if missing and delete "/" at the end if so
		$request = preg_replace("#^/*([^/]*)((/[^/]+)*)(/*)$#", "/$1$2", $iRequest);
		if(empty($request)) $request = "/";
		
		$routes = file($this -> sp -> basedir()."/".sp_StaticProjector::config_dir."/".sp_StaticProjector::routes_file, FILE_IGNORE_NEW_LINES);
		foreach ($routes as $route_pattern)
		{
			if(preg_match("#^([^>\s]+)\s*->\s*([a-zA-Z0-9_\-]+)\s*\(([^\s]*)\)\s*$#",$route_pattern,$matches))
			{
				$route = $matches[1];
				$template = $matches[2];
				$replace_pattern = $matches[3];
				
				if(preg_match("#^$route$#",$request,$matches))
				{
					if(!empty($replace_pattern))
						$matches[0] = preg_replace("#^$route$#",$replace_pattern,$request);
					
					$this -> success = true;
					$this -> template_name = $template;
					$this -> route_data = $matches;
					break;
				}
			}
		}
		
		return $this -> succeeded();
	}
	
	public function succeeded()
	{
		return $this -> success;
	}
	
	public function get_command_data()
	{
		return $this -> route_data;
	}
	
	public function get_template()
	{
		return $this -> template_name;
	}
}