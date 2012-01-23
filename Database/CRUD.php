<?php
class ENT_Database_CRUD {
	public function cou($table, $data, $where) {
		$result = $this->execute("SELECT * FROM $table WHERE $where");
		if ($this->getRows($result)) {
			$this->update($table, $data, $where);
		} else {
			$this->create($table, $data);
		}
	}

	public function create($table, $data) {
		$table = $this->matchTable($table);
		
		$query = "INSERT INTO $table (";
		$query .= implode_key(', ', $data);
		
		$query .= ') VALUES (';
		
		$x = 1;
		$length = sizeof($data);
		foreach ($data as $field => $value) {
			if (is_string($value)) {
				$query .= "'".mysql_real_escape_string(stripslashes($value))."'";
			} else {
				if ($value) {
					$query .= $value;
				} else {
					$query .= "''";
				}
			}
			if ($x != $length) {
				$query .= ', ';
			}
			
			$x++;
		}
		
		$query .= ')';
		
		$result = $this->execute($query);
		echo mysql_error();
		return $result;
	}
	
	public function read($table, $fields, $additional = false) {
		if (is_array($table) || strstr($table, ',') || (!is_array($table) && !is_array($fields))) {
			/*
			 * Deprecation support
			 */
		
			$_table = $table;
			$table = $fields;
			$fields = $_table;
		}
	
		$table = $this->matchTable($table);
		
		if (!is_string($fields)) {
			$fields = implode(", ", $fields);
		}
		
		$query = "SELECT $fields FROM $table $additional";
		
		$result = $this->execute($query);
		
		if ($error = mysql_error()) {
			echo "<br /><br />table";
			print_r($table);
			echo "<br /><br />fields";
			print_r($fields);
			echo "<br /><br />read:".$error;
			
			return false;
		}
		return $result;
	}
	
	public function update($table, $data, $where) {
		$table = $this->matchTable($table);
		$datalength = sizeof($data);
		
		$query = "UPDATE $table SET ";
		$x = 1;
		foreach ($data as $field => $value) {
			$query .= $field." = ";
			
			if (is_string($value)) {
				$query .= "'".mysql_real_escape_string(stripslashes($value))."'";
			} else {
				$query .= $value;
			}
			
			if ($x != $datalength) {
				$query .= ', ';
			}
			
			$x++;
		}
		
		$query .= " WHERE ".$where;
		
		$result = $this->execute($query);
		echo mysql_error();
		return $result;
	}
	
	public function delete($table, $where) {
		$table = $this->matchTable($table);
		
		$query = "DELETE FROM $table";
		if ($where) {
			$query .= " WHERE ".$where;
		}
		
		$result = $this->execute($query);
		if ($error = mysql_error()) {
			echo $error."<br />";
			echo $query."<br />";
		}
		return $result;
	}
}
?>
