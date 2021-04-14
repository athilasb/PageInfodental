<?php
class String {
	function subs($textov,$sim) {
		if($sim) $texto=nl2br($textov);
		else $texto=$textov;
		$texto=str_replace("[neg]","<b>", $texto);
		$texto=str_replace("[/neg]","</b>", $texto);
		$texto=str_replace("[sub]","<u>", $texto);
		$texto=str_replace("[/sub]","</u>", $texto);
		$texto=str_replace("[ita]","<i>", $texto);
		$texto=str_replace("[/ita]","</i>", $texto);
		$texto=str_replace("[top]","<font face=arial color=#8D2727 size=2>", $texto);
		$texto=str_replace("[/top]","</font>", $texto);
		$texto=str_replace("[ico]","<img src=\"img/seta_laranja.gif\" border=\"0\">", $texto);
		$texto=str_replace("[link=|","<a href=", $texto);
		$texto=str_replace("| target=blank]"," target=_blank class=descricao2>", $texto);
		$texto=str_replace("|]"," class=descricao2>", $texto);
		$texto=str_replace("[/link]","</a>", $texto);
		$texto=str_replace("[tabela]","<table celpadding=\"0\" cellspacing=\"1\" border=\"0\">\n", $texto);
		$texto=str_replace("[/tabela]","</table>\n", $texto);
		$texto=str_replace("[linha]","<tr>\n", $texto);
		$texto=str_replace("[/linha]","</tr>\n", $texto);
		$texto=str_replace("[col]","<td style=\"font-family:Verdana;font-color:#333333;border:solid 1px;border-color:#CCCCCC;padding:3px;\">\n", $texto);
		$texto=str_replace("[/col]","</td>\n", $texto);
		return $texto;
	}
	function description($str,$num=200) {
		$str=str_replace("\r\n","",$str);
		$str=strip_tags($str);
		$strProc = new String();
		return $strProc->res($str,$num);
	}
	function replacer($rpc,$texto) {
		return $texto;
	}
	
	function res($string,$qnts) {
		if(strlen($string)>$qnts) {
			$x=0;
			while(@eregi("[., ;><:/_@#!?-]$", substr($string,0,$qnts-$x))) {
				$ret=substr($string,0,$qnts-$x)."...";
				$x++;
			}
			$ret=substr($string,0,$qnts-$x)."...";
		}
		else $ret=$string;	
		return $ret;
	}
	
	function protege($entStr) {
		$str=htmlspecialchars($entStr);
		$str=str_replace("\"","&quot;",$str);
		$str=str_replace("'","&prime;",$str);
		return $str;
	}
	
	
}
?>