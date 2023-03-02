<?php
if (isset($_POST['ajax'])) {
	$dir = "../../";
	require_once("../../lib/conf.php");
	require_once("../../usuarios/checa.php");
	$attr = array('prefixo' => $_p, 'usr' => $usr);
	$infozap = new Whatsapp($attr);
	$rtn = array('vazio');
	$_tableEspecialidades = $_p . "parametros_especialidades";
	$_tablePlanos = $_p . "parametros_planos";
	$_tableMarcas = $_p . "produtos_marcas";
	$_tablePacientes = $_p . "pacientes";
	$_tableProfissoes = $_p . "parametros_profissoes";
	$_tableListaPersonalizada = $_p . "parametros_indicacoes";
	$_tableTags = $_p . "parametros_tags";
	$_tableChecklist = $_p . "agenda_checklist";
	$pagamento;
	if (isset($_POST['id_pagamento']) and is_numeric($_POST['id_pagamento'])) {
		$id_pagamento =  $_POST['id_pagamento'];
		$sql->consult($_p . "financeiro_fluxo_recebimentos", "*", "where id='$id_pagamento'");
		if ($sql->rows) {
			$pagamento = mysqli_fetch_object($sql->mysqry);
		}
	}

	if ($_POST['ajax'] == 'pagamentoBaixa') {
		if (is_object($pagamento)) {
			$formaDePagamento = '';
			if (isset($_POST['id_formapagamento'])) {
				$sql->consult($_p . "parametros_formasdepagamento", "*", "where id='" . $_POST['id_formapagamento'] . "'");
				if ($sql->rows) {
					$formaDePagamento = mysqli_fetch_object($sql->mysqry);
				}
			}
			$dataPagamento = (isset($_POST['dataPagamento']) and !empty($_POST['dataPagamento'])) ? $_POST['dataPagamento'] : "";
			$dataVencimento = (isset($_POST['dataVencimento']) and !empty($_POST['dataVencimento'])) ? invDate($_POST['dataVencimento']) : date('Y-m-d');
			$valor = (isset($_POST['valor']) and !empty($_POST['valor'])) ? $_POST['valor'] : "";
			$valorParcela = (isset($_POST['valorParcela']) and !empty($_POST['valorParcela'])) ? ($_POST['valorParcela']) : 0;
			$valorMulta = (isset($_POST['valorMulta']) and !empty($_POST['valorMulta'])) ? ($_POST['valorMulta']) : 0;
			$valorJuros = (isset($_POST['valorJuros']) and !empty($_POST['valorJuros'])) ? ($_POST['valorJuros']) : 0;
			$descontoMultasJuros = (isset($_POST['descontoMultasJuros']) and !empty($_POST['descontoMultasJuros'])) ? ($_POST['descontoMultasJuros']) : 0;

			$tipoBaixa = (isset($_POST['tipoBaixa']) and !empty($_POST['tipoBaixa'])) ? $_POST['tipoBaixa'] : "";
			$obs = (isset($_POST['obs']) and !empty($_POST['obs'])) ? addslashes(utf8_decode($_POST['obs'])) : "";
			$cobrarJuros = (isset($_POST['cobrarJuros']) and isset($_POST['cobrarJuros']) and $_POST['cobrarJuros'] == 1) ? $_POST['cobrarJuros'] : 0;
			$debitoBandeira = (isset($_POST['debitoBandeira']) and is_numeric($_POST['debitoBandeira'])) ? $_POST['debitoBandeira'] : "";
			$creditoBandeira = (isset($_POST['creditoBandeira']) and is_numeric($_POST['creditoBandeira'])) ? $_POST['creditoBandeira'] : "";
			$creditoParcelas = (isset($_POST['creditoParcelas']) and is_numeric($_POST['creditoParcelas'])) ? $_POST['creditoParcelas'] : "";
			$id_operadora = (isset($_POST['id_operadora']) and is_numeric($_POST['id_operadora'])) ? $_POST['id_operadora'] : 0;
			$taxa = (isset($_POST['taxa']) and !empty($_POST['taxa'])) ? $_POST['taxa'] : 0;
			$taxa = floatval($taxa);

			if (empty($erro)) {
				if ($tipoBaixa == "pagamento") {
					$vSQLBaixa = "data=now(),
								lixo=0,
								id_origem=1,
								id_registro=$pagamento->id,
								pagamento_id_colaborador=" . $usr->id . ",
								id_formapagamento=$formaDePagamento?->id,
								id_operadora='" . $id_operadora . "',
								id_bandeira='" . $creditoBandeira . "',
								taxa_cartao='" . $taxa . "',
								tipo='paciente',
								valor_juros='" . $valorJuros . "',
								valor_multa='" . $valorMulta . "',
								valor_desconto='" . $descontoMultasJuros . "',
								obs='" . $obs . "'";
				} else {
					$vSQLBaixa = "data=now(),
									lixo=0,
									id_origem=1,
									id_registro=$pagamento->id,
									pagamento_id_colaborador=" . $usr->id . ",
									id_formapagamento=0,
									tipo='paciente',
									desconto='1',
									obs='" . $obs . "'";
				}
				if ($tipoBaixa == "pagamento" and $formaDePagamento->tipo == "credito") {
					$_prazos = array();
					$sql->consult($_p . "parametros_cartoes_operadoras_bandeiras", "*", "where id_operadora=$id_operadora and id_bandeira=$creditoBandeira and lixo=0");
					while ($x = mysqli_fetch_object($sql->mysqry)) {
						$taxas = json_decode($x->taxas);
						foreach ($taxas->creditoTaxas as $qtd => $tx) {
							$_prazos[$qtd] = $tx->$qtd->dias;
						}
					}
					for ($i = 1; $i <= $creditoParcelas; $i++) {
						$prazo = isset($_prazos[$i]) ? $_prazos[$i] : 0;
						$dtVencimento = date('Y-m-d', strtotime(date($dataVencimento) . " + $prazo days"));
						$vSQLComp = ",data_vencimento='$dtVencimento',valor='$valorParcela'";
						$sql->add($_p . "financeiro_fluxo", $vSQLBaixa . $vSQLComp);
					}
				} else {
					$sql->add($_p . "financeiro_fluxo", $vSQLBaixa . ",data_vencimento='$dataVencimento',valor='$valor'");
				}
				$rtn = array('success' => true);
			} else {
				$rtn = array('success' => false, 'error' => $erro);
			}
		} else {
			$rtn = array('success' => false, 'error' => 'Pagamento não encontrado!');
		}
	} else if ($_POST['ajax'] == "getPagamentosBaixas") {
		if (is_object($pagamento)) {
			// formas de pagamento
			$_formasDePagamento = array();
			$optionFormasDePagamento = '';
			$sql->consult($_p . "parametros_formasdepagamento", "*", "order by titulo asc");
			while ($x = mysqli_fetch_object($sql->mysqry)) {
				$_formasDePagamento[$x->id] = $x;
				$optionFormasDePagamento .= '<option value="' . $x->id . '" data-tipo="' . $x->tipo . '">' . utf8_encode($x->titulo) . '</option>';
			}
			$baixas = array();
			$sql->consult($_p . "pacientes_tratamentos", "*", "WHERE id='$pagamento->id_tratamento'");
			$tratamento = mysqli_fetch_object($sql->mysqry);
			$sql->consult($_p . "financeiro_fluxo", "*", "WHERE id_registro='$pagamento->id' AND lixo=0 order by data_vencimento asc");
			$saldoPago = 0;
			$saldoApagar = $pagamento->valor;
			while ($x = mysqli_fetch_object($sql->mysqry)) {
				if ($x->desconto == "1") $x->data_vencimento = $x->data;
				$baixas[] = array(
					"id_baixa" => (int)$x->id,
					"data" => date('d/m/Y', strtotime($x->data_vencimento)),
					"valor" => (float)$x->valor,
					"tipoBaixa" => $x->desconto == 0 ? 'PAGAMENTO' : 'DESCONTO',
					"id_formapagamento" => (int)$x->id_formapagamento,
					"formaDePagamento" => isset($_formasDePagamento[$x->id_formapagamento]) ? utf8_encode($_formasDePagamento[$x->id_formapagamento]->titulo) : '',
					"pago" => $x->pagamento,
					"valorMulta" => $x->valor_multa,
					"valorJuros" => $x->valor_juros,
					"descontoMultasJuros" => $x->valor_desconto,
					"vencido" => (strtotime($x->data_vencimento) < strtotime(date('Y-m-d')) ? true : false),
					"obs" => utf8_encode($x->obs),
					"taxa_cartao" => $x->taxa_cartao,
					"total" => (float)$x->valor,
				);
				$saldoApagar -= $x->valor;
			}
			$dados = array(
				"id" => $pagamento->id,
				"id_parcela" => (int)$pagamento->id,
				"data_vencimento" => $pagamento->data_vencimento,
				"data_emissao" => $pagamento->data_emissao,
				"id_tratamento" => $pagamento->id_tratamento,
				"qtdParcelas" => $pagamento->qtdParcelas,
				"valor" => $pagamento->valor,
				"valor_desconto" => $pagamento->valor_desconto,
				"valor_multa" => $pagamento->valor_multa,
				"valor_taxa" => $pagamento->valor_taxa,
				"taxa_cartao" => $pagamento->taxa_cartao,
				"pago" => $pagamento->pago,
				"titulo" => $tratamento->titulo,
				"saldoApagar" => $saldoApagar,
				"baixas" => $baixas,
			);
			$rtn = array('success' => true, 'dados' => $dados);;
		} else {
			$rtn = array('error' => true, 'message' => "Pagamento Não Encontrado");
		}
	} else if ($_POST['ajax'] == "baixas") {
		$baixas = array();
		$sql->consult($_p . "financeiro_fluxo", "*", "WHERE id_registro='$pagamento->id' AND lixo=0 order by data_vencimento asc");
		while ($x = mysqli_fetch_object($sql->mysqry)) {
			if ($x->desconto == "1") $x->data_vencimento = $x->data;
			$baixas[] = array(
				"id_baixa" => (int)$x->id,
				"data" => date('d/m/Y', strtotime($x->data_vencimento)),
				"valor" => (float)$x->valor,
				"tipoBaixa" => $x->desconto == 0 ? 'PAGAMENTO' : 'DESCONTO',
				"id_formapagamento" => (int)$x->id_formapagamento,
				"formaDePagamento" => isset($_formasDePagamento[$x->id_formapagamento]) ? utf8_encode($_formasDePagamento[$x->id_formapagamento]->titulo) : '',
				"pago" => $x->pagamento,
				//"recibo" => $x->recibo,
				"valorMulta" => $x->valor_multa,
				"valorJuros" => $x->valor_juros,
				"descontoMultasJuros" => $x->valor_desconto,
				//"parcelas" => $x->parcelas,
				"vencido" => (strtotime($x->data_vencimento) < strtotime(date('Y-m-d')) ? true : false),
				//"parcela" => $x->parcela,
				"obs" => utf8_encode($x->obs),
				"taxa_cartao" => $x->taxa_cartao,
				"total" => (float)$x->valor,
			);
		}

		$rtn = array('success' => true, 'baixas' => $baixas);;
	} else if ($_POST['ajax'] == "valoresPersistir") {

		$tipo = (isset($_POST['tipo']) and ($_POST['tipo'] == 'descontos' || $_POST['tipo'] == 'despesas')) ? $_POST['tipo'] : '';
		$valor = (isset($_POST['valor']) and ($_POST['valor'])) ? $_POST['valor'] : 0;

		$pagamento = '';
		if (isset($_POST['id_pagamento']) and is_numeric($_POST['id_pagamento'])) {
			$sql->consult($_p . "financeiro_fluxo_recebimentos", "*", "where id='" . $_POST['id_pagamento'] . "'");
			if ($sql->rows) {
				$pagamento = mysqli_fetch_object($sql->mysqry);
			}
		}

		if (!empty($tipo) and is_object($pagamento)) {
			$vSQL = "$tipo='" . $valor . "'";
			$vWHERE = "where id=$pagamento->id";
			$sql->update($_p . "financeiro_fluxo_recebimentos", $vSQL, $vWHERE);

			$rtn = array('success' => true);
		} else {
			$rtn = array('success' => false, 'error' => 'Dados incompletos para persistir');
		}
	} else if ($_POST['ajax'] == "baixaEstornar") {

		if (is_object($pagamento)) {
			$baixa = '';
			if (isset($_POST['id_baixa']) && is_numeric($_POST['id_baixa'])) {
				$sql->consult($_p . "financeiro_fluxo", "*", "where id='" . $_POST['id_baixa'] . "' and id_registro=$pagamento->id");
				if ($sql->rows) {
					$baixa = mysqli_fetch_object($sql->mysqry);
				}
			}
			if (is_object($baixa)) {
				$sql->update($_p . "financeiro_fluxo", "lixo=1,lixo_data=now(),lixo_id_colaborador=$usr->id", "where id=$baixa->id");
				$rtn = array('success' => true);
			} else {
				$rtn = array('success' => false, 'error' => 'Baixa não encontrada!');
			}
		} else {
			$rtn = array('success' => false, 'error' => 'Pagamento não encontrado!');
		}
	} else if ($_POST['ajax'] == "baixaEstornarPagamento") {

		if (is_object($pagamento)) {
			$baixa = '';
			if (isset($_POST['id_baixa']) && is_numeric($_POST['id_baixa'])) {
				$sql->consult($_p . "financeiro_fluxo", "*", "where id='" . $_POST['id_baixa'] . "' and id_registro=$pagamento->id");
				if ($sql->rows) {
					$baixa = mysqli_fetch_object($sql->mysqry);
				}
			}

			if (is_object($baixa)) {
				$sql->update($_p . "financeiro_fluxo", "pagamento=1,data_efetivado=now(),pagamento_id_colaborador=$usr->id", "where id=$baixa->id");
				$rtn = array('success' => true);
			} else {
				$rtn = array('success' => false, 'error' => 'Baixa não encontrada!');
			}
		} else {
			$rtn = array('success' => false, 'error' => 'Pagamento não encontrado!');
		}
	} else if ($_POST['ajax'] == "unirPagamentos") {
		if (isset($_POST['pagamentos']) and is_array($_POST['pagamentos'])) {
			if (count($_POST['pagamentos']) >= 2) {
				$uniaoIds = array();
				$valor = 0;
				$id_tratamento = 0;
				$sql->consult($_p . "financeiro_fluxo_recebimentos", "*", "where id IN (" . implode(",", $_POST['pagamentos']) . ")");
				if ($sql->rows) {
					while ($x = mysqli_fetch_object($sql->mysqry)) {
						$valor += $x->valor;
						$uniaoIds[] = $x->id;
						$id_paciente = $x->id_paciente;
						$id_unidade = $x->id_unidade;
						$id_tratamento = $x->id_tratamento;
					}
				}

				if (count($uniaoIds) >= 2) {
					$vSQL = "data_emissao=now(),
								data_vencimento='" . (isset($_POST['dataVencimento']) ? invDate($_POST['dataVencimento']) : now()) . "',
								id_colaborador=$usr->id,
								id_tratamento=$id_tratamento,
								id_pagante=$id_paciente,
								valor='" . $valor . "',
								fusao=1";
					//echo $vSQL;die();
					$sql->add($_p . "financeiro_fluxo_recebimentos", $vSQL);
					$id_fusao = $sql->ulid;

					$sql->update($_p . "financeiro_fluxo_recebimentos", "id_fusao=$id_fusao", "where id IN (" . implode(",", $uniaoIds) . ")");

					$rtn = array('success' => true);
				} else {
					$rtn = array('success' => false, 'error' => 'Selecione pelo menos 2 pagamentos');
				}
			} else {
				$rtn = array('success' => false, 'error' => 'Selecione pelo menos 2 pagamentos');
			}
		}
	} else if ($_POST['ajax'] == "desfazerUniao") {
		if (is_object($pagamento)) {
			if ($pagamento->fusao == 1) {
				$sql->consult($_p . "financeiro_fluxo", "*", "where id_registro=$pagamento->id and lixo=0");
				if ($sql->rows == 0) {
					$sql->update($_p . "financeiro_fluxo_recebimentos", "id_fusao=0", "where id_fusao=$pagamento->id");
					$sql->update($_p . "financeiro_fluxo_recebimentos", "lixo=1,lixo_data=now(),lixo_id_colaborador=$usr->id", "where id=$pagamento->id");
					$rtn = array('success' => true);
				} else {
					$rtn = array('success' => false, 'error' => 'Estorne todas as baixas desta parcela para desfazer a união!');
				}
			} else {
				$rtn = array('success' => false, 'Este pagamento não é uma união de pagamento!');
			}
		} else {
			$rtn = array('success' => false, 'error' => 'Pagamento não encontrado!');
		}
	} else if ($_POST['ajax'] == "receber") {
		$baixa = '';
		if (isset($_POST['id_baixa']) and is_numeric($_POST['id_baixa'])) {
			$sql->consult($_p . "financeiro_fluxo", "*", "where id='" . $_POST['id_baixa'] . "'");
			if ($sql->rows) {
				$baixa = mysqli_fetch_object($sql->mysqry);
			}
		}
		$id_banco = (isset($_POST['id_banco']) and is_numeric($_POST['id_banco'])) ? $_POST['id_banco'] : 0;

		$dataPagamento = '';
		if (isset($_POST['dataPagamento']) and !empty($_POST['dataPagamento'])) {
			list($dia, $mes, $ano) = explode("/", $_POST['dataPagamento']);
			if (checkdate($mes, $dia, $ano)) {
				$dataPagamento = $ano . "-" . $mes . "-" . $dia;
			}
		}



		if (is_object($baixa)) {
			if (!empty($dataPagamento)) {
				$sql->update($_p . "financeiro_fluxo", "pagamento=1,data_efetivado='" . $dataPagamento . "',pagamento_id_colaborador=$usr->id,id_banco='$id_banco'", "where id=$baixa->id");
				$rtn = array('success' => true);
			} else {
				$rtn = array('success' => false, 'error' => 'Defina uma data de pagamento válida!');
			}
		} else {
			$rtn = array('success' => false, 'error' => 'Baixa não encontrada!');
		}
	}

	header("Content-type: application/json");
	echo json_encode($rtn);
	die();
}
?>
<script type="text/javascript" src="js/aside.funcoes.js"></script>
<?php if (isset($apiConfig['Pagamentos'])) { ?>
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
				<div class="js-fin js-fin-programacao">
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
						<section class="js-desconto" style="display:none">
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
										echo '<option value="' . $x->id . '">' . utf8_encode($x->titulo) . '</option>';
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
	<script>
		let id_pagamento = 0;
		let _pagamentos = [];
		var dataHoje = '<?= date('d/m/Y'); ?>';

		const abrirAside = (tipo, index) => {
			if (tipo == 'contasAreceber') {
				let data = `ajax=getPagamentosBaixas&id_pagamento=${index}`;
				$.ajax({
					type: "POST",
					url: baseURLApiAsidePagamentos,
					data: data,
					success: function(rtn) {
						if (rtn.success) {
							let dados = rtn.dados
							_pagamentos[dados.id] = dados
							id_pagamento = dados.id
							// preenche o HEADER 
							$('#js-aside-asFinanceiro .js-index').val(dados.id);
							$('#js-aside-asFinanceiro .js-id_pagamento').val(dados.id);
							$('#js-aside-asFinanceiro .js-titulo').html(dados.titulo);
							$('#js-aside-asFinanceiro .js-dataOriginal').html(`${`${dados.data_vencimento.split('/')[2]}/${dados.data_vencimento.split('/')[1]}/${dados.data_vencimento.split('/')[0]}`}`);
							$('#js-aside-asFinanceiro .js-valorParcela').html(`R$ ${number_format(dados.valor, 2, ",", ".")}`);
							$('#js-aside-asFinanceiro .js-valorDesconto').html(`R$ ${number_format(dados.valor_desconto, 2, ",", ".")}`);
							$('#js-aside-asFinanceiro .js-valorCorrigido').html(`R$ ${number_format((dados.valor-dados.valor_desconto), 2, ",", ".")}`);
							$('#js-aside-asFinanceiro .js-saldoPagar').html(`R$ ${number_format((dados.saldoApagar), 2, ",", ".")}`);
							$('#js-aside-asFinanceiro .js-btn-pagamento').attr('data-id_pagamento', dados.id);
							// de fato abre o ASIDE
							$("#js-aside-asFinanceiro").fadeIn(100, function() {
								$("#js-aside-asFinanceiro .aside__inner1").addClass("active");
							});
							baixasAtualizar();
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
								text: "Algum erro ocorreu durante a busca por este pagamento",
								html: true,
								type: "error",
								confirmButtonColor: "#424242"
							});
						}
					},
					error: function(err) {
						swal({
							title: "Erro!",
							text: "Algum erro ocorreu durante a busca por este pagamento",
							html: true,
							type: "error",
							confirmButtonColor: "#424242"
						});
					}
				}).done(function() {});
			}
		}

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
		const creditoDebitoValorParcela = () => {
			let id_formapagamento = $('select.js-id_formapagamento option:selected').val();
			let tipo = $('select.js-id_formapagamento option:selected').attr('data-tipo');

			if (id_formapagamento.length > 0) {

				let valor = $('.js-valor').val().length > 0 ? unMoney($('.js-valor').val()) : 0;

				let valorCreditoDebito = 0;

				if (tipo == 'credito') {
					let id_bandeira = $('select.js-creditoBandeira').val();
					let id_operadora = $('select.js-creditoBandeira option:checked').attr('data-id_operadora');
					let parcela = eval($('select.js-parcelas option:selected').val());


					//alert(id_operadora+' - '+id_bandeira+' -'+parcela);
					if (id_operadora !== undefined && parcela !== undefined) {

						let taxa = 0;
						let cobrarTaxa = 0;
						if (_taxasCredito[id_operadora][id_bandeira][parcela]) taxa = _taxasCredito[id_operadora][id_bandeira][parcela];
						//	if(_taxasCreditoSemJuros[id_operadora][id_bandeira][parcela]) cobrarTaxa=eval(_taxasCreditoSemJuros[id_operadora][id_bandeira][parcela]);


						if (cobrarTaxa == 1) {
							valorCreditoDebito = taxa == 0 ? valor : (valor * (1 + (taxa / 100)));
						} else {
							valorCreditoDebito = valor;
						}

						valorCreditoDebito /= parcela;

						$('.js-valorCreditoDebito').val(number_format(valorCreditoDebito, 2, ",", "."));
						$('.js-valorCreditoDebitoTaxa').val(`${cobrarTaxa==1?"+":"-"} ${taxa}%`);
					}

				} else if (tipo == 'debito') {
					let taxa = eval($('select.js-debitoBandeira option:selected').attr('data-taxa'));
					let id_operadora = $('select.js-debitoBandeira option:checked').attr('data-id_operadora');
					let cobrarTaxa = eval($('select.js-debitoBandeira option:selected').attr('data-cobrarTaxa'));

					if (taxa !== undefined) {
						if (cobrarTaxa == 1) {
							valorCreditoDebito = taxa == 0 ? valor : (valor * (1 + (taxa / 100)));
						} else {
							valorCreditoDebito = valor;
						}
						$('.js-valorCreditoDebito').val(number_format(valorCreditoDebito, 2, ",", "."));
						$('.js-valorCreditoDebitoTaxa').val(`${cobrarTaxa==1?"+":"-"} ${taxa}%`);
					}

				} else {
					$('.js-valorCreditoDebitoTaxa').val('-');
					$('.js-valorCreditoDebito').val('-');

				}


			}
		}
		const baixasAtualizar = () => {
			let data = `ajax=baixas&id_pagamento=${id_pagamento}`;
			$.ajax({
				type: "POST",
				url: baseURLApiAsidePagamentos,
				data: data,
				success: function(rtn) {
					if (rtn.success) {
						$('#js-aside-asFinanceiro .js-baixas tr').remove();
						$('[name="alteracao"]').val("1")
						total = 0;
						let desconto = 0;
						let despesas = 0;
						if (rtn.baixas.length > 0) {
							let contador = 0;
							baixas = rtn.baixas
							rtn.baixas.forEach(x => {
								let textJuros = "";
								let textMulta = "";
								let TextDescontoIncargos = "";
								let ValorParcela = 0
								let pagamento = '';
								let alertVencimento = "";
								let taxaCartao = ""
								let btnReceber = ''
								let btnEstorno = ''
								if (x.tipoBaixa == "PAGAMENTO") {
									if (x.formaDePagamento.length > 0) {
										if (x.id_formapagamento == 2) {
											// pagamento = `${x.formaDePagamento}<font color=#999><br />Parcela ${x.parcela} de ${x.parcelas}</font>`;
											pagamento = `${x.formaDePagamento}<font color=#999><br /></font>`;
										} else {
											pagamento = x.formaDePagamento;
										}
									}
								} else {
									pagamento = `<span class="iconify" data-icon="il:dialog" data-inline="true" data-height="18"></span> ${x.obs}`;
								}

								if (x.tipoBaixa == "DESCONTO") {
									desconto += x.valor;
								} else if (x.tipoBaixa == "DESPESA") {
									despesas += x.valor;
								} else {

									total += x.valor;
								}

								let btns = ``;

								if (x.pago == 1) {
									icon = `<span class="iconify tooltip" title="pago" data-icon="akar-icons:circle-check" data-inline="true" style="color:green"></span>`;
									btnReceber = `<button type="button" class="button button_green" data-id_baixa="${x.id_baixa}" data-index="${contador}" title="Pagar" disabled><span>Recebido</span></button>`
								} else {
									btns = `<a href="javascript:;" class="js-estorno button button__sec" data-id_baixa="${x.id_baixa}" title="Estorno"><span class="iconify" data-icon="typcn:arrow-back" data-inline="false"></span></a>`;
									btnEstorno = `<a href="javascript:;" class="js-estorno button button__sec" data-id_baixa="${x.id_baixa}" title="Estorno"><i class="iconify" data-icon="fluent:delete-24-regular"></i></span></a>`;
									if (x.tipoBaixa == "PAGAMENTO") {
										btns += ` <a href="javascript:;" class="js-receber button button__sec" data-id_baixa="${x.id_baixa}" data-index="${contador}" title="Pagar"><span class="iconify" data-icon="ic:round-attach-money" data-inline="false"></span></a>`;
										btnReceber = `<a href="javascript:;" class="js-receber button button__sec" data-id_baixa="${x.id_baixa}" data-index="${contador}" title="Pagar"><i class="iconify" data-icon="fluent:checkmark-24-filled"></i><span>Receber</span></a>`
									}
									if (x.vencido) {
										icon = `<span class="iconify tooltip" title="vencido" data-icon="icons8:cancel" data-inline="true" style="color:red"></span>`;
									} else {
										icon = `<span class="iconify tooltip" title="em aberto" data-icon="bx:bx-hourglass" data-inline="true" style="color:orange"></span>`;
									}
								}

								contador++;
								ValorParcela += x.valor;
								if (x.valorMulta > 0) {
									ValorParcela += parseFloat(x.valorMulta);
									textMulta = `<span style="font-size:12px;color:var(--cinza4)">Multa: R$${number_format(x.valorMulta,2,",",".")}</span></br>`
								}
								if (x.valorJuros > 0) {
									ValorParcela += parseFloat(x.valorJuros);
									textJuros = `<span style="font-size:12px;color:var(--cinza4)">Juros: R$ ${number_format(x.valorJuros,2,",",".")}</span></br>`
								}
								if (x.descontoMultasJuros > 0) {
									ValorParcela -= parseFloat(x.descontoMultasJuros);
									TextDescontoIncargos = `<span style="font-size:12px;color:var(--cinza4)">Descontos: R$ ${number_format(x.descontoMultasJuros,2,",",".")}</span></br>`
								}
								// let diferenca = (new Date().getTime()-new Date(`${x.vencimento.split('/')[2]}/${x.vencimento.split('/')[1]}/${x.vencimento.split('/')[0]}`).getTime()) / (1000 * 60 * 60 * 24);
								// if(diferenca>=1){
								// 	alertVencimento = `<span style="color:red">FATURA VENCIDA!</span>` 
								// }
								if (x.tipoBaixa == "PAGAMENTO") {
									if (x.formaDePagamento.length > 0) {
										if (x.id_formapagamento == 2) {
											taxaCartao = `<span style="font-size:12px;color:var(--cinza4)">Taxa Cartão: R$${number_format(((ValorParcela)*parseFloat(x.taxa)/100),2,",",".")}</span><br>`
										}
									}
								}

								html = `<tr class="">
							<td>${icon}</td>
							<td>${x.data}<br>${alertVencimento}</td>
							<td>${x.tipoBaixa}</td>
							<td>${pagamento}</td>
							<td>
								<font style="font-size:18px">${number_format(ValorParcela,2,",",".")}</font></br>
								${taxaCartao}
								${textMulta}
								${textJuros}
								${TextDescontoIncargos}
							</td>
							<td>${btnReceber}</td>
							<td>${btnEstorno}</td>
						</tr>`;
								$('.js-tr .js-recibo, .js-tr .js-estorno').tooltipster({
									theme: "borderless"
								});
								$('#js-aside-asFinanceiro .js-baixas').append(html);
							});
						} else {
							$('#js-aside-asFinanceiro .js-baixas').append('<tr class="js-tr"><td colspan="4"><center>Nenhuma baixa cadastrada</center></td></tr>');
						}
						$('.js-valorDesconto').val(number_format(desconto, 2, ",", "."));
						$('.js-valorDespesa').val(number_format(despesas, 2, ",", "."));
						baixasAtualizarValores();
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
				error: function(err) {
					swal({
						title: "Erro!",
						text: "Algum erro ocorreu durante a baixa deste pagamento!",
						html: true,
						type: "error",
						confirmButtonColor: "#424242"
					});
				}
			})
		}
		const baixasAtualizarValores = () => {
			let valorParcela = unMoney($('.js-valorParcela').html()) ?? 0
			let desconto = unMoney($('.js-valorDesconto').html());
			let pagamentoIndex = $('#js-aside-asFinanceiro .js-index').val();
			let pagamento = _pagamentos[pagamentoIndex]
			let valorCorrigido = valorParcela;
			valorCorrigido -= desconto;
			let saldoPagar = pagamento.saldoApagar

			$('.js-saldoPagar').html(`R$ ${number_format(saldoPagar, 2, ",", ".")}`);
			$('.js-valorCorrigido').html(`R$ ${number_format(valorCorrigido, 2, ",", ".")}`);
			if (saldoPagar <= 0) {
				$('.js-fieldset-pagamentos').hide();
			} else {
				$('.js-fieldset-pagamentos').show();
			}

		}

		$(function() {
			const _taxaBandeiras = <?= json_encode($taxasBandeiras) ?>;
			pagamentosAtualizaCampos('');
			// clica para estornar 
			$('#js-aside-asFinanceiro .js-baixas').on('click', '.js-estorno', function() {
				id_pagamento = $('#js-aside-asFinanceiro .js-id_pagamento').val();
				let id_baixa = $(this).attr('data-id_baixa')
				let data = `ajax=baixaEstornar&id_baixa=${id_baixa}&id_pagamento=${id_pagamento}`;
				let obj = $(this);
				let objHTMLAntigo = obj.html();
				swal({
					title: "Atenção",
					text: "Você tem certeza que deseja estornar esta baixa?",
					type: "warning",
					showCancelButton: true,
					confirmButtonColor: "#DD6B55",
					confirmButtonText: "Sim!",
					cancelButtonText: "Não",
					closeOnConfirm: true,
					closeOnCancel: true
				}, function(isConfirm) {
					if (isConfirm) {
						obj.html('<span class="iconify" data-icon="eos-icons:loading"></span>');
						$.ajax({
							type: "POST",
							url: baseURLApiAsidePagamentos,
							data: data,
							success: function(rtn) {
								if (rtn.success) {
									baixasAtualizar();
									$('[name="alteracao"]').val("1")
								} else if (rtn.error) {
									swal({
										title: "Erro!",
										text: rtn.error,
										html: true,
										type: "error",
										confirmButtonColor: "#424242"
									});
									obj.html(objHTMLAntigo);
								} else {
									swal({
										title: "Erro!",
										text: "Algum erro ocorreu durante o estorno desta baixa!",
										html: true,
										type: "error",
										confirmButtonColor: "#424242"
									});
									obj.html(objHTMLAntigo);
								}
							},
							error: function(err) {
								swal({
									title: "Erro!",
									text: "Algum erro ocorreu durante o estorno desta baixa!",
									html: true,
									type: "error",
									confirmButtonColor: "#424242"
								});
								obj.html(objHTMLAntigo);
							}
						}).done(function() {});
					}
				});


			});

			$('#js-aside-asFinanceiro .js-baixas').on('click', '.js-estornoPagamento', function() {
				let pagamentoIndex = $('#js-aside-asFinanceiro .js-index').val();
				let baixaIndex = $(this).attr('data-index');

				if (_pagamentos[pagamentoIndex]) {
					let pagamento = _pagamentos[pagamentoIndex];
					if (pagamento.baixas[baixaIndex]) {
						let baixa = pagamento.baixas[baixaIndex];
						if (baixa.pago == "0") {
							swal({
								title: "Erro!",
								text: "Esta baixa não foi recebida ainda!",
								html: true,
								type: "error",
								confirmButtonColor: "#424242"
							});
						} else {
							let id_baixa = pagamento.baixas[baixaIndex].id_baixa;
							let id_parcela = pagamento.id_parcela;
							let data = `ajax=baixaEstornarPagamento&id_baixa=${id_baixa}&id_pagamento=${id_parcela}`;
							swal({
								title: "Atenção",
								text: "Você tem certeza que deseja estornar este pagamento?",
								type: "warning",
								showCancelButton: true,
								confirmButtonColor: "#DD6B55",
								confirmButtonText: "Sim!",
								cancelButtonText: "Não",
								closeOnConfirm: true,
								closeOnCancel: true
							}, function(isConfirm) {
								if (isConfirm) {
									$.ajax({
										type: "POST",
										url: baseURLApiAsidePagamentos,
										data: data,
										success: function(rtn) {
											if (rtn.success) {
												_pagamentos[pagamentoIndex].baixas[baixaIndex].pago = "0";
												$('#js-aside-asFinanceiro-receber .aside-close').click();
												$('#js-aside-asFinanceiro .aside-close').click();
												$('.js-pagamento-item-' + id_parcela).click();
												$('[name="alteracao"]').val("1")
												document.location.reload();
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
													text: "Algum erro ocorreu durante o estorno desta baixa!",
													html: true,
													type: "error",
													confirmButtonColor: "#424242"
												});
											}
										},
										error: function() {
											swal({
												title: "Erro!",
												text: "Algum erro ocorreu durante o estorno desta baixa!",
												html: true,
												type: "error",
												confirmButtonColor: "#424242"
											});
										}
									});
								}
							});
						}

					}
				}
			});

			$('#js-aside-asFinanceiro .js-baixas').on('click', '.js-receber', function() {
				let pagamentoIndex = $('#js-aside-asFinanceiro .js-index').val();
				let baixaIndex = $(this).attr('data-index');
				if (_pagamentos[pagamentoIndex]) {
					let pagamento = _pagamentos[pagamentoIndex];
					if (pagamento.baixas[baixaIndex]) {
						let baixa = pagamento.baixas[baixaIndex];
						let valorParcela = baixa.valor
						let valorJuros = baixa.valorJuros ?? 0
						let valorMulta = baixa.valorMulta ?? 0
						let descontoMultasJuros = baixa.descontoMultasJuros ?? 0
						valorParcela = valorParcela + parseFloat(valorJuros) + parseFloat(valorMulta) + parseFloat(descontoMultasJuros)
						$('#js-aside-asFinanceiro-receber .js-index').val(baixaIndex);
						$('#js-aside-asFinanceiro-receber .js-dataPagamento').val(dataHoje);
						$('#js-aside-asFinanceiro-receber .js-vencimentoParcela').val(baixa.data);
						$('#js-aside-asFinanceiro-receber .js-valorParcela').val(number_format((valorParcela), 2, ",", "."));
						$('#js-aside-asFinanceiro-receber .js-formaPagamento').val(baixa.formaDePagamento);
						$('#js-aside-asFinanceiro-receber .js-fieldset-conta').show();
						$("#js-aside-asFinanceiro-receber").fadeIn(100, function() {
							$("#js-aside-asFinanceiro-receber .aside__inner1").addClass("active");
						});
					}
				}
			});

			$('#js-aside-asFinanceiro-receber .js-btn-receber').click(function() {
				let pagamentoIndex = $('#js-aside-asFinanceiro .js-index').val();
				let baixaIndex = $('#js-aside-asFinanceiro-receber .js-index').val();
				let dataPagamento = $('#js-aside-asFinanceiro-receber .js-dataPagamento').val();
				let bancoPagamento = $('#js-aside-asFinanceiro-receber .js-id_banco').val();

				let erro = '';
				if (!_pagamentos[pagamentoIndex].baixas[baixaIndex]) erro = 'Pagamento não encontrado!';
				else if (dataPagamento.length == 0) erro = 'Defina a Data de Pagamento!';


				if (erro.length > 0) {
					swal({
						title: "Erro!",
						text: erro,
						html: true,
						type: "error",
						confirmButtonColor: "#424242"
					});
				} else {

					if (_pagamentos[pagamentoIndex].baixas[baixaIndex]) {
						let id_baixa = _pagamentos[pagamentoIndex].baixas[baixaIndex].id_baixa;
						let id_parcela = _pagamentos[pagamentoIndex].id_parcela;

						let obj = $(this);
						let objHTMLAntigo = $(this).html();

						if (obj.attr('data-loading') == 0) {
							obj.html('<span class="iconify" data-icon="eos-icons:loading"></span>');
							obj.attr('data-loading', 1);

							let data = `ajax=receber&id_baixa=${id_baixa}&dataPagamento=${dataPagamento}&id_banco=${bancoPagamento}`;

							$.ajax({
								type: "POST",
								url: baseURLApiAsidePagamentos,
								data: data,
								success: function(rtn) {
									if (rtn.success) {
										pagamentos[pagamentoIndex].baixas[baixaIndex].pago = "1";
										$('#js-aside-asFinanceiro-receber .aside-close').click();
										$('#js-aside-asFinanceiro .aside-close').click();
										$('.js-pagamento-item-' + id_parcela).click();
										document.location.reload();
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
											text: 'Algum erro ocorreu. Tente novamente!',
											html: true,
											type: "error",
											confirmButtonColor: "#424242"
										});
									}
								}
							}).done(function() {
								obj.attr('data-loading', 0);
								obj.html(objHTMLAntigo);
							})

						}
					}
				}
			})

			$('.js-tr-fusao').click(function() {
				let idPagamento = $(this).parent().attr('data-id_pagamento');

				if ($(`.js-fusao-${idPagamento}:hidden`).length > 0) {
					$(`.js-fusao-${idPagamento}`).show();
				} else {
					$(`.js-fusao-${idPagamento}`).hide();
				}

			})
			$('#cal-popup').on('click', '.js-btn-pagamento', function() {
				let idPagamento = $(this).attr('data-id_pagamento');
				$.fancybox.open({
					type: `ajax`,
					src: `box/boxPacientePagamentos.php?id_pagamento=${idPagamento}`,
					opts: {
						'beforeClose': function() {
							document.location.reload();
						}
					}
				});
				return false;
			});

			$('#cal-popup').on('click', '.js-btn-pagamento-excluir', function() {
				let idPagamento = _pagamentos[index].id_parcela;
				swal({
						title: "Atenção",
						text: "Você tem certeza que deseja remover este registro?",
						type: "warning",
						showCancelButton: true,
						confirmButtonColor: "#DD6B55",
						confirmButtonText: "Sim!",
						cancelButtonText: "Não",
						closeOnConfirm: false,
						closeOnCancel: false
					},
					function(isConfirm) {
						if (isConfirm) {
							document.location.href = '?<#?= "id_paciente=$paciente->id&id_pagamento="; ?>' + idPagamento;
						} else {
							swal.close();
						}
					});

			})
			// quando digita o valor a ser pago na parcela ASIDE
			$('.js-valor').keyup(function() {
				let idPagamento = $('.js-id_pagamento').val()
				let pagamento = _pagamentos.filter((item) => {
					return item.id_parcela == idPagamento
				})[0]
				let ValorDigitado = unMoney($(this).val())
				if (ValorDigitado > pagamento.saldoApagar) {
					swal({
						title: "Erro!",
						text: "o Valor da Parcela Não Pode ser Maior que o Saldo a Pagar!",
						html: true,
						type: "error",
						confirmButtonColor: "#424242"
					});
					$(this).val(0);
					ValorDigitado = pagamento.saldoApagar
				}
				let data = new Date(`${pagamento.data_vencimento.split('-')[2]}/${pagamento.data_vencimento.split('-')[1]}/${pagamento.data_vencimento.split('-')[0]}`);
				let hoje = new Date();
				let diferenca = (hoje.getTime() - data.getTime()) / (1000 * 60 * 60 * 24);
				if (diferenca >= 1) {
					if ($('.js-aplicar-multas-juros').prop('checked') == true) {
						$('.js-multa').show()
						let ValorMulta = (ValorDigitado * ((_clinica[1].politica_multas) / 100))
						let ValorJuros = (ValorDigitado * (((_clinica[1].politica_juros) / 30) / 100)) * Math.floor(diferenca)
						$('.js-valorMultas').text(number_format(ValorMulta, 2, ",", "."))
						$('.js-valorJuros').text(number_format(ValorJuros, 2, ",", "."))
						$('.js-TotalaPagar').text(number_format(ValorDigitado + ValorMulta + ValorJuros, 2, ",", "."))
					}
				} else {
					$('.js-multa').hide()
					$('.js-valorMultas').text(number_format(0, 2, ",", "."))
					$('.js-valorJuros').text(number_format(0, 2, ",", "."))
					$('.js-TotalaPagar').text(number_format(0, 2, ",", "."))
					$('.js-descontoMultasJuros').text(number_format(0, 2, ",", "."))
				}
			})
			// quando digita o valor de desconto que deseja Dar na Parcela
			$('.js-descontoMultasJuros').keyup(function() {
				let ValorDigitado = unMoney($(this).val())
				let valorOriginal = unMoney($('.js-valor').val())
				let ValorJuros = unMoney($('.js-valorJuros').text())
				let ValorMulta = unMoney($('.js-valorMultas').text())

				if (ValorDigitado > (valorOriginal + ValorJuros + ValorMulta)) {
					swal({
						title: "Erro!",
						text: "Voce Não Pode Ofertar um Desconto Maior que o valor da Parcela Somando aos encargos!",
						html: true,
						type: "error",
						confirmButtonColor: "#424242"
					});
					($(this).val("0"));
					ValorDigitado = 0
				}
				$('.js-TotalaPagar').text(number_format((valorOriginal + ValorJuros + ValorMulta) - ValorDigitado, 2, ",", "."))
			})
			// quando clica para ativa e desativar o juros
			$('.js-aplicar-multas-juros').click(function() {
				let idPagamento = $('.js-id_pagamento').val()
				let pagamento = _pagamentos.filter((item) => {
					return item.id_parcela == idPagamento
				})[0]
				let data = new Date(`${pagamento.data_vencimento.split('-')[2]}/${pagamento.data_vencimento.split('-')[1]}/${pagamento.data_vencimento.split('-')[0]}`);
				let hoje = new Date();
				let diferenca = (hoje.getTime() - data.getTime()) / (1000 * 60 * 60 * 24);
				if (diferenca >= 1) {
					if ($(this).prop('checked') == true) {
						$('.js-multa').show()
						let ValorDigitado = unMoney($('.js-valor').val())
						if (ValorDigitado > 0) {
							let ValorMulta = (ValorDigitado * ((_clinica[1].politica_multas) / 100))
							let ValorJuros = (ValorDigitado * (((_clinica[1].politica_juros) / 100))) * Math.floor(diferenca)
							$('.js-valorMultas').text(number_format(ValorMulta, 2, ",", "."))
							$('.js-valorJuros').text(number_format(ValorJuros, 2, ",", "."))
							$('.js-TotalaPagar').text(number_format(ValorDigitado + ValorJuros + ValorMulta, 2, ",", "."))
						}
						if ($('.js-id_formapagamento option:checked').attr('data-tipo') == 'credito') {
							$(".js-parcelas").trigger("change");
						} else if ($('.js-id_formapagamento option:checked').attr('data-tipo') == 'debito') {
							pagamentosAtualizaCampos($('.js-id_formapagamento.js-tipoPagamento'))
						}
					} else {
						$('.js-multa').hide()
						let ValorDigitado = unMoney($('.js-valor').val())
						$('.js-valorMultas').text(number_format(0, 2, ",", "."))
						$('.js-valorJuros').text(number_format(0, 2, ",", "."))
						$('.js-descontoMultasJuros').val(number_format(0, 2, ",", "."))
						$('.js-TotalaPagar').text(number_format(ValorDigitado, 2, ",", "."))
						let tipoPagamento = $('.js-id_formapagamento option:checked').attr('data-tipo');
						if ($('.js-id_formapagamento option:checked').attr('data-tipo') == 'credito') {
							$(".js-parcelas").trigger("change");
						} else if ($('.js-id_formapagamento option:checked').attr('data-tipo') == 'debito') {
							pagamentosAtualizaCampos($('.js-id_formapagamento.js-tipoPagamento'))
						}
					}
				}
			})
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
							url: baseURLApiAsidePagamentos,
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
								url: baseURLApiAsidePagamentos,
								data: data,
								success: function(rtn) {
									if (rtn.success) {
										let pagamentoIndex = $('#js-aside-asFinanceiro .js-index').val();
										_pagamentos[pagamentoIndex].saldoApagar = unMoney(_pagamentos[pagamentoIndex].saldoApagar) - unMoney(valor)
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

			$('.js-btn-fechar').click(function() {
				$('.cal-popup').hide();
				document.location.reload();
			});
		})
	</script>
<?php } ?>