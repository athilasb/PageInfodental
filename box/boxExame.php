<?php
	require_once("../lib/conf.php");
	$dir="../";
	require_once("../usuarios/checa.php");

	if(isset($_POST['ajax'])) {
		$sql = new Mysql();

		$rtn = array();
		if($_POST['ajax']=="examePersistir") {

			$exame='';
			if(isset($_POST['id_exame']) and is_numeric($_POST['id_exame'])) {
				$sql->consult($_p."parametros_examedeimagem","*","where id='".$_POST['id_exame']."'");
				if($sql->rows) {
					$exame=mysqli_fetch_object($sql->mysqry);
				}
			}

			$vSQL="titulo='".addslashes(utf8_decode($_POST['titulo']))."',
				   	id_regiao='".$_POST['id_regiao']."',
					clinicas='".utf8_decode($_POST['clinicas'])."'";

			if(is_object($exame)) {
				$vWHERE="where id=$exame->id";
				$sql->update($_p."parametros_examedeimagem",$vSQL,$vWHERE);
				$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='update',vsql='".addslashes($vSQL)."',vwhere='".addslashes($vWHERE)."',tabela='".$_p."parametros_examedeimagem',id_reg='".$exame->id."'");
				$id_reg=$exame->id;
			} else {
				$sql->add($_p."parametros_examedeimagem",$vSQL);
				$id_reg=$sql->ulid;
				$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='insert',vsql='".addslashes($vSQL)."',tabela='".$_p."parametros_examedeimagem"."',id_reg='".$sql->ulid."'");
			}
			$rtn=array('success'=>true);
		} else if($_POST['ajax']=="exameRemover") {
			if(isset($_POST['id_exame']) and is_numeric($_POST['id_exame'])) {
				$sql->consult($_p."parametros_examedeimagem","*","where id='".$_POST['id_exame']."'");
				if($sql->rows) {
					$exame=mysqli_fetch_object($sql->mysqry);
				}
			}

			if(isset($exame) and is_object($exame)) {
				$vSQL="lixo=1";
				$vWHERE="where id=$exame->id";
				$sql->update($_p."parametros_examedeimagem",$vSQL,$vWHERE);

				$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='update',vwhere='".addslashes($vWHERE)."',vsql='".addslashes($vSQL)."',tabela='".$_p."parametros_examedeimagem',id_reg='".$exame->id."'");

				$rtn=array('success'=>true);

			} else {
				$rtn=array('success'=>false,'error'=>'Exame não encontrado');
			}
		}

		header("Content-type: application/json");
		echo json_encode($rtn);
		die();
	}
	$campos=explode(",","titulo,id_regiao");
		
	foreach($campos as $v) $values[$v]='';

	$jsc = new Js();
	$exame='';
	if(isset($_GET['id_exame']) and is_numeric($_GET['id_exame'])) {
		$sql->consult($_p."parametros_examedeimagem","*","where id='".$_GET['id_exame']."'");
		if($sql->rows) {
			$exame=mysqli_fetch_object($sql->mysqry);

			foreach($campos as $v) {
				$values[$v]=utf8_encode($exame->$v);
			}
		}
	}

	$_clinicas=array();
	$sql->consult($_p."parametros_fornecedores","*","where lixo=0 order by nome asc, razao_social");
	while($x=mysqli_fetch_object($sql->mysqry)) $_clinicas[$x->id]=$x;

	$_regioes=array();
	$sql->consult($_p."parametros_procedimentos_regioes","*","where lixo=0 order by titulo asc");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_regioes[$x->id]=$x;
	}
?>
<script>
	var id_exame = '<?php echo is_object($exame)?$exame->id:'';?>';
	$('.js-remover').click(function(){
		swal({   title: "Atenção",   text: "Você tem certeza que deseja remover este registro?",   type: "warning",   showCancelButton: true,   confirmButtonColor: "#DD6B55",   confirmButtonText: "Sim!",   cancelButtonText: "Não",   closeOnConfirm: false,   closeOnCancel: false }, function(isConfirm){   
			if (isConfirm) { 

				let data = `ajax=exameRemover&id_exame=${id_exame}`;   
				$.ajax({
					type:"POST",
					url:'box/boxExame.php',
					data:data,
					success:function(rtn){
						swal.close();  
						if(rtn.success) {
							$.fancybox.close();
							location.reload(true);
						} else if(rtn.error) {
							swal({title: "Erro!", text: rtn.error, type:"error", confirmButtonColor: "#424242"});
						} else {
							swal({title: "Erro!", text: "Exame não removido. Por favor tente novamente!", type:"error", confirmButtonColor: "#424242"});
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
			
			let campos = $('form.js-form-clinica').serialize();
			let data = `ajax=examePersistir&id_exame=${id_exame}&${campos}`;

			$.ajax({
				type:'POST',
				url:'box/boxExame.php',
				data:data,
				success:function(rtn) {
					if(rtn.success) {
						$.fancybox.close();
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
				if(empty($exame)) {
			?>
			<h1 class="filtros__titulo"></h1>
			<div class="filtros-acoes filter-button">
				<a href="javascript:;" class="azul js-salvar"><i class="iconify" data-icon="bx-bx-check"></i><span>salvar</span></a>
			</div>
			<?php
				} else {
			?>
			<h1 class="filtros__titulo">Editar</h1>
			
			<div class="filtros-acoes filter-button">
				<a href="javascript:;" class="js-remover"><i class="iconify" data-icon="bx-bx-trash"></i></a>
				<a href="javascript:;" class="azul js-salvar"><i class="iconify" data-icon="bx-bx-check"></i><span>salvar</span></a>
			</div>
			<?php
				}
			?>
		
		</div>
	</header>
	
	<article class="modal-conteudo">

		<form method="post" class="form js-form-clinica">
			<fieldset>
				<legend><span class="badge">1</span> Dados do Exame</legend>
				<div class="colunas4">
					<dl class="dl2">
						<dt>Nome do Exame</dt>
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
				</div>
			</fieldset>

			<fieldset>
				<legend><span class="badge">2</span> Clínicas que realizam</legend>
				<textarea name="clinicas" style="text-transform: none;display: none;"><?php echo isset($values['clinicas'])?$values['clinicas']:'';?></textarea>
				<script type="text/javascript">
					var clinicas = [];
					function clinicasListar() {
					
						$('.js-clinicas').remove();
						clinicas.forEach(x => {
							$('.js-laboratorio-id_laboratorio').find(`option[value=${x.id_laboratorio}]`).prop('disabled',true);

							var html = `<div class="reg-group js-clinicas">
											<div class="reg-color" style="background-color:green"></div>
											<div class="reg-data js-titulo" style="flex:0 1 300px">
												<h1>${x.laboratorio}</h1>
											</div>		
											<div class="reg-icon">
												<a href="javascript:;" class="js-tr-deleta"><i class="iconify" data-icon="bx-bx-trash"></i></a>
											</div>							
										</div>`;
							
							let tr = `<tr><td>${x.laboratorio}</td><td><a href="javascript:;" data-id="${x.id_laboratorio}" class="js-tr-deleta registros__acao registros__acao_sec"><i class="iconify" data-icon="bx:bxs-trash"></i></a></tr>`;

							$('.js-div-clinicas').append(html);
							$('.js-clinicas .reg-icon .js-tr-deleta:last').click(function() {
								let index = $(this).index('.js-clinicas .reg-icon .js-tr-deleta');
								swal({   title: "Atenção",   text: "Você tem certeza que deseja remover este registro?",   type: "warning",   showCancelButton: true,   confirmButtonColor: "#DD6B55",   confirmButtonText: "Sim!",   cancelButtonText: "Não",   closeOnConfirm: false,   closeOnCancel: false }, function(isConfirm){   if (isConfirm) {  clinicasRemover(index); swal.close();   } else {   swal.close();   } });
							});
						});

						let json = JSON.stringify(clinicas);
						$('textarea[name=clinicas]').val(json);
								
					}
					function clinicasRemover(index) {
						$('select.js-laboratorio-id_laboratorio').find(`option[value=${clinicas[index].id_laboratorio}]`).prop('disabled',false);

						clinicas.splice(index,1);
						clinicasListar();
					}
					$(function(){
						$('input.money').maskMoney({symbol:'', allowZero:false, showSymbol:true, thousands:'.', decimal:',', symbolStay: true});
						clinicasListar();

						<?php
						if(is_object($exame) and !empty($exame->clinicas)) {
							echo "clinicas=JSON.parse('".utf8_encode($exame->clinicas)."');";
							echo "clinicasListar();";
						} 
						?>

						$('.js-btn-add').click(function(){
							let laboratorio = $('select.js-laboratorio-id_laboratorio option:selected').text();
							let id_laboratorio = $('select.js-laboratorio-id_laboratorio').val();

							if(id_laboratorio.length==0) {
								swal({title: "Erro!", text: "Selecione a Clínica!", type:"error", confirmButtonColor: "#424242"});
								$('select.js-laboratorio-id_laboratorio').addClass('erro');
							} else {
								$('select.js-laboratorio-id_laboratorio').val(``);
								$('input.js-laboratorio-valor').val(``);
								$('input.js-laboratorio-id').val(0);

								let item = {};
      							item.laboratorio = laboratorio;
      							item.id_laboratorio = id_laboratorio;
      							
      							clinicas.push(item);
								clinicasListar();
								$('select.js-laboratorio-id_laboratorio option:selected').prop('selected',false);
	      						$('input.js-laboratorio-valor').val('');
							}
						});
					});
				</script>
				<div class="colunas4">
					<dl>
						<dt>Clínica</dt>
						<select class="js-laboratorio-id_laboratorio">
							<option value="">-</option>
							<?php
							foreach($_clinicas as $p) {
								$laboratorioTitulo='';
								if($p->tipo_pessoa=='PJ') $laboratorioTitulo=utf8_encode($p->razao_social);
								else $laboratorioTitulo=utf8_encode($p->nome);
								echo '<option value="'.$p->id.'">'.$laboratorioTitulo.'</option>';
							}
							?>
						</select>
					</dl>
					<dl>
						<dd><button type="button" class="button js-btn-add">Adicionar</button></dd>
					</dl>
				</div>
				<div class="reg js-div-clinicas" style="margin-top:2rem;"></div>
				<div class="registros">
					<table class="tablesorter js-clinicas">
						<thead>
							<tr>
								<th>Clínica</th>
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