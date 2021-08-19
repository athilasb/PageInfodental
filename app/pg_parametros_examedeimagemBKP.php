<?php

	if(isset($_POST['ajax'])) {
		require_once("lib/conf.php");	
		require_once("usuarios/checa.php");

		$sql = new Mysql();

		$rtn = array();
		if($_POST['ajax']==="categoriasAtualizaLista") {

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
									'valor'=>number_format($x->valor,2,",","."),
									'comissionamento'=>number_format($x->comissionamento,2,",","."),
									'custo'=>number_format($x->custo,2,",","."));
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
					if(is_object($plano)) {

						$vSQL="id_procedimento=$procedimento->id,
								id_plano=$plano->id,
								valor='".valor($_POST['valor'])."',
								custo='".valor($_POST['custo'])."',
								comissionamento='".valor($_POST['comissionamento'])."',
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
	

	$title="";
	include "includes/header.php";
	include "includes/nav.php";

	if($usr->tipo!="admin" and !in_array("parametros",$_usuariosPermissoes)) {
		$jsc->jAlert("Você não tem permissão para acessar esta área!","erro","document.location.href='dashboard.php'");
		die();
	}

	$_regioes=array();
	$sql->consult($_p."parametros_procedimentos_regioes","*","where lixo=0 order by titulo asc");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_regioes[$x->id]=$x;
	}
	$_regioes=array();
	$sql->consult($_p."parametros_procedimentos_regioes","*","where lixo=0 order by titulo asc");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_regioes[$x->id]=$x;
	}

	$_fases=array();
	$sql->consult($_p."parametros_fases","*","where lixo=0 order by titulo asc");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_fases[$x->id]=$x;
	}

	$_planos=array();
	$sql->consult($_p."parametros_planos","*","where lixo=0 order by titulo asc");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_planos[$x->id]=$x;
	}

	$_camposEvolucao=array();
	$sql->consult($_p."parametros_procedimentos_evolucoes","*","where lixo=0 order by ordem asc");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_camposEvolucao[$x->id]=$x;
	}

	$_especialidades=array();
	$sql->consult($_p."parametros_especialidades","*","where lixo=0 order by titulo asc");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_especialidades[$x->id]=$x;
	}


	$values=$adm->get($_GET);

	$_table=$_p."parametros_procedimentos";
	$_page=basename($_SERVER['PHP_SELF']);


?>
<script type="text/javascript">
	<?php
		if(isset($values['id_subcategoria'])) $idSubCategoria=$values['id_subcategoria'];
		else $idSubCategoria='';
	?>
	var id_subcategoria='<?php echo $idSubCategoria;?>';
	
</script>
<section class="content">

	<?php /*<header class="caminho">
		<h1 class="caminho__titulo">Configurações <i class="iconify" data-icon="bx-bx-chevron-right"></i> <strong>Procedimentos</strong></h1>
		<a href="javascript:;" class="caminho__tutorial button button__sec"><i class="iconify" data-icon="ic-baseline-slow-motion-video"></i> ASSISTIR TUTORIAL</a>
	</header>*/?>
	<?php
	require_once("includes/abaConfiguracoes.php");
	?>

		<section style="padding:2rem 0;">


			<section class="content__item">
			
				<?php
				if(isset($_GET['form'])) {
					$cnt='';
					$campos=explode(",","id_especialidade,id_subcategoria,titulo,id_regiao,face,camposEvolucao,quantitativo");
					
					foreach($campos as $v) $values[$v]='';
					$values['camposEvolucao']=array();
					
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
								//echo $vSQL;die();
								$sql->add($_table,$vSQL);
								$id_reg=$sql->ulid;
								$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='insert',vsql='".addslashes($vSQL)."',tabela='".$_table."',id_reg='".$sql->ulid."'");


								$id_procedimento=$id_reg;
								if(isset($_POST['planos']) and !empty($_POST['planos'])) {
									$planos=json_decode($_POST['planos'],true);
									if(is_array($planos) and count($planos)>0) {
										foreach($planos as $p) {
											$vsql="id_procedimento=$id_procedimento,
													id_plano='".addslashes($p['id_plano'])."',
													valor='".valor($p['valor'])."',
													comissionamento='".valor($p['comissionamento'])."',
													custo='".valor($p['custo'])."',
													obs='".addslashes($p['obs'])."',
													data=now(),
													id_usuario=$usr->id";

											$sql->add($_table."_planos",$vsql);
										}
									}
								}
								if(isset($_POST['fases']) and !empty($_POST['fases'])) {
									$fases=json_decode($_POST['fases'],true);
									if(is_array($fases) and count($fases)>0) {
										foreach($fases as $f) {
											$evolucao='';
											if(isset($f['evolucao']) and is_array($f['evolucao'])) {
												$evolucao=implode(",",$f['evolucao']);
											}
											$vsql="id_procedimento=$id_procedimento,
													id_fase='".addslashes($f['id_fase'])."',
													evolucao=',".addslashes($evolucao).",',
													data=now(),
													id_usuario=$usr->id";
											$sql->add($_table."_fases",$vsql);
										}
									}
								
								}
							}

							$msgErro='';
							if(!empty($msgErro)) {
								$jsc->jAlert($msgErro,"erro","");
							} else {
								$jsc->jAlert("Informações salvas com sucesso!","sucesso","document.location.href='".$_page."?".$url."'");
								die();
							}
						}
					}
					?>
					<form method="post" class="form formulario-validacao"  autocomplete="off" enctype="multipart/form-data">
						
						<input type="hidden" name="acao" value="wlib" />
						<fieldset>
							<legend>Dados do Exame</legend>

							
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
												echo  '<option value="'.$v->id.'" data-face="'.$v->face.'" data-quantitativo="'.$v->quantitativo.'"'.($v->id==$values['id_regiao']?' selected':'').'>'.utf8_encode($v->titulo).'</option>';
											}
											?>
										</select>
									</dd>
								</dl>
								
								
							</div>
						</fieldset>

						<fieldset>
							<legend>Clínicas Radiológicas</legend>

							<p>Cadastre as Clínicas Radiológicas que executam o Exame</p>
						<?php
						if(is_object($cnt)) {
						?>
							<input type="hidden" class="js-plano-id" value="0"  />
							<div class="colunas4">
								<dl>
									<dt>Clínica Radiológica</dt>
									<dd>
										<select class="js-plano-id_plano">
										</select>
									</dd>
								</dl>
								
								<dl>
									<dt>&nbsp;</dt>
									<dd>
										<a href="javascript:;" class="button button__sec js-plano-salvar"><i class="iconify" data-icon="bx-bx-check"></i></a>
										
										<a href="javascript:;" class="js-plano-cancelar tooltip" style="display: none;color:red" title="Cancelar edição"><span class="iconify" data-icon="icons8:cancel"></span> cancelar edição</a>
									</dd>
								</dl>
							</div>

							<script type="text/javascript">
								var id_procedimento = '<?php echo $cnt->id;?>';
								var planosProcedimentos = [];
								const planoListar = () => {
									let data = `ajax=planoListar&id_procedimento=${id_procedimento}`;
									$.ajax({
										type:'POST',
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
													
													let tr = `<tr><td>${x.plano}</td><td>${x.valor}</td><td>${x.custo}</td><td>${x.comissionamento}</td><td class="js-obs"></td><td><a href="javascript:;" data-id="${x.id}" class="js-editar registros__acao registros__acao_sec"><i class="iconify" data-icon="bx:bxs-edit-alt"></i></a><a href="javascript:;" data-id="${x.id}" class="js-remover registros__acao registros__acao_sec"><i class="iconify" data-icon="bx:bxs-trash"></i></a></tr>`;
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
								const planoAdicionar = () => {
									let id_plano = $('select.js-plano-id_plano').val();
									let valor = $('input.js-plano-valor').val();
									let custo = $('input.js-plano-custo').val();
									let comissionamento = $('input.js-plano-comissionamento').val();
									let obs = $('input.js-plano-obs').val();
									let id_procedimento_plano = $('input.js-plano-id').val();

									if(id_plano.length==0) {
										swal({title: "Erro!", text: "Selecione o Plano!", type:"error", confirmButtonColor: "#424242"});
									} else if(valor.length==0) {
										swal({title: "Erro!", text: "Selecione o Valor!", type:"error", confirmButtonColor: "#424242"});
									} else {

										let data = `ajax=planoAdicionar&id_plano=${id_plano}&id_procedimento=${id_procedimento}&valor=${valor}&custo=${custo}&obs=${obs}&id_procedimento_plano=${id_procedimento_plano}&comissionamento=${comissionamento}`;
										console.log(data);
										$.ajax({
											type:'POST',
											data: data,
											success:function(rtn) {
												if(rtn.success===true) {
													$('select.js-plano-id_plano').val(``);
													$('input.js-plano-valor').val(``);
													$('input.js-plano-custo').val(``);
													$('input.js-plano-comissionamento').val(``);
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
											$('input.js-plano-custo').val(``);
											$('input.js-plano-comissionamento').val(``);
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
														success:function(rtn) {
															if(rtn.success) {
																if(id_procedimento_plano==$('input.js-plano-id').val()) {
																	$('select.js-plano-id_plano').val(``);
																	$('input.js-plano-valor').val(``);
																	$('input.js-plano-custo').val(``);
																	$('input.js-plano-comissionamento').val(``);
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
											$('input.js-plano-custo').val(obj[0].custo);
											$('input.js-plano-comissionamento').val(obj[0].comissionamento);
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
											<th>Clínica Radiológica</th>
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
									<dt>Clínicas Radiológicas</dt>
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
									<dt>&nbsp;</dt>
									<dd>
										<a href="javascript:;" class="button button__sec js-plano-salvar"><i class="iconify" data-icon="bx-bx-check"></i></a>
										
										<a href="javascript:;" class="js-plano-cancelar tooltip" style="display: none;color:red" title="Cancelar edição"><span class="iconify" data-icon="icons8:cancel"></span> cancelar edição</a>
									</dd>
								</dl>
							</div>
							<script type="text/javascript">
								var planos = [];
								const planosListar = () => {
									$('.js-planos-table tbody tr').remove();
									planos.forEach(x => {
										$('.js-plano-id_plano').find(`option[value=${x.id_plano}]`).remove();

										let tr = `<tr>
														<td>${x.plano}</td>
														<td>${x.valor}</td>
														<td>${x.custo}</td>
														<td>${x.comissionamento}</td>
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
								const planoRemover = (index) => {

									let idPlano = planos[index].id_plano;
									let plano = planos[index].plano;

									$('.js-plano-id_plano').append(`<option value="${idPlano}" data-plano="${plano}">${plano}</option>`);
									planos.splice(index,1);
									
									planosListar();
								};
								const planoEditar = (index) => {
									let cont=0;
									index++;

									$('.js-plano-editando').val(index);

									planos.forEach(x=>{
										cont++;
										if(cont==index) {
											$('select.js-plano-id_plano').append(`<option value="${x.id_plano}" data-plano="${x.plano}">${x.plano}</option>`);
											$('select.js-plano-id_plano').find(`option[value=${x.id_plano}]`).prop('selected',true);
											$('input.js-plano-valor').val(x.valor);
											$('input.js-plano-custo').val(x.custo);
											$('input.js-plano-comissionamento').val(x.comissionamento);
											$('input.js-plano-obs').val(x.obs);
											$('.js-plano-cancelar').show();
											return;
										}
									});
								};
								const planoAdicionar = () => {
									let id_plano = $('select.js-plano-id_plano').val();
									let valor = $('input.js-plano-valor').val();
									let custo = $('input.js-plano-custo').val();
									let comissionamento = $('input.js-plano-comissionamento').val();
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
										item.custo=custo;
										item.comissionamento=comissionamento;
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
										$('input.js-plano-custo').val('');
										$('input.js-plano-comissionamento').val('');
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
										$('input.js-plano-custo').val(``);
										$('input.js-plano-comissionamento').val(``);
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
											<th>Clínicas Radiológicas</th>
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

					<div class="acoes">
						<a href="<?php echo $_page."?".$url;?>" class="button button__lg button__ter"><i class="iconify" data-icon="bx-bx-left-arrow-alt"></i></a>
						<?php
						if(is_object($cnt)) {
						?>
						
						<a data-fancybox data-type="ajax" data-src="ajax/log.php?table=<?php echo $_table;?>&id=<?php echo $cnt->id;?>" href="javascript:;" class="button button__lg button__ter"><span class="iconify" data-icon="fa:history" data-inline="false"></span> Logs</a>
						<?php	
						}
						?>
						<a href="javascript:;" class="button button__lg btn-submit"><i class="iconify" data-icon="bx-bx-check"></i> Salvar</a>			
					</div>
					<?php
				} else {
				

					?>
					
					<section class="filtros">
						<form method="get" class="filtros-form form">
							<input type="hidden" name="csv" value="0" />
							<div class="colunas4">
								<dl class="">
									<dt>Clínica Radiológica</dt>
									<dd>
										<select name="id_laboratorio" >
											<option value="">-</option>
											<?php
											foreach($_especialidades as $v) echo '<option value="'.$v->id.'"'.($values['id_especialidade']==$v->id?' selected':'').'>'.utf8_encode($v->titulo).'</option>';
											?>
										</select>
									</dd>
								</dl>
								<dl>		
									<dt>&nbsp;</dt>			
									<dd><button type="submit" class="button button__sec"><i class="iconify" data-icon="bx-bx-search" data-inline="false"></i></button></dd>
								</dl>
							</div>
						</form>
						<div class="filtros-acoes">
							<a href="<?php echo $_page."?form=1&$url";?>" data-padding="0" class="filtros-acoes__button tooltip" title="Adicionar"><i class="iconify" data-icon="ic-baseline-add"></i></a>
						</div>
					</section>

				
					<?php
					
					if(isset($_GET['deleta']) and is_numeric($_GET['deleta']) and $usr->tipo=="admin") {
						$vSQL="lixo='1'";
						$vWHERE="where id='".$_GET['deleta']."'";
						$sql->update($_table,$vSQL,$vWHERE);
						$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='delete',vsql='".addslashes($vSQL)."',vwhere='".addslashes($vWHERE)."',tabela='".$_table."',id_reg='".$_GET['deleta']."'");
						$jsc->jAlert("Registro excluído com sucesso!","sucesso","document.location.href='".$_page."?".$url."'");
						die();
					}
					
					$where="WHERE lixo='0'";

					if(isset($values['id_especialidade']) and is_numeric($values['id_especialidade'])) $where.=" and id_especialidade='".$values['id_especialidade']."'";
					if(isset($values['id_subcategoria']) and is_numeric($values['id_subcategoria'])) $where.=" and id_subcategoria='".$values['id_subcategoria']."'";
					
					$sql->consult($_table,"*",$where." order by id");
					
					?>

					<div class="registros-qtd">
						<p class="registros-qtd__item"><?php echo $sql->rows;?> registros</p>
					</div>
					<div class="registros">
						
						<table class="tablesorter">
							<thead>
								<tr>
									<th>Nome do Exame</th>
									<th style="width:270px;">Região</th>
									<th style="width:270px;">Clínica Radiológica</th>
									<th style="width:120px;">Ações</th>
								</tr>
							</thead>
							<tbody>
							<?php
							$registros=$procediemntosIDs=array();
							while($x=mysqli_fetch_object($sql->mysqry)) {
								$registros[$x->id]=$x;
								$procediemntosIDs[]=$x->id;
							} 

							$_procedimentosPlanos=array();
							if(count($procediemntosIDs)>0) {
								$sql->consult($_table."_planos","*","where id_procedimento IN (".implode(",",$procediemntosIDs).") and lixo=0");
								if($sql->rows) {
									while($x=mysqli_fetch_object($sql->mysqry)) {
										$_procedimentosPlanos[$x->id_procedimento][]=$x;
									}
								}
							}
				 
							foreach($registros as $x) {
							?>
							<tr>
								<td><?php echo utf8_encode($x->titulo);?></td>
								<td><?php echo isset($_regioes[$x->id_regiao])?utf8_encode($_regioes[$x->id_regiao]->titulo):"-";?></td>
								<td>lab</td>
								<td>
									<a href="<?php echo $_page;?>?form=1&edita=<?php echo $x->id."&".$url;?>" class="registros__acao"><i class="iconify" data-icon="bx:bxs-edit-alt"></i></a>
									<?php if($usr->tipo=="admin") { ?><a href="<?php echo $_page;?>?deleta=<?php echo $x->id."&".$url;?>" class="registros__acao registros__acao_sec js-deletar"><i class="iconify" data-icon="bx:bxs-trash"></i></a><?php } ?>
								</td>
							</tr>
							<?php
							}
							?>
							</tbody>
						</table>
					</div>
				
				<?php
				}
				?>

				
			</section>
		</section>
	</section>
</section>

<?php
	include "includes/footer.php";
?>