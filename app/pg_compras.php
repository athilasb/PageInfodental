<?php

    require_once("lib/conf.php");
    require_once("usuarios/checa.php");

    include "includes/header.php";
    include "includes/nav.php";
    if($usr->tipo!="admin" and !in_array("estoque",$_usuariosPermissoes)) {
        $jsc->jAlert("Você não tem permissão para acessar esta área!","erro","document.location.href='dashboard.php'");
        die();
    }


?>
<header class="header">
    <div class="header__content content">

        <div class="header__inner1">
            <section class="header-title">
                <h1>Estoque</h1>
            </section>
            <section class="tab">
                <a href="pg_estoque.php" class="">Estoque</a>
                <a href="pg_estoque.php" class="active">Compras</a>
            </section>
        </div>
    </div>
</header>



<main class="main ">
    <div class="main__content content">
        <section class="filter">
            <div class="filter-group">
                <div class="filter-title">	
                    <h1>Lista de compras</h1>
                </div>
            </div>
        </section>

        <div class="grid grid_3">

            <div class="box-etapas">
                <div class="box-titulo-etapas active">Aprovado</div>
                <div class="box-inf-etapas">
                    <div class="conteudo-box-etapas js-iside-compras">
                        <div>Compra 001</div>
                        <div><span class="iconify" data-icon="material-symbols:check-box" style="color: #15b64f;"></span> Aprovado</div>
                        <div>4</div>
                    </div>
                    <div class="conteudo-box-etapas js-iside-compras">
                        <div>Compra 001</div>
                        <div><span class="iconify" data-icon="material-symbols:check-box" style="color: #15b64f;"></span> Aprovado</div>
                        <div>4</div>
                    </div>
                    <div class="conteudo-box-etapas js-iside-compras">
                        <div>Compra 001</div>
                        <div><span class="iconify" data-icon="material-symbols:check-box" style="color: #15b64f;"></span> Aprovado</div>
                        <div>4</div>
                    </div>
                </div>
            </div>
            <div class="box-etapas">
                <div class="box-titulo-etapas">Aguard. aprovação</div>
                <div class="box-inf-etapas">
                    <div class="conteudo-box-etapas js-iside-compras">
                        <div>Compra 001</div>
                        <div><span class="iconify" data-icon="material-symbols:check-box" style="color: #15b64f;"></span> Aprovado</div>
                        <div>4</div>
                    </div>
                    <div class="conteudo-box-etapas js-iside-compras">
                        <div>Compra 001</div>
                        <div><span class="iconify" data-icon="material-symbols:check-box" style="color: #15b64f;"></span> Aprovado</div>
                        <div>4</div>
                    </div>
                    <div class="conteudo-box-etapas js-iside-compras">
                        <div>Compra 001</div>
                        <div><span class="iconify" data-icon="material-symbols:check-box" style="color: #15b64f;"></span> Aprovado</div>
                        <div>4</div>
                    </div>
                </div>
            </div>
            <div class="box-etapas">
                <div class="box-titulo-etapas disable">Reprovado</div>
                <div class="box-inf-etapas">
                    <div class="conteudo-box-etapas js-iside-compras">
                        <div>Compra 001</div>
                        <div class="disable"><span class="iconify" data-icon="fluent:dismiss-square-20-regular"></span>Reprovado</div>
                        <div>4</div>
                    </div>
                    <div class="conteudo-box-etapas js-iside-compras">
                        <div>Compra 001</div>
                        <div class="disable"><span class="iconify" data-icon="fluent:dismiss-square-20-regular"></span>Reprovado</div>
                        <div>4</div>
                    </div>
                    <div class="conteudo-box-etapas js-iside-compras">
                        <div>Compra 001</div>
                        <div class="disable"><span class="iconify" data-icon="fluent:dismiss-square-20-regular"></span>Reprovado</div>
                        <div>4</div>
                    </div>
                </div>
            </div>
        </div>    
    
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    	var options = {
			//informações do grafico 
			series: [5.00000, 5.00000, 5.00000, 5.00000],
			chart: {
				height: 327,
				type: 'donut',
			},
			//cor de cada elemento
			dataLabels: {
				enabled: false
			},
			//cor de cada elemento
			colors: ['#01E296', 'red', '#FFAF15', "#a9b926"],
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
			labels: [`Pago: R$ 5.000,00`, `Vencidos: R$ 5.000,00`, `Definir pagamento: R$  5.000,00`, `A receber: R$  5.000,00`]
		};
		var chart = new ApexCharts(document.querySelector("#chart1"), options);
		//redenrizar elementos
		chart.render();

        var options1 = {
			//informações do grafico 
			series: [5.00000, 5.00000, 5.00000, 5.00000],
			chart: {
				height: 327,
				type: 'donut',
			},
			//cor de cada elemento
			dataLabels: {
				enabled: false
			},
			//cor de cada elemento
			colors: ['#01E296', 'red', '#FFAF15', "#a9b926"],
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
			labels: [`Pago: R$ 5.000,00`, `Vencidos: R$ 5.000,00`, `Definir pagamento: R$  5.000,00`, `A receber: R$  5.000,00`]
		};
		var chart1 = new ApexCharts(document.querySelector("#chart2"), options1);
		//redenrizar elementos
		chart1.render();
</script>

<?php 

$apiConfig = array(
    'compras' => 1,
);
require_once("includes/api/apiAsideEstoque.php");
    include "includes/footer.php"; 
?>