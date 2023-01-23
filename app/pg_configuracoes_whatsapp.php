<?php
	if(isset($_POST['ajax'])) {

		require_once("lib/conf.php");
		require_once("usuarios/checa.php");

		$rtn  = array();

		if($_POST['ajax']=="whatsappStatus") {

			$rtn=array('success'=>true,
						'connected'=>is_object($_wts)?1:0,
						'connected_date'=>is_object($_wts)?date('d/m/Y H:i',strtotime($_wts->data)):'');

		} else if ($_POST['ajax']=="pub") {

			$tipo = '';
			if(isset($_POST['id_tipo']) and is_numeric($_POST['id_tipo'])) {
				$sql->consult($_p."whatsapp_mensagens_tipos","*","where id=".$_POST['id_tipo']);
				if($sql->rows) {
					$tipo=mysqli_fetch_object($sql->mysqry);
				}
			}

			$checked = (isset($_POST['checked']) and $_POST['checked']==1)?1:0;


			if(empty($tipo)) $erro='Tipo de mensagem não identificada!';

			if(empty($erro)) {
				$vSQL="pub='".$checked."'";
				$vWHERE="where id=$tipo->id";

				$sql->update($_p."whatsapp_mensagens_tipos",$vSQL,$vWHERE);

				$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='update',vsql='".addslashes($vSQL)."',vwhere='".addslashes($vWHERE)."',tabela='".$_p."whatsapp_mensagens_tipos',id_reg='".$tipo->id."'");

			}


			if(empty($erro)) {
				$rtn=array('success'=>true);
			} else {
				$rtn=array('success'=>false,'error'=>$erro);
			}
		} else if ($_POST['ajax']=="geolocalizacao") {

			$tipo = '';
			if(isset($_POST['id_tipo']) and is_numeric($_POST['id_tipo'])) {
				$sql->consult($_p."whatsapp_mensagens_tipos","*","where id=".$_POST['id_tipo']);
				if($sql->rows) {
					$tipo=mysqli_fetch_object($sql->mysqry);
				}
			}

			$checked = (isset($_POST['checked']) and $_POST['checked']==1)?1:0;


			if(empty($tipo)) $erro='Tipo de mensagem não identificada!';

			if(empty($erro)) {
				$vSQL="geolocalizacao='".$checked."'";
				$vWHERE="where id=$tipo->id";

				$sql->update($_p."whatsapp_mensagens_tipos",$vSQL,$vWHERE);

				$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='update',vsql='".addslashes($vSQL)."',vwhere='".addslashes($vWHERE)."',tabela='".$_p."whatsapp_mensagens_tipos',id_reg='".$tipo->id."'");

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

	$_table = $_p."whatsapp_mensagens_tipos";

	$sql = new Mysql(true);

	$campos = explode(",","pub,texto");

	$values=array();
	foreach($campos as $v) $values[$v]='';
	$values['tipo']='PJ';

	$cnt='';
	$sql->consult($_table,"*","where lixo=0 limit 1");
	if($sql->rows) {
		$cnt=mysqli_fetch_object($sql->mysqry);
		$values=$adm->values($campos,$cnt);
	}


	$_tipos=array();
	$sql->consult($_table,"*","where lixo=0 order by id asc");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_tipos[$x->id]=$x;
	}

	$sql->consult($_p."whatsapp_respostasdeconfirmacao","*","");
	if($sql->rows==0) {
		$sql->add($_p."whatsapp_respostasdeconfirmacao","pubSim=0,pubNao=0,pubNaoIdentificado=0");
	}

	$sql->consult($_p."whatsapp_respostasdeconfirmacao","*","where id=1");
	$wrc=mysqli_fetch_object($sql->mysqry);



	if(isset($_POST['acao'])) {
		

		$vSQL="";

		foreach($_tipos as $t) {

			/*$vSQL="pub='".((isset($_POST['pub-'.$t->id]) && $_POST['pub-'.$t->id]==1)?1:0)."',
					geolocalizacao='".((isset($_POST['geolocalizacao-'.$t->id]) && $_POST['geolocalizacao-'.$t->id]==1)?1:0)."',
					texto='".($_POST['texto-'.$t->id])."'";*/

			$vSQL="pub='".((isset($_POST['pub-'.$t->id]) && $_POST['pub-'.$t->id]==1)?1:0)."',
					geolocalizacao='".((isset($_POST['geolocalizacao-'.$t->id]) && $_POST['geolocalizacao-'.$t->id]==1)?1:0)."'";

			$vWHERE="where id='".$t->id."'";
			$sql->update($_table,$vSQL,$vWHERE);
			$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='update',vsql='".addslashes($vSQL)."',vwhere='".addslashes($vWHERE)."',tabela='".$_table."',id_reg='".$cnt->id."'");

		}


		$vSQL="pubSim='".((isset($_POST['pubSim']) and $_POST['pubSim']==1)?1:0)."',
				pubNao='".((isset($_POST['pubNao']) and $_POST['pubNao']==1)?1:0)."',
				pubNaoIdentificado='".((isset($_POST['pubNaoIdentificado']) and $_POST['pubNaoIdentificado']==1)?1:0)."',
				msgSim='".addslashes($_POST['msgSim'])."',
				msgNao='".addslashes($_POST['msgNao'])."',
				msgNaoIdentificado='".addslashes($_POST['msgNaoIdentificado'])."'";
			/*
			2022-06-14 -> removido por Luciano porque nao tera mais whatsapp no gestao de tempo
				,
				pubInteligenciaSim='".((isset($_POST['pubInteligenciaSim']) and $_POST['pubInteligenciaSim']==1)?1:0)."',
				pubInteligenciaNao='".((isset($_POST['pubInteligenciaNao']) and $_POST['pubInteligenciaNao']==1)?1:0)."',
				pubInteligenciaNaoIdentificado='".((isset($_POST['pubInteligenciaNaoIdentificado']) and $_POST['pubInteligenciaNaoIdentificado']==1)?1:0)."',
				msgInteligenciaSim='".addslashes($_POST['msgInteligenciaSim'])."',
				msgInteligenciaNao='".addslashes($_POST['msgInteligenciaNao'])."',
				msgInteligenciaNaoIdentificado='".addslashes($_POST['msgInteligenciaNaoIdentificado'])."'
				";
			*/

		$sql->update($_p."whatsapp_respostasdeconfirmacao",$vSQL,"where id=$wrc->id");

		

		$jsc->go($_page);
		die();
	}
	
	function substituiTags($texto) {

		$texto = str_replace("*[nome]*", "<b>João</b>", $texto);
		$texto = str_replace("*[clinica_nome]*", "<b>Clínica Sorriso Feliz</b>", $texto);
		$texto = str_replace("*[agenda_data]*", "<b>".date('d/m/Y')."</b>", $texto);
		$texto = str_replace("*[agenda_hora]*", "<b>".date('H:i',strtotime(date('Y-m-d H:i')." + 2 hour"))."</b>", $texto);
		$texto = str_replace("*[agenda_antiga_data]*", "<b>".date('d/m/Y',strtotime(date('Y-m-d')." + 2 day"))."</b>", $texto);
		$texto = str_replace("*[agenda_antiga_hora]*", "<b>09:00</b>", $texto);
		$texto = str_replace("*[duracao]*", "<b>60min</b>", $texto);
		$texto = str_replace("[clinica_endereco]", "<b>Rua das Esmeraldas, nº3444 Bairro Ouro Fino, Sala 01, São Paulo-SP</b>", $texto);
		$texto = str_replace("*Confirmar*", "<b>Confirmar</b>", $texto);
		$texto = str_replace("*Desmarcar*", "<b>Desmarcar</b>", $texto);
		$texto = str_replace("*CONFIRMADO*", "<b>CONFIRMADO</b>", $texto);
		$texto = str_replace("*DESMARCADO*", "<b>DESMARCADO</b>", $texto);

		return nl2br($texto);

	}

?>


	<header class="header">
		<div class="header__content content">

			<div class="header__inner1">
				<section class="header-title">
					<h1>Configurações</h1>
				</section>
				<?php
				require_once("includes/menus/menuWhatsapp.php");
				?>
			</div>
		</div>
	</header>

	<script type="text/javascript">
		const whatsappStatus = () => {
			$.ajax({
				type:"POST",
				data:`ajax=whatsappStatus`,
				success:function(rtn) {
					if(rtn.success) {
						if(rtn.connected==1) {
							$('.js-infozap-conectado').show();
							$('.js-infozap-desconectado').hide();
						} else {
							$('.js-infozap-conectado').hide();
							$('.js-infozap-desconectado').show();
						}

					}
				}
			}).done(function(){
				setTimeout(whatsappStatus,1000*10)
			})
		}

		$(function(){
			whatsappStatus();

			$('.js-pub').click(function(){
				let id_tipo = $(this).attr('data-id_tipo');
				let data = `ajax=pub&id_tipo=${id_tipo}&checked=${($(this).prop('checked')?1:0)}`
				//alert(data);
				$.ajax({
					type:"POST",
					data:data,
					success:function(rtn) {

					}
				})
			});

			$('.js-geolocalizacao').click(function(){
				let id_tipo = $(this).attr('data-id_tipo');
				let data = `ajax=geolocalizacao&id_tipo=${id_tipo}&checked=${($(this).prop('checked')?1:0)}`
				//alert(data);
				$.ajax({
					type:"POST",
					data:data,
					success:function(rtn) {

					}
				})
			})
		})
	</script>

	<main class="main">
		<div class="main__content content">

			<section class="filter">
				
				<div class="filter-group">
					<div class="filter-title">
						<h1>Configure as mensagens do Whatsapp</h1>
					</div>
				</div>
			</section>
 	
 	
			<section class="grid">

				<div class="box box-col">

					<?php
					require_once("includes/submenus/subConfiguracoesWhatsapp.php");
					?>

					<div class="box-col__inner1">
				
						<div class="infozap">
							
							<section class="infozap-status js-infozap-conectado" style="display:none">
								<aside style="color:var(--verde);">
									<i class="iconify" data-icon="fluent:plug-connected-24-regular"></i>
									<h1>Conectado</h1>
								</aside>
								<article>
									<h1>O Infozap está conectado.</h1>
								</article>
							</section>
							
							<section class="infozap-status js-infozap-desconectado">
								<aside style="color:var(--vermelho);">
									<i class="iconify" data-icon="fluent:plug-disconnected-24-regular"></i>
									<h1>Desconectado</h1>
								</aside>
								<article>
									<h1>O Infozap está desconectado no momento.</h1>
									<p>1. Adicione a extensão <a href="https://chrome.google.com/webstore/detail/infozap/nakbcclpnadhimbcmepfjalgiihokpki?hl=pt-br" target="_blank">Infozap</a> ao seu Google Chrome</p>
									<p>2. Conecte-se ao <a href="https://web.whatsapp.com" target="_blank">WhatsApp Web</a></p>
									<p>3. Ative a extensão Infozap informando seus dados de acesso</p>
								</article>
							</section>
							

							<fieldset>
								<legend>Confirmação de Agendamento</legend>

								 <div class="grid grid_3" style="margin-bottom:0;">
									<form method="post" class="form">
										<dl>
											<dd>
												<label><input type="checkbox" class="js-pub input-switch" data-id_tipo="1"<?php echo $_tipos[1]->pub?" checked":"";?> />Ativar</label></dd>
											</dd>
										</dl>
										<dl>
											<dd>Mensagem para confirmação dos agendamentos. A mesma pode ser realizada com 24 horas ou 48 horas de antecedência, a depender do tempo de criação do agendamento.</dd>
										</dl>
									</form>

									<div class="infozap-chat">
										<div class="infozap-chat-text infozap-chat-text--author">
											<article>
												<p class="infozap-chat-text__msg">
													<?php echo substituiTags($_tipos[1]->texto);?>
												</p>
												<p class="infozap-chat-text__date">11:59</p>
											</article>
											<footer>
												<a href="javascript:;">Confirmar</a>
												<a href="javascript:;">Desmarcar</a>
											</footer>
										</div>
										<div class="infozap-chat-text">
											<article>
												<p class="infozap-chat-text__msg">Confirmar</p>
												<p class="infozap-chat-text__date">12:00</p>
											</article>
										</div>

										<div class="infozap-chat-text infozap-chat-text--author">
											<article>
												<p class="infozap-chat-text__msg">
													<?php echo substituiTags($wrc->msgSim);?>
												</p>
												<p class="infozap-chat-text__date">12:01</p>
											</article>
										</div>
									</div>

									<div class="infozap-chat">
										<div class="infozap-chat-text infozap-chat-text--author">
											<article>
												<p class="infozap-chat-text__msg">
													<?php echo substituiTags($_tipos[1]->texto);?>
												</p>
												<p class="infozap-chat-text__date">11:59</p>
											</article>
											<footer>
												<a href="javascript:;">Confirmar</a>
												<a href="javascript:;">Desmarcar</a>
											</footer>
										</div>
										<div class="infozap-chat-text">
											<article>
												<p class="infozap-chat-text__msg">Desmarcar</p>
												<p class="infozap-chat-text__date">12:00</p>
											</article>
										</div>

										<div class="infozap-chat-text infozap-chat-text--author">
											<article>
												<p class="infozap-chat-text__msg">
													<?php echo substituiTags($wrc->msgNao);?>
												</p>
												<p class="infozap-chat-text__date">12:01</p>
											</article>
										</div>
									</div>
								</div>
							</fieldset>

							<fieldset>
								<legend>LEMBRETE de Agendamento</legend>

								 <div class="grid grid_3" style="margin-bottom:0;">
									<form method="post" class="form">
										<dl>
											<dd>
												<label><input type="checkbox" class="js-pub input-switch" data-id_tipo="2"<?php echo $_tipos[2]->pub?" checked":"";?> />Ativar</label></dd>
											</dd>
											<dd>
												<label><input type="checkbox" class="js-geolocalizacao input-switch" data-id_tipo="2"<?php echo $_tipos[2]->geolocalizacao?" checked":"";?> /> Enviar geolocalização em seguida</label>
											</dd>
										</dl>

										<dl>
											<dd>Mensagem enviada com 3 horas de antecedencia para agendamentos com status CONFIRMADO.</dd>
										</dl>
									</form>

									<div class="infozap-chat">
										<div class="infozap-chat-text infozap-chat-text--author">
											<article>
												<p class="infozap-chat-text__msg">
													<?php echo substituiTags($_tipos[2]->texto);?>
												</p>
												<p class="infozap-chat-text__date">11:59</p>
											</article>
										
										</div>
									</div>
								</div>
							</fieldset>

							<fieldset>
								<legend>ALTERAÇÃO de Agendamento</legend>

								 <div class="grid grid_3" style="margin-bottom:0;">
									<form method="post" class="form">
										<dl>
											<dd>
												<label><input type="checkbox"  class="js-pub input-switch" data-id_tipo="5"<?php echo $_tipos[5]->pub?" checked":"";?> />Ativar envio para paciente</label></dd>
											</dd>
										</dl>
										<dl>
											<dd>
												<label><input type="checkbox" class="js-pub input-switch" data-id_tipo="6"<?php echo $_tipos[6]->pub?" checked":"";?> />Ativar envio para profissional</label></dd>
											</dd>
										</dl>

										<dl>
											<dd>Mensagem enviada para paciente/dentista quando um agendamento é alterado.</dd>
										</dl>
									</form>
									<div class="infozap-chat">
										<div class="infozap-chat-text infozap-chat-text--author">
											<article>
												<p class="infozap-chat-text__msg">
													<?php echo substituiTags($_tipos[5]->texto);?>
												</p>
												<p class="infozap-chat-text__date">11:59</p>
											</article>
										
										</div>
										
									</div>

									<div class="infozap-chat">
										<div class="infozap-chat-text infozap-chat-text--author">
											<article>
												<p class="infozap-chat-text__msg">
													<?php echo substituiTags($_tipos[6]->texto);?>
												</p>
												<p class="infozap-chat-text__date">11:59</p>
											</article>
										
										</div>
										
									</div>
									
								</div>
							</fieldset>

							<fieldset>
								<legend>CANCELAMENTO de Agendamento</legend>

								 <div class="grid grid_3" style="margin-bottom:0;">
									<form method="post" class="form">
										<dl>
											<dd>
												<label><input type="checkbox" class="js-pub input-switch" data-id_tipo="3"<?php echo $_tipos[3]->pub?" checked":"";?> />Ativar envio para paciente</label></dd>
											</dd>
										</dl>
										<dl>
											<dd>
												<label><input type="checkbox" class="js-pub input-switch" data-id_tipo="8"<?php echo $_tipos[8]->pub?" checked":"";?> />Ativar envio para profissional</label></dd>
											</dd>
										</dl>

										<dl>
											<dd>Mensagem enviada para paciente/dentista quando um agendamento é cancelado.</dd>
										</dl>
									</form>
									<div class="infozap-chat">
										<div class="infozap-chat-text infozap-chat-text--author">
											<article>
												<p class="infozap-chat-text__msg">
													<?php echo substituiTags($_tipos[3]->texto);?>
												</p>
												<p class="infozap-chat-text__date">11:59</p>
											</article>
										</div>
									</div>


									<div class="infozap-chat">
										<div class="infozap-chat-text infozap-chat-text--author">
											<article>
												<p class="infozap-chat-text__msg">
													<?php echo substituiTags($_tipos[8]->texto);?>
												</p>
												<p class="infozap-chat-text__date">11:59</p>
											</article>
										</div>
									</div>
									
								</div>
							</fieldset>



							<fieldset>
								<legend>Envio de PDF de Prontuários</legend>

								 <div class="grid grid_3" style="margin-bottom:0;">
									<form method="post" class="form">
										<dl>
											<dd>
												<label><input type="checkbox" class="js-pub input-switch" data-id_tipo="9"<?php echo $_tipos[9]->pub?" checked":"";?> />Ativar</label></dd>
											</dd>
										</dl>

										<dl>
											<dd>Mensagem enviada para paciente quando é criado um novo prontuário.</dd>
										</dl>
									</form>
									<div class="infozap-chat">
										<div class="infozap-chat-text infozap-chat-text--author">
											<article>
												<p class="infozap-chat-text__msg">
													<?php echo substituiTags($_tipos[9]->texto);?>
												</p>
												<p class="infozap-chat-text__date">11:59</p>
											</article>
										</div>
									</div>


								
								</div>
							</fieldset>

						</div>

						<?php

						/*?>

						<form method="post" class="form formulario-validacao">
							<input type="hidden" name="acao" value="wlib" />
							
							
							<fieldset>
								<legend>Tipos de Mensagens</legend>

								<?php
								foreach($_tipos as $x) {
								?>
								<dl>
									<dt>
										<label><input type="checkbox" class="input-switch" name="pub-<?php echo $x->id;?>" value="1"<?php echo $x->pub==1?" checked":"";?> /> <?php echo ($x->titulo);?></label>
									</dt>
									<dd>
										<textarea name="texto-<?php echo $x->id;?>" style="height:200px;" disabled><?php echo ($x->texto);?></textarea>
									</dd>
									<?php
									// Lembrete de Agendamento inclui envio de geolocalizacao

									if($x->id==2) {
									?>
									<dd>
										<label><input type="checkbox" name="geolocalizacao-<?php echo $x->id;?>" value="1"<?php echo $x->geolocalizacao==1?" checked":"";?> /> Enviar geolocalização em seguida</label>
									</dd>
									<?php
									}
									?>
								</dl>
								<?php
								}
								?>
									
							
							</fieldset>

							<fieldset>
								<legend>Respostas para Confirmação</legend>

								<dl>
									<dt>
										<label><input type="checkbox" class="input-switch" name="pubSim" value="1"<?php echo $wrc->pubSim==1?" checked":"";?> /> Confirmação de Agendamento (1)</label>
									</dt>
									<dd>
										<textarea name="msgSim" style="height:120px;"><?php echo ($wrc->msgSim);?></textarea>
									</dd>
								</dl>

								<dl>
									<dt>
										<label><input type="checkbox" class="input-switch" name="pubNao" value="1"<?php echo $wrc->pubNao==1?" checked":"";?> /> Não confirmação de Agendamento (2)</label>
									</dt>
									<dd>
										<textarea name="msgNao" style="height:120px;"><?php echo ($wrc->msgNao);?></textarea>
									</dd>
								</dl>

								<dl>
									<dt>
										<label><input type="checkbox" class="input-switch" name="pubNaoIdentificado" value="1"<?php echo $wrc->pubNaoIdentificado==1?" checked":"";?> /> Resposta não identificada</label>
									</dt>
									<dd>
										<textarea name="msgNaoIdentificado" style="height:120px;"><?php echo ($wrc->msgNaoIdentificado);?></textarea>
									</dd>
								</dl>

							</fieldset>

						

							<fieldset class="box-registros">
								<legend>Palavra Chaves</legend>

								<table class="table">
									<tr>
										<th style="width:200px;">Palavra Chave</th>
										<th>Descrição</th>
									</tr>
									<tr>
										<td>[nome]</td>
										<td>Nome do paciente</td>
									</tr>
									<tr>
										<td>[agenda_data]</td>
										<td>Data do agendamento (dd/mm/AAAA)</td>
									</tr>
									<tr>
										<td>[agenda_hora]</td>
										<td>Horário do agendamento (HH:mm)</td>
									</tr>
									<tr>
										<td>[agenda_antiga_data]</td>
										<td>Data do agendamento que foi alterado (dd/mm/AAAA)</td>
									</tr>
									<tr>
										<td>[agenda_antiga_hora]</td>
										<td>Horário do agendamento que foi alterado (HH:mm)</td>
									</tr>
									<tr>
										<td>[consultorio]</td>
										<td>Consultório do agendamento</td>
									</tr>
									<tr>
										<td>[profissionais]</td>
										<td>Profissionais do agendamento</td>
									</tr>
									<tr>
										<td>[duracao]</td>
										<td>Duração do agendamento</td>
									</tr>
									<tr>
										<td>[tempo_sem_atendimento]</td>
										<td>Tempo de cadastro em meses</td>
									</tr>
									<tr>
										<td>[clinica_nome]</td>
										<td>Nome da Clínica</td>
									</tr>
									<tr>
										<td>[clinica_endereco]</td>
										<td>Endereço da Clínica</td>
									</tr>
								</table>
							</fieldset>



						</form>
*/?>
					</div>
					
				</div>

			</section>
		
		</div>
	</main>

<?php 
include "includes/footer.php";
?>	