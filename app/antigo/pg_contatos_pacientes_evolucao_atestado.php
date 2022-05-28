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

	$_profissionais=array();
	$sql->consult($_p."colaboradores","id,nome,calendario_iniciais,foto,calendario_cor","where tipo_cro<>'' and lixo=0 order by nome asc");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_profissionais[$x->id]=$x;
	}
	$_cids=array();
	$sql->consult($_p."parametros_cids","*","where lixo=0 order by titulo asc");//"where unidades like '%,$unidade->id,%' and lixo=0 order by nome asc");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_cids[$x->id]=$x;
	}

	$selectProfissional='<select class="js-profissional"><option value="">-</option>';
	foreach($_profissionais as $p) {
	
		$aux=explode(" ",$p->nome);
		$aux[0]=strtoupper($aux[0]);
		$iniciais='';
		if($aux[0] =="DR" or $aux[0]=="DR." or $aux[0]=="DRA" or $aux[0]=="DRA.") {
			$iniciais=strtoupper(substr($aux[1],0,1));
			if(isset($aux[2])) $iniciais.=strtoupper(substr($aux[2],0,1));
		} else {
			$iniciais=strtoupper(substr($aux[0],0,1));
			if(isset($aux[1])) $iniciais.=strtoupper(substr($aux[1],0,1));
		}
											
		$selectProfissional.='<option value="'.$p->id.'" data-iniciais="'.$iniciais.'" data-iniciaisCor="'.$p->calendario_cor.'">'.utf8_encode($p->nome).'</option>';
	}
	$selectProfissional.='</select>';

	$evolucao='';
	$evolucaoAtestado='';
	if(isset($_GET['edita']) and is_numeric($_GET['edita'])) {	
		$sql->consult($_p."pacientes_evolucoes","*","where id='".$_GET['edita']."' and id_tipo=4");
		if($sql->rows) {
			$evolucao=mysqli_fetch_object($sql->mysqry);

			$sql->consult($_p."pacientes_evolucoes_atestados","*","where id_evolucao=$evolucao->id and lixo=0");
			if($sql->rows) {
				$evolucaoAtestado=mysqli_fetch_object($sql->mysqry);
			} 
		} else {
			$jsc->jAlert("Procedimento Aprovado não encontrado!","erro","document.location.href='pg_contatos_pacientes_evolucao.php?id_paciente='".$paciente->id."'");
			die();
		}
	}


	if(isset($_POST['acao'])) {


		if(is_object($evolucao)) {
			$id_evolucao=$evolucao->id;
		} else {
			// id_tipo = 4 -> Atestado
			$sql->consult($_p."pacientes_evolucoes","*","WHERE data > NOW() - INTERVAL 1 MINUTE and 
																					id_paciente=$paciente->id and
																					id_tipo=4 and  
																					id_usuario=$usr->id");	
			if($sql->rows) {
				$e=mysqli_fetch_object($sql->mysqry);
				$id_evolucao=$e->id;
			} else {
				$sql->add($_p."pacientes_evolucoes","data=now(),
														id_tipo=4,
														id_paciente=$paciente->id,
														id_usuario=$usr->id");
				$id_evolucao=$sql->ulid;
			}
		}


		$vSQLAtestado="data=now(),
						id_paciente=$paciente->id,
						id_evolucao=$id_evolucao,
						data_atestado='".invDateTime($_POST['data'])."',
						tipo='".addslashes($_POST['tipo'])."',
						objetivo='".addslashes($_POST['objetivo'])."',
						id_profissional='".addslashes($_POST['id_profissional'])."',
						atestado='".addslashes(utf8_encode($_POST['atestado']))."',
						dias='".addslashes($_POST['dias'])."'";


		if(empty($evolucaoAtestado)) {
			/*$sql->consult($_p."pacientes_evolucoes_atestados","*","WHERE data > NOW() - INTERVAL 1 MINUTE and 
																				id_paciente=$paciente->id and 
																				id_evolucao=$id_evolucao");	
			if($sql->rows) {
				$x=mysqli_fetch_object($sql->mysqry);
				$sql->update($_p."pacientes_evolucoes_atestados",$vSQLAtestado,"where id=$x->id");
			} else {*/
				$sql->add($_p."pacientes_evolucoes_atestados",$vSQLAtestado);
		//	}
		} else {
			$sql->update($_p."pacientes_evolucoes_atestados",$vSQLAtestado,"where id=$evolucaoAtestado->id");
		}


		$jsc->jAlert("Evolução salva com sucesso!","sucesso","document.location.href='pg_contatos_pacientes_evolucao.php?id_paciente=$paciente->id'");
		die();
				
		
	}

	$_tiposAtestados=array('acompanhamento'=>'Acompanhamento',
							'comparecimento'=>'Comparecimento',
							'odontologico'=>'Odontológico',
							);

	$_objetivosAtestado=array('escolar'=>'Escolar',
							'esportivo'=>'Esportivo',
							'trabalhista'=>'Trabalhista',
							);

	?>
	<section class="content">
		
		<?php
		$exibirEvolucaoNav=1;
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

				<section class="js-evolucao-adicionar" id="evolucao-atestado">
						
					<form class="form formulario-validacao" method="post">
						<input type="hidden" name="acao" value="wlib" />
						<input type="hidden" name="id_evolucao" value="<?php echo is_object($evolucao)?$evolucao->id:0;?>" />

						<fieldset>
							<legend><span class="badge">1</span> Cabeçalho do atestado</legend>

							<div class="colunas5">
								<dl>
									<dt>Data e hora</dt>
									<dd><input type="text" name="data"  class="datahora datepicker obg" value="<?php echo is_object($evolucaoAtestado)?date('d/m/Y H:i',strtotime($evolucaoAtestado->data_atestado)):date('d/m/Y H:i');?>" autocomplete="off" /></dd>
								</dl>
								<dl>
									<dt>Tipo de atestado</dt>
									<dd>
										<select name="tipo" class="obg">
											<option value="">-</option>
											<?php
											foreach($_tiposAtestados as $k=>$v) {
												echo '<option value="'.$k.'"'.((is_object($evolucaoAtestado) and $evolucaoAtestado->tipo==$k)?' selected':'').'>'.$v.'</option>';
											}
											?>
										</select>
									</dd>
								</dl>
								<dl>
									<dt>Fim do atestado</dt>
									<dd>
										<select name="objetivo" class="obg">
											<option value="">-</option>
											<?php
											foreach($_objetivosAtestado as $k=>$v) {
												echo '<option value="'.$k.'"'.((is_object($evolucaoAtestado) and $evolucaoAtestado->objetivo==$k)?' selected':'').'>'.$v.'</option>';
											}
											?>
										</select>
									</dd>
								</dl>
								<dl>
									<dt>Cirurgião Dentista</dt>
									<dd>
										<select name="id_profissional" class="obg">
											<option value="">-</option>
											<?php
											foreach($_profissionais as $v) {
												echo '<option value="'.$v->id.'"'.((is_object($evolucaoAtestado) and $evolucaoAtestado->id_profissional==$v->id)?' selected':'').'>'.utf8_encode($v->nome).'</option>';
											}
											?>	
										</select>
									</dd>
								</dl>
								<?php /*<dl>
									<dt>CID/Procedimento</dt>
									<select name="id_cid">
										<option value="">-</option>
										<?php
										foreach($_cids as $v) {
											echo '<option value="'.$v->id.'"'.((is_object($evolucaoAtestado) and $evolucaoAtestado->id_profissional==$v->id)?' selected':'').'>'.utf8_encode($v->titulo).'</option>';
										}
										?>	
									</select>
								</dl>*/?>
								<dl>
									<dt>Dias de Atestado</dt>
									<dd>
										<input type="number" name="dias" class="obg" value="<?php echo is_object($evolucaoAtestado)?$evolucaoAtestado->dias:'';?>" />
									</dd>
								</dl>
							</div>
						</fieldset>
						<fieldset>
							<legend><span class="badge">2</span> Pré-visualize e edite se necessário</legend>
							<script>
								$(function(){
									var fck_texto = CKEDITOR.replace('texto',{
						    							filebrowserUploadUrl: '/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Files',
															height: '350',
															width: '100%',
															language: 'pt-br'
														});
									CKFinder.setupCKEditor(fck_texto);
								});
							</script>
							<textarea name="atestado" id="texto" class="noupper" style="height:400px;">
								<?php
								if(is_object($evolucaoAtestado)) {
									echo utf8_encode($evolucaoAtestado->atestado);

								} else {
								?>
								<h1 style="text-align:center;">Atestado</h1>
								<p>Atesto para os devidos fins que <b><?php echo utf8_encode($paciente->nome);?></b> estará dispensado das atividades trabalhistas durante o período de {DIAS ATESTADO} dias a partir da data de {DATA ATESTADO}</p>
								<?php
								}
								?>

							</textarea>
						</fieldset>
					</form>

				</section>
				

			</div>				
		</section>
			
		</section>
		
<?php
include "includes/footer.php";
?>