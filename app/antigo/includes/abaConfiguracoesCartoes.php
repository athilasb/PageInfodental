<?php
	$_abaConfiguracoesCartao=array("pg_parametros_bandeirasDeCartao.php"=>"Bandeiras de Cartão",
									"pg_parametros_operadorasDeCartao.php"=>"Operadoras de Cartão");
?>
<ul class="abas">
	<?php
	foreach($_abaConfiguracoesCartao as $m=>$t) {
		echo '<li><a href="'.$m.'" class="'.(basename($_SERVER['PHP_SELF'])==$m?" active":"").'">'.$t.'</a></li>';
	}
	?>
</ul>