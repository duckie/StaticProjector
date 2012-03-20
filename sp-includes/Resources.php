<?php 


class sp_ResourceBrowser
{
	private $sp;
	
	public function __construct(sp_StaticProjector $iSP)
	{
		$this -> sp = $iSP;
	}
	
	public function get_resource_path($iPartialPath)
	{
		$path = sp_filter_path($iPartialPath);
		return $this -> sp -> basedir()."/".sp_StaticProjector::data_dir.$path;
	}
}