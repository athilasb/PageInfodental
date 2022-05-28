<?php
	if(isset($_POST['ajax'])) {
		require_once("lib/conf.php");	
		require_once("usuarios/checa.php");

		$sql = new Mysql();

		$rtn = array();
		if($_POST['ajax']=="indicacoesLista") {
			$indicacao='';
			if(isset($_POST['id_indicacao']) and is_numeric($_POST['id_indicacao'])) {
				$sql->consult($_p."parametros_indicacoes","*","where id='".addslashes($_POST['id_indicacao'])."' and lixo=0");
				if($sql->rows) {
					$indicacao=mysqli_fetch_object($sql->mysqry);
				}
			}

			if(is_object($indicacao)) {
				$sql->consult($_p."parametros_indicacoes_listas","*","where id_indicacao=$indicacao->id and lixo=0 order by titulo asc");
				$indicacoes=array();
				while($x=mysqli_fetch_object($sql->mysqry)) {
					$indicacoes[]=array('id'=>$x->id,
									'titulo'=>utf8_encode($x->titulo));
				}

				$rtn=array('success'=>true,'indicacoes'=>$indicacoes);
			} else {
				$rtn=array('success'=>false,'error'=>'Indicação não definida!');
			}
		}

		header("Content-type: application/json");
		echo json_encode($rtn);
		die();
	}
	if(isset($_GET['ajax'])) {
		if($_GET['ajax']=="profissao") {
			if(isset($_GET['id_profissao']) and is_numeric($_GET['id_profissao'])) {
				$_GET['edita']=$_GET['id_profissao'];
				$_GET['form']=1;
			}
			require_once("pg_parametros_profissoes.php");

		}

		die();
	}
	$title="";
	include "includes/header.php";
	include "includes/nav.php";

	if($usr->tipo!="admin" and !in_array("clientes",$_usuariosPermissoes)) {
		$jsc->jAlert("Você não tem permissão para acessar esta área!","erro","document.location.href='dashboard.php'");
		die();
	}
	$values=$adm->get($_GET);

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

?>
<section class="content">

	<?php /*<header class="caminho">
		<h1 class="caminho__titulo">Contatos <i class="iconify" data-icon="bx-bx-chevron-right"></i> <strong>Pacientes</strong></h1>
		<a href="javascript:;" class="caminho__tutorial button button__sec"><i class="iconify" data-icon="ic-baseline-slow-motion-video"></i> ASSISTIR TUTORIAL</a>
	</header>*/?>

	<?php
	$_table=$_p."pacientes";
	$_page=basename($_SERVER['PHP_SELF']);

	$_width=400;
	$_height=400;
	$_dir="arqs/pacientes/";

	$_cidades=array();
	$sql->consult($_p."cidades","*","");
	while($x=mysqli_fetch_object($sql->mysqry)) $_cidades[$x->id]=$x;
	
	
	require_once("includes/asidePaciente.php");
	$where="WHERE lixo='0'";
	if(isset($values['busca']) and !empty($values['busca'])) $where.=" and (nome like '%".utf8_decode($values['busca'])."%' or cpf like '%".cpf($values['busca'])."%' or id = '".addslashes($values['busca'])."')";
	
	$where.=" order by nome asc";
	
	$registros=array();
	$sql->consult($_table,"*",$where);
	if($sql->rows) {
		while($x=mysqli_fetch_object($sql->mysqry)) {
			$registros[$x->codigo_bi][]=$x;
		}
	}
	?>


	<section class="grid">
		<div class="kanban" id="kanban">
			<?php
			foreach($_codigoBI as $codigoBI=>$biTitulo) {
				//if($codigoBI==6) continue;
			?>
			<div class="kanban-item" style="background:<?php echo $_codigoBICores[$codigoBI];?>;color:var(--cor1);">
				<h1 class="kanban-item__titulo"><?php echo $biTitulo;?> (<?php echo (isset($registros[$codigoBI]))?number_format(count($registros[$codigoBI]),0,"","."):0;?>) <span class="tooltip" title="Descrição do item..."><i class="iconify" data-icon="ph:question"></i></span></h1>
				<?php
				if(isset($registros[$codigoBI])) {
					foreach($registros[$codigoBI] as $x) {
						$cor='';
				?>
				<a href="pg_contatos_pacientes_resumo.php?id_paciente=<?php echo $x->id?>" onclick="$(this).next('.kanban-card-modal').show();" class="kanban-card-dados js-kanban-item">
					<div class="reg-data" style="flex:0 1 50%;">
						<h1><?php echo strtoupperWLIB(utf8_encode($x->nome));?></h1>
						<p>Código: <?php echo $x->id;?></p>
					</div>
				</a>
				
				<?php		
					}
				}
				?>
			</div>
			<?php
				}
			?>
		</div> 

	</section>

	

</section>

<?php
	include "includes/footer.php";
?>