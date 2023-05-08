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
		} else if($_POST['ajax']=="persistirMensagem") {

			if(isset($_POST['mensagem']) and !empty($_POST['mensagem'])) {

				$sql = new Mysql(true);
				$sql->consult($_p."whatsapp_mensagens_tipos","*","where id=13");
				if($sql->rows) {
					$tipo=mysqli_fetch_object($sql->mysqry);
				}

				if(empty($tipo)) $erro='Tipo de mensagem não identificada!';

				if(empty($erro)) {
					$vSQL="texto='".addslashes($_POST['mensagem'])."'";
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
		}

		header("Content-type: application/json");
		echo json_encode($rtn);
		die();

	}

	include "includes/header.php";
	include "includes/nav.php";
	if($usr->tipo!="admin" and !in_array("whatsapp",$_usuariosPermissoes)) {
		$jsc->jAlert("Você não tem permissão para acessar esta área!","erro","document.location.href='dashboard.php'");
		die();
	}

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
		$texto = str_replace("*[paciente]*", "<b>João</b>", $texto);
		$texto = str_replace("*[clinica_nome]*", "<b>Clínica Sorriso Feliz</b>", $texto);
		$texto = str_replace("*[agenda_data]*", "<b>".date('d/m/Y')."</b>", $texto);
		$texto = str_replace("*[agenda_hora]*", "<b>".date('H:i',strtotime(date('Y-m-d H:i')." + 2 hour"))."</b>", $texto);
		$texto = str_replace("*[agenda_antiga_data]*", "<b>".date('d/m/Y',strtotime(date('Y-m-d')." + 2 day"))."</b>", $texto);
		$texto = str_replace("*[agenda_antiga_hora]*", "<b>09:00</b>", $texto);
		$texto = str_replace("*[duracao]*", "<b>60min</b>", $texto);
		$texto = str_replace("*[profissionais]*", "<b>Dr. Luciano</b>", $texto);
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

			$('.js-mensagem').keyup(function(){
				let mensagem = $(this).val();
				let texto = $(this).val();
				mensagem = mensagem.replace("*[nome]*","<b>João</b>");
				mensagem = mensagem.replace(/(?:\*)([^*]*)(?:\*)/gm,"<b>$1</b>");
				$('.js-msg').html(mensagem);

				let data = `ajax=persistirMensagem&mensagem=${texto}`
				$.ajax({
					type:"POST",
					data:data,
					success:function(rtn) {

					}
				})
			});
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

							<fieldset>
								<legend>ANIVERSARIANTE do dia</legend>

								<div class="grid grid_3" style="margin-bottom:0;">
									<form method="post" class="form">
										<dl>
											<dd>
												<label><input type="checkbox" class="js-pub input-switch" data-id_tipo="13"<?php echo $_tipos[13]->pub?" checked":"";?> />Ativar</label></dd>
											</dd>
										</dl>
										<dl>
											<dd>Mensagem enviada para paciente quando é seu aniversário.</dd>
										</dl>
									</form>

									<div>
										<dt></dt>
										<dl>
											<dd><textarea class="js-mensagem" rows="6"><?php echo $_tipos[13]->texto;?></textarea></dd>
										</dl>
									</div>

									<div class="infozap-chat">

										<div class="infozap-chat-text infozap-chat-text--author">
											<article>
												<p class="infozap-chat-text__msg js-msg">
													<?php echo substituiTags($_tipos[13]->texto);?>
												</p>
												<p class="infozap-chat-text__date">11:59</p>
											</article>
										</div>
									</div>


								
								</div>
							</fieldset>

						</div>
					</div>
					
				</div>

			</section>
		
		</div>
	</main>

<?php 
include "includes/footer.php";
?>	