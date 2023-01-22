<?php
	include "includes/header.php";
	include "includes/nav.php";

	$_table=$_p."landingpage_aclinica";
	$_page=basename($_SERVER['PHP_SELF']);
	$_width=750;
	$_height='';

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

	$fotos = array(
		'foto1' => array('titulo' => 'Foto 1', 'dir' => 'arqs/landingpages/aclinica/fotos1/', 'class' => 'obg', 'titulo_legenda' => 'Descrição', 'legenda' => 'legenda1'),
		'foto2' => array('titulo' => 'Foto 2', 'dir' => 'arqs/landingpages/aclinica/fotos2/', 'class' => '', 'titulo_legenda' => 'Descrição', 'legenda' => 'legenda2'),
		'foto3' => array('titulo' => 'Foto 3', 'dir' => 'arqs/landingpages/aclinica/fotos3/', 'class' => '', 'titulo_legenda' => 'Descrição', 'legenda' => 'legenda3')
	);

	$campos=explode(",","id_tema,nome,legenda1,legenda2,legenda3");
	foreach($campos as $v) $values[$v]='';

	if(is_object($cnt)) {
		$values=$adm->values($campos,$cnt);
	}

	if(isset($_POST['acao']) and $_POST['acao']=="wlib") {
		$vSQL=$adm->vSQL($campos,$_POST);
		$values=$adm->values;
		$processa=true;

		if(isset($_POST['foto1']) and !empty($_POST['foto1'])) $vSQL.="foto1='".$_POST['foto1']."',";
		if(isset($_POST['foto2']) and !empty($_POST['foto2'])) $vSQL.="foto2='".$_POST['foto2']."',";
		if(isset($_POST['foto3']) and !empty($_POST['foto3'])) $vSQL.="foto3='".$_POST['foto3']."',";

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
			foreach($fotos as $k => $v) {
				if(isset($_FILES[$k]) and !empty($_FILES[$k]['tmp_name'])) { 
					$up=new Uploader();
					$up->uploadCorta("Imagem",$_FILES[$k],"",5242880*2,$_width,$_height,$v['dir'],$id_reg);

					if($up->erro) {
						$msgErro=$up->resul;
					} else {
						$ext=$up->ext;
						$vSQL="$k='".$ext."'";
						$vWHERE="where id='".$id_reg."'";
						$sql->update($_table,$vSQL,$vWHERE);
						$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='update',vsql='".addslashes($vSQL)."',tabela='".$_table."',id_reg='".$id_reg."'");
					}
				}
			}

			if(!empty($msgErro)) {
				$jsc->jAlert($msgErro,"erro","");
			} else {
				$jsc->jAlert("Informações salvas com sucesso!","sucesso","document.location.href='".$_page."?id_landingpage=".$landingpage->id."'");
				die();
			}
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
								<span class="badge">4</span> Escolha as fotos e preencha a descrição
							</div>
						</div>

						<div class="filter-group filter-group_right">
							<div class="filter-button">
								<?php if(is_object($cnt)){?><a href="?deletaAclinica=<?php echo $cnt->id."&".$url;?>" class="js-deletar"><i class="iconify" data-icon="bx-bx-trash"></i></a><?php }?>
								<a href="javascript:;" class="azul  btn-submit"><i class="iconify" data-icon="bx-bx-check"></i><span>salvar</span></a>
							</div>
						</div>

					</div>
					<dl class="dl2">
						<dt>Nome da Clínica <span class="iconify" data-icon="bi:info-circle-fill" data-inline="true" style="color: #98928E;"></span></dt>
						<dd>
							<input type="text" name="nome" value="<?php echo $values['nome'];?>" class="obg"/>
						</dd>
					</dl>
					<dl>	
						<?php
							if(is_object($cnt)) {
								if(!empty($cnt->foto1)) {
									$ft='https://res.cloudinary.com/infodental/image/upload/'.$cnt->foto1;
									$ftThumb='https://res.cloudinary.com/infodental/image/upload/c_thumb,w_100,g_face/'.$cnt->foto1;
									echo "<a href=\"".$ft."\" data-fancybox><img src=\"".$ftThumb."\" /></a>";
								} else {
									echo "<span class=\"botao\"><i class=\"icon-cancel\"></i> Sem imagem</span>";
								}
							}
						?>
					</dl>
					<dl>
						<dt>Foto 1 <span class="iconify" data-icon="bi:info-circle-fill" data-inline="true" style="color: #98928E;"></span>&nbsp;&nbsp;Dimensão: 750x</dt>
						<dd>
							<button id="foto1" onclick="return false;" class="cloudinary-button">Procurar foto</button>
							<input type="hidden" name="foto1" id="js-foto1" class="" />
							<script>
								var foto1 = cloudinary.createUploadWidget({
								  cloudName: 'infodental',
								  language: 'pt',
								  text: {
								    "pt": {
								        "local": {
											"browse": "Carregar arquivo",
											"main_title": "Enviar Arquivos",
											"dd_title_single": "Carregue e solte a imagem aqui",
											"dd_title_multi": "Carregue e solte imagens aqui",
											"drop_title_single": "Solte a foto para carregar",
											"drop_title_multiple": "Solte as fotos para carregar"
										}
								    }
								  },
								  multiple: false,
								  sources: ["local"],
								  folder: 'aclinica',
								  uploadPreset: 'ir9b4eem'}, (error, result) => {
								    if (!error && result && result.event === "success") {
								      console.log('Done! Here is the image info: ', result.info);
								      $("#js-foto1").val(result.info.path);
								    }
								  }
								)

								document.getElementById("foto1").addEventListener("click", function(){
								    foto1.open();
								}, false);
							</script>
						</dd>
					</dl>
					<dl>
						<dt>Descrição <span class="iconify" data-icon="bi:info-circle-fill" data-inline="true" style="color: #98928E;"></span></dt>
						<dd>
							<input type="text" name="legenda1" value="<?php echo $values['legenda1'];?>" class="obg" />
						</dd>
					</dl>
					<dl>	
						<?php
							if(is_object($cnt)) {
								if(!empty($cnt->foto2)) {
									$ft='https://res.cloudinary.com/infodental/image/upload/'.$cnt->foto2;
									$ftThumb='https://res.cloudinary.com/infodental/image/upload/c_thumb,w_100,g_face/'.$cnt->foto2;
									echo "<a href=\"".$ft."\" data-fancybox><img src=\"".$ftThumb."\" /></a>";
								} else {
									echo "<span class=\"botao\"><i class=\"icon-cancel\"></i> Sem imagem</span>";
								}
							}
						?>
					</dl>
					<dl>
						<dt>Foto 2 <span class="iconify" data-icon="bi:info-circle-fill" data-inline="true" style="color: #98928E;"></span>&nbsp;&nbsp;Dimensão: 750x</dt>
						<dd>
							<button id="foto2" onclick="return false;" class="cloudinary-button">Procurar foto</button>
							<input type="hidden" name="foto2" id="js-foto2" class="" />
							<script>
								var foto2 = cloudinary.createUploadWidget({
								  cloudName: 'infodental',
								  language: 'pt',
								  text: {
								    "pt": {
								        "local": {
											"browse": "Carregar arquivo",
											"main_title": "Enviar Arquivos",
											"dd_title_single": "Carregue e solte a imagem aqui",
											"dd_title_multi": "Carregue e solte imagens aqui",
											"drop_title_single": "Solte a foto para carregar",
											"drop_title_multiple": "Solte as fotos para carregar"
										}
								    }
								  },
								  multiple: false,
								  sources: ["local"],
								  folder: 'aclinica',
								  uploadPreset: 'ir9b4eem'}, (error, result) => {
								    if (!error && result && result.event === "success") {
								      console.log('Done! Here is the image info: ', result.info);
								      $("#js-foto2").val(result.info.path);
								    }
								  }
								)

								document.getElementById("foto2").addEventListener("click", function(){
								    foto2.open();
								}, false);
							</script>
						</dd>
					</dl>
					<dl>
						<dt>Descrição <span class="iconify" data-icon="bi:info-circle-fill" data-inline="true" style="color: #98928E;"></span></dt>
						<dd>
							<input type="text" name="legenda2" value="<?php echo $values['legenda2'];?>" class="" />
						</dd>
					</dl>
					<dl>	
						<?php
							if(is_object($cnt)) {
								if(!empty($cnt->foto2)) {
									$ft='https://res.cloudinary.com/infodental/image/upload/'.$cnt->foto2;
									$ftThumb='https://res.cloudinary.com/infodental/image/upload/c_thumb,w_100,g_face/'.$cnt->foto2;
									echo "<a href=\"".$ft."\" data-fancybox><img src=\"".$ftThumb."\" /></a>";
								} else {
									echo "<span class=\"botao\"><i class=\"icon-cancel\"></i> Sem imagem</span>";
								}
							}
						?>
					</dl>
					<dl>
						<dt>Foto 3 <span class="iconify" data-icon="bi:info-circle-fill" data-inline="true" style="color: #98928E;"></span>&nbsp;&nbsp;Dimensão: 750x</dt>
						<dd>
							<button id="foto3" onclick="return false;" class="cloudinary-button">Procurar foto</button>
							<input type="hidden" name="foto3" id="js-foto3" class="" />
							<script>
								var foto3 = cloudinary.createUploadWidget({
								  cloudName: 'infodental',
								  language: 'pt',
								  text: {
								    "pt": {
								        "local": {
											"browse": "Carregar arquivo",
											"main_title": "Enviar Arquivos",
											"dd_title_single": "Carregue e solte a imagem aqui",
											"dd_title_multi": "Carregue e solte imagens aqui",
											"drop_title_single": "Solte a foto para carregar",
											"drop_title_multiple": "Solte as fotos para carregar"
										}
								    }
								  },
								  multiple: false,
								  sources: ["local"],
								  folder: 'aclinica',
								  uploadPreset: 'ir9b4eem'}, (error, result) => {
								    if (!error && result && result.event === "success") {
								      console.log('Done! Here is the image info: ', result.info);
								      $("#js-foto3").val(result.info.path);
								    }
								  }
								)

								document.getElementById("foto3").addEventListener("click", function(){
								    foto3.open();
								}, false);
							</script>
						</dd>
					</dl>
					<dl>
						<dt>Descrição <span class="iconify" data-icon="bi:info-circle-fill" data-inline="true" style="color: #98928E;"></span></dt>
						<dd>
							<input type="text" name="legenda3" value="<?php echo $values['legenda3'];?>" class="" />
						</dd>
					</dl>
					
				</div>
			</section>

		</form>
	</section>
		
<?php
include "includes/footer.php";
?>