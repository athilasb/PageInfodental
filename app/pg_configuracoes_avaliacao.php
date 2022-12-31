<?php
	require_once("lib/conf.php");
	require_once("usuarios/checa.php");

	$_table=$_p."avaliacoes_habilitadas";

	$_avaliacoes = array();
	$sql->consult("infodentalADM.infod_avaliacoes_tipos","*","where lixo=0") ;
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_avaliacoes[$x->id]=$x;
	}

	if(isset($_POST['ajax'])) {

		require_once("usuarios/checa.php");
		$rtn=array();

		if($_POST['ajax']=="persistirPub") {
			$avaliacao = '';
			if(isset($_POST['id_avaliacao']) and is_numeric($_POST['id_avaliacao'])) {
				$sql->consult($_p."avaliacoes_habilitadas","*","where id=".$_POST['id_avaliacao']);
				if($sql->rows) {
					$avaliacao=mysqli_fetch_object($sql->mysqry);
				}
			}

			if(empty($avaliacao)) {
				$rtn=array('success'=>false,'error'=>'Registro não encontrado!');
			} else {
				$vWHERE="where id=$avaliacao->id";
				$vSQL="pub='".((isset($_POST['pub']) and $_POST['pub']==1)?1:0)."'";
				$sql->update($_p."avaliacoes_habilitadas",$vSQL,$vWHERE);
				$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='delete',vsql='".addslashes($vSQL)."',vwhere='".addslashes($vWHERE)."',tabela='".$_p."avaliacoes_habilitadas"."',id_reg='".$avaliacao->id."'");

				$rtn=array('success'=>true);
			}
		}

		header("Content-type: application/json");
		echo json_encode($rtn);
		die();
	}

	include "includes/header.php";
	include "includes/nav.php";

	$values=$adm->get($_GET);
	$campos=explode(",","id_tipo,pub");

	foreach($_avaliacoes as $x) {
		$sql->consult($_p."avaliacoes_habilitadas","*","WHERE id_tipo='".$x->id."'");
		if($sql->rows==0) {
			$sql->add($_p."avaliacoes_habilitadas","id_tipo='".$x->id."',pub=1");
		}
	}

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
						<h1>Dados</h1>
					</div>
				</div>
			</section>
 	
			<section class="grid">

				<div class="box box-col">

					<?php
					require_once("includes/submenus/subConfiguracoesAvaliacao.php");
					?>
					<script type="text/javascript">
						
						$(function(){
							$('.js-pub').click(function(){

								let obj = $(this);
								let pub = $(this).prop('checked')===true?1:0;
								let id_avaliacao = $(this).attr('data-id_avaliacao');
								let data = `ajax=persistirPub&id_avaliacao=${id_avaliacao}&pub=${pub}`;

								$.ajax({
									type:"POST",
									data:data,
									success:function(rtn) {
										if(rtn.success) {
											if(pub==1)
												obj.parent().parent().parent().find('.list1__border').css("color","green");
											else
												obj.parent().parent().parent().find('.list1__border').css("color","");

										} else if(rtn.error) {
											swal({title: "Erro", text: rtn.error, html:true, type:"error", confirmButtonColor: "#424242"});
										} else {	
											swal({title: "Erro", text: 'Algum erro ocorreu durante a ativação/desativação da avaliação', html:true, type:"error", confirmButtonColor: "#424242"});
										}
									}
								})
							});
						})
					</script>

					<div class="box-col__inner1">

						<div class="list1">
							<table>
								<?php 
									$sql->consult($_p."avaliacoes_habilitadas","*","");
									while($x=mysqli_fetch_object($sql->mysqry)) {
								?>
								<tr>
									<td class="list1__border" style="color:<?php echo $x->pub==1?"green":"";?>"></td>
									<td><h1><strong><?php echo isset($_avaliacoes[$x->id_tipo])?utf8_encode($_avaliacoes[$x->id_tipo]->titulo):"-";?></strong></h1></td>
									<td><label><input type="checkbox" data-id_avaliacao="<?php echo $x->id;?>" class="input-switch js-pub"<?php echo $x->pub==1?" checked":"";?> /> </label></td>
								</tr>
								<?php 
									}
								?>
							</table>
						</div>
							

					</div>					
				</div>

			</section>
		
		</div>
	</main>

<?php 
	include "includes/footer.php";
?>	