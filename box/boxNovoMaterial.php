<?php
	require_once("../lib/conf.php");
	$dir="../";
	require_once("../usuarios/checa.php");
?>
<script type="text/javascript">
	$(function(){
		$('.js-salvar').click(function(){
			let material = $('input[name=material]').val();

			if(material.length==0) {
				$('input[name=material]').addClass('obg');
				swal({title: "Erro!", text: "Digite o título!",html:true,type:"error", confirmButtonColor: "#424242"});
			} else {
				$(this).html('criando novo material...');
				let data = $('form.js-form-novomaterial').serialize(); 
			
				$.ajax({
					type:"POST",
					data:data,
					success:function(rtn) {
						if(rtn.success) {
							$('select[name=tipo_material]').prepend(`<option value="${rtn.id_material}">${rtn.material}</option>`);
							$('select[name=tipo_material]').val(rtn.id_material).trigger('chosen:updated');
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
			<h1 class="filtros__titulo">Novo Tipo de Material</h1>
			<div class="filter-button">
				<a href="javascript:;" class="azul js-salvar"><i class="iconify" data-icon="bx-bx-check"></i><span>Salvar</span></a>
			</div>
		</div>
	</header>

	<article class="modal-conteudo">
		<form method="post" class="form js-form-novomaterial">
			<input type="hidden" name="ajax" value="persistirMaterial" />
			
			<dl>
				<dt>Título</dt>
				<dd><input type="text" name="material" class="obg" autocomplete="off" /></dd>
			</dl>
			
		</form>
	</article>
</div>