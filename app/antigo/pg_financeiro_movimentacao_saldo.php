<?php
	$title="";
	include "includes/header.php";
	include "includes/nav.php";

	if($usr->tipo!="admin" and !in_array("clientes",$_usuariosPermissoes)) {
		$jsc->jAlert("Você não tem permissão para acessar esta área!","erro","document.location.href='dashboard.php'");
		die();
	}
	$values=$adm->get($_GET);
	$_table=$_p."financeiro_bancosecontas";

?>
<section class="content">

	<?php
	require_once("includes/asideFinanceiro.php");
	?>
	<section id="cal-popup" class="cal-popup cal-popup_paciente cal-popup_top cal-popup_alt" style="left:703px; top:338px; margin-left:303px;display: none">
		<a href="javascript:;" class="cal-popup__fechar js-btn-fechar"><i class="iconify" data-icon="mdi-close"></i></a>
		
		<div class="reg-data" style="flex:0 1 30%;margin-bottom: 20px;">
			<h1 class="js-titulo"></h1>
			<p class="js-tipo"></p>
		</div>

		<center>
			<a href="javascript:;" class="js-btn-ofx button">Carregar OFX</a>
			<a href="javascript:;"class="js-btn-visualizar button">Visualizar Movimentações</a>
		</center>
	</section>

	<script type="text/javascript">
		const popView = (obj) => {

			$('.js-pop-informacoes').click();

			index=$(obj).index();
			id=$(`div.reg a:eq(${index})`).find('.js-id').val();

			$('#cal-popup .js-titulo').html($(`div.reg a:eq(${index})`).find('.js-titulo').html());
			$('#cal-popup .js-tipo').html($(`div.reg a:eq(${index})`).find('.js-tipo').html());


			tipo = $(`div.reg a:eq(${index})`).find('.js-input-tipo').val();

			$('#cal-popup .js-btn-visualizar').show().attr('href',`pg_financeiro_movimentacao.php?id_conta=${id}`);

			if(tipo=="contacorrente") {
				$('#cal-popup .js-btn-ofx').show().attr('href',`pg_financeiro_movimentacao_ofx.php?id_conta=${id}`);
			} else {
				$('#cal-popup .js-btn-ofx').hide().attr('href',`javascript:;`);
			}

			$('#cal-popup')
					.removeClass('cal-popup_left')
					.removeClass('cal-popup_right')
					.removeClass('cal-popup_bottom')
					.removeClass('cal-popup_top');

			let clickTop=obj.getBoundingClientRect().top+window.scrollY;
		
			let clickLeft=Math.round(obj.getBoundingClientRect().left);
			let clickMargin=Math.round(obj.getBoundingClientRect().width/2);
			$(obj).prev('.cal-popup')
					.removeClass('cal-popup_left')
					.removeClass('cal-popup_right')
					.removeClass('cal-popup_bottom')
					.removeClass('cal-popup_top');

			let popClass='cal-popup_top';
			$('#cal-popup').addClass(popClass).toggle();
			$('#cal-popup').css({'top':clickTop,'left':clickLeft,'margin-left': clickMargin});
			$('#cal-popup').show();


			
		}

		$(function(){

			$('.js-btn-fechar').click(function(){
				$('.cal-popup').hide();
			});
			
			$(document).mouseup(function(e)  {
			    var container = $("#cal-popup");
			    if (!container.is(e.target) && container.has(e.target).length === 0) $('#cal-popup').hide();
			});
		})
	</script>
	<section class="grid">
		<div class="box">

			<div class="reg">
				<?php
				$sql->consult($_table,"*","where lixo=0");
				if($sql->rows==0) {
					echo "<center>Nenhum Banco/Conta cadastrado</center>";
				} else {
					
					while($x=mysqli_fetch_object($sql->mysqry)) {
						$registros[]=$x;
					}

					foreach($registros as $x) {
						$saldo=0;
						$sql->consult($_p."financeiro_extrato","SUM(valor) as total","where id_conta='".$x->id."' and lixo=0 order by data_extrato desc");
						if($sql->rows) {
							$t=mysqli_fetch_object($sql->mysqry); 
							$saldo=$t->total;
						}

				?>
				<a href="javascript:;" class="reg-group" onclick="popView(this);">
					<input type="hidden" class="js-input-tipo" value="<?php echo $x->tipo;?>" />
					<input type="hidden" class="js-id" value="<?php echo $x->id;?>" />

					<div class="reg-color" style=""></div>
					<div class="reg-data" style="flex:0 1 30%;">
						<h1 class="js-titulo"><?php echo utf8_encode($x->titulo);?></h1>
						<p class="js-tipo"><?php echo isset($_bancosEContasTipos[$x->tipo])?$_bancosEContasTipos[$x->tipo]:$x->tipo;?></p>
					</div>
					
					<div class="reg-data" style="flex:0 1 120px;">
						<h1>R$ <?php echo number_format($saldo,2,",",".");?></h1>
					</div>
					
				</a>
				<?php
					}

					
				}
				?>
			</div>
			
		</div>
	</section>


</section>

<?php
	include "includes/footer.php";
?>