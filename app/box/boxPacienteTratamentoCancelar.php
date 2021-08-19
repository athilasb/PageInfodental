	<?php
	require_once("../lib/conf.php");
	$dir="../";
	require_once("../usuarios/checa.php");

	$jsc = new Js();

	$_regioesOpcoes=array();
	$sql->consult($_p."parametros_procedimentos_regioes_opcoes","*","order by titulo asc");
	while($x=mysqli_fetch_object($sql->mysqry)) $_regioesOpcoes[$x->id_regiao][]=$x;

	$_regioes=array();
	$sql->consult($_p."parametros_procedimentos_regioes","*","");
	while($x=mysqli_fetch_object($sql->mysqry)) $_regioes[$x->id]=$x;

	$_procedimentos=array();
	$sql->consult($_p."parametros_procedimentos","*","where lixo=0");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_procedimentos[$x->id]=$x;
	}	

	$_formasDePagamento=array();
	$sql->consult($_p."parametros_formasdepagamento","*","order by titulo asc");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_formasDePagamento[$x->id]=$x;
	}

	$tratamento='';
	if(isset($_GET['id_tratamento']) and is_numeric($_GET['id_tratamento'])) {
		$sql->consult($_p."pacientes_tratamentos","*","where id='".$_GET['id_tratamento']."'"); 
		if($sql->rows) {
			$tratamento=mysqli_fetch_object($sql->mysqry);
		}
	}

	if(empty($tratamento)) {
		$jsc->jAlert("Tratamento não encontrado!","erro","$.fancybox.close()");
		die();
	} else if($tratamento->status!="APROVADO") {
		$jsc->jAlert("Este tratamento não está aprovado!","erro","$.fancybox.close()");
	}
	

	$unidade='';
	if(isset($_GET['id_unidade']) and is_numeric($_GET['id_unidade']) and isset($_optUnidades[$_GET['id_unidade']])) {
		$unidade=$_optUnidades[$_GET['id_unidade']];
	}
	if(empty($unidade)) {
		$jsc->jAlert("Unidade não encontrada!","erro","$.fancybox.close()");
		die();
	}
	
	$campos=explode(",","id_paciente,profissionais,id_cadeira,id_status,clienteChegou,emAtendimento,agenda_data,agenda_hora,agenda_duracao,obs,procedimentos");
	foreach($campos as $v) {
		if($v=="profissionais") $values[$v]=array();
		else $values[$v]='';
	}


	
?>
<section class="modal" style="height:auto;">

	<header class="modal-header">
		<div class="filtros">
			<h1 class="filtros__titulo"><?php echo utf8_encode($tratamento->titulo);?></h1>
		</div>
	</header>

	<article class="modal-conteudo">

		<form method="post" class="form js-form-agendamento">
			<fieldset>
				<legend>Procedimentos</legend>
				<div class="registros">
					<table class="js-table-procedimentos">
						<thead>
							<tr>
								<th>Procedimento</th>
								<th>Região Qtd</th>
								<th>Profissional</th>
								<th>Plano</th>
								<th>Valor</th>
								<th>Situação</th>								
							</tr>
						</thead>
						<tbody class="js-procedimentos">
						<?php
						$sql->consult($_p."pacientes_tratamentos_procedimentos","*","where id_tratamento=$tratamento->id and situacao='aprovado' and lixo=0");
						while($x=mysqli_fetch_object($sql->mysqry)) {
						?>
						<tr>
							<td><?php echo $x->procedimento;?></td>
							<td><?php echo $x->quantitativo==1?$x->quantidade:$x->opcao;?></td>
							<td><?php echo $x->profissional;?></td>
							<td><?php echo $x->plano;?></td>
							<td><?php echo number_format($x->valor,2,",",".");?></td>
							<td><?php echo $x->situacao;?></td>
						</tr>
						<?php	
						}
						?>
						</tbody>
					</table>
				</div>
			</fieldset>

			<fieldset>
				<legend>Pagamentos</legend>
				<div class="registros">
					<table>
						<thead>
							<tr>
								<th>Vencto</th>
								<th>Valor</th>
								<th>Forma Pagto</th>
							</tr>
						</thead>
						<tbody class="js-pagamentos">
							<?php
							$sql->consult($_p."pacientes_tratamentos_pagamentos","*","where id_tratamento=$tratamento->id  and lixo=0");
							while($x=mysqli_fetch_object($sql->mysqry)) {
							?>
							<tr>
								<td><?php echo date('d/m/Y',strtotime($x->data_vencimento));?></td>
								<td><?php echo number_format($x->valor,2,",",".");?></td>
								<td><?php echo isset($_formasDePagamento[$x->id_formapagamento])?utf8_encode($_formasDePagamento[$x->id_formapagamento]->titulo):'-';?></td>
							</tr>
							<?php	
							}
							?>
						</tbody>
					</table>		
				</div>

			</fieldset>
		</form>
	</article>


</section>