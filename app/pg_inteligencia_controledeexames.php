<?php
	include "includes/header.php";
	include "includes/nav.php";

	$_clinicas=array();
	$sql->consult($_p."parametros_fornecedores","*","");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_clinicas[$x->id]=$x;
	}


	$_pedidosDeExames=array('concluido'=>[],'aguardando'=>[],'naoRealizado'=>[]);
	$_pacientes=$_evolucoes=$_exames=array();


	$evolucoesIds=$pacientesIds=$examesIds=[];
	$sql->consult($_p."pacientes_evolucoes","*","where id_tipo=6 and lixo=0 order by data_pedido desc");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_evolucoes[$x->id]=$x;
		$evolucoesIds[]=$x->id;
		$pacientesIds[]=$x->id_paciente;
	}

	if(count($evolucoesIds)>0) {
		$sql->consult($_p."pacientes_evolucoes_pedidosdeexames","*","where id_evolucao IN (".implode(",",$evolucoesIds).") and lixo=0 order by id desc");
		if($sql->rows) { 
			while($x=mysqli_fetch_object($sql->mysqry)) {
				$_pedidosDeExames[$x->status][$x->id_evolucao][]=$x;
				$examesIds[]=$x->id_exame;
			}
		}

		$sql->consult($_p."pacientes","id,nome","where id IN (".implode(",",$pacientesIds).") and lixo=0");
		if($sql->rows) {
			while($x=mysqli_fetch_object($sql->mysqry)) {
				$_pacientes[$x->id]=$x;
			}
		}

		$sql->consult($_p."parametros_examedeimagem","*","where id IN (".implode(",",$examesIds).")");
		while($x=mysqli_fetch_object($sql->mysqry)) {
			$_exames[$x->id]=$x;
		}
	}


	// monta arrays
	$pedidos=array('aguardando'=>[],'concluido'=>[],'naoRealizado'=>[]);
	
	if(isset($_pedidosDeExames['aguardando'])) {
		foreach($_pedidosDeExames['aguardando'] as $id_evolucao=>$x) {
			if(isset($_evolucoes[$id_evolucao])) {
				$evolucao=$_evolucoes[$id_evolucao];


				$dif = strtotime(date('Y-m-d'))-strtotime($evolucao->data_pedido);
				$dif = floor($dif/(60 * 60 * 24));
				$alertaMaisDe8Dias=($dif>=8)?1:0;

				if(isset($_pacientes[$evolucao->id_paciente])) {
					$paciente=$_pacientes[$evolucao->id_paciente];
					$clinica = isset($_clinicas[$evolucao->id_clinica]) ? encodingToJson($_clinicas[$evolucao->id_clinica]->razao_social) : '';

					$pedidos['aguardando'][]=array('id_evolucao'=>$evolucao->id,
													'id_evolucao_pedidodeexame'=>$x[0]->id,
													'data'=>date('d/m/Y',strtotime($evolucao->data_pedido)),
													'paciente'=>encodingToJson($paciente->nome),
																'exames'=>count($x),
													'alerta'=>$alertaMaisDe8Dias,
													'clinica'=>$clinica);
				}
			}
		}
	}	




	if(isset($_pedidosDeExames['concluido'])) {
		foreach($_pedidosDeExames['concluido'] as $id_evolucao=>$x) {
			if(isset($_evolucoes[$id_evolucao])) {
				$evolucao=$_evolucoes[$id_evolucao];
				if(isset($_pacientes[$evolucao->id_paciente])) {
					$paciente=$_pacientes[$evolucao->id_paciente];
					$clinica = isset($_clinicas[$evolucao->id_clinica]) ? encodingToJson($_clinicas[$evolucao->id_clinica]->razao_social) : '';


					$pedidos['concluido'][]=array('id_evolucao'=>$evolucao->id,
													'id_evolucao_pedidodeexame'=>$x[0]->id,
													'data'=>date('d/m/Y',strtotime($evolucao->data_pedido)),
													'paciente'=>encodingToJson($paciente->nome),
																'exames'=>count($x),
													'clinica'=>$clinica);
				}
			}
		}
	}

	if(isset($_pedidosDeExames['naoRealizado'])) {
		foreach($_pedidosDeExames['naoRealizado'] as $id_evolucao=>$x) {
			if(isset($_evolucoes[$id_evolucao])) {
				$evolucao=$_evolucoes[$id_evolucao];
				if(isset($_pacientes[$evolucao->id_paciente])) {
					$paciente=$_pacientes[$evolucao->id_paciente];
					$clinica = isset($_clinicas[$evolucao->id_clinica]) ? encodingToJson($_clinicas[$evolucao->id_clinica]->razao_social) : '';
					$pedidos['naoRealizado'][]=array('id_evolucao'=>$evolucao->id,
													'id_evolucao_pedidodeexame'=>$x[0]->id,
													'data'=>date('d/m/Y',strtotime($evolucao->data_pedido)),
													'paciente'=>encodingToJson($paciente->nome),
																'exames'=>count($x),
													'clinica'=>$clinica);
				}
			}
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

	<script type="text/javascript">
		
		var pedidosAguardando = JSON.parse(`<?php echo json_encode($pedidos['aguardando']);?>`);
		var pedidosConcluido = JSON.parse(`<?php echo json_encode($pedidos['concluido']);?>`);
		var pedidosNaoRealizado = JSON.parse(`<?php echo json_encode($pedidos['naoRealizado']);?>`);

		const pedidosListar = () => {
			
			$('.js-kanban-aguardando').html('');
			pedidosAguardando.forEach(x=>{

				alerta='';
				if(x.alerta==1) {
					alerta=' <span class="iconify" data-icon="akar-icons:triangle-alert-fill" style="color:var(--vermelho)"></span>'
				}
				$('.js-kanban-aguardando').append(`<a href="javascript:;" class="js-exame-item" data-id_evolucao_pedidodeexame="${x.id_evolucao_pedidodeexame}" style="padding:  0 0 0 0;display: flex;flex-direction: row;">
														<img src="img/ilustra-usuario.jpg" style="display: block;width: 70px;height: 70px;border-radius: 4px 0 0 4px;" />
														<div style="padding-top:7px;">
															<h1>${x.paciente}${alerta}</h1>
															<p>${x.data}</p>
															<p>${x.clinica}</p>
															<p>Exames: ${x.exames}</p>
														</div>
													</a>`);
			});

			$('.js-kanban-concluido').html('');
			pedidosConcluido.forEach(x=>{

				$('.js-kanban-concluido').append(`<a href="javascript:;" class="js-exame-item" data-id_evolucao_pedidodeexame="${x.id_evolucao_pedidodeexame}" style="padding:  0 0 0 0;display: flex;flex-direction: row;">
														<img src="img/ilustra-usuario.jpg" style="display: block;width: 70px;height: 70px;border-radius: 4px 0 0 4px;" />
														<div style="padding-top:7px;">
															<h1>${x.paciente}</h1>
															<p>${x.data}</p>
															<p>${x.clinica}</p>
															<p>Exames: ${x.exames}</p>
														</div>
													</a>`);
			});

			$('.js-kanban-naoRealizado').html('');
			pedidosNaoRealizado.forEach(x=>{

				$('.js-kanban-naoRealizado').append(`<a href="javascript:;" class="js-exame-item" data-id_evolucao_pedidodeexame="${x.id_evolucao_pedidodeexame}" style="padding:  0 0 0 0;display: flex;flex-direction: row;">
														<img src="img/ilustra-usuario.jpg" style="display: block;width: 70px;height: 70px;border-radius: 4px 0 0 4px;" />
														<div style="padding-top:7px;">
															<h1>${x.paciente}</h1>
															<p>${x.data}</p>
															<p>${x.clinica}</p>
															<p>Exames: ${x.exames}</p>
														</div>
													</a>`);
			});
		}
			
		

		$(function(){
			pedidosListar();
		})

	</script>

	<main class="main">
		<div class="main__content content">

			

			<section class="grid" style="flex:1;margin-top:40px;">
				<div class="kanban" id="kanban" style="   grid-template-columns: repeat(3,minmax(0,1fr))">
					
					
					<div class="kanban-item" style="background:var(--cinza5)">
						<header>
							<h1 class="kanban-item__titulo">Aguardando Exame (<?php echo count($_pedidosDeExames['aguardando']);?>)</h1>
						</header>
						<article class="kanban-card js-kanban-aguardando" style="min-height: 200px;">
							
						</article>
					</div>


					<div class="kanban-item" style="background:var(--cinza5)">
						<header>
							<h1 class="kanban-item__titulo">Concluído (<?php echo count($_pedidosDeExames['concluido']);?>)</h1>
						</header>
						<article class="kanban-card js-kanban-concluido" style="min-height: 100px;">
							
						</article>
					</div>

					<div class="kanban-item" style="background:var(--cinza5)">
						<header>
							<h1 class="kanban-item__titulo">Exame não Realizado (<?php echo count($_pedidosDeExames['naoRealizado']);?>)</h1>

						</header>

						<article class="kanban-card js-kanban-naoRealizado" style="min-height: 100px;">
							
						</article>
					</div>

					
					
				</div>
			</section>

		</div>
	</main>

<?php 
	

	require_once("includes/api/apiAsideInteligenciaExames.php");


	include "includes/footer.php";
?>	