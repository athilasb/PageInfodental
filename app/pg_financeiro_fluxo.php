<?php
	$title="";
	include "includes/header.php";
	include "includes/nav.php";

	if($usr->tipo!="admin" and !in_array("clientes",$_usuariosPermissoes)) {
		$jsc->jAlert("Você não tem permissão para acessar esta área!","erro","document.location.href='dashboard.php'");
		die();
	}
	$values=$adm->get($_GET);
	$financeiro = new Financeiro(array('prefixo'=>$_p,'usr'=>$usr));
	$financeiro->adm=$adm;


	$_fornecedores=array();
	$sql->consult($_p."parametros_fornecedores","id,IF(tipo_pessoa='PJ',razao_social,nome_fantasia) as titulo,IF(tipo_pessoa='PJ',cnpj,cpf) as cnpjcpf","where lixo=0 order by titulo asc");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_fornecedores[$x->id]=$x;
	}

	$_formasDePagamentos=array();
	$optionFormasDePagamento='';
	$sql->consult($_p."parametros_formasdepagamento","*","where lixo=0 order by titulo asc");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_formasDePagamentos[$x->id]=$x;
		$optionFormasDePagamento.='<option value="'.$x->id.'" data-tipo="'.$x->tipo.'">'.utf8_encode($x->titulo).'</option>';
	}

	$_pacientes=array();
	$sql->consult($_p."pacientes","id,nome","where lixo=0 order by nome asc");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_pacientes[$x->id]=$x;
	}
	
	$_colaboradores=array();
	$sql->consult($_p."colaboradores","*","order by nome asc");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_colaboradores[$x->id]=$x;
	}
	
	$_categoriasFinanceiro=array();
	$_categoriasFinanceiroCategorias=array();
	$_categoriasFinanceiroSubcategorias=array();
	$sql->consult($_p."financeiro_categorias","*","where receita=0 and lixo=0 order by titulo asc");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_categoriasFinanceiro[$x->id]=$x;
		if($x->id_categoria>0) {
			if(!isset($_categoriasFinanceiroSubcategorias[$x->id_categoria])) $_categoriasFinanceiroSubcategorias[$x->id_categoria]=array();
			$_categoriasFinanceiroSubcategorias[$x->id_categoria][]=$x;
		} else {
			$_categoriasFinanceiroCategorias[]=$x;
		}
	}

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
	$_table=$_p."financeiro_fluxo";
	$_page=basename($_SERVER['PHP_SELF']);

	$_status=array('avencer'=>'A Vencer',
					'vencido'=>'Vencido',
					'pagorecebido'=>'Pago/Recebido');

	$_receber=(isset($_GET['receber']) and $_GET['receber']==1)?1:0;

	
	if(isset($_GET['desconciliar']) and is_numeric($_GET['desconciliar'])) {

		if($financeiro->contaDesconciliar($_GET['desconciliar'])) {
			if(isset($values['edita']) and is_numeric($values['edita'])) $url.="&form=1&edtia=".$values['edita'];
			$jsc->go($_page."?".$url);
		} else {
			$jsc->jAlert($financeiro->erro,"erro","");
		}
	}

	if(isset($_GET['form'])) {

		$cnt=$extrato='';
		$campos=explode(",","data_vencimento,valor,descricao,id_categoria,credor_pagante,id_fornecedor,id_colaborador,id_paciente,id_formapagamento,data_emissao,tipo,custo_fixo,custo_recorrente");
		
		foreach($campos as $v) $values[$v]='';
		$values['data_emissao']=date('d/m/Y');
		$values['data_vencimento']=date('d/m/Y');
		$values['credor_pagante']="fornecedor";
		$values['tipo']="produto";

		if(isset($_GET['edita']) and is_numeric($_GET['edita'])) {
			$sql->consult($_table,"*,date_format(data,'%d/%m/%Y %H:%i') as dataf","where id='".$_GET['edita']."'");
			if($sql->rows) {
				$cnt=mysqli_fetch_object($sql->mysqry);
				
				$values=$adm->values($campos,$cnt);
				$values['data']=$cnt->dataf;
				
				// Se o fluxo estiver conciliada;
				$extrato=$financeiro->fluxoConciliado($cnt->id);

			} else {
				$jsc->jAlert("Informação não encontrada!","erro","document.location.href='".$_page."'");
				die();
			}
		};

		if(isset($_POST['acao']) and $_POST['acao']=="wlib") {
			$vSQL=$adm->vSQL($campos,$_POST);
			$values=$adm->values;


			if(empty($cnt)) {
				if(isset($_POST['pagamentos']) and !empty($_POST['pagamentos'])) {
					$pagamentosJSON=json_decode($_POST['pagamentos']);
					if(is_array($pagamentosJSON)) {
						$i=1;
						foreach($pagamentosJSON as $p) {
							if(!is_numeric($p->valor)) continue;

							if($_receber==1) {
								$p->valor=$p->valor<0?$p->valor*-1:$p->valor;
							} else {
								$p->valor=$p->valor>0?$p->valor*-1:$p->valor;
							}
							$vSQLPagamento=$vSQL."data_vencimento='".invDate($p->vencimento)."',
													valor='".$p->valor."',
													id_formapagamento='".addslashes(isset($p->id_formadepagamento)?$p->id_formadepagamento:0)."',
													identificador='".addslashes(isset($p->identificador)?$p->identificador:'')."',
													parcela='$i',
													qtdParcelas='".count($pagamentosJSON)."',
													id_usuario=$usr->id";
						
							$sql->consult($_p."financeiro_fluxo","*","where data_vencimento='".invDate($p->vencimento)."' and valor='".$p->valor."' and parcela='".$i."' and id_usuario=$usr->id");
							$x=$sql->rows?mysqli_fetch_object($sql->mysqry):"";

							if(is_object($x)) {
								$sql->update($_p."financeiro_fluxo",$vSQLPagamento,"where id=$x->id");
							} else {
								$sql->add($_p."financeiro_fluxo",$vSQLPagamento.",data=now()");
							}
							$i++;
						}
					}
				}
			} else {

				$vSQL=substr($vSQL,0,strlen($vSQL)-1);
				$vWHERE="where id='".$cnt->id."'";
				$sql->update($_table,$vSQL,$vWHERE);
				$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='update',vsql='".addslashes($vSQL)."',vwhere='".addslashes($vWHERE)."',tabela='".$_table."',id_reg='".$cnt->id."'");
				$id_reg=$cnt->id;

				$jsc->jAlert("Informações salvas com sucesso!","sucesso","document.location.href='?".$url."'");
			
				
			}

		
			
			/*if(is_object($cnt)) {
				$vSQL=substr($vSQL,0,strlen($vSQL)-1);
				$vWHERE="where id='".$cnt->id."'";
				$sql->update($_table,$vSQL,$vWHERE);
				$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='update',vsql='".addslashes($vSQL)."',vwhere='".addslashes($vWHERE)."',tabela='".$_table."',id_reg='".$cnt->id."'");
				$id_reg=$cnt->id;
			} else {
				$sql->add($_table,$vSQL."data=now(),id_usuario='".$usr->id."'");
				$id_reg=$sql->ulid;
				$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='insert',vsql='".addslashes($vSQL)."',tabela='".$_table."',id_reg='".$sql->ulid."'");
			}*/

			
			$jsc->jAlert("Informações salvas com sucesso!","sucesso","document.location.href='".$_page."?".$url."'");
			die();
		};


		if(is_object($cnt) and isset($_GET['deleta']) and is_numeric($_GET['deleta'])) {

		}

		//var_dump($values);
	?>
		<script type="text/javascript">
			var valorTotal = 0;
			var pagamentos = [];
			var pagamentosHTML = `<div class="js-pagamento-item" style="background:var(--cinza1); border-radius:8px; margin-bottom:.5rem; padding:.5rem 1.5rem;">
										<div class="colunas3">
											<dl><dd><label class="js-num"></label><input type="text" name="" class="datepicker data js-vencimento" value="" /></dd></dl>												
											<dl><dd><input type="text" name="" value="" class="js-valor" /></dd></dl>
											<dl><dd>
												<select class="js-id_formadepagamento js-tipoPagamento">
													<option value="">Forma de Pagamento...</option>
													<?php echo $optionFormasDePagamento;?>
												</select>
											</dd></dl>
										</div>

										<dl>
											<dt>Identificador</dt>
											<dd><input type="text" class="js-input-identificador" /></dd>
										</dl>
									</div>`;

			const pagamentosListar = () => {
				$('.js-pagamentos .js-pagamento-item').remove();
				//console.log(pagamentos);
				if(pagamentos.length>0) {

					
					let index=1;
					pagamentos.forEach(x=>{
						$('.js-pagamentos').append(pagamentosHTML);

						$('.js-pagamento-item .js-vencimento:last').val(x.vencimento);
						$('.js-pagamento-item .js-valor:last').val(number_format(x.valor,2,",","."));


						$('.js-pagamento-item .js-vencimento:last').inputmask('99/99/9999');
						$('.js-pagamento-item .js-num:last').html(index++);
						$('.js-pagamento-item .js-vencimento:last').datetimepicker({timepicker:false,
																				format:'d/m/Y',
																				scrollMonth:false,
																				scrollTime:false,
																				scrollInput:false});
						$('.js-pagamento-item .js-valor:last').maskMoney({symbol:'', allowZero:true, showSymbol:true, thousands:'.', decimal:',', symbolStay: true});
							$('.js-pagamento-item .js-input-identificador:last').val(x.identificador);
						if(x.id_formadepagamento) {
							$('.js-pagamento-item .js-id_formadepagamento:last').val(x.id_formadepagamento);
						
						}
					});

					if(pagamentos.length==1) $('.js-pagamento-item .js-valor:last').prop('disabled',true);
				}
				$('textarea.js-json-pagamentos').val(JSON.stringify(pagamentos))
				//atualizaValor();
				
			}

			const atualizaValor = (atualizacao,pelaQtd) => { 

				valorTotal=unMoney($('input[name=valor_total]').val());
				
				let parcelas = [];


				if($('input[name=pagamento]:checked').length>0) {
					if($('input[name=pagamento]:checked').val()=="avista") {
						$('.js-pagamentos-quantidade').hide();

						let item = {};
						item.vencimento='<?php echo date('d/m/Y');?>';
						item.identificador=$(`.js-input-identificador:eq(0)`).val();
						item.id_formadepagamento=$(`.js-id_formadepagamento:eq(0)`).val();
						item.valor=valorTotal;

						parcelas.push(item);

						if(pagamentos.length==1) {

						} else {
							pagamentos=parcelas;
						}

						$('.js-pagamentos-quantidade').val(1);

						/*console.log(x);
							$('.js-pagamentos').append(pagamentosHTML);
							$('.js-pagamento-item .js-vencimento:last').val(x.vencimento);
							$('.js-pagamento-item .js-valor:last').val(number_format(x.valor,2,",","."));
							$('.js-pagamento-item .js-vencimento:last').inputmask('99/99/9999');
							$('.js-pagamento-item .js-vencimento:last').datetimepicker({timepicker:false,
																					format:'d/m/Y',
																					scrollMonth:false,
																					scrollTime:false,*/
					} else {
						$('.js-pagamentos-quantidade').show();

						let numeroParcelas = $('.js-pagamentos-quantidade').val();
						//alert(numeroParcelas)
						if(numeroParcelas.length==0 || numeroParcelas<=0) numeroParcelas=2;
						
						valorParcela=valorTotal/numeroParcelas;

						let startDate = new Date();
						for(var i=1;i<=numeroParcelas;i++) {
							/*val = -1;
							if($(`.js-pagamentos .js-valor:eq(${i})`).length) {
								val = $(`.js-pagamentos .js-valor:eq(${(i-1)})`).val();
							}
							//console.log(`${$(`.js-pagamentos .js-valor:eq(${i})`).length} -> .js-pagamentos .js-valor:eq(${(i-1)}) => ${val}`);*/

							let item = {};
							let mes = startDate.getMonth()+1;
							mes = mes <= 9 ? `0${mes}`:mes;

							let dia = startDate.getDate();
							dia = dia <= 9 ? `0${dia}`:dia;
							item.vencimento=`${dia}/${mes}/${startDate.getFullYear()}`;
							item.valor=valorParcela;
							item.identificador=$(`.js-input-identificador:eq(${i-1})`).val();
							item.id_formadepagamento=$(`.js-id_formadepagamento:eq(${i-1})`).val();
							parcelas.push(item);

							newDate = startDate;
							newDate.setMonth(newDate.getMonth()+1);

							startDate=newDate;
						}
					}

					

					let totalAtual = ($('.js-valorTotal').html());
					if(totalAtual.length>0) totalAtual=unMoney(totalAtual);


					if(totalAtual!=0) {
						if(totalAtual!=valorTotal) {
							
							//$.notify('Os valores foram alterados.<br />Por favor redefina as formas de pagamento');
							swal({title: "Atenção", text: 'Os valores foram alterados.<br />Por favor redefina as formas de pagamento', html:true, type:"warning", confirmButtonColor: "#424242"});
							atualizacao=true;
							
						}
						else atualizacao=false;
					}

					if(atualizacao===true || pelaQtd===true) {
						pagamentos=parcelas;
					}

					//console.log(`estava ${totalAtual} e vai ficar ${valorTotal}`)
				}
				
				pagamentosListar();


				$('.js-valorTotal').val(number_format(valorTotal,2,",","."));
			}

			$(function(){
				
				$('.js-btn-fechar').click(function(){
					$('.cal-popup').hide();
						});
						
				$('input.money').maskMoney({symbol:'', allowZero:true, showSymbol:true, thousands:'.', decimal:',', symbolStay: true});
				<?php
				if($_receber==0) {
				?>
				$('input[name=valor]').keypress(function(){
					var val = eval($(this).val().replace(/[^0-9,-]/g, "").replace(',','.'));
					if(val>0) $(this).val(number_format((val*-1),2,",","."));
				});
				<?php	
				}
				?>

				$('.js-table-procedimentos').on('change','.js-valor',function(){
					let index = $(this).index(`.js-table-procedimentos .js-valor`);
					alert(index);
				});

				$('.js-pagamentos').on('change','.js-valor',function() {
					let index = $(this).index('.js-pagamentos .js-valor');
					pagamentos[index].valor=unMoney($(this).val());
					pagamentosListar();
				});

				$('.js-pagamentos').on('keyup','.js-valor',function(){
					let index = $(this).index('.js-pagamentos .js-valor');
					let numeroParcelas = eval($('.js-pagamentos-quantidade').val());
					let valorTotalAux = valorTotal;
					let valorAcumulado = 0;
					let parcelas = [];
					let val = unMoney($(this).val());



					for(i=0;i<=index;i++) {
						val = unMoney($(`.js-pagamentos .js-valor:eq(${i})`).val());

						id_formapagamento = $(`.js-pagamentos .js-id_formadepagamento:eq(${i})`).val();
						identificador = $(`.js-pagamentos .js-identificador:eq(${i})`).val();
						
						debitoBandeira = $(`.js-pagamentos .js-debitoBandeira:eq(${i})`).val();
						qtdParcelas = $(`.js-pagamentos .js-parcelas:eq(${i})`).val();
						valorAcumulado += val;
						//console.log(`${val} = ${valorAcumulado}`);

						let item = {};
						item.vencimento=pagamentos[i].vencimento;
						item.valor=val;
						item.id_formapagamento=id_formapagamento;
						item.identificador=identificador;
						item.qtdParcelas=qtdParcelas;

						parcelas.push(item);
					}

					let valorRestante = valorTotal-valorAcumulado;
					let continua = true;
					if(valorAcumulado>valorTotal) {

						let dif = valorAcumulado - valorTotal;
						dif=dif.toFixed(2);

						if(dif>0.1) {
							continua=false;
							swal({title: "Erro!", text: 'Os valores das parcelas não podem superar o valor total', html:true, type:"error", confirmButtonColor: "#424242"});
						}
					} 


					if(continua) {


						numeroParcelasRestantes = numeroParcelas - (index+1);
						valorParcela=valorRestante/numeroParcelasRestantes;

						for(i=(index+1);i<numeroParcelas;i++) {
							let item = {};
							item.vencimento=pagamentos[i].vencimento;
							item.valor=valorParcela;
							parcelas.push(item);

						}


						pagamentos=parcelas;


						$('textarea.js-json-pagamentos').val(JSON.stringify(pagamentos))
					}
				});


				$('.js-pagamentos-quantidade').click(function(){

					let qtd = $(this).val();

					if(!$.isNumeric(eval(qtd))) qtd=1;
					else if(qtd<1) qtd=2;
					else if(qtd>=36) qtd=36;


					$('.js-pagamentos-quantidade').val(qtd);

					atualizaValor(true,true);
				});

				$('input[name=credor_pagante]').change(function(){
					$('.js-box-bp').hide();

					if($('input[name=credor_pagante]:checked').val()=="paciente") {
						$('select[name=id_paciente]').parent().parent().show();
					} else if($('input[name=credor_pagante]:checked').val()=="fornecedor") {
						$('select[name=id_fornecedor]').parent().parent().show();
					} else if($('input[name=credor_pagante]:checked').val()=="colaborador") {
						$('select[name=id_colaborador]').parent().parent().show();
					}
				}).trigger('change');

				$('input[name=valor_total]').keyup(function(){
					let valorTotal = $(this).val();

					$('.js-valorTotal').val(valorTotal);
					
					atualizaValor(true,true);
				});

				$('input[name=pagamento]').click(function(){
					atualizaValor(false,true);
				});

				$('.js-pagamentos').on('change','input.js-input-identificador,select.js-id_formadepagamento',function(){
		
					atualizaValor(false,true);
				});

				$('.js-pagamento-avista').click();

			});	
		</script>

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
							
							<legend><span class="badge">1</span> Informações da Conta à <?php echo $_receber==1?"Receber":"Pagar";?></legend>

							<dl>
								<dt><?php echo $_receber==1?"Pagante":"Beneficiário";?></dt>
								<dd>
									<label><input type="radio" name="credor_pagante" value="fornecedor"<?php echo $values['credor_pagante']=="fornecedor"?" checked":"";?> /> Fornecedor</label>
									<label><input type="radio" name="credor_pagante" value="paciente"<?php echo $values['credor_pagante']=="paciente"?" checked":"";?> /> Paciente</label>
									<label><input type="radio" name="credor_pagante" value="colaborador"<?php echo $values['credor_pagante']=="colaborador"?" checked":"";?> /> Colaborador</label>
								</dd>
							</dl>

							<dl class="js-box-bp">
								<dt>Paciente</dt>
								<dd>
									<select name="id_paciente" class="chosen">
										<option value=""></option>
										<?php
										foreach($_pacientes as $x) {
											echo '<option value="'.$x->id.'"'.($values['id_paciente']==$x->id?' selected':'').'>'.utf8_encode($x->nome).'</option>';
										}
										?>
									</select>
								</dd>
							</dl>

							<dl class="js-box-bp">
								<dt>Fornecedor</dt>
								<dd>
									<select name="id_fornecedor" class="chosen">
										<option value=""></option>
										<?php
										foreach($_fornecedores as $x) {

											if(!empty($x->cnpjcpf)) {
												$fCPFCNPJ=strlen($x->cnpjcpf)==11?maskCPF($x->cnpjcpf):maskCNPJ($x->cnpjcpf);
											} else {
												$fCPFCNPJ='';
											}
											echo '<option value="'.$x->id.'"'.($values['id_fornecedor']==$x->id?' selected':'').'>'.utf8_encode($x->titulo).$fCPFCNPJ.'</option>';
										}
										?>
									</select>
								</dd>
							</dl>

							<dl class="js-box-bp">
								<dt>Colaborador</dt>
								<dd>
									<select name="id_colaborador" class="chosen">
										<option value=""></option>
										<?php
										foreach($_colaboradores as $x) {
											echo '<option value="'.$x->id.'"'.($values['id_colaborador']==$x->id?' selected':'').'>'.utf8_encode($x->nome).'</option>';
										}
										?>
									</select>
								</dd>
							</dl>
							
							<div class="colunas3">

								<dl>
									<dt>Valor Total</dt>
									<dd><input type="text" name="valor_total" value="<?php echo $values['valor'];?>" class="obg money"></dd>
								</dl>

								<dl>
									<dt>Data de Emissão</dt>
									<dd><input type="text" name="data_emissao" value="<?php echo $values['data_emissao'];?>" class="obg datecalendar data"></dd>
								</dl>

								<?php
								if(is_object($cnt)) {
								?>
								<dl>
									<dt>Data de Vencimento</dt>
									<dd><input type="text" name="data_vencimento" value="<?php echo $values['data_vencimento'];?>" class="obg datecalendar data"></dd>
								</dl>
								<dl>
									<dt>Forma de Pagamento</dt>
									<dd>
										<select name="id_formapagamento">
											<option value="">-</option>
											<?php
											foreach($_formasDePagamentos as $x) {
												echo '<option value="'.$x->id.'"'.($values['id_formapagamento']==$x->id?' selected':'').'>'.utf8_encode($x->titulo).'</option>';
											}
											?>
										</select>
									</dd>
								</dl>
								<?php
								}
								?>

							</div>

							<dl class="dl4">
								<dt>Descriçao</dt>
								<dd><input type="text" name="descricao" value="<?php echo $values['descricao'];?>" /></dd>
							</dl>
							
							<script type="text/javascript">
								const tipoProcessa = () => { 
									if($(`input[name=tipo]:checked`).val()=="produto") {
										$('select[name=id_categoria]').removeClass('obg').parent().parent().hide();
										$('input[name=custo_recorrente],input[name=custo_fixo]').parent().parent().parent().hide();
									} else {
										$('select[name=id_categoria]').addClass('obg').parent().parent().show();
										$('input[name=custo_recorrente],input[name=custo_fixo]').parent().parent().parent().show();

									}
								}
								$(function(){
									tipoProcessa();
									$('input[name=tipo]').change(function(){
										tipoProcessa()
									});
								})
							</script>

							<dl>
								<dd>
									<label><input type="radio" name="tipo" value="produto"<?php echo $values['tipo']=="produto"?" checked":"";?> /> Produto Odontológico</label>
									<label><input type="radio" name="tipo" value="outrosprodutos"<?php echo $values['tipo']=="outrosprodutos"?" checked":"";?> /> Outros Produtos/Serviços Gerais</label>
								</dd>
							</dl>

							<dl class="dl2">
								<dt>Categoria</dt>
								<dd>
									<select name="id_categoria" class="chosen">
										<option value=""></option>
										<?php
										foreach($_categoriasFinanceiroCategorias as $c)  {
											if(isset($_categoriasFinanceiroSubcategorias[$c->id])) {
												echo '<optgroup label="'.utf8_encode($c->titulo).'">';
												foreach($_categoriasFinanceiroSubcategorias[$c->id] as $v) {
													echo '<option value="'.$v->id.'"'.($values['id_categoria']==$v->id?' selected':'').'>'.utf8_encode($v->titulo).'</option>';
												}
												echo '</optgroup>';
											}
										}
										?>
									</select>
								</dd>
							</dl>

							<div class="colunas3">

								<dl>
									<dd>
										<label><input type="checkbox" name="custo_fixo" value="1"<?php echo $values['custo_fixo']==1?" checked":"";?> /> Custo Fixo</label>
									</dd>
								</dl>
								<dl>
									<dd>
										<label><input type="checkbox" name="custo_recorrente" value="1"<?php echo $values['custo_recorrente']==1?" checked":"";?> /> Custo Recorrente</label>
									</dd>
								</dl>
							</div>


							
						</fieldset>												
						<?php
						if(empty($cnt)) {
						?>
						<fieldset style="margin:0;">
							<legend><span class="badge">2</span> Defina o Financeiro</legend>

						
							<div class="js-formDiv-financeiro">
								<div class="colunas2">
									<dl>
										<dd>
											<label><input type="radio" name="pagamento" value="avista" class="js-pagamento-avista" /> À Vista</label>
											<label><input type="radio" name="pagamento" value="parcelado" class="js-pagamento-parcelado" /> Parcelado em</label>
											<input type="number" name="parcelas" style="float:left;width:50px;display: none;" value="" class="js-pagamentos-quantidade" />
										</dd>
									</dl>
									<dl>													
										<dd>
											<label style="white-space:nowrap">Valor Total:</label><input type="text" class="js-valorTotal" value="0,00" disabled style="max-width:90px; text-align:center;" />
										</dd>
									</dl>												
								</div>
							</div>

							<textarea name="pagamentos" class="js-json-pagamentos" style="display:none;"></textarea>
							
								
							<div class="js-pagamentos" style="margin-top:1rem;">
								
							</div>
								
						</fieldset>
						<?php
						} else {
							if(is_array($extrato)) {
						?>
						<fieldset>
							<legend>Conciliada com Movimentação Bancária</legend>
							
							<div class="registros">
								<table class="tablesorter">
									<tr>
										<th>Data</th>
										<th>Nº Doc</th>
										<th>Descrição</th>
										<th>Unidade</th>
										<th>Conta</th>
										<th>Valor</th>
										<th style="width:50px;">Ação</th>
									</tr>
									<?php
									$total=0;
									$descontosMultasJuros=array();
									foreach($extrato as $x) {

										if($x->juros!=0) $descontosMultasJuros[]=array('data'=>$x->data_extrato,
																						'titulo'=>'JUROS',
																						'valor'=>$x->juros);

										if($x->multa!=0) $descontosMultasJuros[]=array('data'=>$x->data_extrato,
																						'titulo'=>'MULTA',
																						'valor'=>$x->multa);

										if($x->desconto!=0) $descontosMultasJuros[]=array('data'=>$x->data_extrato,
																							'titulo'=>'DESCONTO',
																							'valor'=>$x->desconto);
									?>
									<tr>
										<td><?php echo $x->dataf;?></td>
										<td><?php echo !empty($x->checknumber)?utf8_encode($x->checknumber):"-";?></td>
										<td><?php echo utf8_encode($x->descricao);?></td>
										<td><?php echo isset($_contas[$x->id_conta])?utf8_encode($_unidades[$_contas[$x->id_conta]->id_unidade]->titulo):"-";?></td>
										<td><?php echo isset($_contas[$x->id_conta])?utf8_encode($_contas[$x->id_conta]->titulo):"-";?></td>
										<td style="text-align: right"><font color="<?php echo $x->valor>=0?"green":"red";?>"><?php echo number_format($x->valor,2,",",".");?></font></td>
										<td><a href="pg_financeiro_movimentacao.php?id_conta=<?php echo $x->id_conta;?>&form=1&edita=<?php echo $x->id;?>" target="_blank" class="button" style="color:#FFF"><span class="iconify" data-icon="bx:bx-search-alt"></span></a></td>
									</tr>
									<?php
									}
									foreach($descontosMultasJuros as $x) {
										$x=(object)$x;
									?>
									<tr>
										<td><?php echo date('d/m/Y',strtotime($cnt->data));?></td>
										<td>-</td>
										<td><?php echo $x->titulo;?> do MOVIMENTO</td>
										<td>-</td>
										<td>-</td>
										<td style="text-align: right"><font color="<?php echo $x->valor>=0?"green":"red";?>"><?php echo number_format($x->valor,2,",",".");?></font></td>
										<td>-</td>
									</tr>
									<?php	
									}
									if($cnt->juros!=0) {
										$total-=$cnt->juros;
									?>
									<tr>
										<td><?php echo date('d/m/Y',strtotime($cnt->data));?></td>
										<td>-</td>
										<td>JUROS</td>
										<td>-</td>
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
										<td><?php echo date('d/m/Y',strtotime($cnt->data));?></td>
										<td>-</td>
										<td>MULTA</td>
										<td>-</td>
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
										<td><?php echo date('d/m/Y',strtotime($cnt->data));?></td>
										<td>-</td>
										<td>DESCONTO</td>
										<td>-</td>
										<td>-</td>
										<td style="text-align: right"><font color="<?php echo $cnt->desconto>=0?"green":"red";?>"><?php echo number_format($cnt->desconto,2,",",".");?></font></td>
										<td>-</td>
									</tr>
									<?php
									}
									if($cnt->taxa!=0) {
										$total-=$cnt->valor-$cnt->valor_original;
									?>
									<tr>
										<td><?php echo date('d/m/y',strtotime($cnt->data));?></td>
										<td>-</td>
										<td>TAXA - <?php echo $cnt->taxa?>%</td>
										<td>-</td>
										<td>-</td>
										<td style="text-align: right"><font color="red"><?php echo number_format($cnt->valor-$cnt->valor_original,2,",",".");?></font></td>
										<td>-</td>
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
		}  else if(isset($_POST['conciliar']) and is_numeric($_POST['conciliar'])) {
			//var_dump($_POST['conciliar']);die();

			// metodo concilia Dinheiro, Cartao/Online (acumulativo) e outros
			if($financeiro->contaConciliar($_POST)) {
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
							<a href="?form=1" class="verde"><i class="iconify" data-icon="bx-bx-plus"></i><span>Conta à Pagar</span></a>
							<a href="?form=1&receber=1" class="verde"><i class="iconify" data-icon="bx-bx-plus"></i><span>Conta à Receber</span></a>
						</div>
					</div>


					
					<div class="filter-group filter-group_right">
						<form method="get" class="filter-form">
							<input type="hidden" name="csv" value="0" />
							<dl>
								<dd><input type="text" name="data_inicio" value="<?php echo isset($values['data_inicio'])?$values['data_inicio']:"";?>" class="noupper data datecalendar" placeholder="De" autocomplete="off" /></dd>
							</dl>
							<dl>
								<dd><input type="text" name="data_fim" value="<?php echo isset($values['data_fim'])?$values['data_fim']:"";?>" class="noupper data datecalendar" placeholder="Até" autocomplete="off" /></dd>
							</dl>
							<dl>
								<dd>
									<select name="status" placeholder="Status">
										<option value="">Status</option>
										<?php
										foreach($_status as $k=>$v) {
										?>
										<option value="<?php echo $k;?>"<?php echo (isset($values['status']) and $values['status']==$k)?' selected':'';?>><?php echo utf8_encode($v);?></option>
										<?php	
										}
										?>
									</select>
								</dd>
							</dl>
							<dl>
								<dd>
									<select name="id_formapagamento" placeholder="Forma de Pagamento">
										<option value="">Forma de Pagamento</option>
										<?php
										foreach($_formasDePagamentos as $k=>$v) {
										?>
										<option value="<?php echo $k;?>"<?php echo (isset($values['id_formapagamento']) and $values['id_formapagamento']==$k)?' selected':'';?>><?php echo utf8_encode($v->titulo);?></option>
										<?php	
										}
										?>
									</select>
								</dd>
							</dl>
							<button type="submit"><i class="iconify" data-icon="bx-bx-search"></i></button>
						</form>
					</div>

				</div>

				<section id="cal-popup" class="cal-popup cal-popup_paciente cal-popup_top cal-popup_alt" style="left:703px; top:338px; margin-left:303px;display: none">
					<a href="javascript:;" class="cal-popup__fechar js-btn-fechar"><i class="iconify" data-icon="mdi-close"></i></a>
					<section class="paciente-info">
						<header class="paciente-info-header">
							<section class="">
								<h1 class="js-titulo"></h1>
								<p style="color:var(--cinza4);"><span class="js-vencimento"></span></p>
							</section>
						</header>
						<input type="hidden" class="js-index" />

						<div class="abasPopover">
							<a href="javascript:;" class="js-pop-informacoes" onclick="$(this).parent().parent().find('a').removeClass('active');$(this).parent().parent().find('.js-grid').hide();$(this).parent().parent().find('.js-grid-info').show();$(this).addClass('active');" class="active">Informações</a>
							<a href="javascript:;" class="js-pop-descricao" onclick="$(this).parent().parent().find('a').removeClass('active');$(this).parent().parent().find('.js-grid').hide();$(this).parent().parent().find('.js-grid-descricao').show();$(this).addClass('active');" class="active">Descrição</a>
							
						</div>

						<div class="paciente-info-grid js-grid js-grid-info" style="font-size: 12px;">		
							
							<dl>
								<dt>Categoria</dt>
								<dd><input type="text" class="js-categoria" value="" readonly /></dd>
							</dl>
							<dl>
								<dt>Valor</dt>
								<dd><input type="text" class="js-valor" value="" readonly /></dd>
							</dl>
							<dl>
								<dt>Fixo</dt>
								<dd><input type="text" class="js-fixo" value="" readonly /></dd>
							</dl>
							<dl>
								<dt>Recorrente</dt>
								<dd><input type="text" class="js-recorrente" value="" readonly /></dd>
							</dl>
							
						</div>

						<div class="paciente-info-grid js-grid js-grid-descricao registros" style="font-size: 12px;display:none;"></div>

						
					</section>

					<div class="paciente-info-opcoes">
						<a href="javascript:;" class="js-btn-editar button">Editar</a>
						<a href="javascript:;" data-fancybox data-type="ajax" class="js-btn-pagar button">Pagar</a>
						<a href="javascript:;" data-fancybox data-type="ajax" class="js-btn-conciliar button">Conciliar</a><a href="javascript:;" class="js-btn-desconciliar button">Desconciliar</a>
					</div>
	    		</section>

				<script type="text/javascript">
					const popView = (obj) => {

						$('.js-pop-informacoes').click();

						index=$(obj).index();
						id=$(`div.reg a:eq(${index})`).find('.js-id').val();
						$('#cal-popup .js-titulo').html($(`div.reg a:eq(${index})`).find('.js-titulo').html());
						$('#cal-popup .js-vencimento').html($(`div.reg a:eq(${index})`).find('.js-vencimento').html());
						$('#cal-popup .js-categoria').val($(`div.reg a:eq(${index})`).find('.js-categoria').val());
						$('#cal-popup .js-valor').val($(`div.reg a:eq(${index})`).find('.js-valor').val());
						$('#cal-popup .js-recorrente').val($(`div.reg a:eq(${index})`).find('.js-recorrente').val());
						$('#cal-popup .js-fixo').val($(`div.reg a:eq(${index})`).find('.js-fixo').val());
						$('#cal-popup .js-grid-descricao').html($(`div.reg a:eq(${index})`).find('.js-descricao').val());

						pagamento = $(`div.reg a:eq(${index})`).find('.js-pagamento').val();
						conciliado = $(`div.reg a:eq(${index})`).find('.js-conciliado').val();

						$('#cal-popup .js-btn-desconciliar').hide();
						
						if(pagamento==1) {
							$('.js-btn-pagar').hide();

							if(conciliado==1) {
								$('#cal-popup .js-btn-conciliar').hide();
								$('#cal-popup .js-btn-desconciliar').show().attr('href',`?desconciliar=${id}&<?php echo $url;?>`);
							} else {

								$('#cal-popup .js-btn-conciliar').show();
							}
						} else {
							$('#cal-popup .js-btn-pagar').show();
							$('#cal-popup .js-btn-conciliar').hide();
						}

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
						$('#cal-popup .js-btn-pagar').attr('href',`box/boxFinanceiroEfetivar.php?id=${id}`);
						$('#cal-popup .js-btn-conciliar').attr('href',`box/boxConciliacaoDeFluxo.php?id=${id}`);
						
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
				$where="WHERE lixo='0'";

				if(isset($values['data_inicio']) and !empty($values['data_inicio'])) {
					$where .=" and data_vencimento>='".$values['data_inicioWH']."'";
				}

				if(isset($values['data_fim']) and !empty($values['data_fim'])) {
					$where .=" and data_vencimento<='".$values['data_fimWH']."'";
				}

				if(isset($values['id_formapagamento']) and is_numeric($values['id_formapagamento'])) $where.=" and id_formapagamento='".$values['id_formapagamento']."'";
				

				?>
				<div class="reg">
					<?php
					$sql->consultPagMto2($_table,"*",10,$where,"",15,"pagina",$_page."?".$url."&pagina=");
					echo $where."->".$sql->rows;;
					$registros=array();
					$fluxosIDs=array();
					$fornecedoresIds=$colaboradoresIds=$pacientesIds=array(-1);
					while($x=mysqli_fetch_object($sql->mysqry)) {
						$registros[]=$x;
						$fluxosIDs[]=$x->id;

						if($x->credor_pagante=="fornecedor") $fornecedoresIds[]=$x->id_fornecedor;
						else if($x->credor_pagante=="colaborador") $colaboradoresIds[]=$x->id_colaborador;
						else if($x->credor_pagante=="paciente") $pacientesIds[]=$x->id_paciente;

					}




					$contasConciliadas=$contasConciliadasID=array();
					if(count($fluxosIDs)>0) {
						$sql2=new Mysql();
						$sql->consult($_p."financeiro_conciliacoes","*","where id_fluxo in (".implode(",",$fluxosIDs).") and lixo=0");
						if($sql->rows) {
							while($x=mysqli_fetch_object($sql->mysqry)) {
								$contasConciliadas[$x->id_fluxo][]=$x;
								$contasConciliadasID[]=$x->id_extrato;
								if($x->multiplo==1) {
									$sql2->consult($_p."financeiro_conciliacoes","*","where id_extrato='".$x->id_extrato."' and lixo=0");
									$extratosConciliados[$x->id_fluxo]=$sql2->rows;
								}
							}
						}
					} 
					$conciliadasBanco=array();
					if(isset($contasConciliadas) and count($contasConciliadasID)>0) {
						$sql->consult($_p."financeiro_extrato","*","where id in (".implode(",",$contasConciliadasID).") and lixo=0");
						if($sql->rows) {
							while($x=mysqli_fetch_object($sql->mysqry)) { 
								$conciliadasBanco[$x->id]=$x;
							}
						}
					} 

					if(count($registros)==0) {
						echo "<center>Nenhuma registro</center>";
					} else {
					

						$_fornecedores=array();
						$sql->consult($_p. "parametros_fornecedores","id,IF(tipo_pessoa='PJ',razao_social,nome) as titulo","where id IN (".implode(",",$fornecedoresIds).") order by titulo asc");
						while($x=mysqli_fetch_object($sql->mysqry)) {
							$_fornecedores[$x->id]=$x;
						}

						$_pacientes=array();
						$sql->consult($_p. "pacientes","id,nome","where id IN (".implode(",",$pacientesIds).") order by nome");
						while($x=mysqli_fetch_object($sql->mysqry)) {
							$_pacientes[$x->id]=$x;
						}

						$_colaboradores=array();
						$sql->consult($_p. "colaboradores","id,nome","where id IN (".implode(",",$colaboradoresIds).") order by nome");
						while($x=mysqli_fetch_object($sql->mysqry)) {
							$_colaboradores[$x->id]=$x;
						}

						foreach($registros as $x) {

							$credorPagante='';
							if($x->credor_pagante=="fornecedor") {
								if(isset($_fornecedores[$x->id_fornecedor])) $credorPagante=utf8_encode($_fornecedores[$x->id_fornecedor]->titulo);
							} else if($x->credor_pagante=="colaborador") {
								if(isset($_colaboradores[$x->id_colaborador])) $credorPagante=utf8_encode($_colaboradores[$x->id_colaborador]->nome);
							} else if($x->credor_pagante=="paciente") {
								if(isset($_pacientes[$x->id_paciente])) $credorPagante=utf8_encode($_pacientes[$x->id_paciente]->nome);
							}

							$_conciliado=0;
							if($x->pagamento==1 and isset($contasConciliadas[$x->id])) {
								$_conciliado=1;
							}

					?>
					<a href="javascript:;" class="reg-group" onclick="popView(this);">

						<input type="hidden" class="js-categoria" value="<?php echo isset($_categoriasFinanceiro[$x->id_categoria])?utf8_encode($_categoriasFinanceiro[$x->id_categoria]->titulo):'-';?>" />
						<input type="hidden" class="js-id" value="<?php echo $x->id;?>" />
						<input type="hidden" class="js-valor" value="<?php echo number_format($x->valor,2,",",".");?>" />
						<input type="hidden" class="js-recorrente" value="<?php echo $x->custo_recorrente==1?"Sim":"Não";?>" />
						<input type="hidden" class="js-fixo" value="<?php echo $x->custo_fixo==1?"Sim":"Não";?>" />
						<input type="hidden" class="js-descricao" value="<?php echo !empty($x->descricao)?utf8_encode($x->descricao):'-';?>" />
						<input type="hidden" class="js-pagamento" value="<?php echo $x->pagamento;?>" />
						<input type="hidden" class="js-conciliado" value="<?php echo $_conciliado;?>" />

						<div class="reg-color" style=""></div>
						<div class="reg-data" style="flex:0 1 30%;">
							<h1 class="js-titulo"><?php echo $credorPagante;?></h1>
							<p class="js-vencimento">Forma de Pag.: <?php echo isset($_formasDePagamentos[$x->id_formapagamento])?utf8_encode($_formasDePagamentos[$x->id_formapagamento]->titulo):'-';?></p>
							<p class="js-vencimento">Vencimento: <?php echo date('d/m/Y',strtotime($x->data_vencimento));?></p>
						</div>
						<div class="reg-steps" style="margin:0 auto;">

							<div class="reg-steps__item active">
								<h1 style="background:var(--verde);">1</h1>
								<p>Parcela lançada</p>									
							</div>

							<?php

							$cor['promessa']='';
							if($x->id_formapagamento>0) $cor['promessa']='var(--verde);';
							else $cor['promessa']='var(--amarelo);';

							if($x->pagamento==0) {
							?>
							<div class="reg-steps__item active">
								<h1 style="background:<?php echo $cor['promessa'];?>;color:#FFF">2</h1>
								<p>Promessa de Pagamento</p>									
							</div>

							<?php
								// se vencido
								if(strtotime($x->data_vencimento)<strtotime(date('Y-m-d'))) {

							?>
							<div class="reg-steps__item">
								<h1 style="background:var(--vermelho);color:#FFF">3</h1>
								<p>Vencido</p>									
							</div>
							<?php
								} else {
							?>
							<div class="reg-steps__item">
								<h1>3</h1>
								<p>Vencido/Pago/Conciliado</p>									
							</div>
							<?php

								}
							?>
							<?php

							} else {
							?>
							<div class="reg-steps__item active">
								<h1 style="background:<?php echo ($x->pagamento==1 or $_conciliado==1)?"var(--verde)":$cor['promessa'];?>;color:#FFF">2</h1>
								<p>Promessa de Pagamento</p>									
							</div>
							<?php
								if($_conciliado==0) {
							?>
							<div class="reg-steps__item">
								<h1 style="background:var(--azul);color:#FFF;">3</h1>
								<p>Pago</p>									
							</div>
							<?php
								} else {
							?>
							<div class="reg-steps__item">
								<h1 style="background:var(--verde);color:#FFF;">3</h1>
								<p>Conciliado</p>									
							</div>
							<?php		
								}
							}
							?>
							
						</div>						

						<div class="reg-data" style="flex:0 1 70px;">
							<h1>R$ <?php echo number_format($x->valor,2,",",".");?></h1>
							<p>
								Parcela <?php echo $x->parcela."/".$x->qtdParcelas;?>
							</p>
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