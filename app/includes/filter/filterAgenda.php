<section class="filter">
				
	<div class="filter-group">
		<div class="filter-form form">

			<?php
			if($_page=="pg_agenda.php") {
			?>
			<dl>
				<dd><a href="javascript:;" class="button button_main js-novoAgendamento"><i class="iconify" data-icon="fluent:add-circle-24-regular"></i> <span>Novo Agendamento</span></a></dd>
			</dl>
			<?php
			}
			?>
		</div>
	</div>
	<?php
	if($_page=="pg_agenda.php") {
	?>
	<div class="filter-group" style="margin-left:auto; margin-right:0;">

		<div class="button-group">
			<?php
			foreach($_views as $k=>$v) {
				echo '<a href="javascript:;" data-view="'.$k.'" class="button js-view-a'.($k=="resourceTimeGridOneDay"?' active':'').'"><span>'.$v.'</a>';
			}
			?>
		</div>
	</div>
	<?php
	}
	?>

	<div class="filter-group">
		<div class="filter-form form">
			<dl style="width:160px;">
				<dd>
					<select name="id_cadeira" class="js-cadeira">
						<option value="">Consultório...</option>
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
				</dd>
			</dl>
			<dl style="width:160px;">
				<dd>
					<select name="id_profissional" class="js-profissionais">
						<option value="">Profissional...</option>
						<?php
						foreach($_profissionais as $v) {
							if($v->check_agendamento==0) continue;
							echo '<option value="'.$v->id.'"'.((isset($values['id_profissional']) and $values['id_profissional']==$v->id)?' selected':'').'>'.utf8_encode($v->nome).'</option>';
						}
						?>
					</select>
				</dd>
			</dl>	
			<dl>
				<dd>
					<a href="javascript:;" class="button js-calendario"><span class="iconify" data-icon="bi:calendar-week" data-inline="false" data-width="20"></span></a>	
					<a href="javascript:;" class="button active js-today">hoje</a>	
					<a href="javascript:;" class="button js-left"><i class="iconify" data-icon="fluent:arrow-left-24-filled"></i></a>	
					<a href="javascript:;" class="button js-right"><i class="iconify" data-icon="fluent:arrow-right-24-filled"></i></a>	
				</dd>
			</dl>						
		</div>					
	</div>

</section>