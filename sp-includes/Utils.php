<?php

/**
 * ArrayConvertible allows any extending class to be a struct
 * easily convertible in an array. It makes thoses classes 
 * more usable for both the developer of the classes and
 * their users
 *  
 * @author Jean-Bernard Jansen
 *
 */
abstract class ArrayConvertible
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
