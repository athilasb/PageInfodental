<?php
	include "includes/header.php";
	include "includes/nav.php";


?>

<section class="content">
	<?php 
		require_once("includes/nav2.php");
	?>

	<section class="grid grid_3">
		<div class="box">
			<h1 class="filtros__titulo">Olá <b><?php echo utf8_encode($usr->nome);?></b>!<br /><br />Seja bem vindo ao Infodental</h1>
		</div>					
	</section>
			
</section>
	
<?php
include "includes/footer.php";
?>