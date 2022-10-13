<?php
	if(isset($_POST['ajax'])) {

		$dir="../../";
		require_once("../../lib/conf.php");
		require_once("../../usuarios/checa.php");

		$rtn=array();

		if($_POST['ajax']=="planos") {
			$planos=array();
			if(isset($_POST['id_procedimento']) and is_numeric($_POST['id_procedimento'])) {
				$sql->consult($_p."parametros_procedimentos","*","where id='".addslashes($_POST['id_procedimento'])."' and lixo=0");
				if($sql->rows) {
					$procedimento=mysqli_fetch_object($sql->mysqry);
				}
			}

			if(is_object($procedimento)) {
				$sql->consult($_p."parametros_procedimentos_planos","*","where id_procedimento=$procedimento->id"); 
				
				$planosID=array();
				$procedimentoPlano=array();
				while($x=mysqli_fetch_object($sql->mysqry)) {
					$procedimentoPlano[$x->id_plano]=$x;
					$planosID[]=$x->id_plano;
				}	


				if(count($planosID)) {
					$sql->consult($_p."parametros_planos","*","where id IN (".implode(",",$planosID).")");
					if($sql->rows) {
						while($x=mysqli_fetch_object($sql->mysqry)) {
							if(isset($procedimentoPlano[$x->id])) {
								$procP=$procedimentoPlano[$x->id];
								$planos[]=array('id'=>$x->id,'titulo'=>utf8_encode($x->titulo),'valor'=>$procP->valor);
							}
						}
					}
				}

				$rtn=array('success'=>true,'planos'=>$planos);
			} else {
				$rtn=array('success'=>false,'error'=>'Procedimento não encontrado!');
			}
		}

		header("Content-type: application/json");
		echo json_encode($rtn);
		die();
	}

?>
<script type="text/javascript">

	var facesInfos = JSON.parse(`<?php echo json_encode($_regioesInfos);?>`);
	var id_usuario = '<?php echo $usr->id;?>';
	var autor = '<?php echo utf8_encode($usr->nome);?>';

	const atualizaValor = (atualizarParcelas) => {
		valorTotal=0;

		let cont = 1;
		procedimentos.forEach(x=> {
			if(x.situacao!='naoAprovado') {


				valorProcedimento=x.valor;
				if(x.quantitativo==1) valorProcedimento*=x.quantidade;
				if(x.face==1) valorProcedimento*=x.faces.length;

				//valorProcedimento=valorProcedimento.toFixed(2);
				if(x.desconto>0) {
					valorTotal+=eval(valorProcedimento-x.desconto);
					//valorTotal=valorTotal.toFixed(2);
				}
				else {
					valorTotal+=eval(valorProcedimento);
					//valorTotal=valorTotal.toFixed(2);
				}
			}

			if(cont==procedimentos.length) {
				$('.js-valorTotal').html(number_format(valorTotal,2,",","."));
			}
			cont++;
			
		});


		let parcelas = [];

		if(atualizarParcelas===true && $('input[name=pagamento]:checked').length>0) {
			let pagamento = $('input[name=pagamento]:checked').val();

			if(pagamento=="avista") {
				$('.js-pagamentos-quantidade').hide();

				let startDate = new Date();
				let item = {};
				let mes = startDate.getMonth()+1;
				mes = mes <= 9 ? `0${mes}`:mes;

				let dia = startDate.getDate();
				dia = dia <= 9 ? `0${dia}`:dia;
				item.vencimento=`${dia}/${mes}/${startDate.getFullYear()}`;
				item.valor=valorTotal;
				parcelas.push(item);

				newDate = startDate;
				newDate.setMonth(newDate.getMonth()+1);

				startDate=newDate;
				

			} else {
				$('.js-pagamentos-quantidade').show();
				let numeroParcelas = $('.js-pagamentos-quantidade').val();

				if(numeroParcelas.length==0 || numeroParcelas<=0) numeroParcelas=2;
				
				valorParcela=valorTotal/numeroParcelas;

				valorParcela=valorParcela.toFixed(2);

				let startDate = new Date();

				if($('.js-vencimento:eq(0)').val()!=undefined) {
					aux = $('.js-vencimento:eq(0)').val().split('/');
					startDate = new Date();//`${aux[2]}-${aux[1]}-${aux[0]}`);
					startDate.setDate(aux[0]);
					startDate.setMonth(eval(aux[1])-1);
					startDate.setFullYear(aux[2]);
				}
				for(var i=1;i<=numeroParcelas;i++) {
					/*val = -1;
					if($(`.js-pagamentos .js-valor:eq(${i})`).length) {
						val = $(`.js-pagamentos .js-valor:eq(${(i-1)})`).val();
					}
					//console.log(`${$(`.js-pagamentos .js-valor:eq(${i})`).length} -> .js-pagamentos .js-valor:eq(${(i-1)}) => ${val}`);*/

					let item = {};
					if(pagamentos[i-1]) {
						//item = pagamentos[i-1];
						console.log('-');
						console.log(pagamentos[i-1]);
					}
					let mes = startDate.getMonth()+1;
					mes = mes <= 9 ? `0${mes}`:mes;

					let dia = startDate.getDate();
					dia = dia <= 9 ? `0${dia}`:dia;
					item.vencimento=`${dia}/${mes}/${startDate.getFullYear()}`;
					item.valor=valorParcela;




					parcelas.push(item);

					newDate = startDate;
					newDate.setMonth(newDate.getMonth()+1);

					startDate=newDate;
				}

			}

			pagamentos=parcelas;
		}

		pagamentosListar();
	}

	const pagamentosListar = () => {
		$('.js-pagamentos').html('');

		//console.log(pagamentos);
		if(pagamentos.length>0) {

			let index=1;
			pagamentos.forEach(x=>{
				$('.js-pagamentos').append(`<div class="fpag-item js-pagamento-item">
												<aside>${index++}</aside>
												<article>
													<div class="colunas3">
														<dl>
															<dd class="form-comp"><span><i class="iconify" data-icon="fluent:calendar-ltr-24-regular"></i></span><input type="tel" name="" class="data js-vencimento" value="${x.vencimento}" /></dd>
														</dl>
														<dl>
															<dd class="form-comp"><span>R$</i></span><input type="tel" name="" class="valor js-valor" value="${number_format(x.valor,2,",",".")}" /></dd>
														</dl>
														<dl>
															<dd>
																<select class="js-id_formadepagamento js-tipoPagamento">
																	<option value="">Forma de Pagamento...</option>
																	<?php echo $optionFormasDePagamento;?>
																</select>
															</dd>
														</dl>
													</div>
														
													<div class="colunas3">

														<dl style="display:none">
															<dt>Bandeira</dt>
															<dd>

															<select class="js-debitoBandeira js-tipoPagamento">
																<option value="">selecione</option>
																<?php
																foreach($debitoBandeiras as $id_operadora=>$x) {
																	echo '<optgroup label="'.utf8_encode($x['titulo']).'">';
																	foreach($x['bandeiras'] as $band) {
																		echo '<option value="'.$band['id_bandeira'].'" data-id_operadora="'.$id_operadora.'">'.utf8_encode($band['titulo']).'</option>';
																	}
																	echo '</optgroup>';
																}
																?>
															</select>
														</dd></dl>


														<dl style="display:none">
															<dt>Bandeira</dt>
															<dd>
																<select class="js-creditoBandeira js-tipoPagamento">
																	<option value="">selecione</option>
																	<?php
																	foreach($creditoBandeiras as $id_operadora=>$x) {
																		echo '<optgroup label="'.utf8_encode($x['titulo']).'">';
																		foreach($x['bandeiras'] as $band) {
																			echo '<option value="'.$band['id_bandeira'].'" data-parcelas="'.$band['parcelas'].'" data-parcelas-semjuros="'.$band['semJuros'].'" data-id_operadora="'.$id_operadora.'" data-id_operadorabandeira="'.($id_operadora.$band['id_bandeira']).'">'.utf8_encode($band['titulo']).'</option>';
																		}
																		echo '</optgroup>';
																	}
																	?>
																</select>
															</dd>
														</dl>

														<dl style="display:none">
															<dt>Qtd. Parcelas</dt>
															<dd>
																<select class="js-parcelas js-tipoPagamento">
																	<option value="">selecione a bandeira</option>
																</select>
															</dd>
														</dl>

														<dl style="display:none">
															<dt>Identificador</dt>
															<dd><input type="text" class="js-identificador js-tipoPagamento" /></dd>
														</dl>

													</div>
												</article>
											</div>`);

				$('.js-pagamento-item .js-vencimento:last').inputmask('99/99/9999');
				$('.js-pagamento-item .js-vencimento:last').datetimepicker({timepicker:false,
																		format:'d/m/Y',
																		scrollMonth:false,
																		scrollTime:false,
																		scrollInput:false});
				$('.js-pagamento-item .js-valor:last').maskMoney({symbol:'', allowZero:true, showSymbol:true, thousands:'.', decimal:',', symbolStay: true});

				if(x.id_formapagamento) { 
					$('.js-pagamento-item .js-id_formadepagamento:last').val(x.id_formapagamento);
					$('.js-pagamento-item .js-identificador:last').val(x.identificador);
					let tipo = $('.js-pagamento-item .js-id_formadepagamento:last option:selected').attr('data-tipo');

					if(tipo=="credito") {
						parcelaProv=x.qtdParcelas;

						$('.js-pagamento-item .js-creditoBandeira:last').find(`option[data-id_operadorabandeira=${x.id_operadora}${x.creditoBandeira}]`).prop('selected',true);
						creditoBandeiraAtualizaParcelas($('.js-pagamento-item .js-creditoBandeira:last'),x.qtdParcelas);
					
					} else if(tipo=="debito") {
						$('.js-pagamento-item .js-debitoBandeira:last').val(x.debitoBandeira);
					}	
					pagamentosAtualizaCampos($('.js-pagamento-item .js-id_formadepagamento:last'),false);
				}
			});

			if(pagamentos.length==1) $('.js-pagamento-item .js-valor:last').prop('disabled',true);
		}
		//console.log(pagamentos);
		$('#js-textarea-pagamentos').val(JSON.stringify(pagamentos))
		//atualizaValor();
		//desativarCampos();
	}

	const procedimentosListar = () => {

		$('#js-table-procedimentos').html('');
		let cont = 1;

		if(procedimentos.length>0) {
			procedimentos.forEach(x=>{

				let valor = '';


				/*procedimentoValor=x.valor;
				procedimentoValorCorrigido=x.valorCorrigido;
				if(eval(x.quantitativo)==1) {
					procedimentoValor*=x.quantidade;
					procedimentoValorCorrigido*=x.quantidade;
				}

				if(x.desconto) {
					valor = `<strike>${number_format(procedimentoValor,2,",",".")}</strike><br />${number_format(procedimentoValorCorrigido,2,",",".")}`;
				} else {
					valor = number_format(procedimentoValorCorrigido?procedimentoValorCorrigido:procedimentoValor,2,",",".");
				}*/


				procedimentoValor=x.valor;
				if(x.quantitativo==1) procedimentoValor*=x.quantidade;
				if(x.face==1) procedimentoValor*=x.faces.length;

				if(x.desconto>0) {
					procedimentoValorComDesconto=procedimentoValor-x.desconto;
					valor = `<strike>${number_format(procedimentoValor,2,",",".")}</strike><br />${number_format(procedimentoValorComDesconto,2,",",".")}`;
				} else {
					valor = number_format(procedimentoValor,2,",",".");
				}



				let opcao = '';
				if(x.id_regiao==4) {
					opcaoAux='';
					if(x.face==1) {
						
						let cont = 1;
						x.faces.forEach(fId=>{
							opcaoAux+=facesInfos[fId].abreviacao+', ';

							if(cont==x.faces.length) {
								opcaoAux=opcaoAux.substr(0,opcaoAux.length-2);
							}
							cont++;
						})
					}
					opcao=`<i class="iconify" data-icon="mdi:tooth-outline"></i> ${x.opcao}<br />${opcaoAux}`;
				}
				else {
					if(x.quantitativo==1) opcao=`Qtd. ${x.quantidade}`;
					else opcao=x.opcao;
				}

				let reprovadoCss='';
				if(x.situacao=="naoAprovado") reprovadoCss=` style="opacity:0.3"`;

				let tr = `<tr class="js-tr-item"${reprovadoCss}>								
							<td>
								<h1>${x.procedimento}</h1>
								<p>${x.plano}</p>
							</td>
							<td><div class="list1__icon">${opcao}</td>
							<td style="text-align:right;">${valor}</td>
						</tr>`;


				$('#js-table-procedimentos').append(tr);
				if(cont==procedimentos.length) { 
					atualizaValor(false);
				}
				cont++;

			});
			$('input[name=pagamento]').prop('disabled',false);
		} else {	
			$('input[name=pagamento]').prop('disabled',true);
		}

		$('#js-textarea-procedimentos').val(JSON.stringify(procedimentos));
		$('.aside-plano-procedimento-adicionar .js-fieldset-adicionar').find('select').val('').trigger('chosen:updated').trigger('change');
		$('.aside-plano-procedimento-adicionar .js-fieldset-faces .js-faces').remove();
		$('.aside-plano-procedimento-adicionar .js-fieldset-adicionar').find('textarea,input').val('');
		$('.aside-plano-procedimento-adicionar .aside-close').click();
	}

	const procedimentoEditar = (index) => {
		if(procedimentos[index]) {
			pEd=procedimentos[index];

			valorTabela=pEd.valor;
			regiao=pEd.opcao;

			if(pEd.faces.length>0) {
				regiaoAux='';
				cont = 1;
				pEd.faces.forEach(idF=>{
					regiaoAux+=facesInfos[idF].titulo+', ';
					if(cont==pEd.faces.length) {
						regiaoAux=regiaoAux.substr(0,regiaoAux.length-2)+'.';
						regiao+=`: ${regiaoAux}`;
					}
					cont++;
				})
			}

			if(pEd.quantitativo==1) valorTabela*=pEd.quantidade;
			else if(pEd.face==1) valorTabela*=pEd.faces.length;
 
			$('.aside-plano-procedimento-editar .js-asidePlanoEditar-index').val(index);
			$('.aside-plano-procedimento-editar .js-asidePlanoEditar-valorTabela').val(number_format(valorTabela,2,",","."));
			$('.aside-plano-procedimento-editar .js-asidePlanoEditar-valorDesconto').val(number_format(pEd.desconto,2,",","."));
			$('.aside-plano-procedimento-editar .js-asidePlanoEditar-valorCorrigido').val(number_format(pEd.valorCorrigido,2,",","."));
			$('.aside-plano-procedimento-editar .js-asidePlanoEditar-valorUnitario').val(number_format(pEd.valor,2,",","."));
			$('.aside-plano-procedimento-editar .js-asidePlanoEditar-obs').val(pEd.obs);
			$('.aside-plano-procedimento-editar .js-asidePlanoEditar-procedimento').val(pEd.procedimento);
			$('.aside-plano-procedimento-editar .js-asidePlanoEditar-situacao').val(pEd.situacao);
			$('.aside-plano-procedimento-editar .js-asidePlanoEditar-plano').val(pEd.plano);
			$('.aside-plano-procedimento-editar .js-asidePlanoEditar-regiao').val(regiao);
			$('.aside-plano-procedimento-editar .js-asidePlanoEditar-quantidade').val(pEd.quantidade);
			$('.aside-plano-procedimento-editar .js-asidePlanoEditar-data').html(pEd.data);
			$('.aside-plano-procedimento-editar .js-asidePlanoEditar-usuario').html(pEd.autor);

			if(pEd.quantitativo==1) {
				$('.aside-plano-procedimento-editar .js-asidePlanoEditar-quantidade').parent().parent().parent().show();
				$('.aside-plano-procedimento-editar .js-asidePlanoEditar-regiao').parent().parent().hide();
			} else if(pEd.opcao.length>0) {
				$('.aside-plano-procedimento-editar .js-asidePlanoEditar-quantidade').parent().parent().parent().hide();
				$('.aside-plano-procedimento-editar .js-asidePlanoEditar-regiao').parent().parent().show();
			} else {
				$('.aside-plano-procedimento-editar .js-asidePlanoEditar-quantidade').parent().parent().parent().hide();
				$('.aside-plano-procedimento-editar .js-asidePlanoEditar-regiao').parent().parent().hide();
			}

			$(".aside-plano-procedimento-editar").fadeIn(100,function() {
				$(".aside-plano-procedimento-editar .aside__inner1").addClass("active");
			});

		}
	}

	// atualiza valores dos campos do box desconto
	const descontoAtualizar = () => {

		let cont = 0;
		let totalProcedimentos = 0;
		let totalDescontoAplicado = 0;
		$('#js-descontos-table-procedimentos .js-desconto-procedimento').each(function(ind,el) {
			
			let index = $(el).attr('data-index');
			if($(el).prop('checked')===true) {
				console.log(eval(procedimentos[index].quantitativo));
				if(eval(procedimentos[index].quantitativo)==1) {
					totalProcedimentos+=(eval(procedimentos[index].valor)*procedimentos[index].quantidade);
				} else {
					totalProcedimentos+=eval(procedimentos[index].valor);
				}
				totalDescontoAplicado+=eval(procedimentos[index].desconto);
			}

			cont++;

			if(cont==procedimentos.length) {

			}
		});


		$('.aside-plano-desconto .js-total-procedimentos').val(number_format(totalProcedimentos,2,",","."));
		$('.aside-plano-desconto .js-total-descontosAplicados').val(number_format(totalDescontoAplicado,2,",","."));

		let valor = 0;
		if($('.aside-plano-desconto .js-input-desconto').val().length>0) {
			if($('.aside-plano-desconto .js-select-tipoDesconto').val()=="dinheiro") {
				valor = unMoney($('.aside-plano-desconto .js-input-desconto').val());
				$('.aside-plano-desconto .js-total-descontos').val(number_format(valor,2,",","."));
			} else {

				valor = unMoney($('.aside-plano-desconto .js-input-desconto').val().replace(".",","));
			
				if(valor>100) {
					valor = 100;
					$('.aside-plano-desconto .js-input-desconto').val('100')
				}

				valor = totalProcedimentos * (valor/100);
				valor = valor.toFixed(2);

				$('.aside-plano-desconto .js-total-descontos').val(number_format(valor,2,",","."));
			}
		}
		$('.aside-plano-desconto .js-total-procedimentosdescontos').val(number_format(totalProcedimentos-totalDescontoAplicado,2,",","."));
	}

	// lista procedimentos no box desconto
	/*
		@checked: 1 para checar os checkbox e 0 para não checar os checkbox
	*/
	const descontoListarProcedimentos = (checked) => {
		let totalDesconto = 0;
		let cont = 1;
		$('#js-descontos-table-procedimentos').html('');
		procedimentos.forEach(x => {

			if(x.situacao=="aprovado") {
				opcao='';
				if(x.opcao.length>0) opcao=x.opcao+' - ';


				let valorHTML = '';

				let valorProcedimento=x.valor;
				if(x.quantitativo==1) valorProcedimento*=x.quantidade;
				if(x.face==1) valorProcedimento*=x.faces.length;

				if(x.desconto>0) {
					totalDesconto+=x.desconto;
					valorProcedimentoComDesconto=valorProcedimento-x.desconto;
					valorHTML = `<strike>${number_format(valorProcedimento,2,",",".")}</strike><br />${number_format(valorProcedimentoComDesconto,2,",",".")}`
				} else {
					valorHTML = number_format(valorProcedimento,2,",",".");
				}

				let iniciarChecado = checked==1?' checked':'';
				if(x.desconto>0) iniciarChecado=' checked';
				let tr = `<tr class="js-tr-item">			
								<td style="width:25px;">
									<label><input type="checkbox" class="js-desconto-procedimento" data-index="${(cont-1)}"${iniciarChecado} /></label>
								</td>					
								<td>
									<h1>${x.procedimento}</h1>
									<p>${opcao}${x.plano}</p>
								</td>
								<td style="text-align:right;">
									${valorHTML}
								</td>
							</tr>`;


				$('#js-descontos-table-procedimentos').append(tr);
			}

			if(cont==procedimentos.length) {
				descontoAtualizar();
			}
			cont++;
		});
	}

	// atualiza os campos de pagamento (ex.: quando credito exibe bandeira etc..)
	const pagamentosAtualizaCampos = (formaDePagamento,atualizaObjetoPagamento) => {
		let id_formadepagamento  = formaDePagamento.val();
		let obj = formaDePagamento.parent().parent().parent().parent();
		let tipo = $(obj).find('select.js-id_formadepagamento option:checked').attr('data-tipo');

		$(obj).find('.js-identificador,.js-parcelas,.js-creditoBandeira,.js-debitoBandeira,.js-debitoBandeira,.js-valorCreditoDebito,.js-obs,.js-valorCreditoDebitoTaxa').parent().parent().hide();

		if(tipo=="credito") {
			$(obj).find('.js-parcelas,.js-creditoBandeira,.js-valorCreditoDebito,.js-valorCreditoDebitoTaxa,.js-identificador').parent().parent().show();
		} else if(tipo=="debito") {
			$(obj).find('.js-debitoBandeira,.js-valorCreditoDebito,.js-valorCreditoDebitoTaxa,.js-identificador').parent().parent().show();
		} else {
			$(obj).find('.js-identificador').parent().parent().show();

			if(tipo=="permuta") {
				//$(obj).find('.js-obs').parent().parent().show();
			}
		}


		let index = $('.js-pagamentos .js-id_formadepagamento').index(this);

		if(atualizaObjetoPagamento===true) {
			pagamentosPersistirObjeto();	
		}
	}


	const pagamentosPersistirObjeto = () => {
		console.log('salvando...');
		parcelas = [];
		$('.js-pagamento-item').each(function(index,el) {
			let vencimento = $(el).find('.js-vencimento').val();
			let valor = eval(unMoney($(el).find('.js-valor').val()));
			let id_formapagamento = $(el).find('.js-id_formadepagamento').val();
			let identificador = $(el).find('.js-identificador').val();

			let item = { vencimento, 
							valor,
							id_formapagamento,
							identificador };

			// se credito
			if(id_formapagamento==2) {
				let creditoBandeira = $(el).find('.js-creditoBandeira').val();;
				let id_operadora = $(el).find('.js-creditoBandeira option:selected').attr('data-id_operadora');
				let qtdParcelas = $(el).find('.js-parcelas').val();;

				item.creditoBandeira=creditoBandeira;
				item.qtdParcelas=qtdParcelas;
				item.id_operadora=id_operadora;
			}
			// se debito
			else if(id_formapagamento==3) {
				debitoBandeira = $(el).find('.js-debitoBandeira').val();
				let id_operadora = $(el).find('.js-debitoBandeira option:selected').attr('data-id_operadora');

				item.debitoBandeira=debitoBandeira;
				item.id_operadora=id_operadora;
			}


			parcelas.push(item);
		});

		pagamentos=parcelas;
		$('textarea#js-textarea-pagamentos').val(JSON.stringify(pagamentos));
		//pagamentosListar();
	}

	const creditoBandeiraAtualizaParcelas = (selectCreditoBandeira,qtdParcelasSelecionar) => {
		let obj = $(selectCreditoBandeira).parent().parent().parent();
		$(obj).find('select.js-parcelas option').remove();
		
		if($(selectCreditoBandeira).val().length>0) {
			let semJuros = eval($(selectCreditoBandeira).find('option:checked').attr('data-parcelas-semjuros'));
			let parcelas = eval($(selectCreditoBandeira).find('option:checked').attr('data-parcelas'));
		
			if($.isNumeric(parcelas)) {
				$(obj).find('select.js-parcelas').append(`<option value="">-</option>`);
				for(var i=1;i<=parcelas;i++) {
					semjuros='';
					if($.isNumeric(semJuros) && semJuros>=i) semjuros=` - sem juros`;
					else sel ='';

					if(i==qtdParcelasSelecionar) sel=' selected';
					else sel='';

					$(obj).find('select.js-parcelas').append(`<option value="${i}"${sel}>${i}x${semjuros}</option>`);
				}
			} else {
				$(obj).find('select.js-parcelas').append(`<option value="">erro</option>`);
			}
		} else {
			$(obj).find('select.js-parcelas').append(`<option value="">selecione a bandeira</option>`);
		}
	}

	$(function(){

		procedimentos=JSON.parse($('textarea#js-textarea-procedimentos').val());
		procedimentosListar();
		pagamentosListar();

		$('.js-pagamentos').on('change','.js-vencimento:eq(0)',function(){
			atualizaValor(true);
		});

		// remove descontos
		$('.aside-plano-desconto .js-btn-removerDesconto').click(function(){

			let cont = 0;
			procedimentos.forEach(x=>{
				procedimentos[cont].desconto=0;
				procedimentos[cont].valorCorrigido=procedimentos[cont].valor;
				cont++;
			});
			descontoListarProcedimentos(0);
			procedimentosListar();
			atualizaValor(true);
		});

		// clica no botao de aplicar desconto na janela de desconto
		$('.aside-plano-desconto .js-btn-aplicarDesconto').click(function(){
			let tipoDesconto = $('.aside-plano-desconto .js-select-tipoDesconto').val();
			let quantidadeDesconto = $('.aside-plano-desconto .js-desconto-procedimento:checked').length;
			let desconto = unMoney($(`.aside-plano-desconto .js-input-desconto`).val());
			//console.log('Descontando '+desconto);
			if(quantidadeDesconto==0) {
				swal({title: "Erro", text: 'Selecione pelo menos um procedimento para aplicar desconto!', html:true, type:"error", confirmButtonColor: "#424242"});
					
			} else if(desconto==0 || desconto===undefined || desconto==='' || !desconto) {
				swal({title: "Erro", text: 'Defina o desconto que deverá ser aplicado!', html:true, type:"error", confirmButtonColor: "#424242"});
			} else {
				let valorTotal = 0;
				let cont = 0;
				let qtdItensDesconto = 0;

				procedimentos.forEach(x=>{
					//console.log(cont+' '+x.situacao);
					if(x.situacao!="naoAprovado" && x.situacao!="observado") {
						if($(`.aside-plano-desconto .js-desconto-procedimento:eq(${cont})`).prop('checked')===true) {
							valorTotal+=eval(x.valor);
							//console.log(cont+' '+x.situacao+'->'+x.valorCorrigido);
							qtdItensDesconto++;
						}
						cont++;
					}
				});

				if(tipoDesconto!="dinheiro") {
					desconto = unMoney($(`.aside-plano-desconto .js-input-desconto`).val().replace('.',','));
					desconto = (valorTotal*(desconto/100)).toFixed(2);
				}

				// calcula percentual do desconto em cima do valor total
				let descontoParcentual = ((desconto/valorTotal)*100).toFixed(4);


				//alert(descontoParcentual+' '+desconto);

				//console.log('Valor Total:'+valorTotal+' Desconto: '+desconto+' Perc:'+descontoParcentual);;
				
				if(desconto==0 || desconto===undefined || desconto==='' || !desconto) {
					swal({title: "Erro", text: 'Defina o desconto que deverá ser aplicado!', html:true, type:"error", confirmButtonColor: "#424242"});
					$(`.aside-plano-desconto .js-input-desconto`).addClass('erro');
				} else {
					let cont = 0;
					let contProcedimento = 0;
					procedimentos.forEach(x=>{
						//console.log(x);
						if(x.situacao=="aprovado") {

							if($(`.aside-plano-desconto .js-desconto-procedimento:eq(${cont})`).prop('checked')===true) {
								let desc = 0;

								if(x.desconto>0) {
									valorProc=procedimentos[contProcedimento].valor;
								} else {
									valorProc=procedimentos[contProcedimento].valor;
									//if(eval(x.quantitativo)==1) valorProc*=eval(x.quantidade);
								}

								equivalente = (valorProc/valorTotal).toFixed(8);
								descontoAplicar = desconto*equivalente;

								//console.log(valorProc+' eq '+equivalente+' = '+descontoAplicar);

								//descontoAplicar = desconto/qtdItensDesconto;


								if(procedimentos[contProcedimento].desconto && $.isNumeric(procedimentos[contProcedimento].desconto)) {
									desc=procedimentos[contProcedimento].desconto+descontoAplicar;
								} else {
									desc=descontoAplicar;
								}

								//console.log('desc '+desc);
								valorProc=procedimentos[contProcedimento].valor;
								if(eval(x.quantitativo)==1) {
									//valorProc*=eval(x.quantidade);
									procedimentos[contProcedimento].valorCorrigido=valorProc-desc;
									procedimentos[contProcedimento].desconto=desc;///eval(x.quantidade);
								} else {

									procedimentos[contProcedimento].valorCorrigido=valorProc-desc;
									procedimentos[contProcedimento].desconto=desc
								}

								//console.log('desconto em '+cont+'\n'+procedimentos[contProcedimento].valor+' - '+desc)
							}
							cont++;
						}
						contProcedimento++;
					});

					procedimentosListar();
					descontoListarProcedimentos(0);
					atualizaValor(true);
					$('.js-input-desconto').val('');

				}
			}
		})

		// abre janela de desconto
		$('.js-btn-desconto').click(function(){
			$(".aside-plano-desconto").fadeIn(100,function() {
				$(".aside-plano-desconto .aside__inner1").addClass("active");
			});

			descontoListarProcedimentos(1);

		});

		// ao selecionar procedimento em desconto
		$('#js-descontos-table-procedimentos').on('click','.js-desconto-procedimento',descontoAtualizar);
		$('.aside-plano-desconto .js-input-desconto').change(descontoAtualizar);

		$('.aside-plano-desconto .js-select-tipoDesconto').change(function(){
			$('.aside-plano-desconto .js-input-desconto').maskMoney('destroy');
			$('.aside-plano-desconto .js-input-desconto').val('');
			if($(this).val()=="dinheiro") {
				$('.aside-plano-desconto .js-input-desconto').maskMoney({symbol:'', allowZero:true, showSymbol:true, thousands:'.', decimal:',', symbolStay: true}).attr('maxlength',10);
				
			} else {
				$('.aside-plano-desconto .js-input-desconto').maskMoney({symbol:'', precision:1, suffix:'%', allowZero:true, showSymbol:true, thousands:'', decimal:'.', symbolStay: true}).attr('maxlength',6);
			}
			$('.aside-plano-desconto .js-input-desconto').trigger('keyup');
		}).trigger('change');

		$('.js-pagamentos').on('change','.js-debitoBandeira,.js-creditoBandeira,.js-parcelas,.js-valor',function(){
						
			//	creditoDebitoValorParcela($(this));
			let obj = $(this);
			setTimeout(function(){$(obj).parent().parent().parent().parent().find('.js-valor').trigger('keyup');},200);	
		});

		$('.js-pagamentos').on('change','.js-creditoBandeira',function(){
			creditoBandeiraAtualizaParcelas($(this),0);
			pagamentosPersistirObjeto();
		});

		$('.js-pagamentos').on('keyup','.js-identificador',pagamentosPersistirObjeto);
		$('.js-pagamentos').on('change','.js-debitoBandeira,.js-creditoBandeira,.js-parcelas',pagamentosPersistirObjeto);

		$('.js-pagamentos').on('blur','.js-valor',function(){
			pagamentosListar();
		});

		$('.js-pagamentos').on('keyup','.js-identificador',function(){

			let obj = $(this).parent().parent().parent().parent();

			setTimeout(function(){$(obj).find('.js-valor').trigger('keyup');},200);
		});

		$('.js-pagamentos').on('keyup','.js-valor',function(){
			let index = $(this).index('.js-pagamentos .js-valor');
			let numeroParcelas = eval($('.js-pagamentos-quantidade').val());
			let valorTotalAux = valorTotal;
			let valorAcumulado = 0;
			let parcelas = [];
			let val = unMoney($(this).val());

			for(i=0;i<=index;i++) {
				val = unMoney($(`.js-pagamentos .js-valor:eq(${i})`).val());

				id_formapagamento = $(`.js-pagamentos .js-id_formadepagamento:eq(${i})`).val();
				identificador = $(`.js-pagamentos .js-identificador:eq(${i})`).val();
				creditoBandeira = $(`.js-pagamentos .js-creditoBandeira:eq(${i})`).val();
				operadora=0;
				if(id_formapagamento==2) {
					operadora = $(`.js-pagamentos .js-creditoBandeira:eq(${i}) option:selected`).attr('data-id_operadora');
				} else if(id_formapagamento==3) {
					operadora = $(`.js-pagamentos .js-debitoBandeira:eq(${i}) option:selected`).attr('data-id_operadora');
				}
				
				debitoBandeira = $(`.js-pagamentos .js-debitoBandeira:eq(${i})`).val();
				qtdParcelas = $(`.js-pagamentos .js-parcelas:eq(${i})`).val();
				valorAcumulado += val;
				//console.log(`${i} => ${val} = ${valorAcumulado}`);

				let item = {};


				item.vencimento=pagamentos[i].vencimento;
				item.valor=val;
				/*item.id_formapagamento=id_formapagamento;
				item.identificador=identificador;
				item.creditoBandeira=creditoBandeira;
				item.operadora=operadora;
				item.debitoBandeira=debitoBandeira;
				item.qtdParcelas=qtdParcelas;*/

				parcelas.push(item);
			}

			let valorRestante = valorTotal-valorAcumulado;
			let continua = true;
			if(valorAcumulado>valorTotal) {

				let dif = valorAcumulado - valorTotal;
				dif=dif.toFixed(2);

				if(dif>0.1) {
					continua=false;
					swal({title: "Erro!", text: 'Os valores das parcelas não podem superar o valor total', html:true, type:"error", confirmButtonColor: "#424242"});
				}
			}  


			if(continua) {


				numeroParcelasRestantes = numeroParcelas - (index+1);
				valorParcela=valorRestante/numeroParcelasRestantes;
				valorParcela=valorParcela.toFixed(2);
				let valorInputado=0;
				for(i=(index+1);i<numeroParcelas;i++) {

					if(pagamentos[i]) {
						let item = {};
						item.vencimento=pagamentos[i].vencimento;
						item.valor=valorParcela;

						parcelas.push(item);
					}

				}

				// se alterou a ultima parcela
				if(numeroParcelas==(index+1)) {

					// verifica todos os valores inputados batem com o valor total
					if(valorAcumulado<valorTotal) {
						dif = valorTotal-valorAcumulado;
						parcelas[index].valor+=dif;
					} else if(valorAcumulado>valorTotal) {
						dif = valorTotal-valorAcumulado;
						parcelas[index].valor=dif;
					}

					//alert('alterou o ulitmo '+valorTotal+' = '+valorAcumulado)
				}


				pagamentos=parcelas;


				$('textarea.js-textarea-pagamentos').val(JSON.stringify(pagamentos))
			}
		});

		$('.js-pagamentos').on('change','.js-id_formadepagamento',function(){
			pagamentosAtualizaCampos($(this),true);
		});

		// altera quantidade de parcelas
		$('.js-pagamentos-quantidade').change(function(){

			let qtd = $(this).val();

			if(!$.isNumeric(eval(qtd))) qtd=1;
			else if(qtd<1) qtd=2;
			else if(qtd>=36) qtd=36;

			$('.js-pagamentos-quantidade').val(qtd);

			atualizaValor(true);
		});

		// seleciona o tipo de pagamento
		$('input[name=pagamento]').change(function(){
			atualizaValor(true);
		})

		// remove procedimento
		$('.aside-plano-procedimento-editar .js-removerProcedimento').click(function(){
			swal({
					title: "Atenção",
					text: 'Tem certeza que deseja excluir este procedimento?',
					type: "warning",
					showCancelButton: true,
					confirmButtonColor: "#DD6B55",   
					confirmButtonText: "Sim!",   
					cancelButtonText: "Não",
					closeOnConfirm: false,  
					closeOnCancel: false 
				}, function(isConfirm){   
						if (isConfirm) {    
							let index = $('.aside-plano-procedimento-editar .js-asidePlanoEditar-index').val();
							procedimentos.splice(index,1);
							procedimentosListar();	
							swal.close();
							$('.aside-plano-procedimento-editar .aside-close').click();
						} else {  
							swal.close();   
						} 
				});
		});

		// edita procedimento
		$('.aside-plano-procedimento-editar .js-salvarEditarProcedimento').click(function(){


			// capta dados
			let index = $('.aside-plano-procedimento-editar .js-asidePlanoEditar-index').val();
			let situacao = $('.aside-plano-procedimento-editar .js-asidePlanoEditar-situacao').val();
			let obs = $('.aside-plano-procedimento-editar .js-asidePlanoEditar-obs').val();

			if(procedimentos[index].quantitativo==1) {
				let quantidade = $('.aside-plano-procedimento-editar .js-asidePlanoEditar-quantidade').val();
				procedimentos[index].quantidade=quantidade;
			}


			procedimentos[index].situacao=situacao;
			procedimentos[index].obs=obs;

			procedimentosListar();

			$('.aside-plano-procedimento-editar .aside-close').click();
		});

		// clica em um procedimento para editar
		$('#js-table-procedimentos').on('click','.js-tr-item',function(){
			let index = $('#js-table-procedimentos .js-tr-item').index(this);
			procedimentoEditar(index);
		});

		// adiciona procedimento
		$('.aside-plano-procedimento-adicionar .js-salvarAdicionarProcedimento').click(function(){
			
			// capta dados 
				let id_procedimento = $('.aside-plano-procedimento-adicionar .js-asidePlano-id_procedimento option:selected').val();
				let procedimento = $(`.aside-plano-procedimento-adicionar .js-asidePlano-id_procedimento option:selected`).text();
				let id_regiao = $('.aside-plano-procedimento-adicionar .js-asidePlano-id_procedimento option:selected').attr('data-id_regiao');
				let quantitativo = $('.aside-plano-procedimento-adicionar .js-asidePlano-id_procedimento option:selected').attr('data-quantitativo');
				let face = $('.aside-plano-procedimento-adicionar .js-asidePlano-id_procedimento option:selected').attr('data-face');
				let id_plano = $(`.aside-plano-procedimento-adicionar .js-asidePlano-id_plano option:selected`).val();
				let plano = $(`.aside-plano-procedimento-adicionar .js-asidePlano-id_plano option:selected`).text();
				let valor = $(`.aside-plano-procedimento-adicionar .js-asidePlano-id_plano option:selected`).attr('data-valor');
				let quantidade = $(`.aside-plano-procedimento-adicionar .js-asidePlano-quantidade`).val();
				let obs = $('.aside-plano-procedimento-adicionar .js-asidePlano-obs').val();
				let valorCorrigido=valor;
				//if(quantitativo==1) valorCorrigido=quantidade*valor;


			
			// valida
				let erro='';
				if(id_procedimento.length==0) erro='Selecione o Procedimento para adicionar';
				else if(id_plano.length==0) erro='Selecione o Plano';
				else if(id_regiao>2 && $(`.js-regiao-${id_regiao}-select`).val().length==0) erro='Preencha a Região';
				else if(quantitativo==1 && quantidade<=0) erro=`A quantidade não pode ser valor negativo!`; 
				else if(face==1) {
					$(`.js-regiao-${id_regiao}-select option:selected`).each(function(index,el){
						let idO=$(el).val();
						if(erro.length==0 && $(`.aside-plano-procedimento-adicionar select.js-face-${idO}-select option:selected`).length==0) {
							erro=`Selecione as Faces do Dente ${$(el).text()}`;
						}
					});
				}

			if(erro.length>0) {
				swal({title: "Erro!", text: erro, type:"error", confirmButtonColor: "#424242"});
			} else {

				let linhas=1;
				if(id_regiao>=2) linhas = eval($(`.js-regiao-${id_regiao}-select option:selected`).length);
				
				let item= {};
				let opcoes = ``;

				for(var i=0;i<linhas;i++) {

					item = {};
					item.obs = obs;
					item.id_procedimento=id_procedimento;
					item.procedimento=procedimento;
					item.id_regiao=id_regiao;
					item.id_plano=id_plano; 
					item.face=face;
					item.plano=plano;
					item.profissional=0;
					item.quantidade=quantidade;
					item.situacao='aprovado';
					item.valor=valor;
					item.quantitativo=quantitativo;
					item.desconto=0;

				
					// Data e Usuario
						let dt = new Date();
						let dia = dt.getDate();
						let mes = dt.getMonth();
						let min = dt.getMinutes();
						let hrs = dt.getHours();
						mes++
						mes=mes<=9?`0${mes}`:mes;
						dia=dia<=9?`0${dia}`:dia;
						min=min<=9?`0${min}`:min;
						hrs=hrs<=9?`0${hrs}`:hrs;

						let data = `${dia}/${mes}/${dt.getFullYear()} ${hrs}:${min}`;
						item.data=data;
						item.id_usuario=id_usuario;
						item.autor=autor;

					// Opcoes
						opcao = id_opcao = ``;
						if(id_regiao>=2) {
							id_opcao = $(`.js-regiao-${id_regiao}-select option:selected:eq(${i})`).val();
							opcao = $(`.js-regiao-${id_regiao}-select option:selected:eq(${i})`).text();
						}
						item.opcao=opcao;
						item.id_opcao=id_opcao;

					// Faces
						faces=[];
						if(face==1) {

							$(`.aside-plano-procedimento-adicionar select.js-regiao-${id_regiao}-select option:selected:eq(${i})`).each(function(index,el){
								let id_opcao = $(el).val();
								let faceItem = {};
								facesItens = $(`.aside-plano-procedimento-adicionar select.js-face-${id_opcao}-select`).val();
								
								faces=facesItens;

							});

							valorCorrigido=faces.length*valor;

						}
						item.faces=faces;

					item.valorCorrigido=valorCorrigido;

					procedimentos.push(item);

					if((i+1)==linhas) {
						$(`.aside-plano-procedimento-adicionar .js-asidePlano-quantidade`).val(1).parent().parent().hide();;  
						procedimentosListar();
						atualizaValor(true);
					}
				}
			}
		});

		// quando seleciona o procedimento, exibe as regioes parametrizadas
		$('.aside-plano-procedimento-adicionar select.js-asidePlano-id_procedimento').change(function(){

			let id_procedimento = $(this).val();

			if(id_procedimento.length>0) {
				let id_regiao = $(this).find('option:selected').attr('data-id_regiao');
				let regiao = $(this).find('option:selected').attr('data-regiao');
				let quantitativo = $(this).find('option:selected').attr('data-quantitativo');
				let face = $(this).find('option:selected').attr('data-face');


				if(quantitativo==1) {
					$(`.js-asidePlano-quantidade`).parent().parent().show();
					$(`.js-asidePlano-quantidade`).val(1);
				} else {
					$(`.js-asidePlano-quantidade`).parent().parent().hide();
				}

				$(`.js-regiao-${id_regiao}-select`).find('option:selected').prop('selected',false).trigger('change').trigger('chosen:updated');

				$(`.js-regiao`).hide();
				$(`.js-regiao-${id_regiao}`).show();
				$(`.js-regiao-${id_regiao}`).find('select').chosen({hide_results_on_select:false,allow_single_deselect:true});

				
				let data = `ajax=planos&id_procedimento=${id_procedimento}`;

				$('.aside-plano-procedimento-adicionar .js-asidePlano-id_plano option').remove();
				$('.aside-plano-procedimento-adicionar .js-asidePlano-id_plano').append(`<option value="">Carregando...</option>`);
				
				$.ajax({
					type:"POST",
					url:baseURLApiAsidePlanoDeTratamento,
					data:data,
					success:function(rtn) {
						if(rtn.success) { 
							$('.aside-plano-procedimento-adicionar .js-asidePlano-id_plano option').remove();
							$('.aside-plano-procedimento-adicionar .js-asidePlano-id_plano').append(`<option value="">-</option>`);
						
							if(rtn.planos) {
								let cont = 1;
								rtn.planos.forEach(x=> {
									$('.aside-plano-procedimento-adicionar .js-asidePlano-id_plano').append(`<option value="${x.id}" data-valor="${x.valor}">${x.titulo}</option>`);

									if(cont==rtn.planos.length) {
										$('.aside-plano-procedimento-adicionar .js-asidePlano-id_plano option:eq(1)').prop('selected',true);
									} 
									cont++;
								});
							}
						}
					},
				})
			} else {
				$(`.js-regiao`).hide();
				$(`.js-procedimento-btnOk`).hide();
			}
		});

		// quando seleciona a regiao 4 (dentes), monta as faces
		$('.aside-plano-procedimento-adicionar select.js-regiao-4-select').change(function(){
			let face = $('.aside-plano-procedimento-adicionar select.js-asidePlano-id_procedimento option:selected').attr('data-face');

			if(face==1) {

				$('.aside-plano-procedimento-adicionar .js-fieldset-faces').show();
				$('.js-faces').hide();

				let cont = 0;
				let selectRegiao4 = $(this);

				selectRegiao4.find('option:selected').each(function(index,el) {
					let id_regiao = $(el).val();
					let regiao = $(el).text();


					if($('.aside-plano-procedimento-adicionar .js-fieldset-faces .js-face-'+id_regiao).length>0) {
						$('.aside-plano-procedimento-adicionar .js-fieldset-faces .js-face-'+id_regiao).show();
					} else {
						$('.aside-plano-procedimento-adicionar .js-fieldset-faces').append(`<dl class="js-faces js-face-${id_regiao}">
																								<dt>${regiao}</dt>
																								<dd>
																									<select class="js-select-faces js-face-${id_regiao}-select" multiple>
																										<option value=""></option>
																										<?php echo $_regioesFacesOptions;?>
																									</select>
																								</dd>
																							</dl>`);
					}

					cont++;

					if(selectRegiao4.find('option:selected').length==cont) {
						$('.aside-plano-procedimento-adicionar .js-fieldset-faces .js-faces:hidden').remove();
						$('.aside-plano-procedimento-adicionar .js-select-faces').chosen('destroy');
						$('.aside-plano-procedimento-adicionar .js-select-faces').chosen({hide_results_on_select:false,allow_single_deselect:true});
					}

				})
			} else {

				$('.aside-plano-procedimento-adicionar .js-fieldset-faces').hide();
			}
		});


		desativarCampos();

	})
</script>


<section class="aside aside-plano-procedimento-editar" style="display: none;">
	<div class="aside__inner1">
		<header class="aside-header">
			<h1>Procedimento</h1>
			<a href="javascript:;" class="aside-header__fechar aside-close"><i class="iconify" data-icon="fluent:dismiss-24-filled"></i></a>
		</header>

		<form method="post" class="aside-content form js-form-editar-procedimento">
			<input type="hidden" class="js-asidePlanoEditar-index" value="" />
			<section class="filter">
				<div class="filter-group"></div>
				<div class="filter-group">
					<div class="filter-form form">

						<dl>
							<dd><a href="javascript:;" class="button js-removerProcedimento"><i class="iconify" data-icon="fluent:delete-24-regular"></i></a></dd>
						</dl>

						<dl>
							<dd><button type="button" class="button button_main js-salvarEditarProcedimento" data-loading="0"><i class="iconify" data-icon="fluent:checkmark-12-filled"></i><span> Salvar</button></dd>
						</dl>
					</div>								
				</div>
			</section>

			<fieldset>
				<legend>Procedimento</legend>

				<dl>
					<dt>Procedimento</dt>
					<dd><input type="text" class="js-asidePlanoEditar-procedimento" disabled style="background:#ccc" /></dd>
				</dl>

				<dl>
					<dt>Plano</dt>
					<dd><input type="text" class="js-asidePlanoEditar-plano" disabled style="background:#ccc" /></dd>
				</dl>	

				<dl>
					<dt>Região</dt>
					<dd><input type="text" class="js-asidePlanoEditar-regiao" disabled style="background:#ccc" /></dd>
				</dl>

				<div class="colunas4">
					<dl>
						<dt>Quantidade</dt>
						<dd><input type="number" class="js-asidePlanoEditar-quantidade" min=0 oninput="validity.valid||(value='');" /></dd>
					</dl>
				</div>

			</fieldset>

			<fieldset>
				<legend>Informações</legend>

					<dl>
						<dt>Status</dt>
						<dd>
							<select class="js-asidePlanoEditar-situacao">
								<?php echo $selectSituacaoOptions;?>
							</select>
						</dd>
					</dl>
					

					<div class="colunas2">
						<dl>
							<dt>Valor Tabela</dt>
							<dd><input type="text" class="js-asidePlanoEditar-valorTabela" disabled style="background:#ccc" /></dd>
						</dl>
						<dl>
							<dt>Valor Desconto</dt>
							<dd><input type="text" class="js-asidePlanoEditar-valorDesconto" disabled style="background:#ccc" /></dd>
						</dl>
					</div>
				
					<div class="colunas2">
						<dl>
							<dt>Valor Corrigido</dt>
							<dd><input type="text" class="js-asidePlanoEditar-valorCorrigido" disabled style="background:#ccc" /></dd>
						</dl>
						<dl>
							<dt>Valor Unitário</dt>
							<dd><input type="text" class="js-asidePlanoEditar-valorUnitario" disabled style="background:#ccc" /></dd>
						</dl>
					</div>

					<dl>
						<dt>Observações</dt>
						<dd>
							<textarea class="js-asidePlanoEditar-obs" style="height:100px;"></textarea>
						</dd>
					</dl>
				
					<div class="colunas2">
						<dl>
							<dt>Adicionado por</dt>
							<dd class="js-asidePlanoEditar-usuario"></dd>
						</dl>
						<dl>
							<dt>Data</dt>
							<dd class="js-asidePlanoEditar-data"></dd>
						</dl>
					</div>
					
			</fieldset>

		</form>
	</div>
</section>

<section class="aside aside-plano-procedimento-adicionar" style="display: none;">
	<div class="aside__inner1">
		<header class="aside-header">
			<h1>Adicionar Procedimento</h1>
			<a href="javascript:;" class="aside-header__fechar aside-close"><i class="iconify" data-icon="fluent:dismiss-24-filled"></i></a>
		</header>

		<form method="post" class="aside-content form js-form-adicionar-procedimento">
			<section class="filter">
				<div class="filter-group"></div>
				<div class="filter-group">
					<div class="filter-form form">
						<dl>
							<dd><button type="button" class="button button_main js-salvarAdicionarProcedimento" data-loading="0"><i class="iconify" data-icon="fluent:add-circle-24-regular"></i> <span>Adicionar</span></button></dd>
						</dl>
					</div>								
				</div>
			</section>

			<fieldset class="js-fieldset-adicionar">
				<legend>Adicionar</legend>
					<dl>
						<dt>Procedimento</dt>
						<dd>
							<select class="js-asidePlano-id_procedimento" data-placeholder="Selecione o procedimento">
								<option value=""></option>
								<?php
								foreach($_procedimentos as $p) {
									echo '<option value="'.$p->id.'" data-id_regiao="'.$p->id_regiao.'" data-regiao="'.(isset($_regioes[$p->id_regiao])?utf8_encode($_regioes[$p->id_regiao]->titulo):"-").'" data-quantitativo="'.($p->quantitativo==1?1:0).'" data-face="'.$p->face.'">'.utf8_encode($p->titulo).'</option>';
								}
								?>
							</select>
						</dd>
					</dl>
					<dl>
						<dt>Plano</dt>
						<dd>
							<select class="js-asidePlano-id_plano">
								<option value="">-</option>
							</select>
						</dd>
					</dl>
					
					<dl style="display: none">
						<dt>Quantidade</dt>
						<dd><input type="number" class="js-asidePlano-quantidade" value="1" min=0 oninput="validity.valid||(value='');" /></dd>
					</dl>

					<dl class="js-regiao-2 js-regiao" style="display: none;">
						<dt>Arcada(s)</dt>
						<dd>
							<select class="js-regiao-2-select" multiple>
								<?php
								if(isset($_regioesOpcoes[2])) {
									foreach($_regioesOpcoes[2] as $o) {
										echo '<option value="'.$o->id.'" data-titulo="'.utf8_encode($o->titulo).'">'.utf8_encode($o->titulo).'</option>';
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
								<?php
								if(isset($_regioesOpcoes[3])) {
									foreach($_regioesOpcoes[3] as $o) {
										echo '<option value="'.$o->id.'" data-titulo="'.utf8_encode($o->titulo).'">'.utf8_encode($o->titulo).'</option>';
									}
								}
								?>
							</select>
						</dd>
					</dl>

					<dl class="js-regiao-4 js-regiao" style="display: none">
						<dt>Dente(s)</dt>
						<dd>
							<select class="js-regiao-4-select" multiple>
								<?php
								if(isset($_regioesOpcoes[4])) {
									foreach($_regioesOpcoes[4] as $o) {
										echo '<option value="'.$o->id.'" data-titulo="'.utf8_encode($o->titulo).'">'.utf8_encode($o->titulo).'</option>';
									}
								}
								?>
							</select>
						</dd>
					</dl>


					<dl>
						<dt>Observações</dt>
						<dd>
							<textarea class="js-asidePlano-obs" style="height:100px;"></textarea>
						</dd>
					</dl>
					
			</fieldset>

			<fieldset class="js-fieldset-faces" style="display:none">
				<legend>Faces</legend>


			</fieldset>
		</form>
	</div>
</section>

<section class="aside aside-plano-desconto" style="display: none;">
	<div class="aside__inner1">
		<header class="aside-header">
			<h1>Aplicar Desconto</h1>
			<a href="javascript:;" class="aside-header__fechar aside-close"><i class="iconify" data-icon="fluent:dismiss-24-filled"></i></a>
		</header>

		<form method="post" class="aside-content form js-form-adicionar-procedimento">
			<?php /*<section class="filter">
				<div class="filter-group"></div>
				<div class="filter-group">
					<div class="filter-form form">
						<dl>
							<dd><button type="button" class="button button_main js-salvarAdicionarProcedimento" data-loading="0"><i class="iconify" data-icon="fluent:add-circle-24-regular"></i> <span>Adicionar</span></button></dd>
						</dl>
					</div>								
				</div>
			</section>*/?>

			<fieldset class="js-descontos-fieldset-procedimentos">
				<legend>Procedimentos</legend>

				<div class="list1">
					<table id="js-descontos-table-procedimentos">
						
					</table>
				</div>
			</fieldset>

			<fieldset>
				<legend>Desconto</legend>

				<div class="colunas4">
					<dl>
						<dt>Desconto em</dt>
						<dd>
							<select class="js-select-tipoDesconto">
								<option value="dinheiro">Dinheiro</option>
								<option value="porcentual">Porcentagem</option>
							</select>
						</dd>
					</dl>

					<dl>
						<dt>&nbsp;</dt>
						<dd>
							<input type="text" class="js-input-desconto" />
						</dd>
					</dl>

					<dl class="dl2">
						<dt>&nbsp;</dt>
						<dd>
							<a href="javascript:;" class="button button_main js-btn-aplicarDesconto">Aplicar Desconto</a>
							<a href="javascript:;" class="button js-btn-removerDesconto">Remover Descontos</a>
						</dd>
					</dl>
				</div>

				<div class="colunas4">
					<dl>
						<dt>Total dos Procedimentos</dt>
						<dd><input type="text" class="js-total-procedimentos" disabled /></dd>
					</dl>
					<dl style="display:none">
						<dt>Desconto a ser aplicado</dt>
						<dd><input type="text" class="js-total-descontos" disabled /></dd>
					</dl>
					<dl>
						<dt>Descontos aplicados</dt>
						<dd><input type="text" class="js-total-descontosAplicados" disabled /></dd>
					</dl>
					<dl>
						<dt>Total com descontos</dt>
						<dd><input type="text" class="js-total-procedimentosdescontos" disabled /></dd>
					</dl>
				</div>
			</fieldset>
		</form>
	</div>
</section>