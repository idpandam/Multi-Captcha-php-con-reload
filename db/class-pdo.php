<?php
class MyPdo extends PDO {
	private $host=DB_HOST;
	private $user=DB_USER;
	private $pass=DB_PASS;
	private $dbname=DB_NAME;
	private $charset=DB_CHARSET;
	
	public function __construct() {
		#set DNS
		$col='mysql:host='.$this->host.';dbname='.$this->dbname;
		
		#set options
		$options = array(
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
		);
		if(version_compare(PHP_VERSION, '5.3.6', '<')) {
			if(defined('PDO::MYSQL_ATTR_INIT_COMMAND')){
				$options[PDO::MYSQL_ATTR_INIT_COMMAND] = 'SET NAMES ' . DB_CHARSET;
			}
		} else {
			$col.=';charset='.DB_CHARSET;
		}
		
		#connessione
		try {
            parent::__construct($col, $this->user, $this->pass, $options);
            $this->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			
			if( version_compare(PHP_VERSION, '5.3.6', '<') && !defined('PDO::MYSQL_ATTR_INIT_COMMAND') ) {
				$sql='SET NAMES ' . DB_CHARSET;
				$this->exec($sql);
			}
        } 
		
		#gestione errori
		catch(PDOException $e){                
            die('Attenzione errore: '. $e->getMessage());
        }
		
		catch(Exception $e) {
			//notifica in caso di errorre NON PDO
			die('Attenzione errore: '. $e->getMessage());
		}
	}
}
?>
