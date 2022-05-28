<?php
	if(isset($_POST['ajax'])) {
		require_once("lib/conf.php");	
		require_once("usuarios/checa.php");

		$sql = new Mysql();

		$rtn = array();
		if($_POST['ajax']=="planos") {
			$planos=array();
			if(isset($_POST['id_procedimento']) and is_numeric($_POST['id_procedimento'])) {
				$sql->consult($_p."parametros_procedimentos","*","where id='".addslashes($_POST['id_procedimento'])."' and lixo=0");
				if($sql->rows) {
					$procedimento=mysqli_fetch_object($sql->mysqry);
				}
			}

			if(is_object($procedimento)) {
				$sql->consult($_p."parametros_procedimentos_planos","*","where id_procedimento=$procedimento->id"); 
				
				$planosID=array();
				$procedimentoPlano=array();
				while($x=mysqli_fetch_object($sql->mysqry)) {
					$procedimentoPlano[$x->id_plano]=$x;
					$planosID[]=$x->id_plano;
				}	


				if(count($planosID)) {
					$sql->consult($_p."parametros_planos","*","where id IN (".implode(",",$planosID).")");
					if($sql->rows) {
						while($x=mysqli_fetch_object($sql->mysqry)) {
							if(isset($procedimentoPlano[$x->id])) {
								$procP=$procedimentoPlano[$x->id];
								$planos[]=array('id'=>$x->id,'titulo'=>utf8_encode($x->titulo),'valor'=>$procP->valor);
							}
						}
					}
				}

				$rtn=array('success'=>true,'planos'=>$planos);
			} else {
				$rtn=array('success'=>false,'error'=>'Procedimento/Unidade não definida(s)!');
			}
		} else if($_POST['ajax']=="persistirDesconto") {

			$tratamento='';
			if(isset($_POST['id_tratamento']) and is_numeric($_POST['id_tratamento'])) {
				$sql->consult($_p."pacientes_tratamentos","*","where id='".addslashes($_POST['id_tratamento'])."' and lixo=0");
				if($sql->rows) {
					$tratamento=mysqli_fetch_object($sql->mysqry);
				}
			}

			if(is_object($tratamento)) {
				$vsql="procedimentos='".addslashes(utf8_decode($_POST['procedimentos']))."',
						pagamentos='".addslashes(utf8_decode($_POST['pagamentos']))."'";
				$vwhere="where id=$tratamento->id";

				$sql->update($_p."pacientes_tratamentos",$vsql,$vwhere);
				
				$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='update',vsql='".addslashes($vsql)."',vwhere='".addslashes($vwhere)."',tabela='".$_p."pacientes_tratamentos',id_reg='".$tratamento->id."'");


				$rtn=array('success'=>true);
			} else {
				$rtn=array('success'=>false,'error'=>'Tratamento não encontrado');
			}
		}

		header("Content-type: application/json");
		echo json_encode($rtn);
		die();
	}

	if(isset($_GET['ajax'])) {
		if($_GET['ajax']=="profissao") {
			if(isset($_GET['id_profissao']) and is_numeric($_GET['id_profissao'])) {
				$_GET['edita']=$_GET['id_profissao'];
				$_GET['form']=1;
			}
			require_once("pg_parametros_profissoes.php");

		}

		die();
	}

	include "includes/header.php";
	include "includes/nav.php";


	$_table=$_p."pacientes_tratamentos";
	$_page=basename($_SERVER['PHP_SELF']);

	
	$_formasDePagamento=array();
	$optionFormasDePagamento='';
	$sql->consult($_p."parametros_formasdepagamento","*","order by titulo asc");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_formasDePagamento[$x->id]=$x;
		$optionFormasDePagamento.='<option value="'.$x->id.'" data-tipo="'.$x->tipo.'">'.utf8_encode($x->titulo).'</option>';
	}

	$_bandeiras=array();
	$sql->consult($_p."parametros_cartoes_bandeiras","*","where lixo=0");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_bandeiras[$x->id]=$x;
	}

	$_regioesOpcoes=array();
	$sql->consult($_p."parametros_procedimentos_regioes_opcoes","*","order by titulo asc");
	while($x=mysqli_fetch_object($sql->mysqry)) $_regioesOpcoes[$x->id_regiao][]=$x;

	$_regioes=array();
	$sql->consult($_p."parametros_procedimentos_regioes","*","");
	while($x=mysqli_fetch_object($sql->mysqry)) $_regioes[$x->id]=$x;

	$_procedimentos=array();
	$sql->consult($_p."parametros_procedimentos","*","where lixo=0");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_procedimentos[$x->id]=$x;
	}

	$_planos=array();
	$sql->consult($_p."parametros_planos","*","where lixo=0");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_planos[$x->id]=$x;
	}

	$_profissionais=array();
	$sql->consult($_p."colaboradores","id,nome,calendario_iniciais,foto,calendario_cor","where tipo_cro<>'' and lixo=0 order by nome asc");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_profissionais[$x->id]=$x;
	}

	$paciente='';
	if(isset($_GET['id_paciente']) and is_numeric($_GET['id_paciente'])) {
		$sql->consult($_p."pacientes","*","where id='".$_GET['id_paciente']."'");
		if($sql->rows) {
			$paciente=mysqli_fetch_object($sql->mysqry);
			$cnt=$paciente;
		}
	}

	if(isset($_GET['deletaTratamento']) and is_numeric($_GET['deletaTratamento'])) {
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
				$jsc->jAlert("Tratamento excluído com sucesso!","sucesso","document.location.href='pg_contatos_pacientes_tratamento.php?id_paciente=".$paciente->id."'");
			}
		}

	}
	$_selectSituacaoOptions=array(//'aguardandoAprovacao'=>array('titulo'=>'AGUARDANDO APROVAÇÃO','cor'=>'blue'),
											'aprovado'=>array('titulo'=>'APROVADO','cor'=>'green'),
											'naoAprovado'=>array('titulo'=>'REPROVADO','cor'=>'red'),
										//	'observado'=>array('titulo'=>'NOTADO','cor'=>'orange'),
											//'cancelado'=>array('titulo'=>'CANCELADO');
										);

	$selectSituacaoOptions='<select class="js-situacao">';
	foreach($_selectSituacaoOptions as $key=>$value) {
		$selectSituacaoOptions.='<option value="'.$key.'">'.$value['titulo'].'</option>';
	}
	$selectSituacaoOptions.='</select>';

	$selectProfissional='<select class="js-profissional"><option value="">-</option>';
	foreach($_profissionais as $p) {
	
											
		$selectProfissional.='<option value="'.$p->id.'" data-iniciais="'.$p->calendario_iniciais.'" data-iniciaisCor="'.$p->calendario_cor.'">'.utf8_encode($p->nome).'</option>';
	}
	$selectProfissional.='</select>';

	$planosDosProcedimentos=array();
	$sql->consult($_p."parametros_procedimentos_planos","*","where lixo=0");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$planosDosProcedimentos[$x->id_procedimento][]=array("id"=>$x->id_plano,"titulo"=>utf8_encode($_planos[$x->id_plano]->titulo));
	}

	$campos=explode(",","titulo,id_profissional");
	foreach($campos as $v) $values[$v]='';

	if(is_object($paciente)) {
	
		$values=$adm->values($campos,$cnt);
		$values['data']=date('d/m/Y H:i',strtotime($cnt->data));
		
	} else {
		$jsc->jAlert("Paciente não encontrado!","erro","document.location.href='pg_contatos_pacientes.php'");
		die();
	}

	?>

	<section class="content">
		
		<?php
		require_once("includes/abaPaciente.php");
		?>
		
		<?php
		/*
		if(!isset($_GET['form'])) {
		?>
		<div class="filtros">
			<h1 class="filtros__titulo">Tratamento</h1>
			<div class="filtros-acoes">
				<a href="<?php echo $_page."?form=1&$url";?>" data-padding="0" class="principal tooltip" title="Adicionar"><i class="iconify" data-icon="bx-bx-plus"></i></a>
			</div>
		</div>
		<?php
		}
		*/
		?>
			
		<?php
		if(isset($_GET['form'])) {

			$campos=explode(",","titulo,id_profissional");
			
			foreach($campos as $v) $values[$v]='';
			$values['procedimentos']="[]";
			$values['pagamentos']="[]";

			$sql->consult($_table,"id","where id_paciente=$paciente->id");
			$values['titulo']="Plano de tratamento ".($sql->rows+1);

			$cnt='';
			if(isset($_GET['edita']) and is_numeric($_GET['edita'])) {
				$sql->consult($_table,"*","where id='".$_GET['edita']."' and lixo=0");
				if($sql->rows) {
					$cnt=mysqli_fetch_object($sql->mysqry);
					$values=$adm->values($campos,$cnt);

					// Procedimentos
						$procedimentos=array();
						$where="where id_tratamento=$cnt->id and id_paciente=$paciente->id and lixo=0";
						$sql->consult($_table."_procedimentos","*",$where);
						while($x=mysqli_fetch_object($sql->mysqry)) {

							/*$profissional=isset($_profissionais[$x->id_profissional])?$_profissionais[$x->id_profissional]:'';
							$iniciaisCor='';
							$iniciais='?';
							if(is_object($profissional)) {
								$iniciais=$profissional->calendario_iniciais;

								$iniciaisCor=$profissional->calendario_cor;
							}*/

							$valor=$x->valorSemDesconto;
							//if($x->quantitativo==1) $valor*=$x->quantidade;

							$procedimentos[]=array('id'=>$x->id,
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
													'valorCorrigido'=>(float)($x->quantitativo==1?$x->quantidade*$x->valor:$x->valor)-$x->desconto,
													'valor'=>(float)$valor,
													'desconto'=>(float)$x->desconto,
													'obs'=>utf8_encode($x->obs),
													'situacao'=>$x->situacao);
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

				} else {
					$jsc->jAlert("Plano de Tratamento não encontrado!","erro","document.location.href='$_page?$url'");
					die();
				}
			}

			$tratamentoAprovado=(is_object($cnt) and $cnt->status=="APROVADO")?true:false;
			
			$creditoBandeiras=array();
			$debitoBandeiras=array();

			

			$sql->consult($_p."parametros_cartoes_operadoras","*","where lixo=0 order by titulo");
			while($x=mysqli_fetch_object($sql->mysqry)) {
				$creditoBandeiras[$x->id]=array('titulo'=>utf8_encode($x->titulo),'bandeiras'=>array());
				$debitoBandeiras[$x->id]=array('titulo'=>utf8_encode($x->titulo),'bandeiras'=>array());
			}

			$_semJuros=array();
			$sql->consult($_p."parametros_cartoes_taxas_semjuros","*","where lixo=0");
			while($x=mysqli_fetch_object($sql->mysqry)) {
				$_semJuros[$x->id_operadora][$x->id_bandeira]=$x->semjuros;
			}

			/*$_parcelas=array();
			$sql->consult($_p."parametros_cartoes_taxas","*","where operacao='credito' and lixo=0 order by parcela asc");
			while($x=mysqli_fetch_object($sql->mysqry)) {
				$_parcelas[$x->id_operadora][$x->id_bandeira]=$x->parcela;
			}*/

			$sql->consult($_p."parametros_cartoes_taxas","*","where lixo=0");
			$_taxasCredito=$_taxasCreditoSemJuros=array();
			while($x=mysqli_fetch_object($sql->mysqry)) {

				if(isset($_bandeiras[$x->id_bandeira])) {
					$bandeira=$_bandeiras[$x->id_bandeira];

					if($x->operacao=="credito") {
						if(isset($creditoBandeiras[$x->id_operadora])) {

							//$parcelas=isset($_parcelas[$x->id_operadora][$x->id_bandeira])?$_parcelas[$x->id_operadora][$x->id_bandeira]:0;

							//$semJuros=0;
							//if(isset($_semJuros[$x->id_operadora][$bandeira->id])) $semJuros=$_semJuros[$x->id_operadora][$bandeira->id];

							$semJurosTexto="";
							if($bandeira->parcelasAte>0) {
								$semJurosTexto.=" - em ate ".$bandeira->parcelasAte."x";
								//if($semJuros>0) {
								//	$semJurosTexto.=", sendo ate ".$semJuros."x sem juros";
								//} 
							}
							if(!isset($_taxasCredito[$x->id_operadora][$bandeira->id][$x->parcela])) {
								$_taxasCredito[$x->id_operadora][$bandeira->id][$x->parcela]=$x->taxa;
							}
							///$_taxasCreditoSemJuros[$x->id_operadora][$bandeira->id][$x->vezes][$x->parcela]=($semJuros>0 && $semJuros<$x->parcela)?1:0;



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
			}

			if(isset($_POST['acao'])) {

				$processa=true;
				if(empty($cnt)) {
					$sql->consult($_p."pacientes_tratamentos","*","where id_paciente=$paciente->id and titulo='".addslashes($_POST['titulo'])."' and lixo=0");
					if($sql->rows) {
						$x=mysqli_fetch_object($sql->mysqry);	
						//$processa=false;
						//$jsc->go("?form=1&id_paciente=$paciente->id&edita=$x->id");
					//	die();
					}
				}


				
				if($processa===true) {	


					// persiste as informacoes do tratamento
					if($_POST['acao']=="salvar") {
						$vSQL=$adm->vSQL($campos,$_POST);
						$values=$adm->values;

						if($tratamentoAprovado===false) {
							$vSQL.="procedimentos='".addslashes(utf8_decode($_POST['procedimentos']))."',";
							$vSQL.="pagamentos='".addslashes(utf8_decode($_POST['pagamentos']))."',";
						}
						//echo $vSQL;die();

						$idProfissional=(isset($_POST['id_profissional']) and is_numeric($_POST['id_profissional']))?$_POST['id_profissional']:0;

						if(isset($_POST['parcelas']) and is_numeric($_POST['parcelas'])) $vSQL.="parcelas='".$_POST['parcelas']."',";
						if(isset($_POST['pagamento'])) $vSQL.="pagamento='".$_POST['pagamento']."',";
					
						if(is_object($cnt)) {
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
															$valorProcedimento+=$x->valorCorrigido;
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

												// se for credito
												if(isset($x->id_formapagamento)) {
													if($x->id_formapagamento==2 and isset($x->creditoBandeira) and isset($x->operadora) and isset($x->qtdParcelas)) {
														$where="where id_bandeira='".$x->creditoBandeira."' and id_operadora='".$x->operadora."' and vezes='".$x->qtdParcelas."' and operacao='credito' and lixo=0";
														$sql->consult($_p."parametros_cartoes_taxas","parcela,taxa,prazo",$where);
														
														if($sql->rows) {
															while($t=mysqli_fetch_object($sql->mysqry)) {
																$taxasPrazos[$t->parcela]=$t;
															}
														}
													}
													// se for debito
													else if($x->id_formapagamento==3 and isset($x->debitoBandeira) and isset($x->operadora)) {
														$where="where id_bandeira='".$x->debitoBandeira."' and id_operadora='".$x->operadora."' and operacao='debito' and lixo=0";
														$sql->consult($_p."parametros_cartoes_taxas","parcela,taxa,prazo",$where);
														
														if($sql->rows) {
															while($t=mysqli_fetch_object($sql->mysqry)) {
																$taxasPrazos=$t;
															}
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
															$id_operadora=$x->operadora;
															

															if(isset($x->qtdParcelas) and is_numeric($x->qtdParcelas)) {
																$valorParcela=$x->valor/$x->qtdParcelas;
																for($i=1;$i<=$x->qtdParcelas;$i++) {

																	$prazo=$taxa=0;
																	if(isset($taxasPrazos[$i])) {
																		$prazo=$taxasPrazos[$i]->prazo;
																		$taxa=$taxasPrazos[$i]->taxa;
																	}

																	$dtVencimento=date('Y-m-d',strtotime(invDate($x->vencimento)." + $prazo days"));


																	$vSQLBaixa[]=array("id_pagamento"=>$id_tratamento_pagamento,
																						"data_vencimento"=>$dtVencimento,
																						"valor"=>$valorParcela,
																						"id_formadepagamento"=>$f->id,
																						"parcela"=>$i,
																						"taxa"=>$taxa,
																						"parcelas"=>$x->qtdParcelas,
																						"id_bandeira"=>$id_bandeira,
																						"id_operadora"=>$id_operadora,
																						"tipo"=>"credito");
																}
															}
														}
													} else if($f->tipo=="debito") {
														if(isset($x->debitoBandeira) and is_numeric($x->debitoBandeira) and isset($_bandeiras[$x->debitoBandeira])) {

															$b = $_bandeiras[$x->debitoBandeira];

															$id_bandeira=$b->id;
															$id_operadora=$x->operadora;

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
																	opcao='".addslashes(utf8_decode($x->opcao))."',";
																	//id_profissional='".addslashes($x->id_profissional)."',
												//echo $vSQLProcedimento."<BR>";die();
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
						$adm->biCategorizacao();
						$jsc->jAlert("Informações salvas com sucesso!","sucesso","document.location.href='".$_page."?form=1&edita=$id_tratamento&id_paciente=$paciente->id'");
						die();
					}
				}

			}
		?>	
			<section id="cal-popup" class="cal-popup cal-popup_paciente cal-popup_top cal-popup_alt" style="left:703px; top:338px; margin-left:303px;display: none">
				<a href="javascript:;" class="cal-popup__fechar js-btn-fechar"><i class="iconify" data-icon="mdi-close"></i></a>
				<section class="paciente-info">
					<header class="paciente-info-header">
						<section class="paciente-info-header__inner1">
							<h1 class="js-titulo">Procedimento</h1>
							<p style="color:var(--cinza4);"><span style="color:var(--cinza4);" class="js-opcaoEQtd"></span> - <span class="js-plano">Plano SD</span> </p>
							
						</section>
					</header>
					<input type="hidden" class="js-index" />

					<div class="abasPopover">
						<a href="javascript:;" onclick="$(this).parent().parent().find('a').removeClass('active');$(this).parent().parent().find('.js-grid').hide();$(this).parent().parent().find('.js-grid-info').show();$(this).addClass('active');" class="active">Informações</a>
						<?php /*<a href="javascript:;" onclick="$(this).parent().parent().find('a').removeClass('active');$(this).parent().parent().find('.js-grid').hide();$(this).parent().parent().find('.js-grid-valor').show();$(this).addClass('active');">Valor</a>*/?>
						<a href="javascript:;" onclick="$(this).parent().parent().find('a').removeClass('active');$(this).parent().parent().find('.js-grid').hide();$(this).parent().parent().find('.js-grid-obs').show();$(this).addClass('active');">Observações</a>
					</div>

					<div class="paciente-info-grid js-grid js-grid-info" style="font-size: 12px;">		
						
						<?php /*<dl style="grid-column:span 2;">
							<dt>Profissional</dt>
							<dd><?php echo $selectProfissional;?></dd>
						</dl>*/?>

						<dl>

							<dt>Valor Tabela</dt>
								<dd><input type="text" class="js-valorTabela money" style="background: #ccc" disabled /></dd>
							</dl>
							<dl>
								<dt>Valor Desconto</dt>
								<dd>
									<input type="text" class="js-valorDeDesconto money" style="background: #ccc" disabled />
								</dd>
								<?php /*<dd style="margin-top:5px">
									<label><input type="checkbox" class="js-check-todos input-switch" /> Aplicar em Todos</label>
								</dd>*/?>
							</dl>
							<dl>
								<dt>Valor Corrigido</dt>
								<dd><input type="text" class='js-valorCorrigido money' style="background: #ccc" disabled /></dd>
							</dl>
							<dl>
								<dt>Valor Unitário</dt>
								<dd><input type="text" class="js-valorUnit money" style="background: #ccc" disabled /></dd>
						
							</dl>
							<?php /*<dl>								
								<dd style="padding-top: 10px;opacity:0.2"><button type="button" class="js-btn-descontoAplicarEmTodos button">Aplicar Desconto</button></dd>
							</dl>*/?>


						<dl style="grid-column:span ;">
							<dd><span class="iconify" data-icon="bx:bx-user-circle" data-inline="true"></span> <span class="js-autor"></span></dd>
						</dl>
						<dl style="grid-column:span ;">
							<dd><span class="iconify" data-icon="bi:clock" data-inline="true"></span> <span class="js-autor-data"></span></dd>
						</dl>
					</div>
					<script type="text/javascript">
						$(function(){
							$('input.money').maskMoney({symbol:'', allowZero:true, showSymbol:true, thousands:'.', decimal:',', symbolStay: true});
							
						});
					</script>

					<div class="paciente-info-grid js-grid js-grid-obs" style="display:none;font-size:12px;color:#666">	
						<dl style="grid-column:span 2;">
							<dd>
								<textarea style="height:100px" class="js-obs"></textarea>
							</dd>
						</dl>
					</div>
					<div class="paciente-info-opcoes">
						<?php echo $selectSituacaoOptions;?>
						<a href="javascript:;" class="js-btn-excluir button button__sec">excluir</a>
					</div>
				</section>
    		</section>

			<script type="text/javascript">

				var _taxasCredito = JSON.parse('<?php echo json_encode($_taxasCredito);?>');
				var _taxasCreditoSemJuros = JSON.parse('<?php echo json_encode($_taxasCreditoSemJuros);?>');
				var tratamentoAprovado = <?php echo ($tratamentoAprovado===true)?1:0;?>;
				var procedimentos = [];
				var id_tratamento = <?php echo is_object($cnt)?$cnt->id:0;?>;
				var planosDosProcedimentos = JSON.parse(`<?php echo json_encode($planosDosProcedimentos);;?>`);
				var pagamentos = JSON.parse(`<?php echo ($values['pagamentos']);;?>`);
				var valorTotal = 0;
				var valorPagamento = 0;
				var valorSaldo = 0;
				var autor = `<?php echo utf8_encode($usr->nome);?>`;
				var id_usuario = `<?php echo utf8_encode($usr->id);?>`;
				var parcelaProv = '';

				const desativarCampos = () => {
					if(tratamentoAprovado===1) { 
						$('.js-pagamento-item').find('select:not(.js-profissional),input').prop('disabled',true);
						$('#cal-popup').find('select:not(.js-profissional),input').prop('disabled',true);
						$('#cal-popup').find('.js-btn-excluir,.js-btn-descontoAplicarEmTodos').hide();
					}
				}

				// ATUALIZACAO DE VALORES
					/*const atualizaValor = () => {
						valorTotal=0;
						$(`.js-procedimentos .js-valor`).each(function(index,el){
							let val = unMoney($(el).html());
							let situacao = $(el).parent().parent().find('.js-situacao').val();
							if(situacao=='aguardandoAprovacao' || situacao=='aprovado') valorTotal+=val;
						});

						let reprovarAtivo=true;
						$(`.js-table-procedimentos .js-situacao`).each(function(index,el){
							let situacao = $(el).val();
							if(situacao=='aprovado') {
								reprovarAtivo=false;
							}
						});

						if(reprovarAtivo===true) {
							$('.js-btn-reprovar').show();
						} else {
							$('.js-btn-reprovar').hide();

						}

						valorPagamento=0;
						pagamentos.forEach(x=>{
							valorPagamento+=x.valor;
						});

						valorSaldo=valorPagamento-valorTotal;
						$('.js-valorTotal').html(`R$ ${number_format(valorTotal,2,",",".")}`);
						$('.js-valorPagamento').html(`R$ ${number_format(valorPagamento,2,",",".")}`);
						$('.js-valorSaldo').html(`R$ ${number_format(valorSaldo,2,",",".")}`);

						if(valorSaldo<0) {
							$('.js-btn-aprovar').css('opacity',0.2);
						}  else {

							$('.js-btn-aprovar').css('opacity',1);
						}
					}*/
				
				// ATUALIZACAO DE VALORES
					const atualizaValor = (atualizacao,pelaQtd) => { 

						valorTotal=0;

						let reprovarAtivo=true;
						procedimentos.forEach(x=> {
							if(x.valorCorrigido!==undefined || x.valor!==undefined) {
								if(x.situacao!='naoAprovado' && x.situacao!='observado') {


									if(x.desconto>0) {
										valorTotal+=$.isNumeric(x.valorCorrigido)?eval(x.valorCorrigido):unMoney(x.valorCorrigido);

										//if(eval(x.quantitativo)==1) valorTotal*=x.quantidade;
									}
									else {
										if(eval(x.quantitativo)==1) {

											valorTotal+=$.isNumeric(x.valor)?eval(x.valor*x.quantidade):unMoney(x.valor*x.quantidade);
										} else {
											valorTotal+=$.isNumeric(x.valor)?eval(x.valor):unMoney(x.valor);

										}
									}
								}
								if(x.situacao=='aprovado') {
									reprovarAtivo=false;
								}
							}
						})
						


						if(reprovarAtivo===true) {
							$('.js-btn-reprovar').show();
						} else {
							$('.js-btn-reprovar').hide();
						}


						let parcelas = [];


						if($('input[name=pagamento]:checked').length>0) {
							if($('input[name=pagamento]:checked').val()=="avista") {
								$('.js-pagamentos-quantidade').hide();

								let item = {};
								item.vencimento='<?php echo date('d/m/Y');?>';
								item.valor=valorTotal;

								parcelas.push(item);

								if(pagamentos.length==1) {

								} else {
									pagamentos=parcelas;
								}

								$('.js-pagamentos-quantidade').val(1);

								/*console.log(x);
									$('.js-pagamentos').append(pagamentosHTML);
									$('.js-pagamento-item .js-vencimento:last').val(x.vencimento);
									$('.js-pagamento-item .js-valor:last').val(number_format(x.valor,2,",","."));
									$('.js-pagamento-item .js-vencimento:last').inputmask('99/99/9999');
									$('.js-pagamento-item .js-vencimento:last').datetimepicker({timepicker:false,
																							format:'d/m/Y',
																							scrollMonth:false,
																							scrollTime:false,*/
							} else {
								$('.js-pagamentos-quantidade').show();

								let numeroParcelas = $('.js-pagamentos-quantidade').val();
								//alert(numeroParcelas)
								if(numeroParcelas.length==0 || numeroParcelas<=0) numeroParcelas=2;
								
								valorParcela=valorTotal/numeroParcelas;

								let startDate = new Date();
								for(var i=1;i<=numeroParcelas;i++) {
									/*val = -1;
									if($(`.js-pagamentos .js-valor:eq(${i})`).length) {
										val = $(`.js-pagamentos .js-valor:eq(${(i-1)})`).val();
									}
									//console.log(`${$(`.js-pagamentos .js-valor:eq(${i})`).length} -> .js-pagamentos .js-valor:eq(${(i-1)}) => ${val}`);*/

									let item = {};
									let mes = startDate.getMonth()+1;
									mes = mes <= 9 ? `0${mes}`:mes;

									let dia = startDate.getDate();
									dia = dia <= 9 ? `0${dia}`:dia;
									item.vencimento=`${dia}/${mes}/${startDate.getFullYear()}`;
									item.valor=valorParcela;
									parcelas.push(item);

									newDate = startDate;
									newDate.setMonth(newDate.getMonth()+1);

									startDate=newDate;
								}
							}

							

							let totalAtual = ($('.js-valorTotal').html());
							if(totalAtual.length>0) totalAtual=unMoney(totalAtual);


							if(totalAtual!=0) {
								if(totalAtual!=valorTotal) {
									
									//$.notify('Os valores foram alterados.<br />Por favor redefina as formas de pagamento');
									swal({title: "Atenção", text: 'Os valores foram alterados.<br />Por favor redefina as formas de pagamento', html:true, type:"warning", confirmButtonColor: "#424242"});
									atualizacao=true;
									
								}
								else atualizacao=false;
							}

							if(atualizacao===true || pelaQtd===true) {
								pagamentos=parcelas;
							}

							//console.log(`estava ${totalAtual} e vai ficar ${valorTotal}`)
						}
						
						pagamentosListar();

						
						if(id_tratamento>0) {
								let data = `ajax=persistirDesconto&id_tratamento=${id_tratamento}&procedimentos=${JSON.stringify(procedimentos)}&pagamentos=${JSON.stringify(pagamentos)}`;
							
								$.ajax({
									type:"POST",
									data:data,
									success:function(rtn) {

									}
								})
							}

						$('.js-valorTotal').val(number_format(valorTotal,2,",","."));

					}

				// PROCEDIMENTOS
				
					/*var procedimentosHMTL = `<a href="javascript:;" class="reg-group js-procedimento-item">

												<div class="reg-data js-descricao">
													<h1 class="js-procedimento"></h1>
													<p class="js-regiao"></p>
												</div>

												<div class="reg-steps js-steps" style="margin:0 auto;">

												
													
												</div>						

												<div class="js-profissional">
													
												</div>
												

												
											</a>`;*/

					var procedimentosHMTL = `<a href="javascript:;" class="reg-group js-procedimento-item">

												<div class="reg-data js-descricao">
													<h1 class="js-procedimento"></h1>
													<p class="js-plano"></p>
												</div>

												
												<div class="js-regiao"></div>						

												<div class="js-valor"></div>	
												

												
											</a>`;
									


					const procedimentosListar = (atualizacao) => {
						$('.js-procedimentos .js-procedimento-item').remove();
						if(procedimentos.length>0) {
							$('.js-btn-aprovarTodosProcedimentos').show();
							let index=0;
							let total = 0;

							

							procedimentos.forEach(x=> {
								popViewInfos[index] = x;

								

								$(`.js-procedimentos`).append(procedimentosHMTL);
								$(`.js-procedimentos .js-procedimento-item:last`).attr('data-situacao',x.situacao);
								//$(`.js-procedimentos .js-procedimento-item:last`).css('border-left',`solid 10px ${corSituacao}`)
								$(`.js-procedimentos .js-procedimento-item:last`).click(function(){popView(this);})
								$(`.js-procedimentos .js-procedimento:last`).html(x.procedimento);



								let opcaoShow = x.opcao;
								if(x.quantitativo==0 && x.opcao.length==2) {
									opcaoShow=`<span class="iconify" data-icon="la:tooth" data-height="13" style="color:var(--cor1)" data-inline="true"></span> ${x.opcao}`;
								}
								$(`.js-procedimentos .js-regiao:last`).html(x.quantitativo==1?`Qtd.: ${x.quantidade}`:opcaoShow);
								if(x.opcao.length==0) {
									$(`.js-procedimentos .js-plano:last`).append(`${x.plano}`);
								} else {
									$(`.js-procedimentos .js-plano:last`).append(`${x.plano}`);
								}
								if(x.situacao!="observado" && x.situacao!="naoAprovado") {
									if(x.desconto) {
										$(`.js-procedimentos .js-valor:last`).append(`<span class="iconify" data-icon="dashicons:money-alt" data-width="15" style="color:var(--cor1)" data-inline="true"></span> <strike>${number_format((eval(x.quantitativo)==1?x.quantidade*x.valor:x.valor),2,",",".")}</strike><br /> ${number_format(x.valorCorrigido,2,",",".")}`);
									} else {
										$(`.js-procedimentos .js-valor:last`).append(`<span class="iconify" data-icon="dashicons:money-alt" data-width="15" style="color:var(--cor1)" data-inline="true"></span><br /> ${number_format(x.valorCorrigido?x.valorCorrigido:x.valor,2,",",".")}`);
									}
								} else {
									$(`.js-procedimentos .js-regiao:last`).append('');
								}



								if(x.situacao=="aprovado") {
									$(`.js-procedimentos .js-procedimento-item:last`).css('opacity',1);
								} else if(x.situacao=="naoAprovado") {
									$(`.js-procedimentos .js-procedimento-item:last`).css('opacity',0.3);
								}

								//$(`.js-procedimentos .js-profissional:last`).html(iniciais);
								//console.log(x);
								index++;
								total+=x.valorCorrigido?(x.valorCorrigido):(x.valor);
							});
							
							


							atualizaValor(atualizacao,false);
							desativarCampos();
						} else {

							$('.js-btn-aprovarTodosProcedimentos').hide();
						}
						//console.log(procedimentos);
						$('textarea.js-json-procedimentos').val(JSON.stringify(procedimentos));
						
					}

					const procedimentosRemover = (index) => {
						procedimentos.splice(index,1);
						procedimentosListar(true);
					}

					const validarTratamento = () => {
						let erro = ``;

						if($('input[name=titulo]').val().length==0) {
							erro='Digite o título do <b>Tratamento</b>';
							$('input[name=titulo]').addClass('erro');
						}

						if(erro.length==0) {
							$('.js-procedimento-item').each(function(index,elem){
								//console.log(`Profissional: ${$(elem).find('select.js-profissional').val()}`);
								//console.log(`Plano: ${$(elem).find('select.js-plano').val()}`);
								/*if($(elem).find('select.js-profissional').val()==null || $(elem).find('select.js-profissional').val().length==0) {
									$(elem).find('select.js-profissional').addClass('erro');
									erro='Selecione o(s) <b>Profissional(s)</b>!';
								} else if($(elem).find('select.js-plano').val()==null || $(elem).find('select.js-plano').val().length==0) {
									$(elem).find('select.js-plano').addClass('erro');
									erro='Selecione o(s) <b>Plano(s)</b>!';
								}*/
							});
						}

						if(erro.length==0) {
							$('.js-pagamento-item').each(function(index,elem) {
								if($(elem).find('.js-vencimento').val().length==0) {
									$(elem).find('.js-vencimento').addClass('erro');
									erro='Defina as <b>Data(s) de Vencimento!</b>';
								}
							})
						}

						return erro;
					}
					var popViewInfos = [];

					const popView = (obj) => {


						index=$(obj).index();


						$('#cal-popup')
								.removeClass('cal-popup_left')
								.removeClass('cal-popup_right')
								.removeClass('cal-popup_bottom')
								.removeClass('cal-popup_top');

						let clickTop=obj.getBoundingClientRect().top+window.scrollY;
					
						let clickLeft=Math.round(obj.getBoundingClientRect().left);
						let clickMargin=Math.round(obj.getBoundingClientRect().width/2);
						$(obj).prev('.cal-popup')
								.removeClass('cal-popup_left')
								.removeClass('cal-popup_right')
								.removeClass('cal-popup_bottom')
								.removeClass('cal-popup_top');

						let popClass='cal-popup_top';
						$('#cal-popup').addClass(popClass).toggle();
						$('#cal-popup').css({'top':clickTop,'left':clickLeft,'margin-left': clickMargin});
						$('#cal-popup').show();


						//console.log(popViewInfos[index]);
						if(popViewInfos[index].opcao.length>0) {
							$('#cal-popup .js-opcaoEQtd').html(`Região: ${popViewInfos[index].opcao}`);
						} else {
							$('#cal-popup .js-opcaoEQtd').html(`Quantidade: ${popViewInfos[index].quantidade}`);
						}

						valorTabela=popViewInfos[index].valor;
						valorUnit=valorTabela;
						if(popViewInfos[index].quantitativo==1) valorTabela*=eval(popViewInfos[index].quantidade);

						$('#cal-popup .js-titulo').html(popViewInfos[index].procedimento);
						$('#cal-popup .js-plano').html(popViewInfos[index].plano);
						$('#cal-popup .js-profissional').val(popViewInfos[index].id_profissional);
						$('#cal-popup .js-valorDeDesconto').val(number_format(popViewInfos[index].desconto,2,",","."))
						$('#cal-popup .js-valorTabela').val(number_format(valorTabela,2,",","."));
						$('#cal-popup .js-valorUnit').val(number_format(valorUnit,2,",","."));
						if(popViewInfos[index].situacao=="observado" || popViewInfos[index].situacao=="naoAprovado") {
							$('#cal-popup .js-valorCorrigido').val(number_format(0,2,",","."));
						} else {
							$('#cal-popup .js-valorCorrigido').val(number_format(valorTabela-popViewInfos[index].desconto,2,",","."));
						}
						$('#cal-popup .js-obs').val(popViewInfos[index].obs);

						$('#cal-popup .js-autor').html(popViewInfos[index].autor);
						$('#cal-popup .js-autor-data').html(procedimentos[index].data);

						//$('#cal-popup .js-btn-descontoAplicarEmTodos').prop('checked',popViewInfos[index].descontoAplicarEmTodos==1?true:false)

						$('#cal-popup .js-situacao').val(popViewInfos[index].situacao);
						$('#cal-popup .js-index').val(index);
						//$('#cal-popup .js-valorDeDesconto').trigger('change');
						//	atualizaValor();	
						
					}

				// PAGAMENTOS
					var pagamentosHTML = `<div class="js-pagamento-item" style="background:var(--cinza1); border-radius:8px; margin-bottom:.5rem; padding:.5rem 1.5rem;">
												<div class="colunas3">
													<dl><dd><label class="js-num"></label><input type="text" name="" class="datepicker data js-vencimento" value="" /></dd></dl>												
													<dl><dd><input type="text" name="" value="" class="js-valor" /></dd></dl>
													<dl><dd>
														<select class="js-id_formadepagamento js-tipoPagamento">
															<option value="">Forma de Pagamento...</option>
															<?php echo $optionFormasDePagamento;?>
														</select>
													</dd></dl>
												</div>
												<div class="colunas3">

													<dl style="display:none">
														<dt>Bandeira</dt>
														<dd>

														<select class="js-debitoBandeira js-tipoPagamento">
															<option value="">selecione</option>
															<?php
															foreach($debitoBandeiras as $id_operadora=>$x) {
																echo '<optgroup label="'.utf8_encode($x['titulo']).'">';
																foreach($x['bandeiras'] as $band) {
																	echo '<option value="'.$band['id_bandeira'].'" data-id_operadora="'.$id_operadora.'"data-id_operadorabandeira="'.$id_operadora.$band['id_bandeira'].'" data-taxa="'.$band['taxa'].'" data-cobrarTaxa="'.$band['cobrarTaxa'].'">'.utf8_encode($band['titulo']).'</option>';
																}
																echo '</optgroup>';
															}
															?>
														</select>
													</dd></dl>


													<dl style="display:none">
														<dt>Bandeira</dt>
														<dd>
															<select class="js-creditoBandeira js-tipoPagamento">
																<option value="">selecione</option>
																<?php
																foreach($creditoBandeiras as $id_operadora=>$x) {
																	echo '<optgroup label="'.utf8_encode($x['titulo']).'">';
																	foreach($x['bandeiras'] as $band) {
																		echo '<option value="'.$band['id_bandeira'].'" data-id_operadora="'.$id_operadora.'" data-id_operadorabandeira="'.$id_operadora.$band['id_bandeira'].'" data-parcelas="'.$band['parcelas'].'" data-taxa="'.$band['taxa'].'">'.utf8_encode($band['titulo']).'</option>';
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
															<select class="js-parcelas js-tipoPagamento">
																<option value="">selecione a bandeira</option>
															</select>
														</dd>
													</dl>

													<dl style="display:none">
														<dt>Identificador</dt>
														<dd><input type="text" class="js-identificador js-tipoPagamento" /></dd>
													</dl>

												</div>
											
											</div>
											`;


					const pagamentosListar = () => {
						$('.js-pagamentos .js-pagamento-item').remove();
						//console.log(pagamentos);
						if(pagamentos.length>0) {

							/*if(pagamentos.length>1) {
								$('.js-pagamento-parcelado').prop('checked',true);

							}
							else {
								$('.js-pagamento-avista').prop('checked',true);
							}*/
							let index=1;
							pagamentos.forEach(x=>{
								$('.js-pagamentos').append(pagamentosHTML);

								$('.js-pagamento-item .js-vencimento:last').val(x.vencimento);
								$('.js-pagamento-item .js-valor:last').val(number_format(x.valor,2,",","."));


								$('.js-pagamento-item .js-vencimento:last').inputmask('99/99/9999');
								$('.js-pagamento-item .js-num:last').html(index++);
								$('.js-pagamento-item .js-vencimento:last').datetimepicker({timepicker:false,
																						format:'d/m/Y',
																						scrollMonth:false,
																						scrollTime:false,
																						scrollInput:false});
								$('.js-pagamento-item .js-valor:last').maskMoney({symbol:'', allowZero:true, showSymbol:true, thousands:'.', decimal:',', symbolStay: true});
								if(x.id_formapagamento) {
									$('.js-pagamento-item .js-id_formadepagamento:last').val(x.id_formapagamento).trigger('change');
									$('.js-pagamento-item .js-identificador:last').val(x.identificador);
									let tipo = $('.js-pagamento-item .js-id_formadepagamento:last option:selected').attr('data-tipo');

									if(tipo=="credito") {
										parcelaProv=x.qtdParcelas;
										//alert(parcelaProv);
										$('.js-pagamento-item .js-creditoBandeira:last').find(`option[data-id_operadorabandeira=${x.operadora}${x.creditoBandeira}]`).prop('selected',true);
										$('.js-pagamento-item .js-creditoBandeira:last').trigger('change');
									
									} else if(tipo=="debito") {
										$('.js-pagamento-item .js-debitoBandeira:last').find(`option[data-id_operadorabandeira=${x.operadora}${x.debitoBandeira}]`).prop('selected',true);

										//$('.js-pagamento-item .js-debitoBandeira:last').val(x.debitoBandeira);//   .trigger('change');
									}	
								}
							});

							if(pagamentos.length==1) $('.js-pagamento-item .js-valor:last').prop('disabled',true);
						}
						//console.log(pagamentos);
						$('textarea.js-json-pagamentos').val(JSON.stringify(pagamentos))
						//atualizaValor();
						desativarCampos();
					}

					const pagamentosRemover = (index) => {
						pagamentos.splice(index,1);
						pagamentosListar();
					}
					$(document).mouseup(function(e)  {
					    let container = $("#cal-popup");
					    // if the target of the click isn't the container nor a descendant of the container
					    if (!container.is(e.target) && container.has(e.target).length === 0) 
					    {
					       $('#cal-popup').hide();
					    }

					});
				
				const creditoDebitoValorParcela = (obj) => {


					obj = $(obj).parent().parent().parent().parent();

					let id_formadepagamento = $(obj).find('select.js-id_formadepagamento option:selected').val();
					let tipo = $(obj).find('select.js-id_formadepagamento option:selected').attr('data-tipo');
					
					if(id_formadepagamento.length>0) {

						//let valor = $('.js-valor').val().length>0?unMoney($('.js-valor').val()):0;

						let valorCreditoDebito=0;

						if(tipo=='credito') {
							let id_bandeira = $(obj).find('select.js-creditoBandeira').val();
							let id_operadora = $(obj).find('select.js-creditoBandeira option:checked').attr('data-id_operadora');
							let parcela = eval($(obj).find('select.js-parcelas option:selected').val());

							if(id_operadora!==undefined && parcela!==undefined) {


								let taxa = 0;
								let cobrarTaxa = 0;
								if(_taxasCredito[id_operadora][id_bandeira][parcela]) taxa=_taxasCredito[id_operadora][id_bandeira][parcela];
								//if(_taxasCreditoSemJuros[id_operadora][id_bandeira][parcela]) cobrarTaxa=eval(_taxasCreditoSemJuros[id_operadora][id_bandeira][parcela]);
								

								/*if(cobrarTaxa==1) {
									valorCreditoDebito=taxa==0?valor:(valor*(1+(taxa/100)));
								} else {
									valorCreditoDebito=valor;
								}

								valorCreditoDebito/=parcela;

								$('.js-valorCreditoDebito').val(number_format(valorCreditoDebito,2,",","."));
								$('.js-valorCreditoDebitoTaxa').val(`${cobrarTaxa==1?"+":"-"} ${taxa}%`);*/
							}

						} else if(tipo=='debito') {
							let taxa = eval($(obj).find('select.js-debitoBandeira option:selected').attr('data-taxa'));
							let id_operadora = $(obj).find('select.js-debitoBandeira option:checked').attr('data-id_operadora');
							let cobrarTaxa = eval($(obj).find('select.js-debitoBandeira option:selected').attr('data-cobrarTaxa'));

							
							

						} else {
							//$('.js-valorCreditoDebitoTaxa').val('-');
							//$('.js-valorCreditoDebito').val('-');

						}


					}
				}

				$(function(){
					pagamentos=JSON.parse($('textarea.js-json-pagamentos').val());
					
					$('.js-pagamentos').on('change','.js-debitoBandeira,.js-creditoBandeira,.js-parcelas,.js-valor',function(){
						
					//	creditoDebitoValorParcela($(this));
						let obj = $(this);
						setTimeout(function(){$(obj).parent().parent().parent().parent().find('.js-valor').trigger('keyup');},200);	
					});

					$('.js-pagamentos').on('change','.js-creditoBandeira',function(){

						let obj = $(this).parent().parent().parent();


						$(obj).find('select.js-parcelas option').remove();
						
						if($(this).val().length>0) {
							let semJuros = eval($(this).find('option:checked').attr('data-semjuros'));
							let parcelas = eval($(this).find('option:checked').attr('data-parcelas'));
						
							if($.isNumeric(parcelas)) {
								$(obj).find('select.js-parcelas').append(`<option value="">-</option>`);
								for(var i=1;i<=parcelas;i++) {
									semjuros='';
									if($.isNumeric(semJuros) && semJuros>=i) semjuros=` - sem juros`;
									if(parcelaProv && eval(parcelaProv)==i) sel=' selected';
									else sel ='';

									$(obj).find('select.js-parcelas').append(`<option value="${i}"${sel}>${i}x${semjuros}</option>`);
								}
							} else {
								$(obj).find('select.js-parcelas').append(`<option value="">erro</option>`);
							}
						} else {
							$(obj).find('select.js-parcelas').append(`<option value="">selecione a bandeira</option>`);
						}

						setTimeout(function(){$(obj).find('.js-valor').trigger('keyup');},200);
					});

					$('.js-pagamentos').on('change','.js-id_formadepagamento',function(){

						let obj = $(this).parent().parent().parent();

						setTimeout(function(){$(obj).find('.js-valor').trigger('keyup');},200);
					});

					$('.js-pagamentos').on('keyup','.js-identificador',function(){

						let obj = $(this).parent().parent().parent().parent();

						setTimeout(function(){$(obj).find('.js-valor').trigger('keyup');},200);
					});


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
							//console.log(`${val} = ${valorAcumulado}`);

							let item = {};
							item.vencimento=pagamentos[i].vencimento;
							item.valor=val;
							item.id_formapagamento=id_formapagamento;
							item.identificador=identificador;
							item.creditoBandeira=creditoBandeira;
							item.operadora=operadora;
							item.debitoBandeira=debitoBandeira;
							item.qtdParcelas=qtdParcelas;

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

							for(i=(index+1);i<numeroParcelas;i++) {

								if(pagamentos[i]) {
	 								let item = {};
									item.vencimento=pagamentos[i].vencimento;
									item.valor=valorParcela;
									parcelas.push(item);
								}

							}


							pagamentos=parcelas;


							$('textarea.js-json-pagamentos').val(JSON.stringify(pagamentos))
						}
					});

					$('.js-pagamentos').on('change','.js-id_formadepagamento',function(){
						let id_formadepagamento  = $(this).val();
						let obj = $(this).parent().parent().parent().parent();
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
		
					});

					$('.js-btn-aprovarTodosProcedimentos').click(function(){

						

						if(procedimentos.length>0) {

							swal({ title: "Atenção",text: "Você deseja realmente aprovar todos procedimentos?",type:"warning",showCancelButton:true,confirmButtonColor: "#DD6B55",confirmButtonText:"Sim!",cancelButtonText: "Não",closeOnConfirm: true,closeOnCancel: true }, function(isConfirm){   if (isConfirm) {  
										let aux = 0;
										procedimentos.forEach(x=>{
											if(procedimentos[aux].situacao=="aguardandoAprovacao") procedimentos[aux].situacao='aprovado';
											aux++;
										});
										$.notify('Procedimentos aprovados!');
										procedimentosListar();
								 } 
							});
							
						} else {

						}
					});

					$('.js-pagamentos').on('blur','.js-valor',function(){
						pagamentosListar();
					});

					$('#cal-popup .js-obs').keyup(function(){
						let index = $('.js-index').val();
						procedimentos[index].obs=$(this).val();
						procedimentosListar(true);
					})

					$('.js-btn-descontoGeral').click(function(){


						/*if($('.js-descontoGeral').val().length==0) {
							swal({title: "Erro!", text: 'Digite o % de desconto que será aplicado', html:true, type:"error", confirmButtonColor: "#424242"});
							$('.js-descontoGeral').addClass('erro');
						} else if(procedimentos.length==0) {
							swal({title: "Erro!", text: 'Adicione pelo menos um procedimento para aplicar o desconto', html:true, type:"error", confirmButtonColor: "#424242"});
						} else {
							let desconto = unMoney($('.js-descontoGeral').val().replace('.',','));
							
							let valor = descontoAtual = cont = 0;
							procedimentos.forEach(x=>{	
								valor = eval(x.valorCorrigido);

								descontoAplicar = x.valor*(desconto/100);
								descontoAtual = eval(x.desconto);

								procedimentos[cont].desconto=descontoAtual+descontoAplicar;
								procedimentos[cont].valorCorrigido=valor-descontoAplicar;
								procedimentos[cont].descontoAplicado=desconto;


								//console.log(`${valor} ${descontoAplicar} ${descontoAtual}`);
								cont++;

							});


							procedimentosListar(true);
							
							atualizaValor(true,false);

							$('.js-descontoGeral').val('');
						}*/
					})

					$('.js-btn-descontoAplicarEmTodos').click(function(){

						$('.js-valorTabela').trigger('change');
						if($('.js-check-todos').prop('checked')===true) {
							let index = $('.js-index').val();
							let count = 0; 
							let id_procedimento = procedimentos[index].id_procedimento;
						
							procedimentos.forEach(x=>{
								
									if(id_procedimento==procedimentos[count].id_procedimento) {
										procedimentos[count].desconto=procedimentos[index].desconto;
										procedimentos[count].valorCorrigido=procedimentos[index].valor-procedimentos[index].desconto;
										$(`.js-procedimentos .js-valor:eq(${count})`).html(number_format(procedimentos[count].valorCorrigido,2,",","."));
									}
								
								count++;
							});
						} 

						procedimentosListar(true);
						
						atualizaValor(true,false);

						$.notify('Desconto aplicado!');

						$('.js-check-todos').prop('checked',false);
						$('.js-btn-descontoAplicarEmTodos').parent().css('opacity','0.2');
			      		$('#cal-popup').hide();
			      
						
						//procedimentos[index].descontoAplicarEmTodos=descontoAplicarEmTodos;
					});

					$('.js-btn-fechar').click(function(){
						$('.js-valorDeDesconto').val(0);
						$('.js-check-todos').prop('checked',false);
						$('.js-btn-descontoAplicarEmTodos').parent().css('opacity','0.2');
						$('.cal-popup').hide();
					})

					$('input[name=pagamento]').click(function(){
						atualizaValor(false,false);
					});

					$('.js-pagamentos-quantidade').click(function(){

						let qtd = $(this).val();

						if(!$.isNumeric(eval(qtd))) qtd=1;
						else if(qtd<1) qtd=2;
						else if(qtd>=36) qtd=36;


						$('.js-pagamentos-quantidade').val(qtd);

						atualizaValor(true,true);
					});

					$('.js-procedimentos').on('click','.js-procedimento-item',function(index,el){
						
					});

					$('.js-btn-reprovar').click(function(){
					});

					$('.js-btn-aprovar').click(function(){
						if(valorSaldo!=0) {
							swal({title: "Erro!", text: 'Para salvar este tratamento, o saldo não pode apresentar diferença!', html:true, type:"error", confirmButtonColor: "#424242"});
						} else {
							let erro=validarTratamento();

							if(erro.length==0) {
								$('.js-procedimento-item').each(function(index,elem){
									let situacao = $(this).attr('data-situacao');


									if(erro.length==0) {
										if(situacao=='aprovado') {
											procedimentoARealizar=true;
										} 
										else if(situacao=='aguardandoAprovacao') {
											erro='Para aprovar este tratamento, não pode existir procedimentos aguardando aprovação!';
										}
									}
								});

								if(erro.length==0 && procedimentoARealizar===false) erro='Para aprovar este tratamento, é preciso que pelo menos um precedimento seja <b>aprovado</b>!';
							}

							if(erro.length>0) {
								swal({title: "Erro!", text: erro, html:true, type:"error", confirmButtonColor: "#424242"});
							} else {

								swal({ title: "Atenção",text: "Você deseja realmente aprovar este Plano de Tratamento?",type:"warning",showCancelButton:true,confirmButtonColor: "#DD6B55",confirmButtonText:"Sim!",cancelButtonText: "Não",closeOnConfirm: false,closeOnCancel: true }, function(isConfirm){   if (isConfirm) {    $('input[name=acao]').val('aprovar');$('form.js-form').submit();  } });

							}
						}
					})

					$('.js-btn-salvar').click(function(){
						if($('.js-procedimento-item').length==0) {
							swal({title: "Erro!", text: 'Para salvar este tratamento, adicione pelo menos um procedimento!', html:true, type:"error", confirmButtonColor: "#424242"});
						} else {

							let erro=validarTratamento();

							if(erro.length>0) {
								swal({title: "Erro!", text: erro, html:true, type:"error", confirmButtonColor: "#424242"});
							} else {

								if($(this).attr('data-loading')==0) {
									$(this).attr('data-loading',1);
									$(this).html('Savalando...');
									$('input[name=acao]').val('salvar');
									$('form.js-form').submit();
								} 
							}

						}
					});

					// PROCEDIMENTOS
						$('.js-btn-add').click(function(){

							let id_procedimento = $(`.js-id_procedimento`).val();
							let id_regiao = $(`.js-id_procedimento option:selected`).attr('data-id_regiao');
							let id_plano = $(`.js-id_plano`).val();
							let valor = $(`.js-id_plano option:selected`).attr('data-valor');
							let procedimento = $(`.js-id_procedimento option:selected`).text();
							let plano = $(`.js-id_plano option:selected`).text();
							let quantitativo = $(`.js-id_procedimento option:selected`).attr('data-quantitativo');
							let quantidade = $(`.js-inpt-quantidade`).val();
							let situacao = `aprovado`;
							let obs = $('.js-procedimento-obs').val();
							//let id_profissional = $('.js-id_profissional').val();
							let iniciais = $('.js-id_profissional option:selected').attr('data-iniciais');
							let iniciaisCor = $('.js-id_profissional option:selected').attr('data-iniciaisCor');
							let valorCorrigido=valor;

							//alert(quantitativo);
							//
							if(quantitativo==1) {
								valorCorrigido=quantidade*valor;
							} 

							let erro = ``;
							if(id_procedimento.length==0) erro=`Selecione o Procedimento`;
							//else if(quantitativo==1 && (quantidade.length==0 || eval(quantidade)<=0 || eval(quantidade)>=99)) erro=`Defina a quantidade<br />(mín: 1, máx: 99)`;
							else if(id_regiao>=2 && $(`.js-regiao-${id_regiao}-select`).val().length==0) erro=`Preencha a Região`
							else if(id_plano.length==0) erro=`Selecione o Plano`;
							else if(quantidade<=0) erro=`A quantidade não pode ser valor negativo!`; 
							if(erro.length==0) {

								let linhas=1;
								if(id_regiao>=2) {
									linhas = eval($(`.js-regiao-${id_regiao}-select`).val().length);
								}

								let item= {};

								
								let opcoes = ``;
								for(var i=0;i<linhas;i++) {
									item = {};
									item.obs = obs;
									item.id_procedimento=id_procedimento;
									item.procedimento=procedimento;
									item.id_regiao=id_regiao;
									item.id_plano=id_plano; 
									item.plano=plano;
									item.profissional=0;
									item.quantidade=quantidade;
									item.situacao=situacao;
									item.valor=valor;
									item.desconto=0;
									item.valorCorrigido=valorCorrigido;
									item.descontoAplicarEmTodos=0;
									item.quantitativo=quantitativo;
									//item.id_profissional=id_profissional;
									item.iniciais=iniciais
									item.iniciaisCor=iniciaisCor;
									let dt = new Date();
									let dia = dt.getDate();
									let mes = dt.getMonth();
									let min = dt.getMinutes();
									let hrs = dt.getHours();
									mes++
									mes=mes<=9?`0${mes}`:mes;
									dia=dia<=9?`0${dia}`:dia;
									min=min<=9?`0${min}`:min;
									hrs=hrs<=9?`0${hrs}`:hrs;

									let data = `${dia}/${mes}/${dt.getFullYear()} ${hrs}:${min}`;
									item.data=data;
									item.autor=autor;
									item.id_usuario=id_usuario;

									opcao = id_opcao = ``;
									if(id_regiao>=2) {
										id_opcao = $(`.js-regiao-${id_regiao}-select option:selected:eq(${i})`).val();
										opcao = $(`.js-regiao-${id_regiao}-select option:selected:eq(${i})`).text();
									}
									item.opcao=opcao;
									item.id_opcao=id_opcao;

									procedimentos.push(item);
								}
								$(`.js-id_procedimento`).val('').trigger('chosen:updated');
								$(`.js-id_plano`).val('').trigger('chosen:updated');
								//$(`.js-id_profissional`).val('').trigger('chosen:updated');
								
								$(`.js-inpt-quantidade`).val(1).parent().parent().hide();
								
								$(`.js-regiao-${id_regiao}-select`).val([]).trigger('chosen:updated').parent().parent().hide();
								$.fancybox.close();
								procedimentosListar(true);
							} else {
								swal({title: "Erro!", text: erro, html:true, type:"error", confirmButtonColor: "#424242"});
							}
						});

						$('select.js-id_procedimento').change(function(){

							let id_procedimento = $(this).val();

							if(id_procedimento.length>0) {
								let id_regiao = $(this).find('option:selected').attr('data-id_regiao');
								let regiao = $(this).find('option:selected').attr('data-regiao');
								let quantitativo = $(this).find('option:selected').attr('data-quantitativo');

								$(`.js-inpt-quantidade`).parent().parent().hide();
								if(quantitativo==1) {
									$(`.js-inpt-quantidade`).parent().parent().show();
								}
								$(`.js-regiao`).hide();
								$(`.js-regiao-${id_regiao}`).show();
								//$(`.js-regiao-${id_regiao}`).find('select').chosen({hide_results_on_select:false,allow_single_deselect:true});
								$(`.js-regiao-${id_regiao}`).find('select').select2({  allowClear:true,closeOnSelect: false,dropdownParent: $("#modalProcedimento")});

								$(`.js-procedimento-btnOk`).show();
								let data = `ajax=planos&id_procedimento=${id_procedimento}`;
								$.ajax({
									type:"POST",
									data:data,
									success:function(rtn) {
										if(rtn.success) { 
											$('.js-id_plano option').remove();
											$('.js-id_plano').append(`<option value=""></option>`);
											//console.log(rtn.planos);
											if(rtn.planos) {

												rtn.planos.forEach(x=> {
													$('.js-id_plano').append(`<option value="${x.id}" data-valor="${x.valor}">${x.titulo}</option>`);
												});
											}
											//$(".js-id_plano").select2({dropdownParent: $("#modalProcedimento")});
											//$('.js-id_plano').trigger('chosen:updated')
										}
									},
								})
							} else {
								$(`.js-regiao`).hide();
								$(`.js-procedimento-btnOk`).hide();
							}
						});

						$('.js-procedimentos').on('click','.js-btn-removerProcedimento',function() {
							let index = $(this).index('.js-procedimentos .js-btn-removerProcedimento');
							procedimentosRemover(index);
						});


						$('#cal-popup').on('change','.js-situacao',function(){
							let index = $('#cal-popup .js-index').val();
							procedimentos[index].situacao=$(this).val();
							//	$('input[name=pagamento][value=avista]').click();
							procedimentosListar(true);
							atualizarValorDesconto();
						})

						$('#cal-popup').on('change','.js-valorTabela',function(){
								let index = $('#cal-popup .js-index').val();
								let valorTabela = unMoney($('.js-valorTabela').val());
								let valorDesconto = $.isNumeric(unMoney($('.js-valorDeDesconto').val()))?unMoney($('.js-valorDeDesconto').val()):0;
								let valorCorrigido = valorTabela-valorDesconto;
								//alert(valorCorrigido);
								if(valorCorrigido<0) {
									valorCorrigido=0;
									valorDesconto=valorTabela;
								}
									
								$('.js-valorDeDesconto').val(number_format(valorDesconto,2,",","."));
								$('.js-valorCorrigido').val(number_format(valorCorrigido,2,",","."));
								if(index>=0 && procedimentos[index]) {
									procedimentos[index].desconto=valorDesconto;
									procedimentos[index].valor=valorTabela;
									procedimentos[index].valorCorrigido=valorTabela-valorDesconto;
								}
								procedimentosListar(true);

						});

						$('#cal-popup').on('change','.js-valorDeDesconto',function(){

							$('#cal-popup .js-btn-descontoAplicarEmTodos').parent().css('opacity',1);

						});

						/*$('#cal-popup').on('change','.js-profissional',function(){
							let index = $('#cal-popup .js-index').val();
							procedimentos[index].id_profissional=$(this).val();
							procedimentos[index].iniciais=$(this).find('option:selected').attr('data-iniciais');
							procedimentos[index].iniciaisCor=$(this).find('option:selected').attr('data-iniciaisCor');
							procedimentosListar();
						})
						$('.js-table-procedimentos').on('change','.js-profissional',function(){
							
							if(tratamentoAprovado===1) { 
								swal({title: "Atenção!", text: 'Ao alterar o profissional, as comissões poderão ser alteradas!', html:true, type:"warning", confirmButtonColor: "#424242"});
							}
							let index = $(this).index(`.js-table-procedimentos .js-profissional`);
							procedimentos[index].id_profissional=$(this).val();
							procedimentos[index].profissional=$(this).find(':selected').text();
							procedimentosListar();
						});*/

						$('.js-table-procedimentos').on('change','.js-quantidade',function(){
							let index = $(this).index(`.js-table-procedimentos .js-quantidade`);
							procedimentos[index].quantidade=$(this).val();
							procedimentosListar();
						});

						$('.js-table-procedimentos').on('change','.js-situacao',function(){
							let index = $(this).index(`.js-table-procedimentos .js-situacao`);
							procedimentos[index].situacao=$(this).val();
							procedimentosListar();
						});

						$('.js-table-procedimentos').on('change','.js-valor',function(){
							let index = $(this).index(`.js-table-procedimentos .js-valor`);
							procedimentos[index].valor=unMoney($(this).val());
							
							procedimentosListar();
						});

						$('.js-table-procedimentos').on('click','.js-obs',function(){
							let index = $(this).index(`.js-procedimento-item .js-obs`);
							let obsVal = procedimentos[index].obs;
							$('.js-boxObs-obs').val(obsVal);
							$('.js-boxObs-index').val(index);
							$.fancybox.open({
								'src':'#boxObs'
							});
						});

						$('#boxObs').on('click','.js-boxObs-salvar',function(){
							let index = $('#boxObs .js-boxObs-index').val();
							let obsVal = $('#boxObs .js-boxObs-obs').val();
							procedimentos[index].obs=obsVal;
							procedimentosListar();
							$.fancybox.close();
						});

						procedimentos=JSON.parse($('textarea.js-json-procedimentos').val());
						procedimentosListar();
				
					// PAGAMENTOS
						$('.js-pagamentos').on('change','.js-valor',function() {
							let index = $(this).index('.js-pagamentos .js-valor');
							pagamentos[index].valor=unMoney($(this).val());
							pagamentosListar();
						});

						$('.js-pagamentos').on('change','.js-vencimento',function() {
							let index = $(this).index('.js-pagamentos .js-vencimento');
							pagamentos[index].vencimento=$(this).val();
							pagamentosListar();
						});

						$('.js-pagamentos').on('click','.js-btn-removerPagamento',function() {
							let index = $(this).index('.js-pagamentos .js-btn-removerPagamento');
							pagamentosRemover(index);
							pagamentosListar();
						});

						$('.js-btn-addPagamento').click(function() {

							let qtd = unMoney($('.js-pagamentos-quantidade').val());

							if($.isNumeric(qtd)) {

								let valorParcela = valorSaldo/qtd;

								
								for(var i=1;i<=qtd;i++) {
									item = {};
									item.vencimento = '';
									item.id_formapagamento = '' ;
									item.valor = valorParcela>0?valorParcela:valorParcela*-1;
									pagamentos.push(item);
								}
								pagamentosListar();
								$('.js-pagamentos-quantidade').val(1)
							} else {
								alert('erro');
							}
						});

						$('.js-btn-addProcedimento').click(function(){
							let id_regiao = $(`.js-id_procedimento option:selected`).attr('data-id_regiao');
							$(`.js-id_procedimento`).val('').trigger('chosen:updated');
							$(`.js-id_plano`).val('').trigger('chosen:updated');
							//$(`.js-id_profissional`).val('').trigger('chosen:updated');
							$(`.js-inpt-quantidade`).val(1).parent().parent().hide();
							$(`.js-regiao-${id_regiao}-select`).val([]).trigger('chosen:updated').parent().parent().hide();
							$(`.js-procedimento-obs`).val('');
							$.fancybox.open({
								src:'#modalProcedimento',

								afterLoad:function(){
									//$(".js-id_procedimento,.js-id_plano,.js-id_profissional").select2({dropdownParent: $("#modalProcedimento")});
									$(".js-id_procedimento,.js-id_plano").select2({dropdownParent: $("#modalProcedimento")});
								},
								afterClose:function(){
									//$(".js-id_procedimento,.js-id_plano,.js-id_profissional").select2('destroy');
									$(".js-id_procedimento,.js-id_plano").select2('destroy');
								}
							})
						})

						$('.js-metodoPagamento').click(function() {
							if($(this).val()=="parcelado") {
								$('.js-parcelas').parent().parent().show();
							} else {
								$('.js-parcelas').parent().parent().hide();
							}
						});

						$('.js-metodoPagamento:checked').trigger('click');


						
						pagamentosListar();

					desativarCampos();

					$(document).mouseup(function(e)  {

						if($(".select2-container").is(":visible")) {
						    var container = $('.select2-dropdown');
						    var container2 = $('.select2-container');

						    // if the target of the click isn't the container nor a descendant of the container
						    if ((!container.is(e.target) && container.has(e.target).length === 0) && 
						    	(!container2.is(e.target) && container2.has(e.target).length === 0)) {
						   		$('.js-id_procedimento').select2('close');
						   		$('.js-id_plano').select2('close');
						   		//$('.js-id_profissional').select2('close');
						   		if($('.js-regiao-1-select').data('select2')) $('.js-regiao-1-select').select2('close');
						   		if($('.js-regiao-2-select').data('select2')) $('.js-regiao-2-select').select2('close');
						   		if($('.js-regiao-3-select').data('select2')) $('.js-regiao-3-select').select2('close');
						   		if($('.js-regiao-4-select').data('select2')) $('.js-regiao-4-select').select2('close');
						    }
						}
					});


					$('#modalProcedimento').hide();

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

						$('form.js-form').submit();
					});

					$('#cal-popup').on('click','.js-btn-excluir',function(){

						swal({
							title: "Atenção",
							text: "Você tem certeza que deseja remover este registro?",
							type: "warning",
							showCancelButton: true,
							confirmButtonColor: "#DD6B55",
							confirmButtonText: "Sim!",
							cancelButtonText: "Não",
							closeOnConfirm: true,
							closeOnCancel: false 
							}, 
							function(isConfirm) {   
								if (isConfirm) {  
								 	let index = $('#cal-popup .js-index').val();
									procedimentos.splice(index,1);
									procedimentosListar(true);	
								} else {   
									swal.close();   
								}
							}
							);

						
					})
					
				});
			</script>
			
		
			<form method="post" class="form js-form"  autocomplete="off" enctype="multipart/form-data">
				<input type="hidden" name="acao" value="salvar" />

				<section class="grid" style="padding:2rem; height:calc(100vh - 210px);?>">

						<div class="box" style="display:flex; flex-direction:column;">
							<div class="filter">
								<div class="filter-group">
									<div class="filter-button">
										<a href="<?php echo $_page."?id_paciente=$paciente->id&$url";?>"><i class="iconify" data-icon="bx-bx-left-arrow-alt"></i></a>
									</div>
								</div>

								<div class="filter-group">
									<div class="filter-input">
										<input type="text" name="titulo" value="<?php echo $values['titulo'];?>" placeholder="Nome do plano" style="width:300px" />
									</div>
									<div class="filter-input">
										&nbsp;&nbsp;<select name="id_profissional" class="js-id_profissional" placeholder="Selecione o Profissional">
											<option value="">Selecione o Profissional...</option>
											<?php
											foreach($_profissionais as $x) {
												$iniciais=$x->calendario_iniciais;
												echo '<option value="'.$x->id.'" data-iniciais="'.$iniciais.'" data-iniciaisCor="'.$x->calendario_cor.'"'.($values['id_profissional']==$x->id?" selected":"").'>'.utf8_encode($x->nome).'</option>';
											}
											?>
										</select>
									</div>
								</div>

								<?php /*
								<div class="filter-group">
									<div class="filter-data">
										<h1>Valor Total</h1>
										<h2 class="js-valorTotal">0,00</h2>
									</div>					
								</div>	
								*/ ?>

								<?php
								if(is_object($cnt)) {
								?>
								<input type="hidden" name="status" />
								<div class="filter-group filter-group_right">
									<h1 class="filter-group__titulo">Status do Plano:</h1>
									<div class="filter-links">
										<a href="javascript:;" data-status="PENDENTE" class="js-btn-status<?php echo $cnt->status=="PENDENTE"?" active":"";?>">Em aberto</a>
										<a href="javascript:;" data-status="APROVADO" class="js-btn-status<?php echo $cnt->status=="APROVADO"?" active":"";?>">Aprovado</a>
										<a href="javascript:;" data-status="CANCELADO" class="js-btn-status<?php echo $cnt->status=="CANCELADO"?" active":"";?>">Reprovado</a>
									</div>
								</div>
								<?php
								} else {
								?>
								<div class="tooltip filter-group filter-group_right" style="opacity: 0.5" title="Salve o tratamento para poder alterar o status">
									<div class="filter-links">
										<a href="javascript:;" data-status="PENDENTE" class="active">Em aberto</a>
										<a href="javascript:;" data-status="APROVADO">Aprovado</a>
										<a href="javascript:;" data-status="CANCELADO">Reprovado</a>
									</div>
								</div>
								<?php
								}
								?>								

								<div class="filter-group">
									<div class="filter-button">
										<?php if(is_object($cnt)){?><a href="?deletaTratamento=<?php echo $cnt->id."&".$url;?>" class="js-deletar"><i class="iconify" data-icon="bx-bx-trash"></i></a><?php }?>

										<?php if(is_object($cnt)){?><a href="impressao/planodetratamento.php?id=<?php echo md5($cnt->id);?>" target="_blank"><i class="iconify" data-icon="bx-bx-printer"></i></a><?php }?>
										<a href="javascript:;" class="azul js-btn-salvar" data-loading="0"><i class="iconify" data-icon="bx-bx-check"></i><span>Salvar</span></a>
									</div>
								</div>
							</div>
							


							<div class="grid grid_auto" style="flex:1;">
								<fieldset style="margin:0;">
									
									<legend><span class="badge">1</span> Adicione os Procedimentos</legend>
									<?php
									if($tratamentoAprovado===false) {
									?>
									<?php /*<div class="clearfix" style="margin-bottom: 10px;">
										<a href="javascript:;" class="button js-btn-addProcedimento tooltip " title="Adicionar Procedimento" style="background:var(--azul);color:#FFF;float: right"><i class="iconify" data-icon="ic-baseline-add"></i> Adicionar Procedimento</a>
									</div>*/?>
									
											
									<dl>
										<dd>
											<a href="javascript:;" class="button js-btn-addProcedimento" style="background:var(--verde);"><i class="iconify" data-icon="bx-bx-plus"></i><span>Novo Procedimento</span></a>
											<?php /*<a href="javascript:;" class="button js-btn-aprovarTodosProcedimentos">Aprovar Todos Aguardando Aprovação</span></a>*/?>
										</dd>
									</dl>							
									
									<?php
									}
									?>

									<textarea name="procedimentos" class="js-json-procedimentos" style="display:none"><?php echo $values['procedimentos'];?></textarea>
									
									<div class="registros2" style="margin-top:1rem;"><?php /* style="height:<?php echo $tratamentoAprovado==false?"calc(115vh - 570px)":"calc(100vh - 400px)";?>; overflow:auto;">*/?>

										<div class="js-procedimentos">
											<?php /*<div class="card">
												<div class="js-descricao">
													<h1>Exodontia Simples</h1>
													<h2>45 - Plano SD</h2>
												</div>
												<div class="js-valor">
													R$2.000,00
												</div>
												<div class="js-profissional">
													<div class="cal-item-foto"><span style="background:#0066FF">KF</span></div>
												</div>
											</div>*/?>
										</div>
									</div>
									
								</fieldset>												
								
								<fieldset style="margin:0;">
									<legend><span class="badge">2</span> Defina o Financeiro</legend>

									<?php /*
									<div class="filter-button">										
										<a href="javascript:;" class="js-btn-boxDesconto">Aplicar Desconto</a>
									</div>
									*/ ?>

									<?php /*<div class="colunas4">
										<dl>
											<dt>Tratamento</dt>
											<dd style="color:red"><span class="js-valorTotal">R$ 0,00</span></dd>
										</dl>
										<dl>
											<dt>Pagamentos</dt>
											<dd style="color:green"><span class="js-valorPagamento">R$ 0,00</span></dd>
										</dl>
										<dl>
											<dt>Saldo</dt>
											<dd ><span class="js-valorSaldo">R$ 0,00</span></dd>
										</dl>
										<?php
										if($tratamentoAprovado===false) {
										?>
										<dl>
											<dt></dt>
											<dd>
												<a href="javascript:;" class="registros__acao js-btn-addPagamento"><i class="iconify" data-icon="ic-baseline-add"></i></a>
											</dd>
										</dl>
										<?php
										}
										?>
									</div>*/?> 
									<?php
									if($tratamentoAprovado===false) {
									?>
										<div class="js-formDiv-financeiro">
											<div class="colunas2">
												<dl>
													<dd>
														<label><input type="radio" name="pagamento" value="avista" class="js-pagamento-avista"<?php echo (is_object($cnt) and $cnt->pagamento=="avista")?" checked":"";?> /> À Vista</label>
														<label><input type="radio" name="pagamento" value="parcelado" class="js-pagamento-parcelado"<?php echo (is_object($cnt) and $cnt->pagamento=="parcelado")?" checked":"";?> /> Parcelado em</label>
														<input type="number" name="parcelas" style="float:left;width:50px;display: none;" value="<?php echo is_object($cnt)?$cnt->parcelas:1;?>" class="js-pagamentos-quantidade" />
													</dd>
												</dl>
												<dl>													
													<dd>
														<label style="white-space:nowrap">Valor Total:</label><input type="text" class="js-valorTotal" val="0.00" disabled style="max-width:90px; text-align:center;" />
														<a href="javascript:;" class="button js-btn-boxDesconto">Aplicar desconto</a>
													</dd>
												</dl>												
											</div>
										</div>
										<?php 
										/*<dl class="dl4">
											<dt>&nbsp;</dt>
											<dd>
												<a href="javascript:;" class="button js-btn-addPagamento tooltip" title="Adicionar Procedimento" style="background:var(--azul);color:#FFF;margin-top: 15px;"><i class="iconify" data-icon="ic-baseline-add"></i> Adicionar Parcela</a>
											</dd>
										</dl>*/?>
									<?php
									}
									?>
									<textarea name="pagamentos" class="js-json-pagamentos" style="display:none;"><?php echo $values['pagamentos'];?></textarea>
									
										
									<div class="js-pagamentos" style="margin-top:1rem;">
										
									</div>
										
								</fieldset>
								
							</div>
						</div>
				</section>

			</form>
			<script type="text/javascript">
				<?php
				//<a href="javascript:;" class="reg-group js-procedimento-item" style="border-left:solid 10px var(--cor1);">
				?>
				var procedimentosDescontoHMTL = `<a href="javascript:;" class="reg-group js-procedimento-item">
													<div class="js-descricao" style="width:5%;">
														<input type="checkbox" name="pagamentos[]" class="js-checkbox-descontos" checked />
													</div>
													<div class="reg-data js-descricao">
														
														<h1 class="js-procedimento"></h1>
														<p class="js-regiao"></p>
													</div>
													<div class="js-valor">
													</div>
													<div class="js-profissional">
														
													</div>
												</a>`;

				const procedimentosListarDesconto = () => {
					$('.js-procedimentos-descontos .js-procedimento-item').remove();
					if(procedimentos.length>0) {
						$('.js-btn-aprovarTodosProcedimentos').show();
						let index=0;
						let total = 0;
						let descontoPersistido = 0;
					
						procedimentos.forEach(x=> {
   
							if(x.situacao!="naoAprovado" && x.situacao!="observado") {
								popViewInfos[index] = x;

								//console.log(x);

								btnExcluir='';
								if(tratamentoAprovado===1 && x.situacao=="aprovado") btnExcluir='Ex';

								let corSituacao="blue";
								if(x.situacao=="aprovado") corSituacao="green";
								else if(x.situacao=="naoAprovado") corSituacao="red";
								else if(x.situacao=="observado") corSituacao="orange";

								/*if(eval(x.id_profissional)>0) {
									iniciais=`<div class="cal-item-foto"><span style="background:${x.iniciaisCor}">${x.iniciais}</span></div>`;
								} else {
									iniciais=`<div class="cal-item-foto"><span style=""><span class="iconify" data-icon="bi:person-fill" data-inline="false"></span></span></div>`
								}*/
								$(`.js-procedimentos-descontos`).append(procedimentosDescontoHMTL);
								$(`.js-procedimentos-descontos .js-procedimento-item:last`).attr('data-situacao',x.situacao);
								//$(`.js-procedimentos-descontos .js-procedimento-item:last`).css('border-left',`solid 10px ${corSituacao}`)
								$(`.js-procedimentos-descontos .js-procedimento-item:last`).click(function(){popView(this);})
								$(`.js-procedimentos-descontos .js-procedimento:last`).html(x.procedimento);
								$(`.js-procedimentos-descontos .js-regiao:last`).html(eval(x.quantitativo)==1?x.quantidade:x.opcao);
								$(`.js-procedimentos-descontos .js-regiao:last`).append(` - ${x.plano}`);

								if(x.quantitativo==0 && x.opcao.length==2) {
									//x.opcao=`<span class="iconify" data-icon="la:tooth" data-height="13" style="color:var(--cor1)" data-inline="true"></span>${x.opcao}`;
								}

								$(`.js-procedimentos-descontos .js-regiao:last`).html(x.quantitativo==1?`Qtd.: ${x.quantidade} - `:x.opcao);
								if(x.opcao.length==0) {
									$(`.js-procedimentos-descontos .js-regiao:last`).append(`${x.plano}`);
								} else {
									$(`.js-procedimentos-descontos .js-regiao:last`).append(` - ${x.plano}`);
								}

								if(x.desconto) {
									descontoPersistido+=x.desconto;
									$(`.js-procedimentos-descontos .js-valor:last`).html(`<strike>${number_format((eval(x.quantitativo)==1?x.quantidade*x.valor:x.valor),2,",",".")}</strike><br />${number_format(x.valorCorrigido,2,",",".")}`);
								} else {
									$(`.js-procedimentos-descontos .js-valor:last`).html(number_format(x.valorCorrigido?x.valorCorrigido:x.valor,2,",","."));
								}

								//$(`.js-procedimentos-descontos .js-profissional:last`).html(iniciais);

							}
						});


					} else {

						$('.js-btn-aprovarTodosProcedimentos').hide();
					}
					//console.log(procedimentos);
					$('textarea.js-json-procedimentos').val(JSON.stringify(procedimentos));


					
					atualizarValorDesconto();
					
				}
				
				const atualizarValorDesconto = () => {

					let valorProcedimentos = 0;
					let valorProcedimentosSemDesconto = 0;
					let valorDesconto = ``;
					let desconto = 0;
					let descontoPersistido = 0;
					
					let cont = 0;
					procedimentos.forEach(x=>{
						if(x.situacao!="naoAprovado" && x.situacao!="observado") {
								//console.log(`- ${x.desconto} - ${x.situacao} ${cont} - ${$(`#boxDesconto .js-checkbox-descontos:eq(${cont})`).prop('checked')}`)
							if($(`#boxDesconto .js-checkbox-descontos:eq(${cont})`).prop('checked')===true) {
								valorProcedimentos+=eval(x.valorCorrigido);
								if(eval(x.quantitativo)==1) {
									valorProcedimentosSemDesconto+=eval(x.valor*eval(x.quantidade));
								} else {
									valorProcedimentosSemDesconto+=eval(x.valor);
								}
								descontoPersistido+=eval(x.desconto);
							}
							cont++;
						}

						/*if($(`#boxDesconto .js-checkbox-descontos:eq(${cont})`).prop('checked')==true) {
							
						} */
					});

					if($('#boxDesconto .js-select-descontoEm').val()=="dinheiro") {
						desconto = unMoney($('#boxDesconto .js-input-desconto').val());
						valorDesconto=`R$ ${number_format(desconto,2,",",".")}`;
						descontoAplicado=desconto;
					} else {
						desconto = unMoney($('#boxDesconto .js-input-desconto').val().replace(".",","));
						descontoAplicado = valorProcedimentos*(desconto/100);
						valorDesconto=`R$ ${number_format(descontoAplicado,2,",",".")}`;
					}

					let valorComDesconto = valorProcedimentos - descontoAplicado;
					console.log(valorProcedimentos+' '+valorProcedimentosSemDesconto+' '+desconto);
					if(valorProcedimentosSemDesconto!=valorProcedimentos) {
						$('.js-btn-removerDesconto').parent().parent().show();
						$('#boxDesconto .js-valorProcedimentos').html(`<strike>R$ ${number_format(valorProcedimentosSemDesconto,2,",",".")}</strike><br />R$ ${number_format(valorProcedimentos,2,",",".")}`);

						$('.js-valorDescontoAplicados').html(`R$ ${number_format(descontoPersistido,2,",",".")}`).parent().parent().show();
					} else {
						//$('.js-btn-removerDesconto').parent().parent().hide();
						$('#boxDesconto .js-valorProcedimentos').html(`R$ ${number_format(valorProcedimentos,2,",",".")}`);
						$('.js-valorDescontoAplicados').html(`R$ ${number_format(descontoPersistido,2,",",".")}`).parent().parent().show();
						//$('.js-valorDescontoAplicados').html('').parent().parent().hide();
					}
					$('#boxDesconto .js-valorDesconto').html(`R$ ${number_format(descontoAplicado,2,",",".")}`);
					$('#boxDesconto .js-valorComDesconto').html(`R$ ${number_format(valorComDesconto,2,",",".")}`);

				}

				$(function(){
					$('#boxDesconto .js-btn-removerDesconto').click(function(){
						let contProcedimento = 0;
						procedimentos.forEach(x=>{

							if(x.quantitativo==1) {
								procedimentos[contProcedimento].valorCorrigido=x.valor*x.quantidade;
							} else {
								procedimentos[contProcedimento].valorCorrigido=x.valor;
							}
							procedimentos[contProcedimento].desconto=0;
								
							contProcedimento++;
						});

						procedimentosListar(true);
						
						atualizaValor(true,false);

						$.fancybox.close();
						$.notify('Desconto removido!');
						$('.js-input-desconto').val('');
					});

					$('#boxDesconto .js-btn-aplicarDesconto').click(function(){

						let tipoDesconto = $('#boxDesconto .js-select-descontoEm').val();
						let quantidadeDesconto = $('#boxDesconto .js-checkbox-descontos:checked').length;
						let desconto = unMoney($(`#boxDesconto .js-input-desconto`).val());
						//console.log('Descontando '+desconto);
						if(quantidadeDesconto==0) {
							swal({title: "Erro", text: 'Selecione pelo menos um procedimento para aplicar desconto!', html:true, type:"error", confirmButtonColor: "#424242"});
								
						} else if(desconto==0 || desconto===undefined || desconto==='' || !desconto) {
							swal({title: "Erro", text: 'Defina o desconto que deverá ser aplicado!', html:true, type:"error", confirmButtonColor: "#424242"});
						} else {
							let valorTotal = 0;
							let cont = 0;
							let qtdItensDesconto = 0;

							procedimentos.forEach(x=>{
								//console.log(cont+' '+x.situacao);
								if(x.situacao!="naoAprovado" && x.situacao!="observado") {
									if($(`#boxDesconto .js-checkbox-descontos:eq(${cont})`).prop('checked')===true) {
										valorTotal+=eval(x.valorCorrigido);
										console.log(cont+' '+x.situacao+'->'+x.valorCorrigido);
										qtdItensDesconto++;
									}
									cont++;
								}
							});

							if(tipoDesconto!="dinheiro") {
								desconto = unMoney($(`#boxDesconto .js-input-desconto`).val().replace('.',','));
								desconto = (valorTotal*(desconto/100)).toFixed(2);
							}

							// calcula percentual do desconto em cima do valor total
							let descontoParcentual = ((desconto/valorTotal)*100).toFixed(4);


							//alert(descontoParcentual+' '+desconto);

							console.log('Valor Total:'+valorTotal+' Desconto: '+desconto+' Perc:'+descontoParcentual);;
							
							if(desconto==0 || desconto===undefined || desconto==='' || !desconto) {
								swal({title: "Erro", text: 'Defina o desconto que deverá ser aplicado!', html:true, type:"error", confirmButtonColor: "#424242"});
								$(`#boxDesconto .js-input-desconto`).addClass('erro');
							} else {
								let cont = 0;
								let contProcedimento = 0;
								procedimentos.forEach(x=>{
									console.log(x);
									if(x.situacao=="aprovado") {

										if($(`#boxDesconto .js-checkbox-descontos:eq(${cont})`).prop('checked')===true) {
											let desc = 0;

											if(x.desconto>0) {
												valorProc=procedimentos[contProcedimento].valorCorrigido;
											} else {
												valorProc=procedimentos[contProcedimento].valor;
												if(eval(x.quantitativo)==1) valorProc*=eval(x.quantidade);
											}

											equivalente = (valorProc/valorTotal).toFixed(8);
											descontoAplicar = desconto*equivalente;

											console.log(valorProc+' eq '+equivalente+' = '+descontoAplicar);

											//descontoAplicar = desconto/qtdItensDesconto;




											if(procedimentos[contProcedimento].desconto && $.isNumeric(procedimentos[contProcedimento].desconto)) {
												desc=procedimentos[contProcedimento].desconto+descontoAplicar;
											} else {
												desc=descontoAplicar;
											}
											valorProc=procedimentos[contProcedimento].valor;
											if(eval(x.quantitativo)==1) {
												valorProc*=eval(x.quantidade);
												procedimentos[contProcedimento].valorCorrigido=valorProc-desc;
												procedimentos[contProcedimento].desconto=desc;///eval(x.quantidade);
											} else {

												procedimentos[contProcedimento].valorCorrigido=valorProc-desc;
												procedimentos[contProcedimento].desconto=desc
											}

											//console.log('desconto em '+cont+'\n'+procedimentos[contProcedimento].valor+' - '+desc)
										}
										cont++;
									}
									contProcedimento++;
								});

								procedimentosListar(true);
								
								atualizaValor(true,false);

								$.fancybox.close();
								$.notify('Desconto aplicado!');
								$('.js-input-desconto').val('');

							}
						}
					})

					$('#boxDesconto').on('click .js-checkbox-descontos',function(){
						atualizarValorDesconto();
					});

					$('.js-btn-boxDesconto').click(function(){
						$.fancybox.open({
							src:"#boxDesconto",
							modal:false,
							afterLoad:function() {
								procedimentosListarDesconto();
							}

						})
					});
					$('#boxDesconto .js-select-descontoEm').change(function(){
						$('#boxDesconto .js-input-desconto').maskMoney('destroy');
						$('#boxDesconto .js-input-desconto').val('');
						if($(this).val()=="dinheiro") {
							$('#boxDesconto .js-input-desconto').maskMoney({symbol:'', allowZero:true, showSymbol:true, thousands:'.', decimal:',', symbolStay: true}).attr('maxlength',10);
							
						} else {
							$('#boxDesconto .js-input-desconto').maskMoney({symbol:'', precision:1, suffix:'%', allowZero:true, showSymbol:true, thousands:'', decimal:'.', symbolStay: true}).attr('maxlength',6);
						}
						$('#boxDesconto .js-input-desconto').trigger('keyup');
					}).trigger('change');

					$('#boxDesconto .js-input-desconto').keyup(function(){
						let tipoDesconto = $('#boxDesconto .js-select-descontoEm').val();

						if(tipoDesconto=="porcentual") {
							if(unMoney($(this).val().replace('.',','))>100) $(this).val(100);
						}

						atualizarValorDesconto();
					})
				});
			</script>
			<section class="modal" id="boxDesconto" style="display:none;width:50%; height:auto;">

				<header class="modal-header">
					<div class="filtros">
						<h1 class="filtros__titulo">Procedimentos</h1>
						<div class="filtros-acoes">
						</div>
						<?php /*<div class="filter-group filter-group_right">
							<div class="filter-button">
								<a href="javascript:;" class="azul js-btn-aplicarDesconto"><i class="iconify" data-icon="bx-bx-check"></i><span>Aplicar Desconto</span></a>
							</div>
						</div>*/?>
					</div>

					
				</header>

				<article class="modal-conteudo">
					
					<div class="js-procedimentos-descontos">
						<?php /*<a href="javascript:;" class="reg-group js-procedimento-item">
							<div class="reg-data js-descricao">
								<h1 class="js-procedimento">Procedimento</h1>
								<p class="js-regiao">15 - Particular SD</p>
							</div>
							<div class="js-valor">
								R$2.000,00
							</div>
							<div class="js-profissional">
								
							</div>
						</a>*/?>
					</div>



					</table>
					<form class="formulario">
						<div class="colunas4">

							<dl>
								<dt>Desconto em</dt>
								<dd>
									<select class="js-select-descontoEm">
										<option value="dinheiro">Dinheiro</option>
										<option value="porcentual">Porcentagem</option>
									</select>
								</dd>
							</dl>

							<dl>
								<dt>&nbsp;</dt>
								<dd><input type="text" class="js-input-desconto" /></dd>
							</dl>

							<dl>
								<dt>&nbsp;</dt>
								<dd class="filter-button">
									<a href="javascript:;" class="azul js-btn-aplicarDesconto"><i class="iconify" data-icon="bx-bx-check"></i><span>Aplicar Desconto</span></a>
								</dd>
							</dl>

							<dl>
								<dt>&nbsp;</dt>
								<dd class="filter-button">
									<a href="javascript:;" class="js-btn-removerDesconto" style="background: var(--vermelho);color:#FFF;"><i class="iconify" data-icon="bx-bx-trash"></i><span>Remover Desconto</span></a>
								</dd>
							</dl>
						</div>

						<div class="colunas4">
							<dl>
								<dt>Valor dos Procedimentos</dt>
								<dd class="js-valorProcedimentos"></dd>
							</dl>
							<dl>
								<dt>Desconto</dt>
								<dd class="js-valorDesconto"></dd>
							</dl>
							<dl>
								<dt>Desconto Aplicados</dt>
								<dd class="js-valorDescontoAplicados"></dd>
							</dl>
							<dl>
								<dt>Total com desconto</dt>
								<dd class="js-valorComDesconto"></dd>
							</dl>
						</div>
					</form>
					

				</article>
			</section>



			<section class="modal" id="boxObs" style="display:none;width:50%; height:auto;">

				<header class="modal-header">
					<div class="filtros">
						<h1 class="filtros__titulo">Observações</h1>
						<div class="filtros-acoes">
							<a href="javascript:;" class="principal js-boxObs-salvar"><i class="iconify" data-icon="bx-bx-check"></i></a>
						</div>
					</div>
				</header>

				<article class="modal-conteudo">
					<input type="hidden" class="js-boxObs-index" />
					<dl>
						<dt>Observação</dt>
						<dd>
							<textarea class="js-boxObs-obs" rows="4"></textarea>
						</dd>
					</dl>
				</article>
			</section>
			
			<section id="modalProcedimento" class="modal" style="width:950px;height:auto;padding-top:20px;">
				
				<header class="modal-conteudo">
						<form method="post" class="form js-form-agendamento">
					<fieldset>
						<legend>Adicionar Procedimento</legend>
							
							<dl class="dl3">
								<dt>Procedimento</dt>
								<dd>
									<select class="js-id_procedimento">
										<?php
										foreach($_procedimentos as $p) {
											echo '<option value="'.$p->id.'" data-id_regiao="'.$p->id_regiao.'" data-regiao="'.(isset($_regioes[$p->id_regiao])?utf8_encode($_regioes[$p->id_regiao]->titulo):"-").'" data-quantitativo="'.($p->quantitativo==1?1:0).'">'.utf8_encode($p->titulo).'</option>';
										}
										?>
									</select>
								</dd>
							</dl>
							<dl style="display: none">
								<dt>Qtd.</dt>
								<dd><input type="number" class="js-inpt-quantidade" value="1" /></dd>
							</dl>
							<dl class="js-regiao-2 js-regiao dl2" style="display: none;">
								<dt>Arcada(s)</dt>
								<dd>
									<select class="js-regiao-2-select" multiple>
										<?php
										if(isset($_regioesOpcoes[2])) {
											foreach($_regioesOpcoes[2] as $o) {
												echo '<option value="'.$o->id.'" data-titulo="'.utf8_encode($o->titulo).'">'.utf8_encode($o->titulo).'</option>';
											}
										}
										?>
									</select>
								</dd>
							</dl>
							<dl class="js-regiao-3 js-regiao dl2" style="display: none">
								<dt>Quadrante(s)</dt>
								<dd>
									<select class="js-regiao-3-select" multiple>
										<?php
										if(isset($_regioesOpcoes[3])) {
											foreach($_regioesOpcoes[3] as $o) {
												echo '<option value="'.$o->id.'" data-titulo="'.utf8_encode($o->titulo).'">'.utf8_encode($o->titulo).'</option>';
											}
										}
										?>
									</select>
								</dd>
							</dl>
							<dl class="js-regiao-4 js-regiao dl2" style="display: none">
								<dt>Dente(s)</dt>
								<dd>
									<select class="js-regiao-4-select" multiple>
										<?php
										if(isset($_regioesOpcoes[4])) {
											foreach($_regioesOpcoes[4] as $o) {
												echo '<option value="'.$o->id.'" data-titulo="'.utf8_encode($o->titulo).'">'.utf8_encode($o->titulo).'</option>';
											}
										}
										?>
									</select>
								</dd>
							</dl>

							<div class="colunas4">
								
								<dl class="dl2">
									<dt>Plano</dt>
									<dd>
										<select class="js-id_plano">
										</select>
									</dd>
								</dl>


								<?php /*<dl class="dl2">
									<dt>Profissional</dt>
									<dd>
										<select class="js-id_profissional">
											<option value="" data-iniciais="" data-iniciaisCor=""></option>
											<?php
											foreach($_profissionais as $x) {
												$iniciais=$x->calendario_iniciais;
												echo '<option value="'.$x->id.'" data-iniciais="'.$iniciais.'" data-iniciaisCor="'.$x->calendario_cor.'">'.utf8_encode($x->nome).'</option>';
											}
											?>
										</select>
									</dd>
								</dl>*/?>
							</div>

								<dl>
									<dt>Observações</dt>
									<dd>
										<textarea class="js-procedimento-obs" style="height: 120px;"></textarea>
									</dd>
								</dl>

								<a href="javascript:;" class="button js-btn-add"><i class="iconify" data-icon="ic-baseline-add"></i> Adicionar</a>
							
					
						</fieldset>

					</form>
				</header>
			</section>

		<?php
		} else {
			$where="WHERE id_paciente=$paciente->id and lixo=0";
			$sql->consult($_table,"*",$where);

			$registros=array();
			$tratamentosIDs=array(0);
			while($x=mysqli_fetch_object($sql->mysqry)) {
				$registros[]=$x;
				$tratamentosIDs[]=$x->id;
			}

			$_procedimentosAprovado=array();
			$procedimentosIds=$tratamentosProcedimentosIDs=array(-1);
			$sql->consult($_table."_procedimentos","*","where id_tratamento IN (".implode(",",$tratamentosIDs).") and situacao='aprovado' and lixo=0");
			while($x=mysqli_fetch_object($sql->mysqry)) {
				$tratamentosProcedimentosIDs[]=$x->id;
				$_procedimentosAprovado[$x->id]=$x;
			}

			$procedimentosIds=array(0);
			$sql->consult($_p."pacientes_tratamentos_procedimentos_evolucao","*","where id_tratamento_procedimento IN (".implode(",",$tratamentosProcedimentosIDs).") and lixo=0");

			while($x=mysqli_fetch_object($sql->mysqry)) {
				if(isset($_procedimentosAprovado[$x->id_tratamento_procedimento])) {
					$p=$_procedimentosAprovado[$x->id_tratamento_procedimento];
					//echo $x->id_tratamento_procedimento."<BR>";
					if($x->status_evolucao=="finalizado") {
						$_procedimentosFinalizados[$p->id_tratamento][]=$x;
					} 
					$_todosProcedimentos[$p->id_tratamento][]=$x;
					$procedimentosIds[]=$x->id_procedimento;
				}
			}
			$_procedimentos=array();
			$sql->consult($_p."parametros_procedimentos","*","where id IN (".implode(",",$procedimentosIds).")");
			while($x=mysqli_fetch_object($sql->mysqry)) {
				$_procedimentos[$x->id]=$x;
			}


			$sql->consult($_table."_pagamentos","*","where id_tratamento IN (".implode(",",$tratamentosIDs).") and id_fusao=0 and lixo=0");
			$pagRegs=array();
			$pagamentosIds=array(0);
			while($x=mysqli_fetch_object($sql->mysqry)) {
				$pagamentosIds[]=$x->id;
				$pagRegs[]=$x;
			}

			$_baixas=array();
			$sql->consult($_p."pacientes_tratamentos_pagamentos_baixas","*","where id_pagamento IN (".implode(",",$pagamentosIds).") and lixo=0");
			while($x=mysqli_fetch_object($sql->mysqry)) {
				$_baixas[$x->id_pagamento][]=$x;
			}


			$_pagamentos=array();
			foreach($pagRegs as $x) {

				// se possui baixa
				if(isset($_baixas[$x->id])) {

					$valorTotal=$x->valor;
					$valorBaixas=0;
					foreach($_baixas[$x->id] as $b) {
						$_pagamentos[$x->id_tratamento][]=array('pago'=>$b->pago,
																'tipo'=>'baixa',
																'valor'=>$b->valor);
						$valorBaixas+=$b->valor;
					}

					// restante que falta dar baixa
					if($valorTotal>$valorBaixas) {
						$_pagamentos[$x->id_tratamento][]=array('pago'=>0,
																'tipos'=>'restante',
																'valor'=>$valorTotal-$valorBaixas);

					}

				} else {

					$_pagamentos[$x->id_tratamento][]=array('pago'=>$x->pago,
															'tipo'=>'parcela '.$x->id,
															'valor'=>$x->valor);
					
				}
			}

		?>

		<section class="grid">
			<div class="box">
				<div class="filter">
					<div class="filter-group">
						<div class="filter-button">
							<a href="<?php echo $_page."?form=1&$url";?>" class="verde"><i class="iconify" data-icon="bx-bx-plus"></i><span>Novo Tratamento</span></a>
						</div>
					</div>
				</div>

				<?php /*<div class="filtros">
					<h1 class="filtros__titulo">Tratamento</h1>
					<div class="filtros-acoes">
						<a href="<?php echo $_page."?form=1&$url";?>" data-padding="0" class="adicionar tooltip" title="Adicionar">Adicionar Tratamento</a>
					</div>
				</div>*/?>
				<div class="registros2">
					<?php
					foreach($registros as $x) {
						$cor="orange";
						if($x->status=="CANCELADO") $cor="red";
						else if($x->status=="APROVADO") $cor="green";

						$procedimentos=array();
						if(isset($_procedimentos[$x->id])) $procedimentos=$_procedimentos[$x->id];

						$pagamentos=array();
						if(isset($_pagamentos[$x->id])) $pagamentos=$_pagamentos[$x->id];
					?>
					<div class="js-procedimentos">
						<a href="<?php echo "$_page?form=1&edita=$x->id&$url";?>" class="reg-group js-procedimento-item"><?php /* style="border-left:solid 10px <?php echo $cor;?>;">*/?>
							<div class="reg-data js-descricao" style="width:58%;">
								<h1 class="js-procedimento"><strong><?php echo utf8_encode($x->titulo);?></strong></h1>
								<p class="js-regiao"><?php echo date('d/m/Y H:i',strtotime($x->data));?></p>
							</div>
							<div class="reg-steps" style="margin:0 auto;">

								<?php
								if($x->status=="PENDENTE") {
								?>
								<div class="reg-steps__item active">
									<h1 style="color:var(--laranja);">1</h1>
									<p>Aguardando Aprovação</p>									
								</div>

								<div class="reg-steps__item active">
									<h1  style="color:#ccc;">2</h1>
									<p>Aprovado/Reprovado</p>									
								</div>
								<?php
								} else if($x->status=="APROVADO") {
								?>
								<div class="reg-steps__item active">
									<h1 style="color:var(--verde);">1</h1>
									<p>Aguardando Aprovação</p>									
								</div>

								<div class="reg-steps__item active">
									<h1  style="color:var(--verde);">2</h1>
									<p>Aprovado</p>									
								</div>
								<?php
								}  else if($x->status=="CANCELADO") {
								?>
								<div class="reg-steps__item active">
									<h1 style="color:var(--verde);">1</h1>
									<p>Aguardando Aprovação</p>									
								</div>

								<div class="reg-steps__item active">
									<h1  style="color:var(--vermelho);">2</h1>
									<p>Reprovado</p>									
								</div>
								<?php
								}
								?>
								
								
							</div>				

							<div class="js-valor" style="width:20%;margin-right: 120px;">
								<?php
								if($x->id_aprovado==0) {
									echo "-";
								} else {
								
										$pagamentos=array();
										if(isset($_pagamentos[$x->id])) $pagamentos=$_pagamentos[$x->id];

										$procedimentos=array();
										if(isset($_procedimentos[$x->id])) $procedimentos=$_procedimentos[$x->id];

										$total=isset($_todosProcedimentos[$x->id])?count($_todosProcedimentos[$x->id]):0;
										$finalizados=isset($_procedimentosFinalizados[$x->id])?count($_procedimentosFinalizados[$x->id]):0;
										$perc=($total)==0?0:number_format(($finalizados/($total))*100,0,"","");



										$pagPago=$pagTotal=0;
										foreach($pagamentos as $p) { 
											$p=(object)$p;
											if($p->pago==1) $pagPago+=$p->valor;

											$pagTotal+=$p->valor;
										}
										$percPag=($pagTotal)==0?0:number_format(($pagPago/($pagTotal))*100,0,"","");

									?>
									<div class="reg-bar" style="flex:0 1 10px;">
										<p>Evolução<br /><span style="color: var(--cinza3);font-size:12px;"><?php echo "Realizado <b>".$finalizados."</b> de <b>".$total."</b> - ".$perc."%";?></span></p>
										<div class="reg-bar__container">
											<span style="width:<?php echo $perc;?>%">&nbsp;</span>
										</div>
									</div>
									<?php
									
								}
								?>
							</div>
							<div class="js-valor" style="width:20%;">
								<?php
								if($x->id_aprovado==0) {
									echo "-";
								} else {
									if(count($pagamentos)==0) echo '';//<a href="javascript" class="tooltip" title="Nenhum pagamento foi adicionado"><span class="iconify" data-icon="eva:alert-triangle-fill" data-inline="false" data-height="25"></span></a>';
									else {
										
									?>
									<div class="reg-bar" style="flex:0 1 120px;">
										<p>Pagamento<br /><span style="color: var(--cinza3);font-size:12px;"><?php echo "Recebido <b>".number_format($pagPago,2,",",".")."</b> de <b>".number_format($pagTotal,2,",",".")."</b> - ".$percPag."%";?></span></p>
										<div class="reg-bar__container"><span style="width:<?php echo $percPag;?>%">&nbsp;</span></div>
									</div>
									<?php
									}
								}
								?>
							</div>

						</a>
					</div>
					<?php
					}
					?>
				</div>
				
				<?php
				}
				?>
			</div>
		</section>		
	</section>

<?php
	include "includes/footer.php";
?>