<?php
class ENT_Layout_Abstract extends ENT_Frontend {
	protected $contentView;
	protected $file;
	protected $content;
	
	public function setFile($file) {
		$this->file = $file;
	}
	
	public function setContent($content) {
		$this->content = $content;
	}
	
	public function getContent() {
		return $this->content;
	}
	
	public function getContentView() {
		return $this->contentView;
	}
	
	public function setContentView($contentView) {
		$this->contentView = $contentView;
	}
}
?>
