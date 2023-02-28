<?php
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




	$(function() {
		// quando clica em algum pre filtro de datas
		$('.btn-prefiltro').click(function() {
			$(this).closest('div').find('a').map((i, el) => {
				$(el).removeClass('active')
			})
			$(this).addClass('active')
			let dias = $(this).attr('data-dias')

		
		})
		// Quando clica para abrir o aside
		$('.js-pagamento-item').on('click', (function() {
			console.log('clicou Aqui')
			let idPagamento = $(this).attr('data-id-pagamento') ?? false
			let idBaixa = $(this).attr('data-id-baixa') ?? false
			
		}));
	})
</script>


<?php
$apiConfig = array(
	'Pagamentos' => 1,
);
require_once("includes/api/apiAsidePagamentos.php");

include "includes/footer.php";
?>