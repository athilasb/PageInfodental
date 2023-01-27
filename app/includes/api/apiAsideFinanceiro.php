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
		$taxasBandeiras = array();
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
				$taxasBandeiras[$x->id_operadora][$x->id_bandeira] = $txJson->creditoTaxas??[];
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

	// banco e contas
		$_bancos=array();
		$sql->consult($_p."financeiro_bancosecontas","*"," WHERE lixo =0 order by titulo asc");
		while($x=mysqli_fetch_object($sql->mysqry)) $_bancos[$x->id]=$x;

	
?>

<script type="text/javascript">
	const _taxaBandeiras = <?=json_encode($taxasBandeiras)?>;
	// carrega campos de complemento da forma de pagamento
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
		} else {
			$('#js-aside-asFinanceiro').find('.js-identificador,.js-parcelas,.js-creditoBandeira,.js-debitoBandeira,.js-debitoBandeira,.js-valorCreditoDebito,.js-obs,.js-valorCreditoDebitoTaxa').parent().parent().hide();
		}
	}
	$(function(){
		// se clicar nas abas
		$('#js-aside-asFinanceiro .js-tab a').click(function() {
			$(".js-tab a").removeClass("active");
			$(this).addClass("active");							
		});

		// desfaz uniao de agrupamento de pagametnos
		$('#js-aside-asFinanceiro').on('click','.js-desfazerUniao',function(){
			let idPagamento = $('#js-aside-asFinanceiro .js-id_pagamento').val();
			let data = `ajax=desfazerUniao&id_pagamento=${idPagamento}`;

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

		// atualiza complementos das formas de pagamento 
		$('select.js-id_formadepagamento').change(function(){
			pagamentosAtualizaCampos($(this));
		});

		// se alterar o tipo de baixa
		$('input[name=tipoBaixa]').click(function(){
			
			if($(this).val()=="pagamento") {
				$('.js-tipoPagamento').parent().parent().show();
				$('.js-tipoDescontoDespesa').parent().parent().hide();
				$('.js-obs').parent().parent().hide();
				$('select.js-id_formadepagamento').trigger('change');
			} else { 
				$('.js-tipoPagamento').parent().parent().hide();
				$('.js-tipoDescontoDespesa').parent().parent().show();
				$('.js-obs').parent().parent().show();
			}
		});

		pagamentosAtualizaCampos('');

		// se clicar em adicionar baixa
		$('.js-btn-addBaixa').click(function(){
			let obj = $(this);
			let objHTMLAntigo = obj.html();
			let loading = obj.attr('data-loading');
			if(loading==0) {
				obj.html('<span class="iconify" data-icon="eos-icons:loading"></span>');
				obj.attr('data-loading',1);
				let tipoPagamento = $('.js-id_formadepagamento option:checked').attr('data-tipo');
				saldoAPagar = unMoney($('.js-saldoPagar').val());
				let tipoBaixa = $('input[name=tipoBaixa]:checked').val();
				if(tipoBaixa=="despesa" || tipoBaixa=="desconto" || tipoPagamento!==undefined) {
					let dataPagamento = $('input.js-dataPagamento').val();
					let dataVencimento = $('input.js-vencimento').val();
					let valor = ($('input.js-valor').val().length>0 && unMoney($('input.js-valor').val())>0)?unMoney($('input.js-valor').val()):'';
					let valorJuros = ($('input.js-valorJuros').val().length>0 && unMoney($('input.js-valorJuros').val())>0)?unMoney($('input.js-valorJuros').val()):'';
					let valorMulta = ($('input.js-valorMultas').val().length>0 && unMoney($('input.js-valorMultas').val())>0)?unMoney($('input.js-valorMultas').val()):'';
					let descontoMultasJuros = ($('input.js-descontoMultasJuros').val().length>0 && unMoney($('input.js-descontoMultasJuros').val())>0)?unMoney($('input.js-descontoMultasJuros').val()):0;
					let id_formadepagamento = $('.js-id_formadepagamento').val();
					let obs = $('input.js-obs').val();
					let debitoBandeira=$('select.js-debitoBandeira').val();
					let creditoBandeira=$('select.js-creditoBandeira').val();
					let creditoParcelas=$('select.js-parcelas').val();
					let id_operadora=0;
					let taxa=0;
					id_pagamento = $('#js-aside-asFinanceiro .js-id_pagamento').val();
					let erro = '';
					if($('.js-aplicar-multas-juros').prop('checked')==false){
						valorJuros=0
						valorMulta=0
						descontoMultasJuros=0
					}
					if(tipoBaixa=='pagamento') {
						if(tipoPagamento=='credito') {
							if(dataVencimento.length==0) erro= 'Defina a <b>Data do Vencimento</b>';
							else if(valor.length==0) erro= 'Defina o <b>Valor do Pagamento</b>';
							else if(creditoBandeira.length==0) erro= 'Selecione a <b>Bandeira</b> do Cartão de Crédito';
							else if(creditoParcelas.length==0) erro= 'Selecione o <b>Nº de Parcelas</b> do Cartão de Crédito';
							else if(saldoAPagar<unMoney($('input.js-valor').val())) erro=`O valor não pode ser maior que <b>${number_format(saldoAPagar,2,",",".")}`;
							id_operadora=$('select.js-creditoBandeira option:selected').attr('data-id_operadora');
							taxa=$('input.js-valorCreditoDebitoTaxa').val();
							valorParcela=$('input.js-valorCreditoDebito').val();
							valorJuros = valorJuros/creditoParcelas
							valorMulta = valorMulta/creditoParcelas
							descontoMultasJuros = descontoMultasJuros/creditoParcelas

						} else if(tipoPagamento=='debito') {
							if(dataVencimento.length==0) erro= 'Defina a <b>Data do Vencimento</b>';
							else if(valor.length==0) erro= 'Defina o <b>Valor do Pagamento</b>';
							else if(debitoBandeira.length==0) erro= 'Selecione a <b>Bandeira</b> do Cartão de Débito';
							else if(saldoAPagar<unMoney($('input.js-valor').val())) erro=`O valor não pode ser maior que <b>${number_format(saldoAPagar,2,",",".")}`;
							id_operadora=$('select.js-debitoBandeira option:selected').attr('data-id_operadora');
							taxa=$('input.js-valorCreditoDebitoTaxa').val();
							valorParcela=$('input.js-valorCreditoDebito').val();
						
						} else {
							id_operadora=0;
							taxa=0;
							valorParcela=$('input.js-valor').val().replace(/[^\d,-.]+/g,'');

							if(dataVencimento.length==0) erro= 'Defina a <b>Data do Vencimento</b>';
							else if(valor.length==0) erro= 'Defina o <b>Valor do Pagamento</b>';
						}
					} else if(tipoBaixa=='desconto' || tipoBaixa=='despesa') {
						if(dataPagamento.length==0) erro= 'Defina a <b>Data do Pagamento</b>';
						else if(valor.length==0) erro= 'Defina o <b>Valor</b>';
						else if(obs.length==0) erro= 'Digite uma <b>Observação</b>';
						valorParcela=$('input.js-valor').val().replace(/[^\d,-.]+/g,'');
					}

					if(dataPagamento.length==0) erro='Defina a <b>Data</b>';
					else if(tipoBaixa.length==0) erro='Defina o <b>Tipo de Baixa</b>';
					else if(valor.length==0) erro= 'Defina o <b>Valor</b> a ser pago';
					else if(tipoBaixa=="pagamento" && id_formadepagamento.length==0) erro='Defina a <b>Forma de Pagamento</b>';
					else if(saldoAPagar<=0) erro=`Não existe mais débitos!`; 
					else if(descontoMultasJuros>=(valorParcela+valorJuros+valorMulta)) erro=`Voce Não Pode dar um Desconto Maior do que o Valor da Parcela!`; 
					else if(saldoAPagar<unMoney(valorParcela)) erro=`O valor não pode ser maior que <b>${number_format(saldoAPagar,2,",",".")}`;
					valorParcela=(unMoney(valorParcela)+valorJuros+valorMulta)-descontoMultasJuros
					if(erro.length==0) {
						//return;
						let data = `ajax=pagamentoBaixa&tipoBaixa=${tipoBaixa}&id_pagamento=${id_pagamento}&dataPagamento=${dataPagamento}&dataVencimento=${dataVencimento}&valor=${valor}&id_formadepagamento=${id_formadepagamento}&debitoBandeira=${debitoBandeira}&creditoBandeira=${creditoBandeira}&creditoParcelas=${creditoParcelas}&obs=${obs}&id_operadora=${id_operadora}&taxa=${taxa}&valorParcela=${(valorParcela)}&valorJuros=${(valorJuros)}&valorMulta=${(valorMulta)}&descontoMultasJuros=${descontoMultasJuros}`;
						$.ajax({
							type:"POST",
							data:data,
							success:function(rtn) {
								if(rtn.success) {
									baixas=rtn.baixas;
									baixasAtualizar();
									$('.js-dataPagamento').val('<?php echo date('d/m/Y');?>');
									$('.js-valor').val('');
									$('.js-desconto').val('');
									$('.js-id_formadepagamento').val('');
									$('.js-obs').val('');
									$('.js-valorJuros').val('');
									$('.js-valorMultas').val('');
									$('.js-descontoMultasJuros').val('');
									$('.js-TotalaPagar').val('');

									$('.js-valorJuros').closest('dl').hide()
									$('.js-valorMultas').closest('dl').hide()
									$('.js-descontoMultasJuros').closest('dl').hide()
									$('.js-TotalaPagar').closest('dl').hide()

									$('.js-valorCreditoDebitoTaxa').closest('dl').hide()
									$('.js-valorCreditoDebito').closest('dl').hide()
									$('.js-parcelas').closest('dl').hide()
									$('.js-creditoBandeira').closest('dl').hide()


									if(saldoAPagar<=0) $('.js-form-pagamentos').hide();
								} else if(rtn.error) {
									swal({title: "Erro!", text: rtn.error,  html:true,type:"error", confirmButtonColor: "#424242"});
								} else {
									swal({title: "Erro!", text: "Algum erro ocorreu durante a baixa deste pagamento!",  html:true,type:"error", confirmButtonColor: "#424242"});
								}
							},
							error:function() {
								swal({title: "Erro!", text: "Algum erro ocorreu durante a baixa deste pagamento",  html:true,type:"error", confirmButtonColor: "#424242"});
								obj.html(objHTMLAntigo);
								obj.attr('data-loading',0);
							}
						}).done(function(){
							obj.html(objHTMLAntigo);
							obj.attr('data-loading',0);
						});
					} else {
						swal({title: "Erro!", text: erro,  html:true,type:"error", confirmButtonColor: "#424242"});
						obj.html(objHTMLAntigo);
						obj.attr('data-loading',0);
					}
				} else {
					swal({title: "Erro!", text: "Define a <b>Forma de Pagamento</b>",  html:true,type:"error", confirmButtonColor: "#424242"});	
					obj.html(objHTMLAntigo);
					obj.attr('data-loading',0);
				}
			}
		});
		
		$('input.money').maskMoney({symbol:'', allowZero:false, showSymbol:true, thousands:'.', decimal:',', symbolStay: true});

		$('.aside-close').click(function(){
			if($('[name="alteracao"]').val()=='1'){
				document.location.reload();
			}
		})

	});
</script>
<section class="aside" id="js-aside-asFinanceiro">	
	<div class="aside__inner1">
		<input type="hidden" name="alteracao" value="0">
		<header class="aside-header">
			<h1 class="js-titulo"></h1>
			<a href="javascript:;" class="aside-header__fechar aside-close"><i class="iconify" data-icon="fluent:dismiss-24-filled"></i></a>
		</header>

		<form method="post" class="aside-content form">
			<input type="hidden" class="js-id_pagamento" value="0" />

			<section class="tab tab_alt js-tab">
				<?php /*<a href="javascript:;" onclick="$('.js-fin').hide(); $('.js-fin-resumo').show();" class="active">Informações</a>*/?>
				<a href="javascript:;" onclick="$('.js-fin').hide(); $('.js-fin-programacao').show();" class="">Programação de Pagamento</a>
				<a href="javascript:;" onclick="$('.js-fin').hide(); $('.js-fin-agrupamento').show();" class="js-tab-agrupamento">Agrupamento</a>
			</section>

			<input type="hidden" class="js-index" />

	
			<!-- Programacao de pagamento -->
			<div class="js-fin js-fin-programacao" style="display: ;">
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
					<div class="colunas4">
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
							<dd><input type="text" class="js-valorDespesa money" data-tipo="despesas" style="background: #ccc" disabled /></dd>
						</dl>
						<dl>
							<dt>Valor Corrigido</dt>
							<dd><input type="text" class="js-valorCorrigido money" value="" disabled style="background: #ccc" disabled/></dd>
						</dl>

					</div>
				</fieldset>
				<fieldset class="js-fieldset-pagamentos">
					<legend>Definição de Pagamento</legend>
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
						<dl style="justify-content:center;align-items: center;">
							<dd style='margin-left:50px;'>
								<dt style="text-align:center">Aplicar Juros e Multas</dt>
								<label><input  type="checkbox" class="input-switch js-aplicar-multas-juros" checked/></label>
							</dd>
						</dl>

					</div>
					<div class="colunas5">
						<dl>
							<dt>Pagamento</dt>
							<dd><input type="text" class="js-dataPagamento js-tipoDescontoDespesa data" value="<?php echo date('d/m/Y');?>" /></dd>
						</dl>
						<dl>
							<dt>Valor</dt>
							<dd><input type="text" class="js-valor money" /></dd>
						</dl>
						<dl class="dl2">
							<dt>Forma de Pagamento</dt>
							<dd>
								<select class="js-id_formadepagamento js-tipoPagamento">
									<option value="">-</option>
									<?= $optionFormasDePagamento;?>
								</select>
							</dd>
						</dl>
						
						<dl>
							<dt class="js-txt-vencimento">Vencimento</dt>
							<dd><input type="text" class="js-vencimento js-tipoPagamento data" value="<?= date('d/m/Y');?>" /></dd>
						</dl>

						<dl>
							<dt>Identificador</dt>
							<dd><input type="text" class="js-identificador js-tipoPagamento" /></dd>
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

						<dl>
							<dt>Valor da Parcela</dt>
							<dd><input type="text" class="js-valorCreditoDebito js-tipoPagamento" readonly style="background:#CCC" /></dd>
						</dl>

						<dl>
							<dt>Taxa (%)</dt>
							<dd><input type="text" class="js-valorCreditoDebitoTaxa js-tipoPagamento" readonly style="background:#CCC" /></dd>
						</dl>
						<dl  style="display:none">
							<dt>Valor Multa</dt>
							<dd><input type="text" class="js-valorMultas money" style="background:#CCC" disabled/></dd>
						</dl>
						<dl style="display:none">
							<dt>Valor juros</dt>
							<dd><input type="text" class="js-valorJuros money" style="background:#CCC" disabled/></dd>
						</dl>
						<dl style="display:none">
							<dt>Desconto Multas/Juros</dt>
							<dd><input type="text" class="js-descontoMultasJuros money" value="0,00"/></dd>
						</dl>
						<dl style="display:none">
							<dt>Total a ser Pago</dt>
							<dd><input type="text" class="js-TotalaPagar money" style="background:#CCC" disabled/></dd>
						</dl>
						<script type="text/javascript">
							$(function(){
								// quando seleciona a bandeira do cartao
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
								//quando selecionar a quantidade de parcelas
								$('.js-parcelas').change(function(){
									let qtdParcelas = unMoney($(this).val())
									let valorDigitado = unMoney($('.js-valor').val())
									let valorMulta =  unMoney($('.js-valorMultas').val())
									let valorJuros =  unMoney($('.js-valorJuros').val())
									let valorDesconto = unMoney($('.js-descontoMultasJuros').val())
									let valorSomado = (valorDigitado+valorMulta+valorJuros)-valorDesconto
									let valorParcelas = valorSomado/qtdParcelas
									let id_operadora = $('.js-creditoBandeira option:selected').attr('data-id_operadora')
									let id_bandeira = $('.js-creditoBandeira option:selected').val()
									if(_taxaBandeiras[id_operadora] && _taxaBandeiras[id_operadora][id_bandeira] && _taxaBandeiras[id_operadora][id_bandeira][qtdParcelas] && _taxaBandeiras[id_operadora][id_bandeira]	[qtdParcelas][qtdParcelas] && _taxaBandeiras[id_operadora][id_bandeira][qtdParcelas][qtdParcelas].taxa){
										valorTaxa = unMoney(_taxaBandeiras[id_operadora][id_bandeira][qtdParcelas][qtdParcelas].taxa)
									}
									$('.js-valorCreditoDebito').val(number_format(valorParcelas,2,",","."))
									$('.js-valorCreditoDebitoTaxa').val(number_format(valorTaxa,2))

								})
							});
						</script>
						

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
						<dd><a href="javascript:;" class="button__full button button_main js-btn-addBaixa" data-loading="0">adicionar baixa</a></dd>
					</dl>

					
				</fieldset>

				<fieldset>
					<legend>Pagamentos</legend>
					<div class="list2">
						<table class="js-baixas">
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

<section class="aside" id="js-aside-asFinanceiro-receber">
	<div class="aside__inner1" style="width:600px;">

		<header class="aside-header">
			<h1 class="js-titulo"></h1>
			<a href="javascript:;" class="aside-header__fechar aside-close"><i class="iconify" data-icon="fluent:dismiss-24-filled"></i></a>
		</header>

		<form method="post" class="aside-content form">
			<input type="hidden" class="js-id_pagamento" value="0" />



			<!-- Programacao de pagamento -->
			<div class="js-fin js-fin-programacao" style="display: ;">
				<section class="filter">
					<div class="filter-group"></div>
					<div class="filter-group">
						<div class="filter-form form">
							<?php /*<dl>
								<dd><a href="" class="button"><i class="iconify" data-icon="fluent:delete-24-regular"></i></a></dd>
							</dl>*/?>
							<dl>
								<dd><button type="button" class="button button_main js-btn-receber" data-loading="0"><i class="iconify" data-icon="fluent:checkmark-12-filled"></i> <span>Receber</span></button></dd>
							</dl>
						</div>								
					</div>
				</section>
				

				<input type="hidden" class="js-index" />
				<fieldset>
					<legend>Confirmação do Pagamento</legend>
					<div class="colunas3">
						<dl>
							<dt>Data do Pagamento</dt>
							<dd><input type="text" class="js-dataPagamento datecalendar" value="" /></dd>
						</dl>
					</div>
					<div class="colunas3">

						<dl>
							<dt>Vencimento da Parcela</dt>
							<dd><input type="text" class="js-vencimentoParcela" readonly /></dd>
						</dl>
						<dl>
							<dt>Valor da Parcela</dt>
							<dd><input type="text" class="js-valorParcela" readonly /></dd>
						</dl>
						<dl>
							<dt>Forma de Pagamento</dt>
							<dd><input type="text" class="js-formaPagamento" readonly /></dd>
						</dl>

					</div>
				</fieldset>

				<fieldset class="js-fieldset-conta">
					<legend>Conta</legend>
					<dl>
						<dd>
							<select class="js-id_banco">
								<option value="">-</option>
								<?php
								foreach($_bancos as $x) {
									//if($x->tipo=="dinheiro") {
										echo '<option value="'.$x->id.'">'.utf8_encode($x->titulo).'</option>';
									//}
								}
								?>
							</select>
						</dd>
					</dl>

					
				</fieldset>

			</div>

		</form>
	</div>
</section><!-- .aside -->

<script>
	$(function(){
		// adiciona o calendario quando selecionar a data de vencimento
		$('.js-vencimento').datetimepicker({timepicker:false,format:'d/m/Y',scrollMonth:false,scrollInput:false});
	})
</script>