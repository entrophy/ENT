<?php
class ENT_Database_QueryBuilder {
	private $query;
	protected $table;
	protected $fields = '*';
	protected $tableAlias;
	private $left_joins;
	private $conditions;
	private $having;
	protected $ammount;
	protected $offset = 0;
	private $type = "SELECT";
	protected $sort;
	protected $database;
	private $data;
	protected $group_by;
	
	public function __construct($database) {
		$this->database = $database;
	}
	
	public function setType($type) {
		$this->type = strtoupper($type);
		return $this;
	}
	public function setTable($table, $alias = NULL) {
		$this->table = $table;
		$this->tableAlias = $alias;
		return $this;
	}
	
	public function addLeftJoin($table, $condition) {
		$this->left_joins[] = array($table, $condition);
	}
	
	public function resetFields() {
		$this->fields = '';
	}
	public function setFields($fields) {
		if (is_string($fields)) {
			$this->fields = array($fields);
		} else {
			$this->fields = $fields;
		}
		return $this;
	}
	
	
	public function addField($field) {
		if ($this->fields == '*') {
			$this->fields = array();
		}
		$this->fields[] = $field;
		return $this;
	}
	
	public function getFields() {
		return $this->fields;
	}
	
	public function setData($data) {
		$this->data = $data;
		return $this;
	}
	
	public function setGroupBy($group_by) {
		$this->group_by = $group_by;
		return $this;
	}
	
	public function newCondition($params, $key = '', $weight = 0) {
		return new ENT_Database_QueryBuilder_Condition($params, $key, $weight);
	}
	
	public function removeCondition($param) {
		if (is_object($param)) {
			$key = $param->getKey();
		} else {
			$key = $param;
		}
		
		$this->conditions[$key] = null;
		unset($this->conditions[$key]);
	}
	public function setCondition($param, $glue = 'AND', $key = '', $weight = 0) {
		if (is_object($param)) {
			$condition = $param;
			if (!$key) {
				$key = $condition->getKey();
			}
		} else {
			$condition = $this->newCondition($param, $key, $weight);
		}
		
		if ($key && $key != '') {
			$this->conditions[$key] = array($condition, $glue);
		} else {
			$this->conditions[] = array($condition, $glue);
		}
		
		return $this;
	}
	
	public function addCondition($condition, $glue = "AND") {
		if (is_array($condition)) {
			foreach ($condition as $field => $value) {
				$value = mysql_real_escape_string($value);
				$this->conditions[] = array("$field = '$value'", $glue);
			}
		} else {
			$this->conditions[] = array($condition, $glue);
		}
		return $this;
	}
	
	public function addHaving($having, $glue = "AND") {
		if (is_array($having)) {
			foreach ($having as $field => $value) {
				$value = mysql_real_escape_string($value);
				$this->having[] = array("$field = '$value'", $glue);
			}
		} else {
			$this->having[] = array($having, $glue);
		}
		return $this;
	}
	
	public function setSort($sort) {
		$this->sort = $sort;
	}
	
	public function setAmmount($ammount) {
		$this->ammount = $ammount;
		return $this;
	}
	public function setLimit($limit) {
		return $this->setAmmount($limit);
	}
	public function setOffset($offset) {
		$this->offset = $offset;
		return $this;
	}
	
	public function escapeName($name) {
		return $this->database->field($name);
	}
	public function _escapeName(&$name) {
		$name = $this->escapeName($name);
	}
	
	public function sortConditions($a, $b) {
		$a = $a[0];
		$b = $b[0];
		
		if (is_object($a)) {
			if (is_object($b)) {
				if ($a->getWeight() == $b->getWeight()) {
					return 0;
				}	
				return ($a->getWeight() < $b->getWeight()) ? -1 : 1;
			}
			
			return 1;
		} elseif (is_object($b)) {
			return -1;
		}
		return 0;
	}
	
	public function buildQuery() {
		$query = $this->type." ";
			
			switch ($this->type) {
				case "SELECT":
					if (!is_array($this->fields)) {
						$this->fields = array($this->fields);
					}

					if ($this->ammount) {
						$query .= "SQL_CALC_FOUND_ROWS ";
					}
					
					array_walk($this->fields, array($this, '_escapeName'));
					$query .= implode(", ", $this->fields);
					
					$query .= " FROM ";
					break;
				case "CREATE";
				case "INSERT":
					$query .= "INTO ";
					break;
				case "DELETE":
					$query .= "FROM ";
					break;
				}
			
			if (is_array($this->table)) {
				array_walk($this->table, array($this, '_escapeName'));
				$query .= implode(", ", $this->table);
			} else {
				$query .= $this->escapeName($this->table);
				if ($this->tableAlias) {
					$query .= " ".$this->tableAlias;
				}
			}
			
			if (is_array($this->left_joins) && count($this->left_joins) && $this->type == "SELECT") {
				foreach ($this->left_joins as $left_join) {
					$query .= " LEFT JOIN `".$left_join[0]."` ON ".$left_join[1];
				}
			}
			
			if ($this->data) {
				$length = sizeof($this->data);
				$x = 1;
				switch ($this->type) {
					case "UPDATE":
						$query .= " SET ";
						foreach ($this->data as $key => $data) {
							$query .= "`".$key."` = ";
							
							if (!$data) {
								$query .= "''";
							} elseif (is_string($data)) {
								$query .= "'".mysql_real_escape_string(stripslashes($data))."'";
							} elseif (is_numeric($data)) {
								$query .= $data;
							} else {
								$query .= "'".mysql_real_escape_string(stripslashes($data))."'";
							}
							
							if ($x != $length) {
								$query .= ", ";
							}
							
							$x++;
						}
						break;
					case "CREATE";
					case "INSERT":
						$keys = array_keys($this->data);
						array_walk($keys, array($this, '_escapeName'));
					
						$query .= ' ('.implode(', ', $keys).') VALUES (';
						
						foreach ($this->data as $key => $data) {
							//$query .= is_string($data) ? "'".mysql_real_escape_string(stripslashes($data))."'" : $data;
							
							if (!$data) {
								$query .= "''";
							} elseif (is_string($data)) {
								$query .= "'".mysql_real_escape_string(stripslashes($data))."'";
							} elseif (is_numeric($data)) {
								$query .= $data;
							} else {
								$query .= "'".mysql_real_escape_string(stripslashes($data))."'";
							}
							
							if ($x != $length) {
								$query .= ", ";
							}
							
							$x++;
						}
						
						$query .= ")";
						break;
				}
			}
			
			$condCount = sizeof($this->conditions);
			$x = 1;
			if ($condCount && $this->type != 'INSERT' && $this->type != 'CREATE') {
				usort($this->conditions, array($this, 'sortConditions'));
			
				$query .= " WHERE";
				foreach ($this->conditions as $condition) {
					$query .= " ";
					if ($x != 1) {
						$query .= $condition[1]." ";
					}
					
					if (is_object($condition[0])) {
						$query .= "(".$condition[0]->getSql().")";
					} else {
						$query .= "(".$condition[0].")";
					}
					$x++;
				}
			}
			
			if ($this->group_by) {
				$query .= " GROUP BY ".$this->group_by;
			}
			
			$havingCount = sizeof($this->having);
			$x = 1;
			if ($havingCount && $this->type != 'INSERT' && $this->type != 'CREATE') {
				$query .= " HAVING";
				foreach ($this->having as $having) {
					$query .= " ";
					if ($x != 1) {
						$query .= $having[1]." ";
					}
					$query .= "(".$having[0].")";
					$x++;
				}
			}
			
			if ($this->sort) {
				$query .= " ORDER BY ".$this->sort;
			}
			
			if ($this->ammount) {
				$query .= " LIMIT ";
				$query .= $this->offset;
				$query .= ", ";
				$query .= $this->ammount;
			}
			
			$this->query = $query;
	}
	
	public function unsetQuery() {
		$this->query = NULL;
	}
	
	public function getQuery() {
		if (!$this->query) {
			$this->buildQuery();
		}
		return $this->query;
	}
	
	public function execute() {
		if (!$this->query) {
			$this->buildQuery();
		}
		return $this->database->execute($this->query);
	}
}
?>
