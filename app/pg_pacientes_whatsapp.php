<?php
	if(isset($_POST['ajax'])) {
		require_once("lib/conf.php");	
		require_once("usuarios/checa.php");


		$paciente = '';
		if(isset($_POST['id_paciente']) and is_numeric($_POST['id_paciente'])) {
			$sql->consult($_p."pacientes","id,nome","where id=".$_POST['id_paciente']);
			if($sql->rows) {
				$paciente=mysqli_fetch_object($sql->mysqry);
			}
		}

		$evolucao = '';
		if(is_object($paciente) and isset($_POST['id_evolucao']) and is_numeric($_POST['id_evolucao'])) {
			$sql->consult($_p."pacientes_evolucoes","id,data","where id=".$_POST['id_evolucao']." and id_paciente=$paciente->id and lixo=0");
			if($sql->rows) {
				$evolucao=mysqli_fetch_object($sql->mysqry);
			}
		}


		$rtn = array();

		if($_POST['ajax']=="evolucaoErrataPersisitr") {

			$texto = (isset($_POST['texto']) and !empty($_POST['texto']))?$_POST['texto']:'';

			$erro='';
			if(empty($paciente)) $erro='Paciente não encontrado!';
			else if(empty($evolucao)) $erro='Evolução não encontrada!';
			else if(empty($texto)) $erro='Preencha o campo da errata!';

			if(empty($erro)) {

				$vsql="data=now(),
						id_usuario=$usr->id,
						id_evolucao=$evolucao->id,
						id_paciente=$paciente->id,
						texto='".addslashes(utf8_decode($texto))."'";

				$sql->add($_p."pacientes_evolucoes_erratas",$vsql);

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

	$_table=$_p."pacientes_prontuarios";
	require_once("includes/header/headerPacientes.php");
?>



	<main class="main">
		<div class="main__content content">

			<section class="filter">
				<div class="filter-group">
					<div class="filter-title">
						<h1>Histórico Whatsapp</h1>
					</div>
				</div>
			</section>

			<section class="grid">

				<div class="box box-col">

					<?php 
					require_once("includes/submenus/subPacientesWhatsapp.php");
					?>

					<div class="box-col__inner1">
				
						<div class="list1">

							<?php
						
							$_tipos=[];
							$sql->consult($_p."whatsapp_mensagens_tipos","*","");
							while($x=mysqli_fetch_object($sql->mysqry)) $_tipos[$x->id]=$x;

							$registros=[];
							$where="where id_paciente=$paciente->id and lixo=0 order by data desc";
							$sql->consult($_p."whatsapp_mensagens","*",$where);

							while($x=mysqli_fetch_object($sql->mysqry)) {
								$registros[]=$x;
							}

							if(count($registros)==0) echo "<center>Não foi enviado nenhuma mensagem para este paciente.</center>";
							else {
							?>
							<table class="js-table-pagamentos">

								<div class="history-item">
									<div class="infozap-chat">
								<?php
								foreach($registros as $x) {
									$tipo = '';
									if(isset($_tipos[$x->id_tipo])) $tipo=$_tipos[$x->id_tipo];
									if(empty($tipo)) continue;
								?>
									
									

										<div class="infozap-chat-text infozap-chat-text--author">
											<article>
												<p class="infozap-chat-text__msg">
													<?php echo utf8_encode($x->mensagem);?>
												</p>
												<p class="infozap-chat-text__date"><?php echo date('d/m/Y H:i',strtotime($x->data));?></p>
												<p class="infozap-chat-text__date" style="font-weight: bold;color:#666;margin-top:5px;"><?php echo utf8_encode($tipo->titulo);?></p>
											</article>
										</div>
								<?php
								}
								?>
									</div>
								</div>
							</table>
							<?php
							}
							?>

						</div>	
					</div>
					
				</div>

			</section>


			
		</div>
	</main>

<?php 

	require_once("includes/api/apiAsideArquivos.php");

	include "includes/footer.php";
?>	