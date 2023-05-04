<?php
	if(isset($_POST['ajax'])) {
		$dir="../../";
		require_once("../../lib/conf.php");
		require_once("../../usuarios/checa.php");


		$attr=array('prefixo'=>$_p,'usr'=>$usr);

		// Categorias
			if($_POST['ajax']=="categoriasListar") {

				$regsCategorias=[];
				$categoriasIds=[];
				$sql->consult($_p."financeiro_fluxo_categorias","*","where id_categoria=0 and lixo=0 order by titulo asc");
				while($x=mysqli_fetch_object($sql->mysqry)) {
					$regsCategorias[]=$x;
					$categoriasIds[]=$x->id;
				}

				$subcategorias=[];
				if(count($categoriasIds)>0) {
					$sql->consult($_p."financeiro_fluxo_categorias","*","where id_categoria IN (".implode(",",$categoriasIds).") and lixo=0 order by titulo asc");
					while($x=mysqli_fetch_object($sql->mysqry)) {
						$subcategorias[$x->id_categoria][]=array('id'=>$x->id,
																	'titulo'=>utf8_encode($x->titulo));
					}
				}

				$categorias=[];
				foreach($regsCategorias as $x) {
					$categorias[]=array('id'=>$x->id,
										'titulo'=>utf8_encode($x->titulo),
										'subcategorias'=>isset($subcategorias[$x->id])?$subcategorias[$x->id]:[]);

				}

				$rtn=array('success'=>true,'categorias'=>$categorias);

			} 

			else if($_POST['ajax']=="categoriasPersistir") {

				$titulo = (isset($_POST['titulo'])) ? $_POST['titulo'] : '';

				$categoria = '';
				if(isset($_POST['id_categoria']) and is_numeric($_POST['id_categoria']) and $_POST['id_categoria']>0) {
					$sql->consult($_p."financeiro_fluxo_categorias","*","where id=".$_POST['id_categoria']." and id_categoria=0 and lixo=0");
					if($sql->rows) $categoria=mysqli_fetch_object($sql->mysqry);
				}

				$erro='';
				if(empty($titulo)) $erro='Digite o título da categoria';


				if(empty($erro)) {

					$vSQL="titulo='".addslashes(utf8_decode($_POST['titulo']))."',
							id_categoria=0";

					if(is_object($categoria)) {
						$vWHERE="where id=$categoria->id";

						$sql->update($_p."financeiro_fluxo_categorias",$vSQL,$vWHERE);
						$id_categoria=$categoria->id;

						$sql->add($_p."log","data=now(),
												id_usuario='".$usr->id."',
												tipo='update',
												vsql='".addslashes($vSQL)."',
												vwhere='".addslashes($vWHERE)."',
												tabela='".$_p."financeiro_fluxo_categorias',
												id_reg='".$id_categoria."'");


					} else {

						$vSQL.=",data=now()";

						$sql->add($_p."financeiro_fluxo_categorias",$vSQL);
						$id_categoria=$sql->ulid;

						$sql->add($_p."log","data=now(),
												id_usuario='".$usr->id."',
												tipo='insert',
												vsql='".addslashes($vSQL)."',
												tabela='".$_p."financeiro_fluxo_categorias',
												id_reg='".$id_categoria."'");


					}
				}

				if(empty($erro)) {
					$rtn=array('success'=>true);
				} else {
					$rtn=array('success'=>false);
				}

			}

			else if($_POST['ajax']=="categoriasRemover") {

				$categoria='';
				if(isset($_POST['id']) and is_numeric($_POST['id'])) {
					$sql->consult($_p."financeiro_fluxo_categorias","*","where id='".addslashes($_POST['id'])."'");
					if($sql->rows) {
						$categoria=mysqli_fetch_object($sql->mysqry);
					}
				}

				$erro='';
				if(empty($categoria)) $erro='Categoria não encontrada';

				if(empty($erro)) {
					$vSQL="lixo=1";
					$vWHERE="where id=$categoria->id";

					$sql->update($_p."financeiro_fluxo_categorias",$vSQL,$vWHERE);

					// Subcategorias no lixo
					$sql->update($_p."financeiro_fluxo_categorias",$vSQL,"WHERE id_categoria='".$categoria->id."'");

					$sql->add($_p."log","data=now(),
											id_usuario='".$usr->id."',
											tipo='update',
											vsql='".addslashes($vSQL)."',
											vwhere='".addslashes($vWHERE)."',
											tabela='".$_p."financeiro_fluxo_categorias',
											id_reg='".$categoria->id."'");
				}

				if(empty($erro)) {
					$rtn=array('success'=>true);
				} else {
					$rtn=array('success'=>false);
				}
			}

		// Subcategorias
			else if($_POST['ajax']=="subcategoriasListar") {

				$categoria = '';
				if(isset($_POST['id_categoria']) and is_numeric($_POST['id_categoria']) and $_POST['id_categoria']>0) {
					$sql->consult($_p."financeiro_fluxo_categorias","*","where id=".$_POST['id_categoria']." and id_categoria=0 and lixo=0");
					if($sql->rows) $categoria=mysqli_fetch_object($sql->mysqry);
				}

				$erro='';
				if(empty($categoria)) $erro='Categoria não definida';

				if(empty($erro)) {

					$subcategorias=[];
					$sql->consult($_p."financeiro_fluxo_categorias","*","where id_categoria=$categoria->id and lixo=0 order by titulo asc");
					while($x=mysqli_fetch_object($sql->mysqry)) {
						$subcategorias[]=array('id'=>$x->id,
												'titulo'=>utf8_encode($x->titulo));
					}
					

					$rtn=array('success'=>true,'subcategorias'=>$subcategorias);
				} else {
					$rtn=array('success'=>false,'error'=>$erro);
				}

			} 

			else if($_POST['ajax']=="subcategoriasPersistir") {

				$titulo = (isset($_POST['titulo'])) ? $_POST['titulo'] : '';

				$categoria = '';
				if(isset($_POST['id_categoria']) and is_numeric($_POST['id_categoria']) and $_POST['id_categoria']>0) {
					$sql->consult($_p."financeiro_fluxo_categorias","*","where id=".$_POST['id_categoria']." and id_categoria=0 and lixo=0");
					if($sql->rows) $categoria=mysqli_fetch_object($sql->mysqry);
				}

				$erro='';
				if(empty($titulo)) $erro='Digite o título da categoria';


				if(empty($erro)) {

					$vSQL="titulo='".addslashes(utf8_decode($_POST['titulo']))."',
							id_categoria=0";

					if(is_object($categoria)) {
						$vWHERE="where id=$categoria->id";

						$sql->update($_p."financeiro_fluxo_categorias",$vSQL,$vWHERE);
						$id_categoria=$categoria->id;

						$sql->add($_p."log","data=now(),
												id_usuario='".$usr->id."',
												tipo='update',
												vsql='".addslashes($vSQL)."',
												vwhere='".addslashes($vWHERE)."',
												tabela='".$_p."financeiro_fluxo_categorias',
												id_reg='".$id_categoria."'");


					} else {

						$vSQL.=",data=now()";

						$sql->add($_p."financeiro_fluxo_categorias",$vSQL);
						$id_categoria=$sql->ulid;

						$sql->add($_p."log","data=now(),
												id_usuario='".$usr->id."',
												tipo='insert',
												vsql='".addslashes($vSQL)."',
												tabela='".$_p."financeiro_fluxo_categorias',
												id_reg='".$id_categoria."'");


					}
				}

				if(empty($erro)) {
					$rtn=array('success'=>true);
				} else {
					$rtn=array('success'=>false);
				}

			}

		header("Content-type: application/json");
		echo json_encode($rtn);
		die();
	}



	# ASIDES

		// Categorias
			if(isset($apiConfig['financeiroFluxoCategorias'])) {

				?>

				<script type="text/javascript">

					const financeiroFluxoCategoriasLista = () => {
						let data = `ajax=categoriasListar`;

						$.ajax({
							type:"POST",
							data:data,
							url:baseURLApiAsideFinanceiro,
							success:function(rtn) {
								if(rtn.success) {

									$('.js-financeiroFluxoCategorias-table tbody tr').remove();

									if(rtn.categorias.length>0) {
										rtn.categorias.forEach(x => {
											let tr = `<tr>
															<td class="titulo">${x.titulo}</td>
															<td>
																<a href="javascript:;" class="button js-asFinanceiroFluxoCategorias-subcategoria-adicionar" data-id="${x.id}"><i class="iconify" data-icon="fluent:add-circle-24-regular"></i></a>

																<a href="javascript:;" class="button js-asFinanceiroFluxoCategorias-categoria-editar" data-id="${x.id}" data-titulo="${x.titulo}"><i class="iconify" data-icon="fluent:edit-24-regular"></i></a>

																<a href="javascript:;" class="button js-asFinanceiroFluxoCategorias-categoria-remover" data-id="${x.id}"><i class="iconify" data-icon="fluent:delete-24-regular"></i></a>
															</td>
														</tr>`;

											if(x.subcategorias.length>0) {
												x.subcategorias.forEach(s => {
													tr += `<tr style="background:var(--cinza1);font-size:12px;color:#999">
																<td>${s.titulo}</td>
																<td>
																	<a href="javascript:;" class="button js-asFinanceiroFluxoCategorias-subcategoria-editar" data-id="${x.id}"><i class="iconify" data-icon="fluent:edit-24-regular"></i></a>

																	<a href="javascript:;" class="button js-asFinanceiroFluxoCategorias-subcategoria-remover" data-id="${x.id}"><i class="iconify" data-icon="fluent:delete-24-regular"></i></a>
																</td>
															</tr>`;
												});
											} else {
												tr += `<tr style="background:var(--cinza1);font-size:12px;color:#999"><td colspan="2"><center>Nenhums subcategoria cadastrada</center></td></tr>`;
											}

											$('.js-financeiroFluxoCategorias-table tbody').append(tr);
										});
									} else {
										$('.js-financeiroFluxoCategorias-table tbody').append(`<tr><td colspan="2"><center>Nenhuma categoria cadastrada</center></td></tr>`);
									}

								} else {
									let error = rtn.error ? rtn.error : 'Algum erro ocorreu durante a listagem das categorias';
									swal({title: "Erro!", text: error, type:"error", confirmButtonColor: "#424242"});

								}
							}
						})
					}

					const financeiroFluxoSubcategoriasLista = (id_categoria) => {
						let data = `ajax=subcategoriasListar&id_categoria=${id_categoria}`;

						$.ajax({
							type:"POST",
							data:data,
							url:baseURLApiAsideFinanceiro,
							success:function(rtn) {
								if(rtn.success) {

									$('.js-financeiroFluxoSubcategorias-table tbody tr').remove();

									if(rtn.subcategorias.length>0) {
										rtn.subcategorias.forEach(x => {
											let tr = `<tr>
															<td class="titulo">${x.titulo}</td>
															<td>

																<a href="javascript:;" class="button js-asFinanceiroFluxoSubcategorias-editar" data-id="${x.id}"><i class="iconify" data-icon="fluent:edit-24-regular"></i></a>

																<a href="javascript:;" class="button js-asFinanceiroFluxoSubcategorias-remover" data-id="${x.id}"><i class="iconify" data-icon="fluent:delete-24-regular"></i></a>
															</td>
														</tr>`;

											
											$('.js-financeiroFluxoSubcategorias-table tbody').append(tr);
										});
									} else {
										$('.js-financeiroFluxoSubcategorias-table tbody').append(`<tr><td colspan="2"><center>Nenhuma subcategoria cadastrada</center></td></tr>`);
									}

									$("#js-aside-financeiroFluxoSubcategorias").fadeIn(100, function() {
										$("#js-aside-financeiroFluxoSubcategorias .aside__inner1").addClass("active");
										$('.js-asFinanceiroFluxoCategorias-id_categoria').val(id_categoria);
										$('.js-asFinanceiroFluxoCategorias-id_subcategoria').val(0);
										$('.js-asFinanceiroFluxoCategorias-titulo').val('');
									});
								} else {
									let error = rtn.error ? rtn.error : 'Algum erro ocorreu durante a listagem das categorias';
									swal({title: "Erro!", text: error, type:"error", confirmButtonColor: "#424242"});

								}
							}
						})

					}

					$(function(){
						financeiroFluxoCategoriasLista(); 	

						$('.js-btn-financeiroFluxoCategorias').click(function(){

							$("#js-aside-financeiroFluxoCategorias").fadeIn(100, function() {
								$("#js-aside-financeiroFluxoCategorias .aside__inner1").addClass("active");
							});

						});	

						$('.aside-close-financeiroFluxoCategorias').click(function(){
							$('.aside-close-financeiroFluxoCategorias').parent().parent().removeClass("active");
							$('.aside-close-financeiroFluxoCategorias').parent().parent().parent().fadeOut();
						});

						$('.js-asFinanceiroFluxoCategorias-submit').click(function(){

							let titulo = $('.js-asFinanceiroFluxoCategorias-titulo').val();
							let id_categoria = $('.js-asFinanceiroFluxoCategorias-id_categoria').val();

							let data = `ajax=categoriasPersistir&titulo=${titulo}&id_categoria=${id_categoria}`;

							let obj = $(this);
							let objHTMLAntigo = $(this).html();

							if(obj.attr('data-loading')==0) {

								obj.attr('data-loading',1);
								obj.html(`<span class="iconify" data-icon="eos-icons:loading"></span>`);

								$.ajax({
									type:"POST",
									data:data,
									url:baseURLApiAsideFinanceiro,
									success:function(rtn) {
										if(rtn.success) {
											financeiroFluxoCategoriasLista();
											$('.js-asFinanceiroFluxoCategorias-id_categoria').val(0);
											$('.js-asFinanceiroFluxoCategorias-titulo').val('');
										} else {
											let error = rtn.error ? rtn.error : 'Algum erro ocorreu durante o registro da Categoria. Tente novamente!';
											swal({title: "Erro!", text: error, type:"error", confirmButtonColor: "#424242"});
										}
									}
								}).done(function(){
									obj.attr('data-loading',0);
									obj.html(objHTMLAntigo);
								});
							}
						});

						$('.js-financeiroFluxoCategorias-table').on('click','.js-asFinanceiroFluxoCategorias-subcategoria-adicionar',function(){
							let id_categoria = $(this).attr('data-id');
							let titulo = $(this).parent().parent().find('.titulo').html();
						
							$('.js-asFinanceiroFluxoSubcategorias-categoriaTitulo').val(titulo);
							financeiroFluxoSubcategoriasLista(id_categoria);
							
						});

						$('.js-financeiroFluxoCategorias-table').on('click', '.js-asFinanceiroFluxoCategorias-categoria-editar',function() {
							let id_categoria = $(this).attr('data-id');
							let titulo = $(this).attr('data-titulo');

							if(id_categoria) {
								$('.js-asFinanceiroFluxoCategorias-titulo').val(titulo);
								$('.js-asFinanceiroFluxoCategorias-id_categoria').val(id_categoria);
							}
						});

						$('.js-financeiroFluxoCategorias-table').on('click', '.js-asFinanceiroFluxoCategorias-categoria-remover',function() {
							let id_categoria = $(this).attr('data-id');

							swal({   
								title: "Atenção",   
								text: "Você tem certeza que deseja remover esta categoria?",   
								type: "warning",   
								showCancelButton: true,   
								confirmButtonColor: "#DD6B55",   
								confirmButtonText: "Sim!",   
								cancelButtonText: "Não",   
								closeOnConfirm: false,   
								closeOnCancel: false 
							}, function(isConfirm){   
								if (isConfirm) {    

									let data = `ajax=categoriasRemover&id=${id_categoria}`;
									$.ajax({
										type:"POST",
										url:baseURLApiAsideFinanceiro,
										data:data,
										success:function(rtn) {
											if(rtn.success) {
												financeiroFluxoCategoriasLista(); 
												swal.close();
												
											} else if(rtn.error) {
												swal({title: "Erro!", text: rtn.error, type:"error", confirmButtonColor: "#424242"});
											} else {
												swal({title: "Erro!", text: 'Algum erro ocorreu durante a remoção deste registro', type:"error", confirmButtonColor: "#424242"});
											}
										},
										error:function(){
											swal({title: "Erro!", text: 'Algum erro ocorreu durante a remoção deste registro.', type:"error", confirmButtonColor: "#424242"});
										}
									}) 
									
								} else {   
									swal.close();   
								} 
							});
						});


						$('.aside-close-financeiroFluxoSubcategorias').click(function(){
							$('.aside-close-financeiroFluxoSubcategorias').parent().parent().removeClass("active");
							$('.aside-close-financeiroFluxoSubcategorias').parent().parent().parent().fadeOut();
						});

						$('.js-asFinanceiroFluxoSubcategorias-submit').click(function(){

							let titulo = $('.js-asFinanceiroFluxoSubcategorias-titulo').val();
							let id_categoria = $('.js-asFinanceiroFluxoSubcategorias-id_categoria').val();
							let id_subcategoria = $('.js-asFinanceiroFluxoSubcategorias-id_subcategoria').val();

							let data = `ajax=subcategoriasPersistir&titulo=${titulo}&id_categoria=${id_categoria}&id_subcategoria=${id_subcategoria}`;

							let obj = $(this);
							let objHTMLAntigo = $(this).html();

							if(obj.attr('data-loading')==0) {

								obj.attr('data-loading',1);
								obj.html(`<span class="iconify" data-icon="eos-icons:loading"></span>`);

								$.ajax({
									type:"POST",
									data:data,
									url:baseURLApiAsideFinanceiro,
									success:function(rtn) {
										if(rtn.success) {
											financeiroFluxoSubcategoriasLista(id_categoria);
											$('.js-asFinanceiroFluxoSubcategorias-id_subcategoria').val(0);
											$('.js-asFinanceiroFluxoSubcategorias-titulo').val('');
										} else {
											let error = rtn.error ? rtn.error : 'Algum erro ocorreu durante o registro da Subcategoria. Tente novamente!';
											swal({title: "Erro!", text: error, type:"error", confirmButtonColor: "#424242"});
										}
									}
								}).done(function(){
									obj.attr('data-loading',0);
									obj.html(objHTMLAntigo);
								});
							}
						})
					})
				</script>

				<!-- Aside Financeiro Fluxo Categorias -->
				<section class="aside aside-financeiroFluxoCategorias" id="js-aside-financeiroFluxoCategorias">

					<div class="aside__inner1">

						<header class="aside-header">
							<h1>Categorias</h1>
							<a href="javascript:;" class="aside-header__fechar aside-close-financeiroFluxoCategorias"><i class="iconify" data-icon="fluent:dismiss-24-filled"></i></a>
						</header>

						<form method="post" class="aside-content form js-asFinancieroFluxoCategorias-form">
							<input type="hidden" class="js-asFinanceiroFluxoCategorias-id_categoria" value="0" />

							<section class="filter" style="margin-bottom:0;">
								<div class="filter-group"></div>
								<div class="filter-group">
									<div class="filter-form form">
										<dl>
											<dd><button type="button" class="button button_main js-asFinanceiroFluxoCategorias-submit" data-loading="0"><i class="iconify" data-icon="fluent:checkmark-12-filled"></i> <span>Salvar</span></button></dd>
										</dl>
									</div>								
								</div>
							</section>

							<div class=" ">
								<dl class="">
									<dt>Categoria</dt>
									<dd>
										<input type="text" class="js-asFinanceiroFluxoCategorias-titulo" />
									</dd>
								</dl>
							</div>

							<div class="list2" style="margin-top:2rem;">
								<table class="js-financeiroFluxoCategorias-table">
									<thead>
										<tr>
											<th>TÍTULO</th>
											<th style="width:150px;"></th>
										</tr>
									</thead>
									<tbody>
									</tbody>
								</table>
							</div>

						</form>
					</div>
				</section>


				<!-- Aside Financeiro Fluxo Categorias Subcategorias -->
				<section class="aside aside-financeiroFluxoSubcategorias" id="js-aside-financeiroFluxoSubcategorias">

					<div class="aside__inner1" style="width:40%">

						<header class="aside-header">
							<h1>Subcategorias</h1>
							<a href="javascript:;" class="aside-header__fechar aside-close-financeiroFluxoSubcategorias"><i class="iconify" data-icon="fluent:dismiss-24-filled"></i></a>
						</header>

						<form method="post" class="aside-content form js-asFinancieroFluxoCategorias-form">
							<input type="hidden" class="js-asFinanceiroFluxoCategorias-id_subcategoria" value="0" />
							<input type="hidden" class="js-asFinanceiroFluxoCategorias-id_categoria" value="0" />

							<section class="filter" style="margin-bottom:0;">
								<div class="filter-group"></div>
								<div class="filter-group">
									<div class="filter-form form">
										<dl>
											<dd><button type="button" class="button button_main js-asFinanceiroFluxoSubcategorias-submit" data-loading="0"><i class="iconify" data-icon="fluent:checkmark-12-filled"></i> <span>Salvar</span></button></dd>
										</dl>
									</div>								
								</div>
							</section>

							<div class=" ">
								<dl class="">
									<dt>Categoria</dt>
									<dd>
										<input type="text" class="js-asFinanceiroFluxoSubcategorias-categoriaTitulo" disabled />
									</dd>
								</dl>
								<dl class="">
									<dt>Subcategoria</dt>
									<dd>
										<input type="text" class="js-asFinanceiroFluxoSubcategorias-titulo" />
									</dd>
								</dl>
							</div>

							<div class="list2" style="margin-top:2rem;">
								<table class="js-financeiroFluxoSubcategorias-table">
									<thead>
										<tr>
											<th>TÍTULO</th>
											<th style="width:100px;"></th>
										</tr>
									</thead>
									<tbody>
									</tbody>
								</table>
							</div>

						</form>
					</div>
				</section>
				<?php
			}
	?>


