$(document).ready(function() {
  $('.box-satistacao .itens').click(function() {
   
      $('.box-satistacao .itens, .box-satistacao .check').removeClass('active'); // Remove a classe 'active' de todos os elementos com as classes 'itens' e 'check'
      $(this).addClass('active'); // Adiciona a classe 'active' ao elemento clicado
      $(this).find('.check').addClass('active'); // Adiciona a classe 'active' ao elemento com a classe 'check' dentro do elemento clicado
  });
});