<?php
	if(isset($_POST['ajax'])) {
		require_once("lib/conf.php");	
		require_once("usuarios/checa.php");

		$sql = new Mysql();

		$rtn = array();
		if($_POST['ajax']=="planos") {
			$planos=array();
			if(isset($_POST['id_procedimento']) and is_numeric($_POST['id_procedimento'])) {
				$sql->consult($_p."parametros_procedimentos","*","where id='".addslashes($_POST['id_procedimento'])."' and lixo=0");
				if($sql->rows) {
					$procedimento=mysqli_fetch_object($sql->mysqry);
				}
			}
			$unidade='';
			if(isset($_POST['id_unidade']) and is_numeric($_POST['id_unidade']) and isset($_optUnidades[$_POST['id_unidade']])) {
				$unidade=$_optUnidades[$_POST['id_unidade']];
			}

			if(is_object($procedimento) and is_object($unidade)) {
				$sql->consult($_p."parametros_procedimentos_planos","*","where id_procedimento=$procedimento->id and 
																				id_unidade='".$unidade->id."' and 
																				lixo=0");
				
				$planosID=array();
				$procedimentoPlano=array();
				while($x=mysqli_fetch_object($sql->mysqry)) {
					$procedimentoPlano[$x->id_plano]=$x;
					$planosID[]=$x->id_plano;
				}	


				if(count($planosID)) {
					$sql->consult($_p."parametros_planos","*","where id IN (".implode(",",$planosID).")");
					if($sql->rows) {
						while($x=mysqli_fetch_object($sql->mysqry)) {
							if(isset($procedimentoPlano[$x->id])) {
								$procP=$procedimentoPlano[$x->id];
								$planos[]=array('id'=>$x->id,'titulo'=>utf8_encode($x->titulo),'valor'=>$procP->valor);
							}
						}
					}
				}

				$rtn=array('success'=>true,'planos'=>$planos);
			} else {
				$rtn=array('success'=>false,'error'=>'Procedimento/Unidade não definida(s)!');
			}
		}

		header("Content-type: application/json");
		echo json_encode($rtn);
		die();
	}
	if(isset($_GET['ajax'])) {
		if($_GET['ajax']=="profissao") {
			if(isset($_GET['id_profissao']) and is_numeric($_GET['id_profissao'])) {
				$_GET['edita']=$_GET['id_profissao'];
				$_GET['form']=1;
			}
			require_once("pg_parametros_profissoes.php");

		}

		die();
	}

	include "includes/header.php";
	include "includes/nav.php";


	$_table=$_p."pacientes_tratamentos";
	$_page=basename($_SERVER['PHP_SELF']);

	
	$_formasDePagamento=array();
	$optionFormasDePagamento='';
	$sql->consult($_p."parametros_formasdepagamento","*","order by titulo asc");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_formasDePagamento[$x->id]=$x;
		$optionFormasDePagamento.='<option value="'.$x->id.'">'.utf8_encode($x->titulo).'</option>';
	}

	$_regioesOpcoes=array();
	$sql->consult($_p."parametros_procedimentos_regioes_opcoes","*","order by titulo asc");
	while($x=mysqli_fetch_object($sql->mysqry)) $_regioesOpcoes[$x->id_regiao][]=$x;

	$_regioes=array();
	$sql->consult($_p."parametros_procedimentos_regioes","*","");
	while($x=mysqli_fetch_object($sql->mysqry)) $_regioes[$x->id]=$x;

	$_procedimentos=array();
	$sql->consult($_p."parametros_procedimentos","*","where lixo=0");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_procedimentos[$x->id]=$x;
	}

	$_planos=array();
	$sql->consult($_p."parametros_planos","*","where lixo=0");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_planos[$x->id]=$x;
	}

	$_profissionais=array();
	$sql->consult($_p."profissionais","*","where lixo=0 order by nome asc");//"where unidades like '%,$unidade->id,%' and lixo=0 order by nome asc");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_profissionais[$x->id]=$x;
	}

	$paciente='';
	if(isset($_GET['id_paciente']) and is_numeric($_GET['id_paciente'])) {
		$sql->consult($_p."pacientes","*","where id='".$_GET['id_paciente']."'");
		if($sql->rows) {
			$paciente=mysqli_fetch_object($sql->mysqry);
			$cnt=$paciente;
		}
	}
	$_selectSituacaoOptions=array('aguardandoAprovacao'=>'AGUARDANDO APROVAÇÃO',
															'aprovado'=>'APROVADO',
															'naoAprovado'=>'NÃO APROVADO',
															'observado'=>'OBSERVADO',
															'cancelado'=>'CANCELADO');

	$selectSituacaoOptions='<select class="js-situacao">';
	foreach($_selectSituacaoOptions as $key=>$value) {
		$selectSituacaoOptions.='<option value="'.$key.'">'.$value.'</option>';
	}
	$selectSituacaoOptions.='</select>';

	$selectProfissional='<select class="js-profissional"><option value="">-</option>';
	foreach($_profissionais as $p) {
		$selectProfissional.='<option value="'.$p->id.'">'.utf8_encode($p->nome).'</option>';
	}
	$selectProfissional.='</select>';

	$planosDosProcedimentos=array();
	$sql->consult($_p."parametros_procedimentos_planos","*","where id_unidade='".$usrUnidade->id."' and lixo=0");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$planosDosProcedimentos[$x->id_procedimento][]=array("id"=>$x->id_plano,"titulo"=>utf8_encode($_planos[$x->id_plano]->titulo));
	}



	$campos=explode(",","titulo");
	
	foreach($campos as $v) $values[$v]='';

	if(is_object($paciente)) {
	
		$values=$adm->values($campos,$cnt);
		$values['data']=date('d/m/Y H:i',strtotime($cnt->data));
		
	} else {
		$jsc->jAlert("Paciente não encontrado!","erro","document.location.href='pg_contatos_pacientes.php'");
		die();
	}
	?>

	<section class="content">
		
		<?php
		require_once("includes/abaPaciente.php");
		?>
		
		<?php
		/*
		if(!isset($_GET['form'])) {
		?>
		<div class="filtros">
			<h1 class="filtros__titulo">Tratamento</h1>
			<div class="filtros-acoes">
				<a href="<?php echo $_page."?form=1&$url";?>" data-padding="0" class="principal tooltip" title="Adicionar"><i class="iconify" data-icon="bx-bx-plus"></i></a>
			</div>
		</div>
		<?php
		}
		*/
		?>
			
		<?php
		if(isset($_GET['form'])) {

			$campos=explode(",","titulo");
			
			foreach($campos as $v) $values[$v]='';
			$values['procedimentos']="[]";

			$cnt='';
			if(isset($_GET['edita']) and is_numeric($_GET['edita'])) {
				$sql->consult($_table,"*","where id='".$_GET['edita']."' and lixo=0");
				if($sql->rows) {
					$cnt=mysqli_fetch_object($sql->mysqry);



					$values=$adm->values($campos,$cnt);
					$values['procedimentos']=utf8_encode($cnt->procedimentos);
				} else {
					$jsc->jAlert("Informação não encontrada!","erro","document.location.href='".$_page."'");
					die();
				}
			}
			if(isset($_POST['acao'])) {
				if($_POST['acao']=="wlib") {
					$vSQL=$adm->vSQL($campos,$_POST);
					$values=$adm->values;


					if(is_object($cnt)) {

					} else {
						if(isset($_POST['procedimentos'])  and !empty($_POST['procedimentos'])) {
							$procedimetosJSON=json_decode($_POST['procedimentos']);

							foreach($procedimetosJSON as $x) {
								//var_dump($x);
								//echo '<hr />';
							}
						}

					}
					
					$vSQL.="procedimentos='".addslashes(utf8_decode($_POST['procedimentos']))."',";
					

					if(is_object($cnt)) {
						$vSQL=substr($vSQL,0,strlen($vSQL)-1);
						$vWHERE="where id='".$cnt->id."'";
						$sql->update($_table,$vSQL,$vWHERE);
						$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='update',vsql='".addslashes($vSQL)."',vwhere='".addslashes($vWHERE)."',tabela='".$_table."',id_reg='".$cnt->id."'");
						$id_tratamento=$cnt->id;
					} else {
						$vSQL.="data=now(),id_paciente=$paciente->id";
						//echo $vSQL;die();
						$sql->add($_table,$vSQL);
						$id_tratamento=$sql->ulid;
						$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='insert',vsql='".addslashes($vSQL)."',tabela='".$_table."',id_reg='".$id_tratamento."'");
					}


					$jsc->jAlert("Informações salvas com sucesso!","sucesso","document.location.href='".$_page."?id_paciente=$paciente->id'");
					die();

				}
			}
		?>	
		<script type="text/javascript">
			var procedimentos = [];
			var id_unidade = '<?php echo $usrUnidade->id;?>';
			var planosDosProcedimentos = JSON.parse(`<?php echo json_encode($planosDosProcedimentos);;?>`);
			var pagamentos = [];
			var valorTotal = 0;
			var valorPagamento = 0;
			var valorSaldo = 0;
		</script>

		<form method="post" class="form formulario-validacao"  autocomplete="off" enctype="multipart/form-data">
			<input type="hidden" name="acao" value="wlib" />
			
			<section class="grid" style="padding:2rem;">
			
					<div class="box">
						
						<div class="filtros">
							<h1 class="filtros__titulo">Plano de Tratamento</h1>
							<div class="filtros-acoes">
								<a href="<?php echo $_page."?".$url;?>"><i class="iconify" data-icon="bx-bx-left-arrow-alt"></i></a>
								<a href="<?php echo $_page."?form=1&$url";?>" data-padding="0" class="principal tooltip" title="Adicionar"><i class="iconify" data-icon="bx-bx-check"></i></a>
							</div>
						</div>
						
						<div class="grid grid_3">
							<div style="grid-column:span 2">
								
								<?php
								if(is_object($cnt)) {
								?>
								<dl>
									<dt>Data</dt>
									<dd>
										<input type="text" name="data" value="<?php echo date('d/m/Y H:i',strtotime($cnt->data));?>" class="obg" disabled />
									</dd>
								</dl>
								<?php
								}
								?>
								<fieldset>
									<legend>Título</legend>
									<dl>
										<dd>
											<input type="text" name="titulo" value="<?php echo $values['titulo'];?>" class="obg" />
										</dd>
									</dl>
								</fieldset>

								<fieldset>
									<legend>Procedimentos</legend>
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

									<script type="text/javascript">
										var procedimentosHMTL = `<div class="js-procedimento-item" style="">
																	<span class="iconify js-btn-removerProcedimento" data-icon="clarity:remove-solid" data-inline="false" data-width="20" style="cursor:pointer;float:right;color:#ccc"></span>

																	<div class="colunas5">
																		<dl class="dl2">
																			<dt>Procedimento</dt>
																			<dd><input type="text" class="js-procedimento" disabled /></dd>
																		</dl>
																		<dl>
																			<dt>Qtd/Região</dt>
																			<dd class="js-regiao"></dd>
																			</dd>
																		</dl>
																		<dl class="dl2">
																			<dt>Situação</dt>
																			<dd class="js-situacao"><?php echo $selectSituacaoOptions;?></dd>
																			</dd>
																		</dl>
																	</div>

																	<div class="colunas5">
																		
																		<dl class="dl2">
																			<dt>Profissional</dt>
																			<dd class="js-profissional"><?php echo $selectProfissional;?></dd>
																			</dd>
																		</dl>
																		<dl class="dl2">
																			<dt>Plano</dt>
																			<dd class="js-plano"></dd>
																			</dd>
																		</dl>
																		<dl>
																			<dt>Valor</dt>
																			<dd><input type="text"  class="js-valor" /></dd>
																			</dd>
																		</dl>
																	</div>
																		
																</div>`;

										const atualizaValor = () => {
											valorTotal=0;
											$(`.js-table-procedimentos .js-tr .js-valor`).each(function(index,el){
												let val = unMoney($(el).val());
												valorTotal+=val;
											});

											valorPagamento=0;
											pagamentos.forEach(x=>{
												valorPagamento+=x.valor;
											});

											valorSaldo=valorPagamento-valorTotal;
											$('.js-valorTotal').html(`R$ ${number_format(valorTotal,2,",",".")}`);
											$('.js-valorPagamento').html(`R$ ${number_format(valorPagamento,2,",",".")}`);
											$('.js-valorSaldo').html(`R$ ${number_format(valorSaldo,2,",",".")}`);
										}

										const procedimentosListar = () => {

											$('.js-procedimentos .js-procedimento-item').remove();
											if(procedimentos.length>0) {
												procedimentos.forEach(x=>{
													
													let selectPlanos = `<select class="js-plano"></select>`;

													/*let tr = `<tr class="js-tr">
																	<td><label><input type="checkbox" class="js-checkbox-tratamentos" /></label></td>
																	<td>${x.procedimento}</td>
																	<td>${x.quantitativo==1?`<input type="text" class="js-quantidade" value="${x.quantidade}" />`:x.opcoes}</td>
																	<td><?php echo $selectProfissional;?></td>
																	<td><?php echo $selectSituacaoOptions;?></td>
																	<td>${selectPlanos}</td>
																	<td><input type="text" class="js-valor" value="${number_format(x.valor,2,",",".")}" /></td>
																	<td>
																		<a href="javascript:;" class="js-obs">${x.obs.length>0?`<span class="iconify" data-icon="bx:bxs-notepad" data-inline="false" data-width="30"></span>`:`<span class="iconify" data-icon="bx:bx-notepad" data-inline="false" data-width="30"></span>`}</a>
																	</td>
																</tr>`;*/
																	
													$(`.js-procedimentos`).append(procedimentosHMTL);

													$(`.js-procedimentos .js-procedimento:last`).val(x.procedimento);
													$(`.js-procedimentos .js-regiao:last`).html(x.quantitativo==1?`<input type="number" class="js-quantidade" value="${x.quantidade}" />`:`<input type="text" value="${x.opcoes}" disabled />`);
													$(`.js-procedimentos .js-plano:last`).html(selectPlanos);	
													$(`.js-procedimentos .js-valor:last`).val(number_format(x.valor,2,",","."));
													$('.js-procedimentos .js-valor:last').maskMoney({symbol:'', allowZero:true, showSymbol:true, thousands:'.', decimal:',', symbolStay: true});
													
													$(`.js-table-procedimentos .js-plano:last`).append(`<option value="">-</optino>`);

													if(planosDosProcedimentos[x.id_procedimento]) {
														planosDosProcedimentos[x.id_procedimento].forEach(p => {
															let sel = p.id==x.id_plano?' selected':'';    
															$(`.js-procedimentos .js-plano:last`).append(`<option value="${p.id}"${sel}>${p.titulo}</optino>`);
														});
													}

													$(`.js-procedimentos .js-situacao:last`).val(x.situacao);
													$(`.js-procedimentos .js-profissional:last`).val(x.profissional);

												});

												atualizaValor();
											}
											
											$('textarea.js-json-procedimentos').val(JSON.stringify(procedimentos))
										}

										const procedimentosRemover = (index) => {
											procedimentos.splice(index,1);
											procedimentosListar();
										}

										$(function(){
											$('.js-btn-add').click(function(){

												let id_procedimento = $(`.js-id_procedimento`).val();
												let id_regiao = $(`.js-id_procedimento option:selected`).attr('data-id_regiao');
												let id_plano = $(`.js-id_plano`).val();
												let valor = $(`.js-id_plano option:selected`).attr('data-valor');
												let procedimento = $(`.js-id_procedimento option:selected`).text();
												let plano = $(`.js-id_plano option:selected`).text();
												let quantitativo = $(`.js-id_procedimento option:selected`).attr('data-quantitativo');
												let quantidade = $(`.js-inpt-quantidade`).val();
												let situacao = `aguardandoAprovacao`;
												let obs = ``;
												//alert(quantitativo);

												let erro = ``;
												if(id_procedimento.length==0) erro=`Selecione o Procedimento`;
												//else if(quantitativo==1 && (quantidade.length==0 || eval(quantidade)<=0 || eval(quantidade)>=99)) erro=`Defina a quantidade<br />(mín: 1, máx: 99)`;
												else if(id_regiao>=2 && $(`.js-regiao-${id_regiao}-select`).val().length==0) erro=`Preencha a Região`
												else if(id_plano.length==0) erro=`Selecione o Plano`;

												if(erro.length==0) {

													let linhas=1;
													if(id_regiao>=2) {
														linhas = eval($(`.js-regiao-${id_regiao}-select`).val().length);
													}

													let item= {};

													
													let opcoes = ``;
													for(var i=0;i<linhas;i++) {
														item = {};
														item.id_procedimento=id_procedimento;
														item.procedimento=procedimento;
														item.id_regiao=id_regiao;
														item.id_plano=id_plano;
														item.plano=plano;
														item.profissional=0;
														item.quantidade=quantidade;
														item.situacao=situacao;
														item.valor=valor;
														item.desconto=0;
														item.quantitativo=quantitativo;
														item.obs='';

														opcoes = opcoesID = ``;
														if(id_regiao>=2) {
															opcoesID = $(`.js-regiao-${id_regiao}-select option:selected:eq(${i})`).val();
															opcoes = $(`.js-regiao-${id_regiao}-select option:selected:eq(${i})`).text();
														}
														item.opcoes=opcoes;
														item.opcoesID=opcoesID;

														procedimentos.push(item);
													}

													$(`.js-id_procedimento`).val('').trigger('chosen:updated');
													$(`.js-id_plano`).val('').trigger('chosen:updated');
													$(`.js-inpt-quantidade`).val(1).parent().parent().hide();
													
													$(`.js-regiao-${id_regiao}-select`).val([]).trigger('chosen:updated').parent().parent().hide();;

													procedimentosListar();
												} else {
													swal({title: "Erro!", text: erro, html:true, type:"error", confirmButtonColor: "#424242"});
												}
											});

											$('select.js-id_procedimento').change(function(){

												let id_procedimento = $(this).val();

												if(id_procedimento.length>0) {
													let id_regiao = $(this).find('option:selected').attr('data-id_regiao');
													let regiao = $(this).find('option:selected').attr('data-regiao');
													let quantitativo = $(this).find('option:selected').attr('data-quantitativo');

													$(`.js-inpt-quantidade`).parent().parent().hide();
													if(quantitativo==1) {
														$(`.js-inpt-quantidade`).parent().parent().show();
													}
													$(`.js-regiao`).hide();
													$(`.js-regiao-${id_regiao}`).show();
													$(`.js-regiao-${id_regiao}`).find('select').chosen();

													$(`.js-procedimento-btnOk`).show();
													let data = `ajax=planos&id_unidade=${id_unidade}&id_procedimento=${id_procedimento}`;
													$.ajax({
														type:"POST",
														data:data,
														success:function(rtn) {
															if(rtn.success) { 
																$('.js-id_plano option').remove();
																$('.js-id_plano').append(`<option value=""></option>`);
																if(rtn.planos) {

																	rtn.planos.forEach(x=> {
																		$('.js-id_plano').append(`<option value="${x.id}" data-valor="${x.valor}">${x.titulo}</option>`);
																	});
																}
																$('.js-id_plano').trigger('chosen:updated')
															}
														},
													})
												} else {
													$(`.js-regiao`).hide();
													$(`.js-procedimento-btnOk`).hide();
												}
											});

											$('.js-prcedimentos').on('click','.js-btn-removerProcedimento',function() {
												alert('a');
												let index = $(this).index('.js-procedimentos .js-btn-removerProcedimento');
												procedimentosRemover(index);
											});

											$('.js-table-procedimentos').on('change','.js-profissional',function(){
												let index = $(this).index(`.js-table-procedimentos .js-profissional`);
												procedimentos[index].profissional=$(this).val();
											});

											$('.js-table-procedimentos').on('change','.js-quantidade',function(){
												let index = $(this).index(`.js-table-procedimentos .js-quantidade`);
												procedimentos[index].quantidade=$(this).val();
											});

											$('.js-table-procedimentos').on('change','.js-situacao',function(){
												let index = $(this).index(`.js-table-procedimentos .js-situacao`);
												procedimentos[index].situacao=$(this).val();
											});

											$('.js-table-procedimentos').on('change','.js-valor',function(){
												let index = $(this).index(`.js-table-procedimentos .js-valor`);
												procedimentos[index].valor=unMoney($(this).val());
											});


											$('.js-table-procedimentos').on('click','.js-obs',function(){
												let index = $(this).index(`.js-table-procedimentos .js-obs`);
												let obsVal = procedimentos[index].obs;
												$('.js-boxObs-obs').val(obsVal);
												$('.js-boxObs-index').val(index);
												$.fancybox.open({
													'src':'#boxObs'
												});
											});

											$('#boxObs').on('click','.js-boxObs-salvar',function(){
												let index = $('#boxObs .js-boxObs-index').val();
												let obsVal = $('#boxObs .js-boxObs-obs').val();
												procedimentos[index].obs=obsVal;
												procedimentosListar();
												$.fancybox.close();
											});


											procedimentos=JSON.parse($('textarea.js-json-procedimentos').val());
											procedimentosListar();
											
										});
									</script>

									<?php /*<a href="javascript:;" class="registros__acao js-btn-remover"><span class="iconify" data-icon="clarity:remove-solid" data-inline="false" data-width="30"></span></a>*/ ?>

									<textarea name="procedimentos" class="js-json-procedimentos" style="display:none;"><?php echo $values['procedimentos'];?></textarea>
									<?php /*<div class="registros js-table-procedimentos">
										<table>
												<tr style="text-align: center;">
													<th style="width: 30px;"><input type="checkbox" class="js-checkbox-all" /></th>
													<th>Procedimento</th>
													<th>Região/Qtd.</th>
													<th>Profissional</th>
													<th>Situação</th>
													<th>Plano</th>
													<th style="width:120px;">Valor</th>
													<th style="width:50px;">Obs.</th>
												</tr>
										</table>
									</div>*/?>

									<div class="registros js-procedimentos">

									</div>
								</fieldset>					
							</div>
							
							<fieldset>
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
									<dl>
										<dt></dt>
										<dd>
											<a href="javascript:;" class="registros__acao js-btn-addPagamento"><i class="iconify" data-icon="ic-baseline-add"></i></a>
										</dd>
									</dl>
								</div>
								<textarea name="procedimentos" class="js-json-procedimentos" style="display:none;"><?php echo $values['procedimentos'];?></textarea>
								<div class="registros js-pagamentos">
									
								</div>
							</fieldset>
							
						</div>
					</div>

					<style type="text/css">
						/*
						.js-pagamento-item, .js-procedimento-item { 
							border:solid 1px #CCC;
							padding:15px;
							border-radius:5px;
							margin-bottom:15px;
							background:#f9f9f9; 
						}
						*/
					</style>
				
					<script type="text/javascript">
						var pagamentosHTML = `<div class="js-pagamento-item" style="">
													<span class="iconify js-btn-removerPagamento" data-icon="clarity:remove-solid" data-inline="false" data-width="20" style="cursor:pointer;float:right;color:#ccc" ></span>
													<div class="colunas4">
														<dl class="dl2">
															<dt>Vencimento</dt>
															<dd><input type="text" class="js-vencimento" /></select>
														</dl>
														<dl class="dl2">
															<dt>Valor</dt>
															<dd><input type="text" class="js-valor" /></select>
															</dd>
														</dl>
													</div>

														<dl>
															<dt>Pagamento</dt>
															<dd>
																<select class="js-formaDePagamento">
																	<option value="">-</option>
																	<?php echo $optionFormasDePagamento;?>
																</select>
															</dd>
														</dl>
												</div>`;


						const pagamentosListar = () => {
							$('.js-pagamentos .js-pagamento-item').remove();
							if(pagamentos.length>0) {
								pagamentos.forEach(x=>{
									$('.js-pagamentos').append(pagamentosHTML);
									$('.js-pagamento-item .js-vencimento:last').val(x.vencimento);
									$('.js-pagamento-item .js-valor:last').val(number_format(x.valor,2,",","."));
									$('.js-pagamento-item .js-formaDePagamento:last').val(x.formaDePagamento);
									$('.js-pagamento-item .js-vencimento:last').inputmask('99/99/9999');
									$('.js-pagamento-item .js-vencimento:last').datepicker({timepicker:false,
																							format:'d/m/Y',
																							scrollMonth:false,
																							scrollTime:false,
																							scrollInput:false});
									$('.js-pagamento-item .js-valor:last').maskMoney({symbol:'', allowZero:true, showSymbol:true, thousands:'.', decimal:',', symbolStay: true});
								});
							}
							atualizaValor();
						}

						const pagamentosRemover = (index) => {
							pagamentos.splice(index,1);
							pagamentosListar();
						}

						$(function(){

							$('.js-pagamentos').on('change','.js-valor',function() {
								let index = $(this).index('.js-pagamentos .js-valor');
								pagamentos[index].valor=unMoney($(this).val());
								atualizaValor();
							});

							$('.js-pagamentos').on('change','.js-vencimento',function() {
								let index = $(this).index('.js-pagamentos .js-vencimento');
								pagamentos[index].vencimento=$(this).val();
								atualizaValor();
							});

							$('.js-pagamentos').on('change','.js-formaDePagamento',function() {
								let index = $(this).index('.js-pagamentos .js-formaDePagamento');
								pagamentos[index].formaDePagamento=$(this).val();
								atualizaValor();
							});

							$('.js-pagamentos').on('click','.js-btn-removerPagamento',function() {
								let index = $(this).index('.js-pagamentos .js-btn-removerPagamento');
								pagamentosRemover(index);
								atualizaValor();
							});

							$('.js-btn-addPagamento').click(function() {
								item = {};
								item.vencimento = '';
								item.formaDePagamento = '' ;
								item.valor = 0;
								pagamentos.push(item);
								pagamentosListar();
							});

							$('.js-metodoPagamento').click(function() {
								if($(this).val()=="parcelado") {
									$('.js-parcelas').parent().parent().show();
								} else {
									$('.js-parcelas').parent().parent().hide();
								}
							});

							$('.js-metodoPagamento:checked').trigger('click');
						});
					</script>
			</section>
		</form>

		<section class="content" id="boxObs" style="display:none;width:50%;">

			<header class="caminho">
				<h1 class="caminho__titulo">Plano de Tratamento <i class="iconify" data-icon="bx-bx-chevron-right"></i> <strong>Observações</strong></h1>
			</header>

			<section class="content-grid">

				<section class="content__item">

					
					<input type="hidden" class="js-boxObs-index" />
					<dl>
						<dd>
							<textarea class="js-boxObs-obs"></textarea>
						</dd>
					</dl>

					<div class="acoes">
						<a href="javascript:;" class="button button__lg js-boxObs-salvar"><i class="iconify" data-icon="bx-bx-check"></i> Salvar</a>			
					</div>

				</section>
			</section>
		</section>
		<?php
		} else {
			$where="WHERE id_paciente=$paciente->id and lixo=0";
			$sql->consult($_table,"*",$where);
		?>

		<section class="grid">
			<div class="box">

				<div class="filtros">
					<h1 class="filtros__titulo">Tratamento</h1>
					<div class="filtros-acoes">
						<a href="<?php echo $_page."?form=1&$url";?>" data-padding="0" class="principal tooltip" title="Adicionar"><i class="iconify" data-icon="bx-bx-plus"></i></a>
					</div>
				</div>

				<div class="registros">

					<table class="tablesorter">
						<thead>
							<tr>
								<th>Nome do Tema</th>
								<th>Endereço</th>
								<th style="width:120px;">Ações</th>
							</tr>
						</thead>
						<tbody>
						<?php
						while($x=mysqli_fetch_object($sql->mysqry)) {
						?>
						<tr>
							<td><strong><?php echo utf8_encode($x->titulo);?></strong></td>
							<td><?php echo $x->code;?></td>
							<td>
								<a href="<?php echo $_page;?>?form=1&edita=<?php echo $x->id."&".$url;?>" class="registros__acao"><i class="iconify" data-icon="bx:bxs-edit-alt"></i></a>
								<?php if($usr->tipo=="admin") { ?><a href="<?php echo $_page;?>?deleta=<?php echo $x->id."&".$url;?>" class="registros__acao registros__acao_sec js-deletar"><i class="iconify" data-icon="bx:bxs-trash"></i></a><?php } ?>
							</td>
						</tr>
						<?php
						}
						?>
						</tbody>
					</table>
				</div>
				<?php
				}
				?>
			</div>
		</section>		
	</section>

<?php
	include "includes/footer.php";
?>