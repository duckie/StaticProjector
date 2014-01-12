<?php 

class sp_Routes
{
	private $sp;
	private $success = false;
	private $route_data = null;
	private $template_name = null;
	
	public function  __construct(sp_StaticProjector $iSp)
	{
		$this -> sp = $iSp;
	}
	
	public function execute_request($iRequest)
	{
    $this -> sp -> log(sp_Logger::info, "{Routes} Parsing request '$iRequest'.");
		$this -> success = false;
		$this -> route_data = null;
		$this -> template_name = null;
		
		// Remodeling the request : add "/" to the beginning if missing and delete "/" at the end if so
		$request = sp_filter_path($iRequest);
		
		//preg_replace("#^/*([^/]*)((/[^/]+)*)(/*)$#", "/$1$2", $iRequest);
		//if(empty($request)) $request = "/";
		
		$routes = sp_ArrayUtils::load_array($this -> sp -> targetdir().'/'.sp_StaticProjector::cache_dir.'/'.sp_StaticProjector::routes_dico);
		foreach ($routes as $route_data)
		{
			$route = $route_data['route'];
			$template = $route_data['template'];
			$replace_pattern = $route_data['replace_pattern'];
			
			$matches = array();

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
