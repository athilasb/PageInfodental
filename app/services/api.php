<?php
	
	
	require_once '../lib/conf.php';
	require_once '../lib/class/classMysql.php';
	require_once '../vendor/autoload.php';

	use Dompdf\Dompdf;
	$dompdf = new Dompdf(array('isRemoteEnabled'=>true));

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


	$sql = new Mysql();
	$id_evolucao=1990;


	$token='ee7a1554b556f657e8659a56d1a19c315684c39d';
	$request = file_get_contents('php://input');
	$request = json_decode($request);

	$infoConta = '';
	if(isset($request->infoConta) and !empty($request->infoConta)) {
		$sql->consult("infodentalADM.infod_contas","instancia","where instancia='".addslashes($request->infoConta)."'");
		if($sql->rows) {
			$infoConta = mysqli_fetch_object($sql->mysqry);
		}
	}

	if(isset($request->token) and $request->token==$token) {

		require_once("../lib/class/classWhatsapp.php");
		require_once("../lib/class/classMysql.php");
		$sql = new Mysql();

		$attr=array('prefixo'=>$_p,'usr'=>(object)array('id'=>0));
		$infozap = new Whatsapp($attr);

		header("Content-type: application/json");

		$rtn = [];

		if(is_object($infoConta)) {

			$_p=$infoConta->instancia.".ident_";

			if(isset($request->method)) {


				if($request->method=='generatePDF') {

					$erro='';

					$enviaWhatsapp = (isset($request->enviaWhatsapp) and $request->enviaWhatsapp==1) ? 1 : 0; 

					$evolucao=$evolucaoTipo=$paciente='';
					if(isset($request->id_evolucao) and is_numeric($request->id_evolucao)) {
						$sql->consult($_p."pacientes_evolucoes","*","where id=$request->id_evolucao"); 

						if($sql->rows) {
							$evolucao = mysqli_fetch_object($sql->mysqry);

							$sql->consult($_p."pacientes_evolucoes_tipos","*","where id='$evolucao->id_tipo'");
							if($sql->rows) $evolucaoTipo=mysqli_fetch_object($sql->mysqry);

							$sql->consult($_p."pacientes","id,telefone1,nome","where id=$evolucao->id_paciente");
							if($sql->rows) $paciente=mysqli_fetch_object($sql->mysqry);
						}
					}

					// verifica se whatsapp está conectado
					$where="where instancia='".$_ENV['NAME']."' and lixo=0 order by data desc limit 1";
					$sql->consult("infodentalADM.infod_contas_onlines","*",$where);
					$conexao=$sql->rows?mysqli_fetch_object($sql->mysqry):'';

					//var_dump($conexao);
					
					if(empty($evolucao)) $erro='Evolução não encontrada!';
					else if(empty($evolucaoTipo)) $erro='Tipo de evolução não encontrado!';
					else if(empty($paciente)) $erro='Paciente não encontrado!';
					else if($enviaWhatsapp==1 and is_object($paciente) and empty($paciente->telefone1)) $erro='Paciente não possui celular cadastrado para envio do whatsapp';
					else if($enviaWhatsapp==1 and empty($conexao)) $erro='Infozap não conectado';
					else if($enviaWhatsapp==1 and is_object($conexao) and $conexao->versao!=2) $erro='Versão do Infozap não suporta envio de arquivos. Favor atualize seu Infozap!';



					if(empty($erro)) {
						//var_dump($evolucao);

						// Print-header
							$sql->consult($_p."clinica","*","where id=1");
							$unidade=mysqli_fetch_object($sql->mysqry);

							$unidadeTelefones='';
							if(!empty($unidade->whatsapp) or !empty($unidade->telefone)) {
								$unidadeTelefones='<tr><td>';
								if(!empty($unidade->whatsapp)) $unidadeTelefones.=($unidade->whatsapp)."&nbsp;&nbsp;&nbsp;&nbsp;";
								if(!empty($unidade->telefone)) $unidadeTelefones.=mask($unidade->telefone);
								$unidadeTelefones.='</td></tr>';
							}


							$unidadeEndereco = !empty($unidade->endereco) ? '<tr><td>'.utf8_encode($unidade->endereco).'</td></tr>' : '';

							$unidadeDigital = '';
							if(!empty($unidade->site) or !empty($unidade->instagram)) {

								$unidadeDigital='<tr><td>';
								if(!empty($unidade->site)) $unidadeDigital.=$unidade->site."&nbsp;&nbsp;&nbsp;&nbsp;";
								if(!empty($unidade->instagram)) $unidadeDigital.="@".$unidade->instagram."&nbsp;&nbsp;&nbsp;&nbsp;";
								$unidadeDigital.='</td></tr>';
							}

						# PEDIDO DE EXAME
						if($evolucao->id_tipo==6) {

							$sql->consult($_p."colaboradores","id,nome","where id=$evolucao->id_profissional");
							if($sql->rows) {
								$solicitante=mysqli_fetch_object($sql->mysqry);
							}

							$clinicaTitulo=$clinicaTelefone=$clinicaEndereco=$clinicaComoChegar='';
							$sql->consult($_p."parametros_fornecedores","*","where id=$evolucao->id_clinica and lixo=0");
							if($sql->rows) {
								$clinica=mysqli_fetch_object($sql->mysqry);

								if($clinica->tipo_pessoa=='PF') {
									$clinicaTitulo='<small>Nome</small><br />'.utf8_encode($clinica->nome);
								} else {
									$clinicaTitulo='<small>Razão Social</small><br />'.utf8_encode($clinica->razao_social);
								}

								$clinicaTelefone=mask($clinica->telefone1);
								$clinicaEndereco=utf8_encode($clinica->endereco);

								if(!empty($clinica->lat) and !empty($clinica->lng)) {
									$lat=$clinica->lat;
									$lng=$clinica->lng;
									$clinicaComoChegar='<small>Como Chegar</small><br /><a href="https://www.google.com/maps/search/'.$lat.','.$lng.'">Google Maps</a> &nbsp;&nbsp;
														<a href="https://www.waze.com/pt-BR/live-map/directions?locale=pt_BR&utm_source=waze_app&to=ll.'.$lat.'%2C'.$lng.';">Waze</a>';
								}
							}

							$idade=$sexo=$celular='';
							$sql->consult($_p."pacientes","*","where id=$evolucao->id_paciente");
							if($sql->rows) {
								$paciente=mysqli_fetch_object($sql->mysqry);
								$idade=idade($paciente->data_nascimento).' anos';
								$sexo=$paciente->sexo=="M"?"Masculino":"Feminino";
								$celular=mask($paciente->telefone1);
							}

							$examesIds=array(0);
							$sql->consult($_p."pacientes_evolucoes_pedidosdeexames","*","where id_evolucao=$evolucao->id and lixo=0");
							
							while($x=mysqli_fetch_object($sql->mysqry)) {
								$exames[]=$x;
								$examesIds[]=$x->id_exame;
							}

							$sql->consult($_p."parametros_examedeimagem","*","where id IN (".implode(",",$examesIds).")");
							while($x=mysqli_fetch_object($sql->mysqry)) {
								$_exames[$x->id]=$x;
							}


							$cont=1;
							$examesSolicitados='';
							foreach($exames as $x) {
								if(isset($_exames[$x->id_exame])) {
									$regiao='GERAL';
									if(isset($x->opcao) and !empty($x->opcao)) {
										$opcoes=explode(",",utf8_encode($x->opcao));
										$regiao='';
										foreach($opcoes as $opcao) {
											$regiao.=" ".$opcao.", ";
										}
										$regiao=substr($regiao,0,strlen($regiao)-2);
									}
									$examesSolicitados.='<tr><td>';
									$examesSolicitados.='<small>'.$cont++.') '.utf8_encode($_exames[$x->id_exame]->titulo).'</small><br />
														Região: '.$regiao.'<br />';
									
									if(!empty($x->obs)) $examesSolicitados.='Obs: '.utf8_encode($x->obs).'<br />';
									$examesSolicitados.='</td></tr>';
									
								}
							}
							
							

							$html='
									<html>
									<head>
										<style>		
											@import url("http://163.172.187.183:5000/css/pdf.css");
										</style>
									</head>
									<body>							
										<header>
											<img src="http://163.172.187.183:5000/img/logo-cliente.png" />
										</header>
										<footer>
											<table>
											'.$unidadeTelefones.$unidadeEndereco.$unidadeDigital.'
											</table>
										</footer>
										<main>									

											<h1>Pedido de Exame Complementar</h1>

											<p style="text-align:center;">'.date('d/m/Y',strtotime($evolucao->data)).'</p>

											<table>
												<tr>
													<td><small>'.utf8_encode($paciente->nome).'</small></td>
													<td>'.$idade.'</td>
													<td>'.$sexo.'</td>
													<td>'.$celular.'</td>
												</tr>
											</table>

											<h2>Clínica Radiológica</h2>

											<table>
												<tr>
													<td>'.$clinicaTitulo.'</td>
													<td><small>Telefone</small><br />'.$clinicaTelefone.'</td>
													<td><small>Solicitado por</small><br />'.utf8_encode($solicitante->nome).'</td>
												</tr>
												<tr>
													<td colspan="3">
														<small>Endereço</small><br />
														'.$clinicaEndereco.'
													</td>
												</tr>
												<tr>
													<td colspan="3">
														'.$clinicaComoChegar.'
													</td>
												</tr>
											</table>

											<h2>Exames Solicitados</h2>

											<table>
												'.$examesSolicitados.'
											</table>
										</main>
									</body>
									</html>
										
								';


							$dompdf->loadHtml($html);
							$dompdf->setPaper('A4', 'portrait');
							$dompdf->render();
							//$dompdf->stream();

							$output = $dompdf->output();
							file_put_contents('arqs/temp.pdf', $output);
						}

						# RECEITUARIO
						else if($evolucao->id_tipo==7) {

							$sql->consult($_p."colaboradores","id,nome,cro,uf_cro","where id=$evolucao->id_profissional");
							if($sql->rows) {
								$solicitante=mysqli_fetch_object($sql->mysqry);
							}

							$sql->consult($_p."clinica","endereco","");
							if($sql->rows) {
								$clinica=mysqli_fetch_object($sql->mysqry);
							}

							$idade=$sexo=$celular='';
							$sql->consult($_p."pacientes","*","where id=$evolucao->id_paciente");
							if($sql->rows) {
								$paciente=mysqli_fetch_object($sql->mysqry);
								$idade=idade($paciente->data_nascimento).' anos';
								$sexo=$paciente->sexo=="M"?"Masculino":"Feminino";
								$celular=mask($paciente->telefone1);
							}

							$controleEspecial=false;
							$receitas=array();
							$sql->consult($_p."pacientes_evolucoes_receitas","*","where id_evolucao=$evolucao->id and lixo=0");
							while($x=mysqli_fetch_object($sql->mysqry)) {
								$receitas[]=$x;

								if($x->controleespecial==1) $controleEspecial=true;
							}


							$cont=1;
							$receitaSolicitada='';
							foreach($receitas as $x) {
								$regiao='GERAL';
								if(isset($x->opcao) and !empty($x->opcao)) {
									$opcoes=explode(",",utf8_encode($x->opcao));
									$regiao='';
									foreach($opcoes as $opcao) {
										$regiao.=" ".$opcao.", ";
									}
									$regiao=substr($regiao,0,strlen($regiao)-2);
								}
								$receitaSolicitada.='<tr><td>';
								$receitaSolicitada.='<small>'.$cont++.') '.utf8_encode($x->medicamento).'</small><span style="float:right"><small>'.($x->quantidade." ".(isset($_medicamentosTipos[$x->tipo])?$_medicamentosTipos[$x->tipo]:$x->tipo)).'</small></span>';
								$receitaSolicitada.='<br /><small><span style="font-weight:normal">'.utf8_encode($x->posologia).'</span></small>';
								
								$receitaSolicitada.='</td></tr>';
									
								
							}
							


							$receitaControleEspecial='';
							$receitaTitulo='Receituário Simples';
							if($controleEspecial===true) {
								$receitaTitulo='Receituário Especial';
								$receitaControleEspecial='<br /><br /><div>
																<table style="width:47%;float:left;overflow:hidden;">
																	<tr>
																		<td>
																			<h2>Identificação do Comprador</h2>
																			<span style="font-size:0.8em">
																				<small>NOME COMPLETO</small><br /><br />
																				<small>RG/ÓRGÃO EMISSOR</small><br /><br />
																				<small>ENDEREÇO COMPLETO</small><br /><br /><BR /><BR />
																				<small>CIDADE-UF</small><br /><br />
																				<small>TELEFONE</small><br /><br />
																			</span>
																		</td>
																	</tr>
																</table>
																<table style="width:47%;float:right;overflow:hidden;">
																	<tr>
																		<td>
																			<h2>Identificação do Fornecedor</h2>
																			<span style="font-size:0.8em">
																				<small>NOME COMPLETO</small><br /><br />
																				<small>RG/ÓRGÃO EMISSOR</small><br /><br />
																				<small>ENDEREÇO COMPLETO</small><br /><br /><BR /><BR />
																				<small>CIDADE-UF</small><br /><br />
																				<small>TELEFONE</small><br /><br />
																			</span>
																		</td>
																	</tr>
																</table>
															</div><br /><br />';

							} 

							$html='
									<html>
									<head>
										<style>		
											@import url("http://163.172.187.183:5000/css/pdf.css");
										</style>
									</head>
									<body>							
										<header>
											<img src="http://163.172.187.183:5000/img/logo-cliente.png" />
										</header>
										<footer>
											<table>
											'.$unidadeTelefones.$unidadeEndereco.$unidadeDigital.'
											</table>
										</footer>
										<main>									

											<h1>'.$receitaTitulo.'</h1>

											<table>
												<tr>
													<td><small>'.utf8_encode($paciente->nome).'</small></td>
													<td>'.$idade.'</td>
													<td>'.$sexo.'</td>
													<td>'.$celular.'</td>
												</tr>
											</table>

											<h2>Prescrição</h2>

											<table>
												'.$receitaSolicitada.'
											</table>
											<br /><br />
											<table>
												<tr>
													<td><small>Nome do Dentista</small><br /><span style="font-weght:normal;font-size:0.85em">'.utf8_encode($solicitante->nome).'</span></td>
													<td><small>CRO</small><br /><span style="font-weght:normal;font-size:0.85em">'.utf8_encode($solicitante->cro).'</span></td>
													<td><small>UF</small><br /><span style="font-weght:normal;font-size:0.85em">'.utf8_encode($solicitante->uf_cro).'</span></td>
												</tr>
												<tr>
													<td style="max-width:300px;"><b>Local de Atendimento</b><br /><span style="font-weght:normal;font-size:0.85em">'.utf8_encode($clinica->endereco).'</span></td>
													<td><b>Data de Emissão</b><br /><span style="font-weght:normal;font-size:0.85em">'.date('d/m/Y',strtotime($evolucao->data)).'</span></td>
												</tr>
											</table>
											'.$receitaControleEspecial.'
										</main>
									</body>
									</html>
										
								';


							$dompdf->loadHtml($html);
							$dompdf->setPaper('A4', 'portrait');
							$dompdf->render();
							//$dompdf->stream();

							$output = $dompdf->output();
							file_put_contents('arqs/temp.pdf', $output);

							
							$_dirReceituario="arqs/pacientes/receituarios/";
							$uploadPathFile=$infoConta->instancia."/".$_dirReceituario.sha1($evolucao->id).".pdf";

							try {
								 $s3->putObject(array(
												        'Bucket'=>$_scalewayBucket,
												        'Key' =>  $uploadPathFile,
												        'SourceFile' => 'arqs/temp.pdf',
												        'ACL'    => 'public-read', //for public access
												    ));

								 $sql->update($_p."pacientes_evolucoes","s3=1","where id=$evolucao->id");
							} catch (S3Exception $e) {
							    $erro='Algum erro ocorreu durante a persistência do PDF em nosso cloud de armazenamento. Favor contactar nossa equipe de suporte!';
							}
							//$uploaded=$wasabiS3->putObject(S3::inputFile('arqs/temp.pdf',true),$_wasabiBucket,$uploadPathFile,S3::ACL_PUBLIC_READ);

						}

						# ATESTADO
						else if($evolucao->id_tipo==4) {

						}

					} 


					// dispara whatsapp com o PDF gerado se parametro enviaWhatsapp = 1
					if($enviaWhatsapp and empty($erro)) {

						// verifica se possui conexao
						$conexao='';
						$sql->consult("infodentalADM.infod_contas_onlines","*","where instancia='".$_ENV['NAME']."' and lixo=0");
						if($sql->rows) $conexao=mysqli_fetch_object($sql->mysqry);
						


						$numero=$paciente->telefone1;

						// teste
						/*$attr=array('numero'=>$numero,
									'mensagem'=>'oi',
									'id_conexao'=>$conexao->id);
						echo $infozap->enviaMensagem($attr)?1:$infozap->erro;
						die();*/
					
						// envia whatsapp
						$attr=array('numero'=>$numero,
									'arq'=>'arqs/temp.pdf',
									'documentName'=>utf8_encode($evolucaoTipo->titulo)." ".date('d/m/Y',strtotime($evolucao->data))." - ".utf8_encode($paciente->nome).".pdf",
									'id_conexao'=>$conexao->id);

						if($infozap->enviaArquivo($attr)) {

							$rtn=array('success'=>true,'numero'=>mask($numero));
						} else {
							$rtn=array('success'=>false,'error'=>$infozap->erro);
						}
					} 


					if(empty($erro)) {
						$rtn=array('success'=>true);
					} else {
						$rtn=array('success'=>false,'error'=>$erro);
					}


				} 
				else if($request->method=="sendWhatsapp") {
					$evolucao=$evolucaoTipo=$paciente='';
					if(isset($request->id_evolucao) and is_numeric($request->id_evolucao)) {
						$sql->consult($_p."pacientes_evolucoes","*","where id=$request->id_evolucao"); 

						if($sql->rows) {
							$evolucao = mysqli_fetch_object($sql->mysqry);

							$sql->consult($_p."pacientes_evolucoes_tipos","*","where id='$evolucao->id_tipo'");
							if($sql->rows) $evolucaoTipo=mysqli_fetch_object($sql->mysqry);

							$sql->consult($_p."pacientes","id,telefone1,nome","where id=$evolucao->id_paciente");
							if($sql->rows) $paciente=mysqli_fetch_object($sql->mysqry);
						}
					}


					// verifica se possui conexao
					$conexao='';
					$sql->consult("infodentalADM.infod_contas_onlines","*","where instancia='".$_ENV['NAME']."' and lixo=0");
					if($sql->rows) $conexao=mysqli_fetch_object($sql->mysqry);

					if(empty($evolucao)) $erro='Evolução não encontrada!';
					else if(empty($evolucaoTipo)) $erro='Tipo de evolução não encontrado!';
					else if(empty($paciente)) $erro='Paciente não encontrado!';
					else if(is_object($paciente) and empty($paciente->telefone1)) $erro='Paciente não possui celular cadastrado para envio do whatsapp';
					else if(empty($conexao)) $erro='Infozap não conectado';
					else if(is_object($conexao) and $conexao->versao!=2) $erro='Versão do Infozap não suporta envio de arquivos. Favor atualize seu Infozap!';

					if(empty($erro)) {

						// Se Receituario
						if($evolucao->id_tipo==7) {


							$pdf="$_scalewayS3endpoint/".$infoConta->instancia."/arqs/pacientes/receituarios/";

							// verifica se foi assinado
							if($evolucao->receita_assinada=="0000-00-00 00:00:00") {
								$pdf.=sha1($evolucao->id).".pdf";
							} else {
								$pdf.="assinados/".sha1($evolucao->id).".pdf";
							}

							// envia whatsapp
							$attr=array('numero'=>$paciente->telefone1,
										'arq'=>$pdf,
										'documentName'=>utf8_encode($evolucaoTipo->titulo)." ".date('d/m/Y',strtotime($evolucao->data))." - ".utf8_encode($paciente->nome).".pdf",
										'id_conexao'=>$conexao->id);

							if(!$infozap->enviaArquivo($attr)) {
								$erro='Algum erro ocorreu durante o envio do Receituário via Whatsapp. Entre em contato com nossa equipe de suporte!';
							}

						}
					}

					if(!empty($erro)) {
						$rtn=array('success'=>false,'error'=>$erro);
					}
				}

				else {
					$rtn=array('success'=>false,'error'=>'Method undefined');
				}

			} else {
				$rtn=array('success'=>false,'error'=>'Method undefined.');
			}
		} else {
			$rtn=array('success'=>false,'error'=>'Conta Infodental não encontrada!');
		}


		echo json_encode($rtn);

	} else http_response_code(403);


?>