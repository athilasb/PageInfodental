<?php
	include "includes/header.php";
	include "includes/nav.php";

	$_table=$_p."landingpage_depoimentos";
	$_page=basename($_SERVER['PHP_SELF']);

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
		$campos=explode(",","autor,depoimento,id_tema");
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

				$jsc->jAlert("Informações salvas com sucesso!","sucesso","document.location.href='".$_page."?form=1&edita=".$id_reg."&".$url."'");
				die();
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
						<dt>Autor</dt>
						<dd><input type="text" name="autor" class="obg noupper" value="<?php echo $values['autor'];?>" /></dd>
					</dl>
					<dl>
						<dt>Depoimento</dt>
						<dd><input type="text" name="depoimento" class="depoimento noupper" value="<?php echo $values['depoimento'];?>" /></dd>
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
								<th>Autor</th>
								<th>Depoimento</th>
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
									<td><strong><?php echo utf8_encode($x->autor);?></strong></td>
									<td><?php echo utf8_encode($x->depoimento);?></td>
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