<?php
	$_abaConfiguracoes=array("pg_landingpage_captacao.php"=>"Captação",
								"pg_landingpage_captacao_abandono.php"=>"Abandono")
?>
<ul class="abas">
	<?php
	foreach($_abaConfiguracoes as $m=>$t) {
		echo '<li><a href="'.$m.'" class="'.(basename($_SERVER['PHP_SELF'])==$m?" active":"").'">'.$t.'</a></li>';
	}
	?>
</ul>