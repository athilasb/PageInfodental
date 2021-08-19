<?php
	$diasExtenso=array('(domingo)','(segunda-feira)','(terça-feira)','(quarta-feria)','(quinta-feira)','(sexta-feira)','(sábado)');

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


	$leads=array('semAgendamento'=>array());

	# Leads sem Agendamentos

		$pacientesNovosIds=array();
		$where="WHERE data > NOW() - INTERVAL 300 DAY";
		$sql->consult($_p."pacientes","*",$where);
		while($x=mysqli_fetch_object($sql->mysqry)) {
			$pacientesNovosIds[$x->id]=$x->id;
		}

		$sql->consult($_p."agenda","*","where id_paciente IN (".implode(",",$pacientesNovosIds).")");
		if($sql->rows) {
			while($x=mysqli_fetch_object($sql->mysqry)) {
				unset($pacientesNovosIds[$x->id_paciente]);
			}
		}

		$_pacientes=array();
		$sql->consult($_p."pacientes","*","");
		while($x=mysqli_fetch_object($sql->mysqry)) {
			$_pacientes[$x->id]=$x;
		}

		foreach($pacientesNovosIds as $id_paciente) {
			if(isset($_pacientes[$id_paciente])) {
				$paciente=$_pacientes[$id_paciente];

				$leads['semAgendamento'][]=array('id_paciente'=>$paciente->id,
													'nome'=>utf8_encode($paciente->nome),
													'telefone1'=>utf8_encode($paciente->telefone1) );
			}
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
			
			var leads = JSON.parse(`<?php echo json_encode($leads);?>`);

			const leadsListar = () => {

				$(`#kanban .js-kanban-item,#kanban .js-kanban-item-modal`).remove();

				leads.semAgendamento.forEach(x=>{

					let barra=``;
				
					let html = `<div class="kanban-card">
									<a href="javascript:;"  onclick="$('.kanban-card-modal').hide(); $(this).next('.kanban-card-modal').show();" class="kanban-card-dados js-kanban-item" data-id="${x.id_agenda}">
										${barra}
										<h1>${x.nome}</h1>
										<h2>${x.telefone1}</h2>
									</a>
									<div class="kanban-card-modal js-kanban-item-modal" style="display:none;">
										<div class="kanban-card-modal__inner1">
											<a class="kanban-card-modal__fechar" href="javascript:;" onclick="$(this).parent().parent().hide(); $('.js-reagendar, .js-cancelar').hide(); $('.js-opcoes').show();"><i class="iconify" data-icon="ph-x"></i></a>
											<h1>${x.nome}</h1>
											<h2>${x.telefone1}</h2>
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
									</div>
								</div>`;

					$(`#kanban .js-kanban-status-semAgendamento`).append(html);
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

				leadsListar();

				$('.js-calendario-title').val(dataFormatada);

				
				/*var droppable = $(".js-kanban-status").dad({
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
		<section class="grid">
			<div class="kanban" id="kanban">
				
				<div class="kanban-item" style="background:<?php echo $s->cor;?>;color:var(--cor1);">
					<h1 class="kanban-item__titulo">LEAD SEM AGENDAMENTO<span class="tooltip" title="Paciente novo sem agendamento"><i class="iconify" data-icon="ph:question"></i></span></h1>
					<div class="kanban-card js-kanban-status js-kanban-status-semAgendamento" data-id_status="semAgendamento" style="min-height: 100px;">
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
				
				<div class="kanban-item" style="background:<?php echo $s->cor;?>;color:var(--cor1);">
					<h1 class="kanban-item__titulo">LEAD COM AGENDAMENTO <span class="tooltip" title="Paciente que nunca "><i class="iconify" data-icon="ph:question"></i></span></h1>
					<div class="kanban-card js-kanban-status js-kanban-status-comAgendamento" data-id_status="comAgendamento" style="min-height: 100px;">
						
					</div>
				</div>
				
				<div class="kanban-item" style="background:<?php echo $s->cor;?>;color:var(--cor1);">
					<h1 class="kanban-item__titulo">LEAD PARA RETORNO <span class="tooltip" title="Paciente foi na consulta e precisa"><i class="iconify" data-icon="ph:question"></i></span></h1>
					<div class="kanban-card js-kanban-status js-kanban-status-comAgendamento" data-id_status="comAgendamento" style="min-height: 100px;">
						
					</div>
				</div>
				
				<div class="kanban-item" style="background:<?php echo $s->cor;?>;color:var(--cor1);">
					<h1 class="kanban-item__titulo">AGUARDANDO APROVAÇÃO<span class="tooltip" title="Paciente foi na consulta e precisa"><i class="iconify" data-icon="ph:question"></i></span></h1>
					<div class="kanban-card js-kanban-status js-kanban-status-comAgendamento" data-id_status="comAgendamento" style="min-height: 100px;">
						
					</div>
				</div>
				
				
			</div> 

		</section>

	</section>
			
<?php
	include "includes/footer.php";
?>