<?php
	$pagesWhatsapp=explode(",","pg_configuracoes_whatsapp.php");
?>

<section class="tab">
	<a href="pg_configuracoes_whatsapp.php"<?php echo in_array(basename($_SERVER['PHP_SELF']),$pagesWhatsapp)?' class="active"':'';?>>Whatsapp</a>			
</section>