<?php
	include "includes/header.php";
	include "includes/nav.php";

	$_table=$_p."pacientes_prontuarios";
	require_once("includes/header/headerPacientes.php");

	
	$_usuarios=array();
	$sql->consult($_p."colaboradores","id,nome","");
	while($x=mysqli_fetch_object($sql->mysqry)) $_usuarios[$x->id]=$x;
?>

	<script type="text/javascript">
		var id_paciente = <?php echo $paciente->id;?>; 
	</script>

	<main class="main">
		<div class="main__content content">
			<?php
			# Formulario de Adição/Edição
			if(isset($_GET['form'])) {

				$campos=explode(",","data,texto,id_usuario");

				foreach($campos as $v) $values[$v]='';
				$values['data']=date('d/m/Y H:i');

				$cnt='';
				// busca edicao
				if(isset($_GET['edita']) and is_numeric($_GET['edita'])) {
					$sql->consult($_table,"*","where id='".$_GET['edita']."' and id_paciente=$paciente->id and lixo=0");
					if($sql->rows) {
						$cnt=mysqli_fetch_object($sql->mysqry);
						$values=$adm->values($campos,$cnt);
					} else {
						$jsc->jAlert("Prontuário não encontrado!","erro","document.location.href='$_page?$url'");
						die();
					}
				}

				if(isset($_GET['deleta'])) {
					if(is_object($cnt)) {
						$vSQL="lixo=1";
						$vWHERE="where id=$cnt->id";
						$sql->update($_table,$vSQL,$vWHERE);
						$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='delete',vsql='".addslashes($vSQL)."',vwhere='".addslashes($vWHERE)."',tabela='".$_table."',id_reg='".$cnt->id."'");

						$jsc->go("$_page?$url");
						die();
 					} else {
 						$jsc->jAlert("Prontuário não encontrado","erro","document.location.href='$_page?$url';");
 					}
				}

				// persistencia
				if(isset($_POST['acao']) and $_POST['acao']=="wlib") {

					// monta sql de insert/update
					$vSQL=$adm->vSQL($campos,$_POST);

					// popula $values para persistir nos cmapos
					$values=$adm->values;
					$vSQL.="id_paciente=$paciente->id";


					//echo $vSQL;die();
					$processa=true;

					if($processa===true) {	

						if(is_object($cnt)) {


							$vWHERE="where id='".$cnt->id."'";
							$sql->update($_table,$vSQL,$vWHERE);
							$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='update',vsql='".addslashes($vSQL)."',vwhere='".addslashes($vWHERE)."',tabela='".$_table."',id_reg='".$cnt->id."'");
							$id_reg=$cnt->id;
						} else {
							$sql->add($_table,$vSQL);
							$id_reg=$sql->ulid;
							$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='insert',vsql='".addslashes($vSQL)."',tabela='".$_table."',id_reg='".$sql->ulid."'");
						}

						
						$jsc->jAlert("Informações salvas com sucesso!","sucesso","document.location.href='$_page?form=1&edita=".$id_reg."&".$url."'");
						die();
						
					}
				}
			?>
			<section class="filter">
				
				<div class="filter-group">
					<div class="filter-title">	
						<h1></h1>
					</div>
				</div>

				<?php /*<div class="filter-group">
					<div class="filter-form form">
						<dl>
							<dd><a href="<?php echo $_page."?".$url;?>" class="button"><i class="iconify" data-icon="fluent:arrow-left-24-regular"></i></a></dd>
						</dl>
						<?php
						if(is_object($cnt)) {
						?>
						<dl>
							<dd><a href="<?php $_page."?form=1&edita=$cnt->id&deleta=1";?>" class="button"><i class="iconify" data-icon="fluent:delete-24-regular"></i></a></dd>
						</dl>
						<dl>
							<dd><a href="" class="button"><i class="iconify" data-icon="fluent:print-24-regular"></i></a></dd>
						</dl>
						<?php
						}
						?>
						<dl>
							<dd><a href="javascript://" class="button button_main js-submit"><i class="iconify" data-icon="fluent:checkmark-12-filled"></i><span>Salvar</span></a></dd>
						</dl>
					</div>
				</div>*/?>
			</section>
			<?php
			} 
			# Listagem
			else {
			?>
			<br /><br />
			<?php
			} 
			?>

			<section class="grid">

				<div class="box box-col">

					
					<div class="box-col__inner1">
				<?php

				# Formulario de Adição/Edição
				if(isset($_GET['form'])) {
					if(is_object($cnt)) {
				?>	

					
				<?php
					}
				?>
						<form method="post" class="form formulario-validacao">
							<button style="display:none;"></button>
							<input type="hidden" name="acao" value="wlib" />
							<section class="filter">
								<div class="filter-group">

									
								</div>
								<div class="filter-group">
									<div class="filter-form form">
										<dl>
											<dd><a href="<?php echo $_page."?".$url;?>" class="button"><i class="iconify" data-icon="fluent:arrow-left-24-regular"></i></a></dd>
										</dl>
										<?php
										if(is_object($cnt)) {
										?>
										<dl>
											<dd><a href="<?php echo "$_page?form=1&edita=$cnt->id&deleta=1&$url";?>" class="button js-confirmarDeletar" data-msg="Tem certeza que deseja remover este prontuário?"><i class="iconify" data-icon="fluent:delete-24-regular"></i></a></dd>
										</dl>
										<?php /*<dl>
											<dd><a href="javascript:;" class="button"><i class="iconify" data-icon="fluent:print-24-regular"></i></a></dd>
										</dl>*/?>
										<?php
										}
										?>
										<dl>
											<dd><a href="javascript:;" class="button button_main js-submit"><i class="iconify" data-icon="fluent:checkmark-12-filled"></i><span>Salvar</span></a></dd>
										</dl>
									</div>
								</div>
							</section>

							<div class="js-tabs js-dadospessoais">


								<fieldset>
									<legend>Dados de Contato</legend>

									<div class="colunas5">
										<dl>
											<dt>Data</dt>
											<dd><input type="text" name="data" class="datepicker datahora" value="<?php echo $values['data'];?>"<?php echo is_object($cnt)?" disabled":"";?> /></dd>
										</dl>
										<?php
										/*if(is_object($cnt)) {
											$autor='Desconhecido';
											$sql->consult($_p."colaboradores","nome","where id=$cnt->id_usuario");
											if($sql->rows) {
												$a=mysqli_fetch_object($sql->mysqry);
												$autor=utf8_encode($a->nome);
											}
										?>
										<dl class="dl4">
											<dt>Autor</dt>
											<dd>
												<input type="text" value="<?php echo $autor;?>">
											</dd>
										</dl>
										<?php	
										} else {*/
										?>
										<dl class="dl2">
											<dt>Profissional</dt>
											<dd>
												<select name="id_usuario" class="obg chosen">
													<option value=""></option>
													<?php
													foreach($_usuarios as $x) {
														echo '<option value="'.$x->id.'"'.($values['id_usuario']==$x->id?' selected':'').'>'.utf8_encode($x->nome).'</option>';
													}
													?>
												</select>
											</dd>
										</dl>
										<?php	
										//}
										?>
									</div>


									<textarea name="texto" style="height: 300px;"><?php echo $values['texto'];?></textarea>

									
								</fieldset>

								
							</div><!-- .js-dadospessoais -->

						</form>

				<?php
				}

				# Listagem
				else {


					$values=$adm->get($_GET);
				?>	

						<section class="filter">
							<div class="filter-group">
								<div class="filter-form form">
									<dl>
										<dd><a href="<?php echo $_page."?form=1&id_paciente=".$paciente->id;?>" class="button button_main"><i class="iconify" data-icon="fluent:add-circle-24-regular"></i> <span>Novo Prontuário</span></a></dd>
									</dl>
								</div>
							</div>
							<?php /*<form method="get" class="js-filtro">
								<div class="filter-group">
									<div class="filter-form form">
										<dl>
											<dd class="form-comp form-comp_pos"><input type="text" name="busca" placeholder="Buscar..." value="<?php echo isset($values['busca'])?($values['busca']):"";?>" /><a href="javascript:;" onclick="$('form.js-filtro').submit();"><i class="iconify" data-icon="fluent:search-12-filled"></i></a></dd>
										</dl>
									</div>					
								</div>
							</form>*/?>
						</section>
						
						
						<div class="list1">
							<?php
							$where="where id_paciente=$paciente->id and lixo=0";
							if(isset($values['busca']) and !empty($values['busca'])) {
								$where.=" and nome like '%".$values['busca']."%'";
							}
							//$sql->consult($_table,"*",$where." order by nome asc");

							$sql->consultPagMto2($_table,"*",10,$where." order by data desc","",15,"pagina",$_page."?".$url."&pagina=");
							if($sql->rows==0) {
								$msg="Nenhum prontuário cadastrado";

								echo "<center>$msg</center>";
							} else {
							?>
							<table>
								<?php
								while($x=mysqli_fetch_object($sql->mysqry)) {
									$autor=isset($_usuarios[$x->id_usuario])?utf8_encode($_usuarios[$x->id_usuario]->nome):"Desconhecido";
								?>
								<tr onclick="document.location.href='<?php echo $_page."?id_paciente=".$paciente->id."&form=1&edita=$x->id";?>';">
									<td style="width:150px;"><h1><strong><?php echo date('d/m/Y H:i',strtotime($x->data));?></strong></h1> <?php echo "por $autor";?></td>
									<td><?php echo substr(utf8_encode($x->texto),0,150)."...";?></td>
								</tr>
								<?php
								}
								?>								
							</table>
							<?php
							}
							?>		
						</div>
						<?php
						if(isset($sql->myspaginacao) and !empty($sql->myspaginacao)) {
						?>
						<div class="pagination">						
							<?php echo $sql->myspaginacao;?>
						</div>
						<?php
						}
						?>
				<?php
				}
				?>
				
					</div>
					
					
				</div>

			</section>
		
		</div>
	</main>

<?php 
			


	include "includes/footer.php";
?>	