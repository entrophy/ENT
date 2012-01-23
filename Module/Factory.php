<?php
class ENT_Module_Factory {
	public static $version = '2.0';

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
	public static function load($param, $type = null) {
		$class = get_called_class().'_Load';
		return $class::factory($param, $type);
	}

	public function is($obj) {
		return ($obj && get_class($this) == get_class($obj) && $this->getID() == $obj->getID());
	}
	
	public function save($data) {
		$id = $this->dao->save($this->getID(), $data);
		if ($this->getID()) {
			$id = $this->getID();
		}
		
		#$this->load($id, static::LOAD_ID);
		
		$load_class = $class = get_class($this).'_Load';
		$this->infuse($load_class::factory($id, $load_class::ID, false));
	}
	
	public function delete() {
		$this->dao->delete($this->getID());
	}
	
	public function setAdditional($object) {
	
	}
	public function infuse($data) {
		if (is_array($data)) {
			$this->valueObject->load($data);
			$this->id = $this->valueObject->id;
			
			if ($this->id) {
				$this->exists = true;
			}
		}
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
