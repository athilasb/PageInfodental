<?php
include "print-header.php";
?>
			
<header class="titulo1">
	<h1>Receituário de Controle Especial</h1>
	<h2>1ª via</h2>
</header>

<header class="titulo2">
	<h1>Identificação do Emitente</h1>
</header>

<div class="box">
	<table>
		<tr>
			<td colspan="2">
				<h1>NOME DO MÉDICO</h1>
				<p>KRONER MACHADO COSTA</p>
			</td>
			<td>
				<h1>CRM</h1>
				<p>284984874</p>
			</td>
			<td>
				<h1>UF</h1>
				<p>GO</p>
			</td>
		</tr>
		<tr>
			<td colspan="3">
				<h1>LOCAL DE ATENDIMENTO</h1>
				<p>Rua 5, 691, The Prime Tamandaré Office, Térreo Loja 1, Setor Bueno</p>
			</td>
			<td>
				<h1>CNES</h1>
				<p>4787248</p>
			</td>
		</tr>
		<tr>		
			<td>
				<h1>CIDADE</h1>
				<p>GOIÂNIA</p>
			</td>
			<td>
				<h1>UF</h1>
				<p>GO</p>
			</td>
			<td>
				<h1>TELEFONE</h1>
				<p>(62) 3515-1717</p>
			</td>
			<td>
				<h1>DATA DE EMISSÃO</h1>
				<p>08/10/2021</p>
			</td>
		</tr>
	</table>
</div>

<div class="box box_empty">
	<table>
		<tr>
			<td>
				<h1>NOME</h1>
				<p>Pedro Henrique Saddi de Azevedo</p>
			</td>			
			<td>
				<h1>ENDEREÇO COMPLETO</h1>
				<p>Rua T-29, 875, Apto 1208, Setor Bueno. Goiânia-GO</p>
			</td>			
		</tr>
	</table>
</div>

<header class="titulo2">
	<h1>Prescrição</h1>
</header>
<div class="box box_empty">
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

<div style="display:grid; grid-template-columns:1fr 1fr; grid-gap:1.5rem;">
	<div style="flex:1; border:1px solid silver; border-radius:12px; padding:.5rem;">

		<header class="titulo2">
			<h1 style="font-size:1em;">Identificação do Comprador</h1>
		</header>
		<table class="no-padding">
			<tr>
				<td><h1>Nome completo</h1></td>
			</tr>
			<tr>
				<td><h1>RG</h1></td>
				<td><h1>Órgão Emissor</h1></td>
			</tr>
			<tr>
				<td style="vertical-align:top; height:60px;"><h1>Endereço Completo</h1></td>
			</tr>
			<tr>
				<td><h1>Cidade</h1></td>
				<td><h1>UF</h1></td>
			</tr>
			<tr>
				<td><h1>Telefone</h1></td>
			</tr>
		</table>

	</div>
	<div style="flex:1; border:1px solid silver; border-radius:12px; padding:.5rem;">

		<header class="titulo2">
			<h1 style="font-size:1em;">Identificação do Fornecedor</h1>
		</header>
		<table class="no-padding">
			<tr>
				<td colspan="2"><h1>Nome Farmacêutico(a)</h1></td>
			</tr>
			<tr>
				<td><h1>CPF</h1></td>
				<td><h1>UF</h1></td>
			</tr>
			<tr>
				<td><h1>Nome Farmácia</h1></td>
			</tr>
			<tr>
				<td><h1>Endereço</h1></td>
			</tr>
			<tr>
				<td><h1>Cidade</h1></td>
				<td><h1>UF</h1></td>
			</tr>
			<tr>
				<td><h1>CNPJ</h1></td>
				<td><h1>Telefone</h1></td>
			</tr>
			<tr>
				<td colspan="2" style="vertical-align:bottom; height:50px; text-align:center;"><h1>ASSINATURA FARMACÊUTICO(A)</h1></td>
			</tr>
		</table>

	</div>
</div>



<?php
include "print-footer.php";
?>