<?php
	require_once("../lib/conf.php");
	require_once("../lib/classes.php");

	$sql = new Mysql();
	$usr = (object) array('id'=>1);

	$attr=array('prefixo'=>$_p,'usr'=>$usr);
	$infozap = new Whatsapp($attr);


	if(isset($_POST['ajax'])) {

		if(isset($_POST['token']) and $_POST['token']=="d048aa153c175d827a8603c60ce03ad81b01573a") {

			$rtn=array();

			$sql->consult("infodentalADM.infod_contas_onlines","*","where instancia='".$_ENV['NAME']."' and lixo=0");
			$_wts=$sql->rows?mysqli_fetch_object($sql->mysqry):'';


			if($_POST['ajax']=="wtsFoto") {

				$paciente = '';
				if(isset($_POST['id_paciente']) and is_numeric($_POST['id_paciente'])) {
					$sql->consult($_p."pacientes","id,telefone1","where id=".$_POST['id_paciente']);
					if($sql->rows) {
						$paciente=mysqli_fetch_object($sql->mysqry);
					}
				}

				if(is_object($paciente)) {


					if(!empty($paciente->telefone1)) {

						$attr=array('instance'=>'556282433773',
									'numero'=>$paciente->telefone1);

						if($infozap->getProfile($attr)) { 
							if(isset($infozap->response->pictureUrl)) {
								echo $infozap->response->pictureUrl;
								$rtn=array('success'=>true);
							} else {
								$rtn=array('success'=>false,'erro'=>'Foto não encontrada');
							}

						} else {
							$rtn=array('success'=>false,'erro'=>$infozap->erro);
						}

					} else {
						$rtn=array('success'=>false,'erro'=>'Paciente sem celular!');
					}

				} else {
					$rtn=array('success'=>false,'erro'=>'Paciente não encontrado!');
				}
			}

			http_response_code(200);
			header("Content-type: application/json");
			echo json_encode($rtn);
			die();
		}
	}

	http_response_code(401);
?>