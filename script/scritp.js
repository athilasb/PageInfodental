


// função nav bar menu mobile desktop
var bolean = false
$(document).ready(function() {
    $('.icon-mobile').click(function() {
        if (bolean) {
            bolean = false
            $(".menu nav").slideUp() 
            $(".menu ul").slideUp() 
        } else {
            $(".menu nav").slideDown()
            $(".menu ul").slideDown()
            bolean = true
        }
        
    });
});




$(document).ready(function() {
  // Verifica a largura da janela ao carregar a página
  if ($(window).width() < 768) {
    $(".menu ul li a").click(function() {
      $(".menu ul").slideUp();
      $(".menu nav").slideUp();
    });
  }
  
  // Verifica a largura da janela ao redimensionar
  $(window).resize(function() {
    if ($(window).width() < 768) {
      $(".menu ul li a").click(function() {
        $(".menu ul").slideUp();
        $(".menu nav").slideUp();
      });
    } else {
      // Caso a largura da janela seja maior ou igual a 768, remova o evento de clique
      $(".menu ul li a").off("click");
    }
  });
});






$(document).ready(function() {
    $(window).resize(function() {
      if ($(window).width() > 768) {
        bolean = true
        $(".menu nav").slideDown();
        $(".menu ul").slideDown();
      } else {
        bolean = false

        $(".menu nav").slideUp();
        $(".menu ul").slideUp();
      }
    });
  });

//fim

/*Gerador de caracteres aleatorios para apresentar a frase na pagina inicial*/
class TextScramble {
  constructor(el) {
    this.el = el
    this.chars = '.'
    this.update = this.update.bind(this)
  }
  setText(newText) {
    const oldText = this.el.innerText
    const length = Math.max(oldText.length, newText.length)
    const promise = new Promise((resolve) => this.resolve = resolve)
    this.queue = []
    for (let i = 0; i < length; i++) {
      const from = oldText[i] || ''
      const to = newText[i] || ''
      const start = Math.floor(Math.random() * 40)
      const end = start + Math.floor(Math.random() * 40)
      this.queue.push({ from, to, start, end })
    }
    cancelAnimationFrame(this.frameRequest)
    this.frame = 0
    this.update()
    return promise
  }
  update() {
    let output = ''
    let complete = 0
    for (let i = 0, n = this.queue.length; i < n; i++) {
      let { from, to, start, end, char } = this.queue[i]
      if (this.frame >= end) {
        complete++
        output += to
      } else if (this.frame >= start) {
        if (!char || Math.random() < 0.28) {
          char = this.randomChar()
          this.queue[i].char = char
        }
        output += `<span class="dud" style="color:#9aa4a4;">${char}</span>`
      } else {
        output += from
      }
    }
    this.el.innerHTML = output
    if (complete === this.queue.length) {
      this.resolve()
    } else {
      this.frameRequest = requestAnimationFrame(this.update)
      this.frame++
    }
  }
  randomChar() {
    return this.chars[Math.floor(Math.random() * this.chars.length)]
  }
}

const phrases = [
  `beneficiar,
  os dentistas e pacientes.`,
  `tecnologia,
  do atendimento a gestão clínica.`
]

const el = document.querySelector('.frase1')
const fx = new TextScramble(el)

let counter = 0
const next = () => {
  fx.setText(phrases[counter]).then(() => {
    setTimeout(next, 3600)
  })
  counter = (counter + 1) % phrases.length
}

const phrases1 = [
  `tecnologia,
  do atendimento a gestão clínica.`
]

const el1 = document.querySelector('.frase2')
const fx1 = new TextScramble(el1)

let counter1 = 0
const next1 = () => {
  fx1.setText(phrases1[counter1]).then(() => {
    setTimeout(next1, 2000)
  })
  counter1 = (counter1 + 1) % phrases1.length
}
next()
/*
setTimeout(function() {
  next1()
}, 2000);
*/


/*var swiper = new Swiper(".Carroseu1", {
  slidesPerView: 2,
  spaceBetween: 30,
  freeMode: true,
  autoplay: {
      delay: 1500, // tempo em milissegundos entre cada slide
      disableOnInteraction: false, // se o auto-play deve ser desativado quando o usuário interagir com o Swiper
  },
  breakpoints: {
      // quando a largura da tela for maior que 768px (tablets), exibir 3 slides por view
      1024: {
      slidesPerView: 5,
      },
      // quando a largura da tela for menor ou igual a 767px (celulares), exibir 2 slides por view
      767: {
      slidesPerView: 3,
      },
  },
  });*/



  $(document).ready(function() {
  $('.marquee_text').marquee({
    duration:15000,
    duplicated: true,
    startVisible: true
});
  })
  var swiper = new Swiper(".Carroseu2", {
  slidesPerView: 5,
  spaceBetween: 10,
  freeMode: true,
  autoplay: {
      delay: 2500, // tempo em milissegundos entre cada slide
      disableOnInteraction: false, // se o auto-play deve ser desativado quando o usuário interagir com o Swiper
  },
  breakpoints: {
      // quando a largura da tela for maior que 768px (tablets), exibir 3 slides por view
      961: {
      slidesPerView: 14,
      },
      960: {
      slidesPerView: 13,
      },
      760: {
      slidesPerView: 11,
      },
      700: {
      slidesPerView: 10,
      },
      600: {
      slidesPerView: 9,
      },
      500: {
      slidesPerView: 8,
      },
      400: {
      slidesPerView: 7,
      },
      350: {
      slidesPerView: 6,
      },
      300: {
      slidesPerView: 5,
      },
  },
  });



  $(document).ready(function() {
    $('.menu a ,.footer a').click(function(e) {
      e.preventDefault();
      var targetId = $(this).attr('href').substring(1);
      var targetElement = $('#' + targetId);
      
      if (targetElement.length) {
        var menuHeight = $('.menu').outerHeight();
        var targetPosition = targetElement.offset().top - menuHeight;
        
        $('html, body').animate({
          scrollTop: targetPosition
        }, 100); // Tempo de duração da animação em milissegundos
      }
    });
  });


  $(document).ready(function(){
    $(".button-group a:nth-of-type(1)").click(function(){
      $(".button-group a").removeClass("active")
      $(".button-group a:nth-of-type(1)").addClass("active")
      $(".prices1").fadeIn()
      $(".prices2").css("display", "none")
      $(".prices3").css("display", "none")

    })
    $(".button-group a:nth-of-type(2)").click(function(){
      $(".button-group a").removeClass("active")
      $(".button-group a:nth-of-type(2)").addClass("active")
      $(".prices1").css("display", "none")
      $(".prices2").fadeIn()
      $(".prices3").css("display", "none")
      
    })
    $(".button-group a:nth-of-type(3)").click(function(){
      $(".button-group a").removeClass("active")
      $(".button-group a:nth-of-type(3)").addClass("active")
      $(".prices1").css("display", "none")
      $(".prices2").css("display", "none")
      $(".prices3").fadeIn()
      
    })
  })
  
  //Contagem regreciva
  $(document).ready(function() {
    var daysElement = $('.regressive-prices-day b');
    var hoursElement = $('.regressive-prices-hrs b');
    var minutesElement = $('.regressive-prices-min b');
    var secondsElement = $('.regressive-prices-seg b');
  
    // Define o tempo total da contagem regressiva em segundos
    var countdownTime = 172800;
  
    // Obtém o horário de entrada do usuário ou usa o horário atual
    var entryTime = localStorage.getItem('entryTime');
    if (!entryTime) {
      entryTime = new Date().getTime(); // Obtém o horário atual em milissegundos
      localStorage.setItem('entryTime', entryTime); // Salva o horário de entrada no armazenamento local
    }
  
    function updateCountdown() {
      var currentTime = new Date().getTime();
      var elapsedSeconds = Math.floor((currentTime - entryTime) / 1000);
  
      var remainingSeconds = countdownTime - elapsedSeconds;
      var days = Math.floor(remainingSeconds / 86400); // Calcula o número de dias
      var hours = Math.floor((remainingSeconds % 86400) / 3600); // Calcula o número de horas restantes
      var minutes = Math.floor((remainingSeconds % 3600) / 60); // Calcula o número de minutos restantes
      var seconds = Math.floor(remainingSeconds % 60); // Calcula o número de segundos restantes
  
      if (hours < 0 || minutes < 0 || seconds < 0) {
        entryTime = new Date().getTime(); // se a contagem for menor de 0 refazer a contagem
        localStorage.setItem('entryTime', entryTime); //Regravar a contagem em localStorage
      }
  
      daysElement.text(('0' + days).slice(-2)); // Exibe os dias formatados com dois dígitos
      hoursElement.text(('0' + hours).slice(-2)); // Exibe as horas formatadas com dois dígitos
      minutesElement.text(('0' + minutes).slice(-2)); // Exibe os minutos formatados com dois dígitos
      secondsElement.text(('0' + seconds).slice(-2)); // Exibe os segundos formatados com dois dígitos
  
      if (remainingSeconds <= 0) {
        clearInterval(countdownInterval);
      }
    }
    updateCountdown();
    var countdownInterval = setInterval(updateCountdown, 1000);
  });
  

  $('#datetimepicker3').datetimepicker({
    format:'d.m.Y H:i',
    inline:true,
    theme: 'dark',
  });



  
//modal

  $(".js-fechar-modal , .fechar-mobile").click(function() {
    $(".modal-calendario").fadeOut();
  });

  $(".button-prices").click(function() {
    $(".modal-calendario").fadeIn();
  });


  var swiper = new Swiper(".mySwiper", {
    slidesPerView: 1,
    spaceBetween: 10,
    pagination: {
      el: ".swiper-pagination",
      clickable: true,
    },
          autoplay: {
        delay: 2500,
        disableOnInteraction: false,
      },
      navigation: {
        nextEl: ".swiper-button-next",
        prevEl: ".swiper-button-prev",
      },
    breakpoints: {
      640: {
        slidesPerView: 1,
        spaceBetween: 20,
      },
      768: {
        slidesPerView: 2,
        spaceBetween: 40,
      },
      1024: {
        slidesPerView: 3,
        spaceBetween: 50,
      },
    },
  });