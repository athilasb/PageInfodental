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

	if($paciente->data_nascimento !="0000-00-00"){
		$idade=idade($paciente->data_nascimento);	
	} else {
		$idade = "";
	}


?>
<head>
<link rel="stylesheet" type="text/css" href="../css/annamnese.css?v08">
</head>

<header style="text-align: center;" >
	<table class="text-center w100">
      <thead>
			<h1 class="Titulo">Formulário da anamnese</h1> 
			<span class="sub-titulo" ><?php echo date('d/m/Y',strtotime($evolucao->data));?></span>   
		</td>
      </thead>
    </table>
</header>



<table class="dados-pessoais">
        <tbody>
            <tr>
              <td ><b><?php echo utf8_encode($paciente->nome);?></b> </td>
              <td class="text-right"><span class="iconify" data-icon="mdi:file-document-outline"></span><?php $cpf = utf8_encode($paciente->cpf); $cpf_formatado = preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $cpf);echo $cpf_formatado;?>
</td>
             
            </tr>    
            <tr  class=>
                <td><?php echo $idade>1?"$idade anos":"$idade";?></td> 
                <td class="text-right"><span class="iconify" data-icon="bxs:phone"></span><?php echo maskTelefone($paciente->telefone1);?></td>
              </tr> 
          </tbody>
</table>

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
