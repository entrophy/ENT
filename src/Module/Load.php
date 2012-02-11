<?php
class ENT_Module_Load {
	protected static $cache;
	protected static $module_key;
	
	const ID = 'module/load=id';
	const DATA = 'module/load=data';
	const MIXED = 'module/load=mixed';
	const INIT = 'module/load=init';
	const KEY = 'module/load=key';
	
	protected static function getCacheKey($data, $type, $object = true) {
		return sha1(static::$module_key.((int) $object).(is_object($data) ? get_class($data).$data->getID() : serialize($data)).$type);
	}
	
	public function clearCache($key = null) {
		if ($key) {
			unset(static::$cache[$key]);
		} else {
			$this->cache = array();
		}
	}
	
	public static function factory($data, $type = null, $object = true) {
		if (!$type) {
			if (is_array($data)) {
				$type = static::MIXED;
			} else if (is_numeric($data)) {
				$type = static::ID;
			}
		}
		
		if ($type != static::DATA && $type != static::INIT) {
			$cache_key = static::getCacheKey($data, $type, $object);
		}
		
		if ($type != static::DATA && $type != static::INIT && ($cached = static::$cache[$cache_key])) {
			return $cached;
		} else {
			if ($object) {
				$item = ENT::getModule(static::$module_key);
			}
			
			$dao = str_replace("_Load", "", get_called_class()).'_DAO';
			$dao = $dao::getInstance();

			switch ($type) {
				case static::DATA:
					// Redundant for clarification
					$data = $data;
					break;
				case static::ID:
					$data = $dao->load(array('id' => $data));
					break;
				case static::MIXED:
					$data = $dao->load($data);
					break;
				case static::INIT:
					$data = array();
					break;
				default:
					$data = static::type($data, $type, $dao);
					break;
			}
			
			$additional = null;
			
			if (is_array($data[0]) || is_object($data[1]) || is_array($data[1])) {
				$_data = array_shift($data);	
				$additional = $data;
				$data = $_data;
			}
			
			if ($object) {
				$item->infuse($data, $additional);
				if ($additional) {
					$item->setAdditional($additional);
				}
			}
			
			if ($type == static::DATA) {
				if ($data['id']) {
					$_cache_key = static::getCacheKey((int)$data['id'], static::ID);
					static::$cache[$_cache_key] = $item;
				}
				if ($data['key']) {
					$_cache_key = static::getCacheKey($data['key'], static::KEY);
					static::$cache[$_cache_key] = $item;
				}
			} else {
				static::$cache[$cache_key] = $item;
			}
			
			return ($object ? $item : $data);
		}
	}
	
	protected static function type($data, $type, $dao) {
		
	}
}
?>
