<?php
	include "includes/header.php";
	include "includes/nav.php";
	require_once("includes/header/headerPacientes.php");

	// configuracao da pagina
		$_table=$_p."pacientes_tratamentos";
		$_page=basename($_SERVER['PHP_SELF']);


	// dados
		// clinica
			$clinica='';
			$sql->consult($_p."clinica","*","");
			$clinica=mysqli_fetch_object($sql->mysqry);

		// profissionais
			$_profissionais=array();
			$sql->consult($_p."colaboradores","id,nome,calendario_iniciais,foto,calendario_cor,check_agendamento,contratacaoAtiva","where tipo_cro<>'' and lixo=0 order by nome asc");
			while($x=mysqli_fetch_object($sql->mysqry)) $_profissionais[$x->id]=$x;

		// procedimentos
			$_procedimentos=array();
			$sql->consult($_p."parametros_procedimentos","*","where lixo=0");
			while($x=mysqli_fetch_object($sql->mysqry)) $_procedimentos[$x->id]=$x;

		// politica de Pagamento
			$_politicas=array();
			$sql->consult($_p."parametros_politicapagamento","*","where lixo=0 and status=0");
			while($x=mysqli_fetch_object($sql->mysqry)) $_politicas[$x->id]=$x;

		// regioes
			$_regioesOpcoes=array();
			$sql->consult($_p."parametros_procedimentos_regioes_opcoes","*","order by titulo asc");
			while($x=mysqli_fetch_object($sql->mysqry)) $_regioesOpcoes[$x->id_regiao][]=$x;

			$_regioes=array();
			$sql->consult($_p."parametros_procedimentos_regioes","*","");
			while($x=mysqli_fetch_object($sql->mysqry)) $_regioes[$x->id]=$x;

			$_regioesFaces=array();
			$_regioesFacesOptions='';
			$_regioesInfos=array();
			$sql->consult($_p."parametros_procedimentos_regioes_faces","*"," order by titulo asc");
			while($x=mysqli_fetch_object($sql->mysqry)) {
				$_regioesFaces[$x->id]=$x;
				$_regioesFacesOptions.='<option value="'.$x->id.'">'.utf8_encode($x->titulo).'</option>';
				$_regioesInfos[$x->id]=array('abreviacao'=>$x->abreviacao,'titulo'=>utf8_encode($x->titulo));
			}

		// situacao
			$_selectSituacaoOptions=array('aprovado'=>array('titulo'=>'APROVADO','cor'=>'green'),
											'naoAprovado'=>array('titulo'=>'REPROVADO','cor'=>'red'));

			$selectSituacaoOptions='';
			foreach($_selectSituacaoOptions as $key=>$value) {
				$selectSituacaoOptions.='<option value="'.$key.'">'.$value['titulo'].'</option>';
			}

		// formas de pagamento
			$_formasDePagamento=array();
			$optionFormasDePagamento='';
			$sql->consult($_p."parametros_formasdepagamento","*","where lixo=0 order by titulo asc");
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
				$_operadoras[$x->id]=$x;
				$creditoBandeiras[$x->id]=array('titulo'=>utf8_encode($x->titulo),'bandeiras'=>array());
				$debitoBandeiras[$x->id]=array('titulo'=>utf8_encode($x->titulo),'bandeiras'=>array());
			}

			$sql->consult($_p."parametros_cartoes_operadoras_bandeiras","*","where lixo=0");
			while($x=mysqli_fetch_object($sql->mysqry)) {
				if(isset($_bandeiras[$x->id_bandeira]) and isset($_operadoras[$x->id_operadora])) {
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
						$creditoBandeiras[$x->id_operadora]['bandeiras'][$x->id_bandeira]=array('id_bandeira'=>$x->id_bandeira,
																								'titulo'=>utf8_encode($bandeira->titulo),
																								'parcelas'=>$x->credito_parcelas,
																								'semJuros'=>$x->credito_parcelas_semjuros);
					}
				}
			}



			/*$_semJuros=array();
			$sql->consult($_p."parametros_cartoes_taxas_semjuros","*","where lixo=0");
			while($x=mysqli_fetch_object($sql->mysqry)) {
				$_semJuros[$x->id_operadora][$x->id_bandeira]=$x->semjuros;
			}


			$sql->consult($_p."parametros_cartoes_taxas","*","where lixo=0");
			$_taxasCredito=$_taxasCreditoSemJuros=array();
			while($x=mysqli_fetch_object($sql->mysqry)) {
				if(isset($_bandeiras[$x->id_bandeira])) {
					$bandeira=$_bandeiras[$x->id_bandeira];
					if($x->operacao=="credito") {
						if(isset($creditoBandeiras[$x->id_operadora])) {
							$semJurosTexto="";
							if($bandeira->parcelasAte>0) {
								$semJurosTexto.=" - em ate ".$bandeira->parcelasAte."x";
							}
							if(!isset($_taxasCredito[$x->id_operadora][$bandeira->id][$x->parcela])) {
								$_taxasCredito[$x->id_operadora][$bandeira->id][$x->parcela]=$x->taxa;
							}

							$creditoBandeiras[$x->id_operadora]['bandeiras'][$bandeira->id]=array('id_bandeira'=>$bandeira->id,
																								//	'semJuros'=>$semJuros,
																									'parcelas'=>$bandeira->parcelasAte,
																									'taxa'=>$x->taxa,	
																									'titulo'=>utf8_encode($bandeira->titulo).$semJurosTexto);
						}
					} else {

						$debitoBandeiras[$x->id_operadora]['bandeiras'][$bandeira->id]=array('id_bandeira'=>$bandeira->id,
																								'titulo'=>utf8_encode($bandeira->titulo),
																								'taxa'=>$x->taxa);
					}

				}
			}*/


	// formulario
		$cnt='';
		$campos=explode(",","titulo,id_profissional,tempo_estimado,pagamento,id_politica,tipo_financeiro,parcelas");
			
		foreach($campos as $v) $values[$v]='';
		$values['procedimentos']="[]";
		$values['pagamentos']="[]";

		if(isset($_GET['edita']) and is_numeric($_GET['edita'])) {
			$sql->consult($_table,"*","where id=".$_GET['edita']);
			if($sql->rows) {
				$cnt=mysqli_fetch_object($sql->mysqry);
				$values=$adm->values($campos,$cnt);
				// Procedimentos
					$procedimentosRegs=$usuariosIds=array();
					$where="where id_tratamento=$cnt->id and id_paciente=$paciente->id and lixo=0";
					$sql->consult($_table."_procedimentos","*",$where);
					while($x=mysqli_fetch_object($sql->mysqry)) {
						$usuariosIds[$x->id_usuario]=$x->id_usuario;
						$procedimentosRegs[]=$x;
					}

					$_usuarios=array();
					if(count($usuariosIds)>0) {
						$sql->consult($_p."colaboradores","id,nome","where id IN (".implode(",",$usuariosIds).")");
						while ($x=mysqli_fetch_object($sql->mysqry)) {
							$_usuarios[$x->id]=$x;
						}
					}


					$procedimentos=array();
					foreach($procedimentosRegs as $x) {
						/*$profissional=isset($_profissionais[$x->id_profissional])?$_profissionais[$x->id_profissional]:'';
						$iniciaisCor='';
						$iniciais='?';
						if(is_object($profissional)) {
							$iniciais=$profissional->calendario_iniciais;

							$iniciaisCor=$profissional->calendario_cor;
						}*/

						$valor=$x->valorSemDesconto;
						//if($x->quantitativo==1) $valor*=$x->quantidade;

						$autor = isset($_usuarios[$x->id_usuario]) ? utf8_encode($_usuarios[$x->id_usuario]->nome) : 'Desconhecido';

						$facesArray=[];
						$aux=explode(",",$x->faces);
						foreach($aux as $f) {
							if(!empty($f) and is_numeric($f)) $facesArray[]=$f;
						}

						$valorCorrigido=$x->valor;

						if($x->quantitativo==1) $valorCorrigido*=$x->quantidade;
						else if($x->face==1)  $valorCorrigido*=count($facesArray);
						else if($x->id_regiao==5) $valorCorrigido*=$x->hof;

						$procedimentos[]=array('id'=>$x->id,
												'autor'=>$autor,
												'data'=>date('d/m/Y H:i',strtotime($x->data)),
												'id_procedimento'=>(int)$x->id_procedimento,
												'procedimento'=>utf8_encode($x->procedimento),
												//'id_profissional'=>(int)$x->id_profissional,
												'profissional'=>utf8_encode($x->profissional),
												'id_plano'=>(int)$x->id_plano,
												'plano'=>utf8_encode($x->plano),
												'quantitativo'=>(int)$x->quantitativo,
												'quantidade'=>(int)$x->quantidade,
												'id_opcao'=>(int)$x->id_opcao,
												'opcao'=>utf8_encode($x->opcao),
												'valorCorrigido'=>(float)$valorCorrigido,
												'valor'=>(float)$valor,
												'desconto'=>(float)$x->desconto,
												'obs'=>utf8_encode($x->obs),
												'situacao'=>$x->situacao,
												'id_regiao'=>$x->id_regiao,
												'face'=>$x->face,
												'faces'=>$facesArray,
												'hof'=>$x->hof);
												/*'iniciais'=>$iniciais,
												'iniciaisCor'=>$iniciaisCor);*/
					}

					if($cnt->status=="APROVADO") {
						$values['procedimentos']=json_encode($procedimentos);
					} else {
						$values['procedimentos']=empty($cnt->procedimentos)?"[]":utf8_encode($cnt->procedimentos);
					}
				// Pagamentos
					$pagamentos=array();
					$where="where id_tratamento=$cnt->id and id_paciente=$paciente->id and lixo=0";
					$sql->consult($_table."_pagamentos","*",$where);
					while($x=mysqli_fetch_object($sql->mysqry)) {
						$pagamentos[]=array('id'=>$x->id,
												'vencimento'=>date('d/m/Y',strtotime($x->data_vencimento)),
												'valor'=>(float)$x->valor);
					}

					if($cnt->status=="APROVADO") {
						$values['pagamentos']=json_encode($pagamentos);
					} else {
						$values['pagamentos']=empty($cnt->pagamentos)?"[]":utf8_encode($cnt->pagamentos);
					}

					$values['pagamentos']=empty($cnt->pagamentos)?"[]":utf8_encode($cnt->pagamentos);
			}
		}

		if(is_object($cnt)) {
			if(isset($_GET['deletaTratamento']) and $_GET['deletaTratamento']==$cnt->id) {
				$vsql="lixo=1";
				$vwhere="where id='".addslashes($_GET['deletaTratamento'])."'";
				$sql->consult($_table,"*",$vwhere);
				if($sql->rows) {
					$x=mysqli_fetch_object($sql->mysqry);

					if($x->status=="APROVADO") {
						$jsc->jAlert("Não é possível excluir tratamentos aprovados!","erro","");

					} else {
						$sql->update($_table,$vsql,$vwhere);
						$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='update',vsql='".addslashes($vsql)."',vwhere='".addslashes($vwhere)."',tabela='".$_table."',id_reg='".$x->id."'");

						$sql->update($_table."_pagamentos","lixo=1,lixo_obs=6,lixo_data=now(),lixo_id_usuario=$usr->id","where id_tratamento=$x->id and id_paciente=$paciente->id");

						$adm->biCategorizacao();
						$jsc->jAlert("Tratamento excluído com sucesso!","sucesso","document.location.href='pg_pacientes_planosdetratamento.php?$url'");
					}
				}
			}
		} else {
			$sql->consult($_table,"id","where id_paciente=$paciente->id");
			$values['titulo']="Plano de tratamento ".($sql->rows+1);
		}
		$tratamentoAprovado=(is_object($cnt) and $cnt->status=="APROVADO")?true:false;

	// submit
		if(isset($_POST['acao'])) {
			if($_POST['acao']=="wlib") {
				$processa=true;
				if(empty($cnt)) {
					$sql->consult($_p."pacientes_tratamentos","*","where id_paciente=$paciente->id and titulo='".addslashes($_POST['titulo'])."' and lixo=0");
					if($sql->rows) {
						$x=mysqli_fetch_object($sql->mysqry);	
						//$processa=false;
						//$jsc->go("?form=1&id_paciente=$paciente->id&edita=$x->id");
						//die();
					}
				}
				if($processa===true) {	
					// persiste as informacoes do tratamento
					if($_POST['acao']=="wlib") {
						if(isset($_POST['tipo_financeiro']) && $_POST['tipo_financeiro']=='politica'){
							$_POST['parcelas'] = count(json_decode($_POST['pagamentos']))??0;
						}
						$vSQL=$adm->vSQL($campos,$_POST);
						$values=$adm->values;
					
						if($tratamentoAprovado===false) {
							$vSQL.="procedimentos='".addslashes(utf8_decode($_POST['procedimentos']))."',";
							$vSQL.="pagamentos='".addslashes(utf8_decode($_POST['pagamentos']))."',";
						}
						$idProfissional=(isset($_POST['id_profissional']) and is_numeric($_POST['id_profissional']))?$_POST['id_profissional']:0;

						if(isset($_POST['parcelas']) and is_numeric($_POST['parcelas'])) $vSQL.="parcelas='".$_POST['parcelas']."',";
						if(isset($_POST['pagamento'])) $vSQL.="pagamento='".$_POST['pagamento']."',";

						if(is_object($cnt)) {
							if(!empty($vSQL)) {
								$vSQL=substr($vSQL,0,strlen($vSQL)-1);
								$vWHERE="where id='".$cnt->id."'";
								$sql->update($_table,$vSQL,$vWHERE);
								$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='update',vsql='".addslashes($vSQL)."',vwhere='".addslashes($vWHERE)."',tabela='".$_table."',id_reg='".$cnt->id."'");
								$id_tratamento=$cnt->id;

								if($tratamentoAprovado===false) {
									$sql->update($_table."_procedimentos","lixo=1","where id_tratamento=$id_tratamento and id_paciente=$paciente->id");
									$sql->update($_table."_pagamentos","lixo=1,lixo_obs=1,lixo_data=now(),lixo_id_usuario=$usr->id","where id_tratamento=$id_tratamento and id_paciente=$paciente->id");
								}
							} else {

								$id_tratamento=$cnt->id;
							}
						} else {
							$vSQL.="data=now(),id_paciente=$paciente->id";
							//echo $_table." ".$vSQL;die();
							$sql->add($_table,$vSQL);
							$id_tratamento=$sql->ulid;
							$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='insert',vsql='".addslashes($vSQL)."',tabela='".$_table."',id_reg='".$id_tratamento."'");
						}

						$sql->update($_table."_procedimentos","id_profissional=$idProfissional","where id_tratamento=$id_tratamento");
					}
					if(isset($_POST['status']) and !empty($_POST['status'])) {
						if(is_object($cnt)) {
							$persistir=true;
							$msgOk='';
							$erro='';


							// Baixas de pagamento
							$pagamentosUnidosIds=array(-1);
							$pagamentosBaixas=0;
							$sql->consult($_table."_pagamentos","*","where id_tratamento=$cnt->id and lixo=0");
							if($sql->rows) {
								$pagamentosIds=array(-1);
								while($x=mysqli_fetch_object($sql->mysqry)) {
									$pagamentosIds[]=$x->id;

									// se for pagamento de fusao/uniao
									if($x->id_fusao>0) {
										$pagamentosUnidosIds[$x->id_fusao]=$x->id_fusao;
									}
								}

								// retorna pagamentos unidos
								$sql->consult($_table."_pagamentos","*","where id IN (".implode(",",$pagamentosUnidosIds).") and fusao=1 and lixo=0");
								if($sql->rows) {
									while($x=mysqli_fetch_object($sql->mysqry)) {
										$pagamentosIds[]=$x->id;
									}
								}

								
								$sql->consult($_table."_pagamentos_baixas","*","where id_pagamento IN (".implode(",",$pagamentosIds).") and lixo=0");
								$pagamentosBaixas=$sql->rows;

							}


							// APROVACAO
								if($_POST['status']=="APROVADO") { 

									// verifica se todos procedimentos estao com situacao/status APROVADO, OBSERVADO e/ou REPROVADO
										$erro="";
										if(isset($_POST['procedimentos'])  and !empty($_POST['procedimentos'])) {
									
											$procedimetosJSON=!empty($_POST['procedimentos'])?json_decode($_POST['procedimentos']):array();

											if(is_array($procedimetosJSON)){ 
												foreach($procedimetosJSON as $x) {
													$x=(object)$x;
													if($x->situacao=='aguardandoAprovacao') {
														$erro='Para aprovar o tratamento, é necessário aprovar/reprovar todos os procedimentos';
														$persistir=false;
														break;
													}
													/*if($x->situacao=="aprovado" and ($x->id_profissional==0 or empty($x->id_profissional))) {
														$erro='Para aprovar o tratamento, é necessário selecionar o Profissional para todos os procedimentos aprovados';
														$persistir=false;
														break;
													}*/
												}
											}
										};

									// verifica se financeiro bate
										if(empty($erro)) {
											$valorProcedimento=0;
											if(isset($_POST['procedimentos'])  and !empty($_POST['procedimentos'])) {
										
												$procedimetosJSON=!empty($_POST['procedimentos'])?json_decode($_POST['procedimentos']):array();

												if(is_array($procedimetosJSON)){ 
													foreach($procedimetosJSON as $x) {
														$x=(object)$x;
														if($x->situacao=='aprovado') {


															$qtd=1;
															if($x->quantitativo==1) $qtd=$x->quantidade;
															else if($x->face==1) $qtd=count($x->faces);
															else if($x->id_regiao==5) $qtd=$x->hof;

															$valorProcedimento+=number_format($x->valor*$qtd,2,".","");
															$valorProcedimento-=$x->desconto;

														}
													}
												}
											};

											$valorPagamento=0;
											
											if(isset($_POST['pagamentos'])  and !empty($_POST['pagamentos'])) {
												$pagamentosJSON=json_decode($_POST['pagamentos']);
												if(is_array($pagamentosJSON)) {
													foreach($pagamentosJSON as $x) {
														$x=(object)$x;
														$valorPagamento+=$x->valor;
													} 
												}
											}
											$valorPagamento=number_format($valorPagamento,2,".","");
											$valorProcedimento=number_format($valorProcedimento,2,".","");

											//echo $valorPagamento." ".$valorProcedimento." = ".abs($valorPagamento - $valorProcedimento);die();
											if(!(abs($valorPagamento - $valorProcedimento) < 0.50000001)) {
												$erro="Defina as parcelas de pagamento!";
											} 
										}

									


									if(empty($erro)) {
										if($cnt->status=="PENDENTE" or $cnt->status=="CANCELADO") {
											$sql->update($_table,"status='APROVADO',id_aprovado=$usr->id,data_aprovado=now()","where id=$cnt->id");
											$msgOk="Plano de Tratamento foi <b>APROVADO</b> com sucesso!";
										} else {
											$erro="Este tratamento já está APROVADO";
											$persistir=false;
										}
									}
								}

							// PENDENTE
								else if($_POST['status']=="PENDENTE") {
									if($pagamentosBaixas==0) {
										if($cnt->status=="APROVADO" || $cnt->status=="CANCELADO") {


											if(empty($erro)) {

												$sql->update($_table,"status='PENDENTE',id_aprovado=0,data_aprovado='0000-00-00 00:00:00'","where id=$cnt->id");
												$msgOk="Plano de Tratamento foi <b>ABERTO</b> com sucesso!";
												$persistir=false;


												// remove os pagamentos com fusao
												$sql->consult($_table."_pagamentos","*","where id_tratamento=$cnt->id and id_fusao>0");
												$pagamentosUnidosIds=array(-1);
												while($x=mysqli_fetch_object($sql->mysqry)) {
													$pagamentosUnidosIds[$x->id_fusao]=$x->id_fusao;
												}

												// retorna pagamentos de uniao
												$pagamentosFusaoIds=array(-1);
												$sql->consult($_table."_pagamentos","*","where id IN (".implode(",",$pagamentosUnidosIds).") and fusao=1");
												while($x=mysqli_fetch_object($sql->mysqry)) {
													$pagamentosFusaoIds[$x->id_fusao]=$x->id_fusao;
												}

												// retorna procedimentos de evolucao
												$tratamentosProdecimentosIds=array(0);
												$sql->consult($_table."_procedimentos","id","where id_tratamento=$cnt->id");
												while($x=mysqli_fetch_object($sql->mysqry)) $tratamentosProdecimentosIds[]=$x->id;

												$sql->update($_table."_procedimentos_evolucao","lixo=1","where id_tratamento_procedimento IN (".implode(",",$tratamentosProdecimentosIds).")");

												$sql->update($_table."_procedimentos","lixo=1","where id_tratamento=$cnt->id");
												$sql->update($_table."_pagamentos","lixo=1,lixo_obs='2 $cnt->id or id_fusao IN (".implode(",", $pagamentosFusaoIds).")',lixo_data=now(),lixo_id_usuario=$usr->id","where id_tratamento=$cnt->id");// or id_fusao IN (".implode(",", $pagamentosFusaoIds).")");
												//$sql->update($_table,"pagamentos=''","where id=$cnt->id");

											}



										} else {
											$erro="Este tratamento já está PENDENTE";
											$persistir=false;
										}
									} else {
										$erro="Para <b>REABRIR</b> este tratamento, estorne todas as suas baixas de pagamento!";
										$persistir=false;
									}
								}

							// CANCELADO
								else if($_POST['status']=="CANCELADO") {

									

									if($pagamentosBaixas==0) {

										if($cnt->status=="APROVADO" || $cnt->status=="PENDENTE") {
											$sql->update($_table,"status='CANCELADO',id_aprovado=0,data_aprovado='0000-00-00 00:00:00'","where id=$cnt->id");
											$msgOk="Plano de Tratamento foi <b>REPROVADO</b> com sucesso!";
											$persistir=false;

											// remove os pagamentos com fusao
											$sql->consult($_table."_pagamentos","*","where id_tratamento=$cnt->id and id_fusao>0");
											$pagamentosUnidosIds=array(-1);
											while($x=mysqli_fetch_object($sql->mysqry)) {
												$pagamentosUnidosIds[$x->id_fusao]=$x->id_fusao;
											}

											// retorna pagamentos de uniao
											$pagamentosFusaoIds=array(-1);
											$sql->consult($_table."_pagamentos","*","where id IN (".implode(",",$pagamentosUnidosIds).") and fusao=1");
											while($x=mysqli_fetch_object($sql->mysqry)) {
												$pagamentosFusaoIds[$x->id_fusao]=$x->id_fusao;
											}


											// retorna procedimentos de evolucao
											$tratamentosProcedimentosIds=array(-1);
											$sql->consult($_table."_procedimentos","id","where id_tratamento=$cnt->id");
											while($x=mysqli_fetch_object($sql->mysqry)) $tratamentosProcedimentosIds[]=$x->id;

											$sql->update($_table."_procedimentos_evolucao","lixo=1","where id_tratamento_procedimento IN (".implode(",",$tratamentosProcedimentosIds).")");

											$sql->update($_table."_procedimentos","lixo=1","where id_tratamento=$cnt->id");
											$sql->update($_table."_pagamentos","lixo=1,lixo_obs=3,lixo_data=now(),lixo_id_usuario=$usr->id","where id_tratamento=$cnt->id or id_fusao IN (".implode(",", $pagamentosFusaoIds).")");
											//$sql->update($_table,"pagamentos=''","where id=$cnt->id");
										} else {
											$erro="Este tratamento já está REPROVADO";
											$persistir=false;
										}
									} else {
										$erro="Não é possível REPROVAR este tratamento, pois ele já teve baixas de pagamentos. Estorne as baixas para poder REPROVÁ-LO!";
										$persistir=false;
									}
								}



							// Persiste informações
							if($persistir===true) {

								// Pagamentos
									if(isset($_POST['pagamentos'])  and !empty($_POST['pagamentos'])) {
										$pagamentosJSON=json_decode($_POST['pagamentos']);
										if(is_array($pagamentosJSON)) {
											$vSQLBaixa=array();
											foreach($pagamentosJSON as $x) {

												$taxasPrazos=array();

												// se for credito/debito
												if(isset($x->id_formapagamento)) {
													// se for credito
													if($x->id_formapagamento==2 and isset($x->creditoBandeira) and isset($x->id_operadora) and isset($x->qtdParcelas)) {
														/*$where="where id_bandeira='".$x->creditoBandeira."' and id_operadora='".$x->id_operadora."' and vezes='".$x->qtdParcelas."' and operacao='credito' and lixo=0";
														$sql->consult($_p."parametros_cartoes_taxas","parcela,taxa,prazo",$where);
														
														if($sql->rows) {
															while($t=mysqli_fetch_object($sql->mysqry)) {
																$taxasPrazos[$t->parcela]=$t;
															}
														}*/

														$where="where id_bandeira='".$x->creditoBandeira."' and id_operadora='".$x->id_operadora."' and check_credito=1 and lixo=0";
														$sql->consult($_p."parametros_cartoes_operadoras_bandeiras","*",$where);
														if($sql->rows) {
															$tx=mysqli_fetch_object($sql->mysqry);
															$taxasPrazos = json_decode($tx->taxas,true);
														}
													}
													// se for debito
													else if($x->id_formapagamento==3 and isset($x->debitoBandeira) and isset($x->id_operadora)) {
														/*$where="where id_bandeira='".$x->debitoBandeira."' and id_operadora='".$x->id_operadora."' and operacao='debito' and lixo=0";
														$sql->consult($_p."parametros_cartoes_taxas","parcela,taxa,prazo",$where);
														
														if($sql->rows) {
															while($t=mysqli_fetch_object($sql->mysqry)) {
																$taxasPrazos=$t;
															}
														}*/

														$where="where id_bandeira='".$x->debitoBandeira."' and id_operadora='".$x->id_operadora."' and check_debito=1 and lixo=0";
														$sql->consult($_p."parametros_cartoes_operadoras_bandeiras","*",$where);
														if($sql->rows) {
															$tx=mysqli_fetch_object($sql->mysqry);
															$taxasPrazos = json_decode($tx->taxas,true);
														}
													}
												}

												
												$vSQLPagamento="lixo=0,
																id_paciente=$paciente->id,
																id_tratamento=$id_tratamento,
																id_formapagamento='".addslashes(isset($x->id_formapagamento)?$x->id_formapagamento:0)."',
																qtdParcelas='".addslashes(isset($x->qtdParcelas)?$x->qtdParcelas:0)."',
																data_vencimento='".addslashes(invDate($x->vencimento))."',
																valor='".addslashes(($x->valor))."',";

												$pagamento='';
												if(isset($x->id) and is_numeric($x->id)) {
													$sql->consult($_table."_pagamentos","*","where id_tratamento=$id_tratamento and id=$x->id");
													if($sql->rows) {
														$pagamento=mysqli_fetch_object($sql->mysqry);
													}
												}

												if(is_object($pagamento)) {
													$vSQLPagamento.="data_alteracao=now(),id_usuario_alteracao=$usr->id";
													$vWHERE="WHERE id=$pagamento->id";
													$sql->update($_table."_pagamentos",$vSQLPagamento,$vWHERE);
													$id_tratamento_pagamento=$sql->ulid;
													$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='update',vsql='".addslashes($vSQLPagamento)."',vwhere='".addslashes($vWHERE)."',tabela='".$_table."_pagamentos',id_reg='".$id_tratamento_pagamento."'");

												} else {
													$vSQLPagamento.="data=now(),id_usuario=$usr->id";
													$sql->add($_table."_pagamentos",$vSQLPagamento);
													$id_tratamento_pagamento=$sql->ulid;
													$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='insert',vsql='".addslashes($vSQLPagamento)."',tabela='".$_table."_pagamentos',id_reg='".$id_tratamento_pagamento."'");
												}


												if(isset($x->id_formapagamento) and is_numeric($x->id_formapagamento) and isset($_formasDePagamento[$x->id_formapagamento])) {
													$f=$_formasDePagamento[$x->id_formapagamento];
													if($f->tipo=="credito") {

														if(isset($x->creditoBandeira) and is_numeric($x->creditoBandeira) and isset($_bandeiras[$x->creditoBandeira])) {

															$b = $_bandeiras[$x->creditoBandeira];

															$id_bandeira=$b->id;
															$id_operadora=$x->id_operadora;

															if(isset($x->qtdParcelas) and is_numeric($x->qtdParcelas)) {
																$valorParcela=$x->valor/$x->qtdParcelas;
																for($i=1;$i<=$x->qtdParcelas;$i++) {

																	$prazo=$taxa=0;
																	/*if(isset($taxasPrazos[$i])) {
																		$prazo=$taxasPrazos[$i]->prazo;
																		$taxa=$taxasPrazos[$i]->taxa;
																	}*/

																	if(isset($taxasPrazos['creditoTaxas'][$x->qtdParcelas][$i])) {
																		$tx=$taxasPrazos['creditoTaxas'][$x->qtdParcelas][$i];

																		$taxa=valor($tx['taxa']);
																		$prazo=$tx['dias'];
																		//var_dump($taxasPrazos['creditoTaxas'][$x->qtdParcelas][$i]);
																	} else echo "n";
																	

																	$dtVencimento=date('Y-m-d',strtotime(invDate($x->vencimento)." + $prazo days"));


																	$vSQLBaixa[]=array("id_pagamento"=>$id_tratamento_pagamento,
																						"data_vencimento"=>$dtVencimento,
																						"valor"=>$valorParcela,
																						"id_formadepagamento"=>$f->id,
																						"parcela"=>$i,
																						"taxa"=>$taxa,
																						"dias"=>$prazo,
																						"parcelas"=>$x->qtdParcelas,
																						"id_bandeira"=>$id_bandeira,
																						"id_operadora"=>$id_operadora,
																						"tipo"=>"credito");
																}
															}
														}

														//echo json_encode($vSQLBaixa);die();
													} else if($f->tipo=="debito") {
														if(isset($x->debitoBandeira) and is_numeric($x->debitoBandeira) and isset($_bandeiras[$x->debitoBandeira])) {

															$b = $_bandeiras[$x->debitoBandeira];

															$id_bandeira=$b->id;
															$id_operadora=$x->id_operadora;

															$prazo=$taxa=0;
															if(is_object($taxasPrazos)) {
																$prazo=$taxasPrazos->prazo;
																$taxa=$taxasPrazos->taxa;
															}

															$dtVencimento=date('Y-m-d',strtotime(invDate($x->vencimento)." + $prazo days"));
															

															$vSQLBaixa[]=array("id_pagamento"=>$id_tratamento_pagamento,
																			"data_vencimento"=>$dtVencimento,
																			"valor"=>$x->valor,
																			"id_formadepagamento"=>$f->id,
																			"taxa"=>$taxa,
																			"id_bandeira"=>$id_bandeira,
																			"id_operadora"=>$id_operadora,
																			"tipo"=>"debito");
																
															
														}
													} else {
														$vSQLBaixa[]=array("id_pagamento"=>$id_tratamento_pagamento,
																			"data_vencimento"=>invDate($x->vencimento),
																			"valor"=>$x->valor,
																			"id_formadepagamento"=>$f->id,
																			"tipo"=>"outros");

													}
												}
											} 

											foreach($vSQLBaixa as $x) {
												$x=(object)$x;
												$vsql="";
												$where="where id_pagamento=$x->id_pagamento";

												if($x->tipo=="credito") $where.=" and id_operadora='".$x->id_operadora."'
																					and id_bandeira='".$x->id_bandeira."' 
																					and parcela='".$x->parcelas."' 
																					and parcela='".$x->parcela."'";
												$baixa='';
												$sql->consult($_p."pacientes_tratamentos_pagamentos_baixas","*",$where);
												if($sql->rows) {
													$baixa=mysqli_fetch_object($sql->mysqry);
												} 

												if(!isset($x->id_operadora)) $x->id_operadora=0;
												if(!isset($x->id_bandeira)) $x->id_bandeira=0;
												if(!isset($x->parcelas)) $x->parcelas=0;
												if(!isset($x->parcela)) $x->parcela=0;

												$vsql="id_pagamento='$x->id_pagamento',
														id_usuario=$usr->id,
														tipoBaixa='pagamento',
														valor='$x->valor',
														taxa='".(isset($x->taxa)?$x->taxa:0)."',
														dias='".(isset($x->dias)?$x->dias:0)."',
														id_formadepagamento='$x->id_formadepagamento',
														data_vencimento='".($x->data_vencimento)."',
														parcelas='$x->parcelas',
														parcela='$x->parcela',
														id_operadora='$x->id_operadora',
														id_bandeira='$x->id_bandeira'";
														//echo $vsql."<BR>";die();
												if(is_object($baixa)) {
													$sql->update($_p."pacientes_tratamentos_pagamentos_baixas",$vsql,"where id=$baixa->id");
												} else {
													$sql->add($_p."pacientes_tratamentos_pagamentos_baixas","data=now(),".$vsql);

												}

											}
										}
									}

								// Procedimentos
									if(isset($_POST['procedimentos'])  and !empty($_POST['procedimentos'])) {
										
										$procedimetosJSON=!empty($_POST['procedimentos'])?json_decode($_POST['procedimentos']):array();
										//echo json_encode($procedimetosJSON);die();
										if(is_array($procedimetosJSON)){ 


											$procedimentosEvolucao=array();
											foreach($procedimetosJSON as $x) {

												if(!isset($x->quantidade)) $x->quantidade=1;

												$vSQLProcedimento="lixo=0,
																	id_paciente=$paciente->id,
																	id_tratamento=$id_tratamento,
																	id_procedimento='".addslashes($x->id_procedimento)."',
																	procedimento='".addslashes(utf8_decode($x->procedimento))."',
																	id_plano='".addslashes($x->id_plano)."',
																	plano='".addslashes(utf8_decode($x->plano))."',
																	profissional='".addslashes(utf8_decode($x->profissional))."',
																	situacao='".addslashes($x->situacao)."',
																	id_profissional='".$idProfissional."',
																	valor='".addslashes($x->valor)."',
																	desconto='".addslashes($x->desconto)."',
																	valorSemDesconto='".addslashes($x->valor)."',
																	quantitativo='".addslashes($x->quantitativo)."',
																	quantidade='".addslashes($x->quantidade)."',
																	id_opcao='".addslashes($x->id_opcao)."',
																	obs='".addslashes(utf8_decode($x->obs))."',
																	opcao='".addslashes(utf8_decode($x->opcao))."',
																	id_regiao='".addslashes($x->id_regiao)."',
																	face='".addslashes(utf8_decode($x->face))."',
																	faces='".(implode(",",$x->faces))."',
																	hof='".(isset($x->hof)?addslashes($x->hof):"")."',";

																	//var_dump($x->faces);die();
																	//id_profissional='".addslashes($x->id_profissional)."',
											
												$procedimento='';
												if(isset($x->id) and is_numeric($x->id)) {
													$sql->consult($_table."_procedimentos","*","where id_tratamento=$id_tratamento and id=$x->id");
													if($sql->rows) {
														$procedimento=mysqli_fetch_object($sql->mysqry);
													}
												}

												if(is_object($procedimento)) {
													$vSQLProcedimento.="data_alteracao=now(),id_usuario_alteracao=$usr->id";
													$vWHERE="WHERE id=$procedimento->id";
													$sql->update($_table."_procedimentos",$vSQLProcedimento,$vWHERE);
													$id_tratamento_procedimento=$procedimento->id;
													$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='update',vsql='".addslashes($vSQLProcedimento)."',vwhere='".addslashes($vWHERE)."',tabela='".$_table."_procedimentos',id_reg='".$id_tratamento_procedimento."'");

												} else {
													$vSQLProcedimento.="data=now(),id_usuario=$usr->id";
													$sql->add($_table."_procedimentos",$vSQLProcedimento);
													$id_tratamento_procedimento=$sql->ulid;
													$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='insert',vsql='".addslashes($vSQLProcedimento)."',tabela='".$_table."_procedimentos',id_reg='".$id_tratamento_procedimento."'");
												}

												if($id_tratamento_procedimento>0) {

													for($i=1;$i<=$x->quantidade;$i++) {
														$procedimentosEvolucao[]=array('id_tratamento_procedimento'=>$id_tratamento_procedimento,
																						'id_paciente'=>$paciente->id,
																						'id_procedimento'=>$x->id_procedimento,
																						'id_profissional'=>$idProfissional,
																						'status_evolucao'=>'iniciar',
																						'numeroTotal'=>$x->quantidade,
																						'numero'=>$i);
													}
												}

											}

											// cria os procedimentos de evolucao
											foreach($procedimentosEvolucao as $x) {
												$x=(object)$x;

												$vSQL="id_tratamento_procedimento=$x->id_tratamento_procedimento,
														id_paciente=$x->id_paciente,
														id_procedimento=$x->id_procedimento,
														id_profissional=$idProfissional,
														status_evolucao='$x->status_evolucao',
														numeroTotal='$x->numeroTotal',
														numero='$x->numero'";
												//echo $vSQL;die();

												$sql->consult($_p."pacientes_tratamentos_procedimentos_evolucao","*","where id_tratamento_procedimento='$x->id_tratamento_procedimento' and numero='$x->numero' and numeroTotal='$x->numeroTotal' and lixo=0");
												if($sql->rows) {
													$reg=mysqli_fetch_object($sql->mysqry);
													
													$sql->add($_p."pacientes_tratamentos_procedimentos_evolucao",$vSQL,"where id=$reg->id");
												} else {
													$sql->add($_p."pacientes_tratamentos_procedimentos_evolucao",$vSQL);
												}
											}
										}
									}
							}

							$adm->biCategorizacao();
							if(empty($erro)) {
								$jsc->jAlert($msgOk,"sucesso","document.location.href='$_page?form=1&edita=$cnt->id&$url'");
								die();
							} else {
								$jsc->jAlert($erro,"erro","document.location.href='$_page?form=1&edita=$cnt->id&$url'");
								die();
							}

						} else {
							$jsc->jAlert("Tratamento não encontrado!","erro","document.location.href='$_page?$url'");
							die();
						}
					} else {
						//$adm->biCategorizacao();e
						$jsc->jAlert("Informações salvas com sucesso!","sucesso","document.location.href='".$_page."?form=1&edita=$id_tratamento&id_paciente=$paciente->id'");
						die();
					}
				}
			}
		}
?>
	<script type="text/javascript">
		var procedimentos = [];
		let valorTotalProcedimentos = 0;
		var valorOriginalProcedimentos = 0;
		var _politicas = <?=json_encode($_politicas);?>;
		var temPolitica = false;
		var pagamentos = JSON.parse(`<?php echo ($values['pagamentos']);?>`);
		var usuario = '<?php echo utf8_encode($usr->nome);?>';
		var id_usuario = <?php echo $usr->id;?>;
		var tratamentoAprovado = <?php echo ($tratamentoAprovado===true)?1:0;?>;
		var contrato = <?=json_encode($values);?>;
		contrato.pagamentos = pagamentos
		contrato.procedimentos = JSON.parse(`<?php echo ($values['procedimentos']);?>`);

		const desativarCampos = () => {
			if(tratamentoAprovado===1) { 
				$('.js-pagamento-item').find('select,input').prop('disabled',true);
				$('#cal-popup').find('select:not(.js-profissional),input').prop('disabled',true);
				$('#cal-popup').find('.js-btn-excluir,.js-btn-descontoAplicarEmTodos').hide();
			}
		}

		const AtualizaPolitica = ()=>{
			$('.filter-title').find('strike').html("")
			$('.js-tipo-politica table').html("")
			if(temPolitica){
				$('[name="id_politica"]').val(temPolitica.id)
				let valorTotal = valorTotalProcedimentos
				let metodosHabilitados = temPolitica.parcelasParametros.metodos
				metodosHabilitados.forEach(x=>{
					let qtdParcelas = 0
					let taxaJuros = 0
					let qtdParcelasTotal = parseInt(x.parcelas)
					let qtdParcelasSemJuros = parseInt(x.parcelaSemJuros)
					let taxaJurosAnual = parseFloat(x.jurosAnual)
					let valorEntrada = valorTotal/qtdParcelasTotal
					let valorParcela = valorTotal/qtdParcelasTotal
					let valorTotalParcelado = valorTotal
					let metodo = x.tipo
					let icone = '<i class="iconify" data-icon="ep:tickets">'
					let textoAux = ""
					switch(metodo){
						case 'dinheiro':
							icone = `ri:money-dollar-box-line`
							textoAux = `No Dinheiro`
						break;
						case 'pix':
							icone = `ic:baseline-pix`
							textoAux = `No Pix`
							break;
						case 'debito':
							icone = `ic:baseline-credit-card`
							textoAux = `No Cartão de Débito`
							break;
						case 'credito':
							icone = `ic:round-credit-card`
							textoAux = `No Cartão de Crédito`
							break;
						case 'boleto':
							icone = `ep:tickets`
							textoAux = `No Boleto`
							break;
						case 'cheque':
							icone = `mdi:cheque-book`
							textoAux = `No Cheque`
							break;
						case 'deposito':
							icone = `mdi:bank`
							textoAux = `No Depósito Bancario`
							break;
						case 'transferencia':
							icone = `fa6-solid:money-bill-transfer`
							textoAux = `Por Transferencia Bancaria`
							break;
					}
					let textCard = `a Vista`
					let valor = valorTotal;
					let desconto = "";
					let acrescimo = "";
					if(x.parcelas==1){
						if(parseFloat(x.descontoAvista)>0){
							valor = (valor-(valor*(parseFloat(x.descontoAvista)/100)))
							desconto = `<em style="background:var(--verde); color:#fff;">desconto de R$ ${number_format(((valor*(parseFloat(x.descontoAvista)/100))),2,",",".")}</em>`
						}
						if(parseFloat(x.jurosAnual)>0){
							let tempo = Math.ceil(qtdParcelasTotal/12)
							valor = valor+(valor*(taxaJurosAnual/100))
							valorTotalParcelado = valor*qtdParcelasTotal
						}
						
						tr =`<tr onclick='politicaEscolhida("${metodo}")'>
								<td class="list1__border" style="color:silver">
								<i class="iconify" data-icon="${icone}" style="font-size:50px">
								</td>
								<td>
									<h1 style="font-size:1.375em;">${textoAux}</h1>
									<p><strong>${textCard}</strong>${desconto}</p>
								</td>	
								<td>
									<p>TOTAL: <strong>R$ ${number_format(valor,2,",",".")}</strong></p>
								</td>									
							</tr>`
						$('.js-tipo-politica table').append(tr)
					}else if(x.parcelas>1){
						if(parseFloat(x.descontoAvista)>0){
							valor = (valor-(valor*(parseFloat(x.descontoAvista)/100)))
							desconto = `<em style="background:var(--verde); color:#fff;">desconto de R$ ${number_format(((valor*(parseFloat(x.descontoAvista)/100))),2,",",".")}</em>`
						}
						if(x.parcelaSemJuros>0){
							textCard = `Em Até ${x.parcelaSemJuros}x sem Juros`
							tr =`<tr onclick='politicaEscolhida("${metodo}")'>
									<td class="list1__border" style="color:silver">
									<i class="iconify" data-icon="${icone}" style="font-size:50px">
									</td>
									<td>
										<h1 style="font-size:1.375em;">${textoAux}</h1>
										<p><strong>${textCard}</strong>${desconto}</p>
									</td>	
									<td>
										<p>TOTAL: <strong>R$ ${number_format(valor,2,",",".")}</strong></p>
									</td>									
								</tr>`
							$('.js-tipo-politica table').append(tr)
							if(parseFloat(x.jurosAnual)>0){
								let tempo = Math.ceil(qtdParcelasTotal/12)
								acrescimo = `<em style="background:var(--cinza3); color:#fff;">acréscimo de R$ ${number_format((valor*(taxaJurosAnual/100)),2,",",".")}</em>`
								valor = valor+(valor*(taxaJurosAnual/100))
							}
							textCard = `Em Até ${x.parcelas}x com Juros`
							tr =`<tr onclick='politicaEscolhida("${metodo}")'>
									<td class="list1__border" style="color:silver">
									<i class="iconify" data-icon="${icone}" style="font-size:50px">
									</td>
									<td>
										<h1 style="font-size:1.375em;">${textoAux}</h1>
										<p><strong>${textCard}</strong>${acrescimo}</p>
									</td>	
									<td>
										<p>TOTAL: <strong>R$ ${number_format(valor,2,",",".")}</strong></p>
									</td>									
								</tr>`
							$('.js-tipo-politica table').append(tr)
						}else{
							valor = valorTotal;
							desconto = "";
							acrescimo = "";
							if(parseFloat(x.jurosAnual)>0){
								let tempo = Math.ceil(qtdParcelasTotal/12)
								valor = valor+(valor*(taxaJurosAnual/100))
							}
							textCard = `Em Até ${x.parcelas}x com Juros`
							tr =`<tr onclick='politicaEscolhida("${metodo}")'>
									<td class="list1__border" style="color:silver">
									<i class="iconify" data-icon="${icone}" style="font-size:50px">
									</td>
									<td>
										<h1 style="font-size:1.375em;">${textoAux}</h1>
										<p><strong>${textCard}</strong></p>
									</td>	
									<td>
										<p>TOTAL: <strong>R$ ${number_format(valor,2,",",".")}</strong></p>
									</td>									
								</tr>`
							$('.js-tipo-politica table').append(tr)
						}
					}
				})
				return
				
			}else{
				atualizaValor(true)
			}
		}

		const politicaEscolhida = (metodo)=>{
			$('#botao-voltar-menu-parcelas').show()
			$('.js-tipo-politica table').html("")
			let x = temPolitica.parcelasParametros.metodos.find((item)=>{
				return item.tipo==metodo
			})
			let valorTotal = valorTotalProcedimentos
			let qtdParcelas = 0
			let taxaJuros = 0
			let qtdParcelasTotal = parseInt(x.parcelas)
			let qtdParcelasSemJuros = parseInt(x.parcelaSemJuros)
			let taxaJurosAnual = parseFloat(x.jurosAnual)
			let valorEntrada = valorTotal/qtdParcelasTotal
			let valorParcela = valorTotal/qtdParcelasTotal
			let valorTotalParcelado = valorTotal
			while(qtdParcelas<qtdParcelasTotal){
				qtdParcelas ++
				valorParcela = valorTotal/qtdParcelas
				valorEntrada = valorParcela
				let valor = 0
				let textoCard = `PARCELA ${qtdParcelas}`
				let texto1 = `texto 1`
				let texto2 = `texto 2`
				let texto3 = `texto 3`
				let texto4 = `texto 4`
				let tr = `<tr>
								<td class="list1__border" style="color:silver"></td>
								<td>
									<h1 style="font-size:1.375em;">NÃO HABILITADO</h1>
									<p><strong></strong></p>
								</td>
								<td></td>
							</tr>`
				if(qtdParcelas==1){
					valor = valorParcela
					texto1 = `A vista`
					texto2 = `R$ ${number_format(valor,2,",",".")}`
					texto3 = ``
					texto4 = ``
					if(parseFloat(x.descontoAvista)>0){
						valor = valor-(valor*(parseFloat(x.descontoAvista)/100))
						textoCard = `De: R$ ${number_format(valorTotal,2,",",".")} Por: ${number_format(valor,2,",",".")} a Vista`
						valorTotalParcelado = valor
						texto1 = `A vista`
						texto2 = `R$ ${number_format(valorTotal,2,",",".")}`
						texto3 = `<em style="background:var(--verde); color:#fff;">desconto de R$ ${number_format((valorTotal-valor),2,",",".")}</em>`
						texto4 = ``
					}
				}else{
					valor = valorParcela
					valorTotalParcelado = valor*qtdParcelas
					if(qtdParcelas<=qtdParcelasSemJuros){
						if(x.entradaMinima.length>0){
							valorEntrada = ((parseFloat(x.entradaMinima))/100)*(valorTotalParcelado)
						 	valorParcela = ((valorTotalParcelado)-valorEntrada)/(qtdParcelas-1)
						 	if(valorEntrada<valorParcela){
						 		valorEntrada = ((valorTotalParcelado))/(qtdParcelas)
						 		valorParcela = ((valorTotalParcelado))/(qtdParcelas)
						 	}
						}
						if(valorEntrada==valorParcela){
							texto1 = `${qtdParcelas}x sem juros`
							texto2 = `R$ ${number_format(valorTotalParcelado,2,",",".")}`
							texto3 = ``
							texto4 = `${qtdParcelas} parcelas de R$ ${number_format(valorParcela,2,",",".")}`
						}else{
							texto1 = `${qtdParcelas}x sem juros`
							texto2 = `R$ ${number_format(valorTotalParcelado,2,",",".")}`
							texto3 = ``
							texto4 = `1 parcela de R$ ${number_format(valorEntrada,2,",",".")} + ${qtdParcelas-1} de R$ ${number_format(valorParcela,2,",",".")}`
						}

					}else{
						let tempo = Math.ceil(qtdParcelasTotal/12)
						let montante = valor+(1+(taxaJurosAnual/100))*(qtdParcelas/12)
						//valor = valor+(valor*(taxaJurosAnual/100))
						valor = valor+(valor*(taxaJurosAnual/100))*(qtdParcelas/12)

						valorTotalParcelado = valor*qtdParcelas
						valorEntrada = valorParcela = valor
						if(x.entradaMinima.length>0){
							valorEntrada = ((parseFloat(x.entradaMinima))/100)*(valorTotalParcelado)
							valorParcela = ((valorTotalParcelado)-valorEntrada)/(qtdParcelas-1)
							if(valorEntrada<valorParcela){
								valorEntrada = ((valorTotalParcelado))/(qtdParcelas)
								valorParcela = ((valorTotalParcelado))/(qtdParcelas)
							}
						}
						if(valorEntrada==valorParcela){
							texto1 = `${qtdParcelas}x com juros`
							texto2 = `R$ ${number_format(valorTotalParcelado,2,",",".")}`
							texto3 = `<em style="background:var(--cinza3); color:#fff;">acréscimo de R$ ${number_format((valorTotalParcelado-valorTotal),2,",",".")}</em>`
							texto4 = `${qtdParcelas} parcelas de R$ ${number_format(valorParcela,2,",",".")}`
						}else{
							texto1 = `${qtdParcelas}x com juros`
							texto2 = `R$ ${number_format(valorTotalParcelado,2,",",".")}`
							texto3 = `<em style="background:var(--cinza3); color:#fff;">acréscimo de R$ ${number_format((valorTotalParcelado-valorTotal),2,",",".")}</em>`
							texto4 = `1 parcela de R$ ${number_format(valorEntrada,2,",",".")} + ${qtdParcelas-1} de R$ ${number_format(valorParcela,2,",",".")}`
						}
					}
				}
				tr =`<tr onclick='EscolheParcelas("${metodo}",${qtdParcelas},${valor})'>
						<td class="list1__border" style="color:silver"></td>
						<td>
							<h1 style="font-size:1.375em;">${texto1}</h1>
							<p><strong>${texto2}</strong>${texto3}</p>
						</td>
						<td>${texto4}</td>
					</tr>`
				$('.js-tipo-politica table').append(tr)
			}	
		}

		const EscolheParcelas = (metodo,qtdParcelas,valor,primary=false)=>{
			$('.js-valorTotal').html(number_format(qtdParcelas*valor,2,",","."));
			$('#botao-voltar-menu-parcelas').show()
			$('.js-pagamentos-quantidade').val(qtdParcelas)
			$('.js-tipo-politica').hide()
			$('.js-listar-parcelas').show()
			
			if(primary){
				pagamentosListar(3);
				if(contrato.pagamentos.length>0){
					$('.js-creditoBandeira').closest('dl').show();
					$('.js-parcelas').closest('dl').show();
					let valorPagamentos = 0
					pagamentos.forEach(x=>{
						valorPagamentos+=x.valor
						$('.js-listar-parcelas').find('.js-id_formadepagamento').each((k,select)=>{
							$(select).find('option').each(function(key,option){
								let dataTipo = $(option).attr('data-tipo')
								if(dataTipo==x.metodo){
									$(option).attr('selected',true)
									$(select).attr('disabled',true)
								}
							})
							$(select).closest('article').find('dl').each(function (ind,dls){
								let classe = $(dls).find('select,input').attr('class')
								if(typeof(classe)=='string'){
									classe = classe.replace(' js-tipoPagamento',"")
									if(x.metodo=='credito'){
										if(classe == 'js-creditoBandeira'){
											$(dls).show()
											let bandeirasAceitas = []
											$(dls).find('select optgroup option').each((key,option)=>{
												if($(option).attr('data-parcelas')>=x.parcelas){
													bandeirasAceitas.push($(option))
												}		
											})
											$(dls).find('select optgroup').html("")
											bandeirasAceitas.forEach(x=>{
												console.log(x)
												$(dls).find('select optgroup').append(`<option value="${x.value}" data-parcelas="${x['data-parcelas']}" data-parcelas-semjuros="${x['data-parcelas-semjuros']}" data-id_operadora="${x['data-id_operadora']}" data-id_operadorabandeira="${x['data-id_operadorabandeira']}" >${x.text()}</option>`)
											})
										}
									}else if(x.metodo=='debito'){
										if(classe == 'js-debitoBandeira'){
											$(dls).show()
										}
									}else {
										if(classe == 'js-creditoBandeira'){
											$(dls).hide()
										}
										if(classe == 'js-debitoBandeira'){
											$(dls).hide()
										}
									}
									if(classe == 'js-parcelas'){
											$(dls).show()
											$(dls).find('select').append(`
												<option value='${x.parcelas??1}' selected>${x.parcelas??1} x </option>
											`)
											$(dls).find('select').attr('disabled',true)
										}
									if(classe=='js-identificador'){
										$(dls).val(x.identificador??"")
										$(dls).show()
									}
								}
							})
						})
					})
				}
				return
			}
			let politicaEscolhida = temPolitica.parcelasParametros.metodos.find((item)=>{
				return item.tipo==metodo
			})
		
			$('#metodos-pagamento-politica .js-pagamentos').html("")
			let valorEntrada = valor
			let valorParcela = valor

			if(politicaEscolhida && politicaEscolhida.entradaMinima && politicaEscolhida.entradaMinima.length>0 && qtdParcelas>1){
				valorEntrada = ((parseFloat(politicaEscolhida.entradaMinima))/100)*(qtdParcelas*valor)
				valorParcela = ((qtdParcelas*valor)-valorEntrada)/(qtdParcelas-1)
				if(valorEntrada<valorParcela){
					valorEntrada = ((qtdParcelas*valor))/(qtdParcelas)
					valorParcela = ((qtdParcelas*valor))/(qtdParcelas)
				}
			}
			let parcelaAtual = 0

			let startDate = new Date();
			if($('.js-vencimento:eq(0)').val()!=undefined) {
				aux = $('.js-vencimento:eq(0)').val().split('/');
				startDate = new Date();//`${aux[2]}-${aux[1]}-${aux[0]}`);
				startDate.setDate(aux[0]);
				startDate.setMonth(eval(aux[1])-1);
				startDate.setFullYear(aux[2]);
			}

			pagamentosTextarea = JSON.parse($('#js-textarea-pagamentos').val());
			teste2 = [];
			let parcelas = []
			let item = {}
			let mes = startDate.getMonth()+1;
			let dia = startDate.getDate();
			mes = mes <= 9 ? `0${mes}`:mes;
			dia = dia <= 9 ? `0${dia}`:dia;
			item.valor = valor*qtdParcelas
			item.vencimento=`${dia}/${mes}/${startDate.getFullYear()}`;
			item.parcelas=qtdParcelas
			item.bandeira=""
			item.metodo=metodo

			parcelas.push(item)
			newDate = startDate;
			newDate.setMonth(newDate.getMonth()+1);
			startDate=newDate;

			$('#js-textarea-pagamentos').val(JSON.stringify(parcelas))
			pagamentos = parcelas;
			pagamentosListar(3);
			const selects = $('.js-listar-parcelas').find('.js-id_formadepagamento')
			selects.each(function (i,select){
				$(select).find('option').each(function(key,option){
					let dataTipo = $(option).attr('data-tipo')
					if(dataTipo==metodo){
						$(option).attr('selected',true)
						$(select).attr('disabled',true)
					}
				})
				$(select).closest('article').find('dl').each(function (ind,dls){
					let classe = $(dls).find('select').attr('class')
					if(typeof(classe)=='string'){
						classe = classe.replace(' js-tipoPagamento',"")
						if(metodo=='credito'){
							if(classe == 'js-creditoBandeira'){
								$(dls).show()
								let bandeirasAceitas = []
								$(dls).find('select optgroup option').each((key,option)=>{
									if($(option).attr('data-parcelas')>=qtdParcelas){
										bandeirasAceitas.push($(option))
									}		
								})
								$(dls).find('select optgroup').html("")
								bandeirasAceitas.forEach(x=>{
									console.log(x)
									$(dls).find('select optgroup').append(`<option value="${x.value}" data-parcelas="${x['data-parcelas']}" data-parcelas-semjuros="${x['data-parcelas-semjuros']}" data-id_operadora="${x['data-id_operadora']}" data-id_operadorabandeira="${x['data-id_operadorabandeira']}" >${x.text()}</option>`)
								})
							}
							if(classe == 'js-parcelas'){
								$(dls).show()
								$(dls).find('select').append(`
									<option value='${qtdParcelas}' selected>${qtdParcelas} x </option>
								`)
								$(dls).find('select').attr('disabled',true)
							}
						}else if(metodo=='debito'){
							if(classe == 'js-debitoBandeira'){
								$(dls).show()
							}
						}
					}
				})
			})
		}

		const voltarMenuParcelas = ()=>{
			$('#botao-voltar-menu-parcelas').hide()
			$('.js-tipo-politica').show()
			$('.js-listar-parcelas').hide()
			$('#js-textarea-pagamentos').text("")
			atualizaValor()
			$('.js-listar-parcelas').html("")
			$('.filter-title').find('strike').html("")
		}

		const verificaSeExisteParcelasSalvas = ()=>{
			if(contrato.tipo_financeiro =='politica'){
				$('.js-tipo-manual').hide()
				$('.js-tipo-politica').show()
				
				let qtdParcelas = contrato.pagamentos.length
				let valor = (valorTotalProcedimentos/qtdParcelas)
				if(pagamentos.length>0){
					valor = pagamentos.reduce((acc, obj) => acc + obj.valor, 0);
				}
				if(qtdParcelas>0){
					EscolheParcelas(contrato.pagamentos[0].metodo,qtdParcelas,valor,true)
				}
			}else if(contrato.tipo_financeiro =='manual'){
				$('.js-tipo-manual').show()
				$('.js-tipo-politica').hide()
				pagamentosListar();
			}else{
				$('.js-tipo-manual').hide()
				$('.js-tipo-politica').show()
			}
			$('.filter-title').find('strike').html("")
		}

		$(function(){
			$('.js-btn-salvar').click(function(){
				let erro = ``;

				if($('input[name=titulo]').val().length==0) {
					erro='Digite o título do <b>Tratamento</b>';
					$('input[name=titulo]').addClass('erro');
				} else if(procedimentos.length==0) {
					erro='Adicione pelo menos um procedimento para iniciar um Plano de Tratamento';
				}

				if(erro.length==0) {
					$('.js-pagamento-item').each(function(index,elem) {
						if($(elem).find('.js-vencimento').val().length==0) {
							$(elem).find('.js-vencimento').addClass('erro');
							erro='Defina a(s) <b>Data(s) de Vencimento</b> do(s) pagamento(s)';
						}
					})
				}
				if(erro.length>0) {
					swal({title:"Erro", text: erro, html:true, type:"error", confirmButtonColor: "#424242"});
				} else {
					$('.js-form-plano').submit();
				}
			});

			$('.js-btn-status').click(function(){
				let status = $(this).attr('data-status');
				if(status=="PENDENTE") {
					$('input[name=status]').val('PENDENTE');
				} else if(status=="APROVADO") {
					$('input[name=status]').val('APROVADO');

				} else if(status=="CANCELADO") {
					$('input[name=status]').val('CANCELADO');

				} else  {

					$('input[name=status]').val('');
				}

				$('form.js-form-plano').submit();
			});

			$('.js-btn-adicionarProcedimento').click(function(){
				$(".aside-plano-procedimento-adicionar").fadeIn(100,function() {
					$(".aside-plano-procedimento-adicionar .aside__inner1").addClass("active");
				});

				$('.aside-plano-procedimento-adicionar .js-asidePlano-id_procedimento').chosen('destroy');
				$('.aside-plano-procedimento-adicionar .js-asidePlano-id_procedimento').chosen();
				
			})
			// verifica se ha alteracao na primeira data de pagamento 
			$('.js-listar-parcelas').on('change','.js-vencimento:eq(0)',function(){
				let CamposDatas = $('.js-listar-parcelas').find('.js-vencimento');
				if(CamposDatas.length>1) {
					let numeroParcelas = CamposDatas.length
					let aux = $('.js-vencimento:eq(0)').val().split("/")
					var startDate = new Date();
					startDate.setDate(aux[0]);
					startDate.setMonth(eval(aux[1])-1);
					startDate.setFullYear(aux[2]);
					CamposDatas.each(function (index, input){
							let newDAte = startDate
							let mes = startDate.getMonth()+1;
							let dia = startDate.getDate();
							mes = mes <= 9 ? `0${mes}`:mes;
							dia = dia <= 9 ? `0${dia}`:dia;
							pagamentos[index].vencimento=`${dia}/${mes}/${startDate.getFullYear()}`;
							newDate = startDate;
							newDate.setMonth(newDate.getMonth()+1);
							console.log(newDAte)
							//$(this).val(newData)
					})
					pagamentosListar();
					return
				}
			});
			//verifica se ha alteracao no valor de cada parcela 
			$('.js-listar-parcelas').on('keyup','.js-valor',function(){
				let CamposValor = $('.js-listar-parcelas').find('.js-valor');
				let valorDigitado = unMoney($(this).val());
				let numeroParcelas = CamposValor.length;
				let dataOrdem = ($(this).attr('data-ordem')-1)
				let erro = "";
				if(valorDigitado>valorTotalProcedimentos){
					swal({title: "Erro!", text: 'Os valores das parcelas não podem superar o valor total', html:true, type:"error", confirmButtonColor: "#424242"});
					$(this).val(number_format(valorTotalProcedimentos/numeroParcelas,2,",","."))
					return;
				}
				let valor = 0
				let valorAteInput = valorDigitado
				let valorFinal =0
				let valorRestante = (valorTotalProcedimentos-valorDigitado)
				CamposValor.each(function (index, input){
					valorFinal += valorRestante-unMoney($(input).val())
					
					if(index+1<dataOrdem){
						valorRestante = valorRestante-unMoney($(input).val())
					}
					if(index+1>dataOrdem){
						
						$(input).val(number_format(valorRestante/((numeroParcelas-dataOrdem)),2,",","."))
					}
					if(index+1 == numeroParcelas){
						if(valorDigitado>valorRestante){
							swal({title: "Erro!", text: 'Os valores das parcelas não podem superar o valor total do procedimento', html:true, type:"error", confirmButtonColor: "#424242"});
							$(this).val(number_format(valorRestante,2,",","."))
							valorRestante = 0
							return
						}
					}
				});
			});
		});

	</script>

	<main class="main">
		<div class="main__content content">
			<section class="filter">
				<div class="filter-group">				
				</div>
				<div class="filter-group">
					<div class="filter-form form">
						<dl>
							<dd><a href="pg_pacientes_planosdetratamento.php?<?php echo $url;?>" class="button"><i class="iconify" data-icon="fluent:arrow-left-24-regular"></i></a></dd>
						</dl>
						<?php
						if(is_object($cnt)) {
						?>
						<dl>
							<dd>
								<?php
								if($cnt->status=="APROVADO") {
								?>
								<a href="javascript:;" class="button" style="opacity: 0.3;"><i class="iconify" data-icon="fluent:delete-24-regular"></i></a>
								<?php
								} else {
								?>
								<a href="pg_pacientes_planosdetratamento_form.php?<?php echo $url;?>edita=<?php echo $cnt->id;?>&deletaTratamento=<?php echo $cnt->id;?>" class="button js-confirmarDeletar" data-msg="Deseja realmente remover este plano de tratamento?"><i class="iconify" data-icon="fluent:delete-24-regular"></i></a>
								<?php
								}
								?>
							</dd>
						</dl>
						<dl>
							<dd><a href="impressao/planodetratamento.php?id=<?php echo md5($cnt->id);?>" class="button" target="_blank"><i class="iconify" data-icon="fluent:print-24-regular"></i></a></dd>
						</dl>
						<?php
						}
						if($tratamentoAprovado===false) {
						?>
						<dl>
							<dd><a href="javascript:;" class="button button_main js-btn-salvar"><i class="iconify" data-icon="fluent:checkmark-12-filled"></i><span>Salvar</span></a></dd>
						</dl>
						<?php
						}
						?>
					</div>
				</div>				
			</section>
			<form method="post" class="form js-form-plano">
				<input type="hidden" name="acao" value="wlib" />
				<input type="hidden" name="status" />
				<input type="hidden" name="id_politica" value='<?=$values['id_politica']??0;?>'/>
				<div class="grid grid_2">
					<!-- Identificacao -->
					<fieldset>
						<legend>Identificação</legend>
						<dl>
							<dd>
								<?php
								if(is_object($cnt)) {
								?>
								<div class="button-group">
									<a href="javascript:;" data-status="PENDENTE" class="button js-btn-status<?php echo $cnt->status=="PENDENTE"?" active":"";?>"><i class="iconify" data-icon="fluent:timer-24-regular"></i><span>Aguard. Aprovação</span></a>
									<a href="javascript:;" data-status="APROVADO" class="button js-btn-status<?php echo $cnt->status=="APROVADO"?" active":"";?>"><i class="iconify" data-icon="fluent:checkbox-checked-24-filled"></i><span>Aprovado</span></a>
									<a href="javascript:;" data-status="CANCELADO" class="button js-btn-status<?php echo $cnt->status=="CANCELADO"?" active":"";?>"><i class="iconify" data-icon="fluent:dismiss-square-24-regular"></i><span>Reprovado</span></a>
								</div>
								<?php
								} else {
								?>
								<div class="button-group tooltip" style="opacity:0.4" title="Salve o tratamento para poder alterar o status">
									<a href="javascript:;" class="button"><i class="iconify" data-icon="fluent:timer-24-regular"></i><span>Aguard. Aprovação</span></a>
									<a href="javascript:;" class="button"><i class="iconify" data-icon="fluent:checkbox-checked-24-filled"></i><span>Aprovado</span></a>
									<a href="javascript:;" class="button"><i class="iconify" data-icon="fluent:dismiss-square-24-regular"></i><span>Reprovado</span></a>
								</div>
								<?php
								}
								?>
							</dd>
						</dl>
						<div class="colunas">
							<dl>
								<dt>Título</dt>
								<dd><input type="text" name="titulo" value="<?php echo $values['titulo'];?>"<?php echo $tratamentoAprovado==true?" disabled":"";?> /></dd>
							</dl>
							<dl>
								<dt>Profissional</dt>
								<dd>
									<select name="id_profissional" class="js-id_profissional"<?php echo $tratamentoAprovado==true?" disabled":"";?>>
										<option value=""><?php echo utf8_encode($clinica->clinica_nome);?></option>
										<?php
										foreach($_profissionais as $x) {
											if($x->check_agendamento==0 or $x->contratacaoAtiva==0) continue;
											$iniciais=$x->calendario_iniciais;
											echo '<option value="'.$x->id.'" data-iniciais="'.$iniciais.'" data-iniciaisCor="'.$x->calendario_cor.'"'.($values['id_profissional']==$x->id?" selected":"").'>'.utf8_encode($x->nome).'</option>';
										}
										?>
									</select>
								</dd>
							</dl>					
						</div>

						<div class="colunas">
							<dl>
								<dt>Tempo Estimado</dt>
								<dd class="form-comp form-comp_pos"><input type="number" name="tempo_estimado" value="<?php echo $values['tempo_estimado'];?>"<?php echo $tratamentoAprovado==true?" disabled":"";?> /><span>dias</span></dd>
							</dl>
						</div>
					</fieldset>
					<!-- Financeiro -->
					<fieldset>
						<legend>Financeiro</legend>
						<textarea name="pagamentos" id="js-textarea-pagamentos" style="display:none;"><?php echo $values['pagamentos'];?></textarea>
						<section class="filter">
							<div class="filter-group">	
								<div class="filter-title">									
									<h1>Total: <strike  class="js-valorTotalOriginal">R$ 350,00</strike> <strong  class="js-valorTotal">R$ 340,00</strong></h1>
								</div>								
							</div>
							<div class="filter-group">
							<?php if($tratamentoAprovado===false):?>
								<div>
									<a href="javascript:;" class="button js-btn-desconto"><i class="iconify" data-icon="fluent:money-calculator-24-filled"></i><span>Descontos</span></a>
								</div>
								<dl id='botao-voltar-menu-parcelas' style='display: none;'>
									<dd>
										<label onclick="voltarMenuParcelas()"><i class="iconify" data-icon="fluent:arrow-left-24-regular"></i></label>
									</dd>
								</dl>
							<?php endif;?>
							</div>
						</section>
						<?php if($tratamentoAprovado===false):?>
						<dl style="margin-bottom:2rem">
							<dd>
								<label><input type="radio" name="tipo_financeiro" value="politica" onclick="$('.js-tipo').hide(); $('.js-tipo-politica').show();" <?= (is_object($cnt) and $cnt->tipo_financeiro=="politica")?" checked":"";?>/> Política de pagamento</label>
								<label><input type="radio" name="tipo_financeiro" value="manual" onclick="$('.js-tipo').hide(); $('.js-tipo-manual').show();atualizaValor(true);" <?= (is_object($cnt) and $cnt->tipo_financeiro=="manual")?" checked":"";?> /> Financeiro manual</label>
							</dd>
						</dl>
						<?php endif;?>
						<section class="js-tipo js-tipo-manual">
							<dl>
								<dt>Parcelas</dt>
								<dd>
									<label><input class="js-pagamentos-quantidade" type="number" name="parcelas" value="<?=isset($values['parcelas'])?$values['parcelas']:'1';?>" style="width:80px;" /></label>
								</dd>
							</dl>							
						</section>
						<section class="js-tipo js-tipo-politica" style="display:none;">
							<div class="list1">
								<table >
								</table>
							</div>
						</section>
						<section class="js-tipo js-listar-parcelas" style="display:none;">
							<div class="fpag" style="margin-top:1rem;">
								<div class="fpag-item">
									<aside>1</aside>
									<article>
										<div class="colunas3">
											<dl>
												<dd class="form-comp"><span><i class="iconify" data-icon="fluent:calendar-ltr-24-regular"></i></span><input type="tel" name="" class="data" value="07/09/2022" /></dd>
											</dl>
											<dl>
												<dd class="form-comp"><span>R$</i></span><input type="tel" name="" class="valor" value="" /></dd>
											</dl>
											<dl>
												<dd>
													<select class="js-id_formadepagamento js-tipoPagamento">
														<option value="9" data-tipo="boleto">BOLETO</option>
													</select>
												</dd>
											</dl>
										</div>
										<div class="colunas3">
											<dl>
												<dt>Identificador</dt>
												<dd><input type="text" name="" /></dd>
											</dl>
										</div>
									</article>
								</div>
								<div class="fpag-item">
									<aside>2</aside>
									<article>
										<div class="colunas3">
											<dl>
												<dd class="form-comp"><span><i class="iconify" data-icon="fluent:calendar-ltr-24-regular"></i></span><input type="tel" name="" class="data" value="07/09/2022" /></dd>
											</dl>
											<dl>
												<dd class="form-comp"><span>R$</i></span><input type="tel" name="" class="valor" value="" /></dd>
											</dl>
											<dl>
												<dd>
													<select class="js-id_formadepagamento js-tipoPagamento">
														<option value="9" data-tipo="boleto">CARTÃO DE CRÉDITO</option>
													</select>
												</dd>
											</dl>
										</div>
										<div class="colunas3">
											<dl>
												<dt>Bandeira</dt>
												<dd><select name=""><option value=""></option></select></dd>
											</dl>
											<dl>
												<dt>Parcelas</dt>
												<dd><select name=""><option value="">1x</option></select></dd>
											</dl>
											<dl>
												<dt>Identificador</dt>
												<dd><input type="text" name="" /></dd>
											</dl>
										</div>
									</article>
								</div>
							</div>
						</section>
					</fieldset>
					<!-- Procedimentos --> 
					<fieldset>
						<legend>Procedimentos</legend>
						<textarea name="procedimentos" id="js-textarea-procedimentos" style="display:none"><?php echo $values['procedimentos'];?></textarea>
						<?php
						if($tratamentoAprovado===false) {
						?>
						<dl>
							<dd>
								<a href="javascript:;" ata-aside="plano-procedimento-adicionar" class="button button_main js-btn-adicionarProcedimento"><i class="iconify" data-icon="fluent:add-circle-24-regular"></i><span>Adicionar Procedimento</span></a>
							</dd>
						</dl>
						<?php
						}
						?>
						
						<div class="list1">
							<table id="js-table-procedimentos">
								
							</table>
						</div>
					</fieldset>
				</div>
			</form>
		</div>
	</main>

<?php 
	require_once("includes/api/apiAsidePlanoDeTratamento.php");
	include "includes/footer.php";
?>	