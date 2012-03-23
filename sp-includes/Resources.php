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
	const contains = 4;
	
	public function __construct()
	{
		
	}
	
	public function add_name($iName)
	{
		$this -> add_constraint("relative_path", str_replace("*","(.*)",$iName), self::name_lookup);
	}
	
	public function add_constraint($iAttributeName, $iValue, $iConstraintType = self::equals)
	{
		array_push($this -> attribute_constraints, array($iAttributeName, $iValue, $iConstraintType));
	}
	
	public function add_order_by($iAttributeName, $iDirection = self::order_ascend)
	{
		array_push($this -> order_by, array($iAttributeName,$iDirection));
	}
	
	public function set_limit($iEnd)
	{
		sp_assert(-1 <= $iEnd);
		$this -> limit_end = $iEnd;
	}
	
	public function set_range($iBegin, $iEnd)
	{
		sp_assert(0 <= $iBegin && -1 <= $iEnd);
		$this -> limit_begin = $iBegin;
		$this -> limit_end = $iEnd;
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
		$current_set = $this->db;
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
					{
						$match = ( $value == $resource[$attr] );
					}
					else if(sp_Criterion::name_lookup == $type)
					{
						$match = preg_match("#$value$#", $resource[$attr]);
					}
					else if(sp_Criterion::regexp_match == $type)
					{
						$match = preg_match("#^$value$#", $resource[$attr]);
					}
					else if(sp_Criterion::contains == $type)
					{
						$match = is_array($resource[$attr]) ? in_array($value, $resource[$attr]) : preg_match("#$value#", $resource[$attr]);
					}
					else 
					{
						sp_assert(false, "The constraint type $type is not implemented.");
					}
					
					if($match)
					{
						array_push($result_set, $resource);
					}
				}
			}
			
			$current_set = $result_set;
			$result_set = array();
		}
		
		$result_set = &$current_set;
		
		// Ordering
		
		// creating multi sort params		
		$orders = $iCriterion -> orders_by();	
		if(count($orders))
		{
			$ordering_keys = array();
			foreach($orders as $order)
				$ordering_keys[] = $order[0];
			
			// Converting rows to columns
			$columns = sp_ArrayUtils::rows_to_columns($result_set, $ordering_keys);
			$multisort_params = array();
			foreach($orders as $order)
			{
				$attr = $order[0];
				$direction = $order[1];
				$type = count($columns[$attr]) ? (is_string($columns[$attr][0]) ? SORT_STRING : SORT_NUMERIC) : SORT_NUMERIC;

				$multisort_params[] = &$columns[$attr];
				$multisort_params[] = (sp_Criterion::order_ascend ==  $direction) ? SORT_ASC : SORT_DESC;
				$multisort_params[] = $type;
			}
			
			// Adding the data to ve sorted at the end
			$multisort_params[] = &$result_set;

			// Sorting
			call_user_func_array('array_multisort', $multisort_params);
		}
		
		// Fetching the result
		$range = $iCriterion -> limits();
		$range_begin = $range[0];
		$range_end = (-1 === $range[1]) ? count($result_set) : $range[1];
		
		$index = $range_begin;
		$result = array();
		for($index = $range_begin; $index < $range_end; $index++)
		{
			$result[] = $result_set[$index];
		}
		return $result;
	}
}