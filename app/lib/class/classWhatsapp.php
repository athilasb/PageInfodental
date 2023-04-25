<?php
	class WhatsApp {
		
		private $prefixo = "", 
				$block = array(),//'62982414610'),
				$endpoint = "https://srv.infodental.dental:8443",
				$token = "b5b9f54a9b11125a63136f3712e853f1023836b3";

		function __construct($attr) {
			if(isset($attr['prefixo'])) $this->prefixo=$attr['prefixo'];
			if(isset($attr['usr'])) $this->usr=$attr['usr'];

			$sql=new Mysql(true);
			$_p=$this->prefixo;

			$tipos=array();
			$sql->consult($_p."whatsapp_mensagens_tipos","*","where lixo=0");
			while($x=mysqli_fetch_object($sql->mysqry)) $tipos[$x->id]=$x;

			$this->tipos=$tipos;

		}

		// cadastra na fila do rabbitmq
		function wtsRabbitmq($id_whatsapp,$complemento=false) {
			$sql=new Mysql(true);
			$_p=$this->prefixo;



			$sql->consult("infodentalADM.infod_contas","*","where instancia='".addslashes($_ENV['NAME'])."'");
			$infoConta=$sql->rows?mysqli_fetch_object($sql->mysqry):'';

			if(is_object($infoConta)) {
				$rabbitmqFila="infozap_".$infoConta->instancia;


				$whatsappMessage='';

				if(is_numeric($id_whatsapp)) {

					if($complemento===true) {
						$sql->consult($_p."whatsapp_mensagens_complemento","*","where id=".$id_whatsapp);
						if($sql->rows) $whatsappMessage=mysqli_fetch_object($sql->mysqry);
					} else {
						$sql->consult($_p."whatsapp_mensagens","*","where id=".$id_whatsapp);
						if($sql->rows) $whatsappMessage=mysqli_fetch_object($sql->mysqry);
					}
				}

				if(is_object($whatsappMessage)) {

					if(!empty($rabbitmqFila)) {

						$_rabbitMQServer='51.158.67.192';
						$_rabbitMQPort='5672';
						$_rabbitMQUsername='infozap';
						$_rabbitMQPassword='zapInf0@#';
						$_rabbitmqFila=$rabbitmqFila;

						//echo getcwd();die();
						
						$dir="../";
						if(getcwd()=="/root") $dir="../var/www/html/";
						else if(getcwd()=="/var/www/html/includes/api") $dir="../../";

						require_once $dir.'vendor/autoload.php';
						require_once $dir.'lib/class/classRabbitMQ.php';

						$rabbitmq = new RabbitMQ(array(
							'host' => $_rabbitMQServer,
							'port' => $_rabbitMQPort,
							'username' => $_rabbitMQUsername,
							'password' => $_rabbitMQPassword
						));

						$isConnected = $rabbitmq->createConnection();

						if ($isConnected) {
							$rabbitmq->setQueue($_rabbitmqFila);

							$message='';
							if($complemento===true) {

								if($whatsappMessage->tipo=="geolocalizacao") {
									/*
										{type: "sendLocationMessage",
										    data: {
										      number: "556292553015",
										      // quotedMessageId: "true_556282400606@c.us_3EB08E9B10835D387461",
										      location: {
										        lat: -22.95201,
										        lng: -43.2102601,
										        name: "Cristo Rendentor",
										        address:
										          "Parque Nacional da Tijuca - Alto da Boa Vista, Rio de Janeiro - RJ",
										        url: "https://santuariocristoredentor.com.br/",
										      },
										    },
										  };
									 */

									$message=json_encode(array('type'=>'sendLocationMessage',
																'data'=>array('number'=>$this->wtsNumero($whatsappMessage->numero),
																				'location'=>array('lat'=>$whatsappMessage->lat,
																									'lng'=>$whatsappMessage->lng,
																									'name'=>utf8_encode($whatsappMessage->name),
																									'address'=>utf8_encode($whatsappMessage->address))
																			)
															)
														);
								} 

							} else {

								if($whatsappMessage->id_tipo=="10") { 
									$arquivo = @file_get_contents($whatsappMessage->arquivo); 
									if(!$arquivo) {
										$this->erro='PDF não encontrado no servidor. Favor entrar em contato como nossa equipe de suporte!';
										$sql->update($_p."whatsapp_mensagens","enviado=0,
																				erro=1,
																				data_erro=now(),
																				erro_retorno='".utf8_decode((isset($this->erro)?$this->erro:'Algum erro ocorreu durante o cadastro da mensagem na fila'))."'",
																				"where id=$whatsappMessage->id");
										return false;
									}
									$arquivo64 = "data:application/pdf;base64,".base64_encode($arquivo); 

									//echo $whatsappMessage->arquivo;die();
									//$arquivo64 = "data:image/jpg;base64,".base64_encode(file_get_contents("https://testes.infodental.dental/img/ilustra-usuario.jpg"));
									//echo $arquivo64;die();

									$message=json_encode(array('type'=>'sendFileMessage',
																	   'data'=>array('number' =>  $this->wtsNumero($whatsappMessage->numero),
																   				     'base64'  => $arquivo64,
																		             'options' => array('type'  => 'document',
																						     	        'caption'  => "Teste",
																						     	        'filename' => utf8_encode($whatsappMessage->arquivo_titulo)))));

									
								} else {
									$message=json_encode(array('type'=>'sendTextMessage',
																   'data'=>array('number'=>$this->wtsNumero($whatsappMessage->numero),
																		         'text'=>($whatsappMessage->mensagem))));
								}
							}

							# cadastra na fila e atualiza tabela
								if(!empty($message) and $rabbitmq->sendMessageToQueueWts($message,$_rabbitmqFila)) {
									if($complemento===true) {
										$sql->update($_p."whatsapp_mensagens_complemento","enviado=1,data_enviado=now(),json_request='".addslashes($message)."'","where id=$whatsappMessage->id");
									} else {
										$sql->update($_p."whatsapp_mensagens","enviado=1,data_enviado=now()","where id=$whatsappMessage->id");
									}

									return true;
								} else {

									if($complemento===true) {
										$sql->update($_p."whatsapp_mensagens_complemento","enviado=0,
																							erro=1,
																							data_erro=now(),
																							erro_retorno='".(isset($rabbitmq->erro)?$rabbitmq->erro:'Algum erro ocorreu durante o cadastro da mensagem na fila')."'",
																							"where id=$whatsappMessage->id");

									} else {
										$sql->update($_p."whatsapp_mensagens","enviado=0,
																				erro=1,
																				data_erro=now(),
																				erro_retorno='".(isset($rabbitmq->erro)?$rabbitmq->erro:'Algum erro ocorreu durante o cadastro da mensagem na fila')."'",
																				"where id=$whatsappMessage->id");
									}
								}

								return false;
						} 

						$rabbitmq->closeConnection();
					} else {
						$this->erro="Fila do Rabbitmq não definida!"; 
						return false;
					}

				} else {
					$this->erro="Mensagem não encontrada!"; 
					return false;
				}
			} else {
				$this->erro='Conta não encontrada!';
				return false;
			}
		}

		// formata numero para o whatsapp (remove 9 de alguns ddds)
		function wtsNumero($numero) {

			
			$novoNumero='';

			/*$dddsComOitoDigitos=array(62,61,64,84);

			if(in_array(substr($numero,0,2),$dddsComOitoDigitos)) {
				$novoNumero=substr($numero,0,2).substr($numero,3,8);
			} else {
				$novoNumero=$numero;
			}*/

			$dddsComNoveDigitos=array(11,12,21,19,13,15,16,17,18,20,22,24,27,28);
			if(in_array(substr($numero,0,2),$dddsComNoveDigitos)) {
				$novoNumero=$numero;
			} else if(strlen($numero)==11){ 
				$novoNumero=substr($numero,0,2).substr($numero,3,8);
			} else {
				$novoNumero=$numero;
			}

			return "55$novoNumero";
		}

		// substitui os atalhos
		function mensagemAtalhos($attr) {

			$sql = new Mysql();
			$_p=$this->prefixo;

			$_dias=array('Domingo',
						'Segunda-Feira',
						'Terça-Feira',
						'Quarta-Feira',
						'Quinta-Feira',
						'Sexta-Feira',
						'Sábado');

			$paciente = (isset($attr['paciente']) and is_object($attr['paciente']))?$attr['paciente']:'';
			$agenda = (isset($attr['agenda']) and is_object($attr['agenda']))?$attr['agenda']:'';
			$ultimoAgendamento = (isset($attr['ultimoAgendamento']) and is_object($attr['ultimoAgendamento']))?$attr['ultimoAgendamento']:'';
			$cadeira = (isset($attr['cadeira']) and is_object($attr['cadeira']))?$attr['cadeira']:'';
			$profissionais = (isset($attr['profissionais']) and !empty($attr['profissionais']))?$attr['profissionais']:'';
			$agenda_profissionais = (isset($attr['agenda_profissionais']) and !empty($attr['agenda_profissionais']))?$attr['agenda_profissionais']:'';
			$msg = (isset($attr['msg']) and !empty($attr['msg']))?$attr['msg']:'';
			$evolucao = (isset($attr['evolucao']) and is_object($attr['evolucao']))?$attr['evolucao']:'';

			if(is_object($paciente)) {
				$msg = str_replace("[nome]",utf8_encode($paciente->nome), $msg);
			}
			if(is_object($agenda)) {

				$clinica='';
				$sql->consult($_p."clinica","clinica_nome,endereco","order by id desc limit 1");
				if($sql->rows) $clinica=mysqli_fetch_object($sql->mysqry);

				$dataFormatada=$_dias[date('w',strtotime($agenda->agenda_data))].", ";
				$dataFormatada.=date('d/m/Y',strtotime($agenda->agenda_data));

				$msg = str_replace("[agenda_data]",$dataFormatada, $msg);
				$msg = str_replace("[agenda_hora]",date('H:i',strtotime($agenda->agenda_data)), $msg);


				$dataFormatada=$_dias[date('w',strtotime($agenda->agenda_data_original))].", ";
				$dataFormatada.=date('d/m/Y',strtotime($agenda->agenda_data_original));

				$msg = str_replace("[agenda_antiga_data]",$dataFormatada, $msg);
				$msg = str_replace("[agenda_antiga_hora]",date('H:i',strtotime($agenda->agenda_data_original)), $msg);

				$msg = str_replace("[profissionais]",($profissionais), $msg);
				$msg = str_replace("[agenda_profissionais]",($agenda_profissionais), $msg);
				$msg = str_replace("[duracao]",($agenda->agenda_duracao)." minutos", $msg);

				if(is_object($clinica)) {
					$msg = str_replace("[clinica_nome]",(utf8_encode($clinica->clinica_nome)), $msg);
					$msg = str_replace("[clinica_endereco]",(utf8_encode($clinica->endereco)), $msg);
				}


				$dias='';
				if(is_object($ultimoAgendamento)) {
					$dias=strtotime(date('Y-m-d H:i:s'))-strtotime($ultimoAgendamento->agenda_data);
					$dias/=60*60*24;
					$dias=round($dias);

					if($dias>30) {
						$dias/=30;
						$dias=ceil($dias);
						$dias.=$dias>1?" meses":"mês";
					} else {
						$dias.=" dia(s)";
					}
					$msg = str_replace("[tempo_sem_atendimento]",$dias,$msg);
				} else {
					$msg = str_replace("[tempo_sem_atendimento]","...",$msg);
				}

			}
			if(is_object($cadeira)) {
				$msg = str_replace("[consultorio]",is_object($cadeira)?utf8_encode($cadeira->titulo):"Consultório", $msg);
			}

			if(is_object($evolucao)) {
				$msg = str_replace("[linkAnamnese]","https://".$_ENV['NAME'].".infodental.dental/anamnese/".md5($evolucao->id), $msg);
			}

			return $msg;
		}

		// cadastra na tabela ident_whatsapp_mensagens
		function adicionaNaFila($attr) {
			$_p=$this->prefixo;
			$sql=new Mysql();
			$sqlWts=new Mysql(true);

			$tipo='';
			if(isset($attr['id_tipo']) and is_numeric($attr['id_tipo']) and isset($this->tipos[$attr['id_tipo']])) {
				$tipo=$this->tipos[$attr['id_tipo']];
			}


			$cronjob = isset($attr['cronjob']) ? 1 : 0;

			if(is_object($tipo)) {

				if($tipo->pub==1) {

					# Capta dados (profissiona, paciente, agenda, cadeira)
						$profissional = '';
						if(isset($attr['id_profissional']) and is_numeric($attr['id_profissional'])) {
							$sql->consult($_p."colaboradores","id,nome,telefone1","where id=".$attr['id_profissional']." and lixo=0");
							if($sql->rows) {
								$profissional=mysqli_fetch_object($sql->mysqry);
							}
						}


						$paciente = '';
						if(isset($attr['id_paciente']) and is_numeric($attr['id_paciente'])) {
							$sql->consult($_p."pacientes","id,nome,telefone1,foto_wts","where id=".$attr['id_paciente']." and lixo=0");
							if($sql->rows) {
								$paciente=mysqli_fetch_object($sql->mysqry);
							}
						}

						$agenda = $cadeira = $profissionais = $agenda_profissionais = '';
						if(isset($attr['id_agenda']) and is_numeric($attr['id_agenda'])) {
							$whereAg="where id=".$attr['id_agenda']." and lixo=0";
							//echo $whereAg."<BR>";
							$sql->consult($_p."agenda","id,id_paciente,id_cadeira,agenda_data,agenda_data_original,agenda_duracao,profissionais",$whereAg);
							//echo $whereAg." => $sql->rows<BR>";
							if($sql->rows) {
								$agenda=mysqli_fetch_object($sql->mysqry);

								if($agenda->id_cadeira>0) {
									$sql->consult($_p."parametros_cadeiras","*","where id=$agenda->id_cadeira");
									if($sql->rows) {
										$cadeira=mysqli_fetch_object($sql->mysqry);
									}
								}

								if(!empty($agenda->profissionais)) {
									$aux=explode(",",$agenda->profissionais);
									foreach($aux as $idProf) {
										if(!empty($idProf) and is_numeric($idProf)) {
											$profissionaisIds[]=$idProf;
										}
									}

									if(count($profissionaisIds)>0) {
										$sql->consult($_p."colaboradores","id,nome","where id IN (".implode(",",$profissionaisIds).")");
										if($sql->rows) {
											while($x=mysqli_fetch_object($sql->mysqry)) {
												$profissionais.=utf8_encode($x->nome)." e ";
												$agenda_profissionais.=utf8_encode($x->nome)."; ";
											}

											$profissionais=substr($profissionais,0,strlen($profissionais)-3);
											$agenda_profissionais=substr($agenda_profissionais,0,strlen($agenda_profissionais)-2);
										}
									}
								}
							}
						} 

						$evolucao = '';
						if(isset($attr['id_evolucao']) and is_numeric($attr['id_evolucao'])) {
							// id_tipo=1: anamnese
							$sql->consult($_p."pacientes_evolucoes","*","where id=".$attr['id_evolucao']." and id_tipo=1");
							if($sql->rows) {
								$evolucao=mysqli_fetch_object($sql->mysqry);

								$sql->consult($_p."pacientes","id,nome,foto_wts,foto,telefone1","where id=$evolucao->id_paciente and lixo=0");
								if($sql->rows) {
									$paciente=mysqli_fetch_object($sql->mysqry);
								}
							}
						}


					# Envia mensagens (cadastra rabbitmq)
						// Lembrete de Agendamento 24-18h (id_tipo=1)
						// Lembrete de Agendamento 3h (id_tipo=2)
						// Cancelamento (id_tipo=3)
						// Alteração de horario da agenda (id_tipo=5)
						$this->erro='';
						if($tipo->id==1) {
							if(is_object($paciente)) {

								if(is_object($agenda)) {

									if($agenda->id_paciente == $paciente->id) {

										$msg = $tipo->texto;
										$numero = telefone($paciente->telefone1);

										$this->celular=$numero;


										if(!empty($numero) and !empty($msg)) {

											if(in_array($numero,$this->block)) {
												$this->erro="Numero bloqueado ($numero)";
												return false;
											}

											$attr=array('paciente'=>$paciente,
														'agenda'=>$agenda,
														'profissionais'=>$profissionais,
														'agenda_profissionais'=>$agenda_profissionais,
														'cadeira'=>$cadeira,
														'msg'=>$msg);

											$msg = $this->mensagemAtalhos($attr);
											//echo "<br />=> ".$msg."<BR>";die();

											// verifica se ja enviou
											$where="where id_agenda=$agenda->id and 
															id_paciente=$paciente->id and 
															id_tipo=$tipo->id  and 
															numero='".addslashes($numero)."' and 
															data > NOW() - INTERVAL 48 HOUR";

											$sql->consult($_p."whatsapp_mensagens","*",$where);

										
											if($sql->rows==0) {

												// verifica a conexao ativa
												$id_conexao=0;
												$sql->consult("infodentalADM.infod_contas_onlines","id","where instancia='".$_ENV['NAME']."' and lixo=0 order by id desc limit 1");
												if($sql->rows) {
													$conexao=mysqli_fetch_object($sql->mysqry);
													$id_conexao=$conexao->id;
												}

												$webhookExpiracao = date('Y-m-d H:i:s',strtotime(' + 3 hours'));

												$vSQL="data=now(),
														id_tipo=$tipo->id,
														id_paciente=$paciente->id,
														fila_agenda_data='$agenda->agenda_data',
														id_agenda=$agenda->id,
														numero='$numero',
														id_conexao='$id_conexao',
														mensagem='$msg',
														webhook_expiracao='".$webhookExpiracao."'";

												// verifica se possui alguma mensagem de confirmacao para o mesmo celular e que webhook/escuta nao foi expirado ou finalizado
												$sql->consult($_p."whatsapp_mensagens","*","where numero='$numero' and webhook_desativado=0 and webhook_expiracao>now()");
											

												// se possui confirmacao com escuta para o mesmo numero, entra para fila
												if($sql->rows) {
													$vSQL.=",fila_data=now(),
															webhook_desativado=1,
															fila_numero='$numero'";
													$sqlWts->add($_p."whatsapp_mensagens",$vSQL);
													$id_whatsapp=$sqlWts->ulid;
												} 
												// se nao possui, envia mensagem
												else {
													$sqlWts->add($_p."whatsapp_mensagens",$vSQL);
													$id_whatsapp=$sqlWts->ulid;
													$this->wtsRabbitmq($id_whatsapp);
												}

											} else {
												$wtsEnviada=mysqli_fetch_object($sql->mysqry);
												$id_whatsapp=$wtsEnviada->id;

												// se nao foi enviado)
												if($wtsEnviada->enviado==0) {

													// se nao tiver na fila de numero (quando possui 2 ou mais agendamentos para ser confirmado no mesmo numero)
													if(empty($wtsEnviada->fila_numero)) {
														$this->wtsRabbitmq($id_whatsapp);
													} else {
														$this->erro='Esta mensagem está na fila ('.$wtsEnviada->fila_numero.') desde '.$wtsEnviada->fila_data;
													}
												} else {
													$this->erro="Esta mensagem já foi enviada!";
												}
											}
										} else {
											$this->erro="Paciente #$paciente->id não possui número de whatsapp";
										}

									} else {
										$this->erro="Agendamento #$agenda->id não é do paciente #$paciente->id";
									}

								} else {
									$this->erro="Agendamento não encontrado!";
								}

							} else {
								$this->erro="Paciente não encontrado!";
							}
						}
						else if($tipo->id==2 or $tipo->id==3 or $tipo->id==5) {

							if(is_object($paciente)) {

								if(is_object($agenda)) {

									$ultimoAgendamento='';
									$sql->consult($_p."agenda","agenda_data,id,agenda_data_original","where id_paciente=$paciente->id and id_status=5 order by agenda_data desc limit 1");
									if($sql->rows) {
										$ultimoAgendamento=mysqli_fetch_object($sql->mysqry);
									}

									if($agenda->id_paciente == $paciente->id) {

										$msg = $tipo->texto;
										$numero = telefone($paciente->telefone1);

										if(!empty($numero) and !empty($msg)) {

											if(in_array($numero,$this->block)) {
												$this->erro="Numero bloqueado ($numero)";
												return false;
											}

											$attr=array('paciente'=>$paciente,
														'agenda'=>$agenda,
														'ultimoAgendamento'=>$ultimoAgendamento,
														'profissionais'=>$profissionais,
														'cadeira'=>$cadeira,
														'msg'=>$msg);

											$msg = $this->mensagemAtalhos($attr);
											//echo "<br />=> ".$msg."<BR>";die();

											// verifica se ja enviou
											$where="where id_agenda=$agenda->id and 
															id_paciente=$paciente->id and 
															id_tipo=$tipo->id  and 
															numero='".addslashes($numero)."' and 
															data > NOW() - INTERVAL 48 HOUR";

											$sql->consult($_p."whatsapp_mensagens","*",$where);
											//echo $sql->rows;
										
											if($sql->rows==0) {

												// verifica a conexao ativa
												$id_conexao=0;
												$sql->consult("infodentalADM.infod_contas_onlines","id","where instancia='".$_ENV['NAME']."' and lixo=0 order by id desc limit 1");
												if($sql->rows) {
													$conexao=mysqli_fetch_object($sql->mysqry);
													$id_conexao=$conexao->id;
												}

												$vSQL="data=now(),
														id_tipo=$tipo->id,
														id_paciente=$paciente->id,
														id_agenda=$agenda->id,
														numero='$numero',
														id_conexao='$id_conexao',
														mensagem='$msg'";


												$sqlWts->add($_p."whatsapp_mensagens",$vSQL);

												$id_whatsapp=$sqlWts->ulid;


												// Alteração de horario da agenda (id_tipo=5)
												if($tipo->id==5) {
													// atualiza data original, e agenda_alteracao_id_whatsapp
													$vsql="agenda_alteracao_id_whatsapp='".$id_whatsapp."',
															agenda_data_original='".$agenda->agenda_data."'";
													$sql->update($_p."agenda",$vsql,"where id=$agenda->id");
												}

												$this->wtsRabbitmq($id_whatsapp);

											} else {
												$wtsEnviada=mysqli_fetch_object($sql->mysqry);
												$id_whatsapp=$wtsEnviada->id;

												if($wtsEnviada->enviado==0) {
													$this->wtsRabbitmq($id_whatsapp);
												} else {
													$this->erro="Esta mensagem já foi enviada!";
												}
											}
										} else {
											$this->erro="Paciente #$paciente->id não possui número de whatsapp";
										}

									} else {
										$this->erro="Agendamento #$agenda->id não é do paciente #$paciente->id";
									}

								} else {
									$this->erro="Agendamento não encontrado!";
								}

							} else {
								$this->erro="Paciente não encontrado!";
							}
						} 

						// Confirmação de agendamento para dentistas (id_tipo=6)
						else if($tipo->id==6) {
							if(is_object($paciente)) {

								if(is_object($agenda)) {
									if(is_object($profissional)) {
										if($agenda->id_paciente == $paciente->id) {

											$msg = $tipo->texto;
											$numero = telefone($profissional->telefone1);


											if(!empty($numero) and !empty($msg)) {

												if(in_array($numero,$this->block)) {
													$this->erro="Numero bloqueado ($numero)";
													return false;
												}

												$attr=array('paciente'=>$paciente,
															'agenda'=>$agenda,
															'profissionais'=>$profissionais,
															'cadeira'=>$cadeira,
															'msg'=>$msg);

												$msg = $this->mensagemAtalhos($attr);
												
												// verifica se ja enviou
												$where="where id_agenda=$agenda->id and 
																id_paciente=$paciente->id and 
																id_tipo=$tipo->id  and 
																numero='".addslashes($numero)."' and 
																data > NOW() - INTERVAL 48 HOUR";

												$sql->consult($_p."whatsapp_mensagens","*",$where);

											
												if($sql->rows==0) {

													$vSQL="data=now(),
															id_tipo=$tipo->id,
															id_paciente=$paciente->id,
															id_agenda=$agenda->id,
															id_profissional=$profissional->id,
															numero='$numero',
															mensagem='$msg'";


													$sqlWts->add($_p."whatsapp_mensagens",$vSQL);

													$id_whatsapp=$sqlWts->ulid;

													// cadastra no rabbitmq
													$this->wtsRabbitmq($id_whatsapp);

												} else {

													$wtsEnviada=mysqli_fetch_object($sql->mysqry);
													$id_whatsapp=$wtsEnviada->id;

													if($wtsEnviada->enviado==0) {
														$this->wtsRabbitmq($id_whatsapp);
													} else {
														$this->erro="Esta mensagem já foi enviada!";
													}
												}
											} else {
												$this->erro="Paciente #$paciente->id não possui número de whatsapp";
											}

										} else {
											$this->erro="Agendamento #$agenda->id não é do paciente #$paciente->id";
										}
									} else {
										$this->erro="Profissional não encontrado!";
									}

								} else {
									$this->erro="Agendamento não encontrado!";
								}

							} else {
								$this->erro="Paciente não encontrado!";
							}
						}


						// Relacionamento Gestão do Tempo (id_tipo=4)
						else if($tipo->id==4) {
							if(is_object($paciente)) {

								$ultimoAgendamento='';
								$sql->consult($_p."agenda","agenda_data,agenda_data_original,id","where id_paciente=$paciente->id and id_status=5 order by agenda_data desc limit 1");
								if($sql->rows) {
									$ultimoAgendamento=mysqli_fetch_object($sql->mysqry);
								}

								$msg = $tipo->texto;
								$numero = telefone($paciente->telefone1);

								if(!empty($numero) and !empty($msg)) {

									if(in_array($numero,$this->block)) {
										$this->erro="Numero bloqueado ($numero)";
										return false;
									}

									// busca ultimo agendamento
									$agenda='';
									$sql->consult($_p."agenda","*","where id_paciente=$paciente->id and id_status IN (5) and lixo=0 order by agenda_data desc limit 1");
									if($sql->rows) {
										$agenda=mysqli_fetch_object($sql->mysqry);
									}

									if(is_object($agenda)) {
										$attr=array('paciente'=>$paciente,
													'agenda'=>$agenda,
													'ultimoAgendamento'=>$ultimoAgendamento,
													'profissionais'=>$profissionais,
													'cadeira'=>$cadeira,
													'msg'=>$msg);
										$msg = $this->mensagemAtalhos($attr);
										$this->msg=$msg;

										//$numero="62982400606";

										// verifica se ja enviou
										$where="where id_agenda=$agenda->id and 
														id_paciente=$paciente->id and 
														id_tipo=$tipo->id  and 
														numero='".addslashes($numero)."' and 
														data > NOW() - INTERVAL 4 HOUR and lixo=0";

										$sql->consult($_p."whatsapp_mensagens","*",$where);

									
										if($sql->rows==0) {
											$vSQL="data=now(),
													id_tipo=$tipo->id,
													id_paciente=$paciente->id,
													id_agenda=$agenda->id,
													numero='$numero',
													mensagem='$msg'";

											$sqlWts->add($_p."whatsapp_mensagens",$vSQL);
											$id_whatsapp=$sqlWts->ulid;

											// cadastra no rabbitmq
											$this->wtsRabbitmq($id_whatsapp);

										} else {
											$wtsEnviada=mysqli_fetch_object($sql->mysqry);
											$id_whatsapp=$wtsEnviada->id;

											if($wtsEnviada->enviado==0) {
												$this->wtsRabbitmq($id_whatsapp);
											} else {
												$this->erro="Esta mensagem já foi enviada!";
											}
										}

									} else {
										$this->erro="Esta mensagem já foi cadastrada nos últimos 60 minutos";
									}
								} else {
									$this->erro="Paciente #$paciente->id não possui número de whatsapp";
								}

								
							} else {
								$this->erro="Paciente não encontrado!";
							}
						} 

						// Envio de Formulario de Preenchimento da Anamnese
						else if($tipo->id==11) {

							if(is_object($evolucao)) {
								if(is_object($paciente)) {

									$msg = $tipo->texto;
									$numero = telefone($paciente->telefone1);

									if(!empty($numero) and !empty($msg)) {

										// verifica se numero esta na blacklist
										if(in_array($numero,$this->block)) {
											$this->erro="Numero bloqueado ($numero)";
											return false;
										}

										$attr=array('paciente'=>$paciente,
													'evolucao'=>$evolucao,
													'msg'=>$msg);
										$msg = $this->mensagemAtalhos($attr);	
										$this->msg=$msg;

										$this->celular=$numero;

										// verifica se ja enviou
										/*$where="where id_evolucao=$evolucao->id and 
														id_paciente=$paciente->id and 
														id_tipo=$tipo->id  and 
														numero='".addslashes($numero)."' and 
														data > NOW() - INTERVAL 4 HOUR and lixo=0";

										$sql->consult($_p."whatsapp_mensagens","*",$where);

									
										if($sql->rows==0) {*/
											$vSQL="data=now(),
													id_tipo=$tipo->id,
													id_paciente=$paciente->id,
													id_evolucao=$evolucao->id,
													numero='$numero',
													mensagem='$msg'";

											$sqlWts->add($_p."whatsapp_mensagens",$vSQL);
											$id_whatsapp=$sqlWts->ulid;

											// cadastra no rabbitmq
											$this->wtsRabbitmq($id_whatsapp);

										/*} else {
											$wtsEnviada=mysqli_fetch_object($sql->mysqry);
											$id_whatsapp=$wtsEnviada->id;

											if($wtsEnviada->enviado==0) {
												$this->wtsRabbitmq($id_whatsapp);
											} else {
												$this->erro="Esta mensagem já foi enviada!";
											}
										}*/

									} else {
										$this->erro="Paciente #$paciente->id não possui número de whatsapp";
									}

								} else {
									$this->erro="Paciente não encontrado";
								}


							} else {
								$this->erro="Evolução de Anamnese não encontrada";
							}


						}

						else {
							$this->erro="Nenhum tipo encontrado";
						}


					# Envia complemento (getProfile, sendLocation)
						if(empty($this->erro)) {
							
							
							// se for savar foto do whatsapp
							$atualizacao=$paciente->foto_wts;
							$dif = number_format((strtotime(date('Y-m-d H:i:s')) - strtotime($atualizacao)) / (60*60*24),0,"","");

							if($cronjob==1 and $tipo->getProfile==1 and $dif>30) {
					
								$vSQLWhatsappComplemento="data=now(),
															tipo='getprofile',
															enviado=0,
															id_whatsapp=$id_whatsapp,
															numero='".$numero."'";

								$sql->add($_p."whatsapp_mensagens_complemento",$vSQLWhatsappComplemento);
								$id_whatsapp_complemento=$sql->ulid;

							
								$attr=array('id_paciente'=>$paciente->id);
								if($this->getProfile($attr)) {
									$sql->update($_p."whatsapp_mensagens_complemento","enviado=1,data_enviado=now()","where id=$id_whatsapp_complemento");
								} else {
									$this->erro='getProfile não executado!';
									$sql->update($_p."whatsapp_mensagens_complemento","erro=1,data_erro=now(),erro_retorno='Whatsapp desconectado'","where id=$id_whatsapp_complemento");
									return false;
								}
								

							}

							// se for enviar geolocalizacao
							if($tipo->geolocalizacao==1) {

								$clinica = '';
								$sql->consult($_p."clinica","clinica_nome,lat,lng,endereco","");
								$clinica=mysqli_fetch_object($sql->mysqry);

								$vSQLWhatsappComplemento="data=now(),
												tipo='geolocalizacao',
												enviado=0,
												lat='$clinica->lat',
												lng='$clinica->lng',
												name='".addslashes($clinica->clinica_nome)."',
												address='".addslashes($clinica->endereco)."',
												id_whatsapp=$id_whatsapp,
												numero='".$numero."'";
								//echo $vSQLWhatsappComplemento;
								$sql->add($_p."whatsapp_mensagens_complemento",$vSQLWhatsappComplemento);
								$id_whatsapp_complemento=$sql->ulid;

								// segundo param como true (complemento=true)
								 $this->wtsRabbitmq($id_whatsapp_complemento,true);
							}

							return true;
						}

				} else {
					$this->erro="Tipo de mensagem desativada";
				}

			} else {
				$this->erro = 'Tipo de mensagem inválida!';
			}

			return false;
		}

		// capta foto
		function getProfile($attr) {
			$sql = new Mysql();
			$_p =  $this->prefixo;


			// consulta paciente
				$paciente = '';
				if(isset($attr['id_paciente']) and is_numeric($attr['id_paciente'])) {
					$sql->consult($_p."pacientes","id,nome,foto_wts,foto,telefone1","where id=".$attr['id_paciente']);
					if($sql->rows) {
						$paciente=mysqli_fetch_object($sql->mysqry);
					}
				}
			
			// verifica se possui conexao
				$conexao='';
				$where="where instancia='".$_ENV['NAME']."' and lixo=0 order by data desc limit 1";
				$sql->consult("infodentalADM.infod_contas_onlines","*",$where);
				$conexao=$sql->rows?mysqli_fetch_object($sql->mysqry):'';

			if(is_object($paciente)) {
				if(!empty($paciente->telefone1)) {
	
				
				if(empty($conexao)) $erro="Nenhum whatsapp está conectado a esta unidade";
				else {

					if($conexao->versao==2) {
						$url="https://srv.infodental.dental:8443/v2/profile?instance=".$conexao->wid."&contact=".$this->wtsNumero($paciente->telefone1);
					} else {
						$url="https://srv.infodental.dental:8443/profile?instance=".$conexao->wid."&contact=".$this->wtsNumero($paciente->telefone1);
					}	

					$curl = curl_init(); 

					curl_setopt_array($curl, [
					  CURLOPT_PORT => "8443",
					  CURLOPT_URL => $url,
					  CURLOPT_RETURNTRANSFER => true,
					  CURLOPT_ENCODING => "",
					  CURLOPT_MAXREDIRS => 10,
					  CURLOPT_TIMEOUT => 30,
					  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
					  CURLOPT_CUSTOMREQUEST => "GET",
					  CURLOPT_POSTFIELDS => "",
					  CURLOPT_HTTPHEADER => [
					    "token: b5b9f54a9b11125a63136f3712e853f1023836b3"
					  ],
					]);


					$response = curl_exec($curl);
					$err = curl_error($curl);
					$info = curl_getinfo($curl);

					curl_close($curl);

					if ($err) {
					  $erro="cURL Error #:" . $err;

					} else {
						if($info['http_code']==500) {
							 $erro="Whatsapp não liberado para captação de foto";
						} else {
							$response=json_decode($response);

							if(isset($response->pictureUrl) and !empty($response->pictureUrl)) {
								$_dir="arqs/";
								if(getcwd()=="/var/www/html") $_dir=$_dir;
								else $_dir.="../".$_dir;
								$img = $_dir."wtsTemp.jpg";
								$url=$response->pictureUrl;
								
								if(file_put_contents($img, file_get_contents($url))) {
									// upload da foto 
									$uploadFile=$img;
									$uploadType=filesize($img);
									$uploadPathFile=$this->infosWasabi['_wasabiPathRoot']."arqs/clientes/".$paciente->id.".jpg";
									$uploaded=$this->infosWasabi['wasabiS3']->putObject(S3::inputFile($img,false),$this->infosWasabi['_wasabiBucket'],$uploadPathFile,S3::ACL_PUBLIC_READ);
									
									if($uploaded) {	
										$sql->update($_p."pacientes","foto='jpg',foto_wts=now()","where id=$paciente->id");
									}
								}
							}
						}
					}
				}

				} else {
					$erro="Número não definido";

				}
			} else {
				$erro='Paciente não encontrado!';
			}


			if(empty($erro)) {
				return true;
			} else {
				$this->erro=$erro;
				return false;
			}
		}

		// pre-capta foto (requisita funcao getProfile)
		function atualizaFoto($id_paciente) {
			$sql = new Mysql();
			$_p =  $this->prefixo;

			$paciente='';
			if(isset($id_paciente) and is_numeric($id_paciente)) {
				$sql->consult($_p."pacientes","id,telefone1,foto_wts","where id=$id_paciente");
				if($sql->rows) {
					$paciente=mysqli_fetch_object($sql->mysqry);
				}
			}

			if(is_object($paciente)) {
				$attr=array('id_paciente'=>$paciente->id);


				// se for savar foto do whatsapp
				$atualizacao=$paciente->foto_wts;
				$dif = number_format((strtotime(date('Y-m-d H:i:s')) - strtotime($atualizacao)) / (60*60*24),0,"","");

				if($dif>30) {
					$vSQLWhatsappComplemento="data=now(),
												tipo='getprofile',
												enviado=0,
												id_whatsapp=0,
												numero='".telefone($paciente->telefone1)."'";

					$sql->add($_p."whatsapp_mensagens_complemento",$vSQLWhatsappComplemento);
					$id_whatsapp_complemento=$sql->ulid;
					if($this->getProfile($attr)) {
						//echo "foto ok";
						$sql->update($_p."whatsapp_mensagens_complemento","enviado=1,data_enviado=now()","where id=$id_whatsapp_complemento");
						return true;
					} else {
						$this->erro='getProfile não executado!';
						$sql->update($_p."whatsapp_mensagens_complemento","erro=1,data_erro=now(),erro_retorno='Whatsapp desconectado'","where id=$id_whatsapp_complemento");
						return false;
					}
				} else {
					return true;
				}
				
			} else {
				$this->erro="Paciente não encontrado!";
				return false;
			}
		}

		// envia arquivo
		function enviaArquivo($attr) {
			$_p=$this->prefixo;
			$sql=new Mysql(true);

			$numero=(isset($attr['numero']) and is_numeric($attr['numero']))?$attr['numero']:'';
			$arq=(isset($attr['arq']) and !empty($attr['arq']))?$attr['arq']:'';
			$id_paciente=(isset($attr['id_paciente']) and is_numeric($attr['id_paciente']))?$attr['id_paciente']:0;
			$documentName = (isset($attr['documentName']) and !empty($attr['documentName']))?$attr['documentName']:'';



			if(empty($numero)) $erro="Número destinatário não definido";
			else if(empty($arq)) $erro="Mensagem não definida!";
			else if($id_paciente==0) $erro='Paciente não encontrado!';
			else {

				$whatsappMessage='';
				$sql->consult($_p."whatsapp_mensagens","*","where data > NOW() - INTERVAL 5 MINUTE and numero='".$numero."' and id_tipo=10 and enviado=0");
				if($sql->rows) {
					$whatsappMessage=mysqli_fetch_object($sql->mysqry);
				}	



				// se enviou essa mensagem
				if(is_object($whatsappMessage)) {

				} else {
					$vSQL="data=now(),
							enviado=0,
							id_paciente=$id_paciente,
							id_tipo=10,
							numero='".$numero."',
							arquivo='".$arq."',
							arquivo_titulo='".addslashes(($documentName))."'";

					$sql->add($_p."whatsapp_mensagens",$vSQL);
					$id_whatsapp_fila=$sql->ulid;


					// cadastra no rabbitmq
					return $this->wtsRabbitmq($id_whatsapp_fila);
				}

			}
		}

		// 2023-03-20: descontinuada
		function dispara() {
			return true;
			/*$_p=$this->prefixo;
			$sql=new Mysql(true);

			$enviarMsgs=array();
			$pacienteIds=array(-1);
			$agendasIds=array(-1);
			$profissionaisIds=array(-1);

			// consulta se esta disparando
			$sql->consult($_p."whatsapp_disparos","*","where ativo=1 and data > NOW() - INTERVAL 15 MINUTE");
			if($sql->rows) {
				$this->erro="Já existe disparo ativo";
				//return false;
			}

			// consulta lista de disparos
			$sql->consult($_p."whatsapp_mensagens","*","where enviado=0 and erro=0 and data > NOW() - INTERVAL 30 MINUTE and lixo=0 order by data asc");
			//echo "where enviado=0 and erro=0 and data > NOW() - INTERVAL 30 MINUTE and lixo=0 order by data asc -> ".$sql->rows;die();
		
			if($sql->rows) {
				while($x=mysqli_fetch_object($sql->mysqry)) {
					if(empty($x->mensagem)) continue;
					$enviarMsgs[]=$x;
					if($x->id_paciente>0)  $pacienteIds[$x->id_paciente]=$x->id_paciente;
					if($x->id_agenda>0)  $agendasIds[$x->id_agenda]=$x->id_agenda;
					if($x->id_profissional>0)  $profissionaisIds[$x->id_profissional]=$x->id_profissional;
				}
			}
			if(count($enviarMsgs)>0) {

				$_agendas=array();
				$sql->consult($_p."agenda","id,id_paciente,profissionais","where id IN (".implode(",",$agendasIds).") and lixo=0");
				while($x=mysqli_fetch_object($sql->mysqry)) {
					$_agendas[$x->id]=$x;
					$pacienteIds[$x->id_paciente]=$x->id_paciente;
					if(!empty($x->profissionais)) {
						$aux=explode(",",$x->profissionais);
						foreach($aux as $idProfissional) {
							if(!empty($idProfissional) and is_numeric($idProfissional)) {
								$profissionaisIds[$idProfissional]=$idProfissional;
							}
						}
					}
				}

				$_pacientes=array();
				$sql->consult($_p."pacientes","id,nome","where id IN (".implode(",",$pacienteIds).") and lixo=0");
				while($x=mysqli_fetch_object($sql->mysqry)) {
					$_pacientes[$x->id]=$x;
				}

				$_profissionais=array();
				$sql->consult($_p."colaboradores","*","where id IN (".implode(",",$profissionaisIds).") and lixo=0");
				while($x=mysqli_fetch_object($sql->mysqry)) {
					$_profissionais[$x->id]=$x;
				}

				$_tipos=$this->tipos;

				// verifica se possui conexao
				$where="where instancia='".$_ENV['NAME']."' and lixo=0 order by data desc limit 1";
				$sql->consult("infodentalADM.infod_contas_onlines","*",$where);

				$conexao=$sql->rows?mysqli_fetch_object($sql->mysqry):'';
				
				$clinica='';
				$sql->consult($_p."clinica","clinica_nome,endereco,lat,lng","order by id desc limit 1");
				if($sql->rows) $clinica=mysqli_fetch_object($sql->mysqry);

				$sql->add($_p."whatsapp_disparos","data=now(),ativo=1");
				$id_disparo=0;//$sql->ulid;
				foreach($enviarMsgs as $v) {


					if(empty($conexao)) {
						//$this->erro="Nenhuma whatsapp está conectado no momento!";
						//return false;
						$vsql="data_erro=now(),erro=1,erro_retorno='whatsapp desconectado'";
						$vwhere="where id=$v->id";
						$sql->update($_p."whatsapp_mensagens",$vsql,$vwhere);
						continue;
					} else {
							

						$tipo=isset($_tipos[$v->id_tipo])?$_tipos[$v->id_tipo]:'';
					
						if(is_object($tipo)) {


							$paciente = isset($_pacientes[$v->id_paciente])?$_pacientes[$v->id_paciente]:'';
							$agenda = isset($_agendas[$v->id_agenda])?$_agendas[$v->id_agenda]:'';
							$profissional = isset($_profissionais[$v->id_profissional])?$_profissionais[$v->id_profissional]:'';
							if($tipo->id==1 or $tipo->id==2 or $tipo->id==3 or $tipo->id==4 or $tipo->id==5 or $tipo->id==6) {

								if(is_object($paciente) and is_object($agenda)) {

									$attr=array('numero'=>$v->numero,
												'mensagem'=>$v->mensagem,
												'id_tipo'=>$tipo->id,
												'id_conexao'=>$conexao->id);
									if($this->enviaMensagem($attr)) {
										$vsql="data_enviado=now(),enviado=1,retorno_json='".addslashes($this->response)."',id_conexao=$conexao->id";
										$vwhere="where id=$v->id";
										$sql->update($_p."whatsapp_mensagens",$vsql,$vwhere);

										if($tipo->getProfile==1) {
											$attr=array('numero'=>$v->numero,'instance'=>$conexao->wid);
											if($this->getProfile($attr)) {
												$response=json_decode($this->response);
												if(isset($response->pictureUrl) and !empty($response->pictureUrl)) {
													$_dir="arqs/temp/";
													$img = "../../retaguarda/".$_dir."wtsTemp.jpg";
													$url=$response->pictureUrl;
													echo $url;
													/*if(file_put_contents($img, file_get_contents($url))) {
														// upload da foto 
														$uploadFile=$img;
														$uploadType=filesize($img);
														$uploadPathFile=$this->infosWasabi['_wasabiPathRoot']."arqs/clientes/".$cliente->id.".jpg";
														$uploaded=$this->infosWasabi['wasabiS3']->putObject(S3::inputFile($uploadFile,false),$this->infosWasabi['_wasabiBucket'],$uploadPathFile,S3::ACL_PUBLIC_READ);
														
														if($uploaded) {	
															$sql->update($_p."clientes","foto='jpg',foto_vn='".$_ENV['NAME']."'","where id=$cliente->id");
														}
													}//
												}
											}
										}	

										// se tiver habilitado para enviar geolozalizacao
										if($tipo->geolocalizacao==1 and !empty($clinica->lat) and !empty($clinica->lng)) {
										
											$attr=array('numero'=>$v->numero,
															'lat'=>$clinica->lat,
															'lng'=>$clinica->lng,
															'id_conexao'=>$conexao->id,
															'name'=>($clinica->clinica_nome),
															'descricao'=>($clinica->clinica_nome.(!empty($clinica->endereco)?" ".$clinica->endereco:"")));
										
											if($this->enviaLocalizacao($attr)) {
												echo "localizacao enviada com sucesso!";
											} else {
												echo "erro ao enviar localizacao: $this->erro";
											}


										}
											
											
										
										sleep(1);
									}  else {
										$vsql="data_erro=now(),erro=1,erro_retorno='".addslashes(isset($this->erro) ? $this->erro : 'sem erro')."',id_conexao=$conexao->id";
										$vwhere="where id=$v->id";
										$sql->update($_p."whatsapp_mensagens",$vsql,$vwhere);
									}

								}

							}
							
							
						}
					}
				}

				$sql->update($_p."whatsapp_disparos","ativo=0","where ativo=1");

				return true;

			} else {
				$this->erro="Nenhum mensagem para ser enviada!";
				return false;
			}*/
		}

		// 2023-03-20: descontinuada
		function enviaMensagem($attr,$quotedMessageId="") {
			return true;
			/*$_p=$this->prefixo;
			$sql=new Mysql(true);

			$numero=(isset($attr['numero']) and is_numeric($attr['numero']))?$attr['numero']:'';
			$mensagem=(isset($attr['mensagem']) and !empty($attr['mensagem']))?$attr['mensagem']:'';
			$offline=(isset($attr['offline']) and $attr['offline']==true)?true:false;

	
			$conexao='';
			if(isset($attr['id_conexao']) and is_numeric($attr['id_conexao'])) {
				$sql->consult("infodentalADM.infod_contas_onlines","*","where id=".$attr['id_conexao']);
				if($sql->rows) $conexao=mysqli_fetch_object($sql->mysqry);
			}

			if(empty($conexao)) $erro="Nenhum whatsapp está conectado a esta unidade";
			else if(empty($numero)) $erro="Número destinatário não definido";
			else if(empty($mensagem)) $erro="Mensagem não definida!";
			else {


				$postfields=array("number"=>$this->wtsNumero($numero),
									"quotedMessageId"=>$quotedMessageId,
									"instance"=>$conexao->wid,
									"text"=>$mensagem,);

				/*
				{
					"instance": "556282400606",
					"number": "556282400606",
					"text": "Teste",
					"title":"Titulo",
					"footer":"Footer",
					"quotedMessageId": "",
					"buttons": [
						{
							"id": "1",
							"text": "Sim"
						},
						{
							"id": "0",
							"text":  "Não"
						}
					]
				}
				*


				if($conexao->versao==2) {
					$url=$this->endpoint."/v2/message/text";

					if(isset($attr['id_tipo']) and $attr['id_tipo']==1) {
						//$postfields['buttons'][]=array('id'=>"nao",'text'=>'Não');
						//$postfields['buttons'][]=array('id'=>"sim",'text'=>'Sim');
					}
				} else {
					$url=$this->endpoint."/message/text";
				}





				if($offline===true) $postfields['offline']=true;
				

				//echo json_encode($postfields);

				/*$sql->add($_p."whatsapp_log","data=now(),
												endpoint='".$this->endpoint."/send/text',
												params='".addslashes(json_encode($postfields))."',
												id_unidade=$unidade->id");
				$id_log=$sql->ulid;*


				$curl = curl_init();


				curl_setopt_array($curl, [
				  CURLOPT_PORT => "8443",
				  CURLOPT_URL => $url,
				  CURLOPT_RETURNTRANSFER => true,
				  CURLOPT_ENCODING => "",
				  CURLOPT_MAXREDIRS => 10,
				  CURLOPT_TIMEOUT => 30,
				  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				  CURLOPT_CUSTOMREQUEST => "POST",
				  CURLOPT_POSTFIELDS => json_encode($postfields),
				  CURLOPT_HTTPHEADER => [
				    "Content-Type: application/json",
				    "token: ".$this->token
				  ],
				]);

				$response = curl_exec($curl);

				$err = curl_error($curl);

				curl_close($curl);
				$this->response=$response;
				

				if($err) {
				  $erro="cURL Error #:" . $err;
				} else {
				 
				}
			}


			if(empty($erro)) {
				return true;
			} else {
				$this->erro=$erro;
				return false;
			}*/
		}

		// 2023-03-20: descontinuada
		function enviaLocalizacao($attr) {
			return true;

			/*$_p=$this->prefixo;
			$sql=new Mysql(true);

			$numero=(isset($attr['numero']) and is_numeric($attr['numero']))?$attr['numero']:'';
			$lat=(isset($attr['lat']) and !empty($attr['lat']))?$attr['lat']:'';
			$lng=(isset($attr['lng']) and !empty($attr['lng']))?$attr['lng']:'';
			$descricao=(isset($attr['descricao']) and !empty($attr['descricao']))?$attr['descricao']:'';
			$name=(isset($attr['name']) and !empty($attr['name']))?$attr['name']:'';

			
			$conexao='';
			if(isset($attr['id_conexao']) and is_numeric($attr['id_conexao'])) {
				$sql->consult("infodentalADM.infod_contas_onlines","*","where id=".$attr['id_conexao']);
				if($sql->rows) $conexao=mysqli_fetch_object($sql->mysqry);
			}

			if(empty($conexao)) $erro="Nenhum whatsapp está conectado a esta unidade";
			else if(empty($numero)) $erro="Número destinatário não definido";
			else if(empty($lat) or empty($lng)) $erro="Coordenadas não definida!";
			else {
				if($conexao->versao==2) {
					$postfields=array('number'=>$this->wtsNumero($numero),
										'instance'=>$conexao->wid,
										'location'=>array('lat'=>$lat,
															'lng'=>$lng,
															'name'=>$name,
															'address'=>$descricao));
					
					$curl = curl_init();

					curl_setopt_array($curl, [
					  CURLOPT_PORT => "8443",
					  //CURLOPT_URL => $this->endpoint."/message/location",
					  CURLOPT_URL => $this->endpoint."/v2/message/location",
					  CURLOPT_RETURNTRANSFER => true,
					  CURLOPT_ENCODING => "",
					  CURLOPT_MAXREDIRS => 10,
					  CURLOPT_TIMEOUT => 30,
					  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
					  CURLOPT_CUSTOMREQUEST => "POST",
					  CURLOPT_POSTFIELDS => json_encode($postfields),
					  CURLOPT_HTTPHEADER => [
					    "Content-Type: application/json",
					    "token: ".$this->token
					  ],
					]);

					$response = curl_exec($curl);

					$err = curl_error($curl);

					curl_close($curl);
					$this->response=$response;

					/*$textMessage="📍 Nossa Localização:\n\n*GOOGLE MAPS*\nhttps://www.google.com/maps/search/".$lat.",".$lng."\n\n*WAZE*\nhttps://www.waze.com/pt-BR/live-map/directions?locale=pt_BR&utm_source=waze_app&to=ll.".$lat."%2C".$lng;

					$attr=array('numero'=>$numero,
								'mensagem'=>$textMessage,
								'id_conexao'=>$conexao->id);

					if($this->enviaMensagem($attr)) {
						echo "localizacao enviada com sucesso!";
					}*

				} else {
				
					$postfields=array('number'=>$this->wtsNumero($numero),
										'instance'=>$conexao->wid,
										'location'=>array('lat'=>$lat,
															'lng'=>$lng,
															'name'=>$name,
															'description'=>$descricao));

					$textMessage="📍 Nossa Localização:\n\n*GOOGLE MAPS*\nhttps://www.google.com/maps/search/".$lat.",".$lng."\n\n*WAZE*\nhttps://www.waze.com/pt-BR/live-map/directions?locale=pt_BR&utm_source=waze_app&to=ll.".$lat."%2C".$lng;

					$postfields=array('number'=>$this->wtsNumero($numero),
										'instance'=>$conexao->wid,
										'text'=>$textMessage);
					
					$curl = curl_init();

					curl_setopt_array($curl, [
					  CURLOPT_PORT => "8443",
					  //CURLOPT_URL => $this->endpoint."/message/location",
					  CURLOPT_URL => $this->endpoint."/message/text",
					  CURLOPT_RETURNTRANSFER => true,
					  CURLOPT_ENCODING => "",
					  CURLOPT_MAXREDIRS => 10,
					  CURLOPT_TIMEOUT => 30,
					  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
					  CURLOPT_CUSTOMREQUEST => "POST",
					  CURLOPT_POSTFIELDS => json_encode($postfields),
					  CURLOPT_HTTPHEADER => [
					    "Content-Type: application/json",
					    "token: ".$this->token
					  ],
					]);

					$response = curl_exec($curl);

					$err = curl_error($curl);

					curl_close($curl);
					$this->response=$response;
				}

				if($err) {
				  $erro="cURL Error #:" . $err;
				} else {
				 
				}
			}


			if(empty($erro)) {
				return true;
			} else {
				$this->erro=$erro;
				return false;
			}*/
		}

	}
?>