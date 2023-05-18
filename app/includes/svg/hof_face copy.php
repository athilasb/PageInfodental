<div style="align-items: center; justify-content: end; display: flex;">							
        <a class="button " href="javascript:;" id="limpar-canvas" > <span class="iconify" data-icon="carbon:clean"></span> Limpar</a>
        <a class="button js-desenhar" href="javascript:;" id="limpar-canvas" ><span class="iconify" data-icon="fluent:copy-select-20-filled"></span> Desenhar</a>
        <a class="button active" href="javascript:;" id="limpar-canvas" href=""> Região</a>
</div>
    <canvas style="display: block;margin: auto;position: absolute;" id="canvas" width="600px" height="500"></canvas>
    <div class="svg-face" style="text-align: center;">
        <svg style="position: relative;top: 109px;right: 55px; width: 20px;" width="23" height="39" viewBox="0 0 23 39" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path stroke="#ffff" d="M16.9738 34.772C15.5752 36.5633 13.9782 37.8553 12.2915 38.4922L11.9382 37.5567C11.1012 37.8727 10.2569 37.9962 9.41496 37.9165C8.57301 37.8368 7.7669 37.557 7.00407 37.0895L6.4815 37.9421C4.94434 36.9999 3.61825 35.4311 2.58067 33.4091L3.47037 32.9525C2.67768 31.4077 2.05316 29.5603 1.64764 27.493L0.66634 27.6855C0.299322 25.8146 0.108329 23.7871 0.120825 21.6646L1.12081 21.6705C1.12646 20.7111 1.17497 19.7309 1.26918 18.736C1.36338 17.7412 1.49971 16.7693 1.67424 15.8259L0.690923 15.644C1.07704 13.5568 1.6452 11.6012 2.35686 9.83243L3.28459 10.2057C4.07091 8.25133 5.0311 6.5539 6.09966 5.18537L5.31146 4.56995C6.71013 2.77862 8.30711 1.48659 9.99379 0.849698L10.347 1.78523C11.1841 1.46916 12.0284 1.34567 12.8703 1.4254C13.7123 1.50512 14.5184 1.78489 15.2812 2.25244L15.8038 1.39985C17.341 2.34201 18.667 3.91083 19.7046 5.93284L18.8149 6.38939C19.6076 7.93416 20.2321 9.78165 20.6377 11.8489L21.619 11.6564C21.986 13.5273 22.177 15.5548 22.1645 17.6773L21.1645 17.6714C21.1588 18.6308 21.1103 19.611 21.0161 20.6059C20.9219 21.6007 20.7856 22.5726 20.6111 23.516L21.5944 23.6979C21.2083 25.7851 20.6401 27.7407 19.9284 29.5095L19.0007 29.1362C18.2144 31.0906 17.2542 32.788 16.1856 34.1565L16.9738 34.772Z" stroke-width="2" stroke-dasharray="5 5"/>
        </svg>
        
        <svg style="position: relative;top: 100px;right: 56px;width: 135px;" width="168" height="55" viewBox="0 0 168 55" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path stroke="#ffff" d="M26.9015 1.89618L2.24426 53.0931H165.852L143.578 1.89618H26.9015Z" stroke-width="2" stroke-dasharray="5 5"/>
        </svg>
        
        <svg width="23" height="39" viewBox="0 0 23 39" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path stroke="#ffff" d="M6.08079 34.772C7.47946 36.5633 9.07645 37.8553 10.7631 38.4922L11.1164 37.5567C11.9534 37.8727 12.7977 37.9962 13.6397 37.9165C14.4816 37.8368 15.2877 37.557 16.0506 37.0895L16.5731 37.9421C18.1103 36.9999 19.4364 35.4311 20.474 33.4091L19.5843 32.9525C20.377 31.4077 21.0015 29.5603 21.407 27.493L22.3883 27.6855C22.7553 25.8146 22.9463 23.7871 22.9338 21.6646L21.9338 21.6705C21.9282 20.7111 21.8797 19.7309 21.7855 18.736C21.6912 17.7412 21.5549 16.7693 21.3804 15.8259L22.3637 15.644C21.9776 13.5568 21.4094 11.6012 20.6978 9.83243L19.77 10.2057C18.9837 8.25133 18.0235 6.5539 16.955 5.18537L17.7432 4.56995C16.3445 2.77862 14.7475 1.48659 13.0608 0.849698L12.7076 1.78523C11.8705 1.46916 11.0262 1.34567 10.1843 1.4254C9.34234 1.50512 8.53623 1.78489 7.7734 2.25244L7.25083 1.39985C5.71367 2.34201 4.38758 3.91083 3.35 5.93284L4.2397 6.38939C3.44701 7.93416 2.82249 9.78165 2.41697 11.8489L1.43567 11.6564C1.06865 13.5273 0.877662 15.5548 0.890157 17.6773L1.89014 17.6714C1.89579 18.6308 1.9443 19.611 2.03851 20.6059C2.13271 21.6007 2.26904 22.5726 2.44357 23.516L1.46026 23.6979C1.84637 25.7851 2.41453 27.7407 3.1262 29.5095L4.05392 29.1362C4.84025 31.0906 5.80043 32.788 6.86899 34.1565L6.08079 34.772Z" stroke-width="2" stroke-dasharray="5 5"/>
        </svg>
    </div>





<script>
    // Seleciona o elemento canvas
    $(document).ready(function() {
        var desenhar = false;

    // Seleciona o elemento canvas
    const canvas = $("#canvas")[0];

    // Configura o contexto 2D
    const ctx = canvas.getContext("2d");
    ctx.lineWidth = 5;
    ctx.lineCap = "round";

    // Cria um objeto de imagem e define o caminho da imagem
    const img = new Image();
    img.src = "./img/RetratoMulher.png";

    // Desenha a imagem de fundo no canvas
    img.onload = function() {
    ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
    };

    // Inicializa as variáveis de posição
    let isDrawing = false;
    let lastX = 0;
    let lastY = 0;

    // Adiciona os eventos de mouse
    canvas.addEventListener("mousedown", (e) => {

    if (desenhar === true) {
    isDrawing = true;
    lastX = e.offsetX;
    lastY = e.offsetY;
    
    } else {
            
        }
    });

    canvas.addEventListener("mousemove", (e) => {
    if (!isDrawing) return;

    ctx.beginPath();
    ctx.moveTo(lastX, lastY);
    ctx.lineTo(e.offsetX, e.offsetY);
    ctx.stroke();

    lastX = e.offsetX;
    lastY = e.offsetY;
    });

    canvas.addEventListener("mouseup", () => {
    isDrawing = false;
    });

    canvas.addEventListener("mouseout", () => {
    isDrawing = false;
    });

    // Seleciona o botão de limpar
    const btnLimpar = $('#limpar-canvas').click(
    function(){
        ctx.fillStyle = '#ffffff';
        // Preenche o canvas com branco
        ctx.fillRect(0, 0, canvas.width, canvas.height);
        ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
        

    }
    );

    // Seleciona o botão de desenhar
    $(".js-desenhar").click( ()=>{
        if (desenhar) {
            desenhar = false
            $('.js-desenhar').removeClass('active');

        } else {
            desenhar = true
            $('.js-desenhar').addClass('active');
        }
    })
    });

    $("#js-permanentes").click(()=>{
        $('#js-permanentes').addClass('active');
        $('#js-deciduos').removeClass('active');
        $('.permanentes').slideDown();
        $('.deciduos').slideUp();

    })

    $("#js-deciduos").click(()=>{
        $('#js-deciduos').addClass('active');
        $('#js-permanentes').removeClass('active');
        $('.permanentes').slideUp();
        $('.deciduos').slideDown();

    })

</script>