<?php
abstract class ENT_Core_Collection_Abstract extends ENT_Core_Resource_Abstract implements IteratorAggregate, Countable {
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
		$this->init();
	}
	
	public function init() {
		$dao = str_replace("_Collection", "_DAO", get_called_class());
		$this->queryBuilder->setTable($dao::$table);
		$this->queryBuilder->setFields($dao::$fields);
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
			
			$this->queryBuilder->addCondition("`{$name}` >= $min AND `{$name}` <= $max");
		} else {	
			$comparator = $param;
			if (!is_numeric($value) || (!$value && $value !== 0)) {
				$value = "'".$value."'";
			}
			$this->queryBuilder->addCondition("`{$name}` $comparator $value");
		}
	}
	
	public function select($pass_parameter) {
		return $this->filter($pass_parameter);
	}
	public function filter($pass_parameter) {	
		$function = $pass_parameter;
		$passed = array();
		
		foreach ($this->objects as $object) {
			$passes = call_user_func($function, $object);
			if ($passes) {
				$passed[] = $object;
			}
		}
		
		$class = get_called_class();
		return new $class($passed);
	}
	public function reject($pass_parameter) {
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
		$function = $pass_parameter;
		$values = array();
		foreach ($this->objects as $object) {
			$values[] = call_user_func($function, $object);
		}
		return $values;
	}
	public function each($pass_parameter) {
		$function = $pass_parameter;
		foreach ($this->objects as $object) {
			$passes = call_user_func($function, $object);
		}
	}
	public function find($pass_parameter) {
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
		if (is_numeric($pass_parameter)) {
			$pass_parameter = function($object) {
				return ($object->getID() == $pass_parameter);
			};
		}
		return $this->find($pass_parameter);
	}
	public function all($pass_parameter) {
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
	public function has($matches) {
		return $this->includes($matches);
	}
	public function reverse() {
		$this->objects = array_reverse($this->objects);
	}
	
	public function slice($index, $length = null) {
		$result = array_slice($this->objects, $index, $length);
		if ($length == 1) {
			return $result;
		} else {		
			$class = get_called_class();
			return new $class($result);
		}
	}
	
	public function getObjects() {
		return $this->objects;
	}
	public function at($index) {
		return $this->eq($index);
	}
	public function eq($index) {
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
 	
 	protected function beforeBuild() {
 	
 	}
 	
 	public function build($reset = false) {
		if (!$this->built || $reset) {
			$this->beforeBuild();
			
			if (!$items = $this->items) {
				$items = $this->fetch();
			}

			$object_static = ENT::getStatic($this->key);
			if (count($items)) {
				if ($object_static::$version == '2.0') {
					$object_load = $object_static.'_Load';
			
					foreach ($items as $item) {
						$object = $object_static::load($item, $object_load::DATA);

						$this->objects[] = $object;
					}				
				} else {
					foreach ($items as $item) {
						$object = ENT::getModule($this->key);
						$object->load($item, $object_static::LOAD_DATA);

						$this->objects[] = $object;
					}
				}
			}
		}

		$this->built = true;
		return $this->objects;
	}
}
?>
