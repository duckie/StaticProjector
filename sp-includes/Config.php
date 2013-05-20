<?php 

/**
 * This class is in charge of managing global config of the project
 *
 * @author Jean-Bernard Jansen
 * @final
 */
class sp_Config
{
	private $sp = null;
	private $config_array = null;
	private $env_checked = false;
	private $config_loaded = false;
	
	private $cache_regen;
	private $debug_mode;
	private $log_activated;
	private $default_routes_activated;
	private $template_chunks;
	private $use_commands;
	private $fancy_urls;
	
	private $force_update = false;
	
	const cache_no_regen = 0;
	const cache_force_regen = 1;

	const no_debug = 0;
	const debug = 1;
	
	const no_log = 0;
	const with_log = 1;
	
	const no_default_routes = 0;
	const default_routes = 1;
	
	public function __construct(sp_StaticProjector $sp)
	{
		$this -> sp = $sp;
	}
	
	/**
	 * Creates the given directory. Delete the file having its name if exists before.
	 * 
	 * @param string $iDir
	 */
	private function mkdir($iDir)
	{
		$created = false;
		if(!is_dir($iDir))
		{
			if(file_exists($iDir))
			{
				unlink($iDir);
			}	
			mkdir($iDir,sp_StaticProjector::dir_create_rights,true);
			$created = true;
		}
		
		return $created;
	}
	
	/**
	 * Copies the source to the target if the target does not exist
	 *
	 * @param string $iSrc
	 * @param string $iDest
	 */
	private function copy_default_file($iSrc, $iDest)
	{
		$created = false;
		
		sp_assert(file_exists($iSrc));
		if( ! file_exists($iDest))
		{
			$cp = new sp_RecursiveCopier($iSrc, $iDest);
			$cp -> execute();
			$created = true;
		}
		
		return $created;
	}
	
	/**
	 * Creates what is lacking in the environment
	 * 
	 * This function checks for the existence of the user cache dir, the cache
	 * dir and the config file and creates them if needed. Does nothing otherwise.
	 */
	public function CheckAndRestoreEnvironment()
	{
		if( ! $this -> env_checked)
		{
			umask(0000);
			
			$uc_cache_created = $this -> mkdir($this->sp->targetdir().'/'.sp_StaticProjector::config_dir);
			$cache_created = $this -> mkdir($this->sp->targetdir().'/'.sp_StaticProjector::cache_dir);
      $cache_db_missing = !file_exists($this -> sp -> targetdir().'/'.sp_StaticProjector::cache_dir.'/'.sp_StaticProjector::dic_file);
			// If the folder has just been created, we need to force cache update
			if($uc_cache_created || $cache_created || $cache_db_missing)
			{
				$this -> force_update = true;
			}
			
			$this -> mkdir(sp_file($this->sp->targetdir(),sp_StaticProjector::user_cache_dir));
			$this -> mkdir(sp_file($this->sp->targetdir(),sp_StaticProjector::templates_dir));
			$this -> mkdir($this->sp->targetdir().'/'.sp_StaticProjector::style_dir);
			$this -> mkdir($this->sp->targetdir().'/'.sp_StaticProjector::webcache_dir);
			
			$default_config = $this -> sp -> defaultsdir().'/'.sp_StaticProjector::config_file;
			$dest_config = $this->sp->targetdir().'/'.sp_StaticProjector::config_dir.'/'.sp_StaticProjector::config_file;
			$this -> copy_default_file($default_config, $dest_config);
			
			$default_css = $this -> sp -> defaultsdir().'/'.sp_StaticProjector::style_file;
			$dest_css = $this->sp->targetdir().'/'.sp_StaticProjector::style_dir.'/'.sp_StaticProjector::style_file;
			$this -> copy_default_file($default_css, $dest_css);
			
			$default_route = $this -> sp -> defaultsdir().'/'.sp_StaticProjector::routes_file;
			$dest_route = $this->sp->targetdir().'/'.sp_StaticProjector::config_dir.'/'.sp_StaticProjector::routes_file;
			$this -> copy_default_file($default_route, $dest_route);
			
			if (! file_exists($this->sp->targetdir().'/'.sp_StaticProjector::cache_dir.'/.htaccess'))
				sp_set_http_granting($this->sp->targetdir().'/'.sp_StaticProjector::cache_dir, SP_HTTP_DENY_ACCESS);
			
			if (! file_exists($this->sp->targetdir().'/'.sp_StaticProjector::config_dir.'/.htaccess'))
				sp_set_http_granting($this->sp->targetdir().'/'.sp_StaticProjector::config_dir, SP_HTTP_DENY_ACCESS);
			
			if (! file_exists($this->sp->targetdir().'/'.sp_StaticProjector::user_cache_dir.'/.htaccess'))
				sp_set_http_granting($this->sp->targetdir().'/'.sp_StaticProjector::user_cache_dir, SP_HTTP_DENY_ACCESS);
			
			if (! file_exists($this->sp->targetdir().'/'.sp_StaticProjector::style_dir.'/.htaccess'))
				sp_set_http_granting($this->sp->targetdir().'/'.sp_StaticProjector::style_dir, SP_HTTP_DENY_LISTING);
			
			if (! file_exists($this->sp->targetdir().'/'.sp_StaticProjector::webcache_dir.'/.htaccess'))
				sp_set_http_granting($this->sp->targetdir().'/'.sp_StaticProjector::webcache_dir, SP_HTTP_DENY_LISTING);
			
			$this -> env_checked = true;
		}
	}
	
	private function LoadConfig()
	{
		sp_assert(__FILE__, __LINE__, $this -> env_checked);
		if( ! $this -> config_loaded)
		{
			$config_cache  = $this->sp->targetdir().'/'.sp_StaticProjector::cache_dir.'/'.sp_StaticProjector::config_cache;
			$config_cache_stamp = 0;
			if(file_exists($config_cache))
			{
				$config_cache_stamp = filemtime($config_cache);
				$this -> config_array = sp_ArrayUtils::load_array($config_cache);
			}
			
			$config_file = $this->sp->targetdir().'/'.sp_StaticProjector::config_dir.'/'.sp_StaticProjector::config_file;
			$config_stamp = filemtime($config_file);
			
			if($config_cache_stamp <= $config_stamp)
			{
				$default_config = sp_ArrayUtils::load_config($this -> sp -> defaultsdir().'/'.sp_StaticProjector::config_file);
				$config = sp_ArrayUtils::load_config($config_file);
				$this -> config_array = array_merge($default_config,$config);
				$dest_array = array();
				foreach($this -> config_array as $key => $value)
				{
					if('sp.' === substr($key, 0, 3))
					{
						$value = trim($value);
						if( 0 === strcasecmp($value,'Yes') || 0 === strcasecmp($value,'No'))
						{
							$value = (0 === strcasecmp($value,'Yes'));
						}
					}
					$dest_array[$key] = $value;
				}
				$this -> config_array = $dest_array;
				sp_ArrayUtils::store_array($this -> config_array, $config_cache);	
			}
			
			$config = $this -> config_array;
			
			// Time zone setting
			$timezone_valid = date_default_timezone_set($config['sp.timezone']);
			
			$this -> cache_regen = $config['sp.regen_cache'];
			$this -> debug_mode = $config['sp.debug'];
			$this -> log_activated = $config['sp.activate_log'];
			$this -> use_commands = $config['sp.use_commands'];			
			$this -> template_chunks = array_map('trim',explode(';',$config['sp.override_chunks']));
			$this -> fancy_urls = $config['sp.enable_fancy_urls'];

			$this -> config_loaded = true;
			$this -> sp -> log(sp_Logger::info,'Config file loaded.');
			if(!$timezone_valid)
			{
				$this -> sp -> log(sp_Logger::warning,'The time zone '.$config['sp.timezone'].' is invalid.');
			}
		}
	} 
	
	public function get_fail_route()
	{
		$this -> LoadConfig();
		return trim($this -> config_array['sp.notfound_route']);
	}
	
	
	public function debug_enabled()
	{
		$this -> LoadConfig();
		return $this -> debug_mode;
	}
	
	public function log_enabled()
	{
		$this -> LoadConfig();
		return $this -> log_activated;
	}
	
	public function cache_regen_request()
	{
		$this -> LoadConfig();
		if($this -> force_update)
			return true;
		else
			return $this -> cache_regen;
	}
	
	public function default_templates_chunks()
	{
		return $this -> template_chunks;		
	}

	public function fancy_urls_enabled()
	{
		return $this -> fancy_urls;
	}

	public function use_commands()
	{
		//$this -> LoadConfig();
		return $this -> use_commands;
	}
	
	public function get_value($iKey)
	{
		$this -> LoadConfig();
		return $this -> config_array["website.$iKey"];
	}

	public function set_cache_update($iValue)
	{
		$this -> cache_regen = $iValue ? self::cache_force_regen : self::cache_no_regen;
	}
}
