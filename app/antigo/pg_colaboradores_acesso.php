<?php
	include "includes/header.php";
	include "includes/nav.php";

	$_table=$_p."colaboradores";
	$_page=basename($_SERVER['PHP_SELF']);

	$colaborador=$cnt='';
	if(isset($_GET['id_colaborador']) and is_numeric($_GET['id_colaborador'])) {
		$sql->consult($_p."colaboradores","*","where id='".$_GET['id_colaborador']."'");
		if($sql->rows) {
			$colaborador=mysqli_fetch_object($sql->mysqry);
			$cnt=$colaborador;
		}
	}

	$campos=explode(",","cpf,permitir_acesso");
	foreach($campos as $v) {
		$values[$v]='';
	}

	if(is_object($cnt)) {
		$values=$adm->values($campos,$cnt);
	}

	if(isset($_POST['acao']) and $_POST['acao']=="wlib") {
		$vSQL=$adm->vSQL($campos,$_POST);
		$values=$adm->values;
		$processa=true;

		if($processa===true) {	
		
			if(is_object($cnt)) {
				if(isset($_POST['senha'])  and !empty($_POST['senha'])) $vSQL.="senha='".sha1($_POST['senha'])."',";
				$vSQL=substr($vSQL,0,strlen($vSQL)-1);
				$vWHERE="where id='".$cnt->id."'";
				$sql->update($_table,$vSQL,$vWHERE);
				$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='update',vsql='".addslashes($vSQL)."',vwhere='".addslashes($vWHERE)."',tabela='".$_table."',id_reg='".$cnt->id."'");
				$id_reg=$cnt->id;
			} 
			$jsc->jAlert("Informações salvas com sucesso!","sucesso","document.location.href='".$_page."?id_colaborador=".$colaborador->id."'");
			die();
		}
	}

?>
	<section class="content">
		
		<?php
		require_once("includes/abaColaborador.php");
		?>

		<form method="post" class="form formulario-validacao"  autocomplete="off" enctype="multipart/form-data">
			<input type="hidden" name="acao" value="wlib" />

			<section class="grid" style="padding:2rem;">
				<div class="box">
					<div class="filter">

						<div class="filter-group">
							<div class="filter-button">
								<a href="javascript:history.back(-1);"><i class="iconify" data-icon="bx-bx-left-arrow-alt"></i></a>
							</div>
						</div>

						<div class="filter-group filter-group_right">
							<div class="filter-button">
								<a href="javascript:;" class="azul  btn-submit"><i class="iconify" data-icon="bx-bx-check"></i><span>salvar</span></a>
							</div>
						</div>
					</div>

					<fieldset style="margin:0;">
						<legend style="font-size: 12px;">
							<div class="filter-group">
								<div class="filter-title">
									<span class="badge">1</span> Dados de Acesso
								</div>
							</div>
						</legend>

						<dl>
							<dt></dt>
							<dd><label><input type="checkbox" name="permitir_acesso" value="1" class="input-switch"<?php echo $values['permitir_acesso']=='1'?' checked':'';?> /> Permitir Acesso</label></dd>
						</dl>

						<div class="colunas4">
							<dl>
								<dt>Login</dt>
								<dd><input type="text" name="cpf" value="<?php echo $values['cpf'];?>" class="cpf" disabled /></dd>
							</dl>
							<dl class="dl2">
								<dt>Senha</dt>
								<dd><input type="text" name="senha" autocomplete="off" class="senha" /></dd>
							</dl>
						</div>
					</fieldset>
			</section>

		</form>
		
<?php
include "includes/footer.php";
?>