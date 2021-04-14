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
<script>
	$(function(){
		$('.m-produtos').next().show();		
	});
</script>
<section id="conteudo">
	
	<div class="box-caminho">
		<a href="javascript" class="js-collapse"><span></span></a>
		<h1>Produtos <i class="icon-angle-right"></i> Subcategorias</h1>
	</div>
	
	<?php
	$_table=$_p."produtos_subcategorias";
	$_page=basename($_SERVER['PHP_SELF']);

	$_empresas=array();
	$sql->consult($_p."empresas","*","where lixo=0");
	if($sql->rows) {
		while($x=mysqli_fetch_object($sql->mysqry)) {
			$_empresas[$x->id]=$x;
		}
	}

	$_categorias=array();
	$sql->consult($_p."produtos_categorias","*","where lixo=0 order by titulo asc");
	if($sql->rows) {
		while($x=mysqli_fetch_object($sql->mysqry)) {
			$_categorias[$x->id]=$x;
		}
	}

	$_width="";
	$_height="";
	$_dir="";
	
	if(isset($_GET['form'])) {
		$cnt='';
		$campos=explode(",","data,id_empresa,id_categoria,titulo");
		
		foreach($campos as $v) $values[$v]='';
		$values['data'] = date("%d/%m/%Y %H:%i");
		
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
	<div class="box-botoes clearfix">
		<a href="<?php echo $_page."?".$url;?>" class="botao"><i class="icon-left-big"></i> Voltar</a>
		<?php if(is_object($cnt)) {?><a data-fancybox data-type="ajax" data-src="ajax/log.php?table=<?php echo $_table;?>&id=<?php echo $cnt->id;?>" href="javascript:;" class="botao" ><i class="icon-info-circled"></i> Logs</a><?php } ?>
		<a href="javascript://" class="botao botao-principal btn-submit"><i class="icon-ok"></i> Salvar</a>

	</div>
	<div class="box-form">

		<form method="post" class="formulario-validacao"  autocomplete="off" enctype="multipart/form-data">
			<input type="hidden" name="acao" value="wlib" />
			<fieldset>
				<legend>Dados da Subcategoria</legend>

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
						<dt>Empresa</dt>
						<dd>
							<select name="id_empresa" class="obg chosen">
								<option value=""></option>
								<?php
								foreach($_empresas as $x) {
								?>
								<option value="<?php echo $x->id;?>"<?php echo $x->id==$values['id_empresa']?' selected':'';?>><?php echo utf8_encode($x->razao_social);?></option>
								<?php	
								}
								?>
							</select>
						</dd>
					</dl>
					<dl>
						<dt>Categoria</dt>
						<dd>
							<select name="id_categoria" class="obg chosen">
								<option value=""></option>
								<?php
								foreach($_categorias as $x) {
								?>
								<option value="<?php echo $x->id;?>"<?php echo $x->id==$values['id_categoria']?' selected':'';?>><?php echo utf8_encode($x->titulo);?></option>
								<?php	
								}
								?>
							</select>
						</dd>
					</dl>
					<dl>
						<dt>Título</dt>
						<dd>
							<input type="text" name="titulo" value="<?php echo $values['titulo'];?>"  class="obg" />
						</dd>
					</dl>	
				</div>
			</fieldset>
	
		</form>

	</div>
	<?php
	} else {
	
	?>
	<div class="box-botoes clearfix">
		<a href="<?php echo $_page;?>?form=1<?php echo "&".$url;?>" class="botao botao-principal"><i class="icon-plus"></i> Adicionar</a>
	</div>


	<div class="box-filtros clearfix">
		<form method="get" class="formulario-validacao js-filtro">
			<input type="hidden" name="csv" value="0" />
			<div class="colunas4">
				<dl>
					<dt>Empresa</dt>
					<dd>
						<select name="id_empresa" class="chosen">
							<option value=""></option>
							<?php
							foreach($_empresas as $x) {
							?>
							<option value="<?php echo $x->id;?>"<?php echo (isset($values['id_empresa']) and $x->id==$values['id_empresa'])?' selected':'';?>><?php echo utf8_encode($x->razao_social);?></option>
							<?php	
							}
							?>
						</select>
					</dd>
				</dl>
				<dl>
					<dt>Categoria</dt>
					<dd>
						<select name="id_categoria" class="chosen">
							<option value=""></option>
							<?php
							foreach($_categorias as $x) {
							?>
							<option value="<?php echo $x->id;?>"<?php echo (isset($values['id_categoria']) and $x->id==$values['id_categoria'])?' selected':'';?>><?php echo utf8_encode($x->titulo);?></option>
							<?php	
							}
							?>
						</select>
					</dd>
				</dl>
			</div>
			<dl>		
				<dt>&nbsp;</dt>			
				<dd><button type="submit"><i class="icon-search"></i> Filtrar</button></dd>
			</dl>
		</form>
	</div>

	<div class="box-registros">
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

		if(isset($values['id_empresa']) and is_numeric($values['id_empresa'])) $where.=" and id_empresa='".$values['id_empresa']."'";
		if(isset($values['id_categoria']) and is_numeric($values['id_categoria'])) $where.=" and id_categoria='".$values['id_categoria']."'";
		
		if($usr->login=="wlib" and isset($_GET['cmd'])) echo $where;
		$sql->consult($_table,"*,date_format(data,'%d/%m/%Y %H:%i') as dataf",$where." order by data desc,id desc");
		
		?>
		<div class="opcoes clearfix">
			<div class="qtd"><?php echo $sql->rows;?> registros</div>
			<?php /*<div class="link"><a href="javascript://" id="btn-csv"><i class="icon-doc-text"></i>exportar</a></div>*/ ?>
		</div>

		<table class="tablesorter">
			<thead>
				<tr>
					<th style="width:12%">Data</th>
					<th>Empresa</th>
					<th>Categoria</th>
					<th>Título</th>
					<th style="width:100px;">Ações</th>
				</tr>
			</thead>
			<tbody>
			<?php
			while($x=mysqli_fetch_object($sql->mysqry)) {
			?>
			<tr>
				<td><?php echo utf8_encode($x->dataf);?></td>
				<td><?php echo isset($_empresas[$x->id_empresa])?utf8_encode($_empresas[$x->id_empresa]->razao_social):"-";?></td>
				<td><?php echo isset($_categorias[$x->id_categoria])?utf8_encode($_categorias[$x->id_categoria]->titulo):"-";?></td>
				<td><?php echo utf8_encode($x->titulo);?></td>
				<td>
					<a href="<?php echo $_page;?>?form=1&edita=<?php echo $x->id."&".$url;?>" class="tooltip botao botao-principal" title="editar"><i class="icon-pencil"></i></a>
					<?php if($usr->tipo=="admin") { ?><a href="<?php echo $_page;?>?deleta=<?php echo $x->id."&".$url;?>" class="js-deletar tooltip botao botao-principal" title="excluir "><i class="icon-cancel"></i></a><?php } ?>
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