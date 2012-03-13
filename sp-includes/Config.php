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
	
	const cache_no_regen = 0;
	const cache_auto_regen = 1;
	const cache_force_regen = 2;

	const no_debug = 0;
	const debug = 1;
	
	const no_log = 0;
	const with_log = 1;
	
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
		if(!is_dir($iDir))
		{
			if(file_exists($iDir))
				unlink($iDir);
			else
				mkdir($iDir,null,true);
		}
	}
	
	/**
	 * Copies the source to the target if the target does not exist
	 *
	 * @param string $iSrc
	 * @param string $iDest
	 */
	private function copy_default_dile($iSrc, $iDest)
	{
		sp_assert(file_exists($iSrc));
		
		if(file_exists($iDest))
		{
			if(is_dir($iDest) && ! is_dir($iSrc))
			{
				$del = new sp_RecursiveDeleter($iDest);
				$del -> execute();
			}
		}
		else
		{
			$cp = new sp_RecursiveCopier($iSrc, $iDest);
			$cp -> execute();
		}
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
			$this -> mkdir($this->sp->basedir()."/".sp_StaticProjector::config_dir);
			$this -> mkdir($this->sp->basedir()."/".sp_StaticProjector::user_cache_dir);
			$this -> mkdir($this->sp->basedir()."/".sp_StaticProjector::templates_dir);
			$this -> mkdir($this->sp->basedir()."/".sp_StaticProjector::cache_dir);

			$default_config = $this->sp->basedir()."/".sp_StaticProjector::defaults_dir."/".sp_StaticProjector::config_file;
			$dest_config = $this->sp->basedir()."/".sp_StaticProjector::config_dir."/".sp_StaticProjector::config_file;
			$this -> copy_default_dile($default_config, $dest_config);

			$this -> env_checked = true;
		}
	}
	
	private function LoadConfig()
	{
		sp_assert($this -> env_checked);
		if( ! $this -> config_loaded)
		{
			$this -> config_array = sp_ArrayUtils::load_config( $this->sp->basedir()."/".sp_StaticProjector::config_dir."/".sp_StaticProjector::config_file );
			$config = array_map("trim", $this -> config_array);
			
			if(0 == strcasecmp($config["sp.regen_cache"],"No"))
				$this -> cache_regen = self::cache_no_regen;
			else if(0 == strcasecmp($config["sp.regen_cache"],"Auto"))
				$this -> cache_regen = self::cache_auto_regen;
			else
				$this -> cache_regen = self::cache_force_regen;
			
			$this -> debug_mode = (0 == strcasecmp($config["sp.debug"], "Yes")) ? self::debug : self::no_debug;
			$this -> log_activated = (0 == strcasecmp($config["sp.activate_log"],"Yes")) ? self::with_log : self::no_log;

			$this -> config_loaded = true;
		}
	} 
	
	
	public function debug_mode()
	{
		LoadConfig();
		return $this -> debug_mode;
	}
	
	public function log_status()
	{
		LoadConfig();
		return $this -> log_activated;
	}
	
	public function cache_policy()
	{
		LoadConfig();
		return $this -> cache_regen;
	}
	
	public function get_value($iKey)
	{
		LoadConfig();
		return $this -> config_array["website.$iKey"];
	}
}