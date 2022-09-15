<?php
	include "includes/header.php";
	include "includes/nav.php";


	require_once("includes/header/headerPacientes.php");
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

			<div class="box">
				<div class="list1">
					<table>
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
					</table>
				</div>	
			</div>

		</div>
	</main>

<?php 
include "includes/footer.php";
?>	