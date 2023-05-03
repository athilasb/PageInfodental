<?php

// POPULAR EM PROXIMOSAGENDAMENTOS A COLUNA SITUACAO
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
	
		$attr=array('_cloudinaryURL'=>$_cloudinaryURL,
					'_codigoBI'=>$_codigoBI,
					'prefixo'=>$_p);
		$inteligencia=new Inteligencia($attr);

		if($_POST['ajax']=="gestaoDoTempo") {

			$pacientes=$inteligencia->gestaoDoTempo();
		
			$rtn=array('success'=>true,
						'indisponiveis'=>$inteligencia->indisponiveis,
						'pacientes'=>$pacientes);
		}


		else if($_POST['ajax']=="listaInteligenteBKP") {

			
			$listaInteligente=array();
			$todosPacientesIds=array();

			$_profissionais=array();
			$sql->consult($_p."colaboradores","id,nome","");
			while($x=mysqli_fetch_object($sql->mysqry)) {
				$_profissionais[$x->id]=$x;
			}


			# Lista de proximos atendimentos

				$di = date('Y-m-d');
				$df = date('Y-m-d',strtotime(date('Y-m-d H:i:s')." + 3 day"));
				$pacientesIds=array();
				$pacienteOrdem=array();
				$proximasConsultasIds=array();

				// pacientes que tem proximo agendamento para hoje ou nos proximos 2 dias
				// situacao<3 => paciente entrara em contato (3), ou excluido (5)
				$where="where  DATE_ADD(data, INTERVAL retorno DAY)<='".$df." 23:59:59' and lixo=0 and situacao<3 order by data desc";
				$sql->consult($_p."pacientes_proximasconsultas","*,DATE_ADD(data, INTERVAL retorno DAY) as proximaConsulta",$where);
				//echo $where."->".$sql->rows."\n\n";
				while($x=mysqli_fetch_object($sql->mysqry)) {
					
					// se ja encontrou proxima consulta, verifica qual é a mais proxima
					if(isset($proximaConsulta[$x->id_paciente])) {
						continue;
						if(strtotime($proximaConsulta[$x->id_paciente]->proximaConsulta)<strtotime($x->proximaConsulta)) {
							$proximaConsulta[$x->id_paciente]=$x;
						}
					} else {
						$proximaConsulta[$x->id_paciente]=$x;
					}
					$pacientesIds[$x->id_paciente]=$x->id_paciente;

					$index=$x->situacao.".".strtotime($x->proximaConsulta);

					while(isset($pacienteOrdem[$index])) {
						$index++;
					}

					$pacienteOrdem[$index]=$x->id_paciente;
					$proximasConsultasIds[]=$x->id;
				}


				// verifica se possuem agendamentos
				$pacientesIdsQueMarcaram=array();
				if(count($pacientesIds)>0) {
					$where="where id_paciente IN (".implode(",",$pacientesIds).") and lixo=0 and id_status IN (1,2,6,7) order by agenda_data desc";
					$sql->consult($_p."agenda","*",$where);
					while($x=mysqli_fetch_object($sql->mysqry)) {


						if(strtotime($x->agenda_data)>strtotime($proximaConsulta[$x->id_paciente]->proximaConsulta)) {
							//if($x->id_paciente==8082) echo $x->id_paciente."->".$x->agenda_data." ".$proximaConsulta[$x->id_paciente]->proximaConsulta."<BR>";
							//echo $x->id_paciente." => ".$proximaConsulta[$x->id_paciente]->data." ($x->agenda_data)<BR>";
							$pacientesIdsQueMarcaram[$x->id_paciente][]=$x->agenda_data;
						}
					}
				

					$preListaInteligente=array();
					$sql->consult($_p."pacientes","id,nome,periodicidade,data_nascimento,telefone1,codigo_bi,foto_cn,foto","where id IN (".implode(",",$pacientesIds).")");
					if($sql->rows) {
						while($x=mysqli_fetch_object($sql->mysqry)) {


							// verifica se o paciente possui agendamento futuro
							$agendamentosFuturos=array();
							if(isset($pacientesIdsQueMarcaram[$x->id])) {
								foreach($pacientesIdsQueMarcaram[$x->id] as $dt) {
									$agendamentosFuturos[]=date('d/m/Y H:i',strtotime($dt));
								}
							}

							$proximaConsultaJSON='';
							$proxima='';
							if(isset($proximaConsulta[$x->id])) {

								$profissionais=array();

								if(!empty($proximaConsulta[$x->id]->profissionais)) {
									$aux=explode(",",$proximaConsulta[$x->id]->profissionais);
									
									foreach($aux as $idP) {
										if(!empty($idP) and is_numeric($idP) and isset($_profissionais[$idP])) {
											$profissionais[]=array('nome'=>$_profissionais[$idP]->nome);
										}
									}	
								}

								$i=$proximaConsulta[$x->id];
								$proxima=date('Y-m-d H:i',strtotime("$i->data + $i->retorno day"));
								$proximaConsultaJSON=array('duracao'=>(int)$i->duracao,
															'dataProx'=>date('d/m/Y',strtotime("$i->data + $i->retorno day")),
															'laboratorio'=>(int)$i->laboratorio,
															'imagem'=>(int)$i->imagem,
															'profissionais'=>$profissionais,
															'obs'=>addslashes(utf8_encode($i->obs)));
								
							
								$atendimentos=$faltou=$tempoMedio=0;
								$ultimoAtendimento="-";


								$ft='';
								if(!empty($x->foto_cn)) $ft=$_cloudinaryURL.'c_thumb,w_100,h_100/'.$x->foto_cn;

								$preListaInteligente[$x->id]=array('id_proximaconsulta'=>(int)$i->id,
																	'proxima'=>$proxima,
																	'id_paciente'=>$x->id,
																	'nome'=>addslashes(utf8_encode($x->nome)),
																	'proximaConsulta'=>$proximaConsultaJSON,
																	'periodicidade'=>$x->periodicidade,
																	'telefone'=>telefoneMascara($x->telefone1),
																	'bi'=>isset($_codigoBI[$x->codigo_bi])?utf8_encode($_codigoBI[$x->codigo_bi]):$x->codigo_bi,
																	'idade'=>(int)idade($x->data_nascimento),
																	'ft'=>$ft,
																	'ultimoAtendimento'=>'-',
																	'atendimentos'=>0,
																	'tempoMedio'=>0,
																	'faltou'=>0,
																	'futuros'=>$agendamentosFuturos);
								$todosPacientesIds[$x->id]=$x->id;
							}
						}

						ksort($pacienteOrdem);
						foreach($pacienteOrdem as $str=>$idPaciente) {
							if(isset($preListaInteligente[$idPaciente])) {
								$listaInteligente[]=$preListaInteligente[$idPaciente];
							}
						}
					}
				}


			# Metricas (Ultimo agendamento, atendimentos, faltas...)

				if(count($todosPacientesIds)>0) {
					$where="where  id_paciente IN (".implode(",",$todosPacientesIds).") and agenda_data>now() and lixo=0 order by data desc";
					$sql->consult($_p."agenda","id,id_status,id_paciente,agenda_data,agenda_duracao",$where);
					while($x=mysqli_fetch_object($sql->mysqry)) {


						if(!isset($pacientesMetricas[$x->id_paciente])) {

							$ultimo='';
							if($x->id_status==5) {
								$ultimo=strtotime(date('Y-m-d'))-strtotime($x->agenda_data);
								$ultimo/=(60*60*24);
								$ultimo=floor($ultimo)+1;
							}

							$pacientesMetricas[$x->id_paciente]=array('atendimentos'=>0,
																'tempo'=>0,
																'faltou'=>0,
																'ultimoAtendimento'=>$ultimo);
						}

						if($x->id_status==5) {
							$pacientesMetricas[$x->id_paciente]['tempo']+=$x->agenda_duracao;
							$pacientesMetricas[$x->id_paciente]['atendimentos']++; 


							if(empty($pacientesMetricas[$x->id_paciente]['ultimoAtendimento'])) {
								$ultimo=strtotime(date('Y-m-d'))-strtotime($x->agenda_data);
								$ultimo/=(60*60*24);
								$ultimo=floor($ultimo)+1;
								$pacientesMetricas[$x->id_paciente]['ultimoAtendimento']=$ultimo;


							}
						}
						else if($x->id_status==3) $pacientesMetricas[$x->id_paciente]['faltou']++; 
					}


					// busca historicos dos pacientes (evento = observacao => Pedir para entrar em contato, Nao conseguiu contato...)
					$_pacientesHistorico=array();
					if(count($proximasConsultasIds)>0) {
						$where="where id_paciente IN (".implode(",",$todosPacientesIds).") and id_proximaconsulta IN (".implode(",",$proximasConsultasIds).") and evento='observacao' and lixo=0 order by data desc";
						$sql->consult($_p."pacientes_historico","*",$where);
						if($sql->rows) {
							while($x=mysqli_fetch_object($sql->mysqry)) {
								//if($x->id_paciente==9004) echo $x->id." ".$x->id_obs;
								if(!isset($_pacientesHistorico[$x->id_paciente])) {
									$_pacientesHistorico[$x->id_paciente]=$x;
								}
							}
						}
					}
				}



			$listaInteligenteFinal=array();
			foreach($listaInteligente as $x) {
				$x=(object)$x;

				$obj=$x;
				if(isset($pacientesMetricas[$x->id_paciente])) {
					$obj->atendimentos=$pacientesMetricas[$x->id_paciente]['atendimentos'];
					$obj->faltou=$pacientesMetricas[$x->id_paciente]['faltou'];
					$obj->ultimoAtendimento=$pacientesMetricas[$x->id_paciente]['ultimoAtendimento'];
					$obj->tempoMedio=$pacientesMetricas[$x->id_paciente]['atendimentos']==0?0:round($pacientesMetricas[$x->id_paciente]['tempo']/$pacientesMetricas[$x->id_paciente]['atendimentos']);
					//$obj->tempoMedioFormula=$pacientesMetricas[$x->id_paciente]['tempo']." / ".$pacientesMetricas[$x->id_paciente]['atendimentos'];
				}

				if(isset($_pacientesHistorico[$x->id_paciente])) {
					$obj->id_obs=(int)$_pacientesHistorico[$x->id_paciente]->id_obs;
					$obj->id_obs_observacoes=(int)$_pacientesHistorico[$x->id_paciente]->relacionamento_momento;
				} else {
					$obj->id_obs=0;
					$obj->id_obs_observacoes='';
				}



				$listaInteligenteFinal[]=$obj;
			}


			$listaInteligenteFinalPreOrdenada=array();
			foreach($listaInteligenteFinal as $x) {

				//if($x->id_paciente==9004) echo $x->id_obs;
				// exclui da lista se for: excluido (5), resolvido (6), agendado pela tarefa inteligente (7)
				if($x->id_obs==5 or $x->id_obs==6 or $x->id_obs==7) continue;

				$idObs=1;
				if($x->id_obs==1 or $x->id_obs==2 or $x->id_obs==3 or $x->id_obs==4) $idObs=2;

				$index=strtotime($x->proxima);;


				while(isset($listaInteligenteFinalPreOrdenada[$idObs][$index])) {
					$index+=1;
				}
				

				$listaInteligenteFinalPreOrdenada[$idObs][$index]=$x; 
			}
			if(isset($listaInteligenteFinalPreOrdenada[1])) ksort($listaInteligenteFinalPreOrdenada[1]);
			if(isset($listaInteligenteFinalPreOrdenada[2])) ksort($listaInteligenteFinalPreOrdenada[2]);


			if(isset($listaInteligenteFinalPreOrdenada[1]) and isset($listaInteligenteFinalPreOrdenada[2])) {
				$listaInteligenteFinalOrdenada=array_merge($listaInteligenteFinalPreOrdenada[1],$listaInteligenteFinalPreOrdenada[2]);
			} else if(isset($listaInteligenteFinalPreOrdenada[1])) {
				$listaInteligenteFinalOrdenada=$listaInteligenteFinalPreOrdenada[1];
			} else if(isset($listaInteligenteFinalPreOrdenada[2])) {
				$listaInteligenteFinalOrdenada=$listaInteligenteFinalPreOrdenada[2];
			}

			$listaInteligenteFinalOrdenadaFinal=array();
			if(isset($listaInteligenteFinalOrdenada) and is_array($listaInteligenteFinalOrdenada)) {
				foreach($listaInteligenteFinalOrdenada as $x) {
					$listaInteligenteFinalOrdenadaFinal[]=$x;
				}
			}

			$rtn=array('success'=>true,'pacientes'=>$listaInteligenteFinalOrdenadaFinal);
		}

		else if($_POST['ajax']=="pacienteHistoricoObs") {

			$obs = (isset($_POST['obs']) and !empty($_POST['obs'])) ? $_POST['obs'] : '';
			$tipo =  (isset($_POST['tipo']) and !empty($_POST['tipo'])) ? $_POST['tipo'] : '';
			$id_proximaconsulta =  (isset($_POST['id_proximaconsulta']) and is_numeric($_POST['id_proximaconsulta'])) ? $_POST['id_proximaconsulta'] : '';

			$paciente='';
			if(isset($_POST['id_paciente']) and is_numeric($_POST['id_paciente'])) {
				$sql->consult($_p."pacientes","id,nome,telefone1","where id='".$_POST['id_paciente']."'");
				if($sql->rows) {
					$paciente=mysqli_fetch_object($sql->mysqry);
				}
			}

			if(is_object($paciente)) {


				if($tipo=="naoAtendeu" or $tipo=="retorno" or $tipo=="contato" or $tipo=="excluir" or $tipo=="pular" or $tipo=="resolvido") {

					// baseado na tabela ident_pacientes_historico_status
					$idObs=0;
					if($tipo=="naoAtendeu") $idObs=1;
					else if($tipo=="contato") $idObs=2;
					else if($tipo=="retorno") $idObs=3;
					else if($tipo=="pular") $idObs=4;
					else if($tipo=="excluir") $idObs=5;
					else if($tipo=="resolvido") $idObs=6;

					if($idObs>0) {

						$sql->consult($_p."pacientes_historico_status","*","where id=$idObs");
						if($sql->rows) {
							$d=mysqli_fetch_object($sql->mysqry);

							$obsDescricao=($d->titulo);
						}

						$vSQLHistorico="data=now(),
										id_paciente=$paciente->id,
										descricao='".utf8_decode(addslashes($obs))."',
										id_obs='$idObs',
										id_proximaconsulta='$id_proximaconsulta',
										evento='observacao',
										id_usuario=$usr->id";

						$sql->add($_p."pacientes_historico",$vSQLHistorico);

						$rtn=array('success'=>true);

					 
					} else {
						$rtn=array('success'=>false,'error'=>'Observação não encontrado!');
					}
				} else if($tipo=="desativar") {
					$sql->update($_p."pacientes","situacao='EXCLUIDO'","where id=$paciente->id");
					$rtn=array('success'=>true);
				} else if($tipo=="whatsapp") {
					$attr=array('prefixo'=>$_p,'usr'=>$usr);
					$wts = new Whatsapp($attr);

					$attr=array('id_tipo'=>4,
								'id_paciente'=>$paciente->id,
								'cronjob'=>0);


					if($wts->adicionaNaFila($attr)) {
						$rtn=array('success'=>true,'numero'=>mask($paciente->telefone1));
					}
					else {
						$rtn=array('success'=>false,'error'=>isset($wts->erro)?$wts->erro:'Algum erro ocorreu. Tente novamente!');
					}
				} else {
					$rtn=array('success'=>false,'error'=>'Ação não compreendida');
				}

			} else {
				$rtn=array('success'=>false,'error'=>'Paciente não encontrado!');
			}
		}

		else if($_POST['ajax']=="ocupacaoListar") {
			$data = isset($_POST['data'])?$_POST['data']:'';

			$erro='';
			if(empty($data)) $erro='Data não definida!';

			if(empty($erro)) {

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

				$sql->consult($_p."agenda","id,id_cadeira,agenda_data,agenda_duracao","where agenda_data>='".date('Y-m-01')." 00:00:00' and agenda_data<='".date('Y-m-t')." 23:59:59' and id_status IN (1,2,5,7) and lixo=0");
				while($x=mysqli_fetch_object($sql->mysqry)) {

						$dia = date('d',strtotime($x->agenda_data));

						if(!isset($_agendaHorasMes[$x->id_cadeira][$dia])) $_agendaHorasMes[$x->id_cadeira][$dia]=0;
						$_agendaHorasMes[$x->id_cadeira][$dia]+=$x->agenda_duracao;

						if(date('Y-m-d',strtotime($x->agenda_data))==$data) {
							if(!isset($_agendaHoras[$x->id_cadeira])) $_agendaHoras[$x->id_cadeira]=0;
							$_agendaHoras[$x->id_cadeira]+=$x->agenda_duracao;
						}
				}


				$_cadeiras=array();
				$sql->consult($_p."parametros_cadeiras","id,titulo","where lixo=0 order by ordem asc");
				while($x=mysqli_fetch_object($sql->mysqry)) $_cadeiras[$x->id]=$x;


				$cadeiras=array();
				foreach($_cadeiras as $c) {
					$cadeiraHoras = isset($_horas[$c->id]) ? $_horas[$c->id] : 0;
					$agendaHoras = isset($_agendaHoras[$c->id]) ? $_agendaHoras[$c->id] : 0;

					$indice = ceil($cadeiraHoras==0?100:($agendaHoras/$cadeiraHoras)*100);

					$cadeiras[]=array('id'=>$c->id,
										'titulo'=>utf8_encode($c->titulo),
										'cadeiraHoras'=>$cadeiraHoras,
										'agendaHoras'=>$agendaHoras,
										'indice'=>$indice);
				}



				$rtn=array('success'=>true,'cadeiras'=>$cadeiras);

			} else {
				$rtn=array('success'=>false,'error'=>$erro);
			}
		}

		else if($_POST['ajax']=="ocupacaoHorarios") {

			$data = isset($_POST['data'])?$_POST['data']:'';

			$cadeira='';
			if(isset($_POST['id_cadeira']) and is_numeric($_POST['id_cadeira'])) {
				$sql->consult($_p."parametros_cadeiras","*","where id=".$_POST['id_cadeira']);
				if($sql->rows) {
					$cadeira=mysqli_fetch_object($sql->mysqry);
				}
			}

			$erro='';
			if(empty($data)) $erro='Data não definida!';
			else if(empty($cadeira)) $erro='Cadeira não definida';

			if(empty($erro)) {
				$horarios=array();

				class Interval {
				    // TODO: use getter/setters instead of public
				    public $from; // in minutes, included
				    public $to;   // in minutes, excluded

				    public function __construct($from, $to) {
				        $this->from = $from;
				        $this->to = $to;
				    }
				}


				// busca agendamentos que existe para a data selecionada
				$schedule=array();
				$agendamentos=array();
				$sql->consult($_p."agenda","*","where agenda_data>='".$data." 000:00:00' and agenda_data<='".$data." 23:59:59' and id_cadeira=$cadeira->id and lixo=0 and id_status IN (1,2,3,5,6,7) order by agenda_data asc");
				while($x=mysqli_fetch_object($sql->mysqry)) {
					$inicio=(date('H:i',strtotime($x->agenda_data)));
					$fim=(date('H:i',strtotime($x->agenda_data." + $x->agenda_duracao minutes")));
					$schedule[] = new Interval(strtotime($inicio), strtotime($fim));
					$agendamentos[]=$inicio." - ".$fim;
				}


				// busca os horarios de atendimento da cadeira
				$dia = date('w',strtotime($data));
				$intervals=array();
				$horariosDaCadeira=array();
				$where="where id_cadeira=$cadeira->id and dia=$dia and lixo=0 order by data asc";
				$sql->consult($_p."parametros_cadeiras_horarios","*,date_format(inicio,'%H:%i') as inicio,date_format(fim,'%H:%i') as fim",$where);
				
				$horarioFinal=0;
				while($x=mysqli_fetch_object($sql->mysqry)) {
				
					$inpInicio=strtotime($x->inicio);
					$inpFim=strtotime($x->fim);

					// capta o ultimo horario
					if($inpFim>$horarioFinal) $horarioFinal=$inpFim;

					// horarios da agenda
					$intervals[] = new Interval($inpInicio, $inpFim);

					$horariosDaCadeira[]="$x->inicio - $x->fim";

				}



				$newInterval=array();
				// busca intervalos nos horarios da cadeira que estão disponíveis
				foreach( $schedule as $item ) {
				    foreach($intervals as $interval) {
				        // TODO: the algorithm assumes there are no overlapping items in the schedule

				    	//echo date('H:i',$interval->from)." <= ".date('H:i',$item->from)." && ".date('H:i',$interval->to)." >= ".date('H:i',$item->to)." ";
				    	// se intercede
				        if ($interval->from <= $item->from && $interval->to >= $item->to) {

				            if ($interval->from == $item->from) {
				            	//echo  "a";
				                $interval->from = $item->to;
				            } else if ($interval->to == $item->to) {
				            	//echo  "a2";
				                $interval->to = $item->from;
				            } else {

				            	
				                $intervals[] = new Interval($item->to, $interval->to);
				                $interval->to = $item->from;
				            }
				            break;

				        } else {

				        }
				    }
				}

				foreach($intervals as $interval) {
					if($interval->from!=$interval->to and $interval->from<$interval->to) {
						$horarios[]=date('H:i',$interval->from)." - ".date('H:i',$interval->to);
					}
				}

				$rtn=array('success'=>true,
							'cadeira'=>$horariosDaCadeira,
							'agendamentos'=>$agendamentos,
							'horarios'=>$horarios);
			} else {
				$rtn=array('success'=>false,'error'=>$erro);
			}
		}


		header("Content-type: application/json");
		echo json_encode($rtn);
		die();

	}
	include "includes/header.php";
	include "includes/nav.php";
	if($usr->tipo!="admin" and !in_array("inteligencia",$_usuariosPermissoes)) {
		$jsc->jAlert("Você não tem permissão para acessar esta área!","erro","document.location.href='dashboard.php'");
		die();
	}
	$_table=$_p."colaboradores";

	$_historicoStatus=array();
	$sql->consult($_p."pacientes_historico_status","*","");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_historicoStatus[$x->id]=$x;
	}
	$_profissionais=array();
	$sql->consult($_p."colaboradores","id,nome,calendario_iniciais,foto,calendario_cor,check_agendamento","where lixo=0 order by nome asc");
	while($x=mysqli_fetch_object($sql->mysqry)) $_profissionais[$x->id]=$x;


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
						<?php

						?>
						<p>Você tem <b class="js-disponiveis">...</b> sugestões disponível(s) pela data e <b class="js-indisponiveis">...</b> ainda não disponível(s)</p>
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

					<?php /*<section class="tab">
						<a href="javascript:;" class="active js-btn-grafico" data-tipo="cadeiras">Cadeiras</a>
						<a href="javascript:;" class="js-btn-grafico" data-tipo="dentistas">Dentistas</a>					
					</section>*/?>

					

					<div class="list4 js-ocupacao-cadeiras">
						<?php
						/*foreach($_cadeiras as $c) {

							$cadeiraHoras = isset($_horas[$c->id]) ? $_horas[$c->id] : 0;
							$agendaHoras = isset($_agendaHoras[$c->id]) ? $_agendaHoras[$c->id] : 0;

							$indice = ceil($cadeiraHoras==0?100:($agendaHoras/$cadeiraHoras)*100);
						?>
						<a href="javascript:;" class="list4-item js-ocupacao-cadeira" data-id_cadeira="<?php echo $c->id;?>">
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
						}*/
						?>
						<div style="margin-bottom:var(--margin1);" class="js-ocupacao-horarios">
							<a href="" class="button button_disabled">08:00 - 09:00</a>
							<a href="" class="button button_disabled">10:00 - 13:00</a>
							<a href="" class="button button_disabled">15:00 - 16:00</a>
							<a href="" class="button button_disabled">17:30 - 18:00</a>
						</div>
					</div>
				</div>

				<script type="text/javascript">
					
					/*
					var pacientesDesmarcados = JSON.parse(`<?php echo json_encode($desmarcadosPacientesAgendaJSON);?>`);
					var pacientesRetorno = JSON.parse(`<?php echo json_encode($retornoPacientesAgendaJSON);?>`);
					var pacientesExcluidos = JSON.parse(`<?php echo json_encode($pacientesExcluidosJSON);?>`);
					*/
				


					var pacientesOportunidades = [];
					var pacientesRetorno = [];
					var pacientesExcluidos = [];

					var pacientes = [];
					var pagina = 0;
					var paginaReg = 1;
					var paginaQtd = 0;
					var dataOcupacao = '<?php echo $data;?>';

					const ocupacaoListar = () => {

						let data = `ajax=ocupacaoListar&data=${dataOcupacao}`;
						

						$.ajax({
							type:"POST",
							data:data,
							success:function(rtn) {
								if(rtn.success) {
									$('.js-ocupacao-cadeiras').html('');

									rtn.cadeiras.forEach(x=>{
										$('.js-ocupacao-cadeiras').append(`<a href="javascript:;" class="list4-item js-ocupacao-cadeira" data-id_cadeira="${x.id}">
												<div>
													<h1>${x.indice<0?rtn.indice:(x.indice==0?0:x.indice)}%</h1>
												</div>
												<div>
													<p>
														${x.titulo}
														<br /><span style="font-size:12px;color:var(--cinza4)"><span class="iconify" data-icon="bi:clock-history" style=""></span>&nbsp;&nbsp;${x.agendaHoras} / ${x.cadeiraHoras}min</span>
													</p>
												</div>
											</a>
											`);
									});

									$('.js-ocupacao-cadeiras').append(`<div style="margin-bottom:var(--margin1);" class="js-ocupacao-horarios">
																		</div>`);

									$('.js-ocupacao-cadeiras a:eq(0)').click();
								}
							}
						})
					}

					const atualizaValorListasInteligentes = () => {

						let data = `ajax=gestaoDoTempo`;

						$.ajax({
							type:"POST",
							data:data,
							success:function(rtn) {
								if(rtn.success) {

									pacientesOportunidades = rtn.pacientes;

									$('.js-disponiveis').html(rtn.pacientes.length);
									$('.js-indisponiveis').html(rtn.indisponiveis);


									

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


						let status = $('.js-filtro-status option:selected').val();

						if(status>0) {
							pacientes = pacientes.filter(x=>{return x.status==status});
						}


						let profissional = $('.js-filtro-profissional option:selected').val();
						if(profissional>0) {
							pacientes = pacientes.filter(x=>{return x.id_profissional==profissional});
						}

						if(pacientes.length==0) {
							$('#js-inteligencia-paciente').hide();
							$('.js-nenhumpaciente').show(); 
						} else {
							$('#js-inteligencia-paciente').show();
							$('.js-nenhumpaciente').hide(); 

							$('.js-paginacao,.js-guia').show();
							paginaQtd =  Math.ceil(pacientes.length/paginaReg);

							for (var i = pagina * paginaReg; i < pacientes.length && i < (pagina + 1) * paginaReg; i++) {

								x = pacientes[i];

								let icone = ``;

								// nao conseguiu contato 
								if(x.id_obs==1) {
									icone=`<i class="iconify" data-icon="fluent:call-dismiss-24-regular" style="font-size:2em;"></i>`;
								} 
								// paciente entrara em contato
								else if(x.id_obs==2) {
									icone=`<i class="iconify" data-icon="fluent:call-inbound-24-regular" style="font-size:2em;"></i>`;
								} 
								// paciente pediu para retornar posteriormente
								else if(x.id_obs==3) {
									icone=`<i class="iconify" data-icon="fluent:call-missed-24-regular" style="font-size:2em;"></i>`;
								}
								// pulou sugestao
								else if(x.id_obs==4) { 
									icone=`<i class="iconify" data-icon="fluent:skip-forward-tab-24-filled" style="font-size:2em;"></i>`;
								}
								// excluiu
								else if(x.id_obs==5) {
									icone=`<i class="iconify" data-icon="fluent:delete-24-regular" style="font-size:2em;"></i>`;
								}

								$('#js-inteligencia-paciente .js-id_obs').html(icone);

								let lista=``;
								if(filtro=="excluidos") {
									lista=x.lista;
								}



								let ft = (x.ft && x.ft.length>0)?x.ft:'img/ilustra-usuario.jpg';

								$('#js-inteligencia-paciente .js-nome').html(`${x.nome} <a href="pg_pacientes_resumo.php?id_paciente=${x.id_paciente}" target="_blank"><i class="iconify" data-icon="fluent:share-screen-person-overlay-20-regular" style="color:var(--cinza4)"></i></a>`);


								if(x.futuros.length==0) {
									$('#js-inteligencia-paciente .js-futuros').html('');
								} else {
									let agendamentos='Agendamentos Futuros:<br>';
									x.futuros.forEach(dt=>{ 
										agendamentos+=`${dt}<br>`;
									});
									//agendamentos=agendamentos.substr(0,agendamentos.length-2)
									$('#js-inteligencia-paciente .js-futuros').html(`<a href="javascript:;" class="js-futuros-agendamentos" title="${agendamentos}"><i class="iconify" data-icon="fluent:calendar-checkmark-24-regular"></i></a>`);
									$('#js-inteligencia-paciente .js-futuros-agendamentos').tooltipster({contentAsHTML:true});

								}

								$('#js-inteligencia-paciente .js-id_paciente').val(x.id_paciente);
								$('#js-inteligencia-paciente .js-id_proximaconsulta').val(x.id_proximaconsulta);

								if(x.bi.length>0) {
									$('#js-inteligencia-paciente .js-bi').html(x.bi).show();
								} else {
									$('#js-inteligencia-paciente .js-bi').html('').hide();
								}
 								$('#js-inteligencia-paciente .js-periodicidade').html(x.periodicidade+' meses');
								$('#js-inteligencia-paciente .js-idade').html(x.idade>1?x.idade+' anos':x.idade+' ano');
 								$('#js-inteligencia-paciente .js-telefone').html(x.telefone);
 								$('#js-inteligencia-paciente .js-ft').attr('src',ft);

 								$('#js-inteligencia-paciente .js-btn-queroAgendar').attr('href',`javascript:asideQueroAgendar(${x.id_paciente},${x.id_proximaconsulta})`);
 								

 								$('#js-inteligencia-paciente .js-atendimentos').html(x.atendimentos);
 								if(x.ultimoAtendimento.length==0) {
	 								$('#js-inteligencia-paciente .js-ultimoAtendimento').html('-');
 								} else {
	 								$('#js-inteligencia-paciente .js-ultimoAtendimento').html(x.ultimoAtendimento+'d');
	 							}
 								$('#js-inteligencia-paciente .js-tempoMedio').html(x.tempoMedio+'m');
 								$('#js-inteligencia-paciente .js-faltou').html(x.faltou);


 								if(x.proximaConsulta && x.proximaConsulta.duracao) {
 									$('#js-inteligencia-paciente .js-proxag .js-proxDuracao').html(`${x.proximaConsulta.duracao}m`);
 									$('#js-inteligencia-paciente .js-proxag .js-obs').html(x.proximaConsulta.obs);
 									$('#js-inteligencia-paciente .js-proxag .js-agendamento').html(x.proximaConsulta.dataProx);

 									if(x.proximaConsulta.laboratorio==1) {
 										$('#js-inteligencia-paciente .js-proxag .js-laboratorio').html(`Necessita de laboratório`).css("color","#ccc");
 									} else {
 										$('#js-inteligencia-paciente .js-proxag .js-laboratorio').html(`Não necessita de laboratório`).css("color","#666");
 									}

 									if(x.proximaConsulta.imagem==1) {
 										$('#js-inteligencia-paciente .js-proxag .js-imagem').html(`Necessita de imagem`).css("color","#ccc");
 									} else {
 										$('#js-inteligencia-paciente .js-proxag .js-imagem').html(`Não necessita de imagem`).css("color","#666");
 									}

 									$('#js-inteligencia-paciente .js-proxag .js-profissionais').html('');
 									if(x.proximaConsulta.profissionais.length>0) {
 										x.proximaConsulta.profissionais.forEach(p=>{
 											$('#js-inteligencia-paciente .js-proxag .js-profissionais').append(`${p.nome}<br />`)
 										})
 									}
 								} else {
 									$('#js-inteligencia-paciente .js-proxag .js-proxDuracao').html(`-`);
 									$('#js-inteligencia-paciente .js-proxag .js-obs').html('-');
 									$('#js-inteligencia-paciente .js-proxag .js-agendamento').html('-');
									$('#js-inteligencia-paciente .js-proxag .js-laboratorio').html('');
									$('#js-inteligencia-paciente .js-proxag .js-imagem').html('');
 									
 								}

							};

							$('.js-guia').html(`Página <b>${pagina+1}</b> de <b>${paginaQtd}</b>`);

							if(paginaQtd==1) {
								$('.js-guia,.js-paginacao').hide();
							} else {

								$('.js-guia,.js-paginacao').show().hide();
							}
						}
					}

					const btnRelacionamento = (obj) => {

						let tipo = obj.attr('data-tipo');
						let id_paciente = $('#js-inteligencia-paciente .js-id_paciente').val();
						let obs = $('#js-inteligencia-paciente .js-textarea-obs').val();
						let id_proximaconsulta = $('#js-inteligencia-paciente .js-id_proximaconsulta').val();
						
						let objTextoAntigo = obj.html();

						if(obj.attr('data-loading')==0) {

							obj.attr('data-loading',1);
							let data = `ajax=pacienteHistoricoObs&obs=${obs}&tipo=${tipo}&id_paciente=${id_paciente}&id_proximaconsulta=${id_proximaconsulta}`;

							$.ajax({
								type:"POST",
								data:data,
								success:function(rtn) {
									if(rtn.success) {

										
										$('#js-inteligencia-paciente .js-textarea-obs').val('');
										atualizaValorListasInteligentes();

									} else  {

										let erro = rtn.error ? rtn.error : 'Algum erro ocorreu!';
										swal({title: "Erro!", text: erro, type:"error", confirmButtonColor: "#424242"});	
										
									} 
								}
							}).done(function(){
								obj.attr('data-loading',0);
								obj.html(objTextoAntigo);
							});
						}
					}

					


					$(function(){
						ocupacaoListar();

						atualizaValorListasInteligentes();

						$('.js-ocupacao-cadeiras').on('click','.js-ocupacao-cadeira',function(){


							$('.js-ocupacao-cadeira').removeClass('active');
							$(this).addClass('active');
							let id_cadeira = $(this).attr('data-id_cadeira');

							let data = `ajax=ocupacaoHorarios&id_cadeira=${id_cadeira}&data=${dataOcupacao}`;

							$.ajax({
								type:"POST",
								data:data,
								success:function(rtn) {
									if(rtn.success) {	


										$('.js-ocupacao-horarios').html('');

										if(rtn.horarios.length>0) {
											rtn.horarios.forEach(x=>{
												$('.js-ocupacao-horarios').append(`<a href="javascript:;" class="button button_disabled" style="margin:1px;">${x}</a>`)
											})
										}

									} else if(rtn.error) {
										swal({title: "Erro!", text: rtn.error, type:"error", confirmButtonColor: "#424242"});	
									} else {
										swal({title: "Erro!", text: "Não foi possível identificar a taxa de ocupação desta cadeira. Por favor tente novamente!", type:"error", confirmButtonColor: "#424242"});
									}
								}
							})
						})

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
						});

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

						$('#js-inteligencia-paciente .js-btn-relacionamento').click(function(){
							var obj = $(this);
							let tipo = obj.attr('data-tipo');


							if(tipo=="pular") {
								$('.js-proximo').click();
								return;
							}
							let obs = $('#js-inteligencia-paciente .js-textarea-obs').val();

							if(obs.length==0) {
								swal({title: "Erro!", text: "Preencha o campo de Obervações", type:"error", confirmButtonColor: "#424242"});	
							} else {

								if(tipo=="excluir") {
									swal({
											title: "Atenção",
											text: "Esta opção irá ocultar esse paciente da lista. Deseja continuar?",
											type: "warning",
											showCancelButton: true,
											confirmButtonColor: "#DD6B55",
											confirmButtonText: "Sim!",
											cancelButtonText: "Não",
											closeOnConfirm: false,
											closeOnCancel: false 
										}, function(isConfirm){
											if (isConfirm) { 
												swal.close(); 
												btnRelacionamento(obj); 
											} else {   
												swal.close();   
											} 
									});
			
								} else {

									btnRelacionamento(obj);
								}
							}	
						});
					})

				</script>

				<div class="box box_inv js-nenhumpaciente" style="display:none">

					<center>Nenhum paciente</center>
				</div>

				<div class="box box_inv" id="js-inteligencia-paciente" style="display:none">
					
					<div class="filter">
						<div class="filter-group">
							<div class="filter-title">
								<h1 style="color:var(--cor1);">Sugestões Inteligentes</h1>
							</div>
						</div>
					</div>

					<section class="header-profile">
						<img src="img/ilustra-usuario.jpg" alt="" width="60" height="60" class="header-profile__foto js-ft" />
						<div class="header-profile__inner1">
							<h1 class="js-nome"></h1>
							<input type="hidden" class="js-id_paciente" />
							<input type="hidden" class="js-id_proximaconsulta" />
							<div>
								<p class="js-bi"></p>
								<p class="js-idade"></p>
								<p>Periodicidade: <strong class="js-periodicidade"></strong></p>
							</div>
						</div>
						
						<h1 class="js-id_obs"></h1>
						
						<div class="header-fone">
							<span class="js-futuros"></span>
							<i class="iconify" data-icon="fluent:call-connecting-20-regular"></i><p class="js-telefone"></p>
						</div>
					</section>

					<div class="list6">
						<?php /*<div class="list6-item">
							<h1>Atendimentos</h1>
							<h2 class="js-atendimentos"></h2>
						</div>
						<div class="list6-item">
							<h1>Último Atend.</h1>
							<h2 class="js-ultimoAtendimento"></h2>
						</div>
						<div class="list6-item">
							<h1>Tempo Médio</h1>
							<h2 class="js-tempoMedio"></h2>
						</div>
						<div class="list6-item">
							<h1>Faltou</h1>
							<h2 class="js-faltou"></h2>
						</div>*/?>
					</div>

					<div class="proxag js-proxag" style="flex:0 0 150px;">
						<header>
							<i class="iconify" data-icon="fluent:calendar-checkmark-24-regular"></i>
							<center><h1>Agendar em</h1></center>
							<p class="js-agendamento"></p>
						</header>
						<article>
							<p>Duração: <strong class="js-proxDuracao"></strong></p>
							<p class="js-laboratorio" style="font-size:12px;"></p>
							<p class="js-imagem"  style="font-size:12px;"></p>
						</article>
						<article>
							<p>Profissionais:</p>
							<p class="js-profissionais"></p>
						</article>
						<article>
							<p>Obs:</p>
							<p class="js-obs"></p>
						</article>
					</div>

					<div class="filter">

						<textarea placeholder="Observações..." style="height:80px;" class="js-textarea-obs"></textarea>
					</div>

					<div class="filter" style="margin-bottom:0;">
						<div class="filter-group">
							<div class="filter-form form">
								<dl>
									<dd><a href="javascript:;" class="button tooltip js-btn-relacionamento" data-tipo="resolvido" data-loading="0" title="Realizado"><i class="iconify" data-icon="clarity:success-line"></i></a></dd>
								</dl>
								<dl>
									<dd><a href="javascript:;" class="button tooltip js-btn-relacionamento" data-tipo="naoAtendeu" data-loading="0" title="Não atendeu"><i class="iconify" data-icon="fluent:call-dismiss-24-regular"></i></a></dd>
								</dl>
								<dl>
									<dd><a href="javascript:;" class="button tooltip js-btn-relacionamento" data-tipo="retorno" data-loading="0" title="Paciente pediu para retornar"><i class="iconify" data-icon="fluent:call-missed-24-regular"></i></a></dd>
								</dl>
								<dl>
									<dd><a href="javascript:;" class="button tooltip js-btn-relacionamento" data-tipo="contato" data-loading="0" title="Paciente entrará em contato"><i class="iconify" data-icon="fluent:call-inbound-24-regular"></i></a></dd>
								</dl>
								<dl>
									<dd><a href="javascript:;" class="button tooltip js-btn-relacionamento" data-tipo="pular" data-loading="0" title="Pular sugestão"><i class="iconify" data-icon="fluent:skip-forward-tab-24-filled"></i></a></dd>
								</dl>
								<dl>
									<dd><a href="javascript:;" class="button tooltip js-btn-relacionamento" data-tipo="excluir" data-loading="0" title="Excluir das sugestões"><i class="iconify" data-icon="fluent:delete-24-regular"></i></a></dd>
								</dl>
							</div>
						</div>
						<div class="filter-group">
							<div class="filter-form form">
								<dl>
									<dd><a href="javascript:;" class="button button_main js-btn-queroAgendar"><i class="iconify" data-icon="fluent:calendar-checkmark-24-regular"></i><span>Agendar</span></a></dd>
								</dl>
							</div>
						</div>


					</div>

					<div style="display:flex;flex-wrap: nowrap;justify-content:space-between;margin: 10px 10px 0px 10px;" class="js-paginacao">
						<a href="javascript:;" class="js-anterior"><span class="iconify" data-icon="akar-icons:circle-chevron-left-fill" data-height="25"></span></a>
						<span class="js-guia"></span>
						<a href="javascript:;" class="js-proximo"><span class="iconify" data-icon="akar-icons:circle-chevron-right-fill" data-height="25"></span></a>
					</div>

				</div>

				

			</section>
		
		</div>
	</main>

	

<?php 
	


	$apiConfig=array('queroAgendar'=>1);
	require_once("includes/api/apiAside.php");

	include "includes/footer.php";
?>	