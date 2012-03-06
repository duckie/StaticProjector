<?php

require_once(__DIR__."/Utils.php");
require_once(__DIR__."/FileReader.php");
require_once(__DIR__."/CacheGenerator.php");


class sp_StaticProjector
{
	private $basedir;
	private $request;
	
	const data_dir = "data";
	const user_cache_dir = "user-cache/data";
	const config_dir = "user-cache";
	const templates_dir = "user-cache/templates";
	const cache_dir = "cache";
	const admin_dir = "admin";
	
	const file_order_name = "_sp_fileorder.txt";
	const file_metadata_ext = ".json";
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
	}
	
	public function run()
	{
		$cache_gen = new sp_CacheGenerator($this);
		$cache_gen -> run();
		
		
		
	}
	
	public function basedir()
	{
		return $this->basedir;
	}
}