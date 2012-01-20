<?php
class ENT_Core_Template extends ENT_Core_Template_Abstract {
	private $header;
	private $content;
	private $template;
	public function __construct($template, $content, $templateHeader) {
		$this->header = $templateHeader;
		$this->content = $content;
		$this->template = $template;
	}
	
	public function render() {
		ob_start();
		include_once $this->template;
		$output = ob_get_contents();
		ob_end_clean();
		
		return $output;
	}
	public function getContent() {
		return $this->content;
	}
	public function getHeader() {
		return $this->header;
	}
}
?>