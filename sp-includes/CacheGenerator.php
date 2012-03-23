<?php

class sp_UserCacheGenerator extends sp_FileReaderVisitor
{
	private $sp;
	private $uc_dir;
	private $conf_dir;
	private $cache_dir;
	private $meta_additional_fields;
	private $routes;
	
	public function __construct(sp_StaticProjector $iSP)
	{
		$this -> sp = $iSP;
		$this -> basedir = $this -> sp -> basedir()."/".sp_StaticProjector::data_dir;
		$this -> uc_dir = $this -> sp -> targetdir()."/".sp_StaticProjector::user_cache_dir;
		$this -> conf_dir = $this -> sp -> targetdir()."/".sp_StaticProjector::config_dir;
		$this -> cache_dir = $this -> sp -> targetdir()."/".sp_StaticProjector::cache_dir;
		$this -> with_details = false;
		$this -> is_recursive = true;
		$this -> meta_additional_fields = explode(";", sp_StaticProjector::file_metadata_additional_fields);
		$this -> routes = array();
	}
	
	public function enter_directory(sp_FileInfo $info)
	{
		$cache_dir = $this -> uc_dir.$info -> relative_path;
		
		// Creating the folder if missing
		if(!is_dir($cache_dir))
		{
			// Deleting any occurence of a directory which was a file before
			if(file_exists($cache_dir))
				unlink($cache_dir);

			@mkdir($cache_dir,null,true);
		}

		// Handling file order lists update
		//{ // In fact PHP does not support anonymous blocks, lol
			$local_vis = new sp_SimpleDirectoryContentList($info -> absolute_path);
			$file_list = $local_vis -> get_list();
			$cache_list = array();
			$file_order_name = $cache_dir."/".sp_StaticProjector::file_order_name;
			if(file_exists($file_order_name))
			{
				$cache_list = file($file_order_name, FILE_IGNORE_NEW_LINES);
				// Search the files which do not exists anymore
				$not_existing_files = array_diff($cache_list,$file_list);

				// Delete those files from the cache
				foreach ($not_existing_files as $file)
				{
					$path = $cache_dir."/$file";
					if(file_exists($path))
					{
						$del = new sp_RecursiveDeleter($path);
						$del->execute();

						// Also delete rich data
						$del = new sp_RecursiveDeleter($path.sp_StaticProjector::file_metadata_ext);
						$del->execute();
					}
				}

				// Remove them from the list
				$cache_list = array_intersect($cache_list,$file_list);

				// Get the ones which are not in the list
				$new_files = array_diff($file_list,$cache_list);
				foreach ($new_files as $file)
				{
					array_push($cache_list, $file);
				}
			}
			else
			{
				$cache_list = $file_list;
			}

			sp_ArrayUtils::store_config($cache_list,$file_order_name);
		//} // lol
		
		// Adding route patterns
		if(empty($info -> relative_path))
			array_push($this -> routes, "/ -> default()");
		else
			array_push($this -> routes, $info -> relative_path."/([^/]+) -> default()");
	}
	
	public function process(sp_FileInfo $info)
	{
		$cache_file = $this -> uc_dir.$info -> relative_path;
		// Deleting any occurence of a directory which is now a file
		if(file_exists($cache_file) && is_dir($cache_file) && ! $info -> is_dir)
		{
			$del = new sp_RecursiveDeleter($cache_file);
			$del->execute();
		}
		
		// Adding rich data 
		if(!empty($info -> relative_path))
		{
			$meta_file = $cache_file.sp_StaticProjector::file_metadata_ext;
			$data = array();
			//$meta_file = "C:\\Documents and Settings\\jj4\\prog\\StaticProjector\\web-data\\data\\images.txt";
			if(file_exists($meta_file))
			{
				$data = sp_ArrayUtils::load_config($meta_file);
			}
			//$data = sp_ArrayUtils::load_config($meta_file);
			if(!array_key_exists(sp_StaticProjector::file_metadata_title_field, $data))
			{
				$data[sp_StaticProjector::file_metadata_title_field] = $info->basename;
			}
			foreach($this->meta_additional_fields as $field)
			{
				if(!array_key_exists($field,$data))
				{
					$data[$field]="";
				}
			}
			
			sp_ArrayUtils::store_config($data, $meta_file);
		}
		
		// Default route
		if(!empty($info -> relative_path))
			array_push($this -> routes, $info -> relative_path." -> default()");
	}
	
	public function execute()
	{	
		if( ! $this -> is_processed())
		{
			if(! file_exists($this -> cache_dir))
			{
				@mkdir($this -> cache_dir, null, true);
			}
			parent::execute();
			
			$route_file = $this -> conf_dir."/".sp_StaticProjector::routes_file;
			if(!file_exists($route_file))
			{
				touch($route_file);
			}
			if($this->sp->get_config()->default_routes_policy())
				sp_ArrayUtils::dump_array($this -> routes, $this -> cache_dir."/".sp_StaticProjector::routes_default_file);
			
			sp_set_http_granting($this -> conf_dir, SP_HTTP_DENY_ACCESS);
			sp_set_http_granting($this -> uc_dir, SP_HTTP_DENY_ACCESS);
		}
	}
}

class sp_PrivateCacheGenerator extends sp_FileReaderVisitor
{
	private $sp;
	private $user_cache;
	private $cache_dir;
	
	private $dic_file;
	private $dic_fp;
	private $dic_array;
	private $id = 0;
	
	private $current_file_stack = array();
	private $current_file_order = null;

	private $meta_additional_fields;
	private $debug = false;

	public function __construct(sp_StaticProjector $iSP)
	{
		$this -> sp = $iSP;
		$this -> basedir = $this -> sp -> basedir()."/".sp_StaticProjector::data_dir;
		$this -> user_cache = $this -> sp -> targetdir()."/".sp_StaticProjector::user_cache_dir;
		$this -> cache_dir = $this -> sp -> targetdir()."/".sp_StaticProjector::cache_dir;
		$this -> with_details = true;
		$this -> is_recursive = true;
		$this -> dic_file = $this -> cache_dir."/".sp_StaticProjector::dic_file;
		$this -> dic_array = array();
		$this -> current_file_order = null;
		$this -> meta_additional_fields = explode(";", sp_StaticProjector::file_metadata_additional_fields);
	}
	
	public function enter_directory(sp_FileInfo $info)
	{
		if(null != $this -> current_file_order)
			array_push($this -> current_file_stack, $this -> current_file_order);
		
		$file_order_file = $this -> user_cache.$info -> relative_path."/".sp_StaticProjector::file_order_name;
		sp_assert(file_exists($file_order_file));
		$this -> current_file_order = file($file_order_file, FILE_IGNORE_NEW_LINES);
	}
	
	public function exit_directory(sp_FileInfo $info)
	{
		$this -> current_file_order = array_pop($this -> current_file_stack);
	}

	public function process(sp_FileInfo $info)
	{
		if(empty($info -> relative_path)) return; // Case of basedir folder
		$info_to_store = $info -> as_array();
		$info_to_store["order_index"] = array_search($info -> name, $this -> current_file_order);
		$info_to_store["id"] = $this -> id ++;
		$info_to_store["url"] = $this -> sp -> baseurl()."/".sp_StaticProjector::data_dir.$info -> relative_path;
		$user_cache_file = $this -> user_cache.$info -> relative_path.sp_StaticProjector::file_metadata_ext;
		sp_assert(file_exists($user_cache_file));
		$user_cache_data = sp_ArrayUtils::load_config($user_cache_file);
		$info_to_store = array_merge($info_to_store, $user_cache_data);
		array_push($this -> dic_array, $info_to_store);
	}
	
	public function execute()
	{
		if( ! $this -> is_processed())
		{				
			$this -> debug = (sp_Config::debug == $this -> sp -> get_config() -> debug_mode());
			if( ! file_exists($this -> cache_dir))
			{
				@mkdir($this -> cache_dir, null, true);
				sp_forbid_http_access($this -> cache_dir);
			}
			sp_assert(is_dir($this -> cache_dir));
			parent::execute();
			sp_ArrayUtils::store_array($this -> dic_array, $this -> dic_file, $this -> debug);

			// Parsing routes
			$routes_data = array();
			$routes = file($this -> sp -> targetdir()."/".sp_StaticProjector::config_dir."/".sp_StaticProjector::routes_file, FILE_IGNORE_NEW_LINES);
			foreach ($routes as $route_pattern)
			{
				if(preg_match("#^([^>\s]+)\s*->\s*([a-zA-Z0-9_\-]+)\s*\(([^\s]*)\)\s*$#",$route_pattern,$matches))
				{
					array_push($routes_data, array("route" => $matches[1], "template" => $matches[2], "replace_pattern" => $matches[3]));
				}
			}
			sp_ArrayUtils::store_array($routes_data, $this -> sp -> targetdir()."/".sp_StaticProjector::cache_dir."/".sp_StaticProjector::routes_dico, $this -> debug);
		}
	}
}


class sp_CacheGenerator 
{
	private $sp;
	private $data_stamp = 0;
	private $uc_stamp = 0;
	private $cache_stamp = 0;
	
	public function __construct(sp_StaticProjector $iSP)
	{
		$this -> sp = $iSP;
	}
	
	public function check_current_filesystem_state()
	{
		$this -> data_stamp = sp_FileReader::get_directory_last_modified($this -> sp -> basedir()."/".sp_StaticProjector::data_dir);
		$this -> uc_stamp = sp_FileReader::get_directory_last_modified($this -> sp -> targetdir()."/".sp_StaticProjector::user_cache_dir);
		$this -> cache_stamp = sp_FileReader::get_directory_last_modified($this -> sp -> targetdir()."/".sp_StaticProjector::cache_dir);
	}
	
	private function update_user_cache()
	{		
		$user_cache = new sp_UserCacheGenerator($this->sp);
		$user_cache -> execute();
		$this -> sp -> log(sp_Logger::info, "Intermediate cache (".sp_StaticProjector::config_dir.") updated.");
	}
	
	private function generate_cache()
	{
		$sp_cache = new sp_PrivateCacheGenerator($this->sp);
		$sp_cache -> execute();
		$this -> sp -> log(sp_Logger::info, "Cache updated.");
	}
	
	public function run()
	{
		$cache_gen_policy = $this -> sp -> get_config() -> cache_policy();
		if(sp_Config::cache_no_regen != $cache_gen_policy)
		{
			$update_user_cache = ($this -> uc_stamp <= $this -> data_stamp);

			if($update_user_cache || sp_Config::cache_force_regen == $cache_gen_policy)
				$this -> update_user_cache();

			if( ($update_user_cache || $this -> cache_stamp <= $this -> uc_stamp) || sp_Config::cache_force_regen == $cache_gen_policy)
				$this -> generate_cache();
		}
	}
}