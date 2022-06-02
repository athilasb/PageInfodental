<?php



	require_once("/var/www/html/lib/conf.php");
	require_once("/var/www/html/lib/classes.php");


	$sql = new Mysql();

	$sql->consult($_p."agenda","id_status,profissionais,id_paciente","where id_status=5 and lixo=0");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		if(!empty($x->profissionais)) {
			$aux=explode(",",$x->profissionais);

			foreach($aux as $idProfissional) {
				if(!empty($idProfissional) and is_numeric($idProfissional)) {
					if(!isset($pacientesProfissional[$x->id_paciente][$idProfissional])) $pacientesProfissional[$x->id_paciente][$idProfissional]=0;
					$pacientesProfissional[$x->id_paciente][$idProfissional]++;
				}
			}
		}
	}

	foreach($pacientesProfissional as $id_paciente=>$agendamentos) {
		

		if(count($agendamentos)==1) {
			foreach($agendamentos as $idProfissional=>$qtd) {
				//echo "-  $idProfissional => $qtd";
				$sql->update($_p."pacientes","profissional_maisAtende='$idProfissional'","where id=$id_paciente");
			}
		} else {
			//echo $id_paciente."=>".count($agendamentos);
			//echo '<BR>';
			//var_dump($agendamentos);
			//echo "<BR>";
			//arsort($agendamentos);
			//var_dump($agendamentos);

			foreach($agendamentos as $idProfissional=>$qtd) {
				//echo "-  $idProfissional => $qtd";break;
				$sql->update($_p."pacientes","profissional_maisAtende='$idProfissional'","where id=$id_paciente");
			}
			//echo "<HR>";
		}
	}

?>