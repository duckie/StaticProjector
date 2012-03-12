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
		$this -> uc_dir = $this -> sp -> basedir()."/".sp_StaticProjector::user_cache_dir;
		$this -> conf_dir = $this -> sp -> basedir()."/".sp_StaticProjector::config_dir;
		$this -> cache_dir = $this -> sp -> basedir()."/".sp_StaticProjector::cache_dir;
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
		//{
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

			sp_ArrayUtils::dump_array($cache_list,$file_order_name);
		//}
		
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
		if(file_exists($cache_file) && is_dir($cache_file))
		{
			$del = new sp_RecursiveDeleter($cache_file);
			$del->execute();
		}
		
		// Adding rich data 
		if(!empty($info -> relative_path))
		{
			$meta_file = $cache_file.sp_StaticProjector::file_metadata_ext;
			$json_data = array();
			if(file_exists($meta_file))
			{
				$json_data = json_decode(file_get_contents($meta_file), true);
			}
			if(!array_key_exists(sp_StaticProjector::file_metadata_title_field, $json_data))
			{
				$json_data[sp_StaticProjector::file_metadata_title_field] = $info->basename;
			}
			foreach($this->meta_additional_fields as $field)
			{
				if(!array_key_exists($field,$json_data))
				{
					$json_data[$field]="";
				}
			}
			$json_string = json_encode($json_data);
			file_put_contents($meta_file, $json_string);
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
	
	private $current_file_stack = array();
	private $current_file_order = null;

	private $meta_additional_fields;

	public function __construct(sp_StaticProjector $iSP)
	{
		$this -> sp = $iSP;
		$this -> basedir = $this -> sp -> basedir()."/".sp_StaticProjector::data_dir;
		$this -> user_cache = $this -> sp -> basedir()."/".sp_StaticProjector::user_cache_dir;
		$this -> cache_dir = $this -> sp -> basedir()."/".sp_StaticProjector::cache_dir;
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
		if(! file_exists($file_order_file)) throw new ErrorException("The order file $file_order_file has not been found, check that the user cache is generated");
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
		$user_cache_file = $this -> user_cache.$info -> relative_path.sp_StaticProjector::file_metadata_ext;
		if(! file_exists($user_cache_file)) throw new ErrorException("The metadata file $user_cache_file has not been found, check that the user cache is generated");
		$user_cache_data = json_decode(file_get_contents($user_cache_file), true);
		$info_to_store = array_merge($info_to_store, $user_cache_data);
		array_push($this -> dic_array, $info_to_store);
	}
	
	public function execute()
	{
		if( ! file_exists($this -> cache_dir))
		{
			@mkdir($this -> cache_dir, null, true);
			sp_forbid_http_access($this -> cache_dir);
		}
		assert(is_dir($this -> cache_dir));
		parent::execute();
		sp_ArrayUtils::store_array($this -> dic_array, $this -> dic_file);
	}
}


class sp_CacheGenerator 
{
	private $sp;
	
	public function __construct(sp_StaticProjector $iSP)
	{
		$this -> sp = $iSP;
	}
	
	private function update_user_cache()
	{
		$user_cache = new sp_UserCacheGenerator($this->sp);
		$user_cache -> execute();
		
		$sp_cache = new sp_PrivateCacheGenerator($this->sp);
		$sp_cache -> execute();
	}
	
	private function generate_cache()
	{
		
	}
	
	public function run()
	{
		$this -> update_user_cache();
		$this -> generate_cache();
	}
}