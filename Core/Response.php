<?php
class RAD_Core_Response {
	private $type = 'html';
	private $content;

	public function setType($type) {
		$this->type = $type;
	}
	public function setContent($content) {
		$this->content = $content;
	}
	public function getType() {
		return $this->type;
	}
	
	public function setSession($key, $value) {
		$_SESSION[$key] = $value;
	}
	public function unsetSession($key) {
		$_SESSION[$key] = null;
		unset($_SESSION[$key]);
	}
	public function setCookie($key, $value, $expire, $path = '/') {
		$expire = time() + $expire;
		setcookie($key, $value, $expire, $path);
	}
	public function deleteCookie($key) {
		$this->unsetCookie($key);
	}
	public function unsetCookie($key, $path = '/') {
		$expire = time() - 60 * 60;
		setcookie($key, null, $expire, $path);
	}	
	
	public function getContent() {
		return $this->content;
	}
	public function send() {
		$compress = RAD::app()->getConfig()->doCompress();
	
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
		
		if ($this->content) {
			$content = $this->content;
			#echo str_replace(array("\t", "\r\n", "\r", "\n"), "", $this->content);
			
			if ($compress) {
				$encoding = false;
				if (strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'x-gzip') !== false ) {
					$encoding = 'x-gzip';
				}
				elseif (strpos($_SERVER['HTTP_ACCEPT_ENCODING'],'gzip') !== false ) {
					$encoding = 'gzip';
				} else {
					$encoding = false;
				}
	
	
				if ($encoding) {
					$size = strlen($content);
					
					#if ($size > 50) {
						header('Content-Encoding: '.$encoding);
						#print("\x1f\x8b\x08\x00\x00\x00\x00\x00");
						
						$content = gzencode($content, 6);
						#$content = substr($content, 0, $size);
					#}
				} 
			}
			
			echo trim($content);
		}
	}
}
?>
