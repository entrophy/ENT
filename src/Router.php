<?php
class ENT_Router {
	private $_default;
	private $routes;
	private $rewrites;
	private $rewrite_cache;
	
	public function __construct($file) {
		$this->load($file);
	}
	public function load($file) {
		if (file_exists($file)) {
			if (($json = json_decode(file_get_contents($file))) !== NULL) {
				$this->_default = $json->default;
				$this->routes = $json->routes;
				$this->rewrites = (object)$this->flattenRewrites($json->rewrites ? : $json->rewrite);
				unset($json);
			} else {
				throw new ENT_Exception('Malformed routes file: '.$file.' (json decoding failed)');
			}
		} else {
			throw new ENT_Exception('Unable to load routes file: '.$file.' (file doesn\'t exist)');
		}
	}
	
	public function getDefault() {
		return $this->_default;
	}
	
	public function flattenRewrites($item, $prefix = '', $values = null) {
		if (!$values) {
			$values = array();
		}
		$item = (array)$item;
		
		foreach ($item as $key => $value) {
			if (is_object($value)) {
				$values = array_merge($values, $this->flattenRewrites($value, ($prefix ? $prefix.'/'.$key : $key), $values));
			} else {
				if ($prefix) {$prefix .= '/';}
				$values[($key ? ($prefix.$key) : $prefix)] = $value;
			}
		}

		return $values;
	}
	public function rewrite($path) {		
		if ($this->rewrite_cache[$path] === null) {
			$response = false;
			if (count($this->rewrites)) {	
				if ($path == '') { $path = '_empty_'; }

				foreach ($this->rewrites as $match => $rewrite) {
					if ($match == $path) {
						$response = $rewrite;
						break;
					} else {			
						if (preg_match_all('/\:(.+?)(?:\/|$)/ism', $match, $tags) !== 0) {
							$tags = $tags[1];
							$regex = '^'.preg_replace('/\:(.+?)(?:\/|$)/ism', '([^\/]+?)(?:\/|$)', str_replace('\:', ':', preg_quote($match, '/'))).'$';
					
							if (preg_match('/'.$regex.'/ism', $path, $matches) !== 0) {
								$values = array_slice($matches, 1);
								$response = $rewrite;
								$matched = $regex;
						
								if (count($tags)) {
									foreach ($tags as $index => $tag) {
										if (strpos($response, ':'.$tag) === false) {
											$response .= '/'.$tag.'/'.$values[$index];
										} else {
											$response = str_replace(':'.$tag, $values[$index], $response);
										}
									}
								}
								break;
							}
						}
						
						if (strpos($match, '*') !== false) {
							$regex = '^'.str_replace("\*", '(.+)', preg_quote($match, '/')).'$';
							if (preg_match('/'.$regex.'/ism', $path, $matches) !== 0) {
								$response = $rewrite;
								break;
							}
						}
					}
				}
			}
			
			$this->rewrite_cache[$path] = $response;
		}

		return $this->rewrite_cache[$path];
	}
	
	public function match($request) {
		if ($request->getPath() != $this->_default) {
			if ($rewrite = $this->rewrite($request->getPath())) {
				$request->addDebug('router-rw from: /'.$request->getPath().' to: '.$rewrite);
				$request->init($rewrite);
			}

			if (!$request->getPath()) {
				$request->init($this->_default);
			}
		}
		
		$section = $request->getSection();
		$controller = $request->getController();
		$controller_name = str_replace("_", "/", $controller);
		$action = $request->getAction();
		$method = $request->getMethod();
		
		$view = $path = $template = implode("/", array_filter(array($section, $controller_name, $action)));
		$full = implode("/", array_filter(array($section, $controller, $action)));
		$traversable = str_replace("_", "/", $full);
		
		$response = array(
			"method" => $method,
			"path" => $path,
			"section" => $section, 
			"controller" => $controller, 
			"action" => $action, 
			"layout" => (string)$layout, 
			"view" => $view, 
			"template" => $template, 
			"cache" => $cache,
			"found" => (object) $this->find($traversable, array('layout', 'minify', 'view', 'template', 'default'))
		);

		$this->_default = $response['found']->default ? : $this->_default;
	
		return (object) $response;
	}
	
	private function find($path, $items, $context = null) {
		if (!$context) {
			if (!is_array($items)) {
				$items = array($items);
			}
			$first = true;
			$context = $this->routes;
		}
		if (strpos($path, '/') === false) {
			$target = $path;
			$end = true;
		} else {
			$path = explode('/', $path);
			$target = $path[0];
			$end = false;
		}
		
		if ($target && $context = $context->$target) {
			$values = array();
			foreach ($items as $item) {
				$values[$item] = $context->$item;
			}

			if (!$end) {
				$values = array_merge($values, array_filter($this->find(implode("/", array_slice($path, 1)), $items, $context)));
			}
			return $values;
		} else {
			return array();
		}
	}
	
}
?>
