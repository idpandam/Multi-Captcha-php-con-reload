<?php
error_reporting(E_ALL); ini_set('display_errors', 1);
require_once('config.php');

#SESSIONE MYSQL
/*require_once(DATAROOT.INC.'/class-mysqlSession.php'); //salvo sessione in db
$session_db=new mysqlSession($dbh);
$session_db->SetName('PaSession');
$session_db->StartSession();*/

#CAPTCHA
require_once(DATAROOT.INC.'/class-captcha.php'); //includo class-captcha.php

?>
<!DOCTYPE html>
<html>
<head>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
</head>
<body>
<?php
#PRIMO CAPTCHA
//$captcha=new PaCaptcha('CaptchaName1', $session_db->GetName());
$captcha=new PaCaptcha('CaptchaName1');
$captcha->SetImgHeight(80);
$captcha->SetCodeCI(1);
?>
<form method="post" action="validate.php">
	<div class="refresh"><?php echo $captcha->CaptchaReloadInline('test1', $captcha); //imposto reload?></div>
	<img id="test1" src="<?php echo $captcha->ImgCaptchaInline();?>" alt="test" />
	<div class="input"><?php echo $captcha->CaptchaInputText('input1'); //imposto nome input?></div>
	<input type="submit" />
</form>

<hr>

<?php
#SECONDO CAPTCHA
//$captcha2=new PaCaptcha('CaptchaName2', $session_db->GetName());
$captcha2=new PaCaptcha('CaptchaName2');
$captcha2->SetBgColor('#cccccc');
?>
<form method="post" action="validate.php">
	<div class="refresh"><?php echo $captcha2->CaptchaReloadInline('test2', $captcha2); ?></div>
	<img id="test2" src="<?php echo $captcha2->ImgCaptchaInline(); ?>" alt="test" />
	<div class="input"><?php echo $captcha2->CaptchaInputText('input2'); ?></div>
	<input type="submit" />
</form>


</body>
</html>