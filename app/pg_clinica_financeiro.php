

<?php
	if (isset($_POST['ajax'])) {
		require_once("lib/conf.php");
		require_once("usuarios/checa.php");
		$rtn  = array();

		header("Content-type: application/json");
		echo json_encode($rtn);
		die();
	}
	include "includes/header.php";
	include "includes/nav.php";
	if($usr->tipo!="admin" and !in_array("financeiro",$_usuariosPermissoes)) {
		$jsc->jAlert("Você não tem permissão para acessar esta área!","erro","document.location.href='dashboard.php'");
		die();
	}
?>
<head>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200;300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" type="text/css" href="css/style.css?v20"/>
</head>
<header class="header">

	<div class="header__content content">
		<div class="header__inner1">
			<section class="header-title">
				<h1>Financeiro</h1>
			</section>
			<?php require_once("includes/menus/menuFinaceiro.php"); ?>
		</div>
	</div>
</header>


<main class="main">
	<div class="main__content content">
	<section class="filter">
		<div class="filter-group">
			<div class="filter-title">
				<h1>Resumo</h1>
			</div>
		</div>
	</section>
		<section class="grid">
			<div class="box box-col">
				<?php require_once("./includes/submenus/SubFinanceiroResumo.php"); ?>
				<div class="box-col__inner1"> 
					<div class="container">
						<div class="grupos-display">
							<div class="elementos">
								<div>R$ 1.000,00</div>
								<span>Promessa de pagamento</span>
							</div>
							<div class="elementos">
								<div>R$ 1.000,00</div>
								<span>Inadimplente</span>
							</div>
							<div class="elementos">
								<div>R$ 1.000,00</div>
								<span>A receber hoje</span>
							</div>
							<div class="elementos">
								<div>R$ 1.000,00</div>
								<span>A pagar hoje</span>
							</div>
							<div class="elementos">
								<div>R$ 1.000,00</div>
								<span>Contas vencidas</span>
							</div>
						</div> 
						
					</div>
					<div class="container">
						<div class="titulo">
							<h2>Contas cadastradas (4)</h2>
							<div class="itens"> <span class="bancos">Banco santader</span> <span class="valores-negativos">- R$ 10.000,00</span></div>
							<div class="itens"> <span class="bancos">Banco santader</span> <span class="valores-positivos">+ R$ 10.000,00</span></div>
							<div class="itens"> <span class="bancos">Banco santader</span> <span class="valores-negativos">- R$ 10.000,00</span></div>
							<div class="itens"> <span class="bancos">Banco santader</span> <span class="valores-positivos">+ R$ 10.000,00</span></div>
						</div>
						
					</div>
			
				</div>
			</div>
		</section>
	</div>
</main>

<?php
include "includes/footer.php";
?>