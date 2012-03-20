<?php
class ENT_Inflector {
	public static function pluralize($string) {
		if (substr($string, -1, 1) == 'y') {
			$string = substr_replace($string, 'ies', -1, 1);
		} else {
			$string .= 's';
		}
		return $string;
	}
	public static function singularize($string) {
		if (substr($string, -3, 3) == 'ies') {
			$string = substr_replace($string, 'y', -3, 3);
		} elseif (substr($string, -1, 1) == 's') {
			$string = substr_replace($string, '', -1, 1);
		}
		return $string;
	}
}
?>
