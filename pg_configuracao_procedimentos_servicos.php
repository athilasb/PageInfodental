<?php
	$title="";
	include "includes/header.php";
	include "includes/nav.php";

	if($usr->tipo!="admin" and !in_array("clientes",$_usuariosPermissoes)) {
		$jsc->jAlert("Você não tem permissão para acessar esta área!","erro","document.location.href='dashboard.php'");
		die();
	}
	$values=$adm->get($_GET);
?>
<section class="content">

	<?php
	require_once("includes/abaConfiguracao.php");
	?>

	<?php
	$_table=$_p."landingpage_temas";
	$_page=basename($_SERVER['PHP_SELF']);

	$_regioes=array();
	$sql->consult($_p."parametros_procedimentos_regioes","*","where lixo=0 order by titulo asc");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_regioes[$x->id]=$x;
	}

	$_especialidades=array();
	$sql->consult($_p."parametros_especialidades","*","where lixo=0 order by titulo asc");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_especialidades[$x->id]=$x;
	}
	?>

		<section class="grid grid_2">

			<script type="text/javascript">
				var procedimentos = [];

				const popViewProcedimento = (obj) => {

					index=$(obj).index();
					$('#cal-popup-procedimento')
							.removeClass('cal-popup_left')
							.removeClass('cal-popup_right')
							.removeClass('cal-popup_bottom')
							.removeClass('cal-popup_top');

					let clickTop=obj.getBoundingClientRect().top+window.scrollY;
				
					let clickLeft=Math.round(obj.getBoundingClientRect().left);
					let clickMargin=Math.round(obj.getBoundingClientRect().width/2);
					$(obj).prev('.cal-popup')
							.removeClass('cal-popup_left')
							.removeClass('cal-popup_right')
							.removeClass('cal-popup_bottom')
							.removeClass('cal-popup_top');

					let popClass='cal-popup_top';
					$('#cal-popup-procedimento').addClass(popClass).toggle();
					$('#cal-popup-procedimento').css({'top':clickTop,'left':clickLeft,'margin-left': clickMargin});
					$('#cal-popup-procedimento').show();

					$('#cal-popup-procedimento .js-planos tr').remove();

					if(procedimentos[index].planos && procedimentos[index].planos.length>0) {
						procedimentos[index].planos.forEach(x=> {
							$('.js-planos').append(`<tr>
														<td>${x.plano}</td>
														<td>${number_format(x.valor,2,",",".")}</td>
														<td>${number_format(x.custo,2,",",".")}</td>
														<td>${number_format(x.comissionamento,2,",",".")}</td>
													</tr>`);
							});
					} else {
						$('.js-planos').append(`<tr><td colspan="5"><center>Nehnum procedimento</center></td></tr>`);
					}

					$('#cal-popup-procedimento .js-titulo').html(procedimentos[index].titulo);
					$('#cal-popup-procedimento .js-regiao').html(`Região: ${procedimentos[index].regiao}`);
					$('#cal-popup-procedimento .js-hrefProcedimento').attr('href',`box/boxProcedimentos.php?id_procedimento=${procedimentos[index].id}`);
					$('#cal-popup-procedimento .js-index').val(index);
				}

				$(function(){
					$('.js-btn-fechar').click(function(){
						$('.cal-popup').hide();
					});
					$(document).mouseup(function(e)  {
					    var container = $("#cal-popup-procedimento");
					    // if the target of the click isn't the container nor a descendant of the container
					    if (!container.is(e.target) && container.has(e.target).length === 0) 
					    {
					       $('#cal-popup-procedimento').hide();
					    }
					});
					$('#cal-popup-procedimento').on('click','.js-btn-procedimento',function(){
						let id_procedimento = $(this).attr('data-id_procedimento');
						$.fancybox.open({
							type:`ajax`,
							src:`box/boxProcedimentos.php?id_procedimento=${id_procedimento}`
						});
						return false;
					});
				});
			</script>
			<section class="grid">
				<div class="box">
					<div class="filter">

						<div class="filter-group">
							<div class="filter-button">
								<a href="box/boxProcedimentos.php" data-fancybox data-type="ajax" data-height="300" data-padding="0" class="verde adicionar tooltip" title="adicionar"><i class="iconify" data-icon="bx-bx-plus"></i><span>Procedimento</span></a>
							</div>
						</div>

						<div class="filter-group filter-group_right">
							<form method="get" class="filter-form">
								<dl>
									<dd><input type="text" name="busca" value="<?php echo isset($values['busca'])?$values['busca']:"";?>" placeholder="" style="width:235px;" class="noupper" /></dd>
								</dl>
								<button type="submit"><i class="iconify" data-icon="bx-bx-search"></i></button>
							</form>
						</div>

					</div>

					<?php
					$where="WHERE lixo='0'";
					if(isset($values['busca']) and !empty($values['busca'])) $where.=" and (titulo like '%".utf8_decode($values['busca'])."%')";
					
					//echo $where;

					?>
					<div class="reg">
						<?php
						$registros = array();
						$sql->consultPagMto2($_p."parametros_procedimentos","*",10,$where,"",15,"pagina",$_page."?".$url."&pagina=");
						if($sql->rows==0) {
							$msgSemResultado="Nenhum Procedimento";
							if(isset($values['busca'])) $msgSemResultado="Nenhuma Procedimento encontrado";

							echo "<center>$msgSemResultado</center>";
						} else {
							while($x=mysqli_fetch_object($sql->mysqry)) {
								$registros[]=$x;
						?>
						<a href="javascript:;" class="reg-group" onclick="popViewProcedimento(this);">
							<div class="reg-color" style="background-color:green;"></div>
							<div class="reg-data" style="flex:0 1 50%;">
								<h1><?php echo strtoupperWLIB(utf8_encode($x->titulo));?></h1>
								<p>
									<?php echo isset($_regioes[$x->id_regiao])?utf8_encode($_regioes[$x->id_regiao]->titulo):"-";?>
								</p>
							</div>
							<div class="reg-data" style="flex:0 1 100px;">
								<p>
									<?php
									if(isset($_especialidades[$x->id_especialidade])) echo utf8_encode($_especialidades[$x->id_especialidade]->titulo);
									?>
								</p>
							</div>
							
						</a>
						<?php
							}

							if(isset($sql->myspaginacao) and !empty($sql->myspaginacao)) {
							?>	
						<div class="paginacao" style="margin-top: 30px;">
							<p class="paginacao__item"><span>Página</span><?php echo $sql->myspaginacao;?></p>
						</div>
							<?php
							}
						}
						?>
					</div>

				</div>
			</section>

			<?php
				$procedimentosJSON=array();
				$_planos=array();
				$_planosSel=array();
				$sql->consult($_p."parametros_planos","*","where lixo=0 order by titulo asc");
				while($x=mysqli_fetch_object($sql->mysqry)) {
					$_planos[$x->id]=$x;
				}
				foreach($registros as $x) {
					$sql->consult($_p."parametros_procedimentos_planos","*","where id_procedimento=$x->id and lixo=0 order by id desc");
					$planos=array();

					while($y=mysqli_fetch_object($sql->mysqry)) {
						$plano=isset($_planos[$y->id_plano])?utf8_encode($_planos[$y->id_plano]->titulo):"-";

						$planos[]=array('id'=>$y->id,
										'plano'=>$plano,
										'valor'=>$y->valor,
										'comissionamento'=>$y->comissionamento,
										'custo'=>$y->custo);
					}

					$item=array(
						'id' 	 => $x->id,
						'titulo' => utf8_encode($x->titulo),
						'regiao' => isset($_regioes[$x->id_regiao])?utf8_encode($_regioes[$x->id_regiao]->titulo):"",
						'planos' => $planos
					);
					$procedimentosJSON[]=$item;
				}
			?>

			<section id="cal-popup-procedimento" class="cal-popup cal-popup_paciente cal-popup_top cal-popup_alt" style="left:703px; top:338px; margin-left:303px;display: none">
				<a href="javascript:;" class="cal-popup__fechar js-btn-fechar"><i class="iconify" data-icon="mdi-close"></i></a>
				<section class="paciente-info">
					<header class="paciente-info-header">
						<section class="paciente-info-header__inner1">
							<h1 class="js-titulo">Procedimento</h1>
							<p style="color:var(--cinza4);"><span style="color:var(--cinza4);" class="js-opcaoEQtd"></span><span class="js-regiao">Região</span> </p>
							
						</section>
					</header>
					<input type="hidden" class="js-index" />

					<div class="abasPopover">
						<a href="javascript:;" onclick="$(this).parent().parent().find('a').removeClass('active');$(this).parent().parent().find('.js-grid').hide();$(this).parent().parent().find('.js-grid-info').show();$(this).addClass('active');" class="active">Informações</a>
					</div>

					<div class="paciente-info-grid js-grid js-grid-planos registros" style="font-size: 12px;">		
						
						<table style="grid-column:span 2;">
							<thead>
								<tr>
									<th>Plano</th>
									<th>Valor</th>
									<th>Custo</th>
									<th>Comissionamento</th>
								</tr>
							</thead>
							<tbody class="js-planos">
							</tbody>
						</table>
					</div>

					<div class="paciente-info-opcoes">
						<a href="javascript:;" data-fancybox="" data-type="ajax" data-padding="0" class="js-hrefProcedimento button" onclick="$('.cal-popup').hide();" style="margin-left: 230px;">Editar</a>
						<a href="javascript:;" class="button button__sec">Excluir</a>
					</div>
				
				</section>
				<script type="text/javascript">
					procedimentos = JSON.parse(`<?php echo json_encode($procedimentosJSON);?>`);
				</script>
    		</section>


			<section class="grid">

				<script type="text/javascript">
					var labs = [];

					const popViewServico = (obj) => {

						index=$(obj).index();
						$('#cal-popup-servico')
								.removeClass('cal-popup_left')
								.removeClass('cal-popup_right')
								.removeClass('cal-popup_bottom')
								.removeClass('cal-popup_top');

						let clickTop=obj.getBoundingClientRect().top+window.scrollY;
					
						let clickLeft=Math.round(obj.getBoundingClientRect().left);
						let clickMargin=Math.round(obj.getBoundingClientRect().width/2);
						$(obj).prev('.cal-popup')
								.removeClass('cal-popup_left')
								.removeClass('cal-popup_right')
								.removeClass('cal-popup_bottom')
								.removeClass('cal-popup_top');

						let popClass='cal-popup_top';
						$('#cal-popup-servico').addClass(popClass).toggle();
						$('#cal-popup-servico').css({'top':clickTop,'left':clickLeft,'margin-left': clickMargin});
						$('#cal-popup-servico').show();

						$('#cal-popup-servico .js-servicos tr').remove();

						if(labs[index].servicos && labs[index].servicos.length>0) {
							labs[index].servicos.forEach(x=> {
								$('.js-servicos').append(`<tr>
															<td>${x.servico}</td>
															<td>${number_format(x.valor,2,",",".")}</td>
														</tr>`);
								});
						} else {
							$('.js-servicos').append(`<tr><td colspan="5"><center>Nenhum serviço de laboratório</center></td></tr>`);
						}

						$('#cal-popup-servico .js-titulo').html(labs[index].titulo);
						$('#cal-popup-servico .js-regiao').html(`Região: ${labs[index].regiao}`);
						$('#cal-popup-servico .js-hrefServico').attr('href',`box/boxServicos.php?id_servico=${labs[index].id}`);
						$('#cal-popup-servico .js-index').val(index);
					}

					$(function(){
						$('.js-btn-fechar').click(function(){
							$('.cal-popup').hide();
						});
						$(document).mouseup(function(e)  {
						    var container = $("#cal-popup-servico");
						    if (!container.is(e.target) && container.has(e.target).length === 0) {
						       $('#cal-popup-servico').hide();
						    }
						});
						$('#cal-popup-servico').on('click','.js-btn-servico',function(){
							let id_servico = $(this).attr('data-id_servico');
							$.fancybox.open({
								type:`ajax`,
								src:`box/boxServicos.php?id_servico=${id_servico}`
							});
							return false;
						});
					});
				</script>
				<div class="box">

					<div class="filter">

						<div class="filter-group">
							<div class="filter-button">
								<a href="box/boxServicos.php" data-fancybox data-type="ajax" data-height="300" data-padding="0" class="verde adicionar tooltip" title="adicionar"><i class="iconify" data-icon="bx-bx-plus"></i><span>Trabalho Protético</span></a>
							</div>
						</div>

						<div class="filter-group filter-group_right">
							<form method="get" class="filter-form">
								<dl>
									<dd><input type="text" name="buscaServico" value="<?php echo isset($values['buscaServico'])?$values['buscaServico']:"";?>" placeholder="" style="width:235px;" class="noupper" /></dd>
								</dl>
								<button type="submit"><i class="iconify" data-icon="bx-bx-search"></i></button>
							</form>
						</div>

					</div>

					<?php
					$_tipos = array(
						'porcelona' => 'PORCELANA',
						'resina'    => 'RESINA'
					);
					$where="WHERE lixo='0'";
					if(isset($values['buscaServico']) and !empty($values['buscaServico'])) $where.=" and (titulo like '%".utf8_decode($values['buscaServico'])."%')";

					?>
					<div class="reg">
						<?php
						$registros = array();
						$sql->consultPagMto2($_p."parametros_servicosdelaboratorio","*",10,$where,"",15,"pagina",$_page."?".$url."&pagina=");
						if($sql->rows==0) {
							$msgSemResultado="Nenhum Trabalho Protético";
							if(isset($values['busca'])) $msgSemResultado="Nenhum Trabalho Protético encontrado";

							echo "<center>$msgSemResultado</center>";
						} else {
							while($x=mysqli_fetch_object($sql->mysqry)) {
								$registros[]=$x;
						?>
						<a href="javascript:;" class="reg-group" onclick="popViewServico(this);">
							<div class="reg-color" style="background-color:green;"></div>
							<div class="reg-data" style="flex:0 1 50%;">
								<h1><?php echo strtoupperWLIB(utf8_encode($x->titulo));?></h1>
								<p>
									<?php echo isset($_regioes[$x->id_regiao])?utf8_encode($_regioes[$x->id_regiao]->titulo):"-";?>
								</p>
							</div>
							<div class="reg-data" style="flex:0 1 100px;">
								<p><?php echo isset($_tipos[$x->tipo_material])?$_tipos[$x->tipo_material]:"";?>
								</p>
							</div>
						</a>
						<?php
							}
							if(isset($sql->myspaginacao) and !empty($sql->myspaginacao)) {
							?>	
						<div class="paginacao" style="margin-top: 30px;">
							<p class="paginacao__item"><span>Página</span><?php echo $sql->myspaginacao;?></p>
						</div>
							<?php
							}
						}
						?>
					</div>
					
				</div>
			</section>
			<?php
				$laboratoriosJSON=array();
				$_servicos=array();
				$_servicosSel=array();
				$sql->consult($_p."parametros_fornecedores","*","where lixo=0 order by nome asc, razao_social");
				while($x=mysqli_fetch_object($sql->mysqry)) {
					$_servicos[$x->id]=$x;
				}
				foreach($registros as $x) {
					$sql->consult($_p."parametros_servicosdelaboratorio_laboratorios","*","where id_servicodelaboratorio=$x->id and lixo=0 order by id desc");
					$servicos=array();

					while($y=mysqli_fetch_object($sql->mysqry)) {
						$servico=isset($_servicos[$y->id_fornecedor])?utf8_encode($_servicos[$y->id_fornecedor]->razao_social):"-";

						$servicos[]= array('id'=>$y->id,
										'servico'=>$servico,
										'valor'=>$y->valor);
					}

					$item=array(
						'id' 	 => $x->id,
						'titulo' => utf8_encode($x->titulo),
						'regiao' => isset($_regioes[$x->id_regiao])?utf8_encode($_regioes[$x->id_regiao]->titulo):"",
						'servicos' => $servicos
					);
					$laboratoriosJSON[]=$item;
				}
			?>
			<section id="cal-popup-servico" class="cal-popup cal-popup_paciente cal-popup_top cal-popup_alt" style="left:703px; top:338px; margin-left:303px;display: none">
				<a href="javascript:;" class="cal-popup__fechar js-btn-fechar"><i class="iconify" data-icon="mdi-close"></i></a>
				<section class="paciente-info">
					<header class="paciente-info-header">
						<section class="paciente-info-header__inner1">
							<h1 class="js-titulo">Serviço</h1>
							<p style="color:var(--cinza4);"><span style="color:var(--cinza4);" class="js-opcaoEQtd"></span><span class="js-regiao">Região</span> </p>
							
						</section>
					</header>
					<input type="hidden" class="js-index" />

					<div class="abasPopover">
						<a href="javascript:;" onclick="$(this).parent().parent().find('a').removeClass('active');$(this).parent().parent().find('.js-grid').hide();$(this).parent().parent().find('.js-grid-info').show();$(this).addClass('active');" class="active">Informações</a>
					</div>

					<div class="paciente-info-grid js-grid js-grid-planos registros" style="font-size: 12px;">		
						
						<table style="grid-column:span 2;">
							<thead>
								<tr>
									<th>Laboratório</th>
									<th>Valor</th>
								</tr>
							</thead>
							<tbody class="js-servicos">
							</tbody>
						</table>
					</div>

					<div class="paciente-info-opcoes">
						<a href="javascript:;" data-fancybox="" data-type="ajax" data-padding="0" class="js-hrefServico button" onclick="$('.cal-popup').hide();" style="margin-left: 230px;">Editar</a>
						<a href="javascript:;" class="button button__sec">Excluir</a>
					</div>
				
				</section>
				<script type="text/javascript">
					labs = JSON.parse(`<?php echo json_encode($laboratoriosJSON);?>`);
				</script>
    		</section>
		
		</section>

</section>

<?php
	include "includes/footer.php";
?>