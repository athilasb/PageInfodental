<?php
	if(isset($_POST['ajax']) and $_POST['ajax']=="wlibweb" and isset($_POST['login'])) {
		$dir="../";
		require_once("../lib/conf.php");
		require_once("../usuarios/checa.php");
		$sql->consult($_p."usuarios","*","where login='".addslashes($_POST['login'])."' and lixo=0");
		if($sql->rows) {
			echo 1;
		}
	}
	echo 0;
?>