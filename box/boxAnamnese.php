<?php
	require_once("../lib/conf.php");
	$dir="../";
	require_once("../usuarios/checa.php");

	$_avaliacaoTipos = array(
		'nota' 	  	  => 'Nota (0 ou 10)',
		'simnao' 	  => 'Sim / Não',
		'simnaotexto' => 'Sim / Não / Texto',
		'texto'  => 'Texto'
	);

	$_obrigatorio = array(1 => 'Sim', 0 => 'Não');
	$_alerta = array('sim' => 'Alerta se Resposta SIM', 'nao' => 'Alerta se Resposta NÃO', 'nenhum' => 'Sem Alerta');

	if(isset($_POST['ajax'])) {
		$sql = new Mysql();

		$rtn = array();
		if($_POST['ajax']=="anamnesePersistir") {

			$anamnese='';
			if(isset($_POST['id_anamnese']) and is_numeric($_POST['id_anamnese'])) {
				$sql->consult($_p."parametros_anamnese","*","where id='".$_POST['id_anamnese']."'");
				if($sql->rows) {
					$anamnese=mysqli_fetch_object($sql->mysqry);
				}
			}

			$vSQL="titulo='".utf8_decode(strtoupperWLIB(addslashes($_POST['titulo'])))."'";
			if(isset($_POST['perguntas']) and !empty($_POST['perguntas'])) {
				$vSQL.=",perguntas='".utf8_decode($_POST['perguntas'])."'";
			}

			if(is_object($anamnese)) {
				$vWHERE="where id=$anamnese->id";
				$sql->update($_p."parametros_anamnese",$vSQL,$vWHERE);
				$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='update',vsql='".addslashes($vSQL)."',vwhere='".addslashes($vWHERE)."',tabela='".$_p."parametros_anamnese',id_reg='".$anamnese->id."'");
				$id_reg=$anamnese->id;
			} else {
				$sql->add($_p."parametros_anamnese",$vSQL);
				$id_reg=$sql->ulid;
				$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='insert',vsql='".addslashes($vSQL)."',tabela='".$_p."parametros_anamnese"."',id_reg='".$sql->ulid."'");
			}

			$_tipos = array(
				'Nota (0 ou 10)' => 'nota',
				'Sim / Não' => 'simnao',
				'Sim / Não / Texto' => 'simnaotexto',
				'Texto' => 'texto'
			);

			$_obg = array('Sim' => 1, 'Não' => 0);	
			$_alert = array('Alerta se Resposta SIM' => 'sim', 'Alerta se Resposta NÃO' => 'nao', 'Sem Alerta' => 'nenhum');

			$id_anamnese=$id_reg;
			$sql->update($_p."parametros_anamnese_formulario","lixo=1","where id_anamnese=$id_anamnese");
			if(isset($_POST['perguntas']) and !empty($_POST['perguntas'])) {
				$perguntas=json_decode($_POST['perguntas']);

				foreach($perguntas as $v) {

					$vsql="id_anamnese='$v->id_anamnese',pergunta='".utf8_decode(addslashes($v->pergunta))."',tipo='".$_tipos[$v->tipo]."',alerta='".$_alert[$v->alerta]."',obrigatorio='".$_obg[$v->obrigatorio]."',lixo=0";

					if(isset($v->id_pergunta) and is_numeric($v->id_pergunta)) {
						$sql->consult($_p."parametros_anamnese_formulario","*","where id=$v->id_pergunta and id_anamnese=$id_anamnese");
						if($sql->rows) {
							$x=mysqli_fetch_object($sql->mysqry);
							$sql->update($_p."parametros_anamnese_formulario",$vsql,"where id=$x->id");
						} else {
							$sql->add($_p."parametros_anamnese_formulario",$vsql);
						}
					} else {
						$sql->add($_p."parametros_anamnese_formulario",$vsql);
					} 
				}
			}

			$rtn=array('success'=>true,'id_anamnese'=>$id_anamnese);
		} else if($_POST['ajax']=="anamneseRemover") {
			if(isset($_POST['id_anamnese']) and is_numeric($_POST['id_anamnese'])) {
				$sql->consult($_p."parametros_anamnese","*","where id='".$_POST['id_anamnese']."'");
				if($sql->rows) {
					$anamnese=mysqli_fetch_object($sql->mysqry);
				}
			}

			if(isset($anamnese) and is_object($anamnese)) {
				$vSQL="lixo=1";
				$vWHERE="where id=$anamnese->id";
				$sql->update($_p."parametros_anamnese",$vSQL,$vWHERE);

				$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='update',vwhere='".addslashes($vWHERE)."',vsql='".addslashes($vSQL)."',tabela='".$_p."parametros_anamnese',id_reg='".$anamnese->id."'");

				$rtn=array('success'=>true);

			} else {
				$rtn=array('success'=>false,'error'=>'Anamnese não encontrada');
			}
		} else if($_POST['ajax']=="perguntasListar") {
			$anamnese='';
			if(isset($_POST['id_anamnese']) and is_numeric($_POST['id_anamnese'])) {
				$sql->consult($_p."parametros_anamnese","*","where id='".addslashes($_POST['id_anamnese'])."' and lixo=0");
				if($sql->rows) {
					$anamnese=mysqli_fetch_object($sql->mysqry);
				}
			}

			$perguntas=array();
			if(is_object($anamnese)) {
				$sql->consult($_p."parametros_anamnese_formulario","*","WHERE id_anamnese='".$anamnese->id."' and lixo=0");
				if($sql->rows) {
					while($x=mysqli_fetch_object($sql->mysqry)) {
						$perguntas[]=array('id_pergunta' =>$x->id,
											'id_anamnese' =>$x->id_anamnese,
											'pergunta' =>utf8_encode(strtoupperWLIB(addslashes($x->pergunta))),
											'tipo' => isset($_avaliacaoTipos[$x->tipo])?$_avaliacaoTipos[$x->tipo]:"-",
											'obrigatorio' => isset($_obrigatorio[$x->obrigatorio])?$_obrigatorio[$x->obrigatorio]:"-",
											'alerta' => isset($_alerta[$x->alerta])?$_alerta[$x->alerta]:"-");
					}
				} 
				$rtn=array('success'=>true,'perguntas'=>$perguntas);
			} else {
				$rtn=array('success'=>false,'error'=>'Anamnese não definida!');
			}
		} else if($_POST['ajax']=='perguntaRemover') {

			$anamnese='';
			if(isset($_POST['id_anamnese']) and is_numeric($_POST['id_anamnese'])) {
				$sql->consult($_p."parametros_anamnese","*","where id='".$_POST['id_anamnese']."'");
				if($sql->rows) {
					$anamnese=mysqli_fetch_object($sql->mysqry);
				}
			}

			if(is_object($anamnese)) {
				$pergunta='';
				if(isset($_POST['id_pergunta']) and is_numeric($_POST['id_pergunta'])) {
					$sql->consult($_p."parametros_anamnese_formulario","*","where id='".$_POST['id_pergunta']."' and id_anamnese='".$anamnese->id."'");
					if($sql->rows) {
						$pergunta=mysqli_fetch_object($sql->mysqry);
					}
				}
				if(is_object($pergunta)) {

					$sql->update($_p."parametros_anamnese_formulario","lixo=$usr->id","where id=$pergunta->id and id_anamnese=$anamnese->id");

					$rtn=array('success'=>true);
				} else {
					$rtn=array("success"=>false,"error"=>"Pergunta não encontrada!");
				}
			} else {
				$rtn=array("success"=>false,"error"=>"Pergunta não encontrada!");
			}
		}

		header("Content-type: application/json");
		echo json_encode($rtn);
		die();
	}

	$campos=explode(",","titulo");
	foreach($campos as $v) $values[$v]='';

	$jsc = new Js();
	$anamnese='';
	$perguntas=array();
	if(isset($_GET['id_anamnese']) and is_numeric($_GET['id_anamnese'])) {
		$sql->consult($_p."parametros_anamnese","*","where id='".$_GET['id_anamnese']."'");
		if($sql->rows) {
			$anamnese=mysqli_fetch_object($sql->mysqry);

			$sql->consult($_p."parametros_anamnese_formulario","*","where id_anamnese=$anamnese->id and lixo=0");
			if($sql->rows) {
				while($x=mysqli_fetch_object($sql->mysqry)) {
					$perguntas[]=array('id_pergunta' =>$x->id,
										'id_anamnese' =>$x->id_anamnese,
										'pergunta' =>utf8_encode($x->pergunta),
										'tipo' => isset($_avaliacaoTipos[$x->tipo])?$_avaliacaoTipos[$x->tipo]:"-",
										'obrigatorio' => isset($_obrigatorio[$x->obrigatorio])?$_obrigatorio[$x->obrigatorio]:"-",
										'alerta' => isset($_alerta[$x->alerta])?$_alerta[$x->alerta]:"-");
				}
 			} 

			foreach($campos as $v) {
				$values[$v]=utf8_encode($anamnese->$v);
			}
		}
	}
?>
<script>
	var id_anamnese = '<?php echo is_object($anamnese)?$anamnese->id:'';?>';
	$(function(){
		$('.js-remover').click(function(){

			swal({   title: "Atenção",   text: "Você tem certeza que deseja remover este registro?",   type: "warning",   showCancelButton: true,   confirmButtonColor: "#DD6B55",   confirmButtonText: "Sim!",   cancelButtonText: "Não",   closeOnConfirm: false,   closeOnCancel: false }, function(isConfirm){   
				if (isConfirm) { 

					let data = `ajax=anamneseRemover&id_anamnese=${id_anamnese}`;   
					$.ajax({
						type:"POST",
						url:'box/boxAnamnese.php',
						data:data,
						success:function(rtn){
							swal.close();  
							if(rtn.success) {
								document.location.href=`pg_configuracao_anamnese_exames.php`
							} else if(rtn.error) {
								swal({title: "Erro!", text: rtn.error, type:"error", confirmButtonColor: "#424242"});
							} else {
								swal({title: "Erro!", text: "Anamnese não removida. Por favor tente novamente!", type:"error", confirmButtonColor: "#424242"});
							}
						},
						error:function(){
							swal.close();  
							swal({title: "Erro!", text: "Anamnese não removida. Por favor tente novamente!", type:"error", confirmButtonColor: "#424242"});
						}
					})
				} else {   
					swal.close();   
				} 
			});
		});
		$('.js-form-anamnese').submit(function(event){
		    let erro=false;
			$('form .obg').each(function(index,elem){
				if($('form .obg').attr('name')!==undefined && $('form .obg').val().length==0) {
					$(elem).addClass('erro');
					erro=true;
				}
			});

			if(erro===true) {
				swal({title: "Erro!", text: "Complete os campos destacados", type:"error", confirmButtonColor: "#424242"});
				
			} else {
				
				let campos = $('form.js-form-anamnese').serialize();
				let data = `ajax=anamnesePersistir&id_anamnese=${id_anamnese}&${campos}`;

				$.ajax({
					type:'POST',
					url:'box/boxAnamnese.php',
					data:data,
					success:function(rtn) {
						if(rtn.success) {
							<?php
								if(is_object($anamnese)) {
							?>
							$.fancybox.close();
							<?php
								} else {
							?>
							document.location.href=`?abrirAnamnese=${rtn.id_anamnese}`
							<?php
								}
							?>
						} else if(rtn.error) {
							swal({title: "Erro!", text: rtn.error, type:"error", confirmButtonColor: "#424242"});
						} else {
							swal({title: "Erro!", text: "Anamnese não salva. Por favor tente novamente!", type:"error", confirmButtonColor: "#424242"});
						}
					},
					error:function(){
						swal({title: "Erro!", text: "Anamnese não salva. Por favor tente novamente!", type:"error", confirmButtonColor: "#424242"});
					}
				})

			}
			return false;
		});
		$('.js-salvar').click(function(){
			$('.js-form-anamnese').submit();
		});
	});
</script>
<section class="modal" style="height:auto; width:950px;">

	<header class="modal-header">
		<div class="filtros">

			<?php
				if(empty($anamnese)) {
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

		<form method="post" class="form js-form-anamnese">
			<input type="submit" style="display: none;" />
			<fieldset>
				<legend><span class="badge">1</span> Defina o título da Anamnese</legend>
				<div>
					<dl>
						<dt>Título</dt>
						<dd>
							<input type="text" name="titulo" value="<?php echo $values['titulo'];?>"  class="obg" />
						</dd>
					</dl>
				</div>
			</fieldset>

			<?php
				if(is_object($anamnese)) {
			?>
			<textarea name="perguntas" style="text-transform: none;display: none;"><?php echo isset($values['perguntas'])?$values['perguntas']:'';?></textarea>
			<fieldset>
				<legend><span class="badge">2</span> Defina as perguntas</legend>
				<div>
					<dl>
						<dt>Pergunta</dt>
						<dd>
							<input type="text" name="pergunta" class="js-pergunta-titulo" />
						</dd>
					</dl>
					<div class="colunas3">
						<dl>
							<dt>Tipo</dt>
							<dd>
								<select name="pergunta_tipo" class="js-tipo">
									<option value="">-</option>
									<option value="nota">Nota (0 ou 10)</option>
									<option value="simnao">Sim / Não</option>
									<option value="simnaotexto">Sim / Não / Texto</option>
									<option value="texto">Texto</option>
								</select>
							</dd>
						</dl>
						<dl>
							<dt>Obrigatório</dt>
							<dd>
								<select name="pergunta_obrigatorio" class="js-obrigatorio">
									<option value="">-</option>
									<option value="1">Sim</option>
									<option value="0">Não</option>
								</select>
							</dd>
						</dl>
						<dl class="js-dl-alerta"style="display: none;">
							<dt>Alerta para Pergunta</dt>
							<dd>
								<select name="pergunta_alerta" class="js-alerta">
									<option value="nao">Alerta se Resposta NÃO</option>
									<option value="sim">Alerta se Resposta SIM</option>
									<option value="nenhum" selected>Sem Alerta</option>
								</select>
							</dd>
						</dl>
						<dl>
							<dd><button type="button" class="button js-btn-add">Adicionar</button></dd>
						</dl>
					</div>
					<script>
						var id_anamnese = '<?php echo $anamnese->id;?>';
						var anamnesePerguntas = JSON.parse(jsonEscape(`<?php echo json_encode($perguntas);?>`));
						function perguntasListar() {
							$('.js-pergunta').remove();
							anamnesePerguntas.forEach(x => {

								var html = `<div class="reg-group js-pergunta">
												<div class="reg-color" style="background-color:green"></div>
												<div class="reg-data js-titulo" style="flex:0 1 300px">
													<h1>${x.pergunta}</h1>
													<p>${x.tipo}</p>
												</div>
												<div class="reg-data js-obrigatorio" style="flex:0 1 70px;">
													<h1>Obrigatório</h1>
													<p>${x.obrigatorio}</p>
												</div>	
												<div class="reg-data js-alerta" style="flex:0 1 70px;">
													<h1>Alerta</h1>
													<p>${x.alerta}</p>
												</div>	
												<div class="reg-icon">
													<a href="javascript:;" class="js-deleta" data-id="${x.id_pergunta}"><i class="iconify" data-icon="bx-bx-trash"></i></a>
												</div>							
											</div>`;
							
								$('.js-div-perguntas').append(html);
								$('.js-pergunta .reg-icon .js-deleta:last').click(function() {
									let index = $(this).index('.js-pergunta .reg-icon .js-deleta');
									swal({   title: "Atenção",   text: "Você tem certeza que deseja remover este registro?",   type: "warning",   showCancelButton: true,   confirmButtonColor: "#DD6B55",   confirmButtonText: "Sim!",   cancelButtonText: "Não",   closeOnConfirm: false,   closeOnCancel: false }, function(isConfirm){   if (isConfirm) {  perguntasRemover(index); swal.close();   } else {   swal.close();   } });
								});
							})
							let json = JSON.stringify(anamnesePerguntas);
							$('textarea[name=perguntas]').val(json);
						}

						function perguntasRemover(index) {
							anamnesePerguntas.splice(index,1);
							perguntasListar();
						}

						$(function(){
							perguntasListar();
							$('.js-btn-add').click(function(){
								let pergunta = $('.js-pergunta-titulo').val();
								let tipo = $('.js-tipo option:selected').val();
								let obrigatorio = $('.js-obrigatorio option:selected').val();
								let alerta = $('.js-alerta option:selected').val();

								let tipoText = $('.js-tipo option:selected').text();
								let obrigatorioText = $('.js-obrigatorio option:selected').text();
								let alertaText = $('.js-alerta option:selected').text();

								if(pergunta.length==0) {
									swal({title: "Erro!", text: 'Digite a pergunta', html:true, type:"error", confirmButtonColor: "#424242"});
								} else if(tipo.length==0) {
									swal({title: "Erro!", text: 'Selecione o tipo da pergunta', html:true, type:"error", confirmButtonColor: "#424242"});
								} else if(obrigatorio.length==0) {
									swal({title: "Erro!", text: 'Selecione a obrigatoriedade', html:true, type:"error", confirmButtonColor: "#424242"});
								} else if(tipo.length>0 && (tipo=='simnao' || tipo=='simnaotexto') && alerta.length==0) {
									swal({title: "Erro!", text: 'Selecione o alerta da pergunta', html:true, type:"error", confirmButtonColor: "#424242"});
								} else {
									$('.js-pergunta-titulo').val(``);
									$('.js-tipo').val(``);
									$('.js-obrigatorio').val(``);
									$('.js-alerta').val(``);
									$('.js-dl-alerta').hide();

									let item = {};
									item.id_anamnese = id_anamnese;
	      							item.pergunta = pergunta;
	      							item.tipo = tipoText;
	      							item.obrigatorio = obrigatorioText;
	      							item.alerta = alertaText;
	      							
	      							anamnesePerguntas.push(item);
									perguntasListar();
								}
							});
							$('.js-tipo').change(function(){
								let tipo = $(this).val();

								if(tipo.length>0) {
									if(tipo=='simnao') {
										$('.js-dl-alerta').show();
										$('select[name=pergunta_alerta]').addClass('obg');
									} else if(tipo=='simnaotexto') {
										$('.js-dl-alerta').show();
										$('select[name=pergunta_alerta]').addClass('obg');
									} else {
										$('.js-alerta').val('nenhum');
										$('.js-dl-alerta').hide();
										$('select[name=pergunta_alerta]').removeClass('obg');
									}
								} else {
									$('.js-alerta').val('nenhum');
									$('.js-dl-alerta').hide();
									$('select[name=pergunta_alerta]').removeClass('obg');
								}
							});
						});
					</script>
					<div class="reg js-div-perguntas" style="margin-top:2rem;"></div> 

				</div>
			</fieldset>
			<?php
				}
			?>
				
		</form>
	</article>

</section>