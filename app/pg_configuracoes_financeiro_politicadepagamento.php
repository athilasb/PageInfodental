<?php
	require_once("lib/conf.php");
	require_once("usuarios/checa.php");

	$_table=$_p."parametros_politicapagamento";
	// formas de pagamento
	$_formasDePagamento=array();
	$optionFormasDePagamento='';
	$sql->consult($_p."parametros_formasdepagamento","*","where lixo=0  AND politica_de_pagamento=0 order by titulo asc");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_formasDePagamento[$x->id]=(object)array("id"=>$x->id,
											"titulo"=>utf8_encode($x->titulo),
											"tipo"=>$x->tipo);
	}
	// consulta todas politicas para verificar interceção do "de" e "até";
	$_politica=[];
	$sql->consult($_table,"*","where lixo=0");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_politica[$x->id]=$x;
	}
	
	if(isset($_POST['ajax'])) {
		require_once("usuarios/checa.php");
		$rtn=array();
		if($_POST['ajax']=="editar") {
			$cnt = '';
			if(isset($_POST['id']) and is_numeric($_POST['id'])) {
				$sql->consult($_table,"*","where id=".$_POST['id']);
				if($sql->rows) {
					$cnt=mysqli_fetch_object($sql->mysqry);
				}
			}
			if(empty($cnt)) {
				$rtn=array('success'=>false,'error'=>'Registro não encontrado!');
			} else {	
				$data = array('id'=>$cnt->id,
								'de'=>number_format($cnt->de,2,",","."),
								'ate'=>number_format($cnt->ate,2,",","."),
								'entrada'=>utf8_encode($cnt->entrada),
								'parcelas'=>utf8_encode($cnt->parcelas),
								'parcelasJSON'=>!empty($cnt->parcelasParametros)?json_decode($cnt->parcelasParametros):[]);
				$rtn=array('success'=>true,'data'=>$data);

			}
		} 

		else if($_POST['ajax']=="remover") {
			$cnt = '';
			if(isset($_POST['id']) and is_numeric($_POST['id'])) {
				$sql->consult($_table,"*","where id=".$_POST['id']);
				if($sql->rows) {
					$cnt=mysqli_fetch_object($sql->mysqry);
				}
			}

			if(empty($cnt)) {
				$rtn=array('success'=>false,'error'=>'Registro não encontrado!');
			} else {

				
				$vWHERE="where id=$cnt->id";
				$vSQL="lixo=1";
				$sql->update($_table,$vSQL,$vWHERE);
				$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='delete',vsql='".addslashes($vSQL)."',vwhere='".addslashes($vWHERE)."',tabela='$_table',id_reg='".$cnt->id."'");
				$rtn=array('success'=>true);

			}
		}

		else if($_POST['ajax']=="update") {
			$cnt = '';
			if(isset($_POST['id']) and is_numeric($_POST['id'])) {
				$sql->consult($_table,"*","where id=".$_POST['id']);
				if($sql->rows) {
					$cnt=mysqli_fetch_object($sql->mysqry);
				}
			}

			if(empty($cnt)) {
				$rtn=array('success'=>false,'error'=>'Registro não encontrado!');
			} else {
				$status = $_POST['status'];
					
				$vWHERE="where id=$cnt->id";
				$vSQL="status=$status";
				$sql->update($_table,$vSQL,$vWHERE);
				$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='delete',vsql='".addslashes($vSQL)."',vwhere='".addslashes($vWHERE)."',tabela='$_table',id_reg='".$cnt->id."'");
				$rtn=array('success'=>true);
			}
		}
		else if($_POST['ajax']=="persistir") {
			$tipo = (isset($_POST['tipo']) and !empty($_POST['tipo'])) ? ($_POST['tipo']) : '';
			$de = (isset($_POST['de']) and !empty($_POST['de'])) ? ($_POST['de']) : 0;
			$ate = (isset($_POST['ate']) and !empty($_POST['ate'])) ? ($_POST['ate']) : '10000000000';
			$parcelasJSON = (isset($_POST['parcelasJSON']) and !empty($_POST['parcelasJSON'])) ? json_decode($_POST['parcelasJSON']) : '';
			
			$erro='';
			if(($tipo =='intervalo') && (empty($ate))) $erro='Preencha o campo Até';
			else if($de>$ate) $erro='O campo De deve ser menor que o campo Até';
			if(!empty($erro)) {
				$rtn=array('success'=>false,'error'=>$erro);
			} else {
				// consulta todas politicas para verificar interceção do "de" e "até";
				$_politica=[];
				$sql->consult($_table,"*","where lixo=0  AND status=0");
				while($x=mysqli_fetch_object($sql->mysqry)) {
					$_politica[$x->id]=$x;
				}

				
				$vSQL="de='".addslashes($de)."',
						ate='".addslashes($ate)."',
						tipo_politica='".$tipo."',
						entrada='0',
						parcelas='0',
						parcelasParametros='".json_encode($parcelasJSON)."'";

				$cnt = '';
				if(isset($_POST['id']) and is_numeric($_POST['id']) and $_POST['id']>0) {
					$sql->consult($_table,"*","where id=".$_POST['id']."");
					if($sql->rows) {
						$cnt=mysqli_fetch_object($sql->mysqry);
					}
				}

				// se for edição, remove o registro que está removendo da verificação
				if(is_object($cnt)) { 
					if(isset($_politica[$cnt->id])) {
						 unset($_politica[$cnt->id]); 
					}
				}
				// verifica se tem interceção
				$possuiIntercecao=false;
				$possuiIntercecaoObj='';
				foreach($_politica as $x) {
					$start_one = $de;
					$end_one = $ate;
					$start_two = $x->de;
					$end_two = $x->ate;
					if($start_one <= $end_two && $end_one >= $start_two) { //If the dates overlap
						$possuiIntercecao=true;
						$possuiIntercecaoObj="R$".number_format($x->de,2,",",".")." até R$".number_format($x->ate,2,",",".");
						break;
					} 
				}
				if($possuiIntercecao===true) {
					$rtn=array('success'=>false,'error'=>"Já existe Política de Pagamento ".$possuiIntercecaoObj);
				} else {

					if(is_object($cnt)) {
						$vWHERE="where id=$cnt->id";
						$sql->update($_table,$vSQL,$vWHERE);
						$id_politica=$cnt->id;
						$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='update',vsql='".addslashes($vSQL)."',vwhere='".addslashes($vWHERE)."',tabela='$_table',id_reg='$id_politica'");

					} else {
						$vSQL.=",data=now()";
						$sql->add($_table,$vSQL);
						$id_politica=$sql->ulid;
						$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='insert',vsql='".addslashes($vSQL)."',vwhere='',tabela='$_table',id_reg='$id_politica'");
					}

					// persiste as configurações de bandeiras e taxas
					/*if(isset($_POST['bandeiras_json']) and !empty($_POST['bandeiras_json'])) {

						$bandeiraJson = json_decode($_POST['bandeiras_json']);

						$sql->update($_p."parametros_cartoes_operadoras_bandeiras","lixo=1","where id_operadora=$id_operadora and lixo=0");
						foreach($bandeiraJson as $idBandeira=>$obj) {


							// verifica se bandeira ja esta vinculado a operadora
							$sql->consult($_p."parametros_cartoes_operadoras_bandeiras","*","where id_operadora=$id_operadora and id_bandeira=$idBandeira and lixo=0");
							$vinculo=$sql->rows?mysqli_fetch_object($sql->mysqry):'';

							$vSQL="id_operadora=$id_operadora, 
									id_bandeira=$idBandeira, 
									check_debito='$obj->debito',
									check_credito='$obj->credito',
									credito_parcelas='$obj->credito_parcelas',
									credito_parcelas_semjuros='$obj->creditoSemJuros',
									taxas='".addslashes(json_encode($obj))."',
									lixo=0";

							if(is_object($vinculo)) {
								$vWHERE="where id=$vinculo->id";
								$sql->update($_p."parametros_cartoes_operadoras_bandeiras",$vSQL,$vWHERE);

								$sql->add($_p."log","data=now(),
														id_usuario='".$usr->id."',
														tipo='update',
														vsql='".addslashes($vSQL)."',
														vwhere='".addslashes($vWHERE)."',
														tabela='".$_p."parametros_cartoes_operadoras_bandeiras',id_reg='".$vinculo->id."'");
							} else {
								$sql->add($_p."parametros_cartoes_operadoras_bandeiras",$vSQL);
								$id_reg=$sql->ulid;
								$sql->add($_p."log","data=now(),
														id_usuario='".$usr->id."',
														tipo='insert',
														vsql='".addslashes($vSQL)."',
														tabela='".$_p."parametros_cartoes_operadoras_bandeiras',
														id_reg='".$id_reg."'");
							}
							
						}

						$jsc->jAlert("Informações salvas com sucesso!","sucesso","document.location.href='$_page'");
						die();
					}*/
					$rtn=array('success'=>true,'id_reg'=>$id_politica);

				}
			}
		}
		header("Content-type: application/json");
		echo json_encode($rtn);
		die();
	}



	
	$qtdParcelamento=12;

	include "includes/header.php";
	include "includes/nav.php";
	$values=$adm->get($_GET);
	$campos=explode(",","de,ate,entrada,parcelas,parcelasParametros");
	if(isset($_POST['acao'])) {
		
	}

?>

	<header class="header">
		<div class="header__content content">
			<div class="header__inner1">
				<section class="header-title">
					<h1>Configuração</h1>
				</section>
				<?php
				require_once("includes/menus/menuConfiguracoes.php");
				?>
			</div>
		</div>
	</header>

	<main class="main">
		<div class="main__content content">

			<section class="filter">
				<div class="filter-group">
					<div class="filter-title">
						<h1>Configure a Política das Formas de Pagamento</h1>
					</div>
				</div>
			</section>
 	
			<section class="grid">
				<div class="box box-col">

					<?php
					require_once("includes/submenus/subConfiguracoesFinanceiro.php");
					?>

					<div class="box-col__inner1">
						<section class="filter">
							<div class="filter-group">
								<div class="filter-form form">
									<dl>
										<dd><a href="javascript:;" class="button button_main js-openAside"><i class="iconify" data-icon="fluent:add-circle-24-regular"></i> <span>Nova Política</span></a></dd>
									</dl>
								</div>								
							</div>
										
						</section>

						<?php
						# LISTAGEM #
						$where="where lixo=0";
						if(isset($values['busca']) and !empty($values['busca'])) {
							$where.=" and titulo like '%".$values['busca']."%'";
						}
						$sql->consultPagMto2($_table,"*",10,$where." order by de asc","",15,"pagina",$_page."?".$url."&pagina=");
						//echo $_table." ".$where."->".$sql->rows;
						if($sql->rows==0) {
							if(isset($values['busca'])) $msg="Nenhum registro encontrado";
							else $msg="Nenhum registro";
							echo "<center>$msg</center>";
						} else {
						?>	
							<div class="list1">
								<table>
									<?php
									while($x=mysqli_fetch_object($sql->mysqry)) {
									?>
									<tr class="js-item" data-id="<?php echo $x->id;?>">
									<?php if($x->tipo_politica=='intervalo'):?>
										<td><?= "De <b>R$".number_format($x->de,2,",",".")."</b> até <b>R$".number_format($x->ate,2,",",".")."</b>";?></td>
									<?php else: ?>
										<td><?= "Acima de <b>R$".number_format($x->de,2,",",".");?></td>
									<?php endif; ?>
										<td>Entrada mínima: 
											<?=$x->entrada?>%
										</td>
										<td>Máximo de parcelas: 
											<?=$x->parcelas;?>
										</td>
										<td>
											<label><input type="checkbox" class="input-switch" <?= ($x->status==0)?'checked':''?> onclick="UpdateStatus(<?=$x->id?>, <?=$x->status?>)"/></label>
										</td>
									</tr>
									<?php
									}
									?>
								</table>
							</div>
							<?php
								if(isset($sql->myspaginacao) and !empty($sql->myspaginacao)) {
							?>
							<div class="paginacao">						
								<?php echo $sql->myspaginacao;?>
							</div>
							<?php
							}
						}
						# LISTAGEM #
						?>

					</div>					
				</div>
			</section>
		
		</div>
	</main>

	<script type="text/javascript">
		const _politicas = <?=json_encode($_politica);?>;
		const asideParcelasPersiste = () => {

			let parcelas = $('#js-aside select[name=parcelas]').val();

			let parcelasJSON = [];
			for(var i=1;i<=parcelas;i++) {
				let item = {};

				let prazo = eval($(`.js-prazo-${i}`).val());
				let prazoAnterior = eval($(`.js-prazo-${(i-1)}`).val());
				let juros = $(`.js-juros-${i}`).val();
				
				//console.log('Parcela '+i+': '+prazoAnterior+' depois '+prazo)

				if(i>1) {
					if(prazoAnterior>prazo) {
						$(`.js-prazo-${i}`).addClass('erro');
						$(`.js-prazo-${i}`).val('');
						swal({title: "Erro!", text: 'O prazo da Parcela '+i+' deve ser maior que o prazo da Parcela '+(i-1), type:"error", confirmButtonColor: "#424242"});
						prazo='';
					}
				}

				item.parcela = i;
				item.juros = juros;
				item.prazo = prazo;

				parcelasJSON.push(item);
			}

			$('.js-textarea-parcelas').val(JSON.stringify(parcelasJSON));


		}
	
		// abre aside para adição (id=0) ou edição (id>0) de operadora
		const openAside = (id) => {
			if($.isNumeric(id) && id>0) {
				let data = `ajax=editar&id=${id}`;
				$.ajax({
					type:"POST",
					data:data,
					success:function(rtn){ 
						if(rtn.success) {
							rtn.data.parcelasJSON.metodos.forEach(x=>{
								var closestParent = $($(`#status-${x.tipo}`)).closest('div');
								$(`#status-${x.tipo}`).prop('checked',true);
								showOptionsPolitica($(`#status-${x.tipo}`))
								$(closestParent).find('[name="quantidadeParcelas"]').attr('value',x.parcelas);
								$(closestParent).find('[name="quantidadeParcelasSemJuros"]').val(x.parcelaSemJuros);
								$(closestParent).find('[name="entradaMinima"]').val(x.entradaMinima);
								$(closestParent).find('[name="jurosAnual"]').val(x.jurosAnual);
								$(closestParent).find('[name="descontoAvista"]').val(x.descontoAvista);
							});
							$('[name="tipo_politica"]').filter(`[value=${rtn.data.parcelasJSON.tipoPolitica}]`).prop("checked", true);
							if(rtn.data.parcelasJSON.tipoPolitica == 'acima'){
								$('#parametros_gerais dl:eq(1)').hide();
							}else{
								$('#parametros_gerais dl:eq(1)').show();
							}

							$('#js-aside input[name=id]').val(rtn.data.id);
							$('#js-aside input[name=de]').val(rtn.data.de);
							$('#js-aside input[name=ate]').val(rtn.data.ate);
							let parcelas = rtn.data.parcelas;

							$('.js-textarea-parcelas').val(JSON.stringify(rtn.data.parcelasJSON));
							
							$("#js-aside").fadeIn(100,function() {
								$("#js-aside .aside__inner1").addClass("active");
								$('.js-money').maskMoney({decimal:',',thousands:'.',allowZero:true});
							});

						} else if(rtn.error) {
							swal({title: "Erro!", text: rtn.error, type:"error", confirmButtonColor: "#424242"});
						} else {
							swal({title: "Erro!", text: 'Algum erro ocorreu durante a abertura deste registro.', type:"error", confirmButtonColor: "#424242"});
						}
					},
					error:function(){
						swal({title: "Erro!", text: 'Algum erro ocorreu durante a abertura deste registro', type:"error", confirmButtonColor: "#424242"});
					}
				});

			} 
			else {
				$('.js-fieldset-bandeiras,.js-btn-remover').hide();
				
				$("#js-aside").fadeIn(100,function() {
					$('.js-money').maskMoney({decimal:',',thousands:'.',allowZero:true});
					$("#js-aside .aside__inner1").addClass("active");
				});
			}
		}

		const UpdateStatus = (id, status) => {
			(status==0)?status=1:status=0;

			let data = `ajax=update&id=${id}&status=${status}`;
			$.ajax({
				type:"POST",
				data:data,
				success:function(rtn) {
					console.log(rtn)
					if(rtn.success) {
						//swal({title: "Sucesso!", text: "Salvo com Sucesso", type:"sucess", confirmButtonColor: "#424242"});
						document.location.reload();
					} else if(rtn.error) {
						swal({title: "Erro!", text: rtn.error, type:"error", confirmButtonColor: "#424242"});
						
					} else {
						swal({title: "Erro!", text: 'Algum erro ocorreu! Tente novamente.', type:"error", confirmButtonColor: "#424242"});
						
					}
				},
				error:function() {
					swal({title: "Erro!", text: 'Algum erro ocorreu! Tente novamente', type:"error", confirmButtonColor: "#424242"});
				}
			}).done(function(){
			})
		}

		const showOptionsPolitica = (e)=>{
			let closestParent = $(e).closest('div');
			let dataTipo = $(e).attr('data-tipo')
			if($(e).prop('checked')===true) {
				$(closestParent).find('dl').each(function(i,input){
					if(i>0){
				 		$(input).show()
				 	}
				})
			}else{
				$(closestParent).find('dl').each(function(i,input){
				 	if(i>0){
				 		$(input).hide()
				 	}
				})
			}
			
		}

		$(function(){
			// Botao salvar Politica
			document.querySelector('#js-aside .js-salvarPolitica').addEventListener('click',function(e){
				let erro= "";
				const idPolitica = $('[name="id"]').val()
				const tipoPolitica = $('[name="tipo_politica"]:checked').val()
				const checkboxesChecked =$('form input[type="checkbox"]:checked');
				let de = 0;
				let ate =0;
				let ObjetoPolitica={};
			
				if(tipoPolitica=='intervalo'){
					de = unMoney($('[name="de"]').val());
					ate = unMoney($('[name="ate"]').val());

				}else if(tipoPolitica=='acima'){
					de = unMoney($('[name="de"]').val());
					ate = unMoney("0");
				}else{
					erro = "Nenhuma Tipo de Politica selecionado"
				}
				if(de ==undefined || de ==null){
					erro = "Voce Precisa Adicionar uma Valor 'DE'";
				}else if(ate == undefined || ate == null){
					erro = "Voce Precisa Adicionar uma Valor 'ATE'";
				}else if(tipoPolitica == 'intervalo' && (de > ate || ate == 0)){
					erro = "O campo 'DE' Não Pode ser Menor que o campo 'ATE'";
				}else if(checkboxesChecked.length<=0){
					erro = "Voce Precisa Selecionar pelo menos 1 metodo de pagamento";
				}
				if(erro){
					swal({title: "Erro!", text: erro, type:"error", confirmButtonColor: "#424242"});
					return;
				}else{
 					erro= ""
					ObjetoPolitica = {
						de,ate,tipoPolitica,metodos:[]
					}
					checkboxesChecked.each(function(i,checkbox){
						if(checkbox.getAttribute('data-tipo')){
							let Pai = $(checkbox).closest('div');
							let obj = {
								metodoPagamentoId:parseInt($(Pai).attr('id')),
								habilitado:0,
								de,
								ate,
								tipo:checkbox.getAttribute('data-tipo'),
								parcelas:parseInt($(Pai).find('[name="quantidadeParcelas"]').val()),
								parcelaSemJuros:parseInt($(Pai).find('[name="quantidadeParcelasSemJuros"]').val()),
								entradaMinima:parseFloat($(Pai).find('[name="entradaMinima"]').val()),
								jurosAnual:parseFloat($(Pai).find('[name="jurosAnual"]').val()),
								descontoAvista:parseFloat($(Pai).find('[name="descontoAvista"]').val())
							}
							if(isNaN(parseInt($(Pai).find('[name="quantidadeParcelas"]').val())) || isNaN(parseInt($(Pai).find('[name="quantidadeParcelasSemJuros"]').val())) || isNaN(parseFloat($(Pai).find('[name="entradaMinima"]').val())) || isNaN(parseFloat($(Pai).find('[name="jurosAnual"]').val())) || isNaN(parseFloat($(Pai).find('[name="descontoAvista"]').val()))){
								erro  = `O Metodo ${checkbox.getAttribute('data-tipo').toUpperCase()} esta Habilitado, Todos Os campos Precisam ser preenchidos!`
								return;
							}
							ObjetoPolitica.metodos.push(obj)
					    }
					})

					if(erro){
						swal({title: "Erro!", text: erro, type:"error", confirmButtonColor: "#424242"});
						return;
					}
					let data = `ajax=persistir&id=${idPolitica}&tipo=${tipoPolitica}&de=${de}&ate=${ate}&parcelasJSON=${JSON.stringify(ObjetoPolitica)}`;
					$.ajax({
						type:"POST",
						data:data,
						success:function(rtn) {
							console.log(rtn)
							if(rtn.success) {
								//swal({title: "Sucesso!", text: "Salvo com Sucesso", type:"sucess", confirmButtonColor: "#424242"});
								document.location.reload();
							} else if(rtn.error) {
								swal({title: "Erro!", text: rtn.error, type:"error", confirmButtonColor: "#424242"});
								
							} else {
								swal({title: "Erro!", text: 'Algum erro ocorreu! Tente novamente.', type:"error", confirmButtonColor: "#424242"});
								
							}
						},
						error:function() {
							swal({title: "Erro!", text: 'Algum erro ocorreu! Tente novamente', type:"error", confirmButtonColor: "#424242"});
						}
					}).done(function(){
					})
				}

			})
			
			$('.js-table-parcelas').on('keyup','.js-prazo,.js-juros',function(){
				$(this).removeClass('erro')
			});
			$('.js-table-parcelas').on('keyup','.js-prazo',function() {

		        this.value=this.value.replace('.',',');

		        var regexp = (/[^0-9]/g);

		        if (regexp.test(this.value)) {
		            this.value = this.value.replace(regexp, '');
		        }
		    });

		    $('.js-table-parcelas').on('keyup','.js-money',function(){
		    	$(this).maskMoney({decimal:',',thousands:'',precision:2,allowZero:true});
		    })

			$('.js-table-parcelas').on('change','.js-prazo,.js-juros',function(){
				asideParcelasPersiste();
			})
			// fechar aside de bandeiras
			$('#js-aside .aside-close-parcelas').click(function(){
				const checkboxesChecked =$('form input[type="checkbox"]');
				let obj = $(this);
				if($('#js-aside input[name=alteracao]').val()=="1") {
					swal({   
							title: "Atenção",   
							text: "Tem certeza que deseja fechar sem salvar as informações?",
							type: "warning",   
							showCancelButton: true,   
							confirmButtonColor: "#DD6B55",   
							confirmButtonText: "Sim!",   
							cancelButtonText: "Não",   
							closeOnConfirm: false,   
							closeOnCancel: false 
						}, function(isConfirm){   
							if (isConfirm) {   
								$(obj).parent().parent().removeClass("active");
								$(obj).parent().parent().parent().fadeOut();
								checkboxesChecked.each(function(i,checkbox){
									if(checkbox.getAttribute('data-tipo')){
										let Pai = $(checkbox).closest('div');
										$(Pai).find('[name="quantidadeParcelas"]').val(""),
										$(Pai).find('[name="quantidadeParcelasSemJuros"]').val("")
										$(Pai).find('[name="entradaMinima"]').val("")
										$(Pai).find('[name="jurosAnual"]').val("")
										$(Pai).find('[name="descontoAvista"]').val("")
										$(Pai).find('dl:eq(1)').hide()
										$(Pai).find('dl:eq(2)').hide()
										$(Pai).find('dl:eq(3)').hide()
										$(Pai).find('dl:eq(4)').hide()
										$(Pai).find('dl:eq(5)').hide()
									}
								})
								swal.close();
					  		 } else {   
					  		 	swal.close();   
					  		 } 
					  	});
				} else {
					$(obj).parent().parent().removeClass("active");
					$(obj).parent().parent().parent().fadeOut();
					checkboxesChecked.each(function(i,checkbox){
						if(checkbox.getAttribute('data-tipo')){
							let Pai = $(checkbox).closest('div');
							$(Pai).find('[name="quantidadeParcelas"]').val(""),
							$(Pai).find('[name="quantidadeParcelasSemJuros"]').val("")
							$(Pai).find('[name="entradaMinima"]').val("")
							$(Pai).find('[name="jurosAnual"]').val("")
							$(Pai).find('[name="descontoAvista"]').val("")
							$(Pai).find('dl:eq(1)').hide()
							$(Pai).find('dl:eq(2)').hide()
							$(Pai).find('dl:eq(3)').hide()
							$(Pai).find('dl:eq(4)').hide()
							$(Pai).find('dl:eq(5)').hide()
						}
					})
				}
			});
			// nova operadora/maquininha
			$('.js-openAside').click(function(){
				$('#js-aside form.formulario-validacao').trigger('reset');
				$('#js-aside input[name=id]').val(0);
				openAside(0);
			});
			// abre aside de bandeira ao clicar na operadora/maquininha
			$('.list1').on('click','.js-item',function(){
				$('#js-aside form.formulario-validacao').trigger('reset');
				let id = $(this).attr('data-id');
				openAside(id);
			});
			// ao alterar alguma informação 
			$('#js-aside').on('change','input,select,textarea',function(){
				$('#js-aside input[name=alteracao]').val(1);
			});
			// configuracao dos inputs dias
			$('#js-aside-taxas .js-input-dias').keyup(function(){
				 var regexp = (/[^0-9\.]|^\.+(?!$)|^0+(?=[0-9]+)|\.(?=\.|.+\.)/g);
			    if (regexp.test(this.value)) {
			        this.value = this.value.replace(regexp, '');
			    }
			});
			// Botao de deletar
			$('.js-fieldset-bandeiras,.js-btn-remover').click(function(){
				const idPolitica = document.querySelector('[name="id"]').value
				swal({   
					title: "Atenção",   
					text: "Tem certeza que deseja Deletar essa politica ?",
					type: "warning",   
					showCancelButton: true,   
					confirmButtonColor: "#DD6B55",   
					confirmButtonText: "Sim!",   
					cancelButtonText: "Não",   
					closeOnConfirm: false,   
					closeOnCancel: false 
					}, function(isConfirm){   
						if (isConfirm) {   
							let data = `ajax=remover&id=${idPolitica}`;
							$.ajax({
								type:"POST",
								data:data,
								success:function(rtn) {
									if(rtn.success) {
										//swal({title: "Sucesso!", text: "Salvo com Sucesso", type:"sucess", confirmButtonColor: "#424242"});
										document.location.reload();
									} else if(rtn.error) {
										swal({title: "Erro!", text: rtn.error, type:"error", confirmButtonColor: "#424242"});
										
									} else {
										swal({title: "Erro!", text: 'Algum erro ocorreu! Tente novamente.', type:"error", confirmButtonColor: "#424242"});
										
									}
								},
								error:function() {
									swal({title: "Erro!", text: 'Algum erro ocorreu! Tente novamente', type:"error", confirmButtonColor: "#424242"});
								}
							}).done(function(){
							})
								swal.close();
					  		} else {   
					  		 	swal.close();   
			  		 } 
			  	});
			})
		})
	</script>

	<!-- NOVA POLITICA -->
	<section class="aside aside-form" id="js-aside">
		<div class="aside__inner1">
			<header class="aside-header">
				<h1>Política de Pagamento</h1>
				<a href="javascript:;" class="aside-header__fechar aside-close-parcelas"><i class="iconify" data-icon="fluent:dismiss-24-filled"></i></a>
			</header>

			<form method="post" class="aside-content form js-form formulario-validacao">
				<input type="hidden" name="acao" value="wlib" />
				<input type="hidden" name="id" value="0" />
				<input type="hidden" name="alteracao" value="0" />

				<section class="filter">
					<div class="filter-group"></div>
					<div class="filter-group">
						<div class="filter-form form">
							<dl>
								<dd><a href="javascript:;" class="button js-btn-remover"><i class="iconify" data-icon="fluent:delete-24-regular"></i></a></dd>
							</dl>
							<dl>
								<dd><button type="button" class="button button_main js-salvarPolitica" data-loading="0"><i class="iconify" data-icon="fluent:checkmark-12-filled"></i> <span>Salvar</span></button></dd>
							</dl>
						</div>								
					</div>
				</section>

				<fieldset>
					<legend>
						Politica
					</legend>
					<div class="colunas4">
						<dl style="margin-bottom:2rem">
							<dd>
								<label><input type="radio" name="tipo_politica" value="intervalo" onclick="$('#parametros_gerais dl:eq(1)').show();" checked/> intervalo</label>
								<label><input type="radio" name="tipo_politica" value="acima" onclick="$('#parametros_gerais dl:eq(1)').hide();" /> Acima De</label>
							</dd>
						</dl>
					</div>
					<div class="colunas4" id='parametros_gerais'>
						<dl>
							<dt>De</dt>
							<dd class="form-comp"><span>R$</i></span><input type="text" name="de" class="obg js-money" placeholder="0,00" /></dd>
						</dl>
						<dl>
							<dt>Até</dt>
							<dd class="form-comp"><span>R$</i></span><input type="text" name="ate" class="obg js-money" placeholder="0,00" /></dd>
						</dl>
					</div>
				</fieldset>
				<?php foreach($_formasDePagamento as $id=>$p):?>
				<fieldset>
					<legend><?=$p->titulo?></legend>
					<div class="colunas4" id="<?=$p->id?>">
						<dl class="dl1">
							<dd>
								<label><input  id="status-<?=$p->tipo?>"  data-tipo='<?=$p->tipo?>' type="checkbox" class="input-switch" onchange="showOptionsPolitica(this)"/></label>
							</dd>
						</dl>
						<dl style="display:none">
							<dt>Máximo de Parcela</dt>
							<dd><input type="text" name="quantidadeParcelas" /></dd>
						</dl>
						<dl style="display:none">
							<dt>Parcelas Sem Juros</dt>
							<dd><input type="number" name="quantidadeParcelasSemJuros" /></dd>
						</dl>
						<dl style="display:none">
							<dt>Entrada Mínima %</dt>
							<dd class="form-comp"><span>%</i></span><input type="text" name="entradaMinima" placeholder="0%" /></dd>
						</dl>
						<dl style="display:none">
							<dt>Juros Anual %</dt>
							<dd class="form-comp"><span>%</i></span><input type="number" name="jurosAnual" placeholder="0%"/></dd>
						</dl>
						<dl style="display:none">
							<dt>Desconto a Vista % </dt>
							<dd class="form-comp"><span>%</i></span><input type="number" name="descontoAvista" placeholder="0%"/></dd>
						</dl>
					</div>
				</fieldset>
				<?php endforeach?>
			</form>

		</div>
		<script>
			const acimaOrIntervalo = (e)=>{
				const selected = e.options[e.selectedIndex].value;
				if(selected=='acima'){
					$('#info-politica').html("")
					$('#info-politica').append(`
							<dl>
								<dt>De</dt>
								<dd><input type="text" name="de" class="obg js-money" /></dd>
							</dl>
							<dl>
								<dt>Entrada Mínima</dt>
								<dd><input type="text" name="ate" class="" placeholder="0%" /></dd>
							</dl>
							<dl>
								<dt>Quantidade Máxima de Parcela</dt>
								<dd><input type="text" name="ate" class="" /></dd>
							</dl>
						`)
				}else{
					$('#info-politica').html("")
						$('#info-politica').append(`
							<dl>
								<dt>De</dt>
								<dd><input type="text" name="de" class="obg js-money" /></dd>
							</dl>
							<dl>
							<dt>Até</dt>
								<dd><input type="text" name="ate" class="obg js-money" /></dd>
							</dl>
							<dl>
								<dt>Entrada Mínima</dt>
								<dd><input type="text" name="ate" class="" placeholder="0%" /></dd>
							</dl>
							<dl>
								<dt>Quantidade Máxima de Parcela</dt>
								<dd><input type="text" name="ate" class="" /></dd>
							</dl>
						`)
				}
			}
		</script>	
	</section>

	<!-- Aside Configurações de Taxas -->
	<section class="aside aside-form" id="js-aside-taxas">
		<div class="aside__inner1" style="width: 92%;">

			<header class="aside-header">
				<h1>Taxas</h1>
				<a href="javascript:;" class="aside-header__fechar aside-close"><i class="iconify" data-icon="fluent:dismiss-24-filled"></i></a>
			</header>

			<form method="post" class="aside-content form js-form formulario-validacao">
				<input type="hidden" name="acao" value="wlib" />
				<input type="hidden" name="id_operadora" value="0" />
				<input type="hidden" name="id_bandeira" value="0" />
				<input type="hidden" name="alteracao" value="0" />

				<section class="filter">
					<div class="filter-group"></div>
					<div class="filter-group">
						<div class="filter-form form">
							<dl>
								<dd><button type="button" class="button button_main js-salvarTaxas"><i class="iconify" data-icon="fluent:checkmark-12-filled"></i> <span>Salvar</span></button></dd>
							</dl>
						</div>								
					</div>
				</section>
				

				<fieldset class="js-fieldset-debito">
					<legend>Débito</legend>

					<div class="colunas8">
						<dl>
							<dd style="gap:0;margin-left:25px;">
								<input type="text" class="js-input-taxa js-debito-taxa" style="width: 70px;" maxlength="5" />&nbsp;
								<input type="text" class="js-input-dias js-debito-dias" style="width: 70px;" maxlength="3" />
							</dd>
						</dl>
					</div>
				</fieldset>

				<fieldset  class="js-fieldset-credito">
					<legend>Crédito</legend>

						<div class="colunas5">
							<dl class="dl2">
								<dt>Quantidade de parcelas sem juros para o cliente</dt>
								<dd>
									<select class="js-credito-parcelasSemJuros">
										<option value="">-</option>
										<?php
										for($i=1;$i<=12;$i++) {
										?>
										<option value="<?php echo $i;?>">até <?php echo $i;?>x sem juros</option>
										<?php
										}
										?>
									</select>
								</dd>
							</dl>
						</div>
				

						<table class="list2" style="width:100%;">
							<tr>
						<?php
						for($i=1;$i<=6;$i++) {
						?>
							<th style="text-transform: none;" class="js-th-creditoParcela-<?php echo $i;?>"><?php echo $i."x";?></th>
						<?php
						}
						?>
							</tr>
							<tr>
						<?php
						for($i=1;$i<=6;$i++) {
						?>
								<td valign="top" class="js-parcela-<?php echo $i;?>">
									<?php
									for($parcela=1;$parcela<=$i;$parcela++) {
									?>
									<div style="display:flex;margin-bottom:3px;">
										<span><?php echo $parcela;?>x</span>&nbsp;
										<input type="text" class="js-input-taxa js-taxa-<?php echo $parcela;?>" style="width: 70px;" maxlength="5" />&nbsp;
										<input type="text" class="js-input-dias js-dias-<?php echo $parcela;?>" style="width: 70px;" maxlength="3" />
									</div>
									<?php
									}
									?>
								</td>
						<?php
						}
						?>
							</tr>
						</table>

						<table class="list2" style="width:100%;">
							<tr>
						<?php
						for($i=7;$i<=12;$i++) {
						?>
							<th style="text-transform: none;" class="js-th-creditoParcela-<?php echo $i;?>"><?php echo $i."x";?></th>
						<?php
						}
						?>
							</tr>
							<tr>
						<?php
						for($i=7;$i<=12;$i++) {
						?>
								<td valign="top" class="js-parcela-<?php echo $i;?>">
									<?php
									for($parcela=1;$parcela<=$i;$parcela++) {
									?>
									<div style="display:flex;margin-bottom:3px;">
										<span><?php echo $parcela<10?"<font color=#FFF>x</font>".$parcela:$parcela;?>x</span>&nbsp;
										<input type="text" class="js-input-taxa js-taxa-<?php echo $parcela;?>" style="width: 70px;" maxlength="5" />&nbsp;
										<input type="text" class="js-input-dias js-dias-<?php echo $parcela;?>" style="width: 70px;" maxlength="3" />
									</div>
									<?php
									}
									?>
								</td>
						<?php
						}
						?>
							</tr>
						</table>
					</fieldset>



			</form>

		</div>
	</section>
	
	

<?php 
	include "includes/footer.php";
?>	
