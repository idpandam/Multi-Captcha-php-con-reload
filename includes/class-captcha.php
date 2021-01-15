<?php
/*
*	GESTIONE CAPTCHA (sessione)
*/
//error_reporting(E_ALL); ini_set('display_errors', 1);
		
class PaCaptcha {
	protected $CaptchaName; //nome univoco img Captcha
	protected $SessionName; //nome sessione
	protected $ImgHeight=45; //altezza immagine
	protected $ImgWidth=200; //larghezza immagine
	protected $ImgQuality=100; //qualità immagine
	protected $BgColor='#ffffff'; //colore sfondo
	protected $DotsColor='#666666'; //colore punti
	protected $LinesColor='#999999'; //colore linee
	protected $NrDots=100; //quanti punti
	protected $NrLines=20; //quante linee
	protected $TextColor='#4d4d4d'; //colore testo
	protected $FontSize=0.55; //dimensione testo
	protected $CodeLength=6; //lunghezza codice
	protected $CodeCI=0; //case sensitive

	public function __construct($CaptchaName, $SessionName='PaCaptcha'){
		$this->SetCaptchaName($CaptchaName);
		$this->SetSessionName($SessionName);
		
		//controllo sessione per salvataggio
		self::CaptchaStartSession($this->SessionName);
	}
	
	public function CaptchaReloadInline($IdImg, $objCaptcha){
		$objOption=get_object_vars($objCaptcha); //proprietà oggetto
		$defaultOption=get_class_vars(__CLASS__); //proprietà default
		$customOption=array(); //proprietà custom
		foreach($objOption as $k=>$v) {
			if($k==='SessionName' || $v!==$defaultOption[$k]) {
				$customOption[$k]=$v;
			}
		}
		$sendOption=json_encode($customOption);
		unset($customOption);
		unset($defaultOption);

		if(!empty($sendOption)) {
			#link
			echo '<a href="#" id="reloadcaptcha_'.$IdImg.'">Reload</a>';
			
			#jquery
			echo '<script> $(document).ready(function() {';
			echo '$("#reloadcaptcha_'.$IdImg.'").click(function(e) {
				e.preventDefault();
				var JCaptcha= JSON.stringify('.$sendOption.');
				$.ajax({
					type: "POST",
					url: "./includes/class-captcha.php?r=reload",
					data: {sendOption : JCaptcha},
					dataType: "html",
					cache:false,
					beforeSend:function(){
						$("#'.$IdImg.'").attr("src","./style/loader.gif");
					},
					success: function(risposta){
						$("#'.$IdImg.'").attr("src",risposta);
					},
					error: function(){
						alert("Errore Captcha Reload");
					}
				});
			});';
			echo '}); </script>';
		} else {
			throw new InvalidArgumentException('Problema reload captcha');
		}
	}
	
	public function ImgCaptchaOutput(){
		$this->ImgBuild();
		header('Content-Type:image/jpeg');
		$this->ImgJpeg();
		imagedestroy($this->CaptchaImage);
	}
	
	public function ImgCaptchaInline(){
        return 'data:image/jpeg;base64,' . base64_encode($this->ImgGet());
    }
	
	public function CaptchaInputText($InputName){
		$this->SetInputName($InputName);
		echo '<input type="text" class="form-control" name="'.$this->InputName.'" maxlength="'.$this->CodeLength.'" placeholder="Codice sicurezza" />';
	}
	
	public static function ValidateCaptcha($CaptchaName, $CodeEntered, $SessionName='PaCaptcha'){
		if(self::FilterText($CaptchaName) && self::FilterText($SessionName) && self::FilterText($CodeEntered)) {
			//sessione per verifica
			self::CaptchaStartSession($SessionName);
			
			if(isset($_SESSION['pa_captchacode_'.$CaptchaName])) {
				if(!empty($_SESSION['pa_captchacode_'.$CaptchaName]) && $_SESSION['pa_captchacode_'.$CaptchaName]===$CodeEntered) {
					unset ($_SESSION['pa_captchacode_'.$CaptchaName]);
					return TRUE;
				} else {
					unset ($_SESSION['pa_captchacode_'.$CaptchaName]);
					return FALSE;
				}
			} else {
				return FALSE;
			}
		} else {
			return FALSE;
		}
	}
		
	
	
	public function SetImgHeight($h){ 
		if(self::FilterInt($h)) {
			$this->ImgHeight=$h;
		} else {
			throw new InvalidArgumentException('Altezza non valida');
		}
	}
	
	public function SetImgWidth($w){
		if(self::FilterInt($w)) {
			$this->ImgWidth=$w;
		} else {
			throw new InvalidArgumentException('Larghezza non valida');
		}
	}
	
	public function SetNrDots($nd){
		if(self::FilterInt($nd)) {
			$this->NrDots=$nd;
		} else {
			throw new InvalidArgumentException('Punti non validi');
		}
	}
	
	public function SetNrLines($nl){
		if(self::FilterInt($nl)) {
			$this->NrLines=$nl;
		} else {
			throw new InvalidArgumentException('Linee non valide');
		}
	}
	
	public function SetBgColor($bc){
		if(self::FilterColor($bc)) {
			$this->BgColor=$bc;
		} else {
			throw new InvalidArgumentException('Colore sfondo non valido');
		}
	}
	
	public function SetTextColor($tc){
		if(self::FilterColor($tc)) {
			$this->TextColor=$tc;
		} else {
			throw new InvalidArgumentException('Colore testo non valido');
		}
	}
	
	public function SetDotsColor($dc){
		if(self::FilterColor($dc)) {
			$this->DotsColor=$dc;
		} else {
			throw new InvalidArgumentException('Colore punti non valido');
		}
	}
	
	public function SetLinesColor($lc){
		if(self::FilterColor($lc)) {
			$this->LinesColor=$lc;
		} else {
			throw new InvalidArgumentException('Colore linee non valido');
		}
	}
	
	public function SetImgQuality($q){
		$q=trim($q);
		if(ctype_digit(strval($q)) && $q>=1 && $q<=100) {
			$this->ImgQuality=$q;
		} else {
			throw new InvalidArgumentException('Qualità immagine non valida');
		}
	}
	
	public function SetFontSize($fs){
		$fs=trim($fs);
		if(filter_var($fs, FILTER_VALIDATE_FLOAT)) {
			$this->FontSize=$fs;
		} else {
			throw new InvalidArgumentException('Dimensione font non valido');
		}
	}
	
	public function SetCodeLength($cl){
		$cl=trim($cl);
		if(ctype_digit(strval($cl)) && $cl>0) {
			$this->CodeLenght=$cl;
		} else {
			throw new InvalidArgumentException('Dimesione codice non valida');
		}
	}
	
	public function SetCodeCI($c){
		$c=trim($c);
		$c=(!empty($c))? 1 : 0;
		$this->CodeCI=$c;
	}
	
	public function SetAllOption($obj){
		if(is_object($obj)) {
			if(isset($obj->ImgHeight)) { $this->SetImgHeight($obj->ImgHeight); } 
			if(isset($obj->ImgWidth)) { $this->SetImgWidth($obj->ImgWidth); }
			if(isset($obj->ImgQuality)) { $this->SetImgQuality($obj->ImgQuality); }
			if(isset($obj->BgColor)) { $this->SetBgColor($obj->BgColor); }
			if(isset($obj->DotsColor)) { $this->SetDotsColor($obj->DotsColor); }
			if(isset($obj->LinesColor)) { $this->SetLinesColor($obj->LinesColor); }
			if(isset($obj->NrDots)) { $this->SetNrDots($obj->NrDots); }
			if(isset($obj->NrLines)) { $this->SetNrLines($obj->NrLines); }
			if(isset($obj->TextColor)) { $this->SetTextColor($obj->TextColor); }
			if(isset($obj->FontSize)) { $this->SetFontSize($obj->FontSize); }
			if(isset($obj->CodeLength)) { $this->SetCodeLength($obj->CodeLength); }
			if(isset($obj->CodeCI)) { $this->SetCodeCI($obj->CodeCI); }
			return TRUE;
		} else {
			return FALSE;
		}	
	}
	
	
	
	public static function FilterColor($c){
		$c=trim($c);
		if(preg_match('/#([a-f]|[A-F]|[0-9]){6}?\b/', $c)) {
			return TRUE;
		} else {
			return FALSE;
		}
	}
	
	public static function FilterInt($i){
		$i=trim($i);
		if(ctype_digit(strval($i)) && $i>0) {
			return TRUE;
		} else {
			return FALSE;
		}
	}
	
	public static function FilterText($t){
		$t=trim($t);
		if(is_string($t) && (strlen($t)>0) && preg_match('/^[a-zA-Z0-9_-]+$/', $t)) {
			return TRUE;
		} else {
			return FALSE;
		}
	}
	
	
	
	protected function SetSessionName($sn){
		if(self::FilterText($sn)) {
			$this->SessionName=$sn;
		} else {
			throw new InvalidArgumentException('Nome sessione non valido');
		}
	}
	
	protected function SetCaptchaName($cn){
		if(self::FilterText($cn)) {
			$this->CaptchaName=$cn;
		} else {
			throw new InvalidArgumentException('Nome captcha non valido');
		}
	}
	
	protected function SetInputName($in){
		if(self::FilterText($in)) {
			$this->InputName=$in;
		} else {
			throw new InvalidArgumentException('Nome input non valido');
		}
	}
	
	
	
	protected function ImgGet(){
		$this->ImgBuild();
		ob_start();
		$this->ImgJpeg();
        return ob_get_clean();
    }
	
	protected function ImgJpeg(){
		return imagejpeg($this->CaptchaImage, null, $this->ImgQuality);
	}
	
	protected function ImgBuild(){
		$this->CaptchaImage=imagecreate($this->ImgWidth, $this->ImgHeight);
		$this->ColorAllocate($this->BgColor);
		$this->DotsCaptcha();
		$this->LinesCaptcha();
		$this->Text();
	}
	
	protected function DotsCaptcha(){
		$image_dots_color=$this->ColorAllocate($this->DotsColor);
		$dots='';
		for($count=0; $count<$this->NrDots; $count++ ) {
			$dots.=imagefilledellipse($this->CaptchaImage,mt_rand(0,$this->ImgWidth),mt_rand(0,$this->ImgHeight),2,3,$image_dots_color);
		}
		return $dots;
	}
	
	protected function LinesCaptcha(){
		$image_lines_color=$this->ColorAllocate($this->LinesColor);
		$lines='';
		for($count=0; $count<$this->NrLines; $count++) {
			$lines.=imageline($this->CaptchaImage,mt_rand(0,$this->ImgWidth),mt_rand(0,$this->ImgHeight),mt_rand(0,$this->ImgWidth),mt_rand(0,$this->ImgHeight),$image_lines_color);
		}
		return $lines;
	}
	
	protected function Text(){
		$captcha_code='';
		//FontFamily
		$fontdir=DATAROOT.'/style/Font/';
		$files=glob($fontdir."*.ttf");
		$this->FontFamily=$files[array_rand($files,1)];
		//code
		$code=$this->Code();
		$size=$this->ImgHeight * $this->FontSize; //dimensione testo
		$text_color=$this->ColorAllocate($this->TextColor); //colore testo 
		$text_code=imagettfbbox($size,0,$this->FontFamily,$code); //testo
		//posizionamento
		$x=($this->ImgWidth - $text_code[4])/2;
		$y=($this->ImgHeight - $text_code[5])/2;
		
		$captcha_code=imagettftext($this->CaptchaImage,$size,0,$x,$y,$text_color,$this->FontFamily,$code);
		return $captcha_code;
	}
	
	protected function Code(){
		$code='';
		$this->CharSet='abcdefghijkmnopqrstuvwxzyABCDEFGHJKLMNPQRSTUVWXZY0123456789'; //set caratteri validi
		
		for($count=0; $count<$this->CodeLength; $count++) {
			$code .= substr($this->CharSet,mt_rand(0, strlen($this->CharSet)-1),1);
		}
		if(!empty($code)) {
			if($this->CodeCI===0) { $code=strtolower($code); } //forzo minuscole 
			$_SESSION['pa_captchacode_'.$this->CaptchaName]=$code;
		}
		return $code;
	}
	
	protected function ColorAllocate($color){
		$array_color=$this->HexToRgb($color);
		$image_color=imagecolorallocate($this->CaptchaImage,$array_color['red'],$array_color['green'],$array_color['blue']);
		return $image_color;
	}
	
	protected function HexToRgb($color){
		$integar=hexdec($color);
		return array(
			"red" => 0xFF & ($integar >> 0x10),
			"green" => 0xFF & ($integar >> 0x8),
			"blue" => 0xFF & $integar
		);
	}
	
	
	protected static function CaptchaStartSession($SessionName='PaCaptcha'){
		if (session_id()=='' || (function_exists('session_status') && session_status()==PHP_SESSION_NONE)) {
			$configSession=self::ConfigSession('file');
			//$configSession=self::ConfigSession('db');
			if(!empty($configSession)) {
				$configSession->SetName($SessionName);
				$configSession->StartSession();
			} else {
				//default
				session_name($this->SessionName=$SessionName);
				session_start();
			}			
		}
	}
	
	protected static function ConfigSession($type, $path=null){
		switch($type) {
			case 'file':
			$secsess=DATAROOT.INC.'/class-secureSession.php';
			if(file_exists($secsess)) {
				require_once(DATAROOT.INC.'/class-secureSession.php');
				$session=new SecuritySession();
				if(isset($path) && realpath($path)) {
					$session->setSave($path);
				}
				return $session;
			} else {
				throw new InvalidArgumentexception($secsess.' non esiste');
			}
			break;
			
			case 'db':
			$secsess=DATAROOT.INC.'/class-mysqlSession.php';
			if(file_exists($secsess)) {
				require_once(DATAROOT.INC.'/class-mysqlSession.php');
				return $session=new mysqlSession($dbh);
			} else {
				throw new InvalidArgumentexception($secsess.' non esiste');
			}
			break;
			
		}
	}
}

//RELOAD CAPTCHA
if(isset($_GET['r']) && $_GET['r']==='reload'){
	if(!empty($_POST['sendOption'])) {
		require_once('../config.php');
		$customOption=json_decode($_POST['sendOption']); //ajax post
		if(!empty($customOption) && PaCaptcha::FilterText($customOption->CaptchaName) && PaCaptcha::FilterText($customOption->SessionName)) {
			$rcaptcha=new PaCaptcha($customOption->CaptchaName, $customOption->SessionName);
			if($rcaptcha->SetAllOption($customOption)) {
				echo $rcaptcha->ImgCaptchaInline();
			}
		} else {
			throw new InvalidArgumentException('problema con i dati ricevuti');
		}
	}
}
?>