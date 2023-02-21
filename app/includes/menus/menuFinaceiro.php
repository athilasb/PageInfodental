<?php
$pagesFinanceiro = explode(",", "pg_clinica_financeiro.php");
?>

<section class="tab">
	<a href="pg_clinica_financeiro.php" <?= $_SERVER['PHP_SELF'] == '/pg_clinica_financeiro.php' ? ' class="active"' : ''; ?>>Resumo</a>
	<a href="pg_clinica_financeiro_contasapagar.php" <?= $_SERVER['PHP_SELF'] == '/pg_clinica_financeiro_contasapagar.php' ? ' class="active"' : ''; ?>>Contas a Pagar</a>
	<a href="pg_clinica_financeiro_contasareceber.php" <?= $_SERVER['PHP_SELF'] == '/pg_clinica_financeiro_contasareceber.php' ? ' class="active"' : ''; ?>>Contas a Receber</a>
</section>