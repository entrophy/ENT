<?php
class RAD_Core_Config {
	public function load() {
		if (file_exists($file = RAD::registry('project_path').'app/etc/config.json')) {
			return new RAD_Core_Config_Json($file);
		} elseif (file_exists($file = RAD::registry('project_path').'app/etc/config.xml')) {
			return new RAD_Core_Config_Xml($file);
		}
	}
}
?>
