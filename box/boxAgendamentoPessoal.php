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

				$cadeira='';
				if(isset($_POST['id_cadeira']) and is_numeric($_POST['id_cadeira'])) {
					$sql->consult($_p."parametros_cadeiras","*","where id='".$_POST['id_cadeira']."' and id_unidade=$unidade->id and lixo=0");
					if($sql->rows) {
						$cadeira=mysqli_fetch_object($sql->mysqry);
					}
				}

				if(empty($profissional) and empty($cadeira)) {
					$rtn=array('success'=>false,'error'=>'Por favor seleciona o Profissional ou a Cadeira!');
				} else {
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

					$agendaDataFinal='';
					if(isset($_POST['agenda_data_final']) and !empty($_POST['agenda_data_final'])) {
						list($_dia,$_mes,$_ano)=explode("/",$_POST['agenda_data_final']);
						if(checkdate($_mes, $_dia, $_ano)) {
							$agendaDataFinal=$_ano."-".$_mes."-".$_dia;
						}
					}

					$agendaHoraFinal='';
					if(isset($_POST['agenda_hora_final']) and !empty($_POST['agenda_hora_final'])) {
						list($_h,$_m)=explode(":",$_POST['agenda_hora_final']);
						if(is_numeric($_h) and is_numeric($_m)) {
							$agendaHoraFinal=$_h.":".$_m;
						}
					}
						

					if(empty($agendaData)) {
						$rtn=array('success'=>false,'error'=>'Data de início inválida!');
					} else if(empty($agendaHora)) {
						$rtn=array('success'=>false,'error'=>'Hora de início inválida!');
					} else if(!isset($_POST['dia_inteiro']) and empty($agendaDataFinal)) {
						$rtn=array('success'=>false,'error'=>'Hora de término inválida!');
					}  else if(!isset($_POST['dia_inteiro']) and empty($agendaHoraFinal)) {
						$rtn=array('success'=>false,'error'=>'Hora  de término inválida!');
					} else {
						$agendaData.=" ".$agendaHora;
						$agendaDataFinal.=" ".$agendaHoraFinal;

						$vSQL="id_unidade=$unidade->id,
								profissionais=',".(is_object($profissional)?$profissional->id:0).",',
								id_cadeira=$cadeira->id,
								dia_inteiro='".((isset($_POST['dia_inteiro']) and $_POST['dia_inteiro']==1)?1:0)."',
								agenda_data='".$agendaData."',
								agenda_data_final='".$agendaDataFinal."',
								agendaPessoal=1";

						if(isset($_POST['obs'])) $vSQL.=",obs='".addslashes(utf8_decode($_POST['obs']))."'";

						$agenda='';
						if(isset($_POST['id_agenda']) and is_numeric($_POST['id_agenda'])) {
							$sql->consult($_p."agenda","*","where id='".$_POST['id_agenda']."'");
							if($sql->rows) {
								$agenda=mysqli_fetch_object($sql->mysqry);
							}
						}

						if(is_object($agenda)) {
							$vWHERE="where id=$agenda->id";
							$sql->update($_p."agenda",$vSQL,$vWHERE);
							$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='update',vsql='".addslashes($vSQL)."',vwhere='".addslashes($vWHERE)."',tabela='".$_p."agenda',id_reg='".$agenda->id."'");
						} else {
							$sql->consult($_p."agenda","*","where agenda_data='".$agendaData."' and profissionais=',$profissional->id,' and agendaPessoal=1 and id_unidade=$unidade->id");
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
				} else if(isset($_POST['id_profissional']) and is_numeric($_POST['id_profissional'])) {
					$profissionaisID=array($_POST['id_profissional']);
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

	$unidade='';
	if(isset($_GET['id_unidade']) and is_numeric($_GET['id_unidade']) and isset($_optUnidades[$_GET['id_unidade']])) {
		$unidade=$_optUnidades[$_GET['id_unidade']];
	}

	if(empty($unidade)) {
		$jsc->jAlert("Unidade não encontrada!","erro","$.fancybox.close()");
		die();
	}
	$campos=explode(",","id_paciente,profissionais,id_cadeira,id_status,clienteChegou,emAtendimento,agenda_data,agenda_hora,agenda_data_final,agenda_hora_final,dia_inteiro,obs");
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
			if($agenda->agenda_data_final=="0000-00-00 00:00:00") $agenda->agenda_data_final="";
			foreach($campos as $v) {

				if($v=="agenda_data") $values[$v]=date('d/m/Y',strtotime($agenda->$v));
				else if($v=="agenda_hora") $values[$v]=date('H:i',strtotime($agenda->agenda_data));
				else if($v=="agenda_data_final") $values[$v]=date('d/m/Y',strtotime($agenda->$v));
				else if($v=="agenda_hora_final") $values[$v]=date('H:i',strtotime($agenda->agenda_data_final));
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

?>

<script type="text/javascript">
	var id_unidade = <?php echo $unidade->id;?>;

	var id_agenda = '<?php echo is_object($agenda)?$agenda->id:'';?>';
	$(function(){

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
		$('.js-salvar').click(function(){

			let erro=false;
			$('form .obg').each(function(index,elem){
				console.log($(this).attr('name'));
				if($(this).val().length==0) {
					$(elem).addClass('erro');
					erro=true;
				}
			});

			if(erro===true) {
				swal({title: "Erro!", text: "Complete os campos destacados", type:"error", confirmButtonColor: "#424242"});
			} else {
				
				let campos = $('form.js-form-agendamento').serialize();
				let id_profissional = $('form.js-form-agendamento .js-profissional').val();

				let data = `ajax=agendamentoPersistir&id_unidade=${id_unidade}&id_agenda=${id_agenda}&id_profissional=${id_profissional}&${campos}`;

				$.ajax({
					type:'POST',
					url:'box/boxAgendamentoPessoal.php',
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
				});
				return false;

			}
		});
	})
</script>


<section class="modal" style="width:950px; height:auto;">

	<header class="modal-header">
		<div class="filtros">
			<?php
			if(empty($agenda)) {
			?>
			<ul class="abas">
				<li><a href="javascript:;" class="js-btn-paciente">Paciente</a></li>
				<li><a href="javascript:;" class="active">Compromisso</a></li>
			</ul>
		
			<script type="text/javascript">
				$(function(){ 
					$('.js-btn-paciente').click(function(){
						$.fancybox.close();
						$.fancybox.open({
					        src: "box/boxAgendamento.php?id_unidade=<?php echo $unidade->id;?>",
					        type: "ajax"
					    });
					})
				});
			</script>
			<?php
			} else {
			?>
			<h1 class="filtros__titulo">Editar</h1>
			<?php
			}
			?>
			<div class="filtros-acoes">
				<button type="button" class="principal js-salvar"><i class="iconify" data-icon="bx-bx-check"></i></button>
			</div>
		</div>
	</header>
	
	<article class="modal-conteudo">

		
		<form method="post" class="form js-form-agendamento">
			
			<fieldset>
				<legend>Agendamento de Compromisso</legend>
				
				<script type="text/javascript">

					$(function(){
						if($('input[name=dia_inteiro]').prop('checked')===true) {
							$('input[name=agenda_data_final],input[name=agenda_hora_final]').removeClass('obg').parent().parent().hide();
						} else {
							$('input[name=agenda_data_final],input[name=agenda_hora_final]').addClass('obg').parent().parent().show();

						}
						$('input[name=dia_inteiro]').click(function(){
							if($('input[name=dia_inteiro]').prop('checked')===true) {
							$('input[name=agenda_data_final],input[name=agenda_hora_final]').removeClass('obg').parent().parent().hide();
						} else {
							$('input[name=agenda_data_final],input[name=agenda_hora_final]').addClass('obg').parent().parent().show();

						}
						})
					})
				</script>
				<div class="colunas5">

					<dl>
						<dd>
							<div class="input-icon"><i class="iconify" data-icon="uil-clock-two"></i></div>
							<input type="text" name="agenda_data" class="agendaData obg js-agenda-verifica" value="<?php echo $values['agenda_data'];?>" autocomplete="off" placeholder="Data (início)" />
						</dd>
					</dl>
					<dl>
						<dd><input type="text" name="agenda_hora" class="agendaHora obg js-agenda-verifica" value="<?php echo $values['agenda_hora'];?>" autocomplete="off" placeholder="Hora" /></dd>
					</dl>
					<dl>
						<dd>
							<div class="input-icon"><i class="iconify" data-icon="uil-clock-eight"></i></div>
							<input type="text" name="agenda_data_final" class="agendaData" value="<?php echo $values['agenda_data_final'];?>" autocomplete="off" placeholder="Data (fim)" />
						</dd>
					</dl>
					<dl>
						<dd><input type="text" name="agenda_hora_final" class="agendaHora" value="<?php echo $values['agenda_hora_final'];?>" autocomplete="off" placeholder="Hora" /></dd>
					</dl>
					<dl>
						<dd>
							<label><input type="checkbox" name="dia_inteiro" value="1"<?php echo $values['dia_inteiro']?" checked":"";?> /> Dia inteiro</label>
						</dd>
					</dl>
				</div>

				<dl>					
					<dd>
						<div class="input-icon"><i class="iconify" data-icon="uil-comment-info"></i></div>
						<input type="text" name="obs" placeholder="Descrição" value="<?php echo $values['obs'];?>" />
					</dd>
				</dl>
				<div class="colunas5">
					<dl class="dl3">						
						<dd>
							<div class="input-icon"><i class="iconify" data-icon="uil-user-md"></i></div>
							<select class="chosen js-agenda-verifica js-profissional">
								<option value="">PROFISSIONAL...</option>
								<?php
								foreach($_profissionais as $p) {
									echo '<option value="'.$p->id.'"'.(in_array($p->id, $values['profissionais'])?' selected':'').'>'.utf8_encode($p->nome).'</option>';
								}
								?>
							</select>
						</dd>
					</dl>
					<dl class="dl2">						
						<dd>
							<select name="id_cadeira" class="chosen obg js-agenda-verifica">
								<option value="">CADEIRA...</option>
								<?php
								foreach($_cadeiras as $p) {
									echo '<option value="'.$p->id.'"'.($values['id_cadeira']==$p->id?' selected':'').'>'.utf8_encode($p->titulo).'</option>';
								}
								?>
							</select>
						</dd>
					</dl>
				</div>
				<dl style="color:red" id="box-validacoes">
				</dl>
			</fieldset>
		</form>

	</article>
</section>