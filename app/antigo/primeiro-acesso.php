<?php
	require_once("lib/conf.php");
	require_once("lib/classes.php");

	$sql= new Mysql();
	$sql->consult($_p."colaboradores","id","where cpf<>'00000000000' and lixo=0");
	if($sql->rows) {
		header("Location: ./");
	}

?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Infodental - Primeiro Acesso</title>
	<link href="https://fonts.googleapis.com/css2?family=Roboto+Condensed:wght@400;700&family=Roboto:wght@400;700&display=swap" rel="stylesheet">
	<link rel="stylesheet" type="text/css" href="css/style.css?v3" />
	<link rel="stylesheet" type="text/css" href="css/apps.css?v3" />

	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
	<script type="text/javascript" src="js/jquery.sweetalert.js"></script>
</head>
<body>
	<section class="wrapper">

		<?php

		$jsc = new Js();

		if(isset($_POST['acao']) and $_POST['acao']=="wlib") {

			$erro='';

			if(empty($_POST['nome'])) $erro='Preencha o campo nome';
			else if(empty($_POST['cpf'])) $erro='Preencha o campo cpf';
			else if(empty($_POST['senha'])) $erro='Preencha o campo senha';
			else if(empty($_POST['telefone'])) $erro='Preencha o campo telefone';
			else if(empty($_POST['email'])) $erro='Preencha o campo e-mail';
			
			if(empty($erro)) {
			
				$vSQL="nome='".addslashes(utf8_decode(strtoupperWLIB($_POST['nome'])))."',
						cpf='".cpf(utf8_decode($_POST['cpf']))."',
						senha='".sha1(($_POST['senha']))."',
						permitir_acesso=1";


				$sql->add($_p."colaboradores",$vSQL);


				$jsc->jAlert("Cadastro realizado com sucesso","sucesso","document.location.href='./'");
				die();


			} else {
				$jsc->jAlert($erro,"erro","");
			}

		}
		?>
		<header class="header">
			
			<section class="header-logo">
			</section>

			<section class="header-cliente">
				<img src="img/logo.svg" width="32" height="30" alt="Info Dental" class="header-logo__img" />
			</section>

			<section class="header-controles">
				<?php /*<select name="unidade" class="header-controles__select">
					<option value="">STUDIO DENTAL - OESTE</option>
					<option value="">STUDIO DENTAL - GYN SHOP</option>
				</select>*/?>
			</section>

		</header>

		<form method="post" class="form formulario-validacao"  autocomplete="off" enctype="multipart/form-data">
			<input type="hidden" name="acao" value="wlib" />

			<section class="content">
				<section class="grid">
					<div class="box">
						<div class="filter">
							<div class="filter-group">
							</div>
							<div class="filter-group filter-group_right">
								<div class="filter-button">
									<a href="javascript:;" class="azul btn-submit" onclick="$('form').submit();"><i class="iconify" data-icon="bx-bx-check"></i><span>salvar</span></a>
								</div>
							</div>
						</div>
						<fieldset>
							<legend>Primeiro acesso</legend>

							<div class="colunas4">
								<dl class="dl2">
									<dt>Nome</dt>
									<dd><input type="text" name="nome" value="<?php echo isset($_POST['nome'])?$_POST['nome']:'';?>" /></dd>
								</dl>
								<dl>
									<dt>CPF</dt>
									<dd><input type="text" name="cpf" value="<?php echo isset($_POST['cpf'])?$_POST['cpf']:'';?>" /></dd>
								</dl>
								<dl>
									<dt>Senha</dt>
									<dd><input type="password" name="senha" /></dd>
								</dl>
							</div>	

							<div class="colunas4">
								<dl class="">
									<dt>Telefone</dt>
									<dd><input type="text" name="telefone" class="celular" value="<?php echo isset($_POST['telefone'])?$_POST['telefone']:'';?>" /></dd>
								</dl>
								<dl>
									<dt>E-mail</dt>
									<dd><input type="text" name="email" value="<?php echo isset($_POST['email'])?$_POST['email']:'';?>" /></dd>
								</dl>
							</div>

						</fieldset>
					</div>
				</section>
			</section>
		</form>
	</section>


</body>
</html>