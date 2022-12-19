<?php
include "includes/header.php";
include "includes/nav.php";

if(empty($landingpage)){
   $jsc->jAlert("Landing Page não encontrada!","erro","document.location.href='http://163.172.187.183:5000/pg_landingpage.php'");
   die();
}
?>
	
	<?php
		$sql->consult($_p."landingpage_banner","*","WHERE id_tema='".$landingpage->id."' and lixo=0"); 
		if($sql->rows) {
			$banner=mysqli_fetch_object($sql->mysqry);

			$image="";
			if(!empty($banner->foto)) {
				$image=$_cloudinaryURL.$banner->foto;
			}

			$bannerPalavras="";
			$bannerPalavrasAux=explode(",",utf8_encode($banner->palavras));
			if(is_array($bannerPalavrasAux) and count($bannerPalavrasAux)>0) {
				foreach($bannerPalavrasAux as $v) {
					$bannerPalavras.="'".trim($v)."',";
				}
				$bannerPalavras=substr($bannerPalavras,0,strlen($bannerPalavras)-1);
			}
	?>
	<script>
		$(function() {
			  var typed5 = new Typed('.typed', {
			    strings: [<?php echo $bannerPalavras;?>],
			    typeSpeed: 50,
			    backSpeed: 30,
			    backDelay: 1500,
			    cursorChar: '',
			    smartBackspace: true,
			    loop: true
			  });
		});
	</script>
	<section class="banner" style="background-image:url(<?php echo $image;?>)">
		<section class="banner__content">
			<h1 class="banner__titulo"><?php echo utf8_encode($banner->titulo);?> <span class="typed"></span></h1>
			<h2 class="banner__descricao"><?php echo utf8_encode($banner->descricao);?></h2>
			<?php 
				if($banner->video) {
					echo utf8_encode($banner->video);
			?>
			<?php /* <iframe width="560" height="315" src="https://www.youtube.com/embed/fJ432l2wt8U" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe> */ ?>
			<?php 
				}
			?>
		</section>
	</section>
	<?php 
		}
	?>

	<?php
		$sql->consult($_p."landingpage_informacoes_apresentacao","*","WHERE id_tema='".$landingpage->id."' and lixo=0"); 
		if($sql->rows) {
			$informacoes=mysqli_fetch_object($sql->mysqry);
	?>
	<section id="informacoes" class="informacoes">
		<section class="informacoes__content content fck">
			<h2><?php echo utf8_encode($informacoes->titulo);?></h2>
			<?php echo utf8_encode($informacoes->texto);?>
		</section>
	</section>
	<?php 
		}
	?>

	<section class="destaques">
		<section class="destaques__content content">

			<section class="destaques__slick">

				<section class="destaques__item">
					<img src="img/ilustra1.png" alt="" width="268" height="193" class="destaques__foto" />
					<p class="destaques__descricao">1º passo: exames clínicos e radiográficos + moldagens para diagnóstico.</p>
				</section>
				<section class="destaques__item">
					<img src="img/ilustra1.png" alt="" width="268" height="193" class="destaques__foto" />
					<p class="destaques__descricao">1º passo: exames clínicos e radiográficos + moldagens para diagnóstico.</p>
				</section>
				<section class="destaques__item">
					<img src="img/ilustra1.png" alt="" width="268" height="193" class="destaques__foto" />
					<p class="destaques__descricao">1º passo: exames clínicos e radiográficos + moldagens para diagnóstico.</p>
				</section>
				<section class="destaques__item">
					<img src="img/ilustra1.png" alt="" width="268" height="193" class="destaques__foto" />
					<p class="destaques__descricao">1º passo: exames clínicos e radiográficos + moldagens para diagnóstico.</p>
				</section>
				<section class="destaques__item">
					<img src="img/ilustra1.png" alt="" width="268" height="193" class="destaques__foto" />
					<p class="destaques__descricao">1º passo: exames clínicos e radiográficos + moldagens para diagnóstico.</p>
				</section>

			</section>

		</section>
	</section>

	<section id="captacao" class="captacao" style="background-image:url(img/banner2.jpg)">
		<section class="captacao__content">
			<h1 class="captacao__titulo">Saiba o preço</h1>
			<script>
				$(function(){
					$('input[name=preferencia]').click(function(){
						if($(this).val()=="whatsapp") {
							$('input[name=telefone]').parent().parent().show();
							$('input[name=email]').parent().parent().hide();
							$('input[name=telefone]').addClass('obg');
							$('input[name=email]').removeClass('obg');
						} else {
							$('input[name=email]').parent().parent().show();
							$('input[name=telefone]').parent().parent().hide();
							$('input[name=email]').addClass('obg');
							$('input[name=telefone]').removeClass('obg');
						}
					});
					$('input[name=preferencia]:checked').trigger('click');
				});
			</script>
			
			<form method="post" class="formulario-validacao form captacao-form">
				<input type="hidden" name="acao" value="preco" />
				<h2 class="captacao__texto">Preencha seus dados e receba um orçamento sem compromisso</h2>
				<dl>
					<dd><input type="text" name="nome" placeholder="Seu nome" class="obg" /></dd>
				</dl>
				<dl>
					<dt>Como prefere ser atendido?</dt>
					<dd>
						<label><input type="radio" name="preferencia" value="whatsapp" checked>WhatsApp</label>
						<label><input type="radio" name="preferencia" value="email">E-mail</label>
					</dd>
				</dl>
				<dl>
					<dd><input type="tel" name="telefone" class="celular obg" placeholder="Seu número WhatsApp" style="max-width:200px;" /></dd>
				</dl>
				<dl>
					<dd><input type="email" name="email" class="email" placeholder="Seu E-mail" style="max-width:200px;" /></dd>
				</dl>
				<dl>
					<dd><button type="submit" class="button button__lg">SOLICITAR ORÇAMENTO</button></dd>
				</dl>
			</form>

		</section>
	</section>

	<?php
		$sql->consult($_p."landingpage_antesedepois","*","WHERE id_tema='".$landingpage->id."' and lixo=0"); 
		if($sql->rows) {
			$antesedepois=mysqli_fetch_object($sql->mysqry);

			$fotosAntesDepois=array();
			if(!empty($antesedepois->foto_antes1) and !empty($antesedepois->foto_depois1)) {
				$fotosAntesDepois[]=(object) array('foto_antes'=>$_cloudinaryURL.$antesedepois->foto_antes1,'foto_depois'=> $_cloudinaryURL.$antesedepois->foto_depois1);
			} else if(!empty($depoimentos->foto_antes2) and !empty($antesedepois->foto_depois2)) {
				$fotosAntesDepois[]=(object) array('foto_antes'=>$_cloudinaryURL.$antesedepois->foto_antes2,'foto_depois'=>$_cloudinaryURL.$antesedepois->foto_depois2);
			} 
	?>
	<section id="antes-e-depois" class="galeria">
		<section class="galeria__content content">

			<h1 class="galeria__titulo">Antes e Depois</h1>

			<section class="galeria__slick">

				<?php 
					foreach($fotosAntesDepois as $x) {
				?>
				<section class="galeria__item">
					<img src="<?php echo $x->foto_antes;?>" alt="" width="285" height="264" class="galeria__foto" />
					<img src="<?php echo $x->foto_depois;?>" alt="" width="285" height="264" class="galeria__foto" />
				</section>
				<?php 
					}
				?>

			</section>
			
		</section>
	</section>
	<?php 
		}
	?>

	<?php
		$sql->consult($_p."landingpage_depoimentos","*","WHERE id_tema='".$landingpage->id."' and lixo=0"); 
		if($sql->rows) {
			$depoimentos=mysqli_fetch_object($sql->mysqry);

			$depoimentosFeitos=array();
			if(!empty($depoimentos->depoimento1)) {
				$depoimentosFeitos[]=(object) array('descricao'=>utf8_encode($depoimentos->depoimento1),'autor'=>utf8_encode($depoimentos->autor1));
			} else if(!empty($depoimentos->depoimento2)) {
				$depoimentosFeitos[]=(object) array('descricao'=>utf8_encode($depoimentos->depoimento2),'autor'=>utf8_encode($depoimentos->autor2));
			} else if(!empty($depoimentos->depoimento3)) {
				$depoimentosFeitos[]=(object) array('descricao'=>utf8_encode($depoimentos->depoimento3),'autor'=>utf8_encode($depoimentos->autor3));
			}
	?>
	<section id="depoimentos" class="depoimentos">
		<section class="depoimentos__content content">

			<h1 class="galeria__titulo">Depoimentos</h1>

			<section class="depoimentos__slick">

				<?php 
					foreach($depoimentosFeitos as $x) {
				?>
				<section class="depoimentos__item">
					<p class="depoimentos__quote"><i class="iconify" data-icon="dashicons-format-quote"></i> <?php echo $x->descricao;?> <i class="iconify" data-icon="dashicons-format-quote" data-flip="horizontal,vertical"></i></p>
					<p class="depoimentos__autor"><?php echo $x->autor;?></p>
				</section>
				<?php 
					}
				?>

				<?php /* 
				<section class="depoimentos__item">
					<iframe width="560" height="315" src="https://www.youtube.com/embed/fJ432l2wt8U" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
				</section> */ ?>

			</section>

		</section>
	</section>
	<?php 
		}
	?>

	<?php
		if(is_object($sobrenos)) {
	?>
	<section id="sobre-nos" class="sobre">
		<section class="sobre__content content">

			<section class="sobre__inner1">
				<h1 class="sobre__titulo"><?php echo utf8_encode($sobrenos->nome);?></h1>
				<section class="sobre__social">
					<?php if($sobrenos->instagram){?><a href="<?php echo $sobrenos->instagram;?>" target="_blank"><i class="iconify" data-icon="fa-brands:instagram"></i></a><?php }?>
					<?php if($sobrenos->facebook){?><a href="<?php echo $sobrenos->facebook;?>" target="_blank"><i class="iconify" data-icon="fa-brands:facebook"></i></a><?php }?>
					<?php /*
					<a href="" target="_blank"><i class="iconify" data-icon="fa-brands:youtube"></i></a>
					<a href="" target="_blank"><i class="iconify" data-icon="fa-brands:linkedin"></i></a>
					<a href="" target="_blank"><i class="iconify" data-icon="fa-brands:twitter"></i></a> */ ?>
				</section>
				<div class="sobre__texto fck">
					<?php echo utf8_encode($sobrenos->texto);?>
				</div>
			</section>
			<section class="sobre__inner2">
				<a href="tel:<?php echo telefone($sobrenos->telefone);?>" class="sobre__telefone button button__lg button__full"><?php echo $sobrenos->telefone;?></a>
				<a href="https://wa.me/55<?php echo telefone($sobrenos->whatsapp);?>" target="_blank" class="sobre__whatsapp button button__lg button__full">WhatsApp</a>
				<p class="sobre__endereco"><?php echo utf8_encode($sobrenos->endereco);?></p>
				<?php if(!empty($sobrenos->mapa)){?><a href="<?php echo $sobrenos->mapa;?>" class="button">mapa da localização</a><?php }?>
			</section>

		</section>
	</section>
	<?php 
		}
	?>

</section>

<?php
if(isset($_POST['acao']) and $_POST['acao']=="preco") {

	$sql->consult($_p."landingpage_formulario","*","where lixo='0' and ip='".$_SERVER['REMOTE_ADDR']."' and data > NOW() - INTERVAL 5 MINUTE and preferencia='".addslashes($_POST['preferencia'])."'");
	if($sql->rows) {
		$jsc->jAlert("Já recebemos seu contato!<br />Entraremos em contato o mais breve!","sucesso","");

	} else {

		$vSQL="data=now(),
				status='novo',
				id_tema=$landingpage->id,
				ip='".$_SERVER['REMOTE_ADDR']."',
				tipo='captacao',
				nome='".utf8_decode(addslashes($_POST['nome']))."',
				preferencia='".utf8_decode(addslashes($_POST['preferencia']))."',
				telefone='".utf8_decode(addslashes($_POST['telefone']))."',
				email='".utf8_decode(addslashes($_POST['email']))."'";

		$sql->add($_p."landingpage_formulario",$vSQL);
		$jsc->jAlert("Entraremos em contato o mais breve!","sucesso","#captacao");
	}
}
include "includes/footer.php";
?>