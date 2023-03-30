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


if(1==1){
    echo "migração da oralsalute </br>";
    echo "trabalha apenas com a tabela ident_pacientes </br>";
    echo "nenhum dado será apagado";
    die();
}

$sql = new Mysql();

$pacientes = file("relatorio_total_pacientes_cadastrados_modificado.csv");

$id = 0;
// apaga pacientes e agendamentos
//$sql->del("oralsalute.ident_pacientes", "");

/*$sql->consult($_p."pacientes", "MAX(id) as id", "");
if($sql->rows){
  $tmp = mysqli_fetch_object($sql->mysqry);
  $id = $tmp->id;
  ++$id;
}*/


//nome,???,cpf,???,Data-cadastro,Data-nascimento,endenreco,bairro ,cidade,uf,cep,telefones,e-mail,status

//pegando os pacientes da lista
$telefone;
$celular;

foreach($pacientes as $linha){
    list(
        $id_lixo,               
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
    if(!empty($estado))
        $endereco .= ", $estado";

    $datanascimento = invDate($data_nascimento);
    $datacadastro   = invDate($data_cadastro);
    $celular = str_replace(["(", ")", "-", " "], "", $celular);//todos os telefones sempre tem um espaço depois do )
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
               nome = '". (utf8_decode($nome)) ."',                                          
               sexo = '". ($sexo=="Masculino"?"M":"F") ."',                                                         
               data_nascimento = '". invDate($data_nascimento) ."',                             
               estado_civil = '". $estado_civil ."',                             
               telefone2 = '". $telefones ."',                             
               telefone1 = '". $celular ."',                             
               email = '". addslashes($email) ."',                             
               endereco = '". addslashes(utf8_decode($endereco)) ."',                             
               numero = '". $numero."',                             
               complemento = '". addslashes( utf8_decode($complemento)) ."',                             
               bairro = '". addslashes(utf8_decode($bairro)) ."',                             
               cidade = '". addslashes(utf8_decode($cidade)) ."',                             
               estado = '". addslashes(utf8_decode($estado)) ."',                             
               cep = '". str_replace([".", "-"], "", $cep)."',                             
               cpf = '". str_replace([".", "-"], "", $cpf)."',                             
               rg = '".  str_replace([".", "-"], "", $rg)."',                             
               indicacao = '". $indicacao."'";

   $id++;
   echo $_vsql . "</br>";
   $sql->add("oralsalute.ident_pacientes", $_vsql);
}
?>

