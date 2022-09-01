<?php
include "print-header.php";
?>
			
<header class="titulo1">
	<h1>OS Laboratório</h1>
	<p>01/01/2021</p>
</header>

<div style="font-size:14pt; margin:1rem 0; text-align:center;">Status: <strong><i class="iconify" data-icon="ph-check"></i> Aceito</strong></div>

<header class="titulo2">
	<h1>Clínica</h1>
</header>
<div class="box">
	<table>
		<tr>
			<td>
				<h1>Nome</h1>
				<p>Studio Dental</p>
			</td>
			<td>
				<h1>CNPJ</h1>
				<p>10.814.480/0001-88</p>
			</td>
			<td>
				<h1>Valor da OS</h1>
				<p>R$ 100,00</p>
			</td>						
		</tr>	
	</table>
</div>

<header class="titulo2">
	<h1>Paciente</h1>
</header>
<div class="box">
	<table>
		<tr>
			<td>
				<h1>Nome completo</h1>
				<p>Pedro Henrique Saddi de Azevedo</p>
			<td>
				<h1>CPF</h1>
				<p>011.194.171-71</p>
			<td>
				<h1>Sexo</h1>
				<p>Masculino</p>
			<td>
				<h1>Idade</h1>
				<p>36 anos</p>
			</td>
		</tr>	
	</table>
</div>

<header class="titulo2">
	<h1>Descrição Geral</h1>
</header>
<div class="box">
	<table>
		<tr>
			<td>
				<p>Lorem ipsum dolor sit amet consectetur, adipisicing elit. Velit, rem ut repellat nulla autem deleniti ipsum quos accusantium quibusdam exercitationem?</p>
			</td>
		</tr>	
	</table>
</div>

<header class="titulo2">
	<h1>Itens da OS</h1>
</header>
<div class="box">
	<table>
		<thead>
			<tr>
				<th>Procedimento</th>
				<th>Dente(s)</th>
				<th>Descrição</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td>
					<p>PORCELANA INJETADA</p>
				</td>
				<td>				
					<p>21, 22, 24</p>
				</td>
				<td>				
					<p>-</p>
				</td>
			</tr>	
			<tr>
				<td>
					<p>SCAN SERVICE</p>
				</td>
				<td>				
				</td>
				<td>
					<p>Enviar com urgência</p>
				</td>
			</tr>	
		</tbody>
	</table>
</div>

<header class="titulo2">
	<h1>Checklist</h1>
</header>
<div class="box">
	<table>
		<thead>
			<tr>
				<th>Título</th>
				<th>Tipo</th>				
			</tr>
		</thead>
		<tbody>
			<tr>
				<td>Fotos/Sorriso</td>
				<td>Digital</td>
			</tr>
			<tr>
				<td>Modelos/Modelo Antagonista</td>
				<td>Digital</td>
			</tr>
			<tr>
				<td>Componentes / Análogos</td>
				<td>Analógico</td>
			</tr>
		</tbody>
	</table>
</div>

<div style="break-inside: avoid;">
	<header class="titulo2">
		<h1>Histórico</h1>
	</header>
	<div class="box">
		<table>
			<thead>
				<tr>
					<th>Autor</th>
					<th>Data</th>
					<th>Mensagem</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td>Laboratório</td>
					<td>12/07/2021</td>
					<td>Estamos demorando um pouco mais que o habitual. Desculpe a demora e aguarde um pouco mais</td>
				</tr>
				<tr>
					<td>Kroner Costa</td>
					<td>12/07/2021</td>
					<td>Documento enviado!<br /><small>Status alterado para <strong>EM ABERTO</strong></small></td>
				</tr>
				<tr>
					<td>Laboratório</td>
					<td>11/07/2021</td>
					<td>Falta documento sobre as cores da faceta<br /><small>Status alterado para <strong>RECUSADO</strong></small></td>
				</tr>
				<tr>
					<td>Kroner Costa</td>
					<td>11/07/2021</td>
					<td><small><strong>OS CRIADA</strong></small></td>
				</tr>
			</tbody>
		</table>
	</div>
</div>



<?php
include "print-footer.php";
?>