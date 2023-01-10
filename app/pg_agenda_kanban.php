<?php
	$diasExtenso=array('(domingo)','(segunda-feira)','(terça-feira)','(quarta-feria)','(quinta-feira)','(sexta-feira)','(sábado)');
	if(isset($_POST['ajax'])) {

		require_once("lib/conf.php");
		require_once("usuarios/checa.php");

		$rtn = array();

		if($_POST['ajax']=="agenda") {

			// busca kanban

			$agenda = new Agenda(array('prefixo'=>$_p));
			$attr=array('data'=>$_POST['data'],
						'id_profissional'=>((isset($_POST['id_profissional']) and is_numeric($_POST['id_profissional']))?$_POST['id_profissional']:0),
						'id_cadeira'=>((isset($_POST['id_profissional']) and is_numeric($_POST['id_profissional']))?$_POST['id_cadeira']:0)
					);
			if($agenda->kanban($attr)) {
				$rtn=array('success'=>true,'agenda'=>$agenda->kanban);
			} else {
				$rtn=array('success'=>false,'error'=>$agenda->erro);
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

					$erro='';


					if($agenda->id_status != $status->id) {


						// se confirmado
						if($agenda->id_status == 2) {

							// alterar para a confirmar 
 							if($status->id==1) $erro='Não é possível alterar status de <b>CONFIRMADO</b> para <b>À CONFIRMAR</b>';

 							// alterar para reserva de horario
 							else if($status->id==8) $erro='Não é possível alterar status de <b>CONFIRMADO</b> para <b>RESERVA DE HORÁRIO</b>';

						}

						// se desmarcado
						else if($agenda->id_status == 4) {
							$erro='Agendamento com status <b>DESMARCADO</b> não podem ter seus status alterado';
						}

						else if(strtotime(date('Y-m-d',strtotime($agenda->agenda_data)))>strtotime(date('Y-m-d'))) {

							// se à confirmar
							if($agenda->id_status==1) {
								if($status->id==2 and $status->id==8 and $status->id!=4) $erro='Agendamento com status <b>À CONFIRMAR</b> e com data futura, o status só pode ser alterado para <b>RESERVA DE HORÁRIO</b>, <b>CONFIRMADO</b> ou <b>DESMARCADO</b>';
							} 

							// se reserva de horario
							else if($agenda->id_status==8) {
								if($status->id==2 and $status->id==1 and $status->id!=4) $erro='Agendamento com status <b>RESERVA DE HORÁRIO</b> e com data futura, o status só pode ser alterado para <b>À CONFIRMAR</b>, <b>CONFIRMADO</b> ou <b>DESMARCADO</b>';
							}

							// se confirmado
							else if($agenda->id_status==2) {
								if($status->id!=4) $erro='Agendamento com status <b>CONFIRMADO</b> e com data futura, o status só pode ser alterado para <b>DESMARCADO</b>';
							}


							$erro='Não é permitido ';
						}
					}

					if(empty($erro)) {

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
						$rtn=array('success'=>false,
									'error'=>$erro);
					}

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
	$sql->consult($_p."colaboradores","id,nome,check_agendamento,calendario_iniciais,foto,calendario_cor,contratacaoAtiva","where lixo=0 order by nome asc");
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


	// busca kanban

	$agenda = new Agenda(array('prefixo'=>$_p));
	$attr=array('data'=>$dataWH,
				'id_profissional'=>((isset($_GET['id_profissional']) and is_numeric($_GET['id_profissional']))?$_GET['id_profissional']:0),
				'id_cadeira'=>((isset($_GET['id_cadeira']) and is_numeric($_GET['id_cadeira']))?$_GET['id_cadeira']:0)
			);

	if($agenda->kanban($attr)) {
		$agenda=$agenda->kanban;
	} else {
		$agenda=array();
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
						let aConfirmar = ``;
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

						let iconsInfo = '<i class="iconify" data-icon="fluent:lightbulb-filament-20-regular" style="color:var(--vermelho);"></i>';
						if((x.pProx && x.pProx==1) || (x.pHist && x.pHist==1) || (x.pPer && x.pPer==1)) {
							iconsInfo=' <i class="iconify" data-icon="fluent:lightbulb-filament-20-regular" style="color:var(--verde);"></i>';
						}

						let iconsInfoProntuario = '<span class="iconify" data-icon="fluent:clipboard-note-20-filled" style="color:var(--vermelho);"></span>';
						if(x.pPron && x.pPron==1) {
							iconsInfoProntuario='<span class="iconify" data-icon="fluent:clipboard-note-20-filled" style="color:var(--verde);"></span>';
						}
						

						let wtsLembrete = ``;
						if(x.lembrete && x.lembrete==1) {
							wtsLembrete=`<i class="iconify" data-icon="mdi:clock-alert-outline" style="color:var(--verde)"></i>`;
						}
						//wtsIcon+=` ${x.wts}`

						let temAgendamentoFuturo = '<span class="iconify" data-icon="gridicons:scheduled" style="color:var(--vermelho)"></span>';
						if(x.futuro==1) {
						 	temAgendamentoFuturo='<span class="iconify" data-icon="gridicons:scheduled" style="color:var(--verde)"></span>';
						}

						let fichaIncompleta = '';

						// A CONFIRMAR, DESMARCADO E FALTOU
						if(x.id_status!=1 && x.id_status!=4 && x.id_status!=3) {
							if(x.fichaCompleta==0) {
								fichaIncompleta='<i class="iconify" data-icon="fluent:share-screen-person-overlay-20-regular" style="color:var(--vermelho)"></i>';
							} else {
								fichaIncompleta='<i class="iconify" data-icon="fluent:share-screen-person-overlay-20-regular" style="color:var(--verde);"></i>';
							}
							
						}
						// A CONFIRMAR ou RESERVA DE HORARIO
						if(eval(x.id_status)==1 || eval(x.id_status)==8) {

							if(x.wts==4 || x.wts==5 || x.wts==6 || x.mais24==0) {
								wtsIcon=`<div class="kanban-item-wp" style="color:var(--vermelho); display:inline;"><i class="iconify" data-icon="bxs:phone"></i> <span>Confirmar</span></div>`;
							}
							if(x.id_status==8) {
								html = `<a href="javascript:;" draggable="true" data-id="${x.id_agenda}" class="tooltip" title="${x.profissionais}" style="opacity:0.5">
										<p>${x.data} • ${x.hora}</p>
										<h1>${x.paciente}</h1>
										<p>${x.telefone1}</p>
										<p>${x.profissionais}</p>
										<div class="kanban-item-icons">
											${wtsIcon}
											${wtsLembrete}
										</div>
									</a>`;

								x.id_status=1;
							} else {
								html = `<a href="javascript:;" draggable="true" data-id="${x.id_agenda}" class="tooltip" title="${x.profissionais}">
										<p>${x.data} • ${x.hora}</p>
										<h1>${x.paciente}</h1>
										<p>${x.telefone1}</p>
										<p>${x.profissionais}</p>
										<div class="kanban-item-icons">
											${wtsIcon}
											${wtsLembrete}
										</div>
									</a>`;

							}
						} 
						// CONFIRMADO
						else if(eval(x.id_status)==2) {

							if(x.wts!=2) {
								wtsIcon=`<div class="kanban-item-wp" style="color:var(--verde); display:inline;"><i class="iconify" data-icon="bxs:phone"></i></div>`;
							}

							html = `<a href="javascript:;" draggable="true" data-id="${x.id_agenda}" class="tooltip" title="${x.profissionais}">
										<p>${x.data} • ${x.hora}</p>
										<h1>${x.paciente}</h1>
										<p>${x.profissionais}</p>
										<div class="kanban-item-icons">
											${fichaIncompleta}
											${wtsIcon}${wtsLembrete}
										</div>
									</a>`;
						}
						// FALTOU
						else if(eval(x.id_status)==3) {

							if(x.wts!=2) {
								wtsIcon=`<div class="kanban-item-wp" style="color:var(--verde); display:inline;"><i class="iconify" data-icon="bxs:phone"></i></div>`;
							}
							html = `<a href="javascript:;" draggable="true" data-id="${x.id_agenda}" class="tooltip" title="${x.profissionais}">
										<p>${x.data} • ${x.hora}</p>
										<h1>${x.paciente}</h1>
										<p>${x.profissionais}</p>
										<div class="kanban-item-icons">
											${fichaIncompleta}
											${temAgendamentoFuturo}
										</div>
									</a>`;

						}
						// DESMARCOU
						else if(eval(x.id_status)==4) {

							html = `<a href="javascript:;" draggable="true" data-id="${x.id_agenda}" class="tooltip" title="${x.profissionais}">
										<p>${x.data} • ${x.hora}</p>
										<h1>${x.paciente}</h1>
										<p>${x.profissionais}</p>
										<div class="kanban-item-icons">
											${fichaIncompleta}
											${temAgendamentoFuturo}
										</div>
									</a>`;

						}
						// ATENDIDO
						else if(eval(x.id_status)==5) {

							if(x.wts!=2) {
								wtsIcon=`<div class="kanban-item-wp" style="color:var(--verde); display:inline;"><i class="iconify" data-icon="bxs:phone"></i></div>`;
							}
							html = `<a href="javascript:;" draggable="true" data-id="${x.id_agenda}" class="tooltip" title="${x.profissionais}">
										<p>${x.id_agenda} - ${x.data} • ${x.hora}</p>
										<h1>${x.paciente}</h1>
										<p>${x.profissionais}</p>
										<div class="kanban-item-icons">
											${fichaIncompleta}
											${iconsInfo}
											${iconsInfoProntuario}
										</div>
									</a>`;

						}

						// EM ATENDIMENTO
						else if(eval(x.id_status)==6) {

							if(x.wts!=2) {
								wtsIcon=`<div class="kanban-item-wp" style="color:var(--verde); display:inline;"><i class="iconify" data-icon="bxs:phone"></i></div>`;
							}
							html = `<a href="javascript:;" draggable="true" data-id="${x.id_agenda}" class="tooltip" title="${x.profissionais}">
										<p>${x.data} • ${x.hora}</p>
										<h1>${x.paciente}</h1>
										<p>${x.profissionais}</p>
										<div class="kanban-item-icons"><p>
											${fichaIncompleta}
											${wtsIcon}${wtsLembrete}
										</div>
									</a>`;

						}

						// SALA DE ESPERA
						else if(eval(x.id_status)==7) {

							if(x.wts!=2) {
								wtsIcon=`<div class="kanban-item-wp" style="color:var(--verde);"><i class="iconify" data-icon="bxs:phone"></i></div>`;
							}
							html = `<a href="javascript:;" draggable="true" data-id="${x.id_agenda}" class="tooltip" title="${x.profissionais}">
										<p>${x.data} • ${x.hora}</p>
										<h1>${x.paciente}</h1>
										<p>${x.profissionais}</p>
										<div class="kanban-item-icons">
											${fichaIncompleta}
											${wtsIcon}${wtsLembrete}											
										</div>
									</a>`;

						}

						else {
							html = `<a href="javascript:;" draggable="true" data-id="${x.id_agenda}" class="tooltip" title="${x.profissionais}">
										<p>${x.data} • ${x.hora}</p>
										<h1>${x.paciente}</h1>
										<div class="kanban-item-icons">
											${fichaIncompleta}
											${wtsIcon}${wtsLembrete}
										</div>
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

					let dataURL = d2(date.getDate())+"/"+d2(date.getMonth()+1)+'/'+date.getFullYear();

					window.history.pushState('', '', `/<?php echo basename($_SERVER['PHP_SELF']);?>?data=${dataURL}`);


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
								} else if(rtn.error) {
									swal({title: "Erro!", text: rtn.error, html:true, type:"error", confirmButtonColor: "#424242"});
								} else {
									swal({title: "Erro!", text: 'Algum erro ocorreu durante a alteração de status!', type:"error", confirmButtonColor: "#424242"});
								}
							}
						}).done(function(){
							agendaAtualizar();
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

						if($s->id==8) continue;
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

	
	$apiConfig=array('procedimentos'=>1);
	require_once("includes/api/apiAsidePaciente.php");


	include "includes/footer.php";
?>	