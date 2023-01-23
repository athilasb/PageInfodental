<?php
	if(isset($_POST['ajax'])) {
		require_once("lib/conf.php");
		require_once("usuarios/checa.php");
		$sql = new Mysql();
		$rtn = array();

		if($_POST['ajax']=="comissionamentoRemover") {
			$profissional='';
			if(isset($_POST['id_profissional']) and is_numeric($_POST['id_profissional'])) {
				$sql->consult($_p."colaboradores_dadoscontratacao", "*","where id='".$_POST['id_profissional']."' and lixo=0");
				if($sql->rows) {
					$profissional=mysqli_fetch_object($sql->mysqry);
				}
			}

			$comissionamento='';
			if(isset($_POST['id']) and is_numeric($_POST['id']) and is_object($profissional)) {
				$sql->consult($_p."profissionais_comissionamentopersonalizado","*","where id='".$_POST['id']."'");
				if($sql->rows) {
					$comissionamento=mysqli_fetch_object($sql->mysqry);
				}
			}

			if(is_object($profissional)) {
				if(is_object($comissionamento)) {
					$sql->update($_p."profissionais_comissionamentopersonalizado","lixo=$usr->id,lixo_data=now()","where id=$comissionamento->id");
					$rtn=array('success'=>true);
				} else {
					$rtn=array('success'=>false,'error'=>'Comissionamento não encontrado');
				}
			} else {
				$rtn=array('success'=>false,'error'=>'Profissional não encontrado!');
			}
		} else if($_POST['ajax']=="comissionamentoPersistir") {
			$profissional='';
			if(isset($_POST['id_profissional']) and is_numeric($_POST['id_profissional'])) {
				$sql->consult($_p."colaboradores_dadoscontratacao", "*","where id='".$_POST['id_profissional']."' and lixo=0");
				if($sql->rows) {
					$profissional=mysqli_fetch_object($sql->mysqry);
				}
			}

			$id_procedimento=(isset($_POST['id_procedimento']) and is_numeric($_POST['id_procedimento']))?$_POST['id_procedimento']:0;
			$id_plano=(isset($_POST['id_plano']) and is_numeric($_POST['id_plano']))?$_POST['id_plano']:0;
			$valor=(isset($_POST['valor']))?addslashes(valor($_POST['valor'])):0;
			$abaterCustos=(isset($_POST['abater_custos']) and $_POST['abater_custos']==1)?1:0;
			$abaterImpostos=(isset($_POST['abater_impostos']) and $_POST['abater_impostos']==1)?1:0;
			$tipo=(isset($_POST['tipo']) and !empty($_POST['tipo']))?addslashes($_POST['tipo']):'valor';
			$abaterTaxas=(isset($_POST['abater_taxas']) and $_POST['abater_taxas']==1)?1:0;

			if($id_procedimento==0) {
				$rtn=array('success'=>false,'error'=>'Procedimento não definido!');
			} else if($id_plano==0) {
				$rtn=array('success'=>false,'error'=>'Plano não definido!');
			} else {

				$comissionamento='';
				if(is_object($profissional)) {
					$sql->consult($_p."profissionais_comissionamentopersonalizado","*","where id_profissional='".$profissional->id."' and id_procedimento=$id_procedimento and id_plano=$id_plano and lixo=0");
					if($sql->rows) {
						$comissionamento=mysqli_fetch_object($sql->mysqry);
					}
				}

				if(is_object($profissional)) {

					$vsql="id_profissional=$profissional->id,
							id_procedimento=$id_procedimento,
							id_plano=$id_plano,
							valor='".$valor."',
							tipo='".$tipo."',
							abater_custos='".$abaterCustos."',
							abater_impostos='".$abaterImpostos."',
							abater_taxas='".$abaterTaxas."'";


					if(is_object($comissionamento)) {
						$vsql.=",alteracao_data=now(),id_alteracao=$usr->id";
						$sql->update($_p."profissionais_comissionamentopersonalizado",$vsql,"where id=$comissionamento->id");
						$id_reg=$comissionamento->id;
					} else {
						$vsql.=",data=now(),id_usuario=$usr->id";
						$sql->add($_p."profissionais_comissionamentopersonalizado",$vsql);
						$id_reg=$sql->ulid;
					}


					$_planos=array();
					$sql->consult($_p."parametros_planos","*","where lixo=0 order by titulo asc");
					while($x=mysqli_fetch_object($sql->mysqry)) {
						$_planos[$x->id]=$x;
					}

					$_procedimentos=array();
					$sql->consult($_p."parametros_procedimentos","*","where lixo=0 order by titulo asc");
					while($x=mysqli_fetch_object($sql->mysqry)) {
						$_procedimentos[$x->id]=$x;
					}

					$comissionamentoPersonalizado=array();
					$sql->consult($_p."profissionais_comissionamentopersonalizado","*","where id_profissional=$profissional->id and lixo=0");
					while($x=mysqli_fetch_object($sql->mysqry)) {
						$comissionamentoPersonalizado[]=array('id'=>$x->id,
																'id_profissional'=>$x->id_profissional,
																'id_plano'=>$x->id_plano,
																'id_procedimento'=>$x->id_procedimento,
																'plano'=>(isset($_planos[$x->id_plano])?utf8_encode($_planos[$x->id_plano]->titulo):'-'),
																'procedimento'=>isset($_procedimentos[$x->id_procedimento])?utf8_encode($_procedimentos[$x->id_procedimento]->titulo):"-",
																'tipo'=>$x->tipo,
																'valor'=>number_format($x->valor,2,",","."),
																'abaterCustos'=>$x->abater_custos,
																'abaterTaxas'=>$x->abater_taxas,
																'abaterImpostos'=>$x->abater_impostos);
					}

					$rtn=array('success'=>true,'comissionamento'=>$comissionamentoPersonalizado);
				} else {
					$rtn=array('success'=>false,'error'=>'Profissional não encontrado!');
				}
			}
		} 

		header("Content-type: application/json");
		echo json_encode($rtn);
		die();
	}
	include "includes/header.php";
	include "includes/nav.php";

	$_table=$_p."colaboradores_dadoscontratacao";
	$_page=basename($_SERVER['PHP_SELF']);

	$_cargos = array(
		'ASB' => 'ASB',
		'TSB' => 'TSB',
		'TPD' => 'TPD',
		'APD' => 'APD',
		'CD'  => 'Cirurgião Dentista',
		'AF'  => 'Administrador Financeiro',
		'R'   => 'Recepcionista', 
		'GR'  => 'Gerente Geral'
	);

	$_regimes = array(
		'CLT' => 'CLT',
		'ESTAGIO' => 'Estágio',
		'MEI' => 'MEI',
		'AUTONOMO' => 'Autônomo'
	);

	$colaborador=$cnt='';
	if(isset($_GET['id_colaborador']) and is_numeric($_GET['id_colaborador'])) {
		$sql->consult($_p."colaboradores","*","where id='".$_GET['id_colaborador']."'");
		if($sql->rows) {
			$colaborador=mysqli_fetch_object($sql->mysqry);
		}
	}

	$_planos=array();
	$sql->consult($_p."parametros_planos","*","where lixo=0 order by titulo asc");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_planos[$x->id]=$x;
	}

	$_procedimentos=array();
	$sql->consult($_p."parametros_procedimentos","*","where lixo=0 order by titulo asc");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_procedimentos[$x->id]=$x;
	}

	$sql->consult($_table,"*","WHERE id_colaborador='".$colaborador->id."' and lixo=0");
	if($sql->rows) {
		$cnt=mysqli_fetch_object($sql->mysqry);

		$comissionamentoPersonalizado=array();
		$sql->consult($_p."profissionais_comissionamentopersonalizado","*","where id_profissional=$cnt->id_colaborador and lixo=0");
		while($x=mysqli_fetch_object($sql->mysqry)) {
			$comissionamentoPersonalizado[]=array('id'=>$x->id,
													'id_profissional'=>$x->id_profissional,
													'id_plano'=>$x->id_plano,
													'id_procedimento'=>$x->id_procedimento,
													'plano'=>(isset($_planos[$x->id_plano])?utf8_encode($_planos[$x->id_plano]->titulo):'-'),
													'procedimento'=>isset($_procedimentos[$x->id_procedimento])?utf8_encode($_procedimentos[$x->id_procedimento]->titulo):"-",
													'tipo'=>$x->tipo,
													'valor'=>number_format($x->valor,2,",","."),
													'abaterCustos'=>$x->abater_custos,
													'abaterTaxas'=>$x->abater_taxas,
													'abaterImpostos'=>$x->abater_impostos);
		}
	}

	$campos=explode(",","id_colaborador,cargo,regime_contrato,salario");
	
	foreach($campos as $v) $values[$v]='';

	if(is_object($colaborador)) {
		$values=$adm->values($campos,$cnt);
	}

	if(isset($_POST['acao']) and $_POST['acao']=="wlib") {
		$vSQL=$adm->vSQL($campos,$_POST);
		$values=$adm->values;
		$processa=true;

		if($processa===true) {	
		
			if(is_object($cnt)) {
				$vSQL=substr($vSQL,0,strlen($vSQL)-1);
				$vWHERE="where id='".$cnt->id."'";
				$sql->update($_table,$vSQL,$vWHERE);
				$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='update',vsql='".addslashes($vSQL)."',vwhere='".addslashes($vWHERE)."',tabela='".$_table."',id_reg='".$cnt->id."'");
				$id_reg=$cnt->id;
			} else {
				$vSQL=substr($vSQL,0,strlen($vSQL)-1);
				$sql->add($_table,$vSQL);
				$id_reg=$sql->ulid;
				$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='insert',vsql='".addslashes($vSQL)."',tabela='".$_table."',id_reg='".$sql->ulid."'");
			}

			foreach($_planos as $v) {

				$tipo=isset($_POST['tipo_'.$v->id])?addslashes($_POST['tipo_'.$v->id]):0;
				$valor=isset($_POST['valor_'.$v->id])?valor(addslashes($_POST['valor_'.$v->id])):0;
				$abaterCustos=(isset($_POST['abater_custos_'.$v->id]) and $_POST['abater_custos_'.$v->id]==1)?1:0;
				$abaterImpostos=(isset($_POST['abater_impostos_'.$v->id]) and $_POST['abater_impostos_'.$v->id]==1)?1:0;
				$abaterTaxas=(isset($_POST['abater_taxas_'.$v->id]) and $_POST['abater_taxas_'.$v->id]==1)?1:0;

				$vSQLComissionamentoGeral="id_profissional=$id_reg,
											id_plano=$v->id,
											tipo='".$tipo."',
											valor='".$valor."',
											abater_custos='".$abaterCustos."',
											abater_impostos='".$abaterImpostos."',
											abater_taxas='".$abaterTaxas."'";

				$sql->consult($_p."profissionais_comissionamentogeral","*","where id_profissional=$id_reg and id_plano=$v->id and lixo=0");
				if($sql->rows) {
					$x=mysqli_fetch_object($sql->mysqry);
					$sql->update($_p."profissionais_comissionamentogeral",$vSQLComissionamentoGeral,"where id=$x->id");
				} else {
					$sql->add($_p."profissionais_comissionamentogeral",$vSQLComissionamentoGeral);
				}
			}

			$sql->update($_p."profissionais_comissionamentopersonalizado","lixo=1","where id_profissional=$id_reg");

			if(isset($_POST['comissionamentoPersonalizado'])) {
				$obj=json_decode($_POST['comissionamentoPersonalizado']);
			//	var_dump($obj);
				if(is_array($obj)) {
					foreach($obj as $v) {
						$vSQLCP="id_profissional=$id_reg,
								id_procedimento=$v->id_procedimento,
								id_plano=$v->id_plano,
								tipo='".addslashes(	$v->tipo)."',
								valor='".valor($v->valor)."',
								abater_custos='".$v->abaterCustos."',
								abater_impostos='".$v->abaterImpostos."',
								abater_taxas='".$v->abaterTaxas."',
								lixo=0";

						$cp='';
						//echo $vSQLCP."<BR>";
						$sql->consult($_p."profissionais_comissionamentopersonalizado","*","where id_profissional=$id_reg and 
																									id_plano='".addslashes($v->id_plano)."' and 
																									id_procedimento='".addslashes($v->id_procedimento)."'");
						if($sql->rows) {
							$cp=mysqli_fetch_object($sql->mysqry);
						}

						if(is_object($cp)) {
							$sql->update($_p."profissionais_comissionamentopersonalizado",$vSQLCP,"where id=$cp->id");
						} else {
							$sql->add($_p."profissionais_comissionamentopersonalizado",$vSQLCP);
						}
						
					}
				}
			}
			$jsc->jAlert("Informações salvas com sucesso!","sucesso","document.location.href='".$_page."?id_colaborador=".$colaborador->id."'");
			die();
		}
	}

?>
<script>
	$(function(){
		$('input.money').maskMoney({symbol:'', allowZero:false, showSymbol:true, thousands:'.', decimal:',', symbolStay: true});
	});
</script>
	<section class="content">
		
		<?php
		require_once("includes/abaColaborador.php");
		?>

		<form method="post" class="form formulario-validacao"  autocomplete="off" enctype="multipart/form-data">
			<input type="hidden" name="acao" value="wlib" />
			<input type="hidden" name="id_colaborador" value="<?php echo $colaborador->id;?>" />	

			<section class="grid" style="padding:2rem;">
				<div class="box">
					<div class="filter">

						<div class="filter-group">
							<div class="filter-button">
								<a href="javascript:history.back(-1);"><i class="iconify" data-icon="bx-bx-left-arrow-alt"></i></a>
							</div>
						</div>

						<div class="filter-group filter-group_right">
							<div class="filter-button">
								<?php if(is_object($cnt)){?><a href="?deletaContrato=<?php echo $cnt->id."&".$url;?>" class="js-deletar"><i class="iconify" data-icon="bx-bx-trash"></i></a><?php }?>
								<a href="javascript:;"><i class="iconify" data-icon="bx-bx-printer"></i></a>
								<a href="javascript:;" class="azul  btn-submit"><i class="iconify" data-icon="bx-bx-check"></i><span>salvar</span></a>
							</div>
						</div>

					</div>
					
					<div class="grid grid_3">

						<fieldset style="margin:0;">
							<legend style="font-size: 12px;">
								<div class="filter-group">
									<div class="filter-title">
										<span class="badge">1</span> Cargo
									</div>
								</div>
							</legend>	
							<div class="colunas4">
								<dl class="dl3">
									<dt>Cargo Atual</dt>
									<dd>
										<select name="cargo" class="obg">
											<option value="">-</option>
											<?php
											foreach($_cargos as $k => $v) {
												echo '<option value="'.$k.'"'.(($values['cargo']==$k)?' selected':'').'>'.$v.'</option>';
											}
											?>
										</select>
									</dd>
								</dl>
							</div>
						
						</fieldset>
						
						<fieldset style="margin:0;">
							<legend style="font-size: 12px;">
								<div class="filter-group">
									<div class="filter-title">
										<span class="badge">2</span> Regime de Contratação
									</div>
								</div>
							</legend>
							<div class="colunas4">
								<dl class="dl3">
									<dt>Regime de Contrato</dt>
									<dd>
										<select name="regime_contrato" class="obg">
											<option value="">-</option>
											<?php
											foreach($_regimes as $k => $v) {
												echo '<option value="'.$k.'"'.(($values['regime_contrato']==$k)?' selected':'').'>'.$v.'</option>';
											}
											?>
										</select>
									</dd>
								</dl>
							</div>
						</fieldset>

						<fieldset style="margin:0;">
							<legend style="font-size: 12px;">
								<div class="filter-group">
									<div class="filter-title">
										<span class="badge">3</span> Salário
									</div>
								</div>
							</legend>

							<div class="colunas4">
								<dl class="dl3">
									<dt>Valor</dt>
									<dd><input type="text" name="salario" value="<?php echo $values['salario'];?>" class="obg money" /></dd>
								</dl>
							</div>	
						</fieldset>
						</div>

						<fieldset style="margin:0;">
							<legend style="font-size: 12px;">
								<div class="filter-group">
									<div class="filter-title">
										<span class="badge">4</span> Comissionamento Geral
									</div>
								</div>
							</legend>
							<div class="registros">
								<table>
									<tr>
										<th style="width:200px;">Plano</th>
										<th style="width:150px;">Tipo</th>
										<th>Valor</th>
										<th>Abater Custo</th>
										<th>Abater Impostos</th>
										<th>Abater Taxa</th>
									</tr>
									<?php


									foreach($_planos as $v) {
										$cgTipo=$cgValor=$cgAbaterCustos=$cgAbaterImpostos=$cgAbaterTaxas='';
										if(is_object($cnt)) {
											$sql->consult($_p."profissionais_comissionamentogeral","*","where id_profissional=$cnt->id and id_plano=$v->id and lixo=0");
											if($sql->rows) {
												$x=mysqli_fetch_object($sql->mysqry);
												$cgTipo=$x->tipo;
												$cgValor=number_format($x->valor,2,",",".");
												$cgAbaterCustos=$x->abater_custos;
												$cgAbaterImpostos=$x->abater_impostos;
												$cgAbaterTaxas=$x->abater_taxas;
											}
											
										}
									?>
									<tr>
										<td><?php echo utf8_encode($v->titulo);?></td>
										<td>
											<select name="tipo_<?php echo $v->id;?>" class="js-cg-tipo">
												<option>-</option>
												<option value="valor"<?php echo $cgTipo=="valor"?" selected":"";?>>Valor Fixo (R$)</option>
												<option value="porcentual"<?php echo $cgTipo=="porcentual"?" selected":"";?>>Porcentual (%)</option>
												<option value="horas"<?php echo $cgTipo=="horas"?" selected":"";?>>Horas</option>
											</select>
										</td>
										<td><input type="text" name="valor_<?php echo $v->id;?>" class="money js-cg-valor" value="<?php echo $cgValor;?>" /></td>
										<td><label><input type="checkbox" name="abater_custos_<?php echo $v->id;?>" value="1"<?php echo $cgAbaterCustos==1?" checked":"";?> /> Abater</label></td>
										<td><label><input type="checkbox" name="abater_impostos_<?php echo $v->id;?>" value="1"<?php echo $cgAbaterImpostos==1?" checked":"";?> /> Abater</label></td>
										<td><label><input type="checkbox" name="abater_taxas_<?php echo $v->id;?>" value="1"<?php echo $cgAbaterTaxas==1?" checked":"";?>/> Abater</label></td>
									</tr>
									<?php	
									}
									?>
								</table>	
							</div>
						</fieldset>

						<fieldset style="margin:0;">
							<legend style="font-size: 12px;">
								<div class="filter-group">
									<div class="filter-title">
										<span class="badge">5</span> Comissionamento Personalizado
									</div>
								</div>
							</legend>

							<textarea name="comissionamentoPersonalizado" style="display:none;"></textarea>
							<input type="hidden" name="cp_id" value="0" />
							<div class="colunas4">
								<dl class="dl2">
									<dt>Procedimento</dt>
									<dd>
										<select name="cp_id_procedimento">
											<option value="">-</option>
											<?php
											foreach($_procedimentos as $c) {
												echo '<option value="'.$c->id.'">'.utf8_encode($c->titulo).'</option>';
											}
											?>
										</select>
									</dd>
								</dl>
								<dl>
									<dt>Plano</dt>
									<dd>
										<select name="cp_id_plano">
											<option value="">-</option>
											<?php
											foreach($_planos as $c) {
												echo '<option value="'.$c->id.'">'.utf8_encode($c->titulo).'</option>';
											}
											?>
										</select>
									</dd>
								</dl>
								<dl>
									<dt>Tipo</dt>
									<dd>
										<select name="cp_tipo">
											<option value="">-</option>
											<option value="valor">Valor Fixo (R$)</option>
											<option value="porcentual">Porcentual (%)</option>
											<option value="horas">Horas</option>
										</select>
									</dd>
								</dl>
							</div>

							<div class="colunas5">
								<dl>
									<dt>Valor</dt>
									<dd><input type="text" name="cp_valor" class="money" /></dd>
								</dl>
								<dl>
									<dt>&nbsp;</dt>
									<dd><label><input type="checkbox" name="cp_abater_custos" value="1" /> Abater Custos</label></dd>
								</dl>
								<dl>
									<dt>&nbsp;</dt>
									<dd><label><input type="checkbox" name="cp_abater_impostos" value="1" /> Abater Impostos</label></dd>
								</dl>
								<dl>
									<dt>&nbsp;</dt>
									<dd><label><input type="checkbox" name="cp_abater_taxas" value="1" /> Abater Taxas</label></dd>
								</dl>

								<dl>
									<dt>&nbsp;</dt>
									<dd>
										<a href="javascript:;" class="button button__sec js-cp-btn"><i class="iconify" data-icon="bx-bx-check"></i></a>
										<a href="javascript:;" class="js-cp-cancelar tooltip" style="display: none;color:red" title="Cancelar edição"><span class="iconify" data-icon="icons8:cancel"></span> cancelar edição</a>
									</dd>
								</dl>
							</div>	
							<script type="text/javascript">
								var id_profissional = '<?php echo is_object($colaborador)?$colaborador->id:0;?>';
								var comissionamento = <?php echo (isset($comissionamentoPersonalizado) and !empty($comissionamentoPersonalizado))?"JSON.parse('".json_encode($comissionamentoPersonalizado)."')":"[]";?>;

								const comissionamentoListar = () => {
									if(comissionamento) {
										$('.js-cp-table tbody tr').remove();
										comissionamento.forEach(x => {

											let tipo= $('select[name=cp_tipo] option[value='+x.tipo+']').text();
											let html =`<tr>
															<td>${x.procedimento}</td>
															<td>${x.plano}</td>
															<td>${tipo}</td>
															<td>${x.valor}</td>
															<td style="text-align:center;">${x.abaterCustos==1?'<i class="iconify" data-icon="bx-bx-check"></i>':'<span class="iconify" data-icon="dashicons:no-alt" data-inline="false"></span>'}</td>
															<td style="text-align:center;">${x.abaterImpostos==1?'<i class="iconify" data-icon="bx-bx-check"></i>':'<span class="iconify" data-icon="dashicons:no-alt" data-inline="false"></span>'}</td>
															<td style="text-align:center;">${x.abaterTaxas==1?'<i class="iconify" data-icon="bx-bx-check"></i>':'<span class="iconify" data-icon="dashicons:no-alt" data-inline="false"></span>'}</td>
															<td>
																<a href="javascript:;" data-id="${x.id}" class="js-editar registros__acao registros__acao_sec"><i class="iconify" data-icon="bx:bxs-edit-alt"></i></a>
																<a href="javascript:;" data-id="${x.id}" class="js-remover registros__acao registros__acao_sec"><i class="iconify" data-icon="bx:bxs-trash"></i></a>
															</td>
														</tr>`;
											$('.js-cp-table tbody').append(html);
										});

										$('textarea[name=comissionamentoPersonalizado]').val(JSON.stringify(comissionamento))
									}
								};

								$(function(){
									comissionamentoListar();

									$('.js-cp-table').on('click','.js-remover',function() {

										let index = $(this).index('table.js-cp-table .js-remover');
										let id = $(this).attr('data-id');
										comissionamento.splice(index,1);
										comissionamentoListar();

										if(eval(id_profissional)>0) {
											let data = `ajax=comissionamentoRemover&id_profissional=${id_profissional}&id=${id}`;
											$.ajax({
												type:'POST',
												data:data
											})
										} 
											
									});

									$('.js-cp-table').on('click','.js-editar',function(){

										let index = $(this).index('table.js-cp-table .js-editar');

										if(comissionamento[index]) {
											$('select[name=cp_id_procedimento]').val(comissionamento[index].id_procedimento);
											$('select[name=cp_id_plano]').val(comissionamento[index].id_plano);
											$('select[name=cp_tipo]').val(comissionamento[index].tipo);
											$('input[name=cp_valor]').val(comissionamento[index].valor);
											$('input[name=cp_abater_custos]').prop('checked',(comissionamento[index].abaterCustos==1?true:false));
											$('input[name=cp_abater_impostos]').prop('checked',(comissionamento[index].abaterImpostos==1?true:false));
											$('input[name=cp_abater_taxas]').prop('checked',(comissionamento[index].abaterTaxas==1?true:false));
											$('input[name=cp_id]').val(comissionamento[index].id);
											$('.js-cp-cancelar').show();
										} else {

										}
									});

									$('.js-cp-cancelar').click(function(){
											
										$('select[name=cp_id_procedimento]').val('');
										$('select[name=cp_id_plano]').val('');
										$('select[name=cp_tipo]').val('');
										$('input[name=cp_valor]').val('');
										$('input[name=cp_abater_custos]').prop('checked',false);
										$('input[name=cp_abater_impostos]').prop('checked',false);
										$('input[name=cp_abater_taxas]').prop('checked',false);
										$('input[name=cp_id]').val(0);
										comissionamentoListar();
									
										$(this).hide();
									});

									$('.js-cp-btn').click(function(){
										let cpIDProcedimento=$('select[name=cp_id_procedimento]').val();
										let cpIDPlano=$('select[name=cp_id_plano]').val();

										let cpProcedimento=$('select[name=cp_id_procedimento] option:selected').text();
										let cpPlano=$('select[name=cp_id_plano] option:selected').text();

										let cpTipo=$('select[name=cp_tipo]').val();
										let cpValor=$('input[name=cp_valor]').val();
										let cpAbaterCustos=$('input[name=cp_abater_custos]').prop('checked')?1:0;
										let cpAbaterImpostos=$('input[name=cp_abater_impostos]').prop('checked')?1:0;
										let cpAbaterTaxas=$('input[name=cp_abater_taxas]').prop('checked')?1:0;
										let cpID=$('input[name=cp_id]').val();

										let erro='';
										if(cpIDProcedimento.length==0) {
											erro='Selecione o Procedimento';
										} else if(cpIDPlano.length==0) {
											erro='Selecione o Plano';
										} else if(cpTipo.length==0) {
											erro='Selecione o Tipo';
										}  else if(cpValor.length==0) {
											erro='Defina um Valor';
										} 

										if(erro.length>0) {
											swal({title: "Erro!", text: erro, type:"error", confirmButtonColor: "#424242"});
										} else {
											let item = {};
											item.id  = eval(cpID);
											item.id_procedimento  = cpIDProcedimento;
											item.id_plano  = cpIDPlano;
											item.procedimento  = cpProcedimento;
											item.plano  = cpPlano;
											item.tipo  = cpTipo;
											item.valor  = cpValor;
											item.abaterCustos  = cpAbaterCustos;
											item.abaterTaxas  = cpAbaterTaxas;
											item.abaterImpostos  = cpAbaterImpostos;

											let persistido=false;
											if(id_profissional>0) {
												let data = `ajax=comissionamentoPersistir&id_profissional=${id_profissional}&id_procedimento=${cpIDProcedimento}&id_plano=${cpIDPlano}&valor=${cpValor}&abater_custos=${cpAbaterCustos}&abater_taxas=${cpAbaterTaxas}&abater_impostos=${cpAbaterImpostos}&tipo=${cpTipo}`;
												$.ajax({
													type:'POST',
													data:data,
													success:function(rtn) {
														if(rtn.success) {
															comissionamento=rtn.comissionamento;
															comissionamentoListar();
															$('select[name=cp_id_procedimento]').val('');
															$('select[name=cp_id_plano]').val('');
															$('select[name=cp_tipo]').val('');
															$('input[name=cp_valor]').val('');
															$('input[name=cp_abater_custos]').prop('checked',false);
															$('input[name=cp_abater_impostos]').prop('checked',false);
															$('input[name=cp_abater_taxas]').prop('checked',false);
															$('input[name=cp_id]').val(0);
															$('.js-cp-cancelar').hide();
														} else if(rtn.error) {
															swal({title: "Erro!", text: rtn.error, type:"error", confirmButtonColor: "#424242"});
														} else {
															swal({title: "Erro!", text: "Algum erro ocorreu durante o salvamento das informações. Tente novamente.", type:"error", confirmButtonColor: "#424242"});
														}
													},
													error:function() {
														swal({title: "Erro!", text: "Algum erro ocorreu durante o salvamento das informações. Tente novamente.", type:"error", confirmButtonColor: "#424242"});
													}
												});
											} else {
												if(item.id>0) {
													let newComissionamento = comissionamento.map((x)=>{
														if(x.id==item.id) {
															return item;
														} else {
															return x;
														}
													});
													comissionamento=newComissionamento;
												} else {
													comissionamento.push(item);
												}
												comissionamentoListar();
												$('select[name=cp_id_procedimento]').val('');
												$('select[name=cp_id_plano]').val('');
												$('select[name=cp_tipo]').val('');
												$('input[name=cp_valor]').val('');
												$('input[name=cp_abater_custos]').prop('checked',false);
												$('input[name=cp_abater_impostos]').prop('checked',false);
												$('input[name=cp_abater_taxas]').prop('checked',false);
												$('input[name=cp_id]').val(0);
												$('.js-cp-cancelar').hide();
											}
										}
									});
								});
							</script>
							<div class="registros">
								<table class="js-cp-table">
									<thead>
										<tr>
											<th style="width:200px;">Procedimento</th>
											<th style="width:200px;">Plano</th>
											<th style="width:150px;">Tipo</th>
											<th>Valor</th>
											<th>Abater Custo</th>
											<th>Abater Impostos</th>
											<th>Abater Taxa</th>
											<th style="width:120px;"></th>
										</tr>
									</thead>
									<tbody>

									</tbody>
								</table>
							</div>	
						</fieldset>
				</div>
			</section>


		</form>
		
<?php
include "includes/footer.php";
?>