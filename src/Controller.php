<?php
abstract class ENT_Controller extends ENT_Controller_Abstract {
	protected $frontController;
	private $request;
	private $request_cache;
	private $response;
	private $header;
	private $layout;
		
	public function addMessage($message) {
		
	}
	
	public function startSession() {
		if (!ENT::registry('session_start')) {
			session_start();
			ENT::register('session_start', true);
		}
	}
	
	public function setFrontController(ENT_Controller_Front $frontController) {
		$this->frontController = $frontController;
		return $this;
	}
	
	public function setRequest(ENT_Request $request) {
		$this->request = $request;
		return $this;
	}
	public function getRequest() {
		return $this->request;
	}
	
	public function setRequestCache(ENT_Request_Cache $request_cache) {
		$this->request_cache = $request_cache;
		return $this;
	}
	public function getRequestCache() {
		return $this->request_cache;
	}
	
	public function setResponse(ENT_Response $response) {
		$this->response = $response;
		return $this;
	}
	public function getResponse() {
		return $this->response;
	}
	
	public function setHeader(ENT_Template_Header $header) {
		$this->header = $header;
		return $this;
	}
	public function getHeader() {
		return $this->header;
	}
	
	public function setLayoutObject($layout) {
		$this->layout = $layout;
		return $this;
	}
	public function getLayoutObject() {
		return $this->layout;
	}
	
	public function setLayout($layout) {
		$this->frontController->setLayout($layout);
	}
	public function getTemplate() {
		return $this->frontController->getTemplate();
	}
	
	public function url($path) {
		if (substr($path, 0, 1) == '/') {
			$path = substr_replace($path, '', 0, 1);
		}
		$path = ENT::getWebBasePath().$path;
		return $path;
	}
	
	public function renderLayout($render) {
		$this->frontController->renderLayout($render);
		return $this;
	}
	public function renderView($render) {
		$this->frontController->renderLayout($render);
		$this->frontController->renderView($render);
		return $this;
	}
	public function renderViewLater($render) {
		$this->frontController->renderViewLater($render);
		return $this;
	}

	public function redirect($path, $hard = false) {
		$_SESSION['redirect_referer'] = $_SERVER['REDIRECT_URL'];
		#print_r($_SESSION);
		if (!$hard) {
			return $this->frontController->redirect($path);
		} else {
			if (substr($path, 0, 1) != '/' && strpos($path, 'http://') === false) {
				$path = ENT::getWebBasePath().$path;
			}
			header("location: ".$path);
		}
	}
	
	public function helper($name) {
		return ENT::getHelper($name);
	}
}
?>
