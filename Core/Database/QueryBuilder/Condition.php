<?php
class RAD_Core_Database_QueryBuilder_Condition {
	private $sql;
	private $key;
	private $weight;
	
	public function __construct($params, $key = '', $weight = 0) {
		if (is_array($params)) {
			if (is_object($params[0])) {
			
			} else {
				foreach ($params as $field => $value) {
					$field = RAD_Core_Database::getInstance()->field($field);
					
					if (is_numeric($value)) {
						$this->sql = $field." = $value";
					} else {
						$this->sql = $field." = '$value'";
					}
				}
			}
		} else {
			$this->sql = $params;
		}
		
		$this->key = $key;
		$this->weight = $weight;
	}
	
	public function getSql() {
		return $this->sql;
	}	
	public function getKey() {
		return $this->key;
	}
	public function getWeight() {
		return $this->weight;
	}	
}
?>
