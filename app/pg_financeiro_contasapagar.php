<?php
require_once("lib/conf.php");
require_once("usuarios/checa.php");


include "includes/header.php";
include "includes/nav.php";
$data_inicial_filtro = isset($_GET['data_inicio']) ? $_GET['data_inicio'] : date('Y-m-d');
$data_final_filtro =  isset($_GET['data_final']) ? $_GET['data_final'] : date('Y-m-d', strtotime("+7 days"));
$dias_filtro = (strtotime($data_final_filtro) - strtotime($data_inicial_filtro)) / (60 * 60 * 24);

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
				<div class="filter-title">
					<dl>
						<dd><a class="button button_main js-btn-abrir-aside"><i class="iconify" data-icon="fluent:add-circle-24-regular"></i> <span>Novo Pagamento</span></a></dd>
					</dl>
				</div>
			</div>
			<div class="filter-group">
				<a href="javascript:;" class="button js-calendario">
					<span class="iconify" data-icon="bi:calendar-week"></span>
				</a>
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
							<h2><strong id='valor-valorTotal'>R$ <?= number_format(0, 2, ',', '.') ?></strong></h2>
						</div>
						<div class="filter-title">
							<p>A receber</p>
							<h2 style="color:var(--cinza4)" id='valor-aReceber'>R$ <?= number_format(0, 2, ',', '.') ?></h2>
						</div>
						<div class="filter-title">
							<p>Recebido</p>
							<h2 style="color:var(--verde)" id='valor-valorRecebido'>R$ <?= number_format(0, 2, ',', '.') ?></h2>
						</div>
						<div class="filter-title">
							<p>Vencido</p>
							<h2 style="color:var(--vermelho)" id='valor-valoresVencido'>R$ <?= number_format(0, 2, ',', '.') ?></h2>
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
							<tr>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
		</section>
	</div>
</main>
<script>
	$('.js-btn-abrir-aside').on('click', (function() {
		console.log('ABRIU ')
		abrirAside1()
	}));
</script>

<?php
$apiConfig = array(
	'AddPagamento' => 1,
);
require_once("includes/api/apiAsidePagamentos.php");

include "includes/footer.php";
?>