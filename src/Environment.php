<?php
class ENT_Environment {
	private $data;
	
	public function __construct($data) {
		$this->load($data);
	}
	public function load($data) {
		if ($this->data) {
			$this->data = array_merge($this->data, $data);
		} else {
			$this->data = $data;
		}	
	}
	
	public function getHost() {
		return $this->data['host'];
	}
	public function getName() {
		return $this->data['name'];
	}
	public function getType() {
		return $this->data['type'];
	}
}
?>
