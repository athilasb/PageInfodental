<script type="text/javascript">
	var evolucaoPagina = '<?php echo basename($_SERVER['PHP_SELF'])=="pg_contatos_pacientes_evolucao.php"?"listagem":"formulario";?>';
	var persistirAoEditar = '<?php echo (basename($_SERVER['PHP_SELF'])=="pg_contatos_pacientes_evolucao.php" or is_object($evolucao))?1:0;?>';
	var index = -1;

	var popViewInfos = [];
	const popView = (obj) => {


		index=$(obj).index();

		historicoListar(index);


		if(procedimentos[index].id_tratamento_procedimento===undefined) {
			$('#cal-popup .abasPopover').find('a:eq(1),a:eq(2)').hide();
			$('#cal-popup .abasPopover').find('a:eq(0)').click();
			$('#cal-popup .js-situacao').hide();
		} else {
			$('#cal-popup .abasPopover').find('a').show();
			$('#cal-popup .js-situacao').show();
		}


		$('#cal-popup')
				.removeClass('cal-popup_left')
				.removeClass('cal-popup_right')
				.removeClass('cal-popup_bottom')
				.removeClass('cal-popup_top');

		let clickTop=obj.getBoundingClientRect().top+window.scrollY;
	
		let clickLeft=Math.round(obj.getBoundingClientRect().left);
		let clickMargin=Math.round(obj.getBoundingClientRect().width/2);
		$(obj).prev('.cal-popup')
				.removeClass('cal-popup_left')
				.removeClass('cal-popup_right')
				.removeClass('cal-popup_bottom')
				.removeClass('cal-popup_top');

		let popClass='cal-popup_top';
		$('#cal-popup').addClass(popClass).toggle();
		$('#cal-popup').css({'top':clickTop,'left':clickLeft,'margin-left': clickMargin});
		$('#cal-popup').show();
		//console.log(procedimentos[index]);
		/*if(popViewInfos[index].opcao.length>0) {
			$('#cal-popup .js-opcaoEQtd').html(`Região: ${popViewInfos[index].opcao}`);
		} else {
			$('#cal-popup .js-opcaoEQtd').html(`Quantidade: ${popViewInfos[index].quantidade}`);
		}*/

		$('#cal-popup .js-titulo').html(procedimentos[index].titulo);
		$('#cal-popup .js-plano').html(procedimentos[index].plano);
		$('#cal-popup .js-opcao').html(procedimentos[index].opcao);
		$('#cal-popup .js-autor').html(procedimentos[index].autor);
		$('#cal-popup .js-autor-data').html(procedimentos[index].data);
		$('#cal-popup .js-profissional').val(procedimentos[index].id_profissional);
	
		if(procedimentos[index].statusEvolucao) {
			$('#cal-popup .js-situacao').css('display', 'block');
			$('#cal-popup .js-situacao option').remove();
			$('#cal-popup .js-situacao').append('<?php echo $selectSituacaoOptions;?>');
			setTimeout(function(){
				$('#cal-popup .js-situacao').val(procedimentos[index].statusEvolucao);
			},100);
		}
		
		$('#cal-popup .js-index').val(index);

		if(evolucaoPagina==="listagem") {
			//$('#cal-popup').find('select').prop('disabled',true);
			$('#cal-popup .js-aba-historicoAdd').hide();
		}
	}

	var procedimentos = JSON.parse(jsonEscape(`<?php echo json_encode($evolucaoProcedimentos);?>`));
	var historicoGeral = JSON.parse(jsonEscape(`<?php echo json_encode($historicoGeral);?>`));

	var cardHTML = `<a href="javascript:;" class="reg-group js-procedimento">
						<div class="reg-data js-titulo" style="flex:0 1 300px">
							<h1></h1>
							<p></p>
						</div>
						<div class="reg-steps js-steps" style="margin:0 auto;">
							
						</div>
						<?php /*<div class="reg-data js-status">
							<p></p>
						</div>	*/?>								
						<div class="reg-user">
							<span style="background:blueviolet">KP</span>
						</div>
					</a>`;

	var autor = `<?php echo utf8_encode($usr->nome);?>`;
	var id_usuario = `<?php echo utf8_encode($usr->id);?>`;

	const procedimentosListar = () => {

		$('.js-procedimento').remove();

		console.log(procedimentos);
		procedimentos.forEach(x=>{

			//$('.js-sel-procedimento').find(`option[value=${x.id_procedimento}]`).prop('disabled',true);
			$('.js-div-procedimentos').append(cardHTML);

			let cor = `#CCC`;
			let status = ``;
			let steps='';

			if(x.statusEvolucao=='iniciar') {
				status=`Não iniciado`;
				cor=`orange`;

				steps = `<div class="reg-steps__item active">
							<h1 style="background:var(--amarelo)">1</h1>
							<p>A Iniciar</p>									
						</div>

						<div class="reg-steps__item active">
							<h1 style="background:#999">2</h1>
							<p>Em Tratamento</p>									
						</div>

						<div class="reg-steps__item active">
							<h1 style="background:#999">3</h1>
							<p>Finalizado/Cancelado</p>									
						</div>`;

			} else if(x.statusEvolucao=='iniciado') {
				status=`Em Tratamento`;
				cor=`blue`;

				steps = `<div class="reg-steps__item active">
							<h1 style="background:var(--verde)">1</h1>
							<p>A Iniciar</p>									
						</div>

						<div class="reg-steps__item active">
							<h1 style="background:var(--verde)">2</h1>
							<p>Em Tratamento</p>									
						</div>

						<div class="reg-steps__item active">
							<h1 style="background:#999">3</h1>
							<p>Finalizado/Cancelado</p>									
						</div>`;
			} else if(x.statusEvolucao=='finalizado') {
				status=`Finalizado`;
				cor=`green`;

				steps = `<div class="reg-steps__item active">
							<h1 style="background:var(--verde)">1</h1>
							<p>A Iniciar</p>									
						</div>

						<div class="reg-steps__item active">
							<h1 style="background(var--verde)">2</h1>
							<p>Em Tratamento</p>									
						</div>

						<div class="reg-steps__item active">
							<h1 style="background:var(--verde);">3</h1>
							<p>Finalizado</p>									
						</div>`;
			} else if(x.statusEvolucao=='cancelado') {
				cor=`red`;
				status=`Cancelado`;
				steps = `<div class="reg-steps__item active">
							<h1 style="background:var(--verde)">1</h1>
							<p>A Iniciar</p>									
						</div>

						<div class="reg-steps__item active">
							<h1 style="background(var--verde)">2</h1>
							<p>Em Tratamento</p>									
						</div>

						<div class="reg-steps__item active">
							<h1 style="background:var(--vermelho);">3</h1>
							<p>Cancelado</p>									
						</div>`;
			}

			let numero = '';
			if(x.numeroTotal>1) numero = x.numero+'/'+x.numeroTotal;

			let opcao='';
			if(x.opcao.length>0) opcao=x.opcao+' - ';



			//$('.js-procedimento .reg-color:last').css('background-color',cor);
			$('.js-procedimento .js-titulo:last').html(`<h1>${x.titulo} ${numero}</h1><p>${opcao}${x.plano}</p>`);
			//$('.js-procedimento .js-status:last').html(`<p>${status}</p>`);
			$('.js-procedimento .js-steps:last').html(steps);
			$('.js-procedimento .reg-user:last span').html((!x.profissionalIniciais || x.profissionalIniciais.length==0)?'<span class="iconify" data-icon="bi:person-fill" data-inline="false"></span>':x.profissionalIniciais);
			$('.js-procedimento .reg-user:last span').css('background',(!x.profissionalCor || x.profissionalCor.length==0)?'':x.profissionalCor);
			$(`.js-procedimento:last`).attr('data-usuario',autor);
			$(`.js-procedimento:last`).click(function(){popView(this);});
		});

		$('textarea[name=procedimentos]').val(JSON.stringify(procedimentos));


		if(persistirAoEditar==1) {
			if(index>=0) {
				let procedimentoAux=procedimentos[index];
				console.log(procedimentoAux);
				let novosHistoricos = [];
				procedimentoAux.historico.forEach(h=> {
					if(h.id===undefined) {
						novosHistoricos.push(h);
					}
				})


				procedimentoAux.historico=novosHistoricos;
				$.ajax({
					type:"POST",
					url:"pg_contatos_pacientes_evolucao_procedimentos.php",
					data:`ajax=persistirAoEditar&procedimento=${JSON.stringify(procedimentoAux)}`,
					success:function(rtn) {
						if(rtn.success) {
							if(rtn.historico) {
								procedimentos[index].historico=rtn.historico
							}

						} else if(rtn.error) {
							swal({title: "Erro!", text: rtn.error, html:true, type:"error", confirmButtonColor: "#424242"});
						} else {
							swal({title: "Erro!", text: 'Algum erro ocorreu durante o registro desta alteração!', html:true, type:"error", confirmButtonColor: "#424242"});
						}
					}
				})
			}
		}

	}

	const historicoListar = (index) => {

		$(`#cal-popup .js-grid-historico .js-lista`).html('');


		if(procedimentos[index].historico.length>0) {

			procedimentos[index].historico.forEach(x=> {

				let html = `<div class="hist-lista-item hist-lista-item_lab" style="font-size:12px;">
								<h1>${x.usuario} em ${x.data}</h1>
								<p>${x.obs}</p>
							</div>`;
				$(`#cal-popup .js-grid-historico .js-lista`).append(html);
			});
		} else {
			$(`#cal-popup .js-grid-historico .js-lista`).html(`<center>Nenhum histórico lançado<center>`);
		}

		$('textarea[name=procedimentos]').val(JSON.stringify(procedimentos));

	}

	const d2 = (num) => {
		if($.isNumeric(num)) {
			num = eval(num);

			if(num<=9) return `0${num}`;
			else return num;
		}
	}

	const nl2br = (str, is_xhtml) => {
	    if (typeof str === 'undefined' || str === null) {
	        return '';
	    }
	    var breakTag = (is_xhtml || typeof is_xhtml === 'undefined') ? '<br />' : '<br>';
	    return (str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1' + breakTag + '$2');
	}

	const historicoGeralListar = () => {

		$('#js-todosHistoricos').html(``);
		if(historicoGeral.length==0) {
			$('#js-todosHistoricos').html(`<center>Nenhum histórico lançado</center>`);
		} else {
			historicoGeral.forEach(x=>{
				/*if(x.tipo=='procedimento') {
					itemHtml = `<div class="hist-lista-item hist-lista-item_lab">
									<h1>${x.procedimento}</h1>
									<h1>${x.usuario} em ${x.data}</h1>
									<p>${x.obs}</p>
								</div>`;
				} else {*/
					itemHtml = `<div class="hist-lista-item hist-lista-item_lab">
									<h1>${x.usuario} em ${x.data}</h1>
									<p>${x.obs}</p>
								</div>`;

				//}

				$('#js-todosHistoricos').append(itemHtml);
			});
		}

		$('textarea[name=historicoGeral]').val(JSON.stringify(historicoGeral));

		setTimeout(function(){document.getElementById('js-todosHistoricos').scrollTo(0,document.getElementById('js-todosHistoricos').scrollHeight)},100);
	}

	$(function(){
		<?php
		if(basename($_SERVER['PHP_SELF'])=="pg_contatos_pacientes_evolucao.php") {
		?>
		procedimentosListar();
		<?php
		} else if(isset($evolucao)) {
		?>
		procedimentosListar();
		historicoGeralListar();
		<?php
		}
		?>
		$(document).mouseup(function(e)  {
		    var container = $("#cal-popup");
		    // if the target of the click isn't the container nor a descendant of the container
		    if (!container.is(e.target) && container.has(e.target).length === 0) 
		    {
		       $('#cal-popup').hide();
		    }
		});

		$('#cal-popup .js-obs-add').click(function() {
			let obs = $('#cal-popup .js-obs').val();
			let index = $('#cal-popup .js-index').val();
			let dtAux = new Date();
			let dt = `${d2(dtAux.getDay())}/${d2(dtAux.getMonth())}/${dtAux.getFullYear()} ${d2(dtAux.getHours())}:${d2(dtAux.getMinutes())}`;
			let item = {};
			item.id_usuario='<?php echo $usr->id;?>';
			item.usuario='<?php echo utf8_encode($usr->nome);?>';
			item.data=dt;
			item.obs=nl2br(obs,true);

			procedimentos[index].historico.push(item);

			$('#cal-popup .js-obs').val('');

			$('#cal-popup .js-aba-historico').click();
			setTimeout(function(){document.getElementById('historicoLista').scrollTo(0,document.getElementById('historicoLista').scrollHeight)},100);
			historicoListar(index);

			

		});

		$('.js-obsGeral-add').click(function(){
			let obs = $('textarea.js-historicoGeral').val();
			if(obs.length==0) {
				swal({title: "Erro!", text: 'Digite a observação que deseja adicionar', html:true, type:"error", confirmButtonColor: "#424242"});
			} else {
				let dtAux = new Date();
				let dt = `${d2(dtAux.getDay())}/${d2(dtAux.getMonth())}/${dtAux.getFullYear()} ${d2(dtAux.getHours())}:${d2(dtAux.getMinutes())}`;
				let item = {};
				item.id_usuario='<?php echo $usr->id;?>';
				item.usuario='<?php echo utf8_encode($usr->nome);?>';
				item.data=dt;
				item.obs=nl2br(obs,true);

				historicoGeral.push(item);
				historicoGeralListar();
				$('textarea.js-historicoGeral').val('')


			}
		});

		$('.js-btn-salvar').click(function(){
			$('.js-form-evolucao').submit();
		});

		$('.chosen2').chosen({hide_results_on_select:false})

		$('.js-btn-fechar').click(function(){$('.cal-popup').hide();})

		$('.js-btn-add').click(function(){

			$('select.js-sel-procedimento option:selected').each(function(index,el) {
				<?php /*
				let id_procedimento = $('select.js-sel-procedimento').val();
				let numero = $('select.js-sel-procedimento option:selected').attr('data-numero');
				let numeroTotal = $('select.js-sel-procedimento option:selected').attr('data-numeroTotal');
				let opcao = $('select.js-sel-procedimento option:selected').attr('data-opcao');
				let plano = $('select.js-sel-procedimento option:selected').attr('data-plano');
				let titulo = $('select.js-sel-procedimento option:selected').attr('data-titulo');
				let id_profissional = $('select.js-sel-procedimento option:selected').attr('data-id_profissional');
				let profissionalIniciais = $('select.js-sel-procedimento option:selected').attr('data-profissionalIniciais');
				let id_tratamento_procedimento = $('select.js-sel-procedimento option:selected').attr('data-id_tratamento_procedimento');
				let profissionalCor = $('select.js-sel-procedimento option:selected').attr('data-profissionalCor');
				let statusEvolucao = $('select.js-sel-procedimento option:selected').attr('data-statusEvolucao');
				let obs = ``;
				*/?>
				let data = `ajax=historico&id_procedimento_aevoluir=${$(el).val()}`;
				$.ajax({
					type:"POST",
					data:data,
					success:function(rtn) {
						if(rtn.success) { 
							
							let id_procedimento_aevoluir = $(el).val();
							let id_procedimento = $(el).attr('data-id_procedimento');
							let numero = $(el).attr('data-numero');
							let numeroTotal = $(el).attr('data-numeroTotal');
							let opcao = $(el).attr('data-opcao');
							let plano = $(el).attr('data-plano');
							let titulo = $(el).attr('data-titulo');
							let id_profissional = $(el).attr('data-id_profissional');
							let profissionalIniciais = $(el).attr('data-profissionalIniciais');
							let id_tratamento_procedimento = $(el).attr('data-id_tratamento_procedimento');
							let profissionalCor = $(el).attr('data-profissionalCor');
							let statusEvolucao = $(el).attr('data-statusEvolucao');
							let obs = ``;
							let historico = rtn.historico;
							
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
							let data = `${dia}/${mes}/${dt.getFullYear()} ${dt.getHours()}:${dt.getMinutes()}`;

							if(id_procedimento_aevoluir.length>0) {
								let item = { id_procedimento_aevoluir,
												 id_procedimento, 
												opcao, 
												plano, 
												titulo, 
												profissionalCor, 
												profissionalIniciais, 
												statusEvolucao, 
												autor, 
												id_usuario, 
												data, 
												obs,
												id_profissional,
												id_tratamento_procedimento,
												numero,
												numeroTotal,
												historico
											}

								item.avulso=0;
								procedimentos.push(item); 
								procedimentosListar();

					 			$('.js-sel-procedimento').find(`option[value=${id_procedimento_aevoluir}]`).prop('disabled',true);
					 			$('select.js-sel-procedimento').val('').trigger('chosen:updated');
							}
						}
					},
				})
				
			});


			

			/*} else {
				swal({title: "Erro!", text: 'Selecione o procedimento que deseja adicionar', html:true, type:"error", confirmButtonColor: "#424242"});
			}*/

		});

		$('#cal-popup .js-obs').keyup(function(){
			let index = $('.js-index').val();
			procedimentos[index].obs=$(this).val();
		});

		$('#cal-popup .js-obs').change (function(){
			procedimentosListar();
		});

		$('#cal-popup').on('change','.js-situacao',function(){
			let index = $('#cal-popup .js-index').val();


			// adiciona no historico
			let obs = `<h2>alterou status para <strong style=background:${$(this).find('option:selected').attr('data-cor')}>${$(this).find('option:selected').text()}</strong></h2>`;
			let dtAux = new Date();
			let dt = `${d2(dtAux.getDay())}/${d2(dtAux.getMonth())}/${dtAux.getFullYear()} ${d2(dtAux.getHours())}:${d2(dtAux.getMinutes())}`;

			let item = {};
			item.id_usuario='<?php echo $usr->id;?>';
			item.usuario='<?php echo utf8_encode($usr->nome);?>';
			item.data=dt;
			item.obs=nl2br(obs,true);

			procedimentos[index].historico.push(item);


			$('#cal-popup .js-aba-historico').click();
			setTimeout(function(){document.getElementById('historicoLista').scrollTo(0,document.getElementById('historicoLista').scrollHeight)},100);
			historicoListar(index);

			//procedimentos[index].statusEvolucao=$(this).val();
			procedimentos[index].statusEvolucao=$(this).val();
			procedimentosListar();

		});

		$('#cal-popup').on('change','.js-profissional',function(){
			let index = $('#cal-popup .js-index').val();

			// adiciona no historico
			let obs = `<h2>alterou profissional para <strong style=background:${$(this).find('option:selected').attr('data-iniciaisCor')}>${$(this).find('option:selected').attr('data-iniciais')}</strong></h2>`;
			let dtAux = new Date();
			let dt = `${d2(dtAux.getDay())}/${d2(dtAux.getMonth())}/${dtAux.getFullYear()} ${d2(dtAux.getHours())}:${d2(dtAux.getMinutes())}`;

			let item = {};
			item.id_usuario='<?php echo $usr->id;?>';
			item.usuario='<?php echo utf8_encode($usr->nome);?>';
			item.data=dt;
			item.obs=nl2br(obs,true);

			procedimentos[index].historico.push(item);


			$('#cal-popup .js-aba-historico').click();
			setTimeout(function(){document.getElementById('historicoLista').scrollTo(0,document.getElementById('historicoLista').scrollHeight)},100);
			historicoListar(index);

			procedimentos[index].id_profissional=$(this).val();
			procedimentos[index].profissionalIniciais=$(this).find('option:selected').attr('data-iniciais');
			procedimentos[index].profissionalCor=$(this).find('option:selected').attr('data-iniciaisCor');
			procedimentosListar();
		});

		$('#cal-popup').on('click','.js-btn-excluir',function(){

			swal({
				title: "Atenção",
				text: "Você tem certeza que deseja remover este registro?",
				type: "warning",
				showCancelButton: true,
				confirmButtonColor: "#DD6B55",
				confirmButtonText: "Sim!",
				cancelButtonText: "Não",
				closeOnConfirm: true,
				closeOnCancel: false 
				}, 
				function(isConfirm) {   
					if (isConfirm) {  
					 	let index = $('#cal-popup .js-index').val();

					 	let id_procedimento = procedimentos[index].id_procedimento;
					 	$('.js-sel-procedimento').find(`option[value=${id_procedimento}]`).prop('disabled',false);
						procedimentos.splice(index,1);
						procedimentosListar();	
					} else {   
						swal.close();   
					}
				}
				);
		})

		$('#modalProcedimento').hide();
		$('.js-btn-addProcedimento').click(function(){
			$.fancybox.open({
					src: '#modalProcedimento'
			});
		});

		$('select.js-id_procedimento').change(function(){
			let id_procedimento = $(this).val();

			if(id_procedimento.length>0) {
				let id_regiao = $(this).find('option:selected').attr('data-id_regiao');
				let regiao = $(this).find('option:selected').attr('data-regiao');
				let quantitativo = $(this).find('option:selected').attr('data-quantitativo');

				$(`.js-inpt-quantidade`).parent().parent().hide();
				if(quantitativo==1) {
					$(`.js-inpt-quantidade`).parent().parent().show();
				}
				$(`.js-regiao`).hide();
				$(`.js-regiao-${id_regiao}`).show();
				$(`.js-regiao-${id_regiao}`).find('select').chosen({hide_results_on_select:false,allow_single_deselect:true});

				$(`.js-procedimento-btnOk`).show();
				let data = `ajax=planos&id_unidade=${id_unidade}&id_procedimento=${id_procedimento}`;
				$.ajax({
					type:"POST",
					data:data,
					success:function(rtn) {
						if(rtn.success) { 
							$('.js-id_plano option').remove();
							$('.js-id_plano').append(`<option value=""></option>`);
							console.log(rtn.planos);
							if(rtn.planos) {

								rtn.planos.forEach(x=> {
									$('.js-id_plano').append(`<option value="${x.id}" data-valor="${x.valor}">${x.titulo}</option>`);
								});
							}
							$('.js-id_plano').trigger('chosen:updated')
						}
					},
				})
			} else {
				$(`.js-regiao`).hide();
				$(`.js-procedimento-btnOk`).hide();
			}
		});

		$('.js-btn-addAvulso').click(function(){
			let id_procedimento = $(`.js-id_procedimento`).val();
			let id_regiao = $(`.js-id_procedimento option:selected`).attr('data-id_regiao');
			let id_plano = $(`.js-id_plano`).val();
			let valor = $(`.js-id_plano option:selected`).attr('data-valor');
			let titulo = $(`.js-id_procedimento option:selected`).text();
			let plano = $(`.js-id_plano option:selected`).text();
			let quantitativo = $(`.js-id_procedimento option:selected`).attr('data-quantitativo');
			let quantidade = $(`.js-inpt-quantidade`).val();
			let situacao = `aguardandoAprovacao`;
			let statusEvolucao = ``;
			let obs = ``;
			let id_profissional = $('.js-id_profissional').val();
			let profissionalIniciais = $('.js-id_profissional option:selected').attr('data-iniciais');
			let profissionalCor = $('.js-id_profissional option:selected').attr('data-iniciaisCor');
			let historico = [];
			//alert(quantitativo);

			let erro = ``;
			if(id_procedimento.length==0) erro=`Selecione o Procedimento`;
			//else if(quantitativo==1 && (quantidade.length==0 || eval(quantidade)<=0 || eval(quantidade)>=99)) erro=`Defina a quantidade<br />(mín: 1, máx: 99)`;
			else if(id_regiao>=2 && $(`.js-regiao-${id_regiao}-select`).val().length==0) erro=`Preencha a Região`
			else if(id_plano.length==0) erro=`Selecione o Plano`;


			let dt = new Date();
			let mes = dt.getMonth();
			let dia = dt.getDate();
			mes++
			mes=mes<=9?`0${mes}`:mes;
			dia=dia<=9?`0${dia}`:dia;
			let data = `${dia}/${mes}/${dt.getFullYear()} ${dt.getHours()}:${dt.getMinutes()}`;

			if(erro.length==0) {

				let linhas=1;
				if(id_regiao>=2) {
					linhas = eval($(`.js-regiao-${id_regiao}-select`).val().length);
				}

				let item= {};

				
				let opcoes = ``;
				for(var i=0;i<linhas;i++) {
					item = { titulo, 
								id_procedimento,
								id_regiao,
								id_plano,
								plano,
								quantidade,
								situacao,
								valor,
								id_profissional,
								profissionalIniciais,
								profissionalCor,
								autor,
								id_usuario,
								historico,
								data };

					item.profissional=0;
					item.desconto=0;
					item.valorCorrigido=valor;
					item.obs='';
					item.avulso=1;

					opcao = id_opcao = ``;
					if(id_regiao>=2) {
						id_opcao = $(`.js-regiao-${id_regiao}-select option:selected:eq(${i})`).val();
						opcao = $(`.js-regiao-${id_regiao}-select option:selected:eq(${i})`).text();
					}
					item.opcao=opcao;
					item.id_opcao=id_opcao;

					procedimentos.push(item);
				}

				$(`.js-id_procedimento`).val('').trigger('chosen:updated');
				$(`.js-id_plano`).val('').trigger('chosen:updated');
				$(`.js-id_profissional`).val('').trigger('chosen:updated');
				$(`.js-inpt-quantidade`).val(1).parent().parent().hide();
				
				$(`.js-regiao-${id_regiao}-select`).val([]).trigger('chosen:updated').parent().parent().hide();;
				$.fancybox.close();
				procedimentosListar();
			} else {
				swal({title: "Erro!", text: erro, html:true, type:"error", confirmButtonColor: "#424242"});
			}
		});


	});
</script>
	<section id="cal-popup" class="cal-popup cal-popup_paciente cal-popup_top cal-popup_alt" style="left:703px; top:338px; margin-left:303px;display: none">
		<a href="javascript:;" class="cal-popup__fechar js-btn-fechar"><i class="iconify" data-icon="mdi-close"></i></a>
		<section class="paciente-info">
			<header class="paciente-info-header">
				<section class="paciente-info-header__inner1">
					<h1 class="js-titulo"></h1>
					<p style="color:var(--cinza4);"><span style="color:var(--cinza4);" class="js-opcao"></span> - <span class="js-plano"></span> </p>
					
				</section>
			</header>
			<input type="hidden" class="js-index" />

			<div class="abasPopover">
				<a href="javascript:;" onclick="$(this).parent().parent().find('a').removeClass('active');$(this).parent().parent().find('.js-grid').hide();$(this).parent().parent().find('.js-grid-info').show();$(this).addClass('active');" class="active">Informações</a>
				<?php /*<a href="javascript:;" onclick="$(this).parent().parent().find('a').removeClass('active');$(this).parent().parent().find('.js-grid').hide();$(this).parent().parent().find('.js-grid-valor').show();$(this).addClass('active');">Valor</a>*/?>
				<a href="javascript:;" class='js-aba-historicoAdd' onclick="$(this).parent().parent().find('a').removeClass('active');$(this).parent().parent().find('.js-grid').hide();$(this).parent().parent().find('.js-grid-obs').show();$(this).addClass('active');">Observações</a>
				<a href="javascript:;" class="js-aba-historico" onclick="$(this).parent().parent().find('a').removeClass('active');$(this).parent().parent().find('.js-grid').hide();$(this).parent().parent().find('.js-grid-historico').show();$(this).addClass('active');document.getElementById('historicoLista').scrollTo(0,document.getElementById('historicoLista').scrollHeight)">Histórico</a>
			</div>

			<div class="paciente-info-grid js-grid js-grid-info" style="font-size: 12px;">		
				
				<dl style="grid-column:span 2;">
					<dt>Profissional</dt>
					<dd><?php echo $selectProfissional;?></dd>
				</dl>

				

				<dl style="grid-column:span ;">
					<dd><span class="iconify" data-icon="bx:bx-user-circle" data-inline="true"></span> <span class="js-autor"></span></dd>
				</dl>
				<dl style="grid-column:span ;">
					<dd><span class="iconify" data-icon="bi:clock" data-inline="true"></span> <span class="js-autor-data"></span></dd>
				</dl>
			</div>
			<script type="text/javascript">
				$(function(){

					$('input.money').maskMoney({symbol:'', allowZero:true, showSymbol:true, thousands:'.', decimal:',', symbolStay: true});
					

				})
			</script>

			<div class="paciente-info-grid js-grid js-grid-obs" style="display:none;font-size:12px;color:#666">	
				<dl style="grid-column:span 2;">
					<dd>
						<textarea style="height:100px" class="js-obs"></textarea>
					</dd>
				</dl>
				<dl style="grid-column:span 2;">
					<dd><a href="javascript:;" class="js-obs-add button button__full">Adicionar</a></dd>
				</dl>
			</div>

			<div class="paciente-info-grid js-grid js-grid-historico" style="display:none;font-size:12px;color:#666">	
				<div class="hist-lista js-lista" id="historicoLista" style="grid-column:span 2;">
					<?php /*<div class="hist-lista-item hist-lista-item_lab">
						<h1>Laboratório em 12/07/2021</h1>
						<p>Estamos demorando um pouco mais que o habitual. Desculpe a demora e aguarde um pouco mais</p>
					</div>
					<div class="hist-lista-item hist-lista-item_lab">
						<h1>Laboratório em 11/07/2021</h1>
						<h2>status alterado para <strong style="background:limegreen;">Aceito</strong></h2>
					</div>
					<div class="hist-lista-item">
						<h1>Kroner Costa em 11/07/2021</h1>
						<p>Documento enviado!</p>
						<h2>status alterado para <strong style="background:blue">Em aberto</strong></h2>
					</div>
					<div class="hist-lista-item hist-lista-item_lab">
						<h1>Laboratório em 10/07/2021</h1>
						<p>Falta documento sobre as cores da faceta</p>
						<h2>status alterado para <strong style="background:red;">OS Recusada</strong></h2>
					</div>
					<div class="hist-lista-item">
						<h1>Kroner Costa em 10/07/2021</h1>
						<h2><strong style="background:#000;">OS Criada</strong></h2>
					</div>*/?>
				</div>
			</div>
			<div class="paciente-info-opcoes">
				<?php //echo $selectSituacaoOptions;?>
				<select class="js-situacao" style="display: none;"></select>
				<?php
				if(empty($evolucao)) {
				?>
				
				<a href="javascript:;" class="js-btn-excluir button button__sec">excluir</a>
				<?php
				}
				?>
			</div>
		</section>
	</section>