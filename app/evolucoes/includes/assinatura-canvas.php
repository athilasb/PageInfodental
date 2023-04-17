	<section class="wrapper">
		<section class="sign">
			<footer class="sign-footer">
				<form method="post" class="sign-form">

				<?php

				// se foi assinado
				if(is_object($assinatura)) {
					?>
					<div style="color:green;font-size: 15px;text-align: center;">
						<span class="iconify" data-icon="icon-park-solid:success"></span> Documento assinado em <?php echo date('d/m/Y H:i',strtotime($assinatura->data));?>
					</div>
					<?php
				}	

				// se nao foi assinado
				else {

					?>
					<div style="display:<?php echo $evolucaoProntoParaAssinatura==true?"block":"none";?>">

						<div class="sign-form-status">
							<h1 style="background:var(--laranja);padding:5px;text-align: center;">
								<strong>Aguardando Assinatura</strong>
							</h1>
						</div>

						<div class="form sign-form-canva js-passo2">
							<p>Faça a assinatura eletrônica na caixa abaixo:</p>

							<canvas id="canvas" style="width: 100%;border: solid 1px #CCC" >
								<p> painel de assinatura </p>
							</canvas>

							<div class="colunas">
								<dl>
									<dt>CPF</dt>
									<dd><input maxlength="14" type="tel" class="js-sign-cpf cpf" /></dd>
								</dl>
								<dl>
									<dt>Data de Nascimento</dt>
									<dd><input maxlength="10" type="tel" class="js-sign-dn dn" /></dd>
								</dl>
							</div>

							<center>
								<a href="javascript:;" data-loading="0" class="button js-sign-concluir"><span class="iconify" data-icon="mdi:file-sign"></span> Assinar</a>

								<a href="javascript:;" class="button button_lg button_full" id="canvas-clear"><i class="iconify" data-icon="fluent:eraser-24-regular"></i><span> Apagar</span></a>
							</center>
	                        
							
						</div>
					</div>
					<?php
				}
				?>
					
				</form>
			</footer>

		</section>


		<script>
			var id_evolucao = '<?php echo md5($evolucao->id);?>';
			var assinado = 0;
			var pos = {};

			const canvas = $('#canvas')[0];
			const ctx = canvas.getContext('2d');
			var pressed = false;

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
				assinado++;
			}
			
			canvas.addEventListener("touchmove", (e) => {
				e.preventDefault();
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

			const evolucaoAssinar = () => {

				let obj = $('.js-sign-concluir');
				obj.html(`<span class="iconify" data-icon="eos-icons:loading"></span> Assinando...`);

				let cpf = $('.js-sign-cpf').val();
				let dn = $('.js-sign-dn').val();

				let data = {
						'ajaxSign': 'assinatura',
						'id_evolucao': id_evolucao,
						'assinatura': canvas.toDataURL('image/png'),
						'lat': pos.lat,
						'lng': pos.lng,
						'cpf': cpf,
						'dn': dn,
						'dispositivo': navigator.userAgent
					}

				$.ajax({
					type: "POST",
					data: data,
					success:function(rtn) {
						if(rtn.success) {

						} else {

							if(rtn.error) erro=rtn.error;
							else erro='Algum erro ocorreu durante a autenticação. Tente novamente!';

							swal({title: "Erro!", text: erro, html:true, type:"error", confirmButtonColor: "#424242"});

							obj.html(`<span class="iconify" data-icon="mdi:file-sign"></span> Assinar`);
							obj.attr('data-loading',0);
						}
					}
				})

				
			}

			const geolocationSuccess = (position) => {

				let { latitude, longitude, accuracy } = position.coords;

				pos.lat = latitude;
				pos.lng = longitude
				pos.acc = accuracy;

				evolucaoAssinar();
				
			}

			const geolocationFail = () => { 
				pos.lat = '';
				pos.lng = '';
				pos.acc = '';

				evolucaoAssinar();
			}

			$(function() {

				$('.js-sign-cpf').inputmask('999.999.999-99');
				$('.js-sign-dn').inputmask('99/99/9999');

				$('.js-sign-concluir').click(function(){

					let cpf = $('.js-sign-cpf').val();
					let dn = $('.js-sign-dn').val();

					let erro = '';
					if(assinado<=10) erro='Faça uma assinatura para continuar';
					else if(cpf.length==0) erro='Digite o CPF';
					else if(dn.length==0)  erro='Digite a Data de Nascimento';

					if(erro.length>0) {
						swal({ title: "Erro!", text: erro, type: "error", confirmButtonColor: "#424242" });
					} else {

						let obj = $('.js-sign-concluir');

						if(obj.attr('data-loading')==0) {

							obj.attr('data-loading',1);
							obj.html(`<span class="iconify" data-icon="eos-icons:loading"></span> Processando...`);

							if(navigator.geolocation) {
								navigator.geolocation.getCurrentPosition(geolocationSuccess,geolocationFail);
							} else {
								geolocationFail();
							}	
						}
					}
				})
			});

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