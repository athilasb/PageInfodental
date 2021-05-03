<?php
	if(isset($_POST['ajax'])) {
		require_once("lib/conf.php");
		require_once("usuarios/checa.php");

		if($_POST['ajax']=="ordem") {
			$rtn=array();
			$sql = new Mysql();
			if(isset($_POST['id_anamnese']) and is_numeric($_POST['id_anamnese'])) {
				$sql->consult($_p."parametros_anamnese","*","where id='".$_POST['id_anamnese']."'");
				if($sql->rows) {
					$p=mysqli_fetch_object($sql->mysqry);
					if(isset($_POST['ordem']) and is_numeric($_POST['ordem'])) {
						$vSQL="ordem='".$_POST['ordem']."'";
						$vWHERE="where id=$p->id";
						$sql->update($_p."parametros_anamnese_formulario",$vSQL,$vWHERE);
						$rtn['success']=true;

					}
				} else {
					$rtn['error']="Pergunta não encontrada!";
				}
			}
		} else if($_POST['ajax']=="persistirOrdem") {
			if(isset($_POST['ordem']) and !empty($_POST['ordem'])) {
				$ordem=explode(",",$_POST['ordem']);
				if(is_array($ordem) and count($ordem)>0) {
					$aux=1;
					foreach($ordem as $idItem) {
						if(is_numeric($idItem)) {
							$sql->update($_p."parametros_anamnese_formulario","ordem=$aux","where id=$idItem");
							$aux++;
						}
					}
					$rtn=array('success'=>true);
				}
			} else {
				$rtn=array('error'=>'Ordem não definida!');
			}
		} else {
			$rtn['error']="Pergunta não definida!";
		}


		header('Content-Type: application/json');

		echo json_encode($rtn);
		die();
	}
	if(isset($_GET['box'])) {
		require_once("lib/conf.php");
		require_once("usuarios/checa.php");
	
		$sql = new Mysql();
		$jsc = new Js();
		$anamnese='';
		if(isset($_GET['id_anamnese']) and is_numeric($_GET['id_anamnese'])) {
			$sql->consult($_p."parametros_anamnese","*","where id=".$_GET['id_anamnese']." and lixo=0");
			if($sql->rows) {
				$anamnese=mysqli_fetch_object($sql->mysqry);
			}
		}

		if(empty($anamnese)) {
			echo '<div style="padding:100px"><center>Formulário não encontrado!</center></div>';
			die();
		}
		$campos=explode(",","pergunta,tipo,pub");

		foreach($campos as $v) $values[$v]='';
		$values['tipo']="nota";
		$values['pub']=1;

		$pergunta='';
		if(isset($_GET['id_formulario']) and is_numeric($_GET['id_formulario'])) {
			$sql->consult($_p."parametros_anamnese_formulario","*","where id=".$_GET['id_formulario']." and id_anamnese=$anamnese->id and lixo=0");
			if($sql->rows) {
				$pergunta=mysqli_fetch_object($sql->mysqry);

				$values['pergunta']=utf8_encode($pergunta->pergunta);
				$values['tipo']=($pergunta->tipo);
				$values['pub']=($pergunta->pub);

			} else {
				$jsc->jAlert("Pergunta não encontrado!","erro","$.fancybox.close();");
				die();
			}
		}


		?>
		<script src="js/jquery.validacao.js"></script>
		<section class="modal" style="height:auto; width:950px;">

			<header class="modal-header">
				<div class="filtros">
					<h1 class="filtros__titulo">Anamnese</h1>
					
					<div class="filtros-acoes">
						<button type="button" class="principal btn-submit"><i class="iconify" data-icon="bx-bx-check"></i></button>
						<button type="button" class="js-remover"><i class="iconify" data-icon="bx-bx-trash"></i></button>
					</div>
				</div>
			</header>

			<article class="modal-conteudo">
					<form method="post" class="modal form formulario-validacao js-formulario" style="height: auto">
						<input type="hidden" name="acao" value="formulario" />
						<input type="hidden" name="id_formulario" value="<?php echo is_object($pergunta)?$pergunta->id:0;?>" />
						<input type="hidden" name="id_anamnese" value="<?php echo $anamnese->id;?>" />

						<fieldset>
							<legend><span class="badge">1</span> Defina a pergunta</legend>
							<div class="colunas4">
								<dl class="dl3">
									<dt>Pergunta</dt>
									<dd><input type="text" name="pergunta" class="obg" value="<?php echo $values['pergunta'];?>" /></dd>
								</dl>	
								<dl>
									<dd>
										<label><input type="checkbox" name="pub" value="1" class="input-switch"<?php echo $values['pub']==1?" checked":"";?> /> Pergunta publicada</label>
									</dd>
								</dl>
							</div>
							<?php
							if(is_object(($pergunta))) {

							?>
							<script>
								$(function(){
									$('input[name=tipo]').prop('disabled',true);
								});
							</script>
							<?php
							}
							?>
							<dl>
								<dt>Tipo</dt>
								<dd>
									<label><input type="radio" name="tipo" value="nota"<?php echo $values['tipo']=="nota"?" checked":"";?> /> Nota (0-10)</label>
									<label><input type="radio" name="tipo" value="discursiva"<?php echo $values['tipo']=="discursiva"?" checked":"";?> /> Discursiva</label>
									<label><input type="radio" name="tipo" value="radiobox"<?php echo $values['tipo']=="radiobox"?" checked":"";?> /> Múltipla Escolha</label>
									<label><input type="radio" name="tipo" value="checkbox"<?php echo $values['tipo']=="checkbox"?" checked":"";?> /> Caixas de Seleção</label>
								</dd>
							</dl>
						</fieldset>
						<script type="text/javascript">
							$(function(){
								$('.btn-submit').click(function(){
									$('form.js-formulario').submit();
								})
								$('input[name=tipo]').click(function(){
									if($(this).val()=="nota" || $(this).val()=="discursiva") {
										$('.js-opcoes').hide().find('input').removeClass('obg');
									} else {
										$('.js-opcoes').show().find('input').addClass('obg');
									}
								});

								$('.js-opcoes-dl').on('click','.js-opcao-deleta',function(){
									$(this).parent().parent().remove();
							
								})
								$('input[name=tipo]:checked').trigger('click');

								var htmlOpcao = `<dl>
													<dd>
														<input type="text" name="opcao[]" class="obg" style="width:60%" />
														<label><input type="checkbox" name="alerta[]" value="1" /> Alerta de anamnese</input></label>
														<input type="hidden" name="id_opcao[]" value="0" /> 
														<a href="javascript:;" class="botao js-opcao-deleta button__sec button"><i class="iconify" data-icon="bx-bx-trash"></i></a>
													</dd>
												</dl>`;
								$('a.js-opcoes-add').click(function(){
									$('.js-opcoes-dl').append(htmlOpcao);
								});
							})
						</script>
						<fieldset class="js-opcoes">
							<legend><span class="badge">2</span> Defina as opções desta pergunta</legend>

							<div class="js-opcoes-dl">
								<?php
								if(is_object($pergunta) and ($pergunta->tipo=="checkbox" or $pergunta->tipo=="radiobox")) {
									$sql->consult($_p."parametros_anamnese_formulario_opcoes","*","where id_formulario=$pergunta->id and lixo=0");
									if($sql->rows) {
										$aux=0;
										while($x=mysqli_fetch_object($sql->mysqry)) {
											if($aux++==0) $aJsDeleta='';
											else $aJsDeleta='<a href="javascript:;" class="botao js-opcao-deleta button__sec button"><i class="iconify" data-icon="bx-bx-trash"></i></a>';
								?>
								<dl>
									<dd>
										<input type="text" name="opcao[]" class="obg " style="width:60%" value="<?php echo utf8_encode($x->opcao);?>" />
										<label><input type="checkbox" name="alerta[]" value="1"<?php echo $x->alerta==1?" checked":"";?> /> Alerta de anamnese</input></label>
										<input type="hidden" name="id_opcao[]" value="<?php echo $x->id;?>" /> <?php echo $aJsDeleta;?>
									</dd>
								</dl>
								<?php			
										}
									}
								} else {
								?>
								<dl>
									<dd>
										<input type="text" name="opcao[]" class="obg" style="width:60%" />
										<label><input type="checkbox" name="alerta[]" value="1" /> Alerta de anamnese</input></label>
										<input type="hidden" name="id_opcao[]" value="0" />
									</dd>
								</dl>
								<?php
								}
								?>
							</div>

							<dl>
								<dd><a href="javascript:;" class="js-opcoes-add"><i class="iconify" data-icon="bx-bx-plus"></i> nova opção</a></dd>
							</dl>
							
						</fieldset>
					</form>
				
			</article>

		</section>

		<?php
		die();
	}



	$title="";
	include "includes/header.php";
	include "includes/nav.php";

	if($usr->tipo!="admin" and !in_array("parametros",$_usuariosPermissoes)) {
		$jsc->jAlert("Você não tem permissão para acessar esta área!","erro","document.location.href='dashboard.php'");
		die();
	}
	$values=$adm->get($_GET);



	$_avaliacaoTipos=array('nota'=>'Nota',
							'discursiva'=>'Discursiva',
							'radiobox'=>'Múltipla Escolha',
							'checkbox'=>'Caixas de Seleção');
?>
<section class="content">

	<?php /*<header class="caminho">
		<h1 class="caminho__titulo">Configurações <i class="iconify" data-icon="bx-bx-chevron-right"></i> <strong>Planos</strong></h1>
		<a href="javascript:;" class="caminho__tutorial button button__sec"><i class="iconify" data-icon="ic-baseline-slow-motion-video"></i> ASSISTIR TUTORIAL</a>
	</header>*/?>

	<section class="content">

		<?php
		require_once("includes/abaConfiguracoes.php");
		?>
		
		<?php
			$_table=$_p."parametros_anamnese";
			$_page=basename($_SERVER['PHP_SELF']);

			if(isset($_GET['form'])) {
				$cnt='';
				$campos=explode(",","titulo");
				
				foreach($campos as $v) $values[$v]='';
				
				if(isset($_GET['edita']) and is_numeric($_GET['edita'])) {
					$sql->consult($_table,"*","where id='".$_GET['edita']."'");
					if($sql->rows) {
						$cnt=mysqli_fetch_object($sql->mysqry);
						
						$values=$adm->values($campos,$cnt);
					} else {
						$jsc->jAlert("Informação não encontrada!","erro","document.location.href='".$_page."'");
						die();
					}
				}
				if(isset($_POST['acao'])) {

					if($_POST['acao']=="wlib") {
						$vSQL=$adm->vSQL($campos,$_POST);
						$values=$adm->values;
					 	$processa=true;
						
						if($processa===true) {	
						
							if(is_object($cnt)) {
								$vSQL=substr($vSQL,0,strlen($vSQL)-1);
								$vWHERE="where id='".$cnt->id."'";
								$sql->update($_table,$vSQL,$vWHERE);
								$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='update',vsql='".addslashes($vSQL)."',vwhere='".addslashes($vWHERE)."',tabela='".$_table."',id_reg='".$cnt->id."'");
								$id_reg=$cnt->id;
							} else {
								$vSQL=substr($vSQL,0,strlen($vSQL)-1);
								$sql->add($_table,$vSQL);
								$id_reg=$sql->ulid;
								$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='insert',vsql='".addslashes($vSQL)."',tabela='".$_table."',id_reg='".$sql->ulid."'");

							}

							$msgErro='';
							if(isset($_FILES['foto']) and !empty($_FILES['foto']['tmp_name'])) {
								$up=new Uploader();
								$up->uploadCorta("Imagem Inicial",$_FILES['foto'],"",5242880*2,$_width,'',$_dir,$id_reg);

								if($up->erro) {
									$msgErro=$up->resul;
								} else {
									$ext=$up->ext;
									$vSQL="foto='".$ext."'";
									$vWHERE="where id='".$id_reg."'";
									$sql->update($_table,$vSQL,$vWHERE);
									$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='update',vsql='".addslashes($vSQL)."',tabela='".$_table."',id_reg='".$id_reg."'");
								}
							}
							if(!empty($msgErro)) {
								$jsc->jAlert($msgErro,"erro","");
							} else {
								$jsc->go($_page."?form=1&edita=$id_reg&".$url);
								die();
							}
						}
					} else if($_POST['acao']=="formulario") {
						$pergunta='';
						if(isset($_POST['id_formulario']) and is_numeric($_POST['id_formulario']) and $_POST['id_formulario']>0) {
							$sql->consult($_table."_formulario","*","where id=".$_POST['id_formulario']." and lixo=0");
							if($sql->rows) {
								$pergunta=mysqli_fetch_object($sql->mysqry);
							}
						}

						if(is_object($pergunta)) {

							$vSQL="id_anamnese=".$_POST['id_anamnese'].", pergunta='".utf8_decode(strtoupperWLIB(addslashes($_POST['pergunta'])))."'";
							$vSQL.=",pub='".((isset($_POST['pub']) and $_POST['pub']==1)?1:0)."'";
						

							$sql->update($_table."_formulario",$vSQL,"where id=$pergunta->id");
							$id_formulario=$pergunta->id;

						//	var_dump($_POST['opcao']);die();
							if(isset($_POST['opcao']) and is_array($_POST['opcao']) and count($_POST['opcao'])>0) {
								$vSQLOpcoes=array('update'=>array(),'insert'=>array());
								$aux=0;
								foreach($_POST['opcao'] as $v) {
									if(!empty($v)) {
										if($_POST['id_opcao'][$aux]>0) {
											$vSQLOpcoes['update'][$_POST['id_opcao'][$aux]]="id_formulario=$id_formulario,
																								opcao='".utf8_decode(strtoupperWLIB(addslashes($v)))."',
																								lixo=0,
																								alerta='".((isset($_POST['alerta'][$aux]) and $_POST['alerta'][$aux]==1)?1:0)."'";
										} else {
											$vSQLOpcoes['insert'][]="id_formulario=$id_formulario,
																		opcao='".utf8_decode(strtoupperWLIB(addslashes($v)))."',
																		lixo=0,
																		alerta='".((isset($_POST['alerta'][$aux]) and $_POST['alerta'][$aux]==1)?1:0)."'";
										}
										$aux++;
									}
								}
								$sql->update($_table."_formulario_opcoes","lixo=1","where id_formulario=$pergunta->id");
								if(count($vSQLOpcoes['insert'])>0) {
									foreach($vSQLOpcoes['insert'] as $v) {
										//echo $v."<Br>";
										$sql->add($_table."_formulario_opcoes",$v);
									}
								}
								if(count($vSQLOpcoes['update'])>0) {
									foreach($vSQLOpcoes['update'] as $k=>$v) {
										//echo $v." up<Br>";
										$sql->update($_table."_formulario_opcoes",$v,"where id=$k and id_formulario=$pergunta->id");
									}
								}
							}

							//echo "edit";die();
						} else {

							$vSQL="id_anamnese=".$_POST['id_anamnese'].", pergunta='".utf8_decode(strtoupperWLIB(addslashes($_POST['pergunta'])))."',tipo='".$_POST['tipo']."'";
							$vSQL.=",pub='".((isset($_POST['pub']) and $_POST['pub']==1)?1:0)."'";
						
							$sql->add($_table."_formulario",$vSQL);
							$id_formulario=$sql->ulid;
							if(isset($_POST['opcao']) and is_array($_POST['opcao'])) {
								$vSQLOpcoes=array();
								foreach($_POST['opcao'] as $v) {
									$vSQLOpcoes[]="id_formulario=$id_formulario,opcao='".addslashes(utf8_decode(strtoupperWLIB(addslashes($v))))."'";
								}
								if(count($vSQLOpcoes)>0) {
									foreach($vSQLOpcoes as $v) {
										$sql->add($_table."_formulario_opcoes",$v);
									}
								}
							}
						}


						//$jsc->jAlert("Informações salvas com sucesso!","sucesso","document.location.href='?".$url."&form=1&edita=".$cnt->id."'");
						$jsc->go($_page."?form=1&edita=$cnt->id&".$url);
						die();
					}
				}	
			?>

			<form method="post" class="form formulario-validacao"  autocomplete="off" enctype="multipart/form-data">
				<input type="hidden" name="acao" value="wlib" />
			
			
				<section class="grid" style="padding:2rem;">
					<div class="box">

						<div class="filter">
							<div class="filter-group">
								<div class="filter-button">
									<a href="<?php echo $_page;?>"><i class="iconify" data-icon="bx-bx-left-arrow-alt"></i></a>
								</div>
							</div>
							<div class="filter-group">
								<div class="filter-title">
									Anamnese
								</div>
							</div>
							<div class="filter-group filter-group_right">
								<div class="filter-button">
									<a href="<?php echo $_page;?>?deleta=<?php echo $cnt->id."&".$url;?>" class="js-deletar"><i class="iconify" data-icon="bx-bx-trash"></i></a>
									<a href="javascript:window.print();"><i class="iconify" data-icon="bx-bx-printer"></i></a>
									<a href="javascript:;" class="azul js-btn-salvar"><i class="iconify" data-icon="bx-bx-check"></i><span>salvar</span></a>
								</div>
							</div>
						</div>


						<fieldset>
							<legend><span class="badge">1</span> Defina o título da Anamnese</legend>
							<dl>
								<dt>Título</dt>
								<dd>
									<input type="text" name="titulo" value="<?php echo $values['titulo'];?>" class="obg" />
								</dd>
							</dl>
						</fieldset>

						<?php
						if(is_object($cnt)) {
							if(isset($_GET['deleta']) and is_numeric($_GET['deleta']) and $usr->tipo=="admin") {
								$vSQL="lixo='1'";
								$vWHERE="where id='".$_GET['deleta']."'";
								$sql->update($_table."_formulario",$vSQL,$vWHERE);
								$sql->add($_p."log","data=now(),id_usuario='".$usr->user_id."',tipo='delete',vsql='".addslashes($vSQL)."',vwhere='".addslashes($vWHERE)."',tabela='".$_table."_formulario',id_reg='".$_GET['deleta']."'");

								$jsc->jAlert("Pegunta excluída com sucesso!","sucesso","document.location.href='".$_page."?".$url."&form=1&edita=$cnt->id'");
								die();
							}
						?>
						<script>
							const persistirOrdem = () => {
								let ordem = [];
								$(`.js-perguntas tbody tr`).each(function(index,elem){
									
									ordem.push($(elem).attr('data-id_formulario'));
								});

								let data = `ajax=persistirOrdem&ordem=${ordem}`;
								$.ajax({
									type:"POST",
									data:data,
									success:function(rtn) {
										console.log(rtn);
									},
									error:function() {

									}
								})
							}
							$(function(){
								$('.js-ordem').change(function(){
									let ordem=$(this).val();
									let id_formulario=$(this).attr('data-id_formulario');
									let id_anamnese='<?php echo $cnt->id;?>';

									$.ajax({
										type:'POST',
										data:`ajax=ordem&id_anamnese=${id_anamnese}&ordem=${ordem}&id_formulario=${id_formulario}`,
										success:function(rtn) {
											if(rtn.success) {
												document.location.href='<?php echo "$_page?$url&form=1&edita=$cnt->id";?>';
											} else if(rtn.error) {
												alert(rtn.error);
											} else {
												alert('Erro ao atualizar a ordem!');
											}
										}
									});
								});


								$('.js-btn-novaPergunta').click(function(){
									$.fancybox.open({
										type:"ajax",
										src:"<?php echo $_page;?>?box=1&id_anamnese=<?php echo $cnt->id;?>",
									});
									return false;
								});

								$('.js-pergunta').click(function(){ 
									let id_formulario = $(this).attr('data-id_formulario');
									let id_anamnese = $(this).attr('data-id_anamnese');

									$.fancybox.open({
										type:"ajax",
										src:`?box=1&id_anamnese=${id_anamnese}&id_formulario=${id_formulario}`
									})
								});
								$(".js-perguntas tbody").sortable({
									cursor: "move",
									placeholder: "sortable-placeholder",
									helper: function(e, tr) {
											var $originals = tr.children();
											var $helper = tr.clone();
											$helper.children().each(function(index) {$(this).width($originals.eq(index).width());});
											return $helper;
										},
									stop:function(event,ui) {
										let id_formulario = $(ui.item).attr('data-id_formulario');
										persistirOrdem(id_formulario); 
									}
								}).disableSelection();
							})
						</script>


						<fieldset class="registros">
							<legend><span class="badge">2</span> Defina as perguntas</legend>
							<div class="filtros">
								
								<div class="filter-group">
									<div class="filter-button">
										<button class="button js-btn-novaPergunta"><i class="iconify" data-icon="bx-bx-plus"></i>Nova Pergunta</button>
									</div>
								</div>

							</div>
							
							<table class="js-perguntas">
								<thead>
									<tr>
										<th style="width:20px;">Ordem</th>
										<th>Pergunta</th>
										<th>Tipo</th>
										<th>Publicado</th>
									</tr>
								</thead>
								<tbody>
								<?php
								$sql->consult($_table."_formulario","*","where id_anamnese=$cnt->id and lixo=0 order by ordem asc");
								if($sql->rows) {
									while($x=mysqli_fetch_object($sql->mysqry)) {
								?>
									<tr class="js-pergunta" data-id_formulario="<?php echo $x->id;?>" data-id_anamnese="<?php echo $cnt->id;?>">
										<td style="text-align: center;">
											<?php /*<input type="number" class="js-ordem" data-id_formulario="<?php echo $x->id;?>" value="<?php echo $x->ordem;?>" />*/?>
											<a href="javascript:;//" style="cursor: all-scroll;color:#ccc;text-decoration:none;border:none;" ><span class="iconify" data-icon="carbon:drag-vertical" data-inline="false" data-height="30"></span></a>
										</td>
										<td><?php echo utf8_encode($x->pergunta);?></td>
										<td><?php echo isset($_avaliacaoTipos[$x->tipo])?$_avaliacaoTipos[$x->tipo]:"-";?></td>
										<td><?php echo $x->pub==1?"<font color=green>Sim</font>":"<font color=red>Não</font>";?></td>
									</tr>
								<?php		
									}
								}
								?>
								</tbody>
							</table>

						</fieldset>
					<?php
					}
					?>
				</section>
				<?php	
				
				?>
				</section>
			</form>
			<script>
				$(function(){
					$('input.money').maskMoney({symbol:'', allowZero:true, showSymbol:true, thousands:'.', decimal:',', symbolStay: true});
				})
			</script>
				
			<?php
			} else {
			
				
			if(isset($_GET['deleta']) and is_numeric($_GET['deleta']) and $usr->tipo=="admin") {
				$vSQL="lixo='1'";
				$vWHERE="where id='".$_GET['deleta']."'";
				$sql->update($_table,$vSQL,$vWHERE);
				$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='delete',vsql='".addslashes($vSQL)."',vwhere='".addslashes($vWHERE)."',tabela='".$_table."',id_reg='".$_GET['deleta']."'");
				$jsc->jAlert("Registro excluído com sucesso!","sucesso","document.location.href='".$_page."?".$url."'");
				die();
			}
			
			$where="WHERE lixo='0'";
			if(isset($values['busca']) and !empty($values['busca'])) $where.=" and (titulo like '%".utf8_decode($values['busca'])."%')";
		

			$sql->consult($_table,"*",$where." order by titulo asc");
			
			?>
			<section class="grid">
				<div class="box registros">

					<div class="filter-group">
						<div class="filter-button">
							<a href="<?php echo $_page."?form=1&$url";?>" class="verde"><i class="iconify" data-icon="bx-bx-plus"></i><span> Nova Anamnese</span></a>
						</div>
					</div>


					<table class="tablesorter">
						<thead>
							<tr>
								<th>Título</th>								
							</tr>
						</thead>
						<tbody>
						<?php
						while($x=mysqli_fetch_object($sql->mysqry)) {
						?>
						<tr onclick="document.location.href='<?php echo $_page;?>?form=1&edita=<?php echo $x->id."&".$url;?>'">
							<td><?php echo utf8_encode($x->titulo);?></td>
						</tr>
						<?php
						}
						?>
						</tbody>
					</table>
				</div>
			</section>
			
			
			<?php
			}
			?>

</section>

<?php
	include "includes/footer.php";
?>