<?php

	if(isset($_POST['ajax'])) {

		$dir="../../";
		require_once("../../lib/conf.php");
		require_once("../../usuarios/checa.php");

		$rtn = array();

		$_tableEspecialidades=$_p."parametros_especialidades";
		$_tablePlanos=$_p."parametros_planos";
		$_tableMarcas=$_p."produtos_marcas";
		$_tablePacientes=$_p."pacientes";


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

		# Marcas
			else if($_POST['ajax']=="asMarcasListar") {

				$regs=array();
				$sql->consult($_tableMarcas,"*","where lixo=0 order by titulo asc") ;
				while($x=mysqli_fetch_object($sql->mysqry)) {
					$regs[]=array('id'=>$x->id,'titulo'=>utf8_encode($x->titulo));
				}

				$rtn=array('success'=>true,
							'regs'=>$regs);
				
			} 

			else if($_POST['ajax']=="asMarcasEditar") {

				$cnt='';
				if(isset($_POST['id']) and is_numeric($_POST['id'])) {
					$sql->consult($_tableMarcas,"*","where id='".addslashes($_POST['id'])."' and lixo=0");
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

			else if($_POST['ajax']=="asMarcasPersistir") {

				$cnt='';
				if(isset($_POST['id']) and is_numeric($_POST['id'])) {
					$sql->consult($_tableMarcas,"*","where id='".addslashes($_POST['id'])."' and lixo=0");
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
						$sql->update($_tableMarcas,$vSQL,$vWHERE);
						$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='update',vsql='".addslashes($vSQL)."',vwhere='".addslashes($vWHERE)."',tabela='".$_tableMarcas."',id_reg='$cnt->id'");
					} else {
						//$vSQL.=",data=now(),id_usuario=$usr->id";
						$sql->add($_tableMarcas,$vSQL);
						$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='update',vsql='".addslashes($vSQL)."',vwhere='',tabela='".$_tableMarcas."',id_reg='$sql->ulid'");

					}

					$rtn=array('success'=>true);
				}
			} 

			else if($_POST['ajax']=="asMarcasRemover") { 
				$cnt='';
				if(isset($_POST['id']) and is_numeric($_POST['id'])) {
					$sql->consult($_tableMarcas,"*","where id='".$_POST['id']."'");
					if($sql->rows) {
						$cnt=mysqli_fetch_object($sql->mysqry);
					}
				}

				if(is_object($cnt)) {
					$vSQL="lixo=$usr->id";
					$vWHERE="where id=$cnt->id";
					$sql->update($_tableMarcas,$vSQL,$vWHERE);
						$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='delete',vsql='".addslashes($vSQL)."',vwhere='".addslashes($vWHERE)."',tabela='".$_tableMarcas."',id_reg='$cnt->id'");

					$rtn=array('success'=>true);
				} else {
					$rtn=array('success'=>false,'error'=>'Registro não encontrado!');
				}
			}

		# Pacientes
			else if($_POST['ajax']=="asPacientePersistir") {


				$nome=isset($_POST['nome'])?addslashes(utf8_decode($_POST['nome'])):'';
				$cpf=isset($_POST['cpf'])?addslashes(telefone($_POST['cpf'])):'';
				$telefone1=isset($_POST['telefone1'])?addslashes(telefone($_POST['telefone1'])):'';
				$indicacao_tipo=isset($_POST['indicacao_tipo'])?addslashes(utf8_decode($_POST['indicacao_tipo'])):'';
				$indicacao=isset($_POST['indicacao'])?addslashes(utf8_decode($_POST['indicacao'])):'';

				if(empty($nome)) $rtn=array('success'=>false,'error'=>'Preencha o nome do Paciente!');
				else if(empty($telefone1)) $rtn=array('success'=>false,'error'=>'Preencha o whatsapp do Paciente!');
				else if(!empty($cpf) && strlen($cpf)!=11) $rtn=array('success'=>false,'error'=>'Digite um CPF válido!');
				else {

					$vSQL="nome='$nome',
							cpf='$cpf',
							telefone1='$telefone1',
							indicacao_tipo='$indicacao_tipo',
							indicacao='$indicacao',
							data=now(),
							id_usuario=$usr->id";


					$erro='';
					if(!empty($cpf)) {
						$where="where cpf = '".$cpf."' and lixo=0";
						$sql->consult($_tablePacientes,"id",$where);
						
						if($sql->rows) {
							$erro="Já existe paciente cadastrado com esse CPF!";
						}
					}

					if(empty($erro)) {

						$sql->add($_tablePacientes,$vSQL);
						$id_paciente=$sql->ulid;
						$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='update',vsql='".addslashes($vSQL)."',vwhere='',tabela='".$_tableMarcas."',id_reg='$id_paciente'");

					
						$rtn=array('success'=>true,
									'id_paciente'=>$id_paciente,
									'nome'=>utf8_encode($nome));
					} else {
						$rtn=array('success'=>false,'error'=>$erro);
					}
				}
			} 

		# Pacientes Relacionamento
			else if($_POST['ajax']=="asRelacionamentoPaciente") {

				$_profissionais=array();
				$sql->consult($_p."colaboradores","id,nome,calendario_iniciais,foto,calendario_cor,check_agendamento","where lixo=0 order by nome asc");
				while($x=mysqli_fetch_object($sql->mysqry)) $_profissionais[$x->id]=$x;

				$paciente = '';
				if(isset($_POST['id_paciente']) and is_numeric($_POST['id_paciente'])) {
					$sql->consult($_p."pacientes","id,nome,data_nascimento,telefone1,codigo_bi,musica,periodicidade","where id=".$_POST['id_paciente']);
					if($sql->rows) $paciente=mysqli_fetch_object($sql->mysqry);
				}


				if(is_object($paciente)) {

					if($paciente->data_nascimento!="0000-00-00") {
						$dob = new DateTime($paciente->data_nascimento);
						$now = new DateTime();
						$idade = $now->diff($dob)->y;
					} else $idade=0;
				

					$agendamentosFuturos=array();
					$sql->consult($_p."agenda","*","where id_paciente=$paciente->id and agenda_data>'".date('Y-m-d')."' and id_status IN (1,2) and lixo=0 order by agenda_data");

					while($x=mysqli_fetch_object($sql->mysqry)) {

						$cor='';
						$iniciais='';

						$aux = explode(",",$x->profissionais);
						$profissionais=array();
						foreach($aux as $id_profissional) {
							if(!empty($id_profissional) and is_numeric($id_profissional)) {

								if(isset($_profissionais[$id_profissional])) {
									$cor=$_profissionais[$id_profissional]->calendario_cor;
									$iniciais=$_profissionais[$id_profissional]->calendario_iniciais;

									$profissionais[]=array('iniciais'=>$iniciais,'cor'=>$cor);
								}
							}

						}

						$agendamentosFuturos[]=array('id_agenda'=>$x->id,
													'data'=>date('d/m/Y H:i',strtotime($x->agenda_data)),
													'initDate'=>date('d/m/Y',strtotime($x->agenda_data)),
													'cadeira'=>isset($_cadeiras[$x->id_cadeira])?utf8_encode($_cadeiras[$x->id_cadeira]->titulo):'',
													'profissionais'=>$profissionais);
					}

					$ultimoAgendamento=is_object($x)?$x:'';

				
				

					$_historico=array();
					$sql->consult($_p."pacientes_historico","*","where id_paciente=$paciente->id and  lixo=0 order by data desc");
					
					while($x=mysqli_fetch_object($sql->mysqry)) {

						if($x->evento=="agendaHorario") {
								$_historico[]=array('usr'=>utf8_encode($_profissionais[$x->id_usuario]->nome),
																	'dt'=>date('d/m H:i',strtotime($x->data)),
																	'ev'=>'horario',
																	'nvDt'=>date('d/m H:i',strtotime($x->agenda_data_novo)),
																	'antDt'=>date('d/m H:i',strtotime($x->agenda_data_antigo))
																);

						} else {
							if(isset($_status[$x->id_status_novo])) {
								$_historico[]=array('usr'=>utf8_encode($_profissionais[$x->id_usuario]->nome),
																	'dt'=>date('d/m H:i',strtotime($x->data)),
																	'ev'=>'status',
																	'desc'=>utf8_encode($x->descricao),
																	'sts'=>utf8_encode($_status[$x->id_status_novo]->titulo),
																	'novo'=>$x->evento=="agendaNovo",
																	'cor'=>$_status[$x->id_status_novo]->cor
																);
							}
						}
						
					}

					$dias='';
					if(is_object($ultimoAgendamento)) {
						$dias=strtotime(date('Y-m-d H:i:s'))-strtotime($ultimoAgendamento->data);
						$dias/=60*60*24;
						$dias=round($dias);
					}


					$pacienteInfo=array('id'=>$paciente->id,
										'nome'=>addslashes(utf8_encode($paciente->nome)),
										'agendou_dias'=>(int)$dias,
										'idade'=>(int)$idade,
										'telefone1'=>$paciente->telefone1,
										'musica'=>utf8_encode($paciente->musica),
										'periodicidade'=>isset($_pacientesPeriodicidade[$paciente->periodicidade])?$_pacientesPeriodicidade[$paciente->periodicidade]:$paciente->periodicidade,
										'statusBI'=>isset($_codigoBI[$paciente->codigo_bi])?utf8_encode($_codigoBI[$paciente->codigo_bi]):"",
										'agendamentosFuturos'=>$agendamentosFuturos,
										'historico'=>$_historico
								);

					$rtn=array('success'=>true,
								'paciente'=>$pacienteInfo);
				} else {
					$rtn=array('success'=>false,'error'=>'Paciente não encontrado!');
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
	if(isset($apiConfig['marca'])) {
?>
<script type="text/javascript">
	var asMarcas = [];

	const asMarcasListar = (openAside) => {
		
		if(asMarcas) {
			$('.js-asMarcas-table tbody').html('');

			let atualizaMarca = $('select.ajax-id_plano')?1:0;
			let atualizaMarcaId = 0;
			let marcasDisabledIds = [];
			if(atualizaMarca==1) {

				$('select.ajax-id_marca option').each(function(index,el){
					if($(el).prop('disabled')===true) {
						marcasDisabledIds.push($(el).val());
					}
				})
				atualizaMarcaId=$('select.ajax-id_marca').val();
				$('select.ajax-id_marca').find('option').remove();
				$('select.ajax-id_marca').append('<option value="">-</option>');
			}

			asMarcas.forEach(x=>{

				$(`.js-asMarcas-table tbody`).append(`<tr class="aside-open">
													<td><h1>${x.titulo}</h1></td>
													<td style="text-align:right;"><a href="javascript:;" class="button js-asMarcas-editar" data-id="${x.id}"><i class="iconify" data-icon="fluent:edit-24-regular"></i></a></td>
												</tr>`);

				if(atualizaMarca==1) {
					dis=marcasDisabledIds.includes(x.id)?' disabled':'';
					sel=(atualizaMarcaId==x.id)?' selected':'';
					$('select.ajax-id_marca').append(`<option value="${x.id}"${sel}${dis}>${x.titulo}</option>`);
				}

			});
			
			if(openAside===true) {
				$("#js-aside-asMarcas").fadeIn(100,function() {
					$("#js-aside-asMarcas .aside__inner1").addClass("active");
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

	const asMarcasAtualizar = (openAside) => {	
		let data = `ajax=asMarcasListar`;

		$.ajax({
			type:"POST",
			url:baseURLApiAside,
			data:data,
			success:function(rtn) {
				if(rtn.success) {
					asMarcas=rtn.regs;
					asMarcasListar(openAside);
				}
			}
		})
	}
	
	const asMarcasEditar = (id) => {
		let data = `ajax=asMarcasEditar&id=${id}`;
		$.ajax({
			type:"POST",
			url:baseURLApiAside,
			data:data,
			success:function(rtn) {
				if(rtn.success) {
					reg=rtn.cnt

					$(`.js-asMarcas-id`).val(reg.id);
					$(`.js-asMarcas-titulo`).val(reg.titulo);

					
					$('.js-asMarcas-form').animate({scrollTop: 0},'fast');
					$('.js-asMarcas-submit').html(`<i class="iconify" data-icon="fluent:checkmark-12-filled"></i>`);
					$('.js-asMarcas-remover').show();

					$('.aside-content').animate({scrollTop: 0},'fast');

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

		asMarcasAtualizar();

		$('.js-asMarcas-submit').click(function(){
			let obj = $(this);
			if(obj.attr('data-loading')==0) {

				let id = $(`.js-asMarcas-id`).val();
				let titulo = $(`.js-asMarcas-titulo`).val();

			

				if(titulo.length==0) {
					swal({title: "Erro!", text: "Digite a Marca", type:"error", confirmButtonColor: "#424242"});
				}  else {

					obj.html(`<span class="iconify" data-icon="eos-icons:loading"></span>`);
					obj.attr('data-loading',1);

					let data = `ajax=asMarcasPersistir&id=${id}&titulo=${titulo}`;
				
					$.ajax({
						type:'POST',
						data:data,
						url:baseURLApiAside,
						success:function(rtn) {
							if(rtn.success) {
								asMarcasAtualizar();	

								$(`.js-asMarcas-id`).val(0);
								$(`.js-asMarcas-titulo`).val(``);

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
						$('.js-asMarcas-remover').hide();
						obj.html(`<i class="iconify" data-icon="fluent:add-circle-24-regular"></i>`);
						obj.attr('data-loading',0);
					});

				}
			}
		})

		$('.js-asMarcas-table').on('click','.js-asMarcas-editar',function(){
			let id = $(this).attr('data-id');
			asMarcasEditar(id);
		});

		$('.aside-marca').on('click','.js-asMarcas-remover',function(){
			let obj = $(this);

			if(obj.attr('data-loading')==0) {

				let id = $('.js-asMarcas-id').val();
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
							let data = `ajax=asMarcasRemover&id=${id}`; 
							$.ajax({
								type:"POST",
								data:data,
								url:baseURLApiAside,
								success:function(rtn) {
									if(rtn.success) {
										$(`.js-asMarcas-id`).val(0);
										$(`.js-asMarcas-titulo`).val('');
										asMarcasAtualizar();
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
								$('.js-asMarcas-remover').hide();
								obj.html('<i class="iconify" data-icon="fluent:delete-24-regular"></i>');
								obj.attr('data-loading',0);
								$(`.js-asMarcas-submit`).html(`<i class="iconify" data-icon="fluent:add-circle-24-regular"></i>`);
							});
						} else {   
							swal.close();   
						} 
					});
			}
		});

	});
</script>

<section class="aside aside-marca">
	<div class="aside__inner1">

		<header class="aside-header">
			<h1>Marca</h1>
			<a href="javascript:;" class="aside-header__fechar aside-close"><i class="iconify" data-icon="fluent:dismiss-24-filled"></i></a>
		</header>

		<form method="post" class="aside-content form">
			<input type="hidden" class="js-asMarcas-id" />
			
			<dl>
				<dt>Título da Marca</dt>
				<dd>
					<input type="text" class="js-asMarcas-titulo" />
					<button type="button" class="js-asMarcas-submit button button_main" data-loading="0"><i class="iconify" data-icon="fluent:add-circle-24-regular"></i></button>
					<a href="javascript:;" class="button js-asMarcas-remover" data-loading="0" style="display:none;"><i class="iconify" data-icon="fluent:delete-24-regular"></i></a>
				</dd>
			</dl>
			<div class="list2" style="margin-top:2rem;">
					<table class="js-asMarcas-table">
						<thead>
							<tr>									
								<th>MARCA</th>
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
	if(isset($apiConfig['paciente'])) {
?>
<script type="text/javascript">
	
	$(function(){

		$('.js-asPaciente-submit').click(function(){
			let obj = $(this);
			if(obj.attr('data-loading')==0) {

				let nome = $(`.js-asPaciente-nome`).val();
				let telefone1 = $(`.js-asPaciente-telefone1`).val();
				let cpf = $(`.js-asPaciente-cpf`).val();
				let indicacao_tipo = $(`.js-asPaciente-indicacao_tipo`).val();
				let indicacao = $(`.js-asPaciente-indicacao`).val();
			

				if(nome.length==0) {
					swal({title: "Erro!", text: "Digite o Nome do Paciente", type:"error", confirmButtonColor: "#424242"});
				} else if(telefone1.length==0) {
					swal({title: "Erro!", text: "Digite o Whatsapp do Paciente", type:"error", confirmButtonColor: "#424242"});
				}  else {

					obj.html(`<span class="iconify" data-icon="eos-icons:loading"></span>`);
					obj.attr('data-loading',1);

					let data = `ajax=asPacientePersistir&nome=${nome}&telefone1=${telefone1}&cpf=${cpf}&indicacao_tipo=${indicacao_tipo}&indicacao=${indicacao}`;
					
					$.ajax({
						type:'POST',
						data:data,
						url:baseURLApiAside,
						success:function(rtn) {
							if(rtn.success) {

								$(`.js-asPaciente-nome`).val(``);
								$(`.js-asPaciente-telefone1`).val(``);
								$(`.js-asPaciente-cpf`).val(``);
								$(`.js-asPaciente-indicacao_tipo`).val(``);
								$(`.js-asPaciente-indicacao`).val(``);

								$('.ajax-id_paciente').append(`<option value="${rtn.id_paciente}" selected>${rtn.nome}</option>`);
								$('.aside-paciente .aside-close').click();

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
		})



	});
</script>

<section class="aside aside-paciente">
	<div class="aside__inner1">

		<header class="aside-header">
			<h1>Novo Paciente</h1>
			<a href="javascript:;" class="aside-header__fechar aside-close"><i class="iconify" data-icon="fluent:dismiss-24-filled"></i></a>
		</header>

		<form method="post" class="aside-content form js-asPaciente-form">
			
			<dl>
				<dt>Nome</dt>
				<dd>
					<input type="text" class="js-asPaciente-nome" />
				</dd>
			</dl>

			<div class="colunas2">

				<dl>
					<dt>Whatsapp</dt>
					<dd class="form-comp">
						<span class="js-country">BR</span><input type="text" class="js-asPaciente-telefone1" />
					</dd>
				</dl>
				<dl>
					<dt>CPF</dt>
					<dd>
						<input type="text" class="js-asPaciente-cpf cpf" />
					</dd>
				</dl>
			</div>

			<div class="colunas2">

				<dl>
					<dt>Tipo Indicação</dt>
					<dd>
						<select class="js-asPaciente-indicacao_tipo">
							<option value="">-</option>
							<?php
							foreach($_pacienteIndicacoes as $v) {
								echo '<option value="'.$v->id.'"'.($values['indicacao_tipo']==$v->id?' selected':'').' data-id="'.$v->id.'">'.utf8_encode($v->titulo).'</option>';
							}
							?>
						</select>
					</dd>
				</dl>
				<dl>
					<dt>Indicação</dt>
					<dd>
						<input type="text" class="js-asPaciente-indicacao" />
						<button type="button" class="js-asPaciente-submit button button_main" data-loading="0"><i class="iconify" data-icon="fluent:add-circle-24-regular"></i></button>

					</dd>
				</dl>
			</div>
		</form>
	</div>
</section>
<?php
	}
	if(isset($apiConfig['pacienteRelacionamento'])) {
?>
<script type="text/javascript">
	const pacienteRelacionamento = (id_paciente) => {

		let data = `ajax=asRelacionamentoPaciente&id_paciente=${id_paciente}`;
					
		$.ajax({
			type:'POST',
			data:data,
			url:baseURLApiAside,
			success:function(rtn) {
				if(rtn.success) {
					$('#js-aside-pacienteRelacionamento .js-nome').html(`${rtn.paciente.nome} <i class="iconify" data-icon="fluent:share-screen-person-overlay-20-regular" style="color:var(--cinza4)"></i>`).attr('href',`pg_pacientes_resumo.php?id_paciente=${rtn.paciente.id_paciente}`);


					if(rtn.paciente.idade && rtn.paciente.idade>0) {
						
						$('#js-aside-pacienteRelacionamento .js-idade').html(rtn.paciente.idade+(rtn.paciente.idade>=2?' anos':' ano'));
					} else {
						$('#js-aside-pacienteRelacionamento .js-idade').html(``);
					}

					if(rtn.paciente.periodicidade && rtn.paciente.periodicidade.length>0) {
						
						$('#js-aside-pacienteRelacionamento .js-periodicidade').html(`Periodicidade: ${rtn.paciente.periodicidade}`);
					} else {
						$('#js-aside-pacienteRelacionamento .js-periodicidade').html(`Periodicidade: -`);
					}

					if(rtn.paciente.musica && rtn.paciente.musica.length>0) {
						$('#js-aside-pacienteRelacionamento .js-musica').html(`<i class="iconify" data-icon="bxs:music"></i> ${rtn.paciente.musica}`);
					} else {
						$('#js-aside-pacienteRelacionamento .js-musica').html(``);
					}

					$("#js-aside-pacienteRelacionamento").fadeIn(100,function() {
						$('#js-aside-pacienteRelacionamento .js-profissionais').chosen();
						$('#js-aside-pacienteRelacionamento .js-tab').find('a:eq(0)').click();
						$("#js-aside-pacienteRelacionamento .aside__inner1").addClass("active");
					});

					$('#js-aside-pacienteRelacionamento input[name=agenda_data]').datetimepicker({
						timepicker:false,
						format:'d/m/Y',
						scrollMonth:false,
						scrollTime:false,
						scrollInput:false,
					}).css('background','');

					$('#js-aside-pacienteRelacionamento input[name=agenda_hora]').datetimepicker({
						  datepicker:false,
					      format:'H:i',
					      pickDate:false
					}).css('background','');

					$('#js-aside-pacienteRelacionamento input[name=id_paciente]').val(rtn.paciente.id);

					$('input[name=telefone1],.js-asPaciente-telefone1').mobilePhoneNumber({allowPhoneWithoutPrefix: '+55'}).bind('country.mobilePhoneNumber', function(echo, country) {
						let countryOut = country || '  ';
						$(this).parent().parent().find('.js-country').html(countryOut);
					}).trigger('keyup');

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
			//obj.html(`<i class="iconify" data-icon="fluent:add-circle-24-regular"></i>`);
			//obj.attr('data-loading',0);
		});

		
	}
</script>

<section class="aside aside-pacienteRelacionamento" id="js-aside-pacienteRelacionamento">
	
	<div class="aside__inner1">

		<header class="aside-header">
			<h1>Relacionamento com Paciente</h1>
			<a href="javascript:;" class="aside-header__fechar aside-close"><i class="iconify" data-icon="fluent:dismiss-24-filled"></i></a>
		</header>

		<form method="post" class="aside-content form" onsubmit="return false">
			<input type="text" name="id_paciente" />
			<input type="text" name="tipo" value="queroAgendar" />
			<section class="header-profile">
				<img src="img/ilustra-usuario.jpg" alt="" width="60" height="60" class="header-profile__foto" />
				<div class="header-profile__inner1">
					<h1><a href="" target="_blank" class="js-nome"></a></h1>
					<div>
						<p class="js-statusBI"></p>
						<p class="js-idade"></p>
						<p class="js-periodicidade">Periodicidade: 6 meses</p>
						<p class="js-musica"></p>
					</div>
				</div>
			</section>

			<script>
				$(function() {
					$('.js-tab a').click(function() {
						$(".js-tab a").removeClass("active");
						$(this).addClass("active");							
					});

					$('#js-aside-pacienteRelacionamento .js-btn-acao').click(function(){
						$('#js-aside-pacienteRelacionamento .js-btn-acao').removeClass('active');
						$(this).addClass('active');

						if($(this).attr('data-tipo')=="queroAgendar") {
							$('#js-aside-pacienteRelacionamento .js-ag-agendamento-naoQueroAgendar').hide();
							$('#js-aside-pacienteRelacionamento .js-ag-agendamento-queroAgendar').show();
							$('#js-aside-pacienteRelacionamento input[name=tipo]').val('queroAgendar');
						} else {
							$('#js-aside-pacienteRelacionamento .js-ag-agendamento-naoQueroAgendar').show();
							$('#js-aside-pacienteRelacionamento .js-ag-agendamento-queroAgendar').hide();
							$('#js-aside-pacienteRelacionamento input[name=tipo]').val('naoQueroAgendar');

						}
					});

					$('#js-aside-pacienteRelacionamento .js-ag-agendamento .js-salvar').click(function(){
						let tipo = $('#js-aside-pacienteRelacionamento input[name=tipo]').val();
						let id_paciente = $('#js-aside-pacienteRelacionamento input[name=id_paciente]').val();

						alert(id_paciente);

					})
				});
			</script>
			<section class="tab tab_alt js-tab">
				<a href="javascript:;" onclick="$('.js-ag').hide(); $('.js-ag-agendamento').show();" class="active">Agendamento</a>
				<a href="javascript:;" onclick="$('.js-ag').hide(); $('.js-ag-proximas').show();">Próximas Consultas</a>
				<a href="javascript:;" onclick="$('.js-ag').hide(); $('.js-ag-historico').show();">Histórico</a>					
			</section>
			
			<div class="js-ag js-ag-agendamento">

				<section class="filter">
					<div class="button-group">
						<a href="javascript:;" class="js-btn-acao button active" data-tipo="queroAgendar"><span>Quero agendar</span></a>
						<a href="javascript:;" class="js-btn-acao button" data-tipo="naoQueroAgendar"><span>Não quero agendar</span></a>
					</div>
					<div class="filter-group">
						<div class="filter-form form">
							<dl>
								<dd><button class="button button_main js-salvar" data-loading="0"><i class="iconify" data-icon="fluent:checkmark-12-filled"></i> <span>Salvar</span></button></dd>
							</dl>
						</div>								
					</div>
				</div>

				<div class="js-ag-agendamento-queroAgendar">
					<div class="colunas3">
						<dl>
							<dt>Data</dt>
							<dd class="form-comp"><span><i class="iconify" data-icon="fluent:calendar-ltr-24-regular"></i></span><input type="tel" name="agenda_data" class="data" /></dd>
						</dl>
					
						<dl>
							<dt>Duração</dt>
							<dd class="form-comp form-comp_pos">
								<?php /*<input type="tel" name="agenda_duracao" class="" />*/?>
								<select name="agenda_duracao">
									<?php
									foreach($optAgendaDuracao as $v) {
										echo '<option value="'.$v.'"'.($values['agenda_duracao']==$v?' selected':'').'>'.$v.'</option>';
									}
									?>
								</select>
								<span>min</span>
							</dd>
						</dl>

						<dl>
							<dt>Consultório</dt>
							<dd>
								<select name="id_cadeira">
									<option value=""></option>
									<?php
									foreach($_cadeiras as $p) {
										echo '<option value="'.$p->id.'"'.($values['id_cadeira']==$p->id?' selected':'').'>'.utf8_encode($p->titulo).'</option>';
									}
									?>
								</select>
							</dd>
						</dl>
					</div>
					<div class="colunas3">
						<dl class="dl2">
							<dt>Profissionais</dt>
							<dd>
								<select class="js-profissionais">
									<option value=""></option>
									<?php
									foreach($_profissionais as $p) {
										if($p->check_agendamento==0) continue;
										echo '<option value="'.$p->id.'">'.utf8_encode($p->nome).'</option>';
									}
									?>
								</select>
							</dd>
						</dl>
						<dl>
							<dt>Hora</dt>
							<dd class="form-comp"><span><i class="iconify" data-icon="fluent:clock-24-regular"></i></span><input type="tel" name="agenda_hora" class="hora" /></dd>
						</dl>
					</div>
				</div>


				<div class="js-ag-agendamento-naoQueroAgendar">
					<dl>
						<dd>
							<select>
								<option value="">selecione</option>
								<?php
								foreach($_historicoStatus as $v) {
									echo '<option value="'.$v->id.'">'.utf8_encode($v->titulo).'</option>';
								}
								?>
							</select>
						</dd>
					</dl>
					<dl>
						<dt>Observações</dt>
						<dd>
							<textarea style="height:80px;"></textarea>
						</dd>
					</dl>
				</div>

			</div>
			<div class="js-ag js-ag-proximas" style="display:none;">
				Proximas
			</div>
			<div class="js-ag js-ag-historico" style="display:none;">
				Historico
			</div>
		</div>

	</form>
</section><!-- .aside -->
<?php
	}
?>