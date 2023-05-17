<?php
include "includes/header.php";
include "includes/nav.php";
if ($usr->tipo != "admin" and !in_array("analytics", $_usuariosPermissoes)) {
	$jsc->jAlert("Você não tem permissão para acessar esta área!", "erro", "document.location.href='dashboard.php'");
	die();
}
?>

<header class="header">
	<div class="header__content content">

		<div class="header__inner1">
			<section class="header-title">
				<h1>Analytics</h1>
			</section>
			<?php
			require_once("includes/menus/menuAnalytics.php");
			?>


		</div>
		<div class="header__inner2">
			<section class="header-date">
				<div class="header-date-buttons"></div>
				<div class="header-date-now">
					<h1 class="js-cal-titulo-diames"></h1>
					<h2 class="js-cal-titulo-mes"></h2>
					até
					<h1 class="js-cal-titulo-diames"></h1>
					<h2 class="js-cal-titulo-mes"></h2>
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
					<p>Analise como você está investindo seu tempo.</p>
				</div>
			</div>
			<div class="filter-group">
				<a href="javascript:;" class="button js-calendario"><span class="iconify" data-icon="bi:calendar-week" data-inline="false" data-width="20"></span></a>
				<div class="button-group">
					<a href="">7 dias</a>
					<a href="">30 dias</a>
					<a href="">60 dias</a>
					<a href="">90 dias</a>
					<a href="">ano</a>
				</div>
			</div>
		</section>



		<div class="grid">
			<section class="box">

				<div class="list4">
					<a href="" class="list4-item active">
						<p style="margin: auto; padding: 10px 0px;">Primeiro atendimento</p>
					</a>
					<a href="" class="list4-item">
						<p style="margin: auto; padding: 10px 0px;">Durante o tratamento</p>
					</a>
					<a href="" class="list4-item">
						<p style="margin: auto; padding: 10px 0px;">Atendimento finalizado</p>
					</a>
					<a href="" class="list4-item">
						<p style="margin: auto; padding: 10px 0px;">NPS médio global</p>
					</a>
				</div>

				<div style="margin:0;">
					<div class="box">
						<div class="grid grid_3">
							<div id="chart1" class="list4-item"></div>
							<div id="chart2" class="list4-item"></div>
							<div id="chart3" class="list4-item"></div>
						</div>
					</div>
					<div class="box">
						<div class="grid grid_3">
							<div id="chart4" class="list4-item"></div>
							<div id="chart5" class="list4-item"></div>
							<div id="chart6" class="list4-item"></div>
						</div>
					</div>
				</div>
			</section>
		</div>




	</div>
</main>

<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<script>

/*Primeiro atendimento */	
	var options = {
		series: [{
			data: [400, 430, 448, 470, 540, 580, 690, 1100, 1200, 1380]
		}],
		chart: {
			type: 'bar',
			height: 350
		},
		plotOptions: {
			bar: {
				borderRadius: 4,
				horizontal: true,
			}
		},
		dataLabels: {
			enabled: false
		},
		xaxis: {
			categories: ['Korea', 'Canada', 'United Kingdom', 'Netherlands', 'Italy', 'France', 'Japan',
				'United States', 'China', 'Germany'
			],
		},
		title: {
			text: 'Titulo do grafico'
		},
	};

	var chart = new ApexCharts(document.querySelector("#chart1"), options);
	chart.render();

	var options1 = {
		series: [{
			data: [400, 430, 448]
		}],
		chart: {
			type: 'bar',
			height: 350
		},
		plotOptions: {
			bar: {
				borderRadius: 4,
				horizontal: true,
			}
		},
		dataLabels: {
			enabled: false
		},
		xaxis: {
			categories: ['Ótima', 'Bom', 'Ruim'
			],
		},
		title: {
			text: 'Titulo do grafico'
		},
	};

	var chart1 = new ApexCharts(document.querySelector("#chart2"), options1);
	chart1.render();

	var options2 = {
		series: [{
			data: [400, 430, 448]
		}],
		chart: {
			type: 'bar',
			height: 350
		},
		plotOptions: {
			bar: {
				borderRadius: 4,
				horizontal: true
			}
		},
		dataLabels: {
			enabled: false
		},
		xaxis: {
			categories: ['Ótima', 'Bom', 'Ruim']
		},

		colors: ['#33b2df', '#8f2c2c', '#d4526e'],
		title: {
			text: 'Titulo do grafico'
		}
	};

	var chart2 = new ApexCharts(document.querySelector("#chart3"), options2);
	chart2.render();


/*Durante o tratamento */
	var options4 = {
			series: [{
				data: [400, 430, 448]
			}],
			chart: {
				type: 'bar',
				height: 350
			},
			plotOptions: {
				bar: {
					borderRadius: 4,
					horizontal: true
				}
			},
			dataLabels: {
				enabled: false
			},
			xaxis: {
				categories: ['Korea', 'Canada', 'United Kingdom']
			},

			fill: {
				colors: ['#F44336', '#E91E63', '#9C27B0']},
			title: {
				text: 'Titulo do grafico'
			}
	};
	var chart4 = new ApexCharts(document.querySelector("#chart4"), options4);
		chart4.render();


</script>


<?php



$apiConfig = array('pacienteRelacionamento' => 1);
require_once("includes/api/apiAside.php");

include "includes/footer.php";
?>