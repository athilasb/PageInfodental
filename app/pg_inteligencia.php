<?php
	if(isset($_POST['ajax'])) {

		require_once("lib/conf.php");
		require_once("usuarios/checa.php");

		$rtn = array();

		if($_POST['ajax']=="atualizaListaInteligente") {

			$_profissionais=array();
			$sql->consult($_p."colaboradores","id,nome,calendario_iniciais,foto,calendario_cor,check_agendamento","where lixo=0 order by nome asc");
			while($x=mysqli_fetch_object($sql->mysqry)) $_profissionais[$x->id]=$x;

			$_historicoStatus=array();	
			$sql->consult($_p."pacientes_historico_status","*","");
			while($x=mysqli_fetch_object($sql->mysqry)) {
				$_historicoStatus[$x->id]=$x;
			}

			$_pacientesExcluidos=array();
			$pacientesIds=array();
			$atendidosPacientesIds=array();
			$desmarcadosPacientesIds=array();

			$sql->consult($_p."pacientes_excluidos","*","where lixo=0");
			while($x=mysqli_fetch_object($sql->mysqry)) {
				$_pacientesExcluidos[$x->id_paciente]=$x;
				$pacientesIds[]=$x->id_paciente;
				$atendidosPacientesIds[]=$x->id_paciente;
				$desmarcadosPacientesIds[]=$x->id_paciente;
			}


			$_pacientesExcluidosObj=array();
			$_pacientesExcluidosLista=array();
			
			if(count($pacientesIds)>0) {
				$sql->consult($_p."pacientes","id,telefone1,foto_cn,nome","where id IN (".implode(",",$pacientesIds).")");
				while($x=mysqli_fetch_object($sql->mysqry)) {
					$_pacientesExcluidosObj[$x->id]=$x;
				}

				
			}

			# Sugestoes sem BI

				$listaUnicaJSON=array();

				# Desmarcados sem agendamentos

					$desmarcadosPacientesAgenda=array();
					$pacientesTodosIds=array();

					// busca pacientes desmarcados nos ultimos 360 dias 
					$sql->consult($_p."agenda","id,id_paciente,agenda_data","WHERE data > NOW() - INTERVAL 360 DAY and id_status=4 and lixo=0 order by agenda_data desc");
					if($sql->rows) {
						while($x=mysqli_fetch_object($sql->mysqry)) {
							if(isset($_pacientesExcluidos[$x->id_paciente])) {
								$_pacientesExcluidosLista[$x->id_paciente]='Desmarcado';
								continue;
							}

							$desmarcadosPacientesIds[$x->id_paciente]=$x->id_paciente;

							// capta apenas o ultimo desmarcado
							if(!isset($desmarcadosPacientesAgenda[$x->id_paciente])) {
								$desmarcadosPacientesAgenda[$x->id_paciente]=$x;
							}
						}
					}

					$desmarcadosPacientesAgendaJSON=array();
					// busca agendamentos confirmados, a confirmar ou atendidos dos pacientes que foram desmarcados nos ultimos 360 dias
					if(count($desmarcadosPacientesIds)>0) {
						$sql->consult($_p."agenda","id,id_paciente,agenda_data","WHERE data > NOW() - INTERVAL 360 DAY and id_paciente IN (".implode(",",$desmarcadosPacientesIds).") and id_status IN (1,2,5) and lixo=0 order by agenda_data desc");
						while($x=mysqli_fetch_object($sql->mysqry)) {

							if(isset($desmarcadosPacientesAgenda[$x->id_paciente])) {

								// ultimo agendamento desmarcado
								$ultimoAgendamentoDesmarcado = $desmarcadosPacientesAgenda[$x->id_paciente];


								// se o ultimo agendamento desmarcado for menor que o ultimo agendamento confirmado, a confirmado ou atendido, remove da lista
								$removerDaLista = (strtotime($ultimoAgendamentoDesmarcado->agenda_data)<strtotime($x->agenda_data))?1:0;
								if($removerDaLista==1) {
									unset($desmarcadosPacientesAgenda[$x->id_paciente]);
								} else {
									$pacientesTodosIds[$x->id_paciente]=$x->id_paciente;
								}
							}

						}

						// busca historico
						$sql->consult($_p."pacientes_historico","*","where id_paciente IN (".implode(",",$desmarcadosPacientesIds).") and evento='observacao' order by data desc");
						while($x=mysqli_fetch_object($sql->mysqry)) {
							if(!isset($_pacientesStatus[$x->id_paciente])) {
								$_pacientesStatus[$x->id_paciente]=$x->id_obs;
							}
						}

						// busca pacientes que foram desmarcados
						$_pacientesDesmarcados=array();
						$sql->consult($_p."pacientes","id,nome,telefone1,foto_cn,profissional_maisAtende","where id IN (".implode(",",$desmarcadosPacientesIds).") and lixo=0");
						while ($x=mysqli_fetch_object($sql->mysqry)) {
							$_pacientesDesmarcados[$x->id]=$x;
						}

						// pacientes que foram desmarcados e nao tiveram outro agendamento confirmado, a confirmar ou atendido
						$cont=1;
						foreach($desmarcadosPacientesAgenda as $v) {
							if(isset($_pacientesDesmarcados[$v->id_paciente])) {
								$paciente=$_pacientesDesmarcados[$v->id_paciente];
								//echo $v->id_paciente." ".$_pacientesDesmarcados[$v->id_paciente]->nome."<BR>";
								//$nome=$cont++." - ".utf8_encode($paciente->nome);
								$nome=utf8_encode($paciente->nome);

								$ft='';
								if(!empty($paciente->foto_cn)) $ft=$_cloudinaryURL.'c_thumb,w_100,h_100/'.$paciente->foto_cn;

								$status=isset($_pacientesStatus[$paciente->id])?$_pacientesStatus[$paciente->id]:0;
								//$desmarcadosPacientesAgendaJSON[]
								$listaUnicaJSON[]=array('id_paciente'=>$paciente->id,
														'profissional'=>$paciente->profissional_maisAtende,
														'nome'=>$nome,
														'ft'=>$ft,
														'status'=>$status,
														'telefone'=>empty($paciente->telefone1)?"":maskTelefone($paciente->telefone1));
							}
						}
					}


				# Pacientes contencao sem horario

					$atendidosPacientesAgenda=array();
					$atendidosPacientesVezes=array();


					// busca os agendamentos dos ultimos 720 dias com status atendido
					$sql->consult($_p."agenda","id,id_paciente,agenda_data","WHERE data > NOW() - INTERVAL 720 DAY and id_status=5 and lixo=0 order by agenda_data desc");
					while($x=mysqli_fetch_object($sql->mysqry)) {
						if(isset($_pacientesExcluidos[$x->id_paciente])) {
							$_pacientesExcluidosLista[$x->id_paciente]='Retorno';
							continue;
						}

						if(!isset($atendidosPacientesAgenda[$x->id_paciente])) {

							if(!isset($atendidosPacientesVezes[$x->id_paciente])) $atendidosPacientesVezes[$x->id_paciente]=0;
							$atendidosPacientesVezes[$x->id_paciente]++;

							if($atendidosPacientesVezes[$x->id_paciente]>=3) {
								$atendidosPacientesAgenda[$x->id_paciente]=$x;
								$atendidosPacientesIds[]=$x->id_paciente;
							}
						}
					}


					$retornoPacientesAgendaJSON=array();
					if(count($atendidosPacientesIds)>0) {


						// busca pacientes que foram atendidos nos ultimos 720 dias
						$_pacientesAtendidosIds=array();
						$_pacientesAtendidos=array();
						$sql->consult($_p."pacientes","id,nome,telefone1,periodicidade,profissional_maisAtende","where id IN (".implode(",",$atendidosPacientesIds).") and lixo=0 order by nome");
						while ($x=mysqli_fetch_object($sql->mysqry)) {
							if(isset($_pacientesPeriodicidade[$x->periodicidade])) {
								$_pacientesAtendidosIds[$x->periodicidade][$x->id]=$x->id;
								$_pacientesAtendidos[$x->id]=$x;
							}
						}

						// busca historico
						$sql->consult($_p."pacientes_historico","*","where id_paciente IN (".implode(",",$atendidosPacientesIds).") and evento='observacao' order by data desc");
						while($x=mysqli_fetch_object($sql->mysqry)) {
							if(!isset($_pacientesStatus[$x->id_paciente])) {
								$_pacientesStatus[$x->id_paciente]=$x->id_obs;
							}
						}

						// cria o array que restaram os pacientes que necessitam de retorno
						$_pacientesAtendidosIdsResto = $_pacientesAtendidosIds;

						$_pacientesAtendidosUltimoAgendamento = array();

						// roda todos os pacientes atendidos por periodicidade
						foreach($_pacientesAtendidosIds as $periodicidade=>$pacientesIds) {

							// busca agendamentos dos pacientes da periodicidade que foram atendidos e nao necessitam de retorno
							$sql->consult($_p."agenda","id,id_paciente,agenda_data","WHERE data > NOW() - INTERVAL $periodicidade MONTH and id_status IN (5,1,2) and id_paciente IN (".implode(",",$pacientesIds).") and lixo=0 order by agenda_data desc");
							while($x=mysqli_fetch_object($sql->mysqry)) {
								// remove da lista de pacientes que necessitam de retorno
								unset($_pacientesAtendidosIdsResto[$periodicidade][$x->id_paciente]);
							}
						}



						$retornoPacientesAgendaJSONAux=array();
						// monta a lista dos pacientes que necessitam de retorno
						foreach($_pacientesAtendidosIdsResto as $periodicidade=>$pacientes) {
					
							foreach($pacientes as $idPaciente) {

								// se nao estiver na lista de desmarcados (desmarcadosPacientesIds);
								if(isset($_pacientesAtendidos[$idPaciente]) and !isset($desmarcadosPacientesIds[$idPaciente])) {
									$paciente=$_pacientesAtendidos[$idPaciente];
									$nome=utf8_encode($paciente->nome);

									
									// ultimo agendamento 
									$ultimoAtendimento='';
									
									if(isset($atendidosPacientesAgenda[$paciente->id])) {
										$u=$atendidosPacientesAgenda[$paciente->id];
										$ultimoAtendimento=date('d/m/Y',strtotime($u->agenda_data));

										$tem=strtotime(date('Y-m-d H:i'))-strtotime($u->agenda_data);
										$tem/=(60*60*24*30);
										$tem=ceil($tem);
										if($tem<$paciente->periodicidade) continue;
										//$nome.=" ($paciente->periodicidade) ha $tem mese(s) - $u->agenda_data";
									} else {
										continue;
									}



									$status=isset($_pacientesStatus[$paciente->id])?$_pacientesStatus[$paciente->id]:0;

									$index=strtotime($u->agenda_data);
									if(isset($retornoPacientesAgendaJSONAux[$index])) {
										$index++;
									}

									$pacientesTodosIds[$paciente->id]=$paciente->id;

									$ft='';
									if(!empty($paciente->foto_cn)) $ft=$_cloudinaryURL.'c_thumb,w_100,h_100/'.$paciente->foto_cn;

									$retornoPacientesAgendaJSONAux[$index]=array('id_paciente'=>$paciente->id,
																			'profissional'=>$paciente->profissional_maisAtende,
																			'nome'=>$nome,
																			'ft'=>$ft,
																			'status'=>$status,
																			'telefone'=>empty($paciente->telefone1)?"":maskTelefone($paciente->telefone1));
								}
							}
						}
						arsort($retornoPacientesAgendaJSONAux);
						foreach($retornoPacientesAgendaJSONAux as $x) {
							//$retornoPacientesAgendaJSON
							$listaUnicaJSON[]=$x;
						}
					}

				# Excluidos

					$pacientesExcluidosJSON=array();
					foreach($_pacientesExcluidos as $x) {

						if(isset($_pacientesExcluidosObj[$x->id_paciente])) {
							$paciente=$_pacientesExcluidosObj[$x->id_paciente];

							$ft='';
							if(!empty($paciente->foto_cn)) $ft=$_cloudinaryURL.'c_thumb,w_100,h_100/'.$paciente->foto_cn;


							$status=isset($_pacientesStatus[$paciente->id])?$_pacientesStatus[$paciente->id]:0;

							$lista=isset($_pacientesExcluidosLista[$paciente->id])?$_pacientesExcluidosLista[$paciente->id]:'';

							$pacientesExcluidosJSON[]=array('id_paciente'=>$paciente->id,
																'nome'=>utf8_encode($paciente->nome),
																'ft'=>$ft,
																'lista'=>$lista,
																'status'=>$status,
																'telefone'=>empty($paciente->telefone1)?"":maskTelefone($paciente->telefone1));
						}
					}

				# Quantidade de agendamentos
					$agendaQtd=array();
					$agendaProfissionais=array();
					if(count($pacientesTodosIds)>0) {
						$sql->consult($_p."agenda","id,id_paciente,id_status,profissionais","where lixo=0");
						if($sql->rows) {
							while($x=mysqli_fetch_object($sql->mysqry)) {
								if(!isset($agendaQtd[$x->id_paciente][$x->id_status])) {
									$agendaQtd[$x->id_paciente][$x->id_status]=0;
								}

								$agendaQtd[$x->id_paciente][$x->id_status]++;

								if($x->id_status==5) {
									$aux = explode(",",$x->profissionais);
									foreach($aux as $idP) {
										if(!empty($idP) and is_numeric($idP)) {
											if(!isset($agendaProfissionais[$x->id_paciente][$idP])) {
												$agendaProfissionais[$x->id_paciente][$idP]=0;
											} 
											$agendaProfissionais[$x->id_paciente][$idP]++;
										}
									}
								}
							}

						}
					}


				# Ordena lista
					// Ordena lista

					/*
					3 - Pediu pra retornar
					0 - Sem status
					1 - Não conseguiu contato
					2 - Paciente entrará em contato
					*/

					$statusOrdem=array(3=>1,
										0=>2,
										1=>3,
										2=>4);


					/*$desmarcadosPacientesAgendaJSONOrdenada=array();
					foreach($desmarcadosPacientesAgendaJSON as $v) {
						$index = $statusOrdem[$v['status']];
						$desmarcadosPacientesAgendaJSONOrdenada[$index][]=$v;
					};

					$retornoPacientesAgendaJSONOrdenada=array();
					foreach($retornoPacientesAgendaJSON as $v) {
						$index = $statusOrdem[$v['status']];
						$retornoPacientesAgendaJSONOrdenada[$index][]=$v;
					}*/

					$listaUnicaJSONOrdenada=array();
					foreach($listaUnicaJSON as $v) {
						$index = $statusOrdem[$v['status']];

						if($index==4) continue;
						$listaUnicaJSONOrdenada[$index][]=$v;
					}

					//$desmarcadosPacientesAgendaJSON=array();
					//$retornoPacientesAgendaJSON=array();
					$listaUnicaJSON=array();
					for($i=1;$i<=4;$i++) {

						/*if(isset($desmarcadosPacientesAgendaJSONOrdenada[$i])) {
							foreach($desmarcadosPacientesAgendaJSONOrdenada[$i] as $v) {
								$desmarcadosPacientesAgendaJSON[]=$v;
							}
						}

						if(isset($retornoPacientesAgendaJSONOrdenada[$i])) {
							foreach($retornoPacientesAgendaJSONOrdenada[$i] as $v) {
								$retornoPacientesAgendaJSON[]=$v;
							}
						}*/
						if(isset($listaUnicaJSONOrdenada[$i])) {
							foreach($listaUnicaJSONOrdenada[$i] as $v) {
								$listaUnicaJSON[]=$v;
							}
						}
					}

					$listaFinal=array();

					foreach($listaUnicaJSON as $v) {
						$qtd=isset($agendaQtd[$v['id_paciente']][5])?$agendaQtd[$v['id_paciente']][5]:0;
						$qtdDesmarcados=isset($agendaQtd[$v['id_paciente']][4])?$agendaQtd[$v['id_paciente']][4]:0;
						$qtdFaltas=isset($agendaQtd[$v['id_paciente']][3])?$agendaQtd[$v['id_paciente']][3]:0;
						$item=$v;
						$item['atendidos']=$qtd;
						$item['desmarcados']=$qtdDesmarcados;
						$item['faltas']=$qtdFaltas;


						$profissionais = array();

						if(isset($agendaProfissionais[$v['id_paciente']])) {
							foreach($agendaProfissionais[$v['id_paciente']] as $idP=>$qtd){
								if(isset($_profissionais[$idP])) {
									$profissionais[]=array('nome'=>utf8_encode($_profissionais[$idP]->nome),
															'qtd'=>$qtd);
								}
							}
						}

						$item['profissionais']=$profissionais;

						$index=$qtd;
						while(isset($listaFinal[$index])) {
							$index.=".";
						}
						$listaFinal[$index]=$item;
					}
					krsort($listaFinal);

					$newLista=array();
					foreach($listaFinal as $v) {
						$newLista[]=$v;
					}



			$rtn=array('success'=>true,
						'pacientesOportunidades'=> ($newLista));

		}


		header("Content-type: application/json");
		echo json_encode($rtn);
		die();

	}
	include "includes/header.php";
	include "includes/nav.php";

	$_table=$_p."colaboradores";

	$_historicoStatus=array();
	$sql->consult($_p."pacientes_historico_status","*","");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_historicoStatus[$x->id]=$x;
	}
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

	


	$data = date('Y-m-d');

	if(isset($_GET['data']) and !empty($_GET['data'])) {
		list($dia,$mes,$ano)=explode("/",$_GET['data']);

		if(checkdate($mes, $dia, $ano)) {

			$data = $ano."-".$mes."-".$dia;

		}
	}


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
						<h3 class="js-cal-titulo-dia"><?php echo strtolower(diaDaSemana(date('w',strtotime($data))));?></h3>
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
						<p>Valorize o que mais importa, seu tempo! Análise de índices e sugestões guiadas por Inteligência Artificial</p>
					</div>
				</div>
				

				<div class="filter-group">
					<div class="filter-form form">
						<dl>
							<dd>
								<a href="<?php echo $_page."?data=".date('d/m/Y');?>" class="button<?php echo date('Y-m-d')==$data?" active":"";?>">hoje</a>	
								<a href="<?php echo $_page."?data=".date('d/m/Y',strtotime(date('Y-m-d')." + 1 day"));?>" class="button<?php echo date('Y-m-d',strtotime(date('Y-m-d')." + 1 day"))==$data?" active":"";?>">+ 1 dia</a>	
								<a href="<?php echo $_page."?data=".date('d/m/Y',strtotime(date('Y-m-d')." + 2 day"));?>" class="button<?php echo date('Y-m-d',strtotime(date('Y-m-d')." + 2 day"))==$data?" active":"";?>">+ 2 dias</a>	
								<a href="<?php echo $_page."?data=".date('d/m/Y',strtotime(date('Y-m-d')." + 3 day"));?>" class="button<?php echo date('Y-m-d',strtotime(date('Y-m-d')." + 3 day"))==$data?" active":"";?>">+ 3 dias</a>		
							</dd>
						</dl>						
					</div>					
				</div>

			</section>

			<section class="grid" style="grid-template-columns:40% auto">

				<div class="box">

					<div class="filter">
						<div class="filter-group">
							<div class="filter-title">
								<h1>Taxa de Ocupação</h1>
							</div>
						</div>
					</div>

					<section class="tab">
						<a href="javascript:;" class="active js-btn-grafico" data-tipo="cadeiras">Cadeiras</a>
						<?php /*<a href="javascript:;" class="js-btn-grafico" data-tipo="dentistas">Dentistas</a>*/?>					
					</section>

					<section style="width:100%; height:300px; background:var(--cinza2); margin-bottom:var(--margin1);padding:15px;">		

						<?php

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

					<div class="list4">
						<?php
						foreach($_cadeiras as $c) {

							$cadeiraHoras = isset($_horas[$c->id]) ? $_horas[$c->id] : 0;
							$agendaHoras = isset($_agendaHoras[$c->id]) ? $_agendaHoras[$c->id] : 0;

							$indice = ceil($cadeiraHoras==0?100:($agendaHoras/$cadeiraHoras)*100);
						?>
						<a href="" class="list4-item active">
							<div>
								<h1>
									<?php 
									if($indice<0) {
										echo $indice.'%';
									} else {
										echo ($indice==0?0:($indice)).'%';
									}
									?>
								</h1>
							</div>
							<div>
								<p>
									<?php echo utf8_encode($c->titulo);?>
									<?php echo "<br /><span style=\"font-size:12px;color:var(--cinza3)\"><span class=\"iconify\" data-icon=\"bi:clock-history\" style=\"color:var(--cor1)\"></span>&nbsp;&nbsp;".$agendaHoras." / ".$cadeiraHoras."min</span>";?>
								</p>
							</div>
						</a>
						<?php
						}
						?>
						
					</div>
				</div>

				<div class="box box-col">

					<div class="box-col__inner1" style="flex:0 1 45%;">

						<div class="filter">
							<div class="filter-group">
								<div class="filter-title">
									<h1>Sugestões</h1>
								</div>
							</div>
						</div>

						<?php

						





						?>

						<div class="list3">
							<span class="list3-item">
								<i class="iconify" data-icon="fluent:lightbulb-filament-20-regular"></i>
								<p>Há <b class="js-indicador-oportunidades">0</b> oportunidades de agendamentos</p>
							</span>
							
						</div>

					</div>

					<div class="box-col__inner1 box_inv">
						
						<form method="form" class="form">
							<div class="colunas">
								<dl>
									<dd>
										<select class="js-filtro-profissional">
											<option>Todos Profissionais</option>
											<?php
											foreach($_profissionais as $p) {
												if($p->check_agendamento==0) continue;
												echo '<option value="'.$p->id.'">'.utf8_encode($p->nome).'</option>';
											}
											?>
										</select>
									</dd>
								</dl>
								<dl>
									<dd>
										<select class="js-filtro-status">
											<option value="0">Todos Status</option>
											<?php
											foreach($_historicoStatus as $v) {
												echo '<option value="'.$v->id.'">'.utf8_encode($v->titulo).'</option>';
											}
											?>
										</select>
									</dd>
								</dl>
							</div>
						</form>

						<div class="list1">

							<script type="text/javascript">
								<?php 
								/*var pacientesDesmarcados = JSON.parse(`<?php echo json_encode($desmarcadosPacientesAgendaJSON);?>`);
								var pacientesRetorno = JSON.parse(`<?php echo json_encode($retornoPacientesAgendaJSON);?>`);
								var pacientesExcluidos = JSON.parse(`<?php echo json_encode($pacientesExcluidosJSON);?>`);*/
								?>


								var pacientesOportunidades = [];
								var pacientesRetorno = [];
								var pacientesExcluidos = [];

								var pacientes = [];
								var pagina = 0;
								var paginaReg = 1;
								var paginaQtd = 0;

								const atualizaValorListasInteligentes = () => {

									let data = `ajax=atualizaListaInteligente`;

									$.ajax({
										type:"POST",
										data:data,
										success:function(rtn) {
											if(rtn.success) {

												pacientesOportunidades = rtn.pacientesOportunidades;

												

											}
										}
									}).done(function(){
										pacientesLista();
									})

								}



								const pacientesLista = () => {
									
									$('.js-pacientes').html(``);

									pacientes = pacientesOportunidades;

									let filtro = $('.js-filtro-pacientes option:selected').val();

									$('.js-indicador-oportunidades').html(pacientesOportunidades.length);

									let status = $('.js-filtro-status option:selected').val();

									if(status>0) {
										pacientes = pacientes.filter(x=>{return x.status==status});
									}


									let profissional = $('.js-filtro-profissional option:selected').val();
									if(profissional>0) {
										pacientes = pacientes.filter(x=>{return x.profissional==profissional});
									}

									if(pacientes.length==0) {
										$('.js-nenhumpaciente').show();
										$('.js-paginacao,.js-guia,.js-carregando').hide();
									} else {
										$('.js-nenhumpaciente,.js-carregando').hide();
										$('.js-paginacao,.js-guia').show();
										paginaQtd =  Math.ceil(pacientes.length/paginaReg);

										for (var i = pagina * paginaReg; i < pacientes.length && i < (pagina + 1) * paginaReg; i++) {

											x = pacientes[i];

											let icone = ``;

											// nao conseguiu contato 
											if(x.status==1) {
												icone=`<i class="iconify" data-icon="fluent:call-dismiss-24-regular" style="font-size:2em; color:red;"></i>`;
											} 
											// paciente entrara em contato
											else if(x.status==2) {
												icone=`<i class="iconify" data-icon="fluent:call-inbound-24-regular" style="font-size:2em; color:orange;"></i>`;
											} 
											// paciente pediu para retornar posteriormente
											else if(x.status==3) {
												icone=`<i class="iconify" data-icon="fluent:call-missed-24-regular" style="font-size:2em; color:blue;"></i>`;
											}

											let lista=``;
											if(filtro=="excluidos") {
												lista=x.lista;
											}

		
											let profissionais = '';

											if(x.profissionais && x.profissionais.length>0) {
												x.profissionais.forEach(x=>{
													profissionais+=`${x.nome}: ${x.qtd}<br />`;
												})
											}

											let ft = (x.ft && x.ft.length>0)?x.ft:'img/ilustra-usuario.jpg';
											$('.js-pacientes').append(`<tr class="js-item" data-filtro="${filtro}" data-id_paciente="${x.id_paciente}" data-lista="${lista}" style="height:420px">
																			<td class="list1__foto"><img src="${ft}" width="54" height="54" /></td>
																			<td>
																			<h1>${x.nome}</h1>
																			<p>${x.telefone}</p>
																			<p>Atendido: ${x.atendidos}</p>
																			<p>Faltas: ${x.faltas}</p>
																			<p>Desmarcado: ${x.desmarcados}</p>
																			<p>${profissionais}</p>
																			</td>
																			<td>${icone}</td>
																		</tr>`);

										};

										//$('.js-guia').html(`Página <b>${pagina+1}</b> de <b>${paginaQtd}</b>`);

										if(paginaQtd==1) {
											$('.js-guia,.js-paginacao').hide();
										} else {

											$('.js-guia,.js-paginacao').show();
										}
									}
								}


								$(function(){

									atualizaValorListasInteligentes();

									$('.js-filtro-status').change(function(){
										pagina=0;
										pacientesLista();
									});

									$('.js-filtro-profissional').change(function(){
										pagina=0;
										pacientesLista();
									}).trigger('change');

									$('.js-anterior').click(function(){
										if(pagina<=0) {
											pagina = paginaQtd-1;
										} else {
											pagina--;
										}
										pacientesLista();
									});

									$('.js-pacientes').on('click','.js-item',function(){
										pacienteRelacionamento($(this));
									})

									


									$('.js-proximo').click(function(){

										if(paginaQtd>1) {
											if((pagina+1)>=paginaQtd) {
												pagina = 0;
											} else {
												pagina++;
											}
											pacientesLista();
										}

									});
								})

							</script>

							<span class="js-nenhumpaciente"><center>Nenhum paciente</center></span>
							<span class="js-carregando"><center>Carregando...</center></span>
							<table class="js-pacientes">
								
										
							</table>

							<div style="display:flex;flex-wrap: nowrap;justify-content:space-between;margin: 10px 10px 0px 10px;" class="js-paginacao">
								<a href="javascript:;" class="js-anterior"><span class="iconify" data-icon="akar-icons:circle-chevron-left-fill" data-height="25"></span></a>
								<span class="js-guia"></span>
								<a href="javascript:;" class="js-proximo"><span class="iconify" data-icon="akar-icons:circle-chevron-right-fill" data-height="25"></span></a>
							</div>
						</div>

					</div>

				</div>

			</section>
		
		</div>
	</main>

	

<?php 
	


	$apiConfig=array('pacienteRelacionamento'=>1);
	require_once("includes/api/apiAside.php");

	include "includes/footer.php";
?>	