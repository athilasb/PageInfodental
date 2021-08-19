<?php
	class WLIB {

		private $prefixo="dpa_";
		public $comandaCampos='',
				$marcasJS,
				$marcas='',
				$dir=array('comandasFotos'=>'arqs/comandas/fotos/');

		function __construct() {
			$_p=$this->prefixo;
			$sql = new Mysql();

			$_veiculos=array();
			$sql->consult($_p."veiculos_modelos","*","where lixo=0 order by titulo");
			while($x=mysqli_fetch_object($sql->mysqry)) $_veiculos[$x->id]=$x;
			$this->veiculos=$_veiculos;

			$_marcas=$_marcasJS=array();
			$sql->consult($_p."veiculos_marcas","*","where lixo=0 order by destaque desc, titulo asc");
			while($x=mysqli_fetch_object($sql->mysqry)) {
				$_marcas[$x->id]=$x;
				$_marcasJS[]=$x;
			}
			$this->marcas=$_marcas;
			$this->marcasJS=$_marcasJS;

			/*$_categorias=$_categoriasJS=array();
			$sql->consult($_p."veiculos_categorias","*","where lixo=0 order by id asc");
			while($x=mysqli_fetch_object($sql->mysqry)) {
				$_categorias[$x->id]=$x;
				$_categoriasJS[]=$x;
			}
			$this->categoriasJS=$_categoriasJS;
			$this->categorias=$_categorias;

			$_processos=array();
			$sql->consult($_p."processos","*","where lixo=0 order by conferencia asc, id asc");
			while($x=mysqli_fetch_object($sql->mysqry)) {
				$_processos[$x->id]=$x;
			}
			$this->processos=$_processos;*/

			$_servicos=array();
			$sql->consult($_p."servicos","*","where lixo=0 order by titulo asc");
			while($x=mysqli_fetch_object($sql->mysqry)) {
				$_servicos[$x->id]=$x;
			}
			$this->servicos=$_servicos;


			$this->comandaCampos=explode(",","calibragem,agendamento,data_agendamento,data_entrega,obs,checklist-pos-frente,checklist-pos-frente_esq,checklist-pos-frente_dir,checklist-pos-porta_esq,checklist-pos-porta_dir,checklist-pos-traseira_esq,checklist-pos-taseira_dir,checklist-pos-traseira,cliente_recepcao,id_usuario,id_usuario_turno,id_usuario_caixa,tapetes,objetos_valor,objetos_valor_obs,agendamento_confirmado,obsextras");
		}
		
		
		function formataDataHora($data) {

			list($_data,$_hora)=explode(" ",$data);
			list($ano,$mes,$dia)=explode("-",$_data);


			$_dias = array('Domingo','Segunda-Feira','Terça-Feira','Quarta-Feira','Quinta-Feira','Sexta-Feira','Sábado');
			$_meses = array('Janeiro','Fevereiro','Março','Abril','Maio','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro');

			$diaDaSemana=date('w',strtotime($data));
			//return $diaDaSemana;
			return $_dias[$diaDaSemana].", ".$dia." de ".$_meses[und2($mes)]." de ".$ano." - ".$_hora;
		}

		function produtos($attr=array()) {
			$_p=$this->prefixo;
			$sql = new Mysql();
			$produtos=array();

			$where=(isset($attr['where']) and !empty($attr['where']))?$attr['where']." and lixo=0 and pub=1 order by titulo":"where lixo=0 and pub=1 order by titulo";
			$sql->consult($_p."produtos","*",$where);
			if($sql->rows) {
				while($x=mysqli_fetch_object($sql->mysqry)) $produtos[$x->id]=$x;
			} 
			return $produtos;
		}

		function servicos($attr=array()) {
			$_p=$this->prefixo;
			$sql = new Mysql();
			$servicos=array();

			$where=(isset($attr['where']) and !empty($attr['where']))?$attr['where']." and lixo=0 and pub=1":"where lixo=0 and pub=1";
			$sql->consult($_p."servicos","*",$where." order by titulo");
			if($sql->rows) {
				while($x=mysqli_fetch_object($sql->mysqry)) $servicos[$x->id]=$x;
			} 
			return $servicos;
		}


		function veiculosCategorias($id_categoria=0,$attr=array()) {
			$_p=$this->prefixo;
			$sql = new Mysql();
			$categorias=array();

			if(isset($id_categoria) and is_numeric($id_categoria) and $id_categoria>0) {
				$sql->consult($_p."veiculos_categorias","*","where id=$id_categoria and lixo=0");
				if($sql->rows) $categorias=mysqli_fetch_object($sql->mysqry);
			} else {
				$sql->consult($_p."veiculos_categorias","*","where lixo=0 order by id asc");
				if($sql->rows) while($x=mysqli_fetch_object($sql->mysqry)) $categorias[$x->id]=$x;
			}

			return $categorias;
		}

		function buscaCliente($id_cliente) {
			$_p=$this->prefixo;
			$sql = new Mysql(); 
			if(isset($id_cliente) and is_numeric($id_cliente)) {
				$sql->consult($_p."clientes","*","where id=$id_cliente and lixo=0");
				if($sql->rows) return mysqli_fetch_object($sql->mysqry);
			}
			return false;
		}

		function buscaClientesID($attr) {
			$_p=$this->prefixo;
			$sql = new Mysql();

			$where="where lixo=0";

			$whereBusca='';
			if(isset($attr['nome'])) $whereBusca.="nome like '%".$attr['nome']."%' or ";
			if(isset($attr['telefone'])) $whereBusca.="telefone like '%".$attr['telefone']."%' or ";

			if(!empty($whereBusca)) {
				$whereBusca=substr($whereBusca,0,strlen($whereBusca)-4);
				$where.=" and ($whereBusca)";
			}

			$ids=array();
			$sql->consult($_p."clientes","*",$where);
			//echo $where."-> ".$sql->rows;
			if($sql->rows) {
				while($x=mysqli_fetch_object($sql->mysqry)) {
					$ids[]=$x->id;
				}
			}
			return $ids;
		}

		function buscaVeiculo($id_veiculo,$attr=array()) {
			if(isset($this->veiculos[$id_veiculo])) { 
				$objVeiculo=$this->veiculos[$id_veiculo];
				$veiculo=utf8_encode($objVeiculo->titulo);
				if(isset($this->marcas[$objVeiculo->id_marca])) {
					if(isset($attr['rtnObj'])) $veiculo=$objVeiculo;
					else $veiculo=utf8_encode($this->marcas[$objVeiculo->id_marca]->titulo." / ".$veiculo);
				}
			} else {
				$veiculo="";
			}
			return $veiculo;
		}

		function removeClienteVeiculo($id_cliente_veiculo)  {
			$_p=$this->prefixo;
			$sql = new Mysql();

			$clienteVeiculo=$this->buscaVeiculoCliente($id_cliente_veiculo,array('rtnObj'=>true));

			if(is_object($clienteVeiculo)) {
				$sql->update($_p."clientes_veiculos","lixo=1","where id=$clienteVeiculo->id");
				return true;
			}

			return false;
		}

		function buscaVeiculoCliente($id_cliente_veiculo,$attr=array()) {
			$_p=$this->prefixo;
			$sql = new Mysql();

			
			
			if(isset($attr['rtnObj'])) {
				$veiculoCliente="";
				if(is_numeric($id_cliente_veiculo)) {
					$sql->consult($_p."clientes_veiculos","*","where id=".$id_cliente_veiculo." and lixo=0");
					if($sql->rows) {
						$x=mysqli_fetch_object($sql->mysqry);
						$veiculoCliente=$x;
					}
				}
			} else {
				$veiculoCliente="Desconhecido";
				if(is_numeric($id_cliente_veiculo)) {
					$sql->consult($_p."clientes_veiculos","*","where id=".$id_cliente_veiculo." and lixo=0");
					if($sql->rows) {
						$x=mysqli_fetch_object($sql->mysqry);
						$veiculoCliente=$this->buscaVeiculo($x->id_modelo);
					}
				}
			}
			return $veiculoCliente; 
		}	

		function comanda($id_cliente_veiculo,$attr=array()) {
			$_p=$this->prefixo;
			$sql = new Mysql();

			if(isset($attr['comanda'])) {
				$data='';
				$id_comanda=$id_cliente_veiculo;

				$where="where id=$id_comanda";
				if(isset($attr['data']) and !empty($attr['data'])) {
					$where.=" and ((data>='".$attr['data']." 00:00:00' and data<='".$attr['data']." 23:59:59') or (agendamento=1 and data_agendamento>='".$attr['data']." 00:00:00' and data_agendamento<='".$attr['data']." 23:59:59'))";
				}

				$comanda=array();
				$sql->consult($_p."clientes_comandas","*,date_format(retorno_data, '%d/%m %H:%i') as retorno_dataf",$where." and lixo=0");
				if($sql->rows) return mysqli_fetch_object($sql->mysqry);
				

			} else {
				$clienteVeiculo=$cli='';

				$clienteVeiculo=$this->buscaVeiculoCliente($id_cliente_veiculo,array('rtnObj'=>true));
				if(is_object($clienteVeiculo)) {
					$cli=$this->buscaCliente($clienteVeiculo->id_cliente,array('rtnObj'=>true));
				}	

				$data='';
				if(isset($attr['data']) and !empty($attr['data'])) $data=$attr['data'];

				$comanda=array();
				if(!empty($data) and is_object($cli)) {
					//$where="where id_cliente_veiculo=$clienteVeiculo->id and id_cliente=$cli->id and ((data>='".$data." 00:00:00' and data<='".$data." 23:59:59' and agendamento=0) or (data_agendamento>='".$data." 00:00:00' and data_agendamento<='".$data." 23:59:59' and agendamento=1)) and fechada=0 and lixo=0";

					$where="where id_cliente_veiculo=$clienteVeiculo->id and id_cliente=$cli->id and ((data>='".$data." 00:00:00' and data<='".$data." 23:59:59' and agendamento=0) or (data_agendamento > NOW() - INTERVAL 7 DAY and agendamento=1)) and fechada=0 and lixo=0";
					$sql->consult($_p."clientes_comandas","*,date_format(data_entrega, '%Y-%m-%dT%H:%i') as data_entrega,
																date_format(data_agendamento, '%Y-%m-%dT%H:%i') as data_agendamento,
																date_format(retorno_data, '%d/%m %H:%i') as retorno_dataf",$where);
					if($sql->rows) {
						return mysqli_fetch_object($sql->mysqry);
					}
				} 
			}
			return;
		}

		function comandaServicosProcessos($id_servico) {
			$_p=$this->prefixo;
			$sql = new Mysql();
			$rtn=array(); 
			if(isset($this->servicos[$id_servico])) { 
				$s=$this->servicos[$id_servico];
				if(!empty($s->processos)) {
					$aux=explode(",",$s->processos);
					foreach($aux as $v) { 
						if(!empty($v) and is_numeric($v)) {
							if($this->processos[$v]) $rtn[]=$this->processos[$v];
						}
					}
				}
			}
			return $rtn;
		}

		function comandaServicos($id_comanda) {
			$_p=$this->prefixo;
			$sql = new Mysql();
			$rtn=array();

			$comanda=$this->comanda($id_comanda,array('comanda'=>true));
			if(is_object($comanda)) {
				$prtn=$rtn=array();
				$sql->consult($_p."clientes_comandas_servicos","*","where id_comanda=$comanda->id and servico=1 and lixo=0");
				if($sql->rows) {
					while($x=mysqli_fetch_object($sql->mysqry)) {
						$prtn[]=$x;
					}
				}

				foreach($prtn as $x) {
					$processos=$this->comandaServicosProcessos($x->id_servico);
					$rtn[]=array('servicos'=>$x,'processos'=>$processos);
				}
			}
			return $rtn;
		}

		function comandaServicosData($id_comanda) {
			$_p=$this->prefixo;
			$sql = new Mysql();
			$rtn=array();

			$comanda=$this->comanda($id_comanda,array('comanda'=>true));
			if(is_object($comanda)) {
				$prtn=$rtn=array();
				$sql->consult($_p."clientes_comandas_servicos","*,date_format(data,'%d/%m/%y') as dataf","where id_comanda=$comanda->id and servico=1 and lixo=0");
				if($sql->rows) {
					while($x=mysqli_fetch_object($sql->mysqry)) {
						$prtn[$x->dataf][]=$x;
					}
				}

				/*foreach($prtn as $x) {
					$processos=$this->comandaServicosProcessos($x->id_servico);
					$rtn[]=array('servicos'=>$x,'processos'=>$processos);
				}*/
			}
			return $prtn;
		}

		function comandaProdutos($id_comanda) {
			$_p=$this->prefixo;
			$sql = new Mysql();
			$rtn=array();

			$comanda=$this->comanda($id_comanda,array('comanda'=>true));
			if(is_object($comanda)) {
				$prtn=$rtn=array();
				$sql->consult($_p."clientes_comandas_servicos","*","where id_comanda=$comanda->id and servico=0 and lixo=0");
				if($sql->rows) {
					while($x=mysqli_fetch_object($sql->mysqry)) {
						$rtn[]=array('produto'=>$x);
					}
				}
			}
			return $rtn;
		}

		function comandaConcluida($id_comanda) {
			$_p=$this->prefixo;
			$sql = new Mysql();
			$sql2 = new Mysql();
			$sql3 = new Mysql();

			// retorna todos os processos dos servicos desta comanda possui
			$processosDosServicos=array();
			$sql2->consult($_p."clientes_comandas_servicos","*","where id_comanda=$id_comanda and servico=1 and lixo=0");
			if($sql2->rows) {
				// laco de todos os servicos da comanda
				while($y=mysqli_fetch_object($sql2->mysqry)) {
					if(!isset($processosDosServicos[$y->id_servico])) $processosDosServicos[$y->id_servico]=array();
					$processosDosServicos[$y->id_servico]['id_comanda_servico']=$y->id;
					// retorna todos os processos do servico da vez do laco
					$sql3->consult($_p."servicos","*","where id=$y->id_servico");
					while($z=mysqli_fetch_object($sql3->mysqry)) {
						if(!empty($z->processos)) {
							$aux=explode(",",$z->processos);
							//var_dump($aux);
							// lista os processos do servico
							foreach($aux as $w) {
								if(!empty($w) and is_numeric($w) and $w!=6) {
									$processosDosServicos[$y->id_servico]['processos'][]=array('id'=>$w,
																								'carro'=>$this->processos[$w]->carro);
								}
							}
						}
					}
				}
			}
			// se nao possui nenhum processo nos servicos
			if(count($processosDosServicos)==0) {
				return false;
			}
			else {
				$estagio=count($processosDosServicos);
				foreach($processosDosServicos as $arrayIdServico=>$arrayProcessos) {
					if(!isset($concluido[$arrayIdServico])) $concluido[$arrayIdServico]=1;
					

					
					if(isset($arrayProcessos['processos']) and count($arrayProcessos['processos'])>0) {

						foreach($arrayProcessos['processos'] as $arrayProcesso) {
							$where="where id_comanda=$id_comanda and id_processo=".$arrayProcesso['id'];//." and id_comanda_servico=".$arrayProcessos['id_comanda_servico'];

							// lista cada processo de cada servico da comanda
							$sql2->consult($_p."clientes_comandas_servicos_processos","*",$where);
							//echo $where."-".$sql2->rows."\r\n";

							if($sql2->rows) {

								$cp=mysqli_fetch_object($sql2->mysqry);

								if($cp->dti=="0000-00-00 00:00:00") {echo "erro"; return false;} 
								/*	
								echo "entrou";
								verifica se todos foram concluidos
								if($servicosConferencia==0) $servicosConferencia=1;

								if($arrayProcesso['carro']==1) {
									//echo $cp->frente_esq." ".$cp->frente_dir." ".$cp->traseira_dir." ".$cp->traseira_esq." concluido: $concluido[$arrayIdServico]\r\n";
									if($cp->frente_esq=="00:00:00") {
										return false;
										$concluido[$arrayIdServico]=0;
									}
									if($cp->frente_dir=="00:00:00") {
										return false;
										$concluido[$arrayIdServico]=0;
									}
									if($cp->traseira_esq=="00:00:00") {
										return false;
										$concluido[$arrayIdServico]=0;
									}
									if($cp->traseira_dir=="00:00:00") {
										return false;
										$concluido[$arrayIdServico]=0;
									}
								} else {
									echo $cp->dti;
								}
								*/
							} else return false;

							//echo $arrayProcessos['id_comanda_servico']."-> concluido: ".$concluido[$arrayIdServico]."-\r\n";
						}
						if($concluido[$arrayIdServico]==0) return false;
					}


					if($concluido[$arrayIdServico]==0) return false;
				}
				return true;
			}
		}

		function comandaConferencia($id_comanda) {
			$_p=$this->prefixo;
			$sql = new Mysql();
			$sql2 = new Mysql();
			$sql3 = new Mysql();

			// retorna todos os processos dos servicos desta comanda possui
			$processosDosServicos=array();
			$sql2->consult($_p."clientes_comandas_servicos","*","where id_comanda=$id_comanda and servico=1 and lixo=0");
			if($sql2->rows) {
				// laco de todos os servicos da comanda
				while($y=mysqli_fetch_object($sql2->mysqry)) {
					if(!isset($processosDosServicos[$y->id_servico])) $processosDosServicos[$y->id_servico]=array();
					$processosDosServicos[$y->id_servico]['id_comanda_servico']=$y->id;
					// retorna todos os processos do servico da vez do laco
					$sql3->consult($_p."servicos","*","where id=$y->id_servico");
					while($z=mysqli_fetch_object($sql3->mysqry)) {
						if(!empty($z->processos)) {
							$aux=explode(",",$z->processos);
							//var_dump($aux);
							// lista os processos do servico
							foreach($aux as $w) {
								if(!empty($w) and is_numeric($w) and $this->processos[$w]->conferencia==0) {
									$processosDosServicos[$y->id_servico]['processos'][]=array('id'=>$w,
																								'carro'=>$this->processos[$w]->carro);
								}
							}
						}
					}
				}
			}
			// se nao possui nenhum processo nos servicos
			if(count($processosDosServicos)==0) {
				return false;
			}
			else {
				$estagio=count($processosDosServicos);
				foreach($processosDosServicos as $arrayIdServico=>$arrayProcessos) {
					if(!isset($concluido[$arrayIdServico])) $concluido[$arrayIdServico]=1;
					

					
					if(isset($arrayProcessos['processos']) and count($arrayProcessos['processos'])>0) {

						$todosProcessosConferidos=true;
						foreach($arrayProcessos['processos'] as $arrayProcesso) {
							$where="where id_comanda=$id_comanda and id_processo=".$arrayProcesso['id']." and id_comanda_servico=".$arrayProcessos['id_comanda_servico'];

							// lista cada processo de cada servico da comanda
							$sql2->consult($_p."clientes_comandas_servicos_processos","*",$where);
							//echo $where."-".$sql2->rows."\r\n";

							if($sql2->rows) {
							//	echo "entrou";
								// verifica se todos foram concluidos
								$cp=mysqli_fetch_object($sql2->mysqry);
								//if($servicosConferencia==0) $servicosConferencia=1;
								if($cp->conferencia==0) $todosProcessosConferidos=false;

								if($arrayProcesso['carro']==1) {
									//echo $cp->frente_esq." ".$cp->frente_dir." ".$cp->traseira_dir." ".$cp->traseira_esq." concluido: $concluido[$arrayIdServico]\r\n";
									if($cp->frente_esq=="00:00:00") {
										return false;
										$concluido[$arrayIdServico]=0;
									}
									if($cp->frente_dir=="00:00:00") {
										return false;
										$concluido[$arrayIdServico]=0;
									}
									if($cp->traseira_esq=="00:00:00") {
										return false;
										$concluido[$arrayIdServico]=0;
									}
									if($cp->traseira_dir=="00:00:00") {
										return false;
										$concluido[$arrayIdServico]=0;
									}
								} else {
									if($cp->tempo=="00:00:00") return false;//$concluido[$arrayIdServico]=0;
								}
							} else $concluido[$arrayIdServico]=0;

							//echo $arrayProcessos['id_comanda_servico']."-> concluido: ".$concluido[$arrayIdServico]."-\r\n";
						}
						if($todosProcessosConferidos==true) return false;
					}


					if($concluido[$arrayIdServico]==0) return false;
				}
				return true;
			}
		}	

		function estacionamentoValor($id_comanda) {

			$_p=$this->prefixo;
			$sql = new Mysql();

			$comanda=$this->comanda($id_comanda,array('comanda'=>true));
			if(is_object($comanda) and $comanda->tipo=="estacionamento") {
				$sql->consult($_p."parametros_estacionamento","*","");
				$est=mysqli_fetch_object($sql->mysqry);

				$total=0;
				$now = new DateTime();
				$then = new DateTime($comanda->data_entrada);
				$diff = $now->diff($then);

				if($diff->h==0) {
					if($diff->i>=20) {
						$total+=$est->valor_hora;
					}
				} else {
					$total=$est->valor_hora; 
					if($diff->h>1)  $total+=($est->valor_adicional)*($diff->h-1);
					if($diff->i>0) {
						$total+=$est->valor_adicional;
					}
				}


				return array('valor'=>$total,'tempo'=>$diff->format('%hh %im'));
			}
		}

		function comandaStatus($id_comanda) {


			$_p=$this->prefixo;
			$sql = new Mysql();

			$comanda=$this->comanda($id_comanda,array('comanda'=>true));

			if(is_object($comanda)) {
				if($comanda->tipo=="lavagem") {
					if($comanda->fechada==1) {
						$statusPosicao='4';
						$status='Concluído';
						$statusCor='green';
					} else if($comanda->checklist>0) {
						$statusPosicao='3';
						$status="Retirar";
						$statusCor="#666";
					} else {
						$servicos=$this->comandaServicos($comanda->id);
						$iniciou=0;
						$processosFinalizado=1;
						foreach($servicos as $y) {
							foreach($y['processos'] as $p) {
							//	if($comanda->id==1058) echo $p->conferencia."--";
								if($p->conferencia==0) {
									$whereProcessos="where id_comanda=$comanda->id and id_processo=$p->id and id_comanda_servico=".$y['servicos']->id;
									$sql->consult($_p."clientes_comandas_servicos_processos","*",$whereProcessos." order by conferencia asc");

									if($sql->rows) {
										$iniciou=1;
										$pConf=mysqli_fetch_object($sql->mysqry);
										if($pConf->conferencia==0) $processosFinalizado=0;
									} else {
										$processosFinalizado=0;
									}
									
								} 
							} 
						}
						
							if($iniciou==0) {
								$statusPosicao='1';
								$status="Não iniciado";
								$statusCor="#cc3300";
							} else if($processosFinalizado==1) {
								$statusPosicao='3';
								$status="Retirar";
								$statusCor="#666";
							} else if($iniciou==1) {
								$statusPosicao='2';
								$status="Processo";
								$statusCor="orange";
							}
					}
				} else if($comanda->tipo=="estacionamento") {
					// a retirar e concluido
					if($comanda->fechada==1) {
						$statusPosicao='4';
						$status='Concluído';
						$statusCor='green';
					} else {
						$statusPosicao='3';
						$status="Retirar";
						$statusCor="#666";
						
					}
				}


				return array('status'=>$status,'statusCor'=>$statusCor,'statusPosicao'=>$statusPosicao);
			} else {
				$this->erro="Comanda não realizada";
				return false;
			}
		}

		function colaborador($id_usuario) {

			$_p=$this->prefixo;
			$sql = new Mysql();

			$sql->consult($_p."colaboradores","*","where id=$id_usuario");
			if($sql->rows) {
				return mysqli_fetch_object($sql->mysqry);
			}

			return ;
		}
		function estacionamento($dataEntrada,$dataSaida,$veiculo) {
	
			$dtEntrada=strtotime($dataEntrada);
			$dtSaida=strtotime($dataSaida);
			$totalDeHoras=floor(($dtSaida-$dtEntrada)/60/60);

			$dtF = new \DateTime('@0');
		    $dtT = new \DateTime("@".($dtSaida-$dtEntrada));

			$dtMinutos = $dtF->diff($dtT)->format('%I');

			if($dtMinutos>0) {
				$totalDeHoras++;
			}

			if($veiculo=="moto") {
				$diaria=20;
				$diaria2=10;
				$primeiroAdicional=5;
				$segundoAdicional=10;
			} else {
				$diaria=20;
				$diaria2=20;
				$primeiroAdicional=10;
				$segundoAdicional=15;
			}

			$_dias=floor($totalDeHoras/24);
			$_diasHoras=$_dias*24;
			$_diasHoras=$totalDeHoras-$_diasHoras;
			$valor=$diaria;
			$valor+=($_dias-1)*$diaria2;

			if($_diasHoras>=1) {
				if($veiculo=="moto") {
					if($_diasHoras>=0 and $_diasHoras<=12) $valor+=$primeiroAdicional;
					else if($_diasHoras>=13) $valor+=$diaria2;
				} else {
					if($_diasHoras>=0 and $_diasHoras<=6) $valor+=$primeiroAdicional;
					else if($_diasHoras>=7 and $_diasHoras<=12) $valor+=$segundoAdicional;
					else if($_diasHoras>=13) $valor+=$diaria2;
				}
			}
			return $valor;
		}
		function comandaItens($id_comanda,$attr=array()) {

			$_p=$this->prefixo;
			$sql = new Mysql();

			$comanda = $this->comanda($id_comanda,array('comanda'=>true));

			$cliente='';
			if(is_object($comanda)) {
				$sql->consult($_p."clientes","*","where id=$comanda->id_cliente");
				$cliente=mysqli_fetch_object($sql->mysqry);

				$clienteVeiculo='';
				$sql->consult($_p."clientes_veiculos","*","where id='".$comanda->id_cliente_veiculo."'");
				if($sql->rows) $clienteVeiculo=mysqli_fetch_object($sql->mysqry);
			}
			$rtn = array();


			// verifica se cliente e mensalista
			if(is_object($cliente) and $cliente->tipo=="mensalista") {
				$rtn[]=array("titulo"=>"MENSALISTA","quantidade"=>'',"valor"=>0,"tipo"=>"estacionamento");
			} else {
				$valorEstacionamento=$this->estacionamento($comanda->data,date('Y-m-d H:i:s'),$clienteVeiculo->tipo);
				/*if($comanda->combinado==1 && $comanda->combinado_valor>0) {

					$_titulo="<span class=\"iconify\" data-icon=\"ant-design:lock-filled\" data-width=\"20\" data-height=\"20\"></span> VALOR FIXO COMBINADO</span>";
					$valorEstacionamento=$valorEstacionamento>$comanda->combinado_valor?$valorEstacionamento:$comanda->combinado_valor;
					$rtn[]=array("titulo"=>$_titulo,"quantidade"=>'',"valor"=>$valorEstacionamento,"tipo"=>"estacionamento");
				} else {*/
					
					$rtn[]=array("titulo"=>"ESTACIONAMENTO (".strtoupperWLIB($clienteVeiculo->tipo).")","quantidade"=>'',"valor"=>$valorEstacionamento,"tipo"=>"estacionamento");
				//}
			}

			if($comanda->lavajato==1) {
				$lavajatoTitulo='LAVAJATO';
				/*if($comanda->id_lavajato>0) {
					$sql->consult($_p."lavajato", "*","where id=$comanda->id_lavajato");
					if($sql->rows) {
						$x=mysqli_fetch_object($sql->mysqry);
						$lavajatoTitulo=($x->titulo);
					}
				} */
				$rtn[]=array('titulo'=>$lavajatoTitulo,'quantidade'=>'','valor'=>$comanda->lavajato_valor,'tipo'=>'lavajato');
			}


			if(!empty($comanda->ps)) {
				$obj = json_decode($comanda->ps);
				foreach($obj as $v) {
					$rtn[]=array('titulo'=>($v->titulo),'quantidade'=>$v->quantidade,'valor'=>$v->valor*$v->quantidade,'tipo'=>$v->tipo);
				}
			}

			

			if(isset($attr['valorTotal']) and $attr['valorTotal']==true) {
				$valorTotal=0;
				foreach ($rtn as $key => $value) {
					$valorTotal+=$value['valor'];
				}
				return $valorTotal;
			} else {
				return $rtn;
			}
		}
 	}
?>