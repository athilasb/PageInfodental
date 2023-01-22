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

	<?php /*<header class="caminho">
		<h1 class="caminho__titulo">Configurações <i class="iconify" data-icon="bx-bx-chevron-right"></i> <strong>Planos</strong></h1>
		<a href="javascript:;" class="caminho__tutorial button button__sec"><i class="iconify" data-icon="ic-baseline-slow-motion-video"></i> ASSISTIR TUTORIAL</a>
	</header>*/?>
	<script>
		$(function(){
			$('input.money').maskMoney({symbol:'', allowZero:true, showSymbol:true, thousands:'.', decimal:',', symbolStay: true});
		})
	</script>

	<section class="content">

		<?php
			require_once("includes/abaConfiguracoes.php");
			$_table=$_p."parametros_cartoes_bandeiras";

			$_page=basename($_SERVER['PHP_SELF']);

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
				if(isset($_POST['acao'])) {

					if($_POST['acao']=="wlib") {
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

							
							if(!empty($msgErro)) {
								$jsc->jAlert($msgErro,"erro","");
							} else {
								$jsc->jAlert("Informações salvas com sucesso!","sucesso","document.location.href='".$_page."?".$url."'");
								die();
							}
						}
					}
				}	
			?>
			<form method="post" class="form formulario-validacao"  autocomplete="off" enctype="multipart/form-data">
				<input type="hidden" name="acao" value="wlib" />
			
				<section class="filtros">
					<h1 class="filtros__titulo">Cartões / Bandeiras de Cartão</h1>
					<div class="filtros-acoes">
						<a href="<?php echo $_page."?".$url;?>" ><i class="iconify" data-icon="bx-bx-left-arrow-alt"></i></a>
						<a href="javascript:;" class="principal btn-submit"><i class="iconify" data-icon="bx-bx-check"></i></a>	
						<a class="sec js-deletar" href="<?php echo $_page;?>?deleta=<?php echo $cnt->id."&".$url;?>"><i class="iconify" data-icon="bx-bx-trash"></i></a>
					</div>
				</section>

				<?php
					require_once("includes/abaConfiguracoesCartoes.php");
				?>
				
				<section class="grid" style="padding:2rem;">
					<div class="box">
						<h1 class="paciente__titulo1">Informações</h1>

						<dl>
							<dt>Bandeira</dt>
							<dd>
								<input type="text" name="titulo" value="<?php echo $values['titulo'];?>" class="obg" />
							</dd>
						</dl>

					</div>
				</section>
			</form>

			
				
			<?php
			} else {
			
			?>
			<section class="filtros">
				<h1 class="filtros__titulo">Cartões / Operadoras de Cartão</h1>
				<form method="get" class="filtros-form">
					<input type="hidden" name="csv" value="0" />
					<dl class=""> 
						<dt>Busca</dt>
						<dd><input type="text" name="busca" value="<?php echo isset($values['busca'])?$values['busca']:'';?>" /></dd>
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
			if(isset($values['busca']) and !empty($values['busca'])) $where.=" and (titulo like '%".utf8_decode($values['busca'])."%')";
		

			$sql->consult($_table,"*",$where." order by titulo asc");
			
			require_once("includes/abaConfiguracoesCartoes.php");
			?>
			
			<section class="grid">
				<div class="box registros">

					<div class="registros-qtd">
						<p class="registros-qtd__item"><?php echo $sql->rows;?> registros</p>
					</div>

					<table class="tablesorter">
						<thead>
							<tr>
								<th>Título</th>								
							</tr>
						</thead>
						<tbody>
						<?php
						while($x=mysqli_fetch_object($sql->mysqry)) {
						?>
						<tr onclick="document.location.href='<?php echo $_page;?>?form=1&edita=<?php echo $x->id."&".$url;?>'">
							<td><?php echo utf8_encode($x->titulo);?></td>
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