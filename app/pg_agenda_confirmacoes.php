<?php
	$diasExtenso=array('(domingo)','(segunda-feira)','(terça-feira)','(quarta-feria)','(quinta-feira)','(sexta-feira)','(sábado)');
	if(isset($_POST['ajax'])) {

		require_once("lib/conf.php");
		require_once("usuarios/checa.php");

		$rtn = array();

		if($_POST['ajax']=="agenda") {
			$data='';
			if(isset($_POST['data']) and !empty($_POST['data'])) {
				list($ano,$mes,$dia)=explode("-",$_POST['data']);
				if(checkdate($mes, $dia, $ano)) $data=$_POST['data'];
			}


			if(!empty($data)) {


				$dataWH=date('Y-m-d');

				$_profissionais=array();
				$sql->consult($_p."colaboradores","id,nome,calendario_iniciais,foto,calendario_cor","where tipo_cro<>'' and lixo=0 order by nome asc");
				while($x=mysqli_fetch_object($sql->mysqry)) $_profissionais[$x->id]=$x;

				


				# Agendamentos a confirmar (hj, amanha e depois de amanha): id_status=1 -> a confirmar
					$where="where agenda_data>='".$dataWH." 00:00:00' and agenda_data<='".date('Y-m-d',strtotime($dataWH." + 2 day"))." 23:59:59' and id_status=1 and lixo=0 order by agenda_data asc";

					$registros=array();
					$pacientesIds=array(0);
					$sql->consult($_p."agenda","*",$where);
					while($x=mysqli_fetch_object($sql->mysqry)) {
						$registros[]=$x;
						$pacientesIds[]=$x->id_paciente;
						// ATENDIDO
						if($x->id_status==5) {
							$pacientesAtendidosIds[]=$x->id_paciente;
						}
					}


					$_pacientes=array();
					$sql->consult($_p."pacientes","id,nome,telefone1","where id IN (".implode(",",$pacientesIds).")");
					while($x=mysqli_fetch_object($sql->mysqry)) {
						$_pacientes[$x->id]=$x;
					}


					$hoje=date('Y-m-d');
					$amanha=date('Y-m-d',strtotime(date('Y-m-d')." + 1 day"));
					$depoisDeAmanha=date('Y-m-d',strtotime(date('Y-m-d')." + 2 day"));
					foreach($registros as $x) {
						if(isset($_pacientes[$x->id_paciente])) {

							$dataAg=date('d/m',strtotime($x->agenda_data));
							$dia=" ".$diasExtenso[date('w',strtotime($x->agenda_data))];

							$agendaData=date('Y-m-d',strtotime($x->agenda_data));

							$dias=round((strtotime(date('Y-m-d H:i:s'))-strtotime($x->data_atualizacao=="0000-00-00 00:00:00"?$x->data:$x->data_atualizacao))/(60 * 60 * 24));


							$idStatus='';
							if($agendaData==$hoje) {
								$idStatus='hoje';
							} else if($agendaData==$amanha) {
								$idStatus='amanha';
							} else if($agendaData==$depoisDeAmanha) {
								$idStatus="depoisDeAmanha";
							}

							$aux = explode(",",$x->profissionais);
							$profissionais=array();
							$idProfissional=0;
							foreach($aux as $id_profissional) {
								if(!empty($id_profissional) and is_numeric($id_profissional)) {

									if(isset($_profissionais[$id_profissional])) {
										if($idProfissional==0) $idProfissional=$id_profissional;
										$cor=$_profissionais[$id_profissional]->calendario_cor;
										$iniciais=$_profissionais[$id_profissional]->calendario_iniciais;

										$profissionais[]=array('iniciais'=>$iniciais,'cor'=>$cor);
									}
								}

							}
							$agenda[]=(object) array('id_agenda'=>$x->id,
														'data'=>$dataAg,
														'hora'=>date('H:i',strtotime($x->agenda_data))." às ".date('H:i',strtotime($x->agenda_data)),
														'id_status'=>$idStatus,
														'paciente'=>ucwords(strtolowerWLIB(utf8_encode($_pacientes[$x->id_paciente]->nome))),
														'telefone1'=>mask($_pacientes[$x->id_paciente]->telefone1),
														'evolucao'=>isset($pacientesEvolucoes[$x->id_paciente])?1:0,
														'dias'=>$dias,
														'idPr'=>$idProfissional,
														'idC'=>$x->id_cadeira,
														'aDur'=>$x->agenda_duracao,
														'procedimentos'=>'',
														'profissionais'=>$profissionais
													);
						}
					}

				# Agendamentos marcou/faltou
					$where="where agenda_data>='".date('Y-m-d',strtotime(date('Y-m-d')." - 30 day"))." 00:00:00' and agenda_data<='".date('Y-m-d')." 23:59:59' and id_status IN (3,4) and lixo=0 order by agenda_data asc";
					$registros=array();
					
					$sql->consult($_p."agenda","*",$where);
					$pacientesIds=array(0);
					while($x=mysqli_fetch_object($sql->mysqry)) {
						$registros[]=$x;
						$pacientesIds[]=$x->id_paciente;
						
					}


					$_pacientes=array();
					$where="where id IN (".implode(",",$pacientesIds).")";
					$sql->consult($_p."pacientes","id,nome,telefone1,codigo_bi",$where);
					while($x=mysqli_fetch_object($sql->mysqry)) {
						$_pacientes[$x->id]=$x;
					}

					$_agendamentosFuturos=array();
					$sql->consult($_p."agenda","*","where agenda_data>='".date('Y-m-d')." 00:00:00' and 
															id_paciente IN (".implode(",",$pacientesIds).") and 
															id_status IN (1,2,5) and lixo=0");
					while($x=mysqli_fetch_object($sql->mysqry)) {
						$_agendamentosFuturos[$x->id_paciente]=true;
					}


					$pacientesEmTratamentosSemHorarioIds=array(0);
					//$sql->consult($_p."pacientes_tratamentos_procedimentos","distinct id_paciente","where status_evolucao IN ('iniciar','iniciado') and lixo=0");
					$sql->consult($_p."pacientes","id","where codigo_bi=4 and lixo=0");
					while($x=mysqli_fetch_object($sql->mysqry)) {
						$pacientesEmTratamentosSemHorarioIds[$x->id]=$x->id;
					}

					$pacientesDeInteligencia=array();
					$sql->consult($_p."pacientes","id","where codigo_bi IN (2,5) and lixo=0 order by nome");
					while($x=mysqli_fetch_object($sql->mysqry)) {
						$pacientesDeInteligencia[]=$x->id;
						$pacientesEmTratamentosIds[$x->id]=$x->id;
						$pacientesEmContencaoSemHorarioIds[$x->id]=$x->id;
					}

					$agendasDosUltimos6meses=array();
					$sql->consult($_p."agenda","distinct id_paciente","where agenda_data > NOW() - INTERVAL 6 MONTH and id_status IN (5) and lixo=0 order by  agenda_data desc");
					while($x=mysqli_fetch_object($sql->mysqry)) {

						$agendasDosUltimos6meses[$x->id_paciente]=1;

					}


					$sql->consult($_p."pacientes","id,nome,telefone1,codigo_bi","where id IN (".implode(",",$pacientesEmTratamentosIds).") or id IN (".implode(",",$pacientesEmTratamentosSemHorarioIds).")");
					while($x=mysqli_fetch_object($sql->mysqry)) {

						$_pacientes[$x->id]=$x;
					}

					$pacienteObs=array();
					$sql->consult($_p."pacientes_historico","*","where id_paciente IN (".implode(",",$pacientesEmTratamentosSemHorarioIds).") and evento='observacao' and lixo=0 order by data desc");
					while($x=mysqli_fetch_object($sql->mysqry)) {
						if(!isset($pacienteObs[$x->id_paciente])) {
							$pacienteObs[$x->id_paciente]=$x;
						}
					}
					$sql->consult($_p."pacientes_historico","*","where id_paciente IN (".implode(",",$pacientesEmContencaoSemHorarioIds).") and evento='observacao' and lixo=0 order by data desc");
					while($x=mysqli_fetch_object($sql->mysqry)) {
						if(!isset($pacienteObs[$x->id_paciente])) {
							$pacienteObs[$x->id_paciente]=$x;
						}
					}


					$pacientesTratamentos=array();
					$sql->consult($_p."agenda","distinct id_paciente","where agenda_data>='".date('Y-m-d')." 00:00:00' and 
																				id_paciente IN (".implode(",",$pacientesEmTratamentosSemHorarioIds).") and 
																				id_status IN (1,2) and lixo=0");

					if($sql->rows) {
						while($x=mysqli_fetch_object($sql->mysqry)) {
							//echo $x->id_paciente."-";
							unset($pacientesEmTratamentosSemHorarioIds[$x->id_paciente]);
						}
					}

					$_historicoStatus=array();
					$sql->consult($_p."pacientes_historico_status","*","");
					while($x=mysqli_fetch_object($sql->mysqry)) {
						$_historicoStatus[$x->id]=$x;
					}

					$agendaTratamento=array();
					foreach($pacientesEmTratamentosSemHorarioIds as $id_paciente) {
						if(isset($_pacientes[$id_paciente])) {
							$paciente=$_pacientes[$id_paciente];
							if(is_object($paciente)) {
								$corObs="";
								if(isset($pacienteObs[$paciente->id])) {
									if(isset($_historicoStatus[$pacienteObs[$paciente->id]->id_obs])) {
										$h=$_historicoStatus[$pacienteObs[$paciente->id]->id_obs];
										$corObs=$h->cor;
									}
								}
								$agendaTratamento[]=array('id_paciente'=>$paciente->id,
															'corObs'=>$corObs,
															'nome'=>utf8_encode($paciente->nome),
															'telefone1'=>$paciente->telefone1);
							}
						} 
					}


					$agendaInteligencia=array();
					foreach($pacientesDeInteligencia as $id_paciente) {
						
						if(isset($agendasDosUltimos6meses[$id_paciente])) {
							//echo $id_paciente."\n";
							continue;
						}
						if(isset($_pacientes[$id_paciente])) {
							$paciente=$_pacientes[$id_paciente];

							if(is_object($paciente)) {
								if($paciente->codigo_bi==7) continue; // excluidos
								$ag=isset($_ulAg[$paciente->id])?$_ulAg[$paciente->id]:'';


								$corObs="";
								if(isset($pacienteObs[$paciente->id])) {
									if(isset($_historicoStatus[$pacienteObs[$paciente->id]->id_obs])) {
										$h=$_historicoStatus[$pacienteObs[$paciente->id]->id_obs];
										$corObs=$h->cor;
									}
								}

								$agendaInteligencia[]=array('id_paciente'=>$paciente->id,
															'corObs'=>$corObs,
															'nome'=>utf8_encode($paciente->nome).(is_object($ag)?'<br />'.$ag->agenda_data:''),
															'telefone1'=>$paciente->telefone1);
							}
						} 
					}



					foreach($registros as $x) {
						if(isset($_pacientes[$x->id_paciente])) {
							//echo $_pacientes[$x->id_paciente]->nome."\n";
							$dataAg=date('d/m',strtotime($x->agenda_data));
							$dia=" ".$diasExtenso[date('w',strtotime($x->agenda_data))];

							$agendaData=date('Y-m-d',strtotime($x->agenda_data));

							$dias=round((strtotime(date('Y-m-d H:i:s'))-strtotime($x->data_atualizacao=="0000-00-00 00:00:00"?$x->data:$x->data_atualizacao))/(60 * 60 * 24));


							$futuro=false;
							if(isset($_agendamentosFuturos[$x->id_paciente])) {
								$futuro=true;
								continue;
							}

							$id_profissional='';
							if(!empty($x->profissionais)) {
								$aux=explode(",",$x->profissionais);
								foreach($aux as $p) {
									if(!empty($p) and is_numeric($p)) $id_profissional=$p;
								}
							}


							$aux = explode(",",$x->profissionais);
							$profissionais=array();
							foreach($aux as $id_profissional) {
								if(!empty($id_profissional) and is_numeric($id_profissional)) {

									if(isset($_profissionais[$id_profissional])) {
										$cor=$_profissionais[$id_profissional]->calendario_cor;
										$iniciais=$_profissionais[$id_profissional]->calendario_iniciais;

										$profissionais[]=array('iniciais'=>$iniciais,'cor'=>$cor);
									}
								}

							}



							$agenda[]=(object) array('id_agenda'=>$x->id,
														'data'=>$dataAg,
														'agenda_hora'=>date('H:i',strtotime($x->agenda_data)),
														'agenda_data'=>date('d/m/Y',strtotime($x->agenda_data)),
														'agenda_duracao'=>$x->agenda_duracao,
														'id_profissional'=>$id_profissional,
														'id_cadeira'=>$x->id_cadeira,
														'id_status'=>'reagendar',
														'hora'=>date('H:i',strtotime($x->agenda_data))." às ".date('H:i',strtotime($x->agenda_data." + $x->agenda_duracao minutes")),
														'paciente'=>ucwords(strtolowerWLIB(utf8_encode($_pacientes[$x->id_paciente]->nome))),
														'telefone1'=>mask($_pacientes[$x->id_paciente]->telefone1),
														'evolucao'=>isset($pacientesEvolucoes[$x->id_paciente])?1:0,
														'dias'=>$dias,
														'procedimentos'=>'',
														'profissionais'=>$profissionais
													);
						}
					}


				$rtn=array('success'=>true,
							'agenda'=>$agenda,
							'agendaTratamento'=>$agendaTratamento,
							'agendaInteligencia'=>$agendaInteligencia);

			} else {
				$rtn=array('success'=>false,'error'=>'Data inválida!');
			}
		} /*else if ($_POST['ajax']=="alterarStatus") {

			$agenda = '';
			if(isset($_POST['id_agenda']) and is_numeric($_POST['id_agenda'])) {
				$sql->consult($_p."agenda","*","where id='".$_POST['id_agenda']."'");
				if($sql->rows) { 
					$agenda=mysqli_fetch_object($sql->mysqry);
				}
			}


			$status = '';
			if(isset($_POST['id_status']) and is_numeric($_POST['id_status'])) {
				$sql->consult($_p."agenda_status","*","where id='".$_POST['id_status']."'");
				if($sql->rows) { 
					$status=mysqli_fetch_object($sql->mysqry);
				}
			}


			if(is_object($agenda)) {
				if(is_object($status)) {

					$vSQL="id_status=$status->id,data_atualizacao=now()";
					$vWHERE="where id=$agenda->id";

					$sql->update($_p."agenda",$vSQL,$vWHERE);

					$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='update',vwhere='".addslashes($vWHERE)."',vsql='".addslashes($vSQL)."',tabela='".$_p."agenda',id_reg='".$agenda->id."'");


					$rtn=array('success'=>true);

				} else {
					$rtn=array('success'=>false,'error'=>'Status não encontrado');
				}
			} else {	
				$rtn=array('success'=>false,'error'=>'Agendamento não encontrado');
			}
		} */ else if($_POST['ajax']=="confirmarAgendamento") {
			$agenda = '';
			if(isset($_POST['id_agenda']) and is_numeric($_POST['id_agenda'])) {
				$sql->consult($_p."agenda","*","where id='".$_POST['id_agenda']."'");
				if($sql->rows) { 
					$agenda=mysqli_fetch_object($sql->mysqry);
				}
			}

			if(is_object($agenda)) {

				$vSQL="id_status=2,data_atualizacao=now()";
				$vWHERE="where id=$agenda->id";

				$sql->update($_p."agenda",$vSQL,$vWHERE);

				$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='update',vwhere='".addslashes($vWHERE)."',vsql='".addslashes($vSQL)."',tabela='".$_p."agenda',id_reg='".$agenda->id."'");

				$rtn=array('success'=>true);

			} else {	
				$rtn=array('success'=>false,'error'=>'Agendamento não encontrado');
			}
		} else if($_POST['ajax']=="cancelarAgendamento") {

			$agenda = '';
			if(isset($_POST['id_agenda']) and is_numeric($_POST['id_agenda'])) {
				$sql->consult($_p."agenda","*","where id='".$_POST['id_agenda']."'");
				if($sql->rows) { 
					$agenda=mysqli_fetch_object($sql->mysqry);
				}
			}

			if(is_object($agenda)) {

				$cancelamentoMotivo=isset($_POST['motivo'])?addslashes(utf8_decode($_POST['motivo'])):'';

				$vSQL="id_status=4,cancelamento_motivo='".$cancelamentoMotivo."',data_atualizacao=now()";
				$vWHERE="where id=$agenda->id";

				$sql->update($_p."agenda",$vSQL,$vWHERE);


				$vSQLHistorico="data=now(),
									id_usuario=$usr->id,
									id_paciente=$agenda->id_paciente,
									id_agenda=$agenda->id,
									id_status_antigo=$agenda->id_status,
									id_status_novo=4,
									descricao='".$cancelamentoMotivo."'";
				$sql->add($_p."pacientes_historico",$vSQLHistorico);

				$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='update',vwhere='".addslashes($vWHERE)."',vsql='".addslashes($vSQL)."',tabela='".$_p."agenda',id_reg='".$agenda->id."'");

				$rtn=array('success'=>true);

			} else {	
				$rtn=array('success'=>false,'error'=>'Agendamento não encontrado');
			}
		} else if($_POST['ajax']=="horarioDisponivel") {
			
			$agenda = '';
			if(isset($_POST['id_agenda']) and is_numeric($_POST['id_agenda'])) {
				$sql->consult($_p."agenda","*","where id='".$_POST['id_agenda']."'");
				if($sql->rows) { 
					$agenda=mysqli_fetch_object($sql->mysqry);
				}
			}

			$data = '';
			if(isset($_POST['data']) and !empty($_POST['data'])) {
				list($dia,$mes,$ano)=explode("/",$_POST['data']);
				if(checkdate($mes, $dia, $ano)) { 
					$data="$ano-$mes-$dia";
				}
 			}

 			$profissional = '';
 			if(isset($_POST['id_profissional']) and is_numeric($_POST['id_profissional'])) {
 				$sql->consult($_p."colaboradores","*","where id='".$_POST['id_profissional']."'");
 				if($sql->rows) {
 					$profissional=mysqli_fetch_object($sql->mysqry);
 				}
 			}

 			$cadeira = '';
 			if(isset($_POST['id_cadeira']) and is_numeric($_POST['id_cadeira'])) {
 				$sql->consult($_p."parametros_cadeiras","*","where id='".$_POST['id_cadeira']."' and lixo=0");
 				if($sql->rows) {
 					$cadeira=mysqli_fetch_object($sql->mysqry);
 				}
 			}

			$tempo = (isset($_POST['tempo']) and is_numeric($_POST['tempo']))?$_POST['tempo']:0;

			if(is_object($agenda) or empty($agenda)) {
				if(!empty($data)) {
					if(is_object($profissional)) {
						if(is_object($cadeira)) {
							$dataInicio=$data." 07:00:00";	
							$dataFim=$data." 23:59:59";

							//echo $dataInicio." - $dataFim -> $tempo\n\n";
							$horariosDisponiveis=array();
							do {
								$di=$dataInicio;
								$dataInicio=date('Y-m-d H:i:s',strtotime($dataInicio." + $tempo minutes"));
								$df=$dataInicio;

								$where="WHERE agenda_data='".$di."' and 
												profissionais like '%,$profissional->id,%' and 
												id_cadeira=$cadeira->id and id_status NOT IN (3,4) and lixo=0";

								$where="WHERE (agenda_data>='$data 00:00:00' and agenda_data<='$data 23:59:59') and
												(('$di'<agenda_data && '$df'<DATE_ADD(agenda_data, INTERVAL $tempo MINUTE)) or 
												('$di'>agenda_data && '$df'>DATE_ADD(agenda_data, INTERVAL $tempo MINUTE))) and
												profissionais like '%,$profissional->id,%' and 
												id_cadeira=$cadeira->id and id_status NOT IN (3,4) and lixo=0";
												//echo $where;die();

								$where="WHERE (agenda_data>='$data 00:00:00' and agenda_data<='$data 23:59:59') and 
												(
													('$di'<=agenda_data && '$df'>=DATE_ADD(agenda_data, INTERVAL agenda_duracao MINUTE)) or 
													('$di'>=agenda_data && '$df'<=DATE_ADD(agenda_data, INTERVAL agenda_duracao MINUTE)) or 
													('$di'<=agenda_data && '$df'>agenda_data && '$df'<=DATE_ADD(agenda_data, INTERVAL agenda_duracao MINUTE))
												)";

								$where.=" and profissionais like '%,$profissional->id,%' and id_status NOT IN (3,4) and lixo=0";
								$sql->consult($_p."agenda","agenda_data,DATE_ADD(agenda_data, INTERVAL $tempo MINUTE) as agenda_data_fim,agenda_duracao",$where);
								//echo $where."->".$sql->rows."\n";
								//$x=mysqli_fetch_object($sql->mysqry);
								//echo $x->agenda_data." - ".$x->agenda_data_fim." -> ".$x->agenda_duracao;die();
								if($sql->rows==0) {
									$where="WHERE (agenda_data>='$data 00:00:00' and agenda_data<='$data 23:59:59') and 
												(
													('$di'<=agenda_data && '$df'>=DATE_ADD(agenda_data, INTERVAL agenda_duracao MINUTE)) or 
													('$di'>=agenda_data && '$df'<=DATE_ADD(agenda_data, INTERVAL agenda_duracao MINUTE)) or 
													('$di'<=agenda_data && '$df'>agenda_data && '$df'<=DATE_ADD(agenda_data, INTERVAL agenda_duracao MINUTE))
												)";

									$where.=" and id_cadeira=$cadeira->id and id_status NOT IN (3,4) and lixo=0";
									$sql->consult($_p."agenda","agenda_data,DATE_ADD(agenda_data, INTERVAL $tempo MINUTE) as agenda_data_fim,agenda_duracao",$where);
									if($sql->rows==0) {
										$horariosDisponiveis[]=date('H:i',strtotime($di));
									}
								}
								//echo $where." -> $sql->rows\n";
								//while($x=mysqli_fetch_object($sql->mysqry)) {
								//	echo $x->agenda_data." - $x->agenda_data_final\n";
								//}
							} while(strtotime($dataInicio)<strtotime($dataFim));

							
							$rtn=array('success'=>true,'horariosDisponiveis'=>$horariosDisponiveis);


						} else {
							$rtn=array('success'=>false,'error'=>'Cadeira/Consultório não encontrado!');
						}
					} else {
						$rtn=array('success'=>false,'error'=>'Profissional não encontrado!');
					}
				} else {
					$rtn=array('success'=>false,'error'=>'Data não válida');
				}
			} else {
				$rtn=array('success'=>false,'error'=>'Agendamento não encontrado');
			}
		} else if($_POST['ajax']=="reagendar") {

			$erro='';

			$agenda = '';
			if(isset($_POST['id_agenda']) and is_numeric($_POST['id_agenda'])) {
				$sql->consult($_p."agenda","*","where id='".$_POST['id_agenda']."'");
				if($sql->rows) { 
					$agenda=mysqli_fetch_object($sql->mysqry);
				}
			}
			
			$agendaData='';
			if(isset($_POST['agenda_data']) and !empty($_POST['agenda_data'])) {
				list($dia,$mes,$ano)=explode("/",$_POST['agenda_data']);
				if(checkdate($mes, $dia, $ano)) {
					$agendaData=$ano."-".$mes."-".$dia;

					if(isset($_POST['horario']) and !empty($_POST['horario']) and strlen($_POST['horario'])==5) {
						$agendaData.=" ".$_POST['horario'];
					}
				}
			}

			$profissional='';
			if(isset($_POST['id_profissional']) and is_numeric($_POST['id_profissional'])) {
				$sql->consult($_p."colaboradores","id,nome","where id='".$_POST['id_profissional']."'");
				if($sql->rows) {
					$profissional=mysqli_fetch_object($sql->mysqry);
				}
			}

			$cadeira='';
			if(isset($_POST['id_cadeira']) and is_numeric($_POST['id_cadeira'])) {
				$sql->consult($_p."parametros_cadeiras","*","where id='".$_POST['id_cadeira']."' and lixo=0");
				if($sql->rows) {
					$cadeira=mysqli_fetch_object($sql->mysqry);
				}
			}

			$tempo='';
			if(isset($_POST['tempo']) and is_numeric($_POST['tempo'])) {
				$tempo=$_POST['tempo'];
			}

			if(empty($agenda)) $erro='Agendamento não encontrado';
			else if(empty($agendaData)) $erro='Data/horário não definidos';
			else if(empty($profissional)) $erro='Profissional não encontrado';
			else if(empty($cadeira)) $erro='Cadeira não encontrada!';
			else if(empty($tempo)) $erro='Tempo não definido!';


			if(empty($erro)) {

				$agendaFinal=date('Y-m-d H:i:s',strtotime($agendaData." + $tempo minutes"));

				$vSQL="agenda_data='".$agendaData."',
						agenda_duracao='".$tempo."',
						agenda_data_final='".$agendaFinal."',
						id_cadeira='".$cadeira->id."',
						data_atualizacao=now()";


				// verifica se alterou profissional
				$aux=explode(",",$agenda->profissionais);
				$alterou=true;
				foreach($aux as $idP) {
					if($idP==$profissional->id) {
						$alterou=false;
					}
				}

				if($alterou===true) {
					$vSQL.=",profissionais=',$profissional->id,'";
				} 

				$sql->update($_p."agenda",$vSQL,"where id=$agenda->id");

				if(strtotime($agenda->agenda_data)!=strtotime($agendaData)) {
					$vSQLHistorico="data=now(),
						id_usuario=$usr->id,
						evento='agendaHorario',
						id_paciente=$agenda->id_paciente,
						id_agenda=$agenda->id,
						agenda_data_antigo='$agenda->agenda_data',
						agenda_data_novo='$agendaData',
						descricao=''";
					$sql->add($_p."pacientes_historico",$vSQLHistorico);
				}

				$rtn=array('success'=>true);


			} else {
				$rtn=array('success'=>false,'error'=>$erro);
			}			
		} else if($_POST['ajax']=="agendar") {


			$erro='';

			$paciente = '';
			if(isset($_POST['id_paciente']) and is_numeric($_POST['id_paciente'])) {
				$sql->consult($_p."pacientes","*","where id='".$_POST['id_paciente']."'");
				if($sql->rows) { 
					$paciente=mysqli_fetch_object($sql->mysqry);
				}
			}
			
			$agendaData='';
			if(isset($_POST['agenda_data']) and !empty($_POST['agenda_data'])) {
				list($dia,$mes,$ano)=explode("/",$_POST['agenda_data']);
				if(checkdate($mes, $dia, $ano)) {
					$agendaData=$ano."-".$mes."-".$dia;

					if(isset($_POST['horario']) and !empty($_POST['horario']) and strlen($_POST['horario'])==5) {
						$agendaData.=" ".$_POST['horario'];
					}
				}
			}

			$profissional='';
			if(isset($_POST['id_profissional']) and is_numeric($_POST['id_profissional'])) {
				$sql->consult($_p."colaboradores","id,nome","where id='".$_POST['id_profissional']."'");
				if($sql->rows) {
					$profissional=mysqli_fetch_object($sql->mysqry);
				}
			}

			$cadeira='';
			if(isset($_POST['id_cadeira']) and is_numeric($_POST['id_cadeira'])) {
				$sql->consult($_p."parametros_cadeiras","*","where id='".$_POST['id_cadeira']."' and lixo=0");
				if($sql->rows) {
					$cadeira=mysqli_fetch_object($sql->mysqry);
				}
			}

			$tempo='';
			if(isset($_POST['tempo']) and is_numeric($_POST['tempo'])) {
				$tempo=$_POST['tempo'];
			}

			if(empty($paciente)) $erro='Paciente não encontrado';
			else if(empty($agendaData)) $erro='Data/horário não definidos';
			else if(empty($profissional)) $erro='Profissional não encontrado';
			else if(empty($cadeira)) $erro='Cadeira não encontrada!';
			else if(empty($tempo)) $erro='Tempo não definido!';


			if(empty($erro)) {

				$agendaFinal=date('Y-m-d H:i:s',strtotime($agendaData." + $tempo minutes"));
				$idStatusNovo=1; // a confirmar

				$vSQL="id_status=$idStatusNovo,
						id_unidade=1,
						id_paciente=$paciente->id,
						agenda_data='".$agendaData."',
						agenda_duracao='".$tempo."',
						agenda_data_final='".$agendaFinal."',
						id_cadeira='".$cadeira->id."',
						data_atualizacao=now(),
						data=now(),
						id_usuario=$usr->id,
						profissionais=',$profissional->id,'";
				
				$sql->consult($_p."agenda","id","where id_paciente=$paciente->id and 
														agenda_data='".$agendaData."' and 
														agenda_duracao='".$tempo."' and
														id_cadeira='".$cadeira->id."' and 
														lixo=0");
				if($sql->rows==0) {
					$sql->add($_p."agenda",$vSQL);
					$id_agenda=$sql->ulid;

					$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='insert',vsql='".addslashes($vSQL)."',tabela='".$_p."agenda',id_reg='".$id_agenda."'");

					$vSQLHistorico="data=now(),
										id_usuario=$usr->id,
										evento='agendaNovo',
										id_paciente=".$paciente->id.",
										id_agenda=$id_agenda,
										id_status_antigo=0,
										id_status_novo=".$idStatusNovo;
					$sql->add($_p."pacientes_historico",$vSQLHistorico);
					
				}

				$rtn=array('success'=>true);


			} else {
				$rtn=array('success'=>false,'error'=>$erro);
			}			
		} else if($_POST['ajax']=="naoQueroAgendar") {
			$paciente = '';
			if(isset($_POST['id_paciente']) and is_numeric($_POST['id_paciente'])) {
				$sql->consult($_p."pacientes","*","where id='".$_POST['id_paciente']."'");
				if($sql->rows) { 
					$paciente=mysqli_fetch_object($sql->mysqry);
				}
			}

			$obs = isset($_POST['obs'])?$_POST['obs']:'';

			$status='';
			if(isset($_POST['id_status']) and is_numeric($_POST['id_status'])) {
				$sql->consult($_p."pacientes_historico_status","*","where id='".$_POST['id_status']."'");
				if($sql->rows) {
					$status=mysqli_fetch_object($sql->mysqry);
				}
			}

			$erro='';
			if(empty($paciente)) $erro='Paciente não encontrado!';
			else if(empty($status)) $erro='Motivo não selecionado!';
			
			if(empty($erro)) {
				$vSQL="data=now(),
						evento='observacao',
						id_paciente=$paciente->id,
						id_agenda=0,
						id_obs=$status->id,
						descricao='".addslashes(utf8_decode($obs))."',
						id_usuario=$usr->id";

				$sql->add($_p."pacientes_historico",$vSQL);

				$rtn=array('success'=>true);
			} else {
				$rtn=array('success'=>false,'error'=>$erro);
			}
		} else if($_POST['ajax']=="historico") {

			$paciente = '';
			if(isset($_POST['id_paciente']) and is_numeric($_POST['id_paciente'])) {
				$sql->consult($_p."pacientes","*","where id='".$_POST['id_paciente']."'");
				if($sql->rows) { 
					$paciente=mysqli_fetch_object($sql->mysqry);
				}
			}


			if(is_object($paciente)) {

				$_usuarios=array();
				$sql->consult($_p."colaboradores","id,nome,calendario_iniciais,foto,calendario_cor","where lixo=0 order by nome asc");
				while($x=mysqli_fetch_object($sql->mysqry)) {
					$_usuarios[$x->id]=$x;
				}

				$_historicoStatus=array();
				$sql->consult($_p."pacientes_historico_status","*","");
				while($x=mysqli_fetch_object($sql->mysqry)) {
					$_historicoStatus[$x->id]=$x;
				}

				$_historico=array();
				$sql->consult($_p."pacientes_historico","*","where id_paciente=$paciente->id and evento='observacao' and lixo=0 order by data desc");
			
				while($x=mysqli_fetch_object($sql->mysqry)) {

					if(isset($_usuarios[$x->id_usuario])) {

						$_historico[]=array('usr'=>utf8_encode($_usuarios[$x->id_usuario]->nome),
															'dt'=>date('d/m H:i',strtotime($x->data)),
															'ev'=>'observacao',
															'obs'=>isset($_historicoStatus[$x->id_obs])?utf8_encode($_historicoStatus[$x->id_obs]->titulo):"",
															'desc'=>utf8_encode($x->descricao)
														);


						/*if($x->evento=="agendaHorario") {
								$_historico[]=array('usr'=>utf8_encode($_usuarios[$x->id_usuario]->nome),
																	'dt'=>date('d/m H:i',strtotime($x->data)),
																	'ev'=>'horario',
																	'nvDt'=>date('d/m H:i',strtotime($x->agenda_data_novo)),
																	'antDt'=>date('d/m H:i',strtotime($x->agenda_data_antigo))
																);

						} else {
							if(isset($_status[$x->id_status_novo])) {
								$_historico[]=array('usr'=>utf8_encode($_usuarios[$x->id_usuario]->nome),
																	'dt'=>date('d/m H:i',strtotime($x->data)),
																	'ev'=>'status',
																	'desc'=>utf8_encode($x->descricao),
																	'sts'=>utf8_encode($_status[$x->id_status_novo]->titulo),
																	'novo'=>$x->evento=="agendaNovo",
																	'cor'=>$_status[$x->id_status_novo]->cor
																);
							}
						}*/
					}
				}

				$rtn=array('success'=>true,'historico'=>$_historico);
			} else {
				$rtn=array('success'=>false,'error'=>'Paciente não encontrado!'); 
			}

		}

		header("Content-type: application/json");
		echo json_encode($rtn);

		die();


	}
	$title="";
	include "includes/header.php";
	include "includes/nav.php";

	if($usr->tipo!="admin" and !in_array("produtos",$_usuariosPermissoes)) {
		$jsc->jAlert("Você não tem permissão para acessar esta área!","erro","document.location.href='dashboard.php'");
		die();
	}
	$values=$adm->get($_GET);

	$data = isset($_GET['data'])?$_GET['data']:date('d/m/Y');

	list($dia,$mes,$ano)=explode("/",$data);

	if(checkdate($mes, $dia, $ano)) {
		$data=$mes."/".$dia."/".$ano;
		$dataWH=$ano."-".$mes."-".$dia;
	} else { 
		$data=date('m/d/Y');
		$dataWH=date('Y-m-d');
	}


	$agenda=array();
	$pacientesIds=$pacientesAtendidosIds=array(-1);

	$_profissionais=array();
	$selectProfissional='';
	$sql->consult($_p."colaboradores","id,nome,calendario_iniciais,foto,calendario_cor","where tipo_cro<>'' and lixo=0 order by nome asc");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_profissionais[$x->id]=$x;
		$selectProfissional.='<option value="'.$x->id.'">'.utf8_encode($x->nome).'</option>';
	}

	$_cadeiras=array();
	$selectCadeira='';
	$sql->consult($_p."parametros_cadeiras","*","where lixo=0 order by titulo asc");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_cadeiras[$x->id]=$x;
		$selectCadeira.='<option value="'.$x->id.'">'.utf8_encode($x->titulo).'</option>';
	}	

	$_historicoStatus=array();
	$sql->consult($_p."pacientes_historico_status","*","where lixo=0 order by titulo asc");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_historicoStatus[$x->id]=$x;
	}	


	$selectTempo='';
	foreach($optAgendaDuracao as $v) {
		$selectTempo.='<option value="'.$v.'">'.$v.' min</option>';
	}

?>
	<style type="text/css">
		.erro {
			border: solid 2px #CC3300;
		}
	</style>

	<section id="cal-popup" class="cal-popup cal-popup_paciente cal-popup_top cal-popup_alt" style="left:703px; top:338px; margin-left:303px;display: none">
			<?php /*<a href="javascript:;" class="cal-popup__fechar js-btn-fechar"><i class="iconify" data-icon="mdi-close"></i></a>*/?>
			<section class="paciente-info">
				<header class="paciente-info-header">
					<section class="paciente-info-header__inner1">
						<div>
							<h1 class="js-nome"></h1>
							<p class="js-idade"></p>
							<p><span style="color:var(--cinza3);" class="js-id_paciente">#44</span> <span style="color:var(--cor1);"></span></p>
						</div>
					</section>
				</header>

				<div class="abasPopover">
					<a href="javascript:;" class="js-aba-agendamento" onclick="$(this).parent().parent().find('a').removeClass('active');$(this).parent().parent().find('.js-grid').hide();$(this).parent().parent().find('.js-grid-agendamento').show();$(this).addClass('active');$('.js-grid-agendamento-agendar').show();">Agendamento</a>
					<a href="javascript:;" onclick="$(this).parent().parent().find('a').removeClass('active');$(this).parent().parent().find('.js-grid').hide();$(this).parent().parent().find('.js-grid-proximaConsulta').show();$(this).addClass('active');">Próxima Consulta</a>
					<a href="javascript:;" onclick="$(this).parent().parent().find('a').removeClass('active');$(this).parent().parent().find('.js-grid').hide();$(this).parent().parent().find('.js-grid-historico').show();$(this).addClass('active');" class="active">Histórico</a>
				</div>

				<input type="hidden" class="js-input-id_paciente" />

				<div class="paciente-info-grid js-grid js-grid-agendamento-agendar" style="font-size: 12px;">
					<dl>
						<dt>Data</dt>
						<dd>
							<input type="text" class="js-input-data data datecalendar" placeholder="escolha a nova data" />
						</dd>
					</dl>
					<dl>
						<dt>Tempo</dt>
						<dd>
							<select class="js-select-tempo">
								<option value="">Tempo...</option>
								<?php
								echo $selectTempo;
								?>
							</select>
						</dd>
					</dl>
					<dl>
						<dt>Profissional</dt>
						<dd>
							<select class="js-select-profissional">
								<option value="">Profissional...</option>
								<?php
								echo $selectProfissional;
								?>
							</select>
						</dd>
					</dl>
					<dl>
						<dt>Cadeira</dt>
						<dd>
							<select class="js-select-cadeira">
								<option value="">Cadeira...</option>
								<?php
								echo $selectCadeira;
								?>
							</select>
						</dd>
					</dl>
					<dl>
						<dt>Horário</dt>
						<dd>
							<select class="js-select-horario">

							</select>
						</dd>
					</dl>
					<button type="button" class="button button__full js-gridbtn-agendar" style="background:var(--amarelo);">Agendar</button>
				</div>

				<div class="paciente-info-grid js-grid js-grid-agendamento-naoQueroAgendar" style="display: none;grid-template-columns:1fr">
					<dl>
						<dd>
							<select class="js-select-historicoStatus">
								<?php
								foreach($_historicoStatus as $s) {
								?>
								<option value="<?php echo $s->id;?>"><?php echo utf8_encode($s->titulo);?></option>
								<?php	
								}
								?>
							</select>
						</dd>
					</dl>
					<textarea name="" rows="4" class="js-textarea-obs" placeholder="Descreva o motivo..."></textarea>
					<button type="button" class="button button__full js-gridbtn-naoQueroAgendar" style="background:;">Salvar</button>
				</div>

				<div class="paciente-info-grid js-grid js-grid-proximaConsulta" style="display:none;">

				</div>

				<div class="paciente-info-grid js-grid js-grid-historico" style="display:none;font-size:12px;color:#666;grid-template-columns:1fr;max-height:300px; overflow-y:auto;">	
				</div>

				<div class="paciente-info-opcoes">
					<a href="javascript:;" class="button button__full js-btn-agendar" data-id_agenda="${x.id_agenda}" style="background-color:var(--verde);">Quero agendar</a>

					<a href="javascript:;" class="button button__full js-btn-naoQueroAgendar" data-id_agenda="${x.id_agenda}" style="background-color:var(--vermelho);">Não quero agendar</a>

					<a href="javascript:;" class="js-hrefPaciente button button__sec"><i class="iconify" data-icon="bx:bxs-user"></i></a>
				</div>
			</section>
		</section>
	<section class="content">  
		
		<?php 
			require_once("includes/nav2.php");
		
			$agendaConfirmacao=true;
		require_once("includes/asideAgenda.php");
		?>

		<script type="text/javascript">

			var data = '<?php echo $dataWH;?>';
			var popViewInfos = [];
			let dataAux = new Date("<?php echo $data;?>");

			const meses = ["jan.", "fev.", "mar.", "abr.", "mai.", "jun.", "jul.","ago.","set.","out.","nov.","dez."];
			const dias = ["domingo","segunda-feira","terça-feira","quarta-feira","quinta-feira","sexta-feira","sábado"];
			
			let dataFormatada = `${dias[dataAux.getDay()]}, ${dataAux.getDate()} de ${meses[(dataAux.getMonth())]} de ${dataAux.getFullYear()}`;
			
			var agenda = [];

			var agendaTratamento = [];

			var agendaInteligencia = [];


			const historicoConsulta = (id_paciente) => {

				$('#cal-popup .js-grid-historico').html(`<center>Carregando...</center>`);
				let dataAjax = `ajax=historico&id_paciente=${id_paciente}`;
				$.ajax({
					type:"POST",
					data:dataAjax,
					success:function(rtn) {
						if(rtn.success) {
							let historico = rtn.historico;
							$('#cal-popup .js-grid-historico').html(``);
							if(historico.length>0) {
								historico.forEach(x=>{
									
									$('#cal-popup .js-grid-historico').append(`<div class="hist-lista-item hist-lista-item_lab" style="max-width:95%;font-size:12px;">
																						<h1>${x.usr} em ${x.dt}</h1>
																						<p><b>${x.obs}</b></p>
																						<p>${x.desc}</p>
																					</div>`);
									
								});

							} else {
								$('#cal-popup .js-grid-historico').html(`<center>Sem histórico</center>`);
							}
						} else if(rtn.error) {

						} else {

						}
					},
					error:function(){

					}
				})

			}

			const agendaAtualizar = () => {

				let dataAjax = `ajax=agenda&data=${data}`;
				$.ajax({
					type:"POST",
					data:dataAjax,
					success:function(rtn) {
						if(rtn.success) {
							agenda=rtn.agenda;
							agendaTratamento=rtn.agendaTratamento;
							agendaInteligencia=rtn.agendaInteligencia;
							//historico = rtn.historico;
							agendaListar();
							pacientesTratamento();
							pacientesInteligencia();
						} else if(rtn.error) {

						} else {

						}
					},
					error:function(){

					}
				})
			}

			const agendaListar = () => {

				$(`#kanban .js-kanban-item,#kanban .js-kanban-item-modal`).remove();

				popViewInfos = [];

				let qtdReagendar = 0;

				agenda.forEach(x=>{

					/*popInfos = {};
				    popInfos.nome = nome;
				    popInfos.nomeCompleto = nomeCompleto;
				    popInfos.idade = idade;
				    popInfos.id_paciente = id_paciente;
				    popInfos.situacao = situacao;
				    popInfos.obs = obs;
				    popInfos.infos=infos;
				    popInfos.id_status=id_status;
				    popInfos.id_unidade=id_unidade;
				    popInfos.id_agenda=id_agenda;
				    popInfos.foto=foto.length>0?foto:'';
				    popInfos.procedimentosLista=procedimentosLista;

					popViewInfos[x.id_agenda] = popInfos;*/

					let evolucao = ``;
					let agendadoHa = ``;



					if(x.dias==0) agendadoHa=`Agendado hoje`;
					else agendadoHa=`agendado há <b>${x.dias}</b> dia(s)`;
					

					if(x.dias<7 && x.id_status != 'reagendar') {
						// cor = 'var(--verde)';
						cor = '#424242';
					}
					else if(x.dias>=7 && x.dias<30 && x.id_status != 'reagendar') {
						cor = '#6C6C6C';
					}
					else if(x.dias>=30 && x.id_status != 'reagendar') {
						cor = '#929292';
					} else {
						cor = '#fff';
					}
					
					

					if(x.telefone1.length<5) x.telefone1='';

					let barra = ``;
					if(x.id_status == 'reagendar') {
						qtdReagendar++;
						if(x.futuro === true) {
							// barra = `<div style="background:purple;width:100%;padding:5px;border-radius:5px;"></div>`;
							barra = `kanban-item_destaque`;
						} 
					} else {
						// barra = `<div style="background:${cor};width:100%;padding:5px;border-radius:5px;"></div>`;
						barra = `kanban-item_destaque`;
						
					}
					// barra = `<div style="background:${cor};width:100%;padding:5px;border-radius:5px;"></div>`;

					let btnConfirmar = ``;

					if(x.id_status!=2) {
						btnConfirmar = `<a href="javascript:;" class="button button__full js-btn-confirmarAgendamento" data-id_agenda="${x.id_agenda}" style="background-color:#1182ea;">Confirmar Agendamento</a>`;
					}
					
					let tempoComplemento=``;
					if(x.agenda_duracao>120) {
						tempoComplemento=`<option value="${x.agenda_duracao}">${x.agenda_duracao} min</option>`;
					}

					let prof = '';
					if(x.profissionais.length>0) {
						x.profissionais.forEach(p=>{
							prof+=`<div class="cal-item-foto" style="float:right;"><span style="background:${p.cor}">${p.iniciais}</span></div>`;
						});
					}

 					let html = `<div class="kanban-card">
									<a href="javascript:;" onclick="$(this).next('.kanban-card-modal').show();$(this).next('.kanban-card-modal').find('.js-opcoes').show();$(this).next('.kanban-card-modal').find('.js-acoes').hide();" class="kanban-card-dados js-kanban-item js-kanban-item-${x.id_agenda} ${barra}" style="background-color:${cor}" data-id="${x.id_agenda}">
										<p class="kanban-card-dados__data">
											<i class="iconify" data-icon="ph:calendar-blank"></i>
											${x.data} &bull; ${x.hora}
										</p>
										<h1>${x.paciente}</h1>
										<h2>${x.telefone1}</h2>
										<h2>${agendadoHa}</h2>
										
									</a>
									<div class="kanban-card-modal js-kanban-item-modal" style="display:none;border-radius:10px;z-index:9999999">
										<div class="kanban-card-modal__inner1" style="border-radius:10px">
											<?php /*<a class="kanban-card-modal__fechar" href="javascript:;" onclick="$(this).parent().parent().hide(); $('.js-reagendar, .js-cancelar').hide(); $('.js-opcoes').show();"><i class="iconify" data-icon="ph-x"></i></a>*/?>
											<h2>${prof}</h2>
											<h1>${x.paciente}</h1>
											<h2>${x.telefone1}</h2>
											<h2>${x.procedimentos}</h2>
										</div>
										<div class="kanban-card-modal__inner2 js-opcoes">
											${btnConfirmar}
											<a href="javascript:;" onclick="$(this).parent().hide(); $(this).parent().nextAll('.js-reagendar').show();" class="js-btn-reagendar-${x.id_agenda} button button__full" style="background-color:purple;">Reagendar Agendamento</a>
											<a href="javascript:;" onclick="$(this).parent().hide(); $(this).parent().nextAll('.js-cancelar').show();" class="button button__full" style="background-color:#f9de27;">Desmarcar Agendamento</a>
										</div>
										<div class="kanban-card-modal__inner2 js-reagendar js-acoes" style="display:none;">
											<form class="js-form-${x.id_agenda}">
												<input type="hidden" class="js-input-id" placeholder="" />
												<input type="text" class="js-input-data" placeholder="escolha a nova data" />
												<select class="js-select-tempo">
													<option value="">Tempo...</option>
													<?php
													echo $selectTempo;
													?>
													${tempoComplemento}
												</select>
												<select class="js-select-profissional">
													<option value="">Profissional...</option>
													<?php
													echo $selectProfissional;
													?>
												</select>
												<select class="js-select-cadeira">
													<option value="">Cadeira...</option>
													<?php
													echo $selectCadeira;
													?>
												</select>
												<select class="js-select-horario">

												</select>
												<button type="button" class="button button__full js-btn-reagendar" style="background:var(--amarelo);">Reagendar</button>
											</form>
										</div>
										<div class="kanban-card-modal__inner2 js-cancelar js-acoes" style="display:none;">
											<form>
												<textarea name="" rows="4" class="js-cancelar-motivo" placeholder="Descreva o motivo do cancelamento..."></textarea>
												<button type="button" class="button button__full js-btn-cancelarAgendamento" data-id_agenda="${x.id_agenda}" style="background:#f9de27;">Desmarcar</button>
											</form>
										</div>
									</div>
								</div>`;


					$(`#kanban .js-kanban-status-${x.id_status}`).append(html);
					//console.log(x.agenda_data);
					$(`#kanban input.js-input-id:last`).val(x.id_agenda);
					$(`#kanban select.js-select-tempo:last`).val(x.aDur);
					$(`#kanban select.js-select-profissional:last`).val(x.idPr);
					$(`#kanban select.js-select-cadeira:last`).val(x.idC);
					$(`#kanban input.js-input-data:last`).datetimepicker({
															timepicker:false,
															format:'d/m/Y',
															scrollMonth:false,
															scrollTime:false,
															scrollInput:false,

														}).inputmask('99/99/9999').val(``);
					$(`#kanban select.js-select-horario:last`).append(`<option value="">Horário...</option>`);
				});
				
				$('.js-qtd-reagendar').html(qtdReagendar);
			}	

			const pacientesTratamento = () => {

				$(`#kanban .js-kanban-status-semHorario .js-kanban-item,#kanban .js-kanban-status-semHorario .js-kanban-item-modal`).remove();

				popViewInfos = [];


				$('.js-qtd-semHorario').html(`(${agendaTratamento.length})`);

				agendaTratamento.forEach(x=>{

					let evolucao = ``;
					let agendadoHa = ``;

					if(x.dias==0) agendadoHa=`Agendado hoje`;
					else agendadoHa=`agendado há <b>${x.dias}</b> dia(s)`;

					if(x.dias<7) {
						cor = 'var(--verde)';
					}
					else if(x.dias>=7 && x.dias<30) {
						cor = 'var(--laranja)';
					}
					else {
						cor = 'var(--vermelho)';
					}
					
					

					if(x.telefone1.length<5) x.telefone1='';


					let barra = ``;
					if(x.id_status == 'reagendar') {
						if(x.futuro === true) {
						//	barra = `<div style="background:purple;width:100%;padding:5px;border-radius:5px;"></div>`;
						} 
					} else {
						//barra = `<div style="background:${cor};width:100%;padding:5px;border-radius:5px;"></div>`;
						
					}
						// barra = `<div style="background:${cor};width:100%;padding:5px;border-radius:5px;"></div>`;

					let style = '';
					if(x.corObs.length>0) style = ` style="background:${x.corObs};color:#FFF !important;"`;

				
					let html = `<a href="javascript:;" onclick="popView(this,${x.id_paciente});" class="kanban-card-dados js-kanban-item" data-id="${x.id_paciente}"${style}>
									${barra}
									<h1${style}>${x.nome}</h1>
									<h2${style}>${x.telefone1}</h2>
								</a>`;
								

					$(`#kanban .js-kanban-status-semHorario`).append(html);
				})
			}

			const pacientesInteligencia = () => {

				$(`#kanban .js-kanban-status-inteligencia .js-kanban-item,#kanban .js-kanban-status-inteligencia .js-kanban-item-modal`).remove();

				popViewInfos = [];

				$('.js-qtd-inteligencia').html(`(${agendaInteligencia.length})`);

				agendaInteligencia.forEach(x=>{

					let evolucao = ``;
					let agendadoHa = ``;

					if(x.dias==0) agendadoHa=`Agendado hoje`;
					else agendadoHa=`agendado há <b>${x.dias}</b> dia(s)`;

					if(x.dias<7) {
						cor = 'var(--verde)';
					}
					else if(x.dias>=7 && x.dias<30) {
						cor = 'var(--laranja)';
					}
					else {
						cor = 'var(--vermelho)';
					}
					
					

					if(x.telefone1.length<5) x.telefone1='';


					let barra = ``;
					if(x.id_status == 'reagendar') {
						if(x.futuro === true) {
						//	barra = `<div style="background:purple;width:100%;padding:5px;border-radius:5px;"></div>`;
						} 
					} else {
						//barra = `<div style="background:${cor};width:100%;padding:5px;border-radius:5px;"></div>`;
						
					}
						// barra = `<div style="background:${cor};width:100%;padding:5px;border-radius:5px;"></div>`;


					let style = '';
					if(x.corObs.length>0) style = ` style="background:${x.corObs};color:#FFF !important;"`;
				
					let html = `<a href="javascript:;"  onclick="popView(this,${x.id_paciente});" class="kanban-card-dados js-kanban-item" data-id="${x.id_paciente}"${style}>
									${barra}
									<h1${style}>${x.nome}</h1>
									<h2${style}>${x.telefone1}</h2>
								</a>`;
								<?php /*<div class="kanban-card-modal js-kanban-item-modal" style="display:none;">
									<div class="kanban-card-modal__inner1">
										<a class="kanban-card-modal__fechar" href="javascript:;" onclick="$(this).parent().parent().hide(); $('.js-reagendar, .js-cancelar').hide(); $('.js-opcoes').show();"><i class="iconify" data-icon="ph-x"></i></a>
										<h1>Ana Paula Toniazzo</h1>
										<h2>(62) 98450-2332</h2>
										<h2>Anestesia</h2>
									</div>
									<div class="kanban-card-modal__inner2 js-opcoes">
										<a href="javascript:;" class="button button__full" style="background-color:var(--verde);">Confirmar agendamento</a>
										<a href="javascript:;" onclick="$(this).parent().hide(); $(this).parent().nextAll('.js-reagendar').show();" class="button button__full" style="background-color:var(--amarelo);">Reagendar</a>
										<a href="javascript:;" onclick="$(this).parent().hide(); $(this).parent().nextAll('.js-cancelar').show();" class="button button__full" style="background-color:var(--vermelho);">Cancelar Agendamento</a>
									</div>
									<div class="kanban-card-modal__inner2 js-reagendar" style="display:none;">
										<form>
											<input type="text" name="" class="datecalendar" placeholder="06/04/2021" />
											<select name=""><option value="">Profissional...</option></select>
											<select name=""><option value="">Cadeira...</option></select>
											<select name=""><option value="">Horas disponíveis...</option></select>
											<button type="submit" class="button button__full" style="background:var(--amarelo);">Reagendar</button>
										</form>
									</div>
									<div class="kanban-card-modal__inner2 js-cancelar" style="display:none;">
										<form>
											<textarea name="" rows="4" placeholder="Descreva o motivo do cancelamento..."></textarea>
											<button type="submit" class="button button__full" style="background:var(--vermelho);">Cancelar</button>
										</form>
									</div>
								</div>`;*/?>

					$(`#kanban .js-kanban-status-inteligencia`).append(html);
				})
			}

			const d2 = (num) => {
				return num <=9 ? `0${num}`:num;
			}

			const dataProcess = (dtObj) => {
					

				let dataFormatada = `${dias[dtObj.getDay()]}, ${dtObj.getDate()} de ${meses[(dtObj.getMonth())]} de ${dtObj.getFullYear()}`;


				data = `${dtObj.getFullYear()}-${d2(dtObj.getMonth()+1)}-${d2(dtObj.getDate())}`;

				agendaAtualizar();

				$('.js-calendario-title').val(dataFormatada)
			}

			const horarioDisponivel = (id_agenda,box) => {

				if(box=="#kanban") {
					box =`${box} .js-form-${id_agenda}`
				} else {

				}

				data_agenda = $(`${box} .js-input-data`).val();
				tempo = $(`${box} .js-select-tempo`).val();
				id_profissional = $(`${box} .js-select-profissional`).val();
				id_cadeira = $(`${box} .js-select-cadeira`).val();

				if(data_agenda.length>0 && tempo.length>0 && id_profissional.length>0 && id_cadeira.length>0) {
					dataAjax = `ajax=horarioDisponivel&data=${data_agenda}&tempo=${tempo}&id_profissional=${id_profissional}&id_cadeira=${id_cadeira}&id_agenda=${id_agenda}`;

					$.ajax({
						type:"POST",
						data:dataAjax,
						success:function(rtn) {
							if(rtn.success) {
								$(`${box} .js-select-horario`).find('option').remove();

								if(rtn.horariosDisponiveis.length>0) {
									$(`${box} .js-select-horario`).append(`<option value="">Selecione o horário</option>`)
									rtn.horariosDisponiveis.forEach(x=>{

									$(`${box} .js-select-horario`).append(`<option value="${x}">${x}</option>`)
									})
								} else {
									$(`${box} .js-select-horario`).append(`<option value="">Nenhum horário disponível</option>`)
								}
							}
						}
					});
				} else {
					$(`#${box} .js-select-horario option`).remove();
					$(`#${box} .js-select-horario`).append(`<option value="">Complete os campos</option>`);
				}
			}

			var popViewInfos = [];

			const popView = (obj,id_paciente) => {


				historicoConsulta(id_paciente);

				let nomeCompleto = $(obj).find('h1').html();

				$('#cal-popup')
						.removeClass('cal-popup_left')
						.removeClass('cal-popup_right')
						.removeClass('cal-popup_bottom')
						.removeClass('cal-popup_top');

				let clickTop=obj.getBoundingClientRect().top+window.scrollY;
				//console.log(clickTop);
				let clickLeft=Math.round(obj.getBoundingClientRect().left);
				let clickMargin=Math.round(obj.getBoundingClientRect().width/2);
				$(obj).prev('.cal-popup')
						.removeClass('cal-popup_left')
						.removeClass('cal-popup_right')
						.removeClass('cal-popup_bottom')
						.removeClass('cal-popup_top');

				let popClass='cal-popup_top';
				if(clickLeft>=1200) {
					//popClass='cal-popup_left';
					//clickLeft-=Math.round($('#cal-popup').width());
					//clickMargin/=4;
				}
				$('#cal-popup').addClass(popClass).toggle();

				
				$('#cal-popup').css({'top':clickTop,'left':clickLeft,'margin-left': clickMargin});
				$('#cal-popup').show();
				$('#cal-popup .js-nome').html(nomeCompleto);
				$('#cal-popup .js-id_paciente').html(`#${id_paciente}`);

				$('#cal-popup .js-input-data').val('');
				$('#cal-popup .js-select-tempo').val('');
				$('#cal-popup .js-select-profissional').val('');
				$('#cal-popup .js-select-horario').val('');
				$('#cal-popup .js-select-cadeira').val('');
				$('#cal-popup .js-input-id_paciente').val(id_paciente);

				$('#cal-popup .js-btn-agendar').click();
			}

			$(function(){

				$('#cal-popup').on('click','.js-gridbtn-naoQueroAgendar',function(){
					//alert('a');
					let id_paciente = $('#cal-popup .js-input-id_paciente').val();
					let id_status = $(this).parent().find('.js-select-historicoStatus').val();
					let obs = $(this).parent().find('.js-textarea-obs').val();
					let erro = ``;

					if(id_status.length==0) {
						erro = 'Preencha o campo de Status';
						$(this).parent().find('.js-input-data').addClass('erro');
					} else if(obs.length==0) {
						erro = 'Preencha o campo de Observações';
						$(this).parent().find('.js-textarea-obs').addClass('erro');
					}

					if(erro.length===0) {

						 $(this).parent().find('.js-select-historicoStatus').val('');
						 $(this).parent().find('.js-textarea-obs').val('');

						let data=`ajax=naoQueroAgendar&id_paciente=${id_paciente}&id_status=${id_status}&obs=${obs}`;

						$.ajax({
							type:"POST",
							data:data,
							success:function(rtn) {
								if(rtn.success) {
									agendaAtualizar();
								} else if(rtn.error) { 
									swal({title: "Erro!", text: rtn.error, type:"error", confirmButtonColor: "#424242"});
								} else {
									swal({title: "Erro!", text: "Algum erro ocorreu durante o registro da observação.", type:"error", confirmButtonColor: "#424242"});
								}
							},
							error:function(){
								swal({title: "Erro!", text: "Algum erro ocorreu durante o registro da observação.", type:"error", confirmButtonColor: "#424242"});
							}
						}).done(function(){
							$('#cal-popup').hide();
						})

					} else {
						swal({title: "Erro!",
							text: erro,
							type:"error",
							confirmButtonColor: "#424242"},function(){
								//$(`.js-kanban-item-${id_agenda}`).click();
							//	$(`.js-btn-reagendar-${id_agenda}`).click();
							});
					}

				});

				$('#cal-popup').on('change','.js-select-tempo,.js-select-cadeira,.js-select-profissional,.js-input-data',function(){
					
					horarioDisponivel(0,'#cal-popup');
					$(`.js-kanban-item-${id_agenda}`).click();
					$(`.js-btn-reagendar-${id_agenda}`).click();
					
				});

				$('#cal-popup').on('click','.js-btn-agendar',function(){
					
					$('#cal-popup .js-aba-agendamento').click();
					$('#cal-popup .js-grid-agendamento-naoQueroAgendar').hide();
					$('#cal-popup .js-grid-agendamento-agendar').show();

				});

				$('#cal-popup').on('click','.js-hrefPaciente',function(){
					let id_paciente = $('.js-input-id_paciente').val();
					window.open(`pg_contatos_pacientes_resumo.php?id_paciente=${id_paciente}`)
				});

				$('#cal-popup').on('click','.js-btn-naoQueroAgendar',function(){
					
					$('#cal-popup .js-aba-agendamento').click();
					$('#cal-popup .js-grid-agendamento-agendar').hide();
					$('#cal-popup .js-grid-agendamento-naoQueroAgendar').show();

				});

				$('#cal-popup').on('click','.js-gridbtn-agendar',function(){

					let id_paciente = $('#cal-popup .js-input-id_paciente').val();
					let agenda_data = $(this).parent().find('.js-input-data').val();
					let tempo = $(this).parent().find('.js-select-tempo').val();
					let id_profissional = $(this).parent().find('.js-select-profissional').val();
					let id_cadeira = $(this).parent().find('.js-select-cadeira').val();
					let horario = $(this).parent().find('select.js-select-horario').val();
					let erro = ``;

					if(agenda_data.length==0) {
						erro = 'Preencha o campo de Data';
						$(this).parent().find('.js-input-data').addClass('erro');
					} else if(tempo.length==0) {
						erro = 'Preencha o campo de Tempo';
						$(this).parent().find('.js-input-tempo').addClass('erro');
					} else if(id_profissional.length==0) {
						erro = 'Preencha o campo de Profissional';
						$(this).parent().find('.js-input-profissional').addClass('erro');
					} else if(id_cadeira.length==0) {
						erro = 'Preencha o campo de Cadeira';
						$(this).parent().find('.js-input-cadeira').addClass('erro');
					} else if(horario.length==0) {
						erro = 'Preencha o campo de Horário';
						$(this).parent().find('.js-input-horario').addClass('erro');
					}

					if(erro.length===0) {


						let dataAjax = `ajax=agendar&id_paciente=${id_paciente}&agenda_data=${agenda_data}&tempo=${tempo}&id_profissional=${id_profissional}&id_cadeira=${id_cadeira}&horario=${horario}`;

						$.ajax({
							type:"POST",
							data:dataAjax,
							success:function(rtn) {
								if(rtn.success) {
									
									agendaAtualizar();
									pacientesTratamento();
									pacientesInteligencia();
									$('#cal-popup').hide();

									swal({title: "Sucesso!",
											text: 'Agendamento realizado com sucesso!',
											type:"success",
											confirmButtonColor: "#424242"});
								} else if(rtn.error) { 
									swal({title: "Erro!", text: rtn.error, type:"error", confirmButtonColor: "#424242"});
								} else {
									swal({title: "Erro!", text: "Algum erro ocorreu durante a confirmação de agendamento.", type:"error", confirmButtonColor: "#424242"});
								}
							},
							error:function() {
								swal({title: "Erro!", text: "Algum erro ocorreu durante a confirmação de agendamento", type:"error", confirmButtonColor: "#424242"});
							}
						})


					} else {

						swal({title: "Erro!",
								text: erro,
								type:"error",
								confirmButtonColor: "#424242"},function(){
									//$(`.js-kanban-item-${id_agenda}`).click();
									//$(`.js-btn-reagendar-${id_agenda}`).click();
								});
					}

				});

				$(document).mouseup(function(e)  {
				    var container = $("#cal-popup");
				    var containerCalendar = $(".xdsoft_datetimepicker");
				    var showSweetAlert = $(".showSweetAlert");

				    // if the target of the click isn't the container nor a descendant of the container
				    if ((!container.is(e.target) && container.has(e.target).length === 0) && (!containerCalendar.is(e.target) && containerCalendar.has(e.target).length === 0) && (!showSweetAlert.is(e.target) && showSweetAlert.has(e.target).length === 0)) {
				   		$('#cal-popup').hide();
				    }
				});

				$('#kanban').on('click','.js-btn-reagendar',function(){

					let id_agenda = $(this).parent().find('.js-input-id').val();
					let agenda_data = $(this).parent().find('.js-input-data').val();
					let tempo = $(this).parent().find('.js-select-tempo').val();
					let id_profissional = $(this).parent().find('.js-select-profissional').val();
					let id_cadeira = $(this).parent().find('.js-select-cadeira').val();
					let horario = $(this).parent().find('select.js-select-horario').val();
					let erro = ``;

					if(agenda_data.length==0) {
						erro = 'Preencha o campo de Data';
						$(this).parent().find('.js-input-data').addClass('erro');
					} else if(tempo.length==0) {
						erro = 'Preencha o campo de Tempo';
						$(this).parent().find('.js-input-tempo').addClass('erro');
					} else if(id_profissional.length==0) {
						erro = 'Preencha o campo de Profissional';
						$(this).parent().find('.js-input-profissional').addClass('erro');
					} else if(id_cadeira.length==0) {
						erro = 'Preencha o campo de Cadeira';
						$(this).parent().find('.js-input-cadeira').addClass('erro');
					} else if(horario.length==0) {
						erro = 'Preencha o campo de Horário';
						$(this).parent().find('.js-input-horario').addClass('erro');
					}

					if(erro.length===0) {


						let dataAjax = `ajax=reagendar&id_agenda=${id_agenda}&agenda_data=${agenda_data}&tempo=${tempo}&id_profissional=${id_profissional}&id_cadeira=${id_cadeira}&horario=${horario}`;
						$.ajax({
							type:"POST",
							data:dataAjax,
							success:function(rtn) {
								if(rtn.success) {
									agendaAtualizar();
									swal({title: "Sucesso!",
											text: 'Reagendamento realizado com sucesso!',
											type:"success",
											confirmButtonColor: "#424242"});
								} else if(rtn.error) { 
									swal({title: "Erro!", text: rtn.error, type:"error", confirmButtonColor: "#424242"});
								} else {
									swal({title: "Erro!", text: "Algum erro ocorreu durante a confirmação de agendamento.", type:"error", confirmButtonColor: "#424242"});
								}
							},
							error:function() {
								swal({title: "Erro!", text: "Algum erro ocorreu durante a confirmação de agendamento", type:"error", confirmButtonColor: "#424242"});
							}
						})


					} else {

						swal({title: "Erro!",
								text: erro,
								type:"error",
								confirmButtonColor: "#424242"},function(){
									$(`.js-kanban-item-${id_agenda}`).click();
									$(`.js-btn-reagendar-${id_agenda}`).click();
								});
					}

				});

				$('#kanban').on('change','.js-select-tempo,.js-select-cadeira,.js-select-profissional,.js-input-data',function(){
					let id_agenda = $(this).parent().find('.js-input-id').val();
					horarioDisponivel(id_agenda,'#kanban');
					$(`.js-kanban-item-${id_agenda}`).click();
					$(`.js-btn-reagendar-${id_agenda}`).click();
					
				});

				$('#kanban').on('click','.js-btn-confirmarAgendamento',function(){
					let id_agenda = $(this).attr('data-id_agenda');
					let dataAjax = `ajax=confirmarAgendamento&id_agenda=${id_agenda}`;

					$.ajax({
						type:"POST",
						data:dataAjax,
						success:function(rtn) {
							if(rtn.success) {
								agendaAtualizar();
								swal({title: "Sucesso!", text: 'Paciente confirmado com sucesso!', type:"success", confirmButtonColor: "#424242"});
							} else if(rtn.error) { 
								swal({title: "Erro!", text: rtn.error, type:"error", confirmButtonColor: "#424242"});
							} else {
								swal({title: "Erro!", text: "Algum erro ocorreu durante a confirmação de agendamento.", type:"error", confirmButtonColor: "#424242"});
							}
						},
						error:function() {
							swal({title: "Erro!", text: "Algum erro ocorreu durante a confirmação de agendamento", type:"error", confirmButtonColor: "#424242"});
						}
					});

				});

				$('#kanban').on('click','.js-btn-cancelarAgendamento',function(){
					let id_agenda = $(this).attr('data-id_agenda');
					let motivo = $(this).parent().find('textarea.js-cancelar-motivo').val();
					let dataAjax = `ajax=cancelarAgendamento&id_agenda=${id_agenda}&motivo=${motivo}`;
				
					$.ajax({
						type:"POST",
						data:dataAjax,
						success:function(rtn) {
							if(rtn.success) {
								agendaAtualizar();
								swal({title: "Sucesso!", text: 'Paciente desmarcado com sucesso!', type:"success", confirmButtonColor: "#424242"});
							} else if(rtn.error) { 
								swal({title: "Erro!", text: rtn.error, type:"error", confirmButtonColor: "#424242"});
							} else {
								swal({title: "Erro!", text: "Algum erro ocorreu durante a confirmação de agendamento.", type:"error", confirmButtonColor: "#424242"});
							}
						},
						error:function() {
							swal({title: "Erro!", text: "Algum erro ocorreu durante a confirmação de agendamento", type:"error", confirmButtonColor: "#424242"});
						}
					});

				});

				$(document).mouseup(function(e)  {
				    var container = $(".js-kanban-item-modal");
				    // if the target of the click isn't the container nor a descendant of the container
				    if (!container.is(e.target) && container.has(e.target).length === 0) {
				       $('.js-kanban-item-modal').hide();
				    }
				});


				$('.js-calendario').datetimepicker({
					timepicker:false,
					format:'d F Y',
					scrollMonth:false,
					scrollTime:false,
					scrollInput:false,
					onChangeDateTime:function(dp,dt) {
						dataProcess(dp);
					}
				});

				agendaAtualizar();
				pacientesTratamento();
				pacientesInteligencia();

				$('.js-calendario-title').val(dataFormatada);

				
				/*
				
				inteligencia-> 744aff
					-> 6, 4 e nao tem agendamento a mais de 6meses
				semhorario-> ff0011
				reagendar -> ffe82d


				var droppable = $(".js-kanban-status").dad({
					placeholderTarget: ".js-kanban-item"
				});

				$(".js-kanban-status").on("dadDrop", function (e, element) {
					let id_agenda = $(element).attr('data-id');
					let id_status = $(element).parent().attr('data-id_status');

					let dataAjax = `ajax=alterarStatus&id_agenda=${id_agenda}&id_status=${id_status}`;
					$.ajax({
						type:"POST",
						data:dataAjax,
						success:function(rtn) {
							if(rtn.success) {
								agendaAtualizar();
							}
						}
					})
		        });*/

				$('a.js-right').click(function(){
					let aux = data.split('-');
					let dtObj = new Date(`${aux[1]}/${aux[2]}/${aux[0]}`);
					dtObj.setDate(dtObj.getDate()+1);
					dataProcess(dtObj);
				});

				$('a.js-left').click(function(){ 
					let aux = data.split('-');
					let dtObj = new Date(`${aux[1]}/${aux[2]}/${aux[0]}`);
					dtObj.setDate(dtObj.getDate()-1);
					dataProcess(dtObj);
				});

				$('a.js-today').click(function(){
					let dtObj = new Date(`<?php echo date('m/').(date('d')-1).date('/Y');?>`);
					dtObj.setDate(dtObj.getDate()+1);
					dataProcess(dtObj);
				});

			});
		</script>

		

		<section class="grid">

			<div class="kanban" id="kanban">
				
				<div class="kanban-item" style="color:var(--cor1);">
					<h1 class="kanban-item__titulo">CONFIRMAR HOJE<?php /* <span class="tooltip" title="Descrição do item..."><i class="iconify" data-icon="ph:question"></i></span>*/?></h1>
					<div class="kanban-card js-kanban-status js-kanban-status-hoje" data-id_status="hoje" style="min-height: 100px;">
					
					</div>
				</div>
				
				<div class="kanban-item" style="color:var(--cor1);">
					<h1 class="kanban-item__titulo">CONFIRMAR AMANHÃ<?php /* <span class="tooltip" title="Descrição do item..."><i class="iconify" data-icon="ph:question"></i></span>*/?></h1>
					<div class="kanban-card js-kanban-status js-kanban-status-amanha" data-id_status="amanha" style="min-height: 100px;">
						
					</div>
				</div>
				
				<?php /*<div class="kanban-item" style="background:<?php echo $s->cor;?>;color:var(--cor1);">
					<h1 class="kanban-item__titulo">CONFIRMAR DEPOIS DE AMANHÃ<?php /* <span class="tooltip" title="Descrição do item..."><i class="iconify" data-icon="ph:question"></i></span>*?></h1>
					<div class="kanban-card js-kanban-status js-kanban-status-depoisDeAmanha" data-id_status="depoisDeAmanha" style="min-height: 100px;">
						
					</div>
				</div>*/?>

				<?php /*<div class="kanban-item" style="background:#e1b8a5;color:var(--cor1);">
					<h1 class="kanban-item__titulo">REAGENDAR DESMARCOU/AGENDOU <?php /* <span class="tooltip" title="Descrição do item..."><i class="iconify" data-icon="ph:question"></i></span>*?></h1>
					<div class="kanban-card js-kanban-status js-kanban-status-reagendar" data-id_status="reagendar" style="min-height: 100px;">
						
					</div>
				</div>*/?>

				<div class="kanban-item" style="background:#d49d83;color:var(--cor1);">
					<h1 class="kanban-item__titulo">PACIENTES EM TRATAMENTO SEM HORÁRIO <span class="js-qtd-semHorario">(0)</span><?php /* <span class="tooltip" title="Descrição do item..."><i class="iconify" data-icon="ph:question"></i></span>*/?></h1>
					<div class="kanban-card js-kanban-status js-kanban-status-semHorario" data-id_status="semHorario" style="min-height: 100px;">
						
					</div>
				</div>
				<div class="kanban-item" style="background:#D38E69;color:var(--cor1);">
					<h1 class="kanban-item__titulo">PACIENTES EM CONTENÇÃO SEM HORÁRIO <span class="js-qtd-inteligencia">(0)</span><?php /* <span class="tooltip" title="Descrição do item..."><i class="iconify" data-icon="ph:question"></i></span>*/?></h1>
					<div class="kanban-card js-kanban-status js-kanban-status-inteligencia" data-id_status="inteligencia" style="min-height: 100px;">
						
					</div>
				</div>
				
			</div> 

		</section>

	</section>
			
<?php
	include "includes/footer.php";
?>