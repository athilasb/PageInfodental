<?php

//OBS: não esqueça de procurar por ";" em lugares inválidos do sistema

set_time_limit(0);

require_once("../../lib/conf.php");
require_once("../../lib/classes.php");

$arq_paciente_dados = file("arqs/pacientes_original.csv");



$arq_agenda = file("arqs/agendamentos_original.csv");


$_paciente = array();
$_funcionarios = array();
$_funcionarios_id = array();

$_p = "infodental.ident_";
$sql = new Mysql();
print_r("Banco de dados: ". $_ENV['MYSQL_DB'] . "<br>");


//migracao/excellenceatelieoral/excellenceatelieoral.php

//$sql->del($_p."colaboradores", "");
$sql->del($_p."agenda", "");
$sql->del($_p."pacientes", "");

//funcionarios
echo "Adicionando colaboradores na tabela:";
echo "<br>Configurando dados dos pacientes:";
$num_colunas = count(explode(";", $arq_paciente_dados[0]));
//echo "<h1> " . count($arq_paciente_dados) . "</h1>";

$num_colunas = count(explode(";", $arq_paciente_dados[0]));
//echo "<h1> " . count($arq_paciente_dados) . "</h1>";

$id_novo = 0;

for($i = 1; $i < count($arq_paciente_dados); $i++ ){
    $cpf;
    
    $linha = explode(";", $arq_paciente_dados[$i]);
    if($num_colunas != count($linha)){
        echo "<h1> ERRO: uma das tabelas está formatada de forma errada <h1><br>";
        echo "<h2>linha: $i <h2>";
        die();
    }


    list (
        $situacao,
        $numcadastro,
        $primeironome,
        $sobrenome,
        $anotacoes,
        $cpf,
        $rg,
        $sexo,
        $email,
        $telefone1,
        $telefone2,
        $telcom1,
        $telcom2,
        $celular,
        $enderecores,
        $bairrores,
        $numero,
        $complemento,
        $cidaderes,
        $ufres,
        $cepres,
        $enderecocom,
        $bairrocom,
        $cidadecom,
        $ufcom,
        $cepcom,
        $convenionum,
        $datacadastro,
        $nascimento,
        $nomepai,
        $profpai,
        $nomemae,
        $profmae,
        $indicadornum,
        $responsavelnum,
        $profissaonum,
        $empresanum,
        $localnascimento,
        $matricula,
        $estadocivil,
        $dentistanum
    ) = $linha; 

    $telefones = 0;
    if($celular == ""){
        if($telefone1 != ""){
            $celular = $telefone1;
        }
        if($telefone2 != ""){
            $telefones .= $telefone2;
        }
    }else{
        $celular = str_replace(["(", ")", "-", " "], "", $celular);
        if($telefone1 != ""){
            $telefones = str_replace(["(", ")", "-", " "], "", $telefone1);
        }
        if($telefone2 != ""){
            $telefones .= ", " . str_replace(["(", ")", "-", " "], "", $telefone2);
        }
    }

    $enderecores .= $numero . ", " . $bairrores . ", " . $cidaderes . ", " . $ufres;
    $cepres = str_replace(["-", " "], "", $cepres);
      

    $estadocivil = trim($estadocivil);
    if($estadocivil == ""){
        $estadocivil = $dentistanum;
    }
    switch($estadocivil){
        case 's':
            $estadocivil = "solteiro(a)";
            break;
        case 'd':
            $estadocivil = "divorciado(a)";
            break;
        case 'c':
            $estadocivil = "casado(a)";
            break;
        case 'v':
            $estadocivil = "viuvo(a)";
            break;
        default:
            //print_r($linha);
            //echo "<h3> '$estadocivil' </h3>";
            $estadocivil = 'solteiro(a)';
           // echo "erro no estado civil";
            
    }

    if($sexo == 'f'){
        $sexo = 'F';    
    }else{
        $sexo = 'M';
    }

    $nome_responsavel;
    if($nomemae != ""){
        $nome_responsavel = $nomemae;
    }else{
        $nome_responsavel = $nomepai;
    }
       
    $index = strtolowerWLIB(str_replace(" ", "", tirarAcentos($primeironome)));

    $_paciente[$index] = array(
        
        'data' => $datacadastro,
        'id_paciente' => $id_novo,
        'nome' => utf8_decode($primeironome),
        'telefone1' => $celular,
        'telefone2' => $telefone2,
        'sexo' => $sexo,
        'cpf' => ($cpf != "") ? $cpf : "", 
        'estado_civil' => $estadocivil,
        'rg' =>  str_replace( [".", " ", "x"], "", $rg),
        'data_nascimento' => $nascimento,
        'profissao' => "",
        'lixo' => "0",
        'cep' => $cepres,  
        'endereco' => utf8_decode($enderecores),
        'numero' => $numero,
        'complemento' => utf8_decode($complemento),
        'bairro' => "",
        'estado' => "",
        'cidade' => "",
        'responsavel_possui' => ($nome_responsavel != "")? 1:0, 
        'responsavel_nome' => utf8_decode($nome_responsavel),
        'responsavel_cpf' => "",
        'responsavel_telefone' => "",
        'agenda' =>array()
    );  
$id_novo++;
}

$_paciente[""] = array(
    'data' => "",
    'id_paciente' => "",
    'nome' => utf8_decode("paciente desconhecido"),
    'telefone1' => 0,
    'telefone2' => 0,
    'sexo' => 0,
    'cpf' => "", 
    'estado_civil' => "",
    'rg' =>  "",
    'data_nascimento' => "",
    'profissao' => "",
    'lixo' => "0",
    'cep' => "",  
    'endereco' => "",
    'numero' => "",
    'complemento' => "",
    'bairro' => "",
    'estado' => "",
    'cidade' => "",
    'responsavel_possui' => "", 
    'responsavel_nome' => "",
    'responsavel_cpf' => "",
    'responsavel_telefone' => "",
    'agenda' =>array()
);  
//$_paciente['0'] = $_paciente['189'] = $_paciente['594'] = $_paciente['518'] = $_paciente['437'] = $_paciente['589'] = $_paciente['318'] = $_paciente[""]; 






$colaboradores = array(
    "thiago" => 0,
    "mel" => 0,
    "joao" => 0,
    "naira" => 0
);

$sql->add($_p."colaboradores", "data=now(), nome='Hiago Aquino'");
$colaboradores['thiago'] = $sql->ulid;
$sql->add($_p."colaboradores", 'data=now(), nome="Mel"');
$colaboradores['mel'] = $sql->ulid;
$sql->add($_p."colaboradores", 'data=now(), nome="'. utf8_decode("João Victor Soares"). '"');
$colaboradores['joao'] = $sql->ulid;
$sql->add($_p."colaboradores", 'data=now(), nome="Naira Lima"');
$colaboradores['naira'] = $sql->ulid;

/*foreach ($colaboradores as $nome){
    $colab;
    $sql->consult($_p."colaboradores", "nome", "where nome like %\"$nome\"% ");
    if($sql->rows){
        $colab = mysqli_fetch_object($sql->mysqry).;

    }
}*/

echo "<br>id;nome;descricao_cliente;data_inicio;data_fim;cadeira;dentista;duracao;situacao;convenio";

$num_colunas = count(explode(";", $arq_agenda[0]));
//echo "<h1> " . count($arq_paciente_dados) . "</h1>";
for($i = 1; $i < count($arq_agenda); $i++ ){

    $linha = explode(";", $arq_agenda[$i]);
    if($num_colunas != count($linha)){
        echo "<h1> ERRO: uma das tabelas está formatada de forma errada <h1><br>";
        echo "<h2>linha: $i <h2>";
        die();
    }

    list(
        $cadeira,
        $dentista,
        $id_cliente,
        $descricao_cliente,
        $inicio,
        $fim,
        $duracao,
        $situacao,
        $convenio
    ) = $linha;

    $tmp_id_status = "";
    $situacao = trim($situacao); 
    $index = strtolowerWLIB(str_replace(" ", "", tirarAcentos($descricao_cliente)));


    
/*    switch($situacao){
        case ("Atendido (atrasado)" || "Atendido"):
            $tmp_id_status = 5;
            $tmp_obs_adicional = "Atendido (atrasado)"; 
        break;

        case ("Desmarcou" || "Desmarcou em cima da hora"):
            echo "<h1>case1</h1>";
            $tmp_id_status = 4;
        break;

        case ("CONTRATO CANCELADO"):
            echo "<h1>case2</h1>";

            $tmp_id_status = 4;
            $tmp_obs_adicional = ", CONTRATO CANCELADO";
        break;

        case ("Faltou"):
            $tmp_id_status = 3;
        break;

        case ("nós desmarcamos"):
            $tmp_id_status = 4;
            $tmp_obs_adicional = ", nós desmarcamos";
        break;

        case ("atendido" || "Atendido" || "Cliente chegou" || "Confirmar"):
            $tmp_id_status = 5;
        break;

        default:
            $tmp_id_status = 0;
            $descricao_cliente .= ("," . $situacao);
        break;
    }*/

    if ($situacao == "Atendido (atrasado)" || $situacao == "Atendido") {
        $tmp_id_status = 5;
        $descricao_cliente = "Atendido (atrasado)"; 
    } elseif ($situacao == "Desmarcou" || $situacao == "Desmarcou em cima da hora") {
        echo "<h1>case1</h1>";
        $tmp_id_status = 4;
    } elseif ($situacao == "CONTRATO CANCELADO") {
        echo "<h1>case2</h1>";
        $tmp_id_status = 4;
        $descricao_cliente = ", CONTRATO CANCELADO";
    } elseif ($situacao == "Faltou") {
        $tmp_id_status = 3;
    }elseif($situacao == "Confirmar" || $situacao == "CONFIRMAR HORÁRIO"){
        $tmp_id_status = 1;
    } elseif ($situacao == "nós desmarcamos") {
        $tmp_id_status = 4;
        $descricao_cliente = ", nós desmarcamos";
    } elseif ($situacao == "atendido" || $situacao == "Atendido" || $situacao == "Cliente chegou") {
        $tmp_id_status = 5;
    } else {
        $tmp_id_status = 0;
        $descricao_cliente .= (", " . $situacao);
    }

    $dentista = trim($dentista);

    switch($dentista){
        case "ZZNaira Lima":
            $profissionais = $colaboradores['naira'];
            break;

        case "ZZZJoão Victor Soares":
            $profissionais = $colaboradores['joao'];;
            break;

        case "ZZZMel":
            $profissionais = $colaboradores['mel'];;
            break;
        case "ZZZThiago Aquino":
            $profissionais = $colaboradores['thiago'];;
            break;

        case "Dra. Ana Laura Albuquerque":
            $profissionais = 105;
            break;

        case "Dr. Walter Curti":
            $profissionais = 104;
            break;

        case "Dra. Anelise Fernandes":
            $profissionais = 108;
            break;

        case "Dr. Ana Beatriz Laguna":
            $profissionais = 103;
            break;

        case "Dra. Karol Miwa Hayashi":
            $profissionais = 107;
            break;

        case "Dr. Lourenço Carnevari":
            $profissionais = 106;
            break;

        default:
          //  echo "<h1> Dentista não existe: '$dentista'<h1>";
    }


    if(!isset($_paciente[$index])){
        echo "<br>$id_cliente;$index;$descricao_cliente;$inicio;$fim;$cadeira;$dentista;$duracao;$situacao;$convenio";
        continue;
    }


    $_paciente[$index]["agenda"][] = array(
            "agenda_data" =>str_replace([" UTC"], "", trim($inicio)) ,
            "agenda_duracao" => $duracao,
            "id_paciente" => $_paciente[$index]["id_paciente"],
            "id_status" => $tmp_id_status,
            "agenda_data_final" =>str_replace([" UTC"], "", trim($fim)),
            "profissionais" => $profissionais,
            "observacao" =>  utf8_decode($descricao_cliente)
    );

    //echo "<br>ggggggggggggggggggggggggggggggggggggggggggggggggggg<br>";
    //print_r($_paciente[$index]["agenda"]);
    //echo $situacao;
    //echo "<br>ggggggggggggggggggggggggggggggggggggggggggggggggggg<br>";
    
  //  echo ".";
}

/*
pega novos dados;

$sql->consult($_p."pacientes", "MAX(id) as id", "");
if($sql->rows){
  $tmp = mysqli_fetch_object($sql->mysqry);
  $id = $tmp-> id;
  ++$id;
}*/

echo "<br>Adicionando dados na tabela: ";
//cadastrando pacientes no banco 
$num = count($_paciente);
$i= 0;
$dados = "";
$valores = "";
$vSQLPaciente ="data, id, nome, telefone1, telefone2, sexo, cpf, estado_civil, rg, data_nascimento, profissao, numero, cep, complemento, endereco, bairro, estado, cidade, responsavel_possui, responsavel_cpf, responsavel_nome, responsavel_telefone, lixo";
$vSQL = "data, agenda_data, agenda_duracao, id_paciente, id_status, agenda_data_final, profissionais, obs";

foreach ($_paciente as $x){

    if(!isset($x['id_paciente'])){
        echo "<br>++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++";
        print_r($x);
        echo "++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++<br>";
        die();
    }

  /*  $vSQLPaciente = "data='" . $x['data'] . "',
                        nome='" . addslashes($x['nome']) . "',
                        telefone1='" . telefone($x['telefone1']) . "',
                        telefone2='" . telefone($x['telefone2']) . "',
                        sexo='" . $x['sexo'] . "',
                        cpf='" . addslashes($x['cpf']) . "',
                        estado_civil='" . $x['estado_civil'] . "',
                        rg='" . addslashes($x['rg']) . "',
                        data_nascimento='" . ($x['data_nascimento']) . "',
                        profissao = '" . $x['profissao'] . "',
                        numero='". $x['numero'] ."',

                        cep='" . addslashes($x['cep']) . "',
                        complemento='" . $x['complemento'] . "',
                        endereco='" . addslashes($x['endereco']) . "',


                        bairro = '" . $x['bairro'] . "',
                        estado = '" . $x['estado'] . "',
                        cidade = '" . $x['cidade'] . "',
                
                        responsavel_possui='" . $x['responsavel_possui'] . "',
                        responsavel_cpf = '" . $x['responsavel_cpf'] . "',
                        responsavel_nome = '" . addslashes($x['responsavel_nome']) . "',
                        responsavel_telefone = '" . $x['responsavel_telefone'] ."',

                        lixo='" . $x['lixo'] . "'";*/


        
        $dados .= ("('" . $x['data'] . "', '". $x['id_paciente'] . "', '" . addslashes($x['nome'])) . "', '" . telefone($x['telefone1']) . "', '" . telefone($x['telefone2']) . "', '" . $x['sexo'] . "', '" . 
        addslashes($x['cpf']) . "', '" . $x['estado_civil']   . "', '" . addslashes($x['rg']) . "', '" . ($x['data_nascimento']). "', '" . $x['profissao'] . "', '" . 
        $x['numero'] . "', '" . addslashes($x['cep']) . "', '" . $x['complemento'] . "', '" . addslashes($x['endereco']) . "', '" . $x['bairro'] . "', '" . 
        $x['estado'] . "', '" . $x['cidade'] . "', '" . $x['responsavel_possui'] . "', '" . $x['responsavel_cpf'] . "', '" . addslashes($x['responsavel_nome']) . "', '" . $x['responsavel_telefone'] . "', '" . $x['lixo'] . "')";


        if(isset($x['agenda'])){
            echo "[";
            foreach($x['agenda'] as $a){
                $valores .= "(now()" . ", '" . $a['agenda_data']  . "', '" . $a['agenda_duracao']  . "', '" .  $x['id_paciente']   . "', '" . $a['id_status'] . "', '" . $a['agenda_data_final'] . "', '" . $a['profissionais']  . "', '" . addslashes($a['observacao']) . "'),";
    
                echo "=";
            }
            echo "]";
        }
        echo ".";
        
    if(!($i+1 == $num)){
        $dados .= ", ";
    }
    $i++;
}
echo "<br> <h1> Primeiro </h1> <br>";
echo $dados;

$sql->insertMultiple($_p."pacientes", $vSQLPaciente, $dados); 


echo "<br> <h1> Segundo </h1> <br>";
$valores = substr($valores, 0, -1);
echo $valores;

$sql->insertMultiple($_p."agenda", $vSQL, $valores); 

echo "<br> terminado";
