$apiConfig = array(
	'Pagamentos' => 1,
	'Avulso' => 1,
);






<!-- ASIDE PAGAMENTO AVULSO  -->
<?php if (isset($apiConfig['Avulso'])){?>
<script> 
	$("#pagamento_avulso").click(() => {
		//alert("teste");
		$(".default").show();
	});
</script>

	<!-- ASIDE PROGRAMAÇÂO DE PAGAMENTO-->
	<section class="aside aside-form default" id="js-aside-asFinanceiro">
		<div class="aside__inner1">
			<input type="hidden" name="alteracao" value="0">
			<header class="aside-header">
				<h1 class="js-titulo"></h1>
				<a href="javascript:;" class="aside-header__fechar aside-close"><i class="iconify" data-icon="fluent:dismiss-24-filled"></i></a>
			</header>
		</div>
	</section>






<?php
}
?>
