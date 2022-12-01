<?php
	$pagesLandinPage=explode(",","pg_landingpage_configuracao.php");

?>

<section class="tab">
	<a href="pg_landingpage_configuracao.php"<?php echo in_array(basename($_SERVER['PHP_SELF']),$pagesLandinPage)?' class="active"':'';?>>Configuração</a>
</section>