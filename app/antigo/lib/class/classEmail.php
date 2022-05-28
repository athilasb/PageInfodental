<?php
	/*require_once("lib/phpmailer/class.phpmailer.php");
	
	class Email {
		public $email,$assunto,$msg;
		
		
		function enviar() {
			
			$msg=$this->msg;
			$email=$this->email;
			$assunto=$this->assunto;
			
			$msg= "<div style=\"width:100%; background:#ebebeb; padding:20px 0;\"><div style=\"display:block; margin:0 auto; width:600px; padding:20px 20px 20px 20px; border:1px solid #ddd; border-radius:10px; background:#fff; font-family:helvetica, arial;\">
								<p>".$msg."</p>
								<br /><br />
								<p>
								<strong>Grupo Via Mais</strong><br />
								<a href=\"http://www.grupoviamais.com/marketing\">www.grupoviamais.com/marketing</a><br /><br /><br />
								</p></div></div>";
								
			
			$headers  = "From: Grupo Via Mais <falecom@grupoviamais.com>\n";
			$headers  .= "Reply-To: Grupo Via Mais <falecom@grupoviamais.com>\n";
			$headers  .= "Return-Path: Grupo Via Mais <falecom@grupoviamais.com>\n";
			$headers .= "MIME-Version: 1.0\n";
			$headers .= "Content-type: text/html; charset=iso-8859-1\n";
			//echo $email."wlib";
			return mail($email,$assunto,$msg,$headers)?true:false;
		}
	}*/
	
	class Email {
		public $email,$assunto,$msg;
		
		
		function enviar() {
			
			if(isset($this->diretorio)) $dir=$this->diretorio;
			else $dir='';
			
			require_once($dir."lib/phpmailer/class.phpmailer.php");
			
			$msg=$this->msg;
			$email=$this->email;
			$assunto=$this->assunto;
			
			
			
			
			$mail = new PHPMailer();
			$mail->IsHTML(true);
			$mail->IsSMTP();
			$mail->CharSet="UTF-8";
			$mail->SMTPAuth   = true;
			//$mail->SMTPDebug   = true;
			//$mail->SMTPSecure = "ssl";
			$mail->Port   = 587;
			$mail->Host       = "smtp.umbler.com";
			$mail->Username =   "naoresponda@grupoviamais.com";
			$mail->Password =   "web@wlib123"; 
			$mail->Subject  = $assunto;
			if(isset($this->reply) and is_array($this->reply)) {
				foreach($this->reply as $v) {
					if($v) {
						$mail->AddReplyTo($v);
					}
				}
			}
			$mail->From = "naoresponda@grupoviamais.com";
			$mail->FromName = "Grupo Via Mais";
			if(is_array($email)) {
				foreach($email as $v) {
					if($v) {
						$mail->AddAddress($v);
					}
				}
			} else {
				$mail->AddAddress($email);
			}
			$mail->AltBody = strip_tags($msg);
			$mail->Body = "<div style=\"width:100%; background:#ebebeb; padding:20px 0;\"><div style=\"display:block; margin:0 auto; width:600px; padding:20px 20px 20px 20px; border:1px solid #ddd; border-radius:10px; background:#fff; font-family:helvetica, arial;\">
								<p>".$msg."</p></div></div>";
			echo $mail->Send();
		}
	}
?>