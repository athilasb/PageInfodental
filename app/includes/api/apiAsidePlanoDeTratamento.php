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

	const procedimentosListar = () => {

		$('#js-table-procedimentos').html('');
		procedimentos.forEach(x=>{

			let valor = '';

			if(x.desconto) {
				valor = `<strike>${number_format((eval(x.quantitativo)==1?x.quantidade*x.valor:x.valor),2,",",".")}</strike><br />${number_format(x.valorCorrigido,2,",",".")}`;
			} else {
				valor = number_format(x.valorCorrigido?x.valorCorrigido:x.valor,2,",",".");
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

			let tr = `<tr class="js-tr-item">								
						<td>
							<h1>${x.procedimento}</h1>
							<p>${x.plano}</p>
						</td>
						<td><div class="list1__icon">${opcao}</td>
						<td style="text-align:right;">${valor}</td>
					</tr>`;


			$('#js-table-procedimentos').append(tr);

		});

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
 
			$('.aside-plano-procedimento-editar .js-asidePlanoEditar-valorTabela').val(number_format(valorTabela,2,",","."));
			$('.aside-plano-procedimento-editar .js-asidePlanoEditar-valorDesconto').val(number_format(pEd.desconto,2,",","."));
			$('.aside-plano-procedimento-editar .js-asidePlanoEditar-valorCorrigido').val(number_format(pEd.valorCorrigido,2,",","."));
			$('.aside-plano-procedimento-editar .js-asidePlanoEditar-valorUnitario').val(number_format(pEd.valor,2,",","."));
			$('.aside-plano-procedimento-editar .js-asidePlanoEditar-obs').val(pEd.obs);
			$('.aside-plano-procedimento-editar .js-asidePlanoEditar-procedimento').val(pEd.procedimento);
			$('.aside-plano-procedimento-editar .js-asidePlanoEditar-plano').val(pEd.plano);
			$('.aside-plano-procedimento-editar .js-asidePlanoEditar-regiao').val(regiao);
			$('.aside-plano-procedimento-editar .js-asidePlanoEditar-quantidade').val(pEd.quantidade);
			$('.aside-plano-procedimento-editar .js-asidePlanoEditar-data').html(pEd.data);
			$('.aside-plano-procedimento-editar .js-asidePlanoEditar-usuario').html(pEd.usuario);

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

	$(function(){

		// quando edita um procedimento
		$('#js-table-procedimentos').on('click','.js-tr-item',function(){
			let index = $('#js-table-procedimentos .js-tr-item').index(this);
			procedimentoEditar(index);
		})

		// quando clica no botão adicionar procedimento
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
				if(quantitativo==1) valorCorrigido=quantidade*valor;
			
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
		})

	})
</script>


<section class="aside aside-plano-procedimento-editar" style="display: none;">
	<div class="aside__inner1">
		<header class="aside-header">
			<h1>Procedimento</h1>
			<a href="javascript:;" class="aside-header__fechar aside-close"><i class="iconify" data-icon="fluent:dismiss-24-filled"></i></a>
		</header>

		<form method="post" class="aside-content form js-form-editar-procedimento">
			<section class="filter">
				<div class="filter-group"></div>
				<div class="filter-group">
					<div class="filter-form form">

						<dl>
							<dd><a href="javascript:;" class="button"><i class="iconify" data-icon="fluent:delete-24-regular"></i></a></dd>
						</dl>

						<dl>
							<dd><button type="button" class="button button_main js-salvarAdicionarProcedimento" data-loading="0"><i class="iconify" data-icon="fluent:checkmark-12-filled"></i><span> Salvar</button></dd>
						</dl>
					</div>								
				</div>
			</section>

			<fieldset>
				<legend>Procedimento</legend>

				<dl>
					<dt>Procedimento</dt>
					<dd><input type="text" class="js-asidePlanoEditar-procedimento" /></dd>
				</dl>

				<dl>
					<dt>Plano</dt>
					<dd><input type="text" class="js-asidePlanoEditar-plano" /></dd>
				</dl>	

				<dl>
					<dt>Região</dt>
					<dd><input type="text" class="js-asidePlanoEditar-regiao" /></dd>
				</dl>

				<div class="colunas4">
					<dl>
						<dt>Quantidade</dt>
						<dd><input type="text" class="js-asidePlanoEditar-quantidade" /></dd>
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
							<dd class="js-asidePlanoEditar-usuario">Luciano Dexheimer Morais</dd>
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
						<dd><input type="number" class="js-asidePlano-quantidade" value="1" /></dd>
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