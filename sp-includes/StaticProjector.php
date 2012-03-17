<?php

require_once(__DIR__."/Utils.php");
require_once(__DIR__."/Config.php");
require_once(__DIR__."/FileReader.php");
require_once(__DIR__."/CacheGenerator.php");
require_once(__DIR__."/Commands.php");
require_once(__DIR__."/Logger.php");


class sp_StaticProjector
{
	private $basedir;
	private $request;
	private $config;
	private $logger;
	
	const version = "0.1";
	const data_dir = "data";
	const user_cache_dir = "web-data/data";
	const config_dir = "web-data";
	const templates_dir = "web-data/templates";
	const cache_dir = "cache";
	const defaults_dir = "sp-includes/defaults";
	const config_file = "config.txt";
	const log_file = "log.txt";
	const file_order_name = "_sp_fileorder.txt";
	const file_metadata_ext = ".txt";
	const file_metadata_title_field = "title";
	const file_metadata_additional_fields = "template;link;alt;comment";
	const dic_file = "db.dico";
	const routes_file = "routes.txt";
	const routes_default_file = "routes.default.txt";
	
	/**
	 * StaticProjector constructor
	 * 
	 * Creates a new StaticProjector instance based on
	 * $iBasedir root. Thus, you can manipulate as many instances as you want.
	 * 
	 * @param string $iBasedir
	 * @param string $iRequest
	 */
	public function __construct($iBasedir, $iRequest)
	{
		$this -> basedir = $iBasedir;
		$this -> request = $iRequest;
		$this -> config = new sp_Config($this);
		$this -> logger = new sp_Logger($this);
	}
	
	public function get_config()
	{
		return $this -> config;
	}
	
	public function run()
	{
		// This function initializes everything at installation, does nothing otherwise
		$this -> config -> CheckAndRestoreEnvironment();
		
		// First thing to do before modifying anything : computing timestamp
		// Must be done before any call to log() cause log() may modify this state
		$cache_gen = new sp_CacheGenerator($this);
		$cache_gen -> check_current_filesystem_state();

		$this -> log(sp_Logger::info,"Static Projector execution began.");
		
		// Generating the caches
		$cache_gen -> run();
		
		$commands = new sp_Commands($this);
		$commands -> parse_request($this -> request);
		
		$this -> log(sp_Logger::info,"Static Projector execution ended.");
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
		if( sp_Config::with_log == $this -> config -> log_status() )
		{
			$this -> logger -> log($iLevel, $iMessage);
		}
	}
}