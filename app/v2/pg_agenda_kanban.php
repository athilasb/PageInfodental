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



			if(!empty($data)) {

				$agenda=array();
				$pacientesIds=$pacientesAtendidosIds=array(-1);
				$where="where agenda_data>='".$data." 00:00:00' and agenda_data<='".$data." 23:59:59' and lixo=0";
				if($id_profissional>0) $where.=" and profissionais like '%,$id_profissional,%'";
				if($id_cadeira>0) $where.=" and id_cadeira = '$id_cadeira'";
				$sql->consult($_p."agenda","*",$where." order by agenda_data asc");

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
				$sql->consult($_p."pacientes","id,nome,telefone1,codigo_bi","where id IN (".implode(",",$pacientesIds).")");
				while($x=mysqli_fetch_object($sql->mysqry)) {
					$_pacientes[$x->id]=$x;
				}
				foreach($registros as $x) {
					if(isset($_pacientes[$x->id_paciente])) {
						$paciente=$_pacientes[$x->id_paciente];

						$dataAg=date('d/m',strtotime($x->agenda_data));
						$dia=" ".$diasExtenso[date('w',strtotime($x->agenda_data))];

						$agenda[]=(object) array('id_agenda'=>$x->id,
													'data'=>$dataAg.$dia,
													'hora'=>date('H:i',strtotime($x->agenda_data)),
													'id_status'=>$x->id_status,
													'id_paciente'=>$paciente->id,
													'statusBI'=>isset($_codigoBI[$paciente->codigo_bi])?$_codigoBI[$paciente->codigo_bi]:'',
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

	$agenda=$registros=array();
	$pacientesIds=$pacientesAtendidosIds=array(-1);
	$where="where agenda_data>='".$dataWH." 00:00:00' and agenda_data<='".$dataWH." 23:59:59' and lixo=0";
	if(isset($_GET['id_profissional']) and is_numeric($_GET['id_profissional']) and $_GET['id_profissional']>0) $where.=" and profissionais like '%,".$_GET['id_profissional'].",%'";
	if(isset($_GET['id_cadeira']) and is_numeric($_GET['id_cadeira']) and $_GET['id_cadeira']>0) $where.=" and id_cadeira=".$_GET['id_cadeira'];
	$sql->consult($_p."agenda","*",$where." order by agenda_data asc");
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

			$agenda[]=(object) array('id_agenda'=>$x->id,
										'id_paciente'=>$x->id_paciente,
										'statusBI'=>isset($_codigoBI[$paciente->codigo_bi])?$_codigoBI[$paciente->codigo_bi]:'',
										'data'=>$dataAg,
										'hora'=>date('H:i',strtotime($x->agenda_data))." às ".date('H:i',strtotime($x->agenda_data)),
										'id_status'=>$x->id_status,
										'paciente'=>ucwords(strtolowerWLIB(utf8_encode($_pacientes[$x->id_paciente]->nome))),
										'telefone1'=>mask($_pacientes[$x->id_paciente]->telefone1),
										'evolucao'=>isset($pacientesEvolucoes[$x->id_paciente])?1:0
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
					<a href="pg_agenda.php">Calendário</a>
					<a href="pg_agenda_kanban.php" class="active">Kanban</a>					
				</section>
			</div>

			<div class="header__inner2">
				<section class="header-date">
					<div class="header-date-buttons">
						<a href="" class="button active">hoje</a>	
						<a href="" class="button"><i class="iconify" data-icon="fluent:arrow-left-24-filled"></i></a>	
						<a href="" class="button"><i class="iconify" data-icon="fluent:arrow-right-24-filled"></i></a>	
					</div>
					<div class="header-date-now">
						<h1>12</h1>
						<h2>dez</h2>
						<h3>terça-feira</h3>
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

					$(`#kanban .js-kanban-item,#kanban .js-kanban-item-modal`).remove();

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
						
						if(eval(x.id_status)==5) {
							//console.log(x);
							html = `<div href="javascript:;" onclick="$(this).next('.kanban-card-modal').show();" class="kanban-card-dados js-kanban-item ${evolucao}" data-id="${x.id_agenda}">
											
											<h1>${x.paciente}</h1>
											<h2>${x.statusBI}</h2>
											<a href="pg_contatos_pacientes_resumo.php?id_paciente=${x.id_paciente}" target="_blank" class="js-hrefPaciente button button__sec"><i class="iconify" data-icon="bx:bxs-user"></i></a>
										</div>`;
							html = `<a href="javascript:;" onclick="$(this).next('.kanban-card-modal').show();" class="js-kanban-item ${evolucao}" data-id="${x.id_agenda}">
											<p class="kanban-card-dados__data">
												<i class="iconify" data-icon="ph:calendar-blank"></i>
												${x.data} &bull; ${x.hora}
											</p>
											<h1>${x.paciente}</h1>
											<p>${x.telefone1}</p>
										</a>`;

						} else {
							html = `<a href="javascript:;" onclick="$(this).next('.kanban-card-modal').show();" class="js-kanban-item ${evolucao}" data-id="${x.id_agenda}">
											<p class="kanban-card-dados__data">
												<i class="iconify" data-icon="ph:calendar-blank"></i>
												${x.data} &bull; ${x.hora}
											</p>
											<h1>${x.paciente}</h1>
											<p>${x.telefone1}</p>
										</a>`;

						
						}	

						$(`#kanban .js-kanban-status-${x.id_status}`).append(html);
					})

				}	


				const dataProcess = (dtObj) => {
						

					let dataFormatada = `${dias[dtObj.getDay()]}, ${dtObj.getDate()} de ${meses[(dtObj.getMonth())]} de ${dtObj.getFullYear()}`;


					data = `${dtObj.getFullYear()}-${d2(dtObj.getMonth()+1)}-${d2(dtObj.getDate())}`;
					dataAgenda = `${dtObj.getDate()}/${d2(dtObj.getMonth()+1)}/${d2(dtObj.getFullYear())}`;

					agendaAtualizar();

					$('.js-calendario-title').val(dataFormatada)
				}


				$(function(){

					$('.js-profissionais').change(function(){
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
	
	$apiConfig=array('paciente'=>1);
	require_once("includes/api/apiAside.php");


	include "includes/footer.php";
?>	