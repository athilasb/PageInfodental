<?php
	if(isset($_POST['ajax'])) {
		require_once("lib/conf.php");
		require_once("usuarios/checa.php");

		$rtn = array();

		if($_POST['ajax']=="wlib") {

			if(isset($_POST['id']) and is_numeric($_POST['id'])) {
				$sql->consult($_p."produtos","*","where id='".$_POST['id']."'");
				if($sql->rows) {
					$x=mysqli_fetch_object($sql->mysqry);


					if(isset($_POST['ncm'])) {
						$sql->update($_p."produtos","ncm='".addslashes($_POST['ncm'])."'","where id=$x->id");
						$rtn['success']=true;
					} else if(isset($_POST['cest'])) {
						$sql->update($_p."produtos","cest='".addslashes($_POST['cest'])."'","where id=$x->id");
						$rtn['success']=true;
					} else if(isset($_POST['id_categoria_imposto'])) {
						$sql->update($_p."produtos","id_categoria_imposto='".addslashes($_POST['id_categoria_imposto'])."'","where id=$x->id");
						$rtn['success']=true;
					} else {
						$rtn['error']="NCM/CEST não especificado!";
					}
				} else {
					$rtn['error']="Produto não encontrado";
				}
			} 
		} else if($_POST['ajax']=="unidadesAdicionar") {
			if(isset($_POST['unidade']) and is_numeric($_POST['unidade'])) { 
				if(isset($_POST['id_produto']) and is_numeric($_POST['id_produto'])) { 
					if(isset($_POST['contagem']) and !empty($_POST['contagem'])) { 

						$contagem = explode(",",$_POST['contagem']);
						$diasContagem=array();
						foreach($contagem as $v) {
							if(!empty($v) and is_numeric($v) and $v>=1 and $v<=7) $diasContagem[]=$v;
						}
						if(count($diasContagem)>0) {

							if(isset($_POST['entrega']) and is_numeric($_POST['entrega'])) { 

								$sql->consult($_p."produtos_unidades","*","where id_unidade='".$_POST['unidade']."' and id_produto='".$_POST['id_produto']."'");
								if($sql->rows) {
									$unid=mysqli_fetch_object($sql->mysqry);
								} else {
									$unid="";
								}

								$vSQL="id_produto='".$_POST['id_produto']."',
										id_unidade='".$_POST['unidade']."',
										contagem=',".implode(",",$diasContagem).",',
										entrega='".$_POST['entrega']."',
										lixo=0";
										

								if(is_object($unid)) {
									$sql->update($_p."produtos_unidades",$vSQL,"where id=$unid->id");
								} else {
									$sql->add($_p."produtos_unidades",$vSQL);
								}
								$rtn=array('success'=>true);

							} else {
								$rtn=array('success'=>false,'error'=>'Dias de Entrega não especifiado!');
							}
						} else {
							$rtn=array('success'=>false,'error'=>'Nenhum dia de contagem especificado.');
						}
					} else {
						$rtn=array('success'=>false,'error'=>'Nenhum dia de contagem especificado!');
					}
				} else {
					$rtn=array('success'=>false,'error'=>'Produto não especificado');
				} 
			} else {
				$rtn=array('success'=>false,'error'=>'Unidade não especificada!');
			} 	
		} else if($_POST['ajax']=="unidadesListar") {
			if(isset($_POST['id_produto']) and is_numeric($_POST['id_produto'])) {

				$unidadesExistentes=array();
				$sql->consult($_p."produtos_unidades","*","where id_produto='".$_POST['id_produto']."' and lixo=0");
				if($sql->rows) {
					$unidades=array();
					while($x=mysqli_fetch_object($sql->mysqry)) {

						$diasDeContagem="";
						$diasDeContagemArray=array();
						if(!empty($x->contagem)) {
							$contagem=explode(",",$x->contagem);
							foreach($contagem as $v) {
								if(isset($_optTurnoEscala[$v])) {
									$diasDeContagem.=$_optTurnoEscala[$v].", ";
									$diasDeContagemArray[]=$v;
								}
							}
						}
						$unidadesExistentes[]=$x->id_unidade;
						$diasDeContagem=strlen($diasDeContagem)>0?substr($diasDeContagem,0,-2):$diasDeContagem;
						$unidades[]=array('id'=>$x->id,
											'id_unidade'=>$x->id_unidade,
											'unidade'=>(isset($_optUnidades[$x->id_unidade])?utf8_encode($_optUnidades[$x->id_unidade]->titulo):"-"),
											'contagem'=>$diasDeContagem,
											'contagemID'=>$diasDeContagemArray,
											'entrega'=>$x->entrega);
					}
				}
				$selectUnidades=array();
				foreach($_optUnidades as $v) {
					if(!in_array($v->id,$unidadesExistentes)) {
						$selectUnidades[]=array('titulo'=>utf8_encode($v->titulo),'id'=>$v->id);
					}
				}
				$rtn=array('success'=>true,'unidades'=>$unidades,'selectUnidades'=>$selectUnidades);
			} else {
				$rtn=array('success'=>false,'error'=>'Produto não especificado!');
			}
		} else if($_POST['ajax']=="unidadesRemover") {
			if(isset($_POST['id_produto']) and is_numeric($_POST['id_produto'])) {
				if(isset($_POST['id']) and is_numeric($_POST['id'])) {
					$sql->consult($_p."produtos_unidades","*","where id='".$_POST['id']."' and id_produto='".$_POST['id_produto']."'");
					if($sql->rows) {
						$x=mysqli_fetch_object($sql->mysqry);
						$sql->update($_p."produtos_unidades","lixo=1","where id=$x->id");
						$rtn=array('success'=>true);
					} else {
						$rtn=array('success'=>false,'error'=>'Item não especificado.');
					}
				} else {
					$rtn=array('success'=>false,'error'=>'Item não especificado!');
				}
			} else {
				$rtn=array('success'=>false,'error'=>'Produto não especificado!');
			}
		} else {
			$rtn['error']="Produto não especificado!";
		}

		header('Content-Type: application/json');
		echo json_encode($rtn);
		die();
			
	}
	$title="";
	include "includes/header.php";
	include "includes/nav.php";
	if(!is_object($_session) or $_sessionPermission===false) {
		$jsc->jAlert("Você não tem permissão para acessar esta sessão!","erro", "document.location.href='dashboard.php'");
		die();
	}

	$_title=utf8_encode($_sessionMenu->titulo).' <i class="icon-angle-right"></i> '.utf8_encode($_session->titulo).' <i class="icon-angle-right"></i> Produtos';
	$_table=$_p."produtos";
	$_page=basename($_SERVER['PHP_SELF']);

	$_gruposOpicionais=array();
	$sql->consult($_table."_gruposdeopcionais","*","WHERE lixo=0 order by titulo asc");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_gruposOpicionais[$x->id]=$x;
	}

	$_setorProducao=array();
	$sql->consult($_p."producao_setoresdeproducao","*","WHERE lixo=0 order by titulo asc");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_setorProducao[$x->id]=$x;
	}

	$_cardapioCategorias=array();
	$sql->consult($_table."_categorias","*","WHERE lixo=0 order by titulo asc");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_cardapioCategorias[$x->id]=$x;
	}

	$_unidadeMedidas=array();
	$sql->consult($_table."_unidademedidas","*","order by titulo asc");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_unidadeMedidas[$x->id]=$x;
	}

	$_categoriaDeSubstituicao=array();
	$sql->consult($_table."_categoriasdesubstituicao","*","WHERE lixo=0 order by titulo asc");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_categoriaDeSubstituicao[$x->id]=$x;
	}

	$_categoriaImpostos=array();
	$sql->consult($_p."impostos_categorias","*","where lixo=0 order by titulo asc");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_categoriaImpostos[$x->id]=$x;
	}

	$_produtos=array();
	$sql->consult($_p."produtos","*","where lixo=0 order by codigo asc");
	if($sql->rows) {
		while($x=mysqli_fetch_object($sql->mysqry)) {
			$_produtos[$x->id]=$x;
		}
	}


	$estoque = new Estoque(array('prefixo'=>$_p,'usr'=>$usr));

	$campos=explode(",","tipo,titulo,codigo,unidade_medida,seguranca,contagem,abastecimento,id_imposto,id_setor,valor_nota,valor_transferencia,valor_lojas,id_categoria,tempo,valor_venda,texto_descricao,texto_nutricional,texto_alergico,texto_harmonizacao,composicao,ponto,adicional,quantidade,valor_adicional,id_categoria_substituicao,id_categoria_imposto,ncm,cest");
	$values=$adm->get($_GET);
	
?>
<script>
	$(function(){
		$('.money').maskMoney({decimal:',',thousands:'.',allowZero:true});
		$('.m-<?php echo $_sessionMenu->id;?>').next().show();

	});
</script>
<section id="conteudo">
	
	<div class="box-caminho">
		<a href="javascript" class="js-collapse"><span></span></a>
		<h1><?php echo $_title;?></h1>
	</div>
	
	
	<?php

	
	require_once("includes/aside_parametrosEstoque.php");
	if(isset($_GET['form'])) {
	
		$cnt='';
		
		
		foreach($campos as $v) $values[$v]='';
		$values['tipo']="materiaprima";
		$values['contagem']=array();
	
		
		if(isset($_GET['edita']) and is_numeric($_GET['edita'])) {
			$sql->consult($_table,"*","where id='".$_GET['edita']."' and lixo=0");
			if($sql->rows) {
				$cnt=mysqli_fetch_object($sql->mysqry);
				
				$values=$adm->values($campos,$cnt);
				$values['quantidade']=number_format($values['quantidade'],3,",","");
			} else {
				$jsc->jAlert("Produto não encontrado!","erro","document.location.href='".$_page."'");
				die();
			}
		} else if(isset($_GET['copy']) and is_numeric($_GET['copy'])) {
			$sql->consult($_table,"*","where id='".$_GET['copy']."' and lixo=0");
			if($sql->rows) {
				$x=mysqli_fetch_object($sql->mysqry);
				
				$values=$adm->values($campos,$x);
			} else {
				$jsc->jAlert("Produto não encontrado!","erro","document.location.href='".$_page."'");
				die();
			}
		}

		if(empty($cnt)) {
			$sql->consult($_table,"*","where lixo=0 order by id desc limit 1");
			if($sql->rows) {
				$x=mysqli_fetch_object($sql->mysqry);
				$values['codigo']=$x->id+1;
			}
		}
		if(isset($_POST['acao'])) {
			if($_POST['acao']=="wlib") {
				$_POST['quantidade']=str_replace(",",".",$_POST['quantidade']);
				$vSQL=$adm->vSQL($campos,$_POST);
				
			
				$values=$adm->values;
				
				//if(empty($cnt)) $vSQL.="code='".codeIn($_table,outUrl(utf8_encode($_POST['titulo'])))."',";
				//else $vSQL.="code='".codeIn2($_table,outUrl(utf8_encode($_POST['titulo'])),$cnt->id)."',";
					
				$processa=true;

				$msgErro='';
				if(empty($cnt) or(is_object($cnt) and $cnt->codigo!=$_POST['codigo'])) {
					$sql->consult($_table,"*","where codigo='".addslashes($_POST['codigo'])."' and lixo=0");
					if($sql->rows) {
						$x=mysqli_fetch_object($sql->mysqry);
						$msgErro="Já existe produto cadastrado com o código <b>".$_POST['codigo']."</b><br />".utf8_encode($x->titulo);
						$processa=false;
					}
				}	

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

					if(isset($_FILES['foto']) and !empty($_FILES['foto']['tmp_name'])) {
						$up=new Uploader();
						$up->uploadCorta("Foto",$_FILES['foto'],"",5242880*2,$_width,$_height,$_dir,$id_reg);

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
				}


				if(!empty($msgErro)) {
					$jsc->jAlert($msgErro,"erro","");
				} else {
					$jsc->jAlert("Informações salvas com sucesso!","sucesso","document.location.href='".$_page."?".$url."'");
					die();
				}
			} 

			else if($_POST['acao']=="opcionais" and isset($_POST['id_grupo']) and is_numeric($_POST['id_grupo'])) {


				$sql->consult($_table."_opcionais","*","where id_produto='".$cnt->id."' and id_grupo='".$_POST['id_grupo']."' and lixo=0");
				if($sql->rows) {
					$jsc->jAlert("Este Grupo de Adicional já foi adicionado!","erro","document.location.href='?form=1&edita=".$cnt->id."'");
				} else {
					$vSQL="id_produto='".$cnt->id."', id_grupo=".$_POST['id_grupo'];
					$sql->add($_table."_opcionais",$vSQL);
					$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='insert',vsql='".addslashes($vSQL)."',tabela='".$_table."',id_reg='".$cnt->id."'");

					$jsc->jAlert("Grupo de adicional cadastrado com sucesso!","sucesso","document.location.href='?form=1&edita=".$cnt->id."'");
				}

				
				die();
			}
		}
	?>
	<div class="box-botoes clearfix">
		<a href="<?php echo $_page."?".$url;?>" class="botao"><i class="icon-left-big"></i> Voltar</a>
		<?php if(is_object($cnt)) {?><a data-fancybox data-type="ajax" data-src="ajax/log.php?table=<?php echo $_table;?>&id=<?php echo $cnt->id;?>" href="javascript:;" class="botao" ><i class="icon-info-circled"></i> Logs</a><?php } ?>

			<a href="javascript://" class="botao botao-principal btn-submit" style="margin-bottom: 20px;margin-top: 20px;"><i class="icon-ok"></i> Salvar</a>

	</div>
	<script>
		$(function(){
			$('input[name=tipo]').click(function(){
				let tipo = $(this).val();
				$(`.js-particulares`).hide();
				$(`.js-particulares`).find('select[name=id_categoria_imposto]').removeClass('obg');
				$(`.js-${tipo}`).show();
				$(`.js-${tipo}`).find('select[name=id_categoria_imposto]').addClass('obg');
			});

			$('input[name=tipo]:checked').trigger('click');
		});
	</script>
	<div class="box-form">
		<form method="post" autocomplete="off" class="formulario-validacao formulario" enctype="multipart/form-data">
			<input type="hidden" name="acao" value="wlib" />

			<fieldset>
				<legend>Tipo do Produto</legend>

				<dl>
					<dd>
						<?php
						foreach($_tiposProdutos as $k=>$v) {
							echo '<label><input type="radio" name="tipo" value="'.$k.'"'.($values['tipo']==$k?' checked':'').' /> '.$v.'</label>';
						}
						?>
					</dd>
				</dl>
			</fieldset>

			<fieldset>
				<legend>Dados do Produto</legend>

				<dl class="dl2">
					<dt>Título</dt>
					<dd><input type="text" name="titulo" value="<?php echo $values['titulo'];?>" class="obg" maxlength="20" />
				</dl>
				<div class="colunas4">
					<dl>
						<dt>Código</dt>
						<dd><input type="text" name="codigo" value="<?php echo $values['codigo'];?>" class="obg" maxlength="10" /></dd>
					</dl>
					<dl>
						<dt>Unidade de Medida</dt>
						<dd>
							<select name="unidade_medida" class="obg">
								<option value="">-</option>
								<?php
								foreach($_unidadeMedidas as $v) echo '<option value="'.$v->id.'"'.($values['unidade_medida']==$v->id?' selected':'').'>'.utf8_encode($v->titulo.' ('.$v->unidade.')').'</option>'
								?>
							</select>
						</dd>
					</dl>
					<dl class="js-particulares js-materiaprima js-revenda  js-embalagem js-limpeza js-interno js-producaopropria">
						<dt>Segurança acima do Record (%)</dt>
						<dd><input type="text" name="seguranca" value="<?php echo $values['seguranca'];?>" class="" maxlength="4" /></dd>
					</dl>
					<dl class=" js-venda js-particulares">
						<dt>Categoria de Imposto</dt>
						<dd>
							<select name="id_categoria_imposto" class="obg">
								<option value="">-</option>
								<?php
								foreach($_categoriaImpostos as $v) echo '<option value="'.$v->id.'"'.($values['id_categoria_imposto']==$v->id?' selected':'').'>'.utf8_encode($v->titulo).'</option>';
								?>
							</select>
						</dd>
					</dl>
				</div>	
				<div class="colunas4">
					<dl>
						<dt>NCM</dt>
						<dd><input type="text" name="ncm" value="<?php echo $values['ncm'];?>" /></dd>
					</dl>
					<dl>
						<dt>CEST</dt>
						<dd><input type="text" name="cest" value="<?php echo $values['cest'];?>" /></dd>
					</dl>
				</div>
				<?php /*<dl class="js-particulares js-materiaprima js-limpeza js-interno js-producaopropria js-revenda js-embalagem">
					<dt>Dias de Contagem</dt>
					<dd>
						<select name="contagem[]" class="chosen js-contagem" multiple>
						<?php
						foreach($_optTurnoEscala as $k=>$v) {
							echo '<option value="'.$k.'"'.(in_array($k,$values['contagem'])?' selected':'').' >'.$v.'</option>';
						}
						?>
						</select>
					</dd>
					<dd>
						<label><input type="checkbox" class="js-todososdias" data-select="js-contagem"> Selecionar todos os dias</label>
						<script>
							$(function(){
								$('.js-todososdias').click(function(){
									let opt = $(this).attr('data-select');
									if($(this).prop('checked')==true) {
										$('select.'+opt+' option').prop('selected', true); 
										$('select.'+opt).trigger('chosen:updated');
									} else {
										$('select.'+opt+' option').prop('selected', false); 
										$('select.'+opt).trigger('chosen:updated');

									}
								});
							});
						</script>
					</dd>
				</dl>
				<dl class="js-particulares js-materiaprima js-limpeza js-interno js-producaopropria js-revenda js-embalagem">
					<dt>Abastecimento</dt>
					<dd>
						<select name="abastecimento[]" class="chosen js-abastecimento" multiple>
						<?php
						foreach($_optTurnoEscala as $k=>$v) {
							echo '<option value="'.$k.'"'.(in_array($k,$values['abastecimento'])?' selected':'').' >'.$v.'</option>';
						}
						?>
						</select>
					</dd>
					<dd>
						<label><input type="checkbox" class="js-todososdias" data-select="js-abastecimento"> Selecionar todos os dias</label>
					</dd>
				</dl>*/ ?>
				<script type="text/javascript">
					$(function(){
						$('input[name=adicional]').click(function(){
							if($(this).val()==1) {
								$('.js-div-adicional').show().find('input').addClass('obg');
							} else {
								$('.js-div-adicional').hide().find('input').removeClass('obg');
							}
						});
						$('input[name=adicional]:checked').trigger('click');
					});
				</script>
				<div class="colunas4">
					<dl class="js-particulares js-venda">
						<dt>Possui Ponto?</dt>
						<dd>
							<label><input type="radio" name="ponto" value="1"<?php echo $values['ponto']==1?" checked":"";?> /> Sim</label>
							<label><input type="radio" name="ponto" value="0"<?php echo $values['ponto']==0?" checked":"";?> /> Não</label>
						</dd>
					</dl>
					<dl>
						<dt>Adicional</dt>
						<dd>
							<label><input type="radio" name="adicional" value="1"<?php echo $values['adicional']==1?" checked":"";?> /> Sim</label>
							<label><input type="radio" name="adicional" value="0"<?php echo $values['adicional']==0?" checked":"";?> /> Não</label>
						</dd>
					</dl>
				</div> 	
				<div class="colunas4 js-div-adicional">
					<dl>
						<dt>Quantidade</dt>
						<dd><input type="text" name="quantidade" value="<?php echo $values['quantidade'];?>" class="quantidade" /></dd>
					</dl>
					<dl>
						<dt>Valor Adicional</dt>
						<dd><input type="text" name="valor_adicional" value="<?php echo $values['valor_adicional'];?>" class="money" /></dd>
					</dl>
					<dl>
						<dt>Categoria de Substituição</dt>
						<dd>
							<select name="id_categoria_substituicao">
								<option value="">-</option>
								<?php
								foreach($_categoriaDeSubstituicao as $v) echo '<option value="'.$v->id.'"'.(($v->id==$values['id_categoria_substituicao'])?' selected':'').'>'.utf8_encode($v->titulo).'</option>';
								?>
							</select>
						</dd>
					</dl>
				</div>
			</fieldset>

			<fieldset class="js-producaopropria  js-particulares" style="display: none">
				<legend>Produção Própria</legend>

				<div class="colunas4">
					<dl>
						<dt>Setor de Produção</dt>
						<dd>
							<select name="id_setor">
								<option value="">-</option>
								<?php
								foreach($_setorProducao as $v) echo '<option value="'.$v->id.'"'.($values['id_setor']==$v->id?' selected':'').'>'.utf8_encode($v->titulo).'</option>';
								?>
							</select>
						</dd>
					</dl>
					<dl>
						<dt>Valor Nota</dt>
						<dd><input type="text" name="valor_nota" class="money" value="<?php echo $values['valor_nota'];?>" /></dd>
					</dl>	
					<dl>
						<dt>Valor Transferência</dt>
						<dd><input type="text" name="valor_transferencia" class="money" value="<?php echo $values['valor_transferencia'];?>" /></dd>
					</dl>	
					<dl>
						<dt>Valor Venda Lojas</dt>
						<dd><input type="text" name="valor_lojas" class="money" value="<?php echo $values['valor_lojas'];?>" /></dd>
					</dl>	
				</div>
			</fieldset>

			<fieldset class="js-venda js-particulares" style="display: none">
				<legend>Produto de Venda</legend>

				<div class="colunas4">
					<dl>
						<dt>Categoria Cardápio</dt>
						<dd>
							<select name="id_categoria">
								<option value="">-</option>
								<?php
								foreach($_cardapioCategorias as $v) {
									echo '<option value="'.$v->id.'"'.($values['id_categoria']==$v->id?' selected':'').'>'.utf8_encode($v->titulo).'</option>';
								}
								?>
							</select>
						</dd>
					</dl>
					<dl>
						<dt>Tempo médio de montagem (min)</dt>
						<dd><input type="text" name="tempo" value="<?php echo $values['tempo'];?>" maxlength="2" /></dd>
					</dl>	
					<dl>
						<dt>Valor de Venda</dt>
						<dd><input type="text" name="valor_venda" value="<?php echo $values['valor_venda'];?>" class="money" /></dd>
					</dl>	
				</div>
			</fieldset>

			<fieldset class="js-producaopropria js-venda js-particulares box-registros">
				<legend>Composição</legend>
				<input type="hidden" name="composicao" value="<?php echo $values['composicao'];?>" />
				<script>
					var composicao = [];


					const composicaoRemover = (index) => {
						composicao.splice(index,1);
						composicaoListar();
					};
					const composicaoListar = () => {
						console.log(composicao);
						$('table.js-composicao .js-tr').remove();

						html = `<tr class="js-tr"><td class="js-tr-produto"></td><td class="js-tr-quantidade"></td><td class="js-tr-medida"></td><td><a href="javascript:;" class="js-tr-deleta"><i class="icon-cancel"></i></a></td></tr>`;

						composicao.forEach(x => {
							$('table.js-composicao').append(html);

							$('table.js-composicao .js-tr-produto:last').html(x.produto);
							$('table.js-composicao .js-tr-quantidade:last').html(x.quantidade);
							$('table.js-composicao .js-tr-medida:last').html(x.unidadeMedida);
							$('table.js-composicao .js-tr-deleta:last').click(function() {
								let index = $(this).index('table.js-composicao .js-tr-deleta');
								swal({   title: "Atenção",   text: "Você tem certeza que deseja remover este registro?",   type: "warning",   showCancelButton: true,   confirmButtonColor: "#DD6B55",   confirmButtonText: "Sim!",   cancelButtonText: "Não",   closeOnConfirm: false,   closeOnCancel: false }, function(isConfirm){   if (isConfirm) {  composicaoRemover(index); swal.close();   } else {   swal.close();   } });
							});

							let json = JSON.stringify(composicao);
							$('input[name=composicao]').val(json);

						});
					}

					$(function(){
						<?php
						if(!empty($values['composicao'])) {
							echo "composicao=JSON.parse('".$values['composicao']."');";
							echo "composicaoListar();";
						} 
						?>
						$('select.js-inpt-produto').change(function(){
							let medida='';
							if($(this).find(':selected').val().length>0) {
								medida=$(this).find(':selected').attr('data-unidade');
							} 
							$('.js-produto-descricao').html(medida);
							
						})
						$('input[name=quantidade]').on('input', function() {
      						this.value = this.value.replace(/[^\d]/g, '');
      					});

      					$('.js-btn-add').click(function(){
      						let produto = $('select.js-inpt-produto').val();
      						let produtoTitulo = $('select.js-inpt-produto option:selected').html();
      						let medida = $('select.js-inpt-produto option:selected').attr('data-unidade');
      						let quantidade = $('input.js-inpt-quantidade').val();
      						
      						if(produto.length==0) {
      							swal({title: "Erro!", text: "Selecione o produto!", type:"error", confirmButtonColor: "#424242"});
      							$('select.js-inpt-produto').addClass('erro');
      						} else if(quantidade.length==0) {
      							swal({title: "Erro!", text: "Selecione a quantidade!", type:"error", confirmButtonColor: "#424242"});
      							$('input.js-inpt-quantidade').addClass('erro');
      						} else {
      							let item = {};
      							item.id_produto = produto;
      							item.produto = produtoTitulo;
      							item.quantidade = quantidade;
      							item.unidadeMedida = medida;
      							composicao.push(item);
      							composicaoListar();
      							$('select.js-inpt-produto option:selected').prop('selected',false);
      							$('select.js-inpt-produto').trigger('chosen:updated');
      							$('input.js-inpt-quantidade').val('');
      						}

      					});
					});
				</script>	
				<div class="colunas4">
					<dl class="dl2">
						<dt>Produto</dt>
						<dd>
							<select class="js-inpt-produto chosen">
								<option value=""></option>
								<?php
								foreach($_produtos as $v) {
									if($v->tipo=="venda")continue;
									echo '<option value="'.$v->id.'" data-unidade="'.$_unidadeMedidas[$v->unidade_medida]->unidade.'">'.utf8_encode($v->codigo.' - '.$v->titulo).'</option>';
								}
								?>
							</select>	
						</dd>
					</dl>
					<dl>
						<dt>Quantidade</dt>
						<dd><input type="text" class="js-inpt-quantidade" maxlength="7" style="width:90%" /> <span class="js-produto-descricao" style="font-size:16px;"></span></dd>
					</dl>
					<dl>
						<dt>&nbsp;</dt>
						<dd><a href="javascript:;" class="js-btn-add botao botao-principal"><i class="icon-plus"></i></a></dd>
					</dl>	
				</div>
				<table class="js-composicao">
					<tr>
						<th>Produto</th>
						<th>Quantidade</th>
						<th>Unidade de Medida</th>
						<th></th>
					</tr>

				</table>
			</fieldset>

			<fieldset class="js-producaopropria js-venda js-particulares" style="display: none">
				<legend>Informações Complementares</legend>

				<dl>
					<dt>Descrição</dt>
					<dd><textarea name="texto_descricao" style="height: 100px;"><?php echo $values['texto_descricao'];?></textarea></dd>
				</dl>

				<dl>
					<dt>Ficha Nutricional</dt>
					<dd><textarea name="texto_nutricional" style="height: 100px;"><?php echo $values['texto_nutricional'];?></textarea></dd>
				</dl>

				<dl>
					<dt>Alérgico</dt>
					<dd><textarea name="texto_alergico" style="height: 100px;"><?php echo $values['texto_alergico'];?></textarea></dd>
				</dl>

				<dl>
					<dt>Harmonização</dt>
					<dd><textarea name="texto_harmonizacao" style="height: 100px;"><?php echo $values['texto_harmonizacao'];?></textarea></dd>
				</dl>
			</fieldset>
		
		</form>
			
			<?php
			if(is_object($cnt)) {


				if($cnt->tipo=="venda") {
					if(isset($_GET['opcional_deleta']) and is_numeric($_GET['opcional_deleta'])) {
						$vSQL="lixo=1";
						$vWHERE="where id=".$_GET['opcional_deleta'];
						$sql->update($_table."_opcionais",$vSQL,$vWHERE);
						$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='delete',vsql='".addslashes($vSQL)."',vwhere='".addslashes($vWHERE)."',tabela='".$_table."',id_reg='".$_GET['opcional_deleta']."'");
						$jsc->jAlert("Registro excluído com sucesso!","sucesso","document.location.href='".$_page."?form=1&edita=".$cnt->id."&".$url."'");
						die();
					}		
					$_opcionais=array();
					$sql->consult($_table."_opcionais","*","where id_produto=$cnt->id and lixo=0");
					if($sql->rows) {
						
						while($x=mysqli_fetch_object($sql->mysqry)) {
							$_opcionais[$x->id]=$x;
						}
					}	

				
			?>
		<form method="post" autocomplete="off" class="js-form-opcionais" enctype="multipart/form-data">
			<input type="hidden" name="acao" value="opcionais" />
			<fieldset class="box-registros">
				<legend>Grupo de Opcionais</legend>

				<input type="hidden" name="tipo" value="produto" />
				<div class="colunas4">
					<dl class="dl3">	
						<dt>Grupo de Opcionais</dt>
						<dd>
							<select name="id_grupo" class=" obg">
								<option value=""></option>
								<?php
								foreach($_gruposOpicionais as $v) {
									if(isset($_opcionais[$v->id_grupo])) continue;
										echo '<option value="'.$v->id.'">'.utf8_encode($v->titulo).'</option>';
									
								}
								?>
							</select>
						</dd>
					</dl>
					<dl>
						<dt>&nbsp;</dt>
						<dd>
							<a href="javascript:;" class="botao botao-principal btn-submit-mv" data-form="js-form-opcionais"><i class="icon-plus"></i></a>
						</dd>
					</dl>
				</div>
				<?php
				//var_dump($_opcionais);
				?>

				<table>
					<tr>
						<th>Título</th>
						<th style="width:50px"></th>
					</tr>
					<tr>
						<?php
						foreach($_opcionais as $x) {
							if(isset($_gruposOpicionais[$x->id_grupo])) {
						?>	
						<tr>
							<td><?php echo utf8_encode($_gruposOpicionais[$x->id_grupo]->titulo);?></td>
							<td><a href="<?php echo $_page."?".$url."&form=1&edita=$cnt->id&opcional_deleta=".$x->id;?>" class="js-deletar botao"><i class="icon-cancel"></i></a>
						</tr>
						<?php
							}	
							
						}
						?>
					</tr>
				</table>

			</fieldset>
		</form>
			<?php
				} else {
			?>
			<input type="text" name="unidades" value="" />
			<script>
				var unidades = [];
				var id_produto = '<?php echo $cnt->id;?>';

				
				const unidadesListar = () => {

					$('table.js-unidades tbody tr').remove();

					html = `<tr class="js-tr"><td class="js-tr-unidade"></td><td class="js-tr-contagem"></td><td class="js-tr-entrega"></td><td><a href="javascript:;" class="js-tr-editar botao"><i class="icon-pencil"></i></a><a href="javascript:;" class="js-tr-deletar botao"><i class="icon-cancel"></i></a></td></tr>`;

					let data = `ajax=unidadesListar&id_produto=${id_produto}`;

					$.ajax({
						type:'POST',
						data:data,
						url:'<?php echo $_page;?>',
						success:function(rtn) {
							if(rtn.success) {
								rtn.unidades.forEach(x => {
									$('table.js-unidades tbody').append(html);
									$('table.js-unidades tbody .js-tr-unidade:last').html(x.unidade);
									$('table.js-unidades tbody .js-tr-contagem:last').html(x.contagem);
									$('table.js-unidades tbody .js-tr-entrega:last').html(`${x.entrega} dia(s)`);
									$('table.js-unidades tbody .js-tr-editar:last').attr('data-id',x.id);
									$('table.js-unidades tbody .js-tr-deletar:last').attr('data-id',x.id);
								});
								unidades = rtn.unidades;

								$('.js-unidades-id_unidade').find('option').remove();
								$('.js-unidades-id_unidade').append('<option value="">-</option>');
								rtn.selectUnidades.forEach(x => {
									$('.js-unidades-id_unidade').append(`<option value="${x.id}">${x.titulo}</optio>`);
								})
								
							}
						}
					});
					
				}


				$(function(){
					
					$('table.js-unidades tbody').on('click','.js-tr-editar',function() {
						let id = $(this).attr('data-id');
						unidades.forEach(x=>{
							if(x.id==id) {
								$('select.js-unidades-id_unidade').append(`<option value="${x.id_unidade}">${x.unidade}</option>`);
								$('select.js-unidades-id_unidade').val(x.id_unidade).trigger('chosen:updated');
								$('input.js-unidades-tempoEntrega').val(x.entrega);
								$('select.js-unidades-diasContagem').val('').trigger('chosen:updated');
								x.contagemID.forEach(y=>{
									$('select.js-unidades-diasContagem').find(`option[value=${y}]`).prop('selected',true);
								});
								$('select.js-unidades-diasContagem').trigger('chosen:updated');
							}
						})
					});

					$('table.js-unidades tbody').on('click','.js-tr-deletar',function() {
						let id = $(this).attr('data-id');
						let data = `ajax=unidadesRemover&id_produto=${id_produto}&id=${id}`;

						swal({   
								title: "Atenção",
								text: 'Deseja remover este item?',
								html:true,
								type: "warning",
								showCancelButton: true,
								confirmButtonColor: "#DD6B55",
								confirmButtonText: "Sim!",
								cancelButtonText: "Não",
								closeOnConfirm: false,
								closeOnCancel: false 
							}, function(isConfirm){   
								if (isConfirm) {    
									$.ajax({
										type:"POST",
										data:data,
										url:'<?php echo $_page;?>',
										success:function(rtn) {
											unidadesListar();
											swal.close();  
										},
									});
						   		} else {   
						   			swal.close();  
						   		} 
						});

						
						
					});
					
  					$('.js-btn-unidadesadd').click(function(){
  						let unidade = $('select.js-unidades-id_unidade').val();
  						let contagem = $('select.js-unidades-diasContagem').val();
  						let entrega = $('input.js-unidades-tempoEntrega').val();
  						
  						
  						if(unidade.length==0) {
  							swal({title: "Erro!", text: "Selecione a unidade!", type:"error", confirmButtonColor: "#424242"});
  							$('select.js-unidades-id_unidade').addClass('erro');
  						} else if(contagem===null) {
  							swal({title: "Erro!", text: "Selecione o(s) Dia(s) de Contagem!", type:"error", confirmButtonColor: "#424242"});
  							$('input.js-unidades-diasContagem').addClass('erro');
  						} else if(entrega.length==0) {
  							swal({title: "Erro!", text: "Defina o Tempo de Entrega!", type:"error", confirmButtonColor: "#424242"});
  							$('input.js-unidades-tempoEntrega').addClass('erro');
  						} else {

  							let data = `ajax=unidadesAdicionar&unidade=${unidade}&contagem=${contagem}&entrega=${entrega}&id_produto=${id_produto}`;
  							$.ajax({
  								type:'POST',
  								data:data,
  								url:'<?php echo $_page;?>',
  								success:function(rtn) {
  									if(rtn.success) {
  										$('select.js-unidades-id_unidade').val('');
  										$('select.js-unidades-diasContagem').val('').trigger('chosen:updated');
  										$('input.js-unidades-tempoEntrega').val('');
  										unidadesListar();
  									} else if(rtn.error) {
										swal({title: "Erro!", text: rtn.error, type:"error", confirmButtonColor: "#424242"});
  									} else {
  										swal({title: "Erro!", text: "Algum erro ocorreu. Tente novamente!", type:"error", confirmButtonColor: "#424242"});
  									}
  								},
  								error:function(){
  									swal({title: "Erro!", text: "Algum erro ocorreu. Tente novamente!", type:"error", confirmButtonColor: "#424242"});
  								}
  							});	
  						}
  					});

  					unidadesListar();
				});
			</script>	
			<form method="post" autocomplete="off" class="js-form-unidades" enctype="multipart/form-data">
				<fieldset class="box-registros">
					<legend>Unidades</legend>

					<div class="colunas4">
						<dl>
							<dt>Unidade</dt>
							<dd>
								<select class="js-unidades-id_unidade">
									<option value="">-</option>
									<?php
									foreach($_optUnidades as $v) {
										echo '<option value="'.$v->id.'">'.utf8_encode($v->titulo).'</option>';
									}
									?>
								</select>
							</dd>
						</dl>

						<dl class="dl2">
							<dt>Dias de Contagem</dt>
							<dd>
								<select class="chosen js-unidades-diasContagem" multiple>
								<?php
								foreach($_optTurnoEscala as $k=>$v) {
									echo '<option value="'.$k.'">'.$v.'</option>';
								}
								?>
								</select>
							</dd>
						</dl>
						<dl>
							<dt>Tempo de Entrega (dias)</dt>
							<dd>
								<input type="text" class="js-unidades-tempoEntrega js-maskNumber" value="" style="width:70%;float:left;margin-right:10px" maxlength="3" />
								<a href="javascript:;" class="botao js-btn-unidadesadd" style="width:20%;float:left;" ><i class="icon-ok"></i></a>
							</dd>
						</dl>
					</div>	


					<table class="js-unidades">
						<thead>
							<tr>
								<th style="width: 200px">Unidade</th>
								<th>Dias de Contagem</th>
								<th style="width:120px">Tempo de Entrega</th>
								<th style="width: 100px"></th>
							</tr>
						</thead>
						<tbody>
						</tbody>
					</table>
				</fieldset>
			</form>
			<?php
				}
			?>
			<fieldset>
				<legend>Fotos</legend>

				<form method="post" autocomplete="off" class="js-form-fotos" enctype="multipart/form-data">
					<div class="colunas4">
						<dl class="dl3">
							<dt>Foto</dt>
							<dd><input type="file" name="foto[]" accept=".jpg,.png" multiple="" /></dd>
						</dl>
						<dl>
							<dt>&nbsp;</dt>
							<dd><button type="submit"  class="botao"><i class="icon-plus"></i></button></dd>
						</dl>

					</div>
				</form>
			</fieldset>
			
			<fieldset>
				<legend>Vídeos</legend>
			</fieldset>
			<?php
			}
			?>
			


	</div>
	<?php
	} else {

		//if(!isset($values['data_de'])) $values['data_de']="data";
	
	?>
	<div class="box-botoes clearfix">
		<a href="<?php echo $_page;?>?form=1<?php echo "&".$url;?>" class="botao botao-principal" style="float:right"><i class="icon-plus"></i> Adicionar</a>
	</div>

	<div class="box-filtros clearfix">
		<form method="get" class="formulario-validacao js-filtro">
			<input type="hidden" name="csv" value="0" />
			<div class="colunas5">
				<dl class="dl2">
					<dt>Busca</dt>
					<dd><input type="text" name="busca" value="<?php echo isset($values['busca'])?$values['busca']:"";?>" /></dd>
				</dl>
				<dl>
					<dt>Tipo</dt>
					<dd>
						<select name="tipo">
							<option value="">-</option>
							<?php
							foreach($_tiposProdutos as $k=>$v) echo '<option value="'.$k.'"'.((isset($values['tipo']) and $k==$values['tipo'])?' selected':'').'>'.($v).'</option>';
							?>
						</select>
					</dd>
				</dl>
				<dl>		
					<dt>&nbsp;</dt>			
					<dd><button type="submit"><i class="icon-search"></i> Filtrar</button></dd>
				</dl>
				
			</div>
		</form>
	</div>

	<div class="box-registros">
		<?php
		
		if(isset($_GET['deleta']) and is_numeric($_GET['deleta']) and $usrCargo->admin==1) {

			$sql->consult($_p."unidades_cardapio","*","where id_produto='".$_GET['deleta']."' and lixo=0");
			if($sql->rows) {
				$x=mysqli_fetch_object($sql->mysqry);

				$sql->consult($_p."produtos","*","where lixo=0 and id='".$_GET['deleta']."'");
				if($sql->rows) {
					$p=mysqli_fetch_object($sql->mysqry);
					$jsc->jAlert("Este produto está incluso no Cardápio!<br />Para removê-lo, é necessário retirá-lo do Cardápio.<br /><a href=pg_unidades_cardapio.php?form=1&id_categoria=".$p->id_categoria."&id_unidade=".$x->id_unidade." target=_blank><u>Clique aqui para abrir o Cardápio</u></a>","erro","");
				}
			} else {
				$vSQL="lixo='1'";
				$vWHERE="where id='".$_GET['deleta']."'";
				$sql->update($_table,$vSQL,$vWHERE);
				$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='delete',vsql='".addslashes($vSQL)."',vwhere='".addslashes($vWHERE)."',tabela='".$_table."',id_reg='".$_GET['deleta']."'");
				$jsc->jAlert("Registro excluído com sucesso!","sucesso","document.location.href='".$_page."?".$url."'");
				die();
			}
		}
		

		
		$where="WHERE lixo='0'";
		
		if(isset($values['busca']) and !empty($values['busca'])) $where.=" and (titulo like '%".addslashes($values['busca'])."%' or codigo like '%".addslashes($values['busca'])."%')";
		if(isset($values['tipo']) and !empty($values['tipo'])) $where.=" and tipo='".addslashes($values['tipo'])."'";
		

		//echo $where;
		
		//if($usr->cpf=="00648938123") echo $where;
		$sql->consult($_table,"*",$where." order by codigo asc");
		

		?>
		<div class="opcoes clearfix">
			<div class="qtd"><?php echo $sql->rows;?> registros</div>
		</div>
		<script type="text/javascript">
			$(function(){
				$('input[name=ncm]').change(function(){
					let id = $(this).attr('data-id');
					let ncm = $(this).val();
					var elem = $(this);
					$.ajax({
						type:'POST',
						data:`ajax=wlib&id=${id}&ncm=${ncm}`,
						success:function(rtn) {
							if(rtn.success) {
								elem.css('background','green');
							} else if(rtn.error) {
								alert(rtn.error);
							} else {
								alert('Erro!');
							}
						}
					});
				});

				$('input[name=cest]').change(function(){
					let id = $(this).attr('data-id');
					let cest = $(this).val();
					var elem = $(this);
					$.ajax({
						type:'POST',
						data:`ajax=wlib&id=${id}&cest=${cest}`,
						success:function(rtn) {
							if(rtn.success) {
								elem.css('background','green');
							} else if(rtn.error) {
								alert(rtn.error);
							} else {
								alert('Erro!');
							}
						}
					});
				});

				$('select[name=id_categoria_imposto]').change(function(){
					let id = $(this).attr('data-id');
					let id_categoria_imposto = $(this).val();
					var elem = $(this);
					$.ajax({
						type:'POST',
						data:`ajax=wlib&id=${id}&id_categoria_imposto=${id_categoria_imposto}`,
						success:function(rtn) {
							if(rtn.success) {
								elem.css('background','green');
							} else if(rtn.error) {
								alert(rtn.error);
							} else {
								alert('Erro!');
							}
						}
					});
				})
			});
		</script>
		<table class="tablesorter">
			<thead>
				<tr>
					<th style="width:50px;">Código</th>
					<th>Título</th>
					<th>Tipo</th>
					<th>Unidade de Medida</th>
					<th>Categoria de Imposto</th>
					<th>NCM</th>
					<th>CEST</th>
					<th style="width:150px;">Ações</th>
				</tr>
			</thead>
			<tbody>
			<?php
			while($x=mysqli_fetch_object($sql->mysqry)) {
			?>
			<tr>
				<td><?php echo $x->codigo;?></td>
				<td>
					<strong><?php echo utf8_encode($x->titulo);?></strong>
					<?php
					echo $estoque->composicao($x->id);
					?>aaa
				</td>
				<td><?php echo $_tiposProdutos[$x->tipo];?></td>
				<td><?php echo utf8_encode($_unidadeMedidas[$x->unidade_medida]->titulo)." (".$_unidadeMedidas[$x->unidade_medida]->unidade.")";?></td>
				<td>
					<select name="id_categoria_imposto" data-id="<?php echo $x->id;?>">
						<option value="">-</option>
						<?php
						foreach($_categoriaImpostos as $v) echo '<option value="'.$v->id.'"'.($x->id_categoria_imposto==$v->id?' selected':'').'>'.utf8_encode($v->titulo).'</option>';
						?>
					</select>
				</td>
				<td><input type="text" name="ncm" data-id="<?php echo $x->id;?>" value="<?php echo $x->ncm;?>" /></td>
				<td><input type="text" name="cest" data-id="<?php echo $x->id;?>" value="<?php echo $x->cest;?>" /></td>
				<td>
					<a href="<?php echo $_page;?>?form=1&edita=<?php echo $x->id."&".$url;?>" class="tooltip botao botao-principal" title="editar"><i class="icon-pencil"></i></a>
					<a href="<?php echo $_page;?>?form=1&copy=<?php echo $x->id;?>" class="tooltip botao botao-principal" title="duplicar"><i class="icon-link"></i></a>
					<?php if($usrCargo->admin==1) { ?><a href="<?php echo $_page;?>?deleta=<?php echo $x->id."&".$url;?>" class="js-deletar tooltip botao botao-principal" title="excluir "><i class="icon-cancel"></i></a><?php } ?>
				</td>
			</tr>
			<?php
			}
			?>
			</tbody>
		</table>
	</div>
	
	<?php
	}
	?>

</section>

<?php
	include "includes/footer.php";
?>