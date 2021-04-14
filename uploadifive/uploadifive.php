<?php
/*
UploadiFive
Copyright (c) 2012 Reactive Apps, Ronnie Garcia
*/
$arq=fopen("arq.txt","w");
fputs($arq,"1\r\n");

require_once("../lib/classes.php");



if($_POST['token'] and !empty($_FILES) and $_POST['dir'] and $_POST['tabela']) {
fputs($arq,"2\r\n");
	
	// Set the uplaod directory
	$targetFolder = $_POST['dir'];
	
	$sql= new Mysql();
	
	
	$ext=explode(".",$_FILES['Filedata']['name']);
	$ext=strtolower($ext[count($ext)-1]);
	
	if(isset($_POST['ref']) and !empty($_POST['ref'])) {
		$sql->add($_POST['tabela'],"lixo='0', ".$_POST['ref']."='".$_POST[$_POST['ref']]."', data_foto=now(), foto='".$ext."'");
	} else {
		$sql->add($_POST['tabela'],"lixo='0', data_foto=now(), foto='".$ext."'");
	}
	$ulid=$sql->ulid;
		
	fputs($arq,"3\r\n");

	// Set the allowed file extensions
	$fileTypes = array('jpg', 'jpeg'); // Allowed file extensions

	$verifyToken = md5('unique_salt' . $_POST['timestamp']);

	if (!empty($_FILES) && $_POST['token'] == $verifyToken) {

		$tempFile = $_FILES['Filedata']['tmp_name'];
		$targetPath = $targetFolder;
		$targetFile = rtrim($targetPath,'/') . '/' . $ulid.".".$ext;

		// Validate the file type
		$fileTypes = array('jpg','jpeg','JPG','JPEG','png','PNG','gif','GIF'); // File extensions
		$fileParts = pathinfo($_FILES['Filedata']['name']);




		if (in_array(strtolower($fileParts['extension']), $fileTypes)) {



			$img=new Canvas();
				$img->carrega( $tempFile )
				->redimensiona( 800, '' )
				->hexa( '#FFFFFF' )
				->grava($targetFile);
			// Save the file
			//move_uploaded_file($tempFile, $targetFile);
			//echo 1;

		} else {


			$sql->del($_POST['tabela'],"where id='".$ulid."'");
			// The file type wasn't allowed
			echo 'Invalid file type.';

		}
	}

}
fclose($arq);
?>