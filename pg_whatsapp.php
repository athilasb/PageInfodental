<?php
	if(isset($_POST['ajax'])) {
		require_once("lib/conf.php");
		require_once("usuarios/checa.php");

		$sql = new Mysql();

		$rtn=array();

		if($_POST['ajax']=="wtsSincronizar") {
			$wts='';
			if(isset($_POST['id_whatsapp']) and is_numeric($_POST['id_whatsapp'])) {
				$sql->consult($_p."whatsapp_instancias","*","where id='".$_POST['id_whatsapp']."'");
				if($sql->rows) $wts=mysqli_fetch_object($sql->mysqry);
			}

			if(is_object($wts)) {
				$attr=array('prefixo'=>$_p,'id_whatsapp'=>$wts->id);
				$chatpro = new ChatPRO($attr);

				if($chatpro->status()) {
					if($chatpro->connected===true) {
						$rtn=array('success'=>true,
										'connected'=>true,
										'marcamodelo'=>$chatpro->marcamodelo,
										'numero'=>$chatpro->numero,
										'pushname'=>$chatpro->pushname,
										'bateria'=>$chatpro->bateria);
					} else {

						if($chatpro->qrcode()) {
							$rtn=array('success'=>true,'connected'=>false,'qrcode'=>$chatpro->qrcode);
						} else {
							$rtn=array('success'=>false,'error'=>$chatpro->erro);
						}
					}
				} else {
					$rtn=array('success'=>false,'error'=>$chatpro->erro);
				}
			} else {
				$rtn=array('success'=>false,'error'=>'Instância de whatsapp não encontrado');
			}
		}

		header("Content-Type: application/json");
		echo json_encode($rtn);
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
?>

<section class="content">

	<?php
	$_table=$_p."whatsapp_instancias";
	$_page=basename($_SERVER['PHP_SELF']);

	$_width="";
	$_height="";
	$_dir="";

	$_planos=array();
	$sql->consult($_p."parametros_planos","*","where lixo=0 order by titulo asc");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_planos[$x->id]=$x;
	}

	if(isset($_GET['form'])) {
		$cnt='';
		$campos=explode(",","titulo,id_unidade,token,endpoint,instancia");
		
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
		if(isset($_POST['acao']) and $_POST['acao']=="wlib") {
			
			$vSQL='';
			foreach($campos as $v) {
				if(isset($_POST[$v])) {
					if($v=="titulo") $vSQL.=$v."='".addslashes(strtoupperWLIB(utf8_decode($_POST[$v])))."',";
					else $vSQL.=$v."='".addslashes($_POST[$v])."',";
				$values[$v]=$_POST[$v];
			}
			
			}

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
					$jsc->jAlert("Informações salvas com sucesso!","sucesso","document.location.href='".$_page."?".$url."'");
					die();
				}
			}
		}	
	?>
			
	<div class="filtros">
		<h1 class="filtros__titulo">WhatsApp</h1>
		<div class="filtros-acoes">
			<a href="<?php echo $_page."?".$url;?>"><i class="iconify" data-icon="bx-bx-left-arrow-alt"></i></a>
			<a href="javascript:;" class="principal btn-submit"><i class="iconify" data-icon="bx-bx-check"></i></a>
			<?php if(is_object($cnt) and $usr->tipo=="admin") { ?>
			<a class="sec js-deletar" href="<?php echo $_page;?>?deleta=<?php echo $cnt->id."&".$url;?>"><i class="iconify" data-icon="bx-bx-trash"></i></a>
			<?php } ?>			
		</div>
	</div>

	<section class="grid">
		<div class="box">
			<form method="post" class="form formulario-validacao"  autocomplete="off" enctype="multipart/form-data">
				<input type="hidden" name="acao" value="wlib" />
				<fieldset>
					<legend>Dados do Whatsapp</legend>

					<div class="colunas4">
						<dl>
							<dt>Unidade</dt>
							<dd>
								<select name="id_unidade" class="obg">
									<option value="">-</option>
									<?php
									foreach($_unidades as $v) echo '<option value="'.$v->id.'"'.($v->id==$values['id_unidade']?' selected':'').'>'.utf8_decode($v->titulo).'</option>';
									?>
								</select>
							</dd>
						</dl>
						<dl class="dl3">
							<dt>Título</dt>
							<dd>
								<input type="text" name="titulo" value="<?php echo $values['titulo'];?>" class="" />
							</dd>
						</dl>
					</div>
					<div class="colunas4">
						<dl>
							<dt>Instância Whatsapp</dt>
							<dd>
								<input type="text" name="instancia" value="<?php echo $values['instancia'];?>" class="noupper" />
							</dd>
						</dl>
						<dl class="dl2">
							<dt>Endpoint</dt>
							<dd>
								<input type="text" name="endpoint" value="<?php echo $values['endpoint'];?>" class="noupper" />
							</dd>
						</dl>
						<dl>
							<dt>Token</dt>
							<dd><input type="text" name="token" value="<?php echo $values['token'];?>" class="noupper" /></dd>
						</dl>
						
					</div>
				</fieldset>

				<?php
				if(is_object($cnt)) {
				?>
				<script type="text/javascript">
					const sincronizar = () => {
						$.ajax({
							type:"POST",
							data:"ajax=wtsSincronizar&id_whatsapp=<?php echo $cnt->id;?>",
							success:function(rtn) {
								if(rtn.success) {
									if(rtn.connected===true) {
										console.log(rtn);
										$('.js-conexao').html(`<div class="colunas4">
																	<dl>
																		<dt>Marca/Modelo</dt>
																		<dd>${rtn.marcamodelo}</dd>
																	</dl>
																	<dl>
																		<dt>Número</dt>
																		<dd>${rtn.numero}</dd>
																	</dl>
																	<dl>
																		<dt>Pushname</dt>
																		<dd>${rtn.pushname}</dd>
																	</dl>
																	<dl>
																		<dt>Bateria</dt>
																		<dd>${rtn.bateria}</dd>
																	</dl>
																</div>`)
									} else {
										$('.js-conexao').html(`<center><img src="${rtn.qrcode}" width="200" height="200" style="border:#CCC solid 1px;padding:1px;" /><br /><br />Seu smartphone não está conectado ao InfoDental.</center>`);
										setTimeout(sincronizar,1000*20);
									}
								} else if(rtn.error) {
									$('.js-conexao').html(`<font color=red><center>${rtn.error}</center></font>`);
								} else {
									$('.js-conexao').html(`<font color=red><center>Algum erro ocorreu durante a sincronização!</center></font>`);
								}
							},
							error:function() {
								$('.js-conexao').html(`<font color=red><center>Algum erro ocorreu durante a sincronização!</center></font>`);
							}
						})
					}
					$(function(){
						sincronizar();
					})
				</script>
				<fieldset>
					<legend>Conexão</legend>

					<div class="js-conexao">
						<center>Sincronizando...</center>
					</div>

				</fieldset>
				<?php	
				}
				?>
			</form>
		</div>
	</section>
	<?php
	} else {			
	?>

	<section class="filtros">
		<h1 class="filtros__titulo">WhatsApp</h1>
		<form method="get" class="filtros-form">
			<input type="hidden" name="csv" value="0" />
			<dl>
				<dt>Unidade</dt>
				<dd>
					<select name="id_unidade" class="obg">
						<option value="">-</option>
						<?php
						foreach($_unidades as $v) echo '<option value="'.$v->id.'"'.($v->id==$values['id_unidade']?' selected':'').'>'.utf8_decode($v->titulo).'</option>';
						?>
					</select>
				</dd>
			</dl>
			<button type="submit" class="filtros-form__button"><i class="iconify" data-icon="bx-bx-search"></i></button>			
		</form>
		<div class="filtros-acoes">
			<a href="<?php echo $_page."?form=1&$url";?>" data-padding="0" class="adicionar tooltip" title="Adicionar"><i class="iconify" data-icon="bx-bx-plus"></i></a>
		</div>
	</section>

	<?php
	if(isset($_GET['deleta']) and is_numeric($_GET['deleta']) and $usr->tipo=="admin") {
		$vSQL="lixo='1'";
		$vWHERE="where id='".$_GET['deleta']."'";
		$sql->update($_table,$vSQL,$vWHERE);
		$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='delete',vsql='".addslashes($vSQL)."',vwhere='".addslashes($vWHERE)."',tabela='".$_table."',id_reg='".$_GET['deleta']."'");
		$jsc->jAlert("Registro excluído com sucesso!","sucesso","document.location.href='".$_page."?".$url."'");
		die();
	}
	
	$where="WHERE lixo='0'";
	if(isset($values['id_unidade']) and is_numeric($values['id_unidade'])) $where.=" and (id_unidade = '".($values['id_unidade'])."')";
	
	if($usr->cpf=="wlib" and isset($_GET['cmd'])) echo $where;

	$sql->consult($_table,"*",$where." order by titulo asc");
	?>
	<section class="grid">
		<div class="box">
			
			<div class="registros-qtd">
				<p class="registros-qtd__item"><?php echo $sql->rows;?> usuários</p>
			</div>
			
			<div class="registros">
				<table class="tablesorter">
					<thead>
						<tr>
							<th>Título</th>
							<th>Unidade</th>
							<th>Instância Whatsapp</th>
						</tr>
					</thead>
					<tbody>
					<?php
					while($x=mysqli_fetch_object($sql->mysqry)) {
					?>
					<tr onclick="document.location.href='<?php echo $_page;?>?form=1&edita=<?php echo $x->id."&".$url;?>'">
						<td><strong><?php echo utf8_encode($x->titulo);?></strong></td>
						<td><?php echo isset($_unidades[$x->id_unidade])?utf8_encode($_unidades[$x->id_unidade]->titulo):'-';?></td>
						<td><?php echo $x->instancia;?></td>						
					</tr>
					<?php
					}
					?>
					</tbody>
				</table>
			</div>
		</div>
	</section>
			
	<?php
	}
	?>
</section>

<?php
	include "includes/footer.php";
?>