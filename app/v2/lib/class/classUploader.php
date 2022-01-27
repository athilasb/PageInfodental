<?php
class Uploader {

	// upload("Foto 1","$_FILES['foto']","f1_",900000,241,156,"estilovida/fotos/","id");
	function uploadCorta($nome="",$foto="",$prefixo="",$tamanho="",$width="",$height="",$pasta="",$id="") { 
		if(!file_exists($foto['tmp_name'])) $this->resul="Erro na ".$nome.": A imagem nao foi localizada!";
		else {
			if(!@eregi("^(image)/(pjpeg|jpeg)$", $foto['type']) and !@eregi("^(image)/(gif)$", $foto['type']) and !@eregi("^(image)/(png)$", $foto['type'])) $this->resul="Erro na ".$nome.": Só é permitido imagens com extençao <b>.JPG, .GIF ou .PNG</b>!";
			else { 
				if($foto['size']>=$tamanho) $this->resul="Erro na ".$nome.": A figura é maior que o permitido!";
				else { 
					$ar=explode(".",$foto['name']);
					$ext=strtolower($ar[sizeof($ar)-1]);
					$fotonome=$prefixo.$id.".".$ext;
					$this->ext=$ext;
					//die("$fotonome");
					$this->redimCorta($foto,$width,$height,$pasta.$fotonome,$nome);
					if(isset($this->error)) $this->resul=$this->error;
				} // tamanho
			} // nome
		} // existe
		if(isset($this->resul)) $this->erro=1;
		else {
			$this->erro=0;
			$this->nome=$fotonome;
		}
	}

	function redimCorta($deffoto,$deflargura,$defaltura,$defdir,$nome="") {
		$defdir=@strtolower($defdir);
		$img=new Canvas();
		$img->carrega( $deffoto['tmp_name'] )
			->redimensiona( $deflargura, $defaltura, 'crop' )
			->hexa( '#FFFFFF' )
			//->legenda( 'LOL Catz!', 20, 'meio', 'centro', '#FF005C', true, 'Aller_Bd.ttf' )
			->grava($defdir);
			
	}	

	// upload("Foto 1","$_FILES['foto']","f1_",900000,241,156,"estilovida/fotos/","id");
	function upload($nome,$foto,$prefixo,$tamanho,$width,$height,$pasta,$id) {
		if(!file_exists($foto['tmp_name'])) $this->resul="Erro na ".$nome.": A imagem nao foi localizada!";
		else {
			if(!@eregi("^(image)/(pjpeg|jpeg)$", $foto['type'])) $this->resul="Erro na ".$nome.": Só é permitido imagens com extençao <b>.JPG</b>!";
			else {
				if($foto['size']>=$tamanho) $this->resul="Erro na ".$nome.": A figura é maior que o permitido!";
				else {
					$ar=explode(".",$foto['name']);
					$ext=strtolower($ar[sizeof($ar)-1]);
					$fotonome=$prefixo.$id.".".$ext;
					$this->ext=$ext;
					//die($pasta.$fotonome);
					$this->redim($foto,$width,$height,$pasta.$fotonome);
					if($this->error) $this->resul=$this->error;
				} // tamanho
			} // nome
		} // existe
		if($this->resul) $this->erro=1;
		else {
			$this->erro=0;
			$this->nome=$fotonome;
		}
	}

	function redim($deffoto,$deflargura,$defaltura,$defdir) {
		$defdir=@strtolower($defdir);
		$img=@imagecreatefromjpeg($deffoto[tmp_name]);
		$larg=@imagesx($img);
		$altu=@imagesy($img);
		$img=new Canvas();
		$img->carrega( $deffoto['tmp_name'] )
			
			->redimensiona( $deflargura, $delaltura )
			->hexa( '#FFFFFF' )
			->grava($defdir);
	}


	

	// upload("Foto 1","$_FILES['foto']","f1_",900000,241,156,"estilovida/fotos/","id");
	function upload_2($nome,$foto,$prefixo,$tamanho,$width,$height,$pasta,$id) {
		if(!file_exists($foto['tmp_name'])) $this->resul="Erro na ".$nome.": A imagem nao foi localizada!";
		else {
			if(!@eregi("^(image)/(pjpeg|jpeg)$", $foto['type'])) $this->resul="Erro na ".$nome.": Só é permitido imagens com extençao <b>.JPG</b>!";
			else {
				if($foto['size']>=$tamanho) $this->resul="Erro na ".$nome.": A figura é maior que o permitido!";
				else {
					$ar=explode(".",$foto['name']);
					$ext=strtolower($ar[sizeof($ar)-1]);
					$fotonome=$prefixo.$id.".".$ext;
					$this->ext=$ext;
					$this->redim_2($foto,$width,$pasta.$fotonome);
					if($this->error) $this->resul=$this->error;
				} // tamanho
			} // nome
		} // existe
		if($this->resul) $this->erro=1;
		else {
			$this->erro=0;
			$this->nome=$fotonome;
		}
	}

	function redim_2($deffoto,$deflargura,$defdir) {
		$defdir=@strtolower($defdir);
		$img=@imagecreatefromjpeg($deffoto[tmp_name]);
		$larg=@imagesx($img);
		$altu=@imagesy($img);
		if($larg < $deflargura) {
			$this->error="A imagem tem que ter no mínimo ".$deflargura."px";
		} else {
			$def_prorp = $defaltura/$deflargura;
			$nor_prorp = $altu/$larg;
			$nlargura=$deflargura;
			$naltura=(($nor_prorp)*$deflargura); 
			$centro1=($nlargura-$deflargura)/2;
			$centro2=($naltura-$defaltura)/2;
			echo $nlargura."x".$naltura;
			$imgred=@imagecreatetruecolor($nlargura,$naltura);
			@imagecopyresampled($imgred, $img, 0, 0, 0, 0, $nlargura, $naltura, $larg, $altu);
			@imagejpeg($imgred,$defdir,100);
			$this->erro="no";
			@imagedestroy($img);
			@imagedestroy($imgred);
		}
	}
	// upload("Foto 1","$_FILES['foto']","f1_",900000,241,156,"estilovida/fotos/","id");
	function upload_3($nome,$foto,$prefixo,$tamanho,$width,$height,$pasta,$id) {
		if(!file_exists($foto['tmp_name'])) $this->resul="Erro na ".$nome.": A imagem nao foi localizada!";
		else {
			if(!@eregi("^(image)/(pjpeg|jpeg)$", $foto['type'])) $this->resul="Erro na ".$nome.": Só é permitido imagens com extençao <b>.JPG</b>!";
			else {
				if($foto['size']>=$tamanho) $this->resul="Erro na ".$nome.": A figura é maior que o permitido!";
				else {
					$ar=explode(".",$foto['name']);
					$ext=strtolower($ar[sizeof($ar)-1]);
					$fotonome=$prefixo.$id.".".$ext;
					$this->ext=$ext;
					$this->redim_3($foto,$height,$width,$pasta.$fotonome);
					if($this->error) $this->resul=$this->error;
				} // tamanho
			} // nome
		} // existe
		if($this->resul) $this->erro=1;
		else {
			$this->erro=0;
			$this->nome=$fotonome;
		}
	}

	function redim_3($deffoto,$defaltura,$deflargura,$defdir) {
		$defdir=@strtolower($defdir);
		$img=@imagecreatefromjpeg($deffoto[tmp_name]);
		$larg=@imagesx($img);
		$altu=@imagesy($img);
		$img=new Canvas();
	//	echo $deflargura."x".$defaltura;die();
		$img->carrega( $deffoto['tmp_name'] )
			
			->redimensiona( "", $defaltura )
			->hexa( '#cc3300' )
			->grava($defdir);
	}
}

?>