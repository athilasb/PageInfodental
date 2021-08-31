<?php
	$title="";
	include "includes/header.php";
	include "includes/nav.php";

	if($usr->tipo!="admin" and !in_array("clientes",$_usuariosPermissoes)) {
		$jsc->jAlert("Você não tem permissão para acessar esta área!","erro","document.location.href='dashboard.php'");
		die();
	}
	$values=$adm->get($_GET);

	$_profissoes=array();
	$sql->consult($_p."parametros_profissoes","*","where lixo=0 order by titulo asc");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_profissoes[$x->id]=$x;
	}


	$_fornecedores=array();
	$sql->consult($_p."parametros_fornecedores","id,IF(tipo_pessoa='PJ',razao_social,nome_fantasia) as titulo,IF(tipo_pessoa='PJ',cnpj,cpf) as cnpjcpf","where lixo=0 order by titulo asc");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_fornecedores[$x->id]=$x;
	}

	$_formasDePagamentos=array();
	$sql->consult($_p."parametros_formasdepagamento","*","where lixo=0 order by titulo asc");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_formasDePagamentos[$x->id]=$x;
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

	
	if(isset($_GET['form'])) {

		$cnt='';
		$campos=explode(",","data_vencimento,valor,descricao,id_categoria,credor_pagante,id_fornecedor,id_colaborador,id_paciente,id_formapagamento");
		
		foreach($campos as $v) $values[$v]='';
		$values['data_vencimento']=date('d/m/Y');
		$values['credor_pagante']="fornecedor";

		if(isset($_GET['edita']) and is_numeric($_GET['edita'])) {
			$sql->consult($_table,"*,date_format(data,'%d/%m/%Y %H:%i') as dataf","where id='".$_GET['edita']."'");
			if($sql->rows) {
				$cnt=mysqli_fetch_object($sql->mysqry);
				
				$values=$adm->values($campos,$cnt);
				$values['data']=$cnt->dataf;

			} else {
				$jsc->jAlert("Informação não encontrada!","erro","document.location.href='".$_page."'");
				die();
			}
		}
		if(isset($_POST['acao']) and $_POST['acao']=="wlib") {
			$vSQL=$adm->vSQL($campos,$_POST);
			$values=$adm->values;
			
			if(is_object($cnt)) {
				$vSQL=substr($vSQL,0,strlen($vSQL)-1);
				$vWHERE="where id='".$cnt->id."'";
				$sql->update($_table,$vSQL,$vWHERE);
				$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='update',vsql='".addslashes($vSQL)."',vwhere='".addslashes($vWHERE)."',tabela='".$_table."',id_reg='".$cnt->id."'");
				$id_reg=$cnt->id;
			} else {
				$sql->add($_table,$vSQL."data=now(),id_usuario='".$usr->id."'");
				$id_reg=$sql->ulid;
				$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='insert',vsql='".addslashes($vSQL)."',tabela='".$_table."',id_reg='".$sql->ulid."'");
			}

			
			$jsc->jAlert("Informações salvas com sucesso!","sucesso","document.location.href='".$_page."?form=1&edita=".$id_reg."&".$url."'");
			die();
			
			
		}	
	?>
		<script type="text/javascript">
			$(function(){
				$('input.money').maskMoney({symbol:'', allowZero:true, showSymbol:true, thousands:'.', decimal:',', symbolStay: true});
			})
		</script>
		<section class="grid">
			<div class="box">

				<form method="post" class="form formulario-validacao"  autocomplete="off" enctype="multipart/form-data">
					<input type="hidden" name="acao" value="wlib" />

					<div class="filter">
						<div class="filter-group">
							<div class="filter-button">
								<a href="<?php echo $_page;?>"><i class="iconify" data-icon="bx-bx-left-arrow-alt"></i></a>
							</div>
						</div>
						<div class="filter-group filter-group_right">
							<div class="filter-button">
								<?php if(is_object($cnt)){?><a href="<?php echo $_page;?>?deleta=<?php echo $cnt->id."&".$url;?>" class="js-deletar"><i class="iconify" data-icon="bx-bx-trash"></i></a><?php }?>
								<a href="javascript:;" class="azul btn-submit"><i class="iconify" data-icon="bx-bx-check"></i><span>salvar</span></a>
							</div>
						</div>
					</div>
					<fieldset>
						<legend><?php echo $_receber==1?"Conta à Receber":"Conta à Pagar";?></legend>
						<div class="colunas6">
							<dl>
								<dt>Vencimento</dt>
								<dd><input type="text" name="data_vencimento" value="<?php echo $values['data_vencimento'];?>" class="obg data datecalendar"></dd>
							</dl>
							<dl>
								<dt>Valor</dt>
								<dd><input type="text" name="valor" value="<?php echo $values['valor'];?>" class="obg money"></dd>
							</dl>
							<dl class="dl2">
								<dt>Forma de Pagamento</dt>
								<dd>
									<select name="id_formapagamento" class="" placeholder="Forma de Pagamento">
										<option value="">-</option>
										<?php
										foreach($_formasDePagamentos as $k=>$v) {
										?>
										<option value="<?php echo $k;?>"<?php echo $values['id_formapagamento']==$k?" selected":"";?>><?php echo utf8_encode($v->titulo);?></option>
										<?php	
										}
										?>
									</select>
								</dd>
							</dl>
						</div>
						<div class="colunas6">
							<dl class="dl2">
								<dt>Categoria <a href="pg_configuracoes_categorias.php" target="_blank" class="botao"><span class="iconify" data-icon="akar-icons:circle-plus"></span></a></dt>
								<dd>
									<select name="id_categoria" class="obg chosen">
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
							<dl class="dl4">
								<dt>Descriçao</dt>
								<dd><input type="text" name="descricao" value="<?php echo $values['descricao'];?>" /></dd>
							</dl>
						</div>
						<script type="text/javascript">
							$(function(){
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
							});
						</script>

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
										echo '<option value="'.$x->id.'"'.($values['id_fornecedor']==$x->id?' selected':'').'>'.utf8_encode($x->titulo).' - '.(strlen($x->cnpjcpf)==11?maskCPF($x->cnpjcpf):maskCNPJ($x->cnpjcpf)).'</option>';
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


					</fieldset>
				</form>
			</div>
		</section>
	<?php
	} else {


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
							<section class="paciente-info-header__inner1">
								<h1 class="js-titulo"></h1>
								<p style="color:var(--cinza4);"><span class="js-vencimento"></span></p>
							</section>
						</header>
						<input type="hidden" class="js-index" />

						<div class="abasPopover">
							<a href="javascript:;" class="js-pop-informacoes" onclick="$(this).parent().parent().find('a').removeClass('active');$(this).parent().parent().find('.js-grid').hide();$(this).parent().parent().find('.js-grid-info').show();$(this).addClass('active');" class="active">Informações</a>
							<a href="javascript:;" onclick="$(this).parent().parent().find('a').removeClass('active');$(this).parent().parent().find('.js-grid').hide();$(this).parent().parent().find('.js-grid-baixas').show();$(this).addClass('active');">Programação de Pag.</a>
							<a href="javascript:;" class="js-pop-agrupamento" onclick="$(this).parent().parent().find('a').removeClass('active');$(this).parent().parent().find('.js-grid').hide();$(this).parent().parent().find('.js-grid-pagamentos').show();$(this).addClass('active');">Agrupamento de Pag.</a>
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
										<th style="width:5%"></th>
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
							<a href="javascript:;" target="_blank" class="js-btn-pagamento button ">Programação de Pagamentos</a>
							
						</div>
					</section>
	    		</section>

				<script type="text/javascript">
					const popView = (obj) => {

						$('.js-pop-informacoes').click();

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

								if(x.pago==1) {
									icon = `<span class="iconify" data-icon="akar-icons:circle-check" data-inline="true" style="color:green"></span>`;
								} else {
									if(x.vencido) {
										icon = `<span class="iconify" data-icon="icons8:cancel" data-inline="true" style="color:red"></span>`;
									} else {
										icon = `<span class="iconify" data-icon="bx:bx-hourglass" data-inline="true" style="color:orange"></span>`;
									}
								}

								$('.js-baixas').append(`<tr>
															<td>${icon}</td>
															<td>${x.data}</td>
															<td>${x.formaobs}</td>
															<td>${number_format(x.valor,2,",",".")}</td>
														</tr>`);
								});
						} else {
							$('.js-baixas').append(`<tr><td colspan="4"><center>Nehnuma programação de pagamento</center></td></tr>`);
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
																<td colspan="3"><center><a href="javascript:;" class="js-desfazerUniao" data-id_pagamento="${pagamentos[index].id_parcela}"><span class="iconify" data-icon="eva:undo-fill" data-inline="false"></span> Desfazer união</a></center></td>
															</tr>`)

							$('.js-pop-agrupamento').show();
						} else {
							$('.js-pop-agrupamento').hide();
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
					if($sql->rows==0) {
						echo "<center>Nenhuma registro</center>";
					} else {
						$registros=array();
						$fornecedoresIds=$colaboradoresIds=$pacientesIds=array(-1);
						while($x=mysqli_fetch_object($sql->mysqry)) {
							$registros[]=$x;
							if($x->credor_pagante=="fornecedor") $fornecedoresIds[]=$x->id_fornecedor;
							else if($x->credor_pagante=="colaborador") $colaboradoresIds[]=$x->id_colaborador;
							else if($x->credor_pagante=="paciente") $pacientesIds[]=$x->id_paciente;
						}

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



					?>
					<a href="javascript:;" class="reg-group" onclick="popView(this);">
						<div class="reg-color" style="background-color:var(--cinza3)"></div>
						<div class="reg-data" style="flex:0 1 30%;">
							<h1><?php echo $credorPagante;?></h1>
							<p>Vencimento: <?php echo date('d/m/Y',strtotime($x->data_vencimento));?></p>
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
								<h1 style="background:<?php echo $cor['promessa'];?>;color:#FFF">2</h1>
								<p>Promessa de Pagamento</p>									
							</div>

							<div class="reg-steps__item">
								<h1 style="background:var(--verde);color:#FFF;">3</h1>
								<p>Pago</p>									
							</div>

							<?php
							}
							?>
							
						</div>						

						<div class="reg-data" style="flex:0 1 70px;">
							<h1>R$ <?php echo number_format($x->valor,2,",",".");?></h1>
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