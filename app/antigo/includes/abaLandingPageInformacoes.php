<?php
	$_abaConfiguracoes=array("pg_landingpage_informacoes.php"=>"Informações",
								"pg_landingpage_destaques.php"=>"Destaques")
?>
<ul class="abas">
	<?php
	foreach($_abaConfiguracoes as $m=>$t) {
		echo '<li><a href="'.$m.'" class="'.(basename($_SERVER['PHP_SELF'])==$m?" active":"").'">'.$t.'</a></li>';
	}
	?>
</ul>