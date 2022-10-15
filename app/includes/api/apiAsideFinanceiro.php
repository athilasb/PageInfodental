<?php
	// formas de pagamento
		$_formasDePagamento=array();
		$optionFormasDePagamento='';
		$sql->consult($_p."parametros_formasdepagamento","*","order by titulo asc");
		while($x=mysqli_fetch_object($sql->mysqry)) {
			$_formasDePagamento[$x->id]=$x;
			$optionFormasDePagamento.='<option value="'.$x->id.'" data-tipo="'.$x->tipo.'">'.utf8_encode($x->titulo).'</option>';
		}

	// credito, debito, bandeiras
		$_bandeiras=array();
		$sql->consult($_p."parametros_cartoes_bandeiras","*","where lixo=0");
		while($x=mysqli_fetch_object($sql->mysqry)) {
			$_bandeiras[$x->id]=$x;
		}


		$creditoBandeiras=array();
		$debitoBandeiras=array();
		$_operadoras=array();

		$sql->consult($_p."parametros_cartoes_operadoras","*","where lixo=0 order by titulo");
		while($x=mysqli_fetch_object($sql->mysqry)) {
			$creditoBandeiras[$x->id]=array('titulo'=>utf8_encode($x->titulo),'bandeiras'=>array());
			$debitoBandeiras[$x->id]=array('titulo'=>utf8_encode($x->titulo),'bandeiras'=>array());
			$_operadoras[$x->id]=$x;
		}

		$sql->consult($_p."parametros_cartoes_operadoras_bandeiras","*","where lixo=0");
		while($x=mysqli_fetch_object($sql->mysqry)) {
			if(!isset($_operadoras[$x->id_operadora])) continue;

			if(isset($_bandeiras[$x->id_bandeira])) {
				$bandeira=$_bandeiras[$x->id_bandeira];
				
				$txJson = json_decode($x->taxas);


				if($x->check_debito==1) {
					$debitoTaxa=isset($txJson->debitoTaxas->taxa)?$txJson->debitoTaxas->taxa:0;
					$debitoDias=isset($txJson->debitoTaxas->dias)?$txJson->debitoTaxas->dias:0;
					$debitoBandeiras[$x->id_operadora]['bandeiras'][$x->id_bandeira]=array('id_bandeira'=>$x->id_bandeira,
																							'titulo'=>utf8_encode($bandeira->titulo),
																						 	'taxa'=>$debitoTaxa,
																						 	'dias'=>$debitoDias);
				}
				if($x->check_credito==1) {
					$creditoTaxa=isset($txJson->creditoTaxas->taxa)?$txJson->creditoTaxas->taxa:0;
					$creditoBandeiras[$x->id_operadora]['bandeiras'][$x->id_bandeira]=array('id_bandeira'=>$x->id_bandeira,
																							'titulo'=>utf8_encode($bandeira->titulo),
																							'parcelas'=>$x->credito_parcelas,
																						 	'taxa'=>$creditoTaxa,
																							'semJuros'=>$x->credito_parcelas_semjuros);
				}
			}
		}


?>

<script type="text/javascript">
	const pagamentosAtualizaCampos = (formaDePagamento) => {
		if(formaDePagamento) {
			let id_formadepagamento  = formaDePagamento.val();
			let obj = formaDePagamento.parent().parent().parent().parent();
			let tipo = $(obj).find('select.js-id_formadepagamento option:checked').attr('data-tipo');

			$(obj).find('.js-identificador,.js-parcelas,.js-creditoBandeira,.js-debitoBandeira,.js-debitoBandeira,.js-valorCreditoDebito,.js-obs,.js-valorCreditoDebitoTaxa').parent().parent().hide();

			if(tipo=="credito") {
				$(obj).find('.js-parcelas,.js-creditoBandeira,.js-valorCreditoDebito,.js-valorCreditoDebitoTaxa,.js-identificador').parent().parent().show();
			} else if(tipo=="debito") {
				$(obj).find('.js-debitoBandeira,.js-valorCreditoDebito,.js-valorCreditoDebitoTaxa,.js-identificador').parent().parent().show();
			} else {
				$(obj).find('.js-identificador').parent().parent().show();

				if(tipo=="permuta") {
					//$(obj).find('.js-obs').parent().parent().show();
				}
			}


			let index = $('.js-pagamentos .js-id_formadepagamento').index(this);

			if(atualizaObjetoPagamento===true) {
				pagamentosPersistirObjeto();	
			}
		} else {
			$('#js-aside-asFinanceiro').find('.js-identificador,.js-parcelas,.js-creditoBandeira,.js-debitoBandeira,.js-debitoBandeira,.js-valorCreditoDebito,.js-obs,.js-valorCreditoDebitoTaxa').parent().parent().hide();
		}
	}
	$(function(){
		$('#js-aside-asFinanceiro .js-tab a').click(function() {
			$(".js-tab a").removeClass("active");
			$(this).addClass("active");							
		});

		$('#js-aside-asFinanceiro').on('click','.js-desfazerUniao',function(){
			let id_pagamento = $(this).attr('data-id_pagamento');
			let data = `ajax=desfazerUniao&id_pagamento=${id_pagamento}`;

			swal({   
					title: "Atenção",   
					text: "Tem certeza que deseja desfazer essa união de pagamento?",
					type: "warning",   
					showCancelButton: true,   
					confirmButtonColor: "#DD6B55",   
					confirmButtonText: "Sim!",   
					cancelButtonText: "Não",  
					closeOnConfirm: false,   
					closeOnCancel: false 
				}, function(isConfirm){   
					if (isConfirm) {    
						$.ajax({
							type:"POST",
							data:data,
							success:function(rtn) {
								if(rtn.success) {
									document.location.href='<?php echo "$_page?$url";?>';
								} else if(rtn.error) {
									swal({title: "Erro!", text: rtn.error,  html:true,type:"error", confirmButtonColor: "#424242"});
								} else {
									swal({title: "Erro!", text: "Algum erro ocorreu durante a baixa deste pagamento!",  html:true,type:"error", confirmButtonColor: "#424242"});
								}
							},
							error:function() {
								swal({title: "Erro!", text: "Algum erro ocorreu durante a baixa deste pagamento!",  html:true,type:"error", confirmButtonColor: "#424242"});
							}
						})  
					} else {   
						swal.close();   
					} 
				});
		});

		$('select.js-id_formadepagamento').change(function(){
			pagamentosAtualizaCampos($(this));
		});
		pagamentosAtualizaCampos('');
	})
</script>
<section class="aside" id="js-aside-asFinanceiro">
	<div class="aside__inner1">

		<header class="aside-header">
			<h1 class="js-titulo"></h1>
			<a href="javascript:;" class="aside-header__fechar aside-close"><i class="iconify" data-icon="fluent:dismiss-24-filled"></i></a>
		</header>

		<form method="post" class="aside-content form">
			

			<section class="tab tab_alt js-tab">
				<a href="javascript:;" onclick="$('.js-fin').hide(); $('.js-fin-resumo').show();" class="active">Informações</a>	
				<a href="javascript:;" onclick="$('.js-fin').hide(); $('.js-fin-programacao').show();" class="">Programação de Pagamento</a>
				<a href="javascript:;" onclick="$('.js-fin').hide(); $('.js-fin-agrupamento').show();" class="js-tab-agrupamento">Agrupamento</a>
			</section>


			<!-- Resumo do pagamento -->
			<div class="js-fin js-fin-resumo">
				<section class="filter"></section>
				<fieldset>
					<legend>Informações da Parcela</legend>
					<div class="colunas3">
						<dl>
							<dt><strong>Valor da Parcela</strong></dt>
							<dd><input type="tel" class="js-parcela" disabled /></dd>
						</dl>
					</div>
					<div class="colunas3">
						<dl>
							<dt>Desconto</dt>
							<dd class="form-comp"><span>–</span><input type="tel" class="js-desconto" disabled /></dd>
						</dl>
						<dl>
							<dt>Despesa</dt>
							<dd class="form-comp"><span>+</span><input type="tel" class="js-despesa" disabled /></dd>
						</dl>
						<dl>
							<dt>Valor Corrigido</dt>
							<dd><input type="tel" class="js-corrigido" disabled /></dd>
						</dl>
					</div>
					<div class="colunas3">
						<dl>
							<dt>Valor Pago</dt>
							<dd><input type="tel" class="js-pago" disabled /></dd>
						</dl>
						<dl>
							<dt>Saldo a Pagar</dt>
							<dd><input type="tel" class="js-apagar" disabled /></dd>
						</dl>
					</div>
				</fieldset>
			</div>

			<!-- Programacao de pagamento -->
			<div class="js-fin js-fin-programacao" style="display: none;">
				<section class="filter">
					<?php /*<div class="filter-group"></div>
					<div class="filter-group">
						<div class="filter-form form">
							<dl>
								<dd><a href="" class="button"><i class="iconify" data-icon="fluent:delete-24-regular"></i></a></dd>
							</dl>
							<dl>
								<dd><button class="button button_main"><i class="iconify" data-icon="fluent:checkmark-12-filled"></i> <span>Salvar</span></button></dd>
							</dl>
						</div>								
					</div>*/?>
				</section>
				
				<fieldset>
					<legend>Informações do Pagamento</legend>
					<div class="colunas5">
						<dl>
							<dt>Valor da Parcela</dt>
							<dd><input type="text" class="js-valorParcela" value=""  disabled style="background: #ccc" /></dd>
						</dl>

						<dl>
							<dt>Desconto (-)</dt>
							<dd><input type="text" class="js-valorDesconto money" data-tipo="descontos" style="background: #ccc" disabled  /></dd>
						</dl>
						<dl>
							<dt>Despesa (+)</dt>
							<dd><input type="text" class="js-valorDespesa money" data-tipo="despesas" style="background: #ccc" disabled  /></dd>
						</dl>
						<dl>
							<dt>Multas/Juros (+)</dt>
							<dd><input type="text" class="js-multasJuros money"  value="0,0" style="background: #ccc"  /></dd>
						</dl>
						<dl>
							<dt>Valor Corrigido</dt>
							<dd><input type="text" class="js-valorCorrigido" value="" disabled style="background: #ccc" /></dd>
						</dl>

					</div>
				</fieldset>

				<fieldset class="js-fieldset-pagamentos">
					<legend>Baixa</legend>
					<div class="colunas5">

						<dl>
							<dt>Saldo a Pagar</dt>
							<dd><input type="text" class="js-saldoPagar" value="" disabled style="background:#ccc" /></dd>
						</dl>

						<dl class="dl3">
							<dd>
								<label><input type="radio" name="tipoBaixa" value="pagamento" /> Pagamento</label>
								<label><input type="radio" name="tipoBaixa" value="desconto" /> Desconto (-)</label>
								<label><input type="radio" name="tipoBaixa" value="despesa" /> Despesa (+)</label>
							</dd>
						</dl>

					</div>
					<div class="colunas5">
						<dl>
							<dt>Data Pagamento</dt>
							<dd><input type="text" class="js-dataPagamento js-tipoDescontoDespesa datahora" value="<?php echo date('d/m/Y H:i');?>" /></dd>
						</dl>
						<dl class="dl2">
							<dt>Forma de Pagamento</dt>
							<dd>
								<select class="js-id_formadepagamento js-tipoPagamento">
									<option value="">-</option>
									<?php echo $optionFormasDePagamento;?>
								</select>
							</dd>
						</dl>
						
						<dl>
							<dt class="js-txt-vencimento">Pagamento</dt>
							<dd><input type="text" class="js-vencimento js-tipoPagamento data" value="<?php echo date('d/m/Y');?>" /></dd>
						</dl>
						<dl>
							<dt>Valor</dt>
							<dd><input type="text" class="js-valor money" /></dd>
						</dl>

						<dl>
							<dt>Identificador</dt>
							<dd><input type="text" class="js-identificador js-tipoPagamento" /></dd>
						</dl>

						<dl>
							<dt>Valor da Parcela</dt>
							<dd><input type="text" class="js-valorCreditoDebito js-tipoPagamento" readonly style="background:#CCC" /></dd>
						</dl>

						<dl>
							<dt>Taxa (%)</dt>
							<dd><input type="text" class="js-valorCreditoDebitoTaxa js-tipoPagamento" readonly style="background:#CCC" /></dd>
						</dl>



						<dl class="dl3">
							<dt>Obs.:</dt>
							<dd><input type="text" class="js-obs js-tipoDescontoDespesa" /></dd>
						</dl>
					</div>
					<div class="colunas5">

						<dl class="dl3">
							<dt>Bandeira</dt>
							<dd>
								<select class="js-creditoBandeira js-tipoPagamento">
									<option value="">selecione</option>
									<?php
									foreach($creditoBandeiras as $id_operadora=>$x) {
										echo '<optgroup label="'.utf8_encode($x['titulo']).'">';
										foreach($x['bandeiras'] as $band) {
											echo '<option value="'.$band['id_bandeira'].'" data-id_operadora="'.$id_operadora.'" data-semjuros="'.$band['semJuros'].'" data-parcelas="'.$band['parcelas'].'" data-taxa="'.$band['taxa'].'">'.utf8_encode($band['titulo']).'</option>';
										}
										echo '</optgroup>';
									}
									
									?>
								</select>
							</dd>
						</dl>

						<dl class="dl2">
							<dt>Qtd. Parcelas</dt>
							<dd>
								<select class="js-parcelas js-tipoPagamento">
									<option value="">selecione a bandeira</option>
								</select>
							</dd>
						</dl>

						<script type="text/javascript">
							$(function(){
								$('.js-creditoBandeira').change(function(){
									$('select.js-parcelas option').remove();
									
									if($(this).val().length>0) {
										let parcelas = eval($(this).find('option:checked').attr('data-parcelas'));
										//alert(parcelas);
										if($.isNumeric(parcelas)) {
											$('select.js-parcelas').append(`<option value="">-</option>`);
											for(var i=1;i<=parcelas;i++) {
												$('select.js-parcelas').append(`<option value="${i}">${i}x</option>`);
											}
										} else {
											$('select.js-parcelas').append(`<option value="">erro</option>`);
										}
									} else {
										$('select.js-parcelas').append(`<option value="">selecione a bandeira</option>`);
									}

								});
							});
						</script>


						

						<?php
						/*
							copiar sistema do cartao decolar formas de pagamento
							
						*/
						?>
						

					</div>

					<div class="colunas5">
						<dl class="dl3">
							<dt>Bandeira</dt>
							<dd>
								<select class="js-debitoBandeira js-tipoPagamento">
									<option value="">selecione</option>
									<?php
									foreach($debitoBandeiras as $id_operadora=>$x) {
										echo '<optgroup label="'.utf8_encode($x['titulo']).'">';
										foreach($x['bandeiras'] as $band) {
											echo '<option value="'.$band['id_bandeira'].'" data-id_operadora="'.$id_operadora.'" data-taxa="'.$band['taxa'].'" data-cobrarTaxa="'.$band['cobrarTaxa'].'">'.utf8_encode($band['titulo']).'</option>';
										}
										echo '</optgroup>';
									}
									?>
								</select>
							</dd>
						</dl>
					</div>

					<dl>
						<dd><a href="javascript:;" class="button__full button js-btn-addPagamento" data-loading="0">adicionar baixa</a></dd>
					</dl>

					
				</fieldset>

				<fieldset>
					<legend>Pagamentos</legend>

					<div class="list2">
						<table class="js-table-baixas">
							<tr>
								<th></th>
								<th>Data Recebimento</th>
								<th>Tipo de Baixa</th>
								<th>Forma/Obs.</th>
								<th>Valor</th>
								<th style="width:80px;"></th>
							</tr>

						</table>
					</div>

				</fieldset>

			</div>

			<!-- Agrupamento de pagamento -->
			<div class="js-fin js-fin-agrupamento" style="display: none;">
				<section class="filter"></section>
				<fieldset>
					<legend>Agrupamento de Pagamentos</legend>

					<div class="list2">
						<table>
							<thead>
								<tr>									
									<th>Data</th>
									<th>Plano</th>
									<th>Valor</th>		
								</tr>
							</thead>
							<tbody class="js-subpagamentos">
								
							</tbody>
						</table>
					</div>				
				</fieldset>
			</div>
		</form>
	</div>
</section><!-- .aside -->