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
			$unidade='';
			if(isset($_POST['id_unidade']) and is_numeric($_POST['id_unidade']) and isset($_optUnidades[$_POST['id_unidade']])) {
				$unidade=$_optUnidades[$_POST['id_unidade']];
			}

			if(is_object($procedimento) and is_object($unidade)) {
				$sql->consult($_p."parametros_procedimentos_planos","*","where id_procedimento=$procedimento->id and 
																				id_unidade='".$unidade->id."'"); 
				
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
		$optionFormasDePagamento.='<option value="'.$x->id.'">'.utf8_encode($x->titulo).'</option>';
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
	$sql->consult($_p."profissionais","*","where lixo=0 order by nome asc");//"where unidades like '%,$unidade->id,%' and lixo=0 order by nome asc");
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
	$_selectSituacaoOptions=array('aguardandoAprovacao'=>array('titulo'=>'AGUARDANDO APROVAÇÃO','cor'=>'blue'),
											'aprovado'=>array('titulo'=>'APROVADO','cor'=>'green'),
											'naoAprovado'=>array('titulo'=>'NÃO APROVADO','cor'=>'red'),
											'observado'=>array('titulo'=>'OBSERVADO','cor'=>'orange'),
											//'cancelado'=>array('titulo'=>'CANCELADO');
										);

	$selectSituacaoOptions='<select class="js-situacao">';
	foreach($_selectSituacaoOptions as $key=>$value) {
		$selectSituacaoOptions.='<option value="'.$key.'">'.$value['titulo'].'</option>';
	}
	$selectSituacaoOptions.='</select>';

	$selectProfissional='<select class="js-profissional"><option value="">-</option>';
	foreach($_profissionais as $p) {
	
		$aux=explode(" ",$p->nome);
		$aux[0]=strtoupper($aux[0]);
		$iniciais='';
		if($aux[0] =="DR" or $aux[0]=="DR." or $aux[0]=="DRA" or $aux[0]=="DRA.") {
			$iniciais=strtoupper(substr($aux[1],0,1));
			if(isset($aux[2])) $iniciais.=strtoupper(substr($aux[2],0,1));
		} else {
			$iniciais=strtoupper(substr($aux[0],0,1));
			if(isset($aux[1])) $iniciais.=strtoupper(substr($aux[1],0,1));
		}
											
		$selectProfissional.='<option value="'.$p->id.'" data-iniciais="'.$iniciais.'" data-iniciaisCor="'.$p->calendario_cor.'">'.utf8_encode($p->nome).'</option>';
	}
	$selectProfissional.='</select>';

	$planosDosProcedimentos=array();
	$sql->consult($_p."parametros_procedimentos_planos","*","where id_unidade='".$usrUnidade->id."' and lixo=0");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$planosDosProcedimentos[$x->id_procedimento][]=array("id"=>$x->id_plano,"titulo"=>utf8_encode($_planos[$x->id_plano]->titulo));
	}

	$campos=explode(",","titulo");
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

			$campos=explode(",","titulo");
			
			foreach($campos as $v) $values[$v]='';
			$values['procedimentos']="[]";
			$values['pagamentos']="[]";

			$sql->consult($_table,"id","where lixo=0");
			$values['titulo']="Plano de tratamento ".($sql->rows+1);

			$cnt='';
			if(isset($_GET['edita']) and is_numeric($_GET['edita'])) {
				$sql->consult($_table,"*","where id='".$_GET['edita']."' and lixo=0");
				if($sql->rows) {
					$cnt=mysqli_fetch_object($sql->mysqry);
					$values=$adm->values($campos,$cnt);

					// Procedimentos
						$procedimentos=array();
						$where="where id_tratamento=$cnt->id and id_paciente=$paciente->id and id_unidade=$usrUnidade->id and lixo=0";
						$sql->consult($_table."_procedimentos","*",$where);
						while($x=mysqli_fetch_object($sql->mysqry)) {

							$profissional=isset($_profissionais[$x->id_profissional])?$_profissionais[$x->id_profissional]:'';
							$iniciaisCor='';
							$iniciais='?';
							if(is_object($profissional)) {
								$aux=explode(" ",$profissional->nome);
								$aux[0]=strtoupper($aux[0]);
								$iniciais='';
								if($aux[0] =="DR" or $aux[0]=="DR." or $aux[0]=="DRA" or $aux[0]=="DRA.") {
									$iniciais=strtoupper(substr($aux[1],0,1));
									if(isset($aux[2])) $iniciais.=strtoupper(substr($aux[2],0,1));
								} else {
									$iniciais=strtoupper(substr($aux[0],0,1));
									if(isset($aux[1])) $iniciais.=strtoupper(substr($aux[1],0,1));
								}

								$iniciaisCor=$profissional->calendario_cor;
							}

							$procedimentos[]=array('id'=>$x->id,
													'id_procedimento'=>(int)$x->id_procedimento,
													'procedimento'=>utf8_encode($x->procedimento),
													'id_profissional'=>(int)$x->id_profissional,
													'profissional'=>utf8_encode($x->profissional),
													'id_plano'=>(int)$x->id_plano,
													'plano'=>utf8_encode($x->plano),
													'quantitativo'=>(int)$x->quantitativo,
													'quantidade'=>(int)$x->quantidade,
													'id_opcao'=>(int)$x->id_opcao,
													'opcao'=>utf8_encode($x->opcao),
													'valorCorrigido'=>(float)$x->valor,
													'valor'=>(float)$x->valorSemDesconto,
													'desconto'=>(float)$x->desconto,
													'obs'=>utf8_encode($x->obs),
													'situacao'=>$x->situacao,
													'iniciais'=>$iniciais,
													'iniciaisCor'=>$iniciaisCor);
						}
						if($cnt->status=="APROVADO") {
							$values['procedimentos']=json_encode($procedimentos);
						} else {
							$values['procedimentos']=empty($cnt->procedimentos)?"[]":utf8_encode($cnt->procedimentos);
						}

					// Pagamentos
						$pagamentos=array();
						$where="where id_tratamento=$cnt->id and id_paciente=$paciente->id and id_unidade=$usrUnidade->id and lixo=0";
						$sql->consult($_table."_pagamentos","*",$where);
						while($x=mysqli_fetch_object($sql->mysqry)) {
							$pagamentos[]=array('id'=>$x->id,
													//'id_formapagamento'=>(int)$x->id_formapagamento,
													'vencimento'=>date('d/m/Y',strtotime($x->data_vencimento)),
													'valor'=>(float)$x->valor);
						}

						if($cnt->status=="APROVADO") {
							$values['pagamentos']=json_encode($pagamentos);
						} else {
							$values['pagamentos']=empty($cnt->pagamentos)?"[]":utf8_encode($cnt->pagamentos);
						}


				} else {
					$jsc->jAlert("Plano de Tratamento não encontrado!","erro","document.location.href='$_page?$url'");
					die();
				}
			}

			$tratamentoAprovado=(is_object($cnt) and $cnt->status=="APROVADO")?true:false;
			

			if(isset($_POST['acao'])) {


				// persiste as informacoes do tratamento
				if($_POST['acao']=="salvar") {
					$vSQL=$adm->vSQL($campos,$_POST);
					$values=$adm->values;

					if($tratamentoAprovado===false) {
						$vSQL.="procedimentos='".addslashes(utf8_decode($_POST['procedimentos']))."',";
						$vSQL.="pagamentos='".addslashes(utf8_decode($_POST['pagamentos']))."',";
					}

					if(isset($_POST['parcelas']) and is_numeric($_POST['parcelas'])) $vSQL.="parcelas='".$_POST['parcelas']."',";
					if(isset($_POST['pagamento'])) $vSQL.="pagamento='".$_POST['pagamento']."',";
				
					if(is_object($cnt)) {
						$vSQL=substr($vSQL,0,strlen($vSQL)-1);
						$vWHERE="where id='".$cnt->id."'";
						$sql->update($_table,$vSQL,$vWHERE);
						$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='update',vsql='".addslashes($vSQL)."',vwhere='".addslashes($vWHERE)."',tabela='".$_table."',id_reg='".$cnt->id."'");
						$id_tratamento=$cnt->id;

						if($tratamentoAprovado===false) {
							$sql->update($_table."_procedimentos","lixo=1","where id_tratamento=$id_tratamento and id_paciente=$paciente->id and id_unidade=$usrUnidade->id");
							$sql->update($_table."_pagamentos","lixo=1","where id_tratamento=$id_tratamento and id_paciente=$paciente->id and id_unidade=$usrUnidade->id");
						}
					} else {
						$vSQL.="data=now(),id_paciente=$paciente->id";
						//echo $vSQL;die();
						$sql->add($_table,$vSQL);
						$id_tratamento=$sql->ulid;
						$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='insert',vsql='".addslashes($vSQL)."',tabela='".$_table."',id_reg='".$id_tratamento."'");
					}

					
				

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
													break;
												}
												if($x->id_profissional==0 or empty($x->id_profissional)) {
													$erro='Para aprovar o tratamento, é necessário selecionar o Profissional para todos os procedimentos';
													break;
												}
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

										if(!(abs($valorPagamento - $valorProcedimento) < 0.00000001)) {
											$erro="Defina as parcelas de pagamento!";
										} 
									}

								


								if(empty($erro)) {
									if($cnt->status=="PENDENTE" or $cnt->status=="CANCELADO") {
										$sql->update($_table,"status='APROVADO',id_aprovado=$usr->id,data_aprovado=now()","where id=$cnt->id");
										$msgOk="Plano de Tratamento foi <b>APROVADO</b> com sucesso!";
									} else {
										$erro="Este tratamento já está APROVADO";
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



											$sql->update($_table."_procedimentos","lixo=1","where id_tratamento=$cnt->id");
											$sql->update($_table."_pagamentos","lixo=1","where id_tratamento=$cnt->id or id_fusao IN (".implode(",", $pagamentosFusaoIds).")");
											$sql->update($_table,"pagamentos=''","where id=$cnt->id");

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



										$sql->update($_table."_procedimentos","lixo=1","where id_tratamento=$cnt->id");
										$sql->update($_table."_pagamentos","lixo=1","where id_tratamento=$cnt->id or id_fusao IN (".implode(",", $pagamentosFusaoIds).")");
										$sql->update($_table,"pagamentos=''","where id=$cnt->id");
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
										foreach($pagamentosJSON as $x) {
											

											$vSQLPagamento="lixo=0,
															id_paciente=$paciente->id,
															id_tratamento=$id_tratamento,
															id_unidade=$usrUnidade->id,
									
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
										} 
									}
								}

							// Procedimentos
								if(isset($_POST['procedimentos'])  and !empty($_POST['procedimentos'])) {
									
									$procedimetosJSON=!empty($_POST['procedimentos'])?json_decode($_POST['procedimentos']):array();
									//echo json_encode($procedimetosJSON);die();
									if(is_array($procedimetosJSON)){ 
										foreach($procedimetosJSON as $x) {
											

											$vSQLProcedimento="lixo=0,
																id_paciente=$paciente->id,
																id_tratamento=$id_tratamento,
																id_unidade=$usrUnidade->id,
																id_procedimento='".addslashes($x->id_procedimento)."',
																procedimento='".addslashes(utf8_decode($x->procedimento))."',
																id_plano='".addslashes($x->id_plano)."',
																plano='".addslashes(utf8_decode($x->plano))."',
																id_profissional='".addslashes($x->id_profissional)."',
																profissional='".addslashes(utf8_decode($x->profissional))."',
																situacao='".addslashes($x->situacao)."',
																valor='".addslashes($x->valorCorrigido)."',
																desconto='".addslashes($x->desconto)."',
																valorSemDesconto='".addslashes($x->valor)."',
																quantitativo='".addslashes($x->quantitativo)."',
																quantidade='".addslashes($x->quantidade)."',
																id_opcao='".addslashes($x->id_opcao)."',
																obs='".addslashes(utf8_decode($x->obs))."',
																opcao='".addslashes(utf8_decode($x->opcao))."',";
											//echo $vSQLProcedimento."<BR>";
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
										}
									}
								}
						}

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
					$jsc->jAlert("Informações salvas com sucesso!","sucesso","document.location.href='".$_page."?form=1&edita=$id_tratamento&id_paciente=$paciente->id'");
					die();
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
						
						<dl style="grid-column:span 2;">
							<dt>Profissional</dt>
							<dd><?php echo $selectProfissional;?></dd>
						</dl>

						<dl>
							<dt>Valor Tabela</dt>
								<dd><input type="text" class="js-valorTabela money" /></dd>
							</dl>
							<dl>
								<dt>Valor Desconto</dt>
								<dd><input type="text" class="js-valorDeDesconto money" /></dd>
							</dl>
							<dl>
								<dt>Valor Corrigido</dt>
								<dd><input type="text" class='js-valorCorrigido money' /></dd>
							</dl>
							<dl>
								
								<dd style="padding-top: 10px"><button type="button" class="js-btn-descontoAplicartEmTodos button">aplicar em todos</button></dd>
							</dl>

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
							

						})
					</script>
					<?php /*<div class="paciente-info-grid js-grid js-grid-valor" style="font-size: 12px;display:none;">
						

							
							
					</div>*/?>


					<div class="paciente-info-grid js-grid js-grid-obs" style="display:none;font-size:12px;color:#666">	
						<dl style="grid-column:span 2;">
							<dd>
								<textarea style="height:100px" class="js-obs"></textarea>
							</dd>
						</dl>
					</div>
					<div class="paciente-info-opcoes">
						<?php echo $selectSituacaoOptions;?>
						<a href="javascript:;" target="_blank" class="js-btn-excluir button button__sec">excluir</a>
					</div>
				</section>
    		</section>

			<script type="text/javascript">

				var tratamentoAprovado = <?php echo ($tratamentoAprovado===true)?1:0;?>;
				var procedimentos = [];
				var id_unidade = '<?php echo $usrUnidade->id;?>';
				var planosDosProcedimentos = JSON.parse(`<?php echo json_encode($planosDosProcedimentos);;?>`);
				var pagamentos = [];
				var valorTotal = 0;
				var valorPagamento = 0;
				var valorSaldo = 0;

				const desativarCampos = () => {
					if(tratamentoAprovado===1) { 
						$('.js-pagamento-item').find('select:not(.js-profissional),input').prop('disabled',true);
						$('#cal-popup').find('select:not(.js-profissional),input').prop('disabled',true);
						$('#cal-popup').find('.js-btn-excluir,.js-btn-descontoAplicartEmTodos').hide();
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
					const atualizaValor = () => { 
						valorTotal=0;

						let reprovarAtivo=true;
						procedimentos.forEach(x=> {
							if(x.valorCorrigido!==undefined || x.valor!==undefined) {
								if(x.situacao!='naoAprovado' && x.situacao!='observado') {
									if(x.valorCorrigido) valorTotal+=$.isNumeric(x.valorCorrigido)?eval(x.valorCorrigido):unMoney(x.valorCorrigido);
									else valorTotal+=$.isNumeric(x.valor)?eval(x.valor):unMoney(x.valor);
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
								item.vencimento='<?php echo date('d/m/Y');?>'
								item.valor=valorTotal;

								parcelas.push(item);

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

								if(numeroParcelas.length==0 || numeroParcelas<=0) numeroParcelas=2;
								
								valorParcela=valorTotal/numeroParcelas;

								let startDate = new Date();
								for(var i=1;i<=numeroParcelas;i++) {

									/*val = -1;
									if($(`.js-pagamentos .js-valor:eq(${i})`).length) {
										val = $(`.js-pagamentos .js-valor:eq(${(i-1)})`).val();
									}
									console.log(`${$(`.js-pagamentos .js-valor:eq(${i})`).length} -> .js-pagamentos .js-valor:eq(${(i-1)}) => ${val}`);*/

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

							
							 pagamentos=parcelas;
						}
						
						pagamentosListar();

						$('.js-valorTotal').html(number_format(valorTotal,2,",","."));

					}

				// PROCEDIMENTOS
					/*var procedimentosHMTL = `<tr class="js-procedimento-item">
											<td class="js-procedimento"></td>
											<td class="js-regiao"></td>
											<td><?php echo $selectProfissional;?></td>
											<td class="js-td-plano"></td>
											<td><input type="text" value="" style="width:80px;" class="js-valor" /></td>
											<td><?php echo $selectSituacaoOptions;?></td>	
											<td class="js-td-obs"  style="font-size:1.25rem;" ></td>											
											<td>${(tratamentoAprovado===1)?'':'<a href="javascript:;" style="font-size:1.25rem;" class="js-btn-removerProcedimento"><i class="iconify" data-icon="bx-bx-trash"></i></a>'}</td>
										</tr>`;*/

					var procedimentosHMTL = `<div class="card js-procedimento-item">
												<div class="js-descricao">
													<h1 class="js-procedimento"></h1>
													<h2 class="js-regiao"></h2>
												</div>
												<div class="js-valor">
													R$2.000,00
												</div>
												<div class="js-profissional">
													
												</div>
											</div>`;


					const procedimentosListar = () => {
						$('.js-procedimentos .js-procedimento-item').remove();
						if(procedimentos.length>0) {
							let index=0;
							let total = 0;
							procedimentos.forEach(x=> {
								popViewInfos[index] = x;

								btnExcluir='';
								if(tratamentoAprovado===1 && x.situacao=="aprovado") btnExcluir='Ex';

								let corSituacao="blue";
								if(x.situacao=="aprovado") corSituacao="green";
								else if(x.situacao=="naoAprovado") corSituacao="red";
								else if(x.situacao=="observado") corSituacao="orange";

								if(eval(x.id_profissional)>0) {
									iniciais=`<div class="cal-item-foto"><span style="background:${x.iniciaisCor}">${x.iniciais}</span></div>`;
								} else {
									iniciais=`<div class="cal-item-foto"><span style=""><span class="iconify" data-icon="bi:person-fill" data-inline="false"></span></span></div>`
								}

								$(`.js-procedimentos`).append(procedimentosHMTL);
								$(`.js-procedimentos .js-procedimento-item:last`).attr('data-situacao',x.situacao);
								$(`.js-procedimentos .js-procedimento-item:last`).css('border-left',`solid 10px ${corSituacao}`)
								$(`.js-procedimentos .js-procedimento-item:last`).click(function(){popView(this);})
								$(`.js-procedimentos .js-procedimento:last`).html(x.procedimento);
								$(`.js-procedimentos .js-regiao:last`).html(x.quantitativo==1?x.quantidade:x.opcao);
								$(`.js-procedimentos .js-regiao:last`).append(` - ${x.plano}`);
								$(`.js-procedimentos .js-valor:last`).html(number_format(x.valorCorrigido?x.valorCorrigido:x.valor,2,",","."));
								$(`.js-procedimentos .js-profissional:last`).html(iniciais);
								index++;
								total+=x.valorCorrigido?x.valorCorrigido:x.valor;
							});

							$('.js-valorTotal').html(total);

							atualizaValor();
							desativarCampos();
						}
						console.log(procedimentos);
						$('textarea.js-json-procedimentos').val(JSON.stringify(procedimentos));
						
					}

					const procedimentosRemover = (index) => {
						procedimentos.splice(index,1);
						procedimentosListar();
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

						console.log(popViewInfos[index]);
						if(popViewInfos[index].opcao.length>0) {
							$('#cal-popup .js-opcaoEQtd').html(`Região: ${popViewInfos[index].opcao}`);
						} else {
							$('#cal-popup .js-opcaoEQtd').html(`Quantidade: ${popViewInfos[index].quantidade}`);
						}

						$('#cal-popup .js-titulo').html(popViewInfos[index].procedimento);
						$('#cal-popup .js-plano').html(popViewInfos[index].plano);
						$('#cal-popup .js-profissional').val(popViewInfos[index].id_profissional);
						$('#cal-popup .js-valorDeDesconto').val(number_format(popViewInfos[index].desconto,2,",","."))
						$('#cal-popup .js-valorTabela').val(number_format(popViewInfos[index].valor,2,",","."));
						$('#cal-popup .js-valorCorrigido').val(number_format(popViewInfos[index].valor-popViewInfos[index].desconto,2,",","."))
						$('#cal-popup .js-valorDeDesconto').trigger('change');
						$('#cal-popup .js-obs').val(popViewInfos[index].obs);

						//$('#cal-popup .js-btn-descontoAplicartEmTodos').prop('checked',popViewInfos[index].descontoAplicartEmTodos==1?true:false)

						$('#cal-popup .js-situacao').val(popViewInfos[index].situacao);
						$('#cal-popup .js-index').val(index);
						//	atualizaValor();	
						
					}

				// PAGAMENTOS
					var pagamentosHTML = `<tr class="js-pagamento-item">
												<td class="js-num"></td>
												<td><input type="text" name="" class="datepicker data js-vencimento" value="" /></td>
												<td><input type="text" name="" value="" class="js-valor" /></td>
												
											<?php /*	<td>${tratamentoAprovado===1?'':'<a href="javascript:;" style="font-size:1.25rem;" class="js-btn-removerPagamento"><i class="iconify" data-icon="bx-bx-trash"></i></a>'}</td>*/?>
											</tr>`;


					const pagamentosListar = () => {
						$('.js-pagamentos .js-pagamento-item').remove();
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
							});
						}


						$('textarea.js-json-pagamentos').val(JSON.stringify(pagamentos))
						//atualizaValor();
						desativarCampos();
					}

					const pagamentosRemover = (index) => {
						pagamentos.splice(index,1);
						pagamentosListar();
					}
					$(document).mouseup(function(e)  {
						    var container = $("#cal-popup");
						    // if the target of the click isn't the container nor a descendant of the container
						    if (!container.is(e.target) && container.has(e.target).length === 0) 
						    {
						       $('#cal-popup').hide();
						    }
						});
				
				$(function(){
					pagamentos=JSON.parse($('textarea.js-json-pagamentos').val());

					$('.js-pagamentos').on('keyup','.js-valor',function(){
						let index = $(this).index('.js-pagamentos .js-valor');
						let numeroParcelas = eval($('.js-pagamentos-quantidade').val());
						let valorTotalAux = valorTotal;
						let valorAcumulado = 0;
						let parcelas = [];
						let val = unMoney($(this).val());



						for(i=0;i<=index;i++) {
							val = unMoney($(`.js-pagamentos .js-valor:eq(${i})`).val());
							valorAcumulado += val;
							let item = {};
							item.vencimento=pagamentos[i].vencimento;
							item.valor=val;
							parcelas.push(item);
						}

						let valorRestante = valorTotal-valorAcumulado;

						if(valorAcumulado>valorTotal) {

							swal({title: "Erro!", text: 'Os valores das parcelas não podem superar o valor total', html:true, type:"error", confirmButtonColor: "#424242"});
						} else {


							numeroParcelasRestantes = numeroParcelas - (index+1);
							valorParcela=valorRestante/numeroParcelasRestantes;

							for(i=(index+1);i<numeroParcelas;i++) {
								let item = {};
								item.vencimento=pagamentos[i].vencimento;
								item.valor=valorParcela;
								parcelas.push(item);

							}


							pagamentos=parcelas;
						}

					});

					$('.js-pagamentos').on('blur','.js-valor',function(){
						pagamentosListar();
					});

					$('#cal-popup .js-obs').keyup(function(){
						let index = $('.js-index').val();
						procedimentos[index].obs=$(this).val();
					})

					$('.js-btn-descontoAplicartEmTodos').click(function(){
						let index = $('.js-index').val();
						let count = 0; 
						let id_procedimento = procedimentos[index].id_procedimento;
						procedimentos.forEach(x=>{
							if(count!=index) {

								if(id_procedimento==procedimentos[count].id_procedimento) {
									procedimentos[count].desconto=procedimentos[index].desconto;
									procedimentos[count].valorCorrigido=procedimentos[index].valor-procedimentos[index].desconto;


									$(`.js-procedimentos .js-valor:eq(${count})`).html(number_format(procedimentos[count].valorCorrigido,2,",","."));
								}
							}
							count++;
						});

						procedimentosListar();
						
						atualizaValor();
						//procedimentos[index].descontoAplicartEmTodos=descontoAplicartEmTodos;


					});

					$('.js-btn-fechar').click(function(){
						$('.js-valorDeDesconto').val(0)
						$('.cal-popup').hide();
					})

					$('input[name=pagamento]').click(function(){
						atualizaValor();
					});

					$('.js-pagamentos-quantidade').click(function(){

						let qtd = $(this).val();

						if(!$.isNumeric(eval(qtd))) qtd=2;
						else if(qtd<=1) qtd=2;
						else if(qtd>=36) qtd=36;


						$('.js-pagamentos-quantidade').val(qtd);

						atualizaValor();
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
								$('input[name=acao]').val('salvar');
								$('form.js-form').submit();
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
							let situacao = `aguardandoAprovacao`;
							let obs = ``;
							let id_profissional = $('.js-id_profissional').val();
							let iniciais = $('.js-id_profissional option:selected').attr('data-iniciais');
							let iniciaisCor = $('.js-id_profissional option:selected').attr('data-iniciaisCor');
							//alert(quantitativo);

							let erro = ``;
							if(id_procedimento.length==0) erro=`Selecione o Procedimento`;
							//else if(quantitativo==1 && (quantidade.length==0 || eval(quantidade)<=0 || eval(quantidade)>=99)) erro=`Defina a quantidade<br />(mín: 1, máx: 99)`;
							else if(id_regiao>=2 && $(`.js-regiao-${id_regiao}-select`).val().length==0) erro=`Preencha a Região`
							else if(id_plano.length==0) erro=`Selecione o Plano`;

							if(erro.length==0) {

								let linhas=1;
								if(id_regiao>=2) {
									linhas = eval($(`.js-regiao-${id_regiao}-select`).val().length);
								}

								let item= {};

								
								let opcoes = ``;
								for(var i=0;i<linhas;i++) {
									item = {};
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
									item.valorCorrigido=valor;
									item.descontoAplicartEmTodos=0;
									item.quantitativo=quantitativo;
									item.obs='';
									item.id_profissional=id_profissional;
									item.iniciais=iniciais
									item.iniciaisCor=iniciaisCor;

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
								$(`.js-id_profissional`).val('').trigger('chosen:updated');
								$(`.js-inpt-quantidade`).val(1).parent().parent().hide();
								
								$(`.js-regiao-${id_regiao}-select`).val([]).trigger('chosen:updated').parent().parent().hide();;
								$.fancybox.close();
								procedimentosListar();
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
								$(`.js-regiao-${id_regiao}`).find('select').chosen({hide_results_on_select:false,allow_single_deselect:true});

								$(`.js-procedimento-btnOk`).show();
								let data = `ajax=planos&id_unidade=${id_unidade}&id_procedimento=${id_procedimento}`;
								$.ajax({
									type:"POST",
									data:data,
									success:function(rtn) {
										if(rtn.success) { 
											$('.js-id_plano option').remove();
											$('.js-id_plano').append(`<option value=""></option>`);
											console.log(rtn.planos);
											if(rtn.planos) {

												rtn.planos.forEach(x=> {
													$('.js-id_plano').append(`<option value="${x.id}" data-valor="${x.valor}">${x.titulo}</option>`);
												});
											}
											$('.js-id_plano').trigger('chosen:updated')
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
							procedimentosListar();
						})

						$('#cal-popup').on('keyup','.js-valorTabela,.js-valorDeDesconto',function(){
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
								procedimentosListar();


							});

						$('#cal-popup').on('change','.js-profissional',function(){
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
						});

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
								alert('erro')
							}
						});

						$('.js-btn-addProcedimento').click(function(){
							$.fancybox.open({
								src:'#modalProcedimento'
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
								</div>
								<?php
								if(is_object($cnt)) {
								?>
								<input type="hidden" name="status" />
								<div class="filter-group filter-group_right">
									<div class="filter-links">
										<a href="javascript:;" data-status="PENDENTE" class="js-btn-status<?php echo $cnt->status=="PENDENTE"?" active":"";?>">Em aberto</a>
										<a href="javascript:;" data-status="APROVADO" class="js-btn-status<?php echo $cnt->status=="APROVADO"?" active":"";?>">Aprovado</a>
										<a href="javascript:;" data-status="CANCELADO" class="js-btn-status<?php echo $cnt->status=="CANCELADO"?" active":"";?>">Reprovado</a>
									</div>
								</div>
								<?php
								}
								?>
								<div class="filter-group">
									<div class="filter-data">
										<h1>Valor Total</h1>
										<h2 class="js-valorTotal">0,00</h2>
									</div>					
								</div>	

								<div class="filter-group filter-group_right">
									<div class="filter-button">
										<a href="javascript:;"><i class="iconify" data-icon="bx-bx-trash"></i></a>
										<a href="javascript:;"><i class="iconify" data-icon="bx-bx-printer"></i></a>
										<a href="javascript:;" class="azul js-btn-salvar"><i class="iconify" data-icon="bx-bx-check"></i><span>salvar</span></a>
									</div>
								</div>
							</div>
							<?php /*<div class="filtros" style="flex:0;background:none;">
								<h1 class="filtros__titulo" style="width:500px; max-width:70%;">
									<input type="text" name="titulo" placeholder="Título do tratamento..." value="<?php echo $values['titulo'];?>" style="background:none;border:0; border-radius:0; border-bottom:1px solid var(--cinza2); " />
								</h1>
								<?php
									if(is_object($cnt)) {

									?>
									<input type="hidden" name="status" />
									
									<style type="text/css">
										ul.btns {
										}
										ul.btns li {
											float:left;
											background: var(--cinza2);
											padding:23px;
											border-radius: 31px;
											margin-right: 10px;

										}
										ul.btns li.active {
											background: var(--cinza3)

										}
									</style>
									<ul class="btns">
										<a href="javascript:;" data-status="PENDENTE" class="js-btn-status"><li class="<?php echo $cnt->status=="PENDENTE"?"active":"";?>">Em Aberto</li></a>
										<a href="javascript:;" data-status="APROVADO" class="js-btn-status"><li class="<?php echo $cnt->status=="APROVADO"?"active":"";?>">Aprovado</li></a>
										<a href="javascript:;" data-status="CANCELADO" class="js-btn-status"><li class="<?php echo $cnt->status=="CANCELADO"?"active":"";?>">Reprovado</li></a>
									</ul>
									<?php
										/*if($cnt->status=="PENDENTE") {
									?>
									<a href="javascript:;" data-padding="0" class="principal2 tooltip js-btn-aprovar" title="Aprovar"><i class="iconify" data-icon="bx-bx-check-double"></i> <p>Aprovar tratamento</p></a>

									<a href="javascript:;" data-padding="0" class="principal2 tooltip js-btn-reprovar" title="Reprovar" style="background:var(--vermelho);"><i class="iconify" data-icon="websymbol:cancel" data-height="16"></i> <p>Reprovar tratamento</p></a>
									<?php
										} 
										if($cnt->status=="APROVADO") {

									?>
									<a href="box/boxPacienteTratamentoCancelar.php?id_paciente=<?php echo $paciente->id;?>&id_tratamento=<?php echo $cnt->id;?>&id_unidade=<?php echo $usrUnidade->id;?>" data-fancybox data-type="ajax" data-padding="0" class="tooltip js-btn-reprovar sec" title="Cancelar"><i class="iconify" data-icon="bx-bx-x"></i></a>
									<?php
										}*888
									}
									?>
								<div class="filtros-acoes">
									<a href="javascript:;"><b>Valor Total:</b>&nbsp;<span class="js-valorTotal"></span></a>
									<a href="<?php echo $_page."?".$url;?>"><i class="iconify" data-icon="bx-bx-left-arrow-alt"></i></a>
									<a href="javascript:;" data-padding="0" class="principal tooltip js-btn-salvar" title="Salvar">Salvar</a>

									
								</div>
							</div>*/?>
							
							<div class="grid grid_auto" style="flex:1;">
								<fieldset style="grid-column:span 2; margin:0;">
									
									<legend><span class="badge">1</span> Adicione os Procedimentos</legend>
									<?php
									if($tratamentoAprovado===false) {
									?>
									<?php /*<div class="clearfix" style="margin-bottom: 10px;">
										<a href="javascript:;" class="button js-btn-addProcedimento tooltip " title="Adicionar Procedimento" style="background:var(--azul);color:#FFF;float: right"><i class="iconify" data-icon="ic-baseline-add"></i> Adicionar Procedimento</a>
									</div>*/?>
									<div class="filter">
									<div class="filter-group">
										<div class="filter-button">
											<a href="javascript:;" class="verde js-btn-addProcedimento "><i class="iconify" data-icon="bx-bx-plus"></i><span>Novo Procedimento</span></a>
										</div>
									</div>
								</div>
									<?php
									}
									?>

									<textarea name="procedimentos" class="js-json-procedimentos" style="display:none"><?php echo $values['procedimentos'];?></textarea>
									
									<div class="registros2"><?php /* style="height:<?php echo $tratamentoAprovado==false?"calc(115vh - 570px)":"calc(100vh - 400px)";?>; overflow:auto;">*/?>

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
											<dl class="dl3" style="padding: 0">
												<dd>
													<label><input type="radio" name="pagamento" value="avista" class="js-pagamento-avista"<?php echo (is_object($cnt) and $cnt->pagamento=="avista")?" checked":"";?> /> À Vista</label>
													<label><input type="radio" name="pagamento" value="parcelado" class="js-pagamento-parcelado"<?php echo (is_object($cnt) and $cnt->pagamento=="parcelado")?" checked":"";?> /> Parcelado em</label>
													<input type="number" name="parcelas" style="float:left;width:50px;display: none;" value="<?php echo is_object($cnt)?$cnt->parcelas:1;?>" class="js-pagamentos-quantidade" />
												</dd>
											</dl>
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
									<div class="registros"><?php /* style="height:<?php echo $tratamentoAprovado==false?"calc(115vh - 570px)":"calc(100vh - 400px)";?>; overflow:auto;">*/?>
										<table>
											<thead>

												<tr>
													<th style="width: 10%"></th>
													<th style="width: 35%">Vencto</th>
													<th>Valor</th>
													<?php /*<th></th>*/?>
												</tr>
											</thead>
											<tbody class="js-pagamentos">
												
											</tbody>
										</table>									
									</div>
									
									
								</fieldset>
								
							</div>
						</div>
				</section>

			</form>

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
						<dd>
							<textarea class="js-boxObs-obs" rows="4"></textarea>
						</dd>
					</dl>
				</article>
			</section>
				<section id="modalProcedimento" class="modal" style="width:950px;">
				
				<header class="modal-conteudo">
						<form method="post" class="form js-form-agendamento">
					<fieldset>
						<legend>Adicionar Procedimento</legend>
							
							<dl class="dl3">
								<dt>Procedimento</dt>
								<dd>
									<select class="js-id_procedimento chosen">
										<option value=""></option>
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
										<option value=""></option>
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
										<option value=""></option>
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
										<option value=""></option>
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

							<div class="colunas5">
								
								<dl class="dl2">
									<dt>Plano</dt>
									<dd>
										<select class="js-id_plano chosen">
										</select>
									</dd>
								</dl>


								<dl class="dl2">
									<dt>Profissional</dt>
									<dd>
										<select class="js-id_profissional chosen">
											<option value="" data-iniciais="" data-iniciaisCor=""></option>
											<?php
											foreach($_profissionais as $x) {
												$aux=explode(" ",$x->nome);
												$aux[0]=strtoupper($aux[0]);
												$iniciais='';
												if($aux[0] =="DR" or $aux[0]=="DR." or $aux[0]=="DRA" or $aux[0]=="DRA.") {
													$iniciais=strtoupper(substr($aux[1],0,1));
													if(isset($aux[2])) $iniciais.=strtoupper(substr($aux[2],0,1));
												} else {
													$iniciais=strtoupper(substr($aux[0],0,1));
													if(isset($aux[1])) $iniciais.=strtoupper(substr($aux[1],0,1));
												}
												echo '<option value="'.$x->id.'" data-iniciais="'.$iniciais.'" data-iniciaisCor="'.$x->calendario_cor.'">'.utf8_encode($x->nome).'</option>';
											}
											?>
										</select>
									</dd>
								</dl>

								<dl>
									<dt>&nbsp;</dt>
									<dd>
										<a href="javascript:;" class="button js-btn-add" style="background:var(--azul)"><i class="iconify" data-icon="ic-baseline-add"></i> Adicionar</a>
									</dd>
								</dl>
							</div>
					
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

			$_procedimentos=array();
			$sql->consult($_table."_procedimentos","*","where id_tratamento IN (".implode(",",$tratamentosIDs).") and id_unidade = $usrUnidade->id and lixo=0");
			while($x=mysqli_fetch_object($sql->mysqry)) {
				if($x->situacao=="aprovado") {
					$_procedimentos[$x->id_tratamento][]=$x;
				}
			}

			$_pagamentos=array();
			$sql->consult($_table."_pagamentos","*","where id_tratamento IN (".implode(",",$tratamentosIDs).") and id_unidade = $usrUnidade->id and lixo=0");
			while($x=mysqli_fetch_object($sql->mysqry)) {
				$_pagamentos[$x->id_tratamento][]=$x;
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
						<div class="card js-procedimento-item" style="border-left:solid 10px <?php echo $cor;?>;" onclick="document.location.href='<?php echo "$_page?form=1&edita=$x->id&$url";?>'">
							<div class="js-descricao" style="width:58%;">
								<h1 class="js-procedimento"><strong><?php echo utf8_encode($x->titulo);?></strong></h1>
								<h2 class="js-regiao"><?php echo date('d/m/Y H:i',strtotime($x->data));?></h2>
							</div>
							<div class="js-valor" style="width:20%;margin-right: 10px;">
								<?php
								if($x->id_aprovado==0) {
									echo "-";
								} else {
									if(count($procedimentos)==0) echo '<a href="javascript" class="tooltip" title="Nenhum procedimento foi aprovado"><span class="iconify" data-icon="eva:alert-triangle-fill" data-inline="false" data-height="25"></span></a>';
									else {
										$abertos=0;
										$finalizados=0;
										foreach($procedimentos as $p) {
											if($p->id_concluido==0) $abertos++;
											else $finalizados++;
										}
										$perc=($abertos+$finalizados)==0?0:number_format(($finalizados/($abertos+$finalizados))*100,0,"","");
									?>
									<span style="font-size:12px;color:#999;">Evolução</span>
									<div class="grafico-barra"><span style="width:<?php echo $perc;?>%">&nbsp;</span></div>
									<?php
									}
								}
								?>
							</div>
							<div class="js-valor" style="width:20%;">
								<?php
								if($x->id_aprovado==0) {
									echo "-";
								} else {
									if(count($pagamentos)==0) echo '<a href="javascript" class="tooltip" title="Nenhum pagamento foi adicionado"><span class="iconify" data-icon="eva:alert-triangle-fill" data-inline="false" data-height="25"></span></a>';
									else {
										$abertos=0;
										$finalizados=0;
										foreach($pagamentos as $p) {
											//if($p->id_pago==0) $abertos++;
										//	else $finalizados++;
										}
										$perc=($abertos+$finalizados)==0?0:number_format(($finalizados/($abertos+$finalizados))*100,0,"","");
									?>
									<span style="font-size:12px;color:#999;">Pagamento</span>
									<div class="grafico-barra"><span style="width:<?php echo $perc;?>%">&nbsp;</span></div>
									<?php
									}
								}
								?>
							</div>

						</div>
					</div>
					<?php
					}
					?>
				</div>
				<?php /*<div class="registros">

					<table class="tablesorter">
						<thead>
							<tr>
								<th style="width:30px;"></th>
								<th>Título</th>
								<th>Data</th>
								<th style="width:300px;">Conclusão da Evolução</th>
								<th style="width:300px;">Conclusão do Pagamento</th>								
							</tr>
						</thead>
						<tbody>
						<?php
						
						foreach($registros as $x) {

							$procedimentos=array();
							if(isset($_procedimentos[$x->id])) $procedimentos=$_procedimentos[$x->id];

							$pagamentos=array();
							if(isset($_pagamentos[$x->id])) $pagamentos=$_pagamentos[$x->id];
							
						?>
						<tr onclick="document.location.href='<?php echo "$_page?form=1&edita=$x->id&$url";?>'">
							<td>
								<?php
								
								?>
							</td>
							<td><strong><?php echo utf8_encode($x->titulo);?></strong></td>
							<td><?php echo date('d/m/Y H:i',strtotime($x->data));?></td>
							<td>
								<?php
								if($x->id_aprovado==0) {
									echo "-";
								} else {
									if(count($procedimentos)==0) echo '<a href="javascript" class="tooltip" title="Nenhum procedimento foi aprovado"><span class="iconify" data-icon="eva:alert-triangle-fill" data-inline="false" data-height="25"></span></a>';
									else {
										$abertos=0;
										$finalizados=0;
										foreach($procedimentos as $p) {
											if($p->id_concluido==0) $abertos++;
											else $finalizados++;
										}
										$perc=($abertos+$finalizados)==0?0:number_format(($finalizados/($abertos+$finalizados))*100,0,"","");
									?>
									<div class="grafico-barra"><span style="width:<?php echo $perc;?>%">&nbsp;</span></div>
									<?php
									}
								}
								?>
							</td>
							<td>
								<?php
								if($x->id_aprovado==0) {
									echo "-";
								} else {
									if(count($pagamentos)==0) echo '<a href="javascript" class="tooltip" title="Nenhum pagamento foi adicionado"><span class="iconify" data-icon="eva:alert-triangle-fill" data-inline="false" data-height="25"></span></a>';
									else {
										$abertos=0;
										$finalizados=0;
										foreach($pagamentos as $p) {
											//if($p->id_pago==0) $abertos++;
										//	else $finalizados++;
										}
										$perc=($abertos+$finalizados)==0?0:number_format(($finalizados/($abertos+$finalizados))*100,0,"","");
									?>
									<div class="grafico-barra"><span style="width:<?php echo $perc;?>%">&nbsp;</span></div>
									<?php
									}
								}
								?>
							</td>
						</tr>
						<?php
						}
						?>
						
						</tbody>
					</table>
				</div>*/?>
				<?php
				}
				?>
			</div>
		</section>		
	</section>

<?php
	include "includes/footer.php";
?>