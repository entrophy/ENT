<?php
abstract class ENT_Controller_Abstract {
	protected $type;
	protected $frontController;
	protected $request;
	protected $response;

	public function getType() {
		return $this->type;
	}

	public function init() {
	
	}
	
	public function _beforeAction() {
	
	}
	public function _afterAction() {
	
	}
	public function _afterTemplateAction() {
	
	}
	
	public function setFrontController(ENT_Controller_Front $frontController) {
		$this->frontController = $frontController;
		return $this;
	}
	
	public function setRequest(ENT_Request $request) {
		$this->request = $request;
		return $this;
	}
	protected function getRequest() {
		return $this->request;
	}
	
	public function setResponse(ENT_Response $response) {
		$this->response = $response;
		return $this;
	}
	public function getResponse() {
		return $this->response;
	}
	
	public function getController($path) {
		if ($controller = ENT::getController($path)) {
			$controller->setFrontController($this->frontController)
						  ->setRequest($this->request)
						  ->setResponse($this->response)
						  ->init();
			return $controller;
		}
		return false;
	}
}
?>
