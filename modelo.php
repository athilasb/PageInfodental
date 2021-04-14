<?php
include "includes/header.php";
include "includes/nav.php";
if($usr->tipo!="admin" and !in_array("parametros",$_usuariosPermissoes)) {
	$jsc->jAlert("Você não tem permissão para acessar esta área!","erro","document.location.href='dashboard.php'");
	die();
}
$values=$adm->get($_GET);
?>

<section class="content">

	<ul class="abas">
		<li><a href="javascript:;" class="active">Análise</a></li>
		<li><a href="javascript:;">Procedimentos</a></li>
		<li><a href="javascript:;">Cadeiras</a></li>
		<li><a href="javascript:;">Cirurgiões Dentistas</a></li>
		<li><a href="javascript:;">Fornecedores e Parceiros</a></li>
		<li><a href="javascript:;">Indicações</a></li>
		<li><a href="javascript:;">Usuários</a></li>
	</ul>

	<section class="grid">

		<div class="box">

			<div class="filter">

				<div class="filter-group">
					<div class="filter-button">
						<a href="javascript:;" class="verde"><i class="iconify" data-icon="bx-bx-plus"></i><span>novo procedimento</span></a>
					</div>
				</div>

				<div class="filter-group filter-group_right">
					<div class="filter-data">
						<h1>Valor Total</h1>
						<h2>R$ 3.540,00</h2>
					</div>					
				</div>

				<div class="filter-group">
					<div class="filter-data">
						<h1>Valor Médio</h1>
						<h2>R$ 340,00</h2>
					</div>					
				</div>

				<div class="filter-group">
					<div class="filter-data">
						<h1>Previsto</h1>
						<h2>R$ 500,00</h2>
					</div>					
				</div>

				<div class="filter-group filter-group_right">
					<form method="post" class="filter-form">
						<dl>
							<dd><input type="text" name="campo" placeholder="" style="width:120px;"></dd>
						</dl>
						<button type="submit"><i class="iconify" data-icon="bx-bx-search"></i></button>
					</form>
				</div>

			</div>

			<div class="reg">

				<a href="javascript:;" class="reg-group">
					<div class="reg-color" style="background-color:palegreen"></div>
					<div class="reg-data" style="flex:0 1 200px">
						<h1>EXODONTIA COMPLEXA</h1>
						<p>11 - PARTICULAR SD</p>
					</div>
					<div class="reg-data">
						<p>1.180,00</p>
					</div>
					<div class="reg-bar" style="flex:0 1 120px;">
						<p>Evolução</p>
						<div class="reg-bar__container"><span style="width:5%">&nbsp;</span></div>
					</div>
					<div class="reg-user">
						<span style="background:#44FF00">KM</span>
					</div>
				</a>

				<a href="javascript:;" class="reg-group">
					<div class="reg-color" style="background-color:red"></div>
					<div class="reg-data" style="flex:0 1 200px">
						<h1>EXODONTIA COMPLEXA</h1>
						<p>11 - PARTICULAR SD</p>
					</div>
					<div class="reg-data">
						<p>1.180,00</p>
					</div>
					<div class="reg-bar" style="flex:0 1 120px;">
						<p>Evolução</p>
						<div class="reg-bar__container"><span style="width:20%">&nbsp;</span></div>
					</div>
					<div class="reg-user">
						<span style="background:#44FF00">KM</span>
					</div>
				</a>

				<a href="javascript:;" class="reg-group">
					<div class="reg-color" style="background-color:blueviolet"></div>
					<div class="reg-data" style="flex:0 1 200px">
						<h1>EXODONTIA COMPLEXA</h1>
						<p>11 - PARTICULAR SD</p>
					</div>
					<div class="reg-data">
						<p>1.180,00</p>
					</div>
					<div class="reg-bar" style="flex:0 1 120px;">
						<p>Evolução</p>
						<div class="reg-bar__container"><span style="width:40%">&nbsp;</span></div>
					</div>
					<div class="reg-user">
						<span style="background:#44FF00">KM</span>
					</div>
				</a>

			</div>


		</div>

	</section>

	<section class="grid">

		<div class="box">

			<div class="filter">

				<div class="filter-group">
					<div class="filter-button">
						<a href="javascript:;"><i class="iconify" data-icon="bx-bx-left-arrow-alt"></i></a>
					</div>
				</div>

				<div class="filter-group filter-group_right">
					<div class="filter-button">
						<a href="javascript:;"><i class="iconify" data-icon="bx-bx-trash"></i></a>
						<a href="javascript:;"><i class="iconify" data-icon="bx-bx-printer"></i></a>
						<a href="javascript:;" class="azul"><i class="iconify" data-icon="bx-bx-check"></i><span>salvar</span></a>
					</div>
				</div>

			</div>

		</div>

	</section>

	<section class="grid">

		<div class="box">

			<div class="filter">

				<div class="filter-group">
					<div class="filter-button">
						<a href="javascript:;"><i class="iconify" data-icon="bx-bx-left-arrow-alt"></i></a>
					</div>
				</div>

				<div class="filter-group">
					<div class="filter-input">
						<input type="text" name="" placeholder="Nome do paciente" />
					</div>
				</div>

				<div class="filter-group filter-group_right">
					<div class="filter-links">
						<a href="" class="active">Em aberto</a>
						<a href="">Aprovado</a>
						<a href="">Reprovado</a>
					</div>
				</div>

				<div class="filter-group">
					<div class="filter-data">
						<h1>Valor Total</h1>
						<h2>R$ 3.540,00</h2>
					</div>					
				</div>				

				<div class="filter-group">
					<div class="filter-button">
						<a href="javascript:;"><i class="iconify" data-icon="bx-bx-trash"></i></a>
						<a href="javascript:;"><i class="iconify" data-icon="bx-bx-printer"></i></a>
						<a href="javascript:;" class="azul"><i class="iconify" data-icon="bx-bx-check"></i><span>salvar</span></a>
					</div>
				</div>

			</div>

		</div>

	</section>

	<section class="grid">

		<div class="box">

			<div class="filter">

				<div class="filter-group">
					<div class="filter-button">
						<a href="javascript:;" class="verde"><i class="iconify" data-icon="bx-bx-plus"></i><span>novo procedimento</span></a>
					</div>
				</div>

				<div class="filter-group">
					<div class="filter-input">
						<input type="text" name="" placeholder="Nome do paciente" />
					</div>
				</div>

				<div class="filter-group">
					<form method="post" class="filter-form">
						<dl>
							<dt>Buscar</dt>
							<dd><input type="text" name="campo" style="width:120px;"></dd>
						</dl>
						<dl>
							<dt>Seleção</dt>
							<dd><select name="" style="width:120px;"><option value=""></option><option value="">opção 1</option></select></dd>
						</dl>
						<button type="submit"><i class="iconify" data-icon="bx-bx-search"></i></button>
					</form>
				</div>

				<div class="filter-group">
					<div class="filter-data">
						<h1>Valor Total</h1>
						<h2>R$ 3.540,00</h2>
					</div>					
				</div>

				<div class="filter-group">
					<div class="filter-data">
						<h1>Valor Médio</h1>
						<h2>R$ 340,00</h2>
					</div>					
				</div>

				<div class="filter-group">
					<div class="filter-links">
						<a href="" class="active">Ativado</a>
						<a href="">Desativado</a>
						<a href="">Cancelado</a>
					</div>
				</div>

				<div class="filter-group filter-group_right">
					<div class="filter-button">
						<a href="javascript:;"><i class="iconify" data-icon="bx-bx-trash"></i></a>
						<a href="javascript:;"><i class="iconify" data-icon="bx-bx-printer"></i></a>
						<a href="javascript:;" class="azul"><i class="iconify" data-icon="bx-bx-check"></i><span>salvar</span></a>
					</div>
				</div>

			</div>

		</div>

	</section>

</section>




<?php
include "includes/footer.php";
?> 
