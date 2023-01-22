
<?php /*<section class="filtros" style="padding-bottom:0; margin-bottom:-.5rem;">	
	<div class="filtros-paciente">
		
	</div>
</section>*/?>

<ul class="abas">
	<li><a href="pg_configuracao_cadeiras.php?" class="main-nav__resumo-do-paciente<?php echo basename($_SERVER['PHP_SELF'])=="pg_configuracao_cadeiras.php"?" active":"";?>">Cadeiras</a></li>
	<li><a href="pg_colaboradores.php" class="main-nav__resumo-do-paciente<?php echo basename($_SERVER['PHP_SELF'])=="pg_colaboradores.php"?" active":"";?>">Colaboradores</a></li>

	<?php
	if($_infodentalCompleto==1) {
	?>
	<li><a href="pg_configuracao_anamnese_exames.php?" class="main-nav__resumo-do-paciente<?php echo basename($_SERVER['PHP_SELF'])=="pg_configuracao_anamnese_exames.php"?" active":"";?>">Anamnese e Exame</a></li>
	<li><a href="pg_configuracao_procedimentos_servicos.php?" class="main-nav__resumo-do-paciente<?php echo basename($_SERVER['PHP_SELF'])=="pg_configuracao_procedimentos_servicos.php"?" active":"";?>">Procedimentos</a></li>
	<?php /*<li><a href="pg_configuracao_profissionais.php?" class="main-nav__resumo-do-paciente<?php echo basename($_SERVER['PHP_SELF'])=="pg_configuracao_profissionais.php"?" active":"";?>">Cirurgiões Dentistas</a></li>*/?>
	<li><a href="pg_configuracao_fornecedores.php?" class="main-nav__resumo-do-paciente<?php echo basename($_SERVER['PHP_SELF'])=="pg_configuracao_fornecedores.php"?" active":"";?>">Fornecedores</a></li>
	<li><a href="pg_configuracao_indicacoes.php?" class="main-nav__resumo-do-paciente<?php echo basename($_SERVER['PHP_SELF'])=="pg_configuracao_indicacoes.php"?" active":"";?>">Indicações</a></li>
	<li><a href="pg_configuracao_cartoes.php?" class="main-nav__resumo-do-paciente<?php echo basename($_SERVER['PHP_SELF'])=="pg_configuracao_cartoes.php"?" active":"";?>">Cartões</a></li>
	<li><a href="pg_configuracao_bancosecontas.php?" class="main-nav__resumo-do-paciente<?php echo basename($_SERVER['PHP_SELF'])=="pg_configuracao_bancosecontas.php"?" active":"";?>">Bancos/Contas</a></li>
	<li><a href="pg_configuracoes_categorias.php?" class="main-nav__resumo-do-paciente<?php echo basename($_SERVER['PHP_SELF'])=="pg_configuracoes_categorias.php"?" active":"";?>">Categorias Financeiras</a></li>
	<li><a href="pg_configuracao_produtos.php" class="main-nav__resumo-do-paciente<?php echo basename($_SERVER['PHP_SELF'])=="pg_configuracao_produtos.php"?" active":"";?>">Produtos</a></li>
	<li><a href="pg_configuracoes_planos.php" class="main-nav__resumo-do-paciente<?php echo basename($_SERVER['PHP_SELF'])=="pg_configuracoes_planos.php"?" active":"";?>">Planos</a></li>
	<?php
	}
	?>
</ul>
