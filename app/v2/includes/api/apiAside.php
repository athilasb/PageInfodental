<?php

	if(isset($_POST['ajax'])) {

		$dir="../../";
		require_once("../../lib/conf.php");
		require_once("../../usuarios/checa.php");

		$rtn = array();

		$_tableEspecialidades=$_p."parametros_especialidades";
		$_tablePlanos=$_p."parametros_planos";


		# Especialidades
			if($_POST['ajax']=="asEspecialidadesListar") {

				$regs=array();
				$sql->consult($_tableEspecialidades,"*","where lixo=0 order by titulo asc") ;
				while($x=mysqli_fetch_object($sql->mysqry)) {
					$regs[]=array('id'=>$x->id,
											'titulo'=>utf8_encode($x->titulo));
				}

				$rtn=array('success'=>true,
							'regs'=>$regs);
				
			} 

			else if($_POST['ajax']=="asEspecialidadesEditar") {

				$cnt='';
				if(isset($_POST['id']) and is_numeric($_POST['id'])) {
					$sql->consult($_tableEspecialidades,"*","where id='".addslashes($_POST['id'])."' and lixo=0");
					if($sql->rows) {
						$x=mysqli_fetch_object($sql->mysqry);
						$cnt=(object)array('id' =>$x->id,'titulo' =>utf8_encode($x->titulo));
					}
				}

				if(is_object($cnt)) {
					$rtn=array('success'=>true,
								'id'=>$cnt->id,
								'cnt'=>$cnt);
				} else {
					$rtn=array('success'=>false,'error'=>'Registro não encontrado!');
				}
				
			} 

			else if($_POST['ajax']=="asEspecialidadesPersistir") {

				$cnt='';
				if(isset($_POST['id']) and is_numeric($_POST['id'])) {
					$sql->consult($_tableEspecialidades,"*","where id='".addslashes($_POST['id'])."' and lixo=0");
					if($sql->rows) {
						$cnt=mysqli_fetch_object($sql->mysqry);
					}
				}

				$titulo=isset($_POST['titulo'])?addslashes(utf8_decode($_POST['titulo'])):'';

				if(empty($titulo)) $rtn=array('success'=>false,'error'=>'Título não preenchido!');
				else {


					$vSQL="titulo='$titulo'";

					if(is_object($cnt)) {
						$vWHERE="where id=$cnt->id";
						//$vSQL.=",alteracao_data=now(),id_alteracao=$usr->id";
						$sql->update($_tableEspecialidades,$vSQL,$vWHERE);
						$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='update',vsql='".addslashes($vSQL)."',vwhere='".addslashes($vWHERE)."',tabela='".$_tableEspecialidades."',id_reg='$cnt->id'");
					} else {
						$vSQL.=",data=now(),id_usuario=$usr->id";
						$sql->add($_tableEspecialidades,$vSQL);
						$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='update',vsql='".addslashes($vSQL)."',vwhere='',tabela='".$_tableEspecialidades."',id_reg='$sql->ulid'");

					}

					$rtn=array('success'=>true);
				}
			} 

			else if($_POST['ajax']=="asEspecialidadesRemover") { 
				$cnt='';
				if(isset($_POST['id']) and is_numeric($_POST['id'])) {
					$sql->consult($_tableEspecialidades,"*","where id='".$_POST['id']."'");
					if($sql->rows) {
						$cnt=mysqli_fetch_object($sql->mysqry);
					}
				}

				if(is_object($cnt)) {
					$vSQL="lixo=$usr->id";
					$vWHERE="where id=$cnt->id";
					$sql->update($_tableEspecialidades,$vSQL,$vWHERE);
						$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='delete',vsql='".addslashes($vSQL)."',vwhere='".addslashes($vWHERE)."',tabela='".$_tableEspecialidades."',id_reg='$cnt->id'");

					$rtn=array('success'=>true);
				} else {
					$rtn=array('success'=>false,'error'=>'Registro não encontrado!');
				}
			}
		# Planos
			else if($_POST['ajax']=="asPlanosListar") {

				$regs=array();
				$sql->consult($_tablePlanos,"*","where lixo=0 order by titulo asc") ;
				while($x=mysqli_fetch_object($sql->mysqry)) {
					$regs[]=array('id'=>$x->id,'titulo'=>utf8_encode($x->titulo));
				}

				$rtn=array('success'=>true,
							'regs'=>$regs);
				
			} 

			else if($_POST['ajax']=="asPlanosEditar") {

				$cnt='';
				if(isset($_POST['id']) and is_numeric($_POST['id'])) {
					$sql->consult($_tablePlanos,"*","where id='".addslashes($_POST['id'])."' and lixo=0");
					if($sql->rows) {
						$x=mysqli_fetch_object($sql->mysqry);
						$cnt=(object)array('id' =>$x->id,'titulo' =>utf8_encode($x->titulo));
					}
				}

				if(is_object($cnt)) {
					$rtn=array('success'=>true,
								'id'=>$cnt->id,
								'cnt'=>$cnt);
				} else {
					$rtn=array('success'=>false,'error'=>'Registro não encontrado!');
				}
				
			} 

			else if($_POST['ajax']=="asPlanosPersistir") {

				$cnt='';
				if(isset($_POST['id']) and is_numeric($_POST['id'])) {
					$sql->consult($_tablePlanos,"*","where id='".addslashes($_POST['id'])."' and lixo=0");
					if($sql->rows) {
						$cnt=mysqli_fetch_object($sql->mysqry);
					}
				}

				$titulo=isset($_POST['titulo'])?addslashes(utf8_decode($_POST['titulo'])):'';

				if(empty($titulo)) $rtn=array('success'=>false,'error'=>'Título não preenchido!');
				else {


					$vSQL="titulo='$titulo'";

					if(is_object($cnt)) {
						$vWHERE="where id=$cnt->id";
						//$vSQL.=",alteracao_data=now(),id_alteracao=$usr->id";
						$sql->update($_tablePlanos,$vSQL,$vWHERE);
						$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='update',vsql='".addslashes($vSQL)."',vwhere='".addslashes($vWHERE)."',tabela='".$_tablePlanos."',id_reg='$cnt->id'");
					} else {
						//$vSQL.=",data=now(),id_usuario=$usr->id";
						$sql->add($_tablePlanos,$vSQL);
						$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='update',vsql='".addslashes($vSQL)."',vwhere='',tabela='".$_tablePlanos."',id_reg='$sql->ulid'");

					}

					$rtn=array('success'=>true);
				}
			} 

			else if($_POST['ajax']=="asPlanosRemover") { 
				$cnt='';
				if(isset($_POST['id']) and is_numeric($_POST['id'])) {
					$sql->consult($_tablePlanos,"*","where id='".$_POST['id']."'");
					if($sql->rows) {
						$cnt=mysqli_fetch_object($sql->mysqry);
					}
				}

				if(is_object($cnt)) {
					$vSQL="lixo=$usr->id";
					$vWHERE="where id=$cnt->id";
					$sql->update($_tablePlanos,$vSQL,$vWHERE);
						$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='delete',vsql='".addslashes($vSQL)."',vwhere='".addslashes($vWHERE)."',tabela='".$_tablePlanos."',id_reg='$cnt->id'");

					$rtn=array('success'=>true);
				} else {
					$rtn=array('success'=>false,'error'=>'Registro não encontrado!');
				}
			}


		header("Content-type: application/json");
		echo json_encode($rtn);
		die();
	}

	if(isset($apiConfig['especialidade'])) {
?>
<script type="text/javascript">
	var asEspecialidades = [];

	const asEspecialidadesListar = (openAside) => {
		
		if(asEspecialidades) {
			$('.js-asEspecialidades-table tbody').html('');

			//$(`.js-id_plano option`).prop('disabled',false);


			let atualizaEspecialidade = $('select.ajax-id_especialidade')?1:0;
			let atualizaEspecialidadeId = 0;
			if(atualizaEspecialidade==1) {
				atualizaEspecialidadeId=$('select.ajax-id_especialidade').val();
				$('select.ajax-id_especialidade').find('option').remove();
				$('select.ajax-id_especialidade').append('<option value="">-</option>');
			}

			asEspecialidades.forEach(x=>{

				$(`.js-asEspecialidades-table tbody`).append(`<tr class="aside-open">
													<td><h1>${x.titulo}</h1></td>
													<td style="text-align:right;"><a href="javascript:;" class="button js-asEspecialidades-editar" data-id="${x.id}"><i class="iconify" data-icon="fluent:edit-24-regular"></i></a></td>
												</tr>`);

				if(atualizaEspecialidade==1) {
					sel=(atualizaEspecialidadeId==x.id)?' selected':'';
					$('select.ajax-id_especialidade').append(`<option value="${x.id}"${sel}>${x.titulo}</option>`);
				}
			});
			
			if(openAside===true) {
				$("#js-aside-asEspecialidades").fadeIn(100,function() {
					$("#js-aside-asEspecialidades .aside__inner1").addClass("active");
				});
			}

		} else {
			if(openAside===true) {
				$(".aside").fadeIn(100,function() {
						$(".aside .aside__inner1").addClass("active");
				});
			}
		}
	}

	const asEspecialidadesAtualizar = (openAside) => {	
		let data = `ajax=asEspecialidadesListar`;

		$.ajax({
			type:"POST",
			url:baseURLApiAside,
			data:data,
			success:function(rtn) {
				if(rtn.success) {
					asEspecialidades=rtn.regs;
					asEspecialidadesListar(openAside);
				}
			}
		})
	}
	
	const asEspecialidadesEditar = (id) => {
		let data = `ajax=asEspecialidadesEditar&id=${id}`;
		$.ajax({
			type:"POST",
			url:baseURLApiAside,
			data:data,
			success:function(rtn) {
				if(rtn.success) {
					reg=rtn.cnt

					$(`.js-asEspecialidades-id`).val(reg.id);
					$(`.js-asEspecialidades-titulo`).val(reg.titulo);

					
					$('.js-asEspecialidades-form').animate({scrollTop: 0},'fast');
					$('.js-asEspecialidades-submit').html(`<i class="iconify" data-icon="fluent:checkmark-12-filled"></i>`);
					$('.js-asEspecialidades-remover').show();

				} else if(rtn.error) {
					swal({title: "Erro!", text: rtn.error, type:"error", confirmButtonColor: "#424242"});
				} else {
					swal({title: "Erro!", text: "Algum erro ocorreu durante a edição deste registro!", type:"error", confirmButtonColor: "#424242"});
				}
			},
			error:function(){
				swal({title: "Erro!", text: "Algum erro ocorreu durante a edição deste registro!", type:"error", confirmButtonColor: "#424242"});
			}
		});
	}

	
	$(function(){

		asEspecialidadesAtualizar();

		$('.js-asEspecialidades-submit').click(function(){
			let obj = $(this);
			if(obj.attr('data-loading')==0) {

				let id = $(`.js-asEspecialidades-id`).val();
				let titulo = $(`.js-asEspecialidades-titulo`).val();

			

				if(titulo.length==0) {
					swal({title: "Erro!", text: "Digite a Especialidade", type:"error", confirmButtonColor: "#424242"});
				}  else {

					obj.html(`<span class="iconify" data-icon="eos-icons:loading"></span>`);
					obj.attr('data-loading',1);

					let data = `ajax=asEspecialidadesPersistir&id=${id}&titulo=${titulo}`;
					
					$.ajax({
						type:'POST',
						data:data,
						url:baseURLApiAside,
						success:function(rtn) {
							if(rtn.success) {
								asEspecialidadesAtualizar();	

								$(`.js-asEspecialidades-id`).val(0);
								$(`.js-asEspecialidades-titulo`).val(``);

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
						$('.js-asEspecialidades-remover').hide();
						obj.html(`<i class="iconify" data-icon="fluent:add-circle-24-regular"></i>`);
						obj.attr('data-loading',0);
					});

				}
			}
		})

		$('.js-asEspecialidades-table').on('click','.js-asEspecialidades-editar',function(){
			let id = $(this).attr('data-id');
			asEspecialidadesEditar(id);
		});

		$('.aside-especialidade').on('click','.js-asEspecialidades-remover',function(){
			let obj = $(this);

			if(obj.attr('data-loading')==0) {

				let id = $('.js-asEspecialidades-id').val();
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

							obj.html(`<span class="iconify" data-icon="eos-icons:loading"></span>`);
							obj.attr('data-loading',1);
							let data = `ajax=asEspecialidadesRemover&id=${id}`; 
							$.ajax({
								type:"POST",
								data:data,
								url:baseURLApiAside,
								success:function(rtn) {
									if(rtn.success) {
										$(`.js-asEspecialidades-id`).val(0);
										$(`.js-asEspecialidades-titulo`).val('');
										asEspecialidadesAtualizar();
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
								$('.js-asEspecialidades-remover').hide();
								obj.html('<i class="iconify" data-icon="fluent:delete-24-regular"></i>');
								obj.attr('data-loading',0);
								$(`.js-asEspecialidades-submit`).html(`<i class="iconify" data-icon="fluent:add-circle-24-regular"></i>`);
							});
						} else {   
							swal.close();   
						} 
					});
			}
		});

	});
</script>

<section class="aside aside-especialidade">
	<div class="aside__inner1">

		<header class="aside-header">
			<h1>Especialidade</h1>
			<a href="javascript:;" class="aside-header__fechar aside-close"><i class="iconify" data-icon="fluent:dismiss-24-filled"></i></a>
		</header>

		<form method="post" class="aside-content form js-asEspecialidades-form">
			<input type="hidden" class="js-asEspecialidades-id" />
			
			<dl>
				<dt>Título da Especialidade</dt>
				<dd>
					<input type="text" class="js-asEspecialidades-titulo" />
					<button type="button" class="js-asEspecialidades-submit button button_main" data-loading="0"><i class="iconify" data-icon="fluent:add-circle-24-regular"></i></button>
					<a href="javascript:;" class="button js-asEspecialidades-remover" data-loading="0" style="display:none;"><i class="iconify" data-icon="fluent:delete-24-regular"></i></a>
				</dd>
			</dl>
			<div class="list2" style="margin-top:2rem;">
					<table class="js-asEspecialidades-table">
						<thead>
							<tr>									
								<th>ESPECIALIDADE</th>
								<th></th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td><h1>Título da Especialidade</h1></td>
								<td style="text-align:right;"><a href="javascript:;" class="js-edit button" data-loading="0"><i class="iconify" data-icon="fluent:edit-24-regular"></i></a></td>
							</tr>								
						</tbody>
					</table>
				</div>
			</form>
		</form>
	</div>
</section>
<?php
	}
	if(isset($apiConfig['plano'])) {
?>
<script type="text/javascript">
	var asPlanos = [];

	const asPlanosListar = (openAside) => {
		
		if(asPlanos) {
			$('.js-asPlanos-table tbody').html('');

			let atualizaPlano = $('select.ajax-id_plano')?1:0;
			let atualizaPlanoId = 0;
			let planosDisabledIds = [];
			if(atualizaPlano==1) {

				$('select.ajax-id_plano option').each(function(index,el){
					if($(el).prop('disabled')===true) {
						planosDisabledIds.push($(el).val());
					}
				})
				atualizaPlanoId=$('select.ajax-id_plano').val();
				$('select.ajax-id_plano').find('option').remove();
				$('select.ajax-id_plano').append('<option value="">-</option>');
			}

			asPlanos.forEach(x=>{

				$(`.js-asPlanos-table tbody`).append(`<tr class="aside-open">
													<td><h1>${x.titulo}</h1></td>
													<td style="text-align:right;"><a href="javascript:;" class="button js-asPlanos-editar" data-id="${x.id}"><i class="iconify" data-icon="fluent:edit-24-regular"></i></a></td>
												</tr>`);

				if(atualizaPlano==1) {
					dis=planosDisabledIds.includes(x.id)?' disabled':'';
					sel=(atualizaPlanoId==x.id)?' selected':'';
					$('select.ajax-id_plano').append(`<option value="${x.id}"${sel}${dis}>${x.titulo}</option>`);
				}

			});
			
			if(openAside===true) {
				$("#js-aside-asPlano").fadeIn(100,function() {
					$("#js-aside-asPlano .aside__inner1").addClass("active");
				});
			}

		} else {
			if(openAside===true) {
				$(".aside").fadeIn(100,function() {
						$(".aside .aside__inner1").addClass("active");
				});
			}
		}
	}

	const asPlanosAtualizar = (openAside) => {	
		let data = `ajax=asPlanosListar`;

		$.ajax({
			type:"POST",
			url:baseURLApiAside,
			data:data,
			success:function(rtn) {
				if(rtn.success) {
					asPlanos=rtn.regs;
					asPlanosListar(openAside);
				}
			}
		})
	}
	
	const asPlanosEditar = (id) => {
		let data = `ajax=asPlanosEditar&id=${id}`;
		$.ajax({
			type:"POST",
			url:baseURLApiAside,
			data:data,
			success:function(rtn) {
				if(rtn.success) {
					reg=rtn.cnt

					$(`.js-asPlanos-id`).val(reg.id);
					$(`.js-asPlanos-titulo`).val(reg.titulo);

					
					$('.js-asPlanos-form').animate({scrollTop: 0},'fast');
					$('.js-asPlanos-submit').html(`<i class="iconify" data-icon="fluent:checkmark-12-filled"></i>`);
					$('.js-asPlanos-remover').show();

				} else if(rtn.error) {
					swal({title: "Erro!", text: rtn.error, type:"error", confirmButtonColor: "#424242"});
				} else {
					swal({title: "Erro!", text: "Algum erro ocorreu durante a edição deste registro!", type:"error", confirmButtonColor: "#424242"});
				}
			},
			error:function(){
				swal({title: "Erro!", text: "Algum erro ocorreu durante a edição deste registro!", type:"error", confirmButtonColor: "#424242"});
			}
		});
	}

	
	$(function(){

		asPlanosAtualizar();

		$('.js-asPlanos-submit').click(function(){
			let obj = $(this);
			if(obj.attr('data-loading')==0) {

				let id = $(`.js-asPlanos-id`).val();
				let titulo = $(`.js-asPlanos-titulo`).val();

			

				if(titulo.length==0) {
					swal({title: "Erro!", text: "Digite a Especialidade", type:"error", confirmButtonColor: "#424242"});
				}  else {

					obj.html(`<span class="iconify" data-icon="eos-icons:loading"></span>`);
					obj.attr('data-loading',1);

					let data = `ajax=asPlanosPersistir&id=${id}&titulo=${titulo}`;
					
					$.ajax({
						type:'POST',
						data:data,
						url:baseURLApiAside,
						success:function(rtn) {
							if(rtn.success) {
								asPlanosAtualizar();	

								$(`.js-asPlanos-id`).val(0);
								$(`.js-asPlanos-titulo`).val(``);

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
						$('.js-asPlanos-remover').hide();
						obj.html(`<i class="iconify" data-icon="fluent:add-circle-24-regular"></i>`);
						obj.attr('data-loading',0);
					});

				}
			}
		})

		$('.js-asPlanos-table').on('click','.js-asPlanos-editar',function(){
			let id = $(this).attr('data-id');
			asPlanosEditar(id);
		});

		$('.aside-plano').on('click','.js-asPlanos-remover',function(){
			let obj = $(this);

			if(obj.attr('data-loading')==0) {

				let id = $('.js-asPlanos-id').val();
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

							obj.html(`<span class="iconify" data-icon="eos-icons:loading"></span>`);
							obj.attr('data-loading',1);
							let data = `ajax=asPlanosRemover&id=${id}`; 
							$.ajax({
								type:"POST",
								data:data,
								url:baseURLApiAside,
								success:function(rtn) {
									if(rtn.success) {
										$(`.js-asPlanos-id`).val(0);
										$(`.js-asPlanos-titulo`).val('');
										asPlanosAtualizar();
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
								$('.js-asPlanos-remover').hide();
								obj.html('<i class="iconify" data-icon="fluent:delete-24-regular"></i>');
								obj.attr('data-loading',0);
								$(`.js-asPlanos-submit`).html(`<i class="iconify" data-icon="fluent:add-circle-24-regular"></i>`);
							});
						} else {   
							swal.close();   
						} 
					});
			}
		});

	});
</script>

<section class="aside aside-plano">
	<div class="aside__inner1">

		<header class="aside-header">
			<h1>Plano</h1>
			<a href="javascript:;" class="aside-header__fechar aside-close"><i class="iconify" data-icon="fluent:dismiss-24-filled"></i></a>
		</header>

		<form method="post" class="aside-content form">
			<input type="hidden" class="js-asPlanos-id" />
			
			<dl>
				<dt>Título do Plano</dt>
				<dd>
					<input type="text" class="js-asPlanos-titulo" />
					<button type="button" class="js-asPlanos-submit button button_main" data-loading="0"><i class="iconify" data-icon="fluent:add-circle-24-regular"></i></button>
					<a href="javascript:;" class="button js-asPlanos-remover" data-loading="0" style="display:none;"><i class="iconify" data-icon="fluent:delete-24-regular"></i></a>
				</dd>
			</dl>
			<div class="list2" style="margin-top:2rem;">
					<table class="js-asPlanos-table">
						<thead>
							<tr>									
								<th>PLANO</th>
								<th></th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td><h1>Título do Plano</h1></td>
								<td style="text-align:right;"><a href="" class="button"><i class="iconify" data-icon="fluent:edit-24-regular"></i></a></td>
							</tr>								
						</tbody>
					</table>
				</div>
			</form>
		</form>
	</div>
</section>
<?php
	}
?>