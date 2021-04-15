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
	$title="";
	include "includes/header.php";
	include "includes/nav.php";

	if($usr->tipo!="admin" and !in_array("clientes",$_usuariosPermissoes)) {
		$jsc->jAlert("Você não tem permissão para acessar esta área!","erro","document.location.href='dashboard.php'");
		die();
	}
	$values=$adm->get($_GET);

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

?>


<section class="content">

	<?php /*<header class="caminho">
		<h1 class="caminho__titulo">Contatos <i class="iconify" data-icon="bx-bx-chevron-right"></i> <strong>Pacientes</strong></h1>
		<a href="javascript:;" class="caminho__tutorial button button__sec"><i class="iconify" data-icon="ic-baseline-slow-motion-video"></i> ASSISTIR TUTORIAL</a>
	</header>*/?>

	<?php
	$_table=$_p."pacientes";
	$_page=basename($_SERVER['PHP_SELF']);

	$_width=400;
	$_height=400;
	$_dir="arqs/pacientes/";

	$_cidades=array();
	$sql->consult($_p."cidades","*","");
	while($x=mysqli_fetch_object($sql->mysqry)) $_cidades[$x->id]=$x;
	
	if(isset($_GET['form'])) {
		$cnt='';
		$campos=explode(",","nome,situacao,noem,sexo,foto,rg,rg_orgaoemissor,rg_estado,cpf,data_nascimento,profissao,estado_civil,telefone1,telefone1_whatsapp,telefone1_whatsapp_permissao,telefone2,email,instagram,instagram_naopossui,musica,indicacao_tipo,indicacao,cep,endereco,numero,complemento,bairro,estado,cidade,id_cidade,responsavel_nome,responsavel_rg,responsavel_rg_orgaoemissor,responsavel_rg_estado,responsavel_cpf,responsavel_telefone,responsavel_profissao,responsavel_grauparentesco");
		
		foreach($campos as $v) $values[$v]='';
		$values['data']=date('d/m/Y H:i');
		$values['sexo']='M';

		if(isset($_GET['edita']) and is_numeric($_GET['edita'])) {
			$sql->consult($_table,"*,date_format(data,'%d/%m/%Y %H:%i') as dataf","where id='".$_GET['edita']."'");
			if($sql->rows) {
				$cnt=mysqli_fetch_object($sql->mysqry);
				
				$values=$adm->values($campos,$cnt);
				$values['data']=$cnt->dataf;

			} else {
				$jsc->jAlert("Informação não encontrada!","erro","document.location.href='".$_page."'");
				die();
			}
		}
		if(isset($_POST['acao']) and $_POST['acao']=="wlib") {
			$vSQL=$adm->vSQL($campos,$_POST);
			$values=$adm->values;
			$processa=true;

			if(empty($cnt) or (is_object($cnt) and $cnt->cpf!=cpf($_POST['cpf']))) {
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
					$jsc->jAlert("Informações salvas com sucesso!","sucesso","document.location.href='".$_page."?form=1&edita=".$id_reg."&".$url."'");
					die();
				}
			}
		}	
	?>

			<script>
				var _cidade='<?php echo $values['cidade'];?>';
				var _cidadeID='<?php echo empty($values['id_cidade'])?0:$values['id_cidade'];?>';

				$(function(){
					$('.js-btn-profissao').click(function(){ 
						var id_profissao=$('select[name=profissao]').val();
						$.fancybox.open({
							src  : `<?php echo $_page;?>?ajax=profissao&id_profissao=${id_profissao}`,
							type : 'iframe',
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
						
			    });
			</script>
			<div class="acoes">
				<a href="<?php echo $_page."?".$url;?>" class="button button__lg button__ter"><i class="iconify" data-icon="bx-bx-left-arrow-alt"></i></a>
				<?php 
				if(is_object($cnt)) {
				?>
				<a data-fancybox data-type="ajax" data-src="ajax/log.php?table=<?php echo $_table;?>&id=<?php echo $cnt->id;?>" href="javascript:;" class="button button__lg button__ter"><span class="iconify" data-icon="fa:history" data-inline="false"></span> Logs</a>
				<a href="pg_contatos_pacientes_fotos.php?id_paciente=<?php echo $cnt->id;?>" class="button button__lg button__ter"><i class="iconify" data-icon="jam:pictures"></i> Fotos</a>
				<?php
				}
				?>
				<a href="javascript:;" class="button button__lg btn-submit"><i class="iconify" data-icon="bx-bx-check"></i> Salvar</a>			
			</div>

			<form method="post" class="form formulario-validacao"  autocomplete="off" enctype="multipart/form-data">
				<input type="hidden" name="acao" value="wlib" />

				<fieldset>
					<legend>Dados de Cadastro</legend>
					<div class="colunas4">
						<dl>
							<dt>Data do Cadastro</dt>
							<dd><input type="text" name="" value="<?php echo $values['data'];?>" disabled /></dd>
						</dl>
						<?php
						if(is_object($cnt)) {
						?>
						<dl>	
							<dt>ID Paciente</dt>
							<dd><input type="text" disabled="" value="<?php echo $cnt->id;?>" /></dd>
						</dl>
						<?php
						}
						?>
						<dl>
							<dt>Idade</dt>
							<dd><input type="text" class="idade" disabled /></dd>
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
					<?php
					/*if(is_object($cnt)) {
						$sql->consult($_p."usuarios","*","WHERE id='".$cnt->id_usuario."'");
						if($sql->rows){
							$criador=mysqli_fetch_object($sql->mysqry);
					?>
					<dl>
						<dt>Criado por</dt>
						<dd><input type="text" name="" value="<?php echo utf8_encode($criador->nome);?>" disabled /></dd>
					</dl>
					<?php
						}
					}*/
					?>
					<script type="text/javascript">
						$(function(){
							$('select[name=indicacao_tipo]').change(function(){
								let id_indicacao = $(this).find('option:selected').attr('data-id');
								let data = `ajax=indicacoesLista&id_indicacao=${id_indicacao}`;
								$.ajax({
									type:"POST",
									data:data,
									success:function(rtn) {
										$('select[name=indicacao] option').remove();
										$('select[name=indicacao]').append(`<option value=""></option>`);
										console.log(rtn.indicacoes);
										if(rtn.indicacoes) {
											rtn.indicacoes.forEach(x => {
												let option = `<option value="${x.id}">${x.titulo}</option>`
												$('select[name=indicacao]').append(`${option}`);
											});
										}
										$('select[name=indicacao]').trigger('chosen:updated')
									}
								})
							}).trigger('change');
						})
					</script>
					<div class="colunas4">
						<dl class="dl2">
							<dt>Tipo de Indicação</dt>
							<dd>
								<select name="indicacao_tipo" class="chosen">
									<option value=""></option>
									<?php
									foreach($_pacienteIndicacoes as $v) {
										echo '<option value="'.$v->id.'"'.($values['indicacao_tipo']==$v->id?' selected':'').' data-id="'.$v->id.'">'.utf8_encode($v->titulo).'</option>';
									}
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
				</fieldset>

				<fieldset>
					<legend>Dados Pessoais</legend>
					
					<div class="colunas5">
						<?php
						if(is_object($cnt)) {
							$foto=$_dir.$cnt->id.".".$cnt->foto;
							if(file_exists($foto)) {
						?>
						<dl>	
							<dt>
								<center>
									<a href="<?php echo $foto;?>" data-fancybox><img src="<?php echo $foto;?>" width="90%" style="border: solid 1px #CCC;padding: 2px;" /></a>
								</center>
							</dt>
						</dl>
						<?php
							}
						}
						?>
						<dl class="dl2">
							<dt>Nome</dt>
							<dd>
								<input type="text" name="nome" value="<?php echo $values['nome'];?>" class="obg"/>
							</dd>
						</dl>
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
								<?php $inEstado=strtoupperWLIB($values['rg_orgaoemissor']);?><select name="rg_orgaoemissor" class="chosen"><option value=""></option><option value="AC"<?php echo $inEstado=="AC"?" selected":"";?>>ACRE</option><option value="AL"<?php echo $inEstado=="AL"?" selected":"";?>>ALAGOAS</option><option value="AM"<?php echo $inEstado=="AM"?" selected":"";?>>AMAZONAS</option><option value="AP"<?php echo $inEstado=="AP"?" selected":"";?>>AMAPÁ</option><option value="BA"<?php echo $inEstado=="BA"?" selected":"";?>>BAHIA</option><option value="CE"<?php echo $inEstado=="CE"?" selected":"";?>>CEARÁ</option><option value="DF"<?php echo $inEstado=="DF"?" selected":"";?>>DISTRITO FEDERAL</option><option value="ES"<?php echo $inEstado=="ES"?" selected":"";?>>ESPÍRITO SANTO</option><option value="GO"<?php echo $inEstado=="GO"?" selected":"";?>>GOIÁS</option><option value="MA"<?php echo $inEstado=="MA"?" selected":"";?>>MARANHÃO</option><option value="MT"<?php echo $inEstado=="MT"?" selected":"";?>>MATO GROSSO</option><option value="MS"<?php echo $inEstado=="MS"?" selected":"";?>>MATO GROSSO DO SUL</option><option value="MG"<?php echo $inEstado=="MG"?" selected":"";?>>MINAS GERAIS</option><option value="PA"<?php echo $inEstado=="PA"?" selected":"";?>>PARÁ</option><option value="PB"<?php echo $inEstado=="PB"?" selected":"";?>>PARAÍBA</option><option value="PR"<?php echo $inEstado=="PR"?" selected":"";?>>PARANÁ</option><option value="PE"<?php echo $inEstado=="PE"?" selected":"";?>>PERNANBUMCO</option><option value="PI"<?php echo $inEstado=="PI"?" selected":"";?>>PIAUÍ</option><option value="RJ"<?php echo $inEstado=="RJ"?" selected":"";?>>RIO DE JANEIRO</option><option value="RN"<?php echo $inEstado=="RN"?" selected":"";?>>RIO GRANDE DO NORTE</option><option value="RO"<?php echo $inEstado=="RO"?" selected":"";?>>RONDÔNIA</option><option value="RS"<?php echo $inEstado=="RS"?" selected":"";?>>RIO GRANDE DO SUL</option><option value="RR"<?php echo $inEstado=="RR"?" selected":"";?>>RORAIMA</option><option value="SC"<?php echo $inEstado=="SC"?" selected":"";?>>SANTA CATARINA</option><option value="SE"<?php echo $inEstado=="SE"?" selected":"";?>>SERGIPE</option><option value="SP"<?php echo $inEstado=="SP"?" selected":"";?>>SÃO PAULO</option><option value="TO"<?php echo $inEstado=="TO"?" selected":"";?>>TOCANTINS</option></select>
							</dd>
						</dl>
						<dl>
							<dt>CPF</dt>
							<dd>
								<input type="text" name="cpf" value="<?php echo $values['cpf'];?>" class="cpf obg" />
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
									<option value=""></option>
									<?php
									foreach($_pacienteEstadoCivil as $k=>$v) {
										echo '<option value="'.$k.'"'.(($values['estado_civil']==$k)?' selected':'').'>'.$v.'</option>';
									}
									?>
								</select>
							</dd>
						</dl>
						<dl class="dl2">
							<dt>Profissão</dt>
							<dd>
								<select name="profissao" class="chosen" style="width:86%;float:left;">
									<option value=""></option>
									<?php
									foreach($_profissoes as $v) {
										echo '<option value="'.$v->id.'"'.(($values['profissao']==$v->id)?' selected':'').'>'.utf8_encode($v->titulo).'</option>';
									}
									?>
								</select>

								<a href="javascript:;" class="botao botao-principal tooltip js-btn-profissao" title="Gerenciar Profissões" style="float:right;margin-left:5px;"><span class="iconify" data-icon="octicon:gear"></span></a>
							</dd>
						</dl>

						<dl class="">
							<dt>Sexo</dt>
							<dd>
								<select name="sexo" class="obg">
									<option value="">-</option>
									<option value="M"<?php echo $values['sexo']=="M"?" selected":"";?>>Masculino</option>
									<option value="M"<?php echo $values['sexo']=="F"?" selected":"";?>>Feminino</option>
								</select>
							</dd>
						</dl>

						<dl class="">
							<dt>Preferência Musical</dt>
							<dd><input type="text" name="musica" value="<?php echo $values['musica'];?>" /></dd>
						</dl>

						<dl>

							<dt>Foto</dt>
							<dd><input type="file" name="foto" /></dd>
						</dl>
					</div>
				</fieldset>
				<script type="text/javascript">
					$(function(){
						  $('input[name=telefone1]').mobilePhoneNumber({allowPhoneWithoutPrefix: '+55'}).bind('country.mobilePhoneNumber', function(echo, country) {
					      	let countryOut = country || '';
					      	$(this).parent().parent().find('.country').remove();
					      	$(this).before(`<input type="text" diabled style="width:14%;float:left" class="country" value="${countryOut}" />`)
					      }).trigger('keyup');

						  $('input[name=telefone2]').mobilePhoneNumber({allowPhoneWithoutPrefix: '+55'}).bind('country.mobilePhoneNumber', function(echo, country) {
					      	let countryOut = country || '';
					      	$(this).parent().parent().find('.country').remove();
					      	$(this).before(`<input type="text" diabled style="width:14%;float:left" class="country" value="${countryOut}" />`)
					      }).trigger('keyup');
					})
				</script>
				<fieldset>
					<legend>Dados de Contato</legend>
					<div class="colunas5">
						<dl class="dl2">
							<dt>Telefone 1 </dt>
							<dd><input type="text" name="telefone1" style="width:85%;float:right;" class="obg" attern="\d*" x-autocompletetype="tel" value="<?php echo $values['telefone1'];?>" /></dd>
						</dl>
						<dl>
							<dd><label><input type="checkbox" name="telefone1_whatsapp"<?php echo $values['telefone1_whatsapp']==1?" checked":"";?> /> Numero de Whatsapp</label></dd>
							<dd><label><input type="checkbox" name="telefone1_whatsapp_permissao"<?php echo $values['telefone1_whatsapp_permissao']==1?" checked":"";?> /> Permissão para contato</label></dd>
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
						<dl>
							<dt>Instagram</dt>
							<dd><input type="text" name="instagram" class="noupper" value="<?php echo $values['instagram'];?>" /></dd>
						</dl>
						<dl>
							<dt>&nbsp;</dt>
							<dd><label><input type="checkbox" name="instagram_naopossui" value="1"<?php echo $values['instagram_naopossui']==1?" checked":"";?> /> Não possui instagram</label></dd>
						</dl>
					</div>
				</fieldset>

				<fieldset>
					<legend>Dados de Endereço</legend>
					<div class="colunas3">
						<dl>
							<dt>CEP</dt>
							<dd><input type="text" name="cep" id="inpt-cep" value="<?php echo $values['cep'];?>" class="cep" autocomplete="off" /></dd>
						</dl>
						<dl>
							<dt>&nbsp;</dt>
							<dd><button type="button" id="js-consultacep"><i class="icon-search"></i>consultar</button></dd>
						</dl>
					</div>
					<div class="colunas3">

						<dl>
							<dt>Bairro</dt>
							<dd><input type="text" name="bairro" value="<?php echo $values['bairro']; ?>" class="" /></dd>
						</dl>
						<dl>
							<dt>Estado</dt>
							<dd>
								<?php $inEstado=strtoupperWLIB($values['estado']);?><select name="estado" class="js-estado"><option value="">SELECIONE</option><option value="AC"<?php echo $inEstado=="AC"?" selected":"";?>>ACRE</option><option value="AL"<?php echo $inEstado=="AL"?" selected":"";?>>ALAGOAS</option><option value="AM"<?php echo $inEstado=="AM"?" selected":"";?>>AMAZONAS</option><option value="AP"<?php echo $inEstado=="AP"?" selected":"";?>>AMAPÁ</option><option value="BA"<?php echo $inEstado=="BA"?" selected":"";?>>BAHIA</option><option value="CE"<?php echo $inEstado=="CE"?" selected":"";?>>CEARÁ</option><option value="DF"<?php echo $inEstado=="DF"?" selected":"";?>>DISTRITO FEDERAL</option><option value="ES"<?php echo $inEstado=="ES"?" selected":"";?>>ESPÍRITO SANTO</option><option value="GO"<?php echo $inEstado=="GO"?" selected":"";?>>GOIÁS</option><option value="MA"<?php echo $inEstado=="MA"?" selected":"";?>>MARANHÃO</option><option value="MT"<?php echo $inEstado=="MT"?" selected":"";?>>MATO GROSSO</option><option value="MS"<?php echo $inEstado=="MS"?" selected":"";?>>MATO GROSSO DO SUL</option><option value="MG"<?php echo $inEstado=="MG"?" selected":"";?>>MINAS GERAIS</option><option value="PA"<?php echo $inEstado=="PA"?" selected":"";?>>PARÁ</option><option value="PB"<?php echo $inEstado=="PB"?" selected":"";?>>PARAÍBA</option><option value="PR"<?php echo $inEstado=="PR"?" selected":"";?>>PARANÁ</option><option value="PE"<?php echo $inEstado=="PE"?" selected":"";?>>PERNANBUMCO</option><option value="PI"<?php echo $inEstado=="PI"?" selected":"";?>>PIAUÍ</option><option value="RJ"<?php echo $inEstado=="RJ"?" selected":"";?>>RIO DE JANEIRO</option><option value="RN"<?php echo $inEstado=="RN"?" selected":"";?>>RIO GRANDE DO NORTE</option><option value="RO"<?php echo $inEstado=="RO"?" selected":"";?>>RONDÔNIA</option><option value="RS"<?php echo $inEstado=="RS"?" selected":"";?>>RIO GRANDE DO SUL</option><option value="RR"<?php echo $inEstado=="RR"?" selected":"";?>>RORAIMA</option><option value="SC"<?php echo $inEstado=="SC"?" selected":"";?>>SANTA CATARINA</option><option value="SE"<?php echo $inEstado=="SE"?" selected":"";?>>SERGIPE</option><option value="SP"<?php echo $inEstado=="SP"?" selected":"";?>>SÃO PAULO</option><option value="TO"<?php echo $inEstado=="TO"?" selected":"";?>>TOCANTINS</option></select>
							</dd>
						</dl>
						<dl>
							<dt>Cidade</dt>
							<dd>
								<select name="id_cidade" class="js-cidade">
									<option value="">-</option>
								</select>
								<input type="hidden" name="cidade" value="<?php echo $values['cidade'];?>"/>
							</dd>
						</dl>
					</div>
					<div class="colunas3">
						<dl>
							<dt>Endereço</dt>
							<dd>
								<input type="text" name="endereco" value="<?php echo $values['endereco']; ?>" class="" />
							</dd>
						</dl>
						<dl>
							<dt>Número</dt>
							<dd>
								<input type="text" name="numero" value="<?php echo $values['numero']; ?>" class="" />
							</dd>
						</dl>
						<dl>
							<dt>Complemento</dt>
							<dd>
								<input type="text" name="complemento" value="<?php echo $values['complemento']; ?>" class="" />
							</dd>
						</dl>
					</div>
				</fieldset>

				<fieldset>
					<legend>Responsável</legend>
					
					<div class="colunas5">
						<dl class="dl2">
							<dt>Nome</dt>
							<dd>
								<input type="text" name="responsavel_nome" value="<?php echo $values['responsavel_nome'];?>" class="" />
							</dd>
						</dl>
						<dl>
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
					<div class="colunas4">
						<dl>
							<dt>CPF</dt>
							<dd>
								<input type="text" name="responsavel_cpf" value="<?php echo $values['responsavel_cpf'];?>" class="cpf" />
							</dd>
						</dl>

						<script type="text/javascript">
							$(function(){
								  $('input[name=responsavel_telefone]').mobilePhoneNumber({allowPhoneWithoutPrefix: '+55'}).bind('country.mobilePhoneNumber', function(echo, country) {
							      	let countryOut = country || '';
							      	$(this).parent().parent().find('.country').remove();
							      	$(this).before(`<input type="text" diabled style="width:14%;float:left" class="country" value="${countryOut}" />`)
							      }).trigger('keyup');
							})
						</script>
						<dl class="dl2">
							<dt>Telefone</dt>
							<dd>
								<input type="text" name="responsavel_telefone" value="<?php echo $values['responsavel_telefone'];?>" style="width:85%;float:right;" class="" />
							</dd>
						</dl>


						<dl>
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
					<div class="colunas4">

						<dl class="dl2">
							<dt>Profissão</dt>
							<dd>
								<select name="responsavel_profissao" class="chosen" style="width:88%;float:left;">
									<option value=""></option>
									<?php
									foreach($_profissoes as $v) {
										echo '<option value="'.$v->id.'"'.(($values['responsavel_profissao']==$v->id)?' selected':'').'>'.utf8_encode($v->titulo).'</option>';
									}
									?>
								</select>
								<a href="javascript:;" class="botao botao-principal tooltip js-btn-profissao" title="Gerenciar Profissões" style="float:right;margin-left:5px;"><span class="iconify" data-icon="octicon:gear"></span></a>
							</dd>
						</dl>
					</div>
				</fieldset>
				&nbsp;<br />&nbsp;<br />&nbsp;<br />
			</form>
	<?php
	} else {
	?>

		<section class="grid grid_2">
			<?php
			$sql->consult($_p."pacientes","*","where data>='".date('Y-m-d H:i:s',strtotime(date('Y-m-d H:i:s')." - 1 year"))."' and lixo=0");
			$total=$sql->rows;

			// Grafico 2: Idade
			$grafico2Labels=array();
			for($i=0;$i<=70;$i+=10) {
				if($i==70) {
					$grafico2Labels[]="+71";
				} else {
					$grafico2Labels[]=($i==0?$i:$i+1)."-".($i+10);
				}
			}

			$pacintesQuantidade=array();
			$pacientesIdade=array();
			while($x=mysqli_fetch_object($sql->mysqry)) {
				$mes=date('m',strtotime($x->data));
				$ano=date('y',strtotime($x->data));

				if(!isset($pacintesQuantidade[substr(mes($mes),0,3)."/".$ano])) $pacintesQuantidade[substr(mes($mes),0,3)."/".$ano]=0;
				$pacintesQuantidade[substr(mes($mes),0,3)."/".$ano]++;
				
				$idade=idade($x->data_nascimento);

				if($idade<=10) {
					if(!isset($pacientesIdade[0])) $pacientesIdade[0]=0;
					$pacientesIdade[0]++;
				} else if($idade<=20) {
					if(!isset($pacientesIdade[1])) $pacientesIdade[1]=0;
					$pacientesIdade[1]++;
				} else if($idade<=30) {
					if(!isset($pacientesIdade[2])) $pacientesIdade[2]=0;
					$pacientesIdade[2]++;
				} else if($idade<=40) {
					if(!isset($pacientesIdade[3])) $pacientesIdade[3]=0;
					$pacientesIdade[3]++;
				} else if($idade<=50) {
					if(!isset($pacientesIdade[4])) $pacientesIdade[4]=0;
					$pacientesIdade[4]++;
				} else if($idade<=60) {
					if(!isset($pacientesIdade[5])) $pacientesIdade[5]=0;
					$pacientesIdade[5]++;
				} else if($idade<=70) {
					if(!isset($pacientesIdade[6])) $pacientesIdade[6]=0;
					$pacientesIdade[6]++;
				} 
				if(!isset($grafico2[$idade])) $grafico2[$idade]=0;
				$grafico2[$idade]++;
			}	


			// Grafico 2: Idade
			$grafico2Data=array();
			foreach($grafico2Labels as $key=>$v) {
				$grafico2Data[$key]=isset($pacientesIdade[$key])?$pacientesIdade[$key]:0;
			}
			//echo json_encode($grafico2Data);

			// Grafico 1: Quantidade
			$grafico1Labels=array();
			$mes=date('m');
			$ano=date('y');
			for($i=1;$i<=12;$i++) {
				$grafico1Labels[]=substr(mes($mes),0,3)."/".$ano;
				$mes--;
				if($mes==0) {
					$ano--;
					$mes=12;
				}
			}

			$grafico1Labels=array_reverse($grafico1Labels);
			foreach($grafico1Labels as $key) { 
				if(!isset($pacintesQuantidade[$key])) $grafico1Data[]=0;
				else { //echo $key."->".$grafico1DataAux[$key]."<BR>";
					$grafico1Data[]=$pacintesQuantidade[$key];
				}
			}

			

			?>
			<section class="box">
				<div class="lista-botoes">
					<a href="javascript:;" class="lista-botoes__item js-grafico" data-grafico="1">
						<i class="iconify" data-icon="clarity-group-solid"></i>
						<div class="lista-botoes__inner1">
							<h1 class="lista-botoes__titulo">Total</h1>
							<h2 class="lista-botoes__valor"><?php echo $total;?></h2>
						</div>
					</a>
					<a href="javascript:;" class="lista-botoes__item js-grafico" data-grafico="2">
						<i class="iconify" data-icon="cil-birthday-cake"></i>
						<div class="lista-botoes__inner1">
							<h1 class="lista-botoes__titulo">Distribuição por</h1>
							<h2 class="lista-botoes__valor">Idade</h2>
						</div>
					</a>
					<a href="javascript:;" class="lista-botoes__item js-grafico" data-grafico="3">
						<i class="iconify" data-icon="mdi-gender-male-female"></i>
						<div class="lista-botoes__inner1">
							<h1 class="lista-botoes__titulo">Distribuição por</h1>
							<h2 class="lista-botoes__valor">Gênero</h2>
						</div>
					</a>
					<a href="javascript:;" class="lista-botoes__item js-grafico" data-grafico="4">
						<i class="iconify" data-icon="carbon-location"></i>
						<div class="lista-botoes__inner1">
							<h1 class="lista-botoes__titulo">Distribuição por</h1>
							<h2 class="lista-botoes__valor">Localização</h2>
						</div>
					</a>
					<a href="javascript:;" class="lista-botoes__item js-grafico" data-grafico="5">
						<i class="iconify" data-icon="tabler-user-plus"></i>
						<div class="lista-botoes__inner1">
							<h1 class="lista-botoes__titulo">Novos Pacientes</h1>
							<h2 class="lista-botoes__valor">9 / mês</h2>
						</div>
					</a>
				</div>
				<div class="grafico">
					<script>
					$(function() {
						
						$('.js-grafico').click(function(){
							let grafico = $(this).attr('data-grafico');

							$(`.box-grafico`).hide();
							$(`#grafico${grafico}`).show();
							$(`.js-grafico`).removeClass('active');
							$(this).addClass('active');
						});

						$('.js-grafico:eq(0)').trigger('click')

						var ctx = document.getElementById('grafico1').getContext('2d');
						var gradientStroke = ctx.createLinearGradient(0,230,0,50);
						gradientStroke.addColorStop(1, 'rgba(254,71,2,0.2)');
						gradientStroke.addColorStop(0.8, 'rgba(254,71,2,0.1)');
						gradientStroke.addColorStop(0, 'rgba(254,71,2,0)');
						var grafico1 = new Chart(ctx, {    
						    type: 'line',
						    data: {
						        labels: <?php echo json_encode($grafico1Labels);?>,
						        datasets: [{
						            fill:true,
						            borderDashOffset: 0.0,
						            label: 'Pacientes',
						            data: <?php echo json_encode($grafico1Data);?>,
						            backgroundColor: gradientStroke,
						            borderColor:'rgba(254,71,2,0.3)',
						            borderWidth: 1,
						            borderDash: [],
						            borderDashOffset: 0.0
						        }]
						    },
						    options: {
						        scales: {
						            yAxes: [{
						                ticks: {
						                    beginAtZero: true
						                },
						                gridLines: {
						                	drawBorder: false,
						                	color: 'transparent'
						                }
						            }],
						            xAxes: [{
							            gridLines: {
							            	drawBorder: false,
							                color: '#ebebeb',
							                zeroLineColor: "#ebebeb"
							            }	              
							        }]
						        }
						    }
						});

						var ctx = document.getElementById('grafico2').getContext('2d');
						var gradientStroke = ctx.createLinearGradient(0,230,0,50);
						gradientStroke.addColorStop(1, 'rgba(254,71,2,0.2)');
						gradientStroke.addColorStop(0.8, 'rgba(254,71,2,0.1)');
						gradientStroke.addColorStop(0, 'rgba(254,71,2,0)');
						var grafico2 = new Chart(ctx, {    
						    type: 'bar',
						    data: {
						        labels: <?php echo json_encode($grafico2Labels);?>,
						        datasets: [{
						            fill:true,
						            borderDashOffset: 0.0,
						            label: 'Pacientes',
						            data: <?php echo json_encode($grafico2Data);?>,
						            backgroundColor: gradientStroke,
						            borderColor:'rgba(254,71,2,0.3)',
						            borderWidth: 1,
						            borderDash: [],
						            borderDashOffset: 0.0
						        }]
						    },
						    options: {
						        scales: {
						            yAxes: [{
						                ticks: {
						                    beginAtZero: true
						                },
						                gridLines: {
						                	drawBorder: false,
						                	color: 'transparent'
						                }
						            }],
						            xAxes: [{
							            gridLines: {
							            	drawBorder: false,
							                color: '#ebebeb',
							                zeroLineColor: "#ebebeb"
							            }	              
							        }]
						        }
						    }
						});

						var ctx = document.getElementById('grafico3').getContext('2d');
						var gradientStroke = ctx.createLinearGradient(0,230,0,50);
						gradientStroke.addColorStop(1, 'rgba(254,71,2,0.2)');
						gradientStroke.addColorStop(0.8, 'rgba(254,71,2,0.1)');
						gradientStroke.addColorStop(0, 'rgba(254,71,2,0)');
						var grafico3 = new Chart(ctx, {    
						    type: 'line',
						    data: {
						        labels: ["1","2","3","4","5","6","7"],
						        datasets: [{
						            fill:true,
						            borderDashOffset: 0.0,
						            label: '# visitas',
						            data: [1200,1100,1300,1300,500,1200,1345],
						            backgroundColor: gradientStroke,
						            borderColor:'rgba(254,71,2,0.3)',
						            borderWidth: 1,
						            borderDash: [],
						            borderDashOffset: 0.0
						        }]
						    },
						    options: {
						        scales: {
						            yAxes: [{
						                ticks: {
						                    beginAtZero: true
						                },
						                gridLines: {
						                	drawBorder: false,
						                	color: 'transparent'
						                }
						            }],
						            xAxes: [{
							            gridLines: {
							            	drawBorder: false,
							                color: '#ebebeb',
							                zeroLineColor: "#ebebeb"
							            }	              
							        }]
						        }
						    }
						});

						var ctx = document.getElementById('grafico4').getContext('2d');
						var gradientStroke = ctx.createLinearGradient(0,230,0,50);
						gradientStroke.addColorStop(1, 'rgba(254,71,2,0.2)');
						gradientStroke.addColorStop(0.8, 'rgba(254,71,2,0.1)');
						gradientStroke.addColorStop(0, 'rgba(254,71,2,0)');
						var grafico4 = new Chart(ctx, {    
						    type: 'line',
						    data: {
						        labels: ["1","2","3","4","5","6","7"],
						        datasets: [{
						            fill:true,
						            borderDashOffset: 0.0,
						            label: '# visitas',
						            data: [1200,1100,1300,1300,500,1200,1345],
						            backgroundColor: gradientStroke,
						            borderColor:'rgba(254,71,2,0.3)',
						            borderWidth: 1,
						            borderDash: [],
						            borderDashOffset: 0.0
						        }]
						    },
						    options: {
						        scales: {
						            yAxes: [{
						                ticks: {
						                    beginAtZero: true
						                },
						                gridLines: {
						                	drawBorder: false,
						                	color: 'transparent'
						                }
						            }],
						            xAxes: [{
							            gridLines: {
							            	drawBorder: false,
							                color: '#ebebeb',
							                zeroLineColor: "#ebebeb"
							            }	              
							        }]
						        }
						    }
						});

						var ctx = document.getElementById('grafico5').getContext('2d');
						var gradientStroke = ctx.createLinearGradient(0,230,0,50);
						gradientStroke.addColorStop(1, 'rgba(254,71,2,0.2)');
						gradientStroke.addColorStop(0.8, 'rgba(254,71,2,0.1)');
						gradientStroke.addColorStop(0, 'rgba(254,71,2,0)');
						var grafico5 = new Chart(ctx, {    
						    type: 'line',
						    data: {
						        labels: ["1","2","3","4","5","6","7"],
						        datasets: [{
						            fill:true,
						            borderDashOffset: 0.0,
						            label: '# visitas',
						            data: [1200,1100,1300,1300,500,1200,1345],
						            backgroundColor: gradientStroke,
						            borderColor:'rgba(254,71,2,0.3)',
						            borderWidth: 1,
						            borderDash: [],
						            borderDashOffset: 0.0
						        }]
						    },
						    options: {
						        scales: {
						            yAxes: [{
						                ticks: {
						                    beginAtZero: true
						                },
						                gridLines: {
						                	drawBorder: false,
						                	color: 'transparent'
						                }
						            }],
						            xAxes: [{
							            gridLines: {
							            	drawBorder: false,
							                color: '#ebebeb',
							                zeroLineColor: "#ebebeb"
							            }	              
							        }]
						        }
						    }
						});
					});
					</script>
					<div class="grafico">
						<canvas id="grafico1" class="box-grafico" width="300px" height="150px" style="display: none;"></canvas>
						<canvas id="grafico2" class="box-grafico" width="300px" height="150px" style="display: none;"></canvas>
						<canvas id="grafico3" class="box-grafico" width="300px" height="150px" style="display: none;"></canvas>
						<canvas id="grafico4" class="box-grafico" width="300px" height="150px" style="display: none;"></canvas>
						<canvas id="grafico5" class="box-grafico" width="300px" height="150px" style="display: none;"></canvas>
					</div>

				</div>
			</section>

			<section class="grid">
				<div class="box">
					<div class="filter">

						<div class="filter-group">
							<div class="filter-button">
								<a href="pg_contatos_pacientes_dadospessoais.php" class="verde"><i class="iconify" data-icon="bx-bx-plus"></i><span>Novo Paciente</span></a>
							</div>
						</div>

						
						<div class="filter-group filter-group_right">
							<form method="get" class="filter-form">
								<input type="hidden" name="csv" value="0" />
								<dl>
									<dd><input type="text" name="busca" value="<?php echo isset($values['busca'])?$values['busca']:"";?>" placeholder="" style="width:250px;" class="noupper" /></dd>
								</dl>
								<button type="submit"><i class="iconify" data-icon="bx-bx-search"></i></button>
							</form>
						</div>

					</div>
					<?php
					$where="WHERE lixo='0'";
					if(isset($values['busca']) and !empty($values['busca'])) $where.=" and (nome like '%".utf8_decode($values['busca'])."%' or cpf like '%".cpf($values['busca'])."%' or id = '".addslashes($values['busca'])."')";
					
					//echo $where;

					?>
					<div class="reg">
						<?php
						$sql->consultPagMto2($_table,"*",10,$where,"",15,"pagina",$_page."?".$url."&pagina=");
						if($sql->rows==0) {
							$msgSemResultado="Nenhum paciente";
							if(isset($values['busca'])) $msgSemResultado="Nenhum paciente encontrado";

							echo "<center>$msgSemResultado</center>";
						} else {
							while($x=mysqli_fetch_object($sql->mysqry)) {
						?>
						<a href="pg_contatos_pacientes_resumo.php?id_paciente=<?php echo $x->id?>" class="reg-group">
							<div class="reg-color" style="background-color:var(--cinza3)"></div>
							<div class="reg-data" style="flex:0 1 50%;">
								<h1><?php echo strtoupperWLIB(utf8_encode($x->nome));?></h1>
								<p>Código: <?php echo $x->id;?></p>
							</div>
							<div class="reg-data" style="flex:0 1 70px;">
								<p><?php echo $x->data_nascimento!="0000-00-00"?idade($x->data_nascimento)." anos":"";?></p>
							</div>
							<div class="reg-data" style="flex:0 1 100px;">
								<p><?php echo !empty($x->telefone1)?mask($x->telefone1):"";?></p>
							</div>
							
						</a>
						<?php
							}

							if(isset($sql->myspaginacao) and !empty($sql->myspaginacao)) {
							?>	
						<div class="paginacao" style="margin-top: 30px;">
							<p class="paginacao__item"><span>Página</span><?php echo $sql->myspaginacao;?></p>
						</div>
							<?php
							}
						}
						?>
					</div>
					
					<?php /*<div class="registros">
						<table class="tablesorter" style="overflow: none;">
							<thead>
								<tr>
									<th style="width:70px;">Código</th>
									<th>Nome</th>
									<th>Telefone</th>
								</tr>
							</thead>
							<tbody>
							<?php
							
							if($sql->rows==0) {
								$msgSemResultado="Nenhum paciente";
								if(isset($values['busca'])) $msgSemResultado="Nenhum paciente encontrado";
							?>
							<tr>	
								<td colspan="4"><center><?php echo $msgSemResultado;?></center></td>
							</tr>
							<?php
							} else {
								while($x=mysqli_fetch_object($sql->mysqry)) {
							?>
							<tr onclick="document.location.href='pg_contatos_pacientes_resumo.php?id_paciente=<?php echo $x->id?>'">
								<td><?php echo $x->id;?></td>
								<td><?php echo utf8_encode($x->nome);?></td>
								<td><?php echo mask($x->telefone1);?></td>
							</tr>
							<?php
								}
							}
							?>
							</tbody>
						</table>
						<?php
						if(isset($sql->myspaginacao) and !empty($sql->myspaginacao)) {
						?>	
							
						<div class="paginacao">
							<p class="paginacao__item"><span>Página</span><?php echo $sql->myspaginacao;?></p>
						</div>
						<?php
						}
						?>
					</div>*/?>
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