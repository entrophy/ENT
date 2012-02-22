<?php
class ENT_Session_Handler_Database {
	private $config;
	private $database;
	private $lifetime;
	public function __construct($config) {
		$this->config = (object) array_merge($config, array(
			'primary' => 'id',
			'columns' => array(
				'modified' => 'modified',
				'lifetime' => 'lifetime',
				'data' => 'data'
			)
		));
		$this->config->columns = (object) $this->config->columns;
		
		$this->database = ENT::app()->getDatabase();
		$this->database->crud();
		$this->lifetime = get_cfg_var("session.gc_maxlifetime");
	}
	
	public function open($save_path, $session_name) {
      return true;
   }

	public function close() {
		return true;
   }
   
	public function read($session_id) {
		$time = time();

		$qb = $this->database->queryBuilder();
		$qb->table($this->config->table);
		$qb->fields(array($this->config->columns->data));
		$qb->setCondition('`modified` + `lifetime` > :time')->bindParam('time', $time);
		$qb->setCondition(array('id' => $session_id));
		$result = $qb->execute();
		
		return $result[0] ? $result[0]['data'] : '';
	}

	public function write($session_id, $data) {
		$result = $this->database->crud()->read($this->config->table, array($this->config->columns->data), array(
			'where' => array('id' => $session_id)
		));
		$update = count($result) ? true : false;

		$qb = $this->database->queryBuilder();
		$qb->table($this->config->table);
		if ($update) {
			$qb->type('update');
			$qb->where(array('id' => $session_id));
		} else {
			$qb->type('insert');
		}
		
		$qb->values(array(
			'id' => $session_id,
			'data' => $data,
			'modified' => time(),
			'lifetime' => $this->lifetime
		));
		$qb->execute();
		return true;
	}

	public function destroy($session_id) {
		$qb = $this->database->queryBuilder();
		$qb->type('delete');
		$qb->table($this->config->table);
		$qb->setCondition(array('id' => $session_id));
		$qb->execute();
		
		return true;
	}

	public function gc() {
		$time = time();

		$qb = $this->database->queryBuilder();
		$qb->type('delete');
		$qb->table($this->config->table);
		$qb->fields(array($this->config->columns->data));
		$qb->setCondition('`modified` + `lifetime` < :time')->bindParam('time', $time);
		$result = $qb->execute();
		return true;
	}
}
?>
