<?php

require_once(__DIR__."/Utils.php");
require_once(__DIR__."/FileReader.php");
require_once(__DIR__."/CacheGenerator.php");


class StaticProjector
{
	private $basedir;
	private $request;
	
	/**
	 * StaticProjector constructor
	 * 
	 * Creates a new StaticProjector instance based on
	 * $iBasedir root. Thus, you can manipulate as many instances as you want.
	 * 
	 * @param string $iBasedir
	 * @param string $iRequest
	 */
	public function __construct($iBasedir, $iRequest)
	{
		$this -> basedir = $iBasedir;
		$this -> request = $iRequest;
	}
	
	public function run()
	{
		$cache_gen = new CacheGenerator($this);
		$cache_gen -> run();
	}
	
	public function basedir()
	{
		return $this->basedir;
	}
}