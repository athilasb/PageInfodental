<?php
	if(isset($_POST['ajax'])) {
		require_once("lib/conf.php");	
		require_once("usuarios/checa.php");

		$sql = new Mysql();

		$rtn = array();
		if($_POST['ajax']=="indicacoesLista") {

			$indicacoesLista=array();
			if(isset($_POST['indicacao_tipo'])) {
				if($_POST['indicacao_tipo']=="PACIENTE") {
					$tableIndicacao=$_p."pacientes";
					$whereIndicacao="where lixo=0 order by nome asc";
					$campoIndicacao="nome";
				} else if ($_POST['indicacao_tipo']=="PROFISSIONAL") {
					$tableIndicacao=$_p."colaboradores";
					$whereIndicacao="where lixo=0 order by nome asc";
					$campoIndicacao="nome";
				} else {
					$tableIndicacao=$_p."parametros_indicacoes";
					$whereIndicacao="where lixo=0 order by titulo asc";
					$campoIndicacao="titulo";
				}
			}


			$sql->consult($tableIndicacao,"*",$whereIndicacao);
			if($sql->rows) {
				while($x=mysqli_fetch_object($sql->mysqry)) {
					$indicacoesLista[]=array('id'=>$x->id,'titulo'=>utf8_encode($x->$campoIndicacao));
				}
			}

			$rtn=array('success'=>true,'indicacoes'=>$indicacoesLista);

			/*$indicacao='';
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
			}*/
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


	$campos=explode(",","nome,situacao,sexo,foto,rg,rg_orgaoemissor,rg_estado,cpf,data_nascimento,profissao,estado_civil,telefone1,telefone1_whatsapp,telefone1_whatsapp_permissao,telefone2,email,instagram,instagram_naopossui,musica,indicacao_tipo,indicacao,cep,endereco,numero,complemento,bairro,estado,cidade,id_cidade,responsavel_possui,responsavel_nome,responsavel_sexo,responsavel_rg,responsavel_rg_orgaoemissor,responsavel_rg_estado,responsavel_datanascimento,responsavel_estadocivil,responsavel_cpf,responsavel_profissao,responsavel_grauparentesco,preferencia_contato,estrangeiro,estrangeiro_passaporte,lat,lng");
	
	foreach($campos as $v) $values[$v]='';
	$values['data']=date('d/m/Y H:i');


	if(is_object($paciente)) {
	
		$values=$adm->values($campos,$cnt);
		$values['data']=date('d/m/Y H:i',strtotime($cnt->data));
		
	}

	if(isset($_POST['acao']) and $_POST['acao']=="wlib") {
		$vSQL=$adm->vSQL($campos,$_POST);
		$values=$adm->values;
		$processa=true;

		if((empty($cnt) or (is_object($cnt) and $cnt->cpf!=cpf($_POST['cpf']))) and !empty($_POST['cpf'])) {
			$sql->consult($_table,"*","where cpf='".addslashes(cpf($_POST['cpf']))."' and lixo=0");
			if($sql->rows) {
				$processa=false;
				$jsc->jAlert("Já existe cliente cadastrado com este CPF","erro",""); 
			}
		}

		if($processa===true) {	
		
			if(is_object($cnt)) {
				$vSQL=substr($vSQL,0,strlen($vSQL)-1);
				$vWHERE="where id='".$cnt->id."'";
				$sql->update($_table,$vSQL,$vWHERE);
				$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='update',vsql='".addslashes($vSQL)."',vwhere='".addslashes($vWHERE)."',tabela='".$_table."',id_reg='".$cnt->id."'");
				$id_reg=$cnt->id;
			} else {
				$sql->add($_table,$vSQL."data=now(),id_usuario='".$usr->id."'");
				$id_reg=$sql->ulid;
				$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='insert',vsql='".addslashes($vSQL)."',tabela='".$_table."',id_reg='".$sql->ulid."'");
			}

			$msgErro='';
			if(isset($_FILES['foto']) and !empty($_FILES['foto']['tmp_name'])) {
				$up=new Uploader();
				$up->uploadCorta("Foto",$_FILES['foto'],"",5242880*2,$_width,$_height,$_dir,$id_reg);

				if($up->erro) {
					$msgErro=$up->resul;
				} else {
					$ext=$up->ext;
					$vSQL="foto='".$ext."'";
					$vWHERE="where id='".$id_reg."'";
					$sql->update($_table,$vSQL,$vWHERE);
					$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='update',vsql='".addslashes($vSQL)."',tabela='".$_table."',id_reg='".$id_reg."'");
				}
			}
			if(!empty($msgErro)) {
				$jsc->jAlert($msgErro,"erro","");
			} else {
				$jsc->jAlert("Informações salvas com sucesso!","sucesso","document.location.href='".$_page."?id_paciente=".$id_reg."'");
				die();
			}
		}
	}

?>
	<script type="text/javascript">
		var _cidade='<?php echo $values['cidade'];?>';
		var _cidadeID='<?php echo empty($values['id_cidade'])?0:$values['id_cidade'];?>';
		
		$(function(){

			$('.m-contatos').addClass("active");
			$('.js-cpf').keyup(function(){
				let cpf = $(this).val().replace(/[^0-9+]/g, '');

				if(cpf.length==11) {
					if(!validarCPF(cpf)) {
						swal({title: "Erro!", text: "Digite um CPF válido", type:"error", confirmButtonColor: "#424242"});
						return false;
					} 
				} 
			});

			$('.js-btn-profissao').click(function(){ 
				var id_profissao=$('select[name=profissao]').val();
				$.fancybox.open({
					src  : `<?php echo $_page;?>?ajax=profissao&id_profissao=${id_profissao}`,
					type : 'ajax',
					opts : {
						/*afterClose : function( instance, current ) {
							let data = `ajax=categoriasAtualizaLista`;
							$.ajax({
								type:'POST',
								url:'<?php echo $_page;?>',
								data:data,
								success:function(rtn) {
									if(rtn.success) {
										$('select[name=id_categoria] option').remove();
										$('select[name=id_categoria]').append('<option value="">-</option>');
										
										rtn.categorias.forEach(x=> {
											let selected = x.id==id_categoria?' selected':'';
											$('select[name=id_categoria]').append(`<option value="${x.id}"${selected}>${x.titulo}</option>`);
										});

										$('select[name=id_categoria]').trigger('change');

									} else if(rtn.error) {
										alert(rtn.error);
									} else {
										alert('Algum erro ocorreu durante a atualização das Categorias');
									}
								},
								error:function() {
									alert('Algum erro ocorreu durante a atualização das Categorias.');
								}

							})
						}*/
					}
				});
			});
				
		})
	</script>
	<section class="content">
		
		<?php
		if(is_object($cnt)) require_once("includes/abaPaciente.php");
		?>

		<script type="text/javascript">
			var initIndicacao = '<?php echo $values['indicacao'];?>';
			var initIndicacao_tipo = '<?php echo $values['indicacao_tipo'];?>';

			$(function(){
				$("#upload_link").on('click', function(e){
				    e.preventDefault();
				    $("#upload:hidden").trigger('click');
				});
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
			})
		</script>

		<form method="post" class="form formulario-validacao"  autocomplete="off" enctype="multipart/form-data">
			<input type="hidden" name="acao" value="wlib" />		
			<?php
			if(empty($cnt)) {
			?>
			<section class="filtros">
				<h1 class="filtros__titulo">Paciente</h1>
				<div class="filtros-acoes">
					<a href="pg_contatos_pacientes.php"><i class="iconify" data-icon="bx-bx-left-arrow-alt"></i></a>
					<a href="javascript:;" class="principal btn-submit"><i class="iconify" data-icon="bx-bx-check"></i></a>
				</div>
			</section>
			<?php
			}
			?>

			<section class="grid" style="padding:2rem;">
				<div class="box">
					<div class="filter">

						<div class="filter-group">
							<div class="filter-button">
								<a href="javascript:history.back(-1);"><i class="iconify" data-icon="bx-bx-left-arrow-alt"></i></a>
							</div>
						</div>

						<div class="filter-group filter-group_right">
							<div class="filter-button">
								<a href="?deletaPaciente=<?php echo $paciente->id."&".$url;?>" class="js-deletar"><i class="iconify" data-icon="bx-bx-trash"></i></a>
								<a href="javascript:;"><i class="iconify" data-icon="bx-bx-printer"></i></a>
								<a href="javascript:;" class="azul btn-salvar"><i class="iconify" data-icon="bx-bx-check"></i><span>salvar</span></a>
							</div>
						</div>

					</div>
					<?php /*<div class="filtros">
						<h1 class="filtros__titulo">Dados Pessoais</h1>
						<?php
						if(is_object($cnt)) {
						?>
						<div class="filtros-acoes">
							<a href="javascript:;" class="principal btn-submit"><i class="iconify" data-icon="bx-bx-check"></i></a>
						</div>
						<?php
						}
						?>
					</div>*/?>

					<div>
						<fieldset style="margin:0;">
							<legend style="font-size: 12px;">
								<div class="filter-group">
									<div class="filter-title">
										<span class="badge">1</span> Cadastro
									</div>
								</div>
							</legend>
							
							<div class="colunas4">
								<dl class="dl3">
									<dt>Nome</dt>
									<dd>
										<input type="text" name="nome" value="<?php echo $values['nome'];?>" class="obg" style="width: 840px;"/>
									</dd>
								</dl>
								<dl>
									<dt style="margin-left: 30px;">Sexo</dt>
									<dd style="margin-left: 30px;">
										<select name="sexo" class="">
											<option value="">-</option>
											<option value="M"<?php echo $values['sexo']=="M"?" selected":"";?>>Masculino</option>
											<option value="F"<?php echo $values['sexo']=="F"?" selected":"";?>>Feminino</option>
										</select>
									</dd>
								</dl>
							</div>

							<div class="grid grid_3">
								<fieldset style="margin:0;">
									<legend>Foto</legend>

									<?php
									$ft='img/ilustra-colaborador.jpeg';
									if(is_object($cnt)) {
										$ftColaborador='arqs/pacientes/'.$cnt->id.".".$cnt->foto;
										if(file_exists($ftColaborador)) {
											$ft=$ftColaborador;
										}
									}
									?>
									<dl>
										<dd><a href="javascript:;" id="upload_link"><img id="output" src="<?php echo $ft;?>" width="200" style="border: solid 1px #CCC;padding:2px;" /></a></dd>
									</dl>
									<input type="file" name="foto" id="upload" onchange="document.getElementById('output').src = window.URL.createObjectURL(this.files[0])" style="display: none;" />
									
								</fieldset>
								<div style="margin:0;grid-column:span 2">
							<div class="colunas3">
								<dl>
									<dt>Doc. Identidade</dt>
									<dd>
										<input type="text" name="rg" value="<?php echo $values['rg'];?>"  class="" />
									</dd>
								</dl>
								<dl>
									<dt>Org. Emissor</dt>
									<dd>
										<input type="text" name="rg_orgaoemissor" value="<?php echo $values['rg_orgaoemissor'];?>"  class="" />
									</dd>
								</dl>
								<dl>
									<dt>UF</dt>
									<dd>
										<?php $inEstado=strtoupperWLIB($values['rg_orgaoemissor']);?><select name="rg_orgaoemissor" class="chosen"><option value="">-</option><option value="AC"<?php echo $inEstado=="AC"?" selected":"";?>>ACRE</option><option value="AL"<?php echo $inEstado=="AL"?" selected":"";?>>ALAGOAS</option><option value="AM"<?php echo $inEstado=="AM"?" selected":"";?>>AMAZONAS</option><option value="AP"<?php echo $inEstado=="AP"?" selected":"";?>>AMAPÁ</option><option value="BA"<?php echo $inEstado=="BA"?" selected":"";?>>BAHIA</option><option value="CE"<?php echo $inEstado=="CE"?" selected":"";?>>CEARÁ</option><option value="DF"<?php echo $inEstado=="DF"?" selected":"";?>>DISTRITO FEDERAL</option><option value="ES"<?php echo $inEstado=="ES"?" selected":"";?>>ESPÍRITO SANTO</option><option value="GO"<?php echo $inEstado=="GO"?" selected":"";?>>GOIÁS</option><option value="MA"<?php echo $inEstado=="MA"?" selected":"";?>>MARANHÃO</option><option value="MT"<?php echo $inEstado=="MT"?" selected":"";?>>MATO GROSSO</option><option value="MS"<?php echo $inEstado=="MS"?" selected":"";?>>MATO GROSSO DO SUL</option><option value="MG"<?php echo $inEstado=="MG"?" selected":"";?>>MINAS GERAIS</option><option value="PA"<?php echo $inEstado=="PA"?" selected":"";?>>PARÁ</option><option value="PB"<?php echo $inEstado=="PB"?" selected":"";?>>PARAÍBA</option><option value="PR"<?php echo $inEstado=="PR"?" selected":"";?>>PARANÁ</option><option value="PE"<?php echo $inEstado=="PE"?" selected":"";?>>PERNANBUMCO</option><option value="PI"<?php echo $inEstado=="PI"?" selected":"";?>>PIAUÍ</option><option value="RJ"<?php echo $inEstado=="RJ"?" selected":"";?>>RIO DE JANEIRO</option><option value="RN"<?php echo $inEstado=="RN"?" selected":"";?>>RIO GRANDE DO NORTE</option><option value="RO"<?php echo $inEstado=="RO"?" selected":"";?>>RONDÔNIA</option><option value="RS"<?php echo $inEstado=="RS"?" selected":"";?>>RIO GRANDE DO SUL</option><option value="RR"<?php echo $inEstado=="RR"?" selected":"";?>>RORAIMA</option><option value="SC"<?php echo $inEstado=="SC"?" selected":"";?>>SANTA CATARINA</option><option value="SE"<?php echo $inEstado=="SE"?" selected":"";?>>SERGIPE</option><option value="SP"<?php echo $inEstado=="SP"?" selected":"";?>>SÃO PAULO</option><option value="TO"<?php echo $inEstado=="TO"?" selected":"";?>>TOCANTINS</option></select>
									</dd>
								</dl>
							</div>
							<div class="colunas3">
								
								<dl>
									<dt>CPF</dt>
									<dd>
										<input type="text" name="cpf" value="<?php echo $values['cpf'];?>" class="cpf js-cpf" />
									</dd>
								</dl>
								<dl>
									<dt>Data de Nascimento</dt>
									<dd>
										<input type="text" name="data_nascimento" value="<?php echo $values['data_nascimento'];?>" class="data" />
									</dd>
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
										<select name="profissao" class="chosen">
											<option value="">-</option>
											<?php
											foreach($_profissoes as $v) {
												echo '<option value="'.$v->id.'"'.(($values['profissao']==$v->id)?' selected':'').'>'.utf8_encode($v->titulo).'</option>';
											}
											?>
										</select>
									</dd>
								</dl>						
								<dl>
									<dt>Preferência Musical</dt>
									<dd><input type="text" name="musica" value="<?php echo $values['musica'];?>" /></dd>
								</dl>
							</div>


							<div class="colunas3">
								<dl class="dl2">
									<dt>&nbsp;</dt>
									<dd>
										<label><input type="checkbox" class="input-switch" name="estrangeiro" value="1"<?php echo $values['estrangeiro']==1?" checked":"";?> /> Estrangeiro</label>
									</dd>
								</dl>
								<dl class="dl2">
									<dt>Passaporte</dt>
									<dd>
										<input type="text" name="estrangeiro_passaporte" value="<?php echo $values['estrangeiro_passaporte'];?>" />
									</dd>
								</dl>
							</div>

							</div>
							</div>

						</fieldset>

						<script type="text/javascript">

							$(function(){

								$('input[name=estrangeiro]').click(function(){

									if($(this).prop('checked')==true) {
										$('input[name=estrangeiro_passaporte]').parent().parent().show();
									} else {
										$('input[name=estrangeiro_passaporte]').parent().parent().hide();

									}
								});

								if($('input[name=estrangeiro_passaporte').prop('checked')===false) $('input[name=estrangeiro_passaporte]').parent().parent().hide();
							//	$('input[name=instagram]').inputmask({"mask":"@9-a{1,8}","placeholder":''})
								$('input[name=instagram]').keyup(function(){
									let val = $(this).val().replace('@','');

									$(this).val(`@${val}`)


								})
								$('input[name=telefone1]').mobilePhoneNumber({allowPhoneWithoutPrefix: '+55'}).bind('country.mobilePhoneNumber', function(echo, country) {
									let countryOut = country || '';
									$(this).parent().parent().find('.country').remove();
									$(this).before(`<input type="text" diabled style="width:14%;float:left;background:#e5e5e5;" class="country" value="${countryOut}" disabled />`)
								}).trigger('keyup');

								$('input[name=telefone2]').mobilePhoneNumber({allowPhoneWithoutPrefix: '+55'}).bind('country.mobilePhoneNumber', function(echo, country) {
									let countryOut = country || '';
									$(this).parent().parent().find('.country').remove();
									$(this).before(`<input type="text" diabled style="width:14%;float:left;background:#e5e5e5;" class="country" value="${countryOut}" disabled />`)
								}).trigger('keyup');
							})
						</script>

						<div class="grid grid_2">

							<fieldset style="margin:0;">
								<legend style="font-size: 12px;">
									<div class="filter-group">
										<div class="filter-title">
											<span class="badge">2</span> Contato
										</div>
									</div>
								</legend>
								<div class="colunas4">
									<dl class="dl2">
										<dt>Tipo de Indicação</dt>
										<dd>
											<select name="indicacao_tipo" class="chosen">
												<option value=""></option>
												<?php
												foreach($optTipoIndicacao as $k=>$v) echo '<option value="'.$k.'"'.($values['indicacao_tipo']==$k?' selected':'').'>'.$v.'</option>';
												?>
											</select>
										</dd>
									</dl>
									<dl class="dl2">
										<dt>Indicação</dt>
										<dd>
											<select name="indicacao" class="chosen">
												<option value=""></option>
											</select>
										</dd>
									</dl>
								</div>

								<div class="colunas4">
									<dl class="dl2">
										<dt>Telefone 1</dt>
										<dd><input type="text" name="telefone1" style="width:85%;float:right;" class="obg" attern="\d*" x-autocompletetype="tel" value="<?php echo $values['telefone1'];?>" /></dd>
									</dl>
									<dl class="dl2">
										<dt>Telefone 2</dt>
										<dd><input type="text" name="telefone2" style="width:85%;float:right;" attern="\d*" x-autocompletetype="tel" value="<?php echo $values['telefone2'];?>"  /></dd>
									</dl>
								</div>

								<div class="colunas4">
									<dl class="dl2">
										<dt>E-mail</dt>
										<dd><input type="text" name="email" class="noupper" value="<?php echo $values['email'];?>" /></dd>
									</dl>	
									<dl class="dl2">
										<dt>Instagram</dt>
										<dd><input type="text" name="instagram" class="noupper" placeholder="@" value="<?php echo $values['instagram'];?>" /></dd>
									</dl>
								</div>

								<div class="colunas4">
									<dl class="dl2">
										<dt>Preferência de Contato</dt>
										<dd>
											<?php
											foreach($_preferenciaContato as $k=>$v) {
											?>
											<label><input type="radio" name="preferencia_contato" value="<?php echo $k;?>"<?php echo ($values['preferencia_contato']==$k)?" checked":"";?> /> <?php echo $v;?></label>
											<?php
											}
											?>
										</dd>
									</dl>

									<dl class="dl2">
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

						<fieldset style="margin:0;">

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
										position = new google.maps.LatLng(lat,lng);
										map = new google.maps.Map(document.getElementById('map'), {
										  center: {lat: lat, lng: lng},
										  zoom: 17
										});

										marker = new google.maps.Marker({
										    position: {lat: lat, lng: lng},
										    map,
										    title: "Click to zoom",
										});

										marker.addListener("click", () => {
										    map.setZoom(20);
										    map.setCenter(marker.getPosition());
										});

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

										if(result.address_components) {
											result.address_components.forEach(component => {
												if(component.types) {
													component.types.forEach(type => {
														if(type=='route' && logradouro.length==0) logradouro = component.long_name;
														else if(type=='street_number' && numero.length==0) numero = component.long_name;
														else if(type=='sublocality' && bairro.length==0) bairro = component.long_name;
														else if(type=='administrative_area_level_2' && cidade.length==0) cidade = component.long_name;
														else if(type=='administrative_area_level_1' && estado.length==0) estado = component.short_name;
														else if(type=='postal_code' && cep.length==0) cep = component.long_name;
														else if(type=='country' && pais.length==0) pais = component.long_name;

													})
												}
											});
										}
										if(descricao.length==0) descricao = result.formatted_address;
										

										enderecoObj = { logradouro, numero, bairro, cep, cidade, estado, pais, descricao, lat, lng }

										//$('#js-form-endereco ').html(enderecoObj.descricao);
										$('input[name=descricao]').val(enderecoObj.descricao); 
										$('input[name=numero]').val(enderecoObj.numero);
										$('input[name=logradouro]').val(enderecoObj.logradouro);
										$('input[name=bairro]').val(enderecoObj.bairro);
										$('select[name=estado]').val(enderecoObj.estado.toUpperCase());
        								$('select[name=estado]').trigger('change').trigger('chosen:updated');

										$('select[name=id_cidade] option').each(function () {
											var text = $(this).text();
											if(text == enderecoObj.cidade) {
												$(this).prop('selected', true);
											}
										});
										$('select[name=id_cidade]').trigger('chosen:updated');
										$('select[name=cidade]').val(enderecoObj.cidade);
										$('input[name=cep]').val(enderecoObj.cep);
										$('input[name=lat]').val(enderecoObj.lat);
										$('input[name=lng]').val(enderecoObj.lng);
										$('input[name=pais]').val(enderecoObj.pais);
										console.log(enderecoObj.cidade);
										$('#map').show();
									});

								}	
							</script>
							<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCDLcXlfkEGRZwlDYaXWbF8O-toogN05-g&libraries=geometry,drawing,places&callback=initMap" defer></script>

							<input type="text" name="descricao" style="display:none;" />
							<input type="text" name="lat"  style="display:none;" />
							<input type="text" name="lng"  style="display:none;" />
							<input type="text" name="pais" style="display:none;" />

							<legend style="font-size: 12px;">
								<div class="filter-group">
									<div class="filter-title">
										<span class="badge">3</span> Endereço
									</div>
								</div>
							</legend>
							<div class="colunas4">
								<dl class="dl2">
									<dt>CEP</dt>
									<dd>
										<input type="text" name="cep" id="inpt-cep" value="<?php echo $values['cep'];?>" class="cep" autocomplete="off" style="width:80%float:left;;" />
										<a href="javascript:;" id="js-consultacep" class="button button__sec tooltip" style="float:right;"><i class="iconify" data-icon="bx-bx-search" data-inline="false"></i></a>
									</dd>
								</dl>
							</div>
							<div class="colunas4">
								<dl class="dl2">
									<dt>Estado</dt>
									<dd>
										<?php $inEstado=strtoupperWLIB($values['estado']);?><select name="estado" class="js-estado chosen"><option value=""></option><option value="AC"<?php echo $inEstado=="AC"?" selected":"";?>>ACRE</option><option value="AL"<?php echo $inEstado=="AL"?" selected":"";?>>ALAGOAS</option><option value="AM"<?php echo $inEstado=="AM"?" selected":"";?>>AMAZONAS</option><option value="AP"<?php echo $inEstado=="AP"?" selected":"";?>>AMAPÁ</option><option value="BA"<?php echo $inEstado=="BA"?" selected":"";?>>BAHIA</option><option value="CE"<?php echo $inEstado=="CE"?" selected":"";?>>CEARÁ</option><option value="DF"<?php echo $inEstado=="DF"?" selected":"";?>>DISTRITO FEDERAL</option><option value="ES"<?php echo $inEstado=="ES"?" selected":"";?>>ESPÍRITO SANTO</option><option value="GO"<?php echo $inEstado=="GO"?" selected":"";?>>GOIÁS</option><option value="MA"<?php echo $inEstado=="MA"?" selected":"";?>>MARANHÃO</option><option value="MT"<?php echo $inEstado=="MT"?" selected":"";?>>MATO GROSSO</option><option value="MS"<?php echo $inEstado=="MS"?" selected":"";?>>MATO GROSSO DO SUL</option><option value="MG"<?php echo $inEstado=="MG"?" selected":"";?>>MINAS GERAIS</option><option value="PA"<?php echo $inEstado=="PA"?" selected":"";?>>PARÁ</option><option value="PB"<?php echo $inEstado=="PB"?" selected":"";?>>PARAÍBA</option><option value="PR"<?php echo $inEstado=="PR"?" selected":"";?>>PARANÁ</option><option value="PE"<?php echo $inEstado=="PE"?" selected":"";?>>PERNANBUMCO</option><option value="PI"<?php echo $inEstado=="PI"?" selected":"";?>>PIAUÍ</option><option value="RJ"<?php echo $inEstado=="RJ"?" selected":"";?>>RIO DE JANEIRO</option><option value="RN"<?php echo $inEstado=="RN"?" selected":"";?>>RIO GRANDE DO NORTE</option><option value="RO"<?php echo $inEstado=="RO"?" selected":"";?>>RONDÔNIA</option><option value="RS"<?php echo $inEstado=="RS"?" selected":"";?>>RIO GRANDE DO SUL</option><option value="RR"<?php echo $inEstado=="RR"?" selected":"";?>>RORAIMA</option><option value="SC"<?php echo $inEstado=="SC"?" selected":"";?>>SANTA CATARINA</option><option value="SE"<?php echo $inEstado=="SE"?" selected":"";?>>SERGIPE</option><option value="SP"<?php echo $inEstado=="SP"?" selected":"";?>>SÃO PAULO</option><option value="TO"<?php echo $inEstado=="TO"?" selected":"";?>>TOCANTINS</option></select>
									</dd>
								</dl>
								<dl class="dl2">
									<dt>Cidade</dt>
									<dd>
										<select name="id_cidade" class="js-cidade chosen">
											<option value="">-</option>
										</select>
										<input type="hidden" name="cidade" value="<?php echo $values['cidade'];?>"/>
									</dd>
								</dl>
							</div>
							<div class="colunas4">
								<dl class="dl2">
									<dt>Número</dt>
									<dd>
										<input type="text" name="numero" value="<?php echo $values['numero']; ?>" class="js-maskNumber" />
									</dd>
								</dl>
								<dl class="dl2">
									<dt>Bairro</dt>
									<dd><input type="text" name="bairro" value="<?php echo $values['bairro']; ?>" class="" /></dd>
								</dl>
							</div>
							<dl class="dl2">
								<dt>Endereço</dt>
								<dd>
									<input type="text" name="endereco" value="<?php echo $values['endereco']; ?>" id="search" class="" />
								</dd>
							</dl>
							<dl class="dl2">
								<dt>Complemento</dt>
								<dd>
									<input type="text" name="complemento" value="<?php echo $values['complemento']; ?>" class="" />
								</dd>
							</dl>
							<?php
								if(isset($values['lat']) and !empty($values['lat']) and isset($values['lng']) and !empty($values['lng'])) {
							?>
							<script>
								function initMap() {
									  const map = new google.maps.Map(
									    document.getElementById("map"),
									    {
									      zoom: 17,
									      center: { lat: <?php echo $values['lat'];?>, lng: <?php echo $values['lng'];?> },
									    }
									  );
									  const marker = new google.maps.Marker({
									    position: {lat: <?php echo $values['lat'];?>, lng: <?php echo $values['lng'];?>},
									    map,
									    title: "Click to zoom",
									});
									  $('#map').show();
									}
							</script>	
							<?php
								}
							?>	
							<section id="map" style="width: 600px;height:500px;margin-bottom: 10px;display:none;"></section>
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
						<fieldset style="margin:0;">
							<legend style="font-size: 12px;">
								<div class="filter-group">
									<div class="filter-title">
										<span class="badge">4</span> Responsável
									</div>
								</div>
							</legend>

							<div class="colunas4">
								<dl class="dl2">
									<dt>Possui responsável legal?</dt>
									<dd>
										<label><input type="radio" name="responsavel_possui" value="1"<?php echo $values['responsavel_possui']==1?" checked":"";?> /> Sim</label>
										<label><input type="radio" name="responsavel_possui" value="0"<?php echo $values['responsavel_possui']==0?" checked":"";?> /> Não</label>
									</dd>
								</dl>
								<dl class="dl2 js-box-possuiResponsavel">
									<dt>Grau de Parentesco</dt>
									<dd>
										<select name="responsavel_grauparentesco" class="chosen">
											<option value=""></option>
											<?php
											foreach($_pacienteGrauDeParentesco as $v) {
												echo '<option value="'.$v->id.'"'.(($values['responsavel_grauparentesco']==$v->id)?' selected':'').'>'.utf8_encode($v->titulo).'</option>';
											}
											?>
										</select>
									</dd>
								</dl>
							</div>

							<div class="colunas4 js-box-possuiResponsavel">
								<dl class="dl3">
									<dt>Nome</dt>
									<dd>
										<input type="text" name="responsavel_nome" value="<?php echo $values['responsavel_nome'];?>" class="" />
									</dd>
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

							<div class="colunas4 js-box-possuiResponsavel">

								<dl class="dl2">
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
							</div>

							<div class="colunas4 js-box-possuiResponsavel">
								<dl class="dl2">
									<dt>CPF</dt>
									<dd>
										<input type="text" name="responsavel_cpf" value="<?php echo $values['responsavel_cpf'];?>" class="cpf" />
									</dd>
								</dl>
								<dl class="dl2">
									<dt>Data de Nascimento</dt>
									<dd>
										<input type="text" name="responsavel_datanascimento" value="<?php echo $values['responsavel_datanascimento'];?>" class="data" />
									</dd>
								</dl>
							</div> 	

							<div class="colunas4 js-box-possuiResponsavel">
								<dl class="dl2">
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
								<dl class="dl2">
									<dt>Estado Civil</dt>
									<dd>
										<select name="estado_civil" class="chosen">
											<option value=""></option>
											<?php
											foreach($_pacienteEstadoCivil as $k=>$v) {
												echo '<option value="'.$k.'"'.(($values['estado_civil']==$k)?' selected':'').'>'.$v.'</option>';
											}
											?>
										</select>
									</dd>
								</dl>
							</div>
						</fieldset>
					</div>
						
					</div>
				</div>
			</section>


		</form>
		
<?php
include "includes/footer.php";
?>