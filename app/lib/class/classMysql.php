<?php
class Mysql {
	private $ms_server, $ms_login, $ms_senha, $ms_db;
	public $mysqry, $rows;
	
	function __construct($chatpro=false) { 
		/*if($_SERVER['HTTP_HOST']=="studiodental.dental") {
			$ms_server="localhost";
			$ms_login="root";
			$ms_senha="l1bdab110";
			$ms_db="infodental";
		} else {
			$ms_server=$_ENV['MYSQL_HOST'];
			$ms_login=$_ENV['MYSQL_USER'];
			$ms_senha=$_ENV['MYSQL_PASS'];
			$ms_db=$_ENV['MYSQL_DB'];
		
		}*/

		$ms_server="localhost";
		$ms_login="andre";
		$ms_senha="andre0099@";
		$ms_db="infodental";

		
		$this->connecting=mysqli_connect($ms_server, $ms_login, $ms_senha);
		if(isset($chatpro) and $chatpro===true) {
			mysqli_set_charset($this->connecting,'utf8mb4');
		}
		else mysqli_set_charset($this->connecting,'latin1');
		$dbing=mysqli_select_db($this->connecting, $ms_db);
	}
	function colunas($ms_table) {
		$columns="SHOW COLUMNS FROM $ms_table";
		$this->mysqry=mysqli_query($this->connecting, $columns) or die(mysqli_error($this->connecting));
	}
	function del($ms_table,$ms_arg) {
		$deleting="DELETE from $ms_table $ms_arg";
		$qry=mysqli_query($this->connecting, $deleting) or die(mysqli_error($this->connecting));
		$this->rows = mysqli_affected_rows($this->connecting);
	}
	function update($ms_table,$ms_fields,$ms_arg) {
		$updating="UPDATE $ms_table set $ms_fields $ms_arg";
		$qry=mysqli_query($this->connecting, $updating) or die(mysqli_error($this->connecting));
		if(mysqli_affected_rows($this->connecting)<0) $this->resul="erro";
		else $this->resul="ok";
	}
	function add($ms_table,$ms_values) {
		$inserting="INSERT INTO $ms_table SET $ms_values";
		$qry=mysqli_query($this->connecting, $inserting) or die(mysqli_error($this->connecting));
		if(mysqli_affected_rows($this->connecting)==0) $this->resul="erro";
		$this->ulid=mysqli_insert_id($this->connecting);
	}
	function consult($ms_table,$ms_fields,$ms_arg) {
		$sql="SELECT $ms_fields from $ms_table $ms_arg";
		$this->mysqry=mysqli_query($this->connecting, $sql) or die(mysqli_error($this->connecting));
		$this->rows=mysqli_num_rows($this->mysqry);
	}
	function exists($ms_table) {
		$sql="SELECT * from $ms_table";
		$this->mysqry=mysqli_query($this->connecting, $sql);
		
	}
	function consultPagMto2($mysqltabela,$mysqlcampos,$myslimite,$mysargumentos,$mysurl,$inter,$pagnome,$root="") {
		$myssql="SELECT count(*) as total from ".$mysqltabela." ".$mysargumentos;
		//$myssql="SELECT ".$mysqlcampos." from ".$mysqltabela." ".$mysargumentos;
		$PHP_SELF=basename($_SERVER['PHP_SELF']);
		
		$trab=$pagnome;
		if(isset($_GET[$trab]) and is_numeric($_GET[$trab])) $pagina=$_GET[$trab];
		else $pagina=0;
		
		$mysqry=mysqli_query($this->connecting, $myssql) or die(mysqli_error($this->connecting));
		$this->mysqryPag=$mysqry;
		$t=mysqli_fetch_object($mysqry);
		if($t->total==0) {
			$this->rows=0; 
			$this->mysqry=$mysqry;
			return false;
		}
		
		$mystotal=$t->total;
		$myspaginas=ceil($mystotal / $myslimite); 
		if(isset($_GET[$trab]) and $pagina>=$myspaginas) {
			$pagina=$myspaginas-1;
		}
		if(!isset($pagina)) $mypagina=0;
		
		if(is_numeric($pagina)) $mysinicio=$myslimite * $pagina;
		else $mysinicio=0;
		
		$myssql="SELECT $mysqlcampos from $mysqltabela $mysargumentos LIMIT $mysinicio, $myslimite";
		$this->rows=$t->total;
		
		$this->mysqry=mysqli_query($this->connecting, $myssql) or die(mysqli_error($this->connecting));
		if($t->total<=0) $this->mysvazio="<div align=\"center\"><br /><br />Não foi encontrado nenhum registro!<br /><br /></div>";
		if($mysurl!="") $mysurl="&".$mysurl;
		if($pagina > 0) {
			$mysnewpage=$pagina-1;
			//$mysurl1="<a href=\"".$root."?".$trab."=".$mysnewpage."".$mysurl."\" class=\"".$cssPro."\">&nbsp;&laquo; Anterior&nbsp;</a> |";
		}
		$mod=$pagina/($myspaginas/$inter);
		if($pagina<$inter) {
			$showFim=$inter;
			$showInicio=0;
		}
		else {
			$showInicio=$inter*(int)($pagina/$inter)-1;
			$showFim=($inter*(int)($pagina/$inter))+$inter;
		}
		if($showFim>$myspaginas) $showFim=$myspaginas-1;
		$mysurl2=$_mysurl2=$mysurl1=$mysurl3="";
		
		$mysurl22=$mysurl11=$mysurl33="";
		for($xx=$showInicio;$xx <= $showFim;$xx++) {
			$i=$xx+1;
			if($pagina==$xx) {
				$_mysurl2.="<a href=\"".$root.$xx."\" class=\"pagination__active\">".$i."</a>";
				$mysurl2.="<a href=\"".$root.$xx."\" class=\"active\">".$i."</a>";
				$mysurl22.="<a href=\"".$root.$xx."\" class=\"active\">".$i."</a>";
			}
			else {
				$_mysurl2.="<a href=\"".$root.$xx."\">".$i."</a>";
				$mysurl2.="<a href=\"".$root.$xx."\">".$i."</a>";
				$mysurl22.="<a href=\"".$root.$xx."\">".$i."</a>";
			}
		}
		if($pagina < ($myspaginas - 1)) {
			$mysnewpage=$pagina+1;
			//$mysurl3="| <a href=\"".$PHP_SELF."?".$trab."=".$mysnewpage."".$mysurl."\" class=\"".$cssPro."\">&nbsp;Pr&oacute;xima &raquo;&nbsp;</a>";
		}
		$this->myspaginacao="".$mysurl1."".$mysurl2."".$mysurl3."";
		$this->myspaginacao2=$_mysurl2;
		
		if(!($myspaginas-1)) $this->myspaginacao="";
	}
}
?>
