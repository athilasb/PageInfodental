<?php
	$pagesClinica=explode(",","pg_configuracoes_clinica.php,pg_configuracoes_clinica_colaboradores.php,pg_configuracoes_clinica_cadeiras.php,pg_configuracoes_pagamentos.php");
	$pagesEvolucao=explode(",","pg_configuracoes_evolucao_anamnese.php,pg_configuracoes_evolucao_procedimentos.php,pg_configuracoes_evolucao_servicosdelaboratorio.php,pg_configuracoes_evolucao_examecomplementar.php,pg_configuracoes_evolucao_documentos.php");
	$pagesFornecedor=explode(",","pg_configuracoes_fornecedores.php,pg_configuracoes_fornecedores_produtos.php");
	$pagesFinanceiro=explode(",","pg_configuracoes_financeiro_bancosecontas.php,pg_configuracoes_financeiro_cartoes.php,pg_configuracoes_financeiro_politicadepagamento.php");
	$pagesAssinatura=explode(",","pg_configuracoes_assinatura.php");
?>

<section class="tab">
	<a href="pg_configuracoes_clinica_colaboradores.php"<?php echo in_array(basename($_SERVER['PHP_SELF']),$pagesClinica)?' class="active"':'';?>>Clínica</a>
	<a href="pg_configuracoes_evolucao_anamnese.php"<?php echo in_array(basename($_SERVER['PHP_SELF']),$pagesEvolucao)?' class="active"':'';?>>Evolução</a>
	<a href="pg_configuracoes_fornecedores.php"<?php echo in_array(basename($_SERVER['PHP_SELF']),$pagesFornecedor)?' class="active"':'';?>>Fornecedores</a>
	<a href="pg_configuracoes_financeiro_bancosecontas.php"<?php echo in_array(basename($_SERVER['PHP_SELF']),$pagesFinanceiro)?' class="active"':'';?>>Financeiro</a>
	<a href="pg_configuracoes_assinatura.php"<?php echo in_array(basename($_SERVER['PHP_SELF']),$pagesAssinatura)?' class="active"':'';?>>Assinatura</a>
</section>