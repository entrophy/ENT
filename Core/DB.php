<?php
class RAD_Core_DB {
	private $config;
	private $connection;
	private $prefix;
	public function __construct() {
		$this->config = RAD::app()->getConfig()->getDbConfig();
		$this->connection = mysql_connect($this->config['host'], $this->config['user'], $this->config['password']);
		$this->prefix = $this->config['prefix'];

		mysql_select_db($this->config['database'], $this->connection);
	}
	
	public function query($query) {
		return mysql_query($query, $this->connection);
	}
	
	public function getArray($result) {
		return mysql_fetch_array($result);
	}
	
	public function matchTable($table) {
		if (!preg_match('/^'.$this->prefix.'(.+)/', $table)) {
			$table = $this->prefix . $table;
		}
		return $table;
	}
	
	public function create($table, $data) {
		$table = $this->matchTable($table);
		$datalength = sizeof($data);
		
		$query = "INSERT INTO $table (";
		$x = 1;
		foreach ($data as $field => $value) {
			$query .= $field;
			if ($x != $datalength) {
				$query .= ', ';
			}
			
			$x++;
		}
		
		$query .= ') VALUES (';
		$x = 1;
		foreach ($data as $field => $value) {
			$query .= $value;
			if ($x != $datalength) {
				$query .= ', ';
			}
			
			$x++;
		}
		
		$query .= ')';
		
		$result = mysql_query($query);
		echo mysql_error();
		return $result;
	}
	
	public function read($table, $additional) {
		$table = $this->matchTable($table);
		
		$query = "SELECT * FROM $table $additional";
		
		$result = mysql_query($query);
		echo mysql_error();
		return $result;
	}
	
	public function update($table, $data, $where) {
		$table = $this->matchTable($table);
		$datalength = sizeof($data);
		
		$query = "UPDATE $table SET ";
		$x = 1;
		foreach ($data as $field => $value) {
			$query .= $field." = ".$value;
			if ($x != $datalength) {
				$query .= ', ';
			}
			
			$x++;
		}
		
		$query .= " WHERE ".$where;
		
		$result = mysql_query($query);
		echo mysql_error();
		return $result;
	}
	
	public function delete($table, $where) {
		$table = $this->matchTable($table);
		
		$query = "DELETE FROM $table WHERE $where";
		
		$result = mysql_query($query);
		echo mysql_error();
		return $result;
	}
}
?>