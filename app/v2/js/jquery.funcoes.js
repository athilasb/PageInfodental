$(function() {

	$("input.data").inputmask("99/99/9999");
	$("input.hora").inputmask("99:99");
	$("input.telefone").inputmask("(99) 9999-9999");
	$("input.celular").inputmask("(99) 99999999[9]");
	$("input.cep").inputmask("99999-999");
	$("input.cpf").inputmask("999.999.999-99");
	$("input.cnpj").inputmask("99.999.999/9999-99");

	$(".chosen").chosen({allow_single_deselect:true});

	jQuery.datetimepicker.setLocale('pt');
	$('.datecalendar').datetimepicker({
		timepicker:false,
		format:'d/m/Y',
		scrollMonth:false,
		scrollTime:false,
		scrollInput:false,
	});
	
	jQuery.datetimepicker.setLocale('pt');
	$('.datepicker').datetimepicker({
		timepicker:true,
		format:'d/m/Y H:i',
		scrollMonth:false,
		scrollTime:false,
		scrollInput:false,
	});

	$(".tablesorter").tablesorter(); 

	$("[data-aside]").click(function() {
		let aside = $(this).attr("data-aside");
		$(".aside-" + aside).fadeIn(100,function() {
			$(this).children(".aside__inner1").addClass("active");
		});
	});

	$(".aside-close").click(function() {
		$(this).parent().parent().removeClass("active");
		$(this).parent().parent().parent().fadeOut();
	});

	$("[data-aside-sub]").click(function() {
		let aside = $(this).attr("data-aside");
		$(".aside-" + aside).addClass("aside_sub");
	});

	$(".aside-open").click(function() {
		$(".aside").fadeIn(100,function() {
			$(".aside .aside__inner1").addClass("active");
		});
	});

	$('.js-submit').click(function(){
		$('form.formulario-validacao').submit();
	})
	
});
