<?php
ini_set("max_execution_time", "30000");
require_once("../../lib/conf.php");
require_once("../../lib/classes.php");

setcookie("infoName", $_GET['instancia'], time() + 3600*24, "/");




if($_GET['instancia'] != "angelicaeigor"){
    ?>
    
    <script> 
        alert("ATENÇÃO, INSTANCIA NÃO CONFIGURADA");
    </script>

    <h1>ATENÇÃO, INSTANCIA NÃO CONFIGURADA</h1>
    
    <?php
}

print_r($_ENV['MYSQL_DB'] . "<br>");

if(1==0){
    die();
}

$_p = "angelicaeigor.ident_";
$sql = new Mysql();
$usr = (object) array('id' => 0);
$mensagens_enviadasV2 = array();
$pacientesV2 = array();
$cont = 0;
$merge_mensagens = array();

$pacientes = file("314761_pacientes_2023_03_29.csv");

$clinica = file("314761_minhaclinica_2023_03_29.csv");
$consultas = file("314761_consultas_2023_03_29.csv");
$planos = file("314761_planos_2023_03_29.csv");
$prof = file("314761_profissionais_2023_03_29.csv");


//print_r($_ENV['MYSQL_DB'] . "<br>");
//
//echo "migracao_igor";
//$sql->consult($_p."clinica", "*", "");
//$var = mysqli_fetch_object($sql->mysqry);
//var_dump($var);

//die();
// tratando os profissionais
$sql->del($_p . "pacientes", "");
$sql->del($_p."colaboradores", "");
$sql->del($_p."pacientes_historico","");
$sql->del($_p."agenda","");

// apaga pacientes e agendamentos



echo "<h2> Iniciando profissionais </h2>";
$_profissionaisIds = [];
foreach ($prof as $linha) {
    list(
        $id_profissional,
        $celular,
        $cidade,
        $cpf,
        $cro,
        $email,
        $endereco,
        $nome,
        $rg,
        $sexo,
        $telefone,
        $uf
    ) = explode(",", str_replace("\"", "", $linha));
    $nome = trim($nome);
    $email = trim($email);

    //tabela tem o id dos profissionais
   if($email=="angelicadrago@hotmail.com") {
       $id_profissional=538517;
       $nome="Angelica Drago Marchesi Pimentel";
       continue;
   }
   else if($email=="pena.igor@gmail.com") {
       $id_profissional=501434;
       $nome="Igor Pena Andrade";
       continue;
   }

    $vSQLProfissional = "id=$id_profissional,
nome='" . utf8_decode(addslashes($nome)) . "',
cpf='" . addslashes($cpf) . "',
cidade='" . addslashes($cidade) . "',
telefone1='" . addslashes($celular) . "',
cro='" . addslashes($cro) . "',
email='" . addslashes($email) . "',
endereco='" . addslashes($endereco) . "',
rg='" . addslashes($rg) . "',
sexo='" . addslashes($sexo) . "',
telefone2='" . addslashes($telefone) . "',
estado='" . $uf . "',
check_agendamento=1";
    $sql->consult($_p . "colaboradores", "*", "where trim(nome)='" . utf8_decode($nome) . "'");
    if ($sql->rows) {
        // var_dump($vSQLProfissional);
// echo "<hr>";
        $x = mysqli_fetch_object($sql->mysqry);
        //echo $x->nome."->".$x->id."<BR>";
        $sql->update($_p . "colaboradores", $vSQLProfissional, "where id=$x->id");
        $idprofissional = $x->id;
    } else {
        // var_dump($vSQLProfissional);
//  echo "<hr>";
        $sql->add($_p . "colaboradores", $vSQLProfissional . ", data=now()");
        $idprofissional = $sql->ulid;
    }
    $_profissionaisIds[$nome] = $idprofissional;
    echo ".";
}
echo "<h3> <br> pronto <br> </h3>";


//só existem duas cadeiras no sistema. Vou inserir o id manualmente

//pegando os pacientes
//echo "pegando e formatando pacientes da lista";
echo "<h2> Iniciando pacientes </h2>";
foreach ($pacientes as $linha) {
    list(
        $id_paciente, //id universal da linha
        $data_registro,
        $nome,
        $celular,
        $cpf,
        $datanascimento,
        $email,
        $bairro,
        $cep,
        $cidade,
        $endereco,
        $numeroprontuario,
        //não sei o que fazer com esse dado
        $numeropaciente,//id do paciente
        //não sei o que fazer com esse dado
        $observacao,
        //não sei o que fazer com esse dado
        $rg,
        $sexo,
        $telefone,
        $uf,
        $complemento,
        $plano,
        //não sei o que fazer com esse dado
        $motivo_chegar_clinica,
        //não sei o que fazer com esse dado
        $titular_plano,
        //não sei o que fazer com esse dado
        $cpf_resposavel_plano,
        $numero_carteirinha,
        //não sei o que fazer com esses dois dados
        $excluido
    ) = explode(',', str_replace("\"", "", $linha));

    //endereço
    if (!empty($bairro))
        $endereco .= ", $bairro";
    if (!empty($cidade))
        $endereco .= ", $cidade";
  //  $datanascimento = strtotime($datanascimento);
    $nome = utf8_decode(trim($nome));
    if (empty($celular)) {
        $celular = $telefone;
    } else {
        $celular = 0;
    }

    //inicialmente eu ia usar o nome + id do paciente, mas no agendamento apenas o nome do paciente é usado como identificador
    $index = strtolowerWLIB(str_replace(" ", "", tirarAcentos($nome)));

    //verificando por nomes repetidos
    if (isset($_pacientes[$index])) {
        //    echo "<BR>=" . $index . " $nome<BR>";

        //escolhendo apenas os pacientes mais recentes
        if ($_pacientes[$index]['id_paciente'] > $numeropaciente) {
            //      echo "escolhido já existente";
            //      echo "<hr>";
            continue;
        }


        //TODO: adicionar um método que constroi um novo paciente, usando as informações já existentes e não existentes. 
        // echo "<hr>";
    }

    $_pacientes[$index] = array(
        'id_paciente' => $numeropaciente,
        'lixo' => ($excluido) == 'f' ? 1 : 0,
        'data' => !empty($data_registro) ? $data_registro : "now",
        'nome' => $nome,
        'telefone' => telefone($telefone),
        'celular' => telefone($celular),
        'email' => $email,
        'dn' => $datanascimento,
        'endereco' => $endereco,
        'complemento' => $complemento,
        'cep' => $cep,
        'cpf' => $cpf,
        'rg' => $rg,
        'rg_uf' => $uf,
        'sexo' => $sexo,
        'responsavel_possui' => !empty($cpf_resposavel_plano) ? 0 : 1,
        'responsavel_cpf' => !empty($cpf_resposavel_plano) ? 0 : $cpf_resposavel_plano,
        'agenda' => array(),
        'mensagens' => array()
    );
    echo $datanascimento;
    echo ".";
    // echo $index . "->" . $nome . "->" . $telefone . " -> " . $data_registro . " -> " . $id_paciente . "<br />";
    //  print_r($_pacientes);
    // $sql->add($_p."pacientes",$_pacientes);
}
echo "<h3> <br> pronto <br> </h3>";

//verificando anomalias nos dados da agenda
if (1 == 1) {
    $consultasV2 = array();
    $cont = 0;
    foreach ($consultas as $linha) {
        $linha = trim($linha);
        if (empty($linha))
            continue; //para caso tenha alguma quebra de linha
        $aux = explode(",", $linha);
        //	echo (count($aux)!=7?1:0)." >"; continue;
        if (count($aux) == 7) {
            $consultasV2[$cont] = $linha;
        } else {
            print_r($linha);
            echo "<br />" . $cont . "<br />";
            die();
        }
        $cont++;
    }
}

echo "<h2> Iniciando agenda </h2>";
//recuperando dados da agenda
foreach ($consultas as $linha) {

    list(
        $data,
        $paciente,
        $profissional,
        $status,
        $tempoestimado,
        $descricao,
        $data_retorno_alerta
    ) = explode(',', str_replace("\"", "", $linha));

    $paciente = utf8_decode(trim($paciente));
    $profissional = trim($profissional);
    $status = trim(str_replace("\"", "", $status));
    $index = strtolowerWLIB(str_replace(["\"", " "], "", tirarAcentos($paciente)));

    /**
     * o ideal seria criar uma cadeira lixo para os outros funcionarios. Por padrão eu vou jogar os pacientes que foram
     * atendidos por um profissional sem cadeira para a cadeira do igor
     */
    if (strpos($profissional, "Angelica")) {
        $idCadeira = 2;
    } else {
        $idCadeira = 1;
    }


    $idProfissional = '';
    if (isset($_profissionaisIds[$profissional]))
        $idProfissional = "," . $_profissionaisIds[$profissional] . ",";

    //echo $profissional."->".$idProfissional."<BR>";


    // define status
    $idStatus = "1";
    if ($status == "AGENDADA")
        $idStatus = 2;
    else if ($status == "CONFIRMADA_SMS" || $status == "CONFIRMADA")
        $idStatus = 2;
    else if ($status == "FALTA")
        $idStatus = 3;
    else if ($status == "CANCELADA_PACIENTE" || $status == "CANCELADA_SMS")
        $idStatus = 4;
    else {
        echo $data;
        echo "<br \>";
        echo $status;
        die();
    }

    //verificando se em alguma consulta não existe paciente cadastrado
    if (!isset($_pacientes[$index])) {
        echo "<br \>" . $index . " :: " .$nome        ."<br \>";
        die();
    }

    //construindo a agenda de cada paciente
    $agenda = array(
        'data' => $data,
        'profissional' => $idProfissional,
        'duracao' => $tempoestimado,
        'id_paciente' => $_pacientes[$index]['id_paciente'],
        'id_status' => $idStatus,
        'id_cadeira' => $idCadeira,
        'obs' => $descricao
    );

    $_pacientes[$index]['agenda'][] = $agenda;
    echo ".";

    // echo $index ." -> ". $paciente ." -> ". $profissional ."<br \>";  
}
echo "<h3> <br> pronto <br> </h3>";


echo "<h2> Iniciando cadastro de usuarios no banco </h2>";
$cont = 0;
foreach ($_pacientes as $x) {
    $cont++;
    #cadastrando pacientes no sistema
    $idPaciente = $x['id_paciente'];
    $vSQLPaciente = "data='" . $x['data'] . "',
                        nome='" . addslashes(($x['nome'])) . "',
                        telefone1='" . telefone($x['celular']) . "',
                        telefone2='" . telefone($x['telefone']) . "',
                        sexo='" . addslashes($x['sexo']) . "',
                        cpf='" . addslashes($x['cpf']) . "',
                        rg='" . addslashes($x['rg']) . "',
                        data_nascimento='" . ($x['dn']) . "',
                        endereco='" . addslashes(utf8_decode($x['endereco'])) . "',
                        email='" . utf8_decode($x['email']) . "',
                        cep='" . addslashes($x['cep']) . "',
                        rg_uf='" . $x['rg_uf'] . "',
                        complemento='" . $x['complemento'] . "',
                        responsavel_possui='" . $x['responsavel_possui'] . "',
                        responsavel_cpf='" . $x['responsavel_cpf'] . "',
                        lixo='" . $x['lixo'] . "'";

    $sql->add($_p . "pacientes", $vSQLPaciente);
    $id_paciente = $sql->ulid;

    #cadastra agendamentos
    if (isset($x['agenda'])) {
        foreach ($x['agenda'] as $a) {

            $data = new DateTime($a['data']);
            $data->add(new DateInterval('PT' . ($a['duracao']) . 'M'));

            $vSQL = "data=now(), 
                id_paciente='" . $id_paciente . "',
                profissionais='" . $a['profissional'] . "',
                id_cadeira='" . $a['id_cadeira'] . "',
                id_unidade=1,
                id_status='" . $a['id_status'] . "',
                agenda_data='" . $data->format("Y-m-d H:i:s") . "',
                agenda_data_original='" . $a['data'] . "',
                agenda_data_final='" . date('Y-m-d H:i:s', strtotime($a['data'] . " + " . $a['duracao'] . " minutes")) . "',
                agenda_duracao='" . $a['duracao'] . "',
                obs='" . (utf8_decode($a['obs'])) . "'";
        }
        $sql->add($_p . "agenda", $vSQL);
        $id_agenda = $sql->ulid;
        $sql->add($_p . "log", "id_usuario='" . $usr->id . "',tipo='insert',vsql='" . addslashes($vSQL) . "',tabela='" . $_p . "agenda',id_reg='" . $id_agenda . "'");

        //populando histórico de cada paciente
        $vSQLHistorico = "data=now(),
            id_usuario=$usr->id,
            evento='agendaNovo',
            id_paciente=" . $id_paciente . ",
            id_agenda=$id_agenda,
            id_status_antigo=0,
            id_status_novo=" . $a['id_status'] . ",
            descricao=''";
        $sql->add($_p . "pacientes_historico", $vSQLHistorico);
    }
    //echo $cont . "<br \>";
    echo ".";
}

echo "<br \>terminado";