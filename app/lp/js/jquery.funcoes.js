$(function() {

	$("input.data").inputmask("99/99/9999");
	$("input.hora").inputmask("99:99");
	$("input.telefone").inputmask("(99) 9999-9999");
	$("input.celular").inputmask("(99) 99999999[9]");
	$("input.cep").inputmask("99999-999");
	$("input.cpf").inputmask("999.999.999-99");
	$("input.cnpj").inputmask("99.999.999/9999-99");

	if(window.screen.width>667) {
		$(window).scroll(function() {
			if($(this).scrollTop() > 80) {
				$(".header").addClass("scroll");
			} else if($(this).scrollTop() < 80) {
				$(".header").removeClass("scroll");
			}
		});
	}

	$(".destaques__slick").slick({
		slidesToShow:4,
		slidesToScroll:4,
		dots:false,
		arrows:true,
		infinite:false
	});

	$(".galeria__slick").slick({
		slidesToShow:2,
		slidesToScroll:2,
		dots:false,
		arrows:true,
		infinite:false
	});

	$(".depoimentos__slick").slick({
		dots:true,
		arrows:false,
		adaptiveHeight:true
	});

	$(".widget").hover(function() {
		$(this).children(".widget-links").slideDown("fast");
	},function() {
		$(this).children(".widget-links").slideUp("fast");
	});

	

});
