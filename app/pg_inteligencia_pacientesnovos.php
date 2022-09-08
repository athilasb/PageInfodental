<?php
	include "includes/header.php";
	include "includes/nav.php";


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
		if($x->id_status==1 or $x->id_status==2) $pacientesNovosComAgendamento[$x->id_paciente]=1;
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

			

			<section class="grid" style="flex:1;margin-top:40px;">
				<div class="kanban" id="kanban">
					
					
					<div class="kanban-item" style="background:">
						<header>
							<h1 class="kanban-item__titulo">Sem Agendamento</h1>
						</header>
						<article class="kanban-card" style="min-height: 100px;">
							<?php
							foreach($_pacientes['novos'] as $p) {
								if(isset($pacientesNovosComAgendamento[$p->id])) continue;
							?>
								<a href="pg_pacientes_resumo.php?id_paciente=<?php echo $p->id;?>" target="_blank">
									<h1><?php echo utf8_encode($p->nome);?> <?php echo $p->id;?></h1>
									<p><?php echo maskTelefone($p->telefone1);?></p>
								</a>
							<?php	
							}
							?>
						</article>
					</div>

					<div class="kanban-item" style="background:">
						<header>
							<h1 class="kanban-item__titulo">Com Agendamento</h1>
						</header>
						<article class="kanban-card" style="min-height: 100px;">
							<?php
							foreach($_pacientes['novos'] as $p) {
								if(isset($pacientesNovosComAgendamento[$p->id])) {
									if(isset($pacientesNovosAtendidos[$p->id])) continue;
							?>
								<a href="pg_pacientes_resumo.php?id_paciente=<?php echo $p->id;?>" target="_blank">
									<h1><?php echo utf8_encode($p->nome);?> <?php echo $p->id;?></h1>
									<p><?php echo maskTelefone($p->telefone1);?></p>
								</a>

							<?php	
								}
							}
							?>
						</article>
					</div>

					<div class="kanban-item" style="background:">
						<header>
							<h1 class="kanban-item__titulo">Para Retorno</h1>
						</header>
						<article class="kanban-card" style="min-height: 100px;">
							<?php
							foreach($_pacientes['retorno'] as $p) {
							?>
								<a href="pg_pacientes_resumo.php?id_paciente=<?php echo $p->id;?>" target="_blank">
									<h1><?php echo utf8_encode($p->nome);?> <?php echo $p->id;?></h1>
									<p><?php echo maskTelefone($p->telefone1);?></p>
								</a>
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
	
	$apiConfig=array('paciente'=>1,
						'proximaConsulta'=>1);
	require_once("includes/api/apiAside.php");


	include "includes/footer.php";
?>	