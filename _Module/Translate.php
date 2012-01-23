<?php
class ENT_Module_Translate {
	private static $instance;
	public static function getInstance() {
		if (!self::$instance) {
			self::$instance = new ENT_Module_Translate();
		}
		return self::$instance;
	}
	
	private $lang;
	private $fallbackLanguage;
	private $sectionsLoaded = array();
	private $translations = array();
	public function __construct() {
	
	}
	
	public function loadSection($section) {
		if (!in_array($section, $this->sectionsLoaded)) {
			$this->sectionsLoaded[] = $section;
			
			$file = 'app/locale/'.$this->getLang().'/'.$section.'.csv';
			if (!is_file($file)) {
				$file = 'app/locale/'.$this->getFallbackLanguage().'/'.$section.'.csv';
			}
			if (is_file($file)) {
				$handle = fopen($file, "r");			
				while (($data = fgetcsv($handle, 500, ",")) !== FALSE && !$break) {
					$key = $data[0];
					$value = $data[1];
				
					$this->translations[$key] = $value;
				}
			}
		}
	}
	
	public function setLang($lang) {
		$this->lang = $lang;
	}
	public function getLang() {
		return $this->lang;
	}
	
	public function setFallbackLanguage($fallbackLanguage) {
		$this->fallbackLanguage = $fallbackLanguage;
	}
	public function getFallbackLanguage() {
		return $this->fallbackLanguage;
	}
	
	public function check($key) {
		return !!$this->translations[$key];
	}
	
	public function getTranslations() {
		return $this->translations;
	}	
	public function getTranslation($key, $args = null) {
		$translation = $this->translations[$key] ? : $key;
		if (!$args && !is_array($args)) {
			$args = func_get_args();
			$args = array_slice($args, 1);
		}
		return vsprintf($translation, $args);
	}
}
?>
