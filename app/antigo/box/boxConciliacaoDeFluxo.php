<?php
	$dir="../";
	require_once("../lib/conf.php");
	require_once("../usuarios/checa.php");

	$cnt='';
	$sql = new Mysql();
	$jsc = new Js();
	$financeiro = new Financeiro(array('prefixo'=>$_p,'usr'=>$usr));
	$_dinheiro=false;
	$_credito=false;

	if(isset($_GET['id']) and is_numeric($_GET['id'])) {
		$sql->consult($_p."financeiro_fluxo","*,sum(valor+juros) as valor,date_format(data_efetivado,'%d/%m/%Y') as data_efetivadof,date_format(data_vencimento,'%d/%m/%Y') as vencimento","where id='".$_GET['id']."' and lixo=0");
		if($sql->rows) {
			$cnt=mysqli_fetch_object($sql->mysqry);
			
			$sql->consult($_p."parametros_formasdepagamento","*","where id=$cnt->id_formapagamento");
			if($sql->rows) {
				$formaDePagamento=mysqli_fetch_object($sql->mysqry);
				if($formaDePagamento->tipo=="dinheiro") $_dinheiro=true;
				else if($formaDePagamento->tipo=="credito" or $formaDePagamento->tipo=="debito" or $formaDePagamento->tipo=="online") $_credito=true;
			}

			$extrato=$financeiro->fluxoConciliado($cnt->id);
			
			if(is_object($extrato)) {
				$jsc->jAlert("Fluxo já conciliado!","*","$.fancybox.close();");
				die();
			}
			
			if($cnt->valor<0) {
				$titulo="Conta a Pagar";
				$valorConta=$cnt->valor;
			} else {
				$titulo="Conta a Receber";
				$valorConta=$cnt->valor;
			}
		}
	}

	if(empty($cnt)) {
		$jsc->jAlert("Fluxo não encontrado!","erro","$.fancybox.close();");
		die();
	}

	$_contas=array();
	$_contasUnidades=array();
	$sql->consult($_p."financeiro_bancosecontas","*","where lixo=0 order by titulo asc");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_contas[$x->id]=$x;
		if($_dinheiro===true) {
			if($x->tipo=='dinheiro') $_contasUnidades[]=$x;
		} else {
			$_contasUnidades[]=$x;
		}
	}


	$tipoFornecedor='-';
	if($cnt->tipo=="fornecedor") {
		$tipoTitulo="Fornecedor";
		$sql->consult($_p."fornecedores","tipo_pessoa,razao_social,nome,cpf,cnpj","where id='".$cnt->id_fornecedor."'");
		if($sql->rows) {
			$x=mysqli_fetch_object($sql->mysqry);
			$tipoFornecedor=$x->tipo_pessoa=="pj"?utf8_encode($x->razao_social):utf8_encode($x->nome);
			$tipoTituloCNPJCPF=$x->tipo_pessoa=="pj"?"CNPJ":"CPF";
			$tipoCNPJCPF=$x->tipo_pessoa=="pj"?$x->cnpj:$x->cpf;
		}

	} else if($cnt->tipo=="colaborador") {
		$tipoTitulo="Colaborador";
		$sql->consult($_p."colaboradores","id,nome","where id='".$cnt->id_colaborador."'");
		if($sql->rows) {
			$x=mysqli_fetch_object($sql->mysqry);
			$tipoFornecedor=utf8_encode($x->nome);
		}
	} else if($cnt->tipo=="unidade" or $cnt->tipo=="b2b_unidade") {
		$tipoTitulo="Unidade";
		$sql->consult($_p."unidades","id,titulo","where id='".$cnt->id_unidade_pagcred."'");
		if($sql->rows) {
			$x=mysqli_fetch_object($sql->mysqry);
			$tipoFornecedor=utf8_encode($x->titulo);
		}
		
	} else if($cnt->tipo=="b2b_socio") {
		$tipoTitulo="Sócio (B2B)";
		$sql->consult($_p."colaboradores","id,nome","where id='".$cnt->id_colaborador."'");
		if($sql->rows) {
			$x=mysqli_fetch_object($sql->mysqry);
			$tipoFornecedor=utf8_encode($x->nome);
		}
	} else if($cnt->tipo=="b2b_clientesb2b") {
		$tipoTitulo="Cliente B2B";
		$sql->consult($_p."clientesb2b","id,nome","where id='".$cnt->id_clienteb2b."'");
		if($sql->rows) {
			$x=mysqli_fetch_object($sql->mysqry);
			$tipoFornecedor=utf8_encode($x->nome);
		}
		
	} else if($cnt->tipo=="caixa_loja") {
		$tipoTitulo="Caixa Loja";
		$sql->consult($_p."vendas_caixas","id,data","where id='".$cnt->id_caixa."'");
		if($sql->rows) {
			$x=mysqli_fetch_object($sql->mysqry);
			$tipoFornecedor='Caixa Loja ('.date('d/m/Y',strtotime($x->data)).') #'.$x->id;
		}
		
	} else if($cnt->tipo=="caixa_delivery") {
		$tipoTitulo="Caixa Delivery";
		$sql->consult($_p."delivery_caixas","id,data","where id='".$cnt->id_caixadelivery."'");
		if($sql->rows) {
			$x=mysqli_fetch_object($sql->mysqry);
			$tipoFornecedor='Caixa Delivery ('.date('d/m/Y',strtotime($x->data)).') #'.$x->id;
		}
		
	} else {
		$tipoTitulo="Outros";
	}
?>

<script>
	var valorMov=0;
	var total=0;
	var valor = '<?php echo $cnt->valor;?>';
	
	function ajustaValor() {

		valorMov=0;
		var conciliacao=$('input[name=conciliacao]:checked').val();
		
		if(conciliacao=="unico") {
			if( $('.js-movimento-unico:checked').length>0) {
				var valorAux = $('.js-movimento-unico:checked').attr('data-valor');
				valorMov+=eval(valorAux);
			}
		} else { 
			$('.js-movimento:checked').each(function(a){
				var valorAux=$(this).attr('data-valor');				
				valorMov+=eval(valorAux);
			});
		}

		valorMov=number_format(valorMov,2,".","");

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

		$('#box-ajuste .js-aviso-diferenca').html(number_format(diferenca,2,",","."));
		$('#box-ajuste .js-aviso-valor').html(number_format(total,2,",","."));
		
		if(number_format(valorMov,2,".","")==number_format(total,2,".","")) {
			$('input.js-total').css('border','solid 1px green');
			$('#box-ajuste .js-aviso').hide();
		} else { 
			$('input.js-total').css('border','solid 1px red');
			$('#box-ajuste .js-aviso').show();
		}
	}

	$(function(){
		<?php
		if($_dinheiro===false) {
		?>
		ajustaValor();
		$('input.js-ajustes').keyup(function(){
			
			var desconto = eval($('input[name=desconto]').val().replace(/[^0-9,-]/g, "").replace(',','.'));
			var multa = eval($('input[name=multa]').val().replace(/[^0-9,-]/g, "").replace(',','.'));
			var juros = eval($('input[name=juros]').val().replace(/[^0-9,-]/g, "").replace(',','.'));
			
			if(valor>0) {
				if(desconto>0) $(this).val(number_format((desconto*-1),2,",","."));
				if(multa<0) $(this).val(number_format((multa*-1),2,",","."));
				if(juros<0) $(this).val(number_format((juros*-1),2,",","."));
			} else {
				if(desconto<0) $(this).val(number_format((desconto*-1),2,",","."));
				if(multa>0) $(this).val(number_format((multa*-1),2,",","."));
				if(juros>0) $(this).val(number_format((juros*-1),2,",","."));
				
			}
			
			
		});
		$('input.money').maskMoney({symbol:'', allowNegative:true, allowZero:true, showSymbol:true, thousands:'.', decimal:',', symbolStay: true});

		$('input[name=conciliacao]').click(function(){ 
			var val = $(this).val();
			if(val=="unico") {
				$('#field-unico').show();
				$('#field-multiplo').hide();
				$('input.js-movimento').prop('checked',false);
			} else {
				$('input.js-movimento-unico').prop('checked',false);
				$('#field-unico').hide();
				$('#field-multiplo').show();
			}
			ajustaValor();
		});
		
		$('.js-movimento, .js-movimento-unico').click(function(){ajustaValor();});
		
		$('input[name=juros],input[name=multa],input[name=desconto]').keyup(function(){ajustaValor();})
		
		$('.js-confirmar').click(function(){
			

			var conciliacao=$('input[name=conciliacao]:checked').val();

			var validacao=false;
			if(conciliacao=="unico") {
				if($('input.js-movimento-unico').is(':checked')) { 
					validacao=true;
				} else {
					swal({title: "Erro!", text: "Para conciliação, selecione o movimento bancário!", type:"error", confirmButtonColor: "#424242"});
				}
			} else {
				if($('input.js-movimento').is(':checked')) { 
					validacao=true;
				} else {
					swal({title: "Erro!", text: "Para conciliação, selecione o movimento bancário!", type:"error", confirmButtonColor: "#424242"});
				}
			}
			<?php
			if($_credito==true) {
			?>
			$('form').submit();
			<?php
			} else {
			?>
			if(validacao===true) {
				if(number_format(eval(total),2,".","")!=number_format(eval(valorMov),2,".","")) {
					swal({title: "Erro!", text: "Faça os ajustes necessários para conciliar!", type:"error", confirmButtonColor: "#424242"});
				} else {
					$('form').submit();
				}
			}
			<?php
			}
			?>

		});
		$('select.js-sel-banco').change(function(){
			$('.js-banco').hide();
			$('.mov').removeClass('js-search-active')
			if($(this).val().length>0) {
				$(`.js-banco-${$(this).val()}`).addClass('js-search-active').show();
			}
		});
		
		$('select.js-sel-banco option:selected').trigger('change');
		<?php
		}
		?>
		
		$('.js-confirmar-dinheiro').click(function(){
			if($('select[name=id_conta] option:selected').val().length==0) {
				swal({title: "Erro!", text: "Selecione a Conta onde será conciliada este fluxo!", type:"error", confirmButtonColor: "#424242"});
			} else {
				$('form').submit();
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
	<?php
	if($_dinheiro===true) {
	?>
	<form method="post">
		<input type="hidden" name="conciliar" value="<?php echo $cnt->id;?>">
		<header class="modal-header">
			<h1 class="modal-header__titulo">Conciliação</h1>
		</header>
		<div class="modal-content">
			<h1 class="modal-content__titulo"><?php echo $titulo;?></h1>
			<div class="colunas4">
			
				<?php /*
				<dl class="dl3">
					<dt>Descrição</dt>
					<dd><?php echo empty($cnt->descricao)?"-":utf8_encode($cnt->descricao);?></dd>
				</dl>
				*/ ?>
				<dl class="dl2">
					<dt><?php echo $cnt->valor<0?"Credor":"Pagador";?></dt>
					<dd><?php echo $tipoFornecedor;?></dd>
				</dl>
				<?php
				if(isset($tipoCNPJCPF)) {
				?>
				<dl>
					<dt><?php echo $tipoTituloCNPJCPF;?></dt>
					<dd><?php echo $tipoCNPJCPF;?></dd>
				</dl>
				<?php
				}
				?>
			</div>
			<div class="colunas4">
				<dl>
					<dt>Vencimento</dt>
					<dd><?php echo $cnt->vencimento;;?></dd>
				</dl>
				<dl>
					<dt><?php echo $cnt->valor<0?"Pago em":"Recebido em";?></dt>
					<dd><?php echo $cnt->data_efetivadof;?></dd>
				</dl>
				
				<dl>
					<dt><?php echo "Baixado por";?></dt>
					<dd>
						<?php 
						$sql->consult($_p."colaboradores","nome","where id=$cnt->pagamento_id_colaborador");
						if($sql->rows) {
							$x=mysqli_fetch_object($sql->mysqry);
							echo utf8_encode($x->nome);
						} else {
							echo "-";
						}
						?>
					</dd>
				</dl>
				
				<dl>
					<dt>Valor</dt>
					<dd style="font-size:1.275em; color:<?php echo $cnt->valor>0?"green":"red";?>"><?php echo number_format($cnt->valor,2,",",".");?></dd>
				</dl>
			</div>

			<div class="colunas4">
				<dl>
					<dt>Conta</dt>
					<dd>
						<select name="id_conta">
							<option value="">-</option>
							<?php
							foreach($_contasUnidades as $c) {
								echo '<option value="'.$c->id.'">'.utf8_encode($c->titulo).'</option>';
							}
							?>
						</select>
					</dd>
				</dl>
			</div>
			
			<a href="javascript://" class="botao botao-principal js-confirmar-dinheiro" style="text-decoration: none;"><i class="icon-ok"></i> Criar Movimentação e Conciliar</a>
				
		</div>
	</form>
	<?php
	} else {
	?>
	<form method="post">
		<input type="hidden" name="conciliar" value="<?php echo $cnt->id;?>" />

		<fieldset>
			<legend>Conciliação</legend>


			<div class="colunas4">
			<?php
			if($_credito===false) {
			?>
				<dl>	
					<dd>
						<label><input type="radio" name="conciliacao" class="js-conciliacao" value="unico" checked /> única</label>
						<label><input type="radio" name="conciliacao" class="js-conciliacao" value="multiplo" /> múltipla</label>
					</dd>
				</dl>
			<?php
			} else {
			?>
				<input type="hidden" name="conciliacao" class="js-conciliacao" value="multiplo" />
			<?php
			}
			?>

			<?php
			$dataInicio=date('Y-m-d',strtotime("-15 day",strtotime($cnt->data_efetivado)));
			$dataFim=date('Y-m-d',strtotime("+15 day",strtotime($cnt->data_efetivado)));
		
			$where="WHERE data_extrato>='".$dataInicio."' and data_extrato<='".$dataFim."'";
		
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
			//echo $where;
			$sql->consult($_p."financeiro_extrato","*,date_format(data_extrato,'%d/%m/%Y') as dataf",$where." order by data_extrato desc");
			$registros=$registrosID=array();
			while($x=mysqli_fetch_object($sql->mysqry)) {
				$registros[$x->id]=$x;
				$registrosID[]=$x->id;
			}
			if(count($registrosID)>0) {
				$sql->consult($_p."financeiro_conciliacoes","*","where (id_extrato IN (".implode(",",$registrosID).") or id_transferencia IN (".implode(",",$registrosID).")) and lixo=0");
				if($sql->rows) {
					while($x=mysqli_fetch_object($sql->mysqry)) {
						if(isset($registros[$x->id_extrato])) unset($registros[$x->id_extrato]);
						if(isset($registros[$x->id_transferencia])) unset($registros[$x->id_transferencia]);
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
		</fieldset>



		<fieldset>
			<legend><?php echo $titulo;?></legend>
			<div class="colunas7">
				
				
				<dl class="dl2">
					<dt><?php echo "Baixado por";?></dt>
					<dd>
						<?php 
						$sql->consult($_p."colaboradores","nome","where id=$cnt->pagamento_id_colaborador");
						if($sql->rows) {
							$x=mysqli_fetch_object($sql->mysqry);
							echo utf8_encode($x->nome);
						} else {
							echo "-";
						}
						?>
					</dd>
				</dl>
				<dl class="dl2">
					<dt><?php echo $cnt->valor<0?"Credor":"Pagador";?></dt>
					<dd>
						<?php echo mb_strimwidth($tipoFornecedor,0,35,"...");?><br />
						<?php
						if(isset($tipoCNPJCPF)) {
						echo $tipoCNPJCPF;
						} ?>
					</dd>
				</dl>
				<dl>
					<dt>Vencimento</dt>
					<dd><?php echo $cnt->vencimento;;?></dd>
				</dl>
				<dl>
					<dt><?php echo $cnt->valor<0?"Pago em":"Recebido em";?></dt>
					<dd><?php echo $cnt->data_efetivadof;?></dd>
				</dl>
				<dl>
					<dt>Valor</dt>
					<dd style="font-size:1.5em; color:<?php echo $cnt->valor>0?"var(--verde)":"var(--vermelho)";?>"><?php echo number_format($cnt->valor,2,",",".");?></dd>
				</dl>		
			</div>
		</fieldset>
		
		<fieldset id="field-unico" style="display:<?php echo $_credito==true?"none":"";?>; background:var(--cinza1);">
			<legend>Movimento Bancário</legend>
			<div class="registros">
				<table class="tablesroter">
					<tr>
						<th style="width:3%"></th>
						<th>Data</th>
						<th>Unidade</th>
						<th>Conta</th>
						<th>Transação</th>
						<th>Descrição</th>
						<th>Valor (R$)</th>
					</tr>
					<?php
					if(count($registros)==0) echo "<tr><td colspan=7><center>Nenhum movimento bancário encontrado</center></td></tr>";
					foreach($registros as $x) {
					?>
					<tr class="mov" style="cursor: pointer">
						<td><input type="radio" name="id_movimento" value="<?php echo $x->id;?>" class="js-movimento-unico" data-valor="<?php echo $x->valor;?>" /></td>
						<td><?php echo $x->dataf;?></td>
						<td><?php echo isset($_contas[$x->id_conta])?utf8_encode($_contas[$x->id_conta]->titulo):"-";?></td>
						<td><?php echo $x->tipo;?></td>
						<td><?php echo utf8_encode($x->descricao);?></td>
						<td style="color:<?php echo $x->valor>=0?"green":"red";?>"><?php echo number_format($x->valor,2,",",".");?></td>
					</tr>
					<?php	
					}
					?>
				</table>
			</div>
		</fieldset>
		
		<fieldset id="field-multiplo" style="display:<?php echo $_credito==false?"none":"";?>; background:var(--cinza1);" class="modal-content">
			
			<?php
				$dataInicio=date('Y-m-d',strtotime("-60 day",strtotime($cnt->data_efetivado)));
				$dataFim=date('Y-m-d',strtotime("+60 day",strtotime($cnt->data_efetivado)));
			
				$where="WHERE data_extrato>='".$dataInicio."' and data_extrato<='".$dataFim."'";
			
				if($cnt->valor<0) {
					$where.=" and valor<0";
				} else {
					$where.=" and valor>0";
				}
				
				$where.=" and lixo=0 order by data_extrato desc";
				$sql->consult($_p."financeiro_extrato","*,date_format(data_extrato,'%d/%m/%Y') as dataf",$where);
				$bancosID=array();
				$registros=$registrosID=array();
				while($x=mysqli_fetch_object($sql->mysqry)) {
					$registros[$x->id]=$x;
					if(!isset($bancosID[$x->id])) $bancosID[$x->id]=$x->id_conta;
					$registrosID[]=$x->id;
				}
				if(count($registrosID)>0) {
					$sql->consult($_p."financeiro_conciliacoes","*","where (id_extrato IN (".implode(",",$registrosID).") or id_transferencia IN (".implode(",",$registrosID).")) and lixo=0");
					if($sql->rows) {
						while($x=mysqli_fetch_object($sql->mysqry)) {
							if(isset($registros[$x->id_extrato])) {
								unset($registros[$x->id_extrato]);
								unset($bancosID[$x->id_extrato]);
							}
							if(isset($registros[$x->id_transferencia])) {
								unset($registros[$x->id_transferencia]);
								unset($bancosID[$x->id_transferencia]);
							} 
						}
					}
				}
			?>
			<legend>
				Movimento Bancário <span style="font-weight:normal">(<?php echo invDate2($dataInicio);?> até <?php echo invDate2($dataFim);?>)</span>
			</legend>
			<div class="colunas4">
				<dl>
					<dt>Conta</dt>
					<dd>
						<select name="id_conta" class="js-sel-banco">
							<option value="">-</option>
							<?php
							foreach($_contasUnidades as $c) {
								echo '<option value="'.$c->id.'">'.utf8_encode($c->titulo).'</option>';
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

						     $('table tr.js-search-active').show().filter(function() {
						        var text = $(this).text().replace(/\s+/g, ' ').toLowerCase();
						        return !~text.indexOf(val);
						    }).hide();
						});
				})
			</script>
			
			<div class="registros">				
				<table>
					<tr>
						<th style="width:10px;"></th>
						<th style="width:80px;">Data</th>
						<th style="width:150px">Unidade</th>
						<th>Conta</th>
						<th>Transação</th>
						<th>Descrição</th>
						<th>Valor (R$)</th>
					</tr>
					<?php
					if(count($registros)==0) echo "<tr><td colspan=7><center>Nenhum movimento bancário encontrado</center></td></tr>";
					foreach($registros as $x) {
						
					?>
					<tr class="mov js-banco js-banco-<?php echo $x->id_conta;?>" style="cursor: pointer">
						<td><center><input type="checkbox" name="id_movimento[]" value="<?php echo $x->id;?>" class="js-movimento" data-valor="<?php echo $x->valor;?>" /></center></td>
						<td><?php echo $x->dataf;?></td>
						<td><?php echo isset($_contas[$x->id_conta])?utf8_encode($_contas[$x->id_conta]->titulo):"-";?></td>
						<td><?php echo $x->tipo;?></td>
						<td><?php echo utf8_encode($x->descricao);?></td>
						<td style="color:<?php echo $x->valor>=0?"green":"red";?>"><?php echo number_format($x->valor,2,",",".");?></td>
					</tr>
					<?php	
					}
					?>
				</table>
			</div>
		</fieldset>

		<fieldset id="box-ajuste" style="display:<?php echo $_credito===false?"block":"none";?>">
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
					<dd>R$<span class="js-aviso-diferenca" style="font-weight: bold"></span></dd>
				</dl>				
			</div>			
		</fieldset>

		<?php /*<a href="javascript://" class="botao botao-principal js-confirmar" style="margin-top:1rem;"><i class="icon-ok"></i> Conciliar</a>*/?>

	</form>
	<?php
	}
		?>
	</article>
</section>