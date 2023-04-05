
<div class="box" style="background-color: #ebebeb;">
    <section class="assinatura">
        <div style="background-color: white; border-radius: 5px; padding-left: 5%">
            <p><span> Atenção, <strong> insira o cpf e a data de nascimento </strong> de
                    <?php echo $paciente->nome; ?>. Opcionalmente, <strong> insira uma assinatura eletrônica válida
                    </strong>. Por ultimo, clique em <strong>"aceitar"</strong>
                </span> </p>
        </div>

        <form method="post" class="formulario-validacao" id="classe">
            <input type="hidden" name="acao" value="conf" />
            <div><label>CPF </label><input id="cpf" type="text" name="cpf"></input> </div>
            <div><label>Data Nascimento </label><input class="data" id="data" type="text" name="data_nascimento" />
            </div>
        </form>
        <div class="item">

            <section class="" style="dysplay:flex">
                <div class="filter-group"></div>
                <div class="filter-group">
                    <div class="filter-form form">
                        <dl>
                            <dd>
                                <a href="javascript:enviar();" data-loading="<?php echo $dock_status; ?>" data-aside="prontuario-opcoes" class="button button_main">
                                    <i class="iconify"
                                        data-icon="line-md:circle-to-confirm-circle-transition"></i><span>Enviar</span>
                                </a>
                            </dd>
                        </dl>
                    </div>
                </div>

                <div class="filter-group"></div>
                <div class="filter-group">
                    <div class="filter-form form">
                        <dl>
                            <dd>
                                <a href="javascript:;" id="canvas-clear" data-aside="prontuario-opcoes"
                                    class="button button_main">
                                    <i class="iconify" data-icon="carbon:erase"></i><span>Apagar</span>
                                </a>
                            </dd>
                        </dl>
                    </div>
                </div>
                <div class="filter-group"></div>
            </section>

            <div class="canvas_container" id="canvas_container" style="">
                <canvas id="canvas" style="width: 100%;">
                    <p> painel de assinatura </p>
                </canvas>
            </div>
        </div>
</div>

<script>
    const canvas = $('#canvas')[0];
    const container = $(".canvas_container")[0];
    const ctx = canvas.getContext('2d');
    let pressed = false;
    ctx.lineWidth = 2;
    ctx.lineCap = 'round';

    //calculando a posição do mouse relativo ao bitmap do canvas
    //https://stackoverflow.com/questions/17130395/real-mouse-position-in-canvas/17130415#17130415
    function getmouse(evt) {
        var rect = canvas.getBoundingClientRect();
        var scalex = canvas.width / rect.width;
        var scaley = canvas.height / rect.height;
        return {
            x: (evt.clientX - rect.left) * scalex,
            y: (evt.clientY - rect.top) * scaley
        };
    }

    function draw(e) {
        if (!pressed) { return; }
        ctx.lineWidth = 2;
        ctx.lineCap = 'round';
        var positions = getmouse(e);
        ctx.lineTo(positions.x, positions.y);
        ctx.stroke();
    }


    
    //para mobile
    canvas.addEventListener("touchmove", (e)=>{
        e.preventDefault();
        console.log(`e.touches[0].clientX: ${e.touches[0].clientX}
        e.touches[0].clientY: ${e.touches[0].clientY}`);
        draw(e.touches[0]);
    });
    canvas.addEventListener("touchstart", (e) => {
        e.preventDefault(); //impedir o envento de scrool 
        ctx.beginPath();
        pressed = true;
    });
    canvas.addEventListener("touchend", (e) => {
        pressed = false;
        ctx.stroke();
    });

    //encontar uma forma de parar de desenhar quando o usuário inicia o desenho mas sai da area do canvas (enquanto o botão ainda está pressionado);
    canvas.addEventListener("mousemove", draw);
    canvas.addEventListener("mousedown", () => {
        ctx.beginPath();
        pressed = true;
    });
    canvas.addEventListener("mouseup", (e) => {
        pressed = false;
        ctx.stroke();
    });
    document.getElementById("canvas-clear").addEventListener("click", () => {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
    });

</script>
<script>
    if($(".button.button_main").attr('data-loading')==2){
        alert("Esse documento já foi assinado");
    }
    
    function enviar() {
        if( $(".button.button_main").attr('data-loading')==0){

            let cpf;
            let data;
            let aux =  $('#data')[0].value;
            $(".button.button_main").attr('data-loading', 1);

            aux = aux.split('/');     
            if( aux.length != 3){
                alert("campo data está vazio ou incompleto");
                alert(aux.length);
            }

            cpf = $('#cpf')[0].value.replaceAll('.', '').replace('-', '');
            data = aux[2] + '-' + aux[1] + '-' + aux[0];

            if(cpf == ''){
                alert('campo cpf vazio');
                return;
            }

            navigator.geolocation.getCurrentPosition(
                (pos) => {
                    $.ajax({
                        type: "POST",
                        data:{
                            'conf': true,
                            'cpf_ent': cpf,
                            'data': data,
                            'canvas-url': canvas.toDataURL('image/png'),
                            'latitude': pos.coords.latitude,
                            'longitude': pos.coords.longitude,
                            'aprox': pos.coords.accuracy,
                            'user_agent': navigator.userAgent
                        },
                        async: true,
                        dataType: 'JSON',
                        success: function (rnt) {
                            
                            console.log(rnt);
                            if(rnt.status=="success")   {
                                Swal.fire({title: "Sucesso!", text: rnt.message, type:"success", confirmButtonColor: "#424242"});
                                $(".button.button_main").attr('data-loading', 2);

                            }else{
                                Swal.fire({title: "Erro!", text: rnt.message, type:"error", confirmButtonColor: "#424242"});
                            }
                        },
                    });
                },
                (err) => {
                // swal();
                console.log(`ERROR(${err.code}): ${err.message}`);
                if(err.code ==1){
                    alert("Atenção; Você precisa concordar com a coleta da localização");
                }else{
                    alert("Erro; Algum erro desconhecido foi encontrado");
                }
                },
                {
                    enableHighAccuracy: true, 
                    timeout: Infinity,
                    maximumAge: 0 
                }
            );
        }else if($(".button.button_main").attr('data-loading')==2){
            alert("Esse documento já foi assinado");
        }else{
            alert("Assinatura está sendo processada");
        }
    }

</script>