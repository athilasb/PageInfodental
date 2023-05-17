<?php
	require_once("lib/conf.php");
	require_once("usuarios/checa.php");


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
		$_pacientes =  array();
		$_colaboradores =  array();
		$_fornecedores =  array();
		$idRegistros = array();
		$idPacientes = array();
		$idColaboradores = array();
		$idFornecedores = array();
		$idsPagamentos = array();
		$valor = array(
			'aPagar' => 0,
			'valorPago' => 0,
			'valoresVencido' => 0,
			'valorTotal' => 0,
			'valorJuros' => 0,
			'valorMulta' => 0,
			'valorDescontos' => 0,
			"definirPagamento" => 0
		);
		// pegando as oriugens
		$sql->consult($_p . "financeiro_fluxo_origens", "*", "WHERE 1");
		if ($sql->rows) {
			while ($x = mysqli_fetch_object($sql->mysqry)) {
				$origens[$x->id] = $x->tabela;
			}
		}
		//pegando os recebimentos totais
		$sql->consult($_p . "financeiro_fluxo_pagamentos", "*", "WHERE (data_vencimento>='$data_inicial' AND data_vencimento<='$data_final') and lixo=0 order by data_vencimento asc");
		if ($sql->rows) {
			while ($x = mysqli_fetch_object($sql->mysqry)) {
				$_recebimentos[$x->id] = $x;
				$idsPagamentos[$x->id] = $x->id;
				if($x->tipo=='paciente'){
					$idPacientes[$x->id_pagante_beneficiario] = $x->id_pagante_beneficiario;
				}else if($x->tipo=='fornecedor'){
					$idFornecedores[$x->id_pagante_beneficiario] = $x->id_pagante_beneficiario;
				}else if($x->tipo=='colaborador'){
					$idColaboradores[$x->id_pagante_beneficiario] = $x->id_pagante_beneficiario;
				}
			}
		}
		// aqui eu busco as baixas que foram dadas
		if (count($idsPagamentos) > 0) {
			$_fluxos = array();
			$sql->consult($_p . "financeiro_fluxo", "*", "WHERE id_registro IN (" . IMPLODE(',', $idsPagamentos) . ") AND lixo=0 order by data_vencimento asc");
			if ($sql->rows) {
				while ($x = mysqli_fetch_object($sql->mysqry)) {
					$_fluxos[$x->id_registro][$x->id] = $x;
				}
			}
		}
	
	
		// pegando os pagantes
		if (count($idPacientes) > 0) {
			$sql->consult($_p . "pacientes", "*", " WHERE id IN (" . IMPLODE(',', $idPacientes) . ")");
			if ($sql->rows) {
				while ($x = mysqli_fetch_object($sql->mysqry)) {
					$_pacientes[$x->id] = $x;
				}
			}
		}
		// pegando os pagantes
		if (count($idFornecedores) > 0) {
			$sql->consult($_p . "parametros_fornecedores", "*", " WHERE id IN (" . IMPLODE(',', $idFornecedores) . ")");
			if ($sql->rows) {
				while ($x = mysqli_fetch_object($sql->mysqry)) {
					$_fornecedores[$x->id] = $x;
				}
			}
		}
		// pegando os pagantes
		if (count($idColaboradores) > 0) {
			$sql->consult($_p . "colaboradores", "*", " WHERE id IN (" . IMPLODE(',', $idColaboradores) . ")");
			if ($sql->rows) {
				while ($x = mysqli_fetch_object($sql->mysqry)) {
					$_colaboradores[$x->id] = $x;
				}
			}
		}
		$dados = array();
		$extras = array();
		// aqui faço um foreach em todos os pagamentos
		foreach ($_recebimentos as $id_recebimento => $recebimento) {
			$titulo = "Pagamento Avulso";
			$pagante  = "";
			if($recebimento->tipo=='paciente'){
				$pagante  = (isset($_pacientes[$recebimento->id_pagante_beneficiario]->nome)) ? utf8_decode($_pacientes[$recebimento->id_pagante_beneficiario]->nome) : '-';
			}else if($recebimento->tipo=='fornecedor'){
				$pagante  = (isset($_fornecedores[$recebimento->id_pagante_beneficiario]->nome)) ? utf8_decode($_fornecedores[$recebimento->id_pagante_beneficiario]->nome) : '-';
			}else if($recebimento->tipo=='colaborador'){
				$pagante  = (isset($_colaboradores[$recebimento->id_pagante_beneficiario]->nome)) ? utf8_decode($_colaboradores[$recebimento->id_pagante_beneficiario]->nome) : '-';
			}
			$status = "";
			// verifica se existe um fluxo
			$valor['valorTotal'] += $recebimento->valor;
			// se existe alguma baixa para este pagamento
			if (isset($_fluxos[$id_recebimento])) {
				$fluxos = $_fluxos[$id_recebimento];
				$valor_total = 0;
				// aqui faço um foreach em todos os fluxos
				foreach ($fluxos as $id_fluxo => $fluxo) {
					// se nao é desconto
					if($fluxo->desconto==0){
						isset($extras['formas_pagamentos'][$fluxo->id_formapagamento]) ? $extras['formas_pagamentos'][$fluxo->id_formapagamento] += $fluxo->valor : $extras['formas_pagamentos'][$fluxo->id_formapagamento] = intVal($fluxo->valor);
						$valor_total += $fluxo->valor;
						if($fluxo->data_vencimento>=$data_inicial && $fluxo->data_vencimento<=$data_final){
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
							$dados[$fluxo->id]['exibir'] = 1;
							if ($fluxo->pagamento == 0) {
								$atraso = (strtotime($fluxo->data_vencimento) - strtotime(date('Y-m-d'))) / (60 * 60 * 24);
								if ($atraso < 0) {
									$valor['valoresVencido'] += $fluxo->valor;
									$dados[$fluxo->id]['status'] = 'Vencido';
									$extras['ids']['valoresVencido']['fluxo'][$fluxo->id] = $fluxo->id;
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
					}else{
						// se é desconto
						$valor_total +=$fluxo->valor;
						$valor['valorTotal'] -= $fluxo->valor;
					}
				}
				// aqui é se ainda tiver algum valor faltando da parcela total
				$faltam =0;
				if ($valor_total > $recebimento->valor) {
					$faltam = ($recebimento->valor - $valor_total);
				}
				$dados[$id_recebimento]['id_pagante_beneficiario'] = $recebimento->id_pagante_beneficiario;
				$dados[$id_recebimento]['tipo_beneficiario'] = $recebimento->tipo;
				$dados[$id_recebimento]['data_vencimento'] = $recebimento->data_vencimento;
				$dados[$id_recebimento]['id_registro'] = $recebimento->id;
				$dados[$id_recebimento]['pagamento'] = 0;
				$dados[$id_recebimento]['data_efetivado'] = null;
				$dados[$id_recebimento]['tipo'] = 'fluxo';
				$dados[$id_recebimento]['valor'] = $faltam;
				$dados[$id_recebimento]['valor_multa'] = $recebimento->valor_multa;
				$dados[$id_recebimento]['valor_taxa'] = $recebimento->valor_taxa;
				$dados[$id_recebimento]['valor_desconto'] = $recebimento->valor_desconto;
				$dados[$id_recebimento]['valor_juros'] = 0;
				$dados[$id_recebimento]['desconto'] = 0;
				$dados[$id_recebimento]['valorTotalPagamento'] = $recebimento->valor ?? 0;
				$dados[$id_recebimento]['titulo'] = $titulo;
				$dados[$id_recebimento]['nome_pagante'] = $pagante;
				$dados[$id_recebimento]['status'] = '';
				$dados[$id_recebimento]['baixas'] = $_fluxos[$id_recebimento];
				$dados[$id_recebimento]['saldo_a_pagar'] = $faltam;
				$dados[$id_recebimento]['exibir'] = ($faltam!=0)?1:0;

				$atraso = (strtotime($recebimento->data_vencimento) - strtotime(date('Y-m-d'))) / (60 * 60 * 24);
				if ($atraso < 0) {
					$valor['valoresVencido'] += $faltam;
					$extras['ids']['valoresVencido']['recebimento'][$recebimento->id] = $recebimento->id;
					$dados[$recebimento->id]['status'] = 'Vencido';
				} else {
					$valor['definirPagamento'] += $faltam;
					$extras['ids']['definirPagamento']['recebimento'][$recebimento->id] = $recebimento->id;
					$dados[$recebimento->id]['status'] = 'DEFINIR PAGAMENTO';
				}
			} else {
				$dados[$id_recebimento]['baixas'] = [];
				// se Nao existe alguma baixa para este pagamento
				$atraso = (strtotime($recebimento->data_vencimento) - strtotime(date('Y-m-d'))) / (60 * 60 * 24);
				if ($atraso < 0) {
					$valor['valoresVencido'] += $recebimento->valor;
					$extras['ids']['valoresVencido']['recebimento'][$recebimento->id] = $recebimento->id;
					$status = "Vencido";
				} else {
					$valor['definirPagamento'] += $recebimento->valor;
					$extras['ids']['definirPagamento']['recebimento'][$recebimento->id] = $recebimento->id;
					$status = "DEFINIR PAGAMENT";
				}
				$dados[$id_recebimento]['id_registro'] = $recebimento->id;
				$dados[$id_recebimento]['id_pagamento'] = $recebimento->id;
				$dados[$id_recebimento]['tipo_beneficiario'] = $recebimento->tipo;
				$dados[$id_recebimento]['id_pagante_beneficiario'] = $recebimento->id_pagante_beneficiario;
				$dados[$id_recebimento]['data_vencimento'] = $recebimento->data_vencimento;
				$dados[$recebimento->id]['data_emissao'] = $recebimento->data_emissao;
				$dados[$id_recebimento]['pagamento'] = 0;
				$dados[$id_recebimento]['data_efetivado'] = null;
				$dados[$id_recebimento]['tipo'] = 'pagamento';
				$dados[$id_recebimento]['valor'] = $recebimento->valor;
				$dados[$id_recebimento]['saldo_a_pagar'] = $recebimento->valor;
				$dados[$id_recebimento]['valor_multa'] = $recebimento->valor_multa;
				$dados[$id_recebimento]['valor_taxa'] = $recebimento->valor_taxa;
				$dados[$id_recebimento]['valor_desconto'] = $recebimento->valor_desconto;
				$dados[$id_recebimento]['valor_juros'] = 0;
				$dados[$id_recebimento]['desconto'] = 0;
				$dados[$id_recebimento]['valorTotalPagamento'] = $recebimento->valor;
				$dados[$id_recebimento]['titulo'] = $titulo;
				$dados[$id_recebimento]['nome_pagante'] = $pagante;
				$dados[$id_recebimento]['status'] = $status;
				$dados[$id_recebimento]['exibir'] = 1;
				$dados[$id_recebimento]['baixas'] = [];
			}
		}
		$dados = json_decode(json_encode($dados));
		return [$dados, $_recebimentos, $valor, $extras];
	}
	[$dados, $_recebimentos, $valor, $extras] = getValores($data_inicial_filtro, $data_final_filtro);
	//debug($dados,true);
?>
<head>
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200;300;400;500;600;700;800&display=swap" rel="stylesheet">
	<link rel="stylesheet" type="text/css" href="css/style.css?v99" />
	<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

</head>
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
<main class="main" data-pagina="contasapagar">
	<div class="main__content content">
		<section class="filter">
			<div class="filter-group">
				<dl>
					<dd>
						<a href="javascript:;" class="button button_main js-btn-abrir-aside"  data-tipo="novo"><i class="iconify" data-icon="fluent:add-circle-24-regular"></i> <span>Novo Pagamento</span></a>
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
				<div class="" style="display:flex; flex-wrap:wrap; justify-content:space-between;">
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
								<h2 style="color:var(--vermelho)" id='valor-valoresVencido'>R$ <?= number_format($valor['valoresVencido'], 2, ',', '.') ?></h2>
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
										<span class="value">R$ <?= number_format($valor['valorPago'], 2, ',', '.') ?></span>
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
										<span class="label"><b>A Pagar</b></span>
										<span class="value">R$ <?= number_format($valor['aPagar'], 2, ',', '.') ?></span>
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
							<?php 
								foreach ($dados as $x) { 
									if(isset($x->exibir) && $x->exibir==1){
							?>
								<tr>
									<td><?= date('d/m/Y', strtotime($x->data_vencimento)) ?></td>
									<td><?= $x->status ?></td>
									<td><strong><?= $x->nome_pagante ?></strong><br /><?= $x->titulo ?></td>
									<td><strong>R$ <?= number_format($x->valor, 2, ',', '.') ?></strong></td>
									<td style="font-size:0.813em; line-height:1.2;">
										<span style="color:var(<?=($x->valor_multa==0)?'--cinza3':'--cinza5'?>)" class="tooltip">Multa: R$ <?= number_format($x->valor_multa, 2, ',', '.') ?></span><br>
										<span style="color:var(<?=($x->valor_juros==0)?'--cinza3':'--cinza5'?>)" class="tooltip">Juros: R$ <?= number_format($x->valor_juros, 2, ',', '.') ?></span><br>
										<span style="color:var(<?=($x->valor_desconto==0)?'--cinza3':'--cinza5'?>)" class="tooltip">Desconto: R$ <?= number_format($x->valor_desconto, 2, ',', '.') ?></span><br>
										<span style="color:var(--cinza5)" class="tooltip">Total: R$ <?= number_format((($x->valor+$x->valor_multa+$x->valor_juros)-$x->valor_desconto), 2, ',', '.') ?></span><br>
									</td>
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
							<?php 
								}} 
							?>
						</tbody>
						<!-- <tfoot>
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
						</tfoot> -->
					</table>
				</div>
			</div>
		</section>
	</div>
</main>
<script>
	let _pagamentos = <?=json_encode($dados)?>;
	const _valor = <?= json_encode($valor) ?>;
	const _formasDePagamento = <?= json_encode($_formasDePagamento); ?>;
	const _extras = <?= json_encode($extras); ?>;
	populaGraficos()
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

	function populaGraficos() {
		let aPagar = _valor?.aPagar??0;
		let definirPagamento = _valor?.definirPagamento??0;
		let valoresVencido = _valor?.valoresVencido??0;
		let valorPago = _valor?.valorPago??0;
		let valorTotal = _valor?.valorTotal??0;
		//chart Status do pagamento
		var options = {
			//informações do grafico 
			series: [valorPago, valoresVencido, definirPagamento, aPagar],
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
			labels: [`Pago: R$ ${number_format(valorPago,2,',','.')}`, `Vencidos: R$ ${number_format(valoresVencido,2,',','.')}`, `Definir pagamento: R$  ${number_format(definirPagamento,2,',','.')} `, `A receber: R$  ${number_format(aPagar,2,',','.')}`]
		};
		var chart = new ApexCharts(document.querySelector("#chart1"), options);
		//redenrizar elementos
		chart.render();

		//chart Formas do pagamento
		let series = []
		let labels = []
		if(_extras?.formas_pagamentos){
			for(let x in _extras?.formas_pagamentos){
				series.push(_extras?.formas_pagamentos[x])
				labels.push(`${_formasDePagamento[x]?.titulo}: R$ ${number_format(_extras?.formas_pagamentos[x],2,',','.')}`)
			}
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

	$(document).ready(function() {
		$('#arrow-up').hide(); // oculta o ícone de seta para cima
		$('.link-graficos').click(function() {
			$(".accordion").slideToggle();
			$('#arrow-up').toggle();
			$('#arrow-down').toggle();

		});
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