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
			$cp = new sp_RecursiveCopier($iSrc, $iDst);
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
		$this -> mkdir($this->sp->basedir()."/".sp_StaticProjector::config_dir);
		$this -> mkdir($this->sp->basedir()."/".sp_StaticProjector::user_cache_dir);
		$this -> mkdir($this->sp->basedir()."/".sp_StaticProjector::templates_dir);
		$this -> mkdir($this->sp->basedir()."/".sp_StaticProjector::cache_dir);
		
		$default_config = $this->sp->basedir()."/".sp_StaticProjector::defaults_dir."/".sp_StaticProjector::config_file;
		$dest_config = $this->sp->basedir()."/".sp_StaticProjector::config_dir."/".sp_StaticProjector::config_file;
		$this -> copy_default_dile($default_config, $dest_config);
	}
	
	private function LoadConfig()
	{
		if( ! $this -> config_loaded)
		{
			// Load this damn file !
			$this -> config_loaded = true;
		}
	} 
	
	//public function Get
}