<?php
include "includes/header.php";
?>
<section class="login">

	<section class="login__content">
		
		<div class="login-logo">
			<img src="img/logo.svg" width="52" height="50" alt="Info Dental" />
		</div>

		<?php 
		//var_dump($_ENV);
		/*
		<div>
			<img src="img/logo-cliente.png" width="135" height="49" alt="" class="login__cliente" />
		</div>
		*/ ?>

		<form method="post" action="usuarios/login.php" class="form formulario-validacao">
			<input type="hidden" name="url" value="<?php echo isset($_GET['url'])?str_replace("erro=1&url=","",str_replace("erro=2&url=","",str_replace("erro=3&url=","",str_replace("erro=4&url=","",str_replace("erro=5&url=","",str_replace("erro=6&url=","",$_SERVER['QUERY_STRING'])))))):"";?>" />
			<dl>
				<dt>Login</dt>
				<dd><input type="text" name="auth_cpf" class="obg" /></dd>
			</dl>
			<dl>
				<dt>Senha</dt>
				<dd><input type="password" name="auth_senha" class="obg" /></dd>
			</dl>
			<dl>
				<dd><button type="submit" class="button">Acessar</button></dd>
			</dl>
		</form>
		
	</section>

</section>

	<?php 
	if(isset($_GET['erro'])) {
		if($_GET['erro']==1) {
			$jsc=new Js();
			$jsc->jAlert("Login e/ou senha incorretos!","erro","");
		} else if($_GET['erro']==2) {
			$jsc=new Js();
			$jsc->jAlert("Você não está autenticado","erro","");
		} else if($_GET['erro']==3) {
			$jsc=new Js();
			$jsc->jAlert("Sua jornada diária de trabalho esgotou","erro","");
		} else if($_GET['erro']==4) {
			$jsc=new Js();
			$jsc->jAlert("Sua jornada de hoje inicia-se às ".$_GET['hora'],"erro","");
		} else if($_GET['erro']==5) {
			$jsc=new Js();
			$jsc->jAlert("Você não está vinculado a nenhuma empresa.","erro","");
		}else if($_GET['erro']==6) {
			$jsc=new Js();
			$jsc->jAlert("Seu acesso se encontrado DESATIVADO!","erro","");
		}else if($_GET['erro']==1000) {
			$jsc=new Js();
			$jsc->jAlert(($_GET['msg']),"erro","");
		}
	}
	?>

</body>
</html>
