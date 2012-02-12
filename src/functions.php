<?php

function array_to_object_array(array $array, $class) {
	$_array = array();
	
	$static = ENT::getStatic($class);
	foreach ($array as $data) {
		if ($static::$version == '2.0') {
			$_load = $class.'_Load';
			
			$_array[] = $class::load($data, $_load::ID);
		} else {
			$_array[] = $object = ENT::getModule($class);	
			$object->load($data, $object::LOAD_ID);
		}
	}
	
	return $_array;
}

function object_array_call(array $array) {
	$calls = array_splice(func_get_args(), 1);
	
	foreach ($array as $object) {
		foreach ($calls as $call) {
			$object = $object->$call();
		}
	}
}

function array_to_std_object($array) {
	if (is_array($array)) {
		return (object) array_map(array_to_std_object, $array);
	}

	return $array;
}

function array_merge_recursive_distinct ( array &$array1, array &$array2 )
{
  $merged = $array1;

  foreach ( $array2 as $key => &$value )
  {
    if ( is_array ( $value ) && isset ( $merged [$key] ) && is_array ( $merged [$key] ) )
    {
      $merged [$key] = array_merge_recursive_distinct ( $merged [$key], $value );
    }
    else
    {
      $merged [$key] = $value;
    }
  }

  return $merged;
}
function getext($file) {
	return end(explode(".", $file));
}
function implode_key($glue, $array) {
	$length = sizeof($array);
	$x = 1;
	
	foreach ($array as $key => $value) {
		$string .= $key;
		if ($x != $length) {
			$string .= $glue;
		}
		$x++;
	}
	
	return $string;
}

function implode_class($glue, $array, $function) {
	$length = sizeof($array);
	$x = 1;
	
	if ($length) {
		foreach ($array as $class) {
			$string .= $class->$function();
			if ($x != $length) {
				$string .= $glue;
			}
			$x++;
		}
		
		return $string;
	}
	return false;
}

function recursive_chmod($path) {
	$structure = explode("/", $path);
	$lastPath = '';
	
	foreach ($structure as $folder) {
		if ($lastPath) {
			$folder = $lastPath.'/'.$folder;
		}
		
		if ($folder != '..' && $folder != '.' && $folder != "." && $folder != "..") {
			if (is_dir($folder)) {
				@chmod($folder, 0777);
			}
		}
		
		$lastPath = $folder;
	}
}
function createFolder($path) {
	$structure = explode("/", $path);
	$lastPath = '';
	
	foreach ($structure as $folder) {
		if ($lastPath) {
			$folder = $lastPath.'/'.$folder;
		}
		
		if ($folder != '..') {
			if (!is_dir($folder)) {
				mkdir($folder);
				@chmod($folder, 0777);
			}
		}
		
		$lastPath = $folder;
	}
}

function ENT_Autoload($class) {
	if (strpos($class, 'ENT_') === 0) {
		require ENT::registry('ent_path').'src/'.str_replace(array('ENT_', '_'), array('', '/'), $class).'.php';
	} else if (preg_match('/Controller$/', $class)) {
		require ENT::registry('project_path').'app/code/controllers/'.str_replace("_", "/", $class).'.php';
	} else if (strpos($class, 'Helper_') === 0) {
		require ENT::registry('project_path').'app/code/helpers/'.implode("/", array_slice(explode("_", $class), 1)).'.php';
	} else if (preg_match('/_View$/', $class)) {
		require ENT::registry('project_path').'app/code/views/'.implode("/", array_slice(explode("_", $class), 0, -1)).'.php';
	} else {
		require ENT::registry('project_path').'app/code/modules/'.str_replace("_", "/", $class).'.php';;
	}
}
spl_autoload_register('ENT_Autoload');
?>
