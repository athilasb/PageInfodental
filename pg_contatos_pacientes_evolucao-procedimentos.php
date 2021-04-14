<?php
	include "includes/header.php";
	include "includes/nav.php";


	$_table=$_p."pacientes";
	$_page=basename($_SERVER['PHP_SELF']);

	$_width=400;
	$_height=400;
	$_dir="arqs/pacientes/";

	$_cidades=array();
	$sql->consult($_p."cidades","*","");
	while($x=mysqli_fetch_object($sql->mysqry)) $_cidades[$x->id]=$x;

	$_profissoes=array();
	$sql->consult($_p."parametros_profissoes","*","where lixo=0 order by titulo asc");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_profissoes[$x->id]=$x;
	}


	$_pacienteIndicacoes=array();
	$sql->consult($_p."parametros_indicacoes","*","where lixo=0 order by titulo asc");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_pacienteIndicacoes[$x->id]=$x;
	}

	$_pacienteGrauDeParentesco=array();
	$sql->consult($_p."parametros_grauparentesco","*","where lixo=0 order by titulo asc");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_pacienteGrauDeParentesco[$x->id]=$x;
	}

	$paciente=$cnt='';
	if(isset($_GET['id_paciente']) and is_numeric($_GET['id_paciente'])) {
		$sql->consult($_p."pacientes","*","where id='".$_GET['id_paciente']."'");
		if($sql->rows) {
			$paciente=mysqli_fetch_object($sql->mysqry);
			$cnt=$paciente;
		}
	}


	$campos=explode(",","");
	
	foreach($campos as $v) $values[$v]='';


	if(is_object($paciente)) {
		$values=$adm->values($campos,$cnt);
		$values['data']=date('d/m/Y H:i',strtotime($cnt->data));
	}


	$tratamentosIds=array();
	$sql->consult($_p."pacientes_tratamentos","*","where status='APROVADO' and lixo=0");
	while($x=mysqli_fetch_object($sql->mysqry)) $tratamentosIds[]=$x->id;

	$procedimentosIds=array();
	$_procedimentosAprovados=array();
	$sql->consult($_p."pacientes_tratamentos_procedimentos","*","where id_tratamento IN (".implode(",",$tratamentosIds).") and lixo=0 and situacao='aprovado' and status_evolucao NOT IN ('cancelado','finalizado')");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$procedimentosIds[]=$x->id_procedimento;
		$_procedimentosAprovados[$x->id]=$x;
	}

	$_procedimentos=array();
	$sql->consult($_p."parametros_procedimentos","*","where id IN (".implode(",",$procedimentosIds).")");
	while($x=mysqli_fetch_object($sql->mysqry)) $_procedimentos[$x->id]=$x;

	
	?>
	<section class="content">
		
		<?php
		require_once("includes/abaPaciente.php");
		?>

		<script type="text/javascript">
			$(function(){
				$('input[name=tipo]').click(function(){
					let tipo = $(this).val();
					$(`.js-box`).hide();
					$(`.js-box-${tipo}`).show();
				});

				$('.js-btn-addProcedimento').click(function(){
					$.fancybox.open({
						src:'#modalProcedimento'
					})
				});
				
				$('#modalProcedimento').hide();
			});
		</script>

	
		<section class="grid">
			<div class="box">
				
				<div class="filtros">
					<h1 class="filtros__titulo">Procedimentos Aprovados</h1>
					<div class="filtros-acoes">
						<a href="<?php echo $_page."?".$url;?>"><i class="iconify" data-icon="bx-bx-left-arrow-alt"></i></a>
						<a href="javascript:;" class="button js-btn-addProcedimento tooltip " title="Adicionar Procedimento" style="background:var(--azul);color:#FFF;float: right"><i class="iconify" data-icon="ic-baseline-add"></i> Adicionar Procedimento</a>
					</div>
				</div>
				<fieldset>
					<legend>Procedimentos</legend>
					<dl class="dl3">
							<dt>Procedimento</dt>
							<dd>
								<select class="js-id_procedimento chosen">
									<option value=""></option>
									<?php
									foreach($_procedimentosAprovados as $p) {
										if(isset($_procedimentos[$p->id_procedimento])) {
											$procedimento=$_procedimentos[$p->id_procedimento];

											echo '<option value="'.$p->id.'">'.utf8_encode($procedimento->titulo.' - '.$p->opcao).'</option>';
										}
									}
									?>
								</select>
							</dd>
						</dl>
							<a href="javascript:;" class="button js-btn-addProcedimento tooltip " title="Adicionar Procedimento" style="background:var(--azul);color:#FFF;float: right"><i class="iconify" data-icon="ic-baseline-add"></i> Adicionar Procedimento</a>
					<div class="registros2">
						
					</div>
				</fieldset>


			</div>				
		</section>

				
	</section>
		
<?php
include "includes/footer.php";
?>