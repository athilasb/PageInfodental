<?php
	if(isset($_POST['ajax'])) {
		$rtn=array();
		require_once("lib/conf.php");
		require_once("usuarios/checa.php");


		if($_POST['ajax']=="subcategoria") {
			if(isset($_POST['titulo']) and !empty($_POST['titulo'])) {

				$categoria='';
				if(isset($_POST['id_categoria']) and is_numeric($_POST['id_categoria'])) {
					$sql->consult($_p."parametros_procedimentos_fases","*","where id='".addslashes($_POST['id_categoria'])."' and lixo=0");
					if($sql->rows) {
						$categoria=mysqli_fetch_object($sql->mysqry);
					}
				}

				if(is_object($categoria)) {
					
					if(isset($_POST['id_subcategoria']) and is_numeric($_POST['id_subcategoria']) and $_POST['id_subcategoria']>0) {
						$sql->consult($_p."parametros_procedimentos_subfases","*","where id='".addslashes($_POST['id_subcategoria'])."' and id_categoria=$categoria->id and lixo=0");
						if($sql->rows) {
							$s=mysqli_fetch_object($sql->mysqry);
							$vSQL="titulo='".utf8_decode(addslashes(strtoupperWLIB($_POST['titulo'])))."'";
							$sql->update($_p."parametros_procedimentos_subfases",$vSQL,"where id=$s->id");
							$rtn=array('success'=>true);
						} else {
							$rtn=array('success'=>false,'error'=>'Subcategoria não encontrada!');
						}
					} else {
						$vSQL="titulo='".utf8_decode(addslashes(strtoupperWLIB($_POST['titulo'])))."',
								data=now(),
								id_usuario=$usr->id,
								id_categoria=$categoria->id";
						$sql->add($_p."parametros_procedimentos_subfases",$vSQL);
						$rtn=array('success'=>true);
					}
				} else {
					$rtn=array('success'=>false,'error'=>'Selecione a Categoria');
				}

			} else {
				$rtn=array('success'=>false,'error'=>'Digite o título da Subcategoria');
			}
		} else if($_POST['ajax']=="subfasesListar") {
			$categoria='';
			if(isset($_POST['id_categoria']) and is_numeric($_POST['id_categoria'])) {
				$sql->consult($_p."parametros_procedimentos_fases","*","where id='".addslashes($_POST['id_categoria'])."' and lixo=0");
				if($sql->rows) {
					$categoria=mysqli_fetch_object($sql->mysqry);
				}
			}

			if(is_object($categoria)) {
				$sql->consult($_p."parametros_procedimentos_subfases","*","where id_categoria=$categoria->id and lixo=0 order by titulo asc");
				$subfases=array();
				while($x=mysqli_fetch_object($sql->mysqry)) {
					$subfases[]=array('id'=>$x->id,'titulo'=>utf8_encode($x->titulo));
				}

				$rtn=array('success'=>true,'subfases'=>$subfases);
			} else {
				$rtn=array('success'=>false,'error'=>'Categoria não definida!');
			}
		} else if($_POST['ajax']=="subcategoriaRemover") {
			$categoria='';
			if(isset($_POST['id_categoria']) and is_numeric($_POST['id_categoria'])) {
				$sql->consult($_p."parametros_procedimentos_fases","*","where id='".addslashes($_POST['id_categoria'])."' and lixo=0");
				if($sql->rows) {
					$categoria=mysqli_fetch_object($sql->mysqry);
				}
			}

			$subcategoria='';
			if(is_object($categoria) and isset($_POST['id_subcategoria']) and is_numeric($_POST['id_subcategoria'])) {
				$sql->consult($_p."parametros_procedimentos_subfases","*","where id='".addslashes($_POST['id_subcategoria'])."' and id_categoria=$categoria->id and lixo=0");
				if($sql->rows) {
					$subcategoria=mysqli_fetch_object($sql->mysqry);
				}
			}

			if(is_object($subcategoria)) {
				$sql->update($_p."parametros_procedimentos_subfases","lixo=1,lixo_id_usuario=$usr->id,lixo_data=now()","where id=$subcategoria->id");
				$subfases=array();
				while($x=mysqli_fetch_object($sql->mysqry)) {
					$subfases[]=$x;
				}

				$rtn=array('success'=>true,'subfases'=>$subfases);
			} else {
				$rtn=array('success'=>false,'error'=>'Categoria não definida!');
			}
		}
		header("Content-type: application/json");
		echo json_encode($rtn);
		die();
	}
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

	<header class="caminho">
		<h1 class="caminho__titulo">Configurações <i class="iconify" data-icon="bx-bx-chevron-right"></i> <strong>Fases</strong></h1>
		<a href="javascript:;" class="caminho__tutorial button button__sec"><i class="iconify" data-icon="ic-baseline-slow-motion-video"></i> ASSISTIR TUTORIAL</a>
	</header>

	<section class="content-grid">

		<section class="content__item">
			
			<?php
			require_once("includes/abaConfiguracoes.php");

			$_table=$_p."parametros_fases";
			$_page="pg_parametros_fases.php";

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
							$vSQL.="data=now()";
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
			<div class="box-form">
				<form method="post" class="formulario-validacao"  autocomplete="off" enctype="multipart/form-data">
					<input type="hidden" name="acao" value="wlib" />
					<fieldset>
						<legend>Dados da Fase</legend>

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
			</div>
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
			if(isset($values['busca']) and !empty($values['busca'])) $where.=" and (titulo like '%".utf8_decode($values['busca'])."%')";
			
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
		</section>
	</section>
</section>

<?php
	include "includes/footer.php";
?>