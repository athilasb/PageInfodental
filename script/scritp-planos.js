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



  $(".fechar-mobile").click(function () {
    $(".Video-pop").fadeOut();
    $(".VideoPlayer").html(``)
  })
  $(".video-viewer").click(function () {
    $(".Video-pop").fadeIn();
    $(".VideoPlayer").html(`<vm-player theme="dark" style="--vm-player-theme: #e86c8b;"> <vm-video cross-origin 
      poster="https://media.vimejs.com/poster.png">
      <source data-src="https://media.vimejs.com/720p.mp4" 
      
      type="video/mp4" /> <track  default  kind="subtitles"  src="https://media.vimejs.com/subs/portugues.vtt"  srclang="pt-br"  label="portugues"  /> </vm-video> <vm-default-ui><vm-default-ui> </vm-player>`)
  })

  $(document).ready(function () {
    const player = new Vimeo.Player('#vimeo-player', {
      id: 'https://media.geeksforgeeks.org/wp-content/uploads/20200107020629/sample_video.mp4',
      autoplay: true
    });

    player.on('play', function () {
      console.log('O vídeo foi iniciado');
    });

    player.on('pause', function () {
      console.log('O vídeo foi pausado');
    });
  });

