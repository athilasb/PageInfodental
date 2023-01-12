<?php
	$dir="../";
	require_once("../lib/conf.php");
	require_once("../usuarios/checa.php");

	$sql->consult($_p."clinica","*","where id=1");
	$unidade=mysqli_fetch_object($sql->mysqry);


	$endereco = $imagem = '';

	$endereco = utf8_encode($unidade->endereco);

	$sql->consult($_p."clinica","*","");
	if($sql->rows) {
		$clinica=mysqli_fetch_object($sql->mysqry);
		if(!empty($clinica->cn_logo)) {
			$imagem=$_cloudinaryURL.'c_thumb,w_600/'.$clinica->cn_logo;
		}
	}
	?>


<!doctype html>
<html xmlns="http://www.w3.org/1999/xhtml"
      xmlns:og="http://ogp.me/ns#"
      xmlns:fb="http://www.facebook.com/2008/fbml">

<head>
<meta charset="utf-8">
<title>Infodental - Impressão</title>
<link rel="stylesheet" type="text/css" href="../css/print.css" />
<script defer src="https://code.iconify.design/1/1.0.3/iconify.min.js"></script>
<script src="../js/jquery.js"></script>
</head>

<body>

<div class="print-header" style="padding-top: 20px;">
	<?php
	if(!empty($imagem)) {
	?>
	<img src="<?php echo $imagem;?>" height="50" class="print-header__logo" />
	<?php
	} else {
	?>
	<img src="../img/logo-cliente.png" height="68" class="print-header__logo" />
	<?php
	}
	?>
</div>

<div class="print-footer">
	<p><span class="iconify" data-icon="bx:bxs-phone" data-inline="true"></span><span><?php echo maskTelefone($unidade->telefone);?></span><span class="iconify" data-icon="ri:whatsapp-fill" data-inline="true"></span><span><?php echo maskTelefone($unidade->whatsapp);?></span></p>
	<p><?php echo $endereco;?></p>
	<p>
		<span><i class="iconify" data-icon="ph-globe-simple"></i> <a href="https://www.studiodental.dental"><?php echo $unidade->site;?></a></span>
		<span><i class="iconify" data-icon="ph-instagram-logo"></i> <a href="https://instagram.com/studio.dental.dental"><?php echo $unidade->instagram;?></a></span>
	</p>
</div>

<table class="print-table">
	<thead><tr><td><div class="print-table-header">&nbsp;</div></td></tr></thead>
	<tbody><tr><td>
		<section class="print-content">

			