<?php
require_once "../lib/conf.php";
require_once "../lib/classes.php";
$sql = new Mysql();
$jsc = new Js();

$landingpage=$sobrenos=$title="";
if(isset($_GET['code']) and !empty($_GET['code'])) {
	$sql->consult($_p."landingpage_temas","*","WHERE code='".addslashes($_GET['code'])."' and lixo=0");
	if($sql->rows) {
		$landingpage = mysqli_fetch_object($sql->mysqry);
	}
}

if(is_object($landingpage)) {
   $sql->consult($_p."landingpage_sobreaclinica","*","WHERE id_tema='".$landingpage->id."' and lixo=0"); 
   if($sql->rows) {
	$sobrenos=mysqli_fetch_object($sql->mysqry);
   }
}
?>
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

<?php 
    if(is_object($landingpage) and !empty($landingpage->cor_primaria) and !empty($landingpage->cor_secundaria)) {
?>
<style type="text/css">
	:root {
		--cor1:<?php echo $landingpage->cor_primaria;?>;
		--cor2:<?php echo $landingpage->cor_secundaria;?>;
	}
</style>
<?php
    }
?>

<section class="wrapper">