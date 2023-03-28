<?php
use Aws\Common\Facade\ElasticLoadBalancing;
require_once("../../lib/conf.php");
require_once("../../lib/classes.php");

setcookie("infoName", $_GET['instancia'], time() + 3600*24, "/");

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

$sql = new Mysql();

$pacientes = file("patient_modificado.csv");

// apaga pacientes e agendamentos
//$sql->del($_p."pacientes","");

if(1==1){
        echo "migração do drjoao </br>";
        echo "trabalha apenas com a tabela ident_pacientes </br>";
        echo "nenhum dado será apagado";
        die();
}

//pegando os pacientes da lista
$telefone;
$celular;


$id = 0;
// apaga pacientes e agendamentos
//$sql->del($_p."pacientes","");
$sql->consult($_p."pacientes", "MAX(id) as id", "");
if($sql->rows){
  $tmp = mysqli_fetch_object($sql->mysqry);
  $id = $tmp-> id;
  ++$id;
}

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
        $id_lixo,
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
    ) = explode(';', str_replace(["{", "}", "*", "(", ")"], "", $linha));          
    
    if($id == "")
        continue;

 /*   
    $_pacientes[$index] = [ 
      'id' => $id,
    'data' => addslashes($CreatedAt),
    'lixo' => addslashes($Active),
    'nome' => addslashes(utf8_encode($Name)),
    'sexo' => $Sex,
    'cpf' => $OtherDocumentId,
    'data_nascimento' => $BirthDate,
    'profissao' => $Profession,
    'estado_civil' => $CivilStatus,
    'telefone1' => $MobilePhone,
    'telefone1_whatsapp' => 0,
    'telefone2' => $OtherPhones ,
    'email' => $Email,
    'indicacao' => $IndicationSource ,
    'cep' => $CEP ,
    'endereco' => addslashes(utf8_encode($Address)),
    'numero' => $AddressNumber,
    'complemento' => addslashes(utf8_encode($AddressComplement)) ,
    'bairro' => addslashes(utf8_encode($Neighborhood)),
    'estado' => $State,
    'cidade' => $City,
    'responsavel_possui' => isset($PersonInCharge)?1:0,
    'responsavel_nome' => addslashes(utf8_encode($PersonInCharge)),
    'responsavel_cpf' => $PersonInChargeOtherDocument];
*/

        if($CreatedAt == ''){
                $CreatedAt = "now()";
        }
        $PersonInCharge = $PersonInCharge?"1":"0";
        $Active = ($Active == "X"?"1":0);
        
    $_vsql = " id = '". $id . "',
        data = '". $CreatedAt ."', 
        lixo = '". $Active ."', 
        nome = '". addslashes(utf8_encode($Name)) ."', 
        sexo = '". $Sex ."', 
        cpf = '" . str_replace(["-", "."], "", $OtherDocumentId) ."', 
        data_nascimento = '". $BirthDate ."', 
        profissao = '". $Profession ."', 
        estado_civil = '". $CivilStatus ."', 
        telefone1 = '". $MobilePhone ."',    
        telefone2 = '". $OtherPhones ."',                                  
        email = '". $Email ."',                                                    
        indicacao = '". $IndicationSource ."',                               
        cep = '". str_replace(["-", "."], "", $CEP) ."',                        
        endereco = '". addslashes(utf8_encode($Address)) ."',               
        numero = '". $AddressNumber ."',                                 
        complemento = '". addslashes(utf8_encode($AddressComplement)) ."', 
        bairro = '". addslashes(utf8_encode($Neighborhood)) ."', 
        estado = '". $State ."',                                                
        cidade = '". $City ."',                                      
        responsavel_possui = '". $PersonInCharge ."'";

        $id++;
    
        echo "+= ". $_vsql . "</br>";
        $sql->add($_p."pacientes", $_vsql);
}
echo "terminado";
?>

