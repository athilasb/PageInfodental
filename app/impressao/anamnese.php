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
<link rel="stylesheet" type="text/css" href="../css/annamnese.css">
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
				<td class="text-right"><span class="iconify" data-icon="mdi:file-document-outline"></span><?php $cpf = utf8_encode($paciente->cpf); $cpf_formatado = preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $cpf);echo $cpf_formatado;?></td>   
			</tr>    
			<tr>
				<td><?php echo $idade>1?"$idade anos":"$idade";?></td> 
				<td class="text-right"><span class="iconify" data-icon="bxs:phone"></span><?php echo maskTelefone($paciente->telefone1);?></td>
			</tr> 
	</tbody>
</table>

<div class="page-form">
	<section>
		<table>
			<?php
			foreach($_anamnesePerguntas as $p) {
				$pergunta=json_decode($p->json_pergunta);
			?>
			<tr>
				<td>
					<p class="pergunta-form"><strong><?php echo utf8_encode($p->pergunta);?></strong></p>
					<p class="resposta-form">
						<?php 
						if($pergunta->tipo=="simnao" or $pergunta->tipo=="simnaotexto") {
							if($p->resposta=="SIM") echo "<span class='iconify' data-icon='fluent:chat-12-regular'></span> Sim";
							else echo "<span class='iconify' data-icon='fluent:chat-12-regular'></span> Não";
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
	</section>
	<section class="box-border">
		<table class="w95 conteudo border-solid">
			<div>
				<tr>
				<td width="40%"><b>Nome</b></td>
				<td width="40%"><b>Telefone</b></td>
				<td width="40%"><b>Whatsapp</b></td>
				</tr>

				<tr>
					<td>Kroner</td>
					<td>(62) 99338-3782</td>
					<td>(62) 99338-3782</td>
				</tr>
				</div>
				<tr class="margin">
					<td><b>Nome do Profissional</b></td>
					<td><b>CRO</b></td>
					<td><b>UF</b></td>
				</tr>
				<tr>
					<td>Eliabio da Silva Pereira</td>
					<td>15656</td>
					<td>GO</td>
					</tr>
				<tr class="margin">
				<td colspan="3" > <b>Endereço</b></td>
				</tr>
				<tr>
				<td colspan="3">Studio Dental - Rua 5 - Setor Oeste, Goiânia - State of Goiás, Brazil</td>
				</tr>
			</div>	
		</table>
	</section>
	<section> <div class="assinatura-data">Sábado, 22 de Junho de 2022</div></section>
	<section>
		<table class="assinaturas"> 
			<tr class="display-flex-space-around margin-top-20">
				<td class="display-flex"> <img src="../img/Verificado.svg" alt=""> Assinado eletronicamente por:</td>
				<td class="display-flex"> <img src="../img/Verificado.svg" alt=""> Assinado eletronicamente por:</td>
			</tr>

			<tr class="display-flex-space-around">
				<td class="display-flex"><b>Luciano Dex Teste</b> </td>
				<td class="display-flex"><b>Athila Da Silva</b></td>
			</tr>
			<tr class="display-flex-space-around">
				<td class="display-flex">Cnpj: 000.000.000-00</td>
				<td class="display-flex">Cnpj: 000.000.000-00</td>
			</tr>

			<tr class="display-flex-space-around">
				<td class="display-flex border-bottom"><img src="../img/Assinatura.png" alt=""></td>
				<td class="display-flex border-bottom"><img src="../img/Assinatura.png" alt=""></td>
			</tr>

			<tr class="display-flex-space-around">
				<td class="display-flex font14">Profissional</td>
				<td class="display-flex font14">Paciente</td>
			</tr>
		</table>
	</section>
	<section> <div class="historico-titulo">Histórico do documento</div></section>
	<section class="historico">
		<table > 
			<tr>
				<td colspan="3" class="text-center icon-historico"> 
					<div class="iconify" data-icon="fluent:document-bullet-list-clock-24-regular"  data-width="24" data-height="24"></div> 
					<div><b>Enviado</b></div>
				</td>
				<td> 
					<div><b>17/02/2023</b></div> 
					<div>21:54:14 UTC</div>
				</td>
				<td>
					<div>
						Enviadas para assinatura de Juliana Rodrigues Mendonça</br>
						(contato@advjrm.com.br) and Kroner Machado Costa</br>
						(kronercosta@gmail.com) por ju.rmendonca@gmail.com</br>
						IP: 179.176.102.97</br>
					</div>
					
				</td>
			</tr>
			<tr>
				<td class="text-center icon-historico"> 
					<div class="iconify" data-icon="fluent:eye-tracking-20-regular"  data-width="24" data-height="24"></div> 
					<div><b>Visualizado</b></div>
				</td>
				<td> 
					<div><b>17/02/2023</b></div> 
					<div>21:54:14 UTC</div>
				</td>
				<td>
					<div>
						Visualizado por Kroner Machado Costa (kronercosta@gmail.com)</br>
						IP: 179.176.102.97</br>
					</div>
				</td>
			</tr>
			<tr>
				<td class="text-center icon-historico"> 
					<div class="iconify" data-icon="fluent:draw-shape-20-regular"  data-width="24" data-height="24"></div> 
					<div><b>Assinado</b></div>
				</td>
				<td> 
					<div><b>17/02/2023</b></div> 
					<div>21:54:14 UTC</div>
				</td>
				<td>
					<div>
						Assinado por Kroner Machado Costa (kronercosta@gmail.com)</br>
						IP: 179.176.102.97</br>
					</div>
				</td>
			</tr>
			<tr>
				<td class="text-center icon-historico"> 
					<div class="iconify" data-icon="fluent:checkmark-circle-20-regular"  data-width="24" data-height="24"></div> 
					<div><b>Concluído</b></div>
				</td>
				<td> 
					<div><b>17/02/2023</b></div> 
					<div>21:54:14 UTC</div>
				</td>
				<td>
					<div>
						O documento foi concluído.</br>
					</div>
				</td>
			</tr>
		</table>
	</section>
</div>

<?php
include "print-footer.php";
?>
