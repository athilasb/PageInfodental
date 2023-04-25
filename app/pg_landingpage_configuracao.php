<?php
	include "includes/header.php";
	include "includes/nav.php";
    if($usr->tipo!="admin" and !in_array("landingpage",$_usuariosPermissoes)) {
        $jsc->jAlert("Você não tem permissão para acessar esta área!","erro","document.location.href='dashboard.php'");
        die();
    }

	$_table=$_p."landingpage_temas";

	$landingpage=$cnt='';
	if(isset($_GET['id_landingpage']) and is_numeric($_GET['id_landingpage'])) {
		$sql->consult($_p."landingpage_temas","*","where id='".$_GET['id_landingpage']."'");
		if($sql->rows) {
			$landingpage=mysqli_fetch_object($sql->mysqry);
			$cnt=$landingpage;

		}
	}

	$campos=explode(",","titulo,code,cor_primaria,cor_secundaria,codigo_head,codigo_body");
	
	foreach($campos as $v) $values[$v]='';
	$values['code']= '';

	if(is_object($landingpage)) {
		$values=$adm->values($campos,$cnt);
		$values['code']=$landingpage->code;
	}

	if(isset($_POST['acao'])) {
		$vSQL=$adm->vSQL($campos,$_POST);
		$values=$adm->values;

		$vSQL=substr($vSQL,0,strlen($vSQL)-1);
		$vWHERE="where id='".$cnt->id."'";
		$sql->update($_table,$vSQL,$vWHERE);
		$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='update',vsql='".addslashes($vSQL)."',vwhere='".addslashes($vWHERE)."',tabela='".$_table."',id_reg='".$cnt->id."'");

		$jsc->go($_page."?id_landingpage=".$cnt->id);
		die();
	}
?>
<script src="js/jquery.colorpicker.js"></script>
<script type="text/javascript">
	$(function(){
		$('input[name=cor_primaria]').ColorPicker({
			color: '<?php echo $values['cor_primaria'];?>',
			onShow: function (colpkr) {$(colpkr).fadeIn(500);return false;},
			onHide: function (colpkr) {$(colpkr).fadeOut(500);return false;},
			onChange: function (hsb, hex, rgb) {console.log(hex);$('input[name=cor_primaria]').css('backgroundColor', '#' + hex).val('#'+hex);}
		});
		$('input[name=cor_secundaria]').ColorPicker({
			color: '<?php echo $values['cor_secundaria'];?>',
			onShow: function (colpkr) {$(colpkr).fadeIn(500);return false;},
			onHide: function (colpkr) {$(colpkr).fadeOut(500);return false;},
			onChange: function (hsb, hex, rgb) {console.log(hex);$('input[name=cor_secundaria]').css('backgroundColor', '#' + hex).val('#'+hex);}
		});
		$('input[name=cor_primaria]').css('backgroundColor','<?php echo $values['cor_primaria'];?>');
		$('input[name=cor_secundaria]').css('backgroundColor','<?php echo $values['cor_secundaria'];?>');
		var input = $('input[name=code]');

		input.bind('keypress', function(e)
		{
		    if (((e.which < 65 || e.which > 122) && (e.which < 48 || e.which > 57)) && e.which != 45)
		    {
		        e.preventDefault();
		    } 
		});
		<?php
		if(empty($cnt)) {
		?>
		$('input[name=titulo]').keyup(function(){
			let code = retira_acentos($(this).val().toLowerCase().split(' ').join('-'));
			$('input[name=code]').val(code);
		});
		$('input[name=cor_primaria]').keyup(function(){
			let cor = $(this).val();
			$('input[name=cor_secundaria]').val(cor);
		});
		<?php
		}
		?>
	})
</script>

	<header class="header">
		<div class="header__content content">

			<div class="header__inner1">
				<section class="header-title">
					<h1><?php echo utf8_encode($landingpage->titulo);?></h1>
					<a href="<?php echo $link_landingpage.$landingpage->code;?>" target="_blank"><p><?php echo $link_landingpage.$landingpage->code;?></p></a>
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
						<h1>Configurações</h1>
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

						<form method="post" class="form formulario-validacao">
							<input type="hidden" name="acao" value="wlib" />
							<button style="display:none;"></button>

							<fieldset>
								<legend>Informações</legend>

								<div class="grid grid_2">

									<div style="grid-column:span 2">
										<div class="colunas">
											<dl>
												<dt>Tema</dt>
												<dd><input type="text" name="titulo" value="<?php echo $values['titulo'];?>" class="obg" /></dd>
											</dl>
											<dl>
												<dt>URL do Tema</dt>
												<dd><input type="text" name="code" value="<?php echo $values['code'];?>" class="obg" /></dd>
											</dl>
										</div>
										<div class="colunas">
											<dl>
												<dt>Cor Primária</dt>
												<dd><input type="text" name="cor_primaria" value="<?php echo $values['cor_primaria'];?>" class="obg" /></dd>
											</dl>
											<dl>
												<dt>Cor Secundária</dt>
												<dd><input type="text" name="cor_secundaria" value="<?php echo $values['cor_secundaria'];?>" class="obg" /></dd>
											</dl>
										</div>
										<dl>
											<dt>Código de Rastreamento Body</dt>
											<dd>
												<textarea name="codigo_body" style="height: 200px;" class="noupper"><?php echo $values['codigo_body'];?></textarea>
											</dd>
										</dl>
										<dl>
											<dt>Código de Rastreamento Head</dt>
											<dd>
												<textarea name="codigo_head" style="height: 200px;" class="noupper"><?php echo $values['codigo_head'];?></textarea>
											</dd>
										</dl>
									</div>
								</div>
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