<?php
	require_once("lib/conf.php");
	require_once("usuarios/checa.php");
	$_table=$_p."parametros_fornecedores";

	$_fornecedores=array();
	$sql->consult($_p."parametros_fornecedores","*,IF(tipo_pessoa='PF',nome,razao_social) as titulo","where tipo='FORNECEDOR' and lixo=0 order by titulo");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_fornecedores[$x->id]=$x;
	}

	if(isset($_POST['ajax'])) {

		require_once("usuarios/checa.php");

		$rtn=array();

		if($_POST['ajax']=="editar") {

			$cnt = '';
			$carga = '';
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
								'tipo_pessoa'=>utf8_encode($cnt->tipo_pessoa),
								'nome'=>utf8_encode($cnt->nome),
								'tipo'=>utf8_encode($cnt->tipo),
								'cpf'=>utf8_encode($cnt->cpf),
								'razao_social'=>utf8_encode($cnt->razao_social),
								'nome_fantasia'=>utf8_encode($cnt->nome_fantasia),
								'responsavel'=>utf8_encode($cnt->responsavel),
								'cnpj'=>utf8_encode($cnt->cnpj),
								'telefone1'=>utf8_encode($cnt->telefone1),
								'telefone2'=>utf8_encode($cnt->telefone2),
								'endereco'=>utf8_encode($cnt->endereco),
								'email'=>utf8_encode($cnt->email),
								'complemento'=>utf8_encode($cnt->complemento),
								'pix_tipo'=>utf8_encode($cnt->pix_tipo),
								'pix_chave'=>utf8_encode($cnt->pix_chave),
								'pix_beneficiario'=>utf8_encode($cnt->pix_beneficiario));

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

		else if($_POST['ajax']=="regsPersistir") {

			$cnt='';
			if(isset($_POST['id']) and is_numeric($_POST['id'])) {
				$sql->consult($_table."_bancosecontas","*","where id='".addslashes($_POST['id'])."' and lixo=0");
				if($sql->rows) {
					$cnt=mysqli_fetch_object($sql->mysqry);
				}
			}

			$fornecedor=(isset($_POST['id_fornecedor']) and isset($_fornecedores[$_POST['id_fornecedor']]))?$_fornecedores[$_POST['id_fornecedor']]:'';
			$banco=isset($_POST['banco'])?addslashes(utf8_decode($_POST['banco'])):'';
			$agencia=isset($_POST['agencia'])?addslashes(utf8_decode($_POST['agencia'])):'';
			$conta=isset($_POST['conta'])?addslashes(utf8_decode($_POST['conta'])):'';

			if(empty($fornecedor)) $rtn=array('success'=>false,'error'=>'Fornecedor não encontrado!');
			else {


				$vSQL="id_fornecedor='$fornecedor->id',
						banco='".$banco."',
						agencia='".$agencia."',
						conta='".$conta."',
						lixo=0";

				if(is_object($cnt)) {
					$vWHERE="where id=$cnt->id";
					//$vSQL.=",alteracao_data=now(),id_alteracao=$usr->id";
					$sql->update($_table."_bancosecontas",$vSQL,$vWHERE);
					$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='update',vsql='".addslashes($vSQL)."',vwhere='".addslashes($vWHERE)."',tabela='".$_table."_laboratorios',id_reg='$cnt->id'");
				} else {
					//$vSQL.=",data=now(),id_usuario=$usr->id";
					$sql->add($_table."_bancosecontas",$vSQL);
					$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='update',vsql='".addslashes($vSQL)."',vwhere='',tabela='".$_table."_laboratorios',id_reg='$sql->ulid'");

				}

				$rtn=array('success'=>true);
			}
		} 

		else if($_POST['ajax']=="regsListar") {

			
			$regs=array();
			$fornecedor=(isset($_POST['id_fornecedor']) and isset($_fornecedores[$_POST['id_fornecedor']]))?$_fornecedores[$_POST['id_fornecedor']]:'';
			if(is_object($fornecedor)) {
				$where="WHERE id_fornecedor='".$fornecedor->id."' and lixo=0";
				$sql->consult($_table."_bancosecontas","*",$where);
			
				if($sql->rows) {
					while($x=mysqli_fetch_object($sql->mysqry)) {
						$regs[]=array('id' =>$x->id,
										'id_fornecedor' =>$x->id_fornecedor,
										'banco' => (float)$x->banco,
										'bancoTitulo' => isset($_bancos[$x->banco])?$_bancos[$x->banco]:'',
										'agencia' =>utf8_encode(addslashes($x->agencia)),
										'conta' =>utf8_encode(addslashes($x->conta)));
					}
				} 
				$rtn=array('success'=>true,'regs'=>$regs);
			} else {
				$rtn=array('success'=>false,'error'=>'Fornecedor não definido!');
			}
		} 

		else if($_POST['ajax']=="regsEditar") {
			$cnt='';
			if(isset($_POST['id']) and is_numeric($_POST['id'])) {
				$sql->consult($_table."_bancosecontas","*","where id='".addslashes($_POST['id'])."' and lixo=0");
				if($sql->rows) {
					$x=mysqli_fetch_object($sql->mysqry);
					$cnt=(object)array('id' =>$x->id,
									'id_fornecedor' =>$x->id_fornecedor,
									'banco' =>utf8_encode(addslashes($x->banco)),
									'agencia' =>utf8_encode(addslashes($x->agencia)),
									'conta' =>utf8_encode(addslashes($x->conta))
								);
				}
			}

			if(is_object($cnt)) {

				

				$rtn=array('success'=>true,
							'id'=>$cnt->id,
							'cnt'=>$cnt);
			} else {
				$rtn=array('success'=>false,'error'=>'Pergunta não encontrada!');
			}
		} 

		else if($_POST['ajax']=="regsRemover") {
			$cnt='';
			if(isset($_POST['id']) and is_numeric($_POST['id'])) {
				$sql->consult($_table."_bancosecontas","*","where id='".$_POST['id']."'");
				if($sql->rows) {
					$cnt=mysqli_fetch_object($sql->mysqry);
				}
			}

			if(is_object($cnt)) {
				$vSQL="lixo=$usr->id";
				$vWHERE="where id=$cnt->id";


				$sql->update($_table."_bancosecontas",$vSQL,$vWHERE);
				$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='delete',vsql='".addslashes($vSQL)."',vwhere='".addslashes($vWHERE)."',tabela='".$_table."_laboratorios',id_reg='$cnt->id'");

				$rtn=array('success'=>true);
			} else {
				$rtn=array('success'=>false,'error'=>'Plano não encontrado!');
			}
		}

		header("Content-type: application/json");
		echo json_encode($rtn);
		die();
	}
	include "includes/header.php";
	include "includes/nav.php";

	$values=$adm->get($_GET);
	$campos=explode(",","tipo_pessoa,tipo,nome,cpf,razao_social,nome_fantasia,responsavel,cnpj,telefone1,telefone2,email,endereco,lat,lng,complemento,pix_tipo,pix_chave,pix_beneficiario");

	if(isset($_POST['acao'])) {

		$vSQL=$adm->vSQL($campos,$_POST);
		$values=$adm->values;
		
		$cnt = '';
		if(isset($_POST['id']) and is_numeric($_POST['id'])) {
			$sql->consult($_table,"*","where id=".$_POST['id']." and lixo=0");
			if($sql->rows) {
				$cnt=mysqli_fetch_object($sql->mysqry);
			}
		}

		if(is_object($cnt)) {
			$vWHERE="where id=$cnt->id";
			$vSQL=substr($vSQL,0,strlen($vSQL)-1);
			$sql->update($_table,$vSQL,$vWHERE);
			$id_reg=$cnt->id;
			$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='update',vsql='".addslashes($vSQL)."',vwhere='".addslashes($vWHERE)."',tabela='$_table',id_reg='$id_reg'");
		} else {
			$vSQL=substr($vSQL,0,strlen($vSQL)-1);
			//$vSQL.="tipo='FORNECEDOR'";
			$sql->add($_table,$vSQL);
			$id_reg=$sql->ulid;
			$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='insert',vsql='".addslashes($vSQL)."',vwhere='',tabela='$_table',id_reg='$id_reg'");
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
						<h1>Configure o fornecedor</h1>
					</div>
				</div>
			</section>
 	
			<section class="grid">

				<div class="box box-col">

					<?php
					require_once("includes/submenus/subConfiguracoesFornecedor.php");
					?>
					<script type="text/javascript">
						const openAside = (id) => {

							$('.js-regs-remover').hide();

							if($.isNumeric(id) && id>0) {
								let data = `ajax=editar&id=${id}`;
								$.ajax({
									type:"POST",
									data:data,
									success:function(rtn){ 
										if(rtn.success) {

											if(rtn.data.tipo_pessoa=='PF') {
												$('#js-aside input[name=tipo_pessoa][value=pf]').click();
											} else {

												$('#js-aside input[name=tipo_pessoa][value=pj]').click();
											}

											$('#js-aside input[name=id]').val(rtn.data.id);
											$('#js-aside select[name=tipo]').val(rtn.data.tipo);
											$('#js-aside input[name=nome]').val(rtn.data.nome);
											$('#js-aside input[name=cpf]').val(rtn.data.cpf);
											$('#js-aside input[name=razao_social]').val(rtn.data.razao_social);
											$('#js-aside input[name=nome_fantasia]').val(rtn.data.nome_fantasia);
											$('#js-aside input[name=responsavel]').val(rtn.data.responsavel);
											$('#js-aside input[name=cnpj]').val(rtn.data.cnpj);
											$('#js-aside input[name=telefone1]').val(rtn.data.telefone1);
											$('#js-aside input[name=telefone2]').val(rtn.data.telefone2);
											$('#js-aside input[name=email]').val(rtn.data.email);
											$('#js-aside input[name=endereco]').val(rtn.data.endereco);
											$('#js-aside input[name=complemento]').val(rtn.data.complemento);
											$('#js-aside select[name=pix_tipo]').val(rtn.data.pix_tipo);
											$('#js-aside input[name=pix_chave]').val(rtn.data.pix_chave);
											$('#js-aside input[name=pix_beneficiario]').val(rtn.data.pix_beneficiario);
											regsAtualizar();

											$('.js-fieldset-regs,.js-btn-remover').show();

											$(".aside").fadeIn(100,function() {
												$(".aside .aside__inner1").addClass("active");
											});


											$('html, body').animate({scrollTop: 0},'fast');
											
										} else if(rtn.error) {
											swal({title: "Erro!", text: rtn.error, type:"error", confirmButtonColor: "#424242"});
										} else {
											swal({title: "Erro!", text: 'Algum erro ocorreu durante a abertura deste registro.', type:"error", confirmButtonColor: "#424242"});
										}
									},
									error:function(){
										swal({title: "Erro!", text: 'Algum erro ocorreu durante a abertura deste registro', type:"error", confirmButtonColor: "#424242"});
									}
								})

								

							} else {

								$('.js-fieldset-regs,.js-btn-remover').hide();

								$(".aside").fadeIn(100,function() {
									$(".aside .aside__inner1").addClass("active");
								});
							}
						}
						$(function(){
							$('#js-aside .js-btn-remover').click(function(){
								let id = $('#js-aside input[name=id]').val();
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

							$('.js-openAside').click(function(){
								$('#js-aside form.formulario-validacao').trigger('reset');
								$('#js-aside input[name=id]').val(0);
								openAside(0);
							});

							$('.list1').on('click','.js-item',function(){
								$('#js-aside form.formulario-validacao').trigger('reset');
								let id = $(this).attr('data-id');
								openAside(id);
							});
						})
					</script>

					<div class="box-col__inner1">
				
						<section class="filter">
							<div class="filter-group">
								<div class="filter-form form">
									<dl>
										<dd><a href="javascript:;" class="button button_main js-openAside"><i class="iconify" data-icon="fluent:add-circle-24-regular"></i> <span>Novo Fornecedor</span></a></dd>
									</dl>
								</div>								
							</div>
							<form method="get" class="js-filtro">
								<div class="filter-group">
									<div class="filter-form form">
										<dl>
											<dd class="form-comp form-comp_pos"><input type="text" name="busca" value="<?php echo isset($_GET['busca'])?$_GET['busca']:"";?>" placeholder="Buscar..." /><a href="javascript:;" onclick="$('form.js-filtro').submit();"><i class="iconify" data-icon="fluent:search-12-filled"></i></a></dd>
										</dl>
									</div>
								</div>
							</form>					
						</section>

						<?php
						# LISTAGEM #
						$values=$adm->get($_GET);
						$where="where lixo=0";
						if(isset($values['busca']) and !empty($values['busca'])) {
							//$where.=" and titulo like '%".$values['busca']."%'";
							$wh="";
							$aux = explode(" ",$_GET['busca']);

							foreach($aux as $v) {
								$wh.="(nome REGEXP '$v' or razao_social REGEXP '$v' ) and ";
							}
							$wh=substr($wh,0,strlen($wh)-5);
							$where="where ($wh) and lixo=0";
						}
						//echo $where;//die();
						//$sql->consultPagMto2($_table,"*,IF(tipo_pessoa='PJ',responsavel,nome) as titulo",10,$where." order by titulo","",15,"pagina",$_page."?".$url."&pagina=");
						$sql->consult($_table,"*,IF(tipo_pessoa='PJ',nome_fantasia,nome) as titulo",$where." order by titulo");

						if($sql->rows==0) {
							if(isset($values['busca'])) $msg="Nenhum Resultado encontrado";
							else $msg="Nenhum colaborador cadastrado";
							echo "<center>$msg</center>";
						} else {
							$registros=array();
							while($x=mysqli_fetch_object($sql->mysqry)) {
								$registros[]=$x;
							}
							//ksort($registros);
						?>	
							<div class="list1">
								<table>
									<?php
									foreach($registros as $x) {
									?>
									<tr class="js-item" data-id="<?php echo $x->id;?>">
										<td style="width:20px;"><i class="iconify" data-icon="fluent:chevron-up-down-24-regular"></i></td>
										<td><h1><strong><?php echo utf8_encode($x->titulo.($x->tipo_pessoa=="PJ"?" ($x->razao_social)":""));?></strong></h1></td>
									</tr>
									<?php
									}
									?>
								</table>
							</div>
							<?php
								if(isset($sql->myspaginacao) and !empty($sql->myspaginacao)) {
							?>
							<div class="pagination">						
								Pag<?php echo $sql->myspaginacao;?>
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

	<section class="aside" id="js-aside">
		<div class="aside__inner1">

			<header class="aside-header">
				<h1>Fornecedor</h1>
				<a href="javascript:;" class="aside-header__fechar aside-close"><i class="iconify" data-icon="fluent:dismiss-24-filled"></i></a>
			</header>

			<form method="post" class="aside-content form formulario-validacao">
				<input type="hidden" name="acao" value="wlib" />
				<input type="hidden" name="id" value="0" />
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
					<legend>Dados do Fornecedor</legend>
					
					<div class="colunas3">		
						<dl class="dl">
							<dt>Tipo</dt>
							<dd>
								<select name="tipo" class="obg">
									<option value="">-</option>
									<?php
									foreach($_tiposFornecedores as $k=>$v) echo '<option value="'.$k.'">'.$v.'</option>';
									?>
								</select>
							</dd>
						</dl>
						<dl class="dl2">
							<dt>&nbsp;</dt>
							<dd>
								<label><input type="radio" name="tipo_pessoa" value="pf" checked onclick="$('.js-pessoa').hide(); $('.js-pessoa-pf').show();">Pessoa Física</label>
								<label><input type="radio" name="tipo_pessoa" value="pj" onclick="$('.js-pessoa').hide(); $('.js-pessoa-pj').show();" />Pessoa Jurídica</label>
							</dd>
						</dl>
					</div>
					<div class="js-pessoa js-pessoa-pf">
						<div class="colunas3">
							<dl class="dl2">
								<dt>Nome</dt>
								<dd><input type="text" name="nome" /></dd>
							</dl>
							<dl>
								<dt>CPF</dt>
								<dd><input type="tel" name="cpf" class="cpf" /></dd>
							</dl>
						</div>						
					</div>
					<div class="js-pessoa js-pessoa-pj" style="display:none;">
						<div class="colunas3">
							<dl class="dl2">
								<dt>Nome Fantasia</dt>
								<dd><input type="text" name="nome_fantasia" /></dd>
							</dl>
							<dl>
								<dt>Responsável</dt>
								<dd><input type="text" name="responsavel" class="" /></dd>
							</dl>
							<dl class="dl2">
								<dt>Razão Social</dt>
								<dd><input type="text" name="razao_social" class="" /></dd>
							</dl>
							<dl>
								<dt>CNPJ</dt>
								<dd><input type="tel" name="cnpj" class="cnpj" /></dd>
							</dl>
						</div>						
					</div>
				</fieldset>

				<fieldset>
					<legend>Dados de Contato</legend>
					<div class="colunas3">
						<dl>
							<dt>WhatsApp</dt>
							<dd class="form-comp"><span class="js-country">BR</span><input type="tel" name="telefone1" class=""attern="\d*" x-autocompletetype="tel" /></dd>
						</dl>
						<dl>
							<dt>Telefone</dt>
							<dd class="form-comp"><span class="js-country">BR</span><input type="tel" name="telefone2" class="" attern="\d*" x-autocompletetype="tel" /></dd>
						</dl>
						<dl>
							<dt>Email</dt>
							<dd><input type="email" name="email" /></dd>
						</dl>
					</div>	
					<script>
						var marker = '';
						var map = '';
						var position = '';
						var positionEndereco = '';
						var el = document.getElementById("geolocation");
						var location_timeout = '';
						var geocoder = '';
						var enderecoObj = {};
						var enderecos = [];	
						var lat = `-16.688304`;
						var lng = `-49.267055`;

						function initMap() {
							let options = {componentRestrictions: {country: "bra"}}
							var input = document.getElementById('search');

							var autocomplete = new google.maps.places.Autocomplete(input,options);
							geocoder = new google.maps.Geocoder();

							autocomplete.addListener('place_changed', function() {

								var result = autocomplete.getPlace();
								lat = result.geometry.location.lat();
								lng = result.geometry.location.lng();
								$('input[name=lat]').val(lat);
								$('input[name=lng]').val(lng);

								let logradouro = '';
								let numero = '';
								let bairro = '';
								let cep = '';
								let cidade = '';
								let estado = '';
								let pais = '';
								let descricao = '';

								enderecoObj = { logradouro, numero, bairro, cep, cidade, estado, pais, descricao, lat, lng }

								$('input[name=lat]').val(enderecoObj.lat);
								$('input[name=lng]').val(enderecoObj.lng);
							});

						}	
					</script>
					<script async src="https://maps.googleapis.com/maps/api/js?key=<?php echo $_googleMapsKey;?>&libraries=places&callback=initMap">
					</script>				
					<dl>
						<dt>Endereço</dt>
						<dd><input type="text" name="endereco" id="search" /></dd>
					</dl>
					<dl>
						<dt>Complemento</dt>
						<dd><input type="text" name="complemento" /></dd>
					</dl>
					<input type="hidden" name="lng" id="lng" style="display:none;" />
					<input type="hidden" name="lat" id="lat" style="display:none;" />
				</fieldset>

				<fieldset>
					<legend>PIX</legend>

					<div class="colunas4">
						<dl>
							<dt>Tipo</dt>
							<dd>
								<select name="pix_tipo">
									<option value="">-</option>
									<?php
									foreach($_pixTipos as $k=>$v) {
										echo '<option value="'.$k.'">'.$v.'</option>';
									}
									?>
								</select>
							</dd>
						</dl>
						<dl>
							<dt>Chave</dt>
							<dd><input type="text" name="pix_chave" /></dd>
						</dl>
						<dl class="dl2">
							<dt>Beneficiário</dt>
							<dd><input type="text" name="pix_beneficiario" /></dd>
						</dl>
					</div>
				</fieldset>


				<script type="text/javascript">
					var regs = [];

					const regsListar = (openAside) => {
						
						if(regs) {
							$('.js-regs-table tbody').html('');

							//$(`.js-id_fornecedor option`).prop('disabled',false);


							regs.forEach(x=>{

								//$(`.js-id_fornecedor`).find(`option[value=${x.id_fornecedor}]`).prop('disabled',true);
								$(`.js-regs-table tbody`).append(`<tr class="aside-open js-editar" data-id="${x.id}">
																	<td><h1>${x.bancoTitulo}</h1></td>
																	<td>${x.agencia}</td>
																	<td>${x.conta}</td>
																	<td style="text-align:right;"><a href="javascript:;" class="button js-editar" data-id="${x.id}"><i class="iconify" data-icon="fluent:edit-24-regular"></i></a></td>
																</tr>`)
							});;
							if(openAside===true) {
								$(".aside").fadeIn(100,function() {
									$(".aside .aside__inner1").addClass("active");
								});
							}

						} else {
							if(openAside===true) {
								$(".aside").fadeIn(100,function() {
										$(".aside .aside__inner1").addClass("active");
								});
							}
						}
					}

					const regsAtualizar = (openAside) => {	
						let id_fornecedor=$('#js-aside input[name=id]').val();
						let data = `ajax=regsListar&id_fornecedor=${id_fornecedor}`;
						$.ajax({
							type:"POST",
							data:data,
							success:function(rtn) {
								if(rtn.success) {
									regs=rtn.regs;
									regsListar(openAside);
								}
							}
						})
					}
					
					const regsEditar = (id) => {
						let data = `ajax=regsEditar&id=${id}`;
						$.ajax({
							type:"POST",
							data:data,
							success:function(rtn) {
								if(rtn.success) {
									reg=rtn.cnt

									$(`.js-id`).val(reg.id);
									$(`.js-banco`).val(reg.banco);
									$(`.js-agencia`).val(reg.agencia);
									$(`.js-conta`).val(reg.conta);

									
									$('.js-form').animate({scrollTop: 0},'fast');
									$('.js-regs-submit').html(`<i class="iconify" data-icon="fluent:checkmark-12-filled"></i>`);
									$('.js-regs-remover').show();

								} else if(rtn.error) {
									swal({title: "Erro!", text: rtn.error, type:"error", confirmButtonColor: "#424242"});
								} else {
									swal({title: "Erro!", text: "Algum erro ocorreu durante a edição deste registro!", type:"error", confirmButtonColor: "#424242"});
								}
							},
							error:function(){
								swal({title: "Erro!", text: "Algum erro ocorreu durante a edição deste registro!", type:"error", confirmButtonColor: "#424242"});
							}
						});
					}

					
					$(function(){

						$('form').on('keyup keypress', function(e) {
						  var keyCode = e.keyCode || e.which;
						  if (keyCode === 13) { 
						    e.preventDefault();
						    return false;
						  }
						});

						$('input.money').maskMoney({symbol:'', allowZero:true, showSymbol:true, thousands:'.', decimal:',', symbolStay: true});
						$('input[name=telefone1]').mobilePhoneNumber({allowPhoneWithoutPrefix: '+55'}).bind('country.mobilePhoneNumber', function(echo, country) {
							let countryOut = country || '  ';
							$(this).parent().parent().find('.js-country').html(countryOut);
						}).trigger('keyup');

						$('input[name=telefone2]').mobilePhoneNumber({allowPhoneWithoutPrefix: '+55'}).bind('country.mobilePhoneNumber', function(echo, country) {
							let countryOut = country || '  ';
							$(this).parent().parent().find('.js-country').html(countryOut);
						}).trigger('keyup');
						$('.js-regs-submit').click(function(){
							let obj = $(this);
							if(obj.attr('data-loading')==0) {

								let id_fornecedor=$('#js-aside input[name=id]').val();
								let id = $(`.js-id`).val();
								let banco = $(`.js-banco`).val();
								let agencia = $(`.js-agencia`).val();
								let conta = $(`.js-conta`).val();

							

								if(id_fornecedor.length==0) {
									swal({title: "Erro!", text: "Selecione o Fornecedor", type:"error", confirmButtonColor: "#424242"});
								}  else {

									obj.html(`<span class="iconify" data-icon="eos-icons:loading"></span>`);
									obj.attr('data-loading',1);

									let data = `ajax=regsPersistir&id=${id}&id_fornecedor=${id_fornecedor}&banco=${banco}&agencia=${agencia}&conta=${conta}`;
									
									$.ajax({
										type:'POST',
										data:data,
										success:function(rtn) {
											if(rtn.success) {
												regsAtualizar();	

												$(`.js-id`).val(0);
												$(`.js-banco`).val(``);
												$(`.js-agencia`).val(``);
												$(`.js-conta`).val(``);

											} else if(rtn.error) {
												swal({title: "Erro!", text: rtn.error, type:"error", confirmButtonColor: "#424242"});
											} else {
												swal({title: "Erro!", text: "Algum erro ocorreu! Tente novamente.", type:"error", confirmButtonColor: "#424242"});
											}
											
										},
										error:function() {
											swal({title: "Erro!", text: "Algum erro ocorreu! Tente novamente.", type:"error", confirmButtonColor: "#424242"});
										}
									}).done(function(){
										$('.js-regs-remover').hide();
										obj.html(`<i class="iconify" data-icon="fluent:add-circle-24-regular"></i>`);
										obj.attr('data-loading',0);
									});

								}
							}
						})

						$('.js-regs-table').on('click','.js-editar',function(){
							let id = $(this).attr('data-id');

							regsEditar(id);
						});

						$('.js-fieldset-regs').on('click','.js-regs-remover',function(){
							let obj = $(this);

							if(obj.attr('data-loading')==0) {

								let id = $('.js-id').val();
								swal({
									title: "Atenção",
									text: "Você tem certeza que deseja remover este registro?",
									type: "warning",
									showCancelButton: true,
									confirmButtonColor: "#DD6B55",
									confirmButtonText: "Sim!",
									cancelButtonText: "Não",
									closeOnConfirm:false,
									closeOnCancel: false }, 
									function(isConfirm){   
										if (isConfirm) {   

											obj.html(`<span class="iconify" data-icon="eos-icons:loading"></span>`);
											obj.attr('data-loading',1);
											let data = `ajax=regsRemover&id=${id}`; 
											$.ajax({
												type:"POST",
												data:data,
												success:function(rtn) {
													if(rtn.success) {
														$(`.js-id`).val(0);
														$(`.js-banco`).val('');
														$(`.js-agencia`).val('');
														$(`.js-conta`).val('');
														regsAtualizar();
														swal.close();   
													} else if(rtn.error) {
														swal({title: "Erro!", text: rtn.error, type:"error", confirmButtonColor: "#424242"});
													} else {
														swal({title: "Erro!", text: "Algum erro ocorreu durante a remoção deste horário!", type:"error", confirmButtonColor: "#424242"});
													}
												},
												error:function(){
													swal({title: "Erro!", text: "Algum erro ocorreu durante a remoção deste horário!", type:"error", confirmButtonColor: "#424242"});
												}
											}).done(function(){
												$('.js-regs-remover').hide();
												obj.html('<i class="iconify" data-icon="fluent:delete-24-regular"></i>');
												obj.attr('data-loading',0);
												$(`.js-regs-submit`).html(`<i class="iconify" data-icon="fluent:add-circle-24-regular"></i>`);
											});
										} else {   
											swal.close();   
										} 
									});
							}
						});


						$('.js-tipo').change(function(){
							let tipo = $(this).val();

							if(tipo.length>0) {
								if(tipo=='simnao') {
									$('.js-dl-alerta').show();
									$('select[name=pergunta_alerta]').addClass('obg');
								} else if(tipo=='simnaotexto') {
									$('.js-dl-alerta').show();
									$('select[name=pergunta_alerta]').addClass('obg');
								} else {
									$('.js-alerta').val('nenhum');
									$('.js-dl-alerta').hide();
									$('select[name=pergunta_alerta]').removeClass('obg');
								}
							} else {
								$('.js-alerta').val('nenhum');
								$('.js-dl-alerta').hide();
								$('select[name=pergunta_alerta]').removeClass('obg');
							}
						});

					});
				</script>

				<fieldset class="js-fieldset-regs">
					<input type="hidden" class="js-id" />
					<legend>Dados Bancários</legend>
					<div class="colunas3">
						<dl>
							<dt>Banco</dt>
							<dd>
								<select class="js-banco">
									<option value="">-</option>
									<?php
									foreach($_bancos as $k=>$v) {
									?>
									<option value="<?php echo $k;?>"><?php echo $v;?></option>
									<?php	
									}
									?>
								</select>
							</dd>
						</dl>
						<dl>
							<dt>Agência</dt>
							<dd><input type="text" class="js-agencia" /></dd>
						</dl>
						<dl>
							<dt>Conta</dt>
							<dd>
								<input type="text" class="js-conta" />
							<button type="button" class="button button_main js-regs-submit" data-loading="0"><i class="iconify" data-icon="fluent:add-circle-24-regular"></i></button>
							<a href="javascript:;" class="button js-regs-remover" data-loading="0" style="display:none;"><i class="iconify" data-icon="fluent:delete-24-regular"></i></a>
							</dd>
						</dl>
					</div>
					<div class="list2" style="margin-top:2rem;">
						<table class="js-regs-table">
							<thead>
								<tr>
									<th>BANCO</th>
									<th>AGÊNCIA</th>
									<th>CONTA</th>
									<th></th>
								</tr>
							</thead>
							<tbody>
							</tbody>
						</table>
					</div>
				</fieldset>
			
			</form>

		</div>
	</section><!-- .aside -->

<?php 
include "includes/footer.php";
?>	