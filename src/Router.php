<?php
class ENT_Router {
	private $_default;
	private $routes;
	private $rewrites;
	
	public function __construct($file) {
		$this->load($file);
	}
	public function load($file) {
		$json = json_decode(file_get_contents($file));
		$this->_default = $json->default;
		$this->routes = $json->routes;
		$this->rewrites = $json->rewrites;
	}
	
	public function getDefault() {
		return $this->_default;
	}
	
	public function rewrite($path) {
		$response = false;
		
		if (count($this->rewrites)) {			
			foreach ($this->rewrites as $match => $rewrite) {
				if ($match === $path) {
					$response = $rewrite;
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
						}
					}
				}
			}
		}

		return $response;
	}
	
	public function match($request) {
		if ($request->getPath() != $this->_default) {
			if ($rewrite = $this->rewrite($request->getBaseUrl())) {
				$request->addDebug('router-rw from: /'.$request->getBaseUrl().' to: '.$rewrite);
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
		
		$view = $path = $template = implode("/", array($section, $controller_name, $action));;
		$full = implode("/", array($section, $controller, $action));
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
			"found" => (object) $this->find($traversable, array('layout', 'minify', 'view', 'template'))
		);
	
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
		
		if ($context = $context->$target) {
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
