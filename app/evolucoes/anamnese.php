<?php
	session_start();

	require_once("../lib/conf.php");
	require_once("../lib/classes.php");
	$sql = new Mysql();

	if(isset($_POST['ajax'])) {
		$rtn = [];

		# capta dados
			$evolucao=$paciente=$resposta='';
			if(isset($_POST['id_evolucao']) and !empty($_POST['id_evolucao'])) {
				$sql->consult($_p."pacientes_evolucoes","*","where md5(id) = '".addslashes($_GET['id_evolucao'])."' and lixo=0");
				if($sql->rows) {
					$evolucao=mysqli_fetch_object($sql->mysqry);


					$sql->consult($_p."pacientes","*","where id=$evolucao->id_paciente and lixo=0");
					if($sql->rows) $paciente=mysqli_fetch_object($sql->mysqry);

					if(isset($_POST['id_resposta']) and is_numeric($_POST['id_resposta'])) {
						$sql->consult($_p."pacientes_evolucoes_anamnese","*","where id_evolucao=$evolucao->id and id=".$_POST['id_resposta']);
						if($sql->rows) $resposta=mysqli_fetch_object($sql->mysqry);
					}

				}
			}

		# persistir informacoes
		if($_POST['ajax']=="persistir") {

			
			$val = isset($_POST['val']) ? $_POST['val'] : '';
			$tipo = isset($_POST['tipo']) ? $_POST['tipo'] : '';

			$erro='';
			if(empty($evolucao)) $erro='Evolução não encontrada!';
			else if(empty($paciente)) $erro='Paciente não encontrado!';
			else if(empty($resposta)) $erro='Pergunta não encontrada!';
			else if(empty($tipo)) $erro='Tipo de resposta inválida!';

			if(empty($erro)) {

				if($tipo=="texto") {
					$sql->update($_p."pacientes_evolucoes_anamnese","resposta_texto='".addslashes(utf8_decode($val))."'","where id=$resposta->id");
				} else {
					$sql->update($_p."pacientes_evolucoes_anamnese","resposta='".addslashes(utf8_decode($val))."'","where id=$resposta->id");
				}

				$rtn=array('success'=>true);


			} else {
				$rtn=array('success'=>false,'error'=>$erro);
			}

		}

		# finalizar anamnese para poder assinar
		else if($_POST['ajax']=="finalizar") {

			$erro='';
			if(empty($evolucao)) $erro='Evolução não encontrada!';
			else if(empty($paciente)) $erro='Paciente não encontrado!';

			if(empty($erro)) {

				$sql->update($_p."pacientes_evolucoes","enviarLinkFinalizado=now()","where id=$evolucao->id");
				generatePDF($evolucao->id);

				$rtn=array('success'=>true);


			} else {
				$rtn=array('success'=>false,'error'=>$erro);
			}

		} 

		# autenticacao
		else if($_POST['ajax']=="auth") {

			// capta dados
			$cpf = isset($_POST['cpf']) ? numero($_POST['cpf']) : '';
			$dn = '';
			if(isset($_POST['dn']) and !empty($_POST['dn']) and strpos($_POST['dn'],'/')) {
				list($dia,$mes,$ano) = @explode("/",$_POST['dn']);
				if(checkdate($mes, $dia, $ano)) {
					$dn=$ano."-".$mes."-".$dia;
				} 
			} 

			// validacao
			$erro='';
			if(empty($paciente)) $erro='Paciente não encontrado';
			else if(empty($cpf)) $erro='Preencha o campo de CPF';
			else if(strlen($cpf)!=11) $erro='Digite um CPF com 11 dígitos';
			else if(!verificaCpf($cpf)) $erro='CPF inválido';
			else if(empty($dn)) $erro='Preencha o campo Data de Nascimento com dados válidos';

			if(empty($erro)) {

				if($cpf==$paciente->cpf and strtotime($paciente->data_nascimento)==strtotime($dn)) {

					$_SESSION['infod_dn']=strtotime($paciente->data_nascimento);
					$_SESSION['infod_cpf']=md5($paciente->cpf);

				} else {
					$erro='CPF e/ou Data de Nascimento inválidos!';
				}
			} 


			if(!empty($erro)) {
				$rtn=array('success'=>false,'error'=>$erro);
			} else {
				$rtn=array('success'=>true);
			}

		}

		else {
			$rtn=array('success'=>false,'error'=>'Nenhum método definido!');
		}

		header("Content-type: application/json");
		echo json_encode($rtn);
		die();
	}

	# dados da clinica
		$clinica = $logo = '';
		$sql->consult($_p."clinica","*","");
		$clinica=mysqli_fetch_object($sql->mysqry);
		if(!empty($clinica->cn_logo)) $logo=$_cloudinaryURL.'c_thumb,w_600/'.$clinica->cn_logo;
		$title=utf8_encode($clinica->clinica_nome)." | Info Dental";
		$endereco = utf8_encode($clinica->endereco);

	# dados evolucao
		$evolucao=$paciente=$anamnese=$assinatura='';
		if(isset($_GET['id_evolucao']) and !empty($_GET['id_evolucao'])) {

			// id_tipo=1 -> anamnese
			$where="where md5(id) = '".addslashes($_GET['id_evolucao'])."' and id_tipo=1 and lixo=0";
			$sql->consult($_p."pacientes_evolucoes","*",$where);

			if($sql->rows) {
				$evolucao=mysqli_fetch_object($sql->mysqry);

				$anamneseFinalizada = $evolucao->enviarLinkFinalizado=="0000-00-00 00:00:00" ? 0 : 1;
				
				$title.=" | Anamnese";

				$sql->consult($_p."parametros_anamnese","*","where id=$evolucao->id_anamnese");
				if($sql->rows) $anamnese=mysqli_fetch_object($sql->mysqry);
				

				$sql->consult($_p."colaboradores","id,nome","where id=$evolucao->id_usuario");
				if($sql->rows) {
					$solicitante=mysqli_fetch_object($sql->mysqry);
				}

				$sql->consult($_p."pacientes","*","where id=$evolucao->id_paciente");
				if($sql->rows) {
					$paciente=mysqli_fetch_object($sql->mysqry);
					if($paciente->data_nascimento !="0000-00-00"){
						$idade=idade($paciente->data_nascimento);	
					} else{
						$idade = "";
					}
				}

				$_anamnesePerguntas=array();
				$sql->consult($_p."pacientes_evolucoes_anamnese","*","where id_evolucao=$evolucao->id and lixo=0");
				if($sql->rows) {
					while($x=mysqli_fetch_object($sql->mysqry)) {
						$_anamnesePerguntas[]=$x;
					}
				}

				if($anamneseFinalizada==1) {
					$sql->consult($_p."pacientes_evolucoes_assinaturas","*","where id_evolucao=$evolucao->id and lixo=0");
					if($sql->rows) {
						$assinatura=mysqli_fetch_object($sql->mysqry);
					}
				}
			}

		}

	# autenticacao
		$auth=false;
		if(isset($_SESSION['infod_cpf']) and isset($_SESSION['infod_dn'])) {
		
			if($_SESSION['infod_cpf']==md5($paciente->cpf) and $_SESSION['infod_dn']==strtotime($paciente->data_nascimento)) {
				$auth=true;
			}
		}

	include_once("includes/assinatura-header.php");

?>
<!doctype html>
<html xmlns="http://www.w3.org/1999/xhtml"
      xmlns:og="http://ogp.me/ns#"
      xmlns:fb="http://www.facebook.com/2008/fbml">

	<head>
		<meta charset="utf-8">
		<title><?php echo $title;?></title>

		<base href="//<?php echo $_SERVER['HTTP_HOST'];?>/evolucoes/" />
		<link rel="stylesheet" type="text/css" href="css/evolucoes.css" />
		<link rel="stylesheet" type="text/css" href="../css/apps.css" />
		<link rel="stylesheet" type="text/css" href="../css/annamnese.css" />
		<script src="https://code.iconify.design/iconify-icon/1.0.7/iconify-icon.min.js"></script>
		<script defer src="https://code.iconify.design/1/1.0.3/iconify.min.js"></script>
		<script src="../js/jquery.js"></script>
	</head>
	
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
	<script type="text/javascript" src="../js/jquery.sweetalert.js"></script>
	<script type="text/javascript" src="../js/jquery.inputmask.js"></script>
	<script defer src="https://code.iconify.design/1/1.0.3/iconify.min.js"></script>
	<style type="text/css">
		.erro {color:#cc3300}
	</style>
	<body>	

		<div class="print-header" style="padding-top: 20px;">
			<?php
			if(!empty($logo)) {
			} else {
			?>
			<img src="../img/logo-info.svg"  class="print-header__logo" style="width: auto;height: 25px;" />
			<?php
			}
			?>
		</div>

		<?php

		// Se nao encontrou a evolucao
		if(empty($evolucao) or empty($anamnese)) {

			?>
			<table class="print-table">

				<thead><tr><td><div class="print-table-header">&nbsp;</div></td></tr></thead>
				<tbody>
					<tr>
						<td>
							<center>Anamnese não encontrada!</center>
						</td>
					</tr>
				</tbody>
			</table>
			<?php
		} 

		// Se encontrou a evolucao
		else {

			// Se nao estiver autenticado
			if($auth===false) {
				?>
				<script type="text/javascript">
					var id_evolucao = '<?php echo md5($evolucao->id);?>'

					$(function(){

						$('.js-cpf').inputmask("999.999.999-99");
						$('.js-dn').inputmask("99/99/9999");

						$('.js-auth').click(function(){
							let cpf = $('.js-cpf').val();
							let dn = $('.js-dn').val();

							let erro='';

							if(cpf.length==0) erro='Complete o campo de CPF';
							else if(dn.length==0) erro='Complete o campo de Data de Nascimento';

							if(erro.length>0) {
								swal({title: "Erro!", text: erro, html:true, type:"error", confirmButtonColor: "#424242"});
							} else {
							
								let obj = $(this);
								let objHTMLAntigo = $(this).html();

								if(obj.attr('data-loading')==0) {

									
									obj.html(`<span class="iconify" data-icon="eos-icons:loading"></span> Autenticando...`);
									obj.attr('data-loading',0);

									
									let data = `ajax=auth&cpf=${cpf}&dn=${dn}&id_evolucao=${id_evolucao}`;
									$.ajax({
										type:"POST",
										data:data,
										success:function(rtn){

											if(rtn.success) {
												document.location.reload();
											} else {

												if(rtn.error) erro=rtn.error;
												else erro='Algum erro ocorreu durante a autenticação. Tente novamente!';

												swal({title: "Erro!", text: erro, html:true, type:"error", confirmButtonColor: "#424242"});

												obj.html(objHTMLAntigo);
												obj.attr('data-loading',0);
											}
										}
									});
								}
							}
						})
					})
				</script>


				<div style="text-align: center;margin-top:200px;">
					<form>
						<dl>
							<dt>CPF</dt>
							<dd><input type="text" class="js-cpf" /></dd>
						</dl>
						<dl>
							<dt>Data Nascimento</dt>
							<dd><input type="text" class="js-dn" /></dd>
						</dl>
						<button type="button" class="button js-auth" data-loading="0">Autenticar</button>
					</form>
				</div>
				<?php
			} 

			// Se estiver autenticado
			else {
				?>
				<table class="print-table">
					<thead><tr><td><div class="print-table-header">&nbsp;</div></td></tr></thead>
					<tbody>
						<tr>
							<td>
								<section class="print-content">

									<header class="titulo1">
										<h1>Ficha do Paciente</h1>
										<p><?php echo date('d/m/Y',strtotime($evolucao->data));?></p>
									</header>

									<div class="ficha">
										<table border="0">
											<tr>
												<td colspan="3"><strong><?php echo utf8_encode($paciente->nome);?></strong></td>
											</tr>
											<tr>
												<td><?php echo $idade>1?"$idade anos":"$idade";?></td>
												<td><?php echo $paciente->sexo=="M"?"Masculino":$paciente->sexo=="F"?"Feminino":'';?></td>
												<td style="text-align:right;"><span class="iconify" data-icon="bxs:phone" data-inline="true"></span> <?php echo maskTelefone($paciente->telefone1);?></td>
											</tr>
										</table>
									</div>

									<header class="titulo2">
										<span>
											<h1>Formulário da Anamnese</h1>
											<h2><?php echo utf8_encode($anamnese->titulo);?></h2>
										</span>
									</header>
									<?php

									// Anamnese nao finalizada
									if($anamneseFinalizada==0) {
										?>
										<script type="text/javascript">
											var id_evolucao = '<?php echo md5($evolucao->id);?>'

											const validacaoErro = (elemento,erro) => {
												swal({title: "Erro!", text: erro, html:true, type:"error", confirmButtonColor: "#424242"},function(){
													 $([document.documentElement, document.body]).animate({
												        scrollTop: elemento.offset().top-200
												    }, 100);
												});
											}

											const anamneseFinalizar = () => {

												let obj = $('.js-salvarEAssinar');
												let objHTMLAntigo = obj.html();

												if(obj.attr('data-loading')==0) {

													obj.attr('data-loading',1);
													obj.html(`<span class="iconify" data-icon="eos-icons:loading"></span> Processando...`);

													let data = `ajax=finalizar&id_evolucao=${id_evolucao}`;

													$.ajax({
														type:"POST",
														data:data,
														success:function(rtn) {
															if(rtn.success) {
																document.location.reload();

															} else {
																if(rtn.error) erro=rtn.error;
																else erro='Algum erro ocorreu durante a autenticação. Tente novamente!';

																swal({title: "Erro!", text: erro, html:true, type:"error", confirmButtonColor: "#424242"});

																obj.html(objHTMLAntigo);
																obj.attr('data-loading',0);
															}
														}
													})
												}
											}

											$(function(){

												$('.js-resposta').change(function(){
													let id_reposta = $(this).attr('data-id_resposta');
													let tipo = $(this).attr('data-tipo');
													let val = $(this).val();

													let data = `ajax=persistir&id_resposta=${id_reposta}&tipo=${tipo}&val=${val}&id_evolucao=${id_evolucao}`

													$.ajax({
														type:"POST",
														data:data,
														success:function(rtn) {

														}
													})
												});

												$('.js-salvarEAssinar').click(function(){


													// realiza validacao
													let erro='';
													let cont=0;
													$('.js-anamnese-campo').each(function(index,el){

														if(erro.length==0) {
															let elem = $(el);
															let tipo = elem.attr('data-tipo');
															let id_pergunta = elem.attr('data-id_pergunta');
															let obg = eval(elem.attr('data-obg'));
															console.log(obg);

															if(obg==1) {
																// Se tipo = texto
																if(tipo=="texto") {
																	if(elem.find('textarea').val().length==0) {
																		erro='Preencha o campo: <b>'+$(`.js-pergunta-${id_pergunta}`).html()+'</b>';
																		$(`.js-pergunta-${id_pergunta}`).addClass('erro');
																		validacaoErro($(`.js-pergunta-${id_pergunta}`),erro);
																	}
																}

																// Se tipo = nota
																else if(tipo=="nota") {
																	if($(`.js-nota-${id_pergunta}:checked`).length==0) {
																		erro='Assinale uma opção de <b>1 a 10</b> no campo: <b>'+$(`.js-pergunta-${id_pergunta}`).html()+'</b>';
																		$(`.js-pergunta-${id_pergunta}`).addClass('erro');
																		validacaoErro($(`.js-pergunta-${id_pergunta}`),erro);
																	}
																}

																// Se tipo = simnao ou simnaotexto
																else if(tipo=="simnao" || tipo=="simnaotexto") {
																	if($(`.js-simnao-${id_pergunta}:checked`).length==0) {
																		erro='Assinale <b>SIM</b> ou <b>NÃO</b> no campo: <b>'+$(`.js-pergunta-${id_pergunta}`).html()+'</b>';
																		$(`.js-pergunta-${id_pergunta}`).addClass('erro');
																		validacaoErro($(`.js-pergunta-${id_pergunta}`),erro);
																	}
																}
															}

															cont++;
															if(cont==$('.js-anamnese-campo').length) {
																anamneseFinalizar();
															}
														}
													});

													$('.js-td').click(function(){
														let id_pergunta = $(this).attr('data-id_pergunta');
														$('.js-pergunta-'+id_pergunta).removeClass('erro');
													});
												});
											})
										</script>

										<form method="post">

											<div class="box">
												<table>
													<?php
													$evolucaoProntoParaAssinatura=false;
													foreach($_anamnesePerguntas as $p) {
														$pergunta=json_decode($p->json_pergunta);
													?>
													<tr>
														<td class="js-td" data-id_pergunta="<?php echo $pergunta->id;?>">
															<p><strong class="js-pergunta-<?php echo $pergunta->id;?>"><?php echo utf8_encode($p->pergunta);?></strong></p>
															<p>
																<dl>
																	<dd class="js-anamnese-campo" data-obg="<?php echo $pergunta->obrigatorio;?>" data-tipo="<?php echo $pergunta->tipo;?>" data-id_pergunta="<?php echo $pergunta->id;?>">
																<?php  
																if($pergunta->tipo=="simnao") { 
																	?>
																	<label>
																		<input type="radio" name="resposta_<?php echo $p->id;?>" value="SIM" class="js-resposta js-simnao-<?php echo $pergunta->id;?>" data-tipo="simnao_texto" data-id_resposta="<?php echo $p->id;?>"<?php echo $p->resposta=="SIM"?" checked":"";?> /> Sim
																	</label>
																	<label>
																		<input type="radio" name="resposta_<?php echo $p->id;?>" value="NAO" class="js-resposta js-simnao-<?php echo $pergunta->id;?>" data-tipo="simnao_texto" data-id_resposta="<?php echo $p->id;?>"<?php echo $p->resposta=="NAO"?" checked":"";?> /> Não
																	</label>

																	<?php

																}
																else if($pergunta->tipo=="simnaotexto") {
																	?>
																		<div>
																			<label><input type="radio" name="resposta_<?php echo $p->id;?>" value="SIM" class="js-resposta js-simnao-<?php echo $pergunta->id;?>" data-tipo="simnao" data-id_resposta="<?php echo $p->id;?>"<?php echo $p->resposta=="SIM"?" checked":"";?> /> Sim</label>
																			<label><input type="radio" name="resposta_<?php echo $p->id;?>" value="NAO" class="js-resposta js-simnao-<?php echo $pergunta->id;?>" data-tipo="simnao" data-id_resposta="<?php echo $p->id;?>"<?php echo $p->resposta=="NAO"?" checked":"";?> /> Não</label>
																		</div>
																		<div>
																			<textarea name="resposta_<?php echo $p->id;?>" class="js-resposta js-simnaotexto-<?php echo $pergunta->id;?>" data-tipo="texto" data-id_resposta="<?php echo $p->id;?>"><?php echo utf8_encode($p->resposta_texto);?></textarea>
																		</div>	
																	<?php
																} else if($pergunta->tipo=="nota") {
																	for($i=1;$i<=10;$i++) {
																	?>
																	<label>
																		<input type="radio" name="resposta_<?php echo $p->id;?>" value="<?php echo $i;?>" class="js-resposta js-nota-<?php echo $pergunta->id;?>" data-tipo="nota" data-id_resposta="<?php echo $p->id;?>"<?php echo $p->resposta==$i?" checked":"";?> /> <?php echo $i;?>
																	</label>
																	<?php
																	}
																} else {
																	?>
																	<textarea name="resposta_<?php echo $p->id;?>" class="js-resposta" data-tipo="texto" data-id_resposta="<?php echo $p->id;?>"><?php echo utf8_encode($p->resposta_texto);?></textarea>
																	<?php
																}
																?>
																	</dd>
																</dl>	
															</p>
														</td>
													</tr>
													<?php
													}
													?>
												</table>
											</div>

											<div class="">
												<center><button type="button" class="button button_main js-salvarEAssinar" data-loading="0">Salvar e Assinar</button></center>
											</div>
												
										</form>
										<?php
									}
									// Anamnese finalizada
									else {

										if(is_object($assinatura)) {
											$pdfAnamnese = $_scalewayS3endpoint."/".$infoConta->instancia."/arqs/pacientes/anamneses/assinados/".sha1($evolucao->id).".pdf";
										} else {
											$pdfAnamnese = $_scalewayS3endpoint."/".$infoConta->instancia."/arqs/pacientes/anamneses/".sha1($evolucao->id).".pdf";
										}

										?>
										<object data='<?php echo $pdfAnamnese;?>#view=fit&toolbar=0' style="width:100%;height:700px;" toolbar="0">			    
										    <p><a href="<?php echo $pdfAnamnese;?>" class="button"><i class="iconify" data-icon="fluent:document-24-regular"></i><span>Baixar documento</span></a></p>
										</object>
										<?php
									}
									?>
								</section>

								<?php
								if($anamneseFinalizada==1) {
									require_once("includes/assinatura-canvas.php");
								}
								?>
							</td>
						</tr>

						<tr>
							<td style="padding-top:40px;">

								<div class="print-footer">
									<p><span class="iconify" data-icon="bx:bxs-phone" data-inline="true"></span><span><?php echo maskTelefone($clinica->telefone);?></span><span class="iconify" data-icon="ri:whatsapp-fill" data-inline="true"></span><span><?php echo maskTelefone($clinica->whatsapp);?></span></p>
									<p><?php echo $endereco;?></p>
									<p>
										<span><i class="iconify" data-icon="ph-globe-simple"></i> <a href="https://<?php echo $clinica->site;?>"><?php echo $clinica->site;?></a></span>
										<span><i class="iconify" data-icon="ph-instagram-logo"></i> <a href="https://instagram.com/<?php echo str_replace("@","",$clinica->instagram);?>"><?php echo $clinica->instagram;?></a></span>
									</p>
								</div>
							</td>
						</tr>


					</tbody>
				</table>
				<?php
			}	
		}
		?>


	</body>
</html>