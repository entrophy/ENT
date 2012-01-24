<?php
abstract class ENT_Resource_Smart extends ENT_Resource_Abstract {
	protected $items;
	protected $className;
	
	public function init() {
		$className = get_class($this);
		$className = explode("_", $className);
		array_splice($className, -1, 1);
		$className = implode("_", $className);
		$tableName = strtolower($className);
		$this->className = $className;
		
		$this->setTable($tableName);
	}
	
	public function fetch() {
		$result = $this->database->execute($this->getQuery());
		while ($data = mysql_fetch_array($result)) {
			$item = ENT::getModule(str_replace("_", "/", $this->className));
			
			$item->load($data['id']);
			
			$this->items[] = $item;
		}
		
		return $this->items;
	}
}
?>