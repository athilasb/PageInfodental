


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



class TextScramble {
  constructor(el) {
    this.el = el
    this.chars = '!<>-_\\/[]{}—=+*^?#________'
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
        output += `<span class="dud">${char}</span>`
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


var swiper = new Swiper(".Carroseu1", {
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
  });

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