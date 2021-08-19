<?php
	include "includes/header.php";
	include "includes/nav.php";

	$_table=$_p."pacientes";
	$_page=basename($_SERVER['PHP_SELF']);

	$_width=400;
	$_height=400;
	$_dir="arqs/pacientes/";

	$_cidades=array();
	$sql->consult($_p."cidades","*","");
	while($x=mysqli_fetch_object($sql->mysqry)) $_cidades[$x->id]=$x;

	$_profissoes=array();
	$sql->consult($_p."parametros_profissoes","*","where lixo=0 order by titulo asc");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_profissoes[$x->id]=$x;
	}


	$_pacienteIndicacoes=array();
	$sql->consult($_p."parametros_indicacoes","*","where lixo=0 order by titulo asc");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_pacienteIndicacoes[$x->id]=$x;
	}

	$_pacienteGrauDeParentesco=array();
	$sql->consult($_p."parametros_grauparentesco","*","where lixo=0 order by titulo asc");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_pacienteGrauDeParentesco[$x->id]=$x;
	}

	$paciente=$cnt='';
	if(isset($_GET['id_paciente']) and is_numeric($_GET['id_paciente'])) {
		$sql->consult($_p."pacientes","*","where id='".$_GET['id_paciente']."'");
		if($sql->rows) {
			$paciente=mysqli_fetch_object($sql->mysqry);
			$cnt=$paciente;
		}
	}


	$campos=explode(",","nome,situacao,noem,sexo,foto,rg,rg_orgaoemissor,rg_estado,cpf,data_nascimento,profissao,estado_civil,telefone1,telefone1_whatsapp,telefone1_whatsapp_permissao,telefone2,email,instagram,instagram_naopossui,musica,indicacao_tipo,indicacao,cep,endereco,numero,complemento,bairro,estado,cidade,id_cidade,responsavel_possui,responsavel_nome,responsavel_sexo,responsavel_rg,responsavel_rg_orgaoemissor,responsavel_rg_estado,responsavel_datanascimento,responsavel_estadocivil,responsavel_cpf,responsavel_profissao,responsavel_grauparentesco,preferencia_contato");
	
	foreach($campos as $v) $values[$v]='';
	$values['data']=date('d/m/Y H:i');
	$values['sexo']='M';


	if(is_object($paciente)) {
		$values=$adm->values($campos,$cnt);
		$values['data']=date('d/m/Y H:i',strtotime($cnt->data));
	}

	$_anamnese=array();
	$sql->consult($_p."parametros_anamnese","*","WHERE 	lixo=0 order by titulo asc");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_anamnese[$x->id]=$x;
	}


	$evolucao='';
	if(isset($_GET['edita']) and is_numeric($_GET['edita'])) {	
		$sql->consult($_p."pacientes_evolucoes","*","where id='".$_GET['edita']."' and id_tipo=1");
		if($sql->rows) {
			$evolucao=mysqli_fetch_object($sql->mysqry);
			$_GET['id_anamnese']=$evolucao->id_anamnese;
		} else {
			$jsc->jAlert("Anamnese não encontrada","erro","document.location.href='pg_contatos_pacientes_evolucao.php?id_paciente='".$paciente->id."'");
			die();
		}
	}

	$anamnese='';
	if(isset($_GET['id_anamnese']) and is_numeric($_GET['id_anamnese']) and isset($_anamnese[$_GET['id_anamnese']])) {
		$anamnese=$_anamnese[$_GET['id_anamnese']];
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
				$('.js-anamnese').change(function(){
					let id_anamnese = $(this).val();
					document.location.href=`<?php echo "$_page?id_paciente=$paciente->id"?>&id_anamnese=${this.value}`;
				})
				$('.js-btn-salvar').click(function(){
					///$('form').submit();

					let erro = false;
					$('.js-form-anamnese').find('.js-pergunta').each(function(i,el){
						let obg = eval($(el).attr('data-obg'));
						let tipo = $(el).attr('data-tipo');

						if(obg==1) {
							if(tipo=="nota" || tipo=="simnao") {
								let selecionou = $(el).find('input[type=radio]:checked').length>0?true:false;
								if(selecionou===false) {
									$(el).css('background', '#ffffdb');
									erro=true;
								}
							} else if(tipo=="simnaotexto") {
								let selecionouOpcao = ($(el).find('input[type=radio]:checked').length>0)?true:false;
								let selecionouTexto = ($(el).find('textarea').val().length>0)?true:false;

								if(selecionouOpcao===false) {
									$(el).css('background','#ffffdb');
									erro=true;
								} 
								if(selecionouTexto===false) {
									$(el).find('textarea').css('background-color', '#ffffdb');
									erro=true;
								}
							} else if(tipo=="texto") {

								let selecionou = ($(el).find('textarea').val().length>0)?true:false;

								if(selecionou===false) {
									$(el).find('textarea').css('background-color','#ffffdb');
									erro=true;
								}
							}
						}
					});

					if(erro===true) {
						alert('Preencha os campos destacados');
					} else {
						$('.js-form-anamnese').submit();
					}

				})
			});
		</script>

		
		<section class="grid">
			<div class="box">

				<?php
				$exibirEvolucaoNav=1;
				require_once("includes/evolucaoMenu.php");
				?>

				<section class="js-evolucao-adicionar" id="evolucao-anamnese">

					<form class="form js-form-anamnese" method="post">
						<input type="hidden" name="acao" value="wlib" />
						<fieldset>
							<legend><span class="badge">1</span> Tipo de Anamnese</legend>
							<dl>
								<dd>
									<select name="id_anamnese" class="chosen js-anamnese" data-placeholder="Selecione"<?php echo is_object($anamnese)?" disabled":"";?>>
										<option value=""></option>
										<?php
										foreach($_anamnese as $x) {
											echo '<option value="'.$x->id.'"'.((is_object($anamnese) and $anamnese->id==$x->id)?' selected':'').'>'.utf8_encode($x->titulo).'</option>';
										}
										?>
									</select>
								</dd>
							</dl>
						</fieldset>
						<?php
						if(is_object($anamnese)) {
							$perguntas=array();
							$sql->consult($_p."parametros_anamnese_formulario","*","where id_anamnese=$anamnese->id and lixo=0 order by ordem asc");
							if($sql->rows) while($x=mysqli_fetch_object($sql->mysqry)) $perguntas[$x->id]=$x;

							if(isset($_POST['acao'])) {
								if($_POST['acao']=="wlib") {

									if(count($perguntas)>0) {

										if(is_object($evolucao)) {
											//$sql->update($_p."pacientes_evolucoes","obs='".addslashes(utf8_decode($_POST['obs']))."'","where id=$evolucao->id");
											$id_evolucao=$evolucao->id;
										} else {
											// id_tipo = 1 -> Procedimentos Aprovados
											$sql->consult($_p."pacientes_evolucoes","*","WHERE data > NOW() - INTERVAL 1 MINUTE and 
																													id_paciente=$paciente->id and
																													id_tipo=1 and  
																													id_usuario=$usr->id");	
											if($sql->rows) {
												$e=mysqli_fetch_object($sql->mysqry);
												$id_evolucao=$e->id;
											} else {
												$sql->add($_p."pacientes_evolucoes","data=now(),
																						id_tipo=1,
																						id_anamnese=$anamnese->id,
																						id_paciente=$paciente->id,
																						id_usuario=$usr->id");
												$id_evolucao=$sql->ulid;
											}
										}

										foreach($perguntas as $id_pergunta=>$p) {

											$pJson=array();
											foreach($p as $k=>$v) {
												$pJson[$k]=utf8_encode($v);
											}


											$vsqlResposta="id_paciente=$paciente->id,
															id_evolucao=$id_evolucao,
															id_pergunta=$p->id,
															id_anamnese=$anamnese->id,
															pergunta='".addslashes($p->pergunta)."',
															tipo='".$p->tipo."',
															json_pergunta='".addslashes((json_encode($pJson)))."'";

											if($p->tipo=="nota" or $p->tipo=="simnao") {
												$vsqlResposta.=",resposta='".addslashes(strtoupperWLIB(utf8_decode(isset($_POST["resposta_$p->id"])?$_POST["resposta_$p->id"]:"")))."'";
											} else if($p->tipo=='texto') {
												$vsqlResposta.=",resposta_texto='".addslashes(strtoupperWLIB(utf8_decode(isset($_POST["resposta_texto_$p->id"])?$_POST["resposta_texto_$p->id"]:"")))."'";
											} else if($p->tipo=='simnaotexto') {
												$vsqlResposta.=",resposta='".addslashes(strtoupperWLIB(utf8_decode(isset($_POST["resposta_$p->id"])?$_POST["resposta_$p->id"]:"")))."',resposta_texto='".addslashes(strtoupperWLIB(utf8_decode(isset($_POST["resposta_texto_$p->id"])?$_POST["resposta_texto_$p->id"]:"")))."'";
											}

											//echo $vsqlResposta."<BR>";
											$resposta='';
											$where="where id_paciente=$paciente->id and id_anamnese=$anamnese->id and id_evolucao=$id_evolucao and id_pergunta=$p->id and lixo=0";
											$sql->consult($_p."pacientes_evolucoes_anamnese","id",$where);
											if($sql->rows) {
												$resposta=mysqli_fetch_object($sql->mysqry);
											}

											if(is_object($resposta)) {
												$sql->update($_p."pacientes_evolucoes_anamnese",$vsqlResposta.",data_atualizacao=now()","where id=$resposta->id");
											} else {
												$sql->add($_p."pacientes_evolucoes_anamnese",$vsqlResposta.",data=now(),id_usuario=$usr->id");

											}
										}
									}

									$jsc->go("pg_contatos_pacientes_evolucao.php?id_paciente=$paciente->id");
								}
							}

							$respostas=array();
							if(is_object($evolucao)) {
								$sql->consult($_p."pacientes_evolucoes_anamnese","*","where id_evolucao=$evolucao->id");
								while($x=mysqli_fetch_object($sql->mysqry)) {
									$respostas[$evolucao->id][$x->id_pergunta]=$x;
								}
							}
						?>
						<fieldset>
							<legend><span class="badge">2</span> Preencha o formulário</legend>
							<?php
							$cont=1;
							if(count($perguntas)==0) echo "<center>Anamnese não configurada!</center>";
							foreach($perguntas as $p) {

								if(is_object($evolucao)) {
									if(isset($respostas[$evolucao->id][$p->id])) {
										$values["resposta_texto_$p->id"]=utf8_encode($respostas[$evolucao->id][$p->id]->resposta_texto);
										$values["resposta_$p->id"]=utf8_encode($respostas[$evolucao->id][$p->id]->resposta);
									}
								}
							?>
							<dl class="js-pergunta" data-obg="<?php echo $p->obrigatorio;?>" data-tipo="<?php echo $p->tipo;?>">
								<dt><?php echo $cont.". ".utf8_encode($p->pergunta);?></dt>
								<dd>
									<?php
									if($p->tipo=="nota") {
										for($i=0;$i<=10;$i++) {
									?>
										<label><input type="radio" name="resposta_<?php echo $p->id;?>" value="<?php echo $i;?>"<?php echo (isset($values["resposta_$p->id"]) and $values["resposta_$p->id"]==$i)?"checked":"";?> /> <?php echo $i;?></label>
									<?php
										}
									} else if($p->tipo=="simnao") {
									?>
										<label><input type="radio" name="resposta_<?php echo $p->id;?>" value="SIM"<?php echo (isset($values["resposta_$p->id"]) and $values["resposta_$p->id"]=='SIM')?"checked":"";?> /> Sim</label>
										<label><input type="radio" name="resposta_<?php echo $p->id;?>" value="NAO"<?php echo (isset($values["resposta_$p->id"]) and $values["resposta_$p->id"]=='NAO')?"checked":"";?> /> Não</label>
									<?php
									} else if($p->tipo=="simnaotexto") {
									?>

										<label><input type="radio" name="resposta_<?php echo $p->id;?>" value="SIM"<?php echo (isset($values["resposta_$p->id"]) and $values["resposta_$p->id"]=='SIM')?"checked":"";?> /> Sim</label>
										<label><input type="radio" name="resposta_<?php echo $p->id;?>" value="NAO"<?php echo (isset($values["resposta_$p->id"]) and $values["resposta_$p->id"]=='NAO')?"checked":"";?> /> Não</label>

											<textarea name="resposta_texto_<?php echo $p->id;?>" style="height:150px;" class=""><?php echo isset($values["resposta_texto_$p->id"])?$values["resposta_texto_$p->id"]:"";?></textarea>
										
									<?php
									} else if($p->tipo=="texto") {
									?>
										<textarea name="resposta_texto_<?php echo $p->id;?>" style="height:150px;" class=""><?php echo isset($values["resposta_texto_$p->id"])?$values["resposta_texto_$p->id"]:"";?></textarea>
									<?php
									} 
									?>
								</dd>
							</dl>
							<?php
								$cont++;
							}
							/*
							?>
							<dl>
								<dt>2. Saúde Geral</dt>
								<dd><textarea name=""></textarea></dd>
							</dl>
							<dl>
								<dt>3. Já foi submetido a algum tipo de intervenção cirúrgica?</dt>
								<dd>
									<label><input type="radio" name="campo" value="0" />Não</label>
									<label><input type="radio" name="campo" value="1" />Sim</label>
								</dd>
							</dl>*/
							?>

						</fieldset>
						<?php
						}
						?>
					</form>

				</section>
				

			</div>				
		</section>
			
		</section>
		
<?php
include "includes/footer.php";
?>