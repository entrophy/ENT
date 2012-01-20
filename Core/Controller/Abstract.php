<?php
abstract class ENT_Core_Controller_Abstract {
	protected $frontController;
	private $request;
	private $request_cache;
	private $response;
	private $header;
	private $template;
	
	public function init() {
	
	}
	
	public function _beforeAction() {
	
	}
	public function _afterAction() {
	
	}
	public function _afterTemplateAction() {
	
	}
	
	public function addMessage($message) {
		
	}
	
	public function startSession() {
		if (!ENT::registry('session_start')) {
			session_start();
			ENT::register('session_start', true);
		}
	}
	
	public function setFrontController(ENT_Core_Controller_Front $frontController) {
		$this->frontController = $frontController;
	}
	
	public function setRequest(ENT_Core_Request $request) {
		$this->request = $request;
	}
	public function getRequest() {
		return $this->request;
	}
	
	public function setRequestCache(ENT_Core_Request_Cache $request_cache) {
		$this->request_cache = $request_cache;
	}
	public function getRequestCache() {
		return $this->request_cache;
	}
	
	public function setResponse(ENT_Core_Response $response) {
		$this->response = $response;
	}
	public function getResponse() {
		return $this->response;
	}
	
	public function setHeader(ENT_Core_Template_Header $header) {
		$this->header = $header;
	}
	public function getHeader() {
		return $this->header;
	}
	
	public function setTemplateObject($template) {
		$this->template = $template;
	}
	public function getTemplateObject() {
		return $this->template;
	}
	
	public function setTemplate($layout) {
		$this->frontController->setTemplate($layout);
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
	
	public function renderTemplate($render) {
		$this->frontController->renderTemplate($render);
	}
	public function renderView($render) {
		$this->frontController->renderTemplate($render);
		$this->frontController->renderView($render);
	}
	public function renderViewLater($render) {
		$this->frontController->renderViewLater($render);
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
