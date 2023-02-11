<?php
	if(isset($_POST['ajax'])) {
		require_once("lib/conf.php");
		require_once("usuarios/checa.php");
		
		$rtn = array();

		$pagina='';
		if(isset($_POST['id_pagina']) and is_numeric($_POST['id_pagina'])) {
			$sql->consult($_p."landingpage_banner","*","where id=".$_POST['id_pagina']." and lixo=0");
			if($sql->rows) {
				$pagina=mysqli_fetch_object($sql->mysqry);
			}
		}


		if(empty($pagina)) {
			$rtn=array('success'=>false,'error'=>'Página não encontrado!');
		} else if($_POST['ajax']=="foto") {
			$sql->update($_p."landingpage_banner","foto='".addslashes($_POST['foto'])."'","where id=$pagina->id");
			$rtn=array('success'=>true);
		}

		header("Content-type: application/json");
		echo json_encode($rtn);
		die();
	}
	include "includes/header.php";
	include "includes/nav.php";

	$_table=$_p."landingpage_banner";
	$_dirBanner=$_cloudinaryPath."arqs/landingpage/banner/";

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
		$sql->add($_table,"data=now(),id_usuario='".$usr->id."',id_tema='".$landingpage->id."'");
		$sql->consult($_table,"*","where id=$sql->ulid");
		$cnt=mysqli_fetch_object($sql->mysqry);
	}

	$campos=explode(",","titulo,descricao,video,id_tema,palavras");
	
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

		$jsc->go($_page."?id_landingpage=".$landingpage->id);
		die();
	}
?>

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
						<h1>Página Inicial</h1>
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
												<dt>Título da Página</dt>
												<dd><input type="text" name="titulo" value="<?php echo $values['titulo'];?>" class="obg" /></dd>
											</dl>
											<dl>
												<dt>Subtítulo Dinâmico</dt>
												<dd><input type="text" name="palavras" value="<?php echo $values['palavras'];?>" class="obg" /></dd>
											</dl>
										</div>
										<dl>
											<dt>Descrição</dt>
											<dd><input type="text" name="descricao" class="noupper" value="<?php echo $values['descricao'];?>" /></dd>
										</dl>
										<div>
											<?php
												$thumb="img/logo-user.png";
												if(is_object($cnt)) {
													if(!empty($cnt->foto)) {
														$image=$_cloudinaryURL.'c_thumb,w_600/'.$cnt->foto;
														$thumb=$_cloudinaryURL.'c_thumb,w_600/'.$cnt->foto;
													} 
												}
											?>
											<div class="form-image">
												<img src="<?php echo $thumb;?>" alt="" width="484" height="68" />
											</div>
											<dl>
												<dt>Foto</dt>
												<dd>
													<a href="javascript:;" id="foto" class="button button_main">Procurar</a>
													<input type="hidden" name="foto" />
												</dd>
											</dl>

										</div>
										<input type="hidden" name="foto" />

											<script src="https://widget.cloudinary.com/v2.0/global/all.js" type="text/javascript"></script> 
											<script type="text/javascript">
												var cloudinaryURL = '<?php echo $_cloudinaryURL;?>';
												var id_pagina = <?php echo (int)is_object($cnt)?$cnt->id:0;?>;

												var foto = cloudinary.createUploadWidget({
												  cloudName: '<?php echo $_cloudinaryCloudName;?>',
												  language: 'pt',
												  text: <?php echo json_encode($_cloudinaryText);?>,
												  multiple: false,
												  sources: ["local"],
												  folder: '<?php echo $_dirBanner;?>',
												  uploadPreset: '<?php echo $_cloudinaryUploadPresent;?>'}, 
													(error, result) => {
														if (!error && result) {
															if(result.event === "success") {
																$('input[name=foto]').val(result.info.path);
																

																if(id_pagina>0) {
																	data = `ajax=foto&foto=${result.info.path}&id_pagina=${id_pagina}`;
																	$.ajax({
																		type:"POST",
																		data:data,
																		success:function(rtn) {
																			$(".form-image img").attr('src',`${cloudinaryURL}c_thumb,w_600/${result.info.path}`)
																		}
																	});
																}
															}
														}
													}
												);

												$(function(){
													document.getElementById("foto").addEventListener("click", function(){
													    foto.open();
													}, false);
												})
											</script>
										</dd>
										
										<dl>
											<dt>Vídeo</dt>
											<dd><textarea name="video" class="noupper" style="height: 150px;"><?php echo $values['video'];?></textarea></dd>
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