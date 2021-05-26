<?php
	include "includes/header.php";
	include "includes/nav.php";

	$_table=$_p."pacientes";
	$_page=basename($_SERVER['PHP_SELF']);

	$paciente=$cnt='';
	if(isset($_GET['id_paciente']) and is_numeric($_GET['id_paciente'])) {
		$sql->consult($_p."pacientes","*","where id='".$_GET['id_paciente']."'");
		if($sql->rows) {
			$paciente=mysqli_fetch_object($sql->mysqry);
			$cnt=$paciente;
		}
	}

	$evolucao='';
	$evolucaoReceita='';
	if(isset($_GET['edita']) and is_numeric($_GET['edita'])) {	
		$sql->consult($_p."pacientes_evolucoes","*","where id='".$_GET['edita']."' and id_tipo=8");
		if($sql->rows) {
			$evolucao=mysqli_fetch_object($sql->mysqry);

			$sql->consult($_p."pacientes_evolucoes_receitas","*","where id_evolucao=$evolucao->id and lixo=0");
			if($sql->rows) {
				$evolucaoReceita=mysqli_fetch_object($sql->mysqry);

 			} 
		} else {
			$jsc->jAlert("Receita não encontrada!","erro","document.location.href='pg_contatos_pacientes_evolucao.php?id_paciente='".$paciente->id."'");
			die();
		}
	}

	$_profissionais=array();
	$sql->consult($_p."profissionais","*","where lixo=0 order by nome asc");//"where unidades like '%,$unidade->id,%' and lixo=0 order by nome asc");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_profissionais[$x->id]=$x;
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
				})
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

				<section class="js-evolucao-adicionar" id="evolucao-receituario" style="display:;">
					
					<form class="form">
						<div class="grid grid_3">
							<fieldset>
								<legend><span class="badge">2</span>Cabeçalho da receita</legend>
								
								<dl>
									<dt>Data e Hora</dt>
									<dd><input type="text" name="data" class="datahora datepicker" value="<?php echo is_object($evolucaoReceita)?date('d/m/Y H:i',strtotime($evolucaoReceita->data_receita)):date('d/m/Y H:i');?>" /></dd>
								</dl>
								<dl>
									<dt>Tipo de Uso</dt>
									<dd>
										<select name="tipo" class="obg chosen" data-placeholder="Selecione...">
											<option value=""></option>
											<?php
											foreach($_tiposReceitas as $k=>$v) {
												echo '<option value="'.$k.'">'.$v.'</option>';
											}
											?>
										</select>
									</dd>
								</dl>
								<dl>
									<dt>Cirurgião Dentista</dt>
									<dd>
										<select name="id_profissional" class="obg chosen" data-placeholder="Selecione...">
											<option value=""></option>
										<?php
										foreach($_profissionais as $p) {
											echo '<option value="'.$p->id.'"'.((is_object($evolucaoReceita) and $evolucaoReceita->id_profissional==$p->id)?' selected':'').'>'.utf8_encode($p->nome).'</option>';
										}
										?>
										</select>
									</dd>
								</dl>
								
							</fieldset>

							<fieldset style="grid-column:span 2">
								<legend><span class="badge">3</span>Selecione os medicamentos</legend>
								<dl>
									<dt>Medicamento</dt>
									<dd>
										<select name="">
											<option value="">Amoxilina</option>
										</select>
									</dd>
								</dl>
								<div class="colunas4">
									<dl>
										<dt>Quantidade</dt>
										<dd>
											<input type="number" name="" value="1" />
											<select name="">
												<option value="">caixa</option>	
											</select>
										</dd>
									</dl>
									<dl class="dl3">
										<dt>Posologia</dt>
										<dd><input type="text" name="" value="Tomar 1 comprimido via oral de 8 em 8 horas por 7 dias"><button type="submit" class="button">adicionar</button></dd>
									</dl>
								</div>

								<div class="reg" style="margin-top:2rem;">
									<div class="reg-group">
										<div class="reg-color" style="background-color:palegreen"></div>
										<div class="reg-data">
											<h1>Amoxilina - 1 caixa</h1>
											<p>Tomar 1 comprimido via oral de 8 em 8 horas por 7 dias</p>
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
							<textarea name="texto" id="texto2" class="noupper" style="height:400px;">
								<h1 style="text-align:center;">Receituário</h1>
								<p>Atesto para os devidos fins que {NOME PACIENTE} estará dispensado das atividades trabalhistas durante o período de {DIAS ATESTADO} dias a partir da data de {DATA ATESTADO}</p>
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