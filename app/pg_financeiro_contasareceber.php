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
	$_registros = array();
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
	$_baixas = array();
	$sql->consult($_p . "financeiro_fluxo", "*", "WHERE (data_vencimento>='$data_inicial' AND data_vencimento<='$data_final') and lixo=0 AND desconto=0  AND valor>0 order by data_vencimento asc");
	if ($sql->rows) {
		while ($x = mysqli_fetch_object($sql->mysqry)) {
			$_baixas[$x->id] = $x;
			$origem = $origens[$x->id_origem];
			$idRegistros[$x->id_registro] = $x->id_registro;
		}
	}
	//pegando os recebimentos totais
	$sql->consult($_p . "financeiro_fluxo_recebimentos", "*", "WHERE (data_vencimento>='$data_inicial' AND data_vencimento<='$data_final') and lixo=0 order by data_vencimento asc");
	if ($sql->rows) {
		while ($x = mysqli_fetch_object($sql->mysqry)) {
			$_registros[$x->id] = $x;
			$idsPagamentos[$x->id] = $x->id;
			$idTratamentos[$x->id_tratamento] = $x->id_tratamento;
			$idPagantes[$x->id_pagante] = $x->id_pagante;
		}
	}
	// pegandos os IDS pagantes e tratamentos
	if (count($idRegistros) > 0) {
		$sql->consult($_p . "financeiro_fluxo_recebimentos", "*", " WHERE id IN (" . IMPLODE(',', $idRegistros) . ")");
		if ($sql->rows) {
			while ($x = mysqli_fetch_object($sql->mysqry)) {
				$_registros[$x->id] = $x;
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

	$dados = [];
	foreach ($_baixas as $baixa) {
		$titulo = (isset($_registros[$baixa->id_registro]) && isset($_registros[$baixa->id_registro]->id_tratamento) && isset($_tratamentos[$_registros[$baixa->id_registro]->id_tratamento])) ? utf8_encode($_tratamentos[$_registros[$baixa->id_registro]->id_tratamento]->titulo) : "";
		$pagante  = (isset($_registros[$baixa->id_registro]) && isset($_registros[$baixa->id_registro]->id_pagante) && isset($_pagantes[$_registros[$baixa->id_registro]->id_pagante])) ? $_pagantes[$_registros[$baixa->id_registro]->id_pagante]->nome : '';
		$dados[$baixa->id]['id_baixa'] = $baixa->id;
		$dados[$baixa->id]['data_vencimento'] = $baixa->data_vencimento;
		$dados[$baixa->id]['id_registro'] = $baixa->id_registro;
		$dados[$baixa->id]['pagamento'] = $baixa->pagamento;
		$dados[$baixa->id]['data_efetivado'] = $baixa->data_efetivado;
		$dados[$baixa->id]['tipo'] = 'fluxo';
		$dados[$baixa->id]['valor'] = $baixa->valor;
		$dados[$baixa->id]['valor_multa'] = $baixa->valor_multa;
		$dados[$baixa->id]['valor_taxa'] = $baixa->valor_taxa;
		$dados[$baixa->id]['valor_desconto'] = $baixa->valor_desconto;
		$dados[$baixa->id]['valor_juros'] = $baixa->valor_juros;
		$dados[$baixa->id]['desconto'] = $baixa->desconto;
		$dados[$baixa->id]['valorTotalPagamento'] = $_registros[$baixa->id_registro]->valor ?? 0;
		$dados[$baixa->id]['titulo'] = $titulo;
		$dados[$baixa->id]['nome_pagante'] = $pagante;
		$dados[$baixa->id]['status'] = '';
		$valor['valorTotal'] += $baixa->valor;
		if ($baixa->pagamento == 1) {
			$valor['valorRecebido'] += $baixa->valor;
			$dados[$baixa->id]['status'] = 'Pago';
		} else {
			$atraso = (strtotime($baixa->data_vencimento) - strtotime(date('Y-m-d'))) / (60 * 60 * 24);
			if ($atraso < 0) {
				$valor['valoresVencido'] += $baixa->valor;
				$dados[$baixa->id]['status'] = 'Vencido';
			} else {
				$valor['aReceber'] += $baixa->valor;
				$dados[$baixa->id]['status'] = 'a Receber';
			}
		}
	}




	foreach ($_registros as $id => $pag) {
		$sql->consult($_p . "financeiro_fluxo", "SUM(valor)", "WHERE id_registro='$id' AND id_origem=1 AND lixo=0");
		$key = "SUM(valor)";
		$soma = mysqli_fetch_object($sql->mysqry);
		if ($soma->$key >= $pag->valor) {
			//echo "SOMA È MAIOR OU IGUAL QUE O VALOR ORIGINAL<br>";
		} else {
			$faltam = ($pag->valor - $soma->$key);
			//echo "AINDA FALTA UM VALOR DE: $faltam<br>";
			$titulo = (isset($_tratamentos[$pag->id_tratamento]->titulo)) ? utf8_decode($_tratamentos[$pag->id_tratamento]->titulo) : "-";
			$pagante  = (isset($_pagantes[$pag->id_pagante]->nome)) ? $_pagantes[$pag->id_pagante]->nome : '-';
			$valor['definirPagamento'] += $faltam;
			$dados[$id]['id_baixa'] = $pag->id;
			$dados[$id]['data_vencimento'] = $pag->data_vencimento;
			$dados[$id]['id_registro'] = $pag->id;
			$dados[$id]['pagamento'] = 0;
			$dados[$id]['data_efetivado'] = null;
			$dados[$id]['tipo'] = 'recebimento';
			$dados[$id]['valor'] = $faltam;
			$dados[$id]['valor_multa'] = $pag->valor_multa;
			$dados[$id]['valor_taxa'] = $pag->valor_taxa;
			$dados[$id]['valor_desconto'] = $pag->valor_desconto;
			$dados[$id]['valor_juros'] = 0;
			$dados[$id]['desconto'] = 0;
			$dados[$id]['valorTotalPagamento'] = $_registros[$pag->id]->valor;
			$dados[$id]['titulo'] = $titulo;
			$dados[$id]['nome_pagante'] = $pagante;
			$dados[$id]['status'] = 'DEFINIR PAGAMENTO';
			$valor['valorTotal'] += $pag->valor;
		}
	}
	$dados = json_decode(json_encode($dados));
	return [$dados, $_registros, $valor];
}

[$dados, $_registros, $valor] = getValores($data_inicial_filtro, $data_final_filtro);

?>
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
<main class="main">
	<div class="main__content content">
		<section class="filter">
			<div class="filter-group">
					<dl>
						<dd>
							<a href="javascript:;" id='pagamento_avulso-receber' class="button button_main js-btn-abrir-aside"><i class="iconify" data-icon="fluent:add-circle-24-regular"></i> <span>Pagamento Avulso</span></a>
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
			</div>
			<div class="box">
				<!-- <div class="filter">
					<a href="" class="button"><i class="iconify" data-icon="fluent:link-square-24-filled"></i><span>Unir pagamentos</span></a>
				</div> -->
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
	$('.js-pagamento-item').on('click', (function() {
		let idRegistro = $(this).attr('data-idRegistro')
		abrirAside('contasAreceber', idRegistro)
	}));
</script>
<?php
$apiConfig = array(
	'Pagamentos' => 1,
	'avulsoAReceber' => 1,
);
require_once("includes/api/apiAsidePagamentos.php");

include "includes/footer.php";
?>