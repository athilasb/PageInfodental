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


	$_pacientes=array('novos'=>array(),
						'novosIds'=>array(0),
						'aguardandoAprovacao'=>array(),
						'retorno'=>array());
	$sql->consult($_p."pacientes","id,nome,telefone1","where codigo_bi=1 and lixo=0");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_pacientes['novos'][]=$x;
		$_pacientes['novosIds'][]=$x->id;
	}

	$sql->consult($_p."agenda","*","where id_paciente in (".implode(",",$_pacientes['novosIds']).")");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$pacientesNovosComAgendamento[$x->id_paciente]=1;
		if($x->id_status==5) $pacientesNovosAtendidos[$x->id_paciente]=1;
	}

	$pacientesComTratamentosIds=array(0);
	$_pacientesTratamentos=array();
	$tratamentosIds=array(0);

	$_tratamentosProcedimentos=array();
	$aguardandoAprovacaoAReceber=0;
	$sql->consult($_p."pacientes_tratamentos","*","where lixo=0 and status='PENDENTE' and lixo=0");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$pacientesComTratamentosIds[]=$x->id_paciente;
		$_pacientesTratamentos[$x->id_paciente][]=$x;
		$tratamentosIds[]=$x->id;

		if(!empty($x->procedimentos)) {
			$proc=json_decode($x->procedimentos);
			foreach($proc as $p) {
				if(!isset($_tratamentosProcedimentos[$x->id_paciente])) $_tratamentosProcedimentos[$x->id_paciente]=0;
				$_tratamentosProcedimentos[$x->id_paciente]+=$p->valorCorrigido;
				$aguardandoAprovacaoAReceber+=$p->valorCorrigido;
			}
		}
	}



	$sql->consult($_p."pacientes","id,nome,telefone1","where id IN (".implode(",",$pacientesComTratamentosIds).") and lixo=0");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_pacientes['aguardandoAprovacao'][]=$x;
	}

	$pacientesAtendidosIds=array(0);
	$sql->consult($_p."agenda","distinct id_paciente","where id_status=5 and id_paciente in (".implode(",",$_pacientes['novosIds']).") and lixo=0");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$pacientesAtendidosIds[]=$x->id_paciente;
	}

	$sql->consult($_p."pacientes","id,nome,telefone1","where id IN (".implode(",",$pacientesAtendidosIds).") and lixo=0");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_pacientes['retorno'][]=$x;
	}



?>
	<section id="cal-popup" class="cal-popup cal-popup_paciente cal-popup_top cal-popup_alt" style="left:703px; top:338px; margin-left:303px;display: none">
			<?php /*<a href="javascript:;" class="cal-popup__fechar js-btn-fechar"><i class="iconify" data-icon="mdi-close"></i></a>*/?>
			<section class="paciente-info">
				<header class="paciente-info-header">
					<section class="paciente-info-header__inner1">
						<div>
							<h1 class="js-nome"></h1>
							<p class="js-idade"></p>
							<p><span style="color:var(--cinza3);" class="js-id_paciente">#44</span> <span style="color:var(--cor1);"></span></p>
						</div>
					</section>
				</header>

				<div class="abasPopover">
					<a href="javascript:;" class="js-aba-agendamento" onclick="$(this).parent().parent().find('a').removeClass('active');$(this).parent().parent().find('.js-grid').hide();$(this).parent().parent().find('.js-grid-agendamento').show();$(this).addClass('active');$('.js-grid-agendamento-agendar').show();">Agendamento</a>
					<a href="javascript:;" onclick="$(this).parent().parent().find('a').removeClass('active');$(this).parent().parent().find('.js-grid').hide();$(this).parent().parent().find('.js-grid-proximaConsulta').show();$(this).addClass('active');">Próxima Consulta</a>
					<a href="javascript:;" onclick="$(this).parent().parent().find('a').removeClass('active');$(this).parent().parent().find('.js-grid').hide();$(this).parent().parent().find('.js-grid-historico').show();$(this).addClass('active');" class="active">Histórico</a>
				</div>

				<input type="hidden" class="js-input-id_paciente" />

				<div class="paciente-info-grid js-grid js-grid-agendamento-agendar" style="font-size: 12px;">
					<dl>
						<dt>Data</dt>
						<dd>
							<input type="text" class="js-input-data data datecalendar" placeholder="escolha a nova data" />
						</dd>
					</dl>
					<dl>
						<dt>Tempo</dt>
						<dd>
							<select class="js-select-tempo">
								<option value="">Tempo...</option>
								<?php
								echo $selectTempo;
								?>
							</select>
						</dd>
					</dl>
					<dl>
						<dt>Profissional</dt>
						<dd>
							<select class="js-select-profissional">
								<option value="">Profissional...</option>
								<?php
								echo $selectProfissional;
								?>
							</select>
						</dd>
					</dl>
					<dl>
						<dt>Cadeira</dt>
						<dd>
							<select class="js-select-cadeira">
								<option value="">Cadeira...</option>
								<?php
								echo $selectCadeira;
								?>
							</select>
						</dd>
					</dl>
					<dl>
						<dt>Horário</dt>
						<dd>
							<select class="js-select-horario">

							</select>
						</dd>
					</dl>
					<button type="button" class="button button__full js-gridbtn-agendar" style="background:var(--amarelo);">Agendar</button>
				</div>

				<div class="paciente-info-grid js-grid js-grid-agendamento-naoQueroAgendar" style="display: none;grid-template-columns:1fr">
					<dl>
						<dd>
							<select class="js-select-historicoStatus">
								<?php
								foreach($_historicoStatus as $s) {
								?>
								<option value="<?php echo $s->id;?>"><?php echo utf8_encode($s->titulo);?></option>
								<?php	
								}
								?>
							</select>
						</dd>
					</dl>
					<textarea name="" rows="4" class="js-textarea-obs" placeholder="Descreva o motivo..."></textarea>
					<button type="button" class="button button__full js-gridbtn-naoQueroAgendar" style="background:;">Salvar</button>
				</div>

				<div class="paciente-info-grid js-grid js-grid-proximaConsulta" style="display:none;">

				</div>

				<div class="paciente-info-grid js-grid js-grid-historico" style="display:none;font-size:12px;color:#666;grid-template-columns:1fr;max-height:300px; overflow-y:auto;">	
				</div>

				<div class="paciente-info-opcoes">
					<a href="javascript:;" class="button button__full js-btn-agendar" data-id_agenda="${x.id_agenda}" style="background-color:var(--verde);">Quero agendar</a>

					<a href="javascript:;" class="button button__full js-btn-naoQueroAgendar" data-id_agenda="${x.id_agenda}" style="background-color:var(--vermelho);">Não quero agendar</a>

					<a href="javascript:;" target="_blank" class="js-hrefPaciente button button__sec"><i class="iconify" data-icon="bx:bxs-user"></i></a>
				</div>
			</section>
		</section>
	<section class="content"> 

	<section class="content">  

		<?php
		require_once("includes/nav2.php");
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
					<div class="kanban-card" style="min-height: 100px;">
						<?php
						foreach($_pacientes['novos'] as $p) {
							if(isset($pacientesNovosComAgendamento[$p->id])) continue;
						?>
							<a href="javascript:;" onclick="$(this).next('.kanban-card-modal').show();" class="kanban-card-dados js-kanban-item">
							<h1><?php echo utf8_encode($p->nome);?> <?php echo $p->id;?></h1>
							<h2><?php echo $p->telefone1;?></h2>
						</a>
						<?php	
						}
						?>
					</div>
				</div>
				
				<div class="kanban-item" style="background:<?php echo $s->cor;?>;color:var(--cor1);">
					<h1 class="kanban-item__titulo">LEAD COM AGENDAMENTO <span class="tooltip" title="Paciente que nunca "><i class="iconify" data-icon="ph:question"></i></span></h1>
					<div class="kanban-card"style="min-height: 100px;">
						<?php
						foreach($_pacientes['novos'] as $p) {
							if(isset($pacientesNovosComAgendamento[$p->id])) {
								if(isset($pacientesNovosAtendidos[$p->id])) continue;
						?>
							<a href="javascript:;" onclick="$(this).next('.kanban-card-modal').show();" class="kanban-card-dados js-kanban-item">
							<h1><?php echo utf8_encode($p->nome);?> <?php echo $p->id;?></h1>
							<h2><?php echo $p->telefone1;?></h2>
						</a>
						<?php	
							}
						}
						?>
					</div>
				</div>
				
				<div class="kanban-item" style="background:<?php echo $s->cor;?>;color:var(--cor1);">
					<h1 class="kanban-item__titulo">LEAD PARA RETORNO <span class="tooltip" title="Paciente foi na consulta e precisa"><i class="iconify" data-icon="ph:question"></i></span></h1>
					<div class="kanban-card" style="min-height: 100px;">
						<?php
						foreach($_pacientes['retorno'] as $p) {
						?>
							<a href="javascript:;" onclick="$(this).next('.kanban-card-modal').show();" class="kanban-card-dados js-kanban-item">
							<h1><?php echo utf8_encode($p->nome);?> <?php echo $p->id;?></h1>
							<h2><?php echo $p->telefone1;?></h2>
						</a>
						<?php	
							
						}
						?>
					</div>
				</div>
				
				<div class="kanban-item" style="background:<?php echo $s->cor;?>;color:var(--cor1);">
					<h1 class="kanban-item__titulo">AGUARDANDO APROVAÇÃO<br />R$<?php echo number_format($aguardandoAprovacaoAReceber,2,",",".");?><span class="tooltip" title="Paciente foi na consulta e precisa"><i class="iconify" data-icon="ph:question"></i></span></h1>
					<div class="kanban-card" style="min-height: 100px;">
						<?php
						foreach($_pacientes['aguardandoAprovacao'] as $p) {
							$valor=isset($_tratamentosProcedimentos[$p->id])?$_tratamentosProcedimentos[$p->id]:0;
							
						?>
							<a href="pg_contatos_pacientes_tratamento.php?id_paciente=<?php echo $p->id;?>" target="_blank" class="kanban-card-dados">
							<h1><?php echo utf8_encode($p->nome);?> <?php echo $p->id;?></h1>
							<h2><?php echo $p->telefone1;?></h2>
							<h2>Tratamentos: <?php echo count($_pacientesTratamentos[$p->id]);?></h2>
							<h2>Valor: <?php echo number_format($valor,2,",",".");?></h2>
						</a>
						<?php	
						}
						?>
					</div>
				</div>
				
				
			</div> 

		</section>

	</section>
			
<?php
	include "includes/footer.php";
?>