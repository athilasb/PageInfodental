<?php
	include "includes/header.php";
	include "includes/nav.php";

	$_table=$_p."pacientes";
	$_page=basename($_SERVER['PHP_SELF']);

	$_profissionais=array();
	$sql->consult($_p."colaboradores","id,nome,calendario_iniciais,foto,calendario_cor","where tipo_cro<>'' and lixo=0 order by nome asc");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_profissionais[$x->id]=$x;
	}

	$_tiposExames=array();
	$sql->consult($_p."parametros_examedeimagem","*","where lixo=0 order by titulo asc");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_tiposExames[$x->id]=$x;
	}

	$_usuarios=array();
	$sql->consult($_p."usuarios","id,nome","order by nome asc");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_usuarios[$x->id]=$x;
	}
	$_clinicas=array();
	$sql->consult($_p."parametros_fornecedores","*","where lixo=0 and tipo='CLINICA' order by razao_social, nome asc");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_clinicas[$x->id]=$x;
	}

	$_regioesOpcoes=array();
	$sql->consult($_p."parametros_procedimentos_regioes_opcoes","*","order by titulo asc");
	while($x=mysqli_fetch_object($sql->mysqry)) $_regioesOpcoes[$x->id_regiao][]=$x;

	$_regioes=array();
	$sql->consult($_p."parametros_procedimentos_regioes","*","");
	while($x=mysqli_fetch_object($sql->mysqry)) $_regioes[$x->id]=$x;


	$paciente=$cnt='';
	if(isset($_GET['id_paciente']) and is_numeric($_GET['id_paciente'])) {
		$sql->consult($_p."pacientes","*","where id='".$_GET['id_paciente']."'");
		if($sql->rows) {
			$paciente=mysqli_fetch_object($sql->mysqry);
			$cnt=$paciente;
		}
	}

	$_selectSituacaoOptions=array('aguardando'=>array('titulo'=>'AGUARDANDO EXAME','cor'=>'blue'),
											'concluido'=>array('titulo'=>'CONCLUÍDO','cor'=>'green'),
											'naoRealizado'=>array('titulo'=>'NÃO REALIZADO','cor'=>'red')
											//'cancelado'=>array('titulo'=>'CANCELADO');
										);

	$selectSituacaoOptions='<select class="js-situacao">';
	foreach($_selectSituacaoOptions as $key=>$value) {
		$selectSituacaoOptions.='<option value="'.$key.'">'.$value['titulo'].'</option>';
	}
	$selectSituacaoOptions.='</select>';


	$evolucao='';
	$evolucaoExame=array();
	if(isset($_GET['edita']) and is_numeric($_GET['edita'])) {	
		$sql->consult($_p."pacientes_evolucoes","*","where id='".$_GET['edita']."' and id_tipo=6");
		if($sql->rows) {
			$evolucao=mysqli_fetch_object($sql->mysqry);

			$sql->consult($_p."pacientes_evolucoes_pedidosdeexames","*","where id_evolucao=$evolucao->id");
			if($sql->rows) {
				$registros=array();
				$examesIds=array(-1);
				while($x=mysqli_fetch_object($sql->mysqry)) {
					$registros[]=$x;
					$examesIds[]=$x->id_exame;

				}

				$_exames=array();
				$where="where id IN (".implode(",",$examesIds).")";
				$sql->consult($_p."parametros_examedeimagem","*",$where);
				while($x=mysqli_fetch_object($sql->mysqry)) $_exames[$x->id]=$x;


				foreach($registros as $x) {
					if(isset($_exames[$x->id_exame])) {
						$exame=$_exames[$x->id_exame];

						$profissionalCor='';
						$profissionalIniciais='';

						if(isset($_profissionais[$x->id_profissional])) {
							$p=$_profissionais[$x->id_profissional];
							$profissionalIniciais=$p->calendario_iniciais;
							$profissionalCor=$p->calendario_cor;
						}

						$autor='-';
						if(isset($_usuarios[$evolucao->id_usuario])) {
							$p=$_usuarios[$evolucao->id_usuario];
							$autor=utf8_encode($p->nome);
						}

						$idOpcao=array();

						if(!empty($x->id_opcao)) {
							$idOpcao=json_decode($x->id_opcao,true);
						}

						$evolucaoExame[]=array('id'=>$x->id,
														'autor'=>$autor,
														'data'=>date('d/m/Y H:i',strtotime($x->data)),
														'id_usuario'=>$evolucao->id_usuario,
														'id_exame'=>$exame->id,
														'id_regiao'=>$exame->id_regiao,
														'obs'=>utf8_encode($x->obs),
														'opcao'=>utf8_encode($x->opcao),
														'id_opcao'=>$idOpcao,
														'titulo'=>utf8_encode($exame->titulo),
														'status'=>$x->status);
						
					}
				}
			}  
		} else {
			$jsc->jAlert("Procedimento Aprovado não encontrado!","erro","document.location.href='pg_contatos_pacientes_evolucao.php?id_paciente=".$paciente->id."'");
			die();
		}
	}
	if(isset($_POST['acao'])) {

		if(isset($_POST['exames']) and !empty($_POST['exames'])) {

			$examesJSON = json_decode($_POST['exames']);

			$examesSolicitados=array();
			$erro='';
			foreach($examesJSON as $v) {
				$sql->consult($_p."parametros_examedeimagem","*","where id=$v->id_exame");
				if($sql->rows) {
					$x=mysqli_fetch_object($sql->mysqry);
					$examesSolicitados[]=array('exame'=>$x,'evolucaoExame'=>$v,'id_exame'=>isset($v->id)?$v->id:0);
				} else {
					$erro='Exame '.$v->js-titulo.' não foi encontrado!';
				}
			}


			if(empty($erro)) {

				if(count($examesSolicitados)>0) {

					if(is_object($evolucao)) {
						$sql->update($_p."pacientes_evolucoes","data_pedido='".addslashes(invDate($_POST['data_pedido']))."',
																id_profissional='".addslashes(utf8_decode($_POST['id_profissional']))."',
																id_clinica='".addslashes(utf8_decode($_POST['id_clinica']))."'","where id=$evolucao->id");
						$id_evolucao=$evolucao->id;
					} else {
						// id_tipo = 2 -> Procedimentos Aprovados
						$sql->consult($_p."pacientes_evolucoes","*","WHERE data > NOW() - INTERVAL 1 MINUTE and 
																								id_paciente=$paciente->id and
																								id_tipo=6 and  
																								id_usuario=$usr->id");	
						if($sql->rows) {
							$e=mysqli_fetch_object($sql->mysqry);
							$sql->update($_p."pacientes_evolucoes","data_pedido='".addslashes(invDate($_POST['data_pedido']))."',
																		id_profissional='".addslashes(utf8_decode($_POST['id_profissional']))."',
																		id_clinica='".addslashes(utf8_decode($_POST['id_clinica']))."'","where id=$e->id");
							$id_evolucao=$e->id;
						} else {
							$sql->add($_p."pacientes_evolucoes","data=now(),
																	id_tipo=6,
																	id_paciente=$paciente->id,
																	id_usuario=$usr->id,
																	data_pedido='".addslashes(invDate($_POST['data_pedido']))."',
																	id_profissional='".addslashes(utf8_decode($_POST['id_profissional']))."',
																	id_clinica='".addslashes(utf8_decode($_POST['id_clinica']))."'");
																	//obs='".addslashes(utf8_decode($_POST['obs']))."'");
							$id_evolucao=$sql->ulid;
						}
					}

					

					foreach($examesSolicitados as $obj) {
						$obj=(object)$obj;
						$exame=$obj->exame;
						$evolucaoExame=$obj->evolucaoExame;
						$vSQLExame="data=now(),
									id_paciente=$paciente->id,
									id_evolucao=$id_evolucao,
									id_exame='".addslashes($exame->id)."',
									opcao='".addslashes(utf8_decode($evolucaoExame->opcao))."',
									id_opcao='".addslashes(json_encode($evolucaoExame->id_opcao))."',
									id_profissional='".addslashes($_POST['id_profissional'])."',
									id_clinica='".addslashes($_POST['id_clinica'])."',
									status='".addslashes($evolucaoExame->status)."',
									obs='".addslashes(utf8_decode($evolucaoExame->obs))."'";
									//echo $vSQLExame;die();
						$evProc='';
						if(isset($obj->id_evolucao_exame) and is_numeric($obj->id_evolucao_exame)) {
							$sql->consult($_p."pacientes_evolucoes_pedidosdeexames","*","where id=$obj->id_exame and id_paciente=$paciente->id and lixo=0");
							if($sql->rows) {
								$evProc=mysqli_fetch_object($sql->mysqry);
							}
						}

						if(empty($evProc)) {
							$sql->consult($_p."pacientes_evolucoes_pedidosdeexames","*","WHERE data > NOW() - INTERVAL 1 MINUTE and 
																								id_paciente=$paciente->id and 
																								id_evolucao=$id_evolucao and 
																								id_exame='".addslashes($exame->id)."'");	
							if($sql->rows) {
								$x=mysqli_fetch_object($sql->mysqry);
								$sql->update($_p."pacientes_evolucoes_pedidosdeexames",$vSQLExame,"where id=$x->id");
							} else {
								$sql->add($_p."pacientes_evolucoes_pedidosdeexames",$vSQLExame);
							}
						} else {
							$sql->update($_p."pacientes_evolucoes_pedidosdeexames",$vSQLExame,"where id=$evProc->id");
						}


						

						

					}	

					$jsc->jAlert("Evolução salva com sucesso!","sucesso","document.location.href='pg_contatos_pacientes_evolucao.php?id_paciente=$paciente->id'");
					die();
				} else {
					$jsc->jAlert("Adicione pelo menos um procedimento!","erro","");
				}

			} else {
				$jsc->jAlert($erro,"erro","");
			}

		} else {
			$jsc->jAlert("Adicione pelo menos um pedido de exame para adicionar à Evolução","erro","");
		}
	}
	?>
	<section class="content">
		
		<?php
		require_once("includes/abaPaciente.php");
		?>

		<script type="text/javascript">
			var popViewInfos = [];

			
			const popView = (obj) => {


				index=$(obj).index();

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
				console.log(exames[index]);
				/*if(popViewInfos[index].opcao.length>0) {
					$('#cal-popup .js-opcaoEQtd').html(`Região: ${popViewInfos[index].opcao}`);
				} else {
					$('#cal-popup .js-opcaoEQtd').html(`Quantidade: ${popViewInfos[index].quantidade}`);
				}*/

				$('#cal-popup .js-situacao').val(exames[index].status);
				$('#cal-popup .js-obs').val(jsonUnEscape(exames[index].obs));
				$('#cal-popup .js-titulo').html(`${exames[index].titulo}`);
				$('#cal-popup .js-opcao').html(`${exames[index].opcao}`);
				$('#cal-popup .js-autor').html(exames[index].autor);
				$('#cal-popup .js-autor-data').html(exames[index].data);
				$('#cal-popup .js-situacao').val(exames[index].status);
				$('#cal-popup .js-index').val(index);

			}

			var exames = JSON.parse(jsonEscape(`<?php echo json_encode($evolucaoExame);?>`));

			var cardHTML = `<a href="javascript:;" class="reg-group js-procedimento">
								<div class="reg-color" style="background-color:palegreen"></div>
								<div class="reg-data js-titulo" style="flex:0 1 300px">
									<h1></h1>
									<p></p>
								</div>
								<div class="reg-data js-status">
									<p></p>
								</div>									
								<div class="reg-user">
									<span style="background:blueviolet">KP</span>
								</div>
							</a>`;

			cardHTML = `<div class="reg-group js-exame">
							<div class="reg-color" style="background-color:palegreen"></div>
							<div class="reg-data">
								<h1 class="js-titulo"></h1>
								<p class="js-obs"></p>
							</div>
						</div>`;

			var autor = `<?php echo utf8_encode($usr->nome);?>`;
			var id_usuario = `<?php echo utf8_encode($usr->id);?>`;

			const examesListar = () => {

				$('.js-exame').remove();

				exames.forEach(x=>{
					$('.js-div-exames').append(cardHTML);

					let cor = `#CCC`;
					let status = ``;

					if(x.status=='aguardando') {
						status=`Aguardando Exame`;
						cor=`orange`;
					} else if(x.status=='concluido') {
						status=`Concluído`;
						cor=`green`;
					} else if(x.status=='naoRealizado') {
						cor=`red`;
						status=`Não Realizado`;
					}


					$('.js-exame .reg-color:last').css('background-color',cor);
					$('.js-exame .js-titulo:last').html(`${x.titulo} - ${x.opcao}`);
					$('.js-exame .js-obs:last').html(x.obs);

					$(`.js-exame:last`).click(function(){popView(this);});
				});

				$('textarea[name=exames]').val(JSON.stringify(exames));

			}
			$(function(){

				$(document).mouseup(function(e)  {
				    var container = $("#cal-popup");
				    // if the target of the click isn't the container nor a descendant of the container
				    if (!container.is(e.target) && container.has(e.target).length === 0) 
				    {
				       $('#cal-popup').hide();
				    }
				});

				$('#cal-popup .js-obs').keyup(function(){
					let index = $('.js-index').val();
					exames[index].obs=$(this).val();
				});

				$('#cal-popup .js-obs').change(function(){
					examesListar();
				});

				$('#cal-popup').on('change','.js-situacao',function(){
					let index = $('#cal-popup .js-index').val();
					//procedimentos[index].statusEvolucao=$(this).val();
					exames[index].status=$(this).val();
					examesListar();
				});

				$('.js-div-exames').on('click','.js-btn-excluir',function(){
					let index = $(this).index('table.js-exames .js-btn-excluir');
					
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
								exames.splice(index,1);
								examesListar();	
							} else {   
								swal.close();   
							}
						}
					);

					
				});

				$('input[name=tipo]').click(function(){
					let tipo = $(this).val();

					$(`.js-box`).hide();
					$(`.js-box-${tipo}`).show();
				});

				$('.js-btn-fechar').click(function(){
					$('.js-valorDeDesconto').val(0)
					$('.cal-popup').hide();
				});

				$('.js-btn-salvar').click(function(){
					$('form').submit();
				});

				$('.js-sel-id_exame').change(function(){
					let obs = $('.js-sel-id_exame option:selected').attr('data-obs');
					$('.js-obs').val(obs);
				});

				$('.js-sel-regiao').change(function(){
					let id_regiao = $(this).val();
					let regiao = $(this).find('option:selected').attr('data-regiao');

					$(`.js-regiao`).hide();
					$(`.js-regiao-${id_regiao}`).show();
					$(`.js-regiao-${id_regiao}`).find('select').chosen({hide_results_on_select:false,allow_single_deselect:true});
					$(`.js-regiao-${id_regiao}-select`).val('').trigger('chosen:updated')
				})

				$('.js-btn-add').click(function(){
					let id_exame = $('select.js-sel-id_exame').val();
					let titulo = $('select.js-sel-id_exame option:selected').attr('data-titulo');
					let id_regiao = $('select.js-sel-regiao').val();
					let obs = $('input.js-obs').val();

					let erro = ``;
					if(id_exame.length==0) erro='Selecione o tipo de exame';
					else if(id_regiao.length==0) erro='Seleciona a região';
					else if(id_regiao>=2 && $(`.js-regiao-${id_regiao}-select`).val().length==0) erro=`Preencha a Região`;

					if(erro.length==0) {

						let opcao = ``;
						id_opcao = 0;

						if(id_regiao>=2) {
							id_opcao = $(`.js-regiao-${id_regiao}-select`).val();
							
							$(`.js-regiao-${id_regiao}-select option:selected`).each(function(ind,el){
								if($(el).val()) {
									opcao+=`${$(el).text()}, `;
								}
							});
							opcao = opcao.substr(0,opcao.length-2);
						} 


						let dt = new Date();
						let dia = dt.getMonth();
						let mes = dt.getDate();
						let status = `aguardando`;
						mes++
						mes=mes<=9?`0${mes}`:mes;
						dia=dia<=9?`0${dia}`:dia;
						let data = `${dia}/${mes}/${dt.getFullYear()} ${dt.getHours()}:${dt.getMinutes()}`;

						let item = { id_exame, 
										titulo,
										id_regiao,
										id_opcao,
										opcao,
										obs,
										autor, 
										status,
										id_usuario, 
										data
									}
						exames.push(item);
						examesListar();

						$('select.js-sel-id_exame').val('').trigger('chosen:updated');
						$('select.js-sel-regiao').val('').trigger('chosen:updated').trigger('change');
						$('input.js-obs').val('');
					
					} else {
						swal({title: "Erro!", text: erro, html:true, type:"error", confirmButtonColor: "#424242"});
					}

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
								exames.splice(index,1);
								examesListar();	
							} else {   
								swal.close();   
							}
						}
						);

					
				});

				examesListar();
			});
		</script>

		
		<section class="grid">
			<div class="box">

				<?php
				if(empty($evolucao)) {
					$exibirEvolucaoNav=1;
					require_once("includes/evolucaoMenu.php");
				} else {
				?>
				<div class="filter">
					<div class="filter-group">
						<div class="filter-button">
							<a href="pg_contatos_pacientes_evolucao.php?id_paciente=<?php echo $paciente->id;?>"><i class="iconify" data-icon="bx-bx-left-arrow-alt"></i></a>
						</div>
					</div>
					<div class="filter-group filter-group_right">
						<div class="filter-button">
							<a href="javascript:;"><i class="iconify" data-icon="bx-bx-trash"></i></a>
							<a href="javascript:;"><i class="iconify" data-icon="bx-bx-printer"></i></a>
							<a href="javascript:;" class="azul js-btn-salvar"><i class="iconify" data-icon="bx-bx-check"></i><span>salvar</span></a>
						</div>
					</div>
				</div>
				<?php
				}
				?>


				<section class="js-evolucao-adicionar" id="evolucao-pedidos-de-exames">
						
					<form class="form formulario-validacao" method="post">

						<textarea name="exames" class="js-agenda-examesJSON" style="display:none;"></textarea>
						<input type="hidden" name="acao" value="wlib" />
						<input type="hidden" name="id_evolucao" value="<?php echo is_object($evolucao)?$evolucao->id:0;?>" />

						<div class="grid grid_3">
							<fieldset>
								<legend><span class="badge">1</span>Cabeçalho do exame</legend>
								
								<dl>
									<dt>Data do Pedido</dt>
									<dd><input type="text" name="data_pedido" class="datecalendar data obg" value="<?php echo is_object($evolucao)?date('d/m/Y',strtotime($evolucao->data_pedido)):date('d/m/Y');?>" /></dd>
								</dl>
								<dl>
									<dt>Clínica Radiológica</dt>
									<dd>
										<select name="id_clinica" class="chosen" data-placeholder="Selecione...">
											<option value=""></option>
											<?php
											foreach($_clinicas as $v) {
												echo '<option value="'.$v->id.'"'.((is_object($evolucao) and $evolucao->id_clinica==$v->id)?' selected':'').'>'.utf8_encode($v->tipo_pessoa=="PJ"?$v->razao_social:$v->nome).'</option>';
											}
											?>
										</select>
									</dd>
								</dl>
								<dl>
									<dt>Cirurgião Dentista</dt>
									<dd>
										<select name="id_profissional" class="chosen obg" data-placeholder="Selecione...">
											<option value=""></option>
											<?php
											foreach($_profissionais as $v) {
												echo '<option value="'.$v->id.'"'.((is_object($evolucao) and $evolucao->id_profissional==$v->id)?' selected':'').'>'.utf8_encode($v->nome).'</option>';
											}
											?>
										</select>
									</dd>
								</dl>
								
							</fieldset>

							<fieldset style="grid-column:span 2">
								<legend><span class="badge">2</span>Selecione os exames</legend>
								<div class="colunas3">
									<dl class="dl2">
										<dt>Tipo de Exame</dt>
										<dd>
											<select class="chosen js-sel-id_exame" data-placeholder="Selecione...">
												<option value=""></option>
												<?php
												foreach($_tiposExames as $v) {
													echo '<option value="'.$v->id.'" data-titulo="'.utf8_encode($v->titulo).'" data-obs="'.utf8_encode($v->obs).'">'.utf8_encode($v->titulo).'</option>';
												}
												?>
											</select>
										</dd>
									</dl>
									<dl>
										<dt>Região</dt>
										<dd>
											<select class="chosen js-sel-regiao" data-placeholder="Selecione...">
												<option value=""></option>
												<?php
												foreach($_regioes as $v) {
													echo '<option value="'.$v->id.'" data-regiao="'.utf8_encode($v->titulo).'">'.utf8_encode($v->titulo).'</option>';
												}
												?>
											</select>
										</dd>
									</dl>
								</div>
									
									
								<dl class="js-regiao-2 js-regiao dl2" style="display: none;">
									<dt>Arcada(s)</dt>
									<dd>
										<select class="js-regiao-2-select" multiple>
											<option value=""></option>
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
								<dl class="js-regiao-3 js-regiao dl2" style="display: none">
									<dt>Quadrante(s)</dt>
									<dd>
										<select class="js-regiao-3-select" multiple>
											<option value=""></option>
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
								<dl class="js-regiao-4 js-regiao dl2" style="display: none">
									<dt>Dente(s)</dt>
									<dd>
										<select class="js-regiao-4-select" multiple>
											<option value=""></option>
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
									<dt>Observação</dt>
									<dd><input type="text" class="js-obs noupper" /><button type="button" class="button js-btn-add">adicionar</button></dd>
								</dl>
								


								<textarea name="exames" style="display: none"></textarea>

								<div class="reg js-div-exames" style="margin-top:2rem;"></div>

								<?php /*<div class="reg" style="margin-top:2rem;">
									<div class="reg-group">
										<div class="reg-color" style="background-color:palegreen"></div>
										<div class="reg-data">
											<h1>Raio X - 21, 22, 23</h1>
											<p>Enviar por email</p>
										</div>
										<div class="reg-icon">
											<a href=""><i class="iconify" data-icon="bx-bx-trash"></i></a>
										</div>
									</div>
								</div>	*/?>							
							</fieldset>
						</div>
						<?php /*<fieldset>
							<legend><span class="badge">4</span> Pré-visualize e edite se necessário</legend>
							<script>
								$(function(){
									var fck_texto = CKEDITOR.replace('texto2',{
						    							filebrowserUploadUrl: '/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Files',
															height: '350',
															width: '100%',
															language: 'pt-br'
														});
									CKFinder.setupCKEditor(fck_texto);
								});
							</script>
							<textarea name="pedido" id="texto2" class="noupper" style="height:400px;">
								<?php
								if(is_object($evolucaoPedido)) {
									echo utf8_encode($evolucaoPedido->pedido);

								} else {
								?>
								<h1 style="text-align:center;">Pedido de Exame</h1>
								<p>Atesto para os devidos fins que <b><?php echo utf8_encode($paciente->nome);?></b> estará dispensado das atividades trabalhistas durante o período de {DIAS ATESTADO} dias a partir da data de {DATA ATESTADO}</p>
								<?php
								}
								?>
							</textarea>
						</fieldset>*/?>
					</form>

				</section>
				

			</div>				
		</section>
			
	</section>
	<section id="cal-popup" class="cal-popup cal-popup_paciente cal-popup_top cal-popup_alt" style="left:703px; top:338px; margin-left:303px;display: none">
		<a href="javascript:;" class="cal-popup__fechar js-btn-fechar"><i class="iconify" data-icon="mdi-close"></i></a>
		<section class="paciente-info">
			<header class="paciente-info-header">
				<section class="paciente-info-header__inner1">
					<h1 class="js-titulo"></h1>
					<p style="color:var(--cinza4);"><span style="color:var(--cinza4);" class="js-opcao"></span></p>
					
				</section>
			</header>
			<input type="hidden" class="js-index" />

			<div class="abasPopover">
				<a href="javascript:;" onclick="$(this).parent().parent().find('a').removeClass('active');$(this).parent().parent().find('.js-grid').hide();$(this).parent().parent().find('.js-grid-info').show();$(this).addClass('active');" class="active">Informações</a>
				<?php /*<a href="javascript:;" onclick="$(this).parent().parent().find('a').removeClass('active');$(this).parent().parent().find('.js-grid').hide();$(this).parent().parent().find('.js-grid-valor').show();$(this).addClass('active');">Valor</a>*/?>
				<a href="javascript:;" onclick="$(this).parent().parent().find('a').removeClass('active');$(this).parent().parent().find('.js-grid').hide();$(this).parent().parent().find('.js-grid-obs').show();$(this).addClass('active');">Observações</a>
			</div>

			<div class="paciente-info-grid js-grid js-grid-info" style="font-size: 12px;">		
				
			
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
			</div>
			<div class="paciente-info-opcoes">
				<?php echo $selectSituacaoOptions;?>
				<a href="javascript:;" class="js-btn-excluir button button__sec">excluir</a>
			</div>
		</section>
	</section>
		
<?php
include "includes/footer.php";
?>