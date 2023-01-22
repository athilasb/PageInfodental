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
	$qtdParcelamento=12;

	$bandeira='';
	if(isset($_GET['id_bandeira']) and is_numeric($_GET['id_bandeira'])) {
		$sql->consult($_p."parametros_cartoes_bandeiras","*","where id='".$_GET['id_bandeira']."'");
		if($sql->rows) {
			$bandeira=mysqli_fetch_object($sql->mysqry);

		}
	}



	if(isset($_POST['acao']) and $_POST['acao']=="wlib") {

	
		if(empty(($bandeira))) {
			$jsc->jAlert("Selecione a Bandeira","erro","");
		} else {

			// Persiste configuracoes da bandeira
			$aceitaDebito = (isset($_POST['aceitaDebito']) and $_POST['aceitaDebito']==1)?1:0;
			$vsql="parcelasAte='".$_POST['parcelasAte']."',aceitaDebito='$aceitaDebito'";
			$sql->update($_p."parametros_cartoes_bandeiras",$vsql,"where id=$bandeira->id");

			// Persiste Debito
			if(isset($_POST['debito_taxa']) or isset($_POST['debito_prazo'])) {
				$taxa=addslashes($_POST['debito_taxa']);
				$prazo=addslashes($_POST['debito_prazo']);

				$vsql = "id_bandeira=$bandeira->id,
							id_operadora=$operadora->id,
							operacao='debito',
							taxa='$taxa',
							prazo='$prazo'";

				$vwhere = "where id_bandeira=$bandeira->id and id_operadora=$operadora->id and operacao='debito'";
				$sql->consult($_p."parametros_cartoes_taxas","*",$vwhere);
				if($sql->rows) {
					$x=mysqli_fetch_object($sql->mysqry);
					$sql->update($_p."parametros_cartoes_taxas",$vsql,$vwhere);
				} else {

					$sql->add($_p."parametros_cartoes_taxas",$vsql);
				}
			}



			// Persiste Credito
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

		$jsc->jAlert("Informações salvas com sucesso!","sucesso","document.location.href='?id_operadora=$operadora->id&id_bandeira=$bandeira->id'");
		die();
	}	

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
								<select name="id_bandeira" onchange="document.location.href='?id_operadora=<?php echo $operadora->id;?>&id_bandeira='+this.value">
									<option>-</option>
									<?php
									foreach($_bandeiras as $v) echo '<option value="'.$v->id.'"'.((is_object($bandeira) and $bandeira->id==$v->id)?'selected':'').'>'.utf8_encode($v->titulo).'</option>';
									?>
								</select>
							</dd>
						</dl>
						<dl>
							<dt>Qtd. de Parcelas</dt>
							<dd>
								<select name="parcelasAte" class="js-parcelas" data-idBandeira="<?php echo $v->id;?>">
									<option value="">-</option>
									<?php
									for($i=1;$i<=$qtdParcelamento;$i++) {
									?>
									<option value="<?php echo $i;?>"<?php echo ((is_object($bandeira) and $bandeira->parcelasAte==$i)?" selected":"");?>><?php echo $i."x";?></option>
									<?php
									}
									?>
								</select>
								<?php
								if(is_object($operadora)) {
								?>
								<script type="text/javascript">
									$(function(){
										$('select[name=creditoParcelas_<?php echo $v->id;?>]').val(<?php echo isset($_infos[$operadora->id][$v->id]['credito'])?count($_infos[$operadora->id][$v->id]['credito']):'';?>).trigger('change');
									})
								</script>
								<?php	
								}
								?>
							</dd>
						</dl>
						<dl>
							<dt>&nbsp;</dt>
							<dd>
								<label><input type="checkbox" name="aceitaDebito" value="1"<?php echo (is_object($bandeira) and $bandeira->aceitaDebito==1)?" checked": "";?> /> Aceita Débito</label>
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

					<?php
					if(is_object($bandeira)) {

						$values=array();

						$vwhere="where id_operadora=$operadora->id and id_bandeira=$bandeira->id and lixo=0 and operacao='debito'";
						$sql->consult($_p."parametros_cartoes_taxas","*",$vwhere);
						if($sql->rows) {
							while($x=mysqli_fetch_object($sql->mysqry)) {
								$values[$x->operacao]=array('taxa'=>$x->taxa,'prazo'=>$x->prazo);
							}
						}

						$vwhere="where id_operadora=$operadora->id and id_bandeira=$bandeira->id and lixo=0";
						$sql->consult($_p."parametros_cartoes_taxas","*",$vwhere);

						if($sql->rows) {
							while($x=mysqli_fetch_object($sql->mysqry)) {
								$values[$x->operacao][$x->vezes][$x->parcela]=array('taxa'=>$x->taxa,
																					'prazo'=>$x->prazo);
							}
						}


					?>
					<table>
						<tr>
							<?php
							if($bandeira->aceitaDebito==1) {
							?>
							<th style="width:100px">Débito</th>
							<?php
							}
							for($vezes=1;$vezes<=$bandeira->parcelasAte;$vezes++) {
							?>
							<th style="width:100px">Crédito <?php echo $vezes;?>x</th>
							<?php
							}
							?>
						</tr>

						<tr>
							<?php
							if($bandeira->aceitaDebito==1) {
							?>
							<td valign="top"> 
								<div style="display:flex">
									<span>1x</span>&nbsp;
									<input type="text" name="debito_taxa" value="<?php echo $values['debito']['taxa'];?>" />&nbsp;
									<input type="text" name="debito_prazo" value="<?php echo $values['debito']['prazo'];?>" />
								</div>
							</td>
							<?php
							}
							for($vezes=1;$vezes<=$bandeira->parcelasAte;$vezes++) {
							?>
							<td valign="top">
								<?php
								for($parcela=1;$parcela<=$vezes;$parcela++) {
									$vTaxa = isset($values['credito'][$vezes][$parcela]) ? $values['credito'][$vezes][$parcela]['taxa'] : 0;
									$vPrazo = isset($values['credito'][$vezes][$parcela]) ? $values['credito'][$vezes][$parcela]['prazo'] : 0;

									//if($vPrazo==0) $vPrazo=(30*$parcela)+1;
									//if($vTaxa==0) $vTaxa=2.6;
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
					<?php
					}
					?>
					
				</fieldset>
			</form>
		</div>
	</section>
	
	

</section>

<?php
	include "includes/footer.php";
?>