<?php
	
	require_once("lib/conf.php");
	require_once("usuarios/checa.php");
	$_table = $_p."agenda";

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

	$attr=array('prefixo'=>$_p,'usr'=>$usr);
	$infozap = new Whatsapp($attr);

	if(isset($_POST['ajax'])) {

		$rtn=array();
		if($_POST['ajax']=="editar") {

			$cnt = '';
			$paciente='';
			$carga = '';
			if(isset($_POST['id']) and is_numeric($_POST['id'])) {
				$sql->consult($_table,"*","where id=".$_POST['id']);
				if($sql->rows) {
					$cnt=mysqli_fetch_object($sql->mysqry);

					$sql->consult($_p."pacientes","id,nome,data_nascimento,telefone1,codigo_bi,musica,periodicidade,foto_cn","where id=$cnt->id_paciente");
					if($sql->rows) {
						$paciente=mysqli_fetch_object($sql->mysqry);
					}

				
				}
			}


			if(empty($cnt)) {
				$rtn=array('success'=>false,'error'=>'Registro não encontrado!');
			} else if(empty($cnt)) {
				$rtn=array('success'=>false,'error'=>'Paciente não encontrado!');
			} else {
				$profissionais='';
				if(!empty($cnt->profissionais)) {
					$profissioaisObj=explode(",",$cnt->profissionais);
					$profissionaisID=array(-1);
					foreach($profissioaisObj as $v) {
						if(!empty($v) and is_numeric($v)) $profissionaisID[]=$v;
					}
				}

				if($cnt->agendaPessoal==1) {

					$data = array('id'=>$cnt->id,
									'agendaPessoal'=>2,
									'agenda_data'=>date('d/m/Y',strtotime($cnt->agenda_data)),
									'agenda_hora'=>date('H:i',strtotime($cnt->agenda_data)),
									'agenda_duracao'=>$cnt->agenda_duracao,
									'id_status'=>$cnt->id_status,
									'id_cadeira'=>$cnt->id_cadeira,
									'profissionais'=>$profissionaisID,
									'obs'=>addslashes(utf8_encode($cnt->obs)));

				} else {

					$agendamentosFuturos=array();

					if($paciente->data_nascimento!="0000-00-00") {
						$dob = new DateTime($paciente->data_nascimento);
						$now = new DateTime();
						$idade = $now->diff($dob)->y;
					} else $idade=0;
				

					$_pacientesAgendamentos=array();
					$sql->consult($_p."agenda","*","where id_paciente=$paciente->id and agenda_data>'".date('Y-m-d')."' and id_status IN (1,2) and lixo=0 order by agenda_data");

					while($x=mysqli_fetch_object($sql->mysqry)) {

						// se for o mesmo agendamento que esta sendo editado
						if($x->id==$cnt->id) continue;

						$cor='';
						$iniciais='';


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

						$_pacientesAgendamentos[$x->id_paciente][]=array('id_agenda'=>$x->id,
																			'data'=>date('d/m/Y H:i',strtotime($x->agenda_data)),
																			'initDate'=>date('d/m/Y',strtotime($x->agenda_data)),
																			'cadeira'=>isset($_cadeiras[$x->id_cadeira])?utf8_encode($_cadeiras[$x->id_cadeira]->titulo):'',
																			'profissionais'=>$profissionais);
					}

				

					if(isset($_pacientesAgendamentos[$paciente->id])) {
						$agendamentosFuturos=$_pacientesAgendamentos[$paciente->id];
					}
				


					$_historico=array();
					$sql->consult($_p."pacientes_historico","*","where id_agenda=$cnt->id and lixo=0 order by data desc");

					
					while($x=mysqli_fetch_object($sql->mysqry)) {

						if($x->evento=="agendaHorario") {
								$_historico[]=array('usr'=>utf8_encode($_profissionais[$x->id_usuario]->nome),
																	'dt'=>date('d/m H:i',strtotime($x->data)),
																	'ev'=>'horario',
																	'nvDt'=>date('d/m H:i',strtotime($x->agenda_data_novo)),
																	'antDt'=>date('d/m H:i',strtotime($x->agenda_data_antigo))
																);

						} else {
							if(isset($_status[$x->id_status_novo])) { 
								$_historico[]=array('usr'=>isset($_profissionais[$x->id_usuario])?utf8_encode($_profissionais[$x->id_usuario]->nome):'',
																	'dt'=>date('d/m H:i',strtotime($x->data)),
																	'ev'=>'status',
																	'desc'=>utf8_encode($x->descricao),
																	'sts'=>utf8_encode($_status[$x->id_status_novo]->titulo),
																	'novo'=>$x->evento=="agendaNovo",
																	'cor'=>$_status[$x->id_status_novo]->cor
																);
							}
						}
						
					}



					$dias=strtotime(date('Y-m-d H:i:s'))-strtotime($cnt->data);
					$dias/=60*60*24;
					$dias=round($dias);

					$ft='';
					if(!empty($paciente->foto_cn)) {
						$ft=$_cloudinaryURL.'c_thumb,w_100,h_100/'.$paciente->foto_cn;
					}



					$data = array('id'=>$cnt->id,
									'agendou_profissional'=>isset($_profissionais[$cnt->id_usuario])?utf8_encode($_profissionais[$cnt->id_usuario]->nome):"-",
									'agendou_dias'=>(int)$dias,
									'agendaPessoal'=>0,
									'ft'=>$ft,
									'agenda_data'=>date('d/m/Y',strtotime($cnt->agenda_data)),
									'agenda_hora'=>date('H:i',strtotime($cnt->agenda_data)),
									'agenda_duracao'=>$cnt->agenda_duracao,
									'id_paciente'=>$cnt->id_paciente,
									'id_status'=>$cnt->id_status,
									'nome'=>addslashes(utf8_encode($paciente->nome)),
									'idade'=>(int)$idade,
									'id_cadeira'=>$cnt->id_cadeira,
									'telefone1'=>$paciente->telefone1,
									'musica'=>utf8_encode($paciente->musica),
									'periodicidade'=>isset($_pacientesPeriodicidade[$paciente->periodicidade])?$_pacientesPeriodicidade[$paciente->periodicidade]:$paciente->periodicidade,
									'profissionais'=>$profissionaisID,
									'statusBI'=>isset($_codigoBI[$paciente->codigo_bi])?utf8_encode($_codigoBI[$paciente->codigo_bi]):"",
									'obs'=>addslashes(utf8_encode($cnt->obs)),
									'agendamentosFuturos'=>$agendamentosFuturos,
									'historico'=>$_historico);
				}
	
	

				$rtn=array('success'=>true,'data'=>$data);

			}
		} 

		else if($_POST['ajax']=="remover") {
			$cnt = '';
			if(isset($_POST['id']) and is_numeric($_POST['id'])) {
				$sql->consult($_table,"*","where id=".$_POST['id']);
				if($sql->rows) {
					$cnt=mysqli_fetch_object($sql->mysqry);
				}
			}

			if(empty($cnt)) {
				$rtn=array('success'=>false,'error'=>'Registro não encontrado!');
			} else {

				
				$vWHERE="where id=$cnt->id";
				$vSQL="lixo=1";
				$sql->update($_table,$vSQL,$vWHERE);
				$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='delete',vsql='".addslashes($vSQL)."',vwhere='".addslashes($vWHERE)."',tabela='$_table',id_reg='".$cnt->id."'");

				$rtn=array('success'=>true);

			}
		} 

		else if($_POST['ajax']=="persistirPaciente") {

			$nome=(isset($_POST['nome']) and !empty($_POST['nome']))?$_POST['nome']:'';
			$cpf=(isset($_POST['cpf']) and !empty($_POST['cpf']))?$_POST['cpf']:'';
			$telefone1=(isset($_POST['telefone1']) and !empty($_POST['telefone1']))?$_POST['telefone1']:'';
			$indicacao_tipo=(isset($_POST['indicacao_tipo']) and !empty($_POST['indicacao_tipo']))?$_POST['indicacao_tipo']:'';
			$indicacao=(isset($_POST['indicacao']) and !empty($_POST['indicacao']))?$_POST['indicacao']:'';

			if(empty($nome) or empty($telefone1)) {
				$rtn=array('success'=>false,'error'=>'Nome/Telefone não definidos');
			} else {

				$cpfDuplicado=false;

				if(!empty($cpf)) {
					$sql->consult($_p."pacientes","*","where cpf='".addslashes(cpf($cpf))."' and lixo=0");
					if($sql->rows) {
						$cpfDuplicado=true;
					}
				}

				if($cpfDuplicado===true) {
					$rtn=array('success'=>false,'error'=>'Já existe paciente cadastrado com este CPF');
				} else {
					$vSQL="data=now(),
							nome='".addslashes(utf8_decode(strtoupperWLIB($nome)))."',
							cpf='".addslashes(cpf($cpf))."',
							telefone1='".addslashes(telefone($telefone1))."',
							indicacao_tipo='".addslashes($indicacao_tipo)."',
							indicacao='".addslashes($indicacao)."'";

					$sql->add($_p."pacientes",$vSQL);
					$id_paciente=$sql->ulid;
					$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='insert',vsql='".addslashes($vSQL)."',tabela='".$_p."pacientes',id_reg='".$id_paciente."'");

					$rtn=array('success'=>true,
								'id_paciente'=>$id_paciente,
								'telefone1'=>$telefone1,
								'paciente'=>strtoupperWLIB($nome));
				}
			}
		} 

		else if($_POST['ajax']=="persistirPacienteTelefone") {

			$paciente='';
			if(isset($_POST['id_paciente']) and is_numeric($_POST['id_paciente'])) {
				$sql->consult($_p."pacientes","*","where id='".addslashes($_POST['id_paciente'])."'");
				if($sql->rows) {
					$paciente=mysqli_fetch_object($sql->mysqry);
				}
			}

			$telefone1=(isset($_POST['telefone1']) and !empty($_POST['telefone1']))?$_POST['telefone1']:'';

			if(empty($paciente)) {
				$rtn=array('success'=>false,'error'=>'Paciente não encontrado');
			} else if(empty($telefone1)) {
				$rtn=array('success'=>false,'error'=>'Telefone não definido');
			} else {

				
				$vSQL="telefone1='".addslashes(telefone($telefone1))."'";

				$vWHERE="WHERE id=$paciente->id";
				$sql->update($_p."pacientes",$vSQL,$vWHERE);
				$id_paciente=$sql->ulid;
				$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='update',vsql='".addslashes($vSQL)."',vwhere='".addslashes($vWHERE)."',tabela='".$_p."pacientes',id_reg='".$id_paciente."'");

				$rtn=array('success'=>true,
							'id_paciente'=>$id_paciente,
							'telefone1'=>$telefone1);
				
			}
		} 

		else if($_POST['ajax']=="indicacoesLista") {

			$indicacao='';
			if(isset($_POST['indicacao_tipo']) and is_numeric($_POST['indicacao_tipo'])) {
				$sql->consult($_p."parametros_indicacoes","*","where id='".$_POST['indicacao_tipo']."'");
				if($sql->rows) {
					$indicacao=mysqli_fetch_object($sql->mysqry);
				}
			}

			$listas=array();
			if(is_object($indicacao)) {
				if($indicacao->tipo=="PACIENTE") {
					$sql->consult($_p."pacientes","*","where lixo=0 order by nome asc"); 
					if($sql->rows) {
						while($x=mysqli_fetch_object($sql->mysqry)) {
							$listas[]=array('id'=>$x->id,'value'=>utf8_encode($x->nome));
						}
					}
				} else { 
					$sql->consult($_p."parametros_indicacoes_listas","*","where id_indicacao=$indicacao->id and lixo=0");
					if($sql->rows) {
						while($x=mysqli_fetch_object($sql->mysqry)) {
							$listas[]=array('id'=>$x->id,'value'=>utf8_encode($x->titulo));
						}
					}
				}
			}

			$rtn=array('success'=>true,'listas'=>$listas);
		} 

		else if($_POST['ajax']=="persistirNovoAgendamento") {
			$agenda='';
			if(isset($_POST['id_agenda']) and is_numeric($_POST['id_agenda'])) {
				$sql->consult($_p."agenda","*","where id='".$_POST['id_agenda']."'");
				if($sql->rows) {
					$agenda=mysqli_fetch_object($sql->mysqry);
				}
			}

			if(empty($agenda)) {
				$rtn=array('success'=>false,'error'=>'Agendamento não encontrado!');
			} else {
				$novaData=date('Y-m-d H:i:s',strtotime($_POST['novaData']));
				$vSQL="agenda_data='".$novaData."'";
				if(isset($_POST['id_cadeira']) and is_numeric($_POST['id_cadeira'])) $vSQL.=",id_cadeira='".$_POST['id_cadeira']."'";
				if($agenda->agendaPessoal==1) {
					if($agenda->dia_inteiro==0) {
						$dif=strtotime($agenda->agenda_data_final)-strtotime($agenda->agenda_data);
						$novaDataFinal=date('Y-m-d H:i',strtotime($novaData." + $dif SECONDS"));
						$vSQL.=",agenda_data_final='".$novaDataFinal."'";
						

					}
				}
				$vSQL.=",data_atualizacao=now(),id_usuario=$usr->id";

				$sql->update($_p."agenda",$vSQL,"where id=$agenda->id");

				$agendaAlterado=$novaData;
				if(strtotime($agenda->agenda_data)!=strtotime($agendaAlterado)) {
					$vSQLHistorico="data=now(),
						id_usuario=$usr->id,
						evento='agendaHorario',
						id_paciente=$agenda->id_paciente,
						id_agenda=$agenda->id,
						agenda_data_antigo='$agenda->agenda_data',
						agenda_data_novo='$agendaAlterado',
						descricao=''";
					$sql->add($_p."pacientes_historico",$vSQLHistorico);

					// altera campo agenda_alteracao_data para enviar a notificacao de alteração de horário
					$sql->update($_p."agenda","agenda_alteracao_data=now(),agenda_alteracao_id_whatsapp=0","where id=$agenda->id");
				}


				$rtn=array('success'=>true);
			}
		} 

		else if($_POST['ajax']=="persistirNovoHorario") {
			$agenda='';
			if(isset($_POST['id_agenda']) and is_numeric($_POST['id_agenda'])) {
				$sql->consult($_p."agenda","*","where id='".$_POST['id_agenda']."'");
				if($sql->rows) {
					$agenda=mysqli_fetch_object($sql->mysqry);
				}
			}

			if(empty($agenda)) {
				$rtn=array('success'=>false,'error'=>'Agendamento não encontrado!');
			} else {

				$start=date('Y-m-d H:i:s',strtotime($_POST['start']));
				$end=date('Y-m-d H:i:s',strtotime($_POST['end']));

				$vSQL="agenda_data='".$start."'";$vSQL.=",agenda_data_final='".$end."'";
				if($agenda->agendaPessoal==1) {
					
				} else {
					$dif=(strtotime($end)-strtotime($start))/60;
					$vSQL.=",agenda_duracao='$dif'";
				}
				//echo $vSQL;
				$sql->update($_p."agenda",$vSQL,"where id=$agenda->id");


				$agendaAlterado=$_POST['start'];
				if(strtotime($agenda->agenda_data)!=strtotime($agendaAlterado)) {
					$vSQLHistorico="data=now(),
						id_usuario=$usr->id,
						evento='agendaHorario',
						id_paciente=$agenda->id_paciente,
						id_agenda=$agenda->id,
						agenda_data_antigo='$agenda->agenda_data',
						agenda_data_novo='$agendaAlterado',
						id_status_novo=".$idStatusNovo.",
						descricao=''";
					$sql->add($_p."pacientes_historico",$vSQLHistorico);


					// altera campo agenda_alteracao_data para enviar a notificacao de alteração de horário
					$sql->update($_p."agenda","agenda_alteracao_data=now(),agenda_alteracao_id_whatsapp=0","where id=$agenda->id");
				}

				$rtn=array('success'=>true);
			}
		}

		else if($_POST['ajax']=="agendamentoPersistir") {
			
			$profissional='';
			if(isset($_POST['id_profissional']) and is_numeric($_POST['id_profissional'])) {
				$sql->consult($_p."colaboradores","*","where id='".$_POST['id_profissional']."' and lixo=0");
				if($sql->rows) {
					$profissional=mysqli_fetch_object($sql->mysqry);

					$profissionalUnidades=explode(",",$profissional->unidades);
				}
			}

			$profissionais=array();
			if(isset($_POST['profissionais']) and !empty($_POST['profissionais'])) {
				$pAux=explode(",",$_POST['profissionais']);
				foreach($pAux as $id_profissional) {
					if(is_numeric($id_profissional)) $profissionais[]=$id_profissional;
				}
			}

			$cadeira='';
			if(isset($_POST['id_cadeira']) and is_numeric($_POST['id_cadeira'])) {
				$sql->consult($_p."parametros_cadeiras","*","where id='".$_POST['id_cadeira']."' and lixo=0");
				if($sql->rows) {
					$cadeira=mysqli_fetch_object($sql->mysqry);
				}
			}
			$status='';
			if(isset($_POST['id_status']) and is_numeric($_POST['id_status'])) {
				$sql->consult($_p."agenda_status","*","where id='".$_POST['id_status']."' and lixo=0");
				if($sql->rows) {
					$status=mysqli_fetch_object($sql->mysqry);
				}
			}
			$agendaData='';
			if(isset($_POST['agenda_data']) and !empty($_POST['agenda_data'])) {
				list($_dia,$_mes,$_ano)=explode("/",$_POST['agenda_data']);
				if(checkdate($_mes, $_dia, $_ano)) {
					$agendaData=$_ano."-".$_mes."-".$_dia;
				}
			}

			$agendaHora='';
			if(isset($_POST['agenda_hora']) and !empty($_POST['agenda_hora'])) {
				list($_h,$_m)=explode(":",$_POST['agenda_hora']);
				if(is_numeric($_h) and is_numeric($_m)) {
					$agendaHora=$_h.":".$_m;
				}
			}

			$agenda='';
			if(isset($_POST['id']) and is_numeric($_POST['id'])) {
				$sql->consult($_p."agenda","*","where id='".$_POST['id']."'");
				if($sql->rows) {
					$agenda=mysqli_fetch_object($sql->mysqry);
				}
			}

			if(empty($agenda)) {
				$rtn=array('success'=>false,'error'=>'Agendamento não encontrado!');
			} 
			else if(count($profissionais)==0) {
				$rtn=array('success'=>false,'error'=>'Profissional não encontrado!');
			} 
			else if(empty($cadeira)) {
				$rtn=array('success'=>false,'error'=>'Selecione a cadeira!');
			} 
			else if(empty($status)) {
				$rtn=array('success'=>false,'error'=>'Selecione o status!');
			} 
			if(empty($agendaData)) {
				$rtn=array('success'=>false,'error'=>'Data inválida!');
			} else if(empty($agendaHora)) {
				$rtn=array('success'=>false,'error'=>'Hora inválida!');
			} else {
				$novoPaciente=false;
				$paciente=$pacienteUnidades='';
				$erro='';

				$sql->consult($_p."pacientes","*","where id=$agenda->id_paciente and lixo=0");
				if($sql->rows) {
					$paciente=mysqli_fetch_object($sql->mysqry);
				}
				
				if(!empty($erro)) {
					$rtn=array('success'=>false,'error'=>$erro);
				}
				else if(empty($paciente)) {
					$rtn=array('success'=>false,'error'=>'Paciente não encontrado!');
				} else {

					$agendaData.=" ".$agendaHora;


					$duracao = (isset($_POST['agenda_duracao']) and is_numeric($_POST['agenda_duracao']))?$_POST['agenda_duracao']:30;

					$vSQL="id_paciente=$paciente->id,
							profissionais=',".implode(",",$profissionais).",',
							id_cadeira=$cadeira->id,
							id_status=$status->id,
							agenda_data='".$agendaData."',
							agenda_duracao='".addslashes($_POST['agenda_duracao'])."',
						agenda_data_final='".date('Y-m-d H:i:s',strtotime($agendaData." + $duracao minutes"))."'
							";



					if(isset($_POST['obs'])) $vSQL.=",obs='".addslashes(utf8_decode($_POST['obs']))."'";



					$idStatusNovo=((isset($_POST['id_status']) and is_numeric($_POST['id_status']))?$_POST['id_status']:'');
					
					$vWHERE="where id=$agenda->id";

					$sql->update($_p."agenda",$vSQL,$vWHERE);
					$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='update',vsql='".addslashes($vSQL)."',vwhere='".addslashes($vWHERE)."',tabela='".$_p."agenda',id_reg='".$agenda->id."'");

					if($agenda->id_status!=$_POST['id_status']) {
						$vSQLHistorico="data=now(),
								id_usuario=$usr->id,
								evento='agendaStatus',
								id_paciente=$agenda->id_paciente,
								id_agenda=$agenda->id,
								id_status_antigo=$agenda->id_status,
								id_status_novo=".$idStatusNovo.",
								descricao=''";
						$sql->add($_p."pacientes_historico",$vSQLHistorico);

					}


					$agendaAlterado=invDate($_POST['agenda_data'])." ".$_POST['agenda_hora'].":00";

					if(strtotime($agenda->agenda_data)!=strtotime($agendaAlterado)) {
						$vSQLHistorico="data=now(),
							id_usuario=$usr->id,
							evento='agendaHorario',
							id_paciente=$agenda->id_paciente,
							id_agenda=$agenda->id,
							agenda_data_antigo='$agenda->agenda_data',
							agenda_data_novo='$agendaAlterado',
							id_status_novo=".$idStatusNovo.",
							descricao=''";
						$sql->add($_p."pacientes_historico",$vSQLHistorico);


						// altera campo agenda_alteracao_data para enviar a notificacao de alteração de horário
						$sql->update($_p."agenda","agenda_alteracao_data=now(),agenda_alteracao_id_whatsapp=0","where id=$agenda->id");
					}

					// veririca se desmarcou
					$wts=0;
					//if($agenda->id_status!=$idStatusNovo) {
						
						// Se Desmarcou
						if($idStatusNovo==4) {

							$attr=array('id_tipo'=>3,
										'id_paciente'=>$agenda->id_paciente,
										'id_agenda'=>$agenda->id);
							//var_dump($attr);
							if($infozap->adicionaNaFila($attr)) $wts=1;

						} 
						// Se Confirmou
						else if($idStatusNovo==2) {

							// se virou para confirmado, envia wts para dentista
							$sql->consult($_p."agenda","*","where id=$agenda->id and id_status=2");
							if($sql->rows) {
								$agendaNew=mysqli_fetch_object($sql->mysqry); // registro de agenda atualizado

								if(!empty($agendaNew->profissionais)) {

									$profissionaisIds=array();
									$auxProfissionais = explode(",",$agenda->profissionais);
									foreach($auxProfissionais as $idProfissional) {
										if(!empty($idProfissional) and is_numeric($idProfissional)) {
											$profissionaisIds[]=$idProfissional;
										}
									}

									if(count($profissionaisIds)>0) {
										$sql->consult($_p."colaboradores","*","where id IN (".implode(",",$profissionaisIds).") and whatsapp_notificacoes=1 and lixo=0");
										while($x=mysqli_fetch_object($sql->mysqry)) {
											if(!empty($x->telefone1)) {
												$attr=array('id_tipo'=>6,
															'id_paciente'=>$agendaNew->id_paciente,
															'id_profissional'=>$x->id,
															'id_agenda'=>$agendaNew->id);
									
												if($infozap->adicionaNaFila($attr)) {
													$wts=1;

												}
											}
										}
									}

								}
							}
						}
					//}


				

					$rtn=array('success'=>true,'wts'=>$wts);
				}
				
			}
		} 

		else if($_POST['ajax']=="agendamentoRemover") {
			if(isset($_POST['id']) and is_numeric($_POST['id'])) {
				$sql->consult($_p."agenda","*","where id='".$_POST['id']."'");
				if($sql->rows) {
					$agenda=mysqli_fetch_object($sql->mysqry);
				}
			}


			if(isset($agenda) and is_object($agenda)) {
				$vSQL="lixo=1";
				$vWHERE="where id=$agenda->id";

				$sql->update($_p."agenda",$vSQL,$vWHERE);

				$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='update',vwhere='".addslashes($vWHERE)."',vsql='".addslashes($vSQL)."',tabela='".$_p."agenda',id_reg='".$agenda->id."'");

				$rtn=array('success'=>true);

			} else {
				$rtn=array('success'=>false,'error'=>'Agendamento não encontrado');
			}
		} 

		else if($_POST['ajax']=="agendamentoPessoalPersistir") {
			
			$profissional='';
			if(isset($_POST['id_profissional']) and is_numeric($_POST['id_profissional'])) {
				$sql->consult($_p."colaboradores","*","where id='".$_POST['id_profissional']."' and lixo=0");
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
			$agendaData='';
			if(isset($_POST['agenda_data']) and !empty($_POST['agenda_data'])) {
				list($_dia,$_mes,$_ano)=explode("/",$_POST['agenda_data']);
				if(checkdate($_mes, $_dia, $_ano)) {
					$agendaData=$_ano."-".$_mes."-".$_dia;
				}
			}

			$agendaHora='';
			if(isset($_POST['agenda_hora']) and !empty($_POST['agenda_hora'])) {
				list($_h,$_m)=explode(":",$_POST['agenda_hora']);
				if(is_numeric($_h) and is_numeric($_m)) {
					$agendaHora=$_h.":".$_m;
				}
			}

			$agenda='';
			if(isset($_POST['id']) and is_numeric($_POST['id'])) {
				$sql->consult($_p."agenda","*","where id='".$_POST['id']."'");
				if($sql->rows) {
					$agenda=mysqli_fetch_object($sql->mysqry);
				}
			}

			if(empty($agenda)) {
				$rtn=array('success'=>false,'error'=>'Agendamento não encontrado!');
			} 
			else if(empty($profissional)) {
				$rtn=array('success'=>false,'error'=>'Profissional não encontrado!');
			} 
			else if(empty($cadeira)) {
				$rtn=array('success'=>false,'error'=>'Selecione a cadeira!');
			} 
			if(empty($agendaData)) {
				$rtn=array('success'=>false,'error'=>'Data inválida!');
			} else if(empty($agendaHora)) {
				$rtn=array('success'=>false,'error'=>'Hora inválida!');
			} else {
			
				$erro='';

				
				if(!empty($erro)) {
					$rtn=array('success'=>false,'error'=>$erro);
				}
				 else {

					$agendaData.=" ".$agendaHora;


					$duracao = (isset($_POST['agenda_duracao']) and is_numeric($_POST['agenda_duracao']))?$_POST['agenda_duracao']:30;

				

					$vSQL="profissionais=',".implode(",",array($profissional->id)).",',
							id_cadeira=$cadeira->id,
							agenda_data='".$agendaData."',
							agenda_data_final='".date('Y-m-d H:i:s',strtotime($agendaData." + $duracao minutes"))."',
							agenda_duracao='".$duracao."'
							";



					if(isset($_POST['obs'])) $vSQL.=",obs='".addslashes(utf8_decode($_POST['obs']))."'";

					
					$vWHERE="where id=$agenda->id";

					$sql->update($_p."agenda",$vSQL,$vWHERE);
					$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='update',vsql='".addslashes($vSQL)."',vwhere='".addslashes($vWHERE)."',tabela='".$_p."agenda',id_reg='".$agenda->id."'");

				

					$rtn=array('success'=>true);
				}
				
			}
		}

		else if($_POST['ajax']=="novoAgendamento") {
			$profissionais=array();
			if(isset($_POST['profissionais']) and !empty($_POST['profissionais'])) {
				$pAux=explode(",",$_POST['profissionais']);
				foreach($pAux as $id_profissional) {
					if(is_numeric($id_profissional)) $profissionais[]=$id_profissional;
				}
			}

			$cadeira='';
			if(isset($_POST['id_cadeira']) and is_numeric($_POST['id_cadeira'])) {
				$sql->consult($_p."parametros_cadeiras","*","where id='".$_POST['id_cadeira']."' and lixo=0");
				if($sql->rows) {
					$cadeira=mysqli_fetch_object($sql->mysqry);
				}
			}
			$status='';
			if(isset($_POST['id_status']) and is_numeric($_POST['id_status'])) {
				$sql->consult($_p."agenda_status","*","where id='".$_POST['id_status']."' and lixo=0");
				if($sql->rows) {
					$status=mysqli_fetch_object($sql->mysqry);
				}
			}
			$agendaData='';
			if(isset($_POST['agenda_data']) and !empty($_POST['agenda_data'])) {
				list($_dia,$_mes,$_ano)=explode("/",$_POST['agenda_data']);
				if(checkdate($_mes, $_dia, $_ano)) {
					$agendaData=$_ano."-".$_mes."-".$_dia;
				}
			}

			$agendaHora='';
			if(isset($_POST['agenda_hora']) and !empty($_POST['agenda_hora'])) {
				list($_h,$_m)=explode(":",$_POST['agenda_hora']);
				if(is_numeric($_h) and is_numeric($_m)) {
					$agendaHora=$_h.":".$_m;
				}
			}

			$paciente='';
			if(isset($_POST['id_paciente']) and is_numeric($_POST['id_paciente'])) {
				$sql->consult($_p."pacientes","id,nome","where id='".$_POST['id_paciente']."' and lixo=0");
				if($sql->rows) {
					$paciente=mysqli_fetch_object($sql->mysqry);
				}
			}

			if($_POST['agendaPessoal']==1) {
				 if(count($profissionais)==0) {
					$rtn=array('success'=>false,'error'=>'Profissional não encontrado!');
				} 
				else if(empty($cadeira)) {
					$rtn=array('success'=>false,'error'=>'Selecione o consultório!');
				} 
				else {
					$agendaData.=" ".$agendaHora;

					$duracao = (isset($_POST['agenda_duracao']) and is_numeric($_POST['agenda_duracao']))?$_POST['agenda_duracao']:30;

					$vSQL="profissionais=',".implode(",",$profissionais).",',
							id_cadeira=$cadeira->id,
							id_unidade=1,
							agenda_data='".$agendaData."',
							agenda_data_original='".$agendaData."',
							agenda_data_final='".date('Y-m-d H:i:s',strtotime($agendaData." + $duracao minutes"))."',
							agenda_duracao='".$duracao."',
							agendaPessoal=1
							";
					
					$vSQL.=",data=now(),id_usuario=$usr->id";
					$sql->add($_p."agenda",$vSQL);
					$id_agenda=$sql->ulid;
					$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='insert',vsql='".addslashes($vSQL)."',tabela='".$_p."agenda',id_reg='".$id_agenda."'");
					$rtn=array('success'=>true);


				}
			} else {

				if(empty($paciente)) {
					$rtn=array('success'=>false,'error'=>'Selecione o paciente!');
				} 
				else if(count($profissionais)==0) {
					$rtn=array('success'=>false,'error'=>'Profissional não encontrado!');
				} 
				else if(!is_object($cadeira)) {
					$rtn=array('success'=>false,'error'=>'Selecione o consultório!');
				} 
				else if(empty($status)) {
					$rtn=array('success'=>false,'error'=>'Selecione o status!');
				} 
				else if(empty($agendaData)) {
					$rtn=array('success'=>false,'error'=>'Data inválida!');
				} else if(empty($agendaHora)) {
					$rtn=array('success'=>false,'error'=>'Hora inválida!');
				} else {
					$idStatusNovo=((isset($_POST['id_status']) and is_numeric($_POST['id_status']))?$_POST['id_status']:'');

					$agendaData.=" ".$agendaHora;

					$duracao = (isset($_POST['agenda_duracao']) and is_numeric($_POST['agenda_duracao']))?$_POST['agenda_duracao']:30;

					$vSQL="id_paciente=$paciente->id,
							profissionais=',".implode(",",$profissionais).",',
							id_cadeira='$cadeira->id',
							id_unidade=1,
							id_status=$status->id,
							agenda_data='".$agendaData."',
							agenda_data_original='".$agendaData."',
							agenda_data_final='".date('Y-m-d H:i:s',strtotime($agendaData." + $duracao minutes"))."',
							agenda_duracao='".$duracao."'
							";



					if(isset($_POST['obs'])) $vSQL.=",obs='".addslashes(utf8_decode($_POST['obs']))."'";


					$vSQL.=",data=now(),id_usuario=$usr->id";
					$sql->add($_p."agenda",$vSQL);
					$id_agenda=$sql->ulid;
					$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='insert',vsql='".addslashes($vSQL)."',tabela='".$_p."agenda',id_reg='".$id_agenda."'");

					$idPaciente=((isset($_POST['id_paciente']) && is_numeric($_POST['id_paciente']))?$_POST['id_paciente']:'');
					$vSQLHistorico="data=now(),
						id_usuario=$usr->id,
						evento='agendaNovo',
						id_paciente=".$idPaciente.",
						id_agenda=$id_agenda,
						id_status_antigo=0,
						id_status_novo=".$idStatusNovo.",
						descricao=''";
					$sql->add($_p."pacientes_historico",$vSQLHistorico);
					$rtn=array('success'=>true);

				}
			}
		}

		else if($_POST['ajax']=="whatsappDisparar") {

			$infozap->dispara();
			$rtn=array('success'=>true);
		}

		else if($_POST['ajax']=="agendamentosProfissionais") {

			$data = (isset($_POST['data']) and isset($_POST['data']))?invDate($_POST['data']):'';
			$hora = (isset($_POST['hora']) and isset($_POST['hora']))?$_POST['hora'].":00":'';
			$id_cadeira = (isset($_POST['id_cadeira']) and isset($_POST['id_cadeira']))?$_POST['id_cadeira']:0;

			if(!empty($data) and !empty($hora)) {
				$diaSemana = date('w',strtotime($data));
				

				$profissionaisDestaque=array();
				$where="where dia='$diaSemana' and 
								inicio<='$hora' and 
								fim>='$hora' and 
 								lixo=0";
 				if($id_cadeira>0) $where.=" and id_cadeira=$id_cadeira";
				$sql->consult($_p."profissionais_horarios","distinct id_profissional",$where);
			//	echo $where."->".$sql->rows."\n";
				while($x=mysqli_fetch_object($sql->mysqry)) {
					//echo $x->id_profissional."\n";
					$profissionaisDestaque[$x->id_profissional]=1;
				}

				$listaProfissionais=$listaProfissionaisDestaque=array();
				foreach($_profissionais as $x) {
					if($x->check_agendamento==0) continue;
					if(isset($profissionaisDestaque[$x->id])) {
						$listaProfissionaisDestaque[]=(object)array('id'=>$x->id,'nome'=>utf8_encode($x->nome),'destaque'=>1);
					}
				}


				foreach($_profissionais as $x) {
					if($x->check_agendamento==0) continue;
					if(!isset($profissionaisDestaque[$x->id])) {
						$listaProfissionais[]=(object)array('id'=>$x->id,'nome'=>utf8_encode($x->nome),'destaque'=>0);
					}
				}

				$rtn=array('success'=>true,
							'listaProfissionaisDestaque'=>$listaProfissionaisDestaque,
							'listaProfissionais'=>$listaProfissionais);


			}
		}

		header("Content-type: application/json");
		echo json_encode($rtn);
		die();
	}

	if(isset($_GET['ajax'])) {
		require_once("lib/conf.php");
		require_once("usuarios/checa.php");
		$sql2=new Mysql();
		$str=new StringW();
		$rtn=array();
		if($_GET['ajax']=="agenda") {


			if(isset($_GET['start'])) {
				$start = new DateTime();
				$start->setTimestamp($_GET['start']/1000);
				$data_inicio=$start->format('Y-m-d');
			}

			if(isset($_GET['end'])) {
				$end = new DateTime();
				$end->setTimestamp($_GET['end']/1000);
				$data_fim=$end->format('Y-m-d');
			}

			if(empty($start)) $data_inicio=date('Y-m-01');
			if(empty($end)) $data_fim=date('Y-m-31');


			$_pacientes=array();
			$sql->consult($_p."pacientes","*","where lixo=0");
			while($x=mysqli_fetch_object($sql->mysqry)) $_pacientes[$x->id]=$x;

			$agendamentos=$agendamentosDesmarcados=array();
			$where="where agenda_data>='".$data_inicio." 00:00:00' and agenda_data<='".$data_fim."' and lixo=0";

			if(isset($_GET['id_status']) and is_numeric($_GET['id_status'])) $where.=" and id_status='".$_GET['id_status']."'";
			if(isset($_GET['id_cadeira']) and is_numeric($_GET['id_cadeira']) and $_GET['id_cadeira']>0) $where.=" and id_cadeira='".$_GET['id_cadeira']."'";
			if(isset($_GET['id_profissional']) and is_numeric($_GET['id_profissional']) and $_GET['id_profissional']>0) $where.=" and profissionais like '%,".$_GET['id_profissional'].",%'";
			if(isset($_GET['busca']) and !empty($_GET['busca'])) {
				$sql->consult($_p."pacientes","*","where nome like '%".addslashes($_GET['busca'])."%'");
				if($sql->rows) {
					$pacientesIDs=array();
					while($x=mysqli_fetch_object($sql->mysqry)) $pacientesIDs[]=$x->id;
					$where.=" and id_paciente IN (".implode(",",$pacientesIDs).")";
				} else $where.=" and 2=1";
			}

			$_usuarios=array();
			$sql->consult($_p."colaboradores","id,nome,calendario_iniciais,foto,calendario_cor,check_agendamento","where lixo=0 order by nome asc");
			while($x=mysqli_fetch_object($sql->mysqry)) {
				$_usuarios[$x->id]=$x;
			}


			$registros=$registrosDesmarcados=array();
			$pacientesIds=$agendaIds=array();
			
			$sql->consult($_p."agenda","*",$where);
			if($sql->rows) {

				while($x=mysqli_fetch_object($sql->mysqry)) {
					// se for desmarcado
					if($x->id_status==4) {
						$registrosDesmarcados[]=$x;
					} else {
						$registros[]=$x;
					}
					$pacientesIds[]=$x->id_paciente;
					$agendaIds[]=$x->id;
				}


				$_agendamentosConfirmacaoWts=array();
				/*if(count($agendaIds)>0) {
					$sql->consult($_p."whatsapp_mensagens","*","where id_agenda IN (".implode(",",$agendaIds).")");
					while($x=mysqli_fetch_object($sql->mysqry)) {
						$_agendamentosConfirmacaoWts[$x->id_agenda]=1;
					}
				}*/

				// Agendamentos não desmarcados
				foreach($registros as $x) {
				
					if($x->agendaPessoal==0 and isset($_pacientes[$x->id_paciente])) {

						if($_pacientes[$x->id_paciente]->data_nascimento!="0000-00-00") {
							$dob = new DateTime($_pacientes[$x->id_paciente]->data_nascimento);
							$now = new DateTime();
							$idade = $now->diff($dob)->y;
						} else {
							$idade = "";
						}

						$cadeira=isset($_cadeiras[$x->id_cadeira])?utf8_encode($_cadeiras[$x->id_cadeira]->titulo):'-';
						$dtStart=date('Y-m-d\TH:i',strtotime($x->agenda_data));
						$dtEnd=date('Y-m-d\TH:i',strtotime($x->agenda_data." + $x->agenda_duracao minutes"));
						$hora=date('H:i',strtotime($x->agenda_data));
						$horaFinal=date('H:i',strtotime($x->agenda_data." + $x->agenda_duracao minutes"));

						
						$aux = explode(",",$x->profissionais);
						$profissionais=array();
						foreach($aux as $id_profissional) {
							if(!empty($id_profissional) and is_numeric($id_profissional)) {
								if(isset($_profissionais[$id_profissional])) {
									$p=$_profissionais[$id_profissional];
									$aux=explode(" ",$p->nome);
									$aux[0]=strtoupper($aux[0]);
									$profissionais[]=array('iniciais'=>$p->calendario_iniciais,
															'cor'=>$p->calendario_cor);
								}
							}
						}

						$aux=explode(" ",trim(utf8_encode($_pacientes[$x->id_paciente]->nome)));
						//$pacienteNome=$aux[0];
						$pacienteNome=tirarAcentos(utf8_encode($_pacientes[$x->id_paciente]->nome));
						//if(count($aux)>1) $pacienteNome.=" ".$aux[count($aux)-1];

						$agendadoPor="";
						if(isset($_usuarios[$x->id_usuario])) {
							list($pNome,)=explode(" ",utf8_encode($_usuarios[$x->id_usuario]->nome));
							$agendadoPor=$pNome;
						}	

						$dias=round((strtotime(date('Y-m-d H:i:s'))-strtotime($x->data_atualizacao=="0000-00-00 00:00:00"?$x->data:$x->data_atualizacao))/(60 * 60 * 24));

						if($dias==0) $agendadoHa="Agendado&nbsp;<strong>HOJE</strong>";
						else if($dias==1) $agendadoHa="Agendado&nbsp;<strong>ONTEM</strong>";
						else $agendadoHa="agendou há&nbsp;<strong>$dias</strong>&nbsp;dias";
						
						$agendamentosFuturos=array();
						if(isset($_pacientesAgendamentos[$x->id_paciente])) {
							$agendamentosFuturos=$_pacientesAgendamentos[$x->id_paciente];
						}



						$futuro=0;
						if(strtotime(date('Y-m-d',strtotime($x->agenda_data)))>strtotime(date('Y-m-d'))) $futuro=1;

						$agendamentos[]=array('agendaPessoal'=>0,			
												'resourceId'=>$x->id_cadeira,
												'start'=>$dtStart,
												'end'=>$dtEnd,
												'hora'=>$hora,
												'horaFinal'=>$horaFinal,
												'futuro'=>$futuro,
												'cadeira'=>$cadeira,
												'id_paciente'=>$x->id_paciente,
												'title'=>($pacienteNome),
												'nomeCompleto'=>$pacienteNome,
												'situacao'=>utf8_encode($_pacientes[$x->id_paciente]->situacao),
												'id_status'=>$x->id_status,
												'profissionais'=>$profissionais,
												'color'=>'#FFF',
												'statusColor'=>(isset($_status[$x->id_status])?$_status[$x->id_status]->cor:''),
												'id'=>$x->id,
												'wts'=>(int)isset($_agendamentosConfirmacaoWts[$x->id])?1:0
											);
					} else if($x->agendaPessoal==1) {

						$cadeira=isset($_cadeiras[$x->id_cadeira])?utf8_encode($_cadeiras[$x->id_cadeira]->titulo):'-';
						$dtStart=date('Y-m-d\TH:i',strtotime($x->agenda_data));
						$dtEnd=date('Y-m-d\TH:i',strtotime($x->agenda_data_final));
						$hora=date('H:i',strtotime($x->agenda_data));
						$horaFinal=date('H:i',strtotime($x->agenda_data_final));

						$aux = explode(",",$x->profissionais);
						$profissionais=array();
						foreach($aux as $id_profissional) {
							if(!empty($id_profissional) and is_numeric($id_profissional)) {
								if(isset($_profissionais[$id_profissional])) {
									$p=$_profissionais[$id_profissional];
									$aux=explode(" ",$p->nome);
									$aux[0]=strtoupper($aux[0]);
									$profissionais[]=array('iniciais'=>$p->calendario_iniciais,
															'cor'=>$p->calendario_cor);
								}
							}
						}

						$dias=round((strtotime(date('Y-m-d H:i:s'))-strtotime($x->data))/(60 * 60 * 24));

						if($dias==0) $agendadoHa="Agendado <strong>HOJE</strong>";
						else if($dias==1) $agendadoHa="Agendado <strong>ONTEM</strong>";
						else $agendadoHa="Agendado há&nbsp;<strong>$dias</strong>&nbsp;dias";
						
						//	$pacienteNome=$_pacientes[$x->id_paciente]->nome;
						$agendamentos[]=array('agendaPessoal'=>1,
												'resourceId'=>$x->id_cadeira,'start'=>$dtStart,
												'end'=>$dtEnd,
												'hora'=>$hora,
												'horaFinal'=>$horaFinal,
												'foto'=>'',
												'cadeira'=>$cadeira,
												'id_paciente'=>0,
												'duracao'=>$x->agenda_duracao."m",
												'title'=>'Agendamento Pessoal',
												'profissionais'=>$profissionais,
												'color'=>'#FFF',
												'statusColor'=>(isset($_status[$x->id_status])?$_status[$x->id_status]->cor:''),
												'pontuacao'=>0,
												'agendadoHa'=>$agendadoHa,
												'id'=>$x->id);
					}
				}

				// Agendamentos desmarcados
				foreach($registrosDesmarcados as $x) {
					//echo $start->format('Y-m-d')."!=".date('Y-m-d',strtotime($x->agenda_data));
					if($start->format('Y-m-d')!=date('Y-m-d',strtotime($x->agenda_data))) continue;
				
					if($x->agendaPessoal==0 and isset($_pacientes[$x->id_paciente])) {

						$cadeira=isset($_cadeiras[$x->id_cadeira])?utf8_encode($_cadeiras[$x->id_cadeira]->titulo):'-';
						$dtStart=date('Y-m-d\TH:i',strtotime($x->agenda_data));
						$dtEnd=date('Y-m-d\TH:i',strtotime($x->agenda_data." + $x->agenda_duracao minutes"));
						$hora=date('H:i',strtotime($x->agenda_data));
						$horaFinal=date('H:i',strtotime($x->agenda_data." + $x->agenda_duracao minutes"));


						$aux = explode(",",$x->profissionais);
						$profissionais=array();
						foreach($aux as $id_profissional) {
							if(!empty($id_profissional) and is_numeric($id_profissional)) {
								if(isset($_profissionais[$id_profissional])) {
									$p=$_profissionais[$id_profissional];
									$aux=explode(" ",$p->nome);
									$aux[0]=strtoupper($aux[0]);
									$profissionais[]=array('iniciais'=>$p->calendario_iniciais,
															'cor'=>$p->calendario_cor);
								}
							}
						}

						$aux=explode(" ",trim(utf8_encode($_pacientes[$x->id_paciente]->nome)));
						$pacienteNome=tirarAcentos(utf8_encode($_pacientes[$x->id_paciente]->nome));

						//	$pacienteNome=$_pacientes[$x->id_paciente]->nome;
						$agendamentosDesmarcados[]=array('start'=>$dtStart,
															'end'=>$dtEnd,
															'hora'=>$hora,
															'horaFinal'=>$horaFinal,
															'cadeira'=>$cadeira,
															'color'=>$_status[4]->cor,
															'nome'=>($pacienteNome),
															'profissionais'=>$profissionais,
															'id'=>$x->id,
														);
					} 
				}
			}


			// dias que nao atende
			$dtFim = $data_fim;
			$dt = $data_inicio;

			// horarios disponiveis dos consultorios/cadeiras

			$_cadeirasHorarios=array();
			$sql->consult($_p."parametros_cadeiras_horarios","*","where lixo=0");
			while($x=mysqli_fetch_object($sql->mysqry)) {

				if(!isset($_cadeirasHorarios[$x->id_cadeira])) $_cadeirasHorarios[$x->id_cadeira]=array();
				if(!isset($_cadeirasHorarios[$x->id_cadeira][$x->dia])) $_cadeirasHorarios[$x->id_cadeira][$x->dia]=array();

				$_cadeirasHorarios[$x->id_cadeira][$x->dia][]=array('inicio'=>date('H:i',strtotime($x->inicio)),
																	'fim'=>date('H:i',strtotime($x->fim)));

			}

			//	echo json_encode($_cadeirasHorarios);

			//echo "<BR><BR>";

			$dif = ceil((strtotime($data_fim) - strtotime($data_inicio))/86400);

			if($dif<=1) {

				// cria backgrounds dos dias nao disponiveis

				foreach($_cadeiras as $c) {
					$dt = $data_inicio." 07:00";
					$idCadeira=$c->id;
					do {

						$diaSemana = date('w',strtotime($dt));

						$naoAtende=false;
						if(isset($_cadeirasHorarios[$c->id][$diaSemana])) {


							$iN = (date('H:i',strtotime($dt)));
							$iF = (date('H:i',strtotime($dt." + 30 minutes")));

							$naoAtende=true;
							foreach($_cadeirasHorarios[$c->id][$diaSemana] as $arr) {

							
								$xi = ($arr['inicio']);
								$xf = ($arr['fim']);

								//echo $xi." ".$xf;die();
								/*
								echo "$xi - $xf -> if(strtotime($xi) >= strtotime($iN) && strtotime($xi) < strtotime($iF) && 
									strtotime($xf) > strtotime($iN)\n\n";

								echo "$xi - $xf -> if(strtotime($xi) >= strtotime($iN) && strtotime($xi) < strtotime($iF) && 
									strtotime($xf) < strtotime($iN) && 
									strtotime($xf) > strtotime($iF))<BR>\n\n";

								echo "$xi - $xf -> if(strtotime($xi) <= strtotime($iN) && strtotime($xi) < strtotime($iF) && 
									strtotime($xf) > strtotime($iN) && 
									strtotime($xf) > strtotime($iF))<BR>\n\n";
								*/

								if(strtotime($xi) >= strtotime($iN) && 
									strtotime($xi) < strtotime($iF) && 
									strtotime($xf) > strtotime($iN) ) { 
									//echo "ATENDE\n";
									$naoAtende=false;
									break;
								} else if(strtotime($xi) >= strtotime($iN) && 
									strtotime($xi) < strtotime($iF) && 
									strtotime($xf) < strtotime($iN)  && 
									strtotime($xf) > strtotime($iF) ) { 
									//echo "ATENDEe\n";
									$naoAtende=false;
									break;
								} else if(strtotime($xi) <= strtotime($iN) && 
									strtotime($xi) < strtotime($iF) && 
									strtotime($xf) > strtotime($iN)  && 
									strtotime($xf) >= strtotime($iF) ) { 
									//echo "ATENDEe\n";
									$naoAtende=false;
									break;
								}
							}

						} else {
							$naoAtende=true;
						}
						if($naoAtende===true) {

							$agendamentos[]=array('agendaPessoal'=>2,
										'resourceId'=>$idCadeira,
										'start'=>str_replace(" ","T",$dt),
										'end'=>str_replace(" ","T",date('Y-m-d H:i',strtotime($dt." + 30 minutes"))),
										'cadeira'=>utf8_encode($c->titulo),
										'color'=>'#cccccc',
										'display'=>'background'
									);
						}

						//echo $idCadeira." ".date('Y-m-d H:i',strtotime($dt))." -> $diaSemana ".($naoAtende?"n":"atende")."\n--------->\n\n\n";

						$dt = date('Y-m-d H:i',strtotime($dt."+ 30 minutes"));

					} while(strtotime($dtFim)>strtotime($dt));
				}
			}

			//die();

			$rtn=array('success'=>true,
						'agendamentos'=>$agendamentos,
						'desmarcados'=>$agendamentosDesmarcados);
			
		} else if($_GET['ajax']=="buscaPaciente") {
			$where="WHERE 1=2";
			if(isset($_GET['search']) and !empty($_GET['search'])) {
				$aux = explode(" ",$_GET['search']);

				$wh="";
				$primeiraLetra='';
				foreach($aux as $v) {
					if(empty($v)) continue;

					if(empty($primeiraLetra)) $primeiraLetra=substr($v,0,1);
					$wh.="nome REGEXP '$v' and ";
				}
				$wh=substr($wh,0,strlen($wh)-5);
				$where="where (($wh) or nome like '%".$_GET['search']."%' or telefone1 like '%".$_GET['search']."%' or cpf like '%".$_GET['search']."%') and lixo=0";
				//$where="where nome like '%".$_GET['search']."%' or telefone1 like '%".$_GET['search']."%' or cpf like '%".$_GET['search']."%' and lixo=0";
			}
			if(!empty($primeiraLetra)) $where.=" ORDER BY CASE WHEN nome >= '$primeiraLetra' THEN 1 ELSE 0 END DESC, nome ASC";
			else $where.=" order by nome asc";

			$sql->consult($_p."pacientes","nome,id,telefone1,cpf",$where);
			//echo $where;
			while($x=mysqli_fetch_object($sql->mysqry)) {
				$rtn['items'][]=array('id'=>$x->id,
										'text'=>utf8_encode($x->nome),
										'nome'=>utf8_encode($x->nome),
										'telefone'=>utf8_encode($x->telefone1),
										'cpf'=>utf8_encode($x->cpf));
			}

		}
		header("Content-type: application/json");
		echo json_encode($rtn);
		die();
	}

	include "includes/header.php";
	include "includes/nav.php";

	if($usr->tipo!="admin" and !in_array("produtos",$_usuariosPermissoes)) {
		$jsc->jAlert("Você não tem permissão para acessar esta área!","erro","document.location.href='dashboard.php'");
		die();
	}
	$values=$adm->get($_GET);


	$_agendaStatus=array('confirmado'=>'CONFIRMADO','agendado'=>'AGENDADO');
	//  right:'dayGridMonth,resourceTimeGridOneDay,resourceTimeGridFiveDay,resourceTimeGridSevenDay'
	$_views=array("dayGridMonth"=>"MÊS",
					"resourceTimeGridOneDay"=>"1 dia",
					"resourceTimeGridFiveDay"=>"5 dias",
					"resourceTimeGridSevenDay"=>"7 dias");

	$_page=basename($_SERVER['PHP_SELF']);

	$initDate='';

	if(isset($_GET['data']) and !empty($_GET['data'])) $_GET['initDate']=$_GET['data'];

	if(isset($_GET['initDate']) and !empty($_GET['initDate']) and strpos($_GET['initDate'], '/')!==false) {
		list($dia,$mes,$ano)=explode("/",$_GET['initDate']);
		if(checkdate($mes, $dia, $ano)) {
			$initDate=$ano."-".$mes."-".$dia;
		}
	}
?> 
	<script>
		var calendar = '';
		var calendarView = 'resourceTimeGridOneDay';

		var filtroStatus=``;
		var filtroProfissional = <?php echo (isset($_GET['id_profissional']) and is_numeric($_GET['id_profissional']))?$_GET['id_profissional']:0;?>;
		var filtroCadeira = <?php echo (isset($_GET['id_cadeira']) and is_numeric($_GET['id_cadeira']))?$_GET['id_cadeira']:0;?>;
		
		const verificaAgendamento = () => {
			let profissionais = $('.js-form-agendamento select.js-profissionais').val();
			let id_cadeira = $('.js-form-agendamento select[name=id_cadeira]').val();
			let id_paciente = $('.js-form-agendamento select[name=id_paciente]').val();
			let agenda_data = $('.js-form-agendamento input[name=agenda_data]').val();
			let agenda_hora = $('.js-form-agendamento input[name=agenda_hora]').val();

			let data = `ajax=agendamentoVerificarDisponibilidade&profissionais=${profissionais}&id_cadeira=${id_cadeira}&agenda_data=${agenda_data}&agenda_hora=${agenda_hora}&id_paciente=${id_paciente}`;
			

			$.ajax({
				type:'POST',
				url:'box/boxAgendamento.php',
				data:data,
				success:function(rtn) {
					if(rtn.success) {
						$('#box-validacoes dd').remove();
						rtn.validacao.forEach(x=> {
							let item = ``;
							if(x.atende==1) {
								item = `<dd style="color:green"><i class="iconify" data-icon="bx-bx-check"></i> ${x.profissional} atende neste dia/horário</dd>`;
							} else {
								item = `<dd style="color:red"><span class="iconify" data-icon="ion:alert-circle-sharp"></span> ${x.profissional} não atende neste dia/horário</dd>`;
							}
							$('#box-validacoes').append(item);
						})
					} else {
						$('#box-validacoes dd').remove();
					}
				},
				error:function() {
					$('#box-validacoes dd').remove();
				}
			})
		}

		const calendarioVisualizacaoData = () => { 
			
			let date = calendar.getDate();

			let mesString='';

			if(date.getMonth()==0) mesString='jan'; 
			else if(date.getMonth()==1) mesString='fev'; 
			else if(date.getMonth()==2) mesString='mar'; 
			else if(date.getMonth()==3) mesString='abr'; 
			else if(date.getMonth()==4) mesString='mai'; 
			else if(date.getMonth()==5) mesString='jun'; 
			else if(date.getMonth()==6) mesString='jul'; 
			else if(date.getMonth()==7) mesString='ago'; 
			else if(date.getMonth()==8) mesString='set'; 
			else if(date.getMonth()==9) mesString='out'; 
			else if(date.getMonth()==10) mesString='nov'; 
			else if(date.getMonth()==11) mesString='dez'; 

			if(date.getUTCDay()==0) diaString='domingo';
			else if(date.getUTCDay()==1) diaString='segunda-feira';
			else if(date.getUTCDay()==2) diaString='terça-feira';
			else if(date.getUTCDay()==3) diaString='quarta-feira';
			else if(date.getUTCDay()==4) diaString='quinta-feira';
			else if(date.getUTCDay()==5) diaString='sexta-feira';
			else if(date.getUTCDay()==6) diaString='sábado';

			let dateString = date.getDate()+" "+mesString+" "+date.getFullYear();

			console.log(date.getUTCDay()+' => '+dateString+' => '+calendar.view.title);
			console.log(date.getTimezoneOffset());

			$('.js-cal-titulo-diames').html(date.getDate()>=9?date.getDate():`0${date.getDate()}`);
			$('.js-cal-titulo-mes').html(mesString);
			$('.js-cal-titulo-dia').html(diaString);
		}

		const dia = (d) => {
			if(d==0) return "dom.";
			else if(d==1) return "seg.";
			else if(d==2) return "ter.";
			else if(d==3) return "qua.";
			else if(d==4) return "qui.";
			else if(d==5) return "sex.";
			else if(d==6) return "sáb.";
		}

		const unMes = (m) => {
			m = m.toUpperCase();
			if(m=="JANEIRO") return "0";
			else if(m=="FEVEREIRO") return "1";
			else if(m=="MARÇO") return "2";
			else if(m=="ABRIL") return "3";
			else if(m=="MAIO") return "4";
			else if(m=="JUNHO") return "5";
			else if(m=="JULHO") return "6";
			else if(m=="AGOSTO") return "7";
			else if(m=="SETEMBRO") return "8";
			else if(m=="OUTUBRO") return "9";
			else if(m=="NOVEMBRO") return "10";
			else if(m=="DEZEMBRO") return "11";
		}

		const novoAgendamento = (id_cadeira,dataHora) => {

		
			let date = calendar.getDate();
			let dia = d2(date.getDate());
			let mes = d2(date.getMonth()+1);
			let ano = date.getFullYear();
			data = `${dia}/${mes}/${ano}`;

			let agendaHora='';
			if(dataHora.length>0) {
				let dt = new Date(dataHora);
				let dtHora = d2(dt.getHours());
				let dtMin = d2(dt.getMinutes());
				agendaHora = `${dtHora}:${dtMin}`;
			}
			
			
			$('#js-aside-add select[name=id_status]').val(1);
			$('#js-aside-add select[name=duracao]').val(``);
			$('#js-aside-add input[name=telefone1]').val(``);
			$('#js-aside-add select[name=agenda_duracao]').val(30);
			$('#js-aside-add textarea[name=obs]').val(``);
			$('#js-aside-add select[name=profissionais] option:checked').prop('checked',false).trigger('chosen:updated');
			$('#js-aside-add input[name=agenda_data]').val(data);
			$('#js-aside-add input[name=agenda_hora]').val(agendaHora);
			$('#js-aside-add select[name=id_cadeira]').val(id_cadeira);
			$('#js-aside-add select[name=id_paciente]').val(null).trigger('change');
			$('#js-aside-add input[name=alteracao]').val(0);


			$("#js-aside-add").fadeIn(100,function() {
				$("#js-aside-add .aside__inner1").addClass("active");
				$("#js-aside-add .js-tab a:eq(0)").click();
			});

			$('#js-aside-add .js-profissionais').chosen('destroy');
			$('#js-aside-add .js-profissionais').chosen();
			$('#js-aside-add .js-profissionais').trigger('chosen:updated');
			agendamentosProfissionais(`add`);
		}
		
		const formatTemplate = (state) => {
			if (!state.id) return state.text;
			var baseUrl = "/user/pages/images/flags";
			infoComplementar=``;
			infoComplementar+= !! state.cpf ? ` - CPF: ${state.cpf}` : '';
			infoComplementar+= !! state.telefone ? ` - Tel.: ${state.telefone}` : '';
			var $state = $('<span style="display:flex; align-items:center; gap:.5rem;"><img src="img/ilustra-perfil.png" style="width:40px;height:40px;border-radius:100%;" /> ' + state.text + infoComplementar + '</span>');
			return $state;
		}

		const formatTemplateSelection = (state) => {
			if (!state.id) return state.text;
			var baseUrl = "/user/pages/images/flags";
			infoComplementar=``;
			infoComplementar+= !! state.cpf ? ` - CPF: ${state.cpf}` : '';
			infoComplementar+= !! state.telefone ? ` - Tel.: ${state.telefone}` : '';
			var $state = $('<span><img src="img/ilustra-perfil.png" style="width:30px;height:30px;border-radius:50px;" /> ' + state.text + infoComplementar + '</span>');
			return $state;
		}



		$(function(){
			$('#js-aside-add input[name=agenda_data]').datetimepicker({
												timepicker:false,
												format:'d/m/Y',
												scrollMonth:false,
												scrollTime:false,
												scrollInput:false,
											});

			$('#js-aside-add input[name=agenda_hora]').datetimepicker({
				  datepicker:false,
			      format:'H:i',
			      pickDate:false
			});

			$('input[name=telefone1],.js-asPaciente-telefone1').mobilePhoneNumber({allowPhoneWithoutPrefix: '+55'}).bind('country.mobilePhoneNumber', function(echo, country) {
												let countryOut = country || '  ';
												$(this).parent().parent().find('.js-country').html(countryOut);
											}).trigger('keyup');

			$('#js-aside-add select[name=id_paciente]').select2({
				ajax: {
					url: 'pg_agenda.php?ajax=buscaPaciente',
					data: function (params) {
							var query = {
							search: params.term,
							type: 'public'
						}
						// ?search=[term]&type=public
						return query;
					},
					processResults: function (data) {
						// Transforms the top-level key of the response object from 'items' to 'results'
						return {
							results: data.items
						};
					}

				},
				templateResult:formatTemplate,
				//	templateSelection:formatTemplateSelection,
				//dropdownParent: $(".modal")
			});

			$('#js-aside-add select[name=id_paciente]').on('select2:select',function(e){
				 var telefone = e.params.data.telefone ? e.params.data.telefone : '';
				 $('#js-aside-add input[name=telefone1]').val(telefone).trigger('change');
    			
			});

			$('input[name=agenda_data]').change(function(){
				let val = $(this).val().split('/');
				let valAno = val[2];
				let valMes = val[1];
				let valDia = val[0];
				let valDate = `${valAno}, ${valMes}, ${valDia}`;

				let dataAgenda = new Date(`${valAno}, ${valMes}, ${valDia}`);
				let dataHoje = new Date();

				//console.log(dataAgenda+' '+dataHoje);
				if(dataAgenda>dataHoje) {
					$(this).parent().parent().parent().parent().find('select[name=id_status]').find('option[value=7],option[value=6],option[value=5],option[value=3]').prop('disabled',true);
				} else {
					$(this).parent().parent().parent().parent().find('select[name=id_status]').find('option[value=7],option[value=6],option[value=5],option[value=3]').prop('disabled',false);
				}
			});

			$('.m-produtos').next().show();	

			$('.js-calendario').datetimepicker({
				timepicker:false,
				format:'d F Y',
				scrollMonth:false,
				scrollTime:false,
				scrollInput:false,
				onChangeDateTime:function(dp,dt) {
					let val = dt.val();
					let aux = val.split(' ');
					aux[1]=unMes(aux[1]);
					let data = `${aux[2]}-${(aux[1])}-${aux[0]}`;
					//console.log('->'+data);
					let dtJS = new Date(aux[2],aux[1],aux[0]);
					let newDt = new Date(aux[2],aux[1],aux[0]);
					if(calendarView=="resourceTimeGridOneDay" || calendarView=="dayGridMonth") {

					} else {
						newDt.setDate(newDt.getDate() - (dtJS.getDay()-1));
					}
					//console.log(newDt);
					calendar.gotoDate(newDt);
					calendarioVisualizacaoData();
				}
			});

			$('.js-view-a').click(function() {
				let dtJS = new Date(calendar.getDate());
				let newDt = new Date(calendar.getDate());
				newDt.setDate(newDt.getDate() - (dtJS.getDay()-1));

				let view = $(this).attr('data-view');
				calendar.changeView(view);
				calendarioVisualizacaoData();


				$('.js-view-a').removeClass('active');
				$(this).addClass('active');
				calendarView=view;

				if(view=='resourceTimeGridOneDay') {
					
					calendar.today();
					calendarioVisualizacaoData();

					$('#js-agendamentosDesmarcados').parent().parent().show();
				} else {
					$('#js-agendamentosDesmarcados').parent().parent().hide();
					calendar.gotoDate(newDt);
				}

				$('.js-calendario').val(calendar.view.title)
			});

			$('select.js-view').change(function(){

				let dtJS = new Date(calendar.getDate());
				let newDt = new Date(calendar.getDate());
				newDt.setDate(newDt.getDate() - (dtJS.getDay()-1));

				let view = $(this).val();
				calendar.changeView(view);
				calendarioVisualizacaoData();


				
				if(view=='resourceTimeGridOneDay') {
					
					calendar.today();
					calendarioVisualizacaoData();
				} else {
					calendar.gotoDate(newDt);
				}

			});

			$('a.js-right').click(function(){
				if(calendar.view.type=="resourceTimeGridFiveDay") {
					let dtJS = new Date(calendar.getDate());
					dtJS.setDate(dtJS.getDate() + 7);
					let view = $(this).val();
					//calendar.changeView(view);
					//calendarioVisualizacaoData();
					calendar.gotoDate(dtJS);
				} else {
					calendar.next();
				}

				calendarioVisualizacaoData();
			});

			$('a.js-left').click(function(){ 
				if(calendar.view.type=="resourceTimeGridFiveDay") {
					let dtJS = new Date(calendar.getDate());
					dtJS.setDate(dtJS.getDate() - 7);
					let view = $(this).val();
					//calendar.changeView(view);
					//calendarioVisualizacaoData();
					calendar.gotoDate(dtJS);
				} else {
					calendar.prev();
				}

				calendarioVisualizacaoData();
			});

			$('a.js-today').click(function(){
				calendar.today();
				calendarioVisualizacaoData();
			});

			$('.js-status').change(function(){
				filtroStatus=$(this).val();
				calendar.refetchEvents();
			});

			$('.filter .js-cadeira').change(function(){
				filtroCadeira=$(this).val();
				id_cadeira=$(this).val();
				calendar.refetchEvents();
			});

			$('.js-filter-agenda .js-profissionais').change(function(){
				id_profissional=$(this).val();
				filtroProfissional=$(this).val();
				calendar.refetchEvents();
			});

			$('.js-btn-fechar').click(function(){
				$('.cal-popup').hide();
			});

			$('.js-novoAgendamento').click(function(){
				novoAgendamento(0,'');

			})
		});
	</script>

	<style>
		body {background:#fff;}
		/*the container must be positioned relative:*/
		.custom-select {
		  position: relative;
		  font-family: Arial;
		}

		.custom-select select {
		  display: none; /*hide original SELECT element:*/
		}

		.select-selected {
		  background-color: DodgerBlue;
		}

		/*style the arrow inside the select element:*/
		.select-selected:after {
		  position: absolute;
		  content: "";
		  top: 14px;
		  right: 10px;
		  width: 0;
		  height: 0;
		  border: 6px solid transparent;
		  border-color: #fff transparent transparent transparent;
		}

		/*point the arrow upwards when the select box is open (active):*/
		.select-selected.select-arrow-active:after {
		  border-color: transparent transparent #fff transparent;
		  top: 7px;
		}

		/*style the items (options), including the selected item:*/
		.select-items div,.select-selected {
		  color: #ffffff;
		  padding: 8px 16px;
		  border: 1px solid transparent;
		  border-color: transparent transparent rgba(0, 0, 0, 0.1) transparent;
		  cursor: pointer;
		  user-select: none;
		}

		/*style items (options):*/
		.select-items {
		  position: absolute;
		  background-color: DodgerBlue;
		  top: 100%;
		  left: 0;
		  right: 0;
		  z-index: 99;
		}

		/*hide the items when the select box is closed:*/
		.select-hide {
		  display: none;
		}

		.select-items div:hover, .same-as-selected {
		  background-color: rgba(0, 0, 0, 0.1);
		}
		.fc .fc-timegrid-col.fc-day-today {background:#fff !important;}
		.fc-theme-standard th {border-right:transparent !important; border-left:transparent !important;}
		.fc-scroller {overflow:visible !important;}
		.fc-row.fc-rigid, .fc .fc-scroller-harness {overflow:visible !important;} 
		.fc-scroller, fc.day.grid.containet {overflow:none !important;}
		.fc-timegrid-slot { height: 60px !important; // 1.5em by default }
		.fc-scrollgrid-sync-inner { height:90px; }
		.fc-scrollgrid  { border:none !important; }
		.fc-scrollgrid-liquid{ border:none !important; }
	</style>

	<header class="header">
		<div class="header__content content">

			<div class="header__inner1">
				<section class="header-title">
					<h1>Agenda</h1>
				</section>
				<section class="tab">
					<a href="pg_agenda.php" class="active">Calendário</a>
					<a href="javascript:;" class="js-aba-calendario">Kanban</a>					
				</section>
			</div>

			<div class="header__inner2">
				<section class="header-date">
					<div class="header-date-buttons"></div>
					<div class="header-date-now">
						<h1 class="js-cal-titulo-diames"></h1>
						<h2 class="js-cal-titulo-mes"></h2>
						<h3 class="js-cal-titulo-dia"></h3>
					</div>
				</section>
			</div>

		</div>
	</header>

	<main class="main">
		<div class="main__content content">
			
			<?php
			require_once("includes/filter/filterAgenda.php");

			$filtro='';

			if(isset($values['id_status']) and isset($_status[$values['id_status']])) $filtro.="&id_status=".$values['id_status'];
			//if(isset($values['id_profissional']) and isset($_profissionais[$values['id_profissional']])) $filtro.="&id_profissional=".$values['id_profissional'];
			//if(isset($values['id_cadeira']) and isset($_cadeiras[$values['id_cadeira']])) $filtro.="&id_cadeira=".$values['id_cadeira'];
			if(isset($values['busca']) and !empty($values['busca'])) $filtro.="&busca=".$values['busca'];
			?>

			<section class="grid">

				<link href='https://cdn.jsdelivr.net/npm/fullcalendar-scheduler@5.3.0/main.min.css' rel='stylesheet' />
				<script src='https://cdn.jsdelivr.net/npm/fullcalendar-scheduler@5.3.0/main.min.js'></script>

				<script>
					var calendar = '';
					var calpopID = 0;
					var desmarcados = [];
					var slickDesmarcados = false;
					
					const popView = (id_agenda) => {
						
						let data = `ajax=editar&id=${id_agenda}`;
						$.ajax({
								type:"POST",
								data:data,
								success:function(rtn){ 
									if(rtn.success) {

										//$('html, body').animate({scrollTop: 0},'fast');
										if(rtn.data.agendaPessoal>0) {
											$('#js-aside-edit-agendaPessoal input[name=id]').val(rtn.data.id);
											$('#js-aside-edit-agendaPessoal input[name=agenda_data]').val(rtn.data.agenda_data);
											$('#js-aside-edit-agendaPessoal input[name=agenda_hora]').val(rtn.data.agenda_hora);
											
											$('#js-aside-edit-agendaPessoal select[name=agenda_duracao] option.js-duracaoAdicional').remove();
											if($('#js-aside-edit-agendaPessoal select[name=agenda_duracao]').find(`option[value=${rtn.data.agenda_duracao}]`).length==0) {
												$('#js-aside-edit-agendaPessoal select[name=agenda_duracao]').append(`<option class="js-duracaoAdicional" value="${rtn.data.agenda_duracao}">${rtn.data.agenda_duracao}</option>`);
											}
											$('#js-aside-edit-agendaPessoal select[name=agenda_duracao]').val(rtn.data.agenda_duracao);

											$('#js-aside-edit-agendaPessoal textarea[name=obs]').val(rtn.data.obs);
											$('#js-aside-edit-agendaPessoal select[name=id_cadeira]').val(rtn.data.id_cadeira);
											$('#js-aside-edit select[name=id_profissional]').find(':selected').prop('selected',false);
											if(rtn.data.profissionais) {
												rtn.data.profissionais.forEach(idProfissional=> {
													//console.log(idProfissional);
													$('#js-aside-edit-agendaPessoal select[name=id_profissional]').find(`[value=${idProfissional}]`).prop('selected',true);
												})
											}


											$("#js-aside-edit-agendaPessoal").fadeIn(100,function() {
												$('#js-aside-edit-agendaPessoal select[name=id_profissional]').chosen();
												$("#js-aside-edit-agendaPessoal .aside__inner1").addClass("active");
												$("#js-aside-edit-agendaPessoal .js-tab a:eq(0)").click();
											});
											$('#js-aside-edit-agendaPessoal select[name=id_profissional]').chosen();
											$('#js-aside-edit-agendaPessoal select[name=id_profissional]').trigger('chosen:updated'); 
										} else {
											$('#js-aside-edit input[name=id]').val(rtn.data.id);

											$('#js-aside-edit .js-nome').html(`${rtn.data.nome} <i class="iconify" data-icon="fluent:share-screen-person-overlay-20-regular" style="color:var(--cinza4)"></i>`).attr('href',`pg_pacientes_resumo.php?id_paciente=${rtn.data.id_paciente}`);

											if(rtn.data.ft && rtn.data.ft.length>0) {
												$('#js-aside-edit .js-foto').attr('src',rtn.data.ft);
											} else {
												$('#js-aside-edit .js-foto').attr('src','img/ilustra-usuario.jpg');
											}

											if(rtn.data.idade && rtn.data.idade>0) {
												$('#js-aside-edit .js-idade').html(rtn.data.idade+(rtn.data.idade>=2?' anos':' ano'));
											} else {
												$('#js-aside-edit .js-idade').html(``);
											}

											if(rtn.data.periodicidade && rtn.data.periodicidade.length>0) {
												
												$('#js-aside-edit .js-periodicidade').html(`Periodicidade: ${rtn.data.periodicidade}`);
											} else {
												$('#js-aside-edit .js-periodicidade').html(`Periodicidade: -`);
											}

											if(rtn.data.musica && rtn.data.musica.length>0) {
												$('#js-aside-edit .js-musica').html(`<i class="iconify" data-icon="bxs:music"></i> ${rtn.data.musica}`);
											} else {
												$('#js-aside-edit .js-musica').html(``);
											}
											$('#js-aside-edit input[name=agenda_data]').val(rtn.data.agenda_data);
											$('#js-aside-edit input[name=agenda_hora]').val(rtn.data.agenda_hora);

											$('#js-aside-edit select[name=agenda_duracao] option.js-duracaoAdicional').remove();
											if($('#js-aside-edit select[name=agenda_duracao]').find(`option[value=${rtn.data.agenda_duracao}]`).length==0) {
												$('#js-aside-edit select[name=agenda_duracao]').append(`<option class="js-duracaoAdicional" value="${rtn.data.agenda_duracao}">${rtn.data.agenda_duracao}</option>`);
											}
											$('#js-aside-edit select[name=agenda_duracao]').val(rtn.data.agenda_duracao);

											$('#js-aside-edit select[name=id_cadeira]').val(rtn.data.id_cadeira);
											$('#js-aside-edit input[name=telefone1]').val(rtn.data.telefone1);
											$('#js-aside-edit textarea[name=obs]').val(rtn.data.obs);
											$('#js-aside-edit select[name=id_status]').val(rtn.data.id_status)
											$('#js-aside-edit .js-profissionais').trigger('chosen:updated'); 
											if(rtn.data.agendou_dias>1) $('#js-aside-edit .js-agendou').html(`${rtn.data.agendou_profissional} agendou há ${rtn.data.agendou_dias} dia(s)`);
											else $('#js-aside-edit .js-agendou').html(`${rtn.data.agendou_profissional} agendou hoje`);

											$('.js-fieldset-horarios,.js-btn-remover').show();
											

											if(rtn.data.statusBI && rtn.data.statusBI.length==0) {
												$('#js-aside-edit .js-statusBI').html(``).hide();
											} else {
												$('#js-aside-edit .js-statusBI').html(`${rtn.data.statusBI}`).show();
											}

											$('#js-aside-edit .js-profissionais').find(':selected').prop('selected',false);

											if(rtn.data.profissionais) {
												rtn.data.profissionais.forEach(idProfissional=> {
													$('#js-aside-edit .js-profissionais').find(`[value=${idProfissional}]`).prop('selected',true);
												})
											}

											$('.js-ag-futuro table tr').remove();
											if(rtn.data.agendamentosFuturos && rtn.data.agendamentosFuturos.length>0) {
												rtn.data.agendamentosFuturos.forEach(x=>{


													let profissionalIniciais=``;

													x.profissionais.forEach(p=>{
														profissionalIniciais+=`<div class="badge-prof" title="${p.iniciais}" style="background:${p.cor}">${p.iniciais}</div>`;
													})
													$('.js-ag-futuro table').append(`<tr>
																							<td>
																								<h1>${x.data}</h1>									
																							</td>
																							<td>${x.cadeira}</td>
																							<td>
																								${profissionalIniciais}
																							</td>
																						</tr>`);
												});

											} else {
												$('.js-ag-futuro table').append(`<tr><td><center>Nenhum agendamento futuro</center></td></tr>`);
											}

											$('.js-ag-historico .history div').remove();

											if(rtn.data.historico) {
												rtn.data.historico.forEach(x=>{
													if(x.ev=="horario") {

														$('.js-ag-historico .history').append(`<div class="history-item">
																							<h1>${x.usr} em ${x.dt}</h1>
																							<h2>horário alterado de <strong style="background:var(--cor1);">${x.antDt}</strong> para <strong style="background:var(--cor1);">${x.nvDt}</strong></h2>
																						</div>`);

													} else {

														$('.js-ag-historico .history').append(`<div class="history-item">
																							<h1>${x.usr} em ${x.dt}</h1>
																							<p>${x.desc}</p>
																							<h2>${x.novo==1?'agendamento criado com status':'status alterado para'} <em style="background:${x.cor};">${x.sts}</em></h2>
																						</div>`);

													}

												})
											} else {

											}


											$("#js-aside-edit").fadeIn(100,function() {
												$('#js-aside-edit .js-profissionais').chosen('destroy');
												setTimeout(function(){$('#js-aside-edit .js-profissionais').chosen();},100);
												$("#js-aside-edit .aside__inner1").addClass("active");
												$("#js-aside-edit .js-tab a:eq(0)").click();
											});

											
											$('#js-aside-edit input[name=agenda_data]').trigger('change');

											$('#js-aside-edit .js-profissionais').trigger('chosen:updated');


											$('#js-aside-edit .js-salvar').show();

											$('#js-aside-edit input, #js-aside-edit textarea').prop('readonly',false).css('background','');

											$('#js-aside-edit select').prop('disabled',false).css('background','').trigger('chosen:updated');
											$('#js-aside-edit input[name=id_status_antigo]').val(rtn.data.id_status);
											// se confirmado
											if(rtn.data.id_status=="2") {
												$('#js-aside-edit select[name=id_status]').find('option[value=1]').prop('disabled',true);

												$('#js-aside-edit input[name=agenda_data]').prop('readonly',true).datetimepicker('destroy').css('background','var(--cinza3)');

												$('#js-aside-edit input[name=agenda_hora]').prop('readonly',true).datetimepicker('destroy').css('background','var(--cinza3)');

											}  
											// se desmarcado
											else if(rtn.data.id_status=="4") {

												$('#js-aside-edit .js-salvar').hide();

												$('#js-aside-edit select[name=id_status]').prop('disabled',true);

												$('#js-aside-edit input[name=agenda_data],#js-aside-edit input[name=agenda_hora]').datetimepicker('destroy')

												$('#js-aside-edit input, #js-aside-edit textarea').prop('readonly',true).css('background','var(--cinza3)');

												$('#js-aside-edit select').prop('disabled',true).css('background','var(--cinza3)').trigger('chosen:updated');


											} else {


												$('#js-aside-edit select[name=id_status]').find('option[value=1]').prop('disabled',false)

												$('#js-aside-edit input[name=agenda_data]').datetimepicker({
													timepicker:false,
													format:'d/m/Y',
													scrollMonth:false,
													scrollTime:false,
													scrollInput:false,
												}).css('background','');

												$('#js-aside-edit input[name=agenda_hora]').datetimepicker({
													  datepicker:false,
												      format:'H:i',
												      pickDate:false
												}).css('background','');
											}
										}

										$('#js-aside-edit input[name=alteracao]').val(0);
										$('#js-aside-edit-agendaPessoal input[name=alteracao]').val(0);
										agendamentosProfissionais(`edit`);



									} else if(rtn.error) {
										swal({title: "Erro!", text: rtn.error, type:"error", confirmButtonColor: "#424242"});
									} else {
										swal({title: "Erro!", text: 'Algum erro ocorreu durante a abertura deste agendamento.', type:"error", confirmButtonColor: "#424242"});
									}
								},
								error:function(){
									swal({title: "Erro!", text: 'Algum erro ocorreu durante a abertura deste agendamento', type:"error", confirmButtonColor: "#424242"});
								}
						});
					}

					const agendamentosDesmarcados = () => {
						
						//console.log($('#js-agendamentosDesmarcados').parent().parent().is(':visible'));
						if($('#js-agendamentosDesmarcados').parent().parent().is(':visible')==true) {

							if(desmarcados.length==0) {
								$('#js-agendamentosDesmarcados').html(`<center>Nenhum agendamento desmarcado</center>`);
							} else {
								if(slickDesmarcados===true) {
									$('.cal-lost-slick').slick('destroy');
								}

								$('#js-agendamentosDesmarcados').html(``);

								//console.log(desmarcados)

								let processados=0;
								desmarcados.forEach(x=> {

									profissionais = ``;
									if(x.profissionais.length>0) {
										cont=0;
										x.profissionais.forEach(p=> {
											if(cont>1 && (x.profissionais.length-2)>0) {
												profissionais+=`<div class="cal-item-foto"><span>+${(x.profissionais.length-2)}</span></div>`;
											} else {
												profissionais+=`<div class="cal-item-foto"><span  style="background:${p.cor}">${p.iniciais}</span></div>`;
											}
											cont++;
										})
									}

									let item = `<a href="javascript:;" class="cal-lost-item" style="border-color:${x.color};" onclick="popView(${x.id});">
													<div>
														<p>${x.hora}-${x.horaFinal} - ${x.cadeira}</p>
														<h1 style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:190px;">${x.nome}</h1>
													</div>
													${profissionais}
												</a>`;

									$(`#js-agendamentosDesmarcados`).append(item);
									processados++;
									if(processados==desmarcados.length) {
									
										$('.cal-lost-slick').slick({
											dots:false,
											arrows:true,
											slidesToShow:4,											
											infinite:false
										});
										slickDesmarcados = true;
										
									}
								});

								
							}
						}
					}


					const agendamentosProfissionais = (tipo) => {

						if(tipo=="add" || tipo=="edit") {

							let aside = $(`#js-aside-${tipo}`);

							let profissionaisSelecionados = aside.find('.js-profissionais option:selected').length>0 ? aside.find('.js-profissionais').val() : [];
							let aData = aside.find('input[name=agenda_data]').val();
							let aHora = aside.find('input[name=agenda_hora]').val();
							let id_cadeira = $(`#js-aside-${tipo} select[name=id_cadeira] option:selected`).val();

							let data = `ajax=agendamentosProfissionais&data=${aData}&hora=${aHora}&id_cadeira=${id_cadeira}`;
							
							$.ajax({
								type:"POST",
								data:data,
								success:function(rtn) {
									if(rtn.success) {
										if(rtn.listaProfissionais || rtn.listaProfissionaisDestaque) {
											aside.find('.js-profissionais option').remove();
											//aside.find('.js-profissionais').append(`<option value=""></option>`);

											if(rtn.listaProfissionaisDestaque && rtn.listaProfissionaisDestaque.length>0) {

												itens = 0;
												options = ``;
												rtn.listaProfissionaisDestaque.forEach(x=>{
													let nome = x.nome;

													sel = $.inArray(x.id,profissionaisSelecionados)>=0?' selected':'';
													
													options+=`<option value="${x.id}"${sel}>${nome}</option>`;
													
													itens++;

													if(itens == rtn.listaProfissionaisDestaque.length) {
														aside.find('.js-profissionais').append(`<optgroup label="Atende nesse horário">${options}</optgroup>`);
													}
												})

											}

											if(rtn.listaProfissionais && rtn.listaProfissionais.length>0) {
												
												itens = 0;
												options = ``;
												rtn.listaProfissionais.forEach(x=>{
													let nome = x.nome;

													sel = $.inArray(x.id,profissionaisSelecionados)>=0?' selected':'';
													
													options+=`<option value="${x.id}"${sel}>${nome}</option>`;
													
													itens++;
													if(itens == rtn.listaProfissionais.length) {
														aside.find('.js-profissionais').append(`<optgroup label="Não atende nesse horário">${options}</optgroup>`);
													}
												});
											

											}

											aside.find('.js-profissionais').trigger('chosen:updated');
										}
									}
								}
							})
						}

					}

					$(function(){

						$('.js-aba-calendario').click(function(){
							data =  new Date(calendar.getDate());;
							let dtObj = `${d2(data.getDate())}/${d2(data.getMonth()+1)}/${data.getFullYear()}`;
							
							document.location.href='pg_agenda_kanban.php?data='+dtObj;
						})

						var calendarEl = document.getElementById('calendar'); 
						calendar = new FullCalendar.Calendar(calendarEl, {
						  	schedulerLicenseKey: 'GPL-My-Project-Is-Open-Source',

							locale: 'pt-br',
							contentHeight: 'auto',
						    headerToolbar: {
						      left: '',
						      center:'',
						      right:''
						    },
						 	slotDuration:'00:30:00',
							allDaySlot:false,
							slotMinTime:'07:00:00',
							slotMaxTime:'22:00:00',
							firstDay:1,
							editable:true,
							initialView:'resourceTimeGridOneDay',
	    					eventResizableFromStart: true,
						    views: {
						      dayGridMonth:{
						      	dayMaxEventRows:1,
						      	buttonText:'MÊS',					      	
						      },
						      resourceTimeGridOneDay: {
						      	titleFormat: { weekday: 'long', month:'short', day: '2-digit', year:'numeric'},
						        type: 'resourceTimeGrid',
						        duration: { days: 1 },
						        buttonText: '1 DIA',

						      },
						      resourceTimeGridFiveDay: {
						      	titleFormat: { weekday: 'short', month:'short', day: '2-digit', year:'numeric'},
						        type: 'timeGridWeek',
						        duration: { days: 5 },
						        buttonText: '5 DIAS',

						      },
						      resourceTimeGridSevenDay: {
						      	titleFormat: { weekday: 'short', month:'short', day: '2-digit', year:'numeric'},
						        type: 'timeGridWeek',
						        duration: { days: 7 },
						        buttonText: '7 DIAS'
						      }
						    },
						    resources: <?php echo json_encode($_cadeirasJSON);?>,
						    resourceOrder: 'ordem,titulo',
							dateClick: function(info) {
								if(info.resource) {
									let id_cadeira = info.resource ? info.resource._resource.id : 0;
									let data = info.dateStr;
									novoAgendamento(id_cadeira,data);
								}
								
							},
							eventResize:function(e) {
								let id = e.event.id;
								let start = e.event.startStr;
								let end = e.event.endStr;
								let data=`ajax=persistirNovoHorario&id_agenda=${id}&start=${start}&end=${end}`;
								$.ajax({
									type:"POST",
									data:data,
									success:function(rtn) {
										if(rtn.success) {
											calendar.refetchEvents(); 
										} else if(rtn.error) {
											swal({title: "Erro!", text: rtn.error, type:"error", confirmButtonColor: "#424242"});
										} else {
											swal({title: "Erro!", text: 'Algum erro ocorreu durante a alteração de data deste agendamento!', type:"error", confirmButtonColor: "#424242"});
										}
									}
								});
							},
							eventDrop: function(ev) {
								let id = ev.event.id;
								let novaData=ev.event.startStr;
								let id_cadeira = (ev.newResource)?ev.newResource.id:'';
								let data=`ajax=persistirNovoAgendamento&id_agenda=${id}&novaData=${novaData}&id_cadeira=${id_cadeira}`;
								$.ajax({
									type:"POST",
									data:data,
									success:function(rtn) {
										if(rtn.success) {
											calendar.refetchEvents();
										} else if(rtn.error) {
											swal({title: "Erro!", text: rtn.error, type:"error", confirmButtonColor: "#424242"});
										} else {
											swal({title: "Erro!", text: 'Algum erro ocorreu durante a alteração de data deste agendamento!', type:"error", confirmButtonColor: "#424242"});
										}
									}
								});
						    },
							resourcesSet:function(arg) {
								setTimeout(function(){$('.fc-scrollgrid-sync-inner ').css('height','30px');},10);
							},
							datesSet:function(dateInfo) {
								
							},
						    events: function(info, successCallback, failure) {
								$.getJSON(`<?php echo $_page;?>?ajax=agenda&start=${info.start.valueOf()}&end=${info.end.valueOf()}&<?php echo $filtro;?>&id_status=${filtroStatus}&id_cadeira=${filtroCadeira}&id_profissional=${filtroProfissional}`,
											function (data) {
												if(data.success) {
												 	successCallback(data.agendamentos);
												 	desmarcados = data.desmarcados;
												 	agendamentosDesmarcados();
												}
											});
							},

							eventContent: function (arg) { 
								var view = calendar.view.type;
								let nome = arg.event.title;
								let id = arg.event.id;
								let inicio = arg.event.extendedProps.hora;
								let fim = arg.event.extendedProps.horaFinal;
								let hora = `${inicio}-${fim}`;
								let cadeira = arg.event.extendedProps.cadeira;
								let id_paciente = arg.event.extendedProps.id_paciente;

								let situacao = arg.event.extendedProps.situacao;
								let agendaPessoal = arg.event.extendedProps.agendaPessoal;

								let id_status = arg.event.extendedProps.id_status;
								let telefone1 = arg.event.extendedProps.telefone1;
								let agendadoHa = arg.event.extendedProps.agendadoHa;
								let agendadoPor = arg.event.extendedProps.agendadoPor;
								let statusColor = arg.event.extendedProps.statusColor;
								let profissionais = arg.event.extendedProps.profissionais;
								let id_agenda = arg.event.id;
								let wts = arg.event.extendedProps.wts;

								let wtsIcon = ``;
								/*if(wts !== undefined && wts == 1) {
								
									wtsIcon=`<span class="iconify" data-icon="akar-icons:whatsapp-fill"></span>`;
								}*/

								profissionaisHTML = ``;
								if(profissionais && profissionais.length>0) {
									cont=0;
									profissionais.forEach(p=> {
										if(cont>1 && (profissionais.length-2)>0) {
											profissionaisHTML+=`<div class="cal-item-foto"><span>+${(profissionais.length-2)}</span></div>`;
										} else {
											profissionaisHTML+=`<div class="cal-item-foto"><span  style="background:${p.cor}">${p.iniciais}</span></div>`;
										}
										cont++;
									})
								}
	   							
	   							
								if(profissionaisHTML.length!=0) profissionaisHTML = `<div class="cal-item__fotos">${profissionaisHTML}</div>`; 
								


							    if(view=="dayGridMonth") {
							    	eventHTML=`<section class="cal-item" style="height:100%;border-left:6px solid ${statusColor};" >
													
													<section onclick="popView(${id_agenda});">
														 <p>${hora}</p>
														 <h1 class="cal-item__titulo">${nome}</h1>
													</section>
													${wtsIcon}
												</section>`
							    } else {
							    	eventHTML=`<section class="cal-item" style="height:100%;border-left:6px solid ${statusColor};" >
													<section class="cal-item__inner1" style="height:100%"  onclick="popView(${id_agenda});">
														<div class="cal-item-dados">
															<h1>${hora} - ${cadeira}</h1>
															<h2>${nome}</h2>
														</div>
														${wtsIcon}
														${profissionaisHTML}
													</section>
												</section>`
							    }
							    //console.log(agendaPessoal);
							    if(agendaPessoal==2) {
							    	eventHTML=`<div style="background:red"></div>`;
							    }
								return { html: eventHTML }
							},
							dayHeaderContent: function (arg) {
								//console.log(calendar.view.type);
								let dt = arg.date;
								let html = ``;
								if(calendar.view.type=="dayGridMonth") {
									setTimeout(function(){$('.fc-scrollgrid-sync-inner ').css('height','30px');},10);
									//return { html: html, arg: arg }
								} else {
									html = `<div class="agenda-fc__dia"><h1>${dia(dt.getDay())}</h1><h2>${dt.getDate()}</h2></div>`;
									setTimeout(function(){
										$('.fc-scrollgrid-sync-inner ').css('height','90px');},10);
										$('.fc-scrollgrid-sync-inner ').css('height','90px');
									return { html: html }
								}
							}
						  });
						calendar.render();

						setTimeout(function(){calendarioVisualizacaoData();},100);

						<?php
						if(!empty($initDate)) {
							list($ano,$mes,$dia)=explode("-",$initDate);

						?>
						let initDay='<?php echo $dia;?>';
						let initMonth='<?php echo und2($mes-1);?>';
						let initYear='<?php echo $ano;?>';
						let newDt = new Date(initYear,initMonth,initDay);
						calendar.gotoDate(newDt);
			
						//console.log(newDt);
						<?php
						}
						?>

					});
				</script>

				<div id='calendar'></div>
			</section>

			<section class="box" style="overflow:hidden; width:calc(100vw - 210px);">
				<div class="cal-lost">
					
					<div class="cal-lost-slick" id="js-agendamentosDesmarcados">
						<a href="" class="cal-lost-item" style="border-color:var(--verde);">
							<div>
								<p>08:30-09:00 - CONSULTÓRIO 3</p>
								<h1>Lidiane Vanessa Silva Bastos</h1>
							</div>
							<span class="badge-prof">SH</span>
						</a>
						
					</div>
				</div>
			</section>

		</div>
	</main>

	<section class="aside aside-add" id="js-aside-add">
		<script type="text/javascript">
			$(function(){
				$('#js-aside-add .js-salvar').click(function(){
					let obj = $(this);

					let agendaPessoal = $('#js-aside-add input[name=agendaPessoal]').val();


					if(obj.attr('data-loading')==0) {
						obj.html(`<span class="iconify" data-icon="eos-icons:loading"></span>`);
						obj.attr('data-loading',1);

						let erro=false;
						$(`#js-aside-add form .obg-${agendaPessoal}`).each(function(index,elem){
						//	console.log($(this).attr('name'));
							if($(this).attr('name')!==undefined && $(this).val()  && $(this).val().length==0) {
								$(elem).addClass('erro');
								erro=true;
							}
						});

						if(erro===true) {
							swal({title: "Erro!", text: "Complete os campos destacados", type:"error", confirmButtonColor: "#424242"});
							$('.js-salvar').html(`<i class="iconify" data-icon="bx-bx-check"></i><span>salvar</span>`);
							obj.attr('data-loading',0);
						} else {
							
							
							let campos = $('#js-aside-add form').serialize();
							let profissionais = $('#js-aside-add .js-profissionais').val();

							let data = `ajax=novoAgendamento&profissionais=${profissionais}&${campos}`;

							

							$.ajax({
								type:'POST',
								data:data,
								success:function(rtn) {
									if(rtn.success) {

										$('#js-aside-add select[name=id_paciente]').val('').trigger('change.select2');

										$('#js-aside-add select.js-profissionais').val('').trigger('chosen:updated');
										$('#js-aside-add input,#js-aside-add textarea').val('');
										$('#js-aside-add select[name=agenda_duracao]').val('');

										$.fancybox.close();
										calendar.refetchEvents();
										$('#js-aside-add input[name=alteracao]').val(0);
										$('#js-aside-add .aside-close-novoAgendamento').click();


										//swal({title: "Sucesso!", text: "Agendamento salvo com sucesso!", type:"success", confirmButtonColor: "#424242"});
									} else if(rtn.error) {
										swal({title: "Erro!", text: rtn.error, type:"error", confirmButtonColor: "#424242"});

									} else {
										swal({title: "Erro!", text: "Agendamento não efetuado. Por favor tente novamente!", type:"error", confirmButtonColor: "#424242"});
									}
								},
								error:function(){
									swal({title: "Erro!", text: "Agendamento não efetuado. Por favor tente novamente!", type:"error", confirmButtonColor: "#424242"});
								}
							}).done(function(){
								obj.html(`<i class="iconify" data-icon="fluent:checkmark-12-filled"></i> <span>Salvar</span>`);
								obj.attr('data-loading',0);
							})

						}
					}
					return false;
				});

				$('#js-aside-add').find('input,select,textarea').change(function(x){
					$('#js-aside-add input[name=alteracao]').val(1);
				});

				$('#js-aside-add .aside-close-novoAgendamento').click(function(){
					let obj = $(this);
					if($('#js-aside-add input[name=alteracao]').val()=="1") {
						swal({   
								title: "Atenção",   
								text: "Tem certeza que deseja fechar sem salvar as informações?",
								type: "warning",   
								showCancelButton: true,   
								confirmButtonColor: "#DD6B55",   
								confirmButtonText: "Sim!",   
								cancelButtonText: "Não",   
								closeOnConfirm: false,   
								closeOnCancel: false 
							}, function(isConfirm){   
								if (isConfirm) {   
									$(obj).parent().parent().removeClass("active");
									$(obj).parent().parent().parent().fadeOut(); 
									swal.close();
						  		 } else {   
						  		 	swal.close();   
						  		 } 
						  	});
		
					} else {
						$(obj).parent().parent().removeClass("active");
						$(obj).parent().parent().parent().fadeOut();
					}
				});

				$('#js-aside-add').find('input[name=agenda_data],input[name=agenda_hora]').change(function(){
					agendamentosProfissionais(`add`);
				});
			})
		</script>
		<div class="aside__inner1">

			<header class="aside-header">
				<h1>Novo Agendamento</h1>
				<a href="javascript:;" class="aside-header__fechar aside-close-novoAgendamento"><i class="iconify" data-icon="fluent:dismiss-24-filled"></i></a>
			</header>
			<form method="post" class="aside-content form" onsubmit="return false">
				<input type="hidden" name="agendaPessoal" value="0" />
				<input type="hidden" name="alteracao" value="0" />

				<script>
					
					$(function() {
						$('.js-tab a').click(function() {
							$(".js-tab a").removeClass("active");
							$(this).addClass("active");							
						});
						
					});
				</script>
				<section class="tab tab_alt js-tab">
					<a href="javascript:;" onclick="$('.js-paciente').show();$('#js-aside-add input[name=agendaPessoal]').val(0);" class="active">Paciente</a>
					<a href="javascript:;" onclick="$('.js-paciente').hide();$('#js-aside-add input[name=agendaPessoal]').val(1);">Compromisso Pessoal</a>				
				</section>
			
				<section class="filter">
					<div class="filter-group">
					</div>
					<div class="filter-group">
						<div class="filter-form form">
							<dl>
								<dd><button class="button button_main js-salvar" data-loading="0"><i class="iconify" data-icon="fluent:checkmark-12-filled"></i> <span>Salvar</span></button></dd>
							</dl>
						</div>								
					</div>
				</section>

				<div class="colunas3">
					<dl>
						<dt>Data</dt>
						<dd class="form-comp"><span><i class="iconify" data-icon="fluent:calendar-ltr-24-regular"></i></span><input type="tel" name="agenda_data" class="data obg-0" /></dd>
					</dl>
					<dl>
						<dt>Hora</dt>
						<dd class="form-comp"><span><i class="iconify" data-icon="fluent:clock-24-regular"></i></span><input type="tel" name="agenda_hora" class="hora obg-0" /></dd>
					</dl>
					<dl>
						<dt>Duração</dt>
						<dd class="form-comp form-comp_pos">
							<select name="agenda_duracao" class="obg-0">
								<option value="">-</option>
								<?php
								foreach($optAgendaDuracao as $v) {
									if($values['agenda_duracao']==$v) $possuiDuracao=true;
									echo '<option value="'.$v.'">'.$v.'</option>';
								}
								?>
							</select>
							<span>min</span>
						</dd>
					</dl>
				</div>
				<div class="js-paciente">
					<dl>
						<dt>Status do Agendamento</dt>
						<dd>
							<select name="id_status" class="obg-0">
								<option value="">-</option>
								<?php
								foreach($_status as $p) {
									echo '<option value="'.$p->id.'">'.utf8_encode($p->titulo).'</option>';
								}
								?>
							</select>
						</dd>
					</dl>
					<div class="colunas">
						<dl>
							<dt>Paciente</dt>
							<dd>
								<select name="id_paciente" class="select2 obg-0 ajax-id_paciente">
									<option value="">...</option>
								</select>
								<a  href="javascript:;" class="js-btn-aside button" data-aside="paciente" data-aside-sub><i class="iconify" data-icon="fluent:add-circle-24-regular"></i></a> 

								<?php /*<a href="javascript:;" class="js-btn-aside button" data-aside="profissao" data-aside-sub><i class="iconify" data-icon="fluent:add-24-regular"></i></a>*/?>
							</dd>
						</dl>
						<dl>
							<dt>Whatsapp</dt>
							<dd class="form-comp"><span class="js-country">BR</span><input type="tel" name="telefone1" class="" attern="\d*" x-autocompletetype="tel" /></dd>
						</dl>
					</div>
				</div>
				<div class="colunas">
					<dl>
						<dt>Profissionais</dt>
						<dd>
							<select class="js-profissionais" multiple>
								<option value=""></option>
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
						<dt>Consultório</dt>
						<dd>
							<select name="id_cadeira" class="obg-0">
								<option value="">-</option>
								<?php
								foreach($_cadeiras as $p) {
									echo '<option value="'.$p->id.'"'.($values['id_cadeira']==$p->id?' selected':'').'>'.utf8_encode($p->titulo).'</option>';
								}
								?>
							</select>
						</dd>
					</dl>
				</div>
				<dl>
					<dt>Informações</dt>
					<dd><textarea name="obs" style="height:100px;"></textarea></dd>
				</dl>
			</div>

		</form>
	</section><!-- .aside -->

	<section class="aside aside-edit-agendaPessoal" id="js-aside-edit-agendaPessoal">
		<script type="text/javascript">
			$(function(){

				$('#js-aside-edit-agendaPessoal .js-excluir').click(function(){

					let obj = $(this);

					swal({   
						title: "Atenção",   
						text: "Tem certeza que deseja remover este agendamento pessoal?",
						type: "warning",   
						showCancelButton: true,   
						confirmButtonColor: "#DD6B55",   
						confirmButtonText: "Sim!",   
						cancelButtonText: "Não",   
						closeOnConfirm: false,   
						closeOnCancel: false }, 
						function(isConfirm){   
							if (isConfirm) {    
								if(obj.attr('data-loading')==0) {
									obj.html(`<span class="iconify" data-icon="eos-icons:loading"></span>`);
									obj.attr('data-loading',1);
									let id = $('#js-aside-edit-agendaPessoal input[name=id]').val();

									let data = `ajax=agendamentoRemover&id=${id}`;   
									$.ajax({
										type:"POST",
										data:data,
										success:function(rtn){
											swal.close();  
											if(rtn.success) {
												$.fancybox.close();
												calendar.refetchEvents();
												$('#js-aside-edit-agendaPessoal .aside-close-edicaoAgendamentoPessoal').click();
												//swal({title: "Sucesso!", text: "Agendamento salvo com sucesso!", type:"success", confirmButtonColor: "#424242"});
											} else if(rtn.error) {
												swal({title: "Erro!", text: rtn.error, type:"error", confirmButtonColor: "#424242"});
											} else {
												swal({title: "Erro!", text: "Agendamento não removido. Por favor tente novamente!", type:"error", confirmButtonColor: "#424242"});
											}
										},
										error:function(){
											swal.close();  
											swal({title: "Erro!", text: "Agendamento não removido. Por favor tente novamente!", type:"error", confirmButtonColor: "#424242"});
										}
									}).done(function(){
										obj.html(`<i class="iconify" data-icon="fluent:delete-24-regular"></i>`);
										obj.attr('data-loading',0);
									});
								} 
							} 
							else {   
								swal.close();   
							} 
						});
				})

				$('#js-aside-edit-agendaPessoal .js-salvar').click(function(){
					let obj = $(this);

					if(obj.attr('data-loading')==0) {
						obj.html(`<span class="iconify" data-icon="eos-icons:loading"></span>`);
						obj.attr('data-loading',1);

						let erro=false;
						$('#js-aside-edit-agendaPessoal form .obg').each(function(index,elem){
							//console.log($(this).attr('name'));
							if($(this).attr('name')!==undefined && $(this).val().length==0) {
								$(elem).addClass('erro');
								erro=true;
							}
						});

						if(erro===true) {
							swal({title: "Erro!", text: "Complete os campos destacados", type:"error", confirmButtonColor: "#424242"});
							$('.js-salvar').html(`<i class="iconify" data-icon="bx-bx-check"></i><span>salvar</span>`);
							obj.attr('data-loading',0);
						} else {
							
							let campos = $('#js-aside-edit-agendaPessoal form').serialize();

							let data = `ajax=agendamentoPessoalPersistir&${campos}`;
							

							$.ajax({
								type:'POST',
								data:data,
								success:function(rtn) {
									if(rtn.success) {
										$.fancybox.close();
										calendar.refetchEvents();
										$('#js-aside-edit-agendaPessoal .aside-close-edicaoAgendamentoPessoal').click();


										//swal({title: "Sucesso!", text: "Agendamento salvo com sucesso!", type:"success", confirmButtonColor: "#424242"});
									} else if(rtn.error) {
										swal({title: "Erro!", text: rtn.error, type:"error", confirmButtonColor: "#424242"});

									} else {
										swal({title: "Erro!", text: "Agendamento não efetuado. Por favor tente novamente!", type:"error", confirmButtonColor: "#424242"});
									}
								},
								error:function(){
									swal({title: "Erro!", text: "Agendamento não efetuado. Por favor tente novamente!", type:"error", confirmButtonColor: "#424242"});
								}
							}).done(function(){
								obj.html(`<i class="iconify" data-icon="fluent:checkmark-12-filled"></i> <span>Salvar</span>`);
								obj.attr('data-loading',0);
							})

						}
					}
					return false;
				});

				$('#js-aside-edit-agendaPessoal').find('input,select,textarea').change(function(x){
					$('#js-aside-edit-agendaPessoal input[name=alteracao]').val(1);
				});

				$('#js-aside-edit-agendaPessoal .aside-close-edicaoAgendamentoPessoal').click(function(){
					let obj = $(this);
					if($('#js-aside-edit-agendaPessoal input[name=alteracao]').val()=="1") {
						swal({   
								title: "Atenção",   
								text: "Tem certeza que deseja fechar sem salvar as informações?",
								type: "warning",   
								showCancelButton: true,   
								confirmButtonColor: "#DD6B55",   
								confirmButtonText: "Sim!",   
								cancelButtonText: "Não",   
								closeOnConfirm: false,   
								closeOnCancel: false 
							}, function(isConfirm){   
								if (isConfirm) {   
									$(obj).parent().parent().removeClass("active");
									$(obj).parent().parent().parent().fadeOut(); 
									swal.close();
						  		 } else {   
						  		 	swal.close();   
						  		 } 
						  	});
		
					} else {
						$(obj).parent().parent().removeClass("active");
						$(obj).parent().parent().parent().fadeOut();
					}
				});


			})
		</script>
		<div class="aside__inner1">

			<header class="aside-header">
				<h1>Agenda Pessoal</h1>
				<a href="javascript:;" class="aside-header__fechar aside-close-edicaoAgendamentoPessoal"><i class="iconify" data-icon="fluent:dismiss-24-filled"></i></a>
			</header>

			<form method="post" class="aside-content form" onsubmit="return false">
				<input type="hidden" name="id" />
				<input type="hidden" name="alteracao" value="0" />

				<script>
					$(function() {
						$('.js-tab a').click(function() {
							$(".js-tab a").removeClass("active");
							$(this).addClass("active");							
						});
					});
				</script>
				
			
				<section class="filter">
					<div class="filter-group">
					</div>
					<div class="filter-group">
						<div class="filter-form form">
							<dl>
								<dd><a href="javascript:;" class="button js-excluir" data-loading="0"><i class="iconify" data-icon="fluent:delete-24-regular"></i></a></dd>
							</dl>
							<dl>
								<dd><button class="button button_main js-salvar" data-loading="0"><i class="iconify" data-icon="fluent:checkmark-12-filled"></i> <span>Salvar</span></button></dd>
							</dl>
						</div>								
					</div>
				</section>

				<div class="colunas3">
					<dl>
						<dt>Data</dt>
						<dd class="form-comp"><span><i class="iconify" data-icon="fluent:calendar-ltr-24-regular"></i></span><input type="tel" name="agenda_data" class="data" /></dd>
					</dl>
					<dl>
						<dt>Hora</dt>
						<dd class="form-comp"><span><i class="iconify" data-icon="fluent:clock-24-regular"></i></span><input type="tel" name="agenda_hora" class="hora" /></dd>
					</dl>
					<dl>
						<dt>Duração</dt>
						<dd class="form-comp form-comp_pos">
							<select name="agenda_duracao">
								<?php
								foreach($optAgendaDuracao as $v) {
									if($values['agenda_duracao']==$v) $possuiDuracao=true;
									echo '<option value="'.$v.'"'.($values['agenda_duracao']==$v?' selected':'').'>'.$v.'</option>';
								}
								?>
							</select>
							<span>min</span>
						</dd>
					</dl>
				</div>
				<div class="colunas">
					<dl>
						<dt>Profissionais</dt>
						<dd>
							<select name="id_profissional"  class="">
								<option value=""></option>
								<?php
								foreach($_profissionais as $p) {
									if($p->check_agendamento==0) continue;
									echo '<option value="'.$p->id.'"'.(in_array($p->id, $values['profissionais'])?' selected':'').'>'.utf8_encode($p->nome).'</option>';
								}
								?>
							</select>
						</dd>
					</dl>
					<dl>
						<dt>Consultório</dt>
						<dd>
							<select name="id_cadeira">
								<option value=""></option>
								<?php
								foreach($_cadeiras as $p) {
									echo '<option value="'.$p->id.'"'.($values['id_cadeira']==$p->id?' selected':'').'>'.utf8_encode($p->titulo).'</option>';
								}
								?>
							</select>
						</dd>
					</dl>
				</div>
				<dl>
					<dt>Informações</dt>
					<dd><textarea name="obs" style="height:100px;"></textarea></dd>
				</dl>
			</div>

		</form>
	</section><!-- .aside -->


	<section class="aside aside-edit" id="js-aside-edit">
		<script type="text/javascript">
			$(function(){

				$('#js-aside-edit .js-excluir').click(function(){

					let obj = $(this);

					swal({   
						title: "Atenção",   
						text: "Tem certeza que deseja remover este agendamento?",
						type: "warning",   
						showCancelButton: true,   
						confirmButtonColor: "#DD6B55",   
						confirmButtonText: "Sim!",   
						cancelButtonText: "Não",   
						closeOnConfirm: false,   
						closeOnCancel: false }, 
						function(isConfirm){   
							if (isConfirm) {    
								if(obj.attr('data-loading')==0) {
									obj.html(`<span class="iconify" data-icon="eos-icons:loading"></span>`);
									obj.attr('data-loading',1);
									let id = $('#js-aside-edit input[name=id]').val();

									let data = `ajax=agendamentoRemover&id=${id}`;   
									$.ajax({
										type:"POST",
										data:data,
										success:function(rtn){
											swal.close();  
											if(rtn.success) {
												$.fancybox.close();
												calendar.refetchEvents();
												$('#js-aside-edit .aside-close-edicaoAgendamento').click();
												//swal({title: "Sucesso!", text: "Agendamento salvo com sucesso!", type:"success", confirmButtonColor: "#424242"});
											} else if(rtn.error) {
												swal({title: "Erro!", text: rtn.error, type:"error", confirmButtonColor: "#424242"});
											} else {
												swal({title: "Erro!", text: "Agendamento não removido. Por favor tente novamente!", type:"error", confirmButtonColor: "#424242"});
											}
										},
										error:function(){
											swal.close();  
											swal({title: "Erro!", text: "Agendamento não removido. Por favor tente novamente!", type:"error", confirmButtonColor: "#424242"});
										}
									}).done(function(){
										obj.html(`<i class="iconify" data-icon="fluent:delete-24-regular"></i>`);
										obj.attr('data-loading',0);
									});
								} 
							} 
							else {   
								swal.close();   
							} 
						});
				});

				$('#js-aside-edit .js-salvar').click(function(){
					let obj = $(this);

					if(obj.attr('data-loading')==0) {
						obj.html(`<span class="iconify" data-icon="eos-icons:loading"></span>`);
						obj.attr('data-loading',1);

						let erro=false;
						$('#js-aside-edit form .obg').each(function(index,elem){
							//console.log($(this).attr('name'));
							if($(this).attr('name')!==undefined && $(this).val().length==0) {
								$(elem).addClass('erro');
								erro=true;
							}
						});

						if(erro===true) {
							swal({title: "Erro!", text: "Complete os campos destacados", type:"error", confirmButtonColor: "#424242"});
							$('.js-salvar').html(`<i class="iconify" data-icon="bx-bx-check"></i><span>salvar</span>`);
							obj.attr('data-loading',0);
						} else {
							
							let campos = $('#js-aside-edit form').serialize();
							let profissionais = $('#js-aside-edit .js-profissionais').val();

							let data = `ajax=agendamentoPersistir&profissionais=${profissionais}&${campos}`;
							
							let abrirProximaConsulta = 0;
							if($('#js-aside-edit input[name=id_status_antigo]').val()!=$('#js-aside-edit select[name=id_status]').val() && $('#js-aside-edit select[name=id_status]').val()==5) {
								abrirProximaConsulta=$('#js-aside-edit input[name=id]').val();
							}

							$.ajax({
								type:'POST',
								data:data,
								success:function(rtn) {
									if(rtn.success) {
										$.fancybox.close();
										calendar.refetchEvents();
										$('#js-aside-edit input[name=alteracao]').val(0);
										$('#js-aside-edit .aside-close-edicaoAgendamento').click();

										if(rtn.wts && rtn.wts==1) {
											let data = `ajax=whatsappDisparar`;
											$.ajax({
												type:"POST",
												data:data
											})
										}

										if(abrirProximaConsulta>0) asideProximaConsulta(abrirProximaConsulta);


										//swal({title: "Sucesso!", text: "Agendamento salvo com sucesso!", type:"success", confirmButtonColor: "#424242"});
									} else if(rtn.error) {
										swal({title: "Erro!", text: rtn.error, type:"error", confirmButtonColor: "#424242"});

									} else {
										swal({title: "Erro!", text: "Agendamento não efetuado. Por favor tente novamente!", type:"error", confirmButtonColor: "#424242"});
									}
								},
								error:function(){
									swal({title: "Erro!", text: "Agendamento não efetuado. Por favor tente novamente!", type:"error", confirmButtonColor: "#424242"});
								}
							}).done(function(){
								obj.html(`<i class="iconify" data-icon="fluent:checkmark-12-filled"></i> <span>Salvar</span>`);
								obj.attr('data-loading',0);
							})

						}
					}
					return false;
				});

				$('#js-aside-edit').find('input,select,textarea').change(function(x){
					$('#js-aside-edit input[name=alteracao]').val(1);
				});

				$('#js-aside-edit .aside-close-edicaoAgendamento').click(function(){
					let obj = $(this);
					if($('#js-aside-edit input[name=alteracao]').val()=="1") {
						swal({   
								title: "Atenção",   
								text: "Tem certeza que deseja fechar sem salvar as informações?",
								type: "warning",   
								showCancelButton: true,   
								confirmButtonColor: "#DD6B55",   
								confirmButtonText: "Sim!",   
								cancelButtonText: "Não",   
								closeOnConfirm: false,   
								closeOnCancel: false 
							}, function(isConfirm){   
								if (isConfirm) {   
									$(obj).parent().parent().removeClass("active");
									$(obj).parent().parent().parent().fadeOut(); 
									swal.close();
						  		 } else {   
						  		 	swal.close();   
						  		 } 
						  	});
		
					} else {
						$(obj).parent().parent().removeClass("active");
						$(obj).parent().parent().parent().fadeOut();
					}
				});

				$('#js-aside-edit').find('input[name=agenda_data],input[name=agenda_hora]').change(function(){
					agendamentosProfissionais(`edit`);
				});
			})
		</script>
		<div class="aside__inner1">

			<header class="aside-header">
				<h1>Detalhes do Agendamento</h1>
				<a href="javascript:;" class="aside-header__fechar aside-close-edicaoAgendamento"><i class="iconify" data-icon="fluent:dismiss-24-filled"></i></a>
			</header>

			<form method="post" class="aside-content form" onsubmit="return false">
				<input type="hidden" name="id_status_antigo" />
				<input type="hidden" name="id" />
				<input type="hidden" name="alteracao" value="0" />
				<section class="header-profile">
					<img src="img/ilustra-usuario.jpg" alt="" width="60" height="60" class="header-profile__foto js-foto" />
					<div class="header-profile__inner1">
						<h1><a href="" target="_blank" class="js-nome"></a></h1>
						<div>
							<p class="js-statusBI"></p>
							<p class="js-idade"></p>
							<p class="js-periodicidade">Periodicidade: 6 meses</p>
							<p class="js-musica"></p>
						</div>
					</div>
				</section>

				<script>
					$(function() {
						$('.js-tab a').click(function() {
							$(".js-tab a").removeClass("active");
							$(this).addClass("active");							
						});
					});
				</script>
				<section class="tab tab_alt js-tab">
					<a href="javascript:;" onclick="$('.js-ag').hide(); $('.js-ag-agenda').show();" class="active">Agenda</a>
					<a href="javascript:;" onclick="$('.js-ag').hide(); $('.js-ag-futuro').show();">Agendamentos Futuros</a>
					<a href="javascript:;" onclick="$('.js-ag').hide(); $('.js-ag-historico').show();">Histórico</a>					
				</section>
			
				<div class="js-ag js-ag-agenda">
					<section class="filter">
						<div class="filter-group">
							<div class="filter-title">
								<p class="js-agendou">Simone agendou há 29 dias</p>
							</div>
						</div>
						<div class="filter-group">
							<div class="filter-form form">
								<dl>
									<dd><a href="javascript:;" class="button js-excluir" data-loading="0"><i class="iconify" data-icon="fluent:delete-24-regular"></i></a></dd>
								</dl>
								<dl>
									<dd><button class="button button_main js-salvar" data-loading="0"><i class="iconify" data-icon="fluent:checkmark-12-filled"></i> <span>Salvar</span></button></dd>
								</dl>
							</div>								
						</div>
					</section>

					<div class="colunas3">
						<dl>
							<dt>Data</dt>
							<dd class="form-comp"><span><i class="iconify" data-icon="fluent:calendar-ltr-24-regular"></i></span><input type="tel" name="agenda_data" class="data" /></dd>
						</dl>
						<dl>
							<dt>Hora</dt>
							<dd class="form-comp"><span><i class="iconify" data-icon="fluent:clock-24-regular"></i></span><input type="tel" name="agenda_hora" class="hora" /></dd>
						</dl>
						<dl>
							<dt>Duração</dt>
							<dd class="form-comp form-comp_pos">
								<?php /*<input type="tel" name="agenda_duracao" class="" />*/?>
								<select name="agenda_duracao">
									<?php
									foreach($optAgendaDuracao as $v) {
										echo '<option value="'.$v.'"'.($values['agenda_duracao']==$v?' selected':'').'>'.$v.'</option>';
									}
									?>
								</select>
								<span>min</span>
							</dd>
						</dl>
					</div>
					<dl>
						<dt>Status do Agendamento</dt>
						<dd>
							<select name="id_status" class="obg">
								<option value="">-</option>
								<?php
								foreach($_status as $p) {
									echo '<option value="'.$p->id.'">'.utf8_encode($p->titulo).'</option>';
								}
								?>
							</select>
						</dd>
					</dl>
					<div class="colunas">
						<dl>
							<dt>Telefone</dt>
							<dd class="form-comp"><span class="js-country">BR</span><input type="tel" name="telefone1" class="" /></dd>
						</dl>
						<dl>
							<dt>Consultório</dt>
							<dd>
								<select name="id_cadeira">
									<option value=""></option>
									<?php
									foreach($_cadeiras as $p) {
										echo '<option value="'.$p->id.'"'.($values['id_cadeira']==$p->id?' selected':'').'>'.utf8_encode($p->titulo).'</option>';
									}
									?>
								</select>
							</dd>
						</dl>
					</div>
					<div class="colunas">
						<dl class="dl2">
							<dt>Profissionais</dt>
							<dd>
								<select class="js-profissionais" multiple>
									<option value=""></option>
									<?php
									foreach($_profissionais as $p) {
										if($p->check_agendamento==0) continue;
										echo '<option value="'.$p->id.'">'.utf8_encode($p->nome).'</option>';
									}
									?>
								</select>
							</dd>
						</dl>
					</div>
					<dl>
						<dt>Informações</dt>
						<dd><textarea name="obs" style="height:100px;"></textarea></dd>
					</dl>
				</div>

				<div class="js-ag js-ag-futuro" style="display:none;">
					<div class="list1">
						<table>
						</table>
					</div>
				</div>

				<div class="js-ag js-ag-historico" style="display:none;">
					<div class="history">
						<div class="history-item">
							<h1>Simone agendou para 10/02/2022 17:30</h1>
							<h2>Status alterado para <em style="background:red;">CANCELADO</em></h2>
						</div>
					</div>
				</div>

			</form>

		</div>
	</section><!-- .aside -->	

<?php 
	
	$apiConfig=array('paciente'=>1,'proximaConsulta'=>1);
	require_once("includes/api/apiAside.php");


	include "includes/footer.php";
?>	