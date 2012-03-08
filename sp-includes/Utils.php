<?php

/**
 * Constants
 */
define("SP_HTTP_NO_RULE",0);
define("SP_HTTP_ALLOW_ACCESS",1);
define("SP_HTTP_DENY_LISTING",2);
define("SP_HTTP_DENY_ACCESS",3);

/**
 * Sets how a given directory can be accessed through the server
 *
 * Warning : Beyond so far, this only uses a .htaccess
 *
 * @param string $iDirectoryName The path to the directory that has to be managed
 * @param int $iGrant Can take the value SP_HTTP_NO_RULE, SP_HTTP_ALLOW_ACCESS, SP_HTTP_DENY_LISTING or SP_HTTP_DENY_ACCESS
 */
function sp_set_http_granting($iDirectoryName, $iGrant)
{
	assert(is_dir($iDirectoryName));
	if(SP_HTTP_NO_RULE == $iGrant)
		file_put_contents("$iDirectoryName/.htaccess", "");
	else if(SP_HTTP_ALLOW_ACCESS == $iGrant)
		file_put_contents("$iDirectoryName/.htaccess", "Options +Indexes\n");
	else if(SP_HTTP_DENY_LISTING == $iGrant)
		file_put_contents("$iDirectoryName/.htaccess", "Options -Indexes\n");
	else if(SP_HTTP_DENY_ACCESS == $iGrant)
		file_put_contents("$iDirectoryName/.htaccess", "deny from all\n");
}

/**
 * ArrayConvertible allows any extending class to be a struct
 * easily convertible in an array. It makes those classes 
 * more usable for both the developer of the class and
 * their users
 *
 */
abstract class sp_ArrayConvertible
{
	/**
	 * Reads all the public properties and return them and their
	 * values in an array
	 * 
	 * @return array An array which keys are the properties names
	 */
	public function as_array()
	{
		$reflector = new ReflectionClass($this);
		$result = array();
		$props   = $reflector->getProperties(ReflectionProperty::IS_PUBLIC);
		foreach ($props as $prop)
		{
			$name = $prop -> getName();
			$value = $prop -> getValue($this);
			$result["$name"] = $value;
		}
	
		return $result;
	}
}

class sp_ArrayUtils
{
	private function __construct()
	{} 

	/**
	 * Returns true if the array is associative
	 * 
	 * Based on @see http://php.net/manual/en/function.is-array.php#41179
	 * 
	 * @param array $iArray
	 * @returns bool
	 */
	public static function is_assoc_array($iArray)
	{
		return is_array($iArray) && array_keys($iArray) !== range(0,sizeof($iArray)-1);
	}
	
	/**
	 * Dumps the array content in a file, each element separated by the given separator
	 * 
	 * @param array $iArray
	 * @param string $iFileName
	 * @param string $iSep Separator, default is UNIX newline
	 */
	public static function dump_array(&$iArray, $iFileName, $iSep = "\n")
	{
	    assert( ! is_dir($iFileName));
		$fp = @fopen($iFileName,"w");	
		foreach($iArray as $value)
		{
			fwrite($fp,$value.$iSep);
		}
		fclose($fp);
	}
	
	/**
	 * Recursive function used by @see sp_ArrayUtils::store_array
	 * 
	 * @param array|string $iArray
	 * @param int $iRecurseLevel Level to limit recursivity
	 * @return string 
	 */
	private static function array_as_php_string($iArray,$iRecurseLevel = 100)
	{
		if(0 == $iRecurseLevel) return "";
		if(!is_array($iArray))
		{
			if(is_numeric($iArray))
				return $iArray;
			else if(is_bool($iArray))
				return $iArray ? "true" : "false";
			else
				return "\"".addslashes($iArray)."\"";
		}

		$debug_char = ""; // @todo Link to the config file about the status "debug" or not
		$output = "array($debug_char";
		$first = true;
		if(self::is_assoc_array($iArray))
		{
			foreach($iArray as $key => $elem)
			{
				if(!$first) $output.=",$debug_char";
				$container_char = is_numeric($key) ? "" : "\"";
				$output .= "$container_char$key$container_char=>".self::array_as_php_string($elem);
				$first = false;
			}
		}
		else
		{
			foreach($iArray as $elem)
			{
				if(!$first) $output.=",$debug_char";
				$output .= self::array_as_php_string($elem, $iRecurseLevel - 1);
				$first = false;
			}
		}
		$output.="$debug_char)";
		
		return $output;
	}
	
	/**
	 * Stores the array as PHP code into the given file
	 * 
	 * @see sp_ArrayUtils::load_array
	 * 
	 * @param array $iArray
	 * @param string $iFileName
	 */
	public static function store_array(&$iArray, $iFileName)
	{
		assert( ! is_dir($iFileName));
		file_put_contents($iFileName, "<?php \$sp_stored_array=".self::array_as_php_string($iArray).";?>");
	}
	
	/**
	 * Loads an array stored by @see sp_ArrayUtils::store_array
	 * 
	 * @param string $iFileName
	 * @returns array
	 */
	public static function load_array($iFileName)
	{
		assert( file_exists($iFileName) && !is_dir($iFileName));
		include($iFileName);
		return $sp_stored_array;
	}
}




