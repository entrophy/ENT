<?php
class ENT_Database_DataObject {
	public $data;

	public function __construct(array $_data) {
		$data = array();
		
		foreach ($_data as $key => $value) {
			$key = str_replace("_", " ", $key);
			$key = ucwords($key);
			$firstLetter = strtolower(substr($key, 0, 1));
			$key = substr_replace($key, $firstLetter, 0, 1);
			$key = str_replace(" ", "", $key);
			
			$data[$key] = $value;
		}
		
		$this->data = $data;
	}
	
	public function __call($name, $arguments) {
		$name = explode("get", $name);
		
		$firstLetter = strtolower(substr($name[1], 0, 1));
		$name = substr_replace($name[1], $firstLetter, 0, 1);
		
		return $this->data[$name];
	}
}
?>