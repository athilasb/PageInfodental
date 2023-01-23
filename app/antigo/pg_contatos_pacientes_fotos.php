<?php
	$title="";
	include "includes/header.php";
	include "includes/nav.php";

	if($usr->tipo!="admin" and !in_array("produtos",$_usuariosPermissoes)) {
		$jsc->jAlert("Você não tem permissão para acessar esta área!","erro","document.location.href='dashboard.php'");
		die();
	}
	$values=$adm->get($_GET);
?>
<section class="content">

	<header class="caminho">
		<h1 class="caminho__titulo">Contatos <i class="iconify" data-icon="bx-bx-chevron-right"></i> Pacientes <i class="iconify" data-icon="bx-bx-chevron-right"></i> <strong>Fotos</strong></h1>
		<a href="javascript:;" class="caminho__tutorial button button__sec"><i class="iconify" data-icon="ic-baseline-slow-motion-video"></i> ASSISTIR TUTORIAL</a>
	</header>

	<section class="content-grid">

		<section class="content__item">
	
	<?php
	$_table=$_p."pacientes_fotos";
	$_page=basename($_SERVER['PHP_SELF']);

	$_width=800;
	$_height="";
	$_dir="arqs/pacientes/fotos/";
	
	$_pacientes=array();
	$sql->consult($_p."pacientes","*","where lixo=0 order by nome asc");
	if($sql->rows) {
		while($x=mysqli_fetch_object($sql->mysqry)) {
			$_pacientes[$x->id]=$x;
		}
	}

	if(isset($_GET['form'])) {
		$cnt='';
		$campos=explode(",","data,id_paciente,foto,legenda");
		
		foreach($campos as $v) $values[$v]='';
		$values['data'] = date("%d/%m/%Y %H:%i");
		if(isset($_GET['id_paciente']) and is_numeric($_GET['id_paciente'])) $values['id_paciente']=$_GET['id_paciente'];
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
			$vSQL=$adm->vSQL($campos,$_POST);
			$values=$adm->values;
			$processa=true;
			
			//if(empty($cnt)) $vSQL.="code='".codeIn($_table,outUrl(utf8_encode($_POST['titulo'])))."',";
			//else $vSQL.="code='".codeIn2($_table,outUrl(utf8_encode($_POST['titulo'])),$cnt->id)."',";
		
			if($processa===true) {	
			
				if(is_object($cnt)) {
					$vSQL=substr($vSQL,0,strlen($vSQL)-1);
					$vWHERE="where id='".$cnt->id."'";
					$sql->update($_table,$vSQL,$vWHERE);
					$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='update',vsql='".addslashes($vSQL)."',vwhere='".addslashes($vWHERE)."',tabela='".$_table."',id_reg='".$cnt->id."'");
					$id_reg=$cnt->id;
				} else {
					$vSQL=substr($vSQL,0,strlen($vSQL)-1);
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
			<div class="acoes">
				<a href="<?php echo $_page."?".$url;?>" class="button button__lg button__ter"><i class="iconify" data-icon="bx-bx-left-arrow-alt"></i></a>
				<?php 
				if(is_object($cnt)) {
				?>
				<a data-fancybox data-type="ajax" data-src="ajax/log.php?table=<?php echo $_table;?>&id=<?php echo $cnt->id;?>" href="javascript:;" class="button button__lg button__ter"><span class="iconify" data-icon="fa:history" data-inline="false"></span> Logs</a>
				<?php
				}
				?>
				<a href="javascript:;" class="button button__lg btn-submit"><i class="iconify" data-icon="bx-bx-check"></i> Salvar</a>			
			</div>

			<form method="post" class="form formulario-validacao"  autocomplete="off" enctype="multipart/form-data">
				<input type="hidden" name="acao" value="wlib" />
				<fieldset>
					<legend>Fotos</legend>
					
					<div class="colunas4">
						<dl>
							<dt>Data</dt>
							<dd>
								<input type="text" name="data" value="<?php echo $values['data'];?>"  class="datahora datepicker obg" />
							</dd>
						</dl>
					</div>
					<div>
						<dl>
							<dt>Paciente</dt>
							<dd>
								<select name="id_paciente" class="obg chosen">
									<option value=""></option>
									<?php
									foreach($_pacientes as $x) {
									?>
									<option value="<?php echo $x->id;?>"<?php echo $x->id==$values['id_paciente']?' selected':'';?>><?php echo utf8_encode($x->nome);?></option>
									<?php	
									}
									?>
								</select>
							</dd>
						</dl>
						<dl>
							<?php
							if(is_object($cnt)) {
								$ft = $_dir.$cnt->id.".".$cnt->foto;
								if(file_exists($ft)) {
									echo "<a href=\"".$ft."\" data-fancybox><img style='width: 120px' src=\"".$ft."\" /></a>";
								} else {
									echo "<span class=\"botao\"><i class=\"icon-cancel\"></i> Sem imagem</span>";
								}
							}
							?>
						</dl>
						<dl>
							<dt>Foto</dt>
							<dd>
								<input type="file" name="foto" class="<?php echo is_object($cnt)?"":"obg";?>" />
							</dd>
						</dl>
						<dl>
							<dt>Legenda</dt>
							<dd>
								<input type="text" name="legenda" value="<?php echo $values['legenda'];?>" maxlength="240" class="noupper" />
							</dd>
						</dl>
					</div>
				</fieldset>
			</form>

	
	<?php
	} else {
		
		$paciente='';
		if(isset($values['id_paciente']) and is_numeric($values['id_paciente'])) {
			$sql->consult($_p."pacientes","*","where id='".$values['id_paciente']."'");
			if($sql->rows) {
				$paciente=mysqli_fetch_object($sql->mysqry);
			}
		}

		if(empty($paciente)) {
			$jsc->go("pg_contatos_pacientes.php");
			die();
		}
 	?>
			<div class="box-botoes clearfix">
				<a href="pg_contatos_pacientes.php?form=1&edita=<?php echo $values['id_paciente'];?>" class="button button__ter"><i class="iconify" data-icon="bx-bx-left-arrow-alt"></i></a>
			</div>

			<section class="filtros">
				<form method="get" class="filtros-form form">
					<div class="colunas4">
						<dl class="dl2">
							<dt>Paciente</dt>
							<dd>
								<select name="id_paciente" class="obg chosen">
									<option value=""></option>
									<?php
									foreach($_pacientes as $x) {
									?>
									<option value="<?php echo $x->id;?>"<?php echo (isset($values['id_paciente']) and $x->id==$values['id_paciente'])?' selected':'';?>><?php echo utf8_encode($x->nome);?></option>
									<?php	
									}
									?>
								</select>
							</dd>
						</dl>
						<dl>		
							<dt>&nbsp;</dt>			
							<dd><button type="submit" class="button button__sec"><i class="iconify" data-icon="bx-bx-search" data-inline="false"></i></button></dd>
						</dl>
					</div>
				</form>

				<div class="filtros-acoes">
					<a href="<?php echo $_page."?form=1&$url";?>" data-padding="0" class="filtros-acoes__button tooltip" title="adicionar"><i class="iconify" data-icon="ic-baseline-add"></i></a>
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
			if(isset($values['id_paciente']) and is_numeric($values['id_paciente'])) $where.=" and id_paciente='".utf8_decode($values['id_paciente'])."'";
			
			$sql->consult($_table,"*,date_format(data,'%d/%m/%Y %H:%i') as dataf",$where." order by data desc,id desc");
			?>
			<div class="registros-qtd">
				<p class="registros-qtd__item"><?php echo $sql->rows;?> fotos</p>
			</div>
			<div class="registros">

				<table class="tablesorter">
					<thead>
						<tr>
							<th style="width:150px">Data</th>
							<th>Paciente</th>
							<th>Foto</th>
							<th>Legenda</th>
							<th style="width:130px;">Ações</th>
						</tr>
					</thead>
					<tbody>
					<?php
					while($x=mysqli_fetch_object($sql->mysqry)) {
					?>
					<tr>
						<td><?php echo utf8_encode($x->dataf);?></td>
						<td><?php echo isset($_pacientes[$x->id_paciente])?utf8_encode($_pacientes[$x->id_paciente]->nome):"-";?></td>
						<td style="text-align: center;">
							<?php
								$ft = $_dir.$x->id.".".$x->foto;
								if(file_exists($ft))
									echo "<a href='$ft' data-fancybox><img style='height:30px;' src=\"".$ft."\" style=\"\" /></a>";
								else 
									echo "<span class=\"botao\"><i class=\"icon-cancel\"></i> Sem imagem</span>";
							?>	
						</td>
						<td><?php echo utf8_encode($x->legenda);?></td>
						<td>
							<a href="<?php echo $_page;?>?form=1&edita=<?php echo $x->id."&".$url;?>" class="registros__acao"><i class="iconify" data-icon="bx:bxs-edit-alt"></i></a>
							<?php if($usr->tipo=="admin") { ?><a href="<?php echo $_page;?>?deleta=<?php echo $x->id."&".$url;?>" class="registros__acao registros__acao_sec js-deletar"><i class="iconify" data-icon="bx:bxs-trash"></i></a><?php } ?>
						</td>
					</tr>
					<?php
					}
					?>
					</tbody>
				</table>
			</div>
	
	<?php
	}
	?>

</section>

<?php
	include "includes/footer.php";
?>