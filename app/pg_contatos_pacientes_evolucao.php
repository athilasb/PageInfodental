<?php
	include "includes/header.php";
	include "includes/nav.php";

	$_table=$_p."pacientes";
	$_page=basename($_SERVER['PHP_SELF']);

	$_width=400;
	$_height=400;
	$_dir="arqs/pacientes/";

	$_cidades=array();
	$sql->consult($_p."cidades","*","");
	while($x=mysqli_fetch_object($sql->mysqry)) $_cidades[$x->id]=$x;

	$_profissoes=array();
	$sql->consult($_p."parametros_profissoes","*","where lixo=0 order by titulo asc");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_profissoes[$x->id]=$x;
	}

	$_profissionais=array();
	$sql->consult($_p."colaboradores","id,nome,calendario_iniciais,foto,calendario_cor","where tipo_cro<>'' and lixo=0 order by nome asc");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_profissionais[$x->id]=$x;
	}


	$selectProfissional='<select class="js-profissional"><option value="">-</option>';
	foreach($_profissionais as $p) {
	
											
		$selectProfissional.='<option value="'.$p->id.'" data-iniciais="'.$p->calendario_iniciais.'" data-iniciaisCor="'.$p->calendario_cor.'">'.utf8_encode($p->nome).'</option>';
	}
	$selectProfissional.='</select>';

	$_tiposEvolucao=array();
	$sql->consult($_p."pacientes_evolucoes_tipos","*","");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_tiposEvolucao[$x->id]=$x;
	}

	$_pacienteIndicacoes=array();
	$sql->consult($_p."parametros_indicacoes","*","where lixo=0 order by titulo asc");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_pacienteIndicacoes[$x->id]=$x;
	}

	$_pacienteGrauDeParentesco=array();
	$sql->consult($_p."parametros_grauparentesco","*","where lixo=0 order by titulo asc");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_pacienteGrauDeParentesco[$x->id]=$x;
	}

	$_procedimentos=array();
	$sql->consult($_p."parametros_procedimentos","*","where lixo=0 order by titulo asc");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_procedimentos[$x->id]=$x;
	}

	$paciente=$cnt='';
	if(isset($_GET['id_paciente']) and is_numeric($_GET['id_paciente'])) {
		$sql->consult($_p."pacientes","*","where id='".$_GET['id_paciente']."'");
		if($sql->rows) {
			$paciente=mysqli_fetch_object($sql->mysqry);
			$cnt=$paciente;
		}
	}

	$_planos=array();
	$sql->consult($_p."parametros_planos","*","where lixo=0 order by titulo asc");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_planos[$x->id]=$x;
	}


	$campos=explode(",","nome,situacao,noem,sexo,foto,rg,rg_orgaoemissor,rg_estado,cpf,data_nascimento,profissao,estado_civil,telefone1,telefone1_whatsapp,telefone1_whatsapp_permissao,telefone2,email,instagram,instagram_naopossui,musica,indicacao_tipo,indicacao,cep,endereco,numero,complemento,bairro,estado,cidade,id_cidade,responsavel_possui,responsavel_nome,responsavel_sexo,responsavel_rg,responsavel_rg_orgaoemissor,responsavel_rg_estado,responsavel_datanascimento,responsavel_estadocivil,responsavel_cpf,responsavel_profissao,responsavel_grauparentesco,preferencia_contato");
	
	foreach($campos as $v) $values[$v]='';
	$values['data']=date('d/m/Y H:i');
	$values['sexo']='M';


	if(is_object($paciente)) {
		$values=$adm->values($campos,$cnt);
		$values['data']=date('d/m/Y H:i',strtotime($cnt->data));
	}
	?>
	<section class="content">
		
		<?php
		require_once("includes/abaPaciente.php");
		?>

		<script type="text/javascript">
			$(function(){
				$('input[name=tipo]').click(function(){
					let tipo = $(this).val();

					$(`.js-box`).hide();
					$(`.js-box-${tipo}`).show();
				})
			});
		</script>

		<?php
		if(isset($_GET['form'])) {
		?>
			<section class="grid">
				<div class="box">

					<?php
					require_once("includes/evolucaoMenu.php");
					require_once("includes/evolucaoProcedimentosJs.php");
					?>
					

				</div>				
			</section>


		<?php
		} else {

			$tratamentosIds=array(-1);
			$sql->consult($_p."pacientes_tratamentos","*","where id_paciente=$paciente->id and  status='APROVADO' and lixo=0");
			while($x=mysqli_fetch_object($sql->mysqry)) $tratamentosIds[]=$x->id;

			$procedimentosIds=array(-1);
			$_procedimentosAprovadosASerEvoluido=array();
			$tratamentosProcedimentosIds=array(0);
			$where="where lixo=0 and situacao='aprovado' and id_tratamento IN (".implode(",",$tratamentosIds).")";

			//die();
			$_procedimentosDeTratamentosAprovados=array();
			$sql->consult($_p."pacientes_tratamentos_procedimentos","*",$where);
			while($x=mysqli_fetch_object($sql->mysqry)) {
				$procedimentosIds[]=$x->id_procedimento;
				$tratamentosProcedimentosIds[]=$x->id;
				$_procedimentosDeTratamentosAprovados[$x->id]=$x;
			}

			$historicoGeral=$evolucaoProcedimentos=array();

			$evolucaoRegistros=array();
			$evolucoesIds=array(-1);
			$usuariosIds=array(-1);
			$prodecimentosIds=array(-1);
			$_evolucoes=array();
			$sql->consult($_p."pacientes_evolucoes","*","where id_paciente=$paciente->id order by data desc");
			while($x=mysqli_fetch_object($sql->mysqry)) {
				$_evolucoes[$x->id]=$x;
				$evolucaoRegistros[]=$x;
				$usuariosIds[]=$x->id_usuario;
				if($x->id_tipo==2 or $x->id_tipo==3 or $x->id_tipo==6 or $x->id_tipo==7) $evolucoesIds[]=$x->id;
				

			}

			$_usuarios=array();
			$sql->consult($_p."colaboradores","*","WHERE id IN (".implode(",",$usuariosIds).")");
			while($x=mysqli_fetch_object($sql->mysqry)) {
				$_usuarios[$x->id]=$x;
			}

			$tratamentoProdecimentosIds=$procedimentosAEvoluirIds=array(-1);
			$registrosProcedimentos=array();
			$registrosEvolucoesProcedimentos=array();
			$sql->consult($_p."pacientes_evolucoes_procedimentos","*","where id_paciente=$paciente->id and lixo=0 order by data desc");
			while($x=mysqli_fetch_object($sql->mysqry)) {
				$evolucoesIds[]=$x->id;
				//$registrosProcedimentos[$x->id_evolucao][]=$x;
				$registrosEvolucoesProcedimentos[]=$x;
				$procedimentosIds[]=$x->id_procedimento;
				if($x->id_procedimento_aevoluir>0) {
					$procedimentosAEvoluirIds[]=$x->id_procedimento_aevoluir;
				}
				
			}

			// Procedimentos a Evoluir
				$tratamentosProdecimentosIds=array(0); // procedimentos aprovados (pacientes_tratamentos_procedimentos)
				$_procedimentosAEvoluir=array();
				$sql->consult($_p."pacientes_tratamentos_procedimentos_evolucao","*","where id IN (".implode(',',$procedimentosAEvoluirIds).") and lixo=0");
				while($x=mysqli_fetch_object($sql->mysqry)) {
					$tratamentosProdecimentosIds[]=$x->id;
					$_procedimentosAEvoluir[$x->id]=$x;
				}

				// Procedimentos Aprovados
				$_tratamentosProcedimentosAprovados=array(-1);
				$where="where id IN (".implode(",",$tratamentosProdecimentosIds).") and id_paciente=$paciente->id and lixo=0";
				$sql->consult($_p."pacientes_tratamentos_procedimentos","*",$where);
				while($x=mysqli_fetch_object($sql->mysqry)) $_tratamentosProcedimentosAprovados[$x->id]=$x;


				// Historico dos procedimentos que foram evoluidos
				$_procedimentoEvoluidoHistorico=array();
				$sql->consult($_p."pacientes_tratamentos_procedimentos_evolucao_historico","*","where id_procedimento_aevoluir IN (".implode(",",$procedimentosAEvoluirIds).") and lixo=0 order by data asc");
				while($x=mysqli_fetch_object($sql->mysqry)) {
					$_procedimentoEvoluidoHistorico[$x->id_procedimento_aevoluir][]=array('data'=>date('d/m/Y H:i',strtotime($x->data)),
																							'id_usuario'=>$x->id_usuario,
																							'usuario'=>isset($_usuarios[$x->id_usuario])?utf8_encode($_usuarios[$x->id_usuario]->nome):'Desconhecido',
																							'obs'=>utf8_encode($x->obs),
																							'id'=>$x->id);
				}

			foreach($registrosEvolucoesProcedimentos as $x) {
				if(isset($_evolucoes[$x->id_evolucao])) {
					$evolucao=$_evolucoes[$x->id_evolucao];
				
					if($x->id_procedimento_aevoluir>0 and isset($_procedimentosAEvoluir[$x->id_procedimento_aevoluir])) {
						$procedimentoAEvoluir=$_procedimentosAEvoluir[$x->id_procedimento_aevoluir];
						
						if(isset($evolucaoProcedimentos[$procedimentoAEvoluir->id])) continue;

						if(isset($_procedimentosDeTratamentosAprovados[$procedimentoAEvoluir->id_tratamento_procedimento])) {
							$procedimentoAprovado=$_procedimentosDeTratamentosAprovados[$procedimentoAEvoluir->id_tratamento_procedimento];
						
							if(isset($_procedimentos[$procedimentoAprovado->id_procedimento])) {
								
								$procedimento=$_procedimentos[$procedimentoAprovado->id_procedimento];
								$profissionalCor='';
								$profissionalIniciais='';
								if(isset($_profissionais[$x->id_profissional])) {
									$p=$_profissionais[$x->id_profissional];
									$profissionalIniciais=$p->calendario_iniciais;
									$profissionalCor=$p->calendario_cor;
								}

								$autor='-';
								if(isset($_usuarios[$evolucao->id_usuario])) {
									$p=$_usuarios[$evolucao->id_usuario];
									$autor=($p->nome);
								}



								$historico=isset($_procedimentoEvoluidoHistorico[$procedimentoAEvoluir->id])?$_procedimentoEvoluidoHistorico[$procedimentoAEvoluir->id]:array();

								$evolucaoProcedimentos[$procedimentoAEvoluir->id]=array('id'=>$x->id,
																'autor'=>utf8_encode($autor),
																'data'=>date('d/m/Y',strtotime($x->data)),
																'id_usuario'=>$evolucao->id_usuario,
																'id_tratamento_procedimento'=>$x->id,
																'id_procedimento_aevoluir'=>$procedimentoAEvoluir->id,
																'id_procedimento'=>$x->id_procedimento,
																'id_profissional'=>$x->id_profissional,
																'obs'=>utf8_encode($x->obs),
																'opcao'=>utf8_encode($x->opcao),
																'plano'=>isset($_planos[$x->id_plano])?utf8_encode($_planos[$x->id_plano]->titulo):'-',
																'profissionalCor'=>$profissionalCor,
																'profissionalIniciais'=>$profissionalIniciais,
																'statusEvolucao'=>$x->status,
																'historico'=>$historico,
															 	'titulo'=>utf8_encode($procedimento->titulo),
															 	'numero'=>$procedimentoAEvoluir->numero,
															 	'numeroTotal'=>$procedimentoAEvoluir->numeroTotal,

															 	'avulso'=> 0); 
							} 
						}
					} else if(isset($_procedimentos[$x->id_procedimento])) {
							$procedimento=$_procedimentos[$x->id_procedimento];
							$profissionalCor='';
							$profissionalIniciais='';

							if(isset($_profissionais[$x->id_profissional])) {
								$p=$_profissionais[$x->id_profissional];
								$profissionalIniciais=$p->calendario_iniciais;
								$profissionalCor=$p->calendario_cor;
							
							}

							$autor='-';
							if(isset($_usuarios[$evolucao->id_usuario])) {
								$p=$_usuarios[$evolucao->id_usuario];
								$autor=utf8_encode($p->nome);
							}

							//echo $p->nome." ".$profissionalIniciais."->".$profissionalCor.'<BR>';


							$evolucaoProcedimentos['e'.$x->id]=array('id'=>$x->id,
															'autor'=>$autor,
															'data'=>date('d/m/Y',strtotime($x->data)),
															'id_usuario'=>$evolucao->id_usuario,
															'id_procedimento'=>$x->id_procedimento,
															'id_profissional'=>$x->id_profissional,
															'obs'=>utf8_encode($x->obs),
															'opcao'=>utf8_encode($x->opcao),
															'id_opcao'=>utf8_encode($x->id_opcao),
															'id_plano'=>utf8_encode($x->id_plano),
															'plano'=>isset($_planos[$x->id_plano])?utf8_encode($_planos[$x->id_plano]->titulo):'-',
															'profissionalCor'=>$profissionalCor,
															'profissionalIniciais'=>$profissionalIniciais,
															'statusEvolucao'=>$x->status,
															'historico'=>array(),
														 	'titulo'=>utf8_encode($procedimento->titulo),
														 	'avulso'=>1);
					}
				}
				
			}


			$evolucaoProcedimentosAux=array();
			foreach($evolucaoProcedimentos as $x) {
				$evolucaoProcedimentosAux[]=$x;
			}
			$evolucaoProcedimentos=$evolucaoProcedimentosAux;
			

			$_tratamentoProcedimentos=array();
			$sql->consult($_p."pacientes_tratamentos_procedimentos","*","where id IN (".implode(",",$tratamentoProdecimentosIds).")");
			while($x=mysqli_fetch_object($sql->mysqry)) {
				$prodecimentosIds[]=$x->id_procedimento;
				$_tratamentoProcedimentos[$x->id]=$x;
			}


			$_exames=array();
			$sql->consult($_p."pacientes_evolucoes_pedidosdeexames","id,id_evolucao","where id_paciente=$paciente->id and id_evolucao IN (".implode(",",$evolucoesIds).") and lixo=0 order by data desc");
			while($x=mysqli_fetch_object($sql->mysqry)) {
				$_exames[$x->id_evolucao][]=$x;
			}

			$_receitas=array();
			$sql->consult($_p."pacientes_evolucoes_receitas","id,id_evolucao","where id_paciente=$paciente->id and id_evolucao IN (".implode(",",$evolucoesIds).") and lixo=0 order by data desc");
			while($x=mysqli_fetch_object($sql->mysqry)) {
				$_receitas[$x->id_evolucao][]=$x;
			} 
			
			require_once("includes/evolucaoProcedimentosJs.php");
		?>
			<section class="grid">
				<section class="box">
					<fieldset>
						<legend> Escolha o tipo da evolução</legend>
						<?php /*<div class="filter">
							<div class="filter-group">
								<div class="filter-button">
									<a href="<?php echo $_page."?form=1&$url";?>" class="verde"><i class="iconify" data-icon="bx-bx-plus"></i><span>adicionar evolução</span></a>
								</div>
							</div>
						</div>*/

						


						require_once("includes/evolucaoMenu.php");
						?>
					</fieldset>
						<?php
							$tratamentosIds=array(-1);
							$sql->consult($_p."pacientes_tratamentos","*","where id_paciente=$paciente->id and  status='APROVADO' and lixo=0");
							while($x=mysqli_fetch_object($sql->mysqry)) $tratamentosIds[]=$x->id;

							$procedimentosIds=array(-1);
							$_procedimentosAprovadosASerEvoluido=array();
							$tratamentosProcedimentosIds=array(0);
							$where="where lixo=0 and situacao='aprovado' and id_tratamento IN (".implode(",",$tratamentosIds).")";

							//die();
							$_procedimentosDeTratamentosAprovados=array();
							$sql->consult($_p."pacientes_tratamentos_procedimentos","*",$where);
							while($x=mysqli_fetch_object($sql->mysqry)) {
								$procedimentosIds[]=$x->id_procedimento;
								$tratamentosProcedimentosIds[]=$x->id;
								$_procedimentosDeTratamentosAprovados[$x->id]=$x;
							}


							$sql->consult($_p."pacientes_tratamentos_procedimentos_evolucao","*","where id_tratamento_procedimento IN (".implode(",",$tratamentosProcedimentosIds).") and status_evolucao = 'iniciar' and lixo=0");

							while($x=mysqli_fetch_object($sql->mysqry)) {
								$_procedimentosAprovadosASerEvoluido[$x->id]=$x;
							}

							$_procedimentos=array();
							$sql->consult($_p."parametros_procedimentos","id,titulo","where  lixo=0");
							while($x=mysqli_fetch_object($sql->mysqry)) {
								$_procedimentos[$x->id]=$x;
							}
						?>
					<fieldset style="">
						<legend>Procedimentos a Iniciar (<?php echo count($_procedimentosAprovadosASerEvoluido);?>)</legend>
							<?php
							if(count($_procedimentosAprovadosASerEvoluido)==0) {
								echo '<center>Nenhum procedimento aprovado a iniciar</center>';
							} else {
							?>
							<div class="reg">
								<?php
								foreach($_procedimentosAprovadosASerEvoluido as $v) {
									$disabled='';
									if(isset($procedimentosAEvoluirIds) and in_array($v->id,$procedimentosAEvoluirIds)) $disabled=" disabled";;
									if(isset($_procedimentos[$v->id_procedimento])) {
										$procedimento=$_procedimentos[$v->id_procedimento];
										$profissionalIniciais='';
										$profissionalCor='#ccc';
										if(isset($_profissionais[$v->id_profissional])) {
											$p=$_profissionais[$v->id_profissional];
											$profissionalIniciais=$p->calendario_iniciais;
											$profissionalCor=$p->calendario_cor;

										}
										$complemento='';
										if($v->numeroTotal>1) $complemento.=' - '.utf8_encode($v->numero."/".$v->numeroTotal);

										//	id_tratamento_procedimento => Procedimento de tratamento aprovado
										if(isset($_procedimentosDeTratamentosAprovados[$v->id_tratamento_procedimento])) {
											$procedimentoAprovado=$_procedimentosDeTratamentosAprovados[$v->id_tratamento_procedimento];
											if(!empty($procedimentoAprovado->opcao)) $complemento.=" - ".utf8_encode($procedimentoAprovado->opcao)
												;
										?>
										<a href="<?php echo $tipo->pagina."?form=1&id_paciente=$paciente->id&edita=".$x->id;?>" class="reg-group">
											<div class="reg-color" style=""></div>

											<div class="reg-data" style="width:100%">
												<p><strong><?php echo utf8_encode($procedimento->titulo).$complemento;?></strong></p>
											</div>
										</a>
										<?php
										
										}
									}
								}

								?>
							</div>
							<?php
							}
							?>
					</fieldset>
					<div class="grid grid_2">
						<fieldset >
							<legend>Ficha do Paciente</legend>

							<?php
								

								$profissionaisJaListados=array();
							?>
							<div class="reg">
								<?php
									if(count($evolucaoRegistros)==0) echo '<center>Nenhuma evolução foi lançada!</center>';
									foreach($evolucaoRegistros as $x) {
										if(isset($_tiposEvolucao[$x->id_tipo])) {
											$tipo = $_tiposEvolucao[$x->id_tipo];

											if($x->id_tipo==1 or $x->id_tipo==2) $tipo->tituloSingular="Procedimento";
											
												$url=$tipo->pagina."?form=1&id_paciente=$paciente->id&edita=".$x->id;
											if($x->lixo==1) {
												$style="opacity:0.3;";
											} else {
												$style="";

											}
								?>
								<a href="<?php echo $url;?>" class="reg-group" style="<?php echo $style;?>">
									<div class="reg-color" style=""></div>
									<div class="reg-data" style="width:5%">
										<i class="iconify" data-icon="<?php echo $tipo->icone;?>"></i>
									</div>

									<div class="reg-data" style="width:70%">
										<p><strong><?php echo utf8_encode($tipo->tituloSingular);?></strong></p>
									</div>

									 <?php /*<div class="reg-data" style="width:5%;color:#">
										<p><b><?php echo $x->data_evolucao!="0000-00-00"?date('d/m/Y',strtotime($x->data_evolucao)):"";?></b></p>
									</div>

									<div class="reg-data" style="width:10%;">
										<p>
											<?php 
												if($x->id_tipo==2 or $x->id_tipo==3) {
													echo isset($registrosProcedimentos[$x->id])?count($registrosProcedimentos[$x->id]):0;
												} else if($x->id_tipo==6) {
													echo isset($_exames[$x->id])?count($_exames[$x->id]):0;

												} else if($x->id_tipo==7) {

													echo (isset($_receitas[$x->id])?count($_receitas[$x->id]):0);

												} else {
													echo 1;
												}
											?>
										</p>
									</div>*/?>

									<div class="reg-data" style="width:25%">
										<?php
											$autor=isset($_usuarios[$x->id_usuario])?utf8_encode($_usuarios[$x->id_usuario]->nome):'Desconhecido';
										?>
										<p style="font-size:10px;"><span class="iconify" data-icon="bi:check-all"></span> <span style="color: var(--cor1)"><?php echo date('d/m/Y',strtotime($x->data));?> - <?php echo date('H:i',strtotime($x->data));?></span><br /><?php echo $autor;?>
											</p>
									</div>

									<?php /*<div class="reg-data" style="width: 5%;">
										<?php
											$profissionaisJaListados=array();
											if($tipo->id == 2) {
												$sql->consult($_p."pacientes_evolucoes_procedimentos","*","where id_evolucao=$x->id and lixo=0");
												if($sql->rows) {
													while($y=mysqli_fetch_object($sql->mysqry)) {
														if(isset($_profissionais[$y->id_profissional])) {

															if(!isset($profissionaisJaListados[$x->id_profissional])) {
																$profissionaisJaListados[$x->id_profissional]=1;
																$p=$_profissionais[$y->id_profissional];
																$profissionalIniciais=$p->calendario_iniciais;
																$profissionalCor=$p->calendario_cor;
										?>
										<span style="background:<?php echo empty($profissionalCor)?"#CCC":$profissionalCor;?>;color:#FFF;padding:10px;border-radius: 50%"><?php echo $profissionalIniciais;?></span>
										<?php
															}
														}
													}
												}
											} else {
												if(isset($_profissionais[$x->id_profissional])) {
													if(!isset($profissionaisJaListados[$x->id_profissional])) {
														$profissionaisJaListados[$x->id_profissional]=1;
														$calendario_cor = $_profissionais[$x->id_profissional]->calendario_cor;
														$calendario_iniciais = $_profissionais[$x->id_profissional]->calendario_iniciais;
										?>	
										<span style="background:<?php echo empty($calendario_cor)?"#CCC":$calendario_cor;?>;color:#FFF;padding:10px;border-radius: 50%"><?php echo $calendario_iniciais;?></span>
										<?php
													}
												
												}
											}
										?>
									</div>*/?>
								</a>
								<?php
										}
									}
								?>
							</div>
						
						</fieldset>
						<fieldset style="">
							<legend>Procedimentos Evoluídos</legend>

								<div class="reg js-div-procedimentos">
								</div>
						</fieldset>
					</div>

				</section>
			</section>
		<?php
		}
		?>			
		</section>
		
<?php
include "includes/footer.php";
?>