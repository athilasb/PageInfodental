<?php
	include "includes/header.php";
	include "includes/nav.php";

	$_table=$_p."pacientes";
	$_page=basename($_SERVER['PHP_SELF']);

	$evolucao='';
	$sql->consult($_p."pacientes_evolucoes_tipos","*","where id=2");
	$evolucao=mysqli_fetch_object($sql->mysqry);

	$_width=400;
	$_height=400;
	$_dir="arqs/pacientes/";

	$_cidades=array();
	$sql->consult($_p."cidades","*","");
	while($x=mysqli_fetch_object($sql->mysqry)) $_cidades[$x->id]=$x;

	$_profissoes=array();
	$sql->consult($_p."parametros_profissoes","*","where lixo=0 order by titulo asc");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_profissoes[$x->id]=$x;
	}


	$_pacienteIndicacoes=array();
	$sql->consult($_p."parametros_indicacoes","*","where lixo=0 order by titulo asc");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_pacienteIndicacoes[$x->id]=$x;
	}

	$_pacienteGrauDeParentesco=array();
	$sql->consult($_p."parametros_grauparentesco","*","where lixo=0 order by titulo asc");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_pacienteGrauDeParentesco[$x->id]=$x;
	}

	$paciente=$cnt='';
	if(isset($_GET['id_paciente']) and is_numeric($_GET['id_paciente'])) {
		$sql->consult($_p."pacientes","*","where id='".$_GET['id_paciente']."'");
		if($sql->rows) {
			$paciente=mysqli_fetch_object($sql->mysqry);
			$cnt=$paciente;
		}
	}

	$_selectSituacaoOptions=array('iniciar'=>array('titulo'=>'NÃO INICIADO','cor'=>'orange'),
											'iniciado'=>array('titulo'=>'EM TRATAMENTO','cor'=>'blue'),
											'finalizado'=>array('titulo'=>'FINALIZADO','cor'=>'green'),
											'cancelado'=>array('titulo'=>'CANCELADO','cor'=>'red'),
											//'cancelado'=>array('titulo'=>'CANCELADO');
										);

	$selectSituacaoOptions='<select class="js-situacao">';
	foreach($_selectSituacaoOptions as $key=>$value) {
		$selectSituacaoOptions.='<option value="'.$key.'">'.$value['titulo'].'</option>';
	}
	$selectSituacaoOptions.='</select>';

	$campos=explode(",","nome,situacao,noem,sexo,foto,rg,rg_orgaoemissor,rg_estado,cpf,data_nascimento,profissao,estado_civil,telefone1,telefone1_whatsapp,telefone1_whatsapp_permissao,telefone2,email,instagram,instagram_naopossui,musica,indicacao_tipo,indicacao,cep,endereco,numero,complemento,bairro,estado,cidade,id_cidade,responsavel_possui,responsavel_nome,responsavel_sexo,responsavel_rg,responsavel_rg_orgaoemissor,responsavel_rg_estado,responsavel_datanascimento,responsavel_estadocivil,responsavel_cpf,responsavel_profissao,responsavel_grauparentesco,preferencia_contato");
	
	foreach($campos as $v) $values[$v]='';
	$values['data']=date('d/m/Y H:i');
	$values['sexo']='M';


	if(is_object($paciente)) {
		$values=$adm->values($campos,$cnt);
		$values['data']=date('d/m/Y H:i',strtotime($cnt->data));
	}

	$_profissionais=array();
	$sql->consult($_p."profissionais","*","where lixo=0 order by nome asc");//"where unidades like '%,$unidade->id,%' and lixo=0 order by nome asc");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_profissionais[$x->id]=$x;
	}

	$selectProfissional='<select class="js-profissional"><option value="">-</option>';
	foreach($_profissionais as $p) {
	
		$aux=explode(" ",$p->nome);
		$aux[0]=strtoupper($aux[0]);
		$iniciais='';
		if($aux[0] =="DR" or $aux[0]=="DR." or $aux[0]=="DRA" or $aux[0]=="DRA.") {
			$iniciais=strtoupper(substr($aux[1],0,1));
			if(isset($aux[2])) $iniciais.=strtoupper(substr($aux[2],0,1));
		} else {
			$iniciais=strtoupper(substr($aux[0],0,1));
			if(isset($aux[1])) $iniciais.=strtoupper(substr($aux[1],0,1));
		}
											
		$selectProfissional.='<option value="'.$p->id.'" data-iniciais="'.$iniciais.'" data-iniciaisCor="'.$p->calendario_cor.'">'.utf8_encode($p->nome).'</option>';
	}
	$selectProfissional.='</select>';


	$tratamentosIds=array();
	$sql->consult($_p."pacientes_tratamentos","*","where id_paciente=$paciente->id and  status='APROVADO' and lixo=0");
	while($x=mysqli_fetch_object($sql->mysqry)) $tratamentosIds[]=$x->id;

	$procedimentosIds=array();
	$_procedimentosAprovados=array();
	$sql->consult($_p."pacientes_tratamentos_procedimentos","*","where id_tratamento IN (".implode(",",$tratamentosIds).") and lixo=0 and situacao='aprovado' and status_evolucao NOT IN ('cancelado','finalizado')");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$procedimentosIds[]=$x->id_procedimento;
		$_procedimentosAprovados[$x->id]=$x;
	}

	$_procedimentos=array();
	$sql->consult($_p."parametros_procedimentos","*","where id IN (".implode(",",$procedimentosIds).")");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_procedimentos[$x->id]=$x;
	}


	if(isset($_POST['acao'])) {

		if(isset($_POST['procedimentos']) and !empty($_POST['procedimentos'])) {

			$procedimentosJSON = json_decode($_POST['procedimentos']);

			$procedimentosEvoluidos=array();
			$erro='';
			foreach($procedimentosJSON as $v) {
				$sql->consult($_p."pacientes_tratamentos_procedimentos","*","where id=$v->id_procedimento");
				if($sql->rows) {
					$x=mysqli_fetch_object($sql->mysqry);
					$procedimentosEvoluidos[]=array('tratamentoProc'=>$x,'evolucaoProc'=>$v);
				} else {
					$erro='Procedimento '.$v->titulo.' não foi encontrado!';
				}
			}

			if(empty($erro)) {

				if(count($procedimentosEvoluidos)>0) {
					foreach($procedimentosEvoluidos as $obj) {
						$obj=(object)$obj;
						$tratamentoProc=$obj->tratamentoProc;
						$evolucaoProc=$obj->evolucaoProc;

						$vSQLProc="data=now(),
									id_tipo=$evolucao->id,
									id_paciente=$paciente->id,
									id_tratamento_procedimento='".addslashes($evolucaoProc->id_procedimento)."',
									id_tratamento='".addslashes($tratamentoProc->id)."',
									id_profissional='".addslashes($evolucaoProc->id_profissional)."',
									status='".addslashes($evolucaoProc->statusEvolucao)."',
									obs='".addslashes($evolucaoProc->obs)."'";

						$sql->consult($_p."pacientes_evolucoes","*","WHERE data > NOW() - INTERVAL 1 MINUTE and 
																							id_paciente=$paciente->id and 
																							id_tipo=$evolucao->id and 
																							id_tratamento='".addslashes($tratamentoProc->id)."'");	
						if($sql->rows) {
							$x=mysqli_fetch_object($sql->mysqry);
							$sql->update($_p."pacientes_evolucoes",$vSQLProc);
						} else {
							$sql->add($_p."pacientes_evolucoes",$vSQLProc);
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
			$jsc->jAlert("Adicione pelo menos um procedimento para adicionar à Evolução","erro","");
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
				console.log(procedimentos[index]);
				/*if(popViewInfos[index].opcao.length>0) {
					$('#cal-popup .js-opcaoEQtd').html(`Região: ${popViewInfos[index].opcao}`);
				} else {
					$('#cal-popup .js-opcaoEQtd').html(`Quantidade: ${popViewInfos[index].quantidade}`);
				}*/

				$('#cal-popup .js-obs').val(procedimentos[index].obs);
				$('#cal-popup .js-titulo').html(procedimentos[index].titulo);
				$('#cal-popup .js-plano').html(procedimentos[index].plano);
				$('#cal-popup .js-opcao').html(procedimentos[index].opcao);
				$('#cal-popup .js-autor').html(procedimentos[index].autor);
				$('#cal-popup .js-autor-data').html(procedimentos[index].data);
				$('#cal-popup .js-profissional').val(procedimentos[index].id_profissional);


				$('#cal-popup .js-situacao').val(procedimentos[index].statusEvolucao);
				$('#cal-popup .js-index').val(index);
			}

			var procedimentos = [];
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
			var autor = `<?php echo utf8_encode($usr->nome);?>`;
			var id_autor = `<?php echo utf8_encode($usr->id);?>`;

			const procedimentosListar = () => {

				$('.js-procedimento').remove();

				procedimentos.forEach(x=>{
					$('.js-div-procedimentos').append(cardHTML);

					let cor = `#CCC`;
					let status = ``;

					if(x.statusEvolucao=='iniciar') {
						status=`Não iniciado`;
						cor=`orange`;
					} else if(x.statusEvolucao=='iniciado') {
						status=`Em Tratamento`;
						cor=`blue`;
					} else if(x.statusEvolucao=='finalizado') {
						status=`Finalizado`;
						cor=`green`;
					} else if(x.statusEvolucao=='cancelado') {
						cor=`red`;
						status=`Cancelado`;
					}


					$('.js-procedimento .reg-color:last').css('background-color',cor);
					$('.js-procedimento .js-titulo:last').html(`<h1>${x.titulo}</h1><p>${x.opcao} - ${x.plano}</p>`);
					$('.js-procedimento .js-status:last').html(`<p>${status}</p>`);
					$('.js-procedimento .reg-user:last span').html(x.profissionalIniciais);
					$('.js-procedimento .reg-user:last span').css('background',x.profissionalCor);
					$(`.js-procedimento:last`).attr('data-usuario',autor);
					$(`.js-procedimento:last`).click(function(){popView(this);});
				});

				$('textarea[name=procedimentos]').val(JSON.stringify(procedimentos));

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

				$('.js-btn-salvar').click(function(){
					$('form').submit();
				})

				$('.js-btn-fechar').click(function(){$('.cal-popup').hide();})

				$('.js-btn-add').click(function(){
					let id_procedimento = $('select.js-sel-procedimento').val();
					let opcao = $('select.js-sel-procedimento option:selected').attr('data-opcao');
					let plano = $('select.js-sel-procedimento option:selected').attr('data-plano');
					let titulo = $('select.js-sel-procedimento option:selected').attr('data-titulo');
					let id_profissional = $('select.js-sel-procedimento option:selected').attr('data-id_profissional');
					let profissionalIniciais = $('select.js-sel-procedimento option:selected').attr('data-profissionalIniciais');
					let profissionalCor = $('select.js-sel-procedimento option:selected').attr('data-profissionalCor');
					let statusEvolucao = $('select.js-sel-procedimento option:selected').attr('data-statusEvolucao');
					let obs = ``;
					let dt = new Date();
					let mes = dt.getMonth();
					mes++
					mes=mes<=9?`0${mes}`:mes;
					let data = `${dt.getDate()}/${mes}/${dt.getFullYear()} ${dt.getHours()}:${dt.getMinutes()}`;

					if(id_procedimento.length>0) {
						let item = { id_procedimento, 
										opcao, 
										plano, 
										titulo, 
										profissionalCor, 
										profissionalIniciais, 
										statusEvolucao, 
										autor, 
										id_autor, 
										data, 
										obs,
										id_profissional

									}
						procedimentos.push(item);
						procedimentosListar();

						$('select.js-sel-procedimento').val('').trigger('chosen:updated');
					} else {
						swal({title: "Erro!", text: 'Selecione o procedimento que deseja adicionar', html:true, type:"error", confirmButtonColor: "#424242"});
					}

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
					procedimentos[index].statusEvolucao=$(this).val();
					procedimentos[index].statusEvolucao=$(this).text();
					procedimentosListar();
				});

				$('#cal-popup').on('change','.js-profissional',function(){
					let index = $('#cal-popup .js-index').val();
					procedimentos[index].id_profissional=$(this).val();
					procedimentos[index].profissionalIniciais=$(this).find('option:selected').attr('data-iniciais');
					procedimentos[index].profissionalCor=$(this).find('option:selected').attr('data-iniciaisCor');
					procedimentosListar();
				})


			});
		</script>


		
		<section class="grid">
			<div class="box">

				<?php
				require_once("includes/evolucaoMenu.php");
				?>

				<section class="js-evolucao-adicionar" id="evolucao-procedimentos-aprovados">
						
					<form class="form js-form-evolucao" method="post">
						<input type="hidden" name="acao" value="wlib" />
						<div class="grid grid_3">

							<fieldset style="grid-column:span 2">
								<legend><span class="badge">2</span> Selecione o procedimento</legend>

								<div class="colunas2">
									<dl>
										<dd>
											<select name="" class="chosen js-sel-procedimento" data-placeholder="Selecione o procedimento...">
												<option value=""></option>
												<?php
												foreach($_procedimentosAprovados as $v) {
													if(isset($_procedimentos[$v->id_procedimento])) {
														$procedimento=$_procedimentos[$v->id_procedimento];
														$profissionalIniciais='';
														$profissionalCor='#ccc';
														if(isset($_profissionais[$v->id_profissional])) {
															$p=$_profissionais[$v->id_profissional];
															$profissionalIniciais=$p->calendario_iniciais;
															$profissionalCor=$p->calendario_cor;

														}
														echo '<option value="'.$v->id.'" data-opcao="'.utf8_encode($v->opcao).'" data-plano="'.utf8_encode($v->plano).'" data-profissionalCor="'.$profissionalCor.'" data-id_profissional="'.$v->id_profissional.'" data-profissionalIniciais="'.$profissionalIniciais.'"  data-statusEvolucao="'.$v->status_evolucao.'" data-titulo="'.utf8_encode($procedimento->titulo).'">'.utf8_encode($procedimento->titulo).' - '.utf8_encode($v->opcao).'</option>';
													}
												}
												?>
											</select>
										</dd>
									</dl>
									<dl>
										<dd><button type="button" class="button js-btn-add">Adicionar</button></dd>
									</dl>
								</div>

								<textarea name="procedimentos" style="display: none"></textarea>

								<div class="reg js-div-procedimentos" style="margin-top:2rem;">

									

								</div>

							</fieldset>

							<fieldset>
								<legend><span class="badge">3</span> Preencha o histórico</legend>

								<dl style="height:100%;">
									<dd style="height:100%;"><textarea name="obs" style="height:100%;" class="noupper"></textarea></dd>
								</dl>
							</fieldset>


						</div>
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
					<p style="color:var(--cinza4);"><span style="color:var(--cinza4);" class="js-opcao"></span> - <span class="js-plano"></span> </p>
					
				</section>
			</header>
			<input type="hidden" class="js-index" />

			<div class="abasPopover">
				<a href="javascript:;" onclick="$(this).parent().parent().find('a').removeClass('active');$(this).parent().parent().find('.js-grid').hide();$(this).parent().parent().find('.js-grid-info').show();$(this).addClass('active');" class="active">Informações</a>
				<?php /*<a href="javascript:;" onclick="$(this).parent().parent().find('a').removeClass('active');$(this).parent().parent().find('.js-grid').hide();$(this).parent().parent().find('.js-grid-valor').show();$(this).addClass('active');">Valor</a>*/?>
				<a href="javascript:;" onclick="$(this).parent().parent().find('a').removeClass('active');$(this).parent().parent().find('.js-grid').hide();$(this).parent().parent().find('.js-grid-obs').show();$(this).addClass('active');">Observações</a>
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
			</div>
			<div class="paciente-info-opcoes">
				<?php echo $selectSituacaoOptions;?>
				<a href="javascript:;" target="_blank" class="js-btn-excluir button button__sec">excluir</a>
			</div>
		</section>
	</section>
		
<?php
include "includes/footer.php";
?>