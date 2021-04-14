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
	$_table=$_p."landingpage_temas";
	$_page=basename($_SERVER['PHP_SELF']);
	$_width=800;
	$_height='';
	$_dir="arqs/landingpages/whatsapp/";

	$sql = new Mysql(true);
	
	if(isset($_GET['form'])) {
		$cnt='';
		$campos=explode(",","titulo,cor_primaria,cor_secundaria,descricao,menu,whatsapp_mensagem,codigo_head,codigo_body");
		
		foreach($campos as $v) $values[$v]='';
		$values['code']= '';
		
		if(isset($_GET['edita']) and is_numeric($_GET['edita'])) {
			$sql->consult($_table,"*","where id='".$_GET['edita']."'");
			if($sql->rows) {
				$cnt=mysqli_fetch_object($sql->mysqry);
				//$values=$adm->values($campos,$cnt);
				foreach($campos as $v) {
					$values[$v]=$cnt->$v;
				}
				$values['code']=$cnt->code;
			} else {
				$jsc->jAlert("Informação não encontrada!","erro","document.location.href='".$_page."'");
				die();
			}
		}
		if(isset($_POST['acao']) and $_POST['acao']=="wlib") {

			$processa=true;

			if((is_object($cnt) and $cnt->code!=$_POST['code']) or (empty($cnt))) {
				$sql->consult($_table,"*","where code='".addslashes($_POST['code'])."' and lixo=0");
				if($sql->rows) {
					$jsc->jAlert("Já existe tema com o endereço <b>".$_POST['code']."</b>","erro","");
					$processa=false;
				}
			}

			if($processa===true) {


				$vSQL= "";
				foreach($campos as $v) {
					if($v=="whatsapp_mensagem") $vSQL.=$v."='".(addslashes($_POST[$v]))."',";
					else $vSQL.=$v."='".utf8_decode(addslashes($_POST[$v]))."',";
				//	$vSQL.=$v."='".(addslashes($_POST[$v]))."',";
					$values[$v]=$_POST[$v];
				}
				$values['code']=$_POST['code'];

				$vSQL.="code='".addslashes($_POST['code'])."',";
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
				if(isset($_FILES['whatsapp_banner']) and !empty($_FILES['whatsapp_banner']['tmp_name'])) {
					$up=new Uploader();
					$up->uploadCorta("Banner",$_FILES['whatsapp_banner'],"",5242880*2,$_width,'',$_dir,$id_reg);

					if($up->erro) {
						$msgErro=$up->resul;
					} else {
						$ext=$up->ext;
						$vSQL="whatsapp_banner='".$ext."'";
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
	<script type="text/javascript">
		
		$(function(){
			<?php
			if(empty($cnt)) {
			?>
			$('input[name=titulo]').keyup(function(){
				let code = retira_acentos($(this).val().toLowerCase().split(' ').join('-'));
				$('input[name=code]').val(code);
			});
			<?php
			}
			?>
		})
	</script>

	<section class="filtros">		
		<h1 class="filtros__titulo">Temas</h1>
		<div class="filtros-acoes">
			<a href="<?php echo $_page."?".$url;?>"><i class="iconify" data-icon="bx-bx-left-arrow-alt"></i></a>
			<?php if(is_object($cnt)) { ?>	
			<a class="" data-fancybox data-type="ajax" data-src="ajax/log.php?table=<?php echo $_table;?>&id=<?php echo $cnt->id;?>" href="javascript:;"><i class="iconify" data-icon="bx-bx-history"></i></a>
			<?php } ?>
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
					<dt>Nome do Tema</dt>
					<dd>
						<input type="text" name="titulo" value="<?php echo $values['titulo'];?>"  class="obg noupper" />
					</dd>
				</dl>
				<dl>
					<dt>Título do Menu</dt>
					<dd>
						<input type="text" name="menu" value="<?php echo $values['menu'];?>"  class="obg noupper" />
					</dd>
				</dl>
				<dl>
					<dt>Endereço do Tema</dt>
					<dd>
						<input type="text" name="code" value="<?php echo $values['code'];?>"  class="obg noupper" />
					</dd>
				</dl>	
				<dl>
					<dt>Descrição</dt>
					<dd>
						<input type="text" name="descricao" value="<?php echo $values['descricao'];?>"  class="obg noupper" maxlength="200" />
					</dd>
				</dl>	
				<div class="colunas4">
					<dl>
						<dt>Cor Primária</dt>
						<dd><input type="text" name="cor_primaria" value="<?php echo $values['cor_primaria'];?>" class="obg" /></dd>
					</dl>
					<dl>
						<dt>Cor Secundária</dt>
						<dd><input type="text" name="cor_secundaria" value="<?php echo $values['cor_secundaria'];?>" class="obg" /></dd>
					</dl>
				</div>
			</fieldset>

			<fieldset>
				<legend>Whatsapp</legend>
				<dl>
					<dt>Mensagem</dt>
					<dd><textarea name="whatsapp_mensagem" class="noupper" style="height: 150px;"><?php echo $values['whatsapp_mensagem'];?></textarea></dd>
				</dl>
				<?php
				if(is_object($cnt)) {
				?>
				<dl>
					<dd>
				<?php
					$ft=$_dir.$cnt->id.".".$cnt->whatsapp_banner;
					if(file_exists($ft)) echo '<a href="'.$ft."?".date('H:is').'" data-fancybox><img src="'.$ft."?".date('H:is').'" width="100" /></a>';
					else echo '<font color=red>Nenhum banner anexado</font>';
				}
				?>
					</dd>
				</dl>
				<dl>
					<dt>Banner</dt>
					<dd><input type="file" name="whatsapp_banner" /></dd>
				</dl>
			</fieldset>

			<fieldset>
				<legend>Códigos de Rastreamento</legend>

				<dl>
					<dt>Cabeçalho (head)</dt>
					<dd>
						<textarea name="codigo_head" style="height: 200px;" class="noupper"><?php echo $values['codigo_head'];?></textarea>
					</dd>
				</dl>

				<dl>
					<dt>Cabeçalho (body)</dt>
					<dd>
						<textarea name="codigo_body" style="height: 200px;" class="noupper"><?php echo $values['codigo_body'];?></textarea>
					</dd>
				</dl>
			</fieldset>
		</form>
	</section>
	<?php
	} else {
	?>
			
	<section class="filtros">
		<h1 class="filtros__titulo">Temas</h1>
		<form method="get" class="filtros-form">
			<input type="hidden" name="csv" value="0" />
			<dl>
				<dt>Palavra-chave</dt>
				<dd><input type="text" name="busca" value="<?php echo isset($values['busca'])?$values['busca']:"";?>" /></dd>
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
	if(isset($values['busca']) and !empty($values['busca'])) $where.=" and titulo like '%".$values['busca']."%'";
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
						<th>Nome do Tema</th>
						<th>Endereço</th>
					</tr>
				</thead>
				<tbody>
				<?php
				while($x=mysqli_fetch_object($sql->mysqry)) {
				?>
				<tr onclick="document.location.href='<?php echo $_page;?>?form=1&edita=<?php echo $x->id."&".$url;?>'">
					<td><strong><?php echo utf8_encode($x->titulo);?></strong></td>
					<td><?php echo $x->code;?></td>					
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