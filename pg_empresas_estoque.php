<?php
	if(isset($_POST['ajax']) and $_POST['ajax']=="wlib") {
		require_once("lib/conf.php");
		require_once("usuarios/checa.php");

		$sql=new Mysql();

		$empresa='';
		$rtn=array();

		if(isset($_POST['id_empresa']) and is_numeric($_POST['id_empresa']) and is_object($_empresas[$_POST['id_empresa']])) {
			$empresa=$_empresas[$_POST['id_empresa']];
		}
		if(empty($empresa)) {
			$rtn['error']='Empresa não encontrada';
		} else {

			$produto='';
			if(isset($_POST['id_produto']) and is_numeric($_POST['id_produto'])) {
				$sql->consult($_p."produtos","*","where id='".$_POST['id_produto']."' and lixo=0");
				if($sql->rows) {
					$produto=mysqli_fetch_object($sql->mysqry);
				}
			}


			if(empty($produto)) {
				$rtn['error']='Produto não encontrado';
			} else {

				$margem_lucro=0;
				if(isset($_POST['margem_lucro'])) {
					$margem_lucro=addslashes($_POST['margem_lucro']);
				}
				if(isset($_POST['sel']) and $_POST['sel']==1) {
					$sql->consult($_p."empresas_produtos","*","where id_empresa=$empresa->id and id_produto=$produto->id");
					if($sql->rows) {
						$x=mysqli_fetch_object($sql->mysqry);
						$sql->update($_p."empresas_produtos","id_empresa=$empresa->id,id_produto=$produto->id,margem_lucro='".$margem_lucro."'","where id=$x->id");
					} else {
						$sql->add($_p."empresas_produtos","id_empresa=$empresa->id,id_produto=$produto->id,margem_lucro='".$margem_lucro."'");
					}
					$rtn['selected']=true;
				} else {
					$sql->del($_p."empresas_produtos","where id_empresa=$empresa->id and id_produto=$produto->id");
					$rtn['selected']=false;
				}
				$rtn['id_produto']=$produto->id;;
				$rtn['margem_lucro']=$margem_lucro;
				$rtn['success']=true;
			}

		}

		header('Content-Type: application/json');

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
<script>
	$(function(){
		$('.m-empresas').next().show();		
	});
</script>
<section id="conteudo">
	
	<div class="box-caminho">
		<a href="javascript" class="js-collapse"><span></span></a>
		<h1>Empresas <i class="icon-angle-right"></i> Estoque</h1>
	</div>
	
	<?php
	$_table=$_p."empresas";
	$_page=basename($_SERVER['PHP_SELF']);

	$empresa='';
	if(isset($values['id_empresa']) and is_numeric($values['id_empresa']) and isset($_empresas[$values['id_empresa']])) {
		$empresa=$_empresas[$values['id_empresa']];
	}
	?>

	<div class="box-filtros clearfix">
		<form method="get" class="formulario-validacao js-filtro" onsubmit="return false;">
			<input type="hidden" name="csv" value="0" />
			<div class="colunas4">
				<dl>
					<dt>Empresa</dt>
					<dd>
						<select name="id_empresa">
							<option value="">-</option>
							<?php
							foreach ($_empresas as $x) {
							?>
							<option value="<?php echo $x->id;?>"<?php echo (is_object($empresa) and $empresa->id==$x->id)?" selected": "";?>><?php echo utf8_encode($x->titulo);?></option>
							<?php
							}
							?>
						</select>
					</dd>
				</dl>
				<dl>
					<dt>Busca</dt>
					<dd><input type="text" name="busca" /></dd>
				</dl>
			</div>
		</form>
	</div>
	<script type="text/javascript">
		var busca = '';
		$(function(){
			$('select[name=id_empresa]').change(function(){
				let id_empresa=$(this).val();
				document.location.href=`<?php echo $_page;?>?id_empresa=${id_empresa}`;
			});
		})
	</script>
	<div class="box-registros">
		<?php
		
		if(is_object($empresa)) {

			$where="WHERE lixo='0'";
			$sql->consult($_p."produtos","*",$where." order by titulo");
			$_produtosEmpresa=array();
			$sql->consult($_p."empresas_produtos","*","where id_empresa='".$empresa->id."'");
			while($x=mysqli_fetch_object($sql->mysqry)) {
				$_produtosEmpresa[$x->id_produto]=$x;;
			}

			$_produtos=array();
			$sql->consult($_p."produtos","id,titulo,margem_lucro","where lixo=0 order by titulo");
			while($x=mysqli_fetch_object($sql->mysqry)) {

				$ep='';
				if(isset($_produtosEmpresa[$x->id])) {
					$ep=$_produtosEmpresa[$x->id];
				}

				$_produtos[]=array('id'=>$x->id,
									'titulo'=>utf8_encode($x->titulo),
									'margem_lucro'=>is_object($ep)?$ep->margem_lucro:$x->margem_lucro,
									'selected'=>is_object($ep)?true:false);
			}

		?>
		<script type="text/javascript">
			$(function(){

				/*var produtos = JSON.parse('<?php echo json_encode($_produtos);?>');
				var resultado='';

				

				const filtraProdutos = () => {
					busca = $('input[name=busca]').val().toLowerCase();
					if(busca.length<2) {
						return false;
					}
					keywords = busca.toLowerCase().split(' ');

					resultado = produtos.filter((el) => {
						return el.titulo.toLowerCase().indexOf(busca) > -1;
						//console.log(kerwords);
						//return el;
						//return el.titulo.toLowerCase().match(new RegExp(keywords.join("|"), "g"));
					});
					listaProdutos();
				}

				const listaProdutos = () => {
					$('.js-tabela-produtos tbody tr').remove();
					resultado.forEach(x => {
						$('.js-tabela-produtos tbody').append(`<tr data-id="${x.id}" data-sel="${x.selected===true?1:0}" class="js-tr-${x.id}">
																	<td class="js-select-produto">${x.titulo} - ${x.id}</td>
																	<td><input type="text" value="${x.margem_lucro}" class="js-margem" /></td>
																	<td><a href="pg_produtos.php?form=1&edita=${x.id}" target="_blank" class="botao"><i class="icon-pencil"></i> </a></td></tr>`);
						if(x.selected===true) {
							$('.js-tabela-produtos tbody tr:last').addClass('produtoSel');
						}
					});
				}

				const produtoSalva = (id,sel,margem_lucro) => {

					let data = `ajax=wlib&id_empresa=<?php echo $empresa->id;?>&id_produto=${id}&sel=${sel}&margem_lucro=${margem_lucro}`
					//alert(data);
					$(`.js-tr-${id}`).addClass('produtoLoading');
					$.ajax({
						type:'POST',
						data:data,
						success:function(rtn) {
							$(`.js-tr-${id}`).removeClass('produtoLoading');
							if(rtn.success) {
								produtos = produtos.map(function(elem) {
									if(elem.id==rtn.id_produto) {
										let el = [];
										el=elem
										el.margem_lucro=rtn.margem_lucro;
										el.selected=rtn.selected;
										return el;
									}
									return elem;
								});

							}
						},
						error:function(rtn) {

						}
					})
				}

				$('.js-btn-busca').click(filtraProdutos);
				$('input[name=busca]').keyup(filtraProdutos);

				$('.js-tabela-produtos').on('click','.js-select-produto',function(){
					let id = $(this).parent().attr('data-id');
					let sel = $(this).parent().attr('data-sel');
					let margem_lucro = $(this).parent().find('input.js-margem').val();;

					if(sel==1) {
						$(this).parent().attr('data-sel',0);
						$(this).parent().removeClass('produtoSel');
						sel=0;
					} else {
						$(this).parent().attr('data-sel',1);
						$(this).parent().addClass('produtoSel');
						sel=1;
					}
					produtoSalva(id,sel,margem_lucro);
				});

				$('.js-tabela-produtos').on('change','.js-margem',function(){

					let id = $(this).parent().parent().attr('data-id');
					let sel = $(this).parent().parent().attr('data-sel');
					let margem_lucro = $(this).val();
					produtoSalva(id,sel,margem_lucro);
				});*/

				$('input[name=busca]').keyup(function(){
					let val = $(this).val();

					keywords = val.toLowerCase().split(' ');
					$(".js-tabela-produtos tr").filter(function(index,el) {
					   let str = $(el).find('td.js-titulo').text();
					   str = str.toLowerCase();
					   if(str) {
					  	 if(str.indexOf(val) > -1) {
					  	 	$(el).show();
					  	 } else {

					  	 	$(el).hide();
					  	 }
						}
					});
				});

				$('.js-btn-salvar').click(function(){
					let id = $(this).attr('data-id');

					let qtd = $(`js-produto-${id}`).val();

					if(qtd.length==0) {

					} else {
						alert('ok');
					}
				});
			})
		</script>
		<style type="text/css">
			.produtoSel {
				background:#BBFFC6 !important;
			}
			.produtoLoading {
				background:#82B38A !important;
			}
			.js-tabela-produtos tr {
				line-height: 30px;
				cursor: pointer;
				font-size:16px;
			}
		</style>
		<table class="js-tabela-produtos">
			<thead>
				<tr>
					<th>Código</th>
					<th>Título</th>
					<th>Valor</th>
					<th>Lucro %</th>
					<th>Disponível</th>
					<th>Reservado</th>
					<th>Vendido</th>
					<th>Quantidade</th>
					<th></th>
				</tr>
			</thead>
			<tbody>
				<?php

				$produtos=array();
				$sql->consult($_p."empresas_produtos","*","where id_empresa=$empresa->id");
				if($sql->rows) {
					$empresaProdutosID=$empresaProdutos=array();;
					while($x=mysqli_fetch_object($sql->mysqry)) {
						$empresaProdutosID[]=$x->id_produto;
						$empresaProdutos[$x->id_produto]=$x;
					}


					$sql->consult($_p."produtos","*","where id IN (".implode(",",$empresaProdutosID).") order by lixo, titulo");
					while($x=mysqli_fetch_object($sql->mysqry)) {
						$produtos[$x->id]=$x;
					}

					$produtosEstoque=array();
					$sql->consult($_p."produtos_estoque","id,venda,reserva,id_produto","where id_produto IN (".implode(",",$empresaProdutosID).") and lixo=0"); 
					while($x=mysqli_fetch_object($sql->mysqry)) {
						if($x->venda==1) $produtosEstoque[$x->id_produto]['venda'][]=$x;
						else if($x->reserva==1) $produtosEstoque[$x->id_produto]['reserva'][]=$x;
						else $produtosEstoque[$x->id_produto]['disponivel'][]=$x;
						
					}
				}
				if(count($produtos)>0) {
					foreach($produtos as $x) {
						if(isset($empresaProdutos[$x->id])) {
							$ep=$empresaProdutos[$x->id];
							$estoque='';
							if(isset($produtosEstoque[$x->id])) { 
								$estoque=$produtosEstoque[$x->id];
							}
				?>
				<tr>
					<td><?php echo utf8_encode($x->id);?></td>
					<td class="js-titulo"><?php echo utf8_encode($x->titulo);?></td>
					<td style="text-align: right;"><?php echo number_format($x->valor,2,",",".");?></td>
					<td><?php echo utf8_encode($ep->margem_lucro);?></td>
					<td><?php echo (isset($estoque['disponivel']))?count($estoque['disponivel']):"-";?></td>
					<td><?php echo (isset($estoque['reserva']))?count($estoque['reserva']):"-";?></td>
					<td><?php echo (isset($estoque['venda']))?count($estoque['venda']):"-";?></td>
					<td><input type="text" class="js-produto-<?php echo $x->id;?>" value="" /></td>
					<td><a href="javascript:;" class="botao js-btn-salvar" data-id="<?php echo $x->id;?>"><i class="icon-ok"></i></a></td>

				</tr>
				<?php
						}
					}
				}
				?>
			</tbody>
		</table>
		<?php
		}
		?>
	</div>
	
</section>

<?php
	include "includes/footer.php";
?>