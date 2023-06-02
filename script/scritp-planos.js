$(document).ready(function() {
  $('.box-satistacao .itens').click(function() {
   
      $('.box-satistacao .itens, .box-satistacao .check').removeClass('active'); // Remove a classe 'active' de todos os elementos com as classes 'itens' e 'check'
      $(this).addClass('active'); // Adiciona a classe 'active' ao elemento clicado
      $(this).find('.check').addClass('active'); // Adiciona a classe 'active' ao elemento com a classe 'check' dentro do elemento clicado
  });
});

$(document).ready(function() {
  $('input[name="tipo-pessoa"]').change(function() {
      if ($(this).attr('id') === 'tipo-pessoa-1') {
          $('.js-name').text('CPF');
      } else if ($(this).attr('id') === 'tipo-pessoa-2') {
          $('.js-name').text('CNPJ');
      }
  });
});

$('#datetimepicker3').datetimepicker({
  format: 'd.m.Y H:i',
  inline: true,
  theme: 'dark',
});


//agenda
  $('.xdsoft_time').click(function () {
    var hasTimeCurrent = $('.xdsoft_time').hasClass('xdsoft_current');

    if (hasTimeCurrent) {
      $('.confirmar-agenda').addClass('active');
    } else {
      $('.confirmar-agenda').removeClass('active');
    }
  });
