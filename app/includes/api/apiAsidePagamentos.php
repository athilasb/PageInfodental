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
				$formaDePagamento;
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
									id_formapagamento=$formaDePagamento->id,
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
					"titulo" => utf8_encode($tratamento->titulo),
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
		} else if ($_POST['ajax'] == "buscarUsuarios") {
			$usuarios = [];
			try {
				if (isset($_POST['tipo_beneficiario'])) {
					$tipo_beneficiario = $_POST['tipo_beneficiario'];
					if ($tipo_beneficiario === 'fornecedor') {
						$sql->consult($_p . "parametros_fornecedores", "*", "WHERE lixo=0");
						while ($x = mysqli_fetch_object($sql->mysqry)) {
							//$usuarios[$x->id] = utf8_encode($x->razao_social);
							$usuarios[$x->id]['id'] = "$x->id";
							$usuarios[$x->id]['nome'] = utf8_encode($x->razao_social);
							//$usuarios[$x->id]['razao_social'] = utf8_encode($x->razao_social);
							// $usuarios[$x->id]['nome_fantasia'] = utf8_encode($x->nome_fantasia);
							// $usuarios[$x->id]['cpf'] = "$x->cpf";
						}
					} else if ($tipo_beneficiario === 'paciente') {
						$sql->consult($_p . "pacientes", "*", "where lixo=0");
						while ($x = mysqli_fetch_object($sql->mysqry)) {
							// $usuarios[$x->id] = utf8_encode($x->nome);
							$usuarios[$x->id]['id'] = "$x->id";
							$usuarios[$x->id]['nome'] = utf8_encode($x->nome);
						}
					} else if ($tipo_beneficiario === 'colaborador') {
						$sql->consult($_p . "colaboradores", "*", "where lixo=0");
						while ($x = mysqli_fetch_object($sql->mysqry)) {
							// $usuarios[$x->id] = utf8_encode($x->nome);
							$usuarios[$x->id]['id'] = "$x->id";
							$usuarios[$x->id]['nome'] = utf8_encode($x->nome);
						}
					}
					if (count($usuarios) > 0) {
						$rtn = array('success' => true, 'usuarios' => $usuarios);
					} else {
						$rtn = array('error' => 'Nenhum Usuario Encontrado');
					}
				}
			} catch (Exception $err) {
				$rtn = array('error' => 'Erro: ' . $err->getMessage());
			}
		} else if ($_POST['ajax'] == 'addPagamento') {
			$data_emissao = $_POST['data_emissao'];
			$descricao = utf8_decode($_POST['descricao']);
			$id_beneficiario = $_POST['id_beneficiario'];
			$tipo_beneficiario = $_POST['tipo_beneficiario'];
			$valor_pagamento = floatval($_POST['valor_pagamento']) * (-1);
			$objeto = isset($_POST['objeto']) ? json_decode($_POST['objeto']) : false;

			$vSQL = "data=NOW()";
			$vSQL .= ",lixo=0";
			$vSQL .= ",id_origem=2";
			$vSQL .= ",tipo='$tipo_beneficiario'";
			$vSQL .= ",id_pagante_beneficiario='$id_beneficiario'";
			$vSQL .= ",descricao='$descricao'";
			if (!$objeto) {
				$rtn = ["error" => 'Objeto de Pagamento Não Encontrado'];
			} else {
				if (count($objeto->pagamentos) > 0) {
					if ($objeto->split_recorrente->ativo == true) {
						// aqui é um pagamento recorrente
						if (count($objeto->pagamentos) > 1) {
							$rtn = ["error" => 'Pagamentos recorrentes só são Permitidos com 1 Parcela'];
						} else {
							$acada = intVal($objeto->split_recorrente->acada);
							$meses = intVal($objeto->split_recorrente->meses);
							$data_vencimento_inicial = $objeto->pagamentos[0]->data_vencimento;
							$id_formapagamento = $objeto->pagamentos[0]->id_formapagamento;
							$id_centro_de_custo = $objeto->split_pagamento->id_centro_de_custo;
							$id_categoria_split = $objeto->split_pagamento->id_categoria_split;

							$vSQL .= ",valor='$valor_pagamento'";
							$vSQL .= ",id_formapagamento='$id_formapagamento'";
							while ($meses > 0) {
								$meses--;
								$sql->add($_p . "financeiro_fluxo", "$vSQL,data_vencimento='$data_vencimento_inicial',id_centro_custo='$id_centro_de_custo',id_categoria='$id_categoria_split'");
								$idAdd = $sql->ulid;
								$sql->update($_p . "financeiro_fluxo", "id_registro='$idAdd'", "WHERE id=$idAdd");
								$data_vencimento_inicial = date('Y-m-d', strtotime("+ $acada days", strtotime($data_vencimento_inicial)));
							}
							$rtn = ["sucess" => true, 'objeto' => $objeto];
						}
					} else if ($objeto->split_pagamento->ativo == true) {
						// aqui é se for um split de pagamento
						if (count($objeto->pagamentos) > 1) {
							$tipo_splits = $objeto->split_pagamento->tipo_splits;
							foreach ($objeto->pagamentos as $x) {
								$data_vencimento_inicial = $x->data_vencimento;
								$valor = ($x->valor) * (-1);
								$id_formapagamento = $x->id_formapagamento;
								$sql->add($_p . "financeiro_fluxo", "$vSQL,data_vencimento='$data_vencimento_inicial',valor='$valor',id_formapagamento='$id_formapagamento',dividido=1");
								$id_fluxo = $sql->ulid;
								$sql->update($_p . "financeiro_fluxo", "id_registro='$id_fluxo'", "WHERE id=$id_fluxo");
								$multiplicador_split = 0;
								foreach ($objeto->split_pagamento->splits as $split) {
									if ($tipo_splits == 'porcentagem') {
										$multiplicador_split = ($split->porc_valor / 100);
									} else {
										$multiplicador_split =  ($split->porc_valor / $valor_pagamento);
									}
									$id_centro_de_custo = $split->id_centro_de_custo;
									$id_categoria = $split->id_categoria;
									$valor_split = $valor * $multiplicador_split;
									$sql->add($_p . "financeiro_fluxo", "$vSQL,data_vencimento='$data_vencimento_inicial',valor='$valor_split',id_formapagamento='$id_formapagamento',dividido=0,id_dividido='$id_fluxo',id_centro_custo='$id_centro_de_custo',id_categoria='$id_categoria'");
									$id_split = $sql->ulid;
									$sql->update($_p . "financeiro_fluxo", "id_registro='$id_fluxo'", "WHERE id=$id_split");
									$sql->add($_p . "financeiro_fluxo_splits_vencimentos", "id_split='$id_split',id_fluxo='$id_fluxo',vencimento='$data_vencimento_inicial',centrodecusto='$id_centro_de_custo',valor='$valor_split'");
								}
							}
							$rtn = ["sucess" => true, 'objeto' => $objeto];
						}
						//$rtn = ["error" => 'Ainda não Implementado','objeto'=>$objeto];
					} else {
						// aqui é se nao for split e nao for recorrente
						foreach ($objeto->pagamentos as $x) {
							$data_vencimento_inicial = $x->data_vencimento;
							$valor = ($x->valor) * (-1);
							$id_formapagamento = $x->id_formapagamento;
							$id_centro_de_custo = $objeto->split_pagamento->id_centro_de_custo;
							$id_categoria_split = $objeto->split_pagamento->id_categoria_split;
							$sql->add($_p . "financeiro_fluxo", "$vSQL,data_vencimento='$data_vencimento_inicial',valor='$valor',id_formapagamento='$id_formapagamento',id_centro_custo='$id_centro_de_custo',id_categoria='$id_categoria_split'");
							$idAdd = $sql->ulid;
							$sql->update($_p . "financeiro_fluxo", "id_registro='$idAdd'", "WHERE id=$idAdd");
						}
						$rtn = ["sucess" => true, 'objeto' => $objeto];
					}
				} else {
					$rtn = ["error" => 'Voce Precisa Adicionar ao Menos 1 Parcela'];
				}
			}

			//$sql->add($_p . "financeiro_fluxo", "$vSQL");
			//$idAdd = $sql->ulid;
			//$sql->update($_p . "financeiro_fluxo", "id_registro='$idAdd'", "WHERE id=$idAdd");

			//$rtn = ["sucess" => true, 'objeto' => $objeto];
			//$rtn = ["error" => 'AInda não Implementado'];
			//$rtn = ["sucess" => true, 'post' => $_POST, 'sql' => $vSQL];
		}
		header("Content-type: application/json");
		echo json_encode($rtn);
		die();
	} else if (isset($_GET['ajax'])) {
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
		if ($_GET['ajax'] == "buscaPaciente") {
			$where = "WHERE 1=2";
			$tipo_beneficiario = $_GET['tipo_beneficiario'] ?? 'fornecedor';
			if ($tipo_beneficiario == 'paciente') {
				if (isset($_GET['search']) and !empty($_GET['search'])) {
					$aux = explode(" ", $_GET['search']);
					$wh = "";
					$primeiraLetra = '';
					foreach ($aux as $v) {
						if (empty($v)) continue;
						if (empty($primeiraLetra)) $primeiraLetra = substr($v, 0, 1);
						$wh .= "nome REGEXP '$v' and ";
					}
					$wh = substr($wh, 0, strlen($wh) - 5);
					$where = "where (($wh) or nome like '%" . $_GET['search'] . "%' or telefone1 like '%" . $_GET['search'] . "%' or cpf like '%" . $_GET['search'] . "%') and lixo=0";
				}
				if (!empty($primeiraLetra)) $where .= " ORDER BY CASE WHEN nome >= '$primeiraLetra' THEN 1 ELSE 0 END DESC, nome ASC";
				else $where .= " order by nome asc";

				$sql->consult($_p . "pacientes", "nome,id,telefone1,cpf,foto_cn,foto", $where);
				while ($x = mysqli_fetch_object($sql->mysqry)) {

					$ft = 'img/ilustra-perfil.png';
					if (!empty($x->foto_cn)) {
						$ft = $_cloudinaryURL . 'c_thumb,w_100,h_100/' . $x->foto_cn;
					} else if (!empty($x->foto)) {
						$ft = $_wasabiURL . "arqs/clientes/" . $x->id . ".jpg";
					}

					$rtn['items'][] = array(
						'id' => $x->id,
						'text' => utf8_encode($x->nome),
						'nome' => utf8_encode($x->nome),
						'telefone' => utf8_encode($x->telefone1),
						'ft' => $ft,
						'cpf' => utf8_encode($x->cpf)
					);
				}
			} else if ($tipo_beneficiario == 'fornecedor') {
				if (isset($_GET['search']) and !empty($_GET['search'])) {
					$aux = explode(" ", $_GET['search']);
					$wh = "";
					$primeiraLetra = '';
					foreach ($aux as $v) {
						if (empty($v)) continue;
						if (empty($primeiraLetra)) $primeiraLetra = substr($v, 0, 1);
						$wh .= "razao_social REGEXP '$v' and ";
					}
					$wh = substr($wh, 0, strlen($wh) - 5);
					$where = "where (($wh) or razao_social like '%" . $_GET['search'] . "%' or nome like '%" . $_GET['search'] . "%' or cpf like '%" . $_GET['search'] . "%') and lixo=0";
				}
				if (!empty($primeiraLetra)) $where .= " ORDER BY CASE WHEN razao_social >= '$primeiraLetra' THEN 1 ELSE 0 END DESC, razao_social ASC";
				else $where .= " order by razao_social asc";

				$sql->consult($_p . "parametros_fornecedores", "razao_social,id,telefone1,cpf,cnpj", $where);
				while ($x = mysqli_fetch_object($sql->mysqry)) {
					$ft = 'img/ilustra-perfil.png';
					$rtn['items'][] = array(
						'id' => $x->id,
						'text' => utf8_encode($x->razao_social),
						'nome' => utf8_encode($x->razao_social),
						'telefone' => utf8_encode($x->telefone1),
						'ft' => $ft,
						'cpf' => utf8_encode($x->cpf)
					);
				}
			} else if ($tipo_beneficiario == 'colaborador') {
				if (isset($_GET['search']) and !empty($_GET['search'])) {
					$aux = explode(" ", $_GET['search']);
					$wh = "";
					$primeiraLetra = '';
					foreach ($aux as $v) {
						if (empty($v)) continue;
						if (empty($primeiraLetra)) $primeiraLetra = substr($v, 0, 1);
						$wh .= "nome REGEXP '$v' and ";
					}
					$wh = substr($wh, 0, strlen($wh) - 5);
					$where = "where (($wh) or nome like '%" . $_GET['search'] . "%' or telefone1 like '%" . $_GET['search'] . "%' or cpf like '%" . $_GET['search'] . "%') and lixo=0";
				}
				if (!empty($primeiraLetra)) $where .= " ORDER BY CASE WHEN nome >= '$primeiraLetra' THEN 1 ELSE 0 END DESC, nome ASC";
				else $where .= " order by nome asc";

				$sql->consult($_p . "pacientes", "nome,id,telefone1,cpf,foto", $where);
				while ($x = mysqli_fetch_object($sql->mysqry)) {

					$ft = 'img/ilustra-perfil.png';
					if (!empty($x->foto)) {
						$ft = $_wasabiURL . "arqs/clientes/" . $x->id . ".jpg";
					}

					$rtn['items'][] = array(
						'id' => $x->id,
						'text' => utf8_encode($x->nome),
						'nome' => utf8_encode($x->nome),
						'telefone' => utf8_encode($x->telefone1),
						'ft' => $ft,
						'cpf' => utf8_encode($x->cpf)
					);
				}
			}
		}

		header("Content-type: application/json");
		echo json_encode($rtn);
		die();
	}
?>
<script type="text/javascript" src="js/aside.funcoes.js"></script>
<?php 
	# ASIDE CONTAS A RECEBER 
	if (isset($apiConfig['contasAReceber'])) {
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
								$('#js-aside-asFinanceiro .js-dataOriginal').html(`${`${dados.data_vencimento.split('-')[2]}/${dados.data_vencimento.split('-')[1]}/${dados.data_vencimento.split('-')[0]}`}`);
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
					let baixa = _pagamentos[id_pagamento].baixas.find(element => element.id_baixa == id_baixa)
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
										_pagamentos[id_pagamento].saldoApagar += baixa.valor
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
											//pagamentos[pagamentoIndex].baixas[baixaIndex].pago = "1";
											$('#js-aside-asFinanceiro-receber .aside-close').click();
											$('#js-aside-asFinanceiro .aside-close').click();
											$('.js-pagamento-item-' + id_parcela).click();
											swal({
												title: "Sucesso!",
												text: 'Recebido com Sucesso!',
												html: true,
												type: "success",
												confirmButtonColor: "#424242"
											});
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

					//let data = new Date(`${pagamento.data_vencimento.split('-')[2]}/${pagamento.data_vencimento.split('-')[1]}/${pagamento.data_vencimento.split('-')[0]}`);
					let data = new Date(pagamento.data_vencimento);
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
					// let data = new Date(`${pagamento.data_vencimento.split('-')[2]}/${pagamento.data_vencimento.split('-')[1]}/${pagamento.data_vencimento.split('-')[0]}`);
					let data = new Date(pagamento.data_vencimento);
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
												text: "Algum erro desconhecido ocorreu durante a baixa deste pagamento!",
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
<?php 
	} 
	# ASIDE CONTAS A PÀGAR
	if (isset($apiConfig['contasAPagar'])) { 
		$_formasDePagamento = array();
		$categorias = array();
		$centrodecustos = array();
		$optionFormasDePagamento = '';
		//pegando as formas de pagamentos
		$sql->consult($_p . "parametros_formasdepagamento", "*", "order by titulo asc");
		while ($x = mysqli_fetch_object($sql->mysqry)) {
			$_formasDePagamento[$x->id] = $x;
			$optionFormasDePagamento .= '<option value="' . $x->id . '" data-tipo="' . $x->tipo . '">' . utf8_encode($x->titulo) . '</option>';
		}
		// pegando os centros de custo
		$sql->consult($_p . "financeiro_fluxo_splits_centrodecusto", "*", " WHERE lixo=0 order by titulo asc");
		while ($x = mysqli_fetch_object($sql->mysqry)) {
			$centrodecustos[$x->id]['id'] = $x->id;
			$centrodecustos[$x->id]['titulo'] = utf8_encode($x->titulo);
		}
		// pegando as categorias
		$sql->consult($_p . "financeiro_fluxo_categorias", "*", " WHERE lixo=0 order by titulo asc");
		while ($x = mysqli_fetch_object($sql->mysqry)) {
			//$categorias[$x->id] = $x;
			if ($x->tipo == 'categoria') {
				$categorias[$x->id]['id'] = $x->id;
				$categorias[$x->id]['titulo'] = $x->titulo;
				$categorias[$x->id]['tipo'] = $x->tipo;
				$categorias[$x->id]['subcategorias'] = array();
			} else if ($x->tipo == 'subcategoria') {
				$categorias[$x->id_categoria]['subcategorias'][$x->id] = [
					"id" => $x->id,
					"titulo" => $x->titulo,
					"tipo" => $x->tipo
				];
			}
		}

		?>
		<section class="aside aside-form" id="js-aside-asFinanceiro">
			<div class="aside__inner1">
				<input type="hidden" name="alteracao" value="0">
				<header class="aside-header">
					<h1 class="js-titulo"> Nova Conta a Pagar</h1>
					<a href="javascript:;" class="aside-header__fechar aside-close"><i class="iconify" data-icon="fluent:dismiss-24-filled"></i></a>
				</header>

				<form method="post" class="aside-content form">
					<input type="hidden" class="js-id_pagamento" value="0" />
					<input type="hidden" class="js-index" />

					<textarea id='splits-pagamentos' style='display:none'></textarea>

					<!-- Programacao de pagamento -->
					<div class="js-fin js-fin-programacao">
						<fieldset style="padding:.75rem 1.5rem;">
							<legend>Informações</legend>
							<p>Beneficiário</p>
							<div class="colunas3">
								<dl>
									<label><input type="radio" name="tipo_beneficiario" value="fornecedor">Fornecedor</label>
								</dl>
								<dl>
									<label><input type="radio" name="tipo_beneficiario" value="paciente">Paciente</label>
								</dl>
								<dl>
									<label><input type="radio" name="tipo_beneficiario" value="colaborador">Colaborador</label>
								</dl>
							</div>
							<div class="colunas1">
								<dl class="dl2">
									<dl>
										<dd>
											<select name="id_beneficiario" class="select2 obg-0 ajax-id_paciente" disabled>
												<option value="">Buscar Beneficiario...</option>
											</select>
											
											<a href="javascript:;" class="js-btn-aside button" data-aside="paciente" data-aside-sub><i class="iconify" data-icon="fluent:add-circle-24-regular"></i></a>
										</dd>
									</dl>
								</dl>
							</div>
							<div class="colunas5">
								<dl>
									<dt>Data Emissão</dt>
									<dd><input type="text" class="js-vencimento js-tipoPagamento data" value="<?= date('d/m/Y'); ?>" name="data_emissao" disabled /></dd>
								</dl>
								<dl>
									<dt>Valor Total</dt>
									<dd class="form-comp"><span>R$</span>
										<input type="text" class="js-valor" name="valor_pagamento" value="0" />
									</dd>
								</dl>
								<dl class="dl3">
									<dt>Descrição</dt>
									<dd><input type="text" class="js-obs-desconto" name="descricao" /></dd>
								</dl>

							</div>
							<div class="colunas5">
								<dl>
									<dt>Split de Pagamento</dt>
									<label><input type="checkbox" class="input-switch split-pagamento" /></label>
								</dl>
								<section class="colunas3" id='split-false-splits-qtd' style='display:none'>
									<dl>
										<dt>Quantidade</dt>
										<dd>
											<label><input class="js-splits-quantidade" type="number" name="parcelas" value="1" style="width:80px;" /></label>
										</dd>
									</dl>
									<dl style="font-size: 11px;">
										<dt></dt>
										<dd>
											<label><input type="radio" name="modo_divisao" value="porcentagem" checked>Porcentagem</label>
										</dd>
									</dl>
									<dl style="font-size: 11px;">
										<dt></dt>
										<dd>
											<label><input type="radio" name="modo_divisao" value="valor_absoluto">Valor Absoluto</label>
										</dd>
									</dl>
								</section>
								<dl class="dl2" id='split-false-centro-custo'>
									<dt>Centro de Custo</dt>
									<dd>
										<select name="id_centro_de_custo" class="select2">
											<option value="0">-</option>
											<?php 
											foreach ($centrodecustos as $id => $c) { 
												?>
												<option value="<?= $id ?>"><?= $c['titulo'] ?></option>
												<?php
											}
											?>
										</select>
									</dd>
								</dl>
								<dl class="dl2" id='split-false-categorias'>
									<dt>Categoria</dt>
									<dd >
										<select name="id_categoria" class="select2">
											<option value="0">-</option>
											<?php foreach ($categorias as $id => $cat) { ?>
												<optgroup label="<?= $cat['titulo'] ?>">
													<?php foreach ($cat['subcategorias'] as $id_sub => $sub) : ?>
														<option value="<?= $id_sub ?>"><?= $sub['titulo'] ?></option>
													<?php endforeach; ?>
												</optgroup>
											<?php } ?>
										</select>
									<a href="javascript:;" class="js-btn-financeiroFluxoCategorias button" data-aside="tag" data-aside-sub><i class="iconify" data-icon="fluent:add-circle-24-regular"></i></a> 
									</dd>
								</dl>
							</div>
							<fieldset class="js-fieldset-pagamentos-splits" style="display:none">
								<legend>Splits</legend>
								<section>

								</section>
							</fieldset>
							<div class="">
								<p>É um custo recorrente? se sim qual a recorrência e quando acaba?</p>
								<div class="row">
									<div class="colunas1">
										<dl>
											<label><input type="checkbox" class="input-switch split-custo-recorrente" /></label>
										</dl>
									</div>
									<div class="colunas2" style="display:none" id='area-custo-recorrente'>
										<dl class="form-comp">
											<label>a Cada <span>Dias</span><input type="text" class="input-switch" value="30" name="area-custo-recorrente-acada-dia" /></label>
										</dl>
										<dl class="form-comp">
											<label>Por <span>meses</span><input type="text" class="input-switch" value="12" name="area-custo-recorrente-meses" /></label>
										</dl>

									</div>
								</div>
						</fieldset>
						<fieldset class="js-fieldset-pagamentos">
							<legend>Pagamentos</legend>
							<section class="js-pagamento" style="display:none">
								<div class="colunas4">

									<dl class="dl3">
										<dt>Qual a Quantidade de Pagamentos?</dt>
										<dd><input type="text" class="" value="1" name="qtd_pagamento" /></dd>
									</dl>
								</div>
							</section>
							<section style="display:none">
								<aside></aside>
								<article>
									<div class="colunas3">
										<dl>
											<dt>Data de vencimento</dt>
											<dd class="form-comp"><span><i class="iconify" data-icon="fluent:calendar-ltr-24-regular"></i></span><input type="tel" class="data js-vencimento" data-ordem="" value="" name="data_vencimento" /></dd>
										</dl>
										<dl>
											<dt>Forma de Pagamento</dt>
											<dd>
												<select class="js-id_formapagamento js-tipoPagamento" name="forma_pagamento">
													<option value="0">Forma de Pagamento...</option>
													<?= $optionFormasDePagamento; ?>
												</select>
											</dd>
										</dl>
									</div>
							</section>
							<section class="js-tipo js-tipo-manual">
								<dl>
									<dt>Parcelas</dt>
									<dd>
										<label><input class="js-pagamentos-quantidade" type="number" name="parcelas" value="<?= isset($values['parcelas']) ? $values['parcelas'] : '0'; ?>" style="width:80px;" /></label>
									</dd>
								</dl>
							</section>
							<section class="js-tipo js-listar-parcelas" style="display:none;">
								<div class="fpag" style="margin-top:1rem;">
								</div>
							</section>
							<dl style="margin-top:1.5rem;">
								<dd><button href="javascript:;" class="button button_main js-btn-addPagamento" type="button" data-loading="0"><i class="iconify" data-icon="fluent:add-circle-24-regular"></i><span>Adicionar</span></button></dd>
							</dl>
						</fieldset>
					</div>
				</form>
			</div>
		</section>
		<script>
			const categorias = <?= json_encode($categorias) ?>;
			const centrodecustos = <?= json_encode($centrodecustos) ?>;
			const abrirAside1 = (tipo, index) => {
				$("#js-aside-asFinanceiro").fadeIn(100, function() {
					$("#js-aside-asFinanceiro .aside__inner1").addClass("active");
				});
			}
			const formatTemplateSelection = (state) => {
				if (!state.id) return state.text;
				var baseUrl = "/user/pages/images/flags";
				infoComplementar = ``;
				infoComplementar += !!state.cpf ? ` - CPF: ${state.cpf}` : '';
				infoComplementar += !!state.telefone ? ` - Tel.: ${state.telefone}` : '';
				var $state = $('<span><img src="img/ilustra-perfil.png" style="width:30px;height:30px;border-radius:50px;" /> ' + state.text + infoComplementar + '</span>');
				return $state;
			}
			const formatTemplate = (state) => {
				if (!state.id) return state.text;
				var baseUrl = "/user/pages/images/flags";
				infoComplementar = ``;
				infoComplementar += !!state.cpf ? ` - CPF: ${state.cpf}` : '';
				infoComplementar += !!state.telefone ? ` - Tel.: ${state.telefone}` : '';
				var $state = $('<span style="display:flex; align-items:center; gap:.5rem;"><img src="' + state.ft + '" style="width:40px;height:40px;border-radius:100%;" /> ' + state.text + infoComplementar + '</span>');
				return $state;
			}
			const atualizaQtdSplits = () => {
				let modo_divisao = $('[name="modo_divisao"]:checked').val()
				let quantidade = $('.js-splits-quantidade').val();
				let valor_total = unMoney($('[name="valor_pagamento"]').val())
				let splits = [];
				if (quantidade < 1) {
					$(this).val(1)
					quantidade = 1
				}
				let valor_split = 0
				if (modo_divisao == 'porcentagem') {
					valor_split = (100 / quantidade).toFixed(2)
				} else {
					valor_split = unMoney(valor_total / quantidade)
				}
				for (let i = 0; i < quantidade; i++) {
					let item = {
						id: i + 1,
						valor_split,
						centrodecusto: 0,
						categoria: 0,
						calcular_como: '',
						modo_divisao
					}
					splits.push(item)
				}
				$('#splits-pagamentos').text(JSON.stringify(splits))
				listarSplits()
			}
			const listarSplits = () => {
				let modo_divisao = $('[name="modo_divisao"]:checked').val();
				let splits = JSON.parse($('#splits-pagamentos').text()) ?? [];
				let simbolo = (modo_divisao == 'porcentagem') ? '%' : 'R$';
				let classValor = (modo_divisao == 'porcentagem') ? '' : 'js-valor money';
				$('.js-fieldset-pagamentos-splits').find('section').html("")
				splits.forEach((item) => {
					let valor_split = item.valor_split
					if (modo_divisao == 'porcentagem') {
						valor_split
					} else {
						valor_split = number_format(valor_split, 2, ',', '.')
					}
					$('.js-fieldset-pagamentos-splits').find('section').append(`
							<div class="fpag-item js-pagamento-item" data-id="${item.id}" style="margin-bottom: 10px;">
							<input type="hidden" name="id-split" value="${item.id}">
								<aside>${item.id}</aside>
								<article>
									<div class="colunas3">
										<dl class="form-comp">
											<dt>Porcentagem / Valor</dt>
											<label><span>${simbolo}</span><input type="text" class="${classValor}" name="porcentagem-splits" value="${valor_split}"></label>
										</dl>
										<dl>
											<dt>Centro de Custo</dt>
											<select name="id_centro_de_custo" class="">
												<option value="0">-</option>
											</select>
										</dl>
										<dl>
											<dt>Categoria</dt>
											<select name="id_categoria" class="">
												<option value="0">-</option>
											</select>
										</dl>
									</div>

									<div class="colunas3" style="font-size:11px">
										<dl>
											<label><input type="radio" name="calcular_como_${item.id}" value="horas_clinicas">Calcular Gasto como Horas Clínicas</label>
										</dl>
										<dl>
											<label><input type="radio" name="calcular_como_${item.id}" value="gasto_paciente">Controlar gasto por Paciente</label>
										</dl>
										<dl>
											<label><input type="radio" name="calcular_como_${item.id}" value="investimentos_outros">Investimentos e Outros</label>
										</dl>

									</div>
								</article>
							</div>
						`)

					for (let x in centrodecustos) {
						$('.js-fieldset-pagamentos-splits').find('section').find(`[data-id="${item.id}"]`).find('[name="id_centro_de_custo"]').append(`<option value="${x}">${centrodecustos[x].titulo}</option>`)
					}
					for (let x in categorias) {
						$('.js-fieldset-pagamentos-splits').find('section').find(`[data-id="${item.id}"]`).find('[name="id_categoria"]').append(`<optgroup data-id-categoria="${x}" label="${categorias[x].titulo}"></optgroup>`)
						for (let sub in categorias[x].subcategorias) {
							$('.js-fieldset-pagamentos-splits').find('section').find(`[data-id="${item.id}"]`).find('[name="id_categoria"]').find(`[data-id-categoria="${x}"]`).append(`<option value="${sub}">${categorias[x].subcategorias[sub].titulo}</option>`)
						}
					}

				})
				$('.js-valor').maskMoney({
					symbol: '',
					allowZero: true,
					showSymbol: true,
					thousands: '.',
					decimal: ',',
					symbolStay: true
				});

			}
			const pagamentosListar = () => {
				$('.js-listar-parcelas').show();
				$('.js-listar-parcelas .fpag').html('');
				let valorTotal = unMoney($('[name="valor_pagamento"]').val())
				let qtdParcelas = parseInt($('.js-pagamentos-quantidade').val())
				let metodosPagamentosAceito = '<?php echo $optionFormasDePagamento; ?>';
				let valorParcelas = number_format(valorTotal / qtdParcelas, 2, ",", ".")
				let valorTotalParcelado = 0
				if (valorTotal <= 0) {
					swal({
						title: "Erro!",
						text: "Antes Voce precisa Definir qual Valor Total deste Pagamento",
						type: "error",
						confirmButtonColor: "#424242"
					});
					return
				}
				let startDate = new Date();
				if ($('.js-vencimento:eq(0)').val() != undefined) {
					aux = $('.js-vencimento:eq(0)').val().split('/');
					startDate = new Date(); //`${aux[2]}-${aux[1]}-${aux[0]}`);
					startDate.setDate(aux[0]);
					startDate.setMonth(eval(aux[1]) - 1);
					startDate.setFullYear(aux[2]);
				}
				for (let i = 1; i <= qtdParcelas; i++) {
					startDate = new Date();
					let vencimento = ""
					valorTotalParcelado += unMoney(valorParcelas)
					if (i == qtdParcelas) {
						if (valorTotalParcelado < valorTotal) {
							valorParcelas = number_format(unMoney(valorParcelas) + (unMoney(valorTotal - valorTotalParcelado)), 2, ",", ".")
							valorTotalParcelado += (unMoney(valorTotal) - unMoney(valorTotalParcelado))
						} else if (valorTotalParcelado > valorTotal) {
							valorParcelas = number_format(unMoney(valorParcelas) - (unMoney(valorTotalParcelado - valorTotal)), 2, ",", ".")
							valorTotalParcelado -= (unMoney(valorTotalParcelado) - unMoney(valorTotal))
						}
					}
					let mes = startDate.getMonth() + i;
					mes = mes <= 9 ? `0${mes}` : mes;

					let dia = startDate.getDate();
					dia = dia <= 9 ? `0${dia}` : dia;
					vencimento = `${dia}/${mes}/${startDate.getFullYear()}`;
					$('.js-listar-parcelas .fpag').append(`<div class="fpag-item js-pagamento-item">
							<aside>${i}</aside>
							<article>
								<div class="colunas3">
									<dl>
										<dd class="form-comp"><span><i class="iconify" data-icon="fluent:calendar-ltr-24-regular"></i></span><input type="tel" name="" class="data js-vencimento" data-ordem="${i}" value="${vencimento}"/></dd>
									</dl>
									<dl>
										<dd class="form-comp"><span>R$</i></span><input type="tel" name="" data-ordem="${i}" class="valor js-valor" value="${valorParcelas}"/></dd>
									</dl>
									<dl>
										<dd>
											<select class="js-id_formapagamento js-tipoPagamento">
											<option value="0">Forma de Pagamento...</option>
												${metodosPagamentosAceito}
											</select>
										</dd>
									</dl>
								</div>

								<div class="colunas3">
									<dl style="display:none">
										<dt>Qtd. Parcelas</dt>
										<dd>
											<select class="js-parcelas js-tipoPagamento">
												<option value="">selecione a as Parcelas</option>
											</select>
										</dd>
									</dl>

									<dl style="display:none">
										<dt>Identificador</dt>
										<dd><input type="text" class="js-identificador js-tipoPagamento" /></dd>
									</dl>
									<dl style="display:none" disabled>
										<dd><input type="hidden" class="js-metodo-selecionado js-tipoPagamento" /></dd>
									</dl>

								</div>
							</article>
						</div>
					`);
				}
				AdicionaMaskaras()
			}
			const AdicionaMaskaras = () => {
				$('.js-vencimento').inputmask('99/99/9999');
				$('.js-vencimento').datetimepicker({
					timepicker: false,
					format: 'd/m/Y',
					scrollMonth: false,
					scrollTime: false,
					scrollInput: false
				});
				$('.js-valor').maskMoney({
					symbol: '',
					allowZero: true,
					showSymbol: true,
					thousands: '.',
					decimal: ',',
					symbolStay: true
				});
			}

			$(function() {
				// botao para adicionar pagamento
				$('.js-btn-addPagamento').click(function() {
					let tipo_beneficiario = $('[name="tipo_beneficiario"]:checked').val();
					let id_beneficiario = $('[name="id_beneficiario"]').val();
					let data_emissao = $('[name="data_emissao"]').val().split("/");
					data_emissao = data_emissao[2] + "-" + data_emissao[1] + "-" + data_emissao[0];
					let descricao = $('[name="descricao"]').val();
					let valor_pagamento = unMoney($('[name="valor_pagamento"]').val());
					let CamposPagamentos = $('.js-listar-parcelas').find('article');
					let split_pagamento = $('.split-pagamento:checked')
					let split_recorrente = $('.split-custo-recorrente:checked')
					let objetoPagamento = {}
					let erro = ""
					if (!id_beneficiario || id_beneficiario <= 0) {
						erro = " Voce precisa selecionar um beneficiario."
					} else if (!tipo_beneficiario || tipo_beneficiario == undefined) {
						erro = " Selecione o Tipo de Beneficiario."
					} else if (!data_emissao || data_emissao == undefined) {
						erro = " Selecione a data De emissão."
					} else if (!descricao || descricao == undefined || descricao.length <= 3) {
						erro = "Voce precisa adicionar uma descricao válida."
					} else if (!valor_pagamento || valor_pagamento <= 0 || valor_pagamento == undefined) {
						erro = "Voce precisa adicionar um Valor para Este pagamento."
					}
					objetoPagamento = {
						tipo_beneficiario,
						id_beneficiario,
						data_emissao,
						descricao,
						valor_pagamento,
						valor_pagamento,
					}

					if (split_pagamento.length > 0) {
						let camposSplits = $('.js-fieldset-pagamentos-splits').find('.js-pagamento-item');
						if (camposSplits.length <= 0) {
							erro = "Voce Precisa Adicionar pelo Menos 1 Split de Pagamento ou Desativar o Split!"
						} else {
							let splits = []
							camposSplits.each(function(index, input) {
								let porc_valor = $(input).find('[name="porcentagem-splits"]').val()
								if (($('[name="modo_divisao"]:checked').val() == 'porcentagem')) {
									porc_valor = parseFloat(porc_valor)
								} else {
									porc_valor = unMoney(porc_valor)
								}
								let id_centro_de_custo = $(input).find('[name="id_centro_de_custo"]').val()
								let id_categoria = $(input).find('[name="id_categoria"]').val()
								let calcular_como = $(input).find(`[name="calcular_como_${index+1}"]:checked`).val()
								splits.push({
									porc_valor,
									id_centro_de_custo,
									id_categoria,
									calcular_como
								})
								if (porc_valor <= 0) {
									erro = "Todos Os Splits Prcisam Ter um valor Maior que ZERO"
								}
								if (!id_centro_de_custo || id_centro_de_custo <= 0 || id_centro_de_custo == undefined) {
									erro = "Todos Os Splits Prcisam  de um Centro de Custo."
								}
								if (!id_categoria || id_categoria <= 0 || id_categoria == undefined) {
									erro = "Todos Os Splits Prcisam de uma Categoria."
								}
								// if (!calcular_como || calcular_como == undefined) {
								// 	erro = "Todos Os Splits Prcisam pelo menos uma checkbox marcado."
								// }
							});
							objetoPagamento.split_pagamento = {
								ativo: true,
								quantidade: $('.js-splits-quantidade').val(),
								tipo_splits: $('[name="modo_divisao"]:checked').val(),
								splits
							}
						}
					} else {
						let id_centro_de_custo = $('[name="id_centro_de_custo"]').val();
						let id_categoria_split = $('[name="id_categoria"]').val();
						if (!id_centro_de_custo || id_centro_de_custo <= 0 || id_centro_de_custo == undefined) {
							erro = "Voce precisa Definir um Centro de Custo Para Este Pagamento."
						}
						if (!id_categoria_split || id_categoria_split <= 0 || id_categoria_split == undefined) {
							erro = "Voce precisa Definir uma Categoria Para Este Pagamento."
						}
						objetoPagamento.split_pagamento = {
							ativo: false,
							quantidade: 0,
							id_centro_de_custo,
							id_categoria_split
						}
					}
					if (split_recorrente.length > 0) {
						let cada_dia = $('[name=area-custo-recorrente-acada-dia]').val()
						let meses = $('[name=area-custo-recorrente-meses]').val()
						if (parseFloat(cada_dia) <= 0) {
							erro = "Voce precisa adicionar a quantidade de Dias para recorrencia deste Pagamento!";
						}
						if (parseFloat(meses) <= 0) {
							erro = "Voce precisa adicionar a quantidade de Meses para recorrencia deste Pagamento!";
						}
						objetoPagamento.split_recorrente = {
							ativo: true,
							acada: cada_dia,
							meses: meses,
						}
					} else {
						objetoPagamento.split_recorrente = {
							ativo: false,
							acada: 0,
							meses: 0,
						}
					}
					if (CamposPagamentos.length <= 0) {
						erro = "Voce Precisa Adicionar pelo Menos 1 Pagamento"
					} else {
						let item = []
						let valor_total = 0
						CamposPagamentos.each(function(index, input) {
							let data_vencimento = $(input).find('.js-vencimento').val().split('/')
							data_vencimento = data_vencimento[2] + "-" + data_vencimento[1] + "-" + data_vencimento[0];
							let valor = unMoney($(input).find('.js-valor').val())
							let id_formapagamento = $(input).find('.js-id_formapagamento').val()
							valor_total += valor
							if (!id_formapagamento || id_formapagamento <= 0 || id_formapagamento == undefined) {
								erro = "Todos Os Pagamentos Necessitam ter uma forma de Pagamento."
							}
							if (index + 1 == CamposPagamentos.length) {
								if (valor_total < valor_pagamento) {
									erro = `a Soma dos Pagamentos Precisam Ser igual ao Total do Pagamento. <br> R$ ${number_format((valor_pagamento-valor_total),2,",",".")} Faltando`
								} else if (valor_total > valor_pagamento) {
									erro = `a Soma dos Pagamentos Precisam Ser igual ao Total do Pagamento. <br> R$ ${number_format((valor_total-valor_pagamento),2,",",".")} Passando`
								}
							}
							item.push({
								data_vencimento,
								valor,
								id_formapagamento,
							})

						});
						objetoPagamento.pagamentos = item
					}
					//console.log(erro)
					if (erro.length > 0) {
						swal({
							title: "Erro!",
							text: erro,
							type: "error",
							confirmButtonColor: "#424242"
						});
						return;
					}

					let data = `ajax=addPagamento&tipo_beneficiario=${tipo_beneficiario}&id_beneficiario=${id_beneficiario}&data_emissao=${data_emissao}&descricao=${descricao}&valor_pagamento=${valor_pagamento}&objeto=${JSON.stringify(objetoPagamento)}`;
					$.ajax({
						type: "POST",
						url: baseURLApiAsidePagamentos,
						data: data,
						success: function(rtn) {
							if (rtn.sucess) {
								swal({
									title: "Sucesso!",
									text: "Pagamento Adicionado com Sucesso!",
									html: true,
									type: "success",
									confirmButtonColor: "#424242"
								})
								setTimeout(() => {
									window.location.href = window.location.href
								}, 2000)
							} else if (rtn.error) {
								swal({
									title: "Erro!",
									text: rtn.error,
									html: true,
									type: "error",
									confirmButtonColor: "#424242"
								});
							} else {
								console.log('erro desconhecido')
								console.log(rtn)
							}
						},
						error: function(err) {
							console.log(err)
							swal({
								title: "Erro!",
								text: "Algum erro ocorreu ao tentar adicionar este pagamento!",
								html: true,
								type: "error",
								confirmButtonColor: "#424242"
							});
						}
					})
				})
				// conforme vai escrevendo no campo de busca do select ele vai fazendo ajax para buscar no bd
				$('select[name=id_beneficiario]').select2({
					ajax: {
						url: `${baseURLApiAsidePagamentos}`,
						data: function(params) {
							var query = {
								ajax: 'buscaPaciente',
								tipo_beneficiario: $('[name="tipo_beneficiario"]:checked').val(),
								search: params.term,
								type: 'public'
							}
							return query;
						},
						processResults: function(data) {
							return {
								results: data.items
							};
						}

					},
					//templateResult: formatTemplate,
					//dropdownParent: $(".modal")
				});
				// quando seleciona o tipo de beneficiario ele vai popular a lista no select
				$('[name="tipo_beneficiario"]').click(function() {
					let tipo_beneficiario = $(this).val()
					let data = `ajax=buscarUsuarios&tipo_beneficiario=${tipo_beneficiario}`;
					$('select[name=id_beneficiario]').attr('disabled', false)
					return
				})
				$('[name="valor_pagamento"]').on('keyup', function() {
					pagamentosListar()
				})
				//habilitar o split de pagamento
				$('.split-pagamento').click(function() {
					let valor = $(this).prop('checked')
					if (valor == true) {
						$('.js-fieldset-pagamentos-splits').show();
						$('#split-false-centro-custo').hide();
						$('#split-false-categorias').hide();
						$('#split-false-splits-qtd').show();
						if ($('.js-splits-quantidade').val() <= 1) {
							let splits = [];
							let item = {
								id: 1,
								porcentagem: 0,
								centrodecusto: 0,
								categoria: 0,
								calcular_como: '',
								modo_divisao: 'porcentagem'
							}
							splits.push(item)
							$('#splits-pagamentos').text(JSON.stringify(splits))

						}
						atualizaQtdSplits()
						$('.split-custo-recorrente').prop('checked', false)
						$('#area-custo-recorrente').hide();
					} else {
						$('.js-fieldset-pagamentos-splits').hide();
						$('#split-false-categorias').show();
						$('#split-false-centro-custo').show();
						$('#split-false-splits-qtd').hide();
						$('#splits-pagamentos').val("")
					}
				})
				// modificando modo_divisao
				$('[name="modo_divisao"]').click(function() {
					let modo_divisao = $(this).val()
					atualizaQtdSplits()
				})
				//habilitar pagamento recorrente
				$('.split-custo-recorrente').click(function() {
					let valor = $(this).prop('checked')
					if (valor == true) {
						$('#area-custo-recorrente').show();
						$('.split-pagamento').prop('checked', false)
						$('.js-fieldset-pagamentos-splits').hide();
						$('#split-false-categorias').show();
						$('#split-false-centro-custo').show();
						$('#split-false-splits-qtd').hide();
						$('#splits-pagamentos').val("");
						//	$('.js-pagamentos-quantidade').attr('max', 1);
						$('.js-pagamentos-quantidade').val("1");
					} else {
						$('#area-custo-recorrente').hide();
						$('.js-pagamentos-quantidade').attr('max', 36);
					}
					pagamentosListar();
				})
				// modifica quantidade  de splits
				$('.js-splits-quantidade').change(function() {
					atualizaQtdSplits()
				})
				// ajusta valores de porcentagens de splits
				$('.js-fieldset-pagamentos-splits').on('keyup', '[name="porcentagem-splits"]', async function() {
					let modo_divisao = $('[name="modo_divisao"]:checked').val();
					let splits = JSON.parse($('#splits-pagamentos').text()) ?? [];
					let porc = $(this).val()
					if (modo_divisao == 'porcentagem') {
						porc = parseInt(porc)
						if (isNaN(porc)) {
							porc = 0
						}
						$(this).val(porc)
						let allSplits = $('.fpag-item');
						let porcTotal = 0
						allSplits.each((i, item) => {
							let id_split = $(item).find('[name="id-split"]').val()
							let valorPorc = parseFloat($(item).find('[name="porcentagem-splits"]').val())
							let centrodecusto = $(item).find('[name="id_centro_de_custo"]').val()
							let categoria = $(item).find('[name="id_categoria"]').val()
							let calcular_como = $(item).find('[name="calcular_como"]:checked').val()
							porcTotal += valorPorc
							if (porcTotal > 100) {
								porcTotal = porcTotal - valorPorc;
								valorPorc = (100 - porcTotal);
								porcTotal += valorPorc
							} else if (porcTotal < 100) {
								if ((i + 1) == splits.length) {
									let valorPorc1 = valorPorc + (100 - porcTotal);
									porcTotal -= valorPorc
									porcTotal = porcTotal + valorPorc1
									valorPorc = valorPorc1
									// console.log(`o VALOR TOTA ERA: ${}`)
								}
							}
							splits[parseInt(id_split) - 1].valor_split = valorPorc
							splits[parseInt(id_split) - 1].categoria = categoria
							splits[parseInt(id_split) - 1].centrodecusto = centrodecusto
							splits[parseInt(id_split) - 1].modo_divisao = modo_divisao
							splits[parseInt(id_split) - 1].calcular_como = calcular_como
							$(item).find('[name="porcentagem-splits"]').val(valorPorc)
						});
						$('#splits-pagamentos').text(JSON.stringify(splits));

						// listarSplits();
						$(this).focus()
					} else {
						let allSplits = $('.fpag-item');
						let valorTotalInformado = unMoney($('[name="valor_pagamento"]').val());
						let porcTotal = 0
						allSplits.each((i, item) => {
							let id_split = $(item).find('[name="id-split"]').val()
							let valorPorc = unMoney($(item).find('[name="porcentagem-splits"]').val())
							let centrodecusto = $(item).find('[name="id_centro_de_custo"]').val()
							let categoria = $(item).find('[name="id_categoria"]').val()
							let calcular_como = $(item).find('[name="calcular_como"]:checked').val()
							porcTotal += valorPorc
							if (porcTotal > valorTotalInformado) {
								porcTotal = porcTotal - valorPorc;
								valorPorc = (valorTotalInformado - porcTotal);
								porcTotal += valorPorc
							} else if (porcTotal < valorTotalInformado) {
								if ((i + 1) == splits.length) {
									let valorPorc1 = valorPorc + (valorTotalInformado - porcTotal);
									porcTotal -= valorPorc
									porcTotal = porcTotal + valorPorc1
									valorPorc = valorPorc1
								}
							}
							splits[parseInt(id_split) - 1].valor_split = valorPorc
							splits[parseInt(id_split) - 1].categoria = categoria
							splits[parseInt(id_split) - 1].centrodecusto = centrodecusto
							splits[parseInt(id_split) - 1].modo_divisao = modo_divisao
							splits[parseInt(id_split) - 1].calcular_como = calcular_como
							$(item).find('[name="porcentagem-splits"]').val(number_format(valorPorc, 2, ',', '.'))
						});
						$('#splits-pagamentos').text(JSON.stringify(splits))
						//listarSplits();
					}
				})
				// altera quantidade de parcelas
				$('.js-pagamentos-quantidade').change(function() {
					let qtd = $(this).val();
					if ($('.split-custo-recorrente:checked').length > 0) {
						qtd = 1
						swal({
							title: "Erro!",
							text: 'Só Pode Haver 1 Parcela de Pagamento quando o Pagamento Recorrente Estiver Ativado!',
							type: "error",
							confirmButtonColor: "#424242"
						});
					}
					if (!$.isNumeric(eval(qtd))) qtd = 1;
					else if (qtd < 1) qtd = 1;
					else if (qtd >= 36) qtd = 36;
					$('.js-pagamentos-quantidade').val(qtd);
					pagamentosListar();

					// console.log('Atualizando')
				});
				// verifica se ha alteracao na primeira data de pagamento
				$('.js-listar-parcelas').on('change', '.js-vencimento:eq(0)', function() {
					let CamposDatas = $('.js-listar-parcelas').find('.js-vencimento');
					if (CamposDatas.length > 1) {
						let numeroParcelas = CamposDatas.length
						let aux = $('.js-vencimento:eq(0)').val().split("/")
						var startDate = new Date();
						startDate.setDate(aux[0]);
						startDate.setMonth(eval(aux[1]) - 1);
						startDate.setFullYear(aux[2]);

						CamposDatas.each(function(index, input) {
							let newDAte = startDate
							let mes = startDate.getMonth() + 1;
							let dia = startDate.getDate();
							mes = mes <= 9 ? `0${mes}` : mes;
							dia = dia <= 9 ? `0${dia}` : dia;
							newDate = startDate;
							newDate.setMonth(newDate.getMonth() + 1);
							// console.log(newDate)
						})
						//pagamentosListar();
						return
					}
				});
				//verifica se ha alteracao no valor de cada parcela
				$('.js-listar-parcelas').on('keyup', '.js-valor', function() {
					let valorEmCurso = unMoney($('[name="valor_pagamento"]').val())
					let indexInicial = $(this).attr('data-ordem');
					let CamposValor = $('.js-listar-parcelas').find('.js-valor');
					let valorDigitado = unMoney($(this).val());
					let numeroParcelas = CamposValor.length;
					let dataOrdem = ($(this).attr('data-ordem') - 1)
					let erro = "";
					if (valorDigitado > valorEmCurso) {
						swal({
							title: "Erro!",
							text: 'Os valores das parcelas não podem superar o valor total',
							html: true,
							type: "error",
							confirmButtonColor: "#424242"
						});
						let valor = 0
						CamposValor.each(function(index, input) {
							$(input).val(0)
						})
						$(this).val(number_format(valorEmCurso, 2, ",", "."))
						return;
					}

					let valor = 0
					let valorAteInput = valorDigitado
					let valorFinal = 0
					let valorRestante = (valorEmCurso - valorDigitado)

					CamposValor.each(function(index, input) {
						// valorFinal += valorRestante - unMoney($(input).val())
						// if ((index + 1) < dataOrdem) {
						// 	valorRestante = valorRestante - unMoney($(input).val())
						// }
						// if ((index + 1) > dataOrdem) {
						// 	$('.js-listar-parcelas').find(`.js-valor:eq(${index})`).val(number_format(valorRestante / ((numeroParcelas - dataOrdem)), 2, ",", "."))
						// }
					});
				});

				AdicionaMaskaras()
			})
		</script>
		<!-- STYLE  -->
		<style>
			body {
				background: #fff;
			}

			/*the container must be positioned relative:*/
			.custom-select {
				position: relative;
				font-family: Arial;
			}

			.custom-select select {
				display: none;
				/*hide original SELECT element:*/
			}

			.select-selected {
				background-color: DodgerBlue;
			}

			/*style the arrow inside the select element:*/
			.select-selected:after {
				position: absolute;
				content: "";
				top: 14px;
				right: 10px;
				width: 0;
				height: 0;
				border: 6px solid transparent;
				border-color: #fff transparent transparent transparent;
			}

			/*point the arrow upwards when the select box is open (active):*/
			.select-selected.select-arrow-active:after {
				border-color: transparent transparent #fff transparent;
				top: 7px;
			}

			/*style the items (options), including the selected item:*/
			.select-items div,
			.select-selected {
				color: #ffffff;
				padding: 8px 16px;
				border: 1px solid transparent;
				border-color: transparent transparent rgba(0, 0, 0, 0.1) transparent;
				cursor: pointer;
				user-select: none;
			}

			/*style items (options):*/
			.select-items {
				position: absolute;
				background-color: DodgerBlue;
				top: 100%;
				left: 0;
				right: 0;
				z-index: 99;
			}

			/*hide the items when the select box is closed:*/
			.select-hide {
				display: none;
			}

			.select-items div:hover,
			.same-as-selected {
				background-color: rgba(0, 0, 0, 0.1);
			}

			.fc .fc-timegrid-col.fc-day-today {
				background: #fff !important;
			}

			.fc-theme-standard th {
				border-right: transparent !important;
				border-left: transparent !important;
			}

			.fc-scroller {
				overflow: visible !important;
			}

			.fc-row.fc-rigid,
			.fc .fc-scroller-harness {
				overflow: visible !important;
			}

			.fc-scroller,
			fc.day.grid.containet {
				overflow: none !important;
			}

			.fc-timegrid-slot {
				height: 60px !important;
			}

			.fc-scrollgrid-sync-inner {
				height: 90px;
			}

			.fc-scrollgrid {
				border: none !important;
			}

			.fc-scrollgrid-liquid {
				border: none !important;
			}

			.fc-timegrid-now-indicator-line {
				border-color: var(--cinza5) !important;
			}

			.fc-timegrid-now-indicator-arrow {
				border: 0 !important;
				width: 12px;
				height: 12px;
				background: #344848;
				border-radius: 100%;
			}
		</style>
<?php 
	} 
	# ASIDE CONTAS A RECEBER AVULSO
	if(isset($apiConfig['contasAvulsoAReceber'])){		
		?>
		<script>
			$(function() {

			})
			$(".js-valor-pagamento-avulso").ready(function() {
				$('.js-valor-pagamento-avulso').maskMoney({
					thousands: '.',
					decimal: '.'
				});
			});

			$("#pagamento_avulso-receber").click(() => {
				// alert("teste");
				//$(".default").show();
				$("#pagamanetoAvulso").fadeIn(100, function() {
					$("#pagamanetoAvulso .aside__inner9").addClass("active");
				});
			});

			$(".aside-header__fechar").click(() => {
				location.reload();
			})
		</script>
		<section class="aside aside-form" id="pagamanetoAvulso">
			<div class="aside__inner1 aside__inner9">
				<input type="hidden" name="alteracao" value="0">
				<header class="aside-header">
					<h1 class="js-titulo"> Pagamento Avulso</h1>
					<a href="javascript:;" class="aside-header__fechar aside-close"><i class="iconify" data-icon="fluent:dismiss-24-filled"></i></a>
				</header>
				<div class="js-fin js-fin-programacao" style="margin: 20px;">
					<fieldset style="padding:.75rem 1.5rem;">
						<legend>Informações</legend>
						<form class="form" action="">
							<p>Beneficiário</p>
							<div class="colunas3">
								<dl>
									<label><input type="radio" name="tipo_beneficiario" value="fornecedor">Fornecedor</label>
								</dl>
								<dl>
									<label><input type="radio" name="tipo_beneficiario" value="paciente">Paciente</label>
								</dl>
								<dl>
									<label><input type="radio" name="tipo_beneficiario" value="colaborador">Colaborador</label>
								</dl>
							</div>
							<div class="colunas1">
								<dl class="dl2">
									<dl data-select2-id="select2-data-6-t6vw">
										<dd data-select2-id="select2-data-5-bdxs">
											<select name="id_beneficiario" class="select2 obg-0 ajax-id_paciente select2-hidden-accessible" data-select2-id="select2-data-1-ld9e" tabindex="-1" aria-hidden="true">
												<option value="" data-select2-id="select2-data-3-256t">Buscar Beneficiario...
												</option>
											</select><span class="select2 select2-container select2-container--default select2-container--below" dir="ltr" data-select2-id="select2-data-2-n7js" style="width: 100px;"><span class="selection"><span class="select2-selection select2-selection--single" role="combobox" aria-haspopup="true" aria-expanded="false" tabindex="0" aria-disabled="false" aria-labelledby="select2-id_beneficiario-8s-container" aria-controls="select2-id_beneficiario-8s-container"><span class="select2-selection__rendered" id="select2-id_beneficiario-8s-container" role="textbox" aria-readonly="true" title="Buscar Beneficiario...">Buscar
															Beneficiario...</span><span class="select2-selection__arrow" role="presentation"><b role="presentation"></b></span></span></span><span class="dropdown-wrapper" aria-hidden="true"></span></span>
											<a href="javascript:;" class="js-btn-aside button" data-aside="paciente" data-aside-sub><i class="iconify" data-icon="fluent:add-circle-24-regular"></i></a>
										</dd>
									</dl>
								</dl>
							</div>
							<div class="colunas3">
								<dl>
									<dt style="">Valor Total</dt>
									<dd class="form-comp"><span>R$</span>
										<input type="text" class="js-valor-pagamento-avulso" name="valor_pagamento" value="0" />
									</dd>
								</dl>
								<dl>
									<dt>Parcelas</dt>
									<input type="number" class="js-parcelas-Avulso" name="parcelas" maxlength="2" value="0" />
									</dd>
								</dl>
							</div>
							<div>
								<dl>
									<dt style="">Descrição</dt>
									<textarea style="padding: 10px;" class="js-valor" name="descrição" id="" cols="30" rows="10"></textarea>
									</dd>
								</dl>
							</div>
							<div class="parcelamentos-avulsos">
							</div>
							<dl style="margin-top:1.5rem;">
								<dd><button href="javascript:;" class="button button_main" type="button" data-loading="0"><i class="iconify" data-icon="fluent:add-circle-24-regular"></i><span>Adicionar</span></button></dd>
							</dl>
						</form>
					</fieldset>
				</div>
		</section>
		<script>
			$('.js-parcelas-Avulso , .js-valor-pagamento-avulso').on('change', () => {

				parcelar = Number($('.js-parcelas-Avulso').val());
				valor = Number($('.js-valor-pagamento-avulso').maskMoney('unmasked')[0]);
				ValorParcelas = valor / parcelar

				if (parcelar < 0) {
					$('.js-parcelas-Avulso').val(0);
					$('.parcelamentos-avulsos').append(html);

				}
				if (parcelar <= 24) {
					html = ""
					dataAtual = new Date();
					dia = ("0" + dataAtual.getDate()).slice(-2);
					mes = Number(("0" + (dataAtual.getMonth() + 1)).slice(-2));
					ano = Number(dataAtual.getFullYear());
					dataFormatada = dia + '/' + mes + '/' + ano;
					$('.parcelamentos-avulsos').empty();
					for (let index = 1; index <= parcelar; index++) {

						if (mes >= 12) {
							if (mes < 9) {
								dataFormatada = dia + '/' + 0 + (mes = 1) + '/' + (ano = ano + 1);


							} else {
								dataFormatada = dia + '/' + (mes = 1) + '/' + (ano = ano + 1);
							}



						} else {

							if (mes < 9) {
								dataFormatada = dia + '/' + 0 + (mes = mes + 1) + '/' + (ano);
							} else {
								dataFormatada = dia + '/' + (mes = mes + 1) + '/' + (ano);


							}


						}

						if (mes == 1) {

							dataFormatada = dia + '/' + 0 + (mes = 1) + '/' + (ano);

						}

						html = html + `<div class="fpag-item js-pagamento-item" style="margin-top: 20px;">
																	<aside>${index}</aside>
																	<article>
																		<div class="colunas3">
																			<dl>
																				<dd class="form-comp"><span><i class="iconify" data-icon="fluent:calendar-ltr-24-regular"></i></span><input type="tel" name="" class="data js-vencimento" data-ordem="${index}" value="${dataFormatada}"/></dd>
																			</dl>
																			
																			<dl>
																				<dd class="form-comp"><span>R$</span><input type="tel" name="" data-ordem="1" class="valor js-valor" value="${ValorParcelas.toFixed(2)}"></dd>
																			</dl>
																			<dl>
																			<select name="" id=""><?php echo ($optionFormasDePagamento) ?></select>
																			</dl>
																			<dl>
																			<dt>Bandeira</dt>
																			<select class="js-creditoBandeira js-tipoPagamento">
																												<option value="">selecione</option>
																												<optgroup label="nova_operadora"></optgroup><optgroup label="PAG SEGURO">
																												<option value="1" data-parcelas="10" data-parcelas-semjuros="0" data-id_operadora="2" data-id_operadorabandeira="21">MASTERCARD</option>
																												<option value="2" data-parcelas="12" data-parcelas-semjuros="0" data-id_operadora="2" data-id_operadorabandeira="22">VISA</option>
																												<option value="3" data-parcelas="10" data-parcelas-semjuros="0" data-id_operadora="2" data-id_operadorabandeira="23">ELO</option>
																												<option value="4" data-parcelas="10" data-parcelas-semjuros="0" data-id_operadora="2" data-id_operadorabandeira="24">HIPERCARD</option>
																												<option value="5" data-parcelas="1" data-parcelas-semjuros="0" data-id_operadora="2" data-id_operadorabandeira="25">AMEX</option>
																												<option value="6" data-parcelas="1" data-parcelas-semjuros="0" data-id_operadora="2" data-id_operadorabandeira="26">CABAL</option>
																												<option value="7" data-parcelas="1" data-parcelas-semjuros="0" data-id_operadora="2" data-id_operadorabandeira="27">DINERSCLUB</option>
																												<option value="8" data-parcelas="10" data-parcelas-semjuros="0" data-id_operadora="2" data-id_operadorabandeira="28">UNIONPLAY</option></optgroup>	
																												</select>
																			</dl>
																			<dl>
																			<dt>Identificador</dt>
																			<dd><input type="text" class="js-identificador js-tipoPagamento"></dd>
																			</dl>
																			
																			
																		</div>

																		
																	</article>
																</div>`;



					}
					$('.parcelamentos-avulsos').empty();
					$('.parcelamentos-avulsos').append(html);
				} else {
					$('.js-parcelas-Avulso').val(24);
					$('.parcelamentos-avulsos').append(html);

				};


				if (parcelar > 4) {
					$('.parcelamentos-avulsos').css({
						'height': '40vh',
						'overflow-y': 'auto',
					});

				} else {

					$('.parcelamentos-avulsos').css("");

				}


			});
		</script>
<?php 
	}
	# ASIDE CONTAS A PAGAR AVULSO
	if(isset($apiConfig['contasAvulsoAReceberPaciente'])){
		?>
		<script>
			$(".js-valor-pagamento-avulso").ready(function() {
				$('.js-valor-pagamento-avulso').maskMoney({
					thousands: '.',
					decimal: '.'
				});
			});

			$("#pagamento_avulso").click(() => {
				//alert("teste");
				//$(".default").show();
				$("#Pagamento-avulso").fadeIn(100, function() {
					$("#Pagamento-avulso .aside__inner9").addClass("active");
				});
			});

			$(".aside-header__fechar").click(() => {
				location.reload();
			})
		</script>
		<section class="aside aside-form" id="Pagamento-avulso">
			<div class="aside__inner1 aside__inner9">
				<input type="hidden" name="alteracao" value="0">
				<header class="aside-header">
					<h1 class="js-titulo"> Pagamento Avulso</h1>
					<a href="javascript:;" class="aside-header__fechar aside-close"><i class="iconify" data-icon="fluent:dismiss-24-filled"></i></a>
				</header>
				<div class="js-fin js-fin-programacao" style="margin: 20px;">
					<fieldset style="padding:.75rem 1.5rem;">
						<legend>Informações</legend>
						<form class="form" action="">
							<div class="colunas3">

								<dl>
									<dt style="">Valor Total</dt>
									<dd class="form-comp"><span>R$</span>
										<input type="text" class="js-valor-pagamento-avulso" name="valor_pagamento" value="0" />
									</dd>
								</dl>
								<dl>
									<dt>Parcelas</dt>
									<input type="number" class="js-parcelas-Avulso" name="parcelas" maxlength="2" value="0" />
									</dd>
								</dl>
							</div>
							<div>
								<dl>
									<dt style="">Descrição</dt>
									<textarea style="padding: 10px;" class="js-valor" name="descrição" id="" cols="30" rows="10"></textarea>
									</dd>
								</dl>
							</div>

							<div class="parcelamentos-avulsos">

							</div>
							<dl style="margin-top:1.5rem;">
								<dd><button href="javascript:;" class="button button_main" type="button" data-loading="0"><i class="iconify" data-icon="fluent:add-circle-24-regular"></i><span>Adicionar</span></button></dd>
							</dl>

						</form>

					</fieldset>

				</div>

		</section>
		<script>
			$('.js-parcelas-Avulso , .js-valor-pagamento-avulso').on('change', () => {
				// Seu código aqui

				parcelar = Number($('.js-parcelas-Avulso').val());
				valor = Number($('.js-valor-pagamento-avulso').maskMoney('unmasked')[0]);
				ValorParcelas = valor / parcelar

				if (parcelar < 0) {
					$('.js-parcelas-Avulso').val(0);
					$('.parcelamentos-avulsos').append(html);

				}
				if (parcelar <= 24) {
					html = ""
					dataAtual = new Date();
					dia = ("0" + dataAtual.getDate()).slice(-2);
					mes = Number(("0" + (dataAtual.getMonth() + 1)).slice(-2));
					ano = Number(dataAtual.getFullYear());
					dataFormatada = dia + '/' + mes + '/' + ano;
					$('.parcelamentos-avulsos').empty();
					for (let index = 1; index <= parcelar; index++) {

						if (mes >= 12) {
							if (mes < 9) {
								dataFormatada = dia + '/' + 0 + (mes = 1) + '/' + (ano = ano + 1);


							} else {
								dataFormatada = dia + '/' + (mes = 1) + '/' + (ano = ano + 1);
							}



						} else {

							if (mes < 9) {
								dataFormatada = dia + '/' + 0 + (mes = mes + 1) + '/' + (ano);
							} else {
								dataFormatada = dia + '/' + (mes = mes + 1) + '/' + (ano);


							}


						}

						if (mes == 1) {

							dataFormatada = dia + '/' + 0 + (mes = 1) + '/' + (ano);

						}

						html = html + `<div class="fpag-item js-pagamento-item" style="margin-top: 20px;">
															<aside>${index}</aside>
															<article>
																<div class="colunas3">
																	<dl>
																		<dd class="form-comp"><span><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" aria-hidden="true" focusable="false" width="1em" height="1em" style="vertical-align: -0.125em;-ms-transform: rotate(360deg); -webkit-transform: rotate(360deg); transform: rotate(360deg);" preserveAspectRatio="xMidYMid meet" viewBox="0 0 24 24" class="iconify" data-icon="fluent:calendar-ltr-24-regular"><path fill="currentColor" d="M17.75 3A3.25 3.25 0 0 1 21 6.25v11.5A3.25 3.25 0 0 1 17.75 21H6.25A3.25 3.25 0 0 1 3 17.75V6.25A3.25 3.25 0 0 1 6.25 3h11.5Zm1.75 5.5h-15v9.25c0 .966.784 1.75 1.75 1.75h11.5a1.75 1.75 0 0 0 1.75-1.75V8.5Zm-11.75 6a1.25 1.25 0 1 1 0 2.5a1.25 1.25 0 0 1 0-2.5Zm4.25 0a1.25 1.25 0 1 1 0 2.5a1.25 1.25 0 0 1 0-2.5Zm-4.25-4a1.25 1.25 0 1 1 0 2.5a1.25 1.25 0 0 1 0-2.5Zm4.25 0a1.25 1.25 0 1 1 0 2.5a1.25 1.25 0 0 1 0-2.5Zm4.25 0a1.25 1.25 0 1 1 0 2.5a1.25 1.25 0 0 1 0-2.5Zm1.5-6H6.25A1.75 1.75 0 0 0 4.5 6.25V7h15v-.75a1.75 1.75 0 0 0-1.75-1.75Z"></path></svg></span><input type="tel" name="" class="data js-vencimento" data-ordem="1" value="${dataFormatada}"></dd>
																	</dl>
																	<dl>
																		<dd class="form-comp"><span>R$</span><input type="tel" name="" data-ordem="1" class="valor js-valor" value="${ValorParcelas.toFixed(2)}"></dd>
																	</dl>
																	<dl>
																	<select name="" id=""><?php echo ($optionFormasDePagamento) ?></select>
																	</dl>
																	<dl>
																	<dt>Bandeira</dt>
																	<select class="js-creditoBandeira js-tipoPagamento">
																										<option value="">selecione</option>
																										<optgroup label="nova_operadora"></optgroup><optgroup label="PAG SEGURO">
																										<option value="1" data-parcelas="10" data-parcelas-semjuros="0" data-id_operadora="2" data-id_operadorabandeira="21">MASTERCARD</option>
																										<option value="2" data-parcelas="12" data-parcelas-semjuros="0" data-id_operadora="2" data-id_operadorabandeira="22">VISA</option>
																										<option value="3" data-parcelas="10" data-parcelas-semjuros="0" data-id_operadora="2" data-id_operadorabandeira="23">ELO</option>
																										<option value="4" data-parcelas="10" data-parcelas-semjuros="0" data-id_operadora="2" data-id_operadorabandeira="24">HIPERCARD</option>
																										<option value="5" data-parcelas="1" data-parcelas-semjuros="0" data-id_operadora="2" data-id_operadorabandeira="25">AMEX</option>
																										<option value="6" data-parcelas="1" data-parcelas-semjuros="0" data-id_operadora="2" data-id_operadorabandeira="26">CABAL</option>
																										<option value="7" data-parcelas="1" data-parcelas-semjuros="0" data-id_operadora="2" data-id_operadorabandeira="27">DINERSCLUB</option>
																										<option value="8" data-parcelas="10" data-parcelas-semjuros="0" data-id_operadora="2" data-id_operadorabandeira="28">UNIONPLAY</option></optgroup>	
																										</select>
																	</dl>
																	<dl>
																	<dt>Identificador</dt>
																	<dd><input type="text" class="js-identificador js-tipoPagamento"></dd>
																	</dl>
																	
																	
																</div>

																
															</article>
														</div>`;



					}
					$('.parcelamentos-avulsos').empty();
					$('.parcelamentos-avulsos').append(html);
				} else {
					$('.js-parcelas-Avulso').val(24);
					$('.parcelamentos-avulsos').append(html);

				};


				if (parcelar > 4) {
					$('.parcelamentos-avulsos').css({
						'height': '40vh',
						'overflow-y': 'auto',
					});

				} else {

					$('.parcelamentos-avulsos').css("");

				}

			});
		</script>
<?php 
	}
?>