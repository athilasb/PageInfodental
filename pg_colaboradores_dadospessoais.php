<?php
	include "includes/header.php";
	include "includes/nav.php";

	$_table=$_p."colaboradores";
	$_page=basename($_SERVER['PHP_SELF']);

	$_width=400;
	$_height=400;
	$_dir="arqs/colaboradores/";

	$_tipoCRO = array(
		'CD'  => 'CD',
		'ASB' => 'ASB',
		'TSB' => 'TSB',
		'TPD' => 'TPD',
		'APD' => 'APD'
	);

	$colaborador=$cnt='';
	if(isset($_GET['id_colaborador']) and is_numeric($_GET['id_colaborador'])) {
		$sql->consult($_table,"*","where id='".$_GET['id_colaborador']."'");
		if($sql->rows) {
			$colaborador=mysqli_fetch_object($sql->mysqry);
			$cnt=$colaborador;
		}
	}

	$campos=explode(",","nome,sexo,rg,rg_orgaoemissor,rg_estado,cpf,data_nascimento,estado_civil,telefone1,telefone2,nome_pai,nome_mae,email,instagram,linkedin,facebook,cep,endereco,numero,complemento,bairro,estado,cidade,id_cidade,escolaridade,cro,uf_cro,tipo_cro,calendario_cor,inicial_cd");
	
	foreach($campos as $v) $values[$v]='';
	$values['calendario_cor']="#c18c6a";

	if(is_object($colaborador)) {
		$values=$adm->values($campos,$cnt);
	}

	if(isset($_POST['acao']) and $_POST['acao']=="wlib") {
		$vSQL=$adm->vSQL($campos,$_POST);
		$values=$adm->values;
		$processa=true;

		if(empty($cnt) or (is_object($cnt) and $cnt->cpf!=cpf($_POST['cpf']))) {
			$sql->consult($_table,"*","where cpf='".addslashes(cpf($_POST['cpf']))."' and lixo=0");
			if($sql->rows) {
				$processa=false;
				$jsc->jAlert("Já existe colaborador cadastrado com este CPF","erro",""); 
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
				$jsc->jAlert("Informações salvas com sucesso!","sucesso","document.location.href='".$_page."?form=1&edita=".$id_reg."&".$url."'");
				die();
			}
		}
	}

?>
	<script src="js/jquery.colorpicker.js"></script>
	<script type="text/javascript">
		var _cidade='<?php echo $values['cidade'];?>';
		var _cidadeID='<?php echo empty($values['id_cidade'])?0:$values['id_cidade'];?>';

		$(function(){
			$('input[name=calendario_cor]').ColorPicker({
				color: '<?php echo $values['calendario_cor'];?>',
				onShow: function (colpkr) {$(colpkr).fadeIn(500);return false;},
				onHide: function (colpkr) {$(colpkr).fadeOut(500);return false;},
				onChange: function (hsb, hex, rgb) {console.log(hex);$('input[name=calendario_cor]').css('backgroundColor', '#' + hex).val('#'+hex);}
			});
			
			$('input[name=calendario_cor]').css('backgroundColor','<?php echo $values['calendario_cor'];?>');

			$('select[name=tipo_cro]').change(function(){
				let tipo_cro = $(this).val();
				if(tipo_cro == 'CD') {
					$('.js-inicialCD').show();
					$('.js-calendarioCor').show();
				} else {
					$('.js-inicialCD').hide();
					$('.js-calendarioCor').hide();
				}
			});
			$("#upload_link").on('click', function(e){
			    e.preventDefault();
			    $("#upload:hidden").trigger('click');
			});
		});
	</script>
	<section class="content">
		
		<?php
		if(is_object($cnt)) require_once("includes/abaColaborador.php");
		?>

		<form method="post" class="form formulario-validacao"  autocomplete="off" enctype="multipart/form-data">
			<input type="hidden" name="acao" value="wlib" />	
			<?php
			if(empty($cnt)) {
			?>
			<section class="filtros">
				<h1 class="filtros__titulo">Colaborador</h1>
				<div class="filtros-acoes">
					<a href="pg_colaboradores.php"><i class="iconify" data-icon="bx-bx-left-arrow-alt"></i></a>
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
								<a href="?deletaColaborador=<?php echo $colaborador->id."&".$url;?>" class="js-deletar"><i class="iconify" data-icon="bx-bx-trash"></i></a>
								<a href="javascript:;"><i class="iconify" data-icon="bx-bx-printer"></i></a>
								<a href="javascript:;" class="azul  btn-submit"><i class="iconify" data-icon="bx-bx-check"></i><span>salvar</span></a>
							</div>
						</div>

					</div>

				
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
										<input type="text" name="nome" value="<?php echo $values['nome'];?>" class="obg" style="width: 840px;" />
									</dd>
								</dl>
								<dl>
									<dt style="margin-left: 30px;">Sexo</dt>
									<dd style="margin-left: 30px;">
										<select name="sexo" class="">
											<option value="">-</option>
											<option value="M"<?php echo $values['sexo']=="M"?" selected":"";?>>Masculino</option>
											<option value="M"<?php echo $values['sexo']=="F"?" selected":"";?>>Feminino</option>
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
										$ftColaborador='arqs/colaboradores/'.$cnt->id.".".$cnt->foto;
										if(file_exists($ftColaborador)) {
											$ft=$ftColaborador;
										}
									}
									?>
									<dl>
										<dd><a href="" id="upload_link"><img src="<?php echo $ft;?>" width="200" style="border: solid 1px #CCC;padding:2px;" /></a></dd>
									</dl>
									<input type="file" name="foto" id="upload" style="display: none;" />
									
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
												<input type="text" name="cpf" value="<?php echo $values['cpf'];?>" class="cpf" />
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
									<dl>
										<dt>Nome do Pai</dt>
										<dd>
											<input type="text" name="nome_pai" value="<?php echo $values['nome_pai'];?>" class="" />
										</dd>
									</dl>
									<dl>
										<dt>Nome da Mãe</dt>
										<dd>
											<input type="text" name="nome_mae" value="<?php echo $values['nome_mae'];?>" class="" />
										</dd>
									</dl>
								</div>
							</div>

						</fieldset>

						<script type="text/javascript">

							$(function(){
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
									<dt>Linkedin</dt>
									<dd><input type="text" name="linkedin" class="noupper" value="<?php echo $values['linkedin'];?>" /></dd>
								</dl>	
								<dl class="dl2">
									<dt>Facebook</dt>
									<dd><input type="text" name="facebook" class="noupper" value="<?php echo $values['facebook'];?>" /></dd>
								</dl>
							</div>
						</fieldset>
						
						<fieldset style="margin:0;">
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
									<input type="text" name="endereco" value="<?php echo $values['endereco']; ?>" class="" />
								</dd>
							</dl>
							<dl class="dl2">
								<dt>Complemento</dt>
								<dd>
									<input type="text" name="complemento" value="<?php echo $values['complemento']; ?>" class="" />
								</dd>
							</dl>
						</fieldset>

						<fieldset style="margin:0;">
							<legend style="font-size: 12px;">
								<div class="filter-group">
									<div class="filter-title">
										<span class="badge">4</span> Dados Complementares
									</div>
								</div>
							</legend>

							<dl>
								<dt>Escolaridade</dt>
								<dd>
									<input type="text" name="escolaridade" value="<?php echo $values['escolaridade']; ?>" class="" />
								</dd>
							</dl>
							<div class="colunas4">
								<dl class="dl2">
									<dt>CRO</dt>
									<dd>
										<input type="text" name="cro" value="<?php echo $values['cro']; ?>" class="" />
									</dd>
								</dl>
								<dl class="dl2">
									<dt>UF do CRO</dt>
									<dd>
										<?php $inEstado=strtoupperWLIB($values['uf_cro']);?><select name="uf_cro" class="chosen"><option value=""></option><option value="AC"<?php echo $inEstado=="AC"?" selected":"";?>>ACRE</option><option value="AL"<?php echo $inEstado=="AL"?" selected":"";?>>ALAGOAS</option><option value="AM"<?php echo $inEstado=="AM"?" selected":"";?>>AMAZONAS</option><option value="AP"<?php echo $inEstado=="AP"?" selected":"";?>>AMAPÁ</option><option value="BA"<?php echo $inEstado=="BA"?" selected":"";?>>BAHIA</option><option value="CE"<?php echo $inEstado=="CE"?" selected":"";?>>CEARÁ</option><option value="DF"<?php echo $inEstado=="DF"?" selected":"";?>>DISTRITO FEDERAL</option><option value="ES"<?php echo $inEstado=="ES"?" selected":"";?>>ESPÍRITO SANTO</option><option value="GO"<?php echo $inEstado=="GO"?" selected":"";?>>GOIÁS</option><option value="MA"<?php echo $inEstado=="MA"?" selected":"";?>>MARANHÃO</option><option value="MT"<?php echo $inEstado=="MT"?" selected":"";?>>MATO GROSSO</option><option value="MS"<?php echo $inEstado=="MS"?" selected":"";?>>MATO GROSSO DO SUL</option><option value="MG"<?php echo $inEstado=="MG"?" selected":"";?>>MINAS GERAIS</option><option value="PA"<?php echo $inEstado=="PA"?" selected":"";?>>PARÁ</option><option value="PB"<?php echo $inEstado=="PB"?" selected":"";?>>PARAÍBA</option><option value="PR"<?php echo $inEstado=="PR"?" selected":"";?>>PARANÁ</option><option value="PE"<?php echo $inEstado=="PE"?" selected":"";?>>PERNANBUMCO</option><option value="PI"<?php echo $inEstado=="PI"?" selected":"";?>>PIAUÍ</option><option value="RJ"<?php echo $inEstado=="RJ"?" selected":"";?>>RIO DE JANEIRO</option><option value="RN"<?php echo $inEstado=="RN"?" selected":"";?>>RIO GRANDE DO NORTE</option><option value="RO"<?php echo $inEstado=="RO"?" selected":"";?>>RONDÔNIA</option><option value="RS"<?php echo $inEstado=="RS"?" selected":"";?>>RIO GRANDE DO SUL</option><option value="RR"<?php echo $inEstado=="RR"?" selected":"";?>>RORAIMA</option><option value="SC"<?php echo $inEstado=="SC"?" selected":"";?>>SANTA CATARINA</option><option value="SE"<?php echo $inEstado=="SE"?" selected":"";?>>SERGIPE</option><option value="SP"<?php echo $inEstado=="SP"?" selected":"";?>>SÃO PAULO</option><option value="TO"<?php echo $inEstado=="TO"?" selected":"";?>>TOCANTINS</option></select>
									</dd>
								</dl>
							</div>
							<div class="colunas3">
								
								<dl>
									<dt>Tipo do CRO</dt>
									<dd>
										<select name="tipo_cro" class="chosen">
											<option value="">-</option>
											<?php
											foreach($_tipoCRO as $k=>$v) {
												echo '<option value="'.$k.'"'.(($values['tipo_cro']==$k)?' selected':'').'>'.$v.'</option>';
											}
											?>
										</select>
									</dd>
								</dl>
								<dl class="dl js-inicialCD" style="display:none;">
									<dt>Inicial do CD</dt>
									<dd>
										<input type="text" name="inicial_cd" value="<?php echo $values['inicial_cd'];?>" class="" />
									</dd>
								</dl>
								<dl class="dl js-calendarioCor" style="display:none;">
									<dt>Cor Calendário</dt>
									<dd>
										<input type="text" name="calendario_cor" value="<?php echo $values['calendario_cor'];?>" class="" />
									</dd>
								</dl>
								
							</div>
						</fieldset>
						
					</div>
				</div>
			</section>


		</form>
		
<?php
include "includes/footer.php";
?>