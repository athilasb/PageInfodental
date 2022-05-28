<?php
	require_once("../lib/conf.php");
	$dir="../";
	require_once("../usuarios/checa.php");

	$jsc=new Js();
	$colaborador='';
	if(isset($_GET['id_colaborador']) and is_numeric($_GET['id_colaborador'])) {
		$sql->consult($_p."colaboradores","*","where id='".$_GET['id_colaborador']."'");
		if($sql->rows) {
			$colaborador=mysqli_fetch_object($sql->mysqry);
		}
	}

	if(empty($colaborador)) {
		$jsc->jAlert("Colaborador não encontrado!","erro","$.fancybox.close();");
		die();
	}

?>
<script type="text/javascript" src="../js/jquery.validacao.js"></script>

<section class="modal" style="width:700px; height:auto;">

	<header class="modal-header">
		<div class="filtros">
			<h1 class="filtros__titulo">Foto</h1>
			<div class="filtros-acoes">
				<button type="submit" class="principal js-salvar" onclick="document.getElementById('formFoto').submit(); return false;"><i class="iconify" data-icon="bx-bx-check"></i></button>
			</div>
		</div>
	</header>

	<article class="modal-conteudo">
		<form id="formFoto" method="post" class="form formulario-validacao" enctype="multipart/form-data">
			<input type="hidden" name="colaboradorHiddenFoto" value="1" />
			<?php
			$ftPaciente='';
			$ft='arqs/colaboradores/'.$colaborador->id.".".$colaborador->foto;
			if(file_exists("../".$ft)) {
				$ftColaborador=$ft;
			}
			?>
			<center><img src="<?php echo $ftColaborador;?>" alt="<?php echo utf8_encode($colaborador->nome);?>" width="200" height="200" class="paciente-info-header__foto" /></center>
			<dl>
				<dd><input type="file" name="foto" class="obg" /></dd>
			</dl>			
		</form>
	</article>
</section>