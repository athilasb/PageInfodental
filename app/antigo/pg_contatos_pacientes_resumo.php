<?php
	$title="";
	include "includes/header.php";
	include "includes/nav.php";

	$paciente='';
	if(isset($_GET['id_paciente']) and is_numeric($_GET['id_paciente'])) {
		$sql->consult($_p."pacientes","*","where id='".$_GET['id_paciente']."'");
		if($sql->rows) {
			$paciente=mysqli_fetch_object($sql->mysqry);
		}
	}

	if(empty($paciente)) {
		$jsc->jAlert("Paciente não encontrado!","erro","document.location.href='pg_contatos_pacientes.php'");
		die();
	}

	$_profissionais=array();
	$sql->consult($_p."colaboradores","id,nome,calendario_iniciais,foto,calendario_cor","where tipo_cro<>'' and lixo=0 order by nome asc");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_profissionais[$x->id]=$x;
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

?>
<script>
	$(function(){
		// $('.m-contatos').next().show();		
		$('.m-contatos').addClass("active");
		
		$('.paciente-etapas__slick').slick({
			dots:true,
			arrows:false
		});
		
		$('.paciente-fotos__slick').slick({
			dots:true,
			slidesToShow:2,
			slidesToScroll:2,
			arrows:false
		});
		
	});
</script>
<?php /* <script src="js/jquery.vendas.js"></script> */ ?>

	<section class="content">

		<?php
		require_once("includes/abaPaciente.php");
		?>
		
		<section class="grid grid_3" style="padding-bottom:0;">
			
			<div class="box">
				<div class="paciente-info">
					<?php /*
					<header class="paciente-info-header">
						<img src="../infodental2/img/ilustra-paciente.jpg" alt="" width="84" height="84" class="paciente-info-header__foto" />
						<section class="paciente-info-header__inner1">
							<h1>Ana Lopes da Silva Azevedo</h1>
							<p>25 anos</p>
							<p><span style="color:var(--cinza3);">#224599</span> <span style="color:var(--cor1);">ATIVO</span></p>
						</section>
					</header>
					*/ ?>
					<?php
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
					?>
					<div class="paciente-info-grid">
						<p class="paciente-info-grid__item"><i class="iconify" data-icon="mdi-instagram"></i> <?php echo empty($paciente->instagram)?"-":'<a href="http://instagram.com/'.str_replace("@","",$paciente->instagram).'" target="_blank">'.utf8_encode($paciente->instagram.'</a>');?></p>
						<p class="paciente-info-grid__item"><i class="iconify" data-icon="mdi-phone"></i><?php echo empty($paciente->telefone1)?"-":utf8_encode($paciente->telefone1);?></p>
						<p class="paciente-info-grid__item"><i class="iconify" data-icon="mdi-music"></i><?php echo empty($paciente->musica)?"-":utf8_encode($paciente->musica);?></p>
						<p class="paciente-info-grid__item"><i class="iconify" data-icon="mdi-hand-pointing-right"></i><?php echo $pacienteIndicacao;?></p>
						<?php
						if($paciente->data!='0000-00-00 00:00:00') {
							$dtCadastro = new DateTime($paciente->data);
							$dtHoje = new DateTime();
							$dif = $dtCadastro->diff($dtHoje);
							$haPaciente="";

							if($dif->y>0) $haPaciente.=" $dif->y ".($dif->y>1?"anos":"ano");
							if($dif->m>0) $haPaciente.=" $dif->m  ".($dif->m>1?"meses":"mês");;
							if($dif->d>0) $haPaciente.=" $dif->d ".($dif->d>1?"dias":"dia");;
						?>
						<p class="paciente-info-grid__item"><i class="iconify" data-icon="fa-solid:user-clock" data-height="12"></i> Paciente há <?php echo $haPaciente;?></p>
						<?php
						}
						?>
						
						<?php /*<p class="paciente-info-grid__item" style="color:red;"><i class="iconify" data-icon="mdi-alert"></i> -</p>
						<p class="paciente-info-grid__item" style="color:red;"><i class="iconify" data-icon="mdi-currency-usd-circle-outline"></i> -</p>*/?>
					</div>
				</div>
			</div>

			<style type="text/css">
				.hist2-item__dados .data {
					padding: 5px;
					color: #FFF;
					background:var(--cor1);
					font-weight: bold;
					border-radius: 8px;
				}
			</style>
			<script type="text/javascript">
				$(function(){
					$('.js-historico-filtro').click(function(){
						let tipo = $(this).attr('data-tipo');

						$('.js-historico-filtro').removeClass('active');
						$(this).addClass('active');

						$('.js-evento').hide();
						if(tipo=="todos") {

							$('.js-evento').fadeIn();
						} else if(tipo=="relacionamento") {
							$('.js-evento-relacionamento').fadeIn();
						} else if(tipo=="agendamento") {
							$('.js-evento-agendamento').fadeIn();

						}
					})
				});
			</script>
			<div class="hist2 box" style="grid-column:span 2; grid-row:span 2; min-height:calc(100vh - 290px);">
				<?php
				if($_infodentalCompleto==1) {
				?>
				<aside>
					<h1 class="paciente__titulo1">Histórico</h1>										
					
					<div class="grid-links grid-links_sm">
						<a href="javascript:;" class="js-historico-filtro active" data-tipo="todos">
							<i class="iconify" data-icon="mdi:format-list-bulleted"></i>
							<p>Todos</p>
						</a>
						<a href="javascript:;" class="js-historico-filtro" data-tipo="relacionamento">
							<i class="iconify" data-icon="mdi:chat-processing-outline"></i>
							<p>Relacionamento</p>
						</a>
						<a href="javascript:;" class="js-historico-filtro" data-tipo="agendamento">
							<i class="iconify" data-icon="mdi:calendar-check"></i>
							<p>Agendamentos</p>
						</a>
						<a href="javascript:;" class="js-historico-filtro" data-tipo="financeiro">
							<i class="iconify" data-icon="mdi:finance"></i>
							<p>Financeiro</p>
						</a>						
					</div>
					
				</aside>
				<?php
				}
				?>

				<article>

					<h1 class="paciente__titulo1">Histórico</h1>	

					<div class="paciente-scroll" style="padding-left: 23px;">
					<?php
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

					$_agendas=$_profissionais=array();
					if(count($agendasIds)>0) {
						$sql->consult($_p."agenda","id,id_cadeira,profissionais,agenda_data,id_status,lixo","where id IN (".implode(",",$agendasIds).") and lixo=0");
						while($x=mysqli_fetch_object($sql->mysqry)) {
							$_agendas[$x->id]=$x;

						}
					}

					$registrosComAgendamento=$registrosSemAgendamento=array();
					$agrupamentoAgenda=array();

					foreach($registros as $x) {

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
							$icone='<i class="iconify" data-icon="mdi:calendar-check"></i>';
							$agenda=$cadeira=$profissionais=$profissionaisIniciais='';
							if(isset($_agendas[$x->id_agenda])) {
								$agenda=$_agendas[$x->id_agenda];
								if(isset($_cadeiras[$agenda->id_cadeira])) {
									$cadeira=$_cadeiras[$agenda->id_cadeira];
								}
								$style='style="background:'.$_status[$agenda->id_status]->cor.';color:#FFF;"';

								$aux=explode(",",$agenda->profissionais);
								foreach($aux as $idP) {
									if(!empty($idP) and is_numeric($idP) and isset($_colaboradores[$idP])) {
										$profissionais.=utf8_encode($_colaboradores[$idP]->nome).", ";
										$profissionaisIniciais.='<div class="cal-item-foto" style="float:left;"><span style="background:'.$_colaboradores[$idP]->calendario_cor.'">'.$_colaboradores[$idP]->calendario_iniciais.'</span></div>';
									}
								}
								if(!empty($profissionais)) $profissionais=substr($profissionais,0,strlen($profissionais)-2);
								

								$dataTimeline=date('d/m H:i',strtotime($agenda->agenda_data));
							}

							if(empty($agenda) or empty($cadeira)) continue;
							
						} else if($x->evento=="observacao" || $x->evento=="relacionamento") {
							$evento="relacionamento";
							$icone='<i class="iconify" data-icon="mdi:chat-processing-outline"></i>';
							$dataTimeline=date('d/m H:i',strtotime($x->data));
						}
						?>
						<div class="hist2-item js-evento js-evento-<?php echo $evento;?>">
							<div class="hist2-item__inner1">
								<div class="hist2-item__icone"<?php echo $style;?>><?php echo $icone;?></div>
							</div>
							<div class="hist2-item__inner2">
								<div class="hist2-item__dados">
									<?php
									$obs=utf8_encode($x->descricao);
									if($x->evento=="agendaHorario" or $x->evento=="agendaStatus" or $x->evento=="agendaNovo") {
									?>
									<a style="display:flex;justify-content:space-between;align-items:center;">
										<h1><?php echo date('d/m/Y<\b\r \/\>H:i',strtotime($agenda->agenda_data));?></h1>
										<p><?php echo utf8_encode($_cadeiras[$agenda->id_cadeira]->titulo);?></p>
										<div class="cal-item__fotos">
											<?php echo $profissionaisIniciais;?>
										</div>
									</a>
									<?php
									}
									else if($x->evento=="observacao" || $x->evento=="relacionamento") {
									?>
									<h1><?php echo $dataTimeline;?><?php echo isset($_colaboradores[$x->id_usuario])?" - ".utf8_encode($_colaboradores[$x->id_usuario]->nome):"";?></h1>
									<?php
										if($x->id_obs>0 and isset($_historicoStatus[$x->id_obs])) {
											$obs="<strong>".utf8_encode($_historicoStatus[$x->id_obs]->titulo)."</strong><br />".$obs;
										} 
									}
									if(!empty($obs)) {
									?>
									<p><?php echo $obs;?></p>
									<?php
									}
									if(is_array($subagendas) and count($subagendas)>0) {
									?>
									<a href="javascript:;" onclick="$(this).parent().next('.hist2-item__detalhes').slideToggle('fast');" class="button button__alt button__sm"><i class="iconify" data-icon="mdi:chevron-down"></i> mais detalhes</a>
									<?php	
									}
									?>
								</div>	
								<?php
								if(is_array($subagendas) and count($subagendas)>0) {
								?>
								<div class="hist2-item__detalhes" style="display:none;">
								<?php
									foreach($subagendas as $s) {
										if($s->evento=="agendaStatus" or $s->evento=="agendaHorario" or $s->evento=="agendaNovo") {
											$agenda=$cadeira=$profissionais='';
											if(isset($_agendas[$s->id_agenda])) {
												$agenda=$_agendas[$s->id_agenda];
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
												$dataTimeline=date('d/m H:i',strtotime($agenda->agenda_data));
											}

											if(empty($agenda) or empty($cadeira)) continue;
											
										} 
								?>
									<div class="hist2-item__dados">
									<h1><?php echo $dataTimeline;?><?php echo isset($_colaboradores[$s->id_usuario])?" - ".utf8_encode($_colaboradores[$s->id_usuario]->nome):"";?></h1>
										<?php
										if($s->evento=="agendaHorario") {
										?>
										<p>Horário alterado de <span class="data"><?php echo date('d/m H:i',strtotime($s->agenda_data_antigo));?></span> para <span class="data"><?php echo date('d/m H:i',strtotime($s->agenda_data_novo));?></span> <br /><?php echo utf8_encode($cadeira->titulo);?><?php echo !empty($profissionais)?" - ".$profissionais:"";?></p>
										<?php
										}
										else if($s->evento=="agendaStatus") {
										?>
										<p>Alterou status de <span class="data" style="background:<?php echo $_agendaStatus[$s->id_status_antigo]->cor;?>"><?php echo utf8_encode($_agendaStatus[$s->id_status_antigo]->titulo);?></span> para <span class="data" style="background:<?php echo $_agendaStatus[$s->id_status_novo]->cor;?>"><?php  echo utf8_encode($_agendaStatus[$s->id_status_novo]->titulo);?></span></p>
										<?php
										} 
										else if($s->evento=="agendaNovo") {
										?>
										<p>Criou novo agendamento com status <span class="data" style="background:<?php echo $_agendaStatus[$s->id_status_novo]->cor;?>"><?php  echo utf8_encode($_agendaStatus[$s->id_status_novo]->titulo);?></span></p>
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
							</div>
						</div>
					<?php
					}
					?>
					</div>
					
				</article>
			</div>
			<?php
			if($_infodentalCompleto==1) {
				$where="WHERE id_paciente=$paciente->id and lixo=0";
				$sql->consult($_p."pacientes_tratamentos","*",$where);

				$registros=array();
				$tratamentosIDs=array(0);
				while($x=mysqli_fetch_object($sql->mysqry)) {
					$registros[]=$x;
					$tratamentosIDs[]=$x->id;
				}

				$_procedimentos=array();
				$_procedimentosAprovado=array();
				$procedimentosIds=$tratamentosProcedimentosIDs=array(-1);
				$sql->consult($_p."pacientes_tratamentos_procedimentos","*","where id_tratamento IN (".implode(",",$tratamentosIDs).")  and situacao='aprovado' and lixo=0");
				while($x=mysqli_fetch_object($sql->mysqry)) {
					$tratamentosProcedimentosIDs[]=$x->id;
					$_procedimentosAprovado[$x->id]=$x;
				}


				$sql->consult($_p."pacientes_tratamentos_procedimentos_evolucao","*","where id_tratamento_procedimento IN (".implode(",",$tratamentosProcedimentosIDs).") and lixo=0");
				while($x=mysqli_fetch_object($sql->mysqry)) {
					if(isset($_procedimentosAprovado[$x->id_tratamento_procedimento])) {
						$p=$_procedimentosAprovado[$x->id_tratamento_procedimento];
					
						if($x->status_evolucao=="finalizado") {
							$_procedimentosFinalizados[$p->id_tratamento][]=$x;
						} 
						$_todosProcedimentos[$p->id_tratamento][]=$x;
						$procedimentosIds[]=$x->id_procedimento;
					}
				}


				$_procedimentosObj=array();
				$sql->consult($_p."parametros_procedimentos","*","where id IN (".implode(",",$procedimentosIds).")");
				while($x=mysqli_fetch_object($sql->mysqry)) {
					$_procedimentosObj[$x->id]=$x;
				}


				
				$sql->consult($_p."pacientes_tratamentos_pagamentos","*","where id_tratamento IN (".implode(",",$tratamentosIDs).") and id_fusao=0 and lixo=0");
				$pagRegs=array();
				$pagamentosIds=array(0);
				while($x=mysqli_fetch_object($sql->mysqry)) {
					$pagamentosIds[]=$x->id;
					$pagRegs[]=$x;
				}

				$_baixas=array();
				$sql->consult($_p."pacientes_tratamentos_pagamentos_baixas","*","where id_pagamento IN (".implode(",",$pagamentosIds).") and lixo=0");
				while($x=mysqli_fetch_object($sql->mysqry)) {
					$_baixas[$x->id_pagamento][]=$x;
				}


				$_pagamentos=array();
				foreach($pagRegs as $x) {

					// se possui baixa
					if(isset($_baixas[$x->id])) {

						$valorTotal=$x->valor;
						$valorBaixas=0;
						foreach($_baixas[$x->id] as $b) {
							$_pagamentos[$x->id_tratamento][]=array('pago'=>$b->pago,
																	'tipo'=>'baixa',
																	'valor'=>$b->valor);
							$valorBaixas+=$b->valor;
						}

						// restante que falta dar baixa
						if($valorTotal>$valorBaixas) {
							$_pagamentos[$x->id_tratamento][]=array('pago'=>0,
																	'tipos'=>'restante',
																	'valor'=>$valorTotal-$valorBaixas);

						}

					} else {

						$_pagamentos[$x->id_tratamento][]=array('pago'=>$x->pago,
																'tipo'=>'parcela '.$x->id,
																'valor'=>$x->valor);
						
					}
				}

			?>
			<div class="box" style="overflow:hidden;">
				<div class="paciente-etapas">
					
					<div class="paciente-etapas__slick">
						<?php


						if(count($registros)>0) {
							foreach($registros as $x) {

								$pagamentos=array();
								if(isset($_pagamentos[$x->id])) $pagamentos=$_pagamentos[$x->id];

								$procedimentos=array();
								if(isset($_procedimentos[$x->id])) $procedimentos=$_procedimentos[$x->id];

								$total=isset($_todosProcedimentos[$x->id])?count($_todosProcedimentos[$x->id]):0;
								$finalizados=isset($_procedimentosFinalizados[$x->id])?count($_procedimentosFinalizados[$x->id]):0;
								$perc=($total)==0?0:number_format(($finalizados/($total))*100,0,"","");



								$pagPago=$pagTotal=0;
								foreach($pagamentos as $p) { 
									$p=(object)$p;
									if($p->pago==1) $pagPago+=$p->valor;

									$pagTotal+=$p->valor;
								}
								$percPag=($pagTotal)==0?0:number_format(($pagPago/($pagTotal))*100,0,"","");
							



								if($x->status=="PENDENTE") $x->status="Em Aberto";
								else if($x->status=="APROVADO") $x->status="Aprovado";
								else if($x->status=="CANCELADO") $x->status="Cancelado";
						?>
						<div class="paciente-etapas__item">
							<h1 class="paciente__titulo1"><?php echo utf8_encode($x->titulo);?> <small>(<?php echo date('d/m/Y',strtotime($x->data));?>)</small><br />
							<p style="font-size:14px;"><?php echo $x->status;?></p></h1>
							<div class="paciente-etapas-grid">
								
								<p>Evolução<br /><span style="color: var(--cinza3);font-size:12px;"><?php echo "Realizado <b>".$finalizados."</b> de <b>".$total."</b> - ".$perc."%";?></span></p>
								<div class="grafico-barra"><span style="width:<?php echo $perc;?>%">&nbsp;</span></div>
								<p>Pagamento<br /><span style="color: var(--cinza3);font-size:12px;"><?php echo "Recebido <b>".number_format($pagPago,2,",",".")."</b> de <b>".number_format($pagTotal,2,",",".")."</b> - ".$percPag."%";?></span></p>
								<div class="grafico-barra"><span style="width:<?php echo $percPag;?>%">&nbsp;</span></div>
								
							</div>
						</div>
						<?php
							}
						} else {
						?>
						<div style="text-align: center;color:#CCC"><span class="iconify" data-icon="el:eye-close" data-inline="false" data-height="50"></span><br />Nenhum plano de tratamento</div>
						<?php	
						}
						?>
					</div>	

				</div>
			</div>
			<?php
			}
			?>

			

		</section>
	
	</section>


<?php
	include "includes/footer.php";
?>