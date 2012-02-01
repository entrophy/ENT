<?php
class ENT_View extends ENT_Frontend {
	protected $content;
	protected $rendered = false;
	protected $template;
	protected $childElements = array();
	protected $actions = array('addCSS' => array(), 'addJS' => array());
	protected $header;
	protected $data;
	protected $layout_attributes;
	protected $values;

	public function __construct($template, $render = true) {
		$this->template = $template;
		if ($render) {
			$this->render();
		}
	}
	public function render() {
		$path = str_replace(array(ENT::registry('project_path'), "app/design/template/", ".phtml"), "", $this->template);
		$this->currentPath = $path."/";
	
		ob_start();
		
		if ($this->template) {
			include $this->template;
		} else {
			if (count($children = $this->getChildElements())) {
				foreach ($children as $child) {
					if (is_object($child)) {
						$child->render();
					}
					echo $child;
					unset($child);
				}
				unset($children);
			}
		}		
	
		$this->content = ob_get_contents();
		ob_end_clean();
		
		$this->rendered = true;
		return $this;
	}
	
	public function setValues(array $values) {
		foreach ($values as $key => $value) {
			if (is_array($value)) {
				if (is_object($value[0]) && !is_string($key)) {
					$name = strtolower(get_class($value[0]));
			
					if (substr($name, -1, 1) == 'y') {
						$name = substr($name, 0, -1).'ies';
					} else {
						$name = $name.'s';
					}
				} else {
					$name = $key;
				}
		
				$this->setValue(strtolower($name), $value);
			} else {
				if (is_object($value) && !is_string($key)) {
					$name = strtolower(get_class($value));
				} else {
					$name = $key;
				}
			
				$this->setValue($name, $value);
			}
		}
	}
	public function setValue($key, $value) {
		$this->values[$key] = $value;
		$this->$key = $value;
	}
	public function getValue($key) {
		return $this->values[$key];
	}
	
	public function getContent() {
		if (!$this->rendered) {
			$this->render();
		}
		return $this->content;
	}
	public function getContents() {
		return $this->getContent();
	}
	
	public function __toString() {
		return $this->getContent();
	}
	
	public function setHeader(ENT_Template_Header $header) {
		$this->header = $header;
		return $this;
	}
	public function getHeader() {
		return $this->header;
	}
	
	public function setLayoutAttributes($attributes) {
		$this->layout_attributes = $attributes;
		return $this;
	}
	public function getLayoutAttribute($name) {
		return $this->layout_attributes[$name];
	}
	
	public function addChildElement($key, $object) {
		if (!$key) {
			$key = count($this->childElements);
		}
		$this->childElements[$key] = $object;
	}
	public function getChildElement($key) {
		return $this->childElements[$key] ? $this->childElements[$key] : false;
	}
	
	public function getChildElements() {
		return $this->childElements;
	}	
	
	public function addData($key, $value) {
		$this->data[$key] = $value;
		return $this;
	}
	
	public function addAction($method, $value) {
		$this->actions[$method] = $value;
		return $this;
	}
	
	public function getActions($method) {
		return $this->actions[$method];
	}
	
	/*public function __call($name, $arguments) {
	
	}*/
}
?>
