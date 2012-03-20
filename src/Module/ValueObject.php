<?php
class ENT_Module_ValueObject {
	public function __set($key, $val) {
		if (property_exists($this, $key)) {
			$this->$key = $val;
		} else {
			
		}
	}
	
	public function __destruct() {
		foreach (array_keys(get_object_vars($this)) as $key) {
			unset($this->$key);
		}
	}
	
	public function load(array $data) {
		foreach ($data as $key => $item) {	
			if (property_exists($this, $key)) {
				$this->$key = $item;
			}
		}
	}
	
	public function whitelist($values) {
		foreach ($values as $key => $value) {
			if (!property_exists($this, $key)) {
				unset($values[$key]);
			}
		}
		return $values;
	}

	public function difference($values) {
		$values = $this->whitelist($values);
		foreach ($values as $key => $value) {
			if ($this->$key === $value) {
				unset($values[$key]);
			}
		}
		return $values;
	}
	
	public function copy() {
		$copy = new $this;
		$copy->load($this->getValues());
		return $copy;
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
