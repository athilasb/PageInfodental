<?php
	if(isset($_POST['ajax']) and $_POST['ajax']=="wlib" and isset($_POST['id_categoria']) and is_numeric($_POST['id_categoria'])) {
		$dir="../";
		require_once("../lib/conf.php");
		require_once("../usuarios/checa.php");

		$result=array();
		$sql->consult($_p."produtos_subcategorias","*","where id_categoria='".$_POST['id_categoria']."' and lixo=0");
		while($x=mysqli_fetch_object($sql->mysqry)) {
			$result[]=array('id'=>$x->id,'titulo'=>$x->titulo);
		}
		
		header('Content-Type: application/json');
		echo json_encode($result);
	}
?>