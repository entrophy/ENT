<?php
class ENT_Template_Factory {
	const XML = "xml";
	const PHP_HTML = "php/html";
	const TAG_HTML = "tag/html";
	
	public function getTemplate($type) {
		switch ($type) {
			case self::XML:
				return new ENT_Template_XML();
				break;
			case self::PHP_HTML:
				return new ENT_Template_PHPHTML();
				break;
		}
	}
}
?>