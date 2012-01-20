<?php
class ENT_Core_Template_PHPHTML extends ENT_Core_Template_Abstract implements ENT_Core_Template_Interface {
	public function render() {
		ob_start();
		include_once $this->file;
		$output = ob_get_contents();
		ob_end_clean();
		
		echo $output;
	}
}
?>