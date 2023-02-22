<?php
	$pagesWhatsapp=explode(",","pg_configuracoes_whatsapp.php,pg_configuracoes_whatsapp_pesquisadesatisfacao.php,pg_configuracoes_whatsapp_aniversario.php");
?>

<section class="tab">
	<a href="pg_configuracoes_whatsapp.php"<?php echo in_array(basename($_SERVER['PHP_SELF']),$pagesWhatsapp)?' class="active"':'';?>>Whatsapp</a>			
</section>