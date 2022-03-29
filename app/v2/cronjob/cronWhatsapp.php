<?php
	require_once("../lib/conf.php");
	require_once("../lib/classes.php");

	$sql = new Mysql(true);
	$usr = (object) array('id'=>1);

	$attr=array('prefixo'=>$_p,'usr'=>$usr);
	$wts = new Whatsapp($attr);

	// agendamentos nas proximas 24h - 48h
	$sql->consult($_p."agenda","*","where agenda_data >= NOW() + INTERVAL 24 HOUR and 
											agenda_data <= NOW() + INTERVAL 48 HOUR order by agenda_data asc");
	if($sql->rows) {
		while($x=mysqli_fetch_object($sql->mysqry)) {

			$dif = strtotime(date('Y-m-d H:i:s')) - strtotime($x->data);
			$dif /= 60 * 60 *24;
			$dif= round($dif);

			if($dif>=7) {
				echo $x->data." - $dif - -> ".$x->agenda_data." -> <BR> -> $x->id";
				$attr=array('id_tipo'=>1,
							'id_paciente'=>$x->id_paciente,
							'id_agenda'=>$x->id);
				if($wts->adicionaNaFila($attr)) echo "ok";
				else echo "erro: ".$wts->erro;
				echo "<HR>";
			}
		}
	}
	echo $sql->rows;
?>