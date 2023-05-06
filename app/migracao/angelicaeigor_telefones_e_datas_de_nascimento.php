<?php
	require_once("../lib/conf.php");	
    require_once("../lib/classes.php");

	$sql = new Mysql();

    $paciente = file("pacientes.csv");
    $lista = [];
   

$_p = "angelicaeigor.ident_";



//echo "Instancia atual: " . $_GET['instancia'] . "<br>";

 //echo "Resultado do SHOW DATABASES <br>";
 $sql->consult($_p . "pacientes" , "id, nome, telefone1", "where lixo != 1");
if($sql->rows){
    $lista = mysqli_fetch_all($sql->mysqry, MYSQLI_ASSOC);
   print_r($lista);
}

    echo "<br>pegando e formatando pacientes da lista";
    foreach ($paciente as $linha) {
        list(
            $id_paciente,
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
            $numeropaciente,
            //não sei o que fazer com esse dado
            $observacao,
            //não sei o que fazer com esse dado
            $rg,
            $sexo,
            $telefone,
            $uf,
            $complemento,
            $plano, //não sei o que fazer com esse dado
            $motivo_chegar_clinica,//não sei o que fazer com esse dado
            $titular_plano,//não sei o que fazer com esse dado
            $cpf_resposavel_plano,
            $numero_carteirinha,//não sei o que fazer com esses dois dados
            $excluido
        ) = explode(',', str_replace("\"", "", $linha));
    
        //endereço
        if (!empty($bairro))
            $endereco .= ", $bairro";
        if (!empty($cidade))
            $endereco .= ", $cidade";
        $datanascimento = ($datanascimento);
        $nome = trim($nome);
        if (empty($celular)) {
            $celular = $telefone;
        } 
        //inicialmente eu ia usar o nome + id do paciente, mas no agendamento apenas o nome do paciente é usado como identificador
        $index = strtolowerWLIB(str_replace(" ", "", tirarAcentos($nome)));
    
        //verificando por nomes repetidos
        if (isset($_pacientes[$index])) {
            echo "=" . $index . " $nome<BR>";
            die();
        }

        $_pacientes[$index] = array(
            'id_paciente' => $id_paciente,
            'lixo' => ($excluido) == 'f' ? 1 : 0,
            'data' => $data_registro,
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
        echo ".";
        // echo $index . "->" . $nome . "->" . $telefone . " -> " . $data_registro . " -> " . $id_paciente . "<br />";
    }



$vSQL = "";
$i = 0;

$num = sizeof($lista);

foreach ($lista as $linha){

    if($linha['nome'] == ""){
        continue;
    }
    //pesquisando por nomes na lista de pacientes do arquivo local
    $index = strtolowerWLIB(str_replace(" ", "", tirarAcentos(utf8_encode($linha['nome']))));

    echo("<br> =============================================================<br>");
    echo $linha['nome']."::::::::::::::::::<br>";
    print_r($_pacientes[$index]);
    echo("<br>-------------------------------------------------------------<br>");
    print_r($linha);
    echo("<br> +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++");

    //atualizando com os valores corretos
    if(isset($_pacientes[$index]))
    {
        $linha['telefone1'] = $_pacientes[$index]['celular']; 
        $linha['telefone2'] = $_pacientes[$index]['telefone'];
        $linha['data_nascimento'] = $_pacientes[$index]['dn'];
    }else{
        echo "<br> <h1>Paciente '". $linha['nome'] . "' não existe</h1>";
        continue;
    }
   

    $vSQL .= "('" . $linha['id'] ."', '"
                  . $linha['telefone1'] ."', '"
                  . $linha['telefone2'] . "', '"
                  . $linha['data_nascimento'] ."')";
    if(!($i+1 == $num)){
        $vSQL .= ", ";
    }
    $i++;   
}
//removendo uma vírgula que fica no final
$vSQL = substr($vSQL, 0, -2);




echo "<br>¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬¬<br>$vSQL";
$sintax = "INSERT INTO ". $_p ."pacientes (id, telefone1, telefone2, data_nascimento)
VALUES " . $vSQL ." ON DUPLICATE KEY UPDATE
telefone1=VALUES(telefone1), telefone2=VALUES(telefone2), data_nascimento=VALUES(data_nascimento)
";

//inserindo no banco os dados atualizados;
echo "<br> >>>>>>>>>>>>>>>>>>>>>>>>>>.<br>";
echo $sintax;


die();

//$sql->sintax($sintax)


?>
