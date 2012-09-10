<?php

/**
* The remote class enables the user to make some maintenance operations
* 
* The idea is that a directory contains some files named in a particular way
* for instance, if you delete the file "delete_me_to_regen_cache", then StaticProjector
* forces cache regen on the first load then re-creates this file.
*
* Supported commands:
* - Cache regen
*/
class sp_Remote
{
	private $sp;
	private $config;
	private $cmd_dir;
	private $commands;
	
	public function  __construct(sp_StaticProjector $iSp, sp_Config $iConfig)
	{
		$this -> sp = $iSp;
		$this -> config = $iConfig;
		$this -> commands = array('delete_me_to_regen_cache');
		$this -> cmd_dir = sp_file($this -> sp -> basedir(), sp_StaticProjector::remote_dir);
		if( ! is_dir($this -> cmd_dir))
		{
			mkdir($this -> cmd_dir,sp_StaticProjector::dir_create_rights,true);
			foreach ($this -> commands as $file)
			{
				$file_path = sp_file($cmd_dir, $file);
				file_put_contents($file_path,'# Nothing to do here');
				chmod($file_path,sp_StaticProjector::file_create_rights);
			}

		}
	}

	public function execute_commands()
	{ 
		$dir_reader = new sp_SimpleDirectoryContentList($this -> cmd_dir);
		$dir_reader -> execute();
		$list = $dir_reader -> get_list();
		$deleted_files = array_diff($this -> commands, $list);
		$commands_to_execute = array();
		foreach($deleted_files as $file)
		{
			if(preg_match("#^delete_me_to#", $file))
			{
				$file_path = sp_file($this -> cmd_dir,$file);
				array_push($commands_to_execute, $file);
				file_put_contents($file_path,'# Nothing to do here');
				chmod($file_path,sp_StaticProjector::file_create_rights);
			}
		}
		foreach($commands_to_execute as $command)
		{
			$this -> $command();
		}
	}

	private function delete_me_to_regen_cache()
	{
		$this -> config -> set_cache_update(true);
	}

}
