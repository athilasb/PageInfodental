<?php
	require_once("../lib/conf.php");
	$dir="../";
	require_once("../usuarios/checa.php");

	$_avaliacaoTipos = array(
		'nota' 	  			  => 'Nota (0 ou 10)',
		'radiobox' 			  => 'Sim / Não',
		'radiobox_discursiva' => 'Sim / Não / Texto',
		'discursiva'		  => 'Texto'
	);

	if(isset($_POST['ajax'])) {
		$sql = new Mysql();

		$rtn = array();
		if($_POST['ajax']=="perguntasListar") {
			$anamnese='';
			if(isset($_POST['id_anamnese']) and is_numeric($_POST['id_anamnese'])) {
				$sql->consult($_p."parametros_anamnese","*","where id='".addslashes($_POST['id_anamnese'])."' and lixo=0");
				if($sql->rows) {
					$anamnese=mysqli_fetch_object($sql->mysqry);
				}
			}

			$perguntas=array();
			if(is_object($anamnese)) {
				$sql->consult($_p."parametros_anamnese_formulario","*","WHERE id_anamnese='".$anamnese->id."' and lixo=0 order by ordem asc");
				if($sql->rows) {
					while($x=mysqli_fetch_object($sql->mysqry)) {
						$perguntas[]=array('id' =>$x->id,
											'id_anamnese' =>$x->id_anamnese,
											'pergunta' =>utf8_encode($x->pergunta),
											'tipo' => isset($_avaliacaoTipos[$x->tipo])?$_avaliacaoTipos[$x->tipo]:"-");
					}
				} 
				$rtn=array('success'=>true,'perguntas'=>$perguntas);
			} else {
				$rtn=array('success'=>false,'error'=>'Anamnese não definida!');
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
	if(isset($_GET['id_anamnese']) and is_numeric($_GET['id_anamnese'])) {
		$sql->consult($_p."parametros_anamnese","*","where id='".$_GET['id_anamnese']."'");
		if($sql->rows) {
			$anamnese=mysqli_fetch_object($sql->mysqry);

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
								$.fancybox.close();
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
				
				let campos = $('form.js-form-anamnese').serialize();
				let data = `ajax=anamnesePersistir&id_anamnese=${id_anamnese}&${campos}`;

				$.ajax({
					type:'POST',
					url:'box/boxAnamnese.php',
					data:data,
					success:function(rtn) {
						if(rtn.success) {
							$.fancybox.close();
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
									<option value="radiobox">Sim / Não</option>
									<option value="radiobox_discursiva">Sim / Não / Texto</option>
									<option value="discursiva">Texto</option>
								</select>
							</dd>
						</dl>
						<dl>
							<dt>Obrigatório</dt>
							<dd>
								<select name="pergunta_obrigatorio" class="js-obrigatorio">
									<option value="">-</option>
									<option value="sim">Sim</option>
									<option value="nao">Não</option>
								</select>
							</dd>
						</dl>
						<dl class="js-dl-alerta"style="display: none;">
							<dt>Alerta para Pergunta</dt>
							<dd>
								<select name="pergunta_alerta" class="js-alerta">
									<option value="">-</option>
									<option value="alerta_nao">Alerta se Resposta NÃO</option>
									<option value="alerta_sim">Alerta se Resposta SIM</option>
									<option value="sem_alerta">Sem Alerta</option>
								</select>
							</dd>
						</dl>
						<dl>
							<dd><button type="button" class="button js-btn-add">Adicionar</button></dd>
						</dl>
					</div>
					<script>
						var id_anamnese = '<?php echo $anamnese->id;?>';
						var anamnesePerguntas = [];
						function perguntasListar() {
							let data = `ajax=perguntasListar&id_anamnese=${id_anamnese}`;
							$.ajax({
								type:'POST',
								url:'box/boxAnamnese.php',
								data:data,
								success:function(rtn) {
									if(rtn.success===true) {

										$('.js-pergunta').remove();
										anamnesePerguntas = rtn.perguntas;
										rtn.perguntas.forEach(x => {

											var html = `<div class="reg-group js-pergunta">
															<div class="reg-color" style="background-color:green"></div>
															<div class="reg-data js-titulo" style="flex:0 1 300px">
																<h1>${x.pergunta}</h1>
																<p>${x.tipo}</p>
															</div>
															<div class="reg-data js-obrigatorio">
																<p></p>
															</div>		
															<div class="reg-icon">
																<a href="javascript:;" class="js-tr-deleta"><i class="iconify" data-icon="bx-bx-trash"></i></a>
															</div>							
														</div>`;
										
											$('.js-div-perguntas').append(html);
										})
									} else if(rtn.error) {
										swal({title: "Erro!", text: rtn.error, type:"error", confirmButtonColor: "#424242"});
									} else {
										swal({title: "Erro!", text: "Algum erro ocorreu durante a listagem das perguntas", type:"error", confirmButtonColor: "#424242"});
									}
								},
								error:function(){

								}
							});
						}

						$(function(){
							$('.js-btn-add').click(function(){
								let pergunta = $('.js-pergunta-titulo').val();
								let tipo = $('.js-tipo').val();
								let obrigatorio = $('.js-obrigatorio').val();
								let alerta = $('.js-alerta').val();

								if(pergunta.length==0) {
									swal({title: "Erro!", text: 'Digite a pergunta', html:true, type:"error", confirmButtonColor: "#424242"});
								} else if(tipo.length==0) {
									swal({title: "Erro!", text: 'Selecione o tipo da pergunta', html:true, type:"error", confirmButtonColor: "#424242"});
								} else if(obrigatorio.length==0) {
									swal({title: "Erro!", text: 'Selecione a obrigatoriedade', html:true, type:"error", confirmButtonColor: "#424242"});
								} else if(tipo.length>0 && (tipo=='radiobox' || tipo=='radiobox_discursiva') && alerta.length==0) {
									swal({title: "Erro!", text: 'Selecione o alerta da pergunta', html:true, type:"error", confirmButtonColor: "#424242"});
								}
							});
							$('.js-tipo').click(function(){
								let tipo = $(this).val();

								if(tipo.length>0) {
									if(tipo=='radiobox') {
										$('.js-dl-alerta').show();
										$('select[name=pergunta_alerta]').addClass('obg');
									} else if(tipo=='radiobox_discursiva') {
										$('.js-dl-alerta').show();
										$('select[name=pergunta_alerta]').addClass('obg');
									} else {
										$('.js-dl-alerta').hide();
										$('select[name=pergunta_alerta]').removeClass('obg');
									}
								} else {
									$('.js-dl-alerta').hide();
									$('select[name=pergunta_alerta]').removeClass('obg');
								}
							});
							perguntasListar();
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