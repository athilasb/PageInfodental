<?php
	require_once("../lib/conf.php");
	$dir="../";
	require_once("../usuarios/checa.php");
?>
<script type="text/javascript">
	$(function(){
		$('.js-salvar').click(function(){
			let titulo = $('input[name=titulo]').val();

			if(titulo.length==0) {
				$('input[name=titulo]').addClass('obg');
				swal({title: "Erro!", text: "Digite o <b>TÍTULO</b> da profissão!",html:true,type:"error", confirmButtonColor: "#424242"});
			} else {

				$(this).html('criando nova profissão...');
				let data = $('form.js-form-novaprofissao').serialize(); 
			
				$.ajax({
					type:"POST",
					data:data,
					success:function(rtn) {
						if(rtn.success) {
							$('select[name=profissao]').prepend(`<option value="${rtn.id_profissao}">${rtn.profissao}</option>`);
							$('select[name=profissao]').val(rtn.id_profissao).trigger('chosen:updated');
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
			<h1 class="filtros__titulo">Nova Profissão</h1>
			<div class="filter-button">
				<a href="javascript:;" class="azul js-salvar"><i class="iconify" data-icon="bx-bx-check"></i><span>salvar</span></a>
			</div>
		</div>
	</header>

	<article class="modal-conteudo">
		<form method="post" class="form js-form-novaprofissao">
			<input type="hidden" name="ajax" value="persistirProfissao" />
			
			<dl>
				<dt>Título</dt>
				<dd><input type="text" name="titulo" value="" class="obg" autocomplete="off" /></dd>
			</dl>
			
		</form>
	</article>
</div>