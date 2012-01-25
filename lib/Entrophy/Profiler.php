<?php
spl_autoload_register(array('Entrophy_Profiler', 'autoload'));
class Entrophy_Profiler {
	private static $running = false;
	private static $current_step = null;
	private static $current_query = null;
	private static $steps;
	private static $queries;
	private static $step_id = 0;	
	public static $render = false;
	
	public static function autoload($class) {
		if (strpos($class, 'Entrophy_Profiler_') === 0) {
			if (class_exists($class, false) || interface_exists($class, false)) {
				return;
			}
		
			$file = str_replace(array('_', 'Entrophy/'), array('/', ''), $class).'.php';
			include $file;
		}
	}
	 
	public static function getSteps() {
		return self::$steps;
	}
	
	public static function start() {	
		self::$running = true;
	}
	public static function stop() {
		self::$running = false;
	}
	
	public static function generateStepID() {
		$step_id = self::$step_id + 1;
		self::$step_id = $step_id;
		return $step_id;
	}
	
	public static function startStep($name) {
		if (self::$running) {
			$step = new Entrophy_Profiler_Step($name);
			
			if (self::$current_step) {
				self::$current_step->addChild($step);
			} else {
				self::$steps[] = $step;
			}
			
			self::$current_step = $step;
		}
	}
	public static function stopStep() {
		if (self::$running) {
			if (self::$current_step) {
				self::$current_step->stop();
				if ($parent = self::$current_step->getParent()) {
					self::$current_step = $parent;
				} else {
					self::$current_step = null;
				}
			}
		}
	}
	
	public static function startQuery($sql) {
		if (self::$running) {
			$query = new Entrophy_Profiler_Query($sql);
			
			if (self::$current_step) {
				self::$current_step->addQuery($query);
			} else {
				die("bug?");
			}
			self::$queries[] = $query;
			self::$current_query = $query;
		}
	}	
	public static function stopQuery() {
		if (self::$running) {
			self::$current_query->stop();
			self::$current_query = null;
		}
	}
	
	public static function getIncludes() {
		if (self::$render) {
			ob_start();
			include 'Profiler/template/includes.phtml';
			$content = ob_get_contents();
			ob_end_clean();
			return $content;
		}
	}
	public static function getHtmlContent() {
		if (self::$render) {
			$view = new Entrophy_Profiler_Contents('template/contents.phtml');
			return $view->render();
		}
	}
}
?>
