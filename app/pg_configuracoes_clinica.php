<?php
	include "includes/header.php";
	include "includes/nav.php";

	$_table = $_p."clinica";



	$_dirLogo=$_cloudinaryPath."arqs/clinica/logo/";

	$campos = explode(",","tipo,clinica_nome,instagram,site,email,whatsapp,telefone,endereco,complemento,lat,lng,tipo,razao_social,cnpj,inscricao_estadual,cpf,nome,responsavel_cro,responsavel_cro_uf,responsavel_cro_tipo");

	$values=array();
	foreach($campos as $v) $values[$v]='';
	$values['tipo']='PJ';

	$cnt='';
	$sql->consult($_table,"*","limit 1");
	if($sql->rows) {
		$cnt=mysqli_fetch_object($sql->mysqry);
		$values=$adm->values($campos,$cnt);
	}


	// se nao encontrar registro
	if(empty($cnt)) {
		$sql->add($_table,"tipo='PJ'");
		$sql->consult($_table,"*","where id=$sql->ulid");
		$cnt=mysqli_fetch_object($sql->mysqry);
	}

	if(isset($_POST['acao'])) {
		$vSQL=$adm->vSQL($campos,$_POST);
		$values=$adm->values;

		$vSQL=substr($vSQL,0,strlen($vSQL)-1);
		$vWHERE="where id='".$cnt->id."'";
		$sql->update($_table,$vSQL,$vWHERE);
		$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='update',vsql='".addslashes($vSQL)."',vwhere='".addslashes($vWHERE)."',tabela='".$_table."',id_reg='".$cnt->id."'");

		$jsc->go($_page);
		die();
	}

?>


	<header class="header">
		<div class="header__content content">

			<div class="header__inner1">
				<section class="header-title">
					<h1>Configurações</h1>
				</section>
				<?php
				require_once("includes/menus/menuConfiguracoes.php");
				?>
			</div>
		</div>
	</header>

	<main class="main">
		<div class="main__content content">

			<section class="filter">
				
				<div class="filter-group">
					<div class="filter-title">
						<h1>Configure a clínica</h1>
					</div>
				</div>
			</section>
 	
 	
			<section class="grid">

				<div class="box box-col">

					<?php
					require_once("includes/submenus/subConfiguracoesClinica.php");
					?>

					<div class="box-col__inner1">
				
						<section class="filter">
							<div class="filter-group"></div>
							<div class="filter-group">
								<div class="filter-form form">
									<dl>
										<dd><a href="javascript:;" class="button button_main js-submit"><i class="iconify" data-icon="fluent:checkmark-12-filled"></i> <span>Salvar</span></a></dd>
									</dl>
								</div>
							</div>							
						</section>

						<script src="https://widget.cloudinary.com/v2.0/global/all.js" type="text/javascript"></script> 
						<script type="text/javascript">
							const tipoPessoa = () => {
								if($('input[name=tipo]:checked').val()=="PF") {
									$('.js-cpf').show().find('input').addClass('obg');
									$('.js-cnpj').hide().find('input').removeClass('obg');
								} else {
									$('.js-cpf').hide().find('input').removeClass('obg');
									$('.js-cnpj').show().find('input').addClass('obg');
								}
							}
							var logo = cloudinary.createUploadWidget({
								cloudName: '<?php echo $_cloudinaryCloudName;?>',
								language: 'pt',
								text: <?php echo json_encode($_cloudinaryText);?>,
								multiple: false,
								sources: ["local"],
								folder: '<?php echo $_dirLogo;?>',
								uploadPreset: '<?php echo $_cloudinaryUploadPresent;?>'}, 
								(error, result) => {
									if (!error && result) {
										if(result.event === "success") {
											 $('input[name=cn_logo]').val(result.info.path);
										}
									}
								}
							);
							$(function(){
								tipoPessoa();
								$('input[name=tipo]').click(tipoPessoa);

								$('input[name=whatsapp]').mobilePhoneNumber({allowPhoneWithoutPrefix: '+55'}).bind('country.mobilePhoneNumber', function(echo, country) {
									let countryOut = country || '  ';
									$(this).parent().parent().find('.js-country').html(countryOut);
								}).trigger('keyup');

								$('input[name=telefone]').mobilePhoneNumber({allowPhoneWithoutPrefix: '+55'}).bind('country.mobilePhoneNumber', function(echo, country) {
									let countryOut = country || '  ';
									$(this).parent().parent().find('.js-country').html(countryOut);
								}).trigger('keyup');
							})
						</script>
						<form method="post" class="form formulario-validacao">
							<input type="hidden" name="acao" value="wlib" />

							<fieldset>
								<legend>Dados Gerais</legend>

								<div class="grid grid_3">

									<div style="grid-column:span 2">
										<dl>
											<dd>
												<label><input type="radio" name="tipo" value="PF"<?php echo $values['tipo']=="PF"?" checked":"";?> />Pessoa Física</label>
												<label><input type="radio" name="tipo" value="PJ"<?php echo $values['tipo']=="PJ"?" checked":"";?> />Pessoa Jurídica</label>
											</dd>
										</dl>
										<div class="colunas">
											<dl>
												<dt>Nome</dt>
												<dd><input type="text" name="clinica_nome" value="<?php echo $values['clinica_nome'];?>" class="obg" /></dd>
											</dl>
											<dl>
												<dt>Instagram</dt>
												<dd><input type="text" name="instagram" value="<?php echo $values['instagram'];?>" class="" /></dd>
											</dl>
										</div>
										<div class="colunas">
											<dl>
												<dt>Site</dt>
												<dd><input type="text" name="site" value="<?php echo $values['site'];?>" class="" /></dd>
											</dl>
											<dl>
												<dt>Email</dt>
												<dd><input type="email" name="email" value="<?php echo $values['email'];?>" class="obg email" /></dd>
											</dl>
										</div>
										<div class="colunas">
											<dl>
												<dt>WhatsApp</dt>
												<dd class="form-comp">
													<span class="js-country">BR</span><input type="text" name="whatsapp" class="obg " attern="\d*" x-autocompletetype="tel" value="<?php echo $values['whatsapp'];?>" />
												</dd>
											</dl>
											<dl>
												<dt>Telefone</dt>
												<dd class="form-comp">
													<span class="js-country">BR</span><input type="text" name="telefone" class="obg " attern="\d*" x-autocompletetype="tel" value="<?php echo $values['telefone'];?>" />
												</dd>
											</dl>
										</div>
										<div class="colunas">
											<dl>
												<dt>Endereço</dt>
												<dd><input type="text" name="endereco" value="<?php echo $values['endereco'];?>" class="" /></dd>
											</dl>
											<dl>
												<dt>Complemento</dt>
												<dd><input type="text" name="complemento" value="<?php echo $values['complemento'];?>" class="" /></dd>
											</dl>
										</div>

										<div class="js-cpf">
											<div class="colunas">
												<dl>
													<dt>CPF</dt>
													<dd><input type="text" name="cpf" value="<?php echo $values['cpf'];?>" class="cpf" /></dd>
												</dl>
												<dl>
													<dt>Nome do Responsável Técnico</dt>
													<dd><input type="text" name="nome" value="<?php echo $values['nome'];?>" class="" /></dd>
												</dl>
											</div>
										</div>

										<div class="js-cnpj" style="display:none;">
											<div class="colunas3">
												<dl>
													<dt>Razão Social</dt>
													<dd><input type="text" name="razao_social" value="<?php echo $values['razao_social'];?>" class="" /></dd>
												</dl>
												<dl>
													<dt>CNPJ</dt>
													<dd><input type="text" name="cnpj" value="<?php echo $values['cnpj'];?>" class="cnpj" /></dd>
												</dl>
												<dl>
													<dt>Inscrição Estadual</dt>
													<dd><input type="text" name="inscricao_estadual" value="<?php echo $values['inscricao_estadual'];?>" class="" /></dd>
												</dl>
											</div>
										</div>
										<div class="colunas3">
												<dl>
													<dt>CRO Responsável Técnico</dt>
													<dd><input type="text" name="responsavel_cro" value="<?php echo $values['responsavel_cro'];?>" class="obg" /></dd>
												</dl>
												<dl>
													<dt>UF do CRO</dt>
													<dd>
														<select name="responsavel_cro_uf">
															<option value="">-</option>
															<?php
															foreach($_optUF as $uf=>$titulo) {
																echo '<option value="'.$uf.'"'.($values['responsavel_cro_uf']==$uf?' selected':'').'>'.$titulo.'</option>';
															}
															?>
														</select>
													</dd>
												</dl>
												<dl>
													<dt>Tipo do CRO</dt>
													<dd>
														<select name="responsavel_cro_tipo">
															<option value="">-</option>
															<?php
															foreach($_tipoCRO as $k=>$v) {
																echo '<option value="'.$k.'"'.(($values['responsavel_cro_tipo']==$k)?' selected':'').'>'.$v.'</option>';
															}
															?>
														</select>
													</dd>
												</dl>
											</div>
									</div>

									<div>
										<div class="form-image">
											<img src="img/logo-cliente.png" alt="" width="484" height="68" />
										</div>
										<dl>
											<dt>Logotipo</dt>
											<dd>
												<input type="file" name="" />
												<input type="hidden" name="cn_logo" />
											</dd>
										</dl>
									</div>
								</div>
							</fieldset>

							<fieldset>
								<legend>Certificação Digital</legend>

								<div class="colunas3">
									<dl class="dl2">
										<dt>Certificado A1</dt>
										<dd class="form-comp form-comp_pos"><input type="text" name="" placeholder="" /><a href=""><i class="iconify" data-icon="fluent:add-circle-24-regular"></i></a></dd>
									</dl>
									<dl>
										<dt>Senha</dt>
										<dd><input type="text" name="" /></dd>
									</dl>
								</div>
								<div class="colunas3">
									<dl>
										<dt>Status do Certificado</dt>
										<dd><label style="color:green;"><i class="iconify" data-icon="fluent:checkmark-circle-12-regular"></i> <strong>Ativo</strong></label></dd>
									</dl>
									<dl>
										<dt>Data de Vencimento</dt>
										<dd><label>10/10/2022</label></dd>
									</dl>
								</div>
							</fieldset>



						</form>

					</div>
					
				</div>

			</section>
		
		</div>
	</main>

<?php 
include "includes/footer.php";
?>	