<?php
include "includes/header.php";
include "includes/nav.php";
?>

		<section class="content">

			<header class="caminho">
				<h1 class="caminho__titulo">Cadastros <i class="iconify" data-icon="bx-bx-chevron-right"></i> <strong>Pacientes</strong></h1>
				<a href="" class="caminho__tutorial button button__sec"><i class="iconify" data-icon="ic-baseline-slow-motion-video"></i> ASSISTIR TUTORIAL</a>
			</header>

			<section class="content-grid">

				<section class="content__item">

					<section class="filtros">
						<form method="post" class="filtros-form form">
							<div class="colunas6">
								<dl>
									<dt>Título</dt>
									<dd><input type="text" name="" /></dd>
								</dl>
								<dl>
									<dt>Título</dt>
									<dd><select name="" class="chosen"><option value=""></option><option value="">Valor</option></select></dd>
								</dl>
								<dl class="dl2">
									<dt>Título</dt>
									<dd><select name="" class="chosen" multiple><option value=""></option><option value="">Valor 1</option><option value="">Valor 2</option><option value="">Outro valor que faz pular</option></select></dd>
								</dl>
								<dl>
									<dd><button type="submit" class="button button__sec"><i class="iconify" data-icon="bx-bx-search" data-inline="false"></i></button></dd>
								</dl>
							</div>
						</form>
						<div class="filtros-acoes">
							<a href="modelo-form-ajax.php" data-fancybox data-type="ajax" data-padding="0" class="filtros-acoes__button tooltip" title="adicionar"><i class="iconify" data-icon="ic-baseline-add"></i></a>
						</div>
					</section>
					

					<div class="registros-qtd">
						<p class="registros-qtd__item">1075 itens</p>
						<p class="registros-qtd__item">R$3.353 total</p>
						<a href="modelo-form.php" class="registros-qtd__item"><i class="iconify" data-icon="bx-bxs-download"></i> download</a>
					</div>

					<div class="registros">
						<table class="tablesorter">
							<thead>
								<tr>
									<th>DATA</th>
									<th>NOME</th>
									<th>CPF</th>
									<th>TELEFONE</th>
									<th>CIDADE-UF</th>
									<th>PAINEL</th>
									<th style="width:160px;">AÇÕES</th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td>24/06/2020 13:27</td>
									<td>Kroner Machado Costa</td>
									<td>029.808.481-31</td>
									<td>62999181775</td>
									<td>GOIÂNIA-GO</td>
									<td><a href=""><i class="iconify" data-icon="ic-outline-dashboard"></i> PAINEL</a></td>
									<td>
										<a href="modelo-form.php" class="registros__acao"><i class="iconify" data-icon="bx:bxs-edit-alt"></i></a>
										<a href="" class="registros__acao registros__acao_sec"><i class="iconify" data-icon="bx:bxs-trash"></i></a>
									</td>
								</tr>
								<tr>
									<td>24/06/2020 13:27</td>
									<td>Kroner Machado Costa</td>
									<td>029.808.481-31</td>
									<td>62999181775</td>
									<td>GOIÂNIA-GO</td>
									<td><a href=""><i class="iconify" data-icon="ic-outline-dashboard"></i> PAINEL</a></td>
									<td>
										<a href="modelo-form.php" class="registros__acao"><i class="iconify" data-icon="bx:bxs-edit-alt"></i></a>
										<a href="" class="registros__acao registros__acao_sec"><i class="iconify" data-icon="bx:bxs-trash"></i></a>
									</td>
								</tr>
								<tr>
									<td>24/06/2020 13:27</td>
									<td>Kroner Machado Costa</td>
									<td>029.808.481-31</td>
									<td>62999181775</td>
									<td>GOIÂNIA-GO</td>
									<td><a href=""><i class="iconify" data-icon="ic-outline-dashboard"></i> PAINEL</a></td>
									<td>
										<a href="modelo-form.php" class="registros__acao"><i class="iconify" data-icon="bx:bxs-edit-alt"></i></a>
										<a href="" class="registros__acao registros__acao_sec"><i class="iconify" data-icon="bx:bxs-trash"></i></a>
									</td>
								</tr>
								
							</tbody>
						</table>
					</div>
					<div class="paginacao">
						<p class="paginacao__item"><span>Página</span><a href="" class="active">1</a><a href="">2</a><a href="">3</a>
					</div>
				</section>
			</section>
			
		</section>
	
<?php
include "includes/footer.php";
?>