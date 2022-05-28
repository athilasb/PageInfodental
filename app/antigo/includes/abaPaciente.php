<?php
	if(empty($paciente)) {
		$jsc->jAlert("Paciente não encontrado!","erro","document.location.href='pg_contatos_pacientes.php'");
		die();
	}

	if(isset($_POST['pacienteHiddenFoto'])) {
		if(isset($_FILES['foto']) and !empty($_FILES['foto']['tmp_name'])) {
			$ext = explode(".",$_FILES['foto']['name']);
			$ext = strtolower($ext[count($ext)-1]);

			$extensoes = array("jpg","gif","png","jpeg","heic");

			if(!in_array($ext, $extensoes)) {
				$jsc->jAlert("Extensão ($ext) não permitida!","erro","");
			} else {
				$img=new Canvas();

				try {
					$img->carrega( $_FILES['foto']['tmp_name'] )
										->redimensiona( 300, 300, 'crop' )
										->hexa( '#FFFFFF' )
										->grava("arqs/pacientes/".$paciente->id.".".$ext);

					$sql->update($_p."pacientes","foto='".$ext."'","where id=$paciente->id");
					$jsc->jAlert("Foto alterada com sucesso!","success","document.location.href='?".$url."'");
					die();

				}  catch (Exception $e) {
				   $jsc->jAlert("Algum erro ocorreu durante o uploa da foto","erro","");
				}
				
			}
		}
	}

	if(isset($_GET['deletaPaciente']) and is_numeric($_GET['deletaPaciente']) and $paciente->id==$_GET['deletaPaciente']) {
		$vsql="lixo=1";
		$vwhere="where id=$paciente->id";
		$sql->update($_p."pacientes",$vsql,$vwhere);
		
		$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='update',vsql='".addslashes($vsql)."',vwhere='".addslashes($vwhere)."',tabela='".$_p."pacientes',id_reg='".$paciente->id."'");

		$jsc->jAlert("Paciente excluído com sucesso!","sucesso","document.location.href='pg_contatos_pacientes.php'");
		die();
	}
?>
<section class="filtros" style="padding-bottom:0; margin-bottom:-.5rem;">	
	<div class="filtros-paciente">
		<?php
		$ftPaciente='img/ilustra-perfil.png';
		$ft='arqs/pacientes/'.$paciente->id.".".$paciente->foto;
		if(file_exists($ft)) {
			$ftPaciente=$ft;
		}
		?>
		<a href="box/boxPacienteFoto.php?id_paciente=<?php echo $paciente->id;?>" data-fancybox data-type="ajax"><img src="<?php echo $ftPaciente;?>" alt="<?php echo utf8_encode($paciente->nome);?>" width="62" height="62" class="filtros-paciente__img" /></a>
		<div class="filtros-paciente__inner1">
			<h1><?php echo utf8_encode($paciente->nome);?></h1>
			<p><?php echo idade($paciente->data_nascimento);?> anos</p>
			<p><span style="color:var(--cinza3);">#<?php echo $paciente->id;?></span>
			<?php

			if($_infodentalCompleto==1 and isset($_codigoBI[$paciente->codigo_bi])) {
			?>
			<p><span style="background:var(--cinza4);color:#FFF;padding:4px;border-radius: 5px;"><?php echo $_codigoBI[$paciente->codigo_bi];?></span>
			<?php
			}
			?>
		</div>		
	</div>
	<?php /*<div class="filtros-acoes">
		<a href="pg_contatos_pacientes.php"><i class="iconify" data-icon="bx-bx-search"></i></a>
		<a href="?deletaPaciente=<?php echo $paciente->id."&".$url;?>" class="sec js-deletar" ><i class="iconify" data-icon="bx-bx-trash"></i></a>
	</div>*/?>
</section>

<ul class="abas">
	<li><a href="pg_contatos_pacientes_resumo.php?<?php echo "id_paciente=$paciente->id";?>" class="main-nav__resumo-do-paciente<?php echo basename($_SERVER['PHP_SELF'])=="pg_contatos_pacientes_resumo.php"?" active":"";?>">Resumo do Paciente</a></li>
	<?php
	if($_infodentalCompleto==1) {
	?>
	<li><a href="pg_contatos_pacientes_evolucao.php?<?php echo "id_paciente=$paciente->id";?>" class="main-nav__evolucao-e-laboratorio<?php echo basename($_SERVER['PHP_SELF'])=="pg_contatos_pacientes_evolucao.php"?" active":"";?>">Prontuário</a></li>

	<?php /*<li><a href="pg_contatos_pacientes_laboratorio.php?<?php echo "id_paciente=$paciente->id";?>" class="main-nav__laboratorio<?php echo basename($_SERVER['PHP_SELF'])=="pg_contatos_pacientes_laboratorio.php"?" active":"";?>">Laboratório</a></li>*/?>

	<li><a href="pg_contatos_pacientes_tratamento.php?<?php echo "id_paciente=$paciente->id";?>" class="main-nav__tratamento<?php echo basename($_SERVER['PHP_SELF'])=="pg_contatos_pacientes_tratamento.php"?" active":"";?>">Plano de Tratamento</a></li>

	<li><a href="pg_contatos_pacientes_financeiro.php?<?php echo "id_paciente=$paciente->id";?>" class="main-nav__financeiro<?php echo basename($_SERVER['PHP_SELF'])=="pg_contatos_pacientes_financeiro.php"?" active":"";?>">Financeiro</a></li>

	<?php /*<li><a href="javascript:;" class="main-nav__programa-de-fidelidade<?php echo basename($_SERVER['PHP_SELF'])=="pg_contatos_pacientes_programadefidelidade.php"?" active":"";?>">Programa de Fidelidade</a></li>*/?>
	
	<li><a href="javascript:;" class="main-nav__arquivo-e-documentos<?php echo basename($_SERVER['PHP_SELF'])=="pg_contatos_pacientes_arquivoedocumentos.php"?" active":"";?>">Arquivos</a></li>
	<?php
	}
	?>
	<li><a href="pg_contatos_pacientes_dadospessoais.php?<?php echo "id_paciente=$paciente->id";?>" class="main-nav__dados-pessoais<?php echo basename($_SERVER['PHP_SELF'])=="pg_contatos_pacientes_dadospessoais.php"?" active":"";?>">Dados Pessoais</a></li>


</ul>
