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
						<dd><a href="#" class="button button_main"><i class="iconify" data-icon="fluent:add-circle-24-regular"></i> <span>Novo Pagamento</span></a></dd>
					</dl>
				</div>
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
		<section class="grid" style="grid-template-columns:40% auto">
			<div class="box">
				<div class="filter">
					<div class="filter-group">
						<div class="filter-title">
							<h1>Indicadores</h1>
						</div>
					</div>
					<div class="filter-group">
						<div class="filter-title">
							<h1>0 Pagamentos</h1>
						</div>
					</div>
				</div>

				<div class="list4">
					<a href="javascript:;" class="list4-item active js-grafico" data-grafico="2">
						<div>
							<h1><i class="iconify" data-icon="fluent:food-cake-20-regular"></i></h1>
						</div>
						<div>
							<p>Status <strong>por Pagamento</strong></p>
						</div>
					</a>
					<a href="javascript:;" class="list4-item js-grafico" data-grafico="3">
						<div>
							<h1><i class="iconify" data-icon="ph:gender-intersex"></i></h1>
						</div>
						<div>
							<p>Formas <strong>de Pagamento</strong></p>
						</div>
					</a>
					<a href="javascript:;" class="list4-item js-grafico" data-grafico="4">
						<div>
							<h1><i class="iconify" data-icon="fluent:location-20-regular"></i></h1>
						</div>
						<div>
							<p>Conciliações <strong>de Pagamentos</strong></p>
						</div>
					</a>
					<a href="javascript:;" class="list4-item js-grafico" data-grafico="1">
						<div>
							<h1><i class="iconify" data-icon="fluent:person-add-20-regular"></i></h1>
						</div>
						<div>
							<p>Emissão de <strong>Notas e Recibos</strong></p>
						</div>
					</a>
				</div>
				<div class="grafico">
					<canvas id="grafico1" class="box-grafico" width="300px" height="150px" style="display: none;"></canvas>
					<canvas id="grafico2" class="box-grafico" width="300px" height="150px" style="display: none;"></canvas>
					<canvas id="grafico3" class="box-grafico" width="300px" height="150px" style="display: none;"></canvas>
					<script src="https://maps.googleapis.com/maps/api/js?key=<?php echo $_googleMapsKey; ?>&libraries=geometry,drawing,places&callback=initMap" defer></script>
					<section id="grafico4" class="box-grafico" style="width: 600px;height:500px;margin-bottom: 10px;display:none;"></section>
					<canvas id="grafico5" class="box-grafico" width="300px" height="150px" style="display: none;"></canvas>
				</div>
				<?php /*<section style="width:100%; height:300px; background:var(--cinza2); margin-bottom:var(--margin1);" class="grafico">						
					</section>*/ ?>
			</div>

			<div class="box">
				<div class="list1">
					<table>
						<tr class="js-item" data-id="id">
							<td class="list1__border" style="color:red"></td>
							<td>
								<h1></h1>
								<p></p>
							</td>
							<td></td>
							<td></td>
							<td></td>
						</tr>

					</table>
				</div>
				<div class="pagination">

				</div>
			</div>
		</section>
	</div>
</main>

<?php
$apiConfig = array(
	'Pagamentos' => 1,
);
require_once("includes/api/apiAsidePagamentos.php");

include "includes/footer.php";
?>