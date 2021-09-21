<?php
	require_once("../lib/conf.php");
	$dir="../";
	require_once("../usuarios/checa.php");

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
				
				$tipoBaixa = (isset($_POST['tipoBaixa']) and !empty($_POST['tipoBaixa']))?$_POST['tipoBaixa']:"";
				$obs = (isset($_POST['obs']) and !empty($_POST['obs']))?$_POST['obs']:"";
				$cobrarJuros = (isset($_POST['cobrarJuros']) and isset($_POST['cobrarJuros']) and $_POST['cobrarJuros']==1)?$_POST['cobrarJuros']:0;
				$debitoBandeira = (isset($_POST['debitoBandeira']) and is_numeric($_POST['debitoBandeira']))?$_POST['debitoBandeira']:"";
				$creditoBandeira = (isset($_POST['creditoBandeira']) and is_numeric($_POST['creditoBandeira']))?$_POST['creditoBandeira']:"";
				$creditoParcelas = (isset($_POST['creditoParcelas']) and is_numeric($_POST['creditoParcelas']))?$_POST['creditoParcelas']:"";
				$id_operadora = (isset($_POST['id_operadora']) and is_numeric($_POST['id_operadora']))?$_POST['id_operadora']:0;
				$taxa = (isset($_POST['taxa']) and !empty($_POST['taxa']))?valor($_POST['taxa']):0;

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
										id_operadora='".$id_operadora."',
										taxa='".$taxa."',
										id_bandeira='".$debitoBandeira."',	
										valor='".($valor)."',
										id_usuario='".$usr->id."'";
						} else {
							$vSQLBaixa="data='".invDatetime($dataPagamento)."',
										data_vencimento='".($dataVencimento)."',
										id_pagamento=$pagamento->id,
										id_formadepagamento=$formaDePagamento->id,
										tipoBaixa='".$tipoBaixa."',
										valor='".($valor)."',
										id_usuario='".$usr->id."'";
						}
					} else {
						$vSQLBaixa="data='".invDatetime($dataPagamento)."',
										id_pagamento=$pagamento->id,
										tipoBaixa='".$tipoBaixa."',
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
			$sql->consult($_p."pacientes_tratamentos_pagamentos_baixas","*","where id_pagamento=$pagamento->id and lixo=0 order by data desc");
			while($x=mysqli_fetch_object($sql->mysqry)) {

				$baixas[]=array("id_baixa"=>(int)$x->id,
								"data"=>date('d/m/Y',strtotime($x->data_vencimento)),
								"valor"=>(float)$x->valor,
							  	"tipoBaixa"=>isset($_tipoBaixa[$x->tipoBaixa])?$_tipoBaixa[$x->tipoBaixa]:$x->tipoBaixa,
							  	"id_formadepagamento"=>(int)$x->id_formadepagamento,
							   	"formaDePagamento"=>isset($_formasDePagamento[$x->id_formadepagamento])?utf8_encode($_formasDePagamento[$x->id_formadepagamento]->titulo):'',
							   	"pago"=>$x->pago,
							   	"recibo"=>$x->recibo,
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
					$sql->consult($_p."pacientes_tratamentos_pagamentos","*","where id IN (".implode(",",$_POST['pagamentos']).")");
					if($sql->rows) {
						while($x=mysqli_fetch_object($sql->mysqry)) {
							$valor+=$x->valor;
							$uniaoIds[]=$x->id;
							$id_paciente=$x->id_paciente;
							$id_unidade=$x->id_unidade;
						}
					}

					if(count($uniaoIds)>=2) {
						$vSQL="data=now(),
								data_vencimento='".(isset($_POST['dataVencimento'])?invDate($_POST['dataVencimento']):now())."',
								id_usuario=$usr->id,
								id_unidade=$id_unidade,
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
						$sql->update($_p."pacientes_tratamentos_pagamentos","lixo=1","where id=$pagamento->id");
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


		header("Content-type: application/json");
		echo json_encode($rtn);

		die();
	}

	$jsc = new Js();
	if(isset($_GET['id_pagamento']) and is_numeric($_GET['id_pagamento'])) {
		$sql->consult($_p."pacientes_tratamentos_pagamentos","*","where id='".$_GET['id_pagamento']."'");
		if($sql->rows) {
			$pagamento=mysqli_fetch_object($sql->mysqry);
		}
	}

	if(empty($pagamento)) {
		$jsc->jAlert("Pagamento não encontrado!","erro","$.fancybox.close()");
		die();
	}

	$saldoAPagar=$pagamento->valor;
 	
	$creditoBandeiras=array();
	$debitoBandeiras=array();

	$_bandeiras=array();
	$sql->consult($_p."parametros_cartoes_bandeiras","*","where lixo=0");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_bandeiras[$x->id]=$x;
	}

	$sql->consult($_p."parametros_cartoes_operadoras","*","where lixo=0 order by titulo");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$creditoBandeiras[$x->id]=array('titulo'=>utf8_encode($x->titulo),'bandeiras'=>array());
		$debitoBandeiras[$x->id]=array('titulo'=>utf8_encode($x->titulo),'bandeiras'=>array());
	}

	$_semJuros=array();
	$sql->consult($_p."parametros_cartoes_taxas_semjuros","*","where lixo=0");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_semJuros[$x->id_operadora][$x->id_bandeira]=$x->semjuros;
	}

	$_parcelas=array();
	$sql->consult($_p."parametros_cartoes_taxas","*","where operacao='credito' and lixo=0 order by parcela asc");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_parcelas[$x->id_operadora][$x->id_bandeira]=$x->parcela;
	}

	$sql->consult($_p."parametros_cartoes_taxas","*","where lixo=0");
	$_taxasCredito=$_taxasCreditoSemJuros=array();
	while($x=mysqli_fetch_object($sql->mysqry)) {

		if(isset($_bandeiras[$x->id_bandeira])) {
			$bandeira=$_bandeiras[$x->id_bandeira];

			if($x->operacao=="credito") {
				if(isset($creditoBandeiras[$x->id_operadora])) {

					//$parcelas=isset($_parcelas[$x->id_operadora][$x->id_bandeira])?$_parcelas[$x->id_operadora][$x->id_bandeira]:0;

					//$semJuros=0;
					//if(isset($_semJuros[$x->id_operadora][$bandeira->id])) $semJuros=$_semJuros[$x->id_operadora][$bandeira->id];

					$semJurosTexto="";
					if($bandeira->parcelasAte>0) {
						$semJurosTexto.=" - em ate ".$bandeira->parcelasAte."x";
						//if($semJuros>0) {
						//	$semJurosTexto.=", sendo ate ".$semJuros."x sem juros";
						//} 
					}

					//echo $x->id_operadora."-".$x->id_bandeira."->".$x->parcela."->".$x->taxa."<BR>";
					if($x->parcela==$x->vezes) {
						$_taxasCredito[$x->id_operadora][$x->id_bandeira][$x->parcela]=(float)$x->taxa;
					}
				//	$_taxasCreditoSemJuros[$x->id_operadora][$bandeira->id][$x->parcela]=($semJuros>0 && $semJuros<$x->parcela)?1:0;



					$creditoBandeiras[$x->id_operadora]['bandeiras'][$x->id_bandeira]=array('id_bandeira'=>$bandeira->id,
																						//	'semJuros'=>$semJuros,
																							'parcelas'=>$bandeira->parcelasAte,
																							'taxa'=>$x->taxa,	
																							'titulo'=>utf8_encode($bandeira->titulo).$semJurosTexto);
				}
			} else {
				if($bandeira->aceitaDebito==1) {
					$debitoBandeiras[$x->id_operadora]['bandeiras'][$x->id_bandeira]=array('id_bandeira'=>$bandeira->id,
																							'titulo'=>utf8_encode($bandeira->titulo),
																							'taxa'=>$x->taxa);
				}
			}

		}
	}

?>
<script type="text/javascript">
	id_pagamento = <?php echo $pagamento->id;?>;

	var _taxasCredito = JSON.parse('<?php echo json_encode($_taxasCredito);?>');
	var _taxasCreditoSemJuros = JSON.parse('<?php echo json_encode($_taxasCreditoSemJuros);?>');
	var saldoAPagar = 0;

	$(function(){

		$('.js-dataPagamento').datetimepicker({
			timepicker:true,
			format:'d/m/Y H:i',
			scrollMonth:false,
			scrollTime:false,
			scrollInput:false,
		}).inputmask('99/99/9999 99:99');

		$('input.data').datetimepicker({
			timepicker:false,
			format:'d/m/Y',
			scrollMonth:false,
			scrollTime:false,
			scrollInput:false,
		}).inputmask('99/99/9999');

		$('input.money').maskMoney({symbol:'', allowZero:true, showSymbol:true, thousands:'.', decimal:',', symbolStay: true});

		$('.js-btn-addPagamento').click(function(){
			let tipoPagamento = $('.js-id_formadepagamento option:checked').attr('data-tipo');

			saldoAPagar = unMoney($('.js-saldoPagar').val());
			
			let tipoBaixa = $('input[name=tipoBaixa]:checked').val();

			if(tipoBaixa=="despesa" || tipoBaixa=="desconto" || tipoPagamento!==undefined) {

				let dataPagamento = $('input.js-dataPagamento').val();
				let dataVencimento = $('input.js-vencimento').val();
				let valor = ($('input.js-valor').val().length>0 && unMoney($('input.js-valor').val())>0)?unMoney($('input.js-valor').val()):'';
				let id_formadepagamento = $('.js-id_formadepagamento').val();
				let obs = $('input.js-obs').val();
				let debitoBandeira=$('select.js-debitoBandeira').val();
				let creditoBandeira=$('select.js-creditoBandeira').val();
				let creditoParcelas=$('select.js-parcelas').val();
				let id_operadora=0;
				let taxa=0;

				let erro = '';

				if(tipoBaixa=='pagamento') {
					if(tipoPagamento=='credito') {
						if(dataVencimento.length==0) erro= 'Defina a <b>Data do Vencimento</b>';
						else if(valor.length==0) erro= 'Defina o <b>Valor do Pagamento</b>';
						else if(creditoBandeira.length==0) erro= 'Selecione a <b>Bandeira</b> do Cartão de Crédito';
						else if(creditoParcelas.length==0) erro= 'Selecione o <b>Nº de Parcelas</b> do Cartão de Crédito';

						id_operadora=$('select.js-creditoBandeira option:selected').attr('data-id_operadora');
						taxa=$('input.js-valorCreditoDebitoTaxa').val().replace(/[^\d,-.]+/g,'');
						valorParcela=$('input.js-valorCreditoDebito').val().replace(/[^\d,-.]+/g,'');

					} else if(tipoPagamento=='debito') {
						if(dataVencimento.length==0) erro= 'Defina a <b>Data do Vencimento</b>';
						else if(valor.length==0) erro= 'Defina o <b>Valor do Pagamento</b>';
						else if(debitoBandeira.length==0) erro= 'Selecione a <b>Bandeira</b> do Cartão de Débito';
						
						id_operadora=$('select.js-debitoBandeira option:selected').attr('data-id_operadora');
						taxa=$('input.js-valorCreditoDebitoTaxa').val().replace(/[^\d,-.]+/g,'');
						valorParcela=$('input.js-valorCreditoDebito').val().replace(/[^\d,-.]+/g,'');

					} else {

						id_operadora=0;
						taxa=0;
						valorParcela=$('input.js-valor').val().replace(/[^\d,-.]+/g,'');

						if(dataVencimento.length==0) erro= 'Defina a <b>Data do Vencimento</b>';
						else if(valor.length==0) erro= 'Defina o <b>Valor do Pagamento</b>';
					}
				} else if(tipoBaixa=='desconto' || tipoBaixa=='despesa') {
					if(dataPagamento.length==0) erro= 'Defina a <b>Data do Pagamento</b>';
					else if(valor.length==0) erro= 'Defina o <b>Valor</b>';
					else if(obs.length==0) erro= 'Digite uma <b>Observação</b>';
					valorParcela=$('input.js-valor').val().replace(/[^\d,-.]+/g,'');
				}



				if(dataPagamento.length==0) erro='Defina a <b>Data</b>';
				else if(tipoBaixa.length==0) erro='Defina o <b>Tipo de Baixa</b>';
				else if(valor.length==0) erro= 'Defina o <b>Valor</b> a ser pago';
				else if(tipoBaixa=="pagamento" && id_formadepagamento.length==0) erro='Defina a <b>Forma de Pagamento</b>';
				else if(saldoAPagar<=0) erro=`Não existe mais débitos!`; 
				else if(saldoAPagar<unMoney(valorParcela)) erro=`O valor não pode ser maior que <b>${number_format(saldoAPagar,2,",",".")}`;
			
				if(erro.length==0) {
					//return;
					let data = `ajax=pagamentoBaixa&tipoBaixa=${tipoBaixa}&id_pagamento=${id_pagamento}&dataPagamento=${dataPagamento}&dataVencimento=${dataVencimento}&valor=${valor}&id_formadepagamento=${id_formadepagamento}&debitoBandeira=${debitoBandeira}&creditoBandeira=${creditoBandeira}&creditoParcelas=${creditoParcelas}&obs=${obs}&id_operadora=${id_operadora}&taxa=${taxa}&valorParcela=${unMoney(valorParcela)}`;
				
					$.ajax({
						type:"POST",
						url:'box/<?php echo basename($_SERVER['PHP_SELF']);?>',
						data:data,
						success:function(rtn) {
							if(rtn.success) {
								baixas=rtn.baixas;
								baixasAtualizar();
								$('.js-dataPagamento').val('<?php echo date('d/m/Y');?>');
								$('.js-valor').val('');
								$('.js-desconto').val('');
								$('.js-id_formadepagamento').val('');
								$('.js-obs').val('');
								if(saldoAPagar<=0) $('.js-form-pagamentos').hide();

							} else if(rtn.error) {
								swal({title: "Erro!", text: rtn.error,  html:true,type:"error", confirmButtonColor: "#424242"});
							} else {
								swal({title: "Erro!", text: "Algum erro ocorreu durante a baixa deste pagamento!",  html:true,type:"error", confirmButtonColor: "#424242"});
							}
						},
						error:function() {
							swal({title: "Erro!", text: "Algum erro ocorreu durante a baixa deste pagamento!",  html:true,type:"error", confirmButtonColor: "#424242"});
						}
					});
				} else {
					swal({title: "Erro!", text: erro,  html:true,type:"error", confirmButtonColor: "#424242"});
				}
			} else {
				swal({title: "Erro!", text: "Define a <b>Forma de Pagamento</b>",  html:true,type:"error", confirmButtonColor: "#424242"});		
			}
		});
		var descontoAntigo = $('.js-valorDesconto').val();

		$('.js-valorDesconto').focus(function(){
			descontoAntigo=$(this).val();
		});

		$('.js-valorDesconto,.js-valorDespesa').change(function(){

			let valor = unMoney($(this).val());
			let tipo = $(this).attr('data-tipo');
			let valorCorrigido = unMoney($('.js-valorCorrigido').val())

			if(tipo=="descontos" && valorCorrigido>0 && valor>valorCorrigido) {
				swal({title: "Erro!", text: "O <b>Desconto</b> não pode ser maior que o <b>Valor Corrigido</b>", html:true,type:"error", confirmButtonColor: "#424242"},function(){$('.js-valorDesconto').val(descontoAntigo)});
			} else {

				data = `ajax=valoresPersistir&tipo=${tipo}&valor=${valor}&id_pagamento=${id_pagamento}`;
				$.ajax({
					type:"POST",
					url:'box/<?php echo basename($_SERVER['PHP_SELF']);?>',
					data:data,
					success:function(rtn) {
						baixasAtualizarValores();
					}
				});
			}
		});

		$('input[name=tipoBaixa]').click(function(){
			
			if($(this).val()=="pagamento") {
				$('.js-tipoPagamento').parent().parent().show();
				$('.js-tipoDescontoDespesa').parent().parent().hide();
				$('.js-obs').parent().parent().hide();
				$('select.js-id_formadepagamento').trigger('change');
			} else { 
				$('.js-tipoPagamento').parent().parent().hide();
				$('.js-tipoDescontoDespesa').parent().parent().show();
				$('.js-obs').parent().parent().show();
			}
		});

		$('select.js-id_formadepagamento').change(function(){
			let id_formadepagamento  = $(this).val();
			let tipo = $('select.js-id_formadepagamento option:checked').attr('data-tipo');

			$('.js-identificador,.js-parcelas,.js-creditoBandeira,.js-debitoBandeira,.js-debitoBandeira,.js-valorCreditoDebito,.js-obs,.js-valorCreditoDebitoTaxa').parent().parent().hide();

			$('.js-txt-vencimento').html('Pagamento');

			if(tipo=="boleto" || tipo=="cheque") {

				$('.js-txt-vencimento').html('Vencimento');
			}

			if(tipo=="credito") {
				$('.js-parcelas,.js-creditoBandeira,.js-valorCreditoDebito,.js-valorCreditoDebitoTaxa').parent().parent().show();
			} else if(tipo=="debito") {
				$('.js-debitoBandeira,.js-valorCreditoDebito,.js-valorCreditoDebitoTaxa').parent().parent().show();
			} else {
				$('.js-identificador').parent().parent().show();

				if(tipo=="permuta") {
					$('.js-obs').parent().parent().show();
				}
			}
			$('.js-valorCreditoDebito').val('');
			$('.js-valorCreditoDebitoTaxa').val('');
			$('.js-valor').trigger('change')
		});

		$('input[name=tipoBaixa]:eq(0)').trigger('click');

		$('select.js-id_formadepagamento').trigger('change');

		$('.js-debitoBandeira,.js-creditoBandeira,.js-parcelas,.js-valor').change(function(){
			$('.js-valorCreditoDebito').val('');
			$('.js-valorCreditoDebitoTaxa').val('');
			creditoDebitoValorParcela();
		});

		$('.js-valor').keyup(function(){
			creditoDebitoValorParcela();
		})

		$('.js-table-baixas').on('click','.js-estorno',function(){

			
			let id_baixa = $(this).attr('data-id_baixa')
			let data = `ajax=baixaEstornar&id_baixa=${id_baixa}&id_pagamento=${id_pagamento}`;

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
						$.ajax({
							type:"POST",
							url:'box/<?php echo basename($_SERVER['PHP_SELF']);?>',
							data:data,
							success:function(rtn) {
								if(rtn.success) {
									baixasAtualizar();
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
				});

			
		});

		$('.js-table-baixas').on('click','.js-estornoPagamento',function(){

			
			let id_baixa = $(this).attr('data-id_baixa')
			let data = `ajax=baixaEstornarPagamento&id_baixa=${id_baixa}&id_pagamento=${id_pagamento}`;

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
							url:'box/<?php echo basename($_SERVER['PHP_SELF']);?>',
							data:data,
							success:function(rtn) {
								if(rtn.success) {
									baixasAtualizar();
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
				});

			
		});

		$('.js-table-baixas').on('click','.js-pagar',function(){
			let id_baixa = $(this).attr('data-id_baixa')
			$.fancybox.open({
				src:`box/boxPacientePagamentosPagar.php?id_baixa=${id_baixa}`,
				type:"ajax"
			})
		});


		
		baixasAtualizar();
	});
</script>

<section class="modal" style="height:auto; width:950px;">

	<header class="modal-header">
		<div class="filtros">
			
			<h1 class="filtros__titulo">Financeiro</h1>
		
			<?php /*<div class="filtros-acoes">
				<button type="button" class="principal js-salvar" onclick="document.location.reload();"><i class="iconify" data-icon="bx-bx-check"></i></button>
			</div>*/?>

			<div class="filter-group filter-group_right">
				<div class="filter-button">
					<a href="javascript:;" class="azul js-salvar" onclick="document.location.reload();"><i class="iconify" data-icon="bx-bx-check"></i><span>salvar</span></a>
				</div>
			</div>
		</div>
	</header>
	
	<article class="modal-conteudo">

		<form method="post" class="form js-form-agendamento">

			<fieldset>
				<legend>Informações do Pagamento</legend>
				<div class="colunas5">
					<dl>
						<dt>Valor da Parcela</dt>
						<dd><input type="text" class="js-valorParcela" value="<?php echo number_format($pagamento->valor,2,",",".");?>"  disabled style="background: #ccc" /></dd>
					</dl>

					<dl>
						<dt>Desconto (-)</dt>
						<dd><input type="text" class="js-valorDesconto money" data-tipo="descontos" style="background: #ccc" disabled  /></dd>
					</dl>
					<dl>
						<dt>Despesa (+)</dt>
						<dd><input type="text" class="js-valorDespesa money" data-tipo="despesas" style="background: #ccc" disabled  /></dd>
					</dl>
					<dl>
						<dt>Multas/Juros (+)</dt>
						<dd><input type="text" class="js-multasJuros money"  value="0,0" style="background: #ccc"  /></dd>
					</dl>
					<?php
					$valorCorrigido=$pagamento->valor;//+($pagamento->descontos*-1)+($pagamento->despesas);
					?>
					<dl>
						<dt>Valor Corrigido</dt>
						<dd><input type="text" class="js-valorCorrigido" value="<?php echo number_format($valorCorrigido,2,",",".");?>" disabled style="background: #ccc" /></dd>
					</dl>

				</div>
			</fieldset>

			<fieldset class="js-fieldset-pagamentos" style="display: none">
				<legend>Baixa</legend>
				<div class="colunas5">

					<dl>
						<dt>Saldo a Pagar</dt>
						<dd><input type="text" class="js-saldoPagar" value="" disabled style="background:#ccc" /></dd>
					</dl>

					<dl class="dl3">
						<dd>
							<label><input type="radio" name="tipoBaixa" value="pagamento" /> Pagamento</label>
							<label><input type="radio" name="tipoBaixa" value="desconto" /> Desconto (-)</label>
							<label><input type="radio" name="tipoBaixa" value="despesa" /> Despesa (+)</label>
						</dd>
					</dl>

					
					
				</div>
				<div class="colunas5">
					<dl>
						<dt>Data Pagamento</dt>
						<dd><input type="text" class="js-dataPagamento js-tipoDescontoDespesa datahora" value="<?php echo date('d/m/Y H:i');?>" /></dd>
					</dl>
					<dl class="dl2">
						<dt>Forma de Pagamento</dt>
						<dd>
							<select class="js-id_formadepagamento js-tipoPagamento">
								<option value="">-</option>
								<?php echo $optionFormasDePagamento;?>
							</select>
						</dd>
					</dl>
					
					<dl>
						<dt class="js-txt-vencimento">Pagamento</dt>
						<dd><input type="text" class="js-vencimento js-tipoPagamento data" value="<?php echo date('d/m/Y');?>" /></dd>
					</dl>
					<dl>
						<dt>Valor</dt>
						<dd><input type="text" class="js-valor money" /></dd>
					</dl>

					<dl>
						<dt>Identificador</dt>
						<dd><input type="text" class="js-identificador js-tipoPagamento" /></dd>
					</dl>

					<dl>
						<dt>Valor da Parcela</dt>
						<dd><input type="text" class="js-valorCreditoDebito js-tipoPagamento" readonly style="background:#CCC" /></dd>
					</dl>

					<dl>
						<dt>Taxa (%)</dt>
						<dd><input type="text" class="js-valorCreditoDebitoTaxa js-tipoPagamento" readonly style="background:#CCC" /></dd>
					</dl>



					<dl class="dl3">
						<dt>Obs.:</dt>
						<dd><input type="text" class="js-obs js-tipoDescontoDespesa" /></dd>
					</dl>
				</div>
				<div class="colunas5">

					<dl class="dl3">
						<dt>Bandeira</dt>
						<dd>
							<select class="js-creditoBandeira js-tipoPagamento">
								<option value="">selecione</option>
								<?php
								foreach($creditoBandeiras as $id_operadora=>$x) {
									echo '<optgroup label="'.utf8_encode($x['titulo']).'">';
									foreach($x['bandeiras'] as $band) {
										echo '<option value="'.$band['id_bandeira'].'" data-id_operadora="'.$id_operadora.'" data-semjuros="'.$band['semJuros'].'" data-parcelas="'.$band['parcelas'].'" data-taxa="'.$band['taxa'].'">'.utf8_encode($band['titulo']).'</option>';
									}
									echo '</optgroup>';
								}
								
								?>
							</select>
						</dd>
					</dl>

					<dl class="dl2">
						<dt>Qtd. Parcelas</dt>
						<dd>
							<select class="js-parcelas js-tipoPagamento">
								<option value="">selecione a bandeira</option>
							</select>
						</dd>
					</dl>

					<script type="text/javascript">
						$(function(){
							$('.js-creditoBandeira').change(function(){
								$('select.js-parcelas option').remove();
								
								if($(this).val().length>0) {
									let parcelas = eval($(this).find('option:checked').attr('data-parcelas'));
									//alert(parcelas);
									if($.isNumeric(parcelas)) {
										$('select.js-parcelas').append(`<option value="">-</option>`);
										for(var i=1;i<=parcelas;i++) {
											$('select.js-parcelas').append(`<option value="${i}">${i}x</option>`);
										}
									} else {
										$('select.js-parcelas').append(`<option value="">erro</option>`);
									}
								} else {
									$('select.js-parcelas').append(`<option value="">selecione a bandeira</option>`);
								}

							});
						});
					</script>


					

					<?php
					/*
						copiar sistema do cartao decolar formas de pagamento
						
					*/
					?>
					

				</div>

				<div class="colunas5">
					<dl class="dl3">
						<dt>Bandeira</dt>
						<dd>
							<select class="js-debitoBandeira js-tipoPagamento">
								<option value="">selecione</option>
								<?php
								foreach($debitoBandeiras as $id_operadora=>$x) {
									echo '<optgroup label="'.utf8_encode($x['titulo']).'">';
									foreach($x['bandeiras'] as $band) {
										echo '<option value="'.$band['id_bandeira'].'" data-id_operadora="'.$id_operadora.'" data-taxa="'.$band['taxa'].'" data-cobrarTaxa="'.$band['cobrarTaxa'].'">'.utf8_encode($band['titulo']).'</option>';
									}
									echo '</optgroup>';
								}
								?>
							</select>
						</dd>
					</dl>
				</div>

				<dl>
					<dd><a href="javascript:;" class="button__full button js-btn-addPagamento">adicionar baixa</a></dd>
				</dl>

				
			</fieldset>

			<fieldset>
				<legend>Pagamentos</legend>

				<div class="registros">
					<table class="js-table-baixas">
						<tr>
							<th></th>
							<th>Data Recebimento</th>
							<th>Tipo de Baixa</th>
							<th>Forma/Obs.</th>
							<th>Valor</th>
							<th style="width:80px;"></th>
						</tr>

					</table>
				</div>

			</fieldset>

			
				
		</form>
	</article>

</section>