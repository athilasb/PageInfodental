<?php
	$diasExtenso=array('(domingo)','(segunda-feira)','(terça-feira)','(quarta-feria)','(quinta-feira)','(sexta-feira)','(sábado)');

	$title="";
	include "includes/header.php";
	include "includes/nav.php";

	if($usr->tipo!="admin" and !in_array("financeiro",$_usuariosPermissoes)) {
		$jsc->jAlert("Você não tem permissão para acessar esta área!","erro","document.location.href='dashboard.php'");
		die();
	}

	$values=$adm->get($_GET);


	$pacientesIds=array(-1);
	$traatmentosIds=array(-1);

	$totais=array('promessa'=>0,
					'inadimplentes'=>0,
					'receber'=>0,
					'pagar'=>0,
					'vencidas'=>0);

	// promessa de pagamento
	$regs=array();
	$pagamentosIds=array(0);
	$sql->consult($_p."pacientes_tratamentos_pagamentos","*","WHERE data_vencimento>=current_date() and pago=0 and lixo=0");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$pacientesIds[$x->id_paciente]=$x->id_paciente;
		$tratamentosIds[$x->id_tratamento]=$x->id_tratamento;
		$regs[]=$x;
		$pagamentosIds[]=$x->id;
	}


	// inadimplentes
	$regsIn=array();
	$sql->consult($_p."pacientes_tratamentos_pagamentos","*","WHERE data_vencimento<'".date('Y-m-d')."' and pago=0 and lixo=0");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$pacientesIds[$x->id_paciente]=$x->id_paciente;
		$tratamentosIds[$x->id_tratamento]=$x->id_tratamento;
		$regsIn[]=$x;
	}

	// adimplentes
	$regsAdi=array();
	$sql->consult($_p."pacientes_tratamentos_pagamentos","*","WHERE pago=1 and lixo=0");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$pacientesIds[$x->id_paciente]=$x->id_paciente;
		$tratamentosIds[$x->id_tratamento]=$x->id_tratamento;
		$regsAdi[]=$x;
	}

	// baixas
	$_baixas=array();
	$sql->consult($_p."pacientes_tratamentos_pagamentos_baixas","*","where id_pagamento IN (".implode(",",$pagamentosIds).") and lixo=0");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		if(!isset($_baixas[$x->id_pagamento])) {
			$_baixas[$x->id_pagamento]=array('valor'=>0,'baixas'=>array());
		}
		$_baixas[$x->id_pagamento]['valor']+=$x->valor;
		$_baixas[$x->id_pagamento]['baixas'][]=$x;
	}

	$_tratamentos=array(); 
	$sql->consult($_p."pacientes_tratamentos","id,titulo","where id IN (".implode(",",$tratamentosIds).")");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_tratamentos[$x->id]=$x;
	}


	$fornecedoresIds=$colaboradoresIds=array(0);
	$regsContas=array();
	$sql->consult($_p."financeiro_fluxo","*","WHERE valor<0 and data_vencimento<='".date('Y-m-d')."' and lixo=0");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$regsContas[]=$x;
		if($x->credor_pagante=="fornecedor") $fornecedoresIds[]=$x->id_fornecedor;
		else if($x->credor_pagante=="paciente") $pacientesIds[]=$x->id_paciente;
		else if($x->credor_pagante=="colaborador") $colaboradoresIds[]=$x->id_colaborador;
	}

	$_fornecedores=array();
	$sql->consult($_p."parametros_fornecedores","id,IF(tipo_pessoa='pj',razao_social,nome) as titulo","where id IN (".implode(",",$fornecedoresIds).")");
	while($x=mysqli_fetch_object($sql->mysqry)) $_fornecedores[$x->id]=$x;

	$_colaboradores=array();
	$sql->consult($_p."colaboradores","id,nome","where id IN (".implode(",",$colaboradoresIds).")");
	while($x=mysqli_fetch_object($sql->mysqry)) $_colaboradores[$x->id]=$x;

	$_pacientes=array();
	$sql->consult($_p."pacientes","id,nome","where id IN (".implode(",",$pacientesIds).")");
	while($x=mysqli_fetch_object($sql->mysqry)) $_pacientes[$x->id]=$x;


	$_receberNoDia=array();
	$_inadimplentes=array();
	$_inadimplentesIds=array();
	foreach($regsIn as $x) {
		if(isset($_pacientes[$x->id_paciente]) && isset($_tratamentos[$x->id_tratamento])) {

			$_inadimplentesIds[$x->id]=1;
			$paciente=$_pacientes[$x->id_paciente];
			$plano=$_tratamentos[$x->id_tratamento];

			$aux = explode(" ",$paciente->nome);

			$pacienteNome=$aux[0]." ".$aux[count($aux)-1];

			if(isset($_baixas[$x->id])) {
				var_dump($_baixas[$x->id]);
			} else {
				$totais['inadimplentes']+=$x->valor;
				$_inadimplentes[]=array('id_paciente'=>$paciente->id,
											'paciente'=>utf8_encode($pacienteNome),
											'plano'=>utf8_encode($plano->titulo),
											'data_vencimento'=>date('d/m/Y',strtotime($x->data_vencimento)),
											'valor'=>$x->valor,
											'baixa'=>0);
			}
		}
	
	}

	$_promessaDePagamento=array();
	foreach($regs as $x) {

		
		if(isset($_inadimplentesIds[$x->id])) { 
			continue;
		}
		if(isset($_pacientes[$x->id_paciente]) && isset($_tratamentos[$x->id_tratamento])) {
			$paciente=$_pacientes[$x->id_paciente];
			$plano=$_tratamentos[$x->id_tratamento];

			$aux = explode(" ",$paciente->nome);

			$pacienteNome=$aux[0]." ".$aux[count($aux)-1];

			if(isset($_baixas[$x->id]) and $_baixas[$x->id]['valor']==$x->valor) {
				$baixa=$_baixas[$x->id];
				foreach($baixa['baixas'] as $b) {
			

					if(strtotime($b->data_vencimento)==strtotime(date('Y-m-d'))) {
						$totais['receber']+=$b->valor;
						$_receberNoDia[]=array('id_paciente'=>$paciente->id,
												'paciente'=>utf8_encode($pacienteNome),
												'plano'=>utf8_encode($plano->titulo),
												'data_vencimento'=>date('d/m/Y',strtotime($b->data_vencimento)),
												'valor'=>$b->valor,
												'baixa'=>1);
					} else if(strtotime($b->data_vencimento)<strtotime(date('Y-m-d'))) {
						$totais['inadimplentes']+=$b->valor;
					
						$_inadimplentes[]=array('id_paciente'=>$paciente->id,
												'paciente'=>utf8_encode($pacienteNome),
												'plano'=>utf8_encode($plano->titulo),
												'data_vencimento'=>date('d/m/Y',strtotime($b->data_vencimento)),
												'valor'=>$b->valor,
												'baixa'=>1);
					}

					
				}
			} else { 
				if(strtotime($x->data_vencimento)==strtotime(date('Y-m-d'))) {

					$totais['receber']+=$x->valor;
					$_receberNoDia[]=array('id_paciente'=>$paciente->id,
												'paciente'=>utf8_encode($pacienteNome),
												'plano'=>utf8_encode($plano->titulo),
												'data_vencimento'=>date('d/m/Y',strtotime($x->data_vencimento)),
												'valor'=>$x->valor,
												'baixa'=>0);

				} else if(strtotime($x->data_vencimento)<strtotime(date('Y-m-d'))) {

					
					$totais['inadimplentes']+=$x->valor;
					$_inadimplentes[]=array('id_paciente'=>$paciente->id,
												'paciente'=>utf8_encode($pacienteNome),
												'plano'=>utf8_encode($plano->titulo),
												'data_vencimento'=>date('d/m/Y',strtotime($x->data_vencimento)),
												'valor'=>$x->valor,
												'baixa'=>0);
				} else {
					$totais['promessa']+=$x->valor;
					$_promessaDePagamento[]=array('id_paciente'=>$paciente->id,
												'paciente'=>utf8_encode($pacienteNome),
												'plano'=>utf8_encode($plano->titulo),
												'data_vencimento'=>date('d/m/Y',strtotime($x->data_vencimento)),
												'valor'=>$x->valor,
												'baixa'=>0);
				
				}
			}
		} 
	}

	$_adimplentes=array();
	foreach($regsIn as $x) { 
		if(isset($_inadimplentesIds[$x->id])) continue;
		if(isset($_pacientes[$x->id_paciente]) && isset($_tratamentos[$x->id_tratamento])) {
		
			$paciente=$_pacientes[$x->id_paciente];
			$plano=$_tratamentos[$x->id_tratamento];

			$aux = explode(" ",$paciente->nome);

			$pacienteNome=$aux[0]." ".$aux[count($aux)-1];
		
			$_adimplentes[]=array('id_paciente'=>$paciente->id,
										'paciente'=>utf8_encode($pacienteNome),
										'plano'=>utf8_encode($plano->titulo),
										'data_vencimento'=>date('d/m/Y',strtotime($x->data_vencimento)),
										'valor'=>$x->valor);
		}
	}

	$_contasPagarHoje=$_contasVencidas=array();
	foreach($regsContas as $x) {

		$pagante = "-";
		if($x->credor_pagante=="fornecedor" and isset($_fornecedores[$x->id_fornecedor])) {
			$pagante=utf8_encode($_fornecedores[$x->id_fornecedor]->titulo);
		} 
		else if($x->credor_pagante=="paciente" and isset($_pacientes[$x->id_paciente])) {
			$pagante=utf8_encode($_pacientes[$x->id_paciente]->nome);
		} 
		else if($x->credor_pagante=="colaborador" and isset($_colaboradores[$x->id_colaborador])) {
			$pagante=utf8_encode($_colaboradores[$x->id_colaborador]->nome);
		}

		if(strtotime(date('Y-m-d'))>strtotime($x->data_vencimento)) {
			$totais['vencidas']+=$x->valor;
			$_contasVencidas[]=array('data_vencimento'=>date('d/m/Y', strtotime($x->data_vencimento)),
									'descricao'=>$x->descricao,
									'pagante'=>$pagante,
									'valor'=>(double)$x->valor);
		} else if(strtotime(date('Y-m-d'))==strtotime($x->data_vencimento)) {
			$totais['pagar']+=$x->valor;
			$_contasPagarHoje[]=array('data_vencimento'=>date('d/m/Y', strtotime($x->data_vencimento)),
									'descricao'=>$x->descricao,
									'pagante'=>$pagante,
									'valor'=>(double)$x->valor);
		} 
	}

	$_contasEBancos=array();
	$sql->consult($_p."financeiro_bancosecontas","*","where lixo=0 order by titulo asc");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_contasEBancos[$x->id]=$x;
	}

?>

	<section class="content">  

		<?php
		require_once("includes/nav2.php");
		//require_once("includes/asideFinanceiro.php");
		?>

		<script type="text/javascript">

			var data = '<?php echo $dataWH;?>';
			var popViewInfos = [];
			let dataAux = new Date("<?php echo $data;?>");

			const meses = ["jan.", "fev.", "mar.", "abr.", "mai.", "jun.", "jul.","ago.","set.","out.","nov.","dez."];
			const dias = ["domingo","segunda-feira","terça-feira","quarta-feira","quinta-feira","sexta-feira","sábado"];
			
			let dataFormatada = `${dias[dataAux.getDay()]}, ${dataAux.getDate()} de ${meses[(dataAux.getMonth())]} de ${dataAux.getFullYear()}`;
			
			var leads = JSON.parse(`<?php echo json_encode($leads);?>`);

			const leadsListar = () => {

				$(`#kanban .js-kanban-item,#kanban .js-kanban-item-modal`).remove();

				leads.semAgendamento.forEach(x=>{

					let barra=``;
				
					let html = `<div class="kanban-card">
									<a href="javascript:;"  onclick="$('.kanban-card-modal').hide(); $(this).next('.kanban-card-modal').show();" class="kanban-card-dados js-kanban-item" data-id="${x.id_agenda}">
										${barra}
										<h1>${x.nome}</h1>
										<h2>${x.telefone1}</h2>
									</a>
									<div class="kanban-card-modal js-kanban-item-modal" style="display:none;">
										<div class="kanban-card-modal__inner1">
											<a class="kanban-card-modal__fechar" href="javascript:;" onclick="$(this).parent().parent().hide(); $('.js-reagendar, .js-cancelar').hide(); $('.js-opcoes').show();"><i class="iconify" data-icon="ph-x"></i></a>
											<h1>${x.nome}</h1>
											<h2>${x.telefone1}</h2>
										</div>
										<div class="kanban-card-modal__inner2 js-opcoes">
											<a href="javascript:;" class="button button__full" style="background-color:var(--verde);">Confirmar agendamento</a>
											<a href="javascript:;" onclick="$(this).parent().hide(); $(this).parent().nextAll('.js-reagendar').show();" class="button button__full" style="background-color:var(--amarelo);">Reagendar</a>
											<a href="javascript:;" onclick="$(this).parent().hide(); $(this).parent().nextAll('.js-cancelar').show();" class="button button__full" style="background-color:var(--vermelho);">Cancelar Agendamento</a>
										</div>
										<div class="kanban-card-modal__inner2 js-reagendar" style="display:none;">
											<form>
												<input type="text" name="" class="datecalendar" placeholder="06/04/2021" />
												<select name=""><option value="">Profissional...</option></select>
												<select name=""><option value="">Cadeira...</option></select>
												<select name=""><option value="">Horas disponíveis...</option></select>
												<button type="submit" class="button button__full" style="background:var(--amarelo);">Reagendar</button>
											</form>
										</div>
										<div class="kanban-card-modal__inner2 js-cancelar" style="display:none;">
											<form>
												<textarea name="" rows="4" placeholder="Descreva o motivo do cancelamento..."></textarea>
												<button type="submit" class="button button__full" style="background:var(--vermelho);">Cancelar</button>
											</form>
										</div>
									</div>
								</div>`;

					//$(`#kanban .js-kanban-status-semAgendamento`).append(html);
				})
			}	

			const d2 = (num) => {
				return num <=9 ? `0${num}`:num;
			}

			const dataProcess = (dtObj) => {
					

				let dataFormatada = `${dias[dtObj.getDay()]}, ${dtObj.getDate()} de ${meses[(dtObj.getMonth())]} de ${dtObj.getFullYear()}`;


				data = `${dtObj.getFullYear()}-${d2(dtObj.getMonth()+1)}-${d2(dtObj.getDate())}`;

				agendaAtualizar();

				$('.js-calendario-title').val(dataFormatada)
			}


			$(function(){

				$('.js-calendario').datetimepicker({
					timepicker:false,
					format:'d F Y',
					scrollMonth:false,
					scrollTime:false,
					scrollInput:false,
					onChangeDateTime:function(dp,dt) {
						dataProcess(dp);
					}
				});

				leadsListar();

				$('.js-calendario-title').val(dataFormatada);

				
				/*
				var droppable = $(".js-kanban-status").dad({
					placeholderTarget: ".js-kanban-item"
				});

				$(".js-kanban-status").on("dadDrop", function (e, element) {
					let id_agenda = $(element).attr('data-id');
					let id_status = $(element).parent().attr('data-id_status');

					let dataAjax = `ajax=alterarStatus&id_agenda=${id_agenda}&id_status=${id_status}`;
					$.ajax({
						type:"POST",
						data:dataAjax,
						success:function(rtn) {
							if(rtn.success) {
								agendaAtualizar();
							}
						}
					})
		        });
		        */

				$('a.js-right').click(function(){
					let aux = data.split('-');
					let dtObj = new Date(`${aux[1]}/${aux[2]}/${aux[0]}`);
					dtObj.setDate(dtObj.getDate()+1);
					dataProcess(dtObj);
				});

				$('a.js-left').click(function(){ 
					let aux = data.split('-');
					let dtObj = new Date(`${aux[1]}/${aux[2]}/${aux[0]}`);
					dtObj.setDate(dtObj.getDate()-1);
					dataProcess(dtObj);
				});

				$('a.js-today').click(function(){
					let dtObj = new Date(`<?php echo date('m/').(date('d')-1).date('/Y');?>`);
					dtObj.setDate(dtObj.getDate()+1);
					dataProcess(dtObj);
				});

			});
		</script>

		<section class="grid">
			<div class="kanban" id="kanban">
				
				<div class="kanban-item" style="background:#f9de27;color:var(--cor1);">
					<h1 class="kanban-item__titulo"><span class="iconify" data-icon="akar-icons:circle-plus" data-height="20"></span>&nbsp;&nbsp;&nbsp;Promesssa de Pagagamento<br />R$ <?php echo number_format($totais['promessa'],2,",",".");?></h1>
					<div class="kanban-card" style="min-height: 100px;">
						<?php
						foreach($_promessaDePagamento as $x) {
							$x=(object)$x;
						?>
						<a href="javascript:;" onclick="$(this).next('.kanban-card-modal').show();" class="kanban-card-dados js-kanban-item ${evolucao}" data-id="${x.id_agenda}">
							<h1><?php echo $x->paciente." - ".$x->id_paciente;?></h1>
							<h2><?php echo $x->plano;?></h2>
							<h2><?php echo $x->data_vencimento;?> - <?php echo number_format($x->valor,2,",",".");?></h2>
							<?php echo $x->baixa==1?"baixa":"";?>
						</a>
						<?php
						}
						?>
					</div>
				</div>
				
				<div class="kanban-item" style="background:#fd4b3e;color:var(--cor1);">
					<h1 class="kanban-item__titulo"><span class="iconify" data-icon="akar-icons:circle-plus" data-height="20"></span>&nbsp;&nbsp;&nbsp;Inadimplente<br />R$ <?php echo number_format($totais['inadimplentes'],2,",",".");?></h1>
					<div class="kanban-card" style="min-height: 100px;">
						<?php
						foreach($_inadimplentes as $x) {
							$x=(object)$x;
						?>
						<a href="javascript:;" onclick="$(this).next('.kanban-card-modal').show();" class="kanban-card-dados js-kanban-item ${evolucao}" data-id="${x.id_agenda}">
							<h1><?php echo $x->paciente." - ".$x->id_paciente;?></h1>
							<h2><?php echo $x->plano;?></h2>
							<h2><?php echo $x->data_vencimento;?> - <?php echo number_format($x->valor,2,",",".");?></h2>
							<?php echo $x->baixa==1?"baixa":"";?>
						</a>
						<?php
						}
						?>
					</div>
				</div>
				
				<div class="kanban-item" style="background:#53d429;color:var(--cor1);">
					<h1 class="kanban-item__titulo"><span class="iconify" data-icon="akar-icons:circle-plus" data-height="20"></span>&nbsp;&nbsp;&nbsp;À receber do dia<br />R$ <?php echo number_format($totais['receber'],2,",",".");?></h1>
					<div class="kanban-card" style="min-height: 100px;">
						<?php
						foreach($_receberNoDia as $x) {
							$x=(object)$x;
						?>
						<a href="javascript:;" onclick="$(this).next('.kanban-card-modal').show();" class="kanban-card-dados js-kanban-item ${evolucao}" data-id="${x.id_agenda}">
							<h1><?php echo $x->paciente." - ".$x->id_paciente;?></h1>
							<h2><?php echo $x->plano;?></h2>
							<h2><?php echo $x->data_vencimento;?> - <?php echo number_format($x->valor,2,",",".");?></h2>
						</a>
						<?php
						}
						?>
					</div>
				</div>
				
				<div class="kanban-item" style="background:#f6ac09;color:var(--cor1);">
					<h1 class="kanban-item__titulo"><span class="iconify" data-icon="akar-icons:circle-minus" data-height="20"></span>&nbsp;&nbsp;&nbsp;À pagar do dia<br />R$ <?php echo number_format($totais['pagar'],2,",",".");?></h1>
					<div class="kanban-card" style="min-height: 100px;">
						<?php
						foreach($_contasPagarHoje as $x) {
							$x=(object)$x;
						?>
						<a href="javascript:;" onclick="$(this).next('.kanban-card-modal').show();" class="kanban-card-dados js-kanban-item ${evolucao}" data-id="${x.id_agenda}">
							<h1><?php echo $x->pagante;?></h1>
							<h2><?php echo $x->descricao;?></h2>
							<h2><?php echo $x->data_vencimento;?> - <?php echo number_format($x->valor,2,",",".");?></h2>
						</a>
						<?php
						}
						?>
					</div>
				</div>
				
				<div class="kanban-item" style="background:#e7000e;color:var(--cor1);">
					<h1 class="kanban-item__titulo"><span class="iconify" data-icon="akar-icons:circle-minus" data-height="20"></span>&nbsp;&nbsp;&nbsp;Conta Vencidas<br />R$ <?php echo number_format($totais['vencidas'],2,",",".");?></h1>
					<div class="kanban-card" style="min-height: 100px;">
						<?php
						foreach($_contasVencidas as $x) {
							$x=(object)$x;
						?>
						<a href="javascript:;" onclick="$(this).next('.kanban-card-modal').show();" class="kanban-card-dados js-kanban-item ${evolucao}" data-id="${x.id_agenda}">
							<h1><?php echo $x->pagante;?></h1>
							<h2><?php echo $x->descricao;?></h2>
							<h2><?php echo $x->data_vencimento;?> - <?php echo number_format($x->valor,2,",",".");?></h2>
						</a>
						<?php
						}
						?>
					</div>
				</div>
				
				<div class="kanban-item" style="background:none">
					<h1 class="kanban-item__titulo" style="color:#000">Bancos e Contas</h1>
					<div class="kanban-card" style="min-height: 100px;">
						<?php
						foreach($_contasEBancos as $x) {
							$saldo=0;
							$sql->consult($_p."financeiro_extrato","sum(valor) as total","where id_conta=$x->id and lixo=0");
							if($sql->rows) {
								$t=mysqli_fetch_object($sql->mysqry);
								$saldo=$t->total;
							}
						?>
						<a href="javascript:;" class="kanban-card-dados js-kanban-item" style="background:#118fff;">
							<h1 style="color:#FFF">
								<?php echo utf8_encode($x->titulo);?>:
								<br />
								<?php echo number_format($saldo,2, ",",".");?>
							</h1>
						</a>
						<?php
						}
						?>
					</div>
				</div>
				
				
			</div> 

		</section>

	</section>
			
<?php
	include "includes/footer.php";
?>