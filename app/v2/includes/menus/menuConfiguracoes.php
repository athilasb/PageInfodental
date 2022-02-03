<?php
	$pagesClinica=explode(",","pg_configuracoes_clinica.php,pg_configuracoes_clinica_colaboradores.php,pg_configuracoes_clinica_cadeiras.php");
	$pagesEvolucao=explode(",","pg_configuracoes_evolucao_anamnese.php");
?>

<section class="tab">
	<a href="pg_configuracoes_clinica.php"<?php echo in_array(basename($_SERVER['PHP_SELF']),$pagesClinica)?' class="active"':'';?>>Clínica</a>
	<a href="pg_configuracoes_evolucao_anamnese.php"<?php echo in_array(basename($_SERVER['PHP_SELF']),$pagesEvolucao)?' class="active"':'';?>>Evolução</a>	
	<a href="">Fornecedor</a>					
	<a href="">Financeiro</a>					
</section>