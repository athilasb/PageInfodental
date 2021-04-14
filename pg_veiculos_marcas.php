<?php
	$title="";
	include "includes/header.php";


	if(isset($_GET['box']) and $_GET['box']=="box-veiculos") {
		$values['titulo']='';

		if(isset($_GET['id_marca']) and is_numeric($_GET['id_marca'])) $values['id_marca']=$_GET['id_marca'];
		else $values['id_marca']=0;

		$veiculo='';

		if(isset($_GET['id_veiculo']) and is_numeric($_GET['id_veiculo'])) {
			$sql->consult($_p."veiculos_modelos","*","where id='".$_GET['id_veiculo']."' and lixo=0");
			if($sql->rows==0) {
				$jsc->jAlert("Veículo não encontrado!","erro","$.fancybox.close();");
			} else {
				$veiculo=mysqli_fetch_object($sql->mysqry);
				$values['id_marca']=$veiculo->id_marca;
				$values['titulo']=utf8_encode($veiculo->titulo);
			}
		}

		$_marcas=array();
		$sql->consult($_p."veiculos_marcas","*","where lixo=0 order by titulo");
		while($x=mysqli_fetch_object($sql->mysqry)) $_marcas[$x->id]=$x;
	?>

	<form method="post" class="formulario-validacao js-box-veiculo" style="width:90%;">
		<input type="hidden" name="acao" value="veiculos" />
		<input type="hidden" name="id_veiculo" value="<?php echo is_object($veiculo)?$veiculo->id:0;?>">
		<fieldset>
			<legend>Veículo</legend>

			<div class="colunas3">
				<dl>
					<dt>Marca</dt>
					<dd>
						<select name="id_marca" class="obg">
							<?php
							foreach($_marcas as $v) echo '<option value="'.$v->id.'"'.($v->id==$values['id_marca']?' selected':'').'>'.utf8_encode($v->titulo).'</option>';
							?>
						</select>
					</dd>
				</dl>
				<dl class="dl2">
					<dt>Título</dt>
					<dd><input type="text" name="titulo" value="<?php echo $values['titulo'];?>" class="obg" /></dd>
				</dl>
			</div>

			<button type="submit" class="botao btn-submit-mv" data-form="js-box-veiculo">Salvar</button>
		</fieldset>
	</form>

	<?php
		die();
	}

	include "includes/nav.php";
	if($usr->tipo!="admin" and !in_array("parametros",$_usuariosPermissoes)) {
		$jsc->jAlert("Você não tem permissão para acessar esta área!","erro","document.location.href='dashboard.php'");
		die();
	}
	$values=$adm->get($_GET);
?>
<script>
	$(function(){
		$('.m-parametros').next().show();		
	});
</script>
<section id="conteudo">
	
	<div class="box-caminho">
		<a href="javascript" class="js-collapse"><span></span></a>
		<h1>Marcas</h1>
	</div>
	
	<?php
	$_table=$_p."veiculos_marcas";
	$_page=basename($_SERVER['PHP_SELF']);

	$_width="";
	$_height="";
	$_dir="";

	if(isset($_GET['form'])) {
		$cnt='';
		$campos=explode(",","destaque,titulo");
		
		foreach($campos as $v) $values[$v]='';
		
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

		if(isset($_GET['deleta']) and is_numeric($_GET['deleta'])) {
			$vsql="lixo=1";
			$vWHERE="where id=".$_GET['deleta'];
			$sql->update($_p."veiculos_marcas",$vsql,$vWHERE);
		}

		if(isset($_POST['acao'])) {

			if($_POST['acao']=="veiculos") {
				$vSQL="titulo='".utf8_decode(addslashes(strtoupperWLIB($_POST['titulo'])))."',id_marca='".addslashes($_POST['id_marca']) ."'";
				
				if(isset($_POST['id_veiculo']) and is_numeric($_POST['id_veiculo']) and $_POST['id_veiculo']>0) {
					$sql->update($_p."veiculos_modelos",$vSQL,"WHERE id='".$_POST['id_veiculo']."'");
					$id_reg=$_POST['id_veiculo'];
					$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='update',vsql='".addslashes($vSQL)."',vwhere='',tabela='".$_p."veiculos_modelos',id_reg='".$id_reg."'");
				} else {
					$sql->add($_p."veiculos_modelos",$vSQL);
					$id_reg=$sql->ulid;
					$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='update',vsql='".addslashes($vSQL)."',vwhere='',tabela='".$_p."veiculos_modelos',id_reg='".$id_reg."'");
				}
			}
			else if($_POST['acao']=="wlib") {
				$vSQL=$adm->vSQL($campos,$_POST);
				$values=$adm->values;
			 	$processa=true;
				
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
		}	
		if(isset($_GET['deletar']) and is_numeric($_GET['deletar'])) {
			$sql->update($_p."veiculos_modelos","lixo=1","where id='".$_GET['deletar']."'");
		}
	?>
	<div class="box-botoes clearfix">
		<a href="<?php echo $_page."?".$url;?>" class="botao"><i class="icon-left-big"></i> Voltar</a>
		<?php if(is_object($cnt)) {?><a data-fancybox data-type="ajax" data-src="ajax/log.php?table=<?php echo $_table;?>&id=<?php echo $cnt->id;?>" href="javascript:;" class="botao"><i class="icon-info-circled"></i> Logs</a><?php } ?>
		<a href="javascript://" class="botao botao-principal btn-submit"><i class="icon-ok"></i> Salvar</a>

	</div>

	<div class="box-form">
		<form method="post" class="formulario-validacao"  autocomplete="off" enctype="multipart/form-data">
			<input type="hidden" name="acao" value="wlib" />
			<fieldset>
				<legend>Dados da Marca</legend>

				<div>
					<dl>
						<dt></dt>
						<dd><label><input type="checkbox" name="destaque" value="1"<?php echo $values['destaque']=='1'?' checked':'';?> /> Destaque</label></dd>
					</dl>
					<dl>
						<dt>Título</dt>
						<dd>
							<input type="text" name="titulo" value="<?php echo $values['titulo'];?>" class="obg" />
						</dd>
					</dl>
				</div>
			</fieldset>

			<?php 
				if(is_object($cnt)) {
			?>
			<fieldset class="box-registros" style="background: #FFF !important;">
				<legend>Veículos</legend>

				<a href="<?php echo $_page."?box=box-veiculos&id_marca=$cnt->id";?>" data-fancybox data-type="ajax" class="botao botao-principal" style="float:right;"><i class="icon-plus"></i> Adicionar</a>
				
				<table>
					<tr>
						<th>Veículo</th>
						<th style="width:100px;">Ações</th>
					</tr>
					<?php 
						$sql->consult($_p."veiculos_modelos","*","where id_marca=".$cnt->id." and lixo=0 order by titulo");
						while($x=mysqli_fetch_object($sql->mysqry)) {
					?>
					<tr>
						<td><?php echo utf8_encode($x->titulo); ?></td>
						<td>
							<a href="<?php echo $_page."?box=box-veiculos&form=1&edita=$cnt->id&id_veiculo=$x->id";?>" data-fancybox data-type="ajax" class="botao"><i class="icon-pencil"></i></a>
							<a href="<?php echo $_page."?form=1&edita=$cnt->id&deletar=$x->id";?>" class="botao js-deletar"><i class="icon-cancel"></i></a>
						</td>
					</tr>
						<?php 
						}
					?>
				</table>
			</fieldset>
			<?php
				}
			?>	
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
					<dt>Busca</dt>
					<dd><input type="text" name="campo" value="<?php echo isset($values['campo'])?$values['campo']:"";?>" /></dd>
				</dl>
				<dl>		
					<dt>&nbsp;</dt>			
					<dd><button type="submit"><i class="icon-search"></i> Filtrar</button></dd>
				</dl>
			</div>
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

		$_veiculos=array();
		$sql->consult($_p."veiculos_modelos","*","where lixo=0 order by titulo");
		while($x=mysqli_fetch_object($sql->mysqry)) {
			$_veiculos[$x->id_marca][]=$x;
		}
		
		$where="WHERE lixo='0'";
		if(isset($values['campo']) and !empty($values['campo'])) $where.=" and (titulo like '".utf8_decode($values['campo'])."')";
		
		if($usr->cpf=="wlib" and isset($_GET['cmd'])) echo $where;

		$sql->consult($_table,"*",$where." order by titulo asc");
		
		?>
		<div class="opcoes clearfix">
			<div class="qtd"><?php echo $sql->rows;?> registros</div>
			<?php /*<div class="link"><a href="javascript://" id="btn-csv"><i class="icon-doc-text"></i>exportar</a></div>*/ ?>
		</div>

		<table class="tablesorter">
			<thead>
				<tr>
					<th>Título</th>
					<th style="width:100px;">Veículos</th>
					<th style="width:100px;">Destaque</th>
					<th style="width:100px;">Ações</th>
				</tr>
			</thead>
			<tbody>
			<?php
			while($x=mysqli_fetch_object($sql->mysqry)) {
			?>
			<tr>
				<td><?php echo utf8_encode($x->titulo);?></td>
				<td>
					<?php
					echo isset($_veiculos[$x->id])?count($_veiculos[$x->id]):0;
					?>
				</td>
				<td><?php echo $x->destaque==1?"<font color=green>Sim</font>":"<font color=red>Não</font>";?></td>
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