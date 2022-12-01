<?php
	include "includes/header.php";
	include "includes/nav.php";

	$_table=$_p."landingpage_informacoes_apresentacao";

	$landingpage=$cnt='';
	if(isset($_GET['id_landingpage']) and is_numeric($_GET['id_landingpage'])) {
		$sql->consult($_p."landingpage_temas","*","where id='".addslashes($_GET['id_landingpage'])."'");
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

	// se nao encontrar registro
	if(empty($cnt)) {
		$sql->add($_table,"id_tema='".$landingpage->id."'");
		$sql->consult($_table,"*","where id=$sql->ulid");
		$cnt=mysqli_fetch_object($sql->mysqry);
	}

	$campos=explode(",","texto,titulo,id_tema");
	
	foreach($campos as $v) $values[$v]='';

	if(is_object($cnt)) {
		$values=$adm->values($campos,$cnt);
	}

	if(isset($_POST['acao'])) {
		$vSQL=$adm->vSQL($campos,$_POST);
		$values=$adm->values;

		$vSQL.="id_alteracao=$usr->id,alteracao_data=now()";
		$vWHERE="where id='".$cnt->id."'";
		$sql->update($_table,$vSQL,$vWHERE);
		$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='update',vsql='".addslashes($vSQL)."',vwhere='".addslashes($vWHERE)."',tabela='".$_table."',id_reg='".$cnt->id."'");

		$jsc->go($_page."?id_landingpage=".$cnt->id);
		die();
	}
?>

	<header class="header">
		<div class="header__content content">

			<div class="header__inner1">
				<section class="header-title">
					<h1><?php echo utf8_encode($landingpage->titulo);?></h1>
					<p>studiodental.dental/<?php echo $landingpage->code;?></p>
				</section>
				<?php
				require_once("includes/menus/menuLandingPage.php");
				?>
			</div>
		</div>
	</header>

	<main class="main">
		<div class="main__content content">
			
			<section class="filter">
				<div class="filter-group">
					<div class="filter-title">
						<h1>Informativo</h1>
					</div>
				</div>
			</section>

			<section class="grid">

				<div class="box box-col">

					<?php
					require_once("includes/submenus/subLandingPage.php");
					?>
					<div class="box-col__inner1">

						<section class="filter">
							<div class="filter-group"></div>
							<div class="filter-group">
								<div class="filter-form form">
									<dl>
										<dd><a href="javascript:;" class="button button_main js-submit"><i class="iconify" data-icon="fluent:checkmark-12-filled"></i> <span>Salvar</span></a></dd>
									</dl>
								</div>
							</div>							
						</section>

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
						<form method="post" class="form formulario-validacao">
							<input type="hidden" name="acao" value="wlib" />
							<button style="display:none;"></button>

							<fieldset>
								<legend>Informações</legend>

								<dl>
									<dt>Título Informativo</dt>
									<dd><input type="text" name="titulo" class="obg noupper" value="<?php echo $values['titulo'];?>" /></dd>
								</dl>
								<dl>
									<dt>Descrição</dt>
									<dd><textarea name="texto" id="texto" class="noupper" style="height:400px;"><?php echo $values['texto'];?></textarea></dd>
								</dl>
							</fieldset>

						</form>
			
					</div>		
				</div>

			</section>
		
		</div>
	</main>

<?php 
include "includes/footer.php";
?>	