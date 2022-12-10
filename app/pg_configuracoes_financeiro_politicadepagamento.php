<?php
	require_once("lib/conf.php");
	require_once("usuarios/checa.php");

	$_table=$_p."parametros_politicapagamento";

	
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

		else if($_POST['ajax']=="persistir") {



			$de = (isset($_POST['de']) and !empty($_POST['de'])) ? valor($_POST['de']) : '';
			$ate = (isset($_POST['ate']) and !empty($_POST['ate'])) ? valor($_POST['ate']) : '';
			$entrada = (isset($_POST['entrada']) and !empty($_POST['entrada'])) ? ($_POST['entrada']) : '';
			$parcelas = (isset($_POST['parcelas']) and !empty($_POST['parcelas'])) ? ($_POST['parcelas']) : '';
			$parcelasJSON = (isset($_POST['parcelasJSON']) and !empty($_POST['parcelasJSON'])) ? ($_POST['parcelasJSON']) : '';

			//echo $de." ".$ate;die();

			$erro='';
			if(empty($de)) $erro='Preencha o campo De';
			else if(empty($ate)) $erro='Preencha o campo Até';
			else if($de>$ate) $erro='O campo De deve ser menor que o campo Até';
			else if(empty($entrada)) $erro='Preencha o campo de Entrada mínima (%)';
			else if(empty($parcelas)) $erro='Preencha o campo de Qtd. máxima de parcelas';

			if(!empty($erro)) {

				$rtn=array('success'=>false,'error'=>$erro);

			} else {

				// consulta todas politicas para verificar interceção do "de" e "até";
				$_politica=[];
				$sql->consult($_table,"*","where lixo=0");
				while($x=mysqli_fetch_object($sql->mysqry)) {
					$_politica[$x->id]=$x;
				}

				
				$vSQL="de='".addslashes($de)."',
						ate='".addslashes($ate)."',
						entrada='".addslashes($entrada)."',
						parcelas='".addslashes($parcelas)."',
						parcelasParametros='".addslashes($parcelasJSON)."'";


				$cnt = '';
				if(isset($_POST['id']) and is_numeric($_POST['id'])) {
					$sql->consult($_table,"*","where id=".$_POST['id']." and lixo=0");
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

					//echo "$start_one - $end_one > $start_two - $end_two = ";
					
					if($start_one <= $end_two && $end_one >= $start_two) { //If the dates overlap
						//echo  1; //return how many days overlap
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
	$campos=explode(",","de,ate,entrada,parcelas");

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
										<td><?php echo "De <b>R$".number_format($x->de,2,",",".")."</b> até <b>R$".number_format($x->ate,2,",",".")."</b>";?></td>
										<td>
											<?php
											echo 'Entrada mínima: '.$x->entrada.'%';
											?>
										</td>
										<td>
											<?php
											echo 'Máximo de parcelas: '.$x->parcelas;
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


		const asideParcelas = () => {

			$('#js-aside .js-fieldset-parcelas .js-table-parcelas').html('');

			let parcelas = $('#js-aside select[name=parcelas]').val();

			for(var i=1;i<=parcelas;i++) {
				let div = `<tr>
								<td style="text-align:center;">${i}x</td>
								<td><input type="text" class="js-prazo js-prazo-${i}" maxlength="3" /></td>
								<td><input type="text" class="js-juros js-juros-${i} js-money" maxlength="5" /></td>
							</tr>`;

				$('.js-table-parcelas').append(div);
			}

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
							$('#js-aside input[name=de]').val(rtn.data.de);
							$('#js-aside input[name=ate]').val(rtn.data.ate);
							$('#js-aside input[name=entrada]').val(rtn.data.entrada);
							$('#js-aside input[name=id]').val(rtn.data.id);
							$('#js-aside select[name=parcelas]').val(rtn.data.parcelas);

							let parcelas = rtn.data.parcelas;

							//console.log(rtn.data.parcelasJSON[1]);

							asideParcelas();

							if(rtn.data.parcelasJSON.length>0) {

								for(var i=0;i<parcelas;i++) {
									let index = i+1;
									$(`.js-prazo-${index}`).val(rtn.data.parcelasJSON[i].prazo);
									$(`.js-juros-${index}`).val(rtn.data.parcelasJSON[i].juros);

								}
							}
							$('.js-textarea-parcelas').val(JSON.stringify(rtn.data.parcelasJSON));
							$('.js-fieldset-bandeiras,.js-btn-remover').show();
							
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

		
		$(function(){


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

			// Botao salvar Politica
			$('#js-aside .js-salvarPolitica').click(function(){

				let obj = $(this);
				let objHTMLAntigo = obj.html();

				if(obj.attr('data-loading')==0) {

					let de = $('#js-aside input[name=de]').val();
					let ate = $('#js-aside input[name=ate]').val();
					let entrada = $('#js-aside input[name=entrada]').val();
					let parcelas = $('#js-aside select[name=parcelas]').val();
					let id = $('#js-aside input[name=id]').val();

					let erro = '';
					if(de.length==0) erro='Preencha o campo De';
					else if(ate.length==0) erro='Preencha o campo Até';
					else if(entrada.length==0) erro='Preencha o campo de Entrada mínima (%)';
					else if(parcelas.length==0) erro='Preencha o campo de Qtd. máxima de parcelas';

					if(erro.length>0) {
						swal({title: "Erro!", text: erro, type:"error", confirmButtonColor: "#424242"});
					} else {

						erro='';
						$(`.js-prazo`).each(function(index,el){
							if($(el).val().length==0) {
								$(el).addClass('erro');
								erro='Preencha todos os campos';
							}
						});


						$(`.js-juros`).each(function(index,el){
							if($(el).val().length==0) {
								$(el).addClass('erro');
								erro='Preencha todos os campos';
							}
						});


						if(erro.length>0) {
							swal({title: "Erro!", text: erro, type:"error", confirmButtonColor: "#424242"});
						} else {
							obj.attr('data-loading',1);
							obj.html('<span class="iconify" data-icon="eos-icons:loading"></span>');

							parcelasJSON=$('.js-textarea-parcelas').val();

							let data = `ajax=persistir&de=${de}&ate=${ate}&entrada=${entrada}&parcelas=${parcelas}&id=${id}&parcelasJSON=${parcelasJSON}`;

							$.ajax({
								type:"POST",
								data:data,
								success:function(rtn) {
									if(rtn.success) {
										document.location.reload();
									} else if(rtn.error) {
										swal({title: "Erro!", text: rtn.error, type:"error", confirmButtonColor: "#424242"});
										obj.html(objHTMLAntigo);
										obj.attr('data-loading',0);
									} else {
										swal({title: "Erro!", text: 'Algum erro ocorreu! Tente novamente.', type:"error", confirmButtonColor: "#424242"});
										obj.html(objHTMLAntigo);
										obj.attr('data-loading',0);
									}
								},
								error:function() {
									swal({title: "Erro!", text: 'Algum erro ocorreu! Tente novamente', type:"error", confirmButtonColor: "#424242"});
									obj.html(objHTMLAntigo);
									obj.attr('data-loading',0);
								}
							}).done(function(){
							})
						}

					}
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


		})
	</script>

	<!-- Aside Bandeiras -->
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
					<legend>Informações</legend>
					
					<div class="colunas4">
						<dl>
							<dt>De</dt>
							<dd><input type="text" name="de" class="obg js-money" /></dd>
						</dl>
						<dl>
							<dt>Até</dt>
							<dd><input type="text" name="ate" class="obg js-money" /></dd>
						</dl>
						<dl>
							<dt>Entrada mínima (%)</dt>
							<dd><input type="text" name="entrada" class="obg js-maskFloat" maxlength="5" /></dd>
						</dl>
						<dl>
							<dt>Qtd. máxima de parcelas</dt>
							<dd>
								<select name="parcelas">
									<option value=""></option>
									<?php
									for($i=1;$i<=24;$i++) {
										echo '<option value="'.$i.'">'.$i.'</option>';
									}
									?>
								</select>
							</dd>
						</dl>
					</div>
				</fieldset>


				<fieldset class="js-fieldset-parcelas">
					
					<textarea class="js-textarea-parcelas" style="display:none;"></textarea>
					
					<div style="display: flex;">
						<table style="width:100%">
							<thead>
								<tr>
									<th>Parcela</th>
									<th>Prazo</th>
									<th>Juros</th>
								</tr>
							</thead>
							<tbody class="js-table-parcelas">
								<tr>
									<td style="text-align:center;">1x</td>
									<td><input type="text" class="js-maskFloat" /></td>
									<td><input type="text" /></td>
								</tr>
							</tbody>
						</table>


					</div>

				</fieldset>



			</form>

		</div>
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