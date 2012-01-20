<?php
class ENT_Core_App {
	protected $front_controller;
	protected $config;
	protected $db;
	protected $environment;
	
	public function __construct() {
		
	}
	
	public function getDatabase() {
		if (!$this->db) {
			$this->db = new ENT_Core_Database();
		}
		return $this->db;
	}
	
	public function getEnvironment() {
		if (!$this->environment) {
			$this->environment = new ENT_Core_Environment($this->getConfig()->getEnvironment());
		}
		return $this->environment;
	}
	
	public function setEnvironment($environment) {
		$this->environment = $environment;
	}
	
	public function getConfig() {
		if (!$this->config) {
			$this->config = ENT_Core_Config::load();
		}
		return $this->config;
	}
	
	public function getFrontController() {
		if (!$this->front_controller) {
			$this->front_controller = new ENT_Core_Controller_Front();
		}
		return $this->front_controller;
	}
}
?>
