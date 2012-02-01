<?php
abstract class ENT_Controller_REST extends ENT_Controller_Abstract {
	protected $type = 'REST';
	
	public function init() {
		$this->frontController->renderLayout(false);
		$this->frontController->renderView(false);
	}
}
?>
