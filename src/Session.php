<?php
ENT_Session {
	private static $handler = null;
	private static $started = false;
	private static $namespaces = array();
	public static function start() {
		if (!self::$started) {
			session_start();
			self::$started = true;
		}
	}
	
	public static function getNamespace($namespace) {
		if (!$this->namespaces[$namespace]) {
			$this->namespaces[$namespace] = new ENT_Session_Namespace($namespace);
		}	
		return $this->namespaces[$namespace];
	}
	
	public static function setHandler($handler) {
		self::$handler = $handler;
	}
	
	public static function set($name, $value, $namespace = null) {
		if ($namespace) {
			$_SESSION[$namespace][$name] = $value;
		} else {
			$_SESSION[$name] = $value;
		}
	}
	
	public static function get($name, $namespace = null) {
		if ($namespace) {
			return $_SESSION[$namespace][$name];
		}
		return $_SESSION[$name];
	}
}
?>
