<?php
	require_once("lib/conf.php");
	$_table=$_p."parametros_cadeiras";

	if(isset($_POST['ajax'])) {

		require_once("usuarios/checa.php");

		$rtn=array();

		if($_POST['ajax']=="parametrosPersistir") {

			$campos = explode(",","check_agendaDesativarRegrasStatus");
			$campo = (isset($_POST['campo']) and in_array($_POST['campo'],$campos)) ? $_POST['campo'] : '';
			$checked = (isset($_POST['checked']) and $_POST['checked']==1) ? 1 : 0;

			$erro='';
			if(empty($campo)) $erro='Campo não definido!';

			if(empty($erro)) {
				$vSQL=$campo."='".$checked."'";

				$sql->consult($_p."configuracoes_parametros","*","");
				if($sql->rows==0) {
					$sql->add($_p."configuracoes_parametros","check_agendaDesativarRegrasStatus=1");
					$sql->consult($_p."configuracoes_parametros","*","");
				} 

				$parametros=mysqli_fetch_object($sql->mysqry);

				$sql->update($_p."configuracoes_parametros",$vSQL,"where id=$parametros->id");
			}

			if(empty($erro)) {
				$rtn=array('success'=>true);
			} else {
				$rtn=array('success'=>false,'error'=>$erro);
			}
		} 

		header("Content-type: application/json");
		echo json_encode($rtn);
		die();
	}
	include "includes/header.php";
	include "includes/nav.php";

	$values=$adm->get($_GET);
	$campos=explode(",","titulo");

	if(isset($_POST['acao'])) {

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
						<h1>Configure a clínica</h1>
					</div>
				</div>
			</section>
 	
			<section class="grid">

				<div class="box box-col">

					<?php
					require_once("includes/submenus/subConfiguracoesClinica.php");
					?>
					<script type="text/javascript">
						$(function(){

							$('.js-check').click(function(){
								let campo = $(this).attr('name');
								let checked = $(this).prop('checked') ? 1 : 0;

								let data = `ajax=parametrosPersistir&campo=${campo}&checked=${checked}`;
								$.ajax({
									type:"POST",
									data:data,
									success:function(rtn) {
										if(rtn.success) {

										} else {
											let erro = rtn.error ? rtn.error : 'Algum erro ocorreu durante a persistência dos paramêtros';

											swal({title: "Erro!", text: erro, type:"error", confirmButtonColor: "#424242"});
										}
									}
								})
							})
						})
					</script>

					<div class="box-col__inner1">
				
						<form>
							<fieldset>
								<legend>Agenda</legend>

								<dl>
									<dd>
										<label><input type="checkbox" name="check_agendaDesativarRegrasStatus" value="1" class="input-switch js-check" /> Desativar regras de status de agendamento</label>
									</dd>
								</dl>
							</fieldset>
						</form>
					</div>			
				</div>

			</section>
		
		</div>
	</main>

	<section class="aside" id="js-aside">
		<div class="aside__inner1">

			<header class="aside-header">
				<h1>Adicionar cadeira</h1>
				<a href="javascript:;" class="aside-header__fechar aside-close"><i class="iconify" data-icon="fluent:dismiss-24-filled"></i></a>
			</header>

			<form method="post" class="aside-content form formulario-validacao">
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
					<legend>Dados da Cadeira</legend>
					<div class="colunas3">				
						<dl class="dl2">
							<dt>Título</dt>
							<dd><input type="text" name="titulo" class="obg" /></dd>
						</dl>
						<dl>
							<dt>Carga Horária</dt>
							<dd><input type="text" value="" class="js-carga" disabled />
						</dl>
					</div>
				</fieldset>


				<script type="text/javascript">
					var horarios = [];

					const horariosListar = () => {
						if(horarios) {
							$('.js-td').html('')
							for(var dia in horarios) {
								horarios[dia].forEach(x=>{
									
									/*$(`.js-${dia}`).append(`<div class="js-horario">${x.inicio}  - ${x.fim}<br /><a href="javascript:;" data-id="${x.id}" class="js-editar registros__acao registros__acao_sec"><i class="iconify" data-icon="bx-bx-download"></i></a>
															<a href="javascript:;" data-id="${x.id}" class="js-remover registros__acao registros__acao_sec"><i class="iconify" data-icon="bx-bx-trash"></i></a><div>`);*/
									$(`.js-${dia}`).append(`<a href="javascript:;" class="js-editar tooltiph" title="${x.profissionaisHorario}" data-id="${x.id}" >${x.inicio}~${x.fim}</a><br />`);
								})
							}
							 $(".tooltiph").tooltipster({theme:"borderless",contentAsHTML:true});
							
							
						}
					}
					const horariosAtualizar = () => {
						let id_cadeira=$('#js-aside input[name=id]').val();
						let data = `ajax=horariosListar&id_cadeira=${id_cadeira}`;
						$.ajax({
							type:"POST",
							data:data,
							success:function(rtn) {
								if(rtn.success) {
									horarios=rtn.horarios;

									if(rtn.carga) {
										$('.js-carga').val(rtn.carga);
									}
									horariosListar();
								}
							}
						})
					}
					
					const horarioEditar = (id_horario) => {
						let data = `ajax=horariosEditar&id_horario=${id_horario}`;
						var horarioObj = [];
						$.ajax({
							type:"POST",
							data:data,
							success:function(rtn) {
								if(rtn.success) {

									$(`.js-id`).val(rtn.id);
									$(`.js-dia`).val(rtn.dia);
									$(`.js-inicio`).val(rtn.inicio);
									$(`.js-fim`).val(rtn.fim);
									$('.js-horarios-submit').html(`<i class="iconify" data-icon="fluent:checkmark-12-filled"></i>`);

									$('.js-horarios-remover').show();
								}
							}
						});
					}
					$(function(){

						
						$('.js-horarios-submit').click(function(){
							let obj = $(this);

							if(obj.attr('data-loading')==0) {

								let id = $(`.js-id`).val();
								let dia = $(`.js-dia`).val();
								let inicio = $(`.js-inicio`).val();
								let fim = $(`.js-fim`).val();
								let id_cadeira=$('#js-aside input[name=id]').val();

								errInicio = validaHoraMinuto(inicio);
								errFim = validaHoraMinuto(fim);

								if(dia.length==0) {
									swal({title: "Erro!", text: "Selecione o Dia!", type:"error", confirmButtonColor: "#424242"});
								} else if(errInicio.length>0) {
									swal({title: "Erro!", text: `Erro na hora início: ${errInicio}`, type:"error", confirmButtonColor: "#424242"});
								} else if(errFim.length>0) {
									swal({title: "Erro!", text: `Erro na hora final: ${errFim}`, type:"error", confirmButtonColor: "#424242"});
								} else {

									obj.html(`<span class="iconify" data-icon="eos-icons:loading"></span>`);
									obj.attr('data-loading',1);

									let data = `ajax=horariosPersistir&id_cadeira=${id_cadeira}&dia=${dia}&inicio=${inicio}&fim=${fim}&id=${id}`;
									$.ajax({
										type:'POST',
										data:data,
										success:function(rtn) {
											if(rtn.success) {
												horariosAtualizar();	

												$(`.js-id`).val(0);
												$(`.js-dia`).val('');
												$(`.js-fim`).val('');
												$(`.js-inicio`).val('');
												$(`.js-horarios-cancelar`).hide();
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
										$('.js-horarios-remover').hide();
										obj.html(`<i class="iconify" data-icon="fluent:add-circle-24-regular"></i>`);
										obj.attr('data-loading',0);
									});

								}
							}
						})

						$('.js-horario-table').on('click','.js-editar',function(){
							let id = $(this).attr('data-id');
							horarioEditar(id);
						});

						

						$('.js-fieldset-horarios').on('click','.js-horarios-remover',function(){
							let obj = $(this);

							if(obj.attr('data-loading')==0) {

								let id_horario = $('.js-id').val();
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
											let data = `ajax=horariosRemover&id_horario=${id_horario}`; 
											$.ajax({
												type:"POST",
												data:data,
												success:function(rtn) {
													if(rtn.success) {
														$(`.js-id`).val(0);
														$(`.js-dia`).val('');
														$(`.js-fim`).val('');
														$(`.js-inicio`).val('');
														horariosAtualizar();
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
												$('.js-horarios-remover').hide();
												obj.html('<i class="iconify" data-icon="fluent:delete-24-regular"></i>');
												obj.attr('data-loading',0);
												$(`.js-horarios-submit`).html(`<i class="iconify" data-icon="fluent:add-circle-24-regular"></i>`);
											});
										} else {   
											swal.close();   
										} 
									});
							}
						});

					});
				</script>
				<fieldset class="js-fieldset-horarios">
					<legend>Horário de Funcionamento</legend>

					<input type="hidden" class="js-id" value="0" />
					<div class="colunas4">
						<dl>
							<dt>Dia da Semana</dt>
							<dd>
								<select  class="js-dia">
									<option value="">-</option>
									<?php
									for($i=0;$i<=6;$i++) {
										echo '<option value="'.$i.'">'.$_dias[$i].'</option>';	
									}
									?>
								</select>
							</dd>
						</dl>
						<dl>
							<dt>Início</dt>
							<dd class="form-comp">
								<span><i class="iconify" data-icon="fluent:clock-24-regular"></i></span>
								<input type="text" name="inicio" class="hora js-inicio" />
							</dd>
						</dl>
						<dl>
							<dt>Fim</dt>
							<dd class="form-comp">
								<span><i class="iconify" data-icon="fluent:clock-24-regular"></i></span>
								<input type="text" name="fim" class="hora js-fim" />
							</dd>
						</dl>
						<dl>
							<dt></dt>
							<dd>
								<button type="button" class="button button_main js-horarios-submit" data-loading="0"><i class="iconify" data-icon="fluent:add-circle-24-regular"></i></button>
								<a href="javascript:;" class="button js-horarios-remover" data-loading="0" style="display:none;"><i class="iconify" data-icon="fluent:delete-24-regular"></i></a>
							</dd>
						</dl>
					</div>
					<div class="list2">
						<table class="js-horario-table">
							<thead>
								<tr>
									<?php
									for($i=0;$i<=6;$i++) {
										echo '<th style="width:14.285%">'.$_dias[$i].'</th>';	
									}
									?>
								</tr>
							</thead>
							<tbody>
								<tr style="font-size:12px">
									<?php
									for($i=0;$i<=6;$i++) {
										echo '<td class="js-td js-'.$i.'"></td>';	
									}
									?>
								</tr>
							</tbody>
						</table>
						<?php /*<table> 
							<thead>
								<tr>
									<th style="width:14.285%">DOM</th>
									<th style="width:14.285%">SEG</th>
									<th style="width:14.285%">TER</th>
									<th style="width:14.285%">QUA</th>
									<th style="width:14.285%">QUI</th>
									<th style="width:14.285%">SEX</th>
									<th style="width:14.285%">SÁB</th>
								</tr>
							</thead>
							<tbody>
								<tr style="font-size:12px">
									<td></td>
									<td><a href="">08:00~12:00</a><br /><a href="">14:00~18:00</a></td>
									<td><a href="">08:00~12:00</a><br /><a href="">14:00~18:00</a></td>
									<td><a href="">08:00~12:00</a><br /><a href="">14:00~18:00</a></td>
									<td><a href="">08:00~12:00</a><br /><a href="">14:00~18:00</a></td>
									<td><a href="">08:00~12:00</a><br /><a href="">14:00~18:00</a></td>
									<td><a href="">09:00~13:00</a></td>
								</tr>
							</tbody>
						</table>*/?>
					</div>
				</fieldset>
			
			</form>

		</div>
	</section><!-- .aside -->

<?php 
include "includes/footer.php";
?>	