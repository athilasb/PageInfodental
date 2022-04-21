<?php
	$pagesInteligencia=explode(",","pg_inteligencia.php");
	$pagesInteligenciaFunil=explode(",","pg_inteligencia_funildevendas.php");
	$pagesInteligenciaFinanceiro=explode(",","pg_inteligencia_financeiro.php");
?>

<section class="tab">
	<a href="pg_inteligencia.php"<?php echo in_array(basename($_SERVER['PHP_SELF']),$pagesInteligencia)?' class="active"':'';?>>Gestão do Tempo</a>
	<a href="pg_inteligencia_funildevendas.php"<?php echo in_array(basename($_SERVER['PHP_SELF']),$pagesInteligenciaFunil)?' class="active"':'';?>>Funil de Vendas</a>				
	<a href="pg_inteligencia_financeiro.php"<?php echo in_array(basename($_SERVER['PHP_SELF']),$pagesInteligenciaFinanceiro)?' class="active"':'';?>>Financeiro</a>					
</section>