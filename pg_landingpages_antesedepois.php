<?php
	include "includes/header.php";
	include "includes/nav.php";

	$_table=$_p."landingpage_antesedepois";
	$_page=basename($_SERVER['PHP_SELF']);
	$_dirAntes="arqs/landingpages/antesedepois/antes/";
	$_dirDepois="arqs/landingpages/antesedepois/depois/";
	$_width=800;
	$_height='';

	$landingpage=$cnt='';
	if(isset($_GET['id_landingpage']) and is_numeric($_GET['id_landingpage'])) {
		$sql->consult($_p."landingpage_temas","*","where id='".$_GET['id_landingpage']."'");
		if($sql->rows) {
			$landingpage=mysqli_fetch_object($sql->mysqry);
			$cnt=$landingpage;

		}
	}

	if(empty($landingpage)) {
		$jsc->jAlert("Landing Page não encontrada!","erro","document.location.href='pg_landingpages.php'");
		die();
	}

	if(isset($_GET['form'])) {
		$cnt='';
		$campos=explode(",","descricao,id_tema");
		foreach($campos as $v) $values[$v]='';
		
		if(isset($_GET['id_tema']) and is_numeric($_GET['id_tema'])) $values['id_tema']=$_GET['id_tema'];
		
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

			if($processa===true) {	
			
				if(is_object($cnt)) {
					$vSQL.="id_alteracao=$usr->id,alteracao_data=now()";
					$vWHERE="where id='".$cnt->id."'";
					$sql->update($_table,$vSQL,$vWHERE);
					$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='update',vsql='".addslashes($vSQL)."',vwhere='".addslashes($vWHERE)."',tabela='".$_table."',id_reg='".$cnt->id."'");
					$id_reg=$cnt->id;
				} else {
					$sql->add($_table,$vSQL."data=now(),id_usuario='".$usr->id."'");
					$id_reg=$sql->ulid;
					$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='insert',vsql='".addslashes($vSQL)."',tabela='".$_table."',id_reg='".$sql->ulid."'");
				}

				$msgErro='';
				if(isset($_FILES['foto_antes']) and !empty($_FILES['foto_antes']['tmp_name'])) {
					$up=new Uploader();
					$up->uploadCorta("Foto Antes",$_FILES['foto_antes'],"",5242880*2,$_width,'',$_dirAntes,$id_reg);

					if($up->erro) {
						$msgErro=$up->resul;
					} else {
						$ext=$up->ext;
						$vSQL="foto_antes='".$ext."'";
						$vWHERE="where id='".$id_reg."'";
						$sql->update($_table,$vSQL,$vWHERE);
						$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='update',vsql='".addslashes($vSQL)."',tabela='".$_table."',id_reg='".$id_reg."'");
					}
				}
				if(empty($msgErro)) {
					if(isset($_FILES['foto_antes']) and !empty($_FILES['foto_antes']['tmp_name'])) {
						$up=new Uploader();
						$up->uploadCorta("Foto Depois",$_FILES['foto_depois'],"",5242880*2,$_width,'',$_dirDepois,$id_reg);
						if($up->erro) {
							$msgErro=$up->resul;
						} else {
							$ext=$up->ext;
							$vSQL="foto_depois='".$ext."'";
							$vWHERE="where id='".$id_reg."'";
							$sql->update($_table,$vSQL,$vWHERE);
							$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='update',vsql='".addslashes($vSQL)."',tabela='".$_table."',id_reg='".$id_reg."'");
						}
					}
				}
				if(!empty($msgErro)) {
					$jsc->jAlert($msgErro,"erro","");
				} else {
					$jsc->jAlert("Informações salvas com sucesso!","sucesso","document.location.href='".$_page."?form=1&edita=".$id_reg."&".$url."'");
					die();
				}
			}
		}
	}
?>
	<section class="content">
		
		<?php
		require_once("includes/abaLandingPage.php");
		?>

		<?php
			if(isset($_GET['form'])) {
		?>
		<form method="post" class="form formulario-validacao"  autocomplete="off" enctype="multipart/form-data">
			<input type="hidden" name="acao" value="wlib" />
			<input type="hidden" name="id_tema" value="<?php echo $landingpage->id;?>" />		

			<section class="grid" style="padding:1rem;">
				<div class="box">

					<div class="filter">

						<div class="filter-group">
							<div class="filter-button">
								<a href="javascript:history.back(-1);"><i class="iconify" data-icon="bx-bx-left-arrow-alt"></i></a>
							</div>
						</div>

						<div class="filter-group filter-group_right">
							<div class="filter-button">
								<?php if(is_object($cnt)){?><a href="?deletaLandingPage=<?php echo $landingpage->id."&".$url;?>" class="js-deletar"><i class="iconify" data-icon="bx-bx-trash"></i></a><?php }?>
								<a href="javascript:;" class="azul  btn-submit"><i class="iconify" data-icon="bx-bx-check"></i><span>salvar</span></a>
							</div>
						</div>

					</div>

					<dl>
						<dt>Descrição</dt>
						<dd><input type="text" name="descricao" class="noupper" value="<?php echo $values['descricao'];?>" /></dd>
					</dl>

					<?php
					if(is_object($cnt)) {
						$ft=$_dirAntes.$cnt->id.".".$cnt->foto_antes;
						if(file_exists($ft)) {

						
					?>
					<dl>
						<dd><a href="<?php echo $ft;?>" data-fancybox><img src="<?php echo $ft;?>" width="200" style="border: solid 1px #CCC;padding:2px;" /></a></dd>
					</dl>
					<?php	
						}
					}
					?>
					<dl>
						<dt>Foto Antes</dt>
						<dd><input type="file" name="foto_antes" class="<?php echo empty($cnt)?"obg":"";?>" /></dd>
						<dd><label><span class="iconify" data-icon="bi:info-circle-fill" data-inline="true"></span>&nbsp;&nbsp;Máximo Largura: <?php echo $_width."px";?></label></dd>
					</dl>

					<?php
					if(is_object($cnt)) {
						$ft=$_dirDepois.$cnt->id.".".$cnt->foto_depois;
						if(file_exists($ft)) {

						
					?>
					<dl>
						<dd><a href="<?php echo $ft;?>" data-fancybox><img src="<?php echo $ft;?>" width="200" style="border: solid 1px #CCC;padding:2px;" /></a></dd>
					</dl>
					<?php	
						}
					}
					?>
					<dl>
						<dt>Foto Depois</dt>
						<dd><input type="file" name="foto_depois" class="<?php echo empty($cnt)?"obg":"";?>" /></dd>
						<dd><label><span class="iconify" data-icon="bi:info-circle-fill" data-inline="true"></span>&nbsp;&nbsp;Mínimo Largura: <?php echo $_width."px";?></label></dd>
					</dl>
					
				</div>
			</section>

		</form>
		<?php
			} else {
		?>
		<section class="grid">
			<section class="box">

				<div class="filter">
					<div class="filter-group">
						<div class="filter-button">
							<a href="<?php echo $_page."?form=1&$url";?>" class="verde"><i class="iconify" data-icon="bx-bx-plus"></i><span>adicionar</span></a>
						</div>
					</div>
				</div>

				<div class="registros">
					<table class="tablesorter">
						<thead>
							<tr>
								<th>Descrição</th>
								<th style="width:120px;">Antes</th>
								<th style="width:120px;">Depois</th>
							</tr>
						</thead>
						<tbody>
							<?php
							$registros=array();
							$sql->consult($_table,"*","where id_tema=$landingpage->id and lixo=0 order by data desc");
							while($x=mysqli_fetch_object($sql->mysqry)) {
								$registros[]=$x;
							}

							foreach($registros as $x) {
								?>
								<tr onclick="document.location.href='<?php echo $_page."?form=1&id_landingpage=$landingpage->id&edita=".$x->id;?>';">
									<td><?php echo utf8_encode($x->descricao);?></td>
									<td>
										<?php
										$ft=$_dirAntes.$x->id.".".$x->foto_antes;
										if(file_exists($ft)) {
											echo '<img src="'.$ft.'" width="100" style="padding:3px;border:solid 1px #CCC;" />';
										} else {
											echo "<font color=red>Sem Foto</font>";
										}
										?>
									</td>
									<td>
										<?php
										$ft=$_dirDepois.$x->id.".".$x->foto_depois;
										if(file_exists($ft)) {
											echo '<img src="'.$ft.'" width="100" style="padding:3px;border:solid 1px #CCC;" />';
										} else {

											echo "<font color=red>Sem Foto</font>";
										}
										?>
									</td>
								</tr>	
							<?php
							}
							?>
							<tr>
							</tr>
						</tbody>
					</table>
				</div>

			</section>
		</section>
		<?php
			}
		?>
		</section>
		
<?php
include "includes/footer.php";
?>