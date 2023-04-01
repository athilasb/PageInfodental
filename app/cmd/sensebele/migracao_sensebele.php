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
$_p = "sensebele.ident_";

$pacientes = file("Patient.csv");

// apaga pacientes e agendamentos
//$sql->del($_p."pacientes","");

if(1==1 ){
        echo "migração sensebele </br>";
        echo "trabalha apenas com a tabela ident_pacientes </br>";
        echo "pacientes serão apagados";
        die();
}

//pegando os pacientes da lista
$telefone;
$celular;


$id = 0;
// apaga pacientes e agendamentos
/*
$sql->consult($_p."pacientes", "MAX(id) as id", "");
if($sql->rows){
  $tmp = mysqli_fetch_object($sql->mysqry);
  $id = $tmp-> id;
  ++$id;
}*/

$sql->del("sensebele.ident_pacientes","");


foreach($pacientes as $linha){


    list(
        $Active,
$ActiveSheet_COSMETIC_BOTULINUM_TOXIN,
        $Address,
        $AddressComplement,
        $AddressNumber,
        $Age,
        $BirthDate,
        $City,
        $CivilStatus,
$CreateAtomicDate,
$CreatedAt,
        $Deleted,
        $DocumentId,
$Education,
        $Email,
$FatherOtherDocument,
        $HowDidMeet,
        $IndicationSource,
        $MobilePhone,
        $Name,
$NameSearch,
        $Neighborhood,
$NickName,
$Notes,
$OnlineScheduling,
        $OtherDocumentId,
$OtherDocumentIdSearch,
        $OthersSource,
        $PersonInCharge,
        $PersonInChargeOtherDocument,
$ProfileImage,
$SK_BirthDay,
        $Sex,
$SpecialtyType_COSMETIC_BOTULINUM_TOXIN,
$Type,
$Zip,
$_AccessPath,
        $id_lixo,
        $State,
$z_Create_UserId,
$z_Inserted_Date,
$z_Inserted_Server_Tool,
$z_Inserted_UserId,
$z_LastChange_Date,
$z_LastChange_Server_Tool,
$z_LastChange_UserId,
$z_Server_Tool
    )    = explode(';', str_replace(["{", "}", "*", "(", ")"], "", $linha));          
    
    

        if($CivilStatus == 'MARRIED'){
                $CivilStatus = "Casado(a)";
        }else{
                $CivilStatus = "Solteiro(a)";
        }
        if($CreatedAt == ''){
                $CreatedAt = "now()";
        }
        $PersonInCharge = $PersonInCharge?"1":"0";
        $Active = ($Active == "X"?"0":"1f");
        
    $_vsql = " 
        data = '". $CreatedAt ."', 
        lixo = '". $Active ."', 
        nome = '". addslashes(utf8_decode($Name)) ."', 
        sexo = '". $Sex ."', 
        cpf = '" . str_replace(["-", "."], "", $OtherDocumentId) ."', 
        data_nascimento = '". $BirthDate ."', 
        estado_civil = '". $CivilStatus ."', 
        telefone1 = '". str_replace(["-", "\n"], "", $MobilePhone) ."',    
        email = '". $Email ."',                                                    
        indicacao = '". $IndicationSource ."',                               
        endereco = '". addslashes(utf8_decode($Address)) ."',               
        numero = '". $AddressNumber ."',                                 
        complemento = '". addslashes(utf8_decode($AddressComplement)) ."', 
        bairro = '". addslashes(utf8_decode($Neighborhood)) ."', 
        estado = '". $State ."',                                                
        cidade = '". $City ."',                                      
        responsavel_possui = '". $PersonInCharge ."'";


        echo "+= ". $_vsql . "</br>";
        $sql->add($_p."pacientes", $_vsql);
}
echo "terminado";
?>

