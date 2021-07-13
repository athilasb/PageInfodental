<?php
include "print-header.php";
?>
			
<header class="titulo1">
	<h1>Plano de Tratamento</h1>
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

<div style="font-size:14pt; margin:2.5rem 0; text-align:center;">Status do Plano de Tratamento: <strong><i class="iconify" data-icon="ph-hourglass-high-fill"></i> Em aberto</strong></div>

<header class="titulo2">
	<h1>Listagem de Procedimentos</h1>
</header>
<div class="box">
	<table>
		<tr>
			<td colspan="3">
				<h1>Procedimento</h1>
				<p><strong>Sorriso Gengival</strong> - Mandíbula - Particular SD.</p>				
				<p>Obs: Fazer a cirurgia com guia cirúrgico</p>
			</td>
			<td>
				<h1>Dentista</h1>
				<p>Dr. Kroner Costa</p>
			</td>
		</tr>
		<tr>
			<td>
				<h1>Status</h1>
				<p>Aprovado</p>
			</td>
			<td>
				<h1>Valor</h1>
				<p>R$ 100,00</p>
			</td>
			<td>
				<h1>Desconto</h1>
				<p>R$ 10,00</p>
			</td>
			<td>
				<h1>Valor Corrigido</h1>
				<p>R$ 90,00</p>
			</td>
		</tr>		
	</table>
</div>

<header class="titulo2">
	<h1>Cronograma de Pagamento</h1>
</header>
<div class="box">
	<table>		
		<tr style="font-size:13pt">
			<td>
				<h1>Valor Total</h1>
				<p>R$ 90,00</p>
			</td>
			<td>
				<h1>Desconto Total</h1>
				<p>R$ 10,00</p>
			</td>
			<td></td>
		</tr>		
		<tr>
			<td>
				<h1>Data Vencimento</h1>
				<p>01/01/2020</p>
			</td>
			<td>
				<h1>Valor Parcela 01</h1>
				<p>R$ 45,00</p>
			</td>
			<td>
				<h1>Forma de Pagamento</h1>
				<p>A definir</p>
			</td>
		</tr>
		<tr>
			<td>
				<h1>Data Vencimento</h1>
				<p>01/02/2020</p>
			</td>
			<td>
				<h1>Valor Parcela 02</h1>
				<p>R$ 45,00</p>
			</td>
			<td>
				<h1>Forma de Pagamento</h1>
				<p>A definir</p>
			</td>
		</td>
	</table>
</div>


<?php
include "print-footer.php";
?>