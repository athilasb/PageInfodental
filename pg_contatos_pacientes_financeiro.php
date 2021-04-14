<?php
	include "includes/header.php";
	include "includes/nav.php";
 

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

	$paciente='';
	if(isset($_GET['id_paciente']) and is_numeric($_GET['id_paciente'])) {
		$sql->consult($_p."pacientes","*","where id='".$_GET['id_paciente']."'");
		if($sql->rows) {
			$paciente=mysqli_fetch_object($sql->mysqry);
		}
	}
	
	if(empty($paciente)) {
		$jsc->jAlert("Paciente não encontrado!","erro","document.location.href='pg_contatos_pacientes.php'");
		die();
	}

	?>

	<section class="content">
		
		<?php
		require_once("includes/abaPaciente.php");

		if(isset($_GET['form'])) {

			$campos=explode(",","titulo");
			
			foreach($campos as $v) $values[$v]='';
			$cnt='';
			$tratamentoAprovado=false;
			if(isset($_POST['acao'])) {


				// persiste as informacoes do tratamento
				if($_POST['acao']=="salvar") {
					$vSQL=$adm->vSQL($campos,$_POST);
					$values=$adm->values;

					if($tratamentoAprovado===false) {
						$vSQL.="procedimentos='".addslashes(utf8_decode($_POST['procedimentos']))."',";
						$vSQL.="pagamentos='".addslashes(utf8_decode($_POST['pagamentos']))."',";
					}
					
					if(is_object($cnt)) {
						$vSQL=substr($vSQL,0,strlen($vSQL)-1);
						$vWHERE="where id='".$cnt->id."'";
						$sql->update($_table,$vSQL,$vWHERE);
						$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='update',vsql='".addslashes($vSQL)."',vwhere='".addslashes($vWHERE)."',tabela='".$_table."',id_reg='".$cnt->id."'");
						$id_tratamento=$cnt->id;

						if($tratamentoAprovado===false) {
							$sql->update($_table."_procedimentos","lixo=1","where id_tratamento=$id_tratamento and id_paciente=$paciente->id and id_unidade=$usrUnidade->id");
							$sql->update($_table."_pagamentos","lixo=1","where id_tratamento=$id_tratamento and id_paciente=$paciente->id and id_unidade=$usrUnidade->id");
						}
					} else {
						$vSQL.="data=now(),id_paciente=$paciente->id";
						//echo $vSQL;die();
						$sql->add($_table,$vSQL);
						$id_tratamento=$sql->ulid;
						$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='insert',vsql='".addslashes($vSQL)."',tabela='".$_table."',id_reg='".$id_tratamento."'");
					}
				} 

				if(empty($id_tratamento) and is_object($cnt)) $id_tratamento=$cnt->id;


				if(isset($id_tratamento) and is_numeric($id_tratamento)) {


					// Procedimentos
						if(isset($_POST['procedimentos'])  and !empty($_POST['procedimentos'])) {
							
							$procedimetosJSON=!empty($_POST['procedimentos'])?json_decode($_POST['procedimentos']):array();

							foreach($procedimetosJSON as $x) {


								$vSQLProcedimento="lixo=0,
													id_paciente=$paciente->id,
													id_tratamento=$id_tratamento,
													id_unidade=$usrUnidade->id,
													id_procedimento='".addslashes($x->id_procedimento)."',
													procedimento='".addslashes(utf8_decode($x->procedimento))."',
													id_plano='".addslashes($x->id_plano)."',
													plano='".addslashes(utf8_decode($x->plano))."',
													id_profissional='".addslashes($x->id_profissional)."',
													profissional='".addslashes(utf8_decode($x->profissional))."',
													situacao='".addslashes($x->situacao)."',
													valor='".addslashes($x->valor)."',
													quantitativo='".addslashes($x->quantitativo)."',
													quantidade='".addslashes($x->quantidade)."',
													id_opcao='".addslashes($x->id_opcao)."',
													obs='".addslashes(utf8_decode($x->obs))."',
													opcao='".addslashes(utf8_decode($x->opcao))."',";
								//echo $vSQLProcedimento."<BR>";
								$procedimento='';
								if(isset($x->id) and is_numeric($x->id)) {
									$sql->consult($_table."_procedimentos","*","where id_tratamento=$id_tratamento and id=$x->id");
									if($sql->rows) {
										$procedimento=mysqli_fetch_object($sql->mysqry);
									}
								}

								if(is_object($procedimento)) {
									$vSQLProcedimento.="data_alteracao=now(),id_usuario_alteracao=$usr->id";
									$vWHERE="WHERE id=$procedimento->id";
									$sql->update($_table."_procedimentos",$vSQLProcedimento,$vWHERE);
									$id_tratamento_procedimento=$procedimento->id;
									$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='update',vsql='".addslashes($vSQLProcedimento)."',vwhere='".addslashes($vWHERE)."',tabela='".$_table."_procedimentos',id_reg='".$id_tratamento_procedimento."'");

								} else {
									$vSQLProcedimento.="data=now(),id_usuario=$usr->id";
									$sql->add($_table."_procedimentos",$vSQLProcedimento);
									$id_tratamento_procedimento=$sql->ulid;
									$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='insert',vsql='".addslashes($vSQLProcedimento)."',tabela='".$_table."_procedimentos',id_reg='".$id_tratamento_procedimento."'");
								}
							}
						}

					// Pagamentos
						if(isset($_POST['pagamentos'])  and !empty($_POST['pagamentos'])) {
							$pagamentosJSON=json_decode($_POST['pagamentos']);
							if(is_array($pagamentosJSON)) {
								foreach($pagamentosJSON as $x) {
									

									$vSQLPagamento="lixo=0,
													id_paciente=$paciente->id,
													id_tratamento=$id_tratamento,
													id_unidade=$usrUnidade->id,
													id_formapagamento='".addslashes($x->id_formapagamento)."',
													data_vencimento='".addslashes(invDate($x->vencimento))."',
													valor='".addslashes(valor($x->valor))."',";

									$pagamento='';
									if(isset($x->id) and is_numeric($x->id)) {
										$sql->consult($_table."_pagamentos","*","where id_tratamento=$id_tratamento and id=$x->id");
										if($sql->rows) {
											$pagamento=mysqli_fetch_object($sql->mysqry);
										}
									}

									if(is_object($pagamento)) {
										$vSQLPagamento.="data_alteracao=now(),id_usuario_alteracao=$usr->id";
										$vWHERE="WHERE id=$pagamento->id";
										$sql->update($_table."_pagamentos",$vSQLPagamento,$vWHERE);
										$id_tratamento_pagamento=$sql->ulid;
										$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='update',vsql='".addslashes($vSQLPagamento)."',vwhere='".addslashes($vWHERE)."',tabela='".$_table."_pagamentos',id_reg='".$id_tratamento_pagamento."'");

									} else {
										$vSQLPagamento.="data=now(),id_usuario=$usr->id";
										$sql->add($_table."_pagamentos",$vSQLPagamento);
										$id_tratamento_pagamento=$sql->ulid;
										$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='insert',vsql='".addslashes($vSQLPagamento)."',tabela='".$_table."_pagamentos',id_reg='".$id_tratamento_pagamento."'");
									}
								} 
							}
						}
				}

				if($_POST['acao']=="aprovar") {
					if(is_object($cnt)) {


						if($cnt->status=="PENDENTE") {
							$sql->update($_table,"status='APROVADO',id_aprovado=$usr->id,data_aprovado=now()","where id=$cnt->id");
							$jsc->jAlert("Plano de Tratamento aprovado com sucesso!","sucesso","document.location.href='$_page?form=1&edita=$cnt->id&$url'");
							die();
						} else {
							$jsc->jAlert("Este tratamento não está mais aguardando aprovação","erro","");
						}
					} else {
						$jsc->jAlert("Tratamento não encontrado!","erro","document.location.href='$_page?$url'");
						die();
					}
				} else {
					$jsc->jAlert("Informações salvas com sucesso!","sucesso","document.location.href='".$_page."?form=1&edita=$id_tratamento&id_paciente=$paciente->id'");
					die();
				}
			}
		?>	
			<form method="post" class="form js-form"  autocomplete="off" enctype="multipart/form-data">
				<input type="hidden" name="acao" value="salvar" />
				
				<section class="grid" style="padding:2rem; height:calc(100vh - 210px);?>">
				
					<div class="box" style="display:flex; flex-direction:column;">
						
						<div class="filtros" style="flex:0;">
							<h1 class="filtros__titulo" style="width:500px; max-width:70%;">
								<input type="text" name="titulo" placeholder="Título do tratamento..." value="<?php echo $values['titulo'];?>" style="border:0; border-radius:0; border-bottom:1px solid var(--cinza2); " />
							</h1>

							<div class="filtros-acoes">
								<a href="<?php echo $_page."?".$url;?>"><i class="iconify" data-icon="bx-bx-left-arrow-alt"></i></a>
								<a href="javascript:;" data-padding="0" class="principal tooltip js-btn-salvar" title="Salvar"><i class="iconify" data-icon="bx:bx-check" data-inline="false"></i></a>

								<?php
								if(is_object($cnt)) {
									if($cnt->status=="PENDENTE") {
								?>
								<a href="javascript:;" data-padding="0" class="principal2 tooltip js-btn-aprovar" title="Aprovar"><i class="iconify" data-icon="bx-bx-check-double"></i> <p>Aprovar tratamento</p></a>
								<?php
									} 
									if($cnt->status=="APROVADO") {

								?>
								<a href="box/boxPacienteTratamentoCancelar.php?id_paciente=<?php echo $paciente->id;?>&id_tratamento=<?php echo $cnt->id;?>&id_unidade=<?php echo $usrUnidade->id;?>" data-fancybox data-type="ajax" data-padding="0" class="tooltip js-btn-reprovar sec" title="Cancelar"><i class="iconify" data-icon="bx-bx-x"></i>
								<?php
									}
								}
								?>
								</a>
							</div>
						</div>
						
						<div class="grid grid_auto" style="flex:1;">
							<fieldset style="grid-column:span 2; margin:0;">
								
								<legend>Procedimentos</legend>
								<?php
								if($tratamentoAprovado===false) {
								?>
								<div class="colunas5">
									<dl>
										<dt>Procedimento</dt>
										<dd>
											<select class="js-id_procedimento chosen">
												<option value=""></option>
												<?php
												foreach($_procedimentos as $p) {
													echo '<option value="'.$p->id.'" data-id_regiao="'.$p->id_regiao.'" data-regiao="'.(isset($_regioes[$p->id_regiao])?utf8_encode($_regioes[$p->id_regiao]->titulo):"-").'" data-quantitativo="'.($p->quantitativo==1?1:0).'">'.utf8_encode($p->titulo).'</option>';
												}
												?>
											</select>
										</dd>
									</dl>
									<dl style="display: none">
										<dt>Qtd.</dt>
										<dd><input type="number" class="js-inpt-quantidade" value="1" /></dd>
									</dl>
									<dl class="js-regiao-2 js-regiao dl2" style="display: none;">
										<dt>Arcada(s)</dt>
										<dd>
											<select class="js-regiao-2-select" multiple>
												<option value=""></option>
												<?php
												if(isset($_regioesOpcoes[2])) {
													foreach($_regioesOpcoes[2] as $o) {
														echo '<option value="'.$o->id.'" data-titulo="'.utf8_encode($o->titulo).'">'.utf8_encode($o->titulo).'</option>';
													}
												}
												?>
											</select>
										</dd>
									</dl>
									<dl class="js-regiao-3 js-regiao dl2" style="display: none">
										<dt>Quadrante(s)</dt>
										<dd>
											<select class="js-regiao-3-select" multiple>
												<option value=""></option>
												<?php
												if(isset($_regioesOpcoes[3])) {
													foreach($_regioesOpcoes[3] as $o) {
														echo '<option value="'.$o->id.'" data-titulo="'.utf8_encode($o->titulo).'">'.utf8_encode($o->titulo).'</option>';
													}
												}
												?>
											</select>
										</dd>
									</dl>
									<dl class="js-regiao-4 js-regiao dl2" style="display: none">
										<dt>Dentes(s)</dt>
										<dd>
											<select class="js-regiao-4-select" multiple>
												<option value=""></option>
												<?php
												if(isset($_regioesOpcoes[4])) {
													foreach($_regioesOpcoes[4] as $o) {
														echo '<option value="'.$o->id.'" data-titulo="'.utf8_encode($o->titulo).'">'.utf8_encode($o->titulo).'</option>';
													}
												}
												?>
											</select>
										</dd>
									</dl>
									<dl>
										<dt>Plano</dt>
										<dd>
											<select class="js-id_plano chosen">
											</select>
										</dd>
									</dl>
									<dl>
										<dd>
											<a href="javascript:;" class="registros__acao js-btn-add"><i class="iconify" data-icon="ic-baseline-add"></i></a>
										</dd>
									</dl>
								</div>
								<?php
								}
								?>

								<textarea name="procedimentos" class="js-json-procedimentos" style="display:none;"><?php echo $values['procedimentos'];?></textarea>
								
								<div class="registros" style="height:<?php echo $tratamentoAprovado==false?"calc(100vh - 570px)":"calc(100vh - 400px)";?>; overflow:auto;">

									<table class="js-table-procedimentos">
										<thead>
											<tr>
												<th>Procedimento</th>
												<th>Região Qtd</th>
												<th>Profissional</th>
												<th>Plano</th>
												<th>Valor</th>
												<th>Situação</th>
												<th></th>
												<th></th>
											</tr>
										</thead>
										<tbody class="js-procedimentos">
											
										</tbody>
									</table>

								</div>
							</fieldset>												
							
							<fieldset style="margin:0;">
								<legend>Financeiro</legend>
								
								<div class="colunas4">
									<dl>
										<dt>Tratamento</dt>
										<dd style="color:red"><span class="js-valorTotal">R$ 0,00</span></dd>
									</dl>
									<dl>
										<dt>Pagamentos</dt>
										<dd style="color:green"><span class="js-valorPagamento">R$ 0,00</span></dd>
									</dl>
									<dl>
										<dt>Saldo</dt>
										<dd ><span class="js-valorSaldo">R$ 0,00</span></dd>
									</dl>
									<?php
									if($tratamentoAprovado===false) {
									?>
									<dl>
										<dt></dt>
										<dd>
											<a href="javascript:;" class="registros__acao js-btn-addPagamento"><i class="iconify" data-icon="ic-baseline-add"></i></a>
										</dd>
									</dl>
									<?php
									}
									?>
								</div>
								
								<textarea name="pagamentos" class="js-json-pagamentos" style="display:none;"><?php echo $values['pagamentos'];?></textarea>
								<div class="registros" style="height:<?php echo $tratamentoAprovado==false?"calc(100vh - 560px)":"calc(100vh - 460px)";?>; overflow:auto;">
									<table>
										<thead>
											<tr>
												<th>Vencto</th>
												<th>Valor</th>
												<th>Forma Pagto</th>
												<th></th>
											</tr>
										</thead>
										<tbody class="js-pagamentos">
											
										</tbody>
									</table>									
								</div>
							</fieldset>
							
						</div>
					</div>
				</section>
			</form>
		<?php
		} else {
			$where="WHERE id_paciente=$paciente->id and lixo=0 order by data desc, id desc";
			$sql->consult($_table,"*",$where);

			$valor=array('aReceber'=>0,
						'valorRecebido'=>0,
						'valoresVencido'=>0,
						'valorTotal'=>0);

			$registros=array();
			$tratamentosIDs=array(-1);
			$pagamentosIDs=array(-1);
			$pagamentosUnidos=array(-1);
			while($x=mysqli_fetch_object($sql->mysqry)) {
				if($x->id_fusao==0) $registros[]=$x;
				$tratamentosIDs[]=$x->id_tratamento;
				$pagamentosIDs[$x->id]=$x->id;

				if($x->fusao==1) $pagamentosUnidos[]=$x->id;

				if($x->fusao==0) $valor['valorTotal']+=$x->valor;
				$atraso=(strtotime($x->data_vencimento)-strtotime(date('Y-m-d')))/(60*60*24);

				if($atraso<0) {
					$valor['valoresVencido']+=$x->valor;
				}

			}

			$_subpagamentos=array();
			$sql->consult($_table,"*","where id_fusao IN (".implode(",",$pagamentosUnidos).") and lixo=0");
			while($x=mysqli_fetch_object($sql->mysqry)) {
				$_subpagamentos[$x->id_fusao][]=$x;
			}

			$_baixas=array();
			$pagamentosComBaixas=array();
			$sql->consult($_table."_baixas","*","where id_pagamento IN (".implode(",",$pagamentosIDs).") and lixo=0");
			if($sql->rows) {
				while($x=mysqli_fetch_object($sql->mysqry)) {
					$_baixas[$x->id_pagamento][]=$x;
					$pagamentosComBaixas[$x->id_pagamento]=$x->id_pagamento;
				}
			}

			$sql->consult($_p."pacientes_tratamentos","*","where id IN (".implode(",",$tratamentosIDs).")");
			while($x=mysqli_fetch_object($sql->mysqry)) $_tratamentos[$x->id]=$x;

			foreach($registros as $x) {
				if(isset($_baixas[$x->id])) {

					
					$dataUltimoPagamento=date('d/m/Y',strtotime($_baixas[$x->id][count($_baixas[$x->id])-1]->data));

					foreach($_baixas[$x->id] as $v) {

						$valor['valorRecebido']+=$v->valor;
						//$saldoAPagar-=$v->valor;
						//$valorPago+=$v->valor;
						//$descontos+=$v->desconto;
						//$multas+=$v->multas;
					}
				}
			}

		?>
		<script type="text/javascript">
			var baixas = [];
			var id_pagamento = 0;

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

						if(id_operadora!==undefined && parcela!==undefined) {

							let taxa = 0;
							let cobrarTaxa = 0;
							if(_taxasCredito[id_operadora][id_bandeira][parcela]) taxa=_taxasCredito[id_operadora][id_bandeira][parcela];
							if(_taxasCreditoSemJuros[id_operadora][id_bandeira][parcela]) cobrarTaxa=eval(_taxasCreditoSemJuros[id_operadora][id_bandeira][parcela]);
							

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
					url:"box/boxPacientePagamentos.php",
					data:data,
					success:function(rtn) {
						if(rtn.success) {
							$('.js-table-baixas .js-tr').remove();
							total = 0;
							let desconto = 0;
							let despesas = 0;


							rtn.baixas.forEach(x=> {

								let pagamento = '';

								if(x.tipoBaixa=="PAGAMENTO") {
									if(x.formaDePagamento.length>0) {
										if(x.id_formadepagamento==2) {
											pagamento=`${x.formaDePagamento}<font color=#999><br />Parcela ${x.parcela} de ${x.parcelas}</font>`;
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
								else if(x.tipoBaixa=="DESPESA") despesas+=x.valor;
								else {

									total+=x.valor;
								}

								let btns = ``;

								if(x.pago==1) {

								} else {
									btns=`<a href="javascript:;" class="js-estorno button button__sec" data-id_baixa="${x.id_baixa}" style="color:#FFF;" title="Estorno"><span class="iconify" data-icon="typcn:arrow-back" data-inline="false"></span></a>`;

									if(x.tipoBaixa=="PAGAMENTO") {
										btns+=` <a href="javascript:;" class="js-pagar button button__sec" data-id_baixa="${x.id_baixa}" title="Pagar" style="color:#FFF;"><span class="iconify" data-icon="ic:round-attach-money" data-inline="false"></span></a>`;
									}
								}

								html = `<tr class="js-tr">
											<td>${x.data}</td>
											<td>${x.tipoBaixa}</td>
											<td>${pagamento}</td>
											<td>${number_format(x.valor,2,",",".")}</td>
											<td>
												${btns}
											</td>
										</tr>`;

								


								$('.js-tr .js-recibo, .js-tr .js-estorno').tooltipster({theme:"borderless"});
								$('.js-table-baixas').append(html);
							});




							$('.js-valorDesconto').val(number_format(desconto,2,",","."));
							$('.js-valorDespesa').val(number_format(despesas,2,",","."));
							$('.js-valorPago').val(number_format(total,2,",","."));
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
				let saldoPagar=(valorParcela-total);
				saldoPagar-=desconto;
				saldoPagar+=despesas;


				$('.js-saldoPagar').val(number_format(saldoPagar,2,",","."));

				let valorCorrigido = valorParcela;
				valorCorrigido-=desconto;
				valorCorrigido+=despesas;

				$('.js-valorCorrigido').val(number_format(valorCorrigido,2,",","."));

				if(saldoPagar<=0) {
					$('.js-fieldset-pagamentos').hide();
				} else {
					$('.js-fieldset-pagamentos').show();
				}
			}
			
			
		</script>

		<section class="grid grid_5">
			<div class="box">
				<dl>
					<dt>Valor À Receber</dt>
					<dd>
						<span style="font-weight:bold"><?php echo number_format($valor['aReceber'],2,",",".");?></span>
					</dd>
				</dl>
			</div>
			<div class="box">
				<dl>
					<dt>Valor Recebido</dt>
					<dd>
						<span style="font-weight:bold"><?php echo number_format($valor['valorRecebido'],2,",",".");?></span>
					</dd>
				</dl>
			</div>
			<div class="box">
				<dl>
					<dt>Valores Vencidos</dt>
					<dd>
						<span style="font-weight:bold"><?php echo number_format($valor['valoresVencido'],2,",",".");?></span>
					</dd>
				</dl>
			</div>
			<div class="box">
				<dl>
					<dt>Total</dt>
					<dd>
						<span style="font-weight:bold"><?php echo number_format($valor['valorTotal'],2,",",".");?></span>
					</dd>
				</dl>
			</div>

		</section>

		<script type="text/javascript">
			var pagamentos = [];

			const popView = (obj) => {


				index=$(obj).index();


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

				$('#cal-popup .js-baixas tr').remove();

				if(pagamentos[index].baixas && pagamentos[index].baixas.length>0) {
					pagamentos[index].baixas.forEach(x=> {
						$('.js-baixas').append(`<tr>
													<td>${x.data}</td>
													<td>${x.formaobs}</td>
													<td>${number_format(x.valor,2,",",".")}</td>
												</tr>`);
						});
				} else {
					$('.js-baixas').append(`<tr><td colspan="3"><center>Nehnuma baixa</center></td></tr>`);
				}


				$('#cal-popup .js-subpagamentos tr').remove();
				if(pagamentos[index].subpagamentos && pagamentos[index].subpagamentos.length>0) {
					pagamentos[index].subpagamentos.forEach(x=> {
						$('.js-subpagamentos').append(`<tr>
													<td>${x.vencimento}</td>
													<td>${x.titulo}</td>
													<td>${number_format(x.valor,2,",",".")}</td>
												</tr>`);
					});

					$('.js-subpagamentos').append(`<tr>
														<td colspan="3"><center><a href="javascript:;" class="js-desfazerUniao" data-id_pagamento="<?php echo $x->id;?>"><span class="iconify" data-icon="eva:undo-fill" data-inline="false"></span> Desfazer união</a></center></td>
													</tr>`)

				} else {
					$('.js-subpagamentos').append(`<tr><td colspan="3"><center>Este pagamento não possui união</center></td></tr>`);
				}

				
				$('#cal-popup .js-titulo').html(pagamentos[index].titulo);
				$('#cal-popup .js-vencimento').html(`Vencto: ${pagamentos[index].vencimento}`);
				$('#cal-popup .js-desconto').val(number_format(pagamentos[index].valorDesconto,2,",","."))
				$('#cal-popup .js-parcela').val(number_format(pagamentos[index].valorParcela,2,",","."));
				$('#cal-popup .js-despesa').val(number_format(pagamentos[index].valorDespesa,2,",","."))
				$('#cal-popup .js-corrigido').val(number_format(pagamentos[index].valorCorrigido,2,",","."))
				$('#cal-popup .js-pago').val(number_format(pagamentos[index].valorPago,2,",","."))
				$('#cal-popup .js-btn-pagamento').attr('data-id_pagamento',pagamentos[index].id_parcela)

				$('#cal-popup .js-apagar').val(number_format(pagamentos[index].valorCorrigido-pagamentos[index].valorPago,2,",","."))
				//$('#cal-popup .js-btn-descontoAplicartEmTodos').prop('checked',popViewInfos[index].descontoAplicartEmTodos==1?true:false)
				
				$('#cal-popup .js-index').val(index);
			
				
			}
			$(function(){

				<?php
				if(isset($_GET['unirPagamentos'])) {
				?>
				$('.js-btn-unirPagamentos').click(function(){

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
							url:"box/boxPacientePagamentos.php",
							data:data,
							success:function(rtn) {
								if(rtn.success) {
									document.location.href='<?php echo "$_page?$url";?>';
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
				});
				<?php
				} 
				?>
				$('.js-tr-fusao').click(function(){
					let id_pagamento = $(this).parent().attr('data-id_pagamento');
					
					if($(`.js-fusao-${id_pagamento}:hidden`).length>0) {
						$(`.js-fusao-${id_pagamento}`).show();
					} else {
						$(`.js-fusao-${id_pagamento}`).hide();
					}
					
				})
				$('#cal-popup').on('click','.js-btn-pagamento',function(){
					let id_pagamento = $(this).attr('data-id_pagamento');
					$.fancybox.open({
						type:`ajax`,
						src:`box/boxPacientePagamentos.php?id_pagamento=${id_pagamento}`
					});
					return false;
				});


				$('.js-btn-fechar').click(function(){
					$('.cal-popup').hide();
				});

				$('#cal-popup').on('click','.js-desfazerUniao',function(){
					let id_pagamento = $(this).attr('data-id_pagamento');
					let data = `ajax=desfazerUniao&id_pagamento=${id_pagamento}`;
					$.ajax({
						type:"POST",
						url:"box/boxPacientePagamentos.php",
						data:data,
						success:function(rtn) {
							if(rtn.success) {
								document.location.href='<?php echo "$_page?$url";?>';
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
					
				})
			});
		</script>

		<section class="grid">
			<div class="box">

				<div class="filtros">
					<h1 class="filtros__titulo">Financeiro</h1>
					<div class="filtros-acoes">
						<?php
						if(isset($_GET['unirPagamentos'])) {
						?>
						<input type="text" class="js-dataVencimento data datecalendar" value="" />
						<a href="javascript:;" data-padding="0" class="principal tooltip js-btn-unirPagamentos" title="Unir"><i class="iconify" data-icon="bx:bx-check" data-inline="false"></i></a>

						<a href="<?php echo $_page."?".$url;?>"><i class="iconify" data-icon="bx-bx-left-arrow-alt"></i></a>
						<?php
						} else {
						?>
						<a href="<?php echo $_page."?unirPagamentos=1&$url";?>" data-padding="0" class="adicionar tooltip" title="Unir Pagamentos"><i class="iconify" data-icon="codicon:group-by-ref-type"></i></a>
						<?php
						}
						?>
						<a href="<?php echo $_page."?form=1&$url";?>" data-padding="0" class="adicionar tooltip" title="Adicionar"><i class="iconify" data-icon="bx-bx-plus"></i></a>
					</div>
				</div>

				<div class="registros2">
					<form class="js-form-pagamentos" onsubmit="return false">

						<div class="js-procedimentos">	
							<?php
							$pagamentosJSON=array();
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
										$saldoAPagar-=$v->valor;
										$valorPago+=$v->valor;
									}
								}

								$atraso=(strtotime($x->data_vencimento)-strtotime(date('Y-m-d')))/(60*60*24);

							
								$status='';

								$baixas=$subpagamentos=array();
								// verifica se possui baixas 
								if(isset($_baixas[$x->id])) {
									$baixaVencida=false;
									$baixaEmAberta=false;
									foreach($_baixas[$x->id] as $b) {
										if(strtotime($b->data_vencimento)<strtotime('Y-m-d')) {
											$baixaVencida=true;
											break;
										}
										if($b->pago==0) {
											$baixaEmAberta=true;
										}

										$formaobs='';
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
										$baixas[]=array('data'=>date('d/m',strtotime($b->data_vencimento)),
														'formaobs'=>$formaobs,
														'valor'=>(float)number_format($v->valor,2,",","."));
									}

									if($baixaVencida===true) {
										$status="<font color=red>INADIMPLENTE</font>";
										$cor="red";
									} else if($baixaEmAberta==false) {
										$status="<font color=green>ADIMPLENTE</font>";
										$cor="green";
									} else {
										if($saldoAPagar==0) {
											$cor="orange";
											$status="<font color=orange>PROMESSA DE PAGAMENTO</font>";
										} else {
											$status="<font color=blue>EM ABERTO</font>";
											$cor="blue";
										}
									}
									
								} 
								// nao possui nenhuma baixa
								else {
									if(strtotime($x->data_vencimento)<strtotime(date('Y-m-d'))) {
										$cor="red";
										$status="<font color=red>INADIMPLENTE</font>";
									}
									else {
										$cor="blue";
										$status="<font color=blue>EM ABERTO</font>";
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
											'fusao'=>$x->fusao);

								$pagamentosJSON[]=$item;
	
								

							?>
							<div class="card js-procedimento-item" data-id_pagamento="<?php echo $x->id;?>" style="border-left:solid 10px <?php echo $cor;?>;opacity: <?php echo $opacity;?>" onclick="popView(this);">
							
								<?php
								if(isset($_GET['unirPagamentos'])) {
								?>
								<div class="js-descricao" style="width:5%;">
									<input type="checkbox" name="pagamentos[]" class="js-checkbox-pagamentos" value="<?php echo $x->id;?>" />
								</div>
								<?php	
								}
								?>
								<div class="js-descricao" style="width:30%;">
									<h1 class="js-procedimento">
										<?php  
										if($x->fusao>0) {
										?>
										<a href="javascript:;" style="text-decoration: none;color:#000;"><strong><i class="iconify" data-icon="codicon:group-by-ref-type" data-height="18" data-inline="true"></i> União de Pagamentos (<?php echo isset($_subpagamentos[$x->id])?count($_subpagamentos[$x->id]):0;?>)</strong></a>
										<?php
										} else {
											echo isset($_tratamentos[$x->id_tratamento])?utf8_encode($_tratamentos[$x->id_tratamento]->titulo):'Avulso';
										}
										?>
									</h1>
									<h2 class="js-regiao"><?php echo date('d/m/Y',strtotime($x->data_vencimento));?></h2>
								</div>


								<div class="js-descricao" style="width:20%;">
									
								</div>

								<div class="js-descricao" style="width:20%">
									<span style="font-size:12px;color:#999;">Valor Total</span>
									<?php echo $x->valor==0?"-":number_format($valorCorrigido,2,",",".");?>
								</div>
								<div class="js-descricao" style="width:20%">
									<span style="font-size:12px;color:#999;">Valor Pago</span>
									<?php echo $x->valor==0?"-":number_format($valorPago,2,",",".");?>
								</div>
								<div class="js-descricao" style="width:20%">
									<span style="font-size:12px;color:#999;">À Pagar</span>
									<?php echo number_format($saldoAPagar>=$saldoAPagar?$saldoAPagar:0,2,",",".");?>
								</div>
							</div>
							<?php
										
								
							}
							?>
							</div>
						</div>
					</form>

				</div>

				<section id="cal-popup" class="cal-popup cal-popup_paciente cal-popup_top cal-popup_alt" style="left:703px; top:338px; margin-left:303px;display: none">
					<a href="javascript:;" class="cal-popup__fechar js-btn-fechar"><i class="iconify" data-icon="mdi-close"></i></a>
					<section class="paciente-info">
						<header class="paciente-info-header">
							<section class="paciente-info-header__inner1">
								<h1 class="js-titulo"></h1>
								<p style="color:var(--cinza4);"><span class="js-vencimento"></span></p>
							</section>
						</header>
						<input type="hidden" class="js-index" />

						<div class="abasPopover">
							<a href="javascript:;" onclick="$(this).parent().parent().find('a').removeClass('active');$(this).parent().parent().find('.js-grid').hide();$(this).parent().parent().find('.js-grid-info').show();$(this).addClass('active');" class="active">Informações</a>
							<a href="javascript:;" onclick="$(this).parent().parent().find('a').removeClass('active');$(this).parent().parent().find('.js-grid').hide();$(this).parent().parent().find('.js-grid-baixas').show();$(this).addClass('active');">Baixas</a>
							<a href="javascript:;" onclick="$(this).parent().parent().find('a').removeClass('active');$(this).parent().parent().find('.js-grid').hide();$(this).parent().parent().find('.js-grid-pagamentos').show();$(this).addClass('active');">Pagamentos</a>
						</div>

						<div class="paciente-info-grid js-grid js-grid-info" style="font-size: 12px;">		
							
							<dl>
								<dt>Valor da Parcela</dt>
								<dd><input type="text" class="js-parcela" value="" readonly /></dd>
							</dl>
							<dl>
								<dt>Desconto (-)</dt>
								<dd><input type="text" class="js-desconto" value="" readonly /></dd>

							</dl>
							<dl>
								<dt>Despesa (+)</dt>
								<dd><input type="text" class="js-despesa" value="" readonly /></dd>

							</dl>
							<dl>
								<dt>Valor Corrigido</dt>
								<dd><input type="text" class="js-corrigido" value="" readonly /></dd>

							</dl>
							<dl>
								<dt>Valor Pago</dt>
								<dd><input type="text" class="js-pago" value="" readonly /></dd>

							</dl>
							<dl>
								<dt>Saldo à pagar</dt>
								<dd><input type="text" class="js-apagar" value="" readonly /></dd>
							</dl>

							<?php /*<dl style="grid-column:span 2;">
								<dd><span class="iconify" data-icon="bx:bx-user-circle" data-inline="true"></span> Luciano Dexheimer Morais</dd>
							</dl>
							<dl style="grid-column:span 2;">
								<dd><span class="iconify" data-icon="bi:clock" data-inline="true"></span> 21/03/2021 18:30</dd>
							</dl>*/?>
						</div>

						<div class="paciente-info-grid js-grid js-grid-baixas registros" style="font-size: 12px;display:none;">
							

							<table style="grid-column:span 2;">
								<thead>
									<tr>
										<th>Pgto.</th>
										<th>Forma/Obs.</th>
										<th>Valor</th>
									</tr>
								</thead>
								<tbody class="js-baixas">
								</tbody>
							</table>

								
						</div>

						<div class="paciente-info-grid js-grid js-grid-pagamentos registros" style="font-size: 12px;display:none;">
							

							<table style="grid-column:span 2;">
								<thead>
									<tr>
										<th>Data</th>
										<th>Plano</th>
										<th>Valor</th>
									</tr>
								</thead>
								<tbody class="js-subpagamentos">

								</tbody>
							</table>

								
						</div>


						<div class="paciente-info-opcoes">
							<a href="javascript:;" target="_blank" class="js-btn-pagamento button button__sec">visualizar</a>
						</div>
					</section>
	    		</section>

				<?php /*<div class="registros">
					<form class="js-form-pagamentos" onsubmit="return false">

						<table class="">
							<thead>
								<tr>
									<?php
									if(isset($_GET['unirPagamentos'])) {
									?>
									<th style="width:20px;"></th>
									<?php	
									}
									?>
									<th>Vencimento</th>
									<th style="width:30%">Plano de Tratamento</th>
									<th>Status</th>
									<th>Inicial</th> 
									<th>Corrigido</th> 
									<th>Pago</th> 
									<th>À Pagar</th> 			
									<th style="width:50px;"></th>				
								</tr>
							</thead>
							<tbody>
							<?php
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

								if(isset($_baixas[$x->id])) {
									$dataUltimoPagamento=date('d/m/Y',strtotime($_baixas[$x->id][count($_baixas[$x->id])-1]->data));

									foreach($_baixas[$x->id] as $v) {
										$saldoAPagar-=$v->valor;
										$valorPago+=$v->valor;
									}
								}

								$atraso=(strtotime($x->data_vencimento)-strtotime(date('Y-m-d')))/(60*60*24);

								$status='';

								// verifica se possui baixas 
								if(isset($_baixas[$x->id])) {
									$baixaVencida=false;
									$baixaEmAberta=false;
									foreach($_baixas[$x->id] as $b) {
										if(strtotime($b->data_vencimento)<strtotime('Y-m-d')) {
											$baixaVencida=true;
											break;
										}
										if($b->pago==0) {
											$baixaEmAberta=true;
										}
									}

									if($baixaVencida===true) {
										$status="<font color=red>INADIMPLENTE</font>";
									} else if($baixaEmAberta==false) {
										$status="<font color=green>ADIMPLENTE</font>";
									} else {
										if($saldoAPagar==0) {
											$status="<font color=orange>PROMESSA DE PAGAMENTO</font>";
										} else {
											$status="<font color=blue>EM ABERTO</font>";
										}
									}
									
								} 
								// nao possui nenhuma baixa
								else {
									if(strtotime($x->data_vencimento)<strtotime(date('Y-m-d'))) $status="<font color=red>INADIMPLENTE</font>";
									else $status="<font color=blue>EM ABERTO</font>";
								}

								$valorDespesa=$valorDesconto=0;



							?>
							<tr data-id_pagamento="<?php echo $x->id;?>" style="opacity: <?php echo $opacity;?>">
								<?php
								if(isset($_GET['unirPagamentos'])) {
								?>
								<td class="js-tr-fusao">
									<input type="checkbox" name="pagamentos[]" class="js-checkbox-pagamentos" value="<?php echo $x->id;?>" />
								</td>
								<?php	
								}
								?>
								<td class="js-tr-fusao">
									<?php 
									echo date('d/m/Y',strtotime($x->data_vencimento));
									//echo "<BR>".$x->id;
									?>
								</td>
								<td class="js-tr-fusao">
									<?php 
									if($x->fusao>0) {
									?>
									<a href="javascript:;" style="text-decoration: none;color:#000;"><strong><i class="iconify" data-icon="codicon:group-by-ref-type" data-height="18" data-inline="true"></i> União de Pagamentos (<?php echo isset($_subpagamentos[$x->id])?count($_subpagamentos[$x->id]):0;?>)</strong></a>
									<?php
									} else {
										echo isset($_tratamentos[$x->id_tratamento])?utf8_encode($_tratamentos[$x->id_tratamento]->titulo):'Avulso';
									}
									?>
								</td>
								<td class="js-tr-fusao">
									<?php 


									echo $status;

									/*if($saldoAPagar<=0) echo '<font color=green><span class="iconify" data-icon="el:ok" data-inline="true" data-height="10"></span> pago</font>';
									else {
										echo ($atraso<0)?"<font color=red>atrasado há <b>".($atraso*-1)."</b> dia(s)</font>":"vence em <b>".$atraso."</b> dia(s)";
									}**

									?>
								</td>
								<td class="js-tr-fusao"><?php echo $x->valor==0?"-":number_format($x->valor,2,",",".");?></td>
								<td class="js-tr-fusao"><?php echo $valorCorrigido==0?"-":number_format($valorCorrigido,2,",",".");?></td>
								<td class="js-tr-fusao"><?php echo number_format($valorPago,2,",",".");?></td>
								<td class="js-tr-fusao"><?php echo number_format($saldoAPagar>=$saldoAPagar?$saldoAPagar:0,2,",",".");?></td>
								<td>
									<a href="javascript:;" class="js-tr-pagamento button" data-id_pagamento="<?php echo $x->id;?>" style="color:#FFF"><span class="iconify" data-icon="ic:round-attach-money" data-inline="false" data-height="20"></span></a>
								</td>
							</tr>
							<?php
								if($x->fusao==1) {
									if(isset($_subpagamentos[$x->id])) {
										foreach($_subpagamentos[$x->id] as $y) {
										
							?>
							<tr class="js-fusao-<?php echo $x->id;?>" style="background:#f0f0f0;display:none;font-size:12px;">
								<?php
								if(isset($_GET['unirPagamentos'])) {
								?>
								<td>
									<input type="checkbox" name="pagamentos[]" class="js-checkbox-pagamentos" value="<?php echo $x->id;?>" />
								</td>
								<?php	
								}
								?>
								<td>
									<?php 
									echo date('d/m/Y',strtotime($y->data_vencimento));
									?>
								</td>
								<td>
									<?php 
									if($y->fusao>0) {
									?>
									<a href="javascript:;" class="tooltip" title="Tratamentos" style="text-decoration: none;color:#000;"><strong><i class="iconify" data-icon="codicon:group-by-ref-type" data-height="18" data-inline="true"></i> Fusão</strong></a>
									<?php
									} else {
										echo isset($_tratamentos[$y->id_tratamento])?utf8_encode($_tratamentos[$y->id_tratamento]->titulo):'Avulso';
									}
									?>
								</td>
								<td>
									
								</td>
								<td><?php echo $y->valor==0?"-":number_format($y->valor,2,",",".");?></td>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
							</tr>
							<?php	
										}
							?>

							<tr class="js-fusao-<?php echo $x->id;?>" style="background:#f0f0f0;display:none;color:var(--vermelho)">
								<td colspan="9">
									<center>
										<a href="javascript:;" class="js-desfazerUniao" data-id_pagamento="<?php echo $x->id;?>"><span class="iconify" data-icon="eva:undo-fill" data-inline="false"></span> Desfazer união</a>
									</center>
								</td>
							</tr>
							<?php
									}	
								}
							}
							?>
							
							</tbody>
						</table>
					</form>

				</div>*/?>
				<script type="text/javascript">
					pagamentos = JSON.parse(`<?php echo json_encode($pagamentosJSON);?>`);
					console.log(pagamentos);
				</script>
				<?php
				}
				?>
			</div>
		</section>		
	</section>

<?php
	include "includes/footer.php";
?>