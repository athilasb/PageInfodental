<?php
	
	require_once("lib/conf.php");
	$_table=$_p."parametros_fornecedores";

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
								'cpf'=>utf8_encode($cnt->cpf),
								'razao_social'=>utf8_encode($cnt->razao_social),
								'nome_fantasia'=>utf8_encode($cnt->nome_fantasia),
								'responsavel'=>utf8_encode($cnt->responsavel),
								'cnpj'=>utf8_encode($cnt->cnpj),
								'telefone1'=>utf8_encode($cnt->telefone1),
								'telefone2'=>utf8_encode($cnt->telefone2),
								'endereco'=>utf8_encode($cnt->endereco),
								'complemento'=>utf8_encode($cnt->complemento));

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

		else if($_POST['ajax']=="horariosPersistir") {

			$cadeira='';
			if(isset($_POST['id_cadeira']) and is_numeric($_POST['id_cadeira'])) {
				$sql->consult($_p."parametros_cadeiras","*","where id='".$_POST['id_cadeira']."'");
				if($sql->rows) {
					$cadeira=mysqli_fetch_object($sql->mysqry);
				}
			}

			$horario='';
			if(isset($_POST['id']) and is_numeric($_POST['id']) and $_POST['id']>0) {
				$sql->consult($_p."parametros_cadeiras_horarios","*", "where id='".$_POST['id']."' and lixo=0");
				if($sql->rows) $horario=mysqli_fetch_object($sql->mysqry);
			}


			$inicio=(isset($_POST['inicio']) and !empty($_POST['inicio']))?addslashes($_POST['inicio']):'';
			$fim=(isset($_POST['fim']) and !empty($_POST['fim']))?addslashes($_POST['fim']):'';
			$dia=(isset($_POST['dia']) and is_numeric($_POST['dia']))?addslashes($_POST['dia']):'';

			if(empty($cadeira)) $rtn=array('success'=>false,'error'=>'Cadeira não definida!');
			else if(empty($inicio)) $rtn=array('success'=>false,'error'=>'Cadeira não definida!');
			else if(empty($fim)) $rtn=array('success'=>false,'error'=>'Cadeira não definida!');
			else if(empty($dia) and $dia!=0) $rtn=array('success'=>false,'error'=>'Dia da semana não definido.');
			else {


				$horarios = new Horarios(array('prefixo'=>$_p));

				$attr=array('id_cadeira'=>$cadeira->id,
							'id_horario'=>is_object($horario)?$horario->id:0,
							'diaSemana'=>$dia,
							'inputHoraInicio'=>$inicio,
							'inputHoraFim'=>$fim);

				if($horarios->cadeiraHorariosIntercecao($attr)) {
					$vsql="id_cadeira=$cadeira->id,
						inicio='".$inicio."',
						dia='".$dia."',
						fim='".$fim."'";

					if(is_object($horario)) {
						$vsql.=",id_alteracao=$usr->id,alteracao_data=now()";
						$sql->update($_p."parametros_cadeiras_horarios",$vsql,"where id=$horario->id");
						$rtn=array('success'=>true);
					} else {
						$vsql.=",id_usuario=$usr->id,data=now()";
						$sql->add($_p."parametros_cadeiras_horarios",$vsql);
						$rtn=array('success'=>true);
					}
				} else {
					$rtn=array('success'=>false,'error'=>$horarios->erro);
				}
			}
		} 

		else if($_POST['ajax']=="horariosListar") {


			$cadeira='';
			if(isset($_POST['id_cadeira']) and is_numeric($_POST['id_cadeira'])) {
				$sql->consult($_p."parametros_cadeiras","*","where id='".$_POST['id_cadeira']."'");
				if($sql->rows) {
					$cadeira=mysqli_fetch_object($sql->mysqry);
				}
			}



			$horarios=array();
			if(is_object($cadeira)) {

				$_colaboradores=array();
				$sql->consult($_p."colaboradores","id,nome","where lixo=0");
				while ($x=mysqli_fetch_object($sql->mysqry)) {
					$_colaboradores[$x->id]=$x;
				}

				$_horariosProfissionais=array();
				$sql->consult($_p."profissionais_horarios","*","where id_cadeira=$cadeira->id and lixo=0");
				if($sql->rows) {
					while($x=mysqli_fetch_object($sql->mysqry)) {
						$_horariosProfissionais[]=$x;
					}
				}

				$sql->consult($_p."parametros_cadeiras_horarios","*,date_format(inicio,'%H:%i') as inicio,
															date_format(fim,'%H:%i') as fim","where id_cadeira=$cadeira->id and lixo=0 order by inicio asc");
				if($sql->rows) {
					while($x=mysqli_fetch_object($sql->mysqry)) {

						$profissionaisHorario='';
						$profissionaisHorarioIds=array();

					
						$inpInicio=strtotime($x->inicio);
						$inpFim=strtotime($x->fim);


						foreach($_horariosProfissionais as $h) {

							if(isset($profissionaisHorarioIds[$h->id_profissional]) or $h->dia!=$x->dia) break;
							$hInicio=strtotime($h->inicio);
							$hFim=strtotime($h->fim);
							//echo $x->inicio."-".$x->fim."->\n";

							$intercede=false;
							$intercedeHorario="";
							
							if($inpInicio<$hInicio and $inpInicio<$hFim and $inpFim>$hInicio and $inpFim<$hFim) { 
								//echo 1;
								$intercede=true;
							} else if($inpInicio>=$hInicio and $inpInicio<$hFim and $inpFim>$hInicio and $inpFim<=$hFim) {
								//echo 2;
								$intercede=true;
							} else if($inpInicio>=$hInicio and $inpInicio<$hFim and $inpFim>$hInicio and $inpFim>$hFim) { 
								//echo 3;
								$intercede=true;
							}


							if($intercede===true) {
								$profissionaisHorario.=date('H:i',strtotime($h->inicio))." - ".date('H:i',strtotime($h->fim)).": ".nome(utf8_encode($_colaboradores[$h->id_profissional]->nome),2)."<br />";
								$profissionaisHorarioIds[$h->id_profissional]=1;
								//break;
							}
						}

						

						$horarios[$x->dia][]=array('id'=>$x->id,
													'id_cadeira'=>$x->id_cadeira,
													'cadeira'=>utf8_encode($cadeira->titulo),
													'dia'=>$x->dia,
													'inicio'=>$x->inicio,
													'fim'=>$x->fim,
													'profissionaisHorario'=>empty($profissionaisHorario)?'Nenhum profissional':$profissionaisHorario
													);
						
					}

				}
			} 

			

			$rtn=array('success'=>true,'horarios'=>$horarios );
		} 

		else if($_POST['ajax']=="horariosEditar") {
			$horario='';
			if(isset($_POST['id_horario']) and is_numeric($_POST['id_horario'])) {
				$sql->consult($_p."parametros_cadeiras_horarios","*,date_format(inicio,'%H:%i') as inicio,
																date_format(fim,'%H:%i') as fim","where id='".$_POST['id_horario']."'");
				if($sql->rows) {
					$horario=mysqli_fetch_object($sql->mysqry);
				}
			}

			if(is_object($horario)) {

				$rtn=array('success'=>true,
							'id'=>$horario->id,
							'id_cadeira'=>$horario->id_cadeira,
							'inicio'=>$horario->inicio,
							'fim'=>$horario->fim,
							'dia'=>$horario->dia);
			} else {
				$rtn=array('success'=>false,'error'=>'Horário não encontrado!');
			}
		} 

		else if($_POST['ajax']=="horariosRemover") {
			$horario='';
			if(isset($_POST['id_horario']) and is_numeric($_POST['id_horario'])) {
				$sql->consult($_p."parametros_cadeiras_horarios","*,date_format(inicio,'%H:%i') as inicio,
																date_format(fim,'%H:%i') as fim","where id='".$_POST['id_horario']."'");
				if($sql->rows) {
					$horario=mysqli_fetch_object($sql->mysqry);
				}
			}

			if(is_object($horario)) {
				$sql->update($_p."parametros_cadeiras_horarios","lixo=$usr->id,lixo_data=now()","where id=$horario->id");

				$rtn=array('success'=>true);
			} else {
				$rtn=array('success'=>false,'error'=>'Horário não encontrado!');
			}
		}

		header("Content-type: application/json");
		echo json_encode($rtn);
		die();
	}
	include "includes/header.php";
	include "includes/nav.php";

	$values=$adm->get($_GET);
	$campos=explode(",","tipo_pessoa,nome,cpf,razao_social,nome_fantasia,cnpj,telefone1,telefone2,email,endereco,lat,lng,complemento");

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

							$('.js-horarios-remover').hide();

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
											horariosAtualizar();

											$('.js-fieldset-horarios,.js-btn-remover').show();

											$(".aside").fadeIn(100,function() {
												$(".aside .aside__inner1").addClass("active");
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
								})

								

							} else {

								$('.js-fieldset-horarios,.js-btn-remover').hide();

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
						//$sql->consultPagMto2($_table,"*,IF(tipo_pessoa='PJ',razao_social,nome_fantasia) as titulo",10,$where." order by titulo","",15,"pagina",$_page."?".$url."&pagina=");
						$sql->consult($_table,"*,IF(tipo_pessoa='PJ',nome_fantasia,nome) as titulo",$where." order by titulo");
						if($sql->rows==0) {
							if(isset($values['busca'])) $msg="Nenhum Resultado encontrado";
							else $msg="Nenhum colaborador cadastrado";
							echo "<center>$msg</center>";
						} else {
							$registros=array();
							while($x=mysqli_fetch_object($sql->mysqry)) {
								$registros[$x->titulo]=$x;
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
									
					<dl>
						<dt>Tipo</dt>
						<dd>
							<label><input type="radio" name="tipo_pessoa" value="pf" checked onclick="$('.js-pessoa').hide(); $('.js-pessoa-pf').show();">Pessoa Física</label>
							<label><input type="radio" name="tipo_pessoa" value="pj" onclick="$('.js-pessoa').hide(); $('.js-pessoa-pj').show();" />Pessoa Jurídica</label>
						</dd>
					</dl>
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
								<dd><input type="text" name="nome" /></dd>
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
							<dd class="form-comp"><span>BR</span><input type="tel" name="" /></dd>
						</dl>
						<dl>
							<dt>Telefone</dt>
							<dd class="form-comp"><span>BR</span><input type="tel" name="" /></dd>
						</dl>
						<dl>
							<dt>Email</dt>
							<dd><input type="email" name="email" /></dd>
						</dl>
					</div>					
					<dl>
						<dt>Endereço</dt>
						<dd><input type="text" name="endereco" /></dd>
					</dl>
					<dl>
						<dt>Complemento</dt>
						<dd><input type="text" name="complemento" /></dd>
					</dl>
				</fieldset>


				<script type="text/javascript">
					var horarios = [];

					const horariosListar = () => {
						if(horarios) {
							$('.js-td').html('')
							for(var dia in horarios) {
								horarios[dia].forEach(x=>{
									
									/*$(`.js-${dia}`).append(`<div class="js-horario">${x.inicio}  - ${x.fim}<br /><a href="javascript:;" data-id="${x.id}" class="js-editar registros__acao registros__acao_sec"><i class="iconify" data-icon="bx-bx-download"></i></a>
															<a href="javascript:;" data-id="${x.id}" class="js-remover registros__acao registros__acao_sec"><i class="iconify" data-icon="bx-bx-trash"></i></a><div>`);*/
									$(`.js-${dia}`).append(`<a href="javascript:;" class="js-editar tooltiph" title="${x.profissionaisHorario}" data-id="${x.id}" >${x.inicio}~${x.fim}</a><br />`);
								})
							}
							 $(".tooltiph").tooltipster({theme:"borderless",contentAsHTML:true});
							
							
						}
					}
					const horariosAtualizar = () => {
						let id_cadeira=$('#js-aside input[name=id]').val();
						let data = `ajax=horariosListar&id_cadeira=${id_cadeira}`;
						$.ajax({
							type:"POST",
							data:data,
							success:function(rtn) {
								if(rtn.success) {
									horarios=rtn.horarios;

									if(rtn.carga) {
										$('.js-carga').val(rtn.carga);
									}
									horariosListar();
								}
							}
						})
					}
					
					const horarioEditar = (id_horario) => {
						let data = `ajax=horariosEditar&id_horario=${id_horario}`;
						var horarioObj = [];
						$.ajax({
							type:"POST",
							data:data,
							success:function(rtn) {
								if(rtn.success) {

									$(`.js-id`).val(rtn.id);
									$(`.js-dia`).val(rtn.dia);
									$(`.js-inicio`).val(rtn.inicio);
									$(`.js-fim`).val(rtn.fim);
									$('.js-horarios-submit').html(`<i class="iconify" data-icon="fluent:checkmark-12-filled"></i>`);

									$('.js-horarios-remover').show();
								}
							}
						});
					}
					$(function(){

						
						$('.js-horarios-submit').click(function(){
							let obj = $(this);

							if(obj.attr('data-loading')==0) {

								let id = $(`.js-id`).val();
								let dia = $(`.js-dia`).val();
								let inicio = $(`.js-inicio`).val();
								let fim = $(`.js-fim`).val();
								let id_cadeira=$('#js-aside input[name=id]').val();

								errInicio = validaHoraMinuto(inicio);
								errFim = validaHoraMinuto(fim);

								if(dia.length==0) {
									swal({title: "Erro!", text: "Selecione o Dia!", type:"error", confirmButtonColor: "#424242"});
								} else if(errInicio.length>0) {
									swal({title: "Erro!", text: `Erro na hora início: ${errInicio}`, type:"error", confirmButtonColor: "#424242"});
								} else if(errFim.length>0) {
									swal({title: "Erro!", text: `Erro na hora final: ${errFim}`, type:"error", confirmButtonColor: "#424242"});
								} else {

									obj.html(`<span class="iconify" data-icon="eos-icons:loading"></span>`);
									obj.attr('data-loading',1);

									let data = `ajax=horariosPersistir&id_cadeira=${id_cadeira}&dia=${dia}&inicio=${inicio}&fim=${fim}&id=${id}`;
									$.ajax({
										type:'POST',
										data:data,
										success:function(rtn) {
											if(rtn.success) {
												horariosAtualizar();	

												$(`.js-id`).val(0);
												$(`.js-dia`).val('');
												$(`.js-fim`).val('');
												$(`.js-inicio`).val('');
												$(`.js-horarios-cancelar`).hide();
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
										$('.js-horarios-remover').hide();
										obj.html(`<i class="iconify" data-icon="fluent:add-circle-24-regular"></i>`);
										obj.attr('data-loading',0);
									});

								}
							}
						})

						$('.js-horario-table').on('click','.js-editar',function(){
							let id = $(this).attr('data-id');
							horarioEditar(id);
						});

						

						$('.js-fieldset-horarios').on('click','.js-horarios-remover',function(){
							let obj = $(this);

							if(obj.attr('data-loading')==0) {

								let id_horario = $('.js-id').val();
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
											let data = `ajax=horariosRemover&id_horario=${id_horario}`; 
											$.ajax({
												type:"POST",
												data:data,
												success:function(rtn) {
													if(rtn.success) {
														$(`.js-id`).val(0);
														$(`.js-dia`).val('');
														$(`.js-fim`).val('');
														$(`.js-inicio`).val('');
														horariosAtualizar();
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
												$('.js-horarios-remover').hide();
												obj.html('<i class="iconify" data-icon="fluent:delete-24-regular"></i>');
												obj.attr('data-loading',0);
												$(`.js-horarios-submit`).html(`<i class="iconify" data-icon="fluent:add-circle-24-regular"></i>`);
											});
										} else {   
											swal.close();   
										} 
									});
							}
						});

					});
				</script>

				<fieldset class="js-fieldset-horarios">
					<legend>Horário de Funcionamento</legend>

					<input type="hidden" class="js-id" value="0" />
					<div class="colunas4">
						<dl>
							<dt>Dia da Semana</dt>
							<dd>
								<select  class="js-dia">
									<option value="">-</option>
									<?php
									for($i=0;$i<=6;$i++) {
										echo '<option value="'.$i.'">'.$_dias[$i].'</option>';	
									}
									?>
								</select>
							</dd>
						</dl>
						<dl>
							<dt>Início</dt>
							<dd class="form-comp">
								<span><i class="iconify" data-icon="fluent:clock-24-regular"></i></span>
								<input type="text" name="inicio" class="hora js-inicio" />
							</dd>
						</dl>
						<dl>
							<dt>Fim</dt>
							<dd class="form-comp">
								<span><i class="iconify" data-icon="fluent:clock-24-regular"></i></span>
								<input type="text" name="fim" class="hora js-fim" />
							</dd>
						</dl>
						<dl>
							<dt></dt>
							<dd>
								<button type="button" class="button button_main js-horarios-submit" data-loading="0"><i class="iconify" data-icon="fluent:add-circle-24-regular"></i></button>
								<a href="javascript:;" class="button js-horarios-remover" data-loading="0" style="display:none;"><i class="iconify" data-icon="fluent:delete-24-regular"></i></a>
							</dd>
						</dl>
					</div>
					<div class="list2">
						<table class="js-horario-table">
							<thead>
								<tr>
									<?php
									for($i=0;$i<=6;$i++) {
										echo '<th style="width:14.285%">'.$_dias[$i].'</th>';	
									}
									?>
								</tr>
							</thead>
							<tbody>
								<tr style="font-size:12px">
									<?php
									for($i=0;$i<=6;$i++) {
										echo '<td class="js-td js-'.$i.'"></td>';	
									}
									?>
								</tr>
							</tbody>
						</table>
						<?php /*<table> 
							<thead>
								<tr>
									<th style="width:14.285%">DOM</th>
									<th style="width:14.285%">SEG</th>
									<th style="width:14.285%">TER</th>
									<th style="width:14.285%">QUA</th>
									<th style="width:14.285%">QUI</th>
									<th style="width:14.285%">SEX</th>
									<th style="width:14.285%">SÁB</th>
								</tr>
							</thead>
							<tbody>
								<tr style="font-size:12px">
									<td></td>
									<td><a href="">08:00~12:00</a><br /><a href="">14:00~18:00</a></td>
									<td><a href="">08:00~12:00</a><br /><a href="">14:00~18:00</a></td>
									<td><a href="">08:00~12:00</a><br /><a href="">14:00~18:00</a></td>
									<td><a href="">08:00~12:00</a><br /><a href="">14:00~18:00</a></td>
									<td><a href="">08:00~12:00</a><br /><a href="">14:00~18:00</a></td>
									<td><a href="">09:00~13:00</a></td>
								</tr>
							</tbody>
						</table>*/?>
					</div>
				</fieldset>
			
			</form>

		</div>
	</section><!-- .aside -->

<?php 
include "includes/footer.php";
?>	