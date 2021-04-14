<?php
	if(isset($_POST['ajax'])) {
		require_once("lib/conf.php");
		require_once("usuarios/checa.php");
		$sql = new Mysql();
		$rtn = array();

		if($_POST['ajax']=="comissionamentoRemover") {
			$profissional='';
			if(isset($_POST['id_profissional']) and is_numeric($_POST['id_profissional'])) {
				$sql->consult($_p."profissionais", "*","where id='".$_POST['id_profissional']."' and lixo=0");
				if($sql->rows) {
					$profissional=mysqli_fetch_object($sql->mysqry);
				}
			}

			$comissionamento='';
			if(isset($_POST['id']) and is_numeric($_POST['id']) and is_object($profissional)) {
				$sql->consult($_p."profissionais_comissionamentopersonalizado","*","where id='".$_POST['id']."'");
				if($sql->rows) {
					$comissionamento=mysqli_fetch_object($sql->mysqry);
				}
			}

			if(is_object($profissional)) {
				if(is_object($comissionamento)) {
					$sql->update($_p."profissionais_comissionamentopersonalizado","lixo=$usr->id,lixo_data=now()","where id=$comissionamento->id");
					$rtn=array('success'=>true);
				} else {
					$rtn=array('success'=>false,'error'=>'Comissionamento não encontrado');
				}
			} else {
				$rtn=array('success'=>false,'error'=>'Profissional não encontrado!');
			}
		} else if($_POST['ajax']=="comissionamentoPersistir") {
			$profissional='';
			if(isset($_POST['id_profissional']) and is_numeric($_POST['id_profissional'])) {
				$sql->consult($_p."profissionais", "*","where id='".$_POST['id_profissional']."' and lixo=0");
				if($sql->rows) {
					$profissional=mysqli_fetch_object($sql->mysqry);
				}
			}

			$id_procedimento=(isset($_POST['id_procedimento']) and is_numeric($_POST['id_procedimento']))?$_POST['id_procedimento']:0;
			$id_plano=(isset($_POST['id_plano']) and is_numeric($_POST['id_plano']))?$_POST['id_plano']:0;
			$valor=(isset($_POST['valor']))?addslashes(valor($_POST['valor'])):0;
			$abaterCustos=(isset($_POST['abater_custos']) and $_POST['abater_custos']==1)?1:0;
			$abaterImpostos=(isset($_POST['abater_impostos']) and $_POST['abater_impostos']==1)?1:0;
			$tipo=(isset($_POST['tipo']) and !empty($_POST['tipo']))?addslashes($_POST['tipo']):'valor';
			$abaterTaxas=(isset($_POST['abater_taxas']) and $_POST['abater_taxas']==1)?1:0;

			if($id_procedimento==0) {
				$rtn=array('success'=>false,'error'=>'Procedimento não definido!');
			} else if($id_plano==0) {
				$rtn=array('success'=>false,'error'=>'Plano não definido!');
			} else {

				$comissionamento='';
				if(is_object($profissional)) {
					$sql->consult($_p."profissionais_comissionamentopersonalizado","*","where id_profissional='".$profissional->id."' and id_procedimento=$id_procedimento and id_plano=$id_plano and lixo=0");
					if($sql->rows) {
						$comissionamento=mysqli_fetch_object($sql->mysqry);
					}
				}

				if(is_object($profissional)) {

					$vsql="id_profissional=$profissional->id,
							id_procedimento=$id_procedimento,
							id_plano=$id_plano,
							valor='".$valor."',
							tipo='".$tipo."',
							abater_custos='".$abaterCustos."',
							abater_impostos='".$abaterImpostos."',
							abater_taxas='".$abaterTaxas."'";


					if(is_object($comissionamento)) {
						$vsql.=",alteracao_data=now(),id_alteracao=$usr->id";
						$sql->update($_p."profissionais_comissionamentopersonalizado",$vsql,"where id=$comissionamento->id");
						$id_reg=$comissionamento->id;
					} else {
						$vsql.=",data=now(),id_usuario=$usr->id";
						$sql->add($_p."profissionais_comissionamentopersonalizado",$vsql);
						$id_reg=$sql->ulid;
					}


					$_planos=array();
					$sql->consult($_p."parametros_planos","*","where lixo=0 order by titulo asc");
					while($x=mysqli_fetch_object($sql->mysqry)) {
						$_planos[$x->id]=$x;
					}

					$_procedimentos=array();
					$sql->consult($_p."parametros_procedimentos","*","where lixo=0 order by titulo asc");
					while($x=mysqli_fetch_object($sql->mysqry)) {
						$_procedimentos[$x->id]=$x;
					}

					$comissionamentoPersonalizado=array();
					$sql->consult($_p."profissionais_comissionamentopersonalizado","*","where id_profissional=$profissional->id and lixo=0");
					while($x=mysqli_fetch_object($sql->mysqry)) {
						$comissionamentoPersonalizado[]=array('id'=>$x->id,
																'id_profissional'=>$x->id_profissional,
																'id_plano'=>$x->id_plano,
																'id_procedimento'=>$x->id_procedimento,
																'plano'=>(isset($_planos[$x->id_plano])?utf8_encode($_planos[$x->id_plano]->titulo):'-'),
																'procedimento'=>isset($_procedimentos[$x->id_procedimento])?utf8_encode($_procedimentos[$x->id_procedimento]->titulo):"-",
																'tipo'=>$x->tipo,
																'valor'=>number_format($x->valor,2,",","."),
																'abaterCustos'=>$x->abater_custos,
																'abaterTaxas'=>$x->abater_taxas,
																'abaterImpostos'=>$x->abater_impostos);
					}

					$rtn=array('success'=>true,'comissionamento'=>$comissionamentoPersonalizado);
				} else {
					$rtn=array('success'=>false,'error'=>'Profissional não encontrado!');
				}
			}
		} else if($_POST['ajax']=="horariosPersistir") {
			$unidade='';
			if(isset($_POST['id_unidade']) and is_numeric($_POST['id_unidade']) and isset($_unidades[$_POST['id_unidade']])) {
				$unidade=$_unidades[$_POST['id_unidade']];
			}

			$cadeira='';
			if(isset($_POST['id_cadeira']) and is_numeric($_POST['id_cadeira']) and is_object($unidade)) {
				$sql->consult($_p."parametros_cadeiras","*","where id='".$_POST['id_cadeira']."' and  id_unidade=$unidade->id");
				if($sql->rows) {
					$cadeira=mysqli_fetch_object($sql->mysqry);
				}
			}

			$profissional='';
			if(isset($_POST['id_profissional']) and is_numeric($_POST['id_profissional']) and is_object($unidade)) {
				$sql->consult($_p."profissionais","*","where id='".$_POST['id_profissional']."'");
				if($sql->rows) {
					$profissional=mysqli_fetch_object($sql->mysqry);
				}
			}

			$inicio=(isset($_POST['inicio']) and !empty($_POST['inicio']))?addslashes($_POST['inicio']):'';
			$fim=(isset($_POST['fim']) and !empty($_POST['fim']))?addslashes($_POST['fim']):'';
			$dia=(isset($_POST['dia']) and is_numeric($_POST['dia']))?addslashes($_POST['dia']):'';

			if(empty($unidade)) $rtn=array('success'=>false,'error'=>'Unidade não definida!');
			else if(empty($cadeira)) $rtn=array('success'=>false,'error'=>'Cadeira não definida!');
			else if(empty($profissional)) $rtn=array('success'=>false,'error'=>'Profissional não definido!');
			else if(empty($inicio)) $rtn=array('success'=>false,'error'=>'Cadeira não definida!');
			else if(empty($fim)) $rtn=array('success'=>false,'error'=>'Cadeira não definida!');
			else if(empty($dia) and $dia!=0) $rtn=array('success'=>false,'error'=>'Dia da semana não definido!');
			else {
				$vsql="id_unidade=$unidade->id,
						id_cadeira=$cadeira->id,
						id_profissional=$profissional->id,
						inicio='".$inicio."',
						dia='".$dia."',
						fim='".$fim."'";

				if(isset($_POST['id']) and is_numeric($_POST['id']) and $_POST['id']>0) {
					$sql->consult($_p."profissionais_horarios","*", "where id='".$_POST['id']."' and id_profissional=$profissional->id and id_unidade=$unidade->id and lixo=0");
					if($sql->rows) {
						$x=mysqli_fetch_object($sql->mysqry);
						$vsql.=",id_alteracao=$usr->id,alteracao_data=now()";
						$sql->update($_p."profissionais_horarios",$vsql,"where id=$x->id");
						$rtn=array('success'=>true);
					} else $rtn=array('success'=>false,'error'=>'Horário não encontrado');
				} else {
					$vsql.=",id_usuario=$usr->id,data=now()";
					$sql->add($_p."profissionais_horarios",$vsql);
					$rtn=array('success'=>true);
				}
			}
		} else if($_POST['ajax']=="horariosListar") {

			$profissional='';
			if(isset($_POST['id_profissional']) and is_numeric($_POST['id_profissional'])) {
				$sql->consult($_p."profissionais","*","where id='".$_POST['id_profissional']."'");
				if($sql->rows) {
					$profissional=mysqli_fetch_object($sql->mysqry);
				}
			}

			$_cadeiras=array();
			$sql->consult($_p."parametros_cadeiras","*","where lixo=0");
			while($x=mysqli_fetch_object($sql->mysqry)) $_cadeiras[$x->id]=$x;


			if(empty($profissional)) $rtn=array('success'=>false,'error'=>'Profissional não definido!');
			else {
				$horarios=array();
				$sql->consult($_p."profissionais_horarios","*,date_format(inicio,'%H:%i') as inicio,
															date_format(fim,'%H:%i') as fim","where id_profissional=$profissional->id and lixo=0");
				if($sql->rows) {
					while($x=mysqli_fetch_object($sql->mysqry)) {
						if(isset($_cadeiras[$x->id_cadeira])) {
							$cadeira=$_cadeiras[$x->id_cadeira];
							$horarios[$x->id_unidade][$x->dia][]=array('id'=>$x->id,
																'id_cadeira'=>$x->id_cadeira,
																'cadeira'=>utf8_encode($cadeira->titulo),
																'dia'=>$x->dia,
																'inicio'=>$x->inicio,
																'fim'=>$x->fim
															);
						}
					}

				}
				$rtn=array('success'=>true,'horarios'=>$horarios);
			}
		} else if($_POST['ajax']=="horariosEditar") {
			$horario='';
			if(isset($_POST['id_horario']) and is_numeric($_POST['id_horario'])) {
				$sql->consult($_p."profissionais_horarios","*,date_format(inicio,'%H:%i') as inicio,
																date_format(fim,'%H:%i') as fim","where id='".$_POST['id_horario']."'");
				if($sql->rows) {
					$horario=mysqli_fetch_object($sql->mysqry);
				}
			}

			if(is_object($horario)) {

				$rtn=array('success'=>true,
							'id'=>$horario->id,
							'id_cadeira'=>$horario->id_cadeira,
							'id_unidade'=>$horario->id_unidade,
							'inicio'=>$horario->inicio,
							'fim'=>$horario->fim,
							'dia'=>$horario->dia);
			} else {
				$rtn=array('success'=>false,'error'=>'Horário não encontrado!');
			}
		} else if($_POST['ajax']=="horariosRemover") {
			$horario='';
			if(isset($_POST['id_horario']) and is_numeric($_POST['id_horario'])) {
				$sql->consult($_p."profissionais_horarios","*,date_format(inicio,'%H:%i') as inicio,
																date_format(fim,'%H:%i') as fim","where id='".$_POST['id_horario']."'");
				if($sql->rows) {
					$horario=mysqli_fetch_object($sql->mysqry);
				}
			}

			if(is_object($horario)) {
				$sql->update($_p."profissionais_horarios","lixo=$usr->id,lixo_data=now()","where id=$horario->id");

				$rtn=array('success'=>true);
			} else {
				$rtn=array('success'=>false,'error'=>'Horário não encontrado!');
			}
		}

		header("Content-type: application/json");
		echo json_encode($rtn);
		die();
	}
	
	$title="";
	include "includes/header.php";
	include "includes/nav.php";

	if($usr->tipo!="admin" and !in_array("parametros",$_usuariosPermissoes)) {
		$jsc->jAlert("Você não tem permissão para acessar esta área!","erro","document.location.href='dashboard.php'");
		die();
	}

	$dias=explode(",","Domingo,Segunda,Terça,Quarta,Quinta,Sexta,Sábado");
	$values=$adm->get($_GET);
?>
<section class="content">
			
	<?php
	require_once("includes/abaConfiguracoes.php");
	?>

	<?php
	$_table=$_p."profissionais";
	$_page=basename($_SERVER['PHP_SELF']);

	$_width=200;
	$_height="";
	$_dir="arqs/profissionais/";

	$_planos=array();
	$sql->consult($_p."parametros_planos","*","where lixo=0 order by titulo asc");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_planos[$x->id]=$x;
	}

	$_procedimentos=array();
	$sql->consult($_p."parametros_procedimentos","*","where lixo=0 order by titulo asc");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_procedimentos[$x->id]=$x;
	}

	$_cadeiras=array();
	$sql->consult($_p."parametros_cadeiras","*","where lixo=0 order by titulo asc");
	while($x=mysqli_fetch_object($sql->mysqry)) $_cadeiras[$x->id_unidade][]=$x;

	if(isset($_GET['form'])) {
		$cnt='';
		$campos=explode(",","nome,cpf,rg,data_nascimento,conselho_numero,conselho_uf,sexo,email,telefone,celular,cep,endereco,numero,complemento,bairro,estado,id_cidade,cidade,calendario_iniciais,calendario_cor");
		
		foreach($campos as $v) $values[$v]='';
		$values['calendario_cor']="#c18c6a";
		
		if(isset($_GET['edita']) and is_numeric($_GET['edita'])) {
			$sql->consult($_table,"*","where id='".$_GET['edita']."'");
			if($sql->rows) {
				$cnt=mysqli_fetch_object($sql->mysqry);
				
				$comissionamentoPersonalizado=array();
				$sql->consult($_p."profissionais_comissionamentopersonalizado","*","where id_profissional=$cnt->id and lixo=0");
				while($x=mysqli_fetch_object($sql->mysqry)) {
					$comissionamentoPersonalizado[]=array('id'=>$x->id,
															'id_profissional'=>$x->id_profissional,
															'id_plano'=>$x->id_plano,
															'id_procedimento'=>$x->id_procedimento,
															'plano'=>(isset($_planos[$x->id_plano])?utf8_encode($_planos[$x->id_plano]->titulo):'-'),
															'procedimento'=>isset($_procedimentos[$x->id_procedimento])?utf8_encode($_procedimentos[$x->id_procedimento]->titulo):"-",
															'tipo'=>$x->tipo,
															'valor'=>number_format($x->valor,2,",","."),
															'abaterCustos'=>$x->abater_custos,
															'abaterTaxas'=>$x->abater_taxas,
															'abaterImpostos'=>$x->abater_impostos);
				}


				$values=$adm->values($campos,$cnt);
			} else {
				$jsc->jAlert("Informação não encontrada!","erro","document.location.href='".$_page."'");
				die();
			}
		}
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

				foreach($_planos as $v) {

					$tipo=isset($_POST['tipo_'.$v->id])?addslashes($_POST['tipo_'.$v->id]):0;
					$valor=isset($_POST['valor_'.$v->id])?valor(addslashes($_POST['valor_'.$v->id])):0;
					$abaterCustos=(isset($_POST['abater_custos_'.$v->id]) and $_POST['abater_custos_'.$v->id]==1)?1:0;
					$abaterImpostos=(isset($_POST['abater_impostos_'.$v->id]) and $_POST['abater_impostos_'.$v->id]==1)?1:0;
					$abaterTaxas=(isset($_POST['abater_taxas_'.$v->id]) and $_POST['abater_taxas_'.$v->id]==1)?1:0;

					$vSQLComissionamentoGeral="id_profissional=$id_reg,
												id_plano=$v->id,
												tipo='".$tipo."',
												valor='".$valor."',
												abater_custos='".$abaterCustos."',
												abater_impostos='".$abaterImpostos."',
												abater_taxas='".$abaterTaxas."'";

					$sql->consult($_table."_comissionamentogeral","*","where id_profissional=$id_reg and id_plano=$v->id and lixo=0");
					if($sql->rows) {
						$x=mysqli_fetch_object($sql->mysqry);
						$sql->update($_table."_comissionamentogeral",$vSQLComissionamentoGeral,"where id=$x->id");
					} else {
						$sql->add($_table."_comissionamentogeral",$vSQLComissionamentoGeral);
					}
				}

				$sql->update($_p."profissionais_comissionamentopersonalizado","lixo=1","where id_profissional=$id_reg");

				if(isset($_POST['comissionamentoPersonalizado'])) {
					$obj=json_decode($_POST['comissionamentoPersonalizado']);
				//	var_dump($obj);
					if(is_array($obj)) {
						foreach($obj as $v) {
							$vSQLCP="id_profissional=$id_reg,
									id_procedimento=$v->id_procedimento,
									id_plano=$v->id_plano,
									tipo='".addslashes(	$v->tipo)."',
									valor='".valor($v->valor)."',
									abater_custos='".$v->abaterCustos."',
									abater_impostos='".$v->abaterImpostos."',
									abater_taxas='".$v->abaterTaxas."',
									lixo=0";

							$cp='';
							//echo $vSQLCP."<BR>";
							$sql->consult($_p."profissionais_comissionamentopersonalizado","*","where id_profissional=$id_reg and 
																										id_plano='".addslashes($v->id_plano)."' and 
																										id_procedimento='".addslashes($v->id_procedimento)."'");
							if($sql->rows) {
								$cp=mysqli_fetch_object($sql->mysqry);
							}

							if(is_object($cp)) {
								$sql->update($_p."profissionais_comissionamentopersonalizado",$vSQLCP,"where id=$cp->id");
							} else {
								$sql->add($_p."profissionais_comissionamentopersonalizado",$vSQLCP);
							}
							
						}
					}
				}
				//die();

				$msgErro='';
				if(isset($_FILES['foto']) and !empty($_FILES['foto']['tmp_name'])) {
					$up=new Uploader();
					$up->uploadCorta("Foto",$_FILES['foto'],"",5242880*2,$_width,'',$_dir,$id_reg);

					if($up->erro) {
						$msgErro=$up->resul;
					} else {
						$ext=$up->ext;
						$vSQL="foto='".$ext."'";
						$vWHERE="where id='".$id_reg."'";
						$sql->update($_table,$vSQL,$vWHERE);
						$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='update',vsql='".addslashes($vSQL)."',tabela='".$_table."',id_reg='".$id_reg."'");
					}
				}
				if(!empty($msgErro)) {
					$jsc->jAlert($msgErro,"erro","");
				} else {
					$jsc->jAlert("Informações salvas com sucesso!","sucesso","document.location.href='".$_page."?".$url."'");
					die();
				}
			}
		}	
	?>
	
	<div class="filtros">
		<h1 class="filtros__titulo">Cirurgiões Dentistas</h1>
		<div class="filtros-acoes">
			<a href="<?php echo $_page."?".$url;?>"><i class="iconify" data-icon="bx-bx-left-arrow-alt"></i></a>
			<?php
			if(is_object($cnt)) {
			?>		
			<a data-fancybox data-type="ajax" data-src="ajax/log.php?table=<?php echo $_table;?>&id=<?php echo $cnt->id;?>" href="javascript:;"><i class="iconify" data-icon="bx-bx-history"></i></a>
			<?php	
			}
			?>
			<a href="javascript:;" class="principal btn-submit"><i class="iconify" data-icon="bx-bx-check"></i></a>
			<?php if(is_object($cnt) and $usr->tipo=="admin") { ?>
			<a class="sec js-deletar" href="<?php echo $_page;?>?deleta=<?php echo $cnt->id."&".$url;?>"><i class="iconify" data-icon="bx-bx-trash"></i></a>
			<?php } ?>
		</div>
	</div>

	<script src="js/jquery.colorpicker.js"></script>
	<script>
		var _cidade='<?php echo $values['cidade'];?>';
		var _cidadeID='<?php echo empty($values['id_cidade'])?0:$values['id_cidade'];?>';
		$(function(){

			$('input.money').maskMoney({symbol:'', allowZero:true, showSymbol:true, thousands:'.', decimal:',', symbolStay: true});
			
			$('input[name=calendario_cor]').ColorPicker({
				color: '<?php echo $values['calendario_cor'];?>',
				onShow: function (colpkr) {$(colpkr).fadeIn(500);return false;},
				onHide: function (colpkr) {$(colpkr).fadeOut(500);return false;},
				onChange: function (hsb, hex, rgb) {console.log(hex);$('input[name=calendario_cor]').css('backgroundColor', '#' + hex).val('#'+hex);}
			});
			
			$('input[name=calendario_cor]').css('backgroundColor','<?php echo $values['calendario_cor'];?>');

		})
	</script>

	<section class="grid">
		<div class="box">
			<form method="post" class="form formulario-validacao"  autocomplete="off" enctype="multipart/form-data">
				<input type="hidden" name="acao" value="wlib" />

				<fieldset>
					<legend>Dados do Profissional</legend>

					<div class="colunas5">
						<dl class="dl2">
							<dt>Nome</dt>
							<dd>
								<input type="text" name="nome" value="<?php echo $values['nome'];?>" class="obg" />
							</dd>
						</dl>
						<dl class="">
							<dt>Iniciais</dt>
							<dd>
								<input type="text" name="calendario_iniciais" value="<?php echo $values['calendario_iniciais'];?>" class="obg" maxlength="2" />
							</dd>
						</dl>
						<dl class="">
							<dt>Cor Calendário</dt>
							<dd>
								<input type="text" name="calendario_cor" value="<?php echo $values['calendario_cor'];?>" class="obg" />
							</dd>
						</dl>
						<dl>
							<dt>Data Nascimento</dt>
							<dd>
								<input type="text" name="data_nascimento" value="<?php echo $values['data_nascimento'];?>" class="data" />
							</dd>
						</dl>
					</div>
					<div class="colunas5">
						<dl>
							<dt>Sexo</dt>
							<dd>
								<select name="sexo" class="obg">
									<option value="">-</option>
									<option value="M"<?php echo $values['sexo']=='M'?' selected':'';?>>Masculino</option>
									<option value="F"<?php echo $values['sexo']=='F'?' selected':'';?>>Feminino</option>
								</select>
							</dd>
						</dl>
						<dl>
							<dt>CPF</dt>
							<dd>
								<input type="text" name="cpf" value="<?php echo $values['cpf'];?>" class="cpf" />
							</dd>
						</dl>
						<dl>
							<dt>RG</dt>
							<dd>
								<input type="text" name="rg " value="<?php echo $values['rg'];?>" class="" />
							</dd>
						</dl>
						<dl>
							<dt>Número do Conselho</dt>
							<dd><input type="text" name="conselho_numero" value="<?php echo $values['conselho_numero'];?>" /></dd>
						</dl>
						<dl>
							<dt>UF do Conselho</dt>
							<dd>
								<?php $inEstado=strtoupperWLIB($values['conselho_uf']);?><select name="conselho_uf"><option value="">SELECIONE</option><option value="AC"<?php echo $inEstado=="AC"?" selected":"";?>>ACRE</option><option value="AL"<?php echo $inEstado=="AL"?" selected":"";?>>ALAGOAS</option><option value="AM"<?php echo $inEstado=="AM"?" selected":"";?>>AMAZONAS</option><option value="AP"<?php echo $inEstado=="AP"?" selected":"";?>>AMAPÁ</option><option value="BA"<?php echo $inEstado=="BA"?" selected":"";?>>BAHIA</option><option value="CE"<?php echo $inEstado=="CE"?" selected":"";?>>CEARÁ</option><option value="DF"<?php echo $inEstado=="DF"?" selected":"";?>>DISTRITO FEDERAL</option><option value="ES"<?php echo $inEstado=="ES"?" selected":"";?>>ESPÍRITO SANTO</option><option value="GO"<?php echo $inEstado=="GO"?" selected":"";?>>GOIÁS</option><option value="MA"<?php echo $inEstado=="MA"?" selected":"";?>>MARANHÃO</option><option value="MT"<?php echo $inEstado=="MT"?" selected":"";?>>MATO GROSSO</option><option value="MS"<?php echo $inEstado=="MS"?" selected":"";?>>MATO GROSSO DO SUL</option><option value="MG"<?php echo $inEstado=="MG"?" selected":"";?>>MINAS GERAIS</option><option value="PA"<?php echo $inEstado=="PA"?" selected":"";?>>PARÁ</option><option value="PB"<?php echo $inEstado=="PB"?" selected":"";?>>PARAÍBA</option><option value="PR"<?php echo $inEstado=="PR"?" selected":"";?>>PARANÁ</option><option value="PE"<?php echo $inEstado=="PE"?" selected":"";?>>PERNANBUMCO</option><option value="PI"<?php echo $inEstado=="PI"?" selected":"";?>>PIAUÍ</option><option value="RJ"<?php echo $inEstado=="RJ"?" selected":"";?>>RIO DE JANEIRO</option><option value="RN"<?php echo $inEstado=="RN"?" selected":"";?>>RIO GRANDE DO NORTE</option><option value="RO"<?php echo $inEstado=="RO"?" selected":"";?>>RONDÔNIA</option><option value="RS"<?php echo $inEstado=="RS"?" selected":"";?>>RIO GRANDE DO SUL</option><option value="RR"<?php echo $inEstado=="RR"?" selected":"";?>>RORAIMA</option><option value="SC"<?php echo $inEstado=="SC"?" selected":"";?>>SANTA CATARINA</option><option value="SE"<?php echo $inEstado=="SE"?" selected":"";?>>SERGIPE</option><option value="SP"<?php echo $inEstado=="SP"?" selected":"";?>>SÃO PAULO</option><option value="TO"<?php echo $inEstado=="TO"?" selected":"";?>>TOCANTINS</option></select>
							</dd>
						</dl>
					</div>

					<div class="colunas4">
						<dl class="dl2">

							<?php
							if(is_object($cnt)) {
								$ft=$_dir.$cnt->id.".".$cnt->foto;
								if(file_exists($ft)) {
									echo '<dt><a href="'.$ft.'" data-fancybox><img src="'.$ft.'" width="100" style="border:1px solid #ccc;padding:3px;"  /></a></dt>';
								}
							}
							?>
							<dt>Foto</dt> 
							<dd><input type="file" name="foto" accept=".jpg,.png" /></dd>
						</dl>

					</div>


				</fieldset>

				<fieldset>
					<legend>Dados de Contato</legend>
					<div class="colunas4">
						<dl>
							<dt>E-mail</dt>
							<dd><input type="text" name="email" value="<?php echo $values['email'];?>" class="noupper" /></dd>
						</dl>
						<dl>
							<dt>Celular</dt>
							<dd><input type="text" name="celular" value="<?php echo $values['celular'];?>" class="celular" /></dd>
						</dl>
						<dl>
							<dt>Telefone</dt>
							<dd><input type="text" name="telefone" value="<?php echo $values['telefone'];?>" class="celular" /></dd>
						</dl>
					</div>
				</fieldset>

				<fieldset>
					<legend>Dados de Endereço</legend>
					<div class="colunas3">
						<dl>
							<dt>CEP</dt>
							<dd><input type="text" name="cep" id="inpt-cep" value="<?php echo $values['cep'];?>" class="cep" autocomplete="off" /></dd>
						</dl>
						<dl>
							<dt>&nbsp;</dt>
							<dd><button type="button" id="js-consultacep"><i class="icon-search"></i>consultar</button></dd>
						</dl>
					</div>
					<div class="colunas3">

						<dl>
							<dt>Bairro</dt>
							<dd><input type="text" name="bairro" value="<?php echo $values['bairro']; ?>" class="" /></dd>
						</dl>
						<dl>
							<dt>Estado</dt>
							<dd>
								<?php $inEstado=strtoupperWLIB($values['estado']);?><select name="estado" class="js-estado"><option value="">SELECIONE</option><option value="AC"<?php echo $inEstado=="AC"?" selected":"";?>>ACRE</option><option value="AL"<?php echo $inEstado=="AL"?" selected":"";?>>ALAGOAS</option><option value="AM"<?php echo $inEstado=="AM"?" selected":"";?>>AMAZONAS</option><option value="AP"<?php echo $inEstado=="AP"?" selected":"";?>>AMAPÁ</option><option value="BA"<?php echo $inEstado=="BA"?" selected":"";?>>BAHIA</option><option value="CE"<?php echo $inEstado=="CE"?" selected":"";?>>CEARÁ</option><option value="DF"<?php echo $inEstado=="DF"?" selected":"";?>>DISTRITO FEDERAL</option><option value="ES"<?php echo $inEstado=="ES"?" selected":"";?>>ESPÍRITO SANTO</option><option value="GO"<?php echo $inEstado=="GO"?" selected":"";?>>GOIÁS</option><option value="MA"<?php echo $inEstado=="MA"?" selected":"";?>>MARANHÃO</option><option value="MT"<?php echo $inEstado=="MT"?" selected":"";?>>MATO GROSSO</option><option value="MS"<?php echo $inEstado=="MS"?" selected":"";?>>MATO GROSSO DO SUL</option><option value="MG"<?php echo $inEstado=="MG"?" selected":"";?>>MINAS GERAIS</option><option value="PA"<?php echo $inEstado=="PA"?" selected":"";?>>PARÁ</option><option value="PB"<?php echo $inEstado=="PB"?" selected":"";?>>PARAÍBA</option><option value="PR"<?php echo $inEstado=="PR"?" selected":"";?>>PARANÁ</option><option value="PE"<?php echo $inEstado=="PE"?" selected":"";?>>PERNANBUMCO</option><option value="PI"<?php echo $inEstado=="PI"?" selected":"";?>>PIAUÍ</option><option value="RJ"<?php echo $inEstado=="RJ"?" selected":"";?>>RIO DE JANEIRO</option><option value="RN"<?php echo $inEstado=="RN"?" selected":"";?>>RIO GRANDE DO NORTE</option><option value="RO"<?php echo $inEstado=="RO"?" selected":"";?>>RONDÔNIA</option><option value="RS"<?php echo $inEstado=="RS"?" selected":"";?>>RIO GRANDE DO SUL</option><option value="RR"<?php echo $inEstado=="RR"?" selected":"";?>>RORAIMA</option><option value="SC"<?php echo $inEstado=="SC"?" selected":"";?>>SANTA CATARINA</option><option value="SE"<?php echo $inEstado=="SE"?" selected":"";?>>SERGIPE</option><option value="SP"<?php echo $inEstado=="SP"?" selected":"";?>>SÃO PAULO</option><option value="TO"<?php echo $inEstado=="TO"?" selected":"";?>>TOCANTINS</option></select>
							</dd>
						</dl>
						<dl>
							<dt>Cidade</dt>
							<dd>
								<select name="id_cidade" class="js-cidade">
									<option value="">-</option>
								</select>
								<input type="hidden" name="cidade" value="<?php echo $values['cidade'];?>"/>
							</dd>
						</dl>
					</div>
					<div class="colunas3">
						<dl>
							<dt>Endereço</dt>
							<dd>
								<input type="text" name="endereco" value="<?php echo $values['endereco']; ?>" class="" />
							</dd>
						</dl>
						<dl>
							<dt>Número</dt>
							<dd>
								<input type="text" name="numero" value="<?php echo $values['numero']; ?>" class="" />
							</dd>
						</dl>
						<dl>
							<dt>Complemento</dt>
							<dd>
								<input type="text" name="complemento" value="<?php echo $values['complemento']; ?>" class="" />
							</dd>
						</dl>
					</div>
				</fieldset>

				<fieldset>
					<legend>Comissionamento Geral</legend>
					<script type="text/javascript">
						$(function(){
							$('select.js-cg-tipo').change(function(){
								if($(this).val()=="valor") {
									$(this).parent().parent().find('.js-cg-valor').prop('disabled',true);
								} else {
									$(this).parent().parent().find('.js-cg-valor').prop('disabled',false);
								}
							}).trigger('change')
						})
					</script>
					<div class="registros">
						<table>
							<tr>
								<th style="width:200px;">Plano</th>
								<th style="width:150px;">Tipo</th>
								<th>Valor</th>
								<th>Abater Custo</th>
								<th>Abater Impostos</th>
								<th>Abater Taxa</th>
							</tr>
							<?php


							foreach($_planos as $v) {
								$cgTipo=$cgValor=$cgAbaterCustos=$cgAbaterImpostos=$cgAbaterTaxas='';
								if(is_object($cnt)) {
									$sql->consult($_table."_comissionamentogeral","*","where id_profissional=$cnt->id and id_plano=$v->id and lixo=0");
									if($sql->rows) {
										$x=mysqli_fetch_object($sql->mysqry);
										$cgTipo=$x->tipo;
										$cgValor=number_format($x->valor,2,",",".");
										$cgAbaterCustos=$x->abater_custos;
										$cgAbaterImpostos=$x->abater_impostos;
										$cgAbaterTaxas=$x->abater_taxas;
									}
									
								}
							?>
							<tr>
								<td><?php echo utf8_encode($v->titulo);?></td>
								<td>
									<select name="tipo_<?php echo $v->id;?>" class="js-cg-tipo">
										<option>-</option>
										<option value="valor"<?php echo $cgTipo=="valor"?" selected":"";?>>Valor Fixo (R$)</option>
										<option value="porcentual"<?php echo $cgTipo=="porcentual"?" selected":"";?>>Porcentual (%)</option>
										<option value="horas"<?php echo $cgTipo=="horas"?" selected":"";?>>Horas</option>
									</select>
								</td>
								<td><input type="text" name="valor_<?php echo $v->id;?>" class="money js-cg-valor" value="<?php echo $cgValor;?>" /></td>
								<td><label><input type="checkbox" name="abater_custos_<?php echo $v->id;?>" value="1"<?php echo $cgAbaterCustos==1?" checked":"";?> /> Abater</label></td>
								<td><label><input type="checkbox" name="abater_impostos_<?php echo $v->id;?>" value="1"<?php echo $cgAbaterImpostos==1?" checked":"";?> /> Abater</label></td>
								<td><label><input type="checkbox" name="abater_taxas_<?php echo $v->id;?>" value="1"<?php echo $cgAbaterTaxas==1?" checked":"";?>/> Abater</label></td>
							</tr>
							<?php	
							}
							?>
						</table>	
					</div>
				</fieldset>

				<fieldset>
					<legend>Comissionamento Personalizado</legend>
					<textarea name="comissionamentoPersonalizado" style="display:none;"></textarea>
					<input type="hidden" name="cp_id" value="0" />
					<div class="colunas4">
						<dl class="dl2">
							<dt>Procedimento</dt>
							<dd>
								<select name="cp_id_procedimento">
									<option value="">-</option>
									<?php
									foreach($_procedimentos as $c) {
										echo '<option value="'.$c->id.'">'.utf8_encode($c->titulo).'</option>';
									}
									?>
								</select>
							</dd>
						</dl>
						<dl>
							<dt>Plano</dt>
							<dd>
								<select name="cp_id_plano">
									<option value="">-</option>
									<?php
									foreach($_planos as $c) {
										echo '<option value="'.$c->id.'">'.utf8_encode($c->titulo).'</option>';
									}
									?>
								</select>
							</dd>
						</dl>
						<dl>
							<dt>Tipo</dt>
							<dd>
								<select name="cp_tipo">
									<option value="">-</option>
									<option value="valor">Valor Fixo (R$)</option>
									<option value="porcentual">Porcentual (%)</option>
									<option value="horas">Horas</option>
								</select>
							</dd>
						</dl>
					</div>

					<div class="colunas5">
						<dl>
							<dt>Valor</dt>
							<dd><input type="text" name="cp_valor" class="money" /></dd>
						</dl>
						<dl>
							<dt>&nbsp;</dt>
							<dd><label><input type="checkbox" name="cp_abater_custos" value="1" /> Abater Custos</label></dd>
						</dl>
						<dl>
							<dt>&nbsp;</dt>
							<dd><label><input type="checkbox" name="cp_abater_impostos" value="1" /> Abater Impostos</label></dd>
						</dl>
						<dl>
							<dt>&nbsp;</dt>
							<dd><label><input type="checkbox" name="cp_abater_taxas" value="1" /> Abater Taxas</label></dd>
						</dl>

						<dl>
							<dt>&nbsp;</dt>
							<dd>
								<a href="javascript:;" class="button button__sec js-cp-btn"><i class="iconify" data-icon="bx-bx-check"></i></a>
								<a href="javascript:;" class="js-cp-cancelar tooltip" style="display: none;color:red" title="Cancelar edição"><span class="iconify" data-icon="icons8:cancel"></span> cancelar edição</a>
							</dd>
						</dl>
					</div>	

					<script type="text/javascript">
						var id_profissional = '<?php echo is_object($cnt)?$cnt->id:0;?>';
						var comissionamento = <?php echo (isset($comissionamentoPersonalizado) and !empty($comissionamentoPersonalizado))?"JSON.parse('".json_encode($comissionamentoPersonalizado)."')":"[]";?>;

						const comissionamentoListar = () => {
							if(comissionamento) {
								$('.js-cp-table tbody tr').remove();
								comissionamento.forEach(x => {

									let tipo= $('select[name=cp_tipo] option[value='+x.tipo+']').text();
									let html =`<tr>
													<td>${x.procedimento}</td>
													<td>${x.plano}</td>
													<td>${tipo}</td>
													<td>${x.valor}</td>
													<td style="text-align:center;">${x.abaterCustos==1?'<i class="iconify" data-icon="bx-bx-check"></i>':'<span class="iconify" data-icon="dashicons:no-alt" data-inline="false"></span>'}</td>
													<td style="text-align:center;">${x.abaterImpostos==1?'<i class="iconify" data-icon="bx-bx-check"></i>':'<span class="iconify" data-icon="dashicons:no-alt" data-inline="false"></span>'}</td>
													<td style="text-align:center;">${x.abaterTaxas==1?'<i class="iconify" data-icon="bx-bx-check"></i>':'<span class="iconify" data-icon="dashicons:no-alt" data-inline="false"></span>'}</td>
													<td>
														<a href="javascript:;" data-id="${x.id}" class="js-editar registros__acao registros__acao_sec"><i class="iconify" data-icon="bx:bxs-edit-alt"></i></a>
														<a href="javascript:;" data-id="${x.id}" class="js-remover registros__acao registros__acao_sec"><i class="iconify" data-icon="bx:bxs-trash"></i></a>
													</td>
												</tr>`;
									$('.js-cp-table tbody').append(html);
								});

								$('textarea[name=comissionamentoPersonalizado]').val(JSON.stringify(comissionamento))
							}
						};

						$(function(){
							comissionamentoListar();

							$('.js-cp-table').on('click','.js-remover',function() {

								let index = $(this).index('table.js-cp-table .js-remover');
								let id = $(this).attr('data-id');
								comissionamento.splice(index,1);
								comissionamentoListar();

								if(eval(id_profissional)>0) {
									let data = `ajax=comissionamentoRemover&id_profissional=${id_profissional}&id=${id}`;
									$.ajax({
										type:'POST',
										data:data
									})
								} 
									
							});

							$('.js-cp-table').on('click','.js-editar',function(){

								let index = $(this).index('table.js-cp-table .js-editar');

								if(comissionamento[index]) {
									$('select[name=cp_id_procedimento]').val(comissionamento[index].id_procedimento);
									$('select[name=cp_id_plano]').val(comissionamento[index].id_plano);
									$('select[name=cp_tipo]').val(comissionamento[index].tipo);
									$('input[name=cp_valor]').val(comissionamento[index].valor);
									$('input[name=cp_abater_custos]').prop('checked',(comissionamento[index].abaterCustos==1?true:false));
									$('input[name=cp_abater_impostos]').prop('checked',(comissionamento[index].abaterImpostos==1?true:false));
									$('input[name=cp_abater_taxas]').prop('checked',(comissionamento[index].abaterTaxas==1?true:false));
									$('input[name=cp_id]').val(comissionamento[index].id);
									$('.js-cp-cancelar').show();
								} else {

								}

							

							});

							$('.js-cp-cancelar').click(function(){
									
								$('select[name=cp_id_procedimento]').val('');
								$('select[name=cp_id_plano]').val('');
								$('select[name=cp_tipo]').val('');
								$('input[name=cp_valor]').val('');
								$('input[name=cp_abater_custos]').prop('checked',false);
								$('input[name=cp_abater_impostos]').prop('checked',false);
								$('input[name=cp_abater_taxas]').prop('checked',false);
								$('input[name=cp_id]').val(0);
								comissionamentoListar();
							
								$(this).hide();
							});

							$('.js-cp-btn').click(function(){
								let cpIDProcedimento=$('select[name=cp_id_procedimento]').val();
								let cpIDPlano=$('select[name=cp_id_plano]').val();

								let cpProcedimento=$('select[name=cp_id_procedimento] option:selected').text();
								let cpPlano=$('select[name=cp_id_plano] option:selected').text();

								let cpTipo=$('select[name=cp_tipo]').val();
								let cpValor=$('input[name=cp_valor]').val();
								let cpAbaterCustos=$('input[name=cp_abater_custos]').prop('checked')?1:0;
								let cpAbaterImpostos=$('input[name=cp_abater_impostos]').prop('checked')?1:0;
								let cpAbaterTaxas=$('input[name=cp_abater_taxas]').prop('checked')?1:0;
								let cpID=$('input[name=cp_id]').val();

								let erro='';
								if(cpIDProcedimento.length==0) {
									erro='Selecione o Procedimento';
								} else if(cpIDPlano.length==0) {
									erro='Selecione o Plano';
								} else if(cpTipo.length==0) {
									erro='Selecione o Tipo';
								}  else if(cpValor.length==0) {
									erro='Defina um Valor';
								} 

								if(erro.length>0) {
									swal({title: "Erro!", text: erro, type:"error", confirmButtonColor: "#424242"});
								} else {
									let item = {};
									item.id  = eval(cpID);
									item.id_procedimento  = cpIDProcedimento;
									item.id_plano  = cpIDPlano;
									item.procedimento  = cpProcedimento;
									item.plano  = cpPlano;
									item.tipo  = cpTipo;
									item.valor  = cpValor;
									item.abaterCustos  = cpAbaterCustos;
									item.abaterTaxas  = cpAbaterTaxas;
									item.abaterImpostos  = cpAbaterImpostos;

									let persistido=false;
									if(id_profissional>0) {
										let data = `ajax=comissionamentoPersistir&id_profissional=${id_profissional}&id_procedimento=${cpIDProcedimento}&id_plano=${cpIDPlano}&valor=${cpValor}&abater_custos=${cpAbaterCustos}&abater_taxas=${cpAbaterTaxas}&abater_impostos=${cpAbaterImpostos}&tipo=${cpTipo}`;
										$.ajax({
											type:'POST',
											data:data,
											success:function(rtn) {
												if(rtn.success) {
													comissionamento=rtn.comissionamento;
													comissionamentoListar();
													$('select[name=cp_id_procedimento]').val('');
													$('select[name=cp_id_plano]').val('');
													$('select[name=cp_tipo]').val('');
													$('input[name=cp_valor]').val('');
													$('input[name=cp_abater_custos]').prop('checked',false);
													$('input[name=cp_abater_impostos]').prop('checked',false);
													$('input[name=cp_abater_taxas]').prop('checked',false);
													$('input[name=cp_id]').val(0);
													$('.js-cp-cancelar').hide();
												} else if(rtn.error) {
													swal({title: "Erro!", text: rtn.error, type:"error", confirmButtonColor: "#424242"});
												} else {
													swal({title: "Erro!", text: "Algum erro ocorreu durante o salvamento das informações. Tente novamente.", type:"error", confirmButtonColor: "#424242"});
												}
											},
											error:function() {
												swal({title: "Erro!", text: "Algum erro ocorreu durante o salvamento das informações. Tente novamente.", type:"error", confirmButtonColor: "#424242"});
											}
										});
									} else {
										if(item.id>0) {
											let newComissionamento = comissionamento.map((x)=>{
												if(x.id==item.id) {
													return item;
												} else {
													return x;
												}
											});
											comissionamento=newComissionamento;
										} else {
											comissionamento.push(item);
										}
										comissionamentoListar();
										$('select[name=cp_id_procedimento]').val('');
										$('select[name=cp_id_plano]').val('');
										$('select[name=cp_tipo]').val('');
										$('input[name=cp_valor]').val('');
										$('input[name=cp_abater_custos]').prop('checked',false);
										$('input[name=cp_abater_impostos]').prop('checked',false);
										$('input[name=cp_abater_taxas]').prop('checked',false);
										$('input[name=cp_id]').val(0);
										$('.js-cp-cancelar').hide();
									}

									

								}
							});
						});
					</script>
					<div class="registros">
						<table class="js-cp-table">
							<thead>
								<tr>
									<th style="width:200px;">Procedimento</th>
									<th style="width:200px;">Plano</th>
									<th style="width:150px;">Tipo</th>
									<th>Valor</th>
									<th>Abater Custo</th>
									<th>Abater Impostos</th>
									<th>Abater Taxa</th>
									<th style="width:120px;"></th>
								</tr>
							</thead>
							<tbody>

							</tbody>
						</table>
					</div>	
				</fieldset>
				<?php
				if(is_object($cnt)) {
				?>
				<script type="text/javascript">
					$(function(){
						$('select[name=id_cadeira]').change(function(){
							let id_cadeira = $(this).val();
							alert(id_cadeira);
						})
					});
				</script>

				<?php
				$_dias=explode(",","Domingo,Segunda-Feira,Terça-Feira,Quarta-Feira,Quinta-Feira,Sexta-Feira,Sábado");
				?>
				<style type="text/css">
					div.js-horario {
						padding:3px;
						margin:4px;
						font-size: 16px;

						width:auto;
						border:solid 1px #CCC;
						background:#FFF;
						border-radius: 6px;
						text-align: center;
					}
				</style>
				<script type="text/javascript">
					var horarios = [];
					var id_profissional=<?php echo $cnt->id;?>;

					const horariosListar = () => {
						if(horarios) {
							$('.js-td').html('')
							for(var id_unidade in horarios) {
								let index = `.js-${id_unidade}`;
								for(var dia in horarios[id_unidade]) {
									horarios[id_unidade][dia].forEach(x=>{
										
										$(`${index}-${x.id_cadeira}-${dia}`).append(`<div class="js-horario">${x.inicio}  - ${x.fim}<br /><a href="javascript:;" data-id="${x.id}" class="js-editar registros__acao registros__acao_sec"><i class="iconify" data-icon="bx:bxs-edit-alt"></i></a>
																<a href="javascript:;" data-id="${x.id}" class="js-remover registros__acao registros__acao_sec"><i class="iconify" data-icon="bx:bxs-trash"></i></a><div>`);
									})
								}
							}
							
						}
					}

					const horariosAtualizar = () => {
						let data = `ajax=horariosListar&id_profissional=${id_profissional}`;
						$.ajax({
							type:"POST",
							data:data,
							success:function(rtn) {
								if(rtn.success) {
									horarios=rtn.horarios;
									horariosListar();
								}
							}
						})
					}
					
					const horarioEditar = (id_horario) => {
						let data = `ajax=horariosEditar&id_horario=${id_horario}`;
						var horarioObj = [];
						$.ajax({
							type:"POST",
							data:data,
							success:function(rtn) {
								if(rtn.success) {
									id_unidade=rtn.id_unidade;

									$(`.js-${id_unidade}-id`).val(rtn.id);
									$(`.js-${id_unidade}-id_cadeira`).val(rtn.id_cadeira);
									$(`.js-${id_unidade}-dia`).val(rtn.dia);
									$(`.js-${id_unidade}-inicio`).val(rtn.inicio);
									$(`.js-${id_unidade}-fim`).val(rtn.fim);

									$('.js-horarios-cancelar').show();
								}
							}
						});
					}

					/* const horariosAdicionar = () => {
						let id_unidade = $('.js-horarios-id_unidade').val();
						let dia = $('.js-horarios-dia').val();
						let inicio = $('.js-horarios-inicio').val();
						let fim = $('.js-horarios-fim').val();
						let id_horario = $('.js-horarios-id_horario').val();

						if(id_unidade.length==0) {
							swal({title: "Erro!", text: "Selecione o Unidade!", type:"error", confirmButtonColor: "#424242"});
						} else 	if(dia.length==0) {
							swal({title: "Erro!", text: "Selecione o Dia!", type:"error", confirmButtonColor: "#424242"});
						} else if(inicio.length==0) {
							swal({title: "Erro!", text: "Selecione o Início do Horário!", type:"error", confirmButtonColor: "#424242"});
						} else if(fim.length==0) {
							swal({title: "Erro!", text: "Selecione o Final do Horário!", type:"error", confirmButtonColor: "#424242"});
						} else {
							let data = `ajax=horariosAdicionar&id_profissional=${id_profissional}&id_unidade=${id_unidade}&dia=${dia}&inicio=${inicio}&fim=${fim}&id_horario=${id_horario}`;
							$.ajax({
								type:"POST",
								data:data,
								success:function(rtn) {
									if(rtn.success) {
										$('.js-horarios-id_unidade').val('');
										$('.js-horarios-dia').val('');
										$('.js-horarios-inicio').val('');
										$('.js-horarios-fim').val('');
										$('.js-horarios-id_horario').val(0);
										$('.js-horarios-cancelar').hide();
										horariosAtualizar();
									}
								}
							});
						}
					} */

					$(function(){
						horariosAtualizar();

						$('.js-horarios-submit').click(function(){
							let id_unidade = $(this).attr('data-id_unidade');
							let id_cadeira = $(`.js-${id_unidade}-id_cadeira`).val();
							let id = $(`.js-${id_unidade}-id`).val();
							let dia = $(`.js-${id_unidade}-dia`).val();
							let inicio = $(`.js-${id_unidade}-inicio`).val();
							let fim = $(`.js-${id_unidade}-fim`).val();

							if(id_cadeira.length==0) {
								swal({title: "Erro!", text: "Selecione a Cadeira!", type:"error", confirmButtonColor: "#424242"});
							} else if(dia.length==0) {
								swal({title: "Erro!", text: "Selecione o Dia!", type:"error", confirmButtonColor: "#424242"});
							} else if(inicio.length==0) {
								swal({title: "Erro!", text: "Defina o Início", type:"error", confirmButtonColor: "#424242"});
							} else if(fim.length==0) {
								swal({title: "Erro!", text: "Defina o Fim", type:"error", confirmButtonColor: "#424242"});
							} else {
								let data = `ajax=horariosPersistir&id_cadeira=${id_cadeira}&dia=${dia}&inicio=${inicio}&fim=${fim}&id_unidade=${id_unidade}&id_profissional=${id_profissional}&id=${id}`;
								$.ajax({
									type:'POST',
									data:data,
									success:function(rtn) {
										if(rtn.success) {
											horariosAtualizar();	

											$(`.js-${id_unidade}-id_cadeira`).val('');
											$(`.js-${id_unidade}-id`).val(0);
											$(`.js-${id_unidade}-dia`).val('');
											$(`.js-${id_unidade}-fim`).val('');
											$(`.js-${id_unidade}-inicio`).val('');
											$(`.js-horarios-cancelar`).hide();
										} else if(rtn.error) {
											swal({title: "Erro!", text: rtn.error, type:"error", confirmButtonColor: "#424242"});
										} else {
											swal({title: "Erro!", text: "Algum erro ocorreu! Tente novamente.", type:"error", confirmButtonColor: "#424242"});
										}
										
									},
									error:function() {
										swal({title: "Erro!", text: "Algum erro ocorreu! Tente novamente.", type:"error", confirmButtonColor: "#424242"});
									}
								})

							}
						})

						$('.js-horario-table').on('click','.js-editar',function(){
							let id = $(this).attr('data-id');
							horarioEditar(id);
						});

						$('.js-horarios-cancelar').click(function(){
							let id_unidade = $(this).attr('data-id_unidade');
							$(`.js-${id_unidade}-id_cadeira`).val('');
							$(`.js-${id_unidade}-id`).val(0);
							$(`.js-${id_unidade}-dia`).val('');
							$(`.js-${id_unidade}-fim`).val('');
							$(`.js-${id_unidade}-inicio`).val('');
							//$(`.js-horarios-cancelar`).hide();
						});

						$('.js-horario-table').on('click','.js-remover',function(){
							let id_horario = $(this).attr('data-id');
							swal({
								title: "Atenção",
								text: "Você tem certeza que deseja remover este registro?",
								type: "warning",
								showCancelButton: true,
								confirmButtonColor: "#DD6B55",
								confirmButtonText: "Sim!",
								cancelButtonText: "Não",
								closeOnConfirm:false,
								closeOnCancel: false }, 
								function(isConfirm){   
									if (isConfirm) {   
										let data = `ajax=horariosRemover&id_horario=${id_horario}`; 
										$.ajax({
											type:"POST",
											data:data,
											success:function(rtn) {
												if(rtn.success) {
													
													horariosAtualizar();
													swal.close();   
												} else if(rtn.error) {
													swal({title: "Erro!", text: rtn.error, type:"error", confirmButtonColor: "#424242"});
												} else {
													swal({title: "Erro!", text: "Algum erro ocorreu durante a remoção deste horário!", type:"error", confirmButtonColor: "#424242"});
												}
											},
											error:function(){
												swal({title: "Erro!", text: "Algum erro ocorreu durante a remoção deste horário!", type:"error", confirmButtonColor: "#424242"});
											}
										})
									} else {   
										swal.close();   
									} 
								});
						});

					});
				</script>

				<?php
					foreach($_unidades as $u) {
				?>
				<fieldset>
					<legend><?php echo utf8_encode($u->titulo);?></legend>

					<input type="hidden" class="js-<?php echo $u->id;?>-id" value="0" />
					<div class="colunas5"  style="margin-bottom: 20px;">	
						<dl>
							<dt>Cadeira</dt>
							<dd>
								<select class="<?php echo "js-".$u->id."-id_cadeira";?>">
									<option value="">-</option>
									<?php
									foreach($_cadeiras[$u->id] as $c) echo '<option value="'.$c->id.'">'.utf8_encode($c->titulo).'</option>';
									?>
								</select>
							</dd>
						</dl>
						<dl>
							<dt>Dia</dt>
							<dd>
								<select  class="<?php echo "js-".$u->id."-dia";?>">
									<option value="">-</option>
									<?php
									for($i=0;$i<=6;$i++) {
										echo '<option value="'.$i.'">'.$_dias[$i].'</option>';	
									}
									?>
								</select>
							</dd>
						</dl>
						<dl>
							<dt>Início</dt>
							<dd><input type="text" name="inicio" class="hora <?php echo "js-".$u->id."-inicio";?>" /></dd>
						</dl>
						<dl>
							<dt>Fim</dt>
							<dd><input type="text" name="inicio" class="hora  <?php echo "js-".$u->id."-fim";?>" /></dd>
						</dl>

						<dl>
							<dt>&nbsp;</dt>
							<dd>
								<a href="javascript:;" class="button button__sec js-horarios-submit" data-id_unidade="<?php echo $u->id;?>"><i class="iconify" data-icon="bx-bx-check"></i></a>
								<a href="javascript:;" class="js-horarios-cancelar tooltip" data-id_unidade="<?php echo $u->id;?>" style="display: none;color:red" title="Cancelar edição"><span class="iconify" data-icon="icons8:cancel"></span> cancelar edição</a>
							</dd>
						</dl>
					</div>

					<div class="registros">
						<table class="js-horario-table">
							<tr>
								<th>Unidade</th>
								<?php
								for($i=0;$i<=6;$i++) {
									echo '<th>'.$_dias[$i].'</th>';	
								}
								?>
							</tr>
							<?php
							foreach($_cadeiras[$u->id] as $v) {
							?>
							<tr>
								<td><?php echo $v->titulo;?></td>
								<?php
								for($i=0;$i<=6;$i++) {
									echo '<td class="js-td js-'.$u->id.'-'.$v->id.'-'.$i.'"></td>';	
								}
								?>
							</tr>
							<?php	
							}
							?>
						</table>
					</div>
				</fieldset>
				<?php
					}	
				}
				?>
			</form>			
		</div>
	</section>
			
	<?php
	} else {
	?>
	
	<section class="filtros">
		<h1 class="filtros__titulo">Cirurgiões Dentistas</h1>
		<form method="get" class="filtros-form">
			<input type="hidden" name="csv" value="0" />
			<dl> 
				<dt>Busca</dt>
				<dd><input type="text" name="busca" value="<?php echo isset($values['busca'])?$values['busca']:'';?>" /></dd>
			</dl>
			<button type="submit" class="filtros-form__button"><i class="iconify" data-icon="bx-bx-search"></i></button>						
		</form>
		<div class="filtros-acoes">
			<a href="<?php echo $_page."?form=1&$url";?>" data-padding="0" class="adicionar tooltip" title="Adicionar"><i class="iconify" data-icon="bx-bx-plus"></i></a>
		</div>
	</section>

	<?php
		
	if(isset($_GET['deleta']) and is_numeric($_GET['deleta']) and $usr->tipo=="admin") {
		$vSQL="lixo='1'";
		$vWHERE="where id='".$_GET['deleta']."'";
		$sql->update($_table,$vSQL,$vWHERE);
		$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='delete',vsql='".addslashes($vSQL)."',vwhere='".addslashes($vWHERE)."',tabela='".$_table."',id_reg='".$_GET['deleta']."'");
		$jsc->jAlert("Registro excluído com sucesso!","sucesso","document.location.href='".$_page."?".$url."'");
		die();
	}
	
	$where="WHERE lixo='0'";
	if(isset($values['busca']) and !empty($values['busca'])) $where.=" and (nome like '%".utf8_decode($values['busca'])."%')";
	
	if($usr->cpf=="wlib" and isset($_GET['cmd'])) echo $where;

	$sql->consult($_table,"*",$where." order by nome asc");
	
	?>

	<section class="grid">
		<div class="box">

			<div class="registros-qtd">
				<p class="registros-qtd__item"><?php echo $sql->rows;?> registros</p>
			</div>

			<div class="registros">
				<table class="tablesorter">
					<thead>
						<tr>
							<th>Nome</th>
							<th style="width:200px;">Celular</th>
						</tr>
					</thead>
					<tbody>
					<?php
					while($x=mysqli_fetch_object($sql->mysqry)) {
					?>
					<tr onclick="document.location.href='<?php echo $_page;?>?form=1&edita=<?php echo $x->id."&".$url;?>'">
						<td>
							<?php
							if(!empty($x->calendario_iniciais)) {
							?>
							<span style="background:<?php echo empty($x->calendario_cor)?"#CCC":$x->calendario_cor;?>;color:#FFF;padding:10px;border-radius: 50%"><?php echo $x->calendario_iniciais;?></span>
							<?php
							}
							?>
							<?php echo utf8_encode($x->nome);?>
						</td>
						<td><?php echo empty($x->celular)?"-":$x->celular;?></td>						
					</tr>
					<?php
					}
					?>
					</tbody>
				</table>
			</div>
		</div>
	</section>
			
	<?php
	}
	?>
</section>

<?php
	include "includes/footer.php";
?>