<?php
	
	include "print-header.php";

	$evolucao = $paciente = $documento = $documentoModelo = "";
	if(isset($_GET['id'])) {
		$sql->consult($_p."pacientes_evolucoes","*","where md5(id)='".addslashes($_GET['id'])."' and id_tipo=10");
		if($sql->rows) {
			$evolucao=mysqli_fetch_object($sql->mysqry);

			$sql->consult($_p."colaboradores","*","where id=$evolucao->id_usuario");
			if($sql->rows) {
				$solicitante=mysqli_fetch_object($sql->mysqry);
			}


			$sql->consult($_p."pacientes","*","where id=$evolucao->id_paciente");
			if($sql->rows) {
				$paciente=mysqli_fetch_object($sql->mysqry);
			}

			$sql->consult($_p."pacientes_evolucoes_documentos","*","where id_evolucao=$evolucao->id and lixo=0");
			if($sql->rows) {
				$documento=mysqli_fetch_object($sql->mysqry);

				$sql->consult($_p."parametros_documentos","*","where id=$documento->id_documento") ;
				if($sql->rows) {
					$documentoModelo=mysqli_fetch_object($sql->mysqry);
				}
			}

		
		}
	}


	$jsc = new Js();

	if(empty($evolucao)) {
		$jsc->alert("Pedido de exame não cadastrado!","document.location.href='../dashboard.php'");
		die();
	}

	if(empty($paciente)) {
		$jsc->alert("Paciente não encontrado!","document.location.href='../dashboard.php'");
		die();
	}
	
	if(empty($documento)) {
		$jsc->alert("Documento não encontrado","document.location.href='../dashboard.php'");
		die();
	}
	
	if(empty($solicitante)) {
		$jsc->alert("Solicitante não encontrado","document.location.href='../dashboard.php'");
		die();
	}


	$idade=idade($paciente->data_nascimento);

?>
			
<?php /*<header class="titulo1">
	<h1 style="margin:auto;"><?php echo utf8_encode($documentoModelo->titulo);?></h1>	
</header>*/?>

<p class="fck" style="margin-top: 30px;"><?php echo utf8_encode($documento->texto);?></p>


<?php
include "print-footer.php";
?>