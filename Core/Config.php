<?php
class ENT_Core_Config {
	public function load() {
		if (file_exists($file = ENT::registry('project_path').'app/etc/config.json')) {
			return new ENT_Core_Config_Json($file);
		} elseif (file_exists($file = ENT::registry('project_path').'app/etc/config.xml')) {
			return new ENT_Core_Config_Xml($file);
		}
	}
}
?>
