<?php
	$title="";
	include "includes/header.php";
	include "includes/nav.php";

	if($usr->tipo!="admin" and !in_array("parametros",$_usuariosPermissoes)) {
		$jsc->jAlert("Você não tem permissão para acessar esta área!","erro","document.location.href='dashboard.php'");
		die();
	}
	$values=$adm->get($_GET);
?>
<script>
	$(function(){
		$('.m-parametros').next().show();		
	});
</script>
<section id="conteudo">
	
	<div class="box-caminho">
		<a href="javascript" class="js-collapse"><span></span></a>
		<h1>Parâmetros <i class="icon-angle-right"></i> Parcelamento Taxas</h1>
	</div>
	
	<?php
	$_table=$_p."parcelamentotaxas";
	$_page=basename($_SERVER['PHP_SELF']);

	$_formaspagamento=array();
	$sql->consult($_p."formaspagamento","*","where taxaparcelamento=1 and lixo=0 order by titulo asc");
	if($sql->rows) {
		while($x=mysqli_fetch_object($sql->mysqry)) {
			$_formaspagamento[$x->id]=$x;
		}
	}
	
	if(isset($_GET['form'])) {
		$cnt='';
		$campos=explode(",","id_formapagamento,quantidade,taxa");
		
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
			$vSQL=$adm->vSQL($campos,$_POST);
			$values=$adm->values;
			$processa=true;

			if(isset($_POST['taxa'])) $_POST['taxa']=str_replace(",",".",$_POST['taxa']);
			
			//if(empty($cnt)) $vSQL.="code='".codeIn($_table,outUrl(utf8_encode($_POST['titulo'])))."',";
			//else $vSQL.="code='".codeIn2($_table,outUrl(utf8_encode($_POST['titulo'])),$cnt->id)."',";
			
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

				$jsc->jAlert("Informações salvas com sucesso!","sucesso","document.location.href='".$_page."?form=1&edita=".$id_reg."&".$url."'");
				die();
			}
		}	
	?>

	<div class="box-botoes clearfix">
		<a href="<?php echo $_page."?".$url;?>" class="botao"><i class="icon-left-big"></i> Voltar</a>
		<?php if(is_object($cnt)) {?><a data-fancybox data-type="ajax" data-src="ajax/log.php?table=<?php echo $_table;?>&id=<?php echo $cnt->id;?>" href="javascript:;" class="botao" ><i class="icon-info-circled"></i> Logs</a><?php } ?>
		<a href="javascript://" class="botao botao-principal btn-submit"><i class="icon-ok"></i> Salvar</a>

	</div>
	<div class="box-form">
		<script type="text/javascript">
			$(function(){
				$('input.money').maskMoney({symbol:'', allowZero:true, showSymbol:true, thousands:'.', decimal:',', symbolStay: true});
			});
		</script>
		<form method="post" class="formulario-validacao"  autocomplete="off" enctype="multipart/form-data">
			<input type="hidden" name="acao" value="wlib" />
			<fieldset>
				<legend>Dados do Parcelamento</legend>

				<div class="colunas4">
					<dl class="dl2">
						<dt>Forma de Pagamento</dt>
						<dd>
							<select name="id_formapagamento" class="obg chosen">
								<option value=""></option>
								<?php
								foreach($_formaspagamento as $x) {
								?>
								<option value="<?php echo $x->id;?>"<?php echo $x->id==$values['id_formapagamento']?'selected':'';?>><?php echo utf8_encode($x->titulo);?></option>
								<?php	
								}
								?>
							</select>
						</dd>
					</dl>
					<dl>
						<dt>Quantidade</dt>
						<dd>
							<select name="quantidade" class="obg">
								<?php
								for($i=1;$i <=24;$i++) {
								?>
								<option value="<?php echo $i;?>"<?php echo $i==$values['quantidade']?'selected':'';?>><?php echo $i;?></option>
								<?php	
								}
								?>
							</select>
						</dd>
					</dl>
					
					<dl>
						<dt>Taxa</dt>
						<dd>
							<input type="text" name="taxa" value="<?php echo $values['taxa'];?>"  class="obg money" />
						</dd>
					</dl>
				</div>
			</fieldset>
	
		</form>

	</div>
	<?php
	} else {
	
	?>
	<div class="box-botoes clearfix">
		<a href="<?php echo $_page;?>?form=1<?php echo "&".$url;?>" class="botao botao-principal"><i class="icon-plus"></i> Adicionar</a>
	</div>


	<div class="box-filtros clearfix">
		<form method="get" class="formulario-validacao js-filtro">
			<input type="hidden" name="csv" value="0" />
			<div class="colunas4">
				<dl>
					<dt>Forma de Pagamento</dt>
					<dd>
						<select name="id_formapagamento" class="chosen">
							<option value=""></option>
							<?php
							foreach($_formaspagamento as $x) {
							?>
							<option value="<?php echo $x->id;?>"<?php echo (isset($values['id_formapagamento']) and $x->id==$values['id_formapagamento'])?' selected':'';?>><?php echo utf8_encode($x->titulo);?></option>
							<?php	
							}
							?>
						</select>
					</dd>
				</dl>
				<dt>&nbsp;</dt>			
				<dd><button type="submit"><i class="icon-search"></i> Filtrar</button></dd>
			</div>
		</form>
	</div>

	<div class="box-registros">
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

		if(isset($values['id_formapagamento']) and is_numeric($values['id_formapagamento'])) $where.=" and id_formapagamento='".$values['id_formapagamento']."'";
		//echo $where;
		if($usr->login=="wlib" and isset($_GET['cmd'])) echo $where;
		$sql->consult($_table,"*",$where." order by id desc");
		
		?>
		<div class="opcoes clearfix">
			<div class="qtd"><?php echo $sql->rows;?> registros</div>
			<?php /*<div class="link"><a href="javascript://" id="btn-csv"><i class="icon-doc-text"></i>exportar</a></div>*/ ?>
		</div>

		<table class="tablesorter">
			<thead>
				<tr>
					<th>Forma de Pagamento</th>
					<th>Quantidade</th>
					<th>Taxa</th>
					<th style="width:100px;">Ações</th>
				</tr>
			</thead>
			<tbody>
			<?php
			while($x=mysqli_fetch_object($sql->mysqry)) {
			?>
			<tr>
				<td><?php echo isset($_formaspagamento[$x->id_formapagamento])?utf8_encode($_formaspagamento[$x->id_formapagamento]->titulo):"-";?></td>
				<td><?php echo utf8_encode($x->quantidade);?></td>
				<td><?php echo number_format($x->taxa,2,",","."); ?></td>
				<td>
					<a href="<?php echo $_page;?>?form=1&edita=<?php echo $x->id."&".$url;?>" class="tooltip botao botao-principal" title="editar"><i class="icon-pencil"></i></a>
					<?php if($usr->tipo=="admin") { ?><a href="<?php echo $_page;?>?deleta=<?php echo $x->id."&".$url;?>" class="js-deletar tooltip botao botao-principal" title="excluir "><i class="icon-cancel"></i></a><?php } ?>
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