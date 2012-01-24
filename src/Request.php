<?php
class ENT_Request {
	private $section;
	private $controller;
	private $action;
	private $params;
	private $path;
	
	public function __construct() {
		if (strpos($_SERVER['REQUEST_URI'], '?') !== FALSE) {
			$query = explode("?", $_SERVER['REQUEST_URI']);
			$query = array_splice($query, 1);
			$query = implode("?", $query);

			parse_str($query, $query);
		
			if (count($query)) {
				$_GET = array_merge($_GET, $query);
			}
		}

		if (count($_GET)) {
			foreach ($_GET as $key => $value) {
				switch ($key) {
					case 'path':
						$this->reconfig($value);
						break;
					case 'section':
						$this->section = $value;
						break;
					case 'controller':
						$this->controller = $value;
						break;
					case 'action':
						$this->action = $value;
						break;
					default:
						$this->params[$key] = $value;
						break;
				}
			}
		}
	}
	
	public function reConfig($path) {
		$this->path = $path;
	
		$x = 1;
		$path = explode("/", $path);
		
		$isVar = false;
		$varKey = null;
		foreach ($path as $item) {
			switch ($x) {
				case 1:
					$this->section = $item;
					break;
				case 2:
					$this->controller = $item;
					break;
				case 3:
					$this->action = $item;
					break;
				default:
					if (!$isVar) {
						$isVar = true;
						$varKey = $item;
					} else {
						$this->params[$varKey] = $item;
						$isVar = false;
					}
					break;
			}				
			$x++;
		}	
	}
	
	public function getUrl() {
		return str_replace(ENT::getWebBasePath(), "", $_SERVER['REQUEST_URI']);
	}
	public function getSection() {
		return $this->section;
	}
	public function getController() {
		return $this->controller;
	}
	public function getAction() {
		return $this->action;
	}
	
	public function getType() {
		return $_SERVER['REQUEST_METHOD'];
	}
	
	public function getPosts() {
		return $_POST;
	}
	public function getPost($key = false) {
		if (!$key) {
			return $this->getPosts();
		}
		return $_POST[$key];
	}
	
	public function getParams() {
		return $this->params;
	}
	
	public function getFiles() {
		return $_FILES;
	}
	public function getFile($key) {
		return $_FILES[$key];
	}
	
	public function getParam($key) {
		return $this->params[$key];
	}
	public function getSession($key) {
		return $_SESSION[$key];
	}
	public function setSession($key, $value) {
		$_SESSION[$key] = $value;
	}
	public function getCookie($key) {
		return $_COOKIE[$key];
	}
	public function setCookie($key, $value, $expire) {
		$expire = time() + $expire;
		setcookie($key, $value, $expire, '/');
	}
}
?>