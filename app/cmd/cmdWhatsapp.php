<?php
	
	require_once("../lib/conf.php");
	require_once("../lib/classes.php");

	$sql = new Mysql();
	$usr = (object) array('id'=>1);

	$attr=array('prefixo'=>$_p,'usr'=>$usr);
	$wts = new Whatsapp($attr);


	if(isset($_GET['dispara'])) {

		if($wts->dispara()) echo "Disparado!";
		else echo "Erro: $wts->erro";
		die();
	} else if(isset($_GET['foto'])) {
		if($wts->atualizaFoto(8988)) {
			echo "ok";
			
		} else echo  "erro $wts->erro";
	} else {

		$attr=array('id_tipo'=>2,
					'id_paciente'=>6216,//8988,
					'id_agenda'=>10348);//29507);

		if($wts->adicionaNaFila($attr)) $wts=1;
		echo "aki o";
	}

	die();
	/*
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
	die();*/

	/*
	

	// -> ALTERACAO DE HORARIO
		$dataInicio=date('Y-m-d H:i',strtotime(date('Y-m-d H:i')." - 60 minutes"));
		$dataFim=date('Y-m-d H:i',strtotime(date('Y-m-d H:i')." - 31 minutes"));

		$where="where agenda_alteracao_data>='".$dataInicio."' and agenda_alteracao_data<='".$dataFim."' and id_status=5 and agenda_alteracao_id_whatsapp=0 and lixo=0";

		$sql->consult($_p."agenda","id,id_paciente,agenda_data",$where);
		echo $where."->".$sql->rows."<HR>";;
		if($sql->rows) {
			$regs=array();
			while($x=mysqli_fetch_object($sql->mysqry)) {
				$regs[]=$x;
			}

			foreach($regs as $x) {

				$attr=array('id_tipo'=>5,
							'id_paciente'=>$x->id_paciente,
							'id_agenda'=>$x->id);
				
				if($wts->adicionaNaFila($attr)) {
					echo "ok";

				}
				else echo "erro: ".$wts->erro;
			}
		}
	*/

	// -> CONFIRMAÇÃO DE AGENDAMENTO PARA DENTISTAS (id_tipo=6)

		$where="where id=27808";

		$sql->consult($_p."agenda","*",$where);
		if($sql->rows) {
			$agenda=mysqli_fetch_object($sql->mysqry);


			if(!empty($agenda->profissionais)) {

				$profissionaisIds=array();
				$auxProfissionais = explode(",",$agenda->profissionais);
				foreach($auxProfissionais as $idProfissional) {
					if(!empty($idProfissional) and is_numeric($idProfissional)) {
						$profissionaisIds[]=$idProfissional;
					}
				}

				if(count($profissionaisIds)>0) {
					$sql->consult($_p."colaboradores","*","where id IN (".implode(",",$profissionaisIds).")");
					while($x=mysqli_fetch_object($sql->mysqry)) {
						if(!empty($x->telefone1)) {
							$attr=array('id_tipo'=>6,
										'id_paciente'=>$agenda->id_paciente,
										'id_profissional'=>$x->id,
										'id_agenda'=>$agenda->id);
				
							if($wts->adicionaNaFila($attr)) {
								echo "ok";

							}
							else echo "erro: ".$wts->erro;
						}
					}
				}

			}
		}

	die();

	echo "Starting...";

	echo "<hr >";

	// Novo Agendamento
	if(1==2) {
		echo "<h1>Confirmação de Agendamento</h1>";
		$attr=array('id_tipo'=>1,
					'id_paciente'=>6216,
					'id_agenda'=>10348);
		if($wts->adicionaNaFila($attr)) echo "ok";
		else echo "erro: ".$wts->erro;
		echo "<br />";
	}





?>