<?php 

class sp_StaticRegister
{
	static private $register = array();
	
	static public function push_object($iKey,$iObject)
	{
		if( ! key_exists($iKey, self::$register))
		{
			self::$register[$iKey] = array();
		}
		
		array_push(self::$register[$iKey], $iObject);
	}
	
	static public function pop_object($iKey)
	{
		sp_assert(key_exists($iKey,self::$register));
		return array_pop(self::$register[$iKey]);
	}
	
	static public function get_object($iKey)
	{
		sp_assert(key_exists($iKey,self::$register));
		return self::$register[$iKey][count(self::$register[$iKey])-1];
	}
	
	static public function has_object($iKey)
	{
		return key_exists($iKey,self::$register);
	}
}
