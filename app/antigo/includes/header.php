<?php
	//session_start();
	if(basename($_SERVER['PHP_SELF'])=="index.php") {
		require_once("lib/conf.php");
		require_once("lib/classes.php");	
		$sql = new Mysql();

		if(isset($_COOKIE[$_p.'adm_cpf']) and isset($_COOKIE[$_p.'adm_senha']) and isset($_COOKIE[$_p.'adm_id'])) {
			$str = new StringW();	
			$sql->consult($_p."colaboradores","*","where id='".$str->protege($_COOKIE[$_p.'adm_id'])."' and 
																	cpf='".$str->protege($_COOKIE[$_p.'adm_cpf'])."' and 
																	senha='".$str->protege($_COOKIE[$_p.'adm_senha'])."' and 
																	permitir_acesso='1'");
			if($sql->rows) {
				header("Location: dashboard.php");
				echo "<html><head><title>Redirecionando...</title></head><body><font size=4>Redirecionando...</font></body></html>";
				die();
			}
		}

		$sql->consult($_p."colaboradores","id","where lixo=0");
		if($sql->rows==0) {
			header("Location: primeiro-acesso.php");
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
?>
<!doctype html>
<html xmlns="http://www.w3.org/1999/xhtml"
      xmlns:og="http://ogp.me/ns#"
      xmlns:fb="http://www.facebook.com/2008/fbml"> 

<head>
<meta charset="utf-8">

<title><?php echo isset($_title)?$_title:"Info Dental"; ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
<meta name="description" content="<?php echo isset($description)?$description:"DESCRIÇÃO"; ?>">
<meta name="author" content="WLIB Soluções Web - www.wlib.com.br">

<link href="https://fonts.googleapis.com/css2?family=Roboto+Condensed:wght@400;700&family=Roboto:wght@400;700&display=swap" rel="stylesheet">
<link rel="stylesheet" type="text/css" href="css/style.css?v3" />
<link rel="stylesheet" type="text/css" href="css/apps.css?v3" />
<link rel="stylesheet" type="text/css" href="css/custom.css?v2" />
<link rel="stylesheet" type="text/css" href="css/jquery-ui.css?v2" />

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script defer type="text/javascript" src="js/jquery.migrate.js"></script>
<script defer type="text/javascript" src="js/jquery-ui.js"></script>
<script defer type="text/javascript" src="js/jquery.slick.js"></script>
<script defer type="text/javascript" src="js/jquery.fancybox.js"></script>
<script defer type="text/javascript" src="js/jquery.inputmask.js"></script>
<script defer type="text/javascript" src="js/jquery.chosen.js"></script>
<script type="text/javascript" src="js/jquery.tablesorter.js"></script>
<script defer type="text/javascript" src="js/jquery.datetimepicker.js"></script>
<script type="text/javascript" src="js/jquery.sweetalert.js"></script>
<script type="text/javascript" src="js/jquery.tooltipster.js"></script>
<script type="text/javascript" src="js/jquery.money.js"></script>
<script type="text/javascript" src="js/jquery.chart.js"></script>
<script type="text/javascript" src="js/jquery.caret.js"></script>
<script type="text/javascript" src="js/jquery.notify.js"></script>
<script type="text/javascript" src="js/jquery.dad.js"></script>
<script type="text/javascript" src="js/jquery.mobilePhoneNumber.js"></script>
<script type="text/javascript" src="js/jquery.chart-utils.js"></script>
<script type="text/javascript" src="js/jquery.validacao.js"></script>
<script type="text/javascript" src="js/jquery.funcoes.js?v2"></script>
<script type="text/javascript" src="js/jquery.moment.js"></script>
<script type="text/javascript" src="js/jquery.autocomplete.js"></script>
<script defer src="https://code.iconify.design/1/1.0.3/iconify.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="js/jquery.select2.js"></script>
<script type="text/javascript" src="ckeditor/ckeditor.js"></script>
<script type="text/javascript" src="ckfinder/ckfinder.js"></script>
<script type="text/javascript">
	var id_unidade = '<?php echo (isset($usrUnidade) and is_object($usrUnidade))?$usrUnidade->id:0;?>';
	var infodentalCompleto = <?php echo $_infodentalCompleto;?>;
</script>
</head>

<body>