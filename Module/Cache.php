<?php
class RAD_Module_Cache {
	const TYPE_DATA = 'cache/type=data';
	const TYPE_PAGE = 'cache/type=page';
	
	private $file;
	private $fileName;
	private $content;
	private $fileTimestamp;
	private $params;
	private $maxAge;

	public function load($name, $type = self::TYPE_PAGE) {
		$this->fileName = $name;
		
		foreach ($this->params as $key => $value) {
			$name .= "_".$key."-".$value;
		}
		
		$this->type = $type;
		switch ($this->type) {
			case self::TYPE_DATA:
				$this->file = 'var/cache/data/'.$name.'.php';
				break;
			case self::TYPE_PAGE:
				$this->file = 'var/cache/page/'.$name.'.html';
				break;
		}

		if (is_file($this->file) && file_exists($this->file)) {
			$this->exists = true;
			$this->timestamp = filemtime($this->file);
		}
	}
	
	public function setType($type) {
		$this->type = $type;
	}

	public function setParam($key, $value) {
		$this->params[$key] = $value;
	}
	
	public function maxAge($days, $hours = 0, $minutes = 0) {
		$hours = $hours + $days * 24;
		$minutes = $minutes + $hours  * 60;
		$time = $minutes * 60;
		$this->maxAge = time() - $time;
	}
	
	public function viable() {
		if ($this->exists && ($this->timestamp >= $this->maxAge)) {
			return true;
		}
		return false;
	}
	
	public function save($content) {
		switch ($this->type) {
			case self::TYPE_DATA:
				$content = serialize($content);
				break;
			case self::TYPE_PAGE:
			
				break;
		}
		
		createFolder(dirname($this->file));
		chmod($this->file, 0777);
		recursive_chmod(dirname($this->file), 0777);
		file_put_contents($this->file, $content);
		$this->load($this->file, $this->type);
	}
	
	public function getContent() {
		if (!$this->content) {
			$content = file_get_contents($this->file);
			switch ($this->type) {
				case self::TYPE_DATA:
					$content = unserialize($content);
					break;
				case self::TYPE_PAGE:
				
					break;
			}
			
			$this->content = $content;
		}
		return $this->content;
	}
}
?>