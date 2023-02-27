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
				<?php /*<section class="tab">
					<a href="" class="active">Aba 1</a>
					<a href="">Aba 2</a>
					<a href="">Aba 3</a>
				</section>*/?>
			</div>

			

		</div>
	</header>

	<script>
		$(function(){
			$('.js-listaAniversarios').click(function(){

				if($(this).attr('data-valor')==0){
					$('.js-divAniversarios').show();
					$(this).attr('data-valor', 1);
				}
				else{
					$('.js-divAniversarios').hide();
					$(this).attr('data-valor', 0);
				}
			});
		})
	</script>
	<main class="main">
		<div class="main__content content">
			<div class="box box_inv">
				<span class="iconify" data-icon="cil:birthday-cake" data-width="25" data-height="25"></span>
				<?php 
					$sql->consult($_p."pacientes","id,nome,data_nascimento,telefone1","WHERE month(data_nascimento)='".date('m')."' and day(data_nascimento)='".date('d')."' and telefone1<>'' and lixo=0 order by nome asc");
					$total = $sql->rows;
					echo $total. " Aniversariantes do dia<br><br>";

					$registros=array();
					$telefones=array();
					while($x=mysqli_fetch_object($sql->mysqry)) {
						$registros[]=$x;
						$telefones[]=$x->telefone1;
					}

					if(count($telefones)>0) {
						$sql->consult($_p."whatsapp_mensagens","id","WHERE enviado=1 and id_tipo=10 and numero IN (".implode(",", $telefones).") and month(data_enviado)='".date('m')."' and day(data_enviado)='".date('d')."'");
				?>
				<h1><?php echo $sql->rows;?> Mensagens Enviadas</h1>
				<?php 
						if($total>0) echo '<a href="javascript:;" class="js-listaAniversarios" data-valor="0">Ver Lista</a>';
					}
					
				?>
			</div>

			<div class="box box_inv js-divAniversarios" style="display: none;">
				<?php 
					foreach($registros as $x) {
						echo utf8_encode($x->nome)."<br>";
					}
				?>
			</div>
		</div>
	</main>

<?php 
include "includes/footer.php";
?>	