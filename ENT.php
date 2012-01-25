<?php
require ENT::registry('ent_path').'src/functions.php';

final class ENT {
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
		ENT::getLibrary('entrophy/database');
		ENT::getLibrary('entrophy/profiler');
			
		if (self::getEnvironment() && self::getEnvironment()->getType() == 'development') {
			Entrophy_Profiler::start();
		}
		self::app()->getFrontController()->dispatch();
		return $this;
	}
	
	public function getWebBasePath() {
		if (!$this->_basePath) {
			$this->_basePath = self::app()->getConfig()->getWeb()->path;
		}
		return $this->_basePath;
	}
	
	public static function getConfig() {
		return self::app()->getConfig();
	}
	
	public static function getEnvironment() {
		return self::app()->getEnvironment();
	}
	
	public static function setEnvironment($environment) {
		self::app()->setEnvironment($environment);
		return $this;
	}
	
	public static function app() {
		if (!self::$_app) {
			self::$_app = new ENT_App();		
		}
		return self::$_app;
	}
	
	public static function isCollection($module) {
		return (get_class($module) == 'ENT_Collection_Abstract' || preg_match('/_Collection$/i', get_class($module)));
	}
	public static function getCollection($module, $construct = true) {
		return self::getModule($module.'/collection', $construct);
	}
	
	public static function getHelper($helper) {
		list($name, $path) = self::resolveClass($helper);

		$name = "Helper_".$name;	
		$path = ENT::registry('project_path').'app/code/helpers/'.$path.'.php';
		
		if (!class_exists($name)) {
			require $path;
		}
		return new $name;
	}
	
	public static function getSingleton($module) {
		$static = self::getStatic($module);
		return $static::getInstance();
	}
	
	public static function getStatic($module) {
		return self::getModule($module, false);
	}
	
	public static function getViewTemplate($template) {
		return ENT::registry('project_path').'app/design/template/'.$template.'.phtml';
	}
	
	public static function resolveClass($path) {
		$class_name = str_replace(array("/", "_"), " ", trim($path));
		$class_name = ucwords($class_name);
		
		$class_name = str_replace(array(" ", "-"), array("_", " "), $class_name);
		$class_name = ucwords($class_name);
		$class_name = str_replace(" ", "", $class_name);

		$class_path = str_replace("_", "/", $class_name);
		#$class_name = str_replace(" ", "_", $class_name);
		
		return array($class_name, $class_path);
	}
	
	public static function getView($view, $template, $render = true) {
		list($name, $path) = self::resolveClass($view);

		$path = ENT::registry('project_path').'app/code/views/'.$path.'.php';
		$name = $name.'_View';

		if (class_exists($name, false)) {
			$view = $name;
		}
		elseif (is_file($path)) {
			require $path;
			$view = $name;
		} else {
			return false;
		}
		
		return new $view($template, $render);
	}
	
	public static function getLibrary($library) {
		list($name, $path) = self::resolveClass($library);
		
		$path = ENT::registry('ent_path').'lib/'.$path.'.php';
		if (!class_exists($name, false)) {
			require $path;
		}
	}
	
	public static function getController($controller, $construct = true) {
		list($name, $path) = self::resolveClass($controller);
	
		$path = ENT::registry('project_path').'app/code/controllers/'.$path.'Controller.php';
		$name = $name.'Controller';
		
		
		if (class_exists($name, false)) {
			$controller = $name;
		}
		elseif (is_file($path)) {
			require $path;
			$controller = $name;
		} else {
			return false;
		}
		
		if ($construct) {
			return new $controller;
		}
		return $controller;
	}
	
	public static function getModule($module, $construct = true) {
		list($name, $path) = self::resolveClass($module);

		$localPath = ENT::registry('project_path').'app/code/modules/'.$path.'.php';
		
		if (class_exists($name, false)) {
			$module = $name;
		}
		elseif (class_exists('ENT_Module_'.$name, false)) {
			$module = 'ENT_Module_'.$name;
		}
		elseif (is_file($localPath)) {
			require $localPath;
			$module = $name;
		}
		elseif (is_file(ENT::registry('ENT_path').'src/Module/'.$path.'.php')) {
			require ENT::registry('ENT_path').'src/Module/'.$path.'.php';
			$module = 'ENT_Module_'.$name;	
		} 
		else {	
			echo "unable to load module: ".$name;
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
