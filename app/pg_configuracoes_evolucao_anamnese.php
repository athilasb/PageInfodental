<?php
	require_once("lib/conf.php");
	$_table=$_p."parametros_anamnese";
	$_avaliacaoTipos = array(
		'nota' 	  	  => 'Nota (0 ou 10)',
		'simnao' 	  => 'Sim / Não',
		'simnaotexto' => 'Sim / Não / Texto',
		'texto'  => 'Texto'
	);
	$_obrigatorio = array(1 => 'Obrigatório', 0 => '');
	$_alerta = array('sim' => 'Alerta se Resposta SIM', 'nao' => 'Alerta se Resposta NÃO', 'nenhum' => 'Sem Alerta');

	if(isset($_POST['ajax'])) {

		require_once("usuarios/checa.php");

		$rtn=array();

		$anamnese='';
		if(isset($_POST['id_anamnese']) and is_numeric($_POST['id_anamnese'])) {
			$sql->consult($_p."parametros_anamnese","*","where id='".addslashes($_POST['id_anamnese'])."' and lixo=0");
			if($sql->rows) {
				$anamnese=mysqli_fetch_object($sql->mysqry);
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
								'titulo'=>utf8_encode($cnt->titulo));

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

		else if($_POST['ajax']=="perguntasPersistir") {

			$cadeira='';
			if(isset($_POST['id_pergunta']) and is_numeric($_POST['id_pergunta'])) {
				$sql->consult($_p."parametros_cadeiras","*","where id='".$_POST['id_pergunta']."'");
				if($sql->rows) {
					$cadeira=mysqli_fetch_object($sql->mysqry);
				}
			}

			$pergunta='';
			if(isset($_POST['id']) and is_numeric($_POST['id']) and $_POST['id']>0) {
				$sql->consult($_p."parametros_anamnese_formulario","*", "where id='".$_POST['id']."' and lixo=0");
				if($sql->rows) $pergunta=mysqli_fetch_object($sql->mysqry);
			}


			//var_dump($pergunta);die();

			$titulo=(isset($_POST['pergunta']) and !empty($_POST['pergunta']))?($_POST['pergunta']):'';
			$tipo=(isset($_POST['tipo']) and isset($_avaliacaoTipos[$_POST['tipo']]))?addslashes($_POST['tipo']):'';
			$obrigatorio=(isset($_POST['obrigatorio']) and $_POST['obrigatorio']==1)?1:0;
			$alerta=(isset($_POST['alerta']) and isset($_alerta[$_POST['alerta']]))?addslashes($_POST['alerta']):'';

			if(empty($titulo)) $rtn=array('success'=>false,'error'=>'Pergunta não definida!');
			else {


				$vSQL="id_anamnese='$anamnese->id',
						pergunta='".utf8_decode(addslashes($titulo))."',
						tipo='".$tipo."',
						alerta='".$alerta."',
						obrigatorio='".$obrigatorio."',
						lixo=0";

				if(is_object($pergunta)) {
					$vWHERE="where id=$pergunta->id";
					$sql->update($_p."parametros_anamnese_formulario",$vSQL,$vWHERE);
					$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='update',vsql='".addslashes($vSQL)."',vwhere='".addslashes($vWHERE)."',tabela='".$_p."parametros_anamnese_formulario',id_reg='$pergunta->id'");
				} else {
					$sql->add($_p."parametros_anamnese_formulario",$vSQL);
					$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='update',vsql='".addslashes($vSQL)."',vwhere='',tabela='".$_p."parametros_anamnese_formulario',id_reg='$sql->ulid'");

				}

				$rtn=array('success'=>true);
			}
		} 

		else if($_POST['ajax']=="perguntasListar") {

			
			$perguntas=array();
			if(is_object($anamnese)) {
				$sql->consult($_p."parametros_anamnese_formulario","*","WHERE id_anamnese='".$anamnese->id."' and lixo=0 order by ordem asc");
				if($sql->rows) {
					while($x=mysqli_fetch_object($sql->mysqry)) {
						$perguntas[]=array('id_pergunta' =>$x->id,
											'id_anamnese' =>$x->id_anamnese,
											'pergunta' =>utf8_encode((addslashes($x->pergunta))),
											'tipo' => isset($_avaliacaoTipos[$x->tipo])?$_avaliacaoTipos[$x->tipo]:"-",
											'obrigatorio' => isset($_obrigatorio[$x->obrigatorio])?$_obrigatorio[$x->obrigatorio]:"-",
											'alerta' => isset($_alerta[$x->alerta])?$_alerta[$x->alerta]:"-");
					}
				} 
				$rtn=array('success'=>true,'perguntas'=>$perguntas);
			} else {
				$rtn=array('success'=>false,'error'=>'Anamnese não definida!');
			}
		} 

		else if($_POST['ajax']=="perguntasEditar") {
			$pergunta='';
			if(isset($_POST['id_pergunta']) and is_numeric($_POST['id_pergunta'])) {
				$sql->consult($_p."parametros_anamnese_formulario","*","where id='".addslashes($_POST['id_pergunta'])."' and lixo=0");
				if($sql->rows) {
					$x=mysqli_fetch_object($sql->mysqry);
					$pergunta=(object)array('id_pergunta' =>$x->id,
									'id_anamnese' =>$x->id_anamnese,
									'pergunta' =>utf8_encode((addslashes($x->pergunta))),
									'tipo' => isset($_avaliacaoTipos[$x->tipo])?$_avaliacaoTipos[$x->tipo]:"-",
									'obrigatorio' => isset($_obrigatorio[$x->obrigatorio])?$_obrigatorio[$x->obrigatorio]:"-",
									'alerta' => isset($_alerta[$x->alerta])?$_alerta[$x->alerta]:"-");
				}
			}

			if(is_object($pergunta)) {

				

				$rtn=array('success'=>true,
							'id'=>$pergunta->id_pergunta,
							'pergunta'=>$pergunta);
			} else {
				$rtn=array('success'=>false,'error'=>'Pergunta não encontrada!');
			}
		} 

		else if($_POST['ajax']=="perguntasRemover") {
			$pergunta='';
			if(isset($_POST['id_pergunta']) and is_numeric($_POST['id_pergunta'])) {
				$sql->consult($_p."parametros_anamnese_formulario","*","where id='".$_POST['id_pergunta']."'");
				if($sql->rows) {
					$pergunta=mysqli_fetch_object($sql->mysqry);
				}
			}

			if(is_object($pergunta)) {
				$sql->update($_p."parametros_anamnese_formulario","lixo=$usr->id","where id=$pergunta->id");

				$rtn=array('success'=>true);
			} else {
				$rtn=array('success'=>false,'error'=>'Pergunta não encontrado!');
			}
		} else if($_POST['ajax']=="persistirOrdem") {
			if(isset($_POST['ordem']) and !empty($_POST['ordem'])) {
				$ordem=explode(",",$_POST['ordem']);
				if(is_array($ordem) and count($ordem)>0) {
					$aux=1;
					foreach($ordem as $idItem) {
						if(is_numeric($idItem)) {
							$sql->update($_p."parametros_anamnese_formulario","ordem=$aux","where id=$idItem");
							$aux++;
						}
					}
					$rtn=array('success'=>true);
				}
			} else {
				$rtn=array('error'=>'Ordem não definida!');
			}
		}

		header("Content-type: application/json");
		echo json_encode($rtn);
		die();
	}

	include "includes/header.php";
	include "includes/nav.php";

	$values=$adm->get($_GET);
	$campos=explode(",","titulo");

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
						<h1>Configure a clínica</h1>
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
											perguntasAtualizar(true);

											$('.js-fieldset-perguntas,.js-btn-remover').show();
											
											
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

								$('.js-fieldset-perguntas,.js-btn-remover').hide();
								$('#js-aside input[name=id]').val(0);
								$(".aside").fadeIn(100,function() {
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
										<dd><a href="javascript:;" class="button button_main js-openAside"><i class="iconify" data-icon="fluent:add-circle-24-regular"></i> <span>Nova Anamnese</span></a></dd>
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
							else $msg="Nenhuma anamnese cadastrada";

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
									</tr>
									<?php
									}
									?>
								</table>
							</div>
							<?php
								if(isset($sql->myspaginacao) and !empty($sql->myspaginacao)) {
							?>
							<div class="pagination">						
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
	<section class="aside" id="js-aside">
		<div class="aside__inner1">

			<header class="aside-header">
				<h1>Anamnese</h1>
				<a href="javascript:;" class="aside-header__fechar aside-close"><i class="iconify" data-icon="fluent:dismiss-24-filled"></i></a>
			</header>

			<form method="post" class="aside-content form js-form-perguntas formulario-validacao">
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
					<legend>Título da Anamnese</legend>
					<dl>
						<dd><input type="text" name="titulo" class="obg" /></dd>
					</dl>
				</fieldset>

				<script type="text/javascript">
					var perguntas = [];

					const perguntasListar = (openAside) => {
						if(perguntas) {
							$('.js-perguntas-table').html('');

							perguntas.forEach(x=>{
								$(`.js-perguntas-table`).append(`<tr class="aside-open js-editar" data-id="${x.id_pergunta}">
																	<td><i class="iconify" data-icon="fluent:chevron-up-down-24-regular"></i></td>
																	<td><h1>${x.pergunta} (texto)</h1></td>
																	<td>${x.obrigatorio}</td>
																	<td>${x.alerta}</td>
																</tr>`)
							});

							if(openAside===true) {
								$(".aside").fadeIn(100,function() {
									$(".aside .aside__inner1").addClass("active");
								});
							}

						}
					}

					const perguntasAtualizar = (openAside) => {
						let id_anamnese=$('#js-aside input[name=id]').val();
						let data = `ajax=perguntasListar&id_anamnese=${id_anamnese}`;
						$.ajax({
							type:"POST",
							data:data,
							success:function(rtn) {
								if(rtn.success) {
									perguntas=rtn.perguntas;
									perguntasListar(openAside);
								}
							}
						})
					}
					
					const perguntasEditar = (id_pergunta) => {
						let data = `ajax=perguntasEditar&id_pergunta=${id_pergunta}`;
						var horarioObj = [];
						$.ajax({
							type:"POST",
							data:data,
							success:function(rtn) {
								if(rtn.success) {
									pergunta=rtn.pergunta

									$(`.js-id`).val(pergunta.id_pergunta);
									$(`.js-pergunta-titulo`).val(pergunta.pergunta);
									

									$('select.js-alerta').find('option:contains(' + pergunta.alerta + ')').prop('selected',true);

									if(pergunta.tipo=='texto'){
										$('select.js-tipo').find('option[value=texto]').prop('selected',true);
									} else if(pergunta.tipo=='Sim / Não') {
										$('select.js-tipo').find('option[value=simnao]').prop('selected',true);
									} else {
										$('select.js-tipo').find('option:contains(' + pergunta.tipo + ')').prop('selected',true);
									}

									$('select.js-tipo').change();

									$(`.js-obrigatorio`).prop('checked',pergunta.obrigatorio?true:false);
									$('.js-perguntas-submit').html(`<i class="iconify" data-icon="fluent:checkmark-12-filled"></i>`);

									$('.js-perguntas-remover').show();
									$('.js-form-perguntas').animate({scrollTop: 0},'fast');

								} else if(rtn.error) {
									swal({title: "Erro!", text: rtn.error, type:"error", confirmButtonColor: "#424242"});
								} else {
									swal({title: "Erro!", text: "Algum erro ocorreu durante a edição desta pergunta!", type:"error", confirmButtonColor: "#424242"});
								}
							},
							error:function(){
								swal({title: "Erro!", text: "Algum erro ocorreu durante a edição desta pergunta!", type:"error", confirmButtonColor: "#424242"});
							}
						});
					}

					const persistirOrdem = () => {
						let ordem = [];
						$(`.js-perguntas-table .aside-open`).each(function(index,elem){
							ordem.push($(elem).attr('data-id'));
						});

						let data = `ajax=persistirOrdem&ordem=${ordem}`;
						$.ajax({
							type:"POST",
							data:data,
							success:function(rtn) {
								console.log(rtn);
							},
							error:function() {

							}
						})
					}
					$(function(){

						$('.js-perguntas-submit').click(function(){
							let obj = $(this);
							if(obj.attr('data-loading')==0) {

								let id = $(`.js-id`).val();
								let pergunta = $(`.js-pergunta-titulo`).val();
								let tipo = $(`.js-tipo`).val();
								let alerta = $(`.js-alerta`).val();
								let obrigatorio = $(`.js-obrigatorio`).prop('checked')===true?1:0;
								let id_anamnese=$('#js-aside input[name=id]').val();

								if(pergunta.length==0) {
									swal({title: "Erro!", text: "Preencha o campo de Pergunta", type:"error", confirmButtonColor: "#424242"});
								} else if(tipo.length==0) {
									swal({title: "Erro!", text: "Defina o tipo da pergunta", type:"error", confirmButtonColor: "#424242"});
								} else {

									obj.html(`<span class="iconify" data-icon="eos-icons:loading"></span>`);
									obj.attr('data-loading',1);

									let data = `ajax=perguntasPersistir&id_anamnese=${id_anamnese}&id=${id}&pergunta=${pergunta}&tipo=${tipo}&alerta=${alerta}&obrigatorio=${obrigatorio}`;
								
									$.ajax({
										type:'POST',
										data:data,
										success:function(rtn) {
											if(rtn.success) {
												perguntasAtualizar();	

												$(`.js-id`).val(0);
												$(`.js-pergunta-titulo`).val(``);
												$(`.js-tipo`).val(``).trigger('change');
												$(`.js-alerta`).val(``);
												$(`.js-obrigatorio`).prop('checked',false);

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
										$('.js-perguntas-remover').hide();
										obj.html(`<i class="iconify" data-icon="fluent:add-circle-24-regular"></i>`);
										obj.attr('data-loading',0);
									});

								}
							}
						})

						$('.js-perguntas-table').on('click','.js-editar',function(){
							let id = $(this).attr('data-id');
						
							perguntasEditar(id);
						});

						$('.js-fieldset-perguntas').on('click','.js-perguntas-remover',function(){
							let obj = $(this);

							if(obj.attr('data-loading')==0) {

								let id_pergunta = $('.js-id').val();
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
											let data = `ajax=perguntasRemover&id_pergunta=${id_pergunta}`; 
											$.ajax({
												type:"POST",
												data:data,
												success:function(rtn) {
													if(rtn.success) {
														$(`.js-id`).val(0);
														$(`.js-dia`).val('');
														$(`.js-fim`).val('');
														$(`.js-inicio`).val('');
														perguntasAtualizar();
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
												$('.js-perguntas-remover').hide();
												obj.html('<i class="iconify" data-icon="fluent:delete-24-regular"></i>');
												obj.attr('data-loading',0);
												$(`.js-perguntas-submit`).html(`<i class="iconify" data-icon="fluent:add-circle-24-regular"></i>`);
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

						$(".js-perguntas-table").sortable({
							stop:function(event,ui) {
								 let id = $(ui.item).attr('data-id');
								 persistirOrdem(id);
							}
						});

					});
				</script>
				<fieldset class="js-fieldset-perguntas">
					<legend>Defina as Perguntas</legend>

					<input type="hidden" class="js-id" />
					<dl>
						<dt>Pergunta</dt>
						<dd>
							<input type="text" name="pergunta" class="js-pergunta-titulo" />
						</dd>
					</dl>
					<div class="colunas4">
						<dl>
							<dt>Tipo</dt>
							<dd>
								<select name="pergunta_tipo" class="js-tipo">
									<option value="">-</option>
									<option value="nota">Nota (0 ou 10)</option>
									<option value="simnao">Sim / Não</option>
									<option value="simnaotexto">Sim / Não / Texto</option>
									<option value="texto">Texto</option>
								</select>
							</dd>
						</dl>
						<dl>
							<dt></dt>
							<dd>
								<label><input type="checkbox" name="pergunta_obrigatorio" class="input-switch js-obrigatorio"> obrigatório</label>
							</dd>
						</dl>
						<dl class="js-dl-alerta"style="display: none;">
							<dt>Alerta para Pergunta</dt>
							<dd>
								<select name="pergunta_alerta" class="js-alerta">
									<option value="nao">Alerta se Resposta NÃO</option>
									<option value="sim">Alerta se Resposta SIM</option>
									<option value="nenhum" selected>Sem Alerta</option>
								</select>
							</dd>
						</dl>
						<dl>
							<dt></dt>
							<dd style="justify-content:end;">
								<a href="javascript:;" class="button js-perguntas-remover" data-loading="0" style="display:none;"><i class="iconify" data-icon="fluent:delete-24-regular"></i></a>
								<button type="button" class="js-perguntas-submit button button_main" data-loading="0"><i class="iconify" data-icon="fluent:add-circle-24-regular"></i></button>
							</dd>
						</dl>
					</div>

					<div class="list1" style="margin-top:2rem;">
						<table class="js-perguntas-table">
							<tr class="aside-open">
								<td><i class="iconify" data-icon="fluent:chevron-up-down-24-regular"></i></td>
								<td><h1>Tem histórico de doença? (texto)</h1></td>
								<td>Obrigatório</td>
								<td>Sem Alerta</td>
							</tr>
							<tr class="aside-open">
								<td><i class="iconify" data-icon="fluent:chevron-up-down-24-regular"></i></td>
								<td><h1>Como passou ultimamente? (texto)</h1></td>
								<td></td>
								<td>Sem Alerta</td>
							</tr>
							
						</table>
					</div>
				</fieldset>
			</form>

		</div>
	</section><!-- .aside -->

	

<?php 
include "includes/footer.php";
?>	