<?php

	if(isset($_POST['ajax'])) {
		require_once("lib/conf.php");
		require_once("usuarios/checa.php");

		$rtn=array();
		if($_POST['ajax']=="persistirPaciente") {

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
		} else if($_POST['ajax']=="persistirPacienteTelefone") {

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
		} else if($_POST['ajax']=="indicacoesLista") {

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
		} else if($_POST['ajax']=="persistirNovoAgendamento") {
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
				$rtn=array('success'=>true);
			}
		} else if($_POST['ajax']=="persistirNovoHorario") {
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
				$rtn=array('success'=>true);
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

			$_cadeiras=array();
			$sql->consult($_p."parametros_cadeiras","*","where lixo=0 order by ordem asc");
			while($x=mysqli_fetch_object($sql->mysqry)) $_cadeiras[$x->id]=$x;

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

			$agendamentos=array();
			$where="where agenda_data>='".$data_inicio." 00:00:00' and agenda_data<='".$data_fim."' and lixo=0";

			if(isset($_GET['id_status']) and is_numeric($_GET['id_status'])) $where.=" and id_status='".$_GET['id_status']."'";
			if(isset($_GET['id_cadeira']) and is_numeric($_GET['id_cadeira'])) $where.=" and id_cadeira='".$_GET['id_cadeira']."'";
			if(isset($_GET['id_profissional']) and is_numeric($_GET['id_profissional'])) $where.=" and profissionais like '%,".$_GET['id_profissional'].",%'";
			if(isset($_GET['busca']) and !empty($_GET['busca'])) {
				$sql->consult($_p."pacientes","*","where nome like '%".addslashes($_GET['busca'])."%'");
				if($sql->rows) {
					$pacientesIDs=array();
					while($x=mysqli_fetch_object($sql->mysqry)) $pacientesIDs[]=$x->id;
					$where.=" and id_paciente IN (".implode(",",$pacientesIDs).")";
				} else $where.=" and 2=1";
			}


			$_usuarios=array();
			$sql->consult($_p."usuarios","id,nome","");
			while($x=mysqli_fetch_object($sql->mysqry)) {
				$_usuarios[$x->id]=$x;
			}

			$_status=array();
			$sql->consult($_p."agenda_status","id,titulo,cor","");
			while($x=mysqli_fetch_object($sql->mysqry)) {
				$_status[$x->id]=$x;
			}

			$sql->consult($_p."agenda","*",$where);
			if($sql->rows) {

				while($x=mysqli_fetch_object($sql->mysqry)) {
					//var_dump($x);
				
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

						$ftPaciente="arqs/pacientes/".$_pacientes[$x->id_paciente]->id.".".$_pacientes[$x->id_paciente]->foto;
						if(!file_exists($ftPaciente)) {
							$ftPaciente='';
						} else $ftPaciente.='?'.date('His');

						$nomeIniciais='L';
						$procedimentos=array();
						if(!empty($x->procedimentos)) {
							$procedimentosObj=json_decode($x->procedimentos);
							
							if(is_array($procedimentosObj)) {
								foreach($procedimentosObj as $p) {
									$procedimentos[]=utf8_encode($p->procedimento);
								}
							}
						}


						$profissionais='';
						if(!empty($x->profissionais)) {
							$profissioaisObj=explode(",",$x->profissionais);
							$profissionaisID=array(-1);
							foreach($profissioaisObj as $v) {
								if(!empty($v) and is_numeric($v)) $profissionaisID[]=$v;
							}

							$sql2->consult($_p."colaboradores","*","where id in (".implode(",",$profissionaisID).") and lixo=0");

							if($sql2->rows) {
								$cont=1;
								while($p=mysqli_fetch_object($sql2->mysqry)) {
									$ft="arqs/profissionais/".$p->id.".".$p->foto;
									/*if(file_exists($ft)) {
										$profissionais.='<div class="cal-item-foto"><span><img src="'.$ft.'" width="30" height="30" /></span></div>';
									} else {*/
										$aux=explode(" ",$p->nome);
										$aux[0]=strtoupper($aux[0]);

										/*if($aux[0] =="DR" or $aux[0]=="DR." or $aux[0]=="DRA" or $aux[0]=="DRA.") {
											$iniciais=strtoupper(substr($aux[1],0,1));
											if(isset($aux[2])) $iniciais.=strtoupper(substr($aux[2],0,1));
										} else {
											$iniciais=strtoupper(substr($aux[0],0,1));
											if(isset($aux[1])) $iniciais.=strtoupper(substr($aux[1],0,1));
										}*/
										$profissionais.='<div class="cal-item-foto"><span  style="background:'.$p->calendario_cor.'">'.utf8_encode($p->calendario_iniciais).'</span></div>';
										if($cont==2) {
											$profissionais.='<div class="cal-item-foto"><span>+'.($sql2->rows-2).'</span></div>';
											break;
										}
										$cont++;

									//}

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
						
						//	$pacienteNome=$_pacientes[$x->id_paciente]->nome;
						$agendamentos[]=array('agendaPessoal'=>0,																										
												'resourceId'=>$x->id_cadeira,'start'=>$dtStart,
												'end'=>$dtEnd,
												'hora'=>$hora,
												'horaFinal'=>$horaFinal,
												'nomeIniciais'=>$nomeIniciais,
												'foto'=>$ftPaciente,
												'cadeira'=>$cadeira,
												'id_paciente'=>$x->id_paciente,
												'duracao'=>$x->agenda_duracao."m",
												'indicacao'=>'',
												'title'=>$str->res($pacienteNome,20),
												'nomeCompleto'=>$pacienteNome,
												'telefone1'=>!empty($_pacientes[$x->id_paciente]->telefone1)?mask($_pacientes[$x->id_paciente]->telefone1):'',
												'instagram'=>utf8_encode($_pacientes[$x->id_paciente]->instagram),
												'musica'=>utf8_encode($_pacientes[$x->id_paciente]->musica),
												'situacao'=>utf8_encode($_pacientes[$x->id_paciente]->situacao),
												'id_status'=>$x->id_status,
												'idade'=>$idade,
												'profissionais'=>$profissionais,
												'color'=>'#FFF',
												'statusColor'=>(isset($_status[$x->id_status])?$_status[$x->id_status]->cor:''),
												'pontuacao'=>'1.548',
												'procedimentos'=>$procedimentos,
												'agendadoHa'=>$agendadoHa,
												'agendadoPor'=>$agendadoPor,
												'obs'=>empty($x->obs)?"-":utf8_encode($x->obs),
												'id'=>$x->id);
					} else if($x->agendaPessoal==1) {

						$cadeira=isset($_cadeiras[$x->id_cadeira])?utf8_encode($_cadeiras[$x->id_cadeira]->titulo):'-';
						$dtStart=date('Y-m-d\TH:i',strtotime($x->agenda_data));
						$dtEnd=date('Y-m-d\TH:i',strtotime($x->agenda_data_final));
						$hora=date('H:i',strtotime($x->agenda_data));
						$horaFinal=date('H:i',strtotime($x->agenda_data_final));

						

						

						$profissionais='';
						if(!empty($x->profissionais)) {
							$profissioaisObj=explode(",",$x->profissionais);
							$profissionaisID=array(-1);
							foreach($profissioaisObj as $v) {
								if(!empty($v) and is_numeric($v)) $profissionaisID[]=$v;
							}

							$sql2->consult($_p."colaboradores","*","where id in (".implode(",",$profissionaisID).") and lixo=0");

							if($sql2->rows) {
								$cont=1;
								while($p=mysqli_fetch_object($sql2->mysqry)) {
									$ft="arqs/profissionais/".$p->id.".".$p->foto;
									/*if(file_exists($ft)) {
										$profissionais.='<figure><span><img src="'.$ft.'" width="30" height="30" /></span></figure>';
									 else { */
										$aux=explode(" ",$p->nome);
										$aux[0]=strtoupper($aux[0]);

										/*

										if($aux[0] =="DR" or $aux[0]=="DR." or $aux[0]=="DRA" or $aux[0]=="DRA.") {
											$iniciais=strtoupper(substr($aux[1],0,1));
											if(isset($aux[2])) $iniciais.=strtoupper(substr($aux[2],0,1));
										} else {
											$iniciais=strtoupper(substr($aux[0],0,1));
											if(isset($aux[1])) $iniciais.=strtoupper(substr($aux[1],0,1));
										} 
										//$profissionais.='<figure><span>'. $iniciais.'</span></figure>'; */
										$profissionais.='<div class="cal-item-foto"><span  style="background:'.$p->calendario_cor.'">'.utf8_encode($p->calendario_iniciais).'</span></div>';
										if($cont==2) {
											$profissionais.='<figure><span>+'.($sql2->rows-2).'</span></figure>';
											break;
										}
										$cont++;

									//}
								}
							}
						}

						$dias=round((strtotime(date('Y-m-d H:i:s'))-strtotime($x->data))/(60 * 60 * 24));

						if($dias==0) $agendadoHa="Agendado <strong>HOJE</strong>";
						else if($dias==1) $agendadoHa="Agendado <strong>ONTEM</strong>";
						else $agendadoHa="Agendado há&nbsp;<strong>$dias</strong>&nbsp;dias";
						
						//	$pacienteNome=$_pacientes[$x->id_paciente]->nome;
						$agendamentos[]=array(
												'agendaPessoal'=>1,
												'resourceId'=>$x->id_cadeira,'start'=>$dtStart,
												'end'=>$dtEnd,
												'hora'=>$hora,
												'horaFinal'=>$horaFinal,
												'nomeIniciais'=>'AP',
												'foto'=>'',
												'cadeira'=>$cadeira,
												'id_paciente'=>0,
												'duracao'=>$x->agenda_duracao."m",
												'indicacao'=>'',
												'title'=>'Agendamento Pessoal',
												'telefone1'=>'',
												'instagram'=>'',
												'musica'=>'',
												'situacao'=>'',
												'idade'=>'',
												'profissionais'=>$profissionais,
												'color'=>'#FFF',
												'statusColor'=>(isset($_status[$x->id_status])?$_status[$x->id_status]->cor:''),
												'pontuacao'=>0,
												'agendadoHa'=>$agendadoHa,
												'procedimentos'=>array(),
												'id'=>$x->id,
												'obs'=>empty($x->obs)?"-":utf8_encode($x->obs));
					}
				}
			}

			$rtn=array('success'=>true,'agendamentos'=>$agendamentos);
			
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

	$_status=array();
	$sql->consult($_p."agenda_status","*","where  lixo=0 order by titulo asc");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_status[$x->id]=$x;
	}

	$_cadeiras=array();
	$sql->consult($_p."parametros_cadeiras","*","where lixo=0 order by titulo asc");
	while($x=mysqli_fetch_object($sql->mysqry)) $_cadeiras[$x->id]=$x;

	$_profissionais=array();
	$sql->consult($_p."colaboradores","id,nome,calendario_iniciais,foto,calendario_cor","where tipo_cro<>'' and lixo=0 order by nome asc");
	while($x=mysqli_fetch_object($sql->mysqry)) $_profissionais[$x->id]=$x;

	$_agendaStatus=array('confirmado'=>'CONFIRMADO','agendado'=>'AGENDADO');
	//  right:'dayGridMonth,resourceTimeGridOneDay,resourceTimeGridFiveDay,resourceTimeGridSevenDay'
	$_views=array("dayGridMonth"=>"MÊS",
					"resourceTimeGridOneDay"=>"1 dia",
					"resourceTimeGridFiveDay"=>"5 dias",
					"resourceTimeGridSevenDay"=>"7 dias");

	$_page=basename($_SERVER['PHP_SELF']);

?>
<script>
	var calendar = '';
	var dataKanban = '';
	
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

	const pacienteExistente = () => {
		$(`.js-paciente`).hide().find('input,select').removeClass('obg');;
		if($(`input[name=novoPaciente]`).prop('checked')===false) {
			$(`.js-pacienteExistente`).show().find('input,select').addClass('obg');
		} else {
			$(`.js-pacienteNovo`).show().find('input[name=telefone1],input[name=nome]').addClass('obg');;
		}
	}

	const agendaProcedimentosRemover = (index) => {
		let cont = 0;

		procedimentos=procedimentos.filter(x=> {
			if(cont++==index) return false;
			else return x;
		});

		console.log(procedimentos);

		agendaProcedimentosListar();
	}

	const agendaProcedimentosListar = () => {
		$(`.js-agenda-tableProcedimento tr.item`).remove();
		$(`.js-agenda-id_procedimento option`).prop('disabled',false);
		procedimentos.forEach(x => {
			let opcoesTxt='-';
			if(x.opcoes.length>0) {
				opcoesTxt = `<ul>`;
				x.opcoes.forEach(y => {
					opcoesTxt+=`<li>${y.titulo}</li>`;
				});
				opcoesTxt += `</ul>`;
			} 

			let html = `<tr class="item">
							<td>${x.procedimento}</td>
							<td>${x.regiao}</td>
							<td>${opcoesTxt}</td>
							<td>
								<a href="javascript:;" class="js-procedimentos-editar registros__acao registros__acao_sec"><i class="iconify" data-icon="bx:bxs-edit-alt"></i></a>
								<a href="javascript:;" class="js-procedimentos-remover registros__acao registros__acao_sec"><i class="iconify" data-icon="bx:bxs-trash"></i></a>
							</td>
						</tr>`;

			$(`.js-agenda-tableProcedimento`).append(html);

			$('.js-agenda-id_procedimento').find(`option[value=${x.id_procedimento}]`).prop('disasbled',true);
		});
		$('.js-agendonChangeDateTimea-id_procedimento').trigger('chosen:updated')
		$('.js-agenda-procedimentoJSON').val(JSON.stringify(procedimentos))
	}

	const calendarioVisualizacaoData = () => { 
		
		let date = calendar.getDate();

		let mesString='';

		if(date.getMonth()==0) mesString='JANEIRO'; 
		else if(date.getMonth()==1) mesString='FEVEREIRO'; 
		else if(date.getMonth()==2) mesString='MARÇO'; 
		else if(date.getMonth()==3) mesString='ABRIL'; 
		else if(date.getMonth()==4) mesString='MAIO'; 
		else if(date.getMonth()==5) mesString='JUNHO'; 
		else if(date.getMonth()==6) mesString='JULHO'; 
		else if(date.getMonth()==7) mesString='AGOSTO'; 
		else if(date.getMonth()==8) mesString='SETEMBRO'; 
		else if(date.getMonth()==9) mesString='OUTUBRO'; 
		else if(date.getMonth()==10) mesString='NOVEMBRO'; 
		else if(date.getMonth()==11) mesString='DEZEMBRO'; 

		let dateString = date.getDate()+" "+mesString+" "+date.getFullYear();

		console.log(dateString+' => '+calendar.view.title);
		$('.js-calendario-title').val(calendar.view.title);

	}

	function dia(d) {
		if(d==0) return "dom.";
		else if(d==1) return "seg.";
		else if(d==2) return "ter.";
		else if(d==3) return "qua.";
		else if(d==4) return "qui.";
		else if(d==5) return "sex.";
		else if(d==6) return "sáb.";
	}
	function unMes(m) {
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
	
	function calendarTitle(data) {
		//$('')
	}

	var filtroStatus=``;
	var filtroProfissional=``;
	var filtroCadeira=``;
	$(function(){
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
				//console.log(data);
				let dtJS = new Date(aux[2],aux[1],aux[0]);
				let newDt = new Date(aux[2],aux[1],aux[0]);
				if($('select.js-view').val()=="resourceTimeGridOneDay") {

				} else {
					newDt.setDate(newDt.getDate() - (dtJS.getDay()-1));
				}
				console.log(newDt);
				calendar.gotoDate(newDt);

				$('.js-calendario-title').val(calendar.view.title);
			}
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

			$('.js-calendario').val(calendar.view.title)
		});

		$('a.js-right').click(function(){
			if(calendar.view.type=="resourceTimeGridFiveDay") {
				let dtJS = new Date(calendar.getDate());
				dtJS.setDate(dtJS.getDate() + 7);
				let view = $(this).val();
				//calendar.changeView(view);
				//calendarioVisualizacaoData();
				$('.js-calendario-title').val(calendar.view.title);
				calendar.gotoDate(dtJS);
			} else {
				calendar.next();
			}
			$('.js-calendario-title').val(calendar.view.title);

			calendarioVisualizacaoData();
		});
		$('a.js-left').click(function(){ 
			if(calendar.view.type=="resourceTimeGridFiveDay") {
				let dtJS = new Date(calendar.getDate());
				dtJS.setDate(dtJS.getDate() - 7);
				let view = $(this).val();
				//calendar.changeView(view);
				//calendarioVisualizacaoData();
				$('.js-calendario-title').val(calendar.view.title);
				calendar.gotoDate(dtJS);
			} else {
				calendar.prev();
			}

			$('.js-calendario-title').val(calendar.view.title);
			calendarioVisualizacaoData();
		});
		$('a.js-today').click(function(){
			calendar.today();
			calendarioVisualizacaoData();
		});
		$('.js-status').change(function(){
			filtroStatus=$(this).val();
			calendar.refetchEvents();
		})
		$('.js-cadeira').change(function(){
			filtroCadeira=$(this).val();
			calendar.refetchEvents();
		})
		$('.js-profissionais').change(function(){
			filtroProfissional=$(this).val();
			calendar.refetchEvents();
		});
		$('.js-btn-fechar').click(function(){
			$('.cal-popup').hide();
		});
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
	.fc-timegrid-slot { height: 45px !important; // 1.5em by default }
	.fc-scrollgrid-sync-inner { height:90px; }
	.fc-scrollgrid  { border:none !important; }
	.fc-scrollgrid-liquid{ border:none !important; }
</style>

<section class="content">


		
		
		<?php

		require_once("includes/asideAgenda.php");
		$filtro='';

		if(isset($values['id_status']) and isset($_status[$values['id_status']])) $filtro.="&id_status=".$values['id_status'];
		if(isset($values['id_profissional']) and isset($_profissionais[$values['id_profissional']])) $filtro.="&id_profissional=".$values['id_profissional'];
		if(isset($values['id_cadeira']) and isset($_cadeiras[$values['id_cadeira']])) $filtro.="&id_cadeira=".$values['id_cadeira'];
		if(isset($values['busca']) and !empty($values['busca'])) $filtro.="&busca=".$values['busca'];

		//echo $filtro;
		?>
		<section id="cal-popup" class="cal-popup cal-popup_paciente cal-popup_top cal-popup_alt" style="left:703px; top:338px; margin-left:303px;display: none">
			<a href="javascript:;" class="cal-popup__fechar js-btn-fechar"><i class="iconify" data-icon="mdi-close"></i></a>
			<section class="paciente-info">
				<header class="paciente-info-header">
					<img src="" alt="" width="84" height="84" class="paciente-info-header__foto" style="" />
					<img src="img/loading.gif" width="20" height="20" class="js-loading" style="margin:30px;">
					<section class="paciente-info-header__inner1">
						<h1 class="js-nome"></h1>
						<p class="js-idade"></p>
						<p><span style="color:var(--cinza3);" class="js-id_paciente">#44</span> <span style="color:var(--cor1);"></span></p>
					</section>
				</header>
				<div class="abasPopover">
					<a href="javascript:;" onclick="$(this).parent().parent().find('a').removeClass('active');$(this).parent().parent().find('.js-grid').hide();$(this).parent().parent().find('.js-grid-info').show();$(this).addClass('active');" class="active">Informações</a>
					<a href="javascript:;" onclick="$(this).parent().parent().find('a').removeClass('active');$(this).parent().parent().find('.js-grid').hide();$(this).parent().parent().find('.js-grid-procedimentos').show();$(this).addClass('active');">Procedimentos</a>
					<a href="javascript:;" onclick="$(this).parent().parent().find('a').removeClass('active');$(this).parent().parent().find('.js-grid').hide();$(this).parent().parent().find('.js-grid-obs').show();$(this).addClass('active');">Observações</a>
				</div>
				<div class="paciente-info-grid js-grid js-grid-info">
					
				</div>
				<div class="paciente-info-grid js-grid js-grid-procedimentos" style="display:none;">							
				</div>
				<div class="paciente-info-grid js-grid js-grid-obs" style="display:none;font-size:12px;color:#666">							
				</div>
				<div class="paciente-info-opcoes">
					<select class="js-id_status">
						<?php
						foreach($_status as $v) {
							echo '<option value="'.$v->id.'">'.utf8_encode($v->titulo).'</option>';
						}
						?>
					</select>
					<a href="javascript:;" data-fancybox="" data-type="ajax" data-padding="0" class="js-hrefAgenda button" onclick="$('.cal-popup').hide();">Editar</a>
					
					<a href="javascript:;" target="_blank" class="js-hrefPaciente button button__sec"><i class="iconify" data-icon="bx:bxs-user"></i></a>
				</div>
			</section>
		</section>
		<div class="box-registros">
			<link href='https://cdn.jsdelivr.net/npm/fullcalendar-scheduler@5.3.0/main.min.css' rel='stylesheet' />
			<script src='https://cdn.jsdelivr.net/npm/fullcalendar-scheduler@5.3.0/main.min.js'></script>
			
			<script>
				var popViewInfos = [];

				const popView = (obj,id_agenda) => {
					$('#cal-popup')
							.removeClass('cal-popup_left')
							.removeClass('cal-popup_right')
							.removeClass('cal-popup_bottom')
							.removeClass('cal-popup_top');
					$('.js-id_status').attr('data-id',id_agenda);
					let clickTop=obj.getBoundingClientRect().top+window.scrollY;
					console.log(clickTop);
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
					$('#cal-popup .js-nome').html(popViewInfos[id_agenda].nomeCompleto);
					$('#cal-popup .js-idade').html(popViewInfos[id_agenda].idade.length>0?`${popViewInfos[id_agenda].idade} anos`:``);
					$('#cal-popup .js-id_paciente').html(`#${popViewInfos[id_agenda].id_paciente}`);
					$('#cal-popup .js-grid-info').html(popViewInfos[id_agenda].infos);
					$('#cal-popup .js-grid-procedimentos').html(popViewInfos[id_agenda].procedimentosLista);
					$('#cal-popup .js-grid-obs').html(popViewInfos[id_agenda].obs);
					$('#cal-popup .js-id_status').val(popViewInfos[id_agenda].id_status);
					$('#cal-popup .js-hrefAgenda').attr('href',`box/boxAgendamento.php?&id_agenda=${popViewInfos[id_agenda].id_agenda}`);
					$('#cal-popup .js-hrefPaciente').attr('href',`pg_contatos_pacientes_resumo.php?id_paciente=${popViewInfos[id_agenda].id_paciente}`);

					$('#cal-popup .js-loading').show();
					$('#cal-popup .paciente-info-header__foto').hide();

					if(popViewInfos[id_agenda].foto.length>0) {
						$('#cal-popup img.paciente-info-header__foto').attr({'src':popViewInfos[id_agenda].foto}).load(function(){

							$('#cal-popup .paciente-info-header__foto').show();
							$('#cal-popup .js-loading').hide();
						});
					} else {
						$('#cal-popup .js-loading').hide();
						$('#cal-popup .paciente-info-header__foto').hide();
					}
					
					console.log('top: '+clickTop+' leftOriginal: '+obj.getBoundingClientRect().left+' left+w: '+clickLeft+' wid: '+obj.getBoundingClientRect().width);
					
				}
				var calendar = '';
				var calpopID = 0;
				$(function(){

					$('#cal-popup').on('change','.js-id_status',function(){ 

						let id_agenda = $(this).attr('data-id');

						let id_status = $(this).val();
						let data = `ajax=alterarStatus&id_agenda=${id_agenda}&id_status=${id_status}`;

						$.ajax({
							type:"POST",
							url:"box/boxAgendamento.php",
							data:data,
							success:function(rtn) {
								if(rtn.success) {
									calendar.refetchEvents(); 
								} else if(rtn.error) {
									swal({title: "Erro!", text: rtn.error, type:"error", confirmButtonColor: "#424242"});
								} else {
									swal({title: "Erro!", text: 'Algum erro ocorreu durante a alteração de data deste agendamento!', type:"error", confirmButtonColor: "#424242"});
								}
							},
							error:function() {
									swal({title: "Erro!", text: 'Algum erro ocorreu durante a alteração de data deste agendamento!', type:"error", confirmButtonColor: "#424242"});
							}
						})
					})


					var calendarEl = document.getElementById('calendar'); 
					calendar = new FullCalendar.Calendar(calendarEl, {
					  	schedulerLicenseKey: 'GPL-My-Project-Is-Open-Source',

						locale: 'pt-br',
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
							$('#cal-popup').hide();
							$.fancybox.open({
								src  : `box/boxAgendamento.php?&data_agenda=${info.dateStr}`,
								type : 'ajax',
							});
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
						/*resourcesChange:function(arg) {
							setTimeout(function(){$('.fc-scrollgrid-sync-inner ').css('height','30px');},10);
						},*/
						datesSet:function(dateInfo) {
							
						},
					    events: function(info, successCallback, failure) {
							$.getJSON(`<?php echo $_page;?>?ajax=agenda&start=${info.start.valueOf()}&end=${info.end.valueOf()}&<?php echo $filtro;?>&id_status=${filtroStatus}&id_cadeira=${filtroCadeira}&id_profissional=${filtroProfissional}`,
										function (data) {
											if(data.success) {
											 	successCallback(data.agendamentos)
											}
										});
						},
						//events: 'https://fullcalendar.io/demo-events.json',

						eventContent: function (arg) { 
							var view = calendar.view.type;
							let nome = arg.event.title;
							let nomeCompleto = arg.event.extendedProps.nomeCompleto;
							let obs = arg.event.extendedProps.obs;
							let idade = arg.event.extendedProps.idade;
							let id = arg.event.id;
							let foto = arg.event.extendedProps.foto;
							let img = (arg.event.extendedProps.imageurl);

							let inicio = arg.event.extendedProps.hora;
							let fim = arg.event.extendedProps.horaFinal;
							let hora = `${inicio}-${fim}`;
							let duracao = arg.event.extendedProps.duracao;
							let cadeira = arg.event.extendedProps.cadeira;
							let id_paciente = arg.event.extendedProps.id_paciente;
							let nomeIniciais = arg.event.extendedProps.nomeIniciais;

							let situacao = arg.event.extendedProps.situacao;
							let agendaPessoal = arg.event.extendedProps.agendaPessoal;
							let indicacao = arg.event.extendedProps.indicacao;
							let pontuacao = arg.event.extendedProps.pontuacao;

							let instagram = arg.event.extendedProps.instagram;
							let id_status = arg.event.extendedProps.id_status;
							let telefone1 = arg.event.extendedProps.telefone1;
							let agendadoHa = arg.event.extendedProps.agendadoHa;
							let agendadoPor = arg.event.extendedProps.agendadoPor;
							let musica = arg.event.extendedProps.musica;
							let statusColor = arg.event.extendedProps.statusColor;
							let procedimentos = arg.event.extendedProps.procedimentos;
							let profissionais = arg.event.extendedProps.profissionais;
							let id_agenda = arg.event.id;
							let infos = ``;

   							
   							linkFichaPaciente=``;
   							if(agendaPessoal==0) linkFichaPaciente=`<a href="pg_contatos_pacientes_resumo.php?id_paciente=${id_paciente}" target="_blank" class="button button__sec"><i class="iconify" data-icon="bx:bxs-user"></i></a>`;
							
							if(profissionais.length!=0) profissionais = `<div class="cal-item__fotos">${profissionais}</div>`; 
							

						    if(instagram.length>0) infos+=`<p class="paciente-info-grid__item"><i class="iconify" data-icon="mdi-instagram"></i> ${instagram}</p>`;
						    else infos+=`<p class="paciente-info-grid__item"><i class="iconify" data-icon="mdi-instagram"></i> -</p>`;

						    if(telefone1.length>0) infos+=`<p class="paciente-info-grid__item"><i class="iconify" data-icon="mdi-phone"></i> ${telefone1}</p>`;
						    else infos+=`<p class="paciente-info-grid__item"><i class="iconify" data-icon="mdi-phone"></i> -</p>`;

						    if(musica.length>0) infos+=`<p class="paciente-info-grid__item"><i class="iconify" data-icon="mdi-music"></i> ${musica}</p>`;
						    else infos+=`<p class="paciente-info-grid__item"><i class="iconify" data-icon="mdi-music"></i> -</p>`;

						    // if(agendadoHa.length>0) infos+=`<p class="paciente-info-grid__item"><i class="iconify" data-icon="bi:calendar-check"></i> ${agendadoHa}</p>`;
						    // else infos+=`<p class="paciente-info-grid__item"><i class="iconify" data-icon="bi:calendar-check"></i> -</p>`;

						    if(indicacao.length>0) infos+=`<p class="paciente-info-grid__item"><i class="iconify" data-icon="mdi-hand-pointing-right"></i> ${indicacao}</p>`;
						    else infos+=`<p class="paciente-info-grid__item"><i class="iconify" data-icon="mdi-hand-pointing-right"></i> -</p>`;

						    if(agendadoPor) infos+=`<p class="paciente-info-grid__item" style="grid-column:span 2"><i class="iconify" data-icon="bi:calendar-check"></i> <span><strong>${agendadoPor}</strong> ${agendadoHa}</span></p>`;
						    else infos+=`<p class="paciente-info-grid__item"><i class="iconify" data-icon="bi:calendar-check"></i> -</p>`;

						    /*if(pontuacao.length>0) {
						    	infos+=`<p class="paciente-info-grid__item"><i class="iconify" data-icon="mdi-star"></i> ${pontuacao} <span class="iconify" data-icon="fe:link-external" data-inline="false"></span></p>`;
						    }*/

						   //if(foto.length>0) foto=`<img src="${foto}" alt="" width="84" height="84" class="paciente-info-header__foto" />`;
						 //  if(foto.length>0) foto=`<img src="${foto}" alt="" width="84" height="84" class="paciente-info-header__foto" />`;
							
						    let procedimentosLista='-';
						    if(procedimentos && procedimentos.length>0) {
						    	procedimentosLista='';
						    	procedimentos.forEach(p=>{
						    		procedimentosLista+=`<p class="paciente-info-grid__item"><i class="iconify" data-icon="fluent:dentist-12-regular"></i> ${p}</p>`; 
						    	})
						    }


						   
							
						    popInfos = {};
						    popInfos.nome = nome;
						    popInfos.nomeCompleto = nomeCompleto;
						    popInfos.idade = idade;
						    popInfos.id_paciente = id_paciente;
						    popInfos.situacao = situacao;
						    popInfos.obs = obs;
						    popInfos.infos=infos;
						    popInfos.id_status=id_status;
						    popInfos.id_agenda=id_agenda;
						    popInfos.foto=foto.length>0?foto:'';
						    popInfos.procedimentosLista=procedimentosLista;

							popViewInfos[id_agenda] = popInfos;


							cardView=`<section class="cal-popup  cal-popup_paciente" style="display:none;">
											<a href="javascript:$('.cal-popup').hide();" class="cal-popup__fechar"><i class="iconify" data-icon="mdi-close"></i></a>
											<section class="paciente-info">
												<header class="paciente-info-header">
													${foto}
													<section class="paciente-info-header__inner1">
														<h1>${nome}</h1>
														<p>${idade} anos</p>
														<p><span style="color:var(--cinza3);">#${id_paciente}</span> <span style="color:var(--cor1);">${situacao}</span></p>
													</section>
												</header>
												<div class="abasPopover">
													<a href="javascript:;" onclick="$(this).parent().parent().find('a').removeClass('active');$(this).parent().parent().find('.js-grid').hide();$(this).parent().parent().find('.js-grid-info').show();$(this).addClass('active');" class="active">Informações</a>
													<a href="javascript:;" onclick="$(this).parent().parent().find('a').removeClass('active');$(this).parent().parent().find('.js-grid').hide();$(this).parent().parent().find('.js-grid-procedimentos').show();$(this).addClass('active');">Procedimentos</a>
												</div>
												<div class="paciente-info-grid js-grid js-grid-info">
													${infos}
												</div>

												<div class="paciente-info-grid js-grid js-grid-procedimentos" style="display:none;">
													${procedimentosLista}
												</div>
												<div class="paciente-info-opcoes">
													<select>
														<option value="">opcao 1</option>
														<option value="">opcao 2</option>
														<option value="">opcao 3</option>
													</select>
													<a href="box/${agendaPessoal==1?"boxAgendamentoPessoal":"boxAgendamento"}.php?id_agenda=${id_agenda}" data-fancybox data-type="ajax" data-padding="0" class="button" onclick="$('.cal-popup').hide();">Editar</a>
													
													${linkFichaPaciente}
												</div>
											</section>
							    		</section>`;

						    if(view=="dayGridMonth") {
						    	eventHTML=`<section class="cal-item" style="height:100%;border-left:6px solid ${statusColor};" >
												${cardView}
												<section onclick="popView(this,${id_agenda});">
													 <p>${hora}</p>
													 <h1 class="cal-item__titulo">${nome}</h1>
												</section>
											</section>`
						    } else {
						    	eventHTML=`<section class="cal-item" style="height:100%;border-left:6px solid ${statusColor};" >
												${cardView}
												<section class="cal-item__inner1" style="height:100%"  onclick="popView(this,${id_agenda});">
													<div class="cal-item-dados">
														<h1>${hora} - ${cadeira}</h1>
														<h2>${nome}</h2>
													</div>
													${profissionais}
												</section>
											</section>`
						    }
							return { html: eventHTML }
						},
						dayHeaderContent: function (arg) {
							console.log(calendar.view.type);
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

					$('.js-calendario-title').val(calendar.view.title);
				});
				$(function(){
					$(document).mouseup(function(e)  {
					    var container = $("#cal-popup");
					    // if the target of the click isn't the container nor a descendant of the container
					    if (!container.is(e.target) && container.has(e.target).length === 0) 
					    {
					       $('#cal-popup').hide();
					    }
					});
				});

			</script>

			
			<div id='calendar'></div>

			
			
		</div>

</section>
			
<?php
	include "includes/footer.php";
?>