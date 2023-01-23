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

	$sql->consult($_table,"*",$where." order by titulo asc");
	
	?>
	<section class="grid">
		<div class="box">
			
			<div class="filter">
				<div class="filter-button">
					<a href="<?php echo $_page."?form=1&$url";?>" class="verde"><i class="iconify" data-icon="bx-bx-plus"></i><span> Novo Banco</span></a>
				</div>
			</div>

			<div class="reg">
				<?php
				while($x=mysqli_fetch_object($sql->mysqry)) {
				?>
				<a href="pg_configuracao_bancosecontas.php?form=1&edita=<?php echo $x->id;?>" class="reg-group">
					<div class="reg-color" style="background-color:green;"></div>
					<div class="reg-data" style="flex:0 1 50%;">
						<h1><?php echo strtoupperWLIB(utf8_encode($x->titulo));?></h1>
						<p><?php echo isset($_bancosEContasTipos[$x->tipo])?$_bancosEContasTipos[$x->tipo]:'-';?></p>
					</div>
				</a>
				<?php
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