<?php
	include "includes/header.php";
	include "includes/nav.php";

	$_table=$_p."pacientes";
	$_page=basename($_SERVER['PHP_SELF']);

	$_profissionais=array();
	$sql->consult($_p."colaboradores","id,nome,calendario_iniciais,foto,calendario_cor","where tipo_cro<>'' and lixo=0 order by nome asc");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_profissionais[$x->id]=$x;
	}


	$paciente=$cnt='';
	if(isset($_GET['id_paciente']) and is_numeric($_GET['id_paciente'])) {
		$sql->consult($_p."pacientes","*","where id='".$_GET['id_paciente']."'");
		if($sql->rows) {
			$paciente=mysqli_fetch_object($sql->mysqry);
			$cnt=$paciente;
		}
	}

	$_regioesOpcoes=array();
	$sql->consult($_p."parametros_procedimentos_regioes_opcoes","*","order by titulo asc");
	while($x=mysqli_fetch_object($sql->mysqry)) $_regioesOpcoes[$x->id_regiao][]=$x;

	$_regioes=array();
	$sql->consult($_p."parametros_procedimentos_regioes","*","");
	while($x=mysqli_fetch_object($sql->mysqry)) $_regioes[$x->id]=$x;

	$_procedimentos=array();
	$sql->consult($_p."parametros_procedimentos","*","where lixo=0");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_procedimentos[$x->id]=$x;
	}

	$values=array('profissionais'=>array());
	$evolucao='';
	$evolucaoAgenda='';
	if(isset($_GET['edita']) and is_numeric($_GET['edita'])) {	
		$sql->consult($_p."pacientes_evolucoes","*","where id='".$_GET['edita']."' and id_tipo=8");
		if($sql->rows) {
			$evolucao=mysqli_fetch_object($sql->mysqry);

			$sql->consult($_p."agenda","*","where id_evolucao=$evolucao->id and lixo=0");
			if($sql->rows) {
				$evolucaoAgenda=mysqli_fetch_object($sql->mysqry);

				if(!empty($evolucaoAgenda->profissionais)) {
					$values['profissionais']=explode(",",$evolucaoAgenda->profissionais);
				}
 			} 
		} else {
			$jsc->jAlert("Procedimento Aprovado não encontrado!","erro","document.location.href='pg_contatos_pacientes_evolucao.php?id_paciente='".$paciente->id."'");
			die();
		}
	}


	if(isset($_POST['acao'])) {

		$profissionais='';
		if(isset($_POST['profissionais']) and is_array($_POST['profissionais'])) {
			$profissionais=implode(",",$_POST['profissionais']);
		}

		$vSQL="pconsulta_data='".addslashes(invDate($_POST['agenda_data']))."',
				pconsulta_tempo='".addslashes(($_POST['agenda_duracao']))."',
				pconsulta_profissionais=',".$profissionais.",',
				obs='".addslashes(utf8_decode($_POST['obs']))."'";
		if(is_object($evolucao)) {
			$id_evolucao=$evolucao->id;
			$sql->update($_p."pacientes_evolucoes",$vSQL,"where id=$id_evolucao");
		} else {
			// id_tipo = 8 -> Proxima consulta
			$sql->consult($_p."pacientes_evolucoes","*","WHERE data > NOW() - INTERVAL 1 MINUTE and 
																					id_paciente=$paciente->id and
																					id_tipo=8 and  
																					id_usuario=$usr->id");	
			if($sql->rows) {
				$e=mysqli_fetch_object($sql->mysqry);
				$id_evolucao=$e->id;
				$sql->update($_p."pacientes_evolucoes",$vSQL,"where id=$id_evolucao");
			} else {
				$sql->add($_p."pacientes_evolucoes",$vSQL.",data=now(),
														id_tipo=8,
														id_paciente=$paciente->id,
														id_usuario=$usr->id");
				$id_evolucao=$sql->ulid;
			}
		}

		$profissionais=array();
		if(isset($_POST['profissionais']) and is_array($_POST['profissionais'])) {
			$pAux=$_POST['profissionais'];
			foreach($pAux as $id_profissional) {
				if(is_numeric($id_profissional)) $profissionais[]=$id_profissional;
			}
		}



		/*$agendaData='';
		if(isset($_POST['agenda_data']) and !empty($_POST['agenda_data'])) {
			list($_dia,$_mes,$_ano)=explode("/",$_POST['agenda_data']);
			if(checkdate($_mes, $_dia, $_ano)) {
				$agendaData=$_ano."-".$_mes."-".$_dia;
			}
		}

		$vSQLAgenda="id_paciente=$paciente->id,
						procedimentos='".addslashes(utf8_decode($_POST['procedimentosJSON']))."',
						profissionais=',".implode(",",$profissionais).",',
						id_cadeira=0,
						id_status=1,
						agenda_data='".$agendaData."',
						agenda_duracao='".addslashes($_POST['agenda_duracao'])."',
						id_evolucao=$id_evolucao
						";


		if(empty($evolucaoAgenda)) {
			$sql->consult($_p."agenda","*","WHERE data > NOW() - INTERVAL 1 MINUTE and 
																				id_paciente=$paciente->id and 
																				id_evolucao=$id_evolucao");	
			if($sql->rows) {
				$x=mysqli_fetch_object($sql->mysqry);
				$sql->update($_p."agenda",$vSQLAgenda.",data_atualizacao=now()","where id=$x->id");
			} else {
				$sql->add($_p."agenda",$vSQLAgenda.",data=now(),id_usuario=$usr->id");
			}
		} else {

			$sql->update($_p."agenda",$vSQLAgenda,"where id=$evolucaoAgenda->id");
		}*/


		$jsc->jAlert("Evolução salva com sucesso!","sucesso","document.location.href='pg_contatos_pacientes_evolucao.php?id_paciente=$paciente->id'");
		die();
				
		
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
					$exibirEvolucaoNav=1;
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

				<section class="js-evolucao-adicionar" id="evolucao-proxima-consulta" style="display:;">

					<form class="form formulario-validacao" method="post">

						<textarea name="procedimentosJSON" class="js-agenda-procedimentoJSON" style="display:none;"></textarea>
						<input type="hidden" name="acao" value="wlib" />
						<input type="hidden" name="id_evolucao" value="<?php echo is_object($evolucao)?$evolucao->id:0;?>" />
						<div class="grid grid_3">
						
						<fieldset style="grid-column:span 2">
							<legend><span class="badge">1</span> Agende a próxima consulta</legend>

							<div class="colunas6">
								<dl>
									<dt>Próximo dia</dt>
									<dd><input type="text" name="agenda_data" class="datecalendar obg" autocomplete="off" value="<?php echo is_object($evolucao)?date('d/m/Y',strtotime($evolucao->pconsulta_data)):'';?>" /></dd>
								</dl>
								<dl>
									<dt>Tempo de cadeira</dt>
									<dd>
										<select name="agenda_duracao" class="obg">
											<?php
											$possuiDuracao=false;
											foreach($optAgendaDuracao as $v) {
												echo '<option value="'.$v.'"'.((is_object($evolucao) and $evolucao->pconsulta_tempo==$v)?' selected':'').'>'.$v.'</option>';
											}

											//if(!empty($values['agenda_duracao']) and $possuiDuracao===false) echo '<option value="'.$values['agenda_duracao'].'" selected>'.$values['agenda_duracao'].'</option>';
											?>
										</select><div class="input-info">min</div>
									</dd>
								</dl>
								<dl class="dl4">
									<dt>Profissionais</dt>
									<dd>
										<select name="profissionais[]" class="chosen noupper" data-placeholder="Profissionais..." multiple>
										<option value=""></option>
										<?php
										if(is_object($evolucao) and !empty($evolucao->pconsulta_profissionais)) $values['profissionais']=explode(",",$evolucao->pconsulta_profissionais);
										foreach($_profissionais as $p) {
											echo '<option value="'.$p->id.'"'.(in_array($p->id, $values['profissionais'])?' selected':'').'>'.utf8_encode($p->nome).'</option>';
										}
										?>
									</select>
									</dd>
								</dl>
							</div>
							<?php /*<script type="text/javascript">
								var procedimentos = <?php echo (is_object($evolucaoAgenda) and !empty($evolucaoAgenda->procedimentos))?"JSON.parse('".$evolucaoAgenda->procedimentos."')":"[]";?>;

									const agendaProcedimentosRemover = (index) => {
										let cont = 0;

										procedimentos=procedimentos.filter(x=> {
											if(cont++==index) return false;
											else return x;
										});

										console.log(procedimentos);

										agendaProcedimentosListar();
									}

									const agendaProcedimentosListar = () => {
										$(`.js-agenda-tableProcedimento tr.item`).remove();
										$(`.js-agenda-id_procedimento option`).prop('disabled',false);
										procedimentos.forEach(x => {
											let opcoesTxt='-';
											if(x.opcoes.length>0) {
												opcoesTxt = `<ul>`;
												x.opcoes.forEach(y => {
													opcoesTxt+=`<li>${y.titulo}</li>`;
												});
												opcoesTxt += `</ul>`;
											} 

											let html = `<tr class="item">
															<td>${x.procedimento}</td>
															<td>${x.regiao}</td>
															<td>${opcoesTxt}</td>
															<td>
																<a href="javascript:;" class="js-procedimentos-editar registros__acao registros__acao_sec"><i class="iconify" data-icon="bx:bxs-edit-alt"></i></a>
																<a href="javascript:;" class="js-procedimentos-remover registros__acao registros__acao_sec"><i class="iconify" data-icon="bx:bxs-trash"></i></a>
															</td>
														</tr>`;

											$(`.js-agenda-tableProcedimento`).append(html);

											$('.js-agenda-id_procedimento').find(`option[value=${x.id_procedimento}]`).prop('disasbled',true);
										});
										$('.js-agendonChangeDateTimea-id_procedimento').trigger('chosen:updated')
										$('.js-agenda-procedimentoJSON').val(JSON.stringify(procedimentos))
									}
								$(function(){
									$.fn.autoResize = function(obj) {
										if($(this).prop('tagName') == 'TEXTAREA') {
											
											$(this).css("overflow-y", "hidden");
											$(this).css("resize", "none");

											$(this).keyup(function(){
												arr = $(this).val().split("\n");
												$(this).attr("rows", arr.length);	
											
												if(obj && "step" in obj) {
													obj.step({count: arr.length-1});
												}
											});

										}
									}

									$('textarea').autoResize();
									$('textarea').trigger('keyup')

									agendaProcedimentosListar();

									$('table.js-agenda-tableProcedimento').on('click','.js-procedimentos-remover',function(){
										let index = $(this).index('table.js-agenda-tableProcedimento .js-procedimentos-remover');
										swal({   title: "Atenção",   text: "Você tem certeza que deseja remover este procedimento?",   type: "warning",   showCancelButton: true,   confirmButtonColor: "#DD6B55",   confirmButtonText: "Sim!",   cancelButtonText: "Não",   closeOnConfirm: false,   closeOnCancel: false }, function(isConfirm){   if (isConfirm) {    agendaProcedimentosRemover(index);swal.close();  } else {   swal.close();   } });
									});

									$('.js-agenda-tableProcedimento').on('click','.js-procedimentos-editar',function(){
										let index = $(this).index('table.js-agenda-tableProcedimento .js-procedimentos-editar');
										let cont = 0;
										procedimentoEdicao = procedimentos.filter(x=> {
											if(cont++==index) return x;
											else return false;
										});

										$('select.js-agenda-id_procedimento').val('');
										$(`.js-regiao`).hide();
										$(`.js-regiao-descritivo`).hide().find('dd input').val(``);
										$(`.js-procedimento-btnOk`).hide();
										$(`.js-regiao`).find('select option:selected').prop('selected',false)
										$(`.js-regiao`).find('select').trigger('chosen:updated');

										if(procedimentoEdicao.length>0) {
											let proc = procedimentoEdicao[0];
											$('select.js-agenda-id_procedimento').val(proc.id_procedimento).trigger('change');
											let id_regiao = $('select.js-agenda-id_procedimento option:selected').attr('data-id_regiao');

											if(proc.opcoes.length>0) {
												proc.opcoes.forEach(x=> {
													$(`select.js-regiao-${id_regiao}-select`).find(`option[value=${x.id}]`).prop('selected',true);
												})
											}
											$(`select.js-regiao-${id_regiao}-select`).trigger('chosen:updated');
										}

									});

									$('select.js-agenda-id_procedimento').change(function(){

										let id = $(this).val();

										if(id.length>0) {
											let id_regiao = $(this).find('option:selected').attr('data-id_regiao');
											let regiao = $(this).find('option:selected').attr('data-regiao');

											$(`.js-regiao`).hide();
											$(`.js-regiao-${id_regiao}`).show();
											$(`.js-regiao-${id_regiao}`).find('select').chosen({hide_results_on_select:false,allow_single_deselect:true});
											$(`.js-regiao-descritivo`).show().find('dd input').val(regiao);

											$(`.js-procedimento-btnOk`).show();
										} else {
											$(`.js-regiao`).hide();
											$(`.js-regiao-descritivo`).hide().find('dd input').val(``);
											$(`.js-procedimento-btnOk`).hide();
										}
									}); 

									$('.js-procedimento-btnOk a').click(function(){
										let id_procedimento = $('select.js-agenda-id_procedimento').val();
										let procedimento = $('select.js-agenda-id_procedimento option:selected').text();
										let id_regiao = $('select.js-agenda-id_procedimento option:selected').attr('data-id_regiao');
										let regiao = $('select.js-agenda-id_procedimento option:selected').attr('data-regiao');

										if(id_procedimento.length==0) {
											swal({title: "Erro!", text: "Selecione o Procedimento", type:"error", confirmButtonColor: "#424242"});
										} else {	
											let opcoes = [];
											let erro = ``;

											if($(`.js-regiao-${id_regiao}`).length>0) {
												if($(`.js-regiao-${id_regiao}-select`).val()===null || $(`.js-regiao-${id_regiao}-select`).val()==="") {
													erro=`Selecione a Região!`;
												} else {
													$(`.js-regiao-${id_regiao}-select option:selected`).each(function(index,el){
														let itemOp={};
														itemOp.id=$(el).val();
														itemOp.titulo=$(el).text();
														opcoes.push(itemOp)
													});
												}
											}

											if(erro.length==0) {
												let item = {};
												item.id_procedimento=id_procedimento;
												item.procedimento=procedimento;
												item.regiao=regiao;
												item.opcoes=opcoes;

												let jaPossui=false;
												proc = procedimentos.map(x => { 
													if(x.id_procedimento==id_procedimento) {
														jaPossui=true;
														return item;
													}
													else return x;
												});
												if(jaPossui===false) proc.push(item);
												
												procedimentos=proc;

												console.log(procedimentos);
												agendaProcedimentosListar();
												$('select.js-agenda-id_procedimento').val('');
												$(`.js-regiao`).hide();
												$(`.js-regiao-descritivo`).hide().find('dd input').val(``);
												$(`.js-procedimento-btnOk`).hide();
												$(`.js-regiao`).find('select option:selected').prop('selected',false)
												$(`.js-regiao`).find('select').trigger('chosen:updated');

											} else {
												swal({title: "Erro!", text: erro, type:"error", confirmButtonColor: "#424242"});
											}
										}
									});
								});
							</script>
							<div class="box-filtros clearfix js-agenda-formProcedimento" style="display:">
								<dl>
									<dd>
										<dt>Procedimento</dt>
										<select class="js-agenda-id_procedimento chosen" data-placeholder="Selecione o procedimento...">
											<option value=""></option>
											<?php
											foreach($_procedimentos as $p) {
												echo '<option value="'.$p->id.'" data-id_regiao="'.$p->id_regiao.'" data-regiao="'.(isset($_regioes[$p->id_regiao])?utf8_encode($_regioes[$p->id_regiao]->titulo):"-").'">'.utf8_encode($p->titulo).'</option>';
											}
											?>
										</select>
									</dd>
								</dl>
								<div class="colunas4">
									
									<dl class="js-regiao-descritivo" style="display:none;">
										<dd><input type="text" disabled /></dd>
									</dl>
									<dl class="js-regiao-2 js-regiao dl3" style="display: none;">							
										<dd>
											<select class="js-regiao-2-select" multiple data-placeholder="Arcada(s)">
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
									<dl class="js-regiao-3 js-regiao dl3" style="display: none">
										<dd>
											<select class="js-regiao-3-select" multiple data-placeholder="Quadrante(s)">
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
									<dl class="js-regiao-4 js-regiao dl3" style="display: none">
										<dd>
											<select class="js-regiao-4-select" multiple data-placeholder="Dente(s)">
												<option value=""></option>
												<?php
												if(isset($_regioesOpcoes[4])) {
													foreach($_regioesOpcoes[4] as $o) {
														echo '<option value="'.$o->id.'">'.utf8_encode($o->titulo).'</option>';
													}
												}
												?>
											</select>
										</dd>
									</dl>
									<dl class="js-procedimento-btnOk" style="display: none">
										<?php /* <dd><a href="javascript:;" class="button button__sec"><i class="iconify" data-icon="bx-bx-plus"></i></a></dd> * ?>
										<dd>
											<a href="javascript:;" class="button"><i class="iconify" data-icon="ic-baseline-add"></i> Adicionar</a>
										</dd>
									</dl>
								</div>
								
							</div>

							<div class="registros">
								<table class="js-agenda-tableProcedimento">
									<tr>
										<th>Procedimento</th>
										<th>Tipo</th>
										<th>Região</th>
										<th style="width:110px;"></th>
									</tr>
								</table>
							</div>*/?>
						</fieldset>

						<fieldset>
							<legend><span class="badge">2</span> Preencha o histórico</legend>
							<dl style="height:100%;">
								<dd style="height:100%;"><textarea name="obs" style="height:100%;" class="noupper"><?php echo is_object($evolucao)?utf8_encode($evolucao->obs):'';?></textarea></dd>
							</dl>
						</fieldset>
					</form>

				</section>
				

			</div>				
		</section>
			
		</section>
		
<?php
include "includes/footer.php";
?>