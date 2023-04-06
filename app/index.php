<?php 
	// teste
	if(isset($_GET['instancia'])) {
		setcookie("infoName", $_GET['instancia'], time() + 3600*24, "/");
		header("Location: ./");
	} else if(!isset($_COOKIE['infoName'])) {
		setcookie("infoName", "studiodental", time() + 3600*24, "/");
	}
	include "includes/header.php";
?>

<section class="login">

	<div class="login-bg">
		<img src="img/login-bg.jpg" alt="" width="800" height="1080" class="login-bg__img" />
		<div class="login-bg-logo">
			<img src="img/logo-reduzido.svg" alt="" width="60" height="55" />
		</div>
	</div>

	<div class="login-form">
		
		<div class="login-form__inner1">
			<?php
			$image="";
			$sql = new Mysql();
			$sql->consult($_p."clinica","*","");
			if($sql->rows) {
				$clinica=mysqli_fetch_object($sql->mysqry);
				if(!empty($clinica->cn_logo)) {
					$image=$_cloudinaryURL.'c_thumb,w_600/'.$clinica->cn_logo;
				}
			}
			if(!empty($image)) {
			?>
			<img src="<?php echo $image;?>" alt="" width="484" height="68" class="login-form__logo" />
			<?php
			} 
			?>
			
			<form method="post" action="usuarios/login.php" class="form formulario-validacao">
				<input type="hidden" name="url" value="<?php echo isset($_GET['url'])?str_replace("erro=1&url=","",str_replace("erro=2&url=","",str_replace("erro=3&url=","",str_replace("erro=4&url=","",str_replace("erro=5&url=","",str_replace("erro=6&url=","",$_SERVER['QUERY_STRING'])))))):"";?>" />
				<dl>
					<dd class="form-comp"><span><i class="iconify" data-icon="fluent:person-12-regular"></i></span><input type="text" name="auth_cpf" class="obg" placeholder="Login" /></dd>
				</dl>
				<dl>
					<dd class="form-comp"><span><i class="iconify" data-icon="fluent:lock-closed-12-regular"></i></span><input type="password" name="auth_senha" class="obg" placeholder="Senha" /></dd>
				</dl>
				<dl>
					<dd><button type="submit" class="button button_main button_full">Entrar</button></dd>
				</dl>
				<?php /*<dl>
					<dd><a href="">Esqueci minha senha <i class="iconify" data-icon="fluent:arrow-right-20-filled"></i></a></dd>
				</dl>*/ ?>
			</form>

		</div>

	</div>

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
		} else if($_GET['erro']==6) {
			$jsc=new Js();
			$jsc->jAlert("Seu acesso se encontrado DESATIVADO!","erro","");
		}  else if($_GET['erro']==7) {
			$jsc=new Js();
			$jsc->jAlert("A sua conta não foi habilitada.<br /><br />Favor entrar em contato com o nosso suporte!<br /><br /><a href=https://api.whatsapp.com/send/?phone=55$_whatsappSuporte target=_blank class=button><span class=iconify data-icon=logos:whatsapp-icon></span> Falar no Whatsapp</a>","erro","");
		} else if($_GET['erro']==1000) {
			$jsc=new Js();
			$jsc->jAlert(($_GET['msg']),"erro","");
		}
	}
	?>


</section>


<?php 
include "includes/footer.php";
?>	