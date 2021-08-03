<?php
	require_once("../lib/conf.php");
	require_once("../lib/classes.php");

	$bi = new BI(array('prefixo'=>$_p));

	$bi->classificaTodos();
?>