<?php
class ENT_Session {
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
		if (!self::$namespaces[$namespace]) {
			self::$namespaces[$namespace] = new ENT_Session_Namespace($namespace);
		}	
		return self::$namespaces[$namespace];
	}
	
	public static function setHandler($handler) {
		self::$handler = $handler;
		session_set_save_handler(
			array($handler, 'open'), 
			array($handler, 'close'), 
			array($handler, 'read'), 
			array($handler, 'write'), 
			array($handler, 'destroy'), 
			array($handler, 'gc')
		);
	}
	
	public static function set($name, $value, $namespace = null) {
		if ($namespace) {
			if ($value === null) {
				unset($_SESSION[$namespace][$name]);
			} else {
				$_SESSION[$namespace][$name] = $value;
			}
		} else {
			if ($value === null) {
				unset($_SESSION[$name]);
			} else {
				$_SESSION[$name] = $value;
			}
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
