<?php
	if(isset($_POST['ajax'])) {
		require_once("lib/conf.php");
		require_once("usuarios/checa.php");
		$rtn  = array();

		header("Content-type: application/json");
		echo json_encode($rtn);
		die();
	}

	include "includes/header.php";
	include "includes/nav.php";

?>


	<header class="header">
		<div class="header__content content">

			<div class="header__inner1">
				<section class="header-title">
					<h1>Financeiro</h1>
				</section>
				<?php require_once("includes/menus/menuFinaceiro.php");?>
			</div>
		</div>
	</header>
	<main class="main">
		<div class="main__content content">		
 			<section class="filter">
				<div class="filter-group">
					<div class="filter-form form">
						<dl>
							<dd><a href="javascript:;" class="button button_main"><i class="iconify" data-icon="fluent:add-circle-24-regular"></i> <span>Nova Cobrança</span></a></dd>
						</dl>
					</div>
				</div>
				<form method="get" class="js-filtro">
					<div class="filter-group">
						<div class="filter-form form">
							<dl>
								<dd>
									<select name="bi_multiple[]" multiple class="chosen" style="width:200px;" data-placeholder="Status...">
										<option value=""></option>
									</select>
								</dd>
							</dl>
							<dl>
								<dd>
									<select name="profissional_multiple[]" multiple class="chosen" style="width:200px;" data-placeholder="Profisionais...">
										<option value=""></option>
									</select>
								</dd>
							</dl>
							<dl>
								<dd>
									<select name="paciente_multiple[]" multiple class="chosen" style="width:200px;" data-placeholder="Pacientes...">
										<option value=""></option>
									</select>
								</dd>
							</dl>
							<dl>
								<dd class="form-comp form-comp_pos"><input type="text" name="busca" placeholder="Buscar..." value="" /><a href="javascript:;" class="js-btn-buscar" onclick=""><i class="iconify" data-icon="fluent:search-12-filled"></i></a></dd>
							</dl>
						</div>					
					</div>
				</form>
			</section>
			<section class="grid" style="grid-template-columns:40% auto">
				<div class="box">
					<div class="filter">
						<div class="filter-group">
							<div class="filter-title">
								<h1>Indicadores</h1>
							</div>
						</div>
						<div class="filter-group">
							<div class="filter-title">
								<h1>10 pacientes</h1>
							</div>
						</div>
					</div>
				</div>
				<div class="box">
						<div class="list1">
							<table>
								<tr class="js-item" data-id="01">
									<td class="list1__border" style="color:red"></td>
									<td>
										<h1>NOME TESTE</h1>
										<p>#01</p>
									</td>
									<td>1021</td>
									<td>21 anos</td>
									<td>62982793320</td>
								</tr>
							</table>
						</div>
						<div class="pagination">1 2 3						
						</div>
				</div>
			</section>
		</div>
	</main>

<?php 
include "includes/footer.php";
?>	