<?php
    require_once '../lib/classes.php';
    require_once '../lib/conf.php';
    $sql = new Mysql();

    $values=array();
    $campos=explode(",", "nome,rg,cpf,telefone1,telefone2,email,data_nascimento,estado_civil,musica,endereco,complemento,instagram");
    foreach($campos as $v) {
        $values[$v]='';
    }

    $paciente='';
    if(isset($_GET['id_paciente']) and is_numeric($_GET['id_paciente'])) {
        $sql->consult($_p."pacientes","*","WHERE id='".addslashes($_GET['id_paciente'])."'");
        if($sql->rows) {
            $paciente=mysqli_fetch_object($sql->mysqry);

            foreach($campos as $v) {
                if(isset($paciente->$v)) {

                    if($v=="data_nascimento")
                        $values[$v]=date('d/m/Y', strtotime($paciente->$v));
                    else
                        $values[$v]=utf8_encode($paciente->$v);
                }
            }
        }
    }
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro Pacientes</title>
    <link rel="stylesheet" href="style.css?v5">
    <script src="https://code.iconify.design/3/3.1.0/iconify.min.js"></script>
    <script type="text/javascript" src="../js/jquery.js?v3.6.4"></script>
    <script type="text/javascript" src="../js/jquery.inputmask.js"></script>
    <script type="text/javascript" src="../js/jquery.sweetalert.js"></script>
</head>
<body>
    <script>
        $(function(){
            $("input.data").inputmask("99/99/9999");
            $("input.telefone").inputmask("(99) 9999-9999");
            $("input.celular").inputmask("(99) 99999999[9]");
            $("input.cpf").inputmask("999.999.999-99");

            $('.js-proximo').click(function(){
                let alertar = false;
                let etapa = $(this).attr('data-etapa');
                let nome = $('.js-nome').val();
                let celular = $('.js-celular').val();

                if(etapa) {
                    if(etapa==6) {

                        if(!nome) {
                            alertar = true;
                            $('.js-nome').addClass('erro');
                        } 
                    } else if(etapa==7) {

                        if(!celular) {
                            alertar = true;
                            $('.js-celular').addClass('erro');
                        } 
                    } else if(etapa==9) {
                    }

                    if(alertar) {
                        swal({title: "Erro!", text: "Complete os campos destacados...", type:"error", confirmButtonColor: "#424242"});
                        return false;
                    } else {
                        var target_offset = $(`.js-etapa-${etapa}`).offset();
                        var target_top = target_offset.top;
                        $('html, body').animate({ scrollTop: target_top }, 'slow');
                    }
                    
                }
            });
        });
    </script>
    <section>
        <div class="page-cadastro colum-form page-1-background">
            <div>
                <img src="./img/info-dental.png" alt="">
            </div>
            <div class="section-buttom">
                <h1 class="titulo-bem-vindo">Bem-vindo ao Studio Dental</h1>
                <div class="sub-bem-vindo">Para sua segurança e para ter uma melhor experiência no seu tratamento, insira algumas informações importantes sobre você!  </div>
                <a class="buttom-1 js-proximo" href="javascript:;" data-etapa="2">Continuar</a>
            </div>
        </div>
    </section>

    <section class="js-etapa-2">
        <div class="page-cadastro colum-form">
            <div>
                <div class="barra-progresso"> 
                    <span class="progresso" style="width: 10%;"></span>
                </div>
                <h1 class="titulo">Vamos configurar sua ficha! rimeiro, insira ou tire uma foto sua</h1>
                <div class="display-flex-center">
                    <div class="border-icon"><span class="iconify" data-icon="fluent:image-multiple-20-regular" style="color: #cdcdcd;" data-width="50" data-height="50"></span></div>
                    <div class="border-icon"><span class="iconify" data-icon="fluent:camera-add-20-regular" style="color: #cdcdcd;" data-width="50" data-height="50"></span></div>
                </div>
            </div>
            <div class="section-buttom">
                <a class="buttom-2 js-proximo" href="javascript:;" data-etapa="3">Continuar</a>
            </div>
        </div>
    </section>
    
    <section class="js-etapa-3">
        <div class="page-cadastro colum-form can-background">
            <div class="margin-top">
                <img src="./img/Ellipse.png" alt="">
            </div>
            <div class="section-buttom ">
                <a class="buttom-3 js-proximo" href="javascript:;" data-etapa="4">Tirar foto</a>
            </div>
        </div>
    </section>

    <section class="js-etapa-4">
        <div class="page-cadastro colum-form can-background">
            <div class="margin-top">
                <img src="./img/Ellipse.png" alt="">
            </div>
            <div class="section-buttom ">
                <a class="buttom-3" style="margin-bottom: 15px; border-color:#FECEA2;" href="javascript:;">Tentar novamente</a>
                <a class="buttom-3 js-proximo" href="javascript:;" data-etapa="5">Continuar</a>
            </div>
        </div>
    </section>

    <section class="js-etapa-5">
        <div class="page-cadastro colum-form">
            <div>
                <div class="barra-progresso"> 
                    <span class="progresso" style="width: 30%;"></span>
                </div>
                <h1 class="titulo">Complete suas informações principais</h1>
                <div>
                    <div class="input-unico">
                        <input type="text" class="js-nome" placeholder="Digite seu nome completo" value="<?php echo $values['nome'];?>" />
                    </div>
                    <div class="input-unico">
                        <input type="text" placeholder="RG" value="<?php echo $values['rg'];?>">
                    </div>     
                    <div class="input-unico">
                        <input type="text" class="data" placeholder="Data Nascimento" value="<?php echo $values['data_nascimento'];?>">
                    </div> 
                    <div class="input-unico">
                        <input type="text" class="cpf" placeholder="CPF" value="<?php echo $values['cpf'];?>">
                    </div> 
                     <div class="input-unico">
                        <input type="text" placeholder="Estado Civil" value="<?php echo $values['estado_civil'];?>">
                    </div>
                </div>
            </div>
        </div> 
            <div class="section-buttom">
                <a class="buttom-2 js-proximo" href="javascript:;" data-etapa="6" style="background-color: var(--verde) !important;">Continuar</a>
            </div>
        </div>
    </section>

    <section class="js-etapa-6">
        <div class="page-cadastro colum-form">
            <div>
                <div class="barra-progresso"> 
                    <span class="progresso" style="width: 50%;"></span>
                </div>
                <h1 class="titulo">Só mais algumas informações</h1>
                <div>
                    <div class="input-unico">
                        <input type="text" placeholder="Endereço" value="<?php echo $values['endereco'];?>">
                    </div>
                    <div class="input-unico">
                        <input type="text" placeholder="Complemento" value="<?php echo $values['complemento'];?>">
                    </div>    
                    <div class="input-unico">
                        <input type="text" class="js-celular celular" placeholder="Celular" value="<?php echo $values['telefone1'];?>">
                    </div> 
                    <div class="input-unico">
                        <input type="text" class="telefone" placeholder="Telefone" value="<?php echo $values['telefone2'];?>">
                    </div> 
                    <div class="input-unico">
                        <input type="text" placeholder="E-mail" value="<?php echo $values['email'];?>">
                    </div> 
                </div>
            </div>
        </div> 
            <div class="section-buttom">
                <a class="buttom-2 js-proximo" href="javascript:;" data-etapa="7" style="background-color: var(--verde) !important;">Continuar</a>
            </div>
        </div>
    </section>

    <section class="js-etapa-7">
        <div class="page-cadastro colum-form">
            <div>
                <div class="barra-progresso"> 
                    <span class="progresso" style="width: 75%;"></span>
                </div>
                <h1 class="titulo">Já estamos acabando</h1>
                <div>
                    <div class="input-unico">
                        <input type="text" placeholder="Tipo sanguineo">
                    </div>
                    <div class="input-unico">
                        <input type="tel" placeholder="Peso">
                    </div>    
                    <div class="input-unico">
                        <input type="tel" placeholder="Altura">
                    </div> 
                </div>
            </div>
        </div> 
            <div class="section-buttom">
                <a class="buttom-2 js-proximo" href="javascript:;" data-etapa="8" style="background-color: var(--verde) !important;">Continuar</a>
            </div>
        </div>
    </section>

    <section class="js-etapa-8">
        <div class="page-cadastro colum-form">
            <div>
                <div class="barra-progresso"> 
                    <span class="progresso" style="width: 75%;"></span>
                </div>
                <h1 class="titulo">Para finalizar seu cadastro</h1>
                <div>
                    <div class="input-unico">
                        <input type="text" placeholder="Preferência musical" value="<?php echo $values['musica'];?>">
                    </div>
                    <div class="input-unico">
                        <input type="text" placeholder="Instagram" value="<?php echo $values['instagram'];?>">
                    </div>    
                </div>
            </div>
        </div> 
            <div class="section-buttom">
                <a class="buttom-2 js-proximo" href="javascript:;" data-etapa="9" style="background-color: var(--verde) !important;">Finalizar</a>
            </div>
        </div>
    </section>

    <section class="js-etapa-9">
        <div class="page-cadastro colum-form1">
            <div>
                <h1 class="titulo">Seu cadastro foi criado com sucesso!</h1>
                <div><span class="iconify" data-icon="fluent:checkmark-circle-20-regular" style="color: #15b64f;" data-width="100"></span></div>
            </div>
            <div>Obrigado por dedicar um tempo e inserir suas informações, seu cadastro foi criado com sucesso.</div>
    </section>

</body>
</html>