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

function sp_forbid_http_access($iDirectoryName)
{
	assert(is_dir($iDirectoryName));
	file_put_contents("$iDirectoryName/.htaccess", "deny from all\n");
}

