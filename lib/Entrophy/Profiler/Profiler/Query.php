<?php
class Entrophy_Profiler_Query {
	private $name;
	private $step;
	private $start = null;
	private $stop = null;
	private $duration = null;
	public function __construct($sql) {
		$this->sql = $sql;
		$this->start = microtime(true);
	}
	
	public function stop() {
		$this->stop = microtime(true);
		$this->duration = $this->stop - $this->start;
	}
	public function getDuration() {
		return $this->duration;
	}
	public function getStart() {
		return $this->start;
	}
	public function getStop() {
		return $this->stop;
	}
	public function getSql() {
		return $this->sql;
	}
	
	public function setStep($step) {
		$this->step = $step;
	}
	public function getStep() {
		return $this->step;
	}

}
?>
