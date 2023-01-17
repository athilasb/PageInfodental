<?php
class StringW {
	
	function description($str,$num=200) {
		$str=str_replace("\r\n","",$str);
		$str=strip_tags($str);
		$strProc = new StringW();
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