<?php
class ENT_Session_Namespace {
	private $_namespace;
	public function __construct($namespace) {
		$this->_namespace = $namespace;
	}
	
	public function set($name, $value) {
		ENT_Session::set($name, $value, $this->_namespace);
		return $this;
	}
	
	public function __set($name, $value) {
		$this->set($name, $value);
	}
	
	public function get($name) {
		return ENT_Session::get($name, $this->_namespace);
	}
	
	public function __get($name) {
		return $this->get($name);
	}
}
?>
