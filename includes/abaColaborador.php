<?php
	if(empty($colaborador)) {
		$jsc->jAlert("Colaborador não encontrado!","erro","document.location.href='pg_colaboradores.php'");
		die();
	}

	if(isset($_POST['colaboradorHiddenFoto'])) {
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
										->grava("arqs/colaboradores/".$colaborador->id.".".$ext);

					$sql->update($_p."colaboradores","foto='".$ext."'","where id=$colaborador->id");
					$jsc->jAlert("Foto alterada com sucesso!","success","document.location.href='?".$url."'");
					die();

				}  catch (Exception $e) {
				   $jsc->jAlert("Algum erro ocorreu durante o upload da foto","erro","");
				}
				
			}
		}
	}

	if(isset($_GET['deletaColaborador']) and is_numeric($_GET['deletaColaborador']) and $colaborador->id==$_GET['deletaColaborador']) {
		$vsql="lixo=1";
		$vwhere="where id=$colaborador->id";
		$sql->update($_p."colaboradores",$vsql,$vwhere);
		
		$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='update',vsql='".addslashes($vsql)."',vwhere='".addslashes($vwhere)."',tabela='".$_p."colaboradores',id_reg='".$colaborador->id."'");

		$jsc->jAlert("Colaborador excluído com sucesso!","sucesso","document.location.href='pg_colaboradores.php'");
		die();
	}
?>
<section class="filtros" style="padding-bottom:0; margin-bottom:-.5rem;">	
	<div class="filtros-paciente">
		<?php
		$ftColaborador='img/ilustra-colaborador.jpeg';
		$ft='arqs/colaboradores/'.$colaborador->id.".".$colaborador->foto;
		if(file_exists($ft)) {
			$ftColaborador=$ft;
		}
		?>
		<img src="<?php echo $ftColaborador;?>" alt="<?php echo utf8_encode($colaborador->nome);?>" width="62" height="62" class="filtros-paciente__img" />
		<div class="filtros-paciente__inner1">
			<h1><?php echo utf8_encode($colaborador->nome);?></h1>
			<?php if($colaborador->data_nascimento != "0000-00-00"){?><p><?php echo idade($colaborador->data_nascimento);?> anos</p><?php }?>
			<p><span style="color:var(--cinza3);">#<?php echo $colaborador->id;?></span>
		</div>		
	</div>
	<?php /*<div class="filtros-acoes">
		<a href="pg_contatos_pacientes.php"><i class="iconify" data-icon="bx-bx-search"></i></a>
		<a href="?deletaColaborador=<?php echo $colaborador->id."&".$url;?>" class="sec js-deletar" ><i class="iconify" data-icon="bx-bx-trash"></i></a>
	</div>*/?>
</section>

<ul class="abas">
	<li><a href="pg_colaboradores_dadospessoais.php?<?php echo "id_colaborador=$colaborador->id";?>" class="main-nav__resumo-do-paciente<?php echo basename($_SERVER['PHP_SELF'])=="pg_colaboradores_dadospessoais.php"?" active":"";?>">Dados Pessoais</a></li>
	<li><a href="pg_colaboradores_dadoscontratacao.php?<?php echo "id_colaborador=$colaborador->id";?>" class="main-nav__resumo-do-paciente<?php echo basename($_SERVER['PHP_SELF'])=="pg_colaboradores_dadoscontratacao.php"?" active":"";?>">Dados da Contratação</a></li>
	<li><a href="pg_colaboradores_cargahoraria.php?<?php echo "id_colaborador=$colaborador->id";?>" class="main-nav__resumo-do-paciente<?php echo basename($_SERVER['PHP_SELF'])=="pg_colaboradores_cargahoraria.php"?" active":"";?>">Carga Horária</a></li>
</ul>
