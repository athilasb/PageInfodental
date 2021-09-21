<?php
	$title="";
	include "includes/header.php";
	include "includes/nav.php";

	if($usr->tipo!="admin" and !in_array("clientes",$_usuariosPermissoes)) {
		$jsc->jAlert("Você não tem permissão para acessar esta área!","erro","document.location.href='dashboard.php'");
		die();
	}
	$values=$adm->get($_GET);

	$conta='';
	if(isset($_GET['id_conta']) and is_numeric($_GET['id_conta'])) {
		$sql->consult($_p."financeiro_bancosecontas","*","where id='".$_GET['id_conta']."' and lixo=0");

		if($sql->rows) {
			$conta=mysqli_fetch_object($sql->mysqry);
		}
	}
	if(empty($conta)) {
		$jsc->jAlert("Banco/Conta não selecionado!","erro","document.location.href='pg_financeiro_movimentacaobancaria_saldo.php'");
		die();
	}

	$financeiro = new Financeiro(array('prefixo'=>$_p,'usr'=>$usr));
	$financeiro->adm=$adm;
	$_tipoTransferencias=array('DEP'=>'CRÉDITO','CHECK'=>'CHEQUE','XFER'=>'TRANFERÊNCIA','CASH'=>'SAQUE','DEBIT'=>'DÉBITO','OTHER'=>'OUTROS');
	

?>
<section class="content">

	<?php
	require_once("includes/asideFinanceiro.php");
	?>


	<?php /*<header class="caminho">
		<h1 class="caminho__titulo">Contatos <i class="iconify" data-icon="bx-bx-chevron-right"></i> <strong>Pacientes</strong></h1>
		<a href="javascript:;" class="caminho__tutorial button button__sec"><i class="iconify" data-icon="ic-baseline-slow-motion-video"></i> ASSISTIR TUTORIAL</a>
	</header>*/?>

	<?php
	$_table=$_p."financeiro_extrato";
	$_page=basename($_SERVER['PHP_SELF']);

	$_status=array('avencer'=>'A Vencer',
					'vencido'=>'Vencido',
					'pagorecebido'=>'Pago/Recebido');

	$_receber=(isset($_GET['receber']) and $_GET['receber']==1)?1:0;


	if(isset($_GET['desconciliar']) and is_numeric($_GET['desconciliar'])) {// and ($conta->tipo!="contacorrente" or $usrCargo->admin==1)) {
			
		if($financeiro->extratoDesconciliar($_GET['desconciliar'])) {
			if(isset($values['edita']) and is_numeric($values['edita'])) $url.="&form=1&edtia=".$values['edita'];
			$jsc->go($_page."?".$url);
			die();
		} else {
			$jsc->jAlert($financeiro->erro,"erro","");
		}
	}

	if(isset($_GET['form'])) {

		$cnt='';
		if($conta->tipo=="contacorrente") {
			$campos=explode(",","data_extrato,descricao,tipo,obs,id_despesa,checknumber");
		} 
		else {
			$campos=explode(",","data_extrato,descricao,tipo,obs");
		}
		
		foreach($campos as $v) $values[$v]='';
		$values['data_extrato']=date('d/m/Y');
		$values['valor']='';

		$cnt='';
		$fluxos='';
		$transferencia='';

		
		if(isset($_GET['edita']) and is_numeric($_GET['edita'])) {
			$sql->consult($_table,"*","where id='".$_GET['edita']."' and lixo=0");
			if($sql->rows) {
				$cnt=mysqli_fetch_object($sql->mysqry);

				$fluxos=$financeiro->extratoConciliadoFluxo($cnt->id);

				if(!is_array($fluxos)) { 
					$transferencia=$financeiro->extratoConciliadoTransferencia($cnt->id);
				}

				$values=$adm->values($campos,$cnt);
				$values['valor']=number_format($cnt->valor,2,",",".");
			}
		}

		if(isset($_POST['acao'])) {
			if($_POST['acao']=="wlib") {
				$_POST['descricao']=strtoupperWLIB($_POST['descricao']);
				$vSQL=$adm->vSQL($campos,$_POST);
			//	echo $_POST['descricao'];die();
				if(empty($fluxos) and empty($transferencia) and isset($_POST['valor'])) {
					if(valor($_POST['valor'])>0) {
						$inptValor=valor($_POST['valor']);
						$vSQL.="valor='".($inptValor)."',";
						$_pagamento=0;
					} else {
						$inptValor=valor($_POST['valor']);
						$vSQL.="valor='".($inptValor)."',";
						$_pagamento=1;
					}
				}

				$values=$adm->values;
				if(is_object($cnt)) {
					$vSQL=substr($vSQL,0,strlen($vSQL)-1);
					//echo $vSQL;die();
					$vWHERE="where id='".$cnt->id."' and id_conta='".$conta->id."'";
					$sql->update($_table,$vSQL,$vWHERE);
					$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='update',vsql='".addslashes($vSQL)."',vwhere='".addslashes($vWHERE)."',tabela='".$_table."',id_reg='".$cnt->id."'");
					$id_reg=$cnt->id;
				} 
				else {
					$vSQL.="id_usuario='".$usr->id."',id_conta='".$conta->id."',id_unidade=$conta->id_unidade,ajuste=1";
					$sql->add($_table,$vSQL);
					$id_reg=$sql->ulid;
					$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='insert',vsql='".addslashes($vSQL)."',tabela='".$_table."',id_reg='".$sql->ulid."'");
				}

				$jsc->go("$_page?id_conta=$conta->id");
			}
		}

		if(is_object($cnt) and isset($_GET['deleta']) and is_numeric($_GET['deleta'])) {

		}
		$permissaoEdicao=true;

			if($conta->tipo=="contacorrente") {
				if(is_object($cnt) or is_array($conta) or is_array($transferencia)) {
					$permissaoEdicao=false;
				}
			} else {
				if((is_object($cnt) and $cnt->ajuste==0) or is_array($conta) or is_array($transferencia)) {
					$permissaoEdicao=false;
				}
			}

		//var_dump($values);
	?>

		<section class="grid">
			<div class="box">

				<form method="post" class="form formulario-validacao"  autocomplete="off" enctype="multipart/form-data">
					<input type="hidden" name="acao" value="wlib" />

					<div class="filter">
						<div class="filter-group">
							<div class="filter-button">
								<a href="<?php echo $_page."?".$url;?>"><i class="iconify" data-icon="bx-bx-left-arrow-alt"></i></a>
							</div>
						</div>
						<div class="filter-group filter-group_right">
							<div class="filter-button">
								<?php if(is_object($cnt)){?><a href="<?php echo $_page;?>?deleta=<?php echo $cnt->id."&".$url;?>" class="js-deletar"><i class="iconify" data-icon="bx-bx-trash"></i></a><?php }?>
								<a href="javascript:;" class="azul btn-submit"><i class="iconify" data-icon="bx-bx-check"></i><span>salvar</span></a>
							</div>
						</div>
					</div>

					<div class="grid grid_auto" style="flex:1;">
						<fieldset style="margin:0;">
							
							<legend>Movimentação Bancária</legend>

							
							<div class="colunas4">
								<dl>
									<dt>Conta</dt>
									<dd><input type="text" value="<?php echo utf8_encode($conta->titulo);?>" disabled /></dd>
								</dl>
								<dl>
									<dt>Transação</dt>
									<dd>
										<select name="tipo" class="<?php echo empty($cnt)?"obg":"";?>"<?php echo $permissaoEdicao===false?" disabled":"";?>>
											<option value="">-</option>
											<?php
											if((is_object($cnt))) {
												if($values['tipo']!="DEP" and $values['tipo']!="DEBIT") {
													if($values['valor']>=0) $values['tipo']="DEP";
													else $values['tipo']="DEBIT"; 
												}
												foreach($_tipoTransferencias as $k=>$v) {

													if($k!="DEP" and $k!="DEBIT") continue;
													echo '<option value="'.$k.'"'.((isset($values['tipo']) and $values['tipo']==$k)?' selected':'').'>'.$v.'</option>';
												}
											} else {
												foreach($_tipoTransferencias as $k=>$v) {
													if($k!="DEP" and $k!="DEBIT") continue;
													echo '<option value="'.$k.'"'.((isset($values['tipo']) and $values['tipo']==$k)?' selected':'').'>'.$v.'</option>';
												}
											}
											?>
										</select>
									</dd>
								</dl>

							</div>

							<div class="colunas4">
								<dl>
									<dt>Data</dt>
									<dd><input type="text" name="data_extrato" value="<?php echo $values['data_extrato'];?>"<?php echo $permissaoEdicao===false?" disabled":"";?> /></dd>
								</dl>
								<dl>
									<dt>Valor</dt>
									<dd><input type="text" name="valor" value="<?php echo $values['valor'];?>" class="obg money"<?php echo $permissaoEdicao===false?" disabled":"";?> /></dd>
								</dl>
								<?php
								if($conta->tipo=="contacorrente") {
								?>
								<dl>
									<dt>Nº Doc</dt>
									<dd><input type="text" name="checknumber" value="<?php echo $values['checknumber'];?>" class=""<?php echo $permissaoEdicao===false?" disabled":"";?> /></dd>
								</dl>
								<?php
								}
								?>

							</div>

							<dl>
								<dt>Descrição</dt>
								<dd><input type="text" name="descricao" value="<?php echo $values['descricao'];?>" /></dd>
							</dl>

							<dl>
								<dt>Observações</dt>
								<dd>
									<textarea name="obs"><?php echo $values['obs'];?></textarea>
								</dd>
							</dl>
							<?php
							if(empty($cnt)) {
								$_categorias=array();
								$sql->consult($_p."financeiro_categorias","*","where lixo=0 and id_categoria=0 order by titulo asc");
								while($x=mysqli_fetch_object($sql->mysqry)) {
									$_categorias[$x->id]=$x;
									$_categoriasSelect[$x->id]=array();
								}
								$sql->consult($_p."financeiro_categorias","*","where lixo=0 and id_categoria>0 order by titulo asc");
								while($x=mysqli_fetch_object($sql->mysqry)) {
									if(isset($_categoriasSelect[$x->id_categoria])) {
										$_categoriasSelect[$x->id_categoria][]=$x;
										$_categorias[$x->id]=$x;
									}
								}
							?>
							<script type="text/javascript">
								$(function(){
									setTimeout(function(){$('select[name=id_categoria]').removeClass('obg').parent().parent().hide();},500);
									$('input[name=criarFluxo]').click(function(){
										if($(this).prop('checked')===true) {
											$('select[name=id_categoria]').addClass('obg').parent().parent().show();
										} else {
											$('select[name=id_categoria]').removeClass('obg').parent().parent().hide();
										}
									})
									$('select[name=tipo]').change(function() {
										if($(this).val().length>0) {
											if($(this).val()=="DEBIT") {
												$('select[name=id_categoria]').find('option[data-receita=0]').prop('disabled',false).trigger('chosen:updated');
												$('select[name=id_categoria]').find('option[data-receita=1]').prop('disabled',true).trigger('chosen:updated');
											} else if($(this).val()=="CREDIT") {
												$('select[name=id_categoria]').find('option[data-receita=1]').prop('disabled',false).trigger('chosen:updated');
												$('select[name=id_categoria]').find('option[data-receita=0]').prop('disabled',true).trigger('chosen:updated');
											} else {
												$('select[name=id_categoria]').find('option').prop('disabled',true).trigger('chosen:updated');
											}
										}  else {
												$('select[name=id_categoria]').find('option').prop('disabled',true).trigger('chosen:updated');
											}
									}).trigger('change');
								});
							</script>
							<?php /*<div class="colunas4">
								<dl>
									<dt>&nbsp;</dt>
									<dd>
										<label><input type="checkbox" name="criarFluxo" value="1" /> Criar conta a pagar/receber</label>
									</dd>
								</dl>
								<dl class="dl3">
									<dt>Categoria</dt>
									<dd>
										<select name="id_categoria" class="chosen">
											<option value=""></option>
											<?php
											foreach($_categoriasSelect as $optGrupo=>$options) {
												echo '<optgroup label="'.$_categorias[$optGrupo]->titulo.'">';
												foreach($options as $c) {
													//var_dump($c);die();
													echo '<option value="'.$c->id.'" data-receita="'.$c->receita.'">'.utf8_encode($c->titulo).' ->'.$c->receita.'</option>';
												}
												echo '</optgroup>';
											}
											?>
										</select>
									</dd>
								</dl>
							</div>*/?>
							<?php
							}
							?>

							
						</fieldset>		

						<?php
						if(is_object($cnt)) {
							if(is_array($fluxos)) {
						?>
						<fieldset>
							<legend>Conciliada com Conta a <?php echo $cnt->valor>0?"Receber":"Pagar";?></legend>

							<div class="registros">
								<table class="tablesorter">
									<tr>
										<th style="width:60px;">Data</th>
										<th>Referência</th>
										<th>Descrição</th>
										<th style="width:120px;">Valor</th>
										<th style="width:50px;">Ação</th>
									</tr>
									<?php
									$total=0;
									if($cnt->valor>0) $funcao="receber";
									else $funcao="pagar";
									$aux=1;
									$descontosMultasJuros=array();
									foreach($fluxos as $x) {

										if($x->juros!=0) $descontosMultasJuros[]=array('data'=>$x->data_efetivado,
																						'titulo'=>'JUROS',
																						'valor'=>$x->juros);

										if($x->multa!=0) $descontosMultasJuros[]=array('data'=>$x->data_efetivado,
																						'titulo'=>'MULTA',
																						'valor'=>$x->multa);

										if($x->desconto!=0) $descontosMultasJuros[]=array('data'=>$x->data_efetivado,
																							'titulo'=>'DESCONTO',
																							'valor'=>$x->desconto);
										$total+=$x->valor;
									?>
									<tr>
										<td><?php echo $x->dataf;?></td>
										<td>
											<?php
											$fornecedores='';
											$fornecedores.=$financeiro->getPaganteCredor($x->id).", ";
											$fornecedores=substr($fornecedores,0,strlen($fornecedores)-2);
											echo $fornecedores;
											?>
										</td>
										<td><?php echo !empty($x->descricao)?utf8_encode($x->descricao):'-';?></td>
										<td style="text-align: right"><font color="<?php echo $x->valor>=0?"green":"red";?>"><?php echo number_format($x->valor,2,",",".");?></font></td>
										<td>
											<a href="pg_financeiro_fluxo.php?form=1&funcao=<?php echo $funcao."&edita=".$x->id;?>" class="button" target="_blank" style="color:#FFF"><span class="iconify" data-icon="bx:bx-search-alt"></span></a>

										</td>
									</tr>
									<?php
										foreach($descontosMultasJuros as $x) {
											$x=(object)$x;
										?>
										<tr>
											<td><?php echo date('d/m/Y',strtotime($cnt->data));?></td>
											<td>-</td>
											<td><?php echo $x->titulo;?> do FLUXO</td>
											<td>-</td>
											<td>-</td>
											<td style="text-align: right"><font color="<?php echo $x->valor>=0?"green":"red";?>"><?php echo number_format($x->valor,2,",",".");?></font></td>
											<td>-</td>
										</tr>
										<?php	
										}
										if($x->juros!=0) {
											$total-=$x->juros;
										?>
										<tr>
											<td><?php echo invDate2($cnt->data_extrato);?></td>
											<td>JUROS</td>
											<td>-</td>
											<td style="text-align: right"><font color="<?php echo $cnt->juros>=0?"green":"red";?>"><?php echo number_format($x->juros,2,",",".");?></font></td>
											<td>-</td>
										</tr>
										<?php
										}
										if($x->multa!=0) {
											$total-=$x->multa;
										?>
										<tr>
											<td><?php echo invDate2($cnt->data_extrato);?></td>
											<td>MULTA</td>
											<td>-</td>
											<td style="text-align: right"><font color="<?php echo $x->multa>=0?"green":"red";?>"><?php echo number_format($x->multa,2,",",".");?></font></td>
											<td>-</td>
										</tr>
										<?php
										}
										if($x->desconto!=0) {
											$total-=$x->desconto;
										?>
										<tr>
											<td><?php echo invDate2($cnt->data_extrato);?></td>
											<td>DESCONTO</td>
											<td>-</td>
											<td style="text-align: right"><font color="<?php echo $x->desconto>=0?"green":"red";?>"><?php echo number_format($x->desconto,2,",",".");?></font></td>
											<td>-</td>
										</tr>
										<?php
										}
									}
									if($cnt->juros!=0) {
										$total-=$cnt->juros;
									?>
									<tr>
										<td><?php echo invDate2($cnt->data_extrato);?></td>
										<td>JUROS</td>
											<td>-</td>
										<td style="text-align: right"><font color="<?php echo $cnt->juros>=0?"green":"red";?>"><?php echo number_format($cnt->juros,2,",",".");?></font></td>
										<td>-</td>
									</tr>
									<?php
									}
									if($cnt->multa!=0) {
										$total-=$cnt->multa;
									?>
									<tr>
										<td><?php echo invDate2($cnt->data_extrato);?></td>
										<td>MULTA</td>
											<td>-</td>
										<td style="text-align: right"><font color="<?php echo $cnt->multa>=0?"green":"red";?>"><?php echo number_format($cnt->multa,2,",",".");?></font></td>
										<td>-</td>
									</tr>
									<?php
									}
									if($cnt->desconto!=0) {
										$total-=$cnt->desconto;
									?>
									<tr>
										<td><?php echo invDate2($cnt->data_extrato);?></td>
										<td>DESCONTO</td>
										<td>-</td>
										<td style="text-align: right"><font color="<?php echo $cnt->desconto>=0?"green":"red";?>"><?php echo number_format($cnt->desconto,2,",",".");?></font></td>
										<td>-</td>
									</tr>
									<?php
									}
									?>

								</table>

							</div>
						</fieldset>
						<?php
							} else if(is_array($transferencia)) {
								$_bancosEContas=array();
								$sql->consult($_p."unidades_bancosecontas","*","where lixo=0");
								if($sql->rows) {
									while($x=mysqli_fetch_object($sql->mysqry)) {
										$_bancosEContas[$x->id]=$x;
									}
								}
						?>
						<fieldset>
							<legend>Movimento Transferido</legend>

							<div class="box-registros">
								<table class="tablesorter">
									<tr>
										<th>Data</th>
										<th>Banco</th>
										<th>Descrição</th>
										<th>Categoria</th>
										<th>Clienete/Fornecedor</th>
										<th>Valor</th>
										<th>Ação</th>
									</tr>
									<?php
									foreach($transferencia as $x) {
									?>
									<tr>
										<td><?php echo $x->dataf;?></td>
										<td><?php echo utf8_encode($_bancosEContas[$x->id_banco]->titulo);?></td>
										<td><?php echo utf8_encode($x->descricao);?></td>
										<td><font color="<?php echo $x->valor>=0?"green":"red";?>"><?php echo number_format($x->valor,2,",",".");?></font></td>
										<td><a href="movimentobancario.php?acao=form&id_banco=<?php echo $x->id_banco;?>&edita=<?php echo $x->id;?>" target="_blank" class="botao"><i class="icon-search"></i></a></td>
									</tr>
									<?php
									}
									?>

								</table>

							</div>
						</fieldset>
						<?php
							}
						}
						?>	
						
					</div>

				</form>
			</div>
		</section>
	<?php
	} else {

		if(isset($_POST['efetivar']) and is_numeric($_POST['efetivar'])) {
			$sql->consult($_p. "financeiro_fluxo","*","where id='".$_POST['efetivar']."'");
			if($sql->rows) {
				$fluxo=mysqli_fetch_object($sql->mysqry);

				if($fluxo->pagamento==1) {
					$jsc->jAlert("Esta conta já foi efetivada!","erro","");
				} else {
					if($fluxo->custo_recorrente==1) {
						$proximoVencimento=date("Y-m-d", strtotime("+1 month", strtotime($fluxo->data_vencimento)));
						$vSQL="data=now(),
								data_emissao=now(),
								id_origem=$fluxo->id_origem,
								id_registro=$fluxo->id_registro,
								id_formapagamento=$fluxo->id_formapagamento,
								credor_pagante='$fluxo->credor_pagante',
								id_paciente=$fluxo->id_paciente,
								id_fornecedor=$fluxo->id_fornecedor,
								id_colaborador=$fluxo->id_colaborador,
								valor='$fluxo->valor',
								descricao='".addslashes($fluxo->descricao)."',
								id_usuario=$fluxo->id_usuario,
								tipo='".addslashes($fluxo->tipo)."',
								custo_recorrente='".$fluxo->custo_recorrente."',
								custo_fixo='".$fluxo->custo_fixo."',
								id_recorrente='".$fluxo->id."',
								data_vencimento='".$proximoVencimento."'";
							//	echo $vSQL;die();
						
						$sql->consult($_table,"*","where id_recorrente='".$fluxo->id."' and lixo=0");
						if($sql->rows==0) {
							$sql->add($_table,$vSQL);
						} else {
							$sql->update($_table,$vSQL,"where id_recorrente='".$fluxo->id."' and lixo=0");
						}
					}
					
					$sql->update($_table,"pagamento=1,pagamento_id_colaborador=$usr->id,data_efetivado='".invDate($_POST['data_efetivado'])."'","where id='".$fluxo->id."'");
				}
			} else {
				$jsc->jAlert("Conta não encontrada!","erro", "");
			}
		} else if(isset($_POST['conciliar']) and is_numeric($_POST['conciliar'])) {

			// metodo concilia Fluxo ao movimento
			if($financeiro->movimentoConciliar($_POST)) {
				//echo "ok";
				$jsc->go($_page."?".$url);
			} else {
				$jsc->jAlert($financeiro->erro,"erro","");
			}
		}
 
		if(!isset($values['data_inicio']) or empty($values['data_inicio'])) {
			$values['data_inicioWH']=date('Y-m-01');
			$values['data_inicio']=date('01/m/Y');
		}

		if(!isset($values['data_fim']) or empty($values['data_fim'])) {
			$values['data_fimWH']=date('Y-m-t');
			$values['data_fim']=date('t/m/Y');
		}

	?>
		<section class="grid">
			<div class="box">
				<div class="filter">

					<div class="filter-group">
						<div class="filter-button">
							<a href="?form=1" class="verde"><i class="iconify" data-icon="bx-bx-plus"></i><span>Adicionar</span></a>
						</div>
					</div>


					
					<div class="filter-group filter-group_right">
						<form method="get" class="filter-form">
							<input type="hidden" name="csv" value="0" />
							<input type="hidden" name="id_conta" value="<?php echo $conta->id;?>" />
							<dl>
								<dd><input type="text" name="data_inicio" value="<?php echo isset($values['data_inicio'])?$values['data_inicio']:"";?>" class="noupper data datecalendar" placeholder="De" autocomplete="off" /></dd>
							</dl>
							<dl>
								<dd><input type="text" name="data_fim" value="<?php echo isset($values['data_fim'])?$values['data_fim']:"";?>" class="noupper data datecalendar" placeholder="Até" autocomplete="off" /></dd>
							</dl>
							
							<button type="submit"><i class="iconify" data-icon="bx-bx-search"></i></button>
						</form>
					</div>

				</div>

				<section id="cal-popup" class="cal-popup cal-popup_paciente cal-popup_top cal-popup_alt" style="left:703px; top:338px; margin-left:303px;display: none">
					<a href="javascript:;" class="cal-popup__fechar js-btn-fechar"><i class="iconify" data-icon="mdi-close"></i></a>
					<section class="paciente-info" style="margin-bottom: 20px;">
						<header class="paciente-info-header">
							<section class="">
								<h1 class="js-titulo"></h1>
								<p style="color:var(--cinza4);"><span class="js-vencimento"></span></p>
							</section>
						</header>
						<input type="hidden" class="js-index" />

						<div class="paciente-info-grid js-grid js-grid-descricao registros" style="font-size: 12px;display:none;"></div>

					</section>

					<center>
						<a href="javascript:;" class="js-btn-editar button">Editar</a>
						<?php /*<a href="javascript:;" data-fancybox data-type="ajax" class="js-btn-conciliar button">Conciliar</a>*/?>
						<a href="javascript:;" class="js-btn-desconciliar button">Desconciliar</a>
					</center>
	    		</section>

				<script type="text/javascript">
					const popView = (obj) => {

						$('.js-pop-informacoes').click();

						index=$(obj).index();
						id=$(`div.reg a:eq(${index})`).find('.js-id').val();
						conciliado=$(`div.reg a:eq(${index})`).find('.js-conciliado').val();


						$('#cal-popup .js-titulo').html($(`div.reg a:eq(${index})`).find('.js-titulo').html());
						$('#cal-popup .js-vencimento').html($(`div.reg a:eq(${index})`).find('.js-vencimento').html());


						$('#cal-popup')
								.removeClass('cal-popup_left')
								.removeClass('cal-popup_right')
								.removeClass('cal-popup_bottom')
								.removeClass('cal-popup_top');

						let clickTop=obj.getBoundingClientRect().top+window.scrollY;
					
						let clickLeft=Math.round(obj.getBoundingClientRect().left);
						let clickMargin=Math.round(obj.getBoundingClientRect().width/2);
						$(obj).prev('.cal-popup')
								.removeClass('cal-popup_left')
								.removeClass('cal-popup_right')
								.removeClass('cal-popup_bottom')
								.removeClass('cal-popup_top');

						let popClass='cal-popup_top';
						$('#cal-popup').addClass(popClass).toggle();
						$('#cal-popup').css({'top':clickTop,'left':clickLeft,'margin-left': clickMargin});
						$('#cal-popup').show();

						$('#cal-popup .js-btn-editar').attr('href',`?form=1&edita=${id}&<?php echo $url;?>`);
						$('#cal-popup .js-btn-conciliar').hide().attr('href',`javascript:;`);
						$('#cal-popup .js-btn-desconciliar').hide();

						if(conciliado==0) {
							$('#cal-popup .js-btn-conciliar').show().attr('href',`box/boxConciliacaoDeMovimentacao.php?id=${id}`);
						} else {

							$('#cal-popup .js-btn-desconciliar').show().attr('href',`?desconciliar=${id}&<?php echo $url;?>`);
						}
						
					}

					$(function(){
						$('.js-btn-fechar').click(function(){
							$('.cal-popup').hide();
						});
						
						$(document).mouseup(function(e)  {
						    var container = $("#cal-popup");
						    if (!container.is(e.target) && container.has(e.target).length === 0) $('#cal-popup').hide();
						    
						});
					})
				</script>

				<?php
				$where="where id_conta=$conta->id";

				if(isset($values['ajustes']) and $values['ajustes']==1) $where.=" and ajuste=1";
				if(!empty($values['data_inicio'])) $where.=" and data_extrato>='".$values['data_inicioWH']."'";
				if(!empty($values['data_fim'])) $where.=" and data_extrato<='".$values['data_fimWH']."'";
				$where.=" and lixo=0";

				$sql->consultPagMto2($_table,"*",200,$where." order by data_extrato desc","",15,"pagina",$_page."?id_conta=$conta->id&pagina=");
				$registros=$extratosID=array();
				while($x=mysqli_fetch_object($sql->mysqry)) {
					$registros[$x->id]=$x;
					$extratosID[$x->id]=$x->id;

				}

				$_fluxosConciliados=array();
				$conciliadosFluxosIds=array(-1);
				$extratoConciliado=array();
				$extratoTransferencia=array();
				if(count($extratosID)>0) { 
					
					$sql->consult($_p."financeiro_conciliacoes","*","where id_extrato in (".implode(",",$extratosID).") and id_extrato>0 and id_transferencia=0 and lixo=0");
					if($sql->rows) {
						while($x=mysqli_fetch_object($sql->mysqry)) {
							$extratoConciliado[$x->id_extrato][]=$x;
							unset($extratosID[$x->id_extrato]);
							$conciliadosFluxosIds[]=$x->id_fluxo;
						}
					} 
					if(count($extratosID)>0) {
						$sql->consult($_p."financeiro_conciliacoes","*","where (id_transferencia in (".implode(",",$extratosID).") || id_extrato in (".implode(",",$extratosID).")) and id_fluxo=0 and lixo=0");
						if($sql->rows) {
							while($x=mysqli_fetch_object($sql->mysqry)) {
								$extratoTransferencia[$x->id_transferencia][]=$x;
								$extratoTransferencia[$x->id_extrato][]=$x;
							}
						}
					}

					$sql->consult($_p."financeiro_fluxo","*","where id IN (".implode(",",$conciliadosFluxosIds).")");
					while($x=mysqli_fetch_object($sql->mysqry)) {
						$_fluxosConciliados[$x->id]=$x;
					}
				}
				

				?>
				<div class="reg">
					<?php
					$totalRegistros=0;
					foreach($registros as $x) {
						$cor='';
						$_conciliado=0;
						if(isset($extratoConciliado[$x->id])) {
							$cor="background:#C8FBC4";
							$_conciliado=1;
						} else if(isset($extratoTransferencia[$x->id])) {
							$cor="background:#E9E79C";
							$_conciliado=2;
						} 
						$totalRegistros+=$x->valor;

					?>
					<a href="javascript:;" class="reg-group" onclick="popView(this);">
						<input type="hidden" class="js-id" value="<?php echo $x->id;?>" />
						<input type="hidden" class="js-conciliado" value="<?php echo $_conciliado;?>" />
						<div class="reg-color" style="background: <?php echo $x->valor>0?"green":"red";?>;"></div>
						
						<div class="reg-data" style="flex:0 1 30%;">
							<h1 class="js-titulo"><?php echo utf8_encode($x->descricao);?></h1>
							<p class="js-vencimento"><?php echo date('d/m/Y',strtotime($x->data_extrato));?></p>
						</div>

						<div class="reg-data" style="flex:0 1 30%;">
							<?php
							if($_conciliado==0) {
							?>
							à conciliar
							<?php
							} else {
								if(isset($extratoConciliado[$x->id])) {
									echo "<font color=green>CONCILIADO (".count($extratoConciliado[$x->id]).")</font>";
								} else if(isset($extratoTransferencia[$x->id])) {
									echo "<font color=orange>TRANSFERÊNCIA (".count($extratoTransferencia[$x->id]).")</font>";
								} 
							}
							?>
						</div>

						<div class="reg-data" style="flex:0 1 120px;">
							<h1 style="color:<?php echo $x->valor>0?"green":"red";?>">R$ <?php echo number_format($x->valor,2,",",".");?></h1>
						</div>
						
					</a>
					<?php
					}

					if(isset($sql->myspaginacao) and !empty($sql->myspaginacao)) {
					?>	
					<div class="paginacao" style="margin-top: 30px;">
						<p class="paginacao__item"><span>Página</span><?php echo $sql->myspaginacao;?></p>
					</div>
					<?php
					}
					?>
				</div>
				
			</div>
		</section>
	<?php
	}
	?>

</section>

<?php
	include "includes/footer.php";
?>