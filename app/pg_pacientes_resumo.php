<?php
	if(isset($_POST['ajax'])) {
		require_once("lib/conf.php");	
		require_once("usuarios/checa.php");

		$sql = new Mysql();

		$rtn = array();
		if($_POST['ajax']=="indicacoesLista") {

			$indicacoesLista=array();
			if(isset($_POST['indicacao_tipo'])) {
				if($_POST['indicacao_tipo']=="PACIENTE") {
					$tableIndicacao=$_p."pacientes";
					$whereIndicacao="where lixo=0 order by nome asc";
					$campoIndicacao="nome";
				} else if ($_POST['indicacao_tipo']=="PROFISSIONAL") {
					$tableIndicacao=$_p."colaboradores";
					$whereIndicacao="where lixo=0 order by nome asc";
					$campoIndicacao="nome";
				} else {
					$tableIndicacao=$_p."parametros_indicacoes";
					$whereIndicacao="where lixo=0 order by titulo asc";
					$campoIndicacao="titulo";
				}
			}


			$sql->consult($tableIndicacao,"*",$whereIndicacao);
			if($sql->rows) {
				while($x=mysqli_fetch_object($sql->mysqry)) {
					$indicacoesLista[]=array('id'=>$x->id,'titulo'=>utf8_encode($x->$campoIndicacao));
				}
			}

			$rtn=array('success'=>true,'indicacoes'=>$indicacoesLista);

		} else if($_POST['ajax']=="verificarTelefone") {

			$campo='';
			if(isset($_POST['campo'])) {
				if($_POST['campo']=="telefone1" or $_POST['campo']=="telefone2") $campo=$_POST['campo'];
			}

			$valor=(isset($_POST['valor']) and !empty($_POST['valor']))?$_POST['valor']:0;


			if(!empty($campo) and !empty($valor)) {
				$cadastros=array();
				$sql->consult($_p."pacientes","id,nome","where (telefone1='".addslashes(telefone($valor))."' or telefone2='".addslashes(telefone($valor))."') and lixo=0");
				if($sql->rows) {
					while($x=mysqli_fetch_object($sql->mysqry)) {

						if(isset($_POST['id_paciente']) and $x->id==$_POST['id_paciente']) continue;
						$cadastros[]=$x;
					}
				}
				$rtn=array('success'=>true,'cadastros'=>$cadastros);
			} else {
				$rtn=array('success'=>false);
			}

		}

		header("Content-type: application/json");
		echo json_encode($rtn);
		die();
	}
	require_once("lib/conf.php");
	$_table=$_p."pacientes";

	include "includes/header.php";
	include "includes/nav.php";

	$values=$adm->get($_GET);
	$campos=explode(",","nome,situacao,sexo,foto_cn,rg,rg_orgaoemissor,rg_uf,cpf,data_nascimento,profissao,estado_civil,telefone1,telefone1_whatsapp,telefone1_whatsapp_permissao,telefone2,email,instagram,instagram_naopossui,musica,indicacao_tipo,indicacao,cep,endereco,numero,complemento,bairro,estado,cidade,id_cidade,responsavel_possui,responsavel_nome,responsavel_sexo,responsavel_rg,responsavel_rg_orgaoemissor,responsavel_rg_estado,responsavel_datanascimento,responsavel_estadocivil,responsavel_cpf,responsavel_profissao,responsavel_grauparentesco,preferencia_contato,estrangeiro,estrangeiro_passaporte,lat,lng,responsavel_estado_civil");

	$_profissoes=array();
	$sql->consult($_p."parametros_profissoes","*","where lixo=0 order by titulo asc");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_profissoes[$x->id]=$x;
	}

	$_width=400;
	$_height=400;
	$_dirFoto=$_cloudinaryPath."arqs/pacientes/";

	
	require_once("includes/header/headerPacientes.php");

	if(is_object($paciente)) {
		$values=$adm->values($campos,$paciente);
		$values['data']=date('d/m/Y H:i',strtotime($paciente->data));
	}

	if(isset($_POST['acao'])) {

		$vSQL=$adm->vSQL($campos,$_POST);
		$values=$adm->values;
		
		$vWHERE="where id=$paciente->id";
		$vSQL=substr($vSQL,0,strlen($vSQL)-1);

		$sql->update($_table,$vSQL,$vWHERE);
		$id_reg=$paciente->id;
		$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='update',vsql='".addslashes($vSQL)."',vwhere='".addslashes($vWHERE)."',tabela='$_table',id_reg='$id_reg'");
		
		$jsc->go($_page."?id_paciente=$paciente->id");
		die();
	}

	$_profissionais=array();
	$_profissionaisArr=array();
	$sql->consult($_p."colaboradores","id,nome,calendario_iniciais,foto,calendario_cor","where tipo_cro<>'' and lixo=0 order by nome asc");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_profissionais[$x->id]=$x;
		$_profissionaisArr[$x->id]=$x;
	}

	$_tiposEvolucao=array();
	$sql->consult($_p."pacientes_evolucoes_tipos","*","");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_tiposEvolucao[$x->id]=$x;
	}


	$_cadeiras=array();
	$sql->consult($_p."parametros_cadeiras","*","where lixo=0 order by ordem asc");
	while($x=mysqli_fetch_object($sql->mysqry)) $_cadeiras[$x->id]=$x;

	$_status=array();
	$sql->consult($_p."agenda_status","*","where lixo=0 order by titulo asc");
	while($x=mysqli_fetch_object($sql->mysqry)) $_status[$x->id]=$x;

	if($paciente->indicacao_tipo=="PACIENTE") {
		$indicacaoTabela=$_p."pacientes";
		$indicacaoTitulo="nome";
	} else if($paciente->indicacao_tipo=="PROFISSIONAL") {
		$indicacaoTabela=$_p."profissionais";
		$indicacaoTitulo="nome";
	} else {
		$indicacaoTabela=$_p."parametros_indicacoes";
		$indicacaoTitulo="titulo";
	}
	$pacienteIndicacao="-";
	if(isset($paciente->indicacao) and is_numeric($paciente->indicacao) and $paciente->indicacao>0) {
		$sql->consult($indicacaoTabela,$indicacaoTitulo,"where id=$paciente->indicacao");
		if($sql->rows) {
			$i=mysqli_fetch_object($sql->mysqry);
			$pacienteIndicacao=utf8_encode($i->$indicacaoTitulo);
		}

	}
	
	$haPaciente="-"; 
	if($paciente->data!='0000-00-00 00:00:00') {
		$dtCadastro = new DateTime($paciente->data);
		$dtHoje = new DateTime();
		$dif = $dtCadastro->diff($dtHoje);
		$haPaciente="";

		if($dif->y>0) $haPaciente.=" $dif->y ".($dif->y>1?"anos":"ano");
		if($dif->m>0) $haPaciente.=(empty($haPaciente)?"":" e ")." $dif->m  ".($dif->m>1?"meses":"mês");
		if($dif->d>0) $haPaciente.=(empty($haPaciente)?"":" e ")." $dif->d ".($dif->d>1?"dias":"dia");

		if(empty($haPaciente)) {
			$haPaciente.=" $dif->h horas";
		}

		$haPaciente="Paciente há $haPaciente";

	}

	# Resumo
		$_colaboradores=array();
		$sql->consult($_p."colaboradores","id,nome,calendario_iniciais,calendario_cor","");
		while($x=mysqli_fetch_object($sql->mysqry)) {
			$_colaboradores[$x->id]=$x;
		}

		$_historicoStatus=array();
		$sql->consult($_p."pacientes_historico_status","*","");
		while($x=mysqli_fetch_object($sql->mysqry)) {
			$_historicoStatus[$x->id]=$x;
		}

		$_agendaStatus=array();
		$sql->consult($_p."agenda_status","*","");
		while($x=mysqli_fetch_object($sql->mysqry)) {
			$_agendaStatus[$x->id]=$x;
		}

		$registros=array();
		$agendasIds=array();
		$sql->consult($_p."pacientes_historico","*","where id_paciente=$paciente->id and lixo=0 order by data desc");
		while($x=mysqli_fetch_object($sql->mysqry)) {
			$registros[]=$x;
			if($x->id_agenda>0) $agendasIds[$x->id_agenda]=$x->id_agenda;
		}

		

		$registrosComAgendamento=$registrosSemAgendamento=array();
		$agrupamentoAgenda=array();

		foreach($registros as $x) {
			$agendasIds[]=$x->id;
			if($x->id_agenda>0) {
				if(isset($agrupamentoAgenda[$x->id_agenda])) {
					$agrupamentoAgenda[$x->id_agenda]['grupo'][]=$x;
				} else {
					$agrupamentoAgenda[$x->id_agenda]=array('ultimaAgenda'=>$x,'grupo'=>array($x));
				}
			} else {
				$registrosSemAgendamento[strtotime($x->data)]=$x;
			}
		}



		$_agendas=$_profissionais=array();
		if(count($agendasIds)>0) {
			$sql->consult($_p."agenda","id,id_cadeira,profissionais,agenda_data,id_status,lixo","where id IN (".implode(",",$agendasIds).") and lixo=0");
			while($x=mysqli_fetch_object($sql->mysqry)) {

				$_agendas[$x->id]=$x;

			}
		} 


		$_grAgendasProfissionais=array();
		$_grAgendasProfissionaisTotal=0;
		$sql->consult($_p."agenda","id,id_cadeira,profissionais,agenda_data,id_status,lixo","where id_paciente=$paciente->id and lixo=0");
		while($x=mysqli_fetch_object($sql->mysqry)) {


			$profAux=explode(",",$x->profissionais);

			foreach($profAux as $idP) {
				if(!empty($idP) and is_numeric($idP)) {
					if(!isset($_grAgendasProfissionais[$idP])) $_grAgendasProfissionais[$idP]=0;
					$_grAgendasProfissionais[$idP]++;
					$_grAgendasProfissionaisTotal++;
				}
			}


		}


		foreach($agrupamentoAgenda as $idAg=>$regs) {
			if(isset($_agendas[$regs['ultimaAgenda']->id_agenda])) {
				$agendamento=($_agendas[$regs['ultimaAgenda']->id_agenda]);
				$id=strtotime($agendamento->agenda_data);//.$x->id;


				$registrosComAgendamento[$id]=$regs;
			}
		}

		//var_dump($agrupamentoAgenda);

		$registrosPorOrdem=array();
		foreach($registrosComAgendamento as $id=>$x) {
			$registrosPorOrdem[$id]=$x;
		}

		foreach($registrosSemAgendamento as $id=>$x) {
			$registrosPorOrdem[$id]=$x;
		}

		krsort($registrosPorOrdem);

	?>
	
	<main class="main">
		<div class="main__content content">

			<section class="filter">
			</section>

			<div class="grid grid_3" style="flex:1; grid-template-rows:180px minmax(0px,1fr); margin-top:-4rem;">

				<section class="box pac-ind">
					<div>
						<i class="iconify" data-icon="fa-brands:instagram"></i>
						<h1><?php echo empty($paciente->instagram)?"-":$paciente->instagram;?></h1>
					</div>
					<div>
						<i class="iconify" data-icon="bxs:music"></i>
						<h1><?php echo empty($paciente->musica)?"-":$paciente->musica;?></h1>
					</div>
					<div>
						<i class="iconify" data-icon="ph:phone-call-bold"></i>
						<h1><?php echo empty($paciente->telefone1)?"-":maskTelefone($paciente->telefone1);?></h1>
					</div>
					<div>
						<i class="iconify" data-icon="fluent:target-arrow-20-filled"></i>
						<h1><?php echo $pacienteIndicacao;?></h1>
					</div>
					<div>
						<i class="iconify" data-icon="fluent:calendar-ltr-24-regular"></i>
						<h1 style="font-size:14px"><?php echo $haPaciente;?></h1>
					</div>
				</section>

				<section class="box" style="grid-column:span 2; grid-row:span 2">
					<?php
					$sql->consult($_p."agenda","id,id_status,agenda_duracao","where id_paciente=$paciente->id and lixo=0");
					$agendamentos=$sql->rows;
					$agendamentosAtendidos=$agendamentosDesmarcados=$agendamentosFaltou=$agendamentosAtendidosDuracao=0;
					while($x=mysqli_fetch_object($sql->mysqry)) {
						if($x->id_status==5) {
							$agendamentosAtendidos++;
							$agendamentosAtendidosDuracao+=$x->agenda_duracao;
						} else if($x->id_status==4) $agendamentosDesmarcados++;
						else if($x->id_status==3) $agendamentosFaltou++;
					}

					if($agendamentosAtendidosDuracao>0) $agendamentosAtendidosDuracao*=60;
					?>
					<div class="filter">
						<div class="filter-group">
							<div class="filter-title">
								<h1>Análise da Agenda</h1>
							</div>
						</div>
					</div>

					<div class="list4 box">
						<a href="" class="list4-item">
							<div>
								<h1><?php echo sec_convertOriginal($agendamentosAtendidosDuracao,'HF');?></h1>
							</div>
							<div>
								<p>Horas de Atendimento</p>
							</div>
						</a>
						<a href="" class="list4-item">
							<div>
								<h1><i class="iconify" data-icon="fluent:calendar-ltr-24-regular"></i> <?php echo $agendamentos;?></h1>
							</div>
							<div>
								<p>Agendamentos</p>
							</div>
						</a>
						<a href="" class="list4-item">
							<div>
								<h1><i class="iconify" data-icon="fluent:calendar-checkmark-24-regular"></i> <?php echo $agendamentosAtendidos;?></h1>
							</div>
							<div>
								<p>Atendidos</p>
							</div>
						</a>
						<a href="" class="list4-item">
							<div>
								<h1><i class="iconify" data-icon="fluent:calendar-sync-24-regular"></i> <?php echo $agendamentosDesmarcados;?></h1>
							</div>
							<div>
								<p>Desmarcados</p>
							</div>
						</a>
						<a href="" class="list4-item">
							<div>
								<h1><i class="iconify" data-icon="fluent:calendar-cancel-24-regular"></i> <?php echo $agendamentosFaltou;?></h1>
							</div>
							<div>
								<p>	Faltou</p>
							</div>
						</a>
					</div>

					<div class="filter">
						<div class="filter-group">
							<div class="filter-title">
								<h1>Atendimento por Dentista</h1>
							</div>
						</div>						
					</div>

					<?php

						$grProfissionais = array();
						$labelDentistas = array();
						foreach($_grAgendasProfissionais as $id_profissional=>$qtd) {
							if(isset($_profissionaisArr[$id_profissional])) {
								$profissional=$_profissionaisArr[$id_profissional];

								$labelDentistas[]=utf8_encode($profissional->nome);

								//echo $qtd."->".(($qtd/$_grAgendasProfissionaisTotal)*100)." = $_grAgendasProfissionaisTotal<BR>";

								$perc=number_format((($qtd/$_grAgendasProfissionaisTotal)*100),1,".","");
								$labelDentistasQtd[]=$perc;
								$labelDentistasCor[]=$profissional->calendario_cor;

								$grProfissionais[$profissional->id]=array('id'=>$profissional->id,
																			'nome'=>utf8_encode($profissional->nome),
																			'qtd'=>$qtd,
																			'perc'=>$perc);
							}
						}
					?>

					<div class="grid grid_2 box" style="margin:0;">
						<div style="grid-column:span 1">
							<div style="width:100%; padding:20px;">
								<script>
								$(function() {
									var ctx = document.getElementById('grafico1').getContext('2d');
									var grafico1 = new Chart(ctx, {    
									    type: 'doughnut',
									    data: {
									        labels: <?php echo json_encode($labelDentistas);?>,

									        datasets: [{
									            fill:true,
									            borderDashOffset: 0.0,
									            label: 'Pacientes',
									            data: <?php echo json_encode($labelDentistasQtd);?>,
									            backgroundColor: <?php echo json_encode($labelDentistasCor);?>,
									            borderColor:'transparent',
									            borderWidth: 1,
									            borderDash: [],
									            borderDashOffset: 0.0
									        }]
									    },
									    options: {
									    	legend: {
									            display: false
									         },
									    	responsive:true,

											
									    }
									});
								});
								</script>
								<canvas id="grafico1" class="box-grafico"></canvas>
							</div>
						</div>

						<div>
							<div class="list1">
								<table>
									<?php
									foreach($grProfissionais as $infos) {
										$id_profissional=$infos['id'];
										if(isset($_profissionaisArr[$id_profissional])) {
											$profissional=$_profissionaisArr[$id_profissional];
									?>
									<tr class="js-item">
										<td class="list1__border" style="color:<?php echo $profissional->calendario_cor;?>"></td>
										<td>
											<h1><?php echo utf8_encode($profissional->nome);?></h1>
											<p><?php echo $infos['qtd'];?> consulta(s)</p>
										</td>
										<td style="font-size:25px;"><?php echo $infos['perc'];?>%</td>
									</tr>
									<?php
										}
									}
									?>
								</table>
							</div>
						</div>
					</div>				
				</section>


				<section class="box pac-hist">
	
					<div class="filter">
						<div class="filter-group">
							<div class="filter-title">
								<h1>Histórico de Agendamento</h1>
							</div>
						</div>
					</div>

					<div class="pac-hist-content">
						<section>
							<div class="history2">
					<?php
					foreach($registrosPorOrdem as $e) {

						$subagendas='';
						if(is_array($e) and isset($e['ultimaAgenda'])) {
							$x=$e['ultimaAgenda'];
							$subagendas=$e['grupo'];
						} else {
							$x=$e;
						}

						$style="";
						$evento='';
						if($x->evento=="agendaStatus" or $x->evento=="agendaHorario" or $x->evento=="agendaNovo") {
							$evento="agendamento";
							$obs='';
							$icone='<i class="iconify" data-icon="mdi:calendar-check"></i>';
							$agenda=$cadeira=$profissionais=$profissionaisIniciais='';

							if(isset($_agendas[$x->id_agenda])) {
								$agenda=$_agendas[$x->id_agenda];
								if(isset($_cadeiras[$agenda->id_cadeira])) {
									$cadeira=$_cadeiras[$agenda->id_cadeira];
								}
								$icone='<span style="background:'.$_status[$agenda->id_status]->cor.';color:#FFF;">'.$icone.'</span>';

								$aux=explode(",",$agenda->profissionais);
								foreach($aux as $idP) {
									if(!empty($idP) and is_numeric($idP) and isset($_colaboradores[$idP])) {

										if(!empty($profissionaisIniciais)) {
											$profissionaisIniciais='<div class="badge-prof">+ '.(count($aux)-2).'</div>';
											break;
										}

										$profissionais.=utf8_encode($_colaboradores[$idP]->nome).", ";
										$profissionaisIniciais.='<div class="badge-prof" style="background:'.$_colaboradores[$idP]->calendario_cor.'">'.$_colaboradores[$idP]->calendario_iniciais.'</div>';
									}
								}
								if(!empty($profissionais)) $profissionais=substr($profissionais,0,strlen($profissionais)-2);
								
								//31/03 (quinta) • 10:00
								$dataTimeline=date('d/m • H:i',strtotime($agenda->agenda_data));
							}

							if(empty($agenda) or empty($cadeira)) continue;
							
						} else if($x->evento=="observacao" || $x->evento=="relacionamento") {
							$evento="relacionamento";
							$icone='<span><i class="iconify" data-icon="mdi:chat-processing-outline"></i></span>';
							$dataTimeline=date('d/m/y • H:i',strtotime($x->data));

							$profissionaisIniciais= isset($_colaboradores[$x->id_usuario])?utf8_encode($_colaboradores[$x->id_usuario]->nome):"";
							$cadeira='';

							$obs=utf8_encode($x->descricao);
							if($x->id_obs>0 and isset($_historicoStatus[$x->id_obs])) {
								$obs="<strong>".utf8_encode($_historicoStatus[$x->id_obs]->titulo)."</strong><br />".$obs;
							} 
						}
					?>
								<div class="history2-item">
									<aside>
										<?php
										//<span style="background:var(--verde);"><i class="iconify" data-icon="fluent:calendar-ltr-24-regular"></i></span>
										echo $icone;
										?>
									</aside>

									<article>
										<div class="history2-main">
											<div>
												<h1><?php echo $dataTimeline;?></h1>
												<?php echo is_object($cadeira)?"<h2>".utf8_encode($cadeira->titulo)."</h2>":"" ;?>
												<?php echo $profissionaisIniciais;?>
												
											</div><?php echo $obs;?>
											<?php
											if(is_array($subagendas) and count($subagendas)>0) {
											?>
											<a href="javascript:;" onclick="$(this).parent().next('.history2-more').slideToggle('fast');">detalhes</a></h3>
											<?php
											}
											?>
										</div>
										<?php
										if(is_array($subagendas) and count($subagendas)>0) {
										?>
										<div class="history2-more">
											<?php
											foreach($subagendas as $s) {
												if($s->evento=="agendaStatus" or $s->evento=="agendaHorario" or $s->evento=="agendaNovo") {
													$agenda=$cadeira=$profissionais='';
													if(isset($_agendas[$s->id])) {
														$agenda=$_agendas[$s->id];
														if(isset($_cadeiras[$agenda->id_cadeira])) {
															$cadeira=$_cadeiras[$agenda->id_cadeira];
														}

														$aux=explode(",",$agenda->profissionais);
														foreach($aux as $idP) {
															if(!empty($idP) and is_numeric($idP) and isset($_colaboradores[$idP])) {
																$profissionais.=utf8_encode($_colaboradores[$idP]->nome).", ";
															}
														}
														if(!empty($profissionais)) $profissionais=substr($profissionais,0,strlen($profissionais)-2);
														$dataTimeline=date('d/m/y H:i',strtotime($agenda->agenda_data));
													}

													if(empty($agenda) or empty($cadeira)) continue;
													
												} 
											?>


											<div class="history2-more-item">

												<h1><?php echo $dataTimeline;?><?php echo isset($_colaboradores[$s->id_usuario])?" - ".utf8_encode($_colaboradores[$s->id_usuario]->nome):"";?></h1>
												<?php
												if($s->evento=="agendaHorario") {
												?>
												<h2>Horário alterado de <span class="data"><?php echo date('d/m/ H:i',strtotime($s->agenda_data_antigo));?></span> para <span class="data"><?php echo date('d/m H:i',strtotime($s->agenda_data_novo));?></span> <br /><?php echo utf8_encode($cadeira->titulo);?><?php echo !empty($profissionais)?" - ".$profissionais:"";?></h2>
												<?php
												}
												else if($s->evento=="agendaStatus") {
												?>
												<h2>Alterou status de <span class="data" style="background:<?php echo $_agendaStatus[$s->id_status_antigo]->cor;?>"><?php echo utf8_encode($_agendaStatus[$s->id_status_antigo]->titulo);?></span> para <span class="data" style="background:<?php echo $_agendaStatus[$s->id_status_novo]->cor;?>"><?php  echo utf8_encode($_agendaStatus[$s->id_status_novo]->titulo);?></span></h2>
												<?php
												} 
												else if($s->evento=="agendaNovo") {
												?>
												<h2>Criou novo agendamento com status <span class="data" style="background:<?php echo $_agendaStatus[$s->id_status_novo]->cor;?>"><?php  echo utf8_encode($_agendaStatus[$s->id_status_novo]->titulo);?></span></h2>
												<?php
												}
												?>
												
											</div>
											<?php
											}
											?>
										</div>
										<?php
									}
										?>
									</article>
								</div>
						<?php
							}
						?>	

							</div>
						</section>
					</div>

				</section>


			</div>
	
		</div>
	</main>


<?php 
include "includes/footer.php";
?>	