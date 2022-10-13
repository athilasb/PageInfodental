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
								<legend>Dados Cadastrais</legend>

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
								
								<legend>Cartão de Crédito</legend>

								<a href="javascript:;" class="button button_main js-btn-novoCartao" data-loading="0" style="float:right"><i class="iconify" data-icon="fluent:checkmark-12-filled"></i> <span>Novo Cartão</span></a>

								<div class="list1">
									<table>
										<tr class="js-item">
											<td class="list1__border" style="color:green"></td>
											<td>MASTERCARD **** 1440</td>
										</tr>
										<tr class="js-item">
											<td class="list1__border" style="color:"></td>
											<td>VISA **** 1145</td>
										</tr>
									</table>
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

	<script type="text/javascript" src="https://js.iugu.com/v2"></script>
	<script type="text/javascript">
		Iugu.setAccountID("DDFADAF26C374DBEA50C6359289855A1");
		
		Iugu.setup();
		var bandeira = '';

		$(function(){ 

			$('.js-form-adicionar-cartao input[name=validade]').inputmask('99/99');

			$('.js-btn-novoCartao').click(function(){
				$(".aside-cartao").fadeIn(100,function() {
					$(".aside-cartao .aside__inner1").addClass("active");
				});
			});

			$('.js-navHaderback').attr('href','pagamentos');
			$('.js-navHaderback').html(`<span class="iconify" data-icon="eva:arrow-ios-back-fill" data-inline="false" data-height="30"></span>`)



			$('.js-salvarCartao').click(function(){

				let obj = $(this);
				let objAntigo = $(this).html();

				if(obj.attr('data-loading')==0) {

					let erro = ``;
					$('form.js-form-adicionar-cartao input[type=tel],form.js-form-adicionar-cartao input[type=text]').each(function(index,elem){
						if(erro.length==0 && $(elem).hasClass('obg')===true && $(elem).val().length==0) {
							erro = $(elem).attr('data-msg');
							$(elem).addClass('erro');
						}
					})

					if(erro.length>0) {
						swal({title: "Erro", text: erro, html:true, type:"error", confirmButtonColor: "#424242"});
					} else {



						let number = numero($('input[name=number]').val());
						let expiration = $('input[name=expiration]').val().split('/');
						let expiration_mes = expiration[0] ? expiration[0] : '';
						let expiration_ano = expiration[1] ? expiration[1] : '';
						let verification_value = $('input[name=verification_value]').val();
						let full_name = $('input[name=full_name]').val().split(' ');
						let first_name = full_name[0];
						let apelido = $('input[name=apelido]').val();
						let email = $('input[name=email]').val();

						let last_name = '';
						for(var i = 0;i<full_name.length; i++) {
							if(i>0) last_name+=full_name[i]+' ';
						}

						if(Iugu.utils.validateCreditCardNumber(number)===false) {
							erro='Número de cartão inválido!';
						} else if(Iugu.utils.validateExpiration(expiration_mes, expiration_ano)===false) {
							erro='Data de validade inválida!';
						} else if(Iugu.utils.validateCVV(verification_value, bandeira)===false) {
							erro='CVV inválido!';
						} 

						if(erro.length>0) {
							swal({title: "Erro", text: erro, html:true, type:"error", confirmButtonColor: "#424242"});
						} else {

							alert('chegou aqui');return;
							cc = Iugu.CreditCard(number,expiration_mes,expiration_ano,first_name,last_name,verification_value);

							if(cc.valid()===false) {
								alert('Dados do cartão de crédito inválido!');
							} else {

								obj.html(`<span class="iconify" data-icon="eos-icons:three-dots-loading"></span> Cadastrando...`);
								obj.attr('data-loading',1);

								Iugu.createPaymentToken(cc, function(response) {
								    if (response.errors) {

								    	console.log(response.errors);
								        alert("Algum erro ocorreu durante o registro de seu cartão. Tente novamente!");

										obj.html(objAntigo);
										obj.attr('data-loading',0);
								    } else {

								    	cc_token = response.id;

								    	let data = `act=pagamentosAdicionarCartao&id_cliente=${id_cliente}&email=${email}&cc_token=${cc_token}&token=${vfapiToken}&apelido=${apelido}`;
								    

										obj.html(`<span class="iconify" data-icon="eos-icons:three-dots-loading"></span> Criando forma de pagamento...`);
								        $.ajax({
								        	type:"POST",
								        	data:data,
											url:vfapiURL,
								        	success:function(rtn){
								        		if(rtn.success===true) {
													/*swal({title: "Sucesso", text: "Cartão cadastrado com sucesso!<br /><br />Agora valide o cartão para poder utilizá-lo!", html:true, type:"success", confirmButtonColor: "#424242"},function(){
														document.location.href='pagamentos/';
													});*/
													document.location.href='pagamentos/';
								        		} else if(rtn.error) {
													swal({title: "Erro", text: rtn.error, html:true, type:"error", confirmButtonColor: "#424242"});
								        		} else {
													swal({title: "Erro", text: 'Algum erro ocorreu. Tente novamente!', html:true, type:"error", confirmButtonColor: "#424242"});
								        		}
								        	},
								        	error:function(){
								        		swal({title: "Erro", text: 'Algum erro ocorreu. Tente novamente.', html:true, type:"error", confirmButtonColor: "#424242"});
								        	}
								        }).done(function(){
								        	obj.html(objAntigo);
											obj.attr('data-loading',0);
								        });

								    }   
								});
							}
						}
					}
				}
			});

			$('form.js-cadastro input[type=tel],form.js-cadastro input[type=text]').keyup(function(){
				$(this).removeClass('erro')
			})

			$('input[name=number]').keyup(function(){
				let number = numero($(this).val());
				if(number.length>=4) {
					bandeira = Iugu.utils.getBrandByCreditCardNumber(number);
					/*if(bandeira=="visa") {
						$('.js-dd-bandeira').html(`<span class="iconify" data-icon="logos:visa"></span>`);
					} else if(bandeira=="mastercard") {
						$('.js-dd-bandeira').html(`<span class="iconify" data-icon="logos:mastercard"></span>`);

					} else if(bandeira=="amex") {
						$('.js-dd-bandeira').html(`<span class="iconify" data-icon="logos:amex"></span>`);
					} else {*/
						$('.js-dd-bandeira').html(bandeira);
					//}
				} else {
					$('.js-dd-bandeira').html(``);
				}
			})
		})
	</script>

	<section class="aside aside-cartao" style="display: none;">
		<div class="aside__inner1">
			<header class="aside-header">
				<h1>Cartão de Crédito</h1>
				<a href="javascript:;" class="aside-header__fechar aside-close"><i class="iconify" data-icon="fluent:dismiss-24-filled"></i></a>
			</header>

			<form method="post" class="aside-content form js-form-adicionar-cartao">
				
				<section class="filter">
					<div class="filter-group"></div>
					<div class="filter-group">
						<div class="filter-form form">
							<dl>
								<dd><button type="button" class="button button_main js-salvarCartao" data-loading="0"><i class="iconify" data-icon="fluent:add-circle-24-regular"></i> <span>Salvar</span></button></dd>
							</dl>
						</div>								
					</div>
				</section>


				<fieldset>
					<legend>Informações</legend>

					<div class="colunas4">
						<dl class="dl2">
							<dt>Número do Cartão</dt>
							<dd><input type="text" name="numero" class="obg" data-msg="Digite o número do cartão" /></dd>	
						</dl>
						<dl class="dl2">
							<dt>Nome impresso no Cartão</dt>
							<dd><input type="text" name="nome" placeholder="Joao A Silva" class="obg" data-msg="Digite o nome impresso no cartão" /></dd>	
						</dl>
					</div>
					<div class="colunas4">
						<dl class="">
							<dt>Validade</dt>
							<dd><input type="text" name="validade" placeholder="mm/aa" class="obg" data-msg="Digite a Data de Validade do cartão" /></dd>	
						</dl>
						<dl>
							<dt>CVV</dt>
							<dd><input type="text" name="cvv" placeholder="123" class="obg" data-msg="Digite o código CVV do cartão" maxlength="3" /></dd>	
						</dl>
					</div>
				</fieldset>
			</form>
		</div>
	</section>

<?php 
include "includes/footer.php";
?>	