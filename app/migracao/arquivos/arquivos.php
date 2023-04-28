<?php
require_once("../../lib/classes.php");
require_once("../../lib/conf.php");
require_once("../../lib/class/classMysql.php");
require_once("../../vendor/autoload.php");



use Aws\S3\S3Client;
$s3 = new S3Client([
    'version' => 'latest',
    'endpoint' => $_scalewayS3endpoint,
    'region'  => $_scalewayS3Region,
    'credentials' => 
    [
        'key' => $_scalewayAccessKey,
        'secret' => $_scalewaySecretKey
    ],
    'bucket_endpoint' => true
]);



$sql = new Mysql();



$_p = "infodental.ident_";

//pegando todos os pacientes do sistema, preciso saber qual arquivo pentence a quem
$sql->consult($_p."pacientes", "id, nome", "");
$list_pacientes = mysqli_fetch_all($sql->mysqry, MYSQLI_ASSOC);

//vendo se já não existe um arquivo cadastrado no sistema
$sql->consult($_p.'pacientes_arquivos', "max(id) as id", "");
$max_id = mysqli_fetch_object($sql->mysqry)->id;
$max_id++;


$arquivos = file("arquivos.csv");
$list_arquivos = array();
//id;id_pessoa;id_documento;tx_nome_pessoa;tx_nome_documento;tx_nome_arquivo;tx_url_arquivo;tx_descricao;tx_tipo_arquivo;tx_formato_arquivo


foreach($arquivos as $linha){
          
    list(
        $id,
        $id_pessoa,
        $id_documento,
        $tx_nome_pessoa,
        $tx_nome_documento,
        $tx_nome_arquivo,
        $tx_url_arquivo,
        $tx_descricao,
        $tx_tipo_arquivo,
        $tx_formato_arquivo
    ) = explode(';', $linha);
             
    $tx_nome_pessoa = trim($tx_nome_pessoa);
    $index = strtolowerWLIB(str_replace(" ", "", tirarAcentos($tx_nome_pessoa)));

        
    list(, $extensao) = explode('.', $tx_nome_arquivo);
    //    echo $extensao;

        $isImage = 0;
    if($tx_formato_arquivo == "Imagem"){
        $isImage = 1;
    }

    $list_arquivos[$max_id] = array(
        'id' => $max_id,
        'tx_nome_pessoa' => $tx_nome_pessoa,
        'to_nome_documento' => $tx_nome_documento,
        'tx_nome_arquivo' => $tx_nome_arquivo,
        'tx_url_arquivo' => $tx_url_arquivo,
        'tx_descricao' => $tx_descricao,
        'tx_tipo_arquivo' => $tx_tipo_arquivo,
        'tx_formato_arquivo' => $tx_formato_arquivo,
        'extensao' => $extensao,
        'isImage' => $isImage
    );
   // echo "[$max_id] | <br>";
    $max_id++;
}


if(isset($_GET['fim']) && 
   isset($_GET['inicio']) &&
   ($_GET['fim'] != '') &&
   ($_GET['inicio'] != '')
   ){

//montando o sql e guardando os ids 
//print_r($list_pacientes);
    $vSQL = "id, data, id_paciente, tipo, titulo, extensao, id_colaborador, obs, lixo";

    //echo "??";
    $i = $_GET['inicio'];
    $j = $_GET['fim'];

    for(;$i <= $j; $i++){
        //echo "mmm";
        $linha = $list_pacientes[$i];
    //    print_r($linha);

        //pesquisando por nomes na lista de pacientes do bacno
        $index = strtolowerWLIB(str_replace(" ", "", tirarAcentos(utf8_encode($linha['nome']))));

        if($linha['nome'] == "" || !isset($linha[$index])){
            continue;
        }

        echo("<br> =============================================================<br>");
        echo $linha['nome']."::::::::::::::::::<br>";
        print_r($list_arquivos[$index]);
        echo("<br>-------------------------------------------------------------<br>");
        print_r($linha);
        echo("<br> +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++");

    
        $vSQL .= "( '". $list_arquivos[$index]['id'] ."', 
                        now(), '" . 
                        $linha['id'] . "', '" . 
                        $list_arquivos[$index]['tx_tipo_arquivo'] . "', '" . 
                        $list_arquivos[$index]['tx_nome_arquivo'] . "', '" .
                        $list_arquivos[$index]['extensao'] . "', '" . 
                    // verificar se esse é o id do profissional    "'101', ".
                        "'',".
                        "'0')";
    }
}

if(isset($_GET['image'])){
    $url = $_GET['image'];
    $stream = fopen($url, "rb");
    $file = stream_get_contents($stream);
    $size = strlen($file);
    echo $stream;
    echo $size;
    //print_r($size);
    try {
        $s3->putObject(array(
            'Bucket'=>'infodental',
            'Key' =>  "agenda/arqs/pacientes/arquivos/imgem.webp",
            'Body' => $file,
        //    'ContentLength' => $size,
            'ACL'    => 'public-read', //for public access
        ));
    } catch (Exception $exception) {
        echo "<h1>Failed to upload  with error: " . $exception->getMessage() . "</h1>";
        exit("Please fix error with file upload before continuing.");
    }
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
        <p1>...</p1>
        <div>
            <form method="GET" action=" ">
                <input type="text" name="inicio" placeholder="Insira o inicio aqui!">
                <input type="text" name="fim" placeholder="Insira o fim aqui!">
                <input type="text" name="image" placeholder="imagem_teste">


                <button class="botao-pesquisa" onclick=''>Migrar?</button>
            </form>
        </div>
    </body>
</html>








