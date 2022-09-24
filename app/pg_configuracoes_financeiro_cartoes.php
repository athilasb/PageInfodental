<?php
	require_once("lib/conf.php");
	require_once("usuarios/checa.php");

	$_table=$_p."parametros_cartoes_operadoras";

	
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

				// consulta as bandeiras vinculadas
				$bandeirasJson=array();
				$sql->consult($_p."parametros_cartoes_operadoras_bandeiras","*","where id_operadora=$cnt->id and lixo=0");
				if($sql->rows) {
					while($x=mysqli_fetch_object($sql->mysqry)) {
						$bandeirasJson[$x->id_bandeira]=json_decode($x->taxas);
					}
				}

				$data = array('id'=>$cnt->id,
								'titulo'=>utf8_encode($cnt->titulo),
								'id_banco'=>$cnt->id_banco,
								'bandeiras'=>$bandeirasJson);

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

		header("Content-type: application/json");
		echo json_encode($rtn);
		die();
	}


	$_financeiroBancos=array();
	$sql->consult($_p."financeiro_bancosecontas","*","where lixo=0 order by titulo");
	while ($x=mysqli_fetch_object($sql->mysqry)) {
		$_financeiroBancos[$x->id]=$x;
	}

	$_financeiroBandeiras=$financeiroBandeiras=array();
	$sql->consult($_p."parametros_cartoes_bandeiras","*","where lixo=0 order by titulo");
	while ($x=mysqli_fetch_object($sql->mysqry)) {
		$_financeiroBandeiras[$x->id]=$x;
		$financeiroBandeiras[]=array('id'=>$x->id,'titulo'=>utf8_encode($x->titulo));
	}

	
	$qtdParcelamento=12;

	include "includes/header.php";
	include "includes/nav.php";

	$values=$adm->get($_GET);
	$campos=explode(",","titulo,id_banco");

	if(isset($_POST['acao'])) {



		$vSQL=$adm->vSQL($campos,$_POST);
		$values=$adm->values;

		//echo $vSQL;die();

		$cnt = '';
		if(isset($_POST['id']) and is_numeric($_POST['id'])) {
			$sql->consult($_table,"*","where id=".$_POST['id']." and lixo=0");
			if($sql->rows) {
				$cnt=mysqli_fetch_object($sql->mysqry);
			}
		}

		if(empty($cnt)) {
			$where = "where id_banco='".addslashes($values['id_banco'])."' and
							titulo='".addslashes($values['titulo'])."' and 
							lixo=0";
			$sql->consult($_table,"*",$where);
			if($sql->rows) $cnt=mysqli_fetch_object($sql->mysqry);
		}

		if(is_object($cnt)) {
			$vWHERE="where id=$cnt->id";
			$vSQL=substr($vSQL,0,strlen($vSQL)-1);
			$sql->update($_table,$vSQL,$vWHERE);
			$id_operadora=$cnt->id;
			$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='update',vsql='".addslashes($vSQL)."',vwhere='".addslashes($vWHERE)."',tabela='$_table',id_reg='$id_operadora'");

		} else {
			$vSQL=substr($vSQL,0,strlen($vSQL)-1);
			$sql->add($_table,$vSQL);
			$id_operadora=$sql->ulid;
			$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='insert',vsql='".addslashes($vSQL)."',vwhere='',tabela='$_table',id_reg='$id_operadora'");
		}




		// persiste as configurações de bandeiras e taxas
		if(isset($_POST['bandeiras_json']) and !empty($_POST['bandeiras_json'])) {

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
		}

		?>
		<script type="text/javascript">$(function(){openAside(<?php echo $id_reg;?>)});</script>
		<?php
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
						<h1>Configure o financeiro</h1>
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
										<dd><a href="javascript:;" class="button button_main js-openAside"><i class="iconify" data-icon="fluent:add-circle-24-regular"></i> <span>Nova Operadora</span></a></dd>
									</dl>
								</div>								
							</div>
							<form method="get" class="js-filtro">
								<div class="filter-group">
									<div class="filter-form form">
										<dl>
											<dd class="form-comp form-comp_pos"><input type="text" name="busca" value="<?php echo isset($values['busca'])?$values['busca']:"";?>" placeholder="Buscar..." /><a href="javascript:;" onclick="$('form.js-filtro').submit();"><i class="iconify" data-icon="fluent:search-12-filled"></i></a></dd>
										</dl>
									</div>
								</div>
							</form>					
						</section>

						<?php
						# LISTAGEM #
						$where="where lixo=0";
						if(isset($values['busca']) and !empty($values['busca'])) {
							$where.=" and titulo like '%".$values['busca']."%'";
						}
						$sql->consultPagMto2($_table,"*",10,$where." order by titulo asc","",15,"pagina",$_page."?".$url."&pagina=");
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
										<td><h1><strong><?php echo utf8_encode($x->titulo);?></strong></h1></td>
										<td>
											<?php
											echo isset($_financeiroBancos[$x->id_banco])?utf8_encode($_financeiroBancos[$x->id_banco]->titulo):'';
											?>
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

		var financeiroBandeiras = JSON.parse(`<?php echo json_encode($financeiroBandeiras);?>`)

		// abre aside para adição (id=0) ou edição (id>0) de operadora
		const openAside = (id) => {
			if($.isNumeric(id) && id>0) {
				let data = `ajax=editar&id=${id}`;
				$.ajax({
					type:"POST",
					data:data,
					success:function(rtn){ 
						if(rtn.success) {
							$('#js-aside input[name=titulo]').val(rtn.data.titulo);
							$('#js-aside input[name=id]').val(rtn.data.id);
							$('#js-aside select[name=id_banco]').val(rtn.data.id_banco);
							$('#js-aside .js-bandeira-taxas').attr('data-id_operadora',rtn.data.id);

							if(rtn.data.bandeiras) {
								$('#js-aside .js-bandeiras-json').val(JSON.stringify(rtn.data.bandeiras));

								let cont = 1;
								financeiroBandeiras.forEach(b=> { console.log(b.id);console.log(rtn.data.bandeiras[b.id]);
									if(rtn.data.bandeiras[b.id]) {
										let bandeira=rtn.data.bandeiras[b.id];
										$(`#js-aside .js-input-bandeira-${b.id}`).prop('checked',true)
										$(`#js-aside .js-credito_${b.id}`).prop('checked',(bandeira.credito==1)?true:false);
										$(`#js-aside .js-debito_${b.id}`).prop('checked',(bandeira.debito==1)?true:false);
										$(`#js-aside .js-credito_parcelas_${b.id}`).val(bandeira.credito_parcelas);
									}

									if(cont++==financeiroBandeiras.length) bandeirasComplemento();
								});
								
							}

							$('.js-fieldset-bandeiras,.js-btn-remover').show();
							
							$("#js-aside").fadeIn(100,function() {
								$("#js-aside .aside__inner1").addClass("active");
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

			} else {
				$('.js-fieldset-bandeiras,.js-btn-remover').hide();
				
				$("#js-aside").fadeIn(100,function() {
					$("#js-aside .aside__inner1").addClass("active");
				});
			}
		}

		// atualiza obj das bandeiras de acordo com habilitação de credito/debito e select das parcelas creditos
		const atualizaBandeiras = () => {
			
			bandeiras = {};

			bandeirasAtual = $('.js-bandeiras-json').val().length>0?JSON.parse($('.js-bandeiras-json').val()):{};

			let cont = 1;

			// monta objeto
			$('.js-input-bandeira').each(function(index,el){
				if($(el).prop('checked')===true) {

					let id_bandeira=$(el).val();
					let debito = $(`.js-debito_${id_bandeira}`).prop('checked')?1:0;
					let credito = $(`.js-credito_${id_bandeira}`).prop('checked')?1:0;
					let parcelas = eval($(`.js-credito_parcelas_${id_bandeira}`).val()); 

					let item = {};
					item.id_bandeira=id_bandeira;
					item.debito=debito;
					item.credito=credito;
					item.credito_parcelas=parcelas;

					// mantem as taxas de debito e credito
					tempCreditoTaxas=tempDebitoTaxas={};
					tempCreditoSemJuros='';

					if(bandeirasAtual[id_bandeira] && bandeirasAtual[id_bandeira].creditoTaxas) tempCreditoTaxas = bandeirasAtual[id_bandeira].creditoTaxas;
					item.creditoTaxas=tempCreditoTaxas;

					if(bandeirasAtual[id_bandeira] && bandeirasAtual[id_bandeira].debitoTaxas) tempDebitoTaxas = bandeirasAtual[id_bandeira].debitoTaxas;
					item.debitoTaxas=tempDebitoTaxas;

					if(bandeirasAtual[id_bandeira] && bandeirasAtual[id_bandeira].creditoSemJuros) tempCreditoSemJuros = bandeirasAtual[id_bandeira].creditoSemJuros;
					item.creditoSemJuros=tempCreditoSemJuros;

					bandeiras[id_bandeira]=item;
				}

				if(cont++==$('.js-input-bandeira').length) {
					$('.js-bandeiras-json').val(JSON.stringify(bandeiras));;
				}
			});
		}

		// ao clicar na bandeira e em credito/debito, habilita/desabilita select de parcelas e botão de editar taxas
		const bandeirasComplemento = () => {
			$('#js-aside .js-input-bandeira').each(function(index,el){
				let id_bandeira = $(el).val(); 

				// se bandeira for true/false, exibe/oculta checkbox credito e debito
				if($(el).prop('checked')===true) $(`.js-comp-${id_bandeira}`).show();
				else $(`.js-comp-${id_bandeira}`).hide();

				// se credito for true/false, exibe/oculta select de parcelas
				if($(`.js-credito_${id_bandeira}`).prop('checked')===true) $(`.js-credito_parcelas_${id_bandeira}`).prop('disabled',false);
				else $(`.js-credito_parcelas_${id_bandeira}`).prop('disabled',true);

				// se credito ou debito estiver true, exibe botão de edição de taxas
				if($(`.js-credito_${id_bandeira}`).prop('checked')===true || $(`.js-debito_${id_bandeira}`).prop('checked')===true) {
					$(`.js-bandeira-taxas-${id_bandeira}`).css('opacity',1);
				} else {
					$(`.js-bandeira-taxas-${id_bandeira}`).css('opacity',0.2);
				}
				

			});
		}

		$(function(){

			// Configuração de Taxas: abertura e configuração tela de configuração de taxas ao clicar no botão de edição de taxas da bandeira
			$('#js-aside .js-bandeira-taxas').click(function(){

				// verifica se está com opacity (desativado)
				if($(this).css('opacity')!=1) {
					swal({title: "Erro!", text: 'Ative a opção de Crédito ou Débito para configurar as taxas', type:"error", confirmButtonColor: "#424242"});
				} else {
				
					let id_bandeira = $(this).attr('data-id_bandeira');
					let id_operadora = $(this).attr('data-id_operadora');
					let credito = $(`.js-credito_${id_bandeira}`).prop('checked')===true?1:0;
					let debito = $(`.js-debito_${id_bandeira}`).prop('checked')===true?1:0;
					let parcelas = $(`.js-credito_parcelas_${id_bandeira}`).val();

					$('#js-aside-taxas select.js-credito-parcelasSemJuros option').each(function(index,el){
						if(eval($(el).val())>parcelas) {
							$(el).hide();
							$(el).attr('data-active',0);
						}
						else {
							$(el).show();
							$(el).attr('data-active',1);
						}
					});

					for(i=1;i<=12;i++) {
						if(i<=parcelas) {
							$(`#js-aside-taxas .js-parcela-${i}`).show();
							$(`#js-aside-taxas .js-th-creditoParcela-${i}`).show();
						}
						else {
							$(`#js-aside-taxas .js-parcela-${i}`).hide();
							$(`#js-aside-taxas .js-th-creditoParcela-${i}`).hide();
						}
					}
					
					if(credito==1) $('#js-aside-taxas .js-fieldset-credito').show();
					else $('#js-aside-taxas .js-fieldset-credito').hide();

					if(debito==1) $('#js-aside-taxas .js-fieldset-debito').show();
					else $('#js-aside-taxas .js-fieldset-debito').hide();

					$("#js-aside-taxas").fadeIn(100,function() {
						$("#js-aside-taxas .aside__inner1").addClass("active");
					});

					$('#js-aside-taxas input[name=id_operadora]').val(id_operadora);
					$('#js-aside-taxas input[name=id_bandeira]').val(id_bandeira);

					// popula os campos de taxas e dias
					let objeto = JSON.parse($('#js-aside .js-bandeiras-json').val());

					if(objeto[id_bandeira]) {

						// popula debito
						if(objeto[id_bandeira].debitoTaxas) {
							$('#js-aside-taxas .js-debito-taxa').val(objeto[id_bandeira].debitoTaxas.taxa);
							$('#js-aside-taxas .js-debito-dias').val(objeto[id_bandeira].debitoTaxas.dias);
						}


						// popula credito
						if(objeto[id_bandeira].creditoTaxas) {
							let objMin = objeto[id_bandeira].creditoTaxas;
							for(var parcela=1;parcela<=parcelas;parcela++) {
								if(objMin[parcela]) {
									for(var interno=1;interno<=parcela;interno++) {
										if(objMin[parcela][interno]) {
											$(`#js-aside-taxas .js-parcela-${parcela} .js-taxa-${interno}`).val(objMin[parcela][interno].taxa);
											$(`#js-aside-taxas .js-parcela-${parcela} .js-dias-${interno}`).val(objMin[parcela][interno].dias);
										}
									}
								}
							}
						}

						// popula credito sem juros
						$('#js-aside-taxas select.js-credito-parcelasSemJuros option:selected').prop('selected',false);
						if(objeto[id_bandeira].creditoSemJuros) {
							if($(`#js-aside-taxas select.js-credito-parcelasSemJuros option[value=${objeto[id_bandeira].creditoSemJuros}]`).attr('data-active')==1) {
								$('#js-aside-taxas select.js-credito-parcelasSemJuros').val(objeto[id_bandeira].creditoSemJuros);
							}
						}
					}

					$('#js-aside-taxas input[name=alteracao]').val(0);
				}
			});

			// clicar nos checkbox de bandeira, credito e debito
			$('#js-aside').find('.js-input-bandeira,.js-input-credito,.js-input-debito').click(bandeirasComplemento);

			// ao alterar informações no fieldset de bandeiras, salva objeto
			$('#js-aside .js-fieldset-bandeiras input').click(atualizaBandeiras);
			$('#js-aside .js-fieldset-bandeiras select').change(atualizaBandeiras);

			// ao remover bandeira
			$('#js-aside .js-btn-remover').click(function(){
				let id = $('input[name=id]').val();
				if($.isNumeric(id) && id>0) {
				
					swal({   
							title: "Atenção",   
							text: "Você tem certeza que deseja remover este registro?",   
							type: "warning",   
							showCancelButton: true,   
							confirmButtonColor: "#DD6B55",   
							confirmButtonText: "Sim!",   
							cancelButtonText: "Não",   
							closeOnConfirm: false,   
							closeOnCancel: false 
						}, function(isConfirm){   
							if (isConfirm) {    

								let data = `ajax=remover&id=${id}`;
								$.ajax({
									type:"POST",
									data:data,
									success:function(rtn) {
										if(rtn.success) {
											document.location.href='<?php echo "$_page?$url";?>';
										} else if(rtn.error) {
											swal({title: "Erro!", text: rtn.error, type:"error", confirmButtonColor: "#424242"});
										} else {
											swal({title: "Erro!", text: 'Algum erro ocorreu durante a remoção deste registro', type:"error", confirmButtonColor: "#424242"});
										}
									},
									error:function(){
										swal({title: "Erro!", text: 'Algum erro ocorreu durante a remoção deste registro.', type:"error", confirmButtonColor: "#424242"});
									}
								})
							} else {   
								swal.close();   
							} 
					});
				}
			});

			// nova operadora/maquininha
			$('.js-openAside').click(function(){
				$('#js-aside form.formulario-validacao').trigger('reset');
				openAside(0);
			});

			// abre aside de bandeira ao clicar na operadora/maquininha
			$('.list1').on('click','.js-item',function(){
				$('#js-aside form.formulario-validacao').trigger('reset');
				let id = $(this).attr('data-id');
				openAside(id);
			});

			// ao alterar alguma informação no aside bandeiras
			$('#js-aside').find('input,select,textarea').change(function(x){
				$('#js-aside input[name=alteracao]').val(1);
			});

			// alterar alguma informação no aside taxas
			$('#js-aside-taxas').find('input,select,textarea').change(function(x){
				$('#js-aside-taxas input[name=alteracao]').val(1);
			});	

			// fechar aside de taxas
			$('#js-aside-taxas .aside-close-taxas').click(function(){
				let obj = $(this);
				if($('#js-aside-taxas input[name=alteracao]').val()=="1") {
					swal({   
							title: "Atenção",   
							text: "Tem certeza que deseja fechar sem salvar as configurações de taxas?",
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
								swal.close();
					  		 } else {   
					  		 	swal.close();   
					  		 } 
					  	});
				} else {
					$(obj).parent().parent().removeClass("active");
					$(obj).parent().parent().parent().fadeOut();
				}
			});

			// fechar aside de bandeiras
			$('#js-aside .aside-close-bandeiras').click(function(){
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
								swal.close();
					  		 } else {   
					  		 	swal.close();   
					  		 } 
					  	});
				} else {
					$(obj).parent().parent().removeClass("active");
					$(obj).parent().parent().parent().fadeOut();
				}
			});

			// configuracao dos inputs dias
			$('#js-aside-taxas .js-input-dias').keyup(function(){
				 var regexp = (/[^0-9\.]|^\.+(?!$)|^0+(?=[0-9]+)|\.(?=\.|.+\.)/g);
			    if (regexp.test(this.value)) {
			        this.value = this.value.replace(regexp, '');
			    }
			});

			// configuracao dos inputs de config. de taxa
			$('#js-aside-taxas .js-input-taxa').maskMoney({decimal:',',thousands:'',precision:2});

			// salvar taxas, monta objeto
			$('#js-aside-taxas .js-salvarTaxas').click(function(){

				let id_operadora = $('#js-aside-taxas input[name=id_operadora]').val();
				let id_bandeira = $('#js-aside-taxas input[name=id_bandeira]').val();
				let objeto = JSON.parse($('#js-aside .js-bandeiras-json').val());
				
				let credito = $(`#js-aside .js-credito_${id_bandeira}`).prop('checked')===true?1:0;
				let debito = $(`#js-aside .js-debito_${id_bandeira}`).prop('checked')===true?1:0;
				let parcelas = $(`#js-aside .js-credito_parcelas_${id_bandeira}`).val();

				// se debito estiver habilitado
				if(debito==1) {

					let debitoTaxa = $('#js-aside-taxas .js-debito-taxa').val();
					let debitoDias = $('#js-aside-taxas .js-debito-dias').val();

					objeto[id_bandeira].debitoTaxas = {'taxa':debitoTaxa,'dias':debitoDias};

				}

				// se credito estiver habilitado
				if(credito==1) {
					let creditoTaxas = {};
					for(var parcela = 1;parcela<=parcelas;parcela++) {
						let taxasParcelas = {};

						for(var interno = 1;interno<=parcela;interno++) {
							let item = {};
							item.parcela=parcela;
							item.interno=interno;
							item.taxa=$(`#js-aside-taxas .js-parcela-${parcela} .js-taxa-${interno}`).val();
							item.dias=$(`#js-aside-taxas .js-parcela-${parcela} .js-dias-${interno}`).val();
							taxasParcelas[interno]=item;
							if(interno==parcela) {
								creditoTaxas[interno]=taxasParcelas;
							}
						}

						if(parcela==parcelas) {
							objeto[id_bandeira].creditoTaxas = creditoTaxas;
						}
					}

					objeto[id_bandeira].creditoSemJuros = $('#js-aside-taxas select.js-credito-parcelasSemJuros').val();
				}

				$('#js-aside .js-bandeiras-json').val(JSON.stringify(objeto));
				$('#js-aside-taxas').find('.js-input-taxa,.js-input-dias').val('');
				$('#js-aside-taxas .aside-close-taxas').parent().parent().removeClass("active");
				$('#js-aside-taxas .aside-close-taxas').parent().parent().parent().fadeOut(); 
				$('#js-aside input[name=alteracao]').val(1);

			});

		})
	</script>

	<!-- Aside Bandeiras -->
	<section class="aside aside-form" id="js-aside">
		<div class="aside__inner1">

			<header class="aside-header">
				<h1>Operadora</h1>
				<a href="javascript:;" class="aside-header__fechar aside-close-bandeiras"><i class="iconify" data-icon="fluent:dismiss-24-filled"></i></a>
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
								<dd><button class="button button_main"><i class="iconify" data-icon="fluent:checkmark-12-filled"></i> <span>Salvar</span></button></dd>
							</dl>
						</div>								
					</div>
				</section>

				<fieldset>
					<legend>Dados da Operadora</legend>
					
					<div class="colunas3">

						<dl class="dl2">
							<dt>Título</dt>
							<dd><input type="text" name="titulo" class="obg" /></dd>
						</dl>
						<dl>
							<dt>Banco</dt>
							<dd>
								<select name="id_banco">
									<option value="">-</option>
									<?php
									foreach($_financeiroBancos as $v) {
										if($v->tipo!="contacorrente") continue;
									?>
									<option value="<?php echo $v->id;?>"><?php echo utf8_encode($v->titulo);?></option>
									<?php	
									}
									?>
								</select>
							</dd>
						</dl>
					</div>
				</fieldset>


				<fieldset class="js-fieldset-bandeiras">
					<textarea name="bandeiras_json" class="js-bandeiras-json"></textarea>
					
					<legend>Bandeiras</legend>
					<?php
					foreach($_financeiroBandeiras as $b) {
					?>
					<div class="colunas3">
						<dl>
							<dd><label><input type="checkbox" class="input-switch js-input-bandeira js-input-bandeira-<?php echo $b->id;?>" value="<?php echo $b->id;?>" /><?php echo utf8_encode($b->titulo);?></label></dd>
						</dl>
						<dl class="js-comp-<?php echo $b->id;?>" style="display: none;">
							<dd>
								<label><input type="checkbox" class="input-switch js-input-debito js-debito_<?php echo $b->id;?>" />Débito</label>
								<label><input type="checkbox" class="input-switch js-input-credito js-credito_<?php echo $b->id;?>" />Crédito</label>
							</dd>
						</dl>
						<dl class="js-comp-<?php echo $b->id;?>" style="display: none;">
							<dd>
								<select class="<?php echo "js-credito_parcelas_".$b->id;?>" disabled>
									<?php
									for($i=1;$i<=$qtdParcelamento;$i++) {
									?>
									<option value="<?php echo $i;?>">até <?php echo $i."x";?></option>
									<?php
									}
									?>								
								</select>
								<a href="javascript:;" class="button js-bandeira-taxas js-bandeira-taxas-<?php echo $b->id;?>" data-id_bandeira="<?php echo $b->id;?>" data-id_operadora="0" data-aside="detalhes" data-aside-sub style="opacity: 0.2;"><i class="iconify" data-icon="fluent:edit-24-regular"></i></a>
							</dd>
						</dl>
					</div>
					<?php	
					}
					?>

				</fieldset>



			</form>

		</div>
	</section>

	<!-- Aside Configurações de Taxas -->
	<section class="aside aside-form" id="js-aside-taxas">
		<div class="aside__inner1" style="width: 92%;">

			<header class="aside-header">
				<h1>Taxas</h1>
				<a href="javascript:;" class="aside-header__fechar aside-close-taxas"><i class="iconify" data-icon="fluent:dismiss-24-filled"></i></a>
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