<?php

	class Inteligencia {

		private $prefixo='',
				$_cloudinaryURL='',
				$_wasabiURL='',
				$_pacientesPeriodicidade='',
				$_codigoBI='';


		function __construct($attr) {
			if(isset($attr['_wasabiURL'])) $this->_wasabiURL=$attr['_wasabiURL'];
			if(isset($attr['_cloudinaryURL'])) $this->_cloudinaryURL=$attr['_cloudinaryURL'];
			if(isset($attr['prefixo'])) $this->prefixo=$attr['prefixo'];
			if(isset($attr['_codigoBI'])) $this->_codigoBI=$attr['_codigoBI'];
			if(isset($attr['_pacientesPeriodicidade'])) $this->_pacientesPeriodicidade=$attr['_pacientesPeriodicidade'];

		}

		// Inteligencia / Controle de Exames: retorna os pedidos aguardando, concluido e cancelados(naoRealizado)
		function controleDeExames() {
			$_p=$this->prefixo;
			$sql = new Mysql();
			$_wasabiURL=$this->_wasabiURL;
			$_cloudinaryURL=$this->_cloudinaryURL;


			$_pedidosDeExames=array('concluido'=>[],'aguardando'=>[],'naoRealizado'=>[]);
			$_pacientes=$_evolucoes=$_exames=array();


			$evolucoesIds=$pacientesIds=$examesIds=[];
			$sql->consult($_p."pacientes_evolucoes","*","where id_tipo=6 and lixo=0 order by data_pedido desc");
			while($x=mysqli_fetch_object($sql->mysqry)) {
				$_evolucoes[$x->id]=$x;
				$evolucoesIds[]=$x->id;
				$pacientesIds[]=$x->id_paciente;
			}

			if(count($evolucoesIds)>0) {
				$sql->consult($_p."pacientes_evolucoes_pedidosdeexames","*","where id_evolucao IN (".implode(",",$evolucoesIds).") and lixo=0 order by id desc");
				if($sql->rows) { 
					$regs=array();
					$pacientesConcluidosIds=array();
					$pacientesConcluidosAgendamentos=array();
					$evolucoesPedidosDeExamesIds=array();
					while($x=mysqli_fetch_object($sql->mysqry)) {

						$evolucoesPedidosDeExamesIds[]=$x->id;

						// cancelado so aparecera durante 30 apos o cancelamento
						if($x->status=="naoRealizado") {

							$diasDoCancelamento = strtotime(date('Y-m-d H:i:s')) - strtotime($x->data_atualizacao);
							$diasDoCancelamento /= (60 * 60 * 24);
							$diasDoCancelamento = floor($diasDoCancelamento);

							if($diasDoCancelamento>=30) {
								continue;
							}

						} else if($x->status=="concluido") {
							$pacientesConcluidosIds[]=$x->id_paciente;
							$pacientesConcluidosAgendamentos[$x->id_paciente][]=$x;
						}

						$regs[$x->id]=$x;
					}

					if(count($pacientesConcluidosIds)>0) {
						$sql->consult($_p."agenda","*","where id_paciente IN (".implode(",",$pacientesConcluidosIds).")  and lixo=0 order by agenda_data desc");
						if($sql->rows) {

							$examesExcluir=array();
							while($x=mysqli_fetch_object($sql->mysqry)) {

								// verifica se tem solicitacao de exame que foi concluido
								if(isset($pacientesConcluidosAgendamentos[$x->id_paciente])) {
									$examesConcluidos=$pacientesConcluidosAgendamentos[$x->id_paciente];

									// verifica para cada exame concluido se teve atendimento
									foreach($examesConcluidos as $e) {

										// se a data do agendamento for maior que a data da realização do exame
										if(strtotime($x->agenda_data)>strtotime($e->data_atualizacao)) {
											
											// se o status for concluido, remove da listagem
											if($x->id_status==5) {
												//echo "============================> ".$e->status." / ".$e->id." / ".$e->data_atualizacao." - ".$x->agenda_data." $x->id_status / $e->id_evolucao<BR>"; // 2022-10-29 07:55:49 - 2022-10-29 09:00:00
												//unset($regs[$e->id]);
												$examesExcluir[]=$e->id;
												//echo "$e->id ex\n\n";
											}
											else if($x->id_status==1 or $x->id_status==2) {
												//echo "$e->id add\n\n";
												$regs[$e->id]->agendamentoFuturo=$x->agenda_data;
											
											} 
										}
									}


								}
							}
						}
					}

					// consulta exames anexados
					$_pedidosDeExamesAnexos=array();
					if(count($evolucoesPedidosDeExamesIds)>0) {
						$sql->consult($_p."pacientes_evolucoes_pedidosdeexames_anexos","*","where id_evolucao_pedidodeexame IN (".implode(",",$evolucoesPedidosDeExamesIds).") and lixo=0");
						while($x=mysqli_fetch_object($sql->mysqry)) {
							if(!isset($_pedidosDeExamesAnexos[$x->id_evolucao_pedidodeexame])) $_pedidosDeExamesAnexos[$x->id_evolucao_pedidodeexame]=0;
							$_pedidosDeExamesAnexos[$x->id_evolucao_pedidodeexame]++;
						}
					}


					foreach($regs as $x) {
						$_pedidosDeExames[$x->status][$x->id_evolucao][]=$x;
						$examesIds[]=$x->id_exame;
					}

				}

				$sql->consult($_p."pacientes","id,nome,foto,foto_cn","where id IN (".implode(",",$pacientesIds).") and lixo=0");
				if($sql->rows) {
					while($x=mysqli_fetch_object($sql->mysqry)) {
						$_pacientes[$x->id]=$x;
					}
				}

				$sql->consult($_p."parametros_examedeimagem","*","where id IN (".implode(",",$examesIds).")");
				while($x=mysqli_fetch_object($sql->mysqry)) {
					$_exames[$x->id]=$x;
				}
			}


			// monta arrays
			$pedidos=array('aguardando'=>[],'concluido'=>[],'naoRealizado'=>[]);
			
			if(isset($_pedidosDeExames['aguardando'])) {
				foreach($_pedidosDeExames['aguardando'] as $id_evolucao=>$x) {
					if(isset($_evolucoes[$id_evolucao])) {
						$evolucao=$_evolucoes[$id_evolucao];

						$dif = strtotime(date('Y-m-d'))-strtotime($evolucao->data_pedido);
						$dif = floor($dif/(60 * 60 * 24));
						$alertaMaisDe8Dias=($dif>=8)?1:0;

						if(isset($_pacientes[$evolucao->id_paciente])) {
							$paciente=$_pacientes[$evolucao->id_paciente];

							$ft='';
							if(!empty($paciente->foto_cn)) {
								$ft=$_cloudinaryURL.'c_thumb,w_100,h_100/'.$paciente->foto_cn;
							} else if(!empty($paciente->foto)) {
								$ft=$_wasabiURL."arqs/clientes/".$paciente->id.".jpg";
							}


							$anexos=0;
							foreach($x as $y) {
								// verifica agendamento futuro
								if(isset($y->agendamentoFuturo)) {
									$agendamentoFuturo=date('d/m/Y - H:i',strtotime($y->agendamentoFuturo));
								}

								// verifica se possui anexos
								if(isset($_pedidosDeExamesAnexos[$y->id])) {
									$anexos+=$_pedidosDeExamesAnexos[$y->id];
								}
							}


							$clinica = isset($_clinicas[$evolucao->id_clinica]) ? encodingToJson($_clinicas[$evolucao->id_clinica]->nome_fantasia) : '';

							$pedidos['aguardando'][]=array('id_evolucao'=>$evolucao->id,
															'id_evolucao_pedidodeexame'=>$x[0]->id,
															'data'=>date('d/m/Y',strtotime($evolucao->data_pedido)),
															'paciente'=>encodingToJson($paciente->nome),
															'ft'=>$ft,
															'exames'=>count($x),
															'anexos'=>$anexos,
															'alerta'=>$alertaMaisDe8Dias,
															'clinica'=>$clinica);
						}
					}
				}
			}	
			if(isset($_pedidosDeExames['concluido'])) {

				// para ordenar em ordem de agendamento
				$preLista=[];

				foreach($_pedidosDeExames['concluido'] as $id_evolucao=>$x) {
					
					if(isset($_evolucoes[$id_evolucao])) {
						$evolucao=$_evolucoes[$id_evolucao];
						if(isset($_pacientes[$evolucao->id_paciente])) {
							//echo $evolucao->id."ww\n";
							$paciente=$_pacientes[$evolucao->id_paciente];
							$clinica = isset($_clinicas[$evolucao->id_clinica]) ? encodingToJson($_clinicas[$evolucao->id_clinica]->nome_fantasia) : '';

							$ft='';
							if(!empty($paciente->foto_cn)) {
								$ft=$_cloudinaryURL.'c_thumb,w_100,h_100/'.$paciente->foto_cn;
							} else if(!empty($paciente->foto)) {
								$ft=$_wasabiURL."arqs/clientes/".$paciente->id.".jpg";
							}

							$agendamentoFuturo='';
							$anexos=0;
							foreach($x as $y) {


								$index=0;
								// verifica agendamento futuro
								if(isset($y->agendamentoFuturo)) {
									$agendamentoFuturo=date('d/m/Y - H:i',strtotime($y->agendamentoFuturo));
									$index=strtotime($y->agendamentoFuturo);
								}

								// verifica se possui anexos
								if(isset($_pedidosDeExamesAnexos[$y->id])) {
									$anexos+=$_pedidosDeExamesAnexos[$y->id];
								}
							}


							do {
								$index++;
							} while(isset($preLista[$index]));

							$preLista[$index]=array('index'=>$index,'id_evolucao'=>$evolucao->id,
															'id_evolucao_pedidodeexame'=>$x[0]->id,
															'data'=>date('d/m/Y',strtotime($evolucao->data_pedido)),
															'paciente'=>encodingToJson($paciente->nome),
															'ft'=>$ft,
															'exames'=>count($x),
															'clinica'=>$clinica,
															'anexos'=>$anexos,
															'agendamentoFuturo'=>$agendamentoFuturo);
						} 
					} 
				}

				// ordena lista 
				krsort($preLista);
				foreach($preLista as $x) {
					$pedidos['concluido'][]=$x;
				}
			}

			if(isset($_pedidosDeExames['naoRealizado'])) {
				foreach($_pedidosDeExames['naoRealizado'] as $id_evolucao=>$x) {
					if(isset($_evolucoes[$id_evolucao])) {
						$evolucao=$_evolucoes[$id_evolucao];
						if(isset($_pacientes[$evolucao->id_paciente])) {
							$paciente=$_pacientes[$evolucao->id_paciente];
							$clinica = isset($_clinicas[$evolucao->id_clinica]) ? encodingToJson($_clinicas[$evolucao->id_clinica]->nome_fantasia) : '';

							$ft='';
							if(!empty($paciente->foto_cn)) {
								$ft=$_cloudinaryURL.'c_thumb,w_100,h_100/'.$paciente->foto_cn;
							} else if(!empty($paciente->foto)) {
								$ft=$_wasabiURL."arqs/clientes/".$paciente->id.".jpg";
							}


							$anexos=0;
							foreach($x as $y) {
								// verifica agendamento futuro
								if(isset($y->agendamentoFuturo)) {
									$agendamentoFuturo=date('d/m/Y - H:i',strtotime($y->agendamentoFuturo));
								}

								// verifica se possui anexos
								if(isset($_pedidosDeExamesAnexos[$y->id])) {
									$anexos+=$_pedidosDeExamesAnexos[$y->id];
								}
							}

							$pedidos['naoRealizado'][]=array('id_evolucao'=>$evolucao->id,
															'id_evolucao_pedidodeexame'=>$x[0]->id,
															'data'=>date('d/m/Y',strtotime($evolucao->data_pedido)),
															'paciente'=>encodingToJson($paciente->nome),
															'ft'=>$ft,
															'exames'=>count($x),
															'anexos'=>$anexos,
															'clinica'=>$clinica);
						}
					}
				}
			}

			$this->_pedidosDeExames=$_pedidosDeExames;
			$this->pedidos=$pedidos;
			return true;
		}


		// Gestao de Tempo (basea nos registros de proximo atendimento)
		function gestaoDoTempo() {
			$_wasabiBucket="storage.infodental.dental"; 
			$_wasabiPathRoot= $_ENV['NAME'] . "/";
			$_wasabiS3endpoint = "s3.us-west-1.wasabisys.com";
			$_wasabiS3Region = "us-west-1";
			$_wasabiURL="https://$_wasabiS3endpoint/$_wasabiBucket/$_wasabiPathRoot";
			$_p=$this->prefixo;
			$_cloudinaryURL=$this->_cloudinaryURL;
			$_codigoBI=$this->_codigoBI;
			$sql=new Mysql();

			// lista de profissionais
				$_profissionais=array();
				$sql->consult($_p."colaboradores","id,nome","");
				while($x=mysqli_fetch_object($sql->mysqry)) $_profissionais[$x->id]=$x;

			// define a periodicidade
				$di = date('Y-m-d');
				$df = date('Y-m-d',strtotime(date('Y-m-d H:i:s')." + 3 day"));


			// retornos
				$pacientesIds=array();
				$pacienteOrdem=array();
				$proximasConsultasIds=array();

			# pacientes que tem proximo agendamento para hoje ou nos proximos 3 dias
				
				// nao disponiveis
				$this->indisponiveis=0;
				$where="where  DATE_ADD(data, INTERVAL retorno DAY)>'".$df." 23:59:59' and lixo=0 and situacao<3 order by data desc";
				$sql->consult($_p."pacientes_proximasconsultas","*,DATE_ADD(data, INTERVAL retorno DAY) as proximaConsulta",$where);
				while($x=mysqli_fetch_object($sql->mysqry)) {
					
					// se ja encontrou proxima consulta ignora, pois pega a mais recente
					if(isset($proximaConsulta[$x->id_paciente])) {
						continue;
					} else {
						$this->indisponiveis++;
					}


				
				}

				// situacao<3 => paciente entrara em contato (3), ou excluido (5)
				$where="where  DATE_ADD(data, INTERVAL retorno DAY)<='".$df." 23:59:59' and lixo=0 and situacao<3 order by data desc";
				$sql->consult($_p."pacientes_proximasconsultas","*,DATE_ADD(data, INTERVAL retorno DAY) as proximaConsulta",$where);
				while($x=mysqli_fetch_object($sql->mysqry)) {
					
					// se ja encontrou proxima consulta ignora, pois pega a mais recente
					if(isset($proximaConsulta[$x->id_paciente])) {
						continue;
					} else {
						$proximaConsulta[$x->id_paciente]=$x;
					}


					$pacientesIds[$x->id_paciente]=$x->id_paciente;
					$index=$x->situacao.".".strtotime($x->proximaConsulta);
					while(isset($pacienteOrdem[$index])) {
						$index++;
					}

					$pacienteOrdem[$index]=$x->id_paciente;
					$proximasConsultasIds[]=$x->id;
				}


			$listaInteligente=array();
			if(count($pacientesIds)>0) {

				# verifica os pacientes que possuem agendamento futuro
					$pacientesIdsQueMarcaram=array();

					$where="where id_paciente IN (".implode(",",$pacientesIds).") and lixo=0 and id_status IN (1,2,6,7) order by agenda_data desc";
					$sql->consult($_p."agenda","*",$where);
					while($x=mysqli_fetch_object($sql->mysqry)) {

						// se o agendamento for maior que o "proxima consulta que foi agendado"
						if(strtotime($x->agenda_data)>strtotime($proximaConsulta[$x->id_paciente]->proximaConsulta)) {
							$pacientesIdsQueMarcaram[$x->id_paciente][]=$x->agenda_data;
						}
					}

				# busca pacientes que tem proximo agendamento cadastrado
					$preListaInteligente=array();
					$sql->consult($_p."pacientes","id,nome,periodicidade,foto,data_nascimento,telefone1,codigo_bi,foto_cn,situacao","where id IN (".implode(",",$pacientesIds).")");
					if($sql->rows) {
						while($x=mysqli_fetch_object($sql->mysqry)) {

							

							// busca agendamentos futuros do paciente
								$agendamentosFuturos=array();
								if(isset($pacientesIdsQueMarcaram[$x->id])) {
									foreach($pacientesIdsQueMarcaram[$x->id] as $dt) {
										$agendamentosFuturos[]=date('d/m/Y H:i',strtotime($dt));
									}
								}

							$proximaConsultaJSON='';
							$proxima='';
							if(isset($proximaConsulta[$x->id])) {

								// identifica os profissionais
									$profissionais=array();
									if(!empty($proximaConsulta[$x->id]->profissionais)) {
										$aux=explode(",",$proximaConsulta[$x->id]->profissionais);
										
										foreach($aux as $idP) {
											if(!empty($idP) and is_numeric($idP) and isset($_profissionais[$idP])) {
												$profissionais[]=array('nome'=>utf8_encode($_profissionais[$idP]->nome));
											}
										}	
									}

								// estrutura objeto de proxima consulta
									$i=$proximaConsulta[$x->id];
									$proxima=date('Y-m-d H:i',strtotime("$i->data + $i->retorno day"));
									$proximaConsultaJSON=array('duracao'=>(int)$i->duracao,
																'dataProx'=>date('d/m/Y',strtotime("$i->data + $i->retorno day")),
																'laboratorio'=>(int)$i->laboratorio,
																'imagem'=>(int)$i->imagem,
																'profissionais'=>$profissionais,
																'obs'=>addslashes(utf8_encode($i->obs)));
								
								// cria objeto do paciente da lista inteligente

									$ft='img/ilustra-perfil.png';
									if(!empty($x->foto_cn)) {
										$ft=$_cloudinaryURL.'c_thumb,w_100,h_100/'.$x->foto_cn;
									} else if(!empty($x->foto)) {
										$ft=$_wasabiURL."arqs/clientes/".$x->id.".jpg";
									}
									$preListaInteligente[$x->id]=array('id_proximaconsulta'=>(int)$i->id,
																		'proxima'=>$proxima,
																		'id_paciente'=>$x->id,
																		'nome'=>addslashes(utf8_encode($x->nome)),
																		'proximaConsulta'=>$proximaConsultaJSON,
																		'periodicidade'=>$x->periodicidade,
																		'telefone'=>telefoneMascara($x->telefone1),
																		'bi'=>isset($_codigoBI[$x->codigo_bi])?utf8_encode($_codigoBI[$x->codigo_bi]):$x->codigo_bi,
																		'idade'=>(int)idade($x->data_nascimento),
																		'ft'=>$ft,
																		'ultimoAtendimento'=>'-',
																		'atendimentos'=>0,
																		'tempoMedio'=>0,
																		'faltou'=>0,
																		'futuros'=>$agendamentosFuturos);
							}
						}
					}

				# ordena a lista conforme proximo agendamento de forma crescente
					ksort($pacienteOrdem);
					foreach($pacienteOrdem as $str=>$idPaciente) {
						if(isset($preListaInteligente[$idPaciente])) {
							$listaInteligente[]=$preListaInteligente[$idPaciente];
						}
					}

				# busca as metricas dos pacientes
					$pacientesMetricas=array();
					$where="where id_paciente IN (".implode(",",$pacientesIds).") and agenda_data>now() and lixo=0 order by data desc";
					$sql->consult($_p."agenda","id,id_status,id_paciente,agenda_data,agenda_duracao",$where);

					while($x=mysqli_fetch_object($sql->mysqry)) {

						if(!isset($pacientesMetricas[$x->id_paciente])) {

							$ultimo='';
							if($x->id_status==5) {
								$ultimo=strtotime(date('Y-m-d'))-strtotime($x->agenda_data);
								$ultimo/=(60*60*24);
								$ultimo=floor($ultimo)+1;
							}

							$pacientesMetricas[$x->id_paciente]=array('atendimentos'=>0,
																'tempo'=>0,
																'faltou'=>0,
																'ultimoAtendimento'=>$ultimo);
						}
						if($x->id_status==5) {
							$pacientesMetricas[$x->id_paciente]['tempo']+=$x->agenda_duracao;
							$pacientesMetricas[$x->id_paciente]['atendimentos']++; 


							if(empty($pacientesMetricas[$x->id_paciente]['ultimoAtendimento'])) {
								$ultimo=strtotime(date('Y-m-d'))-strtotime($x->agenda_data);
								$ultimo/=(60*60*24);
								$ultimo=floor($ultimo)+1;
								$pacientesMetricas[$x->id_paciente]['ultimoAtendimento']=$ultimo;


							}
						}

						else if($x->id_status==3) {
							$pacientesMetricas[$x->id_paciente]['faltou']++; 
						}
					}

				# busca historicos dos pacientes (evento = observacao => Pedir para entrar em contato, Nao conseguiu contato...)
					$_pacientesHistorico=array();

					$where="where id_paciente IN (".implode(",",$pacientesIds).") and 
								id_proximaconsulta IN (".implode(",",$proximasConsultasIds).") and 
								evento='observacao' and 
								lixo=0 order by data desc";

					$sql->consult($_p."pacientes_historico","*",$where);
					if($sql->rows) {
						while($x=mysqli_fetch_object($sql->mysqry)) {
							if($x->id_obs==7) continue;
							if(!isset($_pacientesHistorico[$x->id_paciente])) {
								$_pacientesHistorico[$x->id_paciente]=$x;
							}
						}
					}
				
				# aplica metricas dos pacientes na lista de inteligencia
					$listaInteligenteComMetricasAplicadas=array();
					foreach($listaInteligente as $x) {
						$x=(object)$x;

						$obj=$x;
						if(isset($pacientesMetricas[$x->id_paciente])) {
							$obj->atendimentos=$pacientesMetricas[$x->id_paciente]['atendimentos'];
							$obj->faltou=$pacientesMetricas[$x->id_paciente]['faltou'];
							$obj->ultimoAtendimento=$pacientesMetricas[$x->id_paciente]['ultimoAtendimento'];
							$obj->tempoMedio=$pacientesMetricas[$x->id_paciente]['atendimentos']==0?0:round($pacientesMetricas[$x->id_paciente]['tempo']/$pacientesMetricas[$x->id_paciente]['atendimentos']);
						}

						if(isset($_pacientesHistorico[$x->id_paciente])) {
							$obj->id_obs=(int)$_pacientesHistorico[$x->id_paciente]->id_obs;
							$obj->id_obs_observacoes=(int)$_pacientesHistorico[$x->id_paciente]->relacionamento_momento;
						} else {
							$obj->id_obs=0;
							$obj->id_obs_observacoes='';
						}

						$listaInteligenteComMetricasAplicadas[]=$obj;
					}

					$listaInteligente=$listaInteligenteComMetricasAplicadas;

			}

			# ordena em 2 lista e depois unifica de forma ordenada

				$listaInteligenteFinalPreOrdenadaFinal=array();

				$listaInteligenteFinalPreOrdenada=array();
				foreach($listaInteligente as $x) {

					// exclui da lista se for: excluido (5), resolvido (6), agendado pela tarefa inteligente (7)
					if($x->id_obs==5 or $x->id_obs==6 or $x->id_obs==7) continue;

					// primeira lista
					$idObs=1;

					// se "NAO CONSEGUIU CONTATO (1)", "PACIENTE ENTRARA EM CONTATO (2)", "PACIENTE PEDIU PARA RETORNAR (3)", "SUGESTAO PULADA (4)", vai para segunda lista
					if($x->id_obs==1 or $x->id_obs==2 or $x->id_obs==3 or $x->id_obs==4) $idObs=2;

					// trata duplicidade de index
						$index=strtotime($x->proxima);;
						while(isset($listaInteligenteFinalPreOrdenada[$idObs][$index])) {
							$index+=1;
						}
					
					$listaInteligenteFinalPreOrdenada[$idObs][$index]=$x; 
				}

				// ordena as 2 listas geradas 
					if(isset($listaInteligenteFinalPreOrdenada[1])) ksort($listaInteligenteFinalPreOrdenada[1]);
					if(isset($listaInteligenteFinalPreOrdenada[2])) ksort($listaInteligenteFinalPreOrdenada[2]);

				// unifica as 2 listas geradas
					if(isset($listaInteligenteFinalPreOrdenada[1]) and isset($listaInteligenteFinalPreOrdenada[2])) {
						$listaInteligenteFinalOrdenada=array_merge($listaInteligenteFinalPreOrdenada[1],$listaInteligenteFinalPreOrdenada[2]);
					} else if(isset($listaInteligenteFinalPreOrdenada[1])) {
						$listaInteligenteFinalOrdenada=$listaInteligenteFinalPreOrdenada[1];
					} else if(isset($listaInteligenteFinalPreOrdenada[2])) {
						$listaInteligenteFinalOrdenada=$listaInteligenteFinalPreOrdenada[2];
					}

				// cria lista final

					if(isset($listaInteligenteFinalOrdenada) and is_array($listaInteligenteFinalOrdenada)) {
						foreach($listaInteligenteFinalOrdenada as $x) {
							$listaInteligenteFinalPreOrdenadaFinal[]=$x;
						}
					}

			return $listaInteligenteFinalPreOrdenadaFinal;

		}


		// Gestao de Pacientes
		function gestaoDePacientes() {
			$_wasabiBucket="storage.infodental.dental"; 
			$_wasabiPathRoot= $_ENV['NAME'] . "/";
			$_wasabiS3endpoint = "s3.us-west-1.wasabisys.com";
			$_wasabiS3Region = "us-west-1";
			$_wasabiURL="https://$_wasabiS3endpoint/$_wasabiBucket/$_wasabiPathRoot";

			$_p=$this->prefixo;
			$_cloudinaryURL=$this->_cloudinaryURL;
			$_codigoBI=$this->_codigoBI;
			$_pacientesPeriodicidade=$this->_pacientesPeriodicidade;
			$sql=new Mysql();


			// lista de profissionais
				$_profissionais=array();
				$sql->consult($_p."colaboradores","id,nome,calendario_iniciais,calendario_cor","");
				while($x=mysqli_fetch_object($sql->mysqry)) $_profissionais[$x->id]=$x;
			
			// lista de status da agenda
				$_status=array();
				$sql->consult($_p."agenda_status","*","");
				while($x=mysqli_fetch_object($sql->mysqry)) $_status[$x->id]=$x;
			
			// lista de status de historico do paciente (id_obs)
				$_historicoStatus=array();
				$sql->consult($_p."pacientes_historico_status","*","");
				while($x=mysqli_fetch_object($sql->mysqry)) $_historicoStatus[$x->id]=$x;


			$todosPacientesIds=array();

			// lista dos pacientes que tem historico adicionados na data de hoje (vai para o final da fila)
			$listaDosComHistoricoNoDia=array();

			# Desmarcados sem agendamentos

				$pacientesDesmarcadosIds=array();
				$pacientesDesmarcados=array();

				// busca pacientes desmarcados/faltou nos ultimos 90 dias 
					$sql->consult($_p."agenda","id,id_paciente,agenda_data","WHERE agenda_data > NOW() - INTERVAL 90 DAY and id_status IN (4,3) and lixo=0 order by agenda_data desc");
					if($sql->rows) {
						while($x=mysqli_fetch_object($sql->mysqry)) {
							$pacientesDesmarcadosIds[$x->id_paciente]=$x->id_paciente;

							// capta apenas o ultimo desmarcado
							if(!isset($pacientesDesmarcados[$x->id_paciente])) {
								$pacientesDesmarcados[$x->id_paciente]=$x;
							}
						}
					}


				if(count($pacientesDesmarcadosIds)>0) {

					// remove pacientes que nao foram atendidos só uma vez
					$sql->consult($_p."agenda","id_paciente","where id_paciente IN (".implode(",",$pacientesDesmarcadosIds).") and lixo=0 and id_status=5");
					if($sql->rows) {
						$pacientesAtendidos=$pacientesAtendidosIds=array();
						while($x=mysqli_fetch_object($sql->mysqry)) {
							if(!isset($pacientesAtendidos[$x->id_paciente])) {
								$pacientesAtendidos[$x->id_paciente]=1;
							} else {
								$pacientesAtendidos[$x->id_paciente]++;
								$pacientesAtendidosIds[]=$x->id_paciente;
							}
						}
					}

					$pacientesDesmarcadosIds=$pacientesAtendidosIds;

					// remove pacientes que desmarcaram e que foram agendados novamente
						$sql->consult($_p."agenda","id,id_paciente,agenda_data","WHERE agenda_data > NOW() - INTERVAL 360 DAY and id_paciente IN (".implode(",",$pacientesDesmarcadosIds).") and id_status IN (1,2,6,7,5) and lixo=0 order by agenda_data desc");
						while($x=mysqli_fetch_object($sql->mysqry)) {

							if(isset($pacientesDesmarcados[$x->id_paciente])) {

								// ultimo agendamento desmarcado
								$ultimoAgendamentoDesmarcado = $pacientesDesmarcados[$x->id_paciente];

								// se o ultimo agendamento desmarcado for menor que o ultimo agendamento confirmado, a confirmado ou atendido, remove da lista
								$removerDaLista = (strtotime($ultimoAgendamentoDesmarcado->agenda_data)<strtotime($x->agenda_data))?1:0;
								
								if($removerDaLista==1) {
									unset($pacientesDesmarcados[$x->id_paciente]);
									unset($pacientesDesmarcadosIds[$x->id_paciente]);
								}
							}

						}

					// busca historico (evento=observacao) dos pacientes
						$pacientesHistoricos=array();
						$pacientesHistoricosObj=array();
						$sql->consult($_p."pacientes_historico","*","where id_paciente IN (".implode(",",$pacientesDesmarcadosIds).") and evento='observacao' and lixo=0 order by data desc");
						while($x=mysqli_fetch_object($sql->mysqry)) {
							if($x->id_obs==7) continue;
							if(!isset($pacientesHistoricos[$x->id_paciente])) {
								$pacientesHistoricos[$x->id_paciente]=$x->id_obs;
								$pacientesHistoricosObj[$x->id_paciente]=$x;
							}
						}

					// busca pacientes que foram desmarcados
						$_pacientes=array();
						$sql->consult($_p."pacientes","id,nome,telefone1,foto,foto_cn,profissional_maisAtende,codigo_bi,data_nascimento,periodicidade,situacao","where id IN (".implode(",",$pacientesDesmarcadosIds).") and lixo=0");
						while ($x=mysqli_fetch_object($sql->mysqry)) {
							// se desativado
							if($x->situacao=="EXCLUIDO") {
								continue;
							}

							$_pacientes[$x->id]=$x;
						}

					// pacientes que foram desmarcados e nao tiveram outro agendamento confirmado, a confirmar ou atendido
						$pacientesDesmarcadosAUX=array();
						foreach($pacientesDesmarcados as $id_paciente=>$v) {
							if(isset($_pacientes[$id_paciente])) {


								$todosPacientesIds[$v->id_paciente]=$v->id_paciente;

								// busca objetdo do paciente
								$paciente=$_pacientes[$id_paciente];

								// busca ultimo historico evento do paciente
								$historico=isset($pacientesHistoricos[$paciente->id])?$pacientesHistoricos[$paciente->id]:0;
								
								$ft='img/ilustra-perfil.png';
								if(!empty($paciente->foto_cn)) {
									$ft=$_cloudinaryURL.'c_thumb,w_100,h_100/'.$paciente->foto_cn;
								} else if(!empty($paciente->foto)) {
									$ft=$_wasabiURL."arqs/clientes/".$paciente->id.".jpg";
								}

								// se possui historico adicionado no dia, entra no final
								if($historico>0) {
									$historicoObj=$pacientesHistoricosObj[$paciente->id];
									if(strtotime(date('Y-m-d'))==strtotime(date('Y-m-d',strtotime($historicoObj->data)))) {

										

										$listaDosComHistoricoNoDia[]=array('id_paciente'=>$paciente->id,
																			'nome'=>utf8_encode($paciente->nome),
																			'periodicidade'=>$paciente->periodicidade,
																			'ft'=>$ft,
																			'idade'=>idade($paciente->data_nascimento),
																			'bi'=>isset($_codigoBI[$paciente->codigo_bi])?utf8_encode($_codigoBI[$paciente->codigo_bi]):$paciente->codigo_bi,
																			'status'=>$historico,
																			'id_profissional'=>$paciente->profissional_maisAtende,
																		);
										continue;

									}
								}

								$pacientesDesmarcadosAUX[]=array('id_paciente'=>$paciente->id,
																		'nome'=>utf8_encode($paciente->nome),
																		'periodicidade'=>$paciente->periodicidade,
																		'ft'=>$ft,
																		'idade'=>idade($paciente->data_nascimento),
																		'bi'=>isset($_codigoBI[$paciente->codigo_bi])?utf8_encode($_codigoBI[$paciente->codigo_bi]):$paciente->codigo_bi,
																		'status'=>$historico,
																		'id_profissional'=>$paciente->profissional_maisAtende,
																		'telefone'=>empty($paciente->telefone1)?"":maskTelefone($paciente->telefone1));

							}
						}

						$pacientesDesmarcados=$pacientesDesmarcadosAUX;
					
				}

			# Pacientes em contencao (precisa retornar) sem horario

				$pacientesContencaoIds=array();
				$agendaPacientesAtendidos=array();


				// busca os agendamentos dos ultimos 720 dias com status atendido
					$sql->consult($_p."agenda","id,id_paciente,agenda_data","WHERE agenda_data > NOW() - INTERVAL 720 DAY and id_status=5 and lixo=0 order by agenda_data desc");
					$pacientesAtendidosQtdVezes=array(); // contabiliza quantas vezes o paciente foi atendido (id_status=5)
					while($x=mysqli_fetch_object($sql->mysqry)) {

						if(!isset($agendaPacientesAtendidos[$x->id_paciente])) {


							// cotabiliza quantas vezes foi atendido
								if(!isset($pacientesAtendidosQtdVezes[$x->id_paciente])) $pacientesAtendidosQtdVezes[$x->id_paciente]=0;
								$pacientesAtendidosQtdVezes[$x->id_paciente]++;

							// se foi atendido mais de uma vez
								if($pacientesAtendidosQtdVezes[$x->id_paciente]>=2) {
									$agendaPacientesAtendidos[$x->id_paciente]=$x;
									$pacientesContencaoIds[$x->id_paciente]=$x->id_paciente;
								}
						}
					}


				$retornoPacientesAgendaJSON=array();
				if(count($pacientesContencaoIds)>0) {


					// filtra pacientes que possuem periodicidade
						$pacientesAtendidosComPeriodicidade=array();
						$sql->consult($_p."pacientes","id,nome,telefone1,foto,foto_cn,periodicidade,profissional_maisAtende,codigo_bi,data_nascimento,situacao","where id IN (".implode(",",$pacientesContencaoIds).") and lixo=0 order by nome");
						while ($x=mysqli_fetch_object($sql->mysqry)) {

							// se desativado
							if($x->situacao=="EXCLUIDO") {
								unset($pacientesContencaoIds[$x->id]);
							} 
							else if(isset($_pacientesPeriodicidade[$x->periodicidade])) {
								$pacientesAtendidos[$x->id]=$x;
								$pacientesAtendidosComPeriodicidade[$x->periodicidade][$x->id]=$x->id;
							} else {
								unset($pacientesContencaoIds[$x->id]);
							}
						}

					// busca historicos dos pacientes (evento = observacao => Pedir para entrar em contato, Nao conseguiu contato...)
						$_pacientesHistorico=array();
						$_pacientesHistoricoObj=array();
						$sql->consult($_p."pacientes_historico","*","where id_paciente IN (".implode(",",$pacientesContencaoIds).") and evento='observacao' and lixo=0 order by data desc");
						while($x=mysqli_fetch_object($sql->mysqry)) {
							if($x->id_obs==7) continue;
							if(!isset($_pacientesHistorico[$x->id_paciente])) {
								$_pacientesHistorico[$x->id_paciente]=$x->id_obs;
								$_pacientesHistoricoObj[$x->id_paciente]=$x;
							}
						}

					// roda todos os pacientes atendidos por periodicidade para remover os que foram atendidos dentro da periodicidade
						$pacientesAtendidosComPeriodicidadeAux = $pacientesAtendidosComPeriodicidade;
						$contencaoTodosIds=array();
						foreach($pacientesAtendidosComPeriodicidade as $periodicidade=>$pacientesContencaoIds) {

							$contencaoTodosIds = array_merge($pacientesContencaoIds,$contencaoTodosIds);
							// busca agendamentos dos pacientes da periodicidade que foram atendidos e nao necessitam de retorno
							//echo "WHERE data > NOW() - INTERVAL $periodicidade MONTH and id_status IN (5,1,2) and id_paciente IN (".implode(",",$pacientesContencaoIds).") and lixo=0 order by agenda_data desc\n\n";
							$sql->consult($_p."agenda","id,id_paciente,agenda_data","WHERE agenda_data > NOW() - INTERVAL $periodicidade MONTH and id_status IN (5,1,2) and id_paciente IN (".implode(",",$pacientesContencaoIds).") and lixo=0 order by agenda_data desc");
							while($x=mysqli_fetch_object($sql->mysqry)) {
								// remove da lista de pacientes que necessitam de retorno
								unset($pacientesAtendidosComPeriodicidadeAux[$periodicidade][$x->id_paciente]);
							}
						}
					

						$pacientesAtendidosComPeriodicidade=$pacientesAtendidosComPeriodicidadeAux;
					
					// monta a lista dos pacientes que necessitam de retorno
						$pacientesRetorno=array();
						foreach($pacientesAtendidosComPeriodicidade as $periodicidade=>$pacientes) {
					
							foreach($pacientes as $idPaciente) {
								$todosPacientesIds[$idPaciente]=$idPaciente;

								// se nao estiver na lista de desmarcados (desmarcadosPacientesIds);
								if(isset($pacientesAtendidos[$idPaciente]) and !isset($pacientesDesmarcadosIds[$idPaciente])) {
									$paciente=$pacientesAtendidos[$idPaciente];
									
									// ultimo agendamento 
									$ultimoAtendimento='';
									
									if(isset($agendaPacientesAtendidos[$paciente->id])) {
										$u=$agendaPacientesAtendidos[$paciente->id];
										$ultimoAtendimento=date('d/m/Y',strtotime($u->agenda_data));

										$tem=strtotime(date('Y-m-d H:i'))-strtotime($u->agenda_data);
										$tem/=(60*60*24*30);
										$tem=ceil($tem);
										if($tem<$paciente->periodicidade) continue;
										//$nome.=" ($paciente->periodicidade) ha $tem mese(s) - $u->agenda_data";
									} else {
										continue;
									}



									$historico=isset($_pacientesHistorico[$paciente->id])?$_pacientesHistorico[$paciente->id]:0;

									$index=strtotime($u->agenda_data);
									if(isset($pacientesRetorno[$index])) {
										$index++;
									}

									$ft='img/ilustra-perfil.png';
									if(!empty($paciente->foto_cn)) {
										$ft=$_cloudinaryURL.'c_thumb,w_100,h_100/'.$paciente->foto_cn;
									} else if(!empty($paciente->foto)) {
										$ft=$_wasabiURL."arqs/clientes/".$paciente->id.".jpg";
									}



									// se possui historico adicionado no dia, entra no final
									if($historico>0) {
										$historicoObj=$_pacientesHistoricoObj[$paciente->id];
										if(strtotime(date('Y-m-d'))==strtotime(date('Y-m-d',strtotime($historicoObj->data)))) {
										
											$listaDosComHistoricoNoDia[]=array('id_paciente'=>$paciente->id,
																				'nome'=>utf8_encode($paciente->nome),
																				'periodicidade'=>$paciente->periodicidade,
																				'ft'=>$ft,
																				'idade'=>idade($paciente->data_nascimento),
																				'bi'=>isset($_codigoBI[$paciente->codigo_bi])?utf8_encode($_codigoBI[$paciente->codigo_bi]):$paciente->codigo_bi,
																				'status'=>$historico,
																				'id_profissional'=>$paciente->profissional_maisAtende,
																			);
											continue;

										}
									} 

									$pacientesRetorno[$index]=array('id_paciente'=>$paciente->id,
																			'nome'=>utf8_encode($paciente->nome),
																			'periodicidade'=>$paciente->periodicidade,
																			'bi'=>isset($_codigoBI[$paciente->codigo_bi])?utf8_encode($_codigoBI[$paciente->codigo_bi]):$paciente->codigo_bi,
																			'ft'=>$ft,
																			'idade'=>idade($paciente->data_nascimento),
																			'status'=>$historico,
																			'id_profissional'=>$paciente->profissional_maisAtende,
																			'telefone'=>empty($paciente->telefone1)?"":maskTelefone($paciente->telefone1));
								
								}
							}
						}

					// ordena lista sem o index no array
						arsort($pacientesRetorno);
						$pacientesRetornoAux=array();
						foreach($pacientesRetorno as $x) {
							$pacientesRetornoAux[]=$x;
						}
						$pacientesRetorno=$pacientesRetornoAux;

				}


			# Busca metricas de Pacientes Desmarcados e Pacientes em Contencao
				$pacientesMetricas=array();
				if(count($todosPacientesIds)>0) {
					$sql->consult($_p."agenda","id,id_status,id_paciente,agenda_data,agenda_duracao,profissionais","where id_paciente IN (".implode(",",$todosPacientesIds).") and lixo=0 order by agenda_data desc");
					while($x=mysqli_fetch_object($sql->mysqry)) {


						if(!isset($pacientesMetricas[$x->id_paciente])) {

							$ultimo='';
							if($x->id_status==5) {
								$ultimo=strtotime(date('Y-m-d'))-strtotime($x->agenda_data);
								$ultimo/=(60*60*24);
								$ultimo=floor($ultimo)+1;
								//echo (date('Y-m-d'))." - ".($x->agenda_data)." = $ultimo \n";
							}

							$pacientesMetricas[$x->id_paciente]=array('atendimentos'=>0,
																		'tempo'=>0,
																		'faltou'=>0,
																		'desmarcou'=>0,
																		'faltouOuDesmarcou'=>0,
																		'ultimoAtendimento'=>$ultimo,
																		'ultimos'=>array());
						}


						$profissionais=array();
						if(!empty($x->profissionais)) {
							$aux=explode(",",$x->profissionais);
							foreach($aux as $idP) {

								if(isset($_profissionais[$idP])) {
									$p=$_profissionais[$idP];

									$profissionais[]=array('idP'=>$idP,
															'iniciais'=>$p->calendario_iniciais,
															'cor'=>$p->calendario_cor);
								}
							}
						}
						if(count($pacientesMetricas[$x->id_paciente]['ultimos'])<3) {
							$pacientesMetricas[$x->id_paciente]['ultimos'][]=array('status'=>isset($_status[$x->id_status])?utf8_encode($_status[$x->id_status]->titulo):'',
									'agenda_data'=>date('d/m/y',strtotime($x->agenda_data)),
																					'agenda_hora'=>date('H:i',strtotime($x->agenda_data)),
																						'profissionais'=>$profissionais); 
						}

						if($x->id_status==5) {

							$pacientesMetricas[$x->id_paciente]['tempo']+=$x->agenda_duracao;
							$pacientesMetricas[$x->id_paciente]['atendimentos']++; 

							


							if(empty($pacientesMetricas[$x->id_paciente]['ultimoAtendimento'])) {
								$ultimo=strtotime(date('Y-m-d'))-strtotime($x->agenda_data);
								$ultimo/=(60*60*24);
								$ultimo=floor($ultimo)+1;
								$pacientesMetricas[$x->id_paciente]['ultimoAtendimento']=$ultimo;


							}
						}
						else if($x->id_status==3) $pacientesMetricas[$x->id_paciente]['faltou']++; 
						else if($x->id_status==4) $pacientesMetricas[$x->id_paciente]['desmarcou']++; 


						// se faltou ou desmarcou
						if($x->id_status==3 or $x->id_status==4) $pacientesMetricas[$x->id_paciente]['faltouOuDesmarcou']++;
						
					}
				}


			# Ordena lista
					// Ordena lista

					/*
					3 - Pediu pra retornar
					0 - Sem status
					1 - Não conseguiu contato
					2 - Paciente entrará em contato
					*/

					// numero de pacientes total na lista do desmarcados e retorno
					$numeroTotal=0;

					$statusOrdem=array(3=>1,
										0=>2,
										1=>3,
										2=>4);

					// ordena pacientes Desmarcados
						$pacientesDesmarcadosAux=array();
						foreach($pacientesDesmarcados as $v) {
							$statusRelacionamento=1;
							if($v['status']==3) $statusRelacionamento=2;
							else if($v['status']==1) $statusRelacionamento=1;
							else if($v['status']==2) continue;
							else if($v['status']==0) $statusRelacionamento=1;


							$numeroDeVezesAtendidos = isset($pacientesMetricas[$v['id_paciente']]['atendimentos'])?$pacientesMetricas[$v['id_paciente']]['atendimentos']:0;
							$numeroDeVezesFaltadosEDesmarcados = isset($pacientesMetricas[$v['id_paciente']]['faltouOuDesmarcou'])?$pacientesMetricas[$v['id_paciente']]['faltouOuDesmarcou']:0;
							$numeroDeVezesFaltas = isset($pacientesMetricas[$v['id_paciente']]['faltou'])?$pacientesMetricas[$v['id_paciente']]['faltou']:0;
							$numeroDeVezesDesmarcados = isset($pacientesMetricas[$v['id_paciente']]['desmarcou'])?$pacientesMetricas[$v['id_paciente']]['desmarcou']:0;

							if($numeroDeVezesFaltadosEDesmarcados==0) $numeroDeVezesFaltadosEDesmarcados=1;
							$index=round((($numeroDeVezesAtendidos/$numeroDeVezesFaltadosEDesmarcados)+$numeroDeVezesAtendidos)*$statusRelacionamento);


							//echo $v['status']." -> (($numeroDeVezesAtendidos/$numeroDeVezesFaltadosEDesmarcados)+$numeroDeVezesAtendidos)*$statusRelacionamento = $index";die();
							//$v['nome'].="->".$index;
							$v['historico']=isset($_pacientesHistorico[$v['id_paciente']])?(int)$_pacientesHistorico[$v['id_paciente']]:0;
							$v['index']=$index;
							$v['atendidos']=$numeroDeVezesAtendidos;
							$v['faltas']=$numeroDeVezesFaltas;
							$v['desmarcados']=$numeroDeVezesDesmarcados;
							$v['ultimos']=isset($pacientesMetricas[$v['id_paciente']]['ultimos'])?$pacientesMetricas[$v['id_paciente']]['ultimos']:array();


							$v['ultimoAtendimento']=$pacientesMetricas[$v['id_paciente']]['ultimoAtendimento'];
							$v['tempoMedio']=$pacientesMetricas[$v['id_paciente']]['atendimentos']==0?0:round($pacientesMetricas[$v['id_paciente']]['tempo']/$pacientesMetricas[$v['id_paciente']]['atendimentos']);

							$pacientesDesmarcadosAux[$index][]=$v;
							$numeroTotal++;
						};

					


					// ordena pacientes em Contencao/Retorno
						$pacientesRetornoAux=array();
						foreach($pacientesRetorno as $v) {
							//$index = $statusOrdem[$v['status']];

							if($v['status']==3) $statusRelacionamento=2; // paciente pediu para retornar
							else if($v['status']==1) $statusRelacionamento=1; // nao conseguiu contato
							else if($v['status']==2) $statusRelacionamento=0.5; // paciente entrara em contato
							else if($v['status']==0) $statusRelacionamento=1; // nenhum

							$numeroDeVezesAtendidos = isset($pacientesMetricas[$v['id_paciente']]['atendimentos'])?$pacientesMetricas[$v['id_paciente']]['atendimentos']:0;
							$numeroDeVezesFaltadosEDesmarcados = isset($pacientesMetricas[$v['id_paciente']]['faltouOuDesmarcou'])?$pacientesMetricas[$v['id_paciente']]['faltouOuDesmarcou']:0;
							$numeroDeVezesFaltas = isset($pacientesMetricas[$v['id_paciente']]['faltou'])?$pacientesMetricas[$v['id_paciente']]['faltou']:0;
							$numeroDeVezesDesmarcados = isset($pacientesMetricas[$v['id_paciente']]['desmarcou'])?$pacientesMetricas[$v['id_paciente']]['desmarcou']:0;

							if($numeroDeVezesFaltadosEDesmarcados==0) $numeroDeVezesFaltadosEDesmarcados=1;

							$index=round((($numeroDeVezesAtendidos/$numeroDeVezesFaltadosEDesmarcados)+$numeroDeVezesAtendidos)*$statusRelacionamento);

							//echo $v['status']." -> (($numeroDeVezesAtendidos/$numeroDeVezesFaltadosEDesmarcados)+$numeroDeVezesAtendidos)*$statusRelacionamento = $index";die();
							//$v['nome'].="->".$index;
							$v['historico']=isset($_pacientesHistorico[$v['id_paciente']])?(int)$_pacientesHistorico[$v['id_paciente']]:0;
							$v['index']=$index;
							$v['atendidos']=$numeroDeVezesAtendidos;
							$v['faltas']=$numeroDeVezesFaltas;
							$v['desmarcados']=$numeroDeVezesDesmarcados;
							$v['ultimoAtendimento']=$pacientesMetricas[$v['id_paciente']]['ultimoAtendimento'];
							$v['tempoMedio']=$pacientesMetricas[$v['id_paciente']]['atendimentos']==0?0:round($pacientesMetricas[$v['id_paciente']]['tempo']/$pacientesMetricas[$v['id_paciente']]['atendimentos']);
							$v['ultimos']=isset($pacientesMetricas[$v['id_paciente']]['ultimos'])?$pacientesMetricas[$v['id_paciente']]['ultimos']:array();

							$pacientesRetornoAux[$index][]=$v;
							$numeroTotal++;
						}

					// ordena pacientes Desmarcados que tiveram historico adicionada na data de hoje
						$listaDosComHistoricoNoDiaAux=array();
						foreach($listaDosComHistoricoNoDia as $v) {

							
							if($v['status']==3) $statusRelacionamento=2; // paciente pediu para retornar
							else if($v['status']==1) $statusRelacionamento=1; // nao conseguiu contato
							else if($v['status']==2) $statusRelacionamento=0.5; // paciente entrara em contato
							else if($v['status']==0) $statusRelacionamento=1; // nenhum

							$numeroDeVezesAtendidos = isset($pacientesMetricas[$v['id_paciente']]['atendimentos'])?$pacientesMetricas[$v['id_paciente']]['atendimentos']:0;
							$numeroDeVezesFaltadosEDesmarcados = isset($pacientesMetricas[$v['id_paciente']]['faltouOuDesmarcou'])?$pacientesMetricas[$v['id_paciente']]['faltouOuDesmarcou']:0;
							$numeroDeVezesFaltas = isset($pacientesMetricas[$v['id_paciente']]['faltou'])?$pacientesMetricas[$v['id_paciente']]['faltou']:0;
							$numeroDeVezesDesmarcados = isset($pacientesMetricas[$v['id_paciente']]['desmarcou'])?$pacientesMetricas[$v['id_paciente']]['desmarcou']:0;

							if($numeroDeVezesFaltadosEDesmarcados==0) $numeroDeVezesFaltadosEDesmarcados=1;
							$index=round((($numeroDeVezesAtendidos/$numeroDeVezesFaltadosEDesmarcados)+$numeroDeVezesAtendidos)*$statusRelacionamento);


							//echo $v['status']." -> (($numeroDeVezesAtendidos/$numeroDeVezesFaltadosEDesmarcados)+$numeroDeVezesAtendidos)*$statusRelacionamento = $index";die();
							//$v['nome'].="->".$index;
							$v['historico']=isset($_pacientesHistorico[$v['id_paciente']])?(int)$_pacientesHistorico[$v['id_paciente']]:0;
							$v['index']=$index;
							$v['atendidos']=$numeroDeVezesAtendidos;
							$v['faltas']=$numeroDeVezesFaltas;
							$v['desmarcados']=$numeroDeVezesDesmarcados;
							$v['ultimos']=isset($pacientesMetricas[$v['id_paciente']]['ultimos'])?$pacientesMetricas[$v['id_paciente']]['ultimos']:array();


							$v['ultimoAtendimento']=$pacientesMetricas[$v['id_paciente']]['ultimoAtendimento'];
							$v['tempoMedio']=$pacientesMetricas[$v['id_paciente']]['atendimentos']==0?0:round($pacientesMetricas[$v['id_paciente']]['tempo']/$pacientesMetricas[$v['id_paciente']]['atendimentos']);

							$listaDosComHistoricoNoDiaAux[$index][]=$v;
							
						};




					// ordena listas de Contencao e Desmarcados
						krsort($pacientesDesmarcadosAux);
						krsort($pacientesRetornoAux);

					// ordena lista de pacientes que tiveram historico adicionados na data de hoje
						ksort($listaDosComHistoricoNoDiaAux);
						$listaDosComHistoricoNoDia=array();
						foreach($listaDosComHistoricoNoDiaAux as $ind=>$regs) {
							foreach($regs as $x) {
								$listaDosComHistoricoNoDia[]=$x;
							}
						}


					// unifica lista de Contencao e Desmarcado
						$listaUnificada=array('retorno'=>array(),
												'desmarcado'=>array());

						foreach($pacientesDesmarcadosAux as $ind=>$regs) {
							foreach($regs as $x) {
								$listaUnificada['desmarcado'][]=$x;
							}
						}

						foreach($pacientesRetornoAux as $ind=>$regs) {
							foreach($regs as $x) {
								$listaUnificada['retorno'][]=$x;
							}
						}


					// monta lista final, com 2 desmarcado e 1 contencao	
						$indiceDesmarcado=$indiceRetorno=0;

						$listaFinal=array();
						for($i=0;$i<=$numeroTotal;$i++) {

							
							if(isset($listaUnificada['desmarcado'][$indiceDesmarcado])) {
								$r=$listaUnificada['desmarcado'][$indiceDesmarcado];
								$r['tipo']="desmarcado";

								$listaFinal[]=$r;
								$indiceDesmarcado++;
							}
							
							if(isset($listaUnificada['desmarcado'][$indiceDesmarcado])) {
								$r=$listaUnificada['desmarcado'][$indiceDesmarcado];
								$r['tipo']="desmarcado";

								$listaFinal[]=$r;
								$indiceDesmarcado++;
							}
							if(isset($listaUnificada['retorno'][$indiceRetorno])) {
								$r=$listaUnificada['retorno'][$indiceRetorno];
								$r['tipo']="retorno";

								$listaFinal[]=$r;
								$indiceRetorno++;
							}
							
						}
					// adiciona a lista final

						$listaFinal=array_merge($listaFinal,$listaDosComHistoricoNoDia);



			return $listaFinal;


		}
	}

?>