<?php
class ENT_Core_Database_DAO {
	protected $database;
	public function __construct() {
		$this->database = ENT::app()->getDatabase();
	}
	
	protected static $instances;
	public static function getInstance() {
		$className = get_called_class();
		if (!static::$instances[$className]) {
			static::$instances[$className] = new $className;
		}
		return static::$instances[$className];
	}
	
	public static $table = '';
	public static $fields = '';
	public function save($match, $data, $check = false) {
		$builder = $this->database->queryBuilder();
		$builder->setTable(static::$table);
		$builder->setData($data);
		
		if ($match) {		
			$builder->setType('update');
			if (is_array($match)) {
				$builder->addCondition($match);
				
				if ($check) {
					$keys = array_keys($data);
					
					$_builder = $this->database->queryBuilder();
					$_builder->setTable(static::$table);
					$_builder->setData($keys[0]);
					$_builder->addCondition($match);
					$result = $_builder->execute();
					
					if (!$this->database->getRows($result)) {
						$match = false;
						$builder->setType('insert');
					}
				}
			} else {
				$builder->addCondition("id = $match");
				$id = $match;
			}
		} else {
			$builder->setType('insert');
		}
		
		$result = $builder->execute();

		if (!$match) {
			$id = $this->database->insertID();
		}
		
		return $id;
	}
	
	protected $load_limit = 1;
	public function load($data) {
		$builder = $this->database->queryBuilder();
		$builder->setTable(static::$table);
		$builder->setFields(static::$fields);
		$builder->addCondition($data);
		
		if ($this->load_limit) {
			$builder->setAmmount($this->load_limit);
		}
		
		$result = $builder->execute();
		$data = $this->database->getArray($result);
		
		return $data;
	}
	public function delete($data) {
		$builder = $this->database->queryBuilder();
		$builder->setTable(static::$table);
		$builder->setType('delete');
		
		if (is_array($data)) {
			$builder->addCondition($data);
		} else {
			$builder->addCondition("id = $data");
		}
		
		$result = $builder->execute();
	}
}
?>
