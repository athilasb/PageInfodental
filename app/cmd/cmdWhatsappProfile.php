<?php
	require_once("../lib/conf.php");
	require_once("../lib/classes.php");

	$sql = new Mysql();
	$usr = (object) array('id'=>1);

	$attr=array('prefixo'=>$_p,'usr'=>$usr);
	$wts = new Whatsapp($attr);


	$sql->consult($_p."pacientes","id,telefone1,nome","where lixo=0 limit 10");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		echo $x->nome." ($x->id) => ";
		if($wts->atualizaFoto($x->id)) echo "ok";
		else echo $wts->erro;
		echo "<BR>";
	}
	die();

	if($wts->atualizaFoto(8152)) {
		echo "ok";
	} else {
		echo $wts->erro;
	}

	die();

	$attr=array('instance'=>'556282433773',
				//'numero'=>'62982400606',
				'numero'=>'62999181775'
			);
	if($wts->getProfile($attr)) {

		if(isset($wts->response->pictureUrl)) {


			$_dir="arqs/temp/";
			$img = "../".$_dir."wtsTemp.jpg";
			$url=$wts->response->pictureUrl;
			if(file_put_contents($img, file_get_contents($url))) {

				// upload da foto 
				$uploadFile=$img;
				$uploadPathFile=$_wasabiPathRoot."arqs/clientes/1.jpg";

				// upload da foto 
				$uploadFile=$img;
				$uploadType=filesize($img);
				$uploadPathFile=$_wasabiPathRoot."arqs/clientes/1.jpg";
				$uploaded=$wasabiS3->putObject(S3::inputFile($uploadFile,false),$_wasabiBucket,$uploadPathFile,S3::ACL_PUBLIC_READ);
				
				
				var_dump($uploaded);
			}
		}

	} else {
		echo "Erro: ".$wts->erro;
	}


?>