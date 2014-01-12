<?php

require_once(__DIR__.'/Utils.php');
require_once(__DIR__.'/Commands.php');
require_once(__DIR__.'/Config.php');
require_once(__DIR__.'/FileReader.php');
require_once(__DIR__.'/CacheGenerator.php');
require_once(__DIR__.'/Routes.php');
require_once(__DIR__.'/Logger.php');
require_once(__DIR__.'/Resources.php');
require_once(__DIR__.'/StaticRegister.php');
require_once(__DIR__.'/Templates.php');
require_once(__DIR__.'/templates/base_controller.php');
require_once(__DIR__.'/templates/base_template.php');

class sp_StaticProjector
{
	private $basedir;
	private $targetdir;
	private $baseurl;
	private $request;
	private $config;
	private $logger;
	private $resources;
	private $routes;
	private $commands;

	const version = '0.1'; 
	const data_dir = 'data';
	const remote_dir = 'cache/commands'; // Remote here is about the tool to send SP some commands, like a TV remote
	const user_cache_dir = 'metadata';
	const config_dir = 'config';
	const templates_dir = 'sp-local/templates';
	const style_dir = 'static/styles';
	const style_file = 'style.css';
	const cache_dir = 'cache';
	const webcache_dir = 'cache/web';
	const defaults_dir = 'defaults';
	const config_file = 'config.txt';
	const config_cache = 'config.dico';
	const log_file = 'log.txt';
	const cache_stamp_file = 'cache_stamp.dico';
	const file_order_name = '_sp_fileorder.txt';
	const file_metadata_ext = '.txt';
	const file_metadata_title_field = 'title';
	const file_metadata_create_field ='timestamp_created';
	const file_metadata_additional_fields = 'link;alt;comment';
	const dic_file = 'db.dico';
	const routes_file = 'routes.txt';
	const routes_default_file = 'routes.default.txt';
	const routes_dico = 'routes.dico';
	const exec_dir = __DIR__;
	const dir_create_rights = 0775;
	const file_create_rights = 0664;

	/**
	 * StaticProjector constructor
	 * 
	 * Creates a new StaticProjector instance based on
	 * $iBasedir root. Thus, you can manipulate as many instances as you want.
	 * 
	 * @param string $iBasedir
	 * @param string $iRequest
	 */
	public function __construct($iBasedir, $iTargetDir, $iBaseUrl, $iRequest)
	{
		$this -> basedir = str_replace("\\", "/", $iBasedir);
		$this -> targetdir = str_replace("\\", "/", $iTargetDir);
		$this -> baseurl = ('/' === substr($iBaseUrl, strlen($iBaseUrl)-1)) ? substr($iBaseUrl,0, strlen($iBaseUrl)-1) : $iBaseUrl;
		$this -> request = sp_filter_path(preg_replace('#^/?index\.php#', '', $iRequest));
		$this -> config = new sp_Config($this);
		$this -> logger = new sp_Logger($this);
		$this -> routes = new sp_Routes($this);
		$this -> resources = null;
		$this -> commands = null;
	}
	
	public function get_config()
	{
		return $this -> config;
	}
	
	public function targetdir()
	{
		return $this -> targetdir;
	}
	
	public function defaultsdir()
	{
		return sp_StaticProjector::exec_dir."/".sp_StaticProjector::defaults_dir;
	}
	
	public function coretemplatesdir()
	{
		return sp_StaticProjector::exec_dir."/templates";
	}
	
	public function resources()
	{
		return $this -> resources;
	}
	
	public function baseurl()
	{
		return $this -> baseurl;
	}
	
	public function run()
	{
		// This function initializes everything at installation, does nothing otherwise
		$this -> config -> CheckAndRestoreEnvironment();
		
		// Check the data dir is here
		$data_dir = $this -> basedir().'/'.sp_StaticProjector::data_dir;
		if( ! is_dir($data_dir)) $this -> log(sp_Logger::fatal, "Data dir $data_dir not found.");
		
		//set_include_path(get_include_path() . PATH_SEPARATOR . $this->basedir()."/".self::templates_dir);
		sp_StaticRegister::push_object("debug_state", $this -> config -> debug_enabled());
    $this -> logger -> set_debug_enabled($this-> config -> debug_enabled());

		// Executes the commands if configured to do so
		if($this -> config -> use_commands())
		{
			$this -> commands = new sp_Remote($this,$this -> config);
			$this -> commands -> execute_commands();
		}
		
		// First thing to do before modifying anything : computing timestamp
		// Must be done before any call to log() cause log() may modify this state
		$cache_gen = new sp_CacheGenerator($this);
		$this -> log(sp_Logger::info,"Static Projector execution began.");
		
		// Generating the caches
    sp_StaticRegister::push_object('sp', $this);
		$cache_gen -> run();
    sp_StaticRegister::pop_object('sp');
		
		// Loading resources
		$this -> resources = new sp_ResourceBrowser($this);
		
		// Rendering
		$this -> execute_request($this -> request);
		
		sp_StaticRegister::pop_object('debug_state');
		$this -> log(sp_Logger::info,'Static Projector execution ended.');
	}
	
	public function execute_request($iRequest)
	{
		$success = $this -> routes -> execute_request(sp_filter_path($iRequest));
		if(!$success) {
      $fail_route =  $this -> config -> get_fail_route();
      $this->log(sp_Logger::error,"{Main} Failed to route request '$iRequest', fall back to '$fail_route'");
			$success = $this -> routes -> execute_request($fail_route);
    }
		
		if($success)
		{
			$template = $this -> routes -> get_template();
			$renderer = new sp_Template($this, $this->config->fancy_urls_enabled(), $template);
				
			sp_StaticRegister::push_object('sp', $this);
			$data = $renderer -> render($this -> routes -> get_command_data());
			sp_StaticRegister::pop_object('sp');
		}
		else
		{
			$this -> log(sp_Logger::error, 'No route found for '.$this -> request.'.');
		}
	}
	
	public function basedir()
	{
		return $this->basedir;
	}
	
	/**
	 * Logs a message if logging is activated. Do nothing otherwise
	 * 
	 * @param int $iLevel
	 * @param string $iMessage
	 */
	public function log($iLevel, $iMessage)
	{
		if( sp_Config::with_log == $this -> config -> log_enabled() )
		{
			$this -> logger -> log($iLevel, $iMessage);
			if(sp_Logger::fatal == $iLevel)
			{
				exit();		
			}
		}
	}

  /**
  * Returns a list of logged message il debug si enabled(), null otherwise
  */
  public function log_list() {
		if( sp_Config::with_log == $this -> config -> log_enabled() ) {
			return $this -> logger -> log_list();
		}
    else {
      return null;
    }
  }
}
