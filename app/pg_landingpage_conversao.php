<?php
	include "includes/header.php";
	include "includes/nav.php";

	$_table=$_p."landingpage_conversao";

	$landingpage=$cnt='';
	if(isset($_GET['id_landingpage']) and is_numeric($_GET['id_landingpage'])) {
		$sql->consult($_p."landingpage_temas","*","where id='".addslashes($_GET['id_landingpage'])."'");
		if($sql->rows) {
			$landingpage=mysqli_fetch_object($sql->mysqry);
		}
	}

	if(empty($landingpage)) {
		$jsc->jAlert("Landing Page não encontrada!","erro","document.location.href='pg_landingpages.php'");
		die();
	}

	$sql->consult($_table,"*","WHERE id_tema='".$landingpage->id."' and lixo=0");
	if($sql->rows) {
		$cnt=mysqli_fetch_object($sql->mysqry);
	}

	// se nao encontrar registro
	if(empty($cnt)) {
		$sql->add($_table,"data=now(),id_usuario='".$usr->id."',id_tema='".$landingpage->id."'");
		$sql->consult($_table,"*","where id=$sql->ulid");
		$cnt=mysqli_fetch_object($sql->mysqry);
	}

	$campos=explode(",","teleconsulta_nome,teleconsulta_valor,teleconsulta_desconto,teleconsulta_mensagem,consultapresencial_nome,consultapresencial_valor,consultapresencial_desconto,consultapresencial_mensagem,id_tema");
	foreach($campos as $v) $values[$v]='';

	if(is_object($cnt)) {
		$values=$adm->values($campos,$cnt);
	}

	if(isset($_POST['acao'])) {
		$vSQL=$adm->vSQL($campos,$_POST);
		$values=$adm->values;

		$vSQL.="id_alteracao=$usr->id,alteracao_data=now()";
		$vWHERE="where id='".$cnt->id."'";
		$sql->update($_table,$vSQL,$vWHERE);
		$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='update',vsql='".addslashes($vSQL)."',vwhere='".addslashes($vWHERE)."',tabela='".$_table."',id_reg='".$cnt->id."'");

		$jsc->go($_page."?id_landingpage=".$landingpage->id);
		die();
	}
?>

	<header class="header">
		<div class="header__content content">

			<div class="header__inner1">
				<section class="header-title">
					<h1><?php echo utf8_encode($landingpage->titulo);?></h1>
					<p>studiodental.dental/<?php echo $landingpage->code;?></p>
				</section>
				<?php
				require_once("includes/menus/menuLandingPage.php");
				?>
			</div>
		</div>
	</header>

	<main class="main">
		<div class="main__content content">
			
			<section class="filter">
				<div class="filter-group">
					<div class="filter-title">
						<h1>Conversão</h1>
					</div>
				</div>
			</section>

			<section class="grid">

				<div class="box box-col">

					<?php
					require_once("includes/submenus/subLandingPage.php");
					?>
					<div class="box-col__inner1">

						<section class="filter">
							<div class="filter-group"></div>
							<div class="filter-group">
								<div class="filter-form form">
									<dl>
										<dd><a href="javascript:;" class="button button_main js-submit"><i class="iconify" data-icon="fluent:checkmark-12-filled"></i> <span>Salvar</span></a></dd>
									</dl>
								</div>
							</div>							
						</section>

						<form method="post" class="form formulario-validacao">
							<input type="hidden" name="acao" value="wlib" />
							<button style="display:none;"></button>

							<script type="text/javascript">
								$(function(){
									$('input.money').maskMoney({symbol:'', allowZero:true, showSymbol:true, thousands:'.', decimal:',', symbolStay: true});
								});
							</script>

							<div class="grid grid_2">
								<fieldset style="margin:0;">
									<legend>Teleconsulta</legend>

									<input type="hidden" name="teleconsulta_beneficios" value="<?php echo isset($values['teleconsulta_beneficios'])?$values['teleconsulta_beneficios']:'';?>" />
									<script>
										var teleconsultaBeneficios = [];

										const teleconsultaBeneficiosRemover = (index) => {
											teleconsultaBeneficios.splice(index,1);
											teleconsultaBeneficiosListar();
										};
										const teleconsultaBeneficiosListar = () => {
											$('.js-teleconsultaBeneficios tbody').html('');

											html = `<tr class="js-editar">
																	<td class="js-tr-titulo"><h1></h1></td>
																	<td style="text-align:right;"><a href="javascript:;" class="button js-tr-deleta"><i class="iconify" data-icon="fluent:delete-24-regular"></i></a></td>
																</tr>`;

											teleconsultaBeneficios.forEach(x => {
												
												$('.js-teleconsultaBeneficios').append(html);
												$('.js-teleconsultaBeneficios tbody .js-tr-titulo:last').html(x.titulo);
												$('.js-teleconsultaBeneficios tbody .js-tr-deleta:last').click(function() {
													let index = $(this).index('.js-teleconsultaBeneficios tbody .js-tr-deleta');
													swal({   title: "Atenção",   text: "Você tem certeza que deseja remover este registro?",   type: "warning",   showCancelButton: true,   confirmButtonColor: "#DD6B55",   confirmButtonText: "Sim!",   cancelButtonText: "Não",   closeOnConfirm: false,   closeOnCancel: false }, function(isConfirm){   if (isConfirm) {  teleconsultaBeneficiosRemover(index); swal.close();   } else {   swal.close();   } });
												});
											});

											let json = JSON.stringify(teleconsultaBeneficios);
											$('input[name=teleconsulta_beneficios]').val(json);
										}

										$(function(){
											<?php
											if(is_object($cnt) and !empty($cnt->teleconsulta_beneficios)) {
												echo "teleconsultaBeneficios=JSON.parse('".utf8_encode($cnt->teleconsulta_beneficios)."');";
												echo "teleconsultaBeneficiosListar();";
											} 
											?>
											

					      					$('.js-add-teleconsulta').click(function(){
					      						let beneficio = $('input.js-input-teleconsulta').val();
					      						
					      						if(beneficio.length==0) {
					      							swal({title: "Erro!", text: "Digite o Benefício!", type:"error", confirmButtonColor: "#424242"});
					      							$('input.js-input-teleconsulta').addClass('erro');
					      						} else {
					      							let item = {};
					      							item.titulo = beneficio;
					      							teleconsultaBeneficios.push(item);
					      							teleconsultaBeneficiosListar();
					      							$('input.js-input-teleconsulta').val('');
					      						}

					      					});
										});
									</script>

									<dl>
										<dt>Nome</dt>
										<dd>
											<input type="text" name="teleconsulta_nome" value="<?php echo $values['teleconsulta_nome'];?>" class="obg"/>
										</dd>
									</dl>
									<div class="colunas4">
										<dl class="dl2">
											<dt>Valor</dt>
											<dd>
												<input type="text" name="teleconsulta_valor" value="<?php echo $values['teleconsulta_valor'];?>" class="obg money"/>
											</dd>
										</dl>
										<dl class="dl2">
											<dt>Desconto Pagamento Online</dt>
											<dd>
												<input type="text" name="teleconsulta_desconto" value="<?php echo $values['teleconsulta_desconto'];?>" class="obg money"/>
											</dd>
										</dl>
									</div>
									<div class="colunas4">
										<dl class="dl2">
											<dt>Benefício</dt>
											<dd><input type="text" class="js-input-teleconsulta" maxlength="" /></dd>
										</dl>
										<dl>
											<dt></dt>
											<dd>
												<button type="button" class="js-add-teleconsulta button button_main" data-loading="0"><i class="iconify" data-icon="fluent:add-circle-24-regular"></i></button>
											</dd>
										</dl>
									</div>

									<div class="list2" style="margin-top:2rem;">
										<table class="js-regs-table js-teleconsultaBeneficios">
											<thead>
												<tr>
													<th>TÍTULO</th>
													<th></th>
												</tr>
											</thead>
											<tbody>
											</tbody>
										</table>
									</div>
									
									<dl>
										<dt>Mensagem</dt>
										<dd><textarea name="teleconsulta_mensagem" class="obg" style="height:150px;"><?php echo $values['teleconsulta_mensagem'];?></textarea></dd>
									</dl>

								</fieldset>

								<fieldset style="margin:0;">
									<legend>Consulta Presencial</legend>

									<input type="hidden" name="consultapresencial_beneficios" value="<?php echo isset($values['consultapresencial_beneficios'])?$values['consultapresencial_beneficios']:'';?>" />
									<script>
										var consultapresencialBeneficios = [];

										const consultapresencialBeneficiosRemover = (index) => {
											consultapresencialBeneficios.splice(index,1);
											consultapresencialBeneficiosListar();
										};
										const consultapresencialBeneficiosListar = () => {
											$('.js-consultapresencialBeneficios tbody').html('');

											html = `<tr class="js-editar">
																	<td class="js-tr-titulo"><h1></h1></td>
																	<td style="text-align:right;"><a href="javascript:;" class="button js-tr-deleta"><i class="iconify" data-icon="fluent:delete-24-regular"></i></a></td>
																</tr>`;

											consultapresencialBeneficios.forEach(x => {
												$('.js-consultapresencialBeneficios').append(html);

												$('.js-consultapresencialBeneficios tbody .js-tr-titulo:last').html(x.titulo);
												$('.js-consultapresencialBeneficios tbody .js-tr-deleta:last').click(function() {
													let index = $(this).index('.js-consultapresencialBeneficios tbody .js-tr-deleta');
													swal({   title: "Atenção",   text: "Você tem certeza que deseja remover este registro?",   type: "warning",   showCancelButton: true,   confirmButtonColor: "#DD6B55",   confirmButtonText: "Sim!",   cancelButtonText: "Não",   closeOnConfirm: false,   closeOnCancel: false }, function(isConfirm){   if (isConfirm) {  consultapresencialBeneficiosRemover(index); swal.close();   } else {   swal.close();   } });
												});
											});

											let json = JSON.stringify(consultapresencialBeneficios);
											$('input[name=consultapresencial_beneficios]').val(json);
										}

										$(function(){
											<?php
											if(is_object($cnt) and !empty($cnt->consultapresencial_beneficios)) {
												echo "consultapresencialBeneficios=JSON.parse('".utf8_encode($cnt->consultapresencial_beneficios)."');";
												echo "consultapresencialBeneficiosListar();";
											} 
											?>
											

					      					$('.js-add-consultapresencial').click(function(){
					      						let beneficio = $('input.js-input-consultapresencial').val();
					      						
					      						if(beneficio.length==0) {
					      							swal({title: "Erro!", text: "Digite o Benefício!", type:"error", confirmButtonColor: "#424242"});
					      							$('input.js-input-consultapresencial').addClass('erro');
					      						} else {
					      							let item = {};
					      							item.titulo = beneficio;
					      							consultapresencialBeneficios.push(item);
					      							consultapresencialBeneficiosListar();
					      							$('input.js-input-consultapresencial').val('');
					      						}

					      					});
										});
									</script>

									<dl>
										<dt>Nome</dt>
										<dd>
											<input type="text" name="consultapresencial_nome" value="<?php echo $values['consultapresencial_nome'];?>" class="obg"/>
										</dd>
									</dl>
									<div class="colunas4">
										<dl class="dl2">
											<dt>Valor</span></dt>
											<dd>
												<input type="text" name="consultapresencial_valor" value="<?php echo $values['consultapresencial_valor'];?>" class="obg money"/>
											</dd>
										</dl>
										<dl class="dl2">
											<dt>Desconto Pagamento Online</dt>
											<dd>
												<input type="text" name="consultapresencial_desconto" value="<?php echo $values['consultapresencial_desconto'];?>" class="obg money"/>
											</dd>
										</dl>
									</div>
									<div class="colunas4">
										<dl class="dl2">
											<dt>Benefício</dt>
											<dd><input type="text" class="js-input-consultapresencial" maxlength="" /></dd>
										</dl>
										<dl>
											<dt></dt>
											<dd>
												<button type="button" class="js-add-consultapresencial button button_main" data-loading="0"><i class="iconify" data-icon="fluent:add-circle-24-regular"></i></button>
											</dd>
										</dl>
									</div>

									<div class="list2" style="margin-top:2rem;">
										<table class="js-regs-table js-consultapresencialBeneficios">
											<thead>
												<tr>
													<th>TÍTULO</th>
													<th></th>
												</tr>
											</thead>
											<tbody>
											</tbody>
										</table>
									</div>
									<dl>
										<dt>Mensagem</dt>
										<dd><textarea name="consultapresencial_mensagem" class="obg" style="height:150px;"><?php echo $values['consultapresencial_mensagem'];?></textarea></dd>
									</dl>
								</fieldset>
							</div>

						</form>
			
					</div>		
				</div>

			</section>
		
		</div>
	</main>

<?php 
include "includes/footer.php";
?>	