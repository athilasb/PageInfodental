<?php
	if(isset($_POST['ajax'])) {
		$dir="../../";
		require_once("../../lib/conf.php");
		require_once("../../usuarios/checa.php");


		$attr=array('prefixo'=>$_p,'usr'=>$usr);
		$wts = new Whatsapp($attr);

		$rtn = array();

		# Anamnese
			if($_POST['ajax']=="asPAnamnese") {

				$anamnese='';
				if(isset($_POST['id_anamnese']) and is_numeric($_POST['id_anamnese'])) {
					$sql->consult($_p."parametros_anamnese","*", "where id=".$_POST['id_anamnese']." and lixo=0");
					if($sql->rows) {
						$anamnese=mysqli_fetch_object($sql->mysqry);
					}
				}

				if(is_object($anamnese)) {

					$formulario=array();
					$sql->consult($_p."parametros_anamnese_formulario","*","where id_anamnese=$anamnese->id and lixo=0 order by ordem asc");
					if($sql->rows) while($x=mysqli_fetch_object($sql->mysqry)) {
						$formulario[]=array('id'=>(int)$x->id,
											'tipo'=>$x->tipo,
											'pergunta'=>utf8_encode($x->pergunta),
											'obg'=>(int)$x->obrigatorio);
					}

					$rtn=array('success'=>true,'formulario'=>$formulario);

				} else {
					$rtn=array('success'=>false,'error'=>'Anamnese não encontrada!');
				}
			}
			else if($_POST['ajax']=="asPAnamnesePersistir") {
				

				$anamnese='';
				if(isset($_POST['id_anamnese']) and is_numeric($_POST['id_anamnese'])) {
					$sql->consult($_p."parametros_anamnese","*", "where id=".$_POST['id_anamnese']." and lixo=0");
					if($sql->rows) $anamnese=mysqli_fetch_object($sql->mysqry);
				}

				$paciente='';
				if(isset($_POST['id_paciente']) and is_numeric($_POST['id_paciente'])) {
					$sql->consult($_p."pacientes","*", "where id=".$_POST['id_paciente']." and lixo=0");
					if($sql->rows) $paciente=mysqli_fetch_object($sql->mysqry);
				}

				$profissional='';
				if(isset($_POST['id_profissional']) and is_numeric($_POST['id_profissional'])) {
					$sql->consult($_p."colaboradores","*", "where id=".$_POST['id_profissional']." and lixo=0");
					if($sql->rows) $profissional=mysqli_fetch_object($sql->mysqry);
				}

				$enviarLink = (isset($_POST['enviarLink']) and $_POST['enviarLink']==1) ? $_POST['enviarLink'] : 0;

				$anamnesePerguntasDesativadas = [];
				if(isset($_POST['anamnesePerguntasDesativadas']) and !empty($_POST['anamnesePerguntasDesativadas'])) {
					$anamnesePerguntasDesativadas = explode(",",$_POST['anamnesePerguntasDesativadas']);
				}


				$evolucao='';
				if(is_object($paciente) and is_object($anamnese) and is_object($profissional) and $enviarLink==1) {
					$sql->consult($_p."pacientes_evolucoes","*","where id_paciente=$paciente->id and id_anamnese=$anamnese->id and id_profissional=$profissional->id and enviarLink=1 and id_assinatura=0");
					if($sql->rows) {
						$evolucao=mysqli_fetch_object($sql->mysqry);
					}
				}


				if(is_object($paciente)) {

					if(is_object($anamnese)) {

						if(is_object($profissional)) {
						
							$perguntas=array();
							$sql->consult($_p."parametros_anamnese_formulario","*","where id_anamnese=$anamnese->id and lixo=0 order by ordem asc");
							if($sql->rows) while($x=mysqli_fetch_object($sql->mysqry)) $perguntas[$x->id]=$x;

							if(count($perguntas)>0) {
								$id_evolucao=0;
								
								if(is_object($evolucao)) {
									//$sql->update($_p."pacientes_evolucoes","obs='".addslashes(utf8_decode($_POST['obs']))."'","where id=$evolucao->id");
									$id_evolucao=$evolucao->id;
									$sql->update($_p."pacientes_evolucoes","id_profissional='".$profissional->id."'","where id=$id_evolucao");
								} else {

									// cria a evolucao Anamnese
									$sql->add($_p."pacientes_evolucoes","data=now(),
																			id_tipo=1,
																			id_anamnese=$anamnese->id,
																			id_paciente=$paciente->id,
																			id_usuario=$usr->id,
																			enviarLink=$enviarLink,
																			id_assinatura=0,
																			id_profissional=$profissional->id");
									$id_evolucao=$sql->ulid;
									
								}

								foreach($perguntas as $id_pergunta=>$p) {

									$pJson=array();
									foreach($p as $k=>$v) {
										$pJson[$k]=utf8_encode($v);
									}


									$vsqlResposta="id_paciente=$paciente->id,
													id_evolucao=$id_evolucao,
													id_pergunta=$p->id,
													id_anamnese=$anamnese->id,
													pergunta='".addslashes($p->pergunta)."',
													tipo='".$p->tipo."',
													json_pergunta='".addslashes((json_encode($pJson)))."'";

									if(in_array($p->id,$anamnesePerguntasDesativadas)) $vsqlResposta.=",desativado=1";

									if($p->tipo=="nota" or $p->tipo=="simnao") {
										$vsqlResposta.=",resposta='".addslashes(strtoupperWLIB(utf8_decode(isset($_POST["resposta_$p->id"])?$_POST["resposta_$p->id"]:"")))."'";
									} else if($p->tipo=='texto') {
										$vsqlResposta.=",resposta_texto='".addslashes(strtoupperWLIB(utf8_decode(isset($_POST["resposta_texto_$p->id"])?$_POST["resposta_texto_$p->id"]:"")))."'";
									} else if($p->tipo=='simnaotexto') {
										$vsqlResposta.=",resposta='".addslashes(strtoupperWLIB(utf8_decode(isset($_POST["resposta_$p->id"])?$_POST["resposta_$p->id"]:"")))."',resposta_texto='".addslashes(strtoupperWLIB(utf8_decode(isset($_POST["resposta_texto_$p->id"])?$_POST["resposta_texto_$p->id"]:"")))."'";
									}

									$resposta='';
									$where="where id_paciente=$paciente->id and id_anamnese=$anamnese->id and id_evolucao=$id_evolucao and id_pergunta=$p->id and lixo=0";
									$sql->consult($_p."pacientes_evolucoes_anamnese","id",$where);
									if($sql->rows) {
										$resposta=mysqli_fetch_object($sql->mysqry);
									}

									if(is_object($resposta)) {
										$sql->update($_p."pacientes_evolucoes_anamnese",$vsqlResposta.",data_atualizacao=now()","where id=$resposta->id");
									} else {
										$sql->add($_p."pacientes_evolucoes_anamnese",$vsqlResposta.",data=now(),id_usuario=$usr->id");

									}
								}	

								if($enviarLink==1) {

									$attr=array('id_tipo'=>11,
												'id_evolucao'=>$id_evolucao);
						
									if($wts->adicionaNaFila($attr)) {
										$rtn=array('success'=>true,'celular'=>mask($wts->celular),'id_evolucao'=>$id_evolucao);
									} else {
										$rtn=array('success'=>false,'error'=>$wts->erro);
									}


								} else if(generatePDF($id_evolucao)) {

									$sql->update($_p."pacientes_evolucoes","enviarLinkFinalizado=now()","where id=$id_evolucao");

									/*$attr=array('id_tipo'=>11,
												'id_evolucao'=>$id_evolucao);
						
									$wts->adicionaNaFila($attr);*/

									$rtn=array('success'=>true);//,'celular'=>mask($wts->celular));
								} else {
									$rtn=array('success'=>false,'error'=>'Algum erro ocorreu durante a geração do PDF! Favor contate nossa equipe de suporte.');
								}
		
							} else {
								$rtn=array('success'=>false,'error'=>'Anamnese sem formulário configurado!');
							}

						} else {
							$rtn=array('success'=>false,'error'=>'Profissional não encontrado!');
						}
					} else {
						$rtn=array('success'=>false,'error'=>'Anamnese não encontrada!');
					}
				} else {
					$rtn=array('success'=>false,'error'=>'Paciente não encontrado!');
				}
			}
		# Atestado
			else if($_POST['ajax']=="asPAtestadoTexto") {

				$data = isset($_POST['data'])?$_POST['data']:'';
				$id_profissional = (isset($_POST['id_profissional']) and is_numeric($_POST['id_profissional']))?$_POST['id_profissional']:0;
				$dias = (isset($_POST['dias']) and is_numeric($_POST['dias']))?$_POST['dias']:0;
				$duracao = (isset($_POST['duracao']) and is_numeric($_POST['duracao']))?$_POST['duracao']:0;

				$tipo='';
				if(isset($_POST['id_tipo']) and is_numeric($_POST['id_tipo'])) {
					$sql->consult("infodentalADM.infod_parametros_atestados_tipos","*","where id=".$_POST['id_tipo']." and lixo=0");
					if($sql->rows) $tipo=mysqli_fetch_object($sql->mysqry);
				}

				$fim='';
				if(isset($_POST['fim']) and is_numeric($_POST['fim'])) {
					$sql->consult("infodentalADM.infod_parametros_atestados_fins","*","where id=".$_POST['fim']." and lixo=0");
					if($sql->rows) $fim=mysqli_fetch_object($sql->mysqry);
				}

				$paciente='';
				if(isset($_POST['id_paciente']) and is_numeric($_POST['id_paciente'])) {
					$sql->consult($_p."pacientes","nome,cpf,id","where id=".$_POST['id_paciente']." and lixo=0");
					if($sql->rows) $paciente=mysqli_fetch_object($sql->mysqry);
				}

				if(!empty($data) and is_object($tipo) and is_object($fim) and !empty($id_profissional)) {

					$sql->consult("infodentalADM.infod_parametros_atestados_texto","*","where id_tipo=$tipo->id and id_fim=$fim->id");
					if($sql->rows) {

						$t=mysqli_fetch_object($sql->mysqry);

						$texto=utf8_encode($t->texto);

						$texto = str_replace("[dias]",utf8_encode($dias),$texto);
						$texto = str_replace("[data]",utf8_encode($data),$texto);
						$texto = str_replace("[duracao]",utf8_encode($duracao)." minutos",$texto);
						$texto = str_replace("[nome]",utf8_encode($paciente->nome),$texto);
						$texto = str_replace("[cpf]",maskCPF($paciente->cpf),$texto);

						$rtn=array('success'=>true,'texto'=>$texto);
					} else {
						$rtn=array('success'=>false,'error'=>'Nenhum modelo para este tipo e fim');
					}
				} else {
					$rtn=array('success'=>false);
				}
			}
			else if($_POST['ajax']=='asPAtestadoPersistir') {

				//var_dump($_POST);die();
				
				$data = isset($_POST['data'])?$_POST['data']:'';
				$dias = (isset($_POST['dias']) and is_numeric($_POST['dias']))?$_POST['dias']:0;
				$duracao = (isset($_POST['duracao']) and is_numeric($_POST['duracao']))?$_POST['duracao']:0;
				$atestado = (isset($_POST['atestado']) and !empty($_POST['atestado']))?$_POST['atestado']:'';

				$paciente='';
				if(isset($_POST['id_paciente']) and is_numeric($_POST['id_paciente'])) {
					$sql->consult($_p."pacientes","*", "where id=".$_POST['id_paciente']." and lixo=0");
					if($sql->rows) $paciente=mysqli_fetch_object($sql->mysqry);
				}

				$profissional='';
				if(isset($_POST['id_profissional']) and is_numeric($_POST['id_profissional'])) {
					$sql->consult($_p."colaboradores","*", "where id=".$_POST['id_profissional']." and lixo=0");
					if($sql->rows) $profissional=mysqli_fetch_object($sql->mysqry);
				}

				$tipo='';
				if(isset($_POST['id_tipo']) and is_numeric($_POST['id_tipo'])) {
					$sql->consult("infodentalADM.infod_parametros_atestados_tipos","*","where id=".$_POST['id_tipo']." and lixo=0");
					if($sql->rows) $tipo=mysqli_fetch_object($sql->mysqry);
					else echo "nao achou ".$_POST['id_tipo'];
				}

				$fim='';
				if(isset($_POST['fim']) and is_numeric($_POST['fim'])) {
					$sql->consult("infodentalADM.infod_parametros_atestados_fins","*","where id=".$_POST['fim']." and lixo=0");
					if($sql->rows) $fim=mysqli_fetch_object($sql->mysqry);
				}

				$erro='';
				if(empty($paciente)) $erro='Paciente não encontrado!';
				else if(empty($profissional)) $erro='Profissional não encontrado!';
				else if(empty($tipo)) $erro='Tipo do Atestado não definido!';
				else if(empty($fim)) $erro='Fim do Atestado não definido!';
				else if(empty($data)) $erro='Data do Atestado não definida!';
				else if(empty($atestado)) $erro='Atestado não definido!';


				if(empty($erro)) {


					$atestado = '';
					if(isset($_POST['id_atestado']) and is_numeric($_POST['id_atestado'])) {
						$sql->consult($_p."pacientes_evolucoes_atestados","*","where id=".$_POST['id_atestado']." and id_paciente=$paciente->id and lixo=0");
						if($sql->rows) $atestado=mysqli_fetch_object($sql->mysqry);
					}

					$vSQLAtestado="id_paciente=$paciente->id,
									data_atestado='".invDateTime($data)."',
									tipo='".addslashes($tipo->titulo)."',
									objetivo='".addslashes($fim->titulo)."',
									id_tipo=$tipo->id,
									id_fim=$fim->id,
									id_profissional=$profissional->id,
									atestado='".addslashes(utf8_encode($_POST['atestado']))."',
									dias='".addslashes($dias)."',
									duracao='".addslashes($duracao)."'";

					// edita um atestado existente
					if(is_object($atestado)) {

					} 

					// cria um novo atestado
					else {

						// id_tipo = 4 -> Atestado
						/*$sql->consult($_p."pacientes_evolucoes","*","WHERE data > NOW() - INTERVAL 1 MINUTE and 
																								id_paciente=$paciente->id and
																								id_tipo=4 and  
																								id_usuario=$usr->id");	
						if($sql->rows) {
							$e=mysqli_fetch_object($sql->mysqry);
							$id_evolucao=$e->id;
						} else {*/
							$sql->add($_p."pacientes_evolucoes","data=now(),
																	id_tipo=4,
																	id_profissional=$profissional->id,
																	id_paciente=$paciente->id,
																	id_usuario=$usr->id");
							$id_evolucao=$sql->ulid;

							
						//}

						$sql->consult($_p."pacientes_evolucoes_atestados","*","where id_evolucao=$id_evolucao order by id desc limit 1");
						if($sql->rows) {
							$atestado=mysqli_fetch_object($sql->mysqry);
						}

						$vSQLAtestado.=",id_evolucao=$id_evolucao";

						if(empty($atestado)) {
							$sql->add($_p."pacientes_evolucoes_atestados",$vSQLAtestado.",data=now()");
						} else {
							$sql->update($_p."pacientes_evolucoes_atestados",$vSQLAtestado,"where id=$atestado->id");
						}

						if(generatePDF($id_evolucao)) {
							$rtn=array('success'=>true);
						} else {
							$rtn=array('success'=>false,'error'=>'Algum erro ocorreu durante a geração do PDF! Favor contate nossa equipe de suporte.');
						}
					}

				} else {
					$rtn=array('success'=>false,'error'=>$erro);
				}
			}
		# Pedido de Exame
			else if($_POST['ajax']=="asPPedidoExamePersistir") {

				$paciente='';
				if(isset($_POST['id_paciente']) and is_numeric($_POST['id_paciente'])) {
					$sql->consult($_p."pacientes","*", "where id=".$_POST['id_paciente']." and lixo=0");
					if($sql->rows) $paciente=mysqli_fetch_object($sql->mysqry);
				}


				$examesSolicitados=array();
				if(isset($_POST['exames']) and !empty($_POST['exames']) and is_array($_POST['exames'])) {

					$examesJSON = $_POST['exames'];
					$erro='';
					foreach($examesJSON as $v) {
						$v=(object)$v;
						$sql->consult($_p."parametros_examedeimagem","*","where id=$v->id_exame");
						if($sql->rows) {
							$x=mysqli_fetch_object($sql->mysqry);
							$examesSolicitados[]=array('exame'=>$x,'evolucaoExame'=>$v,'id_exame'=>isset($v->id)?$v->id:0);
						} else {
							$erro='Exame '.$v->titulo.' não foi encontrado!';
						}
					}
				}

				if(empty($erro)) {
					if(is_object($paciente)) {
						$evolucao='';
						if(count($examesSolicitados)>0) {

							if(is_object($evolucao)) {
								$sql->update($_p."pacientes_evolucoes","data_pedido='".addslashes(invDate($_POST['data']))."',
																		id_profissional='".addslashes(utf8_decode($_POST['id_profissional']))."',
																		id_clinica='".addslashes(utf8_decode($_POST['id_clinica']))."'","where id=$evolucao->id");
								$id_evolucao=$evolucao->id;
							} else {
								// id_tipo = 2 -> Procedimentos Aprovados
								$sql->consult($_p."pacientes_evolucoes","*","WHERE data > NOW() - INTERVAL 0 MINUTE and 
																										id_paciente=$paciente->id and
																										id_tipo=6 and  
																										id_usuario=$usr->id");	
								//echo $sql->rows;die();
								if($sql->rows) {
									$e=mysqli_fetch_object($sql->mysqry);
									$sql->update($_p."pacientes_evolucoes","data_pedido='".addslashes(invDate($_POST['data']))."',
																				id_profissional='".addslashes(utf8_decode($_POST['id_profissional']))."',
																				id_clinica='".addslashes(utf8_decode($_POST['id_clinica']))."'","where id=$e->id");
									$id_evolucao=$e->id;
								} else {
									$sql->add($_p."pacientes_evolucoes","data=now(),
																			id_tipo=6,
																			id_paciente=$paciente->id,
																			id_usuario=$usr->id,
																			data_pedido='".addslashes(invDate($_POST['data']))."',
																			id_profissional='".addslashes(utf8_decode($_POST['id_profissional']))."',
																			id_clinica='".addslashes(utf8_decode($_POST['id_clinica']))."'");
																			//obs='".addslashes(utf8_decode($_POST['obs']))."'");
									$id_evolucao=$sql->ulid;
								}
							}

							foreach($examesSolicitados as $obj) {
								$obj=(object)$obj;
								$exame=$obj->exame;
								$evolucaoExame=$obj->evolucaoExame;
								$vSQLExame="data=now(),
											id_paciente=$paciente->id,
											id_evolucao=$id_evolucao,
											id_exame='".addslashes($exame->id)."',
											opcao='".addslashes(utf8_decode($evolucaoExame->opcao))."',
											id_opcao='".addslashes(json_encode($evolucaoExame->id_opcao))."',
											id_profissional='".addslashes($_POST['id_profissional'])."',
											id_clinica='".addslashes($_POST['id_clinica'])."',
											status='".addslashes($evolucaoExame->status)."',
											obs='".addslashes(utf8_decode($evolucaoExame->obs))."'";
											//echo $vSQLExame;die();
								$evProc='';
								if(isset($obj->id_evolucao_exame) and is_numeric($obj->id_evolucao_exame)) {
									$sql->consult($_p."pacientes_evolucoes_pedidosdeexames","*","where id=$obj->id_exame and id_paciente=$paciente->id and lixo=0");
									if($sql->rows) {
										$evProc=mysqli_fetch_object($sql->mysqry);
									}
								}

								if(empty($evProc)) {
									/*$sql->consult($_p."pacientes_evolucoes_pedidosdeexames","*","WHERE data > NOW() - INTERVAL 1 MINUTE and 
																										id_paciente=$paciente->id and 
																										id_evolucao=$id_evolucao and 
																										id_exame='".addslashes($exame->id)."'");	
									if($sql->rows) {
										$x=mysqli_fetch_object($sql->mysqry);
										$sql->update($_p."pacientes_evolucoes_pedidosdeexames",$vSQLExame,"where id=$x->id");
									} else {*/
										$sql->add($_p."pacientes_evolucoes_pedidosdeexames",$vSQLExame);
									//}
								} else {
									$sql->update($_p."pacientes_evolucoes_pedidosdeexames",$vSQLExame,"where id=$evProc->id");
								}
							}	

							if(generatePDF($id_evolucao)) {
								$rtn=array('success'=>true);
							} else {
								$rtn=array('success'=>false,'error'=>'Algum erro ocorreu durante a geração do PDF! Favor contate nossa equipe de suporte.');
							}	
						} else {
							$rtn=array('success'=>false,'error'=>'Adicione pelo menos um exame!');
						}
					} else {
						$rtn=array('success'=>false,'error'=>'Paciente não encontrado!');
					}
				} else {
					$rtn=array('success'=>false,'error'=>$erro);
				}

				 
			}
		# Receituario
			else if($_POST['ajax']=="asMedicamentosListar") {


				$medicamentos=array();
				$sql->consult($_p."medicamentos","*","where lixo=0 order by titulo");
				while($x=mysqli_fetch_object($sql->mysqry)) {
					$medicamentos[]=array('id'=>$x->id,'titulo'=>utf8_encode($x->titulo));
				}

				$rtn=array('success'=>true,'medicamentos'=>$medicamentos);
			}

			else if($_POST['ajax']=="asMedicamentosPersistir") {



				$titulo=(isset($_POST['medicamento']) and !empty($_POST['medicamento']))?$_POST['medicamento']:'';
				$quantidade=(isset($_POST['quantidade']) and is_numeric($_POST['quantidade']))?$_POST['quantidade']:'';
				$tipo=(isset($_POST['tipo']) and !empty($_POST['tipo']))?$_POST['tipo']:'';
				$posologia=(isset($_POST['posologia']) and !empty($_POST['posologia']))?$_POST['posologia']:'';
				if(empty($titulo)) {
					$rtn=array('success'=>false,'error'=>'Medicamento não definido');
				} else if(empty($quantidade)) {
					$rtn=array('success'=>false,'error'=>'Quantidade não definida');
				} else if(empty($tipo)) {
					$rtn=array('success'=>false,'error'=>'Tipo do Medicamento não definido');
				} else if(empty($posologia)) {
					$rtn=array('success'=>false,'error'=>'Posologia não definida');
				} else {
					$vSQL="titulo='".addslashes(utf8_decode($titulo))."',
							quantidade='".addslashes(utf8_decode($quantidade))."',
							tipo='".addslashes(utf8_decode($tipo))."',
							posologia='".addslashes(utf8_decode($posologia))."',
							controleespecial='".((isset($_POST['controleEspecial']) and $_POST['controleEspecial']==1)?1:0)."',
							lixo=0";

					$medicamento = '';
					if(isset($_POST['id']) and is_numeric($_POST['id'])) {
						$sql->consult($_p."medicamentos", "*","where id=".$_POST['id']." and lixo=0");
						if($sql->rows) {
							$medicamento=mysqli_fetch_object($sql->mysqry);
						}
					}

					if(empty($medicamento)) {
						$sql->add($_p."medicamentos",$vSQL);
						$id_medicamento=$sql->ulid;
						$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='insert',vsql='".addslashes($vSQL)."',tabela='".$_p."medicamentos',id_reg='".$id_medicamento."'");
					} else {
						$vWHERE="where id=$medicamento->id";
						$sql->update($_p."medicamentos",$vSQL,$vWHERE);
						$id_medicamento=$medicamento->id;
						$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='update',vsql='".addslashes($vSQL)."',tabela='".$_p."medicamentos',vwhere='".addslashes($vWHERE)."',id_reg='".$medicamento->id."'");
					}


					$rtn=array('success'=>true,
								'id_medicamento'=>$id_medicamento);
				}
			}

			else if($_POST['ajax']=="asMedicamentosEditar") {

				$medicamento = '';
				if(isset($_POST['id']) and is_numeric($_POST['id'])) {
					$sql->consult($_p."medicamentos", "*","where id=".$_POST['id']." and lixo=0");
					if($sql->rows) {
						$medicamento=mysqli_fetch_object($sql->mysqry);
					}
				}

				if(is_object($medicamento)) {
					$rtn=array('success'=>true,
								'id'=>(int)$medicamento->id,
								'medicamento'=>utf8_encode($medicamento->titulo),
								'tipo'=>utf8_encode($medicamento->tipo),
								'quantidade'=>utf8_encode($medicamento->quantidade),
								'posologia'=>utf8_encode($medicamento->posologia),
								'controleEspecial'=>(int)($medicamento->controleEspecial));
				} else {
					$rtn=array('success'=>false,'error'=>'Medicamento não encontrado!');
				}
			}

			else if($_POST['ajax']=="asMedicamentosRemover") {
				$medicamento = '';
				if(isset($_POST['id']) and is_numeric($_POST['id'])) {
					$sql->consult($_p."medicamentos", "*","where id=".$_POST['id']." and lixo=0");
					if($sql->rows) {
						$medicamento=mysqli_fetch_object($sql->mysqry);
					}
				}

				if(is_object($medicamento)) {
					$vSQL="lixo=1";
					$vWHERE="where id=$medicamento->id";
					$sql->update($_p."medicamentos",$vSQL,$vWHERE);
					$id_medicamento=$sql->ulid;
					$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='update',vsql='".addslashes($vSQL)."',tabela='".$_p."medicamentos',vwhere='".addslashes($vWHERE)."',id_reg='".$medicamento->id."'");
					$rtn=array('success'=>true);
				} else {
					$rtn=array('success'=>false,'error'=>'Medicamento não encontrado!');
				}
			}

			else if($_POST['ajax']=="asReceituarioPersistir") {
				$paciente='';
				if(isset($_POST['id_paciente']) and is_numeric($_POST['id_paciente'])) {
					$sql->consult($_p."pacientes","*", "where id=".$_POST['id_paciente']." and lixo=0");
					if($sql->rows) $paciente=mysqli_fetch_object($sql->mysqry);
				}


				if(is_object($paciente)) {

					$medicamentosJSON=array();
					if(isset($_POST['medicamentos']) and !empty($_POST['medicamentos']) and is_array($_POST['medicamentos'])) {

						$medicamentosJSON = $_POST['medicamentos'];
						$erro='';
						foreach($medicamentosJSON as $v) {
							$v=(object)$v;

							if(!empty($v->id_medicamento)) {

								$where="where id=$v->id_medicamento";
								$sql->consult($_p."medicamentos","*",$where);
								if($sql->rows==0) {
									$erro='Exame '.$v->titulo.' não foi encontrado!';
								}
							}
						}
					}

					if(empty($medicamentosJSON) or !is_array($medicamentosJSON) or count($medicamentosJSON)==0) $erro='Nenhum medicamento foi adicionado ao receituário';

					if(empty($erro)) {


						// id_tipo = 7 -> receituario
						/*$sql->consult($_p."pacientes_evolucoes","*","WHERE data > NOW() - INTERVAL 1 MINUTE and 
																								id_paciente=$paciente->id and
																								id_tipo=7 and  
																								id_usuario=$usr->id");	*/
						/*if($sql->rows) {
							$e=mysqli_fetch_object($sql->mysqry);
							$sql->update($_p."pacientes_evolucoes","data_pedido='".addslashes(invDate($_POST['data']))."',
																		id_profissional='".addslashes(utf8_decode($_POST['id_profissional']))."',
																		tipo_receita='".addslashes(utf8_decode($_POST['tipo_receita']))."'","where id=$e->id");
							$id_evolucao=$e->id;
						} else {*/
							$sql->add($_p."pacientes_evolucoes","data=now(),
																	id_tipo=7,
																	id_paciente=$paciente->id,
																	id_usuario=$usr->id,
																	data_pedido='".addslashes(invDate($_POST['data']))."',
																	id_profissional='".addslashes(utf8_decode($_POST['id_profissional']))."',
																	tipo_receita='".addslashes(utf8_decode($_POST['tipo_receita']))."'");
																	//obs='".addslashes(utf8_decode($_POST['obs']))."'");
							$id_evolucao=$sql->ulid;
						//}

						foreach($medicamentosJSON as $obj) {
							$obj=(object)$obj;


							$vSQLReceita="data=now(),
										id_paciente=$paciente->id,
										id_evolucao=$id_evolucao,
										medicamento='".addslashes(utf8_decode($obj->medicamento))."',
										quantidade='".addslashes(utf8_decode($obj->quantidade))."',
										posologia='".addslashes(utf8_decode($obj->posologia))."',
										tipo='".addslashes(utf8_decode($obj->tipo))."',
										id_medicamento='".addslashes(utf8_decode($obj->id_medicamento))."',
										controleespecial='".addslashes(utf8_decode($obj->controleespecial))."'";
							
							$sql->consult($_p."pacientes_evolucoes_receitas","*","WHERE data > NOW() - INTERVAL 1 MINUTE and 
																								id_paciente=$paciente->id and 
																								id_evolucao=$id_evolucao and 
																								medicamento='".addslashes($obj->medicamento)."'");	
							if($sql->rows) {
								$x=mysqli_fetch_object($sql->mysqry);
								$sql->update($_p."pacientes_evolucoes_receitas",$vSQLReceita,"where id=$x->id");
							} else {
								$sql->add($_p."pacientes_evolucoes_receitas",$vSQLReceita);
							}
							
						}

						if(generatePDF($id_evolucao)) {
							$rtn=array('success'=>true);
						} else {
							$rtn=array('success'=>false,'error'=>'Algum erro ocorreu durante a geração do PDF! Favor contate nossa equipe de suporte.');
						}

					} else {
						$rtn=array('success'=>false,'error'=>$erro);
					}


				} else {
					$rtn=array('success'=>false,'error'=>'Paciente não encontrado!');
				}
			}
		# Geral (Prontuario simples/normal)
			else if($_POST['ajax']=="asPGeralPersistir") {
				$paciente='';
				if(isset($_POST['id_paciente']) and is_numeric($_POST['id_paciente'])) {
					$sql->consult($_p."pacientes","*", "where id=".$_POST['id_paciente']." and lixo=0");
					if($sql->rows) $paciente=mysqli_fetch_object($sql->mysqry);
				}


				if(is_object($paciente)) {

					$data = (isset($_POST['data']) and !empty($_POST['data']))?$_POST['data']:'';
					$id_profissional = (isset($_POST['id_profissional']) and !empty($_POST['id_profissional']) and is_numeric($_POST['id_profissional']))?$_POST['id_profissional']:'';
					$texto = (isset($_POST['texto']) and !empty($_POST['texto']))?$_POST['texto']:'';

					$erro='';
					if(empty($data)) $erro='Preencha o campo de Data';
					else if(empty($id_profissional)) $erro='Preencha o campo de Profissional';
					else if(empty($texto)) $erro='Preencha o campo de Evolução';
					else {


						// id_tipo = 9 -> geral
						/*$sql->consult($_p."pacientes_evolucoes","*","WHERE data > NOW() - INTERVAL 1 MINUTE and 
																								id_paciente=$paciente->id and
																								id_tipo=9 and  
																								id_usuario=$usr->id");	
						if($sql->rows) {
							$e=mysqli_fetch_object($sql->mysqry);
							$sql->update($_p."pacientes_evolucoes","id_profissional='".addslashes(utf8_decode($_POST['id_profissional']))."'","where id=$e->id");
							$id_evolucao=$e->id;
						} else {*/
							$sql->add($_p."pacientes_evolucoes","data=now(),
																	id_tipo=9,
																	id_paciente=$paciente->id,
																	id_usuario=$usr->id,
																	id_profissional='".addslashes(utf8_decode($_POST['id_profissional']))."'");
							$id_evolucao=$sql->ulid;
						//}


						$geral='';
						$sql->consult($_p."pacientes_evolucoes_geral","*","where id_evolucao=$id_evolucao and lixo=0");
						if($sql->rows) {
							$geral=mysqli_fetch_object($sql->mysqry);
						}

						$vSQLGeral="id_evolucao=$id_evolucao,
									data='".invDatetime($data)."',
									id_profissional='".$id_profissional."',
									texto='".addslashes(utf8_decode($texto))."',
									id_usuario=$usr->id"; 

						if(is_object($geral)) {
							$sql->update($_p."pacientes_evolucoes_geral",$vSQLGeral,"where id=$geral->id");
						} else {
							$sql->add($_p."pacientes_evolucoes_geral",$vSQLGeral);
						}

						$rtn=array('success'=>true);

					}

					if(!empty($erro)) {
						$rtn=array('success'=>false,'error'=>$erro);
					}


				} else {
					$rtn=array('success'=>false,'error'=>'Paciente não encontrado!');
				}
			}
		# Documentos
			else if($_POST['ajax']=="asPDocumentoSubstituir") {
				
				$paciente='';
				if(isset($_POST['id_paciente']) and is_numeric($_POST['id_paciente'])) {
					$sql->consult($_p."pacientes","*", "where id=".$_POST['id_paciente']." and lixo=0");
					if($sql->rows) $paciente=mysqli_fetch_object($sql->mysqry);
				}

				$documento='';
				if(isset($_POST['id_documento']) and is_numeric($_POST['id_documento'])) {
					$sql->consult($_p."parametros_documentos","*", "where id=".$_POST['id_documento']." and lixo=0");
					if($sql->rows) $documento=mysqli_fetch_object($sql->mysqry);
				}

				$plano = '';
				if(isset($_POST['id_planodetratamento']) and is_numeric($_POST['id_planodetratamento'])) {
					$sql->consult($_p."pacientes_tratamentos","*", "where id=".$_POST['id_planodetratamento']." and lixo=0");
					if($sql->rows) $plano=mysqli_fetch_object($sql->mysqry);
				}


				$clinica='';
				$sql->consult($_p."clinica","clinica_nome,endereco,lat,lng","order by id desc limit 1");
				if($sql->rows) $clinica=mysqli_fetch_object($sql->mysqry);

				if(is_object($paciente)) {
					if(is_object($documento)) {
						if(is_object($clinica)) {

							$_formasDePagamento=array();
							$sql->consult($_p."parametros_formasdepagamento","*","order by titulo asc");
							while($x=mysqli_fetch_object($sql->mysqry)) {
								$_formasDePagamento[$x->id]=$x;
							}

							$texto = utf8_encode($documento->texto);

							$dadosPacientes="<b>".trim(utf8_encode($paciente->nome))."</b>, brasileiro, ".trim(utf8_encode($paciente->estado_civil)).", inscrito no CPF de nº <b>".maskCPF($paciente->cpf)."</b> e RG de n°. <b>".trim($paciente->rg).", ".trim($paciente->rg_orgaoemissor)."</b>, com telefone de n° <b>".mask($paciente->telefone1)."</b> e email: <b>".($paciente->email)."</b>, residente e domiciliado à <b>".trim(utf8_encode($paciente->endereco))."</b>";


							$dataExtenso=diaDaSemana(date('d')).", ".date('d')." de ".outMes(date('m'))." de ".date('Y');;

							$texto = str_replace("[nome]",utf8_encode($paciente->nome),$texto);
							$texto = str_replace("[cpf]",maskCPF($paciente->cpf),$texto);
							$texto = str_replace("[endereco]",utf8_encode($paciente->endereco),$texto);
							$texto = str_replace("[clinica_nome]",utf8_encode($clinica->clinica_nome),$texto);
							$texto = str_replace("[clinica_endereco]",utf8_encode($clinica->endereco),$texto);
							$texto = str_replace("[dados_paciente]",$dadosPacientes,$texto);
							$texto = str_replace("[data]",date('d/m/Y'),$texto);
							$texto = str_replace("[data_leitura]",$dataExtenso,$texto);

							if(is_object($plano)) {


								$texto = str_replace("[procedimentos_tempo_estimado]",$plano->tempo_estimado,$texto); 


								// busca os procedimentos aprovados
								$sql->consult($_p."pacientes_tratamentos_procedimentos","*","where id_tratamento=$plano->id and lixo=0 and situacao='aprovado'");

								if($sql->rows) {
									$procedimentos='<table width=100% border=1><tr><th>Procedimento</th><th>Valor</th><th>Obs.:</th></tr>';
									$valorTotal=0;
									while($x=mysqli_fetch_object($sql->mysqry)) {

										if($x->quantitativo==1) $x->valor*=$x->quantidade;
										$x->opcao=encodingToJson($x->opcao);
										$valorTotal+=number_format($x->valor-$x->desconto,2,".","");
										if($x->desconto>0) {
											$valor="<strike>R$".number_format($x->valor,2,",",".")."</strike><br />R$".number_format($x->valor-$x->desconto,2,",",".");
										} else {
											$valor="R$".number_format($x->valor,2,",",".");
										}
										$regiao="";
										if(!empty($x->opcao)) $regiao=' - '.$x->opcao;
										$procedimentos.="<tr><td>".encodingToJson($x->procedimento).$regiao."</td><td>".$valor."</td><td>".encodingToJson($x->obs)."</td></tr>";
									}
									$procedimentos.='</table>';



									$texto = str_replace("[procedimentos]",$procedimentos,$texto);
									$texto = str_replace("[procedimentos_valor_total]",number_format($valorTotal,2,",","."),$texto);
								} else {
									$texto = str_replace("[procedimentos]","<font color=red>Nenhum procedimento aprovado</font>",$texto);
									$texto = str_replace("[procedimentos_valor_total]","<font color=red>??</font>",$texto);
								}

								// busca os pagamentos
								$sql->consult($_p."pacientes_tratamentos_pagamentos","*","where id_tratamento=$plano->id and lixo=0");
								if($sql->rows) {
									$pagamentos='<table width=100% border=1><tr><th>Data Vencimento</th><th>Forma de Pagamento</th><th>Valor</th></tr>';
									while ($x=mysqli_fetch_object($sql->mysqry))  {
										if(isset($_formasDePagamento[$x->id_formapagamento])) {
											$fp=$_formasDePagamento[$x->id_formapagamento];
											$complemento='';
											if($fp->tipo=="credito") $complemento.=" - ".$x->qtdParcelas."x";
											$pagamentos.='<tr><td>'.date('d/m/Y',strtotime($x->data_vencimento)).'</td><td>'.encodingToJson($fp->titulo).$complemento.'</td><td>'.number_format($x->valor,2,",",".").'</td></tr>';
										}
									}
									$pagamentos.='</table>';
									$texto = str_replace("[pagamentos]",$pagamentos,$texto);
								} else {
									$texto = str_replace("[pagamentos]","<font color=red>Nenhum pagamento definido</font>",$texto);
								}
							}
							$rtn=array('success'=>true,'texto'=>$texto);
						} else {
							$rtn=array('success'=>false,'error'=>'Clínica não encontrada!');
						}

					} else {
						$rtn=array('success'=>false,'error'=>'Modelo de documento não encontrado!');
					}
				} else {
					$rtn=array('success'=>false,'error'=>'Paciente não encontrado!');
				}

			}
			else if($_POST['ajax']=="asPDocumentosPersistir") {
				$paciente='';
				if(isset($_POST['id_paciente']) and is_numeric($_POST['id_paciente'])) {
					$sql->consult($_p."pacientes","*", "where id=".$_POST['id_paciente']." and lixo=0");
					if($sql->rows) $paciente=mysqli_fetch_object($sql->mysqry);
				}

				$documento='';
				if(isset($_POST['id_documento']) and is_numeric($_POST['id_documento'])) {
					$sql->consult($_p."parametros_documentos","*", "where id=".$_POST['id_documento']." and lixo=0");
					if($sql->rows) $documento=mysqli_fetch_object($sql->mysqry);
				}

				$texto = (isset($_POST['texto']) and !empty($_POST['texto']))?$_POST['texto']:'';
				$data = (isset($_POST['data']) and !empty($_POST['data']))?$_POST['data']:'';

				


				$erro='';
				if(empty($paciente)) $erro='Paciente não encontrado!';
				else if(empty($documento)) $erro='Documento não encontrado!';
				else if(empty($texto)) $erro='Texto não preenchido!';
				

				if(empty($erro)) {
					// id_tipo = 10 -> documento
					$sql->consult($_p."pacientes_evolucoes","*","WHERE data > NOW() - INTERVAL 1 MINUTE and 
																							id_paciente=$paciente->id and
																							id_tipo=10 and  
																							id_usuario=$usr->id");	
					/*if($sql->rows) {
						$e=mysqli_fetch_object($sql->mysqry);
						$id_evolucao=$e->id;
					} else {*/
						$sql->add($_p."pacientes_evolucoes","data=now(),
																id_tipo=10,
																id_paciente=$paciente->id,
																id_usuario=$usr->id");
						$id_evolucao=$sql->ulid;
					//}


					$geral='';
					$sql->consult($_p."pacientes_evolucoes_documentos","*","where id_evolucao=$id_evolucao and lixo=0");
					if($sql->rows) {
						$geral=mysqli_fetch_object($sql->mysqry);
					}

					$vSQLGeral="id_evolucao=$id_evolucao,
								data='".invDate($data)."',
								id_documento='".$documento->id."',
								texto='".addslashes(utf8_decode($texto))."',
								id_usuario=$usr->id";

					if(is_object($geral)) {
						$sql->update($_p."pacientes_evolucoes_documentos",$vSQLGeral,"where id=$geral->id");
					} else {
						$sql->add($_p."pacientes_evolucoes_documentos",$vSQLGeral);
					}

					if(generatePDF($id_evolucao)) {
						$rtn=array('success'=>true);
					} else {
						$rtn=array('success'=>false,'error'=>'Algum erro ocorreu durante a geração do PDF! Favor contate nossa equipe de suporte.');
					}
				} else {
					$rtn=array('success'=>false,'error'=>$erro);
				}
			}
		# Procedimentos
			else if($_POST['ajax']=="asProcedimentosHistorico") {
				$_usuarios=array();
				$sql->consult($_p."colaboradores","id,nome","order by nome asc");
				while($x=mysqli_fetch_object($sql->mysqry)) {
					$_usuarios[$x->id]=$x;
				}


				$procedimentoAEvoluir='';
				if(isset($_POST['id_procedimento_aevoluir']) and is_numeric($_POST['id_procedimento_aevoluir'])) {
					$sql->consult($_p."pacientes_tratamentos_procedimentos_evolucao","*","where id='".$_POST['id_procedimento_aevoluir']."'");
					if($sql->rows) {
						$procedimentoAEvoluir=mysqli_fetch_object($sql->mysqry);
					}


					if(is_object($procedimentoAEvoluir)) {
						$historico=array();

						$sql->consult($_p."pacientes_tratamentos_procedimentos_evolucao_historico","*","where id_procedimento_aevoluir=$procedimentoAEvoluir->id and lixo=0 order by data desc");
						if($sql->rows) {
							$diasemana = array('Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sabado');
							while($x=mysqli_fetch_object($sql->mysqry)) {

								
								$diaSemana=isset($diasemana[date('w', strtotime($x->data))])?" (".$diasemana[date('w', strtotime($x->data))].") ":'';

								$historico[]=array('data'=>date('d/m/Y',strtotime($x->data))." ".date('H:i',strtotime($x->data)),
													'id_usuario'=>$x->id_usuario,
													'usuario'=>encodingToJson(utf8_encode($x->usuario)),
													'obs'=>str_replace("strong","em",encodingToJson(utf8_encode($x->obs))),
													'idTP'=>(int)$x->id_tratamento_procedimento,
													'id'=>$x->id);
							}
						}

						$rtn=array('success'=>true,'historico'=>$historico);
					} else {
						$rtn=array('success'=>false,'error'=>'Procedimento a evoluir não encontrado!');
					}
				}
			}
			else if($_POST['ajax']=="asProcedimentosHistoricoAdicionar") {
				$_usuarios=array();
				$sql->consult($_p."colaboradores","id,nome","order by nome asc");
				while($x=mysqli_fetch_object($sql->mysqry)) {
					$_usuarios[$x->id]=$x;
				}


				$procedimentoAEvoluir='';
				if(isset($_POST['id_procedimento_aevoluir']) and is_numeric($_POST['id_procedimento_aevoluir'])) {
					$sql->consult($_p."pacientes_tratamentos_procedimentos_evolucao","*","where id='".$_POST['id_procedimento_aevoluir']."'");
					if($sql->rows) {
						$procedimentoAEvoluir=mysqli_fetch_object($sql->mysqry);
					}
				}

				$evolucao='';
				if(isset($_POST['id_evolucao']) and is_numeric($_POST['id_evolucao'])) {
					$sql->consult($_p."pacientes_evolucoes","*","where id=".$_POST['id_evolucao']);
					if($sql->rows) {
						$evolucao=mysqli_fetch_object($sql->mysqry);
					}
				}


				$obs = (isset($_POST['obs']) and !empty($_POST['obs'])) ? $_POST['obs'] : '';

				if(is_object($procedimentoAEvoluir)) {
					if(!empty($obs)) {
						// adiciona obs ao historico
						$vSQL="data=now(),
								id_usuario=$usr->id,
								usuario='".addslashes($usr->nome)."',
								obs='".addslashes(utf8_decode($obs))."',
								id_tratamento_procedimento=$procedimentoAEvoluir->id_tratamento_procedimento,
								id_procedimento_aevoluir=$procedimentoAEvoluir->id";

						if(is_object($evolucao)) $vSQL.=",id_evolucao='$evolucao->id'";

						$sql->add($_p."pacientes_tratamentos_procedimentos_evolucao_historico",$vSQL);

						// lista todos historicos
						$historico=array();
						$sql->consult($_p."pacientes_tratamentos_procedimentos_evolucao_historico","*","where id_procedimento_aevoluir=$procedimentoAEvoluir->id and lixo=0");
						if($sql->rows) {
							$diasemana = array('Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sabado');
							while($x=mysqli_fetch_object($sql->mysqry)) {

								
								$diaSemana=isset($diasemana[date('w', strtotime($x->data))])?" (".$diasemana[date('w', strtotime($x->data))].") ":'';

								$historico[]=array('data'=>date('d/m/Y',strtotime($x->data))." ".date('H:i',strtotime($x->data)),
													'id_usuario'=>$x->id_usuario,
													'usuario'=>isset($_usuarios[$x->id_usuario])?utf8_encode($_usuarios[$x->id_usuario]->nome):'Desconhecido',
													'obs'=>str_replace("strong","em",encodingToJson(utf8_encode($x->obs))),
													'idTP'=>(int)$x->id_tratamento_procedimento,
													'id'=>$x->id);
							}
						}

						$rtn=array('success'=>true,'historico'=>$historico);
					} else {
						$rtn=array('success'=>false,'error'=>'Digite a observação que deseja adicionar ao histórico!');
					}
				} else {
					$rtn=array('success'=>false,'error'=>'Procedimento a evoluir não encontrado!');
				}
			}
			else if($_POST['ajax']=="asProcedimentosPersistir") {
				
				$paciente = '';
				if(isset($_POST['id_paciente']) and is_numeric($_POST['id_paciente'])) {
					$sql->consult($_p."pacientes","id,nome","where id=".$_POST['id_paciente']);
					if($sql->rows) $paciente=mysqli_fetch_object($sql->mysqry);
				}	

				$profissional = '';
				if(isset($_POST['id_profissional']) and is_numeric($_POST['id_profissional'])) {
					$sql->consult($_p."colaboradores","id,nome","where id=".$_POST['id_profissional']);
					if($sql->rows) $profissional=mysqli_fetch_object($sql->mysqry);
				}



				$procedimentos = (isset($_POST['procedimentos']) and is_array($_POST['procedimentos'])) ? $_POST['procedimentos'] : '';
				$obs = (isset($_POST['obs']) and !empty($_POST['obs'])) ? $_POST['obs'] :'';
				$dataEvolucao = date('Y-m-d H:i');
				if(isset($_POST['dataEvolucao']) and !empty($_POST['dataEvolucao'])) {
					list($dt,$hr) = explode(" ",$_POST['dataEvolucao']);
					list($dia,$mes,$ano) = explode("/",$dt);
					list($hra,$min) = explode(":",$hr);

					if(checkdate($mes, $dia, $ano)) {
						$dataEvolucao=$ano."-".$mes."-".$dia." ".$hra.":".$min;
					}
				}

				if(empty($obs) and empty($procedimentos)) {
					$rtn=array('success'=>false,'error'=>'Escreva a evolução geral ou adicione pelo menos um procedimento aprovado!');
				} else {
					if(is_object($paciente)) {

						# Cria evolução
							$vSQLEvolucao="data=now(),
											id_tipo=2, 
											id_paciente=$paciente->id,
											id_usuario=$usr->id,
											id_profissional=$profissional->id,
											data_evolucao='".$dataEvolucao."'";

							$sql->add($_p."pacientes_evolucoes",$vSQLEvolucao);
							$id_evolucao=$sql->ulid;

						# Evolução da Observação Geral
							if(!empty($obs)) {
								$vSQLEvolucaoProcedimento="data=now(),
															id_evolucao=$id_evolucao,
															id_tipo=2,
															id_paciente=$paciente->id,
															obs='".utf8_decode(addslashes($obs))."'";
								$sql->add($_p."pacientes_evolucoes_procedimentos",$vSQLEvolucaoProcedimento);
							}

						# Evolução dos Procedimentos

							if(is_array($procedimentos) and count($procedimentos)>0) {
								foreach($procedimentos as $v) {
									$v=(object)$v;	

									// consulta procedimento que deve ser evoluido
									$sql->consult($_p."pacientes_tratamentos_procedimentos_evolucao","*","where id=$v->id_procedimento_aevoluir and 
																												numero='$v->numero' and 
																												numeroTotal='$v->numeroTotal' and 
																												lixo=0");
										
									if($sql->rows) {
										$procedimentoAEvoluir=mysqli_fetch_object($sql->mysqry);

										// consulta procedimento que foi aprovado
										$sql->consult($_p."pacientes_tratamentos_procedimentos","*","where id=$procedimentoAEvoluir->id_tratamento_procedimento");
										if($sql->rows) {
											$procedimentoAprovado=mysqli_fetch_object($sql->mysqry);
											$procedimentosEvoluidos[]=array('procedimentoAEvoluir'=>$procedimentoAEvoluir,
																			'procedimentoAprovado'=>$procedimentoAprovado,
																			'procedimentoEvolucao'=>$v,
																			'id_procedimento_evolucao'=>isset($v->id)?$v->id:0);


											
										}
									} else {
										$erro='Procedimento '.$v->titulo.' não foi encontrado para baixa de evolução!';
									}
								}


								if(count($procedimentosEvoluidos)>0) {
									
									// Procedimentos Evoluidos
									foreach($procedimentosEvoluidos as $obj) {
										$obj=(object)$obj;
										$procedimentoAEvoluir=$obj->procedimentoAEvoluir; // pacientes_tratamentos_procedimentos_evolucao
										$procedimentoAprovado=$obj->procedimentoAprovado; // pacientes_tratamentos_procedimentos
										$procedimentoEvolucao=$obj->procedimentoEvolucao; // procedimentoEvolucao
										$id_procedimento_evolucao=isset($obj->id_procedimento_evolucao)?$obj->id_procedimento_evolucao:0;

										// atualiza observacoes vinculado a evolucao
										$where="where id_tratamento_procedimento=$procedimentoAprovado->id and 
														id_procedimento_aevoluir=$procedimentoAEvoluir->id and 
														data>='".date('Y-m-d')." 00:00:00' and id_evolucao=0";
									
										$sql->update($_p."pacientes_tratamentos_procedimentos_evolucao_historico","id_evolucao=$id_evolucao",$where);

										$evProc=''; // pacientes_evolucao_procedimentos
										if($id_procedimento_evolucao>0) {
											$sql->consult($_p."pacientes_evolucoes_procedimentos","*","where id=$id_procedimento_evolucao and id_paciente=$paciente->id and lixo=0");
											if($sql->rows) {
												$evProc=mysqli_fetch_object($sql->mysqry);
											}
										}

										//var_dump($obj);die();

										$vSQLProc="data=now(),
													id_paciente=$paciente->id,
													id_evolucao=$id_evolucao,
													id_tratamento_procedimento='".addslashes($procedimentoAprovado->id)."',
													id_procedimento_aevoluir='".addslashes($procedimentoAEvoluir->id)."',
													id_procedimento='".addslashes($procedimentoAprovado->id_procedimento)."',
													id_tratamento='".addslashes($procedimentoAprovado->id_tratamento)."',
													id_profissional='".addslashes($profissional->id)."',
													status='".addslashes($procedimentoEvolucao->statusEvolucao)."',
													id_plano='$procedimentoAprovado->id_plano',
													id_opcao='$procedimentoAprovado->id_opcao',
													numero='$procedimentoAEvoluir->numero',
													numeroTotal='$procedimentoAEvoluir->numeroTotal',
													opcao='$procedimentoAprovado->opcao'";

										
										if(empty($evProc)) {
											$sql->consult($_p."pacientes_evolucoes_procedimentos","*","WHERE data > NOW() - INTERVAL 1 MINUTE and 
																												id_paciente=$paciente->id and 
																												id_evolucao=$id_evolucao and 
																												id_procedimento=$procedimentoAprovado->id_procedimento and 
																												id_opcao=$procedimentoAprovado->id_opcao and 
																												numero='$procedimentoAEvoluir->numero' and 
																												numeroTotal='$procedimentoAEvoluir->numeroTotal' and 

																												id_tratamento='".addslashes($procedimentoAprovado->id_tratamento)."'");	
											// echo $vSQLProc." ----> ".($sql->rows)."<BR>";;
											if($sql->rows) {
												$x=mysqli_fetch_object($sql->mysqry);
												$sql->update($_p."pacientes_evolucoes_procedimentos",$vSQLProc,"where id=$x->id");
											} else { 
												$sql->add($_p."pacientes_evolucoes_procedimentos",$vSQLProc);
											}
										} else {
											$sql->update($_p."pacientes_evolucoes_procedimentos",$vSQLProc,"where id=$evProc->id");
										}


										// atualiza status de Tratamento / Procedimento
										if($procedimentoEvolucao->statusEvolucao=="iniciar" or 
												$procedimentoEvolucao->statusEvolucao=="iniciado" or 
												$procedimentoEvolucao->statusEvolucao=="finalizado" or 
												$procedimentoEvolucao->statusEvolucao=="cancelado") {


											if($procedimentoAEvoluir->status_evolucao==$procedimentoEvolucao->statusEvolucao) continue;
											$novoStatus='';

											if($procedimentoEvolucao->statusEvolucao=="iniciar") $novoStatus=utf8_decode("Não iniciado");
											else if($procedimentoEvolucao->statusEvolucao=="iniciado") $novoStatus="Em Tratamento";
											else if($procedimentoEvolucao->statusEvolucao=="finalizado") $novoStatus="Finalizado";
											else if($procedimentoEvolucao->statusEvolucao=="cancelado") $novoStatus="Cancelado";

											if(!empty($novoStatus)) {
												$sql->update($_p."pacientes_tratamentos_procedimentos_evolucao","status_evolucao='".$procedimentoEvolucao->statusEvolucao."'","where id='".$procedimentoAEvoluir->id."'");


												$vSQLHistorico="data=now(),
																id_usuario='".addslashes($usr->id_usuario)."',
																usuario='".addslashes(($usr->nome))."',
																obs='Alterou status para <b>$novoStatus</b>',
																id_tratamento_procedimento=$procedimentoAprovado->id,
																id_procedimento_aevoluir=$procedimentoAEvoluir->id,
																id_evolucao='".addslashes($id_evolucao)."'";

												$sql->add($_p."pacientes_tratamentos_procedimentos_evolucao_historico",$vSQLHistorico);
											}
										}
										
									}	

									$bi = new BI(array('prefixo'=>$_p));
									$bi->classificaTodos();

								}
							}


						$rtn=array('success'=>true);

					} else {
						$rtn=array('success'=>false,'error'=>'Paciente não encontrado!');
					}

				}
			}
			else if($_POST['ajax']=="asProcedimentosStatusAlterar") {
				$paciente = '';
				if(isset($_POST['id_paciente']) and is_numeric($_POST['id_paciente'])) {
					$sql->consult($_p."pacientes","id,nome","where id=".$_POST['id_paciente']);
					if($sql->rows) $paciente=mysqli_fetch_object($sql->mysqry);
				}

				$procedimentoAEvoluir = '';
				if(is_object($paciente) and isset($_POST['id_procedimento_aevoluir']) and is_numeric($_POST['id_procedimento_aevoluir'])) {
					$sql->consult($_p."pacientes_tratamentos_procedimentos_evolucao","*","where id='".$_POST['id_procedimento_aevoluir']."' and id_paciente=$paciente->id");
					if($sql->rows) {
						$procedimentoAEvoluir=mysqli_fetch_object($sql->mysqry);
					}
				}	

				$evolucao = '';
				if(is_object($paciente) and isset($_POST['id_evolucao']) and is_numeric($_POST['id_evolucao'])) {
					$sql->consult($_p."pacientes_evolucoes","id,data","where id=".$_POST['id_evolucao']." and id_paciente=$paciente->id and lixo=0");
					if($sql->rows) {
						$evolucao=mysqli_fetch_object($sql->mysqry);
					}
				}

				if(isset($_POST['status'])) {

					$status=$_POST['status'];
					$novoStatus='';
					if($status=="iniciar") $novoStatus=utf8_decode("Não iniciado");
					else if($status=="iniciado") $novoStatus="Em Tratamento";
					else if($status=="finalizado") $novoStatus="Finalizado";
					else if($status=="cancelado") $novoStatus="Cancelado";

					if(empty($novoStatus)) $status='';

				}


				$erro='';

				if(empty($paciente)) $erro='Paciente não encontrado!';
				else if(empty($procedimentoAEvoluir)) $erro='Procedimento evoluído não encontrado!';
				else if(empty($status)) $erro='Status não definido!';

				if(empty($erro)) {	


					$vSQLHistorico="data=now(),
									id_usuario='".addslashes($usr->id_usuario)."',
									usuario='".addslashes(($usr->nome))."',
									obs='Alterou status para <b>$novoStatus</b>',
									id_procedimento_aevoluir=$procedimentoAEvoluir->id,
									id_evolucao='".addslashes($evolucao->id)."'";

					$sql->add($_p."pacientes_tratamentos_procedimentos_evolucao_historico",$vSQLHistorico);


					$sql->update($_p."pacientes_tratamentos_procedimentos_evolucao","status_evolucao='".addslashes($status)."'","where id=$procedimentoAEvoluir->id");
					$rtn=array('success'=>true);

				} else {
					$rtn=array('success'=>false,
								'error'=>$erro);
				}
			}
			else if($_POST['ajax']=="asProcedimentosAprovados") {
				
				$paciente = '';
				if(isset($_POST['id_paciente']) and is_numeric($_POST['id_paciente'])) {
					$sql->consult($_p."pacientes","id,nome","where id=".$_POST['id_paciente']);
					if($sql->rows) $paciente=mysqli_fetch_object($sql->mysqry);
				}

				$agenda = '';
				$id_profissional=0;
				if(is_object($paciente) and isset($_POST['id_agenda']) and is_numeric($_POST['id_agenda'])) {
					$sql->consult($_p."agenda","profissionais","where id=".$_POST['id_agenda']);
					if($sql->rows) {
						$agenda=mysqli_fetch_object($sql->mysqry);

						$aux = explode(",",$agenda->profissionais);
						foreach($aux as $x) {
							if(!empty($x) and is_numeric($x)) $id_profissional=$x;
						}
					}
				}

				$_procedimentos=array();
				$sql->consult($_p."parametros_procedimentos","id,titulo","where  lixo=0");
				while($x=mysqli_fetch_object($sql->mysqry)) {
					$_procedimentos[$x->id]=$x;
				}

				$tratamentosIds=$_tratamentos=array();
				if(isset($paciente) and is_object($paciente)) {
					$sql->consult($_p."pacientes_tratamentos","*","where id_paciente=$paciente->id and  status='APROVADO' and lixo=0");
					while($x=mysqli_fetch_object($sql->mysqry)) {
						$tratamentosIds[]=$x->id;
						$_tratamentos[$x->id]=$x;
					}
				}
				$procedimentosIds=$_procedimentosAprovadosASerEvoluido=$tratamentosProcedimentosIds=array();

				if(count($tratamentosIds)>0) {
					$where="where lixo=0 and situacao='aprovado' and id_tratamento IN (".implode(",",$tratamentosIds).")";
					$_procedimentosDeTratamentosAprovados=array();
					$sql->consult($_p."pacientes_tratamentos_procedimentos","*",$where);
					while($x=mysqli_fetch_object($sql->mysqry)) {
						$procedimentosIds[]=$x->id_procedimento;
						$tratamentosProcedimentosIds[]=$x->id;
						$_procedimentosDeTratamentosAprovados[$x->id]=$x;
					}

					if(count($tratamentosProcedimentosIds)>0) {
						$sql->consult($_p."pacientes_tratamentos_procedimentos_evolucao","*","where id_tratamento_procedimento IN (".implode(",",$tratamentosProcedimentosIds).") and status_evolucao NOT IN ('cancelado','finalizado') and lixo=0");
						while($x=mysqli_fetch_object($sql->mysqry)) {
							if(isset($_procedimentosDeTratamentosAprovados[$x->id_tratamento_procedimento])) {
								$procAprovado=$_procedimentosDeTratamentosAprovados[$x->id_tratamento_procedimento];

								$_procedimentosAprovadosASerEvoluido[$procAprovado->id_tratamento][$x->id]=$x;
							}
						}
					}
				}


				$procedimentos=array();
				foreach($_procedimentosAprovadosASerEvoluido as $id_tratamento=>$regs) {
					//echo '<optgroup label="'.utf8_encode($_tratamentos[$id_tratamento]->titulo).'">';

					$tratamentoProcedimentos=array();
					foreach($regs as $v) {
						$disabled='';
						if(isset($procedimentosAEvoluirIds) and in_array($v->id,$procedimentosAEvoluirIds)) $disabled=" disabled";;
						if(isset($_procedimentos[$v->id_procedimento])) {
							$procedimento=$_procedimentos[$v->id_procedimento];
							$profissionalIniciais='';
							$profissionalCor='#ccc';
							if(isset($_profissionais[$v->id_profissional])) {
								$p=$_profissionais[$v->id_profissional];
								$profissionalIniciais=$p->calendario_iniciais;
								$profissionalCor=$p->calendario_cor;

							}
							$complemento='';
							if($v->numeroTotal>1) $complemento.=' - '.utf8_encode($v->numero."/".$v->numeroTotal);

							//	id_tratamento_procedimento => Procedimento de tratamento aprovado
							if(isset($_procedimentosDeTratamentosAprovados[$v->id_tratamento_procedimento])) {
								$procedimentoAprovado=$_procedimentosDeTratamentosAprovados[$v->id_tratamento_procedimento];
								if(!empty($procedimentoAprovado->opcao)) $complemento.=" - ".utf8_encode($procedimentoAprovado->opcao)
									;

								$tratamentoProcedimentos[]=array('id'=>$v->id,
																		'id_procedimento'=>$v->id_procedimento,
																		'numero'=>$v->numero,
																		'numeroTotal'=>$v->numeroTotal,
																		'opcao'=>strip_tags(utf8_encode($procedimentoAprovado->opcao)),
																		'plano'=>utf8_encode($procedimentoAprovado->plano),
																		'profissionalCor'=>$profissionalCor,
																		'id_profissional'=>$v->id_profissional,
																		'profissionalIniciais'=>$profissionalIniciais,
																		'statusEvolucao'=>$v->status_evolucao,
																		'titulo'=>utf8_encode($procedimento->titulo)." ".$complemento,
																		'id_tratamento_procedimento'=>$procedimentoAprovado->id,
																	 	'disabled'=>$disabled);
								/*echo '<option value="'.$v->id.'" 
												data-id_procedimento="'.$v->id_procedimento.'" 
												data-numero="'.$v->numero.'" 
												data-numeroTotal="'.$v->numeroTotal.'" 
												data-opcao="'.strip_tags(utf8_encode($procedimentoAprovado->opcao)).'" 
												data-plano="'.utf8_encode($procedimentoAprovado->plano).'" 
												data-profissionalCor="'.$profissionalCor.'" 
												data-id_profissional="'.$v->id_profissional.'" 
												data-profissionalIniciais="'.$profissionalIniciais.'"  
												data-statusEvolucao="'.$v->status_evolucao.'" 
												data-titulo="'.utf8_encode($procedimento->titulo).'" 
												data-id_tratamento_procedimento="'.$procedimentoAprovado->id.'"'.$disabled.'>'.utf8_encode($procedimento->titulo)." ".$complemento.'</option>';*/
							}
					
						}
					}

					$procedimentos[]=array('titulo'=>utf8_encode($_tratamentos[$id_tratamento]->titulo),
												'procedimentos'=>$tratamentoProcedimentos); 
				}



				$rtn=array('success'=>true,
							'procedimentos'=>$procedimentos,
							'id_profissional'=>$id_profissional);
			}
		# Próxima Consulta
			else if($_POST['ajax']=="asRelacionamentoPacienteQueroAgendar") {
				$erro='';
				$paciente = '';
				if(isset($_POST['id_paciente']) and is_numeric($_POST['id_paciente'])) {
					$sql->consult($_p."pacientes","*","where id='".$_POST['id_paciente']."'");
					if($sql->rows) { 
						$paciente=mysqli_fetch_object($sql->mysqry);
					}
				}

				$id_agenda_origem = (isset($_POST['id_agenda_origem']) and is_numeric($_POST['id_agenda_origem']))?$_POST['id_agenda_origem']:0;
				
				$agendaData='';
				if(isset($_POST['agenda_data']) and !empty($_POST['agenda_data'])) {
					list($dia,$mes,$ano)=explode("/",$_POST['agenda_data']);
					if(checkdate($mes, $dia, $ano)) {
						$agendaData=$ano."-".$mes."-".$dia;

						if(isset($_POST['agenda_hora']) and !empty($_POST['agenda_hora']) and strlen($_POST['agenda_hora'])==5) {
							$agendaData.=" ".$_POST['agenda_hora'];
						}
					}
				}

				$profissional='';
				if(isset($_POST['id_profissional']) and is_numeric($_POST['id_profissional'])) {
					$sql->consult($_p."colaboradores","id,nome","where id='".$_POST['id_profissional']."'");
					if($sql->rows) {
						$profissional=mysqli_fetch_object($sql->mysqry);
					}
				}

				$cadeira='';
				if(isset($_POST['id_cadeira']) and is_numeric($_POST['id_cadeira'])) {
					$sql->consult($_p."parametros_cadeiras","*","where id='".$_POST['id_cadeira']."' and lixo=0");
					if($sql->rows) {
						$cadeira=mysqli_fetch_object($sql->mysqry);
					}
				}

				$agenda_duracao='';
				if(isset($_POST['agenda_duracao']) and is_numeric($_POST['agenda_duracao'])) {
					$agenda_duracao=$_POST['agenda_duracao'];
				}

				$obs = (isset($_POST['obs']) and !empty($_POST['obs']))?addslashes(utf8_decode($_POST['obs'])):'';

				if(empty($paciente)) $erro='Paciente não encontrado';
				else if(empty($agendaData)) $erro='Data/horário não definidos';
				else if(empty($profissional)) $erro='Profissional não encontrado';
				else if(empty($cadeira)) $erro='Cadeira não encontrada!';
				else if(empty($agenda_duracao)) $erro='Duração não definido!';

				if(empty($erro)) {

					$agendaFinal=date('Y-m-d H:i:s',strtotime($agendaData." + $agenda_duracao minutes"));
					$idStatusNovo=1; // a confirmar

					$vSQL="id_status=$idStatusNovo,
							id_paciente=$paciente->id,
							agenda_data='".$agendaData."',
							agenda_data_original='".$agendaData."',
							agenda_duracao='".$agenda_duracao."',
							agenda_data_final='".$agendaFinal."',
							id_cadeira='".$cadeira->id."',
							obs='".$obs."',
							data_atualizacao=now(),
							data=now(),
							id_usuario=$usr->id,
							profissionais=',$profissional->id,'";

					
					$sql->consult($_p."agenda","id","where id_paciente=$paciente->id and 
															agenda_data='".$agendaData."' and 
															agenda_duracao='".$agenda_duracao."' and
															id_cadeira='".$cadeira->id."' and 
															lixo=0");
					if($sql->rows==0) {
						$sql->add($_p."agenda",$vSQL);
						$id_agenda=$sql->ulid;

						$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='insert',vsql='".addslashes($vSQL)."',tabela='".$_p."agenda',id_reg='".$id_agenda."'");

						$vSQLHistorico="data=now(),
											id_usuario=$usr->id,
											evento='agendaNovo',
											id_paciente=".$paciente->id.",
											id_agenda=$id_agenda,
											id_agenda_origem=$id_agenda_origem,
											id_status_antigo=0,
											id_status_novo=".$idStatusNovo;
						$sql->add($_p."pacientes_historico",$vSQLHistorico);
						
					}

					$rtn=array('success'=>true);
				} else {
					$rtn=array('success'=>false,'error'=>$erro);
				}
			}
			else if($_POST['ajax']=="asRelacionamentoPacienteHorarios") {
			
				$agenda = '';
				if(isset($_POST['id_agenda']) and is_numeric($_POST['id_agenda'])) {
					$sql->consult($_p."agenda","*","where id='".$_POST['id_agenda']."'");
					if($sql->rows) { 
						$agenda=mysqli_fetch_object($sql->mysqry);
					}
				}

				$data = '';
				if(isset($_POST['agenda_data']) and !empty($_POST['agenda_data'])) {
					list($dia,$mes,$ano)=explode("/",$_POST['agenda_data']);
					if(checkdate($mes, $dia, $ano)) { 
						$data="$ano-$mes-$dia";
						$dia=date('w',strtotime($data));
					}
	 			}

	 			$profissional = '';
	 			if(isset($_POST['id_profissional']) and is_numeric($_POST['id_profissional'])) {
	 				$sql->consult($_p."colaboradores","*","where id='".$_POST['id_profissional']."'");
	 				if($sql->rows) {
	 					$profissional=mysqli_fetch_object($sql->mysqry);
	 				}
	 			}

	 			$cadeira = '';
	 			if(isset($_POST['id_cadeira']) and is_numeric($_POST['id_cadeira'])) {
	 				$sql->consult($_p."parametros_cadeiras","*","where id='".$_POST['id_cadeira']."' and lixo=0");
	 				if($sql->rows) {
	 					$cadeira=mysqli_fetch_object($sql->mysqry);
	 				}
	 			}

				$agenda_duracao = (isset($_POST['agenda_duracao']) and is_numeric($_POST['agenda_duracao']))?$_POST['agenda_duracao']:0;

				if(is_object($agenda) or empty($agenda)) {
					if(!empty($data)) {
						if(is_object($profissional)) {
							if(is_object($cadeira)) {

								// verifica se o profissional atende nesta cadeira
								$sql->consult($_p."profissionais_horarios","*","where id_profissional=$profissional->id and lixo=0");
								if($sql->rows) {
									while($x=mysqli_fetch_object($sql->mysqry)) {
										$cadeiraHorarios[$x->id_cadeira][$x->dia][]=$x;
									}
								}

								if(isset($cadeiraHorarios[$cadeira->id])) {

									if(isset($cadeiraHorarios[$cadeira->id][$dia])) {

										$dataInicio=$data." 07:00:00";	
										$dataFim=$data." 23:59:59";

										$dataInicio=$dataFim="";


										// retorna o horario mais cedo e mais tarde que a cadeira tem disponibilidade
										foreach($cadeiraHorarios[$cadeira->id][$dia] as $x) {
											$dtI=$data." ".$x->inicio;
											$dtF=$data." ".$x->fim;


											if(empty($dataInicio) and empty($dataFim)) {
												$dataInicio=$dtI;
												$dataFim=$dtF;
											} else {
												if(strtotime($dataInicio)>strtotime($dtI)) {
													$dataInicio=$dtI;
												}


												if(strtotime($dataFim)<strtotime($dtF)) {
													$dataFim=$dtF;
												}
											}
										}



										//echo $dataInicio." - $dataFim -> $tempo\n\n";
										$horariosDisponiveis=array();
										$di=$dataInicio;



										// fiz isso para checar de 1 em 1 minuto, mas na hora de rodar coloquei de 30 em 30 minutos
										/*do {
											$df=date('Y-m-d H:i:s',strtotime($di." + $agenda_duracao minutes"));

											echo date('H:i',strtotime($di))." - ".date('H:i',strtotime($df))."\n";


											$di=date('Y-m-d H:i:s',strtotime($di." + 1 minutes"));
										} while(strtotime($df)<strtotime($dataFim));
										die();*/



										do {
											
											$df=date('Y-m-d H:i:s',strtotime($di." + $agenda_duracao minutes"));

											
											// nova condicao de intersessao captada na internet
											$where="WHERE (agenda_data>='$data 00:00:00' and agenda_data<='$data 23:59:59') and 
															(DATE_ADD(agenda_data, INTERVAL agenda_duracao MINUTE)>'$di' and agenda_data<'$df')";
											//$where="WHERE (agenda_data>='$data 00:00:00' and agenda_data<='$data 23:59:59')";
											$where.=" and profissionais like '%,$profissional->id,%' and id_status NOT IN (3,4) and lixo=0";
											$sql->consult($_p."agenda","agenda_data,DATE_ADD(agenda_data, INTERVAL $agenda_duracao MINUTE) as agenda_data_fim,agenda_duracao",$where);
											//echo $where."->".$sql->rows."\n";
											//$x=mysqli_fetch_object($sql->mysqry);
											//echo $x->agenda_data." - ".$x->agenda_data_fim." -> ".$x->agenda_duracao;die();
											if($sql->rows==0) {
												/*$where="WHERE (agenda_data>='$data 00:00:00' and agenda_data<='$data 23:59:59') and 
															(
																('$di'<=agenda_data && '$df'>=DATE_ADD(agenda_data, INTERVAL agenda_duracao MINUTE)) or 
																('$di'>=agenda_data && '$df'<=DATE_ADD(agenda_data, INTERVAL agenda_duracao MINUTE)) or 
																('$di'<=agenda_data && '$df'>agenda_data && '$df'<=DATE_ADD(agenda_data, INTERVAL agenda_duracao MINUTE))
															)";

												$where.=" and id_cadeira=$cadeira->id and id_status NOT IN (3,4) and lixo=0";
												$sql->consult($_p."agenda","agenda_data,DATE_ADD(agenda_data, INTERVAL $agenda_duracao MINUTE) as agenda_data_fim,agenda_duracao",$where);
												//echo $where."->".$sql->rows."\n";
												if($sql->rows==0) {*/
													$horariosDisponiveis[]=date('H:i',strtotime($di));
												//}
											}


											$di=date('Y-m-d H:i:s',strtotime($di." + 30 minutes")); // de 30 em 30 minutos mas pode mexer 
										} while(strtotime($df)<strtotime($dataFim));//while(strtotime($dataInicio)<strtotime($dataFim));

										//var_dump($horariosDisponiveis);die();

										$horarios = new Horarios(array('prefixo'=>$_p));

										$horariosDisponiveisNew=array();

										// remove horarios que a cadeira nao tem disponibilidade
										foreach($horariosDisponiveis as $v) {

											$strData = date('Y-m-d')." $v:00";
											$dtAux = date('Y-m-d H:i',strtotime($strData));

											$attr=array('id_colaborador'=>$profissional->id,
														'id_cadeira'=>$cadeira->id,
														'id_horario'=>0,
														'diaSemana'=>$dia,
														'inputHoraInicio'=>$v,
														'inputHoraFim'=>date('H:i',strtotime($dtAux." + $agenda_duracao minutes")));

											
											if(!$horarios->cadeiraHorariosIntercecao($attr)===true) {
												$horariosDisponiveisNew[]=$v; 
											} 
										}

										
										
										$rtn=array('success'=>true,'horariosDisponiveis'=>$horariosDisponiveisNew);
									} else {
										$rtn=array('success'=>false,'error'=>'Sem atendimento para este dia');
									}
								} else {
									$rtn=array('success'=>false,'error'=>'Sem atendimento para este consultório');
								}


							} else {
								$rtn=array('success'=>false,'error'=>'Cadeira/Consultório não encontrado!');
							}
						} else {
							$rtn=array('success'=>false,'error'=>'Profissional não encontrado!');
						}
					} else {
						$rtn=array('success'=>false,'error'=>'Data não válida');
					}
				} else {
					$rtn=array('success'=>false,'error'=>'Agendamento não encontrado');
				}
			} 
			else if($_POST['ajax']=="proximaConsulta") {
				$_profissionais=array();
				$sql->consult($_p."colaboradores","id,nome,calendario_iniciais,foto,calendario_cor,check_agendamento","where lixo=0 order by nome asc");
				while($x=mysqli_fetch_object($sql->mysqry)) $_profissionais[$x->id]=$x;

				$_cadeiras=array();
				$sql->consult($_p."parametros_cadeiras","*","where lixo=0  order by titulo asc");
				while($x=mysqli_fetch_object($sql->mysqry)) $_cadeiras[$x->id]=$x;

				$agenda = $paciente = '';
				if(isset($_POST['id_agenda']) and is_numeric($_POST['id_agenda'])) {
					$sql->consult($_p."agenda","*","where id=".$_POST['id_agenda']." and lixo=0");
					if($sql->rows) {
						$agenda=mysqli_fetch_object($sql->mysqry);

						$sql->consult($_p."pacientes","*","where id=$agenda->id_paciente and lixo=0");
						if($sql->rows) {
							$paciente=mysqli_fetch_object($sql->mysqry);
						}
					}
				}

				if(is_object($agenda)) {
					if(is_object($paciente)) {

						if($paciente->data_nascimento!="0000-00-00") {
							$dob = new DateTime($paciente->data_nascimento);
							$now = new DateTime();
							$idade = $now->diff($dob)->y;
						} else $idade=0;

						/*$ft='';
						if(!empty($paciente->foto_cn)) {
							$ft=$_cloudinaryURL.'c_thumb,w_100,h_100/'.$paciente->foto_cn;
						}*/

						$ft='';
						if(!empty($paciente->foto_cn)) {
							$ft=$_cloudinaryURL.'c_thumb,w_100,h_100/'.$paciente->foto_cn;
						} else if(!empty($paciente->foto)) {
							$ft=$_wasabiURL."arqs/clientes/".$paciente->id.".jpg";
						}


						$agendamentosFuturos=array();

					

						$_pacientesAgendamentos=array();
						$sql->consult($_p."agenda","*","where id_paciente=$paciente->id and agenda_data>'".date('Y-m-d')."' and id_status IN (1,2) and lixo=0 order by agenda_data");

						while($x=mysqli_fetch_object($sql->mysqry)) {

							// se for o mesmo agendamento que esta sendo editado
							if($x->id==$agenda->id) continue;

							$cor='';
							$iniciais='';


							$aux = explode(",",$x->profissionais);
							$profissionais=array();
							foreach($aux as $id_profissional) {
								if(!empty($id_profissional) and is_numeric($id_profissional)) {

									if(isset($_profissionais[$id_profissional])) {
										$cor=$_profissionais[$id_profissional]->calendario_cor;
										$iniciais=$_profissionais[$id_profissional]->calendario_iniciais;

										$profissionais[]=array('iniciais'=>$iniciais,'cor'=>$cor);
									}
								}

							}

							$_pacientesAgendamentos[$x->id_paciente][]=array('id_agenda'=>$x->id,
																				'obs'=>str_replace("'","`",utf8_encode($x->obs)),
																				'data'=>date('d/m/Y H:i',strtotime($x->agenda_data)),
																				'initDate'=>date('d/m/Y',strtotime($x->agenda_data)),
																				'cadeira'=>isset($_cadeiras[$x->id_cadeira])?utf8_encode($_cadeiras[$x->id_cadeira]->titulo):'',
																				'profissionais'=>$profissionais);
						}

					

						if(isset($_pacientesAgendamentos[$paciente->id])) {
							$agendamentosFuturos=$_pacientesAgendamentos[$paciente->id];
						}
						

						$idProfissional=0;
						if(!empty($agenda->profissionais)) {

							$aux = explode(",",$agenda->profissionais);

							foreach($aux as $x) {
								if(!empty($x) and is_numeric($x)) {
									$idProfissional=$x;
									break;
								}
							}
						}


						$rtn=array('success'=>true,
									'data'=>array('id_paciente'=>$paciente->id,
													'periodicidade_select'=>$paciente->periodicidade,
													'nome'=>utf8_encode($paciente->nome),
													'idade'=>$idade,
													'telefone1'=>$paciente->telefone1,
													'statusBI'=>isset($_codigoBI[$paciente->codigo_bi])?utf8_encode($_codigoBI[$paciente->codigo_bi]):"",			
													'musica'=>utf8_encode($paciente->musica),
													'ft'=>$ft,
													'periodicidade'=>isset($_pacientesPeriodicidade[$paciente->periodicidade])?$_pacientesPeriodicidade[$paciente->periodicidade]:$paciente->periodicidade,
													'agendamentosFuturos'=>$agendamentosFuturos,
													'id_profissional'=>$idProfissional

													)
									);

					} else {
						$rtn=array('success'=>false,'error'=>'Paciente não encontrado');
					} 
				} else {
					$rtn=array('success'=>false,'error'=>'Agendamento não encontrado');
				}
			}
			else if($_POST['ajax']=="proximaConsultaPersistir") {
				
				$paciente = '';
				if(isset($_POST['id_paciente']) and is_numeric($_POST['id_paciente'])) {
					$sql->consult($_p."pacientes","id","where id=".$_POST['id_paciente']);
					if($sql->rows) {
						$paciente=mysqli_fetch_object($sql->mysqry);
					}
				}

				if(is_object($paciente)) {

					$duracao=isset($_POST['duracao'])?addslashes($_POST['duracao']):'';
					$laboratorio=isset($_POST['laboratorio'])?addslashes($_POST['laboratorio']):'';
					$imagem=isset($_POST['imagem'])?addslashes($_POST['imagem']):'';
					$retorno=isset($_POST['retorno'])?addslashes($_POST['retorno']):'';
					$obs=isset($_POST['obs'])?utf8_decode(addslashes($_POST['obs'])):'';
					$profissionais=isset($_POST['profissionais'])?$_POST['profissionais']:'';
					$id_agenda_origem=(isset($_POST['id_agenda_origem']) and is_numeric($_POST['id_agenda_origem']))?$_POST['id_agenda_origem']:0;

					$vSQL="data=now(),
							id_colaborador=$usr->id,
							id_paciente=$paciente->id,
							duracao='$duracao',
							laboratorio='$laboratorio',
							imagem='$imagem',
							retorno='$retorno',
							obs='$obs',
							id_agenda_origem='$id_agenda_origem',
							profissionais='$profissionais'";


					$sql->add($_p."pacientes_proximasconsultas",$vSQL);

					$rtn=array('success'=>true);

				} else {
					$rtn=array('success'=>false,'error'=>'Paciente não encontrado');
				}
			}
			else if($_POST['ajax']=="prontuarioPersistir") {
			
				$paciente = '';
				if(isset($_POST['id_paciente']) and is_numeric($_POST['id_paciente'])) {
					$sql->consult($_p."pacientes","id","where id=".$_POST['id_paciente']);
					if($sql->rows) {
						$paciente=mysqli_fetch_object($sql->mysqry);
					}
				}


				if(is_object($paciente)) {

					$id_profissional=isset($_POST['id_profissional'])?addslashes($_POST['id_profissional']):0;
					$prontuario=isset($_POST['prontuario'])?addslashes($_POST['prontuario']):'';
					$dataProntuario='';
					if(isset($_POST['dataProntuario']) and !empty($_POST['dataProntuario'])) {

						$aux1 = @explode(" ",$_POST['dataProntuario']);
						$aux2 = @explode("/",$aux1[0]);

 
						if(checkdate($aux2[1], $aux2[0], $aux2[2])) {
							$dataProntuario=$aux2[2]."-".$aux2[1]."-".$aux2[0]." ".$aux1[1];
						}

					}

					if(empty($dataProntuario)) {
						$erro='Preencha uma data válida!';
					}
					else if($id_profissional==0) {
						$erro='Selecione o Profissional';
					} else if(empty($prontuario)) {
						$erro='Digite o prontuário';
					} 

					if(empty($erro)) {

						/*$vsql="data='".$dataProntuario."',id_usuario='".$id_profissional."',
								texto='".addslashes(utf8_decode($prontuario))."',
								id_paciente=$paciente->id";
						//echo $vsql;die();
						$sql->add($_p."pacientes_prontuarios",$vsql);
						$id_reg=$sql->ulid;
						$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='insert',vsql='".addslashes($vsql)."',vwhere='',tabela='".$_p."pacientes_prontuarios',id_reg='$id_reg'");*/

						// id_tipo = 9 -> geral
						$sql->consult($_p."pacientes_evolucoes","*","WHERE data > NOW() - INTERVAL 1 MINUTE and 
																								id_paciente=$paciente->id and
																								id_tipo=9 and  
																								id_usuario=$usr->id");	
						if($sql->rows) {
							$e=mysqli_fetch_object($sql->mysqry);
							$sql->update($_p."pacientes_evolucoes","id_profissional='$id_profissional'","where id=$e->id");
							$id_evolucao=$e->id;
						} else {
							$sql->add($_p."pacientes_evolucoes","data=now(),
																	id_tipo=9,
																	id_paciente=$paciente->id,
																	id_usuario=$usr->id,
																	id_profissional='".$id_profissional."'");
							$id_evolucao=$sql->ulid;
						}


						$geral='';
						$sql->consult($_p."pacientes_evolucoes_geral","*","where id_evolucao=$id_evolucao and lixo=0");
						if($sql->rows) {
							$geral=mysqli_fetch_object($sql->mysqry);
						}

						$vSQLGeral="id_evolucao=$id_evolucao,
									data='".$dataProntuario."',
									id_profissional='".$id_profissional."',
									texto='".addslashes(utf8_decode($prontuario))."',
									id_usuario=$usr->id";

						if(is_object($geral)) {
							$sql->update($_p."pacientes_evolucoes_geral",$vSQLGeral,"where id=$geral->id");
						} else {
							$sql->add($_p."pacientes_evolucoes_geral",$vSQLGeral);
						}

						$rtn=array('success'=>true);

					} else {
						$rtn=array('success'=>false,'error'=>$erro);
					}

				}  else {
					$rtn=array('success'=>false,'error'=>'Paciente não encontrado');
				}
			}
			else if($_POST['ajax']=="proximaConsultaAltaPeriodicidade") {
				$paciente = '';
				if(isset($_POST['id_paciente']) and is_numeric($_POST['id_paciente'])) {
					$sql->consult($_p."pacientes","id","where id=".$_POST['id_paciente']);
					if($sql->rows) {
						$paciente=mysqli_fetch_object($sql->mysqry);
					}
				}
				$periodicidade=(isset($_POST['periodicidade']) and is_numeric($_POST['periodicidade']))?$_POST['periodicidade']:0;
				$alta=(isset($_POST['alta']) and !empty($_POST['alta']))?$_POST['alta']:'';

				$id_agenda_origem=(isset($_POST['id_agenda_origem']) and is_numeric($_POST['id_agenda_origem']))?$_POST['id_agenda_origem']:0;

				if(is_object($paciente)) {

					if($periodicidade>0) {

						$vsql="periodicidade='$periodicidade',id_agenda_origem='$id_agenda_origem'";
						$vwhere="where id=$paciente->id";

						$sql->update($_p."pacientes",$vsql,$vwhere);
						$id_reg=$sql->ulid;
						$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='update',vsql='".addslashes($vsql)."',vwhere='".addslashes($vsql)."',tabela='".$_p."pacientes',id_reg='$id_reg'");


						// cria evolucao
						$sql->add($_p."pacientes_evolucoes","data=now(),
																	id_tipo=11,
																	id_paciente=$paciente->id,
																	id_usuario=$usr->id,
																	id_profissional='".$usr->id."'");
						$id_evolucao=$sql->ulid;



						$vSQL="id_evolucao=$id_evolucao,
									data=now(),
									id_profissional='".$usr->id."',
									texto='".addslashes(utf8_decode($alta))."',
									id_usuario=$usr->id";

						$sql->add($_p."pacientes_evolucoes_alta",$vSQL);
						

						$rtn=array('success'=>true);

					} else {
						$rtn=array('success'=>false,'error'=>'Periodicidade não definida');
					}
				} else {
					$rtn=array('success'=>false,'error'=>'Paciente não encontrado');
				}
			}


		header("Content-type: application/json");
		echo json_encode($rtn);
		die();

	} else if(isset($_GET['ajax'])) {
		if($_GET['ajax']=="medicamentos") {

			
			$dir="../../";
			require_once("../../lib/conf.php");
			require_once("../../usuarios/checa.php");

			if(isset($_GET['search']) and !empty($_GET['search'])) {
				$aux = explode(" ",$_GET['search']);

				$wh="";
				$primeiraLetra='';
				foreach($aux as $v) {
					if(empty($v)) continue;

					if(empty($primeiraLetra)) $primeiraLetra=substr($v,0,1);
					$wh.="titulo REGEXP '$v' and ";
				}
				$wh=substr($wh,0,strlen($wh)-5);
				$where="where (($wh) or titulo like '%".$_GET['search']."%') and lixo=0";
			}
			if(!empty($primeiraLetra)) $where.=" ORDER BY CASE WHEN titulo >= '$primeiraLetra' THEN 1 ELSE 0 END DESC, titulo ASC";
			else $where.=" order by titulo asc";


			$_medicamentos=array();
			$sql->consult($_p."medicamentos","*",$where);
			while($x=mysqli_fetch_object($sql->mysqry)) {
				$_medicamentos[]=array('id'=>$x->id,
										 'medicamento'=>utf8_encode($x->titulo),
										 'text'=>utf8_encode($x->titulo),
										 'quantidade'=>utf8_encode($x->quantidade),
										 'tipo'=>utf8_encode($x->tipo),
										 'posologia'=>utf8_encode($x->posologia),
										 'controleEspecial'=>utf8_encode($x->controleEspecial));
			}

			header("Content-type: json/application");

			$rtn['items']=$_medicamentos;
			echo json_encode($rtn);
		}
		die();
	}

	# ARRAYS (Profissionais, Cadeiras..)
	$_profissionais=array();
	$sql->consult($_p."colaboradores","id,nome,calendario_iniciais,foto,calendario_cor,check_agendamento,contratacaoAtiva","where lixo=0 order by nome asc");
	while($x=mysqli_fetch_object($sql->mysqry)) $_profissionais[$x->id]=$x;



	# OPÇÕES
		?>
		<script type="text/javascript">
			var autor = `<?php echo utf8_encode($usr->nome);?>`;
			var id_usuario = `<?php echo utf8_encode($usr->id);?>`;
		</script>
		<section class="aside aside-prontuario-opcoes" style="display: none;">
			<div class="aside__inner1">
				<header class="aside-header">
					<h1>Escolha uma opção</h1>
					<a href="javascript:;" class="aside-header__fechar aside-close"><i class="iconify" data-icon="fluent:dismiss-24-filled"></i></a>
				</header>
				<article class="aside-content">
					<div class="list5">
						<?php
						if(isset($apiConfig['geral'])) {
						/*?>
						<a href="javascript:;" data-aside="prontuario-geral" data-aside class="list5-item">
							<i class="iconify" data-icon="clarity:note-edit-line"></i>
							<p>Geral</p>
						</a>
						<?php*/
						}
						if(isset($apiConfig['anamnese'])) {
						?>
						<a href="javascript:;" data-aside="prontuario-anamnese" data-aside class="list5-item">
							<i class="iconify" data-icon="mdi-clipboard-check-multiple-outline"></i>
							<p>Anamnese</p>
						</a>
						<?php
						}

						if(isset($apiConfig['procedimentos'])) {
						?>
						<a href="javascript:;" data-aside="prontuario-procedimentos" onclick="asideProcedimentos()" data-aside class="list5-item">
							<i class="iconify" data-icon="mdi-check-circle-outline"></i>
							<p>Procedimentos</p>
						</a>
						<?php
						}
						if(isset($apiConfig['atestado'])) {
						?>
						<a href="javascript:;" data-aside="prontuario-atestado" class="list5-item">
							<i class="iconify" data-icon="mdi-file-document-outline"></i>
							<p>Atestado</p>
						</a>
						<?php
						}
						?>
						<a href="javascript:;" class="list5-item" style="opacity:0.4;">
							<i class="iconify" data-icon="entypo-lab-flask"></i>
							<p>Serviço de Laboratório</p>
						</a>
						<a href="javascript:;" data-aside="prontuario-pedidoExame" class="list5-item">
							<i class="iconify" data-icon="carbon-user-x-ray"></i>
							<p>Pedido de Exame</p>
						</a>
						<a href="javascript:;" data-aside="prontuario-receituario" class="list5-item">
							<i class="iconify" data-icon="mdi-pill"></i>
							<p>Receituário</p>
						</a>
						<?php
						if(isset($apiConfig['proximaConsulta'])) {
						?>
						<a href="javascript:;" data-aside="prontuario-proximaConsulta" onclick="asideEvolucaoProximaConsulta()" class="list5-item">
							<i class="iconify" data-icon="mdi-calendar-cursor"></i>
							<p>Próxima Consulta</p>
						</a>
						<?php
						}
						if(isset($apiConfig['documentos'])) {
						?>
						<a href="javascript:;" data-aside="prontuario-documentos" class="list5-item">
							<i class="iconify" data-icon="fluent:document-add-28-regular"></i>
							<p>Documentos</p>
						</a>
						<?php
						}
						?>
						<a href="javascript:;" class="list5-item" style="opacity:0.4;">
							<i class="iconify" data-icon="clarity:list-outline-badged"></i>
							<p>Estoque</p>
						</a>
					</div>
				</article>
			</div>
		</section>
		<?php
	
	# GERAL
		if(isset($apiConfig['geral'])) {

				
			?>
			<script type="text/javascript">
			
				$(function(){
					$('.aside-prontuario-geral .js-asideGeral-data').datetimepicker({
						timepicker:true,
						format:'d/m/Y H:i',
						scrollMonth:false,
						scrollTime:false,
						scrollInput:false,
					});

					$('.aside-prontuario-geral .js-salvarGeral').click(function(){
						

						let erro = '';
						
						let dataGeral = $('.aside-prontuario-geral .js-asideGeral-data').val();
						let id_profissional = $('.aside-prontuario-geral .js-asideGeral-id_profissional').val();
						let texto = $('.aside-prontuario-geral .js-asideGeral-texto').val();

						if(dataGeral.length==0) erro='Preencha o campo de Data';
						else if(id_profissional.length==0) erro='Preencha o campo de Profissional';
						else if(texto.length==0) erro='Preencha o campo de Evolução';

						
						if(erro.length>0) {
							swal({title: "Erro!", text: erro, html:true, type:"error", confirmButtonColor: "#424242"});
						} else {
							let obj = $(this);
							let obHTMLAntigo = $(this).html();

							if(obj.attr('data-loading')==0) {
								
								obj.html(`<span class="iconify" data-icon="eos-icons:loading"></span>`);
								obj.attr('data-loading',1);

								let data = {'ajax':'asPGeralPersistir',
											'id_paciente':id_paciente,
											'data':dataGeral,
											'id_profissional':id_profissional,
											'texto':texto};

								$.ajax({
										type:'POST',
										data:data,
										url:baseURLApiAsidePaciente,
										success:function(rtn) {
											if(rtn.success) {

												$('.aside-prontuario-geral .js-asideGeral-inputs').val('');
												$('.aside-close').click();
												document.location.reload();

											} else if(rtn.error) {
												swal({title: "Erro!", text: rtn.error, type:"error", confirmButtonColor: "#424242"});
											} else {
												swal({title: "Erro!", text: "Algum erro ocorreu! Tente novamente.", type:"error", confirmButtonColor: "#424242"});
											}
											
										},
										error:function() {
											swal({title: "Erro!", text: "Algum erro ocorreu! Tente novamente.", type:"error", confirmButtonColor: "#424242"});
										} 
								}).done(function(){
									obj.html(obHTMLAntigo);
									obj.attr('data-loading',0);
								});

							}



						}
					});

					
				});
			</script>

			<section class="aside aside-prontuario-geral" style="display: none;">
				<div class="aside__inner1">
					<header class="aside-header">
						<h1>Geral</h1>
						<a href="javascript:;" class="aside-header__fechar aside-close"><i class="iconify" data-icon="fluent:dismiss-24-filled"></i></a>
					</header>

					<form method="post" class="aside-content form js-form-geral">
						<section class="filter">
							<div class="filter-group"></div>
							<div class="filter-group">
								<div class="filter-form form">
									<dl>
										<dd><a href="javascript:;" class="button"><i class="iconify" data-icon="fluent:delete-24-regular"></i></a></dd>
									</dl>
									<dl>
										<dd><button type="button" class="button button_main js-salvarGeral" data-loading="0"><i class="iconify" data-icon="fluent:checkmark-12-filled"></i> <span>Salvar</span></button></dd>
									</dl>
								</div>								
							</div>
						</section>

						<fieldset>
							<legend>Informações</legend>
							<div class="colunas3">
								<dl>
									<dt>Data e Hora</dt>
									<dd>
										<input type="tel" name="" class="datahora js-asideGeral-data js-asideGeral-inputs" value="<?php echo date('d/m/Y H:i');?>" /></dd>
									</dd>
								</dl>
								<dl class="dl2">
									<dt>Cirurgião Dentista</dt>
									<dd>
										<select class="js-asideGeral-id_profissional js-asideGeral-inputs">
											<option value="">-</option>
											<?php
											foreach($_profissionais as $x) {
												if($x->check_agendamento==0 or $x->contratacaoAtiva==0) continue;
												echo '<option value="'.$x->id.'">'.utf8_encode($x->nome).'</option>';
											}
											?>
										</select>
									</dd>
								</dl>
							</div>
						</fieldset>
						<fieldset>
							<legend>Evolução</legend>
							<dl>
								<dd>
									<textarea class="js-asideGeral-texto js-asideGeral-inputs" style="height:320px;width:100%;"></textarea>
								</dd>
							</dl>
						</fieldset>
					</form>
				</div>
			</section>


			<?php
		}

	# ANAMNESE
		if(isset($apiConfig['anamnese'])) {

			$_anamnese=array();
			$sql->consult($_p."parametros_anamnese","*","WHERE 	lixo=0 order by titulo asc");
			while($x=mysqli_fetch_object($sql->mysqry)) {
				$_anamnese[$x->id]=$x;
			}

			?>
			<script type="text/javascript">
				var anamnesePerguntasDesativadas = [];

				const anamnese = (id_anamnese) => {

					let preenchimento = $('.aside-prontuario-anamnese .js-asideAnamnese-preenchimento:checked').val();

					if(id_anamnese>0) {

						$('.aside-prontuario-anamnese .js-formulario-anamnese').html('');
						$('.aside-prontuario-anamnese .js-fieldset-formularioAnamnese').show();
						$('.aside-prontuario-anamnese .js-loading-anamnese').show();

						let data = `ajax=asPAnamnese&id_anamnese=${id_anamnese}`;

						$.ajax({
							type:'POST',
							data:data,
							url:baseURLApiAsidePaciente,
							success:function(rtn) {
								if(rtn.success) {	


									if(rtn.formulario && rtn.formulario.length>0) {

										let div = $('.aside-prontuario-anamnese .js-formulario-anamnese');

										let cont = 1;
										rtn.formulario.forEach(f=>{

											formularioCampo='';
											obg = '';

											if(f.obg==1) obg=' *';

											if(f.tipo=="nota") {
												for(let i=0;i<=10;i++) {
													formularioCampo+=` <label class="input-form-nuber"><input type="radio" name="resposta_${f.id}" value="${i}" /> <div> ${i}</div></label>`;
												}
											} else if(f.tipo=="simnao") {
												
												formularioCampo+=`<div style="width: 100%"> <div class="input-form-sn"> <label class="input-form"><input type="radio" name="resposta_${f.id}" value="SIM" /> <div></div> Sim  </label>
												<label class="input-form"><input type="radio" name="resposta_${f.id}" value="NAO" /> <div></div> Não </label> </div>`;

											} else if(f.tipo=="simnaotexto") {

												formularioCampo+=`<div style="width: 100%"> <div class="input-form-sn"> <label class="input-form"><input type="radio" name="resposta_${f.id}" value="SIM" /> <div></div> Sim </label>
												<label class="input-form" ><input type="radio" name="resposta_${f.id}" value="NAO" /> <div> </div> Não </label> </div> <textarea name="resposta_texto_${f.id}" placeholder="Escreva sua resposta" ></textarea> </div>`;
												
											
											} else if(f.tipo=="texto") {
											
												formularioCampo+=`<textarea name="resposta_texto_${f.id}" class=""></textarea>`;
											
											} 
											
											div.append(`<dl class="js-pergunta js-pergunta-${f.id} input-form-caps filter" data-obg="${f.obg}" data-tipo="${f.tipo}" data-id_pergunta="${f.id}" style="padding:5px;border-radius:10px;flex-direction: inherit;">
															<dl style="width: 90%;>
																<dt class="input-form-titulo">${f.pergunta}${obg}</dt>
																<dd class="input-form-caps-notas" >${formularioCampo}</dd>
															</dl>
															<dl>
																<a href="javascript:;" class="button js-btn-remover" data-id_pergunta="${f.id}"><span class="iconify" data-icon="bx-bx-trash"></span></a>
																<a href="javascript:;" class="button js-btn-voltar" data-id_pergunta="${f.id}" style="display:none;"><span class="iconify" data-icon="ion:arrow-undo"></span></a>
															</dl>
														</dl>`);
											cont++;

											if(cont==rtn.formulario.length) {
												if(preenchimento=="paciente") {
													$('.aside-prontuario-anamnese .js-pergunta dd').find('div,textarea').css('opacity',0.5);
													$('.aside-prontuario-anamnese .js-pergunta dd').find('input,textarea').prop('disabled',true);
												} else {
													$('.aside-prontuario-anamnese .js-pergunta dd').find('div,textarea').css('opacity',1);
													$('.aside-prontuario-anamnese .js-pergunta dd').find('input,textarea').prop('disabled',false);
												}
											}
										});
									}

								} else if(rtn.error) {
									swal({title: "Erro!", text: rtn.error, type:"error", confirmButtonColor: "#424242"});
									$('.aside-prontuario-anamnese .js-fieldset-formularioAnamnese').hide();
								} else {
									swal({title: "Erro!", text: "Algum erro ocorreu! Tente novamente.", type:"error", confirmButtonColor: "#424242"});
									$('.aside-prontuario-anamnese .js-fieldset-formularioAnamnese').hide();
								}
								
							},
							error:function() {
								swal({title: "Erro!", text: "Algum erro ocorreu! Tente novamente.", type:"error", confirmButtonColor: "#424242"});
								$('.aside-prontuario-anamnese .js-fieldset-formularioAnamnese').hide();
							}
						}).done(function(){
							$('.aside-prontuario-anamnese .js-loading-anamnese').hide();
						});

					} else {
						$('.aside-prontuario-anamnese .js-fieldset-formularioAnamnese').hide();
						$('.aside-prontuario-anamnese .js-loading-anamnese').hide();
					}
				}

				const serialize = (obj) => {
					var str = [];
					for (var p in obj)
						if (obj.hasOwnProperty(p)) {
							str.push(encodeURIComponent(p) + "=" + encodeURIComponent(obj[p]));
						}
					return str.join("&");
				}

				$(function(){

					$('.aside-prontuario-anamnese .js-form-anamnese').on('click','.js-btn-remover',function(){
						let id_pergunta = $(this).attr('data-id_pergunta');
						$(this).hide();
						$(`.js-pergunta-${id_pergunta}`).find('.js-btn-voltar').show();
						$(`.js-pergunta-${id_pergunta} dl:eq(0)`).css('opacity',0.2);
						let preenchimento = $('.aside-prontuario-anamnese .js-asideAnamnese-preenchimento:checked').val();
						if(preenchimento=="profissional") {
							$(`.aside-prontuario-anamnese .js-pergunta-${id_pergunta} dd`).find('input,textarea').prop('disabled',true);
						}
						anamnesePerguntasDesativadas.push(id_pergunta);

					});

					$('.aside-prontuario-anamnese .js-form-anamnese').on('click','.js-btn-voltar',function(){
						let id_pergunta = $(this).attr('data-id_pergunta');
						$(this).hide();
						$(`.js-pergunta-${id_pergunta}`).find('.js-btn-remover').show();
						$(`.js-pergunta-${id_pergunta} dl:eq(0)`).css('opacity',1);
						let preenchimento = $('.aside-prontuario-anamnese .js-asideAnamnese-preenchimento:checked').val();
						if(preenchimento=="profissional") {
							$(`.aside-prontuario-anamnese .js-pergunta-${id_pergunta} dd`).find('input,textarea').prop('disabled',false);
						}
						anamnesePerguntasDesativadas = anamnesePerguntasDesativadas.filter(idPergunta=>idPergunta!=id_pergunta);

					});

					$('.aside-prontuario-anamnese .js-asideAnamnese-preenchimento').change(function(){

						if($('.js-asideAnamnese-preenchimento:checked').length==0) {
							$('.aside-prontuario-anamnese .js-enviarAnamnese').parent().parent().hide();
							$('.aside-prontuario-anamnese .js-salvarAnamnese').parent().parent().hide();
							$('.aside-prontuario-anamnese .js-formulario-anamnese').hide();
							$('.aside-prontuario-anamnese .js-asideAnamnese-dl-anamnese').hide();
							$('.aside-prontuario-anamnese .js-asideAnamnese-dl-id_profissional').hide();
						} else {
							if($(this).val()=="paciente") {
								$('.aside-prontuario-anamnese .js-enviarAnamnese').parent().parent().show();
								$('.aside-prontuario-anamnese .js-salvarAnamnese').parent().parent().hide();
								$('.aside-prontuario-anamnese .js-formulario-anamnese').show();
								$('.aside-prontuario-anamnese .js-asideAnamnese-anamnese').trigger('change');''
							} else if($(this).val()=="profissional") {
								$('.aside-prontuario-anamnese .js-enviarAnamnese').parent().parent().hide();
								$('.aside-prontuario-anamnese .js-salvarAnamnese').parent().parent().show();
								$('.aside-prontuario-anamnese .js-formulario-anamnese').show();
								$('.aside-prontuario-anamnese .js-asideAnamnese-anamnese').trigger('change');
							}
							$('.aside-prontuario-anamnese .js-asideAnamnese-dl-anamnese').show();
							$('.aside-prontuario-anamnese .js-asideAnamnese-dl-id_profissional').show();
						}

						
					});

					$('.aside-prontuario-anamnese .js-asideAnamnese-anamnese').change(function(){
						anamnese($(this).val());
					}).trigger('change');

					$('.aside-prontuario-anamnese').on('click','.js-pergunta',function(){
						$(this).css('background-color','');
						$(this).find('textarea,input').css('background-color','');
					})

					$('.aside-prontuario-anamnese .js-enviarAnamnese').click(function(){
						let erro = '';
						let id_profissional = $('.aside-prontuario-anamnese .js-asideAnamnese-id_profissional').val();
						let id_anamnese = $('.aside-prontuario-anamnese .js-asideAnamnese-anamnese').val();

						if(id_profissional.length==0) erro='Selecione o Profissional';
						else if(id_anamnese.length==0) erro='Selecione a Anamnese';

						if(erro.length>0) {
							swal({title: "Erro!", text: erro, html:true, type:"error", confirmButtonColor: "#424242"});
						} else {

							let obj = $(this);
							let obHTMLAntigo = $(this).html();

							if(obj.attr('data-loading')==0) {
								
								obj.html(`<span class="iconify" data-icon="eos-icons:loading"></span>`);
								obj.attr('data-loading',1);

								let data = `ajax=asPAnamnesePersistir&id_profissional=${id_profissional}&id_anamnese=${id_anamnese}&id_paciente=${id_paciente}&enviarLink=1`;

								let campos = {};
								$('.aside-prontuario-anamnese .js-form-anamnese').find('input,textarea').each(function(index,el){

									let inputType = $(el).attr('type');
									let name = $(el).attr('name');
									let tag = $(el).prop('tagName');
									let val = $(el).val();

									

									if(tag=="TEXTAREA") {
										campos[name]=val;
									} else if (tag=="INPUT") {
										if(inputType=='radio') {
											if($(el).prop('checked')===true) {
												campos[name]=val;
											}
										}
									}


								});
								data+=`&anamnesePerguntasDesativadas=${anamnesePerguntasDesativadas}&${serialize(campos)}`;


								$.ajax({
										type:'POST',
										data:data,
										url:baseURLApiAsidePaciente,
										success:function(rtn) {
											if(rtn.success) {
												$('.aside-close').click();
												
												swal({title: "Sucesso!", html:true, text: 'Link enviado para o número <b>'+rtn.celular+'</b> com sucesso', type:"success", confirmButtonColor: "#424242"},function(){
														document.location.reload();
												});
											} else if(rtn.error) {
												swal({title: "Erro!", text: rtn.error, type:"error", confirmButtonColor: "#424242"});
											} else {
												swal({title: "Erro!", text: "Algum erro ocorreu! Tente novamente.", type:"error", confirmButtonColor: "#424242"});
											}
											
										},
										error:function() {
											swal({title: "Erro!", text: "Algum erro ocorreu! Tente novamente.", type:"error", confirmButtonColor: "#424242"});
										} 
								}).done(function(){
									obj.html(obHTMLAntigo);
									obj.attr('data-loading',0);
								});

							}

						}

					});

					$('.aside-prontuario-anamnese .js-salvarAnamnese').click(function(){
			
						let erro = '';
						let id_profissional = $('.aside-prontuario-anamnese .js-asideAnamnese-id_profissional').val();
						let id_anamnese = $('.aside-prontuario-anamnese .js-asideAnamnese-anamnese').val();

						if(id_profissional.length==0) erro='Selecione o Profissional';
						else if(id_anamnese.length==0) erro='Selecione a Anamnese';

						if(erro.length==0) {
							$('.aside-prontuario-anamnese .js-form-anamnese').find('.js-pergunta').each(function(i,el){
								let obg = eval($(el).attr('data-obg'));
								let tipo = $(el).attr('data-tipo');
								let id_pergunta = $(el).attr('data-id_pergunta');


								if(obg==1 && anamnesePerguntasDesativadas.find(x=>x==id_pergunta)===undefined) {
									if(tipo=="nota" || tipo=="simnao") {
										let selecionou = $(el).find('input[type=radio]:checked').length>0?true:false;
										if(selecionou===false) {
											$(el).css({'background':'#ffffdb','padding':'15px;'});
											erro=true;
										}
									} else if(tipo=="simnaotexto") {
										let selecionouOpcao = ($(el).find('input[type=radio]:checked').length>0)?true:false;
										let selecionouTexto = ($(el).find('textarea').val().length>0)?true:false;

										if(selecionouOpcao===false) {
											$(el).css('background','#ffffdb');
											erro=true;
										} 
										if(selecionouTexto===false) {
											$(el).find('textarea').css('background-color', '#ffffdb');
											erro=true;
										}
									} else if(tipo=="texto") {

										let selecionou = ($(el).find('textarea').val().length>0)?true:false;

										if(selecionou===false) {
											$(el).find('textarea').css('background-color','#ffffdb');
											erro=true;
										}
									}
								}
							});
						}

						if(erro===true) {
							erro='Preencha os campos destacados!';
						}

						if(erro.length>0) {
							swal({title: "Erro!", text: erro, html:true, type:"error", confirmButtonColor: "#424242"});
						} else {
							let obj = $(this);
							let obHTMLAntigo = $(this).html();

							if(obj.attr('data-loading')==0) {
								
								obj.html(`<span class="iconify" data-icon="eos-icons:loading"></span>`);
								obj.attr('data-loading',1);

								let data = `ajax=asPAnamnesePersistir&id_profissional=${id_profissional}&id_anamnese=${id_anamnese}&id_paciente=${id_paciente}&anamnesePerguntasDesativadas=${anamnesePerguntasDesativadas}`;

								let campos = {};
								$('.aside-prontuario-anamnese .js-form-anamnese').find('input,textarea').each(function(index,el){

									let inputType = $(el).attr('type');
									let name = $(el).attr('name');
									let tag = $(el).prop('tagName');
									let val = $(el).val();

									

									if(tag=="TEXTAREA") {
										campos[name]=val;
									} else if (tag=="INPUT") {
										if(inputType=='radio') {
											if($(el).prop('checked')===true) {
												campos[name]=val;
											}
										}
									}


								});
								data+=`&${serialize(campos)}`;


								$.ajax({
										type:'POST',
										data:data,
										url:baseURLApiAsidePaciente,
										success:function(rtn) {
											if(rtn.success) {
												$('.aside-close').click();
												swal({title: "Sucesso!", html:true, text: 'Anamnese salva com sucesso!', type:"success", confirmButtonColor: "#424242"},function(){
														document.location.reload();
												});

											} else if(rtn.error) {
												swal({title: "Erro!", text: rtn.error, type:"error", confirmButtonColor: "#424242"});
											} else {
												swal({title: "Erro!", text: "Algum erro ocorreu! Tente novamente.", type:"error", confirmButtonColor: "#424242"});
											}
											
										},
										error:function() {
											swal({title: "Erro!", text: "Algum erro ocorreu! Tente novamente.", type:"error", confirmButtonColor: "#424242"});
										} 
								}).done(function(){
									obj.html(obHTMLAntigo);
									obj.attr('data-loading',0);
								});

							}

						}
					});

					$('.aside-prontuario-anamnese .aside-close').click(function(){
						/*$('.js-asideAnamnese-id_profissional').val('');
						$('.js-asideAnamnese-anamnese').val('');
						$('.js-asideAnamnese-preenchimento:checked').prop('checked',false);
						$('.js-asideAnamnese-preenchimento').trigger('change');
						anamnesePerguntasDesativadas = [];*/
					})
				});
			</script>

			<section class="aside aside-prontuario-anamnese" style="display: none;">
				<div class="aside__inner1">

					<header class="aside-header">
						<h1>Anamnese</h1>
						<a href="javascript:;" class="aside-header__fechar aside-close"><i class="iconify" data-icon="fluent:dismiss-24-filled"></i></a>
					</header>

					<form method="post" class="aside-content form js-form-anamnese">

						<section class="filter">
							<div class="filter-group"></div>
							<div class="filter-group">
								<div class="filter-form form">
									<dl style="display: none;">
										<dd><button type="button" class="button button_main js-enviarAnamnese" data-loading="0" ><i class="iconify" data-icon="la:whatsapp"></i> <span>Enviar para paciente</span></button></dd>
									</dl>
									<dl style="display: none;">
										<dd><button type="button" class="button button_main js-salvarAnamnese" data-loading="0"><i class="iconify" data-icon="fluent:checkmark-12-filled"></i> <span>Salvar</span></button></dd>
									</dl>
								</div>								
							</div>
						</section>

						<div class="box" style="min-height: auto;overflow: initial;margin-bottom: 15px;">
							<dl>
								<dt>Quem irá preencher a Anamnese?</dt>
								<dd class="radios-anamnese">
									<label>
										<input type="radio" name="preenchimentoPelo" class="js-asideAnamnese-preenchimento" value="paciente" /> 
										<div class="icon-raio">
											<div><span class="iconify" data-icon="fluent:person-20-regular" data-width="40"></span></div>
											<div>Paciente</div> 
										</div>
									</label>

									<label>
										<input type="radio" name="preenchimentoPelo" class="js-asideAnamnese-preenchimento" value="profissional" />
										<div class="icon-raio">
											<div><span class="iconify" data-icon="fluent:person-info-20-regular" data-width="40"></span></div>
											<div>Profissional</div>
										</div>
									</label>
								</dd>
							</dl>
							<dl class="js-asideAnamnese-dl-id_profissional" style="display: none;">
								<dt>Profissional</dt>
								<dd>
									<select class="js-asideAnamnese-id_profissional">
										<option value="">-</option>
										<?php
										foreach($_profissionais as $x) {
											if($x->check_agendamento==0 or $x->contratacaoAtiva==0) continue;
											echo '<option value="'.$x->id.'">'.utf8_encode($x->nome).'</option>';
										}
										?>
									</select>
								</dd>
							</dl>

							<dl class="js-asideAnamnese-dl-anamnese" style="display: none;">
								<dt>Tipo de Anamnese</dt>
								<dd>
									<select class="js-asideAnamnese-anamnese">
										<option value="">-</option>
										<?php
										foreach($_anamnese as $x) {
											echo '<option value="'.$x->id.'">'.utf8_encode($x->titulo).'</option>';
										}
										?>
									</select>
								</dd>
							</dl>
						</div>
						<?php /* <fieldset class="js-fieldset-formularioAnamnese">
							<legend>Anamnese</legend> */ ?>


							<div class="js-loading-anamnese">
								<center><br /><span class="iconify" data-icon="eos-icons:loading" data-height="25"></span><br />Carregando...</center>
							</div>

							<div class="js-formulario-anamnese">

							</div>

							<?php /*<dl>
								<dt>1. Queixa principal</dt>
								<dd><textarea name="" rows="3"></textarea></dd>
							</dl>
							<dl>
								<dt>2. Medicação</dt>
								<dd>
									<label><input type="radio" name="resposta_146" value="SIM"> Sim</label>
									<label><input type="radio" name="resposta_146" value="NAO"> Não</label>
								</dd>
								<dd>
									<textarea name="" rows="3"></textarea>	
								</dd>
							</dl>
							<dl>
								<dt>3. Dor/intensidade</dt>
								<dd>
									<label><input type="radio" name="resposta_161" value="0"> 0</label>
									<label><input type="radio" name="resposta_161" value="1"> 1</label>
									<label><input type="radio" name="resposta_161" value="2"> 2</label>
									<label><input type="radio" name="resposta_161" value="3"> 3</label>
									<label><input type="radio" name="resposta_161" value="4"> 4</label>
									<label><input type="radio" name="resposta_161" value="5"> 5</label>
									<label><input type="radio" name="resposta_161" value="6"> 6</label>
									<label><input type="radio" name="resposta_161" value="7"> 7</label>
									<label><input type="radio" name="resposta_161" value="8"> 8</label>
									<label><input type="radio" name="resposta_161" value="9"> 9</label>
									<label><input type="radio" name="resposta_161" value="10"> 10</label>
								</dd>
							</dl>*/?>
						<?php /* </fieldset> */ ?>
					</form>
				</div>
			</section>
			<?php
		}

	# ATESTADO
		if(isset($apiConfig['atestado'])) {

			$_atestadosTipos=array();
			$sql->consult("infodentalADM.infod_parametros_atestados_tipos","*","where lixo=0 order by titulo asc") ;
			while($x=mysqli_fetch_object($sql->mysqry)) {
				$_atestadosTipos[$x->id]=$x;
			}

			$_atestadosFins=array();
			$sql->consult("infodentalADM.infod_parametros_atestados_fins","*","where lixo=0 order by titulo asc") ;
			while($x=mysqli_fetch_object($sql->mysqry)) {
				$_atestadosFins[$x->id]=$x;
			}
				
			?>
			<script type="text/javascript">
				
				const atestadoTexto = () => {

					let dataAtestado = $('.aside-prontuario-atestado .js-asideAtestado-data').val();
					let id_tipo = $('.aside-prontuario-atestado .js-asideAtestado-id_tipo').val();
					let fim = $('.aside-prontuario-atestado .js-asideAtestado-fim').val();
					let id_profissional = $('.aside-prontuario-atestado .js-asideAtestado-id_profissional').val();
					let dias = $('.aside-prontuario-atestado .js-asideAtestado-dias').val();
					let duracao = $('.aside-prontuario-atestado .js-asideAtestado-duracao').val();

					possuiDias=possuiDuracao=0;
					if(id_tipo.length>0) {
						possuiDias = $('.aside-prontuario-atestado .js-asideAtestado-id_tipo option:selected').attr('data-dias');
						possuiDuracao = $('.aside-prontuario-atestado .js-asideAtestado-id_tipo option:selected').attr('data-duracao');

						if(possuiDias==1) $('.aside-prontuario-atestado .js-asideAtestado-dias').parent().parent().show();
						else $('.aside-prontuario-atestado .js-asideAtestado-dias').parent().parent().hide();

						if(possuiDuracao==1) $('.aside-prontuario-atestado .js-asideAtestado-duracao').parent().parent().show();
						else $('.aside-prontuario-atestado .js-asideAtestado-duracao').parent().parent().hide();
					} else {
						$('.aside-prontuario-atestado .js-asideAtestado-dias').parent().parent().hide();
						$('.aside-prontuario-atestado .js-asideAtestado-duracao').parent().parent().hide();
					}

					
					if(dataAtestado.length>0 && 
						id_tipo.length>0 && 
						fim.length>0 && 
						id_profissional.length>0 && 
						(possuiDias==0 || dias.length>0) &&
						(possuiDuracao==0 || duracao.length>0)
						) {

						let data = `ajax=asPAtestadoTexto&data=${dataAtestado}&id_tipo=${id_tipo}&fim=${fim}&id_profissional=${id_profissional}&dias=${dias}&id_paciente=${id_paciente}&duracao=${duracao}`;	
						
						$.ajax({
							type:"POST",
							data:data,
							url:baseURLApiAsidePaciente,
							success:function(rtn) {
								if(rtn.success) {
									//$('.aside-prontuario-atestado .js-asideAtestado-texto').val(rtn.texto);
									CKEDITOR.instances['asideAtestado-texto'].setData(rtn.texto);
									$('.aside-prontuario-atestado .js-fieldset-textoAtestado').show();
								} else {
									$('.aside-prontuario-atestado .js-asideAtestado-texto').val('');
									$('.aside-prontuario-atestado .js-fieldset-textoAtestado').hide();
								}
							}
						});

					} else {
						$('.aside-prontuario-atestado .js-asideAtestado-texto').val('');
						$('.aside-prontuario-atestado .js-fieldset-textoAtestado').hide();
					}
					//$('.aside-prontuario-atestado .js-fieldset-textoAtestado').show();
				}

				$(function(){


					atestadoTexto();

					$('.aside-prontuario-atestado .js-asideAtestado-inputs').change(atestadoTexto);

					$('.aside-prontuario-atestado .js-asideAtestado-data').datetimepicker({
						timepicker:true,
						format:'d/m/Y H:i',
						scrollMonth:false,
						scrollTime:false,
						scrollInput:false,
					});

					$('.aside-prontuario-atestado .js-salvarAtestado').click(function(){
						
						let erro = '';
						
						let dataAtestado = $('.aside-prontuario-atestado .js-asideAtestado-data').val();
						let id_tipo = $('.aside-prontuario-atestado .js-asideAtestado-id_tipo').val();
						let fim = $('.aside-prontuario-atestado .js-asideAtestado-fim').val();
						let id_profissional = $('.aside-prontuario-atestado .js-asideAtestado-id_profissional').val();
						let dias = $('.aside-prontuario-atestado .js-asideAtestado-dias').val();
						let duracao = $('.aside-prontuario-atestado .js-asideAtestado-duracao').val();
						let atestado = CKEDITOR.instances['asideAtestado-texto'].getData();

						if(atestado.length==0) setTimeout(function(){atestado = CKEDITOR.instances['asideAtestado-texto'].getData();},500);
						if(dataAtestado.length==0) erro='Preencha o campo de Data';
						else if(id_tipo.length==0) erro='Preencha o campo de Tipo do Atestado';
						else if(fim.length==0) erro='Preencha o campo de Fim do Atestado';
						else if(id_profissional.length==0) erro='Preencha o campo de Profissional';

						
						if(erro.length>0) {
							swal({title: "Erro!", text: erro, html:true, type:"error", confirmButtonColor: "#424242"});
						} else {
							let obj = $(this);
							let obHTMLAntigo = $(this).html();

							if(obj.attr('data-loading')==0) {
								
								obj.html(`<span class="iconify" data-icon="eos-icons:loading"></span>`);
								obj.attr('data-loading',1);

								let data = {'ajax':'asPAtestadoPersistir',
											'id_paciente':id_paciente,
											'data':dataAtestado,
											'id_tipo':id_tipo,
											'fim':fim,
											'id_profissional':id_profissional,
											'dias':dias,
											'duracao':duracao,
											'atestado':atestado};

								$.ajax({
										type:'POST',
										data:data,
										url:baseURLApiAsidePaciente,
										success:function(rtn) {
											if(rtn.success) {
												console.log(rtn);
												$('.aside-prontuario-atestado .js-asideAtestado-inputs').val('');
												CKEDITOR.instances['asideAtestado-texto'].setData('');
												$('.aside-close').click();
												document.location.reload();
											} else if(rtn.error) {
												console.log(rtn);
												swal({title: "Erro!", text: rtn.error, type:"error", confirmButtonColor: "#424242"});
											} else {
												console.log(rtn);
												swal({title: "Erro!", text: "Algum erro ocorreu! Tente novamente.", type:"error", confirmButtonColor: "#424242"});
											}
											
										},
										error:function() {
											swal({title: "Erro!", text: "Algum erro ocorreu! Tente novamente.", type:"error", confirmButtonColor: "#424242"});
										} 
								}).done(function(){
									obj.html(obHTMLAntigo);
									obj.attr('data-loading',0);
								});

							}



						}
					});

					var fck_texto = CKEDITOR.replace('asideAtestado-texto',{
																			language: 'pt-br',
																			width:'100%',
																			height:250,
																			toolbar: [
																				        {
																				          name: 'document',
																				          items: ['Undo', 'Redo']
																				        },
																				        {
																				          name: 'basicstyles',
																				          items: ['Bold', 'Italic', 'Strike']
																				        },
																				      ]
																			});

					
				});
			</script>

			<section class="aside aside-prontuario-atestado" style="display: none;">
				<div class="aside__inner1">
					<header class="aside-header">
						<h1>Atestado</h1>
						<a href="javascript:;" class="aside-header__fechar aside-close"><i class="iconify" data-icon="fluent:dismiss-24-filled"></i></a>
					</header>

					<form method="post" class="aside-content form js-form-atestado">
						<section class="filter">
							<div class="filter-group"></div>
							<div class="filter-group">
								<div class="filter-form form">
									<dl>
										<dd><button type="button" class="button button_main js-salvarAtestado" data-loading="0"><i class="iconify" data-icon="fluent:checkmark-12-filled"></i> <span>Salvar</span></button></dd>
									</dl>
								</div>								
							</div>
						</section>

						<?php /* <fieldset>
							<legend>Cabeçalho</legend> */ ?>
							<div class="box">
								<div class="colunas3">
									<dl>
										<dt>Data e Hora</dt>
										<dd>
											<input type="tel" name="" class="datahora js-asideAtestado-data js-asideAtestado-inputs"  value="<?php echo date('d/m/Y H:i');?>" /></dd>
										</dd>
									</dl>
									<dl>
										<dt>Tipo de Atestado</dt>
										<dd>
											<select class="js-asideAtestado-id_tipo js-asideAtestado-inputs">
												<option value="">-</option>
												<?php
												foreach($_atestadosTipos as $x) {
													echo '<option value="'.$x->id.'" data-dias="'.$x->possui_dias.'" data-duracao="'.$x->possui_duracao.'">'.utf8_encode($x->titulo).'</option>';
												}
												?>
											</select>
										</dd>
									</dl>
									<dl>
										<dt>Fim do Atestado</dt>
										<dd>
											<select class="js-asideAtestado-fim js-asideAtestado-inputs">
												<option value="">-</option>
												<?php
												foreach($_atestadosFins as $x) {
													echo '<option value="'.$x->id.'">'.utf8_encode($x->titulo).'</option>';
												}
												?>
											</select>
										</dd>
									</dl>
								</div>
								<div class="colunas3">
									<dl>
										<dt>Cirurgião Dentista</dt>
										<dd>
											<select class="js-asideAtestado-id_profissional js-asideAtestado-inputs">
												<option value="">-</option>
												<?php
												foreach($_profissionais as $x) {
													if($x->check_agendamento==0 or $x->contratacaoAtiva==0) continue;
													echo '<option value="'.$x->id.'">'.utf8_encode($x->nome).'</option>';
												}
												?>
											</select>
										</dd>
									</dl>
									<dl>
										<dt>Dias do Atestado</dt>
										<dd class="form-comp form-comp_pos">
											<input type="number" class="js-asideAtestado-dias js-asideAtestado-inputs" /><span>dias</span>
										</dd>
									</dl>
									<dl>
										<dt>Duração do Atestado</dt>
										<dd class="form-comp form-comp_pos">
											<input type="number" class="js-asideAtestado-duracao js-asideAtestado-inputs" /><span>mins</span>
										</dd>
									</dl>
								</div>
							</div>


						<?php /* </fieldset> * ?>
						<?php /* <fieldset class="js-fieldset-textoAtestado" style="display:none;">
							<legend>Atestado</legend> */ ?>
							<dl style="margin-top: 15px;">
								<dd>
									<textarea class="js-asideAtestado-texto" id="asideAtestado-texto" style="height:120px;width:100%;"></textarea>
								</dd>
							</dl>
						<?php /* </fieldset> */ ?>
					</form>
				</div>
			</section>


			<?php
		}

	# PEDIDO DE EXAME
		if(isset($apiConfig['pedidoExame'])) {

			$_tiposExames=array();
			$sql->consult($_p."parametros_examedeimagem","*","where lixo=0 order by titulo asc");
			while($x=mysqli_fetch_object($sql->mysqry)) {
				$_tiposExames[$x->id]=$x;
			}

			$_regioes=array();
			$sql->consult($_p."parametros_procedimentos_regioes","*","");
			while($x=mysqli_fetch_object($sql->mysqry)) $_regioes[$x->id]=$x;


			$_regioesOpcoes=array();
			$sql->consult($_p."parametros_procedimentos_regioes_opcoes","*","order by titulo asc");
			while($x=mysqli_fetch_object($sql->mysqry)) $_regioesOpcoes[$x->id_regiao][]=$x;
			
			$_clinicas=array();
			$sql->consult($_p."parametros_fornecedores","*","where lixo=0 and tipo='CLINICA' order by razao_social, nome asc");
			while($x=mysqli_fetch_object($sql->mysqry)) {
				$_clinicas[$x->id]=$x;
			}

			?>

			<script type="text/javascript">
				var exames = [];
				const examesListar = () => {

					$('.aside-prontuario-pedidoExame .js-asidePedidoExame-tabela tbody tr').remove();
				
					exames.forEach(x=>{
						let opcao='';
						if(x.opcao.length>0) opcao=' - '+x.opcao;
						$('.aside-prontuario-pedidoExame .js-asidePedidoExame-tabela tbody').append(`<tr><td><h1>${x.titulo}${opcao}</h1><p>${x.obs}</p></td></tr>`);
					});

					$('.aside-prontuario-pedidoExame .js-asidePedidoExame-exames').val(JSON.stringify(exames));

				}

				$(function(){

					$('.aside-prontuario-pedidoExame .js-asidePedidoExame-tabela tbody').on('click','tr',function(){
						let index = $(this).index('table.js-asidePedidoExame-tabela tbody tr');
					
						if(exames[index]) {

							$('.aside-prontuario-pedidoExame .js-asidePedidoExame-id_exame').val(exames[index].id_exame);
							$('.aside-prontuario-pedidoExame .js-asidePedidoExame-id_regiao').val(exames[index].id_regiao).trigger('change');
							//console.log(exames[index].id_opcao);
							if(exames[index].id_opcao.length>0) {
								let cont = 0;
								exames[index].id_opcao.forEach(o=>{
									if(o.length>0) {
										$(`.aside-prontuario-pedidoExame .js-regiao-${exames[index].id_regiao}-select`).find(`option[value=${o}]`).prop('selected',true);
									}

									cont++;

									if(exames[index].id_opcao.length==cont) {
										$(`.aside-prontuario-pedidoExame .js-regiao-${exames[index].id_regiao}-select`).trigger('chosen:updated');
									}
								});
							}


							$('.aside-prontuario-pedidoExame .js-asidePedidoExame-obs').val(exames[index].obs);

							$('.js-asidePedidoExame-removerExame').show();
							$('.js-asidePedidoExame-index').val(index);
							
						} else {
							swal({title: "Erro!", text: 'Exame não encontrado!', html:true, type:"error", confirmButtonColor: "#424242"});
						}
					});

					$('.aside-prontuario-pedidoExame .js-asidePedidoExame-id_exame').change(function(){
						let obs = $(this).find('option:selected').attr('data-obs');
						$('.aside-prontuario-pedidoExame .js-asidePedidoExame-obs').val(obs);
					})

					$('.aside-prontuario-pedidoExame .js-asidePedidoExame-id_regiao').change(function(){
						let id_regiao = $(this).val();
						let regiao = $(this).find('option:selected').attr('data-regiao');

						$(`.js-regiao`).hide();
						$(`.js-regiao-${id_regiao}`).show();
						$(`.js-regiao-${id_regiao}`).find('select').chosen({hide_results_on_select:false,allow_single_deselect:true});
						$(`.js-regiao-${id_regiao}-select`).val('').trigger('chosen:updated')
					});

					$('.aside-prontuario-pedidoExame .js-asidePedidoExame-adicionarExame').click(function(){

						let index = $('.aside-prontuario-pedidoExame .js-asidePedidoExame-index').val();
						let id_exame = $('.aside-prontuario-pedidoExame .js-asidePedidoExame-id_exame').val();
						let titulo = $('.aside-prontuario-pedidoExame .js-asidePedidoExame-id_exame option:selected').attr('data-titulo');
						let id_regiao = $('.aside-prontuario-pedidoExame .js-asidePedidoExame-id_regiao').val();
						let obs = $('.aside-prontuario-pedidoExame .js-asidePedidoExame-obs').val();

						let erro = ``;
						if(id_exame.length==0) erro='Selecione o tipo de exame';
						else if(id_regiao.length==0) erro='Seleciona a região';
						else if(id_regiao>=2 && $(`.js-regiao-${id_regiao}-select`).val().length==0) erro=`Preencha a Região`;

						if(erro.length==0) {

							let opcao = ``;
							id_opcao = 0;

							if(id_regiao>=2) {
								id_opcao = $(`.js-regiao-${id_regiao}-select`).val();
								
								$(`.js-regiao-${id_regiao}-select option:selected`).each(function(ind,el){
									if($(el).val()) {
										opcao+=`${$(el).text()}, `;
									}
								});
								opcao = opcao.substr(0,opcao.length-2);
							} 


							let dt = new Date();
							let dia = dt.getMonth();
							let mes = dt.getDate();
							let status = `aguardando`;
							mes++
							mes=mes<=9?`0${mes}`:mes;
							dia=dia<=9?`0${dia}`:dia;
							let data = `${dia}/${mes}/${dt.getFullYear()} ${dt.getHours()}:${dt.getMinutes()}`;

							let item = { id_exame, 
											titulo,
											id_regiao,
											id_opcao,
											opcao,
											obs,
											autor, 
											status,
											id_usuario, 
											data
										}
							if(index>=0) {
								exames[index]=item;
							} else {
								exames.push(item);
							}
							examesListar();

							$('.js-asidePedidoExame-inputsExame,.js-asidePedidoExame-obs').val('');
							$('.js-asidePedidoExame-id_regiao').trigger('change');
							$('.js-asidePedidoExame-removerExame').hide();
							$('.js-asidePedidoExame-index').val(-1);
						
						} else {
							swal({title: "Erro!", text: erro, html:true, type:"error", confirmButtonColor: "#424242"});
						}
					});

					$('.aside-prontuario-pedidoExame .js-asidePedidoExame-removerExame').click(function(){
						let index = eval($('.js-asidePedidoExame-index').val());
						
						if(index>=0) {
							exames.splice(index,1);
							examesListar();

							$('.js-asidePedidoExame-inputsExame,.js-asidePedidoExame-obs').val('');
							$('.js-asidePedidoExame-id_regiao').trigger('change');
							$('.js-asidePedidoExame-removerExame').hide();
							$('.js-asidePedidoExame-index').val(-1);
						} else {
							swal({title: "Erro!", text: 'Exame não encontrado!', html:true, type:"error", confirmButtonColor: "#424242"});
						}
					})

					$('.aside-prontuario-pedidoExame .js-salvarPedidoExame').click(function(){
						let erro = '';
						
						let dataPedidoExame = $('.aside-prontuario-pedidoExame .js-asidePedidoExame-data').val();
						let id_clinica = $('.aside-prontuario-pedidoExame .js-asidePedidoExame-id_clinica').val();
						let id_profissional = $('.aside-prontuario-pedidoExame .js-asidePedidoExame-id_profissional').val();
						let pedidoExames = $('.aside-prontuario-pedidoExame .js-asidePedidoExame-exames').val().length>0?JSON.parse($('.aside-prontuario-pedidoExame .js-asidePedidoExame-exames').val()):[];


						if(dataPedidoExame.length==0) erro='Preencha o campo de Data do Pedido';
						else if(id_clinica.length==0) erro='Preencha o campo de Clínica';
						else if(id_profissional.length==0) erro='Preencha o campo de Profissional';
						else if(pedidoExames.length==0) erro='Adicione pelo menos um Exame!';

						
						if(erro.length>0) {
							swal({title: "Erro!", text: erro, html:true, type:"error", confirmButtonColor: "#424242"});
						} else {
							let obj = $(this);
							let obHTMLAntigo = $(this).html(); 
							

							if(obj.attr('data-loading')==0) {
								
								obj.html(`<span class="iconify" data-icon="eos-icons:loading"></span>`);
								obj.attr('data-loading',1);

								let data = {'ajax':'asPPedidoExamePersistir',
											'id_paciente':id_paciente,
											'data':dataPedidoExame,
											'id_clinica':id_clinica,
											'id_profissional':id_profissional,
											'exames':pedidoExames};

								$.ajax({
										type:'POST',
										data:data,
										url:baseURLApiAsidePaciente,
										success:function(rtn) {
											if(rtn.success) {

												$('.js-asidePedidoExame-inputs').val('');
												
												$('.aside-close').click();
												document.location.reload();

											} else if(rtn.error) {
												swal({title: "Erro!", text: rtn.error, type:"error", confirmButtonColor: "#424242"});
											} else {
												swal({title: "Erro!", text: "Algum erro ocorreu! Tente novamente.", type:"error", confirmButtonColor: "#424242"});
											}
											
										},
										error:function() {
											swal({title: "Erro!", text: "Algum erro ocorreu! Tente novamente.", type:"error", confirmButtonColor: "#424242"});
										} 
								}).done(function(){
									obj.html(obHTMLAntigo);
									obj.attr('data-loading',0);
								});

							}



						}
					});

					$('.aside-prontuario-pedidoExame .js-asidePedidoExame-data').datetimepicker({
						timepicker:false,
						format:'d/m/Y',
						scrollMonth:false,
						scrollTime:false,
						scrollInput:false,
					});

				})	
			</script>

			<section class="aside aside-prontuario-pedidoExame" style="display: none;">
				<div class="aside__inner1">
					<header class="aside-header">
						<h1>Pedido de Exame</h1>
						<a href="javascript:;" class="aside-header__fechar aside-close"><i class="iconify" data-icon="fluent:dismiss-24-filled"></i></a>
					</header>
					<form method="post" class="aside-content form">
						<section class="filter">
							<div class="filter-group"></div>
							<div class="filter-group">
								<div class="filter-form form">
									<dl>
										<dd><a href="" class="button"><i class="iconify" data-icon="fluent:delete-24-regular"></i></a></dd>
									</dl>
									<dl>
										<dd><button type="button" class="button button_main js-salvarPedidoExame" data-loading="0"><i class="iconify" data-icon="fluent:checkmark-12-filled"></i> <span>Salvar</span></button></dd>
									</dl>
								</div>								
							</div>
						</section>
					<div class="box">
						<?php /* <fieldset>
							<legend>Cabeçalho</legend> */ ?>
							<div class="colunas3">
								<dl>
									<dt>Data do Pedido</dt>
									<dd>
										<input type="tel" class="data js-asidePedidoExame-data js-asidePedidoExame-inputs" value="<?php echo date('d/m/Y');?>" /></dd>
									</dd>
								</dl>
								<dl>
									<dt>Clínica Radiológica</dt>
									<dd>
										<select class="js-asidePedidoExame-id_clinica js-asidePedidoExame-inputs">
											<option value=""></option>
											<?php
											foreach($_clinicas as $v) {
												echo '<option value="'.$v->id.'"'.((is_object($evolucao) and $evolucao->id_clinica==$v->id)?' selected':'').'>'.utf8_encode($v->tipo_pessoa=="PJ"?$v->nome_fantasia:$v->nome).'</option>';
											}
											?>
										</select>
									</dd>
								</dl>
								<dl>
									<dt>Profissional</dt>
									<dd>
										<select class="js-asidePedidoExame-id_profissional js-asidePedidoExame-inputs" data-placeholder="Selecione...">
											<option value=""></option>
											<?php
											foreach($_profissionais as $v) {
												if($v->check_agendamento==0 or $v->contratacaoAtiva==0) continue;
												echo '<option value="'.$v->id.'"'.((is_object($evolucao) and $evolucao->id_profissional==$v->id)?' selected':'').'>'.utf8_encode($v->nome).'</option>';
											}
											?>
										</select>
									</dd>
								</dl>
							</div>					
						<?php /* </fieldset> */ ?>

						<?php /* <fieldset>
							<legend>Adicionar Exame</legend> */ ?>
							<input type="hidden" class="js-asidePedidoExame-index" value="-1" />
							<div class="colunas3">
								<dl class="dl2">
									<dt>Tipo de Exame</dt>
									<dd>
										<select class="js-asidePedidoExame-id_exame js-asidePedidoExame-inputsExame">
											<option value="">-</option>
											<?php
											foreach($_tiposExames as $v) {
												echo '<option value="'.$v->id.'" data-titulo="'.utf8_encode($v->titulo).'" data-obs="'.utf8_encode($v->obs).'">'.utf8_encode($v->titulo).'</option>';
											}
											?>
										</select>
									</dd>
								</dl>
								<dl>
									<dt>Região</dt>
									<dd>
										<select class="js-asidePedidoExame-id_regiao js-asidePedidoExame-inputsExame">
											<option value="">-</option>
											<?php
											foreach($_regioes as $v) {
												echo '<option value="'.$v->id.'" data-regiao="'.utf8_encode($v->titulo).'">'.utf8_encode($v->titulo).'</option>';
											}
											?>
										</select>
									</dd>
								</dl>
							</div>		


							<dl class="js-regiao-2 js-regiao dl2" style="display: none;">
								<dt>Arcada(s)</dt>
								<dd>
									<select class="js-regiao-2-select js-asideAtestado-inputsExame" multiple>
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
									<select class="js-regiao-3-select js-asideAtestado-inputsExame" multiple>
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
									<select class="js-regiao-4-select js-asideAtestado-inputsExame" multiple>
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
							
							<dl>
								<dt>Observação</dt>
								<dd>
									<input type="text" class="js-asidePedidoExame-obs js-asideAtestado-inputsExame">

									<button type="button" class="button js-asidePedidoExame-adicionarExame"><span class="iconify" data-icon="akar-icons:plus"></span></button>
									<a href="javascript:;" class="button js-asidePedidoExame-removerExame" style="display:none;"><i class="iconify" data-icon="fluent:delete-24-regular"></i></a>
								</dd>
							</dl>
						<?php /* </fieldset> */ ?>

						<?php /* <fieldset> */ ?>
							<textarea class="js-asidePedidoExame-exames" style="display:none;"></textarea>
							<?php /* <legend>Exames Adicionados</legend> */ ?>
						<?php /* </fieldset> */ ?>
						
					</div>
						<div class="list1" style="margin-top: 15px;">
							<table class="js-asidePedidoExame-tabela">
								<tbody>
								
								</tbody>						
							</table>
						</div>
					</form>
				</div>
			</section>
			<?php
		}

	# RECEITUARIO
		if(isset($apiConfig['receituario'])) {

			?>

			<script type="text/javascript">
				var medicamentos = [];

				const formatTemplate = (state) => {
					if (!state.id) return state.text;
					var $state = $('<span style="display:flex; align-items:center; gap:.5rem;">' + state.text + '</span>');
					return $state;
				}

				const formatTemplateSelection = (state) => {
					if (!state.id) return state.text;

					var $state = $('<span> ' + state.text + '</span>');
					return $state;
				}

				const receituarioMedicamentosListar = () => {

					$('.js-asideReceituario-tabela tbody').html('');

					medicamentos.forEach(x=>{
						$('.js-asideReceituario-tabela tbody').append(`<tr class="js-receituarioMedicacao-item"><td><h1>${x.medicamento} - ${x.quantidade} ${x.tipo}</h1><p>${x.posologia}</p></td></tr>`);
					});

					$('.js-asideReceituario-receitas').val(JSON.stringify(medicamentos));

				}

				$(function(){

					$('.js-asideReceituario-tabela').on('click','.js-receituarioMedicacao-item',function(){

						
						let index = $(this).index('.js-asideReceituario-tabela .js-receituarioMedicacao-item');
						if(medicamentos[index]) {

							$('.js-asideReceituario-id_medicamento').val(medicamentos[index].id_medicamento).trigger('change');
							$('.js-asideReceituario-medicamento').val(medicamentos[index].medicamento);
							$('.js-asideReceituario-quantidade').val(medicamentos[index].quantidade);
							$('.js-asideReceituario-tipo').val(medicamentos[index].tipo);
							$('.js-asideReceituario-posologia').val(medicamentos[index].posologia);
							$('.js-asideReceituario-index').val(index);
							$('.js-asideReceituario-controleEspecial').prop('checked',medicamentos[index].controleespecial==1?true:false);
							$('.js-asideReceituario-medicamento-remover').show();

						}

					});
					$('.aside-prontuario-receituario .js-asideReceituario-data').datetimepicker({
						timepicker:false,
						format:'d/m/Y',
						scrollMonth:false,
						scrollTime:false,
						scrollInput:false,
					});

					$('.aside-prontuario-receituario .js-salvarReceituario').click(function(){
						
						let erro = '';
						
						let dataReceita = $('.aside-prontuario-receituario .js-asideReceituario-data').val();
						let tipo_receita = $('.aside-prontuario-receituario .js-asideReceituario-tipo_receita').val();
						let id_profissional = $('.aside-prontuario-receituario .js-asideReceituario-id_profissional').val();

						

						if(dataReceita.length==0) erro='Preencha o campo de Data do Receituário';
						else if(tipo_receita.length==0) erro='Preencha o campo de Tipo do Receituário';
						else if(id_profissional.length==0) erro='Preencha o campo de Profissional';

						
						if(erro.length>0) {
							swal({title: "Erro!", text: erro, html:true, type:"error", confirmButtonColor: "#424242"});
						} else {
							let obj = $(this);
							let obHTMLAntigo = $(this).html();

							if(obj.attr('data-loading')==0) {
								
								obj.html(`<span class="iconify" data-icon="eos-icons:loading"></span>`);
								obj.attr('data-loading',1);

								let data = {'ajax':'asReceituarioPersistir',
											'id_paciente':id_paciente,
											'data':dataReceita,
											'tipo_receita':tipo_receita,
											'id_profissional':id_profissional,
											'medicamentos':medicamentos};

								$.ajax({
										type:'POST',
										data:data,
										url:baseURLApiAsidePaciente,
										success:function(rtn) {
											if(rtn.success) {
												$('.js-asideReceituario-inputs2').val('');
												$('.js-asideReceituario-inputs').val('').trigger('change');
												$('.js-asideReceituario-controleEspecial').prop('checked',false);
												$('.js-asideReceituario-medicamento-remover').hide();
												medicamentos=[];
												receituarioMedicamentosListar();
												$('.aside-close').click();
												//document.location.reload();
											} else if(rtn.error) {
												swal({title: "Erro!", text: rtn.error, type:"error", confirmButtonColor: "#424242"});
											} else {
												swal({title: "Erro!", text: "Algum erro ocorreu! Tente novamente.", type:"error", confirmButtonColor: "#424242"});
											}
											
										},
										error:function() {
											swal({title: "Erro!", text: "Algum erro ocorreu! Tente novamente.", type:"error", confirmButtonColor: "#424242"});
										} 
								}).done(function(){
									obj.html(obHTMLAntigo);
									obj.attr('data-loading',0);
								});

							}



						}
					});

					$('.aside-prontuario-receituario select.js-asideReceituario-id_medicamento').select2({
						ajax: {
							url: 'includes/api/apiAsidePaciente.php?ajax=medicamentos',
							data: function (params) {
									var query = {
										search: params.term,
										type: 'public'
									}
								// ?search=[term]&type=public
								return query;
							},
							processResults: function (data) {
								// Transforms the top-level key of the response object from 'items' to 'results'
								return {
									results: data.items
								};
							}

						},
						templateResult:formatTemplate,
						//	templateSelection:formatTemplateSelection,
						//dropdownParent: $(".modal")
					});

					$('.aside-prontuario-receituario select.js-asideReceituario-id_medicamento').on('select2:select',function(e){
						//console.log(e.params.data)

						if(e.params.data.medicamento) $('.js-asideReceituario-medicamento').val(e.params.data.medicamento);
						if(e.params.data.quantidade) $('.js-asideReceituario-quantidade').val(e.params.data.quantidade);
						if(e.params.data.tipo) $('.js-asideReceituario-tipo').val(e.params.data.tipo);
						if(e.params.data.posologia) $('.js-asideReceituario-posologia').val(e.params.data.posologia);
						
						if(e.params.data.controleEspecial==1) $('.js-asideReceituario-controleEspecial').prop('checked',true);
						else $('.js-asideReceituario-controleEspecial').prop('checked',false);
					});

					$('.aside-prontuario-receituario .js-asideReceituario-medicamento-add').click(function(){

						let index = $('.js-asideReceituario-index').val();
						let id_medicamento = $('.js-asideReceituario-id_medicamento').val();
						let medicamento = $('.js-asideReceituario-medicamento').val();
						let quantidade = $('.js-asideReceituario-quantidade').val();
						let tipo = $('.js-asideReceituario-tipo').val();
						let tipoExibe = $('.js-asideReceituario-tipo option:selected').text();
						let posologia = $('.js-asideReceituario-posologia').val();
						let controleespecial = $('.js-asideReceituario-controleEspecial').prop('checked')===true?1:0;

						let novoMedicamento = { medicamento, id_medicamento, quantidade, tipo, tipoExibe, posologia, controleespecial }


						if(index.length>0 && $.isNumeric(eval(index))) medicamentos[index]=novoMedicamento;
						else medicamentos.push(novoMedicamento);

						receituarioMedicamentosListar();

						$('.js-asideReceituario-inputs').val('').trigger('change');
						$('.js-asideReceituario-controleEspecial').prop('checked',false);
						$('.js-asideReceituario-medicamento-remover').hide();

					});

					$('.aside-prontuario-receituario .js-asideReceituario-medicamento-remover').click(function(){
						let index = $('.js-asideReceituario-index').val();
						if(index.length>0 && $.isNumeric(eval(index))) {
							medicamentos.splice(index,1);
						}
						receituarioMedicamentosListar();
						$('.js-asideReceituario-inputs').val('').trigger('change');

					})

				});

			</script>

			<section class="aside aside-prontuario-receituario" style="display: none;">
				<div class="aside__inner1">
					<header class="aside-header">
						<h1>Receituário</h1>
						<a href="javascript:;" class="aside-header__fechar aside-close"><i class="iconify" data-icon="fluent:dismiss-24-filled"></i></a>
					</header>
					<form method="post" class="aside-content form">
						<section class="filter">
							<div class="filter-group"></div>
							<div class="filter-group">
								<div class="filter-form form">
									<dl>
										<dd><a href="" class="button"><i class="iconify" data-icon="fluent:delete-24-regular"></i></a></dd>
									</dl>
									<dl>
										<dd><button type="button" class="button button_main  js-salvarReceituario" data-loading="0"><i class="iconify" data-icon="fluent:checkmark-12-filled"></i> <span>Salvar</span></button></dd>
									</dl>
								</div>								
							</div>
						</section>
						<div class="box">
							<?php /* <fieldset>
								<legend>Cabeçalho</legend> */ ?>
								<div class="colunas3">
									<dl>
										<dt>Data do Receituário</dt>
										<dd>
											<input type="tel" class="data js-asideReceituario-data js-asideReceituario-inputs2" value="<?php echo date('d/m/Y');?>" /></dd>
										</dd>
									</dl>
									<dl>
										<dt>Tipo de Uso</dt>
										<dd>
											<select class="js-asideReceituario-tipo_receita js-asideReceituario-inputs2">
												<option value="">-</option>
												<?php
												foreach($_tiposReceitas as $k=>$v) {
													echo '<option value="'.$k.'"'.($k=='interno'?' selected':'').'>'.$v.'</option>';
												}
												?>
											</select>
										</dd>
									</dl>
									<dl>
										<dt>Cirurgião Dentista</dt>
										<dd>
											<select class="js-asideReceituario-id_profissional js-asideReceituario-inputs2">
												<option value="">-</option>
												<?php
												foreach($_profissionais as $p) {
													if($p->check_agendamento==0 or $p->contratacaoAtiva==0) continue;
													echo '<option value="'.$p->id.'"'.((is_object($evolucao) and $evolucao->id_profissional==$p->id)?' selected':'').'>'.utf8_encode($p->nome).'</option>';
												}
												?>
											</select>
										</dd>
									</dl>
								</div>					
							<?php /* </fieldset> */ ?>

							<?php /* <fieldset>
								<legend>Adicionar Medicamento</legend> */ ?>
								<input type="hidden" class="js-asideReceituario-index js-asideReceituario-inputs" />
								<div class="colunas3">
									<dl>
										<dt>Medicamento</dt>
										<dd class="">
											<select class="js-asideReceituario-id_medicamento js-asideReceituario-inputs">
												<option value="">...</option>
											</select>
											<a href="javascript:;" data-aside="medicamento" data-aside-sub><i class="iconify" data-icon="fluent:add-circle-24-regular"></i></a>

										</dd>
										<input type="hidden" class="js-asideReceituario-medicamento js-asideReceituario-inputs">
									</dl>
									<dl>
										<dt>Quantidade</dt>
										<dd>
											<input type="tel" class="js-asideReceituario-quantidade  js-asideReceituario-inputs" />
										</dd>
									</dl>
									<dl>
										<dt>Tipo</dt>
										<dd>
											<select class="js-asideReceituario-tipo  js-asideReceituario-inputs">
												<option value="">-</option>
												<?php
												foreach($_medicamentosTipos as $k=>$v) {
													echo '<option value="'.$k.'"'.($values['tipo']==$k?' selected':'').'>'.$v.'</option>';
												}
												?>								
											</select>
										</dd>
									</dl>
								</div>		
								<dl>
									<dd>
										<label>
											<input type="checkbox" class="js-asideReceituario-controleEspecial js-asideReceituario-inputs" value="1" /> Medicamento de controle especial
										</label>
									</dd>
								</dl>
								<dl>
									<dt>Posologia</dt>
									<dd>
										<input type="text" class="js-asideReceituario-posologia  js-asideReceituario-inputs">
										<button type="button" class="button button_main js-asideReceituario-medicamento-add"><i class="iconify" data-icon="fluent:add-circle-24-regular"></i></button>
										<button type="button" class="button js-asideReceituario-medicamento-remover" style="display: none;"><i class="iconify" data-icon="fluent:delete-24-regular"></i></button>
									</dd>
								</dl>
							<?php /* </fieldset> */ ?>


							<?php /* <fieldset> /* ?>
								<textarea class="js-asideReceituario-receitas js-asideReceituario-inputs" style="display:none;"></textarea>
								<?php /* <legend>Medicamentos Adicionados</legend> */ ?>
							<?php /* </fieldset> */ ?>
							
						</div>
						<div class="list1" style="margin-top: 15px;">
							<table class="js-asideReceituario-tabela">
								<tbody>
									<?php
									/*
									<tr>
										<td>
											<h1>1 - 1 tubo(s)</h1>
											<p>tomar 1 comprimido</p>
										</td>								
									</tr>		
									*/
									?>
								</tbody>						
							</table>
						</div>
					</form>
				</div>
			</section>

			<script type="text/javascript">
				var asMedicamentos = [];

				const asMedicamentosListar = () => {
					
					$('.js-asideReceituarioMedicamento-table tbody').html('');

					asMedicamentos.forEach(x=>{

						$(`.js-asideReceituarioMedicamento-table tbody`).append(`<tr class="aside-open">
																					<td><h1>${x.titulo}</h1></td>
																					<td style="text-align:right;"><a href="javascript:;" class="button js-asMedicamentos-editar" data-id="${x.id}"><i class="iconify" data-icon="fluent:edit-24-regular"></i></a></td>
																				</tr>`);

					});
					
				}

				const asMedicamentosAtualizar = () => {	
					let data = `ajax=asMedicamentosListar`;

					$.ajax({
						type:"POST",
						url:baseURLApiAsidePaciente,
						data:data,
						success:function(rtn) {
							if(rtn.success) {
								asMedicamentos = rtn.medicamentos;
								asMedicamentosListar();
							}
						}
					})
				}
				
				const asMedicamentosEditar = (id) => {
					let data = `ajax=asMedicamentosEditar&id=${id}`;
					$.ajax({
						type:"POST",
						url:baseURLApiAsidePaciente,
						data:data,
						success:function(rtn) {
							if(rtn.success) {

								$(`.js-asideReceituarioMedicamento-id`).val(rtn.id);
								$(`.js-asideReceituarioMedicamento-medicamento`).val(rtn.medicamento);
								$(`.js-asideReceituarioMedicamento-quantidade`).val(rtn.quantidade);
								$(`.js-asideReceituarioMedicamento-tipo`).val(rtn.tipo);
								$(`.js-asideReceituarioMedicamento-posologia`).val(rtn.posologia);

								if(rtn.controleEspecial==1) $(`.js-asideReceituarioMedicamento-controleEspecial`).prop('checked',true);
								else $(`.js-asideReceituarioMedicamento-controleEspecial`).prop('checked',false);

								
								$('.js-asMedicamentos-remover').show();

							} else if(rtn.error) {
								swal({title: "Erro!", text: rtn.error, type:"error", confirmButtonColor: "#424242"});
							} else {
								swal({title: "Erro!", text: "Algum erro ocorreu durante a edição deste registro!", type:"error", confirmButtonColor: "#424242"});
							}
						},
						error:function(){
							swal({title: "Erro!", text: "Algum erro ocorreu durante a edição deste registro!", type:"error", confirmButtonColor: "#424242"});
						}
					});
				}

				
				$(function(){

					asMedicamentosAtualizar();

					$('.aside-medicamento .aside-close').click(function(){
						$(`.js-asideReceituarioMedicamento-id`).val(0);
						$(`.js-asideReceituarioMedicamento-inputs`).val(``);
						$(`.js-asideReceituarioMedicamento-controleEspecial`).prop('checked',false);
						$('.js-asMedicamentos-remover').hide();
					})

					$('.aside-medicamento .js-salvarMedicamento').click(function(){
						let obj = $(this);
						let objHTMLAntigo = $(this).html();
						if(obj.attr('data-loading')==0) {

							let id = $(`.js-asideReceituarioMedicamento-id`).val();
							let medicamento = $(`.js-asideReceituarioMedicamento-medicamento`).val();
							let quantidade = $(`.js-asideReceituarioMedicamento-quantidade`).val();
							let tipo = $(`.js-asideReceituarioMedicamento-tipo`).val();
							let posologia = $(`.js-asideReceituarioMedicamento-posologia`).val();
							let controleEspecial = $(`.js-asideReceituarioMedicamento-controleEspecial`).prop('checked')===true?1:0;

						
							let erro = '';
							if(medicamento.length==0) erro='Preencha o campo de Medicamento!';
							else if(quantidade.length==0) erro='Preencha o campo de Quantidade!';
							else if(tipo.length==0) erro='Preencha o campo de Tipo!';
							else if(posologia.length==0) erro='Preencha o campo de Posologia!';


							if(erro.length>0) {
								swal({title: "Erro!", text: erro, type:"error", confirmButtonColor: "#424242"});
							}  else {

								obj.html(`<span class="iconify" data-icon="eos-icons:loading"></span>`);
								obj.attr('data-loading',1);

								let data = {
											'ajax':'asMedicamentosPersistir',
											'id':id,
											'medicamento':medicamento,
											'quantidade':quantidade,
											'tipo':tipo,
											'posologia':posologia,
											'controleEspecial':controleEspecial
								};

								
								$.ajax({
									type:'POST',
									data:data,
									url:baseURLApiAsidePaciente,
									success:function(rtn) {
										if(rtn.success) {
											asMedicamentosAtualizar();	

											// se add
											if(id==0) {
												//$('.js-asideReceituario-id_medicamento').val(rtn.id_medicamento);
												$('.js-asideReceituario-medicamento').val(medicamento);
												$('.js-asideReceituario-quantidade').val(quantidade);
												$('.js-asideReceituario-tipo').val(tipo);
												$('.js-asideReceituario-posologia').val(posologia);
												$('.aside-medicamento .aside-close').trigger('click');
												var newOption = new Option(medicamento, rtn.id_medicamento, false, false);
												$('.js-asideReceituario-id_medicamento').append(newOption).trigger('change');
											}

											$(`.js-asideReceituarioMedicamento-id`).val(0);
											$(`.js-asideReceituarioMedicamento-inputs`).val(``);
											$(`.js-asideReceituarioMedicamento-controleEspecial`).prop('checked',false);

										} else if(rtn.error) {
											swal({title: "Erro!", text: rtn.error, type:"error", confirmButtonColor: "#424242"});
										} else {
											swal({title: "Erro!", text: "Algum erro ocorreu! Tente novamente.", type:"error", confirmButtonColor: "#424242"});
										}
										
									},
									error:function() {
										swal({title: "Erro!", text: "Algum erro ocorreu! Tente novamente.", type:"error", confirmButtonColor: "#424242"});
									}
								}).done(function(){
									$('.js-asMedicamentos-remover').hide();
									obj.html(objHTMLAntigo);
									obj.attr('data-loading',0);
								});

							}
						}
					})

					$('.js-asideReceituarioMedicamento-table').on('click','.js-asMedicamentos-editar',function(){
						let id = $(this).attr('data-id');
						asMedicamentosEditar(id);
					});

					$('.aside-medicamento .js-asMedicamentos-remover').click(function(){

						let obj = $(this);
						let objHTMLAntigo = $(this).html();

						if(obj.attr('data-loading')==0) {

							let id = $(`.js-asideReceituarioMedicamento-id`).val();
							swal({
								title: "Atenção",
								text: "Você tem certeza que deseja remover este medicamento?",
								type: "warning",
								showCancelButton: true,
								confirmButtonColor: "#DD6B55",
								confirmButtonText: "Sim!",
								cancelButtonText: "Não",
								closeOnConfirm:false,
								closeOnCancel: false }, 
								function(isConfirm){   
									if (isConfirm) {   

										obj.html(`<span class="iconify" data-icon="eos-icons:loading"></span>`);
										obj.attr('data-loading',1);
										let data = `ajax=asMedicamentosRemover&id=${id}`; 
										$.ajax({
											type:"POST",
											data:data,
											url:baseURLApiAsidePaciente,
											success:function(rtn) {
												if(rtn.success) {
													asMedicamentosAtualizar();	

													$(`.js-asideReceituarioMedicamento-id`).val(0);
													$(`.js-asideReceituarioMedicamento-inputs`).val(``);
													$(`.js-asideReceituarioMedicamento-controleEspecial`).prop('checked',false);

													swal.close();   
												} else if(rtn.error) {
													swal({title: "Erro!", text: rtn.error, type:"error", confirmButtonColor: "#424242"});
												} else {
													swal({title: "Erro!", text: "Algum erro ocorreu durante a remoção deste horário!", type:"error", confirmButtonColor: "#424242"});
												}
											},
											error:function(){
												swal({title: "Erro!", text: "Algum erro ocorreu durante a remoção deste horário!", type:"error", confirmButtonColor: "#424242"});
											}
										}).done(function(){
											$('.js-asMedicamentos-remover').hide();
											obj.html(objHTMLAntigo);
											obj.attr('data-loading',0);
										});
									} else {   
										swal.close();   
									} 
								});
						}
					});

				});
			</script>

			<section class="aside aside-medicamento" style="display: none;">
				<div class="aside__inner1">

					<header class="aside-header">
						<h1>Medicamentos</h1>
						<a href="javascript:;" class="aside-header__fechar aside-close"><i class="iconify" data-icon="fluent:dismiss-24-filled"></i></a>
					</header>

					<form method="post" class="aside-content form">
						<input type="hidden" class="js-asideReceituarioMedicamento-id" value="0" />
						<section class="filter">
							<div class="filter-group"></div>
							<div class="filter-group">
								<div class="filter-form form">
									<dl>
										<dd><a href="javascript:;" class="button js-asMedicamentos-remover" data-loading="0" style="display:none"><i class="iconify" data-icon="fluent:delete-24-regular"></i></a></dd>
									</dl>
									<dl>
										<dd><button type="button" class="button button_main js-salvarMedicamento" data-loading="0"><i class="iconify" data-icon="fluent:checkmark-12-filled"></i> <span>Salvar</span></button></dd>
									</dl>
								</div>								
							</div>
						</section>

						<dl>
							<dt>Medicamento</dt>
							<dd><input type="text" class="js-asideReceituarioMedicamento-medicamento js-asideReceituarioMedicamento-inputs" /></dd>
						</dl>
						<div class="colunas3">
							<dl>
								<dt>Quantidade</dt>
								<dd><input type="tel" class="js-asideReceituarioMedicamento-quantidade js-asideReceituarioMedicamento-inputs" /></dd>
							</dl>
							<dl>
								<dt>Tipo</dt>
								<dd>
									<select class="js-asideReceituarioMedicamento-tipo js-asideReceituarioMedicamento-inputs">
										<option value="">-</option>
										<?php
										foreach($_medicamentosTipos as $k=>$v) {
											echo '<option value="'.$k.'"'.($values['tipo']==$k?' selected':'').'>'.$v.'</option>';
										}
										?>										
									</select>
								</dd>
							</dl>
						</div>
						<dl>
							<dt>Posologia</dt>
							<dd><input type="text" class="js-asideReceituarioMedicamento-posologia js-asideReceituarioMedicamento-inputs" /></dd>
						</dl>
						<dl>
							<dd>
								<label>
									<input type="checkbox" class="js-asideReceituarioMedicamento-controleEspecial js-asideReceituarioMedicamento-inputs" value="1" /> Medicamento de controle especial
								</label>
							</dd>
						</dl>

						<div class="list2" style="margin-top:2rem;">
							<table class="js-asideReceituarioMedicamento-table">
								<thead>
									<tr>									
										<th>MEDICAMENTO</th>
										<th></th>
									</tr>
								</thead>
								<tbody>							
								</tbody>
							</table>
						</div>
					</form>
				</div>
			</section>
			<?php
		}

	# PRÓXIMA CONSULTA
		if(isset($apiConfig['proximaConsulta'])) {
			$_cadeiras=array();
			$sql->consult($_p."parametros_cadeiras","*","where lixo=0  order by titulo asc");
			while($x=mysqli_fetch_object($sql->mysqry)) $_cadeiras[$x->id]=$x;
			?>

			<script type="text/javascript" src="js/aside.funcoes.js"></script>
			<script type="text/javascript">
				/*var medicamentos = [];
					const receituarioMedicamentosListar = () => {
					$('.js-asideReceituario-tabela tbody').html('');
					medicamentos.forEach(x=>{
						$('.js-asideReceituario-tabela tbody').append(`<tr class="js-receituarioMedicacao-item"><td><h1>${x.medicamento} - ${x.quantidade} ${x.tipo}</h1><p>${x.posologia}</p></td></tr>`);
					});
					$('.js-asideReceituario-receitas').val(JSON.stringify(medicamentos));
				}*/
				const asideEvolucaoProximaConsulta = () => {
					$('.aside-prontuario-proximaConsulta .aside-header h1').html('Próxima Consulta');
					$(".aside-prontuario-proximaConsulta .js-btn-acao:eq(0)").click();
					setTimeout(function(){
								$('.aside-prontuario-proximaConsulta .js-profissionais').chosen();
								$('.aside-prontuario-proximaConsulta .js-profissionais').trigger('chosen:updated');
							})
				}

				$(function(){

					$('#js-aside-proximaConsulta  .js-btn-acao').click(function(){
						$('#js-aside-proximaConsulta .js-btn-acao').removeClass('active');
						$(this).addClass('active');

						if($(this).attr('data-tipo')=="queroAgendar") {
							$('#js-aside-proximaConsulta  .js-ag-agendamento-lembrete').hide();
							$('#js-aside-proximaConsulta  .js-ag-agendamento-altaPeriodicidade').hide();
							$('#js-aside-proximaConsulta  .js-ag-agendamento-queroAgendar').show();

							$('#js-aside-proximaConsulta  .js-profissionais-qa').chosen();
							$('#js-aside-proximaConsulta  input[name=tipo]').val('queroAgendar');
						} else if($(this).attr('data-tipo')=="altaPeriodicidade") {
							$('#js-aside-proximaConsulta  .js-ag-agendamento-lembrete').hide();
							$('#js-aside-proximaConsulta  .js-ag-agendamento-queroAgendar').hide();
							$('#js-aside-proximaConsulta  .js-ag-agendamento-altaPeriodicidade').show();

							$('#js-aside-proximaConsulta  .js-profissionais-qa').chosen();
							$('#js-aside-proximaConsulta  input[name=tipo]').val('altaPeriodicidade');
						} else {
							$('#js-aside-proximaConsulta  .js-ag-agendamento-altaPeriodicidade').hide();
							$('#js-aside-proximaConsulta  .js-ag-agendamento-queroAgendar').hide();
							$('#js-aside-proximaConsulta  .js-ag-agendamento-lembrete').show();
							$('#js-aside-proximaConsulta  input[name=tipo]').val('lembrete');
						}
					});
					
					$('#js-aside-proximaConsulta  .js-ag-agendamento .js-salvar').click(function(){
						let tipo = $('#js-aside-proximaConsulta input[name=tipo]').val();
						//let id_paciente = $('#js-aside-proximaConsulta .js-id_paciente').val();
						let id_agenda_origem = $('#js-aside-proximaConsulta .js-proximaConsulta-id_agenda').val();

						if(tipo=="queroAgendar") {
							let agenda_data = $('#js-aside-proximaConsulta input[name=agenda_data]').val();
							let agenda_duracao = $('#js-aside-proximaConsulta select[name=agenda_duracao]').val();
							let id_cadeira = $('#js-aside-proximaConsulta select[name=id_cadeira]').val();
							let id_profissional = $('#js-aside-proximaConsulta select.js-profissionais-qa').val();
							let agenda_hora = $('#js-aside-proximaConsulta select[name=agenda_hora]').val();
							let obs = $('#js-aside-proximaConsulta textarea.js-obs-qa').val();
							let erro = '';

							if(agenda_data.length==0) erro='Defina a <b>Data do Agendamento</b>';
							else if(agenda_duracao.length==0) erro='Defina a <b>Duração de Agendamento</b>';
							else if(id_cadeira.length==0) erro='Defina o <b>Consultório do Agendamento</b>';
							else if(id_profissional.length==0) erro='Defina o <b>Profissional do Agendamento</b>';
							else if(agenda_hora.length==0) erro='Defina a <b>Hora do Agendamento</b>';

							if(erro.length==0) {


								let obj = $(this);
								let objHTMLAntigo = $(this).html();

								if(obj.attr('data-loading')==0) {
									
									obj.html(`<span class="iconify" data-icon="eos-icons:loading"></span>`);
									obj.attr('data-loading',1);

									let data = `ajax=asRelacionamentoPacienteQueroAgendar&id_paciente=${id_paciente}&agenda_data=${agenda_data}&agenda_duracao=${agenda_duracao}&id_cadeira=${id_cadeira}&id_profissional=${id_profissional}&agenda_hora=${agenda_hora}&obs=${obs}&id_agenda_origem=${id_agenda_origem}`;

									data = {
										'ajax':'asRelacionamentoPacienteQueroAgendar',
										'id_paciente':id_paciente,
										'agenda_data':agenda_data,
										'agenda_duracao':agenda_duracao,
										'id_cadeira':id_cadeira,
										'id_profissional':id_profissional,
										'agenda_hora':agenda_hora,
										'obs':obs,
										'id_agenda_origem':id_agenda_origem

									}



									$.ajax({
											type:'POST',
											data:data,
											url:baseURLApiAsidePaciente,
											success:function(rtn) {
												if(rtn.success) {

												swal({title: "Sucesso!", text: "Salvo com Sucesso!", type:"sucess", confirmButtonColor: "#424242"});
												document.location.reload();

												} else if(rtn.error) {
													swal({title: "Erro!", text: rtn.error, type:"error", confirmButtonColor: "#424242"});
												} else {
													swal({title: "Erro!", text: "Algum erro ocorreu! Tente novamente.", type:"error", confirmButtonColor: "#424242"});
												}
												
											},
											error:function() {
												swal({title: "Erro!", text: "Algum erro ocorreu! Tente novamente.", type:"error", confirmButtonColor: "#424242"});
											} 
									}).done(function(){
										obj.html(objHTMLAntigo);
										obj.attr('data-loading',0);
									});


								}

							} else {
								swal({title: "Erro!", text: erro, html:true, type:"error", confirmButtonColor: "#424242"});
							}
						}
						else if(tipo=="lembrete") {
							let retorno = $(`#js-aside-proximaConsulta .js-retorno`).val();
							let duracao = $(`#js-aside-proximaConsulta .js-agenda_duracao`).val();
							let laboratorio = $(`#js-aside-proximaConsulta .js-laboratorio`).prop('checked')===true?1:0;
							let imagem = $(`#js-aside-proximaConsulta .js-imagem`).prop('checked')===true?1:0;
							let profissionais = $(`#js-aside-proximaConsulta .js-profissionais-lembrete`).val();
							let obs = $(`#js-aside-proximaConsulta .js-obs`).val();
							let erro = '';
							

							if(retorno.length==0) erro='Preencha o campo <b>Retorno em</b>';
							else if(duracao.length==0) erro='Preencha a <b>Duração</b>';
							else if(profissionais.length==0) erro='Selecione pelo menos um profissional';
							else if(obs.length==0) erro='Preencha o campo <b>Observações</b>';
							
							if(erro.length==0) {

								let obj = $(this);
								let objHTMLAntigo = $(this).html();

								if(obj.attr('data-loading')==0) {
									
									obj.html(`<span class="iconify" data-icon="eos-icons:loading"></span>`);
									obj.attr('data-loading',1);

									let data = `ajax=proximaConsultaPersistir&retorno=${retorno}&duracao=${duracao}&laboratorio=${laboratorio}&imagem=${imagem}&profissionais=${profissionais}&obs=${obs}&id_paciente=${id_paciente}&id_agenda_origem=${id_agenda_origem}`;

									$.ajax({
										type:'POST',
										data:data,
										url:baseURLApiAsidePaciente,
										success:function(rtn) {
											if(rtn.success) {
												swal({title: "Sucesso!", text: "Salvo com Sucesso!", type:"sucess", confirmButtonColor: "#424242"});
												document.location.reload();
											} else if(rtn.error) {
												swal({title: "Erro!", text: rtn.error, type:"error", confirmButtonColor: "#424242"});
											} else {
												console.log(rtn)
												swal({title: "Erro!", text: "Algum erro ocorreu! Tente novamente.", type:"error", confirmButtonColor: "#424242"});
											}
											
										},
										error:function(rtn) {
											console.log(rtn)
											swal({title: "Erro!", text: "Algum erro ocorreu! Tente novamente.", type:"error", confirmButtonColor: "#424242"});
										}
									}).done(function(){
										obj.html(objHTMLAntigo);
										obj.attr('data-loading',0);
									});
								}

							} else {
								swal({title: "Erro!", text: erro, html:true, type:"error", confirmButtonColor: "#424242"});
							}
						}
						else if(tipo=="altaPeriodicidade") {
							let periodicidade = $('#js-aside-proximaConsulta .js-periodicidade_select').val();
							let alta = $('#js-aside-proximaConsulta .js-periodicidade_alta').val();
							let periodicidadeDescricao = $('#js-aside-proximaConsulta .js-periodicidade_select option:selected').attr('data-descricao');


							let erro= '';
							if(periodicidade.length==0) erro='Selecione a Periodicidade do paciente';

							if(erro.length==0) {

								let obj = $(this);
								let objHTMLAntigo = $(this).html();

								if(obj.attr('data-loading')==0) {
									
									obj.html(`<span class="iconify" data-icon="eos-icons:loading"></span>`);
									obj.attr('data-loading',1);

									let data = `ajax=proximaConsultaAltaPeriodicidade&periodicidade=${periodicidade}&id_paciente=${id_paciente}&id_agenda_origem=${id_agenda_origem}&alta=${alta}`;
									
									$.ajax({
										type:'POST',
										data:data,
										url:baseURLApiAsidePaciente,
										success:function(rtn) {
											if(rtn.success) {
												swal({title: "Sucesso!", text: "Salvo com Sucesso!", type:"sucess", confirmButtonColor: "#424242"});
												document.location.reload();
											} else if(rtn.error) {
												swal({title: "Erro!", text: rtn.error, type:"error", confirmButtonColor: "#424242"});
											} else {
												swal({title: "Erro!", text: "Algum erro ocorreu! Tente novamente.", type:"error", confirmButtonColor: "#424242"});
											}
											
										},
										error:function() {
											swal({title: "Erro!", text: "Algum erro ocorreu! Tente novamente...", type:"error", confirmButtonColor: "#424242"});
										}
									}).done(function(){
										obj.html(objHTMLAntigo);
										obj.attr('data-loading',0);
									});
								}

							} else {
								swal({title: "Erro!", text: erro, html:true, type:"error", confirmButtonColor: "#424242"});
							}
						}
					});

					$('#js-aside-proximaConsulta  .js-ag-prontuario .js-salvarProntuario').click(function(){


						let obj = $(this);
						let objTextoAntigo = $(this).html();
						let id_profissional = $('#js-aside-proximaConsulta .js-prontuario-profissional').val();
						let prontuario = $('#js-aside-proximaConsulta .js-prontuario').val();
						let dataProntuario = $('#js-aside-proximaConsulta .js-prontuario-data').val();

						let erro='';

						if(id_profissional.length==0) erro='Selecione o Profissional';
						else if(prontuario.length==0) erro='Digite o prontuário para salvar'

						if(erro.length==0) {
							if(obj.attr('data-loading')==0) {

								obj.html(`<span class="iconify" data-icon="eos-icons:loading"></span>`);
								obj.attr('data-loading',1);

								let data = `ajax=prontuarioPersistir&id_profissional=${id_profissional}&prontuario=${prontuario}&id_paciente=${id_paciente}&dataProntuario=${dataProntuario}`;

								data = {'ajax':'prontuarioPersistir',
											'id_profissional':id_profissional,
											'prontuario':prontuario,
											'id_paciente':id_paciente,
											'dataProntuario':dataProntuario}

								$.ajax({
									type:"POST",
									url:baseURLApiAsidePaciente,
									data:data,
									success:function(rtn) {
										if(rtn.success) {
											
										} else if(rtn.error) {
											swal({title: "Erro!", text: rtn.error, type:"error", confirmButtonColor: "#424242"});
										} else {
											swal({title: "Erro!", text: 'Algum erro ocorreu ao salvar o prontuário. Tente novamente!', type:"error", confirmButtonColor: "#424242"});
										}
									}
								}).done(function(){
									obj.attr('data-loading',0);
									obj.html(objTextoAntigo);
								})
							}
						} else {
							swal({title: "Erro!", text: erro, type:"error", confirmButtonColor: "#424242"});
						}

					});
				});

			</script>
			<section class="aside aside-prontuario-proximaConsulta" style="display: none;">
				<div class="aside__inner1">
					<header class="aside-header">
						<h1>Próxima Consulta</h1>
						<a href="javascript:;" class="aside-header__fechar aside-close"><i class="iconify" data-icon="fluent:dismiss-24-filled"></i></a>
					</header>

					<form class="aside-content form">
						<div class="js-ag js-ag-agendamento">
							<section class="filter">
								<div class="button-group">
									<a href="javascript:;" class="js-btn-acao js-btn-acao-lembrete button active" data-tipo="lembrete"><span>Criar Lembrete</span></a>
									<a href="javascript:;" class="js-btn-acao js-btn-acao-queroAgendar button" data-tipo="queroAgendar"><span>Quero agendar</span></a>
								</div>
								<div class="filter-group">
									<div class="filter-form form">
										<dl>
											<dd></dd>
										</dl>
										<dl>
											<dd><button class="button button_main js-salvarProximaConsulta" data-loading="0"><i class="iconify" data-icon="fluent:checkmark-12-filled"></i> <span>Salvar</span></button></dd>
										</dl>
									</div>								
								</div>
							</section>
							<div class="js-ag-agendamento-lembrete">
								<input type="hidden" class="js-asProfissoes-id" />
								<div class="colunas4">
									<dl>
										<dt>Retorno em</dt>
										<dd class="form-comp form-comp_pos">
											<input type="number" class="js-retorno" maxlength="3" />
											<span>dias</span>
										</dd>
									</dl>
									<dl>
										<dt>Duração</dt>
										<dd class="form-comp form-comp_pos">
											<select class="js-agenda_duracao">
												<option value="">-</option>
												<?php
												foreach($optAgendaDuracao as $v) {
													if($values['agenda_duracao']==$v) $possuiDuracao=true;
													echo '<option value="'.$v.'"'.($values['agenda_duracao']==$v?' selected':'').'>'.$v.'</option>';
												}
												?>
											</select>
											<span>min</span>
										</dd>
									</dl>

									<dl class="dl2">
										<dt>&nbsp;</dt>
										<dd>
											<label>
												<input type="checkbox" class="input-switch js-laboratorio" /> Laboratório
											</label>
											<label>
												<input type="checkbox" class="input-switch js-imagem" /> Imagem
											</label>
										</dd>
									</dl>

								</div>
								<dl>
									<dt>Profissionais</dt>
									<dd>
										<select class="js-profissionais js-profissionais-lembrete" multiple>
											<option value=""></option>
											<?php
											foreach($_profissionais as $p) {
												if($p->check_agendamento==0 or $p->contratacaoAtiva==0) continue;
												echo '<option value="'.$p->id.'">'.utf8_encode($p->nome).'</option>';
											}
											?>
										</select>
									</dd>
								</dl>
								<dl>
									<dt>Observações</dt>
									<dd>
										<textarea class="js-obs" style="height:80px;"></textarea>
									</dd>
								</dl>

								<div class="js-ag-agendamentoFuturos" style="">
									<div class="list1">
										<table>
										</table>
									</div>
								</div>
							</div>
							<div class="js-ag-agendamento-queroAgendar">
								<div class="colunas3">
									<dl>
										<dt>Data</dt>
										<dd class="form-comp"><span><i class="iconify" data-icon="fluent:calendar-ltr-24-regular"></i></span><input type="tel" name="agenda_data" class="data datecalendar" /></dd>
									</dl>
								
									<dl>
										<dt>Duração</dt>
										<dd class="form-comp form-comp_pos">
											<?php /*<input type="tel" name="agenda_duracao" class="" />*/?>
											<select name="agenda_duracao">
												<option value="">-</option>
												<?php
												foreach($optAgendaDuracao as $v) {
													echo '<option value="'.$v.'">'.$v.'</option>';
												}
												?>
											</select>
											<span>min</span>
										</dd>
									</dl>

									<dl>
										<dt>Consultório</dt>
										<dd>
											<select name="id_cadeira">
												<option value=""></option>
												<?php
												foreach($_cadeiras as $p) {
													echo '<option value="'.$p->id.'"'.($values['id_cadeira']==$p->id?' selected':'').'>'.utf8_encode($p->titulo).'</option>';
												}
												?>
											</select>
										</dd>
									</dl>
								</div>
								<div class="colunas3">
									<dl class="dl2">
										<dt>Profissionais</dt>
										<dd>
											<select class="js-profissionais-qa js-select-profissionais">
												<option value=""></option>
												<?php
												foreach($_profissionais as $p) {
													if($p->check_agendamento==0 or $p->contratacaoAtiva==0) continue;
													echo '<option value="'.$p->id.'">'.utf8_encode($p->nome).'</option>';
												}
												?>
											</select>
										</dd>
									</dl>
									<dl>
										<dt>Hora</dt>
										<dd class="form-comp">
											<span><i class="iconify" data-icon="fluent:clock-24-regular"></i></span>
											<select name="agenda_hora">
												<option value="">Selecione o horário</option>
											</select>
										</dd>
									</dl>
								</div>

								<dl>
									<dt>Observações</dt>
									<dd>
										<textarea class="js-obs-qa" style="height:80px;"></textarea>
									</dd>
								</dl>
							</div>
						</div>
					</form>
				</div>
			</section> 
			


			<?php
		}
	
	# DOCUMENTOS
		if(isset($apiConfig['documentos'])) {	

		
			$_documentos=array();
			$sql->consult($_p."parametros_documentos","*","where lixo=0 order by titulo asc") ;
			while($x=mysqli_fetch_object($sql->mysqry)) {
				$_documentos[$x->id]=$x;
			}

			$_planosDeTratamento=array();
			$sql->consult($_p."pacientes_tratamentos","*","where id_paciente=$paciente->id and lixo=0 order by data desc");
			while($x=mysqli_fetch_object($sql->mysqry)) {
				$_planosDeTratamento[$x->id]=$x;
			}

				
			?>
			<script type="text/javascript">
				
				const documentoAtualizaInformacoes = () => {

					let id_documento = $('.aside-prontuario-documentos .js-asideDocumentos-id_documento').val();
					let id_planodetratamento = $('.aside-prontuario-documentos .js-asideDocumentos-id_planodetratamento').val();

					if(id_documento.length>0) {

						let data = {'ajax':'asPDocumentoSubstituir',
											'id_documento':id_documento,
											'id_planodetratamento':id_planodetratamento,
											'id_paciente':id_paciente}

						$.ajax({
								type:'POST',
								data:data,
								url:baseURLApiAsidePaciente,
								success:function(rtn) {
									if(rtn.success) {

										CKEDITOR.instances['asideDocumentos-documento'].setData(rtn.texto);

									} else if(rtn.error) {
										swal({title: "Erro!", text: rtn.error, type:"error", confirmButtonColor: "#424242"});
									} else {
										swal({title: "Erro!", text: "Algum erro ocorreu! Tente novamente.", type:"error", confirmButtonColor: "#424242"});
									}
									
								},
								error:function() {
									swal({title: "Erro!", text: "Algum erro ocorreu! Tente novamente.", type:"error", confirmButtonColor: "#424242"});
								} 
						}).done(function(){
							//obj.html(obHTMLAntigo);
							//obj.attr('data-loading',0);
						});
					}
				}

				$(function(){

					$('.aside-prontuario-documentos .js-asideDocumentos-data').datetimepicker({
						timepicker:false,
						format:'d/m/Y',
						scrollMonth:false,
						scrollTime:false,
						scrollInput:false,
					});

					$('.aside-prontuario-documentos .js-salvarDocumentos').click(function(){
						

						let erro = '';
						
						let dataDocumentos = $('.aside-prontuario-documentos .js-asideDocumentos-data').val();
						let id_documento = $('.aside-prontuario-documentos .js-asideDocumentos-id_documento').val();
						let texto = CKEDITOR.instances['asideDocumentos-documento'].getData();

						if(dataDocumentos.length==0) erro='Preencha o campo de Data';
						else if(id_documento.length==0) erro='Preencha o campo de Modelo de Documento';
						else if(texto.length==0) erro='Preencha o campo de Documento';

						
						if(erro.length>0) {
							swal({title: "Erro!", text: erro, html:true, type:"error", confirmButtonColor: "#424242"});
						} else {
							let obj = $(this);
							let obHTMLAntigo = $(this).html();

							if(obj.attr('data-loading')==0) {
								
								obj.html(`<span class="iconify" data-icon="eos-icons:loading"></span>`);
								obj.attr('data-loading',1);

								let data = {'ajax':'asPDocumentosPersistir',
											'id_paciente':id_paciente,
											'data':dataDocumentos,
											'id_documento':id_documento,
											'texto':texto};

								$.ajax({
										type:'POST',
										data:data,
										url:baseURLApiAsidePaciente,
										success:function(rtn) {
											if(rtn.success) {

												document.location.reload();

											} else if(rtn.error) {
												swal({title: "Erro!", text: rtn.error, type:"error", confirmButtonColor: "#424242"});
											} else {
												swal({title: "Erro!", text: "Algum erro ocorreu! Tente novamente.", type:"error", confirmButtonColor: "#424242"});
											}
											
										},
										error:function() {
											swal({title: "Erro!", text: "Algum erro ocorreu! Tente novamente.", type:"error", confirmButtonColor: "#424242"});
										} 
								}).done(function(){
									obj.html(obHTMLAntigo);
									obj.attr('data-loading',0);
								});

							}



						}
					});

					$('.aside-prontuario-documentos ').find('input,select,textarea').change(function(x){
						$('.aside-prontuario-documentos  input[name=alteracao]').val(1);
					});

					$('.aside-prontuario-documentos .aside-close-documentos').click(function(){
						let obj = $(this);
						if($('.aside-prontuario-documentos input[name=alteracao]').val()=="1") {
							swal({   
									title: "Atenção",   
									text: "Tem certeza que deseja fechar sem salvar as informações?",
									type: "warning",   
									showCancelButton: true,   
									confirmButtonColor: "#DD6B55",   
									confirmButtonText: "Sim!",   
									cancelButtonText: "Não",   
									closeOnConfirm: false,   
									closeOnCancel: false 
								}, function(isConfirm){   
									if (isConfirm) {   
										$(obj).parent().parent().removeClass("active");
										$(obj).parent().parent().parent().fadeOut(); 

											$('.aside-prontuario-documentos .js-asideDocumentos-inputs').val('');
											CKEDITOR.instances['asideDocumentos-documento'].setData('');
										swal.close();
							  		 } else {   
							  		 	swal.close();   
							  		 } 
							  	});
			
						} else {
							$(obj).parent().parent().removeClass("active");
							$(obj).parent().parent().parent().fadeOut();
						}

					});


					
					$('.aside-prontuario-documentos .js-asideDocumentos-id_documento').change(documentoAtualizaInformacoes);
					$('.aside-prontuario-documentos .js-asideDocumentos-id_planodetratamento').change(documentoAtualizaInformacoes);

					var fck_documento = CKEDITOR.replace('asideDocumentos-documento',{
																			language: 'pt-br',
																			width:'100%',
																			height:450
																			});
					
				});
			</script>

			<section class="aside aside-prontuario-documentos" style="display: none;">
				<div class="aside__inner1">
					<header class="aside-header">
						<h1>Documentos</h1>
						<a href="javascript:;" class="aside-header__fechar aside-close-documentos"><i class="iconify" data-icon="fluent:dismiss-24-filled"></i></a>
					</header>



					<form method="post" class="aside-content form js-form-documentos">
						<section class="filter">
							<div class="filter-group"></div>
							<div class="filter-group">
								<div class="filter-form form">
									<dl>
										<dd><a href="javascript:;" class="button"><i class="iconify" data-icon="fluent:delete-24-regular"></i></a></dd>
									</dl>
									<dl>
										<dd><button type="button" class="button button_main js-salvarDocumentos" data-loading="0"><i class="iconify" data-icon="fluent:checkmark-12-filled"></i> <span>Salvar</span></button></dd>
									</dl>
								</div>								
							</div>
						</section>

						<input type="hidden" name="alteracao" value="0" />
						<?php /* <fieldset>
							<legend>Informações</legend> */ ?>
							<div class="colunas3">
								<dl>
									<dt>Data</dt>
									<dd>
										<input type="tel" name="" class="datahora js-asideDocumentos-data" value="<?php echo date('d/m/Y');?>" /></dd>
									</dd>
								</dl>
								<dl class="dl2">
									<dt>Modelo de Documento</dt>
									<dd>
										<select class="js-asideDocumentos-id_documento js-asideDocumentos-inputs">
											<option value="">-</option>
											<?php
											foreach($_documentos as $x) {
												echo '<option value="'.$x->id.'">'.utf8_encode($x->titulo).'</option>';
											}
											?>
										</select>
									</dd>
								</dl>
							</div>

							<dl class="dl2">
								<dt>Plano de Tratamento</dt>
								<dd>
									<select class="js-asideDocumentos-id_planodetratamento js-asideDocumentos-inputs">
										<option value="">-</option>
										<?php
										foreach($_planosDeTratamento as $x) {
											if($x->status!="APROVADO") continue;
											echo '<option value="'.$x->id.'">'.utf8_encode($x->titulo).'</option>';
										}
										?>
									</select>
								</dd>
							</dl>
						<?php /* </fieldset> */ ?>
						<?php /* <fieldset>
							<legend>Documento</legend> */ ?>
							<dl>
								<dd>
									<textarea class="js-asideDocumentos-texto js-asideDocumentos-inputs" id="asideDocumentos-documento" style="height:320px;width:100%;"></textarea>
								</dd>
							</dl>
						<?php /* </fieldset> */ ?>
					</form>
				</div>
			</section>


			<?php
		}

	# PROCEDIMENTOS
		if(isset($apiConfig['procedimentos'])) {	
			?>
			<script type="text/javascript">
				var procedimentos = [];
				var id_procedimento_aevoluir = 0;

				// quando abre o asie de Procedimentos
				const asideProcedimentos = () => {


					$('.aside-prontuario-procedimentos .js-asideProcedimentos-data').val(`<?php echo date('d/m/Y');?>`);

					let data = `ajax=asProcedimentosAprovados&id_paciente=${id_paciente}&id_agenda=${id_agenda}`;

					$.ajax({
						type:"POST",
						data:data,
						url:baseURLApiAsidePaciente,
						success:function(rtn) {
							
							if(rtn.success) {
								rtn.procedimentos.forEach(t=>{
									if(t.procedimentos.length>0) {
										let optgroup = `<optgroup label="${t.titulo}">`;

										t.procedimentos.forEach(p=>{
											optgroup += `<option value="${p.id}" 
																		data-id_procedimento="${p.id_procedimento}" 
																		data-numero="${p.numero}" 
																		data-numeroTotal="${p.numeroTotal}" 
																		data-opcao="${p.opcao}" 
																		data-plano="${p.plano}" 
																		data-profissionalCor="${p.profissionalCor}" 
																		data-id_profissional="${p.id_profissional}" 
																		data-profissionalIniciais="${p.profissionalIniciais}"  
																		data-statusEvolucao="${p.statusEvolucao}" 
																		data-titulo="${p.titulo}" 
																		data-id_tratamento_procedimento="${p.id_tratamento_procedimento}"${p.disabled}>${p.titulo}</option>`;
										});

										optgroup += '</optgroup>';
										$(`.aside-prontuario-procedimentos`).find('select.js-asideProcedimentos-id_procedimento').append(optgroup);
									}
								});
								$('.js-asideProcedimentos-id_profissional').val(rtn.id_profissional);
							}

						}
					}).done(function(){
						setTimeout(function(){
									$(`.aside-prontuario-procedimentos`).find('select.js-asideProcedimentos-id_procedimento').chosen({hide_results_on_select:false,allow_single_deselect:true});
								},50);
					})

					/*setTimeout(function(){
									$(`.aside-prontuario-procedimentos`).find('select.js-asideProcedimentos-id_procedimento').chosen({hide_results_on_select:false,allow_single_deselect:true});
								},100);*/


				}

				const procedimentosListar = () => { 

					$('.js-asideProcedimentos-tabela tbody').html('');
					
					procedimentos.forEach(x=>{	

						let css = '';
						if(x.historico.length>0) css=' style="color:var(--cinza5)"';

						$('.js-asideProcedimentos-tabela tbody').append(`<tr>								
																				<td>
																					<h1>${x.titulo}</h1>
																					<p>${x.opcao} - ${x.plano}</p>
																				</td>
																				<td>
																					<select class="js-statusEvolucao">
																						<option value="iniciar">Não iniciado</option>
																						<option value="iniciado"${(x.statusEvolucao=="iniciado" || x.statusEvolucao=="iniciar")?" selected":""}>Em tratamento</option>
																						<option value="finalizado"${x.statusEvolucao=="finalizado"?" selected":""}>Finalizado</option>
																						<option value="cancelado"${x.statusEvolucao=="cancelado"?" selected":""}>Cancelado</option>
																					</select>
																				</td>								
																				<td style="text-align:right;">
																					<a href="javascript:;" class="button js-btn-historico"${css}><i class="iconify" data-icon="fluent:chat-24-regular"></i></a>
																					<a href="javascript:;" class="button js-btn-excluir"><i class="iconify" data-icon="fluent:delete-24-regular"></i></a>
																				</td>
																			</tr>`);

					});
				}

				$(function(){

					$('.js-asideProcedimentos-tabela tbody').on('change','.js-statusEvolucao',function(){
						let index = $('.aside-prontuario-procedimentos .js-statusEvolucao').index(this);
						procedimentos[index].statusEvolucao=$(this).val();
					})

					$('.aside-prontuario-procedimentos .js-asideProcedimentos-data').datetimepicker({
						timepicker:false,
						format:'d/m/Y',
						scrollMonth:false,
						scrollTime:false,
						scrollInput:false,
					});	

					// ao excluir um procedimento
					$('.aside-prontuario-procedimentos').on('click','.js-btn-excluir',function(){
					 	let index = $(this).index('.aside-prontuario-procedimentos .js-btn-excluir');

					 	let id_procedimento_aevoluir = procedimentos[index].id_procedimento_aevoluir;
					 	$('.aside-prontuario-procedimentos .js-asideProcedimentos-id_procedimento').find(`option[value=${id_procedimento_aevoluir}]`).prop('disabled',false);
					 	$('.aside-prontuario-procedimentos .js-asideProcedimentos-id_procedimento').trigger('chosen:updated');
					 	procedimentos.splice(index,1);
						procedimentosListar();
					});

					// ao clicar em historico do procedimento
					$('.aside-prontuario-procedimentos').on('click','.js-btn-historico',function(){
						//aside-prontuario-procedimentos-historico
						//js-procedimentos-historico

						let index = $('.aside-prontuario-procedimentos .js-btn-historico').index(this);

						// limpa os historicos do aside
						$('.aside-prontuario-procedimentos-historico .js-procedimentos-historico').html('');

						// limpa campo textarea do aside
						$('.aside-prontuario-procedimentos-historico .js-asideProcedimentosHistoricos-obs').val('');

						// lista os historicos do procedimento selecionado
						if(procedimentos[index]) {

							id_procedimento_aevoluir=procedimentos[index].id_procedimento_aevoluir;
							procedimentos[index].historico.forEach(x=>{

								$('.aside-prontuario-procedimentos-historico .js-procedimentos-historico').append(`<div class="history-item">
																														<h1>${x.usuario} em ${x.data}</h1>		
																														${x.obs}
																													</div>`);

							});

							$(".aside-prontuario-procedimentos-historico").fadeIn(100,function() {
								$(".aside-prontuario-procedimentos-historico .aside__inner1").addClass("active");
								$(".aside-prontuario-procedimentos-historico .js-index").val(index);
							});
						}
					});

					$('.aside-prontuario-procedimentos .js-asideProcedimentos-adicionar').click(function(){
						let id_procedimento = $('.aside-prontuario-procedimentos .js-asideProcedimentos-id_procedimento').val();
						if(id_procedimento.length==0) {
							swal({title: "Erro!", text: 'Selecione o procedimento que deseja evoluir', html:true, type:"error", confirmButtonColor: "#424242"});
						} else {

							let obj = $(this);
							let objHTMLAntigo = obj.html();

							if(obj.attr('data-loading')==0) {

								obj.html(`<span class="iconify" data-icon="eos-icons:loading"></span>`);
								obj.attr('data-loading',1);

								$('.aside-prontuario-procedimentos .js-asideProcedimentos-id_procedimento option:selected').each(function(index,el) {

									let id_procedimento_aevoluir = $(el).val();
									let data = `ajax=asProcedimentosHistorico&id_procedimento_aevoluir=${id_procedimento_aevoluir}`;
									let obj = $(el);

									$.ajax({
										type:"POST",
										url:baseURLApiAsidePaciente,
										data:data,
										success:function(rtn) {
											if(rtn.success) { 
												let id_procedimento = $(el).attr('data-id_procedimento');
												let numero = $(el).attr('data-numero');
												let numeroTotal = $(el).attr('data-numeroTotal');
												let opcao = $(el).attr('data-opcao');
												let plano = $(el).attr('data-plano');
												let titulo = $(el).attr('data-titulo');
												let id_profissional = $(el).attr('data-id_profissional');
												let profissionalIniciais = $(el).attr('data-profissionalIniciais');
												let id_tratamento_procedimento = $(el).attr('data-id_tratamento_procedimento');
												let profissionalCor = $(el).attr('data-profissionalCor');
												let statusEvolucao = $(el).attr('data-statusEvolucao');
												let obs = ``;
												let historico=rtn.historico;
												
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
												let data = `${dia}/${mes}/${dt.getFullYear()} ${dt.getHours()}:${dt.getMinutes()}`;

												if(id_procedimento_aevoluir.length>0) {
													let item = { id_procedimento_aevoluir,
																	 id_procedimento, 
																	opcao, 
																	plano, 
																	titulo, 
																	profissionalCor, 
																	profissionalIniciais, 
																	statusEvolucao, 
																	autor, 
																	id_usuario, 
																	data, 
																	obs,
																	id_profissional,
																	id_tratamento_procedimento,
																	numero,
																	numeroTotal,
																	historico
																}
													item.avulso=0;
													procedimentos.push(item); 
													procedimentosListar();

									 				obj.prop('disabled',true);
													$('.aside-prontuario-procedimentos .js-asideProcedimentos-id_procedimento').val('').trigger('chosen:updated');
												}
											}
										}
									}).done(function(){
										$('.aside-prontuario-procedimentos .js-asideProcedimentos-adicionar').attr('data-loading',0);
										$('.aside-prontuario-procedimentos .js-asideProcedimentos-adicionar').html(objHTMLAntigo);
										
									});

									

								});
							}

							
						}
					});
					
					// adiciona novo historico
					$('.aside-prontuario-procedimentos-historico .js-asideProcedimentosHistoricos-adicionar').click(function(){

						let obs = $('.aside-prontuario-procedimentos-historico .js-asideProcedimentosHistoricos-obs').val();

						if(obs.length==0) {
							swal({title: "Erro!", text: 'Digite a observação que deseja adicionar ao histórico!', html:true, type:"error", confirmButtonColor: "#424242"});
						} else {

							let obj = $(this);
							let objHTMLAntigo = obj.html();

							if(obj.attr('data-loading')==0) {

								obj.html(`<span class="iconify" data-icon="eos-icons:loading"></span>`);
								obj.attr('data-loading',1);
								let data = `ajax=asProcedimentosHistoricoAdicionar&id_procedimento_aevoluir=${id_procedimento_aevoluir}&obs=${obs}`

								$.ajax({
									type:"POST",
									url:baseURLApiAsidePaciente,
									data:data,
									success:function(rtn) {
										if(rtn.success===true) {

											let index = $('.aside-prontuario-procedimentos-historico .js-index').val();

											procedimentos[index].historico=rtn.historico;

											$('.aside-prontuario-procedimentos-historico .js-procedimentos-historico').html('');
											rtn.historico.forEach(x=>{
												$('.aside-prontuario-procedimentos-historico .js-procedimentos-historico').append(`<div class="history-item">
																																		<h1>${x.usuario} em ${x.data}</h1>		
																																		${x.obs}
																																	</div>`);
											});

											$('.aside-prontuario-procedimentos-historico .js-asideProcedimentosHistoricos-obs').val('');
										}
									}
								}).done(function(){
									obj.attr('data-loading',0);
									obj.html(objHTMLAntigo);
								});
							}


						}
						
					});

					$('.aside-prontuario-procedimentos .aside-close-procedimentos').click(function(){
						let obj = $(this);
						if($('.aside-prontuario-procedimentos input[name=alteracao]').val()=="1") {
							swal({   
									title: "Atenção",   
									text: "Tem certeza que deseja fechar sem salvar as informações?",
									type: "warning",   
									showCancelButton: true,   
									confirmButtonColor: "#DD6B55",   
									confirmButtonText: "Sim!",   
									cancelButtonText: "Não",   
									closeOnConfirm: false,   
									closeOnCancel: false 
								}, function(isConfirm){   
									if (isConfirm) {   
										$(obj).parent().parent().removeClass("active");
										$(obj).parent().parent().parent().fadeOut(); 


										swal.close();
							  		 } else {   
							  		 	swal.close();   
							  		 } 
							  	});
			
						} else {
							$(obj).parent().parent().removeClass("active");
							$(obj).parent().parent().parent().fadeOut();
						}
					});

					// persiste a evolucao do procedimento
					$('.aside-prontuario-procedimentos .js-salvarEvolucaoProcedimento').click(function(){

						let obs = $('.js-asideProcedimentos-obs').val();
						let id_profissional = $('.js-asideProcedimentos-id_profissional').val();

						if(procedimentos.length==0 && obs.length==0) {
							swal({title: "Erro!", text: 'Escreva a evolução geral ou adicione pelo menos um procedimento aprovado!', html:true, type:"error", confirmButtonColor: "#424242"});
						} else if(id_profissional.length==0) {
							swal({title: "Erro!", text: 'Selecione o Profissional!', html:true, type:"error", confirmButtonColor: "#424242"});
						} else {

							let obj = $(this);
							let objHTMLAntigo = $(this).html();

							if(obj.attr('data-loading')==0) {

								obj.html(`<span class="iconify" data-icon="eos-icons:loading"></span>`);
								obj.attr('data-loading',1);

								let data = {
											'ajax':'asProcedimentosPersistir',
											'obs':obs,
											'procedimentos':procedimentos,
											'id_profissional':id_profissional,
											'id_paciente':id_paciente
										};


								$.ajax({
										type:'POST',
										data:data,
										url:baseURLApiAsidePaciente,
										success:function(rtn) {
											if(rtn.success) {
												if(id_agenda>0) {
													asideProximaConsultaLembrete();
													obj.html(objHTMLAntigo);
													obj.attr('data-loading',0);
												} else {	
													document.location.reload();
												}	
											} else if(rtn.error) {
												swal({title: "Erro!", text: rtn.error, type:"error", confirmButtonColor: "#424242"});
												obj.html(objHTMLAntigo);
												obj.attr('data-loading',0);
											} else {
												swal({title: "Erro!", text: "Algum erro ocorreu! Tente novamente.", type:"error", confirmButtonColor: "#424242"});
												obj.html(objHTMLAntigo);
												obj.attr('data-loading',0);
											}
											
										},
										error:function() {
											swal({title: "Erro!", text: "Algum erro ocorreu! Tente novamente.", type:"error", confirmButtonColor: "#424242"});
											obj.html(objHTMLAntigo);
											obj.attr('data-loading',0);
										} 
								}).done(function(){
									obj.html(objHTMLAntigo);
									obj.attr('data-loading',0);
								});
							} 

						}


					});
					
					// adiciona novo historico em um procedimento que foi evoluido (clica na listagem das evolucoes)
					$('.aside-prontuario-procedimentos-historico-visualizacao .js-hist-asideProcedimentosHistoricos-adicionar').click(function(){

						let obs = $('.aside-prontuario-procedimentos-historico-visualizacao .js-asideProcedimentosHistoricos-obs').val();
						let id_evolucao = $('.aside-prontuario-procedimentos-historico-visualizacao .js-hist-id_evolucao').val();


						if(obs.length==0) {
							swal({title: "Erro!", text: 'Digite a observação que deseja adicionar ao histórico!', html:true, type:"error", confirmButtonColor: "#424242"});
						} else {

							let obj = $(this);
							let objHTMLAntigo = obj.html();

							if(obj.attr('data-loading')==0) {

								obj.html(`<span class="iconify" data-icon="eos-icons:loading"></span>`);
								obj.attr('data-loading',1);
								let data = `ajax=asProcedimentosHistoricoAdicionar&id_procedimento_aevoluir=${id_procedimento_aevoluir}&obs=${obs}&id_evolucao=${id_evolucao}`;

								$.ajax({
									type:"POST",
									url:baseURLApiAsidePaciente,
									data:data,
									success:function(rtn) {
										if(rtn.success===true) {
											document.location.href=`pg_pacientes_prontuario.php?id_paciente=${id_paciente}&pagina=${pagina}&id_evolucao=${id_evolucao}`;
										} else if(rtn.error) {
											swal({title: "Erro!", text: rtn.error, type:"error", confirmButtonColor: "#424242"});
										} else {
											swal({title: "Erro!", text: "Algum erro ocorreu! Tente novamente.", type:"error", confirmButtonColor: "#424242"});
										}
									}
								}).done(function(){
									obj.attr('data-loading',0);
									obj.html(objHTMLAntigo);
								});
							}


						}
						
					});

					// quando altera o status do procedimento evoluido
					$('.aside-prontuario-procedimentos-historico-visualizacao .js-hist-status').change(function(){
						

						let id_evolucao = $('.aside-prontuario-procedimentos-historico-visualizacao .js-hist-id_evolucao').val();
						let data = `ajax=asProcedimentosStatusAlterar&id_procedimento_aevoluir=${id_procedimento_aevoluir}&id_paciente=${id_paciente}&id_evolucao=${id_evolucao}&status=${$(this).val()}`;

						$.ajax({
							type:"POST",
							data:data,
							url:baseURLApiAsidePaciente,
							success:function(rtn) {
								if(rtn.success===true) {
									document.location.href=`pg_pacientes_prontuario.php?id_paciente=${id_paciente}&pagina=${pagina}&id_evolucao=${id_evolucao}`;
								} else if(rtn.error) {
									swal({title: "Erro!", text: rtn.error, type:"error", confirmButtonColor: "#424242"});
								} else {
									swal({title: "Erro!", text: "Algum erro ocorreu! Tente novamente.", type:"error", confirmButtonColor: "#424242"});
								}
							}
						})
					});

				});
			</script>

			<section class="aside aside-prontuario-procedimentos" style="display: none;">
				
				<div class="aside__inner1">
					<header class="aside-header">
						<h1>Procedimentos </h1>
						<a href="javascript:;" class="aside-header__fechar aside-close-procedimentos"><i class="iconify" data-icon="fluent:dismiss-24-filled"></i></a>
					</header>

					<form method="post" class="aside-content form js-form-procedimentos">

						<section class="filter">
							<div class="filter-group"></div>
							<div class="filter-group">
								<div class="filter-form form">
									<?php /*<dl>
										<dd><a href="javascript:;" class="button"><i class="iconify" data-icon="fluent:delete-24-regular"></i></a></dd>
									</dl>*/?>
									<dl>
										<dd><button type="button" class="button button_main js-salvarEvolucaoProcedimento" data-loading="0"><i class="iconify" data-icon="fluent:checkmark-12-filled"></i> <span>Salvar</span></button></dd>
									</dl>
								</div>								
							</div>
						</section>
						<div class="box">

							<input type="hidden" name="alteracao" value="0" />

							
								<div class="colunas3">
									<dl>
										<dt>Data da Evolução</dt>
										<dd>
											<input type="tel" name="" class="datahora js-asideProcedimentos-data js-asideProcedimentos-inputs" value="" /></dd>
										</dd>
									</dl>
									<dl class="dl2">
										<dt>Profissional</dt>
										<dd>
											<select class="js-asideProcedimentos-id_profissional js-asideProcedimentos-inputs">
												<option value="">-</option>
												<?php
												foreach($_profissionais as $x) {
													if($x->check_agendamento==0 or $x->contratacaoAtiva==0) continue;
													echo '<option value="'.$x->id.'"'.($usr->id==$x->id?' selected':'').'>'.utf8_encode($x->nome).'</option>';
												}
												?>
											</select>
										</dd>
									</dl>
								</div>

								<dl>
									<dt>Evolução Geral</dt>
									<dd>
										<textarea class="js-asideProcedimentos-obs js-asideProcedimentos-inputs" style="height:180px;width:100%;"></textarea>
									</dd>
								</dl>

								<dl class="dl2">
									<dt>Procedimento</dt>
									<dd>
										<select class="js-asideProcedimentos-id_procedimento js-asideProcedimentos-inputs" multiple>
											<option value=""></option>
											<?php
											/*foreach($_procedimentosAprovadosASerEvoluido as $id_tratamento=>$regs) {
												echo '<optgroup label="'.utf8_encode($_tratamentos[$id_tratamento]->titulo).'">';
												foreach($regs as $v) {
													$disabled='';
													if(isset($procedimentosAEvoluirIds) and in_array($v->id,$procedimentosAEvoluirIds)) $disabled=" disabled";;
													if(isset($_procedimentos[$v->id_procedimento])) {
														$procedimento=$_procedimentos[$v->id_procedimento];
														$profissionalIniciais='';
														$profissionalCor='#ccc';
														if(isset($_profissionais[$v->id_profissional])) {
															$p=$_profissionais[$v->id_profissional];
															$profissionalIniciais=$p->calendario_iniciais;
															$profissionalCor=$p->calendario_cor;

														}
														$complemento='';
														if($v->numeroTotal>1) $complemento.=' - '.utf8_encode($v->numero."/".$v->numeroTotal);

														//	id_tratamento_procedimento => Procedimento de tratamento aprovado
														if(isset($_procedimentosDeTratamentosAprovados[$v->id_tratamento_procedimento])) {
															$procedimentoAprovado=$_procedimentosDeTratamentosAprovados[$v->id_tratamento_procedimento];
															if(!empty($procedimentoAprovado->opcao)) $complemento.=" - ".utf8_encode($procedimentoAprovado->opcao)
																;
															echo '<option value="'.$v->id.'" 
																			data-id_procedimento="'.$v->id_procedimento.'" 
																			data-numero="'.$v->numero.'" 
																			data-numeroTotal="'.$v->numeroTotal.'" 
																			data-opcao="'.strip_tags(utf8_encode($procedimentoAprovado->opcao)).'" 
																			data-plano="'.utf8_encode($procedimentoAprovado->plano).'" 
																			data-profissionalCor="'.$profissionalCor.'" 
																			data-id_profissional="'.$v->id_profissional.'" 
																			data-profissionalIniciais="'.$profissionalIniciais.'"  
																			data-statusEvolucao="'.$v->status_evolucao.'" 
																			data-titulo="'.utf8_encode($procedimento->titulo).'" 
																			data-id_tratamento_procedimento="'.$procedimentoAprovado->id.'"'.$disabled.'>'.utf8_encode($procedimento->titulo)." ".$complemento.'</option>';
														}
													}
												}
												echo '</optgroup>';
											}*/
											?>
										</select>

										<button type="button" class="button button_main js-asideProcedimentos-adicionar" data-loading="0"><span class="iconify" data-icon="akar-icons:plus"></span></button>

										<?php /*<button type="button" class="button js-asideProcedimentos-adicionarAvulso"><span class="iconify" data-icon="akar-icons:plus"></span> Avulso</button>*/?>
									</dd>
								</dl>

								<div class="list1">
									<table class="js-asideProcedimentos-tabela">
										<tbody>
										
										</tbody>						
									</table>
								</div>
						</div>
					
					</form>
				</div>
			</section>

			<section class="aside aside-prontuario-procedimentos-historico" style="display: none;">
				<div class="aside__inner1" style="width:700px">
					
					<header class="aside-header">
						<h1>Obs e Histórico</h1>
						<a href="javascript:;" class="aside-header__fechar aside-close"><i class="iconify" data-icon="fluent:dismiss-24-filled"></i></a>
					</header>

					<form method="post" class="aside-content form">
						<input type="hidden" class="js-index" />
						<section class="filter">
							<div class="filter-group"></div>					
						</section>

						<fieldset>
							<legend>Adicionar</legend>
							<dl>
								<dd><textarea class="js-asideProcedimentosHistoricos-obs" rows="4"></textarea></dd>
							</dl>
							<dl>
								<dd><button type="button" class="button button_main js-asideProcedimentosHistoricos-adicionar" data-loading="0"><i class="iconify" data-icon="fluent:add-circle-24-regular"></i><span>Adicionar</span></button>
							</dl>
						</fieldset>

						
						<div class="history js-procedimentos-historico">
							
							<div class="history2-item">
								<aside>
									<span style="background:var(--cor1);"><i class="iconify" data-icon="fluent:chat-24-regular"></i></span>
								</aside>
								<article>
									<div class="history2-main">
										<div>
											<h1>31/03 (quinta) • 10:00</h1>
											<div class="badge-prof">KC</div>					
										</div>
										<h1>Esse é um comentário publicado nessa página.</h1>
									</div>
								</article>
							</div>

							<div class="history2-item">
								<aside>
									<span style="background:var(--cinza4);"><i class="iconify" data-icon="fluent:timer-off-24-regular"></i></span>
								</aside>
								<article>
									<div class="history2-main">
										<div>
											<h1>31/03 (quinta) • 10:00</h1>
											<div class="badge-prof">KC</div>					
										</div>
										<h1>alterou o status para <strong>NÃO INICIADO</strong></h1>
									</div>
								</article>
							</div>

							<div class="history2-item">
								<aside>
									<span style="background:var(--laranja);"><i class="iconify" data-icon="fluent:timer-24-regular"></i></span>
								</aside>
								<article>
									<div class="history2-main">
										<div>
											<h1>31/03 (quinta) • 10:00</h1>
											<div class="badge-prof">KC</div>					
										</div>
										<h1>alterou o status para <strong>EM TRATAMENTO</strong></h1>
									</div>
								</article>
							</div>

							<div class="history2-item">
								<aside>
									<span style="background:var(--verde);"><i class="iconify" data-icon="fluent:checkbox-checked-24-regular"></i></span>
								</aside>
								<article>
									<div class="history2-main">
										<div>
											<h1>31/03 (quinta) • 10:00</h1>
											<div class="badge-prof">KC</div>					
										</div>
										<h1>alterou o status para <strong>FINALIZADO</strong></h1>
									</div>
								</article>
							</div>

							<div class="history2-item">
								<aside>
									<span style="background:var(--vermelho);"><i class="iconify" data-icon="fluent:dismiss-square-24-regular"></i></span>
								</aside>
								<article>
									<div class="history2-main">
										<div>
											<h1>31/03 (quinta) • 10:00</h1>
											<div class="badge-prof">KC</div>					
										</div>
										<h1>alterou o status para <strong>CANCELADO</strong></h1>
									</div>
								</article>
							</div>

							
						</div>

					</form>
				</div>
			</section>

			<section class="aside aside-prontuario-procedimentos-historico-visualizacao" style="display: none;">
				<div class="aside__inner1" style="width:700px">
					
					<header class="aside-header">
						<h1>Obs e Histórico</h1>
						<a href="javascript:;" class="aside-header__fechar aside-close"><i class="iconify" data-icon="fluent:dismiss-24-filled"></i></a>
					</header>

					<form method="post" class="aside-content form">
						<input type="hidden" class="js-hist-id_procedimento_aevoluir" />
						<input type="hidden" class="js-hist-id_evolucao" />
						<section class="filter">
							<div class="filter-group"></div>					
						</section>
						<fieldset>
							<legend>Procedimento Evoluído</legend>

							<?php /*<dl>
								<dt>Data da Evolução</dt>
								<dd><input type="text" class="js-hist-dataEvolucao" disabled /></dd>
							</dl>*/?>
							<dl>
								<dt>Procedimento</dt>
								<dd><input type="text" class="js-hist-procedimento" disabled /></dd>
							</dl>

							<dl>
								<dt>Status do Procedimento</dt>
								<dd>
									<select class="js-hist-status">
										<option value="iniciar">Não iniciado</option>
										<option value="iniciado">Em tratamento</option>
										<option value="finalizado">Finalizado</option>
										<option value="cancelado">Cancelado</option>
									</select>
								</dd>
							</dl>

							<dl>
								<dt>Observação</dt>
								<dd><textarea class="js-asideProcedimentosHistoricos-obs" rows="4"></textarea></dd>
							</dl>
							<dl>
								<dd><button type="button" class="button button_main js-hist-asideProcedimentosHistoricos-adicionar" data-loading="0"><i class="iconify" data-icon="fluent:add-circle-24-regular"></i><span>Adicionar Observação</span></button>
							</dl>
						</fieldset>

						
						<div class="history js-procedimentos-historico">
						

							
						</div>

					</form>
				</div>
			</section>


			<?php
		}
	
?>