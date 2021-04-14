<?php
	require_once("../lib/conf.php");
	$dir="../";
	require_once("../usuarios/checa.php");

	if(isset($_POST['ajax'])) {
		$rtn=array();
		$unidade='';
		if(isset($_POST['id_unidade']) and is_numeric($_POST['id_unidade']) and isset($_optUnidades[$_POST['id_unidade']])) {
			$unidade=$_optUnidades[$_POST['id_unidade']];
		}

		if($_POST['ajax']=="agendamentoPersistir") {
			
			if(empty($unidade)) {
				$rtn=array('success'=>false,'error'=>'Unidade não encontrado!');
			} else {

				
				$profissional='';
				if(isset($_POST['id_profissional']) and is_numeric($_POST['id_profissional'])) {
					$sql->consult($_p."profissionais","*","where id='".$_POST['id_profissional']."' and lixo=0");
					if($sql->rows) {
						$profissional=mysqli_fetch_object($sql->mysqry);

						$profissionalUnidades=explode(",",$profissional->unidades);
					}
				}

				$profissionais=array();
				if(isset($_POST['profissionais']) and !empty($_POST['profissionais'])) {
					$pAux=explode(",",$_POST['profissionais']);
					foreach($pAux as $id_profissional) {
						if(is_numeric($id_profissional)) $profissionais[]=$id_profissional;
					}
				}

				$cadeira='';
				if(isset($_POST['id_cadeira']) and is_numeric($_POST['id_cadeira'])) {
					$sql->consult($_p."parametros_cadeiras","*","where id='".$_POST['id_cadeira']."' and id_unidade=$unidade->id and lixo=0");
					if($sql->rows) {
						$cadeira=mysqli_fetch_object($sql->mysqry);
					}
				}
				$status='';
				if(isset($_POST['id_status']) and is_numeric($_POST['id_status'])) {
					$sql->consult($_p."agenda_status","*","where id='".$_POST['id_status']."' and lixo=0");
					if($sql->rows) {
						$status=mysqli_fetch_object($sql->mysqry);
					}
				}
				$agendaData='';
				if(isset($_POST['agenda_data']) and !empty($_POST['agenda_data'])) {
					list($_dia,$_mes,$_ano)=explode("/",$_POST['agenda_data']);
					if(checkdate($_mes, $_dia, $_ano)) {
						$agendaData=$_ano."-".$_mes."-".$_dia;
					}
				}

				$agendaHora='';
				if(isset($_POST['agenda_hora']) and !empty($_POST['agenda_hora'])) {
					list($_h,$_m)=explode(":",$_POST['agenda_hora']);
					if(is_numeric($_h) and is_numeric($_m)) {
						$agendaHora=$_h.":".$_m;
					}
				}

				$agenda='';
				if(isset($_POST['id_agenda']) and is_numeric($_POST['id_agenda'])) {
					$sql->consult($_p."agenda","*","where id='".$_POST['id_agenda']."'");
					if($sql->rows) {
						$agenda=mysqli_fetch_object($sql->mysqry);
					}
				}

				if(count($profissionais)==0) {
					$rtn=array('success'=>false,'error'=>'Profissional não encontrado!');
				} 
				/*else if(!in_array($profissional->id, $profissionalUnidades) and 1==2) { // habilitar quando restricoes de unidades estiver ativado
					$rtn=array('success'=>false,'error'=>'Profissional não encontrado nesta unidade!');
				} */ 
				else if(empty($cadeira)) {
					$rtn=array('success'=>false,'error'=>'Selecione a cadeira!');
				} 
				else if(empty($status)) {
					$rtn=array('success'=>false,'error'=>'Selecione o status!');
				} 
				if(empty($agendaData)) {
					$rtn=array('success'=>false,'error'=>'Data inválida!');
				} else if(empty($agendaHora)) {
					$rtn=array('success'=>false,'error'=>'Hora inválida!');
				} else {
					$novoPaciente=false;
					$paciente=$pacienteUnidades='';
					$erro='';

					if(isset($_POST['novoPaciente'])) {
						$novoPaciente=true;

						if(isset($_POST['cpf']) and !empty($_POST['cpf'])) {
							$sql->consult($_p."pacientes","*","where cpf='".addslashes(cpf($_POST['cpf']))."' and lixo=0");
							if($sql->rows) {
								$erro='Este CPF já possui cadastro!';
							}
						}

						if(empty($erro)) {
							$vSQLPaciente="data=now(),
											nome='".addslashes(strtoupperWLIB(utf8_decode($_POST['nome'])))."',
											cpf='".addslashes(cpf(utf8_decode($_POST['cpf'])))."',
											data_nascimento='".addslashes(invDate($_POST['data_nascimento']))."',
											telefone1='".addslashes(telefone($_POST['telefone1']))."',
											indicacao_tipo='".addslashes(utf8_decode($_POST['indicacao_tipo']))."',
											indicacao='".addslashes(utf8_decode($_POST['indicacao']))."',
											unidades=',$unidade->id,'";

							$sql->add($_p."pacientes",$vSQLPaciente);
							$id_paciente=$sql->ulid;
							$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='insert',vsql='".addslashes($vSQLPaciente)."',tabela='".$_p."pacientes',id_reg='".$id_paciente."'");

							$sql->consult($_p."pacientes","*","where id=$id_paciente");

							if($sql->rows) {
								$paciente=mysqli_fetch_object($sql->mysqry);
								$pacienteUnidades=explode(",",$paciente->unidades);
							}
						}

					}  

					if(isset($_POST['id_paciente']) and is_numeric($_POST['id_paciente'])) {
						$sql->consult($_p."pacientes","*","where id='".$_POST['id_paciente']."' and lixo=0");
						if($sql->rows) {
							$paciente=mysqli_fetch_object($sql->mysqry);
							$pacienteUnidades=explode(",",$paciente->unidades);
						}
					}
					
					if(!empty($erro)) {
						$rtn=array('success'=>false,'error'=>$erro);
					}
					else if(empty($paciente)) {
						if($novoPaciente===true) $rtn=array('success'=>false,'error'=>'Algum erro ocorreu durante o cadastro do novo paciente. Tente novamente!');
						else $rtn=array('success'=>false,'error'=>'Paciente não encontrado!');
					} else if(!in_array($unidade->id, $pacienteUnidades) and 1==2) { // habilitar quando restricoes de unidades estiver ativado
						$rtn=array('success'=>false,'error'=>'Paciente não encontrado nesta unidade!');
					} else {

						$agendaData.=" ".$agendaHora;

						$vSQL="id_paciente=$paciente->id,
								id_unidade=$unidade->id,
								procedimentos='".addslashes(json_encode($_POST['procedimentosJSON']))."',
								profissionais=',".implode(",",$profissionais).",',
								id_cadeira=$cadeira->id,
								id_status=$status->id,
								agenda_data='".$agendaData."',
								agenda_duracao='".addslashes($_POST['agendaDuracao'])."'
								";

						if(isset($_POST['obs'])) $vSQL.=",obs='".addslashes(utf8_decode($_POST['obs']))."'";

						if(isset($_POST['clienteChegou']) and $_POST['clienteChegou']==1) $vSQL.=",clienteChegou=1";
						else $vSQL.=",clienteChegou=0";

						if(isset($_POST['emAtendimento']) and $_POST['emAtendimento']==1) $vSQL.=",emAtendimento=1";
						else $vSQL.=",emAtendimento=0";


						if(is_object($agenda)) {
							$vWHERE="where id=$agenda->id";
							$sql->update($_p."agenda",$vSQL,$vWHERE);
							$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='update',vsql='".addslashes($vSQL)."',vwhere='".addslashes($vWHERE)."',tabela='".$_p."agenda',id_reg='".$agenda->id."'");
						} else {
							$sql->consult($_p."agenda","*","where agenda_data='".$agendaData."' and id_paciente=$paciente->id and id_unidade=$unidade->id");
							if($sql->rows) {
								$agenda=mysqli_fetch_object($sql->mysqry);
								$vWHERE="where id=$agenda->id";
								$sql->update($_p."agenda",$vSQL,$vWHERE);
								$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='update',vsql='".addslashes($vSQL)."',vwhere='".addslashes($vWHERE)."',tabela='".$_p."agenda',id_reg='".$agenda->id."'");
							} else {
								$vSQL.=",data=now(),id_usuario=$usr->id";
								$sql->add($_p."agenda",$vSQL);
								$id_agenda=$sql->ulid;
								$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='insert',vsql='".addslashes($vSQL)."',tabela='".$_p."agenda',id_reg='".$id_agenda."'");
							}
						}

						$rtn=array('success'=>true);
					}
					
				}
				
			}

		} else if($_POST['ajax']=="agendamentoVerificarDisponibilidade") {

			if(is_object($unidade)) {

				$profissionais=array();
				if(isset($_POST['profissionais']) and !empty($_POST['profissionais'])) {
					
					$profissionaisID=explode(",",$_POST['profissionais']);
					if(count($profissionaisID)>0) {
						$sql->consult($_p."profissionais","*","where id IN (".implode(",",$profissionaisID).") and lixo=0");
						if($sql->rows) {
							while($x=mysqli_fetch_object($sql->mysqry)) {
								$profissionais[$x->id]=$x;
							}
						}
					}
				}

				$paciente='';
				if(isset($_POST['id_paciente']) and is_numeric($_POST['id_paciente'])) {
					$sql->consult($_p."pacientes","*","where id='".$_POST['id_paciente']."' and lixo=0");
					if($sql->rows) {
						$paciente=mysqli_fetch_object($sql->mysqry);
					}
				}

				$cadeira='';
				if(isset($_POST['id_cadeira']) and is_numeric($_POST['id_cadeira'])) {
					$sql->consult($_p."parametros_cadeiras","*","where id='".$_POST['id_cadeira']."' and id_unidade=$unidade->id and lixo=0");
					if($sql->rows) {
						$cadeira=mysqli_fetch_object($sql->mysqry);
					}
				}

				$agendaData='';
				if(isset($_POST['agenda_data']) and !empty($_POST['agenda_data'])) {
					list($_d,$_m,$_a)=@explode("/",$_POST['agenda_data']);

					if(checkdate($_m, $_d, $_a)) {
						$agendaData=$_a."-".$_m."-".$_d;
					}
				}
				$agendaHora='';
				if(isset($_POST['agenda_hora']) and !empty($_POST['agenda_hora'])) {
					list($_h,$_m)=@explode(":",$_POST['agenda_hora']);
					if(is_numeric($_h) and is_numeric($_m)) {
						$agendaHora=$_h.":".$_m;
					}
				}

				if(!empty($agendaData)) {
					if(!empty($agendaHora)) {
						if(count($profissionais)>0) {
							if(is_object($cadeira)) {

								$validacao=array();
								$agendaData.=" ".$agendaHora;
								$agendaDataDia=date('w',strtotime($agendaData));
								foreach($profissionais as $p) {
									$where="where dia=$agendaDataDia and 
													id_profissional=$p->id and 
													id_cadeira=$cadeira->id and 
													inicio<='".$agendaHora."' and 
													fim>='".$agendaHora."'";
									//echo $where;

									$sql->consult($_p."profissionais_horarios","*",$where);
									
									$atende=$sql->rows>0?1:0;

									$validacao[]=array('id_profissional'=>$p->id,
														'profissional'=>utf8_encode($p->nome),
														'atende'=>$atende);
								}
								// verifica se o profissinal atende nesta cadeira
								/*$sql->consult($_p."profissionais_horarios","*","where id_profissional=$profissional->id and id_cadeira=$cadeira->id");
								if($sql->rows) {
									while($x=mysqli_fetch_object($sql->mysqry)) {

									}
								} */


								//$where="WHERE agenda_data='".$agendaData."' and id_profissional=$profissional->id and id_cadeira=$cadeira->id";
								//echo $where;

								$rtn=array('success'=>true,'validacao'=>$validacao);

							} else {
								$rtn=array('success'=>false,'error'=>'Cadeira não definida/inválida!');
							}
						} else {
							$rtn=array('success'=>false,'error'=>'Profissional não definido/inválido!');
						}
					} else {
						$rtn=array('success'=>false,'error'=>'Horário de agendamento não definida!');
					}
				} else {
					$rtn=array('success'=>false,'error'=>'Data de agendamento não definida!');
				}
			} else {
				$rtn=array('success'=>false,'error'=>'Unidade não especificada!');
			}


		}

		header("Content-type: application/json");
		echo json_encode($rtn);
		die();
	}	
	$jsc = new Js();

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

	$unidade='';
	if(isset($_GET['id_unidade']) and is_numeric($_GET['id_unidade']) and isset($_optUnidades[$_GET['id_unidade']])) {
		$unidade=$_optUnidades[$_GET['id_unidade']];
	}
	if(empty($unidade)) {
		$jsc->jAlert("Unidade não encontrada!","erro","$.fancybox.close()");
		die();
	}
	$campos=explode(",","id_paciente,profissionais,id_cadeira,id_status,clienteChegou,emAtendimento,agenda_data,agenda_hora,agenda_duracao,obs,procedimentos");
	foreach($campos as $v) {
		if($v=="profissionais") $values[$v]=array();
		else $values[$v]='';
	}


	if(isset($_GET['data_agenda']) and !empty($_GET['data_agenda'])) {
		list($_ano,$_mes,$_dia)=explode("-",$_GET['data_agenda']);
		if(checkdate($_mes, $_dia, $_ano)) {
			$values['agenda_data']=$_dia."/".$_mes."/".$_ano;
		}
	}

	$agenda='';
	if(isset($_GET['id_agenda']) and is_numeric($_GET['id_agenda'])) {
		$sql->consult($_p."agenda","*","where id=".$_GET['id_agenda']." and id_unidade=$unidade->id and lixo=0");
		if($sql->rows) {
			$agenda=mysqli_fetch_object($sql->mysqry);

			foreach($campos as $v) {
				if($v=="agenda_data") $values[$v]=date('d/m/Y',strtotime($agenda->$v));
				else if($v=="agenda_hora") $values[$v]=date('H:i',strtotime($agenda->agenda_data));
				else if($v=="profissionais") {
					$values[$v]=explode(",",$agenda->$v);
				}
				else  {
					$values[$v]=utf8_encode($agenda->$v);
				}
			}

		}
	}

	$_pacientes=array();
	$sql->consult($_p."pacientes","*","where lixo=0 order by nome asc");//"where unidades like '%,$unidade->id,%' and lixo=0 order by nome asc");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_pacientes[$x->id]=$x;
	}
	$_profissionais=array();
	$sql->consult($_p."profissionais","*","where lixo=0 order by nome asc");//"where unidades like '%,$unidade->id,%' and lixo=0 order by nome asc");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_profissionais[$x->id]=$x;
	}
	$_cadeiras=array();
	$sql->consult($_p."parametros_cadeiras","*","where id_unidade=$unidade->id and lixo=0 order by titulo asc");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_cadeiras[$x->id]=$x;
	}
	$_status=array();
	$sql->consult($_p."agenda_status","*","where  lixo=0 order by titulo asc");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_status[$x->id]=$x;
	}
		$_pacienteIndicacoes=array();
	$sql->consult($_p."parametros_indicacoes","*","where lixo=0 order by titulo asc");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_pacienteIndicacoes[$x->id]=$x;
	}
?>
<script type="text/javascript">
	var id_unidade = <?php echo $unidade->id;?>;

	var id_agenda = '<?php echo is_object($agenda)?$agenda->id:'';?>';



	
	$(function(){
		verificaAgendamento();

		$('select[name=id_paciente]').change(function(){
			let telefone = $(this).find('option:selected').attr('data-telefone');
			if($(this).find('option:selected').length==0) $('.js-telefone input').val('');
			else $('.js-telefone input').val(telefone);
		}).trigger('change')

		$('.js-agenda-verifica').change(function(){
			verificaAgendamento();
		});
		$('.js-maskNumber').keyup(function() {
			let regex= /[^(\d+)\.(\d+)]/g;
			let numero = $(this).val().replace(regex,'');
			numero=eval(numero);
			$(this).val(numero);
		});

		$('.chosen').chosen();
		$('.agendaData').datetimepicker({
			timepicker:false,
			format:'d/m/Y',
			scrollMonth:false,
			scrollTime:false,
			scrollInput:false,
		});

		$('.agendaHora').datetimepicker({
			  datepicker:false,
		      format:'H:i',
		      pickDate:false
		});

		$("input.agendaData,input.data").inputmask("99/99/9999");
		$("input.telefone").inputmask("(99) 99999-9999");
		$("input[name=cpf]").inputmask("999.999.999-99");
		
		$("input[name=agenda_hora]").inputmask("99:99");

		$('.js-salvar').click(function(){

			let erro=false;
			$('form .obg').each(function(index,elem){
				//console.log($(this).attr('name'));
				if($(this).attr('name')!==undefined && $(this).val().length==0) {
					$(elem).addClass('erro');
					erro=true;
				}
			});

			if(erro===true) {
				swal({title: "Erro!", text: "Complete os campos destacados", type:"error", confirmButtonColor: "#424242"});
				
			} else {
				
				let campos = $('form.js-form-agendamento').serialize();
				let profissionais = $('form.js-form-agendamento .js-profissionais').val();

				let data = `ajax=agendamentoPersistir&id_unidade=${id_unidade}&id_agenda=${id_agenda}&profissionais=${profissionais}&${campos}`;

				$.ajax({
					type:'POST',
					url:'box/boxAgendamento.php',
					data:data,
					success:function(rtn) {
						if(rtn.success) {
							$.fancybox.close();
							calendar.refetchEvents();
							//swal({title: "Sucesso!", text: "Agendamento salvo com sucesso!", type:"success", confirmButtonColor: "#424242"});
						} else if(rtn.error) {
							swal({title: "Erro!", text: rtn.error, type:"error", confirmButtonColor: "#424242"});
						} else {
							swal({title: "Erro!", text: "Agendamento não efetuado. Por favor tente novamente!", type:"error", confirmButtonColor: "#424242"});
						}
					},
					error:function(){
						swal({title: "Erro!", text: "Agendamento não efetuado. Por favor tente novamente!", type:"error", confirmButtonColor: "#424242"});
					}
				})

			}
			return false;
		});

		$('select[name=id_status]').change(function(){
			if($(this).val()==2) {
				$('.js-statusConfirmado').show();
			} else {
				$('.js-statusConfirmado').hide();
			}
		}).trigger('change');
		<?php
		if(empty($agenda)) {
		?>
		$('input[name=novoPaciente]').click(function(){
			pacienteExistente();
		})
		pacienteExistente();
		<?php
		}
		?>
		$('input[name=telefone1]').mobilePhoneNumber({allowPhoneWithoutPrefix: '+55'}).bind('country.mobilePhoneNumber', function(echo, country) {
	      	let countryOut = country || '';
	      	$(this).parent().parent().find('.country').remove();
	      	$(this).before(`<input type="text" diabled style="width:14%;float:left" class="country" value="${countryOut}" />`)
	      }).trigger('keyup');
	})
</script>

<section class="modal">
	<div class="modal__content">

		<header class="caminho">
			<h1 class="caminho__titulo">Agenda</h1>
		</header>

		<?php
		if(empty($agenda)) {
		?>
		<ul class="abas">
			<li><a href="javascript:;" class="active">Paciente</a></li>
			<li><a href="javascript:;" class="js-btn-pessoal">Pessoal</a></li>
		</ul>
		<script type="text/javascript">
			$(function(){ 
				$('.js-btn-pessoal').click(function(){
					$.fancybox.close();
					$.fancybox.open({
				        src: "box/boxAgendamentoPessoal.php?id_unidade=<?php echo $unidade->id;?>",
				        type: "ajax"
				    });
				})
			});
		</script>
		<?php
		}
		?>
		<form method="post" class="form js-form-agendamento">
			<div class="colunas4">
				<?php
				if(empty($agenda)) {
				?>
				<dl class="dl2">
					<dd>
						<label><input type="checkbox" name="novoPaciente" value="1" /> Novo Paciente</label>
					</dd>
				</dl>
				<?php
				}
				?>
			</div>

			<fieldset class="js-paciente js-pacienteExistente">
				<legend>Paciente</legend>
				
				<div class="colunas5">
					<dl class="dl3">
						<dt>Paciente</dt>
						<dd>
							<select name="id_paciente" class="chosen obg">
								<option value=""></option>
								<?php
								foreach($_pacientes as $p) {
									echo '<option value="'.$p->id.'"'.($values['id_paciente']==$p->id?' selected':'').' data-telefone="'.$p->telefone1.'">'.utf8_encode($p->nome).'</option>';
								}
								?>
							</select>
						</dd>
					</dl>
					<dl class="js-telefone">
						<dt>Telefone</dt>
						<dd><input type="text" class="telefone" disabled></dd>
					</dl>
				</div>
			</fieldset>

			<?php
			if(empty($agenda)) {
			?>
			<fieldset class="js-paciente js-pacienteNovo" style="display: none;">
				<legend>Novo Cadastro</legend>
				
				<div class="colunas4">
					<dl>
						<dt>Data do Cadastro</dt>
						<dd><input type="text" name="" value="<?php echo date('d/m/Y H:i');?>" disabled></dd>
					</dl>
					<dl class="dl3">
						<dt>Nome</dt>
						<dd><input type="text" name="nome" value="" /></dd>
					</dl>
				</div>

				<div class="colunas4">
					<dl>
						<dt>CPF</dt>
						<dd><input type="text" name="cpf" value="" class="cpf" /></dd>
					</dl>
					<dl>
						<dt>Data de Nascimento</dt>
						<dd><input type="text" name="data_nascimento" value="" class="data" /></dd>
					</dl>
					<dl class="dl2">
						<dt>Telefone</dt>
						<dd><input type="text" name="telefone1" style="width:85%;float:right;" class="obg" attern="\d*" x-autocompletetype="tel" value="" /></dd>
					</dl>
				</div>

				<div class="colunas4">
					<dl class="dl2">
						<dt>Tipo de Indicação</dt>
						<dd>
							<select name="indicacao_tipo" class="">
								<option value=""></option>
								<?php
								foreach($_pacienteIndicacoes as $v) {
									echo '<option value="'.$v->id.'"'.($values['indicacao_tipo']==$v->id?' selected':'').' data-id="'.$v->id.'">'.utf8_encode($v->titulo).'</option>';
								}
								?>
							</select>
						</dd>
					</dl>
					<dl class="dl2">
						<dt>Indicação</dt>
						<dd>
							<select name="indicacao" class="">
								<option value=""></option>
							</select>
						</dd>
					</dl>
				</div>
			</fieldset>
			<?php
			}
			?>
			<textarea name="procedimentosJSON" class="js-agenda-procedimentoJSON" style="display: none;"></textarea>

			<fieldset>
				<legend>Agendamento</legend>
				
				<dl>
					<dt>Cirurgião Dentista</dt>
					<dd>
						<select class="chosen js-agenda-verifica js-profissionais" multiple>
							<option value=""></option>
							<?php
							foreach($_profissionais as $p) {
								echo '<option value="'.$p->id.'"'.(in_array($p->id, $values['profissionais'])?' selected':'').'>'.utf8_encode($p->nome).'</option>';
							}
							?>
						</select>
					</dd>
				</dl>
				<div class="colunas5">
					<dl>
						<dt>Cadeira</dt>
						<dd>
							<select name="id_cadeira" class="chosen obg js-agenda-verifica">
								<option value=""></option>
								<?php
								foreach($_cadeiras as $p) {
									echo '<option value="'.$p->id.'"'.($values['id_cadeira']==$p->id?' selected':'').'>'.utf8_encode($p->titulo).'</option>';
								}
								?>
							</select>
						</dd>
					</dl>

					<dl>
						<dt>Status</dt>
						<dd>
							<select name="id_status" class="obg">
								<option value="">-</option>
								<?php
								foreach($_status as $p) {
									echo '<option value="'.$p->id.'"'.($values['id_status']==$p->id?' selected':'').'>'.utf8_encode($p->titulo).'</option>';
								}
								?>
							</select>
						</dd>
					</dl>
					<dl>
						<dt>Data</dt>
						<dd><input type="text" name="agenda_data" class="agendaData obg js-agenda-verifica" value="<?php echo $values['agenda_data'];?>" /></dd>
					</dl>
					<dl>
						<dt>Horário</dt>
						<dd><input type="text" name="agenda_hora" class="agendaHora obg js-agenda-verifica" value="<?php echo $values['agenda_hora'];?>" /></dd>
					</dl>
					<dl>
						<dt>Duração (em minutos)</dt>
						<dd><input type="text" name="agendaDuracao" class="obg js-maskNumber" maxlength="3" value="<?php echo $values['agenda_duracao'];?>" /></dd>
					</dl>
				</div>

				<dl style="color:red" id="box-validacoes">
				</dl>
			</fieldset>

			<style type="text/css">
				.js-agenda-tableProcedimento li {float:left;border: solid 1px #CCC;padding:5px;margin: 2px;background:#FFF;border-radius:5px;}
			</style>

			<fieldset>
				<script type="text/javascript">
					var procedimentos = <?php echo !empty($values['procedimentos'])?"JSON.parse(".$values['procedimentos'].")":"[]";?>;

					
					$(function(){
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
								$(`.js-regiao-${id_regiao}`).find('select').chosen();
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

				<legend>Procedimentos</legend>
				<div class="registros">
					<table class="js-agenda-tableProcedimento">
						<tr>
							<th style="width:40%">Procedimento</th>
							<th>Tipo</th>
							<th style="width:40%">Região</th>
							<th></th>
						</tr>
					</table>
				</div>

				<div class="box-filtros clearfix js-agenda-formProcedimento" style="display:">
					<div class="colunas5">
						<dl class="dl2">
							<dt>Procedimento</dt>
							<dd>
								<select class="js-agenda-id_procedimento">
									<option value="">-</option>
									<?php
									foreach($_procedimentos as $p) {
										echo '<option value="'.$p->id.'" data-id_regiao="'.$p->id_regiao.'" data-regiao="'.(isset($_regioes[$p->id_regiao])?utf8_encode($_regioes[$p->id_regiao]->titulo):"-").'">'.utf8_encode($p->titulo).'</option>';
									}
									?>
								</select>
							</dd>
						</dl>
						<dl class="js-regiao-descritivo" style="display:none;">
							<dt>Região</dt>
							<dd><input type="text" disabled /></dd>
						</dl>
						<dl class="js-procedimento-btnOk" style="display: none">
							<dt>&nbsp;</dt>
							<dd><a href="javascript:;" class="button button__sec"><i class="iconify" data-icon="bx-bx-check"></i></a></dd>
						</dl>
					</div>
					<dl class="js-regiao-2 js-regiao" style="display: none;">
						<dt>Arcada(s)</dt>
						<dd>
							<select class="js-regiao-2-select" multiple>
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
					<dl class="js-regiao-3 js-regiao" style="display: none">
						<dt>Quadrante(s)</dt>
						<dd>
							<select class="js-regiao-3-select" multiple>
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
					<dl class="js-regiao-4 js-regiao" style="display: none">
						<dt>Dentes(s)</dt>
						<dd>
							<select class="js-regiao-4-select" multiple>
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
				</div>

			</fieldset>

			<fieldset>
				<legend>Controle</legend>
				<div class="colunas4">

					<dl class="js-statusConfirmado">
						<dd><label><input type="checkbox" name="clienteChegou" value="1"<?php echo $values['clienteChegou']==1?" checked":"";?> /> Cliente chegou</label></dd>
					</dl>
					<dl class="js-statusConfirmado">
						<dd><label><input type="checkbox" name="emAtendimento" value="1"<?php echo $values['emAtendimento']==1?" checked":"";?> /> Em Atendimento</label></dd>
					</dl>
					<?php
					if(is_object($agenda)) {
						$dias=round((strtotime(date('Y-m-d H:i:s'))-strtotime($agenda->data))/(60 * 60 * 24));
						$sql->consult($_p."usuarios","id,nome","where id='".$agenda->id_usuario."'");
						if($sql->rows) {
							$x=mysqli_fetch_object($sql->mysqry);
							$autor="&nbsp;por&nbsp;<b>".$x->nome."</b>";
						}
						if($dias==0) $agendadoHa="Agendado&nbsp;<b>HOJE</b>$autor";
						else if($dias==1) $agendadoHa="Agendado&nbsp;<b>ONTEM</b>$autor";
						else $agendadoHa="Agendado&nbsp;há&nbsp;<b>$dias</b>&nbsp;dias$autor";

					?>
					<dl class="dl2">
						<dd><label><span class="iconify" data-icon="bx:bx-calendar"></span>&nbsp;<?php echo $agendadoHa;?></label></dd>
					</dl>
					<?php
					}
					?>
				</div>	
				<dl>
					<dt>Observações</dt>
					<dd>
						<textarea name="obs" style="height:80px" class="noupper"><?php echo $values['obs'];?></textarea>
					</dd>
				</dl>
			</fieldset>

				
		</form>
	</div>

	<div class="modal__fixo">
		<button type="button" class="button button__lg js-salvar"><i class="iconify" data-icon="bx-bx-check"></i> Salvar</button>
	</div>


</section>