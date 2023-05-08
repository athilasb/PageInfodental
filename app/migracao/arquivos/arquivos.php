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

$_p = "excellenceatelieoral.ident_";
$dir_arquivos = "arqs/pacientes/arquivos/";

//pegando todos os pacientes do sistema, preciso saber qual arquivo pentence a quem
//$sql->consult($_p."pacientes", "id, nome", "");
//$list_pacientes = mysqli_fetch_all($sql->mysqry, MYSQLI_ASSOC);

//vendo se já não existe um arquivo cadastrado no sistema
$sql->consult($_p.'pacientes_arquivos', "max(id) as id", "");
$max_id = mysqli_fetch_object($sql->mysqry)->id;
$max_id++;


$arquivos = file("arquivos.csv");
$pacientes = file("pacientes_pessoa.csv");
$list_arquivos = array();
$list_pacientes = array();
//id;id_pessoa;id_documento;tx_nome_pessoa;tx_nome_documento;tx_nome_arquivo;tx_url_arquivo;tx_descricao;tx_tipo_arquivo;tx_formato_arquivo

foreach($pacientes as $linha)
{
    list($id_paciente, $id_pessoa) = explode(";", $linha);
    $list_pacientes[trim($id_pessoa)] = trim($id_paciente); 
}


//var_dump($list_pacientes);
//die();
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
        
    list($nome_sem_extensao, $extensao) = explode('.', $tx_nome_arquivo);
    //    echo $extensao;

    $isImage = 0;
    if($tx_formato_arquivo == "Imagem"){
        $isImage = 1;
    }

    if($id_pessoa == "NULL"){
        continue;
    }

    $list_arquivos[$max_id] = array(
        'id' => $max_id,
        'id_pessoa' => $id_pessoa,
        'tx_nome_pessoa' => $tx_nome_pessoa,
        'tx_nome_documento' => utf8_decode($tx_nome_documento),
        'tx_nome_arquivo' => utf8_decode($nome_sem_extensao),
        'tx_url_arquivo' => $tx_url_arquivo,
        'tx_descricao' => utf8_decode($tx_descricao),
        'tx_tipo_arquivo' => $tx_tipo_arquivo,
        'tx_formato_arquivo' => $tx_formato_arquivo,
        'id_paciente' => $list_pacientes[$id_pessoa],
        'extensao' => $extensao,
        'isImage' => $isImage
    );
    $max_id++;
}

//montando o sql e guardando os ids 
$vSQLabels = "id, data, id_paciente, tipo, titulo, extensao, id_colaborador, obs, lixo";

$vSQL = "";
foreach($list_arquivos as $arquivos){

    $vSQL .= "( '". $arquivos['id'] ."', 
                    now(), '" . 
                    $arquivos['id_paciente'] . "', '" . 
                    $arquivos['tx_tipo_arquivo'] . "', '" . 
                    $arquivos['tx_nome_documento'] . "', '" .
                    $arquivos['extensao'] . "', " . 
                    "'101', ".
                    "'', ".
                    "'0'), ";


//echo("Nome: " . $arquivos['tx_nome_pessoa']. " id paciente: " . $arquivos['id_paciente'] . "id_pessoa: " . $arquivos['id_pessoa'] ."<br>");
}
$vSQL = substr($vSQL, 0, -2);
//print_r($vSQL);
//$sql->insertMultiple($_p . "pacientes_arquivos", $vSQLabels, $vSQL);


$i = 0;

foreach($list_arquivos as $arquivos){

    $url = $arquivos['tx_url_arquivo'];
    $stream = fopen($url, "rb");
    $file = stream_get_contents($stream);
    $size = strlen($file);
    $nome_arquivo = md5($arquivos['id']) . "." . $arquivos['extensao'];
  
    echo("Nome: " . $arquivos['tx_nome_pessoa']. " nome_hash:" . md5($arquivos['id'])  . " id paciente: " . $arquivos['id_paciente'] . " id_pessoa: " . $arquivos['id_pessoa'] . " id: " . $arquivos['id'] ."<br>");

    //print_r($size);
    try {
        $s3->putObject(array(
            'Bucket'=>'infodental',
            'Key'   =>  "agenda/walker_testes/". $dir_arquivos . $nome_arquivo,
            'Body'  => $file,
        //    'ContentLength' => $size,
            'ACL'   => 'public-read', //for public access
        ));
    } catch (Exception $exception) {
        echo "<h1>Failed to upload  with error: " . $exception->getMessage() . "</h1>";
        exit("Please fix error with file upload before continuing.");
    }

    if($i == 5){
        die();
    }
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
