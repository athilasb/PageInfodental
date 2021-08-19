<?php
	
	$result=array();
	if(isset($_POST['ajax']) and $_POST['ajax']=="wlibweb" and isset($_POST['id_menu']) and is_numeric($_POST['id_menu']) ) {
		$dir="../";
		require_once("../lib/conf.php");
		require_once("../usuarios/checa.php");
		$sql = new Mysql();
		$str = new String();
		$sql->consult($_p."servicos_submenu","*","where id_menu='".$_POST['id_menu']."' and lixo=0 order by titulo asc");
		if($sql->rows) {
			
			 while($x = mysqli_fetch_object($sql->mysqry)) {
	            $result[] = array('id'=>$x->id,'title'=>utf8_encode($x->titulo));
	    	}
		}
		
	}
	header('Content-Type: application/json');
	echo json_encode($result);
?>