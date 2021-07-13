<?php
include "print-header.php";
?>
			
<header class="titulo1">
	<h1>Receituário</h1>
	<p>01/01/2021</p>
</header>

<header class="titulo2">
	<h1>Dados do Paciente</h1>
</header>
<div class="box">
	<table>
		<tr>
			<td>
				<h1>Nome</h1>
				<p>Kroner Machado Costa</p>
			</td>
			<td>
				<h1>Idade</h1>
				<p>30 anos</p>
			</td>
			<td>
				<h1>Sexo</h1>
				<p>Masculino</p>
			</td>
		</tr>
		<tr>
			<td colspan="3">
				<h1>Endereço</h1>
				<p>Rua T-39, 875, Apto 1803, Setor Bueno. Goiânia-GO</p>
			</td>
		</tr>
	</table>
</div>

<header class="titulo2">
	<h1>Prescrição Medicamentosa</h1>
</header>
<div class="box">
	<div class="prescricao">
		<div class="prescricao__item">
			<h1>01) Amoxilina 500mg</h1>
			<span></span>
			<h2>21 comprimidos</h2>
		</div>
		<p class="prescricao__obs">Tomar 1 comprimido via oral a cada 8 horas durante 7 dias.</p>
	</div>
	<div class="prescricao">
		<div class="prescricao__item">
			<h1>02) Azitromicina</h1>
			<span></span>
			<h2>5 comprimidos</h2>
		</div>
		<p class="prescricao__obs">Tomar 1 comprimido via oral a cada 8 horas durante 7 dias.</p>
	</div>
</div>

<?php
include "print-footer.php";
?>