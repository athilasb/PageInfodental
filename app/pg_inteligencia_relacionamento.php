<?php
	if(isset($_POST['ajax'])) {

		require_once("lib/conf.php");
		require_once("usuarios/checa.php");

		$rtn = array();

		if($_POST['ajax']=="atualizaListaInteligente") {

			$_historicoStatus=array();
			$sql->consult($_p."pacientes_historico_status","*","");
			while($x=mysqli_fetch_object($sql->mysqry)) {
				$_historicoStatus[$x->id]=$x;
			}

			$_pacientesExcluidos=array();
			$pacientesIds=array();
			$atendidosPacientesIds=array();
			$desmarcadosPacientesIds=array();

			$sql->consult($_p."pacientes_excluidos","*","where lixo=0");
			while($x=mysqli_fetch_object($sql->mysqry)) {
				$_pacientesExcluidos[$x->id_paciente]=$x;
				$pacientesIds[]=$x->id_paciente;
				$atendidosPacientesIds[]=$x->id_paciente;
				$desmarcadosPacientesIds[]=$x->id_paciente;
			}


			$_pacientesExcluidosObj=array();
			$_pacientesExcluidosLista=array();
			
			if(count($pacientesIds)>0) {
				$sql->consult($_p."pacientes","id,telefone1,foto_cn,nome","where id IN (".implode(",",$pacientesIds).")");
				while($x=mysqli_fetch_object($sql->mysqry)) {
					$_pacientesExcluidosObj[$x->id]=$x;
				}

				
			}

			# Sugestoes sem BI

				# Desmarcados sem agendamentos

					$desmarcadosPacientesAgenda=array();

					// busca pacientes desmarcados nos ultimos 360 dias 
					$sql->consult($_p."agenda","id,id_paciente,agenda_data","WHERE data > NOW() - INTERVAL 360 DAY and id_status=4 and lixo=0 order by agenda_data desc");
					if($sql->rows) {
						while($x=mysqli_fetch_object($sql->mysqry)) {
							if(isset($_pacientesExcluidos[$x->id_paciente])) {
								$_pacientesExcluidosLista[$x->id_paciente]='Desmarcado';
								continue;
							}

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
						$sql->consult($_p."pacientes","id,nome,telefone1,foto_cn","where id IN (".implode(",",$desmarcadosPacientesIds).") and lixo=0");
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

								$ft='';
								if(!empty($paciente->foto_cn)) $ft=$_cloudinaryURL.'c_thumb,w_100,h_100/'.$paciente->foto_cn;

								$status=isset($_pacientesStatus[$paciente->id])?$_pacientesStatus[$paciente->id]:0;
								$desmarcadosPacientesAgendaJSON[]=array('id_paciente'=>$paciente->id,
																		'nome'=>$nome,
																		'ft'=>$ft,
																		'status'=>$status,
																		'telefone'=>empty($paciente->telefone1)?"":maskTelefone($paciente->telefone1));
							}
						}
					}


				# Pacientes contencao sem horario

					$atendidosPacientesAgenda=array();
					$atendidosPacientesVezes=array();


					// busca os agendamentos dos ultimos 720 dias com status atendido
					$sql->consult($_p."agenda","id,id_paciente,agenda_data","WHERE data > NOW() - INTERVAL 720 DAY and id_status=5 and lixo=0 order by agenda_data desc");
					while($x=mysqli_fetch_object($sql->mysqry)) {
						if(isset($_pacientesExcluidos[$x->id_paciente])) {
							$_pacientesExcluidosLista[$x->id_paciente]='Retorno';
							continue;
						}

						if(!isset($atendidosPacientesAgenda[$x->id_paciente])) {

							if(!isset($atendidosPacientesVezes[$x->id_paciente])) $atendidosPacientesVezes[$x->id_paciente]=0;
							$atendidosPacientesVezes[$x->id_paciente]++;

							if($atendidosPacientesVezes[$x->id_paciente]>=3) {
								$atendidosPacientesAgenda[$x->id_paciente]=$x;
								$atendidosPacientesIds[]=$x->id_paciente;
							}
						}
					}


					$retornoPacientesAgendaJSON=array();
					if(count($atendidosPacientesIds)>0) {


						// busca pacientes que foram atendidos nos ultimos 720 dias
						$_pacientesAtendidosIds=array();
						$_pacientesAtendidos=array();
						$sql->consult($_p."pacientes","id,nome,telefone1,periodicidade","where id IN (".implode(",",$atendidosPacientesIds).") and lixo=0 order by nome");
						while ($x=mysqli_fetch_object($sql->mysqry)) {
							if(isset($_pacientesPeriodicidade[$x->periodicidade])) {
								$_pacientesAtendidosIds[$x->periodicidade][$x->id]=$x->id;
								$_pacientesAtendidos[$x->id]=$x;
							}
						}

						// busca historico
						$sql->consult($_p."pacientes_historico","*","where id_paciente IN (".implode(",",$atendidosPacientesIds).") and evento='observacao' order by data desc");
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

								// se nao estiver na lista de desmarcados (desmarcadosPacientesIds);
								if(isset($_pacientesAtendidos[$idPaciente]) and !isset($desmarcadosPacientesIds[$idPaciente])) {
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

									$ft='';
									if(!empty($paciente->foto_cn)) $ft=$_cloudinaryURL.'c_thumb,w_100,h_100/'.$paciente->foto_cn;

									$retornoPacientesAgendaJSONAux[$index]=array('id_paciente'=>$paciente->id,
																			'nome'=>$nome,
																			'ft'=>$ft,
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

				# Excluidos

					$pacientesExcluidosJSON=array();
					foreach($_pacientesExcluidos as $x) {

						if(isset($_pacientesExcluidosObj[$x->id_paciente])) {
							$paciente=$_pacientesExcluidosObj[$x->id_paciente];

							$ft='';
							if(!empty($paciente->foto_cn)) $ft=$_cloudinaryURL.'c_thumb,w_100,h_100/'.$paciente->foto_cn;


							$status=isset($_pacientesStatus[$paciente->id])?$_pacientesStatus[$paciente->id]:0;

							$lista=isset($_pacientesExcluidosLista[$paciente->id])?$_pacientesExcluidosLista[$paciente->id]:'';

							$pacientesExcluidosJSON[]=array('id_paciente'=>$paciente->id,
																'nome'=>utf8_encode($paciente->nome),
																'ft'=>$ft,
																'lista'=>$lista,
																'status'=>$status,
																'telefone'=>empty($paciente->telefone1)?"":maskTelefone($paciente->telefone1));
						}
					}

				# Ordena lista
					// Ordena lista

					/*
					3 - Pediu pra retornar
					0 - Sem status
					1 - Não conseguiu contato
					2 - Paciente entrará em contato
					*/

					$statusOrdem=array(3=>1,
										0=>2,
										1=>3,
										2=>4);


					$desmarcadosPacientesAgendaJSONOrdenada=array();
					foreach($desmarcadosPacientesAgendaJSON as $v) {
						$index = $statusOrdem[$v['status']];
						$desmarcadosPacientesAgendaJSONOrdenada[$index][]=$v;
					};

					$retornoPacientesAgendaJSONOrdenada=array();
					foreach($retornoPacientesAgendaJSON as $v) {
						$index = $statusOrdem[$v['status']];
						$retornoPacientesAgendaJSONOrdenada[$index][]=$v;
					}

					$desmarcadosPacientesAgendaJSON=array();
					$retornoPacientesAgendaJSON=array();
					for($i=1;$i<=4;$i++) {
						if(isset($desmarcadosPacientesAgendaJSONOrdenada[$i])) {
							foreach($desmarcadosPacientesAgendaJSONOrdenada[$i] as $v) {
								$desmarcadosPacientesAgendaJSON[]=$v;
							}
						}

						if(isset($retornoPacientesAgendaJSONOrdenada[$i])) {
							foreach($retornoPacientesAgendaJSONOrdenada[$i] as $v) {
								$retornoPacientesAgendaJSON[]=$v;
							}
						}
					}



			$rtn=array('success'=>true,
						'pacientesDesmarcados'=> ($desmarcadosPacientesAgendaJSON),
						'pacientesRetorno'=> ($retornoPacientesAgendaJSON),
						'pacientesExcluidos'=> ($pacientesExcluidosJSON));

		}


		header("Content-type: application/json");
		echo json_encode($rtn);
		die();

	}
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

	$_historicoStatus=array();
	$sql->consult($_p."pacientes_historico_status","*","");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_historicoStatus[$x->id]=$x;
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


	// calcula horas do dia de cada cadeira
	$dataDia = date('w',strtotime($data));

	$_horas = array();
	$_horasMes = array();
	$sql->consult($_p."parametros_cadeiras_horarios","*","where lixo=0");
	if($sql->rows) {
		while($x=mysqli_fetch_object($sql->mysqry)) {

			$dif = (strtotime($x->fim)-strtotime($x->inicio))/(60);

			if(!isset($_horasMes[$x->id_cadeira][$x->dia])) $_horasMes[$x->id_cadeira][$x->dia]=0;
			$_horasMes[$x->id_cadeira][$x->dia]+=$dif;


			if($dataDia==$x->dia) {
				if(!isset($_horas[$x->id_cadeira])) $_horas[$x->id_cadeira]=0;
				$_horas[$x->id_cadeira]+=$dif;
			}

		}
	}


	$_agendaHoras = array();
	$_agendaHorasMes = array();

	$sql->consult($_p."agenda","id,id_cadeira,agenda_data,agenda_duracao","where agenda_data>='".date('Y-m-01')." 00:00:00' and agenda_data<='".date('Y-m-t')." 23:59:59' and id_status IN (1,2,5) and lixo=0");
	while($x=mysqli_fetch_object($sql->mysqry)) {

			$dia = date('d',strtotime($x->agenda_data));

			if(!isset($_agendaHorasMes[$x->id_cadeira][$dia])) $_agendaHorasMes[$x->id_cadeira][$dia]=0;
			$_agendaHorasMes[$x->id_cadeira][$dia]+=$x->agenda_duracao;

			if(date('Y-m-d',strtotime($x->agenda_data))==$data) {
				if(!isset($_agendaHoras[$x->id_cadeira])) $_agendaHoras[$x->id_cadeira]=0;
				$_agendaHoras[$x->id_cadeira]+=$x->agenda_duracao;
			}
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
		</div>
	</header>


	<main class="main">
		<div class="main__content content">

			<section class="filter">
				
				<div class="filter-group">
					<div class="filter-title">	
						<p>Lista de relacionamento de pacientes</p>
					</div>
				</div>
				

				<form method="get" class="js-filtro">
					<div class="filter-group">
						<div class="filter-form form">

							<dl>
								<dd>
									<select class="js-filtro-pacientes" style="width:200px;">
										<option value="desmarcados">desmarcado</option>
										<option value="retorno">retorno</option>
										<option value="excluidos">excluídos</option>
									</select>
								</dd>
							</dl>

							<dl>
								<dd>
									<select class="js-filtro-status" style="width:200px;">
										<option value="0">status</option>
										<?php
										foreach($_historicoStatus as $v) {
											echo '<option value="'.$v->id.'">'.utf8_encode($v->titulo).'</option>';
										}
										?>
									</select>
								</dd>
							</dl>

							<dl>
								<dd><input type="text" placeholder="Buscar..." value="<?php echo isset($_GET['busca'])?($_GET['busca']):"";?>" class="js-filtro-busca" /></dd>
							</dl>
						</div>					
					</div>
				</form>

			</section>

			<section class="grid" style="grid-template-columns:100% auto">

			

				<div class="box box-col">

					<div class="box-col__inner1" style="flex:0 1 45%;">

						<div class="filter">
							<div class="filter-group">
								<div class="filter-title">
									<h1>Sugestões</h1>
								</div>
							</div>
						</div>

						<div class="list3">
							<span class="list3-item">
								<i class="iconify" data-icon="fluent:lightbulb-filament-20-regular"></i>
								<p>Há <b class="js-indicador-desmarcados">0</b> pacientes <strong>desmarcados</strong> sem agendamento futuro</p>
							</span>
							<span class="list3-item">
								<i class="iconify" data-icon="fluent:lightbulb-filament-20-regular"></i>
								<p>Há <b class="js-indicador-retorno">0</b> pacientes que <b>necessitam de retorno</b></p>
							</span>
							<span class="list3-item">
								<i class="iconify" data-icon="fluent:lightbulb-filament-20-regular"></i>
								<p>Há <b class="js-indicador-excluidos">0</b> pacientes <b>excluídos</b></p>
							</span>
						</div>

					</div>

					<div class="box-col__inner1 box_inv">
						
						

						<div class="list1">

							<script type="text/javascript">
								<?php 
								/*var pacientesDesmarcados = JSON.parse(`<?php echo json_encode($desmarcadosPacientesAgendaJSON);?>`);
								var pacientesRetorno = JSON.parse(`<?php echo json_encode($retornoPacientesAgendaJSON);?>`);
								var pacientesExcluidos = JSON.parse(`<?php echo json_encode($pacientesExcluidosJSON);?>`);*/
								?>


								var pacientesDesmarcados = [];
								var pacientesRetorno = [];
								var pacientesExcluidos = [];

								var pacientes = [];
								var pagina = 0;
								var paginaReg = 10;
								var paginaQtd = 0;

								const atualizaValorListasInteligentes = () => {

									let data = `ajax=atualizaListaInteligente`;

									$.ajax({
										type:"POST",
										data:data,
										success:function(rtn) {
											if(rtn.success) {

												pacientesDesmarcados = rtn.pacientesDesmarcados;
												pacientesRetorno = rtn.pacientesRetorno;
												pacientesExcluidos = rtn.pacientesExcluidos;

												

											}
										}
									}).done(function(){
										pacientesLista();
									})

								}



								const pacientesLista = () => {
									
									$('.js-pacientes').html(``);

									let filtro = $('.js-filtro-pacientes option:selected').val();
									let busca = $('.js-filtro-busca').val();

									if(filtro=="retorno") {
										pacientes = pacientesRetorno;
									} else if(filtro=="excluidos") {
										pacientes = pacientesExcluidos;
									} else {
										pacientes = pacientesDesmarcados;
									}

									$('.js-indicador-desmarcados').html(pacientesDesmarcados.length);
									$('.js-indicador-retorno').html(pacientesRetorno.length);
									$('.js-indicador-excluidos').html(pacientesExcluidos.length);

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

										pacientes = pacientes.filter(x=>{return x.status==status});
									}

									if(busca.length>0) {
										pacientes = pacientes.filter(x=>{return x.nome.toLowerCase().indexOf(busca.toLowerCase())>=0});
									}

									if(pacientes.length==0) {
										$('.js-nenhumpaciente').show();
										$('.js-paginacao,.js-guia,.js-carregando').hide();
									} else {
										$('.js-nenhumpaciente,.js-carregando').hide();
										$('.js-paginacao,.js-guia').show();
										paginaQtd =  Math.ceil(pacientes.length/paginaReg);

										for (var i = pagina * paginaReg; i < pacientes.length && i < (pagina + 1) * paginaReg; i++) {

											x = pacientes[i];

											let icone = ``;

											// nao conseguiu contato 
											if(x.status==1) {
												icone=`<i class="iconify" data-icon="fluent:call-dismiss-24-regular" style="font-size:2em; color:red;"></i>`;
											} 
											// paciente entrara em contato
											else if(x.status==2) {
												icone=`<i class="iconify" data-icon="fluent:call-inbound-24-regular" style="font-size:2em; color:orange;"></i>`;
											} 
											// paciente pediu para retornar posteriormente
											else if(x.status==3) {
												icone=`<i class="iconify" data-icon="fluent:call-missed-24-regular" style="font-size:2em; color:blue;"></i>`;
											}

											let lista=``;
											if(filtro=="excluidos") {
												lista=x.lista;
											}

	
											let ft = (x.ft && x.ft.length>0)?x.ft:'img/ilustra-usuario.jpg';
											$('.js-pacientes').append(`<tr class="js-item" data-filtro="${filtro}" data-id_paciente="${x.id_paciente}" data-lista="${lista}">
																			<td class="list1__foto"><img src="${ft}" width="54" height="54" /></td>
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

									atualizaValorListasInteligentes();

									$('.js-filtro-status').change(function(){
										pagina=0;
										pacientesLista();
									});

									$('.js-filtro-busca').keyup(function(){
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
										pacienteRelacionamento($(this));
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
							<span class="js-carregando"><center>Carregando...</center></span>
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