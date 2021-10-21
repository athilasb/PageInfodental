function number_format (number, decimals, dec_point, thousands_sep) {
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
function jsonEscape(str)  {
    return str.replace(/\n/g, "\\\\n").replace(/\r/g, "\\\\r").replace(/\t/g, "\\\\t");
}
function jsonUnEscape(str)  {
    return str.replace(/\\n/g, "\n");
}
function retira_acentos(str) {

    com_acento = "Ã€ÃÃ‚ÃƒÃ„Ã…Ã†Ã‡ÃˆÃ‰ÃŠÃ‹ÃŒÃÃŽÃÃÃ‘Ã’Ã“Ã”Ã•Ã–Ã˜Ã™ÃšÃ›ÃœÃÅ”ÃžÃŸÃ Ã¡Ã¢Ã£Ã¤Ã¥Ã¦Ã§Ã¨Ã©ÃªÃ«Ã¬Ã­Ã®Ã¯Ã°Ã±Ã²Ã³Ã´ÃµÃ¶Ã¸Ã¹ÃºÃ»Ã¼Ã½Ã¾Ã¿Å•";
	sem_acento = "AAAAAAACEEEEIIIIDNOOOOOOUUUUYRsBaaaaaaaceeeeiiiionoooooouuuuybyr";

    novastr="";
    for(i=0; i<str.length; i++) {
        troca=false;
        for (a=0; a<com_acento.length; a++) {
            if (str.substr(i,1)==com_acento.substr(a,1)) {
                novastr+=sem_acento.substr(a,1);
                troca=true;
                break;
            }
        }
        if (troca==false) {
            novastr+=str.substr(i,1);
        }
    }
    return novastr;
}     

function unMoney(valor) {
	valor = valor.replace(/[^\d,-.]+/g,'');
    valor = valor.replace('.','').replace('.','').replace('.','').replace('.','').replace('.','').replace(',','.');
    return eval(valor);
}
function idade(ano_aniversario, mes_aniversario, dia_aniversario) {
    var d = new Date,
        ano_atual = d.getFullYear(),
        mes_atual = d.getMonth() + 1,
        dia_atual = d.getDate(),

        ano_aniversario = +ano_aniversario,
        mes_aniversario = +mes_aniversario,
        dia_aniversario = +dia_aniversario,

        quantos_anos = ano_atual - ano_aniversario;

    if (mes_atual < mes_aniversario || mes_atual == mes_aniversario && dia_atual < dia_aniversario) {
        quantos_anos--;
    }

    return quantos_anos < 0 ? 0 : quantos_anos;
}

function removerAcentos( newStringComAcento ) {
	
  var string = newStringComAcento;
	var mapaAcentosHex 	= {
		a : /[\xE0-\xE6]/g,
		A : /[\xC0-\xC6]/g,
		e : /[\xE8-\xEB]/g,
		E : /[\xC8-\xCB]/g,
		i : /[\xEC-\xEF]/g,
		I : /[\xCC-\xCF]/g,
		o : /[\xF2-\xF6]/g,
		O : /[\xD2-\xD6]/g,
		u : /[\xF9-\xFC]/g,
		U : /[\xD9-\xDC]/g,
		c : /\xE7/g,
		C : /\xC7/g,
		n : /\xF1/g,
		N : /\xD1/g,
		'-' : /\s/g
	};

	for ( var letra in mapaAcentosHex ) {
		var expressaoRegular = mapaAcentosHex[letra];
		string = string.replace( expressaoRegular, letra );
	}

	return string.replace(' ','').replace('-','').replace('?','').replace('.','');
}
function removerAcentos2( newStringComAcento ) {

  var string = newStringComAcento;
	var mapaAcentosHex 	= {
		a : /[\xE0-\xE6]/g,
		A : /[\xC0-\xC6]/g,
		e : /[\xE8-\xEB]/g,
		E : /[\xC8-\xCB]/g,
		i : /[\xEC-\xEF]/g,
		I : /[\xCC-\xCF]/g,
		o : /[\xF2-\xF6]/g,
		O : /[\xD2-\xD6]/g,
		u : /[\xF9-\xFC]/g,
		U : /[\xD9-\xDC]/g,
		c : /\xE7/g,
		C : /\xC7/g,
		n : /\xF1/g,
		N : /\xD1/g,
		'-' : /\s/g
	};

	for ( var letra in mapaAcentosHex ) {
		var expressaoRegular = mapaAcentosHex[letra];
		string = string.replace( expressaoRegular, letra );
	}
	string = string.replace(/[`~!@#$%^&*()_|+\-=?;:'",.<>\{\}\[\]\\\/]/gi, '')
	return string.replace(' ','').replace('-','').replace('?','').replace('.','');
}
$(function() {
	$("input.validade").inputmask("99/9999");
	$("input.data").inputmask("99/99/9999");
	$("input.datahora").inputmask("99/99/9999 99:99");
	$("input.hora").inputmask("99:99");
	$("input.telefone").inputmask("(99) 99999-9999");


	$("input.celular").inputmask("(99) 99999999[9]");
	$("input.cep").inputmask("99999-999");
	$("input.cpf").inputmask("999.999.999-99");
	$("input.cnpj").inputmask("99.999.999/9999-99");

	$(".nav-menu .js-expande").click(function() {
		$('.nav-menu li > a').removeClass("active");
		$(this).addClass("active");
		$('.nav-menu dl').fadeOut();
		$(this).next("dl").fadeIn("fast");
		//$(this).toggleClass('active');
		return false;
	});

	$('#btn-csv').click(function(){
	//	alert('a');
		$('form.js-filtro input[name=csv]').val(1);
		$('form.js-filtro').submit();
	});
	
	$(".tooltip").tooltipster({theme:"borderless"});

	$(".chosen").chosen({allow_single_deselect:true});

	$(".tablesorter").tablesorter(); 

	$(".js-collapse").toggle(function() {
		$("#nav, #conteudo").addClass("collapse");
		$(this).addClass("active");
	},function() {
		$("#nav, #conteudo").removeClass("collapse");
		$(this).removeClass("active");
	});
	
	$(".menu-mobile").click(function() {
		$("#nav .menu").slideToggle();
	});

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
	
	$('select.js-estado').change(function(){
		$.ajax({type:'post',
				url:'ajax/listaCidades.php',
				data:'ajax=wlib&estado='+this.value,
				success:function(cidades) { //console.log(cidades);
					if(cidades) {
						$('select.js-cidade').find('option').remove();
						$('select.js-cidade').append('<option value=""></option>');

						for(var i=0;i<cidades.length;i++) {
							if(cidades[i].id==_cidadeID) sel = ' selected';
							else if(cidades[i].cidade==_cidade || cidades[i].cidade.toUpperCase()==_cidade) sel = ' selected';
							else sel='';
						//	console.log(cidades[i].cidade+' - '+_cidade);
							$('select.js-cidade').append('<option value="'+cidades[i].id+'"'+sel+'>'+cidades[i].cidade+'</option>');
						}
						$('select.js-cidade').trigger('change').trigger('chosen:updated');
						//alert(_cidade);
						//selecionaCidade(_cidade);
					}
				}
			});
	}).trigger('change');

	$('.btn-salvar').click(function() {
		let cpf = $('.js-cpf').val().replace(/[^0-9+]/g, '');

		if(cpf.length == 11) {
			if(!validarCPF(cpf)) {
				swal({title: "Erro!", text: "Digite um CPF válido", type:"error", confirmButtonColor: "#424242"});
				return false;
			} else {
				$('form.formulario-validacao').submit();
			}
		} else {
			$('form.formulario-validacao').submit();
		}

	});

	$('.btn-submit').click(function(){
		$('form.formulario-validacao').submit();
	})

	$('.js-deletar').click(function(){
		var href=$(this).attr('href');
		let msg = $(this).attr('data-msg')?$(this).attr('data-msg'):"Você tem certeza que deseja remover este registro?";
		swal({   title: "Atenção",   text: msg,   type: "warning",   showCancelButton: true,   confirmButtonColor: "#DD6B55",   confirmButtonText: "Sim!",   cancelButtonText: "Não",   closeOnConfirm: false,   closeOnCancel: false }, function(isConfirm){   if (isConfirm) {    document.location.href=href;   } else {   swal.close();   } });
		
		return false;
	});

	$('.js-maskNumber').keyup(function() {
		let regex= /[^(\d+)\.(\d+)]/g;
		let numero = $(this).val().replace(regex,'');
		numero=eval(numero);
		$(this).val(numero);
	});
	
	$('.js-maskFloat').keyup(function() {

	    var regexp = (/[^0-9\.]|^\.+(?!$)|^0+(?=[0-9]+)|\.(?=\.|.+\.)/g);

	    if (regexp.test(this.value)) {
	        this.value = this.value.replace(regexp, '');
	    }
	});


	$('input[name=data_nascimento]').keyup(function() {

		let _dt = $(this).val().split('/');

		if(_dt.length==3 && _dt[1] && _dt[2] && _dt[0]) {
			var id = idade(_dt[2],_dt[1],_dt[0]);
			if(id) $('input.idade').val(`${id} anos`);
			else $('input.idade').val('');
		} else {
			$('input.idade').val('');
		}
	}).trigger('keyup');

	$('#js-consultacep').click(function(){
		
		var cep = $('input[name=cep]').val();
		var novo_cep="";
		for(var a =0;a<cep.length;a++) {
			if($.isNumeric(cep[a])) {
				novo_cep+=cep[a];
			}
		}
		
		if(novo_cep.length===8) {
			
			$('input[name=endereco]').prop('disabled','true');
			$('input[name=bairro]').prop('disabled','true');
			$('input[name=cidade]').prop('disabled','true');
			$('select[name=estado]').prop('disabled','true');
			//$('#txt-area-net').html('Verificando Ã¡rea NET...');
			
			cep = cep.replace('-','').replace("_","");
			//alert(cep);
			$.getJSON('https://apps.widenet.com.br/busca-cep/api/cep/'+cep+ '.json', function(data){
				//alert(data);
				$('input[name=endereco]').prop('disabled',false);
				$('input[name=bairro]').prop('disabled',false);
				$('input[name=cidade]').prop('disabled',false);
				$('select[name=estado]').prop('disabled',false);
				if(data.status==0) {
					swal({title: "AtenÃ§Ã£o!", text: "CEP nÃ£o encontrado!<br /><br /><a href=\"http://www.buscacep.correios.com.br/sistemas/buscacep/\" target=\"_blank\" style=\"text-decoration:underline;color:#cc3300;\">Consultar nos CORREIOS</a>", html:true, type:"error", confirmButtonColor: "#424242"});
				} else {
					$('input[name=endereco]').val(data.address);
					$('input[name=bairro]').val(data.district);
					$('input[name=cidade]').val(data.city);
					_cidade=data.city;
					$('select[name=estado] option').each(function() {
						if($(this).val() == data.state.toLowerCase() || $(this).val() == data.state) {
							$(this).prop("selected", true);
						}
					}).trigger('chosen:updated');	
					$('select[name=estado]').trigger('change');	
				}
			});
			
		} else {
			swal({title: "Erro!", text: "Digite corretamente o CEP que deseja consultar", type:"error", confirmButtonColor: "#424242"});
			$('#inpt-cep').addClass('erro');
		}
	
	});

});