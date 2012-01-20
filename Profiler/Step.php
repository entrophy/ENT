<?php
class ENT_Profiler_Step {
	private $name;
	private $parent;
	private $start = null;
	private $stop = null;
	private $duration = null;
	private $children = array();
	private $queries = array();
	private $queries_sorted = false;
	private $id = 0;
	public function __construct($name) {
		$this->name = $name;
		$this->start = microtime(true);
		$this->id = ENT_Profiler::generateStepID();
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
	public function getName() {
		return $this->name;
	}
	public function getID() {
		return $this->id;
	}
	
	public function setParent($step) {
		$this->parent = $step;
	}
	public function getParent() {
		return $this->parent;
	}
	public function addChild($step) {
		$this->children[] = $step;
		$step->setParent($this);
	}
	public function getChildren() {
		return $this->children;
	}
	public function getSteps() {
		return $this->getChildren();
	}
	
	public function getQueryDuration() {
		$duration = 0;
		if (count($queries = $this->getQueries())) {
			foreach ($queries as $query) {
				$duration += $query->getDuration();
			}
		}
		return $duration;
	}
	public function addQuery($query) {
		$this->queries[] = $query;
		$query->setStep($this);
	}
	
	public function sortQueries($a, $b) {
		if ($a->getDuration() == $b->getDuration()) {
			#return strcomp($a->getSql(), $b->getSql());
		}
		return $a->getDuration() < $b->getDuration() ? 1 : -1;
	}
	
	public function getQueries() {
		if (!$this->queries_sorted) {
			usort($this->queries, array($this, 'sortQueries'));
			$this->queries_sorted = true;
		}
		return $this->queries;
}
	
}
?>
