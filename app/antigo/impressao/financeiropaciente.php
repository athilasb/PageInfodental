<?php
include "print-header.php";
?>
			
<header class="titulo1">
	<h1>Financeiro Paciente</h1>
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
	<h1>Resumo do Financeiro</h1>	
</header>
<div class="box">
	<table>
		<tr>
			<td>
				<h1>Valor Total</h1>
				<p>R$ 1.000,00</p>
			</td>
			<td>
				<h1>Valor Recebido</h1>
				<p>R$ 500,00</p>
			</td>
			<td>
				<h1>Valor a Receber</h1>
				<p> R$ 250,00</p>
			</td>
			<td>
				<h1>Valor Vencido</h1>
				<p> R$ 250,00</p>
			</td>
		</tr>
	</table>
</div>

<header class="titulo2">
	<h1>Pagamentos</h1>	
</header>

<div class="box">
	<table>
		<tr>
			<td>
				<h1>Tratamento</h1>
				<p>Plano de Tratamento 7</p>
			</td>
			<td>
				<h1>Vencimento</h1>
				<p>01/01/2020</p>
			</td>
			<td>
				<h1>Status</h1>
				<p><i class="iconify" data-icon="ph-check"></i>Adimplente</p>
			</td>
			<td>
				<h1>Valor Pago</h1>
				<p>R$ 100,00</p>
			</td>
			<td>
				<h1>Saldo a Pagar</h1>
				<p>R$ 0,00</p>
			</td>
		</tr>
		<tr>
			<td>
				<h1>Valor da Parcela</h1>
				<p>R$ 100,00</p>
			</td>
			<td>
				<h1>Desconto (-)</h1>
				<p>R$ 0,00</p>
			</td>
			<td>
				<h1>Despesas (+)</h1>
				<p>R$ 0,00</p>
			</td>
			<td>
				<h1>Valor Corrigido</h1>
				<p>R$ 100,00</p>
			</td>

		</tr>
	</table>
</div>



<?php
include "print-footer.php";
?>