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
			if(isset($_POST['id_cadeira']) and is_numeric($_POST['id_cadeira'])) {
				$sql->consult($_p."parametros_cadeiras","*","where id='".$_POST['id_cadeira']."'");
				if($sql->rows) {
					$cadeira=mysqli_fetch_object($sql->mysqry);
				}
			}

			$horario='';
			if(isset($_POST['id']) and is_numeric($_POST['id']) and $_POST['id']>0) {
				$sql->consult($_p."parametros_cadeiras_horarios","*", "where id='".$_POST['id']."' and lixo=0");
				if($sql->rows) $horario=mysqli_fetch_object($sql->mysqry);
			}


			$inicio=(isset($_POST['inicio']) and !empty($_POST['inicio']))?addslashes($_POST['inicio']):'';
			$fim=(isset($_POST['fim']) and !empty($_POST['fim']))?addslashes($_POST['fim']):'';
			$dia=(isset($_POST['dia']) and is_numeric($_POST['dia']))?addslashes($_POST['dia']):'';

			if(empty($cadeira)) $rtn=array('success'=>false,'error'=>'Cadeira não definida!');
			else if(empty($inicio)) $rtn=array('success'=>false,'error'=>'Cadeira não definida!');
			else if(empty($fim)) $rtn=array('success'=>false,'error'=>'Cadeira não definida!');
			else if(empty($dia) and $dia!=0) $rtn=array('success'=>false,'error'=>'Dia da semana não definido!');
			else {


				$horarios = new Horarios(array('prefixo'=>$_p));

				$attr=array('id_cadeira'=>$cadeira->id,
							'id_horario'=>is_object($horario)?$horario->id:0,
							'diaSemana'=>$dia,
							'inputHoraInicio'=>$inicio,
							'inputHoraFim'=>$fim);

				if($horarios->cadeiraHorariosIntercecao($attr)) {
					$vsql="id_cadeira=$cadeira->id,
						inicio='".$inicio."',
						dia='".$dia."',
						fim='".$fim."'";

					if(is_object($horario)) {
						$vsql.=",id_alteracao=$usr->id,alteracao_data=now()";
						$sql->update($_p."parametros_cadeiras_horarios",$vsql,"where id=$horario->id");
						$rtn=array('success'=>true);
					} else {
						$vsql.=",id_usuario=$usr->id,data=now()";
						$sql->add($_p."parametros_cadeiras_horarios",$vsql);
						$rtn=array('success'=>true);
					}
				} else {
					$rtn=array('success'=>false,'error'=>$horarios->erro);
				}
			}
		} 

		else if($_POST['ajax']=="perguntasListar") {

			
			$perguntas=array();
			if(is_object($anamnese)) {
				$sql->consult($_p."parametros_anamnese_formulario","*","WHERE id_anamnese='".$anamnese->id."' and lixo=0");
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
				$sql->consult($_p."parametros_anamnese","*","where id='".addslashes($_POST['id_pergunta'])."' and lixo=0");
				if($sql->rows) {
					$pergunta=mysqli_fetch_object($sql->mysqry);
				}
			}

			if(is_object($pergunta)) {

				$rtn=array('success'=>true,
							'id'=>$pergunta->id,
							'pergunta'=>utf8_encode($pergunta->titulo));
			} else {
				$rtn=array('success'=>false,'error'=>'Pergunta não encontrado!');
			}
		} 

		else if($_POST['ajax']=="perguntasRemover") {
			$horario='';
			if(isset($_POST['id_horario']) and is_numeric($_POST['id_horario'])) {
				$sql->consult($_p."parametros_cadeiras_horarios","*,date_format(inicio,'%H:%i') as inicio,
																date_format(fim,'%H:%i') as fim","where id='".$_POST['id_horario']."'");
				if($sql->rows) {
					$horario=mysqli_fetch_object($sql->mysqry);
				}
			}

			if(is_object($horario)) {
				$sql->update($_p."parametros_cadeiras_horarios","lixo=$usr->id,lixo_data=now()","where id=$horario->id");

				$rtn=array('success'=>true);
			} else {
				$rtn=array('success'=>false,'error'=>'Horário não encontrado!');
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
											perguntasAtualizar();

											$('.js-fieldset-perguntas,.js-btn-remover').show();
											$(".aside").fadeIn(100,function() {
												$(".aside .aside__inner1").addClass("active");
											});
											
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
							})
							$('.list1').on('click','.js-item',function(){
								$('#js-aside form.formulario-validacao').trigger('reset');
								let id = $(this).attr('data-id');
								openAside(id);
							})
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
							else $msg="Nenhum colaborador cadastrado";

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
	<section class="aside" id="js-aside">
		<div class="aside__inner1">

			<header class="aside-header">
				<h1>Anamnese</h1>
				<a href="javascript:;" class="aside-header__fechar aside-close"><i class="iconify" data-icon="fluent:dismiss-24-filled"></i></a>
			</header>

			<form method="post" class="aside-content form">
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

					const perguntasListar = () => {
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

						}
					}
					const perguntasAtualizar = () => {
						let id_anamnese=$('#js-aside input[name=id]').val();
						let data = `ajax=perguntasListar&id_anamnese=${id_anamnese}`;
						$.ajax({
							type:"POST",
							data:data,
							success:function(rtn) {
								if(rtn.success) {
									perguntas=rtn.perguntas;
									perguntasListar();
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

									$(`.js-id`).val(rtn.id);
									$(`.js-dia`).val(rtn.dia);
									$(`.js-inicio`).val(rtn.inicio);
									$(`.js-fim`).val(rtn.fim);
									$('.js-perguntas-submit').html(`<i class="iconify" data-icon="fluent:checkmark-12-filled"></i>`);

									$('.js-perguntas-remover').show();
								}
							}
						});
					}
					$(function(){

						
						$('.js-perguntas-submit').click(function(){
							let obj = $(this);

							if(obj.attr('data-loading')==0) {

								let id = $(`.js-id`).val();
								let dia = $(`.js-dia`).val();
								let inicio = $(`.js-inicio`).val();
								let fim = $(`.js-fim`).val();
								let id_anamnese=$('#js-aside input[name=id]').val();

								if(dia.length==0) {
									swal({title: "Erro!", text: "Selecione o Dia!", type:"error", confirmButtonColor: "#424242"});
								} else if(inicio.length==0) {
									swal({title: "Erro!", text: "Defina o Início", type:"error", confirmButtonColor: "#424242"});
								} else if(fim.length==0) {
									swal({title: "Erro!", text: "Defina o Fim", type:"error", confirmButtonColor: "#424242"});
								} else {

									return false;
									obj.html(`<span class="iconify" data-icon="eos-icons:loading"></span>`);
									obj.attr('data-loading',1);

									let data = `ajax=perguntasPersistir&id_anamnese=${id_anamnese}&dia=${dia}&inicio=${inicio}&fim=${fim}&id=${id}`;
									$.ajax({
										type:'POST',
										data:data,
										success:function(rtn) {
											if(rtn.success) {
												perguntasAtualizar();	

												$(`.js-id`).val(0);
												$(`.js-dia`).val('');
												$(`.js-fim`).val('');
												$(`.js-inicio`).val('');
												$(`.js-perguntas-cancelar`).hide();
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

								let id_horario = $('.js-id').val();
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

											return false;
											obj.html(`<span class="iconify" data-icon="eos-icons:loading"></span>`);
											obj.attr('data-loading',1);
											let data = `ajax=perguntasRemover&id_horario=${id_horario}`; 
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

					});
				</script>
				<fieldset class="js-fieldset-perguntas">
					<legend>Defina as Perguntas</legend>

					<input type="hidden" class="js-id" />
					<dl>
						<dt>Pergunta</dt>
						<dd><input type="text" name="" class="js-pergunta" /></dd>
					</dl>
					<div class="colunas3">
						<dl>
							<dt>Tipo</dt>
							<dd><select name=""></select></dd>
						</dl>
						<dl>
							<dt></dt>
							<dd><label><input type="checkbox" name="" class="input-switch"> obrigatório</label></dd>
						</dl>
						<dl>
							<dt></dt>
							<dd style="justify-content:end;"><button type="submit" class="button button_main"><i class="iconify" data-icon="fluent:add-circle-24-regular"></i></button></dd>
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