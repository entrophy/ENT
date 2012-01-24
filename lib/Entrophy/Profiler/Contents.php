<?php
class Entrophy_Profiler_Contents {
	protected $file;
	
	public function __construct($file) {
		$this->file = $file;
	}
	
	public function getTotalDuration() {
		$duration = 0;
		foreach (Entrophy_Profiler::getSteps() as $step) {
			$duration += $step->getDuration();
		}
		
		return $duration; 
	}
	
	public function getFirstStart() {
		$steps = Entrophy_Profiler::getSteps();
		return $steps[0]->getStart();
	}
	
	public function displayStep($step) {
		$view = new Entrophy_Profiler_Contents_Step('template/contents/step.phtml', $step);
		return $view->render();
	}
	public function displayQuery($step) { 
		$view = new Entrophy_Profiler_Contents_Query('template/contents/query.phtml', $step);
		return $view->render();
	}
	
	public function displayStart($time) {
		$diff = $time - $this->getFirstStart();
		return number_format($diff * 1000, 1, '.', '');
	}
	public function displayTime($time) {
		return number_format($time * 1000, 1, '.', '');
	}
	
	public function render() {
		ob_start();
		include $this->file;
		$contents = ob_get_contents();
		ob_end_clean();	
		return $contents;
	}
}
?>
