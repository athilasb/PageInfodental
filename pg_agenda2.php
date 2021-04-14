<?php
	if(isset($_GET['ajax'])) {
		require_once("lib/conf.php");
		require_once("usuarios/checa.php");
		$sql2=new Mysql();
		$rtn=array();
		if($_GET['ajax']=="agenda") {

			$_cadeiras=array();
			$sql->consult($_p."parametros_cadeiras","*","");
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

			$unidade='';
			if(isset($_GET['id_unidade']) and is_numeric($_GET['id_unidade']) and isset($_optUnidades[$_GET['id_unidade']])) {
				$unidade=$_optUnidades[$_GET['id_unidade']];
			}

			if(is_object($unidade)) {

				$_pacientes=array();
				$sql->consult($_p."pacientes","*","where lixo=0");
				while($x=mysqli_fetch_object($sql->mysqry)) $_pacientes[$x->id]=$x;

				$agendamentos=array();
				$where="where agenda_data>='".$data_inicio." 00:00:00' and agenda_data<='".$data_fim."' and id_unidade=$unidade->id";

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

				$sql->consult($_p."agenda","*",$where);
				if($sql->rows) {
					while($x=mysqli_fetch_object($sql->mysqry)) {

						if(isset($_pacientes[$x->id_paciente])) {
							$dob = new DateTime($_pacientes[$x->id_paciente]->data_nascimento);
							$now = new DateTime();
							$idade= $now->diff($dob)->y;

							$cadeira=isset($_cadeiras[$x->id_cadeira])?utf8_encode($_cadeiras[$x->id_cadeira]->titulo):'-';
							$dtStart=date('Y-m-d\TH:i',strtotime($x->agenda_data));
							$dtEnd=date('Y-m-d\TH:i',strtotime($x->agenda_data." + 1 hour"));
							$hora=date('H:i',strtotime($x->agenda_data));
							$horaFinal=date('H:i',strtotime($x->agenda_data." + $x->agenda_duracao seconds"));

							$ftPaciente="arqs/pacientes/".$_pacientes[$x->id_paciente]->id.".".$_pacientes[$x->id_paciente]->foto;
							if(!file_exists($ftPaciente)) {
								$ftPaciente='';
							} else $ftPaciente.='?'.date('His');

							$nomeIniciais='L';
							$procedimentos='';
							if(!empty($x->procedimentos)) {
								$procedimentosObj=json_decode(utf8_encode(stripslashes($x->procedimentos)),true);
								$procedimentos=count($procedimentosObj);
							}

							$profissionais='';
							if(!empty($x->profissionais)) {
								$profissioaisObj=explode(",",$x->profissionais);
								$profissionaisID=array(-1);
								foreach($profissioaisObj as $v) {
									if(!empty($v) and is_numeric($v)) $profissionaisID[]=$v;
								}

								$sql2->consult($_p."profissionais","*","where id in (".implode(",",$profissionaisID).") and lixo=0");

								if($sql2->rows) {
									$cont=1;
									while($p=mysqli_fetch_object($sql2->mysqry)) {
										$ft="arqs/profissionais/".$p->id.".".$p->foto;
										if(file_exists($ft)) {
											$profissionais.='<figure><span><img src="'.$ft.'" width="30" height="30" /></span></figure>';
										} else {
											$aux=explode(" ",$p->nome);
											$aux[0]=strtoupper($aux[0]);

											if($aux[0] =="DR" or $aux[0]=="DR." or $aux[0]=="DRA" or $aux[0]=="DRA.") {
												$iniciais=strtoupper(substr($aux[1],0,1));
												if(isset($aux[2])) $iniciais.=strtoupper(substr($aux[2],0,1));
											} else {
												$iniciais=strtoupper(substr($aux[0],0,1));
												if(isset($aux[1])) $iniciais.=strtoupper(substr($aux[1],0,1));
											}
											$profissionais.='<figure><span>'. $iniciais.'</span></figure>';
											if($cont==1) {
												$profissionais.='<figure><span>+'.($sql2->rows).'</span></figure>';
												break;
											}
											$cont++;

										}
									}
								}
							}

							$aux=explode(" ",trim(utf8_encode($_pacientes[$x->id_paciente]->nome)));
							$pacienteNome=$aux[0];
							if(count($aux)>1) $pacienteNome.=" ".$aux[count($aux)-1];

						//	$pacienteNome=$_pacientes[$x->id_paciente]->nome;
							$agendamentos[]=array(
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
													'title'=>$pacienteNome,
													'telefone1'=>utf8_encode($_pacientes[$x->id_paciente]->telefone1),
													'instagram'=>utf8_encode($_pacientes[$x->id_paciente]->instagram),
													'musica'=>utf8_encode($_pacientes[$x->id_paciente]->musica),
													'situacao'=>utf8_encode($_pacientes[$x->id_paciente]->situacao),
													'idade'=>$idade,
													'profissionais'=>$profissionais,
													'color'=>'#FFF',
													'statusColor'=>'green',
													'pontuacao'=>'1.548',
													'procedimentos'=>$procedimentos,
													'id'=>$x->id,
													'id_unidade'=>$x->id_unidade);
						}
					}
				}

				$rtn=array('success'=>true,'agendamentos'=>$agendamentos);
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

	$_status=array();
	$sql->consult($_p."agenda_status","*","where  lixo=0 order by titulo asc");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_status[$x->id]=$x;
	}

	$_cadeiras=array();
	$sql->consult($_p."parametros_cadeiras","*","where lixo=0 order by titulo asc");
	while($x=mysqli_fetch_object($sql->mysqry)) $_cadeiras[$x->id]=$x;

	$_profissionais=array();
	$sql->consult($_p."profissionais","*","where lixo=0 order by nome asc");
	while($x=mysqli_fetch_object($sql->mysqry)) $_profissionais[$x->id]=$x;

	$_agendaStatus=array('confirmado'=>'CONFIRMADO','agendado'=>'AGENDADO');
	//  right:'dayGridMonth,resourceTimeGridOneDay,resourceTimeGridFiveDay,resourceTimeGridSevenDay'
	$_views=array("dayGridMonth"=>"MÊS",
					"resourceTimeGridOneDay"=>"1 dia",
					"resourceTimeGridFiveDay"=>"5 dias",
					"resourceTimeGridSevenDay"=>"7 dias");

?>
<script>
	var calendar = '';
	var id_unidade=<?php echo $usrUnidade->id;?>;
	
	const verificaAgendamento = () => {
		let profissionais = $('.js-form-agendamento select.js-profissionais').val();
		let id_cadeira = $('.js-form-agendamento select[name=id_cadeira]').val();
		let id_paciente = $('.js-form-agendamento select[name=id_paciente]').val();
		let agenda_data = $('.js-form-agendamento input[name=agenda_data]').val();
		let agenda_hora = $('.js-form-agendamento input[name=agenda_hora]').val();

		let data = `ajax=agendamentoVerificarDisponibilidade&id_unidade=${id_unidade}&profissionais=${profissionais}&id_cadeira=${id_cadeira}&agenda_data=${agenda_data}&agenda_hora=${agenda_hora}&id_paciente=${id_paciente}`;
		

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
		if(m=="JANEIRO") return "01";
		else if(m=="FEVEREIRO") return "02";
		else if(m=="MARÇO") return "03";
		else if(m=="ABRIL") return "04";
		else if(m=="MAIO") return "05";
		else if(m=="JUNHO") return "06";
		else if(m=="JULHO") return "07";
		else if(m=="AGOSTO") return "08";
		else if(m=="SETEMBRO") return "09";
		else if(m=="OUTUBRO") return "10";
		else if(m=="NOVEMBRO") return "11";
		else if(m=="DEZEMBRO") return "12";
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

				let data = `${aux[2]}-${unMes(aux[1])}-${aux[0]}`;
				calendar.gotoDate(data);


			}
		});
		$('select.js-view').change(function(){
			let view = $(this).val();
			calendar.changeView(view);
		});

		$('a.js-right').click(function(){
			calendar.next();
		});
		$('a.js-left').click(function(){
			calendar.prev();
		});
		$('a.js-today').click(function(){
			calendar.today();
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
		})
	});
</script>

<section class="content">

	<header class="caminho">
		<h1 class="caminho__titulo">Agenda</h1>
		<!-- <a href="box/boxAgendamento.php?id_unidade=1" data-fancybox data-type="ajax" class="caminho__tutorial button button__sec"><i class="iconify" data-icon="bx-bx-plus-circle"></i> Novo Agendamento</a> -->
	</header>
	<section class="content-grid">

		<section class="content__item">
			<?php
			$_table=$_p."produtos_fotos";
			$_page=basename($_SERVER['PHP_SELF']);
			?>
			<section class="filtros filtros_alt">
				<form method="get" class="formulario-validacao js-filtro filtros-form form">
					<input type="hidden" name="csv" value="0" />
					<div class="colunas6">
						<dl>
							<dd>
								<a href="javascript:;" class="button js-today">HOJE</a>
								<a href="javascript:;" class="button js-left"><span class="iconify" data-icon="bx:bx-left-arrow-circle" data-inline="false" data-width="20" data-height="18"></span></a>
								<a href="javascript:;" class="button js-right"><span class="iconify" data-icon="bx:bx-right-arrow-circle" data-inline="false" data-width="20" data-height="18"></span></a>
							</dd>
						</dl>
						<dl>
							<dd>
								<input type="text" class="js-calendario" value="<?php echo date('d')." ".mes(date('m'))." ".date('Y');?>" readonly="" />
							</dd>
						</dl>
						<dl>
							<dd>
								<select class="js-view" class="chosenWithoutFind">
									<?php
									foreach($_views as $k=>$v) {
										echo '<option value="'.$k.'"'.($k=="resourceTimeGridOneDay"?' selected':'').'>'.$v.'</option>';
									}
									?>
								</select>
							</dd>
						</dl>
						<dl>
							<dd>
								<select name="id_cadeira" class="js-cadeira chosen">
									<option value=""></option>
									<?php
									$_cadeirasJSON=array();
									foreach($_cadeiras as $v) {
										if(!(isset($values['id_cadeira']) and isset($_cadeiras[$values['id_cadeira']]) and $values['id_cadeira']!=$v->id)) {
											$_cadeirasJSON[]=array('id'=>$v->id,'title'=>utf8_encode($v->titulo));
										}
										echo '<option value="'.$v->id.'"'.((isset($values['id_cadeira']) and $values['id_cadeira']==$v->id)?' selected':'').'>'.utf8_encode($v->titulo).'</option>';
									}
									?>
								</select>
							</dd>
						</dl>
						<dl>
							<dd>
								<select name="id_profissional" class="js-profissionais chosen">
									<option value=""></option>
									<?php
									foreach($_profissionais as $v) {
										echo '<option value="'.$v->id.'"'.((isset($values['id_profissional']) and $values['id_profissional']==$v->id)?' selected':'').'>'.utf8_encode($v->nome).'</option>';
									}
									?>
								</select>
							</dd>
						</dl>
						<dl>
							<dd>
								<select name="id_status" class="js-status chosenWithoutFind">
									<option value=""></option>
									<?php
									foreach($_status as $v) {
										echo '<option value="'.$v->id.'"'.((isset($values['id_status']) and $values['id_status']==$v->id)?' selected':'').'>'.utf8_encode($v->titulo).'</option>';
									}
									?>
								</select>
							</dd>
						</dl>
					</div>
				</form>
				<div class="filtros-acoes">
					<a href="box/boxAgendamento.php?id_unidade=1" data-fancybox data-type="ajax" data-padding="0" class="filtros-acoes__button tooltip" title="adicionar"><i class="iconify" data-icon="ic-baseline-add"></i></a>					
				</div>
			</section>
			<?php
			$filtro='';

			if(isset($values['id_status']) and isset($_status[$values['id_status']])) $filtro.="&id_status=".$values['id_status'];
			if(isset($values['id_profissional']) and isset($_profissionais[$values['id_profissional']])) $filtro.="&id_profissional=".$values['id_profissional'];
			if(isset($values['id_cadeira']) and isset($_cadeiras[$values['id_cadeira']])) $filtro.="&id_cadeira=".$values['id_cadeira'];
			if(isset($values['busca']) and !empty($values['busca'])) $filtro.="&busca=".$values['busca'];

			//echo $filtro;
			?>
			<div class="box-registros">
				<link href='https://cdn.jsdelivr.net/npm/fullcalendar-scheduler@5.3.0/main.min.css' rel='stylesheet' />
  				<script src='https://cdn.jsdelivr.net/npm/fullcalendar-scheduler@5.3.0/main.min.js'></script>
				<style type="text/css">
					.fc-scroller, fc.day.grid.containet {overflow:visible !important;}
				</style>
				<script>
					var calendar = '';
					$(function(){
					  var calendarEl = document.getElementById('calendar');

					  calendar = new FullCalendar.Calendar(calendarEl, {
					  	schedulerLicenseKey: 'GPL-My-Project-Is-Open-Source',
						locale: 'pt-br',
					    headerToolbar: {
					      left: '',
					      center:'title',
					      right:''
					    },
						allDaySlot:false,
						firstDay:1,
						initialView:'resourceTimeGridOneDay',
					    views: {
					      dayGridMonth:{
					      	buttonText:'MÊS',
					      },
					      resourceTimeGridOneDay: {
					        type: 'resourceTimeGrid',
					        duration: { days: 1 },
					        buttonText: '1 DIA',
					      },
					      resourceTimeGridFiveDay: {
					        dayHeaderFormat: {  day: '2-digit', weekday: 'short', omitCommas: true  },
					        type: 'timeGridWeek',
					        duration: { days: 5 },
					        buttonText: '5 DIAS',
					      },
					      resourceTimeGridSevenDay: {
					        type: 'timeGridWeek',
					        duration: { days: 7 },
					        buttonText: '7 DIAS'
					      }
					    },
					    resources: <?php echo json_encode($_cadeirasJSON);?>,
						dateClick: function(info) {
							$('.cal-popup').hide();
							$.fancybox.open({
								src  : `box/boxAgendamento.php?id_unidade=${id_unidade}&data_agenda=${info.dateStr}`,
								type : 'ajax'
							});

						},
						resourcesSet:function(arg) {
							setTimeout(function(){$('.fc-scrollgrid-sync-inner ').css('height','30px');},10);
						},
						resourcesChange:function(arg) {
							setTimeout(function(){$('.fc-scrollgrid-sync-inner ').css('height','30px');},10);
						},
						datesSet:function(dateInfo) {
							
						},
					    events: function(info, successCallback, failure) {
							$.getJSON(`<?php echo $_page;?>?ajax=agenda&id_unidade=<?php echo $usrUnidade->id;?>&start=${info.start.valueOf()}&end=${info.end.valueOf()}&<?php echo $filtro;?>&id_status=${filtroStatus}&id_cadeira=${filtroCadeira}&id_profissional=${filtroProfissional}`,
										function (data) {
											if(data.success) {
											 	successCallback(data.agendamentos)
											}
										});
						},

						eventContent: function (arg) {  
							var view = calendar.view.type;
							let nome = arg.event.title;
							let idade = arg.event.extendedProps.idade;
							let foto = arg.event.extendedProps.foto;
							let img = (arg.event.extendedProps.imageurl);

							let inicio = arg.event.extendedProps.hora;
							let fim = arg.event.extendedProps.horaFinal;
							let hora = `${inicio} &ndash; ${fim}`;
							let duracao = arg.event.extendedProps.duracao;
							let cadeira = arg.event.extendedProps.cadeira;
							let id_paciente = arg.event.extendedProps.id_paciente;
							let nomeIniciais = arg.event.extendedProps.nomeIniciais;

							let situacao = arg.event.extendedProps.situacao;
							let indicacao = arg.event.extendedProps.indicacao;
							let pontuacao = arg.event.extendedProps.pontuacao;

							let instagram = arg.event.extendedProps.instagram;
							let telefone1 = arg.event.extendedProps.telefone1;
							let musica = arg.event.extendedProps.musica;
							let statusColor = arg.event.extendedProps.statusColor;
							let procedimentos = arg.event.extendedProps.procedimentos;
							let profissionais = arg.event.extendedProps.profissionais;
							let id_agenda = arg.event.id;
							let id_unidade = arg.event.extendedProps.id_unidade;
							let infos = ``;
   							
   							if(procedimentos.length!=0) procedimentos = `<span>${procedimentos} proced.</span>`; 
							
							if(profissionais.length!=0) profissionais = `<div class="cal-item__fotos">${profissionais}</div>`; 
							

						    if(instagram.length>0) infos+=`<p class="paciente-info-grid__item"><i class="iconify" data-icon="mdi-instagram"></i> ${instagram}</p>`;
						    if(telefone1.length>0) infos+=`<p class="paciente-info-grid__item"><i class="iconify" data-icon="mdi-phone"></i> ${telefone1}</p>`;
						    if(musica.length>0) infos+=`<p class="paciente-info-grid__item"><i class="iconify" data-icon="mdi-music"></i> ${musica}</p>`;
						    if(indicacao.length>0) infos+=`<p class="paciente-info-grid__item"><i class="iconify" data-icon="mdi-hand-pointing-right"></i> ${indicacao}</p>`;
						    if(pontuacao.length>0) {

						    	infos+=`<p class="paciente-info-grid__item"><i class="iconify" data-icon="mdi-star"></i> ${pontuacao} <span class="iconify" data-icon="fe:link-external" data-inline="false"></span></p>`;
						    }

						    if(foto.length>0) foto=`<img src="${foto}" alt="" width="84" height="84" class="paciente-info-header__foto" />`;
							

							cardView=`<section class="cal-popup cal-popup_paciente" style="display:none;">
											<a href="javascript:$('.cal-popup').hide();" style="float:right"><i class="iconify" data-icon="fe-close"></i></a>
											<section class="paciente-info">
												<header class="paciente-info-header">
													${foto}
													<section class="paciente-info-header__inner1">
														<h1>${nome}</h1>
														<p>${idade} anos</p>
														<p><span style="color:var(--cinza3);">#${id_paciente}</span> <span style="color:var(--cor1);">${situacao}</span></p>
													</section>
												</header>
												<section class="paciente-info-grid">
													${infos}
												</section>
												<div class="paciente-info-opcoes">
													<select>
														<option value="">opcao 1</option>
														<option value="">opcao 2</option>
														<option value="">opcao 3</option>
													</select>
													<a href="box/boxAgendamento.php?id_unidade=${id_unidade}&id_agenda=${id_agenda}" data-fancybox data-type="ajax" data-padding="0" class="button" onclick="$('.cal-popup').hide();">Editar</a>
													<a href="javascript:;" class="button button__sec"><i class="iconify" data-icon="bx-bxs-trash"></i></a>
													<a href="javascript:;" class="button button__sec"><i class="iconify" data-icon="codicon:star-full"></i></a>
													<a href="pg_contatos_pacientes.php?form=1&edita=${id_paciente}" target="_blank" class="button button__sec"><i class="iconify" data-icon="bx:bxs-user"></i></a>
												</div>
											</section>
							    		</section>`;

						    if(view=="dayGridMonth") {
						    	eventHTML=`<section class="cal-item">
												${cardView}
												<div class="cal-item-dados" onclick="$('.cal-popup').hide();$(this).prev('.cal-popup').toggle();">
													<div class="cal-item-dados__inner1">
														<p class="cal-item__hora">${hora}</p>
														<h1 class="cal-item__titulo">${nome}</h1>
														<div class="cal-item__info">
															<span><i class="iconify" data-icon="vaadin:dental-chair"></i> 02</span>
															${procedimentos}
														</div>
													</div>
													<div class="cal-item-dados__inner2">
														${profissionais}
													</div>
												</div>
											</section>`
						    } else {
						    	eventHTML=`<section class="cal-item">
												${cardView}
												<div class="cal-item-dados" onclick="$('.cal-popup').hide();$(this).prev('.cal-popup').toggle();">
													<div class="cal-item-dados__inner1">
														<p class="cal-item__hora">${hora}</p>
														<h1 class="cal-item__titulo">${nome}</h1>
														<div class="cal-item__info">
															<span><i class="iconify" data-icon="vaadin:dental-chair"></i> 02</span>
															${procedimentos}
														</div>
													</div>
													<div class="cal-item-dados__inner2">
														${profissionais}
													</div>
												</div>
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
								html = `${dia(dt.getDay())}<br /><br /><span style="background:var(--cor1);padding:15px;border-radius:30px;color:#FFF">${dt.getDate()}</span>`;
								setTimeout(function(){$('.fc-scrollgrid-sync-inner ').css('height','90px');},10);
								$('.fc-scrollgrid-sync-inner ').css('height','90px');
								return { html: html }
							}
						}
					  });
					  calendar.render();
					})
					$(function(){

						

						$(document).mouseup(function(e)  {
						    var container = $("#calendar");

						    // if the target of the click isn't the container nor a descendant of the container
						    if (!container.is(e.target) && container.has(e.target).length === 0) 
						    {
						       $('.cal-popup').hide();
						    }
						});
					})
				</script>

				<div id='calendar'></div>
				<style type="text/css">
					.fc-scrollgrid-sync-inner { height:90px; }
					.fc-scrollgrid  { border:none !important; }
					.fc-scrollgrid-liquid{ border:none !important; }
				</style>
			</div>

		</section>
	</section>
	
</section>

<?php
	include "includes/footer.php";
?>