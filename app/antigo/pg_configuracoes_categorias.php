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
	require_once("includes/abaConfiguracao.php");
	?>

	<?php
	$_table=$_p."financeiro_categorias";
	$_page=basename($_SERVER['PHP_SELF']);

	$_width="";
	$_height="";
	$_dir="";

	$_categorias=array();
	$sql->consult($_p."financeiro_categorias","*","where lixo=0 order by titulo asc");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_categorias[$x->id]=$x;
	}


	if(isset($_GET['form'])) {
		$cnt='';
		$campos=explode(",","titulo,id_categoria,receita");
		
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

			$jsc->jAlert("Informações salvas com sucesso!","sucesso","document.location.href='".$_page."'");
			die();
			
			
		}	
	?>

	<section class="grid">
		<div class="box">

			<form method="post" class="form formulario-validacao"  autocomplete="off" enctype="multipart/form-data">
				<input type="hidden" name="acao" value="wlib" />

				<div class="filter">
					<div class="filter-group">
						<div class="filter-button">
							<a href="<?php echo $_page;?>"><i class="iconify" data-icon="bx-bx-left-arrow-alt"></i></a>
						</div>
					</div>
					<div class="filter-group filter-group_right">
						<div class="filter-button">
							<?php if(is_object($cnt)){?><a href="<?php echo $_page;?>?deleta=<?php echo $cnt->id."&".$url;?>" class="js-deletar"><i class="iconify" data-icon="bx-bx-trash"></i></a><?php }?>
							<a href="javascript:window.print();"><i class="iconify" data-icon="bx-bx-printer"></i></a>
							<a href="javascript:;" class="azul btn-submit"><i class="iconify" data-icon="bx-bx-check"></i><span>salvar</span></a>
						</div>
					</div>
				</div>
				<fieldset>
					<legend><span class="badge">1</span> Dados</legend>
					<div class="colunas4">
						<dl class="dl2">
							<dt>Título</dt>
							<dd>
								<input type="text" name="titulo" value="<?php echo $values['titulo'];?>" class="obg" />
							</dd>
						</dl>
						<dl>
							<dt>Categoria</dt>
							<dd>
								<select name="id_categoria" class="">
									<option value="">-</option>
									<?php
									foreach($_categorias as $v) {
										if($v->id_categoria==0) {
											echo '<option value="'.$v->id.'"'.((isset($values['id_categoria']) and $values['id_categoria']==$v->id)?' selected':'').'>'.utf8_encode($v->titulo).'</option>';
										}
									}
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
							<dd><input type="text" name="agencia" value="<?php echo $values['agencia'];?>"  /></dd>
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

	
	?>
	<section class="grid">
		<div class="box">
			
			<div class="filter">
				<div class="filter-button">
					<a href="<?php echo $_page."?form=1&$url";?>" class="verde"><i class="iconify" data-icon="bx-bx-plus"></i><span> Nova Categoria</span></a>
				</div>
			</div>

			<div class="reg">
				<?php
				$sql->consult($_table,"*","where id_categoria=0 and lixo=0 order by titulo");
				$categorias=array();
				while($x=mysqli_fetch_object($sql->mysqry)) {
					$categorias[]=$x;
				}


				foreach($categorias as $x) {
				
				?>
				<a href="?form=1&edita=<?php echo $x->id;?>" class="reg-group">
					<div class="reg-color" style="background-color:var(--cor1);"></div>
					<div class="reg-data" style="flex:0 1 50%;">
						<h1><?php echo strtoupperWLIB(utf8_encode($x->titulo));?></h1>
					</div>
				</a>
				<?php
					$sql->consult($_table,"*","where id_categoria=$x->id and lixo=0 order by titulo asc");
					if($sql->rows) {
						while($s=mysqli_fetch_object($sql->mysqry)) {
				?>
				<a href="?form=1&edita=<?php echo $s->id;?>" class="reg-group">
					<div class="reg-color" style="background-color:var(--cinza2);"></div>
					<div class="reg-data" style="flex:0 1 50%;">
						<h1>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo strtoupperWLIB(utf8_encode($s->titulo));?></h1>
					</div>
				</a>
				<?php		
						}
					}
				}
				?>
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