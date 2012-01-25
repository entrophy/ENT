<?php
class ENT_Layout_PHPHTML extends ENT_Layout_Abstract {
	public function render() {
		ob_start();
		include_once $this->file;
		$output = ob_get_contents();
		ob_end_clean();
		
		echo $output;
	}
}
?>
