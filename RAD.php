<?php
require RAD::registry('rad_path').'Core/functions.php';

final class RAD {
	private static $_app;
	private static $_user;
	private static $_registry = array();
	private static $_basePath;
	public static function register($key, $value) {
		self::$_registry[$key] = $value;
	}
	public static function registry($key) {
		return self::$_registry[$key];
	}
	public static function testRegistry() {
		print_r(self::$_registry);
	}
	
	public static function run() {
		self::app()->getFrontController()->dispatch();
	}
	
	public function getWebBasePath() {
		if (!$this->_basePath) {
			$config = RAD::app()->getConfig()->getWebConfig();
			$this->_basePath = $config['path'];
		}
		return $this->_basePath;
	}
	
	public static function getConfig() {
		return RAD::app()->getConfig();
	}
	
	public static function getEnvironment() {
		return RAD::app()->getEnvironment();
	}
	
	public static function setEnvironment($environment) {
		RAD::app()->setEnvironment($environment);
	}
	
	public static function app() {
		if (!self::$_app) {
			self::$_app = new RAD_Core_App();		
		}
		return self::$_app;
	}
	
	
	public static function getResource($module, $construct = true) {
		return RAD::getModule($module.'/resource', $construct);
	}
	
	public static function isCollection($module) {
		return (get_class($module) == 'RAD_Core_Collection_Abstract' || preg_match('/_Collection$/i', get_class($module)));
	}
	public static function getCollection($module, $construct = true) {
		return RAD::getModule($module.'/collection', $construct);
	}
	
	public static function getHelper($helper) {
		$helperName = str_replace("/", " ", $helper);
		$helperName = ucwords($helperName);
		
		$helperPath = str_replace(" ", "/", $helperName);
		$helperName = "Helper_".str_replace(" ", "_", $helperName);
		
		$helperPath = RAD::registry('project_path').'app/code/helpers/'.$helperPath.'.php';
		
		if (!class_exists($helperName)) {
			require $helperPath;
		}
		return new $helperName;
	}
	
	public static function getSingleton($module) {
		$static = self::getStatic($module);
		return $static::getInstance();
	}
	
	public static function getStatic($module) {
		return self::getModule($module, false);
	}
	
	public static function getViewTemplate($template) {
		return RAD::registry('project_path').'app/design/template/'.$template.'.phtml';
	}
	
	public static function getView($view, $template, $render = true) {
		$view_name = str_replace(array("/", "_"), " ", trim($view));
		$view_name = ucwords($view_name);
		$view_name = str_replace(array(" ", "-"), array("_", " "), $view_name);
		$view_name = ucwords($view_name);
		$view_name = str_replace(" ", "", $view_name);
	
		$view_path = str_replace("_", "/", $view_name);
		$view_path = RAD::registry('project_path').'app/code/views/'.$view_path.'.php';
		$view_name = $view_name.'_View';

		if (class_exists($view_name, false)) {
			$view = $view_name;
		}
		elseif (is_file($view_path)) {
			require $view_path;
			$view = $view_name;
		} else {
			return false;
		}
		
		return new $view($template, $render);
	}
	
	public static function getController($controller, $construct = true) {
		$controller_name = str_replace(array("/", "_"), " ", trim($controller));
		$controller_name = ucwords($controller_name);

		$controller_path = str_replace(" ", "/", $controller_name);
		$controller_name = str_replace(" ", "_", $controller_name);
	
		$controller_path = RAD::registry('project_path').'app/code/controllers/'.$controller_path.'Controller.php';
		$controller_name = $controller_name.'Controller';
		
		if (class_exists($controller_name, false)) {
			$controller = $controller_name;
		}
		elseif (is_file($controller_path)) {
			require $controller_path;
			$controller = $controller_name;
		} else {
			return false;
		}
		
		if ($construct) {
			return new $controller;
		}
		return $controller;
	}
	
	public static function getModule($module, $construct = true) {
		$moduleName = str_replace("/", " ", $module);
		$moduleName = ucwords($moduleName);
		
		$modulePath = str_replace(" ", "/", $moduleName);
		$moduleName = str_replace(" ", "_", $moduleName);

		$moduleLocalPathFull = RAD::registry('project_path').'app/code/modules/'.$modulePath.'.php';
		
		if (class_exists($moduleName, false)) {
			$module = $moduleName;
		}
		elseif (class_exists('RAD_Module_'.$moduleName, false)) {
			$module = 'RAD_Module_'.$moduleName;
		}
		elseif (is_file($moduleLocalPathFull)) {
			require $moduleLocalPathFull;
			$module = $moduleName;
		}
		elseif (is_file(RAD::registry('rad_path').'/Module/'.$modulePath.'.php')) {
			require RAD::registry('rad_path').'/Module/'.$modulePath.'.php';
			$module = 'RAD_Module_'.$moduleName;	
		} 
		else {	
			echo "unable to load module: ".$moduleName;
			return false;
		}
		
		if ($construct) {
			return new $module;
		} else {
			return $module;
		}
	}

}
?>
