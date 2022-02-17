<section class="nav2">
		<?php
		if($_infodentalCompleto==1) {
			$_menuDash=array(
					'agenda_confirmacoes'=>array('page'=>'pg_agenda_confirmacoes.php',
													'titulo'=>'Confirmações',
													'icone'=>'<span class="iconify" data-icon="akar-icons:check"></span>'),

					'funil_leads'=>array('page'=>'pg_funil_leads.php',
											'titulo'=>'Funil de Leads',
											'icone'=>'<span class="iconify" data-icon="la:funnel-dollar"></span>'),
					'financeiro'=>array('page'=>'pg_financeiro_kanban.php',
											'titulo'=>'Financeiro',
											'icone'=>'<span class="iconify" data-icon="ic:baseline-attach-money" data-inline="false"></span>'),
			   );
			foreach ($_menuDash as $v) {
				// code...
			
			?>
				<a href="<?php echo $v['page'];?>" class="<?php echo basename($_SERVER['PHP_SELF'])==$v['page']?"active ":"";?>tooltip" title="<?php echo $v['titulo'];?>"><?php echo $v['icone'];?></a>
			<?php
			}
		}
		?>
		
	</section>