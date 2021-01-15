<?php
error_reporting(E_ALL); ini_set('display_errors', 1);
require_once('config.php');

#SESSIONE MYSQL
require_once(DATAROOT.INC.'/class-mysqlSession.php'); //salvo sessione in db
$session_db=new mysqlSession($dbh);
$session_db->SetName('PaSession');
$session_db->StartSession();

#CAPTCHA
require_once(DATAROOT.INC.'/class-captcha.php'); //includo class-captcha.php

#RECUPERO CODICI IMMESSI
$code1=(isset($_POST['input1']))? $_POST['input1'] : '';
$code2=(isset($_POST['input2']))? $_POST['input2'] : '';

#VERIFICA 1
if(PaCaptcha::ValidateCaptcha('CaptchaName1', $code1, $session_db->GetName())===TRUE) {
	echo "<h1>CaptchaName1 VALIDO!</h1>";
} else {
	echo "<h1>CaptchaName1 NON VALIDO!</h1>";
}

#VERIFICA 2
if(PaCaptcha::ValidateCaptcha('CaptchaName2', $code2, $session_db->GetName())===TRUE) {
	echo "<h1>CaptchaName2 VALIDO!</h1>";
} else {
	echo "<h1>CaptchaName2 NON VALIDO!</h1>";
}
?>