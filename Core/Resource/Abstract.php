<?php
abstract class RAD_Core_Resource_Abstract {
	protected $database;
	protected $result;
	protected $items;
	protected $totalCount;
	protected $queryBuilder;
	protected $size;
	protected $objects;
	protected $query;
	public function __construct($objects = null) {
		$this->objects = $objects;
		$this->database = RAD::app()->getDatabase();
		$this->queryBuilder = $this->database->queryBuilder();
		$this->init();
	}
	
	public function init() {
	
	}
	
	public function setSort($name, $dir = 'ASC') {
		$this->queryBuilder->setSort($name." ".$dir);
		return $this;
	}
	
	public function setOffset($ammount) {
		$this->queryBuilder->setOffset($ammount);
	}
	
	public function getTotalCount() {
		if (!$this->totalCount) {
			$this->totalCount = $this->database->getTotalRows();
		}
		return $this->totalCount;
	}
	
	public function reset() {	
		$this->result = null;
		$this->items = null;
		$this->objects = null;
		$this->queryBuilder->unsetQuery();
	}
	
	public function setAmmount($ammount) {
		$this->queryBuilder->setLimit($ammount);
		return $this;
	}
	
	public function getSize($recalculate = false) {
		if (!$this->size || $recalculate) {
			if (sizeof($this->objects)) {
				$this->size = sizeof($this->objects);
			} else if (sizeof($this->items)) {
				$this->size = sizeof($this->items);
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
	
	public function getQuery() {
		return $this->query;
	}
	
	public function fetch() {
		if (!$this->result) {
			$query = $this->query = $this->queryBuilder->getQuery();
			$result = $this->database->execute($query);
			$this->getTotalCount();
			
			$this->result = $result;
			$this->items = $this->database->resultToArray($result);
		}
		return $this->items;
	}
}
?>
