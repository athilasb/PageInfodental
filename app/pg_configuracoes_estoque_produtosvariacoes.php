<?php
	require_once("lib/conf.php");
	require_once("usuarios/checa.php");

	$_table=$_p."parametros_produtos_variacoes";

	if(isset($_POST['ajax'])) {

		require_once("usuarios/checa.php");
		$rtn=array();

	# Variações
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

	# Variações Opções
		else if($_POST['ajax']=="opcoesListar") {

			if(isset($_POST['id_variacao']) and is_numeric($_POST['id_variacao'])) {

				$regs=array();
				$where="WHERE id_variacao='".$_POST['id_variacao']."' and lixo=0";
				$sql->consult($_table."_opcoes","*",$where);
			
				if($sql->rows) {
					while($x=mysqli_fetch_object($sql->mysqry)) {
						$regs[]=array('id' =>$x->id,
									  'id_variacao' =>$x->id_variacao,
									  'titulo' =>utf8_encode($x->titulo));
					}
				} 
				$rtn=array('success'=>true,'regs'=>$regs);

			} else {
				$rtn=array('success'=>false,'error'=>'Variação não definida!');
			}
		} else if($_POST['ajax']=="opcaoPersistir") {

			$variacao='';
			if(isset($_POST['id_variacao']) and is_numeric($_POST['id_variacao']) and $_POST['id_variacao']>0) {
				$sql->consult($_table,"*","where id='".addslashes($_POST['id_variacao'])."'");
				if($sql->rows) {
					$variacao=mysqli_fetch_object($sql->mysqry);
				}
			}

			$opcao='';
			if(isset($_POST['id_opcao']) and is_numeric($_POST['id_opcao']) and $_POST['id_opcao']>0) {
				$sql->consult($_table."_opcoes","*","where id='".addslashes($_POST['id_opcao'])."'");
				if($sql->rows) {
					$opcao=mysqli_fetch_object($sql->mysqry);
				}
			}

			$titulo=isset($_POST['titulo'])?addslashes(utf8_decode($_POST['titulo'])):'';

			if(empty($variacao)) $rtn=array('success'=>false,'error'=>'Variação não encontrado');
			if(empty($titulo)) $rtn=array('success'=>false,'error'=>'Preencha o título da Opção!');
			else {

				$vSQL="titulo='$titulo',id_variacao='$variacao->id'";

				if(is_object($opcao)) {
					$vWHERE="WHERE id=$opcao->id";
					$sql->update($_table."_opcoes", $vSQL,$vWHERE);
					$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='update',vsql='".addslashes($vSQL)."',vwhere='".addslashes($vWHERE)."',tabela='".$_table."_opcoes"."',id_reg='$opcao->id'");
				} else {
					$sql->add($_table."_opcoes",$vSQL);
					$id_opcao=$sql->ulid;
					$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='insert',vsql='".addslashes($vSQL)."',vwhere='',tabela='".$_table."_opcoes"."',id_reg='$id_opcao'");
				}

				$rtn=array('success'=>true);
			}

		} else if($_POST['ajax']=="opcaoRemover") {

			$opcao='';
			if(isset($_POST['id_opcao']) and is_numeric($_POST['id_opcao'])) {
				$sql->consult($_table."_opcoes","*","where id='".addslashes($_POST['id_opcao'])."'");
				if($sql->rows) {
					$opcao=mysqli_fetch_object($sql->mysqry);
				}
			}

			if(empty($opcao)) $rtn=array('success'=>false,'error'=>'Opção não encontrada');
			else {
				$sql->update($_table."_opcoes","lixo=1","WHERE id='".$opcao->id."'");
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
	$campos=explode(",","titulo");

	if(isset($_POST['acao']) and $_POST['acao']=='wlib') {

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
						<h1>Configure as variações</h1>
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
											$('.js-fieldset-regs,.js-btn-remover').show();
											$('.js-fieldset-opcoes').show();
											opcoesListar();
											
											$(".aside-form").fadeIn(100,function() {
												$(".aside-form .aside__inner1").addClass("active");
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
								});

							} else {
								$('.js-fieldset-regs,.js-btn-remover').hide();
								$(".aside-form").fadeIn(100,function() {
									$(".aside-form .aside__inner1").addClass("active");
								});
							}
						}

						const opcoesListar = () => {
							let id_variacao = $('#js-aside input[name=id]').val();
							$('.js-opcao-table tbody').html('');

							let data = `ajax=opcoesListar&id_variacao=${id_variacao}`;

							$.ajax({
								type:"POST",
								data:data,
								success:function(rtn) {
									if(rtn.success) {
										if(rtn.regs.length>0) {
											rtn.regs.forEach(x=>{
												$(`.js-opcao-table tbody`).append(`<tr>
														<td>
															${x.titulo}
														</td>
														<td style="text-align:right;">
															<a href="javascript:;" class="button js-opcao-editar" data-id="${x.id}" data-titulo="${x.titulo}"><i class="iconify" data-icon="fluent:edit-24-regular"></i></a>
															<a href="javascript:;" class="button js-opcao-remover" data-id="${x.id}" data-loading="0"><i class="iconify" data-icon="fluent:delete-24-regular"></i></a>
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

							$('.js-opcao-submit').click(function(){
								let obj = $(this);

								if(obj.attr('data-loading')==0) {

									let id_variacao = $('#js-aside input[name=id]').val();
									let id_opcao = $('.js-id_opcao').val();
									let titulo = $(`.js-opcao-titulo`).val();

									if(titulo.length==0) {
										swal({title: "Erro!", text: "Digite o Título", type:"error", confirmButtonColor: "#424242"});
										$('.js-opcao-titulo').addClass('erro');
									} else {
										obj.html(`<span class="iconify" data-icon="eos-icons:loading"></span>`);
										obj.attr('data-loading',1);

										let data = `ajax=opcaoPersistir&titulo=${titulo}&id_opcao=${id_opcao}&id_variacao=${id_variacao}`;

										$.ajax({
											type:'POST',
											data:data,
											success:function(rtn) {
												if(rtn.success) {
													$(`.js-opcao-titulo`).val(``);
													opcoesListar();

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

							$('.js-opcao-table').on('click','.js-opcao-editar',function(){
								let id_opcao = $(this).attr('data-id');
								let titulo = $(this).attr('data-titulo');

								if(id_opcao) {
									$('.js-id_opcao').val(id_opcao);
									$('.js-opcao-titulo').val(titulo);
								}
							});

							$('.js-opcao-table').on('click','.js-opcao-remover',function(){
								let id_opcao = $(this).attr('data-id');

								if($.isNumeric(id_opcao) && id_opcao>0) {
									
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
												let data = `ajax=opcaoRemover&id_opcao=${id_opcao}`; 
												$.ajax({
													type:"POST",
													data:data,
													success:function(rtn) {
														if(rtn.success) {
															$(`.js-id_opcao`).val(0);
															$(`.js-opcao-titulo`).val('');
															opcoesListar();
															swal.close();   
														} else if(rtn.error) {
															swal({title: "Erro!", text: rtn.error, type:"error", confirmButtonColor: "#424242"});
														} else {
															swal({title: "Erro!", text: "Algum erro ocorreu durante a remoção desta opção!", type:"error", confirmButtonColor: "#424242"});
														}
													},
													error:function(){
														swal({title: "Erro!", text: "Algum erro ocorreu durante a remoção desta opção!", type:"error", confirmButtonColor: "#424242"});
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
										<dd><a href="javascript:;" class="button button_main js-openAside"><i class="iconify" data-icon="fluent:add-circle-24-regular"></i> <span>Nova Variação</span></a></dd>
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
				<h1>Variação</h1>
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
					<legend>Dados da Variação</legend>
					<dl>
						<dt>Título</dt>
						<dd><input type="text" name="titulo" class="obg" /></dd>
					</dl>
				</fieldset>

				<fieldset class="js-fieldset-opcoes" style="display: none;">
					<input type="hidden" class="js-id_opcao" />
					<legend>Opções</legend>

					<div class="colunas3">
						<dl class="dl2">
							<dt>Título</dt>
							<dd><input type="text" class="js-opcao-titulo" /></dd>
						</dl>
						<dl>
							<dt></dt>
							<dd>
								<button type="button" class="button button_main js-opcao-submit" data-loading="0"><i class="iconify" data-icon="fluent:add-circle-24-regular"></i></button>
							</dd>
						</dl>
					</div>
					<div class="list2" style="margin-top:2rem;">
						<table class="js-opcao-table">
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