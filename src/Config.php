<?php
class ENT_Config {
	protected $environment = null;
	protected $config;
	protected $config_cache;
	protected $server_host;
	protected $server_path;
	
	public function __construct($file) {
		$this->server_host = str_replace("www.", "", $_SERVER['HTTP_HOST']);
		$this->server_path = str_replace("index.php", "", $_SERVER['SCRIPT_NAME']);
		
		$this->load($file);
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
	
	public function getWeb() {
		return $this->config->web;
	}
	public function doCompress() {
		return $this->config->web->compress;
	}
	
	public function getDatabase() {
		return $this->config->database;
	}
	
	public function getValue($path, $context = null) {
		if (!$context) {
			$initial = true;
			if ($this->config_cache[$path]) {
				return $this->config_cache[$path];
			}
		
			$context = $this->config;
		}
		
		if (strpos($path, '/') !== false) {
			$_path = explode("/", $path);
			
			$first = array_splice($_path, 0, 1);
			$first = $first[0];
			
			$value = $this->getValue(implode("/", $_path), $context->$first);
		} else {
			$value = $context->$path;
		}
		
		if ($initial) {
			$this->config_cache[$path] = $value;
		}

		return $value;
	}

	public function load($file) {		
		$json = json_decode(file_get_contents($file));

		$this->config = $json;
		$this->config->web->path = $this->config->web->path ? : $this->server_path;
		
		if ($environments = $json->environments) {
			foreach ($environments as $name => $environment) {
				if (!$name) {$name = $environment->name;}
				
				if (!($hosts = $environment->hosts) && $envinronment->host) {
					$hosts = array($envinronment->host);			
				}
				
				$path = $environment->path ? : $this->server_path;
				$type = $environment->type ? : 'production';
				
				if ((!$hosts || $this->matchHost($hosts)) && (!$path || $this->matchPath($path))) {
					$this->config->environment = (object)$this->environment = array(
						'name' => $name,
						'type' => $type,
						'path' => $path,
						'host' => $this->server_host
					);
					
					unset($name, $environment, $hosts, $path, $type, $this->config->environments);
					break;
				}
			}
			unset ($environments);
		}
		unset($json);
	}
		
}
?>
