<?php
	$title="";
	include "includes/header.php";
	include "includes/nav.php";

	if($usr->tipo!="admin" and !in_array("landingpage",$_usuariosPermissoes)) {
		$jsc->jAlert("Você não tem permissão para acessar esta área!","erro","document.location.href='dashboard.php'");
		die();
	}
	$values=$adm->get($_GET);
?>
<section class="content">

	<?php
	require_once("includes/abaLandingPageCaptacao.php");
	$_table=$_p."landingpage_captacao_abandono";
	$_page=basename($_SERVER['PHP_SELF']);
	$_dir="arqs/landingpages/abandono/";
	$_width=900;
	$_height=450;

	
	$_temas=array();
	$sql->consult($_p."landingpage_temas","*","where lixo=0 order by titulo asc");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_temas[$x->id]=$x;
	}

	$cnt='';
	$campos=explode(",","texto,titulo,pub");
		
	foreach($campos as $v) $values[$v]='';
	
	$tema='';
	if(isset($values['id_tema']) and is_numeric($values['id_tema'])) {
		$sql->consult($_p."landingpage_temas","*","where id='".$values['id_tema']."'");
		if($sql->rows) {
			$tema=mysqli_fetch_object($sql->mysqry);
		}
	}

	if(is_object($tema)) {
		$sql->consult($_table,"*","where id_tema=$tema->id order by id desc");
		if($sql->rows==0) {
			$sql->add($_table,"id_tema=$tema->id");
			$id_reg=$sql->ulid;
			$sql->consult($_table,"*","where id=$id_reg");
			if($sql->rows) {
				$cnt=mysqli_fetch_object($sql->mysqry);
			}
		} else {
			$cnt=mysqli_fetch_object($sql->mysqry);
		}

		foreach($campos as $v) {
			$values[$v]=utf8_encode($cnt->$v);
		}
		
	}	
	?>

	<section class="filtros">
		<h1 class="filtros__titulo">Captação</h1>
		<div class="filtros-acoes">
			<?php
			if(is_object($cnt)) {
			?>			
			<a data-fancybox data-type="ajax" data-src="ajax/log.php?table=<?php echo $_table;?>&id=<?php echo $cnt->id;?>" href="javascript:;"><i class="iconify" data-icon="bx-bx-history"></i></a>
			<a href="javascript:;" class="principal btn-submit"><i class="iconify" data-icon="bx-bx-check"></i></a>
			<?php	
			}
			?>				
		</div>
	</section>


	<section class="grid">
		<form class="box form">
			<dl>
				<dt>Tema</dt>
				<dd>
					<select name="id_tema" onchange="document.location.href='?id_tema='+this.value">
						<option value="">-</option>
						<?php
						foreach($_temas as $v) echo '<option value="'.$v->id.'"'.(($tema->id==$v->id)?' selected':'').'>'.utf8_encode($v->titulo).'</option>';
						?>
					</select>
				</dd>
			</dl>
		</form>

		<?php
		if(is_object($tema)) {
			if(isset($_POST['acao']) and $_POST['acao']=="wlib") {

				$processa=true;


				if($processa===true) {


					$vSQL= "";
					foreach($campos as $v) {
						if($v=="pub") {
							$vSQL.=$v."='".((isset($_POST[$v]) and $_POST[$v]==1)?1:0)."',";

						} else {
							$vSQL.=$v."='".utf8_decode(addslashes($_POST[$v]))."',";
						}
						if(isset($_POST[$v])) $values[$v]=$_POST[$v];
					}

					if(isset($_POST['foto']) and !empty($_POST['foto'])) $vSQL.="foto='".$_POST['foto']."',";

					
					$vSQL.="id_alteracao=$usr->id,alteracao_data=now()";
					$vWHERE="where id='".$cnt->id."'";
					$sql->update($_table,$vSQL,$vWHERE);
					$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='update',vsql='".addslashes($vSQL)."',vwhere='".addslashes($vWHERE)."',tabela='".$_table."',id_reg='".$cnt->id."'");
					$id_reg=$cnt->id;
				

					$msgErro='';
					if(isset($_FILES['foto']) and !empty($_FILES['foto']['tmp_name'])) {
						$up=new Uploader();
						$up->uploadCorta("Imagem de Fundo",$_FILES['foto'],"",5242880*2,$_width,$_height,$_dir,$id_reg);

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
						$jsc->jAlert("Informações salvas com sucesso!","sucesso","document.location.href='".$_page."?form=1&edita=".$id_reg."&".$url."'");
						die();
					}
				}
			}
		?>
		<form method="post" class="box form formulario-validacao"  autocomplete="off" enctype="multipart/form-data">
			<input type="hidden" name="acao" value="wlib" />
			<dl>
				<dd><label><input type="checkbox" name="pub" class="noupper obg" value="1"<?php echo $values['pub']==1?" checked":"";?> /> Ativo</label></dd>
			</dl>

			<dl>
				<dt>Título</dt>
				<dd><input type="text" name="titulo" class="noupper obg" value="<?php echo $values['titulo'];?>" /></dd>
			</dl>

			<dl>	
				<?php
					if(is_object($cnt)) {
						if(!empty($cnt->foto)) {
							$ft='https://res.cloudinary.com/infodental/image/upload/'.$cnt->foto;
							$ftThumb='https://res.cloudinary.com/infodental/image/upload/c_thumb,w_100/'.$cnt->foto;
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
						  folder: 'captacao',
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
				<dt>Texto</dt>
				<dd><textarea name="texto" class="noupper" style="height:400px;"><?php echo $values['texto'];?></textarea></dd>
			</dl>
		</form>
	</section>
	<?php
	}
	?>	
</section>

<?php
	include "includes/footer.php";
?>