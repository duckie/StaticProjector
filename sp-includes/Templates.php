<?php 

class sp_Template
{
	private $sp;
	private $name;
	private $loaded = false;
	
	public function __construct(sp_StaticProjector $iSP, $iName)
	{
		$this -> sp = $iSP;
		$this -> name = $iName;
	}
	
	/**
	 * This function searches for the given template and creates it if it does not exist
	 */
	private function load_template()
	{
		if(! $this -> loaded)
		{
			$controller = $this -> sp -> basedir()."/".sp_StaticProjector::templates_dir."/".$this->name."_controller.php";
			if(!file_exists($controller))
			{
				if($this -> name == "default")
				{
					$default_controller = __DIR__."/defaults/default_controller.php";
					@copy($default_controller, $controller);
				}
				else
				{
					sp_assert(false); // Not implemented
				}
			}
			require($controller);

			$template = $this -> sp -> basedir()."/".sp_StaticProjector::templates_dir."/".$this->name."_template.php";
			if(!file_exists($template))
			{
				if($this -> name == "default")
				{
					$default_template = __DIR__."/defaults/default_template.php";
					@copy($default_template, $template);
				}
				else
				{
					sp_assert(false); // Not implemented
				}
			}
			require($template);
			$this -> loaded = true;
		}
	}
	
	public function render($iData)
	{
		$this -> load_template();
		$controller_name = $this->name."_controller";
		$controller = new $controller_name($this -> sp, $this -> name);
		$result_data = $controller -> execute($iData);
		
		$template_name = $this->name."_template";
		$template = new $template_name();
		$template -> render_chunk("main", $result_data);
	}

}

function sp_insert_chunk($iChunkName, $iData)
{
	$template = sp_StaticRegister::get_object("tmpl");
	$template -> render_chunk($iChunkName, $iData);
}

function sp_config_value($iKey)
{
	$sp = sp_StaticRegister::get_object("sp");
	return $sp -> get_config() -> get_value($iKey);
}

function sp_markdown($iText)
{
	return $iText;
}

