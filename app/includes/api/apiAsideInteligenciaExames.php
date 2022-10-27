<?php
	if(isset($_POST['ajax'])) {



		$dir="../../";
		require_once("../../lib/conf.php");
		require_once("../../usuarios/checa.php");

		$rtn=array();

		if($_POST['ajax']=="pedidoDeExameConsulta") {
		

			$pedidoDeExame=$evolucao=$clinica=$profissional=$colaborador='';
			$_exames=array();
			if(isset($_POST['id_evolucao_pedidodeexame']) and is_numeric($_POST['id_evolucao_pedidodeexame'])) {
				$sql->consult($_p."pacientes_evolucoes_pedidosdeexames","*","where id=".$_POST['id_evolucao_pedidodeexame']." and lixo=0");
				if($sql->rows) {
					$pedidoDeExame=mysqli_fetch_object($sql->mysqry);

					$sql->consult($_p."pacientes_evolucoes","*","where id=$pedidoDeExame->id_evolucao and lixo=0");
					if($sql->rows) {
						$evolucao=mysqli_fetch_object($sql->mysqry);

						$sql->consult($_p."parametros_fornecedores","*","where id=$evolucao->id_clinica and lixo=0");
						if($sql->rows) {
							$clinica=mysqli_fetch_object($sql->mysqry);
						}

						$sql->consult($_p."colaboradores","id,nome","where id=$evolucao->id_profissional and lixo=0");
						if($sql->rows) {
							$profissional=mysqli_fetch_object($sql->mysqry);
						}

						$sql->consult($_p."colaboradores","id,nome","where id=$evolucao->id_usuario and lixo=0");
						if($sql->rows) {
							$colaborador=mysqli_fetch_object($sql->mysqry);
						}

					}

					$sql->consult($_p."parametros_examedeimagem","*","");
					if($sql->rows) {
						while($x=mysqli_fetch_object($sql->mysqry)) {
							$_exames[$x->id]=$x;
						}
					}

				}
			}

			if(is_object($pedidoDeExame)) {
				if(is_object($evolucao)) {
					if(is_object($evolucao)) {

							$exames=[];
							$sql->consult($_p."pacientes_evolucoes_pedidosdeexames","*","where id_evolucao=$evolucao->id and lixo=0");
							while($x=mysqli_fetch_object($sql->mysqry)) {
								if(isset($_exames[$x->id_exame])) {
									$e=$_exames[$x->id_exame];
								
									$exames[]=array('id_evolucao_pedidodeexame'=>$x->id,
														'titulo'=>trim(encodingToJson($e->titulo)),
														'obs'=>trim(encodingToJson($e->obs)),
														'status'=>$x->status,
														'statusTitulo'=>isset($_selectSituacaoOptions[$x->status])?$_selectSituacaoOptions[$x->status]['titulo']:'-',
														'opcao'=>encodingToJson($x->opcao)
													);
								}
							}


							$pedidodeexame=[];
							$pedidodeexame['data']=date('d/m/Y',strtotime($evolucao->data_pedido));
							$pedidodeexame['clinica']=encodingToJson($clinica->razao_social);
							$pedidodeexame['profissional']=is_object($profissional)?encodingToJson($profissional->nome):'-';
							$pedidodeexame['colaborador']=is_object($colaborador)?encodingToJson($colaborador->nome):'-';
							$pedidodeexame['colaborador']=is_object($colaborador)?encodingToJson($colaborador->nome):'-';
							$pedidodeexame['exames']=$exames;


							$rtn=array('success'=>true,'pedidodeexame'=>$pedidodeexame);

						
					} else {
						$rtn=array('success'=>false,'error'=>'Clínica não encontrada!');
					}
				} else {
					$rtn=array('success'=>false,'error'=>'Evolução não encontrada!');
				}
			} else {
				$rtn=array('success'=>false,'error'=>'Pedido de exame não encontrado!');
			}
		}
		else if($_POST['ajax']=="statusPersistir") {

			$pedidoDeExame='';
			if(isset($_POST['id_evolucao_pedidodeexame']) and is_numeric($_POST['id_evolucao_pedidodeexame'])) {
				$sql->consult($_p."pacientes_evolucoes_pedidosdeexames","*","where id=".$_POST['id_evolucao_pedidodeexame']." and lixo=0");
				if($sql->rows) {
					$pedidoDeExame=mysqli_fetch_object($sql->mysqry);
				}
			}

			$status=(isset($_POST['status']) and isset($_selectSituacaoOptions[$_POST['status']]))?$_POST['status']:'';

			if(is_object($pedidoDeExame)) {

				if(!empty($status)) {

					$vSQL="status='".$status."'";
					$vWHERE="where id=$pedidoDeExame->id";

					$sql->update($_p."pacientes_evolucoes_pedidosdeexames",$vSQL,$vWHERE);
					$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='update',vsql='".addslashes($vSQL)."',vwhere='".addslashes($vWHERE)."',tabela='".$_p."pacientes_evolucoes_pedidosdeexames"."',id_reg='".$pedidoDeExame->id."'");

					$rtn=array('success'=>true);
				}  else {
					$rtn=array('success'=>false,'error'=>'Status não definido!');
				}

			} else {
				$rtn=array('success'=>false,'error'=>'Pedido de exame não encontrada!');
			}
		}
		else if($_POST['ajax']=="pedidosAtualiza") {
			$_clinicas=array();
			$sql->consult($_p."parametros_fornecedores","*","");
			while($x=mysqli_fetch_object($sql->mysqry)) {
				$_clinicas[$x->id]=$x;
			}


			$_pedidosDeExames=array('concluido'=>[],'aguardando'=>[],'naoRealizado'=>[]);
			$_pacientes=$_evolucoes=$_exames=array();


			$evolucoesIds=$pacientesIds=$examesIds=[];
			$sql->consult($_p."pacientes_evolucoes","*","where id_tipo=6 and lixo=0 order by data_pedido desc");
			while($x=mysqli_fetch_object($sql->mysqry)) {
				$_evolucoes[$x->id]=$x;
				$evolucoesIds[]=$x->id;
				$pacientesIds[]=$x->id_paciente;
			}

			if(count($evolucoesIds)>0) {
				$sql->consult($_p."pacientes_evolucoes_pedidosdeexames","*","where id_evolucao IN (".implode(",",$evolucoesIds).") and lixo=0 order by id desc");
				if($sql->rows) { 
					while($x=mysqli_fetch_object($sql->mysqry)) {
						$_pedidosDeExames[$x->status][$x->id_evolucao][]=$x;
						$examesIds[]=$x->id_exame;
					}
				}

				$sql->consult($_p."pacientes","id,nome","where id IN (".implode(",",$pacientesIds).") and lixo=0");
				if($sql->rows) {
					while($x=mysqli_fetch_object($sql->mysqry)) {
						$_pacientes[$x->id]=$x;
					}
				}

				$sql->consult($_p."parametros_examedeimagem","*","where id IN (".implode(",",$examesIds).")");
				while($x=mysqli_fetch_object($sql->mysqry)) {
					$_exames[$x->id]=$x;
				}
			}


			// monta arrays
				$pedidos=array('aguardando'=>[],'concluido'=>[],'naoRealizado'=>[]);
		
				if(isset($_pedidosDeExames['aguardando'])) {
					foreach($_pedidosDeExames['aguardando'] as $id_evolucao=>$x) {
						if(isset($_evolucoes[$id_evolucao])) {
							$evolucao=$_evolucoes[$id_evolucao];


							$dif = strtotime(date('Y-m-d'))-strtotime($evolucao->data_pedido);
							$dif = floor($dif/(60 * 60 * 24));
							$alertaMaisDe8Dias=($dif>=8)?1:0;

							if(isset($_pacientes[$evolucao->id_paciente])) {
								$paciente=$_pacientes[$evolucao->id_paciente];
								$clinica = isset($_clinicas[$evolucao->id_clinica]) ? encodingToJson($_clinicas[$evolucao->id_clinica]->razao_social) : '';

								$pedidos['aguardando'][]=array('id_evolucao'=>$evolucao->id,
																'id_evolucao_pedidodeexame'=>$x[0]->id,
																'data'=>date('d/m/Y',strtotime($evolucao->data_pedido)),
																'paciente'=>encodingToJson($paciente->nome),
																'alerta'=>$alertaMaisDe8Dias,
																'exames'=>count($x),
																'clinica'=>$clinica);
							}
						}
					}
				}	




				if(isset($_pedidosDeExames['concluido'])) {
					foreach($_pedidosDeExames['concluido'] as $id_evolucao=>$x) {
						if(isset($_evolucoes[$id_evolucao])) {
							$evolucao=$_evolucoes[$id_evolucao];
							if(isset($_pacientes[$evolucao->id_paciente])) {
								$paciente=$_pacientes[$evolucao->id_paciente];
								$clinica = isset($_clinicas[$evolucao->id_clinica]) ? encodingToJson($_clinicas[$evolucao->id_clinica]->razao_social) : '';


								$pedidos['concluido'][]=array('id_evolucao'=>$evolucao->id,
																'id_evolucao_pedidodeexame'=>$x[0]->id,
																'data'=>date('d/m/Y',strtotime($evolucao->data_pedido)),
																'paciente'=>encodingToJson($paciente->nome),
																'exames'=>count($x),
																'clinica'=>$clinica);
							}
						}
					}
				}

				if(isset($_pedidosDeExames['naoRealizado'])) {
					foreach($_pedidosDeExames['naoRealizado'] as $id_evolucao=>$x) {
						if(isset($_evolucoes[$id_evolucao])) {
							$evolucao=$_evolucoes[$id_evolucao];
							if(isset($_pacientes[$evolucao->id_paciente])) {
								$paciente=$_pacientes[$evolucao->id_paciente];
								$clinica = isset($_clinicas[$evolucao->id_clinica]) ? encodingToJson($_clinicas[$evolucao->id_clinica]->razao_social) : '';
								$pedidos['naoRealizado'][]=array('id_evolucao'=>$evolucao->id,
																'id_evolucao_pedidodeexame'=>$x[0]->id,
																'data'=>date('d/m/Y',strtotime($evolucao->data_pedido)),
																'paciente'=>encodingToJson($paciente->nome),
																'exames'=>count($x),
																'clinica'=>$clinica);
							}
						}
					}
				}
			


			$rtn=array('success'=>true,
						'pedidosAguardando'=>$pedidos['aguardando'],
						'pedidosConcluido'=>$pedidos['concluido'],
					 	'pedidosNaoRealizado'=>$pedidos['naoRealizado']);
		}

		header("Content-type: application/json");
		echo json_encode($rtn);
		die();
	}
	$selectSituacaoOptions='<select class="js-select-status">';
	foreach($_selectSituacaoOptions as $key=>$value) {
		$selectSituacaoOptions.='<option value="'.$key.'">'.$value['titulo'].'</option>';
	}
	$selectSituacaoOptions.='</select>';
?>
<script type="text/javascript">

	var id_usuario = '<?php echo $usr->id;?>';
	var autor = '<?php echo utf8_encode($usr->nome);?>';
	var pedidodeexame = {};

	// abre aside de Exames
	const asideInteligenciaExames = () => {

		$('.js-asideInteligenciaExames-exame').val(pedidodeexame.exame?pedidodeexame.exame:'-');
		$('.js-asideInteligenciaExames-clinica').val(pedidodeexame.clinica?pedidodeexame.clinica:'-');
		$('.js-asideInteligenciaExames-data').val(pedidodeexame.data?pedidodeexame.data:'-');
		$('.js-asideInteligenciaExames-colaborador').val(pedidodeexame.colaborador?pedidodeexame.colaborador:'-');
		$('.js-asideInteligenciaExames-profissional').val(pedidodeexame.profissional?pedidodeexame.profissional:'-');


		$('.js-asideInteligenciaExames-exames').html('');
		if(pedidodeexame.exames.length>0) {
			pedidodeexame.exames.forEach(x=>{

				cor='';
				if(x.status=='aguardando') cor='var(--laranja)';
				else if(x.status=='naoRealizado') cor='var(--vermelho)';
				else if(x.status=='concluido') cor='var(--verde)';
				$('.js-asideInteligenciaExames-exames').append(`<tr class="js-tr-item" data-id_evolucao_pedidodeexame="${x.id_evolucao_pedidodeexame}">
																	<td class="list1__border" style="color:${cor}"></td>
																	<td class="js-titulo" style="width:66%">${x.titulo}</td>
																	<td>
																		<?php echo $selectSituacaoOptions;?>
																	</td>
																	<td style="text-align:right;width:50px;"><a href="javascript:;" class="button js-tr-item-anexo"><span class="iconify" data-icon="fluent:attach-12-filled" data-inline="true"></span> 0</a></td>
																</tr>`);
				$('.js-asideInteligenciaExames-exames .js-select-status:last').val(x.status);
			})
		}

		$(".aside-inteligencia-exames").fadeIn(100,function() {
			$(".aside-inteligencia-exames .aside__inner1").addClass("active");
		});
	}

	// atualiza informacoes do pedido de exame
	const pedidosDeExameAtualiza = (id_evolucao_pedidodeexame) => {
		let data = `ajax=pedidoDeExameConsulta&id_evolucao_pedidodeexame=${id_evolucao_pedidodeexame}`;
		$.ajax({
			type:"POST",
			data:data,
			url:'includes/api/apiAsideInteligenciaExames.php',
			success:function(rtn) {
				if(rtn.success) {
					pedidodeexame=rtn.pedidodeexame;
					asideInteligenciaExames();
				}
			}
		})
	}

	// atualiza todas solicitacoes de exame
	const pedidosAtualiza = () => {
		let data = `ajax=pedidosAtualiza`;
		$.ajax({
			type:"POST",
			data:data,
			url:'includes/api/apiAsideInteligenciaExames.php',
			success:function(rtn) {
				if(rtn.success) {
					pedidosAguardando = (rtn.pedidosAguardando);
					pedidosConcluido = (rtn.pedidosConcluido);
					pedidosNaoRealizado = (rtn.pedidosNaoRealizado);

					pedidosListar();
				}
			}
		})
	}

	$(function(){

		// atualiza status do exame
		$('.js-asideInteligenciaExames-exames').on('change','.js-select-status',function(){
			let id_evolucao_pedidodeexame = $(this).parent().parent().attr('data-id_evolucao_pedidodeexame');
			let status = $(this).val();
			let data = `ajax=statusPersistir&id_evolucao_pedidodeexame=${id_evolucao_pedidodeexame}&status=${status}`;
			$.ajax({
				type:'POST',
				data:data,
				url:'includes/api/apiAsideInteligenciaExames.php',
				success:function(rtn) {
					if(rtn.success===true) {
						pedidosDeExameAtualiza(id_evolucao_pedidodeexame);
						pedidosAtualiza();
					}
				}
			})
		});

		// clica nos exames do kanban para abrir aside
		$('.kanban-card').on('click','.js-exame-item',function(){
			let id_evolucao_pedidodeexame = $(this).attr('data-id_evolucao_pedidodeexame');
			pedidosDeExameAtualiza(id_evolucao_pedidodeexame);
		});


		// clica no botao de anexo do exame
		$('.js-asideInteligenciaExames-exames').on('click','.js-tr-item-anexo',function(){
			let index = $('.js-asideInteligenciaExames-exames .js-tr-item-anexo').index(this);
			if(pedidodeexame.exames[index]) {
				e=pedidodeexame.exames[index];
				$('.aside-inteligencia-exames-anexos .js-exame').val(e.titulo);
				$('.aside-inteligencia-exames-anexos .js-opcao').val(e.opcao.length==0?'-':e.opcao);
				$('.aside-inteligencia-exames-anexos .js-obs').val(e.obs.length==0?'-':e.obs);
				$('.aside-inteligencia-exames-anexos .js-status').val(e.statusTitulo);
				$(".aside-inteligencia-exames-anexos").fadeIn(100,function() {
					$(".aside-inteligencia-exames-anexos .aside__inner1").addClass("active");
				});
			}
		});

	});
</script>

<section class="aside aside-inteligencia-exames" style="display: none;">
	<div class="aside__inner1" style="width:900px;">
		<header class="aside-header">
			<h1>Pedido de Exame</h1>
			<a href="javascript:;" class="aside-header__fechar aside-close"><i class="iconify" data-icon="fluent:dismiss-24-filled"></i></a>
		</header>

		<form method="post" class="aside-content form js-form-editar-procedimento">
			<input type="hidden" class="js-asidePlanoEditar-index" value="" />
			<section class="filter">
				<div class="filter-group"></div>
				<div class="filter-group">
					<div class="filter-form form">

						<a href="<?php echo $pdf;?>" target="_blank" class="button"><i class="iconify" data-icon="ant-design:file-pdf-outlined"></i></a>
						<a href="javascript:;" class="button js-btn-whatsapp" data-id_evolucao="<?php echo $e->id;?>" data-loading="0"><i class="iconify" data-icon="fa:whatsapp"></i></a>
						<a href="<?php echo $_page."?deleta=".$e->id."&pagina=".((isset($_GET['pagina']) and is_numeric($_GET['pagina']))?$_GET['pagina']:'')."&$url";?>" class="button js-confirmarDeletar"><i class="iconify" data-icon="fluent:delete-24-regular"></i></a>
					</div>								
				</div>
			</section>

			<fieldset>
				<legend>Dados da Evolução</legend>

				<div class="colunas4">

					<dl>
						<dt>Data do Pedido</dt>
						<dd><input type="text" class="js-asideInteligenciaExames-data" disabled /></dd> 
					</dl>
					<dl class="dl3">
						<dt>Cadastrado por</dt>
						<dd><input type="text" class="js-asideInteligenciaExames-colaborador" disabled /></dd> 
					</dl>
				</div>

				<dl>
					<dt>Clínica</dt>
					<dd><input type="text" class="js-asideInteligenciaExames-clinica" disabled /></dd>
				</dl>

				<dl>
					<dt>Profissional</dt>
					<dd><input type="text" class="js-asideInteligenciaExames-profissional" disabled /></dd>
				</dl>	

			</fieldset>


			<fieldset>
				<legend>Exames Solicitados</legend>

				<div class="list1">

					<table class="js-asideInteligenciaExames-exames">
						
					</table>
				</div>
			</fieldset>


		</form>
	</div>
</section>



<section class="aside aside-inteligencia-exames-anexos" style="display: none;">
	<div class="aside__inner1" style="width:750px;">
		<header class="aside-header">
			<h1>Anexos</h1>
			<a href="javascript:;" class="aside-header__fechar aside-close"><i class="iconify" data-icon="fluent:dismiss-24-filled"></i></a>
		</header>

		<form method="post" class="aside-content form js-form-editar-procedimento">
			<input type="hidden" class="js-asidePlanoEditar-index" value="" />
			<section class="filter">
				<div class="filter-group"></div>
				<div class="filter-group">
					<div class="filter-form form">

						
					</div>								
				</div>
			</section>

			<fieldset>
				<legend>Dados do Exame</legend>

			
				<dl>
					<dt>Exame</dt>
					<dd><input type="text" class="js-exame" disabled /></dd>
				</dl>

				<dl>
					<dt>Opções</dt>
					<dd><input type="text" class="js-opcao" disabled /></dd>
				</dl>


				<dl>
					<dt>Obs.:</dt>
					<dd class="js-obs"></dd>
				</dl>
			</fieldset>

			<fieldset>
				<legend>Anexos</legend>

				<div class="colunas4">

					<dl class="dl2">
						<dt>Descrição</dt>
						<dd><input type="text" /></dd>
					</dl>
					<dl>
						<dt>Anexo</dt>
						<dd><input type="file" /></dd>
					</dl>
					<dl>
						<dt>&nbsp;</dt>
						<dd><a href="javascript:;" class="button"><span class="iconify" data-icon="akar-icons:plus"></span></a></dd>
					</dl>
				</div>


				<div class="list1">

					<table class="js-asideInteligenciaExames-exames-anexos">
						
					</table>
				</div>
			</fieldset>


		</form>
	</div>
</section>
