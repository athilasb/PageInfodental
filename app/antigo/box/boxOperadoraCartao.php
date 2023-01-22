<?php
	require_once("../lib/conf.php");
	$dir="../";
	require_once("../usuarios/checa.php");

	if(isset($_POST['ajax'])) {
		$sql = new Mysql();
		$_bandeirasDeCartao=array();
		$sql->consult($_p."parametros_cartoes_bandeiras","*","where lixo=0 order by titulo");
		while ($x=mysqli_fetch_object($sql->mysqry)) {
			$_bandeirasDeCartao[$x->id]=$x;
		}

		$rtn = array();
		if($_POST['ajax']=="operadoraPersistir") {

			$operadora='';
			if(isset($_POST['id_operadora']) and is_numeric($_POST['id_operadora'])) {
				$sql->consult($_p."parametros_cartoes_operadoras","*","where id='".$_POST['id_operadora']."'");
				if($sql->rows) {
					$operadora=mysqli_fetch_object($sql->mysqry);
				}
			}

			$vSQL="titulo='".addslashes(utf8_decode($_POST['titulo']))."',id_banco='".addslashes(utf8_decode($_POST['id_banco']))."'";

			if(is_object($operadora)) {
				$vWHERE="where id=$operadora->id";
				$sql->update($_p."parametros_cartoes_operadoras",$vSQL,$vWHERE);
				$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='update',vsql='".addslashes($vSQL)."',vwhere='".addslashes($vWHERE)."',tabela='".$_p."parametros_cartoes_bandeiras',id_reg='".$operadora->id."'");
				$id_operadora=$operadora->id;
			} else {
				$sql->add($_p."parametros_cartoes_operadoras",$vSQL);
				$id_operadora=$sql->ulid;
				$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='insert',vsql='".addslashes($vSQL)."',tabela='".$_p."parametros_cartoes_bandeiras"."',id_reg='".$sql->ulid."'");
			}

			$sql->update($_p."parametros_cartoes_taxas","lixo=1","where id_operadora=$id_operadora");

			$vsqlPeT=array();
			foreach($_bandeirasDeCartao as $b) {

				if(isset($_POST['bandeiras']) and is_array($_POST['bandeiras']) and in_array($b->id,$_POST['bandeiras'])) {

					if(isset($_POST['recebeCredito_'.$b->id])) {

						$semJuros=isset($_POST['semJuros_'.$b->id])?$_POST['semJuros_'.$b->id]:0;

						for($i=1;$i<=24;$i++) {

							if($_POST['creditoParcelas_'.$b->id]<$i) {
								continue;
							}
							//if($semJuros<$i) continue;

							$vsqlPeT[]=array("id_operadora"=>$id_operadora,
											"id_bandeira"=>$b->id,
											"operacao"=>"credito",
											"parcela"=>$i,
											"taxa"=>valor($_POST['credito_taxa_'.$i.'_'.$b->id]),
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
						$sql->update($_p."parametros_cartoes_taxas_semjuros","semjuros='".$semJuros."'","where id=$taxaSemjuros->id");
					} 

					if(isset($_POST['recebeDebito_'.$b->id])) {
						$vsqlPeT[]=array("id_operadora"=>$id_operadora,
											"id_bandeira"=>$b->id,
											"operacao"=>"debito",
											"parcela"=>1,
											"taxa"=>valor($_POST['debito_taxa_'.$b->id]),
											"cobrarCliente"=>isset($_POST['debito_cobrarCliente_'.$b->id])?$_POST['debito_cobrarCliente_'.$b->id]:0,
											"prazo"=>$_POST['debito_prazo_'.$b->id]);
					}
				}
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
			$rtn=array('success'=>true);
		} else if($_POST['ajax']=="operadoraRemover") {
			if(isset($_POST['id_operadora']) and is_numeric($_POST['id_operadora'])) {
				$sql->consult($_p."parametros_cartoes_operadoras","*","where id='".$_POST['id_operadora']."'");
				//echo "where id='".$_POST['id_operadora']."' ->$sql->rows";
				if($sql->rows) {
					$operadora=mysqli_fetch_object($sql->mysqry);
				}
			}


			if(isset($operadora) and is_object($operadora)) {
				$vSQL="lixo=1";
				$vWHERE="where id=$operadora->id";
				$sql->update($_p."parametros_cartoes_operadoras",$vSQL,$vWHERE);

				$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='update',vwhere='".addslashes($vWHERE)."',vsql='".addslashes($vSQL)."',tabela='".$_p."parametros_cartoes_bandeiras',id_reg='".$operadora->id."'");

				$rtn=array('success'=>true);

			} else {
				$rtn=array('success'=>false,'error'=>'Operadora não encontrada');
			}
		}

		header("Content-type: application/json");
		echo json_encode($rtn);
		die();
	}

	$campos=explode(",","titulo,id_banco");

	$_bandeirasDeCartao=array();
	$sql->consult($_p."parametros_cartoes_bandeiras","*","where lixo=0 order by titulo");
	while ($x=mysqli_fetch_object($sql->mysqry)) {
		$_bandeirasDeCartao[$x->id]=$x;
	}

	$_bancos=array();
	$sql->consult($_p."financeiro_bancosecontas","*","where lixo=0 order by titulo");
	while ($x=mysqli_fetch_object($sql->mysqry)) {
		$_bancos[$x->id]=$x;
	}
		
	foreach($campos as $v) $values[$v]='';

	$jsc = new Js();
	$operadora='';
	if(isset($_GET['id_operadora']) and is_numeric($_GET['id_operadora'])) {
		$sql->consult($_p."parametros_cartoes_operadoras","*","where id='".$_GET['id_operadora']."'");
		if($sql->rows) {
			$operadora=mysqli_fetch_object($sql->mysqry);

			foreach($campos as $v) {
				$values[$v]=utf8_encode($operadora->$v);
			}
		}
	}
?>
<script>
	var id_operadora = '<?php echo is_object($operadora)?$operadora->id:'';?>';
	$(function(){
		$('.chosen').chosen({hide_results_on_select:false,allow_single_deselect:true});
		$('.js-remover').click(function(){

			swal({   title: "Atenção",   text: "Você tem certeza que deseja remover este registro?",   type: "warning",   showCancelButton: true,   confirmButtonColor: "#DD6B55",   confirmButtonText: "Sim!",   cancelButtonText: "Não",   closeOnConfirm: false,   closeOnCancel: false }, function(isConfirm){   
				if (isConfirm) { 

					let data = `ajax=operadoraRemover&id_operadora=${id_operadora}`;   
					$.ajax({
						type:"POST",
						url:'box/boxOperadoraCartao.php',
						data:data,
						success:function(rtn){
							swal.close();  
							if(rtn.success) {
								document.location.reload();
							} else if(rtn.error) {
								swal({title: "Erro!", text: rtn.error, type:"error", confirmButtonColor: "#424242"});
							} else {
								swal({title: "Erro!", text: "operadora não removida. Por favor tente novamente!", type:"error", confirmButtonColor: "#424242"});
							}
						},
						error:function(){
							swal.close();  
							swal({title: "Erro!", text: "operadora não removida. Por favor tente novamente!", type:"error", confirmButtonColor: "#424242"});
						}
					})
				} else {   
					swal.close();   
				} 
			});
		});
		$('.js-salvar').click(function(){

			let erro=false;
			$('form .obg').each(function(index,elem){
				if($(this).attr('name')!==undefined && $(this).val().length==0) {
					$(elem).addClass('erro');
					erro=true;
				}
			});

			if(erro===true) {
				swal({title: "Erro!", text: "Complete os campos destacados", type:"error", confirmButtonColor: "#424242"});
				
			} else {
				
				let campos = $('form.js-form-operadora').serialize();
				let data = `ajax=operadoraPersistir&id_operadora=${id_operadora}&${campos}`;

				$.ajax({
					type:'POST',
					url:'box/boxOperadoraCartao.php',
					data:data,
					success:function(rtn) {
						if(rtn.success) {
							document.location.href='pg_configuracao_cartoes.php';
						} else if(rtn.error) {
							swal({title: "Erro!", text: rtn.error, type:"error", confirmButtonColor: "#424242"});
						} else {
							swal({title: "Erro!", text: "Operadora não salva. Por favor tente novamente!", type:"error", confirmButtonColor: "#424242"});
						}
					},
					error:function(){
						swal({title: "Erro!", text: "Operadora não salva. Por favor tente novamente!", type:"error", confirmButtonColor: "#424242"});
					}
				})

			}
			return false;
		});
	});
</script>
<section class="modal" style="height:auto; width:950px;">

	<header class="modal-header">
		<div class="filtros">

			<?php
				if(empty($operadora)) {
			?>
			<h1 class="filtros__titulo"></h1>
			<div class="filtros-acoes filter-button">
				<a href="javascript:;" class="azul js-salvar"><i class="iconify" data-icon="bx-bx-check"></i><span>salvar</span></a>
			</div>
			<?php
				} else {
			?>
			<h1 class="filtros__titulo">Editar</h1>
			
			<div class="filtros-acoes filter-button">
				<a href="javascript:;" class="js-remover"><i class="iconify" data-icon="bx-bx-trash"></i></a>
				<a href="javascript:;" class="azul js-salvar"><i class="iconify" data-icon="bx-bx-check"></i><span>salvar</span></a>
			</div>
			<?php
				}
			?>
		
		</div>
	</header>
	
	<article class="modal-conteudo">

		<script type="text/javascript">
			function atualizaCards() {
				$('select.js-bandeira option').each(function(index,el){
					let idOperadora = $(el).val();
					if($(el).prop('selected')===true) {
						$(`#js-bandeira-${idOperadora}`).show();
					} else {
						$(`#js-bandeira-${idOperadora}`).hide();
					}
				})
			}

			function atualizaRecebimentos() {

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
					let id = $('select.js-parcelas').val();
				});
			})
		</script>
		<?php
			$_bandeirasDestaOperadora=array();
			$_infos=array();
			if(is_object($operadora)) {
				$sql->consult($_p."parametros_cartoes_taxas","*","where id_operadora=$operadora->id and lixo=0");
				if($sql->rows) {
					while($x=mysqli_fetch_object($sql->mysqry)) {
						$_infos[$operadora->id][$x->id_bandeira][$x->operacao][$x->parcela]=$x;
						$_bandeirasDestaOperadora[$x->id_bandeira]=$x->id_bandeira;

					}
				}
			}
		?>
		<form method="post" class="form js-form-operadora">
			
			<fieldset>
				<legend>Informações</legend>
					<dl>
						<dt>Operadora</dt>
						<dd>
							<input type="text" name="titulo" value="<?php echo $values['titulo'];?>"  class="obg" />
						</dd>
					</dl>
					<dl>
						<dt>Banco/Conta</dt>
						<dd>
							<select name="id_banco" class="obg">
								<option value="">-</option>
							<?php
							foreach($_bancos as $v) {
								$sel=$v->id==$values['id_banco']?" selected":"";
								echo '<option value="'.$v->id.'"'.$sel.'>'.utf8_encode($v->titulo).'</option>';
							}
							?>
							</select>
						</dd>
					</dl>
					<dl>
						<dt>Bandeiras de Cartão</dt>
						<dd>
							<select name="bandeiras[]" class="chosen js-bandeira" data-placeholder="Bandeiras..." multiple>
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
			</fieldset>
				
			<div class="grid grid_3">
				<?php
				foreach($_bandeirasDeCartao as $v) {
					$selRecebeDebito=$selRecebeCredito='';

					if(is_object($operadora)) {
						$taxaSemjuros='';

						$sql->consult($_p."parametros_cartoes_taxas_semjuros","semjuros","where id_operadora=$operadora->id and id_bandeira=$v->id and lixo=0");
						$semJuros='';
						if($sql->rows) {
							$taxaSemjuros=mysqli_fetch_object($sql->mysqry);
							$semJuros=$taxaSemjuros->semjuros;
						}

						if(isset($_infos[$operadora->id][$v->id]['debito'])) $selRecebeDebito=" checked";
						if(isset($_infos[$operadora->id][$v->id]['credito'])) $selRecebeCredito=" checked";
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
								<th style="width:50%">Taxa (%)</th>
								<th style="width:50%">Prazo</th>
								<?php /*<th style="width:40%"></th>*/?>
							</tr>
							<?php
							for($i=1;$i<=24;$i++) {
								$parcelaDisplay='none';
								$parcelaTaxa=$parcelaPrazo=$parcelaCobrarCliente='';
								if(is_object($operadora)) {
									//var_dump($_infos[$operadora->id][$v->id]['credito'][$i]);die();
									if(isset($_infos[$operadora->id][$v->id]['credito'][$i])) {

										$parcelaDisplay="";
										$parcelaTaxa=isset($_infos[$operadora->id][$v->id]['credito'][$i]->taxa)?$_infos[$operadora->id][$v->id]['credito'][$i]->taxa:"";
										$parcelaPrazo=isset($_infos[$operadora->id][$v->id]['credito'][$i]->prazo)?$_infos[$operadora->id][$v->id]['credito'][$i]->prazo:"";
										$parcelaCobrarCliente=isset($_infos[$operadora->id][$v->id]['credito'][$i]->cobrarCliente)?$_infos[$operadora->id][$v->id]['credito'][$i]->cobrarCliente:"";


									}
								}
							?>
							<tr class="js-tr-<?php echo $v->id;?>" style="display: <?php echo $parcelaDisplay;?>;">
								<td><?php echo $i;?>x</td>
								<td><input type="text" name="credito_taxa_<?php echo $i."_".$v->id;?>" maxlength="4" class="js-maskFloat" value="<?php echo is_numeric($parcelaTaxa)?number_format($parcelaTaxa,2,",",""):'';?>" /></td>
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

						if(is_object($operadora)) {
							if(isset($_infos[$operadora->id][$v->id]['debito'][1])) {
								$debitoTaxa=isset($_infos[$operadora->id][$v->id]['debito'][1]->taxa)?$_infos[$operadora->id][$v->id]['debito'][1]->taxa:"";
								$debitoPrazo=isset($_infos[$operadora->id][$v->id]['debito'][1]->prazo)?$_infos[$operadora->id][$v->id]['debito'][1]->prazo:"";
								$debitoCobrarCliente=isset($_infos[$operadora->id][$v->id]['debito'][1]->cobrarCliente)?$_infos[$operadora->id][$v->id]['debito'][1]->cobrarCliente:"";

							}
						}
						?>
						<div class="colunas2">
							<dl>
								<dt>Taxa (%)</dt>
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
				
		</form>
	</article>

</section>