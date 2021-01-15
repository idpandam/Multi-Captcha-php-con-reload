<?php
/*
* IMPOSTARE SESSIONI SICURE
*/

class SecuritySession {
	
	protected $name; //nome cookie
	protected $lifetime; //scadenza cookie
	protected $path; //percorso cookie
    protected $domain; //dominio cookie
    protected $secure; //https
    protected $httponly; //solo da http
	protected $samesite; //contesto
	protected $save; //percorso file sessione
	protected $ExpireSessionAfter; //dopo quanti minuti deve scadere la sessione se inattiva. Se 0 non scade automaticamente

	public function __construct(){
		$this->name='Pandam';
		$this->lifetime=0;
		$this->path='/';
		$this->domain=NULL;
		$this->secure=FALSE;
		$this->httponly=TRUE;
		$this->samesite='lax';
		$this->save=NULL;
		$this->ExpireSessionAfter=15;
	}
	
	public function SetName($name){
		$this->name=trim(filter_var($name, FILTER_SANITIZE_STRING));
	}
	
	public function GetName(){
		return $this->name;
	}
	
	public function SetLifetime($lifetime){ 
		$this->lifetime=filter_var($lifetime, FILTER_VALIDATE_INT);
	}
	
	public function SetPath($path){
		if(file_exists(DATAROOT.$path)) {
			$this->path=$path;
		}
	}
	
	public function SetDomain($domain){
		$this->domain=filter_var($domain, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME);
	}
	
	public function SetSecure($secure){
		$this->secure=filter_var($secure, FILTER_VALIDATE_BOOLEAN);
	}
	
	public function SetHttponly($httponly){
		$this->httponly=filter_var($httponly, FILTER_VALIDATE_BOOLEAN);
	}
	
	public function SetSameSite($samesite){
		$this->samesite=trim(filter_var($samesite, FILTER_SANITIZE_STRING));
	}
	
	public function SetSave($save){
		if(file_exists($save)) {
			$this->save=$save;
		} else {
			throw new InvalidArgumentException('Directory di sessione non valida');
		}
	}
	
	public function SetExpire($ExpireSessionAfter){
		$this->ExpireSessionAfter=filter_var($ExpireSessionAfter, FILTER_VALIDATE_INT);
	}
	
	public function StartSession() {
		if(!empty($this->save)) {
			session_save_path($this->save);
		}
		ini_set('session.use_only_cookies', 1); 
		if(PHP_VERSION_ID < 70300) {
			session_set_cookie_params($this->lifetime, $this->path.'; samesite='.$this->samesite, $this->domain, $this->secure, $this->httponly);
		} else {
			session_set_cookie_params([
				'lifetime' => $this->lifetime,
				'path' => $this->path,
				'domain' => $this->domain,
				'secure' => $this->secure,
				'httponly' => $this->httponly,
				'samesite' => $this->samesite
			]);
		}
		session_name($this->name);
		session_start();
		session_regenerate_id(true);
	}
	
	public function DestroySession() {
		$_SESSION=array(); 
		$params=session_get_cookie_params(); 
		setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
		session_unset();
		session_destroy();
	}
	
	protected function ExpireSession(){
		if($this->ExpireSessionAfter>0) {
			if (isset($_SESSION['last_action'])) { 
				$secondsInactive=time()-$_SESSION['last_action'];
				$expireAfterSeconds=$this->ExpireSessionAfter*60;
				if ($secondsInactive>=$expireAfterSeconds) {
					return false;
				} else {
					$_SESSION['last_action']=time();  //imposto il tempo dell'ultima azione effettuata
					return true;
				}
			} else {
				$_SESSION['last_action']=time();  //imposto il tempo dell'ultima azione effettuata
				return true;
			}
		} else {
			return true;
		}
	}
}
?>