<?php

	if(isset($_POST['ajaxSign'])) {

		$rtn = [];

		# capta dados
			$evolucao=$paciente=$resposta='';
			if(isset($_POST['id_evolucao']) and !empty($_POST['id_evolucao'])) {
				$sql->consult($_p."pacientes_evolucoes","*","where md5(id) = '".addslashes($_GET['id_evolucao'])."' and lixo=0");
				if($sql->rows) {
					$evolucao=mysqli_fetch_object($sql->mysqry);


					$sql->consult($_p."pacientes","*","where id=$evolucao->id_paciente and lixo=0");
					if($sql->rows) $paciente=mysqli_fetch_object($sql->mysqry);

					if(isset($_POST['id_resposta']) and is_numeric($_POST['id_resposta'])) {
						$sql->consult($_p."pacientes_evolucoes_anamnese","*","where id_evolucao=$evolucao->id and id=".$_POST['id_resposta']);
						if($sql->rows) $resposta=mysqli_fetch_object($sql->mysqry);
					}

				}
			}

		if($_POST['ajaxSign']=="assinatura") {

			// capta dados
				$cpf = isset($_POST['cpf']) ? numero($_POST['cpf']) : '';
				$dn = '';
				if(isset($_POST['dn']) and !empty($_POST['dn']) and strpos($_POST['dn'],'/')) {
					list($dia,$mes,$ano) = @explode("/",$_POST['dn']);
					if(checkdate($mes, $dia, $ano)) {
						$dn=$ano."-".$mes."-".$dia;
					} 
				} 
				$lat = (isset($_POST['lat']) and is_numeric($_POST['lat'])) ? $_POST['lat'] : '';
				$lng = (isset($_POST['lng']) and is_numeric($_POST['lng'])) ? $_POST['lng'] : '';
				$dispositivo = (isset($_POST['dispositivo']) and !empty($_POST['dispositivo'])) ? addslashes($_POST['dispositivo']) : '';
				$assinatura = (isset($_POST['assinatura']) and !empty($_POST['assinatura'])) ? addslashes($_POST['assinatura']) : '';
	
			// validacao
				$erro='';
				if(empty($paciente)) $erro='Paciente não encontrado';
				//else if(empty($cpf)) $erro='Preencha o campo de CPF';
				//else if(strlen($cpf)!=11) $erro='Digite um CPF com 11 dígitos';
				//else if(!verificaCpf($cpf)) $erro='CPF inválido';
				//else if(empty($dn)) $erro='Preencha o campo Data de Nascimento com dados válidos';
				else if(empty($assinatura)) $erro='Faça sua assinatura digital para assinar este documento';
				//else if($paciente->cpf != $cpf) $erro='CPF e/ou Data de Nascimento inválidos';
				//else if(strtotime($paciente->data_nascimento) != strtotime($dn)) $erro='CPF e/ou Data de Nascimento inválidos.';

			// assinatura
				if(empty($erro)) {
					$evolucaoAssinatura='';
					$sql->consult($_p."pacientes_evolucoes_assinaturas","*","where id_evolucao=$evolucao->id and id_paciente=$paciente->id");
					if($sql->rows) {
						$evolucaoAssinatura=mysqli_fetch_object($sql->mysqry);
					}

					$vSQL="data=now(),
							assinatura='".$assinatura."',
							cpf='".$paciente->cpf."',
							data_nascimento='".$paciente->data_nascimento."',
							id_evolucao=$evolucao->id,
							id_paciente=$paciente->id,
							dispositivo='$dispositivo',
							lat='$lat',
							lng='$lng',
							ip='".$_SERVER['REMOTE_ADDR']."'";

					if(is_object($evolucaoAssinatura)) {
						$sql->update($_p."pacientes_evolucoes_assinaturas",$vSQL,"where id=$evolucaoAssinatura->id");
						$id_assinatura=$evolucaoAssinatura->id;
					} else {
						$sql->add($_p."pacientes_evolucoes_assinaturas",$vSQL);
						$id_assinatura=$sql->ulid;
					}

					$sql->update($_p."pacientes_evolucoes","id_assinatura=$id_assinatura","where id=$evolucao->id");

					// realiza a geracao do pdf anexando a assinatura
					if(!generatePDF($evolucao->id,$id_assinatura)) {
						$erro='Algum erro ocorreu durante a Assinatura Digital. Favor tentar novamente!';
					}
				}

			


			// retorno
				if(!empty($erro)) {
					$rtn=array('success'=>false,'error'=>$erro);
				} else {
					$rtn=array('success'=>true);
				}
		}

		header("Content-type: application/json");
		echo json_encode($rtn);
		die();
	
	}
    $sql->consult("infodentalADM.infod_contas","*","where instancia='".$_ENV['NAME']."'");
    $infoConta=$sql->rows?mysqli_fetch_object($sql->mysqry):'';


	$assinatura='';

	if(is_object($infoConta) and is_object($evolucao)) {

		// verifica se evolucao foi assinada
		if($evolucao->id_assinatura>0) {
			$sql->consult($_p."pacientes_evolucoes_assinaturas","*","where id=$evolucao->id_assinatura and lixo=0");
			if($sql->rows) $assinatura=mysqli_fetch_object($sql->mysqry);
		}

	}
?>