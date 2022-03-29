<?php
	
	require_once("../lib/conf.php");
	require_once("../lib/classes.php");


	$usr = (object) array('id'=>1);

	$attr=array('prefixo'=>$_p,'usr'=>$usr);
	$wts = new Whatsapp($attr);

	echo "Starting...";

	echo "<hr >";

	// Novo Agendamento
	echo "<h1>Confirmação de Agendamento</h1>";
	$attr=array('id_tipo'=>1,
				'id_paciente'=>6216,
				'id_agenda'=>10348);
	if($wts->adicionaNaFila($attr)) echo "ok";
	else echo "erro: ".$wts->erro;
	echo "<br />";




	if(isset($_GET['dispara'])) {

		if($wts->dispara()) echo "Disparado!";
		else echo "Erro: $wts->erro";
	}
?>