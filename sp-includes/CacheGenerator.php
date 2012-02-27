<?php

class sp_UserCacheGenerator extends sp_FileReaderVisitor
{
	private $sp;
	private $uc_dir;
	
	private $meta_additional_fields;
	
	public function __construct(sp_StaticProjector $iSP)
	{
		$this -> sp = $iSP;
		$this -> basedir = $this -> sp -> basedir()."/".sp_StaticProjector::data_dir;
		$this -> uc_dir = $this -> sp -> basedir()."/".sp_StaticProjector::user_cache_dir;
		$this -> with_details = false;
		$this -> is_recursive = true;
		
		$this -> meta_additional_fields = explode(";", sp_StaticProjector::file_metadata_additional_fields);
	}
	
	public function process(sp_FileInfo $info)
	{
		$cache_file = $this -> uc_dir.$info -> relative_path;
		if($info -> is_dir)
		{
			// Create the cache dir if does not exists
			$cache_dir = $cache_file;
			if(!is_dir($cache_dir))
			{
				if(file_exists($cache_dir))
					unlink($cache_dir);
				
				mkdir($cache_dir);
			}

			// Handling file order lists update
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
			
			$fp = fopen($file_order_name,'w');
			foreach($cache_list as $file)
				fwrite($fp,"$file\n");
			
			fclose($fp);
			//$local_reader = new FileReader($local_vis);
		}
		else
		{
			// Deleting any occurence of a directory which is now a file
			if(file_exists($cache_file))
			{
				$del = new sp_RecursiveDeleter($cache_file);
				$del->execute();
			}
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