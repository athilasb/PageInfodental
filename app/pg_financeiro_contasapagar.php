<?php
echo sha1(287);die();
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
	[$dados, $valor] = getPagamentos($data_inicial_filtro, $data_final_filtro);
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
				<div class="filter-title">
					<dl>
						<dd>
							<a class="button button_main js-btn-abrir-aside"><i class="iconify" data-icon="fluent:add-circle-24-regular"></i> <span>Novo Pagamento</span></a>
						</dd>
					</dl>
				</div>
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
							<h2 style="color:var(--cinza4)" id='valor-aReceber'>R$ <?= number_format($valor['aPagar'], 2, ',', '.') ?></h2>
						</div>
						<div class="filter-title">
							<p>Pago</p>
							<h2 style="color:var(--verde)" id='valor-valorRecebido'>R$ <?= number_format($valor['valorPago'], 2, ',', '.') ?></h2>
						</div>
						<div class="filter-title">
							<p>Vencido</p>
							<h2 style="color:var(--vermelho)" id='valor-valoresVencido'>R$ <?= number_format($valor['valorVencido'], 2, ',', '.') ?></h2>
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
									<td><a href="javascript:;" class="button js-pagamento-item" style="width:120px" data-idregistro='<?= $x->id_registro ?>'><i class="iconify" data-icon="ph:currency-circle-dollar"></i> <span>Pagar</span></a></td>
								</tr>
							<?php } ?>
						</tbody>
						</tbody>
					</table>
				</div>
			</div>
		</section>
	</div>
</main>
<script>
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
	$('.js-pagamento-item').on('click', function() {
		let id_registro = $(this).attr('data-idregistro')
		swal({
				title: "Atenção",
				text: "Você tem certeza que deseja pagar este registro?",
				type: "warning",
				showCancelButton: true,
				confirmButtonColor: "#DD6B55",
				confirmButtonText: "Sim!",
				cancelButtonText: "Não",
				closeOnConfirm: false,
				closeOnCancel: false
			},
			function(isConfirm) {
				if (isConfirm) {
					console.log('DELETANDO...')
					swal({
						title: "Erro!",
						text: "AINDA NAO IMPLEMENTADO...",
						html: true,
						type: "error",
						confirmButtonColor: "#424242"
					});
					//document.location.href = '?<#?= "id_paciente=$paciente->id&id_pagamento="; ?>' + idPagamento;
				} else {
					swal.close();
				}
			});


	})
</script>

<?php
$apiConfig = array(
	'contasAPagar' => 1,
);
require_once("includes/api/apiAsidePagamentos.php");

include "includes/footer.php";
?>