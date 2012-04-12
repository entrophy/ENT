<?php
abstract class ENT_Collection implements IteratorAggregate, Countable {
	protected $database;
	protected $dataset;
	protected $totalCount;
	protected $queryBuilder;
	protected $qb;
	protected $size;
	protected $query;
	
	protected $objects = array();
	protected $key;
	protected $built = false;
	protected $match_on = 'id';
	
	public function __construct($objects = null) {
		$this->objects = $objects;
		if ($this->objects || is_array($objects)) {
			$this->built = true;
		}
		$this->database = ENT::app()->getDatabase();
		$this->queryBuilder = $this->database->queryBuilder();
		$this->qb = $this->queryBuilder;
		$this->init();
	}
	
	public function init() {
		$dao = str_replace("_Collection", "_DAO", get_called_class());
		$this->queryBuilder->setTable($dao::$table);
		$this->queryBuilder->setFields($dao::$fields);
	}

	public function setSort($name, $dir = 'ASC') {
		$this->queryBuilder->setOrder($name, $dir, $name);
		return $this;
	}

	public function addSort($name, $dir = 'ASC') {
		$this->queryBuilder->addOrder($name, $dir, $name);
		return $this;
	}

	public function removeSort($name = null) {
		$this->queryBuilder->removeOrder($name);
		return $this;
	}
	
	public function setAmount($amount) {
		$this->queryBuilder->setLimit($amount);
		return $this;
	}
	
	public function setOffset($offset) {
		$this->queryBuilder->setOffset($offset);
		return $this;
	}
	
	public function getTotalCount() {
		if (!$this->totalCount) {
			$this->totalCount = $this->database->getTotalRows();
		}
		return $this->totalCount;
	}
	
	public function reset() {	
		$this->dataset = null;
		$this->objects = null;
		$this->queryBuilder->unsetQuery();
	}
	
	public function getSize($recalculate = false) {
		if (!$this->size || $recalculate) {
			if (sizeof($this->objects)) {
				$this->size = sizeof($this->objects);
			} else if (sizeof($this->dataset)) {
				$this->size = sizeof($this->dataset);
			} else {
				$_fields = $this->queryBuilder->getFields();
				$this->queryBuilder->setFields('count(id) as `size`');
				$data = $this->database->getArray($this->queryBuilder->execute());
				$this->size = $data['size'];
				
				$this->queryBuilder->unsetQuery();
				$this->queryBuilder->setFields($_fields);
			}
		}
		return $this->size;
	}
	
	public function addFilter($name, $value, $param = '=') {
		if (is_array($value)) {	
			$include = $comparator;
			$min = $value[0];
			$max = $value[1];
			
			if (!is_numeric($min) || (!$min && $min !== 0)) {
				$min = "'".$min."'";
			}
			if (!is_numeric($max) || (!$max && $max !== 0)) {
				$max = "'".$max."'";
			}
			
			$this->queryBuilder->setCondition("`{$name}` >= $min AND `{$name}` <= $max");
		} else {	
			$comparator = $param;
			if (!is_numeric($value) || (!$value && $value !== 0)) {
				$value = "'".$value."'";
			}
			$this->queryBuilder->setCondition("`{$name}` $comparator $value");
		}
	}
	
	public function select($pass_parameter) {
		return $this->filter($pass_parameter);
	}
	public function filter($pass_parameter) {
		if (!$this->built) {
			$this->build();
		}

		$class = get_called_class();
		return new $class(array_filter($this->objects, $pass_parameter));
	}
	public function reject($pass_parameter) {
		if (!$this->built) {
			$this->build();
		}
	
		$function = $pass_parameter;
		$passed = array();
		foreach ($this->objects as $object) {
			$passes = call_user_func($function, $object);
			if (!$passes) {
				$passed[] = $object;
			}
		}
		
		$class = get_called_class();
		return new $class($passed);
	}
	public function min($pass_parameter, $return_value = false) {
		if (!$this->built) {
			$this->build();
		}
	
		if ($return_value) {
			$values = $this->values($pass_parameter);
			sort($values);
			return array_shift($values);
		} else {
			$function = $pass_parameter;
			$values = array();
			$objects = array();
			foreach ($this->objects as $object) {
				$values[$object->getID()] = call_user_func($function, $object);
				$objects[$object->getID()] = $object;
			}
			
			asort($values);
			
			return $objects[key($values)];
		}
	}
	public function max($pass_parameter, $return_value = false) {
		if (!$this->built) {
			$this->build();
		}
	
		if ($return_value) {
			$values = $this->values($pass_parameter);
			rsort($values);
			return array_shift($values);
		} else {
			$function = $pass_parameter;
			$values = array();
			$objects = array();
			foreach ($this->objects as $object) {
				$values[$object->getID()] = call_user_func($function, $object);
				$objects[$object->getID()] = $object;
			}
			
			arsort($values);
			
			return $objects[key($values)];
		}
	}
	public function values($pass_parameter) {
		if (!$this->built) {
			$this->build();
		}
		return array_map($pass_parameter, $this->objects);
	}
	public function each($pass_parameter) {
		if (!$this->built) {
			$this->build();
		}
		$function = $pass_parameter;
		foreach ($this->objects as $object) {
			$passes = call_user_func($function, $object);
		}
	}
	public function find($pass_parameter) {
		if (!$this->built) {
			$this->build();
		}
		$function = $pass_parameter;
		foreach ($this->objects as $object) {
			$passes = call_user_func($function, $object);
			
			if ($passes) {
				return $object;
				break;
			}
		}
		return false;
	}
	public function get($pass_parameter) {
		if (!$this->built) {
			$this->build();
		}
	
		if (is_numeric($pass_parameter)) {
			$pass_parameter = function($object) {
				return ($object->getID() == $pass_parameter);
			};
		}
		return $this->find($pass_parameter);
	}
	public function all($pass_parameter) {
		if (!$this->built) {
			$this->build();
		}
	
		$function = $pass_parameter;
		foreach ($this->objects as $object) {
			$passes = call_user_func($function, $object);
			
			if (!$passes) {
				return false;
				break;
			}
		}
		return true;
	}
	public function any($pass_parameter) {
	if (!$this->built) {
			$this->build();
		}
	
		$function = $pass_parameter;
		foreach ($this->objects as $object) {
			$passes = call_user_func($function, $object);
			
			if ($passes) {
				return true;
				break;
			}
		}
		return false;
	}
	public function includes($match_parameter) {
		if (!$this->built) {
			$this->build();
		}
	
		if (ENT::isCollection($match_parameter)) {
			$match_parameter = $match_parameter->getObjects();
		}
		if (is_object($match_parameter) || (is_array($match_parameter) && is_object($match_parameter[0]))) {
			$matches = $match_parameter;
			if (!is_array($matches)) {
				$matches = array($matches);
			}
			
			$response = true;
		
			foreach ($matches as $match) {
				$itemMatch = false;
				foreach ($this->objects as $object) {
					if ($object === $match && $object->get($this->match_on) === $match->get($this->match_on)) {
						$itemMatch = true;
						break;
					}
				}
				
				if (!$itemMatch) {
					$response = false;
					break;
				}
			}
		} 		
		return $response;
	}
	
	public function map($callback) {
		if (!$this->built) {
			$this->build();
		}
		return array_map($callback, $this->objects);
	}
	
	public function has($matches) {
		return $this->includes($matches);
	}
	public function reverse() {
		if (!$this->built) {
			$this->build();
		}
		$this->objects = array_reverse($this->objects);
	}
	
	public function slice($index, $length = null) {
		if (!$this->built) {
			$this->build();
		}
	
		$result = array_slice($this->objects, $index, $length);
		if ($length == 1) {
			return $result[0];
		} else {		
			$class = get_called_class();
			return new $class($result);
		}
	}
	
	public function getObjects() {
		return $this->objects;
	}
	public function getQuery() {
		return $this->query;
	}
	
	public function toArray() {
		if (!$this->built) {
			$this->build();
		}
		
		if (count($this->objects)) {
			return array_map(function ($object) {
				return $object->toArray();
			}, $this->objects);
		}
		return array();
	}
	
	public function toJSON() {
		return json_encode($this->toArray());
	}
	
	public function at($index) {
		if (!$this->built) {
			$this->build();
		}
		return $this->eq($index);
	}
	public function eq($index) {
		if (!$this->built) {
			$this->build();
		}
		return $this->objects[$index];
	}
	
	public function count() { 
		if (!$this->built) {
			$this->build();
		}
		return count($this->objects); 
	} 
	
	public function getIterator() {
		if (!$this->built) {
			$this->build();
		}
		return new ArrayIterator($this->objects);
 	}

 	public function fetch() {
		if (!$this->dataset) {
			$this->dataset = $this->queryBuilder->execute();
		}
		return $this->dataset;
	}
 	
 	protected function beforeBuild() {
 	
 	}
 	
 	public function isBuilt() {
 		return $this->built;
 	}
 	
 	public function build($reset = false) {
		if (!$this->built || $reset) {
			$this->beforeBuild();
			
			if (!$dataset = $this->dataset) {
				$dataset = $this->fetch();
			}

			$this->objects = array();
			if (count($dataset)) {
				$object_static = ENT::getStatic(static::$module_key);
				$object_load = $object_static.'_Load';
		
				foreach ($dataset as $data) {
					$object = $object_static::load($data, $object_load::DATA);

					$this->objects[] = $object;
				}				
			}
		}

		$this->built = true;
		return $this->objects;
	}
}
?>
