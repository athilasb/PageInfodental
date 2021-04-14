<?php
	$title="";
	include "includes/header.php";
	include "includes/nav.php";

	if($usr->tipo!="admin" and !in_array("landingpage",$_usuariosPermissoes)) {
		$jsc->jAlert("Você não tem permissão para acessar esta área!","erro","document.location.href='dashboard.php'");
		die();
	}
	$values=$adm->get($_GET);

	$_temas=array();
	$sql->consult($_p."landingpage_temas","*","where lixo=0 order by titulo asc");
	if($sql->rows) {
		while($x=mysqli_fetch_object($sql->mysqry)) {
			$_temas[$x->id]=$x;
		}
	}

?>
<section class="content">


			
	<?php
	$_table=$_p."landingpage_banner";
	$_page=basename($_SERVER['PHP_SELF']);
	$_dir="arqs/landingpages/banner/";
	$_width=1440;
	$_height=800;
	
	if(isset($_GET['form'])) {
		$cnt='';
		$campos=explode(",","titulo,descricao,video,id_tema,palavras");
		
		foreach($campos as $v) $values[$v]='';
		
		if(isset($_GET['id_tema']) and is_numeric($_GET['id_tema'])) $values['id_tema']=$_GET['id_tema'];
		
		if(isset($_GET['edita']) and is_numeric($_GET['edita'])) {
			$sql->consult($_table,"*","where id='".$_GET['edita']."'");
			if($sql->rows) {
				$cnt=mysqli_fetch_object($sql->mysqry);
				$values=$adm->values($campos,$cnt);
			} else {
				$jsc->jAlert("Informação não encontrada!","erro","document.location.href='".$_page."'");
				die();
			}
		}
		if(isset($_POST['acao']) and $_POST['acao']=="wlib") {

			$processa=true;

		/*	if((is_object($cnt) and $cnt->code!=$_POST['code']) or (empty($cnt))) {
				$sql->consult($_table,"*","where code='".addslashes($_POST['code'])."' and lixo=0");
				if($sql->rows) {
					$jsc->jAlert("Já existe tema com o endereço <b>".$_POST['code']."</b>","erro","");
					$processa=false;
				}
			}*/

			if($processa===true) {


				$vSQL= "";
				foreach($campos as $v) {
					$vSQL.=$v."='".utf8_decode(addslashes($_POST[$v]))."',";
					$values[$v]=$_POST[$v];
				}
				if(is_object($cnt)) {
					$vSQL.="id_alteracao=$usr->id,alteracao_data=now()";
					$vWHERE="where id='".$cnt->id."'";
					$sql->update($_table,$vSQL,$vWHERE);
					$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='update',vsql='".addslashes($vSQL)."',vwhere='".addslashes($vWHERE)."',tabela='".$_table."',id_reg='".$cnt->id."'");
					$id_reg=$cnt->id;
				} else {
					$vSQL.="data=now(),id_usuario=$usr->id";
					$sql->add($_table,$vSQL);
					$id_reg=$sql->ulid;
					$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='insert',vsql='".addslashes($vSQL)."',tabela='".$_table."',id_reg='".$sql->ulid."'");
				}

				$msgErro='';
				if(isset($_FILES['foto']) and !empty($_FILES['foto']['tmp_name'])) {
					$up=new Uploader();
					$up->uploadCorta("Banner",$_FILES['foto'],"",5242880*2,$_width,$_height,$_dir,$id_reg);

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
	<section class="filtros">
		<h1 class="filtros__titulo">Banner</h1>
		<div class="filtros-acoes">
			<a href="<?php echo $_page."?".$url;?>"><i class="iconify" data-icon="bx-bx-left-arrow-alt"></i></a>
			<?php
			if(is_object($cnt)) {
			?>		
			<a data-fancybox data-type="ajax" data-src="ajax/log.php?table=<?php echo $_table;?>&id=<?php echo $cnt->id;?>" href="javascript:;"><i class="iconify" data-icon="bx-bx-history"></i></a>
			<?php	
			}
			?>
			<a href="javascript:;" class="principal btn-submit"><i class="iconify" data-icon="bx-bx-check"></i></a>
			<?php if(is_object($cnt) and $usr->tipo=="admin") { ?>
			<a class="sec js-deletar" href="<?php echo $_page;?>?deleta=<?php echo $cnt->id."&".$url;?>"><i class="iconify" data-icon="bx-bx-trash"></i></a>
			<?php } ?>
		</div>
	</section>

	<section class="grid"> 			
		<form method="post" class="box form formulario-validacao"  autocomplete="off" enctype="multipart/form-data">
			<input type="hidden" name="acao" value="wlib" />
			<fieldset>
				<legend>Landing Page</legend>
				<dl>
					<dt>Tema</dt>
					<dd>
						<select name="id_tema" class="chosen">
							<option value=""></option>
							<?php
							foreach($_temas as $x) {
							?>
							<option value="<?php echo $x->id;?>"<?php echo (isset($values['id_tema']) and $x->id==$values['id_tema'])?' selected':'';?>><?php echo utf8_encode($x->titulo);?></option>
							<?php	
							}
							?>
						</select>
					</dd>
				</dl>
				<dl>
					<dt>Título do Banner</dt>
					<dd><input type="text" name="titulo" class="obg noupper" value="<?php echo $values['titulo'];?>" /></dd>
				</dl>
				<dl>
					<dt>Palavras de complemento do Título do Banner</dt>
					<dd><input type="text" name="palavras" class="obg noupper" value="<?php echo $values['palavras'];?>" /></dd>
					<dd><label><span class="iconify" data-icon="bi:info-circle-fill" data-inline="true"></span>&nbsp;&nbsp;Separado por vírgula. Ex.: Importante, Especial, Maravilhoso</label></dd>
				</dl>
				<dl>
					<dt>Descrição</dt>
					<dd><input type="text" name="descricao" class="noupper" value="<?php echo $values['descricao'];?>" /></dd>
				</dl>
				<?php
				if(is_object($cnt)) {
					$ft=$_dir.$cnt->id.".".$cnt->foto;
					if(file_exists($ft)) {	
				?>
				<dl>
					<dd><a href="<?php echo $ft;?>" data-fancybox><img src="<?php echo $ft;?>" width="200" style="border: solid 1px #CCC;padding:2px;" /></a></dd>
				</dl>
				<?php	
					}
				}
				?>
				<dl>
					<dt>Banner</dt>
					<dd><input type="file" name="foto" class="<?php echo empty($cnt)?"obg":"";?>" /></dd>
					<dd><label><span class="iconify" data-icon="bi:info-circle-fill" data-inline="true"></span>&nbsp;&nbsp;Dimensão: <?php echo $_width."x".$_height;?></label></dd>
				</dl>
				<dl>
					<dt>Vídeo</dt>
					<dd><textarea name="video" class="noupper" style="height: 150px;"><?php echo $values['video'];?></textarea></dd>
				</dl>
			</fieldset>

		</form>
	</section>


	<?php
	} else {
	?>
	<section class="filtros">
		<h1 class="filtros__titulo">Banner</h1>
		<form method="get" class="filtros-form">
			<input type="hidden" name="csv" value="0" />
			
			<dl class="dl3">
				<dt>Tema</dt>
				<dd>
					<select name="id_tema" class="chosen">
						<option value=""></option>
						<?php
						foreach($_temas as $x) {
						?>
						<option value="<?php echo $x->id;?>"<?php echo (isset($values['id_tema']) and $x->id==$values['id_tema'])?' selected':'';?>><?php echo utf8_encode($x->titulo);?></option>
						<?php	
						}
						?>
					</select>
				</dd>
			</dl>
			<button type="submit" class="filtros-form__button"><i class="iconify" data-icon="bx-bx-search"></i></button>
		</form>
		<div class="filtros-acoes">
			<a href="<?php echo $_page."?form=1&$url";?>" data-padding="0" class="adicionar tooltip" title="Adicionar"><i class="iconify" data-icon="bx-bx-plus"></i></a>
		</div>
	</section>
	<?php
		
	if(isset($_GET['deleta']) and is_numeric($_GET['deleta']) and $usr->tipo=="admin") {
		$vSQL="lixo='1'";
		$vWHERE="where id='".$_GET['deleta']."'";
		$sql->update($_table,$vSQL,$vWHERE);
		$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='delete',vsql='".addslashes($vSQL)."',vwhere='".addslashes($vWHERE)."',tabela='".$_table."',id_reg='".$_GET['deleta']."'");
		$jsc->jAlert("Registro excluído com sucesso!","sucesso","document.location.href='".$_page."?".$url."'");
		die();
	}
	
	
	$where="WHERE lixo='0'";

	if(isset($values['id_tema']) and is_numeric($values['id_tema'])) $where.=" and id_tema='".$values['id_tema']."'";
	
	$sql->consult($_table,"*",$where." order by titulo");
	
	?>
	<section class="grid">
		<div class="box registros">
			<div class="registros-qtd">
				<p class="registros-qtd__item"><?php echo $sql->rows;?> registros</p>
			</div>
			<table class="tablesorter">
				<thead>
					<tr>
						<th>Título do Banner</th>
						<th style="width:150px;">Banner</th>						
					</tr>
				</thead>
				<tbody>
				<?php
				while($x=mysqli_fetch_object($sql->mysqry)) {
				?>
				<tr onclick="document.location.href='<?php echo $_page;?>?form=1&edita=<?php echo $x->id."&".$url;?>'">
					<td><strong><?php echo utf8_encode($x->titulo);?></strong></td>
					<td>
						<?php
						$ft=$_dir.$x->id.".".$x->foto;
						if(file_exists($ft)) {
							echo '<img src="'.$ft.'" width="100" style="padding:3px;border:solid 1px #CCC;" />';
						}
						?>
					</td>					
				</tr>
				<?php
				}
				?>
				</tbody>
			</table>
		</div>
	</section>
	<?php
	}
	?>
</section>

<?php
	include "includes/footer.php";
?>