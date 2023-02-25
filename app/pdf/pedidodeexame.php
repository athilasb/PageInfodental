<?php

	include "print-header.php";

	$evolucao = $paciente = $clinica = $solicitante = "";
	$exames = $_exames = array();
	if(isset($_GET['id'])) {
		$sql->consult($_p."pacientes_evolucoes","*","where md5(id)='".addslashes($_GET['id'])."' and id_tipo=6");
		if($sql->rows) {
			$evolucao=mysqli_fetch_object($sql->mysqry);

			$sql->consult($_p."colaboradores","id,nome","where id=$evolucao->id_profissional");
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

			$examesIds=array(0);
			$sql->consult($_p."pacientes_evolucoes_pedidosdeexames","*","where id_evolucao=$evolucao->id and lixo=0");
			
			while($x=mysqli_fetch_object($sql->mysqry)) {
				$exames[]=$x;
				$examesIds[]=$x->id_exame;
			}

			$sql->consult($_p."parametros_examedeimagem","*","where id IN (".implode(",",$examesIds).")");
			while($x=mysqli_fetch_object($sql->mysqry)) {
				$_exames[$x->id]=$x;
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
	if(count($exames)==0) {
		$jsc->alert("Nenhum exame foi solicitado!","document.location.href='../dashboard.php'");
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
	<h1>Pedido de Exame Complementar</h1>
	<p><?php echo date('d/m/Y',strtotime($evolucao->data));?></p>
</header>

<div class="ficha">
	<?php
	$ftPaciente='../img/ilustra-perfil.png';
	$ft='../arqs/pacientes/'.$paciente->id.".".$paciente->foto;
	if(file_exists($ft)) {
		$ftPaciente=$ft;
	}
	?>
	<img src="<?php echo $ftPaciente;?>" alt="" width="80" height="80" class="ficha__foto" />
	<table>
		<tr>
			<td colspan="2"><strong><?php echo utf8_encode($paciente->nome);?></strong></td>
			<td><?php echo $paciente->sexo=="M"?"Masculino":"Feminino";?></td>
		</tr>
		<tr>
			<td><?php echo $idade>1?"$idade anos":"$idade ano";?></td>
			<td>#<?php echo $paciente->id;?></td>
			<td>
				<?php
				if($paciente->data!='0000-00-00 00:00:00') {
					$dtCadastro = new DateTime($paciente->data);
					$dtHoje = new DateTime();
					$dif = $dtCadastro->diff($dtHoje);
				?>
				Paciente há <?php echo $dif->days;?> dias
				<?php
				}
				?>
			</td>
		</tr>
	</table>
</div>

<header class="titulo2">
	<h1>Clínica Radiológica</h1>	
</header>
<div class="box">
	<table>
		<tr>
			<td>
				<h1>Nome</h1>
				<p><?php echo utf8_encode($clinica->razao_social);?></p>
			</td>
			<td>
				<h1>Telefone</h1>
				<p><?php echo maskTelefone($clinica->telefone1);?></p>
			</td>
			<td>
				<h1>Solicitado por</h1>
				<p><?php echo utf8_encode($solicitante->nome);?></p>
			</td>
		</tr>
		<?php
		if(!empty($endereco)) {
		?>
		<tr>
			<td colspan="3">
				<h1>Endereço</h1>
				<p>
					<?php 
					echo $endereco;
					//Rua T-29, 875, Setor Bueno. Goiânia-GO. CEP: 74210-050
					?>
				</p>
			</td>
		</tr>
		<?php
		}

		if(!empty($clinica->lat) and !empty($clinica->lng)) {
		?>
		<tr>
			<td>
				<h1>Como chegar</h1>
				<p>
					<a href=""><i class="iconify" data-icon="fa-brands:waze"></i> Waze</a> &nbsp; 
					<a href=""><i class="iconify" data-icon="simple-icons:googlemaps"></i> Google Maps</a>
				</p>
			</td>
		</tr>
		<?php
		}
		?>
	</table>
</div>

<header class="titulo2">
	<h1>Listagem de Exames</h1>	
</header>
<div class="box">
	<table>
		<?php
		$cont=1;
		foreach($exames as $x) {
			if(isset($_exames[$x->id_exame])) {
		?>
		<tr>
			<td>
				<p><strong><?php echo $cont++;?>) <?php echo utf8_encode($_exames[$x->id_exame]->titulo);?></strong></p>
				<p>Obs: <?php echo $x->obs;?></p>
			</td>
		</tr>
		<?php
			}
		}
		?>
	</table>
</div>


<?php
include "print-footer.php";
?>