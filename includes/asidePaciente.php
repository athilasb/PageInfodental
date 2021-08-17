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
				<a href="box/boxAgendamento.php?id_unidade=1" data-fancybox data-type="ajax" data-height="300" data-padding="0" class="verde adicionar tooltip" title="adicionar"><span>Novo Paciente</span></a>
			</div>
		</div>

		<div class="filter-group filter-group_right" >
			<div class="filter-links">
				<a href="pg_contatos_pacientes.php" data-status="APROVADO" class="js-btn-status<?php echo basename($_SERVER['PHP_SELF'])=="pg_contatos_pacientes.php"?" active":"";?>">Lista</a>
				<a href="pg_contatos_pacientes_kanban.php" data-status="APROVADO" class="js-btn-status<?php echo basename($_SERVER['PHP_SELF'])=="pg_contatos_pacientes_kanban.php"?" active":"";?>">Kanban</a>
			</div>
		</div>

		<div class="filter-group filter-group_right">
				<form method="get" class="filter-form">
					<input type="hidden" name="csv" value="0" />
					<dl>
						<dd><input type="text" name="busca" value="<?php echo isset($values['busca'])?$values['busca']:"";?>" placeholder="" style="width:250px;" class="noupper" /></dd>
					</dl>
					<button type="submit"><i class="iconify" data-icon="bx-bx-search"></i></button>
				</form>
			</div>
	</section>
	<?php
	}
	?>