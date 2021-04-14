<?php
	$dir="../";
	/*require_once("conf.php");
	require_once("../usuarios/checa.php");
	//var_dump($usr);die();
	if($usr->tipo!="admin" and $usr->tipo!="supervisor") {
		header("Location: ../dashboard.php");
	} else {*/
		$link = $_GET['arq'];
		
		header ("Content-Disposition: attachment; filename=".$_GET['nome']."");
		header ("Content-Type: application/octet-stream");
		header ("Content-Length: ".filesize($link));
		readfile($link);
		//file_put_contents("Tmpfile.zip", fopen($_GET['arq'], 'r'));
	
	
?>