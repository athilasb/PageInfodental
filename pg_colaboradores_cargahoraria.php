<?php
	if(isset($_POST['ajax'])) {
		require_once("lib/conf.php");
		require_once("usuarios/checa.php");
		$sql = new Mysql();
		$rtn = array();

		if($_POST['ajax']=="horariosPersistir") {
			$unidade='';
			if(isset($_POST['id_unidade']) and is_numeric($_POST['id_unidade']) and isset($_unidades[$_POST['id_unidade']])) {
				$unidade=$_unidades[$_POST['id_unidade']];
			}

			$cadeira='';
			if(isset($_POST['id_cadeira']) and is_numeric($_POST['id_cadeira']) and is_object($unidade)) {
				$sql->consult($_p."parametros_cadeiras","*","where id='".$_POST['id_cadeira']."' and  id_unidade=$unidade->id");
				if($sql->rows) {
					$cadeira=mysqli_fetch_object($sql->mysqry);
				}
			}

			$profissional='';
			if(isset($_POST['id_profissional']) and is_numeric($_POST['id_profissional']) and is_object($unidade)) {
				$sql->consult($_p."colaboradores","*","where id='".$_POST['id_profissional']."'");
				if($sql->rows) {
					$profissional=mysqli_fetch_object($sql->mysqry);
				}
			}

			$inicio=(isset($_POST['inicio']) and !empty($_POST['inicio']))?addslashes($_POST['inicio']):'';
			$fim=(isset($_POST['fim']) and !empty($_POST['fim']))?addslashes($_POST['fim']):'';
			$dia=(isset($_POST['dia']) and is_numeric($_POST['dia']))?addslashes($_POST['dia']):'';

			if(empty($unidade)) $rtn=array('success'=>false,'error'=>'Unidade não definida!');
			else if(empty($cadeira)) $rtn=array('success'=>false,'error'=>'Cadeira não definida!');
			else if(empty($profissional)) $rtn=array('success'=>false,'error'=>'Profissional não definido!');
			else if(empty($inicio)) $rtn=array('success'=>false,'error'=>'Cadeira não definida!');
			else if(empty($fim)) $rtn=array('success'=>false,'error'=>'Cadeira não definida!');
			else if(empty($dia) and $dia!=0) $rtn=array('success'=>false,'error'=>'Dia da semana não definido!');
			else {
				$vsql="id_unidade=$unidade->id,
						id_cadeira=$cadeira->id,
						id_profissional=$profissional->id,
						inicio='".$inicio."',
						dia='".$dia."',
						fim='".$fim."'";

				if(isset($_POST['id']) and is_numeric($_POST['id']) and $_POST['id']>0) {
					$sql->consult($_p."profissionais_horarios","*", "where id='".$_POST['id']."' and id_profissional=$profissional->id and id_unidade=$unidade->id and lixo=0");
					if($sql->rows) {
						$x=mysqli_fetch_object($sql->mysqry);
						$vsql.=",id_alteracao=$usr->id,alteracao_data=now()";
						$sql->update($_p."profissionais_horarios",$vsql,"where id=$x->id");
						$rtn=array('success'=>true);
					} else $rtn=array('success'=>false,'error'=>'Horário não encontrado');
				} else {
					$vsql.=",id_usuario=$usr->id,data=now()";
					$sql->add($_p."profissionais_horarios",$vsql);
					$rtn=array('success'=>true);
				}
			}
		} else if($_POST['ajax']=="horariosListar") {

			$profissional='';
			if(isset($_POST['id_profissional']) and is_numeric($_POST['id_profissional'])) {
				$sql->consult($_p."colaboradores","*","where id='".$_POST['id_profissional']."'");
				if($sql->rows) {
					$profissional=mysqli_fetch_object($sql->mysqry);
				}
			}

			$_cadeiras=array();
			$sql->consult($_p."parametros_cadeiras","*","where lixo=0");
			while($x=mysqli_fetch_object($sql->mysqry)) $_cadeiras[$x->id]=$x;


			if(empty($profissional)) $rtn=array('success'=>false,'error'=>'Profissional não definido!');
			else {
				$horarios=array();
				$sql->consult($_p."profissionais_horarios","*,date_format(inicio,'%H:%i') as inicio,
															date_format(fim,'%H:%i') as fim","where id_profissional=$profissional->id and lixo=0");
				if($sql->rows) {
					while($x=mysqli_fetch_object($sql->mysqry)) {
						if(isset($_cadeiras[$x->id_cadeira])) {
							$cadeira=$_cadeiras[$x->id_cadeira];
							$horarios[$x->id_unidade][$x->dia][]=array('id'=>$x->id,
																'id_cadeira'=>$x->id_cadeira,
																'cadeira'=>utf8_encode($cadeira->titulo),
																'dia'=>$x->dia,
																'inicio'=>$x->inicio,
																'fim'=>$x->fim
															);
						}
					}

				}
				$rtn=array('success'=>true,'horarios'=>$horarios);
			}
		} else if($_POST['ajax']=="horariosEditar") {
			$horario='';
			if(isset($_POST['id_horario']) and is_numeric($_POST['id_horario'])) {
				$sql->consult($_p."profissionais_horarios","*,date_format(inicio,'%H:%i') as inicio,
																date_format(fim,'%H:%i') as fim","where id='".$_POST['id_horario']."'");
				if($sql->rows) {
					$horario=mysqli_fetch_object($sql->mysqry);
				}
			}

			if(is_object($horario)) {

				$rtn=array('success'=>true,
							'id'=>$horario->id,
							'id_cadeira'=>$horario->id_cadeira,
							'id_unidade'=>$horario->id_unidade,
							'inicio'=>$horario->inicio,
							'fim'=>$horario->fim,
							'dia'=>$horario->dia);
			} else {
				$rtn=array('success'=>false,'error'=>'Horário não encontrado!');
			}
		} else if($_POST['ajax']=="horariosRemover") {
			$horario='';
			if(isset($_POST['id_horario']) and is_numeric($_POST['id_horario'])) {
				$sql->consult($_p."profissionais_horarios","*,date_format(inicio,'%H:%i') as inicio,
																date_format(fim,'%H:%i') as fim","where id='".$_POST['id_horario']."'");
				if($sql->rows) {
					$horario=mysqli_fetch_object($sql->mysqry);
				}
			}

			if(is_object($horario)) {
				$sql->update($_p."profissionais_horarios","lixo=$usr->id,lixo_data=now()","where id=$horario->id");

				$rtn=array('success'=>true);
			} else {
				$rtn=array('success'=>false,'error'=>'Horário não encontrado!');
			}
		}

		header("Content-type: application/json");
		echo json_encode($rtn);
		die();
	}
	include "includes/header.php";
	include "includes/nav.php";

	$_table=$_p."colaboradores_cargahoraria";
	$_page=basename($_SERVER['PHP_SELF']);

	$colaborador=$cnt='';
	if(isset($_GET['id_colaborador']) and is_numeric($_GET['id_colaborador'])) {
		$sql->consult($_p."colaboradores","*","where id='".$_GET['id_colaborador']."'");
		if($sql->rows) {
			$colaborador=mysqli_fetch_object($sql->mysqry);
		}
	}

	$_cadeiras=array();
	$sql->consult($_p."parametros_cadeiras","*","where lixo=0 order by titulo asc");
	while($x=mysqli_fetch_object($sql->mysqry)) $_cadeiras[$x->id_unidade][]=$x;

	$sql->consult($_table,"*","WHERE id_colaborador='".$colaborador->id."' and lixo=0");
	if($sql->rows) {
		$cnt=mysqli_fetch_object($sql->mysqry);
	}

	$campos=explode(",","id_colaborador,horario,carga_semanal,atendimentopersonalizado");
	foreach($campos as $v) $values[$v]='';

	if(is_object($cnt)) {
		$values=$adm->values($campos,$cnt);
	}

	$_horarios = array(
		1 => '08:00 - 18:00',
		2 => '17:00 - 23:50'
	);

	$_cargasemanal = array(
		1 => '30',
		2 => '44'
	);

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
			$jsc->jAlert("Informações salvas com sucesso!","sucesso","document.location.href='".$_page."?id_colaborador=".$colaborador->id."'");
			die();
		}
	}

?>
<script>
	$(function(){
		$('.js-atendimento_s').click(function(){
			let checked = $(this).prop('checked')?1:0;
			if(checked==1) {
				$('.js-horarios').show();
			}
		});
		$('.js-atendimento_n').click(function(){
			let checked = $(this).prop('checked')?1:0;
			if(checked==1) {
				$('.js-horarios').hide();
			}
		});
		<?php
			if(isset($values['atendimentopersonalizado']) and $values['atendimentopersonalizado']==1) {
		?>
		$('.js-atendimento_s').trigger('click');
		<?php
			} else {
		?>
		$('.js-atendimento_n').trigger('click');
		<?php
			}
		?>
	});
</script>
	<section class="content">
		
		<?php
		require_once("includes/abaColaborador.php");
		?>

		<form method="post" class="form formulario-validacao"  autocomplete="off" enctype="multipart/form-data">
			<input type="hidden" name="acao" value="wlib" />
			<input type="hidden" name="id_colaborador" value="<?php echo $colaborador->id;?>" />

			<section class="grid" style="padding:2rem;">
				<div class="box">
					<div class="filter">

						<div class="filter-group">
							<div class="filter-button">
								<a href="javascript:history.back(-1);"><i class="iconify" data-icon="bx-bx-left-arrow-alt"></i></a>
							</div>
						</div>

						<div class="filter-group filter-group_right">
							<div class="filter-button">
								<?php if(is_object($cnt)){?><a href="?deletaCargahoraria=<?php echo $cnt->id."&".$url;?>" class="js-deletar"><i class="iconify" data-icon="bx-bx-trash"></i></a><?php }?>
								<a href="javascript:;"><i class="iconify" data-icon="bx-bx-printer"></i></a>
								<a href="javascript:;" class="azul  btn-submit"><i class="iconify" data-icon="bx-bx-check"></i><span>salvar</span></a>
							</div>
						</div>
					</div>

					<fieldset style="margin:0;">
						<legend style="font-size: 12px;">
							<div class="filter-group">
								<div class="filter-title">
									<span class="badge">1</span> Horário de Trabalho
								</div>
							</div>
						</legend>

						<div class="colunas4">
							<dl>
								<dt>Horário</dt>
								<dd>
									<select name="horario" class="obg">
										<option value="">-</option>
										<?php
										foreach($_horarios as $k => $v) {
											echo '<option value="'.$k.'"'.(($values['horario']==$k)?' selected':'').'>'.$v.'</option>';
										}
										?>
									</select>
								</dd>
							</dl>
							<dl>
								<dt>Carga Semanal</dt>
								<dd>
									<select name="carga_semanal" class="obg">
										<option value="">-</option>
										<?php
										foreach($_cargasemanal as $k => $v) {
											echo '<option value="'.$k.'"'.(($values['carga_semanal']==$k)?' selected':'').'>'.$v.'</option>';
										}
										?>
									</select>
								</dd>
							</dl>
						</div>
					</fieldset>

					<fieldset style="margin:0;">
						<legend style="font-size: 12px;">
							<div class="filter-group">
								<div class="filter-title">
									<span class="badge">2</span> Horários de Atendimento
								</div>
							</div>
						</legend>

						<div class="colunas4">
							<dl class="dl2">
								<dt>Possui Atendimento Personalizado?</dt>
								<dd>
									<label><input type="radio" name="atendimentopersonalizado" value="1"<?php echo $values['atendimentopersonalizado']==1?" checked":"";?> class="js-atendimento_s" /> Sim</label>
									<label><input type="radio" name="atendimentopersonalizado" value="0"<?php echo $values['atendimentopersonalizado']==0?" checked":"";?> class="js-atendimento_n" /> Não</label>
								</dd>
							</dl>
						</div>
					</fieldset>

					<?php
						$_dias=explode(",","Domingo,Segunda-Feira,Terça-Feira,Quarta-Feira,Quinta-Feira,Sexta-Feira,Sábado");
					?>
					<style type="text/css">
						div.js-horario {
							padding:3px;
							margin:4px;
							font-size: 16px;

							width:auto;
							border:solid 1px #CCC;
							background:#FFF;
							border-radius: 6px;
							text-align: center;
						}
					</style>
					<script type="text/javascript">
						var horarios = [];
						var id_profissional=<?php echo $colaborador->id;?>;

						const horariosListar = () => {
							if(horarios) {
								$('.js-td').html('')
								for(var id_unidade in horarios) {
									let index = `.js-${id_unidade}`;
									for(var dia in horarios[id_unidade]) {
										horarios[id_unidade][dia].forEach(x=>{
											
											$(`${index}-${x.id_cadeira}-${dia}`).append(`<div class="js-horario">${x.inicio}  - ${x.fim}<br /><a href="javascript:;" data-id="${x.id}" class="js-editar registros__acao registros__acao_sec"><i class="iconify" data-icon="bx-bx-download"></i></a>
																	<a href="javascript:;" data-id="${x.id}" class="js-remover registros__acao registros__acao_sec"><i class="iconify" data-icon="bx-bx-trash"></i></a><div>`);
										})
									}
								}
								
							}
						}
						const horariosAtualizar = () => {
							let data = `ajax=horariosListar&id_profissional=${id_profissional}`;
							$.ajax({
								type:"POST",
								data:data,
								success:function(rtn) {
									if(rtn.success) {
										horarios=rtn.horarios;
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
										id_unidade=rtn.id_unidade;

										$(`.js-${id_unidade}-id`).val(rtn.id);
										$(`.js-${id_unidade}-id_cadeira`).val(rtn.id_cadeira);
										$(`.js-${id_unidade}-dia`).val(rtn.dia);
										$(`.js-${id_unidade}-inicio`).val(rtn.inicio);
										$(`.js-${id_unidade}-fim`).val(rtn.fim);

										$('.js-horarios-cancelar').show();
									}
								}
							});
						}
						$(function(){
							horariosAtualizar();

							$('.js-horarios-submit').click(function(){
								let id_unidade = $(this).attr('data-id_unidade');
								let id_cadeira = $(`.js-${id_unidade}-id_cadeira`).val();
								let id = $(`.js-${id_unidade}-id`).val();
								let dia = $(`.js-${id_unidade}-dia`).val();
								let inicio = $(`.js-${id_unidade}-inicio`).val();
								let fim = $(`.js-${id_unidade}-fim`).val();

								if(id_cadeira.length==0) {
									swal({title: "Erro!", text: "Selecione a Cadeira!", type:"error", confirmButtonColor: "#424242"});
								} else if(dia.length==0) {
									swal({title: "Erro!", text: "Selecione o Dia!", type:"error", confirmButtonColor: "#424242"});
								} else if(inicio.length==0) {
									swal({title: "Erro!", text: "Defina o Início", type:"error", confirmButtonColor: "#424242"});
								} else if(fim.length==0) {
									swal({title: "Erro!", text: "Defina o Fim", type:"error", confirmButtonColor: "#424242"});
								} else {
									let data = `ajax=horariosPersistir&id_cadeira=${id_cadeira}&dia=${dia}&inicio=${inicio}&fim=${fim}&id_unidade=${id_unidade}&id_profissional=${id_profissional}&id=${id}`;
									$.ajax({
										type:'POST',
										data:data,
										success:function(rtn) {
											if(rtn.success) {
												horariosAtualizar();	

												$(`.js-${id_unidade}-id_cadeira`).val('');
												$(`.js-${id_unidade}-id`).val(0);
												$(`.js-${id_unidade}-dia`).val('');
												$(`.js-${id_unidade}-fim`).val('');
												$(`.js-${id_unidade}-inicio`).val('');
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
									})

								}
							})

							$('.js-horario-table').on('click','.js-editar',function(){
								let id = $(this).attr('data-id');
								horarioEditar(id);
							});

							$('.js-horarios-cancelar').click(function(){
								let id_unidade = $(this).attr('data-id_unidade');
								$(`.js-${id_unidade}-id_cadeira`).val('');
								$(`.js-${id_unidade}-id`).val(0);
								$(`.js-${id_unidade}-dia`).val('');
								$(`.js-${id_unidade}-fim`).val('');
								$(`.js-${id_unidade}-inicio`).val('');
								//$(`.js-horarios-cancelar`).hide();
							});

							$('.js-horario-table').on('click','.js-remover',function(){
								let id_horario = $(this).attr('data-id');
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
											let data = `ajax=horariosRemover&id_horario=${id_horario}`; 
											$.ajax({
												type:"POST",
												data:data,
												success:function(rtn) {
													if(rtn.success) {
														
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
											})
										} else {   
											swal.close();   
										} 
									});
							});

						});
					</script>
					<?php
						foreach($_unidades as $u) {
					?>
					<fieldset style="margin:0;" class="js-horarios">
						<legend style="font-size: 12px;">
							<div class="filter-group">
								<div class="filter-title">
									<span class="badge">3</span> <?php echo utf8_encode($u->titulo);?>
								</div>
							</div>
						</legend>

						<input type="hidden" class="js-<?php echo $u->id;?>-id" value="0" />
						<div class="colunas5"  style="margin-bottom: 20px;">	
							<dl>
								<dt>Cadeira</dt>
								<dd>
									<select class="<?php echo "js-".$u->id."-id_cadeira";?>">
										<option value="">-</option>
										<?php
										foreach($_cadeiras[$u->id] as $c) echo '<option value="'.$c->id.'">'.utf8_encode($c->titulo).'</option>';
										?>
									</select>
								</dd>
							</dl>
							<dl>
								<dt>Dia</dt>
								<dd>
									<select  class="<?php echo "js-".$u->id."-dia";?>">
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
								<dd><input type="text" name="inicio" class="hora <?php echo "js-".$u->id."-inicio";?>" /></dd>
							</dl>
							<dl>
								<dt>Fim</dt>
								<dd><input type="text" name="inicio" class="hora  <?php echo "js-".$u->id."-fim";?>" /></dd>
							</dl>

							<dl>
								<dt>&nbsp;</dt>
								<dd>
									<a href="javascript:;" class="button button__sec js-horarios-submit" data-id_unidade="<?php echo $u->id;?>"><i class="iconify" data-icon="bx-bx-check"></i></a>
									<a href="javascript:;" class="js-horarios-cancelar tooltip" data-id_unidade="<?php echo $u->id;?>" style="display: none;color:red" title="Cancelar edição"><span class="iconify" data-icon="icons8:cancel"></span> cancelar edição</a>
								</dd>
							</dl>
						</div>

						<div class="registros">
							<table class="js-horario-table">
								<tr>
									<th>Unidade</th>
									<?php
									for($i=0;$i<=6;$i++) {
										echo '<th>'.$_dias[$i].'</th>';	
									}
									?>
								</tr>
								<?php
								if(isset($_cadeiras[$u->id])) {
									foreach($_cadeiras[$u->id] as $v) {
								?>
								<tr>
									<td><?php echo $v->titulo;?></td>
									<?php
									for($i=0;$i<=6;$i++) {
										echo '<td class="js-td js-'.$u->id.'-'.$v->id.'-'.$i.'"></td>';	
									}
									?>
								</tr>
								<?php	
									}
								}
								?>
							</table>
						</div>
					</fieldset>
					<?php
						}
					?>

			</section>

		</form>
		
<?php
include "includes/footer.php";
?>