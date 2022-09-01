<?php
	require_once("lib/conf.php");
	require_once("usuarios/checa.php");

	$_table=$_p."parametros_examedeimagem";

	$_fornecedores=array();
	$sql->consult($_p."parametros_fornecedores","*,IF(tipo_pessoa='PF',nome,razao_social) as titulo","where tipo='LABORATORIO' and lixo=0 order by titulo");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_fornecedores[$x->id]=$x;
	}

	if(isset($_POST['ajax'])) {

		require_once("usuarios/checa.php");

		$rtn=array();

		$servico='';
		if(isset($_POST['id_servico']) and is_numeric($_POST['id_servico'])) {
			$sql->consult($_p."parametros_servicosdelaboratorio","*","where id='".addslashes($_POST['id_servico'])."' and lixo=0");
			if($sql->rows) {
				$servico=mysqli_fetch_object($sql->mysqry);
			}
		}

		if($_POST['ajax']=="editar") {

			$cnt = '';
			if(isset($_POST['id']) and is_numeric($_POST['id'])) {
				$sql->consult($_table,"*","where id=".$_POST['id']);
				if($sql->rows) {
					$cnt=mysqli_fetch_object($sql->mysqry);
				}
			}

			if(empty($cnt)) {
				$rtn=array('success'=>false,'error'=>'Registro não encontrado!');
			} else {

				$data = array('id'=>$cnt->id,
								'titulo'=>utf8_encode($cnt->titulo),
								'obs'=>utf8_encode($cnt->obs),
								'id_regiao'=>$cnt->id_regiao);

				$rtn=array('success'=>true,'data'=>$data);

			}
		} 

		else if($_POST['ajax']=="remover") {
			$cnt = '';
			if(isset($_POST['id']) and is_numeric($_POST['id'])) {
				$sql->consult($_table,"*","where id=".$_POST['id']);
				if($sql->rows) {
					$cnt=mysqli_fetch_object($sql->mysqry);
				}
			}

			if(empty($cnt)) {
				$rtn=array('success'=>false,'error'=>'Registro não encontrado!');
			} else {

				
				$vWHERE="where id=$cnt->id";
				$vSQL="lixo=1";
				$sql->update($_table,$vSQL,$vWHERE);
				$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='delete',vsql='".addslashes($vSQL)."',vwhere='".addslashes($vWHERE)."',tabela='$_table',id_reg='".$cnt->id."'");

				$rtn=array('success'=>true);

			}
		}

		header("Content-type: application/json");
		echo json_encode($rtn);
		die();
	}
	
	$_regioes=array();
	$sql->consult($_p."parametros_procedimentos_regioes","*","where lixo=0 order by titulo asc");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_regioes[$x->id]=$x;
	}

	include "includes/header.php";
	include "includes/nav.php";

	$values=$adm->get($_GET);
	$campos=explode(",","titulo,id_regiao,obs");

	if(isset($_POST['acao'])) {

		$vSQL=$adm->vSQL($campos,$_POST);
		$values=$adm->values;

		$cnt = '';
		if(isset($_POST['id']) and is_numeric($_POST['id'])) {
			$sql->consult($_table,"*","where id=".$_POST['id']." and lixo=0");
			if($sql->rows) {
				$cnt=mysqli_fetch_object($sql->mysqry);
			}
		}

		if(is_object($cnt)) {
			$vWHERE="where id=$cnt->id";
			$vSQL=substr($vSQL,0,strlen($vSQL)-1);
			$sql->update($_table,$vSQL,$vWHERE);
			$id_reg=$cnt->id;
			$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='update',vsql='".addslashes($vSQL)."',vwhere='".addslashes($vWHERE)."',tabela='$_table',id_reg='$id_reg'");
		} else {
			$vSQL=substr($vSQL,0,strlen($vSQL)-1);
			$sql->add($_table,$vSQL);
			$id_reg=$sql->ulid;
			$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='insert',vsql='".addslashes($vSQL)."',vwhere='',tabela='$_table',id_reg='$id_reg'");
		}

		?>
		<script type="text/javascript">$(function(){openAside(<?php echo $id_reg;?>)});</script>
		<?php
	}

?>

	<header class="header">
		<div class="header__content content">
			<div class="header__inner1">
				<section class="header-title">
					<h1>Configuração</h1>
				</section>
				<?php
				require_once("includes/menus/menuConfiguracoes.php");
				?>
			</div>
		</div>
	</header>

	<main class="main">
		<div class="main__content content">

			<section class="filter">
				
				<div class="filter-group">
					<div class="filter-title">
						<h1>Configure as evoluções</h1>
					</div>
				</div>
			</section>
 	
			<section class="grid">

				<div class="box box-col">

					<?php
					require_once("includes/submenus/subConfiguracoesEvolucao.php");
					?>
					<script type="text/javascript">
						const openAside = (id) => {

							if($.isNumeric(id) && id>0) {
								let data = `ajax=editar&id=${id}`;
								$.ajax({
									type:"POST",
									data:data,
									success:function(rtn){ 
										if(rtn.success) {
											$('#js-aside input[name=titulo]').val(rtn.data.titulo);
											$('#js-aside input[name=id]').val(rtn.data.id);
											$('#js-aside select[name=id_regiao]').val(rtn.data.id_regiao);
											$('#js-aside input[name=obs]').val(rtn.data.obs);

											$(".aside").fadeIn(100,function() {
												$(".aside .aside__inner1").addClass("active");
											});

											$('.js-fieldset-regs,.js-btn-remover').show();
											
											
										} else if(rtn.error) {
											swal({title: "Erro!", text: rtn.error, type:"error", confirmButtonColor: "#424242"});
										} else {
											swal({title: "Erro!", text: 'Algum erro ocorreu durante a abertura deste registro.', type:"error", confirmButtonColor: "#424242"});
										}
									},
									error:function(){
										swal({title: "Erro!", text: 'Algum erro ocorreu durante a abertura deste registro', type:"error", confirmButtonColor: "#424242"});
									}
								})

								

							} else {

								$('.js-fieldset-regs,.js-btn-remover').hide();

								$("#js-aside").fadeIn(100,function() {
									$(".aside .aside__inner1").addClass("active");
								});
							}
						}
						$(function(){
							$('#js-aside .js-btn-remover').click(function(){
								let id = $('input[name=id]').val();
								if($.isNumeric(id) && id>0) {
								
									swal({   
											title: "Atenção",   
											text: "Você tem certeza que deseja remover este registro?",   
											type: "warning",   
											showCancelButton: true,   
											confirmButtonColor: "#DD6B55",   
											confirmButtonText: "Sim!",   
											cancelButtonText: "Não",   
											closeOnConfirm: false,   
											closeOnCancel: false 
										}, function(isConfirm){   
											if (isConfirm) {    

												let data = `ajax=remover&id=${id}`;
												$.ajax({
													type:"POST",
													data:data,
													success:function(rtn) {
														if(rtn.success) {
															document.location.href='<?php echo "$_page?$url";?>';
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
								}
							});

							$('.js-openAside').click(function(){
								$('#js-aside form.formulario-validacao').trigger('reset');
								openAside(0);
							});

							$('.list1').on('click','.js-item',function(){
								$('#js-aside form.formulario-validacao').trigger('reset');
								let id = $(this).attr('data-id');
								openAside(id);
							});
						})
					</script>

					<div class="box-col__inner1">
				
						<section class="filter">
							<div class="filter-group">
								<div class="filter-form form">
									<dl>
										<dd><a href="javascript:;" class="button button_main js-openAside"><i class="iconify" data-icon="fluent:add-circle-24-regular"></i> <span>Novo Exame Complementar</span></a></dd>
									</dl>
								</div>								
							</div>
							<form method="get" class="js-filtro">
								<div class="filter-group">
									<div class="filter-form form">
										<dl>
											<dd class="form-comp form-comp_pos"><input type="text" name="busca" value="<?php echo isset($values['busca'])?$values['busca']:"";?>" placeholder="Buscar..." /><a href="javascript:;" onclick="$('form.js-filtro').submit();"><i class="iconify" data-icon="fluent:search-12-filled"></i></a></dd>
										</dl>
									</div>
								</div>
							</form>					
						</section>

						<?php
						# LISTAGEM #
						$where="where lixo=0";
						if(isset($values['busca']) and !empty($values['busca'])) {
							$where.=" and titulo like '%".$values['busca']."%'";
						}
						$sql->consultPagMto2($_table,"*",10,$where." order by titulo asc","",15,"pagina",$_page."?".$url."&pagina=");
						if($sql->rows==0) {
							if(isset($values['busca'])) $msg="Nenhum registro encontrado";
							else $msg="Nenhum registro cadastrado";

							echo "<center>$msg</center>";
						} else {
						?>	
							<div class="list1">
								<table>
									<?php
									while($x=mysqli_fetch_object($sql->mysqry)) {
									?>
									<tr class="js-item" data-id="<?php echo $x->id;?>">
										<td><h1><strong><?php echo utf8_encode($x->titulo);?></strong></h1></td>
										<td><?php echo utf8_encode($x->obs);?></td>
									<?php
									}
									?>
								</table>
							</div>
							<?php
								if(isset($sql->myspaginacao) and !empty($sql->myspaginacao)) {
							?>
							<div class="paginacao">						
								<?php echo $sql->myspaginacao;?>
							</div>
							<?php
							}
						}
						# LISTAGEM #
						?>

					</div>					
				</div>

			</section>
		
		</div>
	</main>
	<section class="aside aside-form" id="js-aside">
		<div class="aside__inner1">

			<header class="aside-header">
				<h1>Serviço de Laboratório</h1>
				<a href="javascript:;" class="aside-header__fechar aside-close"><i class="iconify" data-icon="fluent:dismiss-24-filled"></i></a>
			</header>

			<form method="post" class="aside-content form js-form formulario-validacao">
				<input type="hidden" name="acao" value="wlib" />
				<input type="hidden" name="id" value="0" />

				<section class="filter">
					<div class="filter-group"></div>
					<div class="filter-group">
						<div class="filter-form form">
							<dl>
								<dd><a href="javascript:;" class="button js-btn-remover"><i class="iconify" data-icon="fluent:delete-24-regular"></i></a></dd>
							</dl>
							<dl>
								<dd><button class="button button_main"><i class="iconify" data-icon="fluent:checkmark-12-filled"></i> <span>Salvar</span></button></dd>
							</dl>
						</div>								
					</div>
				</section>

				<fieldset>
					<legend>Dados do Exame</legend>
					<div class="colunas">
						<dl class="dl2">
							<dt>Nome do Exame</dt>
							<dd><input type="text" name="titulo" /></dd>
						</dl>
						<?php /*<dl>
							<dt>Região</dt>
							<dd>
								<select name="id_regiao" class="">
									<option>-</option>
									<?php
									foreach($_regioes as $e) {
									?>
									<option value="<?php echo $e->id;?>"><?php echo utf8_encode($e->titulo);?></option>
									<?php	
									}
									?>
								</select>
							</dd>
						</dl>*/?>
					</div>

					<dl>
						<dt>Observação</dt>
						<dd>
							<input type="text" name="obs" />
						</dd>
					</dl>

				</fieldset>

				<script type="text/javascript">
					var regs = [];

					const regsListar = (openAside) => {
						
						if(regs) {
							$('.js-regs-table tbody').html('');

							$(`.js-id_fornecedor option`).prop('disabled',false);


							regs.forEach(x=>{

								$(`.js-id_fornecedor`).find(`option[value=${x.id_fornecedor}]`).prop('disabled',true);
								$(`.js-regs-table tbody`).append(`<tr class="aside-open js-editar" data-id="${x.id}">
																	<td><h1>${x.fornecedor}</h1></td>
																	<td>${number_format(x.valor,2,",",".")}</td>
																	<td>${x.obs}</td>
																	<td style="text-align:right;"><a href="javascript:;" class="button js-editar" data-id="${x.id}"><i class="iconify" data-icon="fluent:edit-24-regular"></i></a></td>
																</tr>`)
							});;
							if(openAside===true) {
								$(".aside").fadeIn(100,function() {
									$(".aside .aside__inner1").addClass("active");
								});
							}

						} else {
							if(openAside===true) {
								$(".aside").fadeIn(100,function() {
										$(".aside .aside__inner1").addClass("active");
								});
							}
						}
					}

					const regsAtualizar = (openAside) => {	
						let id_servico=$('#js-aside input[name=id]').val();
						let data = `ajax=regsListar&id_servico=${id_servico}`;
						$.ajax({
							type:"POST",
							data:data,
							success:function(rtn) {
								if(rtn.success) {
									regs=rtn.regs;
									regsListar(openAside);
								}
							}
						})
					}
					
					const regsEditar = (id) => {
						let data = `ajax=regsEditar&id=${id}`;
						$.ajax({
							type:"POST",
							data:data,
							success:function(rtn) {
								if(rtn.success) {
									reg=rtn.cnt

									$(`.js-id`).val(reg.id);
									$(`.js-id_fornecedor`).val(reg.id_fornecedor).find(`option[value=${reg.id_fornecedor}]`).prop('disabled',false);
									$(`.js-valor`).val(number_format(reg.valor,2,",","."));
									$(`.js-obs`).val(reg.obs);

									
									$('.js-form').animate({scrollTop: 0},'fast');
									$('.js-regs-submit').html(`<i class="iconify" data-icon="fluent:checkmark-12-filled"></i>`);
									$('.js-regs-remover').show();

								} else if(rtn.error) {
									swal({title: "Erro!", text: rtn.error, type:"error", confirmButtonColor: "#424242"});
								} else {
									swal({title: "Erro!", text: "Algum erro ocorreu durante a edição deste registro!", type:"error", confirmButtonColor: "#424242"});
								}
							},
							error:function(){
								swal({title: "Erro!", text: "Algum erro ocorreu durante a edição deste registro!", type:"error", confirmButtonColor: "#424242"});
							}
						});
					}

					
					$(function(){

						$('input.money').maskMoney({symbol:'', allowZero:true, showSymbol:true, thousands:'.', decimal:',', symbolStay: true});
						$('.js-regs-submit').click(function(){
							let obj = $(this);
							if(obj.attr('data-loading')==0) {

								let id_servico=$('#js-aside input[name=id]').val();
								let id = $(`.js-id`).val();
								let id_fornecedor = $(`.js-id_fornecedor`).val();
								let valor = unMoney($(`.js-valor`).val());
								let obs = $(`.js-obs`).val();

							

								if(id_fornecedor.length==0) {
									swal({title: "Erro!", text: "Selecione o Laboratório", type:"error", confirmButtonColor: "#424242"});
								}  else {

									obj.html(`<span class="iconify" data-icon="eos-icons:loading"></span>`);
									obj.attr('data-loading',1);

									let data = `ajax=regsPersistir&id_servico=${id_servico}&id=${id}&id_fornecedor=${id_fornecedor}&valor=${valor}&obs=${obs}`;
									
									$.ajax({
										type:'POST',
										data:data,
										success:function(rtn) {
											if(rtn.success) {
												regsAtualizar();	

												$(`.js-id`).val(0);
												$(`.js-id_fornecedor`).val(``);
												$(`.js-valor`).val(``);
												$(`.js-obs`).val(``);

											} else if(rtn.error) {
												swal({title: "Erro!", text: rtn.error, type:"error", confirmButtonColor: "#424242"});
											} else {
												swal({title: "Erro!", text: "Algum erro ocorreu! Tente novamente.", type:"error", confirmButtonColor: "#424242"});
											}
											
										},
										error:function() {
											swal({title: "Erro!", text: "Algum erro ocorreu! Tente novamente.", type:"error", confirmButtonColor: "#424242"});
										}
									}).done(function(){
										$('.js-regs-remover').hide();
										obj.html(`<i class="iconify" data-icon="fluent:add-circle-24-regular"></i>`);
										obj.attr('data-loading',0);
									});

								}
							}
						})

						$('.js-regs-table').on('click','.js-editar',function(){
							let id = $(this).attr('data-id');

							regsEditar(id);
						});

						$('.js-fieldset-regs').on('click','.js-regs-remover',function(){
							let obj = $(this);

							if(obj.attr('data-loading')==0) {

								let id = $('.js-id').val();
								swal({
									title: "Atenção",
									text: "Você tem certeza que deseja remover este registro?",
									type: "warning",
									showCancelButton: true,
									confirmButtonColor: "#DD6B55",
									confirmButtonText: "Sim!",
									cancelButtonText: "Não",
									closeOnConfirm:false,
									closeOnCancel: false }, 
									function(isConfirm){   
										if (isConfirm) {   

											obj.html(`<span class="iconify" data-icon="eos-icons:loading"></span>`);
											obj.attr('data-loading',1);
											let data = `ajax=regsRemover&id=${id}`; 
											$.ajax({
												type:"POST",
												data:data,
												success:function(rtn) {
													if(rtn.success) {
														$(`.js-id`).val(0);
														$(`.js-id_fornecedor`).val('');
														$(`.js-valor`).val('');
														$(`.js-obs`).val('');
														regsAtualizar();
														swal.close();   
													} else if(rtn.error) {
														swal({title: "Erro!", text: rtn.error, type:"error", confirmButtonColor: "#424242"});
													} else {
														swal({title: "Erro!", text: "Algum erro ocorreu durante a remoção deste horário!", type:"error", confirmButtonColor: "#424242"});
													}
												},
												error:function(){
													swal({title: "Erro!", text: "Algum erro ocorreu durante a remoção deste horário!", type:"error", confirmButtonColor: "#424242"});
												}
											}).done(function(){
												$('.js-regs-remover').hide();
												obj.html('<i class="iconify" data-icon="fluent:delete-24-regular"></i>');
												obj.attr('data-loading',0);
												$(`.js-regs-submit`).html(`<i class="iconify" data-icon="fluent:add-circle-24-regular"></i>`);
											});
										} else {   
											swal.close();   
										} 
									});
							}
						});


						$('.js-tipo').change(function(){
							let tipo = $(this).val();

							if(tipo.length>0) {
								if(tipo=='simnao') {
									$('.js-dl-alerta').show();
									$('select[name=pergunta_alerta]').addClass('obg');
								} else if(tipo=='simnaotexto') {
									$('.js-dl-alerta').show();
									$('select[name=pergunta_alerta]').addClass('obg');
								} else {
									$('.js-alerta').val('nenhum');
									$('.js-dl-alerta').hide();
									$('select[name=pergunta_alerta]').removeClass('obg');
								}
							} else {
								$('.js-alerta').val('nenhum');
								$('.js-dl-alerta').hide();
								$('select[name=pergunta_alerta]').removeClass('obg');
							}
						});

					});
				</script>

			</form>

		</div>
	</section><!-- .aside -->
	
	

<?php 
include "includes/footer.php";
?>	