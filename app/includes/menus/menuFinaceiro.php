<?php
	$pagesFinanceiro=explode(",","pg_clinica_financeiro.php");
?>

<section class="tab">
	<a href="pg_clinica_financeiro.php"<?php echo in_array(basename($_SERVER['PHP_SELF']),$pagesFinanceiro)?' class="active"':'';?>>Contas a Receber</a>			
</section>