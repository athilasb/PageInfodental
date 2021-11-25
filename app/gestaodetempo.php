<?php
include "includes/header.php";
include "includes/nav.php";
if($usr->tipo!="admin" and !in_array("parametros",$_usuariosPermissoes)) {
	$jsc->jAlert("Você não tem permissão para acessar esta área!","erro","document.location.href='dashboard.php'");
	die();
}
$values=$adm->get($_GET);
?>



<section class="content">

	<section class="filtros">
		<div class="filter-group">
			<div class="filter-title">
				Gestão de Tempo
			</div>
		</div>
		<div class="filter-group filter-group_right">
			<div class="filter-button">
				<a href=""><span>HOJE</span></a>
				<a href="javascript:;" class="datecalendar"><i class="iconify" data-icon="bi:calendar-week"></i> <span>Quinta-feira, 25 de nov. de 2021</span></a></div>
			</div>
		</div>
	</section>

	<section class="grid grid_5">
		
		<div style="grid-column:span 3; display:flex; flex-direction:column;">
			<section class="indices">
				<a href="pg_agenda_kanban.php" class="indices-item">
					<p><strong>AGENDAMENTOS</strong></p>
					<h1><strong>30</strong></h1>
				</a>
				<div class="indices-separador"></div>
				<a href="pg_agenda_kanban.php" class="indices-item">
					<p>CONFIRMADOS</p>
					<div class="indices-item__flex">
						<h1>20</h1>
						<h3><i class="iconify" data-icon="mdi:whatsapp"></i>11</h3>
						<h3><i class="iconify" data-icon="mdi:phone-check"></i>9</h3>
					</div>
				</a>
				<a href="pg_agenda_kanban.php" class="indices-item">
					<p>A CONFIRMAR</p>
					<h1>10</h1>
				</a>
				<a href="pg_agenda_kanban.php" class="indices-item">
					<p>ATENDIDOS</p>
					<h1>2</h1>
				</a>
				<a href="pg_agenda_kanban.php" class="indices-item">
					<p>FALTAS</p>
					<h1>2</h1>
				</a>
			</section>

			<section class="box" style="margin-bottom:2rem">
				<div class="filter">
					<div class="filter-group">
						<div class="filter-title"><span>Ociosidade de <strong>Cadeiras</strong></span></div>
					</div>
					<div class="filter-group filter-group_right">
						<div class="filter-title"><strong>14%</strong></div>
					</div>
				</div>

				<script>
					$(function() {
						$('.ocio-slick').slick({
							variableWidth:true,
							// slidesToShow:6,
							slidesToScroll:1,
							arrows:true,
							dots:false,
							infinite:false
						});
					});
				</script>
				<div class="ocio">
					<div class="ocio-slick">

						<div class="ocio-item">
							<header>
								<h1>CADEIRA 1</h1>
							</header>
							<article>
								<div class="ocio-item__inner1">
									<h1>14%<i class="iconify" data-icon="mdi:arrow-up" style="color:var(--vermelho);"></i></h1>
									<p>OCIOSIDADE <br />PREVISTA</p>									
								</div>
								<div class="ocio-item__inner1">
									<h2>3H20</h2>
									<p>TEMPO <br />DISPONÍVEL</p>
								</div>
							</article>
						</div>

						<div class="ocio-item">
							<header>
								<h1>CADEIRA 2</h1>
							</header>
							<article>
								<div class="ocio-item__inner1">
									<h1>11%<i class="iconify" data-icon="mdi:arrow-down" style="color:var(--verde);"></i></h1>
									<p>OCIOSIDADE <br />PREVISTA</p>									
								</div>
								<div class="ocio-item__inner1">
									<h2>3H20</h2>
									<p>TEMPO <br />DISPONÍVEL</p>
								</div>
							</article>
						</div>

						<div class="ocio-item">
							<header>
								<h1>CADEIRA 3</h1>
							</header>
							<article>
								<div class="ocio-item__inner1">
									<h1>11%<i class="iconify" data-icon="mdi:equal" style="color:var(--cinza3);"></i></h1>
									<p>OCIOSIDADE <br />PREVISTA</p>									
								</div>
								<div class="ocio-item__inner1">
									<h2>3H20</h2>
									<p>TEMPO <br />DISPONÍVEL</p>
								</div>
							</article>
						</div>

						<div class="ocio-item">
							<header>
								<h1>CADEIRA 3</h1>
							</header>
							<article>
								<div class="ocio-item__inner1">
									<h1>11%<i class="iconify" data-icon="mdi:equal" style="color:var(--cinza3);"></i></h1>
									<p>OCIOSIDADE <br />PREVISTA</p>									
								</div>
								<div class="ocio-item__inner1">
									<h2>3H20</h2>
									<p>TEMPO <br />DISPONÍVEL</p>
								</div>
							</article>
						</div>

						<div class="ocio-item">
							<header>
								<h1>CADEIRA 3</h1>
							</header>
							<article>
								<div class="ocio-item__inner1">
									<h1>11%<i class="iconify" data-icon="mdi:equal" style="color:var(--cinza3);"></i></h1>
									<p>OCIOSIDADE <br />PREVISTA</p>									
								</div>
								<div class="ocio-item__inner1">
									<h2>3H20</h2>
									<p>TEMPO <br />DISPONÍVEL</p>
								</div>
							</article>
						</div>

						<div class="ocio-item">
							<header>
								<h1>CADEIRA 3</h1>
							</header>
							<article>
								<div class="ocio-item__inner1">
									<h1>11%<i class="iconify" data-icon="mdi:equal" style="color:var(--cinza3);"></i></h1>
									<p>OCIOSIDADE <br />PREVISTA</p>									
								</div>
								<div class="ocio-item__inner1">
									<h2>3H20</h2>
									<p>TEMPO <br />DISPONÍVEL</p>
								</div>
							</article>
						</div>

						<div class="ocio-item">
							<header>
								<h1>CADEIRA 3</h1>
							</header>
							<article>
								<div class="ocio-item__inner1">
									<h1>11%<i class="iconify" data-icon="mdi:equal" style="color:var(--cinza3);"></i></h1>
									<p>OCIOSIDADE <br />PREVISTA</p>									
								</div>
								<div class="ocio-item__inner1">
									<h2>3H20</h2>
									<p>TEMPO <br />DISPONÍVEL</p>
								</div>
							</article>
						</div>

						<div class="ocio-item">
							<header>
								<h1>CADEIRA 3</h1>
							</header>
							<article>
								<div class="ocio-item__inner1">
									<h1>11%<i class="iconify" data-icon="mdi:equal" style="color:var(--cinza3);"></i></h1>
									<p>OCIOSIDADE <br />PREVISTA</p>									
								</div>
								<div class="ocio-item__inner1">
									<h2>3H20</h2>
									<p>TEMPO <br />DISPONÍVEL</p>
								</div>
							</article>
						</div>
					</div>
				</div>
			</section>

			<section class="box">
				<div class="filter">
					<div class="filter-group">
						<div class="filter-title"><span>Ociosidade de <strong>Dentistas</strong></span></div>
					</div>
					<div class="filter-group filter-group_right">
						<div class="filter-title"><strong>14%</strong></div>
					</div>
				</div>

				<script>
					$(function() {
						$('.ocio-slick').slick({
							variableWidth:true,
							// slidesToShow:6,
							slidesToScroll:1,
							arrows:true,
							dots:false,
							infinite:false
						});
					});
				</script>
				<div class="ocio">
					<div class="ocio-slick">

						<div class="ocio-item">
							<header>
								<h1>DR. KRONER MACHADO COSTA</h1>
							</header>
							<article>
								<div class="ocio-item__inner1">
									<h1>14%<i class="iconify" data-icon="mdi:arrow-up" style="color:var(--vermelho);"></i></h1>
									<p>OCIOSIDADE <br />PREVISTA</p>									
								</div>
								<div class="ocio-item__inner1">
									<h2>3H20</h2>
									<p>TEMPO <br />DISPONÍVEL</p>
								</div>
							</article>
						</div>

						<div class="ocio-item">
							<header>
								<h1>DR. PEDRO HENRIQUE SADDI...</h1>
							</header>
							<article>
								<div class="ocio-item__inner1">
									<h1>14%<i class="iconify" data-icon="mdi:arrow-up" style="color:var(--vermelho);"></i></h1>
									<p>OCIOSIDADE <br />PREVISTA</p>									
								</div>
								<div class="ocio-item__inner1">
									<h2>3H20</h2>
									<p>TEMPO <br />DISPONÍVEL</p>
								</div>
							</article>
						</div>
					</div>
				</div>
			</section>
		</div>

		<div class="box" style="grid-column:span 2; height:840px; display:flex; flex-direction:column;">
			
			<div class="filter">
				<div class="filter-group">
					<div class="filter-title"><span>Oportunidades de Agendamento</span></div>
				</div>
			</div>

			<div class="grid grid_2" style="flex:1; min-height:0;">

				<div class="oport">
					<header>
						<h1><strong>Em tratamento</strong> a agendar</h1>
					</header>
					<article>
						<a href="javascript:;" class="oport-item tooltip" title="retornar depois">
							<img src="img/ilustra-perfil.png" width="45" height="45" class="oport-item__foto" />
							<h1>AUGUSTO XAVIER MACHADO</h1>
							<i class="iconify" data-icon="mdi:phone-clock"></i>
						</a>
						<a href="javascript:;" class="oport-item tooltip" title="retornar depois">
							<img src="img/ilustra-perfil.png" width="45" height="45" class="oport-item__foto" />
							<h1>AUGUSTO XAVIER MACHADO</h1>
							<i class="iconify" data-icon="mdi:phone-clock"></i>
						</a>
						<a href="javascript:;" class="oport-item tooltip" title="retornar depois">
							<img src="img/ilustra-perfil.png" width="45" height="45" class="oport-item__foto" />
							<h1>AUGUSTO XAVIER MACHADO</h1>
							<i class="iconify" data-icon="mdi:phone-clock"></i>
						</a>
						<a href="javascript:;" class="oport-item tooltip" title="nunca abordado">
							<img src="img/ilustra-perfil.png" width="45" height="45" class="oport-item__foto" />
							<h1>AUGUSTO XAVIER MACHADO</h1>
							<i class="iconify" data-icon="mdi:moon-new"></i>
						</a>
						<a href="javascript:;" class="oport-item tooltip" title="nunca abordado">
							<img src="img/ilustra-perfil.png" width="45" height="45" class="oport-item__foto" />
							<h1>AUGUSTO XAVIER MACHADO</h1>
							<i class="iconify" data-icon="mdi:moon-new"></i>
						</a>
						<a href="javascript:;" class="oport-item tooltip" title="nunca abordado">
							<img src="img/ilustra-perfil.png" width="45" height="45" class="oport-item__foto" />
							<h1>AUGUSTO XAVIER MACHADO</h1>
							<i class="iconify" data-icon="mdi:moon-new"></i>
						</a>
						<a href="javascript:;" class="oport-item tooltip" title="não atendeu">
							<img src="img/ilustra-perfil.png" width="45" height="45" class="oport-item__foto" />
							<h1>AUGUSTO XAVIER MACHADO</h1>
							<i class="iconify" data-icon="mdi:phone-remove"></i>
						</a>
						<a href="javascript:;" class="oport-item tooltip" title="não atendeu">
							<img src="img/ilustra-perfil.png" width="45" height="45" class="oport-item__foto" />
							<h1>AUGUSTO XAVIER MACHADO</h1>
							<i class="iconify" data-icon="mdi:phone-remove"></i>
						</a>
						<a href="javascript:;" class="oport-item tooltip" title="não atendeu">
							<img src="img/ilustra-perfil.png" width="45" height="45" class="oport-item__foto" />
							<h1>AUGUSTO XAVIER MACHADO</h1>
							<i class="iconify" data-icon="mdi:phone-remove"></i>
						</a>
						<a href="javascript:;" class="oport-item tooltip" title="vai retornar">
							<img src="img/ilustra-perfil.png" width="45" height="45" class="oport-item__foto" />
							<h1>AUGUSTO XAVIER MACHADO</h1>
							<i class="iconify" data-icon="mdi:phone-return"></i>
						</a>
						<a href="javascript:;" class="oport-item tooltip" title="vai retornar">
							<img src="img/ilustra-perfil.png" width="45" height="45" class="oport-item__foto" />
							<h1>AUGUSTO XAVIER MACHADO</h1>
							<i class="iconify" data-icon="mdi:phone-return"></i>
						</a>
						<a href="javascript:;" class="oport-item tooltip" title="vai retornar">
							<img src="img/ilustra-perfil.png" width="45" height="45" class="oport-item__foto" />
							<h1>AUGUSTO XAVIER MACHADO</h1>
							<i class="iconify" data-icon="mdi:phone-return"></i>
						</a>
						<a href="javascript:;" class="oport-item tooltip" title="não quer ser incomodado">
							<img src="img/ilustra-perfil.png" width="45" height="45" class="oport-item__foto" />
							<h1>AUGUSTO XAVIER MACHADO</h1>
							<i class="iconify" data-icon="mdi:cancel"></i>
						</a>
						<a href="javascript:;" class="oport-item tooltip" title="não quer ser incomodado">
							<img src="img/ilustra-perfil.png" width="45" height="45" class="oport-item__foto" />
							<h1>AUGUSTO XAVIER MACHADO</h1>
							<i class="iconify" data-icon="mdi:cancel"></i>
						</a>
						<a href="javascript:;" class="oport-item tooltip" title="não quer ser incomodado">
							<img src="img/ilustra-perfil.png" width="45" height="45" class="oport-item__foto" />
							<h1>AUGUSTO XAVIER MACHADO</h1>
							<i class="iconify" data-icon="mdi:cancel"></i>
						</a>						
					</article>
				</div>

				<div class="oport">
					<header>
						<h1><strong>Em contenção</strong> a agendar</h1>
					</header>
					<article>
						<a href="javascript:;" class="oport-item tooltip" title="retornar depois">
							<img src="img/ilustra-perfil.png" width="45" height="45" class="oport-item__foto" />
							<h1>AUGUSTO XAVIER MACHADO</h1>
							<i class="iconify" data-icon="mdi:phone-clock"></i>
						</a>
						<a href="javascript:;" class="oport-item tooltip" title="retornar depois">
							<img src="img/ilustra-perfil.png" width="45" height="45" class="oport-item__foto" />
							<h1>AUGUSTO XAVIER MACHADO</h1>
							<i class="iconify" data-icon="mdi:phone-clock"></i>
						</a>					
					</article>
				</div>

				
				
			</div>
			
		</div>

	</section>

</section>




<?php
include "includes/footer.php";
?> 
