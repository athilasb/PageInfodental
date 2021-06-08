<?php
	$urlTema='';
	if(isset($_GET['id_tema']) and is_numeric($_GET['id_tema'])) $urlTema="?id_tema=".$_GET['id_tema'];
	$_menu=array('dashboard'=>array('dashboard.php'=>'Dashboard','icone'=>'<i class="iconify" data-icon="ic-outline-dashboard"></i>'),
					'agenda'=>array('pg_agenda.php'=>'Agenda','icone'=>'<i class="iconify" data-icon="ic-outline-date-range"></i>'),
					/*'contatos'=>array('titulo'=>'Contatos','icone'=>'<i class="iconify" data-icon="ic-baseline-person"></i>','submenu'=>array(
																		array('pg_contatos_pacientes.php'=>'Pacientes'),
																		array('javascipt:;'=>'Prospect')
																	),
					),*/

					'pacientes'=>array('pg_contatos_pacientes.php'=>'Pacientes','icone'=>'<i class="iconify" data-icon="ic-baseline-person"></i>','titulo'=>'Pacientes'),
					'colaboradores'=>array('pg_colaboradores.php'=>'Colaboradores','icone'=>'<span class="iconify" data-icon="gridicons-multiple-users" data-inline="false"></span>','titulo'=>'Colaboradores'),
					'indicacoes'=>array('pg_parametros_indicacoes.php'=>'Indicações','icone'=>'<i class="iconify" data-icon="ic-outline-rocket"></i>','titulo'=>'Indicações'),
					/*'estacionamento'=>array('pg_estacionamento.php'=>'Estacionamento', 'titulo' => 'Estacionamento'),
					'caixa'=>array('pg_caixa.php'=>'Caixa', 'titulo' => 'Caixa'),*/
					/*'parametros'=>array('titulo'=>'Personalização','icone'=>'<i class="iconify" data-icon="clarity:settings-solid"></i>','submenu'=>array(
																		//array('pg_parametros_profissoes.php'=>'Profissões'),
																		array('pg_parametros_procedimentos.php'=>'Procedimentos'),
																		array('pg_parametros_planos.php'=>'Planos'),
																		array('pg_parametros_fases.php'=>'Fases'),
																		array('pg_parametros_camposEvolucao.php'=>'Campos de Evolução'),
																		array('pg_profissionais.php'=>'Profissionais'),
																		array('pg_cadeiras.php'=>'Cadeiras'),
																		//array('pg_veiculos_modelos.php'=>'Modelos'),
																	),
					),*/
					'landingpage'=>array('titulo'=>'Landing Page','icone'=>'<i class="iconify" data-icon="mdi:web"></i>','submenu'=>array(
																		array('pg_landingpage_temas.php'.$urlTema=>'Temas'),
																		array('pg_landingpage_banner.php'.$urlTema=>'Banner'),
																		array('pg_landingpage_informacoes.php'.$urlTema=>'Informações'),
																		array('pg_landingpage_captacao.php'.$urlTema=>'Captação'),
																		array('pg_landingpage_antesedepois.php'.$urlTema=>'Antes e Depois'),
																		array('pg_landingpage_depoimentos.php'.$urlTema=>'Depoimentos'),
																		array('pg_landingpage_sobreaclinica.php'.$urlTema=>'Sobre a Clínica'),
																		array('pg_landingpage_formulario.php'.$urlTema=>'Formulário')
																	),
					),
					'landingpages'=>array('pg_landingpages.php'=>'Landing Pages','icone'=>'<span class="iconify" data-icon="ri:pages-fill" data-inline="false"></span>','titulo'=>'Landing Pages'),
					'whatsapp'=>array('pg_whatsapp.php'=>'Whatsapp','icone'=>'<i class="iconify" data-icon="cib:whatsapp"></i>','titulo'=>'Whatsapp'),
					//'usuarios'=>array('usuarios.php'=>'Usuários','icone'=>'<i class="iconify" data-icon="cib:open-access"></i>','titulo'=>'Usuários')
			   );
?>
<section class="wrapper">

	<header class="header">
		
		<section class="header-logo">
			<img src="img/logo.svg" width="32" height="30" alt="Info Dental" class="header-logo__img" />
		</section>

		<section class="header-cliente">
			<img src="img/logo-cliente.png" alt="" width="270" height="98" class="header-cliente__img" />
		</section>

		<section class="header-controles">
			
			<select name="unidade" class="header-controles__select">
				<option value="">STUDIO DENTAL - OESTE</option>
				<option value="">STUDIO DENTAL - GYN SHOP</option>
			</select>
			<a href="pg_configuracao_anamnese_exames.php" class="header-controles__config"><i class="iconify" data-icon="ic-baseline-settings"></i></a>
			<a href="" class="header-controles__usuario"><img src="img/ilustra-perfil.png" alt="" width="120" height="120" /></a>
		</section>

	</header>

	<script>
		$(function() {
			$(".nav__item").hover(function() {
				$(this).children('a').addClass('hover');
				$(this).children('.nav-sub').show();
			}, function() {
				$(this).children('a').removeClass('hover');
				$(this).children('.nav-sub').hide();
			});
		});
	</script>

	<main class="main">
		<nav class="nav">
			<ul>
			<?php
			foreach($_menu as $permissao=>$v) {
				if($usr->tipo!="admin" and !in_array($permissao,$_usuariosPermissoes)) continue;
				
				if(!isset($v['submenu'])) {
					foreach($v as $k=>$n) {
			?>
				<li class="nav__item"><a href="<?php echo $k;?>"><?php echo $v['icone'];?></a></li>
			<?php		
						break;
					}
				} else {
			?>
				<li class="nav__item"><a href="javscript:;" class="nav__expande m-<?php echo $permissao;?>"><?php echo $v['icone'];?></a>
					<ul class="nav-sub">
					<?php
					foreach($v['submenu'] as $s) {
						foreach($s as $k=>$n) {
					?>
					<li><a href="<?php echo $k;?>"><?php echo $n;?></a></li>
					<?php
						}
					}
					?>
					</ul>
				</li>
			<?php			
				}
			}
			?>
			</ul>
			<?php /*<ul>
				<li class="nav__item">
					<a href="javascript:;" class="nav__expande"><i class="iconify" data-icon="ic-outline-dashboard"></i></a>
					<ul class="nav-sub">
						<li><a href="modelo-registros.php">Painel Adm</a></li>
					</ul>
				</li>
				<li class="nav__item">
					<a href="javascript:;" class="nav__expande"><i class="iconify" data-icon="ic-outline-date-range"></i></a>
					<ul class="nav-sub">
						<li><a href="modelo-registros.php">Agenda</a></li>
					</ul>
				</li>
				<li class="nav__item">
					<a href="javascript:;" class="nav__expande"><i class="iconify" data-icon="ic-outline-check-circle-outline"></i></a>
					<ul class="nav-sub">
						<li><a href="modelo-registros.php">To Do List</a></li>
					</ul>
				</li>
				<li class="nav__item">
					<a href="javascript:;" class="nav__expande active"><i class="iconify" data-icon="ic-baseline-person"></i></a>
					<ul class="nav-sub">
						<li><span>Cadastros</span></li>
						<li><a href="modelo-registros.php">Pacientes</a></li>
						<li><a href="modelo-registros.php">Leads</a></li>
					</ul>
				</li>
				<li class="nav__item">
					<a href="javascript:;" class="nav__expande"><i class="iconify" data-icon="uil-flask"></i></a>
					<ul class="nav-sub">
						<li><a href="">Laboratório</a></li>
					</ul>
				</li>
				<li class="nav__item">
					<a href="javascript:;" class="nav__expande"><i class="iconify" data-icon="ic-baseline-attach-money"></i></a>
					<ul class="nav-sub">
						<li><a href="modelo-registros.php">Financeiro</a></li>
					</ul>
				</li>
				<li class="nav__item">
					<a href="javascript:;" class="nav__expande"><i class="iconify" data-icon="ic-outline-shopping-cart"></i></a>
					<ul class="nav-sub">
						<li><a href="modelo-registros.php">Compras</a></li>
					</ul>
				</li>
				<li class="nav__item">
					<a href="javascript:;" class="nav__expande"><i class="iconify" data-icon="ic-outline-supervised-user-circle"></i></a>
					<ul class="nav-sub">
						<li><a href="modelo-registros.php">RH</a></li>
					</ul>
				</li>
				<li class="nav__item">
					<a href="javascript:;" class="nav__expande"><i class="iconify" data-icon="ic-baseline-bar-chart"></i></a>
					<ul class="nav-sub">
						<li><a href="modelo-registros.php">Relatórios</a></li>
					</ul>
				</li>
				<li class="nav__item">
					<a href="javascript:;" class="nav__expande"><i class="iconify" data-icon="ic-outline-rocket"></i></a>
					<ul class="nav-sub">
						<li><span>Marketing</a></li>
						<li><a href="modelo-registros.php">Landing Page</a></li>
						<li><a href="modelo-registros.php">WhatsApp</a></li>
					</ul>
				</li>

			</ul>	*/?>
		</nav>