<?php
class ENT_Profiler_Contents_Query extends ENT_Profiler_Contents {
	private $step;
	public function __construct($file, $step) {
		$this->file = $file;
		
		$this->step = $step;
	}
	
	public function getStep() {
		return $this->step;
	}

	public function parseSql($query) {
		$tags = array(
			'SELECT', 'DISTINCT', 'WHERE', 'AND', 'ORDER BY', 'OR', '&&', '||', 'INSERT INTO', 'IN', 'BETWEEN', 'LIKE', 'DESC', 'ASC', 'NOT', 
			'COUNT', 'GROUP BY', 'HAVING', 'DROP', 'TABLE', 'UPDATE', 'DELETE', 'TRUNCATE', 'FROM', 'LIMIT', 'SQL_CALC_FOUND_ROWS'
		);
		
		$query = preg_replace("/( |^|\()(".implode('|', $tags).")( |$|\))/", "$1<span class=\"tag\">$2</span>$3", $query);
	
		return $query;
	}
	
	public function getCRUDType($sql) {
		$keyword = strtoupper(array_shift(explode(" ", $sql)));
		
		switch ($keyword) {
			case 'INSERT':
				return 'create';
				break;
			case 'SELECT':
				return 'read';
				break;
			case 'UPDATE':
				return 'update';
				break;
			case 'DELETE':
				return 'delete';
				break;
		}
	}
}
?>
