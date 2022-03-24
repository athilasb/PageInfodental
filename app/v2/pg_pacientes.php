<?php
	require_once("lib/conf.php");
	$_table=$_p."pacientes";

	include "includes/header.php";
	include "includes/nav.php";

	$values=$adm->get($_GET);
	$campos=explode(",","titulo");

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

	$pacientes=0;
	$sql->consult($_p."pacientes","count(*) as total","where lixo=0");
	$x=mysqli_fetch_object($sql->mysqry);
	$pacientes=$x->total;
?>

	<header class="header">
		<div class="header__content content">
			<div class="header__inner1">
				<section class="header-title">
					<h1>Pacientes</h1>
				</section>
				<?php
				require_once("includes/menus/menuPacientes.php");
				?>
			</div>
		</div>
	</header>
	<script type="text/javascript">
		
		$(function(){
			
			$('.js-openAside').click(function(){
				$('#js-aside form.formulario-validacao').trigger('reset');
				openAside(0);
			})
			$('.list1').on('click','.js-item',function(){
				$('#js-aside form.formulario-validacao').trigger('reset');
				let id = $(this).attr('data-id');
				document.location.href=`pg_pacientes_dadospessoais.php?id_paciente=${id}`;
			})
		})
	</script>

	<main class="main">
		<div class="main__content content">

			<section class="filter">
				
				<div class="filter-group">
					<div class="filter-form form">
						<dl>
							<dd><a href="" class="button button_main"><i class="iconify" data-icon="fluent:add-circle-24-regular"></i> <span>Novo Paciente</span></a></dd>
						</dl>
					</div>
				</div>

				<form method="get" class="js-filtro">
				<div class="filter-group">
					<div class="filter-form form">
						<dl>
							<dd class="form-comp form-comp_pos"><input type="text" name="busca" placeholder="Buscar..." value="<?php echo isset($values['busca'])?($values['busca']):"";?>" /><a href="javascript:;" onclick="$('form.js-filtro').submit();"><i class="iconify" data-icon="fluent:search-12-filled"></i></a></dd>
						</dl>
					</div>					
				</div>
			</form>

			</section>
 	
			<section class="grid" style="grid-template-columns:40% auto">

				<div class="box">

					<div class="filter">
						<div class="filter-group">
							<div class="filter-title">
								<h1>Indicadores</h1>
							</div>
						</div>
						<div class="filter-group">
							<div class="filter-title">
								<h1><?php echo number_format($pacientes,0,"",".");?> pacientes</h1>
							</div>
						</div>
					</div>

					<div class="list4">
						
						<a href="" class="list4-item active">
							<div>
								<h1><i class="iconify" data-icon="fluent:food-cake-20-regular"></i></h1>
							</div>
							<div>
								<p>Distribuição <strong>por Idade</strong></p>
							</div>
						</a>
						<a href="" class="list4-item">
							<div>
								<h1><i class="iconify" data-icon="ph:gender-intersex"></i></h1>
							</div>
							<div>
								<p>Distribuição <strong>por Gênero</strong></p>
							</div>
						</a>
						<a href="" class="list4-item">
							<div>
								<h1><i class="iconify" data-icon="fluent:location-20-regular"></i></h1>
							</div>
							<div>
								<p>Distribuição <strong>Localização</strong></p>
							</div>
						</a>
						<a href="" class="list4-item">
							<div>
								<h1><i class="iconify" data-icon="fluent:person-add-20-regular"></i></h1>
							</div>
							<div>
								<p>Novos pacientes <strong>9 por mês</strong></p>
							</div>
						</a>

					</div>

					<section style="width:100%; height:300px; background:var(--cinza2); margin-bottom:var(--margin1);">						
					</section>
				</div>

				<div class="box">

					<?php
					# LISTAGEM #
					$where="WHERE lixo='0'";
					if(isset($values['busca']) and !empty($values['busca'])) {
						//$where.=" and (nome like '%".utf8_decode($values['busca'])."%' or cpf like '%".cpf($values['busca'])."%' or id = '".addslashes($values['busca'])."')";
						$wh="";
						$aux = explode(" ",$_GET['busca']);

						foreach($aux as $v) {
							$wh.="nome REGEXP '$v' and ";
						}
						$wh=substr($wh,0,strlen($wh)-5);
						$where="where (($wh) or nome like '%".$_GET['busca']."%' or telefone1 like '%".$_GET['busca']."%' or cpf like '%".$_GET['busca']."%') and lixo=0";
					}

					
					$where.=" order by nome asc";
					$sql->consultPagMto2($_table,"*",10,$where,"",15,"pagina",$_page."?".$url."&pagina=");
					if($sql->rows==0) {
						if(isset($values['busca'])) $msg="Nenhum Resultado encontrado";
						else $msg="Nenhum colaborador cadastrado";

						echo "<center>$msg</center>";
					} else {
					?>	
						<div class="list1">
							<table>
								<?php
								while($x=mysqli_fetch_object($sql->mysqry)) {
									$cor="var(--cinza3)";
									if(isset($_codigoBICores[$x->codigo_bi])) $cor=$_codigoBICores[$x->codigo_bi];
								/*?>
								<tr class="js-item" data-id="<?php echo $x->id;?>">
									<td style="width:20px;"><i class="iconify" data-icon="fluent:chevron-up-down-24-regular"></i></td>
									<td><h1><strong><?php echo utf8_encode($x->titulo);?></strong></h1></td>
								</tr>*/
								?>
								<tr class="js-item" data-id="<?php echo $x->id;?>">
									<td class="list1__border" style="color:<?php echo $cor;?>"></td>
									<td>
										<h1><?php echo utf8_encode($x->nome);?></h1>
										<p>#<?php echo utf8_encode($x->id);?></p>
									</td>
									<td><?php echo isset($_codigoBI[$x->codigo_bi])?$_codigoBI[$x->codigo_bi]:"";?></td>
									<td><?php echo $x->data_nascimento!="0000-00-00"?idade($x->data_nascimento)." anos":"";?></td>
									<td><?php echo !empty($x->telefone1)?mask($x->telefone1):"-";?></td>
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

			</section>
		
		</div>
	</main>

<?php 
include "includes/footer.php";
?>	