<?php
class ENT_App {
	protected $front_controller;
	protected $config;
	protected $router;
	protected $database;
	protected $environment;
	
	public function __construct() {
		
	}
	
	public function getDatabase() {
		if (!$this->database) {
			$this->database = new ENT_Database();
		}
		return $this->database;
	}
	
	public function getEnvironment() {
		if (!$this->environment) {
			$this->environment = new ENT_Environment($this->getConfig()->getEnvironment());
		}
		return $this->environment;
	}
	
	public function setEnvironment($environment) {
		$this->environment = $environment;
	}
	
	public function getConfig() {
		if (!$this->config) {
			$this->config = new ENT_Config(ENT::registry('project_path').'app/etc/config.json');
		}
		return $this->config;
	}
	
	public function getRouter() {
		if (!$this->router) {
			$this->router = new ENT_Router(ENT::registry('project_path').'app/etc/routes.json');
		}
		return $this->router;
	}
	
	public function getFrontController() {
		if (!$this->front_controller) {
			$this->front_controller = new ENT_Controller_Front();
		}
		return $this->front_controller;
	}
}
?>
