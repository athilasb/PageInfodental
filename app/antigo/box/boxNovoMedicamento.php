<?php
	require_once("../lib/conf.php");
	$dir="../";
	require_once("../usuarios/checa.php");

	$_medicamentosTipos=array('ampola'=>'Ampola(s)',
							 'caixa'=>'Caixa(s)',
							 'comprimido'=>'Comprimido(s)',
							 'frasco'=>'Frasco(s)',
							 'pacote'=>'Pacote(s)',
							 'tubo'=>'Tubo(s)',
							 'capsula'=>'Capsula(s)');
?>
<script type="text/javascript">
	$(function(){
		$('.js-salvar').click(function(){
			let medicamento = $('input[name=medicamento]').val();
			let posologia = $('input[name=posologia]').val();
			let quantidade = $('input[name=quantidade]').val();
			let tipo = $('select[name=tipo]').val(); 
			let controleespecial = $('select[name=controleespecial]').prop('checked')===true?1:0; 

			if(medicamento.length==0) {
				$('input[name=medicamento]').addClass('obg');
				swal({title: "Erro!", text: "Digite o medicamento!",html:true,type:"error", confirmButtonColor: "#424242"});
			} else if(posologia.length==0) {
				$('input[name=posologia]').addClass('obg');
				swal({title: "Erro!", text: "Digite a posologia!",html:true,type:"error", confirmButtonColor: "#424242"});
			} else if(quantidade.length==0) {
				$('input[name=quantidade]').addClass('obg');
				swal({title: "Erro!", text: "Digite a quantidade!",html:true,type:"error", confirmButtonColor: "#424242"});
			} else if(tipo.length==0) {
				$('select[name=tipo]').addClass('obg');
				swal({title: "Erro!", text: "Digite o tipo do medicamento!",html:true,type:"error", confirmButtonColor: "#424242"});
			} else {
				$(this).html('criando novo medicamento...');
				let data = $('form.js-form-novomedicamento').serialize(); 
			
				$.ajax({
					type:"POST",
					data:data,
					success:function(rtn) {
						if(rtn.success) {
							$('.js-input-medicamento').val(rtn.medicamento);
							$('.js-input-quantidade').val(rtn.quantidade);
							$('.js-input-tipo').val(rtn.tipo).trigger('chosen:updated');
							$('.js-input-posologia').val(rtn.posologia);
							$.fancybox.close();
						} else if(rtn.error) {
							swal({title: "Erro!", text: rtn.error, html:true,type:"error", confirmButtonColor: "#424242"});
						} else {
							swal({title: "Erro!", text: "Algum erro ocorreu! Tente novamente...", html:true,type:"error", confirmButtonColor: "#424242"});
						}
					}
				}).done(function(){
					$('.js-salvar').html(`<i class="iconify" data-icon="bx-bx-check"></i> Salvar`);
				});
				
			}
		});
	})
</script>

<div class="modal" style="height:auto;width:600px;">

	<header class="modal-header">
		<div class="filtros">
			<h1 class="filtros__titulo">Novo Medicamento</h1>
			<div class="filter-button">
				<a href="javascript:;" class="azul js-salvar"><i class="iconify" data-icon="bx-bx-check"></i><span>salvar</span></a>
			</div>
		</div>
	</header>

	<article class="modal-conteudo">
		<form method="post" class="form js-form-novomedicamento">
			<input type="hidden" name="ajax" value="persistirMedicamento" />
			
			<dl>
				<dt>Medicamento</dt>
				<dd><input type="text" name="medicamento" value="" class="obg" autocomplete="off" /></dd>
			</dl>
			<div class="colunas3">
				<dl>
					<dt>Quantidade</dt>
					<dd>
						<input type="number" name="quantidade" min="1" value="1" class="obg" />
					</dd>
				</dl>
				<dl class="dl2">
					<dt>Tipo</dt>
					<dd>
						<select name="tipo" class="obg">
							<option value="">-</option>
							<?php
							foreach($_medicamentosTipos as $k=>$v) {
								echo '<option value="'.$k.'"'.($values['tipo']==$k?' selected':'').'>'.$v.'</option>';
							}
							?>
						</select>
					</dd>
				</dl>
			</div>
			<dl>
				<dt>Posologia</dt>
				<dd><input type="text" name="posologia" value="" class="obg" autocomplete="off" /></dd>
			</dl>
				<dd><label><input type="checkbox" name="controleespecial" value="1" /> Medicamento de controle especial</label></dd>
			</dl>
			
		</form>
	</article>
</div>