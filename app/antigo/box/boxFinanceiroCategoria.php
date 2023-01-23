<?php
	require_once("../lib/conf.php");
	$dir="../";
	require_once("../usuarios/checa.php");


	if(isset($_POST['ajax'])) {
		$sql = new Mysql();

		$rtn = array();
		if($_POST['ajax']=="anamnesePersistir") {

			$anamnese='';
			if(isset($_POST['id_anamnese']) and is_numeric($_POST['id_anamnese'])) {
				$sql->consult($_p."parametros_anamnese","*","where id='".$_POST['id_anamnese']."'");
				if($sql->rows) {
					$anamnese=mysqli_fetch_object($sql->mysqry);
				}
			}

			$vSQL="titulo='".utf8_decode((addslashes($_POST['titulo'])))."'";
			if(isset($_POST['perguntas']) and !empty($_POST['perguntas'])) {
				$vSQL.=",perguntas='".utf8_decode($_POST['perguntas'])."'";
			}

			if(is_object($anamnese)) {
				$vWHERE="where id=$anamnese->id";
				$sql->update($_p."parametros_anamnese",$vSQL,$vWHERE);
				$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='update',vsql='".addslashes($vSQL)."',vwhere='".addslashes($vWHERE)."',tabela='".$_p."parametros_anamnese',id_reg='".$anamnese->id."'");
				$id_reg=$anamnese->id;
			} else {
				$sql->add($_p."parametros_anamnese",$vSQL);
				$id_reg=$sql->ulid;
				$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='insert',vsql='".addslashes($vSQL)."',tabela='".$_p."parametros_anamnese"."',id_reg='".$sql->ulid."'");
			}

			$_tipos = array(
				'Nota (0 ou 10)' => 'nota',
				'Sim / Não' => 'simnao',
				'Sim / Não / Texto' => 'simnaotexto',
				'Texto' => 'texto'
			);

			$_obg = array('Sim' => 1, 'Não' => 0);	
			$_alert = array('Alerta se Resposta SIM' => 'sim', 'Alerta se Resposta NÃO' => 'nao', 'Sem Alerta' => 'nenhum');

			$id_anamnese=$id_reg;
			$sql->update($_p."parametros_anamnese_formulario","lixo=1","where id_anamnese=$id_anamnese");
			if(isset($_POST['perguntas']) and !empty($_POST['perguntas'])) {
				$perguntas=json_decode($_POST['perguntas']);

				foreach($perguntas as $v) {

					$vsql="id_anamnese='$v->id_anamnese',pergunta='".utf8_decode(addslashes($v->pergunta))."',tipo='".$_tipos[$v->tipo]."',alerta='".$_alert[$v->alerta]."',obrigatorio='".$_obg[$v->obrigatorio]."',lixo=0";

					if(isset($v->id_pergunta) and is_numeric($v->id_pergunta)) {
						$sql->consult($_p."parametros_anamnese_formulario","*","where id=$v->id_pergunta and id_anamnese=$id_anamnese");
						if($sql->rows) {
							$x=mysqli_fetch_object($sql->mysqry);
							$sql->update($_p."parametros_anamnese_formulario",$vsql,"where id=$x->id");
						} else {
							$sql->add($_p."parametros_anamnese_formulario",$vsql);
						}
					} else {
						$sql->add($_p."parametros_anamnese_formulario",$vsql);
					} 
				}
			}

			$rtn=array('success'=>true,'id_anamnese'=>$id_anamnese);
		} else if($_POST['ajax']=="anamneseRemover") {
			if(isset($_POST['id_anamnese']) and is_numeric($_POST['id_anamnese'])) {
				$sql->consult($_p."parametros_anamnese","*","where id='".$_POST['id_anamnese']."'");
				if($sql->rows) {
					$anamnese=mysqli_fetch_object($sql->mysqry);
				}
			}

			if(isset($anamnese) and is_object($anamnese)) {
				$vSQL="lixo=1";
				$vWHERE="where id=$anamnese->id";
				$sql->update($_p."parametros_anamnese",$vSQL,$vWHERE);

				$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='update',vwhere='".addslashes($vWHERE)."',vsql='".addslashes($vSQL)."',tabela='".$_p."parametros_anamnese',id_reg='".$anamnese->id."'");

				$rtn=array('success'=>true);

			} else {
				$rtn=array('success'=>false,'error'=>'Anamnese não encontrada');
			}
		} else if($_POST['ajax']=="perguntasListar") {
			$anamnese='';
			if(isset($_POST['id_anamnese']) and is_numeric($_POST['id_anamnese'])) {
				$sql->consult($_p."parametros_anamnese","*","where id='".addslashes($_POST['id_anamnese'])."' and lixo=0");
				if($sql->rows) {
					$anamnese=mysqli_fetch_object($sql->mysqry);
				}
			}

			$perguntas=array();
			if(is_object($anamnese)) {
				$sql->consult($_p."parametros_anamnese_formulario","*","WHERE id_anamnese='".$anamnese->id."' and lixo=0");
				if($sql->rows) {
					while($x=mysqli_fetch_object($sql->mysqry)) {
						$perguntas[]=array('id_pergunta' =>$x->id,
											'id_anamnese' =>$x->id_anamnese,
											'pergunta' =>utf8_encode((addslashes($x->pergunta))),
											'tipo' => isset($_avaliacaoTipos[$x->tipo])?$_avaliacaoTipos[$x->tipo]:"-",
											'obrigatorio' => isset($_obrigatorio[$x->obrigatorio])?$_obrigatorio[$x->obrigatorio]:"-",
											'alerta' => isset($_alerta[$x->alerta])?$_alerta[$x->alerta]:"-");
					}
				} 
				$rtn=array('success'=>true,'perguntas'=>$perguntas);
			} else {
				$rtn=array('success'=>false,'error'=>'Anamnese não definida!');
			}
		} else if($_POST['ajax']=='perguntaRemover') {

			$anamnese='';
			if(isset($_POST['id_anamnese']) and is_numeric($_POST['id_anamnese'])) {
				$sql->consult($_p."parametros_anamnese","*","where id='".$_POST['id_anamnese']."'");
				if($sql->rows) {
					$anamnese=mysqli_fetch_object($sql->mysqry);
				}
			}

			if(is_object($anamnese)) {
				$pergunta='';
				if(isset($_POST['id_pergunta']) and is_numeric($_POST['id_pergunta'])) {
					$sql->consult($_p."parametros_anamnese_formulario","*","where id='".$_POST['id_pergunta']."' and id_anamnese='".$anamnese->id."'");
					if($sql->rows) {
						$pergunta=mysqli_fetch_object($sql->mysqry);
					}
				}
				if(is_object($pergunta)) {

					$sql->update($_p."parametros_anamnese_formulario","lixo=$usr->id","where id=$pergunta->id and id_anamnese=$anamnese->id");

					$rtn=array('success'=>true);
				} else {
					$rtn=array("success"=>false,"error"=>"Pergunta não encontrada!");
				}
			} else {
				$rtn=array("success"=>false,"error"=>"Pergunta não encontrada!");
			}
		} else if($_POST['ajax']=="persistirOrdem") {
			if(isset($_POST['ordem']) and !empty($_POST['ordem'])) {
				$ordem=explode(",",$_POST['ordem']);
				if(is_array($ordem) and count($ordem)>0) {
					$aux=1;
					foreach($ordem as $idItem) {
						if(is_numeric($idItem)) {
							$sql->update($_p."parametros_anamnese_formulario","ordem=$aux","where id=$idItem");
							$aux++;
						}
					}
					$rtn=array('success'=>true);
				}
			} else {
				$rtn=array('error'=>'Ordem não definida!');
			}
		}

		header("Content-type: application/json");
		echo json_encode($rtn);
		die();
	}

	$campos=explode(",","titulo");
	foreach($campos as $v) $values[$v]='';

	$jsc = new Js();
	$anamnese='';
	$perguntas=array();
	if(isset($_GET['id_anamnese']) and is_numeric($_GET['id_anamnese'])) {
		$sql->consult($_p."parametros_anamnese","*","where id='".$_GET['id_anamnese']."'");
		if($sql->rows) {
			$anamnese=mysqli_fetch_object($sql->mysqry);

			$sql->consult($_p."parametros_anamnese_formulario","*","where id_anamnese=$anamnese->id and lixo=0 order by ordem asc");
			if($sql->rows) {
				while($x=mysqli_fetch_object($sql->mysqry)) {
					$perguntas[]=array('id_pergunta' =>$x->id,
										'id_anamnese' =>$x->id_anamnese,
										'pergunta' =>utf8_encode($x->pergunta),
										'tipo' => isset($_avaliacaoTipos[$x->tipo])?$_avaliacaoTipos[$x->tipo]:"-",
										'obrigatorio' => isset($_obrigatorio[$x->obrigatorio])?$_obrigatorio[$x->obrigatorio]:"-",
										'alerta' => isset($_alerta[$x->alerta])?$_alerta[$x->alerta]:"-");
				}
 			} 

			foreach($campos as $v) {
				$values[$v]=utf8_encode($anamnese->$v);
			}
		}
	}
?>
<script>
	
</script>
<section class="modal" style="height:auto; width:950px;">

	<header class="modal-header">
		<div class="filtros">
			<div class="filter">
				<div class="filter-group">
					<div class="filter-button">
						<a href="javascript:$.fancybox.close();"><i class="iconify" data-icon="bx-bx-left-arrow-alt"></i></a>
					</div>
				</div>
			</div>
			<?php
				if(empty($anamnese)) {
			?>
			<div class="filtros-acoes filter-button">
				<a href="javascript:;" class="azul js-salvar"><i class="iconify" data-icon="bx-bx-check"></i><span>Salvar</span></a>
			</div>
			<?php
				} else {
			?>
			
			
			<div class="filtros-acoes filter-button">
				<a href="javascript:;" class="js-remover"><i class="iconify" data-icon="bx-bx-trash"></i></a>
				<a href="javascript:;" class="azul js-salvar"><i class="iconify" data-icon="bx-bx-check"></i><span>Salvar</span></a>
			</div>
			<?php
				}
			?>
		
		</div>
	</header>
	
	<article class="modal-conteudo">

		<form method="post" class="form js-form-anamnese">
			<input type="submit" style="display: none;" />
			<fieldset>
				<legend><span class="badge">1</span> Defina o título da Anamnese</legend>
				<div>
					<dl>
						<dt>Título</dt>
						<dd>
							<input type="text" name="titulo" value="<?php echo $values['titulo'];?>"  class="obg" />
						</dd>
					</dl>
				</div>
			</fieldset>

			
				
		</form>
	</article>

</section>