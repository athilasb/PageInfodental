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
	$_table=$_p."landingpage_sobreaclinica";
	$_page=basename($_SERVER['PHP_SELF']);

	
	$cnt='';
	$campos=explode(",","telefone,whatsapp,instagram,facebook,twitter,linkedin,texto,endereco,mapa");
		
	foreach($campos as $v) $values[$v]='';
	

	$sql->consult($_table,"*","order by id desc");
	if($sql->rows) {
		$cnt=mysqli_fetch_object($sql->mysqry);

		foreach($campos as $v) {
			$values[$v]=utf8_encode($cnt->$v);
		}
	}
	
	if(isset($_POST['acao']) and $_POST['acao']=="wlib") {

		$processa=true;


		if($processa===true) {


			$vSQL= "";
			foreach($campos as $v) {
				$vSQL.=$v."='".utf8_decode(addslashes($_POST[$v]))."',";
				$values[$v]=$_POST[$v];
			}

			
			$vSQL.="id_alteracao=$usr->id,alteracao_data=now()";
			$vWHERE="where id='".$cnt->id."'";
			$sql->update($_table,$vSQL,$vWHERE);
			$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='update',vsql='".addslashes($vSQL)."',vwhere='".addslashes($vWHERE)."',tabela='".$_table."',id_reg='".$cnt->id."'");
			$id_reg=$cnt->id;
		

			$msgErro='';
			if(isset($_FILES['foto']) and !empty($_FILES['foto']['tmp_name'])) {
				$up=new Uploader();
				$up->uploadCorta("Imagem Inicial",$_FILES['foto'],"",5242880*2,$_width,'',$_dir,$id_reg);

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
	
	<div class="filtros">
		<div class="filtros__titulo">Sobre a Clínica</div>
		<div class="filtros-acoes">
			<?php
			if(is_object($cnt)) {
			?>		
			<a data-fancybox data-type="ajax" data-src="ajax/log.php?table=<?php echo $_table;?>&id=<?php echo $cnt->id;?>" href="javascript:;"><i class="iconify" data-icon="bx-bx-history"></i></a>
			<?php	
			}
			?>
			<a href="javascript:;" class="principal btn-submit"><i class="iconify" data-icon="bx-bx-check"></i></a>
		</div>
	</div>

	<script>
		$(function(){
			var fck_texto = CKEDITOR.replace('texto',{
    							filebrowserUploadUrl: '/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Files',
									height: '350',
									width:'100%',
									language: 'pt-br'
								});
			CKFinder.setupCKEditor(fck_texto);
		});
	</script>

	<section class="grid">
		<div class="box">

			<form method="post" class="form formulario-validacao"  autocomplete="off" enctype="multipart/form-data">
				<input type="hidden" name="acao" value="wlib" />
				<fieldset>
					<legend>Landing Page</legend>
			
					<div class="colunas4">
						<dl>
							<dt>Telefone</dt>
							<dd>
								<input type="text" name="telefone" value="<?php echo $values['telefone'];?>"  class="obg noupper celular" />
							</dd>
						</dl>
						<dl>
							<dt>Whatsapp</dt>
							<dd>
								<input type="text" name="whatsapp" value="<?php echo $values['whatsapp'];?>"  class="obg noupper celular" />
							</dd>
						</dl>
						
					</div>
					<div class="colunas4">
						<dl>
							<dt>Instagram</dt>
							<dd>
								<input type="text" name="instagram" value="<?php echo $values['instagram'];?>"  class="noupper" />
							</dd>
						</dl>
						<dl>
							<dt>Facebook</dt>
							<dd>
								<input type="text" name="facebook" value="<?php echo $values['facebook'];?>"  class="noupper" />
							</dd>
						</dl>
						<dl>
							<dt>Twitter</dt>
							<dd>
								<input type="text" name="twitter" value="<?php echo $values['twitter'];?>"  class="noupper" />
							</dd>
						</dl>
						<dl>
							<dt>Linkedin</dt>
							<dd>
								<input type="text" name="linkedin" value="<?php echo $values['linkedin'];?>"  class="noupper" />
							</dd>
						</dl>
					</div>

					<dl>
						<dt>Endereço</dt>
						<dd>
							<input type="text" name="endereco" value="<?php echo $values['endereco'];?>" maxlength="140"  class="noupper" />
						</dd>
					</dl>

					<dl>
						<dt>Mapa da Localização</dt>
						<dd>
							<input type="text" name="mapa" class="noupper" value="<?php echo $values['mapa'];?>" />
						</dd>
					</dl>


					<dl>
						<dt>Texto</dt>
						<dd><textarea name="texto" class="noupper" style="height:400px;"><?php echo $values['texto'];?></textarea></dd>
					</dl>
				</fieldset>
		
			</form>
		</div>
	</section>
</section>
			

<?php
	include "includes/footer.php";
?>