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
		
		<section class="grid grid_3">
			
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

			<div class="box" style="grid-column:span 2;grid-row:span 2">
				<div class="paciente-evolucao" sty>
					<h1 class="paciente__titulo1">Prontuário</h1>
					<?php /*<a href="" class="paciente-evolucao__add"><i class="iconify" data-icon="mdi-plus-circle-outline"></i> Adicionar evolução</a>*/ ?>

					<div class="paciente-scroll">
						<?php
							$registros=array();
							$evolucoesIds=array(-1);
							$usuariosIds=array(-1);
							$sql->consult($_p."pacientes_evolucoes","*","where id_paciente=$paciente->id and lixo=0 order by data desc");
							while($x=mysqli_fetch_object($sql->mysqry)) {
								$registros[]=$x;
								$usuariosIds[]=$x->id_usuario;
								if($x->id_tipo==2 or $x->id_tipo==3 or $x->id_tipo==6 or $x->id_tipo==7) $evolucoesIds[]=$x->id;

							}

							$_usuarios=array();
							$sql->consult($_p."colaboradores","*","WHERE id IN (".implode(",",$usuariosIds).")");
							while($x=mysqli_fetch_object($sql->mysqry)) {
								$_usuarios[$x->id]=$x;
							}

							$tratamentoProdecimentosIds=array(-1);
							$registrosProcedimentos=array();
							$sql->consult($_p."pacientes_evolucoes_procedimentos","*","where id_paciente=$paciente->id and id_evolucao IN (".implode(",",$evolucoesIds).") and lixo=0 order by data desc");
							while($x=mysqli_fetch_object($sql->mysqry)) {
								$evolucoesIds[]=$x->id;
								$registrosProcedimentos[$x->id_evolucao][]=$x;
							}
							

							$prodecimentosIds=array(-1);
							$_tratamentoProcedimentos=array();
							$sql->consult($_p."pacientes_tratamentos_procedimentos","*","where id IN (".implode(",",$tratamentoProdecimentosIds).")");
							while($x=mysqli_fetch_object($sql->mysqry)) {
								$prodecimentosIds[]=$x->id_procedimento;
								$_tratamentoProcedimentos[$x->id]=$x;
							}


							$_exames=array();
							$sql->consult($_p."pacientes_evolucoes_pedidosdeexames","id,id_evolucao","where id_paciente=$paciente->id and id_evolucao IN (".implode(",",$evolucoesIds).") and lixo=0 order by data desc");
							while($x=mysqli_fetch_object($sql->mysqry)) {
								$_exames[$x->id_evolucao][]=$x;
							}

							$_receitas=array();
							$sql->consult($_p."pacientes_evolucoes_receitas","id,id_evolucao","where id_paciente=$paciente->id and id_evolucao IN (".implode(",",$evolucoesIds).") and lixo=0 order by data desc");
							while($x=mysqli_fetch_object($sql->mysqry)) {
								$_receitas[$x->id_evolucao][]=$x;
							} 

							$_procedimentos=array();
							$sql->consult($_p."parametros_procedimentos","*","where id IN (".implode(",",$prodecimentosIds).")");
							while($x=mysqli_fetch_object($sql->mysqry)) {
								$_procedimentos[$x->id]=$x;
							}
						?>

						<div class="reg">
							<?php
								foreach($registros as $x) {
									if(isset($_tiposEvolucao[$x->id_tipo])) {
										$tipo = $_tiposEvolucao[$x->id_tipo];
							?>
							<a href="<?php echo $tipo->pagina."?form=1&id_paciente=$paciente->id&edita=".$x->id;?>" class="reg-group">
								<div class="reg-color" style="background-color:;"></div>
								<div class="reg-data" style="width:5%">
									<i class="iconify" data-icon="<?php echo $tipo->icone;?>"></i>
								</div>

								<div class="reg-data" style="width:30%">
									<p><strong><?php echo utf8_encode($tipo->tituloSingular);?></strong></p>
									<p>Qtd.: <?php 
										if($x->id_tipo==2 or $x->id_tipo==3) {
											echo isset($registrosProcedimentos[$x->id])?count($registrosProcedimentos[$x->id]):0;
										} else if($x->id_tipo==6) {
											echo isset($_exames[$x->id])?count($_exames[$x->id]):0;

										} else if($x->id_tipo==7) {

											echo (isset($_receitas[$x->id])?count($_receitas[$x->id]):0);

										} else {
											echo 1;
										}
										?>
									</p>
								</div>

								<div class="reg-data" style="width:60%;color:#">
									<?php
										$autor=isset($_usuarios[$x->id_usuario])?utf8_encode($_usuarios[$x->id_usuario]->nome):'Desconhecido';
									?>
									<p>
										<span class="iconify" data-icon="bi:check-all"></span> <?php echo "<b>".$autor."</b> deu baixa em ";?>
										<b><?php echo date('d/m/Y',strtotime($x->data));?> - <?php echo date('H:i',strtotime($x->data));?></b>
									</p>
								</div>

							</a>
							<?php
									}
								}
							?>
						</div>
					</div>
				</div>
			</div>
			<?php
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
			$sql->consult($_p."pacientes_tratamentos_procedimentos","*","where id_tratamento IN (".implode(",",$tratamentosIDs).") and id_unidade = $usrUnidade->id and situacao='aprovado' and lixo=0");
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


			
			$sql->consult($_p."pacientes_tratamentos_pagamentos","*","where id_tratamento IN (".implode(",",$tratamentosIDs).") and id_unidade = $usrUnidade->id and id_fusao=0 and lixo=0");
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


			/*
			<script>
				$(function(){
					
					var ctx = document.getElementById('grafico1').getContext('2d');
					var grafico1 = new Chart(ctx, {    
					    type: 'doughnut',
					    options: {
					    	legend: {display:false},
					    	cutoutPercentage:70,
					    },
					    data: {
					        labels: ["Procedimento 1","Procedimento 2","Procedimento 3", "Procedimento 4"],
					        datasets: [{
					            data: [10,20,20,50],
					            backgroundColor: ['rgba(211,142,105,1)','rgba(239,198,155,1)','rgba(93,109,112,1)','rgba(72,74,71,1)','rgba(138,176,171,1)'],						            
					        }]
					    },
					});		
				});				
				</script>
				*/
			?>
			<div class="box" style="overflow:hidden;">
				<div class="paciente-etapas">
					<?php

		
					?>
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

			<div class="hist2 box" style="grid-column:span 3">
			
						<aside>
					<h1 class="paciente__titulo1">Histórico</h1>			
						<?php

						$_cadeiras=array();
						$sql->consult($_p."parametros_cadeiras","*","where lixo=0 order by ordem asc");
						while($x=mysqli_fetch_object($sql->mysqry)) $_cadeiras[$x->id]=$x;

						$_status=array();
						$sql->consult($_p."agenda_status","*","where lixo=0 order by titulo asc");
						while($x=mysqli_fetch_object($sql->mysqry)) $_status[$x->id]=$x;

						$sql->consult($_p."agenda","*","where id_paciente=$paciente->id and lixo=0 order by agenda_data desc");
						if($sql->rows) {
						?>
						<div class="reg">
							<?php
							while($x=mysqli_fetch_object($sql->mysqry)) {
								$statusCor='';

								if(isset($_status[$x->id_status])) {
									$statusCor=$_status[$x->id_status]->cor;
								}
							?>
							<a href="<?php echo "pg_agenda.php?initDate=".date('d/m/Y',strtotime($x->agenda_data));?>" target="_blank" class="reg-group">
								<div class="reg-color" style="background-color:<?php echo $statusCor;?>"></div>

								<div class="reg-data" style="width:30%">
									<p><?php echo date('d/m/Y H:i',strtotime($x->agenda_data));?></span></p>
								</div>

								<div class="reg-data" style="width:30%">
									<p><?php echo isset($_cadeiras[$x->id_cadeira])?utf8_encode($_cadeiras[$x->id_cadeira]->titulo):'';?></p>
								</div>
								<?php
								$profissionais="";
								if(!empty($x->profissionais)) {
									$profissionais='';
									$aux=explode(",",$x->profissionais);
									foreach($aux as $v) {
										if(!empty($v) and is_numeric($v) and isset($_profissionais[$v])) {
											//$profissionais.='<div class="cal-item-foto"><span style="background:'.$_profissionais[$v]->calendario_cor.'">'.$_profissionais[$v]->calendario_iniciais.'</span></div>';
											$profissionais.='<div class="cal-item-foto" style="float:left;"><span style="background:'.$_profissionais[$v]->calendario_cor.'">'.$_profissionais[$v]->calendario_iniciais.'</span></div>';
										}
									}
								}
								?>
								<div class="cal-item__fotos">
									<?php echo $profissionais;?>
								</div>
							</a>
							<?php
							}
							?>
						</div>
						<?php
						}
						?>
					</aside>
				
			</div>

			<div class="hist2 box" style="grid-column:span 3">
				<aside>
					<h1 class="paciente__titulo1">Histórico</h1>										
					<div class="grid-links grid-links_sm">
						<a href="pg_contatos_pacientes_evolucao_anamnese.php?id_paciente=6596" class="active">
							<i class="iconify" data-icon="mdi:format-list-bulleted"></i>
							<p>Todos</p>
						</a>
						<a href="pg_contatos_pacientes_evolucao_anamnese.php?id_paciente=6596">
							<i class="iconify" data-icon="mdi:chat-processing-outline"></i>
							<p>Relacionamento</p>
						</a>
						<a href="pg_contatos_pacientes_evolucao_anamnese.php?id_paciente=6596">
							<i class="iconify" data-icon="mdi:calendar-check"></i>
							<p>Agendamentos</p>
						</a>
						<a href="pg_contatos_pacientes_evolucao_anamnese.php?id_paciente=6596">
							<i class="iconify" data-icon="mdi:finance"></i>
							<p>Financeiro</p>
						</a>						
					</div>
				</aside>
				<article>
					<div class="hist2-item">
						<div class="hist2-item__inner1">
							<div class="hist2-item__icone"><i class="iconify" data-icon="mdi:chat-processing-outline"></i></div>
						</div>
						<div class="hist2-item__inner2">
							<div class="hist2-item__dados">
								<h1>11/10/2021 09:30 - Dr. Kroner Machado Costa</h1>
								<p><strong>Não consegui contato:</strong> tentei entrar em contato em todos os números várias vezes</p>
							</div>							
						</div>
					</div>
					<div class="hist2-item">
						<div class="hist2-item__inner1">
							<div class="hist2-item__icone"><i class="iconify" data-icon="mdi:calendar-check"></i></div>
						</div>
						<div class="hist2-item__inner2">
							<div class="hist2-item__dados">
								<h1>11/10/2021 09:30 - Dr. Kroner Machado Costa</h1>
								<p><strong>Agendado para 09/12/2021 08:00</strong> - Consultório 3 - Dr. Simone Helena dos Santos <span style="background:#fc8008">sala de espera</span></p>
								<a href="javascript:;" onclick="$(this).parent().next('.hist2-item__detalhes').slideToggle('fast');" class="button button__alt button__sm"><i class="iconify" data-icon="mdi:chevron-down"></i> mais detalhes</a>
							</div>
							<div class="hist2-item__detalhes" style="display:none;">
								<div class="hist2-item__dados">
									<h1>11/10/2021 09:30 - Dr. Kroner Machado Costa</h1>
									<p><strong>Agendado para 09/12/2021 08:00</strong> - Consultório 3 - Dr. Simone Helena dos Santos <span style="background:#1182ea">confirmado</span></p>
								</div>
								<div class="hist2-item__dados">
									<h1>11/10/2021 09:30 - Dr. Kroner Machado Costa</h1>
									<p><strong>Agendado para 09/12/2021 08:00</strong> - Consultório 3 - Dr. Simone Helena dos Santos <span style="background:#545559">à confirmar</span></p>
								</div>
							</div>
						</div>
					</div>
					<div class="hist2-item">
						<div class="hist2-item__inner1">
							<div class="hist2-item__icone"><i class="iconify" data-icon="mdi:finance"></i></div>
						</div>
						<div class="hist2-item__inner2">
							<div class="hist2-item__dados">
								<h1>11/10/2021 09:30 - Dr. Kroner Machado Costa</h1>
								<p><strong>Inadimplente - R$15.217,50</strong><br />Reabilitação Full - Pedro</p>
							</div>
						</div>
					</div>
				</article>
			</div>

		</section>
	
	</section>


<?php
	include "includes/footer.php";
?>