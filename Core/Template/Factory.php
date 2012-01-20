<?php
class RAD_Core_Template_Factory {
	const XML = "xml";
	const PHP_HTML = "php/html";
	const TAG_HTML = "tag/html";
	
	public function getTemplate($type) {
		switch ($type) {
			case self::XML:
				return new RAD_Core_Template_XML();
				break;
			case self::PHP_HTML:
				return new RAD_Core_Template_PHPHTML();
				break;
		}
	}
}
?>