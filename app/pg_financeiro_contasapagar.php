<?php
	require_once("lib/conf.php");
	require_once("usuarios/checa.php");


	include "includes/header.php";
	include "includes/nav.php";
	$data_inicial_filtro = isset($_GET['data_inicio']) ? $_GET['data_inicio'] : date('Y-m-d');
	$data_final_filtro =  isset($_GET['data_final']) ? $_GET['data_final'] : date('Y-m-d', strtotime("+7 days"));
	$dias_filtro = (strtotime($data_final_filtro) - strtotime($data_inicial_filtro)) / (60 * 60 * 24);

	function getPagamentos($data_inicial_filtro, $data_final_filtro) {
		global $sql;
		global $_p;
		$_origens = array();
		$_pagamentos = array();
		$_pagantes = array();
		$idPagantes = array();
		$valor = array(
			'aPagar' => 0,
			'valorPago' => 0,
			'valorVencido' => 0,
			'valorTotal' => 0,
			'valorJuros' => 0,
			'valorMulta' => 0,
		);
		// pegando as oriugens
		$sql->consult($_p . "financeiro_fluxo_origens", "*", "WHERE 1");
		if ($sql->rows) {
			while ($x = mysqli_fetch_object($sql->mysqry)) {
				$_origens[$x->id] = $x->tabela;
			}
		}
		// aqui eu busco as baixas que foram dadas
		$sql->consult($_p . "financeiro_fluxo", "*", "WHERE (data_vencimento>='$data_inicial_filtro' AND data_vencimento<='$data_final_filtro') AND lixo=0 AND valor<0 AND id_dividido=0 order by data_vencimento asc");
		if ($sql->rows) {
			while ($x = mysqli_fetch_object($sql->mysqry)) {
				$_pagamentos[$x->id]['id'] = $x->id;
				$_pagamentos[$x->id]['data'] = $x->data;
				$_pagamentos[$x->id]['lixo'] = $x->lixo;
				$_pagamentos[$x->id]['id_origem'] = $x->id_origem;
				$_pagamentos[$x->id]['id_registro'] = $x->id_registro;
				$_pagamentos[$x->id]['data_vencimento'] = $x->data_vencimento;
				$_pagamentos[$x->id]['pagamento'] = $x->pagamento;
				$_pagamentos[$x->id]['pagamento_id_colaborador'] = $x->pagamento_id_colaborador;
				$_pagamentos[$x->id]['data_efetivado'] = $x->data_efetivado;
				$_pagamentos[$x->id]['id_formapagamento'] = $x->id_formapagamento;
				$_pagamentos[$x->id]['id_operadora'] = $x->id_operadora;
				$_pagamentos[$x->id]['id_bandeira'] = $x->id_bandeira;
				$_pagamentos[$x->id]['taxa_cartao'] = $x->taxa_cartao;
				$_pagamentos[$x->id]['tipo'] = $x->tipo;
				$_pagamentos[$x->id]['id_pagante_beneficiario'] = $x->id_pagante_beneficiario;
				$_pagamentos[$x->id]['valor'] = $x->valor;
				$_pagamentos[$x->id]['valor_multa'] = $x->valor_multa;
				$_pagamentos[$x->id]['valor_taxa'] = $x->valor_taxa;
				$_pagamentos[$x->id]['valor_desconto'] = $x->valor_desconto;
				$_pagamentos[$x->id]['valor_juros'] = $x->valor_juros;
				$_pagamentos[$x->id]['obs'] = $x->obs;
				$_pagamentos[$x->id]['id_banco'] = $x->id_banco;
				$_pagamentos[$x->id]['lixo_data'] = $x->lixo_data;
				$_pagamentos[$x->id]['lixo_id_colaborador'] = $x->lixo_id_colaborador;
				$_pagamentos[$x->id]['desconto'] = $x->desconto;
				$_pagamentos[$x->id]['descricao'] = utf8_encode($x->descricao);
				$_pagamentos[$x->id]['id_categoria'] = $x->id_categoria;
				$_pagamentos[$x->id]['id_centro_custo'] = $x->id_centro_custo;
				$_pagamentos[$x->id]['dividido'] = $x->dividido;
				$_pagamentos[$x->id]['id_dividido'] = $x->id_dividido;
				$origem = $_origens[$x->id_origem];
				$idRegistros[$x->id_registro] = $x->id_registro;
				$idPagantes[$x->id_pagante_beneficiario] = $x->tipo;
			}
			$_pagamentos = json_decode(json_encode($_pagamentos));
		}
		//pegando os pagantes
		if (count($idPagantes) > 0) {
			foreach ($idPagantes as $id => $tipo) {
				if ($tipo == 'paciente') {
					$sql->consult($_p . "pacientes", "*", " WHERE id =$id");
					if ($sql->rows) {
						while ($x = mysqli_fetch_object($sql->mysqry)) {
							// $_pagantes[$x->id] = $x;
							$_pagantes[$x->id]['nome'] = utf8_encode($x->nome);
						}
					}
				} else if ($tipo == 'colaborador') {
					$sql->consult($_p . "colaboradores", "*", " WHERE id =$id");
					if ($sql->rows) {
						while ($x = mysqli_fetch_object($sql->mysqry)) {
							// $_pagantes[$x->id] = $x;
							$_pagantes[$x->id]['nome'] = utf8_encode($x->nome);
						}
					}
				} else if ($tipo == 'fornecedor') {
					$sql->consult($_p . "parametros_fornecedores", "*", " WHERE id =$id");
					if ($sql->rows) {
						while ($x = mysqli_fetch_object($sql->mysqry)) {
							// $_pagantes[$x->id] = $x;
							$_pagantes[$x->id]['nome'] = utf8_encode($x->razao_social);
						}
					}
				}
			}
		}
		// montando o objeto

		$dados = [];
		foreach ($_pagamentos as $baixa) {
			$titulo = (isset($_registros[$baixa->id_registro]) && isset($_registros[$baixa->id_registro]->id_tratamento) && isset($_tratamentos[$_registros[$baixa->id_registro]->id_tratamento])) ? utf8_encode($_tratamentos[$_registros[$baixa->id_registro]->id_tratamento]->titulo) : "";
			$pagante  = $_pagantes[$baixa->id_pagante_beneficiario]['nome'] ?? 'Nao Econtrado';

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
			$dados[$baixa->id]['titulo'] = $baixa->descricao;
			$dados[$baixa->id]['nome_pagante'] = $pagante;
			$dados[$baixa->id]['status'] = '';
			$valor['valorTotal'] += $baixa->valor;
			if ($baixa->pagamento == 1) {
				$valor['valorPago'] += $baixa->valor;
				$dados[$baixa->id]['status'] = 'Pago';
			} else {
				$atraso = (strtotime($baixa->data_vencimento) - strtotime(date('Y-m-d'))) / (60 * 60 * 24);
				if ($atraso < 0) {
					$valor['valorVencido'] += $baixa->valor;
					$dados[$baixa->id]['status'] = 'Vencido';
				} else {
					$valor['aPagar'] += $baixa->valor;
					$dados[$baixa->id]['status'] = 'a Pagar';
				}
			}
		}
		$dados = json_decode(json_encode($dados));
		return [$dados, $valor];
	}
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
			'aPagar' => 0,
			'valorPago' => 0,
			'valorVencido' => 0,
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
				$idRegistros[$x->id_registro] = $x->id_registro;
			}
		}
		//pegando os recebimentos totais
		$sql->consult($_p . "financeiro_fluxo_pagamentos", "*", "WHERE (data_vencimento>='$data_inicial' AND data_vencimento<='$data_final') and lixo=0 order by data_vencimento asc");
		if ($sql->rows) {
			while ($x = mysqli_fetch_object($sql->mysqry)) {
				$_recebimentos[$x->id] = $x;
				$idsPagamentos[$x->id] = $x->id;
				$idPagantes[$x->id_pagante_beneficiario] = $x->id_pagante_beneficiario;
			}
		}
		// pegandos os IDS pagantes e tratamentos
		if (count($idRegistros) > 0) {
			$sql->consult($_p . "financeiro_fluxo_pagamentos", "*", " WHERE id IN (" . IMPLODE(',', $idRegistros) . ") AND lixo=0");
			if ($sql->rows) {
				while ($x = mysqli_fetch_object($sql->mysqry)) {
					$_recebimentos[$x->id] = $x;
					$idPagantes[$x->id_pagante_beneficiario] = $x->id_pagante_beneficiario;
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
			$titulo = "Pagamento Avulso";
			$pagante  = (isset($_pagantes[$recebimento->id_pagante_beneficiario]->nome)) ? utf8_decode($_pagantes[$recebimento->id_pagante_beneficiario]->nome) : '-';
			// verifica se existe um fluxo
			$valor['valorTotal'] += $recebimento->valor;
			if (isset($_fluxos[$id_recebimento])) {
				$fluxos = $_fluxos[$id_recebimento];
				$valor_total = 0;
				foreach ($fluxos as $id_fluxo => $fluxo) {
					isset($extras['formas_pagamentos'][$fluxo->id_formapagamento]) ? $extras['formas_pagamentos'][$fluxo->id_formapagamento] += $fluxo->valor : $extras['formas_pagamentos'][$fluxo->id_formapagamento] = intVal($fluxo->valor);
					$valor_total += $fluxo->valor;
					$dados[$fluxo->id]['id_baixa'] = $fluxo->id;
					$dados[$fluxo->id]['id_pagante_beneficiario'] = $fluxo->id_pagante_beneficiario;
					$dados[$fluxo->id]['tipo_beneficiario'] = $fluxo->tipo;
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
							$valor['valorVencido'] += $fluxo->valor;
							$dados[$fluxo->id]['status'] = 'Vencido';
							$extras['ids']['valorVencido']['fluxo'][$fluxo->id] = $fluxo->id;
						} else {
							$valor['aPagar'] += $fluxo->valor;
							$dados[$fluxo->id]['status'] = 'a Receber';
							$extras['ids']['aPagar']['fluxo'][$fluxo->id] = $fluxo->id;
						}
					} else {
						$valor['valorPago'] += $fluxo->valor;
						$dados[$fluxo->id]['status'] = 'Pago';
						$extras['ids']['valorPago']['fluxo'][$fluxo->id] = $fluxo->id;
					}
				}
				if ($valor_total < $recebimento->valor) {
					$faltam = ($recebimento->valor - $valor_total);
					$atraso = (strtotime($recebimento->data_vencimento) - strtotime(date('Y-m-d'))) / (60 * 60 * 24);
	
					$dados[$id_recebimento]['id_baixa'] = $fluxo->id;
					$dados[$id_recebimento]['id_pagante_beneficiario'] = $recebimento->id_pagante_beneficiario;
					$dados[$id_recebimento]['tipo_beneficiario'] = $recebimento->tipo;
					$dados[$id_recebimento]['data_vencimento'] = $fluxo->data_vencimento;
					$dados[$id_recebimento]['id_registro'] = $fluxo->id_registro;
					$dados[$id_recebimento]['pagamento'] = $fluxo->pagamento;
					$dados[$id_recebimento]['data_efetivado'] = $fluxo->data_efetivado;
					$dados[$id_recebimento]['tipo'] = 'fluxo';
					$dados[$id_recebimento]['valor'] = $faltam;
					$dados[$id_recebimento]['valor_multa'] = $fluxo->valor_multa;
					$dados[$id_recebimento]['valor_taxa'] = $fluxo->valor_taxa;
					$dados[$id_recebimento]['valor_desconto'] = $fluxo->valor_desconto;
					$dados[$id_recebimento]['valor_juros'] = $fluxo->valor_juros;
					$dados[$id_recebimento]['desconto'] = $fluxo->desconto;
					$dados[$id_recebimento]['valorTotalPagamento'] = $recebimento->valor ?? 0;
					$dados[$id_recebimento]['titulo'] = $titulo;
					$dados[$id_recebimento]['nome_pagante'] = $pagante;
					$dados[$id_recebimento]['status'] = '';
	
					if ($atraso < 0) {
						$valor['valorVencido'] += $faltam;
						$extras['ids']['valorVencido']['recebimento'][$recebimento->id] = $recebimento->id;
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
					$valor['valorVencido'] += $recebimento->valor;
					$extras['ids']['valorVencido']['recebimento'][$recebimento->id] = $recebimento->id;
					$valor['valorVencido'] += $recebimento->valor;
					$dados[$id_recebimento]['id_baixa'] = $recebimento->id;
					$dados[$id_recebimento]['id_pagante_beneficiario'] = $recebimento->id_pagante_beneficiario;
					$dados[$id_recebimento]['tipo_beneficiario'] = $recebimento->tipo;
					$dados[$recebimento->id]['data_emissao'] = $recebimento->data_emissao;
					$dados[$id_recebimento]['data_vencimento'] = $recebimento->data_vencimento;
					$dados[$id_recebimento]['id_registro'] = $recebimento->id;
					$dados[$id_recebimento]['pagamento'] = 0;
					$dados[$id_recebimento]['data_efetivado'] = null;
					$dados[$id_recebimento]['tipo'] = 'pagamento';
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
					$dados[$id_recebimento]['tipo_beneficiario'] = $recebimento->tipo;
					$dados[$id_recebimento]['id_pagante_beneficiario'] = $recebimento->id_pagante_beneficiario;
					$dados[$id_recebimento]['data_vencimento'] = $recebimento->data_vencimento;
					$dados[$recebimento->id]['data_emissao'] = $recebimento->data_emissao;
					$dados[$id_recebimento]['id_registro'] = $recebimento->id;
					$dados[$id_recebimento]['pagamento'] = 0;
					$dados[$id_recebimento]['data_efetivado'] = null;
					$dados[$id_recebimento]['tipo'] = 'pagamento';
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
		$dados = json_decode(json_encode($dados));
		return [$dados, $_recebimentos, $valor, $extras];
	}

	
	[$dados, $_recebimentos, $valor, $extras] = getValores($data_inicial_filtro, $data_final_filtro);
	//[$dados, $valor] = getPagamentos($data_inicial_filtro, $data_final_filtro);
?>
<header class="header">
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
	<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
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
					<h1 class="js-cal-titulo-diames"><?php echo date('d', strtotime($data_inicial_filtro)); ?></h1>
					<h2 class="js-cal-titulo-mes"><?php echo substr(strtolower(mes(date('m', strtotime($data_inicial_filtro)))), 0, 3); ?>/<?php echo substr(strtolower((date('Y', strtotime($data_inicial_filtro)))), 2, 2); ?></h2>
					até
					<h1 class="js-cal-titulo-diames"><?php echo date('d', strtotime($data_final_filtro)); ?></h1>
					<h2 class="js-cal-titulo-mes"><?php echo substr(strtolower(mes(date('m', strtotime($data_final_filtro)))), 0, 3); ?>/<?php echo substr(strtolower((date('Y', strtotime($data_final_filtro)))), 2, 2); ?></h2>
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
						<a href="javascript:;" class="button button_main js-btn-abrir-aside"><i class="iconify" data-icon="fluent:add-circle-24-regular"></i> <span>Novo Pagamento</span></a>
					</dd>
				</dl>
			</div>
			<div class="filter-group">
				<a href="javascript:;" class="button js-calendario"><span class="iconify" data-icon="bi:calendar-week" data-inline="false" data-width="20"></span></a>
				<div class="button-group">
					<a href="/pg_financeiro_contasapagar.php?data_inicio=<?= date('Y-m-d') ?>&data_final=<?= date('Y-m-d', strtotime('+ 7 days')) ?>" class="button btn-prefiltro <?= ($dias_filtro == 7) ? 'active' : '' ?>" data-dias='7'>7 dias</a>
					<a href="/pg_financeiro_contasapagar.php?data_inicio=<?= date('Y-m-d') ?>&data_final=<?= date('Y-m-d', strtotime('+ 30 days')) ?>" class="button btn-prefiltro <?= ($dias_filtro == 30) ? 'active' : '' ?>" data-dias='30'>30 dias</a>
					<a href="/pg_financeiro_contasapagar.php?data_inicio=<?= date('Y-m-d') ?>&data_final=<?= date('Y-m-d', strtotime('+ 60 days')) ?>" class="button btn-prefiltro <?= ($dias_filtro == 60) ? 'active' : '' ?>" data-dias='60'>60 dias</a>
					<a href="/pg_financeiro_contasapagar.php?data_inicio=<?= date('Y-m-d') ?>&data_final=<?= date('Y-m-d', strtotime('+ 90 days')) ?>" class="button btn-prefiltro <?= ($dias_filtro == 90) ? 'active' : '' ?>" data-dias='90'>90 dias</a>
					<a href="/pg_financeiro_contasapagar.php?data_inicio=<?= date('Y-m-d') ?>&data_final=<?= date('Y-m-d', strtotime('+ 365 days')) ?>" class="button btn-prefiltro <?= ($dias_filtro == 365) ? 'active' : '' ?>" data-dias='365'>ano</a>
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
							<p>A Pagar</p>
							<h2 style="color:var(--cinza4)" id='valor-aPagar'>R$ <?= number_format($valor['aPagar'], 2, ',', '.') ?></h2>
						</div>
						<div class="filter-title">
							<p>Pago</p>
							<h2 style="color:var(--verde)" id='valor-valorPago'>R$ <?= number_format($valor['valorPago'], 2, ',', '.') ?></h2>
						</div>
						<div class="filter-title">
							<p>Vencido</p>
							<h2 style="color:var(--vermelho)" id='valor-valorVencido'>R$ <?= number_format($valor['valorVencido'], 2, ',', '.') ?></h2>
						</div>
						<div class="filter-title">
							<p>A definir pagamento</p>
							<h2 style="color:var(--laranja)" id='valor-definirPagamento'>R$ <?= number_format($valor['definirPagamento'], 2, ',', '.') ?></h2>
						</div>
					</div>
					<div class="filter-group">
						<!-- <a href="javascript:;" class="button"><i class="iconify" data-icon="fluent:chevron-down-24-regular"></i> <span>Gráficos</span></a> -->
					</div>
				</section>
			</div>
			<div class="box">
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
									<td>
										<a href="javascript:;" class="button js-pagamento-item" style="width:120px; <?=($x->pagamento==1)?'color:var(--cinza3)':'';?>" data-idregistro='<?= $x->id_registro ?>' >
											<i class="iconify" data-icon="ph:currency-circle-dollar"></i> 
											<span><?=($x->pagamento==1)?'Pago':'Pagar';?></span>
										</a>
									</td>
								</tr>
							<?php } ?>
						</tbody>
						<tfoot>
							<td>00/00/0000</td>
							<td>Teste</td>
							<td>Teste de Pagamento</td>
							<td> R$ 500,00</td>
							<td>0,00</td>
							<td>-</td>
							<td><a href="javascript:;" class="button js-pagamento-item" style="width:120px;" data-idregistro='7' >
									<i class="iconify" data-icon="ph:currency-circle-dollar"></i> 
								</a>
							</td>
						</tfoot>
					</table>
				</div>
			</div>
		</section>
	</div>
</main>
<script>
	const _pagamentos = <?=json_encode($dados)?>;
	$('.js-btn-abrir-aside').on('click', (function() {
		abrirAside1()
	}));
	// add calendario no botao de filtro
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
		document.location.href = `<?php echo "$_page?pg_financeiro_contasapagar?"; ?>&data_inicio=${dtInicio}&data_final=${dtFim}`
	});



</script>
<?php 
	$apiConfig = array(
		'contasAPagar' => 1,
		'confirmaPagamento' => 1,
	);
	require_once("includes/api/apiAsidePagamentos.php");

	$apiConfig = array(
		'financeiroFluxoCategorias' => 1,
	);
	require_once("includes/api/apiAsideFinanceiro.php");

	include "includes/footer.php";
?>