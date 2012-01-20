<?php
class ENT_Core_Config_Xml extends ENT_Core_Config_Abstract {	
	public function load($file) {	
		$config_XML = new SimpleXMLElement(file_get_contents($file));	
		$db = $config_XML->database;

		$server_host = $this->server_host;
		$server_path = $this->server_path;
		
		if ($config_XML->environments && count($environments = $config_XML->environments->children())) {
			foreach ($environments as $_environment) {
				$attributes = $_environment->attributes();
				$_environment_name = (string)$attributes['name'];
				$_environment_type = (string)$_environment->type;
				$_environment_path = (string)$_environment->path;
				
				if ($_environment->hosts) {
					$_environment_hosts = array();
					foreach ($_environment->hosts->host as $_host) {
						$_environment_hosts[] = (string)$_host;
					}
				} else {
					$_environment_hosts = array((string)$_environment->host);
				}
				
				if (in_array($this->server_host, $_environment_hosts) && $_environment_path == $this->server_path) {
					$this->environment = array(
						'name' => $_environment_name,
						'type' => $_environment_type,
						'path' => $_environment_path ? : '/',
						'host' => $server_host
					);
				}
			}
		}
	
		$this->config['db']['host'] = $this->config['database']['host'] = (string)$db->host;
		$this->config['db']['database'] = $this->config['database']['database'] = (string)$db->database;
		$this->config['db']['prefix'] = $this->config['database']['prefix'] = (string)$db->prefix;
		$this->config['db']['user'] = $this->config['database']['user'] = (string)$db->user;
		$this->config['db']['password'] = $this->config['database']['password'] = (string)$db->password;
		
		$this->config['web']['compress'] = ((string)$config_XML->web->compress == 'true') ? true : false;
		$this->config['web']['path'] = (string)$config_XML->web->path ? : '/';
		
		if ($this->environment) {	
			$env_name = $this->environment['name'];
			$env_web = $config_XML->xpath("/config/web/environment[@name='{$env_name}']");
			$env_web = $env_web[0];
			
			$env_db = $config_XML->xpath("/config/database/environment[@name='{$env_name}']");
			$env_db = $env_db[0];

			if ($env_web) {
				$this->config['web']['path'] = (string)$env_web->path ? : $this->config['web']['path'];
			}
			if ($env_db) {
				$this->config['db']['host'] = (string)$env_db->host ? : $this->config['db']['host'];
				$this->config['db']['database'] = (string)$env_db->database ? : $this->config['db']['database'];
				$this->config['db']['prefix'] = (string)$env_db->prefix ? : $this->config['db']['prefix'];
				$this->config['db']['user'] = (string)$env_db->user ? : $this->config['db']['user'];
				$this->config['db']['password'] = (string)$env_db->password ? : $this->config['db']['password'];
			}
		}
		
		unset($config_XML);
	}
}
?>
