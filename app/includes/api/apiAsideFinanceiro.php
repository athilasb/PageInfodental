<?php
// formas de pagamento
$_formasDePagamento = array();
$optionFormasDePagamento = '';
$sql->consult($_p . "parametros_formasdepagamento", "*", "order by titulo asc");
while ($x = mysqli_fetch_object($sql->mysqry)) {
	$_formasDePagamento[$x->id] = $x;
	$optionFormasDePagamento .= '<option value="' . $x->id . '" data-tipo="' . $x->tipo . '">' . utf8_encode($x->titulo) . '</option>';
}

// credito, debito, bandeiras
$_bandeiras = array();
$sql->consult($_p . "parametros_cartoes_bandeiras", "*", "where lixo=0");
while ($x = mysqli_fetch_object($sql->mysqry)) {
	$_bandeiras[$x->id] = $x;
}


$creditoBandeiras = array();
$debitoBandeiras = array();
$taxasBandeiras = array();
$_operadoras = array();

$sql->consult($_p . "parametros_cartoes_operadoras", "*", "where lixo=0 order by titulo");
while ($x = mysqli_fetch_object($sql->mysqry)) {
	$creditoBandeiras[$x->id] = array('titulo' => utf8_encode($x->titulo), 'bandeiras' => array());
	$debitoBandeiras[$x->id] = array('titulo' => utf8_encode($x->titulo), 'bandeiras' => array());
	$_operadoras[$x->id] = $x;
}

$sql->consult($_p . "parametros_cartoes_operadoras_bandeiras", "*", "where lixo=0");
while ($x = mysqli_fetch_object($sql->mysqry)) {
	if (!isset($_operadoras[$x->id_operadora])) continue;

	if (isset($_bandeiras[$x->id_bandeira])) {
		$bandeira = $_bandeiras[$x->id_bandeira];
		$txJson = json_decode($x->taxas);
		$taxasBandeiras[$x->id_operadora][$x->id_bandeira] = $txJson->creditoTaxas ?? [];
		if ($x->check_debito == 1) {
			$debitoTaxa = isset($txJson->debitoTaxas->taxa) ? $txJson->debitoTaxas->taxa : 0;
			$debitoDias = isset($txJson->debitoTaxas->dias) ? $txJson->debitoTaxas->dias : 0;
			$debitoBandeiras[$x->id_operadora]['bandeiras'][$x->id_bandeira] = array(
				'id_bandeira' => $x->id_bandeira,
				'titulo' => utf8_encode($bandeira->titulo),
				'taxa' => $debitoTaxa,
				'dias' => $debitoDias
			);
		}
		if ($x->check_credito == 1) {

			$creditoTaxa = isset($txJson->creditoTaxas->taxa) ? $txJson->creditoTaxas->taxa : 0;
			$creditoBandeiras[$x->id_operadora]['bandeiras'][$x->id_bandeira] = array(
				'id_bandeira' => $x->id_bandeira,
				'titulo' => utf8_encode($bandeira->titulo),
				'parcelas' => $x->credito_parcelas,
				'taxa' => $creditoTaxa,
				'semJuros' => $x->credito_parcelas_semjuros
			);
		}
	}
}

// banco e contas
$_bancos = array();
$sql->consult($_p . "financeiro_bancosecontas", "*", " WHERE lixo =0 order by titulo asc");
while ($x = mysqli_fetch_object($sql->mysqry)) $_bancos[$x->id] = $x;
?>

<script type="text/javascript">
	const _taxaBandeiras = <?= json_encode($taxasBandeiras) ?>;
	// carrega campos de complemento da forma de pagamento
	const pagamentosAtualizaCampos = (formaDePagamento) => {
		if (formaDePagamento) {
			let id_formapagamento = formaDePagamento.val();
			let obj = formaDePagamento.parent().parent().parent().parent();
			let tipo = $(obj).find('select.js-id_formapagamento option:checked').attr('data-tipo');
			$(obj).find('.js-identificador,.js-parcelas,.js-creditoBandeira,.js-debitoBandeira,.js-debitoBandeira,.js-valorCreditoDebito,.js-obs,.js-valorCreditoDebitoTaxa').parent().parent().hide();
			if (tipo == "credito") {
				$(obj).find('.js-parcelas,.js-creditoBandeira,.js-valorCreditoDebito,.js-valorCreditoDebitoTaxa,.js-identificador').parent().parent().show();
			} else if (tipo == "debito") {
				$(obj).find('.js-debitoBandeira,.js-valorCreditoDebito,.js-valorCreditoDebitoTaxa,.js-identificador').parent().parent().show();
				let valorDigitado = unMoney($('.js-valor').val())
				if (valorDigitado == undefined) {
					valorDigitado = unMoney($('.js-saldoPagar').text())
					$('.js-valor').val(number_format(valorDigitado, 2, ",", "."))
					if ($('.js-aplicar-multas-juros').prop('checked') == true) {
						$("input.js-valor").trigger("keyup");
					}
				}
				let valorMulta = unMoney($('.js-valorMultas').text())
				let valorJuros = unMoney($('.js-valorJuros').text())
				let valorDesconto = (unMoney($('.js-descontoMultasJuros').val()) != undefined) ? unMoney($('.js-descontoMultasJuros').val()) : 0
				if ($('.js-aplicar-multas-juros').prop('checked') == false) {
					valorMulta = 0
					valorJuros = 0
				}
				let valorSomado = (valorDigitado + valorMulta + valorJuros) - valorDesconto
				let valorParcelas = valorSomado
				let id_operadora = $('.js-creditoBandeira option:selected').attr('data-id_operadora')
				let id_bandeira = $('.js-creditoBandeira option:selected').val()
				valorTaxa = 0
				$('.js-valorCreditoDebito').text(`R$ ${number_format(valorParcelas, 2, ",", ".")}`)
				$('.js-valorCreditoDebitoTaxa').text(`${number_format(valorTaxa, 2)}`)
				$(obj).find('.js-valorCreditoDebitoTaxa').parent().parent().hide();
			} else {
				$(obj).find('.js-identificador').parent().parent().show();
				if (tipo == "permuta") {
					//$(obj).find('.js-obs').parent().parent().show();
				}
			}
			let index = $('.js-pagamentos .js-id_formapagamento').index(this);
		} else {
			$('#js-aside-asFinanceiro').find('.js-identificador,.js-parcelas,.js-creditoBandeira,.js-debitoBandeira,.js-debitoBandeira,.js-valorCreditoDebito,.js-obs,.js-valorCreditoDebitoTaxa').parent().parent().hide();
		}
	}
	$(function() {
		// se clicar nas abas
		$('#js-aside-asFinanceiro .js-tab a').click(function() {
			$(".js-tab a").removeClass("active");
			$(this).addClass("active");
		});

		// desfaz uniao de agrupamento de pagametnos
		$('#js-aside-asFinanceiro').on('click', '.js-desfazerUniao', function() {
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
			}, function(isConfirm) {
				if (isConfirm) {
					$.ajax({
						type: "POST",
						data: data,
						success: function(rtn) {
							if (rtn.success) {
								document.location.href = '<?php echo "$_page?$url"; ?>';
							} else if (rtn.error) {
								swal({
									title: "Erro!",
									text: rtn.error,
									html: true,
									type: "error",
									confirmButtonColor: "#424242"
								});
							} else {
								swal({
									title: "Erro!",
									text: "Algum erro ocorreu durante a baixa deste pagamento!",
									html: true,
									type: "error",
									confirmButtonColor: "#424242"
								});
							}
						},
						error: function() {
							swal({
								title: "Erro!",
								text: "Algum erro ocorreu durante a baixa deste pagamento!",
								html: true,
								type: "error",
								confirmButtonColor: "#424242"
							});
						}
					})
				} else {
					swal.close();
				}
			});
		});

		// atualiza complementos das formas de pagamento 
		$('select.js-id_formapagamento').change(function() {
			pagamentosAtualizaCampos($(this));
		});

		// se alterar o tipo de baixa
		$('input[name=tipoBaixa]').click(function() {
			if ($(this).val() == "pagamento") {
				$('.js-tipoPagamento').parent().parent().show();
				$('.js-tipoDescontoDespesa').parent().parent().hide();
				$('.js-obs').parent().parent().hide();
				$('select.js-id_formapagamento').trigger('change');
			} else {
				$('.js-tipoPagamento').parent().parent().hide();
				$('.js-tipoDescontoDespesa').parent().parent().show();
				$('.js-obs').parent().parent().show();
			}
		});

		pagamentosAtualizaCampos('');

		// se clicar em adicionar baixa
		$('.js-btn-addBaixa').click(function() {
			let obj = $(this);
			let objHTMLAntigo = obj.html();
			let loading = obj.attr('data-loading');

			if (loading == 0) {
				obj.html('<span class="iconify" data-icon="eos-icons:loading"></span>');
				obj.attr('data-loading', 1);
				let tipoPagamento = $('.js-id_formapagamento option:checked').attr('data-tipo');
				let saldoAPagar = unMoney($('.js-saldoPagar').text());
				let tipoBaixa = $('input[name=tipoBaixa]:checked').val();

				if (tipoBaixa == "despesa" || tipoBaixa == "desconto" || tipoPagamento !== undefined) {
					let dataPagamento = $('input.js-dataPagamento').val();
					let dataVencimento = $('input.js-vencimento').val();
					let valor = ($('input.js-valor').val() && $('input.js-valor').val().length > 0 && unMoney($('input.js-valor').val()) > 0) ? unMoney($('input.js-valor').val()) : '';
					let valorDesconto = ($('input.js-valorDesconto').val().length > 0 && unMoney($('input.js-valorDesconto').val()) > 0) ? unMoney($('input.js-valorDesconto').val()) : '';
					let valorJuros = ($('.js-valorJuros').text() && $('.js-valorJuros').text().length > 0 && unMoney($('.js-valorJuros').text()) > 0) ? unMoney($('.js-valorJuros').text()) : '';
					let valorMulta = ($('.js-valorMultas').text() && $('.js-valorMultas').text().length > 0 && unMoney($('.js-valorMultas').text()) > 0) ? unMoney($('.js-valorMultas').text()) : '';
					let descontoMultasJuros = ($('input.js-descontoMultasJuros').val() && $('input.js-descontoMultasJuros').val().length > 0 && unMoney($('input.js-descontoMultasJuros').val()) > 0) ? unMoney($('input.js-descontoMultasJuros').val()) : 0;
					let id_formapagamento = $('.js-id_formapagamento').val();
					let obs = $('input.js-obs').val();
					let obsDesconto = $('input.js-obs-desconto').val();
					let debitoBandeira = $('select.js-debitoBandeira').val();
					let creditoBandeira = $('select.js-creditoBandeira').val();
					let creditoParcelas = $('select.js-parcelas').val();
					let id_operadora = 0;
					let taxa = 0;
					let valorParcela = valor
					id_pagamento = $('#js-aside-asFinanceiro .js-id_pagamento').val();
					let erro = '';
					if ($('.js-aplicar-multas-juros').prop('checked') == false) {
						valorJuros = 0
						valorMulta = 0
						descontoMultasJuros = 0
					}
					if (tipoBaixa == 'pagamento') {
						if (valor.length == 0) erro = 'Defina o <b>Valor</b> a ser pago';
						else if (saldoAPagar < unMoney(valor)) erro = `O valor não pode ser maior que <b>${number_format(saldoAPagar,2,",",".")}`;
						if (tipoPagamento == 'credito') {
							if (dataVencimento.length == 0) erro = 'Defina a <b>Data do Vencimento</b>';
							else if (valor.length == 0) erro = 'Defina o <b>Valor do Pagamento</b>';
							else if (creditoBandeira.length == 0) erro = 'Selecione a <b>Bandeira</b> do Cartão de Crédito';
							else if (creditoParcelas.length == 0) erro = 'Selecione o <b>Nº de Parcelas</b> do Cartão de Crédito';
							else if (saldoAPagar < unMoney($('input.js-valor').val())) erro = `O valor não pode ser maior que <b>${number_format(saldoAPagar,2,",",".")}`;
							id_operadora = $('select.js-creditoBandeira option:selected').attr('data-id_operadora');
							taxa = $('.js-valorCreditoDebitoTaxa').text();
							valorParcela = unMoney($('.js-valorCreditoDebito').text());
							valorJuros = valorJuros / creditoParcelas
							valorMulta = valorMulta / creditoParcelas
							descontoMultasJuros = descontoMultasJuros / creditoParcelas
							valorParcela = valorParcela - (valorJuros + valorMulta - descontoMultasJuros)

						} else if (tipoPagamento == 'debito') {
							if (dataVencimento.length == 0) erro = 'Defina a <b>Data do Vencimento</b>';
							else if (valor.length == 0) erro = 'Defina o <b>Valor do Pagamento</b>';
							else if (debitoBandeira.length == 0) erro = 'Selecione a <b>Bandeira</b> do Cartão de Débito';
							else if (saldoAPagar < unMoney($('input.js-valor').val())) erro = `O valor não pode ser maior que <b>${number_format(saldoAPagar,2,",",".")}`;
							id_operadora = $('select.js-debitoBandeira option:selected').attr('data-id_operadora');
							taxa = $('input.js-valorCreditoDebitoTaxa').val();
							valorParcela = $('input.js-valorCreditoDebito').val();

						} else {
							id_operadora = 0;
							taxa = 0;
							valorParcela = $('input.js-valor').val().replace(/[^\d,-.]+/g, '');
							if (dataVencimento.length == 0) erro = 'Defina a <b>Data do Vencimento</b>';
							else if (valor.length == 0) erro = 'Defina o <b>Valor do Pagamento</b>';
						}
					} else if (tipoBaixa == 'desconto' || tipoBaixa == 'despesa') {
						if (dataPagamento.length == 0) dataPagamento = dataVencimento;
						if (valorDesconto.length == 0 || valorDesconto == '' || valorDesconto == 0) erro = 'Defina o <b>Valor</b> Para o Desconto';
						else if (obsDesconto.length == 0 || obsDesconto == '') erro = 'Digite uma <b>Observação</b>';
						obs = obsDesconto
						valor = valorDesconto
					}

					if (dataVencimento.length == 0) erro = 'Defina a <b>Data</b> de Vencimento';
					else if (tipoBaixa.length == 0) erro = 'Defina o <b>Tipo de Baixa</b>';
					else if (tipoBaixa == "pagamento" && id_formapagamento.length == 0) erro = 'Defina a <b>Forma de Pagamento</b>';
					else if (saldoAPagar <= 0) erro = `Não existe mais débitos!`;
					else if (descontoMultasJuros >= (saldoAPagar + valorJuros + valorMulta)) erro = `Voce Não Pode dar um Desconto Maior do que o Valor da Parcela!`;
					//valorParcela = (unMoney(valorParcela) + valorJuros + valorMulta) - descontoMultasJuros
					if (erro.length == 0) {
						let data = `ajax=pagamentoBaixa&tipoBaixa=${tipoBaixa}&id_pagamento=${id_pagamento}&dataPagamento=${dataPagamento}&dataVencimento=${dataVencimento}&valor=${valor}&id_formapagamento=${id_formapagamento}&debitoBandeira=${debitoBandeira}&creditoBandeira=${creditoBandeira}&creditoParcelas=${creditoParcelas}&obs=${obs}&id_operadora=${id_operadora}&taxa=${taxa}&valorParcela=${(valorParcela)}&valorJuros=${(valorJuros)}&valorMulta=${(valorMulta)}&descontoMultasJuros=${descontoMultasJuros}`;
						$.ajax({
							type: "POST",
							data: data,
							success: function(rtn) {
								console.log(rtn)
								if (rtn.success) {
									let index = pagamentos.findIndex((item, index) => {
										return item.id_parcela == id_pagamento
									})
									pagamentos[index].saldoApagar = unMoney(pagamentos[index].saldoApagar) - unMoney(valor)
									baixasAtualizar();
									$('.js-dataPagamento').val('<?= date('d/m/Y'); ?>');
									$('.js-valor').val('');
									$('.js-valorDesconto').val('');
									$('.js-id_formapagamento').val('');
									$('.js-obs').val('');
									$('.js-valorJuros').val('');
									$('.js-valorMultas').val('');
									$('.js-descontoMultasJuros').val('');
									$('.js-TotalaPagar').val('');

									$('.js-multa').hide()
									$('.js-valorCreditoDebitoTaxa').closest('dl').hide()
									$('.js-valorCreditoDebito').closest('dl').hide()
									$('.js-parcelas').closest('dl').hide()
									$('.js-creditoBandeira').closest('dl').hide()
									if (saldoAPagar <= 0) $('.js-form-pagamentos').hide();

								} else if (rtn.error) {
									swal({
										title: "Erro!",
										text: rtn.error,
										html: true,
										type: "error",
										confirmButtonColor: "#424242"
									});
								} else {
									swal({
										title: "Erro!",
										text: "Algum erro ocorreu durante a baixa deste pagamento!",
										html: true,
										type: "error",
										confirmButtonColor: "#424242"
									});
								}
							},
							error: function(error) {
								console.log(error)
								swal({
									title: "Erro!",
									text: "Algum erro ocorreu durante a baixa deste pagamento",
									html: true,
									type: "error",
									confirmButtonColor: "#424242"
								});
								obj.html(objHTMLAntigo);
								obj.attr('data-loading', 0);
							}
						}).done(function() {
							obj.html(objHTMLAntigo);
							obj.attr('data-loading', 0);
						});
					} else {
						swal({
							title: "Erro!",
							text: erro,
							html: true,
							type: "error",
							confirmButtonColor: "#424242"
						});
						obj.html(objHTMLAntigo);
						obj.attr('data-loading', 0);
					}
				} else {
					swal({
						title: "Erro!",
						text: "Define a <b>Forma de Pagamento</b>",
						html: true,
						type: "error",
						confirmButtonColor: "#424242"
					});
					obj.html(objHTMLAntigo);
					obj.attr('data-loading', 0);
				}
			}
		});

		$('input.money').maskMoney({
			symbol: '',
			allowZero: false,
			showSymbol: true,
			thousands: '.',
			decimal: ',',
			symbolStay: true
		});

		$('.aside-close').click(function() {
			if ($('[name="alteracao"]').val() == '1') {
				document.location.reload();
			} else {
				// $('#js-aside-asFinanceiro .js-index').val("");
				// $('#js-aside-asFinanceiro .js-id_pagamento').val("");
				// $('#js-aside-asFinanceiro .js-titulo').html("");
				// $('#js-aside-asFinanceiro .js-dataOriginal').html(``);
				// $('#js-aside-asFinanceiro .js-valorParcela').html(`R$ ${number_format(0, 2, ",", ".")}`);
				// $('#js-aside-asFinanceiro .js-valorDesconto').html(`R$ ${number_format(0, 2, ",", ".")}`);
				// $('#js-aside-asFinanceiro .js-valorCorrigido').html(`R$ ${number_format(0, 2, ",", ".")}`);
				// $('#js-aside-asFinanceiro .js-btn-pagamento').attr('data-id_pagamento', 0);
				// $("input").val("");
				// $("select").val("");
			}
		})
	});
</script>
<!-- ASIDE PROGRAMAÇÂO DE PAGAMENTO-->
<section class="aside aside-form" id="js-aside-asFinanceiro">
	<div class="aside__inner1">
		<input type="hidden" name="alteracao" value="0">
		<header class="aside-header">
			<h1 class="js-titulo"></h1>
			<a href="javascript:;" class="aside-header__fechar aside-close"><i class="iconify" data-icon="fluent:dismiss-24-filled"></i></a>
		</header>

		<form method="post" class="aside-content form">
			<input type="hidden" class="js-id_pagamento" value="0" />
			<input type="hidden" class="js-index" />
			<section class="tab tab_alt js-tab">
				<?php /*<a href="javascript:;" onclick="$('.js-fin').hide(); $('.js-fin-resumo').show();" class="active">Informações</a>*/ ?>
				<a href="javascript:;" onclick="$('.js-fin').hide(); $('.js-fin-programacao').show();" class="active">Programação de Pagamento</a>
				<a href="javascript:;" onclick="$('.js-fin').hide(); $('.js-fin-agrupamento').show();" class="js-tab-agrupamento">Agrupamento</a>
			</section>

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
					</div>*/ ?>
				</section>

				<fieldset style="padding:.75rem 1.5rem;">
					<div class="colunas5">
						<dl>
							<dt>Data Original</dt>
							<dd class="js-dataOriginal">12/01/2023</dd>
						</dl>
						<dl>
							<dt>Valor da Parcela</dt>
							<dd class="js-valorParcela"> R$ 0,00</dd>
						</dl>
						<dl>
							<dt>Desconto</dt>
							<dd class="js-valorDesconto" data-tipo="descontos">R$ 0,00</dd>
						</dl>
						<dl style="font-weight:bold">
							<dt>Total</dt>
							<dd><strong class="js-valorCorrigido">R$ 1,00</strong></dd>
						</dl>
						<dl style="font-weight:bold; color:var(--vermelho);">
							<dt>Saldo a Pagar</dt>
							<dd class="js-saldoPagar">R$ 0,00</dd>
						</dl>
					</div>
				</fieldset>
				<fieldset class="js-fieldset-pagamentos">
					<legend>Definir fatura</legend>
					<dl>
						<dd>
							<label><input type="radio" name="tipoBaixa" value="pagamento" checked onclick="$('.js-pagamento').show(); $('.js-desconto').hide()"> Pagamento</label>
							<label><input type="radio" name="tipoBaixa" value="desconto" onclick="$('.js-pagamento').hide(); $('.js-desconto').show();"> Desconto</label>
						</dd>
					</dl>
					<section class="js-pagamento">
						<div class="colunas4">
							<dl>
								<dt>Valor</dt>
								<dd class="form-comp"><span>R$</span>
									<input type="text" class="js-valor money" />
								</dd>
							</dl>
							<dl>
								<dt>Forma de Pagamento</dt>
								<dd><select class="js-id_formapagamento js-tipoPagamento">
										<option value=""></option>
										<?= $optionFormasDePagamento; ?>
									</select>
								</dd>
							</dl>
							<dl>
								<dt>Vencimento</dt>
								<dd><input type="text" class="js-vencimento js-tipoPagamento data" value="<?= date('d/m/Y'); ?>" /></dd>
							</dl>
							<dl>
								<dt>Obs.:</dt>
								<dd><input type="text" class="js-obs" /></dd>
							</dl>
							<dl class="dl2">
								<dt>Bandeira</dt>
								<dd>
									<select class="js-debitoBandeira js-tipoPagamento">
										<option value="">selecione</option>
										<?php
										foreach ($debitoBandeiras as $id_operadora => $x) {
											echo '<optgroup label="' . utf8_encode($x['titulo']) . '">';
											foreach ($x['bandeiras'] as $band) {
												echo '<option value="' . $band['id_bandeira'] . '" data-id_operadora="' . $id_operadora . '" data-taxa="' . $band['taxa'] . '" data-cobrarTaxa="' . $band['cobrarTaxa'] . '">' . utf8_encode($band['titulo']) . '</option>';
											}
											echo '</optgroup>';
										}
										?>
									</select>
								</dd>
							</dl>
							<dl class="dl2">
								<dt>Bandeira</dt>
								<dd>
									<select class="js-creditoBandeira js-tipoPagamento">
										<option value="">selecione</option>
										<?php
										foreach ($creditoBandeiras as $id_operadora => $x) {
											echo '<optgroup label="' . utf8_encode($x['titulo']) . '">';
											foreach ($x['bandeiras'] as $band) {
												echo '<option value="' . $band['id_bandeira'] . '" data-id_operadora="' . $id_operadora . '" data-semjuros="' . $band['semJuros'] . '" data-parcelas="' . $band['parcelas'] . '" data-taxa="' . $band['taxa'] . '">' . utf8_encode($band['titulo']) . '</option>';
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
								<dd><label class="js-valorCreditoDebito js-tipoPagamento">R$ 0,00</label></dd>
							</dl>

							<dl>
								<dt>Taxa (%)</dt>
								<dd><label class="js-valorCreditoDebitoTaxa js-tipoPagamento">R$ 0,00</label></dd>
							</dl>

						</div>
						<dl>
							<dd><label><input type="checkbox" name="juros" class="input-switch  js-aplicar-multas-juros" onclick="//$('.js-multa').toggle();" checked />Aplicar juros e multas</label></dd>
						</dl>
						<div class="js-multa" style="display:none;">
							<div class="colunas4">
								<dl>
									<dt>Multa</dt>
									<dd><label class="js-valorMultas money">R$ 0,0</label></dd>
								</dl>
								<dl>
									<dt>Juros</dt>
									<dd><label class="js-valorJuros money">R$ 0,00</label></dd>
								</dl>
								<dl>
									<dt>Desconto Juros</dt>
									<dd class="form-comp"><span>R$</span><input type="text" class="js-descontoMultasJuros money" style="width:100px;" value="0,00" /></dd>
								</dl>
								<dl style="font-weight:bold">
									<dt>Total</dt>
									<dd><label class="js-TotalaPagar money">R$ 0,00</label></dd>
								</dl>
							</div>
						</div>
					</section>
					<section class="js-desconto">
						<div class="colunas4">
							<dl>
								<dt>Valor</dt>
								<dd class="form-comp"><span>R$</span><input type="text" class="js-valorDesconto money" /></dd>
							</dl>
							<dl class="dl3">
								<dt>Observações</dt>
								<dd><input type="text" class="js-obs-desconto" /></dd>
							</dl>
						</div>
					</section>
					<dl style="margin-top:1.5rem;">
						<dd><button href="javascript:;" class="button button_main js-btn-addBaixa" type="button" data-loading="0"><i class="iconify" data-icon="fluent:add-circle-24-regular"></i><span>Adicionar</span></button></dd>
					</dl>
				</fieldset>

				<fieldset>
					<legend>FATURAS</legend>
					<div class="list2 list2_sm">
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
</section>
<!--ASIDE CONFIRMACAO RECEBER-->
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
							</dl>*/ ?>
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
								foreach ($_bancos as $x) {
									//if($x->tipo=="dinheiro") {
									echo '<option value="' . $x->id . '">' . utf8_encode($x->titulo) . '</option>';
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
</section>

<script type="text/javascript">
	$(function() {
		// adiciona o calendario quando selecionar a data de vencimento
		$('.js-vencimento').datetimepicker({
			timepicker: false,
			format: 'd/m/Y',
			scrollMonth: false,
			scrollInput: false
		});
		// quando seleciona a bandeira do cartao
		$('.js-creditoBandeira').change(function() {
			$('select.js-parcelas option').remove();
			if ($(this).val().length > 0) {
				let parcelas = eval($(this).find('option:checked').attr('data-parcelas'));
				//alert(parcelas);
				if ($.isNumeric(parcelas)) {
					$('select.js-parcelas').append(`<option value="">-</option>`);
					for (var i = 1; i <= parcelas; i++) {
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
		$('.js-parcelas').change(function() {
			let qtdParcelas = unMoney($(this).val())
			let valorDigitado = unMoney($('.js-valor').val())
			if (valorDigitado == undefined) {
				valorDigitado = unMoney($('.js-saldoPagar').text())
				$('.js-valor').val(number_format(valorDigitado, 2, ",", "."))
				if ($('.js-aplicar-multas-juros').prop('checked') == true) {
					$("input.js-valor").trigger("keyup");
				}
			}
			let valorMulta = unMoney($('.js-valorMultas').text())
			let valorJuros = unMoney($('.js-valorJuros').text())
			let valorDesconto = (unMoney($('.js-descontoMultasJuros').val()) != undefined) ? unMoney($('.js-descontoMultasJuros').val()) : 0
			if ($('.js-aplicar-multas-juros').prop('checked') == false) {
				valorMulta = 0
				valorJuros = 0
			}
			let valorSomado = (valorDigitado + valorMulta + valorJuros) - valorDesconto
			let valorParcelas = valorSomado / qtdParcelas

			let id_operadora = $('.js-creditoBandeira option:selected').attr('data-id_operadora')
			let id_bandeira = $('.js-creditoBandeira option:selected').val()
			if (_taxaBandeiras[id_operadora] && _taxaBandeiras[id_operadora][id_bandeira] && _taxaBandeiras[id_operadora][id_bandeira][qtdParcelas] && _taxaBandeiras[id_operadora][id_bandeira][qtdParcelas][qtdParcelas] && _taxaBandeiras[id_operadora][id_bandeira][qtdParcelas][qtdParcelas].taxa) {
				valorTaxa = unMoney(_taxaBandeiras[id_operadora][id_bandeira][qtdParcelas][qtdParcelas].taxa)
			}
			$('.js-valorCreditoDebito').text(`R$ ${number_format(valorParcelas, 2, ",", ".")}`)
			$('.js-valorCreditoDebitoTaxa').text(`${number_format(valorTaxa, 2)}`)

		})
	});
</script>