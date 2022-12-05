<?php
	if(isset($_POST['ajax'])) {
		require_once("lib/conf.php");
		require_once("usuarios/checa.php");
		
		$rtn = array();

		$pagina='';
		if(isset($_POST['id_pagina']) and is_numeric($_POST['id_pagina'])) {
			$sql->consult($_p."landingpage_antesedepois","*","where id=".$_POST['id_pagina']." and lixo=0");
			if($sql->rows) {
				$pagina=mysqli_fetch_object($sql->mysqry);
			}
		}


		if(empty($pagina)) {
			$rtn=array('success'=>false,'error'=>'Página não encontrado!');
		} 

		if($_POST['ajax']=="foto_antes1") {
			$sql->update($_p."landingpage_antesedepois","foto_antes1='".addslashes($_POST['foto_antes1'])."'","where id=$pagina->id");
			$rtn=array('success'=>true);
		} 

		if($_POST['ajax']=="foto_antes2") {
			$sql->update($_p."landingpage_antesedepois","foto_antes2='".addslashes($_POST['foto_antes2'])."'","where id=$pagina->id");
			$rtn=array('success'=>true);
		}

		if($_POST['ajax']=="foto_depois1") {
			$sql->update($_p."landingpage_antesedepois","foto_depois1='".addslashes($_POST['foto_depois1'])."'","where id=$pagina->id");
			$rtn=array('success'=>true);
		}

		if($_POST['ajax']=="foto_depois2") {
			$sql->update($_p."landingpage_antesedepois","foto_depois2='".addslashes($_POST['foto_depois2'])."'","where id=$pagina->id");
			$rtn=array('success'=>true);
		}

		header("Content-type: application/json");
		echo json_encode($rtn);
		die();
	}
	include "includes/header.php";
	include "includes/nav.php";

	$_table=$_p."landingpage_antesedepois";
	$_dirAntes1=$_cloudinaryPath."arqs/landingpage/antesedepois/antes1/";
	$_dirAntes2=$_cloudinaryPath."arqs/landingpage/antesedepois/antes2/";

	$_dirDepois1=$_cloudinaryPath."arqs/landingpage/antesedepois/depois1/";
	$_dirDepois2=$_cloudinaryPath."arqs/landingpage/antesedepois/depois2/";

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

	$campos=explode(",","id_tema,nome_paciente1,nome_paciente2");
	
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
					<p>studiodental.infodental.dental/<?php echo $landingpage->code;?></p>
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
										
										<div>
											<?php
												$thumb="img/logo-user.png";
												if(is_object($cnt)) {
													if(!empty($cnt->foto_antes1)) {
														$image=$_cloudinaryURL.'c_thumb,w_600/'.$cnt->foto_antes1;
														$thumb=$_cloudinaryURL.'c_thumb,w_600/'.$cnt->foto_antes1;
													} 
												}
											?>
											<div class="form-imagefoto_antes1">
												<img src="<?php echo $thumb;?>" alt="" width="150" height="150" />
											</div>
											<dl>
												<dt>Foto 1 Antes</dt>
												<dd>
													<a href="javascript:;" id="foto_antes1" class="button button_main">Procurar</a>
													<input type="hidden" name="foto_antes1" />
												</dd>
											</dl>

										</div>
										<input type="hidden" name="foto_antes1" />

										<script src="https://widget.cloudinary.com/v2.0/global/all.js" type="text/javascript"></script> 
										<script type="text/javascript">
											var cloudinaryURL = '<?php echo $_cloudinaryURL;?>';
											var id_pagina = <?php echo (int)is_object($cnt)?$cnt->id:0;?>;

											var foto_antes1 = cloudinary.createUploadWidget({
											  cloudName: '<?php echo $_cloudinaryCloudName;?>',
											  language: 'pt',
											  text: <?php echo json_encode($_cloudinaryText);?>,
											  multiple: false,
											  sources: ["local"],
											  folder: '<?php echo $_dirAntes1;?>',
											  uploadPreset: '<?php echo $_cloudinaryUploadPresent;?>'}, 
												(error, result) => {
													if (!error && result) {
														if(result.event === "success") {
															$('input[name=foto_antes1]').val(result.info.path);
															

															if(id_pagina>0) {
																data = `ajax=foto_antes1&foto_antes1=${result.info.path}&id_pagina=${id_pagina}`;
																$.ajax({
																	type:"POST",
																	data:data,
																	success:function(rtn) {
																		$(".form-imagefoto_antes1 img").attr('src',`${cloudinaryURL}c_thumb,w_600/${result.info.path}`)
																	}
																});
															}
														}
													}
												}
											);

											$(function(){
												document.getElementById("foto_antes1").addEventListener("click", function(){
												    foto_antes1.open();
												}, false);
											})
										</script>

										<div>
											<?php
												$thumb="img/logo-user.png";
												if(is_object($cnt)) {
													if(!empty($cnt->foto_depois1)) {
														$image=$_cloudinaryURL.'c_thumb,w_600/'.$cnt->foto_depois1;
														$thumb=$_cloudinaryURL.'c_thumb,w_600/'.$cnt->foto_depois1;
													} 
												}
											?>
											<div class="form-imagefoto_depois1">
												<img src="<?php echo $thumb;?>" alt="" width="150" height="150" />
											</div>
											<dl>
												<dt>Foto 1 Depois</dt>
												<dd>
													<a href="javascript:;" id="foto_depois1" class="button button_main">Procurar</a>
													<input type="hidden" name="foto_depois1" />
												</dd>
											</dl>

										</div>
										<input type="hidden" name="foto_depois1" />
										<script type="text/javascript">
											var cloudinaryURL = '<?php echo $_cloudinaryURL;?>';
											var id_pagina = <?php echo (int)is_object($cnt)?$cnt->id:0;?>;

											var foto_depois1 = cloudinary.createUploadWidget({
											  cloudName: '<?php echo $_cloudinaryCloudName;?>',
											  language: 'pt',
											  text: <?php echo json_encode($_cloudinaryText);?>,
											  multiple: false,
											  sources: ["local"],
											  folder: '<?php echo $_dirDepois1;?>',
											  uploadPreset: '<?php echo $_cloudinaryUploadPresent;?>'}, 
												(error, result) => {
													if (!error && result) {
														if(result.event === "success") {
															$('input[name=foto_depois1]').val(result.info.path);
															

															if(id_pagina>0) {
																data = `ajax=foto_depois1&foto_depois1=${result.info.path}&id_pagina=${id_pagina}`;
																$.ajax({
																	type:"POST",
																	data:data,
																	success:function(rtn) {
																		$(".form-imagefoto_depois1 img").attr('src',`${cloudinaryURL}c_thumb,w_600/${result.info.path}`)
																	}
																});
															}
														}
													}
												}
											);

											$(function(){
												document.getElementById("foto_depois1").addEventListener("click", function(){
												    foto_depois1.open();
												}, false);
											})
										</script>

										<dl>
											<dt>Nome Paciente</dt>
											<dd><input type="text" name="nome_paciente1" class="noupper obg" value="<?php echo $values['nome_paciente1'];?>" /></dd>
										</dl>

										<div>
											<?php
												$thumb="img/logo-user.png";
												if(is_object($cnt)) {
													if(!empty($cnt->foto_antes2)) {
														$image=$_cloudinaryURL.'c_thumb,w_600/'.$cnt->foto_antes2;
														$thumb=$_cloudinaryURL.'c_thumb,w_600/'.$cnt->foto_antes2;
													} 
												}
											?>
											<div class="form-imagefoto_antes2">
												<img src="<?php echo $thumb;?>" alt="" width="150" height="150" />
											</div>
											<dl>
												<dt>Foto 2 Antes</dt>
												<dd>
													<a href="javascript:;" id="foto_antes2" class="button button_main">Procurar</a>
													<input type="hidden" name="foto_antes2" />
												</dd>
											</dl>

										</div>
										<input type="hidden" name="foto_antes2" />

										<script type="text/javascript">
											var cloudinaryURL = '<?php echo $_cloudinaryURL;?>';
											var id_pagina = <?php echo (int)is_object($cnt)?$cnt->id:0;?>;

											var foto_antes2 = cloudinary.createUploadWidget({
											  cloudName: '<?php echo $_cloudinaryCloudName;?>',
											  language: 'pt',
											  text: <?php echo json_encode($_cloudinaryText);?>,
											  multiple: false,
											  sources: ["local"],
											  folder: '<?php echo $_dirAntes2;?>',
											  uploadPreset: '<?php echo $_cloudinaryUploadPresent;?>'}, 
												(error, result) => {
													if (!error && result) {
														if(result.event === "success") {
															$('input[name=foto_antes2]').val(result.info.path);
															

															if(id_pagina>0) {
																data = `ajax=foto_antes2&foto_antes2=${result.info.path}&id_pagina=${id_pagina}`;
																$.ajax({
																	type:"POST",
																	data:data,
																	success:function(rtn) {
																		$(".form-imagefoto_antes2 img").attr('src',`${cloudinaryURL}c_thumb,w_600/${result.info.path}`)
																	}
																});
															}
														}
													}
												}
											);

											$(function(){
												document.getElementById("foto_antes2").addEventListener("click", function(){
												    foto_antes2.open();
												}, false);
											})
										</script>

										<div>
											<?php
												$thumb="img/logo-user.png";
												if(is_object($cnt)) {
													if(!empty($cnt->foto_depois2)) {
														$image=$_cloudinaryURL.'c_thumb,w_600/'.$cnt->foto_depois2;
														$thumb=$_cloudinaryURL.'c_thumb,w_600/'.$cnt->foto_depois2;
													} 
												}
											?>
											<div class="form-imagefoto_depois2">
												<img src="<?php echo $thumb;?>" alt="" width="150" height="150" />
											</div>
											<dl>
												<dt>Foto 2 Depois</dt>
												<dd>
													<a href="javascript:;" id="foto_depois2" class="button button_main">Procurar</a>
													<input type="hidden" name="foto_depois2" />
												</dd>
											</dl>

										</div>
										<input type="hidden" name="foto_depois2" />

										<script type="text/javascript">
											var cloudinaryURL = '<?php echo $_cloudinaryURL;?>';
											var id_pagina = <?php echo (int)is_object($cnt)?$cnt->id:0;?>;

											var foto_depois2 = cloudinary.createUploadWidget({
											  cloudName: '<?php echo $_cloudinaryCloudName;?>',
											  language: 'pt',
											  text: <?php echo json_encode($_cloudinaryText);?>,
											  multiple: false,
											  sources: ["local"],
											  folder: '<?php echo $_dirDepois2;?>',
											  uploadPreset: '<?php echo $_cloudinaryUploadPresent;?>'}, 
												(error, result) => {
													if (!error && result) {
														if(result.event === "success") {
															$('input[name=foto_depois2]').val(result.info.path);
															

															if(id_pagina>0) {
																data = `ajax=foto_depois2&foto_depois2=${result.info.path}&id_pagina=${id_pagina}`;
																$.ajax({
																	type:"POST",
																	data:data,
																	success:function(rtn) {
																		$(".form-imagefoto_depois2 img").attr('src',`${cloudinaryURL}c_thumb,w_600/${result.info.path}`)
																	}
																});
															}
														}
													}
												}
											);

											$(function(){
												document.getElementById("foto_depois2").addEventListener("click", function(){
												    foto_depois2.open();
												}, false);
											})
										</script>
										
										<dl>
											<dt>Nome do Paciente</dt>
											<dd><input type="text" name="nome_paciente2" class="noupper" value="<?php echo $values['nome_paciente2'];?>" /></dd>
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