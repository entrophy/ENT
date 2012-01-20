<?php
class RAD_Core_Config_Abstract {
	protected $environment = null;
	protected $config;
	protected $config_cache;
	protected $server_host;
	protected $server_path;
	
	private $routes;
	private $routesXML;
	
	public function __construct($file) {
		$this->server_host = str_replace("www.", "", $_SERVER['HTTP_HOST']);
		$this->server_path = str_replace("index.php", "", $_SERVER['SCRIPT_NAME']);
		
		$this->load($file);
		if (file_exists('app/etc/routes.xml')) {
			$routes = 'app/etc/routes.xml';
			
			$routes_XML = new SimpleXMLElement(file_get_contents($routes));	
			$this->routesXML = $routes_XML;
			$routes_default = $routes_XML->default;
		
			$this->routes['default']['section'] = (string)$routes_default->section;
			$this->routes['default']['controller'] = (string)$routes_default->controller;
			$this->routes['default']['action'] = (string)$routes_default->action;
			$this->routes['default']['layout'] = (string)$routes_default->layout;
			$this->routes['routes'] = $routes_XML;
		}		
	}
	
	public function getRoutes() {
		return $this->routesXML;
	}
	
	public function getRoutesConfig() {
		return $this->routes;
	}
	
	public function matchPath($path) {
		return $this->server_path == $path;
	}
	public function matchHost($hosts) {
		return in_array($this->server_host, $hosts);
	}

	public function getEnvironment() {
		return $this->environment;
	}
	
	public function getWebConfig() {
		return $this->config['web'];
	}
	public function doCompress() {
		return $this->config['web']['compress'];
	}
	
	public function getDbConfig() {
		return $this->config['db'];
	}
	
	public function getValue($path, $context = null) {
		if (!$context) {
			$initial = true;
			if ($this->config_cache[$path]) {
				return $this->config_cache[$path];
			}
		
			$context = $this->config;
		}
		
		if (strstr($path, '/')) {
			$_path = explode("/", $path);
			
			$first = array_splice($_path, 0, 1);
			$first = $first[0];
			
			$value = $this->getValue(implode("/", $_path), $context[$first]);
		} else {
			$value = $context[$path];
		}
		
		if ($initial) {
			$this->config_cache[$path] = $value;
		}

		return $value;
	}
}
?>
