<?php
class ENT_Core_Config_Json extends ENT_Core_Config_Abstract {
	public function load($file) {		
		$json = json_decode(file_get_contents($file));
		
		$this->config['db']['host'] = $this->config['database']['host'] = $json->database->host;
		$this->config['db']['database'] = $this->config['database']['database'] = $json->database->database;
		$this->config['db']['prefix'] = $this->config['database']['prefix'] = $json->database->prefix;
		$this->config['db']['user'] = $this->config['database']['user'] = $json->database->user;
		$this->config['db']['password'] = $this->config['database']['password'] = $json->database->password;
		
		$this->config['web']['compress'] = ($json->web->compress == true) ? true : false;
		$this->config['web']['path'] = $json->web->path ? : $this->server_path;
		
		if ($environments = $json->environments) {
			foreach ($environments as $name => $environment) {
				if (!$name) {$name = $environment->name;}
				
				if (!($hosts = $environment->hosts) && $envinronment->host) {
					$hosts = array($envinronment->host);			
				}
				
				$path = $environment->path ? : $this->server_path;
				$type = $environment->type ? : 'production';
				
				if ((!$hosts || $this->matchHost($hosts)) && (!$path || $this->matchPath($path))) {
					$this->environment = array(
						'name' => $name,
						'type' => $type,
						'path' => $path,
						'host' => $this->server_host
					);
					
					unset($name, $environment, $hosts, $path, $type);
					break;
				}
			}
			unset ($environments);
		}
		
		unset($json);
	}
		
}
?>
