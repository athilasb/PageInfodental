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

	$paciente=$cnt='';
	if(isset($_GET['id_paciente']) and is_numeric($_GET['id_paciente'])) {
		$sql->consult($_p."pacientes","*","where id='".$_GET['id_paciente']."'");
		if($sql->rows) {
			$paciente=mysqli_fetch_object($sql->mysqry);
			$cnt=$paciente;
		}
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
					?>

					

				</div>				
			</section>


		<?php
		} else {
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

					<fieldset>
						<legend>Ficha do Paciente</legend>

						<?php
							$registros=array();
							$evolucoesIds=array(-1);
							$usuariosIds=array(-1);
							$sql->consult($_p."pacientes_evolucoes","*","where id_paciente=$paciente->id and lixo=0 order by data desc");
							while($x=mysqli_fetch_object($sql->mysqry)) {
								$registros[]=$x;
								$usuariosIds[]=$x->id_usuario;
								if($x->id_tipo==2 or $x->id_tipo==3 or $x->id_tipo==6 or $x->id_tipo==7) $evolucoesIds[]=$x->id;

							}

							$_usuarios=array();
							$sql->consult($_p."colaboradores","*","WHERE id IN (".implode(",",$usuariosIds).")");
							while($x=mysqli_fetch_object($sql->mysqry)) {
								$_usuarios[$x->id]=$x;
							}

							$tratamentoProdecimentosIds=array(-1);
							$registrosProcedimentos=array();
							$sql->consult($_p."pacientes_evolucoes_procedimentos","*","where id_paciente=$paciente->id and id_evolucao IN (".implode(",",$evolucoesIds).") and lixo=0 order by data desc");
							while($x=mysqli_fetch_object($sql->mysqry)) {
								$evolucoesIds[]=$x->id;
								$registrosProcedimentos[$x->id_evolucao][]=$x;
							}
							

							$prodecimentosIds=array(-1);
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

							$_procedimentos=array();
							$sql->consult($_p."parametros_procedimentos","*","where id IN (".implode(",",$prodecimentosIds).")");
							while($x=mysqli_fetch_object($sql->mysqry)) {
								$_procedimentos[$x->id]=$x;
							}

							$profissionaisJaListados=array();
						?>
						<div class="reg">
							<?php
								foreach($registros as $x) {
									if(isset($_tiposEvolucao[$x->id_tipo])) {
										$tipo = $_tiposEvolucao[$x->id_tipo];
							?>
							<a href="<?php echo $tipo->pagina."?form=1&id_paciente=$paciente->id&edita=".$x->id;?>" class="reg-group">
								<div class="reg-color" style=""></div>
								<div class="reg-data" style="width:5%">
									<i class="iconify" data-icon="<?php echo $tipo->icone;?>"></i>
								</div>

								<div class="reg-data" style="width:30%">
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

								<div class="reg-data" style="width: 55%;">
									<?php
										$autor=isset($_usuarios[$x->id_usuario])?utf8_encode($_usuarios[$x->id_usuario]->nome):'Desconhecido';
									?>
									<p><span class="iconify" data-icon="bi:check-all"></span> <?php echo "<b>".$autor."</b> deu baixa em ";?>
										<b><?php echo date('d/m/Y',strtotime($x->data));?> - <?php echo date('H:i',strtotime($x->data));?></b></p>
								</div>

								<div class="reg-data" style="width: 5%;">
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
								</div>
							</a>
							<?php
									}
								}
							?>
						</div>
					
					</fieldset>

				</section>
			</section>
		<?php
		}
		?>			
		</section>
		
<?php
include "includes/footer.php";
?>