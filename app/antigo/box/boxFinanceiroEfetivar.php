<?php
	require_once("../lib/conf.php");
	$dir="../";
	require_once("../usuarios/checa.php");

	$cnt = '';

	if(isset($_GET['id']) and is_numeric($_GET['id'])) {
		$sql->consult($_p."financeiro_fluxo","*","where id= '".$_GET['id']."'");
		if($sql->rows) {
			$cnt=mysqli_fetch_object($sql->mysqry);
			$fieldsetTitulo=$cnt->valor>0?"Conta à Receber":"Conta à Pagar";
			$dtTitulo=$cnt->valor>0?"Valor a Receber":"Valor a Pagar";
		}
	}

	if(empty($cnt)) {
		$jsc->jAlert("Conta não encontrada!","erro","$,fancybox.close()");
		die();
	}

?>

<script type="text/javascript">
	$(function(){
		$("input.data").inputmask("99/99/9999");
		$('.datecalendar').datetimepicker({
			timepicker:false,
			format:'d/m/Y',
			scrollMonth:false,
			scrollTime:false,
			scrollInput:false,
		});

		$('.js-salvar').click(function(){
			if($('input[name=data_efetivado]').val().length==0) {
				swal({title: "Erro!", text: "Complete o campo de Data de Pagamento", type:"error", confirmButtonColor: "#424242"});
				$('input[name=data_efetivado]').addClass('erro')
			} else {
				$('.js-form-efetivar').submit();
			}
		})
	})
</script>

<section class="modal" style="height:auto; width:950px;">

	<header class="modal-header">
		<div class="filtros">
		
			<h1 class="filtros__titulo"></h1>
			<div class="filtros-acoes filter-button">
				<a href="javascript:;" class="azul js-salvar"><i class="iconify" data-icon="bx-bx-check"></i><span>Salvar</span></a>
			</div>
		
		</div>
	</header>
	
	<article class="modal-conteudo">

		<form method="post" class="form formulario-validacao js-form-efetivar">
			<input type="hidden" name="efetivar" value="<?php echo $cnt->id;?>" />
			<fieldset>
				<legend><?php echo $fieldsetTitulo;?></legend>
				<div class="colunas4">
					<dl>
						<dt>Recorrente</dt>
						<dd>
							<input type="text" name="" value="<?php echo $cnt->custo_recorrente==1?"Sim":"Não";?>" disabled />
						</dd>
					</dl>
					<dl>
						<dt>Vencimento</dt>
						<dd>
							<input type="text" name="" value="<?php echo date('d/m/Y',strtotime($cnt->data_vencimento));?>" disabled />
						</dd>
					</dl>
					<dl>
						<dt><?php echo $dtTitulo;?></dt>
						<dd>
							<input type="text" name="" value="<?php echo number_format($cnt->valor,2,",",".");?>" disabled />
						</dd>
					</dl>
					<dl>
						<dt>Data Pagamento</dt>
						<dd>
							<input type="text" name="data_efetivado" value="" class="data datecalendar obg" />
						</dd>
					</dl>
				</div>
			</fieldset>

				
		</form>
	</article>

</section>