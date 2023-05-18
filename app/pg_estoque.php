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
                <a href="pg_estoque.php" class="active">Estoque</a>
                <a href="pg_compras.php" class="">Compras</a>
            </section>
        </div>
    </div>
</header>

<main class="main">
    <div class="main__content content">
    <section class="filter">
        <div class="filter-group">
            <div class="filter-title">	
                <p></p>
            </div>
        </div>				
        <div class="filter-group filter-form form">
            <dl>
                <dd class="form-comp form-comp_pos"><input type="text" name="" placeholder="Buscar...">
                    <a href="">
                        <span class="iconify" data-icon="fluent:search-12-regular" style="color: #848484;"></span>
                    </a>
                </dd>
            </dl>
            <dl>
            <div class="button-group">
                <a href="pg_inteligencia_analytics.php?filtro=7&amp;analyticsTipo=horas" class="button active">Todos</a>
                <a href="pg_inteligencia_analytics.php?filtro=30&amp;analyticsTipo=horas" class="button">Estoque baixo</a>
                <a href="pg_inteligencia_analytics.php?filtro=60&amp;analyticsTipo=horas" class="button">Vencimento próximo</a>
            </div>
        </div>
	</section>

    <div class="box">
        <section class="filter">
            <div class="filter-group">
                <div class="filter-title">
                    <p>Estoque baixo</p>
                    <h2><strong>25 produtos</strong></h2>
                </div>
                <div class="filter-title">
                    <p>Vencimento próximo</p>
                    <h2><strong>10 produtos</strong></h2>
                </div>
                <div class="filter-title">
                    <p>Demais produtos</p>
                    <h2><strong>50 produtos</strong></h2>
                </div>							
            </div>
            <div class="filter-group">
                <a href="" class="button"><span class="iconify" data-icon="fluent:chevron-down-24-regular" style="color: #848484;"></span> <span class="iconify" data-icon="fluent:chevron-up-24-regular" style="color: #848484;"></span>  <span>Veja os gráficos</span></a>
            </div>
        </section>
        <div class=" accordion display-flex-center">
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
                                <span class="color" style="background: #01E296;"></span>
                                <span class="label"><b>Biopharma</b></span>
                                <span class="value">R$ 5.000,00</span>
                            </div>
                            <div class="label-info-2 info-item">
                                <span class="color"style="background: red;"></span>
                                <span class="label"><b>Bege</b></span>
                                <span class="value">R$ 5.000,00</span>
                            </div>
                            <div class="label-info-3 info-item">
                                <span class="color" style="background: black;"></span>
                                <span class="label"><b>Equiplex</b></span>
                                <span class="value">R$ 5.000,00</span>
                            </div>
                            <div class="label-info-3 info-item">
                                <span class="color" style="background:#a9b926;"></span>
                                <span class="label"><b>Descarpack</b></span>
                                <span class="value">R$ 5.000,00</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="formas-pagamento" class="grafico-content" style="display:block">
                    <div class="graficos-view display-flex-center">
                        <div id="chart2" style="height: 305px;"></div>
                        <div id="chart-info2" class="margin-left-25">
                                <div class="info-item">
                                    <span class="color" style="background:#a9b926;"></span>
                                    <span class="label"><b>Biopharma</b></span>
                                    <span class="value">R$ 5.000,00</span>
                                </div>
                                <div class="info-item">
                                    <span class="color" style="background:#a9b926;"></span>
                                    <span class="label"><b>Biopharma</b></span>
                                    <span class="value">R$ 5.000,00</span>
                                </div>
                                <div class="info-item">
                                    <span class="color" style="background:#a9b926;"></span>
                                    <span class="label"><b>Biopharma</b></span>
                                    <span class="value">R$ 5.000,00</span>
                                </div>
                                <div class="info-item">
                                    <span class="color" style="background:#a9b926;"></span>
                                    <span class="label"><b>Biopharma</b></span>
                                    <span class="value">R$ 5.000,00</span>
                                </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <section class="box" style="margin-top:20px">
        <div class="list1">
            <table>
                <tbody>
                    <tr data-aside="plano" class="js-iside-estoque" >
                        <td class="list1__foto"><img src="img/ilustra-usuario.jpg" width="54" height="54"></td>
                            <td >
                                <section class="filter itens" style="margin-bottom: 0px;">
                                    <div class="filter-group">
                                        <div class="filter-title filter-form form" style="gap: 1.5rem;">	
                                            <dl>
                                            <h2><strong style="color:#344848">Produto 01 - Cor A</strong></h2>
                                                <div>Marca X - 3 Gramas</div>
                                            </dl>
                                            <dl>
                                                <dd><div>Estoque min. 2</div></dd>
                                                <dd><div>Estoque min. 2</div></dd>
                                            </dl>
                                            <dl>
                                                <dd><label>10</label> </dd>
                                            </dl>
                                        </div>
                                    </div>
                                </section>
                            </td>
                            <td>
                                <section class="filter itens" style="margin-bottom: 0px;">
                                    <div class="filter-group">
                                        <div class="filter-title">	
                                        </div>
                                    </div>
                                    <div class="filter-group">
                                            <a href="" class="button"><span class="iconify" data-icon="fluent:cart-16-regular"></span> <span>Adicionar</span></a>
                                        </div>
                                    </div>
                                </section>
                        </td>
                    </tr>

                    <tr data-aside="plano" class="js-iside-estoque">
                        <td class="list1__foto"><img src="img/ilustra-usuario.jpg" width="54" height="54"></td>
                            <td>
                                <section class="filter itens" style="margin-bottom: 0px;">
                                    <div class="filter-group">
                                        <div class="filter-title filter-form form" style="gap: 1.5rem;">	
                                            <dl>
                                            <h2><strong style="color:#344848">Produto 01 - Cor A</strong></h2>
                                                <div>Marca X - 3 Gramas</div>
                                            </dl>
                                            <dl>
                                                <dd><div>Estoque min. 2</div></dd>
                                                <dd><div>Estoque min. 2</div></dd>
                                            </dl>
                                            <dl>
                                                <dd><label>10</label> </dd>
                                            </dl>
                                        </div>
                                    </div>
                                </section>
                            </td>
                            <td>
                            <section class="filter itens" style="margin-bottom: 0px;">
                                <div class="filter-group">
                                    <div class="filter-title">	
                                    </div>
                                </div>
                                <div class="filter-group">
                                        <a href="" class="button"><span class="iconify" data-icon="fluent:cart-16-regular"></span> <span>Adicionar</span></a>
                                    </div>
                                </div>
                            </section>
                        </td>
                    </tr>

                </tbody>
            </table>
        </div>
    </section>
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
    'estoque' => 1,
);
require_once("includes/api/apiAsideEstoque.php");
    include "includes/footer.php"; 
?>