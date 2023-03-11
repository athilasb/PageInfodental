<?php

	include "print-header.php";

	$evolucao = $paciente = $clinica = $profissional = "";
	$exames = $_exames = array();
	if(isset($_GET['id'])) {
		$sql->consult($_p."pacientes_evolucoes","*","where md5(id)='".addslashes($_GET['id'])."' and id_tipo=6");
		if($sql->rows) {
			$evolucao=mysqli_fetch_object($sql->mysqry);

			$sql->consult($_p."colaboradores","id,nome,cro,uf_cro","where id=$evolucao->id_profissional");
			if($sql->rows) {
				$profissional=mysqli_fetch_object($sql->mysqry);
			}

			$sql->consult($_p."clinica","*","");
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
		$jsc->alert("Clínica não encontrada! ($evolucao->id_clinica)","document.location.href='../dashboard.php'");
		die();
	}
	if(count($exames)==0) {
		$jsc->alert("Nenhum exame foi solicitado!","document.location.href='../dashboard.php'");
		die();
	}
	if(empty($profissional)) {
		$jsc->alert("Profissional não encontrado","document.location.href='../dashboard.php'");
		die();
	}


	$idade=idade($paciente->data_nascimento);

	$endereco=utf8_encode($clinica->endereco);

	/*if(!empty($clinica->logradouro)) $endereco=utf8_encode($clinica->logradouro);
	if(!empty($clinica->numero)) $endereco.=", ".utf8_encode($clinica->numero);
	if(!empty($clinica->complemento)) $endereco.=", ".utf8_encode($clinica->complemento);
	if(!empty($clinica->bairro)) $endereco.=",  ".utf8_encode($clinica->bairro);
	if(!empty($clinica->id_cidade) and is_numeric($clinica->id_cidade) and $clinica->id_cidade>0) {
		$sql->consult($_p."cidades","*","where id=$clinica->id_cidade");
		if($sql->rows) {
			$c=mysqli_fetch_object($sql->mysqry);
			$endereco.=", ".utf8_encode($c->titulo);
			if(!empty($clinica->estado)) $endereco.="-".$clinica->estado;
		}
	}*/
?>
			
<header class="titulo1">
	<h1>Pedido de Exame Complementar</h1>
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
	<h1>Clínica Radiológica</h1>	
</header>
<div class="box">
	<table>
		<tr>
			<td>
				<h1>Nome</h1>
				<p><?php echo utf8_encode($clinica->clinica_nome);?></p>
			</td>
			
			<td>
				<h1>Telefone</h1>
				<p><?php echo maskTelefone($clinica->telefone);?></p>
			</td>
		</tr>
		<tr>
			<td>
				<h1>Profissional</h1>
				<p><?php echo utf8_encode($profissional->nome);?></p>
			</td>
			<td>
				<h1>CRO</h1>
				<p><?php echo !empty($profissional->cro)?utf8_encode($profissional->cro):'-'?></p>
			</td>
			<td>
				<h1>UF</h1>
				<p><?php echo !empty($profissional->uf_cro)?utf8_encode($profissional->uf_cro):'-'?></p>
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
					<a href="https://www.waze.com/pt-BR/live-map/directions?locale=pt_BR&utm_source=waze_app&to=ll.<?php echo $clinica->lat."%2C".$clinica->lng;?>" target="_blank"><i class="iconify" data-icon="fa-brands:waze"></i> Waze</a> &nbsp; 
					<a href="https://www.google.com/maps/search/<?php echo $clinica->lat.",".$clinica->lng;?>" target="_blank"><i class="iconify" data-icon="simple-icons:googlemaps"></i> Google Maps</a>
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
				$regiao=' - GERAL';
				if(isset($x->opcao) and !empty($x->opcao)) {
					$opcoes=explode(",",utf8_encode($x->opcao));
					$regiao='';
					foreach($opcoes as $opcao) {
						$regiao.=" ".$opcao.", ";
					}
					$regiao=substr($regiao,0,strlen($regiao)-2);
				}
		?>
		<tr>
			<td>
				<p><strong><?php echo $cont++;?>) <?php echo utf8_encode($_exames[$x->id_exame]->titulo);?></strong></p>
				<p>Região: <?php echo $regiao;?></p>
				<?php
				if(!empty($x->obs)) {
				?>
				<p>Obs: <?php echo utf8_encode($x->obs);?></p>
				<?php
				}
				?>
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