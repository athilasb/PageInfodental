<?php
	$title="";
	include "includes/header.php";
	include "includes/nav.php";

	if($usr->tipo!="admin" and !in_array("clientes",$_usuariosPermissoes)) {
		$jsc->jAlert("Você não tem permissão para acessar esta área!","erro","document.location.href='dashboard.php'");
		die();
	}
	$values=$adm->get($_GET);

	$_regioes=array();
	$sql->consult($_p."parametros_procedimentos_regioes","*","where lixo=0 order by titulo asc");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_regioes[$x->id]=$x;
	}
?>
<section class="content">

	<?php
	require_once("includes/abaConfiguracao.php");
	?>

	<?php
	$_table=$_p."parametros_cartoes_bandeiras";
	$_page=basename($_SERVER['PHP_SELF']);
	?>

		<section class="grid grid_2">

			<section class="grid">
				<div class="box">
					<div class="filter">

						<div class="filter-group">
							<div class="filter-button">
								<a href="box/boxAnamnese.php" data-fancybox data-type="ajax" data-height="300" data-padding="0" class="verde adicionar tooltip" title="adicionar"><i class="iconify" data-icon="bx-bx-plus"></i><span>Anamnese</span></a>
							</div>
						</div>

					</div>
					<div class="reg">
						<?php
						$registros = array();
						$sql->consultPagMto2($_p."parametros_anamnese","*",10,"WHERE lixo=0 order by titulo asc","",15,"pagina",$_page."?".$url."&pagina=");
						if($sql->rows==0) {
							$msgSemResultado="Nenhuma Anamnese";
							if(isset($values['busca'])) $msgSemResultado="Nenhuma Anamnese encontrada";

							echo "<center>$msgSemResultado</center>";
						} else {
							while($x=mysqli_fetch_object($sql->mysqry)) {
								$registros[]=$x;
						?>
						<a href="box/boxAnamnese.php?id_anamnese=<?php echo $x->id;?>" data-fancybox data-type="ajax" data-height="300" data-padding="0" class="js-anamnese-<?php echo $x->id;?> reg-group">
							<div class="reg-color" style="background-color:green;"></div>
							<div class="reg-data" style="flex:0 1 50%;">
								<h1><?php echo strtoupperWLIB(utf8_encode($x->titulo));?></h1>
							</div>
						</a>
						<?php
							}

							if(isset($sql->myspaginacao) and !empty($sql->myspaginacao)) {
							?>	
						<div class="paginacao" style="margin-top: 30px;">
							<p class="paginacao__item"><span>Página</span><?php echo $sql->myspaginacao;?></p>
						</div>
							<?php
							}
						}
						?>
					</div>

				</div>
			</section>
			<?php
			if(isset($_GET['abrirAnamnese']) and is_numeric($_GET['abrirAnamnese'])) {
			?>
			<script type="text/javascript">
				$(function(){
					$(`.js-anamnese-<?php echo $_GET['abrirAnamnese'];?>`).trigger('click');
				})
			</script>
			<?php	
			}

			?>

			<section class="grid">
				<div class="box">
					<div class="filter">

						<div class="filter-group">
							<div class="filter-button">
								<a href="box/boxExame.php" data-fancybox data-type="ajax" data-height="400" data-padding="0" class="verde adicionar tooltip" title="adicionar"><i class="iconify" data-icon="bx-bx-plus"></i><span>Exame</span></a>
							</div>
						</div>

					</div>
					<div class="reg">
						<?php
						$registros = array();
						$sql->consultPagMto2($_p."parametros_examedeimagem","*",10,"WHERE lixo=0 order by titulo asc","",15,"pagina",$_page."?".$url."&pagina=");
						if($sql->rows==0) {
							$msgSemResultado="Nenhum Exame";
							if(isset($values['busca'])) $msgSemResultado="Nenhuma Exame encontrado";

							echo "<center>$msgSemResultado</center>";
						} else {
							while($x=mysqli_fetch_object($sql->mysqry)) {
								$registros[]=$x;
						?>
						<a href="box/boxExame.php?id_exame=<?php echo $x->id;?>" data-fancybox data-type="ajax" data-height="300" data-padding="0" class="reg-group">
							<div class="reg-color" style="background-color:green;"></div>
							<div class="reg-data" style="flex:0 1 50%;">
								<h1><?php echo strtoupperWLIB(utf8_encode($x->titulo));?></h1>
								<p>
									<?php echo isset($_regioes[$x->id_regiao])?utf8_encode($_regioes[$x->id_regiao]->titulo):'-';?>
								</p>
							</div>
						</a>
						<?php
							}

							if(isset($sql->myspaginacao) and !empty($sql->myspaginacao)) {
							?>	
						<div class="paginacao" style="margin-top: 30px;">
							<p class="paginacao__item"><span>Página</span><?php echo $sql->myspaginacao;?></p>
						</div>
							<?php
							}
						}
						?>
					</div>

				</div>
			</section>

		</section>


</section>

<?php
	include "includes/footer.php";
?>