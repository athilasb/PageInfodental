<?php
	include "includes/header.php";
	include "includes/nav.php";

	$_table=$_p."landingpage_temas";
	$_page=basename($_SERVER['PHP_SELF']);

	$landingpage=$cnt='';
	if(isset($_GET['id_landingpage']) and is_numeric($_GET['id_landingpage'])) {
		$sql->consult($_p."landingpage_temas","*","where id='".$_GET['id_landingpage']."'");
		if($sql->rows) {
			$landingpage=mysqli_fetch_object($sql->mysqry);
			$cnt=$landingpage;
		}
	}

	$campos=explode(",","titulo,cor_primaria,cor_secundaria,codigo_head,codigo_body");
	
	foreach($campos as $v) $values[$v]='';
	$values['code']= '';

	if(is_object($landingpage)) {
		$values=$adm->values($campos,$cnt);
		$values['code']=$landingpage->code;
	}

	if(isset($_POST['acao']) and $_POST['acao']=="wlib") {
		$vSQL=$adm->vSQL($campos,$_POST);
		$values=$adm->values;
		$processa=true;

		if((is_object($cnt) and $cnt->code!=$_POST['code']) or (empty($cnt))) {
			$sql->consult($_table,"*","where code='".addslashes($_POST['code'])."' and lixo=0");
			if($sql->rows) {
				$jsc->jAlert("Já existe tema com o endereço <b>".$_POST['code']."</b>","erro","");
				$processa=false;
			}
		}

		if($processa===true) {	

			$values['code']=$_POST['code'];
			$vSQL.="code='".addslashes($_POST['code'])."',";
		
			if(is_object($cnt)) {
				$vSQL.="id_alteracao=$usr->id,alteracao_data=now()";
				$vWHERE="where id='".$cnt->id."'";
				$sql->update($_table,$vSQL,$vWHERE);
				$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='update',vsql='".addslashes($vSQL)."',vwhere='".addslashes($vWHERE)."',tabela='".$_table."',id_reg='".$cnt->id."'");
				$id_reg=$cnt->id;
			} else {
				$sql->add($_table,$vSQL."data=now(),id_usuario='".$usr->id."'");
				$id_reg=$sql->ulid;
				$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='insert',vsql='".addslashes($vSQL)."',tabela='".$_table."',id_reg='".$sql->ulid."'");
			}

			$jsc->jAlert("Informações salvas com sucesso!","sucesso","document.location.href='".$_page."?form=1&edita=".$id_reg."&".$url."'");
			die();
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
		$('input[name=cor_primaria]').keyup(function(){
			let cor = $(this).val();
			$('input[name=cor_secundaria]').val(cor);
		});
		<?php
		}
		?>
	})
</script>
	<section class="content">
		
		<?php
		if(is_object($cnt)) require_once("includes/abaLandingPage.php");
		?>

		<form method="post" class="form formulario-validacao"  autocomplete="off" enctype="multipart/form-data">
			<input type="hidden" name="acao" value="wlib" />		
			<?php
			if(empty($cnt)) {
			?>
			<section class="filtros">
				<h1 class="filtros__titulo">Landing Page</h1>
				<div class="filtros-acoes">
					<a href="pg_landingpages.php"><i class="iconify" data-icon="bx-bx-left-arrow-alt"></i></a>
					<a href="javascript:;" class="principal btn-submit"><i class="iconify" data-icon="bx-bx-check"></i></a>
				</div>
			</section>
			<?php
			}
			?>

			<section class="grid" style="padding:1rem;">
				<div class="box">

					<div class="filter">

						<div class="filter-group">
							<div class="filter-button">
								<a href="javascript:history.back(-1);"><i class="iconify" data-icon="bx-bx-left-arrow-alt"></i></a>
							</div>
						</div>

						<div class="filter-group filter-group_right">
							<div class="filter-button">
								<a href="?deletaLandingPage=<?php echo $landingpage->id."&".$url;?>" class="js-deletar"><i class="iconify" data-icon="bx-bx-trash"></i></a>
								<a href="javascript:;" class="azul  btn-submit"><i class="iconify" data-icon="bx-bx-check"></i><span>salvar</span></a>
							</div>
						</div>

					</div>

					<div>
						<div class="colunas4">
							<dl class="dl2">
								<dt>Nome do Tema</dt>
								<dd>
									<input type="text" name="titulo" value="<?php echo $values['titulo'];?>" class="obg"/>
								</dd>
							</dl>
							<dl class="dl2">
								<dt>URL do Tema</dt>
								<dd>
									<input type="text" name="code" value="<?php echo $values['code'];?>"  class="obg noupper" />
								</dd>
							</dl>	
						</div>
						<div class="colunas4">
							<dl class="dl2">
								<dt>Cor Primária</dt>
								<dd><input type="text" name="cor_primaria" value="<?php echo $values['cor_primaria'];?>" class="obg" /></dd>
							</dl>
							<dl class="dl2">
								<dt>Cor Secundária</dt>
								<dd><input type="text" name="cor_secundaria" value="<?php echo $values['cor_secundaria'];?>" class="obg" /></dd>
							</dl>
						</div>
						<dl>
							<dt>Código de Rastreamento Body</dt>
							<dd>
								<textarea name="codigo_body" style="height: 200px;" class="noupper"><?php echo $values['codigo_body'];?></textarea>
							</dd>
						</dl>
						<dl>
							<dt>Código de Rastreamento Head</dt>
							<dd>
								<textarea name="codigo_head" style="height: 200px;" class="noupper"><?php echo $values['codigo_head'];?></textarea>
							</dd>
						</dl>
					</div>
				</div>
			</section>


		</form>
		
<?php
include "includes/footer.php";
?>