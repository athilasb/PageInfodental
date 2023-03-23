<?php
require_once '../lib/conf.php';
require_once '../lib/class/classMysql.php';
require_once '../vendor/autoload.php';

/**
 * function uploader - faz o upload do pdf gerado
 * @param string $instancia
 * @param string $_dirEvolucao - diretorio onde a evolucao vai ficar
 * @param int $id_evolucao 
 * @param string $html
 * @return string $erro
 */
function uploader($instancia, $_dirEvolucao, $id_evolucao, $html)
{
	global $dompdf, $s3, $_scalewayBucket, $sql, $_p;
	$erro = '';

	$dompdf->loadHtml($html);
	$dompdf->setPaper('A4', 'portrait');
	$dompdf->render();
	//$dompdf->stream();

	$output = $dompdf->output();
	file_put_contents('arqs/temp.pdf', $output);

	$uploadPathFile = $instancia . "/" . $_dirEvolucao . sha1($id_evolucao) . ".pdf";
	try {
		$s3->putObject(
			array(
				'Bucket' => $_scalewayBucket,
				'Key' => $uploadPathFile,
				'SourceFile' => 'arqs/temp.pdf',
				'ACL' => 'public-read',
			)
		);
		$sql->update($_p . "pacientes_evolucoes", "s3=1", "where id=$id_evolucao");
	} catch (S3Exception $e) {
		$erro = 'Algum erro ocorreu durante a persistência do PDF em nosso cloud de armazenamento. Favor contate o suporte.';
	}
	return $erro;
}

/**
 * Function enviaWhatsapp - envia um pdf qualquer por whatsapp
 * @param string $pdf - caminho para o pdf dos servidores da scaleway. Tecnicamente aceita qualquer tipo de caminho
 * @param mixed $evolucao
 * @param mixed $paciente
 * @param mixed $conexao - um array que contem uma linha da tabela infodentalADM.infod_contas_onlines, checa a conexão com o whatsapp
 * @param mixed $evolucaoTipo
 * @return string - retorna um erro do infozap ou uma mensagem fixa.
 */
function enviaWhatsapp(&$pdf, &$evolucao, &$paciente, &$conexao, &$evolucaoTipo){
	global $infozap; 
	$erro = '';
	// verifica se foi assinado
	if ($evolucao->receita_assinada == "0000-00-00 00:00:00") {
		$pdf .= sha1($evolucao->id) . ".pdf";
	} else {
		$pdf .= "assinados/" . sha1($evolucao->id) . ".pdf";
	}

	// envia whatsapp
	$attr = array(
		'numero' => $paciente->telefone1,
		'arq' => $pdf,
		'id_paciente' => $paciente->id,
		'documentName' => utf8_encode($evolucaoTipo->titulo) . " " . date('d/m/Y', strtotime($evolucao->data)) . " - " . utf8_encode($paciente->nome) . ".pdf",
		'id_conexao' => $conexao->id
	);

	if (!$infozap->enviaArquivo($attr)) {
		$erro = isset($infozap->erro) ? $infozap->erro : 'Algum erro ocorreu durante o envio do Receituário via Whatsapp. Entre em contato com nossa equipe de suporte!';
	}
	return $erro;
}

$mensagem = array("mensagem" => "teste");

use Dompdf\Dompdf;

$dompdf = new Dompdf(array('isRemoteEnabled' => true));

use Aws\S3\S3Client;

$s3 = new S3Client([
	'version' => 'latest',
	'endpoint' => $_scalewayS3endpoint,
	'region' => $_scalewayS3Region,
	'credentials' => [
		'key' => $_scalewayAccessKey,
		'secret' => $_scalewaySecretKey
	],
	'bucket_endpoint' => true
	// 'debug' => true
]);

$sql = new Mysql();
$id_evolucao = 1990;


$token = 'ee7a1554b556f657e8659a56d1a19c315684c39d';
$request = file_get_contents('php://input');
$request = json_decode($request);

$infoConta = '';
if (isset($request->infoConta) and !empty($request->infoConta)) {
	$sql->consult("infodentalADM.infod_contas", "instancia", "where instancia='" . addslashes($request->infoConta) . "'");
	if ($sql->rows) {
		$infoConta = mysqli_fetch_object($sql->mysqry);
	}
}

if (isset($request->token) and $request->token == $token) {

	require_once("../lib/class/classWhatsapp.php");
	require_once("../lib/class/classMysql.php");
	$sql = new Mysql();

	$attr = array('prefixo' => $_p, 'usr' => (object) array('id' => 0));
	$infozap = new Whatsapp($attr);

	header("Content-type: application/json");

	$rtn = [];

	if (is_object($infoConta)) {

		$_p = $infoConta->instancia . ".ident_";

		if (isset($request->method)) {
			
			// necessita apenas do id da evolução e da instância onde ela está
			if ($request->method == 'generatePDF') {

				$erro = '';

				$enviaWhatsapp = (isset($request->enviaWhatsapp) and $request->enviaWhatsapp == 1) ? 1 : 0;

				$evolucao = $evolucaoTipo = $paciente = '';
				if (isset($request->id_evolucao) and is_numeric($request->id_evolucao)) {
					$sql->consult($_p . "pacientes_evolucoes", "*", "where id=$request->id_evolucao");

					if ($sql->rows) {
						$evolucao = mysqli_fetch_object($sql->mysqry);

						$sql->consult($_p . "pacientes_evolucoes_tipos", "*", "where id='$evolucao->id_tipo'");
						if ($sql->rows)
							$evolucaoTipo = mysqli_fetch_object($sql->mysqry);

						$sql->consult($_p . "pacientes", "*", "where id=$evolucao->id_paciente");
						if ($sql->rows)
							$paciente = mysqli_fetch_object($sql->mysqry);
					}
				}

				// verifica se whatsapp está conectado
				$where = "where instancia='" . $_ENV['NAME'] . "' and lixo=0 order by data desc limit 1";
				$sql->consult("infodentalADM.infod_contas_onlines", "*", $where);
				$conexao = $sql->rows ? mysqli_fetch_object($sql->mysqry) : '';

				//var_dump($conexao);

				if (empty($evolucao))
					$erro = 'Evolução não encontrada!';
				else if (empty($evolucaoTipo))
					$erro = 'Tipo de evolução não encontrado!';
				else if (empty($paciente))
					$erro = 'Paciente não encontrado!';
				else if ($enviaWhatsapp == 1 and is_object($paciente) and empty($paciente->telefone1))
					$erro = 'Paciente não possui celular cadastrado para envio do whatsapp';
				else if ($enviaWhatsapp == 1 and empty($conexao))
					$erro = 'Infozap não conectado';
				else if ($enviaWhatsapp == 1 and is_object($conexao) and $conexao->versao != 2)
					$erro = 'Versão do Infozap não suporta envio de arquivos. Favor atualize seu Infozap!';

				if (empty($erro)) {

					// Print-header
					$sql->consult($_p . "clinica", "*", "where id=1");
					$unidade = mysqli_fetch_object($sql->mysqry);
					$unidadeTitulo = utf8_encode($unidade->clinica_nome);
					$unidadeLogo = $_cloudinaryURL.'c_thumb,w_600/'.$unidade->cn_logo;
					$unidadeTelefones = '';
					if (!empty($unidade->whatsapp) or !empty($unidade->telefone)) {
						$unidadeTelefones = '<tr><td>';
						if (!empty($unidade->whatsapp))
							$unidadeTelefones .= ($unidade->whatsapp) . "&nbsp;&nbsp;&nbsp;&nbsp;";
						if (!empty($unidade->telefone))
							$unidadeTelefones .= mask($unidade->telefone);
						$unidadeTelefones .= '</td></tr>';
					}
					if(!empty($fornecedor->lat) && !empty($fornecedor->lng)){
						$forLat = $clinica->lat;
						$forLng = $clinica->lng;
						$clinicaComoChegar = '<small>Como Chegar</small><br /><a href="https://www.google.com/maps/search/' . $forLat . ',' . $forLng . '">Google Maps</a> &nbsp;&nbsp;
												<a href="https://www.waze.com/pt-BR/live-map/directions?locale=pt_BR&utm_source=waze_app&to=ll.' . $forLat . '%2C' . $forLng . ';">Waze</a>';
					}

/*
					$clinicaTitulo = $clinicaTelefone = $clinicaEndereco = $clinicaComoChegar = '';
					$clinica = array();                                                                 
					$sql->consult($_p. "clinica", "*", "");
					if($sql->rows){
						$clinica = mysqli_fetch_object($sql->mysqry);
						$clinicaTitulo = '<small>Nome</small><br />' . utf8_encode($clinica->clinica_nome);
						$clinicaTelefone =  (($fornecedor->telefone1)!=''? mask($fornecedor->telefone1):'') ;
						$clinicaEndereco = utf8_encode($fornecedor->endereco);
						if(!empty($fornecedor->lat) && !empty($fornecedor->lng)){
							$forLat = $clinica->lat;
							$forLng = $clinica->lng;
							$clinicaComoChegar = '<small>Como Chegar</small><br /><a href="https://www.google.com/maps/search/' . $forLat . ',' . $forLng . '">Google Maps</a> &nbsp;&nbsp;
													<a href="https://www.waze.com/pt-BR/live-map/directions?locale=pt_BR&utm_source=waze_app&to=ll.' . $forLat . '%2C' . $forLng . ';">Waze</a>';
						}
					}*/

					$unidadeEndereco = !empty($unidade->endereco) ? '<tr><td>' . utf8_encode($unidade->endereco) . '</td></tr>' : '';

					$unidadeDigital = '';
					if (!empty($unidade->site) or !empty($unidade->instagram)) {

						$unidadeDigital = '<tr><td>';
						if (!empty($unidade->site))
							$unidadeDigital .= $unidade->site . "&nbsp;&nbsp;&nbsp;&nbsp;";
						if (!empty($unidade->instagram))
							$unidadeDigital .= $unidade->instagram . "&nbsp;&nbsp;&nbsp;&nbsp;";
						$unidadeDigital .= '</td></tr>';
					}

					$sql->consult($_p . "colaboradores", "*", "where id=$evolucao->id_profissional");
					if ($sql->rows) {
						$solicitante = mysqli_fetch_object($sql->mysqry);
					}

					#pegando e formatando dados do paciente
					$idade = $sexo = $celular = '';
					$sql->consult($_p . "pacientes", "*", "where id=$evolucao->id_paciente");
					if ($sql->rows) {
						$paciente = mysqli_fetch_object($sql->mysqry);
						$idade = idade($paciente->data_nascimento) . ' anos';
						$sexo = $paciente->sexo == "M" ? "Masculino" : "Feminino";
						$celular = mask($paciente->telefone1);
					}

					$divDadosClinica = '					
						<div style="margin-top:2.5rem; style="text-align: left"">
						<table>
							<tr>
								<th style="text-align: left"><small>Nome</small></th>
								<th style="text-align: left"><small>Telefone</small></th>
								<th style="text-align: left"><small>Whatsapp</small></th>
							</tr>
							<tr>
								<td>' . $unidadeTitulo . '&nbsp;&nbsp;&nbsp;&nbsp;</td>
								<td>' . (!empty($unidade->telefone) ? maskTelefone($unidade->telefone) : "-") . '&nbsp;&nbsp;&nbsp;&nbsp;</td>
								<td>' . (!empty($unidade->whatsapp) ? $unidade->whatsapp : "-") . '&nbsp;&nbsp;&nbsp;&nbsp;</td>
							</tr>
							<tr>
								<th style="text-align: left"><small>Nome do profissional</small></th>
								<th style="text-align: left"><small>CRO</small></th>
								<th style="text-align: left"><small>UF</small></th>
							</tr>
							<tr>
								<td>' . (!empty($solicitante->nome) ? utf8_encode($solicitante->nome) : "-") . '&nbsp;&nbsp;&nbsp;&nbsp;</td>
								<td>' . (!empty($solicitante->cro) ? utf8_encode($solicitante->cro) : "-") . '&nbsp;&nbsp;&nbsp;&nbsp;</td>
								<td>' . (!empty($solicitante->uf_cro) ? utf8_encode($solicitante->uf_cro) : "-") . '&nbsp;&nbsp;&nbsp;&nbsp;</td>
							</tr>
							<tr>
								<th style="text-align: left"><small>Endereço</small></th>
							</tr>
							<tr>
								<td colspan="3">'.(!(empty($unidade->endereco)) ?  utf8_encode($unidade->endereco) : "-").'</td>
							</tr>
						</table>
						</div>'
					;

					#ANAMNESE
					if ($evolucao->id_tipo == 1) {

						$form = '';
						$resp = '';
						$sql->consult($_p . "pacientes_evolucoes_anamnese", "*", "where id_evolucao=$evolucao->id and lixo=0");
						if ($sql->rows) {
							while ($x = mysqli_fetch_object($sql->mysqry)) {
								$_anamnesePerguntas[] = $x;
							}
							foreach ($_anamnesePerguntas as $p) {
								$resp = 'Não';
								$pergunta = json_decode($p->json_pergunta);

								if ($pergunta->tipo == "simnao" or $pergunta->tipo == "simnaotexto") {
									if ($p->resposta == "SIM")
										$resp = "Sim";
								} else if ($pergunta->tipo == "nota") {
									$resp = "Nota: " . $p->resposta;
								}
								$form .= '
									<tr>
										<td>
											<p><strong>' . utf8_encode($p->pergunta) . '</strong></p>
												<p>' . $resp . '</p>
												' .
									(!empty($p->resposta_texto) ? "<p>Resposta: " . utf8_encode($p->resposta_texto) . "</p>" : "")
									. '
										</td>
									</tr>
								';
							}
						}

						$html = '	
						<html>
							<head>
								<style>		
									@import url("http://163.172.187.183:5000/css/pdf.css");
								</style>
							</head>
							<body>
								<header>
									<img src="'.$unidadeLogo.'" />
								</header>

								<footer>
									<table>
										' . $unidadeTelefones . $unidadeEndereco . $unidadeDigital . '
									</table>
								</footer>

								<main>
										<h1>Formulário da Anamnese</h1>	

										<p style="text-align:center;">' . date('d/m/Y', strtotime($evolucao->data)) . '</p>

										<div style="width:100%; display:flex; padding:10px; background:#ebebeb; border-radius:20px; font-size:13pt; margin-bottom:1.5rem;"}
											<table>
												<tr>
													<td colspan="3"><strong>' . utf8_encode($paciente->nome) . '</strong></td>
												</tr>
												<tr>
													<td>' . ($idade > 1 ? "$idade" : "$idade") . '</td>
													<td>' . ($paciente->sexo == "M" ? "Masculino" : $paciente->sexo == "F" ? "Feminino" : '.') . '</td>
			                                        <td style="text-align:right;">' . maskTelefone($paciente->telefone1) . '</td>
												</tr>
											</table>
										</div>

										<table>' .
							$form
							. '</table>
									'.$divDadosClinica.'
								</main>
							</body>
						</html>
					';

						$erro = uploader($infoConta->instancia, "arqs/pacientes/anamneses/", $evolucao->id, $html);
					}

					#ATESTADO
					if ($evolucao->id_tipo == 4) {
						$sql->consult($_p . "pacientes_evolucoes_atestados", "*", "where id_evolucao=$evolucao->id");
						if ($sql->rows) {
							$atestado = mysqli_fetch_object($sql->mysqry);
						}

						$html = '	
						<html>
							<head>
								<style>		
									@import url("http://163.172.187.183:5000/css/pdf.css");
								</style>
							</head>
							<body>
								<header>
									<img src="'.$unidadeLogo.'" />
								</header>

								<footer>
									<table>
										' . $unidadeTelefones . $unidadeEndereco . $unidadeDigital . '
									</table>
								</footer>

								<main>
								
										<h1>Atestado Médico</h1>	

										<p style="text-align:center;">' . date('d/m/Y', strtotime($evolucao->data)) . '</p>

										<p>' . utf8_encode($atestado->atestado) . '</p>
										
										<p style="font-weight:bold;margin-top: 25px;font-size: 10px;">
											Conforme artigo 9° da Resolução CFO-118/2012 - É dever do profissional de odontologia resguardar o sigilo profissional do paciente, e, quando necessário, a depender do caso, não expor o procedimento realizado, bem como a CID correspondente.
										</p>

										'.$divDadosClinica.'
								</main>
								
							</body>
						</html>
										';

						$erro = uploader($infoConta->instancia, "arqs/pacientes/atestados/", $evolucao->id, $html);
					}
					# PEDIDO DE EXAME
					if ($evolucao->id_tipo == 6) {

						#pegando e formatando dados do fornecedor
						$fornecedorTitulo = $fornecedorTelefone = $fornecedorEndereco = $fornecedorComoChegar = '';
						$fornecedor = array();
						$sql->consult($_p . "parametros_fornecedores", "*", "where id=$evolucao->id_clinica and lixo=0");
						if ($sql->rows) {
							$fornecedor = mysqli_fetch_object($sql->mysqry);

							if ($fornecedor->tipo_pessoa == 'PF') {
								$fornecedorTitulo = '<small>Nome</small><br />' . utf8_encode($fornecedor->nome);
							} else {
								$fornecedorTitulo = '<small>Razão Social</small><br />' . utf8_encode($fornecedor->razao_social);
							}

							$fornecedorTelefone = mask($fornecedor->telefone1);
							$fornecedorEndereco = utf8_encode($fornecedor->endereco);

							if (!empty($fornecedor->lat) and !empty($fornecedor->lng)) {
								$lat = $fornecedor->lat;
								$lng = $fornecedor->lng;
								$fornecedorComoChegar = '<small>Como Chegar</small><br /><a href="https://www.google.com/maps/search/' . $lat . ',' . $lng . '">Google Maps</a> &nbsp;&nbsp;
														<a href="https://www.waze.com/pt-BR/live-map/directions?locale=pt_BR&utm_source=waze_app&to=ll.' . $lat . '%2C' . $lng . ';">Waze</a>';
							}
						}
						$examesIds = array(0);
						$sql->consult($_p . "pacientes_evolucoes_pedidosdeexames", "*", "where id_evolucao=$evolucao->id and lixo=0");

						while ($x = mysqli_fetch_object($sql->mysqry)) {
							$exames[] = $x;
							$examesIds[] = $x->id_exame;
						}

						$sql->consult($_p . "parametros_examedeimagem", "*", "where id IN (" . implode(",", $examesIds) . ")");
						while ($x = mysqli_fetch_object($sql->mysqry)) {
							$_exames[$x->id] = $x;
						}

						$cont = 1;
						$examesSolicitados = '';
						foreach ($exames as $x) {
							if (isset($_exames[$x->id_exame])) {
								$regiao = 'GERAL';
								if (isset($x->opcao) and !empty($x->opcao)) {
									$opcoes = explode(",", utf8_encode($x->opcao));
									$regiao = '';
									foreach ($opcoes as $opcao) {
										$regiao .= " " . $opcao . ", ";
									}
									$regiao = substr($regiao, 0, strlen($regiao) - 2);
								}
								$examesSolicitados .= '<tr><td>';
								$examesSolicitados .= '<small>' . $cont++ . ') ' . utf8_encode($_exames[$x->id_exame]->titulo) . '</small><br />
														Região: ' . $regiao . '<br />';

								if (!empty($x->obs))
									$examesSolicitados .= 'Obs: ' . utf8_encode($x->obs) . '<br />';
								$examesSolicitados .= '</td></tr>';
							}
						}

						$html = '
									<html>
									<head>
										<style>		
											@import url("http://163.172.187.183:5000/css/pdf.css");
										</style>
									</head>
									<body>							
										<header>
											<img src="'.$unidadeLogo.'" />
										</header>
										<footer>
											<table>
											' . $unidadeTelefones . $unidadeEndereco . $unidadeDigital . '
											</table>
										</footer>
										<main>									

											<h1>Pedido de Exame Complementar</h1>

											<p style="text-align:center;">' . date('d/m/Y', strtotime($evolucao->data)) . '</p>

											<table>
												<tr>
													<td><small>' . utf8_encode($paciente->nome) . '</small></td>
													<td>' . $idade . '</td>
													<td>' . $sexo . '</td>
													<td>' . $celular . '</td>
												</tr>
											</table>

											<h2>Clínica Radiológica</h2>

											<table>
												<tr>
													<td>' . $fornecedorTitulo . '</td>
													<td><small>Telefone</small><br />' . $fornecedorTelefone . '</td>
													<td><small>Solicitado por</small><br />' . utf8_encode($solicitante->nome) . '</td>
												</tr>
												<tr>
													<td colspan="3">
														<small>Endereço</small><br />
														' . (!(empty($fornecedorEndereco)) ? $fornecedorEndereco : "-") . '
													</td>
												</tr>
												<tr>
													<td colspan="3">
														' . $fornecedorComoChegar . '
													</td>
												</tr>
											</table>

											<h2>Exames Solicitados</h2>

											<table>
												' . $examesSolicitados . '
											</table>
										</main>
									</body>
									</html>
								';

						$erro = uploader($infoConta->instancia, "arqs/pacientes/pedidoExames/", $evolucao->id, $html);
					}

					# RECEITUARIO
					else if ($evolucao->id_tipo == 7) {

						$idade = $sexo = $celular = '';
						$sql->consult($_p . "pacientes", "*", "where id=$evolucao->id_paciente");
						if ($sql->rows) {
							$paciente = mysqli_fetch_object($sql->mysqry);
							$idade = idade($paciente->data_nascimento) . ' anos';
							$sexo = $paciente->sexo == "M" ? "Masculino" : "Feminino";
							$celular = mask($paciente->telefone1);
						}

						$controleEspecial = false;
						$receitas = array();
						$sql->consult($_p . "pacientes_evolucoes_receitas", "*", "where id_evolucao=$evolucao->id and lixo=0");
						while ($x = mysqli_fetch_object($sql->mysqry)) {
							$receitas[] = $x;

							if ($x->controleespecial == 1)
								$controleEspecial = true;
						}

						$cont = 1;
						$receitaSolicitada = '';
						foreach ($receitas as $x) {
							$regiao = 'GERAL';
							if (isset($x->opcao) and !empty($x->opcao)) {
								$opcoes = explode(",", utf8_encode($x->opcao));
								$regiao = '';
								foreach ($opcoes as $opcao) {
									$regiao .= " " . $opcao . ", ";
								}
								$regiao = substr($regiao, 0, strlen($regiao) - 2);
							}
							$receitaSolicitada .= '<tr><td>';
							$receitaSolicitada .= '<small>' . $cont++ . ') ' . utf8_encode($x->medicamento) . '</small><span style="float:right"><small>' . ($x->quantidade . " " . (isset($_medicamentosTipos[$x->tipo]) ? $_medicamentosTipos[$x->tipo] : $x->tipo)) . '</small></span>';
							$receitaSolicitada .= '<br /><small><span style="font-weight:normal">' . utf8_encode($x->posologia) . '</span></small>';

							$receitaSolicitada .= '</td></tr>';
						}

						$receitaControleEspecial = '';
						$receitaTitulo = 'Receituário Simples';
						if ($controleEspecial === true) {
							$receitaTitulo = 'Receituário Especial';
							$receitaControleEspecial = '<br /><br /><div>
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

						$html = '
									<html>
									<head>
										<style>		
											@import url("http://163.172.187.183:5000/css/pdf.css");
										</style>
									</head>
									<body>							
										<header>
											<img src="'.$unidadeLogo.'" />
										</header>
										<footer>
											<table>
											' . $unidadeTelefones . $unidadeEndereco . $unidadeDigital . '
											</table>
										</footer>
										<main>									

											<h1>' . $receitaTitulo . '</h1>

											<table>
												<tr>
													<td><small>' . utf8_encode($paciente->nome) . '</small></td>
													<td>' . $idade . '</td>
													<td>' . $sexo . '</td>
													<td>' . $celular . '</td>
												</tr>
											</table>

											<h2>Prescrição</h2>

											<table>
												' . $receitaSolicitada . '
											</table>
											<br /><br />
											<table>
												<tr>
													<td><small>Nome do Dentista</small><br /><span style="font-weght:normal;font-size:0.85em">' . utf8_encode($solicitante->nome) . '</span></td>
													<td><small>CRO</small><br /><span style="font-weght:normal;font-size:0.85em">' . utf8_encode($solicitante->cro) . '</span></td>
													<td><small>UF</small><br /><span style="font-weght:normal;font-size:0.85em">' . utf8_encode($solicitante->uf_cro) . '</span></td>
												</tr>
												<tr>
													<td style="max-width:300px;"><b>Local de Atendimento</b><br /><span style="font-weght:normal;font-size:0.85em">' . utf8_encode($unidade->endereco) . '</span></td>
													<td><b>Data de Emissão</b><br /><span style="font-weght:normal;font-size:0.85em">' . date('d/m/Y', strtotime($evolucao->data)) . '</span></td>
												</tr>
											</table>
											' . $receitaControleEspecial . '
										</main>
									</body>
									</html>
										
								';

						$erro = uploader($infoConta->instancia, "arqs/pacientes/receituarios/", $evolucao->id, $html);

						//documentos
					} else if ($evolucao->id_tipo == 10) {

						$sql->consult($_p . "pacientes_evolucoes_documentos", "*", "where id_evolucao=$evolucao->id and lixo=0");
						if ($sql->rows) {
							$documento = mysqli_fetch_object($sql->mysqry);

							$sql->consult($_p . "parametros_documentos", "*", "where id=$documento->id_documento");
							if ($sql->rows) {
								$documentoModelo = mysqli_fetch_object($sql->mysqry);
							}
						}

						$html = '
						<html>
							<head>
								<style>		
									@import url("http://163.172.187.183:5000/css/pdf.css");
									.fck {font-weight:300;}
									.fck hr {border:0; border-bottom:1px solid var(--cinza2); margin:2rem 0;}
									.fck > *:first-child, .titulo1:first-child, .titulo2:first-child, .titulo3:first-child {margin-top:0;}
									.fck p {margin:1.25rem 0; font-size:1.125em; line-height:1.6;}
									.fck h1, .titulo1 {margin:2rem 0; line-height:1.1; font-size:2.5em; font-weight:300; color:var(--cor1);}
									.fck h2, .titulo2 {margin:2rem 0; line-height:1.1; font-size:2em; font-weight:300; letter-spacing:-0.02em; color:var(--cor1);}
									.fck h3, .titulo3 {margin:2rem 0; line-height:1.1; font-size:1.5em; font-weight:300;}
									.fck ul {list-style:disc outside; margin:0 0 1rem 30px;}
									.fck ol {list-style:decimal outside; margin:0 0 1rem 30px;}
									.fck li {margin-bottom:.3rem;}
									.fck table {width:100%; margin:2rem 0;}
									.fck table p {margin:0;}
									.fck table th {color:var(--cor2); padding:.5rem; border-bottom:2px solid var(--cor2); text-align:left;}
									.fck table td {padding:.5rem; border-bottom:1px solid var(--cinza2);}
									.fck a {text-decoration:underline; color:var(--cor1);}
									.fck img {max-width:100%; height:auto;}
								</style>
							</head>
							<body>							
								<header>
									<img src="'.$unidadeLogo.'" />
								</header>
								<footer>
									<table>
									'. $unidadeTelefones . $unidadeEndereco . $unidadeDigital .'
									</table>
								</footer>
								<main>			
								<div class="page">
									'. utf8_encode($documento->texto) .'
								</div>
								</main>
							</body>
						</html>		
					';
						$erro = uploader($infoConta->instancia, "arqs/pacientes/documentos/", $evolucao->id, $html);
					}



					// dispara whatsapp com o PDF gerado se parametro enviaWhatsapp = 1
					if ($enviaWhatsapp and empty($erro)) {

						// verifica se possui conexao
						$conexao = '';
						$sql->consult("infodentalADM.infod_contas_onlines", "*", "where instancia='" . $_ENV['NAME'] . "' and lixo=0");
						if ($sql->rows)
							$conexao = mysqli_fetch_object($sql->mysqry);



						$numero = $paciente->telefone1;

						// envia whatsapp
						$attr = array(
							'numero' => $numero,
							'arq' => 'arqs/temp.pdf',
							'documentName' => utf8_encode($evolucaoTipo->titulo) . " " . date('d/m/Y', strtotime($evolucao->data)) . " - " . utf8_encode($paciente->nome) . ".pdf",
							'id_conexao' => $conexao->id
						);

						if ($infozap->enviaArquivo($attr)) {

							$rtn = array('success' => true, 'numero' => mask($numero));
						} else {
							$rtn = array('success' => false, 'error' => $infozap->erro);
						}
					}


					if (empty($erro)) {
						$rtn = array('success' => true);
					} else {
						$rtn = array('success' => false, 'error' => $erro);
					}


				}
				
		    // necessita do id da evolução e da instãncia apenas
			} else if ($request->method == "sendWhatsapp") {
				$evolucao = $evolucaoTipo = $paciente = '';
				if (isset($request->id_evolucao) and is_numeric($request->id_evolucao)) {
					$sql->consult($_p . "pacientes_evolucoes", "*", "where id=$request->id_evolucao");
					if ($sql->rows) {
						$evolucao = mysqli_fetch_object($sql->mysqry);

						$sql->consult($_p . "pacientes_evolucoes_tipos", "*", "where id='$evolucao->id_tipo'");
						if ($sql->rows)
							$evolucaoTipo = mysqli_fetch_object($sql->mysqry);

						$sql->consult($_p . "pacientes", "id,telefone1,nome", "where id=$evolucao->id_paciente");
						if ($sql->rows)
							$paciente = mysqli_fetch_object($sql->mysqry);
					}
				}

				// verifica se possui conexao
				$conexao = '';
				$sql->consult("infodentalADM.infod_contas_onlines", "*", "where instancia='" . $_ENV['NAME'] . "' and lixo=0");
				if ($sql->rows)
					$conexao = mysqli_fetch_object($sql->mysqry);
				if (empty($evolucao))
					$erro = 'Evolução não encontrada!';
				else if (empty($evolucaoTipo))
					$erro = 'Tipo de evolução não encontrado!';
				else if (empty($paciente))
					$erro = 'Paciente não encontrado!';
				else if (is_object($paciente) and empty($paciente->telefone1))
					$erro = 'Paciente não possui celular cadastrado para envio do whatsapp';
				else if (empty($conexao))
					$erro = 'Infozap não conectado';
				else if (is_object($conexao) and $conexao->versao != 2)
					$erro = 'Versão do Infozap não suporta envio de arquivos. Favor atualize seu Infozap!';


				if (empty($erro)) {
					switch ($evolucao->id_tipo){
						case 1:  //anamnese
							$pdf = "$_scalewayS3endpoint/" . $infoConta->instancia . "/arqs/pacientes/anamneses/";
							break;
						case 4:  //atestado
							$pdf = "$_scalewayS3endpoint/" . $infoConta->instancia . "/arqs/pacientes/atestados/";
							break;
						case 6:  //pedido de exame
							$pdf = "$_scalewayS3endpoint/" . $infoConta->instancia . "/arqs/pacientes/pedidoExames/";
							break;
						case 7:  // receituario
							$pdf = "$_scalewayS3endpoint/" . $infoConta->instancia . "/arqs/pacientes/receituarios/";
							break;
						case 10: //documentos
							//$pdf = "$_scalewayS3endpoint/" . $infoConta->instancia . "/arqs/pacientes/documentos/";
							$erro = "esse tipo de evolução ainda não foi implementada para o envio de whatsapp";
							break;
						default:
							$erro = "evolução com id ".$evolucao->id_tipo." de tipo inválido ou não implentado";
							break;
					}
					if(empty($erro)){
						$erro = enviaWhatsapp($pdf, $evolucao, $paciente, $conexao, $evolucaoTipo );
					}
				}
				if (empty($erro)) {
					$rtn = array('success' => true, 'numero' => mask($paciente->telefone1));
				} else {
					$rtn = array('success' => false, 'error' => $erro);
				}
			} else {
				$rtn = array('success' => false, 'error' => 'Method undefined');
			}
		}
	} else {
		$rtn = array('success' => false, 'error' => 'Conta Infodental não encontrada!');
	}
	echo json_encode($rtn);
} else
	http_response_code(403);
?>