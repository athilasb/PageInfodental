<?php


	if(isset($_POST['ajax'])) {

		require_once("../lib/conf.php");
		$dir="../";
		require_once("../usuarios/checa.php");


		header("Content-type: application/json");
		$rtn=array();

		if($_POST['ajax']==="persistirBaixa") {
			
			$baixa='';
			if(isset($_POST['id_baixa']) and is_numeric($_POST['id_baixa'])) {
				$sql->consult($_p."pacientes_tratamentos_pagamentos_baixas","*","where id='".$_POST['id_baixa']."'");
				if($sql->rows) {
					$baixa=mysqli_fetch_object($sql->mysqry);

				}
			}

			$dataPagamento='';
			if(isset($_POST['dataPagamento']) and !empty($_POST['dataPagamento'])) {
				list($dia,$mes,$ano) = explode("/",$_POST['dataPagamento']);
				if(checkdate($mes, $dia, $ano)) {
					$dataPagamento=$ano."-".$mes."-".$dia;
				}
			}



			if(is_object($baixa))  {
				if(!empty($dataPagamento)) {
					$sql->update($_p."pacientes_tratamentos_pagamentos_baixas","pago=1,pago_data='".$dataPagamento."',pago_id_usuario=$usr->id","where id=$baixa->id");

					$rtn=array('success'=>true);
				} else {
					$rtn=array('success'=>false,'error'=>'Defina uma data de pagamento válida!');
				}
			} else {
				$rtn=array('success'=>false,'error'=>'Baixa não encontrada!');
			}

		}

		echo json_encode($rtn);

		die();
	}
	require_once("../lib/conf.php");
	$dir="../";
	require_once("../usuarios/checa.php");

	$_formasDePagamento=array();
	$sql->consult($_p."parametros_formasdepagamento","*","order by titulo asc");
	while($x=mysqli_fetch_object($sql->mysqry)) $_formasDePagamento[$x->id]=$x;
	

	$_bancos=array();
	$sql->consult($_p."financeiro_bancosecontas","*","order by titulo asc");
	while($x=mysqli_fetch_object($sql->mysqry)) $_bancos[$x->id]=$x;

	$baixa=$pagamento='';

	if(isset($_GET['id_baixa']) and is_numeric($_GET['id_baixa'])) {
		$sql->consult($_p."pacientes_tratamentos_pagamentos_baixas","*","where id='".$_GET['id_baixa']."' and lixo=0");
		if($sql->rows) {
			$baixa=mysqli_fetch_object($sql->mysqry);
		
			$sql->consult($_p."pacientes_tratamentos_pagamentos","*","where id=$baixa->id_pagamento and lixo=0");
			if($sql->rows) {
				$pagamento=mysqli_fetch_object($sql->mysqry);
			}
		}
	}
	$jsc=new Js();
	
	if(empty($pagamento)) {
		$jsc->jAlert("Parcela não encontrada!","erro","$.fancybox.close()");
		die();
	} else if(empty($baixa)) {
		$jsc->jAlert("Baixa não encontrada!","erro","$.fancybox.close()");
		die();
	} else if($baixa->pago==1) {
		$jsc->jAlert("Esta baixa já foi paga!","erro","$.fancybox.close()");
		die();
	}
		
?>

<script type="text/javascript">
	var id_baixa = '<?php echo $baixa->id;?>';
	var id_paciente = '<?php echo $pagamento->id_paciente;?>';
	$(function(){
		$('.js-pagar-dataPagamento').inputmask('99/99/9999').datetimepicker({
																		timepicker:false,
																		format:'d/m/Y',
																		scrollMonth:false,
																		scrollTime:false,
																		scrollInput:false,
																	});


		$('.js-btn-pagar').click(function(){

			let dataPagamento = $('.js-pagar-dataPagamento').val();
			

			if(dataPagamento.length==0) {
				swal({title: "Erro!", text: "Defina a data de pagamento",  html:true,type:"error", confirmButtonColor: "#424242"});
			} else {

				let data = `ajax=persistirBaixa&dataPagamento=${dataPagamento}&id_baixa=${id_baixa}`;

				$.ajax({
					type:"POST",
					data:data,
					url:'box/<?php echo basename($_SERVER['PHP_SELF']);?>',
					success:function(rtn) {
						if(rtn.success) {
							document.location.href=`pg_contatos_pacientes_financeiro.php?id_paciente=${id_paciente}`
						} else if(rtn.error) {
							swal({title: "Erro!", text: rtn.error,  html:true,type:"error", confirmButtonColor: "#424242"});
						} else {
							swal({title: "Erro!", text: "Algum erro ocorreu durante a confirmação de pagamento. Tente novamente!",  html:true,type:"error", confirmButtonColor: "#424242"});
						}
					},
					error:function(){
						swal({title: "Erro!", text: "Algum erro ocorreu durante a confirmação de pagamento. Tente novamente.",  html:true,type:"error", confirmButtonColor: "#424242"});
					}
				})
			}

			return false;
		})
	})
</script>

<section class="modal" style="height:auto; width:950px;">

	<header class="modal-header">
		<div class="filtros">
			<div class="filter-group">
				<div class="filter-button">
					<a href="javascript:;" onclick="$.fancybox.close();"><i class="iconify" data-icon="bx-bx-left-arrow-alt"></i></a>
				</div>
			</div>
			<h1 class="filtros__titulo">Recebimento</h1>
		
			<div class="filtros-acoes">
				<button type="button" class="principal js-btn-pagar"><i class="iconify" data-icon="bx-bx-check"></i></button>
			</div>
		</div>
	</header>
	
	<article class="modal-conteudo">

		<form method="post" class="form js-form-agendamento">

			<fieldset>
				<legend>Confirmação do Pagamento</legend>
				<div class="colunas3">

					<dl>
						<dt>Data do Pagamento</dt>
						<dd><input type="text" value="<?php echo date('d/m/Y');?>" class="js-pagar-dataPagamento data datecalendar" /></dd>
					</dl>
				</div>
				<?php /*<div class="colunas3">
					<dl>
						<dt>Vencimento do Pagamento</dt>
						<dd><input type="text" value="<?php echo date('d/m/Y',strtotime($pagamento->data_vencimento));?>" disabled /></dd>
					</dl>
					<dl>
						<dt>Valor do Pagamento</dt>
						<dd><input type="text" value="<?php echo number_format($pagamento->valor,2,",",".");?>" disabled /></dd>
					</dl>
				</div>*/?>
				<div class="colunas3">
					<dl>
						<dt>Vencimento da Parcela</dt>
						<dd><input type="text" value="<?php echo date('d/m/Y',strtotime($baixa->data_vencimento));?>" disabled /></dd>
					</dl>
					<dl>
						<dt>Valor da Parcela</dt>
						<dd><input type="text" value="<?php echo number_format($baixa->valor,2,",",".");?>" disabled /></dd>
					</dl>
					<dl>
						<dt>Forma de Pagamento</dt>
						<dd><input type="text" value="<?php echo isset($_formasDePagamento[$baixa->id_formadepagamento])?utf8_encode($_formasDePagamento[$baixa->id_formadepagamento]->titulo):'-';?>" disabled /></dd>
					</dl>
				</div>
			</fieldset>

			<?php
			if(isset($_formasDePagamento[$baixa->id_formadepagamento]) and $_formasDePagamento[$baixa->id_formadepagamento]->tipo=="dinheiro") {
			?>
			<fieldset>
				<legend>Conta</legend>
				
				<dl>
					<dd>
						<select name="id_banco">
							<option value="">-</option>
							<?php
							foreach($_bancos as $x) {
								if($x->tipo=="dinheiro") {
									echo '<option value="'.$x->id.'">'.utf8_encode($x->titulo).'</option>';
								}
							}
							?>
						</select>
					</dd>
				</dl>
			</fieldset>
			<?php
			}
			?>

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
						<dt>Vencimento</dt>
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
									let semJuros = eval($(this).find('option:checked').attr('data-semjuros'));
									let parcelas = eval($(this).find('option:checked').attr('data-parcelas'));

									if($.isNumeric(parcelas)) {
										$('select.js-parcelas').append(`<option value="">-</option>`);
										for(var i=1;i<=parcelas;i++) {
											semjuros='';
											if($.isNumeric(semJuros) && semJuros>=i) semjuros=` - sem juros`;
											$('select.js-parcelas').append(`<option value="${i}">${i}x${semjuros}</option>`);
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

			

			
				
		</form>
	</article>

</section>