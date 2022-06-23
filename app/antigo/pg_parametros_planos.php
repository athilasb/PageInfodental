<?php
	$title="";
	include "includes/header.php";
	//include "includes/nav.php";

	if($usr->tipo!="admin" and !in_array("parametros",$_usuariosPermissoes)) {
		$jsc->jAlert("Você não tem permissão para acessar esta área!","erro","document.location.href='dashboard.php'");
		die();
	}
	$values=$adm->get($_GET);
?>

<section class="modal">
	<div class="modal__fixo" style="padding-top:0;padding-bottom: 15px;text-align:right">

		<?php
		$_table=$_p."parametros_planos";
		$_page=basename($_SERVER['PHP_SELF']);

		$_width="";
		$_height="";
		$_dir="";

		if(isset($_GET['form'])) {
			$cnt='';
			$campos=explode(",","titulo");
			
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
						$jsc->jAlert("Informações salvas com sucesso!","sucesso","document.location.href='".$_page."?".$url."'");
						die();
					}
				}
			}	
		?>
		

		<div class="acoes">
			<a href="<?php echo $_page."?".$url;?>" class="button button__lg button__ter"><i class="iconify" data-icon="bx-bx-left-arrow-alt"></i></a>
			<a href="javascript:;" class="button button__lg btn-submit"><i class="iconify" data-icon="bx-bx-check"></i> Salvar</a>			
		</div>

		<script>
			$(function(){
				$('input.money').maskMoney({symbol:'', allowZero:true, showSymbol:true, thousands:'.', decimal:',', symbolStay: true});
			})
		</script>
		<form method="post" class="form formulario-validacao"  autocomplete="off" enctype="multipart/form-data">
			<input type="hidden" name="acao" value="wlib" />
			<fieldset>
				<legend>Dados da Profissão</legend>

				<div>
					<dl>
						<dt>Título</dt>
						<dd>
							<input type="text" name="titulo" value="<?php echo $values['titulo'];?>" class="obg" />
						</dd>
					</dl>
				</div>
			</fieldset>
		</form>
		<?php
		} else {
		
		?>
		<section class="filtros">
			<form method="get" class="filtros-form form">
				<input type="hidden" name="csv" value="0" />
				<div class="colunas4">
					<dl class=""> 
						<dt>Busca</dt>
						<dd><input type="text" name="busca" value="<?php echo isset($values['busca'])?$values['busca']:'';?>" /></dd>
					</dl>
					<dl>		
						<dt>&nbsp;</dt>			
						<dd><button type="submit" class="button button__sec"><i class="iconify" data-icon="bx-bx-search" data-inline="false"></i></button></dd>
					</dl>
				</div>
			</form>
			<div class="filtros-acoes">
				<a href="<?php echo $_page."?form=1&$url";?>" data-padding="0" class="filtros-acoes__button tooltip" title="Adicionar"><i class="iconify" data-icon="ic-baseline-add"></i></a>
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
		if(isset($values['busca']) and !empty($values['busca'])) $where.=" and (titulo like '".utf8_decode($values['busca'])."')";
		
		if($usr->cpf=="wlib" and isset($_GET['cmd'])) echo $where;

		$sql->consult($_table,"*",$where." order by titulo asc");
		
		?>
		<div class="registros-qtd">
			<p class="registros-qtd__item"><?php echo $sql->rows;?> registros</p>
		</div>

		<div class="registros">

			<table class="tablesorter">
				<thead>
					<tr>
						<th>Título</th>
						<th style="width:120px;">Ações</th>
					</tr>
				</thead>
				<tbody>
				<?php
				while($x=mysqli_fetch_object($sql->mysqry)) {
				?>
				<tr>
					<td><?php echo utf8_encode($x->titulo);?></td>
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
	</div>
</section>

<?php
	include "includes/footer.php";
?>