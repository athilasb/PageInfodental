<?php
	require_once("../lib/classes.php");
	$sql = new Mysql();
	$_p="ident_";
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Infodental - Sistema de Migração</title>
</head>
<body>


	<?php
	$sistemas=array('dentaloffice'=>'Dental Office',
					'clinicorp'=>'Clinicorp',
					'avulsa'=>'Avulsa');
	
	$sistema='';
	?>

	<p>Selecione o sistema que deseja realizar migração</p>

	<ul>
		<?php
		foreach($sistemas as $k=>$v) {
		?>
		<li><a href="migracao_<?php echo $k;?>.php"><?php echo $v;?></a></li>
		<?php
		}
		?>
	</ul>
		

</body>
</html>