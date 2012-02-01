<?php
class ENT_Frontend {
	private $base;
	private $currentPath;
	public function getBase() {
		if (!$this->base) {
			$this->base = ENT::app()->getConfig()->getWeb()->path;
		}
		return $this->base;
	}
	public function url($path) {
		if ($path[0] == '/') {
			$path[0] = '';
		}
	
		return $this->getBase().$path;
	}
	
	public function helper($name) {
		return ENT::getHelper($name);
	}
	
	public function getBlock($path, $values = null) {
		$template = ENT::getViewTemplate($path);
		if ($view = ENT::getView($path, $template, false)) {			
			if ($values) {
				if (!is_array($values)) {
					$values = array($values);
				}
				
				$view->setValues($values);
			}
			
			$view->render();
			$output = $view->getContents();
			unset($view);
			return $output;
		} else {
			ob_start();
			include $template;
			$output = ob_get_contents();
			ob_end_clean();
			return $output;
		}
	}
	public function getChildBlock($path, $values = null) {		
		$path = $this->currentPath.$path;
		return $this->getBlock($path, $values);
	}
	public function getSiblingBlock($path, $values = null) {		
		$currentPath = explode("/", $this->currentPath);
		array_splice($currentPath, -2, 2);
		$path = implode("/", $currentPath)."/".$path;
		return $this->getBlock($path, $values);
	}
	
	public function getHtml($path, $values = null) {
		return $this->getBlock($path, $values);
	}
	public function getChildHtml($path, $values = null) {
		return $this->getChildBlock($path, $values);
	}
	public function getSiblingHtml($path, $values = null) {
		return $this->getSiblingBlock($path, $values);
	}
}
?>
