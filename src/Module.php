<?php
abstract class ENT_Module {
	public static $version = '2.0';

	protected $dao;
	protected $previous;
	protected $valueObject;
	protected $id;
	protected $reload_on_save = false;
	
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
	
	public function save($values) {	
		$values = $this->valueObject->whitelist($values);

		if ($this->isDifferent($values) || !$this->exists) {
			$this->previous = $this->valueObject->copy();

			$values = $this->valueObject->difference($values);
			$this->valueObject->load($values);
			$id = $this->dao->save($this->getID(), $values);
			
			if (!$this->getID()) {
				$this->valueObject->id = $id;
				$this->id = $id;

				$this->exists = true;
			}
		
			if ($this->reload_on_save) {
				$load_class = $class = get_class($this).'_Load';
				$this->infuse($load_class::factory($id, $load_class::ID, false));
			}
		}
	}
	
	public function delete() {
		$this->dao->delete($this->getID());
	}
	
	public function toArray() {
		return $this->valueObject->getValues();
	}
	
	public function toJSON() {
		return json_encode($this->toArray());
	}
	
	public function getPrevious($key) {
		return $this->previous && $this->previous->$key ? $this->previous->$key : false;
	}
	public function hasChanged($key) {
		if ($this->previous && $this->previous->$key != $this->valueObject->$key) {
			return true;
		}
		return false;
	}

	public function isDifferent($values) {
		return count($this->valueObject->difference($values)) > 0;
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
