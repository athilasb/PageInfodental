<?php
	if($_POST['ajax']=="wlib" and isset($_POST['estado'])) {
		$dir="../";
		require_once("../lib/conf.php");
		require_once("../usuarios/checa.php");
		$sql = new Mysql();
		$cidades=array();

		$estado=addslashes($_POST['estado']);
		$sql->consult($_p."cidades","*","where uf='".$estado."' order by capital desc, titulo asc") ;
		if($sql->rows) {
			while($x=mysqli_fetch_object($sql->mysqry)) {
				$cidades[]=array('id'=>$x->id,'cidade'=>utf8_encode($x->titulo));
			}
		}
		header('Content-Type: application/json');

		echo json_encode($cidades);
	}
	
?>