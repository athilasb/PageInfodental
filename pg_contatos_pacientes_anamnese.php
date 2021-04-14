<?php
	include "includes/header.php";
	include "includes/nav.php";


	$_table=$_p."pacientes_tratamentos";
	$_page=basename($_SERVER['PHP_SELF']);


	$paciente='';
	if(isset($_GET['id_paciente']) and is_numeric($_GET['id_paciente'])) {
		$sql->consult($_p."pacientes","*","where id='".$_GET['id_paciente']."'");
		if($sql->rows) {
			$paciente=mysqli_fetch_object($sql->mysqry);
			$cnt=$paciente;
		}
	}


	$campos=explode(",","titulo");
	
	foreach($campos as $v) $values[$v]='';

	if(is_object($paciente)) {
	
		$values=$adm->values($campos,$cnt);
		$values['data']=date('d/m/Y H:i',strtotime($cnt->data));
		
	} else {
		$jsc->jAlert("Paciente não encontrado!","erro","document.location.href='pg_contatos_pacientes.php'");
		die();
	}
	?>

	<section class="content">
		
		<?php /*<header class="caminho">
			<h1 class="caminho__titulo">Pacientes <i class="iconify" data-icon="bx-bx-chevron-right"></i> Tratamento e Financeiro</h1>
			<a href="" class="caminho__tutorial button button__sec"><i class="iconify" data-icon="ic-baseline-slow-motion-video"></i> ASSISTIR TUTORIAL</a>
		</header>*/ ?>

		
		<section class="content__item">
			<?php
			require_once("includes/abaPaciente.php");
			if(!isset($_GET['form'])) {
			?>
			<a href="<?php echo $_page."?form=1&$url";?>" data-padding="0" class="filtros-acoes__button tooltip" title="Adicionar" style="float:right;margin: 0;"><i class="iconify" data-icon="ic-baseline-add"></i></a>
			<?php
			}
			?>
		</section>
			
		<?php
		if(isset($_GET['form'])) {

			$campos=explode(",","titulo");
			
			foreach($campos as $v) $values[$v]='';
			$values['procedimentos']="[]";

			$cnt='';
			if(isset($_GET['edita']) and is_numeric($_GET['edita'])) {
				$sql->consult($_table,"*","where id='".$_GET['edita']."' and lixo=0");
				if($sql->rows) {
					$cnt=mysqli_fetch_object($sql->mysqry);



					$values=$adm->values($campos,$cnt);
					$values['procedimentos']=utf8_encode($cnt->procedimentos);
				} else {
					$jsc->jAlert("Informação não encontrada!","erro","document.location.href='".$_page."'");
					die();
				}
			}
			if(isset($_POST['acao'])) {
				if($_POST['acao']=="wlib") {
					$vSQL=$adm->vSQL($campos,$_POST);
					$values=$adm->values;


					if(is_object($cnt)) {

					} else {
						if(isset($_POST['procedimentos'])  and !empty($_POST['procedimentos'])) {
							$procedimetosJSON=json_decode($_POST['procedimentos']);

							foreach($procedimetosJSON as $x) {
								//var_dump($x);
								//echo '<hr />';
							}
						}

					}
					
					$vSQL.="procedimentos='".addslashes(utf8_decode($_POST['procedimentos']))."',";
					

					if(is_object($cnt)) {
						$vSQL=substr($vSQL,0,strlen($vSQL)-1);
						$vWHERE="where id='".$cnt->id."'";
						$sql->update($_table,$vSQL,$vWHERE);
						$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='update',vsql='".addslashes($vSQL)."',vwhere='".addslashes($vWHERE)."',tabela='".$_table."',id_reg='".$cnt->id."'");
						$id_tratamento=$cnt->id;
					} else {
						$vSQL.="data=now(),id_paciente=$paciente->id";
						//echo $vSQL;die();
						$sql->add($_table,$vSQL);
						$id_tratamento=$sql->ulid;
						$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='insert',vsql='".addslashes($vSQL)."',tabela='".$_table."',id_reg='".$id_tratamento."'");
					}


					$jsc->jAlert("Informações salvas com sucesso!","sucesso","document.location.href='".$_page."?id_paciente=$paciente->id'");
					die();

				}
			}
		?>
		<form method="post" class="form formulario-validacao"  autocomplete="off" enctype="multipart/form-data">
			<input type="hidden" name="acao" value="wlib" />
			
			<section style="background:var(--cinza1); ">
				<section class="content-grid content-grid_box">
					<div class="acoes">
						<a href="<?php echo $_page."?".$url;?>" class="button button__lg button__ter"><i class="iconify" data-icon="bx-bx-left-arrow-alt"></i></a>
						<a href="javascript:;" class="button button__lg btn-submit"><i class="iconify" data-icon="bx-bx-check"></i> Salvar</a>			
					</div>
					<section class="content__item">
						<h1 class="paciente__titulo1">Plano de Tratamento</h1>

						<div class="colunas6">
							
							<dl class="dl3">
								<dt>Anamnese</dt>
								<dd>
									<select>
										<option value=""></option>
									</select>
								</dd>
							</dl>
						</div>
					</section>
				</section>

			</section>
		</form>
		<section class="content" id="boxObs" style="display: none;width:50%;">

			<header class="caminho">
				<h1 class="caminho__titulo">Plano de Tratamento <i class="iconify" data-icon="bx-bx-chevron-right"></i> <strong>Observações</strong></h1>
			</header>

			<section class="content-grid">

				<section class="content__item">

					
					<input type="hidden" class="js-boxObs-index" />
					<dl>
						<dd>
							<textarea class="js-boxObs-obs"></textarea>
						</dd>
					</dl>

					<div class="acoes">
						<a href="javascript:;" class="button button__lg js-boxObs-salvar"><i class="iconify" data-icon="bx-bx-check"></i> Salvar</a>			
					</div>

				</section>
			</section>
		</section>
		<?php
		} else {
		?>
		<form method="post" class="form formulario-validacao"  autocomplete="off" enctype="multipart/form-data">
			<section style="background:var(--cinza1); padding:2rem 0;">

				<section class="content-grid content-grid_box">
				<?php
				$where="WHERE lixo=0 order by data asc";
				$sql->consult($_table,"*",$where);
				if($sql->rows==0) {
				?>
				<center>Nenhum tratamento cadastrado para este paciente</center>
				<?php
				} else {
					while($x=mysqli_fetch_object($sql->mysqry)) {
				?>
				<section class="content__item registros" onclick="document.location.href='<?php echo "$_page?form=1&edita=$x->id&$url";?>'" style="cursor: pointer;">
					<h1 class="paciente__titulo1"><?php echo utf8_encode($x->titulo);?></h1>
				</section>
				<?php		
					}
				}
				?>
				</section>

			</section>

		</form>	
		<?php
		}
		?>
	</section>

<?php
	include "includes/footer.php";
?>