<?php
require_once("../../lib/classes.php");
require_once("../../lib/conf.php");

$cadastrar_registros = 0;
$_p = "excellenceatelieoral.ident_";


//id;TX_NOME_PACIENTE;TX_DT_EMISSAO;TX_DT_HISTORICO;TX_EXECUTANTE;TX_REGIAO;TX_FACES;CD_PROCEDIMENTO;TX_DESCRICAO;ID_PACIENTE;ID_CONTA_REGISTRO;FL_ATIVO;DT_HISTORICO
$sql = new Mysql();

$historico = file("historico_modificado.csv");
$list_historico = array();
$lista_procedimentos = array(); //procedimentos que serão inseridos no sistema




//////////////////////////////////////////////////////////////
$sql->consult($_p. "pacientes_evolucoes", "max(id) as id", "");
$id_max_evo = mysqli_fetch_object($sql->mysqry)->id;

$id_max_evo++;

//////////////////////////////////////////////////////////////

$colunas_pac_evo_proced="
data,
id_evolucao,
id_tipo,
id_paciente,
id_tratamento,
id_tratamento_procedimento,
id_profissional,
status,
obs,
plano,
id_plano,
opcao,
id_opcao,
id_procedimento,
id_procedimento_aevoluir,
numero,
numeroTotal";



$colunas_pac_evo = "id,
lixo,
data,
data_evolucao,
id_tipo,
id_paciente,
id_usuario,
obs,
id_anamnese,
id_clinica,
id_profissional,
data_pedido,
tipo_receita,
lixo_data,
lixo_id_colaborador,
pconsulta_data,
pconsulta_tempo,
pconsulta_profissionais,
receita_assinada,
s3,
enviarLink,
enviarLinkFinalizado,
id_assinatura";



//$colunas_parametros_para = "id, lixo, fixo, data, pub, id_especialidade, id_regiao, face, garantia, garantia_media, descricao_adicional, camposEvolucao, quantitativo";


$j = 0;
foreach ($historico as $linha) {
    list(
        $id,
        $TX_NOME_PACIENTE,
        $TX_DT_EMISSAO,
        $TX_DT_HISTORICO,
        $TX_EXECUTANTE,
        $TX_REGIAO,
        $TX_FACES,
        $CD_PROCEDIMENTO,
        $TX_DESCRICAO,
        $ID_PACIENTE,
        $ID_CONTA_REGISTRO,
        $FL_ATIVO,
        $DT_HISTORICO
    ) = explode(';', $linha);


    $list_historico[$id] = array(
        "id_evolucao" => "",
        'descricao' => utf8_decode($TX_DESCRICAO),
        'faces' => utf8_decode($TX_FACES),
        'regioes' => utf8_decode($TX_REGIAO),
        'dt_emissao' => utf8_decode($TX_DT_EMISSAO),
        'dt_historico' => $DT_HISTORICO,
        'tx_dt_historico' => $TX_DT_HISTORICO,
        'nome_paciente' => utf8_decode($TX_NOME_PACIENTE),
        'id_paciente' => $ID_PACIENTE,
        'nome_clinico' => utf8_decode($TX_EXECUTANTE)
    );
    $j++;
}
echo ("<h1>número de evolucoes lidas: $j </h1>");

//print_r($list_historico);

//die();
$vSQL = "";
$vSQL2 = "";

//$colunas_pac_evo_proced;
$i = 0;

foreach($list_historico as $linha)
{
    $vSQL.="( ". $id_max_evo .", 
            0, '".
            $linha['dt_historico']. "', '". 
           $linha['dt_historico'] . "', 
           2, '" .
           $linha['id_paciente'] . "', " .
           "101, 
           '', 
           0, 
           1, 
           101,
           '',
           '',
           '',
           '',
           '',
           '',
           '',
           '',
           '',
           '',
           '',
           ''),";
    $linha['id_evolucao'] = $id_max_evo;


    $obs = "
    
    <style>
    .tab {
        display: inline-block;
        margin-left: 40px;
    }
    </style>
    <br>Faces:<br>". "<span class=\"tab\"> </span>" . $linha['faces'] .
        utf8_decode("<br>Regiões:<br>"). "<span class=\"tab\"> </span>" . 
            $linha['regioes'] .
        "<br>Executante:<br>" . "<span class=\"tab\"> </span>" . 
            $linha['nome_clinico'] . 
        utf8_decode("<br>Descrição:<br>") . "<span class=\"tab\"> </span>" .  
            $linha['descricao'] .
        "<br>Data:<br>" . "<span class=\"tab\"> </span>" . $linha['tx_dt_historico']
        ;

$vSQL2 .= "('" . $linha['dt_historico'] . "', '" .
            $id_max_evo . "', 
            2, '" .
            $linha['id_paciente'] . "', 
            0, 
            0, 
            101, 
            '', 
            '". $obs ."', 
            0, 
            0, 
            '', 
            0, 
            0, 
            0, 
            0, 
            0),";  

    $id_max_evo++;
    $i ++;
}
echo ("<h1>número de evolucoes cadastradas: $i </h1>");



/*
foreach($list_historico as $linha)
{
    $vSQL.="( ". $id_max_evo .", 0, now(), '". 
           $linha['dt_historico'] . "', 2, '" .
           $linha['id_paciente'] . "', " .
           "101, '', 0, 1, 101,'','','','','','','','','','','',''),";
    $linha['id_evolucao'] = $id_max_evo;
    $id_max_evo++;
    $i++;
}

echo ("<h1>número de evolucoes cadastradas: $i </h1>");

$vSQL = substr($vSQL, 0, -1);

foreach($list_historico as $linha){
    $obs = "Faces:\n".
                "\t". $linha['faces'] . "\n" .
            "Regiões:\n".
                "\t". $linha['regioes'] . "\n" .
            "Executante:\n".
                "\t". $linha['nome_clinico'] . "\n" .
            "Descricao:\n". 
                "\t". $linha['descricao'];

    $vSQL2 .= "( '" . $linha['dt_historico'] . "', '" .
            $linha['id_evolucao'] . "', 2, '" .
            $linha['id_paciente'] . "', 0, 0, 101, '', '". $obs ."', 0, 0, '', 0, 0, 0, 0, 0),";            
}
*/
$vSQL = substr($vSQL, 0, -1);

$vSQL2  = substr($vSQL2, 0, -1);

echo $vSQL;
echo "<br><hr><hr><hr><hr><hr><hr><br>";
echo $vSQL2;

die();

//$sql->del($_p."pacientes_evolucoes", "");
//$sql->del($_p."pacientes_evolucoes_procedimentos", "");

$sql->insertMultiple( $_p."pacientes_evolucoes",  $colunas_pac_evo,  $vSQL);

$sql->insertMultiple($_p."pacientes_evolucoes_procedimentos" , $colunas_pac_evo_proced, $vSQL2);



/*
$sql->sintax("INSERT INTO " . $_p . " pacientes_evolucoes $colunas_pac_evo 
VALUES $vSQL");

$sql->sintax("INSERT INTO " . $_p . " pacientes_evolucoes $colunas_pac_evo_proced 
VALUES $vSQL");

/*
if ($cadastrar_registros == 1) {
    foreach( $lista_procedimentos as $linha){
        $vSQL = "id='". ."',
        lixo=0'". ."',
        fixo='". ."',
 data=now()'". ."',
 pub='". ."',
 id_especialidade='". ."',
 id_regiao='". ."',
 face='". ."',
 garantia='". ."',
 garantia_media='". ."',
 descricao_adicional='". ."',
 camposEvolucao='". ."',
 quantitativo='". ."'";
        $sql->add($_p . "", "");
    }
    $vSQL = "INSERT INTO ".$_p."parametros_procedimentos ($colunas_parametros) values ()";
}


$lista_historico = array();

/*
foreach($lista as $linha){
$lista_historico[$linha['id']] = array(
'id_tipo' = $list_historico[''],
'id_paciente' = $linha['id'],
);
}*/
?>