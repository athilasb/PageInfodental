<?php
	require_once("../lib/conf.php");
	$dir="../";
	require_once("../usuarios/checa.php");

	$jsc=new Js();

	$paciente='';
	if(isset($_GET['id_paciente']) and is_numeric($_GET['id_paciente'])) {
		$sql->consult($_p."pacientes","*","where id='".addslashes($_GET['id_paciente'])."'");
		if($sql->rows) {
			$paciente=mysqli_fetch_object($sql->mysqry);
		}
	}

	if(empty($paciente)) {
		$jsc->jAlert("Paciente não encontrado!","erro","$.fancybox.close();");
		die();
	}
?>

<script type="text/javascript">
	$(function(){

		$('.js-salvar').click(function(){

			let telefone1 = $('input[name=telefone1]').val();
			
			if(telefone1.length==0) {
				$('input[name=telefone1]').addClass('obg');
				swal({title: "Erro!", text: "Digite o <b>TELEFONE</b> do paciente!", html:true,type:"error", confirmButtonColor: "#424242"});
			} else {

				$(this).html('atualizando telefone...');
				let data = $('form.js-form-novopaciente').serialize(); 
			
				$.ajax({
					type:"POST",
					data:data,
					success:function(rtn) {
						if(rtn.success) {

							
							$('.js-telefone').find('input.telefone').val(rtn.telefone1);
							$('select[name=id_paciente] option:selected').attr('data-telefone',rtn.telefone1);
							$('select[name=id_paciente').trigger('chosen:updated');
							$.fancybox.close();

						} else if(rtn.error) {
							swal({title: "Erro!", text: rtn.error, html:true,type:"error", confirmButtonColor: "#424242"});
						} else {
							swal({title: "Erro!", text: "Algum erro ocorreu! Tente novamente...", html:true,type:"error", confirmButtonColor: "#424242"});
						}
					}
				}).done(function(){
					$('.js-salvar').html(`<i class="iconify" data-icon="bx-bx-check"></i> Salvar`);
				})
				
			}
		});

		$('input[name=telefone1]').mobilePhoneNumber({allowPhoneWithoutPrefix: '+55'}).bind('country.mobilePhoneNumber', function(echo, country) {
	      	let countryOut = country || '';
	      	$(this).parent().parent().find('.country').remove();
	      	$(this).before(`<input type="text" diabled style="width:14%;float:left" class="country" value="${countryOut}" />`)
	      }).trigger('keyup');

	})
</script>
<div class="modal" style="height:auto;">

	<header class="modal-header">
		<div class="filtros">
			<h1 class="filtros__titulo">Editar telefone</h1>
			<div class="filtros-acoes">
				<button type="submit" class="principal js-salvar"><i class="iconify" data-icon="bx-bx-check"></i></button>
			</div>
		</div>
	</header>

	<article class="modal-conteudo">
		<form method="post" class="form js-form-novopaciente">
			<input type="hidden" name="ajax" value="persistirPacienteTelefone" />
			<input type="hidden" name="id_paciente" value="<?php echo $paciente->id;?>" />
			<dl>
				<dd><input type="text" name="telefone1" class="obg" pattern="\d*" x-autocompletetype="tel" value="<?php echo $paciente->telefone1;?>" /></dd>
			</dl>
		</form>
	</article>
</div>