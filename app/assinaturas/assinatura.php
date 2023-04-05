<?php
$doc_status = "0";

require_once("../lib/conf.php");
$sql = new Mysql();

//normalmente essa pesquisa fica em cada arquivo 
//necessário colocar no topo do arquivo porque precisava fazer uma verificação de cpf
$evolucao = $tipo = $paciente = $clinica = $solicitante = "";
$exames = $_exames = array();
if (isset($_GET['id'])) {
	$sql->consult($_p . "pacientes_evolucoes", "*", "where md5(id)='" . addslashes($_GET['id']) . "'");
	if ($sql->rows) {

		$evolucao = mysqli_fetch_object($sql->mysqry);
		$sql->consult($_p . "colaboradores", "id,nome", "where id=$evolucao->id_usuario");
		if ($sql->rows) {
			$solicitante = mysqli_fetch_object($sql->mysqry);
		}
		$sql->consult($_p . "pacientes", "*", "where id=$evolucao->id_paciente");
		if ($sql->rows) {
			$paciente = mysqli_fetch_object($sql->mysqry);
		}
		$sql->consult($_p . "pacientes_evolucoes_tipos", "*", "where id = $evolucao->id_tipo");
		if ($sql->rows) {
			$tipo = mysqli_fetch_object($sql->mysqry);
		}

		//encontrar uma forma de mostrar a evolução em pdf
		$pdf = "javascript:;";
		$aux;
		switch ($evolucao->id_tipo) {
			case "1":
				$aux = "anamneses";
				break;
			case "4":
				$aux = "atestados";
				break;
			case "6":
				$aux = "pedidoExames";
				break;
			case "7":
				$aux = "receituarios";
				break;
			case "10":
				$aux = "documentos";
				break;
			default:
				$jsc=new Js();
				echo "Erro ao acessar o documento, contate o suporte.";
				$jsc->jAlert("Erro ao acessar o documento, contate o suporte.","erro","");
				die();
			break;
		}
		$pdf = "https://infodental.s3.fr-par.scw.cloud/" . $_ENV['NAME'] . "/arqs/pacientes/".$aux."/".sha1($evolucao->id).".pdf";
	}
}

$sql->consult($_p . "clinica", "*", "where id=1");
$unidade = mysqli_fetch_object($sql->mysqry);

$endereco = $imagem = '';

$endereco = utf8_encode($unidade->endereco);

$sql->consult($_p . "clinica", "*", "");
if ($sql->rows) {
	$clinica = mysqli_fetch_object($sql->mysqry);
	if (!empty($clinica->cn_logo)) {
		$imagem = $_cloudinaryURL . 'c_thumb,w_600/' . $clinica->cn_logo;
	}
}

//verificando se o documento já foi assinado previamente
$sql->consult($_p . "pacientes_assinaturas", "id_evolucao", "where id_evolucao=$evolucao->id");
if ($sql->rows) {
	$doc_status = "2";
}

if (isset($_POST['conf']) && $_POST['conf'] == true) {
	$rtn;
	if ($_POST['cpf_ent'] != $paciente->cpf || $_POST['data'] != $paciente->data_nascimento) {
		$rtn = array(
			"status" => "error",
			"message" => "CPF ou Data de nascimento errada"
		);
		echo json_encode($rtn);
		die();
	} else {
		$qry = "INSERT INTO " . $_p . "pacientes_assinaturas" . " (id_evolucao, id_tipo_evolucao, id_paciente, data, png_url, latitude, longitude, aprox, user_agent) VALUES (" . $evolucao->id . ", " . $evolucao->id_tipo . ", " . $evolucao->id_paciente . ", now(), '" . $_POST['canvas-url'] . "', " . $_POST['latitude'] . ", " . $_POST['longitude'] . ", " . $_POST['aprox'] . ", '" . addslashes($_POST['user_agent']) . "')";
		$sql->sintax($qry);
		$rtn = array(
			"status" => "success",
			"message" => "Assinatura realizada"
		);
		echo json_encode($rtn);
		die();
	}
}

?>

<!doctype html>
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:og="http://ogp.me/ns#" xmlns:fb="http://www.facebook.com/2008/fbml">

<head>

	<meta charset="utf-8">

	<title>Infodental</title>
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
	<meta name="description" content="Infodental">
	<meta name="author" content="WLIB Soluções Web - www.wlib.com.br">

	<meta property="og:title" content="" />
	<meta property="og:description" content="Infodental" />
	<meta property="og:type" content="website" />
	<meta property="og:url"
	content="http://aws.wlib.com.br/studiodental.dental/html/novo_html/assinatura-eletronica.php" />
	<meta property="og:image" content="http://aws.wlib.com.br/img/facebook.png" />
	<meta property="og:image:width" content="1300" />
	<meta property="og:image:height" content="700" />
	<meta property="og:site_name" content="Infodental" />
	<meta property="fb:admins" content="1066108721" />

	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Source+Sans+Pro:ital,wght@0,400;0,700;1,400;1,700&display=swap"
		rel="stylesheet">
	<link rel="stylesheet" type="text/css" href="css/style.css" />
	<link rel="stylesheet" type="text/css" href="css/apps.css" />

	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
	<script defer type="text/javascript" src="../js/jquery.slick.js"></script>
	<script defer type="text/javascript" src="../js/jquery.datetimepicker.js"></script>
	<script defer type="text/javascript" src="../js/jquery.chosen.js"></script>
	<script defer type="text/javascript" src="../js/jquery.fancybox.js"></script>
	<script defer type="text/javascript" src="../js/jquery.inputmask.js"></script>
	<script defer type="text/javascript" src="../js/jquery.tablesorter.js"></script>
	<script defer type="text/javascript" src="../js/jquery.chart.js"></script>
	<script defer type="text/javascript" src="../js/jquery.chart-utils.js"></script>
	<script type="text/javascript" src="../js/jquery.sweetalert.js"></script>
	<script type="text/javascript" src="../js/jquery.validacao.js"></script>
	<script type="text/javascript" src="../js/jquery.funcoes.js"></script>
	<script defer src="https://code.iconify.design/1/1.0.3/iconify.min.js"></script>

<body>
	<section class="wrapper">
		<section class="sign">
			<header class="sign-header">
				<img src="<?php echo (isset($imagem) ? $imagem : "../img/logo-info.svg"); ?>" alt="" width="484"
					height="68" />
			</header>

			<article class="sign-article">
				<div class="sign-info">
					<h1>Assinatura Eletrônica</h1>
					<p>Data: <strong>
							<?php echo $evolucao->data; ?>
						</strong></p>
					<p>Clínica: <strong>
							<?php echo $clinica->clinica_nome; ?>
						</strong></p>
					<p>Tipo de Documento: <strong>
							<?php echo $tipo->tituloSingular; ?>
						</strong></p>
					<p>Profissional: <strong>
							<?php echo $solicitante->nome; ?>
						</strong></p>
					<p>Paciente: <strong>
							<?php echo $paciente->nome; ?>
						</strong></p>
				</div>
				<div class="sign-doc">
					<h1>Documento</h1>
					<object data="<?php echo $pdf; ?>#view=fit&toolbar=0' " toolbar="0">
						<p><a href="<?php echo $pdf; ?>" class="button"><i class="iconify"
									data-icon="fluent:document-24-regular"></i><span>Baixar documento</span></a></p>
					</object>
				</div>
			</article>

			<footer class="sign-footer">
				<form method="post" class="sign-form">
					<div class="sign-form-status">
						<?php
						$status = array("--laranja", "Aguardando assinatura");
						if ($doc_status == 2) {
							$status[0] = "--verde";
							$status[1] = "Documento assinado";
						}
						?>
						<h1 style="background:var(<?php echo $status[0]; ?>)">Status: 
						<strong>
							<?php echo $status[1]; ?>
						</strong></h1>
					</div>
					<div class="form js-passo1" <?php echo ($doc_status == 2)?"style=\"display:none\"" : ""; ?>>
						<p>Para aceitar este documento, siga os passos a seguir:</p>
						<div class="colunas">
							<dl>
								<dt>CPF</dt>
								<dd><input maxlength="14" type="tel" name="" class="cpf" /></dd>
							</dl>
							<dl>
								<dt>Data de Nascimento</dt>
								<dd><input maxlength="10" type="tel" name="" class="data" /></dd>
							</dl>
						</div>
						<a href="javascript:;" class="button button_lg button_main"
							onclick="$('.js-passo1').hide(); $('.js-passo2').show();">Avançar</a>
					</div>

					<div class="form sign-form-canva js-passo2" style="display:none;">
						<p>Desenhe sua assinatura com o mouse ou o dedo nesta caixa:</p>
						<canvas id="canvas" style="width: 100%;" >
							<p> painel de assinatura </p>
						</canvas>
						<a href="javascript:;" class="button button_lg button_full" id="canvas-clear"><i class="iconify"
								data-icon="fluent:eraser-24-regular"></i><span>Apagar assinatura</span></a>
						<a href="javascript:;" data-loading="<?php echo $doc_status; ?>" class="button button_lg button_main concluir">Concluir</a>
						
					</div>

				</form>
			</footer>

		</section>


		<script>
			const canvas = $('#canvas')[0];
			const ctx = canvas.getContext('2d');
			let pressed = false;
			ctx.lineWidth = 2;
			ctx.lineCap = 'round';

			//calculando a posição do mouse relativo ao bitmap do canvas
			//https://stackoverflow.com/questions/17130395/real-mouse-position-in-canvas/17130415#17130415
			function getmouse(evt) {
				var rect = canvas.getBoundingClientRect();
				var scalex = canvas.width / rect.width;
				var scaley = canvas.height / rect.height;
				return {
					x: (evt.clientX - rect.left) * scalex,
					y: (evt.clientY - rect.top) * scaley
				};
			}
			function draw(e) {
				if (!pressed) { return; }
				ctx.lineWidth = 2;
				ctx.lineCap = 'round';
				ctx.lineTo(getmouse(e).x, getmouse(e).y);
				ctx.stroke();
			}
			//para mobile
			canvas.addEventListener("touchmove", (e) => {
				e.preventDefault();
				console.log(`e.touches[0].clientX: ${e.touches[0].clientX}
					e.touches[0].clientY: ${e.touches[0].clientY}`);
				draw(e.touches[0]);
			});
			canvas.addEventListener("touchstart", (e) => {
				e.preventDefault(); //impedir o envento de scrool 
				ctx.beginPath();
				pressed = true;
			});
			canvas.addEventListener("touchend", (e) => {
				pressed = false;
				ctx.stroke();
			});

			//encontar uma forma de parar de desenhar quando o usuário inicia o desenho mas sai da area do canvas (enquanto o botão ainda está pressionado);
			canvas.addEventListener("mousemove", draw);
			canvas.addEventListener("mousedown", () => {
				ctx.beginPath();
				pressed = true;
			});
			canvas.addEventListener("mouseup", (e) => {
				pressed = false;
				ctx.stroke();
			});
			document.getElementById("canvas-clear").addEventListener("click", () => {
				ctx.clearRect(0, 0, canvas.width, canvas.height);
			});
		</script>
		<script>
			var data_loading = document.getElementsByClassName("concluir")[0]; //recebendo undefined ao usar o jquery para pegar o atributo	
			var btn = $(".concluir"); 

			btn.click(() => {
				if (data_loading.getAttribute('data-loading') == 0) {
					let cpf;
					let data;
					let aux = $('.data')[0].value;
					data_loading.setAttribute('data-loading', 1);

					aux = aux.split('/');
					if (aux.length != 3) {
						swal({ title: "Atenção!", 
									   text: "campo data está vazio ou incompleto", 
									   type: "warning", 
									   confirmButtonColor: "#424242" });
						return;
					}

					cpf = $('.cpf')[0].value.replaceAll('.', '').replace('-', '');
					data = aux[2] + '-' + aux[1] + '-' + aux[0];

					if (cpf == '') {
						swal({ title: "Atenção!", 
									   text: "campo cpf vazio", 
									   type: "warning", 
									   confirmButtonColor: "#424242" });
						return;
					}

					swal({ title: "Atenção!", 
									   text: "Aguarde enquanto processamos a assinatura", 
									   type: "warning", 
									   confirmButtonColor: "#424242" });
					navigator.geolocation.getCurrentPosition(
						(pos) => {
							$.ajax({
								type: "POST",
								data: {
									'conf': true,
									'cpf_ent': cpf,
									'data': data,
									'canvas-url': canvas.toDataURL('image/png'),
									'latitude': pos.coords.latitude,
									'longitude': pos.coords.longitude,
									'aprox': pos.coords.accuracy,
									'user_agent': navigator.userAgent
								},
								async: true,
								dataType: 'JSON',
								success: function (rtn) {

									console.log(rtn);
									if (rtn.status == "success") {
										swal({ title: "Sucesso!", text: rtn.message, type: "success", confirmButtonColor: "#424242" });
										btn.attr('data-loading', 2);
										location.reload();

									} else {
										swal({ title: "Erro!", text: rtn.message, type: "error", confirmButtonColor: "#424242" });
									}
								},
							});
						},
						(err) => {
							console.log(`ERROR(${err.code}): ${err.message}`);
							if (err.code == 1) {
								swal({ title: "Erro!", 
									   text: "Você precisa concordar com a coleta da localização", 
									   type: "error", 
									   confirmButtonColor: "#424242" });
							} else {
								swal({ title: "Erro!", 
									   text: "Algum erro desconhecido foi encontrado", 
									   type: "error", 
									   confirmButtonColor: "#424242" });
							}
						},
						{
							enableHighAccuracy: true,
							timeout: Infinity,
							maximumAge: 0
						}
					);
				} else if (data_loading.getAttribute('data-loading') == 2) {
					swal({ title: "Atenção!", 
									   text: "Esse documento já foi assinado", 
									   type: "warning", 
									   confirmButtonColor: "#424242" });
				} else {
					swal({ title: "Atenção!", 
									   text: "Assinatura está sendo processada", 
									   type: "warning", 
									   confirmButtonColor: "#424242" });
				}
			})
		</script>
	</section>
</body>
</html>