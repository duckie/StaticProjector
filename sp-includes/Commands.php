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
	private $cmd_dir;
	private $commands;
	
	public function  __construct(sp_StaticProjector $iSp)
	{
		$this -> sp = $iSp;
		$this -> commands = array('delete_me_to_regen_cache');
		$this -> cmd_dir = sp_file($this -> sp -> basedir(), sp_StaticProjector::remote_dir);
		if( ! is_dir($this -> cmd_dir))
		{
			mkdir($this -> cmd_dir,sp_StaticProjector::dir_create_rights,true);
			foreach ($this -> commands as $value)
			{
				$file = sp_file($cmd_dir, $value);
				file_put_contents($file,'');
				chmod($file,sp_StaticProjector::file_create_rights);
			}

		}
	}

	public function execute_commands()
	{ 
		$dir_reader = new sp_SimpleDirectoryContentList($this -> cmd_dir);
		$dir_reader -> execute();
		$list = $dir_reader -> get_list();
		$deleted_files = array_diff($this -> commands, $list);
	}

	private function delete_me_to_regen_cache()
	{

	}

}
