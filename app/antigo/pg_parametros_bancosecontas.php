<?php
	$title="";
	include "includes/header.php";
	include "includes/nav.php";

	if($usr->tipo!="admin" and !in_array("parametros",$_usuariosPermissoes)) {
		$jsc->jAlert("Você não tem permissão para acessar esta área!","erro","document.location.href='dashboard.php'");
		die();
	}
	$values=$adm->get($_GET);
?>
<section class="content">

	<?php
	require_once("includes/abaConfiguracoes.php");
	?>

	<?php
	$_table=$_p."financeiro_bancosecontas";
	$_page=basename($_SERVER['PHP_SELF']);

	$_width="";
	$_height="";
	$_dir="";


	if(isset($_GET['form'])) {
		$cnt='';
		$campos=explode(",","titulo,agencia,conta,tipo");
		
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
		if(isset($_POST['acao']) and $_POST['acao']=="wlib") {
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
					$jsc->jAlert("Informações salvas com sucesso!","sucesso","document.location.href='".$_page."'");
					die();
				}
			}
		}	
	?>
			
	<div class="filtros">
		<h1 class="filtros__titulo">Bancos e Contas</h1>
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
	</div>


	<section class="grid">
		<div class="box">

			<form method="post" class="form formulario-validacao"  autocomplete="off" enctype="multipart/form-data">
				<input type="hidden" name="acao" value="wlib" />
				<fieldset>
					<legend>Dados</legend>
					<div class="colunas4">
						<dl class="dl2">
							<dt>Título</dt>
							<dd>
								<input type="text" name="titulo" value="<?php echo $values['titulo'];?>" class="obg" />
							</dd>
						</dl>
						<dl>
							<dt>Tipo</dt>
							<dd>
								<select name="tipo" class="obg">
									<option value="">-</option>
									<?php
									foreach($_bancosEContasTipos as $k=>$v) echo '<option value="'.$k.'"'.((isset($values['tipo']) and $values['tipo']==$k)?' selected':'').'>'.$v.'</option>';
									?>
								</select>
							</dd>
						</dl>
					</div>
					<script type="text/javascript">
						$(function(){
							$('select[name=tipo]').change(function(){
								let val = $(this).val();
								if(val=="contacorrente") {
									$('.js-contacorrente').fadeIn();
								} else {
									$('.js-contacorrente').hide();
								}
							}).trigger('change');
						});
					</script>
					<div class="colunas4 js-contacorrente" style="display: none">
						<dl>
							<dt>Agência</dt>
							<dd><input type="text" name="agencia" value="<?php echo $values['conta'];?>"  /></dd>
						</dl>
						<dl>
							<dt>Conta</dt>
							<dd><input type="text" name="conta" value="<?php echo $values['conta'];?>" /></dd>
						</dl>
					</div>
				</fieldset>
			</form>
		</div>
	</section>
	
	<?php
	} else {			
	?>

	<section class="filtros">
		<h1 class="filtros__titulo">Banco e Contas</h1>
		<form method="get" class="filtros-form">
			<input type="hidden" name="csv" value="0" />
			
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
	
	if($usr->cpf=="wlib" and isset($_GET['cmd'])) echo $where;

	$sql->consult($_table,"*",$where." order by titulo asc");
	
	?>
	<section class="grid">
		<div class="box">
			
			<div class="registros-qtd">
				<p class="registros-qtd__item"><?php echo $sql->rows;?> registros</p>
			</div>

			<div class="registros">
				
				<table class="tablesorter">
					<thead>
						<tr>
							<th>Título</th>
							<th>Tipo</th>
							<th style="width:250px;">Unidade</th>
						</tr>
					</thead>
					<tbody>
					<?php
					while($x=mysqli_fetch_object($sql->mysqry)) {
					?>
					<tr onclick="document.location.href='<?php echo $_page;?>?form=1&edita=<?php echo $x->id."&".$url;?>'">
						<td><b><?php echo utf8_encode($x->titulo);?></b></td>
						<td><?php echo isset($_bancosEContasTipos[$x->tipo])?$_bancosEContasTipos[$x->tipo]:'-';?></b></td>
					</tr>
					<?php
					}
					?>
					</tbody>
				</table>
			</div>
		</div>
	</section>

	<?php
	}
	?>

</section>

<?php
	include "includes/footer.php";
?>