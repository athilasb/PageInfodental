<?php
	$title="";
	include "includes/header.php";
	include "includes/nav.php";

	if($usr->tipo!="admin" and !in_array("clientes",$_usuariosPermissoes)) {
		$jsc->jAlert("Você não tem permissão para acessar esta área!","erro","document.location.href='dashboard.php'");
		die();
	}
	$values=$adm->get($_GET);

	$_profissoes=array();
	$sql->consult($_p."parametros_profissoes","*","where lixo=0 order by titulo asc");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_profissoes[$x->id]=$x;
	}


	$_pacienteIndicacoes=array();
	$sql->consult($_p."parametros_indicacoes","*","where lixo=0 order by titulo asc");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_pacienteIndicacoes[$x->id]=$x;
	}

	$_pacienteGrauDeParentesco=array();
	$sql->consult($_p."parametros_grauparentesco","*","where lixo=0 order by titulo asc");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_pacienteGrauDeParentesco[$x->id]=$x;
	}

	$_formas='';
	$sql->consult($_p."parametros_formasdepagamento","*","order by titulo asc");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_formas[$x->id]=$x;
	}

?>
<section class="content">

	<?php /*<header class="caminho">
		<h1 class="caminho__titulo">Contatos <i class="iconify" data-icon="bx-bx-chevron-right"></i> <strong>Pacientes</strong></h1>
		<a href="javascript:;" class="caminho__tutorial button button__sec"><i class="iconify" data-icon="ic-baseline-slow-motion-video"></i> ASSISTIR TUTORIAL</a>
	</header>*/?>

	<?php
	$_table=$_p."financeiro_fluxo";
	$_page=basename($_SERVER['PHP_SELF']);

	$_status=array('avencer'=>'A Vencer',
					'vencido'=>'Vencido',
					'pagorecebido'=>'Pago/Recebido');

	$_receber=(isset($_GET['receber']) and $_GET['receber']==1)?1:0;

	
	if(isset($_GET['form'])) {

		$cnt='';
		$campos=explode(",","data_vencimento,valor,descricao");
		
		foreach($campos as $v) $values[$v]='';
		$values['data_vencimento']=date('d/m/Y');

		if(isset($_GET['edita']) and is_numeric($_GET['edita'])) {
			$sql->consult($_table,"*,date_format(data,'%d/%m/%Y %H:%i') as dataf","where id='".$_GET['edita']."'");
			if($sql->rows) {
				$cnt=mysqli_fetch_object($sql->mysqry);
				
				$values=$adm->values($campos,$cnt);
				$values['data']=$cnt->dataf;

			} else {
				$jsc->jAlert("Informação não encontrada!","erro","document.location.href='".$_page."'");
				die();
			}
		}
		if(isset($_POST['acao']) and $_POST['acao']=="wlib") {
			$vSQL=$adm->vSQL($campos,$_POST);
			$values=$adm->values;
			$processa=true;

			if(empty($cnt) or (is_object($cnt) and $cnt->cpf!=cpf($_POST['cpf']))) {
				$sql->consult($_table,"*","where cpf='".addslashes(cpf($_POST['cpf']))."' and lixo=0");

				if($sql->rows) {
					$processa=false;
					$jsc->jAlert("Já existe cliente cadastrado com este CPF","erro",""); 
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
					$sql->add($_table,$vSQL."data=now(),id_usuario='".$usr->id."'");
					$id_reg=$sql->ulid;
					$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='insert',vsql='".addslashes($vSQL)."',tabela='".$_table."',id_reg='".$sql->ulid."'");
				}

				$msgErro='';
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
				if(!empty($msgErro)) {
					$jsc->jAlert($msgErro,"erro","");
				} else {
					$jsc->jAlert("Informações salvas com sucesso!","sucesso","document.location.href='".$_page."?form=1&edita=".$id_reg."&".$url."'");
					die();
				}
			}
		}	
	?>
		<script type="text/javascript">
			$(function(){
				$('input.money').maskMoney({symbol:'', allowZero:true, showSymbol:true, thousands:'.', decimal:',', symbolStay: true});
			})
		</script>
		<section class="grid">
			<div class="box">

				<form method="post" class="form formulario-validacao"  autocomplete="off" enctype="multipart/form-data">
					<input type="hidden" name="acao" value="wlib" />

					<div class="filter">
						<div class="filter-group">
							<div class="filter-button">
								<a href="<?php echo $_page;?>"><i class="iconify" data-icon="bx-bx-left-arrow-alt"></i></a>
							</div>
						</div>
						<div class="filter-group filter-group_right">
							<div class="filter-button">
								<?php if(is_object($cnt)){?><a href="<?php echo $_page;?>?deleta=<?php echo $cnt->id."&".$url;?>" class="js-deletar"><i class="iconify" data-icon="bx-bx-trash"></i></a><?php }?>
								<a href="javascript:;" class="azul btn-submit"><i class="iconify" data-icon="bx-bx-check"></i><span>salvar</span></a>
							</div>
						</div>
					</div>
					<fieldset>
						<legend><?php echo $_receber==1?"Conta à Receber":"Conta à Pagar";?></legend>
						<div class="colunas6">
							<dl>
								<dt>Vencimento</dt>
								<dd><input type="text" name="data_vencimento" value="<?php echo $values['data_vencimento'];?>" class="obg data datecalendar"></dd>
							</dl>
							<dl>
								<dt>Valor</dt>
								<dd><input type="text" name="valor" value="<?php echo $values['valor'];?>" class="obg money"></dd>
							</dl>
						</div>
						<div class="colunas6">
							<dl class="dl2">
								<dt>Categoria</dt>
								<dd>
									<select>
										
									</select>
								</dd>
							</dl>
							<dl class="dl4">
								<dt>Descriçao</dt>
								<dd><input type="text" name="descricao" /></dd>
							</dl>
						</div>
						<script type="text/javascript">
							$(function(){
								<?php
								if($_receber==0) {
								?>
								$('input[name=valor]').keypress(function(){
									var val = eval($(this).val().replace(/[^0-9,-]/g, "").replace(',','.'));
									if(val>0) $(this).val(number_format((val*-1),2,",","."));
								});
								<?php	
								}
								?>
							});
						</script>


						<dl>
							<dt>Paciente</dt>
							<dd><input type="text" name="id_paciente" /></dd>
						</dl>
						<dl>
							<dt>Fornecedor</dt>
							<dd><input type="text" name="id_fornecedor" /></dd>
						</dl>

						<dl>
							<dt>Colaborador</dt>
							<dd><input type="text" name="id_colaborador" /></dd>
						</dl>


					</fieldset>
				</form>
			</div>
		</section>
	<?php
	} else {


		if(!isset($values['data_inicio']) or empty($values['data_inicio'])) {
			$values['data_inicioWH']=date('Y-m-01');
			$values['data_inicio']=date('01/m/Y');
		}

		if(!isset($values['data_fim']) or empty($values['data_fim'])) {
			$values['data_fimWH']=date('Y-m-t');
			$values['data_fim']=date('t/m/Y');
		}

	?>

		<section class="grid ">
			<div class="box">
				<div class="filter">

					<div class="filter-group">
						<div class="filter-button">
							<a href="?form=1" class="verde"><i class="iconify" data-icon="bx-bx-plus"></i><span>Nova Conta</span></a>
						</div>
					</div>

					
					<div class="filter-group filter-group_right">
						<form method="get" class="filter-form">
							<input type="hidden" name="csv" value="0" />
							<dl>
								<dd><input type="text" name="data_inicio" value="<?php echo isset($values['data_inicio'])?$values['data_inicio']:"";?>" class="noupper data datecalendar" placeholder="De" /></dd>
							</dl>
							<dl>
								<dd><input type="text" name="data_fim" value="<?php echo isset($values['data_fim'])?$values['data_fim']:"";?>" class="noupper data datecalendar" placeholder="Até" /></dd>
							</dl>
							<dl>
								<dd>
									<select name="status" placeholder="Status">
										<option value="">Status</option>
										<?php
										foreach($_status as $k=>$v) {
										?>
										<option value="<?php echo $k;?>"><?php echo utf8_encode($v);?></option>
										<?php	
										}
										?>
									</select>
								</dd>
							</dl>
							<dl>
								<dd>
									<select name="id_formapagamento" placeholder="Forma de Pagamento">
										<option value="">Forma de Pagamento</option>
										<?php
										foreach($_formas as $k=>$v) {
										?>
										<option value="<?php echo $k;?>"><?php echo utf8_encode($v->titulo);?></option>
										<?php	
										}
										?>
									</select>
								</dd>
							</dl>
							<button type="submit"><i class="iconify" data-icon="bx-bx-search"></i></button>
						</form>
					</div>

				</div>
				<?php
				$where="WHERE lixo='0'";
				if(isset($values['busca']) and !empty($values['busca'])) $where.=" and (nome like '%".utf8_decode($values['busca'])."%' or cpf like '%".cpf($values['busca'])."%' or id = '".addslashes($values['busca'])."')";
				
				//echo $where;

				?>
				<div class="reg">
					<?php
					$sql->consultPagMto2($_table,"*",10,$where,"",15,"pagina",$_page."?".$url."&pagina=");
					if($sql->rows==0) {
						$msgSemResultado="Nenhuma conta";

						echo "<center>$msgSemResultado</center>";
					} else {
						while($x=mysqli_fetch_object($sql->mysqry)) {
					?>
					<a href="pg_contatos_pacientes_resumo.php?id_paciente=<?php echo $x->id?>" class="reg-group">
						<div class="reg-color" style="background-color:var(--cinza3)"></div>
						<div class="reg-data" style="flex:0 1 50%;">
							<h1><?php echo strtoupperWLIB(utf8_encode($x->nome));?></h1>
							<p>Código: <?php echo $x->id;?></p>
						</div>
						<div class="reg-data" style="flex:0 1 70px;">
							<p><?php echo $x->data_nascimento!="0000-00-00"?idade($x->data_nascimento)." anos":"";?></p>
						</div>
						<div class="reg-data" style="flex:0 1 100px;">
							<p><?php echo !empty($x->telefone1)?mask($x->telefone1):"";?></p>
						</div>
						
					</a>
					<?php
						}

						if(isset($sql->myspaginacao) and !empty($sql->myspaginacao)) {
						?>	
					<div class="paginacao" style="margin-top: 30px;">
						<p class="paginacao__item"><span>Página</span><?php echo $sql->myspaginacao;?></p>
					</div>
						<?php
						}
					}
					?>
				</div>
				
				<?php /*<div class="registros">
					<table class="tablesorter" style="overflow: none;">
						<thead>
							<tr>
								<th style="width:70px;">Código</th>
								<th>Nome</th>
								<th>Telefone</th>
							</tr>
						</thead>
						<tbody>
						<?php
						
						if($sql->rows==0) {
							$msgSemResultado="Nenhum paciente";
							if(isset($values['busca'])) $msgSemResultado="Nenhum paciente encontrado";
						?>
						<tr>	
							<td colspan="4"><center><?php echo $msgSemResultado;?></center></td>
						</tr>
						<?php
						} else {
							while($x=mysqli_fetch_object($sql->mysqry)) {
						?>
						<tr onclick="document.location.href='pg_contatos_pacientes_resumo.php?id_paciente=<?php echo $x->id?>'">
							<td><?php echo $x->id;?></td>
							<td><?php echo utf8_encode($x->nome);?></td>
							<td><?php echo mask($x->telefone1);?></td>
						</tr>
						<?php
							}
						}
						?>
						</tbody>
					</table>
					<?php
					if(isset($sql->myspaginacao) and !empty($sql->myspaginacao)) {
					?>	
						
					<div class="paginacao">
						<p class="paginacao__item"><span>Página</span><?php echo $sql->myspaginacao;?></p>
					</div>
					<?php
					}
					?>
				</div>*/?>
			</div>
		</section>

	<?php
	}
	?>

</section>

<?php
	include "includes/footer.php";
?>