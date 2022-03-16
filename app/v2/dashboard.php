<?php
include "includes/header.php";
include "includes/nav.php";
?>

	<header class="header">
		<div class="header__content content">
	
			<div class="header__inner1">
				<section class="header-title">
					<h1>Bem vindo <?php echo utf8_encode($usr->nome);?></h1>
				</section>
				<select name="id_paciente" class="select2 obg-0">
									<option value="">...</option>
								</select>
				<?php /*<section class="tab">
					<a href="" class="active">Aba 1</a>
					<a href="">Aba 2</a>
					<a href="">Aba 3</a>
				</section>*/?>

			</div>

		</div>
	</header>

<?php 
include "includes/footer.php";
?>	