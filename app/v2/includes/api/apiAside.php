<?php

	if(isset($_POST['ajax'])) {

		$dir="../../";
		require_once("../../lib/conf.php");
		require_once("../../usuarios/checa.php");


		$attr=array('prefixo'=>$_p,'usr'=>$usr);
		$wts = new Whatsapp($attr);

		$rtn = array();

		$_tableEspecialidades=$_p."parametros_especialidades";
		$_tablePlanos=$_p."parametros_planos";
		$_tableMarcas=$_p."produtos_marcas";
		$_tablePacientes=$_p."pacientes";
		$_tableProfissoes=$_p."parametros_profissoes";


		# Especialidades
			if($_POST['ajax']=="asEspecialidadesListar") {

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
							agenda_duracao='".$agenda_duracao."',
							agenda_data_final='".$agendaFinal."',
							id_cadeira='".$cadeira->id."',
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
						
					}

					$rtn=array('success'=>true);
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
										do {
											$di=$dataInicio;
											$dataInicio=date('Y-m-d H:i:s',strtotime($dataInicio." + $agenda_duracao minutes"));
											$df=$dataInicio;

											

											$where="WHERE (agenda_data>='$data 00:00:00' and agenda_data<='$data 23:59:59') and 
															(
																('$di'<=agenda_data && '$df'>=DATE_ADD(agenda_data, INTERVAL agenda_duracao MINUTE)) or 
																('$di'>=agenda_data && '$df'<=DATE_ADD(agenda_data, INTERVAL agenda_duracao MINUTE)) or 
																('$di'<=agenda_data && '$df'>agenda_data && '$df'<=DATE_ADD(agenda_data, INTERVAL agenda_duracao MINUTE))
															)";

											$where.=" and profissionais like '%,$profissional->id,%' and id_status NOT IN (3,4) and lixo=0";
											$sql->consult($_p."agenda","agenda_data,DATE_ADD(agenda_data, INTERVAL $agenda_duracao MINUTE) as agenda_data_fim,agenda_duracao",$where);
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
												$sql->consult($_p."agenda","agenda_data,DATE_ADD(agenda_data, INTERVAL $agenda_duracao MINUTE) as agenda_data_fim,agenda_duracao",$where);
												if($sql->rows==0) {
													$horariosDisponiveis[]=date('H:i',strtotime($di));
												}
											}
											//echo $where." -> $sql->rows\n";
											//while($x=mysqli_fetch_object($sql->mysqry)) {
											//	echo $x->agenda_data." - $x->agenda_data_final\n";
											//}

												//$horariosDisponiveis[]=date('H:i',strtotime($di));

										} while(strtotime($dataInicio)<strtotime($dataFim));


										$horarios = new Horarios(array('prefixo'=>$_p));

										$horariosDisponiveisNew=array();
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
				$sql->consult($_p."whatsapp_mensagens_tipos","*","where id=4");
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
					if($wts->adicionaNaFila($attr)) {
						$rtn=array('success'=>true);
					} else {
						$rtn=array('success'=>false,'error'=>$wts->erro);
					}

				}

				if(!empty($erro)) {
					$rtn=array('success'=>false,'error'=>$erro);
				}
			}
			else if($_POST['ajax']=="asRelacionamentoPacienteDisparaWhatsapp") {
				if($wts->dispara()) {

					$rtn=array('success'=>true);
				} else {
					$rtn=array('success'=>false,'error'=>$wts->erro); 
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

				$titulo=isset($_POST['titulo'])?addslashes(utf8_decode($_POST['titulo'])):'';

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


		header("Content-type: application/json");
		echo json_encode($rtn);
		die();
	}

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
				</form>
			</div>
		</section>
		<?php
			}
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
							$('.js-asPlanos-remover').show();

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
				</form>
			</div>
		</section>
		<?php
			}
	if(isset($apiConfig['marca'])) {
		?>
		<script type="text/javascript">
			var asMarcas = [];

			const asMarcasListar = (openAside) => {
				
				if(asMarcas) {
					$('.js-asMarcas-table tbody').html('');

					let atualizaMarca = $('select.ajax-id_plano')?1:0;
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
						} else if(telefone1.length==0) {
							swal({title: "Erro!", text: "Digite o Whatsapp do Paciente", type:"error", confirmButtonColor: "#424242"});
						}  else {

							obj.html(`<span class="iconify" data-icon="eos-icons:loading"></span>`);
							obj.attr('data-loading',1);

							let data = `ajax=asPacientePersistir&nome=${nome}&telefone1=${telefone1}&cpf=${cpf}&indicacao_tipo=${indicacao_tipo}&indicacao=${indicacao}`;
							
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



			});
		</script>

		<section class="aside aside-paciente">
			<div class="aside__inner1">

				<header class="aside-header">
					<h1>Novo Paciente</h1>
					<a href="javascript:;" class="aside-header__fechar aside-close"><i class="iconify" data-icon="fluent:dismiss-24-filled"></i></a>
				</header>

				<form method="post" class="aside-content form js-asPaciente-form">
					
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
						</dl>
					</div>

					<div class="colunas2">

						<dl>
							<dt>Tipo Indicação</dt>
							<dd>
								<select class="js-asPaciente-indicacao_tipo">
									<option value="">-</option>
									<?php
									foreach($_pacienteIndicacoes as $v) {
										echo '<option value="'.$v->id.'"'.($values['indicacao_tipo']==$v->id?' selected':'').' data-id="'.$v->id.'">'.utf8_encode($v->titulo).'</option>';
									}
									?>
								</select>
							</dd>
						</dl>
						<dl>
							<dt>Indicação</dt>
							<dd>
								<input type="text" class="js-asPaciente-indicacao" />
								<button type="button" class="js-asPaciente-submit button button_main" data-loading="0"><i class="iconify" data-icon="fluent:add-circle-24-regular"></i></button>

							</dd>
						</dl>
					</div>
				</form>
			</div>
		</section>
		<?php
			}
	if(isset($apiConfig['pacienteRelacionamento'])) {
			?>
			<script type="text/javascript">
				const pacienteRelacionamento = (id_paciente) => {

					let data = `ajax=asRelacionamentoPaciente&id_paciente=${id_paciente}`;
								
					$.ajax({
						type:'POST',
						data:data,
						url:baseURLApiAside,
						success:function(rtn) {
							if(rtn.success) {
								$('#js-aside-pacienteRelacionamento .js-nome').html(`${rtn.paciente.nome} <i class="iconify" data-icon="fluent:share-screen-person-overlay-20-regular" style="color:var(--cinza4)"></i>`).attr('href',`pg_pacientes_resumo.php?id_paciente=${rtn.paciente.id}`);

								if(rtn.paciente.ft && rtn.paciente.ft.length>0) {
									$('#js-aside-pacienteRelacionamento .js-foto').attr('src',rtn.paciente.ft);
								} else {
									$('#js-aside-pacienteRelacionamento .js-foto').attr('src','img/ilustra-usuario.jpg');
								}

								$('#js-aside-pacienteRelacionamento .js-whatsapp-numero').val(rtn.paciente.telefone1);

								if(rtn.paciente.idade && rtn.paciente.idade>0) {
									$('#js-aside-pacienteRelacionamento .js-idade').html(rtn.paciente.idade+(rtn.paciente.idade>=2?' anos':' ano')).show();;
								} else {
									$('#js-aside-pacienteRelacionamento .js-idade').html(``).hide();;
								}

								if(rtn.paciente.periodicidade && rtn.paciente.periodicidade.length>0) {
									$('#js-aside-pacienteRelacionamento .js-periodicidade').html(`Periodicidade: ${rtn.paciente.periodicidade}`);
								} else {
									$('#js-aside-pacienteRelacionamento .js-periodicidade').html(`Periodicidade: -`);
								}

								if(rtn.paciente.agendou_dias && rtn.paciente.agendou_dias.length>0) {
									$('#js-aside-pacienteRelacionamento .js-ultimoAtendimento').html(`Atendido há ${rtn.paciente.agendou_dias}`);
								} else {
									$('#js-aside-pacienteRelacionamento .js-ultimoAtendimento').html(`Nunca foi atendido(a)`);
								}

								if(rtn.paciente.musica && rtn.paciente.musica.length>0) {
									$('#js-aside-pacienteRelacionamento .js-musica').html(`<i class="iconify" data-icon="bxs:music"></i> ${rtn.paciente.musica}`);
								} else {
									$('#js-aside-pacienteRelacionamento .js-musica').html(``);
								}

								$("#js-aside-pacienteRelacionamento").fadeIn(100,function() {
									$('#js-aside-pacienteRelacionamento .js-profissionais').chosen();
									$('#js-aside-pacienteRelacionamento .js-tab').find('a:eq(0)').click();
									$("#js-aside-pacienteRelacionamento .aside__inner1").addClass("active");
								});

								$('#js-aside-pacienteRelacionamento input[name=agenda_data]').datetimepicker({
									timepicker:false,
									format:'d/m/Y',
									scrollMonth:false,
									scrollTime:false,
									scrollInput:false,
								}).css('background','');

								$('#js-aside-pacienteRelacionamento input[name=agenda_hora]').datetimepicker({
									  datepicker:false,
								      format:'H:i',
								      pickDate:false
								}).css('background','');

								$('#js-aside-pacienteRelacionamento input[name=id_paciente]').val(rtn.paciente.id);

								$('input[name=telefone1],.js-asPaciente-telefone1').mobilePhoneNumber({allowPhoneWithoutPrefix: '+55'}).bind('country.mobilePhoneNumber', function(echo, country) {
									let countryOut = country || '  ';
									$(this).parent().parent().find('.js-country').html(countryOut);
								}).trigger('keyup');

								$('#js-aside-pacienteRelacionamento .js-ag-historico section').find('.history2-item').remove();

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
																<br />																				
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


										$('#js-aside-pacienteRelacionamento .js-ag-historico section').append(html);
									})
								}
 
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
							const horarioDisponivel = (id_agenda) => {

								let agenda_data = $('#js-aside-pacienteRelacionamento input[name=agenda_data]').val();
								let agenda_duracao = $('#js-aside-pacienteRelacionamento select[name=agenda_duracao]').val();
								let id_cadeira = $('#js-aside-pacienteRelacionamento select[name=id_cadeira]').val();
								let id_profissional = $('#js-aside-pacienteRelacionamento select.js-profissionais').val();
								let agenda_horaObj = $('#js-aside-pacienteRelacionamento select[name=agenda_hora]');


								agenda_horaObj.find('option').remove();
								agenda_horaObj.append('<option value="">Carregando...</option>');

								obj = { agenda_data, agenda_duracao, id_cadeira, id_profissional }

								if(agenda_data.length>0 && agenda_duracao.length>0 && id_profissional.length>0 && id_cadeira.length>0) {
									let data = `ajax=asRelacionamentoPacienteHorarios&agenda_data=${agenda_data}&agenda_duracao=${agenda_duracao}&id_profissional=${id_profissional}&id_cadeira=${id_cadeira}&id_agenda=${id_agenda}`;
									
									$.ajax({
										type:"POST",
										url:baseURLApiAside,
										data:data,
										success:function(rtn) {
											agenda_horaObj.find('option').remove();
											if(rtn.success) {

												if(rtn.horariosDisponiveis.length>0) {
													agenda_horaObj.append(`<option value="">Selecione o horário</option>`)
													rtn.horariosDisponiveis.forEach(x=>{

														agenda_horaObj.append(`<option value="${x}">${x}</option>`)
													})
												} else {
													agenda_horaObj.append(`<option value="">Nenhum horário disponível</option>`)
												}
											} else if(rtn.error) {
												agenda_horaObj.append(`<option value="">${rtn.error}</option>`);
											}
										}
									});
								} else {
									agenda_horaObj.find('option').remove();
									agenda_horaObj.append(`<option value="">Complete os campos</option>`);
								}
							}

							$(function() {
								$('#js-aside-pacienteRelacionamento').on('change','select[name=agenda_duracao], select[name=id_cadeira],  select.js-profissionais, input[name=agenda_data]',function(){
								
									horarioDisponivel(0);
								
									
								});

								$('.js-tab a').click(function() {
									$(".js-tab a").removeClass("active");
									$(this).addClass("active");							
								});

								$('#js-aside-pacienteRelacionamento .js-btn-acao').click(function(){
									$('#js-aside-pacienteRelacionamento .js-btn-acao').removeClass('active');
									$(this).addClass('active');

									if($(this).attr('data-tipo')=="queroAgendar") {
										$('#js-aside-pacienteRelacionamento .js-ag-agendamento-naoQueroAgendar').hide();
										$('#js-aside-pacienteRelacionamento .js-ag-agendamento-queroAgendar').show();
										$('#js-aside-pacienteRelacionamento input[name=tipo]').val('queroAgendar');
									} else {
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
										let erro = '';

										if(agenda_data.length==0) erro='Defina a <b>Data do Agendamento</b>';
										else if(agenda_duracao.length==0) erro='Defina a <b>Duração de Agendamento</b>';
										else if(id_cadeira.length==0) erro='Defina o <b>Consultório do Agendamento</b>';
										else if(id_profissional.length==0) erro='Defina o <b>Profissional do Agendamento</b>';
										else if(agenda_hora.length==0) erro='Defina a <b>Hora do Agendamento</b>';

										if(erro.length==0) {

											let obj = $(this);

											if(obj.attr('data-loading')==0) {
												
												obj.html(`<span class="iconify" data-icon="eos-icons:loading"></span>`);
												obj.attr('data-loading',1);

												let data = `ajax=asRelacionamentoPacienteQueroAgendar&id_paciente=${id_paciente}&agenda_data=${agenda_data}&agenda_duracao=${agenda_duracao}&id_cadeira=${id_cadeira}&id_profissional=${id_profissional}&agenda_hora=${agenda_hora}`;
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


																swal({title: "Sucesso!", text: 'Agendamento realizado com sucesso!', type:"success", confirmButtonColor: "#424242"},function(){
																	document.location.reload();
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
													obj.html(`<i class="iconify" data-icon="fluent:add-circle-24-regular"></i>`);
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

											if(obj.attr('data-loading')==0) {
												
												obj.html(`<span class="iconify" data-icon="eos-icons:loading"></span>`);
												obj.attr('data-loading',1);

												let data = `ajax=asRelacionamentoPacienteNaoQueroAgendar&id_paciente=${id_paciente}&id_status=${status}&obs=${obs}`;
												$.ajax({
														type:'POST',
														data:data,
														url:baseURLApiAside,
														success:function(rtn) {
															if(rtn.success) {
																$('#js-aside-pacienteRelacionamento select[name=id_status]').val('');
																$('#js-aside-pacienteRelacionamento textarea[name=obs]').val('');


																swal({title: "Sucesso!", text: 'Observação cadastrada realizado com sucesso!', type:"success", confirmButtonColor: "#424242"},function(){
																	document.location.reload();
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
													obj.html(`<i class="iconify" data-icon="fluent:add-circle-24-regular"></i>`);
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
							<a href="javascript:;" onclick="$('.js-ag').hide(); $('.js-ag-whatsapp').show();">Whatsapp</a>			
							<a href="javascript:;" onclick="$('.js-ag').hide(); $('.js-ag-historico').show();">Histórico</a>					
						</section>
						
						<div class="js-ag js-ag-agendamento">

							<section class="filter">
								<div class="button-group">
									<a href="javascript:;" class="js-btn-acao js-btn-acao-queroAgendar button active" data-tipo="queroAgendar"><span>Quero agendar</span></a>
									<a href="javascript:;" class="js-btn-acao button" data-tipo="naoQueroAgendar"><span>Não consegui agendar</span></a>
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
											<select class="js-profissionais">
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
									<textarea style="height: 250px;" disabled><?php echo $wts->texto;?></textarea>
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
			<?php
		}
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
							<input type="text" class="js-asProfissoes-titulo" />
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
				</form>
			</div>
		</section>
		<?php
			}
		?>