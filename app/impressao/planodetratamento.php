<?php
	include "print-header.php";

	$tratamento = $paciente = "";
	if(isset($_GET['id'])) {
		$sql->consult($_p."pacientes_tratamentos","*","where md5(id)='".addslashes($_GET['id'])."'");
		if($sql->rows) {
			$tratamento=mysqli_fetch_object($sql->mysqry);

			$sql->consult($_p."pacientes","*","where id=$tratamento->id_paciente");
			if($sql->rows) {
				$paciente=mysqli_fetch_object($sql->mysqry);
			}
		}
	}

	$jsc = new Js();

	if(empty($tratamento)) {
		$jsc->alert("Tratamento não cadastrado!","document.location.href='../dashboard.php'");
		die();
	}

	if(empty($paciente)) {
		$jsc->alert("Paciente não cadastrado!","document.location.href='../dashboard.php'");
		die();
	}

	$idade=idade($paciente->data_nascimento);

	$procedimentos=array();
	$pagamentos=array();
	$procedimentosIds=array(0);
	$valorTotal=$descontoTotal=$valorTotalSemDesconto=0;


	if($tratamento->status!="APROVADO") {
		$procedimentosObj = json_decode($tratamento->procedimentos);
		$pagamentosObj = json_decode($tratamento->pagamentos);


		foreach($procedimentosObj as $x) {
			$procedimentosIds[$x->id_procedimento]=$x->id_procedimento;
			if($x->situacao=="aprovado" or $x->situacao=="aguardandoAprovacao") {
				$descontoTotal+=$x->desconto;

				$valorTotalSemDesconto+=$x->valor*$x->quantidade;

				$x->valorSemDesconto=$x->valor*$x->quantidade;

				$valorTotal+=($x->valor*$x->quantidade)-$x->desconto;
			}
			$procedimentos[]=$x;
		}
		foreach($pagamentosObj as $x) {
			$pagamentos[]=$x;
		}
	} else {


		$sql->consult($_p."pacientes_tratamentos_procedimentos","*","where id_tratamento=$tratamento->id and lixo=0");
		while($x=mysqli_fetch_object($sql->mysqry)) {
			$procedimentos[]=$x;
			$procedimentosIds[$x->id_procedimento]=$x->id_procedimento;
			if($x->situacao=="aprovado" or $x->situacao=="aguardandoAprovacao") {
				$descontoTotal+=$x->desconto;
				$valorTotalSemDesconto+=$x->valorSemDesconto;
				$valorTotal+=$x->valor;
			}
		}

		$sql->consult($_p."pacientes_tratamentos_pagamentos","*","where id_tratamento=$tratamento->id and lixo=0 and fusao=0");
		while($x=mysqli_fetch_object($sql->mysqry)) {
			$x->vencimento=date('d/m/Y',strtotime($x->data_vencimento));
			$pagamentos[]=$x;
		}


	}

	$_procedimentos=array();
	$sql->consult($_p."parametros_procedimentos","*","where id IN (".implode(",",$procedimentosIds).")");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_procedimentos[$x->id]=$x;
	}

	$_profissionais=array();
	$sql->consult($_p."colaboradores","*","where cro<>'' order by nome asc");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_profissionais[$x->id]=$x;
	}

	$_formasDePagamento=array();
	$sql->consult($_p."parametros_formasdepagamento","*","order by titulo asc");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_formasDePagamento[$x->id]=$x;
	}

	$_status=array('aprovado'=>'Aprovado',
					'naoAprovado'=>'Não Aprovado',
					'aguardandoAprovacao'=>'Aguardando Aprovação',
					'observado'=>'Observado');

?>
			
<header class="titulo1">
	<h1>Plano de Tratamento</h1>
	<p style="font-size:1.25em;">
		<?php
		if($tratamento->status=="APROVADO") {
		?>
		<strong><i class="iconify" data-icon="el:ok"></i> Aprovado</strong>
		<?php
		} else if($tratamento->status=="PENDENTE") {
		?>
		<strong><i class="iconify" data-icon="ph-hourglass-high-fill"></i> Em aberto</strong>
		<?php
		} else if($tratamento->status=="CANCELADO") {
		?>
		<strong><i class="iconify" data-icon="topcoat:cancel"></i> Cancelado</strong>
		<?php
		}
		?>
	</p>
	<p><?php echo date('d/m/Y',strtotime($tratamento->data));?></p>
</header>

<div class="ficha">
	<img src="../img/ilustra-perfil.png" alt="" width="80" height="80" class="ficha__foto" />
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
	<h1>Listagem de Procedimentos (<?php echo count($procedimentos);?>)</h1>
</header>

<?php
	foreach($procedimentos as $proc) { 
		if(isset($_procedimentos[$proc->id_procedimento])) {
			$procedimento=$_procedimentos[$proc->id_procedimento];
?>
<div class="box" style="margin-bottom:-1px; border-radius:0 0 0 0;">
	<table>
		<tr>
			<td colspan="4">
				<h1>Procedimento</h1>
				<p><strong><?php echo utf8_encode($procedimento->titulo);?></strong>
					<?php echo $proc->quantidade>1?" - Qtd. $proc->quantidade":"";?>
					<?php echo !empty($proc->opcao)?" - ".utf8_encode($proc->opcao):"";?> - 
					<?php echo utf8_encode($proc->plano);?>
					<?php echo isset($_profissionais[$proc->id_profissional])?' - '.utf8_encode($_profissionais[$proc->id_profissional]->nome):'';?></p>				
				<?php echo (isset($proc->obs) and !empty($proc->obs))?"<p>".utf8_encode($proc->obs)."</p>":"";?>
			</td>			
		</tr>

		<tr>
			<td>
				<h1>Status</h1>
				<p>
					<?php 
					echo $_status[$proc->situacao];
					if($proc->situacao=="observado") {
					 	echo '<b> - Este procedimento está apenas Notado e não consta no plano de tratamento</b>';
					} else if($proc->situacao=="naoAprovado") {
					 	echo '<b> - Este procedimento foi reprovado e não consta no plano de tratamento</b>';
					}
					?>
						
				</p>
			</td>
			<?php
			if($proc->quantitativo==1) {
				$proc->valorSemDesconto*=$proc->quantidade;
			}
			if($proc->situacao=="aprovado" or $proc->situacao=="aguardandoAprovacao") {
			?>
			<td>
				<h1>Valor</h1>
				<p>R$ <?php echo number_format($proc->valorSemDesconto,2,",",".");?></p>
			</td>
			<td>
				<h1>Desconto</h1>
				<p>R$ <?php echo number_format($proc->desconto,2,",",".");?></p>
			</td>
			<td>
				<h1>Valor Corrigido</h1>
				<p>R$ <?php echo number_format(($proc->valorSemDesconto)-$proc->desconto,2,",",".");?></p>
			</td>
			<?php
			} else {
			/*?>
			<td>
				<h1>Valor</h1>
				<p>R$ <?php echo number_format($proc->valor,2,",",".");?></p>
			</td>
			<td>
				<h1>Desconto</h1>
				<p>R$ <?php echo number_format($proc->desconto,2,",",".");?></p>
			</td>
			<td>
				<h1>Valor Corrigido</h1>
				<p><strike>R$ <?php echo number_format($proc->valor-$proc->desconto,2,",",".");?></strike></p>
			</td>
			<?php*/
			}
			?>
		</tr>		
	</table>
</div>
<?php
		}
	}
?>
<header class="titulo2" style="margin-top:2rem;">
	<h1>Cronograma de Pagamento</h1>
</header>

<div class="box">
	<table>		
		<tr style="font-size:13pt">
			<?php
			if($descontoTotal==0) {
			?>
			<td>
				<h1>Valor Total</h1>
				<p>R$ <?php echo number_format($valorTotalSemDesconto,2,",",".");?></p>
			</td>
			<?php
			} else {
			?>
			<td>
				<h1>Valor Total</h1>
				<p><strike>R$ <?php echo number_format($valorTotalSemDesconto,2,",",".");?></strike></p>
			</td>
			<td>
				<h1>Desconto Total</h1>
				<p>R$ <?php echo number_format($descontoTotal,2,",",".");?></p>
			</td>
			<td>
				<h1>Valor Final</h1>
				<p>R$ <?php echo number_format($valorTotal,2,",",".");?></p>
			</td>
			<?php
			}
			?>
		</tr>	
		<?php
			$cont=1;
			foreach($pagamentos as $pag) {
		?>	
		<tr>
			<td style="width:30%">
				<h1>Data Vencimento</h1>
				<p><?php echo $pag->vencimento;?></p>
			</td>
			<td style="width:30%">
				<h1>Valor Parcela <?php echo $cont++;?></h1>
				<p>R$ <?php echo number_format($pag->valor,2,",", ",");?></p>
			</td>
			<td style="width:30%">
				<h1>Forma de Pagamento</h1>
				<p>
					<?php
					if(isset($pag->id_formapagamento) and $pag->id_formapagamento>0 and isset($_formasDePagamento[$pag->id_formapagamento])) {
						echo utf8_encode($_formasDePagamento[$pag->id_formapagamento]->titulo);

						if($_formasDePagamento[$pag->id_formapagamento]->tipo=="credito") echo " - ".(!empty($pag->qtdParcelas)?$pag->qtdParcelas:1)."x";
						if(isset($pag->identificador) and !empty($pag->identificador)) echo "<br />ID: ".utf8_encode($pag->identificador);
					} else echo "a definir";
					?>
				</p>
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