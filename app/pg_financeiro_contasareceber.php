<?php
require_once("lib/conf.php");
require_once("usuarios/checa.php");

// AQUI RECEBO OS AJAXS
if (isset($_POST['ajax'])) {
	$rtn = array();
	if ($_POST['ajax'] == 'updateDataFiltro') {
	}
	header("Content-type: application/json");
	echo json_encode($rtn);
	die();
}

include "includes/header.php";
include "includes/nav.php";

// formas de pagamento
$_formasDePagamento = array();
$optionFormasDePagamento = '';
$sql->consult($_p . "parametros_formasdepagamento", "*", "where lixo=0 order by titulo asc");
while ($x = mysqli_fetch_object($sql->mysqry)) {
	$_formasDePagamento[$x->id] = (object)array("id" => $x->id, "lixo" => $x->lixo, "titulo" => utf8_encode($x->titulo), "tipo" => $x->tipo, "politica_de_pagamento" => $x->politica_de_pagamento);
}
$data_inicial_filtro = isset($_GET['data_inicio']) ? $_GET['data_inicio'] : date('Y-m-d');
$data_final_filtro =  isset($_GET['data_final']) ? $_GET['data_final'] : date('Y-m-d', strtotime("+7 days"));
$dias_filtro = (strtotime($data_final_filtro) - strtotime($data_inicial_filtro)) / (60 * 60 * 24);


function getValores($data_inicial, $data_final)
{
	global $sql;
	global $_p;
	// buscando informações dos pagamentos
	$_tratamentos = array();
	$_origens = array();
	$_recebimentos = array();
	$_pagantes =  array();
	$idRegistros = array();
	$idTratamentos = array();
	$idPagantes = array();
	$valor = array(
		'aReceber' => 0,
		'valorRecebido' => 0,
		'valoresVencido' => 0,
		'valorTotal' => 0,
		'valorJuros' => 0,
		'valorMulta' => 0,
		"definirPagamento" => 0
	);
	// pegando as oriugens
	$sql->consult($_p . "financeiro_fluxo_origens", "*", "WHERE 1");
	if ($sql->rows) {
		while ($x = mysqli_fetch_object($sql->mysqry)) {
			$origens[$x->id] = $x->tabela;
		}
	}

	// aqui eu busco as baixas que foram dadas
	$_fluxos = array();
	$sql->consult($_p . "financeiro_fluxo", "*", "WHERE (data_vencimento>='$data_inicial' AND data_vencimento<='$data_final') and lixo=0 AND desconto=0  AND valor>0 order by data_vencimento asc");
	if ($sql->rows) {
		while ($x = mysqli_fetch_object($sql->mysqry)) {
			$_fluxos[$x->id_registro][$x->id] = $x;
			//$_fluxos[$x->id] = $x;
			#$origem = $origens[$x->id_origem];
			$idRegistros[$x->id_registro] = $x->id_registro;
		}
	}
	//pegando os recebimentos totais
	$sql->consult($_p . "financeiro_fluxo_recebimentos", "*", "WHERE (data_vencimento>='$data_inicial' AND data_vencimento<='$data_final') and lixo=0 order by data_vencimento asc");
	if ($sql->rows) {
		while ($x = mysqli_fetch_object($sql->mysqry)) {
			$_recebimentos[$x->id] = $x;
			$idsPagamentos[$x->id] = $x->id;
			$idTratamentos[$x->id_tratamento] = $x->id_tratamento;
			$idPagantes[$x->id_pagante] = $x->id_pagante;
		}
	}
	// pegandos os IDS pagantes e tratamentos
	if (count($idRegistros) > 0) {
		$sql->consult($_p . "financeiro_fluxo_recebimentos", "*", " WHERE id IN (" . IMPLODE(',', $idRegistros) . ") AND lixo=0");
		if ($sql->rows) {
			while ($x = mysqli_fetch_object($sql->mysqry)) {
				$_recebimentos[$x->id] = $x;
				$idTratamentos[$x->id_tratamento] = $x->id_tratamento;
				$idPagantes[$x->id_pagante] = $x->id_pagante;
			}
		}
	}
	// pegando os tratamentos 
	if (count($idTratamentos) > 0) {
		$sql->consult($_p . "pacientes_tratamentos ", "*", " WHERE id IN (" . IMPLODE(',', $idTratamentos) . ")");
		if ($sql->rows) {
			while ($x = mysqli_fetch_object($sql->mysqry)) {
				$_tratamentos[$x->id] = $x;
			}
		}
	}
	// pegando os pagantes
	if (count($idPagantes) > 0) {
		$sql->consult($_p . "pacientes", "*", " WHERE id IN (" . IMPLODE(',', $idPagantes) . ")");
		if ($sql->rows) {
			while ($x = mysqli_fetch_object($sql->mysqry)) {
				$_pagantes[$x->id] = $x;
			}
		}
	}

	$dados = array();
	$extras = array();

	foreach ($_recebimentos as $id_recebimento => $recebimento) {
		$titulo = (isset($_tratamentos[$recebimento->id_tratamento]->titulo)) ? utf8_decode($_tratamentos[$recebimento->id_tratamento]->titulo) : "Pagamento Avulso";
		$pagante  = (isset($_pagantes[$recebimento->id_pagante]->nome)) ? utf8_decode($_pagantes[$recebimento->id_pagante]->nome) : '-';
		// verifica se existe um fluxo
		$valor['valorTotal'] += $recebimento->valor;
		if (isset($_fluxos[$id_recebimento])) {
			$fluxos = $_fluxos[$id_recebimento];
			$valor_total = 0;
			foreach ($fluxos as $id_fluxo => $fluxo) {
				isset($extras['formas_pagamentos'][$fluxo->id_formapagamento]) ? $extras['formas_pagamentos'][$fluxo->id_formapagamento] += $fluxo->valor : $extras['formas_pagamentos'][$fluxo->id_formapagamento] = intVal($fluxo->valor);
				$valor_total += $fluxo->valor;
				$dados[$fluxo->id]['id_baixa'] = $fluxo->id;
				$dados[$fluxo->id]['data_vencimento'] = $fluxo->data_vencimento;
				$dados[$fluxo->id]['id_registro'] = $fluxo->id_registro;
				$dados[$fluxo->id]['pagamento'] = $fluxo->pagamento;
				$dados[$fluxo->id]['data_efetivado'] = $fluxo->data_efetivado;
				$dados[$fluxo->id]['tipo'] = 'fluxo';
				$dados[$fluxo->id]['valor'] = $fluxo->valor;
				$dados[$fluxo->id]['valor_multa'] = $fluxo->valor_multa;
				$dados[$fluxo->id]['valor_taxa'] = $fluxo->valor_taxa;
				$dados[$fluxo->id]['valor_desconto'] = $fluxo->valor_desconto;
				$dados[$fluxo->id]['valor_juros'] = $fluxo->valor_juros;
				$dados[$fluxo->id]['desconto'] = $fluxo->desconto;
				$dados[$fluxo->id]['valorTotalPagamento'] = $recebimento->valor ?? 0;
				$dados[$fluxo->id]['titulo'] = $titulo;
				$dados[$fluxo->id]['nome_pagante'] = $pagante;
				$dados[$fluxo->id]['status'] = '';

				if ($fluxo->pagamento == 0) {
					$atraso = (strtotime($fluxo->data_vencimento) - strtotime(date('Y-m-d'))) / (60 * 60 * 24);
					if ($atraso < 0) {
						$valor['valoresVencido'] += $fluxo->valor;
						$dados[$fluxo->id]['status'] = 'Vencido';
						$extras['ids']['valoresVencido']['fluxo'][$fluxo->id] = $fluxo->id;
					} else {
						$valor['aReceber'] += $fluxo->valor;
						$dados[$fluxo->id]['status'] = 'a Receber';
						$extras['ids']['aReceber']['fluxo'][$fluxo->id] = $fluxo->id;
					}
				} else {
					$valor['valorRecebido'] += $fluxo->valor;
					$dados[$fluxo->id]['status'] = 'Pago';
					$extras['ids']['valorRecebido']['fluxo'][$fluxo->id] = $fluxo->id;
				}
			}
			if ($valor_total < $recebimento->valor) {
				$faltam = ($recebimento->valor - $valor_total);
				$atraso = (strtotime($recebimento->data_vencimento) - strtotime(date('Y-m-d'))) / (60 * 60 * 24);

				$dados[$recebimento->id]['id_baixa'] = $fluxo->id;
				$dados[$recebimento->id]['data_vencimento'] = $fluxo->data_vencimento;
				$dados[$recebimento->id]['id_registro'] = $fluxo->id_registro;
				$dados[$recebimento->id]['pagamento'] = $fluxo->pagamento;
				$dados[$recebimento->id]['data_efetivado'] = $fluxo->data_efetivado;
				$dados[$recebimento->id]['tipo'] = 'fluxo';
				$dados[$recebimento->id]['valor'] = $faltam;
				$dados[$recebimento->id]['valor_multa'] = $fluxo->valor_multa;
				$dados[$recebimento->id]['valor_taxa'] = $fluxo->valor_taxa;
				$dados[$recebimento->id]['valor_desconto'] = $fluxo->valor_desconto;
				$dados[$recebimento->id]['valor_juros'] = $fluxo->valor_juros;
				$dados[$recebimento->id]['desconto'] = $fluxo->desconto;
				$dados[$recebimento->id]['valorTotalPagamento'] = $recebimento->valor ?? 0;
				$dados[$recebimento->id]['titulo'] = $titulo;
				$dados[$recebimento->id]['nome_pagante'] = $pagante;
				$dados[$recebimento->id]['status'] = '';

				if ($atraso < 0) {
					$valor['valoresVencido'] += $faltam;
					$extras['ids']['valoresVencido']['recebimento'][$recebimento->id] = $recebimento->id;
					$dados[$recebimento->id]['status'] = 'Vencido';
				} else {
					$valor['definirPagamento'] += $faltam;
					$extras['ids']['definirPagamento']['recebimento'][$recebimento->id] = $recebimento->id;
					$dados[$recebimento->id]['status'] = 'DEFINIR PAGAMENTO';
				}
			}
		} else {
			$atraso = (strtotime($recebimento->data_vencimento) - strtotime(date('Y-m-d'))) / (60 * 60 * 24);
			if ($atraso < 0) {
				$valor['valoresVencido'] += $recebimento->valor;
				$extras['ids']['valoresVencido']['recebimento'][$recebimento->id] = $recebimento->id;
				$valor['valoresVencido'] += $recebimento->valor;
				$dados[$id_recebimento]['id_baixa'] = $recebimento->id;
				$dados[$id_recebimento]['data_vencimento'] = $recebimento->data_vencimento;
				$dados[$id_recebimento]['id_registro'] = $recebimento->id;
				$dados[$id_recebimento]['pagamento'] = 0;
				$dados[$id_recebimento]['data_efetivado'] = null;
				$dados[$id_recebimento]['tipo'] = 'recebimento';
				$dados[$id_recebimento]['valor'] = $recebimento->valor;
				$dados[$id_recebimento]['valor_multa'] = $recebimento->valor_multa;
				$dados[$id_recebimento]['valor_taxa'] = $recebimento->valor_taxa;
				$dados[$id_recebimento]['valor_desconto'] = $recebimento->valor_desconto;
				$dados[$id_recebimento]['valor_juros'] = 0;
				$dados[$id_recebimento]['desconto'] = 0;
				$dados[$id_recebimento]['valorTotalPagamento'] = $recebimento->valor;
				$dados[$id_recebimento]['titulo'] = $titulo;
				$dados[$id_recebimento]['nome_pagante'] = $pagante;
				$dados[$id_recebimento]['status'] = 'Vencido';
			} else {
				$valor['definirPagamento'] += $recebimento->valor;
				$extras['ids']['definirPagamento']['recebimento'][$recebimento->id] = $recebimento->id;
				$dados[$id_recebimento]['id_baixa'] = $recebimento->id;
				$dados[$id_recebimento]['data_vencimento'] = $recebimento->data_vencimento;
				$dados[$id_recebimento]['id_registro'] = $recebimento->id;
				$dados[$id_recebimento]['pagamento'] = 0;
				$dados[$id_recebimento]['data_efetivado'] = null;
				$dados[$id_recebimento]['tipo'] = 'recebimento';
				$dados[$id_recebimento]['valor'] = $recebimento->valor;
				$dados[$id_recebimento]['valor_multa'] = $recebimento->valor_multa;
				$dados[$id_recebimento]['valor_taxa'] = $recebimento->valor_taxa;
				$dados[$id_recebimento]['valor_desconto'] = $recebimento->valor_desconto;
				$dados[$id_recebimento]['valor_juros'] = 0;
				$dados[$id_recebimento]['desconto'] = 0;
				$dados[$id_recebimento]['valorTotalPagamento'] = $recebimento->valor;
				$dados[$id_recebimento]['titulo'] = $titulo;
				$dados[$id_recebimento]['nome_pagante'] = $pagante;
				$dados[$id_recebimento]['status'] = 'DEFINIR PAGAMENTO';
			}
		}
	}
	// debug($dados,true);
	// foreach ($_fluxos as $fluxo) {
	//  	$titulo = (isset($_recebimentos[$fluxo->id_registro]) && isset($_recebimentos[$fluxo->id_registro]->id_tratamento) && isset($_tratamentos[$_recebimentos[$fluxo->id_registro]->id_tratamento])) ? utf8_encode($_tratamentos[$_recebimentos[$fluxo->id_registro]->id_tratamento]->titulo) : "";
	//  	$pagante  = (isset($_recebimentos[$fluxo->id_registro]) && isset($_recebimentos[$fluxo->id_registro]->id_pagante) && isset($_pagantes[$_recebimentos[$fluxo->id_registro]->id_pagante])) ? utf8_encode($_pagantes[$_recebimentos[$fluxo->id_registro]->id_pagante]->nome) : '';
	//  	$dados[$fluxo->id]['id_baixa'] = $fluxo->id;
	//  	$dados[$fluxo->id]['data_vencimento'] = $fluxo->data_vencimento;
	//  	$dados[$fluxo->id]['id_registro'] = $fluxo->id_registro;
	//  	$dados[$fluxo->id]['pagamento'] = $fluxo->pagamento;
	//  	$dados[$fluxo->id]['data_efetivado'] = $fluxo->data_efetivado;
	//  	$dados[$fluxo->id]['tipo'] = 'fluxo';
	//  	$dados[$fluxo->id]['valor'] = $fluxo->valor;
	//  	$dados[$fluxo->id]['valor_multa'] = $fluxo->valor_multa;
	//  	$dados[$fluxo->id]['valor_taxa'] = $fluxo->valor_taxa;
	//  	$dados[$fluxo->id]['valor_desconto'] = $fluxo->valor_desconto;
	//  	$dados[$fluxo->id]['valor_juros'] = $fluxo->valor_juros;
	//  	$dados[$fluxo->id]['desconto'] = $fluxo->desconto;
	//  	$dados[$fluxo->id]['valorTotalPagamento'] = $_recebimentos[$fluxo->id_registro]->valor ?? 0;
	//  	$dados[$fluxo->id]['titulo'] = $titulo;
	//  	$dados[$fluxo->id]['nome_pagante'] = $pagante;
	//  	$dados[$fluxo->id]['status'] = '';
	//  	$valor['valorTotal'] += $fluxo->valor;
	//  	if ($fluxo->pagamento == 1) {
	//  		$valor['valorRecebido'] += $fluxo->valor;
	//  		$dados[$fluxo->id]['status'] = 'Pago';
	//  		$extras['ids']['valorRecebido']['fluxo'][$fluxo->id] = $fluxo->id;
	//  	} else {
	//  		$atraso = (strtotime($fluxo->data_vencimento) - strtotime(date('Y-m-d'))) / (60 * 60 * 24);
	//  		if ($atraso < 0) {
	//  			$valor['valoresVencido'] += $fluxo->valor;
	//  			$dados[$fluxo->id]['status'] = 'Vencido';
	//  			$extras['ids']['valoresVencido']['fluxo'][$fluxo->id] = $fluxo->id;
	//  		} else {
	//  			$valor['aReceber'] += $fluxo->valor;
	//  			$dados[$fluxo->id]['status'] = 'a Receber';
	//  			$extras['ids']['aReceber']['fluxo'][$fluxo->id] = $fluxo->id;
	//  		}
	//  	}

	// }

	// foreach ($_recebimentos as $id => $pag) {
	// 	$sql->consult($_p . "financeiro_fluxo", "SUM(valor)", "WHERE id_registro='$id' AND id_origem=1 AND lixo=0");
	//  	$key = "SUM(valor)";
	//  	$soma = mysqli_fetch_object($sql->mysqry);
	//  	if ($soma->$key >= $pag->valor) {
	//  		//echo "SOMA È MAIOR OU IGUAL QUE O VALOR ORIGINAL<br>";
	//  	} else {
	//  		$faltam = ($pag->valor - $soma->$key);
	//  		//echo "AINDA FALTA UM VALOR DE: $faltam<br>";
	//  		$titulo = (isset($_tratamentos[$pag->id_tratamento]->titulo)) ? utf8_decode($_tratamentos[$pag->id_tratamento]->titulo) : "-";
	//  		$pagante  = (isset($_pagantes[$pag->id_pagante]->nome)) ? utf8_decode($_pagantes[$pag->id_pagante]->nome) : '-';
	//  		$atraso = (strtotime($pag->data_vencimento) - strtotime(date('Y-m-d'))) / (60 * 60 * 24);
	//  		if ($atraso < 0) {
	//  			$valor['valoresVencido'] += $faltam;
	//  			$dados[$id]['id_baixa'] = $pag->id;
	//  			$dados[$id]['data_vencimento'] = $pag->data_vencimento;
	//  			$dados[$id]['id_registro'] = $pag->id;
	//  			$dados[$id]['pagamento'] = 0;
	//  			$dados[$id]['data_efetivado'] = null;
	//  			$dados[$id]['tipo'] = 'recebimento';
	//  			$dados[$id]['valor'] = $faltam;
	//  			$dados[$id]['valor_multa'] = $pag->valor_multa;
	//  			$dados[$id]['valor_taxa'] = $pag->valor_taxa;
	//  			$dados[$id]['valor_desconto'] = $pag->valor_desconto;
	//  			$dados[$id]['valor_juros'] = 0;
	//  			$dados[$id]['desconto'] = 0;
	//  			$dados[$id]['valorTotalPagamento'] = $_recebimentos[$pag->id]->valor;
	//  			$dados[$id]['titulo'] = $titulo;
	//  			$dados[$id]['nome_pagante'] = $pagante;
	//  			$dados[$id]['status'] = 'Vencido';
	//  			$valor['valorTotal'] += $pag->valor;
	//  			$extras['ids']['valoresVencido']['recebimento'][$pag->id] = $pag->id;
	//  		} else {
	//  			$valor['definirPagamento'] += $faltam;
	//  			$dados[$id]['id_baixa'] = $pag->id;
	//  			$dados[$id]['data_vencimento'] = $pag->data_vencimento;
	//  			$dados[$id]['id_registro'] = $pag->id;
	// 			$dados[$id]['pagamento'] = 0;
	//  			$dados[$id]['data_efetivado'] = null;
	//  			$dados[$id]['tipo'] = 'recebimento';
	// 			$dados[$id]['valor'] = $faltam;
	//  			$dados[$id]['valor_multa'] = $pag->valor_multa;
	//  			$dados[$id]['valor_taxa'] = $pag->valor_taxa;
	//  			$dados[$id]['valor_desconto'] = $pag->valor_desconto;
	//  			$dados[$id]['valor_juros'] = 0;
	//  			$dados[$id]['desconto'] = 0;
	//  			$dados[$id]['valorTotalPagamento'] = $_recebimentos[$pag->id]->valor;
	//  			$dados[$id]['titulo'] = $titulo;
	//  			$dados[$id]['nome_pagante'] = $pagante;
	//  			$dados[$id]['status'] = 'DEFINIR PAGAMENTO';
	//  			$valor['valorTotal'] += $pag->valor;
	//  			$extras['ids']['definirPagamento']['recebimento'][$pag->id] = $pag->id;
	//  		}
	//  	}
	// }
	$dados = json_decode(json_encode($dados));
	return [$dados, $_recebimentos, $valor, $extras];
}

[$dados, $_recebimentos, $valor, $extras] = getValores($data_inicial_filtro, $data_final_filtro);
?>

<head>
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200;300;400;500;600;700;800&display=swap" rel="stylesheet">
	<link rel="stylesheet" type="text/css" href="css/style.css?v99" />
	<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

</head>

<header class="header">
	<div class="header__content content">
		<div class="header__inner1">
			<section class="header-title">
				<h1>Financeiro</h1>
			</section>
			<?php require_once("includes/menus/menuFinaceiro.php"); ?>
		</div>
		<div class="header__inner2">
			<section class="header-date">
				<div class="header-date-now">
					<h1 id="dia_i"><?= date('d', strtotime($data_inicial_filtro)) ?></h1>
					<h2 id="mes_i"><?= date('M', strtotime($data_inicial_filtro)) ?></h2>
					até
					<h1 id="dia_f"><?= date('d', strtotime($data_final_filtro)) ?></h1>
					<h2 id="mes_f"><?= date('M', strtotime($data_final_filtro)) ?></h2>
				</div>
			</section>
		</div>
	</div>
</header>
<main class="main" id="body" data-pagina="contasareceber">
	<div class="main__content content">
		<section class="filter">
			<div class="filter-group">
				<dl>
					<dd>
						<button id='pagamento_avulso-receber' class="button button_main js-btn-abrir-aside" data-tipoAvulso="geral"><i class="iconify" data-icon="fluent:add-circle-24-regular"></i> <span>Pagamento Avulso</span></button>
					</dd>
				</dl>
			</div>
			<div class="filter-group">
				<a href="javascript:;" class="button js-calendario">
					<span class="iconify" data-icon="bi:calendar-week"></span>
				</a>
				<div class="button-group">
					<a href="/pg_financeiro_contasareceber.php?data_inicio=<?= date('Y-m-d') ?>&data_final=<?= date('Y-m-d', strtotime('+ 7 days')) ?>" class="button btn-prefiltro <?= ($dias_filtro == 7) ? 'active' : '' ?>" data-dias='7'>7 dias</a>
					<a href="/pg_financeiro_contasareceber.php?data_inicio=<?= date('Y-m-d') ?>&data_final=<?= date('Y-m-d', strtotime('+ 30 days')) ?>" class="button btn-prefiltro <?= ($dias_filtro == 30) ? 'active' : '' ?>" data-dias='30'>30 dias</a>
					<a href="/pg_financeiro_contasareceber.php?data_inicio=<?= date('Y-m-d') ?>&data_final=<?= date('Y-m-d', strtotime('+ 60 days')) ?>" class="button btn-prefiltro <?= ($dias_filtro == 60) ? 'active' : '' ?>" data-dias='60'>60 dias</a>
					<a href="/pg_financeiro_contasareceber.php?data_inicio=<?= date('Y-m-d') ?>&data_final=<?= date('Y-m-d', strtotime('+ 90 days')) ?>" class="button btn-prefiltro <?= ($dias_filtro == 90) ? 'active' : '' ?>" data-dias='90'>90 dias</a>
					<a href="/pg_financeiro_contasareceber.php?data_inicio=<?= date('Y-m-d') ?>&data_final=<?= date('Y-m-d', strtotime('+ 365 days')) ?>" class="button btn-prefiltro <?= ($dias_filtro == 365) ? 'active' : '' ?>" data-dias='365'>ano</a>
				</div>
			</div>
		</section>
		<section class="grid">
			<div class="box">
				<div class="" style="display:flex; flex-wrap:wrap; justify-content:space-between;">
					<section class="filter" style="margin-bottom:0;">
						<div class="filter-group">
							<div class="filter-title">
								<p>Total</p>
								<h2><strong id='valor-valorTotal'>R$ <?= number_format($valor['valorTotal'], 2, ',', '.') ?></strong></h2>
							</div>
							<div class="filter-title">
								<p>A receber</p>
								<h2 style="color:var(--cinza4)" id='valor-aReceber'>R$ <?= number_format($valor['aReceber'], 2, ',', '.') ?></h2>
							</div>
							<div class="filter-title">
								<p>A definir pagamento</p>
								<h2 style="color:var(--laranja)" id='valor-definirPagamento'>R$ <?= number_format($valor['definirPagamento'], 2, ',', '.') ?></h2>
							</div>
							<div class="filter-title">
								<p>Recebido</p>
								<h2 style="color:var(--verde)" id='valor-valorRecebido'>R$ <?= number_format($valor['valorRecebido'], 2, ',', '.') ?></h2>
							</div>
							<div class="filter-title">
								<p>Vencido</p>
								<h2 style="color:var(--vermelho)" id='valor-valoresVencido'>R$ <?= number_format($valor['valoresVencido'], 2, ',', '.') ?></h2>
							</div>
						</div>
						<div class="filter-group">
							<!-- <a href="javascript:;" class="button"><i class="iconify" data-icon="fluent:chevron-down-24-regular"></i> <span>Gráficos</span></a> -->
						</div>
					</section>
					<section>
						<a href="javascript:;" class="link-graficos">
							<span class="veja-graficos">Veja os gráficos</span>
							<span class="iconify" id="arrow-up" style="background: #FFFFFF;border: 1px solid #CDCDCD; border-radius: 7px; display:none; width: 35px; height: 35px;" data-icon="material-symbols:arrow-drop-up-rounded"></span>
							<span class="iconify" id="arrow-down" style="background: #FFFFFF;border: 1px solid #CDCDCD; border-radius: 7px; width: 35px; height: 35px;" data-icon="material-symbols:arrow-drop-down-rounded"></span>
						</a>

					</section>
				</div>
				<div class=" accordion display-flex-center" style="display:none">
					<div class="botoes-graficos">
						<button id="status-pagamento-btn" class="grafico-btn active">Status do pagamento</button>
						<button id="formas-pagamento-btn" class="grafico-btn">Formas de pagamento</button>
						<!-- <button id="conciliacoes-btn" class="grafico-btn">Conciliações dos pagamentos</button> -->
						<!-- <button id="emissao-notas-btn" class="grafico-btn">Emissão de notas e recibos</button> -->
					</div>
					<div class="graficos">
						<div id="status-pagamento" class="grafico-content" style="display:block">
							<div class="graficos-view display-flex-center">
								<div id="chart1" style="height: 305px;"></div>
								<div id="chart-info1" class="margin-left-25">
									<div class="label-info-1 info-item">
										<span class="color"></span>
										<span class="label"><b>Pago:</b></span>
										<span class="value">R$ <?= number_format($valor['valorRecebido'], 2, ',', '.') ?></span>
									</div>
									<div class="label-info-2 info-item">
										<span class="color"></span>
										<span class="label"><b>Vencidos:</b></span>
										<span class="value">R$ <?= number_format($valor['valoresVencido'], 2, ',', '.') ?></span>
									</div>
									<div class="label-info-3 info-item">
										<span class="color"></span>
										<span class="label"><b>Definir pagamento:</b></span>
										<span class="value">R$ <?= number_format($valor['definirPagamento'], 2, ',', '.') ?></span>
									</div>
									<div class="label-info-3 info-item">
										<span class="color"></span>
										<span class="label"><b>A receber</b></span>
										<span class="value">R$ <?= number_format($valor['aReceber'], 2, ',', '.') ?></span>
									</div>

								</div>
							</div>
						</div>
						<div id="formas-pagamento" class="grafico-content">
							<div class="graficos-view display-flex-center">
								<div id="chart2" style="height: 305px;"></div>
								<div id="chart-info2" class="margin-left-25">
									<?php 
										foreach($extras['formas_pagamentos'] as $id=>$forma){
									?>
										<div class="info-item">
											<span class="color"></span>
											<span class="label"><b><?=$_formasDePagamento[$id]->titulo?></b></span>
											<span class="value">R$ <?=number_format($forma,2,',','.');?></span>
										</div>
									<?php 
										}
									?>
									<!-- <div class="label-info-2 info-item">
										<span class="color"></span>
										<span class="label"><b>Boleto bancário:</b></span>
										<span class="value">R$ 1.000,00</span>
									</div>
									<div class="label-info-3 info-item">
										<span class="color"></span>
										<span class="label"><b>Dinheiro:</b></span>
										<span class="value">R$ 2.000,00</span>
									</div>
									<div class="label-info-4 info-item">
										<span class="color"></span>
										<span class="label"><b>Pix:</b></span>
										<span class="value">R$ 900,00</span>
									</div> -->
								</div>
							</div>
						</div>
						<div id="conciliacoes" class="grafico-content">
							<div id="chart3" style="height: 305px;"></div>
						</div>
						<div id="emissao-notas" class="grafico-content">
							<div class="graficos-view display-flex-center">
								<div id="chart4" style="height: 305px;"></div>
								<div id="chart-info4" class="margin-left-25">
									<div class="label-info-1 info-item">
										<span class="color"></span>
										<span class="label"><b>Notas emitidas:</b></span>
										<span class="value">R$ 5.000,00</span>
									</div>
									<div class="label-info-2 info-item">
										<span class="color"></span>
										<span class="label"><b>Recibos emitidos:</b></span>
										<span class="value">R$ 1.000,00</span>
									</div>
									<div class="label-info-3 info-item">
										<span class="color"></span>
										<span class="label"><b>Não emitidos:</b></span>
										<span class="value">R$ 2.000,00</span>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="box">
				<div class="filter">
					<a href="javascript:;" class="button"><i class="iconify" data-icon="fluent:link-square-24-filled"></i><span>Unir pagamentos</span></a>
				</div>
				
				<div class="list2">
					<table class="tablesorter" id="list-payments">
						<thead>
							<tr>
								<th>Vencimento</th>
								<th>Status</th>
								<th>Descrição</th>
								<th>Valor</th>
								<th>Valor Detalhes</th>
								<th>Detalhes</th>
								<th style="width:120px;">Receber</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($dados as $x) { ?>
								<tr>
									<td><?= date('d/m/Y', strtotime($x->data_vencimento)) ?></td>
									<td><?= $x->status ?></td>
									<td><strong><?= $x->nome_pagante ?></strong><br /><?= $x->titulo ?></td>
									<td><strong>R$ <?= number_format($x->valor, 2, ',', '.') ?></strong></td>
									<td style="font-size:0.813em; line-height:1.2;">Multa: R$ <?= number_format($x->valor_multa, 2, ',', '.') ?><br />Juros: R$ <?= number_format($x->valor_juros, 2, ',', '.') ?></td>
									<td style="font-size:1.75rem;">
										<span style="color:var(--cinza3)" title="Contrato assinado" class="tooltip"><i class="iconify" data-icon="fluent:signature-20-regular"></i></span>
										<span style="color:var(--cinza3)" title="Nota fiscal emitida" class="tooltip"><i class="iconify" data-icon="heroicons:receipt-percent"></i></span>
										<span style="color:var(--cinza3)" title="Não conciliado" class="tooltip"><i class="iconify" data-icon="fluent:checkbox-checked-sync-20-regular"></i></span>
										<span style="color:var(--cinza3)" title="Regua não executada" class="tooltip"><i class="iconify" data-icon="fluent:task-list-ltr-20-filled"></i></span>
									</td>
									<td><a href="javascript:;" class="button js-pagamento-item" style="width:120px" data-idRegistro='<?= $x->id_registro ?>'><i class="iconify" data-icon="ph:currency-circle-dollar"></i> <span>Receber</span></a></td>
								</tr>
							<?php } ?>
						</tbody>
					</table>
				</div>
			</div>
		</section>
	</div>
</main>
<script>
	const _lista = <?= json_encode($dados) ?>;
	const _valor = <?= json_encode($valor) ?>;
	const _formasDePagamento = <?= json_encode($_formasDePagamento); ?>;
	const _extras = <?= json_encode($extras); ?>;
	populaGraficos()
	$('.js-pagamento-item').on('click', (function() {
		let idRegistro = $(this).attr('data-idRegistro')
		abrirAside('contasAreceber', idRegistro)
	}));

	$('.js-calendario').daterangepicker({
		"autoApply": true,
		"locale": {
			"format": "DD/MM/YYYY",
			"separator": " - ",
			"fromLabel": "De",
			"toLabel": "Até",
			"customRangeLabel": "Customizar",
			"weekLabel": "W",
			"daysOfWeek": [
				"Dom",
				"Seg",
				"Ter",
				"Qua",
				"Qui",
				"Sex",
				"Sáb"
			],
			"monthNames": [
				"Janeiro",
				"Fevereiro",
				"Março",
				"Abril",
				"Maio",
				"Junho",
				"Julho",
				"Agosto",
				"Setembro",
				"Outubro",
				"Novembro",
				"Dezembro"
			],
			"firstDay": 1
		},
	});


	$('.js-calendario').on('apply.daterangepicker', function(ev, picker) {
		let dtFim = picker.endDate.format('YYYY-MM-DD');
		let dtInicio = picker.startDate.format('YYYY-MM-DD');
		document.location.href = `<?php echo "$_page?pg_financeiro_contasareceber?"; ?>&data_inicio=${dtInicio}&data_final=${dtFim}`
	});

	$(document).ready(function() {
		$('#arrow-up').hide(); // oculta o ícone de seta para cima
		$('.link-graficos').click(function() {
			$(".accordion").slideToggle();
			$('#arrow-up').toggle();
			$('#arrow-down').toggle();

		});
	});

	$(document).ready(function() {
		$('.grafico-btn').click(function() {
			// Adiciona a classe ativa apenas para o botão clicado
			$(this).addClass('active');
			// Remove a classe ativa de todos os botões, exceto o botão atual
			$('.grafico-btn').not(this).removeClass('active');
			// Oculta todo o conteúdo do gráfico
			$('.grafico-content').hide();
			// Mostra apenas o conteúdo do gráfico correspondente
			var id = $(this).attr('id').replace('-btn', '');
			$('#' + id).show();
			// Altera a cor de fundo e a cor do texto do botão clicado
		});
	});



	function populaGraficos() {
		// let aReceber = number_format(_valor.aReceber, 2, ',', '.');
		// let definirPagamento = number_format(_valor.definirPagamento, 2, ',', '.');
		// let valorRecebido = number_format(_valor.valorRecebido, 2, ',', '.');
		// let valorTotal = number_format(_valor.valorTotal, 2, ',', '.');
		// let valoresVencido = number_format(_valor.valoresVencido, 2, ',', '.');
		let aReceber = _valor.aReceber;
		let definirPagamento = _valor.definirPagamento;
		let valoresVencido = _valor.valoresVencido;
		let valorRecebido = _valor.valorRecebido;
		let valorTotal = _valor.valorTotal;
		//chart Status do pagamento
		var options = {
			//informações do grafico 
			series: [valorRecebido, valoresVencido, definirPagamento, aReceber],
			chart: {
				height: 327,
				type: 'donut',
			},
			//cor de cada elemento
			dataLabels: {
				enabled: false
			},
			//cor de cada elemento
			colors: ['#01E296', '#FD324E', '#FFAF15', "#566FFF"],
			responsive: [{
				breakpoint: 480,
				options: {
					chart: {
						width: 200
					},
					legend: {
						show: false
					}
				}
			}],
			legend: {
				position: 'right',
				offsetY: 0,
				height: 230,
				show: false // oculta as labels da direita,"
			},
			//informações do hover 
			labels: [`Pago: R$ ${number_format(valorRecebido,2,',','.')}`, `Vencidos: R$ ${number_format(valoresVencido,2,',','.')}`, `Definir pagamento: R$  ${number_format(definirPagamento,2,',','.')} `, `A receber: R$  ${number_format(aReceber,2,',','.')}`]
		};
		var chart = new ApexCharts(document.querySelector("#chart1"), options);
		//redenrizar elementos
		chart.render();

		//chart Formas do pagamento
		let series = []
		let labels = []
		for(let x in _extras?.formas_pagamentos){
			series.push(_extras?.formas_pagamentos[x])
			labels.push(`${_formasDePagamento[x]?.titulo}: R$ ${number_format(_extras?.formas_pagamentos[x],2,',','.')}`)
		}
		var options = {
			//informações do grafico 
			series,
			chart: {
				height: 327,
				type: 'donut',
			},
			//cor de cada elemento
			dataLabels: {
				enabled: false
			},
			//cor de cada elemento
			colors: ['#1E145E', '#FC8DB0', '#6EA1D2', "#566FFF"],
			responsive: [{
				breakpoint: 480,
				options: {
					chart: {
						width: 200
					},
					legend: {
						show: false
					}
				}
			}],
			legend: {
				position: 'right',
				offsetY: 0,
				height: 230,
				show: false // oculta as labels da direita,"
			},
			//informações do hover 
			labels
		};
		var chart = new ApexCharts(document.querySelector("#chart2"), options);
		//redenrizar elementos
		chart.render();
	}



	

	//Conciliações dos pagamentos

	var options = {
		series: [{
			name: 'Paguei',
			data: [{
					x: '26/Abr',
					y: 1292,
					goals: [{
						name: 'Conciliado',
						value: 1400,
						strokeHeight: 5,
						strokeColor: '#5C7DB0'
					}]
				},
				{
					x: '27/Abr',
					y: 4432,
					goals: [{
						name: 'Conciliado',
						value: 5400,
						strokeHeight: 5,
						strokeColor: '#5C7DB0'
					}]
				},
				{
					x: '28/Abr',
					y: 5423,
					goals: [{
						name: 'Conciliado',
						value: 5200,
						strokeHeight: 5,
						strokeColor: '#5C7DB0'
					}]
				},
				{
					x: '29/Abr',
					y: 6653,
					goals: [{
						name: 'Conciliado',
						value: 6500,
						strokeHeight: 5,
						strokeColor: '#5C7DB0'
					}]
				},
				{
					x: '30/Abr',
					y: 8133,
					goals: [{
						name: 'Conciliado',
						value: 6600,
						strokeHeight: 5,
						strokeColor: '#5C7DB0'
					}]
				},
				{
					x: '01/Mar',
					y: 7132,
					goals: [{
						name: 'Conciliado',
						value: 7500,
						strokeHeight: 5,
						strokeColor: '#5C7DB0'
					}]
				},
				{
					x: '02/Mar',
					y: 7332,
					goals: [{
						name: 'Conciliado',
						value: 8700,
						strokeHeight: 5,
						strokeColor: '#5C7DB0'
					}]
				}
			]
		}],
		chart: {
			height: 327,
			type: 'bar'
		},
		plotOptions: {
			bar: {
				columnWidth: '60%'
			}
		},
		colors: ['#00E396'],
		dataLabels: {
			enabled: false
		},
		legend: {
			show: true,
			showForSingleSeries: true,
			customLegendItems: ['Paguei', 'Conciliado'],
			markers: {
				fillColors: ['#00E396', '#5C7DB0']
			}
		}
	};

	var chart = new ApexCharts(document.querySelector("#chart3"), options);
	chart.render();

	//chart Emissão de notas e recibos

	var options = {
		//informações do grafico 
		series: [5000, 1000, 2000],
		chart: {
			height: 327,
			type: 'donut',
		},
		//cor de cada elemento
		dataLabels: {
			enabled: false
		},
		//cor de cada elemento
		colors: ['#1E145E', '#546CF8', '#6EA1D2'],
		responsive: [{
			breakpoint: 480,
			options: {
				chart: {
					width: 200
				},
				legend: {
					show: false
				}
			}
		}],
		legend: {
			position: 'right',
			offsetY: 0,
			height: 230,
			show: false // oculta as labels da direita,"
		},
		//informações do hover 
		labels: ['Notas emitidas: R$ 5.000,00', 'Recibos emitidos: R$  1.000,00', 'Não emitidos: R$  2.000,00 ']
	};
	var chart = new ApexCharts(document.querySelector("#chart4"), options);
	//redenrizar elementos
	chart.render();
</script>
<?php
	$apiConfig = array(
		'contasAReceber' => 1,
		'contasAvulsoAReceber' => 1,
	);
	require_once("includes/api/apiAsidePagamentos.php");

	include "includes/footer.php";
?>