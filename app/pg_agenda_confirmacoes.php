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


				# Agendamentos a confirmar (hj, amanha e depois de amanha): id_status=1 -> a confirmar
					$where="where agenda_data>='".$dataWH." 00:00:00' and agenda_data<='".date('Y-m-d',strtotime($dataWH." + 2 day"))." 23:59:59' and id_status=1 and lixo=0 order by agenda_data asc";

					$registros=array();
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
							$agenda[]=(object) array('id_agenda'=>$x->id,
														'data'=>$dataAg,
														'hora'=>date('H:i',strtotime($x->agenda_data))." às ".date('H:i',strtotime($x->agenda_data)),
														'id_status'=>$idStatus,
														'paciente'=>ucwords(strtolowerWLIB(utf8_encode($_pacientes[$x->id_paciente]->nome))),
														'telefone1'=>mask($_pacientes[$x->id_paciente]->telefone1),
														'evolucao'=>isset($pacientesEvolucoes[$x->id_paciente])?1:0,
														'dias'=>$dias,
														'procedimentos'=>'',
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


					$pacientesTratamentos=array();
					$sql->consult($_p."agenda","distinct id_paciente","where agenda_data>='".date('Y-m-d')." 00:00:00' and 
																				id_paciente IN (".implode(",",$pacientesEmTratamentosSemHorarioIds).") and 
																				id_status IN (1,2) and lixo=0");
					if($sql->rows) {
						while($x=mysqli_fetch_object($sql->mysqry)) {
							//echo $x->id_paciente." ";
							unset($pacientesEmTratamentosSemHorarioIds[$x->id_paciente]);
						}
					}



					$agendaTratamento=array();
					foreach($pacientesEmTratamentosSemHorarioIds as $id_paciente) {
						if(isset($_pacientes[$id_paciente])) {
							$paciente=$_pacientes[$id_paciente];
							if(is_object($paciente)) {
								$agendaTratamento[]=array('id_paciente'=>$paciente->id,
															'nome'=>utf8_encode($paciente->nome),
															'telefone1'=>$paciente->telefone1);
							}
						} 
					}


					$agendaInteligencia=array();
					foreach($pacientesDeInteligencia as $id_paciente) {
						if(isset($agendasDosUltimos6meses[$id_paciente])) continue;
						if(isset($_pacientes[$id_paciente])) {
							$paciente=$_pacientes[$id_paciente];

							if(is_object($paciente)) {
								if($paciente->codigo_bi==7) continue; // excluidos
								$ag=isset($_ulAg[$paciente->id])?$_ulAg[$paciente->id]:'';
								$agendaInteligencia[]=array('id_paciente'=>$paciente->id,
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
		} else if ($_POST['ajax']=="alterarStatus") {

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
		} else if($_POST['ajax']=="confirmarAgendamento") {
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
 				$sql->consult($_p."profissionais","*","where id='".$_POST['id_profissional']."'");
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

			if(is_object($agenda)) {
				if(!empty($data)) {
					if(is_object($profissional)) {
						if(is_object($cadeira)) {
							$dataInicio=$data." 07:00:00";	
							$dataFim=$data." 23:59:59";

							echo $dataInicio." - $dataFim -> $tempo\n\n";
							do {
								$di=$dataInicio;
								$dataInicio=date('Y-m-d H:i:s',strtotime($dataInicio." + $tempo minutes"));
								$df=$dataInicio;

								$where="WHERE agenda_data='".$di."' and 
												profissionais like '%,$profissional->id,%' and 
												id_cadeira=$cadeira->id and id_status NOT IN (3,4) and lixo=0";

								$sql->consult($_p."agenda","*",$where);

								echo $where." -> $sql->rows\n";
								while($x=mysqli_fetch_object($sql->mysqry)) {
									echo $x->agenda_data." - $x->agenda_data_final\n";
								}
							} while(strtotime($dataInicio)<strtotime($dataFim));

						


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


	$selectTempo='';
	foreach($optAgendaDuracao as $v) {
		$selectTempo.='<option value="'.$v.'">'.$v.' min</option>';
	}

?>

	<section class="content">  

		<?php
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

			const agendaAtualizar = () => {

				let dataAjax = `ajax=agenda&data=${data}`;
				$.ajax({
					type:"POST",
					data:dataAjax,
					success:function(rtn) {
						if(rtn.success) {
							agenda=rtn.agenda;
							agendaTratamento=rtn.agendaTratamento
							agendaInteligencia=rtn.agendaInteligencia
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

 					let html = `<div class="kanban-card">
									<a href="javascript:;" onclick="$(this).next('.kanban-card-modal').show();$(this).next('.kanban-card-modal').find('.js-opcoes').show();$(this).next('.kanban-card-modal').find('.js-acoes').hide();" class="kanban-card-dados js-kanban-item ${barra}" style="background-color:${cor}" data-id="${x.id_agenda}">
										<p class="kanban-card-dados__data">
											<i class="iconify" data-icon="ph:calendar-blank"></i>
											${x.data} &bull; ${x.hora}
										</p>
										<h1>${x.paciente}</h1>
										<h2>${x.telefone1}</h2>
										<h2>${agendadoHa}</h2>
									</a>
									<div class="kanban-card-modal js-kanban-item-modal" style="display:none;">
										<div class="kanban-card-modal__inner1">
											<a class="kanban-card-modal__fechar" href="javascript:;" onclick="$(this).parent().parent().hide(); $('.js-reagendar, .js-cancelar').hide(); $('.js-opcoes').show();"><i class="iconify" data-icon="ph-x"></i></a>
											<h1>${x.paciente}</h1>
											<h2>${x.telefone1}</h2>
											<h2>${x.procedimentos}</h2>
										</div>
										<div class="kanban-card-modal__inner2 js-opcoes">
											${btnConfirmar}
											<a href="javascript:;" onclick="$(this).parent().hide(); $(this).parent().nextAll('.js-reagendar').show();" class="button button__full" style="background-color:purple;">Reagendar Agendamento</a>
											<a href="javascript:;" onclick="$(this).parent().hide(); $(this).parent().nextAll('.js-cancelar').show();" class="button button__full" style="background-color:#f9de27;">Desmarcar Agendamento</a>
										</div>
										<div class="kanban-card-modal__inner2 js-reagendar js-acoes" style="display:none;">
											<form class="js-form-${x.id_agenda}">
												<input type="text" class="js-input-id" placeholder="" />
												<input type="text" class="js-input-data" placeholder="" />
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
												<button type="submit" class="button button__full" style="background:var(--amarelo);">Reagendar</button>
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

					$(`#kanban input.js-input-id:last`).val(x.id_agenda);
					$(`#kanban select.js-select-tempo:last`).val(x.agenda_duracao);
					$(`#kanban select.js-select-profissional:last`).val(x.id_profissional);
					$(`#kanban select.js-select-cadeira:last`).val(x.id_cadeira);
					$(`#kanban input.js-input-data:last`).datetimepicker({
															timepicker:false,
															format:'d/m/Y',
															scrollMonth:false,
															scrollTime:false,
															scrollInput:false,
														}).val(`${x.agenda_data}`);
					$(`#kanban select.js-select-horario:last`).append(`<option value="${x.agenda_hora}">${x.agenda_hora}</option>`);
				});
				
				$('.js-qtd-reagendar').html(qtdReagendar);
			}	

			const pacientesTratamento = () => {

				$(`#kanban .js-kanban-status-semHorario .js-kanban-item,#kanban .js-kanban-status-semHorario .js-kanban-item-modal`).remove();

				popViewInfos = [];

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
				
					let html = `<a href="javascript:;" onclick="$(this).next('.kanban-card-modal').show();" class="kanban-card-dados js-kanban-item" data-id="${x.id_paciente}">
									${barra}
									<h1>${x.nome}</h1>
									<h2>${x.telefone1}</h2>
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
				
					let html = `<a href="javascript:;" onclick="$(this).next('.kanban-card-modal').show();" class="kanban-card-dados js-kanban-item" data-id="${x.id_paciente}">
									${barra}
									<h1>${x.nome}</h1>
									<h2>${x.telefone1}</h2>
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

			const horarioDisponivel = (id_agenda) => {

				data_agenda = $(`.js-form-${id_agenda} .js-input-data`).val();
				tempo = $(`.js-form-${id_agenda} .js-select-tempo`).val();
				id_profissional = $(`.js-form-${id_agenda} .js-select-profissional`).val();
				id_cadeira = $(`.js-form-${id_agenda} .js-select-cadeira`).val();

				data = `ajax=horarioDisponivel&data=${data_agenda}&tempo=${tempo}&id_profissional=${id_profissional}&id_cadeira=${id_cadeira}&id_agenda=${id_agenda}`;

				$.ajax({
					type:"POST",
					data:data,
					success:function(rtn) {

					}
				})

			}

			$(function(){

				$('#kanban').on('change','.js-select-tempo,.js-select-cadeira,.js-select-profissional,.js-input-data',function(){
					let id_agenda = $(this).parent().find('.js-input-id').val();
					horarioDisponivel(id_agenda);
				})

				$('#kanban').on('click','.js-btn-confirmarAgendamento',function(){
					let id_agenda = $(this).attr('data-id_agenda');
					let data = `ajax=confirmarAgendamento&id_agenda=${id_agenda}`;

					$.ajax({
						type:"POST",
						data:data,
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
					let data = `ajax=cancelarAgendamento&id_agenda=${id_agenda}&motivo=${motivo}`;
				
					$.ajax({
						type:"POST",
						data:data,
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

		<section class="nav2">
			<a href="javascript:;" class="active tooltip" title="Legenda"><i class="iconify" data-icon="bx:bx-check"></i></a>
			<a href="javascript:;" class="tooltip" title="Legenda"><i class="iconify" data-icon="gridicons-multiple-users"></i></a>
			<a href="javascript:;" class="tooltip" title="Legenda"><i class="iconify" data-icon="bx:bx-dollar-circle"></i></a>
			<a href="javascript:;" class="tooltip" title="Legenda"><i class="iconify" data-icon="bx:bx-filter-alt"></i></a>
			
		</section>

		<section class="grid">

			<div class="kanban" id="kanban">
				
				<div class="kanban-item" style="background:<?php echo $s->cor;?>;color:var(--cor1);">
					<h1 class="kanban-item__titulo">CONFIRMAR HOJE<?php /* <span class="tooltip" title="Descrição do item..."><i class="iconify" data-icon="ph:question"></i></span>*/?></h1>
					<div class="kanban-card js-kanban-status js-kanban-status-hoje" data-id_status="hoje" style="min-height: 100px;">
					
					</div>
				</div>
				
				<div class="kanban-item" style="background:<?php echo $s->cor;?>;color:var(--cor1);">
					<h1 class="kanban-item__titulo">CONFIRMAR AMANHÃ<?php /* <span class="tooltip" title="Descrição do item..."><i class="iconify" data-icon="ph:question"></i></span>*/?></h1>
					<div class="kanban-card js-kanban-status js-kanban-status-amanha" data-id_status="amanha" style="min-height: 100px;">
						
					</div>
				</div>
				
				<?php /*<div class="kanban-item" style="background:<?php echo $s->cor;?>;color:var(--cor1);">
					<h1 class="kanban-item__titulo">CONFIRMAR DEPOIS DE AMANHÃ<?php /* <span class="tooltip" title="Descrição do item..."><i class="iconify" data-icon="ph:question"></i></span>*?></h1>
					<div class="kanban-card js-kanban-status js-kanban-status-depoisDeAmanha" data-id_status="depoisDeAmanha" style="min-height: 100px;">
						
					</div>
				</div>*/?>

				<div class="kanban-item" style="background:#e1b8a5;color:var(--cor1);">
					<h1 class="kanban-item__titulo">REAGENDAR DESMARCOU/AGENDOU <?php /* <span class="tooltip" title="Descrição do item..."><i class="iconify" data-icon="ph:question"></i></span>*/?></h1>
					<div class="kanban-card js-kanban-status js-kanban-status-reagendar" data-id_status="reagendar" style="min-height: 100px;">
						
					</div>
				</div>

				<div class="kanban-item" style="background:#d49d83;color:var(--cor1);">
					<h1 class="kanban-item__titulo">PACIENTES EM TRATAMENTO SEM HORÁRIO<?php /* <span class="tooltip" title="Descrição do item..."><i class="iconify" data-icon="ph:question"></i></span>*/?></h1>
					<div class="kanban-card js-kanban-status js-kanban-status-semHorario" data-id_status="semHorario" style="min-height: 100px;">
						
					</div>
				</div>
				<div class="kanban-item" style="background:#D38E69;color:var(--cor1);">
					<h1 class="kanban-item__titulo">INTELIGÊNCIA <span class="js-qtd-inteligencia">(0)</span><?php /* <span class="tooltip" title="Descrição do item..."><i class="iconify" data-icon="ph:question"></i></span>*/?></h1>
					<div class="kanban-card js-kanban-status js-kanban-status-inteligencia" data-id_status="inteligencia" style="min-height: 100px;">
						
					</div>
				</div>
				
			</div> 

		</section>

	</section>
			
<?php
	include "includes/footer.php";
?>