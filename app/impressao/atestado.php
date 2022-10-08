<?php
	
	include "print-header.php";

	$evolucao = $paciente = $solicitante = $atestado = "";
	$exames = $_exames = array();
	if(isset($_GET['id'])) {
		$sql->consult($_p."pacientes_evolucoes","*","where md5(id)='".addslashes($_GET['id'])."' and id_tipo=4");
		if($sql->rows) {
			$evolucao=mysqli_fetch_object($sql->mysqry);

			$sql->consult($_p."colaboradores","*","where id=$evolucao->id_profissional");
			if($sql->rows) {
				$solicitante=mysqli_fetch_object($sql->mysqry);
			}


			$sql->consult($_p."pacientes","*","where id=$evolucao->id_paciente");
			if($sql->rows) {
				$paciente=mysqli_fetch_object($sql->mysqry);
			}

			$sql->consult($_p."pacientes_evolucoes_atestados","*","where id_evolucao=$evolucao->id and lixo=0");
			if($sql->rows) {
				$atestado=mysqli_fetch_object($sql->mysqry);
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
	
	if(empty($atestado)) {
		$jsc->alert("Atestado não encontrado","document.location.href='../dashboard.php'");
		die();
	}
	
	if(empty($solicitante)) {
		$jsc->alert("Solicitante não encontrado","document.location.href='../dashboard.php'");
		die();
	}


	$idade=idade($paciente->data_nascimento);

?>
			
<header class="titulo1">
	<h1 style="margin:auto;">Atestado Médico</h1>	
</header>

<p><?php echo utf8_encode($atestado->atestado);?></p>

<p style="font-weight:bold;margin-top: 25px;font-size: 10px;">
	Conforme artigo 9° da Resolução CFO-118/2012 - É dever do profissional de odontologia resguardar o sigilo profissional do paciente, e, quando necessário, a depender do caso, não expor o procedimento realizado, bem como a CID correspondente.
</p>

<div class="box" style="margin-top:2.5rem;">
	<table>
		<tr>
			<td colspan="2">
				<h1>NOME DO PROFISSIONAL</h1>
				<p><?php echo utf8_encode($solicitante->nome);?></p>
			</td>
			<td>
				<h1>CRO</h1>
				<p><?php echo !empty($solicitante->cro)?utf8_encode($solicitante->cro):'-';?></p>
			</td>
			<td>
				<h1>UF</h1>
				<p><?php echo !empty($solicitante->uf_cro)?utf8_encode($solicitante->uf_cro):'-';?></p>
			</td>
		</tr>
		<tr>
			<td colspan="3">
				<h1>LOCAL DE ATENDIMENTO</h1>
				<p><?php echo utf8_encode($unidade->endereco);?></p>
			</td>
		</tr>
		<tr>		
			<td>
				<h1>TELEFONE</h1>
				<p><?php echo maskTelefone($unidade->telefone);?></p>
			</td>	
			<td>
				<h1>WHATSAPP</h1>
				<p><?php echo utf8_encode($unidade->whatsapp);?></p>
			</td>
			<td>
				<h1>DATA DE EMISSÃO</h1>
				<p><?php echo date('d/m/Y',strtotime($evolucao->data));?></p>
			</td>
		</tr>
	</table>
</div>

<?php
include "print-footer.php";
?>