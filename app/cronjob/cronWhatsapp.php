<?php
	//require_once("../lib/conf.php");
	//require_once("../lib/classes.php");


	require_once("/var/www/html/lib/conf.php");
	require_once("/var/www/html/lib/classes.php");

	$sql = new Mysql(true);
	$usr = (object) array('id'=>1);

	$attr=array('prefixo'=>$_p,'usr'=>$usr);
	$wts = new Whatsapp($attr);



	# Envia confirmacao de 24-48h para agendamentos realizados a menos de 7 dias (id_tipo=1)
		echo "<h1>Lembrete de 23h-24h</h1>";
		$dataInicio = date('Y-m-d H:i:s',strtotime(date('Y-m-d H:i:s')." + 23 hours"));
		$dataFim = date('Y-m-d H:i:s',strtotime(date('Y-m-d H:i:s')." + 24 hours"));

		if(date('w')==5) {
		//	$dataFim = date('Y-m-d H:i:s',strtotime(date('Y-m-d H:i:s')." + 72 hours"));
		}

		// agendamentos nas proximas 23h-24h
		$sql->consult($_p."agenda","*","where agenda_data >= '$dataInicio' and 
												agenda_data <= '$dataFim' and id_status=1 and lixo=0 order by agenda_data asc");
		//echo $dataInicio."<br />".$dataFim."<BR>Resultado: $sql->rows<BR><BR>";die();
		if($sql->rows) {
			while($x=mysqli_fetch_object($sql->mysqry)) {

				$dif = strtotime(date('Y-m-d H:i:s')) - strtotime($x->data);
				$dif /= 60 * 60 *24;
				$dif= round($dif);
				echo $x->id." ".$dif."<BR>";
				if($dif<=7) { 
					echo $x->data." - $dif - -> ".$x->agenda_data." -> <BR> -> $x->id";
					$attr=array('id_tipo'=>1,
								'id_paciente'=>$x->id_paciente,
								'id_agenda'=>$x->id);
					if($wts->adicionaNaFila($attr)) echo "<BR>Sucesso!";
					else echo "<BR>Erro: ".$wts->erro;
					echo "<hr>";
				}
			}
		}

	# Envia confirmacao de 47-48h para agendamentos realizados a mais de 7 dias (id_tipo=1)
		echo "<h1>Lembrete de 47h-48h</h1>";
		$dataInicio = date('Y-m-d H:i:s',strtotime(date('Y-m-d H:i:s')." + 47 hours"));
		$dataFim = date('Y-m-d H:i:s',strtotime(date('Y-m-d H:i:s')." + 48 hours"));

		if(date('w')==5) {
		//	$dataFim = date('Y-m-d H:i:s',strtotime(date('Y-m-d H:i:s')." + 72 hours"));
		}

		// agendamentos nas proximas 23h-24h
		$sql->consult($_p."agenda","*","where agenda_data >= '$dataInicio' and 
												agenda_data <= '$dataFim' and id_status=1 and lixo=0 order by agenda_data asc");
		echo $dataInicio."<br />".$dataFim."<BR>Resultado: $sql->rows<BR><BR>";
		if($sql->rows) {
			while($x=mysqli_fetch_object($sql->mysqry)) {

				$dif = strtotime(date('Y-m-d H:i:s')) - strtotime($x->data);
				$dif /= 60 * 60 *24;
				$dif= round($dif);

				if($dif>7) {
					echo $x->data." - $dif - -> ".$x->agenda_data." -> <BR> -> $x->id";
					$attr=array('id_tipo'=>1,
								'id_paciente'=>$x->id_paciente,
								'id_agenda'=>$x->id);
					if($wts->adicionaNaFila($attr)) echo "<BR>Sucesso!";
					else echo "<BR>Erro: ".$wts->erro;
					echo "<hr>";
				}
			}
		}

	# Envia confirmacao de 3h de antecedencia para agendamentos confirmados (id_tipo=2)
		echo "<h1>Lembrete de 3h</h1>";
		$dataInicio = date('Y-m-d H:i:s',strtotime(date('Y-m-d H:i:s')." + 1 hours"));
		$dataFim = date('Y-m-d H:i:s',strtotime(date('Y-m-d H:i:s')." + 3 hours"));

	

		$sql->consult($_p."agenda","*","where agenda_data >= '$dataInicio' and 
												agenda_data <= '$dataFim' and id_status=2 and lixo=0 order by agenda_data asc");
		echo $dataInicio."<br />".$dataFim."<BR>Resultado: $sql->rows<BR><BR>";
		if($sql->rows) {
			while($x=mysqli_fetch_object($sql->mysqry)) {
			
				$attr=array('id_tipo'=>2,
							'id_paciente'=>$x->id_paciente,
							'id_agenda'=>$x->id);
				//var_dump($attr);
				if($wts->adicionaNaFila($attr)) echo "ok";
				else echo "erro: ".$wts->erro;
				
				echo "<hr>";
			}
		}

	# Envia mensagens que não foram enviadas por falta de conexao

		$sql->consult($_p."whatsapp_mensagens","*","where data > NOW() - INTERVAL 24 HOUR and id_tipo=1 and erro=1");
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
			$sql->consult($_p."agenda","id","where id IN (".implode(",",$agendaIds).") and id_status=1 and agenda_data>'".date('Y-m-d H:i:s')."' and lixo=0");
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

						$sql->update($_p."whatsapp_mensagens",$vSQL,$vWHERE);

					}
				}
			}
		}

	# Envia mensagens notificando alteracao de data

		$dataInicio=date('Y-m-d H:i',strtotime(date('Y-m-d H:i')." - 60 minutes"));
		$dataFim=date('Y-m-d H:i',strtotime(date('Y-m-d H:i')." - 31 minutes"));

		$where="where agenda_alteracao_data>='".$dataInicio."' and agenda_alteracao_data<='".$dataFim."' and id_status=2 and agenda_alteracao_id_whatsapp=0 and lixo=0";

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

	# Dispara

		//if(isset($_GET['dispara'])) {
			if($wts->dispara()) echo "Disparado!";
			else echo "Erro: $wts->erro";
		//}
?>