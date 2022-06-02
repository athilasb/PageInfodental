<?php

	require_once("../lib/conf.php");
	require_once("../lib/classes.php");

	
	$usr = (object) array('id'=>1);

	$sql = new Mysql();

	$agenda = array();
	$agendaNumero = array();
	$pacientesIds=array();
	$sql->consult($_p."agenda","id,id_paciente","where id_status=5 and lixo=0");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$agenda[$x->id_paciente][]=$x;

		if(!isset($agendaNumero[$x->id_paciente])) $agendaNumero[$x->id_paciente]=0;
		$agendaNumero[$x->id_paciente]++;
	}



	arsort($agendaNumero);
	var_dump($agendaNumero);



?>