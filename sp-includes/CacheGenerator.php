<?php

class sp_UserCacheGenerator extends sp_FileReaderVisitor
{
	private $sp;
	private $uc_dir;
	private $conf_dir;
	private $cache_dir;
	private $meta_additional_fields;
	private $last_update_stamp;
	private $computed_update_stamp = 0;
	
	private $current_file_stack = array();
	private $current_file_order = null;
	
	public function __construct(sp_StaticProjector $iSP, $iUCStamp)
	{
		$this -> sp = $iSP;
		$this -> basedir = $this -> sp -> basedir()."/".sp_StaticProjector::data_dir;
		$this -> uc_dir = $this -> sp -> targetdir()."/".sp_StaticProjector::user_cache_dir;
		$this -> conf_dir = $this -> sp -> targetdir()."/".sp_StaticProjector::config_dir;
		$this -> cache_dir = $this -> sp -> targetdir()."/".sp_StaticProjector::cache_dir;
		$this -> with_details = false;
		$this -> is_recursive = true;
		$this -> meta_additional_fields = explode(";", sp_StaticProjector::file_metadata_additional_fields);
		$this -> last_update_stamp = $iUCStamp;
	}
	
	public function enter_directory(sp_FileInfo $info)
	{
		if(null != $this -> current_file_order)
			array_push($this -> current_file_stack, $this -> current_file_order);
		
		$this -> current_file_order = array();
	}
	
	public function exit_directory(sp_FileInfo $info)
	{
		$cache_dir = $this -> uc_dir.$info -> relative_path;
		
		// Creating the folder if missing
		if(!is_dir($cache_dir))
		{
			// Deleting any occurence of a directory which was a file before
			if(file_exists($cache_dir)) unlink($cache_dir);
			@mkdir($cache_dir,sp_StaticProjector::dir_create_rights,true);
		}
		
		$file_order_name = $cache_dir."/".sp_StaticProjector::file_order_name;
		$local_stamp = max( file_exists($file_order_name) ? filemtime($file_order_name) : 0, $info -> timestamp_modified);
		
		if($this -> last_update_stamp < $local_stamp || ! file_exists($file_order_name))
		{
			$file_list = $this -> current_file_order;
			$cache_list = array();
			$previous_cache_list = array();
			if(file_exists($file_order_name))
			{
				$cache_list = sp_ArrayUtils::load_config($file_order_name);
				$previous_cache_list = $cache_list;
			
				// Search the files which do not exists anymore
				$not_existing_files = array_diff($cache_list,$file_list);
			
				// Delete those files from the cache
				foreach ($not_existing_files as $file)
				{
					$path = $cache_dir."/$file";
					if(file_exists($path))
					{
						if(is_dir($path))
						{
							$del = new sp_RecursiveDeleter($path);
							$del->execute();
						}
					}
					
					$meta = $path.sp_StaticProjector::file_metadata_ext;
					if(file_exists($meta))
					{
						// Also delete rich data
						$del = new sp_RecursiveDeleter($meta);
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
				$previous_cache_list = null;
				$cache_list = $file_list;
			}
			
			if($previous_cache_list !== $cache_list)
				sp_ArrayUtils::store_config(array_values($cache_list),$file_order_name);
		}
		
		$this -> computed_update_stamp = max($this -> computed_update_stamp, filemtime($file_order_name));
		$this -> current_file_order = array_pop($this -> current_file_stack);	
	}
	
	public function process(sp_FileInfo $info)
	{
		if(!empty($info -> relative_path))
		{
			$cache_file = $this -> uc_dir.$info -> relative_path;
			$meta_file = $cache_file.sp_StaticProjector::file_metadata_ext;

			if($this -> last_update_stamp < $info -> timestamp_modified || ! file_exists($meta_file))
			{
				// Deleting any occurence of a directory which is now a file
				if(file_exists($cache_file) && is_dir($cache_file) && ! $info -> is_dir)
				{
					$del = new sp_RecursiveDeleter($cache_file);
					$del->execute();
				}
					
				if($info -> is_dir)
				{
					$cache_dir = $this -> uc_dir.$info -> relative_path;
					// Creating the folder if missing
					if(!is_dir($cache_dir))
					{
						// Deleting any occurence of a directory which was a file before
						if(file_exists($cache_dir)) unlink($cache_dir);
						@mkdir($cache_dir,sp_StaticProjector::dir_create_rights,true);
					}
				}
					
				// Adding rich data
				if(!empty($info -> relative_path))
				{
					$meta_file = $cache_file.sp_StaticProjector::file_metadata_ext;
					$data = array();
						
					if(file_exists($meta_file))
					{
						$data = sp_ArrayUtils::load_config($meta_file);
					}
					$previous_data = $data;
						
					if(!array_key_exists(sp_StaticProjector::file_metadata_title_field, $data))
					{
						$data[sp_StaticProjector::file_metadata_title_field] = $info->basename;
					}
					if(!array_key_exists(sp_StaticProjector::file_metadata_create_field, $data))
					{
						//$data[sp_StaticProjector::file_metadata_create_field] = $info->timestamp_modified;
						$data[sp_StaticProjector::file_metadata_create_field] = time();
					}
					foreach($this->meta_additional_fields as $field)
					{
						if(!array_key_exists($field,$data))
						{
							$data[$field]="";
						}
					}
						
					if($previous_data !== $data)
						sp_ArrayUtils::store_config($data, $meta_file);
				}
			}

			$this -> computed_update_stamp = max($this -> computed_update_stamp, filemtime($meta_file));
			array_push($this -> current_file_order, $info -> name);
		}
	}
	
	public function execute()
	{	
		if( ! $this -> is_processed())
		{
			$this -> computed_update_stamp = $this -> last_update_stamp;
			parent::execute();
		}
	}
	
	public function get_update_stamp()
	{
		return $this -> computed_update_stamp;
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
		$this -> basedir = $this -> sp -> basedir().'/'.sp_StaticProjector::data_dir;
		$this -> user_cache = $this -> sp -> targetdir().'/'.sp_StaticProjector::user_cache_dir;
		$this -> cache_dir = $this -> sp -> targetdir().'/'.sp_StaticProjector::cache_dir;
		$this -> with_details = true;
		$this -> is_recursive = true;
		$this -> dic_file = $this -> cache_dir.'/'.sp_StaticProjector::dic_file;
		$this -> dic_array = array();
		$this -> current_file_order = null;
		$this -> meta_additional_fields = explode(';', sp_StaticProjector::file_metadata_additional_fields);
	}
	
	public function enter_directory(sp_FileInfo $info)
	{
		if(null != $this -> current_file_order)
			array_push($this -> current_file_stack, $this -> current_file_order);
		
		$file_order_file = $this -> user_cache.$info -> relative_path.'/'.sp_StaticProjector::file_order_name;
		sp_assert(file_exists($file_order_file));
		$this -> current_file_order = sp_ArrayUtils::load_config($file_order_file);
	}
	
	public function exit_directory(sp_FileInfo $info)
	{
		$this -> current_file_order = array_pop($this -> current_file_stack);
	}

	public function process(sp_FileInfo $info)
	{
		if(empty($info -> relative_path)) return; // Case of basedir folder
		$info_to_store = $info -> as_array();
		$info_to_store['order_index'] = array_search($info -> name, $this -> current_file_order);
		$info_to_store['id'] = $this -> id ++;
		$info_to_store['url'] = $this -> sp -> baseurl().'/'.sp_StaticProjector::data_dir.$info -> relative_path;
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
			$this -> debug = $this -> sp -> get_config() -> debug_enabled();
			if( ! file_exists($this -> cache_dir))
			{
				@mkdir($this -> cache_dir, sp_StaticProjector::dir_create_rights, true);
				sp_forbid_http_access($this -> cache_dir);
			}
			sp_assert(is_dir($this -> cache_dir));
			parent::execute();
			sp_ArrayUtils::store_array($this -> dic_array, $this -> dic_file, $this -> debug);
		}
	}
}


class sp_CacheGenerator 
{
	private $sp;
	private $uc_stamp = 0;
	private $cache_stamp = 0;
	private $routes_stamp = 0;
	
	public function __construct(sp_StaticProjector $iSP)
	{
		$this -> sp = $iSP;
	}
	
	private function update_user_cache()
	{		
		$user_cache = new sp_UserCacheGenerator($this->sp, $this -> uc_stamp);
		$user_cache -> execute();
		$this -> sp -> log(sp_Logger::info, "Intermediate cache (".sp_StaticProjector::config_dir.") updated.");
		$this -> uc_stamp = $user_cache -> get_update_stamp();
	}
	
	private function generate_cache()
	{
		$sp_cache = new sp_PrivateCacheGenerator($this->sp);
		$sp_cache -> execute();
		$this -> sp -> log(sp_Logger::info, "Cache updated.");
		$this -> cache_stamp = time();
	}
	
	public function run()
	{
		$cache_gen_policy = $this -> sp -> get_config() -> cache_regen_request();
		if($cache_gen_policy)
		{
			$stamp_data = array(0,0,0);
			$cache_stamp_file = $this -> sp -> targetdir().'/'.sp_StaticProjector::cache_dir.'/'.sp_StaticProjector::cache_stamp_file;
			if(file_exists($cache_stamp_file))
			{
				$stamp_data = sp_ArrayUtils::load_array($cache_stamp_file);
			}

			$this -> uc_stamp = $stamp_data[0];
			$this -> cache_stamp = $stamp_data[1];
			$this -> routes_stamp = $stamp_data[2];
			
			$this -> update_user_cache();
			if($this -> cache_stamp <= $this -> uc_stamp)
			{
				$this -> generate_cache();
			}

			$route_in = $this -> sp -> targetdir().'/'.sp_StaticProjector::config_dir.'/'.sp_StaticProjector::routes_file;
			$route_out = $this -> sp -> targetdir().'/'.sp_StaticProjector::cache_dir.'/'.sp_StaticProjector::routes_dico;
			$current_route_stamp = filemtime($route_in);
			
			if($this -> routes_stamp <= $current_route_stamp)
			{
				// Parsing routes
				$routes_data = array();
				$routes = file($this -> sp -> targetdir().'/'.sp_StaticProjector::config_dir.'/'.sp_StaticProjector::routes_file, FILE_IGNORE_NEW_LINES);
				foreach ($routes as $route_pattern)
				{
					if(preg_match('#^([^>\s]+)\s*->\s*([a-zA-Z0-9_\-]+)\s*\(([^\s]*)\)\s*$#',$route_pattern,$matches))
					{
						array_push($routes_data, array('route' => $matches[1], 'template' => $matches[2], 'replace_pattern' => $matches[3]));
					}
				}
				sp_ArrayUtils::store_array($routes_data, $this -> sp -> targetdir().'/'.sp_StaticProjector::cache_dir.'/'.sp_StaticProjector::routes_dico);
				$this -> routes_stamp = filemtime($route_out);
			}
			
			$new_stamp = array($this -> uc_stamp, $this -> cache_stamp, $this -> routes_stamp);
			if($stamp_data !== $new_stamp)
				sp_ArrayUtils::store_array($new_stamp, $cache_stamp_file);
		}
	}
}