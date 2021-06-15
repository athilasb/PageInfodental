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
					<div class="filter-title">
						<span class="badge">1</span> Procedimentos
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

	<section class="grid">

		<div class="box">

			<div class="kanban">
				
				<div class="kanban-item" style="background:var(--cor1); color:var(--cor1);">
					<h1 class="kanban-item__titulo">Confirmação Quente <span class="tooltip" title="Descrição do item..."><i class="iconify" data-icon="ph:question"></i></span></h1>
					<div class="kanban-card">
						<a href="javascript:;" onclick="$(this).next('.kanban-card-modal').show();" class="kanban-card-dados">
							<p class="kanban-card-dados__data">
								<i class="iconify" data-icon="ph:calendar-blank"></i>
								03/06 (quinta-feira) &bull; 09:00
							</p>
							<h1>Cláudia de Paula Gomes</h1>
							<h2>(62) 98450-2332</h2>
						</a>
						<div class="kanban-card-modal" style="display:none;">
							<div class="kanban-card-modal__inner1">
								<a class="kanban-card-modal__fechar" href="javascript:;" onclick="$(this).parent().parent().hide(); $('.js-reagendar, .js-cancelar').hide(); $('.js-opcoes').show();"><i class="iconify" data-icon="ph-x"></i></a>
								<h1>Ana Paula Toniazzo</h1>
								<h2>(62) 98450-2332</h2>
								<h2>Anestesia</h2>
							</div>
							<div class="kanban-card-modal__inner2 js-opcoes">
								<a href="javascript:;" class="button button__full" style="background-color:var(--verde);">Confirmar agendamento</a>
								<a href="javascript:;" onclick="$(this).parent().hide(); $(this).parent().nextAll('.js-reagendar').show();" class="button button__full" style="background-color:var(--amarelo);">Reagendar</a>
								<a href="javascript:;" onclick="$(this).parent().hide(); $(this).parent().nextAll('.js-cancelar').show();" class="button button__full" style="background-color:var(--vermelho);">Cancelar Agendamento</a>
							</div>
							<div class="kanban-card-modal__inner2 js-reagendar" style="display:none;">
								<form>
									<input type="text" name="" class="datecalendar" placeholder="06/04/2021" />
									<select name=""><option value="">Profissional...</option></select>
									<select name=""><option value="">Cadeira...</option></select>
									<select name=""><option value="">Horas disponíveis...</option></select>
									<button type="submit" class="button button__full" style="background:var(--amarelo);">Reagendar</button>
								</form>
							</div>
							<div class="kanban-card-modal__inner2 js-cancelar" style="display:none;">
								<form>
									<textarea name="" rows="4" placeholder="Descreva o motivo do cancelamento..."></textarea>
									<button type="submit" class="button button__full" style="background:var(--vermelho);">Cancelar</button>
								</form>
							</div>
						</div>
					</div>
				</div>

				<div class="kanban-item" style="background:var(--azul); color:var(--azul);">
					<h1 class="kanban-item__titulo">Confirmação Fria <span class="tooltip" title="Descrição do item..."><i class="iconify" data-icon="ph:question"></i></span></h1>
					
				</div>

				<div class="kanban-item">
					<h1 class="kanban-item__titulo">Pacientes em tratamento sem horário <span class="tooltip" title="Descrição do item..."><i class="iconify" data-icon="ph:question"></i></span></h1>
				</div>

				<div class="kanban-item">
					<h1 class="kanban-item__titulo">Possível paciente <span class="tooltip" title="Descrição do item..."><i class="iconify" data-icon="ph:question"></i></span></h1>
				</div>

				<div class="kanban-item">
					<h1 class="kanban-item__titulo">Inteligência <span class="tooltip" title="Descrição do item..."><i class="iconify" data-icon="ph:question"></i></span></h1>
				</div>


			</div>

			

		</div>

	</section>

</section>




<?php
include "includes/footer.php";
?> 
