<!doctype html>
<html xmlns="http://www.w3.org/1999/xhtml"
      xmlns:og="http://ogp.me/ns#"
      xmlns:fb="http://www.facebook.com/2008/fbml">

<head>
<meta charset="utf-8">

<title><?php echo ($title)?$title." | STUDIO DENTAL":"STUDIO DENTAL"; ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
<meta name="description" content="<?php echo ($description)?$description:"DESCRIÇÃO"; ?>">
<meta name="author" content="WLIB Soluções Web - www.wlib.com.br">

<?php if($_SERVER["HTTP_HOST"]=="localhost" or $_SERVER["HTTP_HOST"]=="127.0.0.1") { ?>
<base href="http://localhost/infodental/lp/trunk/" />
<?php } else { ?>
<base href="//<?php echo $_SERVER["HTTP_HOST"];?>/lp/" />
<?php } ?>

<meta property="og:title" content="<?php echo $title; ?>" />
<meta property="og:description" content="<?php echo ($description)?$description:"Descrição"; ?>" />
<meta property="og:type" content="website" />
<meta property="og:url" content="http://<?php echo $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];?>" />
<meta property="og:image" content="http://<?php echo $_SERVER['HTTP_HOST'];?>/img/facebook.png" />
<meta property="og:image:width" content="1300" />
<meta property="og:image:height" content="700" />
<meta property="og:site_name" content="STUDIO DENTAL" />
<meta property="fb:admins" content="1066108721" />

<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;700&display=swap" rel="stylesheet">
<link rel="stylesheet" type="text/css" href="css/style.css" />
<link rel="stylesheet" type="text/css" href="css/apps.css" />

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
<script defer type="text/javascript" src="js/jquery.slick.js"></script>
<script defer type="text/javascript" src="js/jquery.fancybox.js"></script>
<script type="text/javascript" src="js/jquery.inputmask.js"></script>
<script type="text/javascript" src="js/jquery.sweetalert.js"></script>
<script type="text/javascript" src="js/jquery.typed.js"></script>
<script type="text/javascript" src="js/jquery.validacao.js"></script>
<script type="text/javascript" src="js/jquery.funcoes.js"></script>
<script defer src="https://code.iconify.design/1/1.0.3/iconify.min.js"></script>

</head>

<body>

<style>
:root {
	--cor1:#D5906B;
	--cor2:#6F615A;
}
</style>

<section class="wrapper">