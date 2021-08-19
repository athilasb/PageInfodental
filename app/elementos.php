<?php
include "includes/header.php";
include "includes/nav.php";
?>

		<section class="content">

			<header class="caminho">
				<h1 class="caminho__titulo">Caminho <i class="iconify" data-icon="bx-bx-chevron-right"></i> <strong>Item</strong></h1>
				<a href="" class="caminho__tutorial button button__sec"><i class="iconify" data-icon="ic-baseline-slow-motion-video"></i> ASSISTIR TUTORIAL</a>
			</header>

			<section class="content-grid">

				<section class="content__item">

					<h1 class="content__titulo1">Título da section</h1>
					<h1 class="content__titulo2">Título da section (menor)</h1>

					<ul class="abas">
						<li><a href="" class="active">Abas</a></li>
						<li><a href="">Vale Transporte</a></li>
						<li><a href="">Férias</a></li>
						<li><a href="">Rescisão</a></li>
						<li><a href="">13º</a></li>
						<li><a href="">Folha</a></li>
					</ul>

					<div class="acoes">
						<a href="" class="button button__lg"><i class="iconify" data-icon="bx-bx-plus-circle"></i> Botão de ação principal</a>
						<a href="" class="button button__lg button__sec">Botão secundário</a>
						<a href="" class="button button__lg button__ter">Botão terciário</a>
					</div>

					<section class="filtros">
						<form method="post" class="filtros-form form">
							<div class="colunas6">
								<dl>
									<dt>Título</dt>
									<dd><input type="text" name="" /></dd>
								</dl>
								<dl>
									<dt>Título</dt>
									<dd><select name="" class="chosen"><option value=""></option><option value="">Valor</option></select></dd>
								</dl>
								<dl class="dl2">
									<dt>Título</dt>
									<dd><select name="" class="chosen" multiple><option value=""></option><option value="">Valor 1</option><option value="">Valor 2</option><option value="">Outro valor que faz pular</option></select></dd>
								</dl>
								<dl>
									<dd><button type="submit" class="button button__sec"><i class="iconify" data-icon="bx-bx-search" data-inline="false"></i></button></dd>
								</dl>
							</div>
						</form>
						<div class="filtros-acoes">
							<a href="modelo-form-ajax.php" data-fancybox data-type="ajax" data-padding="0" class="filtros-acoes__button tooltip" title="adicionar"><i class="iconify" data-icon="ic-baseline-add"></i></a>
							<a href="modelo-form-ajax.php" data-fancybox data-type="ajax" data-padding="0" class="filtros-acoes__button tooltip" title="confirmar"><i class="iconify" data-icon="ic-baseline-check"></i></a>
						</div>
					</section>

					<div class="registros-qtd">
						<p class="registros-qtd__item">1075 itens</p>
						<p class="registros-qtd__item">R$3.353 total</p>
						<a href="" class="registros-qtd__item"><i class="iconify" data-icon="bx-bxs-download"></i> download</a>
					</div>

					<div class="registros">
						<table class="tablesorter">
							<thead>
								<tr>
									<th>DATA</th>
									<th>NOME</th>
									<th>CPF</th>
									<th>TELEFONE</th>
									<th>CIDADE-UF</th>
									<th>PAINEL</th>
									<th style="width:160px;">AÇÕES</th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td>24/06/2020 13:27</td>
									<td>Kroner Machado Costa</td>
									<td>029.808.481-31</td>
									<td>62999181775</td>
									<td>GOIÂNIA-GO</td>
									<td><a href=""><i class="iconify" data-icon="ic-outline-dashboard"></i> PAINEL</a></td>
									<td>
										<a href="modelo-form.php" class="registros__acao"><i class="iconify" data-icon="bx:bxs-edit-alt"></i></a>
										<a href="" class="registros__acao registros__acao_sec"><i class="iconify" data-icon="bx:bxs-trash"></i></a>
									</td>
								</tr>
								<tr>
									<td>24/06/2020 13:27</td>
									<td>Kroner Machado Costa</td>
									<td>029.808.481-31</td>
									<td>62999181775</td>
									<td>GOIÂNIA-GO</td>
									<td><a href=""><i class="iconify" data-icon="ic-outline-dashboard"></i> PAINEL</a></td>
									<td>
										<a href="modelo-form.php" class="registros__acao"><i class="iconify" data-icon="bx:bxs-edit-alt"></i></a>
										<a href="" class="registros__acao registros__acao_sec"><i class="iconify" data-icon="bx:bxs-trash"></i></a>
									</td>
								</tr>
								<tr>
									<td>24/06/2020 13:27</td>
									<td>Kroner Machado Costa</td>
									<td>029.808.481-31</td>
									<td>62999181775</td>
									<td>GOIÂNIA-GO</td>
									<td><a href=""><i class="iconify" data-icon="ic-outline-dashboard"></i> PAINEL</a></td>
									<td>
										<a href="modelo-form.php" class="registros__acao"><i class="iconify" data-icon="bx:bxs-edit-alt"></i></a>
										<a href="" class="registros__acao registros__acao_sec"><i class="iconify" data-icon="bx:bxs-trash"></i></a>
									</td>
								</tr>
								
							</tbody>
						</table>
					</div>
					<div class="paginacao">
						<p class="paginacao__item"><span>Página</span><a href="" class="active">1</a><a href="">2</a><a href="">3</a>
					</div>
				</section>
			</section>

			

			<section class="content-grid content-grid_3 content-grid_box">
				<section class="content__item">
					<h1 class="content__titulo2">Grid</h1>
					<script>
					$(function(){
						var ctx = document.getElementById('grafico1').getContext('2d');
						var grafico1 = new Chart(ctx, {    
						    type: 'pie',
						    data: {
						        labels: ["Simples","Batata Frita","Coca-Cola","Retrô 200g"],
						        datasets: [{
						            fill:true,
						            borderDashOffset: 0.0,
						            label: '# visitas',
						            data: [50,120,110,90],
						            backgroundColor: [
												window.chartColors.red,
												window.chartColors.orange,
												window.chartColors.yellow,
												window.chartColors.green,
												window.chartColors.blue,
											],
						            borderColor:'transparent',
						            borderWidth: 1,
						            borderDash: [],
						            borderDashOffset: 0.0
						        }]
						    },
						});		
					});				
					</script>
					<div class="grafico">
						<canvas id="grafico1" width="300px" height="200px"></canvas>
					</div>
				</section>
				<section class="content__item">
					<h1 class="content__titulo2">Grid</h1>
					<script>
					$(function() {
						var ctx = document.getElementById('grafico2').getContext('2d');
						var gradientStroke = ctx.createLinearGradient(0,230,0,50);
						gradientStroke.addColorStop(1, 'rgba(254,71,2,0.2)');
						gradientStroke.addColorStop(0.8, 'rgba(254,71,2,0.1)');
						gradientStroke.addColorStop(0, 'rgba(254,71,2,0)');
						var grafico2 = new Chart(ctx, {    
						    type: 'line',
						    data: {
						        labels: ["11","12","13","14","15","16","17"],
						        datasets: [{
						            fill:true,
						            borderDashOffset: 0.0,
						            label: '# visitas',
						            data: [1200,1100,1300,1300,500,1200,1345],
						            backgroundColor: gradientStroke,
						            borderColor:'rgba(254,71,2,0.3)',
						            borderWidth: 1,
						            borderDash: [],
						            borderDashOffset: 0.0
						        }]
						    },
						    options: {
						        scales: {
						            yAxes: [{
						                ticks: {
						                    beginAtZero: true
						                },
						                gridLines: {
						                	drawBorder: false,
						                	color: 'transparent'
						                }
						            }],
						            xAxes: [{
							            gridLines: {
							            	drawBorder: false,
							                color: '#ebebeb',
							                zeroLineColor: "#ebebeb"
							            }	              
							        }]
						        }
						    }
						})
					});
					</script>
					<div class="grafico">
						<canvas id="grafico2" width="300px" height="200px"></canvas>
					</div>
				</section>
				<section class="content__item">
					<h1 class="content__titulo2">Grid</h1>
					<script>
					$(function() {
						var ctx = document.getElementById('grafico3').getContext('2d');
						var gradientStroke = ctx.createLinearGradient(0,230,0,50);
						gradientStroke.addColorStop(1, 'rgba(254,71,2,0.2)');
						gradientStroke.addColorStop(0.8, 'rgba(254,71,2,0.1)');
						gradientStroke.addColorStop(0, 'rgba(254,71,2,0)');
						var grafico3 = new Chart(ctx, {    
						    type: 'bar',
						    data: {
						        labels: ["11","12","13","14","15","16","17"],
						        datasets: [{
						            fill:true,
						            borderDashOffset: 0.0,
						            label: '# visitas',
						            data: [1200,1100,1300,1300,500,1200,1345],
						            backgroundColor: gradientStroke,
						            borderColor:'rgba(254,71,2,0.3)',
						            borderWidth: 1,
						            borderDash: [],
						            borderDashOffset: 0.0
						        }]
						    },
						    options: {
						        scales: {
						            yAxes: [{
						                ticks: {
						                    beginAtZero: true
						                },
						                gridLines: {
						                	drawBorder: false,
						                	color: 'transparent'
						                }
						            }],
						            xAxes: [{
							            gridLines: {
							            	drawBorder: false,
							                color: '#ebebeb',
							                zeroLineColor: "#ebebeb"
							            }	              
							        }]
						        }
						    }
						})
					});
					</script>
					<div class="grafico">
						<canvas id="grafico3" width="300px" height="200px"></canvas>
					</div>
				</section>
				<section class="content__item" style="grid-column:span 2">
					<h1 class="content__titulo2">Grid (grid-column:span 2)</h1>
				</section>
				<section class="content__item">
					<h1 class="content__titulo2">Grid</h1>
				</section>
			</section>
		</section>
	
<?php
include "includes/footer.php";
?>