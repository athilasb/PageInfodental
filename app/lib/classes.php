<?php
date_default_timezone_set('America/Araguaina');
error_reporting(E_ALL);
ini_set("display_errors", true);
define("_permissoes_", "auditoria,atlas,relatorios,naoserializados,parceiros");
define("_permissoesOut_", "Auditoria,Base Atlas,Relatórios,Não Serializados,Parceiros");
$_optOpcoes = array(1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5, 0 => 'não sei informar');

function encodingToJson($str)
{
	$encoding = new Encoding();
	$str = $encoding->toUTF8($str);

	return $str;
}

function telefoneMascara($str)
{

	return "(" . substr($str, 0, 2) . ") " . substr($str, 2, 9);
}
function mask($tel)
{
	return substr($tel, 2, 1) == 9 ? "(" . substr($tel, 0, 2) . ") " . substr($tel, 2, 5) . "-" . substr($tel, 7, 4) : "(" . substr($tel, 0, 2) . ") " . substr($tel, 2, 4) . "-" . substr($tel, 6, 5);
}
function nome($nome, $qtd)
{
	$aux = explode(" ", $nome);

	$rtn = '';
	$cont = 1;
	foreach ($aux as $v) {
		if (strlen($v) <= 3) continue;
		$rtn .= trim($v) . ' ';
		if ($qtd <= $cont) break;
		$cont++;
	}

	return $rtn;
}
function numeroletras($rtn)
{
	return preg_replace("/[^a-zA-Z0-9]+/", "", $rtn);
}
function numero($numero)
{
	return preg_replace('/\D/', '', $numero);
}

function maskCPF($cpf)
{
	$cpf = str_replace(".", "", str_replace("-", "", $cpf));
	return substr($cpf, 0, 3) . "." . substr($cpf, 3, 3) . "." . substr($cpf, 6, 3) . "-" . substr($cpf, 9, 11);
}
function maskCNPJ($cnpj)
{
	$cnpj = str_replace(".", "", str_replace("/", "", str_replace("-", "", $cnpj)));

	return substr($cnpj, 0, 2) . "." . substr($cnpj, 2, 3) . "." . substr($cnpj, 5, 3) . "/" . substr($cnpj, 8, 4) . "-" . substr($cnpj, 12, 2);
}
function retornaMesNumero($mes)
{
	//Jan, Feb, Mar, Apr, May, Jun, Jul, Aug, Sep, Oct, Nov, Dec
	$mes = trim($mes);
	if ($mes == "Jan") return "01";
	elseif ($mes == "Feb") return "02";
	elseif ($mes == "Mar") return "03";
	elseif ($mes == "Apr") return "04";
	elseif ($mes == "May") return "05";
	elseif ($mes == "Jun") return "06";
	elseif ($mes == "Jul") return "07";
	elseif ($mes == "Aug") return "08";
	elseif ($mes == "Sep") return "09";
	elseif ($mes == "Oct") return "10";
	elseif ($mes == "Nov") return "11";
	elseif ($mes == "Dec") return "12";
}
function invDate2($data)
{
	if (!empty($data)) {
		list($ano, $mes, $dia) = explode("-", $data);
		if (isset($dia) and !empty($dia) and isset($mes) and !empty($mes) and isset($ano) and !empty($ano)) {
			if (@checkdate($mes, $dia, $ano)) return trim($dia) . "/" . trim($mes) . "/" . trim($ano);
			else return "00/00/0000";
		} else return "00/00/00000";
	} else return "00/00/0000";
}
function phoneCheck($tel)
{
	$tel = preg_replace('/\D/', '', $tel);

	if (substr($tel, 0, 1) == 0) $tel = substr($tel, 1, strlen($tel));

	if (strlen($tel) == 10) {
		if (substr($tel, 2, 1) >= 2 and substr($tel, 2, 1) <= 5) {
			return $tel;
		} else if (substr($tel, 2, 1) >= 6 and substr($tel, 2, 1) <= 9) {
			$tel = substr($tel, 0, 2) . "9" . substr($tel, 2, strlen($tel));
			return $tel;
		}
	} else if (strlen($tel) == 11) {
		if (substr($tel, 2, 1) == 9 and substr($tel, 3, 1) >= 6 and substr($tel, 3, 1) <= 9) {
			return $tel;
		}
	}
	return false;
}
function detectDelimiter($csvFile)
{
	$delimiters = array(';' => 0, ',' => 0, "\t" => 0, "|" => 0);
	$handle = fopen($csvFile, "r");
	$firstLine = fgets($handle);
	fclose($handle);
	foreach ($delimiters as $delimiter => &$count) $count = count(str_getcsv($firstLine, $delimiter));
	return array_search(max($delimiters), $delimiters);
}
function d2($int)
{
	if ((int)$int < 10) return "0" . (int)$int;
	else return $int;
}
function und2($int)
{
	return (int)$int;
}
function unmaskCPF($cpf)
{
	return str_replace(".", "", str_replace("-", "", $cpf));
}
function maskTelefone($tel)
{
	$tel = preg_replace("/[^a-zA-Z0-9]+/", "", $tel);

	if (strlen($tel) == 10) return "(" . substr($tel, 0, 2) . ") " . substr($tel, 2, 4) . "-" . substr($tel, 6, 6);
	else return "(" . substr($tel, 0, 2) . ") " . substr($tel, 2, 5) . "-" . substr($tel, 7, 6);
}
function invDate($data)
{
	if (!empty($data)) {
		list($dia, $mes, $ano) = explode("/", $data);
		if (isset($dia) and !empty($dia) and isset($mes) and !empty($mes) and isset($ano) and !empty($ano)) {
			if (@checkdate($mes, $dia, $ano)) return trim($ano) . "-" . trim($mes) . "-" . trim($dia);
			else return "0000-00-00";
		} else return "0000-00-00";
	} else return "0000-00-00";
}

/**
 * Pega uma data no formato dd/mm/yyy HH:MM e formata para o padrão mysql
 * @param string $data
 * @return string data no formato "yyyy-mm-dd"
 */
function invDateTime($data)
{
	if (!empty($data)) {
		list($dt, $hr) = explode(" ", $data);
		list($dia, $mes, $ano) = explode("/", $dt);
		if (isset($dia) and !empty($dia) and isset($mes) and !empty($mes) and isset($ano) and !empty($ano)) {
			if (@checkdate($mes, $dia, $ano)) {
				list($hora, $minutos) = explode(":", $hr);
				return trim($ano) . "-" . trim($mes) . "-" . trim($dia) . " $hora:$minutos";
			} else return "0000-00-00";
		} else return "0000-00-00";
	} else return "0000-00-00";
}
function strtoupperWLIB($term)
{
	$palavra = strtr(strtoupper($term), "àáâãäåæçèéêëìíîïðñòóôõö÷øùüúþÿ", "ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖ×ØÙÜÚÞß");
	return $palavra;
}

function strtolowerWLIB($term)
{
	$palavra = strtr(strtolower($term), "ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖ×ØÙÜÚÞß", "àáâãäåæçèéêëìíîïðñòóôõö÷øùüúþÿ");
	return $palavra;
}
function tirarAcentos($string)
{
	return preg_replace(array("/(á|à|ã|â|ä)/", "/(Á|À|Ã|Â|Ä)/", "/(é|è|ê|ë)/", "/(É|È|Ê|Ë)/", "/(í|ì|î|ï)/", "/(Í|Ì|Î|Ï)/", "/(ó|ò|õ|ô|ö)/", "/(Ó|Ò|Õ|Ô|Ö)/", "/(ú|ù|û|ü)/", "/(Ú|Ù|Û|Ü)/", "/(ñ)/", "/(Ñ)/", "/(ç)/", "/(Ç)/"), explode(" ", "a A e E i I o O u U n N c C"), $string);
}
function idade($dn)
{
	$dataNascimento = $dn;
	$date = new DateTime($dataNascimento);
	$interval = $date->diff(new DateTime(date('Y-m-d')));
	return $interval->format('%Y');
}


function converteData($data)
{
	list($dia, $mes, $ano) = explode("/", $data);
	return $ano . "-" . $mes . "-" . $dia;
}
function MathZDC($dividend, $divisor, $quotient = 0)
{
	if ($divisor == 0) {
		return $quotient;
	} else if ($dividend == 0) {
		return 0;
	} else {
		return ($dividend / $divisor);
	}
}

function sec_convertOriginal($sec, $precision)
{


	$neg = $sec < 0 ? true : false;

	if ($neg) $sec *= -1;
	$Ftime = 0;
	$sec = round($sec, 0);
	if ($sec < 1) {
		if ($precision == 'HF') {
			return "00:00";
		} else {
			if ($precision == 'S') {
				return "00:00:00";
			} else {
				return "00:00:00";
			}
		}
	} else {
		if ($precision == 'HF') {
			$precision = 'H';
		} else {
			if (($sec < 3600) and ($precision != 'S')) {
				$precision = 'M';
			}
		}
		if ($precision == 'H') {
			$Fhours_H =	MathZDC($sec, 3600);
			$Fhours_H_int = floor($Fhours_H);
			$Fhours_H_int = intval("$Fhours_H_int");
			$Fhours_M = ($Fhours_H - $Fhours_H_int);
			$Fhours_M = ($Fhours_M * 60);
			$Fhours_M_int = floor($Fhours_M);
			$Fhours_M_int = intval("$Fhours_M_int");
			$Fhours_S = ($Fhours_M - $Fhours_M_int);
			$Fhours_S = ($Fhours_S * 60);
			$Fhours_S = round($Fhours_S, 0);
			if (strlen($Fhours_S) == 1) $Fhours_S = "0" . $Fhours_S;
			if (strlen($Fhours_M_int) == 1) $Fhours_M_int = "0" . $Fhours_M_int;
			if (strlen($Fhours_H_int) == 1) $Fhours_H_int = "0" . $Fhours_H_int;
			$Ftime = $Fhours_H_int . ":" . $Fhours_M_int;
		}
		if ($precision == 'M') {
			$Fminutes_M = MathZDC($sec, 60);
			$Fminutes_M_int = floor($Fminutes_M);
			$Fminutes_M_int = intval("$Fminutes_M_int");
			$Fminutes_S = ($Fminutes_M - $Fminutes_M_int);
			$Fminutes_S = ($Fminutes_S * 60);
			$Fminutes_S = round($Fminutes_S, 0);
			if (strlen($Fminutes_S) == 1) $Fminutes_S = "0$Fminutes_S";
			if (strlen($Fminutes_M_int) == 1) $Fminutes_M_int = "0$Fminutes_M_int";
			$Ftime = "00:$Fminutes_M_int:$Fminutes_S";
		}
		if ($precision == 'S') {
			$Ftime = $sec;
		}
		return ($neg ? "-" : "") . $Ftime;
	}
}
function sec_convert($sec, $precision)
{
	$Ftime = 0;
	$sec = round($sec, 0);
	if ($sec < 1) {
		if ($precision == 'HF') {
			return "0m";
		} else {
			if ($precision == 'S') {
				return "0m";
			} else {
				return "0m";
			}
		}
	} else {
		if ($precision == 'HF') {
			$precision = 'H';
		} else {
			if (($sec < 3600) and ($precision != 'S')) {
				$precision = 'M';
			}
		}
		if ($precision == 'H') {
			$Fhours_H =	MathZDC($sec, 3600);
			$Fhours_H_int = floor($Fhours_H);
			$Fhours_H_int = intval("$Fhours_H_int");
			$Fhours_M = ($Fhours_H - $Fhours_H_int);
			$Fhours_M = ($Fhours_M * 60);
			$Fhours_M_int = floor($Fhours_M);
			$Fhours_M_int = intval("$Fhours_M_int");
			$Fhours_S = ($Fhours_M - $Fhours_M_int);
			$Fhours_S = ($Fhours_S * 60);
			$Fhours_S = round($Fhours_S, 0);
			$horas = $Fhours_H_int;
			//	echo $horas."ss"
			//if (strlen($Fhours_S)==1) $Fhours_S = "0".$Fhours_S;
			//if (strlen($Fhours_M_int)==1) $Fhours_M_int = "0".$Fhours_M_int;
			//if (strlen($Fhours_H_int)==1) $Fhours_H_int = "0".$Fhours_H_int;
			$Ftime = ($horas > 0 ? $Fhours_H_int . "h " : "") . ($Fhours_M_int > 0 ? $Fhours_M_int . "m" : ""); //."m".$Fhours_S."";
		}
		if ($precision == 'M') {
			$Fminutes_M = MathZDC($sec, 60);
			$Fminutes_M_int = floor($Fminutes_M);
			$Fminutes_M_int = intval("$Fminutes_M_int");
			$Fminutes_S = ($Fminutes_M - $Fminutes_M_int);
			$Fminutes_S = ($Fminutes_S * 60);
			$Fminutes_S = round($Fminutes_S, 0);
			if (strlen($Fminutes_S) == 1) $Fminutes_S = "0$Fminutes_S";
			if (strlen($Fminutes_M_int) == 1) $Fminutes_M_int = "0$Fminutes_M_int";
			$Ftime = $Fminutes_M_int . "m" . $Fminutes_S . "";
		}
		if ($precision == 'S') {
			$Ftime = $sec;
		}
		return $Ftime;
	}
}
function secondsToTime($seconds, $saida = false)
{
	$dtF = new \DateTime('@0');
	$dtT = new \DateTime("@$seconds");
	if ($saida == true) {
		return array('data' => $dtF->diff($dtT)->format('%a'), 'hora' => $dtF->diff($dtT)->format('%hh%I'));
	} else {
		return $dtF->diff($dtT)->format('%a dia(s) + %hh%I');
	}
}
function cartaoCreditoUltimos4($numero)
{
	$ultimos4 = strlen($numero) - 4;
	$saida = "";
	for ($a = 0; $a < strlen($numero); $a++) {
		if ($a < $ultimos4) $saida .= "*";
		else $saida .= substr($numero, $a, 1);
	}
	return $saida;
}
function valor($valor)
{
	return str_replace(",", ".", str_replace(".", "", $valor));
}
function verificaCpf($cpf)
{
	$s = str_replace(".", "", str_replace("-", "", $cpf));
	$c = substr($s, 0, 9);
	$dv = substr($s, 9, 2);
	$d1 = 0;
	$v = false;

	for ($i = 0; $i < 9; $i++) {
		$d1 = $d1 + substr($c, $i, 1) * (10 - $i);
	}
	if ($d1 == 0) {
		return false;
		$v = true;
	}
	$d1 = 11 - ($d1 % 11);
	if ($d1 > 9) {
		$d1 = 0;
	}
	if (substr($dv, 0, 1) != $d1) {
		return false;
		$v = true;
	}
	$d1 = $d1 * 2;
	for ($i = 0; $i < 9; $i++) {
		$d1 = $d1 + substr($c, $i, 1) * (11 - $i);
	}
	$d1 = 11 - ($d1 % 11);
	if ($d1 > 9) {
		$d1 = 0;
	}
	if (substr($dv, 1, 1) != $d1) {
		return false;
		$v = true;
	}
	if (!$v) {
		return true;
	}
}
function difHora($hora1, $hora2)
{

	$data1 = new DateTime($hora1);
	$data2 = new DateTime($hora2);

	$intervalo = $data1->diff($data2);

	$difHr = $intervalo->h;
	$difMin = $intervalo->i;
	$difSeg = $intervalo->s;

	return $intervalo->h;
}
function telefone2($tel)
{
	$tel = str_replace("_", "", $tel);
	return $tel;
}
function telefone($tel)
{
	$tel = str_replace(".", "", str_replace("_", "", str_replace("-", "", str_replace("(", "", str_replace(")", "", str_replace(" ", "", $tel))))));
	return $tel;
}

function cpf($tel)
{
	$tel = str_replace("_", "", str_replace("-", "", str_replace(".", "", str_replace(" ", "", $tel))));
	return $tel;
}

function cnpj($tel)
{
	$tel = str_replace("_", "", str_replace("-", "", str_replace(".", "", str_replace(" ", "", str_replace("/", "", $tel)))));
	return $tel;
}


function diaDaSemana($dia)
{
	switch ($dia) {
		case "0":
			return "Domingo";
		case "1":
			return "Segunda-feira";
		case "2":
			return "Terça-feira";
		case "3":
			return "Quarta-feira";
		case "4":
			return "Quinta-feira";
		case "5":
			return "Sexta-feira";
		case "6":
			return "Sábado";
	}
}
function mes($mes)
{
	switch ($mes) {
		case "1":
			$dia_semana = "Janeiro";
			break;
		case "2":
			$dia_semana = "Fevereiro";
			break;
		case "3":
			$dia_semana = "Março";
			break;
		case "4":
			$dia_semana = "Abril";
			break;
		case "5":
			$dia_semana = "Maio";
			break;
		case "6":
			$dia_semana = "Junho";
			break;
		case "7":
			$dia_semana = "Julho";
			break;
		case "8":
			$dia_semana = "Agosto";
			break;
		case "9":
			$dia_semana = "Setembro";
			break;
		case "10":
			$dia_semana = "Outubro";
			break;
		case "11":
			$dia_semana = "Novembro";
			break;
		case "12":
			$dia_semana = "Dezembro";
			break;
	}
	return $dia_semana;
}
function formataURL($url)
{
	return (substr($url, 0, 7) == "http://") ? $url : "http://" . $url;
}
function codeIn($tabela, $str)
{
	$sql = new Mysql();
	$continua = true;
	while ($continua) {
		$sql->consult($tabela, "code", "where code='" . $str . "'");
		if ($sql->rows) {
			$ultimo = substr($str, (strlen($str) - 1), 1);
			if (is_numeric($ultimo)) {
				$ultimo++;
				$str = substr($str, 0, strlen($str) - 1) . $ultimo;
			} else {
				$str .= 2;
			}
		} else $continua = false;
	}
	return str_replace("---", "-", $str);
}

function codeIn2($tabela, $str, $id)
{
	$sql = new Mysql();
	$continua = true;
	while ($continua) {
		$sql->consult($tabela, "code", "where code='" . $str . "' and id<>'" . $id . "'");
		if ($sql->rows) {
			$ultimo = substr($str, (strlen($str) - 1), 1);
			if (is_numeric($ultimo)) {
				$ultimo++;
				$str = substr($str, 0, strlen($str) - 1) . $ultimo;
			} else {
				$str .= 2;
			}
		} else $continua = false;
	}
	return str_replace("--", "-", $str);
}

function outEstado($uf)
{
	switch (strtoupper($uf)) {
		case 'AC':
			$estado = 'Acre';
			break;
		case 'AL':
			$estado = 'Alagoas';
			break;
		case 'AP':
			$estado = 'Amapá';
			break;
		case 'AM':
			$estado = 'Amazonas';
			break;
		case 'BA':
			$estado = 'Bahia';
			break;
		case 'CE':
			$estado = 'Ceará';
			break;
		case 'DF':
			$estado = 'Distrito Federal';
			break;
		case 'ES':
			$estado = 'Espírito Santo';
			break;
		case 'GO':
			$estado = 'Goiás';
			break;
		case 'MA':
			$estado = 'Maranhão';
			break;
		case 'MT':
			$estado = 'Mato Grosso';
			break;
		case 'MS':
			$estado = 'Mato Grosso do Sul';
			break;
		case 'MG':
			$estado = 'Minas Gerais';
			break;
		case 'PA':
			$estado = 'Pará';
			break;
		case 'PB':
			$estado = 'Paraíba';
			break;
		case 'PR':
			$estado = 'Paraná';
			break;
		case 'PE':
			$estado = 'Pernambuco';
			break;
		case 'PI':
			$estado = 'Piauí';
			break;
		case 'RJ':
			$estado = 'Rio de Janeiro';
			break;
		case 'RN':
			$estado = 'Rio Grande do Norte';
			break;
		case 'RS':
			$estado = 'Rio Grande do Sul';
			break;
		case 'RO':
			$estado = 'Rond&ohat;nia';
			break;
		case 'RR':
			$estado = 'Roraima';
			break;
		case 'SC':
			$estado = 'Santa Catarina';
			break;
		case 'SP':
			$estado = 'São Paulo';
			break;
		case 'SE':
			$estado = 'Sergipe';
			break;
		case 'TO':
			$estado = 'Tocantins';
			break;
		default:
			$estado = "Estado Desconhecido";
	}
	return $estado;
}


function outMes($str)
{
	if ($str == 1) return "Janeiro";
	else if ($str == 2) return "Fevereiro";
	else if ($str == 3) return "Março";
	else if ($str == 4) return "Abril";
	else if ($str == 5) return "Maio";
	else if ($str == 6) return "Junho";
	else if ($str == 7) return "Julho";
	else if ($str == 8) return "Agosto";
	else if ($str == 9) return "Setembro";
	else if ($str == 10) return "Outubro";
	else if ($str == 11) return "Novembro";
	else if ($str == 12) return "Dezembro";


	/*if($str==1) return "jan";
		else if($str==2) return "fev";
		else if($str==3) return "mar";
		else if($str==4) return "abr";
		else if($str==5) return "mai";
		else if($str==6) return "jun";
		else if($str==7) return "jul";
		else if($str==8) return "ago";
		else if($str==9) return "set";
		else if($str==10) return "out";
		else if($str==11) return "nov";
		else if($str==12) return "dez";*/
}

function outUrl($str)
{
	$str = utf8_decode($str);
	//return str_replace("'","",str_replace(" ","-",$str));
	//$str = utf8_encode(ereg_replace("[^a-zA-Z0-9_.]", "", strtr($str, "áàãâéêíóôõúüçÁÀÃÂÉÊÍÓÔÕÚÜÇ ","aaaaeeiooouucAAAAEEIOOOUUC_")));
	//return str_replace("_","-",$str);
	$str = str_replace(" ", "-", $str);
	$str = str_replace("á", "a", $str);
	$str = str_replace("à", "a", $str);
	$str = str_replace("ã", "a", $str);
	$str = str_replace("â", "a", $str);
	$str = str_replace("é", "e", $str);
	$str = str_replace("ê", "e", $str);
	$str = str_replace("í", "i", $str);
	$str = str_replace("ó", "o", $str);
	$str = str_replace("ô", "o", $str);
	$str = str_replace("õ", "o", $str);
	$str = str_replace("ü", "u", $str);
	$str = str_replace("ú", "u", $str);
	$str = str_replace("ç", "c", $str);
	$str = str_replace(".", "", $str);
	if (substr($str, strlen($str) - 1, 1) == "-") $str = substr($str, 0, strlen($str) - 1);
	return strtolower(@ereg_replace("[^a-zA-Z0-9_.-]", "", $str));
}

/**
 * generatePDF
 * @param int $id_evolucao
 * @return mixed mensagem de sucesso ou erro ao assinar o documento
 */
function generatePDF($id_evolucao)
{
	$endpoint = "https://" . $_SERVER['HTTP_HOST'] . "/services/api.php";

	$params = [];
	$params['token'] = 'ee7a1554b556f657e8659a56d1a19c315684c39d';
	$params['method'] = 'generatePDF';
	$params['infoConta'] = $_ENV['NAME'];
	$params['id_evolucao'] = $id_evolucao;
	$params['enviaWhatsapp'] = 0;

	$curl = curl_init();
	curl_setopt_array($curl, [
		CURLOPT_URL => $endpoint,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => "",
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 30,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => "GET",
		CURLOPT_POSTFIELDS => json_encode($params),
		CURLOPT_HTTPHEADER => ["Content-Type: application/json"]
	]);

	curl_exec($curl);
	$err = curl_error($curl);

	curl_close($curl);
	if ($err) {
		return false;
	} else {
		return true;
	}
}
function debug($data, $die = false)
{
	$style = "max-width: 100%; word-wrap: break-word;background-color:#ccc;font-size:15px;font-family: Consolas, Courier New, monospace;padding:20px;";
	echo "<div style='$style'>";
	echo "<hr>";
	echo "<pre style='white-space: pre-wrap;margin-left: 150px'>";
	print_r($data);
	echo "</pre>";
	echo "<hr>";
	echo "</div>";
	if ($die) {
		die();
	}
	return;
}

function __autoload($class_name)
{
	$classSFTP = array('AMQPStreamConnection', 'TCPDF', 'S3Client');
	if (!in_array($class_name, $classSFTP)) {
		require_once("class/class" . $class_name . ".php");
	}
}
