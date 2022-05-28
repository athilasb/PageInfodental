<?php
	require_once("../lib/conf.php");
	require_once("../lib/classes.php");

	$sql = new Mysql();

	$request='{"token":"c20ef152b81e559ef400f564fd0e14651a4e6e76","instance":"studiodental","wid":"556282433773","data":{"id":{"fromMe":false,"remote":{"server":"c.us","user":"556282400606","_serialized":"556282400606@c.us"},"id":"3EB03BC81126C622382F","_serialized":"false_556282400606@c.us_3EB03BC81126C622382F"},"ack":1,"hasMedia":false,"body":"sim","type":"chat","timestamp":1648726221,"from":"556282400606@c.us","to":"556282433773@c.us","isStatus":false,"isStarred":false,"broadcast":false,"fromMe":false,"hasQuotedMsg":true,"vCards":[],"mentionedIds":[],"links":[]}}';


	$obj = json_decode($request);


	$instance = $obj->instance;
	$wid = $obj->wid;

	$conexao='';
	$sql->consult("infodentalADM.infod_contas_onlines","*","where instancia='$instance' and wid='$wid' and lixo=0");
	if($sql->rows) {
		$conexao=mysqli_fetch_object($sql->mysqry);

	
	}

	$type = $obj->data->type;
	$body = $obj->data->body;
	$from = $obj->data->from;
	$to = $obj->data->to;
	$timestamp = $obj->data->timestamp;

	echo "Conexao: $conexao->id<br />";
	echo "Instance: $instance<br />";
	echo "Wid: $wid<br />";
	echo "Type: $type<br />";
	echo "Data: ".date('Y-m-d H:i',$timestamp)." - $timestamp<br />";
	echo "From: $from<br />";
	echo "To: $to<br />";
	echo "Body: $body<br />";


?>