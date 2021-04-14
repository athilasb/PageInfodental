<?php
$dir="../";
require_once("../lib/conf.php");
require_once("../usuarios/checa.php");
$sql->consult($_p."usuarios","*","");
$usuarios=array();
while($x=mysqli_fetch_object($sql->mysqry)) $usuarios[$x->id]=$x->login;
?>
<div class="box-registros" style="width:80%">
	<?php
	if($usr->tipo!="admin") {
		echo "<center>Você não tem permissão para analisar LOGs</center>";
	} else {
	?>
	<table class="tablesorter">
		<caption>Log</caption>
		<tr>
			<th>Data</th>
			<th>Usuário</th>
			<th>Ação</th>
			<th>Query</th>
			<th>Condição</th>
		</tr>
		<?php
		if(isset($_GET['table']) and isset($_GET['id']) and is_numeric($_GET['id'])) {
			$sql = new Mysql();
			$sql->consult($_p."log","*,date_format(data,'%d/%m/%Y %H:%i') as dataf","where tabela='".$str->protege($_GET['table'])."' and id_reg='".$_GET['id']."' order by data desc");
			if($sql->rows==0) {
			?>
		<tr>
			<td colspan="5"><center>Nenhum registro</center></td>
		</tr>
			<?php	
			} else {
				while($x=mysqli_fetch_object($sql->mysqry)) {
		?>
		<tr>
			<td><?php echo $x->dataf;?></td>
			<td><?php echo isset($usuarios[$x->id_usuario])?$usuarios[$x->id_usuario]:"-";?></td>
			<td><?php echo $x->tipo;?></td>
			<td><p style="font-family:'Courier'; font-size:0.875em; width:100%; word-break:break-all;"><?php echo utf8_encode($x->vsql);?></p></td>
			<td><?php echo $x->vwhere;?></td>
		</tr>
		<?php	
				}
			}
		} else {
		?>
		<tr>
			<td colspan="5"><center>Configurações incorretas</center></td>
		</tr>
		<?php	
		} 
		?>
	</table>
	<?php
	}
	?>
</div>