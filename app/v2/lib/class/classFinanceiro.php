<?php
	class Financeiro {

		private 
			$prefixo, 
			$usr;

		function __construct($attr) {
			ini_set("memory_limit","1024M");
			$this->prefixo=$attr['prefixo'];
			$this->usr=(isset($attr['usr']) and is_object($attr['usr']))?$attr['usr']:"";
		}
		
		// retorna quem é o pagante ou credor de acordo com o id_origem do fluxo
		function getPaganteCredor($id_fluxo){

			$_p=$this->prefixo;
			$sql =new Mysql();

			$rtn='';

			if(isset($id_fluxo) and is_numeric($id_fluxo)) {
				$sql->consult($_p."financeiro_fluxo","*","where id=$id_fluxo");
				if($sql->rows) {
					$fluxo=mysqli_fetch_object($sql->mysqry);
					//echo $fluxo->tipo."<BR>";
					if($fluxo->tipo=="fornecedor") {
						$sql->consult($_p."fornecedores","id,tipo_pessoa,razao_social,nome","where id=$fluxo->id_fornecedor");
						if($sql->rows) {
							$x=mysqli_fetch_object($sql->mysqry);
							$rtn=utf8_encode($x->tipo_pessoa=="pj"?$x->razao_social:$x->nome);
						} else {
							$rtn=utf8_encode($fluxo->descricao);
						}
					}
					else if($fluxo->tipo=="colaborador" or $fluxo->tipo=="socio_retirada") {
						$sql->consult($_p."colaboradores","id,nome","where id=$fluxo->id_colaborador");
						if($sql->rows) {
							$x=mysqli_fetch_object($sql->mysqry);
							$rtn=utf8_encode($x->nome);
						}
					}
					else if($fluxo->tipo=="unidade") {
						$sql->consult($_p."unidades","id,titulo","where id=$fluxo->id_unidade");
						if($sql->rows) {
							$x=mysqli_fetch_object($sql->mysqry);
							$rtn=utf8_encode($x->titulo);
						}
					}
					else if($fluxo->tipo=="caixa_delivery") {
						$rtn="<a href=\"pg_delivery_caixas.php?id_caixa=$fluxo->id_caixadelivery&form=caixa\" target=\"_blank\">Caixa Delivery #".$fluxo->id_caixadelivery."</a>";
					}
					else if($fluxo->tipo=="caixa_loja") {
						$rtn="<a href=\"pg_financeiro_caixa.php?id_caixa=$fluxo->id_caixa&form=caixa\" target=\"_blank\">Caixa Loja #".$fluxo->id_caixa."</a>";
					}
				}
			}

			return $rtn;
		}

		// retorna quem é o pagante ou credor de acordo com o id_origem do fluxo
		function getPaganteCredor2($id_fluxo){

			$_p=$this->prefixo;
			$sql =new Mysql();

			$rtn='';

			if(isset($id_fluxo) and is_numeric($id_fluxo)) {
				$sql->consult($_p."financeiro_fluxo","*","where id=$id_fluxo");
				if($sql->rows) {
					$fluxo=mysqli_fetch_object($sql->mysqry);
					//echo $fluxo->tipo."<BR>";
					if($fluxo->tipo=="fornecedor") {
						$sql->consult($_p."fornecedores","id,tipo_pessoa,razao_social,nome","where id=$fluxo->id_fornecedor");
						if($sql->rows) {
							$x=mysqli_fetch_object($sql->mysqry);
							$rtn=utf8_encode($x->tipo_pessoa=="pj"?$x->razao_social:$x->nome);
						} else {
							$rtn=utf8_encode($fluxo->descricao);
						}
					}
					else if($fluxo->tipo=="colaborador" or $fluxo->tipo=="socio_retirada") {
						$sql->consult($_p."colaboradores","id,nome","where id=$fluxo->id_colaborador");
						if($sql->rows) {
							$x=mysqli_fetch_object($sql->mysqry);
							$rtn=utf8_encode($x->nome);
						}
					}
					else if($fluxo->tipo=="unidade") {
						$sql->consult($_p."unidades","id,titulo","where id=$fluxo->id_unidade");
						if($sql->rows) {
							$x=mysqli_fetch_object($sql->mysqry);
							$rtn=utf8_encode($x->titulo);
						}
					}
					else if($fluxo->tipo=="caixa_delivery") {
						$rtn="CAIXA DELIVERY=>pg_delivery_caixas.php?id_caixa=$fluxo->id_caixadelivery&form=caixa";
					}
					else if($fluxo->tipo=="caixa_loja") {
						$rtn="CAIXA LOJA=>pg_financeiro_caixa.php?id_caixa=$fluxo->id_caixa&form=caixa";
					}
				}
			}

			return $rtn;
		}

	
	

		// variacao de conta
		function variacaoDeConta($attr) {
			$_p=$this->prefixo;
			$sql =new Mysql();

			$variacoes=array('inicio'=>0,
							'fim'=>0,
							'inicio'=>0,
							'maximo'=>0);

			$anoInicio=(isset($attr['anoInicio']) and is_numeric($attr['anoInicio']))?$attr['anoInicio']:0;
			$mesInicio=(isset($attr['mesInicio']) and is_numeric($attr['mesInicio']))?$attr['mesInicio']:0;

			$anoFim=(isset($attr['anoFim']) and is_numeric($attr['anoFim']))?$attr['anoFim']:0;
			$mesFim=(isset($attr['mesFim']) and is_numeric($attr['mesFim']))?$attr['mesFim']:0;

			$unidades=(isset($attr['unidades']) and is_array($attr['unidades']) and count($attr['unidades'])>0)?$attr['unidades']:'';

			//var_dump($attr['unidades']);die();
			if(!empty($unidades) and $anoInicio>0 and $mesInicio>0 and $anoFim>0 and $mesInicio>0) {


				$bancosEContas=array(-1);
				$sql->consult($_p."financeiro_bancosecontas","*","where id_unidade IN (".implode(",",$unidades).") and lixo=0");
				while($x=mysqli_fetch_object($sql->mysqry)) {
					$bancosEContas[]=$x->id;
				}

				$dataInicio="$anoInicio-$mesInicio-01";
				$dataFim=date('Y-m-t',strtotime("$anoFim-$mesFim-01"));


				// saldo inicio do mes
				$where="WHERE data_extrato<='$dataInicio' and id_conta IN (".implode(",",$bancosEContas).") and lixo=0";
				$sql->consult($_p."financeiro_extrato","sum(valor) as inicioTotal",$where);
				//echo $sql->rows;
				$t=mysqli_fetch_object($sql->mysqry);
				$variacoes['inicio']=$t->inicioTotal;


				// saldo final do mes
				$where="WHERE data_extrato<='$dataFim' and id_conta IN (".implode(",",$bancosEContas).") and lixo=0";
				
				$sql->consult($_p."financeiro_extrato","sum(valor) as fimTotal",$where);
				$t=mysqli_fetch_object($sql->mysqry);
				$variacoes['fim']=$t->fimTotal;

				$saldo=$variacoes['inicio'];
				$saldoMinimo=$saldo;
				$saldoMaximo=0;
				$where="WHERE data_extrato>='$dataInicio' and data_extrato<='$dataFim' and id_conta IN (".implode(",",$bancosEContas).")";
				$sql->consult($_p."financeiro_extrato","valor,data_extrato",$where);
				$registros=array();
				while($x=mysqli_fetch_object($sql->mysqry)) {
					$registros[$x->data_extrato][]=$x->valor;
				}

				foreach($registros as $data=>$regs) {
					foreach($regs as $val) {
						$saldo+=$val;
					}
					if($saldo<$saldoMinimo) $saldoMinimo=$saldo;

					if($saldo>$saldoMaximo) $saldoMaximo=$saldo;

				}

				$variacoes['minimo']=$saldoMinimo;
				$variacoes['maximo']=$saldoMaximo;
			}

			return $variacoes;
		}

		// saldo da conta atual separado por unidade
		function saldoContaPorUnidade($attr) {
			$_p=$this->prefixo;
			$sql =new Mysql();

			$saldo=array();
			$unidades=(isset($attr['unidades']) and is_array($attr['unidades']) and count($attr['unidades'])>0)?$attr['unidades']:'';

			if(!empty($unidades)) {


				foreach($unidades as $id_unidade) {
					$bancosEContas=array(-1);
					$sql->consult($_p."financeiro_bancosecontas","*","where id_unidade = $id_unidade and lixo=0");
					while($x=mysqli_fetch_object($sql->mysqry)) {
						$bancosEContas[]=$x->id;
					}
					
					$where="WHERE id_conta IN (".implode(",",$bancosEContas).") and lixo=0";
					$sql->consult($_p."financeiro_extrato","sum(valor) as saldo",$where);
					$t=mysqli_fetch_object($sql->mysqry);
					$saldo[$id_unidade]=$t->saldo;

				}

			}

			return $saldo;

		}

		// retorna a origem do fluxo financeiro
		function fluxoOrigem($tabela,$secundario=false) {
			$_p=$this->prefixo;
			$sql =new Mysql();

			$where="where tabela='".str_replace($_p,"",$tabela)."'";
			if($secundario==true) $where.=" order by id desc";
			$sql->consult($_p."financeiro_fluxo_origens","*",$where);
			if($sql->rows) return mysqli_fetch_object($sql->mysqry);
			else return '';
		}

		// cria conciliacao de um fluxo
		function criarExtratoEConciliar($attr) {
			$_p=$this->prefixo;
			$usr=$this->usr;
			$sql =new Mysql();

			$fluxo=$unidade='';
			if(isset($attr['id_fluxo']) and is_numeric($attr['id_fluxo'])) {
				$sql->consult($_p."financeiro_fluxo","*","where id='".$attr['id_fluxo']."' and lixo=0");
				if($sql->rows) {
					$fluxo=mysqli_fetch_object($sql->mysqry);
					$sql->consult($_p."unidades","*","where id=$fluxo->id_unidade and lixo=0");
					if($sql->rows) {
						$unidade=mysqli_fetch_object($sql->mysqry);
					}
				}
			}
			if(is_object($unidade) and isset($attr['id_conta']) and is_numeric($attr['id_conta'])) {
				$sql->consult($_p."financeiro_bancosecontas","*","where id='".$attr['id_conta']."' and id_unidade=$unidade->id and lixo=0");
				if($sql->rows) {
					$conta=mysqli_fetch_object($sql->mysqry);
				}
			}

			
			if(is_object($fluxo)) {
				if(is_object($unidade)) {
					if(is_object($conta)) {
						$extratos=$this->fluxoConciliado($conta->id);
					
						if(is_array($extratos)) {
							$this->erro='Este fluxo já foi conciliado!';
						} else {
							$extratoTipo=$fluxo->valor>0?'DEP':'DEBIT';

							$vsql="data_extrato='$fluxo->data_efetivado',
									tipo='".$extratoTipo."',
									id_unidade=$fluxo->id_unidade,
									id_conta=$conta->id,
									valor='".$fluxo->valor."',
									descricao='FLUXO #$fluxo->id',
									id_usuario=$usr->id,
									id_fluxo_criacao=$fluxo->id
									";

							$sql->consult($_p."financeiro_extrato","*","where id_fluxo_criacao=$fluxo->id and lixo=0");
							if($sql->rows) {
								$extrato=mysqli_fetch_object($sql->mysqry);
							} else {
								$sql->add($_p."financeiro_extrato",$vsql);
								$id_extrato=$sql->ulid;
								$sql->consult($_p."financeiro_extrato","*","where id=$id_extrato and lixo=0");
								if($sql->rows) {
									$extrato=mysqli_fetch_object($sql->mysqry);
								}
							}


							if(is_object($extrato)) {
								$sql->consult($_p."financeiro_conciliacoes","*","where id_extrato=$extrato->id and id_fluxo=$fluxo->id and lixo=0");
								if($sql->rows==0) {
									$sql->add($_p."financeiro_conciliacoes","data=now(),
																				id_extrato=$extrato->id,
																				id_fluxo=$fluxo->id,
																				lixo=0,
																				multiplo=0,
																				valor=$fluxo->valor");
									$id_conciliacao=$sql->ulid;
									$sql->update($_p."financeiro_fluxo","id_conciliacao=$id_conciliacao","where id=$fluxo->id");
									return true;
								}
							} else {
								$this->erro="Extrato não criado!";
							}
						}
					} else {
						$this->erro="Conta/Banco não encontrada!";
					}
				} else {
					$this->erro="Unidade não encontrada";
				}
			} else {
				$this->erro="Conta não encontrada";
				return false;
			}
		}

		// retorna os extratos conciliadas a movimentacao
		function fluxoConciliado($id_fluxo) {
			
			$usr=$this->usr;
			$_p=$this->prefixo;
			
			$sql = new Mysql();
			$sql2=new Mysql();
			
			$erro='';
			$extratos='';
			$sql->consult($_p."financeiro_conciliacoes","*,date_format(data,'%d/%m/%Y') as dataf","where id_fluxo='".$id_fluxo."' and id_transferencia=0 and lixo=0");
			
			if($sql->rows) {
				$extratos=array();
				while($conc=mysqli_fetch_object($sql->mysqry)) {
				
					$sql2->consult($_p."financeiro_extrato","*,date_format(data_extrato,'%d/%m/%Y') as dataf","where id='".$conc->id_extrato."' and lixo=0");
					if($sql2->rows) {
						$extratos[]=mysqli_fetch_object($sql2->mysqry);
					} else {
						$sql2->update($_p."financeiro_conciliacoes","lixo=1","where id_fluxo='".$id_fluxo."'");
					}
				}
				
			}
			return $extratos;
		}

		// retorna fluxos conciliadas a esta movimentacao
		function extratoConciliadoFluxo($id_extrato) {
			$usr=$this->usr;
			$_p=$this->prefixo;
			$sql = new Mysql();
			$sql2 = new Mysql();
			$erro='';
			$fluxos='';
			
			$where="where id_extrato='".$id_extrato."' and id_transferencia=0 and lixo=0";
			$sql->consult($_p."financeiro_conciliacoes","*,date_format(data,'%d/%m/%Y') as dataf",$where);
			
			if($sql->rows) {
				$fluxos=array();
				while($conc=mysqli_fetch_object($sql->mysqry)) {
					$sql2->consult($_p."financeiro_fluxo","*,date_format(data,'%d/%m/%Y') as dataf","where id='".$conc->id_fluxo."' and lixo=0");
					if($sql2->rows) {
						while($x=mysqli_fetch_object($sql2->mysqry)) {
							$fluxos[]=$x;
						}
					} else {
						$sql2->update($_p."financeiro_conciliacoes","lixo=1","where id_extrato='".$id_extrato."' and id_transferencia=0");
					}
				}
			} 
			return $fluxos;
		}

		// retorna transferencias conciliadas a esta movimentacao
		function extratoConciliadoTransferencia($id_extrato) {
			
			$usr=$this->usr;
			$_p=$this->prefixo;
			$sql = new Mysql();
			$sql2 = new Mysql();
			$erro='';
			$transferencia='';
			
			$sql->consult($_p."financeiro_conciliacoes","*,date_format(data,'%d/%m/%Y') as dataf","where (id_extrato='".$id_extrato."' or id_transferencia='".$id_extrato."') and id_fluxo=0 and lixo=0");
			
			if($sql->rows) {
				$transferencia=array();
				while($conc=mysqli_fetch_object($sql->mysqry)) {

					if($conc->id_extrato==$id_extrato) {
						$id_ref="id_transferencia";
					} else {
						$id_ref="id_extrato";
					} 
					//echo $id_ref;die();
					$sql2->consult($_p."financeiro_extrato","*,date_format(data_extrato,'%d/%m/%Y') as dataf","where id='".$conc->$id_ref."' and lixo=0");
					
					if($sql2->rows) {
						$transferencia=array();
						$transferencia[]=mysqli_fetch_object($sql2->mysqry);
					} else {
						$sql2->update($_p."financeiro_conciliacoes","lixo=1","where (id_extrato='".$id_extrato."' or id_transferencia='".$id_extrato."') and id_fluxo=0 and lixo=0");
					}
				}
			} 
			return $transferencia;
		}

		// concilia um fluxo com movimento
		function contaConciliar($post) {
			$usr=$this->usr;
			$_p=$this->prefixo;
			$adm=$this->adm;
			$sql = new Mysql();
			$erro='';
			
			$juros=isset($_POST['juros'])?valor($_POST['juros']):0;
			$multa=isset($_POST['multa'])?valor($_POST['multa']):0;
			$desconto=isset($_POST['desconto'])?valor($_POST['desconto']):0;
			
			$sql->consult($_p."financeiro_fluxo","*","where id='".$post['conciliar']."' and lixo=0");
			if($sql->rows==0) {
				$erro='Fluxo não encontrada!';
			} else {
				$fluxo=mysqli_fetch_object($sql->mysqry);
				$_dinheiro=false;
				$_credito=false;
				$sql->consult($_p."parametros_formasdepagamento","*","where id=$fluxo->id_formapagamento");
				
				if($sql->rows) {
					$formaDePagamento=mysqli_fetch_object($sql->mysqry);
					// se for dinheiro
					if($formaDePagamento->tipo=="dinheiro") $_dinheiro=true;
					// se for cartao ou online -> conciliacao acumulativa
					else if($formaDePagamento->tipo=="credito" or $formaDePagamento->tipo=="debito" or $formaDePagamento->tipo=="online") $_credito=true;
				}
				
				$fluxoConciliado=$this->fluxoConciliado($fluxo->id);
				if(is_array($fluxoConciliado)) {
					$erro='Este Fluxo já foi conciliado!';
				} else { 


					// se for conciliacao de acerto entre unidades (balanco): inicio
					/*if($fluxo->acerto==1) {


						$fluxoOrigem=$this->fluxoOrigem($_p."financeiro_fluxo");

						// cria fluxo Conta a Receber se for um pagamento
						if($fluxo->valor<0) {

							$valorAcerto=$fluxo->valor>0?$fluxo->valor:$fluxo->valor*-1;

							// gera fluxo a receber
							$vsql="valor='".$valorAcerto."',
									id_formapagamento='".$fluxo->id_formapagamento."',
									data_vencimento='$fluxo->data_vencimento',
									id_origem='".$fluxoOrigem->id."',
									id_registro='".$fluxo->id."',
									id_unidade='".$fluxo->id_unidade_pagcred."',
									id_unidade_pagcred='".$fluxo->id_unidade."',
									tipo='unidade',
									id_categoria='0',
									pagamento=1,
									data_efetivado=now(),
									id_agrupamento='0',
									acerto=1";

							$where="where data > NOW() - INTERVAL 15 MINUTE and id_origem=$fluxoOrigem->id and id_registro=$fluxo->id and acerto=1 and valor='".$valorAcerto."' and data_vencimento='".$fluxo->data_vencimento."' and lixo=0";
							$sql->consult($_p."financeiro_fluxo","*",$where);
							if($sql->rows==0) {
								$sql->add($_p."financeiro_fluxo",$vsql.",data=now()");
								$id_fluxo=$sql->ulid;
								$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='insert',vsql='".addslashes($vsql)."',tabela='".$_p."financeiro_fluxo"."',id_reg='".$id_fluxo."'");
							} else {
								$fluxo=mysqli_fetch_object($sql->mysqry);
								$vwhere="where id=$fluxo->id";
								$sql->update($_p."financeiro_fluxo",$vsql,$vwhere);
								$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='update',vsql='".addslashes($vsql)."',vwhere='".addslashes($vwhere)."',tabela='".$_p."financeiro_fluxo"."',id_reg='".$fluxo->id."'");
								$id_fluxo=$fluxo->id;
							}
						}
						// cria balanço se for um recebimento
						else {

							$valorAcertoBalanco=$fluxo->valor>0?$fluxo->valor*-1:$fluxo->valor;
							$vsqlBalanco="id_credor='$fluxo->id_unidade_pagcred',
											id_unidade='$fluxo->id_unidade',
											tipo='acerto',
											valor='".$valorAcertoBalanco."',
											id_origem='$fluxoOrigem->id',
											id_registro='$fluxo->id'";



							// gera balanco
							$where="where id_origem=$fluxoOrigem->id and id_registro=$fluxo->id and id_credor=$fluxo->id_unidade_pagcred and id_unidade=$fluxo->id_unidade and tipo='acerto' and valor='$valorAcertoBalanco'";
							$sql->consult($_p."financeiro_balanco","*",$where);
							if($sql->rows==0) {
								$sql->add($_p."financeiro_balanco",$vsqlBalanco.",data=now()");
								$id_fluxo=$sql->ulid;
								$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='insert',vsql='".addslashes($vsqlBalanco)."',tabela='".$_p."financeiro_balanco"."',id_reg='".$id_fluxo."'");
							} else {
								$fx=mysqli_fetch_object($sql->mysqry);
								$vwhere="where id=$fx->id";
								$sql->update($_p."financeiro_balanco",$vsqlBalanco,$vwhere);
								$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='update',vsql='".addslashes($vsqlBalanco)."',vwhere='".addslashes($vwhere)."',tabela='".$_p."financeiro_balanco"."',id_reg='".$fx->id."'");
							}
						}

					} */
					// se for conciliacao de acerto entre unidades (balanco): fim

					// se for conciliacao de aporte/retirada de socio (balanco): inicio
					/*

					// ira gerar balanca assim que pagar (solicitacao realizada em 14/12/20)
					else if($fluxo->socio==1) {
						$fluxoOrigem=$this->fluxoOrigem($_p."financeiro_fluxo");
						
						// cria balanço se for um recebimento
						if($fluxo->tipo=="socio_aporte" or $fluxo->tipo=="socio_retirada") {
							$tipo=$fluxo->tipo;

							if($tipo=="socio_retirada") {
								$fluxo->valor=$fluxo->valor>0?$fluxo->valor*-1:$fluxo->valor;
							}

							$vsqlBalanco="id_credor='$fluxo->id_unidade',
											id_socio='$fluxo->id_colaborador',
											tipo='$tipo',
											valor='".$fluxo->valor."',
											id_origem='$fluxoOrigem->id',
											id_registro='$fluxo->id'";

							//echo $vsqlBalanco;die();


							// gera balanco
							$where="where id_origem=$fluxoOrigem->id and id_registro=$fluxo->id and id_credor=$fluxo->id_unidade and id_socio=$fluxo->id_colaborador and tipo='$tipo' and valor='$fluxo->valor'";
							$sql->consult($_p."financeiro_balanco","*",$where);
							if($sql->rows==0) {
								$sql->add($_p."financeiro_balanco",$vsqlBalanco.",data=now()");
								$id_fluxo=$sql->ulid;
								$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='insert',vsql='".addslashes($vsqlBalanco)."',tabela='".$_p."financeiro_balanco"."',id_reg='".$id_fluxo."'");
							} else {
								$fx=mysqli_fetch_object($sql->mysqry);
								$vwhere="where id=$fx->id";
								$sql->update($_p."financeiro_balanco",$vsqlBalanco,$vwhere);
								$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='update',vsql='".addslashes($vsqlBalanco)."',vwhere='".addslashes($vwhere)."',tabela='".$_p."financeiro_balanco"."',id_reg='".$fx->id."'");
							}
						}
					} */
					// se for conciliacao de aporte/retirada de socio (balanco): fim
					if($_dinheiro===true) {
						$_multiplo=0;

						// cria extrato e concilia a conta selecionada
						if(isset($_POST['id_conta']) and is_numeric($_POST['id_conta'])) {
							$conta='';
							$sql->consult($_p."financeiro_bancosecontas","*","where id='".$_POST['id_conta']."'");
							if($sql->rows) {
								$conta=mysqli_fetch_object($sql->mysqry);
							}
							if(is_object($conta)) {


								$vsql="data_extrato='$fluxo->data_vencimento',
										id_unidade='$conta->id_unidade',
										id_conta=$conta->id,
										descricao='FLUXO #$fluxo->id',
										valor='$fluxo->valor',
										id_usuario=$usr->id,
										id_fluxo_criacao=$fluxo->id";
								$sql->consult($_p."financeiro_extrato","*","where id_fluxo_criacao=$fluxo->id and lixo=0");
								if($sql->rows) {
									$x=mysqli_fetch_object($sql->mysqry);
									$id_extrato=$x->id;
									$vwhere="where id=$id_extrato";
									$sql->update($_p."financeiro_extrato",$vsql,$vwhere);
									$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='update',vsql='".addslashes($vsql)."',vwhere='".addslashes($vwhere)."',tabela='".$_p."financeiro_extrato"."',id_reg='".$id_extrato."'");
								} else {
									$sql->add($_p."financeiro_extrato",$vsql);
									$id_extrato=$sql->ulid;
									$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='insert',vsql='".addslashes($vsql)."',tabela='".$_p."financeiro_extrato"."',id_reg='".$id_extrato."'");
								}
								$sql->consult($_p."financeiro_extrato","*","where id=$id_extrato");

								$e=mysqli_fetch_object($sql->mysqry);
								$extratos[]=$e;

								// verifica se a unidade dona do fluxo esta conciliando com uma conta que nao seja sua (mesma unidade)
								if($fluxo->id_unidade!=$conta->id_unidade) {

									// conta a receber
									if($fluxo->valor>0) {
										$id_credor=$fluxo->id_unidade;
										$id_devedor=$conta->id_unidade;
									}
									// conta a pagar
									else {
										$id_credor=$conta->id_unidade;
										$id_devedor=$fluxo->id_unidade;

									}


									$attr=array('id_credor'=>$id_credor,
												'id_devedor'=>$id_devedor,
												'id_fluxo'=>$fluxo->id,
												'valor'=>$fluxo->valor);
									$this->criarBalanco($attr);
								}

							} else {
								$erro='Fluxo não encontrado!';
							}
							

						} else {
							$erro='Fluxo não especificado!';
						}
					} else if($_credito===true and $fluxo->acerto==0) {
						$_multiplo=1;
						if(isset($_POST['id_conta']) and is_numeric($_POST['id_conta'])) {
							$conta='';
							$sql->consult($_p."financeiro_bancosecontas","*","where id='".$_POST['id_conta']."'");
							if($sql->rows) {
								$conta=mysqli_fetch_object($sql->mysqry);
							}
							if(is_object($conta)) {

								$valorTotal=0;

								if(isset($_POST['id_movimento']) and is_array($_POST['id_movimento']) and count($_POST['id_movimento'])) {
									$sql->consult($_p."financeiro_extrato","*","where id IN (".implode(",",$_POST['id_movimento']).")");
									if($sql->rows) {
										while($x=mysqli_fetch_object($sql->mysqry)) {
											$valorTotal+=$x->valor;
											$extratos[]=$x; // movimentacoes que serao conciliadas
										}
									}
								}

								$taxa=0;
								if($formaDePagamento->tipo=="debito") {
									$taxa=$conta->taxa_debito;
								} else if($formaDePagamento->tipo=="credito") {
									$taxa=$conta->taxa_credito;
								} else if($formaDePagamento->tipo=="online") {
									$taxa=$conta->taxa_online;
								}


								$valorTotalComTaxa=$valorTotal*(1+($taxa/100));
								if($valorTotalComTaxa>$fluxo->valor) {
									$erro='Valor das movimentações + taxa (R$'.number_format($valorTotalComTaxa,2,",",".").') é maior que o Conta a Receber (R$'.number_format($fluxo->valor,2,",",".").')';
								} else {
									//echo $fluxo->valor."x".$valorTotalComTaxa;

									$fluxoOrigem=$this->fluxoOrigem($_p."financeiro_fluxo");
									$fluxoOrigemMovimentacao=$this->fluxoOrigem($_p."financeiro_extrato");

									// cria fluxo conciliado:  [soma dos valores das movimentacoes] + [taxa]
									$vsqlFluxoConciliado="valor='".number_format($valorTotalComTaxa,2,".","")."',
															valor_original='".number_format($valorTotal,2,".","")."',
															id_formapagamento='$fluxo->id_formapagamento',
															data_vencimento=now(),
															id_origem='$fluxoOrigemMovimentacao->id',
															descricao='".$formaDePagamento->titulo.": RECEBIMENTO DE CONCILIACAO (RECEBIMENTO + TAXA)',
															id_registros='".addslashes(json_encode($_POST['id_movimento']))."',
															id_unidade='$fluxo->id_unidade',
															tipo='$fluxo->tipo',
															id_categoria='$fluxo->id_categoria',
															pagamento=1,
															data_efetivado=now(),
															id_agrupamento='0',
															id_agrupamento_conciliacao=1,
															taxa='$taxa'";

									$sql->consult($_p."financeiro_fluxo","*","where id_origem='$fluxoOrigemMovimentacao->id' and id_registros='".addslashes(json_encode($_POST['id_movimento']))."' and id_formapagamento='$fluxo->id_formapagamento' and lixo=0");
									$fC='';
									if($sql->rows) {
										$fC=mysqli_fetch_object($sql->mysqry);
										$vWHERE="where id=$fC->id";
										$sql->update($_p."financeiro_fluxo",$vsqlFluxoConciliado,$vWHERE);
										$id_fluxoConciliado=$fC->id;
										$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='update',vsql='".addslashes($vsqlFluxoConciliado)."',vwhere='".addslashes($vWHERE)."',tabela='".$_p."financeiro_fluxo"."',id_reg='".$id_fluxoConciliado."'");
									} else {
										$sql->add($_p."financeiro_fluxo",$vsqlFluxoConciliado.",data=now()");
										$id_fluxoConciliado=$sql->ulid;
										$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='insert',vsql='".addslashes($vsqlFluxoConciliado)."',tabela='".$_p."financeiro_fluxo"."',id_reg='".$id_fluxoConciliado."'");

										$sql->consult($_p."financeiro_fluxo","*","where id=$id_fluxoConciliado");
										if($sql->rows) {
											$fC=mysqli_fetch_object($sql->mysqry);
										}
									}

									if(empty($fC)) {
										$erro='Fluxo Conciliado não criado!';
									} else {


										$valorDescontado=$fluxo->valor-$valorTotalComTaxa;

										// cria fluxo descontado com o fluxo conciliado
										$vsqlFluxoDescontado="valor_original='".number_format($fluxo->valor,2,".","")."',
																valor='".number_format($valorDescontado,2,".","")."',
																id_formapagamento='$fluxo->id_formapagamento',
																data_vencimento=now(),
																id_origem='$fluxoOrigem->id',
																id_registro='$id_fluxoConciliado',
																descricao='".$formaDePagamento->titulo.": DESCONTADO PELO FLUXO #$fC->id',
																id_unidade='$fluxo->id_unidade',
																tipo='$fluxo->tipo',
																id_caixa='$fluxo->id_caixa',
																id_categoria='$fluxo->id_categoria',
																pagamento=1,
																data_efetivado='$fluxo->data_efetivado',
																id_agrupamento='0',
																id_agrupamento_conciliacao=0";

										$sql->consult($_p."financeiro_fluxo","*","where id_registro='$id_fluxoConciliado' and id_origem='$fluxoOrigem->id' and id_formapagamento='$fluxo->id_formapagamento' and lixo=0");
										if($sql->rows) {
											$fC=mysqli_fetch_object($sql->mysqry);
											$vWHERE="where id=$fC->id";
											$sql->update($_p."financeiro_fluxo",$vsqlFluxoDescontado,$vWHERE);
											$id_fluxoDescontado=$fC->id;
											$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='update',vsql='".addslashes($vsqlFluxoDescontado)."',vwhere='".addslashes($vWHERE)."',tabela='".$_p."financeiro_fluxo"."',id_reg='".$id_fluxoDescontado."'");
										} else {
											$sql->add($_p."financeiro_fluxo",$vsqlFluxoDescontado.",data=now()");
											$id_fluxoDescontado=$sql->ulid;
											$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='insert',vsql='".addslashes($vsqlFluxoConciliado)."',tabela='".$_p."financeiro_fluxo"."',id_reg='".$id_fluxoDescontado."'");
										}

										// agrupa o fluxo que originou conciliacao
										$sql->update($_p."financeiro_fluxo","id_agrupamento=$id_fluxoDescontado","where id=$fluxo->id");

										$fluxo=$fC;

										// verifica se a unidade dona do fluxo esta conciliando com uma conta que nao seja sua (mesma unidade)
										if($fluxo->id_unidade!=$conta->id_unidade) {

											// conta a receber
											if($fluxo->valor>0) {
												$id_credor=$fluxo->id_unidade;
												$id_devedor=$conta->id_unidade;
											}
											// conta a pagar
											else {
												$id_credor=$conta->id_unidade;
												$id_devedor=$fluxo->id_unidade;

											}


											$attr=array('id_credor'=>$id_credor,
														'id_devedor'=>$id_devedor,
														'id_fluxo'=>$fluxo->id,
														'valor'=>$fluxo->valor);
											$this->criarBalanco($attr);
										}
									}
								}
							}  else {
								$erro='Fluxo não encontrado!';
							}
						}
					} else {


						$id_conta=0;
						if(isset($_POST['conciliacao']) and $_POST['conciliacao']=="multiplo") {
								
							$_multiplo=1;
							if(is_array($_POST['id_movimento']) and count($_POST['id_movimento'])>0) {
								$movimentosID=array();
								foreach($_POST['id_movimento'] as $v) {
									if(is_numeric($v)) $movimentosID[]=$v;
								}

								$sql->consult($_p."financeiro_extrato","*","where id IN (".implode(',',$movimentosID).") and lixo=0");

								if($sql->rows==0) {
									$erro="Nenhum movimento bancário encontrado!";
								} else {
									$movimentoTotal=0;
									$extratos=array();
									while($extrato=mysqli_fetch_object($sql->mysqry)) {
										if(is_array($this->extratoConciliadoFluxo($extrato->id))) {
											$erro='Um dos movimentos já está conciliado!';
											break;
										} else if(is_array($this->extratoConciliadoTransferencia($extrato->id))) {
											$erro='Um dos movimentos já está conciliado!';
											break;
										}
										if($id_conta==0) $id_conta=$extrato->id_conta;
										$movimentoTotal+=$extrato->valor;
										$extratos[$extrato->id]=$extrato;
									}
									
									if(empty($erro)) {
										
										$contaValor=$fluxo->valor;
										$contaValor+=$juros;
										$contaValor+=$multa;
										$contaValor+=$desconto;

										$dif=number_format($movimentoTotal,2)+(number_format($contaValor,2)*-1);
									//	echo $dif;die()
										
										if(number_format($movimentoTotal,2)!=number_format($contaValor,2)) $erro='Os valores não batem<br /><br />Valor da Movimentação: R$ '.number_format($movimentoTotal,2).'<br />Valor do Fluxo: R$'.number_format($contaValor,2).'';
									}
								}
									
							} else {
								$erro='Nenhum movimento foi selecionado!';
							}
						} 
						
						else {
							$_multiplo=0;
							if(isset($_POST['id_movimento']) and is_numeric($_POST['id_movimento']) and $_POST['id_movimento']>0) {
								

								$sql->consult($_p."financeiro_extrato","*","where id ='".$_POST['id_movimento']."' and lixo=0");
								
								if($sql->rows==0) {
									$erro="Movimento não encontrado!";
								} else {
									$movimentoTotal=0;
									$extratos=array();
									while($extrato=mysqli_fetch_object($sql->mysqry)) {
										if(is_array($this->extratoConciliadoFluxo($extrato->id))) {
											$erro='O mvimento selecionado já está conciliado!';
											break;
										} else if(is_array($this->extratoConciliadoTransferencia($extrato->id))) {
											$erro='O movimento selecionado já está conciliado!';
											break;
										}
										if($id_conta==0) $id_conta=$extrato->id_conta;
										$movimentoTotal+=$extrato->valor;
										$extratos[$extrato->id]=$extrato;
									}


									if(empty($erro)) {
										//echo $fluxo->valor." -> ".$juros." -> ".$multa." -> ".$desconto."<br />";
										$contaValor=$fluxo->valor;
										$contaValor+=$juros;
										$contaValor+=$multa;
										$contaValor+=$desconto;

										//echo "(".abs($movimentoTotal)."=".abs($contaValor).")";
										if(number_format($movimentoTotal,2)!=number_format($contaValor,2)) {
											$erro='Os valores não batem.<br /><br />Valor da Movimentação: R$ '.number_format($movimentoTotal,2).'<br />Valor do Fluxo: R$'.number_format($contaValor,2).'';
										} 
									}
								}//die();
							} else {
								$erro='Movimento não selecionado!';
							}
						}

						$conta='';
						if($id_conta>0) {
							$sql->consult($_p."financeiro_bancosecontas","*","where id=$id_conta and lixo=0");
							if($sql->rows) {
								$conta=mysqli_fetch_object($sql->mysqry);
							}
						}
						//echo $fluxo->id_unidade." x ".$conta->id_unidade."<br />";
						// verifica se a unidade dona do fluxo esta conciliando com uma conta que nao seja sua (mesma unidade)
						if(is_object($conta)) {


							$valor=$fluxo->valor;

							if(isset($_POST['juros']) and is_numeric(valor($_POST['juros']))) $valor+=valor($_POST['juros']);
							if(isset($_POST['multa']) and is_numeric(valor($_POST['multa']))) $valor+=valor($_POST['multa']);
							if(isset($_POST['desconto']) and is_numeric(valor($_POST['desconto']))) $valor+=valor($_POST['desconto']);

							
							
						}


					}

					// cria vinculos de conciliacoes 
					if(empty($erro)) {
						foreach($extratos as $ex) {

							$vSQL2="data=now(),id_fluxo='".$fluxo->id."',id_extrato='".$ex->id."',id_transferencia=0, multiplo='".$_multiplo."',valor='".$ex->valor."'";
						
							$sql->add($_p."financeiro_conciliacoes",$vSQL2);
							$id_conciliacao=$sql->ulid;
							$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='insert',vsql='".addslashes($vSQL2)."',tabela='".$_p."financeiro_conciliacoes"."',id_reg='".$id_conciliacao."'");
						}
						$vWHERE="where id='".$fluxo->id."'";
						$vSQL=$adm->vSQL(explode(",","juros,multa,desconto"),$_POST);
						$vSQL.="id_conciliacao=$id_conciliacao,";
						if(!empty($vSQL)) {
							$sql->update($_p."financeiro_fluxo",substr($vSQL,0,strlen($vSQL)-1),$vWHERE);
							$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='update',vsql='".addslashes($vSQL)."',vwhere='".addslashes($vWHERE)."',tabela='".$_p."financeiro_fluxo"."',id_reg='".$fluxo->id."'");
						}
					}
					
					
				}
			}
			
			if(!empty($erro)) {
				$this->erro=$erro;
				return false;
			}
			return true;	
		}

		// concilia um movimento com fluxo
		function movimentoConciliar($post) {

			$usr=$this->usr;
			$_p=$this->prefixo;
			$sql = new Mysql();
			$adm=$this->adm;
			$erro='';
			$juros=isset($_POST['juros'])?valor($_POST['juros']):0;
			$multa=isset($_POST['multa'])?valor($_POST['multa']):0;
			$desconto=isset($_POST['desconto'])?valor($_POST['desconto']):0;
			
			$sql->consult($_p."financeiro_extrato","*","where id='".$post['conciliar']."' and lixo=0");
			if($sql->rows==0) {
				$erro='Movimento não encontrado';
			} else {
				$mov=mysqli_fetch_object($sql->mysqry);

				$fluxosConciliados=$this->extratoConciliadoFluxo($mov->id);

				if(is_array($fluxosConciliados)) {
					$erro='Esta movimentação já está conciliada!';
				} else {
					$id_unidade=0;
					if($_POST['conciliacao']=="multiplo") {
						$_multiplo=1;
						if(is_array($_POST['id_fluxo']) and count($_POST['id_fluxo'])>0) {
							$fluxosID=array();
							foreach($_POST['id_fluxo'] as $v) {
								if(is_numeric($v)) $fluxosID[]=$v;
							}

							$sql->consult($_p."financeiro_fluxo","*","where id IN (".implode(',',$fluxosID).") and lixo=0");

							if($sql->rows==0) {
								$erro="Nenhum fluxo encontrado!";
							} else {
								$fluxoTotal=0;
								$fluxos=array();
								while($fluxo=mysqli_fetch_object($sql->mysqry)) {
									if(is_array($this->fluxoConciliado($fluxo->id))) {
										$erro='Um dos movimentos já está conciliado!';
										break;
									} 
									if($id_unidade==0) $id_unidade=$fluxo->id_unidade;
									$fluxoTotal+=$fluxo->valor;
									$fluxos[$fluxo->id]=$fluxo;
								}
								
								if(empty($erro)) {
									
									$movValor=$mov->valor;
									$movValor+=$juros;
									$movValor+=$multa;
									$movValor+=$desconto;


									$dif=$fluxoTotal-$movValor;
									//echo $dif;die();
									
									//if(number_format($fluxoTotal,2)!=number_format($movValor,2)) {
									if($dif>1){
										$erro='Os valores não batem<br /><br />Valor da Movimentação: R$ '.number_format($movValor,2).'<br />Valor do Fluxo: R$'.number_format($fluxoTotal,2).'';
									
									}
								}
							}
								
						} else {
							$erro='Nenhum fluxo selecionado!';
						}
					} 
					
					else {
						$_multiplo=0;
						if(isset($post['id_fluxo']) and is_numeric($_POST['id_fluxo']) and $_POST['id_fluxo']>0) {
							
							$sql->consult($_p."financeiro_fluxo","*","where id ='".$_POST['id_fluxo']."' and lixo=0");

							if($sql->rows==0) {
								$erro="Fluxo não encontrado!";
							} else {
								$fluxoTotal=0;
								$fluxos=array();
								while($fluxo=mysqli_fetch_object($sql->mysqry)) {
									if(is_array($this->fluxoConciliado($fluxo->id))) {
										$erro='O movimento selecionado já está conciliado!';
										break;
									} 
									if($id_unidade==0) $id_unidade=$fluxo->id_unidade;
									$fluxoTotal+=$fluxo->valor;
									$fluxos[$fluxo->id]=$fluxo;
								}


								if(empty($erro)) {
									//echo $fluxo->valor." -> ".$juros." -> ".$multa." -> ".$desconto."<br />";
									$movValor=$mov->valor;
									$movValor+=$juros;
									$movValor+=$multa;
									$movValor+=$desconto;

									//echo $juros." ".$multa." ".$desconto;

									//echo "(".abs($movimentoTotal)."=".abs($contaValor).")";
									if(number_format($fluxoTotal,2)!=number_format($movValor,2)) {
										$erro='Os valores não batem!<br /><br />Valor do Fluxo: R$'.number_format($fluxoTotal,2).'<br />Valor da Movimentação: R$ '.number_format($movValor,2);
									} 
								}
							}//die();
						} else {
							$erro='Movimento não selecionado!';
						}
					}

					$unidade='';
					if(isset($id_unidade) and $id_unidade>0) {
						$sql->consult($_p."unidades","*","where id=$id_unidade and lixo=0");
						if($sql->rows) $unidade=mysqli_fetch_object($sql->mysqry);
					}

						
					//echo $fluxo->id_unidade." x ".$conta->id_unidade."<br />";
					// verifica se a unidade dona do movimento esta conciliando com um fluxo que nao seja sua (mesma unidade)
					if(is_object($unidade) and $mov->id_unidade!=$unidade->id) {

						// conta a receber
						if($mov->valor>0) {
							$id_credor=$mov->id_unidade;
							$id_devedor=$unidade->id;
						}
						// conta a pagar
						else {
							$id_credor=$unidade->id;
							$id_devedor=$mov->id_unidade;
						}

						$valor=$mov->valor;

						if(isset($_POST['juros']) and is_numeric(valor($_POST['juros']))) $valor+=valor($_POST['juros']);
						if(isset($_POST['multa']) and is_numeric(valor($_POST['multa']))) $valor+=valor($_POST['multa']);
						if(isset($_POST['desconto']) and is_numeric(valor($_POST['desconto']))) $valor+=valor($_POST['desconto']);
						
						$valor=$valor>0?$valor:$valor*-1;
						$attr=array('id_credor'=>$id_credor,
									'id_devedor'=>$id_devedor,
									'id_movimento'=>$mov->id,
									'valor'=>$valor);
						$this->criarBalanco($attr);
					}

					if(empty($erro)) {
						foreach($fluxos as $fx) {
							$vSQL2="data=now(),id_extrato='".$mov->id."',id_fluxo='".$fx->id."',id_transferencia=0, multiplo='".$_multiplo."',valor='".$fx->valor."',movimentacaoConciliacao=1";
							$sql->add($_p."financeiro_conciliacoes",$vSQL2);
							$id_conciliacao=$sql->ulid;
							$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='insert',vsql='".addslashes($vSQL2)."',tabela='".$_p."financeiro_conciliacoes"."',id_reg='".$id_conciliacao."'");
						}
						$vWHERE="where id='".$mov->id."'";
						$vSQL=$adm->vSQL(explode(",","juros,multa,desconto"),$_POST);
						if(!empty($vSQL)) {
							$sql->update($_p."financeiro_extrato",substr($vSQL,0,strlen($vSQL)-1),$vWHERE);
							$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='update',vsql='".addslashes($vSQL)."',vwhere='".addslashes($vWHERE)."',tabela='".$_p."financeiro_extrato"."',id_reg='".$mov->id."'");
						}
					}
				}
			}

			if(!empty($erro)) {
				$this->erro=$erro;
				return false;
			}
			return true;	
		}


		// cria fluxo acumulado para tipos de pagamentos CREDITO, DEBITO e ONLINE
		function fluxoAcumulado($attr) {
			$usr=$this->usr;
			$_p=$this->prefixo;
			$sql = new Mysql();

			$erro='';
			if(!isset($attr['valor']) or !is_numeric($attr['valor'])) $erro='Defina o valor';
			else if(!isset($attr['id_formapagamento']) or !is_numeric($attr['id_formapagamento'])) $erro='Defina a Forma de Pagamento';
			else if(!isset($attr['id_origem']) or !is_numeric($attr['id_origem'])) $erro='Defina o Origem do fluxo';
			else if(!isset($attr['id_registro']) or !is_numeric($attr['id_registro'])) $erro='Defina o Registro do fluxo';
			else if(!isset($attr['id_unidade']) or !is_numeric($attr['id_unidade'])) $erro='Defina a Unidade';
			else if(!isset($attr['tipo']) or empty($attr['tipo'])) $erro='Defina o Tipo';
			else if(!isset($attr['id_caixa']) or !is_numeric($attr['id_caixa'])) $attr['id_caixa']='';
			else if(!isset($attr['id_caixadelivery']) or !is_numeric($attr['id_caixadelivery'])) $attr['id_caixadelivery']='';
			else if(!isset($attr['id_categoria']) or !is_numeric($attr['id_categoria'])) $erro='Defina a Categoria';

			if(empty($erro)) {
				// verifica se fluxo ja foi adicionado

				$continua=true;
				$flx='';
				/*if($attr['id_origem']>0 and $attr['id_registro']>0) {
					$where="where id_registro='".$attr['id_registro']."' and id_origem='".$attr['id_origem']."' and id_formapagamento='".$attr['id_formapagamento']."' and lixo=0";
					$sql->consult($_p."financeiro_fluxo","*",$where);
					if($sql->rows) $flx=mysqli_fetch_object($sql->mysqry);
					$continua=true;
				} else {
					$continua=true;
				}*/

				if($continua===true) {

					// retorna fluxo acumulado
					$whereAcumulado="where id_unidade='".$attr['id_unidade']."' and 
																		id_formapagamento='".$attr['id_formapagamento']."' and 
																		id_agrupamento=0 and 
																		id_agrupamento_conciliacao=0 and
																		valor>0";
					$fluxoAcumulado='';
					$sql->consult($_p."financeiro_fluxo","*",$whereAcumulado);
					if($sql->rows) {
						$fluxoAcumulado=mysqli_fetch_object($sql->mysqry);
					}
					
					$vsql="valor_original='".$attr['valor']."',
							id_formapagamento='".$attr['id_formapagamento']."',
							data_vencimento=now(),
							id_origem='".$attr['id_origem']."',
							id_registro='".$attr['id_registro']."',
							id_unidade='".$attr['id_unidade']."',
							tipo='".$attr['tipo']."',
							id_categoria='".$attr['id_categoria']."',
							pagamento=1,
							data_efetivado=now(),
							id_agrupamento='0'";

					if(isset($attr['id_caixadelivery'])) $vsql.=",id_caixadelivery='".$attr['id_caixadelivery']."'";
					else $vsql.=",id_caixa='".$attr['id_caixa']."'";

					//echo $vsql;die();

					if(is_object($fluxoAcumulado)) $vsql.=",valor='".($attr['valor']+$fluxoAcumulado->valor)."'";
					else $vsql.=",valor='".($attr['valor'])."'";
						
					//echo $sql->rows."->".$vsql;die();
					if(is_object($flx))	{
						$vwhere="where id=$flx->id";
						$sql->update($_p."financeiro_fluxo",$vsql,$vwhere);
						$id_novoFluxoAcumulado=$flx->id;
						$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='update',vsql='".addslashes($vsql)."',vwhere='".addslashes($vwhere)."',tabela='".$_p."financeiro_fluxo"."',id_reg='".$id_novoFluxoAcumulado."'");
					} else {
						$sql->add($_p."financeiro_fluxo",$vsql.",data=now()");
						$id_novoFluxoAcumulado=$sql->ulid;
						$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='insert',vsql='".addslashes($vsql)."',tabela='".$_p."financeiro_fluxo"."',id_reg='".$id_novoFluxoAcumulado."'");
					}
					
					
					//if(is_object($fluxoAcumulado)) $sql->update($_p."financeiro_fluxo","id_agrupamento='".$id_novoFluxoAcumulado."'","where id=$fluxoAcumulado->id");
					if(is_object($fluxoAcumulado)) {
						$sql->consult($_p."financeiro_fluxo","*",$whereAcumulado." and id<>$id_novoFluxoAcumulado");
						while($x=mysqli_fetch_object($sql->mysqry)) {
							$sql->update($_p."financeiro_fluxo","id_agrupamento='".$id_novoFluxoAcumulado."'","where id=$x->id");
						}
					}


					return true;
				} else {
					$this->erro="Financeiro já conferido!";
					return false;
				}
			} else {
				$this->erro=$erro;
				return false;
			}
		}

		// desconcilia uma movimentacao bancaria
		function extratoDesconciliar($id_extrato) {
			$usr=$this->usr;
			$_p=$this->prefixo;

			$sql = new Mysql();

			$erro='';
			
			if(is_numeric($id_extrato)) {
				$sql->consult($_p."financeiro_extrato","*","where id='".$id_extrato."' and lixo=0");
				
				if($sql->rows==0) {
					$erro="Movimentação não encontrada!";
				} else {
					$extrato=mysqli_fetch_object($sql->mysqry);
				
					$transferencia='';

					// retorna fluxos conciliadas a esta movimentacao
					$fluxos=$this->extratoConciliadoFluxo($extrato->id);
					if(!is_array($fluxos)) {
						// retorna transferencias conciliadas a esta movimentacao
						$transferencia=$this->extratoConciliadoTransferencia($extrato->id);
					}
					if(is_array($transferencia)) {
						$erro="Movimento conciliado como transferencia!";
					} else if(!is_array($fluxos)) {
						$erro="Movimento não conciliado!";
					}  else {



						$fluxoAgrupadoConciliado=false;
						$fluxosID=array(0);
						foreach($fluxos as $v) {
							$fluxosID[]=$v->id;
							// se for um fluxo de conciliacao de fluxo acumulado (credito/debito...)
							if($v->id_agrupamento_conciliacao==1) {
								$fluxoQueSeraDesconciliado=$v;
								$fluxoAgrupadoConciliado=true;
							}
						}

						// verifica se o(s) fluxo(s) possuem conciliacao e multipla com mais de um extrato
						$extratosDesconciliar=array();
						$sql->consult($_p."financeiro_conciliacoes","*","where id_fluxo IN (".implode(",",$fluxosID).") and lixo=0");
						if($sql->rows) {
							while($x=mysqli_fetch_object($sql->mysqry)) {
								$extratosDesconciliar[]=$x->id_extrato;
								$sql->update($_p."financeiro_extrato","juros=0,multa=0,desconto=0","where id=$x->id_extrato");
							}
						}


						$sql->update($_p."financeiro_fluxo","juros=0,multa=0,desconto=0","where id IN (".implode(",",$fluxosID).")");



						// retorna fluxo agrupado da unidade e da forma de pagamento do fluxo que sera desconciliado
						if($fluxoAgrupadoConciliado===true) { 
							$whereFluxoAgrupado="where id_unidade=$fluxoQueSeraDesconciliado->id_unidade and 
														id_formapagamento=$fluxoQueSeraDesconciliado->id_formapagamento and 
														id_agrupamento=0 and  
														id_agrupamento_conciliacao=0 and
														lixo=0";
							// soma o valores de todos os fluxos sem agrupamento
							$sql->consult($_p."financeiro_fluxo","*",$whereFluxoAgrupado);


							$valorNovoAcumulo=0;
							$fluxosQueSeraoAgrupados=array();
							while($x=mysqli_fetch_object($sql->mysqry)) {
								$valorNovoAcumulo+=$x->valor;
								$fluxosQueSeraoAgrupados[]=$x->id;
							}


							$formaDePagamento='';
							$sql->consult($_p."parametros_formasdepagamento","*","where id=$fluxoQueSeraDesconciliado->id_formapagamento");
							if($sql->rows) {
								$formaDePagamento=mysqli_fetch_object($sql->mysqry);
							}

							// monta o novo fluxo acumulado
							$fluxoOrigemFluxo=$this->fluxoOrigem($_p."financeiro_fluxo");

							// cria fluxo conciliado:  [soma dos valores das movimentacoes] + [taxa]
							$vsqlFluxoDescociliadoEAgrupado="valor_original='".number_format($fluxoQueSeraDesconciliado->valor,2,".","")."',
																valor='".number_format($valorNovoAcumulo+$fluxoQueSeraDesconciliado->valor,2,".","")."',
																id_formapagamento='$fluxoQueSeraDesconciliado->id_formapagamento',
																data_vencimento=now(),
																id_origem='$fluxoOrigemFluxo->id',
																id_registros='".addslashes(json_encode($fluxosQueSeraoAgrupados))."',
																descricao='".$formaDePagamento->titulo.": AGRUPAMENTO DE DESCONCILIAMENTO #$fluxoQueSeraDesconciliado->id',
																id_unidade='$fluxoQueSeraDesconciliado->id_unidade',
																tipo='unidade',
																id_categoria='$fluxoQueSeraDesconciliado->id_categoria',
																pagamento=1,
																data_efetivado=now(),
																id_agrupamento='0',
																id_agrupamento_conciliacao=0";
							//echo $vsqlFluxoDescociliadoEAgrupado."<BR>";
							$sql->consult($_p."financeiro_fluxo","*","where id_origem='$fluxoOrigemFluxo->id' and id_registros='".addslashes(json_encode($fluxosQueSeraoAgrupados))."' and id_formapagamento='$fluxoQueSeraDesconciliado->id_formapagamento' and lixo=0");
							$fC='';
							if($sql->rows) {
								echo "1";
								$fC=mysqli_fetch_object($sql->mysqry);
								$vWHERE="where id=$fC->id";
								$sql->update($_p."financeiro_fluxo",$vsqlFluxoDescociliadoEAgrupado,$vWHERE);
								$id_fluxoDesconciliadoEAgrupado=$fC->id;
								$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='update',vsql='".addslashes($vsqlFluxoDescociliadoEAgrupado)."',vwhere='".addslashes($vWHERE)."',tabela='".$_p."financeiro_fluxo"."',id_reg='".$id_fluxoDesconciliadoEAgrupado."'");
							} else {
								echo "2";
								$sql->add($_p."financeiro_fluxo",$vsqlFluxoDescociliadoEAgrupado.",data=now()");
								$id_fluxoDesconciliadoEAgrupado=$sql->ulid;
								$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='insert',vsql='".addslashes($vsqlFluxoDescociliadoEAgrupado)."',tabela='".$_p."financeiro_fluxo"."',id_reg='".$id_fluxoDesconciliadoEAgrupado."'");

								$sql->consult($_p."financeiro_fluxo","*","where id=$id_fluxoDesconciliadoEAgrupado");
								if($sql->rows) {
									$fC=mysqli_fetch_object($sql->mysqry);
								}
							}

							$sql->update($_p."financeiro_fluxo","id_agrupamento=$fC->id,id_agrupamento_conciliacao=0","where id IN (".implode(",",$fluxosQueSeraoAgrupados).")");
						}

						if(count($extratosDesconciliar)>0) {
							foreach($extratosDesconciliar as $id_extratoDesconciliar) {

								$sql->consult($_p."financeiro_extrato","*","where id='".$id_extratoDesconciliar."' and lixo=0");
								if($sql->rows) {
									$extradoDesc=mysqli_fetch_object($sql->mysqry);
								
									$vSQL="lixo=1";
									$vWHERE="where id_extrato='".$extradoDesc->id."' and id_transferencia=0";
									$sql->update($_p."financeiro_conciliacoes",$vSQL,$vWHERE);

									// remove movimentacao se esta movimentacao foi criada a partir de um fluxo
									if($extradoDesc->id_fluxo_criacao>0) {
										$vSQL="lixo=1";
										$vWHERE="where id='".$extradoDesc->id."' and id_fluxo_criacao>0";
										$sql->update($_p."financeiro_extrato",$vSQL,$vWHERE);
									}

									
									foreach($fluxos as $c) {

										// acumula fluxo acumulado (cartao de debito, cartao de credito e etc)
										if($c->id_agrupamento_conciliacao==1) {
											$vSQL="lixo=1";
											$vWHERE="where id='".$c->id."'";
											$sql->update($_p."financeiro_fluxo",$vSQL,$vWHERE);
											$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='delete',vsql='".addslashes($vSQL)."',vwhere='".addslashes($vWHERE)."',tabela='".$_p."financeiro_fluxo"."',id_reg='".$c->id."'");
										}

										// remove fluxos criados a partir desta movimentacao
										if($c->id_extrato_criacao>0 and $c->id_extrato_criacao==$extradoDesc->id) {
											$vSQL="lixo=1";
											$vWHERE="where id='".$c->id."'";
											$sql->update($_p."financeiro_fluxo",$vSQL,$vWHERE);
											$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='delete',vsql='".addslashes($vSQL)."',vwhere='".addslashes($vWHERE)."',tabela='".$_p."financeiro_fluxo"."',id_reg='".$c->id."'");
										}
										
									}
								}
							}
						} else {
							$erro="Movimento não conciliado!";
						}
					}
				}
			} else {

				$erro='Movimento não encontrado';
			}
			if(!empty($erro)) {
				$this->erro=$erro;
				return false;
			}
			return true;
		}

		// retorna extratos conciliados a uma conta
		function contaConciliada($id_conta) {
			
			
			$usr=$this->usr;
			$_p=$this->prefixo;
			
			$sql = new Mysql();
			
			$erro='';
			$extratos='';
			$sql->consult($_p."financeiro_conciliacoes","*,date_format(data,'%d/%m/%Y') as dataf","where id_fluxo='".$id_conta."' and id_transferencia=0 and lixo=0");
			
			if($sql->rows) {
				$extratos=array();
				$extratosIds=array(0);
				while($x=mysqli_fetch_object($sql->mysqry)) {
					$extratosIds[]=$x->id_extrato;
				}
				
				$sql->consult($_p."financeiro_extrato","*,date_format(data_extrato,'%d/%m/%Y') as dataf","where id IN (".implode(",",$extratosIds).") and lixo=0");
				if($sql->rows) {
					while($x=mysqli_fetch_object($sql->mysqry)) {
						$extratos[]=$x;
					}
				} else {
					$sql->update($_p."conciliacoes","lixo=1","where id_fluxo='".$id_conta."'");
				}
				
				
			}
			return $extratos;
		}

		// desconcilia um fluxo
		function contaDesconciliar($id) {
			
			
			$usr=$this->usr;
			$_p=$this->prefixo;
			$sql = new Mysql();
			$erro='';
			
			if(is_numeric($id)) {
				$sql->consult($_p."financeiro_fluxo","*","where id='".$id."' and lixo=0");
				if($sql->rows==0) {
					$erro="Fluxo não encontrado!";
				} else {
					$conta=mysqli_fetch_object($sql->mysqry); //echo $c->id_conciliacao;die();
				
					if($conta->id_extrato_criacao>0) {
						$erro="Esta conta não poderá ser desconciliada!<br />Fluxo criado através de um movimento bancário.";
					} else {
						
						// retorna movimentacoes conciliadas a este fluxo
						$extrato=$this->contaConciliada($conta->id);
						
						if(!is_array($extrato)) {
							$erro='Fluxo não conciliado!';
						} else {

							$vSQL="lixo=1";
							foreach($extrato as $e) {
								$vWHERE="where id_fluxo='".$conta->id."' and id_extrato='".$e->id."'";
								$sql->update($_p."financeiro_conciliacoes",$vSQL,$vWHERE);
								$sql->update($_p."financeiro_extrato","juros=0,multa=0,desconto=0","where id=$e->id");
							}

							$sql->update($_p."financeiro_fluxo","juros=0,multa=0,desconto=0","where id=$conta->id");

						}
					}
				}
			} else {
				$erro='Fluxo não encontrado!';
			}
			if(!empty($erro)) {
				$this->erro=$erro;
				return false;
			}
			return true;	
		}



	}

?>