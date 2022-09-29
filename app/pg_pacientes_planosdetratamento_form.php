<?php
	include "includes/header.php";
	include "includes/nav.php";

	require_once("includes/header/headerPacientes.php");

	// configuracao da pagina
		$_table=$_p."pacientes_tratamentos";
		$_page=basename($_SERVER['PHP_SELF']);


	// dados
		// profissionais
			$_profissionais=array();
			$sql->consult($_p."colaboradores","id,nome,calendario_iniciais,foto,calendario_cor,check_agendamento,contratacaoAtiva","where tipo_cro<>'' and lixo=0 order by nome asc");
			while($x=mysqli_fetch_object($sql->mysqry)) $_profissionais[$x->id]=$x;

		// procedimentos
			$_procedimentos=array();
			$sql->consult($_p."parametros_procedimentos","*","where lixo=0");
			while($x=mysqli_fetch_object($sql->mysqry)) $_procedimentos[$x->id]=$x;

		// regioes
			$_regioesOpcoes=array();
			$sql->consult($_p."parametros_procedimentos_regioes_opcoes","*","order by titulo asc");
			while($x=mysqli_fetch_object($sql->mysqry)) $_regioesOpcoes[$x->id_regiao][]=$x;

			$_regioes=array();
			$sql->consult($_p."parametros_procedimentos_regioes","*","");
			while($x=mysqli_fetch_object($sql->mysqry)) $_regioes[$x->id]=$x;

			$_regioesFaces=array();
			$_regioesFacesOptions='';
			$_regioesInfos=array();
			$sql->consult($_p."parametros_procedimentos_regioes_faces","*"," order by titulo asc");
			while($x=mysqli_fetch_object($sql->mysqry)) {
				$_regioesFaces[$x->id]=$x;
				$_regioesFacesOptions.='<option value="'.$x->id.'">'.utf8_encode($x->titulo).'</option>';
				$_regioesInfos[$x->id]=array('abreviacao'=>$x->abreviacao,'titulo'=>utf8_encode($x->titulo));
			}

		// situacao
			$_selectSituacaoOptions=array('aprovado'=>array('titulo'=>'APROVADO','cor'=>'green'),
											'naoAprovado'=>array('titulo'=>'REPROVADO','cor'=>'red'));

			$selectSituacaoOptions='';
			foreach($_selectSituacaoOptions as $key=>$value) {
				$selectSituacaoOptions.='<option value="'.$key.'">'.$value['titulo'].'</option>';
			}

		// formas de pagamento
			$_formasDePagamento=array();
			$optionFormasDePagamento='';
			$sql->consult($_p."parametros_formasdepagamento","*","order by titulo asc");
			while($x=mysqli_fetch_object($sql->mysqry)) {
				$_formasDePagamento[$x->id]=$x;
				$optionFormasDePagamento.='<option value="'.$x->id.'" data-tipo="'.$x->tipo.'">'.utf8_encode($x->titulo).'</option>';
			}

		// credito, debito, bandeiras
			$_bandeiras=array();
			$sql->consult($_p."parametros_cartoes_bandeiras","*","where lixo=0");
			while($x=mysqli_fetch_object($sql->mysqry)) {
				$_bandeiras[$x->id]=$x;
			}


			$creditoBandeiras=array();
			$debitoBandeiras=array();
	
			$sql->consult($_p."parametros_cartoes_operadoras","*","where lixo=0 order by titulo");
			while($x=mysqli_fetch_object($sql->mysqry)) {
				$creditoBandeiras[$x->id]=array('titulo'=>utf8_encode($x->titulo),'bandeiras'=>array());
				$debitoBandeiras[$x->id]=array('titulo'=>utf8_encode($x->titulo),'bandeiras'=>array());
			}

			$sql->consult($_p."parametros_cartoes_operadoras_bandeiras","*","where lixo=0");
			while($x=mysqli_fetch_object($sql->mysqry)) {
				if(isset($_bandeiras[$x->id_bandeira])) {
					$bandeira=$_bandeiras[$x->id_bandeira];
					
					$txJson = json_decode($x->taxas);


					if($x->check_debito==1) {
						$debitoTaxa=isset($txJson->debitoTaxas->taxa)?$txJson->debitoTaxas->taxa:0;
						$debitoDias=isset($txJson->debitoTaxas->dias)?$txJson->debitoTaxas->dias:0;
						$debitoBandeiras[$x->id_operadora]['bandeiras'][$x->id_bandeira]=array('id_bandeira'=>$x->id_bandeira,
																								'titulo'=>utf8_encode($bandeira->titulo),
																							 	'taxa'=>$debitoTaxa,
																							 	'dias'=>$debitoDias);
					}
					if($x->check_credito==1) {
						$creditoBandeiras[$x->id_operadora]['bandeiras'][$x->id_bandeira]=array('id_bandeira'=>$x->id_bandeira,
																								'titulo'=>utf8_encode($bandeira->titulo),
																								'parcelas'=>$x->credito_parcelas,
																								'semJuros'=>$x->credito_parcelas_semjuros);
					}
				}
			}



			/*$_semJuros=array();
			$sql->consult($_p."parametros_cartoes_taxas_semjuros","*","where lixo=0");
			while($x=mysqli_fetch_object($sql->mysqry)) {
				$_semJuros[$x->id_operadora][$x->id_bandeira]=$x->semjuros;
			}


			$sql->consult($_p."parametros_cartoes_taxas","*","where lixo=0");
			$_taxasCredito=$_taxasCreditoSemJuros=array();
			while($x=mysqli_fetch_object($sql->mysqry)) {
				if(isset($_bandeiras[$x->id_bandeira])) {
					$bandeira=$_bandeiras[$x->id_bandeira];
					if($x->operacao=="credito") {
						if(isset($creditoBandeiras[$x->id_operadora])) {
							$semJurosTexto="";
							if($bandeira->parcelasAte>0) {
								$semJurosTexto.=" - em ate ".$bandeira->parcelasAte."x";
							}
							if(!isset($_taxasCredito[$x->id_operadora][$bandeira->id][$x->parcela])) {
								$_taxasCredito[$x->id_operadora][$bandeira->id][$x->parcela]=$x->taxa;
							}

							$creditoBandeiras[$x->id_operadora]['bandeiras'][$bandeira->id]=array('id_bandeira'=>$bandeira->id,
																								//	'semJuros'=>$semJuros,
																									'parcelas'=>$bandeira->parcelasAte,
																									'taxa'=>$x->taxa,	
																									'titulo'=>utf8_encode($bandeira->titulo).$semJurosTexto);
						}
					} else {

						$debitoBandeiras[$x->id_operadora]['bandeiras'][$bandeira->id]=array('id_bandeira'=>$bandeira->id,
																								'titulo'=>utf8_encode($bandeira->titulo),
																								'taxa'=>$x->taxa);
					}

				}
			}*/


	// formulario
		$cnt='';
		$campos=explode(",","titulo,id_profissional");
			
		foreach($campos as $v) $values[$v]='';
		$values['procedimentos']="[]";
		$values['pagamentos']="[]";


		$sql->consult($_table,"id","where id_paciente=$paciente->id");
		$values['titulo']="Plano de tratamento ".($sql->rows+1);
		
?>
	<script type="text/javascript">
		var procedimentos = [];
		var pagamentos = [];
		var usuario = '<?php echo utf8_encode($usr->nome);?>';
		var id_usuario = <?php echo $usr->id;?>;

		$(function(){
			$('.js-btn-adicionarProcedimento').click(function(){
				$(".aside-plano-procedimento-adicionar").fadeIn(100,function() {
					$(".aside-plano-procedimento-adicionar .aside__inner1").addClass("active");
				});

				$('.aside-plano-procedimento-adicionar .js-asidePlano-id_procedimento').chosen('destroy');
				$('.aside-plano-procedimento-adicionar .js-asidePlano-id_procedimento').chosen();
				
			})
		});

	</script>

	<main class="main">
		<div class="main__content content">

			<section class="filter">
				
				<div class="filter-group">				
				</div>

				<div class="filter-group">
					<div class="filter-form form">
						<dl>
							<dd><a href="" class="button"><i class="iconify" data-icon="fluent:arrow-left-24-regular"></i></a></dd>
						</dl>
						<dl>
							<dd><a href="" class="button"><i class="iconify" data-icon="fluent:delete-24-regular"></i></a></dd>
						</dl>
						<dl>
							<dd><a href="" class="button"><i class="iconify" data-icon="fluent:print-24-regular"></i></a></dd>
						</dl>
						<dl>
							<dd><a href="" class="button button_main"><i class="iconify" data-icon="fluent:checkmark-12-filled"></i><span>Salvar</span></a></dd>
						</dl>
					</div>
				</div>
				
			</section>

			<form method="post" class="form">
				
				<div class="grid grid_2">

					<!-- Identificacao -->
					<fieldset>
						<legend>Identificação</legend>
						<dl>
							<dd>
								
								<?php
								if(is_object($cnt)) {
								?>
								<div class="button-group">
									<a href="" class="button active"><i class="iconify" data-icon="fluent:timer-24-regular"></i><span>Aguard. Aprovação</span></a>
									<a href="" class="button"><i class="iconify" data-icon="fluent:checkbox-checked-24-filled"></i><span>Aprovado</span></a>
									<a href="" class="button"><i class="iconify" data-icon="fluent:dismiss-square-24-regular"></i><span>Reprovado</span></a>
								</div>
								<?php
								} else {
								?>
								<div class="button-group tooltip" style="opacity:0.4" title="Salve o tratamento para poder alterar o status">
									<a href="javascript:;" class="button"><i class="iconify" data-icon="fluent:timer-24-regular"></i><span>Aguard. Aprovação</span></a>
									<a href="javascript:;" class="button"><i class="iconify" data-icon="fluent:checkbox-checked-24-filled"></i><span>Aprovado</span></a>
									<a href="javascript:;" class="button"><i class="iconify" data-icon="fluent:dismiss-square-24-regular"></i><span>Reprovado</span></a>
								</div>
								<?php
								}
								?>
							</dd>
						</dl>
						<div class="colunas">
							<dl>
								<dt>Título</dt>
								<dd><input type="text" name="titulo" value="<?php echo $values['titulo'];?>" /></dd>
							</dl>
							<dl>
								<dt>Profissional</dt>
								<dd>
									<select name="id_profissional" class="js-id_profissional" placeholder="Selecione o Profissional">
										<option value="">Selecione o Profissional...</option>
										<?php
										foreach($_profissionais as $x) {
											if($x->check_agendamento==0 or $x->contratacaoAtiva==0) continue;
											$iniciais=$x->calendario_iniciais;
											echo '<option value="'.$x->id.'" data-iniciais="'.$iniciais.'" data-iniciaisCor="'.$x->calendario_cor.'"'.($values['id_profissional']==$x->id?" selected":"").'>'.utf8_encode($x->nome).'</option>';
										}
										?>
									</select>
								</dd>
							</dl>					
						</div>
					</fieldset>

					<!-- Financeiro -->
					<fieldset style="grid-row:span 2">
						<legend>Financeiro</legend>
						<textarea id="js-textarea-pagamentos" style="display:;"></textarea>

						<dl>
							<dd>
								<a href="javascript:;" class="button button_main js-btn-desconto"><i class="iconify" data-icon="fluent:money-calculator-24-filled"></i><span>Descontos</span></a>
							</dd>
						</dl>

						<div class="colunas3">
							<dl>
								<dt>Valor Total (R$)</dt>
								<dd style="font-size:1.75em; font-weight:bold;" class="js-valorTotal">0,00</dd>
							</dl>
							<dl class="dl2">
								<dt>Forma de Pagamento</dt>
								<dd>
									<label><input type="radio" name="pagamento" value="avista" disabled />A Vista</label>
									<label><input type="radio" name="pagamento" value="parcelado" disabled />Parcelado em</label>
									<label><input type="number" name="parcelas" class="js-pagamentos-quantidade" value="2" style="width:50px;display:none;" /></label>
								</dd>
							</dl>							
						</div>

						<div class="fpag js-pagamentos" style="margin-top:2rem;">
							<?php

							/*
							?>
							<div class="fpag-item">
								<aside>1</aside>
								<article>
									<div class="colunas3">
										<dl>
											<dd class="form-comp"><span><i class="iconify" data-icon="fluent:calendar-ltr-24-regular"></i></span><input type="tel" name="" class="data" value="07/09/2022" /></dd>
										</dl>
										<dl>
											<dd class="form-comp"><span>R$</i></span><input type="tel" name="" class="valor" value="" /></dd>
										</dl>
										<dl>
											<dd>
												<select class="js-id_formadepagamento js-tipoPagamento">
													<option value="9" data-tipo="boleto">BOLETO</option>
												</select>
											</dd>
										</dl>
									</div>
									<div class="colunas3">
										<dl>
											<dt>Identificador</dt>
											<dd><input type="text" name="" /></dd>
										</dl>
									</div>
								</article>
							</div>

							<div class="fpag-item">
								<aside>2</aside>
								<article>
									<div class="colunas3">
										<dl>
											<dd class="form-comp"><span><i class="iconify" data-icon="fluent:calendar-ltr-24-regular"></i></span><input type="tel" name="" class="data" value="07/09/2022" /></dd>
										</dl>
										<dl>
											<dd class="form-comp"><span>R$</i></span><input type="tel" name="" class="valor" value="" /></dd>
										</dl>
										<dl>
											<dd>
												<select class="js-id_formadepagamento js-tipoPagamento">
													<option value="9" data-tipo="boleto">CARTÃO DE CRÉDITO</option>
												</select>
											</dd>
										</dl>
									</div>
									<div class="colunas3">
										<dl>
											<dt>Bandeira</dt>
											<dd><select name=""><option value=""></option></select></dd>
										</dl>
										<dl>
											<dt>Parcelas</dt>
											<dd><select name=""><option value="">1x</option></select></dd>
										</dl>
										<dl>
											<dt>Identificador</dt>
											<dd><input type="text" name="" /></dd>
										</dl>
									</div>
								</article>
							</div>
							*/?>
						</div>
					</fieldset>

					<!-- Procedimentos --> 
					<fieldset>
						<legend>Procedimentos</legend>
						<textarea id="js-textarea-procedimentos" style="display:none"></textarea>
						<dl>
							<dd>
								<a href="javascript:;" ata-aside="plano-procedimento-adicionar" class="button button_main js-btn-adicionarProcedimento"><i class="iconify" data-icon="fluent:add-circle-24-regular"></i><span>Adicionar Procedimento</span></a>
							</dd>
						</dl>
						
						<div class="list1">
							<table id="js-table-procedimentos">
								
							</table>
						</div>
					</fieldset>


				</div>


			</form>

		</div>
	</main>

<?php 
	
	require_once("includes/api/apiAsidePlanoDeTratamento.php");

	include "includes/footer.php";
?>	