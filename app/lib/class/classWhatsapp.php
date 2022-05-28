<?php

	class WhatsApp {
		
		private $prefixo = "", 
				$block = array('62982414610'),
				$endpoint = "https://srv.infodental.dental:8443",
				$token = "b5b9f54a9b11125a63136f3712e853f1023836b3";

		function __construct($attr) {
			if(isset($attr['prefixo'])) $this->prefixo=$attr['prefixo'];
			if(isset($attr['usr'])) $this->usr=$attr['usr'];

			$sql=new Mysql(true);
			$_p=$this->prefixo;

			$tipos=array();
			$sql->consult($_p."whatsapp_mensagens_tipos","*","");
			while($x=mysqli_fetch_object($sql->mysqry)) $tipos[$x->id]=$x;

			$this->tipos=$tipos;

		}

		function wtsNumero($numero) {

			
			$novoNumero='';

			$dddsComOitoDigitos=array(62,61,64,84);

			if(in_array(substr($numero,0,2),$dddsComOitoDigitos)) {
				$novoNumero=substr($numero,0,2).substr($numero,3,8);
			} else {
				$novoNumero=$numero;
			}

			return "55$novoNumero";
		}

		function mensagemAtalhos($attr) {

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
			$msg = (isset($attr['msg']) and !empty($attr['msg']))?$attr['msg']:'';

			if(is_object($paciente)) {
				$msg = str_replace("[nome]",utf8_encode($paciente->nome), $msg);
			}
			if(is_object($agenda)) {

				$dataFormatada=$_dias[date('w',strtotime($agenda->agenda_data))].", ";
				$dataFormatada.=date('d/m/Y',strtotime($agenda->agenda_data));

				$msg = str_replace("[agenda_data]",$dataFormatada, $msg);
				$msg = str_replace("[agenda_hora]",date('H:i',strtotime($agenda->agenda_data)), $msg);
				$msg = str_replace("[profissionais]",($profissionais), $msg);
				$msg = str_replace("[duracao]",($agenda->agenda_duracao)." minutos", $msg);


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

			return $msg;

		}
		function adicionaNaFila($attr) {
			$_p=$this->prefixo;
			$sql=new Mysql();
			$sqlWts=new Mysql(true);

			$tipo='';
			if(isset($attr['id_tipo']) and is_numeric($attr['id_tipo']) and isset($this->tipos[$attr['id_tipo']])) {
				$tipo=$this->tipos[$attr['id_tipo']];
			}

			if(is_object($tipo)) {

				if($tipo->pub==1) {


					$paciente = '';
					if(isset($attr['id_paciente']) and is_numeric($attr['id_paciente'])) {
						$sql->consult($_p."pacientes","id,nome,telefone1","where id=".$attr['id_paciente']." and lixo=0");
						if($sql->rows) {
							$paciente=mysqli_fetch_object($sql->mysqry);
						}
					}

					$agenda = $cadeira = $profissionais = '';
					if(isset($attr['id_agenda']) and is_numeric($attr['id_agenda'])) {
						$whereAg="where id=".$attr['id_agenda']." and lixo=0";
						//echo $whereAg."<BR>";
						$sql->consult($_p."agenda","id,id_paciente,id_cadeira,agenda_data,agenda_duracao,profissionais",$whereAg);
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
										}

										$profissionais=substr($profissionais,0,strlen($profissionais)-3);
									}
								}
							}
						}
					} 


					// Lembrete de Agendamento 24-18h (id_tipo=1)
					// Lembrete de Agendamento 3h (id_tipo=2)
					// Cancelamento (id_tipo=3)
					$this->erro='';
					if($tipo->id==1 or $tipo->id==2 or $tipo->id==3) {

						if(is_object($paciente)) {

							if(is_object($agenda)) {

								$ultimoAgendamento='';
								$sql->consult($_p."agenda","agenda_data,id","where id_paciente=$paciente->id and id_status=5 order by agenda_data desc limit 1");
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
										//echo "<br />=> ".$msg."<BR>";

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
													numero='$numero',
													mensagem='$msg'";


											$sqlWts->add($_p."whatsapp_mensagens",$vSQL);

											return true;

										} else {
											$this->erro="Esta mensagem já foi cadastrada nos últimos 60 minutos";
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
					} else if($tipo->id==4) {
						if(is_object($paciente)) {

							$ultimoAgendamento='';
							$sql->consult($_p."agenda","agenda_data,id","where id_paciente=$paciente->id and id_status=5 order by agenda_data desc limit 1");
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

										return true;
									} else {
										$this->erro="Este paciente já foi notificado nas últimas 48 horas";
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
					} else {
						$this->erro="Nenhum tipo encontrado";
					}

				} else {
					$this->erro="Tipo de mensagem desativada";
				}

			} else {
				$this->erro = 'Tipo de mensagem inválida!';
			}

			return false;


		}

		
		function dispara() {
			
			$_p=$this->prefixo;
			$sql=new Mysql(true);

			$enviarMsgs=array();
			$pacienteIds=array(-1);
			$agendasIds=array(-1);
			$profissionaisIds=array(-1);

			// consulta se esta disparando
			$sql->consult($_p."whatsapp_disparos","*","where ativo=1 and data > NOW() - INTERVAL 15 MINUTE");
			if($sql->rows) {
				$this->erro="Já existe disparo ativo";
				return false;
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
							if($tipo->id==1 or $tipo->id==2 or $tipo->id==3 or $tipo->id==4) {

								if(is_object($paciente) and is_object($agenda)) {

									$attr=array('numero'=>$v->numero,
												'mensagem'=>$v->mensagem,
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
													}*/
												}
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
			}
		}

		function enviaMensagem($attr,$quotedMessageId="") {
			$_p=$this->prefixo;
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
									"text"=>$mensagem,
									"instance"=>$conexao->wid);

				if($offline===true) $postfields['offline']=true;

				//var_dump($postfields);

				/*$sql->add($_p."whatsapp_log","data=now(),
												endpoint='".$this->endpoint."/send/text',
												params='".addslashes(json_encode($postfields))."',
												id_unidade=$unidade->id");
				$id_log=$sql->ulid;*/


				$curl = curl_init();


				curl_setopt_array($curl, [
				  CURLOPT_PORT => "8443",
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
			}

		}

		function enviaLocalizacao($attr) {
			$_p=$this->prefixo;
			$sql=new Mysql(true);

			$numero=(isset($attr['numero']) and is_numeric($attr['numero']))?$attr['numero']:'';
			$mensagem=(isset($attr['mensagem']) and !empty($attr['mensagem']))?$attr['mensagem']:'';
			$lat=(isset($attr['lat']) and !empty($attr['lat']))?$attr['lat']:'';
			$lng=(isset($attr['lng']) and !empty($attr['lng']))?$attr['lng']:'';
			$name=(isset($attr['name']) and !empty($attr['name']))?$attr['name']:'';

			$unidade='';
			if(isset($attr['id_unidade']) and is_numeric($attr['id_unidade'])) {
				$sql->consult($_p."unidades","*","where id=".$attr['id_unidade']);
				if($sql->rows) $unidade=mysqli_fetch_object($sql->mysqry);
			}

			$conexao='';
			if(is_object($unidade) and isset($attr['id_conexao']) and is_numeric($attr['id_conexao'])) {
				$sql->consult("vucaADM.vuca_contas_vucazaps_unidades","*","where id=".$attr['id_conexao']." and id_unidade=$unidade->id");
				if($sql->rows) $conexao=mysqli_fetch_object($sql->mysqry);
			}

			if(empty($unidade)) $erro='Unidade não encontrada';
			else if(empty($conexao)) $erro="Nenhum whatsapp está conectado a esta unidade";
			else if(empty($numero)) $erro="Número destinatário não definido";
			//	else if(empty($mensagem)) $erro="Mensagem não definida!";
			else if(empty($lat) or empty($lng)) $erro="Coordenadas não definida!";
			else {

				$postfields=array('number'=>$this->wtsNumero($numero),
									'instance'=>$conexao->wid,
									'location'=>array('lat'=>$lat,
														'lng'=>$lng,
														'name'=>$name,
														'description'=>$mensagem));
				$sql->add($_p."whatsapp_log","data=now(),
												endpoint='".$this->endpoint."/send/location',
												params='".addslashes(json_encode($postfields))."',
												id_unidade=$unidade->id");
				$id_log=$sql->ulid;
				
				$curl = curl_init();

				curl_setopt_array($curl, [
				  CURLOPT_PORT => "8443",
				  CURLOPT_URL => $this->endpoint."/send/location",
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

				if($err) {
				  $this->erro="cURL Error #:" . $err;
				  $sql->update($_p."whatsapp_log","response='Erro: ".addslashes($err)."'","where id=$id_log");
				  return false;
				} else {
				  $this->response=$response;
				  $sql->update($_p."whatsapp_log","response='".addslashes($this->response)."'","where id=$id_log");
				}
			}

			if(empty($erro)) {
				return true;
			} else {
				$this->erro=$erro;
				return false;
			}
		}

		function getProfile($attr) {

			$numero=(isset($attr['numero']) and is_numeric($attr['numero']))?$this->wtsNumero($attr['numero']):'';


			if(!empty($numero)) {
				$getUrl=$this->endpoint."/get/profile?instance=".$attr['instance']."&contact=".$numero;
				//echo $getUrl."\n";
				
				$curl = curl_init();

				curl_setopt_array($curl, [
				CURLOPT_PORT => "8443",
				CURLOPT_URL => $getUrl,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_ENCODING => "",
				CURLOPT_MAXREDIRS => 10,
				CURLOPT_TIMEOUT => 30,
				CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST => "GET",
				CURLOPT_HTTPHEADER => [
				"Content-Type: application/json",
				"token: ".$this->token
				],
				]);

				$response = curl_exec($curl);
				$err = curl_error($curl);

				$this->response=$response;
				//var_dump($response);
				curl_close($curl);

				return true;
			} else {
				$this->erro="Número não definido";
				return false;

			}
		}
		
	}
?>