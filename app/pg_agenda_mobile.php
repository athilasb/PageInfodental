<?php
	
	require_once("lib/conf.php");
	require_once("usuarios/checa.php");
	$_table = $_p."agenda";

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
		$_tags[$x->id]=$x;
	}

	$_checklist=array();
	$sql->consult("infodentalADM.infod_parametros_agenda_checklist","*","WHERE lixo=0");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_checklist[$x->id]=$x;
	}

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
	$infozap->infosWasabi=array('_wasabiPathRoot'=>$_wasabiPathRoot,
							'wasabiS3'=>$wasabiS3,
							'_wasabiBucket'=>$_wasabiBucket);

	if(isset($_POST['ajax'])) {

		$rtn=array();
		/*if($_POST['ajax']=="remover") {
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
		else */
		if($_POST['ajax']=="indicacoesLista") {

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
				$vSQL="agenda_data_original='$agenda->agenda_data',agenda_data='".$novaData."'";
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

				// atualiza data do agendamento
				$sql->update($_p."whatsapp_mensagens","fila_agenda_data='$novaData'","where id_agenda=$agenda->id");

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
					
					$dif=(strtotime($end)-strtotime($start))/60;
					$vSQL.=",agenda_duracao='$dif'";
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
		else if($_POST['ajax']=="whatsappDisparar") {

			$infozap->dispara();
			$rtn=array('success'=>true);
		}
		else if($_POST['ajax']=="atualizaFoto") {


			$id_paciente = (isset($_POST['id_paciente']) and is_numeric($_POST['id_paciente']))?$_POST['id_paciente']:0;


			if(!empty($id_paciente)) {
				if($infozap->atualizaFoto($id_paciente)) {
					$rtn=array('success'=>true);
				} else {
					$rtn=array('success'=>false,'erro'=>isset($infozap->erro)?$infozap->erro:'Algum erro ocorreu durante o getprofile');
				}
			} else {
				$rtn=array('success'=>false,'Paciente não passado!');
			}
		}

		else if($_POST['ajax']=="checklistItens") {
			$regs=array();
			$sql->consult("infodentalADM.infod_parametros_agenda_checklist","*","WHERE lixo=0");
			while($x=mysqli_fetch_object($sql->mysqry)) {
				$regs[]=array('id' => $x->id,
							  'titulo' => utf8_encode($x->titulo));
			}

			$rtn=array('success'=>true,'regs'=>$regs);
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

			$_tags=array();
			$sql->consult($_p."parametros_tags","*","WHERE lixo=0 order by titulo asc");
			while($x=mysqli_fetch_object($sql->mysqry)) {
				$_tags[$x->id]=$x;
			}

			$_checklist=array();
			$sql->consult("infodentalADM.infod_parametros_agenda_checklist","*","WHERE lixo=0");
			while($x=mysqli_fetch_object($sql->mysqry)) {
				$_checklist[$x->id]=$x;
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

				$_agendaHorarios=[];

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

						$aux = explode(",",$x->tags);
						$tags=array();
						foreach($aux as $id_tag) {
							if(!empty($id_tag) and is_numeric($id_tag)) {
								if(isset($_tags[$id_tag])) {
									$p=$_tags[$id_tag];
									$tags[]=array('titulo'=>utf8_encode($p->titulo),
												  'cor'=>$p->cor);
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
												'duracao'=>(int)$x->agenda_duracao,
												'horaFinal'=>$horaFinal,
												'futuro'=>$futuro,
												'cadeira'=>$cadeira,
												'id_paciente'=>$x->id_paciente,
												'title'=>($pacienteNome),
												'nomeCompleto'=>$pacienteNome,
												'situacao'=>utf8_encode($_pacientes[$x->id_paciente]->situacao),
												'id_status'=>$x->id_status,
												'profissionais'=>$profissionais,
												'tags'=>$tags,
												'color'=>'#FFF',
												'statusColor'=>(isset($_status[$x->id_status])?$_status[$x->id_status]->cor:''),
												'id'=>$x->id,
												'wts'=>(int)isset($_agendamentosConfirmacaoWts[$x->id])?1:0
											);



						$diaSemana = date('w',strtotime($dtStart));
						$_agendaHorarios[$x->id_cadeira][$diaSemana][]=array('inicio'=>$dtStart,'fim'=>$dtEnd);

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

						// Horários que não atende
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

						// Horários que atendem
						else {	

							
							$horarioOscioso=true;
							$iN = (date('H:i',strtotime($dt)));
							$iF = (date('H:i',strtotime($dt." + 30 minutes")));

							//echo $iN." ".$iF."\n";
							if(isset($_agendaHorarios[$c->id][$diaSemana])) {
								foreach($_agendaHorarios[$c->id][$diaSemana] as $arr) {

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
										$horarioOscioso=false;
										break;
									} else if(strtotime($xi) >= strtotime($iN) && 
										strtotime($xi) < strtotime($iF) && 
										strtotime($xf) < strtotime($iN)  && 
										strtotime($xf) > strtotime($iF) ) { 
										//echo "ATENDEe\n";
										$horarioOscioso=false;
										break;
									} else if(strtotime($xi) <= strtotime($iN) && 
										strtotime($xi) < strtotime($iF) && 
										strtotime($xf) > strtotime($iN)  && 
										strtotime($xf) >= strtotime($iF) ) { 
										//echo "ATENDEe\n";
										$horarioOscioso=false;
										break;
									}
								}

								if($horarioOscioso===true) {
									/*$agendamentos[]=array('agendaPessoal'=>2,
											'resourceId'=>$idCadeira,
											'start'=>str_replace(" ","T",$dt),
											'end'=>str_replace(" ","T",date('Y-m-d H:i',strtotime($dt." + 30 minutes"))),
											'cadeira'=>utf8_encode($c->titulo),
											'color'=>'red',
											'display'=>'background'
										);*/
								}
							}




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

			$sql->consult($_p."pacientes","nome,id,telefone1,cpf,foto_cn,foto",$where);
			//echo $where;
			while($x=mysqli_fetch_object($sql->mysqry)) {

				$ft='img/ilustra-perfil.png';
				if(!empty($x->foto_cn)) {
					$ft=$_cloudinaryURL.'c_thumb,w_100,h_100/'.$x->foto_cn;
				} else if(!empty($x->foto)) {
					$ft=$_wasabiURL."arqs/clientes/".$x->id.".jpg";
				}

				$rtn['items'][]=array('id'=>$x->id,
										'text'=>utf8_encode($x->nome),
										'nome'=>utf8_encode($x->nome),
										'telefone'=>utf8_encode($x->telefone1),
										'ft'=>$ft,
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

	$data_inicio=date('Y-m-d 00:00:00');
	if(isset($_GET['data']) and !empty($_GET['data'])) {
		$data_inicio=invDate($_GET['data'])." 00:00:00";
	}

	$data_fim=date('Y-m-d 23:59:59',strtotime(date($data_inicio)." + 7 days"));


	$dataDia=date('d',strtotime($data_inicio));
	$dataMes=substr(strtolower(mes(date('m',strtotime($data_inicio)))),0,3);
	$dataDiaNome=strtolower($_dias[date('w',strtotime($data_inicio))]);

	$where = "where agenda_data>='$data_inicio' and agenda_data<='$data_fim' and lixo=0";

	if(isset($values['id_profissional']) and is_numeric($values['id_profissional'])) $where.=" and profissionais like '%,".$values['id_profissional'].",%'";
	if(isset($values['id_cadeira']) and is_numeric($values['id_cadeira'])) $where.=" and id_cadeira = '".$values['id_cadeira']."'";

	$registros=$pacientesIds=[];
	$sql->consult($_p."agenda","*",$where);
	//echo $where.$sql->rows;
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$registros[date('Ymd',strtotime($x->agenda_data))][]=$x;
		$pacientesIds[]=$x->id_paciente;
		$cadeirasIds[]=$x->id_cadeira;
	}


	$_pacientes=[];
	if(count($pacientesIds)>0) {
		$sql->consult($_p."pacientes","id,nome","where id in (".implode(",",$pacientesIds).")");
		while($x=mysqli_fetch_object($sql->mysqry)) {
			$_pacientes[$x->id]=$x;
		}
	}

	$_cadeiras=[];
	$sql->consult($_p."parametros_cadeiras","id,titulo,lixo","order by titulo asc");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_cadeiras[$x->id]=$x;
	}
	

	$_profissionais=[];
	$sql->consult($_p."colaboradores","id,nome,calendario_iniciais,calendario_cor,lixo,check_agendamento","order by nome asc");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_profissionais[$x->id]=$x;
	}
?> 

	<?php /*<div class="cal-date-floater">
		<h1 class="js-cal-titulo-diames"><?php echo $dataDia;?></h1>
		<h1 class="js-cal-titulo-mes" style="font-weight:bold"><?php echo $dataMes;?></h1>
		<h2 class="js-cal-titulo-dia"><?php echo $dataDiaNome;?></h2>
	</div>*/ ?>

	

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
						<h1 class="js-cal-titulo-diames"><?php echo $dataDia;?></h1>
						<h2 class="js-cal-titulo-mes"><?php echo $dataMes;?></h2>
						<h3 class="js-cal-titulo-dia"><?php echo $dataDiaNome;?></h3>
					</div>
				</section>
			</div>

		</div>
	</header>


	<script type="text/javascript">
		var agendaMobile=1;
		$(function(){
			$('.js-filtro').change(function(){
				let id_profissional = $('select[name=id_profissional]').val();
				let id_cadeira = $('select[name=id_cadeira]').val();
				let data = $('input[name=data]').val();
				$.fancybox.open({src:"#loading",modal:true});
				document.location.href=`<?php echo basename($_SERVER['PHP_SELF']);?>?data=${data}&id_profissional=${id_profissional}&id_cadeira=${id_cadeira}`;
			});
			$('.js-calendario').datetimepicker({
				timepicker:false,
				format:'d F Y',
				scrollMonth:false,
				scrollTime:false,
				scrollInput:false,
				onChangeDateTime:function(dp,dt) {
					let val = dt.val();
					let aux = val.split(' ');
					aux[1]=eval(unMes(aux[1]));
					aux[1]++;
					aux[1]=d2(aux[1]);
					let data = `${aux[0]}/${(aux[1])}/${aux[2]}`;
					$('input[name=data]').val(`${data}`).trigger('change');
				}
			});
			$('.js-agenda').click(function(){
				let id_agenda = $(this).attr('data-id_agenda');	
				popView(id_agenda);
			})
		})
	</script>
	<main class="main">
		<div class="main__content content">
			<input type="hidden" name="data" class="js-filtro" value="<?php echo date('d/m/Y',strtotime($data_inicio));?>" />
			<section class="filter filter--sticky">
				<div class="filter-group" style="width:100%;">
					<div class="filter-form form" style="width:100%;">
						<dl style="flex:0;">
							<dd><a href="javascript:;" class="button button_main js-novoAgendamento"><i class="iconify" data-icon="fluent:add-circle-24-regular"></i></a></dd>
						</dl>
						<dl style="flex:0;">
							<dd><a href="javascript:;" class="button js-calendario"><span class="iconify" data-icon="bi:calendar-week" data-inline="false" data-width="20"></span></a></dd>
						</dl>
						<dl style="flex:1">
							<dd>
								<select name="id_cadeira" class="chosen js-filtro" data-placeholder="Todos consultórios">
									<option value=""></option>
									<?php
									foreach($_cadeiras as $c) {
										if($c->lixo==1) continue;
										echo '<option value="'.$c->id.'"'.((isset($values['id_cadeira']) and $values['id_cadeira']==$c->id)?' selected':'').'>'.utf8_encode($c->titulo).'</option>';
									}
									?>
								</select>
							</dd>
						</dl>
						<dl style="flex:1;">
							<dd>
								<select name="id_profissional" class="chosen js-filtro" data-placeholder="Todos Profissionais">
									<option value=""></option>
									<?php
									foreach($_profissionais as $p) {
										if($p->lixo==1 or $p->check_agendamento==0) continue;
										echo '<option value="'.$p->id.'"'.((isset($values['id_profissional']) and $values['id_profissional']==$p->id)?' selected':'').'>'.utf8_encode($p->nome).'</option>';
									}
									?>
								</select>
							</dd>
						</dl>					
					</div>
				</div>
			</section>

			<section class="mcal">
				<?php
				if(count($registros)>0) {
					foreach($registros as $data=>$regs) {

						$ano=substr($data,0,4);
						$mes=substr($data,4,2);
						$dia=substr($data,6,2);

						$diaSemana=date('w',strtotime($ano."-".$mes."-".$dia));
						$diaSemana=isset($_dias[$diaSemana]) ? substr(strtolowerWLIB($_dias[$diaSemana]),0,3) : '';
						?>
						<div class="mcal-item">
							<aside class="mcal-date">
								<p class="mcal-date__week"><?php echo $diaSemana;?></p>
								<p class="mcal-date__day"><?php echo $dia;?></p>
								<p class="mcal-date__month"><?php echo strtolower(substr(mes($mes),0,3));?></p>
							</aside>
							<article class="mcal-events">
								<?php
								foreach($regs as $x) {
									
									$cadeira = isset($_cadeiras[$x->id_cadeira]) ? $_cadeiras[$x->id_cadeira] : '';
									$horaInicio = date('H:i',strtotime($x->agenda_data));
									$horaFinal = date('H:i',strtotime($x->agenda_data." + $x->agenda_duracao minutes"));

									$profissionaisIniciais='';
									if(!empty($x->profissionais)) {
										$auxProfissionais=explode(",",$x->profissionais);
										foreach($auxProfissionais as $idProfissional) {
											if(!empty($idProfissional) and isset($_profissionais[$idProfissional])) {
												$profissional = $_profissionais[$idProfissional];

												$profissionaisIniciais .= '<span style="background:'.$profissional->calendario_cor.'">'.$profissional->calendario_iniciais.'</span>';
											}
										}
									}


									if($x->agendaPessoal==1) {
									?>
									<section class="cal-item js-agenda" data-id_agenda="<?php echo $x->id;?>" style="border-left:6px solid var(--cinza2);">
										<section class="cal-item__inner1">
											<div class="cal-item-dados">
												<h2 style="margin-top:0">Agendamento Pessoal</h2>
												<h1><?php echo $horaInicio."-".$horaFinal;?> - <?php echo utf8_encode($cadeira->titulo);?></h1>
											</div>
											<div class="cal-item__fotos"><div class="cal-item-foto"><?php echo $profissionaisIniciais;?></div></div>
										</section>
									</section>
									<?php
									} else {
										$paciente = isset($_pacientes[$x->id_paciente]) ? $_pacientes[$x->id_paciente] : '';

									?>
									<section class="cal-item js-agenda" data-id_agenda="<?php echo $x->id;?>" style="border-left:6px solid #1182ea;">
										<section class="cal-item__inner1">
											<div class="cal-item-dados">
												<h2 style="margin-top:0"><?php echo utf8_encode($paciente->nome);?></h2>
												<h1><?php echo $horaInicio."-".$horaFinal;?> - <?php echo utf8_encode($cadeira->titulo);?></h1>
											</div>
											<div class="cal-item__fotos"><div class="cal-item-foto"><?php echo $profissionaisIniciais;?></div></div>
										</section>
									</section>
									<?php
									}
								}
								?>
							</article>
						</div>
						<?php
					}
				} else {
					?>
					<p style="color:var(--cinza4);text-align: center;"><span class="iconify" data-icon="fe:cry" data-height="40"></span><br />Nenhum agendamento registrado.</p>
					<?php
				}
				?>

			</section>
		</div>
	</main>


<?php 
	
	$apiConfig=array('agenda'=>1,
						'paciente'=>1,
						'proximaConsulta'=>1);

	require_once("includes/api/apiAside.php");

	$apiConfig=array('procedimentos'=>1);
	require_once("includes/api/apiAsidePaciente.php");


	include "includes/footer.php";
?>	