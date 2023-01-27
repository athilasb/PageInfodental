	<?php


	$_menu=array(/*'dashboard'=>array('page'=>'tarefas-inteligentes.php',
									'title'=>'Tarefas Inteligêntes',
									'icon'=>'<i class="iconify" data-icon="fluent:lightbulb-filament-20-regular"></i>'),*/
						'inteligencia'=>array('page'=>'pg_inteligencia.php',
												'pages'=>explode(",",'pg_inteligencia.php,pg_inteligencia_pacientes.php,pg_inteligencia_pacientesnovos.php,pg_inteligencia_controledeexames.php'),
												'title'=>'Dashboard',		
												'icon'=>'<i class="iconify" data-icon="fluent:lightbulb-filament-20-regular"></i>'),
						'agenda'=>array('page'=>'pg_agenda.php',
										'pages'=>explode(",","pg_agenda.php,pg_agenda_kanban.php"),
										'title'=>'Agenda',
										'icon'=>'<i class="iconify" data-icon="fluent:calendar-ltr-20-regular"></i>'),
						'pacientes'=>array('page'=>'pg_pacientes.php',
											'pages'=>explode(',','pg_pacientes.php,pg_pacientes_dadospessoais.php,pg_pacientes_resumo.php,pg_pacientes_kanban.php,pg_pacientes_prontuario.php'),
											'title'=>'Pacientes',
											'icon'=>'<i class="iconify" data-icon="fluent:share-screen-person-overlay-20-regular"></i>'),
						'analytics'=>array('page'=>'pg_inteligencia_analytics.php',
											'pages'=>explode(',','pg_inteligencia_analytics.php'),
											'title'=>'Pacientes',
											'icon'=>'<i class="iconify" data-icon="fluent:data-trending-16-filled"></i>'),
						/*'financeiro'=>array('page'=>'pg_financeiro.php',
											'title'=>'Financeiro',
											'icon'=>'<i class="iconify" data-icon="fluent:data-trending-20-regular"></i>'),
						'ladingpage'=>array('page'=>'pg_landingpage.php',
											'title'=>'Landing Page',
											'icon'=>'<i class="iconify" data-icon="fluent:web-asset-24-regular"></i>'),*/
						'landingpage'=>array('page'=>'pg_landingpage.php',
												'pages'=>explode(",","pg_landingpage.php,pg_landingpage_configuracao.php"),
												'title'=>'Landing Page',
												'icon'=>'<i class="iconify" data-icon="dashicons:admin-site-alt3"></i>'),
						'configuracoes'=>array('page'=>'pg_configuracoes_clinica_colaboradores.php',
												'pages'=>explode(",","pg_configuracoes_clinica.php,pg_configuracoes_clinica_colaboradores.php,pg_configuracoes_clinica_cadeiras.php,pg_configuracoes_evolucao_anamnese.php,pg_configuracoes_evolucao_procedimentos.php,pg_configuracoes_evolucao_servicosdelaboratorio.php,pg_configuracoes_evolucao_examecomplementar.php,pg_configuracoes_fornecedores.php,pg_configuracoes_fornecedores_produtos.php,pg_configuracoes_financeiro_bancosecontas.php,pg_configuracoes_financeiro_cartoes.php,pg_configuracoes_pagamentos.php,pg_configuracoes_evolucao_documentos.php"),
												'title'=>'Configurações',
												'icon'=>'<i class="iconify" data-icon="fluent:settings-20-regular"></i>'),
						'whatsapp'=>array('page'=>'pg_configuracoes_whatsapp.php',
												'pages'=>explode(",","pg_configuracoes_whatsapp.php,pg_configuracoes_whatsapp_pesquisadesatisfacao.php"),
												'title'=>'Whatsapp',
												'icon'=>'<i class="iconify" data-icon="la:whatsapp"></i>'),
						'financeiro'=>array('page'=>'pg_clinica_financeiro.php',
												'pages'=>explode(",","pg_clinica_financeiro.php"),
												'title'=>'Financeiro',
												'icon'=>'<i class="iconify" data-icon="tabler:pig-money"></i>')
						
				   );
	?>
	<section class="nav">
		<div class="nav-header">
			<a href="dashboard.php" class="nav-header__logo"><img src="img/logo-reduzido.svg" alt="" width="30" height="28" /></a>
			<a href="javascript:;" class="nav-header__menu" onclick="$('.nav-buttons').slideToggle('fast');"><i class="iconify" data-icon="fluent:navigation-24-filled"></i></a>
		</div>
		<div class="nav-buttons">
			<?php
			foreach($_menu as $session=>$params) {
				$spanWts='';
				if($session=="whatsapp") {
					if(is_object($_wts)) $spanWts='<span class="nav-buttons__indicator" style="background-color:var(--verde);"></span><span class="nav-buttons__legenda">WhatsApp (conectado)</span>';
					else $spanWts='<span class="nav-buttons__indicator" style="background-color:var(--vermelho);"></span><span class="nav-buttons__legenda">WhatsApp (desconectado)</span>';
				}
			?>
			<a href="<?php echo $params['page'];?>" class="<?php echo in_array(basename($_SERVER['PHP_SELF']),isset($params['pages'])?$params['pages']:array())?" active":"";?>"><?php echo $params['icon'];?><span class="nav-buttons__legenda"><?php echo $params['title'];?></span><?php echo $spanWts;?></a>
			<?php
			}


			$_dirFoto=$_cloudinaryPath."arqs/colaboradores/";
			$ft="img/ilustra-usuario.jpg";
			if(!empty($usr->foto)) {
				$ft=$_cloudinaryURL.',w_50/'.$usr->foto;
			}
			?>

			<a href="javascript:;" class="nav-buttons__usuario nav-buttons__whatsapp"><img src="<?php echo $ft;?>" alt="" width="40" height="40" /><span class="nav-buttons__legenda">Meus dados</span></a>
			<a href="usuarios/sair.php"><i class="iconify" data-icon="fluent:door-arrow-right-20-regular"></i><span class="nav-buttons__legenda">Sair</span></a>
		</div>

	</section>