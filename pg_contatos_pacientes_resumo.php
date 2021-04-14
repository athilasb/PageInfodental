<?php
	$title="";
	include "includes/header.php";
	include "includes/nav.php";

	$paciente='';
	if(isset($_GET['id_paciente']) and is_numeric($_GET['id_paciente'])) {
		$sql->consult($_p."pacientes","*","where id='".$_GET['id_paciente']."'");
		if($sql->rows) {
			$paciente=mysqli_fetch_object($sql->mysqry);
		}
	}

	if(empty($paciente)) {
		$jsc->jAlert("Paciente não encontrado!","erro","document.location.href='pg_contatos_pacientes.php'");
		die();
	}

	$_profissionais=array();
	$sql->consult($_p."profissionais","id,nome","");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_profissionais[$x->id]=$x;
	}

?>
<script>
	$(function(){
		// $('.m-contatos').next().show();		
		$('.m-contatos').addClass("active");
		
		$('.paciente-etapas__slick').slick({
			dots:true,
			arrows:false
		});
		
		$('.paciente-fotos__slick').slick({
			dots:true,
			slidesToShow:2,
			slidesToScroll:2,
			arrows:false
		});
		
	});
</script>
<script src="js/jquery.vendas.js"></script>

	<section class="content">

		<?php
		require_once("includes/abaPaciente.php");
		?>
		
		<section class="grid grid_3">
			
			<div class="box">
				<div class="paciente-info">
					<?php /*
					<header class="paciente-info-header">
						<img src="../infodental2/img/ilustra-paciente.jpg" alt="" width="84" height="84" class="paciente-info-header__foto" />
						<section class="paciente-info-header__inner1">
							<h1>Ana Lopes da Silva Azevedo</h1>
							<p>25 anos</p>
							<p><span style="color:var(--cinza3);">#224599</span> <span style="color:var(--cor1);">ATIVO</span></p>
						</section>
					</header>
					*/ ?>
					<?php
					if($paciente->indicacao_tipo=="PACIENTE") {
						$indicacaoTabela=$_p."pacientes";
						$indicacaoTitulo="nome";
					} else if($paciente->indicacao_tipo=="PROFISSIONAL") {
						$indicacaoTabela=$_p."profissionais";
						$indicacaoTitulo="nome";
					} else {
						$indicacaoTabela=$_p."parametros_indicacoes";
						$indicacaoTitulo="titulo";
					}
					$pacienteIndicacao="-";
					if(isset($paciente->indicacao) and is_numeric($paciente->indicacao) and $paciente->indicacao>0) {
						$sql->consult($indicacaoTabela,$indicacaoTitulo,"where id=$paciente->indicacao");
						if($sql->rows) {
							$i=mysqli_fetch_object($sql->mysqry);
							$pacienteIndicacao=utf8_encode($i->$indicacaoTitulo);
						}

					}
					?>
					<div class="paciente-info-grid">
						<p class="paciente-info-grid__item"><i class="iconify" data-icon="mdi-instagram"></i> <?php echo empty($paciente->instagram)?"-":'<a href="http://instagram.com/'.str_replace("@","",$paciente->instagram).'" target="_blank">'.utf8_encode($paciente->instagram.'</a>');?></p>
						<p class="paciente-info-grid__item"><i class="iconify" data-icon="mdi-phone"></i> <?php echo empty($paciente->telefone1)?"-":utf8_encode($paciente->telefone1);?></p>
						<p class="paciente-info-grid__item"><i class="iconify" data-icon="mdi-music"></i> <?php echo empty($paciente->musica)?"-":utf8_encode($paciente->musica);?></p>
						<p class="paciente-info-grid__item"><i class="iconify" data-icon="mdi-hand-pointing-right"></i> <?php echo $pacienteIndicacao;?></p>
						<p class="paciente-info-grid__item" style="color:red;"><i class="iconify" data-icon="mdi-alert"></i> -</p>
						<p class="paciente-info-grid__item" style="color:red;"><i class="iconify" data-icon="mdi-currency-usd-circle-outline"></i> -</p>
					</div>
				</div>
			</div>

			<div class="box" style="grid-column:span 2;grid-row:span 2">
				<div class="paciente-evolucao" sty>
					<h1 class="paciente__titulo1">Evolução</h1>
					<?php /*<a href="" class="paciente-evolucao__add"><i class="iconify" data-icon="mdi-plus-circle-outline"></i> Adicionar evolução</a>*/ ?>
					<div class="paciente-scroll">
						<?php /*<table class="paciente-agenda-table">
							<tr>
								<td><i class="iconify" data-icon="mdi-pill"></i></td>
								<td>16/03/2020<br /><span style="color:var(--cinza4)">18:06</span></td>
								<td>Dr. Kronner</td>
								<td><strong>Receituário Pós-Operatório</strong></td>
							</tr>
							<tr>
								<td><i class="iconify" data-icon="mdi-clipboard-check-outline"></i></td>
								<td>16/03/2020<br /><span style="color:var(--cinza4)">18:06</span></td>
								<td>Dr. Kronner</td>
								<td><strong>Atestado Pós-Operatório</strong></td>
							</tr>
							<tr>
								<td><i class="iconify" data-icon="mdi-progress-check"></i></td>
								<td>16/03/2020<br /><span style="color:var(--cinza4)">18:06</span></td>
								<td>Dr. Kronner</td>
								<td><strong>Prótese Múltipla de Resina / PMMA (43/43)</strong><br />Procedimento finalizado.</td>			
							</tr>
							<tr>
								<td><i class="iconify" data-icon="mdi-pill"></i></td>
								<td>16/03/2020<br /><span style="color:var(--cinza4)">18:06</span></td>
								<td>Dr. Kronner</td>
								<td><strong>Receituário Pós-Operatório</strong></td>
							</tr>
							<tr>
								<td><i class="iconify" data-icon="mdi-clipboard-check-outline"></i></td>
								<td>16/03/2020<br /><span style="color:var(--cinza4)">18:06</span></td>
								<td>Dr. Kronner</td>
								<td><strong>Atestado Pós-Operatório</strong></td>
							</tr>
							<tr>
								<td><i class="iconify" data-icon="mdi-progress-check"></i></td>
								<td>16/03/2020<br /><span style="color:var(--cinza4)">18:06</span></td>
								<td>Dr. Kronner</td>
								<td><strong>Prótese Múltipla de Resina / PMMA (43/43)</strong><br />Procedimento finalizado.</td>			
							</tr>
							<tr>
								<td><i class="iconify" data-icon="mdi-pill"></i></td>
								<td>16/03/2020<br /><span style="color:var(--cinza4)">18:06</span></td>
								<td>Dr. Kronner</td>
								<td><strong>Receituário Pós-Operatório</strong></td>
							</tr>
							<tr>
								<td><i class="iconify" data-icon="mdi-clipboard-check-outline"></i></td>
								<td>16/03/2020<br /><span style="color:var(--cinza4)">18:06</span></td>
								<td>Dr. Kronner</td>
								<td><strong>Atestado Pós-Operatório</strong></td>
							</tr>
							<tr>
								<td><i class="iconify" data-icon="mdi-progress-check"></i></td>
								<td>16/03/2020<br /><span style="color:var(--cinza4)">18:06</span></td>
								<td>Dr. Kronner</td>
								<td><strong>Prótese Múltipla de Resina / PMMA (43/43)</strong><br />Procedimento finalizado.</td>			
							</tr>
						</table>*/?>
						<div style="text-align: center;color:#CCC"><span class="iconify" data-icon="el:eye-close" data-inline="false" data-height="50"></span><br />Nenhum registro.</div>
					</div>
				</div>
			</div>

			<div class="box" style="overflow:hidden;">
				<div class="paciente-etapas">
					<div class="paciente-etapas__slick">
						<?php /*<div class="paciente-etapas__item">
							<h1 class="paciente__titulo1">Cirúrgico Inferior <small>(08/12/2020)</small></h1>
							<script>
							$(function(){
								
								var ctx = document.getElementById('grafico1').getContext('2d');
								var grafico1 = new Chart(ctx, {    
								    type: 'doughnut',
								    options: {
								    	legend: {display:false},
								    	cutoutPercentage:70,
								    },
								    data: {
								        labels: ["Procedimento 1","Procedimento 2","Procedimento 3", "Procedimento 4"],
								        datasets: [{
								            data: [10,20,20,50],
								            backgroundColor: ['rgba(211,142,105,1)','rgba(239,198,155,1)','rgba(93,109,112,1)','rgba(72,74,71,1)','rgba(138,176,171,1)'],						            
								        }]
								    },
								});		
							});				
							</script>
							<div class="paciente-etapas-grid">
								<p>Conclusão da Evolução</p>
								<div class="grafico-barra"><span style="width:80%">&nbsp;</span></div>
								<p>Conclusão do Pagamento</p>
								<div class="grafico-barra"><span style="width:50%">&nbsp;</span></div>
							</div>

							
							
						</div>
						<div class="paciente-etapas__item">
							<h1 class="paciente__titulo1">Cirúrgico Superior</h1>
						</div>*/?><div style="text-align: center;color:#CCC"><span class="iconify" data-icon="el:eye-close" data-inline="false" data-height="50"></span><br />Nenhum registro.</div>
					</div>					
				</div>
			</div>

			<div class="box" style="overflow:hidden;">
				<div class="paciente-fotos">
					<h1 class="paciente__titulo1">Fotos</h1>
					<?php /*<div class="paciente-fotos__slick">
						<a href="../infodental2/img/ilustra-fotos.jpg" data-fancybox="galeria" class="paciente-fotos__item"><img src="../infodental2/img/ilustra-fotos.jpg" alt="" width="208" height="178" class="paciente-fotos__foto" /></a>
						<a href="../infodental2/img/ilustra-fotos.jpg" data-fancybox="galeria" class="paciente-fotos__item"><img src="../infodental2/img/ilustra-fotos.jpg" alt="" width="208" height="178" class="paciente-fotos__foto" /></a>
						<a href="../infodental2/img/ilustra-fotos.jpg" data-fancybox="galeria" class="paciente-fotos__item"><img src="../infodental2/img/ilustra-fotos.jpg" alt="" width="208" height="178" class="paciente-fotos__foto" /></a>
						<a href="../infodental2/img/ilustra-fotos.jpg" data-fancybox="galeria" class="paciente-fotos__item"><img src="../infodental2/img/ilustra-fotos.jpg" alt="" width="208" height="178" class="paciente-fotos__foto" /></a>
						<a href="../infodental2/img/ilustra-fotos.jpg" data-fancybox="galeria" class="paciente-fotos__item"><img src="../infodental2/img/ilustra-fotos.jpg" alt="" width="208" height="178" class="paciente-fotos__foto" /></a>
						<a href="../infodental2/img/ilustra-fotos.jpg" data-fancybox="galeria" class="paciente-fotos__item"><img src="../infodental2/img/ilustra-fotos.jpg" alt="" width="208" height="178" class="paciente-fotos__foto" /></a>
					</div>*/
					?>

						<div style="text-align: center;color:#CCC"><span class="iconify" data-icon="el:eye-close" data-inline="false" data-height="50"></span><br />Nenhum registro.</div>
				</div>
			</div>

			<div class="box">
				<div class="paciente-agenda">
					<h1 class="paciente__titulo1">Agendamentos</h1>
					<div class="paciente-scroll">						
						<table class="paciente-agenda-table">
							<?php
							$sql->consult($_p."agenda","*","where id_paciente=$paciente->id and lixo=0 order by agenda_data desc");
							if($sql->rows) {
								while($x=mysqli_fetch_object($sql->mysqry)) {
							?>
							<tr>
								<td><?php echo date('d/m/y',strtotime($x->agenda_data));?><br /><span style="color:var(--cinza4);"><?php echo date('H:i',strtotime($x->agenda_data));?></span></td>
								<td>
									<?php
									$profissionais="-";
									if(!empty($x->profissionais)) {
										$profissionais='';
										$aux=explode(",",$x->profissionais);
										foreach($aux as $v) {
											if(!empty($v) and is_numeric($v) and isset($_profissionais[$v])) $profissionais.=utf8_encode($_profissionais[$v]->nome).", ";
										}
									}
									echo substr($profissionais,0,strlen($profissionais)-2);
									?>
								</td>
								<td><i class="iconify" data-icon="mdi-calendar-month" style="color:var(--cinza4)"></i></td>
							</tr>
							<?php
								}
							}
							?>
						</table>
					</div>
				</div>
			</div>

			<div class="box">
				<div class="paciente-wp">
					<h1 class="paciente__titulo1">Histórico WhatsApp</h1>
					<script>
						$(function() {
							$(".paciente-wp__inner1").scrollTop($(".paciente-wp__inner1")[0].scrollHeight);
						});
					</script>
					<?php /*<div class="paciente-scroll paciente-wp__inner1">
						<div class="paciente-wp__item">
							<p class="paciente-wp__msg">Bom dia Dr. Kronner</p>
							<p class="paciente-wp__data">18/04/2020 • 09:40</p>
						</div>
						<div class="paciente-wp__item">
							<p class="paciente-wp__msg">Obrigado pelo cuidado que vocês tiveram comigo!</p>
							<p class="paciente-wp__data">18/04/2020 • 09:40</p>
						</div>
						<div class="paciente-wp__item paciente-wp__item_autor">
							<p class="paciente-wp__msg">Imagina! A gente que agradece</p>
							<p class="paciente-wp__data">18/04/2020 • 09:40</p>
						</div>
						<div class="paciente-wp__item">
							<p class="paciente-wp__msg">Bom dia Dr. Kronner</p>
							<p class="paciente-wp__data">18/04/2020 • 09:40</p>
						</div>
						<div class="paciente-wp__item">
							<p class="paciente-wp__msg">Obrigado pelo cuidado que vocês tiveram comigo!</p>
							<p class="paciente-wp__data">18/04/2020 • 09:40</p>
						</div>
						<div class="paciente-wp__item paciente-wp__item_autor">
							<p class="paciente-wp__msg">Imagina! A gente que agradece</p>
							<p class="paciente-wp__data">18/04/2020 • 09:40</p>
						</div>
					</div>*/?>
					<div style="text-align: center;color:#CCC"><span class="iconify" data-icon="el:eye-close" data-inline="false" data-height="50"></span><br />Nenhum registro.</div>
				</div>
			</div>

		</section>
	
	</section>


<?php
	include "includes/footer.php";
?>