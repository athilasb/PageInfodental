<?php
	if(isset($_POST['ajax'])) {

		require_once("lib/conf.php");
		require_once("usuarios/checa.php");

		$rtn  = array();

		if($_POST['ajax']=="whatsappStatus") {

			$rtn=array('success'=>true,
						'connected'=>is_object($_wts)?1:0,
						'connected_date'=>is_object($_wts)?date('d/m/Y H:i',strtotime($_wts->data)):'');

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
	$sql->consult($_table,"*","limit 1");
	if($sql->rows) {
		$cnt=mysqli_fetch_object($sql->mysqry);
		$values=$adm->values($campos,$cnt);
	}


	$_tipos=array();
	$sql->consult($_table,"*","order by id asc");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_tipos[$x->id]=$x;
	}



	if(isset($_POST['acao'])) {
		

		$vSQL="";

		foreach($_tipos as $t) {

			$vSQL="pub='".((isset($_POST['pub-'.$t->id]) && $_POST['pub-'.$t->id]==1)?1:0)."',
					texto='".($_POST['texto-'.$t->id])."'";

			$vWHERE="where id='".$t->id."'";
			$sql->update($_table,$vSQL,$vWHERE);
			$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='update',vsql='".addslashes($vSQL)."',vwhere='".addslashes($vWHERE)."',tabela='".$_table."',id_reg='".$cnt->id."'");

		}

		

		$jsc->go($_page);
		die();
	}
	


?>


	<header class="header">
		<div class="header__content content">

			<div class="header__inner1">
				<section class="header-title">
					<h1>Configurações</h1>
				</section>
				<?php
				require_once("includes/menus/menuConfiguracoes.php");
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

							if($('.js-whatsappStatus').attr('data-connected')=="0") {
								$('.js-whatsappStatus').html(`<span class="iconify" data-icon="fluent:plug-connected-checkmark-20-filled" data-height="50" style="color:var(--verde);"></span>
									<br />Conectado às ${rtn.connected_date}`);
								$('.js-whatsappStatus').attr('data-connected',"1");
							}
						} else {
							if($('.js-whatsappStatus').attr('data-connected')=="1") {
								$('.js-whatsappStatus').html(`<span class="iconify" data-icon="fluent:plug-connected-add-20-regular" data-height="50" style="color:var(--vermelho);"></span><br />Desconectado`);
								$('.js-whatsappStatus').attr('data-connected',"0");
							}

						}

					}
				}
			}).done(function(){
				setTimeout(whatsappStatus,1000*10)
			})
		}

		$(function(){
			whatsappStatus();
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
				
						<section class="filter">
							<div class="filter-group"></div>
							<div class="filter-group">
								<div class="filter-form form">
									<dl>
										<dd><a href="javascript:;" class="button button_main js-submit"><i class="iconify" data-icon="fluent:checkmark-12-filled"></i> <span>Salvar</span></a></dd>
									</dl>
								</div>
							</div>							
						</section>

						<?php

						?>

						<form method="post" class="form formulario-validacao">
							<input type="hidden" name="acao" value="wlib" />
							
							<p style="margin-bottom: 40px;text-align: center;" class="js-whatsappStatus" data-connected="<?php echo is_object($_wts)?1:0;?>">
								<?php
								if(is_object($_wts)) {
								?>
								<span class="iconify" data-icon="fluent:plug-connected-checkmark-20-filled" data-height="50" style="color:var(--verde);"></span>
								<br />Conectado às <?php echo date('d/m/Y H:i', strtotime($_wts->data));?>
								<?php
								} else {
								?>
								<span class="iconify" data-icon="fluent:plug-connected-add-20-regular" data-height="50" style="color:var(--vermelho);"></span><br />Desconectado
								<?php
								}
								?>
							</p>
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
										<textarea name="texto-<?php echo $x->id;?>" style="height:120px;"><?php echo ($x->texto);?></textarea>
									</dd>
								</dl>
								<?php
								}
								?>
									
							
							</fieldset>



						</form>

					</div>
					
				</div>

			</section>
		
		</div>
	</main>

<?php 
include "includes/footer.php";
?>	