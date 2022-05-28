<?php
	require("../lib/conf.php");
	require("../lib/classes.php");

	$sql = new Mysql();
	$file = file("pacientes.csv");

	foreach($file as $f) {
		list($idAntigo,$data,$nome,$telefone,)=explode(";",$f);

		if($nome=="Nome") continue;

		$sql->consult($_p."pacientes","*","where  nome='".addslashes($nome)."'");
		if($sql->rows) {
			$x=mysqli_fetch_object($sql->mysqry);
			echo $telefone." ".$x->telefone1;
			$vsql="data='".invDate($data)."'";
			echo $vsql;
		}

		echo $nome."->".$sql->rows."<BR>";
		die();
	}
?>