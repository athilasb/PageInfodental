<?php
	include "includes/header.php";
	include "includes/nav.php";

	$_table=$_p."colaboradores";

	$_width=400;
	$_height=400;
	$_dirFoto=$_cloudinaryPath."arqs/colaboradores/";

	$_cargos=array();
	$sql->consult($_p."colaboradores_cargos","*","where lixo=0 order by titulo asc");
	while($x=mysqli_fetch_object($sql->mysqry)) $_cargos[$x->id]=$x;
	
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
			<?php
			# Formulario de Adição/Edição
			if(isset($_GET['form'])) {

				$campos=explode(",","nome,sexo,rg,rg_orgaoemissor,rg_estado,cpf,data_nascimento,estado_civil,telefone1,telefone2,nome_pai,nome_mae,email,instagram,linkedin,facebook,cep,endereco,numero,complemento,bairro,estado,cidade,id_cidade,escolaridade,cro,uf_cro,tipo_cro,calendario_cor,calendario_iniciais,id_cargo,regime_contrato,salario,contratacao_obs,carga_horaria,comissionamento_tipo,permitir_acesso");

				foreach($campos as $v) $values[$v]='';
				$values['calendario_cor']="#c18c6a";
				$values['sexo']="M";
				$values['comissionamento_tipo']="nenhum";

				$cnt='';
				// busca edicao
				if(isset($_GET['edita']) and is_numeric($_GET['edita'])) {
					$sql->consult($_table,"*","where id='".$_GET['edita']."' and lixo=0");
					if($sql->rows) {
						$cnt=mysqli_fetch_object($sql->mysqry);
						$values=$adm->values($campos,$cnt);
					} else {
						$jsc->jAlert("Colaborador não encontrado!","erro","document.location.href='$_page?$url'");
						die();
					}
				}

				// persistencia
				if(isset($_POST['acao']) and $_POST['acao']=="wlib") {

					// monta sql de insert/update
					$vSQL=$adm->vSQL($campos,$_POST);

					// popula $values para persistir nos cmapos
					$values=$adm->values;


					$processa=true;

					// verifica se cpf ja esta cadastrado
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
							$jsc->jAlert("Informações salvas com sucesso!","sucesso","document.location.href='$_page?form=1&edita=".$id_reg."&".$url."'");
							die();
						}
					}
				}
			?>
			<section class="filter">
				
				<div class="filter-group">
					<div class="filter-title">	
						<h1></h1>
					</div>
				</div>

				<?php /*<div class="filter-group">
					<div class="filter-form form">
						<dl>
							<dd><a href="<?php echo $_page."?".$url;?>" class="button"><i class="iconify" data-icon="fluent:arrow-left-24-regular"></i></a></dd>
						</dl>
						<?php
						if(is_object($cnt)) {
						?>
						<dl>
							<dd><a href="<?php $_page."?form=1&edita=$cnt->id&deleta=1";?>" class="button"><i class="iconify" data-icon="fluent:delete-24-regular"></i></a></dd>
						</dl>
						<dl>
							<dd><a href="" class="button"><i class="iconify" data-icon="fluent:print-24-regular"></i></a></dd>
						</dl>
						<?php
						}
						?>
						<dl>
							<dd><a href="javascript://" class="button button_main js-submit"><i class="iconify" data-icon="fluent:checkmark-12-filled"></i><span>Salvar</span></a></dd>
						</dl>
					</div>
				</div>*/?>
			</section>
			<?php
			} 
			# Listagem
			else {
			?>
			<section class="filter">
				<div class="filter-group">
					<div class="filter-title">
						<h1>Configure a clínica</h1>
					</div>
				</div>
			</section>
			<?php
			} 
			?>

			<section class="grid">

				<div class="box box-col">

					<?php
					require_once("includes/submenus/subConfiguracoesClinica.php");
					?>
					<div class="box-col__inner1">
				<?php

				# Formulario de Adição/Edição
				if(isset($_GET['form'])) {
					if(is_object($cnt)) {
				?>	

						<section class="header-profile">
							<img src="img/ilustra-usuario.jpg" alt="" width="60" height="60" class="header-profile__foto" />
							<div class="header-profile__inner1">
								<h1><?php echo utf8_encode($cnt->nome);?></h1>
								<div>
									<p>
									<?php
									if($cnt->permitir_acesso==1) {
									?>
									<strong style="color:var(--verde);"><i class="iconify" data-icon="fluent:checkmark-circle-12-regular"></i> Acesso Ativo</strong>
									<?php
									} else {
									?>
									<strong style="color:var(--vermelho);"><i class="iconify" data-icon="fluent:checkmark-circle-12-regular"></i> Acesso Desativado</strong>
									<?php	
									}
									?>
									</p>
								</div>
							</div>
						</section>
				<?php
					}
				?>
						<script src="https://widget.cloudinary.com/v2.0/global/all.js" type="text/javascript"></script> 
						<script type="text/javascript">
							
							var logo = cloudinary.createUploadWidget({
								cloudName: '<?php echo $_cloudinaryCloudName;?>',
								language: 'pt',
								text: <?php echo json_encode($_cloudinaryText);?>,
								multiple: false,
								sources: ["local"],
								folder: '<?php echo $_dirFoto;?>',
								uploadPreset: '<?php echo $_cloudinaryUploadPresent;?>'}, 
								(error, result) => {
									if (!error && result) {
										if(result.event === "success") {
											// $('.js-cn').val(result.info.path);
										}
									}
								}
							);
							$(function(){

								$('input.money').maskMoney({symbol:'', allowZero:false, showSymbol:true, thousands:'.', decimal:',', symbolStay: true});
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
						<form method="post" class="form formulario-validacao">
							<input type="hidden" name="acao" value="wlib" />
							<section class="filter">
								<div class="filter-group">

									<script>
										$(function() {
											$('.tab a').click(function() {
												let tabName = $(this).attr('data-tab');
												$(".tab a").removeClass("active");
												$(this).addClass("active");
												$(".js-tabs").hide();
												$(".js-" + tabName).show();
											});
										});
									</script>

									<section class="tab">
										<a href="javascript:;" data-tab="dadospessoais" class="active">Dados Pessoais</a>
										<a href="javascript:;" data-tab="dadosdacontratacao">Dados da Contratação</a>					
										<a href="javascript:;" data-tab="habilitaragendamento">Habilitar Agendamento</a>					
										<a href="javascript:;" data-tab="acessoaosistema">Acesso ao Sistema</a>
									</section>
								</div>
								<div class="filter-group">
									<div class="filter-form form">
										<dl>
											<dd><a href="<?php echo $_page."?".$url;?>" class="button"><i class="iconify" data-icon="fluent:arrow-left-24-regular"></i></a></dd>
										</dl>
										<?php
										if(is_object($cnt)) {
										?>
										<dl>
											<dd><a href="" class="button"><i class="iconify" data-icon="fluent:delete-24-regular"></i></a></dd>
										</dl>
										<dl>
											<dd><a href="" class="button"><i class="iconify" data-icon="fluent:print-24-regular"></i></a></dd>
										</dl>
										<?php
										}
										?>
										<dl>
											<dd><a href="javascript:;" class="button button_main js-submit"><i class="iconify" data-icon="fluent:checkmark-12-filled"></i><span>Salvar</span></a></dd>
										</dl>
									</div>
								</div>
							</section>

							<div class="js-tabs js-dadospessoais">

								<fieldset>
									<legend>Dados Pessoais</legend>

									<div class="colunas3">
										<dl>
											<dt>Nome</dt>
											<dd><input type="text" name="nome" value="<?php echo $values['nome'];?>" class="obg" /></dd>
										</dl>
										<dl>
											<dt>Sexo</dt>
											<dd>
												<label><input type="radio" name="sexo" value="M"<?php echo $values['sexo']=="M"?" checked":"";?>>masculino</label>
												<label><input type="radio" name="sexo" value="F"<?php echo $values['sexo']=="F"?" checked":"";?>>feminino</label>
											</dd>
										</dl>
										<dl>
											<dt>Foto</dt>
											<dd><input type="file" /></dd>
										</dl>
									</div>
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
												<select name="rg_orgaoemissor">
													<option value="">-</option>
													<?php
													foreach($_optUF as $uf=>$titulo) {
														echo '<option value="'.$uf.'"'.($values['rg_orgaoemissor']==$uf?' selected':'').'>'.$titulo.'</option>';
													}
													?>
												</select>
											</dd>
										</dl>
									</div>
									<div class="colunas3">
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
										<dl>
											<dt>CRO</dt>
											<dd>
												<input type="text" name="cro" value="<?php echo $values['cro']; ?>" class="" />
											</dd>
										</dl>
										<dl>
											<dt>UF do CRO</dt>
											<dd>
												<select name="uf_cro" class="chosen">
													<option value="">-</option>
													<?php
													foreach($_optUF as $uf=>$titulo) {
														echo '<option value="'.$uf.'"'.($values['uf_cro']==$uf?' selected':'').'>'.$titulo.'</option>';
													}
													?>
												</select></dd>
										</dl>
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
									</div>
									<div class="colunas3">
										<dl>
											<dt>Nome da Mãe</dt>
											<dd>
												<input type="text" name="nome_pai" value="<?php echo $values['nome_pai'];?>" class="" />
											</dd>
										</dl>
										<dl>
											<dt>Nome do Pai</dt>
											<dd>
												<input type="text" name="nome_mae" value="<?php echo $values['nome_mae'];?>" class="" />
											</dd>
										</dl>
									</div>
								</fieldset>

								<fieldset>
									<legend>Dados de Contato</legend>

									<div class="colunas3">
										<dl>
											<dt>WhatsApp</dt>
											<dd class="form-comp">
												<span class="js-country">BR</span><input type="text" name="telefone1" class="obg " attern="\d*" x-autocompletetype="tel" value="<?php echo $values['telefone1'];?>" />
											</dd>
										</dl>
										<dl>
											<dt>Telefone</dt>
											<dd class="form-comp">
												<span class="js-country">BR</span><input type="text" name="telefone2" class="obg " attern="\d*" x-autocompletetype="tel" value="<?php echo $values['telefone2'];?>" />
											</dd>
										</dl>
										<dl>
											<dt>Instagram</dt>
											<dd class="form-comp"><span>@</span><input type="text" name="instagram" value="<?php echo $values['instagram'];?>" /></dd>
										</dl>
									</div>
									<div class="colunas3">
										<dl class="dl2">
											<dt>Endereço</dt>
											<dd><input type="text" name="endereco" value="<?php echo $values['endereco'];?>" /></dd>
										</dl>
										<dl>
											<dt>Complemento</dt>
											<dd><input type="text" name="complemento" value="<?php echo $values['complemento'];?>" /></dd>
										</dl>
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
							</div><!-- .js-dadospessoais -->

							<div class="js-tabs js-dadosdacontratacao" style="display:none">
								<fieldset>
									<legend>Contratação</legend>
									<div class="colunas3">
										<dl>
											<dt>Cargo Atual</dt>
											<dd>
												<select name="id_cargo" class="obg">
													<option value="">-</option>
													<?php
													foreach($_cargos as $v) {
														echo '<option value="'.$v->id.'"'.(($values['id_cargo']==$v->id)?' selected':'').'>'.utf8_encode($v->titulo).'</option>';
													}
													?>
												</select>
											</dd>
										</dl>
										<dl>
											<dt>Regime de Contratação</dt>
											<dd>
												<select name="regime_contrato" class="obg">
													<option value="">-</option>
													<?php
													foreach($_regimes as $k => $v) {
														echo '<option value="'.$k.'"'.(($values['regime_contrato']==$k)?' selected':'').'>'.$v.'</option>';
													}
													?>
												</select>
											</dd>
										</dl>
										<dl>
											<dt>Salário</dt>
											<dd><input type="text" name="salario" value="<?php echo $values['salario'];?>" class="money" /></dd>
										</dl>
									</div>
									<div class="colunas3">
										<dl>
											<dt>Carga Horária</dt>
											<dd>
												<select name="carga_horaria" class="obg">
													<option value="">-</option>
													<?php
													foreach($_cargaHoraria as $k => $v) {
														echo '<option value="'.$k.'"'.(($values['carga_horaria']==$k)?' selected':'').'>'.$v.'</option>';
													}
													?>
												</select>
											</dd>
										</dl>
										<dl class="dl2">
											<dt>Observação Geral </dt>
											<dd><input type="text" name="contratacao_obs" value="<?php echo $values['contratacao_obs'];?>" /></dd>
										</dl>
									</div>
								</fieldset>

								<fieldset>
									<legend><span>Comissionamento</span></legend>
									<script type="text/javascript">
										const comissionamentoTipo = () => {
											//alert($('input[name=comissionamento_tipo]:checked').val());
											if($('input[name=comissionamento_tipo]:checked').val()=="nenhum") {
												$('.js-comissao').hide();
											} else if($('input[name=comissionamento_tipo]:checked').val()=="percentual") {
												$('.js-comissao').hide();
												$('.js-percentual').show();
											} else if($('input[name=comissionamento_tipo]:checked').val()=="valor") {
												$('.js-comissao').hide(); 
												$('.js-valorfixo').show();
											}
										}
										$(function(){
											comissionamentoTipo();
											$('input[name=comissionamento_tipo]').click(comissionamentoTipo);
										})
									</script>
									<?php
									if(empty($values['comissionamento_tipo'])) $values['comissionamento_tipo']="nenhum";
									?>
									<dl>
										<dd>
											<label><input type="radio" name="comissionamento_tipo" value="nenhum"<?php echo $values['comissionamento_tipo']=="nenhum"?" checked":"";?> />Nenhum</label>
											<label><input type="radio" name="comissionamento_tipo" value="percentual" data-tab="percentual"<?php echo $values['comissionamento_tipo']=="percentual"?" checked":"";?> />Percentual <div class="badge-help" title="Comissionamento por porcentagem está vinculado a efetivação do pagamento, por parte do paciente, independente da execução do procedimento."><i class="iconify" data-icon="fluent:chat-help-20-filled"></i></div></label>
											<label><input type="radio" name="comissionamento_tipo" value="valor" data-tab="valorfixo"<?php echo $values['comissionamento_tipo']=="valor"?" checked":"";?> />Valor Fixo <div class="badge-help" title="Comissionamento por valor fixo está vinculado a execução do procedimento, independente do pagamento do paciente."><i class="iconify" data-icon="fluent:chat-help-20-filled"></i></div></label>											
										</dd>										
									</dl>

									<div class="js-comissao js-percentual" style="display:none;">
										<div class="colunas3">
											<dl>
												<dt>Plano</dt>
												<dd><select name=""></select></dd>
											</dl>
											<dl>
												<dt>Percentual</dt>
												<dd class="form-comp form-comp_pos"><input type="text" name="" /><span>%</span></dd>
											</dl>
											<dl>
												<dt>Observação</dt>
												<dd><input type="text" name="" /><a href="" class="button button_main"><i class="iconify" data-icon="fluent:add-circle-24-regular"></i></a></dd>
											</dl>
										</div>
										<div class="list2">
											<table>
												<thead>
													<tr>
														<th>Plano</th>
														<th>Percentual</th>
														<th>Observações</th>
														<th></th>
													</tr>
												</thead>
												<tbody>
													<tr>
														<td><strong>UNIMED</strong></td>
														<td>12.5%</td>
														<td>Negociação especial</td>													
														<td style="text-align:right;">
															<a href="" class="button"><i class="iconify" data-icon="fluent:edit-24-regular"></i></a>
															<a href="" class="button"><i class="iconify" data-icon="fluent:delete-24-regular"></i></a>
														</td>
													</tr>
													<tr>
														<td><strong>ODONTOPREV</strong></td>
														<td>12.5%</td>
														<td>Negociação especial</td>													
														<td style="text-align:right;">
															<a href="" class="button"><i class="iconify" data-icon="fluent:edit-24-regular"></i></a>
															<a href="" class="button"><i class="iconify" data-icon="fluent:delete-24-regular"></i></a>
														</td>
													</tr>
												</tbody>
											</table>
										</div>
									</div><!-- .js-percentual -->

									<div class="js-comissao js-valorfixo" style="display:none;">
										<div class="colunas5">
											<dl>
												<dt>Plano</dt>
												<dd><select name=""></select></dd>
											</dl>
											<dl>
												<dt>Procedimento</dt>
												<dd><select name=""></select></dd>
											</dl>
											<dl>
												<dt>Valor Tabela</dt>
												<dd class="form-comp"><span>R$</span><input type="text" name="" /></dd>
											</dl>
											<dl>
												<dt>Valor Comissão</dt>
												<dd class="form-comp"><span>R$</span><input type="text" name="" /></dd>
											</dl>
											<dl>
												<dt>Observações</dt>
												<dd>
													<input type="text" name="" />
													<a href="" class="button button_main"><i class="iconify" data-icon="fluent:add-circle-24-regular"></i></a>
												</dd>
											</dl>
										</div>
										<div class="list2">
											<table>
												<thead>
													<tr>
														<th>Plano</th>
														<th>Procedimento</th>
														<th>Valor Tabela</th>
														<th>Valor Comissão</th>
														<th>Observação</th>
														<th></th>
													</tr>
												</thead>
												<tbody>
													<tr>
														<td><strong>UNIMED</strong></td>
														<td>Prótese Dental</td>
														<td>R$ 200,00</td>													
														<td>R$ 25,00</td>
														<td>Negociação Especial</td>
														<td style="text-align:right;">
															<a href="" class="button"><i class="iconify" data-icon="fluent:edit-24-regular"></i></a>
															<a href="" class="button"><i class="iconify" data-icon="fluent:delete-24-regular"></i></a>
														</td>
													</tr>
													<tr>
														<td><strong>UNIMED</strong></td>
														<td>Prótese Dental</td>
														<td>R$ 200,00</td>													
														<td>R$ 25,00</td>
														<td>Negociação Especial</td>
														<td style="text-align:right;">
															<a href="" class="button"><i class="iconify" data-icon="fluent:edit-24-regular"></i></a>
															<a href="" class="button"><i class="iconify" data-icon="fluent:delete-24-regular"></i></a>
														</td>
													</tr>													
												</tbody>
											</table>
										</div>
									</div><!-- .js-valorfixo -->

								</fieldset>
							</div><!-- .js-dadosdacontratacao -->

							<div class="js-tabs js-habilitaragendamento" style="display:none;">
								<fieldset>
									<legend>Agendamento</legend>
									<div class="colunas4">
										<dl class="dl2">
											<dt></dt>
											<dd><label><input type="checkbox" class="input-switch" /> Habilitar agendamento</label></dd>
										</dl>
										<dl>
											<dt>Inicial</dt>
											<dd><input type="text" name="" /></dd>
										</dl>
										<dl>
											<dt>Cor</dt>
											<dd><input type="text" name="" /></dd>
										</dl>
									</div>
								</fieldset>

								<fieldset>
									<legend>Horário de Atendimento</legend>
									<div class="colunas4">
										<dl>
											<dt>Dia da Semana</dt>
											<dd><input type="text" name="" /></dd>
										</dl>
										<dl>
											<dt>Início</dt>
											<dd class="form-comp"><span><i class="iconify" data-icon="fluent:clock-24-regular"></i></span><input type="text" name="" class="hora" /></dd>
										</dl>
										<dl>
											<dt>Fim</dt>
											<dd class="form-comp"><span><i class="iconify" data-icon="fluent:clock-24-regular"></i></span><input type="text" name="" class="hora" /></dd>
										</dl>
										<dl>
											<dt>Cadeira</dt>
											<dd>
												<input type="text" name="" />
												<a href="" class="button button_main"><i class="iconify" data-icon="fluent:add-circle-24-regular"></i></a>
											</dd>
										</dl>
									</div>
									<div class="list2">
										<table>
											<thead>
												<tr>
													<th style="width:12.5%">CADEIRA</th>
													<th style="width:12.5%">DOM</th>
													<th style="width:12.5%">SEG</th>
													<th style="width:12.5%">TER</th>
													<th style="width:12.5%">QUA</th>
													<th style="width:12.5%">QUI</th>
													<th style="width:12.5%">SEX</th>
													<th style="width:12.5%">SÁB</th>
												</tr>
											</thead>
											<tbody>
												<tr>
													<td><strong>#1</strong></td>
													<td></td>
													<td><a href="" class="button button_sm">08:00~12:00</a><br /><a href="" class="button button_sm">14:00~18:00</a></td>
													<td><a href="" class="button button_sm">08:00~12:00</a><br /><a href="" class="button button_sm">14:00~18:00</a></td>
													<td><a href="" class="button button_sm">08:00~12:00</a><br /><a href="" class="button button_sm">14:00~18:00</a></td>
													<td><a href="" class="button button_sm">08:00~12:00</a><br /><a href="" class="button button_sm">14:00~18:00</a></td>
													<td><a href="" class="button button_sm">08:00~12:00</a><br /><a href="" class="button button_sm">14:00~18:00</a></td>
													<td><a href="" class="button button_sm">09:00~13:00</a></td>
												</tr>
											</tbody>
										</table>
									</div>
								</fieldset>
							</div>

							<div class="js-tabs js-acessoaosistema" style="display:none;">
								<div class="colunas3">
									<dl>
										<dt>Email de acesso</dt>
										<dd><input type="text" name="" /></dd>
									</dl>
									<dl class="dl2">
										<dt></dt>
										<dd>
											<label><input type="checkbox" name="permitir_acesso" value="1" class="input-switch"<?php echo $values['permitir_acesso']==1?" checked":"";?> /> Acesso ao sistema</label>
											<label><input type="checkbox" name="" class="input-switch"> Ativo</label>
										</dd>
									</dl>									
								</div>
							</div>
						</form>

				<?php
				}

				# Listagem
				else {
					$values=$adm->get($_GET);
				?>
						<section class="filter">
							<div class="filter-group">
								<div class="filter-form form">
									<dl>
										<dd><a href="<?php echo $_page."?form=1";?>" class="button button_main"><i class="iconify" data-icon="fluent:add-circle-24-regular"></i> <span>Novo Colaborador</span></a></dd>
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
					
						
						<div class="list1">
							<?php
							$where="where lixo=0";
							if(isset($values['busca']) and !empty($values['busca'])) {
								$where.=" and nome like '%".$values['busca']."%'";
							}
							//$sql->consult($_table,"*",$where." order by nome asc");
							$sql->consultPagMto2($_table,"*",10,$where." order by nome asc","",15,"pagina",$_page."?".$url."&pagina=");
							if($sql->rows==0) {
								if(isset($values['busca'])) $msg="Nenhum Resultado encontrado";
								else $msg="Nenhum colaborador cadastrado";

								echo "<center>$msg</center>";
							} else {
							?>
							<table>
								<?php
								while($x=mysqli_fetch_object($sql->mysqry)) {
								?>
								<tr onclick="document.location.href='<?php echo $_page."?form=1&edita=$x->id&$url";?>';">
									<td><h1><strong><?php echo utf8_encode($x->nome);?></strong></h1></td>
									<td><?php echo $x->data_nascimento!="0000-00-00"?idade($x->data_nascimento)." anos":"";?></td>
									<td>
										<?php
										if($x->permitir_acesso==1) {
										?>
										<strong style="color:var(--verde);"><i class="iconify" data-icon="fluent:checkmark-circle-12-regular"></i> Acesso Ativo</strong>
										<?php
										} else {
										?>
										<strong style="color:var(--vermelho);"><i class="iconify" data-icon="fluent:checkmark-circle-12-regular"></i> Acesso Desativado</strong>
										<?php	
										}
										?>
									</td>
									<td>
										<?php
										if(!empty($x->calendario_iniciais)) {
										?>
										<div class="badge-prof" title="Kroner Costa" style="<?php echo empty($x->calendario_cor)?"":"background:$x->calendario_cor";?>"><?php echo $x->calendario_iniciais;?></div>
										<?php
										}
										?>
									</td>
								</tr>
								<?php
								}
								?>								
							</table>
							<?php
							}
							?>		
						</div>
						<?php
						if(isset($sql->myspaginacao) and !empty($sql->myspaginacao)) {
						?>
						<div class="pagination">						
							<?php echo $sql->myspaginacao;?>
						</div>
						<?php
						}
						?>
				<?php
				}
				?>
				
					</div>
					
					
				</div>

			</section>
		
		</div>
	</main>

<?php 
include "includes/footer.php";
?>	