<?php
class ENT_Core_Router {
	public static function load($path) {
		if (file_exists($file = ENT::registry('project_path').'app/etc/routes.json')) {
			return new ENT_Core_Router_Json($file, $path);
		} elseif (file_exists($file = ENT::registry('project_path').'app/etc/routes.xml')) {
			return new ENT_Core_Router_Xml($file, $path);
		}
	}

	public function __construct() {
	
	}
	public function match($request) {
		$config = ENT::app()->getConfig()->getRoutesConfig();

		$default = $config["default"];
		$routes = $config["routes"];
	
		$section = $request->getSection() ? $request->getSection() : $default['section'];
		
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
		
		return array("section" => $section, "controller" => $controller, "action" => $action, "layout" => (string)$layout, "view" => $view, "template" => $template, "cache" => $cache);
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
