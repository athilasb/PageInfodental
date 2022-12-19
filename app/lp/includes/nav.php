	<?php 
		if(is_object($landingpage)) {
	?>
	<header class="header">
		<section class="header__content">
			
			<section class="header-logo">
				<img src="img/logo.png" alt="" width="281" height="40" />
			</section>

			<nav class="header-nav">
				<a href="/<?php echo $landingpage->code;?>#"><?php echo utf8_encode($landingpage->titulo);?></a>
				<a href="/<?php echo $landingpage->code;?>#informacoes">Informações</a>
				<a href="/<?php echo $landingpage->code;?>#antes-e-depois">Antes e Depois</a>
				<a href="/<?php echo $landingpage->code;?>#depoimentos">Depoimentos</a>
				<a href="/<?php echo $landingpage->code;?>#sobre-nos">Sobre Nós</a>
			</nav>

			<section class="header-botoes">
				<a href="/<?php echo $landingpage->code;?>#captacao" class="button">SAIBA O PREÇO</a>
				<a href="javascript:;" class="button button__sec">JÁ SOU CLIENTE</a>
			</section>

		</section>
	</header>
	<?php 
		}
	?>