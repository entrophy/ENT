<?php
class RAD_Core_Request_Cache {
	private $params = array();
	private $options = array('max_age' => 30);
	private $request;
	private $enabled = false;
	private $viable = null;
	private $hash = null;

	public function __construct($request) {
		$this->request = $request;
	}
	
	public function enable() {
		$this->enabled = true;
	}	
	public function disable() {
		$this->enabled = false;
	}
	public function isEnabled() {
		return $this->enabled;
	}
	
	public function setParam($key, $value) {
		$this->params[$key] = $value;
	}
	public function removeParam($key) {
		unset($this->params[$key]);
	}
	
	public function setOptions($options) {
		foreach ($options as $key => $value) {
			$this->setOption($key, $value);
		}
	}
	public function setOption($key, $value) {
		$this->options[$key] = $value;
	}
	public function removeOptions($key) {
		unset($this->options[$key]);
	}
	
	public function getFileName() {
		$file = str_replace(array("?", "&", "/"), "_", $this->request->getUrl());
		$file = preg_replace("/_gclid=(.+?)(?:_|$)/ism", "_", $file);

		foreach ($this->params as $key => $value) {
			$file .= '_'.$key.'='.$value;
		}
		
		if (strlen($file) > 50) {
			$file = md5($file);
		}
		
		$file .= '.phtml';
		return $file;
	}
	
	public function isViable() {
		if ($this->enabled && ($this->viable == null || $this->hash != md5($this->getFileName()))) {
			if (file_exists('var/cache/request/'.$this->getFileName()) && filemtime('var/cache/request/'.$this->getFileName()) > (time() - $this->options['max_age'] * 60)) {
				$this->viable = true;
			} else {
				$this->viable = false;
			}
		}
		$this->hash = md5($this->getFileName());
		
		#return false;
		return $this->viable;
	}
	
	public function save($content) {
		file_put_contents('var/cache/request/'.$this->getFileName(), $content);
	}
	public function getContent() {
		return file_get_contents('var/cache/request/'.$this->getFileName());
	}	
}
?>
