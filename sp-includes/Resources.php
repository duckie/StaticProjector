<?php 

class sp_Criterion
{
	private $names = array();
	private $order_by = array();
	private $attribute_constraints = array();
	private $limit_begin = 0;
	private $limit_end = -1;
	
	const order_descend = 0;
	const order_ascend = 1;
	
	const like = 0; 
	const equals = 1;
	const regexp_match = 2;
	const name_lookup = 3;
	
	public function __construct()
	{
		
	}
	
	public function add_name($iName)
	{
		$pattern = str_replace("*","(.*)",$iName);
		array_push($this -> names, str_replace("*","[^/]",$iName));
		$this -> add_constraints("relative_path", $iValue, self::name_lookup);
	}
	
	public function add_constraint($iAttributeName, $iValue, $iConstraintType = self::equals)
	{
		array_push($this -> attribute_constraints, array($iAttributeName, $iValue, $iConstraintType));
	}
	
	public function add_order_by($iAttributeName)
	{
		array_push($this -> order_by, $iAttributeName);
	}
	
	public function constraints()
	{
		return $this -> attribute_constraints; 
	}
	
	public function orders_by()
	{
		return $this -> order_by;
	}
	
	public function limits()
	{
		return array($this -> limit_begin, $this -> limit_end);
	}
}

class sp_ResourceBrowser
{
	private $sp;
	private $db;
	
	public function __construct(sp_StaticProjector $iSP)
	{
		$this -> sp = $iSP;
		$this -> db = sp_ArrayUtils::load_array($this -> sp -> targetdir()."/".sp_StaticProjector::cache_dir."/".sp_StaticProjector::dic_file);
	}
	
	public function get_resource_path($iPartialPath)
	{
		$path = sp_filter_path($iPartialPath);
		return $this -> sp -> basedir()."/".sp_StaticProjector::data_dir.$path;
	}
	
	public function query_resources(sp_Criterion $iCriterion)
	{
		// Filtering
		$constraints = 	$iCriterion -> constraints();
		$current_set = &$this->db;
		$result_set = array();
		foreach($constraints as $constraint)
		{
			$attr = $constraint[0];
			$value = $constraint[1];
			$type = $constraint[2];
			
			foreach($current_set as $resource)
			{
				if(key_exists($attr, $resource))
				{
					$match = false;
					if(sp_Criterion::equals == $type)
						$match = ( $value == $resource[$attr] );
					else if(sp_Criterion::name_lookup == $type)
						$match = preg_match("#$value$#", $resource[$attr]);
					else if(sp_Criterion::regexp_match == $type)
						$match = preg_match("#$value#", $resource[$attr]);
					else
						sp_assert(false, "The constraint type $type is not implemented.");
					
					if($match)
						array_push($result_set, $resource);
				}
			}
			
			$current_set = &$result_set;
			$result_set = array();
		}
		
		// Ordering
		
		// Converting columns to rows
		
		//$dynamicSort = "$sort1,SORT_ASC,$sort2,SORT_ASC,$sort3,SORT_ASC";
		//$param = array_merge(explode(",", $dynamicSort), array($arrayToSort))
		//call_user_func_array('array_multisort', $param)
		
	}
}