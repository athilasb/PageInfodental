<?php
/*


statusRelacionamento 
	- pediu para ligar => *2
	- nao atendeu => *1
	- cliente ficou de retornar => exclui da lista
	- se nao tiver status => *1

btnExcluirDaLista
	- exclui da lista

((numeroDeVezesAtendidos/numeroDeVezesFaltadosEDesmarcados)+numeroDeVezesAtendidos)*statusRelacionamento

Lista 1
	- Paciente de Periodicidade
		periodicidade

Lista 2
	- Paciente em Tratamento


Lista Unica
	- 2 paciente em tratamento
	- 1 paciente de periodicidade


*/
	if(isset($_POST['ajax'])) {

		require_once("lib/conf.php");
		require_once("usuarios/checa.php");

		$rtn = array();

		if($_POST['ajax']=="atualizaListaInteligente") {

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
				$sql->consult($_p."pacientes","id,telefone1,foto_cn,nome,profissional_maisAtende","where id IN (".implode(",",$pacientesIds).")");
				while($x=mysqli_fetch_object($sql->mysqry)) {
					$_pacientesExcluidosObj[$x->id]=$x;
				}

				
			}

			# Sugestoes sem BI

				$todosPacientesIds=array();

				# Desmarcados sem agendamentos

					$desmarcadosPacientesAgenda=array();

					// busca pacientes desmarcados/faltou nos ultimos 360 dias 
					$sql->consult($_p."agenda","id,id_paciente,agenda_data","WHERE data > NOW() - INTERVAL 360 DAY and id_status IN (4,3) and lixo=0 order by agenda_data desc");
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
						$sql->consult($_p."agenda","id,id_paciente,agenda_data","WHERE data > NOW() - INTERVAL 360 DAY and id_paciente IN (".implode(",",$desmarcadosPacientesIds).") and id_status IN (1,2,6,7,5) and lixo=0 order by agenda_data desc");
						while($x=mysqli_fetch_object($sql->mysqry)) {

							if(isset($desmarcadosPacientesAgenda[$x->id_paciente])) {

								// ultimo agendamento desmarcado
								$ultimoAgendamentoDesmarcado = $desmarcadosPacientesAgenda[$x->id_paciente];


								// se o ultimo agendamento desmarcado for menor que o ultimo agendamento confirmado, a confirmado ou atendido, remove da lista
								$removerDaLista = (strtotime($ultimoAgendamentoDesmarcado->agenda_data)<strtotime($x->agenda_data))?1:0;
								if($removerDaLista==1) {
									unset($desmarcadosPacientesAgenda[$x->id_paciente]);
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

								$todosPacientesIds[$v->id_paciente]=$v->id_paciente;

								$paciente=$_pacientesDesmarcados[$v->id_paciente];
								//echo $v->id_paciente." ".$_pacientesDesmarcados[$v->id_paciente]->nome."<BR>";
								//$nome=$cont++." - ".utf8_encode($paciente->nome);
								$nome=utf8_encode($paciente->nome);

								$ft='';
								if(!empty($paciente->foto_cn)) $ft=$_cloudinaryURL.'c_thumb,w_100,h_100/'.$paciente->foto_cn;

								$status=isset($_pacientesStatus[$paciente->id])?$_pacientesStatus[$paciente->id]:0;
								$desmarcadosPacientesAgendaJSON[]=array('id_paciente'=>$paciente->id,
																		'nome'=>$nome,
																		'ft'=>$ft,
																		'status'=>$status,
																		'id_profissional'=>$paciente->profissional_maisAtende,
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

							if($atendidosPacientesVezes[$x->id_paciente]>=1) {
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
								$todosPacientesIds[$idPaciente]=$idPaciente;

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

									$ft='';
									if(!empty($paciente->foto_cn)) $ft=$_cloudinaryURL.'c_thumb,w_100,h_100/'.$paciente->foto_cn;

									$retornoPacientesAgendaJSONAux[$index]=array('id_paciente'=>$paciente->id,
																			'nome'=>$nome,
																			'ft'=>$ft,
																			'status'=>$status,
																			'id_profissional'=>$paciente->profissional_maisAtende,
																			'telefone'=>empty($paciente->telefone1)?"":maskTelefone($paciente->telefone1));
								}
							}
						}
						arsort($retornoPacientesAgendaJSONAux);
						foreach($retornoPacientesAgendaJSONAux as $x) {
							$retornoPacientesAgendaJSON[]=$x;
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

				# Busca Numeros de Atendidos, Desmarcados e Faltantes
					$_pacientesAtendidos=array();
					$_pacientesDesmarcados=array();
					$_pacientesFaltantes=array();
					$_pacientesDesmarcadosEFaltantes=array();
					$sql->consult($_p."agenda","id,id_status,id_paciente","where id_paciente IN (".implode(",",$todosPacientesIds).") and id_status IN (5,3,4) and lixo=0");
					while($x=mysqli_fetch_object($sql->mysqry)) {
						if($x->id_status==5) {
							if(!isset($_pacientesAtendidos[$x->id_paciente])) {
								$_pacientesAtendidos[$x->id_paciente]=0;
							}
							$_pacientesAtendidos[$x->id_paciente]++;
						} else if($x->id_status==3) {
							if(!isset($_pacientesDesmarcadosEFaltantes[$x->id_paciente])) {
								$_pacientesDesmarcadosEFaltantes[$x->id_paciente]=0;
							}
							$_pacientesDesmarcadosEFaltantes[$x->id_paciente]++;


							if(!isset($_pacientesFaltantes[$x->id_paciente])) {
								$_pacientesFaltantes[$x->id_paciente]=0;
							}
							$_pacientesFaltantes[$x->id_paciente]++;
						} else if($x->id_status==4) {
							if(!isset($_pacientesDesmarcadosEFaltantes[$x->id_paciente])) {
								$_pacientesDesmarcadosEFaltantes[$x->id_paciente]=0;
							}
							$_pacientesDesmarcadosEFaltantes[$x->id_paciente]++;

							
							if(!isset($_pacientesDesmarcados[$x->id_paciente])) {
								$_pacientesDesmarcados[$x->id_paciente]=0;
							}
							$_pacientesDesmarcados[$x->id_paciente]++;
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

					// numero de pacientes total na lista do desmarcados e retorno
					$numeroTotal=0;

					$statusOrdem=array(3=>1,
										0=>2,
										1=>3,
										2=>4);


					$desmarcadosPacientesAgendaJSONOrdenada=array();
					foreach($desmarcadosPacientesAgendaJSON as $v) {
						//$index = $statusOrdem[$v['status']];

						if($v['status']==3) $statusRelacionamento=2;
						else if($v['status']==1) $statusRelacionamento=1;
						else if($v['status']==2) continue;
						else if($v['status']==0) $statusRelacionamento=1;

						$numeroDeVezesAtendidos = isset($_pacientesAtendidos[$v['id_paciente']])?$_pacientesAtendidos[$v['id_paciente']]:0;
						$numeroDeVezesFaltadosEDesmarcados = isset($_pacientesDesmarcadosEFaltantes[$v['id_paciente']])?$_pacientesDesmarcadosEFaltantes[$v['id_paciente']]:0;
						$numeroDeVezesFaltas = isset($_pacientesFaltantes[$v['id_paciente']])?$_pacientesFaltantes[$v['id_paciente']]:0;
						$numeroDeVezesDesmarcados = isset($_pacientesDesmarcados[$v['id_paciente']])?$_pacientesDesmarcados[$v['id_paciente']]:0;

						if($numeroDeVezesFaltadosEDesmarcados==0) $numeroDeVezesFaltadosEDesmarcados=1;
						$index=round((($numeroDeVezesAtendidos/$numeroDeVezesFaltadosEDesmarcados)+$numeroDeVezesAtendidos)*$statusRelacionamento);

						//echo $v['status']." -> (($numeroDeVezesAtendidos/$numeroDeVezesFaltadosEDesmarcados)+$numeroDeVezesAtendidos)*$statusRelacionamento = $index";die();
						$v['index']=$index;
						$v['atendidos']=$numeroDeVezesAtendidos;
						$v['faltas']=$numeroDeVezesFaltas;
						$v['desmarcados']=$numeroDeVezesDesmarcados;
						$desmarcadosPacientesAgendaJSONOrdenada[$index][]=$v;
						$numeroTotal++;
					};



					$retornoPacientesAgendaJSONOrdenada=array();
					foreach($retornoPacientesAgendaJSON as $v) {
						//$index = $statusOrdem[$v['status']];

						if($v['status']==3) $statusRelacionamento=2;
						else if($v['status']==1) $statusRelacionamento=1;
						else if($v['status']==2) continue;
						else if($v['status']==0) $statusRelacionamento=1;

						$numeroDeVezesAtendidos = isset($_pacientesAtendidos[$v['id_paciente']])?$_pacientesAtendidos[$v['id_paciente']]:0;
						$numeroDeVezesFaltas = isset($_pacientesFaltantes[$v['id_paciente']])?$_pacientesFaltantes[$v['id_paciente']]:0;
						$numeroDeVezesDesmarcados = isset($_pacientesDesmarcados[$v['id_paciente']])?$_pacientesDesmarcados[$v['id_paciente']]:0;
						$numeroDeVezesFaltadosEDesmarcados = isset($_pacientesDesmarcadosEFaltantes[$v['id_paciente']])?$_pacientesDesmarcadosEFaltantes[$v['id_paciente']]:0;

						if($numeroDeVezesFaltadosEDesmarcados==0) $numeroDeVezesFaltadosEDesmarcados=1;

						$index=round((($numeroDeVezesAtendidos/$numeroDeVezesFaltadosEDesmarcados)+$numeroDeVezesAtendidos)*$statusRelacionamento);

						//echo $v['status']." -> (($numeroDeVezesAtendidos/$numeroDeVezesFaltadosEDesmarcados)+$numeroDeVezesAtendidos)*$statusRelacionamento = $index";die();
						$v['index']=$index;
						$v['atendidos']=$numeroDeVezesAtendidos;
						$v['faltas']=$numeroDeVezesFaltas;
						$v['desmarcados']=$numeroDeVezesDesmarcados;
						$retornoPacientesAgendaJSONOrdenada[$index][]=$v;
						$numeroTotal++;
					}

					$desmarcadosPacientesAgendaJSON=array();
					$retornoPacientesAgendaJSON=array();
					/*for($i=1;$i<=4;$i++) {
						if(isset($desmarcadosPacientesAgendaJSONOrdenada[$i])) {
							foreach($desmarcadosPacientesAgendaJSONOrdenada[$i] as $v) {
								$desmarcadosPacientesAgendaJSON[]=$v;
							}
						}

						if(isset($retornoPacientesAgendaJSONOrdenada[$i])) {
							foreach($retornoPacientesAgendaJSONOrdenada[$i] as $v) {
								$retornoPacientesAgendaJSON[]=$v;
							}
						}
					}*/
					krsort($retornoPacientesAgendaJSONOrdenada);
					krsort($desmarcadosPacientesAgendaJSONOrdenada);

					$listaUnificada=array('retorno'=>array(),
											'desmarcado'=>array());

					foreach($retornoPacientesAgendaJSONOrdenada as $ind=>$regs) {
						foreach($regs as $x) {
							$listaUnificada['retorno'][]=$x;
						}
					}
					foreach($desmarcadosPacientesAgendaJSONOrdenada as $ind=>$regs) {
						foreach($regs as $x) {
							$listaUnificada['desmarcado'][]=$x;
						}
					}

					$indiceDesmarcado=$indiceRetorno=0;

					$listaFinal=array();
					for($i=0;$i<=$numeroTotal;$i++) {

						
						if(isset($listaUnificada['desmarcado'][$indiceDesmarcado])) {
							$r=$listaUnificada['desmarcado'][$indiceDesmarcado];
							$r['tipo']="desmarcado";

							$listaFinal[]=$r;
							$indiceDesmarcado++;
						}
						
						if(isset($listaUnificada['desmarcado'][$indiceDesmarcado])) {
							$r=$listaUnificada['desmarcado'][$indiceDesmarcado];
							$r['tipo']="desmarcado";

							$listaFinal[]=$r;
							$indiceDesmarcado++;
						}
						if(isset($listaUnificada['retorno'][$indiceRetorno])) {
							$r=$listaUnificada['retorno'][$indiceRetorno];
							$r['tipo']="retorno";

							$listaFinal[]=$r;
							$indiceRetorno++;
						}
						
					}



			$rtn=array('success'=>true,
						'pacientes'=>$listaFinal);

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

				<div class="box">

					<div class="filter">
						<div class="filter-group">
							<div class="filter-title">
								<h1>Horários Disponíveis</h1>
							</div>
						</div>
						<div class="filter-form form">
							<dl>
								<dd><select name="" style="width:200px;"><option value="">cadeira...</option></select></dd>
							</dl>
							<dl>
								<dd><select name="" style="width:200px;"><option value="">profissional...</option></select></dd>
							</dl>
						</div>
					</div>

					<div style="margin-bottom:var(--margin1);">
						<a href="" class="button button_disabled">08:00 - 09:00</a>
						<a href="" class="button button_disabled">10:00 - 13:00</a>
						<a href="" class="button button_disabled">15:00 - 16:00</a>
						<a href="" class="button button_disabled">17:30 - 18:00</a>
					</div>

					<div class="box box_inv">
						
						<div class="filter">
							<div class="filter-group">
								<div class="filter-title">
									<h1 style="color:var(--cor1);">Sugestões Inteligentes</h1>
								</div>
							</div>
						</div>

						<section class="header-profile">
							<img src="img/ilustra-usuario.jpg" alt="" width="60" height="60" class="header-profile__foto" />
							<div class="header-profile__inner1">
								<h1>João Ricardo da Costa</h1>
								<div>
									<p>Em tratamento</p>
									<p>35 anos</p>
									<p>Periodicidade: <strong>6 meses</strong></p>
								</div>
							</div>
							<div class="header-fone">
								<i class="iconify" data-icon="fluent:call-connecting-20-regular"></i><p>(62) 98405-0927</p>
							</div>
						</section>

						<div class="list6">
							<div class="list6-item">
								<h1>Atendimentos</h1>
								<h2>2</h2>
							</div>
							<div class="list6-item">
								<h1>Último Atend.</h1>
								<h2>180d</h2>
							</div>
							<div class="list6-item">
								<h1>Tempo Médio</h1>
								<h2>60m</h2>
							</div>
							<div class="list6-item">
								<h1>Faltou</h1>
								<h2>0</h2>
							</div>
						</div>

						<div class="proxag">
							<header>
								<i class="iconify" data-icon="fluent:calendar-checkmark-24-regular"></i>
								<h1>Próximo Agend.</h1>
							</header>
							<article>
								<p>Duração: <strong>60m</strong></p>
								<p>Necessita Laboratório: <strong>Sim</strong></p>
								<p>Necessita Imagem: <strong>Não</strong></p>
							</article>
							<article>
								<p>Obs:</p>
								<p><strong>CIMENTAR 3 ELEMENTOS - 23/24/25</strong></p>
							</article>
						</div>

						<div class="filter" style="margin-bottom:0;">
							<div class="filter-group">
								<div class="filter-form form">
									<dl>
										<dd><a href="" class="button tooltip" title="Não atendeu"><i class="iconify" data-icon="fluent:call-dismiss-24-regular"></i></a></dd>
									</dl>
									<dl>
										<dd><a href="" class="button tooltip" title="Paciente pediu para retornar"><i class="iconify" data-icon="fluent:call-missed-24-regular"></i></a></dd>
									</dl>
									<dl>
										<dd><a href="" class="button tooltip" title="Paciente entrará em contato"><i class="iconify" data-icon="fluent:call-inbound-24-regular"></i></a></dd>
									</dl>
									<dl>
										<dd><a href="" class="button tooltip" title="Excluir das sugestões"><i class="iconify" data-icon="fluent:delete-24-regular"></i></a></dd>
									</dl>
									<dl>
										<dd><a href="" class="button tooltip" title="Pular sugestão"><i class="iconify" data-icon="fluent:skip-forward-tab-24-filled"></i></a></dd>
									</dl>
								</div>
							</div>
							<div class="filter-group">
								<div class="filter-form form">
									<dl>
										<dd><a href="" class="button button_main"><i class="iconify" data-icon="fluent:calendar-checkmark-24-regular"></i><span>Agendar</span></a></dd>
									</dl>
								</div>
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