<?php
abstract class ENT_Controller extends ENT_Controller_Abstract {
	protected $type = 'MVC';	
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
	public function getLayout() {
		return $this->frontController->getLayout();
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
