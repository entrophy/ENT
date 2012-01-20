<?php
class ENT_Profiler_Contents_Step extends ENT_Profiler_Contents {
	private $step;
	public function __construct($file, $step) {
		$this->file = $file;
		
		$this->step = $step;
	}
	
	public function getStep() {
		return $this->step;
	}
}
?>
