<?php
	require_once("../lib/conf.php");
	require_once("../lib/class/classMysql.php");
	$_rabbitmqFila="infozap_".$_ENV['NAME'];

	echo "enviando $_rabbitmqFila<BR><BR>";
	$wts = new Whatsapp(array('prefixo'=>$_p)); 
	$sqlWts = new Mysql(true);

	$numero="6282400606";




	$message=json_encode(array('type'=>'sendTextMessage',
									   'data'=>array('number'=>$wts->wtsNumero($numero),
											         'text'=>'Hello Word!')));


	# envia uma id_whatsapp da tabela ident_whatstapp_mensagens #

		/*if($wts->wtsRabbitmq(1271)) {
			echo 'ok '.$_ENV['NAME'];
		}*/


	# envio direto #

		$_rabbitMQServer='51.158.67.192';
		$_rabbitMQPort='5672';
		$_rabbitMQUsername='infozap';
		$_rabbitMQPassword='zapInf0@#';
		
		$dir="../";
		require_once $dir.'vendor/autoload.php';
		require_once $dir.'lib/class/classRabbitMQ.php';

		$rabbitmq = new RabbitMQ(array(
			'host' => $_rabbitMQServer,
			'port' => $_rabbitMQPort,
			'username' => $_rabbitMQUsername,
			'password' => $_rabbitMQPassword
		));

		$isConnected = $rabbitmq->createConnection();

		if ($isConnected) {
			$rabbitmq->setQueue($_rabbitmqFila);
			if($rabbitmq->sendMessageToQueueWts($message,$_rabbitmqFila)) {
				echo "succes!";
			} else {
				echo "erro!";
			}
		}
?>