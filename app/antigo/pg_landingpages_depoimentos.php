<?php
	include "includes/header.php";
	include "includes/nav.php";

	$_table=$_p."landingpages_depoimentos";
	$_page=basename($_SERVER['PHP_SELF']);

	$landingpage=$cnt='';
	if(isset($_GET['id_landingpage']) and is_numeric($_GET['id_landingpage'])) {
		$sql->consult($_p."landingpage_temas","*","where id='".$_GET['id_landingpage']."'");
		if($sql->rows) {
			$landingpage=mysqli_fetch_object($sql->mysqry);
		}
	}

	if(empty($landingpage)) {
		$jsc->jAlert("Landing Page não encontrada!","erro","document.location.href='pg_landingpages.php'");
		die();
	}

	$sql->consult($_table,"*","WHERE id_tema='".$landingpage->id."' and lixo=0");
	if($sql->rows) {
		$cnt=mysqli_fetch_object($sql->mysqry);
	}

	$campos=explode(",","id_tema,autor1,depoimento1,autor2,depoimento2,autor3,depoimento3");
	foreach($campos as $v) $values[$v]='';

	if(is_object($cnt)) {
		$values=$adm->values($campos,$cnt);
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
?>
	<section class="content">
		
		<?php
		require_once("includes/abaLandingPage.php");
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

						<div class="filter-group">
							<div class="filter-title">
								<span class="badge">7</span> Preencha o depoimento e autor
							</div>
						</div>

						<div class="filter-group filter-group_right">
							<div class="filter-button">
								<?php if(is_object($cnt)){?><a href="?deletaLandingPage=<?php echo $landingpage->id."&".$url;?>" class="js-deletar"><i class="iconify" data-icon="bx-bx-trash"></i></a><?php }?>
								<a href="javascript:;" class="azul  btn-submit"><i class="iconify" data-icon="bx-bx-check"></i><span>salvar</span></a>
							</div>
						</div>

					</div>

					<div class="colunas4">
						<dl class="dl3">
							<dt>1° Depoimento <span class="iconify" data-icon="bi:info-circle-fill" data-inline="true" style="color: #98928E;"></span></dt>
							<dd><input type="text" name="depoimento1" class="depoimento obg noupper" value="<?php echo $values['depoimento1'];?>" /></dd>
						</dl>
						<dl class="dl2">
							<dt>Autor <span class="iconify" data-icon="bi:info-circle-fill" data-inline="true" style="color: #98928E;"></span></dt>
							<dd><input type="text" name="autor1" class="obg noupper" value="<?php echo $values['autor1'];?>" /></dd>
						</dl>
					</div>

					<div class="colunas4">
						<dl class="dl3">
							<dt>2° Depoimento <span class="iconify" data-icon="bi:info-circle-fill" data-inline="true" style="color: #98928E;"></span></dt>
							<dd><input type="text" name="depoimento2" class="depoimento noupper" value="<?php echo $values['depoimento2'];?>" /></dd>
						</dl>
						<dl class="dl2">
							<dt>Autor <span class="iconify" data-icon="bi:info-circle-fill" data-inline="true" style="color: #98928E;"></span></dt>
							<dd><input type="text" name="autor2" class="noupper" value="<?php echo $values['autor2'];?>" /></dd>
						</dl>
					</div>

					<div class="colunas4">
						<dl class="dl3">
							<dt>3° Depoimento <span class="iconify" data-icon="bi:info-circle-fill" data-inline="true" style="color: #98928E;"></span></dt>
							<dd><input type="text" name="depoimento3" class="depoimento noupper" value="<?php echo $values['depoimento3'];?>" /></dd>
						</dl>
						<dl class="dl2">
							<dt>Autor <span class="iconify" data-icon="bi:info-circle-fill" data-inline="true" style="color: #98928E;"></span></dt>
							<dd><input type="text" name="autor3" class="noupper" value="<?php echo $values['autor3'];?>" /></dd>
						</dl>
					</div>
					
				</div>
			</section>
		</form>

	</section>
		
<?php
include "includes/footer.php";
?>