<?php
if (isset($_POST['ajax'])) {

	$dir = "../../";
	require_once("../../lib/conf.php");
	require_once("../../usuarios/checa.php");

	$rtn = array();

	if ($_POST['ajax'] == "planos") {
		$planos = array();
		if (isset($_POST['id_procedimento']) and is_numeric($_POST['id_procedimento'])) {
			$sql->consult($_p . "parametros_procedimentos", "*", "where id='" . addslashes($_POST['id_procedimento']) . "' and lixo=0 and pub=1");
			if ($sql->rows) {
				$procedimento = mysqli_fetch_object($sql->mysqry);
			}
		}

		if (is_object($procedimento)) {
			$sql->consult($_p . "parametros_procedimentos_planos", "*", "where id_procedimento=$procedimento->id");

			$planosID = array();
			$procedimentoPlano = array();
			while ($x = mysqli_fetch_object($sql->mysqry)) {
				$procedimentoPlano[$x->id_plano] = $x;
				$planosID[] = $x->id_plano;
			}


			if (count($planosID)) {
				$sql->consult($_p . "parametros_planos", "*", "where id IN (" . implode(",", $planosID) . ") and lixo=0");
				if ($sql->rows) {
					while ($x = mysqli_fetch_object($sql->mysqry)) {
						if (isset($procedimentoPlano[$x->id])) {
							$procP = $procedimentoPlano[$x->id];
							$planos[] = array('id' => $x->id, 'titulo' => utf8_encode($x->titulo), 'valor' => $procP->valor);
						}
					}
				}
			}

			$rtn = array('success' => true, 'planos' => $planos);
		} else {
			$rtn = array('success' => false, 'error' => 'Procedimento não encontrado!');
		}
	}

	header("Content-type: application/json");
	echo json_encode($rtn);
	die();
}
?>
<script type="text/javascript">
	var facesInfos = JSON.parse(`<?php echo json_encode($_regioesInfos); ?>`);
	var id_usuario = '<?php echo $usr->id; ?>';
	var autor = '<?php echo utf8_encode($usr->nome); ?>';

	const updateValorText = (extra = 0) => {
		$('.js-valorTotalOriginal').text("");
		$('.js-valorTotal').text(number_format(((valorOriginalProcedimentos + extra) - valorDescontos), 2, ",", "."));
		valorTotalProcedimentos = valorOriginalProcedimentos;
		if (valorDescontos > 0) {
			$('.js-valorTotalOriginal').text(number_format((valorOriginalProcedimentos), 2, ",", "."));
		}
	}

	const atualizaValor = (atualizarParcelas) => {
		if (contrato.status == 'REPROVADO' || contrato.status == 'CANCELADO') {
			return;
		}
		valorTotal = 0;
		valorOriginalProcedimentos = 0;
		let cont = 1;
		let descontos = 0
		procedimentos.forEach(x => {
			if (x.situacao != 'naoAprovado') {
				valorProcedimento = x.valor;
				hof = x.hof > 0 ? x.hof : 1;
				if (x.quantitativo == 1) valorProcedimento *= x.quantidade;
				if (x.face == 1) valorProcedimento *= x.faces.length;
				if (x.id_regiao == 5) valorProcedimento *= hof;
				if (x.desconto > 0) {
					//valorProcedimento=eval(valorProcedimento-x.desconto);
					descontos += x.desconto
				} else {
					valorProcedimento = eval(valorProcedimento);
				}
				valorTotal += valorProcedimento;
			}

			cont++;
		});
		valorOriginalProcedimentos = valorTotal
		valorDescontos = descontos
		for (let x in _politicas) {
			let politica = _politicas[x]
			if ((politica.tipo_politica == 'intervalo' && (valorOriginalProcedimentos - descontos) >= parseFloat(politica.de) && (valorOriginalProcedimentos - descontos) <= parseFloat(politica.ate)) || (politica.tipo_politica == 'acima' && (valorOriginalProcedimentos - descontos) >= parseFloat(politica.de))) {
				temPolitica = politica
				if (temPolitica.parcelasParametros) {
					temPolitica.parcelasParametros = (typeof(temPolitica.parcelasParametros) !== "object") ? JSON.parse(temPolitica.parcelasParametros) : temPolitica.parcelasParametros
				}
				break
			} else {
				$('.js-tipo-manual').show();
				$('.js-tipo-politica').hide();
				$('.js-tipo-politica table').html("")
				$('[name="tipo_financeiro').filter("[value='politica']").prop("disabled", false);
				temPolitica = false
			}
		}

		//$('.js-valorTotal').text(number_format(valorTotal,2,",","."));
		let parcelas = [];
		if (atualizarParcelas === true) {
			let numeroParcelas = $('.js-pagamentos-quantidade').val();
			if (numeroParcelas && numeroParcelas <= 0) numeroParcelas = 0;
			valorParcela = ((valorTotalProcedimentos - valorDescontos) / numeroParcelas);
			valorParcela = valorParcela;
			let startDate = new Date();
			if ($('.js-vencimento:eq(0)').val() != undefined) {
				aux = $('.js-vencimento:eq(0)').val().split('/');
				startDate = new Date(); //`${aux[2]}-${aux[1]}-${aux[0]}`);
				startDate.setDate(aux[0]);
				startDate.setMonth(eval(aux[1]) - 1);
				startDate.setFullYear(aux[2]);
			}
			for (var i = 1; i <= numeroParcelas; i++) {
				let item = {};


				let mes = startDate.getMonth() + 1;
				mes = mes <= 9 ? `0${mes}` : mes;

				let dia = startDate.getDate();
				dia = dia <= 9 ? `0${dia}` : dia;
				item.vencimento = `${dia}/${mes}/${startDate.getFullYear()}`;
				item.valor = valorParcela;

				parcelas.push(item);

				newDate = startDate;
				newDate.setMonth(newDate.getMonth() + 1);

				startDate = newDate;

				if (i == numeroParcelas) {
					pagamentos = parcelas;
					$('#js-textarea-pagamentos').text(JSON.stringify(pagamentos))
				}
			}
		}
		updateValorText();
		pagamentosListar();
		AtualizaPolitica();
	}

	const pagamentosListar = (passo = 0) => {
		$('.js-listar-parcelas .fpag').html('');
		$('.js-listar-parcelas').show();
		if (contrato.status == 'REPROVADO' || contrato.status == 'CANCELADO') {
			return;
		}
		if (pagamentos.length > 0) {
			let index = 1;
			let metodosPagamentosAceito = '<?php echo $optionFormasDePagamento; ?>';
			let disabledData = ''
			let disabledValor = ''
			let disabledForma = ''
			let disabledBandeira = ''
			let disabledParcelas = ''
			let disabledIdent = ''
			if (contrato.status == 'APROVADO') {
				disabledData = 'disabled'
				disabledValor = 'disabled'
				disabledForma = 'disabled'
				disabledBandeira = 'disabled'
				disabledParcelas = 'disabled'
				disabledIdent = 'disabled'
			}
			pagamentos.forEach(x => {
				$('.js-listar-parcelas .fpag').append(`<div class="fpag-item js-pagamento-item">
												<aside>${index++}</aside>
												<article>
													<div class="colunas3">
														<dl>
															<dd class="form-comp"><span><i class="iconify" data-icon="fluent:calendar-ltr-24-regular"></i></span><input type="tel" name="" class="data js-vencimento" data-ordem="${index}" value="${x.vencimento}" ${disabledData}/></dd>
														</dl>
														<dl>
															<dd class="form-comp"><span>R$</i></span><input type="tel" name="" data-ordem="${index}" class="valor js-valor" value="${number_format(x.valor,2,",",".")}"  ${disabledValor}/></dd>
														</dl>
														<dl>
															<dd>
																<select class="js-id_formadepagamento js-tipoPagamento" ${disabledForma}>
																<option value="">Forma de Pagamento...</option>
																	${metodosPagamentosAceito}
																</select>
															</dd>
														</dl>
													</div>
														
													<div class="colunas3">
														<dl style="display:none">
															<dt>Bandeira</dt>
															<dd>
															<select class="js-debitoBandeira js-tipoPagamento" ${disabledBandeira}>
																<option value="">selecione</option>
																<?php
																foreach ($debitoBandeiras as $id_operadora => $x) {
																	echo '<optgroup label="' . utf8_encode($x['titulo']) . '">';
																	foreach ($x['bandeiras'] as $band) {
																		echo '<option value="' . $band['id_bandeira'] . '" data-id_operadora="' . $id_operadora . '">' . utf8_encode($band['titulo']) . '</option>';
																	}
																	echo '</optgroup>';
																}
																?>
															</select>
														</dd></dl>
														<dl style="display:none">
															<dt>Bandeira</dt>
															<dd>
																<select class="js-creditoBandeira js-tipoPagamento" ${disabledBandeira}>
																	<option value="">selecione</option>
																	<?php
																	foreach ($creditoBandeiras as $id_operadora => $x) {
																		echo '<optgroup label="' . utf8_encode($x['titulo']) . '">';
																		foreach ($x['bandeiras'] as $band) {

																			echo '<option value="' . $band['id_bandeira'] . '" data-parcelas="' . $band['parcelas'] . '" data-parcelas-semjuros="' . $band['semJuros'] . '" data-id_operadora="' . $id_operadora . '" data-id_operadorabandeira="' . ($id_operadora . $band['id_bandeira']) . '">' . utf8_encode($band['titulo']) . '</option>';
																		}
																		echo '</optgroup>';
																	}
																	?>
																</select>
															</dd>
														</dl>

														<dl style="display:none">
															<dt>Qtd. Parcelas</dt>
															<dd>
																<select class="js-parcelas js-tipoPagamento" ${disabledParcelas}>
																	<option value="">selecione a as Parcelas</option>
																</select>
															</dd>
														</dl>

														<dl style="display:none" ${disabledIdent}>
															<dt>Identificador</dt>
															<dd><input type="text" class="js-identificador js-tipoPagamento" /></dd>
														</dl>
														<dl style="display:none" disabled>
															<dd><input type="hidden" class="js-metodo-selecionado js-tipoPagamento" /></dd>
														</dl>

													</div>
												</article>
											</div>`);

				$('.js-pagamento-item .js-vencimento:last').inputmask('99/99/9999');
				$('.js-pagamento-item .js-vencimento:last').datetimepicker({
					timepicker: false,
					format: 'd/m/Y',
					scrollMonth: false,
					scrollTime: false,
					scrollInput: false
				});
				$('.js-pagamento-item .js-valor:last').maskMoney({
					symbol: '',
					allowZero: true,
					showSymbol: true,
					thousands: '.',
					decimal: ',',
					symbolStay: true
				});
				if (x.id_formapagamento) {
					$('.js-pagamento-item .js-id_formadepagamento:last').val(x.id_formapagamento);
					$('.js-pagamento-item .js-identificador:last').val(x.identificador);
					let tipo = $('.js-pagamento-item .js-id_formadepagamento:last option:selected').attr('data-tipo');

					if (tipo == "credito") {
						parcelaProv = x.qtdParcelas;
						$('.js-pagamento-item .js-creditoBandeira:last').find(`option[data-id_operadorabandeira=${x.id_operadora}${x.creditoBandeira}]`).prop('selected', true);
						creditoBandeiraAtualizaParcelas($('.js-pagamento-item .js-creditoBandeira:last'), x.qtdParcelas);
					} else if (tipo == "debito") {
						$('.js-pagamento-item .js-debitoBandeira:last').val(x.debitoBandeira);
					}
					pagamentosAtualizaCampos($('.js-pagamento-item .js-id_formadepagamento:last'), false);
				}
			});
			if (pagamentos.length == 1) {
				$('.js-pagamento-item .js-valor:last').prop('disabled', true);
			}
		} else {
			$('.js-listar-parcelas').hide();
			if (temPolitica && tipoFinaneiroPadrao == 'politica') {
				$('[name="tipo_financeiro"]:eq(0)').prop('checked', true);
				$('.js-tipo-politica').show()
				$('.js-tipo-manual').hide()
			} else {
				$('[name="tipo_financeiro"]:eq(1)').prop('checked', true);
				$('.js-tipo-politica').hide()
				$('.js-tipo-manual').show()
			}
		}
	}

	const procedimentosListar = () => {
		$('#js-table-procedimentos').html('');
		valorOriginalProcedimentos = 0
		valorDescontos = 0
		let cont = 1;
		if (procedimentos.length > 0) {
			let valor = ""
			procedimentos.forEach(x => {
				procedimentoValor = x.valor;
				valorOriginalProcedimentos += procedimentoValor
				valorDescontos += x.desconto
				if (x.quantitativo == 1) procedimentoValor *= x.quantidade;
				if (x.face == 1) procedimentoValor *= x.faces.length;
				if (x.id_regiao == 5) procedimentoValor *= eval(x.hof > 0 ? x.hof : 1);
				if (x.desconto > 0) {
					procedimentoValorComDesconto = procedimentoValor - x.desconto;
					valor = `<strike>${number_format(procedimentoValor,2,",",".")}</strike><br />${number_format(procedimentoValorComDesconto,2,",",".")}`;
				} else {
					valor = number_format(procedimentoValor, 2, ",", ".");
				}
				let opcao = '';

				if (x.id_regiao == 4) {
					opcaoAux = '';
					if (x.face == 1) {
						let cont = 1;
						x.faces.forEach(fId => {
							opcaoAux += facesInfos[fId].abreviacao + ', ';
							if (cont == x.faces.length) {
								opcaoAux = opcaoAux.substr(0, opcaoAux.length - 2);
							}
							cont++;
						})
					}
					opcao = `<i class="iconify" data-icon="mdi:tooth-outline"></i> ${x.opcao}<br />${opcaoAux}`;
				} else if (x.id_regiao == 5) {

					if (x.hof > 0) {
						opcao = `${x.hof} unidade(s)`;
					} else if (x.quantidade > 0) {
						opcao = `${x.quantidade} unidade(s)`;
					}
				} else {
					if (x.quantitativo == 1) {
						opcao = `Qtd. ${x.quantidade}`;
					} else {
						opcao = x.opcao;
					}
				}
				let reprovadoCss = '';
				if (x.situacao == "naoAprovado") reprovadoCss = ` style="opacity:0.3"`;

				let tr = `<tr class="js-tr-item"${reprovadoCss}>								
							<td>
								<h1>${x.procedimento}</h1>
								<p>${x.plano}</p>
							</td>
							<td><div class="list1__icon">${opcao}</td>
							<td style="text-align:right;">${valor}</td>
						</tr>`;


				$('#js-table-procedimentos').append(tr);
				if (cont == procedimentos.length) {
					atualizaValor(false);
				}
				cont++;

			});
			$('input[name=pagamento]').prop('disabled', false);
		} else {
			$('input[name=pagamento]').prop('disabled', true);
		}

		$('#js-textarea-procedimentos').val(JSON.stringify(procedimentos));
		$('.aside-plano-procedimento-adicionar .js-fieldset-adicionar').find('select').val('').trigger('chosen:updated').trigger('change');
		$('.aside-plano-procedimento-adicionar .js-fieldset-faces .js-faces').remove();
		$('.aside-plano-procedimento-adicionar .js-fieldset-hof .js-hofs').remove();
		$('.aside-plano-procedimento-adicionar .js-fieldset-hof').hide();
		$('.aside-plano-procedimento-adicionar .js-fieldset-adicionar').find('textarea,input').val('');
		$('.aside-plano-procedimento-adicionar .aside-close').click();
	}

	const procedimentoEditar = (index) => {
		if (procedimentos[index]) {
			pEd = procedimentos[index];

			valorTabela = pEd.valor;
			regiao = pEd.opcao;

			if (pEd.faces && pEd.faces.length > 0) {
				regiaoAux = '';
				cont = 1;
				pEd.faces.forEach(idF => {
					regiaoAux += facesInfos[idF].titulo + ', ';
					if (cont == pEd.faces.length) {
						regiaoAux = regiaoAux.substr(0, regiaoAux.length - 2) + '.';
						regiao += `: ${regiaoAux}`;
					}
					cont++;
				})
			} else if (pEd.id_regiao == 5) {
				regiao += `: ${pEd.hof} unidade(s)`;
			}

			if (pEd.quantitativo == 1) valorTabela *= pEd.quantidade;
			else if (pEd.face == 1) valorTabela *= pEd.faces.length;
			else if (pEd.id_regiao == 5) valorTabela *= pEd.hof;

			$('.aside-plano-procedimento-editar .js-asidePlanoEditar-index').val(index);
			$('.aside-plano-procedimento-editar .js-asidePlanoEditar-valorTabela').val(number_format(valorTabela, 2, ",", "."));
			$('.aside-plano-procedimento-editar .js-asidePlanoEditar-valorDesconto').val(number_format(pEd.desconto, 2, ",", "."));
			$('.aside-plano-procedimento-editar .js-asidePlanoEditar-valorCorrigido').val(number_format(pEd.valorCorrigido, 2, ",", "."));
			$('.aside-plano-procedimento-editar .js-asidePlanoEditar-valorUnitario').val(number_format(pEd.valor, 2, ",", "."));
			$('.aside-plano-procedimento-editar .js-asidePlanoEditar-obs').val(pEd.obs);
			$('.aside-plano-procedimento-editar .js-asidePlanoEditar-procedimento').val(pEd.procedimento);
			$('.aside-plano-procedimento-editar .js-asidePlanoEditar-situacao').val(pEd.situacao);
			$('.aside-plano-procedimento-editar .js-asidePlanoEditar-plano').val(pEd.plano);
			$('.aside-plano-procedimento-editar .js-asidePlanoEditar-regiao').val(regiao);
			$('.aside-plano-procedimento-editar .js-asidePlanoEditar-quantidade').val(pEd.quantidade);
			$('.aside-plano-procedimento-editar .js-asidePlanoEditar-data').html(pEd.data);
			$('.aside-plano-procedimento-editar .js-asidePlanoEditar-usuario').html(pEd.autor);

			if (pEd.quantitativo == 1) {
				$('.aside-plano-procedimento-editar .js-asidePlanoEditar-quantidade').parent().parent().parent().show();
				$('.aside-plano-procedimento-editar .js-asidePlanoEditar-regiao').parent().parent().hide();
			} else if (pEd.opcao.length > 0) {
				$('.aside-plano-procedimento-editar .js-asidePlanoEditar-quantidade').parent().parent().parent().hide();
				$('.aside-plano-procedimento-editar .js-asidePlanoEditar-regiao').parent().parent().show();
			} else {
				$('.aside-plano-procedimento-editar .js-asidePlanoEditar-quantidade').parent().parent().parent().hide();
				$('.aside-plano-procedimento-editar .js-asidePlanoEditar-regiao').parent().parent().hide();
			}


			if (tratamentoAprovado == 1) {
				$('.js-salvarEditarProcedimento').hide();
				$('.js-removerProcedimento').hide();
				$('.aside-plano-procedimento-editar input,.aside-plano-procedimento-editar select,.aside-plano-procedimento-editar textarea').prop('disabled', true);
			} else {
				$('.js-salvarEditarProcedimento').show();
				$('.js-removerProcedimento').show();
				$('.aside-plano-procedimento-editar input,.aside-plano-procedimento-editar select,.aside-plano-procedimento-editar textarea').prop('disabled', false);
			}

			$(".aside-plano-procedimento-editar").fadeIn(100, function() {
				$(".aside-plano-procedimento-editar .aside__inner1").addClass("active");
			});

		}
	}

	// atualiza valores dos campos do box desconto
	const descontoAtualizar = () => {
		let cont = 0;
		let totalProcedimentos = 0;
		let totalDescontoAplicado = 0;
		$('#js-descontos-table-procedimentos .js-desconto-procedimento').each(function(ind, el) {
			let index = $(el).attr('data-index');
			if ($(el).prop('checked') === true) {
				valorProcedimento = eval(procedimentos[index].valor);
				// verifica se possui faces
				if (procedimentos[index].face == 1) {
					valorProcedimento *= procedimentos[index].faces.length;
				}
				// verifica se id_regiao = 5 (hof)
				if (procedimentos[index].id_regiao == 5) {
					valorProcedimento *= eval(procedimentos[index].hof);
				}
				if (eval(procedimentos[index].quantitativo) == 1) {
					totalProcedimentos += (valorProcedimento * procedimentos[index].quantidade);
				} else {
					totalProcedimentos += valorProcedimento;
				}
				totalDescontoAplicado += eval(procedimentos[index].desconto);
			}

			cont++;

			if (cont == procedimentos.length) {

			}
		});

		$('.aside-plano-desconto .js-total-procedimentos').val(number_format(totalProcedimentos, 2, ",", "."));
		$('.aside-plano-desconto .js-total-descontosAplicados').val(number_format(totalDescontoAplicado, 2, ",", "."));

		let valor = 0;
		if ($('.aside-plano-desconto .js-input-desconto').val().length > 0) {
			if ($('.aside-plano-desconto .js-select-tipoDesconto').val() == "dinheiro") {
				valor = unMoney($('.aside-plano-desconto .js-input-desconto').val());
				$('.aside-plano-desconto .js-total-descontos').val(number_format(valor, 2, ",", "."));
			} else {

				valor = unMoney($('.aside-plano-desconto .js-input-desconto').val().replace(".", ","));

				if (valor > 100) {
					valor = 100;
					$('.aside-plano-desconto .js-input-desconto').val('100')
				}

				valor = totalProcedimentos * (valor / 100);
				valor = valor.toFixed(2);

				$('.aside-plano-desconto .js-total-descontos').val(number_format(valor, 2, ",", "."));
			}
		}
		$('.aside-plano-desconto .js-total-procedimentosdescontos').val(number_format(totalProcedimentos - totalDescontoAplicado, 2, ",", "."));
	}

	// lista procedimentos no box desconto
	const descontoListarProcedimentos = (checked) => {
		let totalDesconto = 0;
		let cont = 1;
		$('#js-descontos-table-procedimentos').html('');
		procedimentos.forEach(x => {
			if (x.situacao == "aprovado") {
				opcao = '';
				if (x.opcao.length > 0) opcao = x.opcao + ' - ';


				let valorHTML = '';

				let valorProcedimento = x.valor;
				if (x.quantitativo == 1) valorProcedimento *= x.quantidade;
				if (x.face == 1) valorProcedimento *= x.faces.length;
				if (x.id_regiao == 5) valorProcedimento *= eval(x.hof);

				if (x.desconto > 0) {
					totalDesconto += x.desconto;
					valorProcedimentoComDesconto = valorProcedimento - x.desconto;
					valorHTML = `<strike>${number_format(valorProcedimento,2,",",".")}</strike><br />${number_format(valorProcedimentoComDesconto,2,",",".")}`
				} else {
					valorHTML = number_format(valorProcedimento, 2, ",", ".");
				}

				let iniciarChecado = checked == 1 ? ' checked' : '';
				if (x.desconto > 0) iniciarChecado = ' checked';
				let tr = `<tr class="js-tr-item">			
								<td style="width:25px;">
									<label><input type="checkbox" class="js-desconto-procedimento" data-index="${(cont-1)}"${iniciarChecado} /></label>
								</td>					
								<td>
									<h1>${x.procedimento}</h1>
									<p>${opcao}${x.plano}</p>
								</td>
								<td style="text-align:right;">
									${valorHTML}
								</td>
							</tr>`;


				$('#js-descontos-table-procedimentos').append(tr);
			}

			if (cont == procedimentos.length) {
				descontoAtualizar();
			}
			cont++;
		});
	}

	// atualiza os campos de pagamento (ex.: quando credito exibe bandeira etc..)
	const pagamentosAtualizaCampos = (formaDePagamento, atualizaObjetoPagamento) => {
		let id_formadepagamento = formaDePagamento.val();
		let obj = formaDePagamento.parent().parent().parent().parent();
		let tipo = $(obj).find('select.js-id_formadepagamento option:checked').attr('data-tipo');
		$(obj).find('.js-metodo-selecionado').val(tipo);
		$(obj).find('.js-identificador,.js-parcelas,.js-creditoBandeira,.js-debitoBandeira,.js-debitoBandeira,.js-valorCreditoDebito,.js-obs,.js-valorCreditoDebitoTaxa').parent().parent().hide();

		if (id_formadepagamento == "2") {
			$(obj).find('.js-listar-parcelas,.js-creditoBandeira,.js-valorCreditoDebito,.js-valorCreditoDebitoTaxa,.js-identificador').parent().parent().show();
		} else if (id_formadepagamento == "3") {
			$(obj).find('.js-debitoBandeira,.js-valorCreditoDebito,.js-valorCreditoDebitoTaxa,.js-identificador').parent().parent().show();
		} else {
			$(obj).find('.js-identificador').parent().parent().show();
			if (id_formadepagamento == "permuta") {
				//$(obj).find('.js-obs').parent().parent().show();
			}
		}
		let index = $('.js-pagamentos .js-id_formadepagamento').index(this);
		if (atualizaObjetoPagamento === true) {
			pagamentosPersistirObjeto();
		}
	}

	const pagamentosPersistirObjeto = () => {
		console.log('salvando...');
		parcelas = [];
		$('.js-pagamento-item').each(function(index, el) {
			let vencimento = $(el).find('.js-vencimento').val();
			let valor = eval(unMoney($(el).find('.js-valor').val()));
			let id_formapagamento = $(el).find('.js-id_formadepagamento').val();
			let identificador = $(el).find('.js-identificador').val();
			let metodo = $(el).find('.js-metodo-selecionado').val();

			let item = {
				vencimento,
				valor,
				id_formapagamento,
				identificador,
				metodo,
				qtdParcelas: 1
			};

			// se credito
			if (id_formapagamento == 2) {
				let creditoBandeira = $(el).find('.js-creditoBandeira option:selected').val();
				let id_operadora = $(el).find('.js-creditoBandeira option:selected').attr('data-id_operadora');
				let parcelas = $(el).find('.js-parcelas').val();;

				item.creditoBandeira = creditoBandeira;
				item.qtdParcelas = parcelas;
				item.id_operadora = id_operadora;
			}
			// se debito
			else if (id_formapagamento == 3) {
				debitoBandeira = $(el).find('.js-debitoBandeira').val();
				let id_operadora = $(el).find('.js-debitoBandeira option:selected').attr('data-id_operadora');
				item.qtdParcelas = 1;
				item.debitoBandeira = debitoBandeira;
				item.id_operadora = id_operadora;
			}
			parcelas.push(item);
		});

		pagamentos = parcelas;
		$('textarea#js-textarea-pagamentos').val(JSON.stringify(pagamentos));
		//pagamentosListar();
	}

	const creditoBandeiraAtualizaParcelas = (selectCreditoBandeira, qtdParcelasSelecionar) => {
		let obj = $(selectCreditoBandeira).parent().parent().parent().parent();

		if ($(selectCreditoBandeira).val().length > 0) {
			let semJuros = eval($(selectCreditoBandeira).find('option:checked').attr('data-parcelas-semjuros'));
			let parcelas = eval($(selectCreditoBandeira).find('option:checked').attr('data-parcelas'));
			$(obj).find('.js-parcelas').html("")
			if ($.isNumeric(parcelas)) {
				$(obj).find('.js-parcelas').append(`<option value="">-</option>`);
				for (var i = 1; i <= parcelas; i++) {
					semjuros = '';
					if ($.isNumeric(semJuros) && semJuros >= i) semjuros = ` - sem juros`;
					else sel = '';

					if (i == qtdParcelasSelecionar) sel = ' selected';
					else sel = '';

					$(obj).find('select.js-parcelas').append(`<option value="${i}"${sel}>${i}x${semjuros}</option>`);
				}
			} else {
				$(obj).find('.js-parcelas').append(`<option value="">erro</option>`);
			}
		} else {
			$(obj).find('.js-parcelas').append(`<option value="">selecione a bandeira</option>`);
		}

		$(obj).find('.js-parcelas').closest('dl').show()
		if ($('[name="tipo_financeiro"]:checked').val() == 'manual') {
			$(obj).find('.js-parcelas').attr('disabled', false)
		}
	}

	$(function() {
		procedimentos = JSON.parse($('textarea#js-textarea-procedimentos').val());
		procedimentosListar();
		// verificar a forma de Pagamento ja Pré salva 
		verificaSeExisteParcelasSalvas();

		$('.js-pagamentos').on('change', '.js-vencimento:eq(0)', function() {
			let pagamento = $('input[name=pagamento]:checked').val();

			if (pagamento != "avista") {
				$('.js-pagamentos-quantidade').show();
				let numeroParcelas = $('.js-pagamentos-quantidade').val();

				if (numeroParcelas.length == 0 || numeroParcelas <= 0) numeroParcelas = 2;

				valorParcela = valorTotal / numeroParcelas;

				valorParcela = valorParcela.toFixed(2);

				let startDate = new Date();

				if ($('.js-vencimento:eq(0)').val() != undefined) {
					aux = $('.js-vencimento:eq(0)').val().split('/');
					startDate = new Date(); //`${aux[2]}-${aux[1]}-${aux[0]}`);
					startDate.setDate(aux[0]);
					startDate.setMonth(eval(aux[1]) - 1);
					startDate.setFullYear(aux[2]);
				}
				for (var i = 1; i <= numeroParcelas; i++) {
					let mes = startDate.getMonth() + 1;
					mes = mes <= 9 ? `0${mes}` : mes;
					let dia = startDate.getDate();
					dia = dia <= 9 ? `0${dia}` : dia;
					pagamentos[i - 1].vencimento = `${dia}/${mes}/${startDate.getFullYear()}`;
					newDate = startDate;
					newDate.setMonth(newDate.getMonth() + 1);

					startDate = newDate;

					if (i == numeroParcelas) {
						pagamentosListar();
					}
				}

			}
		});

		// remove descontos
		$('.aside-plano-desconto .js-btn-removerDesconto').click(function() {
			pagamentos = [];
			$('[name="pagamentos"]').html("");
			$('.js-pagamentos-quantidade').val("0")
			let cont = 0;
			procedimentos.forEach(x => {
				procedimentos[cont].desconto = 0;
				procedimentos[cont].valorCorrigido = procedimentos[cont].valor;
				cont++;
			});
			descontoListarProcedimentos(1);
			procedimentosListar();
			atualizaValor(true);
		});

		// clica no botao de aplicar desconto na janela de desconto
		$('.aside-plano-desconto .js-btn-aplicarDesconto').click(function() {
			pagamentos = [];
			$('[name="pagamentos"]').html("");
			$('.js-pagamentos-quantidade').val("0")
			let tipoDesconto = $('.aside-plano-desconto .js-select-tipoDesconto').val();
			let quantidadeDesconto = $('.aside-plano-desconto .js-desconto-procedimento:checked').length;
			let desconto = unMoney($(`.aside-plano-desconto .js-input-desconto`).val());
			let valorOriginal = unMoney($(`.js-total-procedimentos`).val())
			let DescontosJaAplicados = procedimentos.reduce((acc, obj) => acc + obj.desconto, 0);

			if (quantidadeDesconto == 0) {
				swal({
					title: "Erro",
					text: 'Selecione pelo menos um procedimento para aplicar desconto!',
					html: true,
					type: "error",
					confirmButtonColor: "#424242"
				});
			} else if (desconto == 0 || desconto === undefined || desconto === '' || !desconto) {
				swal({
					title: "Erro",
					text: 'Defina o desconto que deverá ser aplicado!',
					html: true,
					type: "error",
					confirmButtonColor: "#424242"
				});
			} else if ((DescontosJaAplicados + desconto) > valorOriginal) {
				swal({
					title: "Erro",
					text: 'a Soma dos Descontos Não Podem Ser maior que o Valor total dos Procedimentos!',
					html: true,
					type: "error",
					confirmButtonColor: "#424242"
				});
			} else {
				let valorTotal = 0;
				let cont = 0;
				let qtdItensDesconto = 0;
				let valorItens = []
				let percItens = []
				if (tipoDesconto != "dinheiro") {
					desconto = $(`.aside-plano-desconto .js-input-desconto`).val();
					desconto = ((parseFloat(desconto.replace('%', ""))) / 100) * valorOriginal;
				}
				procedimentos.forEach(x => {
					if (x.situacao == "aprovado") {
						if ($(`.aside-plano-desconto .js-desconto-procedimento:eq(${cont})`).prop('checked') === true) {
							valorTotal += eval(x.valorCorrigido);
							qtdItensDesconto++;
							valorItens[cont] = x.valor;
							percItens[cont] = unMoney(x.valor / valorOriginal)

							if (x.quantitativo > 0) {
								valorItens[cont] = unMoney(x.quantidade * x.valor);
								percItens[cont] = unMoney((x.quantidade * x.valor) / valorOriginal)
							} else if (x.face == 1) {
								valorItens[cont] = unMoney(x.faces.length * x.valor);
								percItens[cont] = unMoney((x.faces.length * x.valor) / valorOriginal)
							} else if (x.id_regiao == 5) {
								valorItens[cont] = unMoney(x.hof * x.valor);
								percItens[cont] = unMoney((x.hof * x.valor) / valorOriginal)
							}
						}
						cont++;
					}
				});


				// calcula percentual do desconto em cima do valor total
				//let descontoParcentual = ((desconto/valorTotal)*100).toFixed(4);

				if (desconto == 0 || desconto === undefined || desconto === '' || !desconto) {
					swal({
						title: "Erro",
						text: 'Defina o desconto que deverá ser aplicado!',
						html: true,
						type: "error",
						confirmButtonColor: "#424242"
					});
					$(`.aside-plano-desconto .js-input-desconto`).addClass('erro');
				} else {
					let cont = 0;
					let contProcedimento = 0;
					let descontoAPlicado = 0
					procedimentos.forEach(x => {
						if (x.situacao == "aprovado") {
							if ($(`.aside-plano-desconto .js-desconto-procedimento:eq(${cont})`).prop('checked') === true) {
								let descontoAplicar = desconto * percItens[cont]
								let desc = 0;
								if (x.desconto > 0) {
									//valorProc=procedimentos[contProcedimento].valorCorrigido;
									descontoAplicar = descontoAplicar + procedimentos[contProcedimento].desconto
									descontoAPlicado += descontoAplicar
								} else {
									descontoAplicar = descontoAplicar
									descontoAPlicado += descontoAplicar
								}
								procedimentos[contProcedimento].desconto = descontoAplicar
							}
							cont++;
						}
						contProcedimento++;
					});
					if (descontoAPlicado < desconto) {
						procedimentos[0].desconto = procedimentos[0].desconto + (desconto - descontoAPlicado)
					}
					$('.js-input-desconto').val('');

				}
				procedimentosListar();
				descontoListarProcedimentos(0);
				atualizaValor(true);
			}
		})

		// abre janela de desconto
		$('.js-btn-desconto').click(function() {
			$(".aside-plano-desconto").fadeIn(100, function() {
				$(".aside-plano-desconto .aside__inner1").addClass("active");
			});
			// $('.js-tipo-manual').hide();
			// $('.js-tipo-politica').hide();
			// $('.js-tipo-politica table').html("")
			descontoListarProcedimentos(1);
		});

		// ao selecionar procedimento em desconto
		$('#js-descontos-table-procedimentos').on('click', '.js-desconto-procedimento', descontoAtualizar);
		$('.aside-plano-desconto .js-input-desconto').change(descontoAtualizar);

		$('.aside-plano-desconto .js-select-tipoDesconto').change(function() {
			$('.aside-plano-desconto .js-input-desconto').maskMoney('destroy');
			$('.aside-plano-desconto .js-input-desconto').val('');
			if ($(this).val() == "dinheiro") {
				$('.aside-plano-desconto .js-input-desconto').maskMoney({
					symbol: '',
					allowZero: true,
					showSymbol: true,
					thousands: '.',
					decimal: ',',
					symbolStay: true
				}).attr('maxlength', 10);

			} else {
				$('.aside-plano-desconto .js-input-desconto').maskMoney({
					symbol: '',
					precision: 1,
					suffix: '%',
					allowZero: true,
					showSymbol: true,
					thousands: '',
					decimal: '.',
					symbolStay: true
				}).attr('maxlength', 6);
			}
			$('.aside-plano-desconto .js-input-desconto').trigger('keyup');
		}).trigger('change');


		$('.js-listar-parcelas').on('change', '.js-vencimento', pagamentosPersistirObjeto);

		$('.js-listar-parcelas').on('change', '.js-creditoBandeira', function() {
			if ($(this).find('option:checked').attr('data-populaParcela') == "false") {
				return
			}
			creditoBandeiraAtualizaParcelas($(this), 0);
			pagamentosPersistirObjeto();
		});

		$('.js-listar-parcelas').on('keyup', '.js-identificador', pagamentosPersistirObjeto);
		$('.js-listar-parcelas').on('change', '.js-debitoBandeira,.js-creditoBandeira,.js-parcelas', pagamentosPersistirObjeto);


		/*
		$('.js-pagamentos').on('keyup','.js-valor',function(){
			let index = $(this).index('.js-pagamentos .js-valor');
			let numeroParcelas = eval($('.js-pagamentos-quantidade').val());
			let valorTotalAux = valorTotal;
			let valorAcumulado = 0;
			let parcelas = [];
			let val = unMoney($(this).val());
			for(i=0;i<=index;i++) {
				val = unMoney($(`.js-pagamentos .js-valor:eq(${i})`).val());
				id_formapagamento = $(`.js-pagamentos .js-id_formadepagamento:eq(${i})`).val();
				identificador = $(`.js-pagamentos .js-identificador:eq(${i})`).val();
				creditoBandeira = $(`.js-pagamentos .js-creditoBandeira:eq(${i})`).val();
				operadora=0;
				if(id_formapagamento==2) {
					operadora = $(`.js-pagamentos .js-creditoBandeira:eq(${i}) option:selected`).attr('data-id_operadora');
				} else if(id_formapagamento==3) {
					operadora = $(`.js-pagamentos .js-debitoBandeira:eq(${i}) option:selected`).attr('data-id_operadora');
				}
				
				debitoBandeira = $(`.js-pagamentos .js-debitoBandeira:eq(${i})`).val();
				qtdParcelas = $(`.js-pagamentos .js-parcelas:eq(${i})`).val();
				valorAcumulado += val;

				let item = pagamentos[i];


				item.vencimento=pagamentos[i].vencimento;
				item.valor=val;
				parcelas.push(item);
			}

			let valorRestante = valorTotal-valorAcumulado;
			let continua = true;
			if(valorAcumulado>valorTotal) {
				let dif = valorAcumulado - valorTotal;
				dif=dif.toFixed(2);

				if(dif>0.1) {
					continua=false;
					swal({title: "Erro!", text: 'Os valores das parcelas não podem superar o valor total', html:true, type:"error", confirmButtonColor: "#424242"});
				}
			}  

			if(continua) {
				numeroParcelasRestantes = numeroParcelas - (index+1);
				valorParcela=valorRestante/numeroParcelasRestantes;
				valorParcela=valorParcela.toFixed(2);
				let valorInputado=0;
				for(i=(index+1);i<numeroParcelas;i++) {

					if(pagamentos[i]) {
						let item = {};
						item=pagamentos[i];
						item.vencimento=pagamentos[i].vencimento;
						item.valor=valorParcela;

						parcelas.push(item);
					}

				}

				// se alterou a ultima parcela
				if(numeroParcelas==(index+1)) {

					// verifica todos os valores inputados batem com o valor total
					if(valorAcumulado<valorTotal) {
						dif = valorTotal-valorAcumulado;
						parcelas[index].valor+=dif;
					} else if(valorAcumulado>valorTotal) {
						dif = valorTotal-valorAcumulado;
						parcelas[index].valor=dif;
					}

					//alert('alterou o ulitmo '+valorTotal+' = '+valorAcumulado)
				}

				pagamentos=parcelas;
				pagamentosPersistirObjeto()
			}
		});
		*/
		$('.js-listar-parcelas').on('change', '.js-id_formadepagamento', function() {
			pagamentosAtualizaCampos($(this), true);
		});

		// altera quantidade de parcelas
		$('.js-pagamentos-quantidade').change(function() {
			let qtd = $(this).val();
			if (!$.isNumeric(eval(qtd))) qtd = 1;
			else if (qtd < 1) qtd = 1;
			else if (qtd >= 36) qtd = 36;
			$('.js-pagamentos-quantidade').val(qtd);
			atualizaValor(true);
		});

		// seleciona o tipo de pagamento
		$('input[name=pagamento]').change(function() {
			atualizaValor(true);
		})

		// remove procedimento
		$('.aside-plano-procedimento-editar .js-removerProcedimento').click(function() {
			swal({
				title: "Atenção",
				text: 'Tem certeza que deseja excluir este procedimento?',
				type: "warning",
				showCancelButton: true,
				confirmButtonColor: "#DD6B55",
				confirmButtonText: "Sim!",
				cancelButtonText: "Não",
				closeOnConfirm: false,
				closeOnCancel: false
			}, function(isConfirm) {
				if (isConfirm) {
					let index = $('.aside-plano-procedimento-editar .js-asidePlanoEditar-index').val();
					procedimentos.splice(index, 1);
					pagamentos = [];
					$('[name="pagamentos"]').html("");
					procedimentosListar();
					swal.close();
					$('.aside-plano-procedimento-editar .aside-close').click();
				} else {
					swal.close();
				}
			});
		});

		// edita procedimento
		$('.aside-plano-procedimento-editar .js-salvarEditarProcedimento').click(function() {
			// capta dados
			let index = $('.aside-plano-procedimento-editar .js-asidePlanoEditar-index').val();
			let situacao = $('.aside-plano-procedimento-editar .js-asidePlanoEditar-situacao').val();
			let obs = $('.aside-plano-procedimento-editar .js-asidePlanoEditar-obs').val();

			if (procedimentos[index].quantitativo == 1) {
				let quantidade = $('.aside-plano-procedimento-editar .js-asidePlanoEditar-quantidade').val();
				procedimentos[index].quantidade = quantidade;
			}


			procedimentos[index].situacao = situacao;
			procedimentos[index].obs = obs;
			pagamentos = []
			$('[name="pagamentos"]').html("");
			$('.js-pagamentos-quantidade').val("")
			procedimentosListar();
			atualizaValor(true);

			$('.aside-plano-procedimento-editar .aside-close').click();
		});

		// clica em um procedimento para editar
		$('#js-table-procedimentos').on('click', '.js-tr-item', function() {
			let index = $('#js-table-procedimentos .js-tr-item').index(this);
			procedimentoEditar(index);
		});

		// adiciona procedimento
		$('.aside-plano-procedimento-adicionar .js-salvarAdicionarProcedimento').click(function() {
			//$('.js-listar-parcelas .fpag').html('');
			pagamentos = [];
			$('[name="pagamentos"]').html("");
			$('.js-pagamentos-quantidade').val("0")
			// capta dados 
			let id_procedimento = $('.aside-plano-procedimento-adicionar .js-asidePlano-id_procedimento option:selected').val();
			let procedimento = $(`.aside-plano-procedimento-adicionar .js-asidePlano-id_procedimento option:selected`).text();
			let id_regiao = $('.aside-plano-procedimento-adicionar .js-asidePlano-id_procedimento option:selected').attr('data-id_regiao');
			let quantitativo = $('.aside-plano-procedimento-adicionar .js-asidePlano-id_procedimento option:selected').attr('data-quantitativo');
			let face = $('.aside-plano-procedimento-adicionar .js-asidePlano-id_procedimento option:selected').attr('data-face');
			let id_plano = $(`.aside-plano-procedimento-adicionar .js-asidePlano-id_plano option:selected`).val();
			let plano = $(`.aside-plano-procedimento-adicionar .js-asidePlano-id_plano option:selected`).text();
			let valor = eval($(`.aside-plano-procedimento-adicionar .js-asidePlano-id_plano option:selected`).attr('data-valor'));
			let quantidade = $(`.aside-plano-procedimento-adicionar .js-asidePlano-quantidade`).val();
			let obs = $('.aside-plano-procedimento-adicionar .js-asidePlano-obs').val();
			let valorCorrigido = eval(valor);
			//if(quantitativo==1) valorCorrigido=quantidade*valor;



			// valida
			let erro = '';
			if (id_procedimento.length == 0) erro = 'Selecione o Procedimento para adicionar';
			else if (id_plano.length == 0) erro = 'Selecione o Plano';
			else if (id_regiao > 2 && id_regiao < 5 && $(`.js-regiao-${id_regiao}-select`).val().length == 0) erro = 'Preencha a Região';
			else if (quantitativo == 1 && quantidade <= 0) erro = `A quantidade não pode ser valor negativo!`;
			else if (face == 1) {
				$(`.js-regiao-${id_regiao}-select option:selected`).each(function(index, el) {
					let idO = $(el).val();
					if (erro.length == 0 && $(`.aside-plano-procedimento-adicionar select.js-face-${idO}-select option:selected`).length == 0) {
						erro = `Selecione as Faces do Dente ${$(el).text()}`;
					}
				});
			}
			// se for hof
			else if (id_regiao == 5) {
				$(`.js-regiao-${id_regiao}-select option:selected`).each(function(index, el) {
					let idO = $(el).val();
					if (erro.length == 0) {
						if ($(`.aside-plano-procedimento-adicionar input.js-hof-${idO}-input`).val().length == 0 ||
							$.isNumeric($(`.aside-plano-procedimento-adicionar input.js-hof-${idO}-input`).val()) === false ||
							eval($(`.aside-plano-procedimento-adicionar input.js-hof-${idO}-input`).val()) == 0) {
							erro = `Defina a quantidade de <b>${$(el).text()}</b>`;
						}
					}
				});
			}

			if (erro.length > 0) {
				swal({
					title: "Erro!",
					text: erro,
					type: "error",
					html: true,
					confirmButtonColor: "#424242"
				});
			} else {
				let linhas = 1;
				if (id_regiao >= 2) linhas = eval($(`.js-regiao-${id_regiao}-select option:selected`).length);
				let item = {};
				let opcoes = ``;
				for (var i = 0; i < linhas; i++) {
					item = {};
					item.obs = obs;
					item.id_procedimento = id_procedimento;
					item.procedimento = procedimento;
					item.id_regiao = id_regiao;
					item.id_plano = id_plano;
					item.face = face;
					item.plano = plano;
					item.profissional = 0;
					item.quantidade = eval(quantidade);
					item.situacao = 'aprovado';
					item.valor = valor;
					item.quantitativo = eval(quantitativo);
					item.desconto = 0;
					item.taxas = 0;
					// Data e Usuario
					let dt = new Date();
					let dia = dt.getDate();
					let mes = dt.getMonth();
					let min = dt.getMinutes();
					let hrs = dt.getHours();
					mes++
					mes = mes <= 9 ? `0${mes}` : mes;
					dia = dia <= 9 ? `0${dia}` : dia;
					min = min <= 9 ? `0${min}` : min;
					hrs = hrs <= 9 ? `0${hrs}` : hrs;

					let data = `${dia}/${mes}/${dt.getFullYear()} ${hrs}:${min}`;
					item.data = data;
					item.id_usuario = id_usuario;
					item.autor = autor;

					// Opcoes
					opcao = id_opcao = ``;
					if (id_regiao >= 2) {
						id_opcao = $(`.js-regiao-${id_regiao}-select option:selected:eq(${i})`).val();
						opcao = $(`.js-regiao-${id_regiao}-select option:selected:eq(${i})`).text();
					}
					item.opcao = opcao;
					item.id_opcao = id_opcao;

					// Faces, Quantitativo ou Hof (id_regiao=5)
					faces = [];
					hof = '';
					if (face == 1) {

						$(`.aside-plano-procedimento-adicionar select.js-regiao-${id_regiao}-select option:selected:eq(${i})`).each(function(index, el) {
							let id_opcao = $(el).val();
							let faceItem = {};
							facesItens = $(`.aside-plano-procedimento-adicionar select.js-face-${id_opcao}-select`).val();

							faces = facesItens;

						});

						valorCorrigido = faces.length * valor;
					} else if (quantitativo == 1) {
						valorCorrigido = quantidade * valor;
					} else if (id_regiao == 5) {
						$(`.aside-plano-procedimento-adicionar select.js-regiao-${id_regiao}-select option:selected:eq(${i})`).each(function(index, el) {
							let id_opcao = $(el).val();
							hof = eval($(`.aside-plano-procedimento-adicionar input.js-hof-${id_opcao}-input`).val());
						});
						valorCorrigido = valor * eval(hof);
					}
					item.hof = hof;
					item.faces = faces;

					item.valorCorrigido = valorCorrigido;


					procedimentos.push(item);

					if ((i + 1) == linhas) {
						$(`.aside-plano-procedimento-adicionar .js-asidePlano-quantidade`).val(1).parent().parent().hide();
						procedimentosListar();

						atualizaValor(true);

					}
				}

			}

			if (temPolitica.parcelasParametros) {
				$('[name="tipo_financeiro').filter("[value='politica']").prop("checked", true);
			}


		});

		// quando seleciona o procedimento, exibe as regioes parametrizadas
		$('.aside-plano-procedimento-adicionar select.js-asidePlano-id_procedimento').change(function() {

			let id_procedimento = $(this).val();

			if (id_procedimento.length > 0) {
				let id_regiao = $(this).find('option:selected').attr('data-id_regiao');
				let regiao = $(this).find('option:selected').attr('data-regiao');
				let quantitativo = $(this).find('option:selected').attr('data-quantitativo');
				let face = $(this).find('option:selected').attr('data-face');


				if (quantitativo == 1) {
					$(`.js-asidePlano-quantidade`).parent().parent().show();
					$(`.js-asidePlano-quantidade`).val(1);
				} else {
					$(`.js-asidePlano-quantidade`).parent().parent().hide();
				}

				$(`.js-regiao-${id_regiao}-select`).find('option:selected').prop('selected', false).trigger('change').trigger('chosen:updated');

				$(`.js-regiao`).hide();
				$(`.js-regiao-${id_regiao}`).show();
				$(`.js-regiao-${id_regiao}`).find('select').chosen({
					hide_results_on_select: false,
					allow_single_deselect: true
				});

				if (id_regiao != 5) {
					$('.aside-plano-procedimento-adicionar .js-fieldset-hof').hide();
					$('.aside-plano-procedimento-adicionar .js-fieldset-hof .js-hofs').remove();
				}

				if (face == 0) {
					$('.aside-plano-procedimento-adicionar .js-fieldset-faces').hide();
					$('.aside-plano-procedimento-adicionar .js-fieldset-faces .js-faces').remove();

				}


				let data = `ajax=planos&id_procedimento=${id_procedimento}`;

				$('.aside-plano-procedimento-adicionar .js-asidePlano-id_plano option').remove();
				$('.aside-plano-procedimento-adicionar .js-asidePlano-id_plano').append(`<option value="">Carregando...</option>`);

				$.ajax({
					type: "POST",
					url: baseURLApiAsidePlanoDeTratamento,
					data: data,
					success: function(rtn) {
						if (rtn.success) {
							$('.aside-plano-procedimento-adicionar .js-asidePlano-id_plano option').remove();

							if (rtn.planos && rtn.planos.length > 0) {
								$('.aside-plano-procedimento-adicionar .js-asidePlano-id_plano').append(`<option value="">-</option>`);
								let cont = 1;
								rtn.planos.forEach(x => {
									$('.aside-plano-procedimento-adicionar .js-asidePlano-id_plano').append(`<option value="${x.id}" data-valor="${x.valor}">${x.titulo}</option>`);

									if (cont == rtn.planos.length) {
										$('.aside-plano-procedimento-adicionar .js-asidePlano-id_plano option:eq(1)').prop('selected', true);
									}
									cont++;
								});
							} else {
								$('.aside-plano-procedimento-adicionar .js-asidePlano-id_plano').append(`<option value="">- SEM PLANO CADASTRAO -<option>`);
							}
						}
					},
				})
			} else {
				$(`.js-regiao`).hide();
				$(`.js-procedimento-btnOk`).hide();
			}
		});

		// quando seleciona a regiao 4 (dentes), monta as faces
		$('.aside-plano-procedimento-adicionar select.js-regiao-4-select').change(function() {
			let face = $('.aside-plano-procedimento-adicionar select.js-asidePlano-id_procedimento option:selected').attr('data-face');

			if (face == 1) {

				$('.aside-plano-procedimento-adicionar .js-fieldset-faces').show();
				$('.js-faces').hide();

				let cont = 0;
				let selectRegiao4 = $(this);

				selectRegiao4.find('option:selected').each(function(index, el) {
					let id_regiao = $(el).val();
					let regiao = $(el).text();


					if ($('.aside-plano-procedimento-adicionar .js-fieldset-faces .js-face-' + id_regiao).length > 0) {
						$('.aside-plano-procedimento-adicionar .js-fieldset-faces .js-face-' + id_regiao).show();
					} else {
						$('.aside-plano-procedimento-adicionar .js-fieldset-faces').append(`<dl class="js-faces js-face-${id_regiao}">
																								<dt>${regiao}</dt>
																								<dd>
																									<select class="js-select-faces js-face-${id_regiao}-select" multiple>
																										<option value=""></option>
																										<?php echo $_regioesFacesOptions; ?>
																									</select>
																								</dd>
																							</dl>`);
					}

					cont++;

					if (selectRegiao4.find('option:selected').length == cont) {
						$('.aside-plano-procedimento-adicionar .js-fieldset-faces .js-faces:hidden').remove();
						$('.aside-plano-procedimento-adicionar .js-select-faces').chosen('destroy');
						$('.aside-plano-procedimento-adicionar .js-select-faces').chosen({
							hide_results_on_select: false,
							allow_single_deselect: true
						});
					}

				})
			} else {

				$('.aside-plano-procedimento-adicionar .js-fieldset-faces').hide();
			}
		});

		// quando seleciona a regiao 5 (hof)
		$('.aside-plano-procedimento-adicionar select.js-regiao-5-select').change(function() {
			$('.aside-plano-procedimento-adicionar .js-fieldset-hof').html("")
			$('.aside-plano-procedimento-adicionar .js-fieldset-hof').show();
			let cont = 0;
			let selectRegiao5 = $(this);
			let regioesAtivas = []
			selectRegiao5.find('option:selected').each(function(index, el) {
				let id_regiao = $(el).val();
				let regiao = $(el).text();
				regioesAtivas[id_regiao] = regiao;
				/*
				if($('.aside-plano-procedimento-adicionar .js-fieldset-hof .js-hof-'+id_regiao).length>0) {
					$('.aside-plano-procedimento-adicionar .js-fieldset-hof .js-hof-'+id_regiao).show();
				} else {
					$('.aside-plano-procedimento-adicionar .js-fieldset-hof').append(`<dl class="js-hofs js-hof-${id_regiao}">
																							<dt>${regiao}</dt>
																							<dd>
																								<input type="text" class="js-input-hofs js-hof-${id_regiao}-input" style="width:80px;" maxlength="2" /> unidade(s)
																							</dd>
																						</dl>`);
				}
				*/
			})
			if (regioesAtivas.length > 0) {
				for (let i in regioesAtivas) {
					$('.aside-plano-procedimento-adicionar .js-fieldset-hof').append(`<dl class="js-hofs js-hof-${i}">
																							<dt>${regioesAtivas[i]}</dt>
																							<dd>
																								<input type="text" class="js-input-hofs js-hof-${i}-input" style="width:80px;" maxlength="2" /> unidade(s)
																							</dd>
																						</dl>`);
				}
			} else {
				$('.aside-plano-procedimento-adicionar .js-fieldset-hof').hide();
			}
		});

		$('.aside-plano-procedimento-adicionar').on('keyup', '.js-input-hofs', function() {
			var regexp = (/[^0-9]|^\.+(?!$)|^0+(?=[0-9]+)|\.(?=\.|.+\.)/g);
			if (regexp.test(this.value)) {
				this.value = this.value.replace(regexp, '');
			}
		})
		// desativarCampos();
		// disabledForm()
	});
</script>

<section class="aside aside-plano-procedimento-editar" style="display: none;">
	<div class="aside__inner1">
		<header class="aside-header">
			<h1>Procedimento</h1>
			<a href="javascript:;" class="aside-header__fechar aside-close"><i class="iconify" data-icon="fluent:dismiss-24-filled"></i></a>
		</header>

		<form method="post" class="aside-content form js-form-editar-procedimento">
			<input type="hidden" class="js-asidePlanoEditar-index" value="" />
			<section class="filter">
				<div class="filter-group"></div>
				<div class="filter-group">
					<div class="filter-form form">

						<dl>
							<dd><a href="javascript:;" class="button js-removerProcedimento"><i class="iconify" data-icon="fluent:delete-24-regular"></i></a></dd>
						</dl>

						<dl>
							<dd><button type="button" class="button button_main js-salvarEditarProcedimento" data-loading="0"><i class="iconify" data-icon="fluent:checkmark-12-filled"></i><span> Salvar</button></dd>
						</dl>
					</div>
				</div>
			</section>

			<fieldset>
				<legend>Procedimento</legend>

				<dl>
					<dt>Procedimento</dt>
					<dd><input type="text" class="js-asidePlanoEditar-procedimento" disabled style="background:#ccc" /></dd>
				</dl>

				<dl>
					<dt>Plano</dt>
					<dd><input type="text" class="js-asidePlanoEditar-plano" disabled style="background:#ccc" /></dd>
				</dl>

				<dl>
					<dt>Região</dt>
					<dd><input type="text" class="js-asidePlanoEditar-regiao" disabled style="background:#ccc" /></dd>
				</dl>

				<div class="colunas4">
					<dl>
						<dt>Quantidade</dt>
						<dd><input type="number" class="js-asidePlanoEditar-quantidade" min=0 oninput="validity.valid||(value='');" /></dd>
					</dl>
				</div>

			</fieldset>

			<fieldset>
				<legend>Informações</legend>

				<dl>
					<dt>Status</dt>
					<dd>
						<select class="js-asidePlanoEditar-situacao">
							<?php echo $selectSituacaoOptions; ?>
						</select>
					</dd>
				</dl>


				<div class="colunas2">
					<dl>
						<dt>Valor Tabela</dt>
						<dd><input type="text" class="js-asidePlanoEditar-valorTabela" disabled style="background:#ccc" /></dd>
					</dl>
					<dl>
						<dt>Valor Desconto</dt>
						<dd><input type="text" class="js-asidePlanoEditar-valorDesconto" disabled style="background:#ccc" /></dd>
					</dl>
				</div>

				<div class="colunas2">
					<dl>
						<dt>Valor Corrigido</dt>
						<dd><input type="text" class="js-asidePlanoEditar-valorCorrigido" disabled style="background:#ccc" /></dd>
					</dl>
					<dl>
						<dt>Valor Unitário</dt>
						<dd><input type="text" class="js-asidePlanoEditar-valorUnitario" disabled style="background:#ccc" /></dd>
					</dl>
				</div>

				<dl>
					<dt>Observações</dt>
					<dd>
						<textarea class="js-asidePlanoEditar-obs" style="height:100px;"></textarea>
					</dd>
				</dl>

				<div class="colunas2">
					<dl>
						<dt>Adicionado por</dt>
						<dd class="js-asidePlanoEditar-usuario"></dd>
					</dl>
					<dl>
						<dt>Data</dt>
						<dd class="js-asidePlanoEditar-data"></dd>
					</dl>
				</div>

			</fieldset>

		</form>
	</div>
</section>

<section class="aside aside-plano-procedimento-adicionar" style="display: none;">
	<div class="aside__inner1">
		<header class="aside-header">
			<h1>Adicionar Procedimento</h1>
			<a href="javascript:;" class="aside-header__fechar aside-close"><i class="iconify" data-icon="fluent:dismiss-24-filled"></i></a>
		</header>

		<form method="post" class="aside-content form js-form-adicionar-procedimento">
			<section class="filter">
				<div class="filter-group"></div>
				<div class="filter-group">
					<div class="filter-form form">
						<dl>
							<dd><button type="button" class="button button_main js-salvarAdicionarProcedimento" data-loading="0"><i class="iconify" data-icon="fluent:add-circle-24-regular"></i> <span>Adicionar</span></button></dd>
						</dl>
					</div>
				</div>
			</section>

			<fieldset class="js-fieldset-adicionar">
				<legend>Adicionar</legend>
				<dl>
					<dt>Procedimento</dt>
					<dd>
						<select class="js-asidePlano-id_procedimento" data-placeholder="Selecione o procedimento">
							<option value=""></option>
							<?php
							foreach ($_procedimentos as $p) {
								if ($p->lixo == 1 or $p->pub == 0) continue;
								echo '<option value="' . $p->id . '" 
													data-id_regiao="' . $p->id_regiao . '" 
													data-regiao="' . (isset($_regioes[$p->id_regiao]) ? utf8_encode($_regioes[$p->id_regiao]->titulo) : "-") . '" 
													data-quantitativo="' . ($p->quantitativo == 1 ? 1 : 0) . '" 
													data-face="' . $p->face . '">' . utf8_encode($p->titulo) . '</option>';
							}
							?>
						</select>
					</dd>
				</dl>
				<dl>
					<dt>Plano</dt>
					<dd>
						<select class="js-asidePlano-id_plano">
							<option value="">-</option>
						</select>
					</dd>
				</dl>

				<dl style="display: none">
					<dt>Quantidade</dt>
					<dd><input type="number" class="js-asidePlano-quantidade" value="1" min=0 oninput="validity.valid||(value='');" /></dd>
				</dl>

				<dl class="js-regiao-2 js-regiao" style="display: none;">
					<dt>Arcada(s)</dt>
					<dd>
						<select class="js-regiao-2-select" multiple>
							<?php
							if (isset($_regioesOpcoes[2])) {
								foreach ($_regioesOpcoes[2] as $o) {
									echo '<option value="' . $o->id . '" data-titulo="' . utf8_encode($o->titulo) . '">' . utf8_encode($o->titulo) . '</option>';
								}
							}
							?>
						</select>
					</dd>
				</dl>

				<dl class="js-regiao-3 js-regiao" style="display: none">
					<dt>Quadrante(s)</dt>
					<dd>
						<select class="js-regiao-3-select" multiple>
							<?php
							if (isset($_regioesOpcoes[3])) {
								foreach ($_regioesOpcoes[3] as $o) {
									echo '<option value="' . $o->id . '" data-titulo="' . utf8_encode($o->titulo) . '">' . utf8_encode($o->titulo) . '</option>';
								}
							}
							?>
						</select>
					</dd>
				</dl>

				<dl class="js-regiao-4 js-regiao" style="display: none">
					<dt>Dente(s)</dt>
					<dd>
						<select class="js-regiao-4-select" multiple>
							<?php
							if (isset($_regioesOpcoes[4])) {
								foreach ($_regioesOpcoes[4] as $o) {
									echo '<option value="' . $o->id . '" data-titulo="' . utf8_encode($o->titulo) . '">' . utf8_encode($o->titulo) . '</option>';
								}
							}
							?>
						</select>
					</dd>
				</dl>


				<dl class="js-regiao-5 js-regiao" style="display: none">
					<dt>Região</dt>
					<dd>
						<select class="js-regiao-5-select" multiple>
							<?php
							if (isset($_regioesOpcoes[5])) {
								foreach ($_regioesOpcoes[5] as $o) {
									echo '<option value="' . $o->id . '" data-titulo="' . utf8_encode($o->titulo) . '">' . utf8_encode($o->titulo) . '</option>';
								}
							}
							?>
						</select>
					</dd>
				</dl>


				<dl>
					<dt>Observações</dt>
					<dd>
						<textarea class="js-asidePlano-obs" style="height:100px;"></textarea>
					</dd>
				</dl>

			</fieldset>

			<fieldset class="js-fieldset-faces" style="display:none">
				<legend>Faces</legend>


			</fieldset>



			<fieldset class="js-fieldset-hof" style="display:none">
				<legend>HOF</legend>


			</fieldset>
		</form>
	</div>
</section>

<section class="aside aside-plano-desconto" style="display: none;">
	<div class="aside__inner1">
		<header class="aside-header">
			<h1>Aplicar Desconto</h1>
			<a href="javascript:;" class="aside-header__fechar aside-close"><i class="iconify" data-icon="fluent:dismiss-24-filled"></i></a>
		</header>

		<form method="post" class="aside-content form js-form-adicionar-procedimento">
			<?php /*<section class="filter">
				<div class="filter-group"></div>
				<div class="filter-group">
					<div class="filter-form form">
						<dl>
							<dd><button type="button" class="button button_main js-salvarAdicionarProcedimento" data-loading="0"><i class="iconify" data-icon="fluent:add-circle-24-regular"></i> <span>Adicionar</span></button></dd>
						</dl>
					</div>								
				</div>
			</section>*/ ?>

			<fieldset class="js-descontos-fieldset-procedimentos">
				<legend>Procedimentos</legend>

				<div class="list1">
					<table id="js-descontos-table-procedimentos">

					</table>
				</div>
			</fieldset>

			<fieldset>
				<legend>Desconto</legend>

				<div class="colunas4">
					<dl>
						<dt>Desconto em</dt>
						<dd>
							<select class="js-select-tipoDesconto">
								<option value="dinheiro">Dinheiro</option>
								<option value="porcentual">Porcentagem</option>
							</select>
						</dd>
					</dl>

					<dl>
						<dt>&nbsp;</dt>
						<dd>
							<input type="text" class="js-input-desconto" />
						</dd>
					</dl>

					<dl class="dl2">
						<dt>&nbsp;</dt>
						<dd>
							<a href="javascript:;" class="button button_main js-btn-aplicarDesconto">Aplicar Desconto</a>
							<a href="javascript:;" class="button js-btn-removerDesconto">Remover Descontos</a>
						</dd>
					</dl>
				</div>

				<div class="colunas4">
					<dl>
						<dt>Total dos Procedimentos</dt>
						<dd><input type="text" class="js-total-procedimentos" disabled /></dd>
					</dl>
					<dl style="display:none">
						<dt>Desconto a ser aplicado</dt>
						<dd><input type="text" class="js-total-descontos" disabled /></dd>
					</dl>
					<dl>
						<dt>Descontos aplicados</dt>
						<dd><input type="text" class="js-total-descontosAplicados" disabled /></dd>
					</dl>
					<dl>
						<dt>Total com descontos</dt>
						<dd><input type="text" class="js-total-procedimentosdescontos" disabled /></dd>
					</dl>
				</div>
			</fieldset>
		</form>
	</div>
</section>