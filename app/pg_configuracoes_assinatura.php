<?php
	if(isset($_POST['ajax'])) {


		require_once("lib/conf.php");
		require_once("usuarios/checa.php");


		header("Content-type: application/json");
		$rtn=array();

		$iugu = new Iugu();


		$infoConta='';
		$sql->consult("infodentalADM.infod_contas","*","where instancia='".addslashes($_ENV['NAME'])."'");
		if($sql->rows) $infoConta=mysqli_fetch_object($sql->mysqry);

		if($_POST['ajax']=="paymentMethodsCreate") {

			$cc_token = (isset($_POST['cc_token']) and !empty($_POST['cc_token'])) ? $_POST['cc_token'] : '';
			$bandeira = (isset($_POST['bandeira']) and !empty($_POST['bandeira'])) ? $_POST['bandeira'] : '';

			$erro='';
			/*if(empty($infoConta)) $erro='Conta não encontrada!';
			else if(empty($ultimosDigitos)) $erro='Os últimos dígitos do cartão não foram enviados!';
			else if(empty($bandeira)) $erro='Não foi possível capturar a bandeira do cartão!';
			else*/ 

			if(empty($cc_token)) $erro='Algum erro ocorreu durante a geração do token do cartão!';
			else if(empty($infoConta->iugu_customer_id)) {
				$attr=array('email'=>$infoConta->email,
							'name'=>$infoConta->tipo=="PF"?utf8_encode($infoConta->responsavel):utf8_encode($infoConta->razao_social),
							'cpf_cnpj'=>$infoConta->tipo=="PF"?utf8_encode($infoConta->cpf):utf8_encode($infoConta->cnpj));
				if($iugu->clientesCriar($attr)) {
					$customer_id=$iugu->response->id;
					$sql->update("infodentalADM.infod_contas","iugu_customer_id='".addslashes($customer_id)."'","where instancia='$infoConta->instancia'");
					$infoConta->iugu_customer_id=$customer_id;
				} else {
					$erro=isset($iugu->erro)?$iugu->erro:'Algum erro ocorreu durante a criação de seu cadastro';
				}
			} 

			if(empty($erro)) {


				$attr=array('description'=>empty($bandeira)?'Cartão de Credito':strtoupper($bandeira),
								'token'=>$cc_token,
								'set_as_default'=>true);
				if($iugu->formaDePagamentoCriar($infoConta->iugu_customer_id,$attr)) {
					
				} else {
					$erro=isset($iugu->erro)?$iugu->erro:'Algum erro ocorreu durante a criação de seu cadastro';
				}
			} 


			if(empty($erro)) {
				$rtn=array('success'=>true);
			} else {
				$rtn=array('success'=>false,
							'error'=>$erro);
			}
		} else if($_POST['ajax']=="paymentMethodRemove") {
			$payment_method_id=(isset($_POST['payment_method_id']) and !empty($_POST['payment_method_id']))?$_POST['payment_method_id']:'';

			$erro='';
			if(empty($infoConta->iugu_customer_id)) $erro='Você não possui assinatura!';
			else if(empty($payment_method_id)) $erro='Algum erro ocorreu durante a alteração do cartão de cobrança!';


			if(empty($erro)) {

				if($iugu->paymentMethodRemove($infoConta->iugu_customer_id,$payment_method_id)) {

				} else {
					$erro=isset($iugu->erro)?$iugu->erro:'Algum erro ocorreu';
				}
			} 


			if(empty($erro)) {
				$rtn=array('success'=>true);
			} else {
				$rtn=array('success'=>false,'error'=>$erro);
			}
		} else if($_POST['ajax']=="paymentMethodSetDefault") {


			$default_payment_method_id=(isset($_POST['default_payment_method_id']) and !empty($_POST['default_payment_method_id']))?$_POST['default_payment_method_id']:'';

			$erro='';
			if(empty($infoConta->iugu_customer_id)) $erro='Você não possui assinatura!';
			else if(empty($default_payment_method_id)) $erro='Algum erro ocorreu durante a alteração do cartão de cobrança!';

			if(empty($erro)) {
				if($iugu->paymentMethodSetDefault($infoConta->iugu_customer_id,$default_payment_method_id)) {

				} else {
					$erro=isset($iugu->erro)?$iugu->erro:'Algum erro ocorreu';
				}
			} 


			if(empty($erro)) {
				$rtn=array('success'=>true);
			} else {
				$rtn=array('success'=>false,'error'=>$erro);
			}
		} else if($_POST['ajax']=="paymentMethodsList") {

			if($iugu->customersDetail($infoConta->iugu_customer_id)) {
				$iuguCustomer=$iugu->response;
				
				$default_payment_method_id=(isset($iuguCustomer->default_payment_method_id) and !empty($iuguCustomer->default_payment_method_id))?$iuguCustomer->default_payment_method_id:'';
				

				$paymentMethods=[];
				if(!empty($infoConta->iugu_customer_id)) {
					if($iugu->formaDePagamentoListar($infoConta->iugu_customer_id)) {

						$response = $iugu->response;
						foreach($response as $v) {
							$paymentMethods[]=array('iugu_payment_method_id'=>$v->id,
													'bandeira'=>$v->data->brand,
													'nome'=>$v->data->holder_name,
													'numero'=>$v->data->display_number,
													'default'=>$v->id==$default_payment_method_id?1:0);
						}

					}
				}
			}

			$rtn=array('success'=>true,
						'paymentMethods'=>$paymentMethods);
		} else if($_POST['ajax']=="subdescriptionDetail") {

			$iuguSubstription='';
			if(!empty($infoConta->iugu_subscription_id)) {
				// consulta assinatura
				if($iugu->assinaturaConsultar($infoConta->iugu_subscription_id)) {
					if(isset($iugu->response->id)) {
						$iuguSubstription=$iugu->response;
					}
				}
			}

			// Se não possuir assinatura
			if(empty($iuguSubstription)) {
				$sql->update("infodentalADM.infod_contas","iugu_subscription_id=''","where instancia='".$infoConta->instancia."'");
				$infoConta->iugu_subscription_id='';
			} 


			$rtn=array('success'=>true,
						'iuguSubstription'=>is_object($iuguSubstription)?$iuguSubstription:0);
		}

		echo json_encode($rtn);
		die();
	}

	include "includes/header.php";
	include "includes/nav.php";

	$iugu = new Iugu();

	$_iuguPlanos=array();
	$sql->consult("infodentalADM.infod_planos","*","where lixo=0");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_iuguPlanos[$x->iugu_identifier]=$x;
	}

	$planoUnico=$_iuguPlanos['unico'];
	/*


		// verifica se possui assinatura
		$subscription='';
		if(!empty($cnt->iugu_subscription_id)) {
			if($iugu->assinaturaConsultar($cnt->iugu_subscription_id)) {
				if(isset($iugu->response->id)) {
					$subscription=$iugu->response;

					
					$assinatura = new Assinatura();

					$attr=array('instancia'=>$_ENV['NAME'],
								'subscription'=>$subscription);
					$assinatura->validarAssinatura($attr);

					
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
		} else if(isset($_GET['reativar'])) {
			if(is_object($subscription)) {

				if($iugu->assinaturaAtivar($cnt->iugu_subscription_id)) {
					//$sql->update("infodentalADM.infod_contas","iugu_subscription_id='$subscription_id'","where instancia='$cnt->instancia'");
					$jsc->jAlert("Assinatura foi ativada com sucesso!","sucesso","document.location.href='$_page'");
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
											'customer_id'=>$customer_id,
											'expires_at'=>date('Y-m-d H:i:s',strtotime(" + 7 days")));
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
		}*/


		// Se possuir assinatura
		/*$iuguSubstription='';
		if(!empty($infoConta->iugu_subscription_id)) {
			// consulta assinatura
			if($iugu->assinaturaConsultar($infoConta->iugu_subscription_id)) {
				if(isset($iugu->response->id)) {
					$iuguSubstription=$iugu->response;
				}
			}
		}

		// Se não possuir assinatura
		if(empty($iuguSubstription)) {
			$sql->update("infodentalADM.infod_contas","iugu_subscription_id=''","where instancia='".$infoConta->instancia."'");
			$infoConta->iugu_subscription_id='';
		} else {

	*/

	if(!empty($infoConta->iugu_subscription_id)) {
		if(isset($_GET['cancelar'])) {
			if($iugu->assinaturaCancelar($infoConta->iugu_subscription_id)) {
				$sql->update("infodentalADM.infod_contas","iugu_subscription_id=''","where instancia='$infoConta->instancia'");
				$sql->add("infodentalADM.infod_contas_subscriptions","data=now(),evento='cancelar',iugu_subscription_id='$infoConta->iugu_subscription_id',instancia='$infoConta->instancia'");
				$jsc->jAlert("Assinatura cancelada com sucesso!","sucesso","document.location.href='$_page'");
				die();
			} else {
				$jsc->jAlert("Algum erro ocorreu durante a suspensão desta assinatura!","erro","");
			}

			
		}
	}


	if(isset($_POST['acao'])) {


		// Realiza assinatura
		if($_POST['acao']=="assinatura") {

			# Persistir dados no infod_contas

				$vSQL="tipo='".($_POST['tipo']=="PJ"?"PJ":"PF")."',
						responsavel='".addslashes(utf8_decode($_POST['responsavel']))."',
						cpf='".addslashes(cpf($_POST['cpf']))."',
						razao_social='".addslashes(utf8_decode($_POST['razao_social']))."',
						cnpj='".addslashes(cnpj($_POST['cnpj']))."',
						celular='".addslashes(utf8_decode($_POST['celular']))."',
						email='".addslashes(utf8_decode($_POST['email']))."'";

				$sql->update("infodentalADM.infod_contas",$vSQL,"where instancia='".$infoConta->instancia."'");

				// atualiza objeto infoConta
				$sql->consult("infodentalADM.infod_contas","*","where instancia='".$infoConta->instancia."'");
				$infoConta=mysqli_fetch_object($sql->mysqry);
			
			# Persistencia do Cliente

				$attr=array('email'=>$infoConta->email,
							'name'=>$infoConta->tipo=="PF"?utf8_encode($infoConta->responsavel):utf8_encode($infoConta->razao_social),
							'cpf_cnpj'=>$infoConta->tipo=="PF"?utf8_encode($infoConta->cpf):utf8_encode($infoConta->cnpj),
							/*'zip_code'=>$infoConta->cep,
							'number'=>$infoConta->numero,
							'street'=>utf8_encode($infoConta->logradouro),
							'state'=>utf8_encode($infoConta->estado),
							'city'=>utf8_encode($infoConta->cidade),
							'district'=>utf8_encode($infoConta->bairro),
							'complement'=>utf8_encode($infoConta->complemento)*/
							);

				// se não possui cadastro na iugu
				if(empty($infoConta->iugu_customer_id)) {
					if($iugu->clientesCriar($attr)) {
						$customer_id=$iugu->response->id;
						$sql->update("infodentalADM.infod_contas","iugu_customer_id='".addslashes($customer_id)."'","where instancia='$infoConta->instancia'");
						$infoConta->iugu_customer_id=$customer_id;
					} else {
						$jsc->jAlert($iugu->erro,"erro","");

					}
					
				} 
				// se possuir altera dados
				else {
					if($iugu->clientesAlterar($infoConta->iugu_customer_id,$attr)) {
						
					} else {
						$jsc->jAlert($iugu->erro,"erro","");

					}
				}
			
			# Assinatura do Plano
				// se nao possui assinatura
				if(empty($infoConta->iugu_subscription_id)) {

					if(isset($_POST['iugu_plano']) and isset($_iuguPlanos[$_POST['iugu_plano']])) {
						$plan_identifier=addslashes($_POST['iugu_plano']);


						// verifica se cliente possui assinatura na iugu antes de criar uma nova
						$subscriptionIugu='';
						if($iugu->assinaturaListar($infoConta->iugu_customer_id)) {
							if(isset($iugu->response->items)) {
								foreach($iugu->response->items as $it) {
									if($it->suspended===false) {
										$subscriptionIugu=$it;
									}
								}
							}
						}

						if(is_object($subscriptionIugu)) {
							$sql->update("infodentalADM.infod_contas","iugu_subscription_id='$subscriptionIugu->id'","where instancia='$infoConta->instancia'");
							$jsc->jAlert("Sua conta já possui assinatura ativa!","erro","document.location.href='$_page'");
							die();
						} else {

							$attr = array('plan_identifier'=>$plan_identifier,
											'customer_id'=>$infoConta->iugu_customer_id,
											'expires_at'=>date('Y-m-d H:i:s',strtotime(" + 7 days")));

							if($iugu->assinaturaCriar($attr)) {

								$subscription_id=$iugu->response->id;

								$sql->update("infodentalADM.infod_contas","iugu_subscription_id='$subscription_id'","where instancia='$infoConta->instancia'");
							
								$jsc->jAlert("Assinatura realizada com sucesso!","sucesso","document.location.href='$_page'");
								die();
							} else {
								$jsc->jAlert($iugu->erro,"erro","");
							}
						}

					} else {
						$jsc->jAlert("Plano selecionado não encontrado!<br /><br />Favor entrar em contato com o nosso Suporte!<br /><br /><a href=https://api.whatsapp.com/send/?phone=55$_whatsappSuporte target=_blank class=button><span class=iconify data-icon=logos:whatsapp-icon></span> Falar no Whatsapp</a>","erro","");
					}

				}

		}
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

	<main class="main">
		<div class="main__content content">

			<section class="filter">
				<div class="filter-group">
					<div class="filter-title">
						<h1>Assinatura</h1>
					</div>
				</div>
			</section>
 	
 	
			<section class="grid">

				<div class="box box-col">

					<?php
					require_once("includes/submenus/subConfiguracoesAssinatura.php");
					?>

					<div class="box-col__inner1">
						<script type="text/javascript" src="https://js.iugu.com/v2"></script>
						<script type="text/javascript">
							var iuguSubstription = {};
							var paymentMethods = [];
							Iugu.setAccountID("26CC7D7DF11A49998FFD2EAFE7FECDB9");
							Iugu.setup();
							var bandeira = '';

							const subdescriptionDetail = (withDescription) => {
								let data = `ajax=subdescriptionDetail`;

								$.ajax({
									type:"POST",
									data:data,
									success:function(rtn) {
										if(rtn.success) {
											if(rtn.iuguSubstription==0) {
												if( withDescription===true) { 
													document.location.reload();
												}
											} else {
												iuguSubstription = rtn.iuguSubstription;


												// verifica status da assinatura
												let subscriptionStatus = '';
												if(iuguSubstription.suspended===false) {
													subscriptionStatus = `<span style="color:var(--verde)">Ativo</span>`;
													$('.js-btn-reativarPlano').hide();
													$('.js-btn-cancelarPlano').show();
												} else {
													subscriptionStatus = `<span style="color:var(--vermelho)">Inativo</span>`;
													$('.js-btn-reativarPlano').hide();
													$('.js-btn-cancelarPlano').show();
												}

												// exibe dados do plano assinado
												$('.js-subscription-plan-name').html(iuguSubstription.plan_name);
												$('.js-subscription-plan-price').html(number_format(iuguSubstription.price_cents/100,2,",","."));
												$('.js-subscription-status').html(subscriptionStatus);

												// lista faturas
												$('.js-invoices tr').remove();
												if(iuguSubstription.recent_invoices.length>0) {	
													iuguSubstription.recent_invoices.forEach(x=>{

														let statusCor = '';
														let status = ''; 
														if(x.status=="paid") {
															status="Pago";
															statusCor="var(--verde)";
														} else if(x.status=="pending") {

															let dtVencimento = new Date(x.due_date);
															let dtHoje = new Date('<?php echo date('Y-m-d');?>');
															if(dtVencimento>dtHoje) {
																status="A Vencer";
																statusCor="#ffcc00";
															} else {
																status='Vencido';
																statusCor='var(--vermelho)'
															}
														} else if(x.status="expired") {
															status="Expirada";
															statusCor="var(--vermelho)";
														}

														dueDate = new Date(x.due_date);
														//console.log(dueDate);
														$('.js-invoices').append(`<tr class="js-item" onclick="window.open('${x.secure_url}')">
																					<td class="list1__border" style="color:${statusCor}"></td>
																					<td><h1>${status}</h1></td>
																					<td>Vencimento: <b>${d2(dueDate.getDate())}/${d2((dueDate.getMonth()+1))}/${dueDate.getFullYear()}</b></td>
																					<td>Valor: <b>${x.total}</b></td>
																				</tr>`);
													});
												} else {
													$('.js-invoices').append('<tr><td><center>Nenhuma fatura recente.</center></td></tr>');
												}

												// exibe informacoes
												$('.js-assinatura-carregando,.js-faturas-carregando').hide();
												$('.js-assinatura-carregado,.js-faturas-carregado').fadeIn();
											}
										}
									}
								})
							}

							const paymentMethodsList = () => {


								$('.js-cartao-carregando').show();
								$('.js-cartao-carregado').hide();
								$('.js-paymentsMethods tr').remove();

								let data = `ajax=paymentMethodsList`;
								$.ajax({
									type:"POST",
									data:data,
									success:function(rtn) {
										if(rtn.success) {

											paymentMethods=rtn.paymentMethods;

											$('.js-paymentsMethods tr').remove();
											if(rtn.paymentMethods.length>0) {

												let num = 1;
												rtn.paymentMethods.forEach(x=>{

													let btn=``;
													if(x.default==1) {
														btn=`<a href="javascript:;" class="button button_main"><span class="iconify" data-icon="mdi:check-decagram-outline" data-height="20"></span></a>&nbsp;<a href="javascript:;" class="button" style="opacity:0.3"><span class="iconify" data-icon="bx-bx-trash"></span></a>`
													} else {
														btn=`<a href="javascript:;" class="button js-btn-paymentMethodSetDefault" data-id_cartao="${x.iugu_payment_method_id}" title="Tornar cartão cobrança padrão"><span class="iconify" data-icon="mdi:check-decagram-outline"></span></a>&nbsp;
															<a href="javascript:;" class="button js-btn-paymentMethodRemove"  data-id_cartao="${x.iugu_payment_method_id}" title="Excluir cartão de crédito"><span class="iconify" data-icon="bx-bx-trash"></span></a>`;//<label><input type="checkbox" data-id_cartao="${x.iugu_payment_method_id}" class="input-switch js-paymentMethodSetDefault"${x.default==1?' checked disabled':''} /> </label>`;
													}

													$('.js-paymentsMethods').append(`<tr class="js-item">
																						<td class="list1__border" style="color:"></td>
																						<td>${x.bandeira}</td>
																						<td>${x.numero}</td>
																						<td>${x.nome}</td>
																						<td style="width:90px;">${btn}</td>
																					</tr>`);

													if(num++==rtn.paymentMethods.length) {

														$('.js-paymentsMethods').append(`<tr class="js-item" style="background:var(--cinza2);">
																							<td colspan="5" style="border-radius:var(--border-radius2);color:var(--cinza5);border-color:var(--cinza5);cursor:pointer" onclick="$(this).parent().hide();$('.js-cc-add').show()"><center><a href="javascript:;" style="text-decoration:none"><i class="iconify" data-icon="material-symbols:add" data-height="15" data-inline="true"></i> Novo Cartão</a></center></td>
																						</tr>`);
													}
												});


											} else {
												$('.js-cc-add').show();
												$('.js-paymentsMethods').append('<tr><td><center>Nenhum cartão de crédito cadastrado.</center></td></tr>');
											}

											$('.js-cartao-carregando').hide();
											$('.js-cartao-carregado').fadeIn();
										}
									}
								})
							}

							/* Máscaras ER */
							function mascara(o,f){
							    v_obj=o
							    v_fun=f
							    setTimeout("execmascara()",1)
							}
							function execmascara(){
							    v_obj.value=v_fun(v_obj.value)
							}
							function mcc(v){
							    v=v.replace(/\D/g,"");
							    v=v.replace(/^(\d{4})(\d)/g,"$1 $2");
							    v=v.replace(/^(\d{4})\s(\d{4})(\d)/g,"$1 $2 $3");
							    v=v.replace(/^(\d{4})\s(\d{4})\s(\d{4})(\d)/g,"$1 $2 $3 $4");
							    return v;
							}
							function id( el ){
								return document.getElementById( el );
							}
							$(function(){

								$('input[name=number]').keyup(function(){
									v=$(this).val();
								    v=v.replace(/\D/g,"");
								    v=v.replace(/^(\d{4})(\d)/g,"$1 $2");
								    v=v.replace(/^(\d{4})\s(\d{4})(\d)/g,"$1 $2 $3");
								    v=v.replace(/^(\d{4})\s(\d{4})\s(\d{4})(\d)/g,"$1 $2 $3 $4");
								    $(this).val(v);
								});

								$('input[name=expiration]').keyup(function(e){

									if(e.which && e.which==8) return true;

									v=$(this).val();
								    v=v.replace(/\D/g,"");
								    v=v.replace(/^(\d{1})(\d)/g,"$1$2/");
								    v=v.replace(/^(\d{2})(\d)/g,"$1/$2");
								    $(this).val(v);
								});

								paymentMethodsList();
								subdescriptionDetail(<?php echo empty($infoConta->iugu_subscription_id)?false:true;?>);

								$('.js-btn-assinar').click(function(){
									let erro='';

									if($('input[name=tipo]:checked').val()=="PF") {
										if($('input[name=responsavel]').val().length==0) erro='Complete o campo <b>Responsável</b>';
										else if($('input[name=cpf]').val().length==0) erro='Complete o campo <b>CPF</b>';
										else if(validarCPF($('input[name=cpf]').val())===false) erro='CPF inválido!';
									} else {
										if($('input[name=razao_social]').val().length==0) erro='Complete o campo <b>Razão Social</b>';
										else if($('input[name=cnpj]').val().length==0) erro='Complete o campo <b>CNPJ</b>';
										else if(validarCNPJ($('input[name=cnpj]').val())===false) erro='CPF inválido!';
									}

									if(erro.length==0) {
										if($('input[name=celular]').val().length==0) erro='Complete o campo <b>Celular</b>';
										else if($('input[name=email]').val().length==0) erro='Complete o campo <b>E-mail</b>';
										else if($('input[name=termos]').prop('checked')==false) erro='Para realizar assinatura é preciso aceitar o Termo de Uso';
										else if(paymentMethods.length==0) erro='Cadsatre um Cartão de Crédito para prosseguir com a assinatura';
									}
									
									if(erro.length>0){
										swal({title: "Erro", text: erro, html:true, type:"error", confirmButtonColor: "#424242"});
									} else {

										let obj = $(this);

										if(obj.attr('data-loading')==0) {
											obj.attr('data-loading',1).html(`<i class="iconify" data-icon="eos-icons:loading"></i> <span>Assinando...</span>`);
											$('form.js-form-assinatura').submit();
											
										} 
									}

								});

								$('.js-paymentsMethods').on('click','.js-btn-paymentMethodRemove',function(){
									let obj = $(this);
									let objHTMLAntigo = obj.html();
									let iugu_payment_method_id = $(this).attr('data-id_cartao');

									$('.js-cartao-carregando').show();
									$('.js-cartao-carregado').hide();
								
									let data = `ajax=paymentMethodRemove&payment_method_id=${iugu_payment_method_id}`;
									$.ajax({
										type:"POST",
										data:data,
										success:function(rtn) {
											if(rtn.success) {

											}
										}
									}).done(function(){
										paymentMethodsList();
									});
								});

								$('.js-paymentsMethods').on('click','.js-btn-paymentMethodSetDefault',function(){

									let obj = $(this);
									let iugu_payment_method_id = $(this).attr('data-id_cartao');

									let data = `ajax=paymentMethodSetDefault&default_payment_method_id=${iugu_payment_method_id}`
									
									$('.js-cartao-carregando').show();
									$('.js-cartao-carregado').hide();

									$.ajax({
										type:"POST",
										data:data,
										success:function(rtn) {
											if(rtn.success) {
											} else if(rtn.error) {
												swal({title: "Erro", text: rtn.error, html:true, type:"error", confirmButtonColor: "#424242"});

											} else {	
												swal({title: "Erro", text: 'Algum erro ocorreu durante a alteração de titularidade', html:true, type:"error", confirmButtonColor: "#424242"});
											}
										},
										error:function(){
											swal({title: "Erro", text: 'Algum erro ocorreu durante a alteração de titularidade', html:true, type:"error", confirmButtonColor: "#424242"});
											
											paymentMethodsList();
										}
									}).done(function(){
										paymentMethodsList();
									})

								});

								$('.js-form-adicionar-cartao input[name=validade]').inputmask('99/99');

								$('.js-btn-novoCartao').click(function(){
									$(".aside-cartao").fadeIn(100,function() {
										$(".aside-cartao .aside__inner1").addClass("active");
										$('.aside-cartao input[name=expiration]').inputmask("99/99");
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

											let number = ($('input[name=number]').val());
											let expiration = $('input[name=expiration]').val().split('/');
											let expiration_mes = expiration[0] ? expiration[0] : '';
											let expiration_ano = expiration[1] ? expiration[1] : '';
											let verification_value = $('input[name=verification_value]').val();
											let full_name = $('input[name=full_name]').val().split(' ');
											let first_name = full_name[0];

											let last_name = '';
											for(var i = 0;i<full_name.length; i++) {
												if(i>0) last_name+=full_name[i]+' ';
											}

											bandeira = Iugu.utils.getBrandByCreditCardNumber(number);;

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

												cc = Iugu.CreditCard(number,expiration_mes,expiration_ano,first_name,last_name,verification_value);

												if(cc.valid()===false) {
													swal({title: "Erro", text: 'Dados do cartão de crédito inválido!', html:true, type:"error", confirmButtonColor: "#424242"});
												} else {

													obj.html(`<span class="iconify" data-icon="eos-icons:three-dots-loading"></span>`);
													obj.attr('data-loading',1);

													Iugu.createPaymentToken(cc, function(response) {
													    if (response.errors) {

													    	console.log(response.errors);
															swal({title: "Erro", text: "Algum erro ocorreu durante o registro de seu cartão. Tente novamente!", html:true, type:"error", confirmButtonColor: "#424242"});

															obj.html(objAntigo);
															obj.attr('data-loading',0);
													    } else {

													    	cc_token = response.id;

													    	let data = `ajax=paymentMethodsCreate&bandeira=${bandeira}&cc_token=${cc_token}`;
													   
													        $.ajax({
													        	type:"POST",
													        	data:data,
													        	success:function(rtn){
													        		if(rtn.success===true) {
													        			$('input.js-cc').val('');
													        			$('.aside-cartao .aside-close').click();
																		paymentMethodsList();
																		$('.js-cc-add').hide();
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
								});

							});
						</script>
					<?php

					// Nao possui assinatura
					if(empty($infoConta->iugu_subscription_id)) {
					?>
						

						<form method="post" class="form js-form-assinatura js-form-adicionar-cartao" action="<?php echo $_page;?>">
							<input type="hidden" name="acao" value="assinatura" />
							<input type="hidden" name="iugu_plano" value="<?php echo $planoUnico->iugu_identifier;?>" />

							<!-- Assinatura --> 
							<fieldset>
								<legend>Assinatura</legend>

								<div class="colunas2">
									<dl>
										<dt>Plano</dt>
										<dd><?php echo utf8_encode($planoUnico->titulo);?></dd>
									</dl>
									<dl>
										<dt>Valor</dt>
										<dd>R$ <?php echo number_format($planoUnico->valor,2,",",".");?>/mês</dd>
									</dl>
								</div>
							</fieldset>

							<!-- Dados Cadastrais --> 
							<fieldset>
								<legend>Dados Cadastrais</legend>

									<div>
										<dl>
											<dd>
												<label><input type="radio" name="tipo" value="PF"<?php echo $infoConta->tipo=="PF"?" checked":"";?> />Pessoa Física</label>
												<label><input type="radio" name="tipo" value="PJ"<?php echo $infoConta->tipo=="PJ"?" checked":"";?> />Pessoa Jurídica</label>
											</dd>
										</dl>

										<div class="colunas4 js-cpf">
											<dl class="dl2">
												<dt>Responsável</dt>
												<dd><input type="text" name="responsavel" value="<?php echo utf8_encode($infoConta->responsavel);?>" class="obg" /></dd>
											</dl>
											<dl>
												<dt>CPF</dt>
												<dd><input type="text" name="cpf" value="<?php echo ($infoConta->cpf);?>" class="obg cpf" /></dd>
											</dl>
										</div>

										<div class="colunas4 js-cnpj">
											<dl class="dl2">
												<dt>Razão Social</dt>
												<dd><input type="text" name="razao_social" value="<?php echo utf8_encode($infoConta->razao_social);?>" class="obg" /></dd>
											</dl>
											<dl>
												<dt>CNPJ</dt>
												<dd><input type="text" name="cnpj" value="<?php echo ($infoConta->cnpj);?>" class="obg cnpj" /></dd>
											</dl>
										</div>

										<div class="colunas4">
											<dl>
												<dt>Celular</dt>
												<dd><input type="text" name="celular" value="<?php echo ($infoConta->celular);?>" class="obg celular" /></dd>
											</dl>
											<dl class="dl2">
												<dt>E-mail</dt>
												<dd><input type="email" name="email" value="<?php echo utf8_encode($infoConta->email);?>" class="obg" /></dd>
											</dl>
										</div>

										<dl>
											<dd>
												<label><input type="checkbox" name="termos" class="input-switch" /> Aceito os Termos de Uso e Privacidade Info Dental</label>
											</dd>
											<dd>
												<a href="javascript:;" class="button"><span class="iconify" data-icon="ic:sharp-manage-search" data-height="20"></span> Visualizar Termos de Uso </a>
											</dd>
										</dl>
										
									</div>
							</fieldset>

							<!-- Cartao de Credito -->
							<fieldset>
								<legend>Cartão de Crédito</legend>

								<div class="colunas7 js-cc-add" style="display:none;">
									<dl class="dl2">
										<dt>Número do Cartão</dt>
										<dd><input type="text" name="number" class="obg js-cc" data-msg="Digite o número do cartão" maxlength="19" /></dd>	
									</dl>
									<dl class="dl2">
										<dt>Nome impresso no Cartão</dt>
										<dd><input type="text" name="full_name" placeholder="Joao A Silva" class="obg js-cc" data-msg="Digite o nome impresso no cartão" style="text-transform:uppercase;" /></dd>	
									</dl>
									<dl class="">
										<dt>Validade</dt>
										<dd><input type="text" name="expiration" placeholder="mm/aa" class="obg js-cc" data-msg="Digite a Data de Validade do cartão" maxlength="5" /></dd>	
									</dl>
									<dl>
										<dt>CVV</dt>
										<dd><input type="number" name="verification_value" placeholder="123" class="obg js-cc" data-msg="Digite o código CVV do cartão" maxlength="3" /></dd>	
									</dl>
									<dl>
										<dt>&nbsp;</dt>
										<dd><a href="javascript:;" class="button button_main js-salvarCartao" data-loading="0" style="float:right"><i class="iconify" data-icon="fluent:add-circle-24-regular"></i></a></dd>
									</dl>
								</div>

								<div class="js-cartao-carregando" style="margin-top:20px;font-size:0.875em;color:var(--cinza4)">
									<center><span class="iconify" data-icon="eos-icons:loading"></span> Carregando...</center>
								</div>

								<div class="js-cartao-carregado" style="display:none">

									<div class="list1">
										<table class="js-paymentsMethods">
											
										</table>
									</div>
								</div>
							</fieldset>

							<center>
								<a href="javascript:;" class="button button_main js-btn-assinar" data-loading="0"><i class="iconify" data-icon="fluent:checkmark-12-filled"></i> <span>Assinar</span></a>
							</center>
						</form>
					<?php
					} 
					else {
					?>

						<script type="text/javascript">
							
						</script>

						<form method="post" class="form formulario-validacao" action="<?php echo $_page;?>">
							<input type="hidden" name="acao" value="wlib" />

							<!-- Assinatura -->
							<fieldset>
								<legend>Assinatura</legend>
								<div class="js-assinatura-carregando" style="font-size:0.875em;color:var(--cinza4)">
									<center><span class="iconify" data-icon="eos-icons:loading"></span> Carregando...</center>
								</div>

								<div class="js-assinatura-carregado" style="display:none">

									<div class="colunas3">
										<dl>
											<dt>Plano</dt>
											<dd class="js-subscription-plan-name"></dd>
										</dl>

										<dl>
											<dt>Valor</dt>
											<dd class="js-subscription-plan-price"></dd>
										</dl>
										<dl>
											<dt>Status</dt>
											<dd class="js-subscription-status"></dd>
										</dl>
									</div>
									<br />
									<center>
											<a href="<?php echo $_page."?reativar=1";?>" class="button js-btn-reativarPlano" style="color:var(--verde);display: none;"><span class="iconify" data-icon="fluent:checkmark-12-filled"></span> 
										Reativar Assinatura</a>
										
											<a href="<?php echo $_page."?cancelar=1";?>" class="button js-btn-cancelarPlano js-confirmarDeletar" style="display: none;" data-msg="Tem certeza que deseja cancelar a assinatura?"><span class="iconify" data-icon="ep:close-bold"></span> 
										Cancelar Assinatura</a>

									</center>
								</div>
							</fieldset>

							<!-- Cartao de Credito -->
							<fieldset>
								
								<legend>Cartão de Crédito</legend>

								<div class="colunas7 js-cc-add" style="display:none;">
									<dl class="dl2">
										<dt>Número do Cartão</dt>
										<dd><input type="text" name="number" class="obg js-cc" data-msg="Digite o número do cartão" maxlength="19" /></dd>	
									</dl>
									<dl class="dl2">
										<dt>Nome impresso no Cartão</dt>
										<dd><input type="text" name="full_name" placeholder="Joao A Silva" class="obg js-cc" data-msg="Digite o nome impresso no cartão" style="text-transform:uppercase;" /></dd>	
									</dl>
									<dl class="">
										<dt>Validade</dt>
										<dd><input type="text" name="expiration" placeholder="mm/aa" class="obg js-cc" data-msg="Digite a Data de Validade do cartão" maxlength="5" /></dd>	
									</dl>
									<dl>
										<dt>CVV</dt>
										<dd><input type="number" name="verification_value" placeholder="123" class="obg js-cc" data-msg="Digite o código CVV do cartão" maxlength="3" /></dd>	
									</dl>
									<dl>
										<dt>&nbsp;</dt>
										<dd><a href="javascript:;" class="button button_main js-salvarCartao" data-loading="0" style="float:right"><i class="iconify" data-icon="fluent:add-circle-24-regular"></i></a></dd>
									</dl>
								</div>

								<div class="js-cartao-carregando" style="font-size:0.875em;color:var(--cinza4)">
									<center><span class="iconify" data-icon="eos-icons:loading"></span> Carregando...</center>
								</div>

								<div class="js-cartao-carregado" style="display:none">

									<div class="list1">
										<table class="js-paymentsMethods">
											
										</table>
									</div>
								</div>
							</fieldset>

							<!-- Faturas -->
							<fieldset>
								<legend>Faturas Recentes</legend>


								<div class="js-faturas-carregando" style="font-size:0.875em;color:var(--cinza4)">
									<center><span class="iconify" data-icon="eos-icons:loading"></span> Carregando...</center>
								</div>

								<div class="js-faturas-carregado" style="display:none">
									<div class="list1">
										<table class="js-invoices">
											
											
										</table>
									</div>
								</div>
							</fieldset>

						</form>
					<?php
					}
					?>

					</div>
					
				</div>

			</section>
		
		</div>
	</main>

	

	<?php /*<section class="aside aside-cartao" style="display: none;">
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
								
								<dd><button type="button" class="button button_main js-salvarCartao" data-loading="0"><i class="iconify" data-icon="fluent:checkmark-12-filled"></i> <span>Salvar</span></button></dd>
							</dl>
						</div>								
					</div>
				</section>


				<fieldset>
					<legend>Informações</legend>

					<div class="colunas4">
						<dl class="dl2">
							<dt>Número do Cartão</dt>
							<dd><input type="text" name="number" class="obg" data-msg="Digite o número do cartão" /></dd>	
						</dl>
						<dl class="dl2">
							<dt>Nome impresso no Cartão</dt>
							<dd><input type="text" name="full_name" placeholder="Joao A Silva" class="obg" data-msg="Digite o nome impresso no cartão" style="text-transform:uppercase;" /></dd>	
						</dl>
					</div>
					<div class="colunas4">
						<dl class="">
							<dt>Validade</dt>
							<dd><input type="text" name="expiration" placeholder="mm/aa" class="obg" data-msg="Digite a Data de Validade do cartão" /></dd>	
						</dl>
						<dl>
							<dt>CVV</dt>
							<dd><input type="number" name="verification_value" placeholder="123" class="obg" data-msg="Digite o código CVV do cartão" maxlength="3" /></dd>	
						</dl>
					</div>
				</fieldset>
			</form>
		</div>
	</section>*/?>

<?php 
include "includes/footer.php";
?>	