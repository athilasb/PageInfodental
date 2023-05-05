<?php
	include "includes/header.php";
	include "includes/nav.php";
	if($usr->tipo!="admin" and !in_array("inteligencia",$_usuariosPermissoes)) {
		$jsc->jAlert("Você não tem permissão para acessar esta área!","erro","document.location.href='dashboard.php'");
		die();
	}


	$data_inicial_filtro = isset($_GET['data_inicio']) ? $_GET['data_inicio'] : date('Y-m-d', strtotime("-1 years"));
	$data_final_filtro =  isset($_GET['data_final']) ? $_GET['data_final'] : date('Y-m-d');
	

	$data = isset($_GET['data'])?$_GET['data']:date('d/m/Y');

	list($dia,$mes,$ano)=explode("/",$data);

	if(checkdate($mes, $dia, $ano)) {
		$data=$mes."/".$dia."/".$ano;
		$dataWH=$ano."-".$mes."-".$dia;
	} else { 
		$data=date('m/d/Y');
		$dataWH=date('Y-m-d');
	}


	$_historicoStatus=array();
	$sql->consult($_p."pacientes_historico_status","*","");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_historicoStatus[$x->id]=$x;
	}
	$_profissionais=array();
	$sql->consult($_p."colaboradores","id,nome,calendario_iniciais,foto,calendario_cor,check_agendamento","where lixo=0 order by nome asc");
	while($x=mysqli_fetch_object($sql->mysqry)) $_profissionais[$x->id]=$x;


	$_status=array();
	$sql->consult($_p."agenda_status","*","where lixo=0 order by kanban_ordem asc");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_status[$x->id]=$x;
	}

	$_cadeiras=array();
	$sql->consult($_p."parametros_cadeiras","id,titulo","where lixo=0 order by ordem asc");
	while($x=mysqli_fetch_object($sql->mysqry)) $_cadeiras[$x->id]=$x;

	$_pacientes=array('novos'=>array(),
						'novosIds'=>array(0),
						'aguardandoAprovacao'=>array(),
						'retorno'=>array());

	$sql->consult($_p."pacientes","id,nome,telefone1,foto,foto_cn","where data>='".$data_inicial_filtro." 00:00:00' and data<='".$data_final_filtro." 23:59:59' and codigo_bi=1 and lixo=0");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_pacientes['novos'][]=$x;
		$_pacientes['novosIds'][]=$x->id;
	}
	$sql->consult($_p."agenda","*","where id_paciente in (".implode(",",$_pacientes['novosIds']).") and lixo=0");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		if($x->id_status==1 or $x->id_status==2 or $x->id_status==5) $pacientesNovosComAgendamento[$x->id_paciente]=$x;
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
			$proc=json_decode(utf8_encode($x->procedimentos));
			foreach($proc as $p) {
				if(!isset($_tratamentosProcedimentos[$x->id_paciente])) $_tratamentosProcedimentos[$x->id_paciente]=0;
				$_tratamentosProcedimentos[$x->id_paciente]+=$p->valorCorrigido;
				$aguardandoAprovacaoAReceber+=$p->valorCorrigido;
			}
		}
	}



	$sql->consult($_p."pacientes","id,nome,telefone1,foto,foto_cn","where id IN (".implode(",",$pacientesComTratamentosIds).") and lixo=0");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_pacientes['aguardandoAprovacao'][]=$x;
	}

	$pacientesAtendidosIds=array(0);
	$pacientesAtendidosIdsQueVaoSair=array();
	$sql->consult($_p."agenda","*","where id_status=5 and id_paciente in (".implode(",",$_pacientes['novosIds']).") and lixo=0");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		if(isset($pacientesAtendidosIds[$x->id_paciente])) $pacientesAtendidosIdsQueVaoSair[]=$x->id_paciente;
		else {
			$pacientesAtendidosIds[$x->id_paciente]=$x->id_paciente;
		}
	}

	// se tiver paciente que foi atendido mais de uma vez, sai da lista de retorno
	if(count($pacientesAtendidosIdsQueVaoSair)>0) {
		foreach($pacientesAtendidosIdsQueVaoSair as $idPaciente) {
			unset($pacientesAtendidosIds[$idPaciente]);
		}
	}

	if(count($pacientesAtendidosIds)) {
		$sql->consult($_p."pacientes","id,nome,telefone1,foto,foto_cn","where id IN (".implode(",",$pacientesAtendidosIds).") and lixo=0");
		//echo "where id IN (".implode(",",$pacientesAtendidosIds).") and lixo=0 $sql->rows;";die();
		while($x=mysqli_fetch_object($sql->mysqry)) {
			$_pacientes['retorno'][]=$x;
		}
	}


?> 
	<script type="text/javascript">
		const atualizaValorListasInteligentes = () => {

			document.location.reload();

		}

		$(function(){
			$('.js-calendario').daterangepicker({
				"autoApply": true,
				"locale": {
					"format": "DD/MM/YYYY",
					"separator": " - ",
					"fromLabel": "De",
					"toLabel": "Até",
					"customRangeLabel": "Customizar",
					"weekLabel": "W",
					"daysOfWeek": [
						"Dom",
						"Seg",
						"Ter",
						"Qua",
						"Qui",
						"Sex",
						"Sáb"
					],
					"monthNames": [
						"Janeiro",
						"Fevereiro",
						"Março",
						"Abril",
						"Maio",
						"Junho",
						"Julho",
						"Agosto",
						"Setembro",
						"Outubro",
						"Novembro",
						"Dezembro"
					],
					"firstDay": 1
				},
			});


			$('.js-calendario').on('apply.daterangepicker', function(ev, picker) {
				let dtFim = picker.endDate.format('YYYY-MM-DD');
				let dtInicio = picker.startDate.format('YYYY-MM-DD');
				document.location.href = `<?php echo "$_page?"; ?>&data_inicio=${dtInicio}&data_final=${dtFim}`
			});
		})
	</script>
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
				<div class="header-date-now">
					<h1 id="dia_i"><?= date('d', strtotime($data_inicial_filtro)) ?></h1>
					<h2 id="mes_i"><?= strtolower(substr(mes(date('m', strtotime($data_inicial_filtro))),0,3)) ?></h2>
					<h1 id="ano_i"><?= date('Y', strtotime($data_inicial_filtro)) ?></h1>
					até
					<h1 id="dia_f"><?= date('d', strtotime($data_final_filtro)) ?></h1>
					<h2 id="mes_f"><?= strtolower(substr(mes(date('m', strtotime($data_final_filtro))),0,3)) ?></h2>
					<h1 id="ano_i"><?= date('Y', strtotime($data_final_filtro)) ?></h1>
				</div>
			</section>
		</div>

			
		</div>
	</header>

	<main class="main">
		<div class="main__content content">

			<section class="filter">
				<div class="filter-group">
					<dl>
						<dd>
							&nbsp;
						</dd>
					</dl>
				</div>
				<div class="filter-group">
					<a href="javascript:;" class="button js-calendario">
						<span class="iconify" data-icon="bi:calendar-week"></span>
					</a>
				</div>
			</section>

			<section class="grid" style="flex:1;margin-top:40px;">
				<div class="kanban" id="kanban" style="grid-template-columns: repeat(4,minmax(0,4fr))">
					
					
					<div class="kanban-item" style="background:var(--cinza5);">
						<header>
							<h1 class="kanban-item__titulo">Sem Agendamento</h1>
						</header>
						<article class="kanban-card" style="min-height: 100px;">
							<?php
							foreach($_pacientes['novos'] as $p) {
								if(isset($pacientesNovosComAgendamento[$p->id])) continue;

								$ft='img/ilustra-usuario.jpg';
								if(!empty($p->foto_cn)) {
									$ft=$_cloudinaryURL.'c_thumb,w_100,h_100/'.$p->foto_cn;
								} else if(!empty($p->foto)) {
									$ft=$_wasabiURL."arqs/clientes/".$p->id.".jpg";
								}
							?>	
								<a href="javascript:asideQueroAgendar(<?php echo $p->id;?>,0)" class="js-exame-item" style="padding:  0 0 0 0;display: flex;flex-direction: row;">
									<img src="<?php echo $ft;?>" style="display: block;width: 70px;height: 70px;border-radius: 4px 0 0 4px;" />
									<div style="padding-top:7px;">
										<h1><?php echo utf8_encode($p->nome);?> - <?php echo $p->id;?></h1>
										<p><?php echo maskTelefone($p->telefone1);?></p>
									</div>
								</a>
								<?php /*<a href="pg_pacientes_resumo.php?id_paciente=<?php echo $p->id;?>" target="_blank">
									<h1><?php echo utf8_encode($p->nome);?> - <?php echo $p->id;?></h1>
									<p><?php echo maskTelefone($p->telefone1);?></p>
								</a>*/?>
							<?php	
							}
							?>
						</article>
					</div>

					<div class="kanban-item" style="background:var(--cinza5);">
						<header>
							<h1 class="kanban-item__titulo">Com Agendamento</h1>
						</header>
						<article class="kanban-card" style="min-height: 100px;">
							<?php
							foreach($_pacientes['novos'] as $p) {
								if(isset($pacientesNovosComAgendamento[$p->id])) {
									$agendamento=$pacientesNovosComAgendamento[$p->id];
									if(isset($pacientesNovosAtendidos[$p->id])) continue;

									$ft='img/ilustra-usuario.jpg';
									if(!empty($p->foto_cn)) {
										$ft=$_cloudinaryURL.'c_thumb,w_100,h_100/'.$p->foto_cn;
									} else if(!empty($p->foto)) {
										$ft=$_wasabiURL."arqs/clientes/".$p->id.".jpg";
									}
							?>	
								<a href="javascript:asideQueroAgendar(<?php echo $p->id;?>,0)" class="js-exame-item" style="padding:  0 0 0 0;display: flex;flex-direction: row;">
									<img src="<?php echo $ft;?>" style="display: block;width: 70px;height: 70px;border-radius: 4px 0 0 4px;" />
									<div style="padding-top:7px;">
										<h1><?php echo utf8_encode($p->nome);?> - <?php echo $p->id;?></h1>
										<p><?php echo maskTelefone($p->telefone1);?></p>
										<p style="color:var(--cinza5);"><span class="iconify" data-icon="akar-icons:calendar"></span> <?php echo date('d/m/Y - H:i',strtotime($agendamento->agenda_data));?></p>
									</div>
								</a>
							<?php /*	
								<a href="pg_pacientes_resumo.php?id_paciente=<?php echo $p->id;?>" target="_blank">
									<h1><?php echo utf8_encode($p->nome);?> <?php echo $p->id;?></h1>
									<p><?php echo maskTelefone($p->telefone1);?></p>
								</a>*/?>

							<?php	
								}
							}
							?>
						</article>
					</div>

					<div class="kanban-item" style="background:var(--cinza5);">
						<header>
							<h1 class="kanban-item__titulo">Para Retorno</h1>
						</header>
						<article class="kanban-card" style="min-height: 100px;">
							<?php
							foreach($_pacientes['retorno'] as $p) {
								$ft='img/ilustra-usuario.jpg';
								if(!empty($p->foto_cn)) {
									$ft=$_cloudinaryURL.'c_thumb,w_100,h_100/'.$p->foto_cn;
								} else if(!empty($p->foto)) {
									$ft=$_wasabiURL."arqs/clientes/".$p->id.".jpg";
								}
							?>	
								<a href="javascript:asideQueroAgendar(<?php echo $p->id;?>,0)" class="js-exame-item" style="padding:  0 0 0 0;display: flex;flex-direction: row;">
									<img src="<?php echo $ft;?>" style="display: block;width: 70px;height: 70px;border-radius: 4px 0 0 4px;" />
									<div style="padding-top:7px;">
										<h1><?php echo utf8_encode($p->nome);?> - <?php echo $p->id;?></h1>
										<p><?php echo maskTelefone($p->telefone1);?></p>
									</div>
								</a>
								<?php /*<a href="pg_pacientes_resumo.php?id_paciente=<?php echo $p->id;?>" target="_blank">
									<h1><?php echo utf8_encode($p->nome);?> - <?php echo $p->id;?></h1>
									<p><?php echo maskTelefone($p->telefone1);?></p>
								</a>*/?>
							<?php	
							}
							?>
						</article>
					</div>

					<div class="kanban-item" style="background:var(--cinza5);">
						<header>
							<h1 class="kanban-item__titulo">Aguardando Aprovação</h1>

						</header>
						<p style="text-align:center;color:#ccc;margin-top:-15px;margin-bottom: 10px;">R$<?php echo number_format($aguardandoAprovacaoAReceber,2,",",".");?></p>
						<article class="kanban-card" style="min-height: 100px;">
							<?php
							foreach($_pacientes['aguardandoAprovacao'] as $p) {
								$valor=isset($_tratamentosProcedimentos[$p->id])?$_tratamentosProcedimentos[$p->id]:0;
								$ft='img/ilustra-usuario.jpg';
								if(!empty($p->foto_cn)) {
									$ft=$_cloudinaryURL.'c_thumb,w_100,h_100/'.$p->foto_cn;
								} else if(!empty($p->foto)) {
									$ft=$_wasabiURL."arqs/clientes/".$p->id.".jpg";
								}
							?>	
								<a href="javascript:asideQueroAgendar(<?php echo $p->id;?>,0)" class="js-exame-item" style="padding:  0 0 0 0;display: flex;flex-direction: row;">
									<img src="<?php echo $ft;?>" style="display: block;width: 70px;height: 70px;border-radius: 4px 0 0 4px;" />
									<div style="padding-top:7px;">
										<h1><?php echo utf8_encode($p->nome);?> - <?php echo $p->id;?></h1>
										<p><?php echo maskTelefone($p->telefone1);?></p>
										<p>Tratamentos: <?php echo count($_pacientesTratamentos[$p->id]);?></p>
										<p>Valor: <?php echo number_format($valor,2,",",".");?></p>
									</div>
								</a>
								<?php /*<a href="pg_pacientes_resumo.php?id_paciente=<?php echo $p->id;?>" target="_blank">
									<h1><?php echo utf8_encode($p->nome);?> - <?php echo $p->id;?></h1>
									<p><?php echo maskTelefone($p->telefone1);?></p>
									<p>Tratamentos: <?php echo count($_pacientesTratamentos[$p->id]);?></p>
									<p>Valor: <?php echo number_format($valor,2,",",".");?></p>
								</a>*/?>
							<?php	
							}
							?>
						</article>
					</div>

					
					
				</div>
			</section>

		</div>
	</main>

<?php 
	
	$apiConfig=array('queroAgendar'=>1);
	require_once("includes/api/apiAside.php");


	include "includes/footer.php";
?>	