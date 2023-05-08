<?php
	header("Access-Control-Allow-Origin: *");
	header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
	header("Access-Control-Allow-Headers: Content-Type, Authorization");
	header("Content-type: application/json");

	http_response_code(202);

	# includes	
		require_once("../lib/conf.php");
		$sql = new Mysql();
		$canvas = new Canvas();

		require_once("../vendor/autoload.php");
		use Aws\S3\S3Client;
		$s3 = new S3Client([
						    'version' => 'latest',
						    'endpoint' => $_scalewayS3endpoint,
						    'region'  => $_scalewayS3Region,
						    'credentials' => [
										    	'key' => $_scalewayAccessKey,
										    	'secret' => $_scalewaySecretKey
										    ],
						     'bucket_endpoint' => true
							]);

	# configuracao
		$_dir = "arqs/pacientes/arquivos/";
		$request = $_POST;		

		if(isset($request['token']) and $request['token']=="ZDNudDRsaW5mMDo4ZTMwM2I1ZDVjMTJkNjg0ZjBjN2VhZGZmNjVkMDg5Yzk3OTM4YWZj") {
			# consulta instancia
				$infoConta = '';
				if(isset($request['instancia'])) {
					$sql->consult("infod_contas","*","where instancia = '".addslashes($request['instancia'])."'");
					if($sql->rows) {
						$infoConta=mysqli_fetch_object($sql->mysqry);
						$_p=$infoConta->instancia.".ident_";
					}
				}

			# consulta paciente
				$paciente = '';
				if(is_object($infoConta) and isset($request['id_paciente']) and is_numeric($request['id_paciente'])) {
					$sql->consult($_p."pacientes","id,nome","where id = '".addslashes($request['id_paciente'])."' and lixo=0");
					if($sql->rows) {
						$paciente=mysqli_fetch_object($sql->mysqry);
					}
				}
		
			# consulta colaborador
				$colaborador = '';
				if(is_object($infoConta) and isset($request['id_colaborador']) and is_numeric($request['id_colaborador'])) {
					$sql->consult($_p."colaboradores","id,nome","where id = '".addslashes($request['id_colaborador'])."' and lixo=0");
					//echo "where id = '".addslashes($request['id_colaborador'])."' and lixo=0 => ". $sql->rows;die();
					if($sql->rows) {
						$colaborador=mysqli_fetch_object($sql->mysqry);
					}
				}

							
			if(isset($request['act'])) {

				if(is_object($infoConta)) {

					if($request['act']=="upload") {

						$erro='';
						$obs = isset($request['obs']) ? addslashes(utf8_decode($request['obs'])) : '';
						$tipo = isset($request['tipo']) ? addslashes(utf8_decode($request['tipo'])) : ''; // foto, 3d, tomografia e outros
						$obs = isset($request['obs']) ? addslashes(utf8_decode($request['obs'])) : '';

						if(empty($colaborador)) $erro='Você não está autenticado!';
						else if(!is_object($paciente)) $erro='Paciente não encontrado!';
						else if(!isset($_FILES['file'])) $erro='Arquivo não encontrado para realização do upload!';
						else if(empty($tipo)) $erro='Tipo de arquivo não definido!';


						if(empty($erro)) {

							$nome = explode(".",$_FILES['file']['name']);
							$cont=0;
							$nomeSemExtensao='';
							do {
								$nomeSemExtensao.=$nome[$cont];
								$cont++;
								if(($cont+1)==count($nome)) break;
								else $nomeSemExtensao.=".";
							} while(isset($nome[$cont]));


							$extensao = $nome[count($nome)-1];

							// registra no banco de dados
							$vSQL="data=now(),
									id_paciente=$paciente->id,
									tipo='$tipo',
									titulo='".addslashes(utf8_decode($nomeSemExtensao))."',
									id_colaborador='$colaborador->id',
									obs='".$obs."',
									extensao='$extensao'";
							$sql->add($_p."pacientes_arquivos",$vSQL);
							$id_arquivo=$sql->ulid;
							$nomeCompleto=md5($id_arquivo).".".$extensao;

							$isImage = @is_array(getimagesize($_FILES['file']['tmp_name'])) ? true : false;

							try {
							    $s3->putObject(array(
							        'Bucket'=>'infodental',
							        'Key' =>  $infoConta->instancia."/".$_dir.$nomeCompleto,
							        'SourceFile' => $_FILES['file']['tmp_name'],
							        'ACL'    => 'public-read', //for public access
							    ));

							    // se for foto, gera thumbnails
							    if($isImage) {

							    	$imgThumb = "arqs/tmp/thumb-".$infoConta->instancia.".".$extensao;


							    	$canvas->carrega($_FILES['file']['tmp_name'])
											->redimensiona("100", "100", 'crop')
											->hexa( '#FFFFFF' )
											->grava($imgThumb);

									 $s3->putObject(array(
												        'Bucket'=>'infodental',
												        'Key' =>  $infoConta->instancia."/".$_dir."thumb/".$nomeCompleto,
												        'SourceFile' => $imgThumb,
												        'ACL'    => 'public-read', //for public access
												    ));


							    }


							} catch (S3Exception $e) {
							    //code when fails
							   $erro='Algum erro ocorreu durante o envio do arquivo. Tente novamente ou entre em contato com nosso suporte!';
							}
						}


						if(empty($erro)) {

							$rtn=array('success'=>true);
						} else {
							$rtn=array('success'=>false,'error'=>$erro);
						}


					}
				} else {
					$rtn=array('success'=>false,'error'=>'Conta não encontrada');
				}

				echo json_encode($rtn);
				
			} else {
	   			//header('WWW-Authenticate: Basic realm="My Realm"');
	    		//header('HTTP/1.0 401 Unauthorized');
			}
		} else {
	   		//header('WWW-Authenticate: Basic realm="My Realm"');
	    	//header('HTTP/1.0 401 Unauthorized');
		}


?>