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

				$agenda=array();
				$pacientesIds=$pacientesAtendidosIds=array(-1);
				$sql->consult($_p."agenda","*","where agenda_data>='".$data." 00:00:00' and agenda_data<='".$data." 23:59:59' and lixo=0 order by agenda_data asc");
				$registros=array();
				while($x=mysqli_fetch_object($sql->mysqry)) {
					$registros[]=$x;
					$pacientesIds[]=$x->id_paciente;

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

				$_pacientes=array();
				$sql->consult($_p."pacientes","id,nome,telefone1","where id IN (".implode(",",$pacientesIds).")");
				while($x=mysqli_fetch_object($sql->mysqry)) {
					$_pacientes[$x->id]=$x;
				}
				foreach($registros as $x) {
					if(isset($_pacientes[$x->id_paciente])) {

						$dataAg=date('d/m',strtotime($x->agenda_data));
						$dia=" ".$diasExtenso[date('w',strtotime($x->agenda_data))];

						$agenda[]=(object) array('id_agenda'=>$x->id,
													'data'=>$dataAg.$dia,
													'hora'=>date('H:i',strtotime($x->agenda_data)),
													'id_status'=>$x->id_status,
													'paciente'=>ucwords(strtolowerWLIB(utf8_encode($_pacientes[$x->id_paciente]->nome))),
													'telefone1'=>mask($_pacientes[$x->id_paciente]->telefone1),
													'evolucao'=>isset($pacientesEvolucoes[$x->id_paciente])?1:0
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

					$rtn=array('success'=>true);

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
		$data=date('d/m/Y');
		$dataWH=date('Y-m-d');
	}



	$agenda=array();
	$pacientesIds=$pacientesAtendidosIds=array(-1);
	$sql->consult($_p."agenda","*","where agenda_data>='".$dataWH." 00:00:00' and agenda_data<='".$dataWH." 23:59:59' and lixo=0 order by agenda_data asc");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$registros[]=$x;
		$pacientesIds[]=$x->id_paciente;

		// ATENDIDO
		if($x->id_status==5) {
			$pacientesAtendidosIds[]=$x->id_paciente;
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

	$_usuarios=array();
	$sql->consult($_p."colaboradores","id,nome,calendario_iniciais,foto,calendario_cor","where tipo_cro<>'' and lixo=0 order by nome asc");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_usuarios[$x->id]=$x;
	}

	$_status=array();
	$sql->consult($_p."agenda_status","id,titulo,cor","");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_status[$x->id]=$x;
	}

	$_pacientes=array();
	$sql->consult($_p."pacientes","*","where id IN (".implode(",",$pacientesIds).")");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_pacientes[$x->id]=$x;
	}
	foreach($registros as $x) {
		if(isset($_pacientes[$x->id_paciente])) {

			$dataAg=date('d/m',strtotime($x->agenda_data));
			$dia=" ".$diasExtenso[date('w',strtotime($x->agenda_data))];

			if($_pacientes[$x->id_paciente]->data_nascimento!="0000-00-00") {
				$dob = new DateTime($_pacientes[$x->id_paciente]->data_nascimento);
				$now = new DateTime();
				$idade = $now->diff($dob)->y;
			} else {
				$idade = "";
			}

			$ftPaciente="arqs/pacientes/".$_pacientes[$x->id_paciente]->id.".".$_pacientes[$x->id_paciente]->foto;
			if(!file_exists($ftPaciente)) {
				$ftPaciente='';
			} else $ftPaciente.='?'.date('His');

			$procedimentos=array();
			if(!empty($x->procedimentos)) {
				$procedimentosObj=json_decode($x->procedimentos);
				
				if(is_array($procedimentosObj)) {
					foreach($procedimentosObj as $p) {
						$procedimentos[]=utf8_encode($p->procedimento);
					}
				}
			}

			$agendadoPor="";
			if(isset($_usuarios[$x->id_usuario])) {
				list($pNome,)=explode(" ",utf8_encode($_usuarios[$x->id_usuario]->nome));
				$agendadoPor=$pNome;
			}	

			$dias=round((strtotime(date('Y-m-d H:i:s'))-strtotime($x->data_atualizacao=="0000-00-00 00:00:00"?$x->data:$x->data_atualizacao))/(60 * 60 * 24));

			if($dias==0) $agendadoHa="Agendado&nbsp;<strong>HOJE</strong>";
			else if($dias==1) $agendadoHa="Agendado&nbsp;<strong>ONTEM</strong>";
			else $agendadoHa="agendou há&nbsp;<strong>$dias</strong>&nbsp;dias";

			$agenda[]=(object) array('id_agenda'=>$x->id,
										'data'=>$dataAg,
										'hora'=>date('H:i',strtotime($x->agenda_data))." às ".date('H:i',strtotime($x->agenda_data)),
										'id_status'=>$x->id_status,
										'foto'=>$ftPaciente,
										'idade'=>$idade,
										'id_paciente'=>$x->id_paciente,
										'paciente'=>ucwords(strtolowerWLIB(utf8_encode($_pacientes[$x->id_paciente]->nome))),
										'telefone1'=>mask($_pacientes[$x->id_paciente]->telefone1),
										'instagram'=>utf8_encode($_pacientes[$x->id_paciente]->instagram),
										'musica'=>utf8_encode($_pacientes[$x->id_paciente]->musica),
										'evolucao'=>isset($pacientesEvolucoes[$x->id_paciente])?1:0,
										'situacao'=>utf8_encode($_pacientes[$x->id_paciente]->situacao),
										'indicacao'=>'',
										'procedimentos'=>$procedimentos,
										'agendadoHa'=>$agendadoHa,
										'agendadoPor'=>$agendadoPor,
										'obs'=>empty($x->obs)?"-":utf8_encode($x->obs),
										'id_unidade'=>$x->id_unidade
									);
		}
	}
?>

	<section class="content">

		<?php
		require_once("includes/asideAgenda.php");
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
		</script>

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
				$('#cal-popup .js-nome').html(popViewInfos[id_agenda].nome);
				$('#cal-popup .js-idade').html(popViewInfos[id_agenda].idade.length>0?`${popViewInfos[id_agenda].idade} anos`:``);
				$('#cal-popup .js-id_paciente').html(`#${popViewInfos[id_agenda].id_paciente}`);
				$('#cal-popup .js-grid-info').html(popViewInfos[id_agenda].infos);
				$('#cal-popup .js-grid-procedimentos').html(popViewInfos[id_agenda].procedimentosLista);
				$('#cal-popup .js-grid-obs').html(popViewInfos[id_agenda].obs);
				$('#cal-popup .js-id_status').val(popViewInfos[id_agenda].id_status);
				$('#cal-popup .js-hrefAgenda').attr('href',`box/boxAgendamento.php?id_unidade=${popViewInfos[id_agenda].id_unidade}&id_agenda=${popViewInfos[id_agenda].id_agenda}`);
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

		<section id="cal-popup" class="cal-popup cal-popup_paciente cal-popup_top cal-popup_alt" style="left:703px; top:338px; margin-left:303px;display: none">
			<a href="javascript:;" onclick="$('.cal-popup').hide();" class="cal-popup__fechar js-btn-fechar"><i class="iconify" data-icon="mdi-close"></i></a>
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
		<script type="text/javascript">

			var data = '<?php echo $dataWH;?>';
			var popViewInfos = [];
			let dataAux = new Date("<?php echo $data;?>");

			const meses = ["jan.", "fev.", "mar.", "abr.", "mai.", "jun.", "jul.","ago.","set.","out.","nov.","dez."];
			const dias = ["domingo","segunda-feira","terça-feira","quarta-feira","quinta-feira","sexta-feira","sábado"];
			
			let dataFormatada = `${dias[dataAux.getDay()]}, ${dataAux.getDate()} de ${meses[(dataAux.getMonth())]} de ${dataAux.getFullYear()}`;
			
			var agenda = JSON.parse(`<?php echo json_encode($agenda);?>`);

			const agendaAtualizar = () => {

				let dataAjax = `ajax=agenda&data=${data}`;
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

				$(`#kanban .js-kanban-item,#kanban .js-kanban-item-modal`).remove();
				popViewInfos = [];

				agenda.forEach(x=>{

					let procedimentosLista='-';
				    if(x.procedimentos && x.procedimentos.length>0) {
				    	procedimentosLista='';
				    	x.procedimentos.forEach(p=>{
				    		procedimentosLista+=`<p class="paciente-info-grid__item"><i class="iconify" data-icon="fluent:dentist-12-regular"></i> ${p}</p>`; 
				    	})
				    }

					let infos = '';
					if(x.instagram.length>0) infos+=`<p class="paciente-info-grid__item"><i class="iconify" data-icon="mdi-instagram"></i> ${x.instagram}</p>`;
				    else infos+=`<p class="paciente-info-grid__item"><i class="iconify" data-icon="mdi-instagram"></i> -</p>`;

				    if(x.telefone1.length>0) infos+=`<p class="paciente-info-grid__item"><i class="iconify" data-icon="mdi-phone"></i> ${x.telefone1}</p>`;
				    else infos+=`<p class="paciente-info-grid__item"><i class="iconify" data-icon="mdi-phone"></i> -</p>`;

				    if(x.musica.length>0) infos+=`<p class="paciente-info-grid__item"><i class="iconify" data-icon="mdi-music"></i> ${x.musica}</p>`;
				    else infos+=`<p class="paciente-info-grid__item"><i class="iconify" data-icon="mdi-music"></i> -</p>`;

				    if(x.indicacao.length>0) infos+=`<p class="paciente-info-grid__item"><i class="iconify" data-icon="mdi-hand-pointing-right"></i> ${x.indicacao}</p>`;
				    else infos+=`<p class="paciente-info-grid__item"><i class="iconify" data-icon="mdi-hand-pointing-right"></i> -</p>`;

				    if(x.agendadoPor) infos+=`<p class="paciente-info-grid__item" style="grid-column:span 2"><i class="iconify" data-icon="bi:calendar-check"></i> <span><strong>${x.agendadoPor}</strong> ${x.agendadoHa}</span></p>`;
				    else infos+=`<p class="paciente-info-grid__item"><i class="iconify" data-icon="bi:calendar-check"></i> -</p>`;

					popInfos = {};
				    popInfos.nome = x.paciente;
				    popInfos.idade = x.idade;
				    popInfos.id_paciente = x.id_paciente;
				    popInfos.situacao = x.situacao;
				    popInfos.obs = x.obs;
				    popInfos.infos=infos;
				    popInfos.id_status=x.id_status;
				    popInfos.id_unidade=x.id_unidade;
				    popInfos.id_agenda=x.id_agenda;
				    popInfos.foto=x.foto.length>0?x.foto:'';
				    popInfos.procedimentosLista=procedimentosLista;

					popViewInfos[x.id_agenda] = popInfos;

					let evolucao = ``;

					// Atendido
					if(eval(x.id_status)==5) {
						// se nao possui evolucao
						if(x.evolucao==0) {

							evolucao = `<div style="width:100%;background:var(--vermelho);color:#FFF;text-align:center;padding:5px;border-radius:10px;"></div>`;
						}
					}
					
					let html = `<a href="javascript:;" onclick="popView(this,${x.id_agenda});" class="kanban-card-dados js-kanban-item" data-id="${x.id_agenda}">
									${evolucao}
									<p class="kanban-card-dados__data">
										<i class="iconify" data-icon="ph:calendar-blank"></i>
										${x.data} &bull; ${x.hora}
									</p>
									<h1>${x.paciente}</h1>
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

					$(`#kanban .js-kanban-status-${x.id_status}`).append(html);
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


			$(function(){

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
		<section class="grid">
			<div class="kanban" id="kanban">
				<?php
				foreach($_status as $s) {
				?>
				<div class="kanban-item" style="background:<?php echo $s->cor;?>;color:var(--cor1);">
					<h1 class="kanban-item__titulo"><?php echo utf8_encode($s->titulo);?><?php /* <span class="tooltip" title="Descrição do item..."><i class="iconify" data-icon="ph:question"></i></span>*/?></h1>
					<div class="kanban-card js-kanban-status js-kanban-status-<?php echo $s->id;?>" data-id_status="<?php echo $s->id;?>" style="min-height: 100px;">
						<?php /*<a href="javascript:;" onclick="$(this).next('.kanban-card-modal').show();" class="kanban-card-dados js-kanban-item">
							<p class="kanban-card-dados__data">
								<i class="iconify" data-icon="ph:calendar-blank"></i>
								03/06 (quinta-feira) &bull; 09:00
							</p>
							<h1>Cláudia de Paula Gomes</h1>
							<h2>(62) 98450-2332</h2>
						</a>
						<div class="kanban-card-modal" style="display:none;">
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
						</div>*/?>
					</div>
				</div>
				<?php
				}
				?>
			</div>

		</section>

	</section>
			
<?php
	include "includes/footer.php";
?>