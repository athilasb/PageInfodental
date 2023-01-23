<?php
	$title="";
	include "includes/header.php";
	include "includes/nav.php";

	if($usr->tipo!="admin" and !in_array("parametros",$_usuariosPermissoes)) {
		$jsc->jAlert("Você não tem permissão para acessar esta área!","erro","document.location.href='dashboard.php'");
		die();
	}

	$values=$adm->get($_GET);

	$_laboratorios=array();
	$sql->consult($_p."parametros_fornecedores","*","where lixo=0 order by nome asc, razao_social");
	while($x=mysqli_fetch_object($sql->mysqry)) $_laboratorios[$x->id]=$x;

	$_table=$_p."parametros_servicosdelaboratorio";
	$_page=basename($_SERVER['PHP_SELF']);

	$_bancos=array();
	$sql->consult($_p."parametros_bancos","*","where lixo=0 order by titulo asc");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_bancos[$x->id]=$x;
	}

	$_regioes=array();
	$sql->consult($_p."parametros_procedimentos_regioes","*","where lixo=0 order by titulo asc");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_regioes[$x->id]=$x;
	}
?>

<section class="content">

	<?php
	require_once("includes/abaConfiguracoes.php");
	?>

	
	<?php
	if(isset($_GET['form'])) {
		$cnt='';
		$campos=explode(",","titulo,id_regiao");
		
		foreach($campos as $v) $values[$v]='';
		$values['tipo_pessoa']='pf';
		
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

		if(isset($_POST['acao']) and $_POST['acao']=="wlib") {
			$vSQL=$adm->vSQL($campos,$_POST);
			$values=$adm->values;
			$processa=true;

			$laboratoriosJSON=utf8_decode($_POST['laboratorios']);
			$vSQL.="laboratorios='".$laboratoriosJSON."',";
			//echo $vSQL;die();
			if($processa===true) {	
				if(is_object($cnt)) {
					$vSQL=substr($vSQL,0,strlen($vSQL)-1);
					$vWHERE="where id='".$cnt->id."'";
					$sql->update($_table,$vSQL,$vWHERE);
					$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='update',vsql='".addslashes($vSQL)."',vwhere='".addslashes($vWHERE)."',tabela='".$_table."',id_reg='".$cnt->id."'");
					$id_reg=$cnt->id;
				} else {
					$vSQL=substr($vSQL,0,strlen($vSQL)-1);
					//echo $vSQL;die();
					$sql->add($_table,$vSQL);
					$id_reg=$sql->ulid;
					$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='insert',vsql='".addslashes($vSQL)."',tabela='".$_table."',id_reg='".$sql->ulid."'");


					$id_procedimento=$id_reg;
					
				}

				$sql->update($_table."_laboratorios","lixo=1","where id_fornecedor=$id_reg");

				if(isset($_POST['laboratorios']) and !empty($_POST['laboratorios'])) {
					$labs=json_decode($_POST['laboratorios']);

					foreach($labs as $v) {

						$vsql="id_fornecedor='$v->id_laboratorio',
								id_servicodelaboratorio='$id_reg',
								valor='".valor($v->valor)."',
								lixo=0";

						$sql->consult($_table."_laboratorios","*","where id_fornecedor=$v->id_laboratorio and id_servicodelaboratorio=$id_reg");
						if($sql->rows) {
							$x=mysqli_fetch_object($sql->mysqry);
							$sql->update($_table."_laboratorios",$vsql,"where id=$x->id");
						} else {
							$sql->add($_table."_laboratorios",$vsql);
						}
					}
				}

				$msgErro='';
				if(!empty($msgErro)) {
					$jsc->jAlert($msgErro,"erro","");
				} else {
					$jsc->jAlert("Informações salvas com sucesso!","sucesso","document.location.href='".$_page."?".$url."'");
					die();
				}
			}
		}
		?>

		<div class="filtros">
			<h1 class="filtros__titulo">Serviços de Laboratório</h1>
			<div class="filtros-acoes">
				<a href="<?php echo $_page."?".$url;?>"><i class="iconify" data-icon="bx-bx-left-arrow-alt"></i></a>
				<?php
				if(is_object($cnt)) {
				?>		
				<a data-fancybox data-type="ajax" data-src="ajax/log.php?table=<?php echo $_table;?>&id=<?php echo $cnt->id;?>" href="javascript:;"><i class="iconify" data-icon="bx-bx-history"></i></a>
				<?php	
				}
				?>
				<a href="javascript:;" class="principal btn-submit"><i class="iconify" data-icon="bx-bx-check"></i></a>
				<?php if(is_object($cnt) and $usr->tipo=="admin") { ?>
				<a class="sec js-deletar" href="<?php echo $_page;?>?deleta=<?php echo $cnt->id."&".$url;?>"><i class="iconify" data-icon="bx-bx-trash"></i></a>
				<?php } ?>
			</div>
		</div>
		
		<section class="grid">
			<div class="box">				
				<form method="post" class="form formulario-validacao"  autocomplete="off" enctype="multipart/form-data">
					
					<input type="hidden" name="acao" value="wlib" />
					<fieldset>
						<legend>Dados do Serviço</legend>

						
						<div class="colunas4">
							<dl class="dl2">
								<dt>Nome do Serviço</dt>
								<dd>
									<input type="text" name="titulo" value="<?php echo $values['titulo'];?>"  class="obg" />
								</dd>
							</dl>
							<dl>
								<dt>Região</dt>
								<dd>
									<select name="id_regiao">
										<option value="">-</option>
										<?php
										foreach($_regioes as $v) {
											echo  '<option value="'.$v->id.'"'.($v->id==$values['id_regiao']?' selected':'').'>'.utf8_encode($v->titulo).'</option>';
										}
										?>
									</select>
								</dd>
							</dl>
						</div>
					</fieldset>

					<fieldset>
						<legend>Serviços por Laboratório</legend>

						<textarea name="laboratorios" style="display: none"><?php echo isset($values['laboratorios'])?$values['laboratorios']:'';?></textarea>
						<script>
							var laboratorios = [];

							const laboratoriosRemover = (index) => {

							//	alert(laboratorios[index].id_laboratorio);
								$('select.js-id_laboratorio').find(`option[value=${laboratorios[index].id_laboratorio}]`).prop('disabled',false);


								laboratorios.splice(index,1);
								laboratoriosListar();
							};
							const laboratoriosListar = () => {
								$('table.js-laboratorios .js-tr').remove();

								html = `<tr class="js-tr">
											<td class="js-tr-laboratorio"></td>
											<td class="js-tr-valor"></td>
											<td>
												<a href="javascript:;" class="js-tr-deleta registros__acao registros__acao_sec"><i class="iconify" data-icon="bx:bxs-trash"></i></a>
											</td>
										</tr>`;

								laboratorios.forEach(x => {

									$('select.js-id_laboratorio').find(`option[value=${x.id_laboratorio}]`).prop('disabled',true);
									$('table.js-laboratorios').append(html);

									$('table.js-laboratorios .js-tr-laboratorio:last').html(x.laboratorio);
									$('table.js-laboratorios .js-tr-valor:last').html(x.valor);

									$('table.js-laboratorios .js-tr-deleta:last').click(function() {
										let index = $(this).index('table.js-laboratorios .js-tr-deleta');
										swal({   title: "Atenção",   text: "Você tem certeza que deseja remover este registro?",   type: "warning",   showCancelButton: true,   confirmButtonColor: "#DD6B55",   confirmButtonText: "Sim!",   cancelButtonText: "Não",   closeOnConfirm: false,   closeOnCancel: false }, function(isConfirm){   if (isConfirm) {  laboratoriosRemover(index); swal.close();   } else {   swal.close();   } });
									});
								});

								let json = JSON.stringify(laboratorios);
								$('[name=laboratorios]').val(json);
							}

							$(function(){
								$('input.money').maskMoney({symbol:'', allowZero:false, showSymbol:true, thousands:'.', decimal:',', symbolStay: true});
								<?php
								if(is_object($cnt) and !empty($cnt->laboratorios)) {
									echo "laboratorios=JSON.parse('".utf8_encode($cnt->laboratorios)."');";
									echo "laboratoriosListar();";
								} 
								?>
								

		      					$('.js-btn-add').click(function(){
		      						let laboratorio = $('select.js-id_laboratorio option:selected').text();
		      						let id_laboratorio = $('select.js-id_laboratorio').val();
		      						let valor = $('input.js-valor').val();
		      						
		      						if(id_laboratorio.length==0) {
		      							swal({title: "Erro!", text: "Selecione o Laboratório!", type:"error", confirmButtonColor: "#424242"});
		      							$('select.js-id_laboratorio').addClass('erro');
		      						} else if(valor.length==0) {
		      							swal({title: "Erro!", text: "Defina o valor", type:"error", confirmButtonColor: "#424242"});
		      							$('input.js-valor').addClass('erro');
		      						} else {
		      							let item = {};
		      							item.laboratorio = laboratorio;
		      							item.id_laboratorio = id_laboratorio;
		      							item.valor = valor;
		      							
		      							laboratorios.push(item);
		      							laboratoriosListar();
		      							$('select.js-id_laboratorio option:selected').prop('selected',false);
		      							$('input.js-valor').val('');
		      						}

		      					});
							});
						</script>	
						<div class="colunas4">
							<dl>
								<dt>Laboratório</dt>
								<dd>
									<select class="js-id_laboratorio">
										<option value="">-</option>
										<?php
										foreach($_laboratorios as $p) {
											$laboratorioTitulo='';
											if($p->tipo_pessoa=='PJ') $laboratorioTitulo=utf8_encode($p->razao_social);
											else $laboratorioTitulo=utf8_encode($p->nome);
											echo '<option value="'.$p->id.'">'.$laboratorioTitulo.'</option>';
										}
										?>
									</select>
								</dd>
							</dl>
							<dl>
								<dt>Valor</dt>
								<dd><input type="text" class="js-valor money" /></dd>
							</dl>
							<dl>
								<dt>&nbsp;</dt>
								<dd><a href="javascript:;" class="button button__sec js-btn-add"><i class="iconify" data-icon="bx-bx-check"></i></a></dd>
							</dl>	
						</div>
						<div class="registros">
							<table class="js-laboratorios">
								<tr>
									<th>Laboratório</th>
									<th style="width:150px;">Valor</th>
									<th style="width:20px;"></th>
								</tr>

							</table>
						</div>
					</fieldset>
				</form>
			</div>
		</section>
					
		<?php
		} else {
				
					
			if(isset($_GET['deleta']) and is_numeric($_GET['deleta']) and $usr->tipo=="admin") {
				$vSQL="lixo='1'";
				$vWHERE="where id='".$_GET['deleta']."'";
				$sql->update($_table,$vSQL,$vWHERE);
				$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='delete',vsql='".addslashes($vSQL)."',vwhere='".addslashes($vWHERE)."',tabela='".$_table."',id_reg='".$_GET['deleta']."'");
				$jsc->jAlert("Registro excluído com sucesso!","sucesso","document.location.href='".$_page."?".$url."'");
				die();
			}
			
			$where="WHERE lixo='0'";

			if(isset($values['id_especialidade']) and is_numeric($values['id_especialidade'])) $where.=" and id_especialidade='".$values['id_especialidade']."'";
			if(isset($values['id_subcategoria']) and is_numeric($values['id_subcategoria'])) $where.=" and id_subcategoria='".$values['id_subcategoria']."'";
			
			$sql->consult($_table,"*",$where." order by id");
		
		?>
		<section class="filtros">
			<h1 class="filtros__titulo">Serviços de Laboratório</h1>
			<?php /*
			<form method="get" class="filtros-form">
				<input type="hidden" name="csv" value="0" />
				<div class="colunas4">
					<dl class="">
						<dt>Laborat</dt>
						<dd>
							<select name="id_especialidade">
								<option value="">-</option>
								<?php
								foreach($_especialidades as $v) echo '<option value="'.$v->id.'"'.($values['id_especialidade']==$v->id?' selected':'').'>'.utf8_encode($v->titulo).'</option>';
								?>
							</select>
						</dd>
					</dl>
					<dl>		
						<dt>&nbsp;</dt>			
						<dd><button type="submit" class="button button__sec"><i class="iconify" data-icon="bx-bx-search" data-inline="false"></i></button></dd>
					</dl>
				</div>
			</form>
			*/ ?>
			<div class="filtros-acoes">
				<a href="<?php echo $_page."?form=1&$url";?>" data-padding="0" class="adicionar tooltip" title="Adicionar"><i class="iconify" data-icon="bx-bx-plus"></i></a>
			</div>
		</section>

		<section class="grid">
			<div class="box">

				<div class="registros-qtd">
					<p class="registros-qtd__item"><?php echo $sql->rows;?> registros</p>
				</div>
				<div class="registros">
					
					<table class="tablesorter">
						<thead>
							<tr>
								<th>Título</th>
								<th>Região</th>
							</tr>
						</thead>
						<tbody>
						<?php
						while($x=mysqli_fetch_object($sql->mysqry)) {
						?>
						<tr onclick="document.location.href='<?php echo $_page;?>?form=1&edita=<?php echo $x->id."&".$url;?>'">
							<td><?php echo utf8_encode($x->titulo);?></td>
							<td><?php echo isset($_regioes[$x->id_regiao])?utf8_encode($_regioes[$x->id_regiao]->titulo):'-';?></td>							
						</tr>
						<?php
						}
						?>
						</tbody>
					</table>
				</div>
			</div>
		</section>	
	<?php
	}
	?>
	</section>
</section>

<?php
	include "includes/footer.php";
?>