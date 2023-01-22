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
	require_once("includes/abaLandingPageInformacoes.php");
	$_table=$_p."landingpage_informacoes_apresentacao";
	$_page=basename($_SERVER['PHP_SELF']);

	
	$_temas=array();
	$sql->consult($_p."landingpage_temas","*","where lixo=0 order by titulo asc");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_temas[$x->id]=$x;
	}

	$cnt='';
	$campos=explode(",","texto,titulo_destaque,titulo");
		
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
	}
		
	?>
	
	<div class="filtros">
		<h1 class="filtros__titulo">Informações</h1>
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
	</div>

	<div class="grid">
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
		foreach($campos as $v) {
			$values[$v]=utf8_encode($cnt->$v);
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
		<form method="post" class="box form formulario-validacao"  autocomplete="off" enctype="multipart/form-data">
			<input type="hidden" name="acao" value="wlib" />
			<script>
				$(function(){
					var fck_texto = CKEDITOR.replace('texto',{
		    							filebrowserUploadUrl: '/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Files',
											height: '350',
											width: '100%',
											language: 'pt-br'
										});
					CKFinder.setupCKEditor(fck_texto);
				});
			</script>
			<dl>
				<dt>Título do Destaques</dt>
				<dd><input type="text" name="titulo_destaque" class="noupper" value="<?php echo $values['titulo_destaque'];?>" /></dd>
			</dl>

			<dl>
				<dt>Título da Apresentação</dt>
				<dd><input type="text" name="titulo" class="noupper" value="<?php echo $values['titulo'];?>" /></dd>
			</dl>

			<dl>
				<dt>Texto</dt>
				<dd><textarea name="texto" id="texto" class="noupper" style="height:400px;"><?php echo $values['texto'];?></textarea></dd>
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