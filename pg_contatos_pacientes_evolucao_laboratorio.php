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

		
		<section class="grid">
			<div class="box">

				<?php
				$exibirEvolucaoNav=1;
				require_once("includes/evolucaoMenu.php");
				?>

				<section class="js-evolucao-adicionar" id="evolucao-servicos-de-laboratorio">
					
					<form class="form">
						<div class="grid grid_3">

							<fieldset style="grid-column:span 2">
								<legend><span class="badge">1</span> Selecione os serviços</legend>

								<div class="colunas2">
									<dl>
										<dd>
											<select name="">
												<option value=""></option>
												<option value="">Serviço 1</option>
												<option value="">Serviço 2</option>
											</select>
										</dd>
									</dl>
									<dl>
										<dd><button type="submit" class="button">Adicionar</button></dd>
									</dl>
								</div>

								<div class="reg" style="margin-top:2rem;">

									<a href="javascript:;" class="reg-group">
										<div class="reg-color" style="background-color:palegreen"></div>
										<div class="reg-data" style="flex:0 1 300px">
											<h1>SERVIÇO 1</h1>
											<p>MANDÍBULA - PARTICULAR-SD</p>
										</div>
										<div class="reg-data">
											<p>APROVADO</p>
										</div>									
										<div class="reg-user">
											<span style="background:blueviolet">KP</span>
										</div>
									</a>

									<a href="javascript:;" class="reg-group">
										<div class="reg-color" style="background-color:palegreen"></div>
										<div class="reg-data" style="flex:0 1 300px">
											<h1>SERVIÇO 2</h1>
											<p>MANDÍBULA - PARTICULAR-SD</p>
										</div>
										<div class="reg-data">
											<p>APROVADO</p>
										</div>									
										<div class="reg-user">
											<span style="background:blueviolet">KP</span>
										</div>
									</a>

									<a href="javascript:;" class="reg-group">
										<div class="reg-color" style="background-color:palegreen"></div>
										<div class="reg-data" style="flex:0 1 300px">
											<h1>GENTIVECTOMIA + GENGIVOPLASTIA</h1>
											<p>MANDÍBULA - PARTICULAR-SD</p>
										</div>
										<div class="reg-data">
											<p>APROVADO</p>
										</div>									
										<div class="reg-user">
											<span style="background:blueviolet">KP</span>
										</div>
									</a>

								</div>

							</fieldset>

							<fieldset>
								<legend><span class="badge">2</span> Preencha o histórico</legend>

								<dl style="height:100%;">
									<dd style="height:100%;"><textarea name="" style="height:100%;" class="noupper"></textarea></dd>
								</dl>
							</fieldset>


						</div>
					</form>

				</section>
				

			</div>				
		</section>
			
		</section>
		
<?php
include "includes/footer.php";
?>