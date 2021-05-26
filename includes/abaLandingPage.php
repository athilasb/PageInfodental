<?php
	if(empty($landingpage)) {
		$jsc->jAlert("Landing Page não encontrada!","erro","document.location.href='pg_landingpages.php'");
		die();
	}

	if(isset($_GET['deletaLandingPage']) and is_numeric($_GET['deletaLandingPage']) and $paciente->id==$_GET['deletaLandingPage']) {
		$vsql="lixo=1";
		$vwhere="where id=$landingpage->id";
		$sql->update($_p."landingpage_temas",$vsql,$vwhere);
		
		$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='update',vsql='".addslashes($vsql)."',vwhere='".addslashes($vwhere)."',tabela='".$_p."landingpage_temas',id_reg='".$landingpage->id."'");

		$jsc->jAlert("Landing Page excluída com sucesso!","sucesso","document.location.href='pg_landingpages.php'");
		die();
	}
?>
<section class="filtros" style="padding-bottom:0; margin-bottom:-.5rem;">	
	<div class="filtros-paciente">
		<div class="filtros-paciente__inner1">
			<h1><?php echo utf8_encode($landingpage->titulo);?></h1>
			<p>studiodental.dental/<?php echo $landingpage->code;?></p>
		</div>		
	</div>
</section>

<ul class="abas">
	<li><a href="pg_landingpages_configuracao.php?<?php echo "id_landingpage=$landingpage->id";?>" class="main-nav__resumo-do-paciente<?php echo basename($_SERVER['PHP_SELF'])=="pg_landingpages_configuracao.php"?" active":"";?>">Configuração</a></li>
	<li><a href="pg_landingpages_paginainicial.php?<?php echo "id_landingpage=$landingpage->id";?>" class="main-nav__dados-pessoais<?php echo basename($_SERVER['PHP_SELF'])=="pg_landingpages_paginainicial.php"?" active":"";?>">Página Inicial</a></li>
	<li><a href="pg_landingpages_informativo.php?<?php echo "id_landingpage=$landingpage->id";?>" class="main-nav__evolucao-e-laboratorio<?php echo basename($_SERVER['PHP_SELF'])=="pg_landingpages_informativo.php"?" active":"";?>">Informativo</a></li>
	<li><a href="pg_landingpages_aclinica.php?<?php echo "id_landingpage=$landingpage->id";?>" class="main-nav__programa-de-fidelidade<?php echo basename($_SERVER['PHP_SELF'])=="pg_landingpages_aclinica.php"?" active":"";?>">A Clínica</a></li>
	<li><a href="pg_landingpages_conversao.php?<?php echo "id_landingpage=$landingpage->id";?>" class="main-nav__tratamento<?php echo basename($_SERVER['PHP_SELF'])=="pg_landingpages_conversao.php"?" active":"";?>">Conversão</a></li>
	<li><a href="pg_landingpages_antesedepois.php?<?php echo "id_landingpage=$landingpage->id";?>" class="main-nav__tratamento<?php echo basename($_SERVER['PHP_SELF'])=="pg_landingpages_antesedepois.php"?" active":"";?>">Antes e Depois</a></li>
	<li><a href="pg_landingpages_depoimentos.php?<?php echo "id_landingpage=$landingpage->id";?>" class="main-nav__financeiro<?php echo basename($_SERVER['PHP_SELF'])=="pg_landingpages_depoimentos.php"?" active":"";?>">Depoimentos</a></li>
	<li><a href="pg_landingpages_sobrenos.php?<?php echo "id_landingpage=$landingpage->id";?>" class="main-nav__programa-de-fidelidade<?php echo basename($_SERVER['PHP_SELF'])=="pg_landingpages_sobrenos.php"?" active":"";?>">Sobre Nós</a></li>
</ul>
