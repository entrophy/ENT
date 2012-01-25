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
		if (class_exists($class, false) || interface_exists($class, false)) {
			return;
		}
		
		require ENT::registry('ent_path').'src/'.str_replace(array('ENT_', '_'), array('', '/'), $class).'.php';
	} else if (preg_match('/Controller$/', $class)) {
		require ENT::registry('project_path').'app/code/controllers/'.str_replace("_", "/", $class).'.php';
	} else if (strpos($class, 'Helper_') === 0) {
	
	}
}
spl_autoload_register('ENT_Autoload');
function __autoload($class) {
	if (class_exists($class, false) || interface_exists($class, false)) {
		return;
	}

	if (preg_match('/^ENT_/', $class)) {
		$includePath = ENT::registry('ent_path').'src/';
		$class = explode("_", $class);
		$class[0] = null;
		
		$x = 1;
		foreach ($class as $classItem) {
			if ($classItem) {
				$fileName .= $classItem;
				if ($x != sizeof($class)) {
					$fileName .= '/';
				}
			}	
			$x++;
		}
		
		$fileName = $fileName.".php";
		$includePath .= $fileName;
	
		if (file_exists($includePath)) {
			
			require $includePath;
		}
	} else if (preg_match('/Controller$/', $class)) {
		$class = str_replace("_", "/", $class);
		
		$controller_path = ENT::registry('project_path').'app/code/controllers/'.$class.'.php';
		require $controller_path;
	} else if (preg_match('/View$/', $class)) {
		$class = explode("_", $class);
	
		$target = sizeof($class) - 1;
		array_splice($class, $target, 1);
		$class = implode("/", $class);

		$view_path = ENT::registry('project_path').'app/code/views/'.$class.'.php';
		require $view_path;
	} else if (preg_match('/^Helper/', $class)) {
		$class = explode("_", $class);
		array_splice($class, 0, 1);

		$class = implode("_", $class);
	
		$class = str_replace("_", "/", $class);
		require ENT::registry('project_path').'app/code/helpers/'.$class.'.php';
	} else {
		$class = explode("_", $class);
		
		
		switch ($class[0]) {
			case 'ENT':
				$class[0] = null;
				$includePath = ENT::registry('ent_path');
				break;
			case 'jQueryTmpl':
				$includePath = ENT::registry('lib_path').'/jquery-tmpl-php/';
				$debug = true;
				break;
			case 'Zend':
				set_include_path(ENT::registry('zend_path'));
				$includePath = ENT::registry('zend_path') ? : '';
				break;
			default:
				set_include_path('');
				$includePath = ENT::registry('project_path').'app/code/modules/';
				
		}
		
		$x = 1;
		foreach ($class as $classItem) {
			if ($classItem) {
				$fileName .= $classItem;
				if ($x != sizeof($class)) {
					$fileName .= '/';
				}
			}	
			$x++;
		}
		
		$fileName = $fileName.".php";
		$includePath .= $fileName;
		if (file_exists($includePath)) {	
			require $includePath;
		}
		
		set_include_path('');
	}
}
?>
