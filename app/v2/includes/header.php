<?php
     
      if(basename($_SERVER['PHP_SELF'])=="index.php") {
            require_once("lib/conf.php");
            require_once("lib/classes.php");
            if(isset($_COOKIE[$_p.'adm_cpf']) and isset($_COOKIE[$_p.'adm_senha']) and isset($_COOKIE[$_p.'adm_id'])) {
                  $str = new StringW();         
                  $sql = new Mysql();
                  $sql->consult($_p."colaboradores","*","where id='".addslashes($_COOKIE[$_p.'adm_id'])."' and 
                                                                                                      cpf='".addslashes($_COOKIE[$_p.'adm_cpf'])."' and 
                                                                                                      senha='".addslashes($_COOKIE[$_p.'adm_senha'])."' and 
                                                                                                      permitir_acesso='1'");
                  if($sql->rows) {
                        header("Location: dashboard.php");
                        echo "<html><head><title>Redirecionando...</title></head><body><font size=4>Redirecionando...</font></body></html>";
                        die();
                  }
            }
      } else {
            
            require_once("lib/conf.php");
            require_once("usuarios/checa.php");
            
            $sql=new Mysql();
            $str=new StringW();
            $jsc=new Js();

            $adm = new Adm($_p);
            $url=$adm->url($_GET);
      }


      $_page=basename($_SERVER['PHP_SELF']);
?>
<!doctype html>
<html xmlns="http://www.w3.org/1999/xhtml"
      xmlns:og="http://ogp.me/ns#"
      xmlns:fb="http://www.facebook.com/2008/fbml">

<head>
<meta charset="utf-8">

<title><?php echo isset($title)?$title." | Infodental":"Infodental"; ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
<meta name="description" content="<?php echo ($description)?$description:"Infodental"; ?>">
<meta name="author" content="WLIB Soluções Web - www.wlib.com.br">


<base href="//<?php echo $_SERVER["HTTP_HOST"];?>/v2/" />


<meta property="og:title" content="<?php echo $title; ?>" />
<meta property="og:description" content="<?php echo ($description)?$description:"Infodental"; ?>" />
<meta property="og:type" content="website" />
<meta property="og:url" content="http://<?php echo $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];?>" />
<meta property="og:image" content="http://<?php echo $_SERVER['HTTP_HOST'];?>/img/facebook.png" />
<meta property="og:image:width" content="1300" />
<meta property="og:image:height" content="700" />
<meta property="og:site_name" content="Infodental" />
<meta property="fb:admins" content="1066108721" />

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Source+Sans+Pro:ital,wght@0,400;0,700;1,400;1,700&display=swap" rel="stylesheet">
<link rel="stylesheet" type="text/css" href="css/style.css" />
<link rel="stylesheet" type="text/css" href="css/apps.css" />

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
<script defer type="text/javascript" src="js/jquery.slick.js"></script>
<script defer type="text/javascript" src="js/jquery.datetimepicker.js"></script>
<script defer type="text/javascript" src="js/jquery.chosen.js"></script>
<script defer type="text/javascript" src="js/jquery.fancybox.js"></script>
<script defer type="text/javascript" src="js/jquery.tablesorter.js"></script>
<script defer type="text/javascript" src="js/jquery.inputmask.js"></script>
<script defer type="text/javascript" src="js/jquery.money.js"></script>
<script defer type="text/javascript" src="js/jquery.caret.js"></script>
<script defer type="text/javascript" src="js/jquery.mobilePhoneNumber.js"></script>
<script type="text/javascript" src="js/jquery.sweetalert.js"></script>
<script type="text/javascript" src="js/jquery.validacao.js"></script>
<script type="text/javascript" src="js/jquery.funcoes.js"></script>
<script defer src="https://code.iconify.design/1/1.0.3/iconify.min.js"></script>

</head>

<body>

<section class="wrapper">