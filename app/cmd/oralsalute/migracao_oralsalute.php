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

$_p = "oralsalute.ident_";
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


//nome,???,cpf,???,Data-cadastro,Data-nascimento,endenreco,bairro ,cidade,uf,cep,telefones,e-mail,status

//pegando os pacientes da lista
$telefone;
$celular;
$id = 0;

foreach($pacientes as $linha){
    list(
        $id,               
        $data_cadastro,     
        $nome,                                         
        $observacoes,                            
        $sexo,                                                        
        $dentista, //o cliente só precisa do paciente,                             
        $data_nascimento,                            
        $estado_civil,                            
        $telefone,                            
        $celular,                            
        $telefone_comercial,                            
        $email,                            
        $endereco,                            
        $numero,                            
        $complemento,                            
        $bairro,                            
        $cidade,                            
        $estado,                            
        $cep,                            
        $cpf,                            
        $rg,                            
        $convenio,                            
        $plano, //inutil                            
        $especialidades, //inutil                            
        $indicacao                                                        
    ) = explode(',', str_replace("\"", "", $linha));                            


    if(!empty($bairro))
        $endereco .= ", $bairro";
    if(!empty($cidade))
        $endereco .= ", $cidade";
    if(!empty($uf))
        $endereco .= ", $uf";

    $datanascimento = invDate(utf8_encode($data_nascimento));
    $datacadastro   = invDate($data_cadastro);

    
    
    $celular = str_replace(["(", ")", "-", " "], "", $celular);
                                    //todos os telefones sempre tem um espaço depois do )
    $telefones =str_replace(["(", ") ", "-"], "", $telefone . ", " . $telefone_comercial);
    $index = strtolowerWLIB(str_replace(" ", "", tirarAcentos($nome)));
    //verificando por nomes repetidos
    if (isset($_pacientes[$index])) {
        $escolhido = 0; //1 mais recente, -1 já cadastrado;
        $datas = explode("/", $data_cadastro);
        $datas = intval($datas[2] . $datas[1] . $datas[0]);
        $datas_existentes = array();
        var_dump($datas_existentes);
        if(($_pacientes[$index]['data_nascimento'])!=""){
            $datas_existentes =  explode("/", $_pacientes[$index]['data_nascimento']);
        }else{
            $escolhido = -1;
        }
        if($escolhido == 0){
        
            $datas_existentes = intval($datas_existentes[2] . $datas_existentes[1] . $datas_existentes[0]);

            switch ( $datas <=> $datas_existentes ) {
                case 1:
                    $escolhido = -1;  
                    //data 
                    break;
                case 0:
                    $escolhido = -1;
                    if($id < intval($p_acientes[$index]['id'])){ //data iguais, o que vale é o tamanho do id 
                        $escolhido = 1;  
                        continue 2;
                    }
                    break;
                case -1: //mais recente já está no sistema
                    $escolhido = 1;  
                    continue 2;
                    break;
            }
        }

       // echo "<br>='" . $index . "' '$nome' " . ': ' . $escolhido;

    }
  /*  
    $_pacientes[$index] = array(
        'id = '".  $id,               
        'data = '". $data_cadastro,     
        'nome = '". $nome,                                         
        'sexo = '". $sexo,                                                        
        'data_nascimento = '". $data_nascimento,                            
        'estado_civil = '". $estado_civil,                            
        'telefone2 = '". $telefones,                            
        'telefone1 = '". $celular,                            
        'email = '". $email,                            
        'endereco = '". $endereco,                            
        'numero = '". $numero,                            
        'complemento = '". $complemento,                            
        'bairro = '". $bairro,                            
        'cidade = '". $cidade,                            
        'estado = '". $estado,                            
        'cep = '". $cep,                            
        'cpf = '". $cpf,                            
        'rg = '". $rg,                            
        'indicacao = '". $indicacao );                    
*/

    $_vsql = " id = '".  $id ."',                
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
               indicacao = '". $indicacao."'";

   echo $_vsql . "</br>";
   $sql->add($_p."pacientes", $_vsql);
}
echo "terminado";
?>

