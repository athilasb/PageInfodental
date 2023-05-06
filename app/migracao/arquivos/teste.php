<?php 

require_once("../../lib/classes.php");
require_once("../../lib/conf.php");
$file = "https://app.easydentalcloud.com.br/wsefs/service.svc/download?file=015807AB-5B4E-45B7-B0D3-56AD0781CEA2";

$stream = fopen($file, "rb");

$i = 0;

if(isset($_GET['caixaSearch'])  ){
    $i++;
}
?>


<!DOCTYPE html>
    <head>
        <title>Pesquisa de colaboradores</title>
        <!-- <link rel='stylesheet' type='text/css' href=''/> -->
        <script>

        </script>
    </head>
    <body>
        <p1>HISTORICO DE COLABORADORES</p1>
        <div>
            <form method="GET" action=" ">
            <input type="text" name="caixaSearch" placeholder="Insira o aqui!">
            
            <button class="botao-pesquisa" onclick=''>Migrar?</button>
            </form>
        </div>
        <?php echo $i;?>
    </body>
</html>
