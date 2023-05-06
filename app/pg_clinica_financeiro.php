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
	if ($usr->tipo != "admin" and !in_array("financeiro", $_usuariosPermissoes)) {
		$jsc->jAlert("Você não tem permissão para acessar esta área!", "erro", "document.location.href='dashboard.php'");
		die();
	}
	$data_hoje = date("Y-m-d");
	$extras = array();
	$_recebimentos = array();
	$_fluxos = array();
	$contas_cadastradas = array();
	$valor = array(
		"valorTotal" => 0,
		"definirPagamento" => 0,
		"aReceberVencidos" => 0,
		"aReceberHoje" => 0,
		"aPagarHoje" => 0,
		"contasVencidas" => 0,
	);
	//pegando bancos e contas
	$sql->consult("ident_financeiro_bancosecontas","*","WHERE lixo=0");
	while($x=mysqli_fetch_object($sql->mysqry)){
		$_contas_cadastradas[$x->id] = $x;
	}
	//pegando valores promessa de pagamento e vencidos
	$sql->consult("ident_financeiro_fluxo_recebimentos","*","WHERE lixo=0");
	while($x=mysqli_fetch_object($sql->mysqry)){
		$_recebimentos[$x->id] = $x;
	}
	$sql->consult("ident_financeiro_fluxo","*","WHERE lixo=0 AND valor>0");
	while($x=mysqli_fetch_object($sql->mysqry)){
		$_fluxos[$x->id_registro][$x->id] = $x;
	}
	foreach($_recebimentos as $id_recebimento=>$recebimento){
		// verifica se existe um fluxo
		if(isset($_fluxos[$id_recebimento])){
			$fluxos = $_fluxos[$id_recebimento];
			$valor_total = 0;
			foreach($fluxos as $id_fluxo=>$fluxo){
				$valor_total += $fluxo->valor;
				$valor['valorTotal'] += $fluxo->valor;
				if($fluxo->pagamento==0){
					$atraso = (strtotime($fluxo->data_vencimento) - strtotime(date('Y-m-d'))) / (60 * 60 * 24);
					if ($atraso < 0) {
						$valor['aReceberVencidos'] += $fluxo->valor;
						$extras['ids']['aReceberVencidos']['fluxo'][$fluxo->id] = $fluxo->id;
					}
				}
			}
			if($valor_total<$recebimento->valor){
				$faltam = ($recebimento->valor-$valor_total);
				$valor['valorTotal'] += $faltam;
				$atraso = (strtotime($recebimento->data_vencimento) - strtotime(date('Y-m-d'))) / (60 * 60 * 24);
				if ($atraso < 0) {
					$valor['aReceberVencidos']+= $faltam;
					$extras['ids']['aReceberVencidos']['fluxo'][$fluxo->id]= $fluxo->id;
				} else {
					$valor['definirPagamento']+= $faltam;
					$extras['ids']['definirPagamento']['fluxo'][$fluxo->id]= $fluxo->id;
				}
			}
		}else{
			$valor['valorTotal'] += $recebimento->valor;
			$atraso = (strtotime($recebimento->data_vencimento) - strtotime(date('Y-m-d'))) / (60 * 60 * 24);
			if ($atraso < 0) {
				$valor['aReceberVencidos'] += $recebimento->valor;
				$extras['ids']['aReceberVencidos']['recebimento'][$recebimento->id] = $recebimento->id;
			} else {
				$valor['definirPagamento']+=$recebimento->valor;
				$extras['ids']['definirPagamento']['recebimento'][$recebimento->id] = $recebimento->id;
			}

			
		}
	}
	foreach($_contas_cadastradas as $id_conta=>$conta){
		$contas_cadastradas[$id_conta]['id']=$id_conta;
		$contas_cadastradas[$id_conta]['titulo']=$conta->titulo;
		$sql->consult("ident_financeiro_fluxo", "SUM(valor) as total", "WHERE valor>0 AND id_dividido=0 AND lixo=0 AND pagamento=1 AND id_banco='$id_conta'");
		$contas_cadastradas[$id_conta]['valor_positivo']=mysqli_fetch_object($sql->mysqry)->total;
		$sql->consult("ident_financeiro_fluxo", "SUM(valor) as total", "WHERE valor<0 AND id_dividido=0 AND lixo=0 AND pagamento=1 AND id_banco='$id_conta'");
		$contas_cadastradas[$id_conta]['valor_negativo']=mysqli_fetch_object($sql->mysqry)->total;

		$contas_cadastradas[$id_conta]['balanco'] = $contas_cadastradas[$id_conta]['valor_positivo']+$contas_cadastradas[$id_conta]['valor_negativo'];
		$contas_cadastradas[$id_conta]['classe'] = ($contas_cadastradas[$id_conta]['balanco']>0)?'valores-positivos':'valores-negativos';
	}
	//pegando valor a receber hoje
	$sql->consult("ident_financeiro_fluxo", "SUM(valor) as total", "WHERE data_vencimento='$data_hoje' AND valor>0 AND id_dividido=0 AND lixo=0 AND pagamento=0");
	$valor['aReceberHoje'] = mysqli_fetch_object($sql->mysqry)->total;
	//pegando valor a pagar hoje
	$sql->consult("ident_financeiro_fluxo", "SUM(valor) as total", "WHERE data_vencimento='$data_hoje' AND valor<0 AND id_dividido=0 AND lixo=0 AND pagamento=0");
	$valor['aPagarHoje'] =  mysqli_fetch_object($sql->mysqry)->total;
	// pegando as contas vencidas
	$sql->consult("ident_financeiro_fluxo", "SUM(valor) as total", "WHERE data_vencimento<'$data_hoje' AND valor<0 AND id_dividido=0 AND lixo=0 AND pagamento=0");
	$valor['contasVencidas'] = mysqli_fetch_object($sql->mysqry)->total;

//	debug($contas_cadastradas,true)
?>

<head>
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200;300;400;500;600;700;800&display=swap" rel="stylesheet">
	<link rel="stylesheet" type="text/css" href="css/style.css?v20" />
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
								<div>R$ <?= number_format($valor['definirPagamento'], 2, ',', '.') ?></div>
								<span>Definir pagamento</span>
							</div>
							<div class="elementos">
								<div>R$ <?= number_format($valor['aReceberVencidos'], 2, ',', '.') ?></div>
								<span>Inadimplente</span>
							</div>
							<div class="elementos">
								<div>R$ <?= number_format($valor['aReceberHoje'], 2, ',', '.') ?></div>
								<span>A receber hoje</span>
							</div>
							<div class="elementos">
								<div>R$ <?= number_format($valor['aPagarHoje'], 2, ',', '.') ?></div>
								<span>A pagar hoje</span>
							</div>
							<div class="elementos">
								<div>R$ <?= number_format($valor['contasVencidas'], 2, ',', '.') ?></div>
								<span>Contas vencidas</span>
							</div>
						</div>

					</div>
					<div class="container">
						<div class="titulo">
							<h2>Contas cadastradas (<?=count($contas_cadastradas)?>)</h2>
							<?php 
								foreach($contas_cadastradas as $id=>$conta){
							?>
								<div class="itens"> <span class="bancos"><?=utf8_encode($conta['titulo']);?></span> <span class="<?=$conta['classe']?>">R$ <?=number_format($conta['balanco'], 2, ',', '.')?></span></div>

							<?php 
								}
							?>
							<!-- <div class="itens"> <span class="bancos">Banco santader</span> <span class="valores-positivos">+ R$ 10.000,00</span></div>
							<div class="itens"> <span class="bancos">Banco santader</span> <span class="valores-negativos">- R$ 10.000,00</span></div>
							<div class="itens"> <span class="bancos">Banco santader</span> <span class="valores-positivos">+ R$ 10.000,00</span></div> -->
						</div>

					</div>

				</div>
			</div>
		</section>
	</div>
</main>
<script>
	const extras = <?=json_encode($extras);?>;
	console.log(extras)
</script>
<?php
include "includes/footer.php";
?>