<?php
	if(isset($_GET['ajax'])) {
		if($_GET['ajax']=="medicamentos") {

			require_once("lib/conf.php");
			require_once("usuarios/checa.php");

			$sql = new Mysql();

			$_medicamentos=array();
			$sql->consult($_p."medicamentos","*","where lixo=0 order by titulo");
			while($x=mysqli_fetch_object($sql->mysqry)) {
				$_medicamentos[]=array('id'=>$x->id,
										 'titulo'=>utf8_encode($x->titulo),
										 'quantidade'=>utf8_encode($x->quantidade),
										 'tipo'=>utf8_encode($x->tipo),
										 'posologia'=>utf8_encode($x->posologia),
										 'controleEspecial'=>utf8_encode($x->controleEspecial));
			}

			header("Content-type: json/application");
			echo json_encode($_medicamentos);
		}


		die();
	}

	if(isset($_POST['ajax'])) {
		require_once("lib/conf.php");	
		require_once("usuarios/checa.php");

		$sql = new Mysql();
		$rtn = array();

		if($_POST['ajax']=="persistirMedicamento") {

			$medicamento=(isset($_POST['medicamento']) and !empty($_POST['medicamento']))?$_POST['medicamento']:'';
			$quantidade=(isset($_POST['quantidade']) and is_numeric($_POST['quantidade']))?$_POST['quantidade']:'';
			$tipo=(isset($_POST['tipo']) and !empty($_POST['tipo']))?$_POST['tipo']:'';
			$posologia=(isset($_POST['posologia']) and !empty($_POST['posologia']))?$_POST['posologia']:'';
			if(empty($medicamento)) {
				$rtn=array('success'=>false,'error'=>'Medicamento não definido');
			} else if(empty($quantidade)) {
				$rtn=array('success'=>false,'error'=>'Quantidade não definida');
			} else if(empty($tipo)) {
				$rtn=array('success'=>false,'error'=>'Tipo do Medicamento não definido');
			} else if(empty($posologia)) {
				$rtn=array('success'=>false,'error'=>'Posologia não definida');
			} else {
				$vSQL="titulo='".addslashes(utf8_decode($medicamento))."',
						quantidade='".addslashes(utf8_decode($quantidade))."',
						tipo='".addslashes(utf8_decode($tipo))."',
						posologia='".addslashes(utf8_decode($posologia))."',
						controleespecial='".((isset($_POST['controleespecial']) and $_POST['controleespecial']==1)?1:0)."',
						lixo=0";

				$sql->add($_p."medicamentos",$vSQL);
				$id_medicamento=$sql->ulid;
				$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='insert',vsql='".addslashes($vSQL)."',tabela='".$_p."medicamentos',id_reg='".$id_medicamento."'");

				$rtn=array('success'=>true,
							'medicamento'=>$medicamento,
							'quantidade'=>$quantidade,
							'tipo'=>$tipo,
							'posologia'=>$posologia);
			}
		}

		header("Content-type: application/json");
		echo json_encode($rtn);
		die();

	}
	include "includes/header.php";
	include "includes/nav.php";

	$_table=$_p."pacientes";
	$_page=basename($_SERVER['PHP_SELF']);

	$paciente=$cnt='';
	if(isset($_GET['id_paciente']) and is_numeric($_GET['id_paciente'])) {
		$sql->consult($_p."pacientes","*","where id='".$_GET['id_paciente']."'");
		if($sql->rows) {
			$paciente=mysqli_fetch_object($sql->mysqry);
			$cnt=$paciente;
		}
	}

	$_profissionais=array();
	$sql->consult($_p."colaboradores","*","where lixo=0 order by nome asc");//"where unidades like '%,$unidade->id,%' and lixo=0 order by nome asc");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_profissionais[$x->id]=$x;
	}

	$_medicamentos=array();
	$sql->consult($_p."medicamentos","*","where lixo=0 order by titulo");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_medicamentos[]=array('id'=>$x->id,
								 'titulo'=>utf8_encode($x->titulo),
								 'quantidade'=>utf8_encode($x->quantidade),
								 'tipo'=>utf8_encode($x->tipo),
								 'posologia'=>utf8_encode($x->posologia));
	}

	$_medicamentosTipos=array('ampola'=>'Ampola(s)',
							 'caixa'=>'Caixa(s)',
							 'comprimido'=>'Comprimido(s)',
							 'frasco'=>'Frasco(s)',
							 'pacote'=>'Pacote(s)',
							 'tubo'=>'Tubo(s)',
							 'capsula'=>'Capsula(s)');

	$evolucao='';
	$evolucaoReceita=array();
	if(isset($_GET['edita']) and is_numeric($_GET['edita'])) {	
		$sql->consult($_p."pacientes_evolucoes","*","where id='".$_GET['edita']."' and id_tipo=7");
		if($sql->rows) {
			$evolucao=mysqli_fetch_object($sql->mysqry);

			$sql->consult($_p."pacientes_evolucoes_receitas","*","where id_evolucao=$evolucao->id and lixo=0");
			
			if($sql->rows) {
				while($x=mysqli_fetch_object($sql->mysqry)) {
					$evolucaoReceita[]=array('id_evolucao_receita'=>$x->id,
											'medicamento'=>utf8_encode($x->medicamento),
											'tipo'=>$x->tipo,
											'tipoTitulo'=>isset($_medicamentosTipos[$x->tipo])?$_medicamentosTipos[$x->tipo]:$x->tipo,
											'quantidade'=>utf8_encode($x->quantidade),
											'posologia'=>utf8_encode($x->posologia),
											'id'=>utf8_encode($x->id_medicamento),
											'controleespecial'=>utf8_encode($x->controleespecial));;
				}

 			} 
		} else {
			$jsc->jAlert("Receita não encontrada!","erro","document.location.href='pg_contatos_pacientes_evolucao.php?id_paciente=".$paciente->id."'");
			die();
		}
	}

	if(isset($_POST['acao'])) {

		if(isset($_POST['receitas']) and !empty($_POST['receitas'])) {

			$receitasJSON = json_decode($_POST['receitas']);



			if(empty($erro)) {

				if(count($receitasJSON)>0) {

					if(is_object($evolucao)) {
						$sql->update($_p."pacientes_evolucoes","data_pedido='".addslashes(invDate($_POST['data_pedido']))."',
																id_profissional='".addslashes(utf8_decode($_POST['id_profissional']))."',
																tipo_receita='".addslashes(utf8_decode($_POST['tipo_receita']))."'","where id=$evolucao->id");
						$id_evolucao=$evolucao->id;
					} else {
						// id_tipo = 7 -> receituario
						$sql->consult($_p."pacientes_evolucoes","*","WHERE data > NOW() - INTERVAL 1 MINUTE and 
																								id_paciente=$paciente->id and
																								id_tipo=7 and  
																								id_usuario=$usr->id");	
						if($sql->rows) {
							$e=mysqli_fetch_object($sql->mysqry);
							$sql->update($_p."pacientes_evolucoes","data_pedido='".addslashes(invDate($_POST['data_pedido']))."',
																		id_profissional='".addslashes(utf8_decode($_POST['id_profissional']))."',
																		tipo_receita='".addslashes(utf8_decode($_POST['tipo_receita']))."'","where id=$e->id");
							$id_evolucao=$e->id;
						} else {
							$sql->add($_p."pacientes_evolucoes","data=now(),
																	id_tipo=7,
																	id_paciente=$paciente->id,
																	id_usuario=$usr->id,
																	data_pedido='".addslashes(invDate($_POST['data_pedido']))."',
																	id_profissional='".addslashes(utf8_decode($_POST['id_profissional']))."',
																	tipo_receita='".addslashes(utf8_decode($_POST['tipo_receita']))."'");
																	//obs='".addslashes(utf8_decode($_POST['obs']))."'");
							$id_evolucao=$sql->ulid;
						}
					}

					

					$sql->update($_p."pacientes_evolucoes_receitas","lixo=1","where id_evolucao=$id_evolucao");
					foreach($receitasJSON as $obj) {
						$obj=(object)$obj;


						$vSQLReceita="data=now(),
									id_paciente=$paciente->id,
									id_evolucao=$id_evolucao,
									medicamento='".addslashes(utf8_decode($obj->medicamento))."',
									quantidade='".addslashes(utf8_decode($obj->quantidade))."',
									posologia='".addslashes(utf8_decode($obj->posologia))."',
									tipo='".addslashes(utf8_decode($obj->tipo))."',
									id_medicamento='".addslashes(utf8_decode($obj->id))."',
									controleespecial='".addslashes(utf8_decode($obj->controleespecial))."'";
						$evProc='';
						if(isset($obj->id_evolucao_receita) and is_numeric($obj->id_evolucao_receita)) {
							$sql->consult($_p."pacientes_evolucoes_receitas","*","where id=$obj->id_evolucao_receita and id_paciente=$paciente->id and lixo=0");
							if($sql->rows) {
								$evProc=mysqli_fetch_object($sql->mysqry);
							}
						}

						if(empty($evProc)) {
							$sql->consult($_p."pacientes_evolucoes_receitas","*","WHERE data > NOW() - INTERVAL 1 MINUTE and 
																								id_paciente=$paciente->id and 
																								id_evolucao=$id_evolucao and 
																								medicamento='".addslashes($obj->medicamento)."'");	
							if($sql->rows) {
								$x=mysqli_fetch_object($sql->mysqry);
								$sql->update($_p."pacientes_evolucoes_receitas",$vSQLReceita,"where id=$x->id");
							} else {
								$sql->add($_p."pacientes_evolucoes_receitas",$vSQLReceita);
							}
						} else {
							$sql->update($_p."pacientes_evolucoes_receitas",$vSQLReceita,"where id=$evProc->id");
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
		$exibirEvolucaoNav=1;
		require_once("includes/abaPaciente.php");
		?>

		<script type="text/javascript">
			$(function(){
				$('input[name=tipo]').click(function(){
					let tipo = $(this).val();

					$(`.js-box`).hide();
					$(`.js-box-${tipo}`).show();
				})
				$('.js-btn-salvar').click(function(){
					$('form').submit();
				});

				var options = {
						<?php /*//data: <?php echo json_encode($_medicamentos);?>,*/?>
						url:function() {
							return "?ajax=medicamentos";
						},
						getValue: "titulo",
						list: {
							match: {enabled: true},
							onChooseEvent: function (){
								let obj = $(".js-input-medicamento").getSelectedItemData();

								$('.js-input-quantidade').val($(".js-input-medicamento").getSelectedItemData().quantidade);
								$('.js-input-tipo').val($(".js-input-medicamento").getSelectedItemData().tipo);
								$('.js-input-posologia').val($(".js-input-medicamento").getSelectedItemData().posologia);
								$('.js-input-id').val($(".js-input-medicamento").getSelectedItemData().id);
								$('.js-input-controleespecial').val($(".js-input-medicamento").getSelectedItemData().controleespecial);
							}
						},
						  template: {
								type: "custom",
								method: function(value, item) {
									return item.titulo
								}
							}
					};
				$('.js-input-medicamento').easyAutocomplete(options);

			});

		</script>

		
		<section class="grid">
			<div class="box">

				<?php
				if(empty($evolucao)) {
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
							<a href="impressao/receituario.php?id=<?php echo md5($evolucao->id);?>" target="_blank"><i class="iconify" data-icon="bx-bx-printer"></i></a>
							<a href="javascript:;" class="azul js-btn-salvar"><i class="iconify" data-icon="bx-bx-check"></i><span>salvar</span></a>
						</div>
					</div>
				</div>
				<?php
				}
				?>
				<script type="text/javascript">
				

					var receitas = JSON.parse(jsonEscape(`<?php echo json_encode($evolucaoReceita);?>`));

					var cardHTML = `<div class="reg-group js-receita">
									<div class="reg-color" style="background-color:palegreen"></div>
									<div class="reg-data">
										<h1 class="js-titulo"></h1>
										<p class="js-posologia"></p>
									</div>
									<div class="reg-icon">
										<a href="javascript:;" class="js-btn-excluir"><i class="iconify" data-icon="bx-bx-trash"></i></a>
									</div>
								</div>`;

					var autor = `<?php echo utf8_encode($usr->nome);?>`;
					var id_usuario = `<?php echo utf8_encode($usr->id);?>`;

					const receitasListar = () => {

						$('.js-receita').remove();

						receitas.forEach(x=>{
							$('.js-div-receitas').append(cardHTML);

							let cor = `#CCC`;


							$('.js-receita .reg-color:last').css('background-color',cor);
							$('.js-receita .js-titulo:last').html(`${x.medicamento} - ${x.quantidade} ${x.tipoTitulo}`);
							$('.js-receita .js-posologia:last').html(x.posologia);

						});

						$('textarea[name=receitas]').val(JSON.stringify(receitas));

					}
					$(function(){


						$('.js-div-receitas').on('click','.js-btn-excluir',function(){
							let index = $(this).index('.js-div-receitas .js-btn-excluir');
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
										receitas.splice(index,1);
										receitasListar();	
									} else {   
										swal.close();   
									}
								}
							);

							
						});
						$('.js-btn-salvar').click(function(){
							$('form').submit();
						});

						

						$('.js-btn-add').click(function(){
							let medicamento = $('.js-input-medicamento').val();
							let quantidade = $('.js-input-quantidade').val();
							let tipo = $('.js-input-tipo').val();
							let tipoTitulo = $('.js-input-tipo option:selected').text();
							let posologia = $('.js-input-posologia').val();
							let controleespecial = $('.js-input-controleespecial').val();
							let id = $('.js-input-id').val();

							let erro = ``;
							if(medicamento.length==0) erro='Digite o Medicamento!';
							else if(quantidade.length==0) erro='Digite a Quantidade!';
							

							if(erro.length==0) {

								let item = { medicamento, 
												quantidade,
												tipo,
												tipoTitulo,
												posologia,
												id_usuario,
												id,
												controleespecial
											}
								receitas.push(item);
								receitasListar();

								$('.js-input-medicamento').val('');
								$('.js-input-quantidade').val('1');
								$('.js-input-tipo').val('');
								$('.js-input-posologia').val('');
								
							
							} else {
								swal({title: "Erro!", text: erro, html:true, type:"error", confirmButtonColor: "#424242"});
							}

						});

						

						receitasListar();
					});
				</script>
				<section class="js-evolucao-adicionar" id="evolucao-receituario" style="display:;">
					
					<form class="form formulario-validacao" method="post">

						<textarea name="receitas" style="display:none;"></textarea>
						<input type="hidden" name="acao" value="wlib" />
						<input type="hidden" name="id_evolucao" value="<?php echo is_object($evolucao)?$evolucao->id:0;?>" />

						<div class="grid grid_3">
							<fieldset>
								<legend><span class="badge">1</span>Cabeçalho da receita</legend>
								
								<dl>
									<dt>Data e Hora</dt>
									<dd><input type="text" name="data_pedido" class="data daecalendar" value="<?php echo is_object($evolucao)?date('d/m/Y',strtotime($evolucao->data_pedido)):date('d/m/Y');?>" /></dd>
								</dl>
								<dl>
									<dt>Tipo de Uso</dt>
									<dd>
										<select name="tipo_receita" class="obg chosen" data-placeholder="Selecione...">
											<option value=""></option>
											<?php
											foreach($_tiposReceitas as $k=>$v) {
												echo '<option value="'.$k.'"'.((is_object($evolucao) and $evolucao->tipo_receita==$k)?' selected':'').'>'.$v.'</option>';
											}
											?>
										</select>
									</dd>
								</dl>
								<dl>
									<dt>Cirurgião Dentista</dt>
									<dd>
										<select name="id_profissional" class="obg chosen" data-placeholder="Selecione...">
											<option value=""></option>
										<?php
										foreach($_profissionais as $p) {
											echo '<option value="'.$p->id.'"'.((is_object($evolucao) and $evolucao->id_profissional==$p->id)?' selected':'').'>'.utf8_encode($p->nome).'</option>';
										}
										?>
										</select>
									</dd>
								</dl>
								
							</fieldset>

							<fieldset style="grid-column:span 2">
								<legend><span class="badge">2</span>Selecione os medicamentos</legend>
								<div class="colunas5">
									<dl>
										<dt>Medicamento</dt>
										<dd>
											<input type="text" class="js-input-medicamento" />
											<a href="box/boxNovoMedicamento.php" data-fancybox data-type="ajax" class="button button__sec"><i class="iconify" data-icon="bx-bx-plus"></i></a>
											
										</dd>
									</dl>	
									<dl>
										<dt>Quantidade</dt>
										<dd>
											<input type="number" min="1"  value="1" class="js-input-quantidade" />
										</dd>
									</dl>
									<dl>
										<dt>Tipo</dt>
										<dd>
											<select class="js-input-tipo">
												<option value="">-</option>
												<?php
												foreach($_medicamentosTipos as $k=>$v) {
													echo '<option value="'.$k.'"'.($values['tipo']==$k?' selected':'').'>'.$v.'</option>';
												}
												?>
											</select>
										</dd>
									</dl>
								</div>
								<div class="">

									<dl class="dl3">
										<dd>
											<input type="text" class="js-input-controleespecial "  />
											<input type="text" class="js-input-id"  />
										</dd>
									</dl>

									<dl class="dl3">
										<dt>Posologia</dt>
										<dd>
											<input type="text" class="js-input-posologia noupper" placeholder="Tomar 1 comprimido via oral de 8 em 8 horas por 7 dias" />
											<button type="button" class="button js-btn-add">adicionar</button>
										</dd>
									</dl>
								</div>

								<div class="reg js-div-receitas" style="margin-top:2rem;">
									
								</div>						
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
							<textarea name="texto" id="texto2" class="noupper" style="height:400px;">
								<h1 style="text-align:center;">Receituário</h1>
								<p>Atesto para os devidos fins que {NOME PACIENTE} estará dispensado das atividades trabalhistas durante o período de {DIAS ATESTADO} dias a partir da data de {DATA ATESTADO}</p>
							</textarea>
						</fieldset>*/?>
					</form>

				</section>
				

			</div>				
		</section>
			
		</section>
		
<?php
include "includes/footer.php";
?>