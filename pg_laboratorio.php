<?php
include "includes/header.php";	
?>

<section class="wrapper">
	<header class="header">
		<section class="header-logo">
			<img src="img/logo.svg" width="32" height="30" alt="Info Dental" class="header-logo__img" />
		</section>
		<section class="header-cliente">
			<img src="img/logo-cliente.png" alt="" width="270" height="98" class="header-cliente__img" />
		</section>
		<section class="header-lab">
			<h1>Laboratório Imagem</h1>
		</section>
	</header>

	<main class="main" style="margin:0;">
		<section class="content">

			<div class="grid grid_3">
				
				<div class="box" style="grid-column:span 2">
					<form class="form">
						<div class="grid grid_2">
							<fieldset>
								<legend>Clínica</legend>
								
									<dl>
										<dt>Nome</dt>
										<dd><strong>Studio Dental</strong></dd>
									</dl>
									<dl>
										<dt>CPF/CNPJ</dt>
										<dd><strong>10.814.480/0001-88</strong></dd>
									</dl>
									<dl>
										<dt>Valor da OS</dt>
										<dd><strong>R$ 100,00</strong></dd>
									</dl>
								
							</fieldset>
							<fieldset>
								<legend>Paciente</legend>
								
									<dl>
										<dt>Nome completo</dt>
										<dd><strong>Pedro Henrique Saddi de Azevedo</strong></dd>
									</dl>
									<dl>
										<dt>CPF</dt>
										<dd><strong>011.194.171-71</strong></dd>
									</dl>
									<div class="colunas2">
										<dl>
											<dt>Sexo</dt>
											<dd><strong>Masculino</strong></dd>
										</dl>
										<dl>
											<dt>Idade</dt>
											<dd><strong>36 anos</strong></dd>
										</dl>
									</div>								
							</fieldset>
						</div>
						<fieldset>
							<legend>Descrição Geral</legend>
							<dd><strong>Lorem ipsum dolor sit, amet consectetur adipisicing elit. Doloribus facere sint recusandae perferendis, dolorum consectetur aut officia, sit officiis hic!</strong></dd>
						</fieldset>
						<fieldset>
							<legend>Itens da OS</legend>							
							<div class="reg">
								<div class="reg-group">
									<div class="reg-color" style="background-color:palegreen"></div>
									<div class="reg-data" style="flex:0 1 300px">
										<h1>PORCELANA INJETADA</h1>
										<p>21, 22, 24</p>
									</div>
									<div class="reg-data">
										<p>R$ 1.022,00</p>
									</div>										
								</div>

								<div class="reg-group">
									<div class="reg-color" style="background-color:palegreen"></div>
									<div class="reg-data" style="flex:0 1 300px">
										<h1>SCAN SERVICE</h1>
										<p>Enviar com urgência</p>
									</div>
									<div class="reg-data">
										<p>R$ 600,00</p>
									</div>										
								</div>
							</div>
						</fieldset>
						<div class="grid grid_2">
							<fieldset>
								<legend>Arquivos para Download</legend>		
								<div class="reg">
									<a href="" class="reg-group">
										<div class="reg-color" style="background-color:palegreen"></div>
										<div class="reg-data" style="flex:0 1 300px">
											<h1>fotos.zip</h1>
										</div>
										<div class="reg-data">
											<p>Fotos / Sorriso</p>
										</div>
										<div class="reg-icon">
											<i class="iconify" data-icon="bx-bx-download"></i>
										</div>
									</a>
									<a href="" class="reg-group">
										<div class="reg-color" style="background-color:palegreen"></div>
										<div class="reg-data" style="flex:0 1 300px">
											<h1>modelo_antagonista.crc</h1>
										</div>
										<div class="reg-data">
											<p>Modelos / Modelo Antagonista</p>
										</div>
										<div class="reg-icon">
											<i class="iconify" data-icon="bx-bx-download"></i>
										</div>
									</a>
								</div>					
							</fieldset>
							<fieldset>
								<legend>Checklist</legend>							
								<div class="reg">
									<div class="reg-group">
										<div class="reg-color" style="background-color:red"></div>
										<div class="reg-data" style="flex:0 1 300px">
											<h1>Fotos / Sorriso</h1>
											<p>Digital</p>
										</div>										
									</div>
									<div class="reg-group">
										<div class="reg-color" style="background-color:red"></div>
										<div class="reg-data" style="flex:0 1 300px">
											<h1>Modelos / Modelo Antagonista</h1>
											<p>Digital</p>
										</div>										
									</div>
									<div class="reg-group">
										<div class="reg-color" style="background-color:blue"></div>
										<div class="reg-data" style="flex:0 1 300px">
											<h1>Componentes / Análogos</h1>
											<p>Analógico</p>
										</div>										
									</div>
									<div class="reg-group">
										<div class="reg-color" style="background-color:blue"></div>
										<div class="reg-data" style="flex:0 1 300px">
											<h1>Componentes / Parafusos</h1>
											<p>Analógico</p>
										</div>										
									</div>
								</div>

							</fieldset>
						</div>
					</form>
				</div>

				<div>
					<div class="hist box">

						<form method="post" class="form formulario-validacao">
							<div style="margin-bottom:1rem;">
								<legend>Situação da OS</legend>
								<dl>
									<dd>
										<div class="filter-links">										
											<a href="javascript:;" data-status="Em aberto" class="js-btn-status active">Em aberto</a>
											<a href="javascript:;" data-status="Aceito" class="js-btn-status">Aceito</a>
											<a href="javascript:;" data-status="Recusado" class="js-btn-status">Recusado</a>
										</div>
									</dd>
								</dl>
							</div>
							<legend>Histórico</legend>
							<div class="hist-lista">
								<div class="hist-lista-item hist-lista-item_lab">
									<h1>Laboratório em 12/07/2021</h1>
									<p>Estamos demorando um pouco mais que o habitual. Desculpe a demora e aguarde um pouco mais</p>
								</div>
								<div class="hist-lista-item hist-lista-item_lab">
									<h1>Laboratório em 11/07/2021</h1>
									<h2>status alterado para <strong style="background:limegreen;">Aceito</strong></h2>
								</div>
								<div class="hist-lista-item">
									<h1>Kroner Costa em 11/07/2021</h1>
									<p>Documento enviado!</p>
									<h2>status alterado para <strong style="background:blue">Em aberto</strong></h2>
								</div>
								<div class="hist-lista-item hist-lista-item_lab">
									<h1>Laboratório em 10/07/2021</h1>
									<p>Falta documento sobre as cores da faceta</p>
									<h2>status alterado para <strong style="background:red;">OS Recusada</strong></h2>
								</div>
								<div class="hist-lista-item">
									<h1>Kroner Costa em 10/07/2021</h1>
									<h2><strong style="background:#000;">OS Criada</strong></h2>
								</div>
							</div>
							
							<dl><dd><textarea name="mensagem" placeholder="escreva uma mensagem (opcional)" rows="3"></textarea></dd></dl>
							<button type="submit" class="button" style="white-space:nowrap;"><i class="iconify" data-icon="ph-paper-plane-right-fill"></i> Enviar</button>
						</form>
					</div>
					
				</div>

			</div>
		</section>
	</main>

</section>



		
<?php
include "includes/footer.php";
?>