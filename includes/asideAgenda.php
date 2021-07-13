	<?php
		$_status=array();
		$sql->consult($_p."agenda_status","*","where  lixo=0 order by kanban_ordem asc");
		while($x=mysqli_fetch_object($sql->mysqry)) {
			$_status[$x->id]=$x;
		}

		$_cadeiras=array();
		$sql->consult($_p."parametros_cadeiras","*","where lixo=0 and id_unidade=$usrUnidade->id order by titulo asc");
		while($x=mysqli_fetch_object($sql->mysqry)) $_cadeiras[$x->id]=$x;

		$_profissionais=array();
		$sql->consult($_p."colaboradores","id,nome,calendario_iniciais,foto,calendario_cor","where tipo_cro<>'' and lixo=0 order by nome asc");
		while($x=mysqli_fetch_object($sql->mysqry)) $_profissionais[$x->id]=$x;

		$_agendaStatus=array('confirmado'=>'CONFIRMADO','agendado'=>'AGENDADO');
		//  right:'dayGridMonth,resourceTimeGridOneDay,resourceTimeGridFiveDay,resourceTimeGridSevenDay'
		$_views=array("dayGridMonth"=>"MÊS",
						"resourceTimeGridOneDay"=>"1 dia",
						"resourceTimeGridFiveDay"=>"5 dias",
						"resourceTimeGridSevenDay"=>"7 dias");

		$_page=basename($_SERVER['PHP_SELF']);

		if(isset($agendaConfirmacao)) {

		} else {
	?>
	<section class="filtros">
		<div class="filter-group">
			<div class="filter-button">
				<a href="box/boxAgendamento.php?id_unidade=1" data-fancybox data-type="ajax" data-height="300" data-padding="0" class="verde adicionar tooltip" title="adicionar"><i class="iconify" data-icon="bx-bx-plus"></i><span>Agendamento</span></a>
			</div>
		</div>

		<div class="filter-group filter-group_right">
			<div class="filter-links">
				<a href="pg_agenda.php" data-status="APROVADO" class="js-btn-status<?php echo basename($_SERVER['PHP_SELF'])=="pg_agenda.php"?" active":"";?>">Agenda</a>
				<a href="pg_agenda_kanban.php" data-status="APROVADO" class="js-btn-status<?php echo basename($_SERVER['PHP_SELF'])=="pg_agenda_kanban.php"?" active":"";?>">Kanban</a>
			</div>
		</div>

		<form method="get" class="formulario-validacao js-filtro form agenda-filtros">
			<div class="agenda-filtros__inner1">
				<input type="hidden" name="csv" value="0" />					
				<a href="javascript:;" class="button button__alt js-today">HOJE</a>
				<a href="javascript:;" class="button button__empty js-left"><i class="iconify" data-icon="entypo-chevron-thin-left"></i></a>
				<a href="javascript:;" class="button button__empty js-right"><i class="iconify" data-icon="entypo-chevron-thin-right"></i></a>
				
				<div class="js-calendario">
					<span class="iconify" data-icon="bi:calendar-week" data-inline="false" data-width="20"></span>
				</div>

				<input class="js-calendario-title noupper" readonly="" />
				<?php
				if(basename($_SERVER['PHP_SELF'])=="pg_agenda.php") {
				?>
					<select class="js-view" class="chosenWithoutFind">
						<?php
						foreach($_views as $k=>$v) {
							echo '<option value="'.$k.'"'.($k=="resourceTimeGridOneDay"?' selected':'').'>'.$v.'</option>';
						}
						?>
					</select>
				<?php
				}
				?>
			</div>
			<div class="agenda-filtros__inner2">
				<select name="id_cadeira" class="js-cadeira custom-select">
					<option value="">Consultório</option>
					<?php
					$_cadeirasJSON=array();
					foreach($_cadeiras as $v) {
						if(!(isset($values['id_cadeira']) and isset($_cadeiras[$values['id_cadeira']]) and $values['id_cadeira']!=$v->id)) {
							$_cadeirasJSON[]=array('ordem'=>$v->ordem,'id'=>$v->id,'title'=>utf8_encode($v->titulo));
						}
						echo '<option value="'.$v->id.'"'.((isset($values['id_cadeira']) and $values['id_cadeira']==$v->id)?' selected':'').'>'.utf8_encode($v->titulo).'</option>';
					}
					?>
				</select>
				<select name="id_profissional" class="js-profissionais">
					<option value="">Profissionais</option>
					<?php
					foreach($_profissionais as $v) {
						echo '<option value="'.$v->id.'"'.((isset($values['id_profissional']) and $values['id_profissional']==$v->id)?' selected':'').'>'.utf8_encode($v->nome).'</option>';
					}
					?>
				</select>
				<?php /*<select name="id_status" class="js-status chosenWithoutFind">
					<option value="">Status</option>
					<?php
					foreach($_status as $v) {
						echo '<option value="'.$v->id.'"'.((isset($values['id_status']) and $values['id_status']==$v->id)?' selected':'').'>'.utf8_encode($v->titulo).'</option>';
					}
					?>
				</select>*/?>
			</div>
		</form>
	</section>
	<?php
	}
	?>