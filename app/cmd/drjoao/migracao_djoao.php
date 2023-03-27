<?php
use Aws\Common\Facade\ElasticLoadBalancing;
	require_once("../../lib/classes.php");
	$sql = new Mysql();
	$_p="ident_";
?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Infodental - Sistema de Migração</title>
</head>
<body>

<?php

$_p = ".ident_";
$sql = new Mysql();

#$mensagens_enviadasV2 = array();
#$pacientesV2 = array();
$cont = 0;
#$merge_mensagens = array();


$pacientes = file("relatorio_total_pacientes_cadastrados_modificado.csv");


// apaga pacientes e agendamentos
$sql->del($_p."pacientes","");
//$sql->del($_p."pacientes_historico","");
//$sql->del($_p."agenda","");

//pegando os pacientes da lista
$telefone;
$celular;
$id = 0;

foreach($pacientes as $linha){
    list(
        $Active,
$ActiveSheet_ALIGNERS,
$ActiveSheet_IMPLANT,
$ActiveSheet_ORTHOPEDIC,
$ActiveSheet_SELF_LIGATING,
        $Address,
        $AddressComplement,
        $AddressNumber,
        $Age,
        $BirthDate,
        $CEP,
        $City,
        $CivilStatus,
$ClinicalRecordNumber,
$CreateAtomicDate,
$CreatedAt,
        $Deleted,
        $DocumentId,
$Education,
        $Email,
$FatherOtherDocument,
        $HowDidMeet,
        
        $ID_PACIENTE,
        $IndicationSource,
$Landline,
        $MobilePhone,
$MotherOtherDocument,
        $Name,
$NameSearch,
        $Neighborhood,
$NickName,
$Notes,
$OnlineScheduling,
        $OtherDocumentId,
$OtherDocumentIdSearch,
        $OtherPhones,
        $OthersSource,
        $PersonBirthDate,
        $PersonInCharge,
        $PersonInChargeOtherDocument,
        $Profession,
$ProfileImage,
$SK_BirthDay,
$SPCShow,
        $Sex,
$SpecialtyType_ALIGNERS,
$SpecialtyType_IMPLANT,
$SpecialtyType_ORTHOPEDIC,
$SpecialtyType_SELF_LIGATING,
$Type,
$WorkingTime,
$Workplace,
$Zip,
$_AccessPath,
$fatherName,
$id,
$insurancePlanName,
$insurancePlanNumber,
$motherName,
        $State,
$z_Create_UserId,
$z_ImportKey,
$z_Inserted_Date,
$z_Inserted_Server_Tool,
$z_Inserted_UserId,
$z_LastChange_Date,
$z_LastChange_Server_Tool,
$z_LastChange_UserId,
$z_Server_Tool,
$z_TransactionId
    ) = explode(',', str_replace("\"", "", $linha));                            

                                    //todos os telefones sempre tem um espaço depois do )
    $index = strtolowerWLIB(str_replace(" ", "", tirarAcentos($Name)));
    //verificando por nomes repetidos
    if (isset($_pacientes[$index])) {
        var_dump($datas_existentes);
        die();
    }
    
    $_pacientes[$index] = [ 
    'data' => $CreatedAt,
    'lixo' => $Active,
    'nome' => $Name,
    'sexo' => $Sex,
    'cpf' => $OtherDocumentId,
    'data_nascimento' => $BirthDate ,
    'profissao' => $Profession ,
    'estado_civil' => $CivilStatus ,
    'telefone1' => $MobilePhone ,
    'telefone1_whatsapp' => 0,
    'telefone2' => $OtherPhones ,
    'email' => $Email ,
    'indicacao' => $IndicationSource ,
    'cep' => $CEP ,
    'endereco' => $Address ,
    'numero' => $AddressNumber ,
    'complemento' => $AddressComplement ,
    'bairro' => $Neighborhood ,
    'estado' => $State ,
    'cidade' => $City ,
    'responsavel_possui' => isset($PersonInCharge)?1:0,
    'responsavel_nome' => $PersonInCharge ,
    'responsavel_cpf' => $PersonInChargeOtherDocument];





  /*  $_vsql = " 
    id  = '". ."',
    data  = '". ."',
    lixo  = '". ."',
    nome = '". ."',
    pacient = '". ."',
    situaca = '". ."',
    sexo = '". ."',
    cpf = '". ."',
    data_nasciment = '". ."',
    rg = '". ."',
    rg_uf = '". ."',
    profissao = '". ."',
    estado_civil = '". ."',
    telefone1 = '". ."',
    telefone1_whatsapp = '". ."',
    telefone2 = '". ."',
    email = '". ."',
    mysica = '". ."',
    indicacao_tipo = '". ."',
    indicacao = '". ."',
    cep = '". ."',
    endereco = '". ."',
    numero = '". ."',
    complemento = '". ."',
    bairro = '". ."',
    estado = '". ."',
    cidade = '". ."',
    responsavel_possui = '". ."',
    responsavel_nome = '". ."',
    responsavel_sexo = '". ."',
    responsavel_datanascimento = '". ."',
    
    ";*/

    
    
    
    
    
    
    
    /*id = '".  $id ."',                
               data = '". invDate($data_cadastro) ."',      
               nome = '". addslashes($nome) ."',                                          
               sexo = '". ($sexo=="Masculino"?"M":"F") ."',                                                         
               data_nascimento = '". invDate($data_nascimento) ."',                             
               estado_civil = '". $estado_civil ."',                             
               telefone2 = '". $telefones ."',                             
               telefone1 = '". $celular ."',                             
               email = '". addslashes($email) ."',                             
               endereco = '". addslashes($endereco) ."',                             
               numero = '". $numero."',                             
               complemento = '". addslashes( utf8_encode($complemento)) ."',                             
               bairro = '". addslashes(utf8_encode($bairro)) ."',                             
               cidade = '". addslashes(utf8_encode($cidade)) ."',                             
               estado = '". addslashes(utf8_encode($estado)) ."',                             
               cep = '". str_replace([".", "-"], "", $cep)."',                             
               cpf = '". str_replace([".", "-"], "", $cpf)."',                             
               rg = '".  str_replace([".", "-"], "", $rg)."',                             
               indicacao = '". $indicacao."'";*/

  // echo $_vsql . "</br>";
 //  $sql->add($_p."pacientes", $_vsql);
 echo ".";
}
echo "terminado";
?>

