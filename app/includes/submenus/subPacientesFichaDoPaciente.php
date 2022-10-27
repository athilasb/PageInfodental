<div class="box-col__inner1 box_inv list5 list5_vert">
	<a href="pg_pacientes_prontuario.php?id_paciente=<?php echo $paciente->id;?>" class="list5-item<?php echo basename($_SERVER['PHP_SELF'])=="pg_pacientes_prontuario.php"?" active":"";?>">
		<i class="iconify" data-icon="fluent:document-checkmark-24-regular"></i>
		<p>Prontuário</p>
	</a>
	<a href="pg_pacientes_planosdetratamento.php?id_paciente=<?php echo $paciente->id;?>" class="list5-item<?php echo basename($_SERVER['PHP_SELF'])=="pg_pacientes_planosdetratamento.php"?" active":"";?>">
		<i class="iconify" data-icon="fa-solid:tooth"></i>
		<p>Planos de Tratamento</p>
	</a>
	<a href="pg_pacientes_financeiro.php?id_paciente=<?php echo $paciente->id;?>" class="list5-item<?php echo basename($_SERVER['PHP_SELF'])=="pg_pacientes_financeiro.php"?" active":"";?>">
		<i class="iconify" data-icon="ic:baseline-attach-money"></i>
		<p>Financeiro</p>
	</a>
</div>