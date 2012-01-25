<?php
class ENT_Layout_Factory {
	const XML = "xml";
	const PHP_HTML = "php/html";
	const TAG_HTML = "tag/html";
	
	public function getTemplate($type) {
		switch ($type) {
			case self::XML:
				return new ENT_Layout_XML();
				break;
			case self::PHP_HTML:
				return new ENT_Layout_PHPHTML();
				break;
		}
	}
}
?>
