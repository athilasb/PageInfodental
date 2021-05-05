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
								<a href="<?php echo $_page."?".$url;?>"><i class="iconify" data-icon="bx-bx-left-arrow-alt"></i></a>
							</div>
						</div>
						<div class="filter-group">
							<div class="filter-input">
								<input type="text" name="" placeholder="título do serviço" />								
							</div>
						</div>
						<div class="filter-group">
							<div class="filter-input">
								<select name=""><option value="">laboratório</option></select>
							</div>
						</div>
						<div class="filter-group">
							<div class="filter-input">
								<select name=""><option value="">cirurgião dentista</option></select>
							</div>
						</div>
						<div class="filter-group filter-group_right">
							<div class="filter-data">
								<h1>Valor Total</h1>
								<h2>R$ 3.540,00</h2>
							</div>					
						</div>		
						<div class="filter-group">
							<div class="filter-button">
								<a href="javascript:;"><i class="iconify" data-icon="bx-bx-trash"></i></a>
								<a href="javascript:;"><i class="iconify" data-icon="bx-bx-printer"></i></a>
								<a href="javascript:;" class="azul"><i class="iconify" data-icon="bx-bx-check"></i><span>salvar</span></a>
								<a href="javascript:;" class="verde"><i class="iconify" data-icon="bx-bxs-paper-plane"></i><span>enviar para laboratório</span></a>
							</div>
						</div>
					</div>

					<form class="form">
						<div class="grid grid_2">
							<fieldset>
								<legend><span class="badge">1</span>Selecione os serviços</legend>
								<dl>
									<dt>Procedimento</dt>
									<dd>
										<select name="" class="chosen">
											<option value="">Porcelana Injetada</option>
											<option value="">Porcelana Fresada</option>
											<option value="">Scan Service</option>
										</select>
									</dd>
								</dl>
								<dl>
									<dt>Dente(s)</dt>
									<dd>
										<select name="" class="chosen">
											<option value="">21</option>
											<option value="">22</option>
											<option value="">23</option>
										</select>
									</dd>
								</dl>
								<dl>
									<dt>Descrição</dt>
									<dd>
										<input type="text" name="" />
										<button type="submit" class="button">adicionar</button>
									</dd>
								</dl>

								<div class="reg" style="margin-top:2rem;">

									<a href="javascript:;" class="reg-group">
										<div class="reg-color" style="background-color:palegreen"></div>
										<div class="reg-data" style="flex:0 1 300px">
											<h1>PORCELANA INJETADA</h1>
											<p>21, 22, 24</p>
										</div>
										<div class="reg-data">
											<p>R$ 1.022,00</p>
										</div>										
									</a>

									<a href="javascript:;" class="reg-group">
										<div class="reg-color" style="background-color:palegreen"></div>
										<div class="reg-data" style="flex:0 1 300px">
											<h1>SCAN SERVICE</h1>
											<p>Enviar com urgência</p>
										</div>
										<div class="reg-data">
											<p>R$ 600,00</p>
										</div>										
									</a>
								</div>

							</fieldset>
							<fieldset>
								<legend><span class="badge">2</span>Descrição Geral</legend>
								<dl style="height:100%;">
									<dd style="height:100%;"><textarea name="" style="height:100%;" class="noupper"></textarea></dd>
								</dl>
							</fieldset>
							<fieldset>
								<legend><span class="badge">3</span>Adicione arquivos</legend>
								<div class="colunas">
									<dl>
										<dt>Localizar</dt>
										<dd><input type="file" name="" /></dd>
									</dl>
									<dl>
										<dt>Conteúdo</dt>
										<dd>
											<select name="">
												<optgroup label="Modelos Digitais">
													<option value="">Modelo de Trabalho</option>
													<option value="">Modelo de Referência</option>
													<option value="">Modelo Antagonista</option>
													<option value="">Outro modelo digital...</option>
												</optgroup>
												<optgroup label="Fotos">
													<option value="">Sorriso</option>
													<option value="">Cor do Substrato</option>
													<option value="">Cor final</option>												
												</optgroup>
												<optgroup label="Outros arquivos">
													<option value="">Outro...</option>										
												</optgroup>
											</select>
											<button type="submit" class="button">enviar</button>
										</dd>
									</dl>
								</div>
								<div class="reg" style="margin-top:2rem;">

									<div class="reg-group">
										<div class="reg-color" style="background-color:palegreen"></div>
										<div class="reg-data" style="flex:0 1 300px">
											<h1>fotos.zip</h1>
										</div>
										<div class="reg-data">
											<p>Fotos / Sorriso</p>
										</div>
										<div class="reg-icon">
											<a href=""><i class="iconify" data-icon="bx-bx-download"></i></a>
											<a href=""><i class="iconify" data-icon="bx-bx-trash"></i></a>
										</div>
									</div>
									<div class="reg-group">
										<div class="reg-color" style="background-color:palegreen"></div>
										<div class="reg-data" style="flex:0 1 300px">
											<h1>modelo_antagonista.crc</h1>
										</div>
										<div class="reg-data">
											<p>Modelos / Modelo Antagonista</p>
										</div>
										<div class="reg-icon">
											<a href=""><i class="iconify" data-icon="bx-bx-download"></i></a>
											<a href=""><i class="iconify" data-icon="bx-bx-trash"></i></a>
										</div>
									</div>

								</div>
							</fieldset>
							<fieldset>
								<legend><span class="badge">4</span>Checklist</legend>

								<div class="colunas">
									<dl>
										<dt>Adicionar</dt>
										<dd>
											<select name="">
												<optgroup label="Modelos">
													<option value="">Modelo de Trabalho</option>
													<option value="">Modelo de Referência</option>
													<option value="">Modelo Antagonista</option>
													<option value="">Outro...</option>
												</optgroup>
												<optgroup label="Componentes">
													<option value="">Análogos</option>
													<option value="">Parafusos</option>
													<option value="">Links</option>												
													<option value="">Transfer</option>												
												</optgroup>
												<optgroup label="Fotos">
													<option value="">Sorriso</option>
													<option value="">Cor do Substrato</option>
													<option value="">Cor final</option>												
												</optgroup>
												
											</select>
										</dd>
									</dl>
									<dl>
										<dt>Tipo</dt>
										<dd>
											<label><input type="radio" name="tipo" value="digital" checked />digital</label>
											<label><input type="radio" name="tipo" value="analógico" />analógico</label>
											<button type="submit" class="button">adicionar</button>
										</dd>
									</dl>
								</div>

								<div class="reg" style="margin-top:2rem;">

									<div class="reg-group">
										<div class="reg-color" style="background-color:red"></div>
										<div class="reg-data" style="flex:0 1 300px">
											<h1>Fotos / Sorriso</h1>
											<p>Digital</p>
										</div>
										<div class="reg-icon">
											<a href=""><i class="iconify" data-icon="bx-bx-trash"></i></a>
										</div>
									</div>
									<div class="reg-group">
										<div class="reg-color" style="background-color:red"></div>
										<div class="reg-data" style="flex:0 1 300px">
											<h1>Modelos / Modelo Antagonista</h1>
											<p>Digital</p>
										</div>
										<div class="reg-icon">
											<a href=""><i class="iconify" data-icon="bx-bx-trash"></i></a>
										</div>
									</div>
									<div class="reg-group">
										<div class="reg-color" style="background-color:blue"></div>
										<div class="reg-data" style="flex:0 1 300px">
											<h1>Componentes / Análogos</h1>
											<p>Analógico</p>
										</div>
										<div class="reg-icon">
											<a href=""><i class="iconify" data-icon="bx-bx-trash"></i></a>
										</div>
									</div>
									<div class="reg-group">
										<div class="reg-color" style="background-color:blue"></div>
										<div class="reg-data" style="flex:0 1 300px">
											<h1>Componentes / Parafusos</h1>
											<p>Analógico</p>
										</div>
										<div class="reg-icon">
											<a href=""><i class="iconify" data-icon="bx-bx-trash"></i></a>
										</div>
									</div>
								</div>

							</fieldset>
						</div>		
					</form>
				
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
								<a href="<?php echo $_page."?form=1&$url";?>" class="verde"><i class="iconify" data-icon="bx-bx-plus"></i><span>nova ordem de serviço</span></a>
							</div>
						</div>				
					</div>

					<div class="reg">
						
						<a href="javascript:;" class="reg-group">
							<div class="reg-color" style="background-color:var(--cinza2);"></div>
							<div class="reg-data" style="flex:0 1 200px">
								<h1>ORDEM DE SERVIÇO 1</h1>
								<p>Aberto em 04/04/2021</p>
							</div>
							<div class="reg-steps" style="margin:0 auto;">
								<div class="reg-steps__item active">
									<h1>1</h1>
									<p>Ordem Aberta</p>									
								</div>
								<div class="reg-steps__item active">
									<h1>2</h1>
									<p>Aprovada Laboratório</p>									
								</div>
								<div class="reg-steps__item active">
									<h1 style="background:var(--amarelo);">3</h1>
									<p>Devolvido</p>									
								</div>
								<div class="reg-steps__item">
									<h1>4</h1>
									<p>Recebido</p>									
								</div>
								<div class="reg-steps__item">
									<h1>5</h1>
									<p>Finalizado</p>
								</div>
							</div>							
							<div class="reg-user">
								<span style="background:#44FF00">KM</span>
							</div>
						</a>

						<a href="javascript:;" class="reg-group">
							<div class="reg-color" style="background-color:var(--cinza2);"></div>
							<div class="reg-data" style="flex:0 1 200px">
								<h1>ORDEM DE SERVIÇO 1</h1>
								<p>Aberto em 04/04/2021</p>
							</div>
							<div class="reg-steps" style="margin:0 auto;">
								<div class="reg-steps__item active">
									<h1>1</h1>
									<p>Ordem Aberta</p>									
								</div>
								<div class="reg-steps__item active">
									<h1>2</h1>
									<p>Aprovada Laboratório</p>									
								</div>
								<div class="reg-steps__item active">
									<h1>3</h1>
									<p>Aceito Laboratório</p>									
								</div>
								<div class="reg-steps__item active">
									<h1>4</h1>
									<p>Recebido</p>									
								</div>
								<div class="reg-steps__item active">
									<h1>5</h1>
									<p>Finalizado</p>
								</div>
							</div>							
							<div class="reg-user">
								<span style="background:#44FF00">KM</span>
							</div>
						</a>

						<a href="javascript:;" class="reg-group">
							<div class="reg-color" style="background-color:var(--cinza2);"></div>
							<div class="reg-data" style="flex:0 1 200px">
								<h1>ORDEM DE SERVIÇO 1</h1>
								<p>Aberto em 04/04/2021</p>
							</div>
							<div class="reg-steps" style="margin:0 auto;">
								<div class="reg-steps__item active">
									<h1>1</h1>
									<p>Ordem Aberta</p>									
								</div>
								<div class="reg-steps__item active">
									<h1>2</h1>
									<p>Aprovada Laboratório</p>									
								</div>
								<div class="reg-steps__item active">
									<h1 style="background:var(--vermelho);">3</h1>
									<p>Recusado</p>									
								</div>
								<div class="reg-steps__item">
									<h1>4</h1>
									<p>Recebido</p>									
								</div>
								<div class="reg-steps__item">
									<h1>5</h1>
									<p>Finalizado</p>
								</div>
							</div>							
							<div class="reg-user">
								<span style="background:#44FF00">KM</span>
							</div>
						</a>

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