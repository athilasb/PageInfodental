<?php
setlocale(LC_TIME, 'pt_BR.UTF-8');
date_default_timezone_set('America/Araguaina');

require_once("lib/conf.php");
require_once("usuarios/checa.php");

// AQUI RECEBO OS AJAXS
if (isset($_POST['ajax'])) {
	$rtn = array();
	if ($_POST['ajax'] == 'updateDataFiltro') {
		$dias = $_POST['dias'] ?? false;
		if ($dias) {
			$data_i =  date('Y-m-d');
			$data_f = date('Y-m-d', strtotime("+$dias days"));
			[$registros, $_baixas, $valor, $_tratamentos, $_pagantes] = getValores($data_i, $data_f);
			$rtn = array('success' => true, 'data' => [
				"pagamentos" => $registros,
				"baixas" => $_baixas,
				"valor" => $valor,
				"tratamentos" => $_tratamentos,
				"pagantes" => $_pagantes,
				"datas" => [
					"data_i" => $data_i,
					"data_f" => $data_f,
					'prefiltro' => true
				]
			]);
			//$rtn = array('success' => false, 'error' => "DATAI: $data_i, DATAF: $data_f");
		} else {
			$rtn = array('success' => false, 'error' => 'Dias Não Informado');
		}
	}
	header("Content-type: application/json");
	echo json_encode($rtn);
	die();
}

include "includes/header.php";
include "includes/nav.php";
$data_inicial_filtro = isset($_GET['data_i']) ? $_GET['data_i'] : date('Y-m-d');
$data_final_filtro =  isset($_GET['data_f']) ? $_GET['data_f'] : date('Y-m-d', strtotime("+7 days"));
$_table = $_p . "financeiro_fluxo_recebimentos";



function getValores($data_inicial, $data_final)
{
	global $sql;
	global $_p;
	global $_table;
	// buscando informações dos pagamentos
	$registros = array();
	$_tratamentos = array();
	$tratamentosIDs = array(-1);
	$pagantesIDs = array(-1);
	$pagamentosIDs = array(-1);
	$pagamentosUnidos = array();
	$_pagantes =  array();
	$valor = array(
		'aReceber' => 0,
		'valorRecebido' => 0,
		'valoresVencido' => 0,
		'valorTotal' => 0,
		'valorJuros' => 0,
		'valorMulta' => 0,
		"definirPagamento" => 0
	);
	//aqui busco os valores do recebimentos sem fusao
	$where = "WHERE (data_vencimento>='$data_inicial' AND data_vencimento<='$data_final') AND  id_fusao=0 and lixo=0 order by data_emissao desc, id asc";
	$sql->consult($_p . "financeiro_fluxo_recebimentos", "*", $where);
	while ($x = mysqli_fetch_object($sql->mysqry)) {
		if ($x->id_fusao == 0) {
			$registros[$x->id] = $x;
		}
		$tratamentosIDs[] = $x->id_tratamento;
		$pagamentosIDs[$x->id] = $x->id;
		$pagantesIDs[$x->id] = $x->id_pagante;

		if ($x->fusao == 1) $pagamentosUnidos[] = $x->id;
	}
	// aqui busco os valores recebimentos com fusao
	$_subpagamentos = array();
	if (count($pagamentosUnidos) > 0) {
		$sql->consult($_table, "*", "where id_fusao IN (" . implode(",", $pagamentosUnidos) . ") and lixo=0");
		while ($x = mysqli_fetch_object($sql->mysqry)) {
			$_subpagamentos[$x->id_fusao][] = $x;
		}
	}
	//AQUI eu Pego os USUARIOS PAGANTES
	if (count($pagantesIDs) > 0) {
		$sql->consult($_p . "pacientes", "*", "where id IN (" . implode(",", $pagantesIDs) . ")");
		while ($x = mysqli_fetch_object($sql->mysqry)) {
			$_pagantes[$x->id]['id'] = $x->id;
			$_pagantes[$x->id]['lixo'] = $x->lixo;
			$_pagantes[$x->id]['nome'] = $x->nome;
			$_pagantes[$x->id]['sexo'] = $x->sexo;
			$_pagantes[$x->id]['telefone1'] = $x->telefone1;
		}
	}
	//AQUI eu Pego os Tratamentos
	if (count($tratamentosIDs) > 0) {
		$sql->consult($_p . "pacientes_tratamentos", "*", "where id IN (" . implode(",", $tratamentosIDs) . ")");
		while ($x = mysqli_fetch_object($sql->mysqry)) {
			//$_tratamentos[$x->id] = $x;
			$_tratamentos[$x->id]['id'] = $x->id;
			$_tratamentos[$x->id]['lixo'] = $x->lixo;
			$_tratamentos[$x->id]['data'] = $x->data;
			$_tratamentos[$x->id]['id_unidade'] = $x->id_unidade;
			$_tratamentos[$x->id]['id_paciente'] = $x->id_paciente;
			$_tratamentos[$x->id]['id_profissional'] = $x->id_profissional;
			$_tratamentos[$x->id]['id_politica'] = $x->id_politica;
			$_tratamentos[$x->id]['titulo'] = $x->titulo;
			$_tratamentos[$x->id]['tempo_estimado'] = $x->tempo_estimado;
			//$_tratamentos[$x->id]['procedimentos'] = $x->procedimentos;
			$_tratamentos[$x->id]['tipo_financeiro'] = $x->tipo_financeiro;
			$_tratamentos[$x->id]['pagamento'] = $x->pagamento;
			$_tratamentos[$x->id]['parcelas'] = $x->parcelas;
			//$_tratamentos[$x->id]['pagamentos'] = $x->pagamentos;
			$_tratamentos[$x->id]['status'] = $x->status;
			$_tratamentos[$x->id]['id_aprovado'] = $x->id_aprovado;
			$_tratamentos[$x->id]['data_aprovado'] = $x->data_aprovado;
		}
	}
	// aqui eu busco as baixas que foram dadas
	$_baixas = array();
	$pagamentosComBaixas = array();
	if (count($pagamentosIDs) > 0) {
		$sql->consult($_p . "financeiro_fluxo", "*", "WHERE (data_vencimento>='$data_inicial' AND data_vencimento<='$data_final') AND id_registro IN (" . implode(",", $pagamentosIDs) . ") and lixo=0 order by data_vencimento asc");
		if ($sql->rows) {
			while ($x = mysqli_fetch_object($sql->mysqry)) {
				$_baixas[$x->id_registro][] = $x;
				$pagamentosComBaixas[$x->id] = $x->id_registro;
			}
		}
	}

	$valorAReceber = $saldoAPagar = $valorDefinido = $multas = $juros = 0;
	// faço um laço de repetição aqui para pegar os valores a receber, em atraso, a definir pagamento etc
	foreach ($registros as $x) {
		$valorDefinido = 0;
		if (isset($_baixas[$x->id])) {
			$dataUltimoPagamento = date('d/m/Y', strtotime($_baixas[$x->id][count($_baixas[$x->id]) - 1]->data));
			foreach ($_baixas[$x->id] as $v) {
				$valor['valorTotal'] += $v->valor;
				if ($v->lixo == 0 && ($v->data_vencimento >= $data_inicial && $v->data_vencimento <= $data_final)) {
					$valor['valorJuros'] += $v->valor_multa;
					$valor['valorMulta'] += $v->valor_taxa;
					$valorDefinido += $v->valor;
					$atraso = (strtotime($v->data_vencimento) - strtotime(date('Y-m-d'))) / (60 * 60 * 24);
					if ($v->pagamento == 1) {
						$valor['valorRecebido'] += $v->valor;
					} else if ($atraso < 0) {
						$valor['valoresVencido'] += $v->valor;
					} else if ($v->pagamento == 0) {
						$valor['aReceber'] += $v->valor;
					}
				}
			}
			if ($valorDefinido < $x->valor) {
				$valor['definirPagamento'] += $x->valor;
			}
		} else {
			$valor['valorTotal'] += $x->valor;
			$atraso = (strtotime($x->data_vencimento) - strtotime(date('Y-m-d'))) / (60 * 60 * 24);
			if ($atraso < 0 and $x->pago == 0) {
				$valor['valoresVencido'] += $x->valor;
			} else {
				$valor['definirPagamento'] += $x->valor;
			}
			//$valor['aReceber']+=$x->valor;
		}
	}

	return [$registros, $_baixas, $valor, $_tratamentos, $_pagantes];
}
[$registros, $_baixas, $valor, $_tratamentos, $_pagantes] = getValores($data_inicial_filtro, $data_final_filtro);




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
					<h1 id="dia_i"></h1>
					<h2 id="mes_i"></h2>
					até
					<h1 id="dia_f"></h1>
					<h2 id="mes_f"></h2>
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
					<p>Lançamentos de contas a receber</p>
				</div>
			</div>

			<div class="filter-group">
				<a href="javascript:;" class="button js-calendario">
					<span class="iconify" data-icon="bi:calendar-week"></span>
				</a>
				<div class="button-group">
					<a href="javascript:;" class="button active btn-prefiltro" data-dias='7'>7 dias</a>
					<a href="javascript:;" class="button btn-prefiltro" data-dias='30'>30 dias</a>
					<a href="javascript:;" class="button btn-prefiltro" data-dias='60'>60 dias</a>
					<a href="javascript:;" class="button btn-prefiltro" data-dias='90'>90 dias</a>
					<a href="javascript:;" class="button btn-prefiltro" data-dias='365'>ano</a>
				</div>
			</div>
		</section>
		<section class="grid">
			<div class="box">
				<section class="filter" style="margin-bottom:0;">
					<div class="filter-group">
						<div class="filter-title">
							<p>Total</p>
							<h2><strong id='valor-valorTotal'>R$ 0,00</strong></h2>
						</div>
						<div class="filter-title">
							<p>A receber</p>
							<h2 style="color:var(--cinza4)" id='valor-aReceber'>R$ 0,00</h2>
						</div>
						<div class="filter-title">
							<p>A definir pagamento</p>
							<h2 style="color:var(--laranja)" id='valor-definirPagamento'>R$ 0,00</h2>
						</div>
						<div class="filter-title">
							<p>Recebido</p>
							<h2 style="color:var(--verde)" id='valor-valorRecebido'>R$ 0,00</h2>
						</div>
						<div class="filter-title">
							<p>Vencido</p>
							<h2 style="color:var(--vermelho)" id='valor-valoresVencido'>R$ 0,00</h2>
						</div>
					</div>
					<div class="filter-group">
						<a href="javascript:;" class="button"><i class="iconify" data-icon="fluent:chevron-down-24-regular"></i> <span>Gráficos</span></a>
					</div>
				</section>
			</div>

			<div class="box">
				<div class="filter">
					<a href="" class="button"><i class="iconify" data-icon="fluent:link-square-24-filled"></i><span>Unir pagamentos</span></a>
				</div>
				<div class="list2">
					<table class="tablesorter" id="list-payments">
						<thead>
							<tr>
								<th></th>
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
							<tr>
								<td><input type="checkbox" name="" /></td>
								<td>10/04/2023</td>
								<td>Vencido</td>
								<td><strong>Pedro Henrique Saddi de Azevedo</strong><br />Reabilitação Oral</td>
								<td><strong>R$ 10.000,00</strong></td>
								<td style="font-size:0.813em; line-height:1.2;">Parcela 1/3<br />Multa: R$ 30,40<br />Juros: R$ 93,47</td>
								<td style="font-size:1.75rem;">
									<span style="color:var(--cinza5)" title="Contrato assinado" class="tooltip"><i class="iconify" data-icon="fluent:signature-20-regular"></i></span>
									<span style="color:var(--cinza5)" title="Nota fiscal emitida" class="tooltip"><i class="iconify" data-icon="heroicons:receipt-percent"></i></span>
									<span style="color:var(--cinza3)" title="Não conciliado" class="tooltip"><i class="iconify" data-icon="fluent:checkbox-checked-sync-20-regular"></i></span>
									<span style="color:var(--cinza3)" title="Regua não executada" class="tooltip"><i class="iconify" data-icon="fluent:task-list-ltr-20-filled"></i></span>
								</td>
								<td><a href="" class="button" style="width:120px"><i class="iconify" data-icon="ph:currency-circle-dollar"></i> <span>Receber</span></a></td>
							</tr>
							<tr>
								<td><input type="checkbox" name="" /></td>
								<td>10/04/2023</td>
								<td>Vencido</td>
								<td><strong>Pedro Henrique Saddi de Azevedo</strong><br />Reabilitação Oral</td>
								<td><strong>R$ 10.000,00</strong></td>
								<td style="font-size:0.813em; line-height:1.2;">Parcela 1/3<br />Multa: R$ 30,40<br />Juros: R$ 93,47</td>
								<td style="font-size:1.75rem;">
									<span style="color:var(--cinza5)" title="Contrato assinado" class="tooltip"><i class="iconify" data-icon="fluent:signature-20-regular"></i></span>
									<span style="color:var(--cinza5)" title="Nota fiscal emitida" class="tooltip"><i class="iconify" data-icon="heroicons:receipt-percent"></i></span>
									<span style="color:var(--cinza3)" title="Não conciliado" class="tooltip"><i class="iconify" data-icon="fluent:checkbox-checked-sync-20-regular"></i></span>
									<span style="color:var(--cinza3)" title="Regua não executada" class="tooltip"><i class="iconify" data-icon="fluent:task-list-ltr-20-filled"></i></span>
								</td>
								<td><a href="" class="button" style="color:var(--verde); width:120px;"><i class="iconify" data-icon="fluent:checkmark-circle-24-regular"></i> <span>Recebido</span></a></td>
							</tr>
							<tr>
								<td><input type="checkbox" name="" /></td>
								<td>10/04/2023</td>
								<td>Vencido</td>
								<td><strong>Pedro Henrique Saddi de Azevedo</strong><br />Reabilitação Oral</td>
								<td><strong>R$ 10.000,00</strong></td>
								<td style="font-size:0.813em; line-height:1.2;">Parcela 1/3<br />Multa: R$ 30,40<br />Juros: R$ 93,47</td>
								<td style="font-size:1.75rem;">
									<span style="color:var(--cinza5)" title="Contrato assinado" class="tooltip"><i class="iconify" data-icon="fluent:signature-20-regular"></i></span>
									<span style="color:var(--cinza5)" title="Nota fiscal emitida" class="tooltip"><i class="iconify" data-icon="heroicons:receipt-percent"></i></span>
									<span style="color:var(--cinza3)" title="Não conciliado" class="tooltip"><i class="iconify" data-icon="fluent:checkbox-checked-sync-20-regular"></i></span>
									<span style="color:var(--cinza3)" title="Regua não executada" class="tooltip"><i class="iconify" data-icon="fluent:task-list-ltr-20-filled"></i></span>
								</td>
								<td><a href="" class="button" style="width:120px;"><i class="iconify" data-icon="ph:currency-circle-dollar"></i> <span>Receber</span></a></td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
		</section>

	</div>
</main>
<script>
	let data_inicial = "<?= $data_inicial_filtro ?>";
	let data_final = "<?= $data_final_filtro ?>";
	let unirPagamentos = false;
	let _valor = <?= json_encode($valor) ?>;
	let _pagamentos = <?= json_encode($registros) ?>;
	let _baixas = <?= json_encode($_baixas) ?>;
	let _tratamentos = <?= json_encode($_tratamentos) ?>;
	let _pagantes = <?= json_encode($_pagantes) ?>;

	function updateValoresHeader() {
		$('#valor-valorTotal').html(`R$ ${number_format(_valor.valorTotal, 2, ",", ".")}`)
		$('#valor-aReceber').html(`R$ ${number_format(_valor.aReceber, 2, ",", ".")}`)
		$('#valor-definirPagamento').html(`R$ ${number_format(_valor.definirPagamento, 2, ",", ".")}`)
		$('#valor-valorRecebido').html(`R$ ${number_format(_valor.valorRecebido, 2, ",", ".")}`)
		$('#valor-valoresVencido').html(`R$ ${number_format(_valor.valoresVencido, 2, ",", ".")}`)
	}

	function updateDatas(data_i = data_inicial, data_f = data_final) {
		const meses = {
			0: 'Jan',
			1: 'Fev',
			2: 'Mar',
			3: 'Abr',
			4: 'Mai',
			5: 'Jun',
			6: 'Jul',
			7: 'Ago',
			8: 'Set',
			9: 'Out',
			10: 'Nov',
			11: 'Dez',
		}
		data_inicial = data_i
		data_final = data_f
		const dataInicialFiltro = new Date(`${data_i} 00:00:00`);
		const dia_i = dataInicialFiltro.getDate();
		const mes_i = dataInicialFiltro.getMonth();
		const ano_i = dataInicialFiltro.getFullYear();

		const dataFinalFiltro = new Date(`${data_f} 00:00:00`);
		const dia_f = dataFinalFiltro.getDate();
		const mes_f = dataFinalFiltro.getMonth();
		const ano_f = dataFinalFiltro.getFullYear();
		$("#dia_i").html(dia_i)
		$("#mes_i").html(`${meses[mes_i]}/${ano_i}`)
		$("#dia_f").html(dia_f)
		$("#mes_f").html(`${meses[mes_f]}/${ano_f}`)
	}

	function populaPagamentos() {
		$('#list-payments tbody').html("");
		for (let id_pagamento in _baixas) {
			let baixas = _baixas[id_pagamento]
			let pagamento = _pagamentos[id_pagamento]
			let tratamento = _tratamentos[pagamento.id_tratamento] ?? false
			let checkbox = (unirPagamentos) ? `<input type="checkbox" name="checkboxUnir" data-idPagamento=${pagamento.id} />` : '';
			let vencido = "DEFINIR PAGAMENTO";
			let pagante = _pagantes[pagamento.id_pagante]
			let valorEmJogo = 0;
			let valorMulta = 0;
			let valorJuros = 0;
			for (let id_baixa in baixas) {
				let baixa = baixas[id_baixa]
				vencido = ((new Date(baixa.data_vencimento) <= new Date())) ? 'Vencido' : 'Em Dias';
				vencido = (baixa.pagamento == 1) ? 'Pago' : vencido;
				valorEmJogo += baixa.valor
				$('#list-payments tbody').append(`
						<tr data-idPagamento='${pagamento.id}'>
						<td>${checkbox}</td>
						<td>${formatarData(baixa.data_vencimento)}</td>
						<td>${vencido}</td>
						<td><strong>${pagante.nome}</strong><br />${tratamento.titulo}</td>
						<td><strong>R$ ${number_format(baixa.valor,2,',','.')}</strong></td>
						<td style="font-size:0.813em; line-height:1.2;">Multa: R$ ${number_format(baixa.valor_multa,2,',','.')}<br />Juros: R$ ${number_format(baixa.valor_juros,2,',','.')}</td>
						<td style="font-size:1.75rem;">
						<span style="color:var(--cinza3)" title="Contrato assinado" class="tooltip"><i class="iconify" data-icon="fluent:signature-20-regular"></i></span>
						<span style="color:var(--cinza3)" title="Nota fiscal emitida" class="tooltip"><i class="iconify" data-icon="heroicons:receipt-percent"></i></span>
						<span style="color:var(--cinza3)" title="Não conciliado" class="tooltip"><i class="iconify" data-icon="fluent:checkbox-checked-sync-20-regular"></i></span>
						<span style="color:var(--cinza3)" title="Regua não executada" class="tooltip"><i class="iconify" data-icon="fluent:task-list-ltr-20-filled"></i></span>
						</td>
						<td><a href="javascript:;" class="button" style="width:120px"><i class="iconify" data-icon="ph:currency-circle-dollar"></i> <span>Receber</span></a></td>
						</tr>
					`)
			}
			vencido = "DEFINIR PAGAMENTO";
			vencido = ((new Date(pagamento.data_vencimento) <= new Date())) ? 'Vencido' : 'Em Dias';
			vencido = (pagamento.pagamento == 1) ? 'Pago' : vencido;
			if (valorEmJogo < pagamento.valor) {
				$('#list-payments tbody').append(`
						<tr data-idPagamento='${pagamento.id}'>
						<td>${checkbox}</td>
						<td>${formatarData(pagamento.data_vencimento)}</td>
						<td>${vencido}</td>
						<td><strong>${pagante.nome}</strong><br />${tratamento.titulo}</td>
						<td><strong>R$ ${number_format((pagamento.valor-valorEmJogo),2,',','.')}</strong></td>
						<td style="font-size:0.813em; line-height:1.2;">Multa: R$ ${number_format(pagamento.valor_multa,2,',','.')}<br />Juros: R$ ${number_format(pagamento.valor_juros,2,',','.')}</td>
						<td style="font-size:1.75rem;">
						<span style="color:var(--cinza3)" title="Contrato assinado" class="tooltip"><i class="iconify" data-icon="fluent:signature-20-regular"></i></span>
						<span style="color:var(--cinza3)" title="Nota fiscal emitida" class="tooltip"><i class="iconify" data-icon="heroicons:receipt-percent"></i></span>
						<span style="color:var(--cinza3)" title="Não conciliado" class="tooltip"><i class="iconify" data-icon="fluent:checkbox-checked-sync-20-regular"></i></span>
						<span style="color:var(--cinza3)" title="Regua não executada" class="tooltip"><i class="iconify" data-icon="fluent:task-list-ltr-20-filled"></i></span>
						</td>
						<td><a href="javascript:;" class="button" style="width:120px"><i class="iconify" data-icon="ph:currency-circle-dollar"></i> <span>Receber</span></a></td>
						</tr>
					`)
			}

		}
		for (let id_pag in _pagamentos) {
			if (!_baixas[id_pag]) {
				let pagamento = _pagamentos[id_pag]
				let tratamento = _tratamentos[pagamento.id_tratamento] ?? false
				let checkbox = (unirPagamentos) ? `<input type="checkbox" name="checkboxUnir" data-idPagamento=${pagamento.id} />` : '';
				let vencido = "DEFINIR PAGAMENTO";
				let pagante = _pagantes[pagamento.id_pagante]
				let valorEmJogo = 0;
				let valorMulta = 0;
				let valorJuros = 0;
				vencido = ((new Date(pagamento.data_vencimento) <= new Date())) ? 'Vencido' : 'Em Dias';
				vencido = (pagamento.pagamento == 1) ? 'Pago' : vencido;
				$('#list-payments tbody').append(`
						<tr data-idPagamento='${pagamento.id}'>
						<td>${checkbox}</td>
						<td>${formatarData(pagamento.data_vencimento)}</td>
						<td>${vencido}</td>
						<td><strong>${pagante.nome}</strong><br />${tratamento.titulo}</td>
						<td><strong>R$ ${number_format(pagamento.valor,2,',','.')}</strong></td>
						<td style="font-size:0.813em; line-height:1.2;">Multa: R$ ${number_format(pagamento.valor_multa,2,',','.')}<br />Juros: R$ ${number_format(pagamento.valor_juros,2,',','.')}</td>
						<td style="font-size:1.75rem;">
						<span style="color:var(--cinza3)" title="Contrato assinado" class="tooltip"><i class="iconify" data-icon="fluent:signature-20-regular"></i></span>
						<span style="color:var(--cinza3)" title="Nota fiscal emitida" class="tooltip"><i class="iconify" data-icon="heroicons:receipt-percent"></i></span>
						<span style="color:var(--cinza3)" title="Não conciliado" class="tooltip"><i class="iconify" data-icon="fluent:checkbox-checked-sync-20-regular"></i></span>
						<span style="color:var(--cinza3)" title="Regua não executada" class="tooltip"><i class="iconify" data-icon="fluent:task-list-ltr-20-filled"></i></span>
						</td>
						<td><a href="javascript:;" class="button" style="width:120px"><i class="iconify" data-icon="ph:currency-circle-dollar"></i> <span>Receber</span></a></td>
						</tr>
					`)
			}
		}
	}


	$(function() {
		$('.btn-prefiltro').click(function() {
			$(this).closest('div').find('a').map((i, el) => {
				$(el).removeClass('active')
			})
			$(this).addClass('active')
			let dias = $(this).attr('data-dias')

			let data = `ajax=updateDataFiltro&dias=${dias}`;
			$.ajax({
				type: "POST",
				data: data,
				success: function(rtn) {
					console.log(rtn)
					if (rtn.success) {
						_valor = rtn.data.valor
						_baixas = rtn.data.baixas
						_pagantes = rtn.data.pagantes
						_pagamentos = rtn.data.pagamentos
						_pagantes = rtn.data.pagantes
						_tratamentos = rtn.data.tratamentos
						updateValoresHeader()
						updateDatas(rtn.data.datas.data_i, rtn.data.datas.data_f)
						populaPagamentos()
					} else if (rtn.error) {
						swal({
							title: "Erro!",
							text: rtn.error,
							html: true,
							type: "error",
							confirmButtonColor: "#424242"
						});
					} else {
						swal({
							title: "Erro!",
							text: "Algum erro ocorreu",
							html: true,
							type: "error",
							confirmButtonColor: "#424242"
						});
					}
				},
				error: function() {
					swal({
						title: "Erro!",
						text: "Algum erro ocorreu durante a requisição.",
						html: true,
						type: "error",
						confirmButtonColor: "#424242"
					});
				}
			})
		})
	})
	updateValoresHeader()
	updateDatas(data_inicial, data_final)
	populaPagamentos();
</script>

<?php include "includes/footer.php"; ?>