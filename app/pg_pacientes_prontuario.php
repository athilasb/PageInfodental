<?php
	if(isset($_POST['ajax'])) {
		require_once("lib/conf.php");	
		require_once("usuarios/checa.php");


		$paciente = '';
		if(isset($_POST['id_paciente']) and is_numeric($_POST['id_paciente'])) {
			$sql->consult($_p."pacientes","id,nome","where id=".$_POST['id_paciente']);
			if($sql->rows) {
				$paciente=mysqli_fetch_object($sql->mysqry);
			}
		}

		$evolucao = '';
		if(is_object($paciente) and isset($_POST['id_evolucao']) and is_numeric($_POST['id_evolucao'])) {
			$sql->consult($_p."pacientes_evolucoes","id,data","where id=".$_POST['id_evolucao']." and id_paciente=$paciente->id and lixo=0");
			if($sql->rows) {
				$evolucao=mysqli_fetch_object($sql->mysqry);
			}
		}


		$rtn = array();

		if($_POST['ajax']=="evolucaoErrataPersisitr") {

			$texto = (isset($_POST['texto']) and !empty($_POST['texto']))?$_POST['texto']:'';

			$erro='';
			if(empty($paciente)) $erro='Paciente não encontrado!';
			else if(empty($evolucao)) $erro='Evolução não encontrada!';
			else if(empty($texto)) $erro='Preencha o campo da errata!';

			if(empty($erro)) {

				$vsql="data=now(),
						id_usuario=$usr->id,
						id_evolucao=$evolucao->id,
						id_paciente=$paciente->id,
						texto='".addslashes(utf8_decode($texto))."'";

				$sql->add($_p."pacientes_evolucoes_erratas",$vsql);

				$rtn=array('success'=>true);

			} else {
				$rtn=array('success'=>false,'error'=>$erro);
			}

		}
		else if($_POST['ajax']=="evolucaoErrataListar") {

			$_profissionais=array();
			$sql->consult($_p."colaboradores","id,nome","");
			while($x=mysqli_fetch_object($sql->mysqry)) {
				$_profissionais[$x->id]=$x;
			}

			$erratas=array();
			if(is_object($paciente) and is_object($evolucao)) {
				$sql->consult($_p."pacientes_evolucoes_erratas","*","where id_evolucao=$evolucao->id and id_paciente=$paciente->id and lixo=0 order by data desc");
				if($sql->rows){
					while($x=mysqli_fetch_object($sql->mysqry)) {
						$erratas[]=array('id'=>$x->id,
										'id_evolucao'=>$x->id_evolucao,
										'data'=>date('d/m/Y H:i',strtotime($x->data)),
										'profissional'=>isset($_profissionais[$x->id_usuario])?utf8_encode($_profissionais[$x->id_usuario]->nome):'',
										'texto'=>utf8_encode($x->texto));
					}
				}
			}

			$rtn=array('success'=>true,'erratas'=>$erratas,'id_evolucao'=>is_object($evolucao)?$evolucao->id:0);
		}

		else if($_POST['ajax']=="evolucaoProcedimentosHistorico") {


			$procedimentoAEvoluir = $procedimento = '';
			if(isset($_POST['id_procedimento_aevoluir']) and is_numeric($_POST['id_procedimento_aevoluir'])) {
				$sql->consult($_p."pacientes_tratamentos_procedimentos_evolucao","*","where id=".$_POST['id_procedimento_aevoluir']);
				if($sql->rows) {
					$procedimentoEvoluir=mysqli_fetch_object($sql->mysqry);

					$sql->consult($_p."parametros_procedimentos","*","where id=$procedimentoEvoluir->id_procedimento");
					if($sql->rows) $procedimento=mysqli_fetch_object($sql->mysqry);
					
				}
			}

			$erro='';
			if(empty($evolucao)) $erro='Evolução não encontrada!';
			else if(empty($procedimentoEvoluir)) $erro='Procedimento evoluído não encontrada!';

			if(empty($erro)) {

				$historico=array();
				$sql->consult($_p."pacientes_tratamentos_procedimentos_evolucao_historico","*","where id_evolucao=$evolucao->id and id_procedimento_aevoluir=$procedimentoEvoluir->id");
				while($x=mysqli_fetch_object($sql->mysqry)) {
					$historico[]=array('id'=>$x->id,
										'data'=>date('d/m/Y H:i',strtotime($x->data)),
										'usuario'=>encodingToJson($x->usuario),
										'obs'=>encodingToJson($x->obs));
				}

				$rtn=array('success'=>true,
							'dataEvolucao'=>date('d/m/Y H:i',strtotime($evolucao->data)),
							'status'=>$procedimentoEvoluir->status_evolucao,
							'procedimento'=>encodingToJson($procedimento->titulo),
							'historico'=>$historico);

			} else {
				$rtn=array('success'=>false,'error'=>$erro);
			}
		}

		header("Content-type: application/json");
		echo json_encode($rtn);
		die();
	}
	include "includes/header.php";
	include "includes/nav.php";


	$lstAssinP = array();
	$sql->consult($_p."pacientes_assinaturas", "id_evolucao", "");
	while(($x = mysqli_fetch_object($sql->mysqry))){
		$lstAssinP[] = $x->id_evolucao; 
	}


	$_table=$_p."pacientes_prontuarios";
	require_once("includes/header/headerPacientes.php");
	
	// remove evolução
	if(isset($_GET['deleta']) and is_numeric($_GET['deleta'])) {
		$sql->update($_p."pacientes_evolucoes","lixo=1","where id=".$_GET['deleta']." and id_paciente=$paciente->id");
		
	}

	$_usuarios=array();
	$sql->consult($_p."colaboradores","id,nome","");
	while($x=mysqli_fetch_object($sql->mysqry)) $_usuarios[$x->id]=$x;

	$evolucoes=array();
	$evolucoesIds=array();
	$evolucoesTodosIds=array();
	//$sql->consult($_p."pacientes_evolucoes","*","where id_paciente=$paciente->id order by data desc");
	$sql->consultPagMto2($_p."pacientes_evolucoes","*",10,"where id_paciente=$paciente->id and lixo=0 order by data desc","",15,"pagina",$_page."?".$url."&pagina=");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$evolucoes[]=$x;
		$evolucoesIds[$x->id_tipo][]=$x->id;
		$evolucoesTodosIds[]=$x->id;
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

	// erratas
		$_erratas=array();
		if(count($evolucoesTodosIds)>0) {
			$sql->consult($_p."pacientes_evolucoes_erratas","*","where id_evolucao IN (".implode(",",$evolucoesTodosIds).") and id_paciente=$paciente->id order by data desc");
			if($sql->rows) {
				while($x=mysqli_fetch_object($sql->mysqry)) {
					$_erratas[$x->id_evolucao][]=$x;
				}
			}
		}

	// documentos
		$_documentosTipos=array();
		$sql->consult($_p."parametros_documentos","*","order by titulo");
		if($sql->rows) {
			while($x=mysqli_fetch_object($sql->mysqry)) {
				$_documentosTipos[$x->id]=$x;
			}
		}

		$_documentos=array();
		if(isset($evolucoesIds[10])) {
			$sql->consult($_p."pacientes_evolucoes_documentos","*","where id_evolucao IN (".implode(",",$evolucoesIds[10]).")");
			if($sql->rows) {
				while($x=mysqli_fetch_object($sql->mysqry)) {
					$_documentos[$x->id_evolucao]=$x;
				}
			}
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

	// alta
		$_alta=array();
		if(isset($evolucoesIds[11])) {
			$sql->consult($_p."pacientes_evolucoes_alta","*","where id_evolucao IN (".implode(",",$evolucoesIds[11]).")");
			if($sql->rows) {
				while($x=mysqli_fetch_object($sql->mysqry)) {
					$_alta[$x->id_evolucao]=$x;
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
		$sql->consult($_p."parametros_fornecedores","id,IF(tipo_pessoa='PF',nome,nome_fantasia) as titulo","");
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

	// procedimentos
		$_procedimentosEvoluidos=$_procedimentosEvoluidosProcedimentos=array();
		if(isset($evolucoesIds[2])) {
			$procedimentosIds=array();
			$sql->consult($_p."pacientes_evolucoes_procedimentos","*","where id_evolucao IN (".implode(",",$evolucoesIds[2]).")");
			if($sql->rows) {
				while($x=mysqli_fetch_object($sql->mysqry)) {
					$_procedimentosEvoluidos[$x->id_evolucao][]=$x;
					$procedimentosIds[]=$x->id_procedimento;
				}
			}


			// historicos escritos ao criar evolucao
			$_procedimentosHistorico=array();
			$sql->consult($_p."pacientes_tratamentos_procedimentos_evolucao_historico","*","where id_evolucao IN (".implode(",",$evolucoesIds[2]).") and tipo_alterouStatus=0 and lixo=0");
			if($sql->rows) {
				while($x=mysqli_fetch_object($sql->mysqry)) {
					$_procedimentosHistorico[$x->id_evolucao][$x->id_procedimento_aevoluir][]=$x;
				}
			}

			if(count($procedimentosIds)>0) {
				$sql->consult($_p."parametros_procedimentos","id,titulo","where id IN (".implode(",",$procedimentosIds).")");
				while($x=mysqli_fetch_object($sql->mysqry)) {
					$_procedimentosEvoluidosProcedimentos[$x->id]=$x;
				}
			}
		}

?>


	<script type="text/javascript">
		id_paciente = <?php echo $paciente->id;?>; 
		var pagina = <?php echo (isset($_GET['pagina']) and is_numeric($_GET['pagina']))?$_GET['pagina']:0;?>; 
	</script>




	<main class="main">
		<div class="main__content content">

			<section class="filter">
				<div class="filter-group">
					<div class="filter-title">
						<h1>Ficha do Paciente</h1>
					</div>
				</div>
			</section>

			<section class="grid">

				<div class="box box-col">

					<?php 
					require_once("includes/submenus/subPacientesFichaDoPaciente.php");
					?>

					<div class="box-col__inner1">
				
						<section class="filter">
							<div class="filter-group"></div>
							<div class="filter-group">
								<div class="filter-form form">
									<dl>
										<dd>
											<a href="javascript:;" data-aside="prontuario-opcoes" class="button button_main">
												<i class="iconify" data-icon="fluent:add-circle-24-regular"></i><span>Adicionar Evolução</span>
											</a>
										</dd>
									</dl>
								</div>
							</div>							
						</section>

						<div class="box">
							<div class="list-toggle">
								<?php
								foreach($evolucoes as $e) {
									if(isset($evolucoesTipos[$e->id_tipo])) {
										$eTipo=$evolucoesTipos[$e->id_tipo];


										$pdf="javascript:;";

										// anamnese
										if($e->id_tipo==1) {
											$pdf="impressao/anamnese.php?id=".md5($e->id);
										} 
										// atestado
										else if($e->id_tipo==4) {
											$pdf="impressao/atestado.php?id=".md5($e->id);
										} 
										// pedido de exame
										else if($e->id_tipo==6) {
											$pdf="impressao/pedidodeexame.php?id=".md5($e->id);
										} 
										// receituario
										else if($e->id_tipo==7) {
											$pdf="impressao/receituario.php?id=".md5($e->id);
										}
										// receituario
										else if($e->id_tipo==10) {
											$pdf="impressao/documentos.php?id=".md5($e->id);
										}
								?>
								<div class="list-toggle-item">

									<header>													
										<div class="list-toggle-cat">
											<i class="iconify" data-icon="<?php echo $eTipo->icone;?>"></i>
											<div>
												<h1><?php echo utf8_encode($eTipo->titulo);?></h1>
												<p>
													<?php 
													// se geral (prontuario), usa a data definida no cadastro
													if($eTipo->id==9 and isset($_geral[$e->id])) {
														echo date('d/m/Y H:i',strtotime($_geral[$e->id]->data));
													} else if($eTipo->id==11 and isset($_alta[$e->id])) {
														echo date('d/m/Y H:i',strtotime($_alta[$e->id]->data));
													} else {
														echo date('d/m/Y H:i',strtotime($e->data));
													}
													?>
												</p>
											</div>
										</div>
										<p class="toggle-tamanho"><?php echo isset($_profissionais[$e->id_profissional])?utf8_encode($_profissionais[$e->id_profissional]->nome):"";?></p>
										<?php
										if($eTipo->id==10 and isset($_documentos[$e->id])) {
											$d=$_documentos[$e->id];
											if(isset($_documentosTipos[$d->id_documento])) {
										?>
										<p><?php echo utf8_encode($_documentosTipos[$d->id_documento]->titulo);?></p>
										<?php
											}
										}
										?>
										<div class="list-toggle-alert">
											<div>
												<p>Dentista</p>
												<div class="list-toggle-alert-icones">
												<i class="iconify" data-icon="quill:signature"></i>
												<i class="iconify" data-icon="fa6-solid:file-signature"  <?php echo ($e->receita_assinada != "0000-00-00 00:00:00")?("style=\"color: red;\""):'';?> ></i>
												</div>
											</div>


											<div>
												<p>Paciente</p>
												<i class="iconify" data-icon="quill:signature" <?php echo in_array($e->id, $lstAssinP)?("style=\"color: yellow;\""):'';?> ></i>
											</div>
											
										</div>
										<div class="list-toggle-buttons">		
											
											<a href="<?php echo $pdf;?>" target="_blank" class="button"><i class="iconify" data-icon="ant-design:file-pdf-outlined"></i></a>
											<?php
											if($eTipo->id==7) {
												if($e->receita_assinada=="0000-00-00 00:00:00") {
												}

												?>
												<a href="javascript:;" class="button js-btn-whatsapp" data-id_evolucao="<?php echo $e->id;?>" data-loading="0"><i class="iconify" data-icon="fa:whatsapp"></i></a>
												<?php
											} else {
											?>
											<a href="javascript:;" class="button js-btn-whatsapp" data-id_evolucao="<?php echo $e->id;?>" data-loading="0"><i class="iconify" data-icon="fa:whatsapp"></i></a>
											<?php
											}
											?>
											<a href="<?php echo $_page."?deleta=".$e->id."&pagina=".((isset($_GET['pagina']) and is_numeric($_GET['pagina']))?$_GET['pagina']:'')."&$url";?>" class="button js-confirmarDeletar"><i class="iconify" data-icon="fluent:delete-24-regular"></i></a>
											<a href="javascript:;" class="button button_main js-expande js-expande-<?php echo $e->id;?>"><i class="iconify" data-icon="fluent:chevron-down-24-regular"></i></a>
										</div>																
									</header>

									<article<?php echo (isset($_GET['id_evolucao']) and $_GET['id_evolucao']==$e->id)?' class="active"':'';?>>
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
														<p><?php echo utf8_encode($atestado->duracao);?> min(s)</p>
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

											// geral
											else if($eTipo->id==9) {
												$correcoes=1;
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

											// alta
											else if($eTipo->id==11) {
												$correcoes=1;
												if(isset($_alta[$e->id])) {
													$g=$_alta[$e->id];
												?>
												<div class="list-toggle-topics">
													<div class="list-toggle-topic">
													
														<p><?php echo utf8_encode($g->texto);?></p>
													
													</div>
													
												</div>			
												<?php
												}
											}

											// documentos
											else if($eTipo->id==10) {

												if(isset($_documentos[$e->id])) {
													$g=$_documentos[$e->id];
												?>
												<div class="list-toggle-topics">
													<div class="list-toggle-topic">
													
														<p>
															<?php
															if(isset($_documentosTipos[$g->id_documento])) echo utf8_encode($_documentosTipos[$g->id_documento]->titulo);
															?>
														</p>
													
													</div>
													
												</div>			
												<?php
												}
											}

											// procedimentos
											else if($eTipo->id==2) {
												$correcoes=1;
												?>
												<div class="list-toggle-topics">
												<?php
												foreach($_procedimentosEvoluidos[$e->id] as $pe) {

													if($pe->id_procedimento==0) {
														?>
														<p>
															<strong>Evolução Geral:</strong>
															<span style="colocar:var(--cinza4);">
																<?php echo utf8_encode($pe->obs);?>
															</span>
														</p>
														<?php
													} else {
														if(isset($_procedimentosEvoluidosProcedimentos[$pe->id_procedimento])) {

															$status='';
															if($pe->status=="iniciar") $status="Não iniciado";
															else if($pe->status=="iniciado") $status="Em Tratamento";
															else if($pe->status=="finalizado") $status="Finalizado";
															else if($pe->status=="cancelado") $status="Cancelado";

															$procedimento=$_procedimentosEvoluidosProcedimentos[$pe->id_procedimento];

															?>
															<div>
																<p>
																	<strong><?php echo trim(utf8_encode($procedimento->titulo));?>:</strong>
																	<?php echo utf8_encode($pe->opcao);?>
																	<?php echo utf8_encode($pe->obs);?>
																	<?php

																	if($pe->numeroTotal>1) echo ' '.utf8_encode($pe->numero."/".$pe->numeroTotal);
																	?>
																	<a href="javascript:;" class="button js-btn-visualizarHistorico" data-id_evolucao="<?php echo $e->id;?>" data-id_procedimento_aevoluir="<?php echo $pe->id_procedimento_aevoluir;?>" style="float:right;"><span class="iconify" data-icon="material-symbols:search"></span></a>
																	<br /><span style="background:var(--cinza5);color:#FFF;padding:2px;border-radius:5px;font-size: 12px;"><?php echo $status;?></span>
																	<?php
																	if(isset($_procedimentosHistorico[$pe->id_evolucao][$pe->id_procedimento_aevoluir])) {
																		foreach($_procedimentosHistorico[$pe->id_evolucao][$pe->id_procedimento_aevoluir] as $obs) {
																	?>
																	<br /><span style="color:#999"><span class="iconify" data-icon="material-symbols:chat-outline-rounded"></span> <?php echo utf8_encode($obs->obs);?></span>
																	<?php
																		}
																	}
																	?>
																</p>
															</div>
															<?php
															
														}
													}
												}
												
												?>
												</div>
												<?php
											}


											if($correcoes!="") {
										?>

										<div class="list-toggle-com js-erratas js-errata-<?php echo $e->id;?>">
											<header>
												<h1>Correções</h1>
											</header>

											<?php
											if(isset($_erratas[$e->id])) {
												foreach($_erratas[$e->id] as $errata) {
											?>
											<article class="js-errata-item">
												<header>
													<?php
													if(isset($_profissionais[$errata->id_usuario])) {
													?>
													<p><?php echo utf8_encode($_profissionais[$errata->id_usuario]->nome);?></p>
													<?php
													}
													?>
													<p><?php echo date('d/m/Y H:i',strtotime($errata->data));?></p>
												</header>
												<article>
													<p><?php echo utf8_encode($errata->texto);?></p>
												</article>
											</article>
											<?php
												}
											} else {
											?>
											<article class="js-errata-item">
												<center>Nenhuma errata realizada.</center>
											</article>
											<?php	
											}
											?>

											<dl class="js-errata-item">
												<textarea style="height: 50px;" class="js-errata-texto-<?php echo $e->id;?>"></textarea>
												<button class="button button_main js-errata-adicionar" data-loading="0" style="float: right;" data-id_evolucao="<?php echo $e->id;?>"><i class="iconify" data-icon="fluent:add-circle-24-regular"></i> Adicionar</button>
											</dl>
											
											
										</div>	
										<?php
										}
										?>						
									</article>
								</div>
								<?php
										
									}
								}
								?>

							</div>	
						</div>
					</div>
					
				</div>

			</section>


			

			<script type="text/javascript">

				const erratasListar = (id_evolucao,id_paciente) => {
					let data = {'ajax':'evolucaoErrataListar',
								'id_evolucao':id_evolucao,
								'id_paciente':id_paciente}

					$.ajax({
						type:"POST",
						data:data,
						success:function(rtn) {
							if(rtn.success) {
								$(`.js-errata-${rtn.id_evolucao}`).find('.js-errata-item').remove();
								if(rtn.erratas.length>0) {
									rtn.erratas.forEach(x=>{
										$(`.js-errata-${rtn.id_evolucao}`).append(`<article class="js-errata-item">
																						<header>
																							<p>${x.profissional}</p>
																							<p>${x.data}</p>
																						</header>
																						<article>
																							<p>${x.texto}</p>
																						</article>
																					</article>`);
									});
									$(`.js-errata-${rtn.id_evolucao}`).append(`<dl class="js-errata-item">
																					<textarea style="height: 50px;" class="js-errata-texto-${rtn.id_evolucao}"></textarea>
																					<button class="button button_main js-errata-adicionar" data-loading="0" style="float: right;" data-id_evolucao="${rtn.id_evolucao}"><i class="iconify" data-icon="fluent:add-circle-24-regular"></i> Adicionar</button>
																				</dl>`);
								} else {
									$(`.js-errata-${rtn.id_evolucao}`).append(`<article class="js-errata-item"><center>Nenhuma errata realizada.</center></article>`);
								}
							}
						}
					})
				}
				$(function(){

					$('.js-expande').click(function() {
						$(this).parent().parent().next('article').toggleClass('active');
						$(this).toggleClass('button-reverse');
					});

					$('.js-btn-whatsapp').click(function(){
						let id_evolucao = $(this).attr('data-id_evolucao');
						let obj = $(this);
						let objHTMLAntigo = $(this).html();

						if(obj.attr('data-loading')==0) {

							obj.attr('data-loading',1);
							obj.html('<span class="iconify" data-icon="eos-icons:loading"></span>');

							let data = {'token':'ee7a1554b556f657e8659a56d1a19c315684c39d',
										'method':'sendWhatsapp',
										'infoConta':'<?php echo $_ENV['NAME'];?>',
										'id_evolucao':id_evolucao};

							$.ajax({
								type:"POST",
								url:'services/api.php',
								data:JSON.stringify(data),
								success:function(rtn){
									if(rtn.success) {
										swal({title: "Sucesso!", text: `PDF enviado para o número ${rtn.numero} com sucesso!`, type:"success", confirmButtonColor: "#424242"});
									} else if(rtn.error) {
										swal({title: "Erro!", text: rtn.error, type:"error", confirmButtonColor: "#424242"});
									} else {
										swal({title: "Erro!", text: 'Algum erro ocorreu durante o envio do PDF. Tente novamente!', type:"error", confirmButtonColor: "#424242"});
									}

									obj.html(objHTMLAntigo);
									obj.attr('data-loading',0);
								},
								error:function(){
										swal({title: "Erro!", text: 'Algum erro ocorreu durante o envio do PDF. Tente novamente.', type:"error", confirmButtonColor: "#424242"});
									obj.html(objHTMLAntigo);
									obj.attr('data-loading',0);
								}
							})
						}
					})

					$('.js-erratas').on('click','.js-errata-adicionar',function(){

						let id_evolucao = $(this).attr('data-id_evolucao');
						let texto = $(`.js-errata-texto-${id_evolucao}`).val();

						let erro='';
						if(id_evolucao.length==0) erro='Evolução não definida';
						else if(texto.length==0) {
							erro='Peencha o campo da erratada!';
							$(`.js-errata-texto-${id_evolucao}`).addClass('erro');
						}


						if(erro.length==0) {

							let obj = $(this);
							let objHTMLAntigo = $(this).html();

							if(obj.attr('data-loading')==0) {

								obj.attr('data-loading',1);
								obj.html(`<span class="iconify" data-icon="eos-icons:loading"></span>`);

								let data = {'ajax':'evolucaoErrataPersisitr',
											'id_evolucao':id_evolucao,
											'id_paciente':id_paciente,
											'texto':texto};

								$.ajax({
									type:"POST",
									data:data,
									success:function(rtn) {
										if(rtn.success) {
											erratasListar(id_evolucao,id_paciente);
											$(`.js-errata-texto-${id_evolucao}`).val('');
										} else if(rtn.error) {
											swal({title: "Erro!", text: rtn.error, type:"error", confirmButtonColor: "#424242"});
										} else {
											swal({title: "Erro!", text: 'Algum erro ocorreu. Tente novamente!', type:"error", confirmButtonColor: "#424242"});
										}
									}
								}).done(function(){
									obj.attr('data-loading',0);
									obj.html(objHTMLAntigo)
								})
							}


						} else {
							swal({title: "Erro!", text: erro, type:"error", confirmButtonColor: "#424242"});
						}

					})

					$('.js-btn-visualizarHistorico').click(function(){

						let id_evolucao = $(this).attr('data-id_evolucao');
						id_procedimento_aevoluir = $(this).attr('data-id_procedimento_aevoluir');


						let data = {'ajax':'evolucaoProcedimentosHistorico',
											'id_evolucao':id_evolucao,
											'id_paciente':id_paciente,
											'id_procedimento_aevoluir':id_procedimento_aevoluir};

						$.ajax({
							type:"POST",
							data:data,
							success:function(rtn) {
								if(rtn.success) {

									// limpa os historicos do aside
									$('.aside-prontuario-procedimentos-historico-visualizacao .js-procedimentos-historico').html('');

									let cont = 0;
									rtn.historico.forEach(x=>{

										$('.aside-prontuario-procedimentos-historico-visualizacao .js-procedimentos-historico').append(`<div class="history-item">
																																<h1>${x.usuario} em ${x.data}</h1>		
																																${x.obs}
																															</div>`);

									
										cont++;
										if(cont==rtn.historico.length) {
											$(".aside-prontuario-procedimentos-historico-visualizacao").fadeIn(100,function() {
												$(".aside-prontuario-procedimentos-historico-visualizacao .aside__inner1").addClass("active");
												$(".aside-prontuario-procedimentos-historico-visualizacao .js-hist-id_procedimento_aevoluir").val(id_procedimento_aevoluir);
												$(".aside-prontuario-procedimentos-historico-visualizacao .js-hist-id_evolucao").val(id_evolucao);
												$(".aside-prontuario-procedimentos-historico-visualizacao .js-hist-procedimento").val(rtn.procedimento);
												$(".aside-prontuario-procedimentos-historico-visualizacao .js-hist-status").val(rtn.status);
												//$(".aside-prontuario-procedimentos-historico-visualizacao .js-hist-dataEvolucao").val(rtn.dataEvolucao);
											});
										}

									});
								}
							}
						})
					});
				})
			</script>

			<center>
				<?php
					if(isset($sql->myspaginacao) and !empty($sql->myspaginacao)) {
				?>
				<div class="pagination" style="margin:15px;">						
					<?php echo $sql->myspaginacao;?>
				</div>
				<?php
				}
				?>		
			</center>
			
		</div>
	</main>

<?php 


	$apiConfig=array('geral'=>1,
						'anamnese'=>1,
						'atestado'=>1,
						'pedidoExame'=>1,
						'receituario'=>1,
						'proximaConsulta'=>1,
						'documentos'=>1,
						'procedimentos'=>1);
	
	require_once("includes/api/apiAsidePaciente.php");

	include "includes/footer.php";
?>	