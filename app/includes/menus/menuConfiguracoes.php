<?php
	$pagesClinica=explode(",","pg_configuracoes_clinica.php,pg_configuracoes_clinica_colaboradores.php,pg_configuracoes_clinica_cadeiras.php");
	$pagesEvolucao=explode(",","pg_configuracoes_evolucao_anamnese.php,pg_configuracoes_evolucao_procedimentos.php,pg_configuracoes_evolucao_servicosdelaboratorio.php,pg_configuracoes_evolucao_examecomplementar.php");
	$pagesFornecedor=explode(",","pg_configuracoes_fornecedores.php,pg_configuracoes_fornecedores_produtos.php");
	$pagesFinanceiro=explode(",","pg_configuracoes_financeiro_bancosecontas.php,pg_configuracoes_financeiro_cartoes.php");
?>

<section class="tab">
	<a href="pg_configuracoes_clinica_colaboradores.php"<?php echo in_array(basename($_SERVER['PHP_SELF']),$pagesClinica)?' class="active"':'';?>>Clínica</a>
</section>