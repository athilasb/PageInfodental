<?php
include "includes/header-pg.php";
include "includes/nav.php";
?>

<script>
$(function() {
	$("input.cep").inputmask("99999-999");
	$("input.validade").inputmask("99/99");

	$("input[name=tipo]").click(function() {
		let val = $("input[name=tipo]:checked").val();
		if (val == 'pf') {
			$("input[name=identity]").removeClass('cnpj');
			$("input[name=identity]").addClass('cpf');
			$("input.cpf").inputmask("999.999.999-99");
			$("#dt_cpf").text('CPF');
      $("#dt_nome").text('Nome Completo');
		} else {
			$("input[name=identity]").removeClass('cpf');
			$("input[name=identity]").addClass('cnpj');
			$("input.cnpj").inputmask("99.999.999/9999-99");
			$("#dt_cpf").text('CNPJ');
      $("#dt_nome").text('Razão Social');
		}
	});
});

function save() {
	let isValidCampos = true;
	let isValidIdentity = true;

	$(".formulario-validacao input").each((index, input) => {
		if ($(input).val() === '') {
			$(input).css('background-color','#FDF3E4');
			isValidCampos = false;
		} else {
			$(input).css('background-color', '#FFFFFF');
		}
	});

	const identityValue = $('input[name=identity]').val();
	if($('input[name=identity]').hasClass('cpf')) {
		isValidIdentity = validarCPF(identityValue);
		isValidIdentityMessage = 'CPF inválido';
	} else {
		isValidIdentity = validarCNPJ(identityValue);
		isValidIdentityMessage = 'CNPJ inválido';
	}

	if(!isValidCampos) {
		swal({title: "Erro", text: "Complete os campos destacados.", type: "error", confirmButtonColor: "#F5C357"});
		return
	} else if(!isValidIdentity) {
		swal({title: "Erro", text: isValidIdentityMessage, type: "error", confirmButtonColor: "#F5C357"});
		return
	}

	$(".confirm").attr('disabled', 'disabled');
	$(".confirm").hide();

	swal({
		title: "Aguarde",
		text: "Armazenando dados do cartão",
		imageUrl: "img/ajax_loading.svg"
	});

	$.post('ajax/pg.php', {
	action: $('input[name=action]').val(),
	instance_id: $('input[name=instance_id]').val(),
	tipo: $('input[type=radio]:checked').val(),
	identity: $('input[name=identity]').val(),
	name: $('input[name=nome]').val(),
	email: $('input[name=email]').val(),
	cep: $('input[name=cep]').val(),
	logradouro: $('input[name=logradouro]').val(),
	numero: $('input[name=numero]').val(),
	complemento: $('input[name=complemento]').val(),
	bairro: $('input[name=bairro]').val(),
	cidade: $('input[name=cidade]').val(),
	estado: $('input[name=estado]').val(),
	cartao_bandeira: $('#bandeira').val(),
	cartao_numero: $('input[name=cartao_numero]').val().replace(/ +/g, ""),
	cartao_validade: $('input[name=cartao_validade]').val(),
	cartao_cvv: $('input[name=cartao_cvv]').val(),
	cartao_nome: $('input[name=cartao_nome]').val()
	}, function(data) {
	$(".confirm").removeAttr("disabled");
	$(".confirm").show();

		if(data) {
			swal({
				title: "Sucesso",
				text: `Cartão salvo com sucesso!`,
				type: "success",
				confirmButtonColor: "#F5C357"
			}, function(redirect) {
				window.location.replace('faturamento-plano.php');
			});
		} else {
			swal({
				title: "Erro",
				text: `Não foi possível salvar os dados do seu cartão!`,
				type: "error",
				confirmButtonColor: "#F5C357"
			});
		}
	});
}
</script>

<style>.header__menu, .header-nav, .footer {display:none;}</style>

	<main class="main">

		<section class="fatura-cartao">

			<h1 class="fatura-cartao__titulo"><a href="javascript:history.back();"><i class="iconify" data-icon="bx:bx-chevron-left"></i></a> Faturamento</h1>

			<form name="save_card" class="fatura-cartao-form form-row formulario-validacao">
			<input type="hidden" name="action" value="save_card" />
			<input type="hidden" name="instance_id" value="<?php echo $instance_id ?>" />

				<fieldset>
					<legend>Dados para Faturamento</legend>
					
					<dl>
						<dt>Tipo</dt>
						<dd>
							<label><input type="radio" name="tipo" value="pf" checked />Pessoa Física</label>
							<label><input type="radio" name="tipo" value="pj" />Pessoa Jurídica</label>
						</dd>
					</dl>
					<dl>
						<dt id="dt_cpf">CPF</dt>
						<dd><input type="tel" name="identity" class="obg cpf" /></dd>
					</dl>
					<dl>
						<dt id="dt_nome">Nome Completo</dt>
						<dd><input type="text" name="nome" class="obg" /></dd>
					</dl>
					<dl>
						<dt>E-mail</dt>
						<dd><input type="email" name="email" class="obg" /></dd>
					</dl>
					
				</fieldset>
				<fieldset>
					<legend>Endereço para Faturamento</legend>
					
					<dl>
						<dt>CEP</dt>
						<dd><input type="tel" name="cep" class="obg cep" /></dd>
					</dl>
				
					<dl>
						<dt>Logradouro</dt>
						<dd><input type="text" name="logradouro" class="obg" /></dd>
					</dl>
					<dl>
						<dt>Número</dt>
						<dd><input type="tel" name="numero" class="obg" /></dd>
					</dl>
					<dl>
						<dt>Complemento</dt>
						<dd><input type="text" name="complemento" class="obg" /></dd>
					</dl>
				
					<dl>
						<dt>Bairro</dt>
						<dd><input type="text" name="bairro" class="obg" /></dd>
					</dl>
					<dl>	
						<dt>Cidade</dt>
						<dd><input type="text" name="cidade" class="obg" /></dd>
					</dl>
					<dl>
						<dt>Estado</dt>
						<dd><input type="text" name="estado" class="obg" /></dd>
					</dl>
					
				</fieldset>
				<fieldset>
					<legend>Cartão de crédito</legend>
					<dl>
						<dt>Bandeira</dt>
						<dd>
							<select id="bandeira" name="cartao_bandeira">
								<option value="master">Mastercard</option>
								<option value="visa">Visa</option>
								<option value="amex">American Express</option>
								<option value="elo">Elo</option>
								<option value="aura">Aura</option>
								<option value="jcb">JCB</option>
							</select>
						</dd>
					</dl>
					<dl>
						<dt>Número do cartão</dt>
						<dd><input type="tel" name="cartao_numero" class="obg" /></dd>
					</dl>
					<dl>
						<dt>Validade</dt>
						<dd><input type="tel" name="cartao_validade" class="obg validade" /></dd>
					</dl>
					<dl>
						<dt>Código de Segurança</dt>
						<dd><input type="tel" name="cartao_cvv" class="obg" maxlength="4" /></dd>
					</dl>
					
					<dl>
						<dt>Nome no Cartão</dt>
						<dd><input type="text" name="cartao_nome" class="obg" /></dd>
					</dl>
				</fieldset>

				<button onclick="save()" type="button" class="button button__lg"><i class="iconify" data-icon="bx:bx-check"></i> Salvar cartão</button>

			</form>	

		</section>
	</main>

<?php
include "includes/footer.php";
?>
