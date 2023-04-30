<?php
	require_once("lib/conf.php");
	$_table=$_p."parametros_cadeiras";

	if(isset($_POST['ajax'])) {

		require_once("usuarios/checa.php");

		$rtn=array();

		if($_POST['ajax']=="parametrosPersistir") {

			$campos = explode(",","check_agendaDesativarRegrasStatus,check_agendaTamanhoMinimoAltura");
			$campo = (isset($_POST['campo']) and in_array($_POST['campo'],$campos)) ? $_POST['campo'] : '';
			$checked = (isset($_POST['checked']) and $_POST['checked']==1) ? 1 : 0;

			$erro='';
			if(empty($campo)) $erro='Campo não definido!';

			if(empty($erro)) {
				$vSQL=$campo."='".$checked."'";

				$sql->consult($_p."configuracoes_parametros","*","");
				if($sql->rows==0) {
					$sql->add($_p."configuracoes_parametros","check_agendaDesativarRegrasStatus=0");
					$sql->consult($_p."configuracoes_parametros","*","");
				} 

				$parametros=mysqli_fetch_object($sql->mysqry);

				$sql->update($_p."configuracoes_parametros",$vSQL,"where id=$parametros->id");
			}

			if(empty($erro)) {
				$rtn=array('success'=>true);
			} else {
				$rtn=array('success'=>false,'error'=>$erro);
			}
		} 

		header("Content-type: application/json");
		echo json_encode($rtn);
		die();
	}
	include "includes/header.php";
	include "includes/nav.php";

	$values=$adm->get($_GET);
	$campos=explode(",","titulo");


	$sql->consult($_p."configuracoes_parametros","*","");
	if($sql->rows==0) {
		$sql->add($_p."configuracoes_parametros","check_agendaDesativarRegrasStatus=0");
		$sql->consult($_p."configuracoes_parametros","*","");
	} 

	$cnt=mysqli_fetch_object($sql->mysqry);
	

?>

	<header class="header">
		<div class="header__content content">
			<div class="header__inner1">
				<section class="header-title">
					<h1>Configuração</h1>
				</section>
				<?php
				require_once("includes/menus/menuConfiguracoes.php");
				?>
			</div>
		</div>
	</header>

	<main class="main">
		<div class="main__content content">

			<section class="filter">
				
				<div class="filter-group">
					<div class="filter-title">
						<h1>Configure a clínica</h1>
					</div>
				</div>
			</section>
 	
			<section class="grid">

				<div class="box box-col">

					<?php
					require_once("includes/submenus/subConfiguracoesClinica.php");
					?>
					<script type="text/javascript">
						$(function(){

							$('.js-check').click(function(){
								let campo = $(this).attr('name');
								let checked = $(this).prop('checked') ? 1 : 0;

								let data = `ajax=parametrosPersistir&campo=${campo}&checked=${checked}`;
								$.ajax({
									type:"POST",
									data:data,
									success:function(rtn) {
										if(rtn.success) {

										} else {
											let erro = rtn.error ? rtn.error : 'Algum erro ocorreu durante a persistência dos paramêtros';

											swal({title: "Erro!", text: erro, type:"error", confirmButtonColor: "#424242"});
										}
									}
								})
							})
						})
					</script>

					<div class="box-col__inner1">
				
						<form>
							<fieldset>
								<legend>Agenda</legend>

								<dl>
									<dd>
										<label><input type="checkbox" name="check_agendaDesativarRegrasStatus" value="1" class="input-switch js-check"<?php echo $cnt->check_agendaDesativarRegrasStatus==1?" checked":"";?> /> Desativar regras de status de agendamento</label>
									</dd>
								</dl>
								<dl>
									<dd>
										<label><input type="checkbox" name="check_agendaTamanhoMinimoAltura" value="1" class="input-switch js-check"<?php echo $cnt->check_agendaTamanhoMinimoAltura==1?" checked":"";?> /> Horários possuir altura mínima no calendário</label>
									</dd>
								</dl>
							</fieldset>
						</form>
					</div>			
				</div>

			</section>
		
		</div>
	</main>

<?php 
include "includes/footer.php";
?>	