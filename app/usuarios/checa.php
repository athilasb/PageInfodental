<?php
	if(!isset($dir)) $dir="";
	if(!isset($wtalk)) $wtalk="";
	require_once($dir."lib/classes.php");
	if(isset($_COOKIE[$_p.'adm_cpf']) and isset($_COOKIE[$_p.'adm_senha']) and isset($_COOKIE[$_p.'adm_id'])) {
		$sql = new Mysql();
		
		$sql->consult($_p."colaboradores","*","where id='".addslashes($_COOKIE[$_p.'adm_id'])."' and cpf='".addslashes($_COOKIE[$_p.'adm_cpf'])."' and senha='".addslashes($_COOKIE[$_p.'adm_senha'])."' and lixo='0'");
															
		if($sql->rows) {
			$usr = mysqli_fetch_object($sql->mysqry);
			//$_usuariosPermissoes=explode(",",$usr->permissoes);
			
			$localIP = '';//getHostByName(getHostName());
			$sql->add($_p."log_sessoes","data=now(),id_usuario='".$usr->id."',ip='".$_SERVER['REMOTE_ADDR']."',ip_lan='".$localIP."',pagina='".$_SERVER['REQUEST_URI']."'");
			
			if($usr->permitir_acesso==0) {
				if(empty($wtalk)) header("Location: ".(empty($dir)?".":$dir)."/?erro=6&url=".$_SERVER['REQUEST_URI']);
			}

			
		}


		else {
			if(empty($wtalk)) header("Location: ".(empty($dir)?".":$dir)."/?erro=2&url=".$_SERVER['REQUEST_URI']);
			//die();
		}
	} else {
		if(empty($wtalk)) header("Location: ".(empty($dir)?".":$dir)."/?erro=2&url=".$_SERVER['REQUEST_URI']);
		//die();
	}
	
?>