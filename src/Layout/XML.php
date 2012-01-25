<?php
class ENT_Layout_XML extends ENT_Layout_Abstract {
	private $xml;
	private $header;
	private $data;
	private $views;
	
	public function setHeader(ENT_Template_Header $header) {
		$this->header = $header;
	}
	public function getHeader() {
		return $this->header;
	}
	
	public function processBlock($xml) {	
		$attr = $xml->attributes();
		
		$dataFull[(string)$attr['name']] = array();
		$data = &$dataFull[(string)$attr['name']];
		
		if (count($attr)) {
			foreach ($attr as $key => $attribute) {
				switch ($key) {
					case 'name':
					case 'template':
					case 'type':
					case 'render':
						$data[$key] = (string)$attribute;
						break;
					default:	
						$data['attributes'][$key] = (string)$attribute;
						break;
				}
			}
		}
		
		if ($xml->action) {
			foreach ($xml->action as $action) {
				$actionAttr = $action->attributes();
				$method = (string)$actionAttr['method'];
				
				$data['actions'][$method][(string)$action] = (string)$action;
			}
		}
		
		$data['type'] = (string)$attr['type'] ? (string)$attr['type'] : "layout_element";
		$data['render'] = $attr['render'] == "false" ? false : true;

		if (sizeof($xml->block)) {
			foreach ($xml->block as $block) {
				$child = $this->processBlock($block);
				$data['children'][key($child)] = current($child);
			}
		}
		
		return $dataFull;
	}
	
	public function processData($xml) {
		$attr = $xml->attributes();
	
		#print_r($xml);
		$dataFull[(string)$attr['name']] = array();
		$data = &$dataFull[(string)$attr['name']];
	
		if ($xml->action) {
			foreach ($xml->action as $action) {
				$actionAttr = $action->attributes();
				$method = (string)$actionAttr['method'];
				
				$data['actions'][$method][(string)$action] = (string)$action;
			}
		}
		
		return $dataFull;
	}
	public function processXML($xml) {	
		$extends = $xml->xpath('@extends');
		$extends = (string)$extends[0][0][0];
		
		$data = $this->processBlock($xml->block);
		if ($xml->data) {
			$data['data'] = $this->processData($xml->data);
		}
	
		if ($extends) {
			$path = 'app/design/layout/'.$extends;
			$extendDataArray = $this->processXML(new SimpleXMLElement(file_get_contents($path)));

			$data = array_merge_recursive_distinct($extendDataArray, $data);
		}
		
		return $data;
	}
	
	public function generateView($data) {
		$view = false;
		if ($data['render'] && $data['name']) {
			if (!$data['template'] && $data['name'] == "content") {
				$view = $this->getContent();
			} else {
				$path = 'app/design/template/';
						
				$viewPath = null;
				
				if ($data['template']) {
					$viewName = array_shift(explode(".phtml", $data['template']));
					
					$view = ENT::getView($viewName, $path.$data['template'], false);
					
					
					/*$viewName = $viewName[0];
					$viewName = str_replace(array("_", "-"), " ", $viewName);
					$viewName = ucwords($viewName);
					$viewName = str_replace(" ", "", $viewName);
					$viewPath = 'app/code/views/';
					$viewName = $viewNamePrefix.$viewName;
					
					$viewName = str_replace("/", " ", $viewName);
					$viewName = ucwords($viewName);
				
					$viewPath = $viewPath . str_replace(" ","/", $viewName) . ".php";
					$viewName = str_replace(" ", "_", $viewName)."_View";*/
				}
				
				/*if ($viewPath && is_file($viewPath) && file_exists($viewPath)) {
					$view = new $viewName($path.$data['template'], false);
				} else {*/
				if (!$view) {
					if ($data['template']) {
						$view = new ENT_View($path.$data['template'], false);
					} else {
						$view = new ENT_View(null, false);
					}
				}
				
				if ($this->getHeader()) {
					$view->setHeader($this->getHeader());
				}
			}
				
			if (count($data['attributes'])) {
				$view->setLayoutAttributes($data['attributes']);
			}
			
			if (count($data['actions'])) {
				foreach ($data['actions'] as $method => $value) {
					$view->addAction($method, $value);
				}
			}
			if (count($data['children'])) {
				foreach ($data['children'] as $item) {
					if ($item['render']) {
						$_view = $this->generateView($item);
						$view->addChildElement($item['name'], $_view);
					}
				}
			}
			
			return $view;
		}
		return false;
	}
	
	public function processViews() {
		if (!$this->views) {
			$data = $this->process();
			foreach ($data as $item) {
				$view = $this->generateView($item);
				$this->views[$item['name']] = $view;
			}
		}
		return $this->views;
	}
	
	public function getViews() {
		return $this->views;
	}
	public function getView($name) {
		return $this->views[$name];
	}
	
	public function process($overwrite = false) {
		if (!$this->data || $overwrite) {
			$this->xml = new SimpleXMLElement(file_get_contents($this->file));
		
			$data = $this->processXML($this->xml);
			
			$this->data = $data;
			ENT::register('template_data', $data['data']);
		}
		return $this->data;
	}
	public function render() {
		ob_start();
		
		$this->processViews();
		$views = $this->getViews();
		
		foreach ($views as $view) {
			if ($view) {
				if (is_object($view)) {
					$view->render();
			
					echo $view->getContent();
				} else {
					echo $view;
				}
			}
		}
		
		$output = ob_get_contents();
		ob_end_clean();
		
		return $output;
	}
}
?>
