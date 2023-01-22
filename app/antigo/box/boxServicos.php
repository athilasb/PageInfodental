<?php
	require_once("../lib/conf.php");
	$dir="../";
	require_once("../usuarios/checa.php");

	if(isset($_POST['ajax'])) {
		$sql = new Mysql();

		$rtn = array();
		if($_POST['ajax']=="laboratoriosListar") {
			$servico='';
			if(isset($_POST['id_servico']) and is_numeric($_POST['id_servico'])) {
				$sql->consult($_p."parametros_servicosdelaboratorio","*","where id='".addslashes($_POST['id_servico'])."' and lixo=0");
				if($sql->rows) {
					$servico=mysqli_fetch_object($sql->mysqry);
				}
			}

			$_laboratorios=array();
			$_laboratoriosSel=array();
			$sql->consult($_p."parametros_fornecedores","*","where lixo=0 order by nome asc, razao_social");
			while($x=mysqli_fetch_object($sql->mysqry)) {
				$_laboratorios[$x->id]=$x;
				$_laboratoriosSel[]=array('id' => $x->id, 'razao_social' => $x->razao_social);
			}

			if(is_object($servico)) {
				$sql->consult($_p."parametros_servicosdelaboratorio_laboratorios","*","where id_servicodelaboratorio=$servico->id and lixo=0");
				$laboratorios=array();
				while($x=mysqli_fetch_object($sql->mysqry)) {
					$laboratorio=isset($_laboratorios[$x->id_fornecedor])?utf8_encode($_laboratorios[$x->id_fornecedor]->razao_social):"-";
					$laboratorios[]=array('id'=>$x->id,
									'laboratorio'=>$laboratorio,
									'id_fornecedor'=>$x->id_fornecedor,
									'valor'=>number_format($x->valor,2,",","."));
				}

				$rtn=array('success'=>true,'select'=>$_laboratoriosSel,'laboratorios'=>$laboratorios);
			} else {
				$rtn=array('success'=>false,'error'=>'Categoria não definida!');
			}
		} else if($_POST['ajax']=='laboratorioRemover') {

			$servico='';
			if(isset($_POST['id_servico']) and is_numeric($_POST['id_servico'])) {
				$sql->consult($_p."parametros_servicosdelaboratorio","*","where id='".$_POST['id_servico']."'");
				if($sql->rows) {
					$servico=mysqli_fetch_object($sql->mysqry);
				}
			}

			if(is_object($servico)) {
				$laboratorio='';
				if(isset($_POST['id_servico_laboratorio']) and is_numeric($_POST['id_servico_laboratorio'])) {
					$sql->consult($_p."parametros_servicosdelaboratorio_laboratorios","*","where id_fornecedor='".$_POST['id_servico_laboratorio']."' and id_servicodelaboratorio='".$servico->id."'");
					if($sql->rows) {
						$laboratorio=mysqli_fetch_object($sql->mysqry);
					}
				}
				if(is_object($laboratorio)) {

					$sql->update($_p."parametros_servicosdelaboratorio_laboratorios","lixo=$usr->id","where id=$laboratorio->id and id_servicodelaboratorio=$servico->id");

					$rtn=array('success'=>true);
				} else {
					$rtn=array("success"=>false,"error"=>"Laboratório não encontrado!");
				}
			} else {
				$rtn=array("success"=>false,"error"=>"Laboratório não encontrado!");
			}
		} else if($_POST['ajax']=="servicoPersistir") {

			$servico='';
			if(isset($_POST['id_servico']) and is_numeric($_POST['id_servico'])) {
				$sql->consult($_p."parametros_servicosdelaboratorio","*","where id='".$_POST['id_servico']."'");
				if($sql->rows) {
					$servico=mysqli_fetch_object($sql->mysqry);
				}
			}

			$vSQL="titulo='".addslashes(utf8_decode($_POST['titulo']))."',
				   	id_regiao='".$_POST['id_regiao']."',
				   	tipo_material='".addslashes(utf8_decode($_POST['tipo_material']))."',
					laboratorios='".utf8_decode($_POST['laboratorios'])."'";

			if(is_object($servico)) {
				$vWHERE="where id=$servico->id";
				$sql->update($_p."parametros_servicosdelaboratorio",$vSQL,$vWHERE);
				$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='update',vsql='".addslashes($vSQL)."',vwhere='".addslashes($vWHERE)."',tabela='".$_p."parametros_servicosdelaboratorio',id_reg='".$servico->id."'");
				$id_reg=$servico->id;
			} else {
				$sql->add($_p."parametros_servicosdelaboratorio",$vSQL);
				$id_reg=$sql->ulid;
				$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='insert',vsql='".addslashes($vSQL)."',tabela='".$_p."parametros_servicosdelaboratorio"."',id_reg='".$sql->ulid."'");
			}

			$id_servico=$id_reg;
			if(isset($_POST['laboratorios']) and !empty($_POST['laboratorios'])) {
				$labs=json_decode($_POST['laboratorios']);

				foreach($labs as $v) {

					$vsql="id_fornecedor='$v->id_laboratorio',
							id_servicodelaboratorio='$id_servico',
							valor='".valor($v->valor)."',
							lixo=0";

					$sql->consult($_p."parametros_servicosdelaboratorio_laboratorios","*","where id_fornecedor=$v->id_laboratorio and id_servicodelaboratorio=$id_reg");
					if($sql->rows) {
						$x=mysqli_fetch_object($sql->mysqry);
						$sql->update($_p."parametros_servicosdelaboratorio_laboratorios",$vsql,"where id=$x->id");
					} else {
						$sql->add($_p."parametros_servicosdelaboratorio_laboratorios",$vsql);
					}
				}
			}
			$rtn=array('success'=>true);
		} else if($_POST['ajax']=="servicoRemover") {
			if(isset($_POST['id_servico']) and is_numeric($_POST['id_servico'])) {
				$sql->consult($_p."parametros_servicosdelaboratorio","*","where id='".$_POST['id_servico']."'");
				if($sql->rows) {
					$servico=mysqli_fetch_object($sql->mysqry);
				}
			}

			if(isset($servico) and is_object($servico)) {
				$vSQL="lixo=1";
				$vWHERE="where id=$servico->id";
				$sql->update($_p."parametros_servicosdelaboratorio",$vSQL,$vWHERE);

				$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='update',vwhere='".addslashes($vWHERE)."',vsql='".addslashes($vSQL)."',tabela='".$_p."parametros_servicosdelaboratorio',id_reg='".$servico->id."'");

				$rtn=array('success'=>true);

			} else {
				$rtn=array('success'=>false,'error'=>'Serviço não encontrado');
			}
		} 

		header("Content-type: application/json");
		echo json_encode($rtn);
		die();
	}
	$campos=explode(",","titulo,id_regiao,tipo_material");
		
	foreach($campos as $v) $values[$v]='';
	$values['tipo_pessoa']='pf';

	$jsc = new Js();
	$servico='';
	if(isset($_GET['id_servico']) and is_numeric($_GET['id_servico'])) {
		$sql->consult($_p."parametros_servicosdelaboratorio","*","where id='".$_GET['id_servico']."'");
		if($sql->rows) {
			$servico=mysqli_fetch_object($sql->mysqry);

			foreach($campos as $v) {
				$values[$v]=utf8_encode($servico->$v);
			}
		}
	}

	$_laboratorios=array();
	$sql->consult($_p."parametros_fornecedores","*","where lixo=0 order by nome asc, razao_social");
	while($x=mysqli_fetch_object($sql->mysqry)) $_laboratorios[$x->id]=$x;

	$_regioes=array();
	$sql->consult($_p."parametros_procedimentos_regioes","*","where lixo=0 order by titulo asc");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_regioes[$x->id]=$x;
	}

	$_materiais=array();
	$sql->consult($_p."parametros_servicosdelaboratorio_materiais","*","where lixo=0 order by titulo asc");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_materiais[$x->id]=$x;
	}
?>
<script>
	var id_servico = '<?php echo is_object($servico)?$servico->id:'';?>';
	$('.js-remover').click(function(){
		swal({   title: "Atenção",   text: "Você tem certeza que deseja remover este registro?",   type: "warning",   showCancelButton: true,   confirmButtonColor: "#DD6B55",   confirmButtonText: "Sim!",   cancelButtonText: "Não",   closeOnConfirm: false,   closeOnCancel: false }, function(isConfirm){   
			if (isConfirm) { 

				let data = `ajax=servicoRemover&id_servico=${id_servico}`;   
				$.ajax({
					type:"POST",
					url:'box/boxServicos.php',
					data:data,
					success:function(rtn){
						swal.close();  
						if(rtn.success) {
							$.fancybox.close();
							location.reload(true);
						} else if(rtn.error) {
							swal({title: "Erro!", text: rtn.error, type:"error", confirmButtonColor: "#424242"});
						} else {
							swal({title: "Erro!", text: "Serviço não removido. Por favor tente novamente!", type:"error", confirmButtonColor: "#424242"});
						}
					},
					error:function(){
						swal.close();  
						swal({title: "Erro!", text: "Serviço não removido. Por favor tente novamente!", type:"error", confirmButtonColor: "#424242"});
					}
				})
			} else {   
				swal.close();   
			} 
		});
	});
	$('.js-salvar').click(function(){

		let erro=false;
		$('form .obg').each(function(index,elem){
			if($(this).attr('name')!==undefined && $(this).val().length==0) {
				$(elem).addClass('erro');
				erro=true;
			}
		});

		if(erro===true) {
			swal({title: "Erro!", text: "Complete os campos destacados", type:"error", confirmButtonColor: "#424242"});
			
		} else {
			
			let campos = $('form.js-form-servico').serialize();
			let data = `ajax=servicoPersistir&id_servico=${id_servico}&${campos}`;

			$.ajax({
				type:'POST',
				url:'box/boxServicos.php',
				data:data,
				success:function(rtn) {
					if(rtn.success) {
						document.location.href=`pg_configuracao_procedimentos_servicos.php`
					} else if(rtn.error) {
						swal({title: "Erro!", text: rtn.error, type:"error", confirmButtonColor: "#424242"});
					} else {
						swal({title: "Erro!", text: "Serviço não salvo. Por favor tente novamente!", type:"error", confirmButtonColor: "#424242"});
					}
				},
				error:function(){
					swal({title: "Erro!", text: "Serviço não salvo. Por favor tente novamente!", type:"error", confirmButtonColor: "#424242"});
				}
			})

		}
		return false;
	});
</script>
<section class="modal" style="height:auto; width:950px;">

	<header class="modal-header">
		<div class="filtros">

			<?php
				if(empty($servico)) {
			?>
			<h1 class="filtros__titulo"></h1>
			<div class="filtros-acoes filter-button">
				<a href="javascript:;" class="azul js-salvar"><i class="iconify" data-icon="bx-bx-check"></i><span>Salvar</span></a>
			</div>
			<?php
				} else {
			?>
			<h1 class="filtros__titulo">Editar</h1>
			
			<div class="filtros-acoes filter-button">
				<a href="javascript:;" class="js-remover"><i class="iconify" data-icon="bx-bx-trash"></i></a>
				<a href="javascript:;" class="azul js-salvar"><i class="iconify" data-icon="bx-bx-check"></i><span>Salvar</span></a>
			</div>
			<?php
				}
			?>
		
		</div>
	</header>
	
	<article class="modal-conteudo">

		<form method="post" class="form js-form-servico">
			<fieldset>
				<legend>Dados do Serviço</legend>
				<div class="colunas4">
					<dl class="dl2">
						<dt>Nome do Serviço</dt>
						<dd>
							<input type="text" name="titulo" value="<?php echo $values['titulo'];?>"  class="obg" />
						</dd>
					</dl>
					<dl>
						<dt>Região</dt>
						<dd>
							<select name="id_regiao">
								<option value="">-</option>
								<?php
								foreach($_regioes as $v) {
									echo  '<option value="'.$v->id.'"'.($v->id==$values['id_regiao']?' selected':'').'>'.utf8_encode($v->titulo).'</option>';
								}
								?>
							</select>
						</dd>
					</dl>
					<dl>
						<dt>Tipo de Material</dt>
						<dd>
							<select name="tipo_material">
								<option value="">-</option>
								<?php
								foreach($_materiais as $v) {
									echo  '<option value="'.$v->id.'"'.($v->id==$values['tipo_material']?' selected':'').'>'.utf8_encode($v->titulo).'</option>';
								}
								?>
							</select>
						</dd>
					</dl>
					<dl>
						<dd><a href="box/boxNovoMaterial.php" data-fancybox data-type="ajax" class="button button__sec"><i class="iconify" data-icon="bx-bx-plus"></i></a></dd>
					</dl>
				</div>
			</fieldset>

			<fieldset>
				<legend>Serviços por Laboratório</legend>
				<textarea name="laboratorios" style="text-transform: none;display: none;"><?php echo isset($values['laboratorios'])?$values['laboratorios']:'';?></textarea>
				<script type="text/javascript">
					var laboratorios = [];
					function laboratoriosListar() {
					
						$('table.js-laboratorios-table tbody tr').remove();
						laboratorios.forEach(x => {
							$('.js-laboratorio-id_laboratorio').find(`option[value=${x.id_laboratorio}]`).prop('disabled',true);
							
							let tr = `<tr><td>${x.laboratorio}</td><td>${x.valor}</td><td><a href="javascript:;" data-id="${x.id_laboratorio}" class="js-remover registros__acao registros__acao_sec"><i class="iconify" data-icon="bx:bxs-trash"></i></a></tr>`;
							$('.js-laboratorios-table tbody').append(tr);
						});

						let json = JSON.stringify(laboratorios);
						$('textarea[name=laboratorios]').val(json);
								
					}
					function laboratoriosRemover(index) {
						$('select.js-laboratorio-id_laboratorio').find(`option[value=${laboratorios[index].id_laboratorio}]`).prop('disabled',false);

						laboratorios.splice(index,1);
						laboratoriosListar();
					}
					$(function(){
						$('input.money').maskMoney({symbol:'', allowZero:false, showSymbol:true, thousands:'.', decimal:',', symbolStay: true});
						laboratoriosListar();

						<?php
						if(is_object($servico) and !empty($servico->laboratorios)) {
							echo "laboratorios=JSON.parse('".utf8_encode($servico->laboratorios)."');";
							echo "laboratoriosListar();";
						} 
						?>

						$('.js-add-laboratorio').click(function(){
							let laboratorio = $('select.js-laboratorio-id_laboratorio option:selected').text();
							let id_laboratorio = $('select.js-laboratorio-id_laboratorio').val();
							let valor = $('input.js-laboratorio-valor').val();

							if(id_laboratorio.length==0) {
								swal({title: "Erro!", text: "Selecione o Laboratório!", type:"error", confirmButtonColor: "#424242"});
								$('select.js-laboratorio-id_laboratorio').addClass('erro');
							} else if(valor.length==0) {
								swal({title: "Erro!", text: "Defina o Valor!", type:"error", confirmButtonColor: "#424242"});
								$('input.js-laboratorio-valor').addClass('erro');
							} else {
								$('select.js-laboratorio-id_laboratorio').val(``);
								$('input.js-laboratorio-valor').val(``);
								$('input.js-laboratorio-id').val(0);

								let item = {};
      							item.laboratorio = laboratorio;
      							item.id_laboratorio = id_laboratorio;
      							item.valor = valor;
      							
      							laboratorios.push(item);
								laboratoriosListar();
								$('select.js-laboratorio-id_laboratorio option:selected').prop('selected',false);
	      						$('input.js-laboratorio-valor').val('');
							}
						});

						<?php
							if(is_object($servico)) {
						?>
						$('.js-laboratorios-table').on('click','.js-remover',function(){
							let index = $(this).index('.js-laboratorios-table .js-remover');
							let id_servico_laboratorio = $(this).attr('data-id');
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
										let data = `ajax=laboratorioRemover&id_servico=${id_servico}&id_servico_laboratorio=${id_servico_laboratorio}`; 
										$.ajax({
											type:"POST",
											data:data,
											url:'box/boxServicos.php',
											success:function(rtn) {
												if(rtn.success) {
													laboratoriosRemover(index);
													swal.close();   
												} else if(rtn.error) {
													swal({title: "Erro!", text: rtn.error, type:"error", confirmButtonColor: "#424242"});
												} else {
													swal({title: "Erro!", text: "Algum erro ocorreu durante a remoção deste Laboratório!", type:"error", confirmButtonColor: "#424242"});
												}

											},
											error:function(){
												swal({title: "Erro!", text: "Algum erro ocorreu durante a remoção deste Laboratório!", type:"error", confirmButtonColor: "#424242"});
											}
										})
									} else {   
										swal.close();   
									} 
								});
						});
						<?php
							}
						?>

					});
				</script>
				<div class="colunas4">
					<dl>
						<dt>Laboratório</dt>
						<select class="js-laboratorio-id_laboratorio">
							<option value="">-</option>
							<?php
							foreach($_laboratorios as $p) {
								$laboratorioTitulo='';
								if($p->tipo_pessoa=='PJ') $laboratorioTitulo=utf8_encode($p->razao_social);
								else $laboratorioTitulo=utf8_encode($p->nome);
								echo '<option value="'.$p->id.'">'.$laboratorioTitulo.'</option>';
							}
							?>
						</select>
					</dl>
					<dl>
						<dt>Valor</dt>
						<dd><input type="text" class="js-laboratorio-valor money" /></dd>
					</dl>
					<dl>
						<dd><button type="button" class="button js-add-laboratorio"><i class="iconify" data-icon="ic-baseline-add"></i> Adicionar</button></dd>
					</dl>
				</div>
				<div class="registros">
					<table class="tablesorter js-laboratorios-table">
						<thead>
							<tr>
								<th>Laboratório</th>
								<th>Valor</th>
								<th style="width:120px"></th>
							</tr>
						</thead>
						<tbody>
							
						</tbody>
					</table>
				</div>
				

			</fieldset>
				
		</form>
	</article>

</section>