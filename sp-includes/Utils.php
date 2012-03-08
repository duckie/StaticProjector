<?php

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

function sp_dump_array(&$iArray, $iFileName)
{
	$fp = @fopen($iFileName,"w");
	if(!$fp) throw new ErrorException("The path $iFileName cannot be opened. Check the directory.");
	foreach($iArray as $value)
	{
		fwrite($fp,$value."\n");
	}
	fclose($fp);
}

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

