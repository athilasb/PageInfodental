<?php


	require_once '../lib/conf.php';
	require_once '../lib/class/classMysql.php';
	require_once 'dompdf/autoload.inc.php';

	use Dompdf\Dompdf;
	$dompdf = new Dompdf(array('enable_remote'=>true));
	$sql = new Mysql();
	$id_evolucao=1990;




	$token='ee7a1554b556f657e8659a56d1a19c315684c39d';
	$request = file_get_contents('php://input');
	$request = json_decode($request);

	if(isset($request->token) and $request->token==$token) {

		require_once("../lib/class/classWhatsapp.php");
		require_once("../lib/class/classMysql.php");
		$sql = new Mysql();

		$attr=array('prefixo'=>$_p,'usr'=>(object)array('id'=>0));
		$infozap = new Whatsapp($attr);

		header("Content-type: application/json");

		$rtn = [];

		if(isset($request->method)) {


			if($request->method=='generatePDF') {
				require_once 'dompdf/autoload.inc.php';

				$erro='';

				$evolucao=$evolucaoTipo=$paciente='';
				if(isset($request->id_evolucao) and is_numeric($request->id_evolucao)) {
					$sql->consult($_p."pacientes_evolucoes","*","where id=$request->id_evolucao"); 
					if($sql->rows) {
						$evolucao = mysqli_fetch_object($sql->mysqry);

						$sql->consult($_p."pacientes_evolucoes_tipos","*","where id='$evolucao->id_tipo'");
						if($sql->rows) $evolucaoTipo=mysqli_fetch_object($sql->mysqry);

						$sql->consult($_p."pacientes","id,telefone1","where id=$evolucao->id_paciente");
						if($sql->rows) $paciente=mysqli_fetch_object($sql->mysqry);
					}
				}

				// verifica se whatsapp está conectado
				$where="where instancia='".$_ENV['NAME']."' and lixo=0 order by data desc limit 1";
				$sql->consult("infodentalADM.infod_contas_onlines","*",$where);
				$conexao=$sql->rows?mysqli_fetch_object($sql->mysqry):'';
				
				if(empty($evolucao)) $erro='Evolução não encontrada!';
				else if(empty($evolucaoTipo)) $erro='Tipo de evolução não encontrado!';
				else if(empty($paciente)) $erro='Paciente não encontrado!';
				else if(is_object($paciente) and empty($paciente->telefone1)) $erro='Paciente não possui celular cadastrado para envio do whatsapp';
				else if(empty($conexao)) $erro='Infozap não conectado';
				else if(is_object($conexao) and $conexao->versao!=2) $erro='Versão do Infozap não suporta envio de arquivos. Favor atualize seu Infozap!';



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

					// Pedido de exame
					if($evolucao->id_tipo==6) {

						$sql->consult($_p."colaboradores","id,nome","where id=$evolucao->id_usuario");
						if($sql->rows) {
							$solicitante=mysqli_fetch_object($sql->mysqry);
						}

						$clinicaTitulo=$clinicaTelefone=$clinicaEndereco=$clinicaComoChegar='';
						$sql->consult($_p."parametros_fornecedores","*","where id=$evolucao->id_clinica and lixo=0");
						if($sql->rows) {
							$clinica=mysqli_fetch_object($sql->mysqry);

							if($clinica->tipo_pessoa=='PF') {
								$clinicaTitulo='<b>Nome</b><br />'.utf8_encode($clinica->nome);
							} else {
								$clinicaTitulo='<b>Razão Social</b><br />'.utf8_encode($clinica->razao_social);
							}

							$clinicaTelefone=mask($clinica->telefone1);
							$clinicaEndereco=utf8_encode($clinica->endereco);

							if(!empty($clinica->lat) and !empty($clinica->lng)) {
								$lat=$clinica->lat;
								$lng=$clinica->lng;
								$clinicaComoChegar='<b>Como Chegar</b><br /><a href="https://www.google.com/maps/search/'.$lat.','.$lng.'">Google Maps</a> &nbsp;&nbsp;
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
								$examesSolicitados.='<b>'.$cont++.') '.utf8_encode($_exames[$x->id_exame]->titulo).'</b><br />
													Região: '.$regiao.'<br />';
								
								if(!empty($x->obs)) $examesSolicitados.='Obs: '.utf8_encode($x->obs).'<br />';
								$examesSolicitados.='</td></tr>';
								
							}
						}
						
						

						$html='<style>
									body { text-align:center}
									a {
										color:#000;
									}
								</style>

								<center>
								<header>
									<img src="http://163.172.187.183:5000/img/logo-cliente.png" height="50" />
								</header>
									
									<h1>Pedido de Exame Complementar</h1>
									<p>'.date('d/m/Y',strtotime($evolucao->data)).'</p>


									<table style="width:100%;border-radius:8px;padding:10px;background:#e5e5e5;text-align:center">
										<tr>
											<td><b>'.utf8_encode($paciente->nome).'</b></td>
											<td>'.$idade.'</td>
											<td>'.$sexo.'</td>
											<td>'.$celular.'</td>
										</tr>
									</table>

									<h2>Clínica Radiológica</h2>


									<table style="width:100%;border-radius:8px;padding:10px;border: solid 1px #CCC;text-align:center">
										<tr>
											<td>'.$clinicaTitulo.'</td>
											<td><b>Telefone</b><br />'.$clinicaTelefone.'</td>
											<td><b>Solicitado por</b><br />'.utf8_encode($solicitante->nome).'</td>
										</tr>
										<tr>
											<td colspan="3">
												<b>Endereço</b><br />
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

									<table style="width:100%;border-radius:8px;padding:10px;border: solid 1px #CCC;text-align:left">
										'.$examesSolicitados.'
									</table>

									<table style="width:100%;border-radius:8px;padding:10px;text-align:center;margin-top:20px;">
										'.$unidadeTelefones.$unidadeEndereco.$unidadeDigital.'
									</table>


								</center>
							';


						$dompdf->loadHtml($html);
						$dompdf->setPaper('A4', 'portrait');
						$dompdf->render();
						//$dompdf->stream();

						$output = $dompdf->output();
						file_put_contents('arqs/temp.pdf', $output);



					}

				} 


				if(empty($erro)) {

					die();
					// envia whatsapp
					$numero=$paciente->telefone1;
					$attr=array('numero'=>$numero,
								'arq'=>'arqs/temp.pdf',
								'documentName'=>utf8_encode($evolucaoTipo->titulo)." ".date('d/m/Y',strtotime($evolucao->data))." - ".utf8_encode($paciente->nome).".pdf",
								'id_conexao'=>1286);

					if($infozap->enviaArquivo($attr)) {

						$rtn=array('success'=>true,'numero'=>mask($numero));
					} else {
						$rtn=array('success'=>false,'error'=>$infozap->erro);
					}
				} else {
					$rtn=array('success'=>false,'error'=>$erro);
				}


			} 

			else {
				$rtn=array('success'=>false,'error'=>'Method undefined');
			}

		} else {
			$rtn=array('success'=>false,'error'=>'Method undefined.');
		}


		echo json_encode($rtn);

	} else http_response_code(403);

?>