<?php
	
	require_once("../lib/conf.php");
	require_once("../lib/classes.php");

	$sql = new Mysql();
	$usr = (object) array('id'=>1);



	/*$sql->consult($_p."pacientes_evolucoes","*","where id_tipo=9 and lixo=0");
	$evolucoes=array();
	$evolucoesIds=array(0);
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$evolucoes[$x->id]=$x;
		$evolucoesIds[]=$x->id;
	}

	$sql->consult($_p."pacientes_evolucoes_geral","*","where id_evolucao IN (".implode(",",$evolucoesIds).")");
	if($sql->rows) {
		while($x=mysqli_fetch_object($sql->mysqry)) {
			if(isset($evolucoes[$x->id_evolucao])) {
				$ev=$evolucoes[$x->id_evolucao];

				echo $ev->data." ".$x->data."<BR>";
				//$sql->update($_p."pacientes_evolucoes","data='".$x->data."'","where id=$ev->id");
			}
		}
	}*/

	$registros=array();
	$sql->consult($_p."pacientes_prontuarios","*","where lixo=0");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$registros[]=$x;
	}



	foreach($registros as $x) {
		$vSQL="data='$x->data',
				id_usuario=$x->id_usuario,
				id_profissional=$x->id_usuario,
				texto='".addslashes($x->texto)."'";

		/*$sql->add($_p."pacientes_evolucoes","data=now(),
											id_tipo=9,
											id_paciente=$x->id_paciente,
											id_usuario=$x->id_usuario,
											id_profissional='".$x->id_usuario."'");
		$id_evolucao=$sql->ulid;


		$sql->add($_p."pacientes_evolucoes_geral",$vSQL.",id_evolucao=$id_evolucao");*/


	}

?>