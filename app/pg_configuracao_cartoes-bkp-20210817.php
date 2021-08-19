<?php
	$title="";
	include "includes/header.php";
	include "includes/nav.php";

	if($usr->tipo!="admin" and !in_array("clientes",$_usuariosPermissoes)) {
		$jsc->jAlert("Você não tem permissão para acessar esta área!","erro","document.location.href='dashboard.php'");
		die();
	}
	$values=$adm->get($_GET);
?>
<section class="content">

	<?php
	require_once("includes/abaConfiguracao.php");
	?>

	<?php
	$_table=$_p."parametros_cartoes_bandeiras";
	$_page=basename($_SERVER['PHP_SELF']);
	?>

		<section class="grid ">

		
			<section class="grid">

				<div class="box">
					<div class="filter">

						<div class="filter-group">
							<div class="filter-button">
								<a href="box/boxOperadoraCartao.php" data-fancybox data-type="ajax" data-height="400" data-padding="0" class="verde adicionar tooltip" title="adicionar"><i class="iconify" data-icon="bx-bx-plus"></i><span>Nova operadora</span></a>
							</div>
						</div>

					</div>
					<div class="reg">
						<?php
						$registros = array();
						$sql->consultPagMto2($_p."parametros_cartoes_operadoras","*",10,"WHERE lixo=0 order by titulo asc","",15,"pagina",$_page."?".$url."&pagina=");
						if($sql->rows==0) {
							$msgSemResultado="Nenhuma Operadora";
							if(isset($values['busca'])) $msgSemResultado="Nenhuma Operadora encontrada";

							echo "<center>$msgSemResultado</center>";
						} else {
							while($x=mysqli_fetch_object($sql->mysqry)) {
								$registros[]=$x;
						?>
						<a href="box/boxOperadoraCartao.php?id_operadora=<?php echo $x->id;?>" data-fancybox data-type="ajax" data-height="300" data-padding="0" class="reg-group">
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

		</section>


</section>

<?php
	include "includes/footer.php";
?>