	<script type="text/javascript">

	var id_agenda = '';

	$(function(){
		verificaAgendamento();

		$('select[name=id_paciente]').change(function(){
			let telefone = $(this).find('option:selected').attr('data-telefone');
			if($(this).find('option:selected').length==0) {
				$('.js-telefone').hide();
				$('.js-telefone input').val('');
			}
			else {
				$('.js-telefone').show();
				$('.js-telefone input').val(telefone);
			}
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

		$('.chosen').chosen({hide_results_on_select:false,allow_single_deselect:true});
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

				let data = `ajax=agendamentoPersistir&id_agenda=${id_agenda}&profissionais=${profissionais}&${campos}`;

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

		$('.js-paciente-telefone').click(function(){
			//box/boxPacienteTelefone.php
			let id_paciente = $('select[name=id_paciente]').val();
			
			$.fancybox.open({
		        src: `box/boxPacienteTelefone.php?id_paciente=${id_paciente}`,
		        type: "ajax"
		    });
		})
		
		
	})
</script>

<section class="modal">


	<div class="modal__fixo" style="padding-top:0;padding-bottom: 15px;text-align:right">
				&nbsp;<a href="javascript:;" class="caminho__tutorial button" style="float:left">Paciente</a>&nbsp;
		&nbsp;<a href="javascript:;" class="caminho__tutorial button button__sec js-btn-pessoal" style="margin-left:10px;float:left;">Compromisso</a>&nbsp;
		
		<script type="text/javascript">
			$(function(){ 
				$('.js-btn-pessoal').click(function(){
					$.fancybox.close();
					$.fancybox.open({
				        src: "box/boxAgendamentoPessoal.php",
				        type: "ajax"
				    });
				})
			});
		</script>
		
		<button type="button" class="button button__lg js-salvar"><i class="iconify" data-icon="bx-bx-check"></i> Agendar</button>
	</center>
	</div>
	<div class="modal__content" style="padding-right: 10px;">

		<form method="post" class="form js-form-agendamento">
			<fieldset>
				<legend>Dados do Agendamento</legend>
				<div>
					<div class="colunas5">
						<dl class="dl3">
							<dt>Paciente</dt>
							<dd>
								<select name="id_paciente" class="chosen obg" style="width:90%;float:left">
									<option value=""></option>
									<option value="42" data-telefone="62999181775">KRONER PACIENTE</option><option value="43" data-telefone="62982400606">LUCIANO DEXHEIMER MORAIS</option>								</select>
								<a href="box/boxNovoPaciente.php" data-fancybox data-type="ajax" class="button button__sec" style="float:right"><i class="iconify" data-icon="ic-baseline-add"></i></a>
							</dd>
						</dl>
						<dl class="js-telefone" style="display: none;">
							<dt>Telefone</dt>
							<dd>
								<input type="text" class="telefone" disabled style="width:90%;float:left" />
								<a href="javascript:;" class="button button__sec js-paciente-telefone" style="float:right"><i class="iconify" data-icon="si-glyph:arrow-change"></i></a>
							</dd>
						</dl>
					</div>
				</div>
				<textarea name="procedimentosJSON" class="js-agenda-procedimentoJSON" style="display: none;"></textarea>
				<div class="colunas4">
					<dl class="dl3">
						<dt>Cirurgião Dentista</dt>
						<dd>
							<select class="chosen js-agenda-verifica js-profissionais" multiple>
								<option value=""></option>
								<option value="9">DR KRONNER FILHO</option><option value="14">DRA GABRIELLA</option><option value="15">DRA MARCIA</option><option value="11">DRA POLYANNA</option><option value="13">KRONER MACHADO COSTA </option>							</select>
						</dd>
					</dl>
					<dl>
						<dt>Cadeira</dt>
						<dd>
							<select name="id_cadeira" class="chosen obg js-agenda-verifica">
								<option value=""></option>
								<option value="1">CONSULTÓRIO 01</option><option value="2">CONSULTÓRIO 02</option><option value="3">CONSULTÓRIO 03</option>							</select>
						</dd>
					</dl>
				</div>
				<div class="colunas4">
					
					<dl>
						<dt>Data</dt>
						<dd><input type="text" name="agenda_data" class="agendaData obg js-agenda-verifica" value="18/11/2020" /></dd>
					</dl>
					<dl>
						<dt>Horário</dt>
						<dd><input type="text" name="agenda_hora" class="agendaHora obg js-agenda-verifica" value="08:00" /></dd>
					</dl>
					<dl>
						<dt>Duração (em minutos)</dt>
						<dd><input type="text" name="agendaDuracao" class="obg js-maskNumber" maxlength="3" value="" /></dd>
					</dl>

					<dl>
						<dt>Status</dt>
						<dd>
							<select name="id_status" class="obg">
								<option value="">-</option>
								<option value="1">À CONFIRMAR</option><option value="5">ATENDIDO</option><option value="2">CONFIRMADO</option><option value="4">DESMARCADO</option><option value="3">FALTOU</option>							</select>
						</dd>
					</dl>
				</div>
			</fieldset>

			<dl style="color:red;display: none;" id="box-validacoes" style="">
			</dl>

			<style type="text/css">
				.js-agenda-tableProcedimento li {float:left;border: solid 1px #CCC;padding:5px;margin: 2px;background:#FFF;border-radius:5px;}
			</style>

			<script type="text/javascript">
				var procedimentos = [];

				
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

			<fieldset>
				<legend>Procedimentos</legend>
				<div class="registros">
					<table class="js-agenda-tableProcedimento">
						<tr>
							<th style="width:40%">Procedimento</th>
							<th>Tipo</th>
							<th style="width:40%">Região</th>
							<th style="width:150px;"></th>
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
									<option value="15" data-id_regiao="1" data-regiao="GERAL">ANESTESIA </option><option value="16" data-id_regiao="4" data-regiao="POR DENTE">AUMENTO DE COROA FUNCIONAL </option><option value="17" data-id_regiao="2" data-regiao="POR ARCADA">GENGIVECTOMIA + GENGIVOPLASTIA </option><option value="18" data-id_regiao="2" data-regiao="POR ARCADA">OSTEOTOMIA + OSTEOPLASTIA </option><option value="19" data-id_regiao="2" data-regiao="POR ARCADA">PRÓTESE ORTOPÉDICA - METACRILATO </option><option value="20" data-id_regiao="1" data-regiao="GERAL">PROTÓTIPO DA MAXILA PARA METACRILATO </option><option value="21" data-id_regiao="2" data-regiao="POR ARCADA">REPOSICIONAMENTO LABIAL INTERNO </option><option value="22" data-id_regiao="4" data-regiao="POR DENTE">RECOBRIMENTO RADICULAR UNITÁRIO </option><option value="23" data-id_regiao="3" data-regiao="POR QUADRANTE">RECOBRIMENTO RADICULAR MÚLTIPLO </option><option value="24" data-id_regiao="4" data-regiao="POR DENTE">ENXERTO PARA MUDANÇA DE BIOTIPO GENGIVAL </option><option value="25" data-id_regiao="1" data-regiao="GERAL">PLACA PROTETORA DE PALATO </option><option value="26" data-id_regiao="3" data-regiao="POR QUADRANTE">LEVANTAMENTO DE SEIO MAXILAR</option><option value="27" data-id_regiao="3" data-regiao="POR QUADRANTE">REGENERAÇÃO ÓSSEA GUIADA (ROG) - HORIZONTAL</option><option value="28" data-id_regiao="4" data-regiao="POR DENTE">REGENERAÇÃO ÓSSEA GUIADA (ROG) - UNITÁRIA</option><option value="29" data-id_regiao="3" data-regiao="POR QUADRANTE">REGENERAÇÃO ÓSSEA GUIADA (ROG) - VERTICAL</option><option value="30" data-id_regiao="4" data-regiao="POR DENTE">EXODONTIA SIMPLES </option><option value="31" data-id_regiao="4" data-regiao="POR DENTE">EXODONTIA COMPLEXA </option><option value="32" data-id_regiao="4" data-regiao="POR DENTE">EXODONTIA ATRAUMÁTICA </option><option value="33" data-id_regiao="4" data-regiao="POR DENTE">PRESERVAÇÃO ALVEOLAR COM BIO - OSS COLLAGEN</option><option value="34" data-id_regiao="4" data-regiao="POR DENTE">IMPLANTE / NEODENT</option><option value="35" data-id_regiao="4" data-regiao="POR DENTE">IMPLANTE / STRAUMANN</option><option value="36" data-id_regiao="2" data-regiao="POR ARCADA">IMPLANTE PRÓTESE PROTOCOLO - 4 UNID / NEODENT </option><option value="37" data-id_regiao="2" data-regiao="POR ARCADA">IMPLANTE PRÓTESE PROTOCOLO - 6 UNID / NEODENT</option><option value="38" data-id_regiao="2" data-regiao="POR ARCADA">IMPLANTE PRÓTESE PROTOCOLO - 4 UNID / STRAUMANN</option><option value="40" data-id_regiao="2" data-regiao="POR ARCADA">IMPLANTE PRÓTESE PROTOCOLO - 6 UNID / STRAUMANN</option><option value="41" data-id_regiao="4" data-regiao="POR DENTE">EXPLANTAÇÃO </option><option value="42" data-id_regiao="4" data-regiao="POR DENTE">MINI - IMPLANTE </option><option value="43" data-id_regiao="3" data-regiao="POR QUADRANTE">MINI - PLACA</option><option value="44" data-id_regiao="3" data-regiao="POR QUADRANTE">BIÓPSIA</option><option value="45" data-id_regiao="2" data-regiao="POR ARCADA">FRENECTOMIA</option><option value="46" data-id_regiao="4" data-regiao="POR DENTE">CIRURGIA PARENDODÔNTICA </option><option value="47" data-id_regiao="1" data-regiao="GERAL">CONSULTA + RETORNO: ANAMNESE; FOTOGRAFIAS; ESCANEAMENTO; PLANO DE TRATAMENTO</option><option value="48" data-id_regiao="2" data-regiao="POR ARCADA">DIGITAL SMILE DESIGN + ENCERAMENTO DIAGNÓSTICO + MOCK UP</option><option value="49" data-id_regiao="1" data-regiao="GERAL">APLICAÇÃO CLINPRO XT VARNISH</option><option value="50" data-id_regiao="1" data-regiao="GERAL">PROFILAXIA + RASPAGEM + FLUOR</option><option value="51" data-id_regiao="1" data-regiao="GERAL">RASPAGEM SUB-GENGIVAL</option><option value="52" data-id_regiao="1" data-regiao="GERAL">ATENDIMENTO EMERGENCIAL</option><option value="53" data-id_regiao="4" data-regiao="POR DENTE">OBSERVAÇÃO</option><option value="54" data-id_regiao="4" data-regiao="POR DENTE">RETRATAMENTO ENDODÔNTICO  DENTES ANTERIOR</option><option value="55" data-id_regiao="4" data-regiao="POR DENTE">RETRATAMENTO ENDODÔNTICO DENTE POSTERIOR</option><option value="56" data-id_regiao="4" data-regiao="POR DENTE">TRATAMENTO ENDODÔNTICO DENTE ANTERIOR</option><option value="57" data-id_regiao="0" data-regiao="-">TRATAMENTO ENDODÔNTICO DENTE POSTERIOR</option><option value="58" data-id_regiao="1" data-regiao="GERAL">CONSULTA + PROFILAXIA + APLICAÇÃO TÓPICA DE FLÚOR (PREVENÇÃO)</option><option value="59" data-id_regiao="4" data-regiao="POR DENTE">EXODONTIA DE DENTES DECÍDUOS</option><option value="60" data-id_regiao="4" data-regiao="POR DENTE">RESTAURAÇÃO  IONÔMERO DE VIDRO PEDIATRIA</option><option value="61" data-id_regiao="4" data-regiao="POR DENTE">RESTAURAÇÃO COM RESINA PEDIATRIA</option><option value="62" data-id_regiao="4" data-regiao="POR DENTE">TRATAMENTO ENDODÔNTICO EM DECÍDUOS</option>								</select>
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
								<option value="2">MANDÍBULA</option><option value="1">MAXILA</option>							</select>
						</dd>
					</dl>
					<dl class="js-regiao-3 js-regiao" style="display: none">
						<dt>Quadrante(s)</dt>
						<dd>
							<select class="js-regiao-3-select" multiple>
								<option value=""></option>
								<option value="3">1º QUADRANTE</option><option value="4">2º QUADRANTE</option><option value="5">3º QUADRANTE</option><option value="6">4º QUADRANTE</option>							</select>
						</dd>
					</dl>
					<dl class="js-regiao-4 js-regiao" style="display: none">
						<dt>Dentes(s)</dt>
						<dd>
							<select class="js-regiao-4-select" multiple>
								<option value=""></option>
								<option value="11">11</option><option value="12">12</option><option value="13">13</option><option value="14">14</option><option value="15">15</option><option value="16">16</option><option value="17">17</option><option value="18">18</option><option value="21">21</option><option value="22">22</option><option value="23">23</option><option value="24">24</option><option value="25">25</option><option value="26">26</option><option value="27">27</option><option value="28">28</option><option value="31">31</option><option value="32">32</option><option value="33">33</option><option value="34">34</option><option value="35">35</option><option value="36">36</option><option value="37">37</option><option value="38">38</option><option value="41">41</option><option value="42">42</option><option value="43">43</option><option value="44">44</option><option value="45">45</option><option value="46">46</option><option value="47">47</option><option value="48">48</option><option value="51">51</option><option value="52">52</option><option value="53">53</option><option value="54">54</option><option value="55">55</option><option value="61">61</option><option value="62">62</option><option value="63">63</option><option value="64">64</option><option value="65">65</option><option value="71">71</option><option value="72">72</option><option value="73">73</option><option value="74">74</option><option value="75">75</option><option value="81">81</option><option value="82">82</option><option value="83">83</option><option value="84">84</option><option value="85">85</option>							</select>
						</dd>
					</dl>
				</div>
			</fieldset>


			<div class="colunas4">

				<dl class="js-statusConfirmado">
					<dd><label><input type="checkbox" name="clienteChegou" value="1" /> Cliente chegou</label></dd>
				</dl>
				<dl class="js-statusConfirmado">
					<dd><label><input type="checkbox" name="emAtendimento" value="1" /> Em Atendimento</label></dd>
				</dl>
							</div>	
			<dl>
				<dt>Observações</dt>
				<dd>
					<textarea name="obs" class="noupper"></textarea>
				</dd>
			</dl>

				
		</form>
	</div>


</section>