<?php
	include "includes/header.php";
	include "includes/nav.php";

	$_table = "infodentalADM.infod_contas";

	$iugu = new Iugu();

	$_dirLogo=$_cloudinaryPath."arqs/clinica/logo/";

	$campos = explode(",","tipo,razao_social,cnpj,responsavel,cpf,cep,logradouro,numero,complemento,bairro,cidade,estado,email");

	$values=array();
	foreach($campos as $v) $values[$v]='';
	$values['tipo']='PJ';

	$cnt='';
	$sql->consult($_table,"*","where instancia='".addslashes($_ENV['NAME'])."'");
	if($sql->rows) {
		$cnt=mysqli_fetch_object($sql->mysqry);
		$values=$adm->values($campos,$cnt);
	}

	$_iuguPlanos=array();
	if($iugu->planosListar()) {
		$_iuguPlanos=$iugu->response;
	}

	// verifica se possui assinatura
	$subscription='';
	if(!empty($cnt->iugu_subscription_id)) {
		if($iugu->assinaturaConsultar($cnt->iugu_subscription_id)) {
			if(isset($iugu->response->id)) {
				$subscription=$iugu->response;
			}
		}
	}

	if(isset($_GET['cancelar'])) {
		if(is_object($subscription)) {

			if($iugu->assinaturaSuspender($cnt->iugu_subscription_id)) {
				$sql->update("infodentalADM.infod_contas","iugu_subscription_id=''","where instancia='$cnt->instancia'");
				$jsc->jAlert("Assinatura foi suspensa com sucesso!","sucesso","document.location.href='$_page'");
				die();
			} else {
				$jsc->jAlert("Algum erro ocorreu durante a suspensão desta assinatura!","erro","");
			}

		} else {
			$jsc->jAlert("Sua conta não possui nenhuma assinatura!","erro","");
		}
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
		$vWHERE="where instancia='".$cnt->instancia."'";
		$sql->update($_table,$vSQL,$vWHERE);
		$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='update',vsql='".addslashes($vSQL)."',vwhere='".addslashes($vWHERE)."',tabela='".$_table."'");

		# Persistencia do Cliente
			
			$sql->consult($_table,"*","where instancia='".addslashes($_ENV['NAME'])."'");
			if($sql->rows) {
				$cnt=mysqli_fetch_object($sql->mysqry);

				$attr=array('email'=>$cnt->email,
							'name'=>$cnt->tipo=="PF"?utf8_encode($cnt->responsavel):utf8_encode($cnt->razao_social),
							'cpf_cnpj'=>$cnt->tipo=="PF"?utf8_encode($cnt->cpf):utf8_encode($cnt->cnpj),
							'zip_code'=>$cnt->cep,
							'number'=>$cnt->numero,
							'street'=>utf8_encode($cnt->logradouro),
							'state'=>utf8_encode($cnt->estado),
							'city'=>utf8_encode($cnt->cidade),
							'district'=>utf8_encode($cnt->bairro),
							'complement'=>utf8_encode($cnt->complemento)
							);

				// se não possui cadastro na iugu
				if(empty($cnt->iugu_customer_id)) {
					if($iugu->clientesCriar($attr)) {
						$customer_id=$iugu->response->id;
						$sql->update($_table,"iugu_customer_id='".addslashes($customer_id)."'","where instancia='$cnt->instancia'");
					} else {
						$jsc->jAlert($iugu->erro,"erro","");

					}
					
				} 
				// se possuir altera dados
				else {
					if($iugu->clientesAlterar($cnt->iugu_customer_id,$attr)) {
						$customer_id=$cnt->iugu_customer_id;
					} else {
						$jsc->jAlert($iugu->erro,"erro","");

					}
				}
			}

		# Assinatura do Plano
			// se ja possui assinatura
			if($cnt->iugu_subscription_id) {

			}
			// se nao possui assinatura
			else {

				if(isset($_POST['iugu_plano']) and !empty($_POST['iugu_plano'])) {
					$plan_identifier=addslashes($_POST['iugu_plano']);


					// verifica se cliente possui assinatura na iugu antes de criar uma nova
					$subscriptionIugu='';
					if($iugu->assinaturaListar($cnt->iugu_customer_id)) {
						if(isset($iugu->response->items)) {
							foreach($iugu->response->items as $it) {
								if($it->suspended===false) {
									$subscriptionIugu=$it;
								}
							}
						}
					}

					if(is_object($subscriptionIugu)) {
						$sql->update("infodentalADM.infod_contas","iugu_subscription_id='$subscriptionIugu->id'","where instancia='$cnt->instancia'");
						$jsc->jAlert("Sua conta já possui assinatura ativa!","erro","document.location.href='$_page'");
						die();
					} else {

						$attr = array('plan_identifier'=>$plan_identifier,
										'customer_id'=>$customer_id);
						if($iugu->assinaturaCriar($attr)) {

							$subscription_id=$iugu->response->id;

							$sql->update("infodentalADM.infod_contas","iugu_subscription_id='$subscription_id'","where instancia='$cnt->instancia'");
						
							$jsc->jAlert("Assinatura realizada com sucesso!","sucesso","document.location.href='$_page'");
							die();
						} else {
							$jsc->jAlert($iugu->erro,"erro","");
						}
					}

				}

			}
	
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
			</div>
		</div>
	</header>

	<main class="main">
		<div class="main__content content">

			<section class="filter">
				
				<div class="filter-group">
					<div class="filter-title">
						<h1>Pagamentos</h1>
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
										<dd><a href="javascript:;" class="button button_main js-submit2" data-loading="0"><i class="iconify" data-icon="fluent:checkmark-12-filled"></i> <span>Salvar</span></a></dd>
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

								$('.js-submit2').click(function(){

									let obj = $(this);

									if(obj.attr('data-loading')==0) {
										obj.attr('data-loading',1).html(`<i class="iconify" data-icon="eos-icons:loading"></i> <span>Salvando...</span>`);
										$('form.formulario-validacao').submit();
									} 

								});
							})
						</script>
						<?php
						//var_dump($_iuguPlanos);
						?>
						<form method="post" class="form formulario-validacao" action="<?php echo $_page;?>">
							<input type="hidden" name="acao" value="wlib" />
							<fieldset>
								<legend>Assinatura</legend>

								<?php
								if(is_object($subscription)) {

								?>
								<div class="colunas3">
									<dl>
										<dt>Plano</dt>
										<dd>
											<?php
											echo $subscription->plan_name;
											?>
										</dd>
									</dl>

									<dl>
										<dt>Valor</dt>
										<dd>
											<?php
											echo number_format($subscription->price_cents/100,2,",",".");
											?>
										</dd>
									</dl>
									<dl>
										<dt>Status</dt>
										<dd>
											<?php
											echo $subscription->suspended==false?"<font color=green>ATIVO</font>":"<font color=red>SUSPENSO</font>";
											?>
										</dd>
									</dl>
								</div>
								<br />
								<center>
										<a href="<?php echo $_page."?cancelar=1";?>" class="button js-confirmarDeletar" data-msg="Tem certeza que deseja cancelar a assinatura?" style="color:red;border-color:red"><span class="iconify" data-icon="ep:close-bold"></span> 
									Cancelar Assinatura</a>
								</center>
								<?php
								} else {
								?>
								<dl>
									<dd>
										<select name="iugu_plano" class="obg">
											<option value="">- Selecione um Plano -</option>
											<?php
											foreach($_iuguPlanos->items as $v) {
												echo '<option value="'.$v->identifier.'"'.($v->id==$values['iugu_customer_id']?' selected':'').'>'.$v->name.' ('.number_format($v->prices[0]->value_cents/100,2,",",".").')</option>';
											}
											?>
										</select>
									</dd>
								</dl>
								<?php
								}
								?>
							</fieldset>
							<fieldset>
								<legend>Dados de Pagamento</legend>

									<div>
										<dl>
											<dd>
												<label><input type="radio" name="tipo" value="PF"<?php echo $values['tipo']=="PF"?" checked":"";?> />Pessoa Física</label>
												<label><input type="radio" name="tipo" value="PJ"<?php echo $values['tipo']=="PJ"?" checked":"";?> />Pessoa Jurídica</label>
											</dd>
										</dl>

										<div class="colunas4 js-cpf">
											<dl class="dl2">
												<dt>Responsável</dt>
												<dd><input type="text" name="responsavel" value="<?php echo $values['responsavel'];?>" class="obg" /></dd>
											</dl>
											<dl>
												<dt>CPF</dt>
												<dd><input type="text" name="cpf" value="<?php echo $values['cpf'];?>" class="obg cpf" /></dd>
											</dl>
										</div>

										<div class="colunas4 js-cnpj">
											<dl class="dl2">
												<dt>Razão Social</dt>
												<dd><input type="text" name="razao_social" value="<?php echo $values['razao_social'];?>" class="obg" /></dd>
											</dl>
											<dl>
												<dt>CNPJ</dt>
												<dd><input type="text" name="cnpj" value="<?php echo $values['cnpj'];?>" class="obg cnpj" /></dd>
											</dl>
										</div>
										<div class="colunas4">
											<dl class="dl2">
												<dt>E-mail</dt>
												<dd><input type="email" name="email" value="<?php echo $values['email'];?>" class="obg" /></dd>
											</dl>
										</div>
										<div class="colunas4">
											<dl>
												<dt>CEP</dt>
												<dd><input type="text" name="cep" value="<?php echo $values['cep'];?>" class="" /></dd>
											</dl>
											<dl>
												<dt>Logradouro</dt>
												<dd>
													<input type="text" name="logradouro" class="obg" value="<?php echo $values['logradouro'];?>" />
												</dd>
											</dl>
											<dl>
												<dt>Número</dt>
												<dd><input type="text" name="numero" value="<?php echo $values['numero'];?>" class="obg" /></dd>
											</dl>
											<dl>
												<dt>Complemento</dt>
												<dd><input type="text" name="complemento" value="<?php echo $values['complemento'];?>" class="" /></dd>
											</dl>
										</div>

										<div class="colunas4">
											
											<dl>
												<dt>Bairro</dt>
												<dd>
													<input type="text" name="bairro" class="obg" value="<?php echo $values['bairro'];?>" />
												</dd>
											</dl>
											<dl>
												<dt>Estado</dt>
												<dd>
													<input type="text" name="estado" class="obg" value="<?php echo $values['estado'];?>" />
												</dd>
											</dl>
											<dl>
												<dt>Cidade</dt>
												<dd>
													<input type="text" name="cidade" class="obg" value="<?php echo $values['cidade'];?>" />
												</dd>
											</dl>
										</div>
										
									</div>
							</fieldset>


							<fieldset>
								<legend>Faturas Recentes</legend>
								<?php
								if(empty($subscription)) {
								?>
								<center>
									Nenhuma assinatura ativa!
								</center>
								<?php
								} else {
									


								?>
								<div class="list1">
									<table>
										<?php
										foreach($subscription->recent_invoices as $f) {
											$cor="var(--cinza3)";
											$status=$f->status;
											if($f->status=="paid") {
												$status="Pago";
												$cor="var(--verde)";
											} else if($f->status=="pending") {
												$status="Aguardando Pagamento";
												$cor="#ffcc00";
											} else if($f->status="expired") {
												$status="Expirada";
												$cor="var(--vermelho)";
											}
											
										?>
										<tr class="js-item" onclick="window.open('<?php echo $f->secure_url;?>')">
											<td class="list1__border" style="color:<?php echo $cor;?>"></td>
											<td><h1><?php echo $status;?></h1></td>
											<td><?php echo date('d/m/Y',strtotime($f->due_date));?></td>
											<td><?php echo $f->total;?></td>
										</tr>
										<?php
										}
										?>
										
									</table>
								</div>
								<?php
									
								}
								?>

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