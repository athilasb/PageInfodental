<?php
	if(isset($_POST['ajax'])) {
		require_once("lib/conf.php");
		require_once("usuarios/checa.php");
		
		$rtn = array();

		$colaborador='';
		if(isset($_POST['id_colaborador']) and is_numeric($_POST['id_colaborador'])) {
			$sql->consult($_p."colaboradores","id,nome","where id=".$_POST['id_colaborador']." and lixo=0");
			if($sql->rows) {
				$colaborador=mysqli_fetch_object($sql->mysqry);
			}
		}


		if(empty($colaborador)) {
			$rtn=array('success'=>false,'error'=>'Colaborador não encontrado!');
		} else if($_POST['ajax']=="comissionamentosPersistir") {


			$comissionamento='';
			if(isset($_POST['id_comissionamento']) and is_numeric($_POST['id_comissionamento'])) {
				$sql->consult($_p."colaboradores_comissionamento","*","where id=".$_POST['id_comissionamento']." and id_colaborador=$colaborador->id and lixo=0");
				if($sql->rows) {
					$comissionamento=mysqli_fetch_object($sql->mysqry);
				}
			}
				

			$vSQL='';
			if($_POST['tipo']=="valor") {

				$id_plano=(isset($_POST['id_plano']) and is_numeric($_POST['id_plano']))?$_POST['id_plano']:0;
				$id_procedimento=(isset($_POST['id_procedimento']) and is_numeric($_POST['id_procedimento']))?$_POST['id_procedimento']:0;
				$valor_tabela=(isset($_POST['valor_tabela']) and !empty($_POST['valor_tabela']))?valor($_POST['valor_tabela']):0;
				$comissao=(isset($_POST['comissao']) and !empty($_POST['comissao']))?($_POST['comissao']):0;
				$obs=(isset($_POST['obs']) and !empty($_POST['obs']))?addslashes(utf8_decode($_POST['obs'])):'';


				$vSQL="tipo='valor',
						id_colaborador=$colaborador->id,
						id_plano=$id_plano,
						id_procedimento=$id_procedimento,
						valor_tabela='$valor_tabela',
						comissao='$comissao',
						obs='$obs'";

			} else if($_POST['tipo']=="percentual") {


				$id_plano=(isset($_POST['id_plano']) and is_numeric($_POST['id_plano']))?$_POST['id_plano']:0;
				$comissao=(isset($_POST['comissao']) and !empty($_POST['comissao']))?valor($_POST['comissao']):0;
				$obs=(isset($_POST['obs']) and !empty($_POST['obs']))?addslashes(utf8_decode($_POST['obs'])):'';

				$vSQL="tipo='percentual',
						id_colaborador=$colaborador->id,
						id_plano=$id_plano,
						comissao='$comissao',
						obs='$obs'";

						

			} 

			if(!empty($vSQL)) {

				if(is_object($comissionamento)) {

					$vWHERE="where id=$comissionamento->id";
					$sql->update($_p."colaboradores_comissionamento",$vSQL,$vWHERE);
					$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='update',vsql='".addslashes($vSQL)."',vwhere='".addslashes($vWHERE)."',tabela='".$_p."colaboradores_comissionamento',id_reg='".$comissionamento->id."'");
				} else {

					$sql->add($_p."colaboradores_comissionamento",$vSQL.",data=now()");
					$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='insert',vsql='".addslashes($vSQL)."',vwhere='',tabela='".$_p."colaboradores_comissionamento',id_reg='".$sql->ulid."'");
				}

				$rtn=array('success'=>true);

			} else {
				$rtn=array('success'=>false,'error'=>'Tipo não especificado!');
			}

		} else if($_POST['ajax']=="comissionamentosListar") {

			$_planos=array();
			$sql->consult($_p."parametros_planos","*","where lixo=0 order by titulo asc");
			while($x=mysqli_fetch_object($sql->mysqry)) $_planos[$x->id]=$x;

			$_procedimentos=array();
			$sql->consult($_p."parametros_procedimentos","id,titulo","where lixo=0 order by titulo asc");
			while($x=mysqli_fetch_object($sql->mysqry)) $_procedimentos[$x->id]=$x;

			$comissionamento = array('valor'=>array(),
										'percentual'=>array());
			$sql->consult($_p."colaboradores_comissionamento","*","where id_colaborador=$colaborador->id and lixo=0 order by data");
			if($sql->rows) {
				while($x=mysqli_fetch_object($sql->mysqry)) {
					if($x->tipo=="valor") {
						$comissionamento[$x->tipo][]=array('id'=>$x->id,
															'id_plano'=>$x->id_plano,
															'plano'=>isset($_planos[$x->id_plano])?utf8_encode($_planos[$x->id_plano]->titulo):'-',
															'id_procedimento'=>$x->id_procedimento,
															'procedimento'=>isset($_procedimentos[$x->id_procedimento])?utf8_encode($_procedimentos[$x->id_procedimento]->titulo):'-',
															'valor_tabela'=>$x->valor_tabela,
															'comissao'=>$x->comissao,
															'obs'=>utf8_encode($x->obs));
					} else if($x->tipo=="percentual") {
						$comissionamento[$x->tipo][]=array('id'=>$x->id,
															'id_plano'=>$x->id_plano,
															'plano'=>isset($_planos[$x->id_plano])?utf8_encode($_planos[$x->id_plano]->titulo):'-',
															'comissao'=>$x->comissao,
															'obs'=>utf8_encode($x->obs));

					}
				}
			}
			$rtn=array('success'=>true,'comissionamento'=>$comissionamento);
		
		} else if($_POST['ajax']=="comissionamentosRemover") {

			$comissionamento='';
			if(isset($_POST['id_comissionamento']) and is_numeric($_POST['id_comissionamento'])) {
				$sql->consult($_p."colaboradores_comissionamento","*","where id=".$_POST['id_comissionamento']." and id_colaborador=$colaborador->id and lixo=0");
				if($sql->rows) {
					$comissionamento=mysqli_fetch_object($sql->mysqry);
				}
			}

			if(is_object($comissionamento)) {

				$vWHERE="where id=$comissionamento->id";
				$vSQL="lixo=1";
				$sql->update($_p."colaboradores_comissionamento",$vSQL,$vWHERE);
				$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='delete',vsql='".addslashes($vSQL)."',vwhere='".addslashes($vWHERE)."',tabela='".$_p."colaboradores_comissionamento',id_reg='".$comissionamento->id."'");

				$rtn=array('success'=>true);

			} else {
				$rtn=array('success'=>false,'error'=>'Comissionamento não encontrado!');
			}


		} else if($_POST['ajax']=="horariosPersistir") {
			$cadeira='';
			if(isset($_POST['id_cadeira']) and is_numeric($_POST['id_cadeira'])) {
				$sql->consult($_p."parametros_cadeiras","*","where id='".$_POST['id_cadeira']."'");
				if($sql->rows) {
					$cadeira=mysqli_fetch_object($sql->mysqry);
				}
			}

			$horario='';
			if(isset($_POST['id']) and is_numeric($_POST['id']) and $_POST['id']>0) {
				$sql->consult($_p."profissionais_horarios","*", "where id='".$_POST['id']."' and lixo=0");
				if($sql->rows) $horario=mysqli_fetch_object($sql->mysqry);
			}

			$inicio=(isset($_POST['inicio']) and !empty($_POST['inicio']))?addslashes($_POST['inicio']):'';
			$fim=(isset($_POST['fim']) and !empty($_POST['fim']))?addslashes($_POST['fim']):'';
			$dia=(isset($_POST['dia']) and is_numeric($_POST['dia']))?addslashes($_POST['dia']):'';

			if(empty($colaborador)) $rtn=array('success'=>false,'error'=>'Colaborador não definido!');
			else if(empty($inicio)) $rtn=array('success'=>false,'error'=>'Ínicio não definido!');
			else if(empty($fim)) $rtn=array('success'=>false,'error'=>'Fim não definido!');
			else if(empty($dia) and $dia!=0) $rtn=array('success'=>false,'error'=>'Dia da semana não definido!');
			else {


				$horarios = new Horarios(array('prefixo'=>$_p));

				$attr=array('id_cadeira'=>is_object($cadeira)?$cadeira->id:0,
							'id_colaborador'=>$colaborador->id,
							'id_horario'=>is_object($horario)?$horario->id:0,
							'diaSemana'=>$dia,
							'inputHoraInicio'=>$inicio,
							'inputHoraFim'=>$fim);

				if($horarios->cadeiraHorariosIntercecao($attr)) {

					$vsql="id_profissional=$colaborador->id,
							inicio='".$inicio."',
							dia='".$dia."',
							fim='".$fim."'";

					if(is_object($cadeira)) $vsql.=",id_cadeira=$cadeira->id";

					if(isset($_POST['id']) and is_numeric($_POST['id']) and $_POST['id']>0) {
						$sql->consult($_p."profissionais_horarios","*", "where id='".$_POST['id']."' and id_profissional=$colaborador->id and lixo=0");
						if($sql->rows) {
							$x=mysqli_fetch_object($sql->mysqry);
							$vsql.=",id_alteracao=$usr->id,alteracao_data=now()";
							$sql->update($_p."profissionais_horarios",$vsql,"where id=$x->id");
							$rtn=array('success'=>true);
						} else $rtn=array('success'=>false,'error'=>'Horário não encontrado');
					} else {
						$vsql.=",id_usuario=$usr->id,data=now()";
						$sql->add($_p."profissionais_horarios",$vsql);
						$rtn=array('success'=>true);
					}
				} else {
					$rtn=array('success'=>false,'error'=>$horarios->erro);
				}
			}
		} else if($_POST['ajax']=="horariosListar") {

			$_cadeiras=array();
			$sql->consult($_p."parametros_cadeiras","*","where lixo=0");
			while($x=mysqli_fetch_object($sql->mysqry)) $_cadeiras[$x->id]=$x;

			if(empty($colaborador)) $rtn=array('success'=>false,'error'=>'Colaborador não encontrado!');
			else {
				$horarios=array();
				$sql->consult($_p."profissionais_horarios","*,date_format(inicio,'%H:%i') as inicio,
																date_format(fim,'%H:%i') as fim",
																"where id_profissional=$colaborador->id and lixo=0");
				if($sql->rows) {
					while($x=mysqli_fetch_object($sql->mysqry)) {
						//if(isset($_cadeiras[$x->id_cadeira])) {
							$cadeira=isset($_cadeiras[$x->id_cadeira])?$_cadeiras[$x->id_cadeira]:(object)array('id'=>0,'titulo'=>'Sem Cadeira');
							$horarios[$x->id_cadeira][$x->dia][]=array('id'=>$x->id,
																'id_cadeira'=>$x->id_cadeira,
																'cadeira'=>utf8_encode($cadeira->titulo),
																'dia'=>$x->dia,
																'inicio'=>$x->inicio,
																'fim'=>$x->fim
															);
						//}
					}

				}

				$horariosObj = new Horarios(array('prefixo'=>$_p));
				$carga='';
				if($horariosObj->colaboradorCargaHoraria($colaborador->id)) {
					$carga=sec_convert($horariosObj->carga,'HF');
				}
				$rtn=array('success'=>true,'horarios'=>$horarios,'carga'=>$carga);
			}
		} else if($_POST['ajax']=="horariosEditar") {
			$horario='';
			if(isset($_POST['id_horario']) and is_numeric($_POST['id_horario'])) {
				$sql->consult($_p."profissionais_horarios","*,date_format(inicio,'%H:%i') as inicio,
																date_format(fim,'%H:%i') as fim","where id='".$_POST['id_horario']."'");
				if($sql->rows) {
					$horario=mysqli_fetch_object($sql->mysqry);
				}
			}

			if(is_object($horario)) {

				$rtn=array('success'=>true,
							'id'=>$horario->id,
							'id_cadeira'=>$horario->id_cadeira,
							'inicio'=>$horario->inicio,
							'fim'=>$horario->fim,
							'dia'=>$horario->dia);
			} else {
				$rtn=array('success'=>false,'error'=>'Horário não encontrado!');
			}
		} else if($_POST['ajax']=="horariosRemover") {
			$horario='';
			if(isset($_POST['id_horario']) and is_numeric($_POST['id_horario'])) {
				$sql->consult($_p."profissionais_horarios","*,date_format(inicio,'%H:%i') as inicio,
																date_format(fim,'%H:%i') as fim","where id='".$_POST['id_horario']."'");
				if($sql->rows) {
					$horario=mysqli_fetch_object($sql->mysqry);
				}
			}

			if(is_object($horario)) {
				$sql->update($_p."profissionais_horarios","lixo=$usr->id,lixo_data=now()","where id=$horario->id");

				$rtn=array('success'=>true);
			} else {
				$rtn=array('success'=>false,'error'=>'Horário não encontrado!');
			}
		} else if($_POST['ajax']=="foto") {

			
			$sql->update($_p."colaboradores","foto='".addslashes($_POST['foto'])."'","where id=$colaborador->id");
			$rtn=array('success'=>true);
			

		}

		header("Content-type: application/json");
		echo json_encode($rtn);
		die();
	

		header("Content-Type: application/json");
		echo json_encode($rtn);
		die();
	}
	include "includes/header.php";
	include "includes/nav.php";

	$_table=$_p."colaboradores";

	$_width=400;
	$_height=400;
	$_dirFoto=$_cloudinaryPath."arqs/colaboradores/";

	$_cargos=array();
	$sql->consult($_p."colaboradores_cargos","*","where lixo=0 order by titulo asc");
	while($x=mysqli_fetch_object($sql->mysqry)) $_cargos[$x->id]=$x;

	$_planos=array();
	$sql->consult($_p."parametros_planos","*","where lixo=0 order by titulo asc");
	while($x=mysqli_fetch_object($sql->mysqry)) $_planos[$x->id]=$x;

	$_procedimentos=array();
	$sql->consult($_p."parametros_procedimentos","id,titulo","where lixo=0 order by titulo asc");
	while($x=mysqli_fetch_object($sql->mysqry)) $_procedimentos[$x->id]=$x;

	$_cadeiras=array();
	$sql->consult($_p."parametros_cadeiras","id,titulo","where lixo=0 order by ordem asc");
	while($x=mysqli_fetch_object($sql->mysqry)) $_cadeiras[$x->id]=$x;
	
?>

	<header class="header">
		<div class="header__content content">

			<div class="header__inner1">
				<section class="header-title">
					<h1>Configurações</h1>
				</section>
				<?php
				require_once("includes/menus/menuConfiguracoes.php");
				?>
			</div>
		</div>
	</header>

	<main class="main">
		<div class="main__content content">
			<?php
			# Formulario de Adição/Edição
			if(isset($_GET['form'])) {

				$campos=explode(",","nome,sexo,rg,rg_orgaoemissor,rg_estado,cpf,data_nascimento,estado_civil,telefone1,telefone2,nome_pai,nome_mae,email,instagram,linkedin,facebook,cep,endereco,numero,complemento,bairro,estado,cidade,id_cidade,escolaridade,cro,uf_cro,tipo_cro,calendario_cor,calendario_iniciais,id_cargo,regime_contrato,salario,contratacao_obs,carga_horaria,comissionamento_tipo,permitir_acesso,lng,lat,check_agendamento,contratacaoAtiva,whatsapp_notificacoes");

				foreach($campos as $v) $values[$v]='';
				$values['calendario_cor']="#c18c6a";
				$values['sexo']="M";
				$values['comissionamento_tipo']="nenhum";

				$cnt='';
				// busca edicao
				if(isset($_GET['edita']) and is_numeric($_GET['edita'])) {
					$sql->consult($_table,"*","where id='".$_GET['edita']."' and lixo=0");
					if($sql->rows) {
						$cnt=mysqli_fetch_object($sql->mysqry);
						$values=$adm->values($campos,$cnt);
					} else {
						$jsc->jAlert("Colaborador não encontrado!","erro","document.location.href='$_page?$url'");
						die();
					}
				}

				if(isset($_GET['deleta'])) {
					if(is_object($cnt)) {
						$vSQL="lixo=1";
						$vWHERE="where id=$cnt->id";
						$sql->update($_table,$vSQL,$vWHERE);
						$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='delete',vsql='".addslashes($vSQL)."',vwhere='".addslashes($vWHERE)."',tabela='".$_table."',id_reg='".$cnt->id."'");

						$jsc->go("$_page?$url");
						die();
 					} else {
 						$jsc->jAlert("Colaborador não encontrado","erro","document.location.href='$_page?$url';");
 					}
				}

				// persistencia
				if(isset($_POST['acao']) and $_POST['acao']=="wlib") {
					if(empty($cnt)) $_POST['contratacaoAtiva']=1;

					if(isset($_POST['calendario_iniciais'])) $_POST['calendario_iniciais']=strtoupperWLIB($_POST['calendario_iniciais']);

					// monta sql de insert/update
					$vSQL=$adm->vSQL($campos,$_POST);

					if(isset($_POST['senha']) and !empty($_POST['senha'])) $vSQL.="senha='".sha1($_POST['senha'])."',";
			

					// popula $values para persistir nos cmapos
					$values=$adm->values;
					if(isset($_POST['foto']) and !empty($_POST['foto'])) $vSQL.="foto='".$_POST['foto']."',";

					$processa=true;

					// verifica se cpf ja esta cadastrado
					if(empty($cnt) or (is_object($cnt) and $cnt->cpf!=cpf($_POST['cpf']))) {
						$sql->consult($_table,"*","where cpf='".addslashes(cpf($_POST['cpf']))."' and lixo=0");
						if($sql->rows) {
							$processa=false;
							$jsc->jAlert("Já existe colaborador cadastrado com este CPF","erro",""); 
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
							$jsc->jAlert("Informações salvas com sucesso!","sucesso","document.location.href='$_page?form=1&edita=".$id_reg."&".$url."'");
							die();
						}
					}
				}
			?>
			<section class="filter">
				
				<div class="filter-group">
					<div class="filter-title">	
						<h1></h1>
					</div>
				</div>

				<?php /*<div class="filter-group">
					<div class="filter-form form">
						<dl>
							<dd><a href="<?php echo $_page."?".$url;?>" class="button"><i class="iconify" data-icon="fluent:arrow-left-24-regular"></i></a></dd>
						</dl>
						<?php
						if(is_object($cnt)) {
						?>
						<dl>
							<dd><a href="<?php $_page."?form=1&edita=$cnt->id&deleta=1";?>" class="button"><i class="iconify" data-icon="fluent:delete-24-regular"></i></a></dd>
						</dl>
						<dl>
							<dd><a href="" class="button"><i class="iconify" data-icon="fluent:print-24-regular"></i></a></dd>
						</dl>
						<?php
						}
						?>
						<dl>
							<dd><a href="javascript://" class="button button_main js-submit"><i class="iconify" data-icon="fluent:checkmark-12-filled"></i><span>Salvar</span></a></dd>
						</dl>
					</div>
				</div>*/?>
			</section>
			<?php
			} 
			# Listagem
			else {
			?>
			<section class="filter">
				<div class="filter-group">
					<div class="filter-title">
						<h1>Configure a clínica</h1>
					</div>
				</div>
			</section>
			<?php
			} 
			?>

			<section class="grid">

				<div class="box box-col">

					<?php
					require_once("includes/submenus/subConfiguracoesClinica.php");
					?>
					<div class="box-col__inner1">
				<?php

				# Formulario de Adição/Edição
				if(isset($_GET['form'])) {
					if(is_object($cnt)) {
				?>	

						<section class="header-profile">
							<?php
							$ft="img/logo-user.png";
							if(is_object($cnt) and !empty($cnt->foto)) {
								$ft=$_cloudinaryURL.',w_100/'.$cnt->foto;
							}
							?>
							<img src="<?php echo $ft;?>" alt="" width="60" height="60" class="header-profile__foto" />
							
							<div class="header-profile__inner1">
								<h1><?php echo utf8_encode($cnt->nome);?></h1>
								<div>
									<p>
									<?php
									if($cnt->permitir_acesso==1) {
									?>
									<strong style="color:var(--verde);"><i class="iconify" data-icon="fluent:checkmark-circle-12-regular"></i> Acesso Ativo</strong>
									<?php
									} else {
									?>
									<strong style="color:var(--vermelho);"><i class="iconify" data-icon="fluent:checkmark-circle-12-regular"></i> Acesso Desativado</strong>
									<?php	
									}
									?>
									</p>
								</div>
							</div>
						</section>
				<?php
					}
				?>
						<script type="text/javascript">
							$(function(){
								$('input.money').maskMoney({symbol:'', allowZero:false, showSymbol:true, thousands:'.', decimal:',', symbolStay: true});
								$('input[name=telefone1]').mobilePhoneNumber({allowPhoneWithoutPrefix: '+55'}).bind('country.mobilePhoneNumber', function(echo, country) {
									let countryOut = country || '  ';
									$(this).parent().parent().find('.js-country').html(countryOut);
								}).trigger('keyup');

								$('input[name=telefone2]').mobilePhoneNumber({allowPhoneWithoutPrefix: '+55'}).bind('country.mobilePhoneNumber', function(echo, country) {
									let countryOut = country || '  ';
									$(this).parent().parent().find('.js-country').html(countryOut);
								}).trigger('keyup');
							})
						</script>
						<form method="post" class="form formulario-validacao">
							<button style="display:none;"></button>
							<input type="hidden" name="acao" value="wlib" />
							<section class="filter">
								<div class="filter-group">

									<script>
										$(function() {
											$('.tab a').click(function() {
												let tabName = $(this).attr('data-tab');
												$(".tab a").removeClass("active");
												$(this).addClass("active");
												$(".js-tabs").hide();
												$(".js-" + tabName).show();

												/*if(tabName=='dadosdacontratacao') {
													$('select[name=id_cargo]').addClass('obg');
													$('select[name=regime_contrato]').addClass('obg');
													$('select[name=carga_horaria]').addClass('obg');
												} else {
													$('select[name=id_cargo]').removeClass('obg');
													$('select[name=regime_contrato]').removeClass('obg');
													$('select[name=carga_horaria]').removeClass('obg');
												}*/
											});
										});
									</script>
									<?php
									if(is_object($cnt)) {
									?>
									<section class="tab">
										<a href="javascript:;" data-tab="dadospessoais" class="active">Dados Pessoais</a>
										<a href="javascript:;" data-tab="dadosdacontratacao">Dados da Contratação</a>					
										<?php /*<a href="javascript:;" data-tab="habilitaragendamento">Habilitar Agendamento</a>*/?>					
										<a href="javascript:;" data-tab="acessoaosistema">Acesso ao Sistema</a>
									</section>
									<?php
									}
									?>
								</div>
								<div class="filter-group">
									<div class="filter-form form">
										<dl>
											<dd><a href="<?php echo $_page."?".$url;?>" class="button"><i class="iconify" data-icon="fluent:arrow-left-24-regular"></i></a></dd>
										</dl>
										<?php
										if(is_object($cnt)) {
										?>
										<dl>
											<dd><a href="<?php echo "$_page?form=1&edita=$cnt->id&deleta=1&$url";?>" class="button js-confirmarDeletar" data-msg="Tem certeza que deseja remover este colaborador?"><i class="iconify" data-icon="fluent:delete-24-regular"></i></a></dd>
										</dl>
										<?php /*<dl>
											<dd><a href="javascript:;" class="button"><i class="iconify" data-icon="fluent:print-24-regular"></i></a></dd>
										</dl>*/?>
										<?php
										}
										?>
										<dl>
											<dd><a href="javascript:;" class="button button_main js-submit"><i class="iconify" data-icon="fluent:checkmark-12-filled"></i><span>Salvar</span></a></dd>
										</dl>
									</div>
								</div>
							</section>

							<div class="js-tabs js-dadospessoais">

								<fieldset>
									<legend>Dados Pessoais</legend>

									<div class="grid grid_3">
										<div style="grid-column:span 2">

											<div class="colunas">
												<dl>
													<dt>Nome</dt>
													<dd><input type="text" name="nome" value="<?php echo $values['nome'];?>" class="obg" /></dd>
												</dl>
												<dl>
													<dt>Sexo</dt>
													<dd>
														<label><input type="radio" name="sexo" value="M"<?php echo $values['sexo']=="M"?" checked":"";?>>masculino</label>
														<label><input type="radio" name="sexo" value="F"<?php echo $values['sexo']=="F"?" checked":"";?>>feminino</label>
													</dd>
												</dl>
											</div>
											<div class="colunas3">
												<dl>
													<dt>Doc. Identidade</dt>
													<dd>
														<input type="text" name="rg" value="<?php echo $values['rg'];?>"  class="" />
													</dd>
												</dl>
												<dl>
													<dt>Org. Emissor</dt>
													<dd>
														<input type="text" name="rg_orgaoemissor" value="<?php echo $values['rg_orgaoemissor'];?>"  class="" />
													</dd>
												</dl>
												<dl>
													<dt>UF</dt>
													<dd>
														<select name="rg_orgaoemissor">
															<option value="">-</option>
															<?php
															foreach($_optUF as $uf=>$titulo) {
																echo '<option value="'.$uf.'"'.($values['rg_orgaoemissor']==$uf?' selected':'').'>'.$titulo.'</option>';
															}
															?>
														</select>
													</dd>
												</dl>
											</div>
											<div class="colunas3">
												<dl>
													<dt>CPF</dt>
													<dd>
														<input type="text" name="cpf" value="<?php echo $values['cpf'];?>" class="cpf obg" />
													</dd>
												</dl>
												<dl>
													<dt>Data de Nascimento</dt>
													<dd>
														<input type="text" name="data_nascimento" value="<?php echo $values['data_nascimento'];?>" class="data" />
													</dd>
												</dl>
												<dl>
													<dt>Estado Civil</dt>
													<dd>
														<select name="estado_civil" class="chosen">
															<option value="">-</option>
															<?php
															foreach($_pacienteEstadoCivil as $k=>$v) {
																echo '<option value="'.$k.'"'.(($values['estado_civil']==$k)?' selected':'').'>'.$v.'</option>';
															}
															?>
														</select>
													</dd>
												</dl>
											</div>
											<div class="colunas3">
												<dl>
													<dt>CRO</dt>
													<dd>
														<input type="text" name="cro" value="<?php echo $values['cro']; ?>" class="" />
													</dd>
												</dl>
												<dl>
													<dt>UF do CRO</dt>
													<dd>
														<select name="uf_cro" class="chosen">
															<option value="">-</option>
															<?php
															foreach($_optUF as $uf=>$titulo) {
																echo '<option value="'.$uf.'"'.($values['uf_cro']==$uf?' selected':'').'>'.$titulo.'</option>';
															}
															?>
														</select></dd>
												</dl>
												<dl>
													<dt>Tipo do CRO</dt>
													<dd>
														<select name="tipo_cro" class="chosen">
															<option value="">-</option>
															<?php
															foreach($_tipoCRO as $k=>$v) {
																echo '<option value="'.$k.'"'.(($values['tipo_cro']==$k)?' selected':'').'>'.$v.'</option>';
															}
															?>
														</select>
													</dd>
												</dl>
											</div>
											<div class="colunas">
												<dl>
													<dt>Nome da Mãe</dt>
													<dd>
														<input type="text" name="nome_pai" value="<?php echo $values['nome_pai'];?>" class="" />
													</dd>
												</dl>
												<dl>
													<dt>Nome do Pai</dt>
													<dd>
														<input type="text" name="nome_mae" value="<?php echo $values['nome_mae'];?>" class="" />
													</dd>
												</dl>
											</div>

										</div>
										<div>
											<?php
												$thumb="img/logo-user.png";
												if(is_object($cnt)) {
													if(!empty($cnt->foto)) {
														$image=$_cloudinaryURL.'c_thumb,w_600/'.$cnt->foto;
														$thumb=$_cloudinaryURL.'c_thumb,w_600/'.$cnt->foto;
													} 
												}
											?>
											<div class="form-image">
												<img src="<?php echo $thumb;?>" alt="" width="484" height="68" />
											</div>
											<dl>
												<dt>Foto</dt>
												<dd>
													<a href="javascript:;" id="foto" class="button button_main">Procurar</a>
													<input type="hidden" name="foto" />
												</dd>
											</dl>

										</div>
										<input type="hidden" name="foto" />

										<script src="https://widget.cloudinary.com/v2.0/global/all.js" type="text/javascript"></script> 
										<script type="text/javascript">
											var cloudinaryURL = '<?php echo $_cloudinaryURL;?>';

											var id_colaborador = <?php echo (int)is_object($cnt)?$cnt->id:0;?>;
											var foto = cloudinary.createUploadWidget({
												cloudName: '<?php echo $_cloudinaryCloudName;?>',
												language: 'pt',
												text: <?php echo json_encode($_cloudinaryText);?>,
												multiple: false,
												sources: ["local"],
												folder: '<?php echo $_dirFoto;?>',
												uploadPreset: '<?php echo $_cloudinaryUploadPresent;?>'}, 
												(error, result) => {
													if (!error && result) {
														if(result.event === "success") {
															$('input[name=foto]').val(result.info.path);
															

															if(id_colaborador>0) {
																data = `ajax=foto&foto=${result.info.path}&id_colaborador=${id_colaborador}`;
																$.ajax({
																	type:"POST",
																	data:data,
																	success:function(rtn) {
																		$(".form-image img").attr('src',`${cloudinaryURL}c_thumb,w_600/${result.info.path}`)
																	}
																});
															}
														}
													}
												}
											);
											$(function(){

												document.getElementById("foto").addEventListener("click", function(){
												    foto.open();
												}, false);

												
											})
										</script>

										<?php /*<script src="https://widget.cloudinary.com/v2.0/global/all.js" type="text/javascript"></script>
										<script>
											$(function(){
												var foto = cloudinary.createUploadWidget({
													cloudName: '<?php echo $_cloudinaryCloudName;?>',
													language: 'pt',
													text: <?php echo json_encode($_cloudinaryText);?>,
													multiple: false,
													sources: ["local"],
													folder: '<?php echo $_dirFoto;?>',
													uploadPreset: '<?php echo $_cloudinaryUploadPresent;?>'}, 
													(error, result) => {
														if (!error && result) {
															if(result.event === "success") {
																 $('input[name=foto]').val(result.info.path);
															}
														}
													}
												)
												document.getElementById("foto").addEventListener("click", function(){
												    foto.open();
												}, false);
											});
										</script>	*/?>
									</div>
								</fieldset>

								<fieldset>
									<legend>Dados de Contato</legend>

									<div class="colunas3">
										<dl>
											<dt>WhatsApp</dt>
											<dd class="form-comp">
												<span class="js-country">BR</span><input type="text" name="telefone1" class="obg " attern="\d*" x-autocompletetype="tel" value="<?php echo $values['telefone1'];?>" />
											</dd>
										</dl>
										<dl>
											<dt>Telefone</dt>
											<dd class="form-comp">
												<span class="js-country">BR</span><input type="text" name="telefone2" class="" attern="\d*" x-autocompletetype="tel" value="<?php echo $values['telefone2'];?>" />
											</dd>
										</dl>
										<dl>
											<dt>Instagram</dt>
											<dd class="form-comp"><span>@</span><input type="text" name="instagram" value="<?php echo $values['instagram'];?>" /></dd>
										</dl>
									</div>
									<script>
										var marker = '';
										var map = '';
										var position = '';
										var positionEndereco = '';
										var el = document.getElementById("geolocation");
										var location_timeout = '';
										var geocoder = '';
										var enderecoObj = {};
										var enderecos = [];	
										var lat = `-16.688304`;
										var lng = `-49.267055`;

										function initMap() {
											let options = {componentRestrictions: {country: "bra"}}
											var input = document.getElementById('search');

											var autocomplete = new google.maps.places.Autocomplete(input,options);
											geocoder = new google.maps.Geocoder();

											autocomplete.addListener('place_changed', function() {

												var result = autocomplete.getPlace();
												lat = result.geometry.location.lat();
												lng = result.geometry.location.lng();
												$('input[name=lat]').val(lat);
												$('input[name=lng]').val(lng);

												let logradouro = '';
												let numero = '';
												let bairro = '';
												let cep = '';
												let cidade = '';
												let estado = '';
												let pais = '';
												let descricao = '';

												enderecoObj = { logradouro, numero, bairro, cep, cidade, estado, pais, descricao, lat, lng }

												$('input[name=lat]').val(enderecoObj.lat);
												$('input[name=lng]').val(enderecoObj.lng);
											});

										}	
									</script>
									<script async src="https://maps.googleapis.com/maps/api/js?key=<?php echo $_googleMapsKey;?>&libraries=places&callback=initMap">
									</script>
									<div>
										<dl class="dl2">
											<dt>Endereço</dt>
											<dd><input type="text" name="endereco" value="<?php echo $values['endereco'];?>" id="search" /></dd>
										</dl>
										<dl>
											<dt>Complemento</dt>
											<dd><input type="text" name="complemento" value="<?php echo $values['complemento'];?>" /></dd>
										</dl>
									</div>
									<input type="hidden" name="lng" id="lng" style="display:none;" />
									<input type="hidden" name="lat" id="lat" style="display:none;" />
								</fieldset>

								<?php /*<fieldset>
									<legend>Certificação Digital</legend>

									<div class="colunas3">
										<dl class="dl2">
											<dt>Certificado A1</dt>
											<dd class="form-comp form-comp_pos"><input type="text" name="" placeholder="" /><a href=""><i class="iconify" data-icon="fluent:add-circle-24-regular"></i></a></dd>
										</dl>
										<dl>
											<dt>Senha</dt>
											<dd><input type="text" name="" /></dd>
										</dl>
									</div>
									<div class="colunas3">
										<dl>
											<dt>Status do Certificado</dt>
											<dd><label style="color:green;"><i class="iconify" data-icon="fluent:checkmark-circle-12-regular"></i> <strong>Ativo</strong></label></dd>
										</dl>
										<dl>
											<dt>Data de Vencimento</dt>
											<dd><label>10/10/2022</label></dd>
										</dl>
									</div>
								</fieldset>*/?>
							</div><!-- .js-dadospessoais -->

							<?php
							if(is_object($cnt)) {
							?>
							<script type="text/javascript">
								
								const contratacaoAtiva = () => { 
									if($('.js-contratacaoAtiva').prop('checked')===true) {
										$('.js-box-contratacaoAtiva').fadeIn().find('');
									} else {
										$('.js-box-contratacaoAtiva').hide();
									}
								}
								$(function(){
									contratacaoAtiva();

									$('.js-contratacaoAtiva').click(contratacaoAtiva);
								});
							</script>
							<div class="js-tabs js-dadosdacontratacao" style="display:none">
								<fieldset>
									<legend>Contratação</legend>
									<div class="colunas3">
										<dl>
											<dd>
												<label><input type="checkbox" name="contratacaoAtiva" value="1" class="input-switch js-contratacaoAtiva"<?php echo $values['contratacaoAtiva']==1?" checked":"";?> /> Contratação Ativa</label>
											</dd>
										</dl>
									</div>

									<div class="colunas3 js-box-contratacaoAtiva">
										<dl>
											<dt>Cargo Atual</dt>
											<dd>
												<select name="id_cargo" class="">
													<option value="">-</option>
													<?php
													foreach($_cargos as $v) {
														echo '<option value="'.$v->id.'"'.(($values['id_cargo']==$v->id)?' selected':'').'>'.utf8_encode($v->titulo).'</option>';
													}
													?>
												</select>
											</dd>
										</dl>
										<dl>
											<dt>Regime de Contratação</dt>
											<dd>
												<select name="regime_contrato" class="">
													<option value="">-</option>
													<?php
													foreach($_regimes as $k => $v) {
														echo '<option value="'.$k.'"'.(($values['regime_contrato']==$k)?' selected':'').'>'.$v.'</option>';
													}
													?>
												</select>
											</dd>
										</dl>
										<dl>
											<dt>Salário</dt>
											<dd><input type="text" name="salario" value="<?php echo $values['salario'];?>" class="money" /></dd>
										</dl>
									</div>
									<div class="colunas3 js-box-contratacaoAtiva">
										<dl>
											<dt>Carga Horária Semanal</dt>
											<dd>
												<?php /*<select name="carga_horaria" class="">
													<option value="">-</option>
													<?php
													foreach($_cargaHoraria as $k => $v) {
														echo '<option value="'.$k.'"'.(($values['carga_horaria']==$k)?' selected':'').'>'.$v.'</option>';
													}
													?>
												</select>*/?>
												<?php /*<input type="text" name="carga_horaria" value="<?php echo $values['carga_horaria'];?>" />*/?>
											</dd>
											<?php
											$horarios = new Horarios(array('prefixo'=>$_p));
											if($horarios->colaboradorCargaHoraria($cnt->id)) {
												$carga=$horarios->carga;
											}
											?>

											<dd><input type="text" value="<?php echo sec_convert($carga,'HF');?>" class="js-carga" disabled /></dd>
										</dl>
										<dl class="dl2">
											<dt>Observação Geral </dt>
											<dd><input type="text" name="contratacao_obs" value="<?php echo $values['contratacao_obs'];?>" /></dd>
										</dl>
									</div>

									
								</fieldset>

								<fieldset class="js-fieldset-horarios">
									<legend>Horário de Atendimento</legend>
									<input type="hidden" class="js-id" value="0" />

									<div class="colunas4">
										<dl>
											<dt>Dia da Semana</dt>
											<dd>
												<select  class="js-dia">
													<option value="">-</option>
													<?php
													for($i=0;$i<=6;$i++) {
														echo '<option value="'.$i.'">'.$_dias[$i].'</option>';	
													}
													?>
												</select>
											</dd>
										</dl>
										<dl>
											<dt>Início</dt>
											<dd class="form-comp"><span><i class="iconify" data-icon="fluent:clock-24-regular"></i></span><input type="text" name="inicio" class="js-inicio hora" /></dd>
										</dl>
										<dl>
											<dt>Fim</dt>
											<dd class="form-comp"><span><i class="iconify" data-icon="fluent:clock-24-regular"></i></span><input type="text" name="fim" class="js-fim hora" /></dd>
										</dl>
										<dl>
											<dt>Cadeira</dt>
											<dd>
												<select class="js-id_cadeira">
													<option value="">-</option>
													<?php
														foreach($_cadeiras as $x) {
													?>
													<option value="<?php echo $x->id;?>"><?php echo utf8_encode($x->titulo);?></option>
													<?php
														}
													?>
												</select>
												<a href="javascript:;" class="button button_main js-horarios-submit" data-loading="0"><i class="iconify" data-icon="fluent:add-circle-24-regular"></i></a>
												<a href="javascript:;" class="button js-horarios-remover" data-loading="0" style="display:none;"><i class="iconify" data-icon="fluent:delete-24-regular"></i></a>
											</dd>
										</dl>
									</div>
									<div class="list2">
										<table class="js-horario-table">
											<thead>
												<tr>
													<th style="width:13.5%">CADEIRA</th>
													<th style="width:12.5%">DOM</th>
													<th style="width:12.5%">SEG</th>
													<th style="width:12.5%">TER</th>
													<th style="width:12.5%">QUA</th>
													<th style="width:12.5%">QUI</th>
													<th style="width:12.5%">SEX</th>
													<th style="width:12.5%">SÁB</th>
												</tr>
											</thead>
											<tbody>
												<tr class="js-cadeira js-cadeira-0">
													<td><strong>Sem Cadeira</strong></td>
													<?php
														for($i=0;$i<=6;$i++) {
															echo '<td class="js-td js-0-'.$i.'"></td>';	
														}
													?>
												</tr>
												<?php
												foreach($_cadeiras as $x) {
												?>
												<tr class="js-cadeira js-cadeira-<?php echo $x->id;?>">
													<td><strong><?php echo utf8_encode($x->titulo);?></strong></td>
													<?php
														for($i=0;$i<=6;$i++) {
															echo '<td class="js-td js-'.$x->id.'-'.$i.'"></td>';	
														}
													?>
												</tr>
												<?php
													}
												?>
											</tbody>
										</table>
									</div>
								</fieldset>

								<fieldset>
									<legend>Agendamento</legend>

									<div class="colunas4">
										
									</div>
									<div class="colunas4">
										<dl class="dl">
											<dt></dt>
											<dd><label><input type="checkbox" class="input-switch js-check_agendamento" name="check_agendamento" value="1"<?php echo $values['check_agendamento']==1?" checked":"";?> /> Habilitar agendamento</label></dd>
										</dl>
										<?php /*<dl class="">
											<dt>Carga Horária</dt>
											<dd><input type="text" value="<?php echo sec_convert($carga,'HF');?>" class="js-carga" disabled />
										</dl>*/?>
										<dl class="js-box-habilitarAgendamento">
											<dt>Inicial</dt>
											<dd><input type="text" name="calendario_iniciais" value="<?php echo $values['calendario_iniciais'];?>" maxlength="2" style="text-transform: uppercase;" /></dd>
										</dl>
										<dl class="js-box-habilitarAgendamento">
											<dt>Cor</dt>
											<dd><input type="color" name="calendario_cor" value="<?php echo $values['calendario_cor'];?>" /></dd>
										</dl>
										<dl class="js-box-habilitarAgendamento">
											<dt>&nbsp;</dt>
											<dd>
												<label><input type="checkbox" name="whatsapp_notificacoes" class="input-switch" value="1"<?php echo $values['whatsapp_notificacoes']==1?" checked":"";?> /> Receber Notificações (Whatsapp)</label>
											</dd>
										</label>
									</div>
								</fieldset>


								<?php
								/*<fieldset>
									<legend><span>Comissionamento</span></legend>

									<script type="text/javascript">

										var comissionamentoPercentual = [];
										var comissionamentoValor = [];
										var id_colaborador = <?php echo $cnt->id;?>;

										const comissionamentoTipo = () => {
											//alert($('input[name=comissionamento_tipo]:checked').val());
											if($('input[name=comissionamento_tipo]:checked').val()=="nenhum") {
												$('.js-comissao').hide();
											} else if($('input[name=comissionamento_tipo]:checked').val()=="percentual") {
												$('.js-comissao').hide();
												$('.js-percentual').show();
											} else if($('input[name=comissionamento_tipo]:checked').val()=="valor") {
												$('.js-comissao').hide(); 
												$('.js-valorfixo').show();
											}
										}

										const comissionamentosAtualizar = () => {
											$.ajax({
												type:"POST",
												data:`ajax=comissionamentosListar&id_colaborador=${id_colaborador}`,
												success:function(rtn) {
													if(rtn.success) {
														if(rtn.comissionamento.percentual) comissionamentoPercentual=rtn.comissionamento.percentual;
														if(rtn.comissionamento.valor) comissionamentoValor=rtn.comissionamento.valor;
														comissionamentosListar();
													} else if(rtn.error) {

													} else {

													}
												},
												error:function(){

												}
											});
										}

										const comissionamentosListar = () => {

											$(`.js-tbody-percentual tr`).remove();
											$('select[name=percentual_id_plano]').find(`option`).prop('disabled',false);

											comissionamentoPercentual.forEach(x=>{	
												$('select[name=percentual_id_plano]').find(`option[value=${x.id_plano}]`).prop('disabled',true);

												let html = `<tr>
																<td>${x.plano}</td>
																<td>${x.comissao}%</td>
																<td>${x.obs}</td>
																<td style="text-align:right;">
																	<a href="javascript:;" class="button js-comissionamento-editar" data-id="${x.id}" data-tipo="percentual"><i class="iconify" data-icon="fluent:edit-24-regular"></i></a>
																	<a href="javascript:;" class="button js-comissionamento-remover" data-id="${x.id}"><i class="iconify" data-icon="fluent:delete-24-regular"></i></a>
																</td>
															</tr>`;

												$(`.js-tbody-percentual`).append(html);
											});

											$(`.js-tbody-valor tr`).remove();
											$('select[name=valor_id_plano]').find(`option`).prop('disabled',false);

											comissionamentoValor.forEach(x=>{
												$('select[name=valor_id_plano]').find(`option[value=${x.id_plano}]`).prop('disabled',true);

												let html = `<tr>
																<td>${x.plano}</td>
																<td>${x.procedimento}</td>
																<td>${number_format(x.valor_tabela,2,",",".")}</td>
																<td>${number_format(x.comissao,2,",",".")}</td>
																<td>${x.obs}</td>
																<td style="text-align:right;">
																	<a href="javascript:;" class="button js-comissionamento-editar" data-id="${x.id}" data-tipo="valor"><i class="iconify" data-icon="fluent:edit-24-regular"></i></a>
																	<a href="javascript:;" class="button js-comissionamento-remover" data-id="${x.id}"><i class="iconify" data-icon="fluent:delete-24-regular"></i></a>
																</td>
															</tr>`;

												$(`.js-tbody-valor`).append(html);
											});

										}

										$(function(){
											comissionamentosAtualizar();
											comissionamentoTipo();
											$('input[name=comissionamento_tipo]').click(comissionamentoTipo);

											$('.js-table-comissionamento').on('click','.js-comissionamento-editar',function(){
												let id = $(this).attr('data-id');	
												let tipo = $(this).attr('data-tipo');
												let obj = {};
												if(tipo=="valor") {
													obj = comissionamentoValor.find(x=>{ return x.id==id});
													if(obj.id) {
														$('input[name=valor_id_comissionamento]').val(obj.id);
														$('select[name=valor_id_plano]').val(obj.id_plano);
														$('select[name=valor_id_plano]').find(`option[value=${obj.id_plano}]`).prop('disabled',false);
														$('select[name=valor_id_procedimento]').val(obj.id_procedimento);
														$('input[name=valor_tabela]').val(number_format(obj.valor_tabela,2,",","."));
														$('input[name=valor_comissao]').val(number_format(obj.comissao,2,",","."));
														$('input[name=valor_obs]').val(obj.obs);
														$('.js-comissionamento-submit').html(`<i class="iconify" data-icon="fluent:checkmark-12-filled"></i>`);
													}
												} else if(tipo=="percentual") {
													obj = comissionamentoPercentual.find(x=>{ return x.id==id});
													if(obj.id) {
														$('input[name=percentual_id_comissionamento]').val(obj.id);
														$('select[name=percentual_id_plano]').val(obj.id_plano);
														$('select[name=percentual_id_plano]').find(`option[value=${obj.id_plano}]`).prop('disabled',false);
														$('input[name=percentual_comissao]').val(number_format(obj.comissao,2,",","."));
														$('input[name=percentual_obs]').val(obj.obs);
														$('.js-comissionamento-submit').html(`<i class="iconify" data-icon="fluent:checkmark-12-filled"></i>`);
													}
												}


											});

											$('.js-table-comissionamento').on('click','.js-comissionamento-remover',function(){
												let id = $(this).attr('data-id');
												let data = `ajax=comissionamentosRemover&id_comissionamento=${id}&id_colaborador=${id_colaborador}`;
												let obj = $(this);

												obj.html(`<span class="iconify" data-icon="eos-icons:loading"></span>`);
												$.ajax({
													type:"POST",
													data:data,
													success:function(rtn) {
														if(rtn.success) {
															comissionamentosAtualizar();
														} else if(rtn.error) {
															swal({title: "Erro!", text: rtn.error, type:"error", confirmButtonColor: "#424242"});
														} else {
															swal({title: "Erro!", text: `Algum erro ocorreu durante a remoção deste comissionamento!`, type:"error", confirmButtonColor: "#424242"});
														}
													},
													error:function(){
														swal({title: "Erro!", text: `Algum erro ocorreu durante a remoção deste comissionamento!`, type:"error", confirmButtonColor: "#424242"});
													}
												}).done(function(){
													obj.html(`<i class="iconify" data-icon="fluent:delete-24-regular"></i>`);
												})
											})


											$('.js-comissionamento-submit').click(function(){


												let tipo = $(this).attr('data-tipo');

												let data = erro = ``;
												if(tipo=="valor") {

													let id_comissionamento = $('input[name=valor_id_comissionamento]').val();
													let id_plano = $('select[name=valor_id_plano]').val();
													let id_procedimento = $('select[name=valor_id_procedimento]').val();
													let valor_tabela = $('input[name=valor_tabela]').val();
													let comissao = $('input[name=valor_comissao]').val();
													let obs = $('input[name=valor_obs]').val();

													if(id_plano.length==0) erro=`Selecione o Plano`;
													else if(id_procedimento.length==0) erro=`Selecione o Procedimento`;
													else if(valor_tabela.length==0) erro=`Preencha o Valor de Tabela`;
													else if(comissao.length==0) erro=`Preencha o Valor de Comissão`;

													if(erro.length==0) {
														data = `tipo=valor&id_plano=${id_plano}&id_procedimento=${id_procedimento}&valor_tabela=${valor_tabela}&comissao=${comissao}&obs=${obs}&id_comissionamento=${id_comissionamento}`;
													}

												} else if(tipo=="percentual") {

													let id_comissionamento = $('input[name=percentual_id_comissionamento]').val();
													let id_plano = $('select[name=percentual_id_plano]').val();
													let comissao = $('input[name=percentual_comissao]').val();
													let obs = $('input[name=percentual_obs]').val();

													if(id_plano.length==0) erro=`Selecione o Plano`;
													else if(comissao.length==0) erro=`Preencha o Percentual de Comissão`;

													if(erro.length==0) {
														data = `tipo=percentual&id_plano=${id_plano}&comissao=${comissao}&obs=${obs}&id_comissionamento=${id_comissionamento}`;
													}

												}

												if(erro.length>0) {
													swal({title: "Erro!", text: erro, type:"error", confirmButtonColor: "#424242"});
												} else {


													$(this).html(`<span class="iconify" data-icon="eos-icons:loading"></span>`);
													$.ajax({
														type:"POST",
														data:`ajax=comissionamentosPersistir&id_colaborador=${id_colaborador}&${data}`,
														success:function(rtn) {
															if(rtn.success) {
																comissionamentosAtualizar();

																if(tipo=="valor") {
																	$('select[name=valor_id_plano], select[name=valor_id_procedimento], input[name=valor_tabela], input[name=valor_comissao], input[name=valor_obs], input[name=valor_id_comissionamento]').val('');
																} else if(tipo=="percentual") {
																	$('select[name=percentual_id_plano], input[name=percentual_comissao], input[name=percentual_obs], input[name=percentual_id_comissionamento]').val('');
																}

															} else if(rtn.error) {
																swal({title: "Erro!",text:rtn.error,type:"error",confirmButtonColor:"#424242"});
															} else {
																swal({title:"Erro!",text:`Algum erro ocorreu durante a persistência deste comissionamento!`,type:"error",confirmButtonColor:"#424242"});
															}
														},
														error:function(){
															swal({title:"Erro!",text:`Algum erro ocorreu durante a persistência deste comissionamento!`,type:"error", confirmButtonColor:"#424242"});
														}
													}).done(function(){
														$('.js-comissionamento-submit').html(`<i class="iconify" data-icon="fluent:add-circle-24-regular"></i>`);
													});
												}

											})
										})
									</script>
									<?php
									if(empty($values['comissionamento_tipo'])) $values['comissionamento_tipo']="nenhum";
									?>
									<dl>
										<dd>
											<label><input type="radio" name="comissionamento_tipo" value="nenhum"<?php echo $values['comissionamento_tipo']=="nenhum"?" checked":"";?> />Nenhum</label>
											<label><input type="radio" name="comissionamento_tipo" value="percentual" data-tab="percentual"<?php echo $values['comissionamento_tipo']=="percentual"?" checked":"";?> />Percentual <div class="badge-help" title="Comissionamento por porcentagem está vinculado a efetivação do pagamento, por parte do paciente, independente da execução do procedimento."><i class="iconify" data-icon="fluent:chat-help-20-filled"></i></div></label>
											<label><input type="radio" name="comissionamento_tipo" value="valor" data-tab="valorfixo"<?php echo $values['comissionamento_tipo']=="valor"?" checked":"";?> />Valor Fixo <div class="badge-help" title="Comissionamento por valor fixo está vinculado a execução do procedimento, independente do pagamento do paciente."><i class="iconify" data-icon="fluent:chat-help-20-filled"></i></div></label>											
										</dd>										
									</dl>

									<div class="js-comissao js-percentual" style="display:none;">
										<div class="colunas3">
											<input type="hidden" name="percentual_id_comissionamento" />
											<dl>
												<dt>Plano</dt>
												<dd>
													<select name="percentual_id_plano">
														<option value="">-</option>
														<?php
														foreach($_planos as $v) {
															echo '<option value="'.$v->id.'">'.utf8_encode($v->titulo).'</option>';
														}
														?>
													</select>
												</dd>
											</dl>
											<dl>
												<dt>Percentual</dt>
												<dd class="form-comp form-comp_pos"><input type="text" name="percentual_comissao" maxlength="5" class="js-maskFloat2" data-min="0" data-max="100" /><span>%</span></dd>
											</dl>
											<dl>
												<dt>Observação</dt>
												<dd><input type="text" name="percentual_obs" /><a href="javascript:;" class="button button_main js-comissionamento-submit" data-tipo="percentual"><i class="iconify" data-icon="fluent:add-circle-24-regular"></i></a></dd>
											</dl>
										</div>
										<div class="list2">
											<table class="js-table-comissionamento">
												<thead>
													<tr>
														<th>Plano</th>
														<th>Percentual</th>
														<th>Observações</th>
														<th></th>
													</tr>
												</thead>
												<tbody class="js-tbody-percentual">
													<tr>
														<td><strong>UNIMED</strong></td>
														<td>12.5%</td>
														<td>Negociação especial</td>													
														<td style="text-align:right;">
															<a href="" class="button"><i class="iconify" data-icon="fluent:edit-24-regular"></i></a>
															<a href="" class="button"><i class="iconify" data-icon="fluent:delete-24-regular"></i></a>
														</td>
													</tr>
												</tbody>
											</table>
										</div>
									</div><!-- .js-percentual -->

									<div class="js-comissao js-valorfixo" style="display:none;">
										<div class="colunas5">
											<input type="hidden" name="valor_id_comissionamento" />
											<dl>
												<dt>Plano</dt>
												<dd>
													<select name="valor_id_plano">
														<option value="">-</option>
														<?php
														foreach($_planos as $v) {
															echo '<option value="'.$v->id.'">'.utf8_encode($v->titulo).'</option>';
														}
														?>
													</select>
												</dd>
											</dl>
											<dl>
												<dt>Procedimento</dt>
												<dd>
													<select name="valor_id_procedimento">
														<option value="">-</option>
														<?php
														foreach($_procedimentos as $v) {
															echo '<option value="'.$v->id.'">'.utf8_encode($v->titulo).'</option>';
														}
														?>
													</select>
												</dd>
											</dl>
											<dl>
												<dt>Valor Tabela</dt>
												<dd class="form-comp"><span>R$</span><input type="text" name="valor_tabela" class="money" /></dd>
											</dl>
											<dl>
												<dt>Valor Comissão</dt>
												<dd class="form-comp"><span>R$</span><input type="text" name="valor_comissao" class="money" /></dd>
											</dl>
											<dl>
												<dt>Observações</dt>
												<dd>
													<input type="text" name="valor_obs" />
													<a href="javascript:;" class="button button_main js-comissionamento-submit" data-tipo="valor"><i class="iconify" data-icon="fluent:add-circle-24-regular"></i></a>
												</dd>
											</dl>
										</div>
										<div class="list2">
											<table class="js-table-comissionamento">
												<thead>
													<tr>
														<th>Plano</th>
														<th>Procedimento</th>
														<th>Valor Tabela</th>
														<th>Valor Comissão</th>
														<th>Observação</th>
														<th></th>
													</tr>
												</thead>
												<tbody class="js-tbody-valor">
													<tr>
														<td><strong>UNIMED</strong></td>
														<td>Prótese Dental</td>
														<td>R$ 200,00</td>													
														<td>R$ 25,00</td>
														<td>Negociação Especial</td>
														<td style="text-align:right;">
															<a href="" class="button"><i class="iconify" data-icon="fluent:edit-24-regular"></i></a>
															<a href="" class="button"><i class="iconify" data-icon="fluent:delete-24-regular"></i></a>
														</td>
													</tr>												
												</tbody>
											</table>
										</div>
									</div><!-- .js-valorfixo -->

								</fieldset>*/
								?>
							</div><!-- .js-dadosdacontratacao -->

							
							<script type="text/javascript">
								var horarios = [];
								var id_colaborador=<?php echo $cnt->id;?>;

								const horariosListar = () => {
									if(horarios) {
										$('.js-td').html('')

										let cadeirasComHorarios = [];
										for(var id_cadeira in horarios) {
											let index = `.js-${id_cadeira}`;
											for(var dia in horarios[id_cadeira]) {

												if(cadeirasComHorarios.includes(id_cadeira)===false) cadeirasComHorarios.push(id_cadeira);
												horarios[id_cadeira][dia].forEach(x=>{
													
													$(`${index}-${dia}`).append(`<a href="javascript:;" class="js-editar" data-id="${x.id}">${x.inicio}~${x.fim}<br />`);
												})
											}
										}
										$('.js-cadeira').hide();
										cadeirasComHorarios.forEach(idC=>{
											$(`.js-cadeira-${idC}`).show();
										})
										
									}
								}
								const horariosAtualizar = () => {
									let data = `ajax=horariosListar&id_colaborador=${id_colaborador}`;
									$.ajax({
										type:"POST",
										data:data,
										success:function(rtn) {
											if(rtn.success) {
												horarios=rtn.horarios;

												if(rtn.carga) {
													$('.js-carga').val(rtn.carga);
												}
												horariosListar();
											}
										}
									})
								}
								
								const horarioEditar = (id_horario) => {
									let data = `ajax=horariosEditar&id_horario=${id_horario}&id_colaborador=${id_colaborador}`;
									var horarioObj = [];
									$.ajax({
										type:"POST",
										data:data,
										success:function(rtn) {
											if(rtn.success) {

												$(`.js-id`).val(rtn.id);
												$(`.js-id_cadeira`).val(rtn.id_cadeira);
												$(`.js-dia`).val(rtn.dia);
												$(`.js-inicio`).val(rtn.inicio);
												$(`.js-fim`).val(rtn.fim);
												$('.js-horarios-submit').html(`<i class="iconify" data-icon="fluent:checkmark-12-filled"></i>`);
												$('.js-horarios-remover').show();
											}
										}
									});
								}

								const habilitaAgendamento = () => {
									if($('.js-check_agendamento').prop('checked')===true) {
										$('.js-box-habilitarAgendamento').fadeIn();
									} else {
										$('.js-box-habilitarAgendamento').hide();

									}
								}



								$(function(){
									horariosAtualizar();
									habilitaAgendamento();

									$('.js-horarios-submit').click(function(){
										let obj = $(this);

										if(obj.attr('data-loading')==0) {

											let id_cadeira = $(`.js-id_cadeira`).val();
											let id = $(`.js-id`).val();
											let dia = $(`.js-dia`).val();
											let inicio = $(`.js-inicio`).val();
											let fim = $(`.js-fim`).val();
										    

											errInicio = validaHoraMinuto(inicio);
											errFim = validaHoraMinuto(fim);

											if(dia.length==0) {
												swal({title: "Erro!", text: "Selecione o Dia!", type:"error", confirmButtonColor: "#424242"});
											} else if(errInicio.length>0) {
												swal({title: "Erro!", text: `Erro na hora início: ${errInicio}`, type:"error", confirmButtonColor: "#424242"});
											} else if(errFim.length>0) {
												swal({title: "Erro!", text: `Erro na hora final: ${errFim}`, type:"error", confirmButtonColor: "#424242"});
											} else if(dia.length==0) {
												swal({title: "Erro!", text: "Selecione a Cadeira!", type:"error", confirmButtonColor: "#424242"});
											} else  {

												obj.html(`<span class="iconify" data-icon="eos-icons:loading"></span>`);
												obj.attr('data-loading',1);

												let data = `ajax=horariosPersistir&id_cadeira=${id_cadeira}&dia=${dia}&inicio=${inicio}&fim=${fim}&id_colaborador=${id_colaborador}&id=${id}`;
												$.ajax({
													type:'POST',
													data:data,
													success:function(rtn) {
														if(rtn.success) {
															horariosAtualizar();	

															$(`.js-id_cadeira`).val('');
															$(`.js-id`).val(0);
															$(`.js-dia`).val('');
															$(`.js-fim`).val('');
															$(`.js-inicio`).val('');
														} else if(rtn.error) {
															swal({title: "Erro!", text: rtn.error, type:"error", confirmButtonColor: "#424242"});
														} else {
															swal({title: "Erro!", text: "Algum erro ocorreu! Tente novamente.", type:"error", confirmButtonColor: "#424242"});
														}
														
													},
													error:function() {
														swal({title: "Erro!", text: "Algum erro ocorreu! Tente novamente.", type:"error", confirmButtonColor: "#424242"});
													}
												}).done(function(){
													$('.js-horarios-remover').hide();
													obj.html(`<i class="iconify" data-icon="fluent:add-circle-24-regular"></i>`);
													obj.attr('data-loading',0);
												});

											}
										}
									})

									$('.js-horario-table').on('click','.js-editar',function(){
										let id = $(this).attr('data-id');
										horarioEditar(id);
									});

									$('.js-fieldset-horarios').on('click','.js-horarios-remover',function(){
										let obj = $(this);

										if(obj.attr('data-loading')==0) {
											let id_horario = $('.js-id').val();
											swal({
												title: "Atenção",
												text: "Você tem certeza que deseja remover este registro?",
												type: "warning",
												showCancelButton: true,
												confirmButtonColor: "#DD6B55",
												confirmButtonText: "Sim!",
												cancelButtonText: "Não",
												closeOnConfirm:false,
												closeOnCancel: false }, 
												function(isConfirm){   
													if (isConfirm) {   
														obj.html(`<span class="iconify" data-icon="eos-icons:loading"></span>`);
														obj.attr('data-loading',1);
														let data = `ajax=horariosRemover&id_horario=${id_horario}&id_colaborador=${id_colaborador}`; 
														$.ajax({
															type:"POST",
															data:data,
															success:function(rtn) {
																if(rtn.success) {
																	$(`.js-id`).val(0);
																	$(`.js-id_cadeira`).val(0);
																	$(`.js-dia`).val('');
																	$(`.js-fim`).val('');
																	$(`.js-inicio`).val('');
																	horariosAtualizar();
																	swal.close();   
																} else if(rtn.error) {
																	swal({title: "Erro!", text: rtn.error, type:"error", confirmButtonColor: "#424242"});
																} else {
																	swal({title: "Erro!", text: "Algum erro ocorreu durante a remoção deste horário!", type:"error", confirmButtonColor: "#424242"});
																}
															},
															error:function(){
																swal({title: "Erro!", text: "Algum erro ocorreu durante a remoção deste horário!", type:"error", confirmButtonColor: "#424242"});
															}
														}).done(function(){
															$('.js-horarios-remover').hide();
															obj.html('<i class="iconify" data-icon="fluent:delete-24-regular"></i>');
															obj.attr('data-loading',0);
															$(`.js-horarios-submit`).html(`<i class="iconify" data-icon="fluent:add-circle-24-regular"></i>`);
														});
													} else {   
														swal.close();   
													} 
												});
											}
									});

									$('.js-check_agendamento').on('click','',habilitaAgendamento);

								});
							</script>

							<div class="js-tabs js-habilitaragendamento" style="display:none;">
								
							</div>

							<div class="js-tabs js-acessoaosistema" style="display:none;">

								<div class="colunas3">
									
									<dl>
										<dt>Email de recuperação</dt>
										<dd><input type="text" name="email" value="<?php echo $values['email'];?>" /></dd>
									</dl>
									<dl>
										<dt>Senha</dt>
										<dd><input type="password" name="senha" value="" /></dd>
									</dl>									
								</div>
								<dl class="dl2">
									<dd>
										<label><input type="checkbox" name="permitir_acesso" value="1" class="input-switch" onclick="$(this).prop('checked')==true?$('input[name=email]').addClass('obg'):$('input[name=email]').removeClass('obg');"<?php echo $values['permitir_acesso']==1?" checked":"";?> /> Acesso ao sistema</label>
										<?php /*<label><input type="checkbox" name="" class="input-switch"> Ativo</label>*/?>
									</dd>
								</dl>
							</div>
							<?php
							}
							?>
						</form>

				<?php
				}

				# Listagem
				else {
					$values=$adm->get($_GET);
				?>
						<section class="filter">
							<div class="filter-group">
								<div class="filter-form form">
									<dl>
										<dd><a href="<?php echo $_page."?form=1";?>" class="button button_main"><i class="iconify" data-icon="fluent:add-circle-24-regular"></i> <span>Novo Colaborador</span></a></dd>
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
					
						
						<div class="list1">
							<?php
							$where="where lixo=0";
							if(isset($values['busca']) and !empty($values['busca'])) {
								$where.=" and nome like '%".$values['busca']."%'";
							}
							//$sql->consult($_table,"*",$where." order by nome asc");
							$sql->consultPagMto2($_table,"*",10,$where." order by contratacaoAtiva desc, nome asc","",15,"pagina",$_page."?".$url."&pagina=");
							if($sql->rows==0) {
								if(isset($values['busca'])) $msg="Nenhum Resultado encontrado";
								else $msg="Nenhum colaborador cadastrado";

								echo "<center>$msg</center>";
							} else {
							?>
							<table>
								<?php
								while($x=mysqli_fetch_object($sql->mysqry)) {
									if($x->contratacaoAtiva==1) {
										$cssOpacity="";
									} else {
										$cssOpacity='style="opacity:0.5;"';
									}
								?>
								<tr onclick="document.location.href='<?php echo $_page."?form=1&edita=$x->id&$url";?>';"<?php echo $cssOpacity;?>>
									<td><h1><strong><?php echo utf8_encode($x->nome);?></strong></h1></td>
									<td><?php echo $x->data_nascimento!="0000-00-00"?idade($x->data_nascimento)." anos":"";?></td>
									<td>
										<?php
										if($x->permitir_acesso==1) {
										?>
										<strong style="color:var(--verde);"><i class="iconify" data-icon="fluent:checkmark-circle-12-regular"></i> Acesso ao sistema</strong>
										<?php
										} /*else {
										?>
										<strong style="color:var(--vermelho);"><i class="iconify" data-icon="fluent:checkmark-circle-12-regular"></i> Acesso Desativado</strong>
										<?php	
										}*/
										?>
									</td>
									<td>
										<?php
										if($x->check_agendamento==1 and !empty($x->calendario_iniciais)) {
										?>
										<div class="badge-prof" title="Kroner Costa" style="<?php echo empty($x->calendario_cor)?"":"background:$x->calendario_cor";?>"><?php echo $x->calendario_iniciais;?></div>
										<?php
										}
										?>
									</td>
								</tr>
								<?php
								}
								?>								
							</table>
							<?php
							}
							?>		
						</div>
						<?php
						if(isset($sql->myspaginacao) and !empty($sql->myspaginacao)) {
						?>
						<div class="pagination">						
							<?php echo $sql->myspaginacao;?>
						</div>
						<?php
						}
						?>
				<?php
				}
				?>
				
					</div>
					
					
				</div>

			</section>
		
		</div>
	</main>

<?php 
include "includes/footer.php";
?>	