<?php
	$title="";
	include "includes/header.php";
	include "includes/nav.php";

	$values=$adm->get($_GET);
?>

<section class="content">
	
	<?php
	require_once("includes/abaConfiguracoes.php");
	?>
	
	
			
	<?php
	$_table=$_p."usuarios";
	$_page="usuarios.php";
	$_campanhas=array();
	$_departamentos=array();
	$campos=explode(",","pub,nome,cpf,data_nascimento,tipo,permissoes");
	
	if(isset($_GET['form'])) {
	
		
		$cnt='';
		foreach($campos as $v) $values[$v]='';
		$values['tipo']="moderador";
		$values['pub']=1;
		
		if(isset($_GET['edita']) and is_numeric($_GET['edita'])) {
			$sql->consult($_table,"*","where id='".$_GET['edita']."'");
			if($sql->rows) {
				$cnt=mysqli_fetch_object($sql->mysqry);
				
				$values=$adm->values($campos,$cnt);
				
			} else {
				$jsc->jAlert("Usuário não encontrado!","erro","document.location.href='".$_page."?".$url."'");
				die();
			}
		}
		
		if(isset($_POST['acao']) and $_POST['acao']=="wlib") {
			
			
			$vSQL=$adm->vSQL($campos,$_POST);
			$values=$adm->values;
			//echo $vSQL;
			$processa=true;
			if(!is_object($cnt)) {
				$sql->consult($_table,"*","where cpf='".$_POST['cpf']."'");
				if($sql->rows) {
					$processa=false;
					$jsc->jAlert("Já existe usuário cadastrado com este CPF!","erro","$('input[name=cpf]').css('background','#EF989D');");
				}
			}
			
			if($processa===true) {	
				
				if(is_object($cnt)) {

					if(isset($_POST['senha'])  and !empty($_POST['senha'])) $vSQL.="senha='".sha1($_POST['senha'])."',";
					$vSQL=substr($vSQL,0,strlen($vSQL)-1);
					$vWHERE="where id='".$cnt->id."'";
					$sql->update($_table,$vSQL,$vWHERE);
					$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='update',vsql='".addslashes($vSQL)."',vwhere='".addslashes($vWHERE)."',tabela='".$_table."',id_reg='".$cnt->id."'");
					$id_reg=$cnt->id;
				} else {
					$vSQL=$vSQL."data=now(),senha='".sha1($_POST['senha'])."'";

					$sql->add($_table,$vSQL);	
					$id_reg=$sql->ulid;
					$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='insert',vsql='".addslashes($vSQL)."',tabela='".$_table."',id_reg='".$id_reg."'");
				
				}

				$jsc->jAlert("Informações salvas com sucesso!","sucesso","document.location.href='".$_page."?form=1&edita=".$id_reg."'");
				die();
			}
			
		}
	?>

	<script type="text/javascript" src="js/jquery.money.js"></script>
	<script>
	$(function(){
		$('input.money').maskMoney({symbol:'', allowZero:false, showSymbol:true, thousands:'.', decimal:',', symbolStay: true});
		
		
		
		$('input[name=tipo]:checked').trigger('click');
		$('input[name=cpf]').keyup(function(){
			$(this).val($(this).val().replace(/[^0-9+]/g, ''));
		})
		$('input[name=login]').blur(function(){
			var login = $(this).val();
			if(login.length>0) {
				var data="ajax=wlibweb&login="+login;
				$.ajax({type:"post",url:"ajax/usuariosCheca.php",data:data,
						success: function(a){
							if(a>0) {
								swal({title: "Erro!", text: "Já existe usuário cadastrado com este login!", type:"error", confirmButtonColor: "#424242"},function(){$('input[name=login]').css('background','#EF989D').attr('data-ok','false');});
							}
							else {
								$('input[name=login]').css('background','#9FE997').attr('data-ok','true');
							}
						}});
			}
		});
		
		$('input[name=senha],input[name=senha2]').keyup(function(){
			$(this).val(removerAcentos2($(this).val()));
		});
		
		$('input[name=tipo]').click(function(){
			if($(this).val()=="moderador") {
				$('.js-permissoes').show();
			} else {
				$('.js-permissoes').hide();
				
			}
		});
		$('input[name=tipo]:checked').trigger('click');
		
	})
	</script>
			
	<div class="filtros">
		<h1 class="filtros__titulo">Usuários</h1>
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

			<form method="post" class="form formulario-validacao"  autocomplete="off">
				<input type="hidden" name="acao" value="wlib" />
				<?php
				if(is_object($cnt)) {
				?>
				<input type="hidden" name="cpf_antigo" value="<?php echo $cnt->cpf;?>" />
				<?php
				}
				?>
				<fieldset>
					<legend>Dados pessoais</legend>
					
					<dl>
						<dt></dt>
						<dd><label><input type="checkbox" name="pub" value="1"<?php echo $values['pub']=='1'?' checked':'';?> /> Usuário ativo</label></dd>
					</dl>
					
					<div class="colunas3">
						<dl>
							<dt>Nome</dt>
							<dd><input type="text" name="nome" value="<?php echo $values['nome'];?>" autocomplete="off" class="obg" /></dd>
						</dl>
						<dl>
							<dt>CPF</dt>
							<dd><input type="text" name="cpf" value="<?php echo $values['cpf'];?>" autocomplete="off" class="obg" maxlength="11"<?php echo is_object($cnt)?" disabled":"";?> /></dd>
						</dl>
						<dl>
							<dt>Senha</dt>
							<dd><input type="text" name="senha" autocomplete="off" value="" class="<?php echo is_object($cnt)?"":"obg";?>" /></dd>
						</dl>
					</div>
				</fieldset>

				
				<fieldset>
					<legend>Tipo do usuário</legend>
					<dl>
						<dd>
							<?php
							foreach($_usuariosTipos as $k=>$v) {
							?>
							<label><input type="radio" name="tipo" value="<?php echo $k;?>"<?php echo $values['tipo']==$k?" checked":"";?> /> <?php echo $v;?></label>
							<?php
							}
							?>
							
						</dd>
					</dl>
					
					<dl class="js-permissoes">
						<dt>Permissões</dt>
						<dd>
							<select name="permissoes[]" multiple class="chosen">
								<option></option>
								<?php
								foreach($_menu as $k=>$v) {
									if($k=="dashboard") continue;
									echo '<option value="'.$k.'"'.(in_array($k,$values['permissoes'])?' selected':'').'>'.($v['titulo']).'</option>';
								}
								?>
							</select>
						</dd>
					</dl>
					
				</fieldset>
				
			</form>
		</div>
	</section>

	<?php
	} else {
	?>
	<section class="filtros">
		<h1 class="filtros__titulo">Usuários</h1>
		<form method="get" class="filtros-form">
			<input type="hidden" name="csv" value="0" />
			<dl>
				<dt>Usuário</dt>
				<dd><input type="text" name="nome" value="<?php echo isset($values['nome'])?$values['nome']:"";?>" /></dd>
			</dl>
			<dl>
				<dt>Tipo</dt>
				<dd>
					<select name="tipo">
						<option value="">-</option>
						<?php
						foreach($_usuariosTipos as $k=>$v) {
							echo '<option value="'.$k.'"'.((isset($values['tipo']) and $values['tipo']==$k)?' selected':'').'>'.$v.'</option>';
						}
						?>
					</select>
				</dd>
			</dl>
			<dl>
				<dt>Ativos/Inativos</dt>
				<dd>
					<select name="pub">
						<option value="">-</option>
						<option value="2"<?php echo (isset($values['pub']) and $values['pub']==2)?" selected":"";?>>Ativos</option>
						<option value="1"<?php echo (isset($values['pub']) and $values['pub']==1)?" selected":"";?>>Inativos</option>
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
		$vWHERE="where id='".$_GET['deleta']."' and nuncalixo=0";
		$sql->update($_table,$vSQL,$vWHERE);
		$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='delete',vsql='".addslashes($vSQL)."',vwhere='".addslashes($vWHERE)."',tabela='".$_table."',id_reg='".$_GET['deleta']."'");
		$jsc->jAlert("Registro excluído com sucesso!","sucesso","document.location.href='".$_page."?".$url."'");
		die();
	}
	
	
	$where="WHERE lixo='0'";
	
	if(isset($values['nome']) and !empty($values['nome'])) $where.=" and (login like '%".$values['nome']."%' or nome like '%".$values['nome']."%')";
	if(isset($values['tipo']) and !empty($values['tipo'])) $where.=" and tipo='".$values['tipo']."'";
	if(isset($values['pub']) and !empty($values['pub'])) $where.=" and pub='".($values['pub']-1)."'";
	
	/*if(isset($_GET['csv']) and $_GET['csv']==1) {
		$camposCSV=$campos;
		array_unshift($camposCSV,"data");
		
		
		$csv=$adm->csv2("vicidial_logins",
					   $sql,
					   $where." order by full_name asc",
					   $camposCSV,$especificacoes);
		?>
		<script>
		window.open('lib/download.php?arq=../<?php echo $csv;?>&nome=usuarios.csv');
		</script>
		<?php
	}*/
	
	$sql->consult($_table,"*",$where." order by nome asc");
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
							<th>Nome</th>
							<th>CPF</th>
							<th>Tipo</th>
							<th>Ativo</th>
						</tr>
					</thead>
					<tbody>
					<?php
					while($x=mysqli_fetch_object($sql->mysqry)) {
					?>
					<tr onclick="document.location.href='<?php echo $_page;?>?form=1&edita=<?php echo $x->id."&".$url;?>'">
						<td><strong><?php echo utf8_encode($x->nome);?></strong></td>
						<td><?php echo $x->cpf;?></td>
						<td><?php echo isset($_usuariosTipos[$x->tipo])?$_usuariosTipos[$x->tipo]:$x->tipo;?></td>
						<td style="text-align: center;"><?php echo $x->pub==1?'<i class="iconify" data-icon="bx-bx-check"></i>':'<i class="iconify" data-icon="dashicons:no-alt"></i>';?></td>						
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