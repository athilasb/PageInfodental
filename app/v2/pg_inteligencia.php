<?php
	include "includes/header.php";
	include "includes/nav.php";

	$_table=$_p."colaboradores";


	$_profissionais=array();
	$sql->consult($_p."colaboradores","id,nome,calendario_iniciais,foto,calendario_cor,check_agendamento","where lixo=0 order by nome asc");
	while($x=mysqli_fetch_object($sql->mysqry)) $_profissionais[$x->id]=$x;

	$_cadeiras=array();
	$sql->consult($_p."parametros_cadeiras","*","where lixo=0  order by titulo asc");
	while($x=mysqli_fetch_object($sql->mysqry)) $_cadeiras[$x->id]=$x;

	$_status=array();
	$sql->consult($_p."agenda_status","*","where lixo=0 order by kanban_ordem asc");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_status[$x->id]=$x;
	}

	$_cadeiras=array();
	$sql->consult($_p."parametros_cadeiras","id,titulo","where lixo=0 order by ordem asc");
	while($x=mysqli_fetch_object($sql->mysqry)) $_cadeiras[$x->id]=$x;

	


	$data = date('Y-m-d');

	if(isset($_GET['data']) and !empty($_GET['data'])) {
		list($dia,$mes,$ano)=explode("/",$_GET['data']);

		if(checkdate($mes, $dia, $ano)) {

			$data = $ano."-".$mes."-".$dia;

		}
	}


	$_cadeiras = array();
	$sql->consult($_p."parametros_cadeiras","*","where lixo=0");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_cadeiras[$x->id]=$x;
	}

	// calcula horas do dia de cada cadeira
	$dataDia = date('w',strtotime($data));

	$_horas = array();
	$sql->consult($_p."parametros_cadeiras_horarios","*","where dia='$dataDia' and lixo=0");
	if($sql->rows) {
		while($x=mysqli_fetch_object($sql->mysqry)) {
			if(!isset($_horas[$x->id_cadeira])) $_horas[$x->id_cadeira]=0;

			$dif = (strtotime($x->fim)-strtotime($x->inicio))/(60);

			$_horas[$x->id_cadeira]+=$dif;

		}
	}


	$_agendaHoras = array();

	$sql->consult($_p."agenda","id,id_cadeira,agenda_duracao","where agenda_data>='".$data." 00:00:00' and agenda_data<='".$data." 23:59:59' and id_status IN (1,2,5) and lixo=0");
	while($x=mysqli_fetch_object($sql->mysqry)) {

			if(!isset($_agendaHoras[$x->id_cadeira])) $_agendaHoras[$x->id_cadeira]=0;
			$_agendaHoras[$x->id_cadeira]+=$x->agenda_duracao;
	}


	
?>

	<header class="header">
		<div class="header__content content">

			<div class="header__inner1">
				<section class="header-title">
					<h1>Tarefas Inteligentes</h1>
				</section>
				<?php
				require_once("includes/menus/menuInteligencia.php");
				?>


			</div>
			<div class="header__inner2">
				<section class="header-date">
					<div class="header-date-buttons">
					
					</div>
					<div class="header-date-now">
						<h1 class="js-cal-titulo-diames"><?php echo date('d',strtotime($data));?></h1>
						<h2 class="js-cal-titulo-mes"><?php echo substr(strtolower(mes(date('m',strtotime($data)))),0,3);?></h2>
						<h3 class="js-cal-titulo-dia"><?php echo strtolower(diaDaSemana(date('w',strtotime($data))));?></h3>
					</div>
				</section>
			</div>
		</div>
	</header>


	<main class="main">
		<div class="main__content content">

			<section class="filter">
				
				<div class="filter-group">
					<div class="filter-title">	
						<p>Valorize o que mais importa, seu tempo! Análise de índices e sugestões guiadas por Inteligência Artificial</p>
					</div>
				</div>
				

				<div class="filter-group">
					<div class="filter-form form">
						<dl>
							<dd>
								<a href="<?php echo $_page."?data=".date('d/m/Y');?>" class="button<?php echo date('Y-m-d')==$data?" active":"";?>">hoje</a>	
								<a href="<?php echo $_page."?data=".date('d/m/Y',strtotime(date('Y-m-d')." + 1 day"));?>" class="button<?php echo date('Y-m-d',strtotime(date('Y-m-d')." + 1 day"))==$data?" active":"";?>">+ 1 dia</a>	
								<a href="<?php echo $_page."?data=".date('d/m/Y',strtotime(date('Y-m-d')." + 2 day"));?>" class="button<?php echo date('Y-m-d',strtotime(date('Y-m-d')." + 2 day"))==$data?" active":"";?>">+ 2 dias</a>	
								<a href="<?php echo $_page."?data=".date('d/m/Y',strtotime(date('Y-m-d')." + 3 day"));?>" class="button<?php echo date('Y-m-d',strtotime(date('Y-m-d')." + 3 day"))==$data?" active":"";?>">+ 3 dias</a>		
							</dd>
						</dl>						
					</div>					
				</div>

			</section>

			<section class="grid" style="grid-template-columns:40% auto">

				<div class="box">

					<div class="filter">
						<div class="filter-group">
							<div class="filter-title">
								<h1>Índices de Ociosidade</h1>
							</div>
						</div>
					</div>

					<section class="tab">
						<a href="" class="active">Cadeiras</a>
						<a href="">Dentistas</a>						
					</section>

					<section style="width:100%; height:300px; background:var(--cinza2); margin-bottom:var(--margin1);">						
					</section>

					<div class="list4">
						<?php
						foreach($_cadeiras as $c) {

							$cadeiraHoras = isset($_horas[$c->id]) ? $_horas[$c->id] : 0;
							$agendaHoras = isset($_agendaHoras[$c->id]) ? $_agendaHoras[$c->id] : 0;

							$indice = 100-ceil($cadeiraHoras==0?0:($agendaHoras/$cadeiraHoras)*100);
						?>
						<a href="" class="list4-item active">
							<div>
								<h1>
									<?php 
									if($indice>0) {
										echo $indice.'% <i class="iconify" data-icon="fluent:arrow-download-20-regular" style="color:#FF0000"></i>';
									} else {
										echo ($indice==0?0:($indice*-1)).'% <i class="iconify" data-icon="fluent:arrow-export-up-20-filled" style="color:green"></i>';
									}
									?>
								</h1>
							</div>
							<div>
								<p><?php echo utf8_encode($c->titulo)." - ".$agendaHoras."/".$cadeiraHoras."m";?></p>
							</div>
						</a>
						<?php
						}
						?>
						
					</div>
				</div>

				<div class="box box-col">

					<div class="box-col__inner1" style="flex:0 1 45%;">

						<div class="filter">
							<div class="filter-group">
								<div class="filter-title">
									<h1>Sugestões</h1>
								</div>
							</div>
						</div>

						<?php

						$_historicoStatus=array();
						$sql->consult($_p."pacientes_historico_status","*","");
						while($x=mysqli_fetch_object($sql->mysqry)) {
							$_historicoStatus[$x->id]=$x;
						}

						# Sugestoes sem BI

							# Desmarcados sem agendamentos

								$desmarcadosPacientesIds=array();
								$desmarcadosPacientesAgenda=array();

								// busca pacientes desmarcados nos ultimos 360 dias 
								$sql->consult($_p."agenda","id,id_paciente,agenda_data","WHERE data > NOW() - INTERVAL 360 DAY and id_status=4 and lixo=0 order by agenda_data desc");
								if($sql->rows) {
									while($x=mysqli_fetch_object($sql->mysqry)) {
										$desmarcadosPacientesIds[$x->id_paciente]=$x->id_paciente;

										// capta apenas o ultimo desmarcado
										if(!isset($desmarcadosPacientesAgenda[$x->id_paciente])) {
											$desmarcadosPacientesAgenda[$x->id_paciente]=$x;
										}
									}
								}

								$desmarcadosPacientesAgendaJSON=array();
								// busca agendamentos confirmados, a confirmar ou atendidos dos pacientes que foram desmarcados nos ultimos 360 dias
								if(count($desmarcadosPacientesIds)>0) {
									$sql->consult($_p."agenda","id,id_paciente,agenda_data","WHERE data > NOW() - INTERVAL 360 DAY and id_paciente IN (".implode(",",$desmarcadosPacientesIds).") and id_status IN (1,2,5) and lixo=0 order by agenda_data desc");
									while($x=mysqli_fetch_object($sql->mysqry)) {

										if(isset($desmarcadosPacientesAgenda[$x->id_paciente])) {

											// ultimo agendamento desmarcado
											$ultimoAgendamentoDesmarcado = $desmarcadosPacientesAgenda[$x->id_paciente];


											// se o ultimo agendamento desmarcado for menor que o ultimo agendamento confirmado, a confirmado ou atendido, remove da lista
											$removerDaLista = (strtotime($ultimoAgendamentoDesmarcado->agenda_data)<strtotime($x->agenda_data))?1:0;
											if($removerDaLista==1) {
												unset($desmarcadosPacientesAgenda[$x->id_paciente]);
											}
										}

									}

									// busca historico
									$sql->consult($_p."pacientes_historico","*","where id_paciente IN (".implode(",",$desmarcadosPacientesIds).") and evento='observacao' order by data desc");
									while($x=mysqli_fetch_object($sql->mysqry)) {
										if(!isset($_pacientesStatus[$x->id_paciente])) {
											$_pacientesStatus[$x->id_paciente]=$x->id_obs;
										}
									}

									// busca pacientes que foram desmarcados
									$_pacientesDesmarcados=array();
									$sql->consult($_p."pacientes","id,nome,telefone1","where id IN (".implode(",",$desmarcadosPacientesIds).")");
									while ($x=mysqli_fetch_object($sql->mysqry)) {
										$_pacientesDesmarcados[$x->id]=$x;
									}

									// pacientes que foram desmarcados e nao tiveram outro agendamento confirmado, a confirmar ou atendido
									$cont=1;
									foreach($desmarcadosPacientesAgenda as $v) {
										if(isset($_pacientesDesmarcados[$v->id_paciente])) {
											$paciente=$_pacientesDesmarcados[$v->id_paciente];
											//echo $v->id_paciente." ".$_pacientesDesmarcados[$v->id_paciente]->nome."<BR>";
											//$nome=$cont++." - ".utf8_encode($paciente->nome);
											$nome=utf8_encode($paciente->nome);

											$status=isset($_pacientesStatus[$paciente->id])?$_pacientesStatus[$paciente->id]:0;
											$desmarcadosPacientesAgendaJSON[]=array('id_paciente'=>$paciente->id,
																					'nome'=>$nome,
																					'status'=>$status,
																					'telefone'=>empty($paciente->telefone1)?"":maskTelefone($paciente->telefone1));
										}
									}
								}

							# Pacientes contencao sem horario

								$atendidosPacientesIds=array();
								$atendidosPacientesAgenda=array();


								// busca os agendamentos dos ultimos 720 dias com status atendido
								$sql->consult($_p."agenda","id,id_paciente,agenda_data","WHERE data > NOW() - INTERVAL 720 DAY and id_status=5 and lixo=0 order by agenda_data desc");
								while($x=mysqli_fetch_object($sql->mysqry)) {

									if(!isset($atendidosPacientesAgenda[$x->id_paciente])) {
										$atendidosPacientesAgenda[$x->id_paciente]=$x;
										$atendidosPacientesIds[]=$x->id_paciente;
									}
								}


								$retornoPacientesAgendaJSON=array();
								if(count($atendidosPacientesIds)>0) {


									// busca pacientes que foram atendidos nos ultimos 720 dias
									$_pacientesAtendidosIds=array();
									$_pacientesAtendidos=array();
									$sql->consult($_p."pacientes","id,nome,telefone1,periodicidade","where id IN (".implode(",",$atendidosPacientesIds).") order by nome");
									while ($x=mysqli_fetch_object($sql->mysqry)) {
										if(isset($_pacientesPeriodicidade[$x->periodicidade])) {
											$_pacientesAtendidosIds[$x->periodicidade][$x->id]=$x->id;
											$_pacientesAtendidos[$x->id]=$x;
										}
									}

									// busca historico
									$sql->consult($_p."pacientes_historico","*","where id_paciente IN (".implode(",",$desmarcadosPacientesIds).") and evento='observacao' order by data desc");
									while($x=mysqli_fetch_object($sql->mysqry)) {
										if(!isset($_pacientesStatus[$x->id_paciente])) {
											$_pacientesStatus[$x->id_paciente]=$x->id_obs;
										}
									}

									// cria o array que restaram os pacientes que necessitam de retorno
									$_pacientesAtendidosIdsResto = $_pacientesAtendidosIds;

									$_pacientesAtendidosUltimoAgendamento = array();

									// roda todos os pacientes atendidos por periodicidade
									foreach($_pacientesAtendidosIds as $periodicidade=>$pacientesIds) {

										// busca agendamentos dos pacientes da periodicidade que foram atendidos e nao necessitam de retorno
										$sql->consult($_p."agenda","id,id_paciente,agenda_data","WHERE data > NOW() - INTERVAL $periodicidade MONTH and id_status IN (5,1,2) and id_paciente IN (".implode(",",$pacientesIds).") and lixo=0 order by agenda_data desc");
										while($x=mysqli_fetch_object($sql->mysqry)) {


											// remove da lista de pacientes que necessitam de retorno
											unset($_pacientesAtendidosIdsResto[$periodicidade][$x->id_paciente]);
										}
									}



									$retornoPacientesAgendaJSONAux=array();
									// monta a lista dos pacientes que necessitam de retorno
									foreach($_pacientesAtendidosIdsResto as $periodicidade=>$pacientes) {
								
										foreach($pacientes as $idPaciente) {
											if(isset($_pacientesAtendidos[$idPaciente])) {
												$paciente=$_pacientesAtendidos[$idPaciente];
												$nome=utf8_encode($paciente->nome);

												
												// ultimo agendamento 
												$ultimoAtendimento='';
												
												if(isset($atendidosPacientesAgenda[$paciente->id])) {
													$u=$atendidosPacientesAgenda[$paciente->id];
													$ultimoAtendimento=date('d/m/Y',strtotime($u->agenda_data));

													$tem=strtotime(date('Y-m-d H:i'))-strtotime($u->agenda_data);
													$tem/=(60*60*24*30);
													$tem=ceil($tem);
													if($tem<$paciente->periodicidade) continue;
													//$nome.=" ($paciente->periodicidade) ha $tem mese(s) - $u->agenda_data";
												} else {
													continue;
												}





												$status=isset($_pacientesStatus[$paciente->id])?$_pacientesStatus[$paciente->id]:0;

												$index=strtotime($u->agenda_data);
												if(isset($retornoPacientesAgendaJSONAux[$index])) {
													$index++;
												}

												$retornoPacientesAgendaJSONAux[$index]=array('id_paciente'=>$paciente->id,
																						'nome'=>$nome,
																						'status'=>$status,
																						'telefone'=>empty($paciente->telefone1)?"":maskTelefone($paciente->telefone1));
											}
										}
									}
									arsort($retornoPacientesAgendaJSONAux);
									foreach($retornoPacientesAgendaJSONAux as $x) {
										$retornoPacientesAgendaJSON[]=$x;
									}
								}




						?>

						<div class="list3">
							<a href="" class="list3-item">
								<i class="iconify" data-icon="fluent:lightbulb-filament-20-regular"></i>
								<p>Há <b><?php echo count($desmarcadosPacientesAgendaJSON);?></b> pacientes <strong>desmarcados</strong> sem agendamento futuro</p>
							</a>
							<a href="" class="list3-item">
								<i class="iconify" data-icon="fluent:lightbulb-filament-20-regular"></i>
								<p>Há <b><?php echo count($retornoPacientesAgendaJSON);?></b> pacientes que <b>necessitam de retorno</b></p>
							</a>
						</div>

					</div>

					<div class="box-col__inner1 box_inv">
						
						<form method="form" class="form">
							<div class="colunas">
								<dl>
									<dd>
										<select class="js-filtro-pacientes">
											<option value="desmarcados">desmarcado</option>
											<option value="retorno">retorno</option>
										</select>
									</dd>
								</dl>
								<dl>
									<dd>
										<select class="js-filtro-status">
											<option value="0">status</option>
											<?php
											foreach($_historicoStatus as $v) {
												echo '<option value="'.$v->id.'">'.utf8_encode($v->titulo).'</option>';
											}
											?>
										</select>
									</dd>
								</dl>
							</div>
						</form>

						<div class="list1">

							<script type="text/javascript">
								var pacientesDesmarcados = JSON.parse(`<?php echo json_encode($desmarcadosPacientesAgendaJSON);?>`);
								var pacientesRetorno = JSON.parse(`<?php echo json_encode($retornoPacientesAgendaJSON);?>`);
								var pacientes = [];
								var pagina = 0;
								var paginaReg = 10;
								var paginaQtd = 0;



								const pacientesLista = () => {
									
									$('.js-pacientes').html(``);

									if($('.js-filtro-pacientes option:selected').val()=="retorno") {
										pacientes = pacientesRetorno;
									} else {
										pacientes = pacientesDesmarcados;
									}

									let status = $('.js-filtro-status option:selected').val();

									if(status>0) {
										let newPacientes = [];
										let cont = 0;
										/*pacientes.forEach(x=>{
											if(x.status==status) {
												newPacientes.push(x);
											}

											cont++;
											if(cont==pacientes.length) {
												pacientes = newPacientes;
											}
										});*/

										pacientes = pacientes.filter(x=>{ return x.status==status});
									}

									if(pacientes.length==0) {

										$('.js-nenhumpaciente').show();
										$('.js-paginacao,.js-guia').hide();

									} else {
										$('.js-nenhumpaciente').hide();
										$('.js-paginacao,.js-guia').show();
										paginaQtd =  Math.ceil(pacientes.length/paginaReg);

										for (var i = pagina * paginaReg; i < pacientes.length && i < (pagina + 1) * paginaReg; i++) {

											x = pacientes[i];

											let icone = ``;

											if(x.status==1) {
												icone=`<i class="iconify" data-icon="fluent:call-dismiss-24-regular" style="font-size:2em; color:red;"></i>`;
											} else if(x.status==2) {
												icone=`<i class="iconify" data-icon="fluent:call-inbound-24-regular" style="font-size:2em; color:orange;"></i>`;
											} else if(x.status==3) {
												icone=`<i class="iconify" data-icon="fluent:call-missed-24-regular" style="font-size:2em; color:blue;"></i>`;
											}

											$('.js-pacientes').append(`<tr class="js-item" data-id_paciente=${x.id_paciente}>
																			<td class="list1__foto"><img src="img/ilustra-usuario.jpg" width="54" height="54" /></td>
																			<td>
																			<h1>${x.nome}</h1>
																			<p>${x.telefone}</p>
																			</td>
																			<td>${icone}</td>
																		</tr>`);

										};

										$('.js-guia').html(`Página <b>${pagina+1}</b> de <b>${paginaQtd}</b>`);

										if(paginaQtd==1) {
											$('.js-guia,.js-paginacao').hide();
										} else {

											$('.js-guia,.js-paginacao').show();
										}
									}
								}


								$(function(){

									$('.js-filtro-status').change(function(){
										pagina=0;
										pacientesLista();
									});

									$('.js-filtro-pacientes').change(function(){
										pagina=0;
										pacientesLista();
									}).trigger('change');

									$('.js-anterior').click(function(){
										if(pagina<=0) {
											pagina = paginaQtd-1;
										} else {
											pagina--;
										}
										pacientesLista();
									});

									$('.js-pacientes').on('click','.js-item',function(){
										pacienteRelacionamento($(this).attr('data-id_paciente'));
									})

									


									$('.js-proximo').click(function(){

										if(paginaQtd>1) {
											if((pagina+1)>=paginaQtd) {
												pagina = 0;
											} else {
												pagina++;
											}
											pacientesLista();
										}

									});
								})

							</script>

							<span class="js-nenhumpaciente"><center>Nenhum paciente</center></span>
							<table class="js-pacientes">
								
										
							</table>

							<div style="display:flex;flex-wrap: nowrap;justify-content:space-between;margin: 10px 10px 0px 10px;" class="js-paginacao">
								<a href="javascript:;" class="js-anterior"><span class="iconify" data-icon="akar-icons:circle-chevron-left-fill" data-height="25"></span></a>
								<span class="js-guia"></span>
								<a href="javascript:;" class="js-proximo"><span class="iconify" data-icon="akar-icons:circle-chevron-right-fill" data-height="25"></span></a>
							</div>
						</div>

					</div>

				</div>

			</section>
		
		</div>
	</main>

	

<?php 
	


	$apiConfig=array('pacienteRelacionamento'=>1);
	require_once("includes/api/apiAside.php");

	include "includes/footer.php";
?>	