<?php
	require_once("../lib/conf.php");
	require_once("../lib/classes.php");
	$sql = new Mysql();

	if(isset($_POST['ajax'])) {
		$rtn = [];

		if($_POST['ajax']=="persistir") {

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

		} else {
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
	$evolucao=$paciente='';
	if(isset($_GET['id_evolucao']) and !empty($_GET['id_evolucao'])) {
		$sql->consult($_p."pacientes_evolucoes","*","where md5(id) = '".addslashes($_GET['id_evolucao'])."' and lixo=0");
		if($sql->rows) {
			$evolucao=mysqli_fetch_object($sql->mysqry);
			
			if($evolucao->id_tipo==1) {
				$title.=" | Anamnese";
			} 

			$sql->consult($_p."colaboradores","id,nome","where id=$evolucao->id_usuario");
			if($sql->rows) {
				$solicitante=mysqli_fetch_object($sql->mysqry);
			}

			$sql->consult($_p."pacientes","*","where id=$evolucao->id_paciente");
			if($sql->rows) {
				$paciente=mysqli_fetch_object($sql->mysqry);
				if($paciente->data_nascimento !="0000-00-00"){
					$idade=idade($paciente->data_nascimento);	
				}else{
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
		}
	}

	require_once("../includes/assinaturas/assinatura-head.php");
?>
<!doctype html>
<html xmlns="http://www.w3.org/1999/xhtml"
      xmlns:og="http://ogp.me/ns#"
      xmlns:fb="http://www.facebook.com/2008/fbml">

	<head>
		<meta charset="utf-8">
		<title><?php echo $title;?></title>
		<link rel="stylesheet" type="text/css" href="../css/evolucoes.css" />
		<script defer src="https://code.iconify.design/1/1.0.3/iconify.min.js"></script>
		<script src="../js/jquery.js"></script>
	</head>

	<style>

input[type=datetime-local], input[type=text], input[type=number], input[type=tel], input[type=date], input[type=password], input[type=email], input[type=password], input[type=file],
select, textarea {-webkit-appearance:none; -moz-appearance:none; appearance:none; font-family:inherit; width:100%; line-height:20px; transition:all 150ms; border:1px solid var(--cinza3); background-color:var(--cinza1); height:38px; padding:0 8px; border-radius:8px;}

.sign {position:static; width:100%; max-width:800px; display:flex; flex-direction:column; margin:0 auto;}
.sign-header {height:80px; display:flex; align-items:center; justify-content:center; border-bottom:2px solid var(--cinza2);}
.sign-header img {width:40%; height:90%; object-fit:contain;}
.sign-article {flex:1; display:flex; flex-direction:column;}
.sign-article h1 {font-size:1.5em; text-align:center; margin-bottom:1.5rem;}
.sign-info {padding:1.5rem;}
.sign-doc {padding:1.5rem; background:var(--cinza1); border-radius:8px;}
.sign-doc object {width:100%; aspect-ratio:0.7; background:#fff; text-align:center;}
.sign-form {padding:1.5rem;}
.sign-form-status {text-align:center; margin-bottom:2rem;}
.sign-form-status h1 {display:inline-block; background:var(--cinza4); color:#fff; padding:.75rem 1.5rem; border-radius:100px; font-weight:normal;}
.sign-form p {text-align:center;}
.sign-form .button {padding:1.5rem 0;}
.sign-form-canva {display:flex; flex-direction:column; gap:.5rem;}
.sign-form-canva canvas {border:1px solid var(--cinza4); border-radius:8px;}
.sign-form-canva p {max-width:80%; margin:0 auto;}
@media screen and (max-width: 667px) {
	.sign-header {height:60px;}
	.sign-form input {font-size:1.5em; height:54px; padding:1rem;}
}

.form [class^=colunas] {display:grid; grid-template-columns:1fr 1fr; grid-gap:0 1rem;}
.form .colunas3 {grid-template-columns:repeat(3,1fr);}
.form .colunas4 {grid-template-columns:repeat(4,1fr);}
.form .colunas5 {grid-template-columns:repeat(5,1fr);}
.form .colunas6 {grid-template-columns:repeat(6,1fr);}
.form .colunas7 {grid-template-columns:repeat(7,1fr);}
.form .colunas8 {grid-template-columns:repeat(8,1fr);}
.form [class^=colunas] .dl2 {grid-column:span 2;}
.form [class^=colunas] .dl3 {grid-column:span 3;}
.form [class^=colunas] .dl4 {grid-column:span 4;}
.form label, .form-row label {display:flex; align-items:center; margin-right:1rem; min-height:38px;}
.form label input, .form-row label input {margin-right:.5rem;}
.aside .form label {margin-right:.5rem;}
.aside .form label input {margin-right:.25rem;}

	
.form label, .form-row label {display:flex; align-items:center; margin-right:1rem; min-height:38px;}
.form label input, .form-row label input {margin-right:.5rem;}
.aside .form label {margin-right:.5rem;}
.aside .form label input {margin-right:.25rem;}

.form .form-comp {gap:0;}
.form-comp *:not(span,a) {border-radius:0 8px 8px 0;} 
.form-comp_pos *:not(span,a) {border-radius:8px 0 0 8px;}
.form-comp span, .form-comp a {background:var(--cinza1); height:38px; display:flex; align-items:center; justify-content:center; padding:0 12px; border:1px solid var(--cinza3); color:var(--cinza4); margin:0 -1px; border-radius:8px 0 0 8px;}
.form-comp_pos span, .form-comp_pos a {border-radius:0 8px 8px 0;}
.form-comp a {background:#fff;}

.form-alert {position:absolute !important; z-index:99; top:0; right:0; color:red; font-size:14px;}

.form-image {border:1px dashed var(--cinza4); border-radius:var(--border-radius1); display:flex; align-items:center; justify-content:center; height:212px; background:#fff; margin-bottom:1rem;}
.form-image img {width:auto; height:auto; max-width:70%; max-height:70%;}

.form-row dl {display:flex; align-items: center; margin-bottom:1rem;}
.form-row dt {flex:0 0 170px;}
.form-row dd {display:flex; align-items:center; width:100%;}
.form-row dd > * {margin-right:1rem;}
.form-row dd > *:last-child {margin-right:0;}
@media screen and (max-width: 896px) {
	.form [class^=colunas] {display:flex; flex-direction: column; grid-gap:0;}
	.form dt:empty {display:none;}
	.form-row dl {flex-direction:column; align-items:flex-start;}
	.form-row dt {flex:1; margin-bottom:.375em;}	
}


	</style>
	
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
	<script defer type="text/javascript" src="../js/jquery.slick.js"></script>
	<script defer type="text/javascript" src="../js/jquery.datetimepicker.js"></script>
	<script defer type="text/javascript" src="../js/jquery.chosen.js"></script>
	<script defer type="text/javascript" src="../js/jquery.fancybox.js"></script>
	<script defer type="text/javascript" src="../js/jquery.inputmask.js"></script>
	<script defer type="text/javascript" src="../js/jquery.tablesorter.js"></script>
	<script defer type="text/javascript" src="../js/jquery.chart.js"></script>
	<script defer type="text/javascript" src="../js/jquery.chart-utils.js"></script>
	<script type="text/javascript" src="../js/jquery.sweetalert.js"></script>
	<script type="text/javascript" src="../js/jquery.validacao.js"></script>
	<script type="text/javascript" src="../js/jquery.funcoes.js"></script>
	<script defer src="https://code.iconify.design/1/1.0.3/iconify.min.js"></script>

	<body>

		<div class="print-header" style="padding-top: 20px;">
			<?php
			if(!empty($logo)) {
			?>
			<img src="<?php echo $logo;?>" class="print-header__logo" style="width: auto;height: 30px;" />
			<?php
			} else {
			?>
			<img src="../img/logo-info.svg"  class="print-header__logo" style="width: auto;height: 25px;" />
			<?php
			}
			?>
		</div>

		<script type="text/javascript">
			var id_evolucao = '<?php echo md5($evolucao->id);?>'
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
			})
		</script>

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
									<h2><?php echo utf8_encode($solicitante->nome);?></h2>
								</span>
							</header>

							<form method="post">

								<div class="box">
									<table>
										<?php
										foreach($_anamnesePerguntas as $p) {
											$pergunta=json_decode($p->json_pergunta);
										?>
										<tr>
											<td>
												<p><strong><?php echo utf8_encode($p->pergunta);?></strong></p>
												<p>
													<dl>
														<dd>
													<?php  
													if($pergunta->tipo=="simnao") { 
														?>
														<label>
															<input type="radio" name="resposta_<?php echo $p->id;?>" value="SIM" class="js-resposta" data-tipo="simnao_texto" data-id_resposta="<?php echo $p->id;?>"<?php echo $p->resposta=="SIM"?" checked":"";?> /> Sim
														</label>
														<label>
															<input type="radio" name="resposta_<?php echo $p->id;?>" value="NAO" class="js-resposta" data-tipo="simnao_texto" data-id_resposta="<?php echo $p->id;?>"<?php echo $p->resposta=="NAO"?" checked":"";?> /> Não
														</label>

														<?php

													}
													else if($pergunta->tipo=="simnaotexto") {
														?>
															<div>
																<label><input type="radio" name="resposta_<?php echo $p->id;?>" value="SIM" class="js-resposta" data-tipo="simnao" data-id_resposta="<?php echo $p->id;?>"<?php echo $p->resposta=="SIM"?" checked":"";?> /> Sim</label>
																<label><input type="radio" name="resposta_<?php echo $p->id;?>" value="NAO" class="js-resposta" data-tipo="simnao" data-id_resposta="<?php echo $p->id;?>"<?php echo $p->resposta=="NAO"?" checked":"";?> /> Não</label>
															</div>
															<div>
																<textarea name="resposta_<?php echo $p->id;?>" class="js-resposta" data-tipo="texto" data-id_resposta="<?php echo $p->id;?>"><?php echo utf8_encode($p->resposta_texto);?></textarea>
															</div>	
														<?php
													} else if($pergunta->tipo=="nota") {
														for($i=1;$i<=10;$i++) {
														?>
														<label>
															<input type="radio" name="resposta_<?php echo $p->id;?>" value="<?php echo $i;?>" class="js-resposta" data-tipo="nota" data-id_resposta="<?php echo $p->id;?>"<?php echo $p->resposta==$i?" checked":"";?> /> <?php echo $i;?>
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
									<?php 
										require_once("../includes/assinaturas/assinatura-canvas.php");
									?>
								</form>
						</section>
					</td>
				</tr>
			</tbody>
		</table>

		<div class="print-footer">
			<p><span class="iconify" data-icon="bx:bxs-phone" data-inline="true"></span><span><?php echo maskTelefone($clinica->telefone);?></span><span class="iconify" data-icon="ri:whatsapp-fill" data-inline="true"></span><span><?php echo maskTelefone($clinica->whatsapp);?></span></p>
			<p><?php echo $endereco;?></p>
			<p>
				<span><i class="iconify" data-icon="ph-globe-simple"></i> <a href="https://<?php echo $clinica->site;?>"><?php echo $clinica->site;?></a></span>
				<span><i class="iconify" data-icon="ph-instagram-logo"></i> <a href="https://instagram.com/<?php echo str_replace("@","",$clinica->instagram);?>"><?php echo $clinica->instagram;?></a></span>
			</p>
		</div>

	</body>
</html>