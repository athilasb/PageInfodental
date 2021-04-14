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
					
					<div class="filter">

						<div class="filter-group">
							<div class="filter-button">
								<a href="javascript:;"><i class="iconify" data-icon="bx-bx-left-arrow-alt"></i></a>
							</div>
						</div>

						<div class="filter-group">
							<div class="filter-title">
								<span class="badge">1</span> Escolha o tipo de evolução
							</div>
						</div>

					</div>

					<div class="filtros">
						
						<div class="filtros-acoes">
							<a href="<?php echo $_page."?".$url;?>"><i class="iconify" data-icon="bx-bx-left-arrow-alt"></i></a>
						</div>
					</div>
					<style type="text/css">
						.div-evolucoes a {
							width:265px;
							text-align: center;
							border: 1px solid #CCC;
							margin: 10px;
							padding:30px;
							float:left;
							background:#efefef;
							border-radius: 10px;
							box-shadow: 0 2px 4px rgb(0 0 0 / 15%);
						}
					</style>
					
					<div class="div-evolucoes">
						<a href="pg_contatos_pacientes_evolucao-anamnese.php"><span class="iconify" data-icon="octicon:checklist-16" data-inline="false" data-width="30"></span><br />Anamnese</a>
						<a href="pg_contatos_pacientes_evolucao-procedimentos.php?id_paciente=<?php echo $paciente->id;?>"><span class="iconify" data-icon="octicon:checklist-16" data-inline="false" data-width="30"></span><br />Precedimentos Aprovados</a>
						<a href="pg_contatos_pacientes_evolucao-avulso.php"><span class="iconify" data-icon="octicon:checklist-16" data-inline="false" data-width="30"></span><br />Procedimentos Avulsos</a>
						<a href="pg_contatos_pacientes_evolucao-atestado.php"><span class="iconify" data-icon="octicon:checklist-16" data-inline="false" data-width="30"></span><br />Atestado</a>
						<a href="pg_contatos_pacientes_evolucao-laboratorio.php"><span class="iconify" data-icon="octicon:checklist-16" data-inline="false" data-width="30"></span><br />Serviços de Laboratório</a>
						<a href="pg_contatos_pacientes_evolucao-exames.php"><span class="iconify" data-icon="octicon:checklist-16" data-inline="false" data-width="30"></span><br />Pedidos de Exames</a>
						<a href="pg_contatos_pacientes_evolucao-receituario.php"><span class="iconify" data-icon="octicon:checklist-16" data-inline="false" data-width="30"></span><br />Receituário</a>
						<a href="pg_contatos_pacientes_evolucao-consulta.php"><span class="iconify" data-icon="octicon:checklist-16" data-inline="false" data-width="30"></span><br />Próxima Consulta</a>
					</div>


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
									<th>Tipo</th>
									<th>Data-hora</th>
									<th>Profissional</th>
									<th>Descrição</th>
								</tr>
							</thead>
							<tbody>
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