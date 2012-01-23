<?php
class ENT_Module_Abstract {
	const LOAD_DATA = 'load/data';
	const LOAD_ID = 'load/id';
	
	public static $version = '1.0';

	protected $dao;
	protected $valueObject;
	protected $id;
	
	public $exists = false;
	public function __construct() {
		$this->dao = $this->dao();
		$this->valueObject = $this->valueObject();
	}
	public function __destruct() {
		unset($this->dao);
		unset($this->valueObject);
	}
	
	public function load($data, $type) {
		switch ($type) {
			case static::LOAD_DATA:
			
				break;
			case static::LOAD_ID:
				$data = $this->dao->load(array('id' => $data));
				break;
		}
		
		if (is_array($data)) {
			$this->valueObject->load($data);
			$this->id = $this->valueObject->id;
			
			if ($this->id = $this->valueObject->id) {
				$this->exists = true;
			}
		}
	}
	
	public function save($data) {
		$id = $this->dao->save($this->getID(), $data);
		if ($this->getID()) {
			$id = $this->getID();
		}
		$this->load($id, static::LOAD_ID);
	}
	public function delete() {
		$this->dao->delete($this->getID());
	}
	
	public function dao() {
		if (!$this->dao) {
			$class = get_class($this);
			$class .= "_DAO";
			
			if (class_exists($class)) {
				$this->dao = $class::getInstance();
			}
		}
		return $this->dao;
	}
	
	public function valueObject() {
		if (!$this->valueObject) {
			$class = get_class($this);
			$class .= "_ValueObject";
			
			if (class_exists($class)) {
				$this->valueObject = new $class;
			}
		}
		return $this->valueObject;
	}
	
	public function get($name) {
		return $this->valueObject->$name;
	}
	public function getID() {
		return $this->id;
	}
}
?>
