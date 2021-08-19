<?php
	session_start();
	require_once("../lib/conf.php");
	$dir="../";
	require_once("../usuarios/checa.php");
	
	
	$_SESSION[$_p.'adm_id']="";
	$_SESSION[$_p.'adm_senha']="";
	$_SESSION[$_p.'adm_cpf']="";
	$_SESSION[$_p.'adm_empresa']="";
	
	setcookie($_p.'adm_id',null,-1,'/');
	setcookie($_p.'adm_senha',null,-1,'/');
	setcookie($_p.'adm_cpf',null,-1,'/');
	setcookie($_p.'adm_empresa',null,-1,'/');
	unset($_COOKIE[$_p.'adm_id']);
	unset($_COOKIE[$_p.'adm_senha']);
	unset($_COOKIE[$_p.'adm_cpf']);
	unset($_COOKIE[$_p.'adm_empresa']);
	
	header("Location: ../");
?>