<?php
include "includes/header.php";
include "includes/nav.php";
?>
	
	<script>
		$(function() {
			  var typed5 = new Typed('.typed', {
			    strings: ['autoestima', 'motivação', 'equilíbrio'],
			    typeSpeed: 50,
			    backSpeed: 30,
			    backDelay: 1500,
			    cursorChar: '',
			    smartBackspace: true,
			    loop: true
			  });
		});
	</script>

	<section class="banner" style="background-image:url(img/banner1.jpg)">
		<section class="banner__content">
			<h1 class="banner__titulo">Lente de Contato Dental aumenta sua <span class="typed"></span></h1>
			<h2 class="banner__descricao">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Repudiandae commodi porro sint illo quo asperiores cumque autem dignissimos ducimus laudantium.</h2>
			<iframe width="560" height="315" src="https://www.youtube.com/embed/fJ432l2wt8U" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
		</section>
	</section>

	<section id="informacoes" class="informacoes">
		<section class="informacoes__content content fck">
			<h2>O sorriso está diretamente ligado à nossa autoestima</h2>
			<p>A Lentes de Contato Dental melhora o formato dos dentes, cor e imperfeições, sem desgastá-los ou com mínimo desgaste em alguns casos. São lâminas de porcelanas muito finas com espessura de 0,2mm a 0,4mm.</p>
			<p>Em apenas um dia o paciente já está com um novo sorriso, mais alinhado, branco e esteticamente bonito. Após a visita ao dentista, o mesmo tira um molde da arcada dentária do paciente e através de procedimentos com tecnologia 3D se faz as lentes com o mesmo tamanho dos dentes do paciente, com um material mais resistente e durável. </p>
			<p>Não ter os dentes da cor ou formato desejados pode se tornar um empecilho a felicidade de ter aquele lindo sorriso, e até mesmo, ser um obstáculo na sua vida profissional.			</p>
		</section>
	</section>

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
			
			<form method="post" class="formulario-validacao form captacao-form">
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
					<dd><button type="submit" class="button button__lg">SOLICITAR ORÇAMENTO</button></dd>
				</dl>
			</form>

		</section>
	</section>

	<section id="antes-e-depois" class="galeria">
		<section class="galeria__content content">

			<h1 class="galeria__titulo">Antes e Depois</h1>

			<section class="galeria__slick">

				<section class="galeria__item">
					<img src="img/antes1.jpg" alt="" width="285" height="264" class="galeria__foto" />
					<img src="img/depois1.jpg" alt="" width="285" height="264" class="galeria__foto" />
				</section>
				<section class="galeria__item">
					<img src="img/antes1.jpg" alt="" width="285" height="264" class="galeria__foto" />
					<img src="img/depois1.jpg" alt="" width="285" height="264" class="galeria__foto" />
				</section>
				<section class="galeria__item">
					<img src="img/antes1.jpg" alt="" width="285" height="264" class="galeria__foto" />
					<img src="img/depois1.jpg" alt="" width="285" height="264" class="galeria__foto" />
				</section>
				<section class="galeria__item">
					<img src="img/antes1.jpg" alt="" width="285" height="264" class="galeria__foto" />
					<img src="img/depois1.jpg" alt="" width="285" height="264" class="galeria__foto" />
				</section>
				<section class="galeria__item">
					<img src="img/antes1.jpg" alt="" width="285" height="264" class="galeria__foto" />
					<img src="img/depois1.jpg" alt="" width="285" height="264" class="galeria__foto" />
				</section>
				<section class="galeria__item">
					<img src="img/antes1.jpg" alt="" width="285" height="264" class="galeria__foto" />
					<img src="img/depois1.jpg" alt="" width="285" height="264" class="galeria__foto" />
				</section>

			</section>
			
		</section>
	</section>

	<section id="depoimentos" class="depoimentos">
		<section class="depoimentos__content content">

			<h1 class="galeria__titulo">Depoimentos</h1>

			<section class="depoimentos__slick">

				<section class="depoimentos__item">
					<p class="depoimentos__quote"><i class="iconify" data-icon="dashicons-format-quote"></i> Achei que em pouco tempo de tratamento tive meu sorriso modificado e melhorado como sempre quis! <i class="iconify" data-icon="dashicons-format-quote" data-flip="horizontal,vertical"></i></p>
					<p class="depoimentos__autor">Letícia G.</p>
				</section>
				<section class="depoimentos__item">
					<iframe width="560" height="315" src="https://www.youtube.com/embed/fJ432l2wt8U" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
				</section>

			</section>

		</section>
	</section>

	<section id="sobre-nos" class="sobre">
		<section class="sobre__content content">

			<section class="sobre__inner1">
				<h1 class="sobre__titulo">Studio Dental</h1>
				<section class="sobre__social">
					<a href="" target="_blank"><i class="iconify" data-icon="fa-brands:instagram"></i></a>
					<a href="" target="_blank"><i class="iconify" data-icon="fa-brands:facebook"></i></a>
					<a href="" target="_blank"><i class="iconify" data-icon="fa-brands:youtube"></i></a>
					<a href="" target="_blank"><i class="iconify" data-icon="fa-brands:linkedin"></i></a>
					<a href="" target="_blank"><i class="iconify" data-icon="fa-brands:twitter"></i></a>
				</section>
				<div class="sobre__texto fck">
					<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Perferendis ad ab voluptatum blanditiis magni enim est beatae sequi soluta itaque, totam obcaecati cupiditate, doloremque iusto iste reiciendis repellendus similique aliquid nihil. Odio adipisci, aut, at sed quisquam, sit magni porro consequatur dolor dolorum sapiente autem veniam. </p>
					<p>Exercitationem nihil fugit provident nobis eius soluta repudiandae, corrupti. Blanditiis unde nam minima reprehenderit labore temporibus cumque dolores hic sit a iure, optio velit perferendis commodi excepturi repellendus? Suscipit, error, tenetur. Maxime, exercitationem.</p>
				</div>
			</section>
			<section class="sobre__inner2">
				<a href="tel:" class="sobre__telefone button button__lg button__full">(62) 3515-1717</a>
				<a href="https://wa.me/55" target="_blank" class="sobre__whatsapp button button__lg button__full">WhatsApp</a>
				<p class="sobre__endereco">Rua 5, n 691,  Ed. The Prime Tamandaré<br />Setor Oeste, Goiânia</p>
				<a href="" class="button">mapa da localização</a>
			</section>

		</section>
	</section>

</section>

<?php
include "includes/footer.php";
?>