<?php
	if(isset($_POST['ajax'])) {
		require_once("lib/conf.php");
		require_once("usuarios/checa.php");
		$sql = new Mysql();
		$rtn = array();

		if($_POST['ajax']=="horariosPersistir") {

			$cadeira='';
			if(isset($_POST['id_cadeira']) and is_numeric($_POST['id_cadeira'])) {
				$sql->consult($_p."parametros_cadeiras","*","where id='".$_POST['id_cadeira']."'");
				if($sql->rows) {
					$cadeira=mysqli_fetch_object($sql->mysqry);
				}
			}


			$inicio=(isset($_POST['inicio']) and !empty($_POST['inicio']))?addslashes($_POST['inicio']):'';
			$fim=(isset($_POST['fim']) and !empty($_POST['fim']))?addslashes($_POST['fim']):'';
			$dia=(isset($_POST['dia']) and is_numeric($_POST['dia']))?addslashes($_POST['dia']):'';

			if(empty($cadeira)) $rtn=array('success'=>false,'error'=>'Cadeira não definida!');
			else if(empty($inicio)) $rtn=array('success'=>false,'error'=>'Cadeira não definida!');
			else if(empty($fim)) $rtn=array('success'=>false,'error'=>'Cadeira não definida!');
			else if(empty($dia) and $dia!=0) $rtn=array('success'=>false,'error'=>'Dia da semana não definido!');
			else {
				$vsql="id_cadeira=$cadeira->id,
						inicio='".$inicio."',
						dia='".$dia."',
						fim='".$fim."'";

				if(isset($_POST['id']) and is_numeric($_POST['id']) and $_POST['id']>0) {
					$sql->consult($_p."parametros_cadeiras_horarios","*", "where id='".$_POST['id']."' and lixo=0");
					if($sql->rows) {
						$x=mysqli_fetch_object($sql->mysqry);
						$vsql.=",id_alteracao=$usr->id,alteracao_data=now()";
						$sql->update($_p."parametros_cadeiras_horarios",$vsql,"where id=$x->id");
						$rtn=array('success'=>true);
					} else $rtn=array('success'=>false,'error'=>'Horário não encontrado');
				} else {
					$vsql.=",id_usuario=$usr->id,data=now()";
					$sql->add($_p."parametros_cadeiras_horarios",$vsql);
					$rtn=array('success'=>true);
				}
			}
		} else if($_POST['ajax']=="horariosListar") {


			$cadeira='';
			if(isset($_POST['id_cadeira']) and is_numeric($_POST['id_cadeira'])) {
				$sql->consult($_p."parametros_cadeiras","*","where id='".$_POST['id_cadeira']."'");
				if($sql->rows) {
					$cadeira=mysqli_fetch_object($sql->mysqry);
				}
			}


			$horarios=array();
			$sql->consult($_p."parametros_cadeiras_horarios","*,date_format(inicio,'%H:%i') as inicio,
														date_format(fim,'%H:%i') as fim","where id_cadeira=$cadeira->id and lixo=0 order by inicio asc");
			if($sql->rows) {
				while($x=mysqli_fetch_object($sql->mysqry)) {
					$horarios[$x->dia][]=array('id'=>$x->id,
														'id_cadeira'=>$x->id_cadeira,
														'cadeira'=>utf8_encode($cadeira->titulo),
														'dia'=>$x->dia,
														'inicio'=>$x->inicio,
														'fim'=>$x->fim
													);
					
				}

			}
			$rtn=array('success'=>true,'horarios'=>$horarios);
			
		} else if($_POST['ajax']=="horariosEditar") {
			$horario='';
			if(isset($_POST['id_horario']) and is_numeric($_POST['id_horario'])) {
				$sql->consult($_p."parametros_cadeiras_horarios","*,date_format(inicio,'%H:%i') as inicio,
																date_format(fim,'%H:%i') as fim","where id='".$_POST['id_horario']."'");
				if($sql->rows) {
					$horario=mysqli_fetch_object($sql->mysqry);
				}
			}

			if(is_object($horario)) {

				$rtn=array('success'=>true,
							'id'=>$horario->id,
							'id_cadeira'=>$horario->id_cadeira,
							'inicio'=>$horario->inicio,
							'fim'=>$horario->fim,
							'dia'=>$horario->dia);
			} else {
				$rtn=array('success'=>false,'error'=>'Horário não encontrado!');
			}
		} else if($_POST['ajax']=="horariosRemover") {
			$horario='';
			if(isset($_POST['id_horario']) and is_numeric($_POST['id_horario'])) {
				$sql->consult($_p."parametros_cadeiras_horarios","*,date_format(inicio,'%H:%i') as inicio,
																date_format(fim,'%H:%i') as fim","where id='".$_POST['id_horario']."'");
				if($sql->rows) {
					$horario=mysqli_fetch_object($sql->mysqry);
				}
			}

			if(is_object($horario)) {
				$sql->update($_p."parametros_cadeiras_horarios","lixo=$usr->id,lixo_data=now()","where id=$horario->id");

				$rtn=array('success'=>true);
			} else {
				$rtn=array('success'=>false,'error'=>'Horário não encontrado!');
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
	require_once("includes/abaConfiguracao.php");
	?>

	<?php
	$_table=$_p."parametros_cadeiras";
	$_page=basename($_SERVER['PHP_SELF']);

	$_width="";
	$_height="";
	$_dir="";


	if(isset($_GET['form'])) {
		$cnt='';
		$campos=explode(",","titulo,id_unidade,ordem,atendimentopersonalizado");
		
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
					$jsc->jAlert("Informações salvas com sucesso!","sucesso","document.location.href='".$_page."'");
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

	<section class="grid">
		<div class="box">

			<form method="post" class="form formulario-validacao"  autocomplete="off" enctype="multipart/form-data">
				<input type="hidden" name="acao" value="wlib" />

				<div class="filter">
					<div class="filter-group">
						<div class="filter-button">
							<a href="<?php echo $_page;?>"><i class="iconify" data-icon="bx-bx-left-arrow-alt"></i></a>
						</div>
					</div>
					<div class="filter-group filter-group_right">
						<div class="filter-button">
							<?php if(is_object($cnt)){?><a href="<?php echo $_page;?>?deleta=<?php echo $cnt->id."&".$url;?>" class="js-deletar"><i class="iconify" data-icon="bx-bx-trash"></i></a><?php }?>
							<a href="javascript:window.print();"><i class="iconify" data-icon="bx-bx-printer"></i></a>
							<a href="javascript:;" class="azul btn-submit"><i class="iconify" data-icon="bx-bx-check"></i><span>salvar</span></a>
						</div>
					</div>
				</div>

				<fieldset>
					<legend><span class="badge">1</span> Dados da Cadeira</legend>
					<div class="colunas6">
						<dl>
							<dt>Ordem</dt>
							<dd>
								<input type="number" name="ordem" value="<?php echo $values['ordem'];?>" class="obg" />
							</dd>
						</dl>
					</div>
					<div class="colunas4">
						<dl>
							<dt>Unidade</dt>
							<dd>
								<select name="id_unidade" class="obg">
									<option value="">-</option>
									<?php
									foreach($_unidades as $v) echo '<option value="'.$v->id.'"'.($v->id==$values['id_unidade']?' selected':'').'>'.utf8_encode($v->titulo).'</option>';
									?>
								</select>
							</dd>
						</dl>
						<dl class="dl3">
							<dt>Título</dt>
							<dd>
								<input type="text" name="titulo" value="<?php echo $values['titulo'];?>" class="obg" />
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
					var id_cadeira=<?php echo $cnt->id;?>;

					const horariosListar = () => {
						if(horarios) {
							$('.js-td').html('')
							for(var dia in horarios) {
								horarios[dia].forEach(x=>{
									
									$(`.js-${dia}`).append(`<div class="js-horario">${x.inicio}  - ${x.fim}<br /><a href="javascript:;" data-id="${x.id}" class="js-editar registros__acao registros__acao_sec"><i class="iconify" data-icon="bx-bx-download"></i></a>
															<a href="javascript:;" data-id="${x.id}" class="js-remover registros__acao registros__acao_sec"><i class="iconify" data-icon="bx-bx-trash"></i></a><div>`);
								})
							}
							
							
						}
					}
					const horariosAtualizar = () => {
						let data = `ajax=horariosListar&id_cadeira=${id_cadeira}`;
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

									$(`.js-id`).val(rtn.id);
									$(`.js-id_cadeira`).val(rtn.id_cadeira);
									$(`.js-dia`).val(rtn.dia);
									$(`.js-inicio`).val(rtn.inicio);
									$(`.js-fim`).val(rtn.fim);

									$('.js-horarios-cancelar').show();
								}
							}
						});
					}
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

						horariosAtualizar();

						$('.js-horarios-submit').click(function(){
							let id_unidade = $(this).attr('data-id_unidade');
							let id = $(`.js-id`).val();
							let dia = $(`.js-dia`).val();
							let inicio = $(`.js-inicio`).val();
							let fim = $(`.js-fim`).val();

							if(dia.length==0) {
								swal({title: "Erro!", text: "Selecione o Dia!", type:"error", confirmButtonColor: "#424242"});
							} else if(inicio.length==0) {
								swal({title: "Erro!", text: "Defina o Início", type:"error", confirmButtonColor: "#424242"});
							} else if(fim.length==0) {
								swal({title: "Erro!", text: "Defina o Fim", type:"error", confirmButtonColor: "#424242"});
							} else {
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
								})

							}
						})

						$('.js-horario-table').on('click','.js-editar',function(){
							let id = $(this).attr('data-id');
							horarioEditar(id);
						});

						$('.js-horarios-cancelar').click(function(){
							let id_unidade = $(this).attr('data-id_unidade');
							$(`.js-id_cadeira`).val('');
							$(`.js-id`).val(0);
							$(`.js-dia`).val('');
							$(`.js-fim`).val('');
							$(`.js-inicio`).val('');
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
				<fieldset style="margin:0;" class="js-horarios">
					<legend style="font-size: 12px;">
						<div class="filter-group">
							<div class="filter-title">
								<span class="badge">3</span> Horários
							</div>
						</div>
					</legend>

					<input type="hidden" class="js-id" value="0" />
					<div class="colunas5"  style="margin-bottom: 20px;">	
						<dl>
							<dt>Dia</dt>
							<dd>
								<select  class="<?php echo "js-dia";?>">
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
							<dd><input type="text" name="inicio" class="hora <?php echo "js-inicio";?>" /></dd>
						</dl>
						<dl>
							<dt>Fim</dt>
							<dd><input type="text" name="inicio" class="hora  <?php echo "js-fim";?>" /></dd>
						</dl>

						<dl>
							<dt>&nbsp;</dt>
							<dd>
								<a href="javascript:;" class="button button__sec js-horarios-submit"><i class="iconify" data-icon="bx-bx-check"></i></a>
								<a href="javascript:;" class="js-horarios-cancelar tooltip" style="display: none;color:red" title="Cancelar edição"><span class="iconify" data-icon="icons8:cancel"></span> cancelar edição</a>
							</dd>
						</dl>
					</div>

					<div class="registros">
						<table class="js-horario-table">
							<tr>
								<?php
								for($i=0;$i<=6;$i++) {
									echo '<th>'.$_dias[$i].'</th>';	
								}
								?>
							</tr>
							<tr>
								<?php
								for($i=0;$i<=6;$i++) {
									echo '<td class="js-td js-'.$i.'"></td>';	
								}
								?>
							</tr>
						</table>
					</div>
				</fieldset>
			</form>
		</div>
	</section>
	
	<?php
	} else {				
	if(isset($_GET['deleta']) and is_numeric($_GET['deleta']) and $usr->tipo=="admin") {
		$vSQL="lixo='1'";
		$vWHERE="where id='".$_GET['deleta']."'";
		$sql->update($_table,$vSQL,$vWHERE);
		$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='delete',vsql='".addslashes($vSQL)."',vwhere='".addslashes($vWHERE)."',tabela='".$_table."',id_reg='".$_GET['deleta']."'");
		$jsc->jAlert("Registro excluído com sucesso!","sucesso","document.location.href='".$_page."?".$url."'");
		die();
	}
	
	$where="WHERE lixo='0'";
	if(isset($values['id_unidade']) and is_numeric($values['id_unidade'])) $where.=" and (id_unidade = '".addslashes($values['id_unidade'])."')";
	
	if($usr->cpf=="wlib" and isset($_GET['cmd'])) echo $where;

	$sql->consult($_table,"*",$where." order by ordem, titulo asc");
	
	?>
	<section class="grid">
		<div class="box">

			<div class="filter">
				<div class="filter-button">
					<a href="<?php echo $_page."?form=1&$url";?>" class="verde"><i class="iconify" data-icon="bx-bx-plus"></i><span> Nova Cadeira</span></a>
				</div>
			</div>

			<div class="reg">
				<?php
				while($x=mysqli_fetch_object($sql->mysqry)) {
				?>
				<a href="pg_configuracao_cadeiras.php?form=1&edita=<?php echo $x->id;?>" class="reg-group">
					<div class="reg-color" style="background-color:green;"></div>
					<div class="reg-data" style="flex:0 1 50%;">
						<h1><?php echo strtoupperWLIB(utf8_encode($x->titulo));?></h1>
						<p>Ordem: <?php echo $x->ordem;?></p>
					</div>
					<div class="reg-data" style="flex:0 1 150px;">
						<p><?php echo isset($_unidades[$x->id_unidade])?utf8_encode($_unidades[$x->id_unidade]->titulo):'-';?></p>
					</div>
				</a>
				<?php
					}
				?>
			</div>

		</div>
	</section>

	<?php
	}
	?>

</section>

<?php
	include "includes/footer.php";
?>