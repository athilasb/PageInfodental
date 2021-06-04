<?php
	if(isset($_POST['auth_cpf']) and isset($_POST['auth_senha'])) {
		
		require_once("../lib/conf.php");
		require_once("../lib/classes.php");
		
		$sql = new Mysql();
		$localIP = '';// getHostByName(getHostName());
		$cpf=addslashes($_POST['auth_cpf']);
		$senha=sha1($_POST['auth_senha']);
		$sql->consult($_p."usuarios","*","where cpf='".$cpf."' and senha='".$senha."' and pub='1' and lixo='0'");
		//echo $sql->rows;die();
			
		if($sql->rows) {
			$usr = mysqli_fetch_object($sql->mysqry);
			
			setcookie($_p."adm_cpf", $usr->cpf, time() + 3600*24, '/');
			setcookie($_p."adm_senha", $usr->senha, time() + 3600*24, '/');
			setcookie($_p."adm_id", $usr->id, time() + 3600*24, '/');
			
			
			$sql->add($_p."logins","erro=0,data=now(),ip='".$_SERVER['REMOTE_ADDR']."',id_usuario='".$usr->id."',cpf='".addslashes($_POST['auth_cpf'])."',senha='".addslashes($_POST['auth_senha'])."',ip_lan='".$localIP."'");
			
			$url=(isset($_POST['url']) and !empty($_POST['url']))?$_POST['url']:"../dashboard.php";
			header("Location: ".$url);
			
			
		} else {
			$sql->add($_p."logins","erro=1,data=now(),ip='".$_SERVER['REMOTE_ADDR']."',id_usuario=0,cpf='".addslashes($_POST['auth_cpf'])."',senha='".addslashes($_POST['auth_senha'])."',ip_lan='".$localIP."'");
			header("Location: ../?erro=1");
		}
	} else {
		header("Location: ../?erro=1");
	}
	
?>