	<?php


	$_menu=array('dashboard'=>array('page'=>'tarefas-inteligentes.php',
									'title'=>'Tarefas Inteligêntes',
									'icon'=>'<i class="iconify" data-icon="fluent:lightbulb-filament-20-regular"></i>'),
						'agenda'=>array('page'=>'pg_agenda.php',
										'title'=>'Agenda',
										'icon'=>'<i class="iconify" data-icon="fluent:calendar-ltr-20-regular"></i>'),
						'pacientes'=>array('page'=>'pg_pacientes.php',
											'title'=>'Pacientes',
											'icon'=>'<i class="iconify" data-icon="fluent:share-screen-person-overlay-20-regular"></i>'),
						'financeiro'=>array('page'=>'pg_financeiro.php',
											'title'=>'Financeiro',
											'icon'=>'<i class="iconify" data-icon="fluent:data-trending-20-regular"></i>'),
						'ladingpage'=>array('page'=>'pg_landingpage.php',
											'title'=>'Landing Page',
											'icon'=>'<i class="iconify" data-icon="fluent:web-asset-24-regular"></i>'),
						'configuracoes'=>array('page'=>'pg_configuracoes_clinica_colaboradores.php',
												'pages'=>explode(",","pg,pg_configuracoes_clinica.php,pg_configuracoes_clinica_colaboradores.php,pg_configuracoes_clinica_cadeiras.php,pg_configuracoes_evolucao_anamnese.php,pg_configuracoes_evolucao_procedimentos.php,pg_configuracoes_evolucao_servicosdelaboratorio.php,pg_configuracoes_evolucao_examecomplementar.php,pg_configuracoes_fornecedores.php,pg_configuracoes_fornecedores_produtos.php,pg_configuracoes_financeiro_bancosecontas.php,pg_configuracoes_financeiro_cartoes.php,"),
												'title'=>'Configurações',
												'icon'=>'<i class="iconify" data-icon="fluent:settings-20-regular"></i>'),
						
				   );
	?>
	<section class="nav">
		<div class="nav-header">
			<a href="dashboard.php"><img src="img/logo-reduzido.svg" alt="" width="30" height="28" /></a>
		</div>
		<div class="nav-buttons">
			<?php
			foreach($_menu as $session=>$params) {
			?>
			<a href="<?php echo $params['page'];?>" class="<?php echo in_array(basename($_SERVER['PHP_SELF']),isset($params['pages'])?$params['pages']:array())?" active":"";?>"><?php echo $params['icon'];?></a>
			<?php
			}


			$_dirFoto=$_cloudinaryPath."arqs/colaboradores/";
			$ft="img/ilustra-usuario.jpg";

			if(!empty($usr->foto)) {
				$ft=$_cloudinaryURL.',w_50/'.$usr->foto;
			}
			?>
			<a href="javascript:;" class="nav-buttons__usuario"><img src="<?php echo $ft;?>" alt="" width="40" height="40" /></a>
			<a href="usuarios/sair.php"><i class="iconify" data-icon="fluent:door-arrow-right-20-regular"></i></a>
		</div>

	</section>