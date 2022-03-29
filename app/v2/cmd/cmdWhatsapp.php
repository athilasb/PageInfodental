<?php
	
	require_once("../lib/conf.php");
	require_once("../lib/classes.php");


	$usr = (object) array('id'=>1);

	$attr=array('prefixo'=>$_p,'usr'=>$usr);
	$wts = new Whatsapp($attr);

	echo "Starting...";

	echo "<hr >";

	// 
	echo "<h1>"

?>