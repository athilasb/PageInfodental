<?php
	
	require_once("../lib/conf.php");
	require_once("../lib/classes.php");

	
	$usr = (object) array('id'=>1);

	$attr=array('prefixo'=>$_p,'usr'=>$usr);
	$wts = new Whatsapp($attr);

	if(isset($_GET['dispara'])) {

		if($wts->dispara()) echo "Disparado!";
		else echo "Erro: $wts->erro";
		die();
	}


	$sql = new Mysql();
	$sql->consult($_p."whatsapp_mensagens","*","where data > NOW() - INTERVAL 24 HOUR and id_tipo=1 and erro=1");
	echo $sql->rows;
	if($sql->rows) {

		$agendaIds=$agendamentosNaoConfirmados=$regs=array();
		while($x=mysqli_fetch_object($sql->mysqry)) {
			if($x->erro_retorno=="whatsapp desconectado") {
				$regs[]=$x;
				$agendaIds[]=$x->id_agenda;
				$agendamentosNaoConfirmados[$x->id_agenda]=$x;
			}
		}


		// id_status = 1 -> a confirmar
		$agendasIdsAindaAConfirmar=array();
		$sql->consult($_p."agenda","id","where id IN (".implode(",",$agendaIds).") and id_status=5 and lixo=0");
		if($sql->rows) {
			while($x=mysqli_fetch_object($sql->mysqry)) {
				$agendasIdsAindaAConfirmar[$x->id]=$x;
			}
		}


		foreach($regs as $x) {
			if($x->erro_retorno=="whatsapp desconectado") {
				$agendaIds[]=$x->id_agenda;
			}
		}

		foreach($agendamentosNaoConfirmados as $idAgenda=>$v) {
			if(isset($agendasIdsAindaAConfirmar[$idAgenda])) {
				if(isset($agendamentosNaoConfirmados[$idAgenda])) {
					$wObj=$agendamentosNaoConfirmados[$idAgenda];


					$vSQL="data=now(),
							erro=0,
							enviado=0,
							semConexao=now()";

					$vWHERE="where id=$wObj->id";

					echo $vSQL." ".$vWHERE;

					$sql->update($_p."whatsapp_mensagens",$vSQL,$vWHERE);

				}
			}
		}
	}
	die();



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




?>