<?php
	$title="";
	include "includes/header.php";
	include "includes/nav.php";

	if($usr->tipo!="admin" and !in_array("clientes",$_usuariosPermissoes)) {
		$jsc->jAlert("Você não tem permissão para acessar esta área!","erro","document.location.href='dashboard.php'");
		die();
	}
	$values=$adm->get($_GET);


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


		$cnt='';
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
						}
						?>
						
					</div>


					<?php /*<fieldset>
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
					</fieldset>*/?>
				</form>
			</div>
		</section>
	

</section>

<?php
	include "includes/footer.php";
?>