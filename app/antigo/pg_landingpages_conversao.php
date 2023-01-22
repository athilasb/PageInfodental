<?php
	include "includes/header.php";
	include "includes/nav.php";

	$_table=$_p."landingpages_conversao";
	$_page=basename($_SERVER['PHP_SELF']);

	$landingpage=$cnt='';
	if(isset($_GET['id_landingpage']) and is_numeric($_GET['id_landingpage'])) {
		$sql->consult($_p."landingpage_temas","*","where id='".$_GET['id_landingpage']."'");
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

	$campos=explode(",","teleconsulta_nome,teleconsulta_valor,teleconsulta_desconto,teleconsulta_mensagem,consultapresencial_nome,consultapresencial_valor,consultapresencial_desconto,consultapresencial_mensagem,id_tema");
	foreach($campos as $v) $values[$v]='';

	if(is_object($cnt)) {
		$values=$adm->values($campos,$cnt);
	}

	if(isset($_POST['acao']) and $_POST['acao']=="wlib") {
		$vSQL=$adm->vSQL($campos,$_POST);
		$values=$adm->values;
		$processa=true;

		$teleconsulta_beneficios=utf8_decode($_POST['teleconsulta_beneficios']);
		$vSQL.="teleconsulta_beneficios='".$teleconsulta_beneficios."',";
		$consultapresencial_beneficios=utf8_decode($_POST['consultapresencial_beneficios']);
		$vSQL.="consultapresencial_beneficios='".$consultapresencial_beneficios."',";

		if($processa===true) {	
		
			if(is_object($cnt)) {
				$vSQL.="id_alteracao=$usr->id,alteracao_data=now()";
				$vWHERE="where id='".$cnt->id."'";
				$sql->update($_table,$vSQL,$vWHERE);
				$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='update',vsql='".addslashes($vSQL)."',vwhere='".addslashes($vWHERE)."',tabela='".$_table."',id_reg='".$cnt->id."'");
				$id_reg=$cnt->id;
			} else {
				$sql->add($_table,$vSQL."data=now(),id_usuario='".$usr->id."'");
				$id_reg=$sql->ulid;
				$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='insert',vsql='".addslashes($vSQL)."',tabela='".$_table."',id_reg='".$sql->ulid."'");
			}

			$jsc->jAlert("Informações salvas com sucesso!","sucesso","document.location.href='".$_page."?id_landingpage=".$landingpage->id."'");
			die();
		}
	}
?>
	<section class="content">
		
		<?php
		require_once("includes/abaLandingPage.php");
		?>

		<form method="post" class="form formulario-validacao"  autocomplete="off" enctype="multipart/form-data">
			<input type="hidden" name="acao" value="wlib" />
			<input type="hidden" name="id_tema" value="<?php echo $landingpage->id;?>" />
			<script type="text/javascript">
				$(function(){
					$('input.money').maskMoney({symbol:'', allowZero:true, showSymbol:true, thousands:'.', decimal:',', symbolStay: true});
				});
			</script>

			<section class="grid" style="padding:1rem;">
				<div class="box">

					<div class="filter">

						<div class="filter-group">
							<div class="filter-button">
								<a href="javascript:history.back(-1);"><i class="iconify" data-icon="bx-bx-left-arrow-alt"></i></a>
							</div>
						</div>

						<div class="filter-group">
							<div class="filter-title">
								<span class="badge">5</span> Preencha os dados da conversão
							</div>
						</div>

						<div class="filter-group filter-group_right">
							<div class="filter-button">
								<a href="javascript:;" class="azul  btn-submit"><i class="iconify" data-icon="bx-bx-check"></i><span>salvar</span></a>
							</div>
						</div>

					</div>

					<div class="grid grid_2">
						<fieldset style="margin:0;">

							<input type="hidden" name="teleconsulta_beneficios" value="<?php echo isset($values['teleconsulta_beneficios'])?$values['teleconsulta_beneficios']:'';?>" />
							<script>
								var teleconsultaBeneficios = [];

								const teleconsultaBeneficiosRemover = (index) => {
									teleconsultaBeneficios.splice(index,1);
									teleconsultaBeneficiosListar();
								};
								const teleconsultaBeneficiosListar = () => {
									$('.js-teleconsultaBeneficios').empty();

									html = `<div class="reg-group">
												<div class="reg-color" style="background-color: rgb(0, 128, 0);"></div>
													<div class="reg-data" style="flex:0 1 300px">
														<h1 class="js-tr-titulo"></h1>
													</div>
													<div class="reg-icon">
														<a href="javascript:;" class="js-tr-deleta"><i class="iconify" data-icon="bx-bx-trash"></i></a>
													</div>
												</div>
											</div>`;

									teleconsultaBeneficios.forEach(x => {
										
										$('.js-teleconsultaBeneficios').append(html);
										$('.js-teleconsultaBeneficios .reg-group .reg-data .js-tr-titulo:last').html(x.titulo);
										$('.js-teleconsultaBeneficios .reg-group .js-tr-deleta:last').click(function() {
											let index = $(this).index('.js-teleconsultaBeneficios .reg-group .js-tr-deleta');
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
							<legend>Teleconsulta</legend>

							<dl>
								<dt>Nome <span class="iconify" data-icon="bi:info-circle-fill" data-inline="true" style="color: #98928E;"></span></dt>
								<dd>
									<input type="text" name="teleconsulta_nome" value="<?php echo $values['teleconsulta_nome'];?>" class="obg"/>
								</dd>
							</dl>
							<div class="colunas4">
								<dl class="dl2">
									<dt>Valor <span class="iconify" data-icon="bi:info-circle-fill" data-inline="true" style="color: #98928E;"></span></dt>
									<dd>
										<input type="text" name="teleconsulta_valor" value="<?php echo $values['teleconsulta_valor'];?>" class="obg money"/>
									</dd>
								</dl>
								<dl class="dl2">
									<dt>Desconto Pagamento Online <span class="iconify" data-icon="bi:info-circle-fill" data-inline="true" style="color: #98928E;"></span></dt>
									<dd>
										<input type="text" name="teleconsulta_desconto" value="<?php echo $values['teleconsulta_desconto'];?>" class="obg money"/>
									</dd>
								</dl>
							</div>
							<div class="colunas4">
								<dl class="dl3">
									<dt>Benefício <span class="iconify" data-icon="bi:info-circle-fill" data-inline="true" style="color: #98928E;"></span></dt>
									<dd><input type="text" class="js-input-teleconsulta" maxlength="" /></dd>
								</dl>
								<dl>
									<dt>&nbsp;</dt>
									<dd><a href="javascript:;" class="button js-add-teleconsulta">Adicionar</a></dd>
								</dl>	
							</div>
							<div class="reg js-teleconsultaBeneficios" style="margin-top:2rem;">

							</div>
							<dl>
								<dt>Mensagem <span class="iconify" data-icon="bi:info-circle-fill" data-inline="true" style="color: #98928E;"></span></dt>
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
									$('.js-consultapresencialBeneficios').empty();

									html = `<div class="reg-group">
												<div class="reg-color" style="background-color: rgb(0, 128, 0);"></div>
													<div class="reg-data" style="flex:0 1 300px">
														<h1 class="js-tr-titulo"></h1>
													</div>
													<div class="reg-icon">
														<a href="javascript:;" class="js-tr-deleta"><i class="iconify" data-icon="bx-bx-trash"></i></a>
													</div>
												</div>
											</div>`;

									consultapresencialBeneficios.forEach(x => {
										$('.js-consultapresencialBeneficios').append(html);

										$('.js-consultapresencialBeneficios .reg-group .reg-data .js-tr-titulo:last').html(x.titulo);
										$('.js-consultapresencialBeneficios .reg-group .js-tr-deleta:last').click(function() {
											let index = $(this).index('.js-consultapresencialBeneficios .reg-group .reg-user .js-tr-deleta');
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
								<dt>Nome <span class="iconify" data-icon="bi:info-circle-fill" data-inline="true" style="color: #98928E;"></span></dt>
								<dd>
									<input type="text" name="consultapresencial_nome" value="<?php echo $values['consultapresencial_nome'];?>" class="obg"/>
								</dd>
							</dl>
							<div class="colunas4">
								<dl class="dl2">
									<dt>Valor <span class="iconify" data-icon="bi:info-circle-fill" data-inline="true" style="color: #98928E;"></span></dt>
									<dd>
										<input type="text" name="consultapresencial_valor" value="<?php echo $values['consultapresencial_valor'];?>" class="obg money"/>
									</dd>
								</dl>
								<dl class="dl2">
									<dt>Desconto Pagamento Online <span class="iconify" data-icon="bi:info-circle-fill" data-inline="true" style="color: #98928E;"></span></dt>
									<dd>
										<input type="text" name="consultapresencial_desconto" value="<?php echo $values['consultapresencial_desconto'];?>" class="obg money"/>
									</dd>
								</dl>
							</div>
							<div class="colunas4">
								<dl class="dl3">
									<dt>Benefício <span class="iconify" data-icon="bi:info-circle-fill" data-inline="true" style="color: #98928E;"></span></dt>
									<dd><input type="text" class="js-input-consultapresencial" maxlength="" /></dd>
								</dl>
								<dl>
									<dt>&nbsp;</dt>
									<dd><a href="javascript:;" class="button js-add-consultapresencial">Adicionar</a></dd>
								</dl>	
							</div>
							<div class="reg js-consultapresencialBeneficios" style="margin-top:2rem;">

							</div>
							<dl>
								<dt>Mensagem <span class="iconify" data-icon="bi:info-circle-fill" data-inline="true" style="color: #98928E;"></span></dt>
								<dd><textarea name="consultapresencial_mensagem" class="obg" style="height:150px;"><?php echo $values['consultapresencial_mensagem'];?></textarea></dd>
							</dl>
						</fieldset>
					</div>

				</div>
			</section>
		</form>
	</section>
		
<?php
include "includes/footer.php";
?>