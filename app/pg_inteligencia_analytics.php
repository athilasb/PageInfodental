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
	$dataDe=date('Y-m-d',strtotime(date('Y-m-d')." - 7 days"));
	$dataAte=date('Y-m-d');

	if(isset($_GET['data_inicio']) and !empty($_GET['data_inicio']) and isset($_GET['data_fim']) and !empty($_GET['data_fim'])) {
		$filtro='';
		$dataDe=$_GET['data_inicio'];
		$dataAte=$_GET['data_fim'];
	}

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

	$analyticsTipos=explode(",","horas,agendamentos,atendidos,desmarcados,faltou");
	$analyticsTiposTitulo=array("horas"=>"Horas de Atendimento",
								"agendamentos"=>"Agendamentos",
								"atendidos"=>"Atendidos",
								"desmarcados"=>"Desmarcados",
								"faltou"=>"Faltou");

	$analyticsTipo="horas";
	if(isset($_GET['analyticsTipo'])) {
		if(in_array($_GET['analyticsTipo'],$analyticsTipos)) $analyticsTipo=$_GET['analyticsTipo'];
	}

	$labelTitulo=$analyticsTiposTitulo[$analyticsTipo];

	$dias = strtotime($dataAte) - strtotime($dataDe);
	$dias /= (60*60*24);;
	$dias = round($dias);
	$dt = $dataDe;

	if($dias<=31) {
		$agruparPor="dia";
		do {
			$label[]=date('d/m',strtotime($dt));
			$dt = date('Y-m-d',strtotime($dt." + 1 day"));
		} while(strtotime($dt)<=strtotime($dataAte));

	} else {
		$agruparPor="mes";

		do {
			$label[]=strtolower(substr(mes(date('m',strtotime($dt))),0,3))."/".date('y',strtotime($dt));
			$dt = date('Y-m-d',strtotime($dt." + 1 month"));
		} while(strtotime($dt)<=strtotime($dataAte));
	}


	$where="where agenda_data>='".$dataDe." 00:00:00' and agenda_data<='".$dataAte." 23:59:59' and agendaPessoal=0 and lixo=0";


	$labelInfos=array();
	$sql->consult($_p."agenda","id,id_status,agenda_duracao,agenda_data",$where);
	$agendamentos=$sql->rows;
	$agendamentosAtendidos=$agendamentosDesmarcados=$agendamentosFaltou=$agendamentosAtendidosDuracao=0;
	while($x=mysqli_fetch_object($sql->mysqry)) {
		if($x->id_status==5) {
			$agendamentosAtendidos++;
			$agendamentosAtendidosDuracao+=($x->agenda_duracao/60);
			$agendamentosAtendidosDuracao=round($agendamentosAtendidosDuracao);
		} else if($x->id_status==4) $agendamentosDesmarcados++;
		else if($x->id_status==3) $agendamentosFaltou++;

		//echo $x->agenda_data."\n<BR>";

		if($analyticsTipo=="horas") {
			if($x->id_status==5) {
				if($agruparPor=="dia") {
					$index=date('d/m',strtotime($x->agenda_data));
				} else {
					$index=strtolower(substr(mes(date('m',strtotime($x->agenda_data))),0,3))."/".date('y',strtotime($x->agenda_data));
				}
				if(!isset($labelInfos[$index])) $labelInfos[$index]=0;
				$labelInfos[$index]+=($x->agenda_duracao/60);
				$labelInfos[$index]=round($labelInfos[$index]);
				
			}
		} else if($analyticsTipo=="agendamentos") {
			
			if($agruparPor=="dia") {
				$index=date('d/m',strtotime($x->agenda_data));
			} else {
				$index=strtolower(substr(mes(date('m',strtotime($x->agenda_data))),0,3))."/".date('y',strtotime($x->agenda_data));
			}
			if(!isset($labelInfos[$index])) $labelInfos[$index]=0;
			$labelInfos[$index]++;
		} else if($analyticsTipo=="atendidos") {
			if($x->id_status==5) {
				if($agruparPor=="dia") {
					$index=date('d/m',strtotime($x->agenda_data));
				} else {
					$index=strtolower(substr(mes(date('m',strtotime($x->agenda_data))),0,3))."/".date('y',strtotime($x->agenda_data));
				}
				if(!isset($labelInfos[$index])) $labelInfos[$index]=0;
				$labelInfos[$index]++;
			}
		} else if($analyticsTipo=="desmarcados") {
			if($x->id_status==4) {
				if($agruparPor=="dia") {
					$index=date('d/m',strtotime($x->agenda_data));
				} else {
					$index=strtolower(substr(mes(date('m',strtotime($x->agenda_data))),0,3))."/".date('y',strtotime($x->agenda_data));
				}
				if(!isset($labelInfos[$index])) $labelInfos[$index]=0;
				$labelInfos[$index]++;
			}
		} else if($analyticsTipo=="faltou") {
			if($x->id_status==3) {
				if($agruparPor=="dia") {
					$index=date('d/m',strtotime($x->agenda_data));
				} else {
					$index=strtolower(substr(mes(date('m',strtotime($x->agenda_data))),0,3))."/".date('y',strtotime($x->agenda_data));
				}
				if(!isset($labelInfos[$index])) $labelInfos[$index]=0;
				$labelInfos[$index]++;
			}
		}

	}



	$labelInfosJSON=array();
	foreach($label as $v) {
		$labelInfosJSON[]=isset($labelInfos[$v])?round(($labelInfos[$v])):0;
	}

	//if($agendamentosAtendidosDuracao>0) $agendamentosAtendidosDuracao*=60;

	


	
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
						<h1 class="js-cal-titulo-diames"><?php echo date('d',strtotime($dataDe));?></h1>
						<h2 class="js-cal-titulo-mes"><?php echo substr(strtolower(mes(date('m',strtotime($dataDe)))),0,3);?>/<?php echo substr(strtolower((date('Y',strtotime($dataDe)))),2,2);?></h2>
						até
						<h1 class="js-cal-titulo-diames"><?php echo date('d',strtotime($dataAte));?></h1>
						<h2 class="js-cal-titulo-mes"><?php echo substr(strtolower(mes(date('m',strtotime($dataAte)))),0,3);?>/<?php echo substr(strtolower((date('Y',strtotime($dataAte)))),2,2);?></h2>
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
						<a href="<?php echo "$_page?filtro=7&analyticsTipo=$analyticsTipo";?>" class="button<?php echo $filtro=="7"?" active":"";?>">7 dias</a>
						<a href="<?php echo "$_page?filtro=30&analyticsTipo=$analyticsTipo";?>" class="button<?php echo $filtro=="30"?" active":"";?>">30 dias</a>
						<a href="<?php echo "$_page?filtro=60&analyticsTipo=$analyticsTipo";?>" class="button<?php echo $filtro=="60"?" active":"";?>">60 dias</a>
						<a href="<?php echo "$_page?filtro=90&analyticsTipo=$analyticsTipo";?>" class="button<?php echo $filtro=="90"?" active":"";?>">90 dias</a>
						<a href="<?php echo "$_page?filtro=ano&analyticsTipo=$analyticsTipo";?>" class="button<?php echo $filtro=="ano"?" active":"";?>">ano</a>
					</div>
				</div>
			</section>

		

			<div class="grid">
				<section class="box">

					<div class="list4">
						<a href="<?php echo "$_page?analyticsTipo=hora&$url";?>" class="list4-item<?php echo $analyticsTipo=="horas"?" active":"";?>">
							<div>
								<h1><?php echo number_format($agendamentosAtendidosDuracao,0,"",".");?></h1>
							</div>
							<div>
								<p>Horas de Atendimento</p>
							</div>
						</a>
						<a href="<?php echo "$_page?analyticsTipo=agendamentos&$url";?>" class="list4-item<?php echo $analyticsTipo=="agendamentos"?" active":"";?>">
							<div>
								<h1><i class="iconify" data-icon="fluent:calendar-ltr-24-regular"></i> <?php echo number_format($agendamentos,0,"",".");?></h1>
							</div>
							<div>
								<p>Agendamentos</p>
							</div>
						</a>
						<a href="<?php echo "$_page?analyticsTipo=atendidos&$url";?>" class="list4-item<?php echo $analyticsTipo=="atendidos"?" active":"";?>">
							<div>
								<h1><i class="iconify" data-icon="fluent:calendar-checkmark-24-regular"></i> <?php echo number_format($agendamentosAtendidos,0,"",".");?></h1>
							</div>
							<div>
								<p>Atendidos</p>
							</div>
						</a>
						<a href="<?php echo "$_page?analyticsTipo=desmarcados&$url";?>" class="list4-item<?php echo $analyticsTipo=="desmarcados"?" active":"";?>">
							<div>
								<h1><i class="iconify" data-icon="fluent:calendar-sync-24-regular"></i> <?php echo number_format($agendamentosDesmarcados,0,"",".");?></h1>
							</div>
							<div>
								<p>Desmarcados</p>
							</div>
						</a>
						<a href="<?php echo "$_page?analyticsTipo=faltou&$url";?>" class="list4-item<?php echo $analyticsTipo=="faltou"?" active":"";?>">
							<div>
								<h1><i class="iconify" data-icon="fluent:calendar-cancel-24-regular"></i> <?php echo number_format($agendamentosFaltou,0,"",".");?></h1>
							</div>
							<div>
								<p>	Faltou</p>
							</div>
						</a>
					</div>

					<div class="grid grid_3" style="margin:0;">						
						<div class="box" style="grid-column:span 3">

							<div class="filter">
								<div class="filter-group">
									<div class="filter-title">
										<h1><?php echo $labelTitulo;?></h1>
									</div>
								</div>						
							</div>

							<div style="width:100%; height:350px;">
								<script>
								$(function() {
									$('.js-calendario').daterangepicker({
										"autoApply":true,
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


									$('.js-calendario').on('apply.daterangepicker',function(ev,picker) {
										let dtFim = picker.endDate.format('YYYY-MM-DD');
										let dtInicio = picker.startDate.format('YYYY-MM-DD');
										document.location.href=`<?php echo "$_page?analyticsTipo=$analyticsTipo";?>&data_inicio=${dtInicio}&data_fim=${dtFim}`
										
									});

									var ctx = document.getElementById('grafico1').getContext('2d');
									var grafico1 = new Chart(ctx, {    
									    type: 'line',
									    data: {
									        labels:  <?php echo json_encode($label);?>,
									        datasets: [{
									            fill:true,
									            borderDashOffset: 0.0,
									            label: '<?php echo $labelTitulo;?>',
									            data: <?php echo json_encode($labelInfosJSON);?>,
									            backgroundColor: 'transparent',
									            borderColor:'gray',
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

						<?php /*<div class="box">
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
						</div>*/?>
					</div>
				</section>
			</div>

			<div class="grid">	
				<section class="box">

					<?php

					// calcula horas do dia de cada cadeira
					$dataDia = date('w',strtotime($data));

					$_horas = array();
					$_horasMes = array();
					$sql->consult($_p."parametros_cadeiras_horarios","*","where lixo=0");
					if($sql->rows) {
						while($x=mysqli_fetch_object($sql->mysqry)) {

							$dif = (strtotime($x->fim)-strtotime($x->inicio))/(60);

							if(!isset($_horasMes[$x->id_cadeira][$x->dia])) $_horasMes[$x->id_cadeira][$x->dia]=0;
							$_horasMes[$x->id_cadeira][$x->dia]+=$dif;


							if($dataDia==$x->dia) {
								if(!isset($_horas[$x->id_cadeira])) $_horas[$x->id_cadeira]=0;
								$_horas[$x->id_cadeira]+=$dif;
							}

						}
					}


					$_agendaHoras = array();
					$_agendaHorasMes = array();

					$sql->consult($_p."agenda","id,id_cadeira,agenda_data,agenda_duracao","where agenda_data>='".date('Y-m-01')." 00:00:00' and agenda_data<='".date('Y-m-t')." 23:59:59' and id_status IN (1,2,5) and lixo=0");
					while($x=mysqli_fetch_object($sql->mysqry)) {

							$dia = date('d',strtotime($x->agenda_data));

							if(!isset($_agendaHorasMes[$x->id_cadeira][$dia])) $_agendaHorasMes[$x->id_cadeira][$dia]=0;
							$_agendaHorasMes[$x->id_cadeira][$dia]+=$x->agenda_duracao;

							if(date('Y-m-d',strtotime($x->agenda_data))==$data) {
								if(!isset($_agendaHoras[$x->id_cadeira])) $_agendaHoras[$x->id_cadeira]=0;
								$_agendaHoras[$x->id_cadeira]+=$x->agenda_duracao;
							}
					}
					
						$dias=array();
						for($i=1;$i<=date('t');$i++) {
							$dias[]=$i;
						}

						$cores=array('blue','green','brown','orange','purple');
						//echo json_encode($_agendaHorasMes[1]["05"]);
						$graficoData = array();
						foreach($_cadeiras as $x) {
							//echo $x->titulo."<BR>";
							for($i=1;$i<=date('t');$i++) {
								if(!isset($graficoData[$x->id])) $graficoData[$x->id]=array();

								$diaSemana=date('w',strtotime(date('Y-m-'.$i)));
								if(isset($_horasMes[$x->id][$diaSemana])) {
									$horasDisp=$_horasMes[$x->id][$diaSemana];

									//echo $diaSemana."->".$horasDisp."<BR>";
									$ocupacao=isset($_agendaHorasMes[$x->id][d2($i)])?$_agendaHorasMes[$x->id][d2($i)]:0;
									
									$tx = ceil(($ocupacao/$horasDisp)*100);

									//echo $i." = ".$diaSemana.": ".$ocupacao."/".$horasDisp." = ".$tx."<Br>";;

									$graficoData[$x->id][]=$tx;
								} else {

									$graficoData[$x->id][]=0;
								}
							}
						}


						$graficoCadeiras=array();
						$graficoDentistas=array();
						$aux=0;
						foreach($_cadeiras as $c) {
							$graficoCadeiras[]=array('fill'=>true,
														'label'=>utf8_encode($c->titulo),
														'data'=>$graficoData[$c->id],
														'backgroundColor'=>'transparent',
														'borderColor'=>$cores[$aux++],
														'borderWidth'=>1);
							$graficoDentistas[]=array('fill'=>true,
														'label'=>utf8_encode($c->titulo),
														'data'=>array(rand(1,10),rand(1,100),rand(1,100)),
														'backgroundColor'=>'transparent',
														'borderColor'=>'gray',
														'borderWidth'=>1);
						
						}

					?>
					<script>
					$(function() {

						$('.js-btn-grafico').click(function(){

							let tipo = $(this).attr('data-tipo');

							$('.box-grafico').hide();

							$('.js-btn-grafico').removeClass('active');
							$(this).addClass('active');

							if(tipo=="dentistas") {
								$('#grafico-dentistas').show();
							} else {
								$('#grafico-cadeiras').show();
							}
						})

						var ctx = document.getElementById('grafico-cadeiras').getContext('2d');
						var graficoCadeiras = new Chart(ctx, {    
						    type: 'line',
						    data: {
						        labels:  <?php echo json_encode($dias);?>,
						        datasets: <?php echo json_encode($graficoCadeiras);?>
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


						var ctx = document.getElementById('grafico-dentistas').getContext('2d');
						var graficoDentistas = new Chart(ctx, {    
						    type: 'line',
						    data: {
						        
						        labels:  <?php echo json_encode($dias);?>,
						        datasets: <?php echo json_encode($graficoDentistas);?>
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
					<canvas id="grafico-cadeiras" class="box-grafico"></canvas>
					<canvas id="grafico-dentistas" class="box-grafico" style="display:none;"></canvas>
				</section>
			</div>
	

			
		</div>
	</main>

	

<?php 
	


	$apiConfig=array('pacienteRelacionamento'=>1);
	require_once("includes/api/apiAside.php");

	include "includes/footer.php";
?>	