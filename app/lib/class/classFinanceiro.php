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

		// materia prima (capta periodo (dtinicio e dtfim))
		function materiaPrimaPeriodo($attr) {
			$_p=$this->prefixo;
			$sql =new Mysql();

			$unidades=(isset($attr['unidades']) and is_array($attr['unidades']) and count($attr['unidades'])>0)?$attr['unidades']:'';
			$dtInicio=(isset($attr['dtInicio']) and !empty($attr['dtInicio']))?$attr['dtInicio']:'';
			$dtFim=(isset($attr['dtFim']) and !empty($attr['dtFim']))?$attr['dtFim']:'';
			if(!empty($dtFim) and !empty($dtInicio)) {

				
				$_categorias=array();
				$sql->consult($_p."financeiro_categorias","*","");
				while($x=mysqli_fetch_object($sql->mysqry)) $_categorias[$x->id]=$x;

				$_produtos=array();
				$sql->consult($_p."produtos","*","");
				while($x=mysqli_fetch_object($sql->mysqry)) $_produtos[$x->id]=$x;

				// tabela de link entre produto x categoria
				$produtosTiposCategorias=array();
				$sql->consult($_p."produtos_tipos_categoriaFinanceiro","*","");
				while($x=mysqli_fetch_object($sql->mysqry)) {
					if($x->id_categoria>0) $produtosTiposCategorias[$x->tipo]=$x->id_categoria;
				}

				$_entradas=$_b2b=array();
				$entradasIDs=$b2bIDs=array(-1);

				
				$where="WHERE data_nota>='$dtInicio 00:00:00' and data_nota<='$dtFim 23:59:59'";
				$sql->consult($_p."financeiro_entradas","*",$where." and lixo=0");
				while($x=mysqli_fetch_object($sql->mysqry)) {
					if($x->id_b2b==0) $entradasIDs[]=$x->id;
					else $b2bIDs[]=$x->id_b2b;
					$_entradas[$x->id]=$x;
				}

				$sql->consult($_p."b2b","*","WHERE id IN (".implode(",",$b2bIDs).") and lixo=0");
				while($x=mysqli_fetch_object($sql->mysqry)) {
					$_b2b[$x->id]=$x;
				}

				$registros=array();

				$where="where id_entrada in (".implode(",",$entradasIDs).") and lixo=0";
				$sql->consult($_p."financeiro_entradas_itens","*",$where);
				while($x=mysqli_fetch_object($sql->mysqry)) {
						

					$entrada='';
					$id_unidade=0;
					if(isset($_entradas[$x->id_entrada])) {
						$entrada=$_entradas[$x->id_entrada];
						$id_unidade=$entrada->id_unidade;
					}
					
						
					if($x->tipo=="pc") {
						$tipo="PRODUTO DE COMPRA";
						if(isset($_produtos[$x->id_produtoPadrao])) {
							$p=$_produtos[$x->id_produtoPadrao];
							if(isset($produtosTiposCategorias[$p->tipo])) $id_categoria=$produtosTiposCategorias[$p->tipo];
						} 
					}
					else {
						if(isset($_produtosDeCompra[$x->id_produto])) {
							$ops=$_produtosDeCompra[$x->id_produto];
							$id_categoria=isset($_categorias[$ops->id_categoria])?$ops->id_categoria:0;
						} else $id_categoria=isset($_categorias[$x->osp_id_categoria])?$x->osp_id_categoria:0;
						
					}
					
					if(is_object($entrada)) {
						// id_categoria=113 -> MATERIA PRIMA
						if(isset($_categorias[$id_categoria]) and $_categorias[$id_categoria]->id_categoria==113) {
							$mes=und2(date('m',strtotime($entrada->data_nota)));


							if(!isset($registros[$id_unidade])) $registros[$id_unidade]=0;
							$registros[$id_unidade]+=$x->valor*$x->quantidade;

						}
					}
				}

				$sql->consult($_p."b2b_itens","*","where id_b2b in (".implode(",",$b2bIDs).") and lixo=0");
				while($x=mysqli_fetch_object($sql->mysqry)) {
					
				
					if(isset($_b2b[$x->id_b2b])) {
						$b2b=$_b2b[$x->id_b2b];
						$id_unidade=$b2b->id_cliente;
					}

					$id_categoria=0;
					
					if(isset($_produtos[$x->id_item])) {
						if(isset($produtosTiposCategorias[$p->tipo])) $id_categoria=$produtosTiposCategorias[$p->tipo];
					}

					if(is_object($b2b)) {

						if(isset($_categorias[$id_categoria]) and $_categorias[$id_categoria]->id_categoria==113) {
							$mes=und2(date('m',strtotime($b2b->data_b2b)));
							if(!isset($registros[$id_unidade])) $registros[$id_unidade]=0;
							$registros[$id_unidade]+=$x->valor*$x->quantidade;
						}
					}
				}

				//echo json_encode($registros);
				$materiaPrima['registros']=$registros;

			}
			return $materiaPrima;

		}

		// materia prima (capta 12 meses)
		function materiaPrima($attr) {
			$_p=$this->prefixo;
			$sql =new Mysql();

			$materiaPrima=array();

			$ano=(isset($attr['ano']) and is_numeric($attr['ano']))?$attr['ano']:0;
			

			$unidades=(isset($attr['unidades']) and is_array($attr['unidades']) and count($attr['unidades'])>0)?$attr['unidades']:'';

			$_categorias=array();
			$sql->consult($_p."financeiro_categorias","*","");
			while($x=mysqli_fetch_object($sql->mysqry)) $_categorias[$x->id]=$x;

			$_produtos=array();
			$sql->consult($_p."produtos","*","");
			while($x=mysqli_fetch_object($sql->mysqry)) $_produtos[$x->id]=$x;

			// tabela de link entre produto x categoria
			$produtosTiposCategorias=array();
			$sql->consult($_p."produtos_tipos_categoriaFinanceiro","*","");
			while($x=mysqli_fetch_object($sql->mysqry)) {
				if($x->id_categoria>0) $produtosTiposCategorias[$x->tipo]=$x->id_categoria;
			}

			//var_dump($attr['unidades']);die();
			if(!empty($unidades) and $ano>0) {

				$dtInicio=date('Y-m-d',strtotime($ano."-01-01"));
				$dtFim=date('Y-m-t',strtotime($ano."-12-01"));

				$_entradas=$_b2b=array();
				$entradasIDs=$b2bIDs=array(-1);

				$_unidades=$this->unidades;

				$where="WHERE data_nota>='$dtInicio 00:00:00' and data_nota<='$dtFim 23:59:59' and id_unidade IN (".implode(",",$unidades).")";
			
				$sql->consult($_p."financeiro_entradas","id,id_b2b,id_unidade,id_fornecedor,data_nota",$where." and lixo=0");
				while($x=mysqli_fetch_object($sql->mysqry)) {
					if($x->id_b2b==0) $entradasIDs[]=$x->id;
					else $b2bIDs[]=$x->id_b2b;

					$_entradas[$x->id]=$x;
				}

				$sql->consult($_p."b2b","id,id_cliente,id_unidade,data_b2b","WHERE id IN (".implode(",",$b2bIDs).") and lixo=0");
				while($x=mysqli_fetch_object($sql->mysqry)) {
					$_b2b[$x->id]=$x;
				}
				$registros=array();
				$registrosProdutos=array();
				$registrosFornecedores=array();
				$registrosUnidades=array();
				$valorTotal=0;

				$where="where id_entrada in (".implode(",",$entradasIDs).") and lixo=0";
				$sql->consult($_p."financeiro_entradas_itens","*",$where);
				while($x=mysqli_fetch_object($sql->mysqry)) {

					$produto='-';
					$fornecedor='-';
					$id_categoria=0;
					$unidade='-';
					$id_fornecedor=0;

					if(isset($_entradas[$x->id_entrada])) {
						$entrada=$_entradas[$x->id_entrada];
						if(isset($_fornecedores[$entrada->id_fornecedor])) {
							$f=$_fornecedores[$entrada->id_fornecedor];
							$fornecedor=$f->tipo_pessoa=="pj"?utf8_encode($f->razao_social):utf8_encode($f->nome);
							
						}
						if(isset($_unidades[$entrada->id_unidade])) {
							$u=$_unidades[$entrada->id_unidade];
							$unidade=utf8_encode($u->titulo);
						}$id_fornecedor=$entrada->id_fornecedor;
					}
					
					if($x->tipo=="pc") {
						$tipo="PRODUTO DE COMPRA";

						if(isset($_produtos[$x->id_produtoPadrao])) {
							$p=$_produtos[$x->id_produtoPadrao];
							$produto=utf8_encode($p->titulo);
							if(isset($produtosTiposCategorias[$p->tipo])) {
								$id_categoria=$produtosTiposCategorias[$p->tipo];
							}
						} else {
							$produto=$x->id_produto."-".$x->id_produtoPadrao;
						}
						$id_produto=$x->id_produtoPadrao;

					}
					else {
						$tipo="OUTROS PRODUTOS E SERVIÇOS";
						if(isset($_produtosDeCompra[$x->id_produto])) {
							$ops=$_produtosDeCompra[$x->id_produto];

							$produto=utf8_encode($ops->titulo);
						
							$id_categoria=isset($_categorias[$ops->id_categoria])?$ops->id_categoria:0;

						} else {
							$id_categoria=isset($_categorias[$x->osp_id_categoria])?$x->osp_id_categoria:0;
						}
						$id_produto=0;
						//$id_produto=$x->id_produto;
					}
					
					if(is_object($entrada)) {
						// id_categoria=113 -> MATERIA PRIMA
						if(isset($_categorias[$id_categoria]) and $_categorias[$id_categoria]->id_categoria==113) {
							$mes=und2(date('m',strtotime($entrada->data_nota)));


							if(!isset($registros[$id_categoria][$mes])) $registros[$id_categoria][$mes]=0;
							$registros[$id_categoria][$mes]+=$x->valor*$x->quantidade;


							if(!isset($registrosProdutos[$id_produto][$mes])) $registrosProdutos[$id_produto][$mes]=0;
							$registrosProdutos[$id_produto][$mes]+=$x->valor*$x->quantidade;

							if(!isset($registrosFornecedores[$id_fornecedor][$mes])) $registrosFornecedores[$id_fornecedor][$mes]=0;
							$registrosFornecedores[$id_fornecedor][$mes]+=$x->valor*$x->quantidade;

							/*$registros[$id_categoria][$mes][]=(object)array('unidade'=>$unidade,
														'tipo'=>$tipo,
														'id'=>is_object($entrada)?$entrada->id:0,
														'produto'=>$produto,
														'fornecedor'=>$fornecedor,
														'id_categoria'=>$id_categoria,
														'categoria'=>isset($_categorias[$id_categoria])?utf8_encode($_categorias[$id_categoria]->titulo):$id_categoria,
														'data'=>$entrada->data_nota,
													 	'valor'=>$x->valor*$x->quantidade);*/

						}
					}
				}

				$sql->consult($_p."b2b_itens","*","where id_b2b in (".implode(",",$b2bIDs).") and lixo=0");
				while($x=mysqli_fetch_object($sql->mysqry)) {
					
					$produto='-';
					$id_categoria=0;
					$unidade='-';
					if(isset($_b2b[$x->id_b2b])) {
						$b2b=$_b2b[$x->id_b2b];
						
						if(isset($_unidades[$b2b->id_cliente])) {
							$u=$_unidades[$b2b->id_cliente];
							$unidade=utf8_encode($u->titulo);
						}

						if(isset($_unidades[$b2b->id_unidade])) {
							$u=$_unidades[$b2b->id_unidade];
							$fornecedor=utf8_encode($u->titulo);
						}
					}
					
					$id_produto=0;
					if(isset($_produtos[$x->id_item])) {
						$p=$_produtos[$x->id_item];
						$id_produto=$x->id_item;
						$produto=utf8_encode($p->titulo);
						if(isset($produtosTiposCategorias[$p->tipo])) {
							$id_categoria=$produtosTiposCategorias[$p->tipo];
						}
					} else {
						$produto="PRODUTO NAO ENCONTRADO";
					}


					if(is_object($b2b)) {

						if(isset($_categorias[$id_categoria]) and $_categorias[$id_categoria]->id_categoria==113) {
							$mes=und2(date('m',strtotime($b2b->data_b2b)));

							if(!isset($registros[$id_categoria][$mes])) $registros[$id_categoria][$mes]=0;
							$registros[$id_categoria][$mes]+=$x->valor*$x->quantidade;

							if($id_produto>0) {
								if(!isset($registrosProdutos[$id_produto][$mes])) $registrosProdutos[$id_produto][$mes]=0;
								$registrosProdutos[$id_produto][$mes]+=$x->valor*$x->quantidade;
							}

							if(!isset($registrosUnidades[$b2b->id_unidade][$mes])) $registrosUnidades[$b2b->id_unidade][$mes]=0;
							$registrosUnidades[$b2b->id_unidade][$mes]+=$x->valor*$x->quantidade;

							/*
							$registros[$id_categoria][$mes][]=(object)array('unidade'=>$unidade,
														'id'=>is_object($entrada)?$entrada->id:0,
														'tipo'=>$tipo,
														'produto'=>$produto,
														'fornecedor'=>$fornecedor,
														'id_categoria'=>$id_categoria,
														'categoria'=>isset($_categorias[$id_categoria])?utf8_encode($_categorias[$id_categoria]->titulo):$id_categoria,
														'data'=>$entrada->data_nota,
													 	'valor'=>$x->valor*$x->quantidade);*/
						}
					}
				}

				$materiaPrima['registros']=$registros;
				$materiaPrima['registrosProdutos']=$registrosProdutos;
				$materiaPrima['registrosFornecedores']=$registrosFornecedores;
				$materiaPrima['registrosUnidades']=$registrosUnidades;

			}



			return $materiaPrima;
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

		// retorna vendaMesa e vendasBalcao
		function infoReceitasVendasLojas($attr) {

			$_p=$this->prefixo;
			$sql=new Mysql();

			if(isset($attr['where']) and !empty($attr['where'])) $where=$attr['where'];

			if(!empty($where)) {	

				$retornarPorMes=isset($attr['retornarPorMes'])?true:false;

				$visitasBalcao=$visitasBalcaoPessoas=$visitasMesa=$visitasMesaPessoas=$gorjetaBalcao=$gorjetaMesa=0;

				$vendaBalcaoMes=$vendaMesaMes=$gorjetaBalcaoMes=$gorjetaMesaMes=array();
				$vendaBalcaoTurno=$vendaMesaTurno=$vendaTurno=array();

				$unidadesIds=array(-1);
				$sql->consult("vucaADM.vuca_contas_unidades","id","where vuca_name='".$_ENV['NAME']."' and lixo=0");
				while($x=mysqli_fetch_object($sql->mysqry)) $unidadesIds[]=$x->id;


				// consulta caixas geral
				$where.=" and id_unidade IN (".implode(",",$unidadesIds).")";
				$sql->consult($_p."vendas_caixas","*",$where." and lixo=0 order by data_caixa desc");	
				$_caixas=array();
				$caixasIDs=array(-1);
				while($x=mysqli_fetch_object($sql->mysqry)) {



					$_caixas[$x->id]=$x;
					$caixasIDs[]=$x->id;
				}


				// consulta subcaixas
				$_subcaixa=array();
				$subcaixas=array();
				$subcaixasIDs=array(-1);
				$sql->consult($_p."vendas_caixas","*","where id_caixa IN (".implode(",",$caixasIDs).") and lixo=0");
				while($x=mysqli_fetch_object($sql->mysqry)) { 
					$_subcaixa[$x->id]=$x;
					$subcaixasIDs[]=$x->id;
					$subcaixas[$x->id_caixa][]=$x->id;
				}

				// consulta pagamentos e capta comandas dos subcaixas
				$pagamentos=array();
				$comandasIDs=array(-1);
				$whereAux="where id_caixa IN (".implode(",",$subcaixasIDs).") and lixo=0";
				$sql->consult($_p."clientes_visitas_comandas_pagamentos","id_comanda,valor,valor_taxa,id_caixa",$whereAux);
				while($x=mysqli_fetch_object($sql->mysqry)) {
					$comandasIDs[]=$x->id_comanda;
					$pagamentos[$x->id_caixa][]=$x;
					
				}

				// consulta comandas para captar visitas
				$visitasIDs=array(-1);
				$whereAux="where id IN (".implode(",",$comandasIDs).")";
				$sql->consult($_p."clientes_visitas_comandas","id_visita,id",$whereAux);
				while($x=mysqli_fetch_object($sql->mysqry)) {
					$visitasIDs[]=$x->id_visita;
					$comandas[$x->id]=$x;
				}


				// consulta as visitas para diferenciar venda balcao e venda mesa
				$visitas=array();
				$whereAux="where id IN (".implode(",",$visitasIDs).")";
				$sql->consult($_p."clientes_visitas","id,balcao,pessoas",$whereAux);
				while($x=mysqli_fetch_object($sql->mysqry)) {
					$visitas[$x->id]=$x;
					if($x->balcao==1) {
						$visitasBalcao++;
						$visitasBalcaoPessoas+=$x->pessoas;
					}
					else {
						$visitasMesa++;
						$visitasMesaPessoas+=$x->pessoas;
					}
				}

				$caixaVendas=array('vendaMesa'=>0,
									'vendaBalcao'=>0,
									'vendaTurno'=>array(),
									'vendaMesaTurno'=>array(),
									'vendaBalcaoTurno'=>array(),
									'vendaMesaMes'=>array(),
									'vendaBalcaoMes'=>array());	


				foreach($_caixas as $x) {

					$mes=und2(date('m',strtotime($x->data_caixa)));


					// se a consulta for de mais de uma unidade (Relatorio > Diario)
					if(isset($attr['multiplasUnidades'])) {
						$vendaMesa=$vendaBalcao=$vendaConferidaPeloFinanceiro=0;
						$vendaMesaOntem=$vendaBalcaoOntem=0;
						$vendaConferidaPeloFinanceiroMes=array();


						if($x->validado==2) {
							if($x->valor>0) {
								$vendaConferidaPeloFinanceiro=$x->valor;
								$vendaConferidaPeloFinanceiroMes[$mes]=$x->valor;
							}
							// se nao, capta os valores inseridos pelo financeiro
							else {
								$whereAux="where id_caixa=$x->id and lixo=0";
								$sql->consult($_p."vendas_caixas_financeiro","sum(valor) as total",$whereAux);
								$t=mysqli_fetch_object($sql->mysqry);
								$vendaConferidaPeloFinanceiro=$t->total;
								$vendaConferidaPeloFinanceiroMes[$mes]=$t->total;
							}
						}

						if(isset($subcaixas[$x->id])) {
							foreach($subcaixas[$x->id] as $id_subcaixa) {

								$subcaixa=$caixa='';
								if(isset($_subcaixa[$id_subcaixa])) {
									$subcaixa=$_subcaixa[$id_subcaixa];
									if(isset($_caixas[$subcaixa->id_caixa])) {
										$caixa=$_caixas[$subcaixa->id_caixa];
									}// else echo "erro>".$subcaixa->id." ".$subcaixa->id_caixa."<BR>";
								}

								//if(empty($subcaixa) or empty($caixa)) continue;
								
								if(isset($pagamentos[$id_subcaixa])) {
									foreach($pagamentos[$id_subcaixa] as $p) {
										if(isset($comandas[$p->id_comanda])) {
											$comanda=$comandas[$p->id_comanda];
											if(isset($visitas[$comanda->id_visita])) {
												$visita=$visitas[$comanda->id_visita];
												if(is_object($visita)) {
													if($visita->balcao==1) {
														$vendaBalcao+=$p->valor;
														$gorjetaBalcao+=$p->valor_taxa;
														
														if(!isset($vendaBalcaoMes[$mes])) $vendaBalcaoMes[$mes]=0;
														$vendaBalcaoMes[$mes]+=$p->valor-$p->valor_taxa;
														
														if(!isset($vendaBalcaoTurno[$caixa->turno])) $vendaBalcaoTurno[$caixa->turno]=0;
														$vendaBalcaoTurno[$caixa->turno]+=$p->valor;

														
														if(!isset($gorjetaBalcaoMes[$mes])) $gorjetaBalcaoMes[$mes]=0;
														$gorjetaBalcaoMes[$mes]+=$p->valor_taxa;
													} else {
														$vendaMesa+=$p->valor;
														$gorjetaMesa+=$p->valor_taxa;
														
														if(!isset($vendaMesaMes[$mes])) $vendaMesaMes[$mes]=0;
														$vendaMesaMes[$mes]+=$p->valor-$p->valor_taxa;

														
														if(!isset($gorjetaMesaMes[$mes])) $gorjetaMesaMes[$mes]=0;
														$gorjetaMesaMes[$mes]+=$p->valor_taxa;
													}

													
												} 
											}
										}
									}
								} 
							}
						}

						// realiza o calculo do percentual da diferenca entre sistema x financeiro
						if($vendaConferidaPeloFinanceiro>0) {
							$diferencaFinanceiroSistema=number_format($vendaConferidaPeloFinanceiro-($vendaBalcao+$vendaMesa),4,".","");
							$percentualDiferencaFinanceiroESistema=(($vendaBalcao+$vendaMesa)==0?0:$diferencaFinanceiroSistema/($vendaBalcao+$vendaMesa));
							$vendaMesaPercentual=$vendaMesa*$percentualDiferencaFinanceiroESistema;
							$vendaBalcaoPercentual=$vendaBalcao*$percentualDiferencaFinanceiroESistema;
						//	echo $x->id_unidade."->".$vendaMesaPercentual."<BR>";
							$vendaMesa+=$vendaMesaPercentual;
							$vendaBalcao+=$vendaBalcaoPercentual;
						}

						if(!isset($caixaVendas[$x->id_unidade]['vendaBalcao'])) $caixaVendas[$x->id_unidade]['vendaBalcao']=0;
						if(!isset($caixaVendas[$x->id_unidade]['vendaMesa'])) $caixaVendas[$x->id_unidade]['vendaMesa']=0;

						
						if($x->validado==2) {

							

							if(!isset($vendaMesaMes[$mes])) $vendaMesaMes[$mes]=0;
							if(!isset($vendaBalcaoMes[$mes])) $vendaBalcaoMes[$mes]=0;

							$vendaMesaMes[$mes]+=(isset($vendaMesaPercentual)?$vendaMesaPercentual:0);
							$vendaBalcaoMes[$mes]+=(isset($vendaBalcaoPercentual)?$vendaBalcaoPercentual:0);



							if(!isset($vendaMesaTurno[$x->id_unidade][$x->turno])) $vendaMesaTurno[$x->id_unidade][$x->turno]=0;
							if(!isset($vendaBalcaoTurno[$x->id_unidade][$x->turno])) $vendaBalcaoTurno[$x->id_unidade][$x->turno]=0;

							$vendaMesaTurno[$x->id_unidade][$x->turno]+=isset($vendaMesaPercentual)?$vendaMesaPercentual:0;
							$vendaBalcaoTurno[$x->id_unidade][$x->turno]+=isset($vendaBalcaoPercentual)?$vendaBalcaoPercentual:0;

							$caixaVendas[$x->id_unidade]['vendaBalcao']+=$vendaBalcao;
							$caixaVendas[$x->id_unidade]['vendaMesa']+=$vendaMesa;

							if(!isset($vendaTurno[$x->id_unidade][$x->turno])) $vendaTurno[$x->id_unidade][$x->turno]=0;
							$vendaTurno[$x->id_unidade][$x->turno]+=$vendaBalcao+$vendaMesa;
						} else {
							$caixaVendas[$x->id_unidade]['vendaBalcao']+=$vendaBalcao;
							$caixaVendas[$x->id_unidade]['vendaMesa']+=$vendaMesa;

							if(!isset($vendaTurno[$x->id_unidade][$x->turno])) $vendaTurno[$x->id_unidade][$x->turno]=0;
							$vendaTurno[$x->id_unidade][$x->turno]+=$vendaBalcao+$vendaMesa;
						}
					
					} 

					// se for consultar de apenas uma unidade
					else {
					
						$vendaMesa=$vendaBalcao=$vendaConferidaPeloFinanceiro=0;
						$vendaMesaOntem=$vendaBalcaoOntem=0;
						$vendaConferidaPeloFinanceiroMes=array();


						if($x->validado==2) {
							if($x->valor>0) {
								$vendaConferidaPeloFinanceiro=$x->valor;
								$vendaConferidaPeloFinanceiroMes[$mes]=$x->valor;
							}
							// se nao, capta os valores inseridos pelo financeiro
							else {
								$whereAux="where id_caixa=$x->id and lixo=0";
								$sql->consult($_p."vendas_caixas_financeiro","sum(valor) as total",$whereAux);
								$t=mysqli_fetch_object($sql->mysqry);
								$vendaConferidaPeloFinanceiro=$t->total;
								$vendaConferidaPeloFinanceiroMes[$mes]=$t->total;
							}
						}

						if(isset($subcaixas[$x->id])) {
							foreach($subcaixas[$x->id] as $id_subcaixa) {

								$subcaixa=$caixa='';
								if(isset($_subcaixa[$id_subcaixa])) {
									$subcaixa=$_subcaixa[$id_subcaixa];
									if(isset($_caixas[$subcaixa->id_caixa])) {
										$caixa=$_caixas[$subcaixa->id_caixa];
									}// else echo "erro>".$subcaixa->id." ".$subcaixa->id_caixa."<BR>";
								}

								//if(empty($subcaixa) or empty($caixa)) continue;
								
								if(isset($pagamentos[$id_subcaixa])) {
									foreach($pagamentos[$id_subcaixa] as $p) {
										if(isset($comandas[$p->id_comanda])) {
											$comanda=$comandas[$p->id_comanda];
											if(isset($visitas[$comanda->id_visita])) {
												$visita=$visitas[$comanda->id_visita];
												if(is_object($visita)) {
													if($visita->balcao==1) {
														$vendaBalcao+=$p->valor;
														$gorjetaBalcao+=$p->valor_taxa;
														
														if(!isset($vendaBalcaoMes[$mes])) $vendaBalcaoMes[$mes]=0;
														$vendaBalcaoMes[$mes]+=$p->valor-$p->valor_taxa;
														
														if(!isset($vendaBalcaoTurno[$caixa->turno])) $vendaBalcaoTurno[$caixa->turno]=0;
														$vendaBalcaoTurno[$caixa->turno]+=$p->valor;

														
														if(!isset($gorjetaBalcaoMes[$mes])) $gorjetaBalcaoMes[$mes]=0;
														$gorjetaBalcaoMes[$mes]+=$p->valor_taxa;
													} else {
														$vendaMesa+=$p->valor;
														$gorjetaMesa+=$p->valor_taxa;
														
														if(!isset($vendaMesaMes[$mes])) $vendaMesaMes[$mes]=0;
														$vendaMesaMes[$mes]+=$p->valor-$p->valor_taxa;

														
														if(!isset($gorjetaMesaMes[$mes])) $gorjetaMesaMes[$mes]=0;
														$gorjetaMesaMes[$mes]+=$p->valor_taxa;
													}

													
												} 
											}
										}
									}
								} 
							}
						}

						
						
						if($x->validado==2) {

							
							// realiza o calculo do percentual da diferenca entre sistema x financeiro
							$diferencaFinanceiroSistema=number_format($vendaConferidaPeloFinanceiro-($vendaBalcao+$vendaMesa),4,".","");
							$percentualDiferencaFinanceiroESistema=(($vendaBalcao+$vendaMesa)==0?0:$diferencaFinanceiroSistema/($vendaBalcao+$vendaMesa));
							$vendaMesaPercentual=number_format($vendaMesa*$percentualDiferencaFinanceiroESistema,2,".","");
							$vendaBalcaoPercentual=$vendaBalcao*$percentualDiferencaFinanceiroESistema;
							$vendaMesa+=$vendaMesaPercentual; //-> retirado mas tem que corrigir
							$vendaBalcao+=$vendaBalcaoPercentual;
							
							if(!isset($vendaMesaMes[$mes])) $vendaMesaMes[$mes]=0;
							if(!isset($vendaBalcaoMes[$mes])) $vendaBalcaoMes[$mes]=0;

							$vendaMesaMes[$mes]+=$vendaMesaPercentual;
							$vendaBalcaoMes[$mes]+=$vendaBalcaoPercentual;



							if(!isset($vendaMesaTurno[$x->turno])) $vendaMesaTurno[$x->turno]=0;
							if(!isset($vendaBalcaoTurno[$x->turno])) $vendaBalcaoTurno[$x->turno]=0;

							$vendaMesaTurno[$x->turno]+=$vendaMesaPercentual;
							$vendaBalcaoTurno[$x->turno]+=$vendaBalcaoPercentual;

							$caixaVendas['vendaBalcao']+=$vendaBalcao;
							$caixaVendas['vendaMesa']+=$vendaMesa;

							if(!isset($vendaTurno[$x->turno])) $vendaTurno[$x->turno]=0;
							$vendaTurno[$x->turno]+=$vendaBalcao+$vendaMesa;
						} else {

							//echo $vendaMesa;die();
							$caixaVendas['vendaBalcao']+=$vendaBalcao;
				
							$caixaVendas['vendaMesa']+=number_format($vendaMesa,2,".","");

							if(!isset($vendaTurno[$x->turno])) $vendaTurno[$x->turno]=0;
							$vendaTurno[$x->turno]+=$vendaBalcao+$vendaMesa;
						}
					}
					
				} 
				$caixaVendas['vendaMesaMes']=$vendaMesaMes;
				$caixaVendas['vendaBalcaoMes']=$vendaBalcaoMes;
				//echo json_encodvendaTurnoe($);echo "<HR>";
				$caixaVendas['vendaTurno']=$vendaTurno;
				$caixaVendas['vendaMesaTurno']=$vendaMesaTurno;
				$caixaVendas['vendaBalcaoTurno']=$vendaBalcaoTurno;


				return array('caixaVendas'=>$caixaVendas,
								'visitasBalcao'=>$visitasBalcao,
								'visitasBalcaoPessoas'=>$visitasBalcaoPessoas,
								'visitasMesa'=>$visitasMesa,
								'visitasMesaPessoas'=>$visitasMesaPessoas,
								'gorjetaMesa'=>$gorjetaMesa,
								'gorjetaBalcao'=>$gorjetaBalcao,
								'gorjetaMesaMes'=>$gorjetaMesaMes,
								'gorjetaBalcaoMes'=>$gorjetaBalcaoMes,
								'totalCaixas'=>count($_caixas));
			}
		}

		// retorna vendasDelivery
		function infoReceitasVendasDelivery($attr) {
			$_p=$this->prefixo;
			$sql=new Mysql();

			if(isset($attr['where']) and !empty($attr['where'])) $where=$attr['where'];

			if(!empty($where)) {	
				$totalVendas=$vendaDelivery=$vendaDeliveryPorCanal=array();
				$caixasTodosIDs=array();

				$taxaEntrega=array();
				$indicadoresCaixasAbertos=array();
				$indicadoresCaixasFechados=array();

				$totalVendasDeTodosOsCanais=isset($attr['multiplasUnidades'])?array():0;
				$totalPedidos=array();
				$cronometroTempoDespacho=array();
				$cronometroTempoProducao=array();
				$caixasIDs=array();
				$_caixas=array();

				$sql->consult($_p."delivery_caixas","*,date_format(data_caixa,'%d/%m/%Y') as data_caixaf",$where." order by data_caixa desc");
				while($x=mysqli_fetch_object($sql->mysqry)) {
					$registros[]=$x;
					if($x->validado>0) $caixasValidadoIDs[]=$x->id; // validado>0, busca valor validado pelo financeiro
					else if($x->fechado==1) $caixasFechadosIDs[]=$x->id; // fechado=1, busca valor validado pelo gerente
					else $caixasIDs[]=$x->id; // senao busca os pagamentos

					// separacao dos caixas para os indicadores
					if($x->fechado==1) $indicadoresCaixasFechados[]=$x->id; // vai somar o valor do gerente
					else $indicadoresCaixasAbertos[]=$x->id; // vai somar  o valor dos pagamentos

					$caixasTodosIDs[]=$x->id;
					$_caixas[$x->id]=$x;
				}

				// caixas validados
				$pagamentosValidado=array();
				if(isset($caixasValidadoIDs)) {
					$where="where id_caixa IN (".implode(",",$caixasValidadoIDs).") and lixo=0";
					$sql->consult($_p."delivery_caixas_canais_fechamentos", "*",$where);
					while($x=mysqli_fetch_object($sql->mysqry)) {
						if(!isset($pagamentosValidado[$x->id_caixa])) $pagamentosValidado[$x->id_caixa]=0;
						$pagamentosValidado[$x->id_caixa]+=$x->valor;
					}
				}

				// caixas fechados
				$pagamentosFechado=array();
				if(isset($caixasFechadosIDs)) {
					$sql->consult($_p."delivery_caixas_canais_fechamentos", "*","where id_caixa IN (".implode(",",$caixasFechadosIDs).") and lixo=0");

					while($x=mysqli_fetch_object($sql->mysqry)) {
						if(!isset($pagamentosFechado[$x->id_caixa])) $pagamentosFechado[$x->id_caixa]=0;
						$pagamentosFechado[$x->id_caixa]+=$x->gerencia_valor;
					}
				}

				// caixas abertos
				if(count($caixasIDs)>0) {
					$pedidosIDs=array();
					$pedidosObj=array();

					$sql->consult($_p."delivery_pedidos","*","where id_caixa IN (".implode(",",$caixasIDs).") and lixo=0");
					if($sql->rows) {
						while($x=mysqli_fetch_object($sql->mysqry)) {
							$pedidosIDs[]=$x->id;
							$pedidosObj[$x->id]=$x;
						}
					}

					$pagamentos=array();
					if(count($pedidosIDs)) {
						$sql->consult($_p."delivery_pagamentos","*","where id_pedido IN (".implode(",",$pedidosIDs).") and lixo=0");
						if($sql->rows) {
							while($x=mysqli_fetch_object($sql->mysqry)) {
								if(isset($pedidosObj[$x->id_pedido])) {
									$pedido=$pedidosObj[$x->id_pedido];
									$id_caixa=$pedido->id_caixa;
									if($pedido->status=="cancelado") {
										if(!isset($cancelados[$id_caixa])) $cancelados[$id_caixa]=array('qtd'=>0,'valor'=>0);
										$cancelados[$id_caixa]['qtd']++;
										$cancelados[$id_caixa]['valor']+=$x->valor;
									} else {
										if(!isset($pagamentos[$id_caixa])) $pagamentos[$id_caixa]=0;
										$pagamentos[$id_caixa]+=$x->valor;
									}
								}
							}
						}
					}
				}

				// periodo: caixas fechados
				if(count($indicadoresCaixasFechados)>0) {
					$sql->consult($_p."delivery_caixas_canais_fechamentos","id_caixa,gerencia_valor,id,id_canal","where id_caixa IN (".implode(",",$indicadoresCaixasFechados).") and lixo=0");
			
					while($x=mysqli_fetch_object($sql->mysqry)) {
						if(isset($_caixas[$x->id_caixa])) {
							$caixa=$_caixas[$x->id_caixa];
							$mes=und2(date('m',strtotime($caixa->data_caixa)));

							if(isset($attr['multiplasUnidades'])) {
						
								if(!isset($totalVendas[$caixa->id_unidade][$x->id_canal])) $totalVendas[$caixa->id_unidade][$x->id_canal]=0;
								if(!isset($vendaDelivery[$mes])) $vendaDelivery[$mes]=0;
								if(!isset($vendaDeliveryPorCanal[$x->id_canal][$mes])) $vendaDeliveryPorCanal[$x->id_canal][$mes]=0;

								$totalVendas[$caixa->id_unidade][$x->id_canal]+=$x->gerencia_valor;

							
								if(!isset($totalVendasDeTodosOsCanais[$caixa->id_unidade])) $totalVendasDeTodosOsCanais[$caixa->id_unidade]=0;
								$totalVendasDeTodosOsCanais[$caixa->id_unidade]+=$x->gerencia_valor;

								$vendaDelivery[$mes]+=$x->gerencia_valor;
								$vendaDeliveryPorCanal[$x->id_canal][$mes]+=$x->gerencia_valor;
							} else {
								if(!isset($totalVendas[$x->id_canal])) $totalVendas[$x->id_canal]=0;
								if(!isset($vendaDelivery[$mes])) $vendaDelivery[$mes]=0;
								if(!isset($vendaDeliveryPorCanal[$x->id_canal][$mes])) $vendaDeliveryPorCanal[$x->id_canal][$mes]=0;

								$totalVendas[$x->id_canal]+=$x->gerencia_valor;

								$totalVendasDeTodosOsCanais+=$x->gerencia_valor;
								
								$vendaDelivery[$mes]+=$x->gerencia_valor;
								$vendaDeliveryPorCanal[$x->id_canal][$mes]+=$x->gerencia_valor;
							}

						}
					}
				} 
				// periodo: caixas abertos
				if(count($indicadoresCaixasAbertos)>0) {
					$pedidosIDs=array();
					$pedidosObj=array();

					$sql->consult($_p."delivery_pedidos","status,id_canal,id_caixa,entregadorProprio,id","where id_caixa IN (".implode(",",$indicadoresCaixasAbertos).") and lixo=0");
					if($sql->rows) {
						while($x=mysqli_fetch_object($sql->mysqry)) {
							$pedidosIDs[]=$x->id;
							$pedidosObj[$x->id]=$x;
						}
					}

					$pagamentos=array();
					if(count($pedidosIDs)>0) {
						$sql->consult($_p."delivery_pagamentos","id,id_pedido,valor,valor_total","where id_pedido IN (".implode(",",$pedidosIDs).") and lixo=0"); 
						if($sql->rows) {
							while($x=mysqli_fetch_object($sql->mysqry)) {
								if(isset($pedidosObj[$x->id_pedido])) {
									$pedido=$pedidosObj[$x->id_pedido];
									if(isset($_caixas[$pedido->id_caixa])) {
										$caixa=$_caixas[$pedido->id_caixa];
										$mes=und2(date('m',strtotime($caixa->data_caixa)));
										if($pedido->status!="cancelado")  {

											if(isset($attr['multiplasUnidades'])) {

												if(!isset($totalVendas[$caixa->id_unidade][$pedido->id_canal])) $totalVendas[$caixa->id_unidade][$pedido->id_canal]=0;
												if(!isset($vendaDelivery[$mes])) $vendaDelivery[$mes]=0;
												if(!isset($vendaDeliveryPorCanal[$pedido->id_canal][$mes])) $vendaDeliveryPorCanal[$pedido->id_canal][$mes]=0;

												//if($pedido->id_canal==1) echo $x->id." -> ".$x->valor_total." ".$x->valor."<BR>";
												if($pedido->entregadorProprio==1) {
													$totalVendas[$caixa->id_unidade][$pedido->id_canal]+=$x->valor_total;
													$vendaDelivery[$mes]+=$x->valor;
													$vendaDeliveryPorCanal[$pedido->id_canal][$mes]+=$x->valor;

													if(!isset($totalVendasDeTodosOsCanais[$caixa->id_unidade])) $totalVendasDeTodosOsCanais[$caixa->id_unidade]=0;
													$totalVendasDeTodosOsCanais[$caixa->id_unidade]+=$x->valor_total;
												} else {
													$totalVendas[$caixa->id_unidade][$pedido->id_canal]+=$x->valor;
													$vendaDelivery[$mes]+=$x->valor;
													$vendaDeliveryPorCanal[$pedido->id_canal][$mes]+=$x->valor;
													
													if(!isset($totalVendasDeTodosOsCanais[$caixa->id_unidade])) $totalVendasDeTodosOsCanais[$caixa->id_unidade]=0;
													$totalVendasDeTodosOsCanais[$caixa->id_unidade]+=$x->valor;
												}
											} else {
												if(!isset($totalVendas[$pedido->id_canal])) $totalVendas[$pedido->id_canal]=0;
												if(!isset($vendaDelivery[$mes])) $vendaDelivery[$mes]=0;
												if(!isset($vendaDeliveryPorCanal[$pedido->id_canal][$mes])) $vendaDeliveryPorCanal[$pedido->id_canal][$mes]=0;

												
												//if($pedido->id_canal==1) echo $x->id." -> ".$x->valor_total." ".$x->valor."<BR>";
												if($pedido->entregadorProprio==1) {
													$totalVendas[$pedido->id_canal]+=$x->valor_total;
													$vendaDelivery[$mes]+=$x->valor;
													$vendaDeliveryPorCanal[$pedido->id_canal][$mes]+=$x->valor;

													$totalVendasDeTodosOsCanais+=$x->valor_total;
												} else {
													$totalVendas[$pedido->id_canal]+=$x->valor;
													$vendaDelivery[$mes]+=$x->valor;
													$vendaDeliveryPorCanal[$pedido->id_canal][$mes]+=$x->valor;
													
													$totalVendasDeTodosOsCanais+=$x->valor;
												}
											}

										}	
									}
								}
							}
						}
					}
				} 
				// periodo: taxa de entrega
				if(count($caixasTodosIDs)>0) {
					$pedidosIDs=array();
					$pedidosObj=array();

					$sql->consult($_p."delivery_pedidos","*","where id_caixa IN (".implode(",",$caixasTodosIDs).") and lixo=0");
					if($sql->rows) {
						while($x=mysqli_fetch_object($sql->mysqry)) {
							if(!isset($totalPedidos[$x->id_canal])) $totalPedidos[$x->id_canal]=0;
							$totalPedidos[$x->id_canal]++;
							
							$pedidosIDs[]=$x->id;
							$pedidosObj[$x->id]=$x;

							if($x->entregadorProprio==0 and $x->data_finalizado!="0000-00-00 00:00:00") {
								$cronometroTempoDespacho[$x->id_canal][] = strtotime($x->data_finalizado)-strtotime($x->data);
							} else if($x->entregadorProprio==1 and $x->data_entregador!="0000-00-00 00:00:00") {
								$cronometroTempoDespacho[$x->id_canal][] = strtotime($x->data_entregador)-strtotime($x->data);
							}

							if($x->data_produzido!="0000-00-00 00:00:00") $cronometroTempoProducao[$x->id_canal][] = strtotime($x->data_produzido)-strtotime($x->data);
							
						}
					}

					if(count($pedidosIDs)) {
						$sql->consult($_p."delivery_pagamentos","*","where id_pedido IN (".implode(",",$pedidosIDs).") and lixo=0");
						//echo "where id_pedido IN (".implode(",",$pedidosIDs).") and lixo=0 -> ".$sql->rows;
						if($sql->rows) {
							while($x=mysqli_fetch_object($sql->mysqry)) {
								if(isset($pedidosObj[$x->id_pedido])) {
									$pedido=$pedidosObj[$x->id_pedido];
									if($pedido->status!="cancelado")  {

										$_txEntrega=0;
										// se a taxa de entrega estiver zerada no pagamento, procura no pedido
										if($x->taxa_entrega==0) {
											$_txEntrega=$pedido->valor_frete;
										} else {
											$_txEntrega=$x->taxa_entrega;
										}
										//echo $pedido->id_canal." -> ".$x->taxa_entrega." -> $pedido->valor_frete<BR>";

										if(!isset($taxaEntrega[$pedido->id_canal])) $taxaEntrega[$pedido->id_canal]=0;
										$taxaEntrega[$pedido->id_canal]+=$_txEntrega;
									}	

								} 
							}
						}
					}
				}


				$obj=array('totalPedidos'=>$totalPedidos,
							'totalVendasDeTodosOsCanais'=>$totalVendasDeTodosOsCanais,
							'cronometroTempoDespacho'=>$cronometroTempoDespacho,
							'cronometroTempoProducao'=>$cronometroTempoProducao,
							'totalVendas'=>$totalVendas,
							'taxaEntrega'=>$taxaEntrega,
							'vendaDelivery'=>$vendaDelivery,
							'vendaDeliveryPorCanal'=>$vendaDeliveryPorCanal);

				return $obj;
			}
		}

		// retorna vendasB2B
		function infoReceitasVendasB2B($attr) {

			$_p=$this->prefixo;
			$sql=new Mysql();

			if(isset($attr['where']) and !empty($attr['where'])) $where=$attr['where'];

			if(!empty($where)) {	

				$vendaB2B=array();
				$b2b=array();
				$_b2b=array();
				$where=str_replace("data","data_b2b",$where)." and lixo=0";

				$sql->consult($_p."b2b","id,data_b2b,valor_frete,id_unidade",$where);
				//echo $where."-> ".$sql->rows."<BR>";
				if($sql->rows) {
					while($x=mysqli_fetch_object($sql->mysqry)) {
						$_b2b[$x->id]=$x;
						$x->mes=und2(date('m',strtotime($x->data_b2b)));
						$b2b[$x->mes][]=$x->id;
						if(isset($attr['multiplasUnidades'])) {
							if(!isset($vendaB2B[$x->id_unidade][$x->mes])) $vendaB2B[$x->id_unidade][$x->mes]=0;
							$vendaB2B[$x->id_unidade][$x->mes]+=$x->valor_frete;
						} else {

						}
					}
				}

				//echo "<HR>";

				for($mes=1;$mes<=12;$mes++) {
					if(isset($b2b[$mes]) and count($b2b[$mes])>0) {

						$where="where id_b2b in (".implode(",",$b2b[$mes]).") and lixo=0";
						$sql->consult($_p."b2b_itens","*",$where);
						//echo "rows -> ".$sql->rows."<br />";
						if($sql->rows) {
							while($x=mysqli_fetch_object($sql->mysqry)) {
								$b2bObj=isset($_b2b[$x->id_b2b])?$_b2b[$x->id_b2b]:'';

								if(is_object($b2bObj)) {
									//echo $mes."-----> ".$x->id_b2b."->".$x->valor." - ".$x->quantidade." = ".(($x->valor*$x->quantidade)-$x->desconto)."<br />";
									if(isset($attr['multiplasUnidades'])) {
										if(!isset($vendaB2B[$b2bObj->id_unidade][$mes])) $vendaB2B[$b2bObj->id_unidade][$mes]=0;
										$vendaB2B[$b2bObj->id_unidade][$mes]+=($x->valor*$x->quantidade)-$x->desconto;
									} else {
										if(!isset($vendaB2B[$mes])) $vendaB2B[$mes]=0;
										$vendaB2B[$mes]+=($x->valor*$x->quantidade)-$x->desconto;
									}
								}
							}
						}
					}
				}
				//var_dump($vendaB2B);	
				return $vendaB2B;
			}
		}

		// retornas as vendasB2B
		function infoReceitas($attr) {

			$_p=$this->prefixo;
			$sql=new Mysql();

			


			if(isset($attr['data_inicio']) and isset($attr['data_fim'])) {
				$dtInicio=$attr['data_inicio'];
				$dtFim=$attr['data_fim'];

				$anoInicio=date('Y',strtotime($dtInicio));
				$mesInicio=date('m',strtotime($dtInicio));

				$anoFim=date('Y',strtotime($dtInicio));
				$mesFim=date('m',strtotime($dtFim));


			} else {
				$anoInicio=(isset($attr['anoInicio']) and is_numeric($attr['anoInicio']))?$attr['anoInicio']:0;
				$mesInicio=(isset($attr['mesInicio']) and is_numeric($attr['mesInicio']))?$attr['mesInicio']:0;

				$anoFim=(isset($attr['anoFim']) and is_numeric($attr['anoFim']))?$attr['anoFim']:0;
				$mesFim=(isset($attr['mesFim']) and is_numeric($attr['mesFim']))?$attr['mesFim']:0;

				$dtInicio=date('Y-m-d',strtotime($anoInicio."-01-01"));
				$dtFim=date('Y-m-t',strtotime(date($anoFim."-12-01")));
			}



			$unidades=(isset($attr['unidades']) and is_array($attr['unidades']) and count($attr['unidades'])>0)?$attr['unidades']:'';

			if(!empty($unidades) and $anoInicio>0 and $mesInicio>0 and $anoFim>0 and $mesInicio>0) {

				$receitas=array();

				$whereGeralAno="WHERE data>='".$dtInicio." 00:00:00' and data<='".$dtFim." 23:59:59' and id_unidade IN (".implode(",",$unidades).")"; 

				// vendaMesa, vendaBalcao, gorjetaVendaMesa, gorjetaVendaBalcao 
				$vendasLoja=$this->infoReceitasVendasLojas(array("where"=>$whereGeralAno));
			

				// vendaDelivery 
				$vendasDelivery=$this->infoReceitasVendasDelivery(array("where"=>$whereGeralAno));


				$vendaB2B=$this->infoReceitasVendasB2B(array("where"=>$whereGeralAno));
				// vendaB2B
				/*$vendaB2B=array();
				$b2b=array();
				$where=str_replace("data","data_b2b",$whereGeralAno)." and lixo=0";
				$sql->consult($_p."b2b","*",$where);
				if($sql->rows) {
					while($x=mysqli_fetch_object($sql->mysqry)) {
						$x->mes=und2(date('m',strtotime($x->data)));
						$b2b[$x->mes][]=$x->id;
						if(!isset($vendaB2B[$x->mes])) $vendaB2B[$x->mes]=0;
						$vendaB2B[$x->mes]+=$x->valor_frete;
					}
				}

				for($mes=1;$mes<=12;$mes++) {
					if(isset($b2b[$mes]) and count($b2b[$mes])>0) {
						$where="where id_b2b in (".implode(",",$b2b[$mes]).") and lixo=0";
						$sql->consult($_p."b2b_itens","*",$where);
						if($sql->rows) {
							while($x=mysqli_fetch_object($sql->mysqry)) {
								//echo $x->id_b2b."-.".$x->valor." - ".$x->quantidade." = ".(($x->valor*$x->quantidade)-$x->desconto)."<br />";
								if(!isset($vendaB2B[$mes])) $vendaB2B[$mes]=0;
								$vendaB2B[$mes]+=($x->valor*$x->quantidade)-$x->desconto;
							}
						}
					}
				}*/
				

				$receitas=array('vendaMesaMes'=>$vendasLoja['caixaVendas']['vendaMesaMes'],
								'vendaBalcaoMes'=>$vendasLoja['caixaVendas']['vendaBalcaoMes'],
								'gorjetaBalcaoMes'=>$vendasLoja['gorjetaBalcaoMes'],
								'gorjetaMesaMes'=>$vendasLoja['gorjetaMesaMes'],
								'vendaDelivery'=>$vendasDelivery['vendaDelivery'],
								'vendaB2B'=>$vendaB2B);

				return $receitas;
			}
		}


		// retorna as despesas 
		function infoDespesas($attr) {
			$_p=$this->prefixo;
			$sql =new Mysql();

			$despesas=array();

			if(isset($attr['data_inicio']) and isset($attr['data_fim'])) {
				$dtInicio=$attr['data_inicio'];
				$dtFim=$attr['data_fim'];

				$anoInicio=date('Y',strtotime($dtInicio));
				$mesInicio=date('m',strtotime($dtInicio));

				$anoFim=date('Y',strtotime($dtInicio));
				$mesFim=date('m',strtotime($dtFim));


			} else {
				$anoInicio=(isset($attr['anoInicio']) and is_numeric($attr['anoInicio']))?$attr['anoInicio']:0;
				$mesInicio=(isset($attr['mesInicio']) and is_numeric($attr['mesInicio']))?$attr['mesInicio']:0;

				$anoFim=(isset($attr['anoFim']) and is_numeric($attr['anoFim']))?$attr['anoFim']:0;
				$mesFim=(isset($attr['mesFim']) and is_numeric($attr['mesFim']))?$attr['mesFim']:0;

				$dtInicio=date('Y-m-d',strtotime($anoInicio."-01-01"));
				$dtFim=date('Y-m-t',strtotime(date($anoFim."-12-01")));
			}

			$unidades=(isset($attr['unidades']) and is_array($attr['unidades']) and count($attr['unidades'])>0)?$attr['unidades']:'';

			//var_dump($attr['unidades']);die();
			if(!empty($unidades) and $anoInicio>0 and $mesInicio>0 and $anoFim>0 and $mesInicio>0) {

				/*$whereGeralAno="WHERE data>='".date('Y-m-d',strtotime($anoFim."-01-01"))." 00:00:00' and data<='".date('Y-m-t',strtotime(date($anoFim."-12-01")))." 23:59:59' and id_unidade IN (".implode(",",$unidades).")";
				$whereGeralDataAno="WHERE data>='".date('Y-m-d',strtotime($anoFim."-01-01"))." 00:00:00' and data<='".date('Y-m-t',strtotime(date($anoFim."-12-01")))." 23:59:59'";
				$whereGeralAnoMesAno="WHERE ano>='$anoInicio' and mes>='$mesInicio' and ano<='$anoFim' and mes<='$mesFim' and id_unidade IN (".implode(",",$unidades).")";*/

				$whereGeralAno="WHERE data>='".$dtInicio." 00:00:00' and data<='".$dtFim." 23:59:59' and id_unidade IN (".implode(",",$unidades).")";

				$whereGeralDataAno="WHERE data>='".$dtInicio." 00:00:00' and data<='".$dtFim." 23:59:59'";
				$whereGeralAnoMesAno="WHERE ano>='$anoInicio' and mes>='$mesInicio' and ano<='$anoFim' and mes<='$mesFim' and id_unidade IN (".implode(",",$unidades).")";

				$despesasValores=array();
				$despesasValoresUnidades=array();
				$despesasValoresSub=array();
				$categorias=array(-1);

				// entradas e b2b
				$b2b=array(-1);
				$b2bIDs=array(-1);
				$entradas=array(-1);
				$_entradas=array();
				$where=str_replace("data","data_nota",$whereGeralAno);
				$sql->consult($_p."financeiro_entradas","*",$where." and lixo=0");
			
				$total=array();
				$descontos=array();
				if($sql->rows) {
					while($x=mysqli_fetch_object($sql->mysqry)) {

						//echo $x->id."->".$x->id_b2b."<BR>";
						$mes=und2(date('m',strtotime($x->data_nota)));
						$_entradas[$x->id]=$x;
						if(!isset($total[$mes])) $total[$mes]=0;
						$total[$mes]+=$x->valor;

						if($x->id_b2b==0) {
							$entradas[$mes][]=$x->id;
							if(!isset($descontos[$mes])) $descontos[$mes]=0;
							$descontos[$mes]+=$x->desconto;
						}
						else { 
							$b2b[$mes][]=$x->id_b2b;
							$b2bIDs[]=$x->id_b2b;
						}
					}
				}


				$_b2b=array();
				$sql->consult($_p."b2b","*","where id in (".implode(",",$b2bIDs).") and lixo=0");
				while ($x=mysqli_fetch_object($sql->mysqry)) {
					$_b2b[$x->id]=$x;
				}

				
				$produtos=array('produtosIDs'=>array(),'produtos'=>array(),
								'outrosServicosEProdutosIDs'=>array(),'outrosServicosEProdutos'=>array());


				
				$produtosUnidades=array('produtosIDs'=>array(),'produtos'=>array(),
								'outrosServicosEProdutosIDs'=>array(),'outrosServicosEProdutos'=>array());

				// tabela de link entre produto x categoria
				$produtosTiposCategorias=array();
				$sql->consult($_p."produtos_tipos_categoriaFinanceiro","*","");
				while($x=mysqli_fetch_object($sql->mysqry)) {
					if($x->id_categoria>0) $produtosTiposCategorias[$x->tipo]=$x->id_categoria;
				}

				$_produtos=array();
				$_outrodProdutosEServicos=array();
				$produtosCategorias=array();

				for($mes=1;$mes<=12;$mes++) {

					// retorna os itens das entradas
					if(isset($entradas[$mes])) {
						$where="WHERE id_entrada IN (".implode(",",$entradas[$mes]).")";

						
						$sql->consult($_p."financeiro_entradas_itens","tipo,id_produtoPadrao,id_produto,valor,quantidade,id_entrada",$where);
						if($sql->rows) {
							while($x=mysqli_fetch_object($sql->mysqry)) {
								if(isset($_entradas[$x->id_entrada])) {
									$e=$_entradas[$x->id_entrada];

									$x->id_unidade=$e->id_unidade;
									if($x->tipo=="pc") {
										$produtos['produtosIDs'][$mes][]=$x->id_produtoPadrao;
										$produtos['produtos'][$mes][$x->id_produtoPadrao][]=$x;

										$produtosUnidades['produtosIDs'][$mes][$x->id_unidade][]=$x->id_produtoPadrao;
										$produtosUnidades['produtos'][$mes][$x->id_produtoPadrao][$x->id_unidade][]=$x;
									} else if($x->tipo=="osp-ninventariado" or $x->tipo=="osp-inventariado") {
										$produtos['outrosServicosEProdutosIDs'][$mes][]=$x->id_produto;
										$produtos['outrosServicosEProdutos'][$mes][$x->id_produto][]=$x;

										$produtosUnidades['outrosServicosEProdutosIDs'][$mes][$x->id_unidade][]=$x->id_produto;
										$produtosUnidades['outrosServicosEProdutos'][$mes][$x->id_produto][$x->id_unidade][]=$x;
									} 
								} 
							}
						}
					}

					// retorna itens b2b
					if(isset($b2b[$mes])) {

						// retorna os itens das entradas
						$where="WHERE id_b2b IN (".implode(",",$b2b[$mes]).") and lixo=0";
						//$sql->consult($_p."b2b_itens","sum(quantidade*valor) as total",$where);//id_item,valor,quantidade",$where);
						//$t=mysqli_fetch_object($sql->mysqry);
						$sql->consult($_p."b2b_itens","id_item,valor,quantidade,id_b2b",$where);
						
						//echo $mes."->".$sql->rows."<BR>";
						if($sql->rows) {
							while($x=mysqli_fetch_object($sql->mysqry)) {
								if(isset($_b2b[$x->id_b2b])) {
									$e=$_b2b[$x->id_b2b];
									$x->id_unidade=$e->id_unidade;

									if($e->tipo=="servico") {

										$produtos['outrosServicosEProdutosIDs'][$mes][]=$x->id_item;
										$produtos['outrosServicosEProdutos'][$mes][$x->id_item][]=$x;
									} else {
										$produtos['produtosIDs'][$mes][]=$x->id_item;
										$produtos['produtos'][$mes][$x->id_item][]=$x;

										$produtosUnidades['produtosIDs'][$mes][$x->id_unidade][]=$x->id_item;
										$produtosUnidades['produtos'][$mes][$x->id_item][$x->id_unidade][]=$x;
									} 

									
								} 
							}
						}
					}

					// capta todas as categorias contabeis dos tipos dos produtos de compras encontrados nas entradas (produto tipo=venda nao entra na despesa, por isso => $produtosTiposCategorias[venda]=0)
					if(isset($produtos['produtosIDs'][$mes]) and count($produtos['produtosIDs'][$mes])>0) {
						$where="where id in (".implode(",",$produtos['produtosIDs'][$mes]).")";
						$sql->consult($_p."produtos","id_categoria,titulo,id,tipo",$where);
						if($sql->rows) {
							while($x=mysqli_fetch_object($sql->mysqry)) {
								$_produtos[$x->id]=$x;
								if(isset($produtosTiposCategorias[$x->tipo])) {
									$categorias[$produtosTiposCategorias[$x->tipo]]=$produtosTiposCategorias[$x->tipo];
								} else {
									//echo "$x->id / $x->tipo / $x->titulo<br />"; // produto de venda nao entra nas despesas
								}
							}
						}
					} 


					// capta todas as categorias contabeis dos outros produtos e servicos encontrado nas entradas
					if(isset($produtos['outrosServicosEProdutosIDs'][$mes])) {
						$where="where id in (".implode(",",$produtos['outrosServicosEProdutosIDs'][$mes]).")";
						$sql->consult($_p."financeiro_produtosdecompra","id_categoria,titulo,id",$where);
						//echo $where."->".$sql->rows;
						if($sql->rows) {
							while($x=mysqli_fetch_object($sql->mysqry)) {
								$_outrodProdutosEServicos[$x->id]=$x;
								$categorias[$x->id_categoria]=$x->id_categoria;
							}
						}
					}

					// TAXAS e IMPOSTOS do FLUXO FINANCEIRO
					$where="WHERE data_vencimento>='".date('Y-m-d',strtotime("$anoFim-$mes-01"))." 00:00:00' and 
									data_vencimento<='".date('Y-m-t',strtotime(date("$anoFim-$mes-01")))." 23:59:59' and 
									id_unidade IN (".implode(",",$unidades).") and 
									valor>0 and 
									id_agrupamento_conciliacao>0";

					$sql->consult($_p."financeiro_fluxo","sum(valor-valor_original) as taxa,
															sum(juros) as juros,
															sum(multa) as multa",$where);
					//echo "$where -> $sql->rows";
					$t=mysqli_fetch_object($sql->mysqry);

					/*echo "<BR>Taxa: $t->taxa";
					echo "<BR>Juros: $t->juros";
					echo "<BR>Desconto: $t->desconto";
					echo "<BR>Multa: $t->multa";*/

					$categorias[122]=122; // taxa 
					$categorias[191]=191; // juros e multa

					if(!isset($despesasValores[$mes][121])) $despesasValores[$mes][121]=0;
					$despesasValores[$mes][121]+=$t->taxa;


					if(!isset($despesasValores[$mes][170])) $despesasValores[$mes][170]=0;
					$despesasValores[$mes][170]+=$t->juros+$t->multa;

					// para subcategorias
					if(!isset($despesasValoresSubcategoria[$mes][122])) $despesasValoresSubcategoria[$mes][122]=0;
					$despesasValoresSubcategoria[$mes][122]+=$t->taxa;

					if(!isset($despesasValoresSubcategoria[$mes][191])) $despesasValoresSubcategoria[$mes][191]=0;
					$despesasValoresSubcategoria[$mes][191]+=$t->juros+$t->multa;
				}

				# PAGAMENTOS DP #
					$pagamentosDP=array('vale'=>array('id_categoria'=>140,'tabela'=>$_p.'financeiro_vales'),
														'vt'=>array('id_categoria'=>151,'tabela'=>$_p.'financeiro_vts'),
														'ferias'=>array('id_categoria'=>180,'tabela'=>$_p.'colaboradores_ferias'),
														'13'=>array('id_categoria'=>179,'tabela'=>$_p.'rh_13'),
														'folha'=>array('id_categoria'=>147,'tabela'=>$_p.'rh_folhadepagamentos'),
														'rescisao'=>array('id_categoria'=>146,'tabela'=>$_p.'rh_rescisao'),
														'freelancer'=>array('id_categoria'=>143,'tabela'=>$_p.'gerenciamento_calendario'));

					// retorna id de colaboradores das unidades selecionadas no filtro 
					$idColaboradoresDaUnidade=array(-1);
					$where="where id_cnpj IN (".implode(",",$unidades).") and lixo=0";
					$sql->consult($_p."colaboradores","id,id_cnpj",$where);
					while($x=mysqli_fetch_object($sql->mysqry)) {
						$idColaboradoresDaUnidade[]=$x->id;
					}

					$despesasValoresDP=array();
					foreach($pagamentosDP as $pagTipo=>$arg) {
						$pagamentoIDCategoria=$arg['id_categoria'];

						for($mes=1;$mes<=12;$mes++) {
							//echo "<H1>$mes</h1>";
							$dtInicio=date('Y-m-d',strtotime($anoFim."-".$mes."-01"));
							$dtFim=date('Y-m-t',strtotime($anoFim."-".$mes."-01"));

							$whereDP="WHERE data>='".$dtInicio." 00:00:00' and data<='".$dtFim." 23:59:59' and id_unidade IN (".implode(",",$unidades).")";
							$whereDPSemUnidades="WHERE data>='".$dtInicio." 00:00:00' and data<='".$dtFim." 23:59:59'";
							$whereDPAnoMes="WHERE ano>='$anoInicio' and mes>='".d2($mes)."' and ano<='$anoFim' and mes<='".d2($mes)."' and id_unidade IN (".implode(",",$unidades).")";
							
							$pagamentosValor=0;
							if($pagTipo=="vale") {
								$where = str_replace("data","data_vencimento",str_replace("id_unidade","id_cnpj",$whereDP));
								$where .= " and lixo=0 and valor<0";
								$sql->consult($arg['tabela'],"sum(valor) as total",$where);
								$t=mysqli_fetch_object($sql->mysqry);
								$pagamentosValor+=$t->total;
							} else if($pagTipo=="vt") {
								$where = str_replace("data","data_vencimento",$whereDP);
								$where .= " and lixo=0";
								$sql->consult($arg['tabela'],"sum(valor) as total",$where);
								$t=mysqli_fetch_object($sql->mysqry);
								$pagamentosValor+=$t->total;
							} else if($pagTipo=="ferias") {

								$where = str_replace("data","pago_data",$whereDPSemUnidades);
								$where .= " and pago=1 and id_colaborador IN (".implode(",",$idColaboradoresDaUnidade).") and lixo=0";
								$sql->consult($arg['tabela'],"sum(financeiro_liquido) as total",$where);
								//echo "$where -> $sql->rows <br />";
								$t=mysqli_fetch_object($sql->mysqry);
								$pagamentosValor+=$t->total;
							} else if($pagTipo=="13") {

								$where = str_replace("data","pago_data",str_replace("id_unidade","id_cnpj",$whereDP));
								$where .= " and pago=1 and id_colaborador IN (".implode(",",$idColaboradoresDaUnidade).") and lixo=0";
								$sql->consult($arg['tabela'],"sum(financeiro_totalliquido) as total",$where);
								//awecho "$where -> $sql->rows <br />";
								$t=mysqli_fetch_object($sql->mysqry);
								$pagamentosValor+=$t->total;
							} else if($pagTipo=="folha") {
								$where = $whereDPAnoMes;
								$where.=" and lixo=0";
								$sql->consult($arg['tabela'],"*",$where);
								//echo $where."->".$sql->rows;
								if($sql->rows) {
									$folhasIDs=array(-1);
									while($f=mysqli_fetch_object($sql->mysqry)) $folhasIDs[]=$f->id;

									$sql->consult($arg['tabela']."_itens","sum(financeiro_totalliquido) as total","where id_folhadepagamento IN (".implode(",",$folhasIDs).") and pago=1 and lixo=0");
									$t=mysqli_fetch_object($sql->mysqry);
									$pagamentosValor+=$t->total;
									//echo "where id_folhadepagamento IN (".implode(",",$folhasIDs).") and lixo=0 ->".$pagamentosValor;
								}
							} else if($pagTipo=="rescisao") {
								$where = $whereDPSemUnidades;
								$where.=" and id_cnpj IN (".implode(",",$unidades).") and lixo=0";
								$sql->consult($arg['tabela'],"sum(financeiro_totalliquido) as total",$where);
								//echo "$where -> $sql->rows <br />";
								$t=mysqli_fetch_object($sql->mysqry);
								$pagamentosValor+=$t->total;
							} else if($pagTipo=="freelancer") {
								
								$where = str_replace("data","data_calendario",$whereDP);
								$where .= " and id_status=9 and lixo=0"; // -> id_status=9: freelancer trabalho
								$sql->consult($arg['tabela'],"*",$where);
								$idCalendarios=array(-1);
								if($sql->rows) {
									while($x=mysqli_fetch_object($sql->mysqry)) $idCalendarios[]=$x->id;
								}

								$where="where id_calendario IN (".implode(",",$idCalendarios).") and lixo=0";
								$sql->consult($_p."vendas_caixas_pagamentos","sum(valor) as total",$where);
								$t=mysqli_fetch_object($sql->mysqry);
								$pagamentosValor+=$t->total;
							}
							//echo $pagTipo."->".$pagamentoIDCategoria."->".$pagamentosValor."<BR>";
							$despesasValoresDP[$mes][$pagamentoIDCategoria]=$pagamentosValor<0?$pagamentosValor*-1:$pagamentosValor;
						}
						//echo $pagamentoIDCategoria."=>".$pagamentosValor."<br />";
						$categorias[$pagamentoIDCategoria]=$pagamentoIDCategoria;
					}

					$categorias[]=123;
					// retorna todas as categorias contabeis encontradas
					$where="where id IN (".implode(",",$categorias).")";
					$categoriasIDs=array(-1);
					$_subcategorias=array();
					$sql->consult($_p."financeiro_categorias","*",$where);
					//echo $where."->$sql->rows";
					while($x=mysqli_fetch_object($sql->mysqry)) {
						$categoriasIDs[$x->id_categoria]=$x->id_categoria;
						$_subcategorias[$x->id]=$x;

					}

					// retorna todas as categorias gerais das subcategorias encontradas
					$where="where id IN (".implode(",",$categoriasIDs).")";
					//echo $where;
					$_categorias=array();
					$sql->consult($_p."financeiro_categorias","*",$where);
					//echo $where."->$sql->rows";
					while($x=mysqli_fetch_object($sql->mysqry)) {
						$_categorias[$x->id]=$x;
					}

					// se possuir despesas do Pagamentos DP (faz a conversao de subcategoria para categoria)
					if(count($despesasValoresDP)>0) {
						foreach($despesasValoresDP as $mes=>$regs) {
							$mes=und2($mes);
						//	echo "<h1>$mes</h1>";
							foreach($regs as $id_subcategoria=>$valor) {
								if(isset($_subcategorias[$id_subcategoria])) {
									$subcategoria=$_subcategorias[$id_subcategoria];
								//	echo $subcategoria->id_categoria;
									if(isset($_categorias[$subcategoria->id_categoria])) {
										$categoria=$_categorias[$subcategoria->id_categoria];
										//echo " -> ".$subcategoria->titulo." -> ".$categoria->titulo." ($categoria->id) -> $valor <Br>";
										//if(!isset($despesasValores[$mes][$categoria->id])) $despesasValores[$mes][$categoria->id]=0;
										//$despesasValores[$mes][$categoria->id]+=$valor;

										if(!isset($despesasValores[$mes][$categoria->id])) $despesasValores[$mes][$categoria->id]=0;
										$despesasValores[$mes][$categoria->id]+=$valor;

										if(!isset($despesasValoresSubcategoria[$mes][$subcategoria->id])) $despesasValoresSubcategoria[$mes][$subcategoria->id]=0;
										$despesasValoresSubcategoria[$mes][$subcategoria->id]+=$valor;;
										//$total+=$v->valor*$v->quantidade;
									} else {
										//echo "erro";
									}
								} else {
									//echo "erro";
								}
							}
						}
					}
				# PAGAMENTOS DP #

				// retorna todas as categorias gerais das subcategorias encontradas
				$where="where id IN (".implode(",",$categoriasIDs).")";
				//echo $where;
				$_categorias=array();
				$sql->consult($_p."financeiro_categorias","*",$where);
				//echo $where."->$sql->rows";
				while($x=mysqli_fetch_object($sql->mysqry)) {
					$_categorias[$x->id]=$x;
				}

				// taxa de delivery
				$where=str_replace("data","data_caixa",$whereGeralAno);
				$sql->consult($_p."delivery_caixas","*",$where." and lixo=0");
				while($x=mysqli_fetch_object($sql->mysqry)) {
					//echo $x->taxa."<BR>";
					$mes=und2(date('m',strtotime($x->data)));

					$id_subcategoria=123; // id_subcategoria => TARIFA APP DELIVERY
					if(isset($_subcategorias[$id_subcategoria])) { 
						$subcategoria=$_subcategorias[$id_subcategoria];
						if(isset($_categorias[$subcategoria->id_categoria])) { 
							$categoria=$_categorias[$subcategoria->id_categoria];
							
							if(!isset($despesasValores[$mes][$categoria->id])) $despesasValores[$mes][$categoria->id]=0;
							$despesasValores[$mes][$categoria->id]+=$x->taxa;

							if(!isset($despesasValoresSubcategoria[$mes][$subcategoria->id])) $despesasValoresSubcategoria[$mes][$subcategoria->id]=0;
							$despesasValoresSubcategoria[$mes][$subcategoria->id]+=$x->taxa;;

						} 
					}
				}

				//var_dump($despesasValoresSubcategoria);

				for($mes=1;$mes<=12;$mes++) {
					$mes=und2($mes);

					// capta valores dos produtos de compra (tipo=pc)
					if(isset($produtos['produtos'][$mes]) and count($produtos['produtos'][$mes])>0) {

						foreach($produtos['produtos'][$mes] as $id_produto=>$regs) {
							//echo $id_produto."->".count($regs)."<BR>";
							if(isset($_produtos[$id_produto])) {
								$produto=$_produtos[$id_produto];
								//echo "<b>$produto->titulo</b><BR>";
								foreach($regs as $v) {
									if(isset($produtosTiposCategorias[$produto->tipo])) {
										$id_subcategoria=$produtosTiposCategorias[$produto->tipo];
										if(isset($_subcategorias[$id_subcategoria])) {
											$subcategoria=$_subcategorias[$id_subcategoria];
											//echo $subcategoria->id_categoria;
											if(isset($_categorias[$subcategoria->id_categoria])) {
												$categoria=$_categorias[$subcategoria->id_categoria];
												//echo $produto->tipo."->".$v->tipo." -> ".$subcategoria->titulo." -> ".$categoria->titulo."<Br>";

												if(!isset($despesasValores[$mes][$categoria->id])) $despesasValores[$mes][$categoria->id]=0;
												$despesasValores[$mes][$categoria->id]+=$v->valor*$v->quantidade;

												if(!isset($despesasValoresSubcategoria[$mes][$subcategoria->id])) $despesasValoresSubcategoria[$mes][$subcategoria->id]=0;
												$despesasValoresSubcategoria[$mes][$subcategoria->id]+=$v->valor*$v->quantidade;



											} else {
												//echo "erro ";
											}
										} else {
											//echo "erro<br>";
										}
									} else {
										//echo "erro-> $produto->tipo $produto->id $produto->titulo<BR>";
									}
								}
							} else {
								//echo "erro (id_produto=$id_produto)";
							}
							//echo "<HR>";
						} 
					}

					// capta valores dos produtos e outros servicos (pc-inventariado e pc-ninventariado)
					if(isset($produtos['outrosServicosEProdutos'][$mes]) and count($produtos['outrosServicosEProdutos'][$mes])>0) {

						foreach($produtos['outrosServicosEProdutos'][$mes] as $id_produto=>$regs) {
							//echo $id_produto."->".count($regs)."<BR>";
							if(isset($_outrodProdutosEServicos[$id_produto])) {
								$produto=$_outrodProdutosEServicos[$id_produto];
								//echo "<b>$produto->titulo --></b><BR>";
								foreach($regs as $v) {
									$id_subcategoria=$produto->id_categoria;
									//echo $id_subcategoria."<BR>";
									if(isset($_subcategorias[$id_subcategoria])) {
										$subcategoria=$_subcategorias[$id_subcategoria];
										if(isset($_categorias[$subcategoria->id_categoria])) {
											$categoria=$_categorias[$subcategoria->id_categoria];

											if(!isset($despesasValores[$mes][$categoria->id])) $despesasValores[$mes][$categoria->id]=0;
											$despesasValores[$mes][$categoria->id]+=$v->valor*$v->quantidade;


											if(!isset($despesasValoresSubcategoria[$mes][$subcategoria->id])) $despesasValoresSubcategoria[$mes][$subcategoria->id]=0;
											$despesasValoresSubcategoria[$mes][$subcategoria->id]+=$v->valor*$v->quantidade;
											//$total+=$v->valor*$v->quantidade;
										} else {
											//echo "erro";
										}
									} else {
										//echo "erro";
									}
								} 
							} else {
								//echo "erro";
							}
							//echo "<HR>";
						} 
					}

					// faz a mesma coisa do codigo acima so que separado por unidades
					// capta valores dos produtos de compra (tipo=pc)
					if(isset($produtos['produtos'][$mes]) and count($produtos['produtos'][$mes])>0) {

						foreach($produtos['produtos'][$mes] as $id_produto=>$regs) {
							//echo $id_produto."->".count($regs)."<BR>";
							if(isset($_produtos[$id_produto])) {
								$produto=$_produtos[$id_produto];
								//echo "<b>$produto->titulo</b><BR>";
								foreach($regs as $id_unidade=>$v) {
									if(isset($produtosTiposCategorias[$produto->tipo])) {
										$id_subcategoria=$produtosTiposCategorias[$produto->tipo];
										if(isset($_subcategorias[$id_subcategoria])) {
											$subcategoria=$_subcategorias[$id_subcategoria];
											//echo $subcategoria->id_categoria;
											if(isset($_categorias[$subcategoria->id_categoria])) {
												$categoria=$_categorias[$subcategoria->id_categoria];
												//echo $produto->tipo."->".$v->tipo." -> ".$subcategoria->titulo." -> ".$categoria->titulo."<Br>";

												if(!isset($despesasValoresUnidades[$mes][$categoria->id][$id_unidade])) $despesasValoresUnidades[$mes][$categoria->id][$id_unidade]=0;
												$despesasValoresUnidades[$mes][$categoria->id][$id_unidade]+=$v->valor*$v->quantidade;

												//if(!isset($despesasValoresSubcategoria[$mes][$subcategoria->id])) $despesasValoresSubcategoria[$mes][$subcategoria->id]=0;
												//$despesasValoresSubcategoria[$mes][$subcategoria->id]+=$v->valor*$v->quantidade;


											} else {
												//echo "erro ";
											}
										} else {
											//echo "erro<br>";
										}
									} else {
										//echo "erro-> $produto->tipo $produto->id $produto->titulo<BR>";
									}
								}
							} else {
								//echo "erro (id_produto=$id_produto)";
							}
							//echo "<HR>";
						} 
					}

					// capta valores dos produtos e outros servicos (pc-inventariado e pc-ninventariado)
					if(isset($produtos['outrosServicosEProdutos'][$mes]) and count($produtos['outrosServicosEProdutos'][$mes])>0) {

						foreach($produtos['outrosServicosEProdutos'][$mes] as $id_produto=>$regs) {
							//echo $id_produto."->".count($regs)."<BR>";
							if(isset($_outrodProdutosEServicos[$id_produto])) {
								$produto=$_outrodProdutosEServicos[$id_produto];
								//echo "<b>$produto->titulo --></b><BR>";
								foreach($regs as $id_unidade=>$v) {
									$id_subcategoria=$produto->id_categoria;
									//echo $id_subcategoria."<BR>";
									if(isset($_subcategorias[$id_subcategoria])) {
										$subcategoria=$_subcategorias[$id_subcategoria];
										if(isset($_categorias[$subcategoria->id_categoria])) {
											$categoria=$_categorias[$subcategoria->id_categoria];

											if(!isset($despesasValoresUnidades[$mes][$categoria->id][$id_unidade])) $despesasValoresUnidades[$mes][$categoria->id][$id_unidade]=0;
											$despesasValoresUnidades[$mes][$categoria->id][$id_unidade]+=$v->valor*$v->quantidade;


											//if(!isset($despesasValoresSubcategoria[$mes][$subcategoria->id])) $despesasValoresSubcategoria[$mes][$subcategoria->id]=0;
											//$despesasValoresSubcategoria[$mes][$subcategoria->id]+=$v->valor*$v->quantidade;
											//$total+=$v->valor*$v->quantidade;
										} else {
											//echo "erro";
										}
									} else {
										//echo "erro";
									}
								} 
							} else {
								//echo "erro";
							}
							//echo "<HR>";
						} 
					}

				}



				//var_dump($despesasValoresSubcategoria);


				foreach($despesasValoresSubcategoria as $mes=>$regs) {
					//echo '<h1>'.$mes."</h1>";
					$total=0;
					foreach($regs as $id_subcategoria=>$v) {
						//echo $_subcategorias[$id_subcategoria]->titulo." -> ".$v."<BR>";
						$total+=$v;
					}
					//echo "<b>TOTAL: $total</b><br />";
				}

				//echo "<HR>";




				//echo "<hr>";
				$despesasTotal=array();
				$despesasLabel=array();
				$despesasData=array();
				for($mes=1;$mes<=12;$mes++) {
					//echo "<h1>$mes</h1>";
					foreach($_categorias as $v) {
						$valor=isset($despesasValores[$mes][$v->id])?$despesasValores[$mes][$v->id]:0;
						//echo $v->titulo." ($v->id): ".$valor."<BR>";
						$despesasData[$mes][$v->id]=number_format($valor,2,".","");
						$despesasLabel[$mes][$v->id]=utf8_encode($v->titulo);
						if(!isset($despesasTotal[$mes])) $despesasTotal[$mes]=0;
						$despesasTotal[$mes]+=$valor;
					}
				}

			}

			$despesas=array('data'=>$despesasData,
							'label'=>$despesasLabel,
							'despesas'=>$despesasValores,
							'despesasUnidades'=>$despesasValoresUnidades,
							'despesasValoresSubcategoria'=>$despesasValoresSubcategoria);
			

			return $despesas;
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
								$erro='Conta não encontrada!';
							}
							

						} else {
							$erro='Conta não especificada!';
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
								$erro='Conta não encontrada!';
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

		// cria balanco (dividas entre unidades)
		function criarBalanco($attr) {
			$usr=$this->usr;
			$_p=$this->prefixo;

			$sql = new Mysql();

			$id_credor=(isset($attr['id_credor']) and is_numeric($attr['id_credor']))?$attr['id_credor']:0;
			$id_devedor=(isset($attr['id_devedor']) and is_numeric($attr['id_devedor']))?$attr['id_devedor']:0;
			$id_fluxo=(isset($attr['id_fluxo']) and is_numeric($attr['id_fluxo']))?$attr['id_fluxo']:0;
			$id_movimento=(isset($attr['id_movimento']) and is_numeric($attr['id_movimento']))?$attr['id_movimento']:0;
			$valor=(isset($attr['valor']) and is_numeric($attr['valor']))?$attr['valor']:0;
			if($id_credor>0) {
				if($id_devedor>0) {
					if($id_fluxo>0 or $id_movimento>0) {
						if(is_numeric($valor)) {
							$vsql="id_credor=$id_credor,
									tipo='unidade',
									id_unidade=$id_devedor,
									valor='".($valor>0?$valor:$valor*-1)."',
									id_origem=".($id_fluxo>0?10:11).",
									id_registro=".($id_fluxo>0?$id_fluxo:$id_movimento);
							$sql->consult($_p."financeiro_balanco","*","where id_credor=$id_credor and id_unidade=$id_devedor and tipo='unidade' and valor='$valor' and data > NOW() - INTERVAL 10 MINUTE");
							if($sql->rows) {
								$b=mysqli_fetch_object($sql->mysqry);
								$sql->update($_p."financeiro_balanco",$vsql,"where id=$b->id and lixo=0");

							} else {
								$sql->add($_p."financeiro_balanco",$vsql.",data=now()");
							}

							return true;
						} else {
							$erro='Valor não definido!';
						}
					} else {
						$erro='Fluxo não encontrado!';
					}
				} else {
					$erro='Devedor não definida';
				}
			} else {
				$erro='Credor não definido';
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
						$fluxosID=array();
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
							}
						}



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

		// calcula o que cada socio tem direto proporcionalmente as retiradas realizadas
		function aRetirar($attr) {

			$return=array();
			if(isset($attr['id_unidade']) and is_numeric($attr['id_unidade']) and isset($attr['registros']) and is_array($attr['registros']) and isset($attr['quadroSocietario']) and is_array($attr['quadroSocietario'])) {
				$regs=$attr['registros'];
				$qs=$attr['quadroSocietario'];
				$id_unidade=$attr['id_unidade'];

				$valorTotal=0;
				foreach($regs as $idSocio=>$valor) {
					//$valor*=-1;
					$valorTotal+=$valor;
				}


				$referencia=0;
				$referenciaValor=0;
				$referenciaRetirada=0;
				$referenciaPorcentagem=0;
				foreach($regs as $idSocio=>$valor) {
					//$valor*=-1;
					if(isset($qs[$id_unidade][$idSocio])) {
						$percentual=$qs[$id_unidade][$idSocio];
						$deveriaTirar=($valorTotal*$percentual)/100;

						$dif=$valor!=0?($valor-$deveriaTirar)/$deveriaTirar:0;

						if($referencia==0 or $dif>$referenciaValor) {
							//echo $referenciaValor." ".$referencia."<BR>";
							$referenciaValor=$dif;
							$referenciaRetirada=$valor;
							$referencia=$idSocio;
							$referenciaPorcentagem=$percentual;
						}
						//echo $idSocio."($valor)->".$valorTotal." -> ".$qs[$id_unidade][$idSocio].' -> '.$deveriaTirar.' -> '.$dif.'<br />';
					}
				}

				foreach($regs as $idSocio=>$valor) {

					if(isset($qs[$id_unidade][$idSocio])) {
						$deveria=($referenciaRetirada*$qs[$id_unidade][$idSocio])/$referenciaPorcentagem;
						$deveria-=$valor;
						$return[$idSocio]=number_format($deveria,2,".","");
						//echo $idSocio."=>".$deveria." - ".$valor."<BR>";
					}
				}

				//echo "<hr />Ref. ".$referencia."->".$referenciaValor." ($referenciaRetirada)";;

			}

			return $return;
		}



	}

?>