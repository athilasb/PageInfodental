<?php
	require_once("lib/conf.php");
	require_once("usuarios/checa.php");

	// config da clinica multas/juros
		$_clinica=array();
		$sql->consult($_p."clinica","*","order by id asc");
		while($x=mysqli_fetch_object($sql->mysqry)){
			$_clinica[$x->id]=["id"=>$x->id,"clinica_nome"=>utf8_encode($x->clinica_nome),"instagram"=>utf8_encode($x->instagram),"site"=>utf8_encode($x->site),"email"=>utf8_encode($x->email),"politica_multas"=>$x->politica_multas,"politica_juros"=>$x->politica_juros];
		}

	$_formasDePagamento=array();
	$optionFormasDePagamento='';
	$sql->consult($_p."parametros_formasdepagamento","*","order by titulo asc");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_formasDePagamento[$x->id]=$x;
		$optionFormasDePagamento.='<option value="'.$x->id.'" data-tipo="'.$x->tipo.'">'.utf8_encode($x->titulo).'</option>';
	}

	if(isset($_POST['ajax'])) {
		$rtn=array();
		$pagamento='';
		if(isset($_POST['id_pagamento']) and is_numeric($_POST['id_pagamento'])) {
			$sql->consult($_p."pacientes_tratamentos_pagamentos","*","where id='".$_POST['id_pagamento']."'");
			if($sql->rows) {
				$pagamento=mysqli_fetch_object($sql->mysqry);
			}
		}

		if($_POST['ajax']=='pagamentoBaixa') {

			if(is_object($pagamento)) {

				$formaDePagamento= '';
				if(isset($_POST['id_formadepagamento'])) {
					$sql->consult($_p."parametros_formasdepagamento","*","where id='".$_POST['id_formadepagamento']."'");
					if($sql->rows) {
						$formaDePagamento=mysqli_fetch_object($sql->mysqry);
					}
				}
				$dataPagamento = (isset($_POST['dataPagamento']) and !empty($_POST['dataPagamento']))?$_POST['dataPagamento']:"";
				$dataVencimento = (isset($_POST['dataVencimento']) and !empty($_POST['dataVencimento']))?invDate($_POST['dataVencimento']):date('Y-m-d');
				$valor = (isset($_POST['valor']) and !empty($_POST['valor']))?$_POST['valor']:"";
				$valorParcela = (isset($_POST['valorParcela']) and !empty($_POST['valorParcela']))?($_POST['valorParcela']):0;
				$valorMulta = (isset($_POST['valorMulta']) and !empty($_POST['valorMulta']))?($_POST['valorMulta']):0;
				$valorJuros = (isset($_POST['valorJuros']) and !empty($_POST['valorJuros']))?($_POST['valorJuros']):0;
				$descontoMultasJuros = (isset($_POST['descontoMultasJuros']) and !empty($_POST['descontoMultasJuros']))?($_POST['descontoMultasJuros']):0;
				
				$tipoBaixa = (isset($_POST['tipoBaixa']) and !empty($_POST['tipoBaixa']))?$_POST['tipoBaixa']:"";
				$obs = (isset($_POST['obs']) and !empty($_POST['obs']))?$_POST['obs']:"";
				$cobrarJuros = (isset($_POST['cobrarJuros']) and isset($_POST['cobrarJuros']) and $_POST['cobrarJuros']==1)?$_POST['cobrarJuros']:0;
				$debitoBandeira = (isset($_POST['debitoBandeira']) and is_numeric($_POST['debitoBandeira']))?$_POST['debitoBandeira']:"";
				$creditoBandeira = (isset($_POST['creditoBandeira']) and is_numeric($_POST['creditoBandeira']))?$_POST['creditoBandeira']:"";
				$creditoParcelas = (isset($_POST['creditoParcelas']) and is_numeric($_POST['creditoParcelas']))?$_POST['creditoParcelas']:"";
				$id_operadora = (isset($_POST['id_operadora']) and is_numeric($_POST['id_operadora']))?$_POST['id_operadora']:0;
				$taxa = (isset($_POST['taxa']) and !empty($_POST['taxa']))?$_POST['taxa']:0;

				$taxa = floatval($taxa);
	
				$erro = '';

				if(empty($erro)) {
					
					if($tipoBaixa=="pagamento") {
						if($formaDePagamento->tipo=="credito") {

							$vSQLBaixa="data=now(),
										id_pagamento=$pagamento->id,
										id_formadepagamento=$formaDePagamento->id,
										tipoBaixa='".$tipoBaixa."',
										cobrarJuros='".$cobrarJuros."',
										valorJuros='".$valorJuros."',
										valorMulta='".$valorMulta."',
										descontoMultasJuros='".$descontoMultasJuros."',
										id_operadora='".$id_operadora."',
										id_bandeira='".$creditoBandeira."',
										id_usuario='".$usr->id."',
										taxa='".$taxa."',
										parcelas='".$creditoParcelas."'";

						} else if($formaDePagamento->tipo=="debito") {
							$where="where id_operadora=$id_operadora and 
											id_bandeira=$debitoBandeira and 
											operacao='debito' and lixo=0";
							$sql->consult($_p."parametros_cartoes_taxas","*",$where);

							$prazo=0;
							if($sql->rows) {
								$x=mysqli_fetch_object($sql->mysqry);
								
								$prazo=$x->prazo;
							}


							$dtVencimento=date('Y-m-d',strtotime(date($dataVencimento)." + $prazo days")); 
							$vSQLBaixa="data=now(),
										data_vencimento='".($dtVencimento)."',
										id_pagamento=$pagamento->id,
										id_formadepagamento=$formaDePagamento->id,
										tipoBaixa='".$tipoBaixa."',
										cobrarJuros='".$cobrarJuros."',
										valorJuros='".$valorJuros."',
										valorMulta='".$valorMulta."',
										descontoMultasJuros='".$descontoMultasJuros."',
										id_operadora='".$id_operadora."',
										taxa='".$taxa."',
										id_bandeira='".$debitoBandeira."',	
										valor='".($valor)."',
										id_usuario='".$usr->id."'";
						} else {
							$vSQLBaixa="data='".invDate($dataPagamento)."',
										data_vencimento='".($dataVencimento)."',
										id_pagamento=$pagamento->id,
										valorJuros='".$valorJuros."',
										valorMulta='".$valorMulta."',
										descontoMultasJuros='".$descontoMultasJuros."',
										id_formadepagamento=$formaDePagamento->id,
										tipoBaixa='".$tipoBaixa."',
										valor='".($valor)."',
										id_usuario='".$usr->id."'";
						}
					} else {
						$vSQLBaixa="data='".invDate($dataPagamento)."',
										data_vencimento='".invDate($dataPagamento)."',
										id_pagamento=$pagamento->id,
										tipoBaixa='".$tipoBaixa."',
										valorJuros='".$valorJuros."',
										valorMulta='".$valorMulta."',
										descontoMultasJuros='".$descontoMultasJuros."',
										valor='".($valor)."',
										id_usuario='".$usr->id."'";
					}
					

					if(isset($_POST['obs'])) $vSQLBaixa.=",obs='".addslashes(utf8_decode($_POST['obs']))."'";
					
					if($tipoBaixa=="pagamento" and $formaDePagamento->tipo=="credito") {

						$_prazos=array();
						$sql->consult($_p."parametros_cartoes_taxas","*","where id_operadora=$id_operadora and id_bandeira=$creditoBandeira and operacao='credito' and vezes='$creditoParcelas' and lixo=0");
						while($x=mysqli_fetch_object($sql->mysqry)) {
							$_prazos[$x->parcela]=$x->prazo;
						}


						for($i=1;$i<=$creditoParcelas;$i++) {
							$prazo = isset($_prazos[$i])?$_prazos[$i]:0;

							$dtVencimento=date('Y-m-d',strtotime(date($dataVencimento)." + $prazo days")); 
							$vSQLComp=",data_vencimento='$dtVencimento',valor='$valorParcela',parcela='$i'";
							//echo $dtVencimento."\n";
						
							$sql->add($_p."pacientes_tratamentos_pagamentos_baixas",$vSQLBaixa.$vSQLComp);
						}
						

					} else {
						$sql->add($_p."pacientes_tratamentos_pagamentos_baixas",$vSQLBaixa);
					}

					$rtn=array('success'=>true);
				} else {
					$rtn=array('success'=>false,'error'=>$erro);
				}

			} else {
				$rtn=array('success'=>false,'error'=>'Pagamento não encontrado!');
			}

		}

		else if($_POST['ajax']=="baixas") {
			$baixas = array();
			$sql->consult($_p."pacientes_tratamentos_pagamentos_baixas","*","where id_pagamento=$pagamento->id and lixo=0 order by data_vencimento asc");
			while($x=mysqli_fetch_object($sql->mysqry)) {

				if($x->tipoBaixa=="desconto" or $x->tipoBaixa=="despesa") $x->data_vencimento=$x->data;

				$baixas[]=array("id_baixa"=>(int)$x->id,
								"data"=>date('d/m/Y',strtotime($x->data_vencimento)),
								"valor"=>(float)$x->valor,
							  	"tipoBaixa"=>isset($_tipoBaixa[$x->tipoBaixa])?$_tipoBaixa[$x->tipoBaixa]:$x->tipoBaixa,
							  	"id_formadepagamento"=>(int)$x->id_formadepagamento,
							   	"formaDePagamento"=>isset($_formasDePagamento[$x->id_formadepagamento])?utf8_encode($_formasDePagamento[$x->id_formadepagamento]->titulo):'',
							   	"pago"=>$x->pago,
							   	"recibo"=>$x->recibo,
								"valorMulta"=>$x->valorMulta,
								"valorJuros"=>$x->valorJuros,
								"descontoMultasJuros"=>$x->descontoMultasJuros,
							   	"parcelas"=>$x->parcelas,
							   	"vencido"=>(strtotime($x->data_vencimento)<strtotime(date('Y-m-d'))?true:false),
							   	"parcela"=>$x->parcela,
							   	"obs"=>utf8_encode($x->obs),
							   	"total"=>(float)$x->valor);

			}

			$rtn=array('success'=>true,'baixas'=>$baixas);;
		}

		else if($_POST['ajax']=="valoresPersistir") {

			$tipo=(isset($_POST['tipo']) and ($_POST['tipo']=='descontos' || $_POST['tipo']=='despesas'))?$_POST['tipo']:'';
			$valor=(isset($_POST['valor']) and ($_POST['valor']))?$_POST['valor']:0;
			
			$pagamento='';
			if(isset($_POST['id_pagamento']) and is_numeric($_POST['id_pagamento'])) {
				$sql->consult($_p."pacientes_tratamentos_pagamentos","*","where id='".$_POST['id_pagamento']."'");
				if($sql->rows) {
					$pagamento=mysqli_fetch_object($sql->mysqry);
				}
			}

			if(!empty($tipo) and is_object($pagamento)) {
				$vSQL="$tipo='".$valor."'";
				$vWHERE="where id=$pagamento->id";
				$sql->update($_p."pacientes_tratamentos_pagamentos",$vSQL,$vWHERE);

				$rtn=array('success'=>true);

			} else {
				$rtn=array('success'=>false,'error'=>'Dados incompletos para persistir');
			}
		}

		else if($_POST['ajax']=="baixaEstornar") {

			if(is_object($pagamento)) {
				$baixa='';
				if(isset($_POST['id_baixa']) && is_numeric($_POST['id_baixa'])) {
					$sql->consult($_p."pacientes_tratamentos_pagamentos_baixas","*","where id='".$_POST['id_baixa']."' and id_pagamento=$pagamento->id");
					if($sql->rows) {
						$baixa=mysqli_fetch_object($sql->mysqry);
					}
				} 

				if(is_object($baixa)) {
					$sql->update($_p."pacientes_tratamentos_pagamentos_baixas","lixo=1,lixo_data=now(),lixo_id_usuario=$usr->id","where id=$baixa->id");
					$rtn=array('success'=>true);
				} else {
					$rtn=array('success'=>false,'error'=>'Baixa não encontrada!');
				}
			} else {
				$rtn=array('success'=>false,'error'=>'Pagamento não encontrado!');
			}

		}

		else if($_POST['ajax']=="baixaEstornarPagamento") {

			if(is_object($pagamento)) {
				$baixa='';
				if(isset($_POST['id_baixa']) && is_numeric($_POST['id_baixa'])) {
					$sql->consult($_p."pacientes_tratamentos_pagamentos_baixas","*","where id='".$_POST['id_baixa']."' and id_pagamento=$pagamento->id");
					if($sql->rows) {
						$baixa=mysqli_fetch_object($sql->mysqry);
					}
				} 

				if(is_object($baixa)) {
					$sql->update($_p."pacientes_tratamentos_pagamentos_baixas","pago=0,pago_data=now(),pago_id_usuario=$usr->id","where id=$baixa->id");
					$rtn=array('success'=>true);
				} else {
					$rtn=array('success'=>false,'error'=>'Baixa não encontrada!');
				}
			} else {
				$rtn=array('success'=>false,'error'=>'Pagamento não encontrado!');
			}

		}

		else if($_POST['ajax']=="unirPagamentos") {
			if(isset($_POST['pagamentos']) and is_array($_POST['pagamentos'])) {
				if(count($_POST['pagamentos'])>=2) {

					$uniaoIds=array();
					$valor=0;
					$id_tratamento=0;
					$sql->consult($_p."pacientes_tratamentos_pagamentos","*","where id IN (".implode(",",$_POST['pagamentos']).")");
					if($sql->rows) {
						while($x=mysqli_fetch_object($sql->mysqry)) {
							$valor+=$x->valor;
							$uniaoIds[]=$x->id;
							$id_paciente=$x->id_paciente;
							$id_unidade=$x->id_unidade;
							$id_tratamento=$x->id_tratamento;
						}
					}

					if(count($uniaoIds)>=2) {
						$vSQL="data=now(),
								data_vencimento='".(isset($_POST['dataVencimento'])?invDate($_POST['dataVencimento']):now())."',
								id_usuario=$usr->id,
								id_unidade=$id_unidade,
								id_tratamento=$id_tratamento,
								id_paciente=$id_paciente,
								valor='".$valor."',
								fusao=1";
								//echo $vSQL;die();
						$sql->add($_p."pacientes_tratamentos_pagamentos",$vSQL);
						$id_fusao=$sql->ulid;

						$sql->update($_p."pacientes_tratamentos_pagamentos","id_fusao=$id_fusao","where id IN (".implode(",",$uniaoIds).")");

						$rtn=array('success'=>true);
					} else {
						$rtn=array('success'=>false,'error'=>'Selecione pelo menos 2 pagamentos');
					}

				} else {
					$rtn=array('success'=>false,'error'=>'Selecione pelo menos 2 pagamentos');
				}
			}
		}

		else if($_POST['ajax']=="desfazerUniao") {
			if(is_object($pagamento)) {
				if($pagamento->fusao==1) {

					$sql->consult($_p."pacientes_tratamentos_pagamentos_baixas","*","where id_pagamento=$pagamento->id and lixo=0");
					if($sql->rows==0) {
						$sql->update($_p."pacientes_tratamentos_pagamentos","id_fusao=0","where id_fusao=$pagamento->id");
						$sql->update($_p."pacientes_tratamentos_pagamentos","lixo=1,lixo_obs=5,lixo_data=now(),lixo_id_usuario=$usr->id","where id=$pagamento->id");
						$rtn=array('success'=>true);
					} else {
						$rtn=array('success'=>false,'error'=>'Estorne todas as baixas desta parcela para desfazer a união!');
					}
				} else {

					$rtn=array('success'=>false,'Este pagamento não é uma união de pagamento!');
				}
			} else {
				$rtn=array('success'=>false,'error'=>'Pagamento não encontrado!');
			}
		}

		else if($_POST['ajax']=="receber") {

			$baixa='';
			if(isset($_POST['id_baixa']) and is_numeric($_POST['id_baixa'])) {
				$sql->consult($_p."pacientes_tratamentos_pagamentos_baixas","*","where id='".$_POST['id_baixa']."'");
				if($sql->rows) {
					$baixa=mysqli_fetch_object($sql->mysqry);

				}
			}
			$id_banco = (isset($_POST['id_banco']) and is_numeric($_POST['id_banco']))?$_POST['id_banco']:0;
		
			$dataPagamento='';
			if(isset($_POST['dataPagamento']) and !empty($_POST['dataPagamento'])) {
				list($dia,$mes,$ano) = explode("/",$_POST['dataPagamento']);
				if(checkdate($mes, $dia, $ano)) {
					$dataPagamento=$ano."-".$mes."-".$dia;
				}
			}



			if(is_object($baixa))  {
				if(!empty($dataPagamento)) {
					$sql->update($_p."pacientes_tratamentos_pagamentos_baixas","pago=1,pago_data='".$dataPagamento."',pago_id_usuario=$usr->id,id_banco='$id_banco'","where id=$baixa->id");

					$rtn=array('success'=>true);
				} else {
					$rtn=array('success'=>false,'error'=>'Defina uma data de pagamento válida!');
				}
			} else {
				$rtn=array('success'=>false,'error'=>'Baixa não encontrada!');
			}
		}


		header("Content-type: application/json");
		echo json_encode($rtn);
		die();
	}
	include "includes/header.php";
	include "includes/nav.php";

	require_once("includes/header/headerPacientes.php");

	$_table=$_p."pacientes_tratamentos_pagamentos";
	$_page=basename($_SERVER['PHP_SELF']);

	
	$_formasDePagamento=array();
	$optionFormasDePagamento='';
	$sql->consult($_p."parametros_formasdepagamento","*","order by titulo asc");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_formasDePagamento[$x->id]=$x;
		$optionFormasDePagamento.='<option value="'.$x->id.'">'.utf8_encode($x->titulo).'</option>';
	}

 
	$_planos=array();
	$sql->consult($_p."parametros_planos","*","where lixo=0");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_planos[$x->id]=$x;
	}

	if(isset($_GET['id_pagamento']) and is_numeric($_GET['id_pagamento'])) {
		$sql->consult($_p."pacientes_tratamentos_pagamentos","*","where id='".$_GET['id_pagamento']."' and id_paciente=$paciente->id");
		if($sql->rows) {
			$pag=mysqli_fetch_object($sql->mysqry);

			$sql->update($_p."pacientes_tratamentos_pagamentos","lixo=1,lixo_data=now(),lixo_obs='excluido'","where id=$pag->id");


		}
	}

	$where="WHERE id_paciente=$paciente->id and id_fusao=0 and lixo=0 order by data desc, id asc";
	$sql->consult($_p."pacientes_tratamentos_pagamentos","*",$where);


	$valor=array('aReceber'=>0,
				'valorRecebido'=>0,
				'valoresVencido'=>0,
				'valorTotal'=>0,
				"definirPagamento"=>0);

	$registros=array();
	$tratamentosIDs=array(-1);
	$pagamentosIDs=array(-1);
	$pagamentosUnidos=array(-1);
	while($x=mysqli_fetch_object($sql->mysqry)) {
		if($x->id_fusao==0) {
			$registros[]=$x;
		}
		$tratamentosIDs[]=$x->id_tratamento;
		$pagamentosIDs[$x->id]=$x->id;

		if($x->fusao==1) $pagamentosUnidos[]=$x->id;

		if($x->fusao==0) $valor['valorTotal']+=$x->valor;
		
	}


	$_subpagamentos=array();
	$sql->consult($_table,"*","where id_fusao IN (".implode(",",$pagamentosUnidos).") and lixo=0");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_subpagamentos[$x->id_fusao][]=$x;
	}

	$_baixas=array();
	$pagamentosComBaixas=array();
	$sql->consult($_table."_baixas","*","where id_pagamento IN (".implode(",",$pagamentosIDs).") and lixo=0 order by data_vencimento asc");
	if($sql->rows) {
		while($x=mysqli_fetch_object($sql->mysqry)) {
			$_baixas[$x->id_pagamento][]=$x;
			$pagamentosComBaixas[$x->id_pagamento]=$x->id_pagamento;
		}
	}
	$sql->consult($_p."pacientes_tratamentos","*","where id IN (".implode(",",$tratamentosIDs).")");
	while($x=mysqli_fetch_object($sql->mysqry)) $_tratamentos[$x->id]=$x;

	$valorAReceber = $saldoAPagar= $valorDefinido=0;
	foreach($registros as $x) {
		$valorDefinido=0;
		if(isset($_baixas[$x->id])) {
			$dataUltimoPagamento=date('d/m/Y',strtotime($_baixas[$x->id][count($_baixas[$x->id])-1]->data));
			foreach($_baixas[$x->id] as $v) {
				$valor['aReceber']+=$v->valor;
				$valorDefinido+=$v->valor;
	   			if($v->pago==1) {
					$valor['aReceber']-=$v->valor;
					$valor['valorRecebido']+=$v->valor;
				}else{
					$atraso=(strtotime($v->data_vencimento)-strtotime(date('Y-m-d')))/(60*60*24);
					if($atraso<0) {
						$valor['valoresVencido']+=$v->valor;
						$valor['aReceber'] -= $v->valor;
					}
				}
			}
			if($x->valor>$valorDefinido){
				$valor['definirPagamento']+=($x->valor-$valorDefinido);
			}
		} else {
			$atraso=(strtotime($x->data_vencimento)-strtotime(date('Y-m-d')))/(60*60*24);
			if($atraso<0 and $x->pago==0) {
			 	$valor['valoresVencido']+=$x->valor;
			}else{
				$valor['definirPagamento']+=$x->valor;
			}
			//$valor['aReceber']+=$x->valor;
		}
	}
?>	

	<script type="text/javascript">
		var baixas = [];
		var id_pagamento = 0;
		const _clinica = <?=json_encode($_clinica)?>;

		const creditoDebitoValorParcela = () => {

			let id_formadepagamento = $('select.js-id_formadepagamento option:selected').val();
			let tipo = $('select.js-id_formadepagamento option:selected').attr('data-tipo');
			
			if(id_formadepagamento.length>0) {

				let valor = $('.js-valor').val().length>0?unMoney($('.js-valor').val()):0;

				let valorCreditoDebito=0;

				if(tipo=='credito') {
					let id_bandeira = $('select.js-creditoBandeira').val();
					let id_operadora = $('select.js-creditoBandeira option:checked').attr('data-id_operadora');
					let parcela = eval($('select.js-parcelas option:selected').val());

			
						//alert(id_operadora+' - '+id_bandeira+' -'+parcela);
					if(id_operadora!==undefined && parcela!==undefined) {

						let taxa = 0;
						let cobrarTaxa = 0;
						if(_taxasCredito[id_operadora][id_bandeira][parcela]) taxa=_taxasCredito[id_operadora][id_bandeira][parcela];
					//	if(_taxasCreditoSemJuros[id_operadora][id_bandeira][parcela]) cobrarTaxa=eval(_taxasCreditoSemJuros[id_operadora][id_bandeira][parcela]);
						

						if(cobrarTaxa==1) {
							valorCreditoDebito=taxa==0?valor:(valor*(1+(taxa/100)));
						} else {
							valorCreditoDebito=valor;
						}

						valorCreditoDebito/=parcela;

						$('.js-valorCreditoDebito').val(number_format(valorCreditoDebito,2,",","."));
						$('.js-valorCreditoDebitoTaxa').val(`${cobrarTaxa==1?"+":"-"} ${taxa}%`);
					}

				} else if(tipo=='debito') {
					let taxa = eval($('select.js-debitoBandeira option:selected').attr('data-taxa'));
					let id_operadora = $('select.js-debitoBandeira option:checked').attr('data-id_operadora');
					let cobrarTaxa = eval($('select.js-debitoBandeira option:selected').attr('data-cobrarTaxa'));

					if(taxa!==undefined) {
						if(cobrarTaxa==1) {
							valorCreditoDebito=taxa==0?valor:(valor*(1+(taxa/100)));
						} else {
							valorCreditoDebito=valor;
						}
						$('.js-valorCreditoDebito').val(number_format(valorCreditoDebito,2,",","."));
						$('.js-valorCreditoDebitoTaxa').val(`${cobrarTaxa==1?"+":"-"} ${taxa}%`);
					}

				} else {
					$('.js-valorCreditoDebitoTaxa').val('-');
					$('.js-valorCreditoDebito').val('-');

				}


			}
		}

		const baixasAtualizar = () => {
			let data = `ajax=baixas&id_pagamento=${id_pagamento}`;
			$.ajax({
				type:"POST",
				//url:"box/boxPacientePagamentos.php",
				data:data,
				success:function(rtn) {
					if(rtn.success) {
						$('#js-aside-asFinanceiro .js-baixas .js-tr').remove();
						$('[name="alteracao"]').val("1")
						total = 0;
						let desconto = 0;
						let despesas = 0;
						if(rtn.baixas.length>0) {
							let contador = 0;
							rtn.baixas.forEach(x=> {
								let textJuros = "";
								let textMulta = "";
								let TextDescontoIncargos ="";
								let ValorParcela = 0
								let pagamento = '';
								let alertVencimento = "";
								let taxaCartao=""
								if(x.tipoBaixa=="PAGAMENTO") {
									if(x.formaDePagamento.length>0) {
										if(x.id_formadepagamento==2) {
											pagamento=`${x.formaDePagamento}<font color=#999><br />Parcela ${x.parcela} de ${x.parcelas}</font>`;
											taxaCartao = `<span style="font-size:12px;color:var(--cinza4)">Taxa Cartão: R$ ${number_format((x.valor*parseFloat(x.taxa)/100),2,",",".")}</span><br>`
										} else {
											pagamento=x.formaDePagamento; 
										}
									}
								} else {
									pagamento=`<span class="iconify" data-icon="il:dialog" data-inline="true" data-height="18"></span> ${x.obs}`;
								}

								if(x.tipoBaixa=="DESCONTO") {
									desconto+=x.valor;
								}

								else if(x.tipoBaixa=="DESPESA") {
									despesas+=x.valor;
								} else {

									total+=x.valor;
								}

								let btns = ``;

								if(x.pago==1) {
									icon = `<span class="iconify" data-icon="akar-icons:circle-check" data-inline="true" style="color:green"></span>`;

									btns=`<a href="javascript:;" class="js-estornoPagamento button button__sec tooltip" data-id_baixa="${x.id_baixa}" data-index="${contador}" style="color:#FFF;background:red" title="Estornar Pagamento"><span class="iconify" data-icon="typcn:arrow-back" data-inline="false"></span></a>`;

								} else {
									btns=`<a href="javascript:;" class="js-estorno button button__sec" data-id_baixa="${x.id_baixa}" title="Estorno"><span class="iconify" data-icon="typcn:arrow-back" data-inline="false"></span></a>`;

									if(x.tipoBaixa=="PAGAMENTO") {
										btns+=` <a href="javascript:;" class="js-receber button button__sec" data-id_baixa="${x.id_baixa}" data-index="${contador}" title="Pagar"><span class="iconify" data-icon="ic:round-attach-money" data-inline="false"></span></a>`;
									}
									if(x.vencido) {
										icon = `<span class="iconify" data-icon="icons8:cancel" data-inline="true" style="color:red"></span>`;
									} else {
										icon = `<span class="iconify" data-icon="bx:bx-hourglass" data-inline="true" style="color:orange"></span>`;
									}
								}

								contador++;
								ValorParcela+=x.valor;
								if(x.valorMulta>0){
									ValorParcela+=parseFloat(x.valorMulta);
									textMulta = `<span style="font-size:12px;color:var(--cinza4)">Multa: R$${number_format(x.valorMulta,2,",",".")}</span></br>`
								}
								if(x.valorJuros>0){
									ValorParcela+=parseFloat(x.valorJuros);
									textJuros = `<span style="font-size:12px;color:var(--cinza4)">Juros: R$ ${number_format(x.valorJuros,2,",",".")}</span></br>`
								}
								if(x.descontoMultasJuros>0){
									ValorParcela-=parseFloat(x.descontoMultasJuros);
									TextDescontoIncargos = `<span style="font-size:12px;color:var(--cinza4)">Descontos: R$ ${number_format(x.descontoMultasJuros,2,",",".")}</span></br>`
								}
								// let diferenca = (new Date().getTime()-new Date(`${x.vencimento.split('/')[2]}/${x.vencimento.split('/')[1]}/${x.vencimento.split('/')[0]}`).getTime()) / (1000 * 60 * 60 * 24);
								// if(diferenca>=1){
								// 	alertVencimento = `<span style="color:red">FATURA VENCIDA!</span>` 
								// }
							

								html = `<tr class="js-tr">
											<td>${icon}</td>
											<td>${x.data}<br>${alertVencimento}</td>
											<td>${x.tipoBaixa}</td>
											<td>${pagamento}</td>
											<td>
												<font style="font-size:18px">${number_format(ValorParcela,2,",",".")}</font></br>
												${taxaCartao}
												${textMulta}
												${textJuros}
												${TextDescontoIncargos}
											</td>
											<td>${btns}</td>
										</tr>`;

								


								$('.js-tr .js-recibo, .js-tr .js-estorno').tooltipster({theme:"borderless"});
								$('#js-aside-asFinanceiro .js-baixas').append(html);
							});
						} else {
							$('#js-aside-asFinanceiro .js-baixas').append('<tr class="js-tr"><td colspan="4"><center>Nenhuma baixa cadastrada</center></td></tr>');
						}




						$('.js-valorDesconto').val(number_format(desconto,2,",","."));
						$('.js-valorDespesa').val(number_format(despesas,2,",","."));

						baixasAtualizarValores();

					} else if(rtn.error) {
						swal({title: "Erro!", text: rtn.error,  html:true,type:"error", confirmButtonColor: "#424242"});
					} else {
						swal({title: "Erro!", text: "Algum erro ocorreu durante a baixa deste pagamento!",  html:true,type:"error", confirmButtonColor: "#424242"});
					}
				},
				error:function() {
					swal({title: "Erro!", text: "Algum erro ocorreu durante a baixa deste pagamento!",  html:true,type:"error", confirmButtonColor: "#424242"});
				}
			})
		}

		const baixasAtualizarValores = () => {
			let valorParcela = unMoney($('.js-valorParcela').val())
			let desconto = unMoney($('.js-valorDesconto').val());
			let despesas = unMoney($('.js-valorDespesa').val());
			let saldoPagar=(valorParcela-total).toFixed(2);
			saldoPagar-=desconto;
			saldoPagar+=despesas;
			let valorCorrigido = valorParcela;
			valorCorrigido-=desconto;
			valorCorrigido+=despesas;
			
			$('.js-saldoPagar').val(number_format(saldoPagar,2,",","."));
			$('.js-valorCorrigido').val(number_format(valorCorrigido,2,",","."));
		
			if(saldoPagar<=0) {
				$('.js-fieldset-pagamentos').hide();
			} else {
				$('.js-fieldset-pagamentos').show();
			}
		}

	
	</script>

	<script type="text/javascript">
		var pagamentos = [];
		var dataHoje = '<?= date('d/m/Y');?>';

		$(function(){
		<?php
		if(isset($_GET['unirPagamentos'])) {
		?>
			$('.js-btn-unirPagamentos').click(function() {

				let dataVencimento = $('.js-dataVencimento').val();

				if(dataVencimento.length==0 || !validaData(dataVencimento)) {
					swal({title: "Erro!", text: "Digite uma data de vencimento válida!",  html:true,type:"error", confirmButtonColor: "#424242"});
				} else if($('.js-checkbox-pagamentos:checked').length<=1) {
					swal({title: "Erro!", text: "Selecione pelo menos 2 pagamentos",  html:true,type:"error", confirmButtonColor: "#424242"});
				} else {
					let pagamentosIds=$('form.js-form-pagamentos').serialize();
					let data = `ajax=unirPagamentos&dataVencimento=${dataVencimento}&${pagamentosIds}`;
				
					$.ajax({
						type:"POST",
						data:data,
						success:function(rtn) {
							if(rtn.success) {
								document.location.href='<?= "$_page?$url";?>';
							} else if(rtn.error) {
								swal({title: "Erro!", text: rtn.error,  html:true,type:"error", confirmButtonColor: "#424242"});
							} else {
								swal({title: "Erro!", text: "Algum erro ocorreu durante a baixa deste pagamento",  html:true,type:"error", confirmButtonColor: "#424242"});
							}
						},
						error:function() {
							swal({title: "Erro!", text: "Algum erro ocorreu durante a baixa deste pagamento.",  html:true,type:"error", confirmButtonColor: "#424242"});
						}
					}) 
				}
			});

			$('.js-checkbox-pagamentos').click(function(){
				let id_tratamento = $(this).attr('data-id_tratamento');
				if($(this).prop('checked')==true) {
					$('.js-checkbox-pagamentos').hide();
					$(`.js-checkbox-pagamentos[data-id_tratamento=${id_tratamento}]`).show();
					$(`.js-checkbox-pagamentos-disabled`).show();
					$(`.js-checkbox-pagamentos-disabled[data-id_tratamento=${id_tratamento}]`).hide();
				} else {
					if($(`.js-checkbox-pagamentos:checked`).length>0) {

					} else {

						$('.js-checkbox-pagamentos').show();
						$(`.js-checkbox-pagamentos-disabled`).hide();
					}
				}
			});

		<?php
		} else {
		?>
			// Quando clica para abrir o aside
			$('.js-pagamento-item').click(function(){
				let index = $(this).index('table.js-table-pagamentos .js-pagamento-item');
				let jurosMultas=pagamentos[index].multaAtraso+pagamentos[index].jurosMensal
				$('.js-colunaMultasJuros').hide()
				$('#js-aside-asFinanceiro .js-multasJuros').val(number_format(0,2,",","."));

				if(jurosMultas>0){
					$('.js-colunaMultasJuros').show()
					$('#js-aside-asFinanceiro .js-multasJuros').val(number_format(jurosMultas,2,",","."));

				}
				// Resumo
					$('#js-aside-asFinanceiro .js-index').val(index);
					$('#js-aside-asFinanceiro .js-id_pagamento').val(pagamentos[index].id_parcela);
					$('#js-aside-asFinanceiro .js-titulo').html(pagamentos[index].titulo);
					$('#js-aside-asFinanceiro .js-vencimento').html(`Vencto: ${pagamentos[index].vencimento}`);
					$('#js-aside-asFinanceiro .js-desconto').val(number_format(pagamentos[index].valorDesconto,2,",","."));
					$('#js-aside-asFinanceiro .js-parcela').val(number_format(pagamentos[index].valorParcela,2,",","."));
					$('#js-aside-asFinanceiro .js-despesa').val(number_format(pagamentos[index].valorDespesa,2,",","."));


					$('#js-aside-asFinanceiro .js-corrigido').val(number_format(pagamentos[index].valorCorrigido,2,",","."));
					$('#js-aside-asFinanceiro .js-pago').val(number_format(pagamentos[index].valorPago,2,",","."));
					$('#js-aside-asFinanceiro .js-btn-pagamento').attr('data-id_pagamento',pagamentos[index].id_parcela);
					$('#js-aside-asFinanceiro .js-apagar').val(number_format(pagamentos[index].valorCorrigido-pagamentos[index].valorPago,2,",","."));

				// Agrupamento
					$('#js-aside-asFinanceiro .js-subpagamentos tr').remove();
					if(pagamentos[index].subpagamentos && pagamentos[index].subpagamentos.length>0) {
						pagamentos[index].subpagamentos.forEach(x=> {
							$('#js-aside-asFinanceiro .js-subpagamentos').append(`<tr>
																					<td>${x.vencimento}</td>
																					<td>${x.titulo}</td>
																					<td>${number_format(x.valor,2,",",".")}</td>
																				</tr>`);
						});

						$('#js-aside-asFinanceiro .js-subpagamentos').append(`<tr>
																				<td colspan="3"><center><a href="javascript:;" class="js-desfazerUniao" data-id_pagamento="${pagamentos[index].id_parcela}"><span class="iconify" data-icon="eva:undo-fill" data-inline="false"></span> Desfazer união</a></center></td>
																			</tr>`)

						$('#js-aside-asFinanceiro .js-tab-agrupamento').show();
					} else {
						$('#js-aside-asFinanceiro .js-tab-agrupamento').hide();
						$('#js-aside-asFinanceiro .js-subpagamentos').append(`<tr><td colspan="3"><center>Este pagamento não possui união</center></td></tr>`);
					}

				// Programação de Pagamentos
					$('#js-aside-asFinanceiro .js-baixas tr').remove();
					total = 0;
					let desconto = 0;
					let despesas = 0;
					let contador = 0;
					if(pagamentos[index].baixas && pagamentos[index].baixas.length>0) {
						pagamentos[index].baixas.forEach(x=> {
							let textJuros = "";
							let textMulta = "";
							let TextDescontoIncargos ="";
							let ValorParcela = 0
							let pagamento = '';
							let alertVencimento = "";
							let taxaCartao = "";
							if(x.tipoBaixa=="PAGAMENTO") {
								if(x.formaDePagamento.length>0) {
									if(x.id_formadepagamento==2) {
										pagamento=`${x.formaDePagamento}<font color=#999><br />Parcela ${x.parcela} de ${x.parcelas}</font>`;
										taxaCartao = `<span style="font-size:12px;color:var(--cinza4)">Taxa Cartão: R$ ${number_format((x.valor*parseFloat(x.taxa)/100),2,",",".")}</span><br>`
									} else {
										pagamento=x.formaDePagamento; 
									}
								}
							} else {
								pagamento=`<span class="iconify" data-icon="il:dialog" data-inline="true" data-height="18"></span> ${x.obs}`;
							}

							if(x.tipoBaixa=="DESCONTO") {
								desconto+=x.valor;
							}

							else if(x.tipoBaixa=="DESPESA") {
								despesas+=x.valor;
							} else {

								total+=x.valor;
							}

							let btns = ``;

							if(x.pago==1) {
								icon = `<span class="iconify" data-icon="akar-icons:circle-check" data-inline="true" style="color:green"></span>`;

								btns=`<a href="javascript:;" class="js-estornoPagamento button button__sec tooltip" data-id_baixa="${x.id_baixa}" data-index="${contador}" style="color:#FFF;background:red" title="Estornar Pagamento"><span class="iconify" data-icon="typcn:arrow-back" data-inline="false"></span></a>`;

							} else {
								btns=`<a href="javascript:;" class="js-estorno button button__sec" data-id_baixa="${x.id_baixa}" title="Estorno"><span class="iconify" data-icon="typcn:arrow-back" data-inline="false"></span></a>`;

								if(x.tipoBaixa=="PAGAMENTO") {
									btns+=` <a href="javascript:;" class="js-receber button button__sec" data-id_baixa="${x.id_baixa}" data-index="${contador}" title="Pagar"><span class="iconify" data-icon="ic:round-attach-money" data-inline="false"></span></a>`;
								}
								if(x.vencido) {
									icon = `<span class="iconify" data-icon="icons8:cancel" data-inline="true" style="color:red"></span>`;
								} else {
									icon = `<span class="iconify" data-icon="bx:bx-hourglass" data-inline="true" style="color:orange"></span>`;
								}
							}
							ValorParcela=x.valor;
							if(x.valorMulta>0){
								ValorParcela += parseFloat(x.valorMulta);
								textMulta = `<span style="font-size:12px;color:var(--cinza4)">Multa: R$${number_format(x.valorMulta,2,",",".")}</span></br>`
							}
							if(x.valorJuros>0){
								ValorParcela+=parseFloat(x.valorJuros);
							 	textJuros = `<span style="font-size:12px;color:var(--cinza4)">Juros: R$ ${number_format(x.valorJuros,2,",",".")}</span></br>`
							 }
							if(x.descontoMultasJuros>0){
							 	ValorParcela-=parseFloat(x.descontoMultasJuros);
							 	TextDescontoIncargos = `<span style="font-size:12px;color:var(--cinza4)">Descontos: R$ ${number_format(x.descontoMultasJuros,2,",",".")}</span></br>`
							}
							let diferenca = (new Date().getTime()-new Date(`${x.vencimento.split('/')[2]}/${x.vencimento.split('/')[1]}/${x.vencimento.split('/')[0]}`).getTime()) / (1000 * 60 * 60 * 24);
							if(diferenca>=1){
								alertVencimento = `<span style="color:red">FATURA VENCIDA!</span>` 
							}
							contador++;
							html = `<tr class="js-tr">
										<td>${icon}</td>
										<td>${x.data}<br>${alertVencimento}</td>
										<td>${x.tipoBaixa}</td>
										<td>${pagamento}</td>
										<td>
											<font style="font-size:18px">${number_format(ValorParcela,2,",",".")}</font></br>
											${taxaCartao}
											${textMulta}
											${textJuros}
											${TextDescontoIncargos}
										</td>
										<td>${btns}</td>
									</tr>`;

							


							$('.js-tr .js-recibo, .js-tr .js-estorno').tooltipster({theme:"borderless"});
							$('#js-aside-asFinanceiro .js-baixas').append(html);
						});
						

					} else {
						$('#js-aside-asFinanceiro .js-baixas').append('<tr class="js-tr"><td colspan="4"><center>Nenhuma baixa cadastrada</center></td></tr>');
					}
					$('#js-aside-asFinanceiro .js-valorDespesa,#js-aside-asFinanceiro .js-despesa').val(number_format(despesas,2,",","."));
					$('#js-aside-asFinanceiro .js-valorDesconto,#js-aside-asFinanceiro .js-desconto').val(number_format(desconto,2,",","."));
					$('#js-aside-asFinanceiro .js-valorParcela').val(number_format(pagamentos[index].valorParcela,2,",","."));


				// Triggers
					$('#js-aside-asFinanceiro input[name=tipoBaixa]:eq(0)').click();
					baixasAtualizarValores();


				$("#js-aside-asFinanceiro").fadeIn(100,function() {
					$("#js-aside-asFinanceiro .aside__inner1").addClass("active");
				});
			});
			
		<?php	
		}
		?>
			$('#js-aside-asFinanceiro .js-baixas').on('click','.js-estorno',function(){
				id_pagamento = $('#js-aside-asFinanceiro .js-id_pagamento').val();
				let id_baixa = $(this).attr('data-id_baixa')
				let data = `ajax=baixaEstornar&id_baixa=${id_baixa}&id_pagamento=${id_pagamento}`;

				let obj = $(this);
				let objHTMLAntigo = obj.html();

				swal({
					title:"Atenção", 
					text: "Você tem certeza que deseja estornar esta baixa?",
					type: "warning",
					showCancelButton: true, 
					confirmButtonColor: "#DD6B55",
					confirmButtonText: "Sim!",
					cancelButtonText: "Não",
					closeOnConfirm: true,
					closeOnCancel: true }
					,function(isConfirm){   
						if (isConfirm) {

							obj.html('<span class="iconify" data-icon="eos-icons:loading"></span>');

							$.ajax({
								type:"POST",
								//url:'box/<?= basename($_SERVER['PHP_SELF']);?>',
								data:data,
								success:function(rtn) {
									if(rtn.success) {
										baixasAtualizar();
										$('[name="alteracao"]').val("1")
									} else if(rtn.error) {
										swal({title: "Erro!", text: rtn.error,  html:true,type:"error", confirmButtonColor: "#424242"});
										obj.html(objHTMLAntigo);
									} else {
										swal({title: "Erro!", text: "Algum erro ocorreu durante o estorno desta baixa!",  html:true,type:"error", confirmButtonColor: "#424242"});
										obj.html(objHTMLAntigo);
									}
								},
								error:function() {
									swal({title: "Erro!", text: "Algum erro ocorreu durante o estorno desta baixa!",  html:true,type:"error", confirmButtonColor: "#424242"});
									obj.html(objHTMLAntigo);
								}
							}).done(function(){
							});
						}
					});

				
			});

			$('#js-aside-asFinanceiro .js-baixas').on('click','.js-estornoPagamento',function(){
				let pagamentoIndex = $('#js-aside-asFinanceiro .js-index').val();
				let baixaIndex = $(this).attr('data-index');

				if(pagamentos[pagamentoIndex]) {

					let pagamento = pagamentos[pagamentoIndex];

					if(pagamento.baixas[baixaIndex]) {

						let baixa = pagamento.baixas[baixaIndex];

						if(baixa.pago=="0") {
							swal({title: "Erro!", text: "Esta baixa não foi recebida ainda!",  html:true,type:"error", confirmButtonColor: "#424242"});

						} else {

						 	let id_baixa = pagamento.baixas[baixaIndex].id_baixa;
						 	let id_parcela = pagamento.id_parcela;

							let data = `ajax=baixaEstornarPagamento&id_baixa=${id_baixa}&id_pagamento=${id_parcela}`;
						
							swal({
								title:"Atenção", 
								text: "Você tem certeza que deseja estornar este pagamento?",
								type: "warning",
								showCancelButton: true, 
								confirmButtonColor: "#DD6B55",
								confirmButtonText: "Sim!",
								cancelButtonText: "Não",
								closeOnConfirm: true,
								closeOnCancel: true }
								,function(isConfirm){   
									if (isConfirm) {
										$.ajax({
											type:"POST",
											data:data,
											success:function(rtn) {
												if(rtn.success) {
													pagamentos[pagamentoIndex].baixas[baixaIndex].pago="0";
													$('#js-aside-asFinanceiro-receber .aside-close').click();
													$('#js-aside-asFinanceiro .aside-close').click();
													$('.js-pagamento-item-'+id_parcela).click();
													$('[name="alteracao"]').val("1")
													document.location.reload();
												} else if(rtn.error) {
													swal({title: "Erro!", text: rtn.error,  html:true,type:"error", confirmButtonColor: "#424242"});
												} else {
													swal({title: "Erro!", text: "Algum erro ocorreu durante o estorno desta baixa!",  html:true,type:"error", confirmButtonColor: "#424242"});
												}
											},
											error:function() {
												swal({title: "Erro!", text: "Algum erro ocorreu durante o estorno desta baixa!",  html:true,type:"error", confirmButtonColor: "#424242"});
											}
										});
									}
								}
							);
						}

					}
				}
			});

			$('#js-aside-asFinanceiro .js-baixas').on('click','.js-receber',function(){
				let pagamentoIndex = $('#js-aside-asFinanceiro .js-index').val();
				let baixaIndex = $(this).attr('data-index');

				if(pagamentos[pagamentoIndex]) {

					let pagamento = pagamentos[pagamentoIndex];

					if(pagamento.baixas[baixaIndex]) {

						let baixa = pagamento.baixas[baixaIndex];

						$('#js-aside-asFinanceiro-receber .js-index').val(baixaIndex);

						$('#js-aside-asFinanceiro-receber .js-dataPagamento').val(dataHoje);
						$('#js-aside-asFinanceiro-receber .js-vencimentoParcela').val(baixa.data);
						$('#js-aside-asFinanceiro-receber .js-valorParcela').val(number_format(baixa.valor,2,",","."));
						$('#js-aside-asFinanceiro-receber .js-formaPagamento').val(baixa.formaDePagamento);

						$('#js-aside-asFinanceiro-receber .js-fieldset-conta').show();
						// if(baixa.formaDePagamentoTipo=="dinheiro") {
						// } else {
						// 	$('#js-aside-asFinanceiro-receber .js-fieldset-conta').hide();
						// }

						$("#js-aside-asFinanceiro-receber").fadeIn(100,function() {
							$("#js-aside-asFinanceiro-receber .aside__inner1").addClass("active");
						});
					}
				}
			});

			$('#js-aside-asFinanceiro-receber .js-btn-receber').click(function(){
				let pagamentoIndex = $('#js-aside-asFinanceiro .js-index').val();
				let baixaIndex = $('#js-aside-asFinanceiro-receber .js-index').val();
				let dataPagamento = $('#js-aside-asFinanceiro-receber .js-dataPagamento').val();
				let bancoPagamento = $('#js-aside-asFinanceiro-receber .js-id_banco').val();
				return 	

				let erro='';
				if(!pagamentos[pagamentoIndex].baixas[baixaIndex]) erro='Pagamento não encontrado!';
				else if(dataPagamento.length==0) erro='Defina a Data de Pagamento!';


				if(erro.length>0) {
					swal({title: "Erro!", text: erro,  html:true,type:"error", confirmButtonColor: "#424242"});
				} else {

					if(pagamentos[pagamentoIndex].baixas[baixaIndex]) {
						let id_baixa = pagamentos[pagamentoIndex].baixas[baixaIndex].id_baixa;
						let id_parcela = pagamentos[pagamentoIndex].id_parcela;

						let obj = $(this);
						let objHTMLAntigo = $(this).html();

						if(obj.attr('data-loading')==0) {
							obj.html('<span class="iconify" data-icon="eos-icons:loading"></span>');
							obj.attr('data-loading',1);

							let data = `ajax=receber&id_baixa=${id_baixa}&dataPagamento=${dataPagamento}&id_banco=${bancoPagamento}`;

							$.ajax({
								type:"POST",
								data:data,
								success:function(rtn) {
									if(rtn.success) {
										pagamentos[pagamentoIndex].baixas[baixaIndex].pago="1";
										$('#js-aside-asFinanceiro-receber .aside-close').click();
										$('#js-aside-asFinanceiro .aside-close').click();
										$('.js-pagamento-item-'+id_parcela).click();
										document.location.reload();
									} else if(rtn.error) {
										swal({title: "Erro!", text: rtn.error,  html:true,type:"error", confirmButtonColor: "#424242"});
									} else {
										swal({title: "Erro!", text: 'Algum erro ocorreu. Tente novamente!',  html:true,type:"error", confirmButtonColor: "#424242"});
									}
								}
							}).done(function(){
								obj.attr('data-loading',0);
								obj.html(objHTMLAntigo);
							})

						}
					}
				}
			})

			$('.js-tr-fusao').click(function(){
				let idPagamento = $(this).parent().attr('data-id_pagamento');
				
				if($(`.js-fusao-${idPagamento}:hidden`).length>0) {
					$(`.js-fusao-${idPagamento}`).show();
				} else {
					$(`.js-fusao-${idPagamento}`).hide();
				}
				
			})
			$('#cal-popup').on('click','.js-btn-pagamento',function(){
				let idPagamento = $(this).attr('data-id_pagamento');
				$.fancybox.open({
					type:`ajax`,
					src:`box/boxPacientePagamentos.php?id_pagamento=${idPagamento}`,
					opts: {
						'beforeClose':function(){
							document.location.reload();
						}
					}
				});
				return false;
			});

			$('#cal-popup').on('click','.js-btn-pagamento-excluir',function(){
				let idPagamento = pagamentos[index].id_parcela;
				swal({
					title: "Atenção",
					text: "Você tem certeza que deseja remover este registro?",
					type: "warning",
					showCancelButton: true,
					confirmButtonColor: "#DD6B55",
					confirmButtonText: "Sim!",
					cancelButtonText: "Não",
					closeOnConfirm:false,
					closeOnCancel: false }, 
					function(isConfirm){   
						if (isConfirm) {   
							document.location.href='?<?= "id_paciente=$paciente->id&id_pagamento=";?>'+idPagamento;
						} else {   
							swal.close();   
						} 
					});

			})


			$('.js-btn-fechar').click(function(){
				$('.cal-popup').hide();
				document.location.reload();
			});

		});
	</script>

	<main class="main">
		<div class="main__content content">

			<section class="filter">
				<div class="filter-group">
					<div class="filter-title">
						<h1>Ficha do Paciente</h1>
					</div>
				</div>
			</section>
			<script type="text/javascript">
				$(function(){
					$('.js-item').click(function(){

						let id = $(this).attr('data-id');
						document.location.href=`pg_pacientes_planosdetratamento_form.php?edita=${id}<?= empty($url)?"":"&".$url;?>`;
					})
					$('.js-valor').keyup(function(){
						let idPagamento = $('.js-id_pagamento').val()
						let pagamento = pagamentos.filter((item)=>{ return item.id_parcela==idPagamento })[0]
						let data = new Date(`${pagamento.vencimento.split('/')[2]}/${pagamento.vencimento.split('/')[1]}/${pagamento.vencimento.split('/')[0]}`);
						let hoje = new Date();
						let diferenca = (hoje.getTime()-data.getTime()) / (1000 * 60 * 60 * 24);
						if(diferenca>=1){
							if($('.js-aplicar-multas-juros').prop('checked')==true){
								$('.js-valorMultas').closest('dl').show()
								$('.js-valorJuros').closest('dl').show()
								$('.js-TotalaPagar').closest('dl').show()
								$('.js-descontoMultasJuros').closest('dl').show()
							}
							let ValorDigitado = unMoney($(this).val())
							let ValorMulta = (ValorDigitado*((_clinica[1].politica_multas)/100))
							let ValorJuros = (ValorDigitado*(((_clinica[1].politica_juros)/100)))*Math.floor(diferenca)
							$('.js-valorMultas').val(number_format(ValorMulta,2,",","."))
							$('.js-valorJuros').val(number_format(ValorJuros,2,",","."))
							$('.js-TotalaPagar').val(number_format(ValorDigitado+ValorMulta+ValorJuros,2,",","."))
						}else{
							$('.js-valorMultas').val(number_format(0,2,",","."))
							$('.js-valorJuros').val(number_format(0,2,",","."))
							$('.js-TotalaPagar').val(number_format(0,2,",","."))
							$('.js-descontoMultasJuros').val(number_format(0,2,",","."))
							$('.js-descontoMultasJuros').closest('dl').attr('disabled',true)
						}
					})
					$('.js-descontoMultasJuros').keyup(function(){
						let ValorDigitado = unMoney($(this).val())
						let valorOriginal = unMoney($('.js-valor').val())
						let ValorJuros = unMoney($('.js-valorJuros').val())
						let ValorMulta = unMoney($('.js-valorMultas').val())
						if(ValorDigitado>(valorOriginal+ValorJuros+ValorMulta)){
							swal({title: "Erro!", text: "Voce Não Pode Ofertar um Desconto Maior que o valor da Parcela Somando aos encargos!",  html:true,type:"error", confirmButtonColor: "#424242"});
							($(this).val("0"));
							ValorDigitado=0
						}
						$('.js-TotalaPagar').val(number_format((valorOriginal+ValorJuros+ValorMulta)-ValorDigitado,2,",","."))
					})
					$('.js-aplicar-multas-juros').click(function(){
						let idPagamento = $('.js-id_pagamento').val()
						let pagamento = pagamentos.filter((item)=>{ return item.id_parcela==idPagamento })[0]
						let data = new Date(`${pagamento.vencimento.split('/')[2]}/${pagamento.vencimento.split('/')[1]}/${pagamento.vencimento.split('/')[0]}`);
						let hoje = new Date();
						let diferenca = (hoje.getTime()-data.getTime()) / (1000 * 60 * 60 * 24);
						if(diferenca>=1){
							if($(this).prop('checked')==true){
								let ValorDigitado = unMoney($('.js-valor').val())
								if(ValorDigitado>0){
									let ValorMulta = (ValorDigitado*((_clinica[1].politica_multas)/100))
									let ValorJuros = (ValorDigitado*(((_clinica[1].politica_juros)/100)))*Math.floor(diferenca)
									$('.js-valorMultas').val(number_format(ValorMulta,2,",","."))
									$('.js-valorJuros').val(number_format(ValorJuros,2,",","."))
									$('.js-TotalaPagar').val(number_format(ValorDigitado+ValorJuros+ValorMulta,2,",","."))
								}
								$('.js-valorMultas').closest('dl').show()
								$('.js-valorJuros').closest('dl').show()
								$('.js-TotalaPagar').closest('dl').show()
								$('.js-descontoMultasJuros').closest('dl').show()
							}else{
							$('.js-valorMultas').val("0")
							$('.js-valorJuros').val("0")
							$('.js-valorMultas').closest('dl').hide()
							$('.js-valorJuros').closest('dl').hide()
							$('.js-TotalaPagar').closest('dl').hide()
							$('.js-descontoMultasJuros').closest('dl').hide()
							$('.js-valorMultas').val(number_format(0,2,",","."))
							$('.js-valorJuros').val(number_format(0,2,",","."))
							$('.js-TotalaPagar').val(number_format(0,2,",","."))
							$('.js-descontoMultasJuros').val(number_format(0,2,",","."))
			

						}
					}
					})
				})
			</script>
			<section class="grid">
				<div class="box box-col">
					<?#php require_once("includes/submenus/subPacientesFichaDoPaciente.php");?>
					<div class="box-col__inner1">
				
						<section class="filter">
							<div class="filter-group"></div>
							<div class="filter-group">
								<div class="filter-form form">
									<dl>
										<dd>
											<!-- <a href="pacientes-plano-form.php" class="button button_main"><i class="iconify" data-icon="fluent:add-circle-24-regular"></i><span>Nova Cobrança</span></a> -->
										</dd>
									</dl>
								</div>
							</div>							
						</section>

						<div class="box">

							<section class="filter">
								<?php
								if(isset($_GET['unirPagamentos'])) {
								?>
								<div class="filter-group js-unir">
									<div class="filter-form form">
										<dl>								
											<dd><input type="tel" name="" class="js-dataVencimento data datecalendar" placeholder="Nova data de vencimento" style="width:190px;" /></dd>
										</dl>
										<dl>								
											<dd>
												<a href="javascript:;" class="button button_main js-btn-unirPagamentos "><i class="iconify" data-icon="fluent:link-square-24-filled"></i><span>Salvar</span></a>
												<a href="<?= $_page."?".$url;?>" class="button tooltip" title="Cancelar" style="background: var(--vermelho);color:#FFF;"><i class="iconify" data-icon="topcoat:cancel"></i><span>Cancelar</span></a>
											</dd>
										</dl>
									</div>				
								</div>
								<?php
								} else {
								?>
								<div class="filter-group">
									<div class="filter-form form">
										<dl>
											<dd><a href="<?= $_page."?unirPagamentos=1&$url";?>" class="button"><i class="iconify" data-icon="fluent:link-square-24-filled"></i><span>Unir Pagamentos</span></a>
										</dl>
									</div>						
								</div>	
								<?php
								}
								?>
								
								<div class="filter-group">
									<div class="filter-title">
										<p style="color:var(--cinza5);font-size:18px">Total<br /><strong>R$ <?= number_format($valor['valorTotal'],2,",",".");?></strong></p>
									</div>
									<div class="filter-title">
										<p style="font-size:13px">A receber<br /><strong>R$ <?= number_format(($valor['aReceber']),2,",",".");?></strong></p>
									</div>
									<div class="filter-title">
										<p style="color:var(--laranja);font-size:13px">Definir Pagamento<br /><strong>R$ <?= number_format($valor['definirPagamento'],2,",",".");?></strong></p>
									</div>
									<div class="filter-title">
										<p style="color:var(--verde);font-size:13px">Recebido<br /><strong>R$ <?= number_format($valor['valorRecebido'],2,",",".");?></strong></p>
									</div>
									<div class="filter-title">
										<p style="color:var(--vermelho);font-size:13px">Vencido<br /><strong>R$ <?= number_format($valor['valoresVencido'],2,",",".");?></strong></p>
									</div>
									
								
								</div>
							</section>

							<form class="js-form-pagamentos" onsubmit="return false">
								<div class="list1">
									<table class="js-table-pagamentos">
									<?php
									
										$parcelasTratamentos=array();
										$DefinirPagamento=0;
										foreach($registros as $x) {
											if(!isset($parcelasTratamentos[$x->id_tratamento])) {
												$parcelasTratamentos[$x->id_tratamento]=0;
											}
											$parcelasTratamentos[$x->id_tratamento]++;
										}



										$pagamentosJSON=array();
										$numeroParcela=array();
										
										foreach($registros as $x) {
										
											$opacity=1;
											if(isset($_GET['unirPagamentos'])) {
												if(isset($pagamentosComBaixas[$x->id])) { 
													$opacity=0.3;
												}
											}

											$saldoAPagar=$x->valor;
											$valorCorrigido=$x->valor;
											$valorPago=$descontos=$multas=0;
											$dataUltimoPagamento='-';
											$valorDesconto=$valorDespesa=0;
											if(isset($_baixas[$x->id])) {
												$dataUltimoPagamento=date('d/m/Y',strtotime($_baixas[$x->id][count($_baixas[$x->id])-1]->data));
												foreach($_baixas[$x->id] as $v) {
													$v->valor=number_format($v->valor,3,".","");
													$saldoAPagar-=$v->valor;
													$valorPago+=$v->valor;
												}
											}
											
											$saldoAPagar=round(number_format($saldoAPagar,3,".",""));
											$atraso=(strtotime($x->data_vencimento)-strtotime(date('Y-m-d')))/(60*60*24);

										
											$status='';
											$icone = '';
											$baixas=$subpagamentos=array();
											// verifica se possui baixas 
											if(isset($_baixas[$x->id])) {
												$baixaVencida=false;
												$baixaEmAberta=false;
												foreach($_baixas[$x->id] as $b) {
													$formaobs='';
												//	$baixaVencida=false;
													if((strtotime($b->data_vencimento)<strtotime(date('Y-m-d'))) && $b->pago==0) {
														$baixaVencida=true;
														
													} else {  
														if($b->pago==0) {
															$baixaEmAberta=true;
														}
													}


												//	echo $b->data_vencimento."-> ".date('Y-m-d')." -> ".$baixaVencida."<BR>";
													if($b->tipoBaixa=="pagamento") {
														$formaobs=isset($_formasDePagamento[$b->id_formadepagamento])?utf8_encode($_formasDePagamento[$b->id_formadepagamento]->titulo):'';
													} else {
														$formaobs=$b->tipoBaixa;
														if($b->tipoBaixa=="desconto") {
															$formaobs="DESCONTO";
														} else if($b->tipoBaixa=="despesa") {
															$formaobs="DESPESA";
														}
													}
													$baixas[]=array("id_baixa"=>(int)$b->id,
																		"vencimento"=>$b->data_vencimento,
																		"data"=>date('d/m/Y',strtotime($b->data_vencimento)),
																		"valor"=>(float)$b->valor,
																	  	"tipoBaixa"=>isset($_tipoBaixa[$b->tipoBaixa])?$_tipoBaixa[$b->tipoBaixa]:$b->tipoBaixa,
																	  	"id_formadepagamento"=>(int)$b->id_formadepagamento,
																	   	"formaDePagamento"=>isset($_formasDePagamento[$b->id_formadepagamento])?utf8_encode($_formasDePagamento[$b->id_formadepagamento]->titulo):'',
																	   	"formaDePagamentoTipo"=>isset($_formasDePagamento[$b->id_formadepagamento])?$_formasDePagamento[$b->id_formadepagamento]->tipo:'',
																	   	"pago"=>$b->pago,
																	   	"recibo"=>$b->recibo,
																	   	"parcelas"=>$b->parcelas,
																	   	"vencido"=>(strtotime($b->data_vencimento)<strtotime(date('Y-m-d'))?true:false),
																	   	"parcela"=>$b->parcela,
																	   	"obs"=>utf8_encode($b->obs),
																	   	"valorJuros"=>$b->valorJuros,
																	   	"valorMulta"=>$b->valorMulta,
																	   	"taxa"=>$b->taxa,
																	   	"total"=>(float)$b->valor);
												

												}
											

												if($baixaVencida===true) {
													$status="INADIMPLENTE";
													$icone = 'fluent:warning-24-regular';
													$cor="red";
												} else if($baixaEmAberta==false && $saldoAPagar<=0) {
													$status="ADIMPLENTE";
													$icone = 'fluent:checkbox-checked-24-filled';
													$cor="green";
												} else {
													if($saldoAPagar==0) {
														$status="A RECEBER";
														$icone = 'fluent:calendar-ltr-24-regular';
														$cor="blue";
													} else {
														$cor="orange";
														$status="DEFINIR PAGAMENTO";
														$icone = 'fluent:checkbox-warning-24-regular';
													}
												}
												if($saldoAPagar>0){
													if(strtotime($x->data_vencimento)<strtotime(date('Y-m-d'))) {
														$cor="red";
														$status="INADIMPLENTE";
														$icone = 'fluent:warning-24-regular';
													}
												}
												
											} 
											// nao possui nenhuma baixa
											else {  
											
												if(strtotime($x->data_vencimento)<strtotime(date('Y-m-d'))) {
													$cor="red";
													$status="INADIMPLENTE";
													$icone = 'fluent:warning-24-regular';
												}
												else {
													$cor="orange";
													$status="DEFINIR PAGAMENTO";
													$icone = 'fluent:checkbox-warning-24-regular';
												}
											}
											$subpagamentos=array();
											if($x->fusao>0) {
												$titulo="União de Pagamentos (".(isset($_subpagamentos[$x->id])?count($_subpagamentos[$x->id]):0).")";
												if(isset($_subpagamentos[$x->id])) {
													foreach($_subpagamentos[$x->id] as $y) {
														$subpagamentos[]=array('id_pagamento'=>$y->id,
																				'vencimento'=> date('d/m/Y',strtotime($y->data_vencimento)),
																				'titulo'=>isset($_tratamentos[$y->id_tratamento])?utf8_encode($_tratamentos[$y->id_tratamento]->titulo):'Avulso',
																				'valor'=>$y->valor);
													}
												}

											} else {
												$titulo=isset($_tratamentos[$x->id_tratamento])?utf8_encode($_tratamentos[$x->id_tratamento]->titulo):'Avulso';
											}


											$statusPromessa=false;
											$statusInadimplente=false;
											$todasPagas=false;

											// nao possui baixa
											if(count($baixas)==0) {
												if(strtotime($x->data_vencimento)<strtotime(date('Y-m-d'))) {
													$statusInadimplente=true;
												}
											}
											// possui baixa
											else { 
												// se saldo = 0
												if($saldoAPagar==0) {
													$baixaVencida=false;
													$baixaPaga=false; 
													$todasPagas=true;
													foreach($baixas as $b) {

														$b=(object)$b;
														if($b->pago==0) {
															if(strtotime(date('Y-m-d'))>strtotime($b->vencimento)) { 
																$baixaVencida=true;
															} 

														
															$todasPagas=false;
														} else {
															$baixaPaga=true;
														}
													}


													// se possui baixa vencida
													if($baixaVencida===true) { 
														$statusInadimplente=true;

														// se todas foram pagas
														if($todasPagas===true) {

														} else {
															$statusPromessa=true;
														}
													} else { 
														// se todas foram pagas
														if($todasPagas===true) {

															$statusPromessa=true;
														} else {
															$statusPromessa=true;
														}
													}
												} else {

												}
											
											} 

											$item=array('id_parcela'=>$x->id,
														'titulo'=>$titulo,
														'vencimento'=>  date('d/m/Y',strtotime($x->data_vencimento)),
														'valorParcela'=>$x->valor,
														'valorDesconto'=>$valorDesconto,
														'valorDespesa'=>$valorDespesa,
														'valorCorrigido'=>$valorCorrigido,
														'valorPago'=>$valorPago,
														'baixas'=>$baixas,
														'subpagamentos'=>$subpagamentos,
														'saldoApagar'=>$saldoAPagar,
														'fusao'=>$x->fusao,
														'multaAtraso'=>0,
														'jurosMensal'=>0);

											$pagamentosJSON[]=$item;
											if($status=='DEFINIR PAGAMENTO'){
												$DefinirPagamento+=$x->valor;
											}
			
										?>
										<tr class="js-pagamento-item js-pagamento-item-<?= $x->id;?>" data-id="<?= $x->id;?>">
											<?php if(isset($_GET['unirPagamentos'])) {?>
												<td style="width:30px;">
													<?php
													if($x->fusao==0 and !isset($pagamentosComBaixas[$x->id])) {
													?>
													<input type="checkbox" name="pagamentos[]" class="js-checkbox-pagamentos" data-id_tratamento="<?= $x->id_tratamento;?>" value="<?= $x->id;?>" />
													<span class="iconify js-checkbox-pagamentos-disabled" data-icon="fxemoji:cancellationx" style="opacity:0.2;display:none;" data-id_tratamento="<?= $x->id_tratamento;?>"></span>
													<?php
													} else {
														echo '<span class="iconify" data-icon="fxemoji:cancellationx" style="opacity:0.2"></span>';
													}
													?>

												</td>
											<?php }	?>
											<td>
												<h1>
													<?php  
													if($x->fusao>0) {
													?>
													<strong><i class="iconify" data-icon="codicon:group-by-ref-type" data-height="18" data-inline="true"></i> União de Pagamentos (<?= isset($_subpagamentos[$x->id])?count($_subpagamentos[$x->id]):0;?>)</strong>
													<?php
													} else {
														echo isset($_tratamentos[$x->id_tratamento])?utf8_encode($_tratamentos[$x->id_tratamento]->titulo):'Avulso';
													}
													?>
												</h1>
												<p><?= date('d/m/Y',strtotime($x->data_vencimento));?></p>
											</td>
											<td><div class="list1__icon" style="color:gray;"><font color=<?=$cor?>><i class="iconify" data-icon="<?=$icone?>"></i> <?=$status?></font> </div></td>
											<td><h1>R$ <?= number_format($x->valor,2,",",".");?></h1><span><?=($status== 'INADIMPLENTE' && $saldoAPagar>0)?"Faltam: R$ ".number_format($saldoAPagar,2,",","."):""?></span></td>
											<?php 
											if(isset($parcelasTratamentos[$x->id_tratamento])) {
												if(!isset($numeroParcela[$x->id_tratamento])) $numeroParcela[$x->id_tratamento]=1;
											?>
											<td>Parcela  <?= $numeroParcela[$x->id_tratamento]++;?> de <?= ($parcelasTratamentos[$x->id_tratamento]);?></td>
											<?php
											}
											?>
										</tr>
										<?php
										}
										?>
									</table>
								</div>	
							</form>
						</div>
					</div>
				</div>
			</section>

		</div>
	</main>

	<script type="text/javascript">
		pagamentos = JSON.parse(`<?= json_encode($pagamentosJSON);?>`);
	
	</script>

<?php 
	
	require_once("includes/api/apiAsideFinanceiro.php");

	include "includes/footer.php";
?>	