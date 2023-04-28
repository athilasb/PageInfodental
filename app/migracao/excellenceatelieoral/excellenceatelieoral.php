<?php

//OBS: não esqueça de procurar por ";" em lugares inválidos do sistema

set_time_limit(0);

require_once("../../lib/conf.php");
require_once("../../lib/classes.php");

$arq_paciente_dados = file("arqs/paciente_dados.csv");
$arq_agenda = file("arqs/agenda.csv");
$arq_telefone_paciente = file("arqs/paciente_fone.csv");
$arq_pacientes_endereco = file("arqs/paciente_endereco.csv");
$arq_funcionarios = file("arqs/prestador_equipe.csv");

$_paciente = array();
$_funcionarios = array();
$_funcionarios_id = array();

$_p = "infodental.ident_";
//$_p = "excellenceatelieoral.ident_";

$sql = new Mysql();
print_r("Banco de dados: ". $_ENV['MYSQL_DB'] . "<br>");
echo "migração já realizada";

//migracao/excellenceatelieoral/excellenceatelieoral.php


//$sql->del($_p."colaboradores", "");
$sql->del($_p."agenda", "");
$sql->del($_p."pacientes", "");

//funcionarios
echo "Adicionando colaboradores na tabela:";
echo "<br>Configurando dados dos pacientes:";
$num_colunas = count(explode(";", $arq_funcionarios[0]));
//echo "<h1> " . count($arq_paciente_dados) . "</h1>";
for($i = 1; $i < count($arq_funcionarios); $i++ ){
    $cpf;
    
    $linha = explode(";", $arq_funcionarios[$i]);
    if($num_colunas != count($linha)){
        echo "<h1> ERRO: uma das tabelas está formatada de forma errada <h1><br>";
        echo "<h2>linha: $i <h2>";
        die();
    }

    list(
    $ID_CONTA,
        $ID_PRESTADOR,
    $ID_PRESTADOR_EQUIPE,
    $ID_PRESTADOR_PRINCIPAL,
    $ID_TIPO_PRESTADOR,
    $ID_USER_STAMP_INS,
    $ID_USER_STAMP_UPD,
    $DT_TIME_STAMP_INS,
    $DT_TIME_STAMP_UPD,
    $FL_ATIVO,
    $FL_HABILITADO,
    $TX_ID_CONTA_USUARIO,
    $TX_ID_PRESTADOR_EQUIPE,
    $TX_PRESTADOR,
    $TX_PRESTADOR_PRINCIPAL,
    $TX_NOME_CONTA,
    $TX_TIPO_PRESTADOR,
    $TX_ID_PRESTADOR,
    $TX_STATUS,
    $TX_HABILITADO,
    $TX_TIME_STAMP_INS,
    $TX_TIME_STAMP_UPD,
    $FL_LEAF,
    $ID_CONTA_REGISTRO,
    $ID_PRESTADOR_REGISTRO,
    $TX_LABEL_CNPJ,
    $TX_CNPJ,
    $TX_CRO,
    $ID_CRO_UF,
    $TX_CRO_LABEL,
    $TX_APELIDO,
    $ID_PRESTADOR_CONTA,
    $TX_CBO,
    $TX_APRESENTACAO,
    $FL_AUTOAGENDAMENTO,
    $TX_FOTO) = $linha;

    $cpf = str_replace([".", "-", "/", " "], "", $TX_CNPJ);

    $tmp = ($ID_TIPO_PRESTADOR==3)?0:1;                                                                      //adicionado id_tipo_prestador    
    $vSQLprofissional = "
        id_cargo = '" . $tmp ."', 
        data = '". $DT_TIME_STAMP_INS ."',
        nome = '". $TX_PRESTADOR . "',
        cpf = '". $cpf ."',
        cro = '". $TX_CRO."'";


    echo $vSQLprofissional . "<br>";


    $sql->add($_p."colaboradores", $vSQLprofissional);
    $_funcionarios_id[$ID_PRESTADOR] = $sql->ulid;
    //$_funcionarios_id[$ID_PRESTADOR] = 1;
    echo ".";
}



//echo "Pegando dados pessoais do paciente: " . "<br>";
echo "<br>Configurando dados dos pacientes:";
$num_colunas = count(explode(";", $arq_paciente_dados[0]));
//echo "<h1> " . count($arq_paciente_dados) . "</h1>";
for($i = 1; $i < count($arq_paciente_dados); $i++ ){
    $st_civil;

    $linha = explode(";", $arq_paciente_dados[$i]);
    if($num_colunas != count($linha)){
        echo "<h1> ERRO: uma das tabelas está formatada de forma errada <h1><br>";
        echo "<h2>linha: $i <h2>";
        die();
    }
    
    list(
            $DT_NASCIMENTO,
        $DT_TIME_STAMP_INS,
        $DT_TIME_STAMP_UPD,
        $ID_DOC_TIPO,
            $ID_ESTADO_CIVIL,
            $ID_PACIENTE,
    $ID_PESSOA,
        $ID_PRESTADOR_RES,
            $ID_SEXO,
            $ID_STATUS,
    $ID_SITUACAO,
    $ID_OCUPACAO,
    $ID_TIPO_INDICACAO,
    $CD_TIPO_INDICACAO,
    $ID_USER_STAMP_INS,
    $ID_USER_STAMP_UPD,
    $TX_APELIDO,
            $TX_OCUPACAO,
            $TX_CPF,
    $TX_CPF_RES,
    $TX_DIA_ANIV,
    $TX_DOC_LABEL,
            $TX_DOC_NRO,
            $TX_DOC_TIPO,
    $TX_DT_NASCIMENTO,
    $TX_ESTADO_CIVIL,
    $TX_ID_PACIENTE,
    $ID_CODIGO,
    $TX_CODIGO,
    $TX_IDADE,
        $TX_INDICACAO,
        $TX_INDICACAO_LABEL, //indicação contem o nome de um paciente ou o nome de um funcionario
    $TX_MES_ANIV,
    $TX_NODE_LABEL,
    $TX_NOME,
    $TX_NOME_CONJUGE,
    $TX_NOME_MAE,
    $TX_NOME_PAI,
    $TX_NOME_RES,
    $TX_OBSERV,
    $TX_PRESTADOR_RES,
    $TX_PRONTUARIO,
    $TX_SEXO,
    $TX_STATUS,
    $TX_TIME_STAMP_INS,
    $TX_TIME_STAMP_UPD,
    $VL_DIA_ANIV,
    $VL_MES_ANIV,
        $FL_ATIVO,
    $TX_STATUS_PACIENTE, //pode ser importante
    $TX_TIPO_INDICACAO,
    $TX_FOTO,
    $TX_OPERADORA,
    $ID_CONTA_REGISTRO,
    $ID_PRESTADOR_REGISTRO,
    $DT_NASCIMENTO_YEAR,
    $TX_NOME_RESPONSAVEL_ATIVO,
        $TX_CPF_RESPONSAVEL_ATIVO, //nome do responsavel
        $TX_FONE_LABEL_UNIC, //cpf responsavel
        $FL_PUBLICO) = $linha;

    $rg = 0;

    if($ID_ESTADO_CIVIL == 1){
        $st_civil = "CASADO";
    }else{  
        $st_civil = "";
    }if($TX_DOC_NRO != "NULL" && $TX_DOC_TIPO == "RG"){
        $rg = $TX_DOC_NRO;
    }else{
        $rg = "";
    }

    $resp_nome = "";
    $resp_cpf = 0;
    $resp_possui = 0;
    $resp_telefone = "";

    if(!empty($TX_CPF_RESPONSAVEL_ATIVO) || !empty($TX_FONE_LABEL_UNIC) || !empty($TX_NOME_RESPONSAVEL_ATIVO)){
        $resp_possui = 1;
        $resp_cpf = ($TX_CPF_RESPONSAVEL_ATIVO == "NULL") ? "": str_replace([".", " ", "-"], "", $TX_CPF_RESPONSAVEL_ATIVO);
        $resp_nome = ($TX_NOME_RESPONSAVEL_ATIVO == "NULL")? "" : $TX_NOME_RESPONSAVEL_ATIVO;
        $resp_telefone = ($TX_FONE_LABEL_UNIC == NULL) ? "": str_replace(["(", ")", "-", " "], 
                                                                    "", 
                                                                      str_replace(["/"], ",", $TX_FONE_LABEL_UNIC));
    }

    $tmp_sexo = (trim($ID_SEXO) == 1) ? "M":"F";

    $_paciente[$ID_PACIENTE] = array(
        'data' => $DT_TIME_STAMP_INS,
        'id_paciente' => $ID_PACIENTE,
        'nome' => utf8_decode($TX_NOME),
        'telefone1' => "",
        'telefone2' => "",
        'data_nascimento' => $DT_NASCIMENTO,
        'estado_civil' => $st_civil,
        'cpf' => ($TX_CPF != "NULL") ? $TX_CPF : "", 
        'sexo' => $tmp_sexo,
        'lixo' => ($ID_STATUS == 1) ? "0": "1",
        'profissao' => ($TX_OCUPACAO != "NULL") ? utf8_decode($TX_OCUPACAO): "",
        'rg' =>  $rg,
        'cep' => "",  
        'endereco' => "",
        'numero' => "",
        'complemento' => "",

        'bairro' => "",
        'estado' => "",
        'cidade' => "",

        'responsavel_possui' => $resp_possui, 
        'responsavel_nome' => utf8_decode($resp_nome),
        'responsavel_cpf' => $resp_cpf,
        'responsavel_telefone' => $resp_telefone,

        'agenda' =>array()
    );  

    echo ".";

}

//na agenda existe id==NULL
$_paciente['NULL'] = array(
    'data' => "now()",
    'id_paciente' => 0,
    'nome' => "paciente_desconhecido",
    'telefone1' => "",
    'telefone2' => "",
    'data_nascimento' => "",
    'estado_civil' => "",
    'cpf' => "", 
    'sexo' => "M",
    'lixo' => "",
    'profissao' => "",
    'rg' =>  "",
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




echo "<br>Configurando endereco dos pacientes:";
$num_colunas = count(explode(";", $arq_pacientes_endereco[0])); //pegando a primeira linha
//endereco dos pacientes
for($i = 1; $i < count($arq_pacientes_endereco); $i++ ){

    $linha = explode(";", $arq_pacientes_endereco[$i]);
    if($num_colunas != count($linha)){
        echo "<h1> ERRO: uma das tabelas está formatada de forma errada <h1><br>";
        echo "<h2>linha: $i <h2>";
        die();
    }



    list(
        $DT_TIME_STAMP_INS,
        $DT_TIME_STAMP_UPD,
        $FL_ATIVO,
        $ID_BAIRRO,
        $ID_CIDADE,
        $ID_ENDERECO,
        $ID_PACIENTE,
        $ID_PESSOA,
        $ID_PESSOA_ENDERECO,
        $ID_PRESTADOR_RES,
        $ID_USER_STAMP_INS,
        $ID_USER_STAMP_UPD,
        $ID_TIPO_ENDERECO,
        $ID_TIPO_LOGRADOURO,
        $ID_UF,
        $TX_BAIRRO,
        $TX_CEP,
        $TX_CEP_LIMPO,
        $TX_CIDADE,
        $TX_COMPLEMENTO,
        $TX_ENDERECO,
        $TX_LOGRADOURO,
        $TX_LOGRADOURO_LABEL,
        $TX_NODE_LABEL,
        $TX_ENDERECO_LABEL,
        $TX_NOME_PACIENTE,
        $TX_NUMERO,
        $TX_REFERENCIA,
        $TX_TIME_STAMP_INS,
        $TX_TIME_STAMP_UPD,
        $TX_TIPO_ENDERECO,
        $TX_TIPO_LOGRADOURO,
        $TX_UF,
        $TX_STATUS,
        $ID_CONTA_REGISTRO,
        $CD_UF
    ) = $linha;

    if($TX_ENDERECO != "NULL"){
        $tmp_endereco = ($TX_ENDERECO) . ", "; 
    }if($TX_NUMERO != "NULL"){
        $tmp_endereco .= $TX_NUMERO . ", ";
    }if($TX_BAIRRO != "NULL"){
        $tmp_endereco .=  ($TX_BAIRRO) . ", ";
    }if($TX_CIDADE != "NULL"){
        $tmp_endereco .= ($TX_CIDADE) . ", ";
    }if($TX_UF != "NULL"){
        $tmp_endereco .= ($TX_UF);
    }
    $tmp_cep = ($TX_CEP_LIMPO == NULL)?0:$TX_CEP_LIMPO;
        
    $_paciente[$ID_PACIENTE]['endereco'] = utf8_decode($tmp_endereco);
    $_paciente[$ID_PACIENTE]['bairro'] =  ($TX_BAIRRO == "NULL") ? "": utf8_decode($TX_BAIRRO);
    $_paciente[$ID_PACIENTE]['numero'] =  ($TX_NUMERO == "NULL") ? "": utf8_decode($TX_NUMERO);
    $_paciente[$ID_PACIENTE]['cep'] = $tmp_cep;


    echo ".";
}

 
echo "<br>Configurando telefones dos pacientes:";
$num_colunas = count(explode(";", $arq_telefone_paciente[0])); //pegando a primeira linha
//telefones dos pacientes
for($i = 1; $i < count($arq_telefone_paciente); $i++ ){

    $linha = explode(";", $arq_telefone_paciente[$i]);
    if($num_colunas != count($linha)){
        echo "<h1> ERRO: uma das tabelas está formatada de forma errada <h1><br>";
        echo "<h2>linha: $i <h2>";
        die();
    }

   list(
$DT_TIME_STAMP_INS,
$DT_TIME_STAMP_UPD,
    $FL_ATIVO,
    $ID_FONE,
    $ID_PACIENTE,
    $ID_PESSOA,
    $ID_PESSOA_FONE,
    $ID_TIPO_FONE,
    $ID_USER_STAMP_INS,
    $ID_USER_STAMP_UPD,
    $TX_COMPLEMENTO,
    $TX_DDI_LOCAL,
    $TX_FONE_DDD,
    $TX_FONE_NRO_FLT,
    $TX_FONE_LABEL,
    $TX_FONE_NRO,
    $TX_FONE_RAM,
    $TX_NODE_LABEL,
    $TX_NOME_PACIENTE,
    $TX_STATUS,
    $TX_TIME_STAMP_INS,
    $TX_TIME_STAMP_UPD,
    $DT_TIME_STAMP_MOD,
    $TX_TIPO_FONE,
    $ID_CONTA_REGISTRO) = $linha;

    $telefone1 = 0;
    $telefone2 = 0;

    if(!isset($_paciente[$ID_PACIENTE])){
        echo "paciente de id: $ID_PACIENTE <br>";
        continue;
    }

    if($TX_TIPO_FONE == "Celular"){
        $telefone1 = str_replace(["(", ")", " ", "-"], "", $TX_FONE_LABEL);
    }else{       
        $telefone2 = str_replace(["(", ")", " ", "-"], "", $TX_FONE_LABEL) . ",";
    }

    $_paciente[$ID_PACIENTE]['telefone1'] = $telefone1;
    $_paciente[$ID_PACIENTE]['telefone2'] .= $telefone2;  
    
    echo ".";
}

//agenda
echo "<br>Adicionando dados na agenda:";
$num_colunas = count(explode(";", $arq_agenda[0])); //pegando a primeira linha
for($i = 1; $i < count($arq_agenda); $i++ ){

    $linha = explode(";", $arq_agenda[$i]);
    if($num_colunas != count($linha)){
        echo "<h1> ERRO: uma das tabelas está formatada de forma errada <h1><br>";
        echo "<h2>linha: $i <h2>";
        die();
    }

    list(
    $id_agenda_item,
    $tx_prestador,
    $tx_data,
    $tx_dt_hora_ini,
    $tx_duracao,
        $tx_status, //converter status
    $tx_descricao,
    $tx_motivo,
        $vl_duracao,
        $id_paciente,
        $id_status,
    $id_convenio,
    $dt_data,
        $dt_hora_ini,
        $id_unidade_atendimento,
        $id_conta_registro,
        $id_prestador_registro
    ) = $linha;

    //calculando a data e hora do sistema
    $tmp_data = new DateTime($dt_hora_ini);
    $tmp_data->add(new DateInterval('PT' . ($vl_duracao) . 'M'));

    switch($id_status){
        case 1 || 42746 || 54865 || 117859 || 128358 : //pendente || aguardando wpp de confirmacao || Pendente || Aguardando confirmação paciente || Aguardando confirmação profissional 
            $tmp_id_status = 1; //à confirmar
            break;
        case 9 || 107360: //confirmado pelo paciente || Confirmado pelo paciente
            $tmp_id_status = 2;
            break;
        case 42748: //atendido
            $tmp_id_status = 5;
            break;
        case 42749 || 75863: //Atrasado - não atendido || Paciente faltou || 
            $tmp_id_status = 3;
            break;
        case 86362 || 96861: // Desmarcado com antecedência || Desmarcado sem antecedência
            $tmp_id_status = 4;
            break;
        default: 
            echo "<h1> valor desconhecido! </h1>";
            die();
    }
 //null
    if($tx_motivo == "NULL"){
        $tx_motivo = "";
    }else{
        $tx_motivo = ", " . $tx_motivo;
    }

    $tx_descricao .= $tx_motivo;
    $id_prestador_registro = trim($id_prestador_registro);

    if(!isset($_funcionarios_id[$id_prestador_registro])){
        echo "<h1> erro: funcionario de id: '$id_prestador_registro' não existe <h1>";
        var_dump($_funcionarios_id);
    }

    $_paciente[$id_paciente]["agenda"][] = array(
        "agenda_data" => $dt_hora_ini,
        "agenda_duracao" => $vl_duracao,
        "id_paciente" => $id_paciente,
        "id_status" => $tmp_id_status,
        "agenda_data_final" => $tmp_data->format("Y-m-d H:i:s"),
        "profissionais" => $_funcionarios_id[$id_prestador_registro],
        "observacao" =>  utf8_decode($tx_descricao) 
    );


   // print_r($_paciente[$id_paciente]);
    echo ".";

    //adicionar na tabela agenda e historico do paciente. adicionar na tabela id paciente. 
}

echo "<br>Adicionando dados na tabela: ";
//cadastrando pacientes no banco 
$num = count($_paciente);
$i= 0;
$dados = "";
$valores = "";
$vSQLPaciente ="data, id, nome, telefone1, telefone2, sexo, cpf, estado_civil, rg, data_nascimento, profissao, numero, cep, complemento, endereco, bairro, estado, cidade, responsavel_possui, responsavel_cpf, responsavel_nome, responsavel_telefone, lixo";
$vSQL = "data, agenda_data, agenda_duracao, id_paciente, id_status, agenda_data_final, profissionais, obs";

foreach ($_paciente as $x){

    if(!is_array($x)){
        echo "<br> =========================================================================================== <br>";
        echo "<br> $x <br>";
        die();
    }

    if(!isset($x['id_paciente'])){
        echo "<br> -------------------------------------------------------------------------------------------- <br>";
        print_r($x);
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
                $valores .= "('0000-00-00'" . ", '" . $a['agenda_data']  . "', '" . $a['agenda_duracao']  . "', '" .  $x['id_paciente']   . "', '" . $a['id_status'] . "', '" . $a['agenda_data_final'] . "', '" . $a['profissionais']  . "', '" . addslashes($a['observacao']) . "'),";
    
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

/*
    $tmp_id_paciente = $sql->ulid;

    if(isset($x['agenda'])){
        echo "[";
        foreach($x['agenda'] as $a){
            /*$vSQL = "data               = " . "now(),
            agenda_data               = '". $a['agenda_data'] ."', 
            agenda_duracao               = '". $a['agenda_duracao'] . "',
            id_paciente               = '". $tmp_id_paciente . "',
            id_status               = '". $a['id_status'] . "',
            agenda_data_final                 = '". $a['agenda_data_final'] ."',
            profissionais               = '". $a['profissionais'] . "',
            obs               = '" . $a['observacao'] . "'";  * /
              

            $vSQL = "data, agenda_data, agenda_duracao, id_paciente, id_status, agenda_data_final, profissionais, obs";
            $valores = "(now()" . ", '" . $a['agenda_data']  . "', '" . $a['agenda_duracao']  . "', '" . $tmp_id_paciente  . "', '" . $a['id_status'] . "', '" . $a['agenda_data_final'] . "', '" . $a['profissionais']  . "', '" . $a['observacao'] . "')";


            $sql->insertMultiple($_p."agenda", $vSQL, $valores); 

           // $sql->add($_p . "agenda", $vSQL);
            $id_agenda = $sql->ulid;
            $sql->add($_p . "log", "id_usuario='" . $tmp_id_paciente . "',tipo='insert',vsql='" . addslashes($vSQL) . "',tabela='" . $_p . "agenda',id_reg='" . $id_agenda . "'");
            echo "=";
        }
        echo "]";
    }
    echo ".";
}
*/
echo "<br>Terminado";

?>