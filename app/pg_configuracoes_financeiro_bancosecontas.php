<?php
	require_once("lib/conf.php");
	require_once("usuarios/checa.php");

	$_table=$_p."financeiro_bancosecontas";

	if(isset($_POST['ajax'])) {

		require_once("usuarios/checa.php");

		$rtn=array();

		
 
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
								'tipo'=>utf8_encode($cnt->tipo),
								'agencia'=>utf8_encode($cnt->agencia),
								'conta'=>utf8_encode($cnt->conta),
								/*'banco'=>utf8_encode($cnt->banco),
								'pix_tipo'=>utf8_encode($cnt->pix_tipo),
								'pix_chave'=>utf8_encode($cnt->pix_chave),
								'pix_beneficiario'=>utf8_encode($cnt->pix_beneficiario)*/);

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

	include "includes/header.php";
	include "includes/nav.php";

	$values=$adm->get($_GET);
	$campos=explode(",","titulo,agencia,conta,tipo");

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
						<h1>Configure o financeiro</h1>
					</div>
				</div>
			</section>
 	
			<section class="grid">

				<div class="box box-col">

					<?php
					require_once("includes/submenus/subConfiguracoesFinanceiro.php");
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
											$(`#js-aside input[name=tipo][value=${rtn.data.tipo}`).click();
											$('#js-aside input[name=titulo]').val(rtn.data.titulo);
											$('#js-aside input[name=id]').val(rtn.data.id);
											$('#js-aside select[name=banco]').val(rtn.data.banco);
											$('#js-aside input[name=agencia]').val(rtn.data.agencia);
											$('#js-aside input[name=conta]').val(rtn.data.conta);
											$('#js-aside input[name=pix_tipo]').val(rtn.data.pix_tipo);
											$('#js-aside input[name=pix_chave]').val(rtn.data.pix_chave);
											$('#js-aside input[name=pix_beneficiario]').val(rtn.data.pix_beneficiario);



											$('.js-fieldset-regs,.js-btn-remover').show();
											
											$(".aside-form").fadeIn(100,function() {
												$(".aside-form .aside__inner1").addClass("active");
												tipoConta();
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

								
								$(".aside-form").fadeIn(100,function() {
									$(".aside-form .aside__inner1").addClass("active");
									tipoConta();
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
										<dd><a href="javascript:;" class="button button_main js-openAside"><i class="iconify" data-icon="fluent:add-circle-24-regular"></i> <span>Novo Banco/Conta</span></a></dd>
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
						//echo $_table." ".$where."->".$sql->rows;
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
										<td><?php echo $x->tipo=="dinheiro"?"Dinheiro":"Conta Corrente";?></td>
										<td><?php echo $x->tipo!="dinheiro"?"$x->agencia / $x->conta":"";?></td>
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
	<section class="aside-form aside-form" id="js-aside">
		<div class="aside__inner1">

			<header class="aside-header">
				<h1>Produto</h1>
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
					<legend>Dados do Banco/Conta</legend>
					<dl>
						<dt>Tipo de Conta</dt>
						<dd>
							<label><input type="radio" name="tipo" value="contacorrente" checked />Conta Corrente</label>
							<label><input type="radio" name="tipo" value="pix" />Pix</label>
							<label><input type="radio" name="tipo" value="dinheiro" />Dinheiro</label>
						</dd>
					</dl>
					<dl>
						<dt>Título</dt>
						<dd><input type="text" name="titulo" class="obg" /></dd>
					</dl>
					<div class="js-tipo js-tipo-cc">
						<div class="colunas3">
							<dl>
								<dt>Banco</dt>
								<dd>
									<select name="banco">
										<option value="">-</option>
										<?php
										foreach($_bancos as $k=>$v) {
										?>
										<option value="<?php echo $k;?>"><?php echo $v;?></option>
										<?php	
										}
										?>
									</select>
								</dd>
							</dl>
							<dl>
								<dt>Agência</dt>
								<dd><input type="text" name="agencia" /></dd>
							</dl>
							<dl>
								<dt>Conta</dt>
								<dd><input type="text" name="conta" /></dd>
							</dl>
						</div>
					</div>
					<div class="js-tipo js-tipo-pix" style="display:none;">
						<div class="colunas3">
							<dl>
								<dt>Tipo</dt>
								<dd>
									<select name="pix_tipo">
										<option value="">-</option>
										<?php
										foreach($_pixTipos as $k=>$v) {
											echo '<option value="'.$k.'">'.$v.'</option>';
										}
										?>
									</select>
								</dd>
							</dl>
							<dl>
								<dt>Chave Pix</dt>
								<dd><input type="text" name="pix_chave" /></dd>
							</dl>
							<dl>
								<dt>Beneficiário</dt>
								<dd><input type="text" name="pix_beneficiario" /></dd>
							</dl>
						</div>
					</div>
				</fieldset>

				<script type="text/javascript">
					const tipoConta = () => {
						let val = $('input[name=tipo]:checked').val();
						$('.js-tipo').hide();

						if(val==="contacorrente") {
							$('.js-tipo-cc').show();
						} else if(val==="pix") {
							$('.js-tipo-pix').show();
						}
					}
					$(function(){
						$('input[name=tipo]').click(tipoConta);
					})
				</script>

			</form>

		</div>
	</section><!-- .aside -->
	
	

<?php 
	include "includes/footer.php";
?>	