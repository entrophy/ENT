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
			if ($rewrite = $this->rewrite('/'.$request->getBaseUrl())) {
				$request->addDebug('router-rw from: /'.$request->getBaseUrl().' to: '.$rewrite);
				$request->init($rewrite);
			}

			if (!$request->getPath()) {
				$request->init($this->_default);
			}
		}
		
		$section = $request->getSection();
		$controller = $request->getController();
		$action = $view = $template = $request->getAction();
		$full = implode("/", array($section, $controller, $action));
		$traversable = str_replace("_", "/", $full);
		
		echo $traversable;
		print_r($this->routes);
		
		$response = array(
			"section" => $section, 
			"controller" => $controller, 
			"action" => $action, 
			"layout" => (string)$layout, 
			"view" => $view, 
			"template" => $template, 
			"cache" => $cache
		);
		
		print_r($this->find($traversable, array('layout', 'minify')));
		
		return (object) $response;
	}
	
	private function find($path, $items, $context = null) {
		if (!$context) {
			$first = true;
			$context = $this->routes;
		}
	}
	
	public function traverse($path, $item, $object) {
		$path = explode("/", $path);
		$target = $path[0];
		$path = implode("/", array_splice($path, 1));
		
		$_value = (string)$object->$target->$item;
		if (!$_value) {
			$_value = (string)$object->default->$item;
		}
		if ($_value) {
			$value = $_value;
		}
	
		if ($path) {
			$_value = $this->traverse($path, $item, $object->$target);
			if ($_value) {
				$value = $_value;
			}
		}
		return $value;
	}
	
}
?>
