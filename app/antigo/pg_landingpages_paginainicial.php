<?php
	include "includes/header.php";
	include "includes/nav.php";

	$_table=$_p."landingpage_banner";
	$_page=basename($_SERVER['PHP_SELF']);
	$_dir="arqs/landingpages/banner/";
	$_width=1440;
	$_height=800;

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

	$campos=explode(",","titulo,descricao,video,id_tema,palavras");
	foreach($campos as $v) $values[$v]='';

	if(is_object($cnt)) {
		$values=$adm->values($campos,$cnt);
	}

	if(isset($_POST['acao']) and $_POST['acao']=="wlib") {
		$vSQL=$adm->vSQL($campos,$_POST);
		$values=$adm->values;
		$processa=true;

		if(isset($_POST['foto']) and !empty($_POST['foto'])) $vSQL.="foto='".$_POST['foto']."',";
		else if(empty($cnt)) $vSQL.="foto='',";

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
			if(isset($_FILES['foto']) and !empty($_FILES['foto']['tmp_name'])) { 
				$up=new Uploader();
				$up->uploadCorta("Imagem",$_FILES['foto'],"",5242880*2,$_width,$_height,$_dir,$id_reg);

				if($up->erro) {
					$msgErro=$up->resul;
				} else {
					$ext=$up->ext;
					$vSQL="foto='".$ext."'";
					$vWHERE="where id='".$id_reg."'";
					$sql->update($_table,$vSQL,$vWHERE);
					$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='update',vsql='".addslashes($vSQL)."',tabela='".$_table."',id_reg='".$id_reg."'");
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
								<span class="badge">2</span> Preencha os dados do Banner
							</div>
						</div>

						<div class="filter-group filter-group_right">
							<div class="filter-button">
								<?php if(is_object($cnt)){?><a href="?deletaPaginainicial=<?php echo $cnt->id."&".$url;?>" class="js-deletar"><i class="iconify" data-icon="bx-bx-trash"></i></a><?php }?>
								<a href="javascript:;" class="azul  btn-submit"><i class="iconify" data-icon="bx-bx-check"></i><span>salvar</span></a>
							</div>
						</div>

					</div>
					<div class="colunas4">
						<dl class="dl2">
							<dt>Título da Página <span class="iconify" data-icon="bi:info-circle-fill" data-inline="true" style="color: #98928E;"></span></dt>
							<dd>
								<input type="text" name="titulo" value="<?php echo $values['titulo'];?>" class="obg"/>
							</dd>
						</dl>
						<dl class="dl2">
							<dt>Subtítulo Dinâmico <span class="iconify" data-icon="bi:info-circle-fill" data-inline="true" style="color: #98928E;"></span></dt>
							<dd>
								<input type="text" name="palavras" value="<?php echo $values['palavras'];?>"  class="obg noupper" />
							</dd>
						</dl>	
					</div>
					<dl>
						<dt>Descrição <span class="iconify" data-icon="bi:info-circle-fill" data-inline="true" style="color: #98928E;"></span></dt>
						<dd><input type="text" name="descricao" class="noupper" value="<?php echo $values['descricao'];?>" /></dd>
					</dl>
					<dl>	
						<?php
							if(is_object($cnt)) {
								if(!empty($cnt->foto)) {
									$ft='https://res.cloudinary.com/infodental/image/upload/'.$cnt->foto;
									$ftThumb='https://res.cloudinary.com/infodental/image/upload/c_thumb,w_100,g_face/'.$cnt->foto;
									echo "<a href=\"".$ft."\" data-fancybox><img src=\"".$ftThumb."\" /></a>";
								} else {
									echo "<span class=\"botao\"><i class=\"icon-cancel\"></i> Sem imagem</span>";
								}
							}
						?>
					</dl>
					<dl>
						<dt><span class="iconify" data-icon="bi:info-circle-fill" data-inline="true" style="color: #98928E;"></span>&nbsp;&nbsp;Dimensão: <?php echo $_width."x".$_height;?></dt>
						<dd>
							<button id="upload_widget" onclick="return false;" class="cloudinary-button">Procurar foto</button>
							<input type="hidden" name="foto" id="js-cloudinary" class="<?php echo is_object($cnt)?"":"obg";?>" />
							<script>
								var myWidget = cloudinary.createUploadWidget({
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
								  folder: 'home',
								  uploadPreset: 'ir9b4eem'}, (error, result) => {
								    if (!error && result && result.event === "success") {
								      console.log('Done! Here is the image info: ', result.info);
								      $("#js-cloudinary").val(result.info.path);
								    }
								  }
								)

								document.getElementById("upload_widget").addEventListener("click", function(){
								    myWidget.open();
								}, false);
							</script>
						</dd>
					</dl>
					<dl>
						<dt>Vídeo <span class="iconify" data-icon="bi:info-circle-fill" data-inline="true" style="color: #98928E;"></span></dt>
						<dd><textarea name="video" class="noupper" style="height: 150px;"><?php echo $values['video'];?></textarea></dd>
						<dd><label><span class="iconify" data-icon="bi:info-circle-fill" data-inline="true"></span></label></dd>
					</dl>
				</div>
			</section>

		</form>
	</section>
		
<?php
include "includes/footer.php";
?>