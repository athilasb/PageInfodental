<?php
	$title="";
	include "includes/header.php";
	include "includes/nav.php";

	if($usr->tipo!="admin" and !in_array("parametros",$_usuariosPermissoes)) {
		$jsc->jAlert("Você não tem permissão para acessar esta área!","erro","document.location.href='dashboard.php'");
		die();
	}
	$values=$adm->get($_GET);

	$_bandeirasDeCartao=array();
	$sql->consult($_p."parametros_cartoes_bandeiras","*","where lixo=0 order by titulo");
	while ($x=mysqli_fetch_object($sql->mysqry)) {
		$_bandeirasDeCartao[$x->id]=$x;
	}
?>
<section class="content">

	<?php /*<header class="caminho">
		<h1 class="caminho__titulo">Configurações <i class="iconify" data-icon="bx-bx-chevron-right"></i> <strong>Planos</strong></h1>
		<a href="javascript:;" class="caminho__tutorial button button__sec"><i class="iconify" data-icon="ic-baseline-slow-motion-video"></i> ASSISTIR TUTORIAL</a>
	</header>*/?> 

	<section class="content">

		<?php
			require_once("includes/abaConfiguracoes.php");
			$_table=$_p."parametros_cartoes_bandeiras";
			$_page=basename($_SERVER['PHP_SELF']);

			if(isset($_GET['form'])) {
				$cnt='';
				$campos=explode(",","titulo");
				
				foreach($campos as $v) $values[$v]='';
				
				if(isset($_GET['edita']) and is_numeric($_GET['edita'])) {
					$sql->consult($_table,"*","where id='".$_GET['edita']."'");
					if($sql->rows) {
						$cnt=mysqli_fetch_object($sql->mysqry);
						
						$values=$adm->values($campos,$cnt);
					} else {
						$jsc->jAlert("Informação não encontrada!","erro","document.location.href='".$_page."'");
						die();
					}
				}
				if(isset($_POST['acao'])) {

					// persiste a operadora
					$vSQLOperadora="titulo='".addslashes(utf8_decode($_POST['titulo']))."'";

					if(is_object($cnt)) {
						$sql->update($_table,$vSQLOperadora,"where id=$cnt->id");

						$id_operadora=$cnt->id;
					} else { 
						$sql->add($_table,$vSQLBandeira);
						$id_operadora=$sql->ulid;
					}

					$sql->update($_p."parametros_cartoes_taxas","lixo=1","where id_operadora=$id_operadora");

					$vsqlPeT=array();
					foreach($_bandeirasDeCartao as $b) {

						if(isset($_POST['bandeiras']) and is_array($_POST['bandeiras']) and in_array($b->id,$_POST['bandeiras'])) {

							if(isset($_POST['recebeCredito_'.$b->id])) {
								for($i=1;$i<=24;$i++) {

									if($_POST['creditoParcelas_'.$b->id]<$i) {
										continue;
									}

									$vsqlPeT[]=array("id_operadora"=>$id_operadora,
													"id_bandeira"=>$b->id,
													"operacao"=>"credito",
													"parcela"=>$i,
													"taxa"=>$_POST['credito_taxa_'.$i.'_'.$b->id],
													"cobrarCliente"=>isset($_POST['credito_cobrarCliente_'.$i.'_'.$b->id])?$_POST['credito_cobrarCliente_'.$i.'_'.$b->id]:0,
													"prazo"=>$_POST['credito_prazo_'.$i.'_'.$b->id]);
								}

								// persiste informacoes de sem juros
								$sql->consult($_p."parametros_cartoes_taxas_semjuros","*","where id_operadora=$id_operadora and id_bandeira=$b->id and lixo=0");
								if($sql->rows) {
									$taxaSemjuros=mysqli_fetch_object($sql->mysqry);
								} else {
									$sql->add($_p."parametros_cartoes_taxas_semjuros","id_operadora=$id_operadora,id_bandeira=$b->id");
									$sql->consult($_p."parametros_cartoes_taxas_semjuros","*","where id=$sql->ulid");
									$taxaSemjuros=mysqli_fetch_object($sql->mysqry);
								}
								$semJuros=isset($_POST['semJuros_'.$b->id])?$_POST['semJuros_'.$b->id]:0;
								$sql->update($_p."parametros_cartoes_taxas_semjuros","semjuros='".$semJuros."'","where id=$taxaSemjuros->id");
							} 

							if(isset($_POST['recebeDebito_'.$b->id])) {
								$vsqlPeT[]=array("id_operadora"=>$id_operadora,
													"id_bandeira"=>$b->id,
													"operacao"=>"debito",
													"parcela"=>1,
													"taxa"=>$_POST['debito_taxa_'.$b->id],
													"cobrarCliente"=>isset($_POST['debito_cobrarCliente_'.$b->id])?$_POST['debito_cobrarCliente_'.$b->id]:0,
													"prazo"=>$_POST['debito_prazo_'.$b->id]);
							}
						}



						


						

						/*$recebeCredito=isset($_POST['recebeCredito_'.$b->id])?$_POST['recebeCredito_'.$b->id]:0;
						$recebeDebito=isset($_POST['recebeDebito_'.$b->id])?$_POST['recebeDebito_'.$b->id]:0;

						$vSQLBandeira="recebeCredito='".$recebeCredito."',
											recebeDebito='".$recebeDebito."',
											semJuros='".$semJuros."'";

						$sql->update($_p."parametros_bandeirasDeCartao",$vSQLBandeira,"where id=$b->id");*/
					}

					foreach($vsqlPeT as $x) {
						$x=(object)$x;

						$where="where id_operadora=$x->id_operadora and 
										id_bandeira=$x->id_bandeira and 
										operacao='$x->operacao' and 
										parcela='$x->parcela'";

						$vSQL="id_operadora='".$x->id_operadora."',
								id_bandeira='".$x->id_bandeira."',
								operacao='".$x->operacao."',
								parcela='".$x->parcela."',
								taxa='".$x->taxa."',
								cobrarCliente='".$x->cobrarCliente."',
								prazo='".$x->prazo."',
								lixo=0";


						$sql->consult($_p."parametros_cartoes_taxas","*",$where);

						if($sql->rows) {
							$v=mysqli_fetch_object($sql->mysqry);
							$sql->update($_p."parametros_cartoes_taxas",$vSQL,"where id=$v->id");
						} else {
							$sql->add($_p."parametros_cartoes_taxas",$vSQL);
						}
					}

					$jsc->jAlert("Informações salvas com sucesso!","sucesso","document.location.href='".$_page."?form=1&edita=".$id_operadora."'");
			
				}
			?>
			<script type="text/javascript">
				const atualizaCards = () => {
					$('select.js-bandeira option').each(function(index,el){
						let idOperadora = $(el).val();
						if($(el).prop('selected')===true) {
							$(`#js-bandeira-${idOperadora}`).show();
						} else {
							$(`#js-bandeira-${idOperadora}`).hide();
						}
					})
				}

				const atualizaRecebimentos = () => {

					$('.js-recebeCredito').each(function(){
						let idBandeira = $(this).attr('data-idBandeira');
						if($(`.js-recebeCredito-${idBandeira}`).prop('checked')===true) {
							$(`.js-box-credito-${idBandeira}`).show();
						} else {
							$(`.js-box-credito-${idBandeira}`).hide();
						}
					});

					$('.js-recebeDebito').each(function(){
						let idBandeira = $(this).attr('data-idBandeira');
						if($(`.js-recebeDebito-${idBandeira}`).prop('checked')===true) {
							$(`.js-box-debito-${idBandeira}`).show();
						} else {
							$(`.js-box-debito-${idBandeira}`).hide();
						}
					});
				}

				$(function(){
					$('select.js-bandeira').change(function(){
						atualizaCards();
					});

					atualizaCards();
					$('input.js-recebeCredito').click(function(){
						atualizaRecebimentos();
					});

					//$('input.js-recebeCredito:checked,input.js-recebeDebito:checked').trigger('click')

					$('input.js-recebeDebito').click(function(){
						atualizaRecebimentos();
					});

					$('select.js-parcelas').change(function(){
						let idBandeira = $(this).attr('data-idBandeira');
						let numero = eval($(this).val());

						$(`tr.js-tr-${idBandeira}`).each(function(index,el) {
							if(index<numero) {
								$(el).show();
							} else {
								$(el).hide();
							}
						});

						$(`.js-semJuros-${idBandeira} option`).each(function(index,el) {
							if($(el).val().length>0) {
								if(numero>=eval($(el).val())) {
									$(el).show();
								} else {
									$(el).hide();
								}
							} else {
									$(el).show();

							}
						})
					});
					atualizaRecebimentos();

					$('select.js-parcelas').change(function(){
						let parcelas = eval($(this).val());
						let id = $('select.js-parcelas')
						$(`.js-semJuros-${id}`)


					});
				})
			</script>

			<form method="post" class="form formulario-validacao"  autocomplete="off" enctype="multipart/form-data">
				<input type="hidden" name="acao" value="wlib" />

			
				<section class="filtros">
					<h1 class="filtros__titulo">Cartões / Operadoras de Cartão</h1>
					<div class="filtros-acoes">
						<a href="<?php echo $_page."?".$url;?>" ><i class="iconify" data-icon="bx-bx-left-arrow-alt"></i></a>
						<a href="javascript:;" class="principal btn-submit"><i class="iconify" data-icon="bx-bx-check"></i></a>	
						<a class="sec js-deletar" href="<?php echo $_page;?>?deleta=<?php echo $cnt->id."&".$url;?>"><i class="iconify" data-icon="bx-bx-trash"></i></a>
					</div>
				</section>

				<?php
				require_once("includes/abaConfiguracoesCartoes.php");

				$_bandeirasDestaOperadora=array();
				$_infos=array();
				if(is_object($cnt)) {
					$sql->consult($_p."parametros_cartoes_taxas","*","where id_operadora=$cnt->id and lixo=0");
					if($sql->rows) {
						while($x=mysqli_fetch_object($sql->mysqry)) {
							$_infos[$cnt->id][$x->id_bandeira][$x->operacao][$x->parcela]=$x;
							$_bandeirasDestaOperadora[$x->id_bandeira]=$x->id_bandeira;

						}
					}
				}
				?>

				<section class="grid" style="padding:2rem;">
					<div class="box">
						<h1 class="paciente__titulo1">Informações</h1>

						<dl>
							<dt>Operadora</dt>
							<dd>
								<input type="text" name="titulo" value="<?php echo $values['titulo'];?>" class="obg" />
							</dd>
						</dl>
						<dl>
							<dt>Bandeiras de Cartão</dt>
							<dd>
								<select name="bandeiras[]" class="chosen js-bandeira" multiple>
								<?php
								foreach($_bandeirasDeCartao as $v) {
									$sel=in_array($v->id, $_bandeirasDestaOperadora)?" selected":"";
									echo '<option value="'.$v->id.'"'.$sel.'>'.utf8_encode($v->titulo).'</option>';
								}
								?>
								</select>
							</dd>
						</dl>

					</div>

					<div class="grid grid_3">
						<?php
						foreach($_bandeirasDeCartao as $v) {



							$selRecebeDebito=$selRecebeCredito='';

							if(is_object($cnt)) {
								$taxaSemjuros='';

								$sql->consult($_p."parametros_cartoes_taxas_semjuros","semjuros","where id_operadora=$cnt->id and id_bandeira=$v->id and lixo=0");
							
								if($sql->rows) {
									$taxaSemjuros=mysqli_fetch_object($sql->mysqry);
									$semJuros=$taxaSemjuros->semjuros;
								}

								if(isset($_infos[$cnt->id][$v->id]['debito'])) $selRecebeDebito=" checked";
								if(isset($_infos[$cnt->id][$v->id]['credito'])) $selRecebeCredito=" checked";

							}
						?>
						<div class="box registros" id="js-bandeira-<?php echo $v->id;?>" style="display: none;">
							<h1 class="paciente__titulo1"><?php echo $v->titulo;?></h1>
							
							<dl>
								<dd>
									<label>
										<input type="checkbox" class="js-recebeCredito js-recebeCredito-<?php echo $v->id;?>" data-idBandeira="<?php echo $v->id;?>" name="recebeCredito_<?php echo $v->id;?>" value="1"<?php echo $selRecebeCredito;?> /> Crédito
									</label>
									<label><input type="checkbox" class="js-recebeDebito js-recebeDebito-<?php echo $v->id;?>" name="recebeDebito_<?php echo $v->id;?>" data-idBandeira="<?php echo $v->id;?>" value="1"<?php echo $selRecebeDebito;?> /> Débito</label>
								</dd>
							</dl>

							<fieldset class="js-box-credito-<?php echo $v->id;?>" style="display: none;">
								<legend>Crédito</legend>
								<dl>
									<dt>Qtd. de Parcelas</dt>
									<dd>
										<select name="creditoParcelas_<?php echo $v->id;?>" class="js-parcelas" data-idBandeira="<?php echo $v->id;?>">
											<option value="">-</option>
											<?php
											for($i=1;$i<=24;$i++) {
											?>
											<option value="<?php echo $i;?>"><?php echo $i."x";?></option>
											<?php
											}
											?>
										</select>
										<?php
										if(is_object($cnt)) {
										?>
										<script type="text/javascript">
											$(function(){
												$('select[name=creditoParcelas_<?php echo $v->id;?>]').val(<?php echo count($_infos[$cnt->id][$v->id]['credito']);?>).trigger('change');
											})
										</script>
										<?php	
										}
										?>
									</dd>
								</dl>

								
							<dl>
								<dt>Até quantas parcelas é sem juros</dt>
								<dd>
									<select name="semJuros_<?php echo $v->id;?>" class="js-semJuros js-semJuros-<?php echo $v->id;?>" data-id="<?php echo $v->id;?>">
										<option value="">-</option>
										<?php
										for($i=1;$i<=24;$i++) {
											echo '<option value="'.$i.'"'.($semJuros==$i?" selected":"").'>'.$i.'x</option>';
										}
										?>
									</select>
								</dd>
							</dl>

								<table>
									<tr>
										<th></th>
										<th style="width:50%">Taxa</th>
										<th style="width:50%">Prazo</th>
										<?php /*<th style="width:40%"></th>*/?>
									</tr>
									<?php
									for($i=1;$i<=24;$i++) {
										$parcelaDisplay='none';
										$parcelaTaxa=$parcelaPrazo=$parcelaCobrarCliente='';
										if(is_object($cnt)) {
											//var_dump($_infos[$cnt->id][$v->id]['credito'][$i]);die();
											if(isset($_infos[$cnt->id][$v->id]['credito'][$i])) {

												$parcelaDisplay="";
												$parcelaTaxa=isset($_infos[$cnt->id][$v->id]['credito'][$i]->taxa)?$_infos[$cnt->id][$v->id]['credito'][$i]->taxa:"";
												$parcelaPrazo=isset($_infos[$cnt->id][$v->id]['credito'][$i]->prazo)?$_infos[$cnt->id][$v->id]['credito'][$i]->prazo:"";
												$parcelaCobrarCliente=isset($_infos[$cnt->id][$v->id]['credito'][$i]->cobrarCliente)?$_infos[$cnt->id][$v->id]['credito'][$i]->cobrarCliente:"";


											}
										}
									?>
									<tr class="js-tr-<?php echo $v->id;?>" style="display: <?php echo $parcelaDisplay;?>;">
										<td><?php echo $i;?>x</td>
										<td><input type="text" name="credito_taxa_<?php echo $i."_".$v->id;?>" maxlength="4" class="js-maskFloat" value="<?php echo $parcelaTaxa;?>" /></td>
										<td><input type="text" name="credito_prazo_<?php echo $i."_".$v->id;?>" maxlength="3" class="js-maskNumber" value="<?php echo $parcelaPrazo;?>" /></td>
										<?php /*<td><label><input type="checkbox" name="credito_cobrarCliente_<?php echo $i."_".$v->id;?>" value="1"<?php echo $parcelaCobrarCliente==1?" checked":"";?> /> cobrar cliente</label></td>*/?>
									</tr>
									<?php
									}
									?>
								</table>
							</fieldset>

							<fieldset  class="js-box-debito-<?php echo $v->id;?>" style="display: none;">
								<legend>Débito</legend>
								<?php
								$debitoTaxa=$debitoPrazo=$debitoCobrarCliente='';

								if(is_object($cnt)) {
									if(isset($_infos[$cnt->id][$v->id]['debito'][1])) {
										$debitoTaxa=isset($_infos[$cnt->id][$v->id]['debito'][1]->taxa)?$_infos[$cnt->id][$v->id]['debito'][1]->taxa:"";
										$debitoPrazo=isset($_infos[$cnt->id][$v->id]['debito'][1]->prazo)?$_infos[$cnt->id][$v->id]['debito'][1]->prazo:"";
										$debitoCobrarCliente=isset($_infos[$cnt->id][$v->id]['debito'][1]->cobrarCliente)?$_infos[$cnt->id][$v->id]['debito'][1]->cobrarCliente:"";

									}
								}
								?>
								<div class="colunas2">
									<dl>
										<dt>Taxa</dt>
										<dd><input type="text" name="debito_taxa_<?php echo $v->id;?>" value="<?php echo $debitoTaxa;?>" maxlength="4" /></dd>
									</dl>
									<dl>
										<dt>Prazo</dt>
										<dd><input type="text" name="debito_prazo_<?php echo $v->id;?>" value="<?php echo $debitoPrazo;?>" maxlength="3" /></dd>
									</dl>
								</div>
								<label>
									<input type="checkbox" name="debito_cobrarCliente_<?php echo $v->id;?>" value="1"<?php echo $debitoCobrarCliente?" checked":"";?> /> cobrar cliente
								</label>
							</fieldset>

						</div>
						<?php
						}
						?>
					</div>
				</section>

			</form>
			<script>
				$(function(){
					$('input.money').maskMoney({symbol:'', allowZero:true, showSymbol:true, thousands:'.', decimal:',', symbolStay: true});
				})
			</script>
				
			<?php
			} else {
			
			?>
			<section class="filtros">
				<h1 class="filtros__titulo">Cartões / Operadoras de Cartão</h1>
				<form method="get" class="filtros-form">
					<input type="hidden" name="csv" value="0" />
					<dl class=""> 
						<dt>Busca</dt>
						<dd><input type="text" name="busca" value="<?php echo isset($values['busca'])?$values['busca']:'';?>" /></dd>
					</dl>
					<button type="submit" class="filtros-form__button"><i class="iconify" data-icon="bx-bx-search"></i></button>
				</form>
				<div class="filtros-acoes">
					<a href="<?php echo $_page."?form=1&$url";?>" data-padding="0" class="adicionar tooltip" title="Adicionar"><i class="iconify" data-icon="bx-bx-plus"></i></a>
				</div>
			</section>
			<?php
				
			if(isset($_GET['deleta']) and is_numeric($_GET['deleta']) and $usr->tipo=="admin") {
				$vSQL="lixo='1'";
				$vWHERE="where id='".$_GET['deleta']."'";
				$sql->update($_table,$vSQL,$vWHERE);
				$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='delete',vsql='".addslashes($vSQL)."',vwhere='".addslashes($vWHERE)."',tabela='".$_table."',id_reg='".$_GET['deleta']."'");
				$jsc->jAlert("Registro excluído com sucesso!","sucesso","document.location.href='".$_page."?".$url."'");
				die();
			}
			
			$where="WHERE lixo='0'";
			if(isset($values['busca']) and !empty($values['busca'])) $where.=" and (titulo like '%".utf8_decode($values['busca'])."%')";
		

			$sql->consult($_table,"*",$where." order by titulo asc");
			
			require_once("includes/abaConfiguracoesCartoes.php");
			?>
			
			<section class="grid">
				<div class="box registros">

					<div class="registros-qtd">
						<p class="registros-qtd__item"><?php echo $sql->rows;?> registros</p>
					</div>

					<table class="tablesorter">
						<thead>
							<tr>
								<th>Título</th>								
							</tr>
						</thead>
						<tbody>
						<?php
						while($x=mysqli_fetch_object($sql->mysqry)) {
						?>
						<tr onclick="document.location.href='<?php echo $_page;?>?form=1&edita=<?php echo $x->id."&".$url;?>'">
							<td><?php echo utf8_encode($x->titulo);?></td>
						</tr>
						<?php
						}
						?>
						</tbody>
					</table>
				</div>
			</section>
			
			
			<?php
			}
			?>

</section>

<?php
	include "includes/footer.php";
?>