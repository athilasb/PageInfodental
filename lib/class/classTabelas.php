<?php
	
	/*
	
CREATE TABLE `apu_inicial_banners` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `lixo` int(11) NOT NULL,
  `pub` int(11) NOT NULL,
  `titulo` varchar(150) NOT NULL,
  `data` date NOT NULL,
  `foto` varchar(10) NOT NULL,
  `url` varchar(250) NOT NULL,
  `nova_janela` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;

*/
	class Tabelas {
	
		public $campos,$tabela,$pre;
	
		function __conscruct() {
			
		}
		
		function criarTabelas() {
			$sql = new Mysql();
			
			$cmp=$this->campos;
			$pre=$this->pre;
			$tbl=$this->tabela;
			$tipo=$this->tipo;
			
			
			$sql->exists($tbl);
			if($sql->mysqry) {// echo "ja tem table";
				return;
			}
						
			$codigo="";
			
			foreach($cmp as $v) {
				
				
				echo $v."<BR>";
				continue;
				
				//echo $v['type']."<br>";
				
				if($v['id']=="id") continue;
				
				if($v['type']=="checkbox" || $v['type']=="pub") {
					$codigo.="`".$v['id']."` int(11) NOT NULL,";
				}
				
				
				else if($v['type']=="data") {
				
					if($v['id']=="data") {
						$codigo.="`data` date NOT NULL,";
					}
				
				}	
				
				
				
				else if($v['type']=="datahora") {
				
					if($v['id']=="data") {
						$codigo.="`data` datetime NOT NULL,";
					}
				
				}
				
				
				else if($v['type']=="text") {
				
					$max=isset($v['maxlength'])?$v['maxlength']:"150";
					
					$codigo.="`".$v['id']."` varchar(".$max.") NOT NULL,";
				
				}
				
				else if($v['type']=="fck" or $v['type']=="textarea") {
					
					$codigo.="`".$v['id']."` text NOT NULL,";
				
				}
				
				else if($v['type']=="referencia") {
					
					$codigo.="`".$v['id']."` int(11) NOT NULL,";
				
				}
				
				
				else if($v['type']=="foto" or $v['type']=="file") {
					
					$codigo.="`".$v['id']."` varchar(10) NOT NULL,";
					
					$dir=$v['dir']; 
					//echo $dir;
					if(!file_exists($dir)) {
						
						$auxDir=str_replace("arqs/","",$dir);
						$auxDir=explode("/",$auxDir);
							//echo $dir;
						if(count($auxDir)>2) {
							
							$pastas="";
							foreach($auxDir as $x) { 
								if(!file_exists("arqs/".$x)) { 
									mkdir("arqs/".$pastas.$x,0777);
									$pastas.=$x."/";
								} else {
									$pastas.=$x."/";
								}
							}
						} else {
							mkdir($dir,0777);
						}
						//echo $auxDir;
						
						//mkdir($dir,0777);
						
					} 
				
				}
				
				//["type"]=> string(8) "checkbox" ["id"]=> string(3) "pub" ["show"]=> string(9) "Publicado" ["obg"]=> bool(true)
			}
			
			$codigo.="PRIMARY KEY (`id`)) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
			
			
			if(isset($this->code)) {
				$codeSQL.="`code` varchar(250) NOT NULL,";
			} else $codeSQL="";
			
			if($this->tipo=="registros") {
				$codigo="CREATE TABLE `".$tbl."` (
						`id` int(11) NOT NULL AUTO_INCREMENT,
  						`lixo` int(11) NOT NULL,".$codeSQL.$codigo;
			} else {
				$codigo="CREATE TABLE `".$tbl."` (
						`id` int(11) NOT NULL AUTO_INCREMENT,".$codeSQL.$codigo;
			
			}
			$qry=mysqli_query($sql->connecting, $codigo) or die(mysqli_error($sql->connecting));
			
			if($this->tipo=="unico") {
				$sql->add($tbl,"id=''");
			
			}
			
			//echo $codigo;
			
		}
	
	}
?>