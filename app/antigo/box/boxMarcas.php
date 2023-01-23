<?php
	require_once("../lib/conf.php");
	$dir="../";
	require_once("../usuarios/checa.php");

	$_table=$_p."produtos_marcas";
	$_urlAjax="box/boxMarcas.php";
	$_title="Marcas";
	
	$campos=explode(",","titulo");


	if(isset($_POST['ajax'])) {
		$rtn=array();

		if($_POST['ajax']=="listar") {

			$id_marca=0;
			if(isset($_POST['id_produto']) and is_numeric($_POST['id_produto'])) {
				$sql->consult($_p."produtos","*","where id='".addslashes($_POST['id_produto'])."' and lixo=0");
				if($sql->rows) {
					$x=mysqli_fetch_object($sql->mysqry);
					$id_marca = $x->id_marca;
				}
			}
			
			$lista=array();
			$sql->consult($_table,"*","where lixo=0 order by titulo asc");
			if($sql->rows) {
				while($x=mysqli_fetch_object($sql->mysqry)) {
					$lista[]=array('id'=>$x->id,'titulo'=>utf8_encode($x->titulo));
				}
			}

			$rtn=array('success'=>true,'lista'=>$lista,'id_marca'=>$id_marca);

		} else if($_POST['ajax']=="persistir") {

			$cnt='';
			if(isset($_POST['id']) and is_numeric($_POST['id'])) {
				$sql->consult($_table,"*","where id='".$_POST['id']."'");
				if($sql->rows) {
					$cnt=mysqli_fetch_object($sql->mysqry);
				}
			}

			$vSQL="";
			foreach($campos as $v) {
				if(isset($_POST[$v])) $vSQL.="$v='".addslashes(utf8_decode($_POST[$v]))."',";
			}

			$vSQL=substr($vSQL,0,strlen($vSQL)-1);

			if(is_object($cnt)) {
				$sql->update($_table,$vSQL,"WHERE id=$cnt->id");
			} else {
				$sql->add($_table,$vSQL);
			}

			$rtn=array('success'=>true);

		}  else if($_POST['ajax']=="consultar") {

			$cnt='';
			if(isset($_POST['id']) and is_numeric($_POST['id'])) {
				$sql->consult($_table,"*","where id='".$_POST['id']."'");
				if($sql->rows) {
					$cnt=mysqli_fetch_object($sql->mysqry);
				}
			}

			if(empty($cnt)) {
				$rtn=array('success'=>false,'error'=>'Registro não encontrado!');
			} else {

				$rtn=array('success'=>true,
							'id'=>(int)$cnt->id,
							'titulo'=>utf8_encode($cnt->titulo));
			}

		}  else if($_POST['ajax']=="deletar") {

			$cnt='';
			if(isset($_POST['id']) and is_numeric($_POST['id'])) {
				$sql->consult($_table,"*","where id='".$_POST['id']."'");
				if($sql->rows) {
					$cnt=mysqli_fetch_object($sql->mysqry);
				}
			}

			if(empty($cnt)) {
				$rtn=array('success'=>false,'error'=>'Registro não encontrado!');
			} else {

				$sql->update($_table,"lixo=1","where id=$cnt->id");

				$rtn=array('success'=>true);
			}

		}


		header("Content-type: application/json");
		echo json_encode($rtn);
		die();
	}		

?>
<script type="text/javascript">
	var lista = [];
	var id_produto = <?php echo isset($_GET['id_produto'])?addslashes($_GET['id_produto']):0;?>;
	var id_marca = 0;

	function atualizar(){
		let data = `ajax=listar`;
		$.ajax({
			type:"POST",
			data:data,
			url:'<?php echo $_urlAjax;?>',
			success:function(rtn){ 
				if(rtn.success) {
					lista=rtn.lista;
					id_marca=rtn.id_marca;
					listar();
				} else if(rtn.error) {
					swal({title: "Erro!", text: rtn.error, type:"error", confirmButtonColor: "#424242"});
				} else {
					swal({title: "Erro!", text: "Algum erro ocorreu durante a listagem", type:"error", confirmButtonColor: "#424242"});
				}
			},
			error:function(){
				swal({title: "Erro!", text: "Algum erro ocorreu!", type:"error", confirmButtonColor: "#424242"});
			}
		});
	}

	function listar(){

		$('table.js-table-registros tr.js-item').remove();
		$('select[name=id_marca]').find('option').remove();
		$('select[name=id_marca]').append(`<option value="">-</option>`);

		lista.forEach(x=>{
			let html = `<tr class="js-item">
							<td>${x.titulo}</td>
							<td>
								<a href="javascript:;" class="js-editar registros__acao registros__acao_sec" data-id="${x.id}"><i class="iconify" data-icon="bx:bxs-edit-alt"></i></a>
								<a href="javascript:;" class="js-deletar registros__acao registros__acao_sec" data-id="${x.id}"><i class="iconify" data-icon="bx:bxs-trash"></i></a>
							</td>
						</tr>`;

			$('.js-table-registros').append(html);
			$('select[name=id_marca]').append(`<option value="${x.id}">${x.titulo}</option>`);
		});
		$('select[name=id_marca]').find(`option[value=${id_marca}]`).prop('selected',true);
	}

	function deletar(id) {
		let data = `ajax=deletar&id=${id}`;

		$.ajax({
			type:"POST",
			url:'<?php echo $_urlAjax;?>',
			data:data,
			success:function(rtn) {
				if(rtn.success) {
					atualizar();
				} else if(rtn.error) {
					swal({title: "Erro!", text: rtn.error, type:"error", confirmButtonColor: "#424242"});
				} else {
					swal({title: "Erro!", text: "Algum erro ocorreu durante a listagem", type:"error", confirmButtonColor: "#424242"});
				}
			},
			error:function(){
				swal({title: "Erro!", text: "Algum erro ocorreu!", type:"error", confirmButtonColor: "#424242"});
			}
		})
	}

	$(function(){
		atualizar();

		$(`.js-btn-persistir`).click(function(){
			let titulo = $('input.js-inpt-titulo').val();
			let id = $('input.js-inpt-id').val();

			if(titulo.length==0) {
				swal({title: "Erro!", text: "Complete o campo de Título!", type:"error", confirmButtonColor: "#424242"});
			} else {
				let data = `ajax=persistir&titulo=${titulo}&id=${id}`;
				$.ajax({
					type:"POST",
					url:'<?php echo $_urlAjax;?>',
					data:data,
					success:function(rtn) {
						if(rtn.success) {
							atualizar();
							$('.js-form-registros').find('input,select,textarea').val('');
						} else if(rtn.error) {
							swal({title: "Erro!", text: rtn.error, type:"error", confirmButtonColor: "#424242"});
						} else {
							swal({title: "Erro!", text: "Algum erro ocorreu durante a listagem", type:"error", confirmButtonColor: "#424242"});
						}
					},
					error: function(){
						swal({title: "Erro!", text: "Algum erro ocorreu!", type:"error", confirmButtonColor: "#424242"});
					}
				})
			}
		});

		$(`.js-table-registros`).on('click','.js-editar',function(){
			let id=$(this).attr('data-id');
			let data = `ajax=consultar&id=${id}`;
			$.ajax({
				type:"POST",
				url:'<?php echo $_urlAjax;?>',
				data:data,
				success:function(rtn) {
					if(rtn.success) {
						$('input.js-inpt-titulo').val(rtn.titulo);
						$('input.js-inpt-id').val(rtn.id);
					} else if(rtn.error) {
						swal({title: "Erro!", text: rtn.error, type:"error", confirmButtonColor: "#424242"});
					} else {
						swal({title: "Erro!", text: "Algum erro ocorreu durante a listagem", type:"error", confirmButtonColor: "#424242"});
					}
				},
				error:function(){
					swal({title: "Erro!", text: "Algum erro ocorreu!", type:"error", confirmButtonColor: "#424242"});
				}
			})
		});

		$(`.js-table-registros`).on('click','.js-deletar',function(){
			var id=$(this).attr('data-id');

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
						deletar(id);
						swal.close(); 
					} else {   
						swal.close();   
					} 
			});

			
		});

	});
</script>

<section class="modal" style="width:700px;">

	<header class="modal-header">
		<div class="filtros">
			<h1 class="filtros__titulo">Marcas</h1>
		</div>
	</header>

	<article class="modal-conteudo">
		<form method="post" class="form js-form-registros">
			<input type="hidden" class="js-inpt-id"  />
			<dl>
				<dt>Título</dt>
				<dd>
					<input type="text" class="js-inpt-titulo" />
					<a href="javascript:;" class="button button__sec tooltip js-btn-persistir" title="Salvar"><i class="iconify" data-icon="bx-bx-plus"></i></a>
				</dd>
			</dl>
			<div class="registros">
				<table class="js-table-registros">
					<tr>
						<th>Título</th>
						<th style="width:110px;"></th>
					</tr>
				</table>
			</div>

		</form>
	</article>

</section>