<?php
	include "print-header.php";
	$evolucao = $paciente = $clinica = $solicitante = "";
	$exames = $_exames = array();
	if(isset($_GET['id'])) {
		$sql->consult($_p."pacientes_evolucoes","*","where md5(id)='".addslashes($_GET['id'])."' and id_tipo=1");
		if($sql->rows) {
			$evolucao=mysqli_fetch_object($sql->mysqry);

			$sql->consult($_p."colaboradores","id,nome","where id=$evolucao->id_usuario");
			if($sql->rows) {
				$solicitante=mysqli_fetch_object($sql->mysqry);
			}

			$sql->consult($_p."pacientes","*","where id=$evolucao->id_paciente");
			if($sql->rows) {
				$paciente=mysqli_fetch_object($sql->mysqry);
			}

			$_anamnesePerguntas=array();
			$sql->consult($_p."pacientes_evolucoes_anamnese","*","where id_evolucao=$evolucao->id and lixo=0");
			if($sql->rows) {
				while($x=mysqli_fetch_object($sql->mysqry)) {
					$_anamnesePerguntas[]=$x;
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

	if(empty($solicitante)) {
		$jsc->alert("Solicitante não encontrado","document.location.href='../dashboard.php'");
		die();
	}


	$idade=idade($paciente->data_nascimento);



?>
			
<header class="titulo1">
	<h1>Ficha do Paciente</h1>
	<p><?php echo date('d/m/Y',strtotime($evolucao->data));?></p>
</header>

<div class="ficha">
	<table border="0">
		<tr>
			<td colspan="3"><strong><?php echo utf8_encode($paciente->nome);?></strong></td>
		</tr>
		<tr>
			<td><?php echo $idade>1?"$idade anos":"$idade ano";?></td>
			<td><?php echo $paciente->sexo=="M"?"Masculino":"Feminino";?></td>
			<td style="text-align:right;"><span class="iconify" data-icon="bxs:phone" data-inline="true"></span> <?php echo maskTelefone($paciente->telefone1);?></td>
		</tr>
	</table>
</div>

<header class="titulo2">
	<span>
		<h1>Formulário da Anamnese</h1>
		<h2><?php echo utf8_encode($solicitante->nome);?></h2>
	</span>
</header>

<div class="box">
	<table>
		<?php
		foreach($_anamnesePerguntas as $p) {
			$pergunta=json_decode($p->json_pergunta);
		?>
		<tr>
			<td>
				<p><strong><?php echo utf8_encode($p->pergunta);?></strong></p>
				<p>
					<?php 
					if($pergunta->tipo=="simnao" or $pergunta->tipo=="simnaotexto") {
						if($p->resposta=="SIM") echo "Sim";
						else echo "Não";
					} else if($pergunta->tipo=="nota") {
						echo "Nota: ".$p->resposta;
					} 
					?>	
				</p>
				<?php
				if(!empty($p->resposta_texto)) {
					echo "<p>Resposta: ".utf8_encode($p->resposta_texto)."</p>";
				}
				?>
			</td>
		</tr>
		<?php
		}
		?>
		
	</table>
</div>

<?php
include "print-footer.php";
?>