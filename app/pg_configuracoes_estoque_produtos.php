<?php
	require_once("lib/conf.php");
	require_once("usuarios/checa.php");

	$_table=$_p."produtos";

	$_marcas=array();
	$sql->consult($_p."produtos_marcas","*","where lixo=0 order by titulo");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_marcas[$x->id]=$x;
	}

	$_especialidades=array();
	$sql->consult($_p."parametros_especialidades","*","where lixo=0 order by titulo asc");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_especialidades[$x->id]=$x;
	}

	$_categorias=array();
	$sql->consult($_p."produtos_categorias","*","where lixo=0 order by titulo");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_categorias[$x->id]=$x;
	}
	
	$_unidadesMedidas=array();
	$sql->consult($_p."produtos_unidadesmedidas","*","where lixo=0 order by titulo asc");
	while($x=mysqli_fetch_object($sql->mysqry)) $_unidadesMedidas[$x->id]=$x;

	$_tipoBaixas=array('horaClinica' => 'Por Hora Clínica','procedimento' => 'Por Procedimento');

	if(isset($_POST['ajax'])) {

		require_once("usuarios/checa.php");

		$rtn=array();

		
	# Produtos
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
								'id_marca'=>utf8_encode($cnt->id_marca),
								'id_especialidade'=>utf8_encode($cnt->id_especialidade),
								'id_categoria'=>utf8_encode($cnt->id_categoria),
								'unidade_medida'=>utf8_encode($cnt->unidade_medida),
								'volume'=>utf8_encode($cnt->volume),
								'referencia'=>utf8_encode($cnt->referencia),
								'estoque_minimo'=>utf8_encode($cnt->estoque_minimo),
								'embalagemComMais'=>(int)($cnt->embalagemComMais),
								'quantidade'=>utf8_encode($cnt->quantidade),
								'tipo_baixa'=>utf8_encode($cnt->tipo_baixa),
								'codigo_barra'=>utf8_encode($cnt->codigo_barra));

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


			$cnt='';
			if(isset($_POST['id']) and is_numeric($_POST['id'])) {
				$sql->consult($_table."_laboratorios","*","where id='".addslashes($_POST['id'])."' and lixo=0");
				if($sql->rows) {
					$cnt=mysqli_fetch_object($sql->mysqry);
				}
			}

			$fornecedor=(isset($_POST['id_fornecedor']) and isset($_fornecedores[$_POST['id_fornecedor']]))?$_fornecedores[$_POST['id_fornecedor']]:'';
			$valor=(isset($_POST['valor']) and is_numeric($_POST['valor']))?addslashes($_POST['valor']):0;
			$obs=isset($_POST['obs'])?addslashes(utf8_decode($_POST['obs'])):'';

			if(empty($servico)) $rtn=array('success'=>false,'error'=>'Serviço de Laboratório não encontrado!');
			else if(empty($fornecedor)) $rtn=array('success'=>false,'error'=>'Laboratório não encontrado!');
			else {


				$vSQL="id_servicodelaboratorio=$servico->id,
						id_fornecedor='$fornecedor->id',
						valor='".$valor."',
						obs='".$obs."',
						lixo=0";

				if(is_object($cnt)) {
					$vWHERE="where id=$cnt->id";
					//$vSQL.=",alteracao_data=now(),id_alteracao=$usr->id";
					$sql->update($_table."_laboratorios",$vSQL,$vWHERE);
					$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='update',vsql='".addslashes($vSQL)."',vwhere='".addslashes($vWHERE)."',tabela='".$_table."_laboratorios',id_reg='$cnt->id'");
				} else {
					//$vSQL.=",data=now(),id_usuario=$usr->id";
					$sql->add($_table."_laboratorios",$vSQL);
					$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='update',vsql='".addslashes($vSQL)."',vwhere='',tabela='".$_table."_laboratorios',id_reg='$sql->ulid'");

				}

				$rtn=array('success'=>true);
			}
		} 

		else if($_POST['ajax']=="regsListar") {

			
			$regs=array();
			if(is_object($servico)) {
				$where="WHERE id_servicodelaboratorio='".$servico->id."' and lixo=0";
				$sql->consult($_table."_laboratorios","*",$where);
			
				if($sql->rows) {
					while($x=mysqli_fetch_object($sql->mysqry)) {
						$regs[]=array('id' =>$x->id,
											'id_fornecedor' =>$x->id_fornecedor,
											'fornecedor' =>isset($_fornecedores[$x->id_fornecedor])?utf8_encode($_fornecedores[$x->id_fornecedor]->titulo):'',
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
			$cnt='';
			if(isset($_POST['id']) and is_numeric($_POST['id'])) {
				$sql->consult($_table."_laboratorios","*","where id='".addslashes($_POST['id'])."' and lixo=0");
				if($sql->rows) {
					$x=mysqli_fetch_object($sql->mysqry);
					$cnt=(object)array('id' =>$x->id,
									'id_fornecedor' =>$x->id_fornecedor,
									'obs' =>utf8_encode((addslashes($x->obs))),
									'valor' => $x->valor);
				}
			}

			if(is_object($cnt)) {

				

				$rtn=array('success'=>true,
							'id'=>$cnt->id,
							'cnt'=>$cnt);
			} else {
				$rtn=array('success'=>false,'error'=>'Pergunta não encontrada!');
			}
		} 

		else if($_POST['ajax']=="regsRemover") {
			$cnt='';
			if(isset($_POST['id']) and is_numeric($_POST['id'])) {
				$sql->consult($_table."_laboratorios","*","where id='".$_POST['id']."'");
				if($sql->rows) {
					$cnt=mysqli_fetch_object($sql->mysqry);
				}
			}

			if(is_object($cnt)) {
				$vSQL="lixo=$usr->id";
				$vWHERE="where id=$cnt->id";


				$sql->update($_table."_laboratorios",$vSQL,$vWHERE);
				$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='delete',vsql='".addslashes($vSQL)."',vwhere='".addslashes($vWHERE)."',tabela='".$_table."_laboratorios',id_reg='$cnt->id'");

				$rtn=array('success'=>true);
			} else {
				$rtn=array('success'=>false,'error'=>'Plano não encontrado!');
			}
		}

	# Variações Produtos
		else if($_POST['ajax']=="variacoesListar") {

			if(isset($_POST['id_produto']) and is_numeric($_POST['id_produto'])) {

				$regs=array();
				$where="WHERE id_produto='".$_POST['id_produto']."' and lixo=0";
				$sql->consult($_table."_variacoes","*",$where);
			
				if($sql->rows) {
					while($x=mysqli_fetch_object($sql->mysqry)) {
						$regs[]=array('id' =>$x->id,
									  'id_produto' =>$x->id_produto,
									  'titulo' =>utf8_encode($x->titulo));
					}
				} 
				$rtn=array('success'=>true,'regs'=>$regs);

			} else {
				$rtn=array('success'=>false,'error'=>'Produto não definido!');
			}
		} else if($_POST['ajax']=="variacaoPersistir") {

			$produto='';
			if(isset($_POST['id_produto']) and is_numeric($_POST['id_produto']) and $_POST['id_produto']>0) {
				$sql->consult($_table,"*","where id='".addslashes($_POST['id_produto'])."'");
				if($sql->rows) {
					$produto=mysqli_fetch_object($sql->mysqry);
				}
			}

			$variacao='';
			if(isset($_POST['id_variacao']) and is_numeric($_POST['id_variacao']) and $_POST['id_variacao']>0) {
				$sql->consult($_table."_variacoes","*","where id='".addslashes($_POST['id_variacao'])."'");
				if($sql->rows) {
					$variacao=mysqli_fetch_object($sql->mysqry);
				}
			}

			$titulo=isset($_POST['titulo'])?addslashes(utf8_decode($_POST['titulo'])):'';

			if(empty($produto)) $rtn=array('success'=>false,'error'=>'Produto não encontrado');
			if(empty($titulo)) $rtn=array('success'=>false,'error'=>'Preencha o título da Variação!');
			else {

				$vSQL="titulo='$titulo',id_produto='$produto->id'";

				if(is_object($variacao)) {
					$vWHERE="WHERE id=$variacao->id";
					$sql->update($_table."_variacoes", $vSQL,$vWHERE);
					$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='update',vsql='".addslashes($vSQL)."',vwhere='".addslashes($vWHERE)."',tabela='".$_table."_variacoes"."',id_reg='$variacao->id'");
				} else {
					$sql->add($_table."_variacoes",$vSQL);
					$id_variacao=$sql->ulid;
					$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='insert',vsql='".addslashes($vSQL)."',vwhere='',tabela='".$_table."_variacoes"."',id_reg='$id_variacao'");
				}

				$rtn=array('success'=>true);
			}

		} else if($_POST['ajax']=="variacaoRemover") {

			$variacao='';
			if(isset($_POST['id_variacao']) and is_numeric($_POST['id_variacao'])) {
				$sql->consult($_table."_variacoes","*","where id='".addslashes($_POST['id_variacao'])."'");
				if($sql->rows) {
					$variacao=mysqli_fetch_object($sql->mysqry);
				}
			}

			if(empty($variacao)) $rtn=array('success'=>false,'error'=>'Variação não encontrada');
			else {
				$sql->update($_table."_variacoes","lixo=1","WHERE id='".$variacao->id."'");
				$rtn=array('success'=>true);
			}
		}

		header("Content-type: application/json");
		echo json_encode($rtn);
		die();
	}

	include "includes/header.php";
	include "includes/nav.php";
	if($usr->tipo!="admin" and !in_array("configuracoes",$_usuariosPermissoes)) {
		$jsc->jAlert("Você não tem permissão para acessar esta área!","erro","document.location.href='dashboard.php'");
		die();
	}

	$values=$adm->get($_GET);
	$campos=explode(",","titulo,id_marca,id_categoria,id_especialidade,unidade_medida,volume,referencia,estoque_minimo,embalagemComMais,quantidade,tipo_baixa,codigo_barra");

	if(isset($_POST['acao'])) {

		$vSQL=$adm->vSQL($campos,$_POST);
		$values=$adm->values;

		//echo $vSQL;die();

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
						<h1>Configure as produtos</h1>
					</div>
				</div>
			</section>
 	
			<section class="grid">

				<div class="box box-col">

					<?php
					require_once("includes/submenus/subConfiguracoesFornecedor.php");
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
											$('#js-aside select[name=id_marca]').val(rtn.data.id_marca);
											$('#js-aside select[name=id_especialidade]').val(rtn.data.id_especialidade);
											$('#js-aside select[name=id_categoria]').val(rtn.data.id_categoria);
											$('#js-aside select[name=unidade_medida]').val(rtn.data.unidade_medida);
											$('#js-aside input[name=volume]').val(rtn.data.volume);
											$('#js-aside input[name=referencia]').val(rtn.data.referencia);
											$('#js-aside input[name=estoque_minimo]').val(rtn.data.estoque_minimo);
											$('#js-aside select[name=tipo_baixa]').val(rtn.data.tipo_baixa);
											$('#js-aside input[name=codigo_barra]').val(rtn.data.codigo_barra);
											$('#js-aside input[name=embalagemComMais]').prop('checked',rtn.data.embalagemComMais==1?true:false);
											$('#js-aside input[name=quantidade]').val(rtn.data.quantidade);

											$('.js-fieldset-regs,.js-btn-remover').show();
											$('.js-fieldset-variacoes').show();
											variacoesListar();
											
											$(".aside-form").fadeIn(100,function() {
												$(".aside-form .aside__inner1").addClass("active");
												embalagemComMais();
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
								$('.js-fieldset-regs,.js-btn-remover').hide();
								$('.js-fieldset-variacoes').hide();
								$(".aside-form").fadeIn(100,function() {
									$(".aside-form .aside__inner1").addClass("active");
									embalagemComMais();
								});
							}
						}

						const variacoesListar = () => {
							let id_produto = $('#js-aside input[name=id]').val();
							$('.js-variacao-table tbody').html('');

							let data = `ajax=variacoesListar&id_produto=${id_produto}`;

							$.ajax({
								type:"POST",
								data:data,
								success:function(rtn) {
									if(rtn.success) {
										if(rtn.regs.length>0) {
											rtn.regs.forEach(x=>{
												$(`.js-variacao-table tbody`).append(`<tr>
														<td>
															${x.titulo}
														</td>
														<td style="text-align:right;">
															<a href="javascript:;" class="button js-variacao-editar" data-id="${x.id}" data-titulo="${x.titulo}"><i class="iconify" data-icon="fluent:edit-24-regular"></i></a>
															<a href="javascript:;" class="button js-variacao-remover" data-id="${x.id}" data-loading="0"><i class="iconify" data-icon="fluent:delete-24-regular"></i></a>
														</td>
													</tr>`);
											});
										}
									}
								},
								error:function (rtn) {
									console.log("erro: "+rtn);						
								}
							});
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
								$('#js-aside input[name=id]').val(0);
								openAside(0);
							});

							$('.list1').on('click','.js-item',function(){
								$('#js-aside form.formulario-validacao').trigger('reset');
								let id = $(this).attr('data-id');
								openAside(id);
							});

							$('.js-variacao-submit').click(function(){
								let obj = $(this);

								if(obj.attr('data-loading')==0) {

									let id_produto = $('#js-aside input[name=id]').val();
									let id_variacao = $('.js-id_variacao').val();
									let titulo = $(`.js-variacao-titulo`).val();

									if(titulo.length==0) {
										swal({title: "Erro!", text: "Digite o Título", type:"error", confirmButtonColor: "#424242"});
										$('.js-variacao-titulo').addClass('erro');
									} else {
										obj.html(`<span class="iconify" data-icon="eos-icons:loading"></span>`);
										obj.attr('data-loading',1);

										let data = `ajax=variacaoPersistir&titulo=${titulo}&id_variacao=${id_variacao}&id_produto=${id_produto}`;

										$.ajax({
											type:'POST',
											data:data,
											success:function(rtn) {
												if(rtn.success) {
													$(`.js-variacao-titulo`).val(``);
													variacoesListar();

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
											obj.html(`<i class="iconify" data-icon="fluent:add-circle-24-regular"></i>`);
											obj.attr('data-loading',0);
										}); 

									}
								}
							});

							$('.js-variacao-table').on('click','.js-variacao-editar',function(){
								let id_variacao = $(this).attr('data-id');
								let titulo = $(this).attr('data-titulo');

								if(id_variacao) {
									$('.js-id_variacao').val(id_variacao);
									$('.js-variacao-titulo').val(titulo);
								}
							});

							$('.js-variacao-table').on('click','.js-variacao-remover',function(){
								let id_variacao = $(this).attr('data-id');

								if($.isNumeric(id_variacao) && id_variacao>0) {
									
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
												let data = `ajax=variacaoRemover&id_variacao=${id_variacao}`; 
												$.ajax({
													type:"POST",
													data:data,
													success:function(rtn) {
														if(rtn.success) {
															$(`.js-id_variacao`).val(0);
															$(`.js-variacao-titulo`).val('');
															variacoesListar();
															swal.close();   
														} else if(rtn.error) {
															swal({title: "Erro!", text: rtn.error, type:"error", confirmButtonColor: "#424242"});
														} else {
															swal({title: "Erro!", text: "Algum erro ocorreu durante a remoção desta variação!", type:"error", confirmButtonColor: "#424242"});
														}
													},
													error:function(){
														swal({title: "Erro!", text: "Algum erro ocorreu durante a remoção desta variação!", type:"error", confirmButtonColor: "#424242"});
													}
												});
											} else {   
												swal.close();   
											} 
										});
								}
							});
						})
					</script>

					<div class="box-col__inner1">
						<section class="filter">
							<div class="filter-group">
								<div class="filter-form form">
									<dl>
										<dd><a href="javascript:;" class="button button_main js-openAside"><i class="iconify" data-icon="fluent:add-circle-24-regular"></i> <span>Novo Produto</span></a></dd>
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
							else $msg="Nenhum registro";

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
										<td><?php echo $x->unidade_medida;?></td>
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
	<section class="aside-form aside-form" id="js-aside">
		<div class="aside__inner1">

			<header class="aside-header">
				<h1>Produto</h1>
				<a href="javascript:;" class="aside-header__fechar aside-close"><i class="iconify" data-icon="fluent:dismiss-24-filled"></i></a>
			</header>

			<form method="post" class="aside-content form js-form formulario-validacao" onsubmit="return false;">
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
					<legend>Dados do Produto</legend>
					<dl>
						<dt>Nome do Produto</dt>
						<dd><input type="text" name="titulo" class="obg" /></dd>
					</dl>
					<div class="colunas">
						<dl>
							<dt>Marca</dt>
							<dd class="form-comp form-comp_pos">
								<select name="id_marca" class="ajax-id_marca">
									<option value="">-</option>
									<?php
									foreach($_marcas as $e) {
									?>
									<option value="<?php echo $e->id;?>"><?php echo utf8_encode($e->titulo);?></option>
									<?php	
									}
									?>
								</select>
								<a href="javascript:;" class="js-btn-aside" data-aside="marca" data-aside-sub><i class="iconify" data-icon="fluent:edit-24-regular"></i></a>
							</dd>
						</dl>
						<dl>
							<dt>Especialidade</dt>
							<dd class="form-comp form-comp_pos">
								<select name="id_especialidade" class="ajax-id_especialidade">
									<option value="">-</option>
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
							<dt>Categoria</dt>
							<dd class="form-comp form-comp_pos">
								<select name="id_categoria" class="ajax-id_categoria">
									<option value="">-</option>
									<?php
									foreach($_categorias as $e) {
									?>
									<option value="<?php echo $e->id;?>"><?php echo utf8_encode($e->titulo);?></option>
									<?php	
									}
									?>
								</select>
								<a href="javascript:;" class="js-btn-aside" data-aside="categoria" data-aside-sub><i class="iconify" data-icon="fluent:edit-24-regular"></i></a>
							</dd>
						</dl>
						<dl>
							<dt>Unidade de Medida</dt>
							<dd>
								<select name="unidade_medida" class="">
									<option value="">-</option>
									<?php
									foreach($_unidadesMedidas as $v) echo '<option value="'.$v->id.'">'.utf8_encode($v->titulo).'</option>';
									?>
								</select>
							</dd>
						</dl>
						<dl>
							<dt>Volume</dt>
							<dd><input type="text" name="volume" /></dd>
						</dl>
						<dl>
							<dt>Referência</dt>
							<dd><input type="text" name="referencia" /></dd>
						</dl>
						<dl>
							<dt>Estoque Mínimo</dt>
							<dd><input type="text" name="estoque_minimo" /></dd>
						</dl>
						<dl>
							<dt>&nbsp;</dt>					
							<dd><label><input type="checkbox" name="embalagemComMais" value="1" class="input-switch" /> Embalagem com mais de 1 unidade</label></dd>
						</dl>
						<dl>
							<dt>Quantidade</dt>
							<dd><input type="text" name="quantidade" /></dd>
						</dl>		
						<dl>
							<dt>Tipo Baixa</dt>
							<dd>
								<select name="tipo_baixa" class="">
									<option value="">-</option>
									<?php
									foreach($_tipoBaixas as $k => $v) echo '<option value="'.$k.'">'.$v.'</option>';
									?>
								</select>
							</dd>
						</dl>
						<dl class="dl2">
							<dt>Código de Barra</dt>
							<dd><input type="text" name="codigo_barra" /></dd>
						</dl>			
					</div>
				</fieldset>

				<fieldset class="js-fieldset-variacoes" style="display: none;">
					<input type="hidden" class="js-id_variacao" />
					<legend>Variações</legend>

					<div class="colunas3">
						<dl class="dl2">
							<dt>Título</dt>
							<dd><input type="text" class="js-variacao-titulo" /></dd>
						</dl>
						<dl>
							<dt></dt>
							<dd>
								<button type="button" class="button button_main js-variacao-submit" data-loading="0"><i class="iconify" data-icon="fluent:add-circle-24-regular"></i></button>
							</dd>
						</dl>
					</div>
					<div class="list2" style="margin-top:2rem;">
						<table class="js-variacao-table">
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
				</fieldset>

				<script type="text/javascript">
					const embalagemComMais = () => {
						if($('input[name=embalagemComMais]').prop('checked')===true) {
							$('input[name=quantidade]').parent().parent().show();
						} else {
							$('input[name=quantidade]').parent().parent().hide();
						}
					}
					$(function(){
						$('input[name=embalagemComMais]').click(embalagemComMais);
					})
				</script>

			</form>

		</div>
	</section><!-- .aside -->
	
	

<?php 
	$apiConfig=array('especialidade'=>1,
						'marca'=>1,
						'categoria'=>1);

	require_once("includes/api/apiAside.php");
	include "includes/footer.php";
?>	