<?php
	$pagesPacientes=explode(",","pg_pacientes.php");

	$pagesPacientesKanban=explode(",","pg_pacientes_kanban.php");
?>

<section class="tab">
	<a href="pg_pacientes.php"<?php echo in_array(basename($_SERVER['PHP_SELF']),$pagesPacientes)?' class="active"':'';?>>Lista</a>
	<a href="pg_pacientes_kanban.php"<?php echo in_array(basename($_SERVER['PHP_SELF']),$pagesPacientesKanban)?' class="active"':'';?>>Kanban</a>					
</section>