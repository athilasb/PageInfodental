<?php

	
	//codigo criado para adaptar a nova estrutura de procedimentos evolucao


	require_once("../lib/conf.php");
	require_once("../lib/classes.php");


	$sql = new Mysql();

	$sql->consult($_p."pacientes_tratamentos","*","where lixo=0");

	if($sql->rows) {

		$tratamentosIds=array(0);
		while($x=mysqli_fetch_object($sql->mysqry)) {
			$tratamentosIds[]=$x->id;
		}


		$sql->consult($_p."pacientes_tratamentos_procedimentos","*","where id_tratamento IN (".implode(",",$tratamentosIds).")");
		while($x=mysqli_fetch_object($sql->mysqry)) {
			

			$procedimentos[]=$x;

		}



		foreach($procedimentos as $x) {

			for($i=1;$i<=$x->quantidade;$i++) {
				$vSQL="id_tratamento_procedimento='$x->id',
						id_paciente='$x->id_paciente',
						id_profissional='$x->id_profissional',
						id_procedimento='$x->id_procedimento',
						status_evolucao='iniciar',
						numero='$i',
						numeroTotal='$x->quantidade'";

				//$sql->add($_p."pacientes_tratamentos_procedimentos_evolucao",$vSQL);
			}
		}
	}

?>