<?php
class Img {
	private $root;
	function __construct($root="") {
		$this->root=$root;
	}
	function thumb($foto,$deflargura,$defaltura) {
		$img=imagecreatefromjpeg($foto);
		$larg=imagesx($img);
		$altu=imagesy($img);
		if($altu>$larg) {
		$naltura=$defaltura;
		$nlargura=round(($larg*$naltura)/$altu);
		$imgred=@imagecreatetruecolor($nlargura,$defaltura);
		}
		else {
		$nlargura=$deflargura;
		$naltura=round(($altu*$nlargura)/$larg);
		if($naltura>$defaltura) $imgred=@imagecreatetruecolor($deflargura,$defaltura);
		else $imgred=@imagecreatetruecolor($deflargura,$naltura);
		}
		$this->wid=$nlargura;
		$this->hei=$naltura;
	}
	// po("1.jpg","arq/transp/","larg","larg","style","tag");
	function po($foto,$dir,$dlargura,$daltura,$style,$ins,$entThumb="",$entAdm="",$entPop="",$entTp="") {
		if($entAdm)
			$dir2=str_replace("../","",$dir);
		else 
			$dir2=$dir;
		if(!$entThumb) 
			$entThumb=$entTp."thumb";
		else
			$entThumb=$entTp."thumb2";
		if($entPop)
			$trs="../";
		else 
			$trs="";
		$sep=explode(".", $foto);
		$ext=$sep[sizeof($sep)-1];
		$ext=strtolower($ext);
		if($ext=="gif") $img=@imagecreatefromgif($dir.$foto);
		elseif($ext=="jpg") $img=@imagecreatefromjpeg($dir.$foto);
		elseif($ext=="png") $img=@imagecreatefrompng($dir.$foto);
		$largura=@imagesx($img);
		$altura=@imagesy($img);
		@imagedestroy($img);
		$dirfoto=$dir.$foto;
		$pop=new Img();
		$pop->popup($dirfoto);
		if($style) $show_style=" style=\"".$style."\"";
		else $show_style='';
		//$this->show="<a href=\"javascript: javascript:pop('".$trs."popup.php?foto=".$dir2.$foto."','Transporte',".$pop->width.",".$pop->height.",'no');\"><img src=\"".$this->root.$entThumb.".php?deffoto=".$dir2.$foto."&defaltura=".$daltura."&deflargura=".$dlargura."\" border=\"0\"".$show_style." ".$ins." /></a>";
		$this->show2="<img src=\"".$this->root.$entThumb.".php?deffoto=".$dir2.$foto."&defaltura=".$daltura."&deflargura=".$dlargura."\" border=\"0\"".$show_style." ".$ins." />";
	//	$this->show3="<a href=\"".$dir2.$foto."\" title=\"\" class=\"modalamplia\"><img src=\"".$entThumb.".php?deffoto=".$dir2.$foto."&defaltura=".$daltura."&deflargura=".$dlargura."\" border=\"0\"".$show_style." ".$ins." /></a>";
		//$this->lin="<a href=\"javascript:pop('popup.php?foto=".$dirfoto."','Transporte',".$pop->width.",".$pop->height.",'no');\"".$style.">";
		$this->img=$entThumb.".php?deffoto=".$dir2.$foto."&defaltura=".$daltura."&deflargura=".$dlargura;
	}
	
	function popup($foto) {
		$sep=explode(".", $foto);
		$ext=$sep[sizeof($sep)-1];
		$ext=strtolower($ext);
		if($ext=="jpg") $img=@imagecreatefromjpeg($foto);
		else if($ext=="gif") $img=@imagecreatefromgif($foto);
		else if($ext=="png") $img=@imagecreatefrompng($foto);
		$this->width=@imagesx($img);
		$this->height=@imagesy($img);
		@imagedestroy($img);
	}
}
?>