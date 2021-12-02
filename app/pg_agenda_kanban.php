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

	<section class="content">

		<?php
		require_once("includes/asideAgenda.php");
		?>

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
				    popInfos.id_unidade=id_unidade;
				    popInfos.id_agenda=id_agenda;
				    popInfos.foto=foto.length>0?foto:'';
				    popInfos.procedimentosLista=procedimentosLista;

					popViewInfos[x.id_agenda] = popInfos;*/

					let evolucao = ``;

					// Atendido
					if(eval(x.id_status)==5) {
						// se nao possui evolucao
						if(x.evolucao==0) {
							evolucao = `kanban-item_erro`;
						}
					}

					let html = ``;
					
					if(eval(x.id_status)==5) {
						//console.log(x);
						html = `<div href="javascript:;" onclick="$(this).next('.kanban-card-modal').show();" class="kanban-card-dados js-kanban-item ${evolucao}" data-id="${x.id_agenda}">
										
										<h1>${x.paciente}</h1>
										<h2>${x.statusBI}</h2>
										<a href="pg_contatos_pacientes_resumo.php?id_paciente=${x.id_paciente}" target="_blank" class="js-hrefPaciente button button__sec"><i class="iconify" data-icon="bx:bxs-user"></i></a>
									</div>`;

					} else {
						html = `<a href="javascript:;" onclick="$(this).next('.kanban-card-modal').show();" class="kanban-card-dados js-kanban-item ${evolucao}" data-id="${x.id_agenda}">
										<p class="kanban-card-dados__data">
											<i class="iconify" data-icon="ph:calendar-blank"></i>
											${x.data} &bull; ${x.hora}
										</p>
										<h1>${x.paciente}</h1>
										<h2>${x.telefone1}</h2>
									</a>`;
					}

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

		<section class="grid">
			<div class="kanban" id="kanban">
				<?php
				foreach($_status as $s) {
				?>
				<div class="kanban-item" style="background:<?php echo $s->cor;?>;color:var(--cor1);">
					<h1 class="kanban-item__titulo"><?php echo utf8_encode($s->titulo);?><?php /* <span class="tooltip" title="Descrição do item..."><i class="iconify" data-icon="ph:question"></i></span>*/?></h1>
					<div class="kanban-card js-kanban-status js-kanban-status-<?php echo $s->id;?>" data-id_status="<?php echo $s->id;?>" style="min-height: 100px;">
						<?php 
						/*<a href="javascript:;" onclick="$(this).next('.kanban-card-modal').show();" class="kanban-card-dados js-kanban-item">
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
						</div>*/
						?>
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