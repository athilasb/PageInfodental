<?php
	$pagesInteligencia=explode(",","pg_inteligencia.php");
	$pagesInteligenciaPacientes=explode(",","pg_inteligencia_pacientes.php");
	$pagesInteligenciaRelacionamento=explode(",","pg_inteligencia_relacionamento.php");
	$pagesInteligenciaAnalytics=explode(",","pg_inteligencia_analytics.php");
	$pagesInteligenciaPacientesNovos=explode(",","pg_inteligencia_pacientesnovos.php");
?>

<section class="tab">
	<a href="pg_inteligencia.php"<?php echo in_array(basename($_SERVER['PHP_SELF']),$pagesInteligencia)?' class="active"':'';?>>Gestão do Tempo</a>
	<a href="pg_inteligencia_pacientes.php"<?php echo in_array(basename($_SERVER['PHP_SELF']),$pagesInteligenciaPacientes)?' class="active"':'';?>>Gestão de Pacientes</a>
	<?php /*<a href="pg_inteligencia_relacionamento.php"<?php echo in_array(basename($_SERVER['PHP_SELF']),$pagesInteligenciaRelacionamento)?' class="active"':'';?>>Relacionamento</a>*/?>
	<a href="pg_inteligencia_analytics.php"<?php echo in_array(basename($_SERVER['PHP_SELF']),$pagesInteligenciaAnalytics)?' class="active"':'';?>>Analytics</a>
	<a href="pg_inteligencia_pacientesnovos.php"<?php echo in_array(basename($_SERVER['PHP_SELF']),$pagesInteligenciaPacientesNovos)?' class="active"':'';?>>Pacientes Novos</a>				
	<?php /*<a href="pg_inteligencia_financeiro.php"<?php echo in_array(basename($_SERVER['PHP_SELF']),$pagesInteligenciaFinanceiro)?' class="active"':'';?>>Financeiro</a>*/?>					
</section>