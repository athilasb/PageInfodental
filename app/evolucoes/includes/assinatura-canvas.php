	<section class="wrapper">
		<section class="sign">
			<footer class="sign-footer">
				<form method="post" class="sign-form">

				<?php

				// se foi assinado
				if(is_object($assinatura)) {
					?>
					<?php
				}	

				// se nao foi assinado
				else {

					?>
					<div style="display:<?php echo $evolucaoProntoParaAssinatura==true?"block":"none";?>">

						<div class="sign-form-status">
							<h1 style="background: var(--cinza4); padding: 5px; color: var(--branco); text-align: center;">
								<strong>Aguardando Assinatura</strong>
							</h1>
						</div>

						<div class="form sign-form-canva js-passo2">
							<p class="text-assinatura">Faça a assinatura eletrônica na caixa abaixo:</p>

							<canvas id="canvas" style="width: 100%; border: 1px solid #E7E7E7; box-shadow: 0px 0px 15px rgba(0, 0, 0, 0.1);margin-bottom:35px;" >
								<p> painel de assinatura </p>
							</canvas>

							<?php /*<div class="colunas" style="margin-top:35px; margin-bottom:35px;">
								
								<dl>
									<dd class="form-comp">
										<span><i class="iconify" data-icon="mdi:file-document-outline" style="color:var(--cor-base);"></i></span>
										<input placeholder="CPF" maxlength="14" type="text" class="js-sign-cpf cpf js-cpf" />
									</dd>
								</dl>
								<dl >
									<dd class="form-comp">
										<span><i class="iconify" data-icon="material-symbols:calendar-month" style="color:var(--cor-base);"></i></span>
										<input placeholder="Data Nascimento" maxlength="10" type="text" class="js-sign-dn js-dn dn"/>
									</dd>
								</dl>
							</div>*/?>

							<center>
								<a href="javascript:;" data-loading="0" class="button js-sign-concluir bottom-assinar"><span class="iconify" data-icon="mdi:file-sign"></span> Assinar</a>

								<a href="javascript:;" class="button button_lg button_full bottom-apagar" id="canvas-clear"><i class="iconify" data-icon="fluent:eraser-24-regular"></i><span> Apagar</span></a>
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
							swal({title: "Sucesso!", text: 'Assinatura realizada com sucesso!', html:true, type:"success", confirmButtonColor: "#424242"},function(){
								document.location.reload();
							});
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

					//let cpf = $('.js-sign-cpf').val();
					//let dn = $('.js-sign-dn').val();

					let erro = '';
					if(assinado<=10) erro='Faça uma assinatura para continuar';
					//else if(cpf.length==0) erro='Digite o CPF';
					//else if(dn.length==0)  erro='Digite a Data de Nascimento';

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

		</script>
	</section>