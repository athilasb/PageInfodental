<?php
	include "includes/header.php";
	include "includes/nav.php";

	$_table=$_p."colaboradores";


	$_cargos=array();
	$sql->consult($_p."colaboradores_cargos","*","where lixo=0 order by titulo asc");
	while($x=mysqli_fetch_object($sql->mysqry)) $_cargos[$x->id]=$x;

	$_planos=array();
	$sql->consult($_p."parametros_planos","*","where lixo=0 order by titulo asc");
	while($x=mysqli_fetch_object($sql->mysqry)) $_planos[$x->id]=$x;

	$_procedimentos=array();
	$sql->consult($_p."parametros_procedimentos","id,titulo","where lixo=0 order by titulo asc");
	while($x=mysqli_fetch_object($sql->mysqry)) $_procedimentos[$x->id]=$x;

	$_cadeiras=array();
	$sql->consult($_p."parametros_cadeiras","id,titulo","where lixo=0 order by ordem asc");
	while($x=mysqli_fetch_object($sql->mysqry)) $_cadeiras[$x->id]=$x;


	$data = date('Y-m-d');

	if(isset($_GET['data']) and !empty($_GET['data'])) {
		list($dia,$mes,$ano)=explode("/",$_GET['data']);

		if(checkdate($mes, $dia, $ano)) {

			$data = $ano."-".$mes."-".$dia;

		}
	}


	$_cadeiras = array();
	$sql->consult($_p."parametros_cadeiras","*","where lixo=0");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_cadeiras[$x->id]=$x;
	}

	// calcula horas do dia de cada cadeira
	$dataDia = date('w',strtotime($data));

	$_horas = array();
	$sql->consult($_p."parametros_cadeiras_horarios","*","where dia='$dataDia' and lixo=0");
	if($sql->rows) {
		while($x=mysqli_fetch_object($sql->mysqry)) {
			if(!isset($_horas[$x->id_cadeira])) $_horas[$x->id_cadeira]=0;

			$dif = (strtotime($x->fim)-strtotime($x->inicio))/(60);

			$_horas[$x->id_cadeira]+=$dif;

		}
	}

	$_agendaHoras = array();

	$sql->consult($_p."agenda","id,id_cadeira,agenda_duracao","where agenda_data>='".$data." 00:00:00' and agenda_data<='".$data." 23:59:59' and id_status IN (1,2,5) and lixo=0");
	while($x=mysqli_fetch_object($sql->mysqry)) {

			if(!isset($_agendaHoras[$x->id_cadeira])) $_agendaHoras[$x->id_cadeira]=0;
			$_agendaHoras[$x->id_cadeira]+=$x->agenda_duracao;
	}


	
?>

	<header class="header">
		<div class="header__content content">

			<div class="header__inner1">
				<section class="header-title">
					<h1>Tarefas Inteligentes</h1>
				</section>
				<?php
				require_once("includes/menus/menuInteligencia.php");
				?>


			</div>
			<div class="header__inner2">
				<section class="header-date">
					<div class="header-date-buttons">
					
					</div>
					<div class="header-date-now">
						<h1 class="js-cal-titulo-diames"><?php echo date('d',strtotime($data));?></h1>
						<h2 class="js-cal-titulo-mes"><?php echo substr(strtolower(mes(date('m',strtotime($data)))),0,3);?></h2>
						<h3 class="js-cal-titulo-dia"><?php echo strtolower(diaDaSemana(date('w',strtotime($data))));?></h3>
					</div>
				</section>
			</div>
		</div>
	</header>


	<main class="main">
		<div class="main__content content">

			

			<section class="filter">
				
				<div class="filter-group">
					<div class="filter-title">	
						<p>Valorize o que mais importa, seu tempo! Análise de índices e sugestões guiadas por Inteligência Artificial</p>
					</div>
				</div>
				

				<div class="filter-group">
					<div class="filter-form form">
						<dl>
							<dd>
								<a href="<?php echo $_page."?data=".date('d/m/Y');?>" class="button<?php echo date('Y-m-d')==$data?" active":"";?>">hoje</a>	
								<a href="<?php echo $_page."?data=".date('d/m/Y',strtotime(date('Y-m-d')." + 1 day"));?>" class="button<?php echo date('Y-m-d',strtotime(date('Y-m-d')." + 1 day"))==$data?" active":"";?>">+ 1 dia</a>	
								<a href="<?php echo $_page."?data=".date('d/m/Y',strtotime(date('Y-m-d')." + 2 day"));?>" class="button<?php echo date('Y-m-d',strtotime(date('Y-m-d')." + 2 day"))==$data?" active":"";?>">+ 2 dias</a>	
								<a href="<?php echo $_page."?data=".date('d/m/Y',strtotime(date('Y-m-d')." + 3 day"));?>" class="button<?php echo date('Y-m-d',strtotime(date('Y-m-d')." + 3 day"))==$data?" active":"";?>">+ 3 dias</a>		
							</dd>
						</dl>						
					</div>					
				</div>

			</section>

			<section class="grid" style="grid-template-columns:40% auto">

				<div class="box">

					<div class="filter">
						<div class="filter-group">
							<div class="filter-title">
								<h1>Índices de Ociosidade</h1>
							</div>
						</div>
					</div>

					<section class="tab">
						<a href="" class="active">Cadeiras</a>
						<a href="">Dentistas</a>						
					</section>

					<section style="width:100%; height:300px; background:var(--cinza2); margin-bottom:var(--margin1);">						
					</section>

					<div class="list4">
						<?php
						foreach($_cadeiras as $c) {

							$cadeiraHoras = isset($_horas[$c->id]) ? $_horas[$c->id] : 0;
							$agendaHoras = isset($_agendaHoras[$c->id]) ? $_agendaHoras[$c->id] : 0;

							$indice = 100-ceil($cadeiraHoras==0?0:($agendaHoras/$cadeiraHoras)*100);
						?>
						<a href="" class="list4-item active">
							<div>
								<h1>
									<?php 
									if($indice>0) {
										echo $indice.'% <i class="iconify" data-icon="fluent:arrow-download-20-regular" style="color:#FF0000"></i>';
									} else {
										echo ($indice==0?0:($indice*-1)).'% <i class="iconify" data-icon="fluent:arrow-export-up-20-filled" style="color:green"></i>';
									}
									?>
								</h1>
							</div>
							<div>
								<p><?php echo utf8_encode($c->titulo)." - ".$agendaHoras."/".$cadeiraHoras."m";?></p>
							</div>
						</a>
						<?php
						}
						?>
						
					</div>
				</div>

				<div class="box box-col">

					<div class="box-col__inner1" style="flex:0 1 45%;">

						<div class="filter">
							<div class="filter-group">
								<div class="filter-title">
									<h1>Sugestões</h1>
								</div>
							</div>
						</div>

						<div class="list3">
							<a href="" class="list3-item">
								<i class="iconify" data-icon="fluent:lightbulb-filament-20-regular"></i>
								<p>Há 33 pacientes <strong>em tratamento</strong> sem agendamento futuro</p>
							</a>
							<a href="" class="list3-item">
								<i class="iconify" data-icon="fluent:lightbulb-filament-20-regular"></i>
								<p>Há 33 pacientes <strong>em tratamento</strong> sem agendamento futuro</p>
							</a>
						</div>

					</div>

					<div class="box-col__inner1 box_inv">
						
						<form method="form" class="form">
							<div class="colunas">
								<dl>
									<dd>
										<select name="">
											<option value="">em tratamento</option>
										</select>
									</dd>
								</dl>
								<dl>
									<dd>
										<select name="">
											<option value="">status</option>
										</select>
									</dd>
								</dl>
							</div>
						</form>

						<div class="list1">
							<table>
								<tr>
									<td class="list1__foto"><img src="img/ilustra-usuario.jpg" width="54" height="54" /></td>
									<td>
										<h1>ANA MARIA SOARES</h1>
										<p>(62) 984050927</p>
									</td>
									<td><i class="iconify" data-icon="fluent:call-dismiss-20-regular" style="font-size:2em; color:red;"></i></td>
								</tr>
								<tr>
									<td class="list1__foto"><img src="img/ilustra-usuario.jpg" width="54" height="54" /></td>
									<td>
										<h1>ANA MARIA SOARES</h1>
										<p>(62) 984050927</p>
									</td>
									<td><i class="iconify" data-icon="fluent:phone-checkmark-20-regular" style="font-size:2em; color:lightgreen;"></i></td>
								</tr>							
							</table>
						</div>

					</div>

				</div>

			</section>
		
		</div>
	</main>

<?php 
include "includes/footer.php";
?>	