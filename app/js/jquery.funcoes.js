
var baseURLApiAside = 'includes/api/apiAside.php';
var baseURLApiAsidePaciente = 'includes/api/apiAsidePaciente.php';
var baseURLApiAsidePlanoDeTratamento = 'includes/api/apiAsidePlanoDeTratamento.php';
var baseURLApiAsidePagamentos = 'includes/api/apiAsidePagamentos.php';

function d2(num) {
	return num > 9 ? num : `0${num}`;
}
function number_format(number, decimals, dec_point, thousands_sep) {
	number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
	var n = !isFinite(+number) ? 0 : +number,
		prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
		sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
		dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
		s = '',
		toFixedFix = function (n, prec) {
			var k = Math.pow(10, prec);
			return '' + Math.round(n * k) / k;
		};
	// Fix for IE parseFloat(0.55).toFixed(0) = 0;
	s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
	if (s[0].length > 3) {
		s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
	}
	if ((s[1] || '').length < prec) {
		s[1] = s[1] || '';
		s[1] += new Array(prec - s[1].length + 1).join('0');
	}
	return s.join(dec);
}
function unMoney(valor) {
	if (!Number.isFinite(valor) && valor != undefined) {
		valor = valor.replace(/[^\d,-.]+/g, '');
		valor = valor.replace('.', '').replace('.', '').replace('.', '').replace('.', '').replace('.', '').replace(',', '.');
	}
	return eval(valor);
}

function retira_acentos(str) {

	com_acento = "Ã€ÃÃ‚ÃƒÃ„Ã…Ã†Ã‡ÃˆÃ‰ÃŠÃ‹ÃŒÃÃŽÃÃÃ‘Ã’Ã“Ã”Ã•Ã–Ã˜Ã™ÃšÃ›ÃœÃÅ”ÃžÃŸÃ Ã¡Ã¢Ã£Ã¤Ã¥Ã¦Ã§Ã¨Ã©ÃªÃ«Ã¬Ã­Ã®Ã¯Ã°Ã±Ã²Ã³Ã´ÃµÃ¶Ã¸Ã¹ÃºÃ»Ã¼Ã½Ã¾Ã¿Å•";
	sem_acento = "AAAAAAACEEEEIIIIDNOOOOOOUUUUYRsBaaaaaaaceeeeiiiionoooooouuuuybyr";

	novastr = "";
	for (i = 0; i < str.length; i++) {
		troca = false;
		for (a = 0; a < com_acento.length; a++) {
			if (str.substr(i, 1) == com_acento.substr(a, 1)) {
				novastr += sem_acento.substr(a, 1);
				troca = true;
				break;
			}
		}
		if (troca == false) {
			novastr += str.substr(i, 1);
		}
	}
	return novastr;
}

function validaHoraMinuto(val) {
	var regexp = (/[^0-9\:]|^\.+(?!$)|^0+(?=[0-9]+)|\.(?=\.|.+\.)/g);

	if (regexp.test(val)) val = val.replace(regexp, '');

	aux = val.split(':');
	hra = (aux[0]);
	min = (aux[1]);

	err = '';
	if (!$.isNumeric(aux[0])) err = "Hora inválida";
	else if (!$.isNumeric(aux[1])) err = "Minutos inválida";
	else if (hra >= 24) err = "A hora deve ser menor que 24";
	else if (min >= 60) err = "O minuto deve ser menor que 60";

	return err;
}
function formatarData(data, formato = 'DD/MM/YYYY') {
	// Dividir a string da data em ano, mês e dia
	const partes = data.split('-');

	let dataFormatada = '';
	// Formatar a data de acordo com o formato especificado
	switch (formato) {
		case 'DD/MM/YYYY':
			dataFormatada = `${partes[2]}/${partes[1]}/${partes[0]}`;
			break;
		case 'MM/DD/YYYY':
			dataFormatada = `${partes[1]}/${partes[2]}/${partes[0]}`;
			break;
		case 'YYYY-MM-DD':
			dataFormatada = `${partes[0]}-${partes[1]}-${partes[2]}`;
			break;
		default:
			dataFormatada = data;
	}

	return dataFormatada;
}

$(function () {

	$("input.data").inputmask("99/99/9999");
	$("input.hora").inputmask("99:99");
	$("input.telefone").inputmask("(99) 9999-9999");
	$("input.celular").inputmask("(99) 99999999[9]");
	$("input.cep").inputmask("99999-999");
	$("input.cpf").inputmask("999.999.999-99");
	$("input.cnpj").inputmask("99.999.999/9999-99");

	$('.tooltip').tooltipster();

	$(".chosen").chosen({ allow_single_deselect: true });

	jQuery.datetimepicker.setLocale('pt');
	$('.datecalendar').datetimepicker({
		timepicker: false,
		format: 'd/m/Y',
		scrollMonth: false,
		scrollTime: false,
		scrollInput: false,
	});

	jQuery.datetimepicker.setLocale('pt');
	$('.datepicker').datetimepicker({
		timepicker: true,
		format: 'd/m/Y H:i',
		scrollMonth: false,
		scrollTime: false,
		scrollInput: false,
	});

	$(".tablesorter").tablesorter();

	$("[data-aside]").click(function () {
		let aside = $(this).attr("data-aside");
		$(".aside-" + aside).fadeIn(100, function () {
			$(this).children(".aside__inner1").addClass("active");
			if (aside == "especialidade") {
				asEspecialidadesAtualizar();
			} else if (aside == "plano") {
				asPlanosAtualizar();
			}
		});
	});

	$(".aside-close").click(function () {
		$(this).parent().parent().removeClass("active");
		$(this).parent().parent().parent().fadeOut();
	});

	$("[data-aside-sub]").click(function () {
		let aside = $(this).attr("data-aside");
		$(".aside-" + aside).addClass("aside_sub");
	});

	$(".aside-open").click(function () {
		$(".aside").fadeIn(100, function () {
			$(".aside .aside__inner1").addClass("active");
		});
	});

	$('.js-submit').click(function () {
		$('form.formulario-validacao').submit();
	});

	$('.js-maskNumber').keyup(function () {
		let regex = /[^(\d+)\.(\d+)]/g;
		let numero = $(this).val().replace(regex, '');
		numero = eval(numero);
		$(this).val(numero);
	});

	$('.js-maskFloat').keyup(function () {

		var regexp = (/[^0-9\.]|^\.+(?!$)|^0+(?=[0-9]+)|\.(?=\.|.+\.)/g);

		if (regexp.test(this.value)) {
			this.value = this.value.replace(regexp, '');
		}
	});

	$('.js-maskFloat2').keyup(function () {

		let val = this.value;


		let min = $(this).attr('data-min') ? eval($(this).attr('data-min')) : -1;
		let max = $(this).attr('data-max') ? eval($(this).attr('data-max')) : -1;



		var regexp = (/[^0-9\,]|^\.+(?!$)|^0+(?=[0-9]+)|\.(?=\.|.+\.)/g);

		if (regexp.test(val)) {
			val = val.replace(regexp, '');
		}
		val = val.replace(',', '.');
		// val = $.isNumeric(val)?eval(val):val;
		// console.log(val)
		if ($.isNumeric(val) && min >= 0 && max >= 0) {
			if (val < min) val = min;
			else if (val > max) val = max;
		}
		this.value = val.toString().replace('.', ',');
	});
	$('.js-confirmarDeletar').click(function () {

		let msg = $(this).attr('data-msg') ? $(this).attr('data-msg') : 'Tem certeza que deseja remover este registro?';
		var href = $(this).attr('href');

		swal({ title: "Atenção", text: msg, type: "warning", showCancelButton: true, confirmButtonColor: "#DD6B55", confirmButtonText: "Sim!", cancelButtonText: "Não", closeOnConfirm: false, closeOnCancel: false }, function (isConfirm) { if (isConfirm) { document.location.href = href; } else { swal.close(); } });

		return false;
	})

});
