<?php
	$paciente='';
	if(isset($_GET['id_paciente']) and is_numeric($_GET['id_paciente'])) {
		$sql->consult($_p."pacientes","*","where id='".$_GET['id_paciente']."' and lixo=0");
		if($sql->rows) {
			$paciente=mysqli_fetch_object($sql->mysqry);
		}
	}

	if(empty($paciente)) {
		$jsc->jAlert("Paciente não encontrado!","erro","document.location.href='pg_pacientes.php?$url'");
		die();
	}
?>
<header class="header">
	<div class="header__content content">
		<div class="header__inner1">
			<section class="header-profile">
				<?php
					$thumb="img/ilustra-perfil.png";
					if(is_object($paciente)) {
						if(!empty($paciente->foto_cn)) {
							$image=$_cloudinaryURL.'c_thumb,w_100/'.$paciente->foto_cn;
							$thumb=$_cloudinaryURL.'c_thumb,w_100/'.$paciente->foto_cn;
						} else if(!empty($paciente->foto)) {
							$thumb=$_wasabiURL."arqs/clientes/".$paciente->id.".jpg";
						}
					}
				?>
				<img src="<?php echo $thumb;?>" alt="" width="60" height="60" class="header-profile__foto" />
				<div class="header-profile__inner1">
					<h1><?php echo utf8_encode($paciente->nome);?></h1>
					<div>
						<p><?php echo isset($_codigoBI[$paciente->codigo_bi])?$_codigoBI[$paciente->codigo_bi]:"";?></p>
						<p><?php echo $paciente->data_nascimento!="0000-00-00"?idade($paciente->data_nascimento)." anos":"";?></p>
						<p style="display:flex">
							Periodicidade: <div class="js-paciente-periodicidade" style="marigin:15px;"><?php echo isset($_pacientesPeriodicidade[$paciente->periodicidade])?$_pacientesPeriodicidade[$paciente->periodicidade]:'-';?></div>
								<select style="margin:5px;display:none;" class="js-select-periodicidade" data-periodicidade="<?php echo $paciente->periodicidade;?>">
									<option value="">-</option>
									<?php
									foreach($_pacientesPeriodicidade as $k=>$v) {
										echo '<option value="'.$k.'">'.$v.'</option>';
									}
									?>
								</select>

							<a href="javascript:;" class="js-btn-periodicidade"> <i class="iconify" data-icon="ph:pencil-line-fill"></i></a>
						</p>
					</div>
				</div>
			</section>
			
			<section class="tab">
				<a href="pg_pacientes_resumo.php?id_paciente=<?php echo $paciente->id;?>"<?php echo $_page=="pg_pacientes_resumo.php"?' class="active"':'';?>>Resumo</a>

				<?php
				$_pagesFichaDoPaciente=array('pg_pacientes_prontuario.php','pg_pacientes_financeiro2.php','pg_pacientes_planosdetratamento.php','pg_pacientes_planosdetratamento_form.php');
				?>
				<a href="pg_pacientes_prontuario.php?id_paciente=<?php echo $paciente->id;?>"<?php echo in_array($_page,$_pagesFichaDoPaciente)?' class="active"':'';?>>Ficha do Paciente</a>

				<a href="pg_pacientes_financeiro.php?id_paciente=<?php echo $paciente->id;?>"<?php echo $_page=="pg_pacientes_financeiro.php"?' class="active"':'';?>>Financeiro Paciente</a>

				<?php /*<a href="pg_pacientes_prontuario.php?id_paciente=<?php echo $paciente->id;?>"<?php echo $_page=="pg_pacientes_prontuario.php"?' class="active"':'';?>>Prontuário</a>
				<a href="pg_pacientes_planosdetratamento.php?id_paciente=<?php echo $paciente->id;?>"<?php echo ($_page=="pg_pacientes_planosdetratamento.php" or $_page=="pg_pacientes_planosdetratamento_form.php")?' class="active"':'';?>>Planos de Tratamento</a>
				<a href="pg_pacientes_financeiro.php?id_paciente=<?php echo $paciente->id;?>"<?php echo ($_page=="pg_pacientes_financeiro.php")?' class="active"':'';?>>Financeiro</a>*/?>

		
				<a href="pg_pacientes_dadospessoais.php?id_paciente=<?php echo $paciente->id;?>"<?php echo $_page=="pg_pacientes_dadospessoais.php"?' class="active"':'';?>>Dados Pessoais</a>
				
				<a href="pg_pacientes_arquivos.php?id_paciente=<?php echo $paciente->id;?>"<?php echo $_page=="pg_pacientes_arquivos.php"?' class="active"':'';?>>Arquivos</a>
				<a href="pg_pacientes_whatsapp.php?id_paciente=<?php echo $paciente->id;?>"<?php echo $_page=="pg_pacientes_whatsapp.php"?' class="active"':'';?>>Histórico Whatsapp</a>
			</section>
		</div>

	</div>
</header>
<script type="text/javascript">
	$(function(){
		$('.js-btn-periodicidade').click(function(){
			$(this).hide();
			$('.js-select-periodicidade').show();
			$('.js-paciente-periodicidade').hide();
			$('.js-select-periodicidade').val($('.js-select-periodicidade').attr('data-periodicidade'))
		});
		$('.js-select-periodicidade').change(function() {
			let id_paciente = <?php echo is_object($paciente)?$paciente->id:0;?>;
			let data = `ajaxHeader=pacientePeriodicidade&id_paciente=${id_paciente}&periodicidade=${$(this).val()}`;
			$.ajax({
				type:"POST",
				data:data,
				success:function(rtn) {
					if(rtn.success) {
						$('.js-paciente-periodicidade').html(`${rtn.periodicidadeHTML}`).show();
						$('.js-select-periodicidade').attr('data-periodicidade',rtn.periodicidade).hide();
						$('.js-btn-periodicidade').show();
					}
				}
			});
		});
	});
</script>