<?php
class ENT_Core_Database extends ENT_Core_Database_CRUD {
	private $config;
	private $connection;
	private $prefix;
	private $insertID;
	private $lastQuery;
	private $totalRows;
	
	private static $instance;
	public static function getInstance() {
		if (!self::$instance) {
			self::$instance = new ENT_Core_Database();
		}
		return self::$instance;	
	}
	
	public function __construct() {
		$this->config = ENT::app()->getConfig()->getDbConfig();
		$this->connection = mysql_connect($this->config['host'], $this->config['user'], $this->config['password']);
		$this->prefix = $this->config['prefix'];
		
		mysql_set_charset('utf8', $this->connection);

		mysql_select_db($this->config['database'], $this->connection);
		$this->config = null;
		
		if (!self::$instance) {
			self::$instance = $this;
		}
	}
	
	public function getArray($result) {
		return mysql_fetch_array($result);
	}
	
	
	public function resultToArray($result, $field = null) {
		$array = array();
		while ($data = $this->getArray($result)) {
			if ($field) {
				$array[] = $data[$field];
			} else {
				$array[] = $data;
			}
		}
		
		return $array;
	}
	
	public function eav($table, $entityid) {
		return new ENT_Core_Database_EAV($this->matchTable($table), $entityid, $this);
	}
	
	public function getTotalRows() {
		#echo $this->lastQuery;
		#$result = $this->execute("SELECT FOUND_ROWS() as rows");
		#$array = mysql_fetch_array($result);
		#print_r($array);
		return $this->totalRows;
	}
	
	public function getRows($result) {
		return mysql_num_rows($result);
	}
	
	public function queryBuilder() {
		return new ENT_Core_Database_QueryBuilder($this);
	}
	
	public function matchTable($table) {
		if (!preg_match('/^'.$this->prefix.'(.+)/', $table)) {
			$table = $this->prefix . $table;
		}
		return $table;
	}
	
	public function returnAsDataObjects($result) {
		$data = array();
		while ($dataItem = $this->getArray($result)) {
			$data[] = new ENT_Core_Database_DataObject($dataItem);
		}
		return $data;
	}
	
	public function insertID() {
		return $this->insertID;
	}
	
	public function escape($value) {
		if (is_array($value)) {
			foreach ($value as $key => $data) {
				$value[$key] = mysql_real_escape_string(stripslashes($data));
			}
			return $value;
		}
		
		if ($value) {
			return mysql_real_escape_string(stripslashes($value));
		} else {
			return $value;
		}
	}
	
	public function field($name) {
		if ($name != '*' && substr($name, 0, 1) != '`' && substr($name, -1, 1) != '`' && substr($name, 0, 1) != '(') {
			if (strpos($name, '.') !== FALSE) {
				$name = str_replace('.', '`.`', $name);
			}
			$name = '`'.$name.'`';
		}		
		return $name;
	}
	public function _field(&$name) {
		$name = $this->field($name);
	}
	
	public function query($query) {
		ENT_Profiler::startQuery($query);
			$result = mysql_query($query, $this->connection);
		ENT_Profiler::stopQuery();
		return $result;
	}
	
	public function execute($query, $showQueryOnError = false) {
		ENT_Profiler::startQuery($query);	
			if ($this->connection) {
				$result = mysql_query($query, $this->connection);
			} else {
				$result = mysql_query($query);
			}
			$this->lastQuery = $query;
			$this->insertID = mysql_insert_id();
			if (mysql_error()) {
				echo "ERROR:".mysql_error()."\n<br />";
				echo "QUERY:".$query."\n<br />";	
				debug_print_backtrace();
			}
		
			if (strstr($query, "LIMIT")) {
				$_result = mysql_query("SELECT FOUND_ROWS() as rows");
				$array = mysql_fetch_array($_result);
				$this->totalRows = $array['rows'];
			}
		ENT_Profiler::stopQuery();
		return $result;
	}
}
?>
