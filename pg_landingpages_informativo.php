<?php
	include "includes/header.php";
	include "includes/nav.php";

	$_table=$_p."landingpage_informacoes_apresentacao";
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

	$campos=explode(",","texto,titulo,id_tema");
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
				$vSQL=substr($vSQL, 0, strlen($vSQL)-1);
				$sql->add($_table,$vSQL);
				$id_reg=$sql->ulid;
				$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='insert',vsql='".addslashes($vSQL)."',tabela='".$_table."',id_reg='".$sql->ulid."'");
			}

			$jsc->jAlert("Informações salvas com sucesso!","sucesso","document.location.href='".$_page."?id_landingpage=".$landingpage->id."'");
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
			<script>
				$(function(){
					var fck_texto = CKEDITOR.replace('texto',{
		    							filebrowserUploadUrl: 'ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Files',
											height: '350',
											width: '100%',
											language: 'pt-br'
										});
					CKFinder.setupCKEditor(fck_texto);
				});
			</script>	

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
								<span class="badge">3</span> Preencha os dados do informativo
							</div>
						</div>

						<div class="filter-group filter-group_right">
							<div class="filter-button">
								<a href="javascript:;" class="azul  btn-submit"><i class="iconify" data-icon="bx-bx-check"></i><span>salvar</span></a>
							</div>
						</div>

					</div>

					<dl>
						<dt>Título Informativo</dt>
						<dd><input type="text" name="titulo" class="obg noupper" value="<?php echo $values['titulo'];?>" /></dd>
						<dd><label><span class="iconify" data-icon="bi:info-circle-fill" data-inline="true"></span></label></dd>
					</dl>
					<dl>
						<dt>Descrição</dt>
						<dd><textarea name="texto" id="texto" class="noupper" style="height:400px;"><?php echo $values['texto'];?></textarea></dd>
						<dd><label><span class="iconify" data-icon="bi:info-circle-fill" data-inline="true"></span></label></dd>
					</dl>
				</div>
			</section>
		</form>
	</section>
		
<?php
include "includes/footer.php";
?>