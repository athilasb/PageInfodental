<?php 
	require_once("/var/www/html/lib/conf.php");
	require_once("/var/www/html/lib/classes.php");

	$sql = new Mysql(true);
	$usr = (object) array('id'=>1);

	$attr=array('prefixo'=>$_p,'usr'=>$usr);
	$wts = new Whatsapp($attr);

	$wts->infosWasabi=array('_wasabiPathRoot'=>$_wasabiPathRoot,
							'wasabiS3'=>$wasabiS3,
							'_wasabiBucket'=>$_wasabiBucket);

	if(isset($_GET['dispara'])) {
		if($wts->dispara()) echo "Disparado!";
		else echo "Erro: $wts->erro";
		die();
	}

	$sql->consult($_p."pacientes","id,nome,data_nascimento,telefone1","WHERE month(data_nascimento)='".date('m')."' and day(data_nascimento)='".date('d')."' and telefone1<>'' and lixo=0");

	if($sql->rows) {
		echo "(Aniversariantes do dia ".date('d/m/Y').") <br />";
		while($x=mysqli_fetch_object($sql->mysqry)) {

			echo "Paciente: ".$x->nome."<br />";

			$attr=array('id_tipo'=>13,
						'id_paciente'=>$x->id,
						'cronjob'=>1);

			if($wts->adicionaNaFila($attr)) echo "Sucesso!";
			else echo "Erro: ".$wts->erro;
			echo "<hr>";
		}
	}
?>