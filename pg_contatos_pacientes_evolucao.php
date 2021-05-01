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
	$sql->consult($_p."profissionais","*","where lixo=0 order by nome asc");//"where unidades like '%,$unidade->id,%' and lixo=0 order by nome asc");
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

					<div class="filter">
						<div class="filter-group">
							<div class="filter-button">
								<a href="<?php echo $_page."?form=1&$url";?>" class="verde"><i class="iconify" data-icon="bx-bx-plus"></i><span>adicionar evolução</span></a>
							</div>
						</div>
					</div>

					<div class="registros">
						<?php /*<a href="<?php echo "$_page?form=1&id_paciente=$paciente->id";?>" class="paciente-evolucao__add"><i class="iconify" data-icon="mdi-plus-circle-outline"></i> Adicionar evolução</a>*/ ?>
						
						<table class="tablesorter">
							<thead>
								<tr>
									<th style="width:20px;"></th>
									<th style="width:150px;">Tipo</th>
									<th style="width:100px;">Data-hora</th>
									<th style="width:100px;">Quantidade</th>
									<th style="width:100px;">Lançado por</th>
									<th>Observação</th>
								</tr>
							</thead>
							<tbody>
								<?php
								$registros=array();
								$revolucoesIds=array(-1);
								$usuariosIds=array(-1);
								$sql->consult($_p."pacientes_evolucoes","*","where id_paciente=$paciente->id and lixo=0 order by data desc");
								while($x=mysqli_fetch_object($sql->mysqry)) {
									$registros[]=$x;
									$usuariosIds[]=$x->id_usuario;
									$revolucoesIds[]=$x->id;
								}

								$_usuarios=array();
								$sql->consult($_p."usuarios","*","WHERE id IN (".implode(",",$usuariosIds).")");
								while($x=mysqli_fetch_object($sql->mysqry)) {
									$_usuarios[$x->id]=$x;
								}

								$tratamentoProdecimentosIds=array(-1);
								$registrosProcedimentos=array();
								$sql->consult($_p."pacientes_evolucoes_procedimentos","*","where id_paciente=$paciente->id and id_evolucao IN (".implode(",",$revolucoesIds).") and lixo=0 order by data desc");
								while($x=mysqli_fetch_object($sql->mysqry)) {
									$revolucoesIds[]=$x->id;
									$registrosProcedimentos[$x->id_evolucao][]=$x;
								}
								

								$prodecimentosIds=array(-1);
								$_tratamentoProcedimentos=array();
								$sql->consult($_p."pacientes_tratamentos_procedimentos","*","where id IN (".implode(",",$tratamentoProdecimentosIds).")");
								while($x=mysqli_fetch_object($sql->mysqry)) {
									$prodecimentosIds[]=$x->id_procedimento;
									$_tratamentoProcedimentos[$x->id]=$x;
								}


								$_procedimentos=array();
								$sql->consult($_p."parametros_procedimentos","*","where id IN (".implode(",",$prodecimentosIds).")");
								while($x=mysqli_fetch_object($sql->mysqry)) {
									$_procedimentos[$x->id]=$x;
								}

								foreach($registros as $x) {
									if(isset($_tiposEvolucao[$x->id_tipo])) {
										$tipo = $_tiposEvolucao[$x->id_tipo];
									?>
									<tr onclick="document.location.href='<?php echo $tipo->pagina."?form=1&id_paciente=$paciente->id&edita=".$x->id;?>';">
										<td style="font-size:1.25rem;"><i class="iconify" data-icon="<?php echo $tipo->icone;?>"></i></td>
										<td><?php echo utf8_encode($tipo->tituloSingular);?></td>
										<td><?php echo date('d/m/Y',strtotime($x->data));?><br /><span style="color:var(--cinza4)"><?php echo date('H:i',strtotime($x->data));?></span></td>
										<td><?php echo isset($registrosProcedimentos[$x->id])?count($registrosProcedimentos[$x->id]):0;?></td>
										<td><?php echo isset($_usuarios[$x->id_usuario])?utf8_encode($_usuarios[$x->id_usuario]->nome):'-';?></td>
										<td><?php echo utf8_encode($x->obs);?></td>
									</tr>	
									<?php
									}
									/*if(isset($_tiposEvolucao[$x->id_tipo]) and isset($_profissionais[$x->id_profissional])) {
										$tipo = $_tiposEvolucao[$x->id_tipo];
										
										//$profissional = $_profissionais[$x->id_profissional];

										// procedimentos aprovados
										if($tipo->id==2) {
											if(!isset($_tratamentoProcedimentos[$x->id_tratamento_procedimento])) continue;
											$tratamentoProcedimento=$_tratamentoProcedimentos[$x->id_tratamento_procedimento];
											if(!isset($_procedimentos[$tratamentoProcedimento->id_procedimento])) continue;
											$procedimento = $_procedimentos[$tratamentoProcedimento->id_procedimento];
										}


								?>
								<tr>
									<td style="font-size:1.25rem;"><i class="iconify" data-icon="<?php echo $tipo->icone;?>"></i></td>
									<td><?php echo date('d/m/Y',strtotime($x->data));?><br /><span style="color:var(--cinza4)"><?php echo date('H:i',strtotime($x->data));?></span></td>
									<td><?php echo utf8_encode($profissional->nome);?></td>
									<td>
										<strong><?php echo utf8_encode($procedimento->titulo);?></strong>
										<p>
											<?php echo utf8_encode($tratamentoProcedimento->plano);?> - <?php echo utf8_encode($tratamentoProcedimento->opcao);?>
										</p>
									</td>
								</tr>
								<?php
									}*/


								}
								?>
								<tr>

								</tr>
								<?php /*<tr>
									<td style="font-size:1.25rem;"><i class="iconify" data-icon="mdi-pill"></i></td>
									<td>16/03/2020<br /><span style="color:var(--cinza4)">18:06</span></td>
									<td>Dr. Kronner</td>
									<td><strong>Receituário Pós-Operatório</strong></td>
								</tr>
								<tr>
									<td style="font-size:1.25rem;"><i class="iconify" data-icon="mdi-clipboard-check-outline"></i></td>
									<td>16/03/2020<br /><span style="color:var(--cinza4)">18:06</span></td>
									<td>Dr. Kronner</td>
									<td><strong>Atestado Pós-Operatório</strong></td>
								</tr>
								<tr>
									<td style="font-size:1.25rem;"><i class="iconify" data-icon="mdi-progress-check"></i></td>
									<td>16/03/2020<br /><span style="color:var(--cinza4)">18:06</span></td>
									<td>Dr. Kronner</td>
									<td><strong>Prótese Múltipla de Resina / PMMA (43/43)</strong><br />Procedimento finalizado.</td>	
								</tr>
								<tr>
									<td style="font-size:1.25rem;"><i class="iconify" data-icon="mdi-pill"></i></td>
									<td>16/03/2020<br /><span style="color:var(--cinza4)">18:06</span></td>
									<td>Dr. Kronner</td>
									<td><strong>Receituário Pós-Operatório</strong></td>
								</tr>
								<tr>
									<td style="font-size:1.25rem;"><i class="iconify" data-icon="mdi-clipboard-check-outline"></i></td>
									<td>16/03/2020<br /><span style="color:var(--cinza4)">18:06</span></td>
									<td>Dr. Kronner</td>
									<td><strong>Atestado Pós-Operatório</strong></td>
								</tr>
								<tr>
									<td style="font-size:1.25rem;"><i class="iconify" data-icon="mdi-progress-check"></i></td>
									<td>16/03/2020<br /><span style="color:var(--cinza4)">18:06</span></td>
									<td>Dr. Kronner</td>
									<td><strong>Prótese Múltipla de Resina / PMMA (43/43)</strong><br />Procedimento finalizado.</td>									
								</tr>
								<tr>
									<td style="font-size:1.25rem;"><i class="iconify" data-icon="mdi-pill"></i></td>
									<td>16/03/2020<br /><span style="color:var(--cinza4)">18:06</span></td>
									<td>Dr. Kronner</td>
									<td><strong>Receituário Pós-Operatório</strong></td>								
								</tr>
								<tr>
									<td style="font-size:1.25rem;"><i class="iconify" data-icon="mdi-clipboard-check-outline"></i></td>
									<td>16/03/2020<br /><span style="color:var(--cinza4)">18:06</span></td>
									<td>Dr. Kronner</td>
									<td><strong>Atestado Pós-Operatório</strong></td>								
								</tr>
								<tr>
									<td style="font-size:1.25rem;"><i class="iconify" data-icon="mdi-progress-check"></i></td>
									<td>16/03/2020<br /><span style="color:var(--cinza4)">18:06</span></td>
									<td>Dr. Kronner</td>
									<td><strong>Prótese Múltipla de Resina / PMMA (43/43)</strong><br />Procedimento finalizado.</td>								
								</tr>*/?>
							</tbody>
						</table>						
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