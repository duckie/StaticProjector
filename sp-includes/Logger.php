<?php 

/**
 * Basic logger class, only used for debug purpose
 * 
 * See the config file to activate logging
 * 
 * @author Jean-Bernard Jansen
 * @final
 */
class sp_Logger
{
	private $sp = null;
	private $fp = 0;
	private $tag_mapper = array("[info] ", "[warning] ", "[error] ", "[fatal] ");
	
	
	const info = 0;
	const warning = 1;
	const error = 2;
	const fatal = 3;
	
	
	
	/**
	 * Class constructor takes a reference of the main StaticProjector
	 * 
	 * @param sp_StaticProjector $iSP
	 */
	public function __construct(sp_StaticProjector $iSP)
	{
		$this -> sp = $iSP;
	}
	
	/**
	 * Class destructor : not to be called manually
	 */
	public function __destruct()
	{
		if($this -> fp)
			fclose($this -> fp);
	}
	
	/**
	 * Writes a message in the log file
	 * 
	 * @param int $iLevel
	 * @param string $iMessage
	 */
	public function log($iLevel, $iMessage)
	{
		sp_assert(__FILE__, __LINE__, 0 <= $iLevel && $iLevel < 5 && ! empty($iMessage));
		if(! $this -> fp)
		{
			$this -> fp = fopen($this -> sp -> targetdir()."/".sp_StaticProjector::cache_dir."/".sp_StaticProjector::log_file,'w');
			sp_assert(__FILE__, __LINE__, $this -> fp);
		}
		fwrite($this -> fp, $this->tag_mapper[$iLevel].$iMessage."\n");
	}
}