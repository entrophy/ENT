<?php
class ENT_Module_DAO {
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
		$qb = $this->database->queryBuilder();
		$qb->setTable(static::$table);
		$qb->setValues($data);
		
		if ($match) {		
			$qb->setType('update');
			if (is_array($match)) {
				$qb->addCondition($match);
				
				if ($check) {
					$keys = array_keys($data);
					
					$_builder = $this->database->queryBuilder();
					$_builder->setTable(static::$table);
					$_builder->setData($keys[0]);
					$_builder->addCondition($match);
					$result = $_builder->execute();
					
					if (!$this->database->getRows($result)) {
						$match = false;
						$qb->setType('insert');
					}
				}
			} else {
				$qb->addCondition("id = $match");
				$id = $match;
			}
		} else {
			$qb->setType('insert');
		}
		
		$result = $qb->execute();

		if (!$match) {
			$id = $this->database->insertID();
		}
		
		return $id;
	}
	
	protected $load_limit = 1;
	public function load($data) {
		$qb = $this->database->queryBuilder();
		$qb->setTable(static::$table);
		$qb->setFields(static::$fields);
		$qb->setCondition($data);
		
		if ($this->load_limit) {
			$qb->setAmount($this->load_limit);
		}
		
		$result = $qb->execute();
		$data = $result[0];
		
		return $data;
	}
	public function delete($data) {
		$qb = $this->database->queryBuilder();
		$qb->setTable(static::$table);
		$qb->setType('delete');
		
		if (is_array($data)) {
			$qb->setCondition($data);
		} else {
			$qb->setCondition('id = :id');
			$qb->bindParam('id', $data);
		}
		
		$result = $qb->execute();
	}
}
?>
