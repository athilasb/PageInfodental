<?php
	include "includes/header.php";
	include "includes/nav.php";
	if($usr->tipo!="admin" and !in_array("inteligencia",$_usuariosPermissoes)) {
		$jsc->jAlert("Você não tem permissão para acessar esta área!","erro","document.location.href='dashboard.php'");
		die();
	}

	$_clinicas=array();
	$sql->consult($_p."parametros_fornecedores","*","");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_clinicas[$x->id]=$x;
	}


	$attr=array('_cloudinaryURL'=>$_cloudinaryURL,
				'prefixo'=>$_p,
				'_wasabiURL'=>$_wasabiURL);
	$inteligencia = new Inteligencia($attr);

	$inteligencia->controleDeExames();
	$pedidos = $inteligencia->pedidos;
	$_pedidosDeExames = $inteligencia->_pedidosDeExames;
							

	
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
		var pedidosAguardandoQtd = <?php echo count($_pedidosDeExames['aguardando']);?>;
		var pedidosConcluidoQtd = <?php echo count($_pedidosDeExames['concluido']);?>;
		var pedidosNaoRealizadoQtd = <?php echo count($_pedidosDeExames['naoRealizado']);?>;

		const pedidosListar = () => {
			

			$('#pedidos-aguardando').html(pedidosAguardandoQtd);
			$('#pedidos-concluido').html(pedidosConcluidoQtd);
			$('#pedidos-naoRealizado').html(pedidosNaoRealizadoQtd);

			$('.js-kanban-aguardando').html('');
			pedidosAguardando.forEach(x=>{

				foto = 'img/ilustra-usuario.jpg';
				if(x.ft.length>0) foto=x.ft;

				alerta='';
				if(x.alerta==1) {
					alerta=' <span class="iconify" data-icon="akar-icons:triangle-alert-fill" style="color:var(--vermelho)"></span>'
				}

				anexos='';
				if(x.anexos>0) {
					anexos=`<p><span class="iconify" data-icon="fluent:attach-12-filled" data-inline="true" data-height="12"></span> ${x.anexos}</p>`;
				}


				$('.js-kanban-aguardando').append(`<a href="javascript:;" class="js-exame-item" data-id_evolucao_pedidodeexame="${x.id_evolucao_pedidodeexame}" style="padding:  0 0 0 0;display: flex;flex-direction: row;">
														<img src="${foto}" style="display: block;width: 70px;height: 70px;border-radius: 4px 0 0 4px;" />
														<div style="padding-top:7px;">
															<h1>${x.paciente}${alerta}</h1>
															<p>${x.data}</p>
															<p>${x.clinica}</p>
															<p>Exames: ${x.exames}</p>
															${anexos}
														</div>
													</a>`);
			});

			$('.js-kanban-concluido').html('');
			pedidosConcluido.forEach(x=>{

				foto = 'img/ilustra-usuario.jpg';
				if(x.ft.length>0) foto=x.ft;

				let agendamentoFuturo = '';
				if(x.agendamentoFuturo.length>0) {
					agendamentoFuturo=`<p style="color:var(--cinza5);"><span class="iconify" data-icon="akar-icons:calendar"></span> ${x.agendamentoFuturo}</p>`;
				}
				anexos='';
				if(x.anexos>0) {
					anexos=`<p><span class="iconify" data-icon="fluent:attach-12-filled" data-inline="true" data-height="12"></span> ${x.anexos}</p>`;
				}

				$('.js-kanban-concluido').append(`<a href="javascript:;" class="js-exame-item" data-id_evolucao_pedidodeexame="${x.id_evolucao_pedidodeexame}" style="padding:  0 0 0 0;display: flex;flex-direction: row;">
														<img src="${foto}" style="display: block;width: 70px;height: 70px;border-radius: 4px 0 0 4px;" />
														<div style="padding-top:7px;">
															<h1>${x.paciente}</h1>
															<p>${x.data}</p>
															<p>${x.clinica}</p>
															<p>Exames: ${x.exames}</p>
															${agendamentoFuturo}
															${anexos}
														</div>
													</a>`);
			});

			$('.js-kanban-naoRealizado').html('');
			pedidosNaoRealizado.forEach(x=>{

				foto = 'img/ilustra-usuario.jpg';
				if(x.ft.length>0) foto=x.ft;

				anexos='';
				if(x.anexos>0) {
					anexos=`<p><span class="iconify" data-icon="fluent:attach-12-filled" data-inline="true" data-height="12"></span> ${x.anexos}</p>`;
				}

				$('.js-kanban-naoRealizado').append(`<a href="javascript:;" class="js-exame-item" data-id_evolucao_pedidodeexame="${x.id_evolucao_pedidodeexame}" style="padding:  0 0 0 0;display: flex;flex-direction: row;">
														<img src="${foto}" style="display: block;width: 70px;height: 70px;border-radius: 4px 0 0 4px;" />
														<div style="padding-top:7px;">
															<h1>${x.paciente}</h1>
															<p>${x.data}</p>
															<p>${x.clinica}</p>
															<p>Exames: ${x.exames}</p>
															${anexos}
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
				<div class="kanban" id="kanban" style="grid-template-columns: repeat(3,minmax(0,1fr))">
					
					
					<div class="kanban-item" style="background:var(--cinza5)">
						<header>
							<h1 class="kanban-item__titulo">Aguardando Exame (<span id="pedidos-aguardando">0</span>)</h1>
						</header>
						<article class="kanban-card js-kanban-aguardando" style="min-height: 200px;">
							
						</article>
					</div>


					<div class="kanban-item" style="background:var(--cinza5)">
						<header>
							<h1 class="kanban-item__titulo">Concluído (<span id="pedidos-concluido">0</span>)</h1>
						</header>
						<article class="kanban-card js-kanban-concluido" style="min-height: 100px;">
							
						</article>
					</div>

					<div class="kanban-item" style="background:var(--cinza5)">
						<header>
							<h1 class="kanban-item__titulo">Cancelado (<span id="pedidos-naoRealizado">0</span>)</h1>

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