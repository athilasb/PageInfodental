<?php
	include "includes/header.php";
	include "includes/nav.php";


	require_once("includes/header/headerPacientes.php");

	$_table=$_p."pacientes_tratamentos";

	$where="WHERE id_paciente=$paciente->id and lixo=0";
	$sql->consult($_table,"*",$where);

	$registros=array();
	$tratamentosIDs=array(0);
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$registros[]=$x;
		$tratamentosIDs[]=$x->id;
	}

	$_procedimentosAprovado=array();
	$procedimentosIds=$tratamentosProcedimentosIDs=array(-1);
	$sql->consult($_table."_procedimentos","*","where id_tratamento IN (".implode(",",$tratamentosIDs).") and situacao='aprovado' and lixo=0");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$tratamentosProcedimentosIDs[]=$x->id;
		$_procedimentosAprovado[$x->id]=$x;
	}

	$procedimentosIds=array(0);
	$sql->consult($_p."pacientes_tratamentos_procedimentos_evolucao","*","where id_tratamento_procedimento IN (".implode(",",$tratamentosProcedimentosIDs).") and lixo=0");

	while($x=mysqli_fetch_object($sql->mysqry)) {
		if(isset($_procedimentosAprovado[$x->id_tratamento_procedimento])) {
			$p=$_procedimentosAprovado[$x->id_tratamento_procedimento];
			//echo $x->id_tratamento_procedimento."<BR>";
			if($x->status_evolucao=="finalizado") {
				$_procedimentosFinalizados[$p->id_tratamento][]=$x;
			} 
			$_todosProcedimentos[$p->id_tratamento][]=$x;
			$procedimentosIds[]=$x->id_procedimento;
		}
	}
	$_procedimentos=array();
	$sql->consult($_p."parametros_procedimentos","*","where id IN (".implode(",",$procedimentosIds).")");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_procedimentos[$x->id]=$x;
	}


	$sql->consult($_table."_pagamentos","*","where id_tratamento IN (".implode(",",$tratamentosIDs).") and id_fusao=0 and lixo=0");
	$pagRegs=array();
	$pagamentosIds=array(0);
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$pagamentosIds[]=$x->id;
		$pagRegs[]=$x;
	}

	$_baixas=array();
	$sql->consult($_p."pacientes_tratamentos_pagamentos_baixas","*","where id_pagamento IN (".implode(",",$pagamentosIds).") and lixo=0");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_baixas[$x->id_pagamento][]=$x;
	}


	$_pagamentos=array();
	foreach($pagRegs as $x) {

		// se possui baixa
		if(isset($_baixas[$x->id])) {

			$valorTotal=$x->valor;
			$valorBaixas=0;
			foreach($_baixas[$x->id] as $b) {
				$_pagamentos[$x->id_tratamento][]=array('pago'=>$b->pago,
														'tipo'=>'baixa',
														'valor'=>$b->valor);
				$valorBaixas+=$b->valor;
			}

			// restante que falta dar baixa
			if($valorTotal>$valorBaixas) {
				$_pagamentos[$x->id_tratamento][]=array('pago'=>0,
														'tipos'=>'restante',
														'valor'=>$valorTotal-$valorBaixas);

			}

		} else {

			$_pagamentos[$x->id_tratamento][]=array('pago'=>$x->pago,
													'tipo'=>'parcela '.$x->id,
													'valor'=>$x->valor);
			
		}
	}

?>

	

	<main class="main">
		<div class="main__content content">

			<section class="filter">
				<div class="filter-group">
					<div class="filter-form form">
						<dl>
							<dd><a href="pg_pacientes_planosdetratamento_form.php?id_paciente=<?php echo $paciente->id;?>" class="button button_main"><i class="iconify" data-icon="fluent:add-circle-24-regular"></i><span>Adicionar Tratamento</span></a>
						</dl>
					</div>
				</div>
			</section>

			<script type="text/javascript">
				$(function(){
					$('.js-item').click(function(){
						let id = $(this).attr('data-id');
						document.location.href=`pg_pacientes_planosdetratamento_form.php?edita=${id}<?php echo empty($url)?"":"&".$url;?>`;
					})
				})
			</script>

			<div class="box">
				<div class="list1">
					<?php
					if(count($registros)==0) {
						echo '<center>Nenhum Plano de Tratamento</center>';
					} else {
					?>
					<table>
						<?php
						foreach($registros as $x) {

							$procedimentos=array();
							if(isset($_procedimentos[$x->id])) $procedimentos=$_procedimentos[$x->id];

							$pagamentos=array();
							if(isset($_pagamentos[$x->id])) $pagamentos=$_pagamentos[$x->id];

						?>

						<tr class="js-item" data-id="<?php echo $x->id;?>">								
							<td>
								<h1><?php echo utf8_encode($x->titulo);?></h1>
								<p><?php echo date('d/m/Y H:i',strtotime($x->data));?></p>
							</td>
							<td>
								<?php
								if($x->status=="PENDETE") {
									echo '<div class="list1__icon" style="color:gray;"><i class="iconify" data-icon="fluent:timer-24-regular"></i> Aguardando Aprovação</div>';
								}
								else if($x->status=="CANCELADO") {
									echo '<div class="list1__icon" style="color:var(--vermelho)"><i class="iconify" data-icon="fluent:dismiss-square-24-regular"></i> Reprovado</div>';
								} 
								else if($x->status=="APROVADO") {
									echo '<div class="list1__icon" style="color:var(--verde)"><i class="iconify" data-icon="fluent:checkbox-checked-24-filled"></i> Aprovado</div>';
								}
								?>
							</td>
							<td style="width:20%;">
								<?php
								if($x->id_aprovado==0) {
									echo '-';
								} else {
									$pagamentos=array();
									if(isset($_pagamentos[$x->id])) $pagamentos=$_pagamentos[$x->id];

									$procedimentos=array();
									if(isset($_procedimentos[$x->id])) $procedimentos=$_procedimentos[$x->id];

									$total=isset($_todosProcedimentos[$x->id])?count($_todosProcedimentos[$x->id]):0;
									$finalizados=isset($_procedimentosFinalizados[$x->id])?count($_procedimentosFinalizados[$x->id]):0;
									$perc=($total)==0?0:number_format(($finalizados/($total))*100,0,"","");



									$pagPago=$pagTotal=0;
									foreach($pagamentos as $p) { 
										$p=(object)$p;
										if($p->pago==1) $pagPago+=$p->valor;

										$pagTotal+=$p->valor;
									}
									$percPag=($pagTotal)==0?0:number_format(($pagPago/($pagTotal))*100,0,"","");

								?>
								<div class="chart-bar">
									<header>
										<p>Evolução (<?php echo $finalizados." de ".$total;?>)</p>
									</header>
									<article>
										<span style="width:<?php echo $perc;?>%"></span>
									</article>
								</div>
								<?php
								}
								?>
							</td>
							<td style="width:20%;">
								<?php
								if($x->id_aprovado==0) {
									echo '-';
								} else {
									if(count($pagamentos)==0) echo '';//<a href="javascript" class="tooltip" title="Nenhum pagamento foi adicionado"><span class="iconify" data-icon="eva:alert-triangle-fill" data-inline="false" data-height="25"></span></a>';
									else {
								?>
								<div class="chart-bar">
									<header>
										<p>Pagamento (<?php echo "<b>".number_format($pagPago,2,",",".")."</b> de <b>".number_format($pagTotal,2,",",".")."</b>";?>)</p>
									</header>
									<article>
										<span style="width:<?php echo $percPag;?>%"></span>
									</article>
								</div>
								<?php
									}
								}
								?>
							</td>
						</tr>
						<?php
						}

						/*
						?>
						<tr>								
							<td>
								<h1>Plano Teste</h1>
								<p>18/11/2021 07:56</p>
							</td>
							<td><div class="list1__icon" style="color:gray;"><i class="iconify" data-icon="fluent:timer-24-regular"></i> Aguardando Aprovação</div></td>
							<td style="width:20%;">
								<div class="chart-bar">
									<header>
										<p>Evolução (0 de 4)</p>
									</header>
									<article>
										<span style="width:0%"></span>
									</article>
								</div>
							</td>
							<td style="width:20%;">
								<div class="chart-bar">
									<header>
										<p>Pagamento (recebido 0%)</p>
									</header>
									<article>
										<span style="width:0%"></span>
									</article>
								</div>
							</td>
						</tr>
						<tr>								
							<td>
								<h1>Plano Teste</h1>
								<p>18/11/2021 07:56</p>
							</td>
							<td><div class="list1__icon" style="color:var(--verde)"><i class="iconify" data-icon="fluent:checkbox-checked-24-filled"></i> Aprovado</div></td>
							<td style="width:20%;">
								<div class="chart-bar">
									<header>
										<p>Evolução (3 de 4)</p>
									</header>
									<article>
										<span style="width:75%"></span>
									</article>
								</div>
							</td>
							<td style="width:20%;">
								<div class="chart-bar">
									<header>
										<p>Pagamento (recebido 33%)</p>
									</header>
									<article>
										<span style="width:33%"></span>
									</article>
								</div>
							</td>
						</tr>
						<tr>								
							<td>
								<h1>Plano Teste</h1>
								<p>18/11/2021 07:56</p>
							</td>
							<td><div class="list1__icon" style="color:var(--verde)"><i class="iconify" data-icon="fluent:checkbox-checked-24-filled"></i> Aprovado</div></td>
							<td style="width:20%;">
								<div class="chart-bar">
									<header>
										<p>Evolução (1 de 4)</p>
									</header>
									<article>
										<span style="width:25%"></span>
									</article>
								</div>
							</td>
							<td style="width:20%;">
								<div class="chart-bar">
									<header>
										<p>Pagamento (recebido 0%)</p>
									</header>
									<article>
										<span style="width:0%"></span>
									</article>
								</div>
							</td>
						</tr>
						<tr>								
							<td>
								<h1>Plano Teste</h1>
								<p>18/11/2021 07:56</p>
							</td>
							<td><div class="list1__icon" style="color:var(--verde)"><i class="iconify" data-icon="fluent:checkbox-checked-24-filled"></i> Aprovado</div></td>
							<td style="width:20%;">
								<div class="chart-bar">
									<header>
										<p>Evolução (5 de 10)</p>
									</header>
									<article>
										<span style="width:50%"></span>
									</article>
								</div>
							</td>
							<td style="width:20%;">
								<div class="chart-bar">
									<header>
										<p>Pagamento (recebido 100%)</p>
									</header>
									<article>
										<span style="width:100%"></span>
									</article>
								</div>
							</td>
						</tr>
						<tr>								
							<td>
								<h1>Plano Teste</h1>
								<p>18/11/2021 07:56</p>
							</td>
							<td><div class="list1__icon" style="color:var(--vermelho)"><i class="iconify" data-icon="fluent:dismiss-square-24-regular"></i> Reprovado</div></td>
							<td style="width:20%;">
								<div class="chart-bar">
									<header>
										<p>Evolução (1 de 10)</p>
									</header>
									<article>
										<span style="width:25%"></span>
									</article>
								</div>
							</td>
							<td style="width:20%;">
								<div class="chart-bar">
									<header>
										<p>Pagamento (recebido 33%)</p>
									</header>
									<article>
										<span style="width:33%"></span>
									</article>
								</div>
							</td>
						</tr>
						*/
						?>
					</table>
					<?php
					}
					?>
				</div>	
			</div>

		</div>
	</main>

<?php 
include "includes/footer.php";
?>	