<?php
	require_once("../lib/conf.php");
	$dir="../";
	require_once("../usuarios/checa.php");

	$jsc = new Js();

	$unidade='';
	if(isset($_GET['id_unidade']) and is_numeric($_GET['id_unidade']) and isset($_optUnidades[$_GET['id_unidade']])) {
		$unidade=$_optUnidades[$_GET['id_unidade']];
	}
	if(empty($unidade)) {
		$jsc->jAlert("Unidade não encontrada!","erro","$.fancybox.close()");
		die();
	}

	$_procedimentos=array();
	$sql->consult($_p."parametros_procedimentos","*","where lixo=0");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_procedimentos[$x->id]=$x;
	}


	$_regioesOpcoes=array();
	$sql->consult($_p."parametros_procedimentos_regioes_opcoes","*","where id_regiao IN (2,3) order by titulo asc");
	while($x=mysqli_fetch_object($sql->mysqry)) $_regioesOpcoes[$x->id_regiao][]=$x;

	$_regioes=array();
	$sql->consult($_p."parametros_procedimentos_regioes","*","");
	while($x=mysqli_fetch_object($sql->mysqry)) $_regioes[$x->id]=$x;
	
?>
<script type="text/javascript">
	var dentes = [];

	$(function(){
		//$('.js-regiao-2-select,.js-regiao-3-select').chosen({hide_results_on_select:false,allow_single_deselect:true});
		$('select.js-agenda-id_procedimento').change(function(){
			let id = $(this).val();
			let id_regiao = $(this).find('option:selected').attr('data-id_regiao');
			let regiao = $(this).find('option:selected').attr('data-regiao');
			console.log(id+' '+id_regiao)
			$(`.js-regiao`).hide();
			$(`.js-regiao-${id_regiao}`).show();
			$(`.js-regiao-${id_regiao}`).find('select').chosen({hide_results_on_select:false,allow_single_deselect:true});
			$(`.js-regiao-descritivo`).show().find('dd input').val(regiao)
		});

		$('.js-procedimentos-salvar').click(function(){
			let id_procedimento = $('select.js-agenda-id_procedimento').val();
			let id_regiao = $('select.js-agenda-id_procedimento option:selected').attr('data-id_regiao');

			if(id_procedimento.length==0) {
				swal({title: "Erro!", text: "Selecione o Procedimento", type:"error", confirmButtonColor: "#424242"});
			} else {
				let data = `ajax=agendamentoProcedimentoPersistir&id_procedimento=${id_procedimento}`;
				
				let complemento = ``;
				let erro = ``;
				if($(`.js-regiao-${id_regiao}`).length>0) {
					if($(`.js-regiao-${id_regiao}-select`).val()===null || $(`.js-regiao-${id_regiao}-select`).val()==="") {
						erro=`Selecione a Região!`;
					} else {
						complemento=`&opcoes=${$(`.js-regiao-${id_regiao}-select`).val()}`;
						data += complemento;
					}
				}

				if(erro.length==0) {
					alert(data);
				} else {

					swal({title: "Erro!", text: erro, type:"error", confirmButtonColor: "#424242"});
				}

				

			}
		})
	})
</script>
<form method="post" class="formulario js-form-agendamento" style="width:80%;">
	<fieldset>
		<legend>Procedimento</legend>
		
		<div class="colunas4">
			<dl>
				<dt>Procedimento</dt>
				<dd>
					<select class="js-agenda-id_procedimento">
						<option value=""></option>
						<?php
						foreach($_procedimentos as $p) {
							echo '<option value="'.$p->id.'" data-id_regiao="'.$p->id_regiao.'" data-regiao="'.(isset($_regioes[$p->id_regiao])?utf8_encode($_regioes[$p->id_regiao]->titulo):"-").'">'.utf8_encode($p->titulo).'</option>';
						}
						?>
					</select>
				</dd>
			</dl>
			<dl class="js-regiao-descritivo" style="display: none">
				<dt>Região</dt>
				<dd><input type="text" disabled /></dd>
			</dl>	
		</div>
		<dl class="js-regiao-2 js-regiao" style="display: none;">
			<dt>Arcada(s)</dt>
			<dd>
				<select class="js-regiao-2-select" multiple>
					<option value=""></option>
					<?php
					if(isset($_regioesOpcoes[2])) {
						foreach($_regioesOpcoes[2] as $o) {
							echo '<option value="'.$o->id.'">'.utf8_encode($o->titulo).'</option>';
						}
					}
					?>
				</select>
			</dd>
		</dl>
		<dl class="js-regiao-3 js-regiao" style="display: none">
			<dt>Quadrante(s)</dt>
			<dd>
				<select class="js-regiao-3-select" multiple>
					<option value=""></option>
					<?php
					if(isset($_regioesOpcoes[3])) {
						foreach($_regioesOpcoes[3] as $o) {
							echo '<option value="'.$o->id.'">'.utf8_encode($o->titulo).'</option>';
						}
					}
					?>
				</select>
			</dd>
		</dl>
	</fieldset>

	<fieldset class="js-regiao-4 js-regiao" style="display: none">
		<legend>Dente(s)</legend>
		<?php
		// id_regiao=4 -> por dente
		$dentes=array();
		$sql->consult($_p."parametros_procedimentos_regioes_opcoes","*","where id_regiao=4 and direito=1 order by id desc");
		while($x=mysqli_fetch_object($sql->mysqry)) $dentes[$x->permanente][$x->superior][]=$x;


		$sql->consult($_p."parametros_procedimentos_regioes_opcoes","*","where id_regiao=4 and direito=0 order by id asc");
		while($x=mysqli_fetch_object($sql->mysqry)) $dentes[$x->permanente][$x->superior][]=$x;
		
		?>
		<fieldset class="js-dentes">
			<legend>Dentes Permanentes</legend>
			<input type="text" class="js-regiao-4-select">
			<script type="text/javascript">
				const atualizaDentes = () => {

					$('.js-dente').removeClass('active');
					dentes.map(x=> {
						$(`.js-dente-${x}`).addClass('active');
					})

				}
				$(function(){
					$('.js-dentes .js-dente').click(function(){
						let id = $(this).attr('data-id');
						let error= false;
						dentes=dentes.filter(x=> {
							if(x==id) error=true;
							else return x;
						})
						if(error===false) dentes.push(id);
						atualizaDentes();
						$('.js-regiao-4-select').val(dentes);
					})
				})
			</script>
			<div class="js-dentes-permanentes">
				<?php
				foreach($dentes[1][1] as $d) {
				?>
				<div class="js-dente js-dente-<?php echo $d->id;?>" data-id="<?php echo $d->id;?>">
					<?php echo $d->titulo;?>
				</div>
				<?php	
				}
				?>
			</div>

			<div class="js-dentes-permanentes">
				<?php
				foreach($dentes[1][0] as $d) {
				?>
				<div class="js-dente js-dente-<?php echo $d->id;?>" data-id="<?php echo $d->id;?>">
					<?php echo $d->titulo;?>
				</div>
				<?php	
				}
				?>
			</div>
		</fieldset>


		<fieldset class="js-dentes">
			<legend>Dentes Deciduos</legend>

			<div class="js-dentes-permanentes">
				<?php
				foreach($dentes[0][1] as $d) {
				?>
				<div class="js-dente js-dente-<?php echo $d->id;?>" data-id="<?php echo $d->id;?>">
					<?php echo $d->titulo;?>
				</div>
				<?php	
				}
				?>
			</div>

			<div class="js-dentes-permanentes">
				<?php
				foreach($dentes[0][0] as $d) {
				?>
				<div class="js-dente js-dente-<?php echo $d->id;?>" data-id="<?php echo $d->id;?>">
					<?php echo $d->titulo;?>
				</div>
				<?php	
				}
				?>
			</div>

		</fieldset>
	</fieldset>

	
	
	<center>
		<button type="button" class="botao botao-principal js-procedimentos-salvar" style="margin-top:40px;"><i class="icon-ok"></i> Salvar</button>
	</center>

</form>