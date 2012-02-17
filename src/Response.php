<?php
class ENT_Response {
	private $type = 'html';
	private $content;
	private $status = 200;
	private $statuses = array(
		200 => 'OK',
		404 => 'Not Found'
	);

	public function setType($type) {
		$this->type = $type;
		return $this;
	}
	public function setContent($content) {
		$this->content = $content;
		return $this;
	}
	public function getType() {
		return $this->type;
	}
	
	public function setStatus($status) {
		$this->status = $status;
		return $this;
	}
	public function getStatus() {
		return $this->status;
	}
	
	public function setSession($key, $value) {
		$_SESSION[$key] = $value;
		return $this;
	}
	public function unsetSession($key) {
		$_SESSION[$key] = null;
		unset($_SESSION[$key]);
	}
	public function setCookie($key, $value, $expire, $path = '/') {
		$expire = time() + $expire;
		setcookie($key, $value, $expire, $path);
		return $this;
	}
	public function deleteCookie($key) {
		$this->unsetCookie($key);
		return $this;
	}
	public function unsetCookie($key, $path = '/') {
		$expire = time() - 60 * 60;
		setcookie($key, null, $expire, $path);
		return $this;
	}	
	
	public function getContent() {
		return $this->content;
	}
	public function send() {
		$compress = ENT::app()->getConfig()->getValue('web/compress');
	
		switch ($this->type) {
			case 'pdf':
				header("Content-type: application/pdf"); 
				break;
			case 'csv':
				header("Content-Type: application/force-download");
				header("Content-Type: application/octet-stream");
				header("Content-Type: application/download");
				break;
			case 'jpg':
				header("Content-type: image/jpeg");
				break;
			case 'gif':
				header("Content-type: image/gif");
				break;
			case 'png':
				header("Content-type: image/png");
				break;
			case 'javascript':
			case 'json':
				header("Content-type: text/javascript");
				break;
			case 'html':
			default:
				header('Content-Type: text/html; charset=utf-8'); 
				break;
		}
		
		if ($this->status != 200) {
			header('HTTP/1.1 '.($this->statuses[$this->status] ? $this->status.' '.$this->statuses[$this->status] : $this->status));
		}
		
		if ($this->content) {
			$content = $this->content;
			
			if ($compress) {
				$encoding = false;
				if (strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'x-gzip') !== false) {
					$encoding = 'x-gzip';
				}
				elseif (strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false) {
					$encoding = 'gzip';
				}
				
				if ($encoding) {
					$size = strlen($content);
					header('Content-Encoding: '.$encoding);
					$content = gzencode($content, 6);
				} 
			}
			
			echo trim($content);
		}
		
		return $this;
	}
}
?>
