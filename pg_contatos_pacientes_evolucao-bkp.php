<?php
	if(isset($_POST['ajax'])) {
		require_once("lib/conf.php");	
		require_once("usuarios/checa.php");

		$sql = new Mysql();

		$rtn = array();
		if($_POST['ajax']=="indicacoesLista") {
			$indicacao='';
			if(isset($_POST['id_indicacao']) and is_numeric($_POST['id_indicacao'])) {
				$sql->consult($_p."parametros_indicacoes","*","where id='".addslashes($_POST['id_indicacao'])."' and lixo=0");
				if($sql->rows) {
					$indicacao=mysqli_fetch_object($sql->mysqry);
				}
			}

			if(is_object($indicacao)) {
				$sql->consult($_p."parametros_indicacoes_listas","*","where id_indicacao=$indicacao->id and lixo=0 order by titulo asc");
				$indicacoes=array();
				while($x=mysqli_fetch_object($sql->mysqry)) {
					$indicacoes[]=array('id'=>$x->id,
									'titulo'=>utf8_encode($x->titulo));
				}

				$rtn=array('success'=>true,'indicacoes'=>$indicacoes);
			} else {
				$rtn=array('success'=>false,'error'=>'Indicação não definida!');
			}
		}

		header("Content-type: application/json");
		echo json_encode($rtn);
		die();
	}
	if(isset($_GET['ajax'])) {
		if($_GET['ajax']=="profissao") {
			if(isset($_GET['id_profissao']) and is_numeric($_GET['id_profissao'])) {
				$_GET['edita']=$_GET['id_profissao'];
				$_GET['form']=1;
			}
			require_once("pg_parametros_profissoes.php");

		}

		die();
	}

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
					
					<div class="filtros">
						<h1 class="filtros__titulo">Adicionar Evolução</h1>
						<div class="filtros-acoes">
							<a href="<?php echo $_page."?".$url;?>"><i class="iconify" data-icon="bx-bx-left-arrow-alt"></i></a>
							<a href="javascript:;" class="principal btn-submit"><i class="iconify" data-icon="bx-bx-check"></i></a>
						</div>
					</div>
					
					<form method="post" class="form formulario-validacao"  autocomplete="off" enctype="multipart/form-data">
						<dl>
							<dd>
								<label><input type="radio" name="tipo" value="anamnese" /> Anamnese</label>
								<label><input type="radio" name="tipo" value="procedimento" /> Procedimento</label>
								<label><input type="radio" name="tipo" value="atestado" /> Atestado</label>
								<label><input type="radio" name="tipo" value="laboratorio" /> Serviços de Laboratório</label>
								<label><input type="radio" name="tipo" value="exame" /> Pedidos de Exames</label>
								<label><input type="radio" name="tipo" value="receituario" /> Receituário</label>
								<label><input type="radio" name="tipo" value="proximaConsulta" /> Próxima consulta</label>
							</dd>
						</dl>

						<fieldset style="display:none;" class="js-box js-box-anamnese">
							<legend>Anamnese</legend>

							<div class="colunas5">
								<dl>
									<dt>Tipo de Anamnese</dt>
									<dd><select></select></dd>
								</dl>
							</div>
						</fieldset>

						<fieldset style="display:none;" class="js-box js-box-procedimento">
							<legend>Procedimento</legend>

							<div class="colunas4">
								<dl class="dl3">
									<dt>Selecione o Procedimento</dt>
									<dd><select></select></dd>
								</dl>
								<dl>
									<dt>&nbsp;</dt>
									<dd><a href="javascript:;" class="button button__sec js-plano-salvar"><i class="iconify" data-icon="bx-bx-check"></i></a></dd>
								</dl>
							</div>

							<div class="registros">
								<table class="tablesorter js-planos-table">
									<thead>
										<tr>
											<th>Nº</th>
											<th>Data e Hora</th>
											<th>Procedimento - Região</th>
											<th>Cirurgião Dentista</th>
											<th>Status</th>
											<th>Observação</th>
											<th style="width:120px"></th>
										</tr>
									</thead>
									<tbody>
										<tr>
											<td>1</td>
											<td>18/11/2020</td>
											<td>Exodontia - 24</td>
											<td>Kroner</td>
											<td>Finalizado</td>
											<td>Caso complicado</td>
											<td></td>
										</tr>
									</tbody>
								</table>
							</div>
						</fieldset>

						<fieldset style="display:none;" class="js-box js-box-atestado">
							<legend>Atestado</legend>

							<div class="colunas7">
								<dl>
									<dt>Data e Hora</dt>
									<dd><input type="text" /></dd>
								</dl>
								<dl>
									<dt>Tipo do Atestado</dt>
									<dd>
										<select>
											<option>Acompanhamento</option>
											<option>Comparecimento</option>
											<option>Odontológico</option>
										</select>
									</dd>
								</dl>
								<dl>
									<dt>Finalidade</dt>
									<dd>
										<select>
											<option>Trabalhista</option>
											<option>Escolar</option>
											<option>Esportiva</option>
										</select>
									</dd>
								</dl>
								<dl>
									<dt>Cirurgião Dentista</dt>
									<dd><select></select></dd>
								</dl>
								<dl class="dl3">
									<dt>CID/Procedimento</dt>
									<dd><input type="text" /></dd>
								</dl>
								<dl>
									<dt>&nbsp;</dt>
									<dd><a href="javascript:;" class="button button__sec js-plano-salvar"><i class="iconify" data-icon="bx-bx-check"></i></a></dd>
								</dl>
							</div>


							<textarea style="height: 500px;">
								
							</textarea>
						</fieldset>

						<fieldset style="display:none;" class="js-box js-box-laboratorio">
							<legend>Serviço de Laboratório</legend>

							<div class="colunas5">
								<dl>
									<dt>Laboratório</dt>
									<dd>
										<select>
											<option>Acompanhamento</option>
											<option>Comparecimento</option>
											<option>Odontológico</option>
										</select>
									</dd>
								</dl>
								<dl>
									<dt>Data de Envio</dt>
									<dd><input type="text" /></dd>
								</dl>
								<dl>
									<dt>Data Prevista</dt>
									<dd><input type="text" /></dd>
								</dl>
								<dl>
									<dt>Cirurgião Dentista</dt>
									<dd><select></select></dd>
								</dl>
							</div>
							<div class="colunas5">
								
								<dl class="dl2">
									<dt>Serviço Protético</dt>
									<dd>
										<select name="teste[]" class="chosen" multiple>
											<option>Acompanhamento</option>
											<option>Comparecimento</option>
											<option>Odontológico</option>
										</select>
									</dd>
								</dl>
								<dl class="dl2">
									<dt>Região</dt>
									<dd>
										<select name="teste[]" class="chosen" multiple>
											<option>Acompanhamento</option>
											<option>Comparecimento</option>
											<option>Odontológico</option>
										</select>
									</dd>
								</dl>
								<dl>
									<dt>&nbsp;</dt>
									<dd><a href="javascript:;" class="button button__sec js-plano-salvar"><i class="iconify" data-icon="bx-bx-check"></i></a></dd>
								</dl>
							</div>

							<div class="registros">
								<table class="tablesorter js-planos-table">
									<thead>
										<tr>
											<th>Nº</th>
											<th>Trabalho</th>
											<th>Região</th>
											<th>Observação</th>
											<th>Status</th>
											<th style="width:120px"></th>
										</tr>
									</thead>
									<tbody>
									</tbody>
								</table>
							</div>
						</fieldset>

						<fieldset style="display:none;" class="js-box js-box-exame">
							<legend>Pedidos de Exames</legend>

							<div class="colunas5">
								<dl>
									<dt>Clínica de Radiologia</dt>
									<dd>
										<select>
											<option></option>
										</select>
									</dd>
								</dl>
								<dl>
									<dt>Data do Pedido</dt>
									<dd><input type="text" /></dd>
								</dl>
								<dl>
									<dt>Cirurgião Dentista</dt>
									<dd><select></select></dd>
								</dl>
							</div>
							<div class="colunas5">
								<dl class="dl2">
									<dt>Tipo de Exame</dt>
									<dd>
										<select>
											<option></option>
										</select>
									</dd>
								</dl>
								<dl class="dl2">
									<dt>Região</dt>
									<dd>
										<select>
											<option></option>
										</select>
									</dd>
								</dl>
								<dl>
									<dt>&nbsp;</dt>
									<dd><a href="javascript:;" class="button button__sec js-plano-salvar"><i class="iconify" data-icon="bx-bx-check"></i></a></dd>
								</dl>
							</div>

							<div class="registros">
								<table class="tablesorter js-planos-table">
									<thead>
										<tr>
											<th>Nº</th>
											<th>Exame</th>
											<th>Região</th>
											<th>Observação</th>
											<th>Status</th>
											<th style="width:120px"></th>
										</tr>
									</thead>
									<tbody>
									</tbody>
								</table>
							</div>
						</fieldset>

						<fieldset style="display:none;" class="js-box js-box-receituario">
							<legend>Receituário</legend>

							<div class="colunas4">
								<dl>
									<dt>Data e Hora</dt>
									<dd><input type="text" /></dd>
								</dl>
								<dl>
									<dt>Tipo de Uso</dt>
									<dd><select></select></dd>
								</dl>
								<dl>
									<dt>Medicacão</dt>
									<dd>
										<select style="width:80%;float:left;" class="chosen">
											<option value=""></option>
										</select>
											<a href="box/boxEspecialidades.php" class="button button__sec tooltip" data-fancybox data-type="ajax" title="Gerenciar Especialidades" style="float:left;margin-left:5px;"><span class="iconify" data-icon="octicon:gear"></span></a>
									</dd>
								</dl>
								<dl>
									<dt>Cirurgião Dentista</dt>
									<dd><input type="text" /></dd>
								</dl>
								<dl>
									<dt>&nbsp;</dt>
									<dd><a href="javascript:;" class="button button__sec js-plano-salvar"><i class="iconify" data-icon="bx-bx-check"></i></a></dd>
								</dl>
							</div>

							<div class="registros">
								<table class="tablesorter js-planos-table">
									<thead>
										<tr>
											<th>Nº</th>
											<th>Remédio</th>
											<th>Qtd.</th>
											<th>Medida</th>
											<th>Posologia</th>
											<th style="width:120px"></th>
										</tr>
									</thead>
									<tbody>
									</tbody>
								</table>
							</div>
						</fieldset>

						<fieldset style="display:none;" class="js-box js-box-proximaConsulta">
							<legend>Próxima Consulta</legend>

							<div class="colunas4">
								<dl>
									<dt>Qtd. dias</dt>
									<dd><input type="text" /></dd>
								</dl>
								<dl>
									<dt>Tempo de Atendimento</dt>
									<dd><select></select></dd>
								</dl>
								<dl>
									<dt>Cirurgião Dentista</dt>
									<dd><input type="text" /></dd>
								</dl>
								<dl>
									<dt>Procedimentos em aberto</dt>
									<dd>
										<select name="por[]" class="chosen" multiple="">
											<option></option>
											<option>1</option>
											<option>2</option>
											<option>3</option>
										</select>
									</dd>
								</dl>
								<dl>
									<dt>&nbsp;</dt>
									<dd><a href="javascript:;" class="button button__sec js-plano-salvar"><i class="iconify" data-icon="bx-bx-check"></i></a></dd>
								</dl>
							</div>

							<div class="registros">
								<table class="tablesorter js-planos-table">
									<thead>
										<tr>
											<th>Nº</th>
											<th>Remédio</th>
											<th>Qtd.</th>
											<th>Medida</th>
											<th>Posologia</th>
											<th style="width:120px"></th>
										</tr>
									</thead>
									<tbody>
									</tbody>
								</table>
							</div>
						</fieldset>
					</form>

				</div>				
			</section>
		<?php
		} else {
		?>
			<section class="grid">
				<section class="box">

					<div class="filtros">
						<h1 class="filtros__titulo">Evolução</h1>
						<div class="filtros-acoes">
							<a href="<?php echo $_page."?form=1&$url";?>" data-padding="0" class="principal tooltip" title="Adicionar"><i class="iconify" data-icon="bx-bx-plus"></i></a>
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