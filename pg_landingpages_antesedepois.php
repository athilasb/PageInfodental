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

					<?php
					foreach($fotos as $k => $v) {
						if(is_object($cnt) and isset($cnt->$k)) {
							$ft = $v['dir'].$cnt->id.".".$cnt->$k;
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
						<dt><?php echo $v['titulo'];?> <span class="iconify" data-icon="bi:info-circle-fill" data-inline="true" style="color: #98928E;"></span>&nbsp;&nbsp;Dimensão: <?php echo $_width."x".$_height;?></dt>
						<dd><input type="file" name="<?php echo $k;?>" class="<?php echo empty($cnt)?$v['class']:"";?>" /></dd>
					</dl>
					<?php
							if(!empty($v['legenda'])) {
					?>
					<dl>
						<dt><?php echo $v['titulo_legenda'];?> <span class="iconify" data-icon="bi:info-circle-fill" data-inline="true" style="color: #98928E;"></span></dt>
						<dd>
							<input type="text" name="<?php echo $v['legenda'];?>" value="<?php echo $values[$v['legenda']];?>" class="<?php echo $v['class'];?>"/>
						</dd>
					</dl>
					<?php
							}
						}
					?>
				</div>
			</section>

		</form>
	</section>
		
<?php
include "includes/footer.php";
?>