<?php
	include "includes/header.php";
	include "includes/nav.php";

	$_table=$_p."colaboradores_cargahoraria";
	$_page=basename($_SERVER['PHP_SELF']);

	$colaborador=$cnt='';
	if(isset($_GET['id_colaborador']) and is_numeric($_GET['id_colaborador'])) {
		$sql->consult($_p."colaboradores","*","where id='".$_GET['id_colaborador']."'");
		if($sql->rows) {
			$colaborador=mysqli_fetch_object($sql->mysqry);
		}
	}

	$sql->consult($_table,"*","WHERE id_colaborador='".$colaborador->id."' and lixo=0");
	if($sql->rows) {
		$cnt=mysqli_fetch_object($sql->mysqry);
	}

	$campos=explode(",","id_colaborador,horario,carga_semanal,atendimentopersonalizado");
	foreach($campos as $v) $values[$v]='';

	if(is_object($cnt)) {
		$values=$adm->values($campos,$cnt);
	}

	$_horarios = array(
		1 => '08:00 - 18:00',
		2 => '17:00 - 23:50'
	);

	$_cargasemanal = array(
		1 => '30',
		2 => '44'
	);

	if(isset($_POST['acao']) and $_POST['acao']=="wlib") {
		$vSQL=$adm->vSQL($campos,$_POST);
		$values=$adm->values;
		$processa=true;

		if($processa===true) {	
		
			if(is_object($cnt)) {
				$vSQL=substr($vSQL,0,strlen($vSQL)-1);
				$vWHERE="where id='".$cnt->id."'";
				$sql->update($_table,$vSQL,$vWHERE);
				$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='update',vsql='".addslashes($vSQL)."',vwhere='".addslashes($vWHERE)."',tabela='".$_table."',id_reg='".$cnt->id."'");
				$id_reg=$cnt->id;
			} else {
				$vSQL=substr($vSQL,0,strlen($vSQL)-1);
				$sql->add($_table,$vSQL);
				$id_reg=$sql->ulid;
				$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='insert',vsql='".addslashes($vSQL)."',tabela='".$_table."',id_reg='".$sql->ulid."'");
			}
			$jsc->jAlert("Informações salvas com sucesso!","sucesso","document.location.href='".$_page."?id_colaborador=".$colaborador->id."'");
			die();
		}
	}

?>
	<section class="content">
		
		<?php
		require_once("includes/abaColaborador.php");
		?>

		<form method="post" class="form formulario-validacao"  autocomplete="off" enctype="multipart/form-data">
			<input type="hidden" name="acao" value="wlib" />
			<input type="hidden" name="id_colaborador" value="<?php echo $colaborador->id;?>" />

			<section class="grid" style="padding:2rem;">
				<div class="box">
					<div class="filter">

						<div class="filter-group">
							<div class="filter-button">
								<a href="javascript:history.back(-1);"><i class="iconify" data-icon="bx-bx-left-arrow-alt"></i></a>
							</div>
						</div>

						<div class="filter-group filter-group_right">
							<div class="filter-button">
								<?php if(is_object($cnt)){?><a href="?deletaCargahoraria=<?php echo $cnt->id."&".$url;?>" class="js-deletar"><i class="iconify" data-icon="bx-bx-trash"></i></a><?php }?>
								<a href="javascript:;"><i class="iconify" data-icon="bx-bx-printer"></i></a>
								<a href="javascript:;" class="azul  btn-submit"><i class="iconify" data-icon="bx-bx-check"></i><span>salvar</span></a>
							</div>
						</div>
					</div>

					<fieldset style="margin:0;">
						<legend style="font-size: 12px;">
							<div class="filter-group">
								<div class="filter-title">
									<span class="badge">1</span> Horário de Trabalho
								</div>
							</div>
						</legend>

						<div class="colunas4">
							<dl>
								<dt>Horário</dt>
								<dd>
									<select name="horario" class="obg">
										<option value="">-</option>
										<?php
										foreach($_horarios as $k => $v) {
											echo '<option value="'.$k.'"'.(($values['horario']==$k)?' selected':'').'>'.$v.'</option>';
										}
										?>
									</select>
								</dd>
							</dl>
							<dl>
								<dt>Carga Semanal</dt>
								<dd>
									<select name="carga_semanal" class="obg">
										<option value="">-</option>
										<?php
										foreach($_cargasemanal as $k => $v) {
											echo '<option value="'.$k.'"'.(($values['carga_semanal']==$k)?' selected':'').'>'.$v.'</option>';
										}
										?>
									</select>
								</dd>
							</dl>
						</div>
					</fieldset>

					<fieldset style="margin:0;">
						<legend style="font-size: 12px;">
							<div class="filter-group">
								<div class="filter-title">
									<span class="badge">2</span> Horários de Atendimento
								</div>
							</div>
						</legend>

						<div class="colunas4">
							<dl class="dl2">
								<dt>Possui Atendimento Personalizado?</dt>
								<dd>
									<label><input type="radio" name="atendimentopersonalizado" value="1"<?php echo $values['atendimentopersonalizado']==1?" checked":"";?> /> Sim</label>
									<label><input type="radio" name="atendimentopersonalizado" value="0"<?php echo $values['atendimentopersonalizado']==0?" checked":"";?> /> Não</label>
								</dd>
							</dl>
						</div>
					</fieldset>

			</section>


		</form>
		
<?php
include "includes/footer.php";
?>