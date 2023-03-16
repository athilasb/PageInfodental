<?php
	require_once("../lib/conf.php");
	require_once("../lib/class/classMysql.php");

	$wts = new Whatsapp(array('prefixo'=>$_p)); 
	$sqlWts = new Mysql(true);

	$numero="6282400606";
	$message=json_encode(array('type'=>'sendTextMessage',
									   'data'=>array('number'=>$wts->wtsNumero($numero),
											         'text'=>'Hello Word!')));

	if($wts->wtsRabbitmq(1271)) {
		echo 'ok';
	}
?>