<?php
	$id_landingpage=0;
	if(isset($_GET['id_landingpage'])) {
		$id_landingpage=addslashes($_GET['id_landingpage']);
	}
?>
<div class="box-col__inner1 box_inv list5 list5_vert">
	<a href="pg_landingpage_configuracao.php?id_landingpage=<?php echo $id_landingpage;?>" class="list5-item<?php echo basename($_SERVER['PHP_SELF'])=="pg_landingpage_configuracao.php"?" active":"";?>">
		<i class="iconify" data-icon="mdi:bank"></i>
		<p>Configuração</p>
	</a>
	<a href="pg_landingpage_paginainicial.php?id_landingpage=<?php echo $id_landingpage;?>" class="list5-item<?php echo basename($_SERVER['PHP_SELF'])=="pg_landingpage_paginainicial.php"?" active":"";?>">
		<i class="iconify" data-icon="mdi:credit-card-multiple-outline"></i>
		<p>Página Inicial</p>
	</a>	
	<a href="pg_landingpage_informativo.php?id_landingpage=<?php echo $id_landingpage;?>" class="list5-item<?php echo basename($_SERVER['PHP_SELF'])=="pg_landingpage_informativo.php"?" active":"";?>">
		<i class="iconify" data-icon="mdi:credit-card-multiple-outline"></i>
		<p>Informativo</p>
	</a>	
	<a href="pg_landingpage_aclinica.php?id_landingpage=<?php echo $id_landingpage;?>" class="list5-item<?php echo basename($_SERVER['PHP_SELF'])=="pg_landingpage_aclinica.php"?" active":"";?>">
		<i class="iconify" data-icon="mdi:credit-card-multiple-outline"></i>
		<p>A Clínica</p>
	</a>	
	<a href="pg_landingpage_conversao.php?id_landingpage=<?php echo $id_landingpage;?>" class="list5-item<?php echo basename($_SERVER['PHP_SELF'])=="pg_landingpage_conversao.php"?" active":"";?>">
		<i class="iconify" data-icon="mdi:credit-card-multiple-outline"></i>
		<p>Conversão</p>
	</a>	
	<a href="pg_landingpage_antesedepois.php?id_landingpage=<?php echo $id_landingpage;?>" class="list5-item<?php echo basename($_SERVER['PHP_SELF'])=="pg_configuracoes_financeiro_cartoes.php"?" active":"";?>">
		<i class="iconify" data-icon="mdi:credit-card-multiple-outline"></i>
		<p>Antes e Depois</p>
	</a>
	<a href="pg_landingpage_depoimentos.php?id_landingpage=<?php echo $id_landingpage;?>" class="list5-item<?php echo basename($_SERVER['PHP_SELF'])=="pg_landingpage_depoimentos.php"?" active":"";?>">
		<i class="iconify" data-icon="mdi:credit-card-multiple-outline"></i>
		<p>Depoimentos</p>
	</a>	
	<a href="pg_landingpage_sobrenos.php?id_landingpage=<?php echo $id_landingpage;?>" class="list5-item<?php echo basename($_SERVER['PHP_SELF'])=="pg_landingpage_sobrenos.php"?" active":"";?>">
		<i class="iconify" data-icon="mdi:credit-card-multiple-outline"></i>
		<p>Sobre Nós</p>
	</a>						
</div>