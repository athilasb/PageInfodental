<?php
	require_once("../lib/conf.php");
	$dir="../";
	require_once("../usuarios/checa.php");

	$_pacienteIndicacoes=array();
	$sql->consult($_p."parametros_indicacoes","*","where lixo=0 order by titulo asc");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_pacienteIndicacoes[$x->id]=$x;
	}

?>
<script type="text/javascript">
	$(function(){

		$('.js-salvar').click(function(){
			let nome = $('input[name=nome]').val();
			let telefone1 = $('input[name=telefone1]').val();
			let cpf = $('input[name=cpf]').val();
			let indicacaoTipo = $('select[name=indicacao_tipo]').val()
			let indicacao = $('select[name=indicacao]').val();

			if(nome.length==0) {
				$('input[name=nome]').addClass('obg');
				swal({title: "Erro!", text: "Digite o <b>NOME</b> do paciente!",html:true,type:"error", confirmButtonColor: "#424242"});
			} else if(telefone1.length==0) {
				$('input[name=telefone1]').addClass('obg');
				swal({title: "Erro!", text: "Digite o <b>TELEFONE</b> do paciente!", html:true,type:"error", confirmButtonColor: "#424242"});
			} else {

				$(this).html('criando novo paciente...');
				let data = $('form.js-form-novopaciente').serialize(); 
			
				$.ajax({
					type:"POST",
					data:data,
					success:function(rtn) {
						if(rtn.success) {
							$('select[name=id_paciente]').prepend(`<option value="${rtn.id_paciente}">${rtn.paciente}</option>`);
							$('select[name=id_paciente]').val(rtn.id_paciente).trigger('chosen:updated');
							$('.js-telefone').find('input.telefone').val(rtn.telefone1);
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

		$('input[name=telefone1]').mobilePhoneNumber({allowPhoneWithoutPrefix: '+55'}).bind('country.mobilePhoneNumber', function(echo, country) {
	      	let countryOut = country || '';
	      	$(this).parent().parent().find('.country').remove();
	      	$(this).before(`<input type="text" disabled style="width:14%;float:left" class="country" value="${countryOut}" />`)
	      }).trigger('keyup');

		$('input[name=cpf]').inputmask('999.999.999-99');

	})
</script>

<div class="modal" style="height:auto;">

	<header class="modal-header">
		<div class="filtros">
			<h1 class="filtros__titulo">Novo Paciente</h1>
			<div class="filter-button">
				<a href="javascript:;" class="azul js-salvar"><i class="iconify" data-icon="bx-bx-check"></i><span>salvar</span></a>
			</div>
		</div>
	</header>

	<article class="modal-conteudo">
		<form method="post" class="form js-form-novopaciente">
			<input type="hidden" name="ajax" value="persistirPaciente" />
			
			<dl>
				<dt>Nome</dt>
				<dd><input type="text" name="nome" value="" class="obg" autocomplete="off" /></dd>
			</dl>
			
			<div class="colunas3">

				<dl class="dl2">
					<dt>Telefone</dt>
					<dd><input type="text" name="telefone1" class="obg" attern="\d*" x-autocompletetype="tel" value="" /></dd>
				</dl>

				<dl class="">
					<dt>CPF</dt>
					<dd><input type="text" name="cpf" value="" class="cpf" /></dd>
				</dl>
			</div>
			<script type="text/javascript">
				var indicacaoLista = [];
				$(function(){
					$('select[name=indicacao_tipo]').change(function(){
						let indicacao_tipo = $(this).val();
						if(indicacao_tipo.length>0) {
							let data = `ajax=indicacoesLista&indicacao_tipo=${indicacao_tipo}`;
							$.ajax({
								type:'POST',
								data:data,
								success:function(rtn) {
									
									console.log(rtn);
									if(rtn.success) {
										if(rtn.listas) {

											indicacaoLista=rtn.listas;
											$('.js-indicacao').val('').prop('disabled',false);
											$('.js-indicacao-id').val('');
											$('.js-indicacao').autocomplete("option", { source: indicacaoLista });
										}
									} else if(rtn.error) {
										swal({title: "Erro!", text: rtn.error,html:true,type:"error", confirmButtonColor: "#424242"});
									} else {
										swal({title: "Erro!", text: "Algum erro ocorreu durante o carregamento da lista de indicações",html:true,type:"error", confirmButtonColor: "#424242"});
									}
								}
							});
						} else {
							$('.js-indicacao').val('').prop('disabled',false);
							$('.js-indicacao-id').val('');
						}
					});

					$('.js-indicacao').autocomplete({
						source:indicacaoLista,
						 select: function (event, ui) {
							$('.js-indicacao-id').val(ui.item.id);
					    },
					    change: function( event, ui ) {
					    	if(ui.item===null) {
								$('.js-indicacao').val('');
								$('.js-indicacao-id').val('');
					    	}
					    }
					});

				})
			</script>
			<div class="colunas3">
				<dl>
					<dt>Tipo de Indicação</dt>
					<dd>
						<select name="indicacao_tipo" class="">
							<option value="">-</option>
							<?php
							foreach($_pacienteIndicacoes as $v) {
								echo '<option value="'.$v->id.'"'.($values['indicacao_tipo']==$v->id?' selected':'').' data-id="'.$v->id.'">'.utf8_encode($v->titulo).'</option>';
							}
							?>
						</select>
					</dd>
				</dl>
				<dl class="dl2">
					<dt>Indicação</dt>
					<dd><input type="text" name="" class="js-indicacao" disabled /><input type="hidden" name="indicacao" class="js-indicacao-id" /></dd>
				</dl>
			</div>
		</form>
	</article>
</div>