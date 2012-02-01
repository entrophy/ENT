<?php
abstract class ENT_Template_Abstract {
	private $basePath;
	protected $currentPath;
	protected $file;
	protected $content;
	protected $contentView;
	public function getBasePath() {
		if (!$this->basePath) {
			$config = ENT::app()->getConfig()->getWeb();
			$this->basePath = $config->path;
		}
		return $this->basePath;
	}
	
	public function setFile($file) {
		$this->file = $file;
	}
	
	public function setContent($content) {
		$this->content = $content;
	}
	
	public function getContent() {
		return $this->content;
	}
	
	public function helper($name) {
		return ENT::getHelper($name);
	}
	
	public function getRequest() {
		return ENT::app()->getFrontController()->getRequest();
	}
	
	public function generateURL($path) {
		return $this->getBasePath().$path;
	}
	
	public function __($key) {
		$args = func_get_args();
		$args = array_slice($args, 1);
		$translate = ENT::getSingleton('translate');
		return $translate->getTranslation($key, $args);
	}
	
	public function urlFriendly($name) {
		$name = strtolower($name);
		$name = str_replace(" ", "-", $name);
		return $name;
	}
	
	public function prepareUrl($path) {
		if (substr($path, 0, 1) == '/') {
			$path = substr_replace($path, '', 0, 1);
		}
		return $path;
	}
	
	public function url($path) {
		$path = $this->prepareUrl($path);
		return $this->generateURL($path);
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
	
	public function getContentView() {
		return $this->contentView;
	}
	public function setContentView($contentView) {
		$this->contentView = $contentView;
	}
}
?>
