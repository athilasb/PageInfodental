<?php
	include "includes/header.php";
	include "includes/nav.php";

	$_table=$_p."pacientes_prontuarios";
	require_once("includes/header/headerPacientes.php");

	$_usuarios=array();
	$sql->consult($_p."colaboradores","id,nome","");
	while($x=mysqli_fetch_object($sql->mysqry)) $_usuarios[$x->id]=$x;

	$evolucoes=array();
	$evolucoesIds=array();
	$sql->consult($_p."pacientes_evolucoes","*","where id_paciente=$paciente->id order by data desc");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$evolucoes[]=$x;
		$evolucoesIds[$x->id_tipo][]=$x->id;
	}

	$evolucoesTipos=array();
	$sql->consult($_p."pacientes_evolucoes_tipos","*","");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$evolucoesTipos[$x->id]=$x;
	}

	$_profissionais=array();
	$sql->consult($_p."colaboradores","id,nome","");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_profissionais[$x->id]=$x;
	}

	// geral
		$_geral=array();
		if(isset($evolucoesIds[9])) {
			$sql->consult($_p."pacientes_evolucoes_geral","*","where id_evolucao IN (".implode(",",$evolucoesIds[9]).")");
			if($sql->rows) {
				while($x=mysqli_fetch_object($sql->mysqry)) {
					$_geral[$x->id_evolucao]=$x;
				}
			}
		}


	// anamnese
		$_anamnesePerguntas=array();
		if(isset($evolucoesIds[1])) {
			$sql->consult($_p."pacientes_evolucoes_anamnese","*","where id_evolucao IN (".implode(",",$evolucoesIds[1]).")");
			if($sql->rows) {
				while($x=mysqli_fetch_object($sql->mysqry)) {
					$_anamnesePerguntas[$x->id_evolucao][]=$x;
				}
			}
		}

	// receituario
		$_medicamentosReceituario=array();
		if(isset($evolucoesIds[7])) {
			$sql->consult($_p."pacientes_evolucoes_receitas","*","where id_evolucao IN (".implode(",",$evolucoesIds[7]).")");
			if($sql->rows) {
				while($x=mysqli_fetch_object($sql->mysqry)) {
					$_medicamentosReceituario[$x->id_evolucao][]=$x;
				}
			}
		}

	// pedido de exames
		$_pedidosDeExames=array();
		if(isset($evolucoesIds[6])) {
			$sql->consult($_p."pacientes_evolucoes_pedidosdeexames","*","where id_evolucao IN (".implode(",",$evolucoesIds[6]).")");
			if($sql->rows) {
				while($x=mysqli_fetch_object($sql->mysqry)) {
					$_pedidosDeExames[$x->id_evolucao][]=$x;
				}
			}
		}

		$_exames=array();
		$sql->consult($_p."parametros_examedeimagem","id,titulo","");
		while($x=mysqli_fetch_object($sql->mysqry)) {
			$_exames[$x->id]=$x;
		}

		$_clinicas=array();
		$sql->consult($_p."parametros_fornecedores","id,IF(tipo_pessoa='PF',nome,razao_social) as titulo","");
		while($x=mysqli_fetch_object($sql->mysqry)) {
			$_clinicas[$x->id]=$x;
		}

	// atestado
		$_atestados=array();
		if(isset($evolucoesIds[4])) {
			$sql->consult($_p."pacientes_evolucoes_atestados","*","where id_evolucao IN (".implode(",",$evolucoesIds[4]).")");
			if($sql->rows) {
				while($x=mysqli_fetch_object($sql->mysqry)) {
					$_atestados[$x->id_evolucao]=$x;
				}
			}
		}

		$_atestadosTipos=array();
		$sql->consult("infodentalADM.infod_parametros_atestados_tipos","*","where lixo=0 order by titulo asc") ;
		while($x=mysqli_fetch_object($sql->mysqry)) {
			$_atestadosTipos[$x->id]=$x;
		}

		$_atestadosFins=array();
		$sql->consult("infodentalADM.infod_parametros_atestados_fins","*","where lixo=0 order by titulo asc") ;
		while($x=mysqli_fetch_object($sql->mysqry)) {
			$_atestadosFins[$x->id]=$x;
		}
?>


	<script type="text/javascript">
		var id_paciente = <?php echo $paciente->id;?>; 
	</script>

	<main class="main">
		<div class="main__content content">

			<section class="filter">
				<div class="filter-group">
					<div class="filter-form form">
						<dl>
							<dd><a href="javascript:;" data-aside="prontuario-opcoes" class="button button_main"><i class="iconify" data-icon="fluent:add-circle-24-regular"></i><span>Adicionar Evolução</span></a>
						</dl>
					</div>
				</div>
			</section>

			<div class="box">
				<div class="list-toggle">

					<script>
						$(function() {
							$('.js-expande').click(function() {
								$(this).parent().parent().next('article').toggleClass('active');
								$(this).toggleClass('button-reverse');
							});
						});
					</script>

					<?php
					foreach($evolucoes as $e) {
						if(isset($evolucoesTipos[$e->id_tipo])) {
							$eTipo=$evolucoesTipos[$e->id_tipo];
					?>
					<div class="list-toggle-item">
						<header>
							<div class="list-toggle-cat">
								<i class="iconify" data-icon="<?php echo $eTipo->icone;?>"></i>
								<div>
									<h1><?php echo utf8_encode($eTipo->titulo);?></h1>
									<p><?php echo date('d/m/Y H:i',strtotime($e->data));?></p>
								</div>
							</div>
							<p><?php echo isset($_profissionais[$e->id_profissional])?utf8_encode($_profissionais[$e->id_profissional]->nome):"Desconhecido";?></p>
							<div class="list-toggle-buttons">									
								<?php /*<a href="" class="button"><i class="iconify" data-icon="fluent:edit-24-regular"></i></a>*/?>
								<a href="" class="button"><i class="iconify" data-icon="fluent:delete-24-regular"></i></a>
								<a href="javascript:;" class="button button_main js-expande"><i class="iconify" data-icon="fluent:chevron-down-24-regular"></i></a>
							</div>							
						</header>
						<article>
							<?php
								$correcoes='';

								// anamnese
								if($eTipo->id==1) {
									$correcoes=1;
									if(isset($_anamnesePerguntas[$e->id])) {
										$perguntas=$_anamnesePerguntas[$e->id];


									?>
									<div class="list-toggle-topics">
									<?php
										foreach($perguntas as $p) {
											$pergunta=json_decode($p->json_pergunta);


									?>
										<div class="list-toggle-topic">
											<h1><?php echo ($pergunta->pergunta);?></h1>
											<p>
												<?php 
												if($pergunta->tipo=="simnao" or $pergunta->tipo=="simnaotexto") {
													if($p->resposta=="SIM") echo "Sim";
													else echo "Não";
												} else if($pergunta->tipo=="nota") {
													echo "Nota: ".$p->resposta;
												} 
												?>	
											</p>
											<?php
											if(!empty($p->resposta_texto)) {
												echo "<p>Resposta: ".utf8_encode($p->resposta_texto)."</p>";
											}
											?>
										</div>
									<?php
										}
									?>
									
									</div>		
									<?php
									}
								}
								// atestado
								else if($eTipo->id==4) {
									if(isset($_atestados[$e->id])) {
										$atestado=$_atestados[$e->id];
									?>
									<div class="list-toggle-topics">
										<div class="list-toggle-topic">
											<h1>Tipo de atestado</h1>
											<p><?php echo utf8_encode($atestado->tipo);?></p>
										</div>
										<div class="list-toggle-topic">
											<h1>Fim do Atestado</h1>
											<p><?php echo isset($_atestadosFins[$atestado->id_fim])?utf8_encode($_atestadosFins[$atestado->id_fim]->titulo):'-';?></p>
										</div>
										<div class="list-toggle-topic">
											<h1>Duração do Atestado</h1>
											<p><?php echo utf8_encode($atestado->duracao);?> dia(s)</p>
										</div>
									</div>		
									<?php
									}
								}

								// pedidos de exame
								else if($eTipo->id==6) {

									if(isset($_pedidosDeExames[$e->id])) {
										$pedidos=$_pedidosDeExames[$e->id];
										
									?>
									<div class="list-toggle-topics">
										<div class="list-toggle-topic">
											<h1><?php echo isset($_clinicas[$e->id_clinica])?utf8_encode($_clinicas[$e->id_clinica]->titulo):"Clínica Desconhecida";?></h1>
											
										</div>
										<div class="list-toggle-topic">
											<h1>Exames:</h1>
											<?php
											foreach($pedidos as $p) {
												if(isset($_exames[$p->id_exame])) {
													$exame=$_exames[$p->id_exame];
													$regiao=' - GERAL';
													if(isset($p->opcao) and !empty($p->opcao)) {
														$opcoes=explode(",",utf8_encode($p->opcao));
														$regiao=' -';
														foreach($opcoes as $opcao) {
															$regiao.=" ".$opcao.", ";
														}
														$regiao=substr($regiao,0,strlen($regiao)-2);
													}
													$obs=!empty($p->obs)?" - ".utf8_encode($p->obs):"";

											?>
											<p><?php echo utf8_encode($exame->titulo).$regiao.$obs;?></p>
											<?php
												}
											}
											?>
										</div>
									</div>		
									<?php
									}
								}

								// receituario
								else if($eTipo->id==7) {

									if(isset($_medicamentosReceituario[$e->id])) {
										$medicamentos=$_medicamentosReceituario[$e->id];
											
												
									?>
									<div class="list-toggle-topics">
										<div class="list-toggle-topic">
											<h1>Tipo de Uso</h1>
											<p><?php echo isset($_tiposReceitas[$e->tipo_receita])?$_tiposReceitas[$e->tipo_receita]:"-";?></p>
										</div>
										<div class="list-toggle-topic">
											<h1>Medicamentos</h1>
										<?php
										foreach($medicamentos as $m) {
										?>
											<p><?php echo utf8_encode($m->medicamento)." - ".$m->quantidade." ".$_medicamentosTipos[$m->tipo]." - ".utf8_encode($m->posologia);?></p>
										<?php
										}
										?>
										</div>
										
									</div>			
									<?php
									}
								}

								// receituario
								else if($eTipo->id==9) {

									if(isset($_geral[$e->id])) {
										$g=$_geral[$e->id];
									?>
									<div class="list-toggle-topics">
										<div class="list-toggle-topic">
										
											<p><?php echo utf8_encode($g->texto);?></p>
										
										</div>
										
									</div>			
									<?php
									}
								}


								if($correcoes!="") {
							?>

							<div class="list-toggle-com">
								<header>
									<h1>Correções</h1>
								</header>
								<article>
									<header>
										<p>Kroner Machado Costa</p>
										<p>12/08/2022 14:34</p>
									</header>
									<article>
										<p>Lorem, ipsum dolor sit amet consectetur adipisicing, elit. Atque voluptates, enim laudantium quis perferendis exercitationem tempora mollitia, ipsum nisi modi!</p>
									</article>
								</article>
								<article>
									<header>
										<p>Kroner Machado Costa</p>
										<p>12/08/2022 14:34</p>
									</header>
									<article>
										<p>Lorem, ipsum dolor sit amet consectetur adipisicing, elit. Atque voluptates, enim laudantium quis perferendis exercitationem tempora mollitia, ipsum nisi modi!</p>
									</article>
								</article>
							</div>	
							<?php
							}
							?>						
						</article>
					</div>
					<?php
							
						}
					}
					/*?>

					<div class="list-toggle-item">
						<header>
							<div class="list-toggle-cat">
								<i class="iconify" data-icon="mdi-file-document-outline"></i>
								<div>
									<h1>Atestado</h1>
									<p>26/07/2022 - 10:33</p>
								</div>
							</div>
							<p>Kroner Machado Costa</p>
							<div class="list-toggle-buttons">									
								<a href="" class="button"><i class="iconify" data-icon="fluent:edit-24-regular"></i></a>
								<a href="" class="button"><i class="iconify" data-icon="fluent:delete-24-regular"></i></a>
								<a href="javascript:;" class="button button_main js-expande"><i class="iconify" data-icon="fluent:chevron-down-24-regular"></i></a>
							</div>							
						</header>
						<article>
							<div class="list-toggle-topics">
								<div class="list-toggle-topic">
									<h1>Tipo de atestado</h1>
									<p>Acompanhamento</p>
								</div>
								<div class="list-toggle-topic">
									<h1>Fim do Atestado</h1>
									<p>Escolar</p>
								</div>
								<div class="list-toggle-topic">
									<h1>Duração do Atestado</h1>
									<p>60 mins</p>
								</div>
							</div>								
							<div class="list-toggle-com">
								<header>
									<h1>Correções</h1>
								</header>								
							</div>							
						</article>
					</div>

					<div class="list-toggle-item">
						<header>
							<div class="list-toggle-cat">
								<i class="iconify" data-icon="carbon-user-x-ray"></i>
								<div>
									<h1>Pedido de Exame</h1>
									<p>26/07/2022 - 10:33</p>
								</div>
							</div>
							<p>Kroner Machado Costa</p>
							<div class="list-toggle-buttons">									
								<a href="" class="button"><i class="iconify" data-icon="fluent:edit-24-regular"></i></a>
								<a href="" class="button"><i class="iconify" data-icon="fluent:delete-24-regular"></i></a>
								<a href="javascript:;" class="button button_main js-expande"><i class="iconify" data-icon="fluent:chevron-down-24-regular"></i></a>
							</div>							
						</header>
						<article>
							<div class="list-toggle-topics">
								<div class="list-toggle-topic">
									<h1>Clínica Radiológica</h1>
									<p>Imagem Dental</p>
								</div>
								<div class="list-toggle-topic">
									<h1>Exames:</h1>
									<p>Cefalometria - GERAL</p>
									<p>Fotos Intra e Extra Oral - Por arcada - Maxila - Pedir urgência</p>
								</div>
							</div>								
							<div class="list-toggle-com">
								<header>
									<h1>Correções</h1>
								</header>								
							</div>							
						</article>
					</div>

					<div class="list-toggle-item">
						<header>
							<div class="list-toggle-cat">
								<i class="iconify" data-icon="mdi-pill"></i>
								<div>
									<h1>Receituário</h1>
									<p>26/07/2022 - 10:33</p>
								</div>
							</div>
							<p>Kroner Machado Costa</p>
							<div class="list-toggle-buttons">									
								<a href="" class="button"><i class="iconify" data-icon="fluent:edit-24-regular"></i></a>
								<a href="" class="button"><i class="iconify" data-icon="fluent:delete-24-regular"></i></a>
								<a href="javascript:;" class="button button_main js-expande"><i class="iconify" data-icon="fluent:chevron-down-24-regular"></i></a>
							</div>							
						</header>
						<article>
							<div class="list-toggle-topics">
								<div class="list-toggle-topic">
									<h1>Tipo de Uso</h1>
									<p>Comprimido</p>
								</div>
								<div class="list-toggle-topic">
									<h1>Tipo de Uso</h1>
									<p>Comprimido</p>
								</div>
								<div class="list-toggle-topic">
									<h1>Medicamentos</h1>
									<p>Azitromicina - 2 caixas - Tomar a cada 10 minutos</p>
									<p>Amoxilina com Clavulanato 250gm - 1 caixa - Tomar a cada 8 horas</p>
								</div>
								
							</div>								
							<div class="list-toggle-com">
								<header>
									<h1>Correções</h1>
								</header>								
							</div>							
						</article>
					</div>*/?>

				</div>				
			</div>
			
		</div>
	</main>

<?php 
			

	$apiConfig=array('geral'=>1,
						'anamnese'=>1,
						'atestado'=>1,
						'pedidoExame'=>1,
						'receituario'=>1);
	require_once("includes/api/apiAsidePaciente.php");

	include "includes/footer.php";
?>	