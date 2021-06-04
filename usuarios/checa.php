<?php
	if(!isset($dir)) $dir="";
	if(!isset($wtalk)) $wtalk="";
	require_once($dir."lib/classes.php");
	if(isset($_COOKIE[$_p.'adm_cpf']) and isset($_COOKIE[$_p.'adm_senha']) and isset($_COOKIE[$_p.'adm_id'])) {
		$sql = new Mysql();
		
		$sql->consult($_p."usuarios","*","where id='".addslashes($_COOKIE[$_p.'adm_id'])."' and cpf='".addslashes($_COOKIE[$_p.'adm_cpf'])."' and senha='".addslashes($_COOKIE[$_p.'adm_senha'])."' and lixo='0'");
																
		if($sql->rows) {
			$usr = mysqli_fetch_object($sql->mysqry);
			$_usuariosPermissoes=explode(",",$usr->permissoes);
			
			$localIP = '';//getHostByName(getHostName());
			$sql->add($_p."log_sessoes","data=now(),id_usuario='".$usr->id."',ip='".$_SERVER['REMOTE_ADDR']."',ip_lan='".$localIP."',pagina='".$_SERVER['REQUEST_URI']."'");
			
			if($usr->pub==0) {
				if(empty($wtalk)) header("Location: ".(empty($dir)?".":$dir)."/?erro=6&url=".$_SERVER['REQUEST_URI']);
			}


			$_unidades=$_optUnidades=array();
			$sql->consult($_p."unidades","*","where lixo=0  order by titulo asc");
			while($x=mysqli_fetch_object($sql->mysqry)) {
				$_unidades[$x->id]=$x;
				$_optUnidades[$x->id]=$x;
			}

			if(isset($_GET['alterarUnidade']) and is_numeric($_GET['alterarUnidade']) and isset($_optUnidades[$_GET['alterarUnidade']])) {
				setcookie($_p."adm_unidade", $_optUnidades[$_GET['alterarUnidade']]->id, time() + 3600*24, '/');
				$usrUnidade=$_optUnidades[$_GET['alterarUnidade']];
			} else if(isset($_COOKIE[$_p.'adm_unidade']) and is_numeric($_COOKIE[$_p.'adm_unidade']) and $_optUnidades[$_COOKIE[$_p.'adm_unidade']]) {
				$usrUnidade=$_optUnidades[$_COOKIE[$_p.'adm_unidade']];
			} else {
				foreach($_optUnidades as $v) {
					$usrUnidade=$v;
					break;
				}
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