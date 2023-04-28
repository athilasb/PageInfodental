<?php

	require_once("lib/conf.php");
	require_once("lib/classes.php");


	$sql = new Mysql();

	$sql->consult("infodentalADM.infod_contas","*","where ativo=1 order by data desc");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$instancias[]=$x;
	}

	$_agendamentos=$_ultimoAcesso=$_ultimoAgendamento=[];

	$ativas=$bloqueadas=$inadimplentes=0;

	$query=['agenda'=>'',
			'whatsapp'=>'',
			'pacientes'=>'',
			'evolucoes'=>'',
			'consultorios'=>'',
			'profissionais'=>'',
			'usuarios'=>'',
			'whatsapp'=>'',
			'whatsapp_dia'=>'',
			'ultimoAcesso'=>'',
			'ultimoWhatsapp'=>'',
			'ultimoAgendamento'=>''];

	foreach($instancias as $i) {

		if($i->status=="ativa") $ativas++;
		else if($i->status=="inadimplente") $inadimplentes++;
		else if($i->status=="bloqueada") $bloqueadas++;

		$query['agenda'] .= "(SELECT count(id) FROM ".$i->instancia.".ident_agenda where lixo=0) as total_".$i->instancia.",";
		$query['whatsapp'] .= "(SELECT count(id) FROM ".$i->instancia.".ident_whatsapp_mensagens where lixo=0 and enviado=1) as total_".$i->instancia.",";
		$query['whatsapp_dia'] .= "(SELECT count(id) FROM ".$i->instancia.".ident_whatsapp_mensagens where data>='".date('Y-m-d')." 00:00:00:' and data<='".date('Y-m-d')." 23:59:59' and lixo=0 and enviado=1) as total_".$i->instancia.",";
		$query['pacientes'] .= "(SELECT count(id) FROM ".$i->instancia.".ident_pacientes where lixo=0) as total_".$i->instancia.",";
		$query['evolucoes'] .= "(SELECT count(id) FROM ".$i->instancia.".ident_pacientes_evolucoes where lixo=0) as total_".$i->instancia.",";
		$query['consultorios'] .= "(SELECT count(id) FROM ".$i->instancia.".ident_parametros_cadeiras where lixo=0) as total_".$i->instancia.",";
		$query['profissionais'] .= "(SELECT count(id) FROM ".$i->instancia.".ident_colaboradores where lixo=0 and cro<>'') as total_".$i->instancia.",";
		$query['usuarios'] .= "(SELECT count(id) FROM ".$i->instancia.".ident_colaboradores where lixo=0) as total_".$i->instancia.",";
		$query['ultimoAcesso'] .= "(SELECT data FROM ".$i->instancia.".ident_log_sessoes order by data desc limit 1) as total_".$i->instancia.",";
		$query['ultimoWhatsapp'] .= "(SELECT data FROM ".$i->instancia.".ident_whatsapp_mensagens where enviado=1 order by data desc limit 1) as total_".$i->instancia.",";
		$query['ultimoAgendamento'] .= "(SELECT agenda_data FROM ".$i->instancia.".ident_agenda order by agenda_data desc limit 1) as total_".$i->instancia.",";

	}

	foreach($query as $k=>$v) {
		$query[$k]="SELECT ".substr($v,0,strlen($v)-1)." FROM DUAL";
		$sql->sintax($query[$k]);
		$qry[$k]=mysqli_fetch_object($sql->mysqry);
	}

?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Infodental - Contas</title>
</head>
<body>

</body>

<style type="text/css">
	body {
		background-color:#000;
		font-family: verdana;
		font-size:12px;
		color:#CCC;
	}

	table {
		width:100%;
	}

	table td {
		border: solid 1px #333;
		padding:5px;
	}

	a {
		color:#ccc;
	}
</style>
<script type="text/javascript" src="js/jquery.js"></script>
<script type="text/javascript" src="js/jquery.tablesorter.js"></script>
<script type="text/javascript">
	$(function(){
		$('.tablesorter').tablesorter();
	})
</script>

<div style="margin-bottom:20px;">
	<b>Instancias:</b> <?php echo count($instancias);?><br />
	<b>Ativas:</b> <?php echo $ativas;?><br />
	<b>Inadimplentes:</b> <?php echo $inadimplentes;?><br />
	<b>Bloqueadas:</b> <?php echo $bloqueadas;?><br />
</div>
<table class="tablesorter">
	<thead>
		<tr>
			<th>Instância</th>
			<th>Assinat.</th>
			<th>Status</th>
			<th>Criação</th>
			<th>Pacientes</th>
			<th>Agend.</th>
			<th>Evol.</th>
			<th>Wts.</th>
			<th>Wts. Dia</th>
			<th>Consult.</th>
			<th>Prof.</th>
			<th>Usr.</th>
			<th>Últ. Wts</th>
			<th>Últ. Agend.</th>
			<th>Últ. Acesso</th>
		</tr>
	</thead>
	<tbody>
	<?php
	foreach($instancias as $i) {
		$instancia=$i->instancia;

		$agendamentos=$ultimoAcesso=$pacientes=$evolucoes="";

		foreach($query as $k=>$v) {
			${"total_".$k}=isset($qry[$k]->{"total_".$instancia})?$qry[$k]->{"total_".$instancia}:0;
		}

	?>
	<tr>
		<td><a href="https://<?php echo $instancia;?>.infodental.dental" target="_blank"><?php echo $instancia;?></a></td>
		<td><a href="https://alia.iugu.com/receive/billing/<?php echo $i->iugu_subscription_id;?>" target="_blank"><?php echo $i->iugu_subscription_id;?></a></td>
		<td><?php echo $i->status;?></td>
		<td><?php echo $i->data;?></td>
		<td><?php echo $total_pacientes;?></td>
		<td><?php echo $total_agenda;?></td>
		<td><?php echo $total_evolucoes;?></td>
		<td><?php echo $total_whatsapp;?></td>
		<td><?php echo $total_whatsapp_dia;?></td>
		<td><?php echo $total_consultorios;?></td>
		<td><?php echo $total_profissionais;?></td>
		<td><?php echo $total_usuarios;?></td>
		<td><?php echo $total_ultimoWhatsapp;?></td>
		<td><?php echo $total_ultimoAgendamento;?></td>
		<td><?php echo $total_ultimoAcesso;?></td>
	</tr>
	<?php
	}
	?>
	</tbody>
</table>
</html>