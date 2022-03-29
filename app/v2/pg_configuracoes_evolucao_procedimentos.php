<?php
	require_once("lib/conf.php");
	require_once("usuarios/checa.php");

	$_table=$_p."parametros_procedimentos";


	$_planos=array();
	$sql->consult($_p."parametros_planos","*","where lixo=0 order by titulo asc");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_planos[$x->id]=$x;
	}

	if(isset($_POST['ajax'])) {

		require_once("usuarios/checa.php");

		$rtn=array();

		$procedimento='';
		if(isset($_POST['id_procedimento']) and is_numeric($_POST['id_procedimento'])) {
			$sql->consult($_p."parametros_procedimentos","*","where id='".addslashes($_POST['id_procedimento'])."' and lixo=0");
			if($sql->rows) {
				$procedimento=mysqli_fetch_object($sql->mysqry);
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
								'id_especialidade'=>$cnt->id_especialidade,
								'id_regiao'=>$cnt->id_regiao,
								'quantitativo'=>$cnt->quantitativo);

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

		else if($_POST['ajax']=="regsPersistir") {


			$procedimentoPlano='';
			if(isset($_POST['id']) and is_numeric($_POST['id'])) {
				$sql->consult($_table."_planos","*","where id='".addslashes($_POST['id'])."' and lixo=0");
				if($sql->rows) {
					$procedimentoPlano=mysqli_fetch_object($sql->mysqry);
				}
			}

			$plano=(isset($_POST['id_plano']) and isset($_planos[$_POST['id_plano']]))?$_planos[$_POST['id_plano']]:'';
			$valor=(isset($_POST['valor']) and is_numeric($_POST['valor']))?addslashes($_POST['valor']):0;
			$obs=isset($_POST['obs'])?addslashes(utf8_decode($_POST['obs'])):'';

			if(empty($procedimento)) $rtn=array('success'=>false,'error'=>'Procedimento não encontrado!');
			else if(empty($plano)) $rtn=array('success'=>false,'error'=>'Plano não encontrado!');
			else {


				$vSQL="id_procedimento=$procedimento->id,
						id_plano='$plano->id',
						valor='".$valor."',
						obs='".$obs."',
						lixo=0";

				if(is_object($procedimentoPlano)) {
					$vWHERE="where id=$procedimentoPlano->id";
					$vSQL.=",alteracao_data=now(),id_alteracao=$usr->id";
					$sql->update($_table."_planos",$vSQL,$vWHERE);
					$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='update',vsql='".addslashes($vSQL)."',vwhere='".addslashes($vWHERE)."',tabela='".$_table."_planos',id_reg='$procedimentoPlano->id'");
				} else {
					$vSQL.=",data=now(),id_usuario=$usr->id";
					$sql->add($_table."_planos",$vSQL);
					$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='update',vsql='".addslashes($vSQL)."',vwhere='',tabela='".$_table."_planos',id_reg='$sql->ulid'");

				}

				$rtn=array('success'=>true);
			}
		} 

		else if($_POST['ajax']=="regsListar") {

			
			$regs=array();
			if(is_object($procedimento)) {
				$where="WHERE id_procedimento='".$procedimento->id."' and lixo=0";
				$sql->consult($_table."_planos","*",$where);
			
				if($sql->rows) {
					while($x=mysqli_fetch_object($sql->mysqry)) {
						$regs[]=array('id' =>$x->id,
											'id_plano' =>$x->id_plano,
											'plano' =>isset($_planos[$x->id_plano])?utf8_encode($_planos[$x->id_plano]->titulo):'',
											'valor' => (float)$x->valor,
											'obs' =>utf8_encode($x->obs));
					}
				} 
				$rtn=array('success'=>true,'regs'=>$regs);
			} else {
				$rtn=array('success'=>false,'error'=>'Procedimento não definido!');
			}
		} 

		else if($_POST['ajax']=="regsEditar") {
			$procedimentoPlano='';
			if(isset($_POST['id']) and is_numeric($_POST['id'])) {
				$sql->consult($_table."_planos","*","where id='".addslashes($_POST['id'])."' and lixo=0");
				if($sql->rows) {
					$x=mysqli_fetch_object($sql->mysqry);
					$procedimentoPlano=(object)array('id' =>$x->id,
									'id_plano' =>$x->id_plano,
									'obs' =>utf8_encode((addslashes($x->obs))),
									'valor' => $x->valor);
				}
			}

			if(is_object($procedimentoPlano)) {

				

				$rtn=array('success'=>true,
							'id'=>$procedimentoPlano->id,
							'procedimentoPlano'=>$procedimentoPlano);
			} else {
				$rtn=array('success'=>false,'error'=>'Pergunta não encontrada!');
			}
		} 

		else if($_POST['ajax']=="regsRemover") {
			$procedimentoPlano='';
			if(isset($_POST['id']) and is_numeric($_POST['id'])) {
				$sql->consult($_table."_planos","*","where id='".$_POST['id']."'");
				if($sql->rows) {
					$procedimentoPlano=mysqli_fetch_object($sql->mysqry);
				}
			}

			if(is_object($procedimentoPlano)) {
				$sql->update($_table."_planos","lixo=$usr->id,lixo_data=now()","where id=$procedimentoPlano->id");

				$rtn=array('success'=>true);
			} else {
				$rtn=array('success'=>false,'error'=>'Plano não encontrado!');
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

	$_fases=array();
	$sql->consult($_p."parametros_fases","*","where lixo=0 order by titulo asc");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_fases[$x->id]=$x;
	}

	$_especialidades=array();
	$sql->consult($_p."parametros_especialidades","*","where lixo=0 order by titulo asc");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_especialidades[$x->id]=$x;
	}

	include "includes/header.php";
	include "includes/nav.php";

	$values=$adm->get($_GET);
	$campos=explode(",","titulo,id_regiao,quantitativo,id_especialidade");

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
											$('#js-aside select[name=id_especialidade]').val(rtn.data.id_especialidade);
											$('#js-aside select[name=id_regiao]').val(rtn.data.id_regiao);
											$('#js-aside input[name=quantitativo]').prop('checked',rtn.data.quantitativo==1?true:false);
											regsAtualizar(true);

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
										<dd><a href="javascript:;" class="button button_main js-openAside"><i class="iconify" data-icon="fluent:add-circle-24-regular"></i> <span>Novo Procedimento</span></a></dd>
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
								if(isset($values['busca'])) $msg="Nenhum Resultado encontrado";
								else $msg="Nenhum procedimento cadastrado";

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
											<td><?php echo isset($_regioes[$x->id_regiao])?utf8_encode($_regioes[$x->id_regiao]->titulo):"-";?></td>
											<td><?php echo isset($_especialidades[$x->id_regiao])?utf8_encode($_especialidades[$x->id_regiao]->titulo):"-";?></td>
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
				<h1>Procedimento</h1>
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
					<legend>Dados do Procedimento</legend>
					<dl>
						<dt>Título</dt>
						<dd><input type="text" name="titulo" class="obg" /></dd>
					</dl>
					<div class="colunas3">
						<dl>
							<dt>Especialidade</dt>
							<dd class="form-comp form-comp_pos">
								<select name="id_especialidade" class="ajax-id_especialidade" class="obg">
									<option></option>
									<?php
									foreach($_especialidades as $e) {
									?>
									<option value="<?php echo $e->id;?>"><?php echo utf8_encode($e->titulo);?></option>
									<?php	
									}
									?>
								</select>
								<a href="javascript:;" class="js-btn-aside" data-aside="especialidade" data-aside-sub><i class="iconify" data-icon="fluent:edit-24-regular"></i></a>
							</dd>
						</dl>
						<dl>
							<dt>Região</dt>
							<dd>
								<select name="id_regiao" class="obg">
									<option></option>
									<?php
									foreach($_regioes as $e) {
									?>
									<option value="<?php echo $e->id;?>"><?php echo utf8_encode($e->titulo);?></option>
									<?php	
									}
									?>
								</select>
							</dd>
						</dl>
						<dl>
							<dt></dt>
							<dd><label><input type="checkbox" name="quantitativo" value="1" class="input-switch" /> Quantitativo</label>
						</dl>
					</dd>
				</fieldset>

				<script type="text/javascript">
					var regs = [];

					const regsListar = (openAside) => {
						
						if(regs) {
							$('.js-regs-table tbody').html('');

								$(`.js-id_plano option`).prop('disabled',false);


							regs.forEach(x=>{

								$(`.js-id_plano`).find(`option[value=${x.id_plano}]`).prop('disabled',true);
								$(`.js-regs-table tbody`).append(`<tr class="aside-open js-editar" data-id="${x.id}">
																	<td><h1>${x.plano}</h1></td>
																	<td>${number_format(x.valor,2,",",".")}</td>
																	<td>${x.obs}</td>
																	<td style="text-align:right;"><a href="javascript:;" class="button js-editar" data-id="${x.id}"><i class="iconify" data-icon="fluent:edit-24-regular"></i></a></td>
																</tr>`)
							});
							
							if(openAside===true) {
								$(".aside-form").fadeIn(100,function() {
									$(".aside-form .aside__inner1").addClass("active");
								});
							}

						} else {
							if(openAside===true) {
								$(".aside-form").fadeIn(100,function() {
										$(".aside-form .aside__inner1").addClass("active");
								});
							}
						}
					}

					const regsAtualizar = (openAside) => {	
						let id_procedimento=$('#js-aside input[name=id]').val();
						let data = `ajax=regsListar&id_procedimento=${id_procedimento}`;
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
									reg=rtn.procedimentoPlano

									$(`.js-id`).val(reg.id);
									$(`.js-id_plano`).val(reg.id_plano).find(`option[value=${reg.id_plano}]`).prop('disabled',false);
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

								let id_procedimento=$('#js-aside input[name=id]').val();
								let id = $(`.js-id`).val();
								let id_plano = $(`.js-id_plano`).val();
								let valor = unMoney($(`.js-valor`).val());
								let obs = $(`.js-obs`).val();

							

								if(id_plano.length==0) {
									swal({title: "Erro!", text: "Selecione o plano", type:"error", confirmButtonColor: "#424242"});
								}  else {

									obj.html(`<span class="iconify" data-icon="eos-icons:loading"></span>`);
									obj.attr('data-loading',1);

									let data = `ajax=regsPersistir&id_procedimento=${id_procedimento}&id=${id}&id_plano=${id_plano}&valor=${valor}&obs=${obs}`;
									
									$.ajax({
										type:'POST',
										data:data,
										success:function(rtn) {
											if(rtn.success) {
												regsAtualizar();	

												$(`.js-id`).val(0);
												$(`.js-id_plano`).val(``);
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
														$(`.js-id_plano`).val('');
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

				<fieldset class="js-fieldset-regs">
					<input type="hidden" class="js-id" />
					<legend>Planos</legend>
					<div class="colunas3">
						<dl>
							<dt>Plano</dt>
							<dd class="form-comp form-comp_pos">
								<select class="js-id_plano ajax-id_plano">
									<option value="">-</option>
									<?php
									foreach($_planos as $e) {
									?>
									<option value="<?php echo $e->id;?>"><?php echo utf8_encode($e->titulo);?></option>
									<?php	
									}
									?>
								</select>
								<a href="javascript://" data-aside="plano" data-aside-sub><i class="iconify" data-icon="fluent:edit-24-regular"></i></a>
							</dd>
						</dl>
						<dl>
							<dt>Valor</dt>
							<dd class="form-comp"><span>R$</span><input type="tel" class="js-valor money" /></dd>
						</dl>
					</div>
					<dl>
						<dt>Observações</dt>
						<dd>
							<input type="text" class="js-obs" />
							<button type="button" class="button button_main js-regs-submit" data-loading="0"><i class="iconify" data-icon="fluent:add-circle-24-regular"></i></button>
							<a href="javascript:;" class="button js-regs-remover" data-loading="0" style="display:none;"><i class="iconify" data-icon="fluent:delete-24-regular"></i></a>
						</dd>
					</dl>
					<div class="list2" style="margin-top:2rem;">
						<table class="js-regs-table">
							<thead>
								<tr>
									<th>PLANO</th>
									<th>VALOR</th>
									<th>OBSERVAÇÕES</th>
									<th></th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td><h1>Nome do Plano</h1></td>
									<td>R$ 500,00</td>
									<td>Plano especial</td>
									<td style="text-align:right;"><a href="" class="button"><i class="iconify" data-icon="fluent:edit-24-regular"></i></a></td>
								</tr>
								<tr>
									<td><h1>Nome do Plano</h1></td>
									<td>R$ 500,00</td>
									<td>Plano especial</td>
									<td style="text-align:right;"><a href="" class="button"><i class="iconify" data-icon="fluent:edit-24-regular"></i></a></td>
								</tr>
							</tbody>
						</table>
					</div>
				</fieldset>
			</form>

		</div>
	</section><!-- .aside -->
	
<?php 
	$apiConfig=array('especialidade'=>1,
						'plano'=>1);
	require_once("includes/api/apiAside.php");

	require_once("includes/footer.php");
?>	