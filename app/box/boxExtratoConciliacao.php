<?php
	$dir="../";
	require_once("../lib/conf.php");
	require_once("../usuarios/checa.php");

	$cnt=$conta='';
	$sql = new Mysql();
	$jsc = new Js();
	$financeiro = new Financeiro(array('prefixo'=>$_p,'usr'=>$usr));

	if(isset($_GET['id']) and is_numeric($_GET['id'])) {
		$sql->consult($_p."financeiro_extrato","*,sum(valor+juros) as valor,date_format(data_extrato,'%d/%m/%Y') as dataf","where id='".$_GET['id']."' and lixo=0");
		if($sql->rows) {
			$cnt=mysqli_fetch_object($sql->mysqry);
			$sql->consult($_p."financeiro_bancosecontas","*","where id=$cnt->id_conta");
			if($sql->rows) {
				$conta=mysqli_fetch_object($sql->mysqry);
			}

			$fluxosConciliados=$financeiro->extratoConciliadoFluxo($cnt->id);
			if(empty($fluxosConciliados)) {
				$transferenciasConciliadas=$financeiro->extratoConciliadoTransferencia($cnt->id);
			}
			
			if(is_array($fluxosConciliados)) {
				$jsc->jAlert("Este movimento já está conciliado!","*","$.fancybox.close();");
				die();
			} else if(is_array($transferenciasConciliadas)) {
				$jsc->jAlert("Este movimento já está conciliado como transferência!","*","$.fancybox.close();");
				die();
			}
			
		}
	}

	if(empty($cnt)) {
		$jsc->jAlert("Movimento não encontrado!","erro","$.fancybox.close();");
		die();
	}


	if(empty($conta)) {
		$jsc->jAlert("Conta não encontrada!","erro","$.fancybox.close();");
		die();
	}

	$_formasDePagamentoQueNaoForDinheiro=array(0);
	$sql->consult($_p."parametros_formasdepagamento","*","where lixo=0 and tipo<>'dinheiro'");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_formasDePagamentoQueNaoForDinheiro[$x->id]=$x->id;
	}
	
	$_fluxosOrigens=array();
	$sql->consult($_p."financeiro_fluxo_origens","*","");
	while($x=mysqli_fetch_object($sql->mysqry)) $_fluxosOrigens[$x->id]=$x;


	$_fornecedores=array();
	$sql->consult($_p."parametros_fornecedores","*","where lixo=0 order by razao_social asc, nome asc");
	while($x=mysqli_fetch_object($sql->mysqry)) $_fornecedores[$x->id]=$x;

	$_colaboradores=array();
	$sql->consult($_p."colaboradores","id,nome","");
	while($x=mysqli_fetch_object($sql->mysqry)) $_colaboradores[$x->id]=$x;

?>
<script>
	var valorMov=0;
	var total=0;
	var valor = '<?php echo $cnt->valor;?>';
	
	function ajustaValor() {

		valorMov=0;
		var conciliacao=$('input[name=conciliacao]:checked').val();
		
		if(conciliacao=="unico") {
			if( $('.js-fluxo-unico:checked').length>0) {
				var valorAux = $('.js-fluxo-unico:checked').attr('data-valor');
				valorMov+=eval(valorAux);
			}
		} else { 
			$('.js-fluxo:checked').each(function(a){
				var valorAux=$(this).attr('data-valor');				
				valorMov+=eval(valorAux);
			});
		}

		valorMov=eval(valorMov);
		
		$('input.js-totalSelecionado').val(number_format(valorMov,2,",","."));
		var juros = ($('input[name=juros]').val());
		var multa = ($('input[name=multa]').val());
		var desconto = ($('input[name=desconto]').val());
	
		if(juros.length>0) juros=juros.split('.').join("").replace(',','.'); 
		else juros=0;

		if(multa.length>0) multa=multa.split('.').join("").replace(',','.'); 
		else multa=0;

		if(desconto.length>0) desconto=desconto.split('.').join("").replace(',','.'); 
		else desconto=0;
		
		total=0;
		
		total+=eval(valor);
		total+=eval(juros);
		total+=eval(multa);
		total+=eval(desconto);
		
		$('input.js-total').val(number_format(total,2,",","."));

		var diferenca=total-valorMov;
		console.log(total+' '+valorMov);
		$('#box-ajuste .js-aviso-diferenca').html(number_format(diferenca,2,",","."));
		$('#box-ajuste .js-aviso-valor').html(number_format(total,2,",","."));
		
		if(valorMov==total) {
			$('input.js-total').css('border','solid 1px green');
			$('#box-ajuste .js-aviso').hide();
		} else { 
			$('input.js-total').css('border','solid 1px red');
			$('#box-ajuste .js-aviso').show();
		}
	}

	$(function(){
		ajustaValor();
		$('input.js-ajustes').keyup(function(){
			
			var desconto = eval($('input[name=desconto]').val().replace(/[^0-9,-]/g, "").replace(',','.'));
			var multa = eval($('input[name=multa]').val().replace(/[^0-9,-]/g, "").replace(',','.'));
			var juros = eval($('input[name=juros]').val().replace(/[^0-9,-]/g, "").replace(',','.'));
			
			if(valor<0) {
				if(desconto>0) $(this).val(number_format((desconto*-1),2,",","."));
				if(multa<0) $(this).val(number_format((multa*-1),2,",","."));
				if(juros<0) $(this).val(number_format((juros*-1),2,",","."));
			} else {
				if(desconto<0) $(this).val(number_format((desconto*-1),2,",","."));
				if(multa>0) $(this).val(number_format((multa*-1),2,",","."));
				if(juros>0) $(this).val(number_format((juros*-1),2,",","."));
				console.log(multa);
			}
			
			
		});
		
		$('input.money').maskMoney({symbol:'', allowNegative:true, allowZero:true, showSymbol:true, thousands:'.', decimal:',', symbolStay: true});

		$('input[name=conciliacao]').click(function(){ 
			var val = $(this).val();
			if(val=="unico") {
				$('#field-unico').show();
				$('#field-multiplo').hide();
				$('input.js-fluxo').prop('checked',false);
			} else {
				$('input.js-fluxo-unico').prop('checked',false);
				$('#field-unico').hide();
				$('#field-multiplo').show();
			}
			ajustaValor();
		});
		
		$('.js-fluxo, .js-fluxo-unico').click(function(){ajustaValor();});
		
		$('input[name=juros],input[name=multa],input[name=desconto]').keyup(function(){ajustaValor();})
		
		$('.js-confirmar').click(function(){
			

			var conciliacao=$('input[name=conciliacao]:checked').val();

			var validacao=false;
			if(conciliacao=="unico") {
				if($('input.js-fluxo-unico').is(':checked')) { 
					validacao=true;
				} else {
					swal({title: "Erro!", text: "Para conciliação, selecione o fluxo!", type:"error", confirmButtonColor: "#424242"});
				}
			} else {
				if($('input.js-fluxo').is(':checked')) { 
					validacao=true;
				} else {
					swal({title: "Erro!", text: "Para conciliação, selecione o fluxo!", type:"error", confirmButtonColor: "#424242"});
				}
			}

			if(validacao===true) {
				//alert(total+' '+valorMov);
				total=(eval(total)).toFixed(2);
				valorMov=(eval(valorMov)).toFixed(2);
				if((total)!=(valorMov)) {
					swal({title: "Erro!", text: "Faça os ajustes necessários para conciliar!", type:"error", confirmButtonColor: "#424242"});
				} else {
					$('form').submit();
				}
			}

		});
		
		
		
	})
</script>

<section class="modal" style="height:auto; width:950px;">

	<header class="modal-header">
		<div class="filtros">
	
			<h1 class="filtros__titulo"></h1>
			<div class="filtros-acoes filter-button">
				<a href="javascript:;" class="azul js-confirmar"><i class="iconify" data-icon="bx-bx-check"></i><span>Conciliar</span></a>
			</div>
			
		</div>
	</header>


	<article class="modal-conteudo">
		<form method="post">
			<input type="hidden" name="conciliar" value="<?php echo $cnt->id;?>" />
			
			<fieldset>
				<legend>Conciliação</legend>

				<div class="colunas4">
					<dl>
						<dt>
							<label><input type="radio" name="conciliacao" class="js-conciliacao" value="unico" checked /> única</label>

							<label><input type="radio" name="conciliacao" class="js-conciliacao" value="multiplo" /> múltipla</label>
						</dt>
					</dl>

					<?php
					$dataInicio=date('Y-m-d',strtotime("-190 day",strtotime($cnt->data_extrato)));
					$dataFim=date('Y-m-d',strtotime("+15 day",strtotime($cnt->data_extrato)));
				
					$where="WHERE data_vencimento>='".$dataInicio."' and data_vencimento<='".$dataFim."' and pagamento=1";
				
					if($cnt->valor<0) {
						$v10=((2*$cnt->valor)/100);
						$valorInicio=($cnt->valor-$v10);
						$valorFim=($cnt->valor+$v10); 
						if($valorInicio>0) $valorInicio*=-1;
						if($valorFim>0) $valorFim*=-1;
						
						$where.=" and valor<='".($valorInicio)."' and valor>='".($valorFim)."'";
					} else {
						
						
						$v10=((2*$cnt->valor)/100);
						$valorInicio=($cnt->valor-$v10);
						$valorFim=($cnt->valor+$v10);
						
						$where.=" and valor>='".$valorInicio."' and valor<='".$valorFim."'";
					}
					$where.=" and lixo=0";
					$where.=" and id_formapagamento IN (".implode(",",$_formasDePagamentoQueNaoForDinheiro).")";

					$sql->consult($_p."financeiro_fluxo","*,date_format(data_vencimento,'%d/%m/%Y') as dataf",$where." order by data_vencimento desc");

					echo $where."->".$sql->rows;
					$registros=$registrosID=array();
					while($x=mysqli_fetch_object($sql->mysqry)) {
						$registros[$x->id]=$x;
						$registrosID[]=$x->id;
					}
					if(count($registrosID)>0) {
						$sql->consult($_p."financeiro_conciliacoes","*","where id_fluxo IN (".implode(",",$registrosID).") and id_transferencia=0 and lixo=0");
						
						if($sql->rows) {
							while($x=mysqli_fetch_object($sql->mysqry)) { 
								if(isset($registros[$x->id_extrato])) unset($registros[$x->id_extrato]);
							}
						}
					}
					?>
					
					<dl class="dl3">
						<dt>
							<i class="iconify" data-icon="mdi-calendar-blank"></i> <big><?php echo invDate2($dataInicio);?></big> <span>até</span> <big><?php echo invDate2($dataFim);?></big>
						</dt>
						<dd>
							<i class="iconify" data-icon="mdi-currency-usd"></i> <?php echo ($valorInicio>0?'<big style="background:var(--verde)">':'<big style="background:var(--vermelho)">').'R$'.number_format($valorInicio,2,",",".").'</big> <span>até</span> '.($valorFim>0?'<big style="background:var(--verde)">':'<big style="background:var(--vermelho)">').'R$'.number_format($valorFim,2,",",".").'</big>';?>
						</dd>
					</dl>
				</div>
			</fieldset>

			<fieldset>
				<legend>Movimento Bancário</legend>
				
				<div class="colunas5">
					<dl>
						<dt>Conta</dt>
						<dd><?php echo utf8_encode($conta->titulo);?></dd>
					</dl>
					<dl class="dl2">
						<dt>Descrição</dt>
						<dd><?php echo empty($cnt->descricao)?"-":utf8_encode($cnt->descricao);?></dd>
					</dl>
					<dl>
						<dt>Data</dt>
						<dd><?php echo $cnt->dataf;;?></dd>
					</dl>
					<dl>
						<dt>Valor</dt>
						<dd style="font-size:1.5em; color:<?php echo $cnt->pagamento==0?"var(--verde)":"var(--vermelho)";?>"><?php echo number_format($cnt->valor,2,",",".");?></dd>
					</dl>
				</div>
			</fieldset>
			
			
			
			<fieldset  id="field-unico" class="modal-content" style="background:var(--cinza1);">
				<legend>Fluxos</legend>

				<script type="text/javascript">
					$(function(){
						$('.tablesorter').tablesorter();
					})
				</script>

				<div class="registros">
					<table class="tablesorter">
						<thead>
							<tr>
								<th style="width:10px;"></th>
								<th style="width:80px;">Vencimento</th>
								<th style="width:250px">Unidade</th>
								<th style="width:150px;">Origem</th>
								<th><?php echo $cnt->valor>0?"Pagante":"Credor";?></th>
								<th style="width:120px">Valor (R$)</th>
							</tr>
						</thead>
						<tbody>
						<?php
						if(count($registros)==0) echo "<tr><td colspan=6><center>Nenhum fluxo encontrado</center></td></tr>";
						foreach($registros as $x) {


							if(is_array($financeiro->fluxoConciliado($x->id))) continue;
							$_valor=number_format($x->valor,2,".","");
						?>
						<tr class="mov" style="cursor: pointer">
							<td><input type="radio" name="id_fluxo" value="<?php echo $x->id;?>" class="js-fluxo-unico" data-valor="<?php echo $_valor;?>" /></td>
							<td><?php echo date('d/m/Y',strtotime($x->data_vencimento));?></td>
							<td><?php echo utf8_encode($_unidades[$x->id_unidade]->titulo);?></td>
							<td>
								<?php 
								if($x->id_origem>0) {
									echo isset($_fluxosOrigens[$x->id_origem])?utf8_encode($_fluxosOrigens[$x->id_origem]->titulo):"-";
								} else {
									echo "-";
								}
								?>		
							</td>
							<td>
								<?php 
								if($x->tipo=="fornecedor") {
									echo isset($_fornecedores[$x->id_fornecedor])?utf8_encode($_fornecedores[$x->id_fornecedor]->tipo_pessoa=="pf"?$_fornecedores[$x->id_fornecedor]->nome:$_fornecedores[$x->id_fornecedor]->razao_social):$x->id_fornecedor;
								} else if($x->tipo=="colaborador" or $x->tipo=="b2b_socio") {
									echo isset($_colaboradores[$x->id_colaborador])?utf8_encode($_colaboradores[$x->id_colaborador]->nome):'-';
								} else if($x->tipo=="b2b_unidade" or $x->tipo=="unidade") {
									echo isset($_unidades[$x->id_unidade_pagcred])?utf8_encode($_unidades[$x->id_unidade_pagcred]->titulo):'-';
								} else if($x->tipo=="caixa_loja") { 
									echo 'CAIXA LOJA';
								} else if($x->tipo=="caixa_delivery") { 
									echo 'CAIXA DELIVERY';
								} else if($x->tipo=="outros") {
									echo "OUTROS";
								}
								?>
							</td>
							<td style="color:<?php echo $_valor>=0?"green":"red";?>"><?php echo number_format($_valor,2,",",".");?></td>
						</tr>
						<?php	
						}
						?>
						</tbody>
					</table>
				</div>
			</fieldset>
			
			<fieldset id="field-multiplo" class="modal-content" style="display:none; background:var(--cinza1);">
				<lenged>Conta <span style="font-weight:normal;">(<?php echo invDate2($dataInicio);?> até <?php echo invDate2($dataFim);?>)</span></lenged>
				
				<?php
				$dataInicio=date('Y-m-d',strtotime("-120 day",strtotime($cnt->data_extrato)));
				$dataFim=date('Y-m-d',strtotime("+60 day",strtotime($cnt->data_extrato)));
			
				$where="WHERE data_vencimento>='".$dataInicio."' and data_vencimento<='".$dataFim."'";
			
				if($cnt->valor<0) {
					$where.=" and valor<0";
				} else {
					$where.=" and valor>0";
				}
				
				$where.=" and lixo=0";
				$where.=" and id_formapagamento IN (".implode(",",$_formasDePagamentoQueNaoForDinheiro).") and pagamento=1";
				//echo $where;
				$sql->consult($_p."financeiro_fluxo","*,date_format(data_efetivado,'%d/%m/%Y') as dataf",$where);
				$bancosID=array();
				$registros=$registrosID=array();
				while($x=mysqli_fetch_object($sql->mysqry)) {
					$registros[$x->id]=$x;
					$registrosID[]=$x->id;
				}
				if(count($registrosID)>0) {
					$sql->consult($_p."financeiro_conciliacoes","*","where (id_fluxo IN (".implode(",",$registrosID).")) and id_transferencia=0 and lixo=0"); 
					if($sql->rows) {
						while($x=mysqli_fetch_object($sql->mysqry)) {
							if(isset($registros[$x->id_fluxo])) unset($registros[$x->id_fluxo]);
							
						}
					}
				}
				?>
				<div class="colunas4">				
					<dl>
						<dt>Unidade</dt>
						<dd>
							<select name="id_unidade" class="js-sel-unidade">
								<option value="">-</option>
								<?php
								foreach($_optUnidades as $u) {
									echo '<option value="'.$u->id.'">'.utf8_encode($u->titulo).'</option>';
								}
								?>
							</select>
						</dd>
					</dl>
					<dl class="dl2">
						<dt>Busca</dt>
						<dd><input type="text" class="js-busca" /></dd>
					</dl>
					<dl>
						<dt>Total</dt>
						<dd><input type="text" class="js-totalSelecionado" disabled /></dd>
					</dl>
				</div>
				<script type="text/javascript">
					$(function(){
							$('.js-busca').keyup(function() {
							    var val = $.trim($(this).val()).replace(/ +/g, ' ').toLowerCase();

							     $('table tr.mov').show().filter(function() {
							        var text = $(this).text().replace(/\s+/g, ' ').toLowerCase();
							        return !~text.indexOf(val);
							    }).hide();
							});

							$('select.js-sel-unidade').change(function(){
								$('.js-extrato').hide();
								if($(this).val().length>0) {
									$(`.js-extrato-${$(this).val()}`).show();
								}
							}).trigger('change');
							
					})
				</script>

				<div class="registros">
					<table class="tablesorter">
						<thead>
							<tr>

								<th style="width:10px;"></th>
								<th style="width:80px;">Vencimento</th>
								<th style="width:250px">Unidade</th>
								<th style="width:150px;">Origem</th>
								<th><?php echo $cnt->valor>0?"Pagante":"Credor";?></th>
								<th style="width:120px">Valor (R$)</th>
							</tr>
						</thead>
						<tbody>
							<?php
							if(count($registros)==0) echo "<tr><td colspan=6><center>Nenhum movimento bancário encontrado</center></td></tr>";
							foreach($registros as $x) {
								$_valor=$x->valor;
							?>
							<tr class="js-extrato js-extrato-<?php echo $x->id_unidade;?>" style="cursor: pointer">
							<td><center><input type="checkbox" name="id_fluxo[]" value="<?php echo $x->id;?>" class="js-fluxo" data-valor="<?php echo $_valor;?>" /></center></td>
							<td><?php echo date('d/m/Y',strtotime($x->data_vencimento));?></td>
							<td><?php echo utf8_encode($_unidades[$x->id_unidade]->titulo);?></td>
							<td>
								<?php 
								if($x->id_origem>0) {
									echo isset($_fluxosOrigens[$x->id_origem])?utf8_encode($_fluxosOrigens[$x->id_origem]->titulo):"-";
								} else {
									echo "-";
								}
								?>		
							</td>
							<td>
								<?php 
								if($x->tipo=="fornecedor") {
									echo isset($_fornecedores[$x->id_fornecedor])?utf8_encode($_fornecedores[$x->id_fornecedor]->tipo_pessoa=="pf"?$_fornecedores[$x->id_fornecedor]->nome:$_fornecedores[$x->id_fornecedor]->razao_social):$x->id_fornecedor;
								} else if($x->tipo=="colaborador" or $x->tipo=="b2b_socio") {
									echo isset($_colaboradores[$x->id_colaborador])?utf8_encode($_colaboradores[$x->id_colaborador]->nome):'-';
								} else if($x->tipo=="b2b_unidade" or $x->tipo=="unidade") {
									echo isset($_unidades[$x->id_unidade_pagcred])?utf8_encode($_unidades[$x->id_unidade_pagcred]->titulo):'-';
								} else if($x->tipo=="caixa_loja") { 
									echo 'CAIXA LOJA';
								} else if($x->tipo=="caixa_delivery") { 
									echo 'CAIXA DELIVERY';
								} else if($x->tipo=="outros") {
									echo "OUTROS";
								}
								?>
							</td>
							<td style="color:<?php echo $_valor>=0?"green":"red";?>"><?php echo number_format($_valor,2,",",".");?></td>
						</tr>
						<?php /*
							<tr class="mov js-extrato js-extrato-<?php echo $x->id_unidade;?>" style="cursor: pointer">
								<td><center><input type="checkbox" name="id_fluxo[]" value="<?php echo $x->id;?>" class="js-fluxo" data-valor="<?php echo $_valor;?>" /></center></td>
								<td><?php echo $x->dataf;?></td>
								<td><?php echo utf8_encode($_unidades[$x->id_unidade]->titulo);?></td>
								<td><?php echo empty($x->descricao)?"-":utf8_encode($x->descricao);?></td>
								<td style="color:<?php echo $_valor>=0?"green":"red";?>"><?php echo number_format($_valor,2,",",".");?></td>
							</tr>*/?>
							<?php	
							}
							?>
						</tbody>
					</table>
				</div>
			</fieldset>
			
			<fieldset id="box-ajuste" class="modal-content">
				<legend>Ajuste</legend>

				<div class="colunas6">
					<dl>
						<dt>Valor</dt>
						<dd><input type="text" disabled value="<?php echo number_format($cnt->valor,2,",",".");?>" /></dd>
					</dl>
					<dl>
						<dt>Juros</dt>
						<dd><input type="text" name="juros" value="0,00" class="money js-ajustes" /></dd>
					</dl>
					<dl>
						<dt>Multa</dt>
						<dd><input type="text" name="multa" value="0,00" class="money js-ajustes" /></dd>
					</dl>
					<dl>
						<dt>Desconto</dt>
						<dd><input type="text" name="desconto" value="0,00" class="money js-ajustes" /></dd>
					</dl>
					<dl>
						<dt>Total</dt>
						<dd><input type="text" class="js-total" disabled /></dd>
					</dl>
					<dl>
						<dt>Diferença</dt>
						<dd><span class="js-aviso-diferenca" style="font-weight: bold"></span></dd>
					</dl>
				</div>
			</fieldset>
						
		</form>
	</article>
</section>