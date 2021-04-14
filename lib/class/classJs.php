<?php

	class Js {
	

		function go($url) {
			echo '<script>document.location.href=`'.$url.'`;</script>';
			die();
		}
		function alert($msg,$callback='') {
			echo "<script language=\"javascript\">$(function(){";
			echo "alert('".$msg."');";
			if(!empty($callback)) echo "$callback";
			echo "});</script>";
		}
		
		function jAlert($msg,$titulo,$callback="") {
			echo "<script language=\"javascript\">$(function(){\n";
			if($titulo=="erro") {
				if($callback) {
					echo 'swal({   title: "Erro!",   html:true,text: "'.$msg.'", type: "error", allowEscapeKey:false, allowOutsideClick:false, confirmButtonColor: "#424242",  closeOnConfirm: true }, function(){   '.$callback.' });';
				} else {
					echo 'swal({   title: "Erro!",  html:true, text: "'.$msg.'",   type: "error", allowEscapeKey:false, allowOutsideClick:false, confirmButtonColor: "#424242",  closeOnConfirm: false });';
				}
			} else {
					if($callback) {
						echo 'swal({   title: "Sucesso!",  html:true,  text: "'.$msg.'",   type: "success", allowEscapeKey:false, allowOutsideClick:false, confirmButtonColor: "#424242",  closeOnConfirm: false }, function(){   '.$callback.' });';
					} else {
						echo "swal({title: 'Sucesso!', html:true, text: '".$msg."', type:'success',allowEscapeKey:false, allowOutsideClick:false, confirmButtonColor: '#424242'});";
					}
			
			}
			echo "});</script>";
		}
		
		
	}
?>