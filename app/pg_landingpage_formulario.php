<?php
	$title="";
	include "includes/header.php";
	include "includes/nav.php";

	if($usr->tipo!="admin" and !in_array("landingpage",$_usuariosPermissoes)) {
		$jsc->jAlert("Você não tem permissão para acessar esta área!","erro","document.location.href='dashboard.php'");
		die();
	}
	$values=$adm->get($_GET);

	$_status=array('novo'=>array('cor'=>'green','titulo'=>'NOVO'),'pendente'=>array('cor'=>'orange','titulo'=>'PENDENTE'),'resolvido'=>array('cor'=>'blue','titulo'=>'RESOLVIDO'));

	$_temas=array();
	$sql->consult($_p."landingpage_temas","*","where lixo=0 order by titulo asc");
	if($sql->rows) {
		while($x=mysqli_fetch_object($sql->mysqry)) {
			$_temas[$x->id]=$x;
		}
	}

?>

<section class="content">
	
	<?php
	$_table=$_p."landingpage_formulario";
	$_page=basename($_SERVER['PHP_SELF']);
	$campos=explode(",","data,id_tema,tipo,nome,telefone,email,status,obs");
	$_csv="leads";
	
	if(isset($_GET['form'])) {
		$cnt='';
		
		if(isset($_GET['edita']) and is_numeric($_GET['edita'])) {
			$sql->consult($_table,"*","where id='".$_GET['edita']."'");
			if($sql->rows) {
				$cnt=mysqli_fetch_object($sql->mysqry);
			} else {
				$jsc->jAlert("Registro não encontrado!","erro","document.location.href='".$_page."'");
				die();
			}
		}

		if(empty($cnt)) {
			$jsc->jAlert("Registros não encontrado!","erro","document.location.href='".$_page."'");
			die();
		}
		if(isset($_POST['acao']) and $_POST['acao']=="wlib") {

			$processa=true;

			$vSQL="status='".addslashes($_POST['status'])."',obs='".utf8_decode(addslashes($_POST['obs']))."',id_alteracao=$usr->id,alteracao_data=now()";
			$vWHERE="where id=$cnt->id";
			$sql->update($_table,$vSQL,$vWHERE);
					$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='update',vsql='".addslashes($vSQL)."',vwhere='".addslashes($vWHERE)."',tabela='".$_table."',id_reg='".$cnt->id."'");

			$jsc->go($_page);
			
		}		
	?>
			
	<div class="filtros">
		<h1 class="filtros__titulo">Formulários</h1>
		<div class="filtros-acoes">
			<a href="<?php echo $_page."?".$url;?>"><i class="iconify" data-icon="bx-bx-left-arrow-alt"></i></a>
			<?php
			if(is_object($cnt)) {
			?>		
			<a data-fancybox data-type="ajax" data-src="ajax/log.php?table=<?php echo $_table;?>&id=<?php echo $cnt->id;?>" href="javascript:;"><i class="iconify" data-icon="bx-bx-history"></i></a>
			<?php	
			}
			?>
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
					<legend>Controle</legend>
				
					<div class="colunas4">
						<dl>
							<dt>Status</dt>
							<dd>
								<select name="status" class="obg">
									<option value="">-</option>
									<?php
									foreach($_status as $k=>$v) {
										echo '<option value="'.$k.'"'.(($cnt->status==$k)?' selected':'').'>'.$v['titulo'].'</option>';
									}
									?>
								</select>
							</dd>
						</dl>
					</div>

					<dl>
						<dt>Obs.:</dt>
						<dd><textarea name="obs" style="height:150px;" class="noupper"><?php echo utf8_encode($cnt->obs);?></textarea></dd>
					</dl>
				</fieldset>
				<fieldset>
					<legend>Lead</legend>
				
					<div class="colunas4">
						<dl>
							<dt>Tema</dt>
							<dd>
								<input type="text" disabled value="<?php echo isset($_temas[$cnt->id_tema])?utf8_encode($_temas[$cnt->id_tema]->titulo):"-";?>" />
							</dd>
						</dl>
						<dl>
							<dt>Data</dt>
							<dd>
								<input type="text" disabled value="<?php echo date('d/m/Y H:i',strtotime($cnt->data));?>" />
							</dd>
						</dl>
						<dl>
							<dt>IP</dt>
							<dd>
								<input type="text" disabled value="<?php echo $cnt->ip;?>" />
							</dd>
						</dl>
						<dl>
							<dt>Tipo</dt>
							<dd>
								<input type="text" disabled value="<?php echo strtoupperWLIB($cnt->tipo);?>" />
							</dd>
						</dl>
					</div>

				
					<div class="colunas4">
						<dl class="dl2">
							<dt>Nome</dt>
							<dd>
								<input type="text" disabled value="<?php echo utf8_encode($cnt->nome);?>" />
							</dd>
						</dl>
						<dl>
							<dt>Telefone</dt>
							<dd>
								<input type="text" disabled value="<?php echo utf8_encode($cnt->telefone);?>" class="telefone" />
							</dd>
						</dl>
						<dl>
							<dt>E-mail</dt>
							<dd>
								<input type="text" disabled value="<?php echo utf8_encode($cnt->email);?>" />
							</dd>
						</dl>
					</div>
				</fieldset>
			</form>
		</div>
	</section>
			
	<?php
	} else {	
	?>
			
	<section class="filtros">
		<h1 class="filtros__titulo">Formulário</h1>		
		<form method="get" class="filtros-form">
			<input type="hidden" name="csv" value="0" />
			<dl>
				<dt>Tema</dt>
				<dd>
					<select name="id_tema" class="">
						<option value="">-</option>
						<?php
						foreach($_temas as $x) {
						?>
						<option value="<?php echo $x->id;?>"<?php echo (isset($values['id_tema']) and $x->id==$values['id_tema'])?' selected':'';?>><?php echo utf8_encode($x->titulo);?></option>
						<?php	
						}
						?>
					</select>
				</dd>
			</dl>
			<dl>
				<dt>Busca</dt>
				<dd><input type="text" name="busca" value="<?php echo isset($values['busca'])?$values['busca']:"";?>" class="noupper" /></dd>
			</dl>
			<dl>
				<dt>Status</dt>
				<dd>
					<select name="status">
						<option value="">-</option>
						<?php
						foreach($_status as $k=>$v) {
							echo '<option value="'.$k.'"'.((isset($values['status']) and $values['status']==$k)?' selected':'').'>'.$v['titulo'].'</option>';
						}
						?>
					</select>
				</dd>
			</dl>
			<button type="submit" class="filtros-form__button"><i class="iconify" data-icon="bx-bx-search"></i></button>						
		</form>
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

	if(isset($values['status']) and isset($_status[$values['status']])) $where.=" and status='".$values['status']."'";
	if(isset($values['id_tema']) and is_numeric($values['id_tema'])) $where.=" and id_tema='".$values['id_tema']."'";
	if(isset($values['busca']) and !empty($values['busca'])) $where.=" and (nome like '%".$values['busca']."%' or email like '%".$values['busca']."%' or telefone like '%".telefone($values['busca'])."%')";
	

	if(isset($_GET['csv']) and $_GET['csv']==1) {
		$especificacoes=array('id_tema'=>array('title'=>'TEMA','table'=>$_p.'landingpage_temas','field'=>'titulo','id'=>'id'));
		
		$camposCSV=$campos;
		//array_unshift($camposCSV,"data");
		
		$csv=$adm->csv2($_table,
					   $sql,
					   $where." order by data desc",
					   $camposCSV,$especificacoes);
		?>
		<script>
		window.open('lib/download.php?arq=../<?php echo $csv;?>&nome=<?php echo $_csv;?>.csv');
		</script>
		<?php
	} 


	$sql->consult($_table,"*",$where." order by data desc");
	
	?>

	<section class="grid">
		<div class="box">

			<div class="registros-qtd">
				<p class="registros-qtd__item"><?php echo $sql->rows;?> registros</p>
			</div>

			<div class="registros">
				<table class="tablesorter">
					<thead>
						<tr>
							<th>Data</th>
							<th>Nome</th>
							<th>Tema</th>
							<th>E-mail/Telefone</th>
							<th>Tipo</th>
							<th>Status</th>
						</tr>
					</thead>
					<tbody>
					<?php
					while($x=mysqli_fetch_object($sql->mysqry)) {
					?>
					<tr onclick="document.location.href='<?php echo $_page;?>?form=1&edita=<?php echo $x->id."&".$url;?>'">
						<td><?php echo date('d/m/Y H:i',strtotime($x->data));?></td>
						<td><strong><?php echo utf8_encode($x->nome);?></strong></td>
						<td><?php echo isset($_temas[$x->id_tema])?utf8_encode($_temas[$x->id_tema]->titulo):"-";?></td>
						<td><?php echo empty($x->email)?$x->telefone:utf8_encode($x->email);?></td>
						<td><?php echo strtoupper($x->tipo);?></td>
						<td>
							<?php
							if(isset($_status[$x->status])) {
								echo '<font color="'.$_status[$x->status]['cor'].'">'.$_status[$x->status]['titulo'].'</font>';
							} else echo "-";
							?>
						</td>
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