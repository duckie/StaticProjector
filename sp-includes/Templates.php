<?php 

require_once(__DIR__."/Markdown.php");

class sp_Template
{
	private $sp;
	private $config;
	private $enable_fancy_urls;
	private $name;
	private $loaded = false;
	
	public function __construct(sp_StaticProjector $iSP, $iFancyUrls, $iName)
	{
		$this -> sp = $iSP;
		$this -> enable_fancy_urls = $iFancyUrls;
		$this -> name = $iName;
	}
	
	public function fancy_urls_enabled()
	{
		return $this -> enable_fancy_urls;
	}

	/**
	 * This function searches for the given template and creates it if it does not exist
	 */
	private function load_template()
	{
		if(! $this -> loaded)
		{
			$controller = $this -> sp -> targetdir()."/".sp_StaticProjector::templates_dir."/".$this->name."_controller.php";
			sp_StaticRegister::push_object("sp", $this -> sp);
			
			if(!file_exists($controller))
			{
				if($this -> name == "default")
				{
					$default_controller = $this -> sp -> defaultsdir()."/default_controller.php";
					@copy($default_controller, $controller);
					chmod($controller,sp_StaticProjector::file_create_rights);
				}
				else
				{
					$base_code = file_get_contents($this -> sp -> defaultsdir()."/new_controller.txt");
					$controller_code = str_replace("%controller_name%", $this->name."_controller", $base_code);
					file_put_contents($controller, $controller_code);
				}
			}
			require_once($controller);

			$template = $this -> sp -> targetdir()."/".sp_StaticProjector::templates_dir."/".$this->name."_template.php";
			if(!file_exists($template))
			{
				if($this -> name == "default")
				{
					$default_template = $this -> sp -> defaultsdir()."/default_template.php";
					@copy($default_template, $template);
					chmod($template,sp_StaticProjector::file_create_rights);
				}
				else
				{
					$chunks_code = "";
					$chunk_base = file_get_contents($this -> sp -> defaultsdir()."/new_template_chunk.txt");
					foreach($this -> sp -> get_config() -> default_templates_chunks() as $chunk)
					{
						$chunks_code .= str_replace("%chunk_name%",$chunk,$chunk_base);
					}
					
					$template_base = file_get_contents($this -> sp -> defaultsdir()."/new_template.txt");
					$template_code = str_replace("%template_name%", $this -> name."_template", $template_base);
					$template_code = str_replace("%template_chunks%", $chunks_code, $template_code);
					file_put_contents($template, $template_code);
				}
			}
			require_once($template);
			sp_StaticRegister::pop_object("sp");
			
			$this -> loaded = true;
		}
	}
	
	public function load()
	{
		$this -> load_template();
	}
	
	public function render($iData)
	{
		sp_StaticRegister::push_object("renderer",$this);
		$this -> load_template();
		$controller_name = $this->name."_controller";
		$controller = new $controller_name($this -> sp, $this -> name);
		$result_data = $controller -> execute($iData);
		$redirect = $controller -> get_redirect();
		
		if(null === $redirect)
		{
			$template_name = $this->name."_template";
			$template = new $template_name();
			$template -> render_chunk("main", $result_data);
		}
		else
		{
			$this -> sp -> execute_request($redirect);
		}
		sp_StaticRegister::pop_object("renderer");
	}

}

function sp_require_template($iName)
{
	$sp = sp_StaticRegister::get_object("sp");
	$renderer = sp_StaticRegister::get_object("renderer");
	$template = new sp_Template($sp, $renderer -> fancy_urls_enabled(), $iName);
	$template -> load();
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

function sp_get_resource_path($iPartialPath)
{
	$sp = sp_StaticRegister::get_object("sp");
	return $sp -> resources() -> get_resource_path($iPartialPath);
}

function sp_markdown($iText)
{
	return Markdown($iText);
}

function sp_resource_url($iRequest)
{
	$sp = sp_StaticRegister::get_object("sp");
	$tmpl = sp_StaticRegister::get_object("renderer");
	#return ($tmpl -> fancy_urls_enabled() ? '' : $sp -> baseurl()).sp_filter_path($iRequest);	
	return ($tmpl -> fancy_urls_enabled() ? '' : $sp -> baseurl()).sp_filter_path($iRequest);	
}

function sp_link($iUrl, $iText, $iCSSClass = null)
{
	echo( (null === $iCSSClass) ? "<a href=\"$iUrl\">$iText</a>" : "<a class=\"$iCSSClass\" href=\"$iUrl\">$iText</a>" );
}

function sp_url($iRequest)
{
	$sp = sp_StaticRegister::get_object("sp");
	$tmpl = sp_StaticRegister::get_object("renderer");
	if(preg_match("#^https?:\/\/#", $iRequest))
		return $iRequest;
	else
		return ($tmpl -> fancy_urls_enabled() ? '' : $sp -> baseurl()."/?").sp_filter_path($iRequest);
}

