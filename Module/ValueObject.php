<?php
class ENT_Module_ValueObject {
	public function __set($key, $val) {
		if (property_exists($this, $key)) {
			$this->$key = $val;
		} else {
			
		}
	}
	
	public function __desturct() {
		foreach (array_keys(get_object_vars($this)) as $key) {
			unset($this->$key);
		}
	}
	
	public function load(array $data) {
		foreach ($data as $key => $dataItem) {	
			if (property_exists($this, $key)) {
				$this->$key = $dataItem;
			}
		}
	}
	
	public function getValues() {
		$values = get_object_vars($this);
		return $values;
	}
	
	public function __get($key) {
		return $this->$key;
	}
}
?>
