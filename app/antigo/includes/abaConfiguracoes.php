<?php
	$_abaConfiguracoes=array("pg_parametros_anamnese.php"=>"Anamnese",
								"pg_parametros_procedimentos.php"=>"Procedimentos",
								"pg_parametros_servicosdelaboratorio.php"=>"Serviços de Laboratório",
								"pg_parametros_examedeimagem.php"=>"Exames de Imagem",
								"pg_profissionais.php"=>"Cirurgiões Dentistas",
								"pg_cadeiras.php"=>"Cadeiras",
								"pg_parametros_fornecedores.php"=>"Fornecedores e Parceiros",
								"pg_parametros_indicacoes.php"=>"Indicações",
								"pg_parametros_operadorasDeCartao.php"=>"Cartões",
								"pg_parametros_bancosecontas.php"=>"Bancos e Contas",
								"usuarios.php"=>"Usuários");
?>
<ul class="abas">
	<?php
	foreach($_abaConfiguracoes as $m=>$t) {
		echo '<li><a href="'.$m.'" class="'.(basename($_SERVER['PHP_SELF'])==$m?" active":"").'">'.$t.'</a></li>';
	}
	?>
</ul>