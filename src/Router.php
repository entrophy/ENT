<?php
class ENT_Router {
	private $_default;
	private $routes;
	private $rewrites;
	
	public function __construct($file) {
		$this->load($file);
	}
	public function load($file) {
		$json = json_decode(file_get_contents($file));
		
		$this->_default = $json->default;
		$this->routes = $json->routes;
		$this->rewrites = $json->rewrites;
	}
	
	public function rewrite($path) {
		$response = false;
		if (count($this->rewrites)) {
			foreach ($this->rewrites as $match => $rewrite) {
				if ($match === $path) {
					$response = $rewrite;
				}
			}
		}

		return $response;
	}
	
	public function match($request) {
		if (!$request->isFull()) {
			$path = '/'.$request->getUrl();
			
			if ($rewrite = $this->rewrite($path)) {
				$request->init($rewrite);
			}
		}
		if (!$request->getPath()) {
			$request->init($this->_default);
		}
		
		#print_r($request);
		$default = $config["default"];
		$routes = $config["routes"];
	
		$section = $request->getSection();
		
		$cache = false;
		
		$controllerRoute = $routes->$section;

		if ($request->getController()) {
			$controller = $request->getController();
		} elseif ($controllerRoute->default->controller) {
			$controller = $controllerRoute->default->controller;
		} else {
			$controller = $default['controller'];
		}
		
		$actionRoute = $controllerRoute->$controller;
		
		if ($request->getAction()) {
			$action = $request->getAction();
		} elseif ($controllerRoute->default->action) {
			$action = $controllerRoute->default->action;
		} else {
			$action = $default['action'];
		}
		
		$_action = $action;
		if (preg_match('/^([0-9]+)/ism', $_action)) {
			$_action = "_".$_action;
		}
		
		$mvc_path = $section.'/'.$controller.'/'.$action;
		$traversable_path = str_replace("_", "/", $mvc_path);

		$layout = $this->traverse($traversable_path, 'layout', $routes);
		if (!$layout) {
			$layout = $default['layout'];
		}
		
		$view = $action;
		$template = $action;
		
		return array(
			"section" => $section, 
			"controller" => $controller, 
			"action" => $action, 
			"layout" => (string)$layout, 
			"view" => $view, 
			"template" => $template, 
			"cache" => $cache
		);
	}
	
	public function traverse($path, $item, $object) {
		$path = explode("/", $path);
		$target = $path[0];
		$path = implode("/", array_splice($path, 1));
		
		$_value = (string)$object->$target->$item;
		if (!$_value) {
			$_value = (string)$object->default->$item;
		}
		if ($_value) {
			$value = $_value;
		}
	
		if ($path) {
			$_value = $this->traverse($path, $item, $object->$target);
			if ($_value) {
				$value = $_value;
			}
		}
		return $value;
	}
	
}
?>
