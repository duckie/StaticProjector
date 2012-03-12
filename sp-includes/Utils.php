<?php
/**
 * @package Utilities
 * @author Jean-Bernard Jansen
 * 
 * This file contains utility functions and classes which are usable 
 * everywhere in the program. You may find some tips for you in it.
 */

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
	sp_assert(is_dir($iDirectoryName));
	if(SP_HTTP_NO_RULE == $iGrant)
		file_put_contents("$iDirectoryName/.htaccess", "");
	else if(SP_HTTP_ALLOW_ACCESS == $iGrant)
		file_put_contents("$iDirectoryName/.htaccess", "Options +Indexes\n");
	else if(SP_HTTP_DENY_LISTING == $iGrant)
		file_put_contents("$iDirectoryName/.htaccess", "Options -Indexes\n");
	else if(SP_HTTP_DENY_ACCESS == $iGrant)
		file_put_contents("$iDirectoryName/.htaccess", "deny from all\n");
}

define("SP_INFO",0);
define("SP_WARNING",1);
define("SP_ERROR",2);
define("SP_FATAL",3);

/**
 * Assertion which can also be an error.
 * 
 * @param bool $iAssertion
 * @param int $iLevel
 * @param string $iMessage
 * @param string $iByPass
 */
function sp_assert($iAssertion, $iLevel = SP_FATAL, $iMessage="", $iByPass ="")
{
	assert($iAssertion);
}

/**
 * This static only class provides callbacks for functions such as array_reduce 
 * 
 * @author Jean-Bernard Jansen
 * @final
 */
class sp_LogicUtils
{
	private function __construct()
	{}
	
	/**
	 * Binary and
	 *
	 * @param bool $a
	 * @param bool $b
	 */
	function binary_and ($a, $b)
	{
		return $a && $b;
	}
	
	/**
	 * Binary or
	 *
	 * @param bool $a
	 * @param bool $b
	 */
	function binary_or ($a, $b)
	{
		return $a || $b;
	}
	
	/**
	 * Binary nand
	 * 
	 * @param bool $a
	 * @param bool $b
	 */
	function binary_nand ($a, $b)
	{
		return !$a && !$b;
	}

	/**
	 * Binary nor
	 *
	 * @param bool $a
	 * @param bool $b
	 */
	function binary_nor ($a, $b)  {
		return !$a || !$b;
	}
}

/**
 * Class to convert structures into arrays
 * 
 * ArrayConvertible allows any extending class to be a struct
 * easily convertible in an array. It makes those classes 
 * more usable for both the developer of the class and
 * their users
 * 
 * @abstract
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

/**
 * This class provides useful functions to manipulate arrays and to make i/o with them
 *
 * @author Jean-Bernard Jansen
 * @final
 */
class sp_ArrayUtils
{
	private function __construct()
	{} 

	/**
	 * Returns true if the array is associative
	 * 
	 * Based on @link http://php.net/manual/en/function.is-array.php#41179
	 * 
	 * @param mixed $iArray
	 * @returns bool
	 */
	public static function is_assoc_array($iArray)
	{
		return is_array($iArray) && array_keys($iArray) !== range(0,sizeof($iArray)-1);
	}
	
	/**
	 * Private callback which is part of sp_ArrayUtils::compute_array_depth implementation
	 * 
	 * @param array $iCurrentDepthAndLevel
	 * @param mixed $iNextArray
	 */
	private function compute_array_depth_cb1($iCurrentDepthAndLevel, &$iNextArray)
	{
		$iCurrentDepthAndLevel[0] = max($iCurrentDepthAndLevel[0], self::compute_array_depth($iNextArray, $iCurrentDepthAndLevel));
		return $iCurrentDepthAndLevel; 
	}
	
	/**
	 * Returns the array depth
	 *
	 * 0 means the argument is not an array
	 * 1 is the depth of a basic array
	 *
	 * @param mixed $iArray
	 * @param int $iRecurseLevel
	 * @return int
	 */
	public static function compute_array_depth(&$iArray, $iCurrentDepthAndLevel = array(0,100))
	{
		if(!is_array($iArray) || 0 >= $iCurrentDepthAndLevel[1]) return 0;
		$iCurrentDepthAndLevel[0] += 1;
		$iCurrentDepthAndLevel[1] -= 1;
		$result = array_reduce($iArray, "sp_ArrayUtils::compute_array_depth_cb1", $iCurrentDepthAndLevel);
		return $result[0];
	}
	
	private function is_multidimensional_array(&$iArray)
	{
		
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
	    sp_assert( ! is_dir($iFileName));
		$fp = @fopen($iFileName,"w");	
		foreach($iArray as $value)
		{
			fwrite($fp,$value.$iSep);
		}
		fclose($fp);
	}
	
	/**
	 * Converts an array into its PHP String representation
	 * 
	 * This function supports correctly numeric and boolean values,
	 * sequential and associative arrays.
	 * 
	 * 
	 * @param array|string $iArray
	 * @param bool $iDebug Activate debug mode, default false
	 * @param int $iRecurseLevel Level to limit recursivity
	 * @return string 
	 */
	public static function array_as_php_string($iArray,$iDebug = false, $iRecurseLevel = 100)
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

		$debug_char = $iDebug ? "\n" : "";
		$output = "array($debug_char";
		$first = true;
		if(self::is_assoc_array($iArray))
		{
			foreach($iArray as $key => $elem)
			{
				if(!$first) $output.=",$debug_char";
				$container_char = is_numeric($key) ? "" : "\"";
				$output .= "$container_char$key$container_char=>".self::array_as_php_string($elem, $iDebug, $iRecurseLevel - 1);
				$first = false;
			}
		}
		else
		{
			foreach($iArray as $elem)
			{
				if(!$first) $output.=",$debug_char";
				$output .= self::array_as_php_string($elem, $iDebug, $iRecurseLevel - 1);
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
	 * @param bool $iDebug Activate debug mode (default false)
	 */
	public static function store_array(&$iArray, $iFileName, $iDebug = false)
	{
		sp_assert( ! is_dir($iFileName));
		file_put_contents($iFileName, "<?php \$sp_stored_array=".self::array_as_php_string($iArray, $iDebug).";?>");
	}
	
	/**
	 * Loads an array stored by @see sp_ArrayUtils::store_array
	 * 
	 * @param string $iFileName
	 * @returns array
	 */
	public static function load_array($iFileName)
	{
		sp_assert( file_exists($iFileName) && !is_dir($iFileName));
		include($iFileName);
		return $sp_stored_array;
	}
	
	public static function store_config(&$iArray, $iFileName)
	{
		
	}
	
	public function load_config($iFileName)
	{
		
	}
}




