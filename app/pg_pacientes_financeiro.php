<?php

	require_once("lib/conf.php");
	require_once("usuarios/checa.php");

	// config da clinica multas/juros
	$_clinica = array();
	$sql->consult($_p . "clinica", "*", "order by id asc");
	while ($x = mysqli_fetch_object($sql->mysqry)) {
		$_clinica[$x->id] = ["id" => $x->id, "clinica_nome" => utf8_encode($x->clinica_nome), "instagram" => utf8_encode($x->instagram), "site" => utf8_encode($x->site), "email" => utf8_encode($x->email), "politica_multas" => $x->politica_multas, "politica_juros" => $x->politica_juros];
	}

	$_formasDePagamento = array();
	$optionFormasDePagamento = '';
	$sql->consult($_p . "parametros_formasdepagamento", "*", "order by titulo asc");
	while ($x = mysqli_fetch_object($sql->mysqry)) {
		$_formasDePagamento[$x->id] = $x;
		$optionFormasDePagamento .= '<option value="' . $x->id . '" data-tipo="' . $x->tipo . '">' . utf8_encode($x->titulo) . '</option>';
	}

	if (isset($_POST['ajax'])) {
		$rtn = array();
		$pagamento = '';
		if (isset($_POST['id_pagamento']) and is_numeric($_POST['id_pagamento'])) {
			$id_pagamento =  $_POST['id_pagamento'];
			$sql->consult($_p . "financeiro_fluxo_recebimentos", "*", "where id='$id_pagamento'");
			if ($sql->rows) {
				$pagamento = mysqli_fetch_object($sql->mysqry);
			}
		}
		if ($_POST['ajax'] == "unirPagamentos") {
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
									data_vencimento='" . (isset($_POST['dataVencimento']) ? invDate($_POST['dataVencimento']) : 'NOW()') . "',
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
		}
		header("Content-type: application/json");
		echo json_encode($rtn);
		die();
	}
	include "includes/header.php";
	include "includes/nav.php";

	require_once("includes/header/headerPacientes.php");

	$_table = $_p . "financeiro_fluxo_recebimentos";
	$_page = basename($_SERVER['PHP_SELF']);


	$_formasDePagamento = array();
	$optionFormasDePagamento = '';
	$sql->consult($_p . "parametros_formasdepagamento", "*", "order by titulo asc");
	while ($x = mysqli_fetch_object($sql->mysqry)) {
		$_formasDePagamento[$x->id] = $x;
		$optionFormasDePagamento .= '<option value="' . $x->id . '">' . utf8_encode($x->titulo) . '</option>';
	}


	$_planos = array();
	$sql->consult($_p . "parametros_planos", "*", "where lixo=0");
	while ($x = mysqli_fetch_object($sql->mysqry)) {
		$_planos[$x->id] = $x;
	}

	if (isset($_GET['id_pagamento']) and is_numeric($_GET['id_pagamento'])) {
		$sql->consult($_p . "financeiro_fluxo_recebimentos", "*", "where id='" . $_GET['id_pagamento'] . "' and id_pagante=$paciente->id");
		if ($sql->rows) {
			$pag = mysqli_fetch_object($sql->mysqry);
			$sql->update($_p . "financeiro_fluxo_recebimentos", "lixo=1,lixo_data=now()", "where id=$pag->id");
		}
	}

	$where = "WHERE id_pagante=$paciente->id and id_fusao=0 and lixo=0 order by data_emissao desc, id asc";
	$sql->consult($_p . "financeiro_fluxo_recebimentos", "*", $where);

	$valor = array(
		'aReceber' => 0,
		'valorRecebido' => 0,
		'valoresVencido' => 0,
		'valorTotal' => 0,
		'valorJuros' => 0,
		'valorMulta' => 0,
		"definirPagamento" => 0
	);

	$registros = array();
	$tratamentosIDs = array(-1);
	$pagamentosIDs = array(-1);
	$pagamentosUnidos = array(-1);
	while ($x = mysqli_fetch_object($sql->mysqry)) {
		if ($x->id_fusao == 0) {
			$registros[] = $x;
		}
		$tratamentosIDs[] = $x->id_tratamento;
		$pagamentosIDs[$x->id] = $x->id;

		if ($x->fusao == 1) $pagamentosUnidos[] = $x->id;

		//if ($x->fusao == 0) $valor['valorTotal'] += $x->valor;
		$valor['valorTotal'] += $x->valor;
	}

	$_subpagamentos = array();
	$sql->consult($_table, "*", "where id_fusao IN (" . implode(",", $pagamentosUnidos) . ") and lixo=0");
	while ($x = mysqli_fetch_object($sql->mysqry)) {
		$_subpagamentos[$x->id_fusao][] = $x;
	}

	$_baixas = array();
	$pagamentosComBaixas = array();
	$sql->consult($_p . "financeiro_fluxo", "*", "WHERE id_registro IN (" . implode(",", $pagamentosIDs) . ") and lixo=0 order by data_vencimento asc");
	if ($sql->rows) {
		while ($x = mysqli_fetch_object($sql->mysqry)) {
			$_baixas[$x->id_registro][] = $x;
			$pagamentosComBaixas[$x->id] = $x->id_registro;
		}
	}


	$sql->consult($_p . "pacientes_tratamentos", "*", "where id IN (" . implode(",", $tratamentosIDs) . ")");
	while ($x = mysqli_fetch_object($sql->mysqry)) $_tratamentos[$x->id] = $x;

	$valorAReceber = $saldoAPagar = $valorDefinido = $multas = $juros = 0;

	foreach ($registros as $x) {
		$valorDefinido = 0;
		if (isset($_baixas[$x->id])) {
			$dataUltimoPagamento = date('d/m/Y', strtotime($_baixas[$x->id][count($_baixas[$x->id]) - 1]->data));
			foreach ($_baixas[$x->id] as $v) {
				//if ($v->lixo == 0 && $v->tipoBaixa == 'pagamento') {
				if ($v->lixo == 0) {
					$valor['valorJuros'] += $v->valor_multa;
					$valor['valorMulta'] += $v->valor_taxa;
					$valorDefinido += $v->valor;
					$atraso = (strtotime($v->data_vencimento) - strtotime(date('Y-m-d'))) / (60 * 60 * 24);
					if ($v->pagamento == 1) {
						$valor['valorRecebido'] += $v->valor;
					} else if ($atraso < 0) {
						$valor['valoresVencido'] += $v->valor;
					} else if ($v->pagamento == 0) {
						$valor['aReceber'] += $v->valor;
					}
				}
			}
			if ($x->valor > $valorDefinido) {
				$valor['definirPagamento'] += ($x->valor - $valorDefinido);
			}
		} else {
			$atraso = (strtotime($x->data_vencimento) - strtotime(date('Y-m-d'))) / (60 * 60 * 24);
			if ($atraso < 0 and $x->pago == 0) {
				$valor['valoresVencido'] += $x->valor;
			} else {
				$valor['definirPagamento'] += $x->valor;
			}
			//$valor['aReceber']+=$x->valor;
		}
	}

?>
<script type="text/javascript">
	const _clinica = <?= json_encode($_clinica) ?>;
	var baixas = [];
	var _pagamentosList = [];
	$(function() {
		<?php
			if (isset($_GET['unirPagamentos'])) {
			?>
		$('.js-btn-unirPagamentos').click(function() {
			let dataVencimento = $('.js-dataVencimento').val();
			if (dataVencimento.length == 0 || !validaData(dataVencimento)) {
				swal({
					title: "Erro!",
					text: "Digite uma data de vencimento válida!",
					html: true,
					type: "error",
					confirmButtonColor: "#424242"
				});
			} else if ($('.js-checkbox-pagamentos:checked').length <= 1) {
				swal({
					title: "Erro!",
					text: "Selecione pelo menos 2 pagamentos",
					html: true,
					type: "error",
					confirmButtonColor: "#424242"
				});
			} else {
				let pagamentosIds = $('form.js-form-pagamentos').serialize();
				let data = `ajax=unirPagamentos&dataVencimento=${dataVencimento}&${pagamentosIds}`;

				$.ajax({
					type: "POST",
					data: data,
					success: function(rtn) {
						if (rtn.success) {
							document.location.href = '<?= "$_page?$url"; ?>';
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
								text: "Algum erro ocorreu durante a baixa deste pagamento",
								html: true,
								type: "error",
								confirmButtonColor: "#424242"
							});
						}
					},
					error: function() {
						swal({
							title: "Erro!",
							text: "Algum erro ocorreu durante a baixa deste pagamento.",
							html: true,
							type: "error",
							confirmButtonColor: "#424242"
						});
					}
				})
			}
		});

		$('.js-checkbox-pagamentos').click(function() {
			let id_tratamento = $(this).attr('data-id_tratamento');
			if ($(this).prop('checked') == true) {
				$('.js-checkbox-pagamentos').hide();
				$(`.js-checkbox-pagamentos[data-id_tratamento=${id_tratamento}]`).show();
				$(`.js-checkbox-pagamentos-disabled`).show();
				$(`.js-checkbox-pagamentos-disabled[data-id_tratamento=${id_tratamento}]`).hide();
			} else {
				if ($(`.js-checkbox-pagamentos:checked`).length > 0) {

				} else {

					$('.js-checkbox-pagamentos').show();
					$(`.js-checkbox-pagamentos-disabled`).hide();
				}
			}
		});

		<?php
			} else {
			?>
		// Quando clica para abrir o aside
		$('.js-pagamento-item').click(function() {
			let id = $(this).attr('data-id');
			abrirAside('contasAreceber', id)
		});

		<?php
			}
			?>

	});
</script>

<main class="main" id="body" data-pagina="pacientes_financeiro">
    <div class="main__content content">

        <section class="filter">
            <div class="filter-group">
                <div class="filter-title">
                    <h1>Ficha do Paciente</h1>
                </div>
            </div>
        </section>
        <script type="text/javascript">
        $(function() {
            $('.js-item').click(function() {
                let id = $(this).attr('data-id');
                document.location.href =
                    `pg_pacientes_planosdetratamento_form.php?edita=${id}<?= empty($url) ? "" : "&" . $url; ?>`;
            })
        })
        </script>
        <section class="grid">
            <div class="box box-col">
                <? #php require_once("includes/submenus/subPacientesFichaDoPaciente.php");
				?>
                <div class="box-col__inner1">

                    <section class="filter">
                        <div class="filter-group"></div>
                        <div class="filter-group">
                            <div class="filter-form form">
                                <dl>
                                    <dd>
                                        <!-- <a href="pacientes-plano-form.php" class="button button_main"><i class="iconify" data-icon="fluent:add-circle-24-regular"></i><span>Nova Cobrança</span></a> -->
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </section>

                    <div class="box">
                        <section class="filter">
                            <?php
								if (isset($_GET['unirPagamentos'])) {
							?>
                            <div class="filter-group js-unir">
                                <div class="filter-form form">
                                    <dl>
                                        <dd><input type="tel" name="" class="js-dataVencimento data datecalendar"
                                                placeholder="Nova data de vencimento" style="width:190px;" /></dd>
                                    </dl>
                                    <dl>
                                        <dd>
                                            <a href="javascript:;" class="button button_main js-btn-unirPagamentos "><i
                                                    class="iconify"
                                                    data-icon="fluent:link-square-24-filled"></i><span>Salvar</span></a>
                                            <a href="<?= $_page . "?" . $url; ?>" class="button tooltip"
                                                title="Cancelar" style="background: var(--vermelho);color:#FFF;"><i
                                                    class="iconify"
                                                    data-icon="topcoat:cancel"></i><span>Cancelar</span></a>


                                </div>
                            </div>
                            <?php
								} else {
							?>
                            <div class="filter-group">
                                <div class="filter-form form">
                                    <dl>
                                        <dd>
										<a href="<?= $_page . "?unirPagamentos=1&$url"; ?>" class="button"><i class="iconify"data-icon="fluent:link-square-24-filled"></i><span>Unir Pagamentos</span></a>
										<button class="button" id='pagamento_avulso-receber' data-tipoAvulso="paciente"><i class="iconify"data-icon="mdi:account-payment"></i><span>Pagamento Avulso</span></button>
                                    </dl>
                                </div>
                            </div>
                            <?php
							}
							?>

                            <div class="filter-group">
                                <div class="filter-title">
                                    <p style="color:var(--cinza5);font-size:18px">Total<br /><strong>R$
                                            <?= number_format($valor['valorTotal'], 2, ",", "."); ?></strong></p>
                                </div>
                                <div class="filter-title">
                                    <p style="font-size:13px">A receber<br /><strong>R$
                                            <?= number_format(($valor['aReceber']), 2, ",", "."); ?></strong></p>
                                </div>
                                <div class="filter-title">
                                    <p style="color:var(--laranja);font-size:13px">Definir Pagamento<br /><strong>R$
                                            <?= number_format($valor['definirPagamento'], 2, ",", "."); ?></strong></p>
                                </div>
                                <div class="filter-title">
                                    <p style="color:var(--verde);font-size:13px">Recebido<br /><strong>R$
                                            <?= number_format($valor['valorRecebido'], 2, ",", "."); ?></strong></p>
                                </div>
                                <div class="filter-title">
                                    <p style="color:var(--vermelho);font-size:13px">Vencido<br /><strong>R$
                                            <?= number_format($valor['valoresVencido'], 2, ",", "."); ?></strong></p>
                                </div>


                            </div>
                        </section>

                        <form class="js-form-pagamentos" onsubmit="return false">
                            <div class="list1">
                                <table class="js-table-pagamentos">
                                    <?php
										$parcelasTratamentos = array();
										$DefinirPagamento = 0;
										foreach ($registros as $x) {
											if (!isset($parcelasTratamentos[$x->id_tratamento])) {
												$parcelasTratamentos[$x->id_tratamento] = 0;
											}
											$parcelasTratamentos[$x->id_tratamento]++;
										}



										$pagamentosJSON = array();
										$numeroParcela = array();

										foreach ($registros as $x) {

											$opacity = 1;
											if (isset($_GET['unirPagamentos'])) {
												if (isset($pagamentosComBaixas[$x->id])) {
													$opacity = 0.3;
												}
											}

											$saldoAPagar = $x->valor;
											$valorCorrigido = $x->valor;
											$valorPago = $descontos = $multas = 0;
											$dataUltimoPagamento = '-';
											$valorDesconto = $valorDespesa = 0;
											$valorMulta = $valorJuros = 0;
											if (isset($_baixas[$x->id])) {
												$dataUltimoPagamento = date('d/m/Y', strtotime($_baixas[$x->id][count($_baixas[$x->id]) - 1]->data));
												foreach ($_baixas[$x->id] as $v) {
													$v->valor = number_format($v->valor, 3, ".", "");
													$saldoAPagar -= $v->valor;
													$valorPago += $v->valor;
													$valorMulta += $v->valor_multa;
													$valorJuros += $v->valor_juros;
												}
											}

											$saldoAPagar = $saldoAPagar;
											$atraso = (strtotime($x->data_vencimento) - strtotime(date('Y-m-d'))) / (60 * 60 * 24);


											$status = '';
											$icone = '';
											$baixas = $subpagamentos = array();
											// verifica se possui baixas 
											if (isset($_baixas[$x->id])) {
												$baixaVencida = false;
												$baixaEmAberta = false;
												$contador = 0;
												foreach ($_baixas[$x->id] as $b) {
													$contador++;
													$formaobs = '';
													//	$baixaVencida=false;
													if ((strtotime($b->data_vencimento) < strtotime(date('Y-m-d'))) && $b->pagamento == 0) {
														$baixaVencida = true;
													} else {
														if ($b->pagamento == 0) {
															$baixaEmAberta = true;
														}
													}
													$baixas[] = array(
														"id_baixa" => (int)$b->id,
														"vencimento" => $b->data_vencimento,
														"data" => date('d/m/Y', strtotime($b->data_vencimento)),
														"descontoMultasJuros" => (float)$b->valor_desconto,
														"valor" => (float)$b->valor,
														"tipoBaixa" => ($b->desconto == 0) ? "PAGAMENTO" : "DESCONTO",
														"id_formapagamento" => (int)$b->id_formapagamento,
														"formaDePagamento" => isset($_formasDePagamento[$b->id_formapagamento]) ? utf8_encode($_formasDePagamento[$b->id_formapagamento]->titulo) : '',
														"formaDePagamentoTipo" => isset($_formasDePagamento[$b->id_formapagamento]) ? $_formasDePagamento[$b->id_formapagamento]->tipo : '',
														"pago" => $b->pagamento,
														//"recibo" => $b->recibo,
														"parcelas" => $x->qtdParcelas,
														"vencido" => (strtotime($b->data_vencimento) < strtotime(date('Y-m-d')) ? true : false),
														"parcela" => $contador,
														"obs" => utf8_encode($b->obs),
														"valorJuros" => $b->valor_juros,
														"valorMulta" => $b->valor_multa,
														"taxa" => $b->taxa_cartao,
														"total" => (float)$b->valor
													);
												}


												if ($baixaVencida === true) {
													$status = "INADIMPLENTE";
													$icone = 'fluent:warning-24-regular';
													$cor = "red";
												} else if ($baixaEmAberta == false && $saldoAPagar <= 0) {
													$status = "ADIMPLENTE";
													$icone = 'fluent:checkbox-checked-24-filled';
													$cor = "green";
												} else {
													if (number_format($saldoAPagar, 2) == 0 || $saldoAPagar < 0) {
														$status = "A RECEBER";
														$icone = 'fluent:calendar-ltr-24-regular';
														$cor = "blue";
													} else {
														$cor = "orange";
														$status = "DEFINIR PAGAMENTO";
														$icone = 'fluent:checkbox-warning-24-regular';
													}
												}
												// if ($saldoAPagar > 0) {
												// 	if (strtotime($x->data_vencimento) < strtotime(date('Y-m-d'))) {
												// 		$cor = "red";
												// 		$status = "INADIMPLENTE2";
												// 		$icone = 'fluent:warning-24-regular';
												// 	}
												// }
											}
											// nao possui nenhuma baixa
											else {
												if (strtotime($x->data_vencimento) < strtotime(date('Y-m-d'))) {
													$cor = "red";
													$status = "INADIMPLENTE";
													$icone = 'fluent:warning-24-regular';
												} else {
													$cor = "orange";
													$status = "DEFINIR PAGAMENTO";
													$icone = 'fluent:checkbox-warning-24-regular';
												}
											}
											$subpagamentos = array();
											if ($x->fusao > 0) {
												$titulo = "União de Pagamentos (" . (isset($_subpagamentos[$x->id]) ? count($_subpagamentos[$x->id]) : 0) . ")";
												if (isset($_subpagamentos[$x->id])) {
													foreach ($_subpagamentos[$x->id] as $y) {
														$subpagamentos[] = array(
															'id_pagamento' => $y->id,
															'vencimento' => date('d/m/Y', strtotime($y->data_vencimento)),
															'titulo' => isset($_tratamentos[$y->id_tratamento]) ? utf8_encode($_tratamentos[$y->id_tratamento]->titulo) : 'Avulso',
															'valor' => $y->valor
														);
													}
												}
											} else {
												$titulo = isset($_tratamentos[$x->id_tratamento]) ? utf8_encode($_tratamentos[$x->id_tratamento]->titulo) : 'Avulso';
											}


											$statusPromessa = false;
											$statusInadimplente = false;
											$todasPagas = false;

											// nao possui baixa
											if (count($baixas) == 0) {
												if (strtotime($x->data_vencimento) < strtotime(date('Y-m-d'))) {
													$statusInadimplente = true;
												}
											}
											// possui baixa
											else {
												// se saldo = 0
												if ($saldoAPagar == 0) {
													$baixaVencida = false;
													$baixaPaga = false;
													$todasPagas = true;
													foreach ($baixas as $b) {

														$b = (object)$b;
														if ($b->pago == 0) {
															if (strtotime(date('Y-m-d')) > strtotime($b->vencimento)) {
																$baixaVencida = true;
															}


															$todasPagas = false;
														} else {
															$baixaPaga = true;
														}
													}


													// se possui baixa vencida
													if ($baixaVencida === true) {
														$statusInadimplente = true;

														// se todas foram pagas
														if ($todasPagas === true) {
														} else {
															$statusPromessa = true;
														}
													} else {
														// se todas foram pagas
														if ($todasPagas === true) {

															$statusPromessa = true;
														} else {
															$statusPromessa = true;
														}
													}
												} else {
												}
											}

											$item = array(
												'id_parcela' => $x->id,
												'titulo' => $titulo,
												'vencimento' =>  date('d/m/Y', strtotime($x->data_vencimento)),
												'valorParcela' => $x->valor,
												'valorDesconto' => $valorDesconto,
												'valorDespesa' => $valorDespesa,
												'valorCorrigido' => $valorCorrigido,
												'valorPago' => $valorPago,
												'baixas' => $baixas,
												'subpagamentos' => $subpagamentos,
												'saldoApagar' => number_format($saldoAPagar, 2),
												'fusao' => $x->fusao,
												'multaAtraso' => $valorMulta,
												'jurosMensal' => $valorJuros
											);

											$pagamentosJSON[$x->id] = $item;
											if ($status == 'DEFINIR PAGAMENTO') {
												$DefinirPagamento += $x->valor;
											}
											$saldoAPagar = $saldoAPagar < 0 ? 0 : number_format($saldoAPagar, 2)
										?>
                                    <tr class="js-pagamento-item js-pagamento-item-<?= $x->id; ?>" data-id="<?= $x->id; ?>">
                                        <?php if (isset($_GET['unirPagamentos'])) { ?>
                                        <td style="width:30px;">
                                            <?php
												if ($x->fusao == 0 and !isset($pagamentosComBaixas[$x->id])) {
											?>
                                            <input type="checkbox" name="pagamentos[]" class="js-checkbox-pagamentos"
                                                data-id_tratamento="<?= $x->id_tratamento; ?>" value="<?= $x->id; ?>" />
                                            <span class="iconify js-checkbox-pagamentos-disabled"
                                                data-icon="fxemoji:cancellationx" style="opacity:0.2;display:none;"
                                                data-id_tratamento="<?= $x->id_tratamento; ?>"></span>
                                            <?php
												} else {
													echo '<span class="iconify" data-icon="fxemoji:cancellationx" style="opacity:0.2"></span>';
												}
											?>

                                        </td>
                                        <?php 
											}	
										?>
                                        <td>
                                            <h1>
                                                <?php
													if ($x->fusao > 0) {
												?>
                                                <strong><i class="iconify" data-icon="codicon:group-by-ref-type"
                                                        data-height="18" data-inline="true"></i> União de Pagamentos
                                                    (<?= isset($_subpagamentos[$x->id]) ? count($_subpagamentos[$x->id]) : 0; ?>)</strong>
                                                <?php
													} else {
														echo isset($_tratamentos[$x->id_tratamento]) ? utf8_encode($_tratamentos[$x->id_tratamento]->titulo) : 'Avulso';
													}
												?>
                                            </h1>
                                            <p><?= date('d/m/Y', strtotime($x->data_vencimento)); ?></p>
                                        </td>
                                        <td>
                                            <div class="list1__icon" style="color:gray;">
                                                <font color=<?= $cor ?>><i class="iconify"
                                                        data-icon="<?= $icone ?>"></i> <?= $status ?></font>
                                            </div>
                                        </td>
                                        <td>
                                            <h1>R$ <?= number_format($x->valor, 2, ",", "."); ?></h1>
                                            <span><?= ($saldoAPagar > 0) ? "Faltam: R$ " . $saldoAPagar . "<br>" : "" ?></span>
                                            <span><?= ($item['multaAtraso'] > 0) ? "Multa: R$ " . number_format($item['multaAtraso'], 2, ",", ".") . "<br>" : "" ?></span>
                                            <span><?= ($item['jurosMensal'] > 0) ? "Juros: R$ " . number_format($item['jurosMensal'], 2, ",", ".") . "<br>" : "" ?></span>
                                        </td>
                                        <?php
											if ($x->id_tratamento>0 && isset($parcelasTratamentos[$x->id_tratamento])) {
												if (!isset($numeroParcela[$x->id_tratamento])) $numeroParcela[$x->id_tratamento] = 1;
										?>
                                        	<td>Parcela <?= $numeroParcela[$x->id_tratamento]++; ?> de <?= ($parcelasTratamentos[$x->id_tratamento]); ?></td>
                                        <?php
											}else{
												echo "<td>Parcela 1 de 1</td>";
											}
										?>
                                    </tr>
                                    <?php
									}
									?>
                                </table>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </section>
    </div>
</main>

<script type="text/javascript">
_pagamentosList = JSON.parse(`<?= json_encode($pagamentosJSON); ?>`);
_paciente = JSON.parse(`<?= json_encode($paciente); ?>`);
</script>

<?php

	//require_once("includes/api/apiAsideFinanceiro.php");
	$apiConfig = array(
		'contasAReceber' => 1,
		'contasAvulsoAReceber' => 1,
	);

	include_once "includes/api/apiAsidePagamentos.php";

	include "includes/footer.php";
?>