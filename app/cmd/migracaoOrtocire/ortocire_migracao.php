<?php
//migração terminada em 29/03/2023; walker;
//bug corrigido,
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


if($_GET['instancia'] != "ortocire"){
    ?>
    
    <script> 
        alert("ATENÇÃO, INSTANCIA NÃO CONFIGURADA");
    </script>

    <h1>ATENÇÃO, INSTANCIA NÃO CONFIGURADA</h1>
    
    <?php
}

print_r($_ENV['MYSQL_DB'] . "<br>");

echo "migração da ortocire </br>";
echo "trabalha apenas com a tabela ident_pacientes </br>";
echo "todos os pacientes serão apagados <br>";


if(1==1){
    die();
}
$sql = new Mysql();

$sql->del("ortocire.ident_pacientes", "");


$pacientes = file("pacientes_27-03-2023.csv");

foreach($pacientes as $linha){

    list(
        $ID, 
        $Numero_Paciente,
        $Nome,
        $CPF,
        $RG,
        $Sexo,
        $CEP,
        $Endereco,
        $Bairro,
        $Cidade,	
        $UF,
        $Celular,
        $Telefone,
        $Email,
        $Data_Nascimento,
        $Numero_Prontuario,
        $Responsavel,	
        $CPF_Responsavel,	
        $Data_Nascimento_Responsavel,
        $CPF_Titular_Plano,
        $Carteirinha_Plano,
        $Nome_plano,
        $Observacao
    ) = explode(",", $linha);

    if(!empty($Bairro))
        $Endereco .= ", " . $Bairro;
    if(!empty($Cidade))
        $Endereco .= ", " . $Cidade;
    if(!empty($UF))
        $Endereco .= ", " . $UF;

    $responsavel_possui = ($Responsavel != "")?1:0 ;
    print_r( $linha);

    $_vsql = " id = '".  $Numero_Paciente ."',                
               data = now(),      
               nome = '". addslashes(utf8_decode($Nome)) ."',                                          
               sexo = '". $Sexo ."',                                                         
               data_nascimento = '". $Data_Nascimento ."',                             
               telefone1 = '". $Celular ."',                             
               email = '". addslashes($Email) ."',                             
               endereco = '". addslashes(utf8_decode($Endereco)) ."',                             
               bairro = '". addslashes(utf8_decode($Bairro)) ."',                             
               cidade = '". addslashes(utf8_decode($Cidade)) ."',                             
               estado = '". addslashes(utf8_decode($UF)) ."',                             
               cep = '". str_replace([".", "-"], "", $CEP)."',                             
               cpf = '". str_replace([".", "-"], "", $CPF)."',                             
               rg = '".  str_replace([".", "-"], "", $RG)."', 
               responsavel_possui = '". $responsavel_possui ."', 
               responsavel_nome = '". $Responsavel ."',
               responsavel_datanascimento = '". $Data_Nascimento_Responsavel ."',
               responsavel_cpf = '". $CPF_Responsavel ."'";        
    
    $sql->add("ortocire.ident_pacientes", $_vsql);
    echo "<hr>";
}
echo "<br>terminado"

?>