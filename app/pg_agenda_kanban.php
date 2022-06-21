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


			$id_profissional=(isset($_POST['id_profissional']) and is_numeric($_POST['id_profissional']))?$_POST['id_profissional']:0;
			$id_cadeira=(isset($_POST['id_cadeira']) and is_numeric($_POST['id_cadeira']))?$_POST['id_cadeira']:0;

			$_profissionais=array();
			$sql->consult($_p."colaboradores","id,nome","");
			while($x=mysqli_fetch_object($sql->mysqry)) {
				$_profissionais[$x->id]=$x;
			}

			if(!empty($data)) {

				$agenda=$agendaIds=array();
				$pacientesIds=$pacientesAtendidosIds=array(-1);
				$where="where agenda_data>='".$data." 00:00:00' and agenda_data<='".$data." 23:59:59' and lixo=0";
				if($id_profissional>0) $where.=" and profissionais like '%,$id_profissional,%'";
				if($id_cadeira>0) $where.=" and id_cadeira = '$id_cadeira'";
				$sql->consult($_p."agenda","*",$where." order by agenda_data asc");

				$registros=array();
				while($x=mysqli_fetch_object($sql->mysqry)) {
					$registros[]=$x;
					$pacientesIds[]=$x->id_paciente;
					$agendaIds[]=$x->id;

					// ATENDIDO
					if($x->id_status==5) {
						$pacientesAtendidosIds[]=$x->id_paciente;
					}
				}

				$pacientesEvolucoes=array();
				$where="where data_evolucao='".$data."' and id_paciente IN (".implode(",",$pacientesAtendidosIds).") and lixo=0";
				
				$sql->consult($_p."pacientes_evolucoes","*",$where);
				if($sql->rows) {
					while($x=mysqli_fetch_object($sql->mysqry)) {
						$pacientesEvolucoes[$x->id_paciente][]=$x;
					}
				}


				$camposParaFichaCompleta=explode(",","nome,sexo,rg,rg_orgaoemissor,rg_uf,cpf,data_nascimento,estado_civil,telefone1,lat,lng,endereco");

				$_pacientes=array();
				$sql->consult($_p."pacientes","*","where id IN (".implode(",",$pacientesIds).")");
				while($x=mysqli_fetch_object($sql->mysqry)) {

					// verifica se a ficha do paciente esta completa
						$fichaCompleta=1;

						foreach($camposParaFichaCompleta as $c) {
							if(empty($x->$c)) {
								$fichaCompleta=0;
								break;
							}
						}

					$_pacientes[$x->id]=(object)array('id'=>$x->id,
														'nome'=>$x->nome,
														'telefone1'=>$x->telefone1,
														'codigo_bi'=>$x->codigo_bi,
														'fichaCompleta'=>$fichaCompleta);
				}

				$_agendamentosConfirmacaoWts=array();
				$_agendamentosLembretes=array();
				if(count($agendaIds)>0) {
					$sql->consult($_p."whatsapp_mensagens","*","where id_agenda IN (".implode(",",$agendaIds).") and id_tipo IN (1,2)");
					while($x=mysqli_fetch_object($sql->mysqry)) {

						if($x->id_tipo==1) {
							$_agendamentosConfirmacaoWts[$x->id_agenda]=1;

							if($x->resposta_sim==1) $_agendamentosConfirmacaoWts[$x->id_agenda]=2;
							else if($x->resposta_nao==1) $_agendamentosConfirmacaoWts[$x->id_agenda]=3;
							else if($x->resposta_naocompreendida>0) $_agendamentosConfirmacaoWts[$x->id_agenda]=4;
							else if($x->enviado==0 and $x->erro==1) $_agendamentosConfirmacaoWts[$x->id_agenda]=6;
							else {
								$dif = strtotime(date('Y-m-d H:i'))-strtotime($x->data_enviado);
								$dif /= 60;
								$dif = ceil($dif);

								if($dif>4) {
									 $_agendamentosConfirmacaoWts[$x->id_agenda]=5;
								}
							}
						} else if($x->id_tipo==2) {
							$_agendamentosLembretes[$x->id_agenda]=1;
						}
					}
				}

				foreach($registros as $x) {
					if(isset($_pacientes[$x->id_paciente])) {
						$paciente=$_pacientes[$x->id_paciente];

						$dataAg=date('d/m',strtotime($x->agenda_data));
						$dia=" ".$diasExtenso[date('w',strtotime($x->agenda_data))];

						$profissionais='';
						if(!empty($x->profissionais)) {
							$profAux=explode(",",$x->profissionais);
							foreach($profAux as $idP) {
								if(!empty($idP) and isset($_profissionais[$idP])) {
									$prof=$_profissionais[$idP];
									$profissionais.=utf8_encode($prof->nome)."<BR>";

								}
							}
						}

						if(!empty($profissionais)) $profissionais=substr($profissionais,0,strlen($profissionais)-4);
 
						$mais24="";

						$dif = round((strtotime($x->agenda_data)-strtotime($x->data))/(60*60));

						$agenda[]=(object) array('id_agenda'=>$x->id,
													'dt'=>$x->data,
													'data'=>$dataAg,//.$dia,
													'hora'=>date('H:i',strtotime($x->agenda_data))." às ".date('H:i',strtotime($x->agenda_data_final)),
													'id_status'=>$x->id_status,
													'id_paciente'=>$paciente->id,
													'statusBI'=>isset($_codigoBI[$paciente->codigo_bi])?$_codigoBI[$paciente->codigo_bi]:'',
													'paciente'=>ucwords(strtolowerWLIB(utf8_encode($_pacientes[$x->id_paciente]->nome))),
													'telefone1'=>mask($_pacientes[$x->id_paciente]->telefone1),
													'evolucao'=>isset($pacientesEvolucoes[$x->id_paciente])?1:0,
													'wts'=>(int)isset($_agendamentosConfirmacaoWts[$x->id])?$_agendamentosConfirmacaoWts[$x->id]:0,
													'mais24'=>(int)($dif>=24?1:0), // se possui mais de 24h que foi feito o agendamento
													'profissionais'=>$profissionais,
													'lembrete'=>isset($_agendamentosLembretes[$x->id])?1:0,
													'fichaCompleta'=>$paciente->fichaCompleta
												);
					}
				}

				$rtn=array('success'=>true,'agenda'=>$agenda);

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

					$wts=0;

					// Se alterou para confirmado
					if($status->id==2 and $status->id!=$agenda->id_status) {
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

								$attr=array('prefixo'=>$_p,'usr'=>$usr);
								$infozap = new Whatsapp($attr);

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

					$rtn=array('success'=>true,
								'wts'=>$wts);

				} else {
					$rtn=array('success'=>false,'error'=>'Status não encontrado');
				}
			} else {	
				$rtn=array('success'=>false,'error'=>'Agendamento não encontrado');
			}

		}

		header("Content-type: application/json");
		echo json_encode($rtn);

		die();
	}
	require_once("lib/conf.php");
	require_once("usuarios/checa.php");
	$_table = $_p."agenda";

	
	include "includes/header.php";
	include "includes/nav.php";


	$_status=array();
	$sql->consult($_p."agenda_status","*","where  lixo=0 order by kanban_ordem asc");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_status[$x->id]=$x;
	}

	$_cadeiras=array();
	$sql->consult($_p."parametros_cadeiras","*","where lixo=0 order by titulo asc");
	while($x=mysqli_fetch_object($sql->mysqry)) $_cadeiras[$x->id]=$x;

	$_profissionais=array();
	$sql->consult($_p."colaboradores","id,nome,check_agendamento,calendario_iniciais,foto,calendario_cor","where tipo_cro<>'' and lixo=0 order by nome asc");
	while($x=mysqli_fetch_object($sql->mysqry)) $_profissionais[$x->id]=$x;

	$_agendaStatus=array('confirmado'=>'CONFIRMADO','agendado'=>'AGENDADO');
	//  right:'dayGridMonth,resourceTimeGridOneDay,resourceTimeGridFiveDay,resourceTimeGridSevenDay'
	$_views=array("dayGridMonth"=>"MÊS",
					"resourceTimeGridOneDay"=>"1 dia",
					"resourceTimeGridFiveDay"=>"5 dias",
					"resourceTimeGridSevenDay"=>"7 dias");

	$data = isset($_GET['data'])?$_GET['data']:date('d/m/Y');

	list($dia,$mes,$ano)=explode("/",$data);

	if(checkdate($mes, $dia, $ano)) {
		$data=$mes."/".$dia."/".$ano;
		$dataWH=$ano."-".$mes."-".$dia;
	} else { 
		$data=date('d/m/Y');
		$dataWH=date('Y-m-d');
	}

	$agenda=$registros=$agendaIds=array();
	$pacientesIds=$pacientesAtendidosIds=array(-1);
	$where="where agenda_data>='".$dataWH." 00:00:00' and agenda_data<='".$dataWH." 23:59:59' and lixo=0";
	if(isset($_GET['id_profissional']) and is_numeric($_GET['id_profissional']) and $_GET['id_profissional']>0) $where.=" and profissionais like '%,".$_GET['id_profissional'].",%'";
	if(isset($_GET['id_cadeira']) and is_numeric($_GET['id_cadeira']) and $_GET['id_cadeira']>0) $where.=" and id_cadeira=".$_GET['id_cadeira'];
	$sql->consult($_p."agenda","*",$where." order by agenda_data asc");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$registros[]=$x;
		$pacientesIds[]=$x->id_paciente;
		$agendaIds[]=$x->id;

		// ATENDIDO
		if($x->id_status==5) {
			$pacientesAtendidosIds[]=$x->id_paciente;
		}
	}

	$_agendamentosConfirmacaoWts=array();
	if(count($agendaIds)>0) {
		$sql->consult($_p."whatsapp_mensagens","*","where id_agenda IN (".implode(",",$agendaIds).") and id_tipo=1");
		while($x=mysqli_fetch_object($sql->mysqry)) {
			$_agendamentosConfirmacaoWts[$x->id_agenda]=1;
			if($x->resposta_sim==1) $_agendamentosConfirmacaoWts[$x->id_agenda]=2;
			else if($x->resposta_nao==1) $_agendamentosConfirmacaoWts[$x->id_agenda]=3;
			else if($x->resposta_naocompreendida>0) $_agendamentosConfirmacaoWts[$x->id_agenda]=4;
		}
	}

	$pacientesEvolucoes=array();
	$where="where data_evolucao='".$dataWH."' and id_paciente IN (".implode(",",$pacientesAtendidosIds).") and lixo=0";
	$sql->consult($_p."pacientes_evolucoes","*",$where);
	if($sql->rows) {
		while($x=mysqli_fetch_object($sql->mysqry)) {
			$pacientesEvolucoes[$x->id_paciente][]=$x;
		}
	}
	$_agendaStatus=array();
	$sql->consult($_p."agenda_status","*","");
	while ($x=mysqli_fetch_object($sql->mysqry)) {
		// code...
		$_agendaStatus[$x->id]=$x;
	}

	$_pacientes=array();
	$sql->consult($_p."pacientes","id,nome,telefone1,codigo_bi","where id IN (".implode(",",$pacientesIds).")");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_pacientes[$x->id]=$x;
	}
	foreach($registros as $x) {
		if(isset($_pacientes[$x->id_paciente])) {
			$paciente=$_pacientes[$x->id_paciente];

			$dataAg=date('d/m',strtotime($x->agenda_data));
			$dia=" ".$diasExtenso[date('w',strtotime($x->agenda_data))];


			$profissionais='';
			if(!empty($x->profissionais)) {
				$profAux=explode(",",$x->profissionais);
				foreach($profAux as $idP) {
					if(!empty($idP) and isset($_profissionais[$idP])) {
						$prof=$_profissionais[$idP];
						$profissionais.=utf8_encode($prof->nome)."<br>";

					}
				}
			}

						if(!empty($profissionais)) $profissionais=substr($profissionais,0,strlen($profissionais)-4);

			$mais24="";

			$dif = round((strtotime($x->agenda_data)-strtotime($x->data))/(60*60));

			$agenda[]=(object) array('id_agenda'=>$x->id,
										'id_paciente'=>$x->id_paciente,
										'statusBI'=>isset($_codigoBI[$paciente->codigo_bi])?$_codigoBI[$paciente->codigo_bi]:'',
										'data'=>$dataAg,
										'hora'=>date('H:i',strtotime($x->agenda_data))." às ".date('H:i',strtotime($x->agenda_data_final)),
										'id_status'=>$x->id_status,
										'paciente'=>ucwords(strtolowerWLIB(utf8_encode($_pacientes[$x->id_paciente]->nome))),
										'telefone1'=>mask($_pacientes[$x->id_paciente]->telefone1),
										'evolucao'=>isset($pacientesEvolucoes[$x->id_paciente])?1:0,
										'mais24'=>(int)($dif>=24?1:0), // se possui mais de 24h que foi feito o agendamento
										'profissionais'=>$profissionais,
										'wts'=>(int)isset($_agendamentosConfirmacaoWts[$x->id])?$_agendamentosConfirmacaoWts[$x->id]:0
									);
		}
	}
?> 
	
	<header class="header">
		<div class="header__content content">

			<div class="header__inner1">
				<section class="header-title">
					<h1>Agenda</h1>
				</section>
				<section class="tab">
					<a href="javascript:;" class="js-aba-calendario">Calendário</a>
					<a href="pg_agenda_kanban.php" class="active">Kanban</a>					
				</section>
			</div>

			<div class="header__inner2">
				<section class="header-date">
					<div class="header-date-buttons">
					
					</div>
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
			/*<section class="filter">
				<div class="filter-group">
					<div class="filter-form form">
						<dl>
							<dd><a href="javascript:;" class="button button_main" data-aside="add"><i class="iconify" data-icon="fluent:add-circle-24-regular"></i> <span>Novo Agendamento</span></a></dd>
						</dl>
					</div>
				</div>
				<div class="filter-group">
					<div class="filter-form form">
						<dl style="width:160px;">
							<dd><select name=""><option value="">Consultório...</option></select></dd>
						</dl>
						<dl style="width:160px;">
							<dd><select name=""><option value="">Profissional...</option></select></dd>
						</dl>						
					</div>					
				</div>
			</section>*/?>
			<script type="text/javascript">
				var data = '<?php echo $dataWH;?>';
				var dataAgenda = '<?php echo date('d/m/Y',strtotime($dataWH));?>';
				var popViewInfos = [];
				let dataAux = new Date("<?php echo $data;?>");
				var id_profissional = <?php echo (isset($_GET['id_profissional']) and is_numeric($_GET['id_profissional']))?$_GET['id_profissional']:0;?>;
				var id_cadeira = <?php echo (isset($_GET['id_cadeira']) and is_numeric($_GET['id_cadeira']))?$_GET['id_cadeira']:0;?>;

				const meses = ["jan.", "fev.", "mar.", "abr.", "mai.", "jun.", "jul.","ago.","set.","out.","nov.","dez."];
				const dias = ["domingo","segunda-feira","terça-feira","quarta-feira","quinta-feira","sexta-feira","sábado"];
				
				let dataFormatada = `${dias[dataAux.getDay()]}, ${dataAux.getDate()} de ${meses[(dataAux.getMonth())]} de ${dataAux.getFullYear()}`;
				
				var agenda = JSON.parse(`<?php echo json_encode($agenda);?>`);

				const agendaAtualizar = () => {

					let dataAjax = `ajax=agenda&data=${data}&id_profissional=${id_profissional}&id_cadeira=${id_cadeira}`;
					$.ajax({
						type:"POST",
						data:dataAjax,
						success:function(rtn) {
							if(rtn.success) {
								agenda=rtn.agenda;
								agendaListar();
							} else if(rtn.error) {

							} else {

							}
						},
						error:function(){

						}
					})
				}

				const agendaListar = () => {

					$(`#kanban a`).remove();

					popViewInfos = [];

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
					    popInfos.id_agenda=id_agenda;
					    popInfos.foto=foto.length>0?foto:'';
					    popInfos.procedimentosLista=procedimentosLista;

						popViewInfos[x.id_agenda] = popInfos;*/

						let evolucao = ``;

						// Atendido
						if(eval(x.id_status)==5) {
							// se nao possui evolucao
							evolucao = `kanban-item_erro`;
							
						}

						let html = ``;
						let wtsIcon = ``;
						let aConfirmar
						if(x.wts !== undefined && x.wts>0) {
							if(x.wts == 1) { // aguardando
								//wtsIcon=` <span class="iconify" data-icon="bi:send" data-inline="true" data-height="16" style="background:var(--cinza5);color:#FFF;padding:7px;border-radius:5px;"></span>`;
								wtsIcon=`<div class="kanban-item-wp"><i class="iconify" data-icon="cib:whatsapp"></i> <span>aguard. resp.</span></div>`;
							} else if(x.wts == 2) { // sim
								//wtsIcon=` <span class="iconify" data-icon="bi:send-check" data-inline="true" data-height="16" style="background:var(--verde);color:#FFF;padding:7px;border-radius:5px;"></span>`;
								wtsIcon=`<div class="kanban-item-wp" style="color:var(--verde)"><i class="iconify" data-icon="cib:whatsapp"></i></div>`;
							} else if(x.wts == 3) { // nao
								//wtsIcon=` <span class="iconify" data-icon="bi:send-x" data-inline="true" data-height="16" style="background:var(--vermelho);color:#FFF;padding:7px;border-radius:5px;"></span>`;
								wtsIcon=`<div class="kanban-item-wp kanban-item-wp_destaque"><i class="iconify" data-icon="cib:whatsapp"></i> <span>desmarcado</span></div>`;
							} else if(x.wts == 4) { // sem entender
								//wtsIcon=` <span class="iconify" data-icon="fluent:person-chat-24-regular" data-inline="true" data-height="16" style="background:var(--cinza4);color:#FFF;padding:7px;border-radius:5px;"></span>`;
								wtsIcon=`<div class="kanban-item-wp"><i class="iconify" data-icon="cib:whatsapp"></i> <span>não compreend.</span></div>`;
							} else if(x.wts == 5) { // mais de 4h sem resposta
								//wtsIcon=` <span class="iconify" data-icon="fluent:person-chat-24-regular" data-inline="true" data-height="16" style="background:var(--cinza4);color:#FFF;padding:7px;border-radius:5px;"></span>`;
								
								wtsIcon=`<div class="kanban-item-wp"><i class="iconify" data-icon="cib:whatsapp"></i> <span>sem resp.</span></div>`;
							} else if(x.wts == 6) { // deu erro ao enviar
								//wtsIcon=` <span class="iconify" data-icon="fluent:person-chat-24-regular" data-inline="true" data-height="16" style="background:var(--cinza4);color:#FFF;padding:7px;border-radius:5px;"></span>`;
								
								wtsIcon=`<div class="kanban-item-wp"><i class="iconify" data-icon="cib:whatsapp"></i> <span>sem conexão</span></div>`;
							} else {
								//wtsIcon=` <span class="iconify" data-icon="bi:send-exclamation" data-inline="true"></span>`;
								wtsIcon=`<div class="kanban-item-wp"><i class="iconify" data-icon="cib:whatsapp"></i> <span>aguard resp.</span></div>`;
							}
						}

						let wtsLembrete = ``;
						if(x.lembrete && x.lembrete==1) {
							wtsLembrete=`<span class="iconify" data-icon="mdi:clock-alert-outline" style="color:var(--verde)"></span>`;
						}
						//wtsIcon+=` ${x.wts}`

						let fichaIncompleta = '';

						// A CONFIRMAR, DESMARCADO E FALTOU
						if(x.id_status!=1 && x.id_status!=4 && x.id_status!=3) {
							if(x.fichaCompleta==0) {
								fichaIncompleta='<p style="color:var(--vermelho)"><i class="iconify" data-icon="fluent:share-screen-person-overlay-20-regular" data-height="20"></i></p>';
							} else {
								fichaIncompleta='<p style="color:var(--verde)"><i class="iconify" data-icon="fluent:share-screen-person-overlay-20-regular" data-height="20"></i></p>';
							}
							
						}
						
						// A CONFIRMAR
						if(eval(x.id_status)==1) {

							if(x.wts==4 || x.wts==5 || x.wts==6 || x.mais24==0) {
								wtsIcon=`<div class="kanban-item-wp"><i class="iconify" data-icon="bxs:phone"></i> <span>Confirmar</span></div>`;
							}
							html = `<a href="javascript:;" draggable="true" data-id="${x.id_agenda}" class="tooltip" title="${x.profissionais}">
										<p>${x.data} • ${x.hora}</p>
										<h1>${x.paciente}</h1>
										<p>${x.telefone1}</p>
										<p>${x.profissionais}</p>
										${wtsIcon}${wtsLembrete}
									</a>`;
						} 
						// CONFIRMADO
						else if(eval(x.id_status)==2) {

							if(x.wts!=2) {
								wtsIcon=`<div class="kanban-item-wp" style="color:var(--verde)"><i class="iconify" data-icon="bxs:phone"></i></div>`;
							}

							html = `<a href="javascript:;" draggable="true" data-id="${x.id_agenda}" class="tooltip" title="${x.profissionais}">
										<p>${x.data} • ${x.hora}</p>
										<h1>${x.paciente}</h1>
										<p>${x.profissionais}</p>
										${wtsIcon}${wtsLembrete}
									</a>`;
						}
						// FALTOU
						else if(eval(x.id_status)==3) {

							if(x.wts!=2) {
								wtsIcon=`<div class="kanban-item-wp" style="color:var(--verde)"><i class="iconify" data-icon="bxs:phone"></i></div>`;
							}
							html = `<a href="javascript:;" draggable="true" data-id="${x.id_agenda}" class="tooltip" title="${x.profissionais}">
										<p>${x.data} • ${x.hora}</p>
										<h1>${x.paciente}</h1>
										<p>${x.profissionais}</p>
										${fichaIncompleta}
									</a>`;

						}
						// DESMARCOU
						else if(eval(x.id_status)==4) {

							html = `<a href="javascript:;" draggable="true" data-id="${x.id_agenda}" class="tooltip" title="${x.profissionais}">
										<p>${x.data} • ${x.hora}</p>
										<h1>${x.paciente}</h1>
										<p>${x.profissionais}</p>
										${fichaIncompleta}
									</a>`;

						}
						// ATENDIDO
						else if(eval(x.id_status)==5) {

							if(x.wts!=2) {
								wtsIcon=`<div class="kanban-item-wp" style="color:var(--verde)"><i class="iconify" data-icon="bxs:phone"></i></div>`;
							}
							html = `<a href="javascript:;" draggable="true" data-id="${x.id_agenda}" class="tooltip" title="${x.profissionais}">
										<p>${x.data} • ${x.hora}</p>
										<h1>${x.paciente}</h1>
										<p>${x.profissionais}</p>
										${fichaIncompleta}
										${wtsIcon}${wtsLembrete}
									</a>`;

						}

						// EM ATENDIMENTO
						else if(eval(x.id_status)==6) {

							if(x.wts!=2) {
								wtsIcon=`<div class="kanban-item-wp" style="color:var(--verde)"><i class="iconify" data-icon="bxs:phone"></i></div>`;
							}
							html = `<a href="javascript:;" draggable="true" data-id="${x.id_agenda}" class="tooltip" title="${x.profissionais}">
										<p>${x.data} • ${x.hora}</p>
										<h1>${x.paciente}</h1>
										<p>${x.profissionais}</p>
										${fichaIncompleta}
										${wtsIcon}${wtsLembrete}
									</a>`;

						}

						// SALA DE ESPERA
						else if(eval(x.id_status)==7) {

							if(x.wts!=2) {
								wtsIcon=`<div class="kanban-item-wp" style="color:var(--verde)"><i class="iconify" data-icon="bxs:phone"></i></div>`;
							}
							html = `<a href="javascript:;" draggable="true" data-id="${x.id_agenda}" class="tooltip" title="${x.profissionais}">
										<p>${x.data} • ${x.hora}</p>
										<h1>${x.paciente}</h1>
										<p>${x.profissionais}</p>
										${fichaIncompleta}
										${wtsIcon}${wtsLembrete}
									</a>`;

						}

						else {
							html = `<a href="javascript:;" draggable="true" data-id="${x.id_agenda}" class="tooltip" title="${x.profissionais}">
										<p>${x.data} • ${x.hora}</p>
										<h1>${x.paciente}</h1>
										${fichaIncompleta}
										${wtsIcon}${wtsLembrete}
									</a>`;

						
						}	

						$(`#kanban .js-kanban-status-${x.id_status}`).append(html);

						//$(`#kanban .js-kanban-status-${x.id_status} .tooltip:last`).tooltipster({theme:"borderless"});
					})

				}	


				const dataProcess = (dtObj) => {
						

					let dataFormatada = `${dias[dtObj.getDay()]}, ${dtObj.getDate()} de ${meses[(dtObj.getMonth())]} de ${dtObj.getFullYear()}`;

					data = `${dtObj.getFullYear()}-${d2(dtObj.getMonth()+1)}-${d2(dtObj.getDate())}`;
					dataAgenda = `${dtObj.getDate()}/${d2(dtObj.getMonth()+1)}/${d2(dtObj.getFullYear())}`;

					agendaAtualizar();

					$('.js-calendario-title').val(dataFormatada);

					let date = dtObj;
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

					$('.js-cal-titulo-diames').html(dtObj.getDate()>=9?dtObj.getDate():`0${dtObj.getDate()}`);
					$('.js-cal-titulo-mes').html(mesString);
					$('.js-cal-titulo-dia').html(diaString);


				}


				$(function(){

					let aux = data.split('-');
					let dtObj = new Date(`${aux[1]}/${aux[2]}/${aux[0]}`);
					dataProcess(dtObj);

					$('.js-aba-calendario').click(function(){
						let aux = data.split('-');
						let dtObj = `${aux[2]}/${aux[1]}/${aux[0]}`;

						document.location.href='pg_agenda.php?initDate='+dtObj;
					})

					$('.js-filter-agenda .js-profissionais').change(function(){
						id_profissional=$(this).val();
						agendaAtualizar();
					});

					$('.js-cadeira').change(function(){
						id_cadeira=$(this).val();
						agendaAtualizar();
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

					agendaListar();

					$('.js-calendario-title').val(dataFormatada);

					
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
									if(rtn.wts && rtn.wts==1) {
										let data = `ajax=whatsappDisparar`;
										$.ajax({
											url:"pg_agenda.php",
											type:"POST",
											data:data
										})
									}

									if(id_status==5) {
										asideProximaConsulta(id_agenda);
									}
								}
							}
						})
			        });


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
			<section class="grid" style="flex:1;">
				<div class="kanban" id="kanban">
					
					<?php
					foreach($_status as $s) {
					?>
					<div class="kanban-item" style="background:<?php echo $s->cor;?>;">
						<header>
							<h1 class="kanban-item__titulo"><?php echo utf8_encode($s->titulo);?></h1>
						</header>
						<article class="kanban-card js-kanban-status js-kanban-status-<?php echo $s->id;?>" data-id_status="<?php echo $s->id;?>" style="min-height: 100px;">
							
						</article>
					</div>

					
					<?php
					}
					
					/*
					<div class="kanban-item" style="background-color:#545559;">
						<header>
							<h1>À CONFIRMAR</h1>
						</header>
						<article>
							<a href="javascript:;" draggable="true">
								<p>08:00 a 10:00</p>
								<h1>Arnaldo Rubio Júnior</h1>
								<p>(62) 99830-0574</p>
							</a>
							<a href="javascript:;" draggable="true">
								<p>08:00 a 10:00</p>
								<h1>Pedro Saddi</h1>
								<p>(62) 99830-0574</p>
							</a>
							<a href="javascript:;" draggable="true">
								<p>08:00 a 10:00</p>
								<h1>Pedro Henrique Saddi de Azevedo</h1>
								<p>(62) 99830-0574</p>
							</a>
						</article>
					</div>
					<div class="kanban-item" style="background-color:#1182EA;">
						<header>
							<h1>CONFIRMADO</h1>
						</header>
					</div>
					<div class="kanban-item" style="background-color:#FC8107;">
						<header>
							<h1>SALA DE ESPERA</h1>
						</header>
					</div>
					<div class="kanban-item" style="background-color:#25E4C2;">
						<header>
							<h1>EM ATENDIMENTO</h1>
						</header>
					</div>
					<div class="kanban-item" style="background-color:#53D328;">
						<header>
							<h1>ATENDIDO</h1>
						</header>
						<article>
							<a href="javascript:;" draggable="true">
								<h1>Pedro Henrique Saddi de Azevedo</h1>
								<span class="button button_sm"><strong>ficha do paciente</strong></span>
							</a>
						</article>
					</div>
					<div class="kanban-item" style="background-color:#FADE26;">
						<header>
							<h1>DESMARCADO</h1>
						</header>
					</div>
					<div class="kanban-item" style="background-color:#FE4B3F;">
						<header>
							<h1>FALTOU</h1>
						</header>
					</div>
					*/
					?>
				</div>
			</section>

		</div>
	</main>

<?php 
	
	$apiConfig=array('paciente'=>1,
						'proximaConsulta'=>1);
	require_once("includes/api/apiAside.php");


	include "includes/footer.php";
?>	