<section class="wrapper">
		<section class="sign">
			<footer class="sign-footer">
				<form method="post" class="sign-form">
					<div class="sign-form-status">
						<?php
                        global $dock_status;

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
                        
						<a href="javascript:;" class="button button_lg button_main"
							onclick="$('.js-passo1').show(); $('.js-passo2').hide();">Voltar</a>
						
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
					/*	swal({ title: "Atenção!", 
									   text: "campo data está vazio ou incompleto", 
									   type: "warning", 
									   confirmButtonColor: "#424242" });*/
                                       alert("campo data vazio");
						return;
					}

					cpf = $('.cpf')[0].value.replaceAll('.', '').replace('-', '');
					data = aux[2] + '-' + aux[1] + '-' + aux[0];

					if (cpf == '') {
					//	swal({ title: "Atenção!", 
					//				   text: "campo cpf vazio", 
					//				   type: "warning", 
					//				   confirmButtonColor: "#424242" });
                    alert("campo cpf vazio");

						return;
					}

			        //swal({ title: "Atenção!", 
			        //				   text: "Aguarde enquanto processamos a assinatura", 
			        //				   type: "warning", 
			        //				   confirmButtonColor: "#424242" });
                    alert("aguarde");

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
										//swal({ title: "Sucesso!", text: rtn.message, type: "success", confirmButtonColor: "#424242" });
										btn.attr('data-loading', 2);
										location.reload();
                                        alert("assinatura concluida");


									} else {
										//swal({ title: "Erro!", text: rtn.message, type: "error", confirmButtonColor: "#424242" });
                                        alert("assinatura não");

									}
								},
							});
						},
						(err) => {
							console.log(`ERROR(${err.code}): ${err.message}`);
							if (err.code == 1) {
								//swal({ title: "Erro!", 
								//	   text: "Você precisa concordar com a coleta da localização", 
								//	   type: "error", 
								//	   confirmButtonColor: "#424242" });
                                alert("concordar com a coleta de dados");
							} else {
								//swal({ title: "Erro!", 
								//	   text: "Algum erro desconhecido foi encontrado", 
								//	   type: "error", 
								//	   confirmButtonColor: "#424242" });
                                alert("erro desconhecido");
							}
						},
						{
							enableHighAccuracy: true,
							timeout: Infinity,
							maximumAge: 0
						}
					);
				} else if (data_loading.getAttribute('data-loading') == 2) {
					//swal({ title: "Atenção!", 
					//				   text: "Esse documento já foi assinado", 
					//				   type: "warning", 
					//				   confirmButtonColor: "#424242" });
                    alert("você já clickou no botão");
				} else {
					//swal({ title: "Atenção!", 
					//				   text: "Assinatura está sendo processada", 
					//				   type: "warning", 
					//				   confirmButtonColor: "#424242" });
                    alert("assinatura sendo processada");
				}
			})
		</script>
	</section>