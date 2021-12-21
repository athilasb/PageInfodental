<!doctype html>
<html xmlns="http://www.w3.org/1999/xhtml"
      xmlns:og="http://ogp.me/ns#"
      xmlns:fb="http://www.facebook.com/2008/fbml">

<head>
<meta charset="utf-8">

<title><?php echo ($title)?$title." | Infodental":"Infodental"; ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
<meta name="description" content="<?php echo ($description)?$description:"Infodental"; ?>">
<meta name="author" content="WLIB Soluções Web - www.wlib.com.br">

<?php if($_SERVER["HTTP_HOST"]=="localhost" or $_SERVER["HTTP_HOST"]=="127.0.0.1") { ?>
<base href="//<?php echo $_SERVER["HTTP_HOST"];?>/infodental/projeto/infodental/html/" />
<?php } else { ?>
<base href="//<?php echo $_SERVER["HTTP_HOST"];?>/novo_html/" />
<?php } ?>

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
<script defer type="text/javascript" src="js/jquery.fancybox.js"></script>
<script defer type="text/javascript" src="js/jquery.inputmask.js"></script>
<script type="text/javascript" src="js/jquery.sweetalert.js"></script>
<script type="text/javascript" src="js/jquery.validacao.js"></script>
<script type="text/javascript" src="js/jquery.funcoes.js"></script>
<script defer src="https://code.iconify.design/1/1.0.3/iconify.min.js"></script>

</head>

<body>

<section class="wrapper">

	<section class="nav">
		<div class="nav-header">
			<img src="img/logo-reduzido.svg" alt="" width="30" height="28" />
		</div>
		<div class="nav-buttons">
			<a href=""><i class="iconify" data-icon="fluent:lightbulb-filament-20-regular"></i></a>
			<a href="" class="active"><i class="iconify" data-icon="fluent:calendar-ltr-20-regular"></i></a>
			<a href=""><i class="iconify" data-icon="fluent:share-screen-person-overlay-20-regular"></i></a>
			<a href=""><i class="iconify" data-icon="fluent:data-trending-20-regular"></i></a>
			<a href=""><i class="iconify" data-icon="fluent:web-asset-24-regular"></i></a>
			<a href=""><i class="iconify" data-icon="fluent:settings-20-regular"></i></a>

			<a href="" class="nav-buttons__usuario"><img src="img/ilustra-usuario.jpg" alt="" width="40" height="40" /></a>
			<a href=""><i class="iconify" data-icon="fluent:door-arrow-right-20-regular"></i></a>
		</div>

	</section>

	<header class="header">
		<div class="header__content content">

			<div class="header__inner1">
				<section class="header-title">
					<h1>Tarefas Inteligentes</h1>
				</section>
				<section class="tab">
					<a href="" class="active">Gestão de Tempo</a>
					<a href="">Funil de Vendas</a>
					<a href="">Financeiro</a>
				</section>
			</div>

			<div class="header__inner2">
				<section class="header-date">
					<div class="header-date-buttons">
						<a href="" class="button active">hoje</a>	
						<a href="" class="button">+ 1 dia</a>	
						<a href="" class="button">+ 2 dias</a>	
						<a href="" class="button">+ 3 dias</a>	
					</div>
					<div class="header-date-now">
						<h1>12</h1>
						<h2>dez</h2>
						<h3>terça-feira</h3>
					</div>
				</section>
			</div>

		</div>
	</header>

	<main class="main">
		<div class="main__content content">

			<section class="filter">
				<div class="filter-group">
					<div class="filter-title">	
						<p>Valorize o que mais importa, seu tempo!</p>
					</div>
				</div>
				
				<div class="filter-group">
					<div class="filter-form form">
						<dl>
							<dd class="form-comp form-comp_pos"><input type="text" name="" placeholder="buscar paciente..." /><a href=""><i class="iconify" data-icon="fluent:search-12-filled"></i></a></dd>
						</dl>
					</div>					
				</div>
			</section>

			<form method="post" class="grid grid_2">
				<fieldset>
					<legend>Exemplo</legend>

					<section class="form">
						<dl>
							<dt>Título</dt>
							<dd class="form-comp"><span>BR</span><input type="text" name="" placeholder="" /></dd>
						</dl>
						<dl>
							<dt>Título</dt>
							<dd class="form-comp form-comp_pos"><select name=""><option value="">teste</option></select><span>BR</span></dd>
						</dl>
						<dl>
							<dt>Título</dt>
							<dd class="form-comp form-comp_pos"><input type="text" name="" placeholder="" /><a href=""><i class="iconify" data-icon="fluent:edit-16-regular"></i></a></dd>
						</dl>					
					</section>

				</fieldset>
				<fieldset>
					<legend>Exemplo</legend>
				</fieldset>
			</form>

		</div>		
	</main>

</section>

</body>
</html>