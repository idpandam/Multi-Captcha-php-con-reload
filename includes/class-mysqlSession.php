<?php
/*
*	SALVARE SESSIONI IN MYSQL
*/

if (!class_exists('MyPdo')) {
	require_once(DATAROOT.'/db/class-pdo.php');
	$dbh=new MyPdo();
}

require_once(DATAROOT.INC.'/class-secureSession.php');

class mysqlSession extends SecuritySession {
	private $dbh;

	public function __construct($dbh){
		parent::__construct(); //secureSession.php
		$this->dbh=$dbh;
		session_set_save_handler(
			array($this, "sess_open"),
			array($this, "sess_close"),
			array($this, "sess_read"),
			array($this, "sess_write"),
			array($this, "sess_destroy"),
			array($this, "sess_gc")
		);
	}
	
	public function SetSave($save) { //SetSave secureSession
		throw new InvalidArgumentException('Non puoi impostare un percorso di salvataggio perchè usi il database'); 
	}
	
	public function sess_open($sess_path, $sess_name) {
		return true;
	}

	public function sess_close() {
		return true;
	}

	public function sess_read($sess_id) {
		$CurrentTime=time();
		$q='SELECT data FROM pa_session WHERE session_id=:sess_id';
		$r=$this->dbh->prepare($q);
		$r->bindParam(':sess_id', $sess_id, PDO::PARAM_STR, 32);
		$r->execute();
		$sess_data=$r->fetch(PDO::FETCH_COLUMN);
		
		if(!empty($sess_data)) {
			$u='UPDATE pa_session SET time=:time WHERE session_id=:sess_id';
			$ru=$this->dbh->prepare($u);
			$ru->bindParam(':time', $CurrentTime, PDO::PARAM_INT, 11);
			$ru->bindParam(':sess_id', $sess_id, PDO::PARAM_STR, 32);
			$ru->execute();
			return $sess_data;
		} else {
			$i='INSERT INTO pa_session (session_id, time) VALUES (:sess_id, :time)';
			$ri=$this->dbh->prepare($i);
			$ri->bindParam(':sess_id', $sess_id, PDO::PARAM_STR, 32);
			$ri->bindParam(':time', $CurrentTime, PDO::PARAM_INT, 11);
			$ri->execute();
			return '';
		}
	}

	public function sess_write($sess_id, $data) {
		$CurrentTime=time();
		$q='UPDATE pa_session SET data=:data, time=:time WHERE session_id=:sess_id';
		//$q='REPLACE INTO pa_session (session_id, time, data) VALUES (:sess_id, :time, :data)';
		$r=$this->dbh->prepare($q);
		$r->bindParam(':data', $data);
		$r->bindParam(':time', $CurrentTime, PDO::PARAM_INT, 11);
		$r->bindParam(':sess_id', $sess_id, PDO::PARAM_STR, 32);
		$r->execute();
		return true;
	}

	public function sess_destroy($sess_id) {
		$q='DELETE FROM pa_session WHERE session_id=:sess_id';
		$r=$this->dbh->prepare($q);
		$r->bindParam(':sess_id', $sess_id, PDO::PARAM_STR, 32);
		$r->execute();
		return true;
	}

	public function sess_gc($sess_maxlifetime) {
		$CurrentTime=time();
		$q='DELETE FROM pa_session WHERE time + :maxlifetime < :time;';
		$r=$this->dbh->prepare($q);
		$r->bindParam(':maxlifetime', $sess_maxlifetime);
		$r->bindParam(':time', $CurrentTime);
		$r->execute();
		return true;
	}
}
?>