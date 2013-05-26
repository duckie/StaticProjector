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
	private $tag_mapper = array("[debug]","[info] ", "[warning] ", "[error] ", "[fatal] ");
  private $log_list = null;
	
	
	const debug = 0;
	const info = 1;
	const warning = 2;
	const error = 3;
	const fatal = 4;
	
	/**
	 * Class constructor takes a reference of the main StaticProjector
	 * 
	 * @param sp_StaticProjector $iSP
	 */
	public function __construct(sp_StaticProjector $iSP)
	{
		$this -> sp = $iSP;
	}
  
  public function set_debug_enabled($iDebugEnabled) {
    if($iDebugEnabled && null === $this->log_list) {
      $this->log_list = array();
    }
    else {
      $this->log_list = null;
    }
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
		sp_assert(__FILE__, __LINE__, 0 <= $iLevel && $iLevel < 5);
		if(! $this -> fp)
		{
			$this -> fp = fopen($this -> sp -> targetdir()."/".sp_StaticProjector::cache_dir."/".sp_StaticProjector::log_file,'w');
			sp_assert(__FILE__, __LINE__, $this -> fp);
		}
		fwrite($this -> fp, $this->tag_mapper[$iLevel].$iMessage."\n");
    if(null !== $this->log_list) {
      $log_elem = array();
      $log_elem['file'] = __FILE__;
      $log_elem['line'] = __LINE__;
      $log_elem['level'] = $this->tag_mapper[$iLevel];
      $log_elem['data'] = $iMessage;
      array_push($this->log_list,$log_elem);
    }
	}

  public function log_list() {
    return $this->log_list;
  }
}
