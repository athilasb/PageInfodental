<?php
	if(isset($_POST['ajax'])) {
		require_once("lib/conf.php");
		require_once("usuarios/checa.php");

		$rtn = array();

		if($_POST['ajax']=="consultaCPF") {

			$cpf = '';

			if(isset($_POST['cpf']) and !empty($_POST['cpf'])) {
				$cpf = cpf($_POST['cpf']);
			}

			$sql->consult($_p."pacientes","id,nome","where cpf = '$cpf' and lixo=0");
		

			$rtn = array('success'=>true,'pacientes'=>$sql->rows);

		}

		header("Content-type: application/json");
		echo json_encode($rtn);
		die();
	}

	require_once("lib/conf.php");
	$_table=$_p."pacientes";

	include "includes/header.php";
	include "includes/nav.php";

	$values=$adm->get($_GET);
	$campos=explode(",","nome,situacao,sexo,foto_cn,rg,rg_orgaoemissor,rg_uf,cpf,data_nascimento,profissao,estado_civil,telefone1,telefone1_whatsapp,telefone1_whatsapp_permissao,telefone2,email,instagram,instagram_naopossui,musica,indicacao_tipo,indicacao,cep,endereco,numero,complemento,bairro,estado,cidade,id_cidade,responsavel_possui,responsavel_nome,responsavel_sexo,responsavel_rg,responsavel_rg_orgaoemissor,responsavel_rg_estado,responsavel_datanascimento,responsavel_estadocivil,responsavel_cpf,responsavel_profissao,responsavel_grauparentesco,preferencia_contato,estrangeiro,estrangeiro_passaporte,lat,lng,responsavel_estado_civil");

	$_profissoes=array();
	$sql->consult($_p."parametros_profissoes","*","where lixo=0 order by titulo asc");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_profissoes[$x->id]=$x;
	}

	$values=array();
	foreach($campos as $v) {
		$values[$v]='';
	}

?>

	<header class="header">
		<div class="header__content content">
			<div class="header__inner1">
				<section class="header-title">
					<h1>Pacientes</h1>
				</section>
				<?php
				require_once("includes/menus/menuPacientes.php");
				?>
			</div>
		</div>
	</header>
	<script type="text/javascript">
		
		$(function(){
			
			$('.js-openAside').click(function(){
				$('#js-aside form.formulario-validacao').trigger('reset');
				openAside(0);
			})
			$('.list1').on('click','.js-item',function(){
				$('#js-aside form.formulario-validacao').trigger('reset');
				let id = $(this).attr('data-id');
				document.location.href=`pg_pacientes_dadospessoais.php?id_paciente=${id}`;
			})
		})
	</script>

	<main class="main">
		<div class="main__content content">

			
 		<?php
 	if(isset($_GET['form'])) {

 		if(isset($_POST['acao'])) {

			$vSQL=$adm->vSQL($campos,$_POST);
			$values=$adm->values;

			$vSQL=substr($vSQL,0,strlen($vSQL)-1);
			$sql->add($_table,$vSQL);
			$id_reg=$sql->ulid;
			$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='insert',vsql='".addslashes($vSQL)."',vwhere='',tabela='$_table',id_reg='$id_reg'");
			

			$jsc->go("pg_pacientes_dadospessoais.php?id_paciente=$id_reg");
			die();
		}
 		$cnt='';
		?>	
			<section class="filter">
				
				<div class="filter-group">
				</div>

				<div class="filter-group">
					<div class="filter-form form">
						<dl>
							<dd><a href="" class="button"><i class="iconify" data-icon="fluent:arrow-left-24-regular"></i></a></dd>
						</dl>
						<dl>
							<dd><a href="javascript:;" class="button button_main js-submit-paciente"><i class="iconify" data-icon="fluent:checkmark-12-filled"></i><span>Salvar</span></a></dd>
						</dl>
					</div>
				</div>
				
			</section>
			<section class="grid">

				<form method="post" class="form formulario-validacao">
					<input type="hidden"  name="acao" value="wlib" />

					<script type="text/javascript">
						var initIndicacao = '<?php echo $values['indicacao'];?>';
						var initIndicacao_tipo = '<?php echo $values['indicacao_tipo'];?>';

						$(function(){

							$('.js-submit-paciente').click(function(){
								if($('.js-cpf-erro').is(':visible')===true) {
									swal({title: "Atenção!", html:true, text: `Já existe paciente cadastrado com este CPF!`, type:"warning", confirmButtonColor: "#424242"});
								} else {
									$('form.formulario-validacao').submit();
								}
							})

							$('input[name=cpf]').change(function(){
								let cpf = $(this).val();
								$('.js-cpf-erro').hide();

								let data = `ajax=consultaCPF&cpf=${cpf}`
								$.ajax({
									type:"POST",
									url:"pg_pacientes.php",
									data:data,
									success:function(rtn) {
										if(rtn.success) {
											if(rtn.pacientes && rtn.pacientes>0) {
												$('.js-cpf-erro').show();
											} else {
												$('.js-cpf-erro').hide();
											}
										} else if(rtn.error) {

										} else {

										}
									},
									error:function(){

									}
								})

							})

							$('select[name=indicacao_tipo]').change(function(){
								//let id_indicacao = $(this).find('option:selected').attr('data-id');
								let indicacao_tipo = $(this).val();

								if(indicacao_tipo.length>0) {
									let data = `ajax=indicacoesLista&indicacao_tipo=${indicacao_tipo}`;
									$.ajax({
										type:"POST",
										data:data,
										success:function(rtn) {
											$('select[name=indicacao] option').remove();
											$('select[name=indicacao]').append(`<option value=""></option>`);
											console.log(rtn.indicacoes);
											if(rtn.indicacoes) {
												rtn.indicacoes.forEach(x => {
													if(initIndicacao_tipo==indicacao_tipo && initIndicacao==x.id) sel = ' selected';
													else sel='';
													let option = `<option value="${x.id}"${sel}>${x.titulo}</option>`
													$('select[name=indicacao]').append(`${option}`);
												});
											}
											$('select[name=indicacao]').trigger('chosen:updated')
										}
									})
								}
							}).trigger('change');

							$('input[name=telefone1],input[name=telefone2]').blur(function(){
								let obj = $(this);
								let id_paciente = 0;
								let campo = $(this).attr('name');
								let val = $(this).val();
								let data = `ajax=verificarTelefone&campo=${campo}&valor=${val}&id_paciente=${id_paciente}`;

								$.ajax({
									type:'POST',
									data:data,
									success:function(rtn) {
										if(rtn.success) {
											if(rtn.cadastros) {
												if(rtn.cadastros.length>0) {

													let cadastros = ``;
													rtn.cadastros.forEach(x=>{
														cadastros+=`<a href="pg_contatos_pacientes_dadospessoais.php?id_paciente=${x.id}" target="_blank"><u><span class="iconify" data-icon="bx:bx-user"></span> ${x.nome}</a></u><br />`;
													});


													swal({title: "Atenção!", html:true, text: `O(s) seguinte(s) paciente(s)<br />possui o mesmo telefone <b>${val}</b>:<br /><br />${cadastros}`, type:"warning", confirmButtonColor: "#424242"});

												}
											}
										}
									}
								})
							})

							$('input[name=estrangeiro]').click(function(){

								if($(this).prop('checked')==true) {
									$('input[name=estrangeiro_passaporte]').parent().parent().show();
								} else {
									$('input[name=estrangeiro_passaporte]').parent().parent().hide();

								}
							});	

							if($('input[name=estrangeiro').prop('checked')===false) {
								$('input[name=estrangeiro_passaporte]').parent().parent().hide();
							} else {
								$('input[name=estrangeiro_passaporte]').parent().parent().show();
							}

							$('input[name=telefone1]').mobilePhoneNumber({allowPhoneWithoutPrefix: '+55'}).bind('country.mobilePhoneNumber', function(echo, country) {
								let countryOut = country || '  ';
								$(this).parent().parent().find('.js-country').html(countryOut);
							}).trigger('keyup');

							$('input[name=telefone2]').mobilePhoneNumber({allowPhoneWithoutPrefix: '+55'}).bind('country.mobilePhoneNumber', function(echo, country) {
								let countryOut = country || '  ';
								$(this).parent().parent().find('.js-country').html(countryOut);
							}).trigger('keyup');
						})
					</script>
					<fieldset>
						<legend>Cadastro</legend>

						<div class="grid">
							
							<div style="grid-column:span 2">
								<div class="colunas3">
									<dl class="dl2">
										<dt>Nome</dt>
										<dd><input type="text" class="obg" name="nome" value="<?php echo $values['nome'];?>" /></dd>
									</dl>
									<dl>
										<dt>Sexo</dt>
										<dd>
											<select name="sexo" class="">
												<option value="">-</option>
												<option value="M"<?php echo $values['sexo']=="M"?" selected":"";?>>Masculino</option>
												<option value="F"<?php echo $values['sexo']=="F"?" selected":"";?>>Feminino</option>
											</select>
										</dd>
									</dl>
								</div>
								<div class="colunas3">
									<dl>
										<dt>Doc. Identidade</dt>
										<dd><input type="text" name="rg" value="<?php echo $values['rg'];?>"  class="" /></dd>
									</dl>
									<dl>
										<dt>Org. Emissor</dt>
										<dd><input type="text" name="rg_orgaoemissor" value="<?php echo $values['rg_orgaoemissor'];?>"  class="" /></dd>
									</dl>
									<dl>
										<dt>UF</dt>
										<dd>
											<?php $inEstado=strtoupperWLIB($values['rg_uf']);?><select name="rg_uf" class="chosen"><option value="">-</option><option value="AC"<?php echo $inEstado=="AC"?" selected":"";?>>ACRE</option><option value="AL"<?php echo $inEstado=="AL"?" selected":"";?>>ALAGOAS</option><option value="AM"<?php echo $inEstado=="AM"?" selected":"";?>>AMAZONAS</option><option value="AP"<?php echo $inEstado=="AP"?" selected":"";?>>AMAPÁ</option><option value="BA"<?php echo $inEstado=="BA"?" selected":"";?>>BAHIA</option><option value="CE"<?php echo $inEstado=="CE"?" selected":"";?>>CEARÁ</option><option value="DF"<?php echo $inEstado=="DF"?" selected":"";?>>DISTRITO FEDERAL</option><option value="ES"<?php echo $inEstado=="ES"?" selected":"";?>>ESPÍRITO SANTO</option><option value="GO"<?php echo $inEstado=="GO"?" selected":"";?>>GOIÁS</option><option value="MA"<?php echo $inEstado=="MA"?" selected":"";?>>MARANHÃO</option><option value="MT"<?php echo $inEstado=="MT"?" selected":"";?>>MATO GROSSO</option><option value="MS"<?php echo $inEstado=="MS"?" selected":"";?>>MATO GROSSO DO SUL</option><option value="MG"<?php echo $inEstado=="MG"?" selected":"";?>>MINAS GERAIS</option><option value="PA"<?php echo $inEstado=="PA"?" selected":"";?>>PARÁ</option><option value="PB"<?php echo $inEstado=="PB"?" selected":"";?>>PARAÍBA</option><option value="PR"<?php echo $inEstado=="PR"?" selected":"";?>>PARANÁ</option><option value="PE"<?php echo $inEstado=="PE"?" selected":"";?>>PERNANBUMCO</option><option value="PI"<?php echo $inEstado=="PI"?" selected":"";?>>PIAUÍ</option><option value="RJ"<?php echo $inEstado=="RJ"?" selected":"";?>>RIO DE JANEIRO</option><option value="RN"<?php echo $inEstado=="RN"?" selected":"";?>>RIO GRANDE DO NORTE</option><option value="RO"<?php echo $inEstado=="RO"?" selected":"";?>>RONDÔNIA</option><option value="RS"<?php echo $inEstado=="RS"?" selected":"";?>>RIO GRANDE DO SUL</option><option value="RR"<?php echo $inEstado=="RR"?" selected":"";?>>RORAIMA</option><option value="SC"<?php echo $inEstado=="SC"?" selected":"";?>>SANTA CATARINA</option><option value="SE"<?php echo $inEstado=="SE"?" selected":"";?>>SERGIPE</option><option value="SP"<?php echo $inEstado=="SP"?" selected":"";?>>SÃO PAULO</option><option value="TO"<?php echo $inEstado=="TO"?" selected":"";?>>TOCANTINS</option></select>
										</dd>
									</dl>
								</div>
								<div class="colunas3">
									<dl>
										<dt>CPF</dt>
										<dd><input type="text" name="cpf" value="<?php echo $values['cpf'];?>" class="cpf js-cpf" /></dd>
										<dd class="js-cpf-erro" style="color:var(--vermelho);display: none;">
											<span class="iconify" data-icon="dashicons:warning" data-inline="true"></span>Já existe cadastro com este CPF
										</dd>
									</dl>
									<dl>
										<dt>Data de Nascimento</dt>
										<dd><input type="text" name="data_nascimento" value="<?php echo $values['data_nascimento'];?>" class="data" /></dd>
									</dl>
									<dl>
										<dt>Estado Civil</dt>
										<dd>
											<select name="estado_civil" class="chosen">
												<option value="">-</option>
												<?php
												foreach($_pacienteEstadoCivil as $k=>$v) {
													echo '<option value="'.$k.'"'.(($values['estado_civil']==$k)?' selected':'').'>'.$v.'</option>';
												}
												?>
											</select>
										</dd>
									</dl>
								</div>
								<div class="colunas3">
									<dl class="dl2">
										<dt>Profissão</dt>
										<dd>
											<select name="profissao" class="chosen" data-placeholder="PROFISSÃO">
												<option value=""></option>
												<?php
												foreach($_profissoes as $v) {
													echo '<option value="'.$v->id.'"'.(($values['profissao']==$v->id)?' selected':'').'>'.utf8_encode($v->titulo).'</option>';
												}
												?>
											</select>
											<a href="" class="button"><i class="iconify" data-icon="fluent:add-24-regular"></i></a></dd>
									</dl>
									<dl>
										<dt>Preferência Musical</dt>
										<dd><input type="text" name="musica" value="<?php echo $values['musica'];?>" /></dd>
									</dl>
								</div>
								<dl>									
									<dd>
										<label><input type="checkbox" class="input-switch" name="estrangeiro" value="1"<?php echo (isset($values['estrangeiro']) and $values['estrangeiro']==1)?" checked":"";?> /> Estrangeiro</label>
									</dd>
								</dl>

								<dl>
									<dt>Passaporte</dt>
									<dd>
										<input type="text" name="estrangeiro_passaporte" value="<?php echo $values['estrangeiro_passaporte'];?>" />
									</dd>
								</dl>
							</div>


						</div>
					</fieldset>

					<fieldset>
						<legend>Contato</legend>
						<div class="colunas4">
							<dl>
								<dt>Telefone 1</dt>
								<dd class="form-comp"><span class="js-country">BR</span><input type="text" name="telefone1" style="width:85%;float:right;" class="obg" attern="\d*" x-autocompletetype="tel" value="<?php echo $values['telefone1'];?>" /></dd>
							</dl>
							<dl>
								<dt>Telefone 2</dt>
								<dd class="form-comp"><span class="js-country">BR</span><input type="text" name="telefone2" style="width:85%;float:right;" attern="\d*" x-autocompletetype="tel" value="<?php echo $values['telefone2'];?>"  /></dd>
							</dl>
							<dl>
								<dt>E-mail</dt>
								<dd><input type="text" name="email" class="noupper" value="<?php echo $values['email'];?>" /></dd>
							</dl>
							<dl>
								<dt>Instagram</dt>
								<dd class="form-comp"><span>@</span><input type="text" name="instagram" class="noupper" placeholder="" value="<?php echo $values['instagram'];?>" /></dd>
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
							<dl class="">
								<dt>Endereço</dt>
								<dd><input type="text" name="endereco" value="<?php echo $values['endereco'];?>" id="search" /></dd>
							</dl>
							<dl>
								<dt>Complemento</dt>
								<dd><input type="text" name="complemento" value="<?php echo $values['complemento'];?>" /></dd>
							</dl>
						</div>
						<input type="hidden" name="lng" id="lng" value="<?php echo $values['lng'];?>" />
						<input type="hidden" name="lat" id="lat" value="<?php echo $values['lat'];?>"/>
					</fieldset>

					<fieldset>
						<legend>BI</legend>
							<div class="colunas3">
							<dl>
								<dt>Tipo de indicação</dt>
								<dd>
									<select name="indicacao_tipo" class="chosen">
										<option value=""></option>
										<?php
										foreach($optTipoIndicacao as $k=>$v) echo '<option value="'.$k.'"'.($values['indicacao_tipo']==$k?' selected':'').'>'.$v.'</option>';
										?>
									</select>
								</dd>
							</dl>
							<dl>
								<dt>Indicação</dt>
								<dd>
									<select name="indicacao" class="chosen">
										<option value=""></option>
									</select>
								</dd>
							</dl>
						
							
							<dl>
								<dt>Situação</dt>
								<dd>
									<select name="situacao">
										<option value="">-</option>
										<?php
										foreach($_pacienteSituacao as $k=>$v) echo '<option value="'.$k.'"'.($values['situacao']==$k?' selected':'').'>'.($v).'</option>';
										?>
									</select>
								</dd>
							</dl>
						</div>
					</fieldset>

					<script type="text/javascript">
						$(function(){
							$('input[name=responsavel_possui]').click(function(){
								if($(this).val()==1) {
									$('.js-box-possuiResponsavel').fadeIn();
								} else {
									$('.js-box-possuiResponsavel').fadeOut();
								}
							});
							$('input[name=responsavel_possui]:checked').trigger('click');
						})
					</script>
					<fieldset>
						<legend>Responsável</legend>

						<dl>
							<dt>Possui responsável legal?</dt>
							<dd>
								<label><input type="radio" name="responsavel_possui" value="1"<?php echo $values['responsavel_possui']==1?" checked":"";?> /> Sim</label>
								<label><input type="radio" name="responsavel_possui" value="0"<?php echo $values['responsavel_possui']==0?" checked":"";?> /> Não</label>
							</dd>
						</dl>

						<div class="colunas5 js-box-possuiResponsavel">
							<dl class="dl2">
								<dt>Nome</dt>
								<dd>
									<input type="text" name="responsavel_nome" value="<?php echo $values['responsavel_nome'];?>" class="" />
								</dd>
							</dl>
							<dl>
								<dt>Sexo</dt>
								<dd>
									<select name="responsavel_sexo" class="">
										<option value="">-</option>
										<option value="M"<?php echo $values['responsavel_sexo']=="M"?" selected":"";?>>Masculino</option>
										<option value="F"<?php echo $values['responsavel_sexo']=="F"?" selected":"";?>>Feminino</option>
									</select>
								</dd>
							</dl>

							<dl class="">
								<dt>Estado Civil</dt>
								<dd>
									<select name="responsavel_estado_civil" class="chosen">
										<option value=""></option>
										<?php
										foreach($_pacienteEstadoCivil as $k=>$v) {
											echo '<option value="'.$k.'"'.(($values['responsavel_estado_civil']==$k)?' selected':'').'>'.$v.'</option>';
										}
										?>
									</select>
								</dd>
							</dl>
							<dl class="">
								<dt>Profissão</dt>
								<dd>
									<select name="responsavel_profissao" class="chosen">
										<option value=""></option>
										<?php
										foreach($_profissoes as $v) {
											echo '<option value="'.$v->id.'"'.(($values['responsavel_profissao']==$v->id)?' selected':'').'>'.utf8_encode($v->titulo).'</option>';
										}
										?>
									</select>
								</dd>
							</dl>

							<dl class="">
								<dt>Doc. Identidade</dt>
								<dd>
									<input type="text" name="responsavel_rg" value="<?php echo $values['responsavel_rg'];?>"  class="" />
								</dd>
							</dl>
							<dl>
								<dt>Org. Emissor</dt>
								<dd>
									<input type="text" name="responsavel_rg_orgaoemissor" value="<?php echo $values['responsavel_rg_orgaoemissor'];?>"  class="" />
								</dd>
							</dl>
							<dl>
								<dt>UF</dt>
								<dd>
									<?php $inEstado=strtoupperWLIB($values['responsavel_rg_estado']);?><select name="responsavel_rg_estado" class="chosen"><option value=""></option><option value="AC"<?php echo $inEstado=="AC"?" selected":"";?>>ACRE</option><option value="AL"<?php echo $inEstado=="AL"?" selected":"";?>>ALAGOAS</option><option value="AM"<?php echo $inEstado=="AM"?" selected":"";?>>AMAZONAS</option><option value="AP"<?php echo $inEstado=="AP"?" selected":"";?>>AMAPÁ</option><option value="BA"<?php echo $inEstado=="BA"?" selected":"";?>>BAHIA</option><option value="CE"<?php echo $inEstado=="CE"?" selected":"";?>>CEARÁ</option><option value="DF"<?php echo $inEstado=="DF"?" selected":"";?>>DISTRITO FEDERAL</option><option value="ES"<?php echo $inEstado=="ES"?" selected":"";?>>ESPÍRITO SANTO</option><option value="GO"<?php echo $inEstado=="GO"?" selected":"";?>>GOIÁS</option><option value="MA"<?php echo $inEstado=="MA"?" selected":"";?>>MARANHÃO</option><option value="MT"<?php echo $inEstado=="MT"?" selected":"";?>>MATO GROSSO</option><option value="MS"<?php echo $inEstado=="MS"?" selected":"";?>>MATO GROSSO DO SUL</option><option value="MG"<?php echo $inEstado=="MG"?" selected":"";?>>MINAS GERAIS</option><option value="PA"<?php echo $inEstado=="PA"?" selected":"";?>>PARÁ</option><option value="PB"<?php echo $inEstado=="PB"?" selected":"";?>>PARAÍBA</option><option value="PR"<?php echo $inEstado=="PR"?" selected":"";?>>PARANÁ</option><option value="PE"<?php echo $inEstado=="PE"?" selected":"";?>>PERNANBUMCO</option><option value="PI"<?php echo $inEstado=="PI"?" selected":"";?>>PIAUÍ</option><option value="RJ"<?php echo $inEstado=="RJ"?" selected":"";?>>RIO DE JANEIRO</option><option value="RN"<?php echo $inEstado=="RN"?" selected":"";?>>RIO GRANDE DO NORTE</option><option value="RO"<?php echo $inEstado=="RO"?" selected":"";?>>RONDÔNIA</option><option value="RS"<?php echo $inEstado=="RS"?" selected":"";?>>RIO GRANDE DO SUL</option><option value="RR"<?php echo $inEstado=="RR"?" selected":"";?>>RORAIMA</option><option value="SC"<?php echo $inEstado=="SC"?" selected":"";?>>SANTA CATARINA</option><option value="SE"<?php echo $inEstado=="SE"?" selected":"";?>>SERGIPE</option><option value="SP"<?php echo $inEstado=="SP"?" selected":"";?>>SÃO PAULO</option><option value="TO"<?php echo $inEstado=="TO"?" selected":"";?>>TOCANTINS</option></select>
								</dd>
							</dl>
							<dl class="">
								<dt>CPF</dt>
								<dd>
									<input type="text" name="responsavel_cpf" value="<?php echo $values['responsavel_cpf'];?>" class="cpf" />
								</dd>
							</dl>
							<dl class="">
								<dt>Data de Nascimento</dt>
								<dd>
									<input type="text" name="responsavel_datanascimento" value="<?php echo $values['responsavel_datanascimento'];?>" class="data" />
								</dd>
							</dl>
						</div> 	

					</fieldset>





				</form>

			</section>
		<?php
 	} else {

		$pacientes=0;
		$sql->consult($_p."pacientes","count(*) as total","where lixo=0");
		$x=mysqli_fetch_object($sql->mysqry);
		$pacientes=$x->total;
	?>
 			<section class="filter">
				
				<div class="filter-group">
					<div class="filter-form form">
						<dl>
							<dd><a href="pg_pacientes.php?form=1" class="button button_main"><i class="iconify" data-icon="fluent:add-circle-24-regular"></i> <span>Novo Paciente</span></a></dd>
						</dl>
					</div>
				</div>

				<form method="get" class="js-filtro">
					<div class="filter-group">
						<div class="filter-form form">
							<dl>
								<dd class="form-comp form-comp_pos"><input type="text" name="busca" placeholder="Buscar..." value="<?php echo isset($values['busca'])?($values['busca']):"";?>" /><a href="javascript:;" onclick="$('form.js-filtro').submit();"><i class="iconify" data-icon="fluent:search-12-filled"></i></a></dd>
							</dl>
						</div>					
					</div>
				</form>

			</section>

			<section class="grid" style="grid-template-columns:40% auto">

				<div class="box">

					<div class="filter">
						<div class="filter-group">
							<div class="filter-title">
								<h1>Indicadores</h1>
							</div>
						</div>
						<div class="filter-group">
							<div class="filter-title">
								<h1><?php echo number_format($pacientes,0,"",".");?> pacientes</h1>
							</div>
						</div>
					</div>

					<div class="list4">
						
						<a href="" class="list4-item active">
							<div>
								<h1><i class="iconify" data-icon="fluent:food-cake-20-regular"></i></h1>
							</div>
							<div>
								<p>Distribuição <strong>por Idade</strong></p>
							</div>
						</a>
						<a href="" class="list4-item">
							<div>
								<h1><i class="iconify" data-icon="ph:gender-intersex"></i></h1>
							</div>
							<div>
								<p>Distribuição <strong>por Gênero</strong></p>
							</div>
						</a>
						<a href="" class="list4-item">
							<div>
								<h1><i class="iconify" data-icon="fluent:location-20-regular"></i></h1>
							</div>
							<div>
								<p>Distribuição <strong>Localização</strong></p>
							</div>
						</a>
						<a href="" class="list4-item">
							<div>
								<h1><i class="iconify" data-icon="fluent:person-add-20-regular"></i></h1>
							</div>
							<div>
								<p>Novos pacientes <strong>9 por mês</strong></p>
							</div>
						</a>

					</div>

					<section style="width:100%; height:300px; background:var(--cinza2); margin-bottom:var(--margin1);">						
					</section>
				</div>

				<div class="box">

					<?php
					# LISTAGEM #

					$values=$adm->get($_GET);
					$where="WHERE lixo='0'";
					if(isset($values['busca']) and !empty($values['busca'])) {
						//$where.=" and (nome like '%".utf8_decode($values['busca'])."%' or cpf like '%".cpf($values['busca'])."%' or id = '".addslashes($values['busca'])."')";
						$wh="";
						$aux = explode(" ",$_GET['busca']);

						foreach($aux as $v) {
							$wh.="nome REGEXP '$v' and ";
						}
						$wh=substr($wh,0,strlen($wh)-5);
						$where="where (($wh) or nome like '%".$_GET['busca']."%' or telefone1 like '%".$_GET['busca']."%' or cpf like '%".$_GET['busca']."%') and lixo=0";
						//echo $where;die();
					}

					
					$where.=" order by nome asc";
					//echo $where;
					$sql->consultPagMto2($_table,"*",10,$where,"",15,"pagina",$_page."?".$url."&pagina=");
					if($sql->rows==0) {
						if(isset($values['busca'])) $msg="Nenhum Resultado encontrado";
						else $msg="Nenhum colaborador cadastrado";

						echo "<center>$msg</center>";
					} else {
					?>	
						<div class="list1">
							<table>
								<?php
								while($x=mysqli_fetch_object($sql->mysqry)) {
									$cor="var(--cinza3)";
									if(isset($_codigoBICores[$x->codigo_bi])) $cor=$_codigoBICores[$x->codigo_bi];
								/*?>
								<tr class="js-item" data-id="<?php echo $x->id;?>">
									<td style="width:20px;"><i class="iconify" data-icon="fluent:chevron-up-down-24-regular"></i></td>
									<td><h1><strong><?php echo utf8_encode($x->titulo);?></strong></h1></td>
								</tr>*/
								?>
								<tr class="js-item" data-id="<?php echo $x->id;?>">
									<td class="list1__border" style="color:<?php echo $cor;?>"></td>
									<td>
										<h1><?php echo utf8_encode($x->nome);?></h1>
										<p>#<?php echo utf8_encode($x->id);?></p>
									</td>
									<td><?php echo isset($_codigoBI[$x->codigo_bi])?$_codigoBI[$x->codigo_bi]:"";?></td>
									<td><?php echo $x->data_nascimento!="0000-00-00"?idade($x->data_nascimento)." anos":"";?></td>
									<td><?php echo !empty($x->telefone1)?mask($x->telefone1):"-";?></td>
								</tr>
								<?php
								}
								?>
							</table>
						</div>
						<?php
							if(isset($sql->myspaginacao) and !empty($sql->myspaginacao)) {
						?>
						
						<div class="pagination">						
							<?php echo $sql->myspaginacao;?>
						</div>
						<?php
						}
					}
					# LISTAGEM #
					?>

					
				</div>

			</section>
		<?php
	}
		?>
		
		</div>
	</main>

<?php 
include "includes/footer.php";
?>	