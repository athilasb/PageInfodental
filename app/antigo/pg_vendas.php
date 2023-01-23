<?php
	$title="";
	include "includes/header.php";
	include "includes/nav.php";

	if($usr->tipo!="admin" and !in_array("vendas",$_usuariosPermissoes)) {
		$jsc->jAlert("Você não tem permissão para acessar esta área!","erro","document.location.href='dashboard.php'");
		die();
	}
	$values=$adm->get($_GET);
?>
<script>
	$(function(){
		$('.m-vendas').next().show();		
	});
</script>
<section id="conteudo">
	
	<div class="box-caminho">
		<a href="javascript" class="js-collapse"><span></span></a>
		<h1>Vendas</h1>
	</div>
	
	<?php
	$_table=$_p."vendas";
	$_page=basename($_SERVER['PHP_SELF']);

	$_empresas=array();
	$sql->consult($_p."empresas","*","where lixo=0");
	if($sql->rows) {
		while($x=mysqli_fetch_object($sql->mysqry)) {
			$_empresas[$x->id]=$x;
		}
	}


	$_clientes=array();
	$sql->consult($_p."clientes","*","where lixo=0");
	if($sql->rows) {
		while($x=mysqli_fetch_object($sql->mysqry)) {
			$_clientes[$x->id]=$x;
		}
	}


	$_categorias=array();
	$sql->consult($_p."produtos_categorias","*","where lixo=0 order by titulo asc");
	if($sql->rows) {
		while($x=mysqli_fetch_object($sql->mysqry)) {
			$_categorias[$x->id]=$x;
		}
	}

	$_grupos=array();
	$sql->consult($_p."produtos_grupos","*","where lixo=0 order by titulo asc");
	if($sql->rows) {
		while($x=mysqli_fetch_object($sql->mysqry)) {
			$_grupos[$x->id]=$x;
		}
	}

	$_marcas=array();
	$sql->consult($_p."produtos_marcas","*","where lixo=0 order by titulo asc");
	if($sql->rows) {
		while($x=mysqli_fetch_object($sql->mysqry)) {
			$_marcas[$x->id]=$x;
		}
	}

	$_produtos=$_produtosObj=array();
	$sql->consult($_p."produtos","*","where lixo=0 order by titulo asc");
	if($sql->rows) {
		while($x=mysqli_fetch_object($sql->mysqry)) {
			if(!isset($_produtos[$x->id_categoria])) $_produtos[$x->id_categoria]=array();
			$_produtosObj[$x->id]=$x;
		}
	}

	$_width="";
	$_height="";
	$_dir="";
	
	if(isset($_GET['form'])) {
		$cnt='';
		$campos=explode(",","data,id_empresa,id_cliente");
		
		foreach($campos as $v) if(!isset($values[$v])) $values[$v]='';
		$values['data'] = date("d/m/Y H:i");
		
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
			
			//if(empty($cnt)) $vSQL.="code='".codeIn($_table,outUrl(utf8_encode($_POST['titulo'])))."',";
			//else $vSQL.="code='".codeIn2($_table,outUrl(utf8_encode($_POST['titulo'])),$cnt->id)."',";
			
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
					$jsc->jAlert("Informações salvas com sucesso!","sucesso","document.location.href='".$_page."?form=1&edita=".$id_reg."&".$url."'");
					die();
				}
			}
		}	
	?>

	<script type="text/javascript"></script>
	<script>
	$(function(){
		$('.js-categoria').change(function(){
			let id_categoria = $(this).val();
			$('.js-subcategoria option').remove();
			
			$.ajax({
					type:'POST',
					data:'ajax=wlib&id_categoria='+id_categoria,
					url:'ajax/getSubcategorias.php',
					success:function(obj){
						$('select[name=id_subcategoria] option').remove();
						$('select[name=id_subcategoria]').append(`<option value="">-</option>`);
						if(obj) {
							obj.forEach(r => {
								$('select[name=id_subcategoria]').append(`<option value="${r.id}">${r.titulo}</option>`);
							})
						}
						$('select[name=id_subcategoria]').prop('disabled',false).trigger('chosen:updated');
					},
					error:function(){
						$('select[name=id_subcategoria] option').remove();
						$('select[name=id_subcategoria]').prop('disabled',false);
						swal({title: "Erro!", text: "Algum erro ocorreu durante o carregamento das Subcategorias!", type:"error", confirmButtonColor: "#424242"});
					}
			});
		});
	})
	</script>
	<div class="box-botoes clearfix">
		<a href="<?php echo $_page."?".$url;?>" class="botao"><i class="icon-left-big"></i> Voltar</a>
		<?php if(is_object($cnt)) {?><a data-fancybox data-type="ajax" data-src="ajax/log.php?table=<?php echo $_table;?>&id=<?php echo $cnt->id;?>" href="javascript:;" class="botao" ><i class="icon-info-circled"></i> Logs</a><?php } ?>
		<a href="javascript://" class="botao botao-principal btn-submit"><i class="icon-ok"></i> Salvar</a>

	</div>
	<div class="box-form">
		<script type="text/javascript">
			$(function(){
				$('input.money').maskMoney({symbol:'', allowZero:true, showSymbol:true, thousands:'.', decimal:',', symbolStay: true});
				$('select[name=id_empresa],select[name=id_cliente],select[name=id_categoria],select[name=id_subcategoria],select[name=id_produto]').change(function(){
					let url =`?form=1&id_empresa=${$('select[name=id_empresa]').val()}&id_cliente=${$('select[name=id_cliente]').val()}`;
					if($('select[name=id_categoria]').length>0) url+=`&id_categoria=${$('select[name=id_categoria]').val()}`;
					if($('select[name=id_subcategoria]').length>0) url+=`&id_subcategoria=${$('select[name=id_subcategoria]').val()}`;
					if($('select[name=id_produto]').length>0) url+=`&id_produto=${$('select[name=id_produto]').val()}`;
					document.location.href=url;
				})
			});
		</script>
		<form method="post" class="formulario-validacao"  autocomplete="off" enctype="multipart/form-data">
			<input type="hidden" name="acao" value="wlib" />
			<?php
			if(is_object($cnt)) {

			} else {
			?>
			<fieldset>
				<legend>Dados da Venda</legend>

				<div class="colunas4">
					<dl>
						<dt>Data</dt>
						<dd>
							<input type="text" name="" value="<?php echo $values['data'];?>" disabled />
						</dd>
					</dl>

					<dl class="dl3">
						<dt>Empresa</dt>
						<dd>
							<select name="id_empresa" class="obg chosen">
								<option value=""></option>
								<?php
								foreach($_empresas as $x) {
								?>
								<option value="<?php echo $x->id;?>"<?php echo $x->id==$values['id_empresa']?' selected':'';?>><?php echo utf8_encode($x->titulo);?></option>
								<?php	
								}
								?>
							</select>
						</dd>
					</dl>
				</div>

				<dl>
					<dt>Cliente</dt>
					<dd>
						<select name="id_cliente" class="obg chosen" style="width: 90%">
							<option value=""></option>
							<?php
							foreach($_clientes as $x) {
							?>
							<option value="<?php echo $x->id;?>"<?php echo $x->id==$values['id_cliente']?' selected':'';?>><?php echo utf8_encode($x->nome);?></option>
							<?php	
							}
							?>
						</select> <a href="pg_clientes.php?form=1" target="_blank" class="botao botao-principal"><i class="icon-plus"></i></a>
					</dd>
				</dl>
			</fieldset>

			<?php
				if(isset($values['id_empresa']) and isset($_empresas[$values['id_empresa']]) and isset($values['id_cliente']) and isset($_clientes[$values['id_cliente']])) {
					
					// retorna a venda ou cria a venda
					$sql->consult($_p."vendas","*","where id_cliente='".$values['id_cliente']."' and id_empresa='".$values['id_empresa']."' and lixo=0 and fechada=0");
					if($sql->rows) {
						$venda=mysqli_fetch_object($sql->mysqry);
					} else {
						$sql->add($_p."vendas","data=now(),
													id_cliente=".$values['id_cliente'].",
													id_empresa=".$values['id_empresa'].",
													id_usuario=$usr->id");
						$sql->consult($_p."vendas","*","where id=$sql->ulid");
						if($sql->rows) {
							$venda=mysqli_fetch_object($sql->mysqry);
						}
					}

					if(isset($_GET['incluir'])) {
						if(isset($values['quantidade']) and is_numeric($values['quantidade'])) {
							$produto="";
							if(isset($_produtosObj[$values['id_produto']])) $produto=$_produtosObj[$values['id_produto']];
							if(empty($produto)) {
								$jsc->jAlert("Produto não encontrado!","erro","");
							} else {
								$sql->consult($_p."produtos_estoque","*","where id_produto=$produto->id and id_empresa='".$values['id_empresa']."' and lixo=0 and venda=0 and reserva=0");
								$estoque=$sql->rows;

								$sql->consult($_p."vendas_produtos","*","where id_venda=$venda->id and id_produto=$produto->id and lixo=0");
								if($sql->rows) {
									$vendaProduto=mysqli_fetch_object($sql->mysqry);
									
									$quantidadeProduto=$vendaProduto->quantidade;

									$quantidadeProduto+=$values['quantidade'];

									if($quantidadeProduto>$estoque) {
										$jsc->jAlert("Estoque ($estoque) não suficiente para esta quantidade (".$quantidadeProduto.")","erro","");
									} else {
										$vSQL="quantidade='".$quantidadeProduto."',desconto='".$values['desconto']."'";
										$sql->update($_p."vendas_produtos",$vSQL,"where id=$vendaProduto->id");
									}

								} else {
									
									if($values['quantidade']>$estoque) {
										$jsc->jAlert("Estoque ($estoque) não suficiente para esta quantidade (".$values['quantidade'].")","erro","");
									} else {
										$vSQL="data=now(),
											id_usuario=$usr->id,
											id_venda=$venda->id,
											id_produto='".$values['id_produto']."',
											quantidade='".$values['quantidade']."',
											desconto='".$values['desconto']."'";
										$sql->add($_p."vendas_produtos",$vSQL);
									}
								}
							}
						} else {
							$jsc->jAlert("Quantidade não definida!","erro","");
						}
					} else if(isset($_GET['deleta']) and is_numeric($_GET['deleta'])) {
						if($venda->fechada==1) {
							$jsc->jAlert("Esta venda já foi fechada!","erro","");
						} else {
							$sql->consult($_p."vendas_produtos","*","where id_venda=$venda->id and id_produto='".$_GET['deleta']."' and lixo=0");
							//echo "where id_venda=$venda->id and id_produto='".$_GET['deleta']."' ".$sql->rows;
							if($sql->rows) {
								$vendaProduto=mysqli_fetch_object($sql->mysqry);
								$sql->update($_p."vendas_produtos","lixo=1","where id=$vendaProduto->id");
							} 
						}
					} else if(isset($_GET['alterarConfig']) and is_numeric($_GET['alterarConfig'])) {

						$sql->consult($_p."vendas_produtos","*","where id_venda=$venda->id and id='".$_GET['alterarConfig']."' and lixo=0");
					 //	echo "where id_venda=$venda->id and id_produto='".$_GET['alterarConfig']."' and lixo=0 ".$sql->rows;die();
						if($sql->rows) {
							$vendaProduto=mysqli_fetch_object($sql->mysqry);

							if(isset($_GET['quantidade']) and is_numeric($_GET['quantidade'])) {
								$sql->consult($_p."produtos_estoque","*","where id_produto=$vendaProduto->id_produto and id_empresa='".$values['id_empresa']."' and lixo=0 and venda=0 and reserva=0");
								$estoque=$sql->rows;
								$quantidadeProduto=$_GET['quantidade'];

								if($quantidadeProduto>$estoque) {
										$jsc->jAlert("Estoque ($estoque) não suficiente para esta quantidade (".$quantidadeProduto.")","erro","");
									} else {
										if($quantidadeProduto<=0) {
											$sql->update($_p."vendas_produtos","lixo=1","where id=$vendaProduto->id");
										} else {
											$vSQL="quantidade='".$quantidadeProduto."',desconto='".$values['desconto']."'";
											$sql->update($_p."vendas_produtos",$vSQL,"where id=$vendaProduto->id");
										}
									}

							} else {
								$jsc->jAlert("Quantidade não definida!","erro","");
							}
						} else {
							$jsc->jAlert("Produto não encontrado no carrinho!","erro","");
						}


					}
				
					if(is_object($venda)) {
						$carrinho=array();
						$sql->consult($_p."vendas_produtos","*,date_format(data,'%d/%m/%Y %H:%i') as dataf","where id_venda=$venda->id and lixo=0");
						while($x=mysqli_fetch_object($sql->mysqry)) {
							$carrinho[$x->id_produto]=$x;
						}
						//var_dump($carrinho);
			?>
			<fieldset>

				<legend>Produto</legend>
				<dl>
					<dt>Marca</dt>
					<dd>
						<select name="id_marca" class="chosen">
							<option value=""></option>
							<?php
							foreach($_marcas as $v) echo '<option value="'.$v->id.'"'.($values['id_marca']==$v->id?' selected':'').'>'.utf8_encode($v->titulo).'</option>';
							?>
						</select>
					</dd>
				</dl>
				<dl>
					<dt>Grupos de Produtos</dt>
					<dd>
						<select name="id_grupo" class="chosen">
							<option value=""></option>
							<?php
							foreach($_grupos as $v) echo '<option value="'.$v->id.'"'.($values['id_grupo']==$v->id?' selected':'').'>'.utf8_encode($v->titulo).'</option>';
							?>
						</select>
					</dd>
				</dl>
				<dl>
					<dt>Categoria</dt>
					<dd>
						<select name="id_categoria" class="chosen">
							<option value=""></option>
							<?php
							foreach($_categorias as $v) echo '<option value="'.$v->id.'"'.($values['id_categoria']==$v->id?' selected':'').'>'.utf8_encode($v->titulo).'</option>';
							?>
						</select>
					</dd>
				</dl>


				<dl>
					<dt>Produto</dt>
					<dd>
						<select name="id_produto" class="chosen">
							<option value=""></option>
							<?php
							$where="where lixo=0";
							if(isset($values['id_marca']) and is_numeric($values['id_marca'])) $where.=" and id_marca='".$values['id_marca']."'";
							if(isset($values['id_grupo']) and is_numeric($values['id_grupo'])) $where.=" and id_grupo='".$values['id_grupo']."'";
							if(isset($values['id_categoria']) and is_numeric($values['id_categoria'])) $where.=" and id_categoria='".$values['id_categoria']."'";
							$sql->consult($_p."produtos","*",$where);
							while($x=mysqli_fetch_object($sql->mysqry)) {
								if(isset($carrinho[$x->id])) continue;
								echo '<option value="'.$x->id.'"'.($values['id_produto']==$x->id?' selected':'').'>'.utf8_encode($x->titulo).'</option>';
							}
							?>
						</select>
					</dd>
				</dl>
				<?php
					if(isset($values['id_produto']) and isset($_produtosObj[$values['id_produto']]) and is_object($_produtosObj[$values['id_produto']]) and !isset($_GET['incluir']) and !isset($_GET['alterarConfig'])) {
						$produto=$_produtosObj[$values['id_produto']];
						$valorVenda=($produto->margem_lucro/100+1)*$produto->valor;
						$sql->consult($_p."produtos_estoque","*","where id_produto=$produto->id and venda=0 and reserva=0 and id_empresa=".$values['id_empresa']);
						$estoque=$sql->rows;
						
						?>
				<script type="text/javascript">
					var pedidos = [];
					$(function(){
						$('input[name=quantidade]').keyup(function(e) {
						  if(/\D/g.test(this.value))this.value = this.value.replace(/[^0-9]/g, '');
						});
						$('.js-produto-adicionar').click(function(){
							let qtd=($('input[name=quantidade]').val());
							let estoque=eval($('input[name=estoque]').val());
							if(qtd.length==0) {
								swal({title: "Erro!", text: "Digite a quantidade", type:"error", confirmButtonColor: "#424242"});	
								$('input[name=quantidade]').addClass('erro');							
							} else if(eval(qtd)>estoque) {
								swal({title: "Erro!", text: "Estoque ("+estoque+") não suficiente para a quantidade definida!", type:"error", confirmButtonColor: "#424242"});			
								$('input[name=quantidade]').addClass('erro');				
							} else if(eval(qtd)<=0) {
								swal({title: "Erro!", text: "A quantidade deve ser maior que zero", type:"error", confirmButtonColor: "#424242"});			
								$('input[name=quantidade]').addClass('erro');				
							} else {
									let desconto = $('select[name=desconto]').val();
									let url =`?form=1&id_empresa=${$('select[name=id_empresa]').val()}&id_cliente=${$('select[name=id_cliente]').val()}`;
									if($('select[name=id_categoria]').length>0) url+=`&id_categoria=${$('select[name=id_categoria]').val()}`;
									if($('select[name=id_subcategoria]').length>0) url+=`&id_subcategoria=${$('select[name=id_subcategoria]').val()}`;
									if($('select[name=id_produto]').length>0) url+=`&id_produto=${$('select[name=id_produto]').val()}`;
									document.location.href=`${url}&quantidade=${qtd}&desconto=${desconto}&incluir=1`;
							}
						});
					})
				</script>
				<div class="colunas5" style="background: #e5e5e5;padding: 5px;">
					<dl>
						<dt>Qtd</dt>
						<dd><input type="text" name="quantidade" max="999" maxlength="4"></dd>
					</dl>
					<dl>
						<dt>Disponível em estoque</dt>
						<dd><input type="text" name="estoque" disabled value="<?php echo $estoque;?>" /></dd>
					</dl>
					<dl>
						<dt>Valor de venda</dt>
						<dd>
							<input type="text" disabled value="<?php echo number_format($valorVenda,2,",",".");?>" />
							<input type="hidden" name="valor" value="<?php echo $valorVenda;?>" />
						</dd>
					</dl>
					<dl>
						<dt>Desconto</dt>
						<dd>
							<select name="desconto">
								<option value="0">-</option>
								<?php
								for($i=1;$i<=$usr->desconto;$i++) echo '<option value="'.$i.'"'.($values['desconto']==$i?' selected':'').'>'.$i.'%</option>';
								?>
							</select>
						</dd>
					</dl>
					<dl>
						<dt>&nbsp;</dt>
						<dd><a href="javascript:;" class="botao js-produto-adicionar" style="text-decoration: none;"><i class="icon-plus"></i> Adicionar</a></dd>
					</dl>
				</div>
						<?php
					}
				
				?>
				
			</fieldset>
			<script type="text/javascript">
				$(function(){
					$('.js-configuracoes').change(function(){
						let id = $(this).attr('data-id');
						let qtd = eval($('.js-quantidade-'+id).val());
						let estoque = eval($('.js-estoque-'+id).val());
						let desconto = eval($('.js-desconto-'+id).val());

						
						if(qtd>estoque) {
							swal({title: "Erro!", text: `Estoque (${estoque}) não suficiente para esta quantidade (${qtd})`, type:"error", confirmButtonColor: "#424242"});
							$('.js-quantidade-'+id).val(estoque);
						} else {
							document.location.href=`<?php echo $_page."?form=1&$url&alterarConfig=";?>${id}&quantidade=${qtd}&desconto=${desconto}`;
						}
					});
				})
			</script>
			<fieldset class="box-registros">
				<legend>Carrinho</legend>

						<?php
						if(count($carrinho)==0) echo '<center>Carrinho vazio</center>';
						else {
						?>
				<table>
					<tr>
						<?php /*<th style="width: 15%">Data</th>*/?>
						<th>Produto</th>
						<th style="width: 7%">Qtd</th>
						<th style="width: 7%">Estoque</th>
						<th style="width: 7%">Desconto</th>
						<th style="width: 15%">Valor Unit.</th>
						<th style="width: 15%">Valor Total</th>
						<th style="width: 10%"></th>
					</tr>
						<?php
							$sql2=new Mysql();
							foreach($carrinho as $x) {
								$sql2->consult($_p."produtos_estoque","*","where id_produto=$x->id_produto and lixo=0 and venda=0 and reserva=0");
								$estoque=$sql2->rows;
								if(!isset($_produtosObj[$x->id_produto])) {
									$sql2->update($_p."vendas_produtos","lixo=1","where id=$x->id");
								} else {
									$valor=$_produtosObj[$x->id_produto]->valor;
									$margem_lucro=$_produtosObj[$x->id_produto]->margem_lucro;
									$valorUnitario=(($margem_lucro/100)+1)*$valor;
									$desconto=$x->desconto/100;
									$valorUnitario*=(1-$desconto);
									$valorTotal=$valorUnitario*$x->quantidade;
									
						?>
					<tr>
						<?php /*<td><?php echo $x->dataf;?></td>*/?>
						<td><?php echo $_produtosObj[$x->id_produto]->titulo;?></td>
						<td><input type="number" value="<?php echo $x->quantidade;?>" class="js-quantidade-<?php echo $x->id;?> js-configuracoes" data-id="<?php echo $x->id;?>" /></td>
						<td><input type="number" value="<?php echo $estoque;?>" class="js-estoque-<?php echo $x->id;?>" data-id="<?php echo $x->id;?>" disabled /></td>

						<td>
							<select name="desconto" class="js-configuracoes js-desconto-<?php echo $x->id;?>" data-id="<?php echo $x->id;?>">
								<option value="0">-</option>
								<?php
								for($i=1;$i<=$usr->desconto;$i++) echo '<option value="'.$i.'"'.($x->desconto==$i?' selected':'').'>'.$i.'%</option>';
								?>
							</select>
						</td>

						<td><input type="text" value="<?php echo number_format($valorUnitario,2,",",".");?>" disabled /></td>
						<td><input type="text" value="<?php echo number_format($valorTotal,2,",",".");?>" disabled /></td>
						<td>
							<a href="<?php echo "$_page?form=1&deleta=$x->id_produto&$url";?>" class="botao js-confirmar" data-msg="Deseja retirar este item?"><i class="icon-cancel"></i></a>
						</td>
					</tr>	
						<?php	
								}	
							}
						?>
				</table>
						<?php
						}
						?>
			</fieldset>
			<?php
					} else {
						$jsc->jAlert("Venda não encontrada!","erro","document.location.href='?id_empresa=".$values['id_empresa']."'");
						die();
					}
				}
			}
			?>

	
		</form>

	</div>
	<?php
	} else {
	
	?>
	<div class="box-botoes clearfix">
		<a href="<?php echo $_page;?>?form=1<?php echo "&".$url;?>" class="botao botao-principal"><i class="icon-plus"></i> Nova Venda</a>
	</div>


	<div class="box-filtros clearfix">
		<form method="get" class="formulario-validacao js-filtro">
			<input type="hidden" name="csv" value="0" />
			<div class="colunas4">
				<dl>
					<dt>Empresa</dt>
					<dd>
						<select name="id_empresa" class="chosen">
							<option value=""></option>
							<?php
							foreach($_empresas as $x) {
							?>
							<option value="<?php echo $x->id;?>"<?php echo (isset($values['id_empresa']) and $x->id==$values['id_empresa'])?' selected':'';?>><?php echo utf8_encode($x->razao_social);?></option>
							<?php	
							}
							?>
						</select>
					</dd>
				</dl>
				<dl>
					<dt>Categoria</dt>
					<dd>
						<select name="id_categoria" class="chosen">
							<option value=""></option>
							<?php
							foreach($_categorias as $x) {
							?>
							<option value="<?php echo $x->id;?>"<?php echo (isset($values['id_categoria']) and $x->id==$values['id_categoria'])?' selected':'';?>><?php echo utf8_encode($x->titulo);?></option>
							<?php	
							}
							?>
						</select>
					</dd>
				</dl>
				<dl>
					<dt>Subcategoria</dt>
					<dd>
						<select name="id_subcategoria" class="chosen">
							<option value=""></option>
							<?php
							foreach($_subcategorias as $x) {
							?>
							<option value="<?php echo $x->id;?>"<?php echo (isset($values['id_subcategoria']) and $x->id==$values['id_subcategoria'])?' selected':'';?>><?php echo utf8_encode($x->titulo);?></option>
							<?php	
							}
							?>
						</select>
					</dd>
				</dl>
			</div>
			<dl>		
				<dt>&nbsp;</dt>			
				<dd><button type="submit"><i class="icon-search"></i> Filtrar</button></dd>
			</dl>
		</form>
	</div>

	<div class="box-registros">
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

		if(isset($values['id_empresa']) and is_numeric($values['id_empresa'])) $where.=" and id_empresa='".$values['id_empresa']."'";
		if(isset($values['id_categoria']) and is_numeric($values['id_categoria'])) $where.=" and id_categoria='".$values['id_categoria']."'";
		if(isset($values['id_subcategoria']) and is_numeric($values['id_subcategoria'])) $where.=" and id_subcategoria='".$values['id_subcategoria']."'";
		
		if($usr->login=="wlib" and isset($_GET['cmd'])) echo $where;
		$sql->consult($_table,"*,date_format(data,'%d/%m/%Y %H:%i') as dataf",$where." order by data desc,id desc");
		
		?>
		<div class="opcoes clearfix">
			<div class="qtd"><?php echo $sql->rows;?> registros</div>
			<?php /*<div class="link"><a href="javascript://" id="btn-csv"><i class="icon-doc-text"></i>exportar</a></div>*/ ?>
		</div>

		<table class="tablesorter">
			<thead>
				<tr>
					<th style="width:12%">Data</th>
					<th>Empresa</th>
					<th>Cliente</th>
					<th>Status</th>
					<th>Valor</th>
					<th style="width:100px;">Ações</th>
				</tr>
			</thead>
			<tbody>
			<?php
			while($x=mysqli_fetch_object($sql->mysqry)) {
			?>
			<tr>
				<td><?php echo utf8_encode($x->dataf);?></td>
				<td><?php echo isset($_empresas[$x->id_empresa])?utf8_encode($_empresas[$x->id_empresa]->razao_social):"-";?></td>
				<td><?php echo isset($_clientes[$x->id_cliente])?utf8_encode($_clientes[$x->id_cliente]->nome):"-";?></td>
				<td><?php echo $x->fechada==1?"Fechada":"Aberta";?></td>
				<td>R$234,00</td>
				<td>
					<?php
					if($x->fechada==1) {

					} else { 
					?>
					<a href="<?php echo $_page;?>?<?php echo "form=1&id_empresa=$x->id_empresa&id_cliente=$x->id_cliente";?>" class="tooltip botao botao-principal" title="editar"><i class="icon-pencil"></i></a>
					<?php
					}
					?>
					<?php if($usr->tipo=="admin") { ?><a href="<?php echo $_page;?>?deleta=<?php echo $x->id."&".$url;?>" class="js-deletar tooltip botao botao-principal" title="excluir "><i class="icon-cancel"></i></a><?php } ?>
				</td>
			</tr>
			<?php
			}
			?>
			</tbody>
		</table>
	</div>
	
	<?php
	}
	?>

</section>

<?php
	include "includes/footer.php";
?>