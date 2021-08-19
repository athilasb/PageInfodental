<?php
include "print-header.php";
?>
			
<header class="titulo1">
	<h1>Pedido de Exame Complementar</h1>
	<p>01/01/2021</p>
</header>

<div class="ficha">
	<img src="../img/ilustra-perfil.png" alt="" width="80" height="80" class="ficha__foto" />
	<table>
		<tr>
			<td colspan="2"><strong>Kroner Machado Costa</strong></td>
			<td>Masculino</td>
		</tr>
		<tr>
			<td>30 anos</td>
			<td>#4333</td>
			<td>Paciente há 2 anos</td>
		</tr>
	</table>
</div>

<header class="titulo2">
	<h1>Clínica Radiológica</h1>	
</header>
<div class="box">
	<table>
		<tr>
			<td>
				<h1>Nome</h1>
				<p>Imagem Dental</p>
			</td>
			<td>
				<h1>Telefone</h1>
				<p>(62) 3444-3322</p>
			</td>
			<td>
				<h1>Solicitado por</h1>
				<p>Dr. Kroner Costa</p>
			</td>
		</tr>
		<tr>
			<td colspan="3">
				<h1>Endereço</h1>
				<p>Rua T-29, 875, Setor Bueno. Goiânia-GO. CEP: 74210-050</p>
			</td>
		</tr>
		<tr>
			<td>
				<h1>Como chegar</h1>
				<p>
					<a href=""><i class="iconify" data-icon="fa-brands:waze"></i> Waze</a> &nbsp; <a href=""><i class="iconify" data-icon="simple-icons:googlemaps"></i> Google Maps</a>
				</p>
			</td>
		</tr>
	</table>
</div>

<header class="titulo2">
	<h1>Listagem de Exames</h1>	
</header>
<div class="box">
	<table>
		<tr>
			<td>
				<p><strong>01) Tomografia - Mandíbula</strong></p>
				<p>Obs: Fazer a cirurgia com guia cirúrgico</p>
			</td>
		</tr>
		<tr>
			<td>
				<p><strong>02) Tomografia - Dente</strong></p>				
			</td>
		</tr>
	</table>
</div>


<?php
include "print-footer.php";
?>