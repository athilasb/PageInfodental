<?php
	include "includes/header.php";
	include "includes/nav.php";

	$_table=$_p."pacientes";
	$_page=basename($_SERVER['PHP_SELF']);

	$_profissionais=array();
	$sql->consult($_p."profissionais","*","where lixo=0 order by nome asc");//"where unidades like '%,$unidade->id,%' and lixo=0 order by nome asc");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_profissionais[$x->id]=$x;
	}

	$_tiposExames=array();
	$sql->consult($_p."parametros_examedeimagem","*","where lixo=0 order by titulo asc");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_tiposExames[$x->id]=$x;
	}

	$_clinicas=array();
	$sql->consult($_p."parametros_fornecedores","*","where lixo=0 and tipo='CLINICA' order by razao_social, nome asc");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_clinicas[$x->id]=$x;
	}

	$_regioesOpcoes=array();
	$sql->consult($_p."parametros_procedimentos_regioes_opcoes","*","order by titulo asc");
	while($x=mysqli_fetch_object($sql->mysqry)) $_regioesOpcoes[$x->id_regiao][]=$x;

	$_regioes=array();
	$sql->consult($_p."parametros_procedimentos_regioes","*","");
	while($x=mysqli_fetch_object($sql->mysqry)) $_regioes[$x->id]=$x;


	$paciente=$cnt='';
	if(isset($_GET['id_paciente']) and is_numeric($_GET['id_paciente'])) {
		$sql->consult($_p."pacientes","*","where id='".$_GET['id_paciente']."'");
		if($sql->rows) {
			$paciente=mysqli_fetch_object($sql->mysqry);
			$cnt=$paciente;
		}
	}

	$evolucao='';
	$evolucaoPedido='';
	if(isset($_GET['edita']) and is_numeric($_GET['edita'])) {	
		$sql->consult($_p."pacientes_evolucoes","*","where id='".$_GET['edita']."' and id_tipo=8");
		if($sql->rows) {
			$evolucao=mysqli_fetch_object($sql->mysqry);

			$sql->consult($_p."pacientes_evolucoes_pedidosdeexames","*","where id_evolucao=$evolucao->id and lixo=0");
			if($sql->rows) {
				$evolucaoPedido=mysqli_fetch_object($sql->mysqry);

 			} 
		} else {
			$jsc->jAlert("Procedimento Aprovado não encontrado!","erro","document.location.href='pg_contatos_pacientes_evolucao.php?id_paciente='".$paciente->id."'");
			die();
		}
	}
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

				$('.js-btn-salvar').click(function(){
					$('form').submit();
				});
			});
		</script>

		
		<section class="grid">
			<div class="box">

				<?php
				if(empty($evolucao)) {
					require_once("includes/evolucaoMenu.php");
				} else {
				?>
				<div class="filter">
					<div class="filter-group">
						<div class="filter-button">
							<a href="pg_contatos_pacientes_evolucao.php?id_paciente=<?php echo $paciente->id;?>"><i class="iconify" data-icon="bx-bx-left-arrow-alt"></i></a>
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


				<section class="js-evolucao-adicionar" id="evolucao-pedidos-de-exames">
						
					<form class="form" method="post">

						<textarea name="examesJSON" class="js-agenda-examesJSON" style="display:none;"></textarea>
						<input type="hidden" name="acao" value="wlib" />
						<input type="hidden" name="id_evolucao" value="<?php echo is_object($evolucao)?$evolucao->id:0;?>" />

						<div class="grid grid_3">
							<fieldset>
								<legend><span class="badge">2</span>Cabeçalho do exame</legend>
								
								<dl>
									<dt>Data do Pedido</dt>
									<dd><input type="text" name="data" class="datecalendar data" value="<?php echo is_object($evolucaoPedido)?date('d/m/Y',strtotime($evolucaoPedido->data_pedido)):date('d/m/Y');?>" /></dd>
								</dl>
								<dl>
									<dt>Clínica Radiológica</dt>
									<dd>
										<select name="id_clinica" class="chosen obg" data-placeholder="Selecione...">
											<option value=""></option>
											<?php
											foreach($_clinicas as $v) {
												echo '<option value="'.$v->id.'"'.((is_object($evolucaoPedido) and $evolucaoPedido->id_clinica==$v->id)?' selected':'').'>'.utf8_encode($v->tipo_pessoa=="PJ"?$v->razao_social:$v->nome).'</option>';
											}
											?>
										</select>
									</dd>
								</dl>
								<dl>
									<dt>Cirurgião Dentista</dt>
									<dd>
										<select name="id_profissional" class="chosen obg" data-placeholder="Selecione...">
											<option value=""></option>
											<?php
											foreach($_profissionais as $v) {
												echo '<option value="'.$v->id.'"'.((is_object($evolucaoPedido) and $evolucaoPedido->id_profissional==$v->id)?' selected':'').'>'.utf8_encode($v->nome).'</option>';
											}
											?>
										</select>
									</dd>
								</dl>
								
							</fieldset>

							<fieldset style="grid-column:span 2">
								<legend><span class="badge">3</span>Selecione os exames</legend>
								<dl>
									<dt>Tipo de Exame</dt>
									<dd>
										<select class="chosen js-tipoExame" data-placeholder="Selecione...">
											<option value=""></option>
											<?php
											foreach($_tiposExames as $v) {
												echo '<option value="'.$v->id.'">'.utf8_encode($v->titulo).'</option>';
											}
											?>
										</select>
									</dd>
								</dl>
								<div class="colunas3">
									<dl>
										<dt>Região</dt>
										<dd>
											<select class="chosen js-regiao" data-placeholder="Selecione...">
												<?php
												foreach($_regioes as $v) {
													echo '<option value="'.$v->id.'">'.utf8_encode($v->titulo).'</option>';
												}
												?>
											</select>
										</dd>
									</dl>
									<dl class="dl2">
										<dt>Observação</dt>
										<dd><input type="text" name="" value="Tomar 1 comprimido via oral de 8 em 8 horas por 7 dias"><button type="submit" class="button">adicionar</button></dd>
									</dl>
								</div>

								<div class="reg" style="margin-top:2rem;">
									<div class="reg-group">
										<div class="reg-color" style="background-color:palegreen"></div>
										<div class="reg-data">
											<h1>Raio X - 21, 22, 23</h1>
											<p>Enviar por email</p>
										</div>
										<div class="reg-icon">
											<a href=""><i class="iconify" data-icon="bx-bx-trash"></i></a>
										</div>
									</div>
								</div>								
							</fieldset>
						</div>
						<?php /*<fieldset>
							<legend><span class="badge">4</span> Pré-visualize e edite se necessário</legend>
							<script>
								$(function(){
									var fck_texto = CKEDITOR.replace('texto2',{
						    							filebrowserUploadUrl: '/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Files',
															height: '350',
															width: '100%',
															language: 'pt-br'
														});
									CKFinder.setupCKEditor(fck_texto);
								});
							</script>
							<textarea name="pedido" id="texto2" class="noupper" style="height:400px;">
								<?php
								if(is_object($evolucaoPedido)) {
									echo utf8_encode($evolucaoPedido->pedido);

								} else {
								?>
								<h1 style="text-align:center;">Pedido de Exame</h1>
								<p>Atesto para os devidos fins que <b><?php echo utf8_encode($paciente->nome);?></b> estará dispensado das atividades trabalhistas durante o período de {DIAS ATESTADO} dias a partir da data de {DATA ATESTADO}</p>
								<?php
								}
								?>
							</textarea>
						</fieldset>*/?>
					</form>

				</section>
				

			</div>				
		</section>
			
		</section>
		
<?php
include "includes/footer.php";
?>