<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title></title>
</head>
<body>

</body>
</html>
<?php

	function telefoneInsert($tel) {
		$tel=telefone($tel);

		$cels=array(6,7,8,9);

		// verifica se e celukar
		if(in_array(substr($tel,0,1),$cels)) {

			if(strlen($tel)==8) $tel="629$tel";
		} else {
			if(strlen($tel)==8) $tel="62$tel";
		}

		return $tel;
	}

	if(isset($_GET['cmd'])) {
		require_once("../lib/conf.php");
		require_once("../lib/classes.php");
		$sql = new Mysql();
	
		$arq = file("agenda.csv");
		$cont=0;
		foreach ($arq as $value) {
			list($data,$profissional,$numCadastro,$obs,$duracao,$status,$cadeira) = explode(";",$value);
			if($cont++==0) continue;

			$aux=explode(";",$value);

			if(utf8_encode($data)!="data" and !empty($data)) {
				$data=trim($data);
				list($dt,$hr)=explode(" - ",$data);
				$dataF=invDate($dt)." ".$hr;
				if(invDate($dt)!="0000-00-00") {
					$id_profissional='';
					$sql->consult($_p."profissionais","*","where nome='".addslashes(trim(utf8_decode($profissional)))."'");
					
					if($sql->rows) {
						$p=mysqli_fetch_object($sql->mysqry);
						$id_profissional=",$p->id,";
					}
					$id_paciente=0;
					$sql->consult($_p."pacientes","*","where id='".$numCadastro."'");
					if($sql->rows) {
						$pa=mysqli_fetch_object($sql->mysqry);
						$id_paciente=$pa->id;
					}

					$id_cadeira=0;

					$sql->consult($_p."parametros_cadeiras","*","where titulo='".addslashes(trim(utf8_decode($cadeira)))."'");
					if($sql->rows) {
						$pa=mysqli_fetch_object($sql->mysqry);
						$id_cadeira=$pa->id;
					}

					$id_status=1;
					if($status=="Atendido") $id_status=5;
					else if($status=="Cliente Chegou") $id_status=2;
					else if($status=="Faltou") $id_status=3;
					else if($status=="Desmarcou") $id_status=4;
					else if($status=="Confirmado") $id_status=2;
					else if($status=="Confirmou") $id_status=2;
					else if($status=="Confirmar") $id_status=1;
					else if($status=="Em Atendimento") $id_status=2;

					if($id_paciente==0) continue;

					$vSQL="data='".$dataF."',
							agenda_data='".$dataF."',
							agenda_duracao='".(is_numeric($duracao)?$duracao:0)."',
							profissionais='".$id_profissional."',
							id_paciente='".$id_paciente."',
							obs='".utf8_decode(addslashes($obs))."',
							id_cadeira='".$id_cadeira."',
							id_status='".$id_status."'";

					$sql->consult($_p."agenda","*","where agenda_data='".$dataF."' and id_paciente=$id_paciente");
					if($sql->rows==0) {
						$sql->add($_p."agenda",$vSQL);
					}
				}
			}

			/*if($dataCadastro!="Data do Cadastro") {


				$sql->consult($_p."cidades","*","where titulo='".utf8_decode($cidade)."'");
				if($sql->rows) {
					$x=mysqli_fetch_object($sql->mysqry);
					$id_cidade=$x->id;
				} else $id_cidade=0;

				$vSQL="nome='".utf8_decode($nome)."',
						sexo='".($sexo=="Feminino"?"F":"M")."',
						telefone1='".addslashes(telefoneInsert($celular))."',
						telefone2='".addslashes(telefoneInsert($telefone))."',
						email='".utf8_decode(addslashes(($email)))."',
						data_nascimento='".addslashes(($dn))."',
						endereco='".utf8_decode(addslashes(($endereco)))."',
						numero='".utf8_decode(addslashes(($numero)))."',
						complemento='".utf8_decode(addslashes(($complemento)))."',
						bairro='".utf8_decode(addslashes(($bairro)))."',
						cidade='".utf8_decode(addslashes(($cidade)))."',
						id_cidade='".addslashes(($id_cidade))."',
						estado='".addslashes(($estado))."',
						cep='".addslashes(($cep))."',
						rg='".addslashes(($rg))."',
						cpf='".addslashes(is_numeric(telefone($cpf))?telefone($cpf):"")."'
						";

				if(is_numeric($numCadastro)) $vSQL.=",id='$numCadastro'";



				echo $value."<BR>";
				echo $vSQL."<hr>";

				$sql->add($_p."pacientes",$vSQL);

			}*/

			# code...
		}	

		/*$arq = file("pacientes.csv");
		foreach ($arq as $value) {
			list($numCadastro,$dataCadastro,$nome,$telefone,$celular,$telefoneComercial,$email,$dn,$endereco,$numero,$complemento,$bairro,$cidade,$estado,$cep,$cpf,$rg,$sexo,$especialidades) = explode(";",$value);
			
			if($dataCadastro!="Data do Cadastro") {


				$sql->consult($_p."cidades","*","where titulo='".utf8_decode($cidade)."'");
				if($sql->rows) {
					$x=mysqli_fetch_object($sql->mysqry);
					$id_cidade=$x->id;
				} else $id_cidade=0;

				$vSQL="nome='".utf8_decode($nome)."',
						sexo='".($sexo=="Feminino"?"F":"M")."',
						telefone1='".addslashes(telefoneInsert($celular))."',
						telefone2='".addslashes(telefoneInsert($telefone))."',
						email='".utf8_decode(addslashes(($email)))."',
						data_nascimento='".addslashes(($dn))."',
						endereco='".utf8_decode(addslashes(($endereco)))."',
						numero='".utf8_decode(addslashes(($numero)))."',
						complemento='".utf8_decode(addslashes(($complemento)))."',
						bairro='".utf8_decode(addslashes(($bairro)))."',
						cidade='".utf8_decode(addslashes(($cidade)))."',
						id_cidade='".addslashes(($id_cidade))."',
						estado='".addslashes(($estado))."',
						cep='".addslashes(($cep))."',
						rg='".addslashes(($rg))."',
						cpf='".addslashes(is_numeric(telefone($cpf))?telefone($cpf):"")."'
						";

				if(is_numeric($numCadastro)) $vSQL.=",id='$numCadastro'";



				echo $value."<BR>";
				echo $vSQL."<hr>";

				$sql->add($_p."pacientes",$vSQL);

			}

			# code...
		}*/

	}
?>