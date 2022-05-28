<?php
	if(isset($_POST['ajax'])) {
		require_once("lib/conf.php");	
		require_once("usuarios/checa.php");

		$sql = new Mysql();

		$rtn = array();
		if($_POST['ajax']=="indicacoesLista") {
			$indicacao='';
			if(isset($_POST['id_indicacao']) and is_numeric($_POST['id_indicacao'])) {
				$sql->consult($_p."parametros_indicacoes","*","where id='".addslashes($_POST['id_indicacao'])."' and lixo=0");
				if($sql->rows) {
					$indicacao=mysqli_fetch_object($sql->mysqry);
				}
			}

			if(is_object($indicacao)) {
				$sql->consult($_p."parametros_indicacoes_listas","*","where id_indicacao=$indicacao->id and lixo=0 order by titulo asc");
				$indicacoes=array();
				while($x=mysqli_fetch_object($sql->mysqry)) {
					$indicacoes[]=array('id'=>$x->id,
									'titulo'=>utf8_encode($x->titulo));
				}

				$rtn=array('success'=>true,'indicacoes'=>$indicacoes);
			} else {
				$rtn=array('success'=>false,'error'=>'Indicação não definida!');
			}
		}

		header("Content-type: application/json");
		echo json_encode($rtn);
		die();
	}

	require_once("lib/conf.php");
	$_table=$_p."pacientes";

	include "includes/header.php";
	include "includes/nav.php";

	$values=$adm->get($_GET);
	$campos=explode(",","nome,situacao,sexo,foto_cn,rg,rg_orgaoemissor,rg_uf,cpf,data_nascimento,profissao,estado_civil,telefone1,telefone1_whatsapp,telefone1_whatsapp_permissao,telefone2,email,instagram,instagram_naopossui,musica,indicacao_tipo,indicacao,cep,endereco,numero,complemento,bairro,estado,cidade,id_cidade,responsavel_possui,responsavel_nome,responsavel_sexo,responsavel_rg,responsavel_rg_orgaoemissor,responsavel_rg_estado,responsavel_datanascimento,responsavel_estadocivil,responsavel_cpf,responsavel_profissao,responsavel_grauparentesco,preferencia_contato,estrangeiro,estrangeiro_passaporte,lat,lng,responsavel_estado_civil");

	$_profissoes=array();
	$sql->consult($_p."parametros_profissoes","*","where lixo=0 order by titulo asc");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_profissoes[$x->id]=$x;
	}



	$_profissionais=array();
	$sql->consult($_p."colaboradores","*","where check_agendamento=1 and lixo=0 order by nome asc");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_profissionais[$x->id]=$x;
	}

	$values=array();
	foreach($campos as $v) {
		$values[$v]='';
	}

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


	<?php

	$_cidades=array();
	$sql->consult($_p."cidades","*","");
	while($x=mysqli_fetch_object($sql->mysqry)) $_cidades[$x->id]=$x;
	
	
	$where="WHERE lixo='0'";
	if(isset($_GET['busca']) and !empty($_GET['busca'])) {
		//$where.=" and (nome like '%".utf8_decode($values['busca'])."%' or cpf like '%".cpf($values['busca'])."%' or id = '".addslashes($values['busca'])."')";
		$wh="";
		$aux = explode(" ",$_GET['busca']);
		$primeiraLetra='';
		foreach($aux as $v) {
			if(empty($v)) continue;

			if(empty($primeiraLetra)) $primeiraLetra=substr($v,0,1);
			$wh.="nome REGEXP '$v' and ";
		}
		$wh=substr($wh,0,strlen($wh)-5);
		$where="where (($wh) or nome like '%".$_GET['busca']."%' or telefone1 like '%".$_GET['busca']."%' or cpf like '%".$_GET['busca']."%') and lixo=0";
		
		
	}

	if(isset($_GET['periodicidade']) and !empty($_GET['periodicidade'])) $where.=" and periodicidade='".addslashes($_GET['periodicidade'])."'";

	if(isset($_GET['profissional_multiple']) and is_array($_GET['profissional_multiple']) and count($_GET['profissional_multiple'])>0) $where.=" and profissional_maisAtende IN (".implode(",",$_GET['profissional_multiple']).")";
	
	$where.=" order by nome asc";
	
	$registros=array();
	$sql->consult($_table,"*",$where);
	if($sql->rows) {
		while($x=mysqli_fetch_object($sql->mysqry)) {
			$registros[$x->codigo_bi][]=$x;
		}
	}

	?>

	<main class="main">
		<div class="main__content content">
			<section class="filter">
				
				<div class="filter-group">
					<div class="filter-form form">
						<dl>
							<dd><a href="pg_pacientes.php?form=1" class="button button_main"><i class="iconify" data-icon="fluent:add-circle-24-regular"></i> <span>Novo Paciente</span></a></dd>
						</dl>
					</div>
				</div>

				<form method="get" class="js-filtro">
					<div class="filter-group">
						<div class="filter-form form">
							<dl>
								<dd>
									<select name="profissional_multiple[]" multiple class="chosen" data-placeholder="Profissionais...">
										<option value=""></option>
										<?php
										foreach($_profissionais as $x) {
											echo '<option value="'.$x->id.'"'.((isset($_GET['profissional_multiple']) and is_array($_GET['profissional_multiple']) and in_array($x->id,$_GET['profissional_multiple']))?" selected":"").'>'.utf8_encode($x->nome).'</option>';
										}
										?>
									</select>
								</dd>
							</dl>

							<dl>
								<dd>
									<select name="periodicidade" placeholder="Periodicidade">
										<option value="">Período...</option>
										<?php
										foreach($_pacientesPeriodicidade as $k=>$v) {
											echo '<option value="'.$k.'"'.((isset($_GET['periodicidade']) and $_GET['periodicidade']==$k)?" selected":"").'>'.$v.'</option>';
										}
										?>
									</select>
								</dd>
							</dl>
							<dl>
								<dd class="form-comp form-comp_pos"><input type="text" name="busca" placeholder="Buscar..." value="<?php echo isset($_GET['busca'])?($_GET['busca']):"";?>" /><a href="javascript:;" onclick="$('form.js-filtro').submit();"><i class="iconify" data-icon="fluent:search-12-filled"></i></a></dd>
							</dl>
						</div>					
					</div>
				</form>

			</section>

			<section class="grid" style="flex:1;">
				<div class="kanban" style="grid-template-columns:repeat(4,minmax(0,1fr));">
					<?php
					foreach($_codigoBI as $codigoBI=>$biTitulo) {
						if($codigoBI==3 or $codigoBI==4 or $codigoBI==5) continue;

					?>
					<div class="kanban-item" style="background:<?php echo $_codigoBICores[$codigoBI];?>;color:var(--cor1);">
						<header>
							<h1><?php echo $biTitulo;?> (<?php echo (isset($registros[$codigoBI]))?number_format(count($registros[$codigoBI]),0,"","."):0;?>)</h1>
						</header>
						<article>
						<?php
						if(isset($registros[$codigoBI])) {
							foreach($registros[$codigoBI] as $x) {
								$cor='';
						?>
							<a href="pg_pacientes_resumo.php?id_paciente=<?php echo $x->id;?>"  target="_blank">
								<h1 style="color:#333"><?php echo strtoupperWLIB(utf8_encode($x->nome));?></h1>
								<p>Código: <?php echo $x->id;?></p>
							</a>
						<?php
							}
						}	
						?>						
						</article>
					</div>
					<?php
					}
					?>
					
				</div>
			</section>
		</div>
	</main>

<?php
	$apiConfig=array('profissao'=>1);
	require_once("includes/api/apiAside.php");
	include "includes/footer.php";
?>	