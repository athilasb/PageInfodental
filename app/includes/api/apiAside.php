<?php	
	if(isset($_POST['ajax'])) {
		$dir="../../";
		require_once("../../lib/conf.php");
		require_once("../../usuarios/checa.php");


		$attr=array('prefixo'=>$_p,'usr'=>$usr);
		$infozap = new Whatsapp($attr);

		$rtn = array();

		$_tableEspecialidades=$_p."parametros_especialidades";
		$_tablePlanos=$_p."parametros_planos";
		$_tableMarcas=$_p."produtos_marcas";
		$_tableCategorias=$_p."produtos_categorias";
		$_tablePacientes=$_p."pacientes";
		$_tableProfissoes=$_p."parametros_profissoes";
		$_tableListaPersonalizada=$_p."parametros_indicacoes";
		$_tableTags=$_p."parametros_tags";
		$_tableChecklist=$_p."agenda_checklist";


		$_usuarios=[];
		$sql->consult($_p."colaboradores","id,nome","");
		while($x=mysqli_fetch_object($sql->mysqry)) $_usuarios[$x->id]=$x;

		# Agenda
			if($_POST['ajax']=="agendamentosProfissionais") {

				$_profissionais=array();
				$_profissionaisTodos=array();
				$sql->consult($_p."colaboradores","id,nome,calendario_iniciais,lixo,foto,calendario_cor,check_agendamento,contratacaoAtiva"," order by nome asc");
				while($x=mysqli_fetch_object($sql->mysqry)) {
					if($x->lixo==0) $_profissionais[$x->id]=$x;
					$_profissionaisTodos[$x->id]=$x;
				}

				$_tags=array();
				$sql->consult($_p."parametros_tags","*","WHERE lixo=0 order by titulo asc");
				while($x=mysqli_fetch_object($sql->mysqry)) {
					$_tags[$x->id]=array('id'=>$x->id,'titulo'=>utf8_encode($x->titulo));
				}
				
				$data = (isset($_POST['data']) and isset($_POST['data']))?invDate($_POST['data']):'';
				$hora = (isset($_POST['hora']) and isset($_POST['hora']))?$_POST['hora'].":00":'';
				$id_cadeira = (isset($_POST['id_cadeira']) and isset($_POST['id_cadeira']))?$_POST['id_cadeira']:0;
				if(!empty($data) and !empty($hora)) {
					$diaSemana = date('w',strtotime($data));
					

					$profissionaisDestaque=array();
					$where="where dia='$diaSemana' and 
									inicio<='$hora' and 
									fim>'$hora' and 
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
						if($x->check_agendamento==0 or $x->contratacaoAtiva==0) continue;
						if(isset($profissionaisDestaque[$x->id])) {
							$listaProfissionaisDestaque[]=(object)array('id'=>$x->id,'nome'=>utf8_encode($x->nome),'destaque'=>1);
						}
					}


					foreach($_profissionais as $x) {
						if($x->check_agendamento==0 or $x->contratacaoAtiva==0) continue;
						if(!isset($profissionaisDestaque[$x->id])) {
							$listaProfissionais[]=(object)array('id'=>$x->id,'nome'=>utf8_encode($x->nome),'destaque'=>0);
						}
					}

					$rtn=array('success'=>true,
								'listaProfissionaisDestaque'=>$listaProfissionaisDestaque,
								'listaProfissionais'=>$listaProfissionais,
								'tags' => $_tags);
				}
			}

			else if($_POST['ajax']=="editar") {
				$cnt = '';
				$paciente='';
				$carga = '';
				if(isset($_POST['id']) and is_numeric($_POST['id'])) {
					$sql->consult($_p."agenda","*","where id=".$_POST['id']);
					if($sql->rows) {
						$cnt=mysqli_fetch_object($sql->mysqry);

						$sql->consult($_p."pacientes","id,nome,data_nascimento,telefone1,foto,codigo_bi,musica,periodicidade,foto_cn,plano_odontologico","where id=$cnt->id_paciente");
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
					$profissionaisID=array(-1);
					if(!empty($cnt->profissionais)) {
						$profissioaisObj=explode(",",$cnt->profissionais);
						foreach($profissioaisObj as $v) {
							if(!empty($v) and is_numeric($v)) $profissionaisID[]=$v;
						}
					}

					$tags='';
					$tagsID=array(-1);
					if(!empty($cnt->tags)) {
						$tagsObj=explode(",",$cnt->tags);
						foreach($tagsObj as $v) {
							if(!empty($v) and is_numeric($v)) $tagsID[]=$v;
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
										'tags'=>$tagsID,
										'obs'=>addslashes(utf8_encode($cnt->obs)));

					} else {

						$agendamentosFuturos=array();

						if($paciente->data_nascimento!="0000-00-00") {
							$dob = new DateTime($paciente->data_nascimento);
							$now = new DateTime();
							$idade = $now->diff($dob)->y;
						} else $idade=0;

						$_status=array();
						$sql->consult($_p."agenda_status","*","where lixo=0 order by kanban_ordem asc");
						while($x=mysqli_fetch_object($sql->mysqry)) {
							$_status[$x->id]=$x;
						}

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

							$tags=array();
							$aux = explode(",",$x->tags);
							
							foreach($aux as $id_tag) {
								if(!empty($id_tag) and is_numeric($id_tag)) {

									if(isset($_tags[$id_tag])) {
										$cor=$_tags[$id_tag]->cor;
										$titulo=utf8_encode($_profissionais[$id_tag]->titulo);

										$tags[]=array('titulo'=>$titulo,'cor'=>$cor);
									}
								}
							} 

							$_pacientesAgendamentos[$x->id_paciente][]=array('id_agenda'=>$x->id,
																				'obs'=>str_replace("'","`",utf8_encode($x->obs)),
																				'data'=>date('d/m/Y H:i',strtotime($x->agenda_data)),
																				'initDate'=>date('d/m/Y',strtotime($x->agenda_data)),
																				'cadeira'=>isset($_cadeiras[$x->id_cadeira])?utf8_encode($_cadeiras[$x->id_cadeira]->titulo):'',
																				'profissionais'=>$profissionais,
																				'tags'=>$tags);
						}

						if(isset($_pacientesAgendamentos[$paciente->id])) {
							$agendamentosFuturos=$_pacientesAgendamentos[$paciente->id];
						}
					
						$_historico=array();
						$sql->consult($_p."pacientes_historico","*","where id_agenda=$cnt->id and lixo=0 order by data desc");

						
						while($x=mysqli_fetch_object($sql->mysqry)) {

							if($x->evento=="agendaHorario") {
									$_historico[]=array('usr'=>isset($_profissionaisTodos[$x->id_usuario])?utf8_encode($_profissionaisTodos[$x->id_usuario]->nome):'Desconhecido',
																		'dt'=>date('d/m H:i',strtotime($x->data)),
																		'ev'=>'horario',
																		'nvDt'=>date('d/m H:i',strtotime($x->agenda_data_novo)),
																		'antDt'=>date('d/m H:i',strtotime($x->agenda_data_antigo))
																	);

							} else {
								if(isset($_status[$x->id_status_novo])) { 
									$_historico[]=array('usr'=>isset($_profissionaisTodos[$x->id_usuario])?utf8_encode($_profissionaisTodos[$x->id_usuario]->nome):'Desconhecido',
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

						$_whatsappTipos=[];
						$sql->consult($_p."whatsapp_mensagens_tipos","*","");
						while($x=mysqli_fetch_object($sql->mysqry)) $_whatsappTipos[$x->id]=$x;

						$_whatsappHistorico=[];
						$sql->consult($_p."whatsapp_mensagens","*","where id_agenda=$cnt->id and lixo=0 order by data desc");
						while($x=mysqli_fetch_object($sql->mysqry)) {
							$_whatsappHistorico[]=array('id'=>$x->id,
														'data'=>date('d/m/Y H:i',strtotime($x->data)),
														'numero'=>$x->numero,
														'tipo'=>isset($_whatsappTipos[$x->id_tipo])?utf8_encode($_whatsappTipos[$x->id_tipo]->titulo):"-",
														'enviado'=>$x->enviado,
														'mensagem'=>utf8_encode(nl2br($x->mensagem)));
						}


						$dias=strtotime(date('Y-m-d H:i:s'))-strtotime($cnt->data);
						$dias/=60*60*24;
						$dias=round($dias);

						$ft='';
						if(!empty($paciente->foto_cn)) {
							$ft=$_cloudinaryURL.'c_thumb,w_100,h_100/'.$paciente->foto_cn;
						} else if(!empty($paciente->foto)) {
							$ft=$_wasabiURL."arqs/clientes/".$paciente->id.".jpg";
						}

						$agendouProfissional="Desconhecido";

						if(isset($_usuarios[$cnt->id_usuario])) {

							$aux = explode(" ",utf8_encode($_usuarios[$cnt->id_usuario]->nome));
							$pnome=$aux[0];
							$unome=$aux[count($aux)-1];

							$agendouProfissional=$pnome." ".$unome;
						} 

						$data = array('id'=>$cnt->id,
										'agendou_profissional'=>$agendouProfissional,
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
										'plano_odontologico'=>utf8_encode($paciente->plano_odontologico),
										'id_cadeira'=>$cnt->id_cadeira,
										'telefone1'=>$paciente->telefone1,
										'musica'=>utf8_encode($paciente->musica),
										'periodicidade'=>isset($_pacientesPeriodicidade[$paciente->periodicidade])?$_pacientesPeriodicidade[$paciente->periodicidade]:$paciente->periodicidade,
										'profissionais'=>$profissionaisID,
										'tags'=>$tagsID,
										'statusBI'=>isset($_codigoBI[$paciente->codigo_bi])?utf8_encode($_codigoBI[$paciente->codigo_bi]):"",
										'obs'=>addslashes(utf8_encode($cnt->obs)),
										'agendamentosFuturos'=>$agendamentosFuturos,
										'historico'=>$_historico,
										'whatsapp'=>$_whatsappHistorico);
					}

					$rtn=array('success'=>true,'data'=>$data);

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

				$tags=array();
				if(isset($_POST['tags']) and !empty($_POST['tags'])) {
					$pAux=explode(",",$_POST['tags']);
					foreach($pAux as $id_tag) {
						if(is_numeric($id_tag)) $tags[]=$id_tag;
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

						if(isset($_POST['obs'])) $vSQL.=",obs='".addslashes(utf8_decode($_POST['obs']))."'";
						if(count($tags)>0) $vSQL.=",tags=',".implode(",",$tags).",'";
						else $vSQL.=",tags=''";

						$sql->add($_p."agenda",$vSQL);
						$id_agenda=$sql->ulid;
						$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='insert',vsql='".addslashes($vSQL)."',tabela='".$_p."agenda',id_reg='".$id_agenda."'");

						// Salvando Checklist
						$vSQLChecklist="";
						$sql->consult("infodentalADM.infod_parametros_agenda_checklist","*","WHERE lixo=0");
						while($x=mysqli_fetch_object($sql->mysqry)) {
							if(isset($_POST['checklist-'.$x->id])) {
								$vSQLChecklist.="(".$id_agenda.",".$x->id;

								if(isset($_POST['checklist_descricao-'.$x->id])) {
									$vSQLChecklist.=",'".utf8_decode($_POST['checklist_descricao-'.$x->id])."'),";
								} 
							}
						}

						if(!empty($vSQLChecklist)) {
							$sql->insertMultiple($_p."agenda_checklist","id_agenda,id_checklist,descricao",$vSQLChecklist);
						}

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
						if(count($tags)>0) $vSQL.=",tags=',".implode(",",$tags).",'";
						else $vSQL.=",tags=''";

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

						// Salvando Checklist
						$vSQLChecklist="";
						$sql->consult("infodentalADM.infod_parametros_agenda_checklist","*","WHERE lixo=0");
						while($x=mysqli_fetch_object($sql->mysqry)) {
							if(isset($_POST['checklist-'.$x->id])) {
								$vSQLChecklist.="(".$id_agenda.",".$x->id;

								if(isset($_POST['checklist_descricao-'.$x->id])) {
									$vSQLChecklist.=",'".utf8_decode($_POST['checklist_descricao-'.$x->id])."'),";
								} 
							}
						}

						if(!empty($vSQLChecklist)) {
							$vSQLChecklist=substr($vSQLChecklist,0,strlen($vSQLChecklist)-1);
							$sql->insertMultiple($_p."agenda_checklist","id_agenda,id_checklist,descricao",$vSQLChecklist);
						}

						$rtn=array('success'=>true,'id_paciente'=>$paciente->id);

					}
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

				$tags=array();
				if(isset($_POST['tags']) and !empty($_POST['tags'])) {
					$pAux=explode(",",$_POST['tags']);
					foreach($pAux as $id_tag) {
						if(is_numeric($id_tag)) $tags[]=$id_tag;
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
				} 
				else if(empty($agendaHora)) {
					$rtn=array('success'=>false,'error'=>'Hora inválida!');
				} 
				else {
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
						if(count($tags)>0) $vSQL.=",tags=',".implode(",",$tags).",'";
						else $vSQL.=",tags=''";

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

						if(strtotime($agenda->agenda_data)!=strtotime($agendaAlterado) and ($agenda->id_status==2 or $idStatusNovo==2)) {
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


							// atualiza data do agendamento
							$sql->update($_p."whatsapp_mensagens","fila_agenda_data='$agendaAlterado'","where id_agenda=$agenda->id");


							// altera campo agenda_alteracao_data para enviar a notificacao de alteração de horário
							$sql->update($_p."agenda","agenda_alteracao_data=now(),agenda_alteracao_id_whatsapp=0","where id=$agenda->id");

							$attr=array('id_tipo'=>5,
										'id_paciente'=>$agenda->id_paciente,
										'id_agenda'=>$agenda->id);
							//var_dump($attr);
							if($infozap->adicionaNaFila($attr)) $wts=1;   

							// se virou para confirmado, envia wts para dentista
							$sql->consult($_p."agenda","*","where id=$agenda->id");
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
								}
								if(count($profissionaisIds)>0) {
									$sql->consult($_p."colaboradores","*","where id IN (".implode(",",$profissionaisIds).") and whatsapp_notificacoes=1 and lixo=0");
									while($x=mysqli_fetch_object($sql->mysqry)) {
										if(!empty($x->telefone1)) {
											$attr=array('id_tipo'=>7,
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

						// veririca se desmarcou
						$wts=0;
							
						// Se Desmarcou
						if($idStatusNovo==4) {
							$attr=array('id_tipo'=>3,
										'id_paciente'=>$agenda->id_paciente,
										'id_agenda'=>$agenda->id);
							//var_dump($attr);
							if($infozap->adicionaNaFila($attr)) $wts=1;   

							 // se virou para desmarcado, envia wts para dentista
							$sql->consult($_p."agenda","*","where id=$agenda->id and id_status=4");

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
												$attr=array('id_tipo'=>8,
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
									
												//if($infozap->adicionaNaFila($attr)) {  // COMENTEI AQUI PARA CORRIGIR O ERRO DE MUDANÇA DE ESTATUS
												//	$wts=1;
												//}
											}
										}
									}
								}

							}
						}
						
					}


					$rtn=array('success'=>true,'wts'=>$wts);
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

		# Checklist
			else if($_POST['ajax']=="checklistListar") {

				$agenda='';
				if(isset($_POST['id_agenda']) and is_numeric($_POST['id_agenda'])) {
					$sql->consult($_p."agenda","*","where id='".addslashes($_POST['id_agenda'])."'");
					if($sql->rows) {
						$agenda=mysqli_fetch_object($sql->mysqry);
					}
				}

				if(empty($agenda)) $rtn=array('success'=>false,'error'=>'Agendamento não encontrado');
				else {

					$_checklist=array();
					$sql->consult("infodentalADM.infod_parametros_agenda_checklist","*","WHERE lixo=0");
					while($x=mysqli_fetch_object($sql->mysqry)) {
						$_checklist[$x->id]=$x;
					}

					$regs=array();
					$sql->consult($_tableChecklist,"*","WHERE lixo=0 and id_agenda='".$agenda->id."'");
					while($x=mysqli_fetch_object($sql->mysqry)) {
						if(isset($_checklist[$x->id_checklist])) {
							$regs[]=array('id'=>$x->id,
										  'id_agenda'=>$x->id_agenda,
										  'titulo' => utf8_encode($_checklist[$x->id_checklist]->titulo),
										  'checado'=>$x->checado,
										  'descricao'=>utf8_encode($x->descricao));
						}
					}

					$rtn=array('success'=>true,'regs'=>$regs);
				}
			}

			else if($_POST['ajax']=="checklistChecado") {

				$agenda='';
				if(isset($_POST['id_agenda']) and is_numeric($_POST['id_agenda'])) {
					$sql->consult($_p."agenda","*","where id='".addslashes($_POST['id_agenda'])."'");
					if($sql->rows) {
						$agenda=mysqli_fetch_object($sql->mysqry);
					}
				}

				$agendaChecklist='';
				if(isset($_POST['id_agenda_checklist']) and is_numeric($_POST['id_agenda_checklist'])) {
					$sql->consult($_tableChecklist,"*","where id='".addslashes($_POST['id_agenda_checklist'])."'");
					if($sql->rows) {
						$agendaChecklist=mysqli_fetch_object($sql->mysqry);
					}
				}

				if(empty($agenda)) $rtn=array('success'=>false,'error'=>'Agendamento não encontrado');
				if(empty($agendaChecklist)) $rtn=array('success'=>false,'error'=>'Agendamento Checklist não encontrado');
				else {
					$checado=(isset($_POST['checado']) and $_POST['checado']==1)?1:0;
					$sql->update($_tableChecklist,"checado='".$checado."'","WHERE id='".$agendaChecklist->id."' and id_agenda='".$agenda->id."'");
					$rtn=array('success'=>true);
				}
			}

		# Tags
			else if($_POST['ajax']=="tagsListar") {

				$_tags=array();
				$sql->consult($_p."parametros_tags","*","WHERE lixo=0");
				while($x=mysqli_fetch_object($sql->mysqry)) { 
					$x->titulo = utf8_encode($x->titulo);  
					$x->cor = utf8_encode($x->cor);
					$_tags[]=$x;
				}
				
				$rtn=array('success'=>true,'regs'=>$_tags);
			}

			else if($_POST['ajax']=="tagsRemover") {

				$tag='';
				if(isset($_POST['id']) and is_numeric($_POST['id'])) {
					$sql->consult($_p."parametros_tags","*","where id='".addslashes($_POST['id'])."'");
					if($sql->rows) {
						$tag=mysqli_fetch_object($sql->mysqry);
					}
				}

				if(empty($tag)) $rtn=array('success'=>false,'error'=>'Tag não encontrada');
				else {

					$sql->update($_p."parametros_tags","lixo=1","WHERE id='".$tag->id."'");

					$_tags=array();
					$sql->consult($_p."parametros_tags","*","WHERE lixo=0");
					while($x=mysqli_fetch_object($sql->mysqry)) {
						$x->titulo = utf8_encode($x->titulo);  
						$x->cor = utf8_encode($x->cor);
						$_tags[]=$x;
					}

					$rtn=array('success'=>true,'regs'=>$_tags);
				}
			}

			else if($_POST['ajax']=="enviarWhatsapp") {
				
				$agenda = $paciente = '';
				if(isset($_POST['id_agenda']) and is_numeric($_POST['id_agenda'])) {
					$sql->consult($_p."agenda","id,id_paciente","where id=".$_POST['id_agenda']);
					if($sql->rows) {
						$agenda = mysqli_fetch_object($sql->mysqry);
					}
				}

				$tipo='';
				if(isset($_POST['id_tipo']) and is_numeric($_POST['id_tipo'])) {
					$sql->consult($_p."whatsapp_mensagens_tipos","*","where id=".$_POST['id_tipo']);
					if($sql->rows) {
						$tipo = mysqli_fetch_object($sql->mysqry);
					}
				}

				$erro='';

				if(empty($agenda)) $erro='Agendamento não encontrado!';
				else if(empty($tipo)) $erro='Tipo não encontrado!';

				if(empty($erro)) {

					$attr=array('id_tipo'=>$tipo->id,
								'id_paciente'=>$agenda->id_paciente,
								'id_agenda'=>$agenda->id);

					if($infozap->adicionaNaFila($attr)) {
						$celular=$infozap->celular;
					} else {
						$erro=isset($infozap->erro)?$infozap->erro:'Algum erro ocorreu no envio. Tente novamente!';
					}
				}

				if(empty($erro)) {
					$rtn=array('success'=>true,'celular'=>mask($celular));
				} else {
					$rtn=array('success'=>false,'error'=>$erro);
				}
			}
		 
		# Especialidades
			else if($_POST['ajax']=="asEspecialidadesListar") {

				$regs=array();
				$sql->consult($_tableEspecialidades,"*","where lixo=0 order by titulo asc") ;
				while($x=mysqli_fetch_object($sql->mysqry)) {
					$regs[]=array('id'=>$x->id,
											'titulo'=>utf8_encode($x->titulo));
				}

				$rtn=array('success'=>true,
							'regs'=>$regs);
			} 

			else if($_POST['ajax']=="asEspecialidadesEditar") {

				$cnt='';
				if(isset($_POST['id']) and is_numeric($_POST['id'])) {
					$sql->consult($_tableEspecialidades,"*","where id='".addslashes($_POST['id'])."' and lixo=0");
					if($sql->rows) {
						$x=mysqli_fetch_object($sql->mysqry);
						$cnt=(object)array('id' =>$x->id,'titulo' =>utf8_encode($x->titulo));
					}
				}

				if(is_object($cnt)) {
					$rtn=array('success'=>true,
								'id'=>$cnt->id,
								'cnt'=>$cnt);
				} else {
					$rtn=array('success'=>false,'error'=>'Registro não encontrado!');
				}	
			} 

			else if($_POST['ajax']=="asEspecialidadesPersistir") {

				$cnt='';
				if(isset($_POST['id']) and is_numeric($_POST['id'])) {
					$sql->consult($_tableEspecialidades,"*","where id='".addslashes($_POST['id'])."' and lixo=0");
					if($sql->rows) {
						$cnt=mysqli_fetch_object($sql->mysqry);
					}
				}

				$titulo=isset($_POST['titulo'])?addslashes(utf8_decode($_POST['titulo'])):'';

				if(empty($titulo)) $rtn=array('success'=>false,'error'=>'Título não preenchido!');
				else {


					$vSQL="titulo='$titulo'";

					if(is_object($cnt)) {
						$vWHERE="where id=$cnt->id";
						//$vSQL.=",alteracao_data=now(),id_alteracao=$usr->id";
						$sql->update($_tableEspecialidades,$vSQL,$vWHERE);
						$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='update',vsql='".addslashes($vSQL)."',vwhere='".addslashes($vWHERE)."',tabela='".$_tableEspecialidades."',id_reg='$cnt->id'");
					} else {
						$vSQL.=",data=now(),id_usuario=$usr->id";
						$sql->add($_tableEspecialidades,$vSQL);
						$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='update',vsql='".addslashes($vSQL)."',vwhere='',tabela='".$_tableEspecialidades."',id_reg='$sql->ulid'");

					}

					$rtn=array('success'=>true);
				}
			} 

			else if($_POST['ajax']=="asEspecialidadesRemover") { 
				$cnt='';
				if(isset($_POST['id']) and is_numeric($_POST['id'])) {
					$sql->consult($_tableEspecialidades,"*","where id='".$_POST['id']."'");
					if($sql->rows) {
						$cnt=mysqli_fetch_object($sql->mysqry);
					}
				}

				if(is_object($cnt)) {
					$vSQL="lixo=$usr->id";
					$vWHERE="where id=$cnt->id";
					$sql->update($_tableEspecialidades,$vSQL,$vWHERE);
						$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='delete',vsql='".addslashes($vSQL)."',vwhere='".addslashes($vWHERE)."',tabela='".$_tableEspecialidades."',id_reg='$cnt->id'");

					$rtn=array('success'=>true);
				} else {
					$rtn=array('success'=>false,'error'=>'Registro não encontrado!');
				}
			}

		# Planos
			else if($_POST['ajax']=="asPlanosListar") {

				$regs=array();
				$sql->consult($_tablePlanos,"*","where lixo=0 order by titulo asc") ;
				while($x=mysqli_fetch_object($sql->mysqry)) {
					$regs[]=array('id'=>$x->id,'titulo'=>utf8_encode($x->titulo));
				}

				$rtn=array('success'=>true,
							'regs'=>$regs);	
			} 

			else if($_POST['ajax']=="asPlanosEditar") {

				$cnt='';
				if(isset($_POST['id']) and is_numeric($_POST['id'])) {
					$sql->consult($_tablePlanos,"*","where id='".addslashes($_POST['id'])."' and lixo=0");
					if($sql->rows) {
						$x=mysqli_fetch_object($sql->mysqry);
						$cnt=(object)array('id' =>$x->id,'titulo' =>utf8_encode($x->titulo),'fixo'=>$x->fixo);
					}
				}

				if(is_object($cnt)) {
					$rtn=array('success'=>true,
								'id'=>$cnt->id,
								'cnt'=>$cnt);
				} else {
					$rtn=array('success'=>false,'error'=>'Registro não encontrado!');
				}
				
			} 

			else if($_POST['ajax']=="asPlanosPersistir") {

				$cnt='';
				if(isset($_POST['id']) and is_numeric($_POST['id'])) {
					$sql->consult($_tablePlanos,"*","where id='".addslashes($_POST['id'])."' and lixo=0");
					if($sql->rows) {
						$cnt=mysqli_fetch_object($sql->mysqry);
					}
				}

				$titulo=isset($_POST['titulo'])?addslashes(utf8_decode($_POST['titulo'])):'';

				if(empty($titulo)) $rtn=array('success'=>false,'error'=>'Título não preenchido!');
				else {


					$vSQL="titulo='$titulo'";

					if(is_object($cnt)) {
						$vWHERE="where id=$cnt->id";
						//$vSQL.=",alteracao_data=now(),id_alteracao=$usr->id";
						$sql->update($_tablePlanos,$vSQL,$vWHERE);
						$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='update',vsql='".addslashes($vSQL)."',vwhere='".addslashes($vWHERE)."',tabela='".$_tablePlanos."',id_reg='$cnt->id'");
					} else {
						//$vSQL.=",data=now(),id_usuario=$usr->id";
						$sql->add($_tablePlanos,$vSQL);
						$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='update',vsql='".addslashes($vSQL)."',vwhere='',tabela='".$_tablePlanos."',id_reg='$sql->ulid'");

					}

					$rtn=array('success'=>true);
				}
			} 

			else if($_POST['ajax']=="asPlanosRemover") { 
				$cnt='';
				if(isset($_POST['id']) and is_numeric($_POST['id'])) {
					$sql->consult($_tablePlanos,"*","where id='".$_POST['id']."'");
					if($sql->rows) {
						$cnt=mysqli_fetch_object($sql->mysqry);
					}
				}

				if(is_object($cnt)) {
					$vSQL="lixo=$usr->id";
					$vWHERE="where id=$cnt->id";
					$sql->update($_tablePlanos,$vSQL,$vWHERE);
						$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='delete',vsql='".addslashes($vSQL)."',vwhere='".addslashes($vWHERE)."',tabela='".$_tablePlanos."',id_reg='$cnt->id'");

					$rtn=array('success'=>true);
				} else {
					$rtn=array('success'=>false,'error'=>'Registro não encontrado!');
				}
			}

		# Marcas
			else if($_POST['ajax']=="asMarcasListar") {

				$regs=array();
				$sql->consult($_tableMarcas,"*","where lixo=0 order by titulo asc") ;
				while($x=mysqli_fetch_object($sql->mysqry)) {
					$regs[]=array('id'=>$x->id,'titulo'=>utf8_encode($x->titulo));
				}

				$rtn=array('success'=>true,
							'regs'=>$regs);
				
			} 

			else if($_POST['ajax']=="asMarcasEditar") {

				$cnt='';
				if(isset($_POST['id']) and is_numeric($_POST['id'])) {
					$sql->consult($_tableMarcas,"*","where id='".addslashes($_POST['id'])."' and lixo=0");
					if($sql->rows) {
						$x=mysqli_fetch_object($sql->mysqry);
						$cnt=(object)array('id' =>$x->id,'titulo' =>utf8_encode($x->titulo));
					}
				}

				if(is_object($cnt)) {
					$rtn=array('success'=>true,
								'id'=>$cnt->id,
								'cnt'=>$cnt);
				} else {
					$rtn=array('success'=>false,'error'=>'Registro não encontrado!');
				}
				
			} 

			else if($_POST['ajax']=="asMarcasPersistir") {

				$cnt='';
				if(isset($_POST['id']) and is_numeric($_POST['id'])) {
					$sql->consult($_tableMarcas,"*","where id='".addslashes($_POST['id'])."' and lixo=0");
					if($sql->rows) {
						$cnt=mysqli_fetch_object($sql->mysqry);
					}
				}

				$titulo=isset($_POST['titulo'])?addslashes(utf8_decode($_POST['titulo'])):'';

				if(empty($titulo)) $rtn=array('success'=>false,'error'=>'Título não preenchido!');
				else {


					$vSQL="titulo='$titulo'";

					if(is_object($cnt)) {
						$vWHERE="where id=$cnt->id";
						//$vSQL.=",alteracao_data=now(),id_alteracao=$usr->id";
						$sql->update($_tableMarcas,$vSQL,$vWHERE);
						$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='update',vsql='".addslashes($vSQL)."',vwhere='".addslashes($vWHERE)."',tabela='".$_tableMarcas."',id_reg='$cnt->id'");
					} else {
						//$vSQL.=",data=now(),id_usuario=$usr->id";
						$sql->add($_tableMarcas,$vSQL);
						$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='update',vsql='".addslashes($vSQL)."',vwhere='',tabela='".$_tableMarcas."',id_reg='$sql->ulid'");

					}

					$rtn=array('success'=>true);
				}
			} 

			else if($_POST['ajax']=="asMarcasRemover") { 
				$cnt='';
				if(isset($_POST['id']) and is_numeric($_POST['id'])) {
					$sql->consult($_tableMarcas,"*","where id='".$_POST['id']."'");
					if($sql->rows) {
						$cnt=mysqli_fetch_object($sql->mysqry);
					}
				}

				if(is_object($cnt)) {
					$vSQL="lixo=$usr->id";
					$vWHERE="where id=$cnt->id";
					$sql->update($_tableMarcas,$vSQL,$vWHERE);
						$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='delete',vsql='".addslashes($vSQL)."',vwhere='".addslashes($vWHERE)."',tabela='".$_tableMarcas."',id_reg='$cnt->id'");

					$rtn=array('success'=>true);
				} else {
					$rtn=array('success'=>false,'error'=>'Registro não encontrado!');
				}
			}

		# Categorias
			else if($_POST['ajax']=="asCategoriasListar") {

				$regs=array();
				$sql->consult($_tableCategorias,"*","where lixo=0 order by titulo asc") ;
				while($x=mysqli_fetch_object($sql->mysqry)) {
					$regs[]=array('id'=>$x->id,'titulo'=>utf8_encode($x->titulo));
				}

				$rtn=array('success'=>true,
							'regs'=>$regs);
				
			} 

			else if($_POST['ajax']=="asCategoriasEditar") {

				$cnt='';
				if(isset($_POST['id']) and is_numeric($_POST['id'])) {
					$sql->consult($_tableCategorias,"*","where id='".addslashes($_POST['id'])."' and lixo=0");
					if($sql->rows) {
						$x=mysqli_fetch_object($sql->mysqry);
						$cnt=(object)array('id' =>$x->id,'titulo' =>utf8_encode($x->titulo));
					}
				}

				if(is_object($cnt)) {
					$rtn=array('success'=>true,
								'id'=>$cnt->id,
								'cnt'=>$cnt);
				} else {
					$rtn=array('success'=>false,'error'=>'Registro não encontrado!');
				}
			} 

			else if($_POST['ajax']=="asCategoriasPersistir") {

				$cnt='';
				if(isset($_POST['id']) and is_numeric($_POST['id'])) {
					$sql->consult($_tableCategorias,"*","where id='".addslashes($_POST['id'])."' and lixo=0");
					if($sql->rows) {
						$cnt=mysqli_fetch_object($sql->mysqry);
					}
				}

				$titulo=isset($_POST['titulo'])?addslashes(utf8_decode($_POST['titulo'])):'';

				if(empty($titulo)) $rtn=array('success'=>false,'error'=>'Título não preenchido!');
				else {
					$vSQL="titulo='$titulo'";

					if(is_object($cnt)) {
						$vWHERE="where id=$cnt->id";
						$sql->update($_tableCategorias,$vSQL,$vWHERE);
						$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='update',vsql='".addslashes($vSQL)."',vwhere='".addslashes($vWHERE)."',tabela='".$_tableCategorias."',id_reg='$cnt->id'");
					} else {
						$sql->add($_tableCategorias,$vSQL);
						$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='update',vsql='".addslashes($vSQL)."',vwhere='',tabela='".$_tableCategorias."',id_reg='$sql->ulid'");

					}

					$rtn=array('success'=>true);
				}
			} 

			else if($_POST['ajax']=="asCategoriasRemover") { 
				$cnt='';
				if(isset($_POST['id']) and is_numeric($_POST['id'])) {
					$sql->consult($_tableCategorias,"*","where id='".$_POST['id']."'");
					if($sql->rows) {
						$cnt=mysqli_fetch_object($sql->mysqry);
					}
				}

				if(is_object($cnt)) {
					$vSQL="lixo=$usr->id";
					$vWHERE="where id=$cnt->id";
					$sql->update($_tableCategorias,$vSQL,$vWHERE);
						$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='delete',vsql='".addslashes($vSQL)."',vwhere='".addslashes($vWHERE)."',tabela='".$_tableCategorias."',id_reg='$cnt->id'");

					$rtn=array('success'=>true);
				} else {
					$rtn=array('success'=>false,'error'=>'Registro não encontrado!');
				}
			}

		# Pacientes
			else if($_POST['ajax']=="asPacientePersistir") {


				$nome=isset($_POST['nome'])?addslashes(utf8_decode($_POST['nome'])):'';
				$cpf=isset($_POST['cpf'])?addslashes(telefone($_POST['cpf'])):'';
				$telefone1=isset($_POST['telefone1'])?addslashes(telefone($_POST['telefone1'])):'';
				$indicacao_tipo=isset($_POST['indicacao_tipo'])?addslashes(utf8_decode($_POST['indicacao_tipo'])):'';
				$indicacao=isset($_POST['indicacao'])?addslashes(utf8_decode($_POST['indicacao'])):'';

				if(empty($nome)) $rtn=array('success'=>false,'error'=>'Preencha o nome do Paciente!');
				else if(empty($telefone1)) $rtn=array('success'=>false,'error'=>'Preencha o whatsapp do Paciente!');
				else if(!empty($cpf) && strlen($cpf)!=11) $rtn=array('success'=>false,'error'=>'Digite um CPF válido!');
				else {

					$vSQL="nome='$nome',
							cpf='$cpf',
							telefone1='$telefone1',
							indicacao_tipo='$indicacao_tipo',
							indicacao='$indicacao',
							data=now(),
							id_usuario=$usr->id";


					$vSQL.=",periodicidade=6";


					$erro='';
					if(!empty($cpf)) {
						$where="where cpf = '".$cpf."' and lixo=0";
						$sql->consult($_tablePacientes,"id",$where);
						
						if($sql->rows) {
							$erro="Já existe paciente cadastrado com esse CPF!";
						}
					}

					if(empty($erro)) {

						$sql->add($_tablePacientes,$vSQL);
						$id_paciente=$sql->ulid;
						$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='update',vsql='".addslashes($vSQL)."',vwhere='',tabela='".$_tableMarcas."',id_reg='$id_paciente'");

					
						$rtn=array('success'=>true,
									'id_paciente'=>$id_paciente,
									'nome'=>utf8_encode($nome));
					} else {
						$rtn=array('success'=>false,'error'=>$erro);
					}
				}
			} 
			else if($_POST['ajax']=="biCategorizacao") {

				$adm = new Adm($_p);
				$adm->biCategorizacao();

				$rtn=array('success'=>true);
			}

		# Pacientes Relacionamento
			else if($_POST['ajax']=="asRelacionamentoPaciente") {

				$_profissionais=array();
				$sql->consult($_p."colaboradores","id,nome,calendario_iniciais,foto,calendario_cor,check_agendamento","where lixo=0 order by nome asc");
				while($x=mysqli_fetch_object($sql->mysqry)) $_profissionais[$x->id]=$x;

				$paciente = '';
				if(isset($_POST['id_paciente']) and is_numeric($_POST['id_paciente'])) {
					$sql->consult($_p."pacientes","id,nome,data_nascimento,telefone1,codigo_bi,musica,periodicidade,foto_cn","where id=".$_POST['id_paciente']);
					if($sql->rows) $paciente=mysqli_fetch_object($sql->mysqry);
				}


				if(is_object($paciente)) {

					if($paciente->data_nascimento!="0000-00-00") {
						$dob = new DateTime($paciente->data_nascimento);
						$now = new DateTime();
						$idade = $now->diff($dob)->y;
					} else $idade=0;
				

					$ultimoAgendamento='';
					$sql->consult($_p."agenda","*","where id_paciente=$paciente->id and id_status IN (5) and lixo=0 order by agenda_data desc limit 1");
					if($sql->rows) {
						$ultimoAgendamento=mysqli_fetch_object($sql->mysqry);

					}

					$_whatsappHistorico=array();
					$sqlwts=new Mysql(true);
					$sqlwts->consult($_p."whatsapp_mensagens","*","where id_paciente=$paciente->id and id_tipo=4 and lixo=0 order by data");
					if($sqlwts->rows) {
						while($x=mysqli_fetch_object($sqlwts->mysqry)) {
							$_whatsappHistorico[]=array('dt'=>date('d/m/Y H:i',strtotime($x->data)),
														'msg'=>nl2br($x->mensagem),
														'resposta_sim'=>$x->resposta_sim,
														'resposta_nao'=>$x->resposta_nao);
						}
					}
				

					$_historico=array();

					# Historico
						$_cadeiras=array();
						$sql->consult($_p."parametros_cadeiras","*","where lixo=0  order by titulo asc");
						while($x=mysqli_fetch_object($sql->mysqry)) $_cadeiras[$x->id]=$x;

						$_colaboradores=array();
						$sql->consult($_p."colaboradores","id,nome,calendario_iniciais,calendario_cor","");
						while($x=mysqli_fetch_object($sql->mysqry)) {
							$_colaboradores[$x->id]=$x;
						}

						$_historicoStatus=array();
						$sql->consult($_p."pacientes_historico_status","*","");
						while($x=mysqli_fetch_object($sql->mysqry)) {
							$_historicoStatus[$x->id]=$x;
						}

						$_agendaStatus=array();
						$sql->consult($_p."agenda_status","*","");
						while($x=mysqli_fetch_object($sql->mysqry)) {
							$_agendaStatus[$x->id]=$x;
						}

						$registrosHistorico=array();
						$agendasIds=array();
						$sql->consult($_p."pacientes_historico","*","where id_paciente=$paciente->id and lixo=0 order by data desc");
						while($x=mysqli_fetch_object($sql->mysqry)) {
							$registrosHistorico[]=$x;
							if($x->id_agenda>0) $agendasIds[$x->id_agenda]=$x->id_agenda;
						}

						$_agenda=array();
						if(count($agendasIds)>0) {
							$sql->consult($_p."agenda","id,id_cadeira,agenda_data,profissionais,id_status","where id IN (".implode(",",$agendasIds).") and lixo=0");
							if($sql->rows) {
								while ($x=mysqli_fetch_object($sql->mysqry)) {
									$_agenda[$x->id]=$x;
								}
							}
						}

						$_todosAgendamentos=array();
						$sql->consult($_p."agenda","id,id_cadeira,agenda_data,profissionais,id_status","where id_paciente=$paciente->id and lixo=0");
						while($x=mysqli_fetch_object($sql->mysqry)) {
							if(isset($agendasIds[$x->id])) continue;
							$_todosAgendamentos[]=$x;
						}

						

						$registrosComAgendamento=$registrosSemAgendamento=array();
						$agrupamentoAgenda=array();


						$historicoAgendamento=array();
						foreach($registrosHistorico as $x) {

							$arr = array();

							if($x->evento=="observacao") {
								$arr['ev']=$x->evento;
								$arr['dt']=date('d/m • H:i',strtotime($x->data));
								$arr['col']=isset($_colaboradores[$x->id_usuario])?utf8_encode($_colaboradores[$x->id_usuario]->nome):"Desconhecido";
								$arr['obs']=isset($_historicoStatus[$x->id_obs])?utf8_encode($_historicoStatus[$x->id_obs]->titulo):'';
								$arr['descricao']=utf8_encode($x->descricao);

								$index=strtotime($x->data);
								while(isset($_historico[$index])) {
									$index++;
								}
								$_historico[$index]=$arr;
							
							} else {
								if(isset($_agenda[$x->id_agenda])) {
									$agenda=$_agenda[$x->id_agenda];
									if(!isset($historicoAgendamento[$agenda->id])) {

										$icone="mdi:calendar-check";
										$iconeCor=isset($_agendaStatus[$agenda->id_status])?$_agendaStatus[$agenda->id_status]->cor:'';

										$cadeira=isset($_cadeiras[$agenda->id_cadeira])?utf8_encode($_cadeiras[$agenda->id_cadeira]->titulo):'-';

										$profissionaisIniciais=array();

										$profissionaisAux=explode(",",$agenda->profissionais);
										foreach($profissionaisAux as $idPro) {
											if(is_numeric($idPro) and isset($_colaboradores[$idPro])) {
												$col=$_colaboradores[$idPro];
												$profissionaisIniciais[]=array('iniciais'=>$col->calendario_iniciais,
																				'cor'=>$col->calendario_cor);
											}
										}



										$historicoAgendamento[$agenda->id]=array('ev'=>$x->evento,
																		'id'=>$agenda->id,
																		'dt2'=>$agenda->agenda_data,
																		'ic'=>$icone,
																		'icC'=>$iconeCor,
																		'dt'=>date('d/m/Y • H:i',strtotime($agenda->agenda_data)),
																		'cad'=>$cadeira,
																		'prof'=>$profissionaisIniciais);
									} 
									
									
								}
								
							}

							
						}

						foreach($_todosAgendamentos as $agenda) {
							$icone="mdi:calendar-check";
							$iconeCor=isset($_agendaStatus[$agenda->id_status])?$_agendaStatus[$agenda->id_status]->cor:'';

							$cadeira=isset($_cadeiras[$agenda->id_cadeira])?utf8_encode($_cadeiras[$agenda->id_cadeira]->titulo):'-';

							$profissionaisIniciais=array();

							$profissionaisAux=explode(",",$agenda->profissionais);
							foreach($profissionaisAux as $idPro) {
								if(is_numeric($idPro) and isset($_colaboradores[$idPro])) {
									$col=$_colaboradores[$idPro];
									$profissionaisIniciais[]=array('iniciais'=>$col->calendario_iniciais,
																	'cor'=>$col->calendario_cor);
								}
							}

							$historicoAgendamento[]=array('ev'=>'agendamento',
															'dt2'=>$agenda->agenda_data,
															'ic'=>$icone,
															'icC'=>$iconeCor,
															'dt'=>date('d/m/Y • H:i',strtotime($agenda->agenda_data)),
															'cad'=>$cadeira,
															'prof'=>$profissionaisIniciais);


						}

						foreach($historicoAgendamento as $id_agenda=>$x) {
							$index=strtotime($x['dt2']);
							while(isset($_historico[$index])) {
								$index++;
							}
							$_historico[$index]=$x;
						}

						krsort($_historico);

						$_historicoJSON=array();
						foreach($_historico as $k=>$v) {
							$_historicoJSON[]=$v;
						}
				

					$dias='';
					if(is_object($ultimoAgendamento)) {
						$dias=strtotime(date('Y-m-d H:i:s'))-strtotime($ultimoAgendamento->agenda_data);
						$dias/=60*60*24;
						$dias=round($dias);

						if($dias>30) {
							$dias/=30;
							$dias=ceil($dias);
							$dias.=$dias>1?" meses":"mês";
						} else {
							$dias.=" dia(s)";
						}
					}

					$ft='';
					if(!empty($paciente->foto_cn)) {
						$ft=$_cloudinaryURL.'c_thumb,w_100,h_100/'.$paciente->foto_cn;
					}

					$pacienteInfo=array('id'=>$paciente->id,
										'ft'=>$ft,
										'nome'=>addslashes(utf8_encode($paciente->nome)),
										'agendou_dias'=>$dias,
										'idade'=>(int)$idade,
										'telefone1'=>$paciente->telefone1,
										'musica'=>utf8_encode($paciente->musica),
										'periodicidade'=>isset($_pacientesPeriodicidade[$paciente->periodicidade])?$_pacientesPeriodicidade[$paciente->periodicidade]:$paciente->periodicidade,
										'statusBI'=>isset($_codigoBI[$paciente->codigo_bi])?utf8_encode($_codigoBI[$paciente->codigo_bi]):"",

										'historico'=>$_historicoJSON,
										'historicoWts'=>$_whatsappHistorico
								);

					$rtn=array('success'=>true,
								'paciente'=>$pacienteInfo);
				} else {
					$rtn=array('success'=>false,'error'=>'Paciente não encontrado!');
				}
			} 
			else if($_POST['ajax']=="asRelacionamentoPacienteExcluido") {

				$_profissionais=array();
				$sql->consult($_p."colaboradores","id,nome,calendario_iniciais,foto,calendario_cor,check_agendamento","where lixo=0 order by nome asc");
				while($x=mysqli_fetch_object($sql->mysqry)) $_profissionais[$x->id]=$x;

				$paciente = '';
				if(isset($_POST['id_paciente']) and is_numeric($_POST['id_paciente'])) {
					$sql->consult($_p."pacientes","id,nome,data_nascimento,telefone1,codigo_bi,musica,periodicidade,foto_cn","where id=".$_POST['id_paciente']);
					if($sql->rows) $paciente=mysqli_fetch_object($sql->mysqry);
				}


				if(is_object($paciente)) {

					if($paciente->data_nascimento!="0000-00-00") {
						$dob = new DateTime($paciente->data_nascimento);
						$now = new DateTime();
						$idade = $now->diff($dob)->y;
					} else $idade=0;
				

					$ultimoAgendamento='';
					$sql->consult($_p."agenda","*","where id_paciente=$paciente->id and id_status IN (5) and lixo=0 order by agenda_data desc limit 1");
					if($sql->rows) {
						$ultimoAgendamento=mysqli_fetch_object($sql->mysqry);

					}

					$excluidoMotivo=$excluidoData=$excluidoUsuario="";
					$sql->consult($_p."pacientes_excluidos","*","where id_paciente=$paciente->id and lixo=0 order by data desc limit 1");
					if($sql->rows) {
						$e=mysqli_fetch_object($sql->mysqry);
						$excluidoMotivo=utf8_encode($e->motivo);
						$excluidoData=date('d/m/Y H:d',strtotime($e->data));
						$excluidoUsuario="Desconhecido";
						$sql->consult($_p."colaboradores","id,nome","where id=$e->id_usuario");
						if($sql->rows) {
							$c=mysqli_fetch_object($sql->mysqry);
							$excluidoUsuario=utf8_encode($c->nome);
						}
					}
				
				

					$_historico=array();

					# Historico
						$_cadeiras=array();
						$sql->consult($_p."parametros_cadeiras","*","where lixo=0  order by titulo asc");
						while($x=mysqli_fetch_object($sql->mysqry)) $_cadeiras[$x->id]=$x;

						$_colaboradores=array();
						$sql->consult($_p."colaboradores","id,nome,calendario_iniciais,calendario_cor","");
						while($x=mysqli_fetch_object($sql->mysqry)) {
							$_colaboradores[$x->id]=$x;
						}

						$_historicoStatus=array();
						$sql->consult($_p."pacientes_historico_status","*","");
						while($x=mysqli_fetch_object($sql->mysqry)) {
							$_historicoStatus[$x->id]=$x;
						}

						$_agendaStatus=array();
						$sql->consult($_p."agenda_status","*","");
						while($x=mysqli_fetch_object($sql->mysqry)) {
							$_agendaStatus[$x->id]=$x;
						}

						$registrosHistorico=array();
						$agendasIds=array();
						$sql->consult($_p."pacientes_historico","*","where id_paciente=$paciente->id and lixo=0 order by data desc");
						while($x=mysqli_fetch_object($sql->mysqry)) {
							$registrosHistorico[]=$x;
							if($x->id_agenda>0) $agendasIds[$x->id_agenda]=$x->id_agenda;
						}

						$_agenda=array();
						if(count($agendasIds)>0) {
							$sql->consult($_p."agenda","id,id_cadeira,agenda_data,profissionais,id_status","where id IN (".implode(",",$agendasIds).") and lixo=0");
							if($sql->rows) {
								while ($x=mysqli_fetch_object($sql->mysqry)) {
									$_agenda[$x->id]=$x;
								}
							}
						}

						$_todosAgendamentos=array();
						$sql->consult($_p."agenda","id,id_cadeira,agenda_data,profissionais,id_status","where id_paciente=$paciente->id and lixo=0");
						while($x=mysqli_fetch_object($sql->mysqry)) {
							if(isset($agendasIds[$x->id])) continue;
							$_todosAgendamentos[]=$x;
						}

						

						$registrosComAgendamento=$registrosSemAgendamento=array();
						$agrupamentoAgenda=array();


						$historicoAgendamento=array();
						foreach($registrosHistorico as $x) {

							$arr = array();

							if($x->evento=="observacao") {
								$arr['ev']=$x->evento;
								$arr['dt']=date('d/m • H:i',strtotime($x->data));
								$arr['col']=isset($_colaboradores[$x->id_usuario])?utf8_encode($_colaboradores[$x->id_usuario]->nome):"Desconhecido";
								$arr['obs']=isset($_historicoStatus[$x->id_obs])?utf8_encode($_historicoStatus[$x->id_obs]->titulo):'';

								$index=strtotime($x->data);
								while(isset($_historico[$index])) {
									$index++;
								}
								$_historico[$index]=$arr;
							
							} else {
								if(isset($_agenda[$x->id_agenda])) {
									$agenda=$_agenda[$x->id_agenda];
									if(!isset($historicoAgendamento[$x->id_agenda])) {

										$icone="mdi:calendar-check";
										$iconeCor=isset($_agendaStatus[$agenda->id_status])?$_agendaStatus[$agenda->id_status]->cor:'';

										$cadeira=isset($_cadeiras[$agenda->id_cadeira])?utf8_encode($_cadeiras[$agenda->id_cadeira]->titulo):'-';

										$profissionaisIniciais=array();

										$profissionaisAux=explode(",",$agenda->profissionais);
										foreach($profissionaisAux as $idPro) {
											if(is_numeric($idPro) and isset($_colaboradores[$idPro])) {
												$col=$_colaboradores[$idPro];
												$profissionaisIniciais[]=array('iniciais'=>$col->calendario_iniciais,
																				'cor'=>$col->calendario_cor);
											}
										}



										$historicoAgendamento[]=array('ev'=>$x->evento,
																		'dt2'=>$agenda->agenda_data,
																		'ic'=>$icone,
																		'icC'=>$iconeCor,
																		'dt'=>date('d/m/Y • H:i',strtotime($agenda->agenda_data)),
																		'cad'=>$cadeira,
																		'prof'=>$profissionaisIniciais);
									} 
									
									
								}
								
							}

							
						}

						foreach($_todosAgendamentos as $agenda) {
							$icone="mdi:calendar-check";
							$iconeCor=isset($_agendaStatus[$agenda->id_status])?$_agendaStatus[$agenda->id_status]->cor:'';

							$cadeira=isset($_cadeiras[$agenda->id_cadeira])?utf8_encode($_cadeiras[$agenda->id_cadeira]->titulo):'-';

							$profissionaisIniciais=array();

							$profissionaisAux=explode(",",$agenda->profissionais);
							foreach($profissionaisAux as $idPro) {
								if(is_numeric($idPro) and isset($_colaboradores[$idPro])) {
									$col=$_colaboradores[$idPro];
									$profissionaisIniciais[]=array('iniciais'=>$col->calendario_iniciais,
																	'cor'=>$col->calendario_cor);
								}
							}

							$historicoAgendamento[]=array('ev'=>'agendamento',
															'dt2'=>$agenda->agenda_data,
															'ic'=>$icone,
															'icC'=>$iconeCor,
															'dt'=>date('d/m/Y • H:i',strtotime($agenda->agenda_data)),
															'cad'=>$cadeira,
															'prof'=>$profissionaisIniciais);


						}

						foreach($historicoAgendamento as $id_agenda=>$x) {
							$index=strtotime($x['dt2']);
							while(isset($_historico[$index])) {
								$index++;
							}
							$_historico[$index]=$x;
						}

						krsort($_historico);

						$_historicoJSON=array();
						foreach($_historico as $k=>$v) {
							$_historicoJSON[]=$v;
						}
				

					$dias='';
					if(is_object($ultimoAgendamento)) {
						$dias=strtotime(date('Y-m-d H:i:s'))-strtotime($ultimoAgendamento->agenda_data);
						$dias/=60*60*24;
						$dias=round($dias);

						if($dias>30) {
							$dias/=30;
							$dias=ceil($dias);
							$dias.=$dias>1?" meses":"mês";
						} else {
							$dias.=" dia(s)";
						}
					}

					$ft='';
					if(!empty($paciente->foto_cn)) {
						$ft=$_cloudinaryURL.'c_thumb,w_100,h_100/'.$paciente->foto_cn;
					}

					$pacienteInfo=array('id'=>$paciente->id,
										'ft'=>$ft,
										'excluidoData'=>$excluidoData,
										'excluidoUsuario'=>$excluidoUsuario,
										'excluidoMotivo'=>$excluidoMotivo,
										'nome'=>addslashes(utf8_encode($paciente->nome)),
										'agendou_dias'=>$dias,
										'idade'=>(int)$idade,
										'telefone1'=>$paciente->telefone1,
										'musica'=>utf8_encode($paciente->musica),
										'periodicidade'=>isset($_pacientesPeriodicidade[$paciente->periodicidade])?$_pacientesPeriodicidade[$paciente->periodicidade]:$paciente->periodicidade,
										'statusBI'=>isset($_codigoBI[$paciente->codigo_bi])?utf8_encode($_codigoBI[$paciente->codigo_bi]):"",

										'historico'=>$_historicoJSON
								);

					$rtn=array('success'=>true,
								'paciente'=>$pacienteInfo);
				} else {
					$rtn=array('success'=>false,'error'=>'Paciente não encontrado!');
				}
			} 
			else if($_POST['ajax']=="asRelacionamentoPacienteQueroAgendar") {
				$erro='';
				$paciente = '';
				if(isset($_POST['id_paciente']) and is_numeric($_POST['id_paciente'])) {
					$sql->consult($_p."pacientes","*","where id='".$_POST['id_paciente']."'");
					if($sql->rows) { 
						$paciente=mysqli_fetch_object($sql->mysqry);
					}
				}

				$id_agenda_origem = (isset($_POST['id_agenda_origem']) and is_numeric($_POST['id_agenda_origem']))?$_POST['id_agenda_origem']:0;
				
				$agendaData='';
				if(isset($_POST['agenda_data']) and !empty($_POST['agenda_data'])) {
					list($dia,$mes,$ano)=explode("/",$_POST['agenda_data']);
					if(checkdate($mes, $dia, $ano)) {
						$agendaData=$ano."-".$mes."-".$dia;

						if(isset($_POST['agenda_hora']) and !empty($_POST['agenda_hora']) and strlen($_POST['agenda_hora'])==5) {
							$agendaData.=" ".$_POST['agenda_hora'];
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

				$agenda_duracao='';
				if(isset($_POST['agenda_duracao']) and is_numeric($_POST['agenda_duracao'])) {
					$agenda_duracao=$_POST['agenda_duracao'];
				}

				$obs = (isset($_POST['obs']) and !empty($_POST['obs']))?addslashes(utf8_decode($_POST['obs'])):'';

				if(empty($paciente)) $erro='Paciente não encontrado';
				else if(empty($agendaData)) $erro='Data/horário não definidos';
				else if(empty($profissional)) $erro='Profissional não encontrado';
				else if(empty($cadeira)) $erro='Cadeira não encontrada!';
				else if(empty($agenda_duracao)) $erro='Duração não definido!';

				if(empty($erro)) {

					$agendaFinal=date('Y-m-d H:i:s',strtotime($agendaData." + $agenda_duracao minutes"));
					$idStatusNovo=1; // a confirmar

					$vSQL="id_status=$idStatusNovo,
							id_paciente=$paciente->id,
							agenda_data='".$agendaData."',
							agenda_data_original='".$agendaData."',
							agenda_duracao='".$agenda_duracao."',
							agenda_data_final='".$agendaFinal."',
							id_cadeira='".$cadeira->id."', 
							obs='".$obs."',
							data_atualizacao=now(),
							data=now(),
							id_usuario=$usr->id,
							profissionais=',$profissional->id,'";

					
					$sql->consult($_p."agenda","id","where id_paciente=$paciente->id and 
															agenda_data='".$agendaData."' and 
															agenda_duracao='".$agenda_duracao."' and
															id_cadeira='".$cadeira->id."' and 
															lixo=0");
					if($sql->rows==0) {
						$sql->add($_p."agenda",$vSQL);
						$id_agenda=$sql->ulid;

						if(isset($_POST['reagendar']) and $_POST['reagendar']==1) {
							$sql->consult($_p."agenda","id","WHERE id='".$id_agenda_origem."'");
							if($sql->rows) {
								$x=mysqli_fetch_object($sql->mysqry);
								$sql->update($_p."agenda","id_status=4,id_reagendamento='".$id_agenda."'","WHERE id='".$x->id."'");
							}
						}

						$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='insert',vsql='".addslashes($vSQL)."',tabela='".$_p."agenda',id_reg='".$id_agenda."'");

						$vSQLHistorico="data=now(),
											id_usuario=$usr->id,
											evento='agendaNovo',
											id_paciente=".$paciente->id.",
											id_agenda=$id_agenda,
											id_agenda_origem=$id_agenda_origem,
											id_status_antigo=0,
											id_status_novo=".$idStatusNovo;
						$sql->add($_p."pacientes_historico",$vSQLHistorico);
						
					}

					$rtn=array('success'=>true);
				} else {
					$rtn=array('success'=>false,'error'=>$erro);
				}
			}
			else if($_POST['ajax']=="asRelacionamentoPacienteNaoQueroAgendar") {
				$erro='';

				$paciente = '';
				if(isset($_POST['id_paciente']) and is_numeric($_POST['id_paciente'])) {
					$sql->consult($_p."pacientes","*","where id='".$_POST['id_paciente']."'");
					if($sql->rows) { 
						$paciente=mysqli_fetch_object($sql->mysqry);
					}
				}

				$status='';
				if(isset($_POST['id_status']) and is_numeric($_POST['id_status'])) {
					$sql->consult($_p."pacientes_historico_status","*","where id='".$_POST['id_status']."'");
					if($sql->rows) {
						$status=mysqli_fetch_object($sql->mysqry);
					}
				}

				$obs = isset($_POST['obs'])?addslashes(utf8_decode($_POST['obs'])):'';

				if(empty($paciente)) $erro='Paciente não encontrado!';
				else if(empty($status)) $erro='Selecione um Status';
				

				if(empty($erro)) {
					$vSQL="data=now(),
						evento='observacao',
						id_paciente=$paciente->id,
						id_agenda=0,
						id_obs=$status->id,
						descricao='".$obs."',
						id_usuario=$usr->id";

					$sql->add($_p."pacientes_historico",$vSQL);


					$rtn=array('success'=>true);
				} else {
					$rtn=array('success'=>false,'error'=>$erro);
				}
			}
			else if($_POST['ajax']=="asRelacionamentoPacienteHorarios") {
			
				$agenda = '';
				if(isset($_POST['id_agenda']) and is_numeric($_POST['id_agenda'])) {
					$sql->consult($_p."agenda","*","where id='".$_POST['id_agenda']."'");
					if($sql->rows) { 
						$agenda=mysqli_fetch_object($sql->mysqry);
					}
				}

				$data = '';
				if(isset($_POST['agenda_data']) and !empty($_POST['agenda_data'])) {
					list($dia,$mes,$ano)=explode("/",$_POST['agenda_data']);
					if(checkdate($mes, $dia, $ano)) { 
						$data="$ano-$mes-$dia";
						$dia=date('w',strtotime($data));
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

				$agenda_duracao = (isset($_POST['agenda_duracao']) and is_numeric($_POST['agenda_duracao']))?$_POST['agenda_duracao']:0;

				if(is_object($agenda) or empty($agenda)) {
					if(!empty($data)) {
						if(is_object($profissional)) {
							if(is_object($cadeira)) {

								// verifica se o profissional atende nesta cadeira
								$sql->consult($_p."profissionais_horarios","*","where id_profissional=$profissional->id and lixo=0");
								if($sql->rows) {
									while($x=mysqli_fetch_object($sql->mysqry)) {
										$cadeiraHorarios[$x->id_cadeira][$x->dia][]=$x;
									}
								}

								if(isset($cadeiraHorarios[$cadeira->id])) {

									if(isset($cadeiraHorarios[$cadeira->id][$dia])) {

										$dataInicio=$data." 07:00:00";	
										$dataFim=$data." 23:59:59";

										$dataInicio=$dataFim="";


										// retorna o horario mais cedo e mais tarde que a cadeira tem disponibilidade
										foreach($cadeiraHorarios[$cadeira->id][$dia] as $x) {
											$dtI=$data." ".$x->inicio;
											$dtF=$data." ".$x->fim;


											if(empty($dataInicio) and empty($dataFim)) {
												$dataInicio=$dtI;
												$dataFim=$dtF;
											} else {
												if(strtotime($dataInicio)>strtotime($dtI)) {
													$dataInicio=$dtI;
												}


												if(strtotime($dataFim)<strtotime($dtF)) {
													$dataFim=$dtF;
												}
											}
										}



										//echo $dataInicio." - $dataFim -> $tempo\n\n";
										$horariosDisponiveis=array();
										$di=$dataInicio;



										// fiz isso para checar de 1 em 1 minuto, mas na hora de rodar coloquei de 30 em 30 minutos
										/*do {
											$df=date('Y-m-d H:i:s',strtotime($di." + $agenda_duracao minutes"));

											echo date('H:i',strtotime($di))." - ".date('H:i',strtotime($df))."\n";


											$di=date('Y-m-d H:i:s',strtotime($di." + 1 minutes"));
										} while(strtotime($df)<strtotime($dataFim));
										die();*/



										do {
											
											$df=date('Y-m-d H:i:s',strtotime($di." + $agenda_duracao minutes"));

											
											// nova condicao de intersessao captada na internet
											$where="WHERE (agenda_data>='$data 00:00:00' and agenda_data<='$data 23:59:59') and 
															(DATE_ADD(agenda_data, INTERVAL agenda_duracao MINUTE)>'$di' and agenda_data<'$df')";
											//$where="WHERE (agenda_data>='$data 00:00:00' and agenda_data<='$data 23:59:59')";
											$where.=" and profissionais like '%,$profissional->id,%' and id_status NOT IN (3,4) and lixo=0";
											$sql->consult($_p."agenda","agenda_data,DATE_ADD(agenda_data, INTERVAL $agenda_duracao MINUTE) as agenda_data_fim,agenda_duracao",$where);
											//echo $where."->".$sql->rows."\n";
											//$x=mysqli_fetch_object($sql->mysqry);
											//echo $x->agenda_data." - ".$x->agenda_data_fim." -> ".$x->agenda_duracao;die();
											if($sql->rows==0) {
												/*$where="WHERE (agenda_data>='$data 00:00:00' and agenda_data<='$data 23:59:59') and 
															(
																('$di'<=agenda_data && '$df'>=DATE_ADD(agenda_data, INTERVAL agenda_duracao MINUTE)) or 
																('$di'>=agenda_data && '$df'<=DATE_ADD(agenda_data, INTERVAL agenda_duracao MINUTE)) or 
																('$di'<=agenda_data && '$df'>agenda_data && '$df'<=DATE_ADD(agenda_data, INTERVAL agenda_duracao MINUTE))
															)";

												$where.=" and id_cadeira=$cadeira->id and id_status NOT IN (3,4) and lixo=0";
												$sql->consult($_p."agenda","agenda_data,DATE_ADD(agenda_data, INTERVAL $agenda_duracao MINUTE) as agenda_data_fim,agenda_duracao",$where);
												//echo $where."->".$sql->rows."\n";
												if($sql->rows==0) {*/
													$horariosDisponiveis[]=date('H:i',strtotime($di));
												//}
											}


											$di=date('Y-m-d H:i:s',strtotime($di." + 30 minutes")); // de 30 em 30 minutos mas pode mexer 
										} while(strtotime($df)<strtotime($dataFim));//while(strtotime($dataInicio)<strtotime($dataFim));

										//var_dump($horariosDisponiveis);die();

										$horarios = new Horarios(array('prefixo'=>$_p));

										$horariosDisponiveisNew=array();

										// remove horarios que a cadeira nao tem disponibilidade
										foreach($horariosDisponiveis as $v) {

											$strData = date('Y-m-d')." $v:00";
											$dtAux = date('Y-m-d H:i',strtotime($strData));

											$attr=array('id_colaborador'=>$profissional->id,
														'id_cadeira'=>$cadeira->id,
														'id_horario'=>0,
														'diaSemana'=>$dia,
														'inputHoraInicio'=>$v,
														'inputHoraFim'=>date('H:i',strtotime($dtAux." + $agenda_duracao minutes")));

											
											if(!$horarios->cadeiraHorariosIntercecao($attr)===true) {
												$horariosDisponiveisNew[]=$v; 
											} 
										}

										
										
										$rtn=array('success'=>true,'horariosDisponiveis'=>$horariosDisponiveisNew);
									} else {
										$rtn=array('success'=>false,'error'=>'Sem atendimento para este dia');
									}
								} else {
									$rtn=array('success'=>false,'error'=>'Sem atendimento para este consultório');
								}


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
			} 
			else if($_POST['ajax']=="asRelacionamentoPacienteEnviarWhatsapp") {
				$paciente = '';
				if(isset($_POST['id_paciente']) and is_numeric($_POST['id_paciente'])) {
					$sql->consult($_p."pacientes","*","where id='".$_POST['id_paciente']."'");
					if($sql->rows) { 
						$paciente=mysqli_fetch_object($sql->mysqry);
					}
				}	

				$wtsMsg = '';
				$sql->consult($_p."whatsapp_mensagens_tipos","*","where id=4 and lixo=0");
				if($sql->rows) {
					$wtsMsg=mysqli_fetch_object($sql->mysqry);
				}

				$erro='';
				if(empty($paciente)) $erro='Paciente não encontrado!';
				else if(empty($wtsMsg)) $erro='Mensagem <b>Relacionamento Gestão de Tempo</b> não configurado';
				else if(is_object($wtsMsg) and $wtsMsg->pub==0) $erro='Mensagem <b>Relacionamento Gestão de Tempo</b> desativada';
				else if(empty($_wts)) $erro='Whatsapp desconectado!';
				else {




					$attr=array('id_tipo'=>4,
								'id_paciente'=>$paciente->id);
					if($infozap->adicionaNaFila($attr)) {

						

						$rtn=array('success'=>true);
					} else {
						$rtn=array('success'=>false,'error'=>$infozap->erro);
					}

				}

				if(!empty($erro)) {
					$rtn=array('success'=>false,'error'=>$erro);
				}
			}
			else if($_POST['ajax']=="asRelacionamentoPacienteDisparaWhatsapp") {
				if($infozap->dispara()) {

					$rtn=array('success'=>true);
				} else {
					$rtn=array('success'=>false,'error'=>$infozap->erro); 
				}
			}
			else if($_POST['ajax']=="asRelacionamentoPacienteExcluir") {
				$erro='';

				$paciente = '';
				if(isset($_POST['id_paciente']) and is_numeric($_POST['id_paciente'])) {
					$sql->consult($_p."pacientes","*","where id='".$_POST['id_paciente']."'");
					if($sql->rows) { 
						$paciente=mysqli_fetch_object($sql->mysqry);
					}
				}

				$motivo = isset($_POST['motivo'])?addslashes(utf8_decode($_POST['motivo'])):'';

				if(empty($paciente)) $erro='Paciente não encontrado!';
				else if(empty($motivo)) $erro='Selecione um Motivo';
				

				if(empty($erro)) {



					$sql->consult($_p."pacientes_excluidos","*","where id_paciente=$paciente->id and lixo=0");
					if($sql->rows) {
						$rtn=array('success'=>false,'error'=>'Este paciente já está excluído');
					}  else {

						$vSQL="data=now(),
								id_paciente=$paciente->id,
								motivo='".$motivo."',
								id_usuario=$usr->id";

						$sql->add($_p."pacientes_excluidos",$vSQL);


						$rtn=array('success'=>true);
					}
				} else {
					$rtn=array('success'=>false,'error'=>$erro);
				}
			}
			else if($_POST['ajax']=="asRelacionamentoPacienteRemoverExcluidos") {
				$erro='';

				$paciente = '';
				if(isset($_POST['id_paciente']) and is_numeric($_POST['id_paciente'])) {
					$sql->consult($_p."pacientes","*","where id='".$_POST['id_paciente']."'");
					if($sql->rows) { 
						$paciente=mysqli_fetch_object($sql->mysqry);
					}
				}


				if(empty($paciente)) $erro='Paciente não encontrado!';

				if(empty($erro)) {



					$sql->consult($_p."pacientes_excluidos","*","where id_paciente=$paciente->id and lixo=0 order by data desc limit 1");
					if($sql->rows) {
						$e=mysqli_fetch_object($sql->mysqry);

						$sql->update($_p."pacientes_excluidos","lixo=1,lixo_data=now(),lixo_id_usuario=$usr->id","where id=$e->id");

						$rtn=array('success'=>true);
					}  else {

						$rtn=array('success'=>false,'error'=>'Este paciente não está na lista de excluídos');
					}
				} else {
					$rtn=array('success'=>false,'error'=>$erro);
				}
			}
		
		# Profissoes
			else if($_POST['ajax']=="asProfissoesListar") {

				$regs=array();
				$sql->consult($_tableProfissoes,"*","where lixo=0 order by titulo asc") ;
				while($x=mysqli_fetch_object($sql->mysqry)) {
					$regs[]=array('id'=>$x->id,'titulo'=>utf8_encode($x->titulo));
				}

				$rtn=array('success'=>true,
							'regs'=>$regs);
				
			} 

			else if($_POST['ajax']=="asProfissoesEditar") {

				$cnt='';
				if(isset($_POST['id']) and is_numeric($_POST['id'])) {
					$sql->consult($_tableProfissoes,"*","where id='".addslashes($_POST['id'])."' and lixo=0");
					if($sql->rows) {
						$x=mysqli_fetch_object($sql->mysqry);
						$cnt=(object)array('id' =>$x->id,'titulo' =>utf8_encode($x->titulo));
					}
				}

				if(is_object($cnt)) {
					$rtn=array('success'=>true,
								'id'=>$cnt->id,
								'cnt'=>$cnt);
				} else {
					$rtn=array('success'=>false,'error'=>'Registro não encontrado!');
				}
				
			} 

			else if($_POST['ajax']=="asProfissoesPersistir") {

				$cnt='';
				if(isset($_POST['id']) and is_numeric($_POST['id'])) {
					$sql->consult($_tableProfissoes,"*","where id='".addslashes($_POST['id'])."' and lixo=0");
					if($sql->rows) {
						$cnt=mysqli_fetch_object($sql->mysqry);
					}
				}

				$titulo=isset($_POST['titulo'])?addslashes(utf8_decode(strtoupperWLIB($_POST['titulo']))):'';

				if(empty($titulo)) $rtn=array('success'=>false,'error'=>'Título não preenchido!');
				else {


					$vSQL="titulo='$titulo'";

					if(is_object($cnt)) {
						$vWHERE="where id=$cnt->id";
						//$vSQL.=",alteracao_data=now(),id_alteracao=$usr->id";
						$sql->update($_tableProfissoes,$vSQL,$vWHERE);
						$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='update',vsql='".addslashes($vSQL)."',vwhere='".addslashes($vWHERE)."',tabela='".$_tableProfissoes."',id_reg='$cnt->id'");
					} else {
						//$vSQL.=",data=now(),id_usuario=$usr->id";
						$sql->add($_tableProfissoes,$vSQL);
						$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='update',vsql='".addslashes($vSQL)."',vwhere='',tabela='".$_tableProfissoes."',id_reg='$sql->ulid'");

					}

					$rtn=array('success'=>true);
				}
			} 

			else if($_POST['ajax']=="asProfissoesRemover") { 
				$cnt='';
				if(isset($_POST['id']) and is_numeric($_POST['id'])) {
					$sql->consult($_tableProfissoes,"*","where id='".$_POST['id']."'");
					if($sql->rows) {
						$cnt=mysqli_fetch_object($sql->mysqry);
					}
				}

				if(is_object($cnt)) {
					$vSQL="lixo=$usr->id";
					$vWHERE="where id=$cnt->id";
					$sql->update($_tableProfissoes,$vSQL,$vWHERE);
						$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='delete',vsql='".addslashes($vSQL)."',vwhere='".addslashes($vWHERE)."',tabela='".$_tableProfissoes."',id_reg='$cnt->id'");

					$rtn=array('success'=>true);
				} else {
					$rtn=array('success'=>false,'error'=>'Registro não encontrado!');
				}
			}

		# Proxima Consulta
			else if($_POST['ajax']=="proximaConsulta") {

				$_profissionais=array();
				$sql->consult($_p."colaboradores","id,nome,calendario_iniciais,foto,calendario_cor,check_agendamento","where lixo=0 order by nome asc");
				while($x=mysqli_fetch_object($sql->mysqry)) $_profissionais[$x->id]=$x;

				$_cadeiras=array();
				$sql->consult($_p."parametros_cadeiras","*","where lixo=0  order by titulo asc");
				while($x=mysqli_fetch_object($sql->mysqry)) $_cadeiras[$x->id]=$x;

				$agenda = $paciente = '';
				if(isset($_POST['id_agenda']) and is_numeric($_POST['id_agenda'])) {
					$sql->consult($_p."agenda","*","where id=".$_POST['id_agenda']." and lixo=0");
					if($sql->rows) {
						$agenda=mysqli_fetch_object($sql->mysqry);

						$sql->consult($_p."pacientes","*","where id=$agenda->id_paciente and lixo=0");
						if($sql->rows) {
							$paciente=mysqli_fetch_object($sql->mysqry);
						}
					}
				}

				if(is_object($agenda)) {
					if(is_object($paciente)) {

						if($paciente->data_nascimento!="0000-00-00") {
							$dob = new DateTime($paciente->data_nascimento);
							$now = new DateTime();
							$idade = $now->diff($dob)->y;
						} else $idade=0;

						/*$ft='';
						if(!empty($paciente->foto_cn)) {
							$ft=$_cloudinaryURL.'c_thumb,w_100,h_100/'.$paciente->foto_cn;
						}*/

						$ft='';
						if(!empty($paciente->foto_cn)) {
							$ft=$_cloudinaryURL.'c_thumb,w_100,h_100/'.$paciente->foto_cn;
						} else if(!empty($paciente->foto)) {
							$ft=$_wasabiURL."arqs/clientes/".$paciente->id.".jpg";
						}


						$agendamentosFuturos=array();

					

						$_pacientesAgendamentos=array();
						$sql->consult($_p."agenda","*","where id_paciente=$paciente->id and agenda_data>'".date('Y-m-d')."' and id_status IN (1,2) and lixo=0 order by agenda_data");

						while($x=mysqli_fetch_object($sql->mysqry)) {

							// se for o mesmo agendamento que esta sendo editado
							if($x->id==$agenda->id) continue;

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
																				'obs'=>str_replace("'","`",utf8_encode($x->obs)),
																				'data'=>date('d/m/Y H:i',strtotime($x->agenda_data)),
																				'initDate'=>date('d/m/Y',strtotime($x->agenda_data)),
																				'cadeira'=>isset($_cadeiras[$x->id_cadeira])?utf8_encode($_cadeiras[$x->id_cadeira]->titulo):'',
																				'profissionais'=>$profissionais);
						}

					

						if(isset($_pacientesAgendamentos[$paciente->id])) {
							$agendamentosFuturos=$_pacientesAgendamentos[$paciente->id];
						}
						

						$idProfissional=0;
						if(!empty($agenda->profissionais)) {

							$aux = explode(",",$agenda->profissionais);

							foreach($aux as $x) {
								if(!empty($x) and is_numeric($x)) {
									$idProfissional=$x;
									break;
								}
							}
						}


						$rtn=array('success'=>true,
									'data'=>array('id_paciente'=>$paciente->id,
													'periodicidade_select'=>$paciente->periodicidade,
													'nome'=>utf8_encode($paciente->nome),
													'idade'=>$idade,
													'telefone1'=>$paciente->telefone1,
													'statusBI'=>isset($_codigoBI[$paciente->codigo_bi])?utf8_encode($_codigoBI[$paciente->codigo_bi]):"",			
													'musica'=>utf8_encode($paciente->musica),
													'ft'=>$ft,
													'periodicidade'=>isset($_pacientesPeriodicidade[$paciente->periodicidade])?$_pacientesPeriodicidade[$paciente->periodicidade]:$paciente->periodicidade,
													'agendamentosFuturos'=>$agendamentosFuturos,
													'id_profissional'=>$idProfissional

													)
									);

					} else {
						$rtn=array('success'=>false,'error'=>'Paciente não encontrado');
					} 
				} else {
					$rtn=array('success'=>false,'error'=>'Agendamento não encontrado');
				}
			}
			else if($_POST['ajax']=="proximaConsultaPersistir") {
				$paciente = '';
				if(isset($_POST['id_paciente']) and is_numeric($_POST['id_paciente'])) {
					$sql->consult($_p."pacientes","id","where id=".$_POST['id_paciente']);
					if($sql->rows) {
						$paciente=mysqli_fetch_object($sql->mysqry);
					}
				}

				if(is_object($paciente)) {

					$duracao=isset($_POST['duracao'])?addslashes($_POST['duracao']):'';
					$laboratorio=isset($_POST['laboratorio'])?addslashes($_POST['laboratorio']):'';
					$imagem=isset($_POST['imagem'])?addslashes($_POST['imagem']):'';
					$retorno=isset($_POST['retorno'])?addslashes($_POST['retorno']):'';
					$obs=isset($_POST['obs'])?utf8_decode(addslashes($_POST['obs'])):'';
					//$profissionaisAux=isset($_POST['profissionais'])?$_POST['profissionais']:'';
					$profissionais=isset($_POST['profissionais'])?$_POST['profissionais']:array();
					$id_agenda_origem=(isset($_POST['id_agenda_origem']) and is_numeric($_POST['id_agenda_origem']))?$_POST['id_agenda_origem']:0;


					/*$profissionais=array();
					if(!empty($profissionaisAux)) {
						$aux=explode(",",$profissionaisAux);

						foreach($aux as $x) {
							if(!empty($x) and is_numeric($x)) $profissionais[]=$x;
						}
					}*/

					if(count($profissionais)>0) $profissionais=",".implode(",",$profissionais).",";
					else $profissionais='';

					$vSQL="data=now(),
							id_colaborador=$usr->id,
							id_paciente=$paciente->id,
							duracao='$duracao',
							laboratorio='$laboratorio',
							imagem='$imagem',
							retorno='$retorno',
							obs='$obs',
							id_agenda_origem='$id_agenda_origem',
							profissionais='$profissionais'";


					$sql->add($_p."pacientes_proximasconsultas",$vSQL);

					$rtn=array('success'=>true);

				} else {
					$rtn=array('success'=>false,'error'=>'Paciente não encontrado');
				}
			}
			else if($_POST['ajax']=="prontuarioPersistir") {
			
				$paciente = '';
				if(isset($_POST['id_paciente']) and is_numeric($_POST['id_paciente'])) {
					$sql->consult($_p."pacientes","id","where id=".$_POST['id_paciente']);
					if($sql->rows) {
						$paciente=mysqli_fetch_object($sql->mysqry);
					}
				}


				if(is_object($paciente)) {

					$id_profissional=isset($_POST['id_profissional'])?addslashes($_POST['id_profissional']):0;
					$prontuario=isset($_POST['prontuario'])?addslashes($_POST['prontuario']):'';
					$dataProntuario='';
					if(isset($_POST['dataProntuario']) and !empty($_POST['dataProntuario'])) {

						$aux1 = @explode(" ",$_POST['dataProntuario']);
						$aux2 = @explode("/",$aux1[0]);

 
						if(checkdate($aux2[1], $aux2[0], $aux2[2])) {
							$dataProntuario=$aux2[2]."-".$aux2[1]."-".$aux2[0]." ".$aux1[1];
						}

					}

					if(empty($dataProntuario)) {
						$erro='Preencha uma data válida!';
					}
					else if($id_profissional==0) {
						$erro='Selecione o Profissional';
					} else if(empty($prontuario)) {
						$erro='Digite o prontuário';
					} 

					if(empty($erro)) {

						/*$vsql="data='".$dataProntuario."',id_usuario='".$id_profissional."',
								texto='".addslashes(utf8_decode($prontuario))."',
								id_paciente=$paciente->id";
						//echo $vsql;die();
						$sql->add($_p."pacientes_prontuarios",$vsql);
						$id_reg=$sql->ulid;
						$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='insert',vsql='".addslashes($vsql)."',vwhere='',tabela='".$_p."pacientes_prontuarios',id_reg='$id_reg'");*/

						// id_tipo = 9 -> geral
						$sql->consult($_p."pacientes_evolucoes","*","WHERE data > NOW() - INTERVAL 1 MINUTE and 
																								id_paciente=$paciente->id and
																								id_tipo=9 and  
																								id_usuario=$usr->id");	
						if($sql->rows) {
							$e=mysqli_fetch_object($sql->mysqry);
							$sql->update($_p."pacientes_evolucoes","id_profissional='$id_profissional'","where id=$e->id");
							$id_evolucao=$e->id;
						} else {
							$sql->add($_p."pacientes_evolucoes","data=now(),
																	id_tipo=9,
																	id_paciente=$paciente->id,
																	id_usuario=$usr->id,
																	id_profissional='".$id_profissional."'");
							$id_evolucao=$sql->ulid;
						}


						$geral='';
						$sql->consult($_p."pacientes_evolucoes_geral","*","where id_evolucao=$id_evolucao and lixo=0");
						if($sql->rows) {
							$geral=mysqli_fetch_object($sql->mysqry);
						}

						$vSQLGeral="id_evolucao=$id_evolucao,
									data='".$dataProntuario."',
									id_profissional='".$id_profissional."',
									texto='".addslashes(utf8_decode($prontuario))."',
									id_usuario=$usr->id";

						if(is_object($geral)) {
							$sql->update($_p."pacientes_evolucoes_geral",$vSQLGeral,"where id=$geral->id");
						} else {
							$sql->add($_p."pacientes_evolucoes_geral",$vSQLGeral);
						}

						$rtn=array('success'=>true);

					} else {
						$rtn=array('success'=>false,'error'=>$erro);
					}

				}  else {
					$rtn=array('success'=>false,'error'=>'Paciente não encontrado');
				}
			}
			else if($_POST['ajax']=="proximaConsultaAltaPeriodicidade") {
				$paciente = '';
				if(isset($_POST['id_paciente']) and is_numeric($_POST['id_paciente'])) {
					$sql->consult($_p."pacientes","id","where id=".$_POST['id_paciente']);
					if($sql->rows) {
						$paciente=mysqli_fetch_object($sql->mysqry);
					}
				}
				$periodicidade=(isset($_POST['periodicidade']) and is_numeric($_POST['periodicidade']))?$_POST['periodicidade']:0;
				$alta=(isset($_POST['alta']) and !empty($_POST['alta']))?$_POST['alta']:'';

				$id_agenda_origem=(isset($_POST['id_agenda_origem']) and is_numeric($_POST['id_agenda_origem']))?$_POST['id_agenda_origem']:0;

				if(is_object($paciente)) {

					if($periodicidade>0) {

						$vsql="periodicidade='$periodicidade',id_agenda_origem='$id_agenda_origem'";
						$vwhere="where id=$paciente->id";

						$sql->update($_p."pacientes",$vsql,$vwhere);
						$id_reg=$sql->ulid;
						$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='update',vsql='".addslashes($vsql)."',vwhere='".addslashes($vsql)."',tabela='".$_p."pacientes',id_reg='$id_reg'");


						// cria evolucao
						$sql->add($_p."pacientes_evolucoes","data=now(),
																	id_tipo=11,
																	id_paciente=$paciente->id,
																	id_usuario=$usr->id,
																	id_profissional='".$usr->id."'");
						$id_evolucao=$sql->ulid;



						$vSQL="id_evolucao=$id_evolucao,
									data=now(),
									id_profissional='".$usr->id."',
									texto='".addslashes(utf8_decode($alta))."',
									id_usuario=$usr->id";

						$sql->add($_p."pacientes_evolucoes_alta",$vSQL);
						

						$rtn=array('success'=>true);

					} else {
						$rtn=array('success'=>false,'error'=>'Periodicidade não definida');
					}
				} else {
					$rtn=array('success'=>false,'error'=>'Paciente não encontrado');
				}
			}

		# Quero Agenda
			else if($_POST['ajax']=="asideQueroAgendarPaciente") {

				$_profissionais=array();
				$sql->consult($_p."colaboradores","id,nome,calendario_iniciais,foto,calendario_cor,check_agendamento","where lixo=0 order by nome asc");
				while($x=mysqli_fetch_object($sql->mysqry)) $_profissionais[$x->id]=$x;

				$_cadeiras=array();
				$sql->consult($_p."parametros_cadeiras","*","where lixo=0  order by titulo asc");
				while($x=mysqli_fetch_object($sql->mysqry)) $_cadeiras[$x->id]=$x;

				$paciente = '';
				if(isset($_POST['id_paciente']) and is_numeric($_POST['id_paciente'])) {
					$sql->consult($_p."pacientes","*","where id=".$_POST['id_paciente']." and lixo=0");
					if($sql->rows) {
						$paciente=mysqli_fetch_object($sql->mysqry);
					}
				}



				
				if(is_object($paciente)) {


					$proximaConsulta=array();
					if(isset($_POST['id_proximaconsulta']) and is_numeric($_POST['id_proximaconsulta'])) {
						$id_proximaconsulta=$_POST['id_proximaconsulta'];
						$sql->consult($_p."pacientes_proximasconsultas","*","where id_paciente=$paciente->id and id=$id_proximaconsulta");
						if($sql->rows) {
							$p=mysqli_fetch_object($sql->mysqry);

							$id_profissional='';
							if(!empty($p->profissionais)) {
								$aux=explode(",",$p->profissionais);
								foreach($aux as $idP) {
									if(!empty($idP) and is_numeric($idP)) {
										$id_profissional=$idP;
										break;
									}
								}
							}
							$proximaConsulta=array('id_proximaconsulta'=>$p->id,
													'obs'=>utf8_encode(addslashes($p->obs)),
													'duracao'=>(int)$p->duracao,
													'id_profissional'=>(int)$id_profissional);
						}

					}

					if($paciente->data_nascimento!="0000-00-00") {
						$dob = new DateTime($paciente->data_nascimento);
						$now = new DateTime();
						$idade = $now->diff($dob)->y;
					} else $idade=0;

				

					$agendamentosFuturos=array();

					$_pacientesAgendamentos=array();
					$sql->consult($_p."agenda","*","where id_paciente=$paciente->id and agenda_data>'".date('Y-m-d')."' and id_status IN (1,2) and lixo=0 order by agenda_data");

					while($x=mysqli_fetch_object($sql->mysqry)) {

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
				

					$ft='img/ilustra-usuario.jpg';
					if(!empty($paciente->foto_cn)) {
						$ft=$_cloudinaryURL.'c_thumb,w_100,h_100/'.$paciente->foto_cn;
					} else if(!empty($paciente->foto)) {
						$ft=$_wasabiURL."arqs/clientes/".$paciente->id.".jpg";
					}

					$rtn=array('success'=>true,
								'data'=>array('id_paciente'=>$paciente->id,
												'periodicidade_select'=>$paciente->periodicidade,
												'nome'=>utf8_encode($paciente->nome),
												'idade'=>$idade,
												'telefone1'=>$paciente->telefone1,
												'statusBI'=>isset($_codigoBI[$paciente->codigo_bi])?utf8_encode($_codigoBI[$paciente->codigo_bi]):"",			
												'musica'=>utf8_encode($paciente->musica),
												'ft'=>$ft,
												'periodicidade'=>isset($_pacientesPeriodicidade[$paciente->periodicidade])?$_pacientesPeriodicidade[$paciente->periodicidade]:$paciente->periodicidade,
												'agendamentosFuturos'=>$agendamentosFuturos,
												'proximaConsulta'=>$proximaConsulta

												)
								);

				} else {
					$rtn=array('success'=>false,'error'=>'Paciente não encontrado');
				} 
			}
			else if($_POST['ajax']=="asideQueroAgendarAgendar") {
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

						if(isset($_POST['agenda_hora']) and !empty($_POST['agenda_hora']) and strlen($_POST['agenda_hora'])==5) {
							$agendaData.=" ".$_POST['agenda_hora'];
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

				$agenda_duracao='';
				if(isset($_POST['agenda_duracao']) and is_numeric($_POST['agenda_duracao'])) {
					$agenda_duracao=$_POST['agenda_duracao'];
				}

				$obs = (isset($_POST['obs']) and !empty($_POST['obs']))?addslashes(utf8_decode($_POST['obs'])):'';
				$id_proximaconsulta = (isset($_POST['id_proximaconsulta']) and is_numeric($_POST['id_proximaconsulta']))?utf8_decode($_POST['id_proximaconsulta']):0;

				if(empty($paciente)) $erro='Paciente não encontrado';
				else if(empty($agendaData)) $erro='Data/horário não definidos';
				else if(empty($profissional)) $erro='Profissional não encontrado';
				else if(empty($cadeira)) $erro='Cadeira não encontrada!';
				else if(empty($agenda_duracao)) $erro='Duração não definido!';

				if(empty($erro)) {

					$agendaFinal=date('Y-m-d H:i:s',strtotime($agendaData." + $agenda_duracao minutes"));
					$idStatusNovo=1; // a confirmar

					$vSQL="id_status=$idStatusNovo,
							id_paciente=$paciente->id,
							agenda_data='".$agendaData."',
							agenda_data_original='".$agendaData."',
							agenda_duracao='".$agenda_duracao."',
							agenda_data_final='".$agendaFinal."',
							id_cadeira='".$cadeira->id."',
							obs='".$obs."',
							data_atualizacao=now(),
							data=now(),
							id_usuario=$usr->id,
							profissionais=',$profissional->id,'";

					
					$sql->consult($_p."agenda","id","where id_paciente=$paciente->id and 
															agenda_data='".$agendaData."' and 
															agenda_duracao='".$agenda_duracao."' and
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

						// id_obs = 7 -> Agendado pela Tarefas Inteligentes
						$vSQL="data=now(),
							evento='observacao',
							id_paciente=$paciente->id,
							id_proximaconsulta=$id_proximaconsulta,
							id_agenda=0,
							id_obs=7,
							descricao='',
							id_usuario=$usr->id";

						$sql->add($_p."pacientes_historico",$vSQL);
						
					}



					$rtn=array('success'=>true);
				} else {
					$rtn=array('success'=>false,'error'=>$erro);
				}
			}

		# Quero Reagendar
			else if($_POST['ajax']=="asideQueroReagendarPaciente") {


				$_profissionais=array();
				$sql->consult($_p."colaboradores","id,nome,calendario_iniciais,foto,calendario_cor,check_agendamento","where lixo=0 order by nome asc");
				while($x=mysqli_fetch_object($sql->mysqry)) $_profissionais[$x->id]=$x;

				$_cadeiras=array();
				$sql->consult($_p."parametros_cadeiras","*","where lixo=0  order by titulo asc");
				while($x=mysqli_fetch_object($sql->mysqry)) $_cadeiras[$x->id]=$x;

				$agenda = $paciente = '';
				if(isset($_POST['id_agenda']) and is_numeric($_POST['id_agenda'])) {
					$sql->consult($_p."agenda","*","where id=".$_POST['id_agenda']." and lixo=0");
					if($sql->rows) {
						$agenda=mysqli_fetch_object($sql->mysqry);

						$sql->consult($_p."pacientes","*","where id=$agenda->id_paciente and lixo=0");
						if($sql->rows) {
							$paciente=mysqli_fetch_object($sql->mysqry);
						}
					}
				}

				if(is_object($agenda)) {

					if(is_object($paciente)) {

						if($paciente->data_nascimento!="0000-00-00") {
							$dob = new DateTime($paciente->data_nascimento);
							$now = new DateTime();
							$idade = $now->diff($dob)->y;
						} else $idade=0;

						$ft='img/ilustra-usuario.jpg';
						if(!empty($paciente->foto_cn)) {
							$ft=$_cloudinaryURL.'c_thumb,w_100,h_100/'.$paciente->foto_cn;
						} else if(!empty($paciente->foto)) {
							$ft=$_wasabiURL."arqs/clientes/".$paciente->id.".jpg";
						}

						$aux = explode(",",$agenda->profissionais);
						$profissionaisIDs=array();
						foreach($aux as $id_profissional) {
							if(!empty($id_profissional) and is_numeric($id_profissional)) {

								if(isset($_profissionais[$id_profissional])) {
									$profissionaisIDs[]=$id_profissional;
								}
							}

						}

						$rtn=array('success'=>true,
								   'data'=>array('id_agenda'=>$agenda->id,
								   				 'id_paciente'=>$paciente->id,
												 'nome'=>utf8_encode($paciente->nome),
												 'idade'=>$idade,
												 'telefone1'=>$paciente->telefone1,		
												 'musica'=>utf8_encode($paciente->musica),
												 'ft'=>$ft,
												 'agenda_duracao' => $agenda->agenda_duracao,
								   				 'id_cadeira' => $agenda->id_cadeira,
								   				 'profissionais' => $profissionaisIDs,
								   				 'obs' => utf8_encode($agenda->obs)));

					} else {
						$rtn=array('success'=>false,'error'=>'Paciente não encontrado');
					} 

				} else {
					$rtn=array('success'=>false,'error'=>'Agendamento não encontrado');
				}

			}
	
		# Lista Personalizada
			else if($_POST['ajax']=="asListaPersonalizadaAtualizar") {

				$regs=array();
				$sql->consult($_tableListaPersonalizada,"*","where lixo=0 order by titulo asc") ;
				while($x=mysqli_fetch_object($sql->mysqry)) {
					$regs[]=array('id'=>$x->id,'titulo'=>utf8_encode($x->titulo));
				}

				$rtn=array('success'=>true,
							'regs'=>$regs);
				
			} 

			else if($_POST['ajax']=="asListaPersonalizadaEditar") {

				$cnt='';
				if(isset($_POST['id']) and is_numeric($_POST['id'])) {
					$sql->consult($_tableListaPersonalizada,"*","where id='".addslashes($_POST['id'])."' and lixo=0");
					if($sql->rows) {
						$x=mysqli_fetch_object($sql->mysqry);
						$cnt=(object)array('id' =>$x->id,'titulo' =>utf8_encode($x->titulo));
					}
				}

				if(is_object($cnt)) {
					$rtn=array('success'=>true,
								'id'=>$cnt->id,
								'cnt'=>$cnt);
				} else {
					$rtn=array('success'=>false,'error'=>'Registro não encontrado!');
				}
				
			} 

			else if($_POST['ajax']=="asListaPersonalizadaPersistir") {

				$cnt='';
				if(isset($_POST['id']) and is_numeric($_POST['id'])) {
					$sql->consult($_tableListaPersonalizada,"*","where id='".addslashes($_POST['id'])."' and lixo=0");
					if($sql->rows) {
						$cnt=mysqli_fetch_object($sql->mysqry);
					}
				}

				$titulo=isset($_POST['titulo'])?addslashes(utf8_decode(strtoupperWLIB($_POST['titulo']))):'';

				if(empty($titulo)) $rtn=array('success'=>false,'error'=>'Título não preenchido!');
				else {


					$vSQL="titulo='$titulo'";

					if(is_object($cnt)) {
						$vWHERE="where id=$cnt->id";
						//$vSQL.=",alteracao_data=now(),id_alteracao=$usr->id";
						$sql->update($_tableListaPersonalizada,$vSQL,$vWHERE);
						$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='update',vsql='".addslashes($vSQL)."',vwhere='".addslashes($vWHERE)."',tabela='".$_tableListaPersonalizada."',id_reg='$cnt->id'");
					} else {
						//$vSQL.=",data=now(),id_usuario=$usr->id";
						$sql->add($_tableListaPersonalizada,$vSQL);
						$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='update',vsql='".addslashes($vSQL)."',vwhere='',tabela='".$_tableListaPersonalizada."',id_reg='$sql->ulid'");

					}

					$rtn=array('success'=>true);
				}
			} 

			else if($_POST['ajax']=="asListaPersonalizadaRemover") { 
				$cnt='';
				if(isset($_POST['id']) and is_numeric($_POST['id'])) {
					$sql->consult($_tableListaPersonalizada,"*","where id='".$_POST['id']."'");
					if($sql->rows) {
						$cnt=mysqli_fetch_object($sql->mysqry);
					}
				}

				if(is_object($cnt)) {
					$vSQL="lixo=$usr->id";
					$vWHERE="where id=$cnt->id";
					$sql->update($_tableListaPersonalizada,$vSQL,$vWHERE);
						$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='delete',vsql='".addslashes($vSQL)."',vwhere='".addslashes($vWHERE)."',tabela='".$_tableListaPersonalizada."',id_reg='$cnt->id'");

					$rtn=array('success'=>true);
				} else {
					$rtn=array('success'=>false,'error'=>'Registro não encontrado!');
				}
			}

		# Tags
			else if($_POST['ajax']=="asTagPersistir") {
				$tag='';
				if(isset($_POST['id']) and is_numeric($_POST['id']) and $_POST['id']>0) {
					$sql->consult($_tableTags,"*","where id='".addslashes($_POST['id'])."'");
					if($sql->rows) {
						$tag=mysqli_fetch_object($sql->mysqry);
					}
				}

				$titulo=isset($_POST['titulo'])?addslashes(utf8_decode($_POST['titulo'])):'';
				$cor=isset($_POST['cor'])?addslashes($_POST['cor']):'';

				if(empty($agenda)) $rtn=array('success'=>false,'error'=>'Agendamento não encontrado');
				if(empty($titulo)) $rtn=array('success'=>false,'error'=>'Preencha o título da Tag!');
				else {

					$vSQL="titulo='$titulo',cor='$cor'";

					if(is_object($tag)) {
						$vWHERE="WHERE id=$tag->id";
						$sql->update($_tableTags, $vSQL,$vWHERE);
						
						$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='update',vsql='".addslashes($vSQL)."',vwhere='".addslashes($vWHERE)."',tabela='".$_tableTags."',id_reg='$tag->id'");
					} else {
						$sql->add($_tableTags,$vSQL);
						$id_tag=$sql->ulid;
						
						$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='insert',vsql='".addslashes($vSQL)."',vwhere='',tabela='".$_tableTags."',id_reg='$id_tag'");
					}

					$_tags=array();
					$sql->consult($_p."parametros_tags","*","WHERE lixo=0 order by titulo asc");
					while($x=mysqli_fetch_object($sql->mysqry)) {
						$_tags[]=array('id'=>$x->id,'titulo'=>utf8_encode($x->titulo));
					}

					$tags_selected=[];
					$tags_selected[]=$id_tag;


					if(isset($_POST['id_agenda']) and is_numeric($_POST['id_agenda'])) {
						$sql->consult($_p."agenda","tags","where id=".$_POST['id_agenda']);
						if($sql->rows) {
							$agenda=mysqli_fetch_object($sql->mysqry);

							if(!empty($agenda->tags)) {
								$aux=explode(",",$agenda->tags);
								foreach($aux as $idTag) {
									if(is_numeric($idTag)) $tags_selected[]=(int)$idTag;
								}
							}
						}
					}


					$rtn=array('success'=>true,
								'tags_selected'=>$tags_selected,
								'tags'=>$_tags);
				}

			}

		header("Content-type: application/json");
		echo json_encode($rtn);
		die();
	} else if(isset($_GET['ajaxApiAside'])) {


		$dir="../../";
		require_once("../../lib/conf.php");
		require_once("../../usuarios/checa.php");

		$rtn=[];

		if($_GET['ajaxApiAside']=='buscaIndicacao') {
			$where="WHERE 1=2";


			if(isset($_GET['indicacaoTipo']) and $_GET['indicacaoTipo']=="INDICACAO") {
				if(isset($_GET['search']) and !empty($_GET['search'])) {
					$aux = explode(" ",$_GET['search']);

					$wh="";
					$primeiraLetra='';
					foreach($aux as $v) {
						if(empty($v)) continue;

						if(empty($primeiraLetra)) $primeiraLetra=substr($v,0,1);
						$wh.="titulo REGEXP '$v' and ";
					}
					$wh=substr($wh,0,strlen($wh)-5);
					$where="where (($wh) or titulo like '%".$_GET['search']."%') and lixo=0";
				}
				if(!empty($primeiraLetra)) $where.=" ORDER BY CASE WHEN titulo >= '$primeiraLetra' THEN 1 ELSE 0 END DESC, titulo ASC";
				else $where.=" order by titulo asc";

				$table=$_p."parametros_indicacoes";
				$fields="titulo,id";
				$field="titulo";
			} else {
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

				if($_GET['indicacaoTipo']=="PACIENTE") {
					$table=$_p."pacientes";
					$fields="nome,id,telefone1,cpf,foto_cn,foto";
				} else {
					$table=$_p."colaboradores";
					$fields="nome,id,telefone1,cpf";
				}
				$field="nome";
			}

			$sql->consult($table,$fields,$where);
			//echo $table." ".$fields." ".$where;
			while($x=mysqli_fetch_object($sql->mysqry)) {

				$rtn['items'][]=array('id'=>$x->id,
										'text'=>utf8_encode($x->$field),
										'nome'=>utf8_encode($x->$field));
			}
		}


		header("Content-type: application/json");

		echo json_encode($rtn);
		die();
	}

	# JS All Asides
?>
	<script type="text/javascript" src="js/aside.funcoes.js"></script>
<?php

	# Asides

		// Agenda
			if(isset($apiConfig['agenda'])) {

				$_tags=array();
				$sql->consult($_p."parametros_tags","*","WHERE lixo=0 order by titulo asc");
				while($x=mysqli_fetch_object($sql->mysqry)) {
					$_tags[]=$x;
				}
				?>

				<!-- Js Geral -->
				<script type="text/javascript">
					var check_agendaDesativarRegrasStatus = eval('<?php echo is_object($infoParametros)?$infoParametros->check_agendaDesativarRegrasStatus:0;?>');

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

						//console.log(date.getUTCDay()+' => '+dateString+' => '+calendar.view.title);
						//console.log(date.getTimezoneOffset());

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
						
						let date = '';
						if(agendaMobile==1) {
							date = new Date();
						} else {
							date = calendar.getDate();
						}
						let dia = d2(date.getDate());
						let mes = d2(date.getMonth()+1);
						let ano = date.getFullYear();

						let agendaHora='';
						if(dataHora.length>0) {
							let dt = new Date(dataHora);
							let dtHora = d2(dt.getHours());
							let dtMin = d2(dt.getMinutes());
							dia = d2(dt.getDate());
							mes = d2(dt.getMonth()+1);
							ano = d2(dt.getFullYear());
							agendaHora = `${dtHora}:${dtMin}`;
						}
						data = `${dia}/${mes}/${ano}`;

						//console.log(dataHora+'-> '+data+' '+agendaHora);
						
						
						$('#js-aside-add select[name=id_status]').val(1);
						$('#js-aside-add select[name=duracao]').val(``);
						$('#js-aside-add input[name=telefone1]').val(``);
						$('#js-aside-add input[name=agenda_duracao]').val(30);
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

						/*$('#js-aside-add .js-profissionais').chosen('destroy');
						$('#js-aside-add .js-profissionais').chosen();
						$('#js-aside-add .js-profissionais').trigger('chosen:updated');*/
						if($('#js-aside-add .js-profissionais').data('select2')) $('#js-aside-add .js-profissionais').select2('destroy');
						$('#js-aside-add .js-profissionais').select2();

						/*$('#js-aside-add .js-tags').chosen('destroy');
						$('#js-aside-add .js-tags').chosen();
						$('#js-aside-add .js-tags').trigger('chosen:updated');*/
						if($('#js-aside-add .js-tags').data('select2')) $('#js-aside-add .js-tags').select2('destroy');
						$('#js-aside-add .js-tags').select2();

						agendamentosProfissionais(`add`);
						checklistItens();
					}
					
					const checklistItens = () => {
						$('#js-aside-add .js-checklist-itens').html('');
						let data = `ajax=checklistItens`;
						$.ajax({
							type:"POST",
							data:data,
							success:function(rtn){ 
								if(rtn.success) {
									rtn.regs.forEach(x=>{
										$(`#js-aside-add .js-checklist-itens`).append(`<div class="colunas3">
											<dl>	
												<dd><label><input type="checkbox" name="checklist-${x.id}" class="input-switch" />${x.titulo}</label></dd>
											</dl>
											<dl class="dl2">
												<dd><input type="text" name="checklist_descricao-${x.id}" placeholder="descrição" /></dd>
											</dl>
										</div>`);
									});
								}
							}
						});
					}

					const formatTemplate = (state) => {
						if (!state.id) return state.text;
						var baseUrl = "/user/pages/images/flags";
						infoComplementar=``;
						infoComplementar+= !! state.cpf ? ` - CPF: ${state.cpf}` : '';
						infoComplementar+= !! state.telefone ? ` - Tel.: ${state.telefone}` : '';
						var $state = $('<span style="display:flex; align-items:center; gap:.5rem;"><img src="'+state.ft+'" style="width:40px;height:40px;border-radius:100%;" /> ' + state.text + infoComplementar + '</span>');
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
						$('.js-novoAgendamento').click(function(){
							novoAgendamento(0,'');
						})
					})
				</script>

				<!-- Aside Novo Agendamento -->
				<section class="aside aside-add" id="js-aside-add">
					<script type="text/javascript">
						
						const popView = (id_agenda) => {
							$('#js-aside-edit .js-foto').attr('src','img/ilustra-usuario.jpg');
							
							let data = `ajax=editar&id=${id_agenda}`;
							$.ajax({
									type:"POST",
									url:baseURLApiAside,
									data:data,
									success:function(rtn){ 
										if(rtn.success) {
											$.ajax({
												type:"POST",
												data:`ajax=atualizaFoto&id_paciente=${rtn.data.id_paciente}`
											});
											//$('html, body').animate({scrollTop: 0},'fast');
											if(rtn.data.agendaPessoal>0) {
												//alert($('#js-aside-edit select[name=agenda_duracao]').find(`option[value=${rtn.data.agenda_duracao}]`).length);
												$('#js-aside-edit-agendaPessoal input[name=id]').val(rtn.data.id);
												$('#js-aside-edit-agendaPessoal input[name=agenda_data]').val(rtn.data.agenda_data);
												$('#js-aside-edit-agendaPessoal input[name=agenda_hora]').val(rtn.data.agenda_hora);
												
												$('#js-aside-edit-agendaPessoal input[name=agenda_duracao] option.js-duracaoAdicional').remove();
												if($('#js-aside-edit-agendaPessoal input[name=agenda_duracao]').find(`option[value=${rtn.data.agenda_duracao}]`).length==0) {
													$('#js-aside-edit-agendaPessoal input[name=agenda_duracao]').append(`<option class="js-duracaoAdicional" value="${rtn.data.agenda_duracao}">${rtn.data.agenda_duracao}</option>`);
												}
												$('#js-aside-edit-agendaPessoal input[name=agenda_duracao]').val(rtn.data.agenda_duracao);

												$('#js-aside-edit-agendaPessoal textarea[name=obs]').val(rtn.data.obs);
												$('#js-aside-edit-agendaPessoal select[name=id_cadeira]').val(rtn.data.id_cadeira);
												$('#js-aside-edit select[name=id_profissional]').find(':selected').prop('selected',false);
												if(rtn.data.profissionais) {
													rtn.data.profissionais.forEach(idProfissional=> {
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

												if(rtn.data.plano_odontologico && rtn.data.plano_odontologico.length>0) {
													$('#js-aside-edit .js-planoOdontologico').html(`Plano Odontológico: ${rtn.data.plano_odontologico}`);
												} else {
													$('#js-aside-edit .js-planoOdontologico').html(`Plano Odontológico: -`);
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

												$('#js-aside-edit input[name=agenda_duracao] option.js-duracaoAdicional').remove();
												if($('#js-aside-edit input[name=agenda_duracao]').find(`option[value=${rtn.data.agenda_duracao}]`).length==0) {
													$('#js-aside-edit input[name=agenda_duracao]').append(`<option class="js-duracaoAdicional" value="${rtn.data.agenda_duracao}">${rtn.data.agenda_duracao}</option>`);
												}
												$('#js-aside-edit input[name=agenda_duracao]').val(rtn.data.agenda_duracao);

												$('#js-aside-edit select[name=id_cadeira]').val(rtn.data.id_cadeira);
												$('#js-aside-edit input[name=telefone1]').val(rtn.data.telefone1);
												$('#js-aside-edit .js-webwhatsapp').attr({'href':'https://wa.me/55'+rtn.data.telefone1})
												$('#js-aside-edit textarea[name=obs]').val(rtn.data.obs);
												$('#js-aside-edit select[name=id_status]').val(rtn.data.id_status)

												//$('#js-aside-edit .js-profissionais').trigger('chosen:updated'); 
												//$('#js-aside-edit .js-tags').trigger('chosen:updated'); 


												if(rtn.data.agendou_dias>1) $('#js-aside-edit .js-agendou').html(`<b>${rtn.data.agendou_profissional}</b> agendou há <b>${rtn.data.agendou_dias} dia(s)</b>`);
												else $('#js-aside-edit .js-agendou').html(`<b>${rtn.data.agendou_profissional}</b> agendou <b>hoje</b>`);

												$('.js-fieldset-horarios,.js-btn-remover').show();
												

												if(rtn.data.statusBI && rtn.data.statusBI.length==0) {
													$('#js-aside-edit .js-statusBI').html(``).hide();
												} else {
													$('#js-aside-edit .js-statusBI').html(`${rtn.data.statusBI}`).show();
												}

												$('#js-aside-edit .js-profissionais').find(':selected').prop('selected',false);
												$('#js-aside-edit .js-tags').find(':selected').prop('selected',false);

												if(rtn.data.profissionais) {
													rtn.data.profissionais.forEach(idProfissional=> {
														$('#js-aside-edit .js-profissionais').find(`[value=${idProfissional}]`).prop('selected',true);
													})
												}

												if(rtn.data.tags) {
													rtn.data.tags.forEach(idTag=> {
														$('#js-aside-edit .js-tags').find(`[value=${idTag}]`).prop('selected',true);
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
																								<td>${x.obs}</td>
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
												} 

												$('.js-ag-whatsapp .history div').remove();
												if(rtn.data.whatsapp && rtn.data.whatsapp.length>0) {
													rtn.data.whatsapp.forEach(x=>{
														

															cor = x.enviado==1 ? '--verde':'--vermelho';
															$('.js-ag-whatsapp .history').append(`<div class="history-item">
																										<h1>${x.tipo} <span style="color:var(${cor})">${x.enviado==1?`<span class="iconify" data-icon="bi:send-check-fill"></span>`:`<span class="iconify" data-icon="bi:send-exclamation-fill"></span> `}</span></h1>
																										
																										<div class="infozap-chat">

																											<div class="infozap-chat-text infozap-chat-text--author">
																												<article>
																													<p class="infozap-chat-text__msg">
																														${x.mensagem}
																													</p>
																													<p class="infozap-chat-text__date">${x.data}</p>
																												</article>
																											</div>
																										</div>
																									</div>`);

														

													})
												} else {
													$('.js-ag-whatsapp .history').append(`<div class="history-item">
																								<center>Nenhuma mensagem foi enviada</center>
																							</div>`);
												}


												$("#js-aside-edit").fadeIn(100,function() {

													if($('#js-aside-edit .js-profissionais').data('select2')) $('#js-aside-edit .js-profissionais').select2('destroy');
													$('#js-aside-edit .js-profissionais').select2();

													/*$('#js-aside-add .js-tags').chosen('destroy');
													$('#js-aside-add .js-tags').chosen();
													$('#js-aside-add .js-tags').trigger('chosen:updated');*/
													if($('#js-aside-edit .js-tags').data('select2')) $('#js-aside-edit .js-tags').select2('destroy');
													$('#js-aside-edit .js-tags').select2();

													//$('#js-aside-edit .js-profissionais').chosen('destroy');
													//setTimeout(function(){$('#js-aside-edit .js-profissionais').chosen();},100);

													//$('#js-aside-edit .js-tags').chosen('destroy');
													//setTimeout(function(){$('#js-aside-edit .js-tags').chosen();},100);

													$("#js-aside-edit .aside__inner1").addClass("active");
													$("#js-aside-edit .js-tab a:eq(0)").click();
												});

												
												$('#js-aside-edit input[name=agenda_data]').trigger('change');

												$('#js-aside-edit .js-profissionais').trigger('chosen:updated');
												$('#js-aside-edit .js-tags').trigger('chosen:updated');

												$('#js-aside-edit .js-salvar').show();

												$('#js-aside-edit input, #js-aside-edit textarea').prop('readonly',false).css('background','');

												$('#js-aside-edit select').prop('disabled',false).css('background','').trigger('chosen:updated');
												$('#js-aside-edit input[name=id_status_antigo]').val(rtn.data.id_status);
												// se confirmado
												if(check_agendaDesativarRegrasStatus==0 && rtn.data.id_status=="2") {
													$('#js-aside-edit select[name=id_status]').find('option[value=1],option[value=8]').prop('disabled',true);

													$('#js-aside-edit input[name=agenda_data]').prop('readonly',true).datetimepicker('destroy').css('background','var(--cinza3)');

													$('#js-aside-edit input[name=agenda_hora]').prop('readonly',true).datetimepicker('destroy').css('background','var(--cinza3)');

												}  
												// se desmarcado
												else if(check_agendaDesativarRegrasStatus==0 && rtn.data.id_status=="4") {

													$('#js-aside-edit .js-salvar').hide();

													$('#js-aside-edit select[name=id_status]').prop('disabled',true);

													$('#js-aside-edit input[name=agenda_data],#js-aside-edit input[name=agenda_hora]').datetimepicker('destroy')

													$('#js-aside-edit input, #js-aside-edit textarea').prop('readonly',true).css('background','var(--cinza3)');

													$('#js-aside-edit select').prop('disabled',true).css('background','var(--cinza3)').trigger('chosen:updated');


												} else if(check_agendaDesativarRegrasStatus==0) {


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
									url:baseURLApiAside,
									data:data,
									success:function(rtn) {
										if(rtn.success) {
											if(rtn.listaProfissionais || rtn.listaProfissionaisDestaque) {
												aside.find('.js-profissionais').find('optgroup, option').remove();
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
															//aside.find('.js-profissionais').append(`${options}`);
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
															//aside.find('.js-profissionais').append(`${options}`);

														}
													});
												

												}

												aside.find('.js-profissionais').trigger('chosen:updated');
											}

											if(rtn.tags && rtn.tags.length>0) {
												aside.find('.js-tags option').remove();

												options = ``;
												rtn.tags.forEach(x=>{
													let titulo = x.titulo;

													sel = $.inArray(x.id,tags)>=0?' selected':'';
													
													options+=`<option value="${x.id}"${sel}>${titulo}</option>`;
													aside.find('.js-tags').append(`<optgroup label="">${options}</optgroup>`);
													
												})
												aside.find('.js-tags').trigger('chosen:updated');
											}
										}
									}
								})
							}

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

							$('#js-aside-add .js-salvar').click(function(){
								let obj = $(this);

								let agendaPessoal = $('#js-aside-add input[name=agendaPessoal]').val();


								if(obj.attr('data-loading')==0) {
									obj.html(`<span class="iconify" data-icon="eos-icons:loading"></span>`);
									obj.attr('data-loading',1);

									let erro=false;
									$(`#js-aside-add form .obg-${agendaPessoal}`).each(function(index,elem){
										if($(this).attr('name')!==undefined && $(this).val()  && $(this).val().length==0) {
											$(elem).addClass('erro');
											erro=true;
										}
									});

									if(erro===true) {
										swal({title: "Erro!", text: "Complete os campos destacados", type:"error", confirmButtonColor: "#424242"});
										obj.html(`<i class="iconify" data-icon="bx-bx-check"></i><span>salvar</span>`);
										obj.attr('data-loading',0);
									} else {
										
										
										let campos = $('#js-aside-add form').serialize();
										let profissionais = $('#js-aside-add .js-profissionais').val();
										let tags = $('#js-aside-add .js-tags').val();

										let data = `ajax=novoAgendamento&profissionais=${profissionais}&tags=${tags}&${campos}`;

										$.ajax({
											type:'POST',
											url:baseURLApiAside,
											data:data,
											success:function(rtn) {
												if(rtn.success) {

													if(rtn.id_paciente) {
														$.ajax({
															type:"POST",
															data:`ajax=atualizaFoto&id_paciente=${rtn.id_paciente}`
														});
													}

													$('#js-aside-add select[name=id_paciente]').val('').trigger('change.select2');

													$('#js-aside-add select.js-profissionais').val('').trigger('chosen:updated');
													$('#js-aside-add select.js-tags').val('').trigger('chosen:updated');
													$('#js-aside-add input,#js-aside-add textarea').val('');
													$('#js-aside-add input[name=agenda_duracao]').val('');

													$.fancybox.close();
													if(calendar) calendar.refetchEvents();
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
						
							<section class="filter" style="overflow-x: visible">
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
										<?php /*<select name="agenda_duracao" class="obg-0">
											<option value="">-</option>
											<?php
											foreach($optAgendaDuracao as $v) {
												if($values['agenda_duracao']==$v) $possuiDuracao=true;
												echo '<option value="'.$v.'">'.$v.'</option>';
											}
											?>
										</select>*/?>
										<input type="number" name="agenda_duracao" class="obg-0" value="30" />

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
												<option value="">Buscar paciente...</option>
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
												if($p->check_agendamento==0 or $p->contratacaoAtiva==0) continue;
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
												if($p->lixo==1) continue;
												echo '<option value="'.$p->id.'"'.($values['id_cadeira']==$p->id?' selected':'').'>'.utf8_encode($p->titulo).'aas</option>';
											}
											?>
										</select>
									</dd>
								</dl>
							</div>
							<dl>
								<dt>Tags</dt>
								<dd>
									<select class="js-tags" multiple>
										<?php
											foreach($_tags as $p) {
												echo '<option value="'.$p->id.'">'.utf8_encode($p->titulo).'</option>';
											}
											?>
									</select>

									<a href="javascript:;" class="js-btn-aside button" data-aside="tag" data-aside-sub><i class="iconify" data-icon="fluent:add-circle-24-regular"></i></a> 
								</dd>
							</dl>
							<dl>
								<dt>Informações</dt>
								<dd><textarea name="obs" style="height:100px;"></textarea></dd>
							</dl>

							<fieldset style="margin-top:2rem;">
								<legend>Itens do checklist</legend>
								
								<div class="js-checklist-itens">
									
								</div>
								
							</fieldset>
						</form>
					</div>
				</section><!-- .aside -->


				<!-- Aside Edição Agenda Pessoal -->
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
													url:baseURLApiAside,
													success:function(rtn){
														swal.close();  
														if(rtn.success) {
															$.fancybox.close();
															if(calendar) calendar.refetchEvents();
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
										if($(this).attr('name')!==undefined && $(this).val().length==0) {
											$(elem).addClass('erro');
											erro=true;
										}
									});

									if(erro===true) {
										swal({title: "Erro!", text: "Complete os campos destacados", type:"error", confirmButtonColor: "#424242"});
										obj.html(`<i class="iconify" data-icon="bx-bx-check"></i><span>salvar</span>`);
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
													if(calendar) calendar.refetchEvents();
													//$('#js-aside-edit-agendaPessoal .aside-close-edicaoAgendamentoPessoal').click();

													$('.aside-close-edicaoAgendamentoPessoal').parent().parent().removeClass("active");
													$('.aside-close-edicaoAgendamentoPessoal').parent().parent().parent().fadeOut();


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
										<?php /*<select name="agenda_duracao">
											<?php
											foreach($optAgendaDuracao as $v) {
												if($values['agenda_duracao']==$v) $possuiDuracao=true;
												echo '<option value="'.$v.'"'.($values['agenda_duracao']==$v?' selected':'').'>'.$v.'</option>';
											}
											?>
										</select>*/?>
										<input type="number" name="agenda_duracao" value="<?php echo isset($values['agenda_duracao'])?$values['agenda_duracao']:'';?>" />
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
												if($p->check_agendamento==0 or $p->contratacaoAtiva==0) continue;
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
												if($p->lixo==1) continue;
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


				<!-- Aside Edição Agendamento -->
				<section class="aside aside-edit" id="js-aside-edit">
					<script type="text/javascript">
						$(function(){

							const agendaChecklistListar = () => {

								let id_agenda = $('#js-aside-edit input[name=id]').val();
								$('.js-agenda-checklist-table tbody').html('');

								let data = `ajax=checklistListar&id_agenda=${id_agenda}`;
								$.ajax({
									type:"POST",
									url:baseURLApiAside,
									data:data,
									success:function(rtn){ 
										if(rtn.success) {
											checklist=rtn.regs;

											if(checklist.length>0) {
												checklist.forEach(x=>{
											
													$(`.js-agenda-checklist-table tbody`).append(`<tr>
															<td>
																<h1>${x.titulo}</h1>
															</td>
															<td>${x.descricao}</td>
															<td><input type="checkbox" class="input-switch js-checklist" data-id_agendachecklist="${x.id}" ${x.checado==1?"checked":""} /></td>
														</tr>`);
												});
											} else {
												$('.js-agenda-checklist-table').append(`<tr><td><center>Nenhum checklist criado</center></td></tr>`);
											}
											
										}
									}
								});
							}

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
													url:baseURLApiAside,
													success:function(rtn){
														swal.close();  
														if(rtn.success) {
															$.fancybox.close();
															if(calendar) calendar.refetchEvents();
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
										if($(this).attr('name')!==undefined && $(this).val().length==0) {
											$(elem).addClass('erro');
											erro=true;
										}
									});

									if(erro===true) {
										swal({title: "Erro!", text: "Complete os campos destacados", type:"error", confirmButtonColor: "#424242"});
										obj.html(`<i class="iconify" data-icon="bx-bx-check"></i><span>salvar</span>`);
										obj.attr('data-loading',0);
									} else {
										
										let campos = $('#js-aside-edit form').serialize();
										let profissionais = $('#js-aside-edit .js-profissionais').val();
										let tags = $('#js-aside-edit .js-tags').val();

										let data = `ajax=agendamentoPersistir&profissionais=${profissionais}&tags=${tags}&${campos}`;
										
										let abrirProximaConsulta = 0;
										if($('#js-aside-edit input[name=id_status_antigo]').val()!=$('#js-aside-edit select[name=id_status]').val() && $('#js-aside-edit select[name=id_status]').val()==5) {
											abrirProximaConsulta=$('#js-aside-edit input[name=id]').val();
										}

										$.ajax({
											type:'POST',
											data:data,
											url:baseURLApiAside,
											success:function(rtn) {
												if(rtn.success) {
													$.fancybox.close();
													if(calendar) calendar.refetchEvents();
													$('#js-aside-edit input[name=alteracao]').val(0);
													$('#js-aside-edit .aside-close-edicaoAgendamento').click();

													if(rtn.wts && rtn.wts==1) {
														let data = `ajax=whatsappDisparar`;
														$.ajax({
															type:"POST",
															data:data
														})
													}
													//alert(abrirProximaConsulta);
													if(abrirProximaConsulta>0) asideProximaConsulta(abrirProximaConsulta);


													//swal({title: "Sucesso!", text: "Agendamento salvo com sucesso!", type:"success", confirmButtonColor: "#424242"});
												} else if(rtn.error) {
													swal({title: "Erro!", text: rtn.error, type:"error", confirmButtonColor: "#424242"});

												} else {
													swal({title: "Erro!", text: "Agendamento não efetuado. Por favor tente novamente! Error:001", type:"error", confirmButtonColor: "#424242"});
												}
											},
											error:function(rtn){
												swal({title: "Erro!", text: "Agendamento não efetuado. Por favor tente novamente! Error:002", type:"error", confirmButtonColor: "#424242"});
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
								agendaChecklistListar();
							});

							$('#js-aside-edit .js-agenda-checklist-table').on('click','.js-checklist',function(){
								let id_agenda = $('#js-aside-edit input[name=id]').val();
								let id_agenda_checklist = $(this).attr('data-id_agendachecklist');
								let checado = $(this).prop('checked')===true?1:0;

								let data = `ajax=checklistChecado&id_agenda=${id_agenda}&id_agenda_checklist=${id_agenda_checklist}&checado=${checado}`;
								$.ajax({
									type:"POST",
									url:baseURLApiAside,
									data:data,
									success:function(rtn){ 
										if(rtn.success) {
										}
									}
								});


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
										<p class="js-planoOdontologico"></p>
									</div>
								</div>
							</section>

							<script>
								const tagsListar = () => {
									let id_agenda = $('#js-aside-edit input[name=id]').val();
									$('.js-tags-table tbody').html('');
									$('.js-asTag-titulo').val('');
									$('.js-asTag-cor').val("#c18c6a");
									$('input[name=id_tag]').val(0);

									let data = `ajax=tagsListar&id_agenda=${id_agenda}`;

									$.ajax({
										type:"POST",
										url:baseURLApiAside,
										data:data,
										success:function(rtn) {
											if(rtn.success) {
												if(rtn.regs.length>0) {
													rtn.regs.forEach(x=>{
														$(`.js-tags-table tbody`).append(`<tr>
																<td>
																	${x.titulo}
																</td>
																<td><input type="color" value="${x.cor}" disabled /></td>
																<td style="text-align:right;">
																	<a href="javascript:;" class="button js-editar" data-id="${x.id}" data-titulo="${x.titulo}" data-cor="${x.cor}"><i class="iconify" data-icon="fluent:edit-24-regular"></i></a>

																	<a href="javascript:;" class="button js-remover" data-id="${x.id}"><i class="iconify" data-icon="fluent:delete-24-regular"></i></a>
																</td>
															</tr>`);
													});
												}
											}
										},
										error:function (rtn) {
											console.log("erro: "+rtn);						
										}
									})
								}

								const asideQueroReagendar = () => {

									let id_agenda = $('#js-aside-edit input[name=id]').val();

									$('#js-aside-queroReagendar .js-tab').show();
									let data = `ajax=asideQueroReagendarPaciente&id_agenda=${id_agenda}`;
									$.ajax({
										type:'POST',
										data:data,
										url:baseURLApiAside,
										success:function(rtn) {
											if(rtn.success) {
											
												$('#js-aside-queroReagendar .js-nome').html(`${rtn.data.nome} <i class="iconify" data-icon="fluent:share-screen-person-overlay-20-regular" style="color:var(--cinza4)"></i>`).attr('href',`pg_pacientes_resumo.php?id_paciente=${rtn.data.id_paciente}`);

												if(rtn.data.ft && rtn.data.ft.length>0) {
													$('#js-aside-queroReagendar .js-foto').attr('src',rtn.data.ft);
												} else {
													$('#js-aside-queroReagendar .js-foto').attr('src','img/ilustra-usuario.jpg');
												}

												if(rtn.data.idade && rtn.data.idade>0) {
													$('#js-aside-queroReagendar .js-idade').html(rtn.data.idade+(rtn.data.idade>=2?' anos':' ano'));
												} else {
													$('#js-aside-queroReagendar .js-idade').html(``);
												}

												if(rtn.data.profissionais) {
													
													$('#js-aside-queroReagendar .js-profissionais2').prop('selected',false);

													rtn.data.profissionais.forEach(idProfissional=> {
														$('#js-aside-queroReagendar .js-profissionais2').find(`[value=${idProfissional}]`).prop('selected',true);
													})
												}

												$('#js-aside-queroReagendar .js-profissionais2').chosen();
												$('#js-aside-queroReagendar .js-profissionais2').trigger('chosen:updated');

												$('#js-aside-queroReagendar input[name=agenda_data]').val('');
												$('#js-aside-queroReagendar select[name=agenda_duracao]').val(rtn.data.agenda_duracao);
												$('#js-aside-queroReagendar select[name=id_cadeira]').val(rtn.data.id_cadeira).trigger('chosen:updated');
												$('#js-aside-queroReagendar textarea[name=obs]').val(rtn.data.obs);

												$('#js-aside-queroReagendar .js-reagendar-id_agenda').val(rtn.data.id_agenda);
												$('#js-aside-queroReagendar .js-id_paciente').val(rtn.data.id_paciente);

											}
											
										},
										error:function() {
											//swal({title: "Erro!", text: "Algum erro ocorreu! Tente novamente.", type:"error", confirmButtonColor: "#424242"});
										}
									}).done(function(){

									});
								}

								$(function() {

									$('.js-tab a').click(function() {
										$(".js-tab a").removeClass("active");
										$(this).addClass("active");							
									});

									$(".js-btn-aside-tag").click(function() {
										$(".aside-tag").fadeIn(100,function() {
											$(".aside-tag .aside__inner1").addClass("active");
											tagsListar();
										});
									});

									$('.js-tags-table').on('click','.js-editar',function(){
										let id = $(this).attr('data-id');
										let titulo = $(this).attr('data-titulo');
										let cor = $(this).attr('data-cor');

										if(id) {
											$('input[name=id_tag]').val(id);
											$('.js-asTag-titulo').val(titulo);
											$('.js-asTag-cor').val(cor);
										}
									});

									$('.js-tags-table').on('click','.js-remover',function(){

										let id = $(this).attr('data-id');
										if($.isNumeric(id) && id>0) {
										
										swal({   
												title: "Atenção",   
												text: "Você tem certeza que deseja remover este registro?",   
												type: "warning",   
												showCancelButton: true,   
												confirmButtonColor: "#DD6B55",   
												confirmButtonText: "Sim!",   
												cancelButtonText: "Não",   
												closeOnConfirm: false,   
												closeOnCancel: false 
											}, function(isConfirm){   
												if (isConfirm) {    

													let data = `ajax=tagsRemover&id=${id}&id_agenda=${id_agenda}`;
													$.ajax({
														type:"POST",
														url:baseURLApiAside,
														data:data,
														success:function(rtn) {
															if(rtn.success) {
																$('.js-tags').empty();

																if(rtn.regs.length>0) {
																	rtn.regs.forEach(x=>{
																		$('.js-tags').append(`<option value="${x.id}" selected>${x.titulo}</option>`);
																	});
																}

																$('.js-tags').trigger('chosen:updated');
																tagsListar();
																swal.close();
																
															} else if(rtn.error) {
																swal({title: "Erro!", text: rtn.error, type:"error", confirmButtonColor: "#424242"});
															} else {
																swal({title: "Erro!", text: 'Algum erro ocorreu durante a remoção deste registro', type:"error", confirmButtonColor: "#424242"});
															}
														},
														error:function(){
															swal({title: "Erro!", text: 'Algum erro ocorreu durante a remoção deste registro.', type:"error", confirmButtonColor: "#424242"});
														}
													}) 
												} else {   
													swal.close();   
												} 
											});
										}
									});


									$(".js-btn-aside-queroReagendar").click(function() {
										let id_paciente = $('#js-aside-queroAgendar .js-id_paciente').val();

										$(".aside-queroReagendar").fadeIn(100,function() {
											$(".aside-queroReagendar .aside__inner1").addClass("active");
											asideQueroReagendar();
										}); 
									});

									$('#js-aside-queroReagendar .js-ag-agendamento-queroReagendar').on('change','select[name=agenda_duracao], select[name=id_cadeira],  select.js-select-profissionais, input[name=agenda_data]',function(){
										horarioDisponivel(0,$('.js-ag-agendamento-queroReagendar'));
									});

									$('#js-aside-queroReagendar .js-agendamento .js-salvar').click(function(){
										
										let id_agenda_origem = $('#js-aside-queroReagendar .js-reagendar-id_agenda').val();
										let id_paciente = $('#js-aside-queroReagendar .js-id_paciente').val();
										let agenda_data = $('.js-ag-agendamento-queroReagendar input[name=agenda_data]').val();
										let agenda_duracao = $('.js-ag-agendamento-queroReagendar select[name=agenda_duracao]').val();
										let id_cadeira = $('.js-ag-agendamento-queroReagendar select[name=id_cadeira]').val();
										let id_profissional = $('.js-ag-agendamento-queroReagendar select.js-select-profissionais').val();
										let agenda_hora = $('.js-ag-agendamento-queroReagendar select[name=agenda_hora]').val();
										let obs = $('.js-ag-agendamento-queroReagendar textarea[name=obs]').val();
										let erro = '';

										if(agenda_data.length==0) erro='Defina a <b>Data do Agendamento</b>';
										else if(agenda_duracao.length==0) erro='Defina a <b>Duração de Agendamento</b>';
										else if(id_cadeira.length==0) erro='Defina o <b>Consultório do Agendamento</b>';
										else if(id_profissional.length==0) erro='Defina o <b>Profissional do Agendamento</b>';
										else if(agenda_hora.length==0) erro='Defina a <b>Hora do Agendamento</b>';

										if(erro.length==0) {

											let obj = $(this);
											let obHTMLAntigo = $(this).html();

											if(obj.attr('data-loading')==0) {
												
												obj.html(`<span class="iconify" data-icon="eos-icons:loading"></span>`);
												obj.attr('data-loading',1);

												let data = `ajax=asRelacionamentoPacienteQueroAgendar&id_paciente=${id_paciente}&agenda_data=${agenda_data}&agenda_duracao=${agenda_duracao}&id_cadeira=${id_cadeira}&id_profissional=${id_profissional}&agenda_hora=${agenda_hora}&obs=${obs}`;

												data = {
													'ajax':'asRelacionamentoPacienteQueroAgendar',
													'id_paciente':id_paciente,
													'agenda_data':agenda_data,
													'agenda_duracao':agenda_duracao,
													'id_cadeira':id_cadeira,
													'id_profissional':id_profissional,
													'agenda_hora':agenda_hora,
													'obs':obs,
													'id_agenda_origem':id_agenda_origem,
													'reagendar':1
												}
												$.ajax({
														type:'POST',
														data:data,
														url:baseURLApiAside,
														success:function(rtn) {
															if(rtn.success) {
																
																$('.aside-close').click();
																$('#js-aside-edit .aside-close-edicaoAgendamento').click();
																if(calendar) calendar.refetchEvents();

															} else if(rtn.error) {
																swal({title: "Erro!", text: rtn.error, type:"error", confirmButtonColor: "#424242"});
															} else {
																swal({title: "Erro!", text: "Algum erro ocorreu! Tente novamente.", type:"error", confirmButtonColor: "#424242"});
															}
															
														},
														error:function() {
															swal({title: "Erro!", text: "Algum erro ocorreu! Tente novamente.", type:"error", confirmButtonColor: "#424242"});
														} 
												}).done(function(){
													obj.html(obHTMLAntigo);
													obj.attr('data-loading',0);
												});
											}

										} else {
											swal({title: "Erro!", text: erro, html:true, type:"error", confirmButtonColor: "#424242"});
										}
									});

									$('.js-ag-whatsapp .js-btn-wtsEnviar').click(function(){
										let id_tipo = $('.js-ag-whatsapp select[name=id_tipo]').val();
										if(id_tipo.length>0) {


											let obj = $(this);
											let objHTMLAntigo = $(this).html();
											let idAgenda = $('#js-aside-edit input[name=id]').val();

											if(obj.attr('data-loading')==0) {
												let data = `ajax=enviarWhatsapp&id_tipo=${id_tipo}&id_agenda=${idAgenda}`;

												obj.html(`<span class="iconify" data-icon="eos-icons:loading"></span> Enviando...`);

												$.ajax({
													type:"POST",
													url:baseURLApiAside,
													data:data,
													success:function(rtn) {
														if(rtn.success) {
															swal({title: "Sucesso!", text: 'Mensagem enviada com sucesso para o numero <b>'+rtn.celular+'</b>!', type:"success", html:true,confirmButtonColor: "#424242"},function(){
																popView(idAgenda);
																setTimeout(function(){$('#js-aside-edit .js-btn-whatsapp').click();},500);
															});
														} else {
															let erro = rtn.error ?rtn.error : 'Algum erro ocorreu. Tente novamente';
															swal({title: "Erro!", text: rtn.error, type:"error", confirmButtonColor: "#424242"});
														}
													}
												}).done(function(){
													obj.attr('data-loading',0);
													obj.html(objHTMLAntigo);
												})
											}
										
										} else {
											swal({title: "Erro!", text: 'Selecione o tipo de mensagem que deseja enviar', type:"error", confirmButtonColor: "#424242"});
										}
									})
								});
							</script>
							<section class="tab tab_alt js-tab">
								<a href="javascript:;" onclick="$('.js-ag').hide(); $('.js-ag-agenda').show();" class="active">Agenda</a>
								<a href="javascript:;" onclick="$('.js-ag').hide(); $('.js-ag-checklist').show();">Checklist</a>					
								<a href="javascript:;" onclick="$('.js-ag').hide(); $('.js-ag-futuro').show();">Agendamentos Futuros</a>
								<a href="javascript:;" onclick="$('.js-ag').hide(); $('.js-ag-historico').show();">Histórico</a>		
								<a href="javascript:;" class="js-btn-whatsapp" onclick="$('.js-ag').hide(); $('.js-ag-whatsapp').show();">Whatsapp</a>					
								
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
												<dd><a href="javascript:;" class="button js-webwhatsapp" target="_blank"><span class="iconify" data-icon="ic:outline-whatsapp"></span></a></dd>
											</dl>
											<dl>
												<dd><a href="javascript:;" class="button js-btn-aside-queroReagendar"><span class="iconify" data-icon="mdi:calendar-clock"></span></a></dd>
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
											<?php /*<input type="tel" name="agenda_duracao" class="" />
											<select name="agenda_duracao">
												<?php
												foreach($optAgendaDuracao as $v) {
													echo '<option value="'.$v.'"'.($values['agenda_duracao']==$v?' selected':'').'>'.$v.'</option>';
												}
												?>
											</select>*/?>
											<input type="text" name="agenda_duracao" value="" />
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
										<dd class="form-comp">
											<span class="js-country">BR</span><input type="tel" name="telefone1" class="" />
										</dd>
									</dl>
									<dl>
										<dt>Consultório</dt>
										<dd>
											<select name="id_cadeira">
												<option value=""></option>
												<?php
												foreach($_cadeiras as $p) {
													if($p->lixo==1) continue;
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
													if($p->check_agendamento==0 or $p->contratacaoAtiva==0) continue;
													echo '<option value="'.$p->id.'">'.utf8_encode($p->nome).'</option>';
												}
												?>
											</select>
										</dd>
									</dl>
								</div>
								<div>
									<dl>
										<dt>Tags</dt>
										<dd>
											<select class="js-tags" multiple>
												<option value=""></option>
												<?php
												foreach ($_tags as $x) {
													echo '<option value="'.$x->id.'">'.utf8_encode($x->titulo).'</option>';
												}
												?>
											</select>

											<a  href="javascript:;" class="js-btn-aside-tag button" data-aside-sub><i class="iconify" data-icon="fluent:add-circle-24-regular"></i></a>
										</dd>
									</dl>
								</div>
								<dl>
									<dt>Informações</dt>
									<dd><textarea name="obs" style="height:100px;"></textarea></dd>
								</dl>
							</div>

							<div class="js-ag js-ag-checklist">
								<section class="filter">
									<div class="filter-group">
									</div>
									<div class="filter-group">
										<div class="filter-form form">
											<?php /*
											<dl>									
												<dd><button class="button" data-loading="0"><i class="iconify" data-icon="fluent:add-circle-24-regular"></i> <span>Adicionar</span></button></dd>
											</dl>
											
											<dl>									
												<dd><button class="button button_main js-salvar" data-loading="0"><i class="iconify" data-icon="fluent:checkmark-24-filled"></i> <span>Salvar</span></button></dd>
											</dl>*/ ?>
										</div>								
									</div>
								</section>

								<div class="list1">
									<table class="js-agenda-checklist-table">
										<tbody>
										</tbody>
										<?php 
											/*
										<tr>								
											<td>
												<h1>Laboratório</h1>
											</td>
											<td>Lorem ipsum dolor sit amet consectetur, adipisicing elit. Corrupti, veritatis?</td>
											<td><input type="checkbox" name="" class="input-switch" /></td>
										</tr>
										<tr>								
											<td>
												<h1>Imagem</h1>
											</td>
											<td>Lorem ipsum dolor, sit amet consectetur adipisicing elit. Voluptatibus eveniet, dolore tempore dicta blanditiis. Eaque atque ipsam eveniet sunt possimus!</td>
											<td><input type="checkbox" name="" class="input-switch" /></td>
										</tr>
										<tr>								
											<td>
												<h1>Insumos</h1>
											</td>
											<td></td>
											<td><button class="button" data-loading="0"><i class="iconify" data-icon="fluent:add-circle-24-regular"></i> <span>Adicionar</span></button></td>
										</tr>
										<tr>								
											<td>
												<h1>Equipamentos</h1>
											</td>
											<td></td>
											<td><button class="button" data-loading="0"><i class="iconify" data-icon="fluent:add-circle-24-regular"></i> <span>Adicionar</span></button></td>
										</tr> */ ?>
									</table>
								</div>
								
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

							<?php
							$wtsTipos=[];
							$sql->consult($_p."whatsapp_mensagens_tipos","id,titulo","where id IN (1,2) order by titulo asc");
							while($x=mysqli_fetch_object($sql->mysqry)) $wtsTipos[]=$x;
							?>

							<div class="js-ag js-ag-whatsapp" style="display:none;">

								<section class="filter">
									<div class="filter-group">
										<dl>
											<dt>Tipo</dt>
											<dd>
												<select name="id_tipo">
													<option value="">-</option>
													<?php
													foreach($wtsTipos as $x) {
														echo '<option value="'.$x->id.'">'.utf8_encode($x->titulo).'</option>';
													}
													?>
												</select>
											</dd>
										</dl>
										<dl>
											<dt>&nbsp;</dt>
											<dd><a href="javascript:;" class="button js-btn-wtsEnviar" data-loading="0"><span class="iconify" data-icon="ic:outline-whatsapp"></span> Enviar</a></dd>
										</dl>
									</div>
									
								</section>


								<div class="history">
									
								</div>
							</div>

						</form>

					</div>
				</section><!-- .aside -->	
				<?php
			}

		// Especialidades
			if(isset($apiConfig['especialidade'])) {
				?>
				<script type="text/javascript">
					var asEspecialidades = [];

					const asEspecialidadesListar = (openAside) => {
						
						if(asEspecialidades) {
							$('.js-asEspecialidades-table tbody').html('');

							//$(`.js-id_plano option`).prop('disabled',false);


							let atualizaEspecialidade = $('select.ajax-id_especialidade')?1:0;
							let atualizaEspecialidadeId = 0;
							if(atualizaEspecialidade==1) {
								atualizaEspecialidadeId=$('select.ajax-id_especialidade').val();
								$('select.ajax-id_especialidade').find('option').remove();
								$('select.ajax-id_especialidade').append('<option value="">-</option>');
							}

							asEspecialidades.forEach(x=>{

								$(`.js-asEspecialidades-table tbody`).append(`<tr class="aside-open">
																	<td><h1>${x.titulo}</h1></td>
																	<td style="text-align:right;"><a href="javascript:;" class="button js-asEspecialidades-editar" data-id="${x.id}"><i class="iconify" data-icon="fluent:edit-24-regular"></i></a></td>
																</tr>`);

								if(atualizaEspecialidade==1) {
									sel=(atualizaEspecialidadeId==x.id)?' selected':'';
									$('select.ajax-id_especialidade').append(`<option value="${x.id}"${sel}>${x.titulo}</option>`);
								}
							});
							
							if(openAside===true) {
								$("#js-aside-asEspecialidades").fadeIn(100,function() {
									$("#js-aside-asEspecialidades .aside__inner1").addClass("active");
								});
							}

						} else {
							if(openAside===true) {
								$(".aside").fadeIn(100,function() {
										$(".aside .aside__inner1").addClass("active");
								});
							}
						}
					}

					const asEspecialidadesAtualizar = (openAside) => {	
						let data = `ajax=asEspecialidadesListar`;

						$.ajax({
							type:"POST",
							url:baseURLApiAside,
							data:data,
							success:function(rtn) {
								if(rtn.success) {
									asEspecialidades=rtn.regs;
									asEspecialidadesListar(openAside);
								}
							}
						})
					}
					
					const asEspecialidadesEditar = (id) => {
						let data = `ajax=asEspecialidadesEditar&id=${id}`;
						$.ajax({
							type:"POST",
							url:baseURLApiAside,
							data:data,
							success:function(rtn) {
								if(rtn.success) {
									reg=rtn.cnt

									$(`.js-asEspecialidades-id`).val(reg.id);
									$(`.js-asEspecialidades-titulo`).val(reg.titulo);

									
									$('.js-asEspecialidades-form').animate({scrollTop: 0},'fast');
									$('.js-asEspecialidades-submit').html(`<i class="iconify" data-icon="fluent:checkmark-12-filled"></i>`);
									$('.js-asEspecialidades-remover').show();

								} else if(rtn.error) {
									swal({title: "Erro!", text: rtn.error, type:"error", confirmButtonColor: "#424242"});
								} else {
									swal({title: "Erro!", text: "Algum erro ocorreu durante a edição deste registro!", type:"error", confirmButtonColor: "#424242"});
								}
							},
							error:function(){
								swal({title: "Erro!", text: "Algum erro ocorreu durante a edição deste registro!", type:"error", confirmButtonColor: "#424242"});
							}
						});
					}

					
					$(function(){

						asEspecialidadesAtualizar();

						$('.js-asEspecialidades-submit').click(function(){
							let obj = $(this);
							if(obj.attr('data-loading')==0) {

								let id = $(`.js-asEspecialidades-id`).val();
								let titulo = $(`.js-asEspecialidades-titulo`).val();

							

								if(titulo.length==0) {
									swal({title: "Erro!", text: "Digite a Especialidade", type:"error", confirmButtonColor: "#424242"});
								}  else {

									obj.html(`<span class="iconify" data-icon="eos-icons:loading"></span>`);
									obj.attr('data-loading',1);

									let data = `ajax=asEspecialidadesPersistir&id=${id}&titulo=${titulo}`;
									
									$.ajax({
										type:'POST',
										data:data,
										url:baseURLApiAside,
										success:function(rtn) {
											if(rtn.success) {
												asEspecialidadesAtualizar();	

												$(`.js-asEspecialidades-id`).val(0);
												$(`.js-asEspecialidades-titulo`).val(``);

											} else if(rtn.error) {
												swal({title: "Erro!", text: rtn.error, type:"error", confirmButtonColor: "#424242"});
											} else {
												swal({title: "Erro!", text: "Algum erro ocorreu! Tente novamente.", type:"error", confirmButtonColor: "#424242"});
											}
											
										},
										error:function() {
											swal({title: "Erro!", text: "Algum erro ocorreu! Tente novamente.", type:"error", confirmButtonColor: "#424242"});
										}
									}).done(function(){
										$('.js-asEspecialidades-remover').hide();
										obj.html(`<i class="iconify" data-icon="fluent:add-circle-24-regular"></i>`);
										obj.attr('data-loading',0);
									});

								}
							}
						})

						$('.js-asEspecialidades-table').on('click','.js-asEspecialidades-editar',function(){
							let id = $(this).attr('data-id');
							asEspecialidadesEditar(id);
						});

						$('.aside-especialidade').on('click','.js-asEspecialidades-remover',function(){
							let obj = $(this);

							if(obj.attr('data-loading')==0) {

								let id = $('.js-asEspecialidades-id').val();
								swal({
									title: "Atenção",
									text: "Você tem certeza que deseja remover este registro?",
									type: "warning",
									showCancelButton: true,
									confirmButtonColor: "#DD6B55",
									confirmButtonText: "Sim!",
									cancelButtonText: "Não",
									closeOnConfirm:false,
									closeOnCancel: false }, 
									function(isConfirm){   
										if (isConfirm) {   

											obj.html(`<span class="iconify" data-icon="eos-icons:loading"></span>`);
											obj.attr('data-loading',1);
											let data = `ajax=asEspecialidadesRemover&id=${id}`; 
											$.ajax({
												type:"POST",
												data:data,
												url:baseURLApiAside,
												success:function(rtn) {
													if(rtn.success) {
														$(`.js-asEspecialidades-id`).val(0);
														$(`.js-asEspecialidades-titulo`).val('');
														asEspecialidadesAtualizar();
														swal.close();   
													} else if(rtn.error) {
														swal({title: "Erro!", text: rtn.error, type:"error", confirmButtonColor: "#424242"});
													} else {
														swal({title: "Erro!", text: "Algum erro ocorreu durante a remoção deste horário!", type:"error", confirmButtonColor: "#424242"});
													}
												},
												error:function(){
													swal({title: "Erro!", text: "Algum erro ocorreu durante a remoção deste horário!", type:"error", confirmButtonColor: "#424242"});
												}
											}).done(function(){
												$('.js-asEspecialidades-remover').hide();
												obj.html('<i class="iconify" data-icon="fluent:delete-24-regular"></i>');
												obj.attr('data-loading',0);
												$(`.js-asEspecialidades-submit`).html(`<i class="iconify" data-icon="fluent:add-circle-24-regular"></i>`);
											});
										} else {   
											swal.close();   
										} 
									});
							}
						});

					});
				</script>

				<section class="aside aside-especialidade">
					<div class="aside__inner1">

						<header class="aside-header">
							<h1>Especialidade</h1>
							<a href="javascript:;" class="aside-header__fechar aside-close"><i class="iconify" data-icon="fluent:dismiss-24-filled"></i></a>
						</header>

						<form method="post" class="aside-content form js-asEspecialidades-form">
							<input type="hidden" class="js-asEspecialidades-id" />
							
							<dl>
								<dt>Título da Especialidade</dt>
								<dd>
									<input type="text" class="js-asEspecialidades-titulo" />
									<button type="button" class="js-asEspecialidades-submit button button_main" data-loading="0"><i class="iconify" data-icon="fluent:add-circle-24-regular"></i></button>
									<a href="javascript:;" class="button js-asEspecialidades-remover" data-loading="0" style="display:none;"><i class="iconify" data-icon="fluent:delete-24-regular"></i></a>
								</dd>
							</dl>

							<div class="list2" style="margin-top:2rem;">
								<table class="js-asEspecialidades-table">
									<thead>
										<tr>									
											<th>ESPECIALIDADE</th>
											<th></th>
										</tr>
									</thead>
									<tbody>
										<tr>
											<td><h1>Título da Especialidade</h1></td>
											<td style="text-align:right;"><a href="javascript:;" class="js-edit button" data-loading="0"><i class="iconify" data-icon="fluent:edit-24-regular"></i></a></td>
										</tr>								
									</tbody>
								</table>
							</div>
							
						</form>
					</div>
				</section>
				<?php
			}

		// Plano
			if(isset($apiConfig['plano'])) {
				?>
				<script type="text/javascript">
					var asPlanos = [];

					const asPlanosListar = (openAside) => {
						
						if(asPlanos) {
							$('.js-asPlanos-table tbody').html('');

							let atualizaPlano = $('select.ajax-id_plano')?1:0;
							let atualizaPlanoId = 0;
							let planosDisabledIds = [];
							if(atualizaPlano==1) {

								$('select.ajax-id_plano option').each(function(index,el){
									if($(el).prop('disabled')===true) {
										planosDisabledIds.push($(el).val());
									}
								})
								atualizaPlanoId=$('select.ajax-id_plano').val();
								$('select.ajax-id_plano').find('option').remove();
								$('select.ajax-id_plano').append('<option value="">-</option>');
							}

							asPlanos.forEach(x=>{

								$(`.js-asPlanos-table tbody`).append(`<tr class="aside-open">
																	<td><h1>${x.titulo}</h1></td>
																	<td style="text-align:right;"><a href="javascript:;" class="button js-asPlanos-editar" data-id="${x.id}"><i class="iconify" data-icon="fluent:edit-24-regular"></i></a></td>
																</tr>`);

								if(atualizaPlano==1) {
									dis=planosDisabledIds.includes(x.id)?' disabled':'';
									sel=(atualizaPlanoId==x.id)?' selected':'';
									$('select.ajax-id_plano').append(`<option value="${x.id}"${sel}${dis}>${x.titulo}</option>`);
								}

							});
							
							if(openAside===true) {
								$("#js-aside-asPlano").fadeIn(100,function() {
									$("#js-aside-asPlano .aside__inner1").addClass("active");
								});
							}

						} else {
							if(openAside===true) {
								$(".aside").fadeIn(100,function() {
										$(".aside .aside__inner1").addClass("active");
								});
							}
						}
					}

					const asPlanosAtualizar = (openAside) => {	
						let data = `ajax=asPlanosListar`;

						$.ajax({
							type:"POST",
							url:baseURLApiAside,
							data:data,
							success:function(rtn) {
								if(rtn.success) {
									asPlanos=rtn.regs;
									asPlanosListar(openAside);
								}
							}
						})
					}
					
					const asPlanosEditar = (id) => {
						let data = `ajax=asPlanosEditar&id=${id}`;
						$.ajax({
							type:"POST",
							url:baseURLApiAside,
							data:data,
							success:function(rtn) {
								if(rtn.success) {
									reg=rtn.cnt

									$(`.js-asPlanos-id`).val(reg.id);
									$(`.js-asPlanos-titulo`).val(reg.titulo);

									
									$('.js-asPlanos-form').animate({scrollTop: 0},'fast');
									$('.js-asPlanos-submit').html(`<i class="iconify" data-icon="fluent:checkmark-12-filled"></i>`);
									if(rtn.fixo==1) {
										$('.js-asPlanos-remover').hide();
									} else {
										$('.js-asPlanos-remover').show();
									}

								} else if(rtn.error) {
									swal({title: "Erro!", text: rtn.error, type:"error", confirmButtonColor: "#424242"});
								} else {
									swal({title: "Erro!", text: "Algum erro ocorreu durante a edição deste registro!", type:"error", confirmButtonColor: "#424242"});
								}
							},
							error:function(){
								swal({title: "Erro!", text: "Algum erro ocorreu durante a edição deste registro!", type:"error", confirmButtonColor: "#424242"});
							}
						});
					}

					
					$(function(){

						asPlanosAtualizar();

						$('.js-asPlanos-submit').click(function(){
							let obj = $(this);
							if(obj.attr('data-loading')==0) {

								let id = $(`.js-asPlanos-id`).val();
								let titulo = $(`.js-asPlanos-titulo`).val();

							

								if(titulo.length==0) {
									swal({title: "Erro!", text: "Digite a Especialidade", type:"error", confirmButtonColor: "#424242"});
								}  else {

									obj.html(`<span class="iconify" data-icon="eos-icons:loading"></span>`);
									obj.attr('data-loading',1);

									let data = `ajax=asPlanosPersistir&id=${id}&titulo=${titulo}`;
									
									$.ajax({
										type:'POST',
										data:data,
										url:baseURLApiAside,
										success:function(rtn) {
											if(rtn.success) {
												asPlanosAtualizar();	

												$(`.js-asPlanos-id`).val(0);
												$(`.js-asPlanos-titulo`).val(``);

											} else if(rtn.error) {
												swal({title: "Erro!", text: rtn.error, type:"error", confirmButtonColor: "#424242"});
											} else {
												swal({title: "Erro!", text: "Algum erro ocorreu! Tente novamente.", type:"error", confirmButtonColor: "#424242"});
											}
											
										},
										error:function() {
											swal({title: "Erro!", text: "Algum erro ocorreu! Tente novamente.", type:"error", confirmButtonColor: "#424242"});
										}
									}).done(function(){
										$('.js-asPlanos-remover').hide();
										obj.html(`<i class="iconify" data-icon="fluent:add-circle-24-regular"></i>`);
										obj.attr('data-loading',0);
									});

								}
							}
						})

						$('.js-asPlanos-table').on('click','.js-asPlanos-editar',function(){
							let id = $(this).attr('data-id');
							asPlanosEditar(id);
						});

						$('.aside-plano').on('click','.js-asPlanos-remover',function(){
							let obj = $(this);

							if(obj.attr('data-loading')==0) {

								let id = $('.js-asPlanos-id').val();
								swal({
									title: "Atenção",
									text: "Você tem certeza que deseja remover este registro?",
									type: "warning",
									showCancelButton: true,
									confirmButtonColor: "#DD6B55",
									confirmButtonText: "Sim!",
									cancelButtonText: "Não",
									closeOnConfirm:false,
									closeOnCancel: false }, 
									function(isConfirm){   
										if (isConfirm) {   

											obj.html(`<span class="iconify" data-icon="eos-icons:loading"></span>`);
											obj.attr('data-loading',1);
											let data = `ajax=asPlanosRemover&id=${id}`; 
											$.ajax({
												type:"POST",
												data:data,
												url:baseURLApiAside,
												success:function(rtn) {
													if(rtn.success) {
														$(`.js-asPlanos-id`).val(0);
														$(`.js-asPlanos-titulo`).val('');
														asPlanosAtualizar();
														swal.close();   
													} else if(rtn.error) {
														swal({title: "Erro!", text: rtn.error, type:"error", confirmButtonColor: "#424242"});
													} else {
														swal({title: "Erro!", text: "Algum erro ocorreu durante a remoção deste horário!", type:"error", confirmButtonColor: "#424242"});
													}
												},
												error:function(){
													swal({title: "Erro!", text: "Algum erro ocorreu durante a remoção deste horário!", type:"error", confirmButtonColor: "#424242"});
												}
											}).done(function(){
												$('.js-asPlanos-remover').hide();
												obj.html('<i class="iconify" data-icon="fluent:delete-24-regular"></i>');
												obj.attr('data-loading',0);
												$(`.js-asPlanos-submit`).html(`<i class="iconify" data-icon="fluent:add-circle-24-regular"></i>`);
											});
										} else {   
											swal.close();   
										} 
									});
							}
						});

					});
				</script>

				<section class="aside aside-plano">
					<div class="aside__inner1">

						<header class="aside-header">
							<h1>Plano</h1>
							<a href="javascript:;" class="aside-header__fechar aside-close"><i class="iconify" data-icon="fluent:dismiss-24-filled"></i></a>
						</header>

						<form method="post" class="aside-content form">
							<input type="hidden" class="js-asPlanos-id" />
							
							<dl>
								<dt>Título do Plano</dt>
								<dd>
									<input type="text" class="js-asPlanos-titulo" />
									<button type="button" class="js-asPlanos-submit button button_main" data-loading="0"><i class="iconify" data-icon="fluent:add-circle-24-regular"></i></button>
									<a href="javascript:;" class="button js-asPlanos-remover" data-loading="0" style="display:none;"><i class="iconify" data-icon="fluent:delete-24-regular"></i></a>
								</dd>
							</dl>
							<div class="list2" style="margin-top:2rem;">
								<table class="js-asPlanos-table">
									<thead>
										<tr>									
											<th>PLANO</th>
											<th></th>
										</tr>
									</thead>
									<tbody>							
									</tbody>
								</table>
							</div>
						</form>
					</div>
				</section>
				<?php
			}

		// Marca
			if(isset($apiConfig['marca'])) {
				?>
				<script type="text/javascript">
					var asMarcas = [];

					const asMarcasListar = (openAside) => {
						
						if(asMarcas) {
							$('.js-asMarcas-table tbody').html('');

							let atualizaMarca = $('select.ajax-id_categoria')?1:0;
							let atualizaMarcaId = 0;
							let marcasDisabledIds = [];
							if(atualizaMarca==1) {

								$('select.ajax-id_marca option').each(function(index,el){
									if($(el).prop('disabled')===true) {
										marcasDisabledIds.push($(el).val());
									}
								})
								atualizaMarcaId=$('select.ajax-id_marca').val();
								$('select.ajax-id_marca').find('option').remove();
								$('select.ajax-id_marca').append('<option value="">-</option>');
							}

							asMarcas.forEach(x=>{

								$(`.js-asMarcas-table tbody`).append(`<tr class="aside-open">
																	<td><h1>${x.titulo}</h1></td>
																	<td style="text-align:right;"><a href="javascript:;" class="button js-asMarcas-editar" data-id="${x.id}"><i class="iconify" data-icon="fluent:edit-24-regular"></i></a></td>
																</tr>`);

								if(atualizaMarca==1) {
									dis=marcasDisabledIds.includes(x.id)?' disabled':'';
									sel=(atualizaMarcaId==x.id)?' selected':'';
									$('select.ajax-id_marca').append(`<option value="${x.id}"${sel}${dis}>${x.titulo}</option>`);
								}

							});
							
							if(openAside===true) {
								$("#js-aside-asMarcas").fadeIn(100,function() {
									$("#js-aside-asMarcas .aside__inner1").addClass("active");
								});
							}

						} else {
							if(openAside===true) {
								$(".aside").fadeIn(100,function() {
										$(".aside .aside__inner1").addClass("active");
								});
							}
						}
					}

					const asMarcasAtualizar = (openAside) => {	
						let data = `ajax=asMarcasListar`;

						$.ajax({
							type:"POST",
							url:baseURLApiAside,
							data:data,
							success:function(rtn) {
								if(rtn.success) {
									asMarcas=rtn.regs;
									asMarcasListar(openAside);
								}
							}
						})
					}
					
					const asMarcasEditar = (id) => {
						let data = `ajax=asMarcasEditar&id=${id}`;
						$.ajax({
							type:"POST",
							url:baseURLApiAside,
							data:data,
							success:function(rtn) {
								if(rtn.success) {
									reg=rtn.cnt

									$(`.js-asMarcas-id`).val(reg.id);
									$(`.js-asMarcas-titulo`).val(reg.titulo);

									
									$('.js-asMarcas-form').animate({scrollTop: 0},'fast');
									$('.js-asMarcas-submit').html(`<i class="iconify" data-icon="fluent:checkmark-12-filled"></i>`);
									$('.js-asMarcas-remover').show();

									$('.aside-content').animate({scrollTop: 0},'fast');

								} else if(rtn.error) {
									swal({title: "Erro!", text: rtn.error, type:"error", confirmButtonColor: "#424242"});
								} else {
									swal({title: "Erro!", text: "Algum erro ocorreu durante a edição deste registro!", type:"error", confirmButtonColor: "#424242"});
								}
							},
							error:function(){
								swal({title: "Erro!", text: "Algum erro ocorreu durante a edição deste registro!", type:"error", confirmButtonColor: "#424242"});
							}
						});
					}

					
					$(function(){

						asMarcasAtualizar();

						$('.js-asMarcas-submit').click(function(){
							let obj = $(this);
							if(obj.attr('data-loading')==0) {

								let id = $(`.js-asMarcas-id`).val();
								let titulo = $(`.js-asMarcas-titulo`).val();

							

								if(titulo.length==0) {
									swal({title: "Erro!", text: "Digite a Marca", type:"error", confirmButtonColor: "#424242"});
								}  else {

									obj.html(`<span class="iconify" data-icon="eos-icons:loading"></span>`);
									obj.attr('data-loading',1);

									let data = `ajax=asMarcasPersistir&id=${id}&titulo=${titulo}`;
								
									$.ajax({
										type:'POST',
										data:data,
										url:baseURLApiAside,
										success:function(rtn) {
											if(rtn.success) {
												asMarcasAtualizar();	

												$(`.js-asMarcas-id`).val(0);
												$(`.js-asMarcas-titulo`).val(``);

											} else if(rtn.error) {
												swal({title: "Erro!", text: rtn.error, type:"error", confirmButtonColor: "#424242"});
											} else {
												swal({title: "Erro!", text: "Algum erro ocorreu! Tente novamente.", type:"error", confirmButtonColor: "#424242"});
											}
											
										},
										error:function() {
											swal({title: "Erro!", text: "Algum erro ocorreu! Tente novamente.", type:"error", confirmButtonColor: "#424242"});
										}
									}).done(function(){
										$('.js-asMarcas-remover').hide();
										obj.html(`<i class="iconify" data-icon="fluent:add-circle-24-regular"></i>`);
										obj.attr('data-loading',0);
									});

								}
							}
						})

						$('.js-asMarcas-table').on('click','.js-asMarcas-editar',function(){
							let id = $(this).attr('data-id');
							asMarcasEditar(id);
						});

						$('.aside-marca').on('click','.js-asMarcas-remover',function(){
							let obj = $(this);

							if(obj.attr('data-loading')==0) {

								let id = $('.js-asMarcas-id').val();
								swal({
									title: "Atenção",
									text: "Você tem certeza que deseja remover este registro?",
									type: "warning",
									showCancelButton: true,
									confirmButtonColor: "#DD6B55",
									confirmButtonText: "Sim!",
									cancelButtonText: "Não",
									closeOnConfirm:false,
									closeOnCancel: false }, 
									function(isConfirm){   
										if (isConfirm) {   

											obj.html(`<span class="iconify" data-icon="eos-icons:loading"></span>`);
											obj.attr('data-loading',1);
											let data = `ajax=asMarcasRemover&id=${id}`; 
											$.ajax({
												type:"POST",
												data:data,
												url:baseURLApiAside,
												success:function(rtn) {
													if(rtn.success) {
														$(`.js-asMarcas-id`).val(0);
														$(`.js-asMarcas-titulo`).val('');
														asMarcasAtualizar();
														swal.close();   
													} else if(rtn.error) {
														swal({title: "Erro!", text: rtn.error, type:"error", confirmButtonColor: "#424242"});
													} else {
														swal({title: "Erro!", text: "Algum erro ocorreu durante a remoção deste horário!", type:"error", confirmButtonColor: "#424242"});
													}
												},
												error:function(){
													swal({title: "Erro!", text: "Algum erro ocorreu durante a remoção deste horário!", type:"error", confirmButtonColor: "#424242"});
												}
											}).done(function(){
												$('.js-asMarcas-remover').hide();
												obj.html('<i class="iconify" data-icon="fluent:delete-24-regular"></i>');
												obj.attr('data-loading',0);
												$(`.js-asMarcas-submit`).html(`<i class="iconify" data-icon="fluent:add-circle-24-regular"></i>`);
											});
										} else {   
											swal.close();   
										} 
									});
							}
						});

					});
				</script>

				<section class="aside aside-marca">
					<div class="aside__inner1">

						<header class="aside-header">
							<h1>Marca</h1>
							<a href="javascript:;" class="aside-header__fechar aside-close"><i class="iconify" data-icon="fluent:dismiss-24-filled"></i></a>
						</header>

						<form method="post" class="aside-content form">
							<input type="hidden" class="js-asMarcas-id" />
							
							<dl>
								<dt>Título da Marca</dt>
								<dd>
									<input type="text" class="js-asMarcas-titulo" />
									<button type="button" class="js-asMarcas-submit button button_main" data-loading="0"><i class="iconify" data-icon="fluent:add-circle-24-regular"></i></button>
									<a href="javascript:;" class="button js-asMarcas-remover" data-loading="0" style="display:none;"><i class="iconify" data-icon="fluent:delete-24-regular"></i></a>
								</dd>
							</dl>
							<div class="list2" style="margin-top:2rem;">
									<table class="js-asMarcas-table">
										<thead>
											<tr>									
												<th>MARCA</th>
												<th></th>
											</tr>
										</thead>
										<tbody>								
										</tbody>
									</table>
								</div>
							</form>
						</form>
					</div>
				</section>


				<?php
			}

			// Categoria
			if(isset($apiConfig['categoria'])) {
				?>
				<script type="text/javascript">
					var asCategorias = [];

					const asCategoriasListar = (openAside) => {
						
						if(asCategorias) {
							$('.js-asCategorias-table tbody').html('');

							let atualizaCategoria = $('select.ajax-id_categoria')?1:0;
							let atualizaCategoriaId = 0;
							let categoriasDisabledIds = [];
							if(atualizaCategoria==1) {

								$('select.ajax-id_categoria option').each(function(index,el){
									if($(el).prop('disabled')===true) {
										categoriasDisabledIds.push($(el).val());
									}
								})
								atualizaCategoriaId=$('select.ajax-id_categoria').val();
								$('select.ajax-id_categoria').find('option').remove();
								$('select.ajax-id_categoria').append('<option value="">-</option>');
							}

							asCategorias.forEach(x=>{

								$(`.js-asCategorias-table tbody`).append(`<tr class="aside-open">
																	<td><h1>${x.titulo}</h1></td>
																	<td style="text-align:right;"><a href="javascript:;" class="button js-asCategorias-editar" data-id="${x.id}"><i class="iconify" data-icon="fluent:edit-24-regular"></i></a></td>
																</tr>`);

								if(atualizaCategoria==1) {
									dis=categoriasDisabledIds.includes(x.id)?' disabled':'';
									sel=(atualizaCategoriaId==x.id)?' selected':'';
									$('select.ajax-id_categoria').append(`<option value="${x.id}"${sel}${dis}>${x.titulo}</option>`);
								}

							});
							
							if(openAside===true) {
								$("#js-aside-asCategorias").fadeIn(100,function() {
									$("#js-aside-asCategorias .aside__inner1").addClass("active");
								});
							}

						} else {
							if(openAside===true) {
								$(".aside").fadeIn(100,function() {
										$(".aside .aside__inner1").addClass("active");
								});
							}
						}
					}

					const asCategoriasAtualizar = (openAside) => {	
						let data = `ajax=asCategoriasListar`;

						$.ajax({
							type:"POST",
							url:baseURLApiAside,
							data:data,
							success:function(rtn) {
								if(rtn.success) {
									asCategorias=rtn.regs;
									asCategoriasListar(openAside);
								}
							}
						})
					}
					
					const asCategoriasEditar = (id) => {
						let data = `ajax=asCategoriasEditar&id=${id}`;
						$.ajax({
							type:"POST",
							url:baseURLApiAside,
							data:data,
							success:function(rtn) {
								if(rtn.success) {
									reg=rtn.cnt

									$(`.js-asCategorias-id`).val(reg.id);
									$(`.js-asCategorias-titulo`).val(reg.titulo);
									$('.js-asCategorias-form').animate({scrollTop: 0},'fast');
									$('.js-asCategorias-submit').html(`<i class="iconify" data-icon="fluent:checkmark-12-filled"></i>`);
									$('.js-asCategorias-remover').show();

									$('.aside-content').animate({scrollTop: 0},'fast');

								} else if(rtn.error) {
									swal({title: "Erro!", text: rtn.error, type:"error", confirmButtonColor: "#424242"});
								} else {
									swal({title: "Erro!", text: "Algum erro ocorreu durante a edição deste registro!", type:"error", confirmButtonColor: "#424242"});
								}
							},
							error:function(){
								swal({title: "Erro!", text: "Algum erro ocorreu durante a edição deste registro!", type:"error", confirmButtonColor: "#424242"});
							}
						});
					}
					
					$(function(){
						asCategoriasAtualizar();

						$('.js-asCategorias-submit').click(function(){
							let obj = $(this);
							if(obj.attr('data-loading')==0) {

								let id = $(`.js-asCategorias-id`).val();
								let titulo = $(`.js-asCategorias-titulo`).val();

								if(titulo.length==0) {
									swal({title: "Erro!", text: "Digite a Marca", type:"error", confirmButtonColor: "#424242"});
								}  else {

									obj.html(`<span class="iconify" data-icon="eos-icons:loading"></span>`);
									obj.attr('data-loading',1);

									let data = `ajax=asCategoriasPersistir&id=${id}&titulo=${titulo}`;
								
									$.ajax({
										type:'POST',
										data:data,
										url:baseURLApiAside,
										success:function(rtn) {
											if(rtn.success) {
												asCategoriasAtualizar();	
												$(`.js-asCategorias-id`).val(0);
												$(`.js-asCategorias-titulo`).val(``);

											} else if(rtn.error) {
												swal({title: "Erro!", text: rtn.error, type:"error", confirmButtonColor: "#424242"});
											} else {
												swal({title: "Erro!", text: "Algum erro ocorreu! Tente novamente.", type:"error", confirmButtonColor: "#424242"});
											}
											
										},
										error:function() {
											swal({title: "Erro!", text: "Algum erro ocorreu! Tente novamente.", type:"error", confirmButtonColor: "#424242"});
										}
									}).done(function(){
										$('.js-asCategorias-remover').hide();
										obj.html(`<i class="iconify" data-icon="fluent:add-circle-24-regular"></i>`);
										obj.attr('data-loading',0);
									});

								}
							}
						})

						$('.js-asCategorias-table').on('click','.js-asCategorias-editar',function(){
							let id = $(this).attr('data-id');
							asCategoriasEditar(id);
						});

						$('.aside-categoria').on('click','.js-asCategorias-remover',function(){
							let obj = $(this);

							if(obj.attr('data-loading')==0) {

								let id = $('.js-asCategorias-id').val();
								swal({
									title: "Atenção",
									text: "Você tem certeza que deseja remover este registro?",
									type: "warning",
									showCancelButton: true,
									confirmButtonColor: "#DD6B55",
									confirmButtonText: "Sim!",
									cancelButtonText: "Não",
									closeOnConfirm:false,
									closeOnCancel: false }, 
									function(isConfirm){   
										if (isConfirm) {   

											obj.html(`<span class="iconify" data-icon="eos-icons:loading"></span>`);
											obj.attr('data-loading',1);
											let data = `ajax=asCategoriasRemover&id=${id}`; 
											$.ajax({
												type:"POST",
												data:data,
												url:baseURLApiAside,
												success:function(rtn) {
													if(rtn.success) {
														$(`.js-asCategorias-id`).val(0);
														$(`.js-asCategorias-titulo`).val('');
														asCategoriasAtualizar();
														swal.close();   
													} else if(rtn.error) {
														swal({title: "Erro!", text: rtn.error, type:"error", confirmButtonColor: "#424242"});
													} else {
														swal({title: "Erro!", text: "Algum erro ocorreu durante a remoção deste registro!", type:"error", confirmButtonColor: "#424242"});
													}
												},
												error:function(){
													swal({title: "Erro!", text: "Algum erro ocorreu durante a remoção deste registro!", type:"error", confirmButtonColor: "#424242"});
												}
											}).done(function(){
												$('.js-asMarcas-remover').hide();
												obj.html('<i class="iconify" data-icon="fluent:delete-24-regular"></i>');
												obj.attr('data-loading',0);
												$(`.js-asMarcas-submit`).html(`<i class="iconify" data-icon="fluent:add-circle-24-regular"></i>`);
											});
										} else {   
											swal.close();   
										} 
									});
							}
						});

					});
				</script>

				<section class="aside aside-categoria">
					<div class="aside__inner1">

						<header class="aside-header">
							<h1>Categoria</h1>
							<a href="javascript:;" class="aside-header__fechar aside-close"><i class="iconify" data-icon="fluent:dismiss-24-filled"></i></a>
						</header>

						<form method="post" class="aside-content form">
							<input type="hidden" class="js-asCategorias-id" />
							
							<dl>
								<dt>Título da Categoria</dt>
								<dd>
									<input type="text" class="js-asCategorias-titulo" />
									<button type="button" class="js-asCategorias-submit button button_main" data-loading="0"><i class="iconify" data-icon="fluent:add-circle-24-regular"></i></button>
									<a href="javascript:;" class="button js-asCategorias-remover" data-loading="0" style="display:none;"><i class="iconify" data-icon="fluent:delete-24-regular"></i></a>
								</dd>
							</dl>
							<div class="list2" style="margin-top:2rem;">
									<table class="js-asCategorias-table">
										<thead>
											<tr>									
												<th>CATEGORIA</th>
												<th></th>
											</tr>
										</thead>
										<tbody>								
										</tbody>
									</table>
								</div>
							</form>
						</form>
					</div>
				</section>
				<?php
			}

		// Paciente
			if(isset($apiConfig['paciente'])) {
				?>
				<script type="text/javascript">

					$(function(){

						$('.js-asPaciente-submit').click(function(){
							let obj = $(this);
							if(obj.attr('data-loading')==0) {

								let nome = $(`.js-asPaciente-nome`).val();
								let telefone1 = $(`.js-asPaciente-telefone1`).val();
								let cpf = $(`.js-asPaciente-cpf`).val();
								let indicacao_tipo = $(`.js-asPaciente-indicacao_tipo`).val();
								let indicacao = $(`.js-asPaciente-indicacao`).val();
							

								if(nome.length==0) {
									swal({title: "Erro!", text: "Digite o Nome do Paciente", type:"error", confirmButtonColor: "#424242"});
									$('.js-asPaciente-nome').addClass('erro');
								} else if(telefone1.length==0) {
									swal({title: "Erro!", text: "Digite o Whatsapp do Paciente", type:"error", confirmButtonColor: "#424242"});
									$('.js-asPaciente-telefone1').addClass('erro');
								}  else {

									obj.html(`<span class="iconify" data-icon="eos-icons:loading"></span>`);
									obj.attr('data-loading',1);

									let data = `ajax=asPacientePersistir&nome=${nome}&telefone1=${telefone1}&cpf=${cpf}&indicacao_tipo=${indicacao_tipo}&indicacao=${indicacao}`;

									data = {
										'ajax':'asPacientePersistir',
										'nome':nome,
										'telefone1':telefone1,
										'cpf':cpf,
										'indicacao_tipo':indicacao_tipo,
										'indicacao':indicacao
									}
									
									$.ajax({
										type:'POST',
										data:data,
										url:baseURLApiAside,
										success:function(rtn) {
											if(rtn.success) {

												$(`.js-asPaciente-nome`).val(``);
												$(`.js-asPaciente-telefone1`).val(``);
												$(`.js-asPaciente-cpf`).val(``);
												$(`.js-asPaciente-indicacao_tipo`).val(``);
												$(`.js-asPaciente-indicacao`).val(``);

												$('.ajax-id_paciente').append(`<option value="${rtn.id_paciente}" selected>${rtn.nome}</option>`);
												$('.aside-paciente .aside-close').click();

												$.ajax({
													type:"POST",
													data:`ajax=biCategorizacao`,
													url:baseURLApiAside
												})

											} else if(rtn.error) {
												swal({title: "Erro!", text: rtn.error, type:"error", confirmButtonColor: "#424242"});
											} else {
												swal({title: "Erro!", text: "Algum erro ocorreu! Tente novamente.", type:"error", confirmButtonColor: "#424242"});
											}
											
										},
										error:function() {
											swal({title: "Erro!", text: "Algum erro ocorreu! Tente novamente.", type:"error", confirmButtonColor: "#424242"});
										} 
									}).done(function(){
										obj.html(`<i class="iconify" data-icon="fluent:add-circle-24-regular"></i>`);
										obj.attr('data-loading',0);
									});

								}
							}
						})

						$('.js-asTag-submit').click(function(){
							let obj = $(this);
							let objHTMLAntigo = $(this).html();

							if(obj.attr('data-loading')==0) {

								let id_agenda = $('#js-aside-edit input[name=id]').val();
								let id = $('input[name=id_tag]').val();
								let titulo = $(`.js-asTag-titulo`).val();
								let cor = $(`.js-asTag-cor`).val();

								if(titulo.length==0) {
									swal({title: "Erro!", text: "Digite o Título da Tag", type:"error", confirmButtonColor: "#424242"});
									$('.js-asTag-titulo').addClass('erro');
								} else {
									obj.html(`<span class="iconify" data-icon="eos-icons:loading"></span>`);
									obj.attr('data-loading',1);

									let data = `ajax=asTagPersistir&titulo=${titulo}&cor=${cor}&id=${id}&id_agenda=${id_agenda}`;

									$.ajax({
										type:'POST',
										data:data,
										url:baseURLApiAside,
										success:function(rtn) {
											if(rtn.success) {

												$(`.js-asTag-titulo`).val(``);
												$('.js-tags').empty();

												if(rtn.tags.length>0) {
													rtn.tags.forEach(x=>{
														$('.js-tags').append(`<option value="${x.id}">${x.titulo}</option>`);
													});
												}

												if(rtn.tags_selected.length>0) {
													let cont = 0;
													rtn.tags_selected.forEach(idTag=>{
														$('.js-tags').find('option[value='+idTag+']').prop('selected',true);

														cont++;
														if(cont==rtn.tags_selected.length) {
															$('.js-tags').trigger('chosen:updated');
														}
													})
												} else {
													$('.js-tags').trigger('chosen:updated');
												}
												
												tagsListar();
												//$('.aside-tag .aside-close').click();

											} else if(rtn.error) {
												swal({title: "Erro!", text: rtn.error, type:"error", confirmButtonColor: "#424242"});
											} else {
												swal({title: "Erro!", text: "Algum erro ocorreu! Tente novamente.", type:"error", confirmButtonColor: "#424242"});
											}
											
										},
										error:function() {
											swal({title: "Erro!", text: "Algum erro ocorreu! Tente novamente.", type:"error", confirmButtonColor: "#424242"});
										} 
									}).done(function(){
										obj.html(objHTMLAntigo);
										obj.attr('data-loading',0);
									}); 
								}
							}
						});
					});
				</script>

				<!-- Aside Paciente -->
				<section class="aside aside-paciente">
					<div class="aside__inner1">

						<header class="aside-header">
							<h1>Novo Paciente</h1>
							<a href="javascript:;" class="aside-header__fechar aside-close"><i class="iconify" data-icon="fluent:dismiss-24-filled"></i></a>
						</header>

						<form method="post" class="aside-content form js-asPaciente-form">

							<section class="filter" style="margin-bottom:0;">
								<div class="filter-group"></div>
								<div class="filter-group">
									<div class="filter-form form">
										<dl>
											<dd><button type="button" class="button button_main js-asPaciente-submit" data-loading="0"><i class="iconify" data-icon="fluent:checkmark-12-filled"></i> <span>Salvar</span></button></dd>
										</dl>
									</div>								
								</div>
							</section>

							<dl>
								<dt>Nome</dt>
								<dd>
									<input type="text" class="js-asPaciente-nome" />
								</dd>
							</dl>

							<div class="colunas2">

								<dl>
									<dt>Whatsapp</dt>
									<dd class="form-comp">
										<span class="js-country">BR</span><input type="text" class="js-asPaciente-telefone1" />
									</dd>
								</dl>
								<dl>
									<dt>CPF</dt>
									<dd>
										<input type="text" class="js-asPaciente-cpf cpf" />
									</dd>
									<dd class="js-cpf-dd" style="color:var(--vermelho);font-size: 12px;padding-top:5px;"></dd>
								</dl>
							</div>

							<script type="text/javascript">
								$(function(){

									$('input.js-asPaciente-cpf').change(function(){
										let cpf = $(this).val();
										$('.js-cpf-dd').hide();

										if(cpf.length==14) {
											if(validarCPF(cpf)) {

												$('.js-cpf-dd').hide();
											} else {
												$('.js-cpf-dd').html(`<span class="iconify" data-icon="dashicons:warning" data-inline="true"></span>CPF inválido!`).show();;
											}
										

											let data = `ajax=consultaCPF&cpf=${cpf}`
											$.ajax({
												type:"POST",
												url:"pg_pacientes.php",
												data:data,
												success:function(rtn) {
													if(rtn.success) {
														if(rtn.pacientes && rtn.pacientes>0) {
															$('.js-cpf-dd').html(`<span class="iconify" data-icon="dashicons:warning" data-inline="true"></span>Já existe cadastro com este CPF!`).show();;
														} else {
														}
													} else if(rtn.error) {

													} else {

													}
												},
												error:function(){

												}
											})
										}

									})
								})
							</script>

							<div class="colunas2">

								<dl>
									<dt>Tipo Indicação</dt>
									<dd>
										<select class="js-asPaciente-indicacao_tipo">
											<option value="">-</option>
											<?php
											//foreach($_pacienteIndicacoes as $v) echo '<option value="'.$v->id.'"'.($values['indicacao_tipo']==$v->id?' selected':'').' data-id="'.$v->id.'">'.utf8_encode($v->titulo).'</option>';
											
												foreach($optTipoIndicacao as $k=>$v) echo '<option value="'.$k.'"'.($values['indicacao_tipo']==$k?' selected':'').'>'.$v.'</option>';
											?>
										</select>
									</dd>
								</dl>
								<dl>
									<dt>Indicação</dt>
									<dd>
										<input type="text" class="js-asPaciente-indicacao" />

									</dd>
								</dl>
							</div>
						</form>
					</div>
				</section>

				<!-- Aside Tag -->
				<section class="aside aside-tag aside_sub">
					<div class="aside__inner1">

						<script type="text/javascript">
							$(function(){
							});
						</script>

						<header class="aside-header">
							<h1>Tags</h1>
							<a href="javascript:;" class="aside-header__fechar aside-close"><i class="iconify" data-icon="fluent:dismiss-24-filled"></i></a>
						</header>

						<form method="post" class="aside-content form js-asTag-form">
							<input type="hidden" name="id_tag" value="0" />

							<section class="filter" style="margin-bottom:0;">
								<div class="filter-group"></div>
								<div class="filter-group">
									<div class="filter-form form">
										<dl>
											<dd><button type="button" class="button button_main js-asTag-submit" data-loading="0"><i class="iconify" data-icon="fluent:checkmark-12-filled"></i> <span>Salvar</span></button></dd>
										</dl>
									</div>								
								</div>
							</section>

							<div class="colunas4">
								<dl class="dl3">
									<dt>Título</dt>
									<dd>
										<input type="text" class="js-asTag-titulo" />
									</dd>
								</dl>
								<dl>
									<dt>Cor</dt>
									<dd><input type="color" class="js-asTag-cor" value="#c18c6a" /></dd>
								</dl>
							</div>

							<div class="list2" style="margin-top:2rem;">
								<table class="js-tags-table">
									<thead>
										<tr>
											<th>TÍTULO</th>
											<th>COR</th>
											<th></th>
										</tr>
									</thead>
									<tbody>
									</tbody>
								</table>
							</div>

						</form>
					</div>
				</section>

				<!-- Aside Reagendamento -->
				<section class="aside aside-queroReagendar aside_sub" id="js-aside-queroReagendar">
					<div class="aside__inner1">

						<header class="aside-header">
							<h1>Quero Reagendar</h1>
							<a href="javascript:;" class="aside-header__fechar aside-close"><i class="iconify" data-icon="fluent:dismiss-24-filled"></i></a>
						</header>

						<form method="post" class="aside-content form" onsubmit="return false;">
							<input type="hidden" class="js-reagendar-id_agenda" value="0" />
							<input type="hidden" class="js-id_paciente" value="0" />

							<section class="header-profile">
								<img src="img/ilustra-usuario.jpg" alt="" width="60" height="60" class="header-profile__foto js-foto" />
								<div class="header-profile__inner1">
									<h1><a href="" target="_blank" class="js-nome"></a></h1>
									<div>
										<p class="js-statusBI"></p>
										<p class="js-idade"></p>
										<p class="js-periodicidade"></p>
										<p class="js-musica"></p>
									</div>
								</div>
							</section>

							<div class="js-agendamento">
								<section class="filter">
									<div class="button-group">
									</div>
									<div class="filter-group">
										<div class="filter-form form">
											<dl>
												<dd><button class="button button_main js-salvar" data-loading="0"><i class="iconify" data-icon="fluent:checkmark-12-filled"></i> <span>Salvar</span></button></dd>
											</dl>
										</div>								
									</div>
								</section>

								<div class="js-ag-agendamento-queroReagendar">
									<div class="colunas3">
										<dl>
											<dt>Data</dt>
											<dd class="form-comp"><span><i class="iconify" data-icon="fluent:calendar-ltr-24-regular"></i></span><input type="tel" name="agenda_data" class="data datecalendar" /></dd>
										</dl>
									
										<dl>
											<dt>Duração</dt>
											<dd class="form-comp form-comp_pos">
												<?php /*<input type="tel" name="agenda_duracao" class="" />*/?>
												<select name="agenda_duracao">
													<option value="">-</option>
													<?php
													foreach($optAgendaDuracao as $v) {
														echo '<option value="'.$v.'">'.$v.'</option>';
													}
													?>
												</select>
												<span>min</span>
											</dd>
										</dl>

										<dl>
											<dt>Consultório</dt>
											<dd>
												<select name="id_cadeira">
													<option value=""></option>
													<?php
													foreach($_cadeiras as $p) {
														if($p->lixo==1) continue;
														echo '<option value="'.$p->id.'"'.($values['id_cadeira']==$p->id?' selected':'').'>'.utf8_encode($p->titulo).'</option>';
													}
													?>
												</select>
											</dd>
										</dl>
									</div>
									<div class="colunas3">
										<dl class="dl2">
											<dt>Profissionais</dt>
											<dd>
												<select class="js-profissionais2 js-select-profissionais">
													<option value=""></option>
													<?php
													foreach($_profissionais as $p) {
														if($p->check_agendamento==0 or $p->contratacaoAtiva==0) continue;
														echo '<option value="'.$p->id.'">'.utf8_encode($p->nome).'</option>';
													}
													?>
												</select>
											</dd>
										</dl>
										<dl>
											<dt>Hora</dt>
											<dd class="form-comp">
												<span><i class="iconify" data-icon="fluent:clock-24-regular"></i></span>
												<select name="agenda_hora">
													<option value="">Selecione o horário</option>
												</select>
											</dd>
										</dl>
									</div>

									<dl>
										<dt>Observações</dt>
										<dd>
											<textarea name="obs" style="height:80px;"></textarea>
										</dd>
									</dl>
								</div>
							</div>

						</form>
					</div>
				</section>

				<?php
			}

		// Pacientes Relacionamento
			if(isset($apiConfig['pacienteRelacionamento'])) {
				$_historicoStatus=array();
				$sql->consult($_p."pacientes_historico_status","*","");
				while($x=mysqli_fetch_object($sql->mysqry)) {
					$_historicoStatus[$x->id]=$x;
				}
				?>
					<script type="text/javascript">

						const pacienteRelacionamentoPopula = (obj,rtn,filtro,lista) => {


							if(filtro=="excluidos") {
								obj.find('textarea[name=motivo]').val(rtn.paciente.excluidoMotivo).prop('disabled',true);

								if(rtn.paciente.excluidoData) {

									listaExt='';
									if(lista.length>0) listaExt=` da lista <b>${lista}</b>`;
									obj.find('.js-excluido-desc').html(`<dd style="font-size:12px;color:#666">Excluído em <b>${rtn.paciente.excluidoData}</b> por <b>${rtn.paciente.excluidoUsuario}</b>${listaExt}</dd>`)
								}
							}

							obj.find('.js-nome').html(`${rtn.paciente.nome} <i class="iconify" data-icon="fluent:share-screen-person-overlay-20-regular" style="color:var(--cinza4)"></i>`).attr('href',`pg_pacientes_resumo.php?id_paciente=${rtn.paciente.id}`);

							if(rtn.paciente.ft && rtn.paciente.ft.length>0) {
								obj.find('.js-foto').attr('src',rtn.paciente.ft);
							} else {
								obj.find('.js-foto').attr('src','img/ilustra-usuario.jpg');
							}

							obj.find('.js-whatsapp-numero').val(rtn.paciente.telefone1);

							if(rtn.paciente.idade && rtn.paciente.idade>0) {
								obj.find('.js-idade').html(rtn.paciente.idade+(rtn.paciente.idade>=2?' anos':' ano')).show();;
							} else {
								obj.find('.js-idade').html(``).hide();;
							}

							if(rtn.paciente.periodicidade && rtn.paciente.periodicidade.length>0) {
								obj.find('.js-periodicidade').html(`Periodicidade: ${rtn.paciente.periodicidade}`);
							} else {
								obj.find('.js-periodicidade').html(`Periodicidade: -`);
							}

							if(rtn.paciente.agendou_dias && rtn.paciente.agendou_dias.length>0) {
								obj.find('.js-ultimoAtendimento').html(`Atendido há ${rtn.paciente.agendou_dias}`);
							} else {
								obj.find('.js-ultimoAtendimento').html(`Nunca foi atendido(a)`);
							}

							if(rtn.paciente.musica && rtn.paciente.musica.length>0) {
								obj.find('.js-musica').html(`<i class="iconify" data-icon="bxs:music"></i> ${rtn.paciente.musica}`);
							} else {
								obj.find('.js-musica').html(``);
							}

							obj.find('input[name=id_paciente]').val(rtn.paciente.id);

							$('input[name=telefone1],.js-asPaciente-telefone1').mobilePhoneNumber({allowPhoneWithoutPrefix: '+55'}).bind('country.mobilePhoneNumber', function(echo, country) {
								let countryOut = country || '  ';
								$(this).parent().parent().find('.js-country').html(countryOut);
							}).trigger('keyup');


							obj.find('.js-whatsappHistorico').html(``);

							if(rtn.paciente.historicoWts && rtn.paciente.historicoWts.length>0) {

								rtn.paciente.historicoWts.forEach(x=>{

									let status = `<span class="iconify" data-icon="bxs:hourglass" data-inline="true"></span> Aguardando resposta`;
									let cor = `var(--cinza5)`;

									if(x.resposta_sim==1) {
										status = '<span class="iconify" data-icon="el:ok-circle" data-inline="true"></span> Confirmado';
										cor = 'var(--verde)';
									} else if(x.resposta_nao==1) {
										status = '<span class="iconify" data-icon="ic:outline-cancel" data-inline="true"></span> Não Confirmado';
										cor = 'var(--vermelho)';
									}
									let html = `<div class="history2-item">
													<aside>
														<span style="background:${cor}"><i class="iconify" data-icon="mdi:chat-processing-outline" ></i></span>			
													</aside>

													<article>
														<div class="history2-main">
															<div>
																<h1>${x.dt}</h1>	
																<p style="color:${cor}">${status}</p>											
															</div><br />
															<p style="font-size:12px;color:#666">
															${x.msg}					
															</p>														
														</div>
													</article>
												</div>`;

									obj.find('.js-whatsappHistorico').append(html);
								})

							} else {
								obj.find('.js-whatsappHistorico').html(`<center>Nenhum Whatsapp enviado</center>`);
							}

							obj.find('.js-ag-historico section').find('.history2-item').remove();

							if(rtn.paciente.historico && rtn.paciente.historico.length>0) {
								rtn.paciente.historico.forEach(x=>{
									let html = '';
									if(x.ev=="observacao") {
										html = `<div class="history2-item">
													<aside>
														<span><i class="iconify" data-icon="mdi:chat-processing-outline"></i></span>			
													</aside>

													<article>
														<div class="history2-main">
															<div>
																<h1>${x.dt}</h1>
																${x.col}												
															</div>
															<strong>${x.obs}</strong>
															${x.descricao?`<p>${x.descricao}</p>`:''}																			
														</div>
													</article>
												</div>`;
									} else {

										let profissionaisHTML = '';


										if(x.prof && x.prof.length>0) {
											x.prof.forEach(p=>{
												profissionaisHTML+=`<div class="badge-prof" style="background:${p.cor}">${p.iniciais}</div>`;
											})
										}

										html = `<div class="history2-item">
													<aside>
														<span style="background:${x.icC};"><i class="iconify" data-icon="${x.ic}"></i></span>
														
													</aside>

													<article>
														<div class="history2-main">
															<div>
																<h1>${x.dt}</h1>
																${x.cad}
																${profissionaisHTML}
															</div>
															
														</div>
													</article>
												</div>`;
									}


									obj.find('.js-ag-historico section').append(html);
								})
							}

							if(filtro=="excluidos") {
							} else {
								$('.js-btn-acao-queroAgendar').click();
							}
						}

						const pacienteRelacionamento = (obj) => {//id_paciente,filtro) => {
							id_paciente=obj.attr('data-id_paciente');
							filtro=obj.attr('data-filtro');
							if(filtro=="excluidos") {
								let data = `ajax=asRelacionamentoPacienteExcluido&id_paciente=${id_paciente}`;
								lista=obj.attr('data-lista');					
								$.ajax({
									type:'POST',
									data:data,
									url:baseURLApiAside,
									success:function(rtn) {
										if(rtn.success) {

											let obj = $("#js-aside-pacienteRelacionamentoExcluido");

											pacienteRelacionamentoPopula(obj,rtn,filtro,lista);
											
											obj.fadeIn(100,function() {
												obj.find('.js-tab').find('a:eq(0)').click();
												obj.find('.aside__inner1').addClass("active");
											});


			 
										} else if(rtn.error) {
											swal({title: "Erro!", text: rtn.error, type:"error", confirmButtonColor: "#424242"});
										} else {
											swal({title: "Erro!", text: "Algum erro ocorreu! Tente novamente.", type:"error", confirmButtonColor: "#424242"});
										}
										
									},
									error:function() {
										swal({title: "Erro!", text: "Algum erro ocorreu! Tente novamente.", type:"error", confirmButtonColor: "#424242"});
									} 
								}).done(function(){
									//obj.html(`<i class="iconify" data-icon="fluent:add-circle-24-regular"></i>`);
									//obj.attr('data-loading',0);
								});

							} else {

								let data = `ajax=asRelacionamentoPaciente&id_paciente=${id_paciente}`;
											
								$.ajax({
									type:'POST',
									data:data,
									url:baseURLApiAside,
									success:function(rtn) {
										if(rtn.success) {
											let obj = $("#js-aside-pacienteRelacionamento");

											pacienteRelacionamentoPopula(obj,rtn,filtro,'');

											obj.fadeIn(100,function() {
												obj.find('.js-profissionais').chosen();
												obj.find('.js-tab').find('a:eq(0)').click();
												obj.find('.aside__inner1').addClass("active");
											});

											obj.find('input[name=agenda_data]').datetimepicker({
												timepicker:false,
												format:'d/m/Y',
												scrollMonth:false,
												scrollTime:false,
												scrollInput:false,
											}).css('background','');

											obj.find('input[name=agenda_hora]').datetimepicker({
												  datepicker:false,
											      format:'H:i',
											      pickDate:false
											}).css('background','');

											
			 
										} else if(rtn.error) {
											swal({title: "Erro!", text: rtn.error, type:"error", confirmButtonColor: "#424242"});
										} else {
											swal({title: "Erro!", text: "Algum erro ocorreu! Tente novamente.", type:"error", confirmButtonColor: "#424242"});
										}
										
									},
									error:function() {
										swal({title: "Erro!", text: "Algum erro ocorreu! Tente novamente.", type:"error", confirmButtonColor: "#424242"});
									} 
								}).done(function(){
									//obj.html(`<i class="iconify" data-icon="fluent:add-circle-24-regular"></i>`);
									//obj.attr('data-loading',0);
								});
							}
						}

						$(function(){
							$('.js-tab a').click(function() {
								$(".js-tab a").removeClass("active");
								$(this).addClass("active");							
							})
						})
					</script>

					<section class="aside aside-pacienteRelacionamento" id="js-aside-pacienteRelacionamento">
						
						<div class="aside__inner1">

							<header class="aside-header">
								<h1>Relacionamento com Paciente</h1>
								<a href="javascript:;" class="aside-header__fechar aside-close"><i class="iconify" data-icon="fluent:dismiss-24-filled"></i></a>
							</header>

							<form method="post" class="aside-content form" onsubmit="return false">
								<input type="hidden" name="id_paciente" />
								<input type="hidden" name="tipo" value="queroAgendar" />
								<section class="header-profile">
									<img src="img/ilustra-usuario.jpg" alt="" width="60" height="60" class="js-foto header-profile__foto" />
									<div class="header-profile__inner1">
										<h1><a href="" target="_blank" class="js-nome"></a></h1>
										<div>
											<p class="js-idade"></p>
											<p class="js-periodicidade"></p>
											<p class="js-ultimoAtendimento"></p>
										</div>
									</div>
								</section>

								<script>
								
									$(function() {
										$('#js-aside-pacienteRelacionamento').on('change','select[name=agenda_duracao], select[name=id_cadeira],  select.js-profissionais, input[name=agenda_data]',function(){
											horarioDisponivel(0,$('#js-aside-pacienteRelacionamento'));
											
										});

										$('#js-aside-pacienteRelacionamento .js-btn-acao').click(function(){
											$('#js-aside-pacienteRelacionamento .js-btn-acao').removeClass('active');
											$(this).addClass('active');

											if($(this).attr('data-tipo')=="queroAgendar") {
												$('#js-aside-pacienteRelacionamento .js-ag-agendamento-excluir').hide();
												$('#js-aside-pacienteRelacionamento .js-ag-agendamento-naoQueroAgendar').hide();
												$('#js-aside-pacienteRelacionamento .js-ag-agendamento-queroAgendar').show();
												$('#js-aside-pacienteRelacionamento input[name=tipo]').val('queroAgendar');
											} else if($(this).attr('data-tipo')=="excluir")  {
												$('#js-aside-pacienteRelacionamento .js-ag-agendamento-excluir').show();
												$('#js-aside-pacienteRelacionamento .js-ag-agendamento-naoQueroAgendar').hide();
												$('#js-aside-pacienteRelacionamento .js-ag-agendamento-queroAgendar').hide();
												$('#js-aside-pacienteRelacionamento input[name=tipo]').val('excluir');
											} else {
												$('#js-aside-pacienteRelacionamento .js-ag-agendamento-excluir').hide();
												$('#js-aside-pacienteRelacionamento .js-ag-agendamento-naoQueroAgendar').show();
												$('#js-aside-pacienteRelacionamento .js-ag-agendamento-queroAgendar').hide();
												$('#js-aside-pacienteRelacionamento input[name=tipo]').val('naoQueroAgendar');
											}
										});

										$('#js-aside-pacienteRelacionamento .js-btn-whatsapp-enviar').click(function(){

											let obj = $(this);
											let objTextoAntigo = $(this).html();

											if(obj.attr('data-loading')==0) {

												obj.attr('data-loading',1);
												obj.html(`<span class="iconify" data-icon="eos-icons:loading"></span>`);
												let id_paciente = $('#js-aside-pacienteRelacionamento input[name=id_paciente]').val();

												let data = `ajax=asRelacionamentoPacienteEnviarWhatsapp&id_paciente=${id_paciente}`;
											
												$.ajax({
														type:'POST',
														data:data,
														url:baseURLApiAside,
														success:function(rtn) {
															if(rtn.success) {

																$.ajax({
																	type:"POST",
																	url:baseURLApiAside,
																	data:'ajax=asRelacionamentoPacienteDisparaWhatsapp'
																});
																

																swal({title: "Sucesso!", text: 'Mensagem enviada com sucesso!', type:"success", confirmButtonColor: "#424242"},function(){
																});

															} else if(rtn.error) {
																swal({title: "Erro!", text: rtn.error, type:"error", confirmButtonColor: "#424242"});
															} else {
																swal({title: "Erro!", text: "Algum erro ocorreu! Tente novamente.", type:"error", confirmButtonColor: "#424242"});
															}
															
														},
														error:function() {
															swal({title: "Erro!", text: "Algum erro ocorreu! Tente novamente.", type:"error", confirmButtonColor: "#424242"});
														} 
												}).done(function(){
													obj.html(objTextoAntigo);
													obj.attr('data-loading',0);
												});

											}

										})

										$('#js-aside-pacienteRelacionamento .js-ag-agendamento .js-salvar').click(function(){
											let tipo = $('#js-aside-pacienteRelacionamento input[name=tipo]').val();
											let id_paciente = $('#js-aside-pacienteRelacionamento input[name=id_paciente]').val();
									
											if(tipo=="queroAgendar") {
												let agenda_data = $('#js-aside-pacienteRelacionamento input[name=agenda_data]').val();
												let agenda_duracao = $('#js-aside-pacienteRelacionamento select[name=agenda_duracao]').val();
												let id_cadeira = $('#js-aside-pacienteRelacionamento select[name=id_cadeira]').val();
												let id_profissional = $('#js-aside-pacienteRelacionamento select.js-profissionais').val();
												let agenda_hora = $('#js-aside-pacienteRelacionamento select[name=agenda_hora]').val();
												let obs = $('#js-aside-pacienteRelacionamento textarea[name=obs]').val();
												let erro = '';

												if(agenda_data.length==0) erro='Defina a <b>Data do Agendamento</b>';
												else if(agenda_duracao.length==0) erro='Defina a <b>Duração de Agendamento</b>';
												else if(id_cadeira.length==0) erro='Defina o <b>Consultório do Agendamento</b>';
												else if(id_profissional.length==0) erro='Defina o <b>Profissional do Agendamento</b>';
												else if(agenda_hora.length==0) erro='Defina a <b>Hora do Agendamento</b>';

												if(erro.length==0) {

													let obj = $(this);
													let obHTMLAntigo = $(this).html();

													if(obj.attr('data-loading')==0) {
														
														obj.html(`<span class="iconify" data-icon="eos-icons:loading"></span>`);
														obj.attr('data-loading',1);

														let data = `ajax=asRelacionamentoPacienteQueroAgendar&id_paciente=${id_paciente}&agenda_data=${agenda_data}&agenda_duracao=${agenda_duracao}&id_cadeira=${id_cadeira}&id_profissional=${id_profissional}&agenda_hora=${agenda_hora}&obs=${obs}`;

														data = {
															'ajax':'asRelacionamentoPacienteQueroAgendar',
															'id_paciente':id_paciente,
															'agenda_data':agenda_data,
															'agenda_duracao':agenda_duracao,
															'id_cadeira':id_cadeira,
															'id_profissional':id_profissional,
															'agenda_hora':agenda_hora,
															'obs':obs,
														}
														$.ajax({
																type:'POST',
																data:data,
																url:baseURLApiAside,
																success:function(rtn) {
																	if(rtn.success) {
																		$('#js-aside-pacienteRelacionamento input[name=agenda_data]').val('');
																		$('#js-aside-pacienteRelacionamento select[name=agenda_duracao]').val('');
																		$('#js-aside-pacienteRelacionamento select[name=id_cadeira]').val('');
																		$('#js-aside-pacienteRelacionamento select.js-profissionais').val('');
																		$('#js-aside-pacienteRelacionamento select[name=agenda_hora]').val('');
																		$('#js-aside-pacienteRelacionamento aside-close').click();
																		atualizaValorListasInteligentes();
																		swal({title: "Sucesso!", text: 'Agendamento realizado com sucesso!', type:"success", confirmButtonColor: "#424242"},function(){
																		});

																	} else if(rtn.error) {
																		swal({title: "Erro!", text: rtn.error, type:"error", confirmButtonColor: "#424242"});
																	} else {
																		swal({title: "Erro!", text: "Algum erro ocorreu! Tente novamente.", type:"error", confirmButtonColor: "#424242"});
																	}
																	
																},
																error:function() {
																	swal({title: "Erro!", text: "Algum erro ocorreu! Tente novamente.", type:"error", confirmButtonColor: "#424242"});
																} 
														}).done(function(){
															obj.html(obHTMLAntigo);
															obj.attr('data-loading',0);
														});


													}

												} else {
													swal({title: "Erro!", text: erro, html:true, type:"error", confirmButtonColor: "#424242"});
												}
											} else if(tipo=="naoQueroAgendar") {
												let status = $('#js-aside-pacienteRelacionamento select[name=id_status]').val();
												let obs = $('#js-aside-pacienteRelacionamento textarea[name=obs]').val();
												let erro = '';

												if(status.length==0) erro = 'Defina o <b>Status</b>';
												
												if(erro.length==0) {
													let obj = $(this);
													let obHTMLAntigo = $(this).html();

													if(obj.attr('data-loading')==0) {
														
														obj.html(`<span class="iconify" data-icon="eos-icons:loading"></span>`);
														obj.attr('data-loading',1);

														let data = `ajax=asRelacionamentoPacienteNaoQueroAgendar&id_paciente=${id_paciente}&id_status=${status}&obs=${obs}`;

														data = {
															'ajax':'asRelacionamentoPacienteNaoQueroAgendar',
															'id_paciente':id_paciente,
															'id_status':status,
															'obs':obs
														}
														$.ajax({
																type:'POST',
																data:data,
																url:baseURLApiAside,
																success:function(rtn) {
																	if(rtn.success) {
																		$('#js-aside-pacienteRelacionamento select[name=id_status]').val('');
																		$('#js-aside-pacienteRelacionamento textarea[name=obs]').val('');

																		atualizaValorListasInteligentes();
																		swal({title: "Sucesso!", text: 'Observação cadastrada realizado com sucesso!', type:"success", confirmButtonColor: "#424242"},function(){
																			$('.aside-close').click();
																		});

																	} else if(rtn.error) {
																		swal({title: "Erro!", text: rtn.error, type:"error", confirmButtonColor: "#424242"});
																	} else {
																		swal({title: "Erro!", text: "Algum erro ocorreu! Tente novamente.", type:"error", confirmButtonColor: "#424242"});
																	}
																	
																},
																error:function() {
																	swal({title: "Erro!", text: "Algum erro ocorreu! Tente novamente.", type:"error", confirmButtonColor: "#424242"});
																} 
														}).done(function(){
															obj.html(obHTMLAntigo);
															obj.attr('data-loading',0);
														});


													}
												} else {
													swal({title: "Erro!", text: erro, html:true, type:"error", confirmButtonColor: "#424242"});
												}
											} else if(tipo=="excluir") {

												let motivo = $('#js-aside-pacienteRelacionamento textarea[name=motivo]').val();
												let erro = '';

												if(motivo.length==0) erro = 'Defina o <b>Motivo</b>';
												
												if(erro.length==0) {
													let obj = $(this);
													let obHTMLAntigo = $(this).html();

													if(obj.attr('data-loading')==0) {
														
														obj.html(`<span class="iconify" data-icon="eos-icons:loading"></span>`);
														obj.attr('data-loading',1);

														let data = `ajax=asRelacionamentoPacienteExcluir&id_paciente=${id_paciente}&motivo=${motivo}`;
														data = {
															'ajax':'asRelacionamentoPacienteExcluir',
															'id_paciente':id_paciente,
															'motivo':motivo
														}
														$.ajax({
																type:'POST',
																data:data,
																url:baseURLApiAside,
																success:function(rtn) {
																	if(rtn.success) {
																		$('#js-aside-pacienteRelacionamento textarea[name=motivo]').val('');

																		atualizaValorListasInteligentes();
																		swal({title: "Sucesso!", text: 'Paciente excluído com sucesso!', type:"success", confirmButtonColor: "#424242"},function(){
																			$('.aside-close').click();
																		});

																	} else if(rtn.error) {
																		swal({title: "Erro!", text: rtn.error, type:"error", confirmButtonColor: "#424242"});
																	} else {
																		swal({title: "Erro!", text: "Algum erro ocorreu! Tente novamente.", type:"error", confirmButtonColor: "#424242"});
																	}
																	
																},
																error:function() {
																	swal({title: "Erro!", text: "Algum erro ocorreu! Tente novamente.", type:"error", confirmButtonColor: "#424242"});
																} 
														}).done(function(){
															obj.html(obHTMLAntigo);
															obj.attr('data-loading',0);
														});


													}
												} else {
													swal({title: "Erro!", text: erro, html:true, type:"error", confirmButtonColor: "#424242"});
												}
											}

		 
										});

										$('.js-btn-acao-queroAgendar').click();
									});
								</script>
								<section class="tab tab_alt js-tab">
									<a href="javascript:;" onclick="$('.js-ag').hide(); $('.js-ag-agendamento').show();" class="active">Agendamento</a>
									<?php /*<a href="javascript:;" onclick="$('.js-ag').hide(); $('.js-ag-whatsapp').show();">Whatsapp</a>*/?>			
									<a href="javascript:;" onclick="$('.js-ag').hide(); $('.js-ag-historico').show();">Histórico</a>					
								</section>
								
								<div class="js-ag js-ag-agendamento">

									<section class="filter">
										<div class="button-group">
											<a href="javascript:;" class="js-btn-acao js-btn-acao-queroAgendar button active" data-tipo="queroAgendar"><span>Quero agendar</span></a>
											<a href="javascript:;" class="js-btn-acao button" data-tipo="naoQueroAgendar"><span>Não consegui agendar</span></a>
											<a href="javascript:;" class="js-btn-acao button" data-tipo="excluir"><span>Excluir da lista</span></a>
										</div>
										<div class="filter-group">
											<div class="filter-form form">
												<dl>
													<dd><button class="button button_main js-salvar" data-loading="0"><i class="iconify" data-icon="fluent:checkmark-12-filled"></i> <span>Salvar</span></button></dd>
												</dl>
											</div>								
										</div>
									</section>

									<div class="js-ag-agendamento-queroAgendar">
										<div class="colunas3">
											<dl>
												<dt>Data</dt>
												<dd class="form-comp"><span><i class="iconify" data-icon="fluent:calendar-ltr-24-regular"></i></span><input type="tel" name="agenda_data" class="data" /></dd>
											</dl>
										
											<dl>
												<dt>Duração</dt>
												<dd class="form-comp form-comp_pos">
													<?php /*<input type="tel" name="agenda_duracao" class="" />*/?>
													<select name="agenda_duracao">
														<?php
														foreach($optAgendaDuracao as $v) {
															echo '<option value="'.$v.'">'.$v.'</option>';
														}
														?>
													</select>
													<span>min</span>
												</dd>
											</dl>

											<dl>
												<dt>Consultório</dt>
												<dd>
													<select name="id_cadeira">
														<option value=""></option>
														<?php
														foreach($_cadeiras as $p) {
															if($p->lixo==1) continue;
															echo '<option value="'.$p->id.'"'.($values['id_cadeira']==$p->id?' selected':'').'>'.utf8_encode($p->titulo).'</option>';
														}
														?>
													</select>
												</dd>
											</dl>
										</div>
										<div class="colunas3">
											<dl class="dl2">
												<dt>Profissionais</dt>
												<dd>
													<select class="js-profissionais js-select-profissionais">
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
												<dt>Hora</dt>
												<dd class="form-comp">
													<span><i class="iconify" data-icon="fluent:clock-24-regular"></i></span>
													<select name="agenda_hora">
														<option value="">Selecione o horário</option>
													</select>
												</dd>
											</dl>
										</div>

										<dl>
											<dt>Observações</dt>
											<dd>
												<textarea name="obs" style="height:80px;"></textarea>
											</dd>
										</dl>
									</div>


									<div class="js-ag-agendamento-naoQueroAgendar">
										<dl>
											<dt>Status</dt>
											<dd>
												<select name="id_status">
													<option value="">selecione</option>
													<?php
													foreach($_historicoStatus as $v) {
														echo '<option value="'.$v->id.'">'.utf8_encode($v->titulo).'</option>';
													}
													?>
												</select>
											</dd>
										</dl>
										<dl>
											<dt>Observações</dt>
											<dd>
												<textarea name="obs" style="height:80px;"></textarea>
											</dd>
										</dl>
									</div>


									<div class="js-ag-agendamento-excluir">
										<dl>
											<dt>Motivo</dt>
											<dd>
												<textarea name="motivo" style="height:80px;"></textarea>
											</dd>
										</dl>
									</div>

								</div>

								<div class="js-ag js-ag-whatsapp">

									<?php
									// id => 4: Relacionamento Gestão de Tempo
									$sqlwts=new Mysql(true);
									$sqlwts->consult($_p."whatsapp_mensagens_tipos","*","where id=4");
									$wts=$sqlwts->rows?mysqli_fetch_object($sqlwts->mysqry):"";
									
									if(is_object($wts)) {
									?>
									<div class="colunas3">
										<dl>
											<dt>Número</dt>
											<dd>
												<input type="text" disabled class="telefone js-whatsapp-numero" />
											</dd>
										</dl>
									</div>
									<dl>
										<dt>Mensagem</dt>
										<dd>
											<textarea style="height: 250px;" disabled><?php echo $infozap->texto;?></textarea>
										</dd>
									</dl>

									<a href="javascript:;" class="button button__sec js-btn-whatsapp-enviar" data-loading="0">Enviar Whatsapp</a>

									<?php
									} else {
									?>
									<center>
										<br />Mensagem <b>Relacionamento Gestão de Tempo</b> não configurado.
										<br /><br />
										<a href="pg_configuracoes_whatsapp.php" target="_blank" class="button">Clique aqui para configurar</a>
									</center>
									<?php	
									}
									?>


									<fieldset style="margin-top:30px;">
										<legend>Histórico</legend>

										<div class="history2 js-whatsappHistorico">
										
										</div>
									</fieldset>
								</div>


								<div class="js-ag js-ag-historico" style="display:none;">
									<div class="history">
										<section>
											<div class="history2">

												<div class="history2-item">
													<aside>
														<span style="background:#f9de27;color:#FFF;"><i class="iconify" data-icon="mdi:calendar-check"></i></span>		
													</aside>

													<article>
														<div class="history2-main">
															<div>
																<h1>02/05 • 09:00</h1>
																<h2>CONSULT. 4</h2>												
																<div class="badge-prof" style="background:#c18c6a">PM</div>												
															</div>																						
															<h3><a href="javascript:;" onclick="$(this).parent().parent().next('.history2-more').slideToggle('fast');">detalhes</a></h3>									
														</div>
														<div class="history2-more">
															

															<div class="history2-more-item">

																<h1>02/02/21 08:30 - Simone Helena dos Santos </h1>
																<h2>Alterou status de <span class="data" style="background:#545559">À CONFIRMAR</span> para <span class="data" style="background:#f9de27">DESMARCADO</span></h2>
																												
															</div>
															

															<div class="history2-more-item">

																<h1>25/01/21 15:30 - Alessandra Silva Alves</h1>
																												<h2>Criou novo agendamento com status <span class="data" style="background:#545559">À CONFIRMAR</span></h2>
																												
															</div>
														</div>
													</article>
												</div>

												<div class="history2-item">
													<aside>
														<span><i class="iconify" data-icon="mdi:chat-processing-outline"></i></span>			
													</aside>

													<article>
														<div class="history2-main">
															<div>
																<h1>28/04/22 • 08:23</h1>
																Kroner Machado Costa												
															</div>
															<strong>Não conseguiu contato</strong>
															<br />																				
														</div>
													</article>
												</div>
											</div>
										</section>
									</div>
								</div>
							</div>

						</form>
					</section><!-- .aside -->
					<section class="aside aside-pacienteRelacionamentoExcluido" id="js-aside-pacienteRelacionamentoExcluido">
						
						<div class="aside__inner1">

							<header class="aside-header">
								<h1>Paciente Excluído (ignorados)</h1>
								<a href="javascript:;" class="aside-header__fechar aside-close"><i class="iconify" data-icon="fluent:dismiss-24-filled"></i></a>
							</header>

							<form method="post" class="aside-content form" onsubmit="return false">
								<input type="hidden" name="id_paciente" />
								<input type="hidden" name="tipo" value="queroAgendar" />
								<section class="header-profile">
									<img src="img/ilustra-usuario.jpg" alt="" width="60" height="60" class="js-foto header-profile__foto" />
									<div class="header-profile__inner1">
										<h1><a href="" target="_blank" class="js-nome"></a></h1>
										<div>
											<p class="js-idade"></p>
											<p class="js-periodicidade"></p>
											<p class="js-ultimoAtendimento"></p>
										</div>
									</div>
								</section>

								<script>
									

									$(function() {

										$('#js-aside-pacienteRelacionamentoExcluido .js-retirarDaLista').click(function(){
											
									
											let obj = $(this);
											let objTextoAntigo = $(this).html();

											if(obj.attr('data-loading')==0) {

												obj.attr('data-loading',1);
												obj.html(`<span class="iconify" data-icon="eos-icons:loading"></span>`);
												let id_paciente = $('#js-aside-pacienteRelacionamentoExcluido input[name=id_paciente]').val();

												let data = `ajax=asRelacionamentoPacienteRemoverExcluidos&id_paciente=${id_paciente}`;
											
												$.ajax({
														type:'POST',
														data:data,
														url:baseURLApiAside,
														success:function(rtn) {
															if(rtn.success) {

																atualizaValorListasInteligentes();
																swal({title: "Sucesso!", text: 'Paciente excluído com sucesso!', type:"success", confirmButtonColor: "#424242"},function(){
																	$('.aside-close').click();
																});

															} else if(rtn.error) {
																swal({title: "Erro!", text: rtn.error, type:"error", confirmButtonColor: "#424242"});
															} else {
																swal({title: "Erro!", text: "Algum erro ocorreu! Tente novamente.", type:"error", confirmButtonColor: "#424242"});
															}
															
														},
														error:function() {
															swal({title: "Erro!", text: "Algum erro ocorreu! Tente novamente.", type:"error", confirmButtonColor: "#424242"});
														} 
												}).done(function(){
													obj.html(objTextoAntigo);
													obj.attr('data-loading',0);
												});
											};
										});

									});
								</script>
								<section class="tab tab_alt js-tab">
									<a href="javascript:;" onclick="$('.js-ag').hide(); $('.js-ag-excluido').show();" class="active">Excluído</a>			
									<a href="javascript:;" onclick="$('.js-ag').hide(); $('.js-ag-historico').show();">Histórico</a>					
								</section>
								
								<div class="js-ag js-ag-excluido">

									

									<div class="js-ag-agendamento-excluir">
										<dl>
											<dt>Motivo</dt>
											<dd>
												<textarea name="motivo" style="height:80px;"></textarea>
											</dd>
											<dd class="js-excluido-desc"></dd>
										</dl>
									</div>

									<center>
										<br /><br />
										<button class="button button_main js-retirarDaLista" data-loading="0"><i class="iconify" data-icon="fluent:delete-24-regular"></i> <span>Remover da Lista de Excluídos</span></button>
									</center>
								</div>
								
								<div class="js-ag js-ag-historico" style="display:none;">
									<div class="history">
										<section>
											<div class="history2">

												<div class="history2-item">
													<aside>
														<span style="background:#f9de27;color:#FFF;"><i class="iconify" data-icon="mdi:calendar-check"></i></span>		
													</aside>

													<article>
														<div class="history2-main">
															<div>
																<h1>02/05 • 09:00</h1>
																<h2>CONSULT. 4</h2>												
																<div class="badge-prof" style="background:#c18c6a">PM</div>												
															</div>																						
															<h3><a href="javascript:;" onclick="$(this).parent().parent().next('.history2-more').slideToggle('fast');">detalhes</a></h3>									
														</div>
														<div class="history2-more">
															

															<div class="history2-more-item">

																<h1>02/02/21 08:30 - Simone Helena dos Santos </h1>
																<h2>Alterou status de <span class="data" style="background:#545559">À CONFIRMAR</span> para <span class="data" style="background:#f9de27">DESMARCADO</span></h2>
																												
															</div>
															

															<div class="history2-more-item">

																<h1>25/01/21 15:30 - Alessandra Silva Alves</h1>
																												<h2>Criou novo agendamento com status <span class="data" style="background:#545559">À CONFIRMAR</span></h2>
																												
															</div>
														</div>
													</article>
												</div>

												<div class="history2-item">
													<aside>
														<span><i class="iconify" data-icon="mdi:chat-processing-outline"></i></span>			
													</aside>

													<article>
														<div class="history2-main">
															<div>
																<h1>28/04/22 • 08:23</h1>
																Kroner Machado Costa												
															</div>
															<strong>Não conseguiu contato</strong>
															<br />																				
														</div>
													</article>
												</div>
											</div>
										</section>
									</div>
								</div>

						</form>
					</section><!-- .aside -->
				<?php
			}

		// Profissao
			if(isset($apiConfig['profissao'])) {
				?>
				<script type="text/javascript">
					var asProfissoes = [];

					const asProfissoesListar = (openAside) => {
						
						if(asProfissoes) {
							$('.js-asProfissoes-table tbody').html('');

							let atualizaProfissao = $('select.ajax-id_profissao')?1:0;
						
							let atualizaProfissaoId = 0;
							let profissaoDisabledIds = [];
							if(atualizaProfissao==1) {

								$('select.ajax-id_profissao option').each(function(index,el){
									if($(el).prop('disabled')===true) {
										profissaoDisabledIds.push($(el).val());
									}
								})
								atualizaProfissaoId=$('select.ajax-id_profissao').val();
								$('select.ajax-id_profissao').find('option').remove();
							
								$('select.ajax-id_profissao').append('<option value=""></option>');
							}

							cont=0;
							asProfissoes.forEach(x=>{

								$(`.js-asProfissoes-table tbody`).append(`<tr class="aside-open">
																	<td><h1>${x.titulo}</h1></td>
																	<td style="text-align:right;"><a href="javascript:;" class="button js-asProfissoes-editar" data-id="${x.id}"><i class="iconify" data-icon="fluent:edit-24-regular"></i></a></td>
																</tr>`);

								if(atualizaProfissao==1) {

									dis=profissaoDisabledIds.includes(x.id)?' disabled':'';
									sel=(atualizaProfissaoId==x.id)?' selected':'';
									$('select.ajax-id_profissao').append(`<option value="${x.id}"${sel}${dis}>${x.titulo}</option>`);
								}

								cont++;
								if(cont==asProfissoes.length) {
									$('select.ajax-id_profissao').trigger('chosen:updated');
								}

							});
							
							if(openAside===true) {
								$("#js-aside-asProfissoes").fadeIn(100,function() {
									$("#js-aside-asProfissoes .aside__inner1").addClass("active");
								});
							}

						} else {
							if(openAside===true) {
								$(".aside").fadeIn(100,function() {
										$(".aside .aside__inner1").addClass("active");
								});
							}
						}
					}

					const asProfissoesAtualizar = (openAside) => {	
						let data = `ajax=asProfissoesListar`;

						$.ajax({
							type:"POST",
							url:baseURLApiAside,
							data:data,
							success:function(rtn) {
								if(rtn.success) {
									asProfissoes=rtn.regs;
									asProfissoesListar(openAside);
								}
							}
						})
					}
					
					const asProfissoesEditar = (id) => {
						let data = `ajax=asProfissoesEditar&id=${id}`;
						$.ajax({
							type:"POST",
							url:baseURLApiAside,
							data:data,
							success:function(rtn) {
								if(rtn.success) {
									reg=rtn.cnt

									$(`.js-asProfissoes-id`).val(reg.id);
									$(`.js-asProfissoes-titulo`).val(reg.titulo);

									
									$('.js-asProfissoes-form').animate({scrollTop: 0},'fast');
									$('.js-asProfissoes-submit').html(`<i class="iconify" data-icon="fluent:checkmark-12-filled"></i>`);
									$('.js-asProfissoes-remover').show();

								} else if(rtn.error) {
									swal({title: "Erro!", text: rtn.error, type:"error", confirmButtonColor: "#424242"});
								} else {
									swal({title: "Erro!", text: "Algum erro ocorreu durante a edição deste registro!", type:"error", confirmButtonColor: "#424242"});
								}
							},
							error:function(){
								swal({title: "Erro!", text: "Algum erro ocorreu durante a edição deste registro!", type:"error", confirmButtonColor: "#424242"});
							}
						});
					}
					
					$(function(){

						asProfissoesAtualizar();

						$('.js-asProfissoes-submit').click(function(){
							let obj = $(this);
							if(obj.attr('data-loading')==0) {

								let id = $(`.js-asProfissoes-id`).val();
								let titulo = $(`.js-asProfissoes-titulo`).val();

								if(titulo.length==0) {
									swal({title: "Erro!", text: "Digite a Profissão", type:"error", confirmButtonColor: "#424242"});
								}  else {

									obj.html(`<span class="iconify" data-icon="eos-icons:loading"></span>`);
									obj.attr('data-loading',1);

									let data = `ajax=asProfissoesPersistir&id=${id}&titulo=${titulo}`;
									
									$.ajax({
										type:'POST',
										data:data,
										url:baseURLApiAside,
										success:function(rtn) {
											if(rtn.success) {
												asProfissoesAtualizar();	

												$(`.js-asProfissoes-id`).val(0);
												$(`.js-asProfissoes-titulo`).val(``);

											} else if(rtn.error) {
												swal({title: "Erro!", text: rtn.error, type:"error", confirmButtonColor: "#424242"});
											} else {
												swal({title: "Erro!", text: "Algum erro ocorreu! Tente novamente.", type:"error", confirmButtonColor: "#424242"});
											}
											
										},
										error:function() {
											swal({title: "Erro!", text: "Algum erro ocorreu! Tente novamente.", type:"error", confirmButtonColor: "#424242"});
										}
									}).done(function(){
										$('.js-asProfissoes-remover').hide();
										obj.html(`<i class="iconify" data-icon="fluent:add-circle-24-regular"></i>`);
										obj.attr('data-loading',0);
									});

								}
							}
						})

						$('.js-asProfissoes-table').on('click','.js-asProfissoes-editar',function(){
							let id = $(this).attr('data-id');
							asProfissoesEditar(id);
						});

						$('.aside-profissao').on('click','.js-asProfissoes-remover',function(){
							let obj = $(this);

							if(obj.attr('data-loading')==0) {

								let id = $('.js-asProfissoes-id').val();
								swal({
									title: "Atenção",
									text: "Você tem certeza que deseja remover este registro?",
									type: "warning",
									showCancelButton: true,
									confirmButtonColor: "#DD6B55",
									confirmButtonText: "Sim!",
									cancelButtonText: "Não",
									closeOnConfirm:false,
									closeOnCancel: false }, 
									function(isConfirm){   
										if (isConfirm) {   

											obj.html(`<span class="iconify" data-icon="eos-icons:loading"></span>`);
											obj.attr('data-loading',1);
											let data = `ajax=asProfissoesRemover&id=${id}`; 
											$.ajax({
												type:"POST",
												data:data,
												url:baseURLApiAside,
												success:function(rtn) {
													if(rtn.success) {
														$(`.js-asProfissoes-id`).val(0);
														$(`.js-asProfissoes-titulo`).val('');
														asProfissoesAtualizar();
														swal.close();   
													} else if(rtn.error) {
														swal({title: "Erro!", text: rtn.error, type:"error", confirmButtonColor: "#424242"});
													} else {
														swal({title: "Erro!", text: "Algum erro ocorreu durante a remoção desta profissão!", type:"error", confirmButtonColor: "#424242"});
													}
												},
												error:function(){
													swal({title: "Erro!", text: "Algum erro ocorreu durante a remoção desta profissão!", type:"error", confirmButtonColor: "#424242"});
												}
											}).done(function(){
												$('.js-asProfissoes-remover').hide();
												obj.html('<i class="iconify" data-icon="fluent:delete-24-regular"></i>');
												obj.attr('data-loading',0);
												$(`.js-asProfissoes-submit`).html(`<i class="iconify" data-icon="fluent:add-circle-24-regular"></i>`);
											});
										} else {   
											swal.close();   
										} 
									});
							}
						});

					});
				</script>

				<section class="aside aside-profissao">
					<div class="aside__inner1">

						<header class="aside-header">
							<h1>Profissão</h1>
							<a href="javascript:;" class="aside-header__fechar aside-close"><i class="iconify" data-icon="fluent:dismiss-24-filled"></i></a>
						</header>

						<form method="post" class="aside-content form">
							<input type="hidden" class="js-asProfissoes-id" />
							
							<dl>
								<dt>Profissão</dt>
								<dd>
									<input type="text" class="js-asProfissoes-titulo" style="text-transform:uppercase;" />
									<button type="button" class="js-asProfissoes-submit button button_main" data-loading="0"><i class="iconify" data-icon="fluent:add-circle-24-regular"></i></button>
									<a href="javascript:;" class="button js-asProfissoes-remover" data-loading="0" style="display:none;"><i class="iconify" data-icon="fluent:delete-24-regular"></i></a>
								</dd>
							</dl>
							<div class="list2" style="margin-top:2rem;">
								<table class="js-asProfissoes-table">
									<thead>
										<tr>									
											<th>PROFISSÃO</th>
											<th></th>
										</tr>
									</thead>
									<tbody>							
									</tbody>
								</table>
							</div>
						</form>
					</div>
				</section>
				<?php
			}

		// Proxima Consulta
			if(isset($apiConfig['proximaConsulta'])) {
				?>
				<script type="text/javascript">

					const headerPaciente = (aside,rtn) => {

						aside.find('.js-nome').html(`${rtn.data.nome} <i class="iconify" data-icon="fluent:share-screen-person-overlay-20-regular" style="color:var(--cinza4)"></i>`).attr('href',`pg_pacientes_resumo.php?id_paciente=${rtn.data.id_paciente}`);

						if(rtn.data.ft && rtn.data.ft.length>0) {
							aside.find('.js-foto').attr('src',rtn.data.ft);
						} else {
							aside.find('.js-foto').attr('src','img/ilustra-usuario.jpg');
						}

						if(rtn.data.idade && rtn.data.idade>0) {
							aside.find('.js-idade').html(rtn.data.idade+(rtn.data.idade>=2?' anos':' ano'));
						} else {
							aside.find(' .js-idade').html(``);
						}

						if(rtn.data.plano_odontologico && rtn.data.plano_odontologico.length>0) {
							aside.find('.js-planoOdontologico').html(`Plano Odontológico: ${rtn.data.plano_odontologico}`);
						} else {
							aside.find('.js-planoOdontologico').html(`Plano Odontológico: -`);
						}

						if(rtn.data.periodicidade && rtn.data.periodicidade.length>0) {
							aside.find('.js-periodicidade').html(`Periodicidade: ${rtn.data.periodicidade}`);
						} else {
							aside.find('.js-periodicidade').html(`Periodicidade: -`);
						}

						if(rtn.data.musica && rtn.data.musica.length>0) {
							aside.find('.js-musica').html(`<i class="iconify" data-icon="bxs:music"></i> ${rtn.data.musica}`);
						} else {
							aside.find('.js-musica').html(``);
						}

						if(rtn.data.statusBI && rtn.data.statusBI.length==0) {
							aside.find('.js-statusBI').html(``).hide();
						} else {
							aside.find('.js-statusBI').html(`${rtn.data.statusBI}`).show();
						}

						aside.find('.js-ag-agendamentoFuturos table tr').remove();
						if(rtn.data.agendamentosFuturos && rtn.data.agendamentosFuturos.length>0) {
							rtn.data.agendamentosFuturos.forEach(x=>{


								let profissionalIniciais=``;

								x.profissionais.forEach(p=>{
									profissionalIniciais+=`<div class="badge-prof" title="${p.iniciais}" style="background:${p.cor}">${p.iniciais}</div>`;
								})
								aside.find('.js-ag-agendamentoFuturos table').append(`<tr>
																		<td>
																			<h1>${x.data}</h1>	
																		</td>
																		<td>${x.obs}</td>
																		<td>${x.cadeira}</td>
																		<td>
																			${profissionalIniciais}
																		</td>
																	</tr>`);
							});

						} else {
							aside.find('.js-ag-agendamentoFuturos table').append(`<tr><td><center>Nenhum agendamento futuro</center></td></tr>`);
						}


						aside.find('input,select,textarea').removeClass('erro').val('');
						aside.find('.js-id_paciente').val(rtn.data.id_paciente);
						aside.find('.js-periodicidade_select').val(rtn.data.periodicidade_select);
						aside.find('.js-proximaConsulta-id_agenda').val(rtn.id_agenda);

						if(rtn.data.id_profissional.length>0) aside.find('.js-prontuario-profissional').val(rtn.data.id_profissional);
						else  aside.find('.js-prontuario-profissional').val('');

					}
					
					const asideProximaConsulta = (idAgenda=0) => {
						$('#js-aside-proximaConsulta .js-tab').show();
						let data = `ajax=proximaConsulta&id_agenda=${idAgenda}`;
						$.ajax({
							type:'POST',
							data:data,
							url:baseURLApiAside,
							success:function(rtn) {
								if(rtn.success) {

									id_agenda=idAgenda;
									id_paciente=rtn.data.id_paciente
									headerPaciente($('#js-aside-proximaConsulta'),rtn);
									headerPaciente($('.aside-prontuario-procedimentos'),rtn);
									asideProcedimentos();


									/*$("#js-aside-proximaConsulta").fadeIn(100,function() {
										$("#js-aside-proximaConsulta .aside__inner1").addClass("active");
										//

										asideProximaConsultaProntuario();
									});*/

									$('.aside-prontuario-procedimentos').fadeIn(100,function() {
										$('.aside-prontuario-procedimentos .aside__inner1').addClass("active");
										$('.aside-prontuario-procedimentos').find('.tab,.header-profile').show();
										
									});

									
									$('#js-aside-proximaConsulta .js-btn-acao-lembrete').click();


								} else if(rtn.error) {
									//swal({title: "Erro!", text: rtn.error, type:"error", confirmButtonColor: "#424242"});
								} else {
									//swal({title: "Erro!", text: "Algum erro ocorreu! Tente novamente.", type:"error", confirmButtonColor: "#424242"});
								}
								
							},
							error:function() {
								//swal({title: "Erro!", text: "Algum erro ocorreu! Tente novamente.", type:"error", confirmButtonColor: "#424242"});
							}
						}).done(function(){
						});
					}

					const asideProximaConsultaLembrete = () => {

						$('.aside-prontuario-procedimentos .aside-close-procedimentos').click();
						$('#js-aside-proximaConsulta').fadeIn(100,function() {
							$('#js-aside-proximaConsulta .aside__inner1').addClass("active");
							
						});
						$('#js-aside-proximaConsulta .aside-header h1').html('Próxima Consulta');
						$("#js-aside-proximaConsulta .js-tab a:eq(0)").click();
						setTimeout(function(){
										$('#js-aside-proximaConsulta .js-profissionais').chosen();
										$('#js-aside-proximaConsulta .js-profissionais').trigger('chosen:updated');
									},100);
					}

					const asideProximaConsultaProntuario = () => {
						
						/*$('#js-aside-proximaConsulta .aside-header h1').html('Evolução Geral');
						$('#js-aside-proximaConsulta .js-prontuario-data').val('<?php echo date('d/m/Y H:i');?>');
						$('#js-aside-proximaConsulta .js-tab').hide();
						$('#js-aside-proximaConsulta .js-ag-agendamento').hide();
						//$('#js-aside-proximaConsulta .js-ag-agendamentoFuturos').hide();
						$('#js-aside-proximaConsulta .js-ag-prontuario').show();
						$('#js-aside-proximaConsulta .js-prontuario-data').datetimepicker({
																				timepicker:true,
																				format:'d/m/Y H:i',
																				scrollMonth:false,
																				scrollTime:false,
																				scrollInput:false,
																			});*/

						
						$('.aside-prontuario-procedimentos').fadeIn(100,function() {
							$(this).children(".aside__inner1").addClass("active");
							
						});
					}

					const asideProximaConsultaFinalizado = () => {
						$('#js-aside-proximaConsulta input[name=agenda_data]').val('');
						$('#js-aside-proximaConsulta select[name=agenda_duracao]').val('');
						$('#js-aside-proximaConsulta select[name=id_cadeira]').val('');
						$('#js-aside-proximaConsulta select.js-profissionais-qa').val('').trigger('chosen:updated');
						$('#js-aside-proximaConsulta select[name=agenda_hora]').val('');
						$('#js-aside-proximaConsulta textarea.js-obs-qa').val('');

						
						$(`#js-aside-proximaConsulta .js-retorno`).val('');
						$(`#js-aside-proximaConsulta .js-agenda_duracao`).val('');
						$(`#js-aside-proximaConsulta .js-laboratorio`).prop('checked',false);
						$(`#js-aside-proximaConsulta .js-imagem`).prop('checked',false);
						$(`#js-aside-proximaConsulta .js-profissionais`).val('');
						$(`#js-aside-proximaConsulta .js-obs`).val('');
						$('#js-aside-proximaConsulta .js-id_paciente').val('');
						swal({title: "Sucesso!", text: 'Dados salvos com sucesso!', type:"success", confirmButtonColor: "#424242"},function(){
							$('#js-aside-proximaConsulta .aside-close').click();
						});
					}

					$(function(){

						$('#js-aside-proximaConsulta .js-btn-acao').click(function(){
							$('#js-aside-proximaConsulta .js-btn-acao').removeClass('active');
							$(this).addClass('active');

							if($(this).attr('data-tipo')=="queroAgendar") {
								$('#js-aside-proximaConsulta .js-ag-agendamento-lembrete').hide();
								$('#js-aside-proximaConsulta .js-ag-agendamento-altaPeriodicidade').hide();
								$('#js-aside-proximaConsulta .js-ag-agendamento-queroAgendar').show();

								$('#js-aside-proximaConsulta .js-profissionais-qa').chosen();
								$('#js-aside-proximaConsulta input[name=tipo]').val('queroAgendar');
							} else if($(this).attr('data-tipo')=="altaPeriodicidade") {
								$('#js-aside-proximaConsulta .js-ag-agendamento-lembrete').hide();
								$('#js-aside-proximaConsulta .js-ag-agendamento-queroAgendar').hide();
								$('#js-aside-proximaConsulta .js-ag-agendamento-altaPeriodicidade').show();

								$('#js-aside-proximaConsulta .js-profissionais-qa').chosen();
								$('#js-aside-proximaConsulta input[name=tipo]').val('altaPeriodicidade');
							} else {
								$('#js-aside-proximaConsulta .js-ag-agendamento-altaPeriodicidade').hide();
								$('#js-aside-proximaConsulta .js-ag-agendamento-queroAgendar').hide();
								$('#js-aside-proximaConsulta .js-ag-agendamento-lembrete').show();
								$('#js-aside-proximaConsulta input[name=tipo]').val('lembrete');
							}
						});

						
						$('#js-aside-proximaConsulta').on('change','select[name=agenda_duracao], select[name=id_cadeira],  select.js-profissionais-qa, input[name=agenda_data]',function(){
							horarioDisponivel(0,$('#js-aside-proximaConsulta'));
						});

						$('#js-aside-proximaConsulta .js-ag-agendamento .js-salvar').click(function(){
							let tipo = $('#js-aside-proximaConsulta input[name=tipo]').val();
							let id_paciente = $('#js-aside-proximaConsulta .js-id_paciente').val();
							let id_agenda_origem = $('#js-aside-proximaConsulta .js-proximaConsulta-id_agenda').val();

							if(tipo=="queroAgendar") {
								let agenda_data = $('#js-aside-proximaConsulta input[name=agenda_data]').val();
								let agenda_duracao = $('#js-aside-proximaConsulta select[name=agenda_duracao]').val();
								let id_cadeira = $('#js-aside-proximaConsulta select[name=id_cadeira]').val();
								let id_profissional = $('#js-aside-proximaConsulta select.js-profissionais-qa').val();
								let agenda_hora = $('#js-aside-proximaConsulta select[name=agenda_hora]').val();
								let obs = $('#js-aside-proximaConsulta textarea.js-obs-qa').val();
								let erro = '';

								if(agenda_data.length==0) erro='Defina a <b>Data do Agendamento</b>';
								else if(agenda_duracao.length==0) erro='Defina a <b>Duração de Agendamento</b>';
								else if(id_cadeira.length==0) erro='Defina o <b>Consultório do Agendamento</b>';
								else if(id_profissional.length==0) erro='Defina o <b>Profissional do Agendamento</b>';
								else if(agenda_hora.length==0) erro='Defina a <b>Hora do Agendamento</b>';

								if(erro.length==0) {


									let obj = $(this);
									let objHTMLAntigo = $(this).html();

									if(obj.attr('data-loading')==0) {
										
										obj.html(`<span class="iconify" data-icon="eos-icons:loading"></span>`);
										obj.attr('data-loading',1);

										let data = `ajax=asRelacionamentoPacienteQueroAgendar&id_paciente=${id_paciente}&agenda_data=${agenda_data}&agenda_duracao=${agenda_duracao}&id_cadeira=${id_cadeira}&id_profissional=${id_profissional}&agenda_hora=${agenda_hora}&obs=${obs}&id_agenda_origem=${id_agenda_origem}`;

										data = {
											'ajax':'asRelacionamentoPacienteQueroAgendar',
											'id_paciente':id_paciente,
											'agenda_data':agenda_data,
											'agenda_duracao':agenda_duracao,
											'id_cadeira':id_cadeira,
											'id_profissional':id_profissional,
											'agenda_hora':agenda_hora,
											'obs':obs,
											'id_agenda_origem':id_agenda_origem

										}



										$.ajax({
												type:'POST',
												data:data,
												url:baseURLApiAside,
												success:function(rtn) {
													if(rtn.success) {

														//asideProximaConsultaProntuario();
														asideProximaConsultaFinalizado();
														

													} else if(rtn.error) {
														swal({title: "Erro!", text: rtn.error, type:"error", confirmButtonColor: "#424242"});
													} else {
														swal({title: "Erro!", text: "Algum erro ocorreu! Tente novamente.", type:"error", confirmButtonColor: "#424242"});
													}
													
												},
												error:function() {
													swal({title: "Erro!", text: "Algum erro ocorreu! Tente novamente.", type:"error", confirmButtonColor: "#424242"});
												} 
										}).done(function(){
											obj.html(objHTMLAntigo);
											obj.attr('data-loading',0);
										});


									}

								} else {
									swal({title: "Erro!", text: erro, html:true, type:"error", confirmButtonColor: "#424242"});
								}
							}
							else if(tipo=="lembrete") {
								let retorno = $(`#js-aside-proximaConsulta .js-retorno`).val();
								let duracao = $(`#js-aside-proximaConsulta .js-agenda_duracao`).val();
								let laboratorio = $(`#js-aside-proximaConsulta .js-laboratorio`).prop('checked')===true?1:0;
								let imagem = $(`#js-aside-proximaConsulta .js-imagem`).prop('checked')===true?1:0;
								let profissionais = $(`#js-aside-proximaConsulta .js-profissionais-lembrete`).val();
								let obs = $(`#js-aside-proximaConsulta .js-obs`).val();
								let id_paciente = $('#js-aside-proximaConsulta .js-id_paciente').val();
								let erro = '';
								
								/*if(retorno.length==0) {
									$(`#js-aside-proximaConsulta .js-retorno`).addClass('erro')
									erro=1;
								} 

								if(obs.length==0) {
									$(`#js-aside-proximaConsulta .js-obs`).addClass('erro')
									erro=1;
								} 


								if(erro==1) {
									swal({title: "Erro!", text: "Complete os campos destacados", type:"error", confirmButtonColor: "#424242"});
								} else if(duracao.length==0) {
									swal({title: "Erro!", text: "Defina a duração da consulta!", type:"error", confirmButtonColor: "#424242"});
								} else if(profissionais.length==0) {
									
									swal({title: "Erro!", text: "Selecione pelo menos um profissional!", type:"error", confirmButtonColor: "#424242"});
								} */


								if(retorno.length==0) erro='Preencha o campo <b>Retorno em</b>';
								else if(duracao.length==0) erro='Preencha a <b>Duração</b>';
								else if(profissionais.length==1) erro='Selecione pelo menos um profissional';
								else if(obs.length==0) erro='Preencha o campo <b>Observações</b>';
								

								if(erro.length==0) {

									let obj = $(this);
									let objHTMLAntigo = $(this).html();

									if(obj.attr('data-loading')==0) {
										
										obj.html(`<span class="iconify" data-icon="eos-icons:loading"></span>`);
										obj.attr('data-loading',1);

										let data = `ajax=proximaConsultaPersistir&retorno=${retorno}&duracao=${duracao}&laboratorio=${laboratorio}&imagem=${imagem}&profissionais=${profissionais}&obs=${obs}&id_paciente=${id_paciente}&id_agenda_origem=${id_agenda_origem}`;
										
										data = {
											'ajax':'proximaConsultaPersistir',
											'retorno':retorno,
											'duracao':duracao,
											'laboratorio':laboratorio,
											'imagem':imagem,
											'profissionais':profissionais,
											'obs':obs,
											'id_paciente':id_paciente,
											'id_agenda_origem':id_agenda_origem
										}

										$.ajax({
											type:'POST',
											data:data,
											url:baseURLApiAside,
											success:function(rtn) {
												if(rtn.success) {
													
													//asideProximaConsultaProntuario();
													asideProximaConsultaFinalizado();

												} else if(rtn.error) {
													swal({title: "Erro!", text: rtn.error, type:"error", confirmButtonColor: "#424242"});
												} else {
													swal({title: "Erro!", text: "Algum erro ocorreu! Tente novamente.", type:"error", confirmButtonColor: "#424242"});
												}
												
											},
											error:function() {
												swal({title: "Erro!", text: "Algum erro ocorreu! Tente novamente.", type:"error", confirmButtonColor: "#424242"});
											}
										}).done(function(){
											obj.html(objHTMLAntigo);
											obj.attr('data-loading',0);
										});
									}

								} else {
									swal({title: "Erro!", text: erro, html:true, type:"error", confirmButtonColor: "#424242"});
								}
							}
							else if(tipo=="altaPeriodicidade") {
								let periodicidade = $('#js-aside-proximaConsulta .js-periodicidade_select').val();
								let alta = $('#js-aside-proximaConsulta .js-periodicidade_alta').val();
								let periodicidadeDescricao = $('#js-aside-proximaConsulta .js-periodicidade_select option:selected').attr('data-descricao');


								let erro= '';
								if(periodicidade.length==0) erro='Selecione a Periodicidade do paciente';

								if(erro.length==0) {

									let obj = $(this);
									let objHTMLAntigo = $(this).html();

									if(obj.attr('data-loading')==0) {
										
										obj.html(`<span class="iconify" data-icon="eos-icons:loading"></span>`);
										obj.attr('data-loading',1);

										let data = `ajax=proximaConsultaAltaPeriodicidade&periodicidade=${periodicidade}&id_paciente=${id_paciente}&id_agenda_origem=${id_agenda_origem}&alta=${alta}`;
										
										$.ajax({
											type:'POST',
											data:data,
											url:baseURLApiAside,
											success:function(rtn) {
												if(rtn.success) {
													

													$('#js-aside-proximaConsulta .js-periodicidade').html(`Periodicidade: ${periodicidadeDescricao}`);

													//asideProximaConsultaProntuario();
													asideProximaConsultaFinalizado();

												} else if(rtn.error) {
													swal({title: "Erro!", text: rtn.error, type:"error", confirmButtonColor: "#424242"});
												} else {
													swal({title: "Erro!", text: "Algum erro ocorreu! Tente novamente.", type:"error", confirmButtonColor: "#424242"});
												}
												
											},
											error:function() {
												swal({title: "Erro!", text: "Algum erro ocorreu! Tente novamente...", type:"error", confirmButtonColor: "#424242"});
											}
										}).done(function(){
											obj.html(objHTMLAntigo);
											obj.attr('data-loading',0);
										});
									}

								} else {
									swal({title: "Erro!", text: erro, html:true, type:"error", confirmButtonColor: "#424242"});
								}
							}
						});

						$('#js-aside-proximaConsulta .js-ag-prontuario .js-salvarProntuario').click(function(){


							let obj = $(this);
							let objTextoAntigo = $(this).html();
							let id_profissional = $('#js-aside-proximaConsulta .js-prontuario-profissional').val();
							let prontuario = $('#js-aside-proximaConsulta .js-prontuario').val();
							let id_paciente = $('#js-aside-proximaConsulta .js-id_paciente').val();
							let dataProntuario = $('#js-aside-proximaConsulta .js-prontuario-data').val();

							let erro='';

							if(id_profissional.length==0) erro='Selecione o Profissional';
							else if(prontuario.length==0) erro='Digite o prontuário para salvar'

							if(erro.length==0) {
								if(obj.attr('data-loading')==0) {

									obj.html(`<span class="iconify" data-icon="eos-icons:loading"></span>`);
									obj.attr('data-loading',1);

									let data = `ajax=prontuarioPersistir&id_profissional=${id_profissional}&prontuario=${prontuario}&id_paciente=${id_paciente}&dataProntuario=${dataProntuario}`;

									data = {'ajax':'prontuarioPersistir',
												'id_profissional':id_profissional,
												'prontuario':prontuario,
												'id_paciente':id_paciente,
												'dataProntuario':dataProntuario}

									$.ajax({
										type:"POST",
										url:baseURLApiAside,
										data:data,
										success:function(rtn) {
											if(rtn.success) {
												asideProximaConsultaLembrete();
												
											} else if(rtn.error) {
												swal({title: "Erro!", text: rtn.error, type:"error", confirmButtonColor: "#424242"});
											} else {
												swal({title: "Erro!", text: 'Algum erro ocorreu ao salvar o prontuário. Tente novamente!', type:"error", confirmButtonColor: "#424242"});
											}
										}
									}).done(function(){
										obj.attr('data-loading',0);
										obj.html(objTextoAntigo);
									})
								}
							} else {
								swal({title: "Erro!", text: erro, type:"error", confirmButtonColor: "#424242"});
							}

						});
					});
				</script>

				<section class="aside aside-proximaConsulta" id="js-aside-proximaConsulta">
					<div class="aside__inner1">
						<header class="aside-header">
							<h1>Próxima Consulta</h1>
							<a href="javascript:;" class="aside-header__fechar aside-close"><i class="iconify" data-icon="fluent:dismiss-24-filled"></i></a>
						</header>

						<form method="post" class="aside-content form" onsubmit="return false;">
							<input type="hidden" class="js-proximaConsulta-id_agenda" />
							<input type="hidden" class="js-id_paciente" value="0" />
							<input type="hidden" name="tipo" value="" />
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

							<section class="tab tab_alt js-tab">
								<a href="javascript:;" onclick="$('.js-ag').hide(); $('.js-ag-agendamento').show();" class="active">Agendamento</a>	
								<a href="javascript:;" onclick="$('.js-ag').hide(); $('.js-ag-agendamentoFuturos').show();" class="">Agendamentos Futuros</a>
							</section>

							<div class="js-ag js-ag-agendamento">
								<section class="filter">
									<div class="button-group">
										<a href="javascript:;" class="js-btn-acao js-btn-acao-lembrete button active" data-tipo="lembrete"><span>Criar Lembrete</span></a>
										<a href="javascript:;" class="js-btn-acao js-btn-acao-queroAgendar button" data-tipo="queroAgendar"><span>Quero agendar</span></a>
										<a href="javascript:;" class="js-btn-acao js-btn-acao-altaPeriodicidade button" data-tipo="altaPeriodicidade"><span>Alta por Periodicidade</span></a>
									</div>
									<div class="filter-group">
										<div class="filter-form form">
											<dl>
												<dd></dd>
											</dl>
											<dl>
												<dd><button class="button button_main js-salvar" data-loading="0"><i class="iconify" data-icon="fluent:checkmark-12-filled"></i> <span>Salvar</span></button></dd>
											</dl>
										</div>								
									</div>
								</section>

								<div class="js-ag-agendamento-lembrete">
									<input type="hidden" class="js-asProfissoes-id" />
									<div class="colunas4">
										<dl>
											<dt>Retorno em</dt>
											
											<dd class="form-comp form-comp_pos">
												<input type="number" class="js-retorno" maxlength="3" min=0 oninput="validity.valid||(value='');" />
												<span>dias</span>
											</dd>
										</dl>
										<dl>
											<dt>Duração</dt>
											
											<dd class="form-comp form-comp_pos">
												<select class="js-agenda_duracao">
													<option value="">-</option>
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

										<dl class="dl2">
											<dt>&nbsp;</dt>
											<dd>
												<label>
													<input type="checkbox" class="input-switch js-laboratorio" /> Laboratório
												</label>
												<label>
													<input type="checkbox" class="input-switch js-imagem" /> Imagem
												</label>
											</dd>
										</dl>

									</div>
									<dl>
										<dt>Profissionais</dt>
										<dd>
											<select class="js-profissionais js-profissionais-lembrete" multiple>
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
										<dt>Observações</dt>
										<dd>
											<textarea class="js-obs" style="height:80px;"></textarea>
										</dd>
									</dl>

									<div class="js-ag-agendamentoFuturos" style="">
										<div class="list1">
											<table>
											</table>
										</div>
									</div>
								</div>

								<div class="js-ag-agendamento-queroAgendar">
									<div class="colunas3">
										<dl>
											<dt>Data</dt>
											<dd class="form-comp"><span><i class="iconify" data-icon="fluent:calendar-ltr-24-regular"></i></span><input type="tel" name="agenda_data" class="data datecalendar" /></dd>
										</dl>
									
										<dl>
											<dt>Duração</dt>
											<dd class="form-comp form-comp_pos">
												<?php /*<input type="tel" name="agenda_duracao" class="" />*/?>
												<select name="agenda_duracao">
													<option value="">-</option>
													<?php
													foreach($optAgendaDuracao as $v) {
														echo '<option value="'.$v.'">'.$v.'</option>';
													}
													?>
												</select>
												<span>min</span>
											</dd>
										</dl>

										<dl>
											<dt>Consultório</dt>
											<dd>
												<select name="id_cadeira">
													<option value=""></option>
													<?php
													foreach($_cadeiras as $p) {
														if($p->lixo==1) continue;
														echo '<option value="'.$p->id.'"'.($values['id_cadeira']==$p->id?' selected':'').'>'.utf8_encode($p->titulo).'</option>';
													}
													?>
												</select>
											</dd>
										</dl>
									</div>
									<div class="colunas3">
										<dl class="dl2">
											<dt>Profissionais</dt>
											<dd>
												<select class="js-profissionais-qa js-select-profissionais">
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
											<dt>Hora</dt>
											<dd class="form-comp">
												<span><i class="iconify" data-icon="fluent:clock-24-regular"></i></span>
												<select name="agenda_hora">
													<option value="">Selecione o horário</option>
												</select>
											</dd>
										</dl>
									</div>

									<dl>
										<dt>Observações</dt>
										<dd>
											<textarea class="js-obs-qa" style="height:80px;"></textarea>
										</dd>
									</dl>
								</div>

								<div class="js-ag-agendamento-altaPeriodicidade">
									<div class="colunas4">
										<dl>
											<dt>Confirme a Periodicidade</dt>
											<dd>
												<select class="js-periodicidade_select">
													<option value="">-</option>
													<?php
													foreach($_pacientesPeriodicidade as $k=>$v) {
														echo '<option value="'.$k.'" data-descricao="'.$v.'">'.$v.'</option>';
													}
													?>
												</select>
											</dd>
										</dl>
									</div>

									<dl>
										<dt>Alta por Periodicidade</dt>
										<dd>
											<textarea style="height:200px;" class="js-periodicidade_alta"></textarea>
										</dd>
									</dl>

									
								</div>

							</div>


							

							<section class="tab tab_alt js-ag js-ag-prontuario">
								<a href="javascript:;" class="active">Prontuário</a>
							</section>

							<div class="js-ag js-ag-prontuario" style="display:none">
								<section class="filter">
									<div class="button-group">
										
									</div>
									<div class="filter-group">
										<div class="filter-form form">
											<dl>
												<dd><button class="button button_main js-salvarProntuario" data-loading="0"><i class="iconify" data-icon="fluent:checkmark-12-filled"></i> <span>Salvar</span></button></dd>
											</dl>
										</div>								
									</div>
								</section>
								<div class="colunas3">
									<dl>
										<dt>Data</dt>
										<dd>
											<input type="text" class="js-prontuario-data" class="datahora" />
										</dd>
									</dl>
								</div>

								<dl>
									<dt>Profissional</dt>
									<dd>
										<select class="js-prontuario-profissional">
											<option value="">-</option>
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
									<dt>Prontuário</dt>
									<dd>
										<textarea class="js-prontuario" style="height:250px;"></textarea>
									</dd>
								</dl>
							</div>


						</form>
					</div>
				</section>
				<?php
			}

		// Quero Agendar
			if(isset($apiConfig['queroAgendar'])) {
				?>
				<script type="text/javascript">
					
					const asideQueroAgendar = (id_paciente,id_proximaconsulta) => {

						$('#js-aside-queroAgendar .js-tab').show();
						let data = `ajax=asideQueroAgendarPaciente&id_paciente=${id_paciente}&id_proximaconsulta=${id_proximaconsulta}`;
						$.ajax({
							type:'POST',
							data:data,
							url:baseURLApiAside,
							success:function(rtn) {
								if(rtn.success) {
									$('#js-aside-queroAgendar .js-nome').html(`${rtn.data.nome} <i class="iconify" data-icon="fluent:share-screen-person-overlay-20-regular" style="color:var(--cinza4)"></i>`).attr('href',`pg_pacientes_resumo.php?id_paciente=${rtn.data.id_paciente}`);

								
									if(rtn.data.ft && rtn.data.ft.length>0) {
										$('#js-aside-queroAgendar .js-foto').attr('src',rtn.data.ft);
									} else {
										$('#js-aside-queroAgendar .js-foto').attr('src','img/ilustra-usuario.jpg');
									}

									if(rtn.data.idade && rtn.data.idade>0) {
										$('#js-aside-queroAgendar .js-idade').html(rtn.data.idade+(rtn.data.idade>=2?' anos':' ano'));
									} else {
										$('#js-aside-queroAgendar .js-idade').html(``);
									}

									if(rtn.data.periodicidade && rtn.data.periodicidade.length>0) {
										$('#js-aside-queroAgendar .js-periodicidade').html(`Periodicidade: ${rtn.data.periodicidade}`);
									} else {
										$('#js-aside-queroAgendar .js-periodicidade').html(`Periodicidade: -`);
									}

									if(rtn.data.musica && rtn.data.musica.length>0) {
										$('#js-aside-queroAgendar .js-musica').html(`<i class="iconify" data-icon="bxs:music"></i> ${rtn.data.musica}`);
									} else {
										$('#js-aside-queroAgendar .js-musica').html(``);
									}

									if(rtn.data.statusBI && rtn.data.statusBI.length==0) {
										$('#js-aside-queroAgendar .js-statusBI').html(``).hide();
									} else {
										$('#js-aside-queroAgendar .js-statusBI').html(`${rtn.data.statusBI}`).show();
									}

									$('#js-aside-queroAgendar .js-ag-agendamentoFuturos table tr').remove();
									if(rtn.data.agendamentosFuturos && rtn.data.agendamentosFuturos.length>0) {
										rtn.data.agendamentosFuturos.forEach(x=>{


											let profissionalIniciais=``;

											x.profissionais.forEach(p=>{
												profissionalIniciais+=`<div class="badge-prof" title="${p.iniciais}" style="background:${p.cor}">${p.iniciais}</div>`;
											})
											$('#js-aside-queroAgendar .js-ag-agendamentoFuturos table').append(`<tr>
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
										$('#js-aside-queroAgendar .js-ag-agendamentoFuturos table').append(`<tr><td><center>Nenhum agendamento futuro</center></td></tr>`);
									}



									$('#js-aside-queroAgendar').find('input,select,textarea').removeClass('erro').val('');
									$('#js-aside-queroAgendar .js-id_paciente').val(rtn.data.id_paciente);
									$('#js-aside-queroAgendar .js-webwhatsapp').attr({'href':'https://wa.me/55'+rtn.data.telefone1})

									$("#js-aside-queroAgendar").fadeIn(100,function() {
										$("#js-aside-queroAgendar .aside__inner1").addClass("active");
										$("#js-aside-queroAgendar .js-tab a:eq(0)").click();


										$('#js-aside-queroAgendar .js-profissionais').chosen();
										$('#js-aside-queroAgendar .js-profissionais').trigger('chosen:updated');
									});

									$('#js-aside-queroAgendar .js-periodicidade_select').val(rtn.data.periodicidade_select);

									$('#js-aside-queroAgendar .js-btn-acao-lembrete').click();
								} else if(rtn.error) {
									//swal({title: "Erro!", text: rtn.error, type:"error", confirmButtonColor: "#424242"});
								} else {
									//swal({title: "Erro!", text: "Algum erro ocorreu! Tente novamente.", type:"error", confirmButtonColor: "#424242"});
								}


								$('#js-aside-queroAgendar select[name=agenda_duracao]').val('');
								$('#js-aside-queroAgendar textarea.js-obs-qa').val('');
								$('#js-aside-queroAgendar textarea.js-profissionais-qa').val('');
								$('#js-aside-queroAgendar .js-id_proximaconsulta').val(0);
								if(rtn.data.proximaConsulta) {

									if(rtn.data.proximaConsulta.duracao) {
										$('#js-aside-queroAgendar select[name=agenda_duracao]').val(rtn.data.proximaConsulta.duracao);
									} 


									if(rtn.data.proximaConsulta.obs) {
										$('#js-aside-queroAgendar textarea.js-obs-qa').val(rtn.data.proximaConsulta.obs);
									}

									if(rtn.data.proximaConsulta.id_profissional) {
										$('#js-aside-queroAgendar select.js-profissionais-qa').val(rtn.data.proximaConsulta.id_profissional);
									}

									if(rtn.data.proximaConsulta.id_proximaconsulta) {
										$('#js-aside-queroAgendar .js-id_proximaconsulta').val(rtn.data.proximaConsulta.id_proximaconsulta);
									}
								}

								
							},
							error:function() {
								//swal({title: "Erro!", text: "Algum erro ocorreu! Tente novamente.", type:"error", confirmButtonColor: "#424242"});
							}
						}).done(function(){

						});
					}

					$(function(){

						
						$('#js-aside-queroAgendar').on('change','select[name=agenda_duracao], select[name=id_cadeira],  select.js-profissionais-qa, input[name=agenda_data]',function(){
							horarioDisponivel(0,$('#js-aside-queroAgendar'));
						});



						$('#js-aside-queroAgendar .js-ag-agendamento .js-salvar').click(function(){
							let id_paciente = $('#js-aside-queroAgendar .js-id_paciente').val();
							
							let agenda_data = $('#js-aside-queroAgendar input[name=agenda_data]').val();
							let agenda_duracao = $('#js-aside-queroAgendar select[name=agenda_duracao]').val();
							let id_cadeira = $('#js-aside-queroAgendar select[name=id_cadeira]').val();
							let id_profissional = $('#js-aside-queroAgendar select.js-profissionais-qa').val();
							let agenda_hora = $('#js-aside-queroAgendar select[name=agenda_hora]').val();
							let id_proximaconsulta = $('#js-aside-queroAgendar .js-id_proximaconsulta').val();
							let obs = $('#js-aside-queroAgendar textarea.js-obs-qa').val();
							let erro = '';

							if(agenda_data.length==0) erro='Defina a <b>Data do Agendamento</b>';
							else if(agenda_duracao.length==0) erro='Defina a <b>Duração de Agendamento</b>';
							else if(id_cadeira.length==0) erro='Defina o <b>Consultório do Agendamento</b>';
							else if(id_profissional.length==0) erro='Defina o <b>Profissional do Agendamento</b>';
							else if(agenda_hora.length==0) erro='Defina a <b>Hora do Agendamento</b>';

							if(erro.length==0) {


								let obj = $(this);
								let obHTMLAntigo = $(this).html();

								if(obj.attr('data-loading')==0) {
									
									obj.html(`<span class="iconify" data-icon="eos-icons:loading"></span>`);
									obj.attr('data-loading',1);

									let data = `ajax=asideQueroAgendarAgendar&id_paciente=${id_paciente}&agenda_data=${agenda_data}&agenda_duracao=${agenda_duracao}&id_cadeira=${id_cadeira}&id_profissional=${id_profissional}&agenda_hora=${agenda_hora}&obs=${obs}&id_proximaconsulta=${id_proximaconsulta}`;

									data = {
										'ajax':'asideQueroAgendarAgendar',
										'id_paciente':id_paciente,
										'agenda_data':agenda_data,
										'agenda_duracao':agenda_duracao,
										'id_cadeira':id_cadeira,
										'id_profissional':id_profissional,
										'agenda_hora':agenda_hora,
										'obs':obs,
										'id_proximaconsulta':id_proximaconsulta

									}

									$.ajax({
											type:'POST',
											data:data,
											url:baseURLApiAside,
											success:function(rtn) {
												if(rtn.success) {

													atualizaValorListasInteligentes();
													$('.aside-close').click();

												} else if(rtn.error) {
													swal({title: "Erro!", text: rtn.error, type:"error", confirmButtonColor: "#424242"});
												} else {
													swal({title: "Erro!", text: "Algum erro ocorreu! Tente novamente.", type:"error", confirmButtonColor: "#424242"});
												}
												
											},
											error:function() {
												swal({title: "Erro!", text: "Algum erro ocorreu! Tente novamente.", type:"error", confirmButtonColor: "#424242"});
											} 
									}).done(function(){
										obj.html(obHTMLAntigo);
										obj.attr('data-loading',0);
									});


								}

							} else {
								swal({title: "Erro!", text: erro, html:true, type:"error", confirmButtonColor: "#424242"});
							}
							
						});

						$('#js-aside-queroAgendar .js-ag-prontuario .js-salvarProntuario').click(function(){


							let obj = $(this);
							let objTextoAntigo = $(this).html();
							let id_profissional = $('#js-aside-queroAgendar .js-prontuario-profissional').val();
							let prontuario = $('#js-aside-queroAgendar .js-prontuario').val();
							let id_paciente = $('#js-aside-queroAgendar .js-id_paciente').val();

							let erro='';

							if(dataProntuario.length==0) erro='Preencha o campo de Data';
							else if(id_profissional.length==0) erro='Selecione o Profissional';
							else if(prontuario.length==0) erro='Digite o prontuário para salvar'

							if(erro.length==0) {
								if(obj.attr('data-loading')==0) {

									obj.html(`<span class="iconify" data-icon="eos-icons:loading"></span>`);
									obj.attr('data-loading',1);

									
									let data = {'ajax':'prontuarioPersistir',
												'id_profissional':id_profissional,
												'prontuario':prontuario,
												'id_paciente':id_paciente}

									$.ajax({
										type:"POST",
										url:baseURLApiAside,
										data:data,
										success:function(rtn) {
											if(rtn.success) {

												$('#js-aside-queroAgendar input[name=agenda_data]').val('');
												$('#js-aside-queroAgendar select[name=agenda_duracao]').val('');
												$('#js-aside-queroAgendar select[name=id_cadeira]').val('');
												$('#js-aside-queroAgendar select.js-profissionais-qa').val('').trigger('chosen:updated');
												$('#js-aside-queroAgendar select[name=agenda_hora]').val('');
												$('#js-aside-queroAgendar textarea.js-obs-qa').val('');

												
												$(`#js-aside-queroAgendar .js-retorno`).val('');
												$(`#js-aside-queroAgendar .js-agenda_duracao`).val('');
												$(`#js-aside-queroAgendar .js-laboratorio`).prop('checked',false);
												$(`#js-aside-queroAgendar .js-imagem`).prop('checked',false);
												$(`#js-aside-queroAgendar .js-profissionais`).val('');
												$(`#js-aside-queroAgendar .js-obs`).val('');
												$('#js-aside-queroAgendar .js-id_paciente').val('');
												swal({title: "Sucesso!", text: 'Dados salvos com sucesso!', type:"success", confirmButtonColor: "#424242"},function(){
													$('#js-aside-queroAgendar .aside-close').click();
												});
											} else if(rtn.error) {
												swal({title: "Erro!", text: rtn.error, type:"error", confirmButtonColor: "#424242"});
											} else {
												swal({title: "Erro!", text: 'Algum erro ocorreu ao salvar o prontuário. Tente novamente!', type:"error", confirmButtonColor: "#424242"});
											}
										}
									}).done(function(){
										obj.attr('data-loading',0);
										obj.html(objTextoAntigo);
									})
								}
							} else {
								swal({title: "Erro!", text: erro, type:"error", confirmButtonColor: "#424242"});
							}

						});


						$('#js-aside-queroAgendar .js-tab-choises a').click(function() {
							$("#js-aside-queroAgendar .js-tab-choises a").removeClass("active");
							$(this).addClass("active");							
						})



					});
				</script>

				<section class="aside aside-queroAgendar" id="js-aside-queroAgendar">
					<div class="aside__inner1">

						<header class="aside-header">
							<h1>Quero Agendar</h1>
							<a href="javascript:;" class="aside-header__fechar aside-close"><i class="iconify" data-icon="fluent:dismiss-24-filled"></i></a>
						</header>

						<form method="post" class="aside-content form" onsubmit="return false;">
							<input type="hidden" class="js-id_paciente" value="0" />
							<input type="hidden" class="js-id_proximaconsulta" value="0" />
							<input type="hidden" name="tipo" value="" />
							<section class="header-profile">
								<img src="img/ilustra-usuario.jpg" alt="" width="60" height="60" class="header-profile__foto js-foto" />
								<div class="header-profile__inner1">
									<h1><a href="" target="_blank" class="js-nome"></a></h1>
									<div>
										<p class="js-statusBI"></p>
										<p class="js-idade"></p>
										<p class="js-periodicidade"></p>
										<p class="js-musica"></p>
									</div>
								</div>
							</section>

							<section class="tab tab_alt js-tab-choises">
								<a href="javascript:;" onclick="$('.js-ag').hide(); $('.js-ag-agendamento').show();" class="active">Agendamento</a>	
								<a href="javascript:;" onclick="$('.js-ag').hide(); $('.js-ag-agendamentoFuturos').show();" class="">Agendamentos Futuros</a>
							</section>

							<div class="js-ag js-ag-agendamento">
								<section class="filter">
									<div class="button-group">
									</div>
									<div class="filter-group">
										<div class="filter-form form">
											<dl>
												<dd><a href="javascript:;" class="button js-webwhatsapp" target="_blank"><span class="iconify" data-icon="ic:outline-whatsapp"></span> Web Whatsapp</a></dd>
											</dl>
											<dl>
												<dd><button class="button button_main js-salvar" data-loading="0"><i class="iconify" data-icon="fluent:checkmark-12-filled"></i> <span>Salvar</span></button></dd>
											</dl>
										</div>								
									</div>
								</section>

								<div class="js-ag-agendamento-queroAgendar">
									<div class="colunas3">
										<dl>
											<dt>Data</dt>
											<dd class="form-comp"><span><i class="iconify" data-icon="fluent:calendar-ltr-24-regular"></i></span><input type="tel" name="agenda_data" class="data datecalendar" /></dd>
										</dl>
									
										<dl>
											<dt>Duração</dt>
											<dd class="form-comp form-comp_pos">
												<?php /*<input type="tel" name="agenda_duracao" class="" />*/?>
												<select name="agenda_duracao">
													<option value="">-</option>
													<?php
													foreach($optAgendaDuracao as $v) {
														echo '<option value="'.$v.'">'.$v.'</option>';
													}
													?>
												</select>
												<span>min</span>
											</dd>
										</dl>

										<dl>
											<dt>Consultório</dt>
											<dd>
												<select name="id_cadeira">
													<option value=""></option>
													<?php
													foreach($_cadeiras as $p) {
														if($p->lixo==1) continue;
														echo '<option value="'.$p->id.'"'.($values['id_cadeira']==$p->id?' selected':'').'>'.utf8_encode($p->titulo).'</option>';
													}
													?>
												</select>
											</dd>
										</dl>
									</div>
									<div class="colunas3">
										<dl class="dl2">
											<dt>Profissionais</dt>
											<dd>
												<select class="js-profissionais-qa js-select-profissionais">
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
											<dt>Hora</dt>
											<dd class="form-comp">
												<span><i class="iconify" data-icon="fluent:clock-24-regular"></i></span>
												<select name="agenda_hora">
													<option value="">Selecione o horário</option>
												</select>
											</dd>
										</dl>
									</div>

									<dl>
										<dt>Observações</dt>
										<dd>
											<textarea class="js-obs-qa" style="height:80px;"></textarea>
										</dd>
									</dl>
								</div>
							</div>


							<div class="js-ag js-ag-agendamentoFuturos" style="display:none">
								<div class="list1">
									<table>
									</table>
								</div>
							</div>


						</form>
					</div>
				</section>
				<?php
			}

		// Indicacao / Lista Personalizada
			if(isset($apiConfig['indicacaoListaPersonalizada'])) {
				?>
				<script type="text/javascript">
					var asListaPersonalizada = [];

					const asListaPersonalizadaListar = (openAside) => {
						
						if(asListaPersonalizada) {
							$('.js-asListaPersonalizada-table tbody').html('');

							let atualizaListaPersonalizada = 0;//$('select.ajax-indicacao')?1:0;
						
							let atualizaIndicacao = 0;
							let listaPersonalizadaDisabledIds = [];
							if(atualizaListaPersonalizada==1) {

								$('select.ajax-indicacao option').each(function(index,el){
									if($(el).prop('disabled')===true) {
										listaPersonalizadaDisabledIds.push($(el).val());
									}
								})
								atualizaIndicacao=$('select.ajax-indicacao').val();
								$('select.ajax-indicacao').find('option').remove();
							
								$('select.ajax-indicacao').append('<option value=""></option>');
							}

							cont=0;
							asListaPersonalizada.forEach(x=>{

								$(`.js-asListaPersonalizada-table tbody`).append(`<tr class="aside-open">
																				<td><h1>${x.titulo}</h1></td>
																				<td style="text-align:right;"><a href="javascript:;" class="button js-asListaPersonalizada-editar" data-id="${x.id}"><i class="iconify" data-icon="fluent:edit-24-regular"></i></a></td>
																			</tr>`);

								if(atualizaListaPersonalizada==1) {

									dis=listaPersonalizadaDisabledIds.includes(x.id)?' disabled':'';
									sel=(atualizaIndicacao==x.id)?' selected':'';
									$('select.ajax-indicacao').append(`<option value="${x.id}"${sel}${dis}>${x.titulo}</option>`);
								}

								cont++;
								if(atualizaListaPersonalizada==1 && cont==asListaPersonalizada.length) {
									$('select.ajax-indicacao').trigger('chosen:updated');
								}

							});
							
							if(openAside===true) {
								$("#js-aside-asListaPersonalizada").fadeIn(100,function() {
									$("#js-aside-asListaPersonalizada .aside__inner1").addClass("active");
								});
							}

						} else {
							if(openAside===true) {
								$(".aside").fadeIn(100,function() {
										$(".aside .aside__inner1").addClass("active");
								});
							}
						}
					}

					const asListaPersonalizadaAtualizar = (openAside) => {	
						let data = `ajax=asListaPersonalizadaAtualizar`;

						$.ajax({
							type:"POST",
							url:baseURLApiAside,
							data:data,
							success:function(rtn) {
								if(rtn.success) {
									asListaPersonalizada=rtn.regs;
									asListaPersonalizadaListar(openAside);
								}
							}
						})
					}
					
					const asListaPersonalizadaEditar = (id) => {
						let data = `ajax=asListaPersonalizadaEditar&id=${id}`;
						$.ajax({
							type:"POST",
							url:baseURLApiAside,
							data:data,
							success:function(rtn) {
								if(rtn.success) {
									reg=rtn.cnt

									$(`.js-asListaPersonalizada-id`).val(reg.id);
									$(`.js-asListaPersonalizada-titulo`).val(reg.titulo);

									
									$('.js-asListaPersonalizada-form').animate({scrollTop: 0},'fast');
									$('.js-asListaPersonalizada-submit').html(`<i class="iconify" data-icon="fluent:checkmark-12-filled"></i>`);
									$('.js-asListaPersonalizada-remover').show();

								} else if(rtn.error) {
									swal({title: "Erro!", text: rtn.error, type:"error", confirmButtonColor: "#424242"});
								} else {
									swal({title: "Erro!", text: "Algum erro ocorreu durante a edição deste registro!", type:"error", confirmButtonColor: "#424242"});
								}
							},
							error:function(){
								swal({title: "Erro!", text: "Algum erro ocorreu durante a edição deste registro!", type:"error", confirmButtonColor: "#424242"});
							}
						});
					}

					const formatTemplateIndicacao = (state) => {
						if (!state.id) return state.text;
						var $state = $('<span style="display:flex; align-items:center; gap:.5rem;">' + state.text + '</span>');
						return $state;
					}
					
					var indicacaoTipo = '';
					$(function(){

						$('select[name=indicacao_tipo]').change(function(){

							if($(this).val()=="INDICACAO") {
								$('.js-parametrizacao-indicacao').show();
							} else {
								$('.js-parametrizacao-indicacao').hide();
							}
							indicacaoTipo=$('select[name=indicacao_tipo]').val();
						}).trigger('change');

						$('select[name=indicacao]').select2({
							ajax: {
								url: function () {
									return baseURLApiAside+'?ajaxApiAside=buscaIndicacao&indicacaoTipo='+indicacaoTipo;
								},
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
							templateResult:formatTemplateIndicacao,
							//	templateSelection:formatTemplateSelection,
							//dropdownParent: $(".modal")
						});

						$('select[name=indicacao_tipo]').trigger('change');

						asListaPersonalizadaAtualizar();

						$('.js-asListaPersonalizada-submit').click(function(){
							let obj = $(this);
							if(obj.attr('data-loading')==0) {

								let id = $(`.js-asListaPersonalizada-id`).val();
								let titulo = $(`.js-asListaPersonalizada-titulo`).val();

								if(titulo.length==0) {
									swal({title: "Erro!", text: "Digite o nome da Indicação", type:"error", confirmButtonColor: "#424242"});
								}  else {

									obj.html(`<span class="iconify" data-icon="eos-icons:loading"></span>`);
									obj.attr('data-loading',1);

									let data = `ajax=asListaPersonalizadaPersistir&id=${id}&titulo=${titulo}`;
									
									$.ajax({
										type:'POST',
										data:data,
										url:baseURLApiAside,
										success:function(rtn) {
											if(rtn.success) {
												asListaPersonalizadaAtualizar();	

												$(`.js-asListaPersonalizada-id`).val(0);
												$(`.js-asListaPersonalizada-titulo`).val(``);

											} else if(rtn.error) {
												swal({title: "Erro!", text: rtn.error, type:"error", confirmButtonColor: "#424242"});
											} else {
												swal({title: "Erro!", text: "Algum erro ocorreu! Tente novamente.", type:"error", confirmButtonColor: "#424242"});
											}
											
										},
										error:function() {
											swal({title: "Erro!", text: "Algum erro ocorreu! Tente novamente.", type:"error", confirmButtonColor: "#424242"});
										}
									}).done(function(){
										$('.js-asListaPersonalizada-remover').hide();
										obj.html(`<i class="iconify" data-icon="fluent:add-circle-24-regular"></i>`);
										obj.attr('data-loading',0);
									});

								}
							}
						})

						$('.js-asListaPersonalizada-table').on('click','.js-asListaPersonalizada-editar',function(){
							let id = $(this).attr('data-id');
							asListaPersonalizadaEditar(id);
						});

						$('.aside-indicacao').on('click','.js-asListaPersonalizada-remover',function(){
							let obj = $(this);

							if(obj.attr('data-loading')==0) {

								let id = $('.js-asListaPersonalizada-id').val();
								swal({
									title: "Atenção",
									text: "Você tem certeza que deseja remover este registro?",
									type: "warning",
									showCancelButton: true,
									confirmButtonColor: "#DD6B55",
									confirmButtonText: "Sim!",
									cancelButtonText: "Não",
									closeOnConfirm:false,
									closeOnCancel: false }, 
									function(isConfirm){   
										if (isConfirm) {   

											obj.html(`<span class="iconify" data-icon="eos-icons:loading"></span>`);
											obj.attr('data-loading',1);
											let data = `ajax=asListaPersonalizadaRemover&id=${id}`; 
											$.ajax({
												type:"POST",
												data:data,
												url:baseURLApiAside,
												success:function(rtn) {
													if(rtn.success) {
														$(`.js-asListaPersonalizada-id`).val(0);
														$(`.js-asListaPersonalizada-titulo`).val('');
														asListaPersonalizadaAtualizar();
														swal.close();   
													} else if(rtn.error) {
														swal({title: "Erro!", text: rtn.error, type:"error", confirmButtonColor: "#424242"});
													} else {
														swal({title: "Erro!", text: "Algum erro ocorreu durante a remoção desta indicação!", type:"error", confirmButtonColor: "#424242"});
													}
												},
												error:function(){
													swal({title: "Erro!", text: "Algum erro ocorreu durante a remoção desta indicação!", type:"error", confirmButtonColor: "#424242"});
												}
											}).done(function(){
												$('.js-asListaPersonalizada-remover').hide();
												obj.html('<i class="iconify" data-icon="fluent:delete-24-regular"></i>');
												obj.attr('data-loading',0);
												$(`.js-asListaPersonalizada-submit`).html(`<i class="iconify" data-icon="fluent:add-circle-24-regular"></i>`);
											});
										} else {   
											swal.close();   
										} 
									});
							}
						});

					});
				</script>

				<section class="aside aside-indicacao">
					<div class="aside__inner1">

						<header class="aside-header">
							<h1>Indicações</h1>
							<a href="javascript:;" class="aside-header__fechar aside-close"><i class="iconify" data-icon="fluent:dismiss-24-filled"></i></a>
						</header>

						<form method="post" class="aside-content form">
							<input type="hidden" class="js-asListaPersonalizada-id" />
							
							<dl>
								<dt>Indicação</dt>
								<dd>
									<input type="text" class="js-asListaPersonalizada-titulo" style="text-transform:uppercase;" />
									<button type="button" class="js-asListaPersonalizada-submit button button_main" data-loading="0"><i class="iconify" data-icon="fluent:add-circle-24-regular"></i></button>
									<a href="javascript:;" class="button js-asListaPersonalizada-remover" data-loading="0" style="display:none;"><i class="iconify" data-icon="fluent:delete-24-regular"></i></a>
								</dd>
							</dl>
							<div class="list2" style="margin-top:2rem;">
								<table class="js-asListaPersonalizada-table">
									<thead>
										<tr>									
											<th>INDICAÇÃO</th>
											<th></th>
										</tr>
									</thead>
									<tbody>							
									</tbody>
								</table>
							</div>
						</form>
					</div>
				</section>
				<?php
			}

	?>
