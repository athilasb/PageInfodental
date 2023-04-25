<?php
	if(isset($_POST['ajax'])) {
		require_once("lib/conf.php");	
		require_once("usuarios/checa.php");

		$sql = new Mysql();

		$rtn = array();
		if($_POST['ajax']=="logo") {

			
			$sql->update($_p."clinica","cn_logo='".addslashes($_POST['logo'])."'","");
			$rtn=array('success'=>true);
			

		}

		header("Content-type: application/json");
		echo json_encode($rtn);
		die();
	}
	include "includes/header.php";
	include "includes/nav.php";
	if($usr->tipo!="admin" and !in_array("configuracoes",$_usuariosPermissoes)) {
		$jsc->jAlert("Você não tem permissão para acessar esta área!","erro","document.location.href='dashboard.php'");
		die();
	}

	$_table = $_p."clinica";



	$_dirLogo=$_cloudinaryPath."arqs/clinica/logo/";

	$campos = explode(",","tipo,clinica_nome,instagram,site,email,whatsapp,telefone,endereco,complemento,lat,lng,tipo,razao_social,cnpj,inscricao_estadual,cpf,nome,responsavel_cro,responsavel_cro_uf,responsavel_cro_tipo,politica_multas,politica_juros");

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
							var cloudinaryURL = '<?php echo $_cloudinaryURL;?>';
							const tipoPessoa = () => {
								if($('input[name=tipo]:checked').val()=="PF") {
									$('.js-cpf').show().find('input');
									$('.js-cnpj').hide().find('input')
								} else {
									$('.js-cpf').hide().find('input');
									$('.js-cnpj').show().find('input');
								}
							}
							var logo = cloudinary.createUploadWidget({
								cloudName: '<?php echo $_cloudinaryCloudName;?>',
								language: 'pt',
								button_caption:'a',
								text: <?php echo json_encode($_cloudinaryText);?>,
								multiple: false,
								sources: ["local"],
								folder: '<?php echo $_dirLogo;?>',
								uploadPreset: '<?php echo $_cloudinaryUploadPresent;?>'}, 
								(error, result) => {
									if (!error && result) {
										if(result.event === "success") {
											$('input[name=cn_logo]').val(result.info.path);
											
											data = `ajax=logo&logo=${result.info.path}`;
											$.ajax({
												type:"POST",
												data:data,
												success:function(rtn) {
													$(".form-image img").attr('src',`${cloudinaryURL}c_thumb,w_600/${result.info.path}`)
												}
											});
											//console.log(`${cloudinaryURL}c_thumb,w_600/${result.info.path}`)
											$('input[name=foto_cn]').val(result.info.path);
										}
									}
								}
							);
							$(function(){

								document.getElementById("logo").addEventListener("click", function(){
								    logo.open();
								}, false);

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
							<?php /*<button style="display:none;"></button>*/?>
							<!-- DADOS GERAIS  -->
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
												<dd><input type="email" name="email" value="<?php echo $values['email'];?>" class="email" /></dd>
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
													<span class="js-country">BR</span><input type="text" name="telefone" class=" " attern="\d*" x-autocompletetype="tel" value="<?php echo $values['telefone'];?>" />
												</dd>
											</dl>
										</div>
										<script>
											var marker = '';
											var map = '';
											var position = '';
											var positionEndereco = '';
											var el = document.getElementById("geolocation");
											var location_timeout = '';
											var geocoder = '';
											var enderecoObj = {};
											var enderecos = [];	
											var lat = `-16.688304`;
											var lng = `-49.267055`;

											function initMap() {
												let options = {componentRestrictions: {country: "bra"}}
												var input = document.getElementById('search');

												var autocomplete = new google.maps.places.Autocomplete(input,options);
												geocoder = new google.maps.Geocoder();

												autocomplete.addListener('place_changed', function() {

													var result = autocomplete.getPlace();
													lat = result.geometry.location.lat();
													lng = result.geometry.location.lng();
													$('input[name=lat]').val(lat);
													$('input[name=lng]').val(lng);

													let logradouro = '';
													let numero = '';
													let bairro = '';
													let cep = '';
													let cidade = '';
													let estado = '';
													let pais = '';
													let descricao = '';

													enderecoObj = { logradouro, numero, bairro, cep, cidade, estado, pais, descricao, lat, lng }

													$('input[name=lat]').val(enderecoObj.lat);
													$('input[name=lng]').val(enderecoObj.lng);
												});

											}	
										</script>
										<script async src="https://maps.googleapis.com/maps/api/js?key=<?php echo $_googleMapsKey;?>&libraries=places&callback=initMap">
										</script>
										<div class="colunas">
											<dl>
												<dt>Endereço</dt>
												<dd>
													<input type="text" name="endereco" value="<?php echo $values['endereco'];?>" class="" id="search" />

													<input type="hidden" name="lat" />
													<input type="hidden" name="lng" />
												</dd>
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
													<div class="js-cpf-dd form-alert"></div>
												</dl>
												<script type="text/javascript">
													$(function(){
														$('input[name=cpf]').change(function(){
															if(validarCPF($(this).val())) {
																$('.js-cpf-dd').hide();
															} else {

																$('.js-cpf-dd').html(`<i class="iconify" data-icon="fluent:info-16-regular"></i> CPF inválido!`).show();
															}
														})
													})
												</script>
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
													<div class="js-cnpj-dd form-alert"></div>
												</dl>
												<script type="text/javascript">
													$(function(){
														$('input[name=cnpj]').change(function(){
															if(validarCNPJ($(this).val())) {
																$('.js-cnpj-dd').hide();
															} else {

																$('.js-cnpj-dd').html(`<i class="iconify" data-icon="fluent:info-16-regular"></i> CNPJ inválido!`).show();
															}
														})
													})
												</script>
												<dl>
													<dt>Inscrição Municipal</dt>
													<dd><input type="text" name="inscricao_estadual" value="<?php echo $values['inscricao_estadual'];?>" class="" /></dd>
												</dl>
											</div>
										</div>
										<div class="colunas3">
												<dl>
													<dt>CRO Responsável Técnico</dt>
													<dd><input type="text" name="responsavel_cro" value="<?php echo $values['responsavel_cro'];?>" class="" /></dd>
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

										<?php
											$thumb="";
											if(is_object($cnt)) { 
												if(!empty($cnt->cn_logo)) {
													$image=$_cloudinaryURL.'c_thumb,w_600/'.$cnt->cn_logo;
													$thumb=$_cloudinaryURL.'c_thumb,w_600/'.$cnt->cn_logo;
												} 
											}
										?>
										<div class="form-image">
											<?php
											if(empty($thumb)) {
											?>
											<span class="iconify" data-icon="carbon:no-image" data-height="80"></span>
											<?php
											} else {
											?>
											<img src="<?php echo $thumb;?>" alt="" width="484" height="" />
											<?php
											}
											?>
										</div>
										<dl>
											<dt>Logotipo</dt>
											<dd>
												<a href="javascript:;" id="logo" class="button button_main">Procurar</a>
												<input type="hidden" name="cn_logo" />
											</dd>
										</dl>
									</div>
								</div>
							</fieldset>
							<!-- MULTAS E JUROS -->
							<fieldset>
								<legend>Politica de Multas e Juros</legend>
								<div class="grid grid_3">
									<div style="grid-column:span 2">
										<div class="colunas">
											<dl>
												<dt>Multas</dt>
												<dd class="form-comp"><span>%</i></span><input type="tel" name="politica_multas" class="valor js-valor" value="<?= $values['politica_multas']??0;?>"/></dd>
											</dl>
											<dl>
												<dt>Juros</dt>
												<dd class="form-comp"><span>%</i></span><input type="tel" name="politica_juros" class="valor js-valor" value="<?= $values['politica_juros']??0;?>"/></dd>
											</dl>
										</div>
							</fieldset>
							<?php /*<fieldset>
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
							</fieldset>*/?>



						</form>

					</div>
					
				</div>

			</section>
		
		</div>
	</main>

<?php 
include "includes/footer.php";
?>	