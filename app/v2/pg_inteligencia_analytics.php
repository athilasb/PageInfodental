<?php
	include "includes/header.php";
	include "includes/nav.php";

	$_table=$_p."colaboradores";


	$_profissionais=array();
	$sql->consult($_p."colaboradores","id,nome,calendario_iniciais,foto,calendario_cor,check_agendamento","where lixo=0 order by nome asc");
	while($x=mysqli_fetch_object($sql->mysqry)) $_profissionais[$x->id]=$x;

	$_cadeiras=array();
	$sql->consult($_p."parametros_cadeiras","*","where lixo=0  order by titulo asc");
	while($x=mysqli_fetch_object($sql->mysqry)) $_cadeiras[$x->id]=$x;

	$_status=array();
	$sql->consult($_p."agenda_status","*","where lixo=0 order by kanban_ordem asc");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_status[$x->id]=$x;
	}

	$_cadeiras=array();
	$sql->consult($_p."parametros_cadeiras","id,titulo","where lixo=0 order by ordem asc");
	while($x=mysqli_fetch_object($sql->mysqry)) $_cadeiras[$x->id]=$x;

	


	
	$filtro="7";
	$dataAte=date('Y-m-d');
	$dataDe=date('Y-m-d',strtotime(date('Y-m-d')." - 7 days"));

	if(isset($_GET['filtro'])) {
		if($_GET['filtro']=="30") {
			$filtro=30;
			$dataDe=date('Y-m-d',strtotime(date('Y-m-d')." - 30 days"));
		} else if($_GET['filtro']=="60") {
			$filtro=60;
			$dataDe=date('Y-m-d',strtotime(date('Y-m-d')." - 60 days"));
		} if($_GET['filtro']=="90") {
			$filtro=90;
			$dataDe=date('Y-m-d',strtotime(date('Y-m-d')." - 90 days"));
		} else if($_GET['filtro']=="ano") {
			$filtro="ano";
			$dataDe=date('Y-m-d',strtotime(date('Y-m-d')." - 1 year"));
		}
	}
	$data=date('Y-m-d');


	$where="where agenda_data>='".$dataDe." 00:00:00' and agenda_data<='".$dataAte." 23:59:59' and agendaPessoal=0";

	echo $where;
	$sql->consult($_p."agenda","id,id_status,agenda_duracao",$where);
	$agendamentos=$sql->rows;
	$agendamentosAtendidos=$agendamentosDesmarcados=$agendamentosFaltou=$agendamentosAtendidosDuracao=0;
	while($x=mysqli_fetch_object($sql->mysqry)) {
		if($x->id_status==5) {
			$agendamentosAtendidos++;
			$agendamentosAtendidosDuracao+=$x->agenda_duracao;
		} else if($x->id_status==4) $agendamentosDesmarcados++;
		else if($x->id_status==3) $agendamentosFaltou++;
	}

	if($agendamentosAtendidosDuracao>0) $agendamentosAtendidosDuracao*=60;


	
?>

	<header class="header">
		<div class="header__content content">

			<div class="header__inner1">
				<section class="header-title">
					<h1>Tarefas Inteligentes</h1>
				</section>
				<?php
				require_once("includes/menus/menuInteligencia.php");
				?>


			</div>
			<div class="header__inner2">
				<section class="header-date">
					<div class="header-date-buttons"></div>
					<div class="header-date-now">
						<h1 class="js-cal-titulo-diames"><?php echo date('d',strtotime($data));?></h1>
						<h2 class="js-cal-titulo-mes"><?php echo substr(strtolower(mes(date('m',strtotime($data)))),0,3);?></h2>
						até
						<h1 class="js-cal-titulo-diames"><?php echo date('d',strtotime($data));?></h1>
						<h2 class="js-cal-titulo-mes"><?php echo substr(strtolower(mes(date('m',strtotime($data)))),0,3);?></h2>
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

						<a href="<?php echo "$_page?filtro=7";?>" class="button<?php echo $filtro=="7"?" active":"";?>">7 dias</a>
						<a href="<?php echo "$_page?filtro=30";?>" class="button<?php echo $filtro=="30"?" active":"";?>">30 dias</a>
						<a href="<?php echo "$_page?filtro=60";?>" class="button<?php echo $filtro=="60"?" active":"";?>">60 dias</a>
						<a href="<?php echo "$_page?filtro=90";?>" class="button<?php echo $filtro=="90"?" active":"";?>">90 dias</a>
						<a href="<?php echo "$_page?filtro=ano";?>" class="button<?php echo $filtro=="ano"?" active":"";?>">ano</a>
					</div>
				</div>
			</section>

		

			<div class="grid">
				<section class="box">

					<div class="list4">
						<a href="" class="list4-item">
							<div>
								<h1><?php echo sec_convertOriginal($agendamentosAtendidosDuracao,'HF');?></h1>
							</div>
							<div>
								<p>Horas de Atendimento</p>
							</div>
						</a>
						<a href="" class="list4-item">
							<div>
								<h1><i class="iconify" data-icon="fluent:calendar-ltr-24-regular"></i> <?php echo number_format($agendamentos,0,"",".");?></h1>
							</div>
							<div>
								<p>Agendamentos</p>
							</div>
						</a>
						<a href="" class="list4-item">
							<div>
								<h1><i class="iconify" data-icon="fluent:calendar-checkmark-24-regular"></i> <?php echo number_format($agendamentosAtendidos,0,"",".");?></h1>
							</div>
							<div>
								<p>Atendidos</p>
							</div>
						</a>
						<a href="" class="list4-item">
							<div>
								<h1><i class="iconify" data-icon="fluent:calendar-sync-24-regular"></i> <?php echo number_format($agendamentosDesmarcados,0,"",".");?></h1>
							</div>
							<div>
								<p>Desmarcados</p>
							</div>
						</a>
						<a href="" class="list4-item">
							<div>
								<h1><i class="iconify" data-icon="fluent:calendar-cancel-24-regular"></i> <?php echo number_format($agendamentosFaltou,0,"",".");?></h1>
							</div>
							<div>
								<p>	Faltou</p>
							</div>
						</a>
					</div>

					<div class="grid grid_3" style="margin:0;">						
						<div class="box" style="grid-column:span 2">

							<div class="filter">
								<div class="filter-group">
									<div class="filter-title">
										<h1>Atendimento por Dentista</h1>
									</div>
								</div>						
							</div>

							<div style="width:100%; height:350px;">
								<script>
								$(function() {
									$('.js-calendario').daterangepicker();

									var ctx = document.getElementById('grafico1').getContext('2d');
									var grafico1 = new Chart(ctx, {    
									    type: 'bar',
									    data: {
									        labels: ["Joao","Pedro","Luciano","Kroner"],
									        datasets: [{
									            fill:true,
									            borderDashOffset: 0.0,
									            label: 'Pacientes',
									            data: [43,44,30,32],
									            backgroundColor: '#ddd',
									            borderColor:'transparent',
									            borderWidth: 1,
									            borderDash: [],
									            borderDashOffset: 0.0
									        }]
									    },
									    options: {
									    	responsive:true,
											maintainAspectRatio: false,
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
									});
								});
								</script>
								<canvas id="grafico1" class="box-grafico"></canvas>
							</div>

						</div>

						<div class="box">
							<div class="filter">
								<div class="filter-group">
									<div class="filter-title">
										<h1>Atendimentos por Horário</h1>
									</div>
								</div>						
							</div>

							<div style="width:100%; height:350px;">
								<script>
								$(function() {
									var ctx = document.getElementById('grafico2').getContext('2d');
									var grafico2 = new Chart(ctx, {    
									    type: 'line',
									    data: {
									        labels: ["8h","9h","10h","11h","12h","13h","14h","15h","16h","17h","18h"],
									        datasets: [{
									            fill:true,
									            label: 'Pacientes',
									            data: [43,44,30,32,10,9,52,50,30,37,19],
									            backgroundColor: 'transparent',
									            borderColor:'gray',
									            borderWidth: 1,
									        }]
									    },
									    options: {
									    	legend: {
								    			display:false								    			
								    		},
									    	responsive:true,
											maintainAspectRatio: false,
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
									});
								});
								</script>
								<canvas id="grafico2" class="box-grafico"></canvas>
							</div>
						</div>
					</div>
				</section>
			</div>
	

			
		</div>
	</main>

	

<?php 
	


	$apiConfig=array('pacienteRelacionamento'=>1);
	require_once("includes/api/apiAside.php");

	include "includes/footer.php";
?>	