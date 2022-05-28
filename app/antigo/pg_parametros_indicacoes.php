<?php
	if(isset($_POST['ajax'])) {
		require_once("lib/conf.php");	
		require_once("usuarios/checa.php");

		$sql = new Mysql();

		$rtn = array();
		if($_POST['ajax']=="indicacoesListar") {
			$indicacao='';
			if(isset($_POST['id_indicacao']) and is_numeric($_POST['id_indicacao'])) {
				$sql->consult($_p."parametros_indicacoes","*","where id='".addslashes($_POST['id_indicacao'])."' and lixo=0");
				if($sql->rows) {
					$indicacao=mysqli_fetch_object($sql->mysqry);
				}
			}

			if(is_object($indicacao)) {
				$sql->consult($_p."parametros_indicacoes_listas","*","where id_indicacao=$indicacao->id and lixo=0 order by titulo asc");
				$indicacoes=array();
				while($x=mysqli_fetch_object($sql->mysqry)) {
					$indicacoes[]=array('id'=>$x->id,
									'titulo'=>utf8_encode($x->titulo));
				}

				$rtn=array('success'=>true,'indicacoes'=>$indicacoes);
			} else {
				$rtn=array('success'=>false,'error'=>'Indicação não definida!');
			}
		} else if($_POST['ajax']=="indicacoesAdicionar") {

			$indicacao='';
			if(isset($_POST['id_indicacao']) and is_numeric($_POST['id_indicacao'])) {
				$sql->consult($_p."parametros_indicacoes","*","where id='".$_POST['id_indicacao']."'");
				if($sql->rows) {
					$indicacao=mysqli_fetch_object($sql->mysqry);
				}
			}

			$indicacaoLista=$erro="";
			if(isset($_POST['id_indicacao_lista']) and is_numeric($_POST['id_indicacao_lista']) and $_POST['id_indicacao_lista']>0) {
				$sql->consult($_p."parametros_indicacoes_listas","*","where id='".$_POST['id_indicacao_lista']."'");
				
				if($sql->rows) {
					$indicacaoLista=mysqli_fetch_object($sql->mysqry);
				} else {
					$erro="Lista de Indicação não encontrada.";
				}
			}

			if(empty($erro)) {
				if(is_object($indicacao)) {

					$vSQL="titulo='".addslashes(utf8_decode(strtoupperWLIB($_POST['titulo'])))."',id_indicacao=$indicacao->id";
					if(is_object($indicacaoLista)) {
						$vSQL.=",lixo=0,id_alteracao=$usr->id,alteracao_data=now()";
						$sql->update($_p."parametros_indicacoes_listas",$vSQL,"where id=$indicacaoLista->id");
					} else {
						$vSQL.=",data=now(),id_usuario=$usr->id,lixo=0";
						$sql->add($_p."parametros_indicacoes_listas",$vSQL);
					}

					$rtn=array('success'=>true);
					
				} else {
					$rtn=array("success"=>false,"error"=>"Indicação não encontrado!");
				}
			} else {
				$rtn=array("success"=>false,"error"=>$erro);
			}
		} else if($_POST['ajax']=="indicacoesRemover") {
			$indicacaoLista='';
			if(isset($_POST['id_indicacao_lista']) and is_numeric($_POST['id_indicacao_lista'])) {
				$sql->consult($_p."parametros_indicacoes_listas","*","where id='".$_POST['id_indicacao_lista']."'");
				if($sql->rows) {
					$indicacaoLista=mysqli_fetch_object($sql->mysqry);
				}
			}

			$indicacao='';
			if(isset($_POST['id_indicacao']) and is_numeric($_POST['id_indicacao'])) {
				$sql->consult($_p."parametros_indicacoes","*","where id='".$_POST['id_indicacao']."'");
				if($sql->rows) {
					$indicacao=mysqli_fetch_object($sql->mysqry);
				}
			}

			if(is_object($indicacao)) {
				if(is_object($indicacaoLista)) {

					$sql->update($_p."parametros_indicacoes_listas","lixo=$usr->id,lixo_data=now()","where id=$indicacaoLista->id and id_indicacao=$indicacao->id");

					$rtn=array('success'=>true);
				} else {
					$rtn=array("success"=>false,"error"=>"Registro não encontrada!");
				}
			} else {
				$rtn=array("success"=>false,"error"=>"Indicação não encontrado!");
			}
		} 

		header("Content-type: application/json");
		echo json_encode($rtn);
		die();
	}
	$title="";
	include "includes/header.php";
	include "includes/nav.php";

	if($usr->tipo!="admin" and !in_array("parametros",$_usuariosPermissoes)) {
		$jsc->jAlert("Você não tem permissão para acessar esta área!","erro","document.location.href='dashboard.php'");
		die();
	}
	$values=$adm->get($_GET);
?>
<section class="content">
	<?php
	require_once("includes/abaConfiguracoes.php");
	?>

	<?php
	$_table=$_p."parametros_indicacoes";
	$_page=basename($_SERVER['PHP_SELF']);

	$_width="";
	$_height="";
	$_dir="";

	if(isset($_GET['form'])) {
		$cnt='';
		$campos=explode(",","titulo");
		
		foreach($campos as $v) $values[$v]='';
		
		if(isset($_GET['edita']) and is_numeric($_GET['edita'])) {
			$sql->consult($_table,"*","where id='".$_GET['edita']."'");
			if($sql->rows) {
				$cnt=mysqli_fetch_object($sql->mysqry);
				
				$values=$adm->values($campos,$cnt);
			} else {
				$jsc->jAlert("Informação não encontrada!","erro","document.location.href='".$_page."'");
				die();
			}
		}
		if(isset($_POST['acao']) and $_POST['acao']=="wlib") {
			$vSQL=$adm->vSQL($campos,$_POST);
			$values=$adm->values;
		 	$processa=true;
			
			if($processa===true) {	
			
				if(is_object($cnt)) {
					$vSQL=substr($vSQL,0,strlen($vSQL)-1);
					$vWHERE="where id='".$cnt->id."'";
					$sql->update($_table,$vSQL,$vWHERE);
					$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='update',vsql='".addslashes($vSQL)."',vwhere='".addslashes($vWHERE)."',tabela='".$_table."',id_reg='".$cnt->id."'");
					$id_reg=$cnt->id;
				} else {
					$vSQL=substr($vSQL,0,strlen($vSQL)-1);
					$sql->add($_table,$vSQL);
					$id_reg=$sql->ulid;
					$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='insert',vsql='".addslashes($vSQL)."',tabela='".$_table."',id_reg='".$sql->ulid."'");

				}

				$msgErro='';
				if(isset($_FILES['foto']) and !empty($_FILES['foto']['tmp_name'])) {
					$up=new Uploader();
					$up->uploadCorta("Imagem Inicial",$_FILES['foto'],"",5242880*2,$_width,'',$_dir,$id_reg);

					if($up->erro) {
						$msgErro=$up->resul;
					} else {
						$ext=$up->ext;
						$vSQL="foto='".$ext."'";
						$vWHERE="where id='".$id_reg."'";
						$sql->update($_table,$vSQL,$vWHERE);
						$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='update',vsql='".addslashes($vSQL)."',tabela='".$_table."',id_reg='".$id_reg."'");
					}
				}
				if(!empty($msgErro)) {
					$jsc->jAlert($msgErro,"erro","");
				} else {
					$jsc->jAlert("Informações salvas com sucesso!","sucesso","document.location.href='".$_page."?".$url."'");
					die();
				}
			}
		}	
	?>
	<script>
		$(function(){
			$('input.money').maskMoney({symbol:'', allowZero:true, showSymbol:true, thousands:'.', decimal:',', symbolStay: true});
		})
	</script>
			
	<section class="filtros">
		<h1 class="filtros__titulo">Indicações</h1>
		<div class="filtros-acoes">
			<a href="<?php echo $_page."?".$url;?>"><i class="iconify" data-icon="bx-bx-left-arrow-alt"></i></a>
			<a href="javascript:;" class="principal btn-submit"><i class="iconify" data-icon="bx-bx-check"></i></a>
			<?php if(is_object($cnt) and $usr->tipo=="admin") { ?>
			<a class="sec js-deletar" href="<?php echo $_page;?>?deleta=<?php echo $cnt->id."&".$url;?>"><i class="iconify" data-icon="bx-bx-trash"></i></a>
			<?php } ?>
		</div>
	</section>

	<section class="grid">
		<form method="post" class="box form formulario-validacao"  autocomplete="off" enctype="multipart/form-data">
			<input type="hidden" name="acao" value="wlib" />

			<fieldset>
				<legend>Indicação</legend>

					<dl class="dl2">
						<dt>Descrição</dt>
						<dd>
							<input type="text" name="titulo" value="<?php echo $values['titulo'];?>" class="obg" />
						</dd>
					</dl>
					
					<?php /*<dl>
						<dt>Tipo</dt>
						<dd>
							<select name="tipo" class="obg">
								<option value="">-</option>
								<?php
								foreach($_parametrosIndicacoesTipo as $k=>$v) {
									echo '<option value="'.$k.'"'.($values['tipo']==$k?' selected':'').'>'.($v).'</option>';
								}
								?>
							</select>
						</dd>
					</dl>*/?>
			</fieldset>

			<?php
			/*if(is_object($cnt)) {
			?>

			<script type="text/javascript">
				$(function(){
					$('select[name=tipo]').change(function() {
						let tipo = $(this).val();

						if(tipo=="LISTA") {
							$('.js-listapersonalizada').show();
						} else {

							$('.js-listapersonalizada').hidden();
						}
					});
				})
			</script>

			<fieldset class="js-listapersonalizada">
				<legend>Lista Personalizada</legend>
				<input type="text" class="js-indicacao-id" value="0" style="display: none;" />
				<div class="colunas4">
					<dl class="dl3">
						<dt>Título</dt>
						<dd><input type="text" class="js-indicacao-titulo" /></dd>
					</dl>
					<dl>
						<dt>&nbsp;</dt>
						<dd>
							<a href="javascript:;" class="button button__sec js-indicacao-salvar"><i class="iconify" data-icon="bx-bx-check"></i></a>
							<a href="javascript:;" class="js-indicacao-cancelar tooltip" style="display: none;color:red" title="Cancelar edição"><span class="iconify" data-icon="icons8:cancel"></span> cancelar edição</a>
						</dd>
					</dl>
				</div>
				<script type="text/javascript">
					var id_indicacao = '<?php echo $cnt->id;?>';
					var indicacoes = [];
					const indicacoesListar = () => {
						let data = `ajax=indicacoesListar&id_indicacao=${id_indicacao}`;
						$.ajax({
							type:'POST',
							data:data,
							success:function(rtn) {
								if(rtn.success===true) {
									indicacoes = rtn.indicacoes;
									$('.js-indicacao-table tbody tr').remove();
									rtn.indicacoes.forEach(x => {

										let tr = `<tr><td>${x.titulo}</td><td><a href="javascript:;" data-id="${x.id}" class="js-editar registros__acao registros__acao_sec"><i class="iconify" data-icon="bx:bxs-edit-alt"></i></a><a href="javascript:;" data-id="${x.id}" class="js-remover registros__acao registros__acao_sec"><i class="iconify" data-icon="bx:bxs-trash"></i></a></td></tr>`;
										$('.js-indicacao-table tbody').append(tr);
										
									})
								} else if(rtn.error) {
									swal({title: "Erro!", text: rtn.error, type:"error", confirmButtonColor: "#424242"});
								} else {
									swal({title: "Erro!", text: "Algum erro ocorreu durante a listagem das indicações", type:"error", confirmButtonColor: "#424242"});
								}
							},
							error:function(){

							}
						});
					}
					const indicacoesAdicionar = () => {
						let titulo = $('input.js-indicacao-titulo').val();
						let id_indicacao_lista = $('input.js-indicacao-id').val();

						if(titulo.length==0) {
							swal({title: "Erro!", text: "Digite o Título da indicação!", type:"error", confirmButtonColor: "#424242"});
						} else {

							let data = `ajax=indicacoesAdicionar&id_indicacao=${id_indicacao}&titulo=${titulo}&id_indicacao_lista=${id_indicacao_lista}`;
							$.ajax({
								type:'POST',
								data: data,
								success:function(rtn) {
									if(rtn.success===true) {
										$('input.js-indicacao-titulo').val(``);
										$('input.js-indicacao-id').val(0);
										indicacoesListar();
										$('.js-indicacao-cancelar').hide();
									} else if(rtn.error) {
										swal({title: "Erro!", text: rtn.error, type:"error", confirmButtonColor: "#424242"});
									} else {
										swal({title: "Erro!", text: "Algum erro ocorreu durante a persistência das indicações", type:"error", confirmButtonColor: "#424242"});
									}
								}
							});

						}
					}

					function indicacao(id_indicacao_lista) {
						return indicacoes.filter( x =>  x.id === id_indicacao_lista);
					}
					$(function(){
						indicacoesListar();

						$('.js-indicacao-salvar').click(function(){
							indicacoesAdicionar();
						});

						$('.js-indicacao-cancelar').click(function(){
							let id_indicacao_lista = eval($('input.js-indicacao-id').val());
							if(id_indicacao_lista>0) {
								$('input.js-indicacao-titulo').val(``);
								$('input.js-indicacao-id').val(0);
							}
							$(this).hide();
						});

						$('.js-indicacao-table').on('click','.js-remover',function(){
							let id_indicacao_lista = $(this).attr('data-id');
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
										let data = `ajax=indicacoesRemover&id_indicacao=${id_indicacao}&id_indicacao_lista=${id_indicacao_lista}`; 
										$.ajax({
											type:"POST",
											data:data,
											success:function(rtn) {
												if(rtn.success) {
													if(id_indicacao_lista==$('input.js-indicacao-id').val()) {
														$('input.js-indicacao-titulo').val(``);
														$('input.js-indicacao-id').val(0);
														$('.js-indicacao-cancelar').hide();
													}
													indicacoesListar();
													swal.close();   
												} else if(rtn.error) {
													swal({title: "Erro!", text: rtn.error, type:"error", confirmButtonColor: "#424242"});
												} else {
													swal({title: "Erro!", text: "Algum erro ocorreu durante a remoção desta indicação!", type:"error", confirmButtonColor: "#424242"});
												}
											},
											error:function(){
												swal({title: "Erro!", text: "Algum erro ocorreu durante a remoção desta indicação!", type:"error", confirmButtonColor: "#424242"});
											}
										})
									} else {   
										swal.close();   
									} 
								});
						});

						$('.js-indicacao-table').on('click','.js-editar',function(){
							let id_indicacao_lista = $(this).attr('data-id');

							const obj = indicacao(id_indicacao_lista);
							if(obj[0]) {
								$('input.js-indicacao-id').val(obj[0].id)
								$('input.js-indicacao-titulo').val(obj[0].titulo);
								$('.js-indicacao-cancelar').show();
							} else {
								swal({title: "Erro!", text: "Indicação não encontrada!", type:"error", confirmButtonColor: "#424242"});
							}
						})
					});
				</script>
				<section class="registros">
					<table class="tablesorter js-indicacao-table">
						<thead>
							<tr>
								<th>Indicação</th>
								<th style="width:120px"></th>
							</tr>
						</thead>
						<tbody>
							
						</tbody>
					</table>
				</section>
			</fieldset>
			<?php
			}*/
			?>
		</form>
	</section>
	
	<?php
	} else {	
	?>

	<section class="filtros">
		<h1 class="filtros__titulo">Indicações</h1>
		<form method="get" class="filtros-form">
			<dl>
				<dt>Busca</dt>
				<dd><input type="text" name="campo" value="<?php echo isset($values['campo'])?$values['campo']:"";?>" /></dd>
			</dl>
			<button type="submit" class="filtros-form__button"><i class="iconify" data-icon="bx-bx-search"></i></button>
		</form>
		<div class="filtros-acoes">
			<a href="<?php echo $_page."?form=1&$url";?>" data-padding="0" class="adicionar tooltip" title="Adicionar"><i class="iconify" data-icon="bx-bx-plus"></i></a>
		</div>
	</section>

	<?php
	if(isset($_GET['deleta']) and is_numeric($_GET['deleta']) and $usr->tipo=="admin") {
		$vSQL="lixo='1'";
		$vWHERE="where id='".$_GET['deleta']."'";
		$sql->update($_table,$vSQL,$vWHERE);
		$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='delete',vsql='".addslashes($vSQL)."',vwhere='".addslashes($vWHERE)."',tabela='".$_table."',id_reg='".$_GET['deleta']."'");
		$jsc->jAlert("Registro excluído com sucesso!","sucesso","document.location.href='".$_page."?".$url."'");
		die();
	}
	
	$where="WHERE lixo='0'";
	if(isset($values['campo']) and !empty($values['campo'])) $where.=" and (titulo like '%".utf8_decode($values['campo'])."%')";
	
	if($usr->cpf=="wlib" and isset($_GET['cmd'])) echo $where;

	$sql->consult($_table,"*",$where." order by titulo asc");
	?>

	<section class="grid">
		<div class="box registros">
			<div class="registros-qtd">
				<p class="registros-qtd__item"><?php echo $sql->rows;?> registros</p>
			</div>

			<table class="tablesorter">
				<thead>
					<tr>
						<th>Título</th>
						<?php /*<th style="width:300px;">Tipo</th>*/?>
					</tr>
				</thead>
				<tbody>
				<?php
				while($x=mysqli_fetch_object($sql->mysqry)) {
				?>
				<tr onclick="document.location.href='<?php echo $_page;?>?form=1&edita=<?php echo $x->id."&".$url;?>'">
					<td><b><?php echo utf8_encode($x->titulo);?></b></td>
					<?php /*<td><?php echo isset($_parametrosIndicacoesTipo[$x->tipo])?($_parametrosIndicacoesTipo[$x->tipo]):"-";?></td>*/?>
				</tr>
				<?php
				}
				?>
				</tbody>
			</table>
		</div>
	</section>
	<?php
	}
	?>
</section>

<?php
	include "includes/footer.php";
?> 