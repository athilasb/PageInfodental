<?php

	class WhatsApp {
		
		private $prefixo = "", 
				$endpoint = "https://srv.infodental.dental:8443",
				$token = "b5b9f54a9b11125a63136f3712e853f1023836b3";

		function __construct($attr) {
			if(isset($attr['prefixo'])) $this->prefixo=$attr['prefixo'];
			if(isset($attr['usr'])) $this->usr=$attr['usr'];

			$sql=new Mysql();
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

		function wtsFilaAdiciona($attr) {
			$_p=$this->prefixo;
			$sql=new Mysql();
			$sqlWts=new Mysql(true);


			// configuracao das mensagens
			$msgConfig=array(1=>'whatsapp_boasvindas',
								2=>'whatsapp_fechamento',
								3=>'whatsapp_chamarClienteFilaDeEspera',
								4=>'whatsapp_filaDeEsperaProximo',
								5=>'whatsapp_chamarClienteVendaBalcao');

			$tipo='';
			if(isset($attr['id_tipo']) and isset($this->tipos[$attr['id_tipo']])) {
				$tipo=$this->tipos[$attr['id_tipo']];
			}

			// boas vindas
			if(is_object($tipo)) {
				$visita='';
				if(isset($attr['id_visita']) and is_numeric($attr['id_visita'])) {
					$sql->consult($_p."clientes_visitas","*","where id='".$attr['id_visita']."'");
					if($sql->rows) {
						$visita=mysqli_fetch_object($sql->mysqry);

						$sql->consult($_p."clientes","*","where id=$visita->id_cliente");
						if($sql->rows) {
							$cliente=mysqli_fetch_object($sql->mysqry);
						}

						$sql->consult($_p."unidades","*","where id=$visita->id_unidade and lixo=0");
						if($sql->rows) {
							$unidade=mysqli_fetch_object($sql->mysqry);
							$sqlWts->consult($_p."unidades","*","where id=$unidade->id");
							$unidadeWts=mysqli_fetch_object($sqlWts->mysqry);
						}

						if(!empty($cliente->celular)) {
							
							$msg='';

							$sql->consult($_p."clientes_visitas","count(*) as total","where id_cliente=$cliente->id and lixo=0");
							$t=mysqli_fetch_object($sql->mysqry);
							$visitasQtd=$t->total;

							$filaPosicao=0;


							// configura e substitui as tags das mensagems

							$msgTipos=array();
							foreach($msgConfig as $idt=>$wm) {
								if(isset($unidadeWts->$wm)) {
									$msgTipos[$idt]=$unidadeWts->$wm;
									$msgTipos[$idt]=str_replace("[nome]",utf8_encode($cliente->nome),$msgTipos[$idt]);
									$msgTipos[$idt]=str_replace("[visitas]",$visitasQtd,$msgTipos[$idt]);
									$msgTipos[$idt]=str_replace("[filaPosicao]",$filaPosicao,$msgTipos[$idt]);
									$msgTipos[$idt]=str_replace("[filaLink]","https://".$_ENV['NAME'].".vucasolution.com.br/fila-de-espera/".sha1($visita->id),$msgTipos[$idt]);
									$avaliacaoTipo=$visita->balcao==0?2:3;
									$msgTipos[$idt]=str_replace("[avaliacaoLink]","https://".$_ENV['NAME'].".vucasolution.com.br/avaliacao/".md5($avaliacaoTipo).".".md5($visita->id),$msgTipos[$idt]);
								}
							}
						
							
							// Mensagem de boas vindas
							if($tipo->id==1) {
								$sql->consult($_p."whatsapp_mensagens","*","where data > NOW() - INTERVAL 3 HOUR AND id_cliente=$cliente->id and id_tipo='".$tipo->id."' and id_visita=$visita->id");
								if($sql->rows==0) {
									if(isset($msgTipos[$tipo->id]) and !empty($msgTipos[$tipo->id])) {
										$sqlWts->consult($_p."unidades","*","where id=$unidade->id");
										$w=mysqli_fetch_object($sqlWts->mysqry);
										$msg=$msgTipos[$tipo->id];
									}
								} else {
									$this->erro="Já está na fila!";
									return false;
								}
							}
							// Mensagem de fechamento
							else if($tipo->id==2) {
								$sql->consult($_p."whatsapp_mensagens","*","where data > NOW() - INTERVAL 3 HOUR AND id_cliente=$cliente->id and id_tipo='".$tipo->id."' and id_visita=$visita->id");
								if($sql->rows==0) {
									if(isset($msgTipos[$tipo->id]) and !empty($msgTipos[$tipo->id])) {
										// $sqlWts->consult($_p."unidades","*","where id=$unidade->id");
										// $w=mysqli_fetch_object($sqlWts->mysqry);
										$msg=$msgTipos[$tipo->id];
									}
								} else {
									$this->erro="Já está na fila!";
									return false;
								}
							}
							// Mensagem de chamar cliente na fila de espera
							else if($tipo->id==3) {
								if(isset($msgTipos[$tipo->id]) and !empty($msgTipos[$tipo->id])) {
									$sqlWts->consult($_p."unidades","*","where id=$unidade->id");
									$w=mysqli_fetch_object($sqlWts->mysqry);
									$msg=$msgTipos[$tipo->id];
								}
							}
							
							// Mensagem de proximo da fila de espera
							else if($tipo->id==4) {
								$sql->consult($_p."whatsapp_mensagens","*","where id_cliente=$cliente->id and id_tipo='".$tipo->id."'  and data > NOW() - INTERVAL 2 HOUR");
								if($sql->rows==0) {
									if(isset($msgTipos[$tipo->id]) and !empty($msgTipos[$tipo->id])) {
										$sqlWts->consult($_p."unidades","*","where id=$unidade->id");
										$w=mysqli_fetch_object($sqlWts->mysqry);
										$msg=$msgTipos[$tipo->id];
									}
								} else {
									$this->erro="Já está na fila!";
									return false;
								}
							}

							// Mensagem de chamar cliente da venda balcao
							else if($tipo->id==5) {
								if(isset($msgTipos[$tipo->id]) and !empty($msgTipos[$tipo->id])) {
									$sqlWts->consult($_p."unidades","*","where id=$unidade->id");
									$w=mysqli_fetch_object($sqlWts->mysqry);
									$msg=$msgTipos[$tipo->id];
								}
							}

							if(!empty($msg)) { 

								$sqlWts->add($_p."whatsapp_mensagens","data=now(),
																	enviado=0,
																	id_cliente=$cliente->id,
																	id_visita=$visita->id,
																	id_unidade=$visita->id_unidade,
																	id_tipo='".$tipo->id."',
																	numero='".telefone($cliente->celular)."',
																	mensagem='".addslashes($msg)."'");
								$id_whatsapp=$sqlWts->ulid;

								if($this->wtsRabbitmq($id_whatsapp)) {

								}
								// $id_msg=$sql->ulid;
								// $sqlWts->update($_p."whatsapp_mensagens","mensagem='".addslashes($msg)."'","where id=$id_msg");
								return true;
							} else {
								$this->erro="Nenhuma mensagem configurada!";
								return false;
							}
							
						}  else {
							$this->erro="Cliente sem celular!";
							return false;
						}
					}  else {
						$this->erro="Visita não encontrada!";
						return false;
					}
				}  else {
					$this->erro="Visita não especificada!";
					return false;
				}
			}  else {
				$this->erro="Tipo de mensagem não definido!";
				return false;
			}
		}

		
		function disparaWhatsapp() {
			$_p=$this->prefixo;
			$sql=new Mysql(true);

			$enviarMsgs=array();
			$visitasIDs=array(-1);
			$deliveryPedidosIDs=array(-1);

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
					if($x->id_visita>0)  $visitasIDs[]=$x->id_visita;
					if($x->id_delivery_pedido>0)  $deliveryPedidosIDs[]=$x->id_delivery_pedido;
				}
			}

			if(count($enviarMsgs)>0) {

				$_visitas=array();
				$clientesIDs=array();
				$unidadesIDs=array();
				$sql->consult($_p."clientes_visitas","*","where id IN (".implode(",",$visitasIDs).") and lixo=0");
				while($x=mysqli_fetch_object($sql->mysqry)) {
					$_visitas[$x->id]=$x;
					$clientesIDs[]=$x->id_cliente;
					$unidadesIDs[]=$x->id_unidade;
				}

				$_pedidos=array();
				$sql->consult($_p."delivery_pedidos","*","where id IN (".implode(",",$deliveryPedidosIDs).") and lixo=0");
			
				while($x=mysqli_fetch_object($sql->mysqry)) {
					$_pedidos[$x->id]=$x;
					$clientesIDs[]=$x->id_cliente;
					$unidadesIDs[]=$x->id_unidade;
				}

				$_clientes=array();
				$sql->consult($_p."clientes","*","where id IN (".implode(",",$clientesIDs).") and lixo=0");
				while($x=mysqli_fetch_object($sql->mysqry)) {
					$_clientes[$x->id]=$x;
				}

				$_unidades=array();
				$whatsappsIDs=array();
				$sql->consult($_p."unidades","*","where id IN (".implode(",",$unidadesIDs).") and lixo=0");
				while($x=mysqli_fetch_object($sql->mysqry)) {
					$_unidades[$x->id]=$x;
					if($x->id_whatsapp>0) $whatsappsIDs[]=$x->id_whatsapp;
				}
				$_whatsapps=array();
				if(count($whatsappsIDs)>0) {
					$sql->consult($_p."whatsapp_instancias","*","where lixo=0");//id IN (".implode(",",$whatsappsIDs).") and lixo=0");
					if($sql->rows) {
						while($x=mysqli_fetch_object($sql->mysqry)) {
							$_whatsapps[$x->id]=$x;
						}
					}
				}

				$_tipos=array();
				$sql->consult($_p."whatsapp_mensagens_tipos","*","");
				while($x=mysqli_fetch_object($sql->mysqry)) {
					$_tipos[$x->id]=$x;
				}

				$sql->add($_p."whatsapp_disparos","data=now(),ativo=1");
				$id_disparo=0;//$sql->ulid;
				foreach($enviarMsgs as $v) {
					
					// verifica se possui conexao
					$sql->consult("vucaADM.vuca_contas_vucazaps_unidades","*","where id_unidade=$v->id_unidade and lixo=0 order by data desc limit 1");
					//echo "<hr>where id_unidade=$v->id_unidade and lixo=0 order by data desc limit 1 -> $sql->rows";
					$conexao=$sql->rows?mysqli_fetch_object($sql->mysqry):'';
					


					if(empty($conexao)) {
						//$this->erro="Nenhuma whatsapp está conectado no momento!";
						//return false;
						$vsql="data_erro=now(),erro=1,erro_retorno='whatsapp desconectado'";
						$vwhere="where id=$v->id";
						$sql->update($_p."whatsapp_mensagens",$vsql,$vwhere);
						continue;
					} else {
							

						$tipo=isset($_tipos[$v->id_tipo])?$_tipos[$v->id_tipo]:'';
						//var_dump($tipo);
						if(is_object($tipo)) {
							if($tipo->tipo=="unidade") {

								if(isset($_visitas[$v->id_visita]) and is_object($_visitas[$v->id_visita])) {
									$visita=$_visitas[$v->id_visita];
									if(isset($_unidades[$visita->id_unidade])) {
										$unidade=$_unidades[$visita->id_unidade];
										



										$attr=array('numero'=>$v->numero,'mensagem'=>$v->mensagem,'id_conexao'=>$conexao->id,'id_unidade'=>$conexao->id_unidade);

										if($this->enviaMensagem($attr)) {
											$vsql="data_enviado=now(),enviado=1,retorno_json='".addslashes($this->response)."',id_conexao=$conexao->id";
											$vwhere="where id=$v->id";
											$sql->update($_p."whatsapp_mensagens",$vsql,$vwhere);

											if(isset($_clientes[$visita->id_cliente]) and is_object($_clientes[$visita->id_cliente])) {
												$cliente=$_clientes[$visita->id_cliente];

												$attr=array('numero'=>telefone($cliente->celular),'instance'=>$conexao->wid);
												if($this->getProfile($attr)) {
													$response=json_decode($this->response);
													if(isset($response->pictureUrl) and !empty($response->pictureUrl)) {
														$_dir="arqs/temp/";
														$img = "../../retaguarda/".$_dir."wtsTemp.jpg";
														$url=$response->pictureUrl;
														if(file_put_contents($img, file_get_contents($url))) {
															// upload da foto 
															$uploadFile=$img;
															$uploadType=filesize($img);
															$uploadPathFile=$this->infosWasabi['_wasabiPathRoot']."arqs/clientes/".$cliente->id.".jpg";
															$uploaded=$this->infosWasabi['wasabiS3']->putObject(S3::inputFile($uploadFile,false),$this->infosWasabi['_wasabiBucket'],$uploadPathFile,S3::ACL_PUBLIC_READ);
															
															if($uploaded) {	
																$sql->update($_p."clientes","foto='jpg',foto_vn='".$_ENV['NAME']."'","where id=$cliente->id");
															}
														}
													}
												}
												
												
											}
											sleep(0.4);
										}  else {
											$vsql="data_erro=now(),erro=1,erro_retorno='".addslashes(isset($this->erro) ? $this->erro : 'sem erro')."',id_conexao=$conexao->id";
											$vwhere="where id=$v->id";
											$sql->update($_p."whatsapp_mensagens",$vsql,$vwhere);
										}

											
									} //else echo "erro";

								} //else echo "erro";
							} else if($tipo->tipo=="delivery") {
								if(isset($_pedidos[$v->id_delivery_pedido]) and is_object($_pedidos[$v->id_delivery_pedido])) {
									$pedido=$_pedidos[$v->id_delivery_pedido];

									if(isset($_unidades[$pedido->id_unidade])) {
										$unidade=$_unidades[$pedido->id_unidade];

										if($tipo->id==6) { // se for mensagem de envio para o entregador
											if(!empty($v->lat) and !empty($v->lng)) {
												$attr=array('id_conexao'=>$conexao->id,
															'id_unidade'=>$conexao->id_unidade,
															'numero'=>$v->numero,
															//'mensagem'=>$v->mensagem,
															'lat'=>$v->lat,
															'lng'=>$v->lng,
															//'mensagem'=>"Pedido #".$pedido->id
														);

												$attrMensagem=array('id_conexao'=>$conexao->id,
																	'id_unidade'=>$conexao->id_unidade,
																	'numero'=>$v->numero,
																	'offline'=>true,
																	'mensagem'=>$v->mensagem);

  		
												$enviado=false;
												if($this->enviaLocalizacao($attr)) {
													$rsp=isset($this->response)?json_decode($this->response):"";
													$quotedMessageId='';
													if(is_object($rsp)) {	
														$quotedMessageId=isset($rsp->id)?$rsp->id:'';
													}

													sleep(3);
													if($this->enviaMensagem($attrMensagem,$quotedMessageId)) {
														$response = isset($this->response)?json_decode($this->response):'';
														if(is_object($response) and isset($response->success) and $response->success===true) {
															$vsql="data_enviado=now(),enviado=1,retorno_json='".addslashes($this->response)."',id_conexao=$conexao->id";
															$vwhere="where id=$v->id";
															$sql->update($_p."whatsapp_mensagens",$vsql,$vwhere);
															$enviado=true;
														} else {
															$vsql="retorno_json='".addslashes($this->response)."',id_conexao=$conexao->id";
															$vwhere="where id=$v->id";
															$sql->update($_p."whatsapp_mensagens",$vsql,$vwhere);	
														}
													}
												}

												/*if($this->enviaLocalizacao($attr) && $this->enviaMensagem($attrMensagem)) {
														$vsql="data_enviado=now(),enviado=1,retorno_json='".addslashes($this->response)."',id_conexao=$conexao->id";
														$vwhere="where id=$v->id";
														$sql->update($_p."whatsapp_mensagens",$vsql,$vwhere);
													
													
												} else {*/
												if($enviado===false) {
													$vsql="data_erro=now(),erro=1,erro_retorno='".addslashes($this->erro)."'";
													$vwhere="where id=$v->id";
													$sql->update($_p."whatsapp_mensagens",$vsql,$vwhere);
												}
											} 
										} else {
											$attr=array('numero'=>$v->numero,'mensagem'=>$v->mensagem,'id_conexao'=>$conexao->id,'id_unidade'=>$conexao->id_unidade);

											if($this->enviaMensagem($attr)) {
												$vsql="data_enviado=now(),enviado=1,retorno_json='".addslashes($this->response)."',id_conexao=$conexao->id";
												$vwhere="where id=$v->id";
												$sql->update($_p."whatsapp_mensagens",$vsql,$vwhere);

												if($tipo->getProfile===1) {
													if(isset($_clientes[$visita->id_cliente]) and is_object($_clientes[$visita->id_cliente])) {
														$cliente=$_clientes[$visita->id_cliente];
													}
												}
												sleep(0.4);
											}  else {
												$vsql="data_erro=now(),erro=1,erro_retorno='".addslashes($this->erro)."',id_conexao=$conexao->id";
												$vwhere="where id=$v->id";
												$sql->update($_p."whatsapp_mensagens",$vsql,$vwhere);
											}
										}

									} //else echo "erro";

								} //else echo "erro";
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
			else if(empty($mensagem)) $erro="Mensagem não definida!";
			else {


				$postfields=array("number"=>$this->wtsNumero($numero),
									"quotedMessageId"=>$quotedMessageId,
									"text"=>$mensagem,
									"instance"=>$conexao->wid);

				if($offline===true) $postfields['offline']=true;

				//var_dump($postfields);

				$sql->add($_p."whatsapp_log","data=now(),
												endpoint='".$this->endpoint."/send/text',
												params='".addslashes(json_encode($postfields))."',
												id_unidade=$unidade->id");
				$id_log=$sql->ulid;


				$curl = curl_init();

				curl_setopt_array($curl, [
				  CURLOPT_PORT => "8443",
				  CURLOPT_URL => $this->endpoint."/send/text",
				  CURLOPT_RETURNTRANSFER => true,
				  CURLOPT_ENCODING => "",
				  CURLOPT_MAXREDIRS => 10,
				  CURLOPT_TIMEOUT => 60,
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