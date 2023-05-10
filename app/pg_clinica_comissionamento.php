

<?php
	if (isset($_POST['ajax'])) {
		require_once("lib/conf.php");
		require_once("usuarios/checa.php");
		$rtn  = array();

		header("Content-type: application/json");
		echo json_encode($rtn);
		die();
	}
	include "includes/header.php";
	include "includes/nav.php";
	if($usr->tipo!="admin" and !in_array("financeiro",$_usuariosPermissoes)) {
		$jsc->jAlert("Você não tem permissão para acessar esta área!","erro","document.location.href='dashboard.php'");
		die();
	}
?>
<head>
<link rel="stylesheet" type="text/css" href="css/style.css?v59"/>
</head>
<header class="header">

	<div class="header__content content">
		<div class="header__inner1">
			<section class="header-title">
				<h1>Financeiro</h1>
			</section>
			<?php require_once("includes/menus/menuFinaceiro.php"); ?>
		</div>
	</div>
</header>


<main class="main">
	<div class="main__content content">
	<section class="filter">
		<div class="filter-group">
			<div class="filter-title">
				<h1>Resumo</h1>
			</div>
		</div>
	</section>
		<section class="grid">
			<div class="box box-col " >
				<?php require_once("./includes/submenus/SubFinanceiroResumo.php"); ?>
				<div class="box-col__inner1 grid media-block" style="grid-template-columns: 55% auto;"> 
					<div class="box">
						<table class="border-bottom tratamento-titulo" style="width: 100%;">
							<tbody>
								<tr>
									<td colspan="3"  class="font-color-cinza" style="padding-bottom: 10px; text-align: right;width: 100%;" >Plano de tratamento 01</td>
								</tr>
								<tr>
									<td class="font-color-cinza">Paciente</td>
									<td class="font-color-cinza">Procedimento</td>
								</tr>
								<tr>
									<td ><b>Caio Lucena dos Santos</b></td>
									<td><b>Botox</b></td>
								</tr>
								<tr>
									<td class="margin-top15 font-color-cinza" colspan="3">Profissionais</td>
								</tr>
								<tr>
									<td colspan="3" class="margin-span">
										<span><b>Dr. Kroner</b></span>
										<span>Dr. Luciano</span>
										<span>Dra. Gabriela</span>
									</td>
								</tr>
								<tr style="text-align: right;">
									<td colspan="3"><span class="font-color-cinza">Total:</span> <b>R$ 3.900,00</b></td>
								</tr>
								<tr style="text-align: right;">
									<td colspan="3" class="font-color-cinza" style="font-size: 10px;">Comissão sugerida: R$ 300,00</td>
								</tr>
							</tbody>
						</table>
						<section class="space-around margin-top25">
									<div> 
										<div class=" etapa ativo-etapa"> Aprovado</div>
										<div>
											<div><span class="iconify" data-icon="fluent:checkmark-12-filled"  style="background: #15B64F; border: 1px solid #15B64F; color: white; border-radius:30px;"></span>Aprovado</div>
											<div class="passo"></div>
											<div class="display-flex-left"><span class="etapa-desabilitado"> </span>  Cancelado</div>
										</div>
									</div>
									<div> 
										<div class="etapa">Execução</div>
										<div>
											<div class="display-flex-left"><span class="etapa-desabilitado"></span>Aprovado</div>
											<div class="passo"></div>
											<div class="display-flex-left"><span class="etapa-desabilitado"></span>Iniciado</div>
											<div class="passo"></div>
											<div class="display-flex-left"><span class="etapa-desabilitado"></span>Executado</div>
										</div>
									</div>
									<div > 
										<div class="etapa">Financeiro</div>
										<div>
											<div class="display-flex-left"><span class="etapa-desabilitado"></span>Em partes</div>
											<div class="passo"></div>
											<div class="display-flex-left"><span class="etapa-desabilitado"></span>Completo</div>
										</div>
									</div>
						</section>
					</div>
					<div class="box">
						<form action="" class="form" method="post">
							<table>
								<tbody>
									<tr style="text-align: right;">
										<td class="font-color-cinza" style="padding-bottom: 10px; text-align: right;width: 100%;" colspan="3">Valor comissão: R$ 300,00</td>
									</tr>
									<tr class="colunas2 margin-top15 ">
										<td colspan="1" class="info-item" style="margin-top:0px"><b>Dr.Kroner</b> /Principal</td>
										<td colspan="1">									
											<dd class="form-comp">
												<span>R$</span>
												<input type="text" class="js-valor" name="valor_pagamento" value="0" />
											</dd>
										</td>
									</tr>
									<tr class="colunas2 margin-top15">
										<td colspan="1" class="info-item" style="margin-top:0px">Dr. Luciano/Assistente</td>
										<td colspan="1">									
											<dd class="form-comp">
												<span>R$</span>
												<input type="text" class="js-valor" name="valor_pagamento" value="0" />
											</dd>
										</td>
									</tr>
									<tr class="colunas2 margin-top15">
										<td colspan="1" class="info-item" style="margin-top:0px">Dra. Gabriela/Assistente</td>
										<td colspan="1">									
											<dd class="form-comp">
												<span>R$</span>
												<input type="text" class="js-valor" name="valor_pagamento" value="0" />
											</dd>
										</td>
									</tr>
									<tr>
										<td class="margin-top15" style="text-align: right;">										
											<button href="javascript:;" class="button button_main botton-add" type="button" data-loading="0"> 
												<span class="iconify" data-icon="fluent:checkmark-circle-12-regular" style="color: #fecea2;"></span> Finalizar 
											</button>
										</td>
									</tr>

								</tbody>
							</table>
						</form>
					</div>
				</div>
			</div>
		</section>
	</div>
</main>

<script> 
	$(".js-valor").ready(function() {
		$('.js-valor').maskMoney({
			thousands: '.',
			decimal: ','
		});
	});



</script>
<?php
include "includes/footer.php";
?>