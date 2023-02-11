<?php
	if(isset($_POST['ajax'])) {
		require_once("lib/conf.php");
		require_once("usuarios/checa.php");
		
		$rtn = array();

		$pagina='';
		if(isset($_POST['id_pagina']) and is_numeric($_POST['id_pagina'])) {
			$sql->consult($_p."landingpage_aclinica","*","where id=".$_POST['id_pagina']." and lixo=0");
			if($sql->rows) {
				$pagina=mysqli_fetch_object($sql->mysqry);
			}
		}


		if(empty($pagina)) {
			$rtn=array('success'=>false,'error'=>'Página não encontrado!');
		} 

		if($_POST['ajax']=="foto1") {
			$sql->update($_p."landingpage_aclinica","foto1='".addslashes($_POST['foto1'])."'","where id=$pagina->id");
			$rtn=array('success'=>true);
		} else if($_POST['ajax']=="foto2") {
			$sql->update($_p."landingpage_aclinica","foto2='".addslashes($_POST['foto2'])."'","where id=$pagina->id");
			$rtn=array('success'=>true);
		} else if($_POST['ajax']=="foto3") {
			$sql->update($_p."landingpage_aclinica","foto3='".addslashes($_POST['foto3'])."'","where id=$pagina->id");
			$rtn=array('success'=>true);
		}

		header("Content-type: application/json");
		echo json_encode($rtn);
		die();
	}
	include "includes/header.php";
	include "includes/nav.php";

	$_table=$_p."landingpage_aclinica";
	$_dirFoto1=$_cloudinaryPath."arqs/landingpage/aclinica/fotos1/";
	$_dirFoto2=$_cloudinaryPath."arqs/landingpage/aclinica/fotos2/";
	$_dirFoto3=$_cloudinaryPath."arqs/landingpage/aclinica/fotos3/";

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

	$campos=explode(",","id_tema,nome,legenda1,legenda2,legenda3");
	
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
						<h1>A Clínica</h1>
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

								<div>

									<div style="grid-column:span 2">
										
										<dl>
											<dt>Nome da Clínica</dt>
											<dd><input type="text" name="nome" value="<?php echo $values['nome'];?>" class="obg" /></dd>
										</dl>
										
										<div>
											<?php
												$thumb="img/logo-user.png";
												if(is_object($cnt)) {
													if(!empty($cnt->foto1)) {
														$image=$_cloudinaryURL.'c_thumb,w_600/'.$cnt->foto1;
														$thumb=$_cloudinaryURL.'c_thumb,w_600/'.$cnt->foto1;
													} 
												}
											?>
											<div class="form-image1">
												<img src="<?php echo $thumb;?>" alt="" width="150" height="150" />
											</div>
											<dl>
												<dt>Foto 1</dt>
												<dd>
													<a href="javascript:;" id="foto1" class="button button_main">Procurar</a>
													<input type="hidden" name="foto1" />
												</dd>
											</dl>

										</div>
										<input type="hidden" name="foto1" />

										<dl>
											<dt>Descrição</dt>
											<dd><input type="text" name="legenda1" class="noupper obg" value="<?php echo $values['legenda1'];?>" /></dd>
										</dl>

										<script src="https://widget.cloudinary.com/v2.0/global/all.js" type="text/javascript"></script> 
										<script type="text/javascript">
											var cloudinaryURL = '<?php echo $_cloudinaryURL;?>';
											var id_pagina = <?php echo (int)is_object($cnt)?$cnt->id:0;?>;

											var image1 = cloudinary.createUploadWidget({
											  cloudName: '<?php echo $_cloudinaryCloudName;?>',
											  language: 'pt',
											  text: <?php echo json_encode($_cloudinaryText);?>,
											  multiple: false,
											  sources: ["local"],
											  folder: '<?php echo $_dirFoto1;?>',
											  uploadPreset: '<?php echo $_cloudinaryUploadPresent;?>'}, 
												(error, result) => {
													if (!error && result) {
														if(result.event === "success") {
															$('input[name=foto1]').val(result.info.path);
															

															if(id_pagina>0) {
																data = `ajax=foto1&foto1=${result.info.path}&id_pagina=${id_pagina}`;
																$.ajax({
																	type:"POST",
																	data:data,
																	success:function(rtn) {
																		$(".form-image1 img").attr('src',`${cloudinaryURL}c_thumb,w_600/${result.info.path}`)
																	}
																});
															}
														}
													}
												}
											)
											document.getElementById("foto1").addEventListener("click", function(){
											    image1.open();
											}, false);
										
										</script>

										<div>
											<?php
												$thumb="img/logo-user.png";
												if(is_object($cnt)) {
													if(!empty($cnt->foto2)) {
														$image=$_cloudinaryURL.'c_thumb,w_600/'.$cnt->foto2;
														$thumb=$_cloudinaryURL.'c_thumb,w_600/'.$cnt->foto2;
													} 
												}
											?>
											<div class="form-image2">
												<img src="<?php echo $thumb;?>" alt="" width="150" height="150" />
											</div>
											<dl>
												<dt>Foto 2</dt>
												<dd>
													<a href="javascript:;" id="foto2" class="button button_main">Procurar</a>
													<input type="hidden" name="foto2" />
												</dd>
											</dl>

										</div>
										<input type="hidden" name="foto2" />
										<script type="text/javascript">
											var cloudinaryURL = '<?php echo $_cloudinaryURL;?>';
											var id_pagina = <?php echo (int)is_object($cnt)?$cnt->id:0;?>;

											var image2 = cloudinary.createUploadWidget({
											  cloudName: '<?php echo $_cloudinaryCloudName;?>',
											  language: 'pt',
											  text: <?php echo json_encode($_cloudinaryText);?>,
											  multiple: false,
											  sources: ["local"],
											  folder: '<?php echo $_dirFoto2;?>',
											  uploadPreset: '<?php echo $_cloudinaryUploadPresent;?>'}, 
												(error, result) => {
													if (!error && result) {
														if(result.event === "success") {
															$('input[name=foto2]').val(result.info.path);
															

															if(id_pagina>0) {
																data = `ajax=foto2&foto2=${result.info.path}&id_pagina=${id_pagina}`;
																$.ajax({
																	type:"POST",
																	data:data,
																	success:function(rtn) {
																		$(".form-image2 img").attr('src',`${cloudinaryURL}c_thumb,w_600/${result.info.path}`)
																	}
																});
															}
														}
													}
												}
											)
											document.getElementById("foto2").addEventListener("click", function(){
											    image2.open();
											}, false);
										</script>
										
										<dl>
											<dt>Descrição</dt>
											<dd><input type="text" name="legenda2" class="noupper" value="<?php echo $values['legenda2'];?>" /></dd>
										</dl>

										<div>
											<?php
												$thumb="img/logo-user.png";
												if(is_object($cnt)) {
													if(!empty($cnt->foto3)) {
														$image=$_cloudinaryURL.'c_thumb,w_600/'.$cnt->foto3;
														$thumb=$_cloudinaryURL.'c_thumb,w_600/'.$cnt->foto3;
													} 
												}
											?>
											<div class="form-image3">
												<img src="<?php echo $thumb;?>" alt="" width="150" height="150" />
											</div>
											<dl>
												<dt>Foto 3</dt>
												<dd>
													<a href="javascript:;" id="foto3" class="button button_main">Procurar</a>
													<input type="hidden" name="foto3" />
												</dd>
											</dl>

										</div>
										<input type="hidden" name="foto3" />
										<script type="text/javascript">
											var cloudinaryURL = '<?php echo $_cloudinaryURL;?>';
											var id_pagina = <?php echo (int)is_object($cnt)?$cnt->id:0;?>;

											var image3 = cloudinary.createUploadWidget({
											  cloudName: '<?php echo $_cloudinaryCloudName;?>',
											  language: 'pt',
											  text: <?php echo json_encode($_cloudinaryText);?>,
											  multiple: false,
											  sources: ["local"],
											  folder: '<?php echo $_dirFoto3;?>',
											  uploadPreset: '<?php echo $_cloudinaryUploadPresent;?>'}, 
												(error, result) => {
													if (!error && result) {
														if(result.event === "success") {
															$('input[name=foto3]').val(result.info.path);
															

															if(id_pagina>0) {
																data = `ajax=foto3&foto3=${result.info.path}&id_pagina=${id_pagina}`;
																$.ajax({
																	type:"POST",
																	data:data,
																	success:function(rtn) {
																		$(".form-image3 img").attr('src',`${cloudinaryURL}c_thumb,w_600/${result.info.path}`)
																	}
																});
															}
														}
													}
												}
											)
											document.getElementById("foto3").addEventListener("click", function(){
											    image3.open();
											}, false);
										</script>
										
										<dl>
											<dt>Descrição</dt>
											<dd><input type="text" name="legenda3" class="noupper" value="<?php echo $values['legenda3'];?>" /></dd>
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