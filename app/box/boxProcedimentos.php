<?php
	require_once("../lib/conf.php");
	$dir="../";
	require_once("../usuarios/checa.php");

	if(isset($_POST['ajax'])) {
		$sql = new Mysql();

		$rtn = array();
		if($_POST['ajax']=="procedimentoPersistir") {

			$procedimento='';
			if(isset($_POST['id_procedimento']) and is_numeric($_POST['id_procedimento'])) {
				$sql->consult($_p."parametros_procedimentos","*","where id='".$_POST['id_procedimento']."'");
				if($sql->rows) {
					$procedimento=mysqli_fetch_object($sql->mysqry);
				}
			}

			$vSQL="id_especialidade='".$_POST['id_especialidade']."',
				   id_regiao='".$_POST['id_regiao']."',
					titulo='".addslashes(utf8_decode($_POST['titulo']))."',
					quantitativo='".((isset($_POST['quantitativo']) and $_POST['quantitativo']==1)?1:0)."',
					face='".((isset($_POST['face']) and $_POST['face']==1)?1:0)."'";

			if(is_object($procedimento)) {
				$vWHERE="where id=$procedimento->id";
				$sql->update($_p."parametros_procedimentos",$vSQL,$vWHERE);
				$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='update',vsql='".addslashes($vSQL)."',vwhere='".addslashes($vWHERE)."',tabela='".$_p."parametros_procedimentos',id_reg='".$procedimento->id."'");
				$id_reg=$procedimento->id;
			} else {
				$sql->add($_p."parametros_procedimentos",$vSQL);
				$id_reg=$sql->ulid;
				$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='insert',vsql='".addslashes($vSQL)."',tabela='".$_p."parametros_procedimentos"."',id_reg='".$sql->ulid."'");
			}

			$id_procedimento=$id_reg;
			if(isset($_POST['planos']) and !empty($_POST['planos'])) {
				$planos = json_decode($_POST['planos'], true);
				if(is_array($planos) and count($planos)>0) {
					foreach($planos as $p) {
						$vsql="id_procedimento=$id_reg,
								id_plano='".addslashes($p['id_plano'])."',
								valor='".valor($p['valor'])."',
								obs='".addslashes($p['obs'])."',
								data=now(),
								id_usuario=$usr->id";

						$sql->add($_p."parametros_procedimentos_planos",$vsql);
					}
				}
			}
			$rtn=array('success'=>true);

		} else if($_POST['ajax']=="procedimentoRemover") {
			if(isset($_POST['id_procedimento']) and is_numeric($_POST['id_procedimento'])) {
				$sql->consult($_p."parametros_procedimentos","*","where id='".$_POST['id_procedimento']."'");
				if($sql->rows) {
					$procedimento=mysqli_fetch_object($sql->mysqry);
				}
			}

			if(isset($procedimento) and is_object($procedimento)) {
				$vSQL="lixo=1";
				$vWHERE="where id=$procedimento->id";
				$sql->update($_p."parametros_procedimentos",$vSQL,$vWHERE);

				$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='update',vwhere='".addslashes($vWHERE)."',vsql='".addslashes($vSQL)."',tabela='".$_p."parametros_procedimentos',id_reg='".$procedimento->id."'");

				$rtn=array('success'=>true);

			} else {
				$rtn=array('success'=>false,'error'=>'Procedimento não encontrado');
			}
		} else if($_POST['ajax']==="categoriasAtualizaLista") {

			$_categorias=array();
			$sql->consult($_p."parametros_especialidades","*","where lixo=0 order by titulo asc");

			if($sql->rows) {
				while($x=mysqli_fetch_object($sql->mysqry)) {
					$_categorias[]=array('id'=>$x->id,'titulo'=>utf8_encode($x->titulo));
				}
			}

			$rtn=array('success'=>true,'categorias'=>$_categorias);
		} else if($_POST['ajax']==="fasesAtualizaLista") {

			$_fases=array();
			$sql->consult($_p."parametros_fases","*","where lixo=0 order by titulo asc");

			if($sql->rows) {
				while($x=mysqli_fetch_object($sql->mysqry)) {
					$_fases[]=array('id'=>$x->id,'titulo'=>utf8_encode($x->titulo));
				}
			}

			$rtn=array('success'=>true,'fases'=>$_fases);
		} else if($_POST['ajax']=="planoListar") {
			$procedimento='';
			if(isset($_POST['id_procedimento']) and is_numeric($_POST['id_procedimento'])) {
				$sql->consult($_p."parametros_procedimentos","*","where id='".addslashes($_POST['id_procedimento'])."' and lixo=0");
				if($sql->rows) {
					$procedimento=mysqli_fetch_object($sql->mysqry);
				}
			}

			$_planos=array();
			$_planosSel=array();
			$sql->consult($_p."parametros_planos","*","where lixo=0 order by titulo asc");
			while($x=mysqli_fetch_object($sql->mysqry)) {
				$_planos[$x->id]=$x;
				$_planosSel[]=$x;
			}

			if(is_object($procedimento)) {
				$sql->consult($_p."parametros_procedimentos_planos","*","where id_procedimento=$procedimento->id and lixo=0 order by id desc");
				$planos=array();
				while($x=mysqli_fetch_object($sql->mysqry)) {
					$plano=isset($_planos[$x->id_plano])?utf8_encode($_planos[$x->id_plano]->titulo):"-";
					$planos[]=array('id'=>$x->id,
									'plano'=>$plano,
									'id_plano'=>$x->id_plano,
									'obs'=>utf8_encode($x->obs),
									'valor'=>number_format($x->valor,2,",","."));
				}

				$rtn=array('success'=>true,'select'=>$_planosSel,'planos'=>$planos);
			} else {
				$rtn=array('success'=>false,'error'=>'Categoria não definida!');
			}
		} else if($_POST['ajax']=="planoAdicionar") {
			$plano='';
			if(isset($_POST['id_plano']) and is_numeric($_POST['id_plano'])) {
				$sql->consult($_p."parametros_planos","*","where id='".$_POST['id_plano']."'");
				if($sql->rows) {
					$plano=mysqli_fetch_object($sql->mysqry);
				}
			}

			$procedimento='';
			if(isset($_POST['id_procedimento']) and is_numeric($_POST['id_procedimento'])) {
				$sql->consult($_p."parametros_procedimentos","*","where id='".$_POST['id_procedimento']."'");
				if($sql->rows) {
					$procedimento=mysqli_fetch_object($sql->mysqry);
				}
			}


			$unidade='';
			if(isset($_POST['id_unidade']) and is_numeric($_POST['id_unidade'])) {
				$sql->consult($_p."unidades","*","where id='".$_POST['id_unidade']."'");
				if($sql->rows) {
					$unidade=mysqli_fetch_object($sql->mysqry);
				}
			}

			$procPlano=$erro="";
			if(isset($_POST['id_procedimento_plano']) and is_numeric($_POST['id_procedimento_plano']) and $_POST['id_procedimento_plano']>0) {
				$sql->consult($_p."parametros_procedimentos_planos","*","where id='".$_POST['id_procedimento_plano']."' and lixo=0");
				//echo "where id='".$_POST['id_procedimento_plano']."' ".$sql->rows;
				if($sql->rows) {
					$procPlano=mysqli_fetch_object($sql->mysqry);
				} else {
					$erro="Procedimento de Plano não encontrado.";
				}
			}

			if(empty($erro)) {
				if(is_object($procedimento)) {
					if(is_object($unidade)) {
						if(is_object($plano)) {

							$vSQL="id_procedimento=$procedimento->id,
									id_unidade=$unidade->id,
									id_plano=$plano->id,
									valor='".valor($_POST['valor'])."',
									obs='".addslashes(utf8_decode($_POST['obs']))."'";

							if(is_object($procPlano)) {
								$vSQL.=",lixo=0,id_alteracao=$usr->id,alteracao_data=now()";
								$sql->update($_p."parametros_procedimentos_planos",$vSQL,"where id=$procPlano->id");
							} else {
								$sql->consult($_p."parametros_procedimentos_planos","*","where id_procedimento=$procedimento->id and id_plano=$plano->id");
								if($sql->rows) {
									$x=mysqli_fetch_object($sql->mysqry);
									$vSQL.=",lixo=0,id_alteracao=$usr->id,alteracao_data=now()";
									$sql->update($_p."parametros_procedimentos_planos",$vSQL,"where id=$x->id");
								} else {
									$vSQL.=",data=now(),id_usuario=$usr->id,lixo=0";
									$sql->add($_p."parametros_procedimentos_planos",$vSQL);
								}
							}

							$rtn=array('success'=>true);
						} else {
							$rtn=array("success"=>false,"error"=>"Plano não encontrado!");
						}
					} else {
						$rtn=array("success"=>false,"error"=>"Unidade não encontrada!");
					}
				} else {
					$rtn=array("success"=>false,"error"=>"Procedimento não encontrado!");
				}
			} else {
				$rtn=array("success"=>false,"error"=>$erro);
			}
		} else if($_POST['ajax']=="planoRemover") {
			$procPlano='';
			if(isset($_POST['id_procedimento_plano']) and is_numeric($_POST['id_procedimento_plano'])) {
				$sql->consult($_p."parametros_procedimentos_planos","*","where id='".$_POST['id_procedimento_plano']."'");
				if($sql->rows) {
					$procPlano=mysqli_fetch_object($sql->mysqry);
				}
			}

			$procedimento='';
			if(isset($_POST['id_procedimento']) and is_numeric($_POST['id_procedimento'])) {
				$sql->consult($_p."parametros_procedimentos","*","where id='".$_POST['id_procedimento']."'");
				if($sql->rows) {
					$procedimento=mysqli_fetch_object($sql->mysqry);
				}
			}

			if(is_object($procedimento)) {
				if(is_object($procPlano)) {

					$sql->update($_p."parametros_procedimentos_planos","lixo=$usr->id,lixo_data=now()","where id=$procPlano->id and id_procedimento=$procedimento->id");

					$rtn=array('success'=>true);
				} else {
					$rtn=array("success"=>false,"error"=>"Plano não encontrado!");
				}
			} else {
				$rtn=array("success"=>false,"error"=>"Procedimento não encontrado!");
			}
		} else if($_POST['ajax']=="faseListar") {
			$procedimento='';
			if(isset($_POST['id_procedimento']) and is_numeric($_POST['id_procedimento'])) {
				$sql->consult($_p."parametros_procedimentos","*","where id='".addslashes($_POST['id_procedimento'])."' and lixo=0");
				if($sql->rows) {
					$procedimento=mysqli_fetch_object($sql->mysqry);
				}
			}

			if(is_object($procedimento)) {
				$_fases=array();
				$sql->consult($_p."parametros_fases","*","where lixo=0 order by titulo asc");
				while($x=mysqli_fetch_object($sql->mysqry)) {
					$_fases[$x->id]=$x;
				}
				$sql->consult($_p."parametros_procedimentos_fases","*","where id_procedimento=$procedimento->id and 
																				lixo=0 order by id asc");
				$fases=array();
				while($x=mysqli_fetch_object($sql->mysqry)) {
					if(isset($_fases[$x->id_fase])) {
						$evolucao=array();
						if(!empty($x->evolucao)) {
							$aux=explode(",",$x->evolucao);
							foreach($aux as $v) {
								if(!empty($v) and is_numeric($v)) $evolucao[]=$v;
							}
						}

						$fases[]=array('id'=>$x->id,
										'fase'=>utf8_encode($_fases[$x->id_fase]->titulo),
										'id_fase'=>utf8_encode($x->id_fase),
										'evolucao'=>$evolucao);
					}
					
				}

				$rtn=array('success'=>true,'fases'=>$fases);
			} else {
				$rtn=array('success'=>false,'error'=>'Procedimento não definido!');
			}
		} else if($_POST['ajax']=="faseAdicionar") {

			$procedimento='';
			if(isset($_POST['id_procedimento']) and is_numeric($_POST['id_procedimento'])) {
				$sql->consult($_p."parametros_procedimentos","*","where id='".$_POST['id_procedimento']."'");
				if($sql->rows) {
					$procedimento=mysqli_fetch_object($sql->mysqry);
				}
			}

			$fase=$erro="";
			if(isset($_POST['id']) and is_numeric($_POST['id']) and $_POST['id']>0) {
				$sql->consult($_p."parametros_procedimentos_fases","*","where id='".$_POST['id']."'");
				//echo "where id='".$_POST['id_procedimento_plano']."' ".$sql->rows;
				if($sql->rows) {
					$fase=mysqli_fetch_object($sql->mysqry);
				} else {
					$erro="Fase de Procedimento não encontrado.";
				}
			}

			if(empty($erro)) {
				if(is_object($procedimento)) {

					$vSQL="id_fase='".addslashes($_POST['id_fase'])."',id_procedimento=$procedimento->id";
					if(is_object($fase)) {
						$vSQL.=",lixo=0,id_alteracao=$usr->id,alteracao_data=now()";
						$sql->update($_p."parametros_procedimentos_fases",$vSQL,"where id=$fase->id");
					} else {
						$vSQL.=",data=now(),id_usuario=$usr->id,lixo=0";
						$sql->add($_p."parametros_procedimentos_fases",$vSQL);
					}

					$rtn=array('success'=>true);
					
				} else {
					$rtn=array("success"=>false,"error"=>"Procedimento não encontrado!");
				}
			} else {
				$rtn=array("success"=>false,"error"=>$erro);
			}
		} else if($_POST['ajax']=="faseRemover") {
			$fase='';
			if(isset($_POST['id_fase']) and is_numeric($_POST['id_fase'])) {
				$sql->consult($_p."parametros_procedimentos_fases","*","where id='".$_POST['id_fase']."'");
				if($sql->rows) {
					$fase=mysqli_fetch_object($sql->mysqry);
				}
			}

			$procedimento='';
			if(isset($_POST['id_procedimento']) and is_numeric($_POST['id_procedimento'])) {
				$sql->consult($_p."parametros_procedimentos","*","where id='".$_POST['id_procedimento']."'");
				if($sql->rows) {
					$procedimento=mysqli_fetch_object($sql->mysqry);
				}
			}

			if(is_object($procedimento)) {
				if(is_object($fase)) {

					$sql->update($_p."parametros_procedimentos_fases","lixo=$usr->id,lixo_data=now()","where id=$fase->id and id_procedimento=$procedimento->id");

					$rtn=array('success'=>true,"id"=>$fase->id);
				} else {
					$rtn=array("success"=>false,"error"=>"Fase de Procedimento não encontrada!");
				}
			} else {
				$rtn=array("success"=>false,"error"=>"Procedimento não encontrado!");
			}
		} else if($_POST['ajax']=="faseEvolucao") {
			$procedimento='';
			if(isset($_POST['id_procedimento']) and is_numeric($_POST['id_procedimento'])) {
				$sql->consult($_p."parametros_procedimentos","*","where id='".addslashes($_POST['id_procedimento'])."' and lixo=0");
				if($sql->rows) {
					$procedimento=mysqli_fetch_object($sql->mysqry);
				}
			}

			$fase='';
			if(isset($_POST['id_fase']) and is_numeric($_POST['id_fase']) and is_object($procedimento)) {
				$sql->consult($_p."parametros_procedimentos_fases","*","where id='".addslashes($_POST['id_fase'])."' and id_procedimento=$procedimento->id and lixo=0");
				if($sql->rows) {
					$fase=mysqli_fetch_object($sql->mysqry);
				}
			}

			if(is_object($procedimento)) {
				if(is_object($fase)) {
					$evolucao='';
					if(isset($_POST['evolucao']) and !empty($_POST['evolucao'])) {
						$evolucao=",".$_POST['evolucao'].",";
					}
					$vsql="evolucao='".($evolucao)."'";
					$sql->update($_p."parametros_procedimentos_fases",$vsql,"where id=$fase->id");
					$rtn=array("success"=>true);

				} else {
					$rtn=array("success"=>false,"error"=>"Fase não encontrada!");
				}
			} else {
				$rtn=array("success"=>false,"error"=>"Procedimento não encontrado!");
			}
		}

		header("Content-type: application/json");
		echo json_encode($rtn);
		die();
	}

	$campos=explode(",","id_especialidade,titulo,id_regiao,face,quantitativo");
		
	foreach($campos as $v) $values[$v]='';
	$values['camposEvolucao']=array();

	$jsc = new Js();
	$procedimento='';
	if(isset($_GET['id_procedimento']) and is_numeric($_GET['id_procedimento'])) {
		$sql->consult($_p."parametros_procedimentos","*","where id='".$_GET['id_procedimento']."'");
		if($sql->rows) {
			$procedimento=mysqli_fetch_object($sql->mysqry);

			foreach($campos as $v) {
				$values[$v]=utf8_encode($procedimento->$v);
			}
		}
	}

	$_regioes=array();
	$sql->consult($_p."parametros_procedimentos_regioes","*","where lixo=0 order by titulo asc");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_regioes[$x->id]=$x;
	}

	$_planos=array();
	$sql->consult($_p."parametros_planos","*","where lixo=0 order by titulo asc");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_planos[$x->id]=$x;
	}

	$_especialidades=array();
	$sql->consult($_p."parametros_especialidades","*","where lixo=0 order by titulo asc");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_especialidades[$x->id]=$x;
	}
?>
<script>
	var id_procedimento = '<?php echo is_object($procedimento)?$procedimento->id:'';?>';
	var id_unidade = '<?php echo $usrUnidade->id;?>';
	$(function(){
		$('.js-remover').click(function(){

			swal({   title: "Atenção",   text: "Você tem certeza que deseja remover este registro?",   type: "warning",   showCancelButton: true,   confirmButtonColor: "#DD6B55",   confirmButtonText: "Sim!",   cancelButtonText: "Não",   closeOnConfirm: false,   closeOnCancel: false }, function(isConfirm){   
				if (isConfirm) { 

					let data = `ajax=procedimentoRemover&id_procedimento=${id_procedimento}`;   
					$.ajax({
						type:"POST",
						url:'box/boxProcedimentos.php',
						data:data,
						success:function(rtn){
							swal.close();  
							if(rtn.success) {
								document.location.href=`pg_configuracao_procedimentos_servicos.php`
							} else if(rtn.error) {
								swal({title: "Erro!", text: rtn.error, type:"error", confirmButtonColor: "#424242"});
							} else {
								swal({title: "Erro!", text: "Procedimento não removido. Por favor tente novamente!", type:"error", confirmButtonColor: "#424242"});
							}
						},
						error:function(){
							swal.close();  
							swal({title: "Erro!", text: "Procedimento não removido. Por favor tente novamente!", type:"error", confirmButtonColor: "#424242"});
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
				
				let campos = $('form.js-form-procedimento').serialize();
				let data = `ajax=procedimentoPersistir&id_procedimento=${id_procedimento}&${campos}`;

				$.ajax({
					type:'POST',
					url:'box/boxProcedimentos.php',
					data:data,
					success:function(rtn) {
						if(rtn.success) {
							document.location.href=`pg_configuracao_procedimentos_servicos.php`
						} else if(rtn.error) {
							swal({title: "Erro!", text: rtn.error, type:"error", confirmButtonColor: "#424242"});
						} else {
							swal({title: "Erro!", text: "Procedimento não salvo. Por favor tente novamente!", type:"error", confirmButtonColor: "#424242"});
						}
					},
					error:function(){
						swal({title: "Erro!", text: "Procedimento não salvo. Por favor tente novamente!", type:"error", confirmButtonColor: "#424242"});
					}
				})

			}
			return false;
		});
	});
</script>
<section class="modal" style="height:auto; width:950px;">

	<header class="modal-header">
		<div class="filtros">

			<?php
				if(empty($procedimento)) {
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

	<script type="text/javascript">
		$(function(){
			$('select[name=id_regiao]').change(function(){
				let face = $(this).find(':checked').attr('data-face');
				let quantitativo = $(this).find(':checked').attr('data-quantitativo');

				$('.js-quantitativo, .js-face').hide();

				if(face==1) {
					$('.js-face').show();
				} 
				if(quantitativo==1) {
					$('.js-quantitativo').show();
				} 

			}).trigger('change');
		});
	</script>
	
	<article class="modal-conteudo">

		<form method="post" class="form js-form-procedimento">
			<fieldset>
				<legend>Dados do Procedimento</legend>
				<div class="colunas4">
					<dl>
						<dt>Especialidade</dt>
						<dd>
							<select name="id_especialidade" style="width:75%;float: left;">
								<option value="">-</option>
								<?php

								foreach($_especialidades as $v) echo '<option value="'.$v->id.'"'.($values['id_especialidade']==$v->id?' selected':'').'>'.utf8_encode($v->titulo).'</option>';
								?>
							</select>
							<?php
								if(is_object($procedimento)) {
							?>
							<a href="box/boxEspecialidades.php?id_procedimento=<?php echo $procedimento->id;?>" class="button button__sec tooltip" data-fancybox data-type="ajax" title="Gerenciar Especialidades" style="float:left;margin-left:5px;"><span class="iconify" data-icon="octicon:gear"></span></a>
							<?php
								} else {
							?>
							<a href="box/boxEspecialidades.php" class="button button__sec tooltip" data-fancybox data-type="ajax" title="Gerenciar Especialidades" style="float:left;margin-left:5px;"><span class="iconify" data-icon="octicon:gear"></span></a>
							<?php
								}
							?>
						</dd>
					</dl>
					<dl class="dl2">
						<dt>Nome do Procedimento</dt>
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
									echo  '<option value="'.$v->id.'" data-face="'.$v->face.'" data-quantitativo="'.$v->quantitativo.'"'.($v->id==$values['id_regiao']?' selected':'').'>'.utf8_encode($v->titulo).'</option>';
								}
								?>
							</select>
						</dd>
					</dl>
					<dl class="js-face">
						<dt>&nbsp;</dt>
						<dd><label><input type="checkbox" name="face" value="1"<?php echo $values['face']==1?" checked":"";?> /> Por Face</label></dd>
					</dl>
					<dl class="js-quantitativo">
						<dt>&nbsp;</dt>
						<dd>
							<label><input type="checkbox" name="quantitativo" value="1"<?php echo $values['quantitativo']==1?" checked":"";?> /> Procedimento quantitativo <a href="javascript:;" class="tooltip" style="color: var(--cor1)" title="Exige definição de quantidade ao incluir em um Plano de Tratamento"><span class="iconify" data-icon="dashicons:editor-help" data-inline="true" data-width="20"></span></a></label>
						</dd>
					</dl>
				
				</div>
			</fieldset>

			<fieldset>
				<legend>Procedimento por Plano</legend>
				<?php
					if(is_object($procedimento)) {
				?>
				<input type="hidden" class="js-plano-id" value="0"  />
					<div class="colunas4">
						<dl>
							<dt>Plano</dt>
							<dd>
								<select class="js-plano-id_plano">
								</select>
							</dd>
						</dl>
						<dl>
							<dt>Valor</dt>
							<dd><input type="text" class="js-plano-valor money" /></dd>
						</dl>
					</div>
					<div class="colunas4">
						
						<dl class="dl3">
							<dt>Obs.</dt>
							<dd><input type="text" class="js-plano-obs noupper" /></dd>
						</dl>
						<dl>
							<dt>&nbsp;</dt>
							<dd>
								<button type="button" class="button js-plano-salvar"><i class="iconify" data-icon="ic-baseline-add"></i> Adicionar</button>
								<a href="javascript:;" class="js-plano-cancelar tooltip" style="display: none;color:red" title="Cancelar edição"><span class="iconify" data-icon="icons8:cancel"></span> cancelar edição</a>
							</dd>
						</dl>
					</div>
					<script type="text/javascript">
						var id_procedimento = '<?php echo $procedimento->id;?>';
						var planosProcedimentos = [];
						function planoListar() {
							let data = `ajax=planoListar&id_procedimento=${id_procedimento}`;
							$.ajax({
								type:'POST',
								url:'box/boxProcedimentos.php',
								data:data,
								success:function(rtn) {
									if(rtn.success===true) {

										if(rtn.select) {
											
											$('select.js-plano-id_plano').find('option').remove();
											$('select.js-plano-id_plano').append(`<option value="">-</option>`);
											rtn.select.forEach(x=>{
												$('select.js-plano-id_plano').append(`<option value="${x.id}">${x.titulo}</option>`);
											});
										}

										$('.js-planos-table tbody tr').remove();
										planosProcedimentos = rtn.planos;
										rtn.planos.forEach(x => {
											$('.js-plano-id_plano').find(`option[value=${x.id_plano}]`).remove();
											
											let tr = `<tr><td>${x.plano}</td><td>${x.valor}</td><td class="js-obs"></td><td><a href="javascript:;" data-id="${x.id}" class="js-editar registros__acao registros__acao_sec"><i class="iconify" data-icon="bx:bxs-edit-alt"></i></a><a href="javascript:;" data-id="${x.id}" class="js-remover registros__acao registros__acao_sec"><i class="iconify" data-icon="bx:bxs-trash"></i></a></tr>`;
											$('.js-planos-table tbody').append(tr);

											let obs = x.obs.length>0?`<a href="javascript:" class="tooltip botao" title="${x.obs}"><span class="iconify" data-icon="fa-solid:comment"></span></a>`:`<span class="iconify" data-icon="fa-solid:comment" style="opacity:0.3"></span>`;
											$('.js-planos-table .js-obs:last').html(`<center>${obs}</center>`);
											$('.js-planos-table .js-obs:last a').tooltipster({theme:"borderless"});
										})
									} else if(rtn.error) {
										swal({title: "Erro!", text: rtn.error, type:"error", confirmButtonColor: "#424242"});
									} else {
										swal({title: "Erro!", text: "Algum erro ocorreu durante a listagem dos planos", type:"error", confirmButtonColor: "#424242"});
									}
								},
								error:function(){

								}
							});
						}
						function planoAdicionar() {
							let id_plano = $('select.js-plano-id_plano').val();
							let valor = $('input.js-plano-valor').val();
							let obs = $('input.js-plano-obs').val();
							let id_procedimento_plano = $('input.js-plano-id').val();

							if(id_plano.length==0) {
								swal({title: "Erro!", text: "Selecione o Plano!", type:"error", confirmButtonColor: "#424242"});
							} else if(valor.length==0) {
								swal({title: "Erro!", text: "Selecione o Valor!", type:"error", confirmButtonColor: "#424242"});
							} else {

								let data = `ajax=planoAdicionar&id_plano=${id_plano}&id_unidade=${id_unidade}&id_procedimento=${id_procedimento}&valor=${valor}&obs=${obs}&id_procedimento_plano=${id_procedimento_plano}`;
								console.log(data);
								$.ajax({
									type:'POST',
									data: data,
									url:'box/boxProcedimentos.php',
									success:function(rtn) {
										if(rtn.success===true) {
											$('select.js-plano-id_plano').val(``);
											$('input.js-plano-valor').val(``);
											$('input.js-plano-obs').val(``);
											$('input.js-plano-id').val(0);
											planoListar();
											$('.js-plano-cancelar').hide();
										} else if(rtn.error) {
											swal({title: "Erro!", text: rtn.error, type:"error", confirmButtonColor: "#424242"});
										} else {
											swal({title: "Erro!", text: "Algum erro ocorreu durante a persistência dos planos", type:"error", confirmButtonColor: "#424242"});
										}
									}
								});

							}
						}
						function procPlano(id_procedimento_plano) {
							return planosProcedimentos.filter( x =>  x.id === id_procedimento_plano);
						}
						$(function(){
							$('input.money').maskMoney({symbol:'', allowZero:false, showSymbol:true, thousands:'.', decimal:',', symbolStay: true});
							planoListar();

							$('.js-plano-salvar').click(function(){
								planoAdicionar();
							});

							$('.js-plano-cancelar').click(function(){
								let id_procedimento_plano = eval($('input.js-plano-id').val());
								if(id_procedimento_plano>0) {
									const obj = procPlano($('input.js-plano-id').val());
									
									$('select.js-plano-id_plano').find(`option[value=${obj[0].id_plano}]`).remove();
									$('select.js-plano-id_plano').val(``);
									$('input.js-plano-valor').val(``);
									$('input.js-plano-obs').val(``);
									$('input.js-plano-id').val(0);
								}

								$(this).hide();
							});

							$('.js-planos-table').on('click','.js-remover',function(){
								let id_procedimento_plano = $(this).attr('data-id');
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
											let data = `ajax=planoRemover&id_procedimento=${id_procedimento}&id_procedimento_plano=${id_procedimento_plano}`; 
											$.ajax({
												type:"POST",
												data:data,
												url:'box/boxProcedimentos.php',
												success:function(rtn) {
													if(rtn.success) {
														if(id_procedimento_plano==$('input.js-plano-id').val()) {
															$('select.js-plano-id_plano').val(``);
															$('input.js-plano-valor').val(``);
															$('input.js-plano-obs').val(``);
															$('input.js-plano-id').val(0);
															$('.js-plano-cancelar').hide();
														}
														planoListar();
														swal.close();   
													} else if(rtn.error) {

														swal({title: "Erro!", text: rtn.error, type:"error", confirmButtonColor: "#424242"});
													} else {

														swal({title: "Erro!", text: "Algum erro ocorreu durante a remoção deste plano!", type:"error", confirmButtonColor: "#424242"});
													}

												},
												error:function(){
													swal({title: "Erro!", text: "Algum erro ocorreu durante a remoção deste plano!", type:"error", confirmButtonColor: "#424242"});
												}
											})
										} else {   
											swal.close();   
										} 
									});
							});

							$('.js-planos-table').on('click','.js-editar',function(){
								let id_procedimento_plano = $(this).attr('data-id');
								
								const obj = procPlano(id_procedimento_plano);
								if(obj[0]) {
									$('select.js-plano-id_plano').append(`<option value="${obj[0].id_plano}">${obj[0].plano}</option>`);
									$('input.js-plano-id').val(obj[0].id)
									$('select.js-plano-id_plano').val(obj[0].id_plano);
									$('input.js-plano-valor').val(obj[0].valor);
									$('input.js-plano-obs').val(obj[0].obs);
									$('.js-plano-cancelar').show();
								} else {
									swal({title: "Erro!", text: "Procedimento de Plano não encontrado!", type:"error", confirmButtonColor: "#424242"});
								}
							})
						});
					</script>
					<div class="registros">
						<table class="tablesorter js-planos-table">
							<thead>
								<tr>
									<th>Plano</th>
									<th>Valor</th>
									<th style="width:50px;">Obs.</th>
									<th style="width:120px"></th>
								</tr>
							</thead>
							<tbody>
								
							</tbody>
						</table>
					</div>
				<?php
					} else {
				?>
				<input type="hidden" name="planos" />
				<div class="colunas4">
					<dl>
						<dt>Plano</dt>
						<dd>
							<select class="js-plano-id_plano">
								<option value="">-</option>
								<?php
								foreach($_planos as $p) {
									echo '<option value="'.$p->id.'" data-plano="'.utf8_encode($p->titulo).'">'.utf8_encode($p->titulo).'</option>';
								}
								?>
							</select>
						</dd>
					</dl>
					<dl>
						<dt>Valor</dt>
						<dd><input type="text" class="js-plano-valor money" /></dd>
					</dl>
					
				</div>
				<div class="colunas4">
					
					<dl class="dl3">
						<dt>Obs.</dt>
						<dd><input type="text" class="js-plano-obs noupper" /></dd>
					</dl>
					<dl>
						<dt>&nbsp;</dt>
						<dd>
							<button type="button" class="button js-plano-salvar">Adicionar</button>
							<a href="javascript:;" class="js-plano-cancelar tooltip" style="display: none;color:red" title="Cancelar edição"><span class="iconify" data-icon="icons8:cancel"></span> cancelar edição</a>
						</dd>
					</dl>
				</div>
				<script type="text/javascript">
					var planos = [];
					function planosListar() {
						$('.js-planos-table tbody tr').remove();
						planos.forEach(x => {
							$('.js-plano-id_plano').find(`option[value=${x.id_plano}]`).remove();

							let tr = `<tr>
											<td>${x.plano}</td>
											<td>${x.valor}</td>
											<td class="js-obs"></td>
											<td>
												<a href="javascript:;" data-id="${x.id}" class="js-editar registros__acao registros__acao_sec"><i class="iconify" data-icon="bx:bxs-edit-alt"></i></a><a href="javascript:;" data-id="${x.id}" class="js-remover registros__acao registros__acao_sec"><i class="iconify" data-icon="bx:bxs-trash"></i></a>
											</td>
										</tr>`;
							$('.js-planos-table tbody').append(tr);

							let obs = x.obs.length>0?`<a href="javascript:" class="tooltip botao" title="${x.obs}"><span class="iconify" data-icon="fa-solid:comment"></span></a>`:`<span class="iconify" data-icon="fa-solid:comment" style="opacity:0.3"></span>`;
							$('.js-planos-table .js-obs:last').html(`<center>${obs}</center>`);
							$('.js-planos-table .js-obs:last a').tooltipster({theme:"borderless"});
							
						});

						$('input[name=planos]').val(JSON.stringify(planos));
					};
					function planoRemover(index) {

						let idPlano = planos[index].id_plano;
						let plano = planos[index].plano;

						$('.js-plano-id_plano').append(`<option value="${idPlano}" data-plano="${plano}">${plano}</option>`);
						planos.splice(index,1);
						
						planosListar();
					};
					function planoEditar(index) {
						let cont=0;
						index++;

						$('.js-plano-editando').val(index);

						planos.forEach(x=>{
							cont++;
							if(cont==index) {
								$('select.js-plano-id_plano').append(`<option value="${x.id_plano}" data-plano="${x.plano}">${x.plano}</option>`);
								$('select.js-plano-id_plano').find(`option[value=${x.id_plano}]`).prop('selected',true);
								$('input.js-plano-valor').val(x.valor);
								$('input.js-plano-obs').val(x.obs);
								$('.js-plano-cancelar').show();
								return;
							}
						});
					};
					function planoAdicionar() {
						let id_plano = $('select.js-plano-id_plano').val();
						let valor = $('input.js-plano-valor').val();
						let obs = $('input.js-plano-obs').val();

						if(id_plano.length==0) {
							swal({title: "Erro!", text: "Selecione o Plano!", type:"error", confirmButtonColor: "#424242"});
						} else if(valor.length==0) {
							swal({title: "Erro!", text: "Selecione o Valor!", type:"error", confirmButtonColor: "#424242"});
						}  else {

							let plano = $('select.js-plano-id_plano option:selected').attr('data-plano');
							let item = {};
							item.id_plano=id_plano;
							item.plano=plano;
							item.valor=valor;
							item.obs=obs;

							if($('.js-plano-editando').val().length && eval($('.js-plano-editando').val())>0) {
								let indexEdita=eval($('.js-plano-editando').val()); 
								let planosNovo = [];
								let cont = 0;
								planos.forEach(x => {
									cont++;
									if(cont==indexEdita) {
										planosNovo.push(item)
									} else {
										planosNovo.push(x);
									}
								});
								planos=planosNovo;

							} else {
								planos.push(item);
							}
							planosListar();
							
							$('select.js-plano-id_plano').val('');
							$('input.js-plano-valor').val('');
							$('input.js-plano-obs').val('');
							$('.js-plano-editando').val('');
							$('.js-plano-cancelar').hide();

						}
					}; 	
					$(function(){

						
						$('input.money').maskMoney({symbol:'', allowZero:false, showSymbol:true, thousands:'.', decimal:',', symbolStay: true});
						planosListar();

						$('.js-plano-salvar').click(function(){
							planoAdicionar();
						});

						$('.js-planos-table').on('click','.js-remover',function(){
							let obj = $(this);
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
										let index = $(obj).index('.js-planos-table .js-remover');
										planoRemover(index);
										swal.close(); 
									} else {   
										swal.close();   
									} 
								});
						});
						$('.js-plano-cancelar').click(function(){
							$('select.js-plano-id_plano').find(`option:selected`).remove();
							$('select.js-plano-id_plano').val(``);
							$('input.js-plano-valor').val(``);
							$('input.js-plano-obs').val(``);
							$('input.js-plano-id').val(0);
							

							$(this).hide();
						});

						$('table.js-planos-table').on('click','.js-editar',function(){
							let index = $(this).index('table.js-planos-table .js-editar');
							planoEditar(index);
						});
					});
				</script>
				<input type="hidden" class="js-plano-editando" />
				<div class="registros">
					<table class="tablesorter js-planos-table">
						<thead>
							<tr>
								<th>Plano</th>
								<th>Valor</th>
								<th style="width:50px;">Obs.</th>
								<th style="width:120px"></th>
							</tr>
						</thead>
						<tbody>
							
						</tbody>
					</table>
				</div>
				<?php
					}
				?>

			</fieldset>
				
		</form>
	</article>

</section>