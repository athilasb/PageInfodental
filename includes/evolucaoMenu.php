
	<?php
	if(isset($_GET['form']) or isset($exibirEvolucaoNav)) {
	?>
<div class="filter">
	<div class="filter-group">
		<div class="filter-button">
			<a href="pg_contatos_pacientes_evolucao.php?id_paciente=<?php echo $paciente->id;?>"><i class="iconify" data-icon="bx-bx-left-arrow-alt"></i></a>
		</div>
	</div>
	<div class="filter-group">
		<div class="filter-title">
			Escolha o tipo de evolução
		</div>
	</div>
	<div class="filter-group filter-group_right">
		<div class="filter-button">
			<a href="javascript:;"><i class="iconify" data-icon="bx-bx-trash"></i></a>
			<a href="javascript:;"><i class="iconify" data-icon="bx-bx-printer"></i></a>
			<a href="javascript:;" class="azul js-btn-salvar"><i class="iconify" data-icon="bx-bx-check"></i><span>salvar</span></a>
		</div>
	</div>
</div>
	<?php
	}		
	?>

<?php
	
	$_tiposEvolucao=array();
	$sql->consult($_p."pacientes_evolucoes_tipos","*","");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_tiposEvolucao[$x->id]=$x;
	}

	$evolucaoMenu = array('anamnese'=>array('titulo'=>'Anamnese','icone'=>'mdi-clipboard-check-multiple-outline','url'=>'pg_contatos_pacientes_evolucao_anamnese.php'),
							'procedimentos-aprovados'=>array('titulo'=>'Procedimentos Aprovados','icone'=>'mdi-check-circle-outline','url'=>'pg_contatos_pacientes_evolucao_procedimentos.php'),
							'procedimentos-avulsos'=>array('titulo'=>'Procedimentos Avulsos','icone'=>'mdi-progress-check','url'=>'pg_contatos_pacientes_evolucao_procedimentosavulsos.php'),
							'atestado'=>array('titulo'=>'Atestado','icone'=>'mdi-file-document-outline','url'=>'pg_contatos_pacientes_evolucao_atestado.php'),
							'servicos-de-laboratorio'=>array('titulo'=>'Serviços de Laboratório','icone'=>'entypo-lab-flask','url'=>'pg_contatos_pacientes_evolucao_laboratorio.php'),
							'pedidos-de-exames'=>array('titulo'=>'Pedidos de Exames','icone'=>'carbon-user-x-ray','url'=>'pg_contatos_pacientes_evolucao_pedidosdeexame.php'),
							'receituario'=>array('titulo'=>'Receituário','icone'=>'mdi-pill','url'=>'pg_contatos_pacientes_evolucao_receituario.php'),
							'proxima-consulta'=>array('titulo'=>'Próxima Consulta','icone'=>'mdi-calendar-cursor','url'=>'pg_contatos_pacientes_evolucao_proximaconsulta.php'),
							'baixa'=>array('titulo'=>'Baixa de Estoque','icone'=>'clarity:list-outline-badged','url'=>'pg_contatos_pacientes_evolucao_baixa.php'),);
//<span class="iconify" data-icon="clarity:list-outline-badged" data-inline="false"></span>
?>

<div class="grid-links" style="grid-template-columns:repeat(9,1fr);<?php echo (isset($_GET['form']) or isset($exibirEvolucaoNav))?"margin-bottom:2rem;":"";?>">
	<?php
	foreach($evolucaoMenu as $tipo=>$arr) {
	?>
	<a href="<?php echo $arr['url']."?id_paciente=$paciente->id";?>" class="js-evolucao<?php echo basename($_SERVER['PHP_SELF'])==$arr['url']?" active":"";?>" data-tipo="<?php echo $tipo;?>">
		<i class="iconify" data-icon="<?php echo $arr['icone'];?>"></i>
		<p><?php echo $arr['titulo'];?></p>
	</a>
	<?php	
	}
	?>
</div>
<?php /*
<div class="grid-links" style="grid-template-columns:repeat(8,1fr); margin-bottom:2rem;">
	<a href="javascript:;" class="js-evolucao" data-tipo="anamnese">
		<i class="iconify" data-icon="mdi-clipboard-check-multiple-outline"></i>
		<p>Anamnese</p>
	</a>
	<a href="javascript:;" class="js-evolucao" data-tipo="procedimentos-aprovados">
		<i class="iconify" data-icon="mdi-check-circle-outline"></i>
		<p>Precedimentos Aprovados</p>
	</a>
	<a href="javascript:;" class="js-evolucao" data-tipo="procedimentos-avulsos">
		<i class="iconify" data-icon="mdi-progress-check"></i>
		<p>Procedimentos Avulsos</p>
	</a>
	<a href="javascript:;" class="js-evolucao" data-tipo="atestado">
		<i class="iconify" data-icon="mdi-file-document-outline"></i>
		<p>Atestado</p>
	</a>
	<a href="javascript:;" class="js-evolucao" data-tipo="servicos-de-laboratorio">
		<i class="iconify" data-icon="entypo-lab-flask"></i>
		<p>Serviços de Laboratório</p>
	</a>
	<a href="javascript:;" class="js-evolucao" data-tipo="pedidos-de-exames">
		<i class="iconify" data-icon="carbon-user-x-ray"></i>
		<p>Pedidos de Exames</p>
	</a>
	<a href="javascript:;" class="js-evolucao" data-tipo="receituario">
		<i class="iconify" data-icon="mdi-pill"></i>
		<p>Receituário</p>
	</a>
	<a href="javascript:;" class="js-evolucao" data-tipo="proxima-consulta">
		<i class="iconify" data-icon="mdi-calendar-cursor"></i>
		<p>Próxima Consulta</p>
	</a>
</div>*/?>