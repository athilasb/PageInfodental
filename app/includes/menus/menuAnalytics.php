<?php
	$pagesInteligenciaAnalytics=explode(",","pg_inteligencia_analytics.php");
	$pagesSatistacaoanalytics=explode(",","pg_satistacao_analytics.php");
?>

<section class="tab">
	<a href="pg_inteligencia_analytics.php"<?php echo in_array(basename($_SERVER['PHP_SELF']),$pagesInteligenciaAnalytics)?' class="active"':'';?>>Agendamentos</a>	
	<a href="pg_satistacao_analytics.php"<?php echo in_array(basename($_SERVER['PHP_SELF']),$pagesSatistacaoanalytics)?' class="active"':'';?>>Pesquisa de satisfação</a>					
</section>