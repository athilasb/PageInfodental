<?php
	include "print-header.php";
	$evolucao = $paciente = $clinica = $solicitante = "";
	$receituario = array();
	if(isset($_GET['id'])) {
		$sql->consult($_p."pacientes_evolucoes","*","where md5(id)='".addslashes($_GET['id'])."' and id_tipo=7");
		if($sql->rows) {
			$evolucao=mysqli_fetch_object($sql->mysqry);


			$sql->consult($_p."colaboradores","id,nome","where id=$evolucao->id_usuario");
			if($sql->rows) {
				$solicitante=mysqli_fetch_object($sql->mysqry);
			}

			$sql->consult($_p."parametros_fornecedores","*","where lixo=0 and tipo='CLINICA' order by razao_social, nome asc");
			if($sql->rows) {
				$clinica=mysqli_fetch_object($sql->mysqry);
			}

			$sql->consult($_p."pacientes","*","where id=$evolucao->id_paciente");
			if($sql->rows) {
				$paciente=mysqli_fetch_object($sql->mysqry);
			}

			$controleEspecial=false;
			$sql->consult($_p."pacientes_evolucoes_receitas","*","where id_evolucao=$evolucao->id and lixo=0");
			while($x=mysqli_fetch_object($sql->mysqry)) {
				$receituario[]=$x;
				if($x->controleespecial==1) $controleEspecial=true;
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
	if(empty($clinica)) {
		$jsc->alert("Clínica não encontrada!","document.location.href='../dashboard.php'");
		die();
	}
	if(count($receituario)==0) {
		$jsc->alert("Nenhum receituário foi solicitado!","document.location.href='../dashboard.php'");
		die();
	}
	if(empty($solicitante)) {
		$jsc->alert("Solicitante não encontrado","document.location.href='../dashboard.php'");
		die();
	}

	$idade=idade($paciente->data_nascimento);

	$endereco="";

	if(!empty($clinica->logradouro)) $endereco=utf8_encode($clinica->logradouro);
	if(!empty($clinica->numero)) $endereco.=", ".utf8_encode($clinica->logradouro);
	if(!empty($clinica->complemento)) $endereco.=", ".utf8_encode($clinica->complemento);
	if(!empty($clinica->bairro)) $endereco.=",  ".utf8_encode($clinica->bairro);
	if(!empty($clinica->id_cidade) and is_numeric($clinica->id_cidade) and $clinica->id_cidade>0) {
		$sql->consult($_p."cidades","*","where id=$clinica->id_cidade");
		if($sql->rows) {
			$c=mysqli_fetch_object($sql->mysqry);
			$endereco.=", ".utf8_encode($c->titulo);
			if(!empty($clinica->estado)) $endereco.="-".$clinica->estado;
		}
	}

?>
			
<header class="titulo1">
	<h1 style="margin:auto"><?php echo $controleEspecial===true?"Receituário Especial":"Receituário Simples";?></h1>
</header>

<div class="box box_empty">
	<table>
		<tr>
			<td>
				<h1>PACIENTE</h1>
				<p><?php echo utf8_encode($paciente->nome);?></p>
			</td>
			<td>
				<h1>IDADE</h1>
				<p><?php echo $idade>1?"$idade anos":"$idade ano";?></p>
			</td>
			<td>
				<h1>SEXO</h1>
				<p><?php echo $paciente->sexo=="M"?"Masculino":"Feminino";?></p>
			</td>
		</tr>
	</table>
</div>

<header class="titulo2">
	<h1>Prescrição</h1>
</header>
<div class="box box_empty">
	<?php
	$cont=1;
	foreach($receituario as $x) {
	?>
	<div class="prescricao">
		<div class="prescricao__item">
			<h1><?php echo $cont++;?>) <?php echo utf8_encode($x->medicamento);?></h1>
			<span></span>
			<h2><?php echo $x->quantidade." ".(isset($_medicamentosTipos[$x->tipo])?$_medicamentosTipos[$x->tipo]:$x->tipo);?></h2>
		</div>
		<p class="prescricao__obs"><?php echo utf8_encode($x->posologia);?></p>
	</div>
	<?php
	}
	?>
	
</div>
<div class="box" style="margin-top:2.5rem;">
	<table>
		<tr>
			<td colspan="2">
				<h1>NOME DO MÉDICO</h1>
				<p>KRONER MACHADO COSTA</p>
			</td>
			<td>
				<h1>CRM</h1>
				<p>284984874</p>
			</td>
			<td>
				<h1>UF</h1>
				<p>GO</p>
			</td>
		</tr>
		<tr>
			<td colspan="3">
				<h1>LOCAL DE ATENDIMENTO</h1>
				<p>Rua 5, 691, The Prime Tamandaré Office, Térreo Loja 1, Setor Bueno</p>
			</td>
			<td>
				<h1>CNES</h1>
				<p>4787248</p>
			</td>
		</tr>
		<tr>		
			<td>
				<h1>CIDADE</h1>
				<p>GOIÂNIA</p>
			</td>
			<td>
				<h1>UF</h1>
				<p>GO</p>
			</td>
			<td>
				<h1>TELEFONE</h1>
				<p>(62) 3515-1717</p>
			</td>
			<td>
				<h1>DATA DE EMISSÃO</h1>
				<p>08/10/2021</p>
			</td>
		</tr>
	</table>
</div>

<?php
	if($controleEspecial===true) {
?>
<div style="display:grid; grid-template-columns:1fr 1fr; grid-gap:1.5rem;">
	<div style="flex:1; border:1px solid silver; border-radius:12px; padding:.5rem;">

		<header class="titulo2">
			<h1 style="font-size:1em;">Identificação do Comprador</h1>
		</header>
		<table class="no-padding">
			<tr>
				<td><h1>Nome completo</h1></td>
			</tr>
			<tr>
				<td><h1>RG</h1></td>
				<td><h1>Órgão Emissor</h1></td>
			</tr>
			<tr>
				<td style="vertical-align:top; height:60px;"><h1>Endereço Completo</h1></td>
			</tr>
			<tr>
				<td><h1>Cidade</h1></td>
				<td><h1>UF</h1></td>
			</tr>
			<tr>
				<td><h1>Telefone</h1></td>
			</tr>
		</table>

	</div>
	<div style="flex:1; border:1px solid silver; border-radius:12px; padding:.5rem;">

		<header class="titulo2">
			<h1 style="font-size:1em;">Identificação do Fornecedor</h1>
		</header>
		<table class="no-padding">
			<tr>
				<td colspan="2"><h1>Nome Farmacêutico(a)</h1></td>
			</tr>
			<tr>
				<td><h1>CPF</h1></td>
				<td><h1>UF</h1></td>
			</tr>
			<tr>
				<td><h1>Nome Farmácia</h1></td>
			</tr>
			<tr>
				<td><h1>Endereço</h1></td>
			</tr>
			<tr>
				<td><h1>Cidade</h1></td>
				<td><h1>UF</h1></td>
			</tr>
			<tr>
				<td><h1>CNPJ</h1></td>
				<td><h1>Telefone</h1></td>
			</tr>
			<tr>
				<td colspan="2" style="vertical-align:bottom; height:50px; text-align:center;"><h1>ASSINATURA FARMACÊUTICO(A)</h1></td>
			</tr>
		</table>

	</div>
</div>
<?php	
	}
?>

<?php
include "print-footer.php";
?>