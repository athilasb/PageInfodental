<?php
	include "includes/header.php";
	include "includes/nav.php";

	$_table=$_p."landingpages_antesedepois";
	$_page=basename($_SERVER['PHP_SELF']);
	$_width=800;
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
		'foto_antes1' => array('titulo' => '1° Foto Antes', 'dir' => 'arqs/landingpages/antesedepois/antes/fotos1/', 'class' => 'obg', 'titulo_legenda' => 'Nome Paciente', 'legenda' => ''),
		'foto_depois1' => array('titulo' => '1° Foto Depois', 'dir' => 'arqs/landingpages/antesedepois/depois/fotos1/', 'class' => 'obg', 'titulo_legenda' => 'Nome Paciente', 'legenda' => 'nome_paciente1'),
		'foto_antes2' => array('titulo' => '2° Foto Antes', 'dir' => 'arqs/landingpages/antesedepois/antes/fotos2/', 'class' => '', 'titulo_legenda' => 'Nome Paciente', 'legenda' => ''),
		'foto_depois2' => array('titulo' => '2° Foto Depois', 'dir' => 'arqs/landingpages/antesedepois/depois/fotos2/', 'class' => '', 'titulo_legenda' => 'Nome Paciente', 'legenda' => 'nome_paciente2')
	);

	$campos=explode(",","id_tema,nome_paciente1,nome_paciente2");
	foreach($campos as $v) $values[$v]='';

	if(is_object($cnt)) {
		$values=$adm->values($campos,$cnt);
	}

	if(isset($_POST['acao']) and $_POST['acao']=="wlib") {
		$vSQL=$adm->vSQL($campos,$_POST);
		$values=$adm->values;
		$processa=true;

		if(isset($_POST['foto_antes1']) and !empty($_POST['foto_antes1'])) $vSQL.="foto_antes1='".$_POST['foto_antes1']."',";
		if(isset($_POST['foto_depois1']) and !empty($_POST['foto_depois1'])) $vSQL.="foto_depois1='".$_POST['foto_depois1']."',";
		if(isset($_POST['foto_antes2']) and !empty($_POST['foto_antes2'])) $vSQL.="foto_antes2='".$_POST['foto_antes2']."',";
		if(isset($_POST['foto_depois2']) and !empty($_POST['foto_depois2'])) $vSQL.="foto_depois2='".$_POST['foto_depois2']."',";

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
								<span class="badge">6</span> Escolha as fotos do antes e depois
							</div>
						</div>

						<div class="filter-group filter-group_right">
							<div class="filter-button">
								<?php if(is_object($cnt)){?><a href="?deletaAclinica=<?php echo $cnt->id."&".$url;?>" class="js-deletar"><i class="iconify" data-icon="bx-bx-trash"></i></a><?php }?>
								<a href="javascript:;" class="azul  btn-submit"><i class="iconify" data-icon="bx-bx-check"></i><span>salvar</span></a>
							</div>
						</div>

					</div>

					<dl>	
						<?php
							if(is_object($cnt)) {
								if(!empty($cnt->foto_antes1)) {
									$ft='https://res.cloudinary.com/infodental/image/upload/'.$cnt->foto_antes1;
									$ftThumb='https://res.cloudinary.com/infodental/image/upload/c_thumb,w_100,g_face/'.$cnt->foto_antes1;
									echo "<a href=\"".$ft."\" data-fancybox><img src=\"".$ftThumb."\" /></a>";
								} else {
									echo "<span class=\"botao\"><i class=\"icon-cancel\"></i> Sem imagem</span>";
								}
							}
						?>
					</dl>
					<dl>
						<dt>1° Foto Antes <span class="iconify" data-icon="bi:info-circle-fill" data-inline="true" style="color: #98928E;"></span>&nbsp;&nbsp;Dimensão: 800x</dt>
						<dd>
							<button id="foto_antes1" onclick="return false;" class="cloudinary-button">Procurar foto</button>
							<input type="hidden" name="foto_antes1" id="js-foto_antes1" class="" />
							<script>
								var foto_antes1 = cloudinary.createUploadWidget({
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
								  folder: 'antesedepois',
								  uploadPreset: 'ir9b4eem'}, (error, result) => {
								    if (!error && result && result.event === "success") {
								      console.log('Done! Here is the image info: ', result.info);
								      $("#js-foto_antes1").val(result.info.path);
								    }
								  }
								)

								document.getElementById("foto_antes1").addEventListener("click", function(){
								    foto_antes1.open();
								}, false);
							</script>
						</dd>
					</dl>
					<dl>	
						<?php
							if(is_object($cnt)) {
								if(!empty($cnt->foto_depois1)) {
									$ft='https://res.cloudinary.com/infodental/image/upload/'.$cnt->foto_depois1;
									$ftThumb='https://res.cloudinary.com/infodental/image/upload/c_thumb,w_100,g_face/'.$cnt->foto_depois1;
									echo "<a href=\"".$ft."\" data-fancybox><img src=\"".$ftThumb."\" /></a>";
								} else {
									echo "<span class=\"botao\"><i class=\"icon-cancel\"></i> Sem imagem</span>";
								}
							}
						?>
					</dl>
					<dl>
						<dt>1° Foto Depois <span class="iconify" data-icon="bi:info-circle-fill" data-inline="true" style="color: #98928E;"></span>&nbsp;&nbsp;Dimensão: 800x</dt>
						<dd>
							<button id="foto_depois1" onclick="return false;" class="cloudinary-button">Procurar foto</button>
							<input type="hidden" name="foto_depois1" id="js-foto_depois1" class="" />
							<script>
								var foto_depois1 = cloudinary.createUploadWidget({
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
								  folder: 'antesedepois',
								  uploadPreset: 'ir9b4eem'}, (error, result) => {
								    if (!error && result && result.event === "success") {
								      console.log('Done! Here is the image info: ', result.info);
								      $("#js-foto_depois1").val(result.info.path);
								    }
								  }
								)

								document.getElementById("foto_depois1").addEventListener("click", function(){
								    foto_depois1.open();
								}, false);
							</script>
						</dd>
					</dl>
					<dl>
						<dt>Nome Paciente <span class="iconify" data-icon="bi:info-circle-fill" data-inline="true" style="color: #98928E;"></span></dt>
						<dd>
							<input type="text" name="nome_paciente1" value="<?php echo $values['nome_paciente1'];?>" class="obg"/>
						</dd>
					</dl>
					<dl>	
						<?php
							if(is_object($cnt)) {
								if(!empty($cnt->foto_antes2)) {
									$ft='https://res.cloudinary.com/infodental/image/upload/'.$cnt->foto_antes2;
									$ftThumb='https://res.cloudinary.com/infodental/image/upload/c_thumb,w_100,g_face/'.$cnt->foto_antes2;
									echo "<a href=\"".$ft."\" data-fancybox><img src=\"".$ftThumb."\" /></a>";
								} else {
									echo "<span class=\"botao\"><i class=\"icon-cancel\"></i> Sem imagem</span>";
								}
							}
						?>
					</dl>
					<dl>
						<dt>2° Foto Antes <span class="iconify" data-icon="bi:info-circle-fill" data-inline="true" style="color: #98928E;"></span>&nbsp;&nbsp;Dimensão: 800x</dt>
						<dd>
							<button id="foto_antes2" onclick="return false;" class="cloudinary-button">Procurar foto</button>
							<input type="hidden" name="foto_antes2" id="js-foto_antes2" class="" />
							<script>
								var foto_antes2 = cloudinary.createUploadWidget({
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
								  folder: 'antesedepois',
								  uploadPreset: 'ir9b4eem'}, (error, result) => {
								    if (!error && result && result.event === "success") {
								      console.log('Done! Here is the image info: ', result.info);
								      $("#js-foto_antes2").val(result.info.path);
								    }
								  }
								)

								document.getElementById("foto_antes2").addEventListener("click", function(){
								    foto_antes2.open();
								}, false);
							</script>
						</dd>
					</dl>
					<dl>	
						<?php
							if(is_object($cnt)) {
								if(!empty($cnt->foto_depois2)) {
									$ft='https://res.cloudinary.com/infodental/image/upload/'.$cnt->foto_depois2;
									$ftThumb='https://res.cloudinary.com/infodental/image/upload/c_thumb,w_100,g_face/'.$cnt->foto_depois2;
									echo "<a href=\"".$ft."\" data-fancybox><img src=\"".$ftThumb."\" /></a>";
								} else {
									echo "<span class=\"botao\"><i class=\"icon-cancel\"></i> Sem imagem</span>";
								}
							}
						?>
					</dl>
					<dl>
						<dt>2° Foto Depois <span class="iconify" data-icon="bi:info-circle-fill" data-inline="true" style="color: #98928E;"></span>&nbsp;&nbsp;Dimensão: 800x</dt>
						<dd>
							<button id="foto_depois2" onclick="return false;" class="cloudinary-button">Procurar foto</button>
							<input type="hidden" name="foto_depois2" id="js-foto_depois2" class="" />
							<script>
								var foto_depois2 = cloudinary.createUploadWidget({
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
								  folder: 'antesedepois',
								  uploadPreset: 'ir9b4eem'}, (error, result) => {
								    if (!error && result && result.event === "success") {
								      console.log('Done! Here is the image info: ', result.info);
								      $("#js-foto_depois2").val(result.info.path);
								    }
								  }
								)

								document.getElementById("foto_depois2").addEventListener("click", function(){
								    foto_depois2.open();
								}, false);
							</script>
						</dd>
					</dl>
					<dl>
						<dt>Nome Paciente <span class="iconify" data-icon="bi:info-circle-fill" data-inline="true" style="color: #98928E;"></span></dt>
						<dd>
							<input type="text" name="nome_paciente2" value="<?php echo $values['nome_paciente2'];?>" class=""/>
						</dd>
					</dl>
					
				</div>
			</section>

		</form>
	</section>
		
<?php
include "includes/footer.php";
?>