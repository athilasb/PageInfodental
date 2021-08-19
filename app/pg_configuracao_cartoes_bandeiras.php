<?php
	$title="";
	include "includes/header.php";
	include "includes/nav.php";

	if($usr->tipo!="admin" and !in_array("parametros",$_usuariosPermissoes)) {
		$jsc->jAlert("Você não tem permissão para acessar esta área!","erro","document.location.href='dashboard.php'");
		die();
	}
	$values=$adm->get($_GET);
?>
<section class="content">
 
	<?php
	require_once("includes/abaConfiguracao.php");
	?>

	<?php
	$_table=$_p."parametros_cartoes_operadoras";
	$_page=basename($_SERVER['PHP_SELF']);

	$_width="";
	$_height="";
	$_dir="";

	$_bandeiras=array();
	$sql->consult($_p."parametros_cartoes_bandeiras","*","where lixo=0 order by titulo");
	while ($x=mysqli_fetch_object($sql->mysqry)) {
		$_bandeiras[$x->id]=$x;
	}
		
	$_operadoras=array();
	$sql->consult($_p."parametros_cartoes_operadoras","*","where lixo=0 order by titulo");
	while ($x=mysqli_fetch_object($sql->mysqry)) {
		$_operadoras[$x->id]=$x;
	}
		

	$operadora='';
	$campos=explode(",","titulo,id_banco");
	
	foreach($campos as $v) $values[$v]='';
	
	if(isset($_GET['id_operadora']) and is_numeric($_GET['id_operadora'])) {
		$sql->consult($_table,"*","where id='".$_GET['id_operadora']."'");
		if($sql->rows) {
			$operadora=mysqli_fetch_object($sql->mysqry);
			
			$values=$adm->values($campos,$operadora);
		} else {
			$jsc->jAlert("Informação não encontrada!","erro","document.location.href='".$_page."'");
			die();
		}
	}

	if(empty($operadora)) {
		$jsc->jAlert("Selecione a operadora!","erro","document.location.href='pg_configuracao_cartoes.php'");
		die();
	}
	$qtdParcelamento=6;

	if(isset($_POST['acao']) and $_POST['acao']=="wlib") {

	


		$bandeira='';
		if(isset($_POST['id_bandeira']) and is_numeric($_POST['id_bandeira'])) {
			$sql->consult($_p."parametros_cartoes_bandeiras","*","where id='".$_POST['id_bandeira']."'");
			if($sql->rows) {
				$bandeira=mysqli_fetch_object($sql->mysqry);

			}
		}

		if(empty(($bandeira))) {
			$jsc->jAlert("Selecione a Bandeira","erro","");
		} else {
		
			for($i=1;$i<=$qtdParcelamento;$i++) {
				for($p=1;$p<=$i;$p++) {
					$taxa = isset($_POST["credito_".$i."_".$p."_taxa"])?$_POST["credito_".$i."_".$p."_taxa"]:'';
					$prazo = isset($_POST["credito_".$i."_".$p."_prazo"])?$_POST["credito_".$i."_".$p."_prazo"]:'';

					$vsql = "id_bandeira=$bandeira->id,
								id_operadora=$operadora->id,
								operacao='credito',
								vezes=$i,
								parcela=$p,
								taxa='$taxa',
								prazo='$prazo'";

					$vwhere = "where id_bandeira=$bandeira->id and id_operadora=$operadora->id and operacao='credito' and vezes=$i and parcela=$p";

					if(empty($taxa) or empty($prazo)) continue;

					$sql->consult($_p."parametros_cartoes_taxas","*",$vwhere);
					if($sql->rows) {
						$x=mysqli_fetch_object($sql->mysqry);
						$sql->update($_p."parametros_cartoes_taxas",$vsql,$vwhere);
					} else {

						$sql->add($_p."parametros_cartoes_taxas",$vsql);
					}
				}
			}
		}
	}	


	$vwhere="where id_operadora=$operadora->id and lixo=0";
	$values=array();
	$sql->consult($_p."parametros_cartoes_taxas","*",$vwhere);


	if($sql->rows) {
		while($x=mysqli_fetch_object($sql->mysqry)) {
			$values[$x->operacao][$x->vezes][$x->parcela]=array('taxa'=>$x->taxa,
																'prazo'=>$x->prazo);
		}
	}
	echo json_encode($values);

	?>

	<section class="grid">
		<div class="box">

			<form method="post" class="form formulario-validacao"  autocomplete="off" enctype="multipart/form-data">
				<input type="hidden" name="acao" value="wlib" />

				<div class="filter">
					<div class="filter-group">
						<div class="filter-button">
							<a href="pg_configuracao_cartoes.php"><i class="iconify" data-icon="bx-bx-left-arrow-alt"></i></a>
						</div>
					</div>
					<div class="filter-group filter-group_right">
						<div class="filter-button">
							<?php 
							if(is_object($operadora)){
							?>
								<a href="<?php echo $_page;?>?deleta=<?php echo $operadora->id."&".$url;?>" class="js-deletar"><i class="iconify" data-icon="bx-bx-trash"></i></a>
								<a href="pg_configuracao_cartoes_bandeiras.php?id_operadora=<?php echo $operadora->id."&".$url;?>"><i class="iconify" data-icon="ic-baseline-settings"></i></a>
							<?php
							}
							?>
							<a href="javascript:;" class="azul btn-submit"><i class="iconify" data-icon="bx-bx-check"></i><span>salvar</span></a>
						</div>
					</div>
				</div>

				<fieldset class="registros">
					<legend><span class="badge">1</span> Regras de Pagamento</legend>
					
					<div class="colunas7">
						<dl>
							<dt>Operadora</dt>
							<dd><input type="text" value="<?php echo utf8_encode($operadora->titulo);?>" disabled /></dd>
						</dl>
						<dl class="dl2">
							<dt>Bandeira</dt>
							<dd>
								<select name="id_bandeira">
									<option>-</option>
									<?php
									foreach($_bandeiras as $v) echo '<option value="'.$v->id.'">'.utf8_encode($v->titulo).'</option>';
									?>
								</select>
							</dd>
						</dl>
						<dl>
							<dt>Qtd. de Parcelas</dt>
							<dd>
								<select name="creditoParcelas_<?php echo $v->id;?>" class="js-parcelas" data-idBandeira="<?php echo $v->id;?>">
									<option value="">-</option>
									<?php
									for($i=1;$i<=$qtdParcelamento;$i++) {
									?>
									<option value="<?php echo $i;?>"><?php echo $i."x";?></option>
									<?php
									}
									?>
								</select>
								<?php
								if(is_object($operadora)) {
								?>
								<script type="text/javascript">
									$(function(){
										$('select[name=creditoParcelas_<?php echo $v->id;?>]').val(<?php echo isset($_infos[$operadora->id][$v->id]['credito'])?count($_infos[$operadora->id][$v->id]['credito']):0;?>).trigger('change');
									})
								</script>
								<?php	
								}
								?>
							</dd>
						</dl>

						
						<?php /*<dl>
							<dt>Até quantas parcelas é sem juros</dt>
							<dd>
								<select name="semJuros_<?php echo $v->id;?>" class="js-semJuros js-semJuros-<?php echo $v->id;?>" data-id="<?php echo $v->id;?>">
									<option value="">-</option>
									<?php
									for($i=1;$i<=12;$i++) {
										echo '<option value="'.$i.'"'.($semJuros==$i?" selected":"").'>'.$i.'x</option>';
									}
									?>
								</select>
							</dd>
						</dl>*/?>
					</div>


					<table>
						<tr>
							<th style="width:100px">Débito</th>
							<?php
							for($vezes=1;$vezes<=$qtdParcelamento;$vezes++) {
							?>
							<th style="width:100px">Crédito <?php echo $vezes;?>x</th>
							<?php
							}
							?>
						</tr>

						<tr>
							<td valign="top"> 
								<div style="display:flex">
									<span>1x</span>&nbsp;
									<input type="text" name="debito_1_taxa" />&nbsp;
									<input type="text" name="debito_1_prazo" />
								</div>
							</td>
							<?php
							for($vezes=1;$vezes<=$qtdParcelamento;$vezes++) {
							?>
							<td valign="top">
								<?php
								for($parcela=1;$parcela<=$vezes;$parcela++) {
									$vTaxa = isset($values['credito'][$vezes][$parcela]) ? $values['credito'][$vezes][$parcela]['taxa'] : 0;
									$vPrazo = isset($values['credito'][$vezes][$parcela]) ? $values['credito'][$vezes][$parcela]['prazo'] : 0;
								?>
								<div style="display:flex">
									<span><?php echo $parcela;?>x</span>&nbsp;
									<input type="text" name="credito_<?php echo $vezes;?>_<?php echo $parcela;?>_taxa" value="<?php echo $vTaxa;?>" />&nbsp;
									<input type="text" name="credito_<?php echo $vezes;?>_<?php echo $parcela;?>_prazo" value="<?php echo $vPrazo;?>" />
								</div>
								<?php
								}
								?>
							</td>
							<?php
							}
							?>
						</tr>
					</table>
					
				</fieldset>
			</form>
		</div>
	</section>
	
	

</section>

<?php
	include "includes/footer.php";
?>