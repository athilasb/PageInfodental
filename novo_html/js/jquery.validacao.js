// JavaScript Document
function validaData(valor) {
	var date=valor;
	var ardt=new Array;
	var ExpReg=new RegExp("(0[1-9]|[12][0-9]|3[01])/(0[1-9]|1[012])/[12][0-9]{3}");
	ardt=date.split("/");
	erro=false;
	if ( date.search(ExpReg)==-1){
		erro = true;
		}
	else if (((ardt[1]==4)||(ardt[1]==6)||(ardt[1]==9)||(ardt[1]==11))&&(ardt[0]>30))
		erro = true;
	else if ( ardt[1]==2) {
		if ((ardt[0]>28)&&((ardt[2]%4)!=0))
			erro = true;
		if ((ardt[0]>29)&&((ardt[2]%4)==0))
			erro = true;
	}
	if (erro) {
		return false;
	}
	return true;
}

function isValidEmailAddress(emailAddress) {
		var pattern = new RegExp(/^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?$/i);
		return pattern.test(emailAddress);
	}
	function validarCNPJ(cnpj) {
		cnpj = cnpj.replace(/[^\d]+/g,'');
		if(cnpj == '') return false;
		if (cnpj.length != 14)
			return false;
		// Elimina CNPJs invalidos conhecidos
		if (cnpj == "00000000000000" || cnpj == "11111111111111" || cnpj == "22222222222222" || cnpj == "33333333333333" || cnpj == "44444444444444" || cnpj == "55555555555555" || cnpj == "66666666666666" || cnpj == "77777777777777" || cnpj == "88888888888888" || cnpj == "99999999999999") return false;
		// Valida DVs
		tamanho = cnpj.length - 2
		numeros = cnpj.substring(0,tamanho);
		digitos = cnpj.substring(tamanho);
		soma = 0;
		pos = tamanho - 7;
		for (i = tamanho; i >= 1; i--) {
		  soma += numeros.charAt(tamanho - i) * pos--;
		  if (pos < 2) pos = 9;
		}
		resultado = soma % 11 < 2 ? 0 : 11 - soma % 11;
		if (resultado != digitos.charAt(0)) return false;
			 
		tamanho = tamanho + 1;
		numeros = cnpj.substring(0,tamanho);
		soma = 0;
		pos = tamanho - 7;
		for (i = tamanho; i >= 1; i--) {
		  soma += numeros.charAt(tamanho - i) * pos--;
		  if (pos < 2) pos = 9;
		}
		resultado = soma % 11 < 2 ? 0 : 11 - soma % 11;
		if (resultado != digitos.charAt(1)) return false;
		return true;
	}
	function validarCPF(cpf) {
		cpf = cpf.replace(/[^\d]+/g,'');
		if(cpf == '') return false;
		if (cpf.length != 11 || cpf == "00000000000" || cpf == "11111111111" || cpf == "22222222222" || cpf == "33333333333" || cpf == "44444444444" || cpf == "55555555555" || cpf == "66666666666" || cpf == "77777777777" || cpf == "88888888888" || cpf == "99999999999") return false;
		// Valida 1o digito
		add = 0;
		for (i=0; i < 9; i ++) add += parseInt(cpf.charAt(i)) * (10 - i);
		rev = 11 - (add % 11);
		if (rev == 10 || rev == 11) rev = 0;
		if (rev != parseInt(cpf.charAt(9))) return false;
		
		// Valida 2o digito
		add = 0;
		for (i = 0; i < 10; i ++) add += parseInt(cpf.charAt(i)) * (11 - i);
		rev = 11 - (add % 11);
		if (rev == 10 || rev == 11) rev = 0;
		if (rev != parseInt(cpf.charAt(10))) return false;
		return true;		
	}

$(function(){
	$('form.formulario-validacao').submit(function(){
		//alert('a');
		var alertar=false;
		var alertarEmail=false;
		var alertaSenha=false;
		var alertaCPF=false;
		var alertaCNPJ=false;
		var alertaData=false;
		$(this).find("input[type=text],input[type=tel],input[type=email],input[type=file],input[type=password],select,textarea").each(function(){  
				if($(this).hasClass('obg')) { 
				//alert($(this).attr('name')+'  '+$(this).val().length);
					if ($(this).val().length==0 || $(this).val()=='Seu nome' || $(this).val()=='E-mail' || $(this).val()=='Telefone' || $(this).val()=='Assunto' || $(this).val()=='Mensagem') {
						$(this).addClass('erro');
						alertar=true;
					} else if($(this).hasClass('email')) {
						if(isValidEmailAddress($(this).val())===false) { 
							$(this).addClass('erro');
							alertarEmail=true;
						}
						
					} else if($(this).hasClass('cpf')) { 
						if(!validarCPF($(this).val())) { 
							$(this).addClass('erro');
							alertaCPF=true;
						}
					} else if($(this).hasClass('cnpj')) { 
						if(!validarCNPJ($(this).val())) { 
							$(this).addClass('erro');
							alertaCNPJ=true;
						}
					} else if($(this).hasClass('data')) { 
						if(validaData($(this).val())===false) { 
							$(this).addClass('erro');
							alertaData=true;
						}
					} else if($(this).hasClass('senha')) { 
						if($(this).val()!=$('form.formulario-validacao input[name=senha2]').val()) { 
							$(this).addClass('erro');
							$('form.formulario-validacao input[name=senha2]').addClass('erro');
							alertaSenha=true;
						}
					}
				}
		});
		if(alertar) {
			swal({title: "Erro!", text: "Complete os campos destacados", type:"error", confirmButtonColor: "#424242"});
			return false;
		} else if(alertarEmail) {
			swal({title: "Erro!", text: "Digite um e-mail válido", type:"error", confirmButtonColor: "#424242"});
			return false;
		}else if(alertaSenha) {
			swal({title: "Erro!", text: "As senhas não conferem", type:"error", confirmButtonColor: "#424242"});
			return false;
		}else if(alertaData) {
			swal({title: "Erro!", text: "Escolha uma data válida", type:"error", confirmButtonColor: "#424242"});
			return false;
		}else if(alertaCPF) {
			swal({title: "Erro!", text: "Digite um CPF válido", type:"error", confirmButtonColor: "#424242"});
			return false;
		}else if(alertaCNPJ) {
			swal({title: "Erro!", text: "Digite um CNPJ válido", type:"error", confirmButtonColor: "#424242"});
			return false;
		}
	});
	
	$('form.formulario-validacao input,form.formulario-validacao select,form.formulario-validacao textarea').focus(function(){
		if($(this).hasClass('erro')) $(this).removeClass('erro');
	});
	
	$('form.formulario-validacao input[type=file]').click(function(){
		if($(this).hasClass('erro')) $(this).removeClass('erro');
	});
	$('form.formulario-validacao .emailh').hide();
});
