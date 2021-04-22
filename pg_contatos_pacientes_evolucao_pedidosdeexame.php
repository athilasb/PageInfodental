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
				require_once("includes/evolucaoMenu.php");
				?>

				<section class="js-evolucao-adicionar" id="evolucao-pedidos-de-exames">
						
					<form class="form">
						<div class="grid grid_3">
							<fieldset>
								<legend><span class="badge">2</span>Cabeçalho do exame</legend>
								
								<dl>
									<dt>Data do Pedido</dt>
									<dd><input type="text" name="" class="datecalendar" /></dd>
								</dl>
								<dl>
									<dt>Clínica Radiológica</dt>
									<dd>
										<select name="">
											<option value="">Clínica 1</option>
											<option value="">Clínica 2</option>
										</select>
									</dd>
								</dl>
								<dl>
									<dt>Cirurgião Dentista</dt>
									<dd>
										<select name="">
											<option value="">Kroner Costa</option>
										</select>
									</dd>
								</dl>
								
							</fieldset>

							<fieldset style="grid-column:span 2">
								<legend><span class="badge">3</span>Selecione os exames</legend>
								<dl>
									<dt>Tipo de Exame</dt>
									<dd>
										<select name="">
											<option value="">Tipo de Exame 1</option>
											<option value="">Tipo de Exame 2</option>
										</select>
									</dd>
								</dl>
								<div class="colunas3">
									<dl>
										<dt>Região</dt>
										<dd>
											<select name="">
												<option value="">1</option>	
												<option value="">2</option>	
												<option value="">3</option>	
											</select>
										</dd>
									</dl>
									<dl class="dl2">
										<dt>Observação</dt>
										<dd><input type="text" name="" value="Tomar 1 comprimido via oral de 8 em 8 horas por 7 dias"><button type="submit" class="button">adicionar</button></dd>
									</dl>
								</div>

								<div class="reg" style="margin-top:2rem;">
									<div class="reg-group">
										<div class="reg-color" style="background-color:palegreen"></div>
										<div class="reg-data">
											<h1>Raio X - 21, 22, 23</h1>
											<p>Enviar por email</p>
										</div>
										<div class="reg-icon">
											<a href=""><i class="iconify" data-icon="bx-bx-trash"></i></a>
										</div>
									</div>
								</div>								
							</fieldset>
						</div>
						<fieldset>
							<legend><span class="badge">4</span> Pré-visualize e edite se necessário</legend>
							<script>
								$(function(){
									var fck_texto = CKEDITOR.replace('texto2',{
						    							filebrowserUploadUrl: '/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Files',
															height: '350',
															width: '100%',
															language: 'pt-br'
														});
									CKFinder.setupCKEditor(fck_texto);
								});
							</script>
							<textarea name="texto" id="texto2" class="noupper" style="height:400px;">
								<h1 style="text-align:center;">Pedido de Exame</h1>
								<p>Atesto para os devidos fins que {NOME PACIENTE} estará dispensado das atividades trabalhistas durante o período de {DIAS ATESTADO} dias a partir da data de {DATA ATESTADO}</p>
							</textarea>
						</fieldset>
					</form>

				</section>
				

			</div>				
		</section>
			
		</section>
		
<?php
include "includes/footer.php";
?>