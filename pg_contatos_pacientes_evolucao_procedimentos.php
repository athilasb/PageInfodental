<?php
	if(isset($_POST['ajax'])) {
		require_once("lib/conf.php");	
		require_once("usuarios/checa.php");

		$sql = new Mysql();

		$rtn = array();
		if($_POST['ajax']=="planos") {
			$planos=array();
			if(isset($_POST['id_procedimento']) and is_numeric($_POST['id_procedimento'])) {
				$sql->consult($_p."parametros_procedimentos","*","where id='".addslashes($_POST['id_procedimento'])."' and lixo=0");
				if($sql->rows) {
					$procedimento=mysqli_fetch_object($sql->mysqry);
				}
			}
			$unidade='';
			if(isset($_POST['id_unidade']) and is_numeric($_POST['id_unidade']) and isset($_optUnidades[$_POST['id_unidade']])) {
				$unidade=$_optUnidades[$_POST['id_unidade']];
			}

			if(is_object($procedimento) and is_object($unidade)) {
				$sql->consult($_p."parametros_procedimentos_planos","*","where id_procedimento=$procedimento->id"); 
				
				$planosID=array();
				$procedimentoPlano=array();
				while($x=mysqli_fetch_object($sql->mysqry)) {
					$procedimentoPlano[$x->id_plano]=$x;
					$planosID[]=$x->id_plano;
				}	


				if(count($planosID)) {
					$sql->consult($_p."parametros_planos","*","where id IN (".implode(",",$planosID).")");
					if($sql->rows) {
						while($x=mysqli_fetch_object($sql->mysqry)) {
							if(isset($procedimentoPlano[$x->id])) {
								$procP=$procedimentoPlano[$x->id];
								$planos[]=array('id'=>$x->id,'titulo'=>utf8_encode($x->titulo),'valor'=>$procP->valor);
							}
						}
					}
				}

				$rtn=array('success'=>true,'planos'=>$planos);
			} else {
				$rtn=array('success'=>false,'error'=>'Procedimento/Unidade não definida(s)!');
			}
		}

		header("Content-type: application/json");
		echo json_encode($rtn);
		die();
	}
	include "includes/header.php";
	include "includes/nav.php";

	$_table=$_p."pacientes";
	$_page=basename($_SERVER['PHP_SELF']);

	$evolucao='';
	$sql->consult($_p."pacientes_evolucoes_tipos","*","where id=2");
	$evolucao=mysqli_fetch_object($sql->mysqry);

	$_width=400;
	$_height=400;
	$_dir="arqs/pacientes/";

	$_cidades=array();
	$sql->consult($_p."cidades","*","");
	while($x=mysqli_fetch_object($sql->mysqry)) $_cidades[$x->id]=$x;

	$_profissionais=array();
	$sql->consult($_p."colaboradores","id,nome,calendario_iniciais,foto,calendario_cor","where tipo_cro<>'' and lixo=0 order by nome asc");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_profissoes[$x->id]=$x;
	}


	$_planos=array();
	$sql->consult($_p."parametros_planos","*","where lixo=0 order by titulo asc");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_planos[$x->id]=$x;
	}
	$_usuarios=array();
	$sql->consult($_p."usuarios","id,nome","order by nome asc");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_usuarios[$x->id]=$x;
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

	$paciente=$cnt='';
	if(isset($_GET['id_paciente']) and is_numeric($_GET['id_paciente'])) {
		$sql->consult($_p."pacientes","*","where id='".$_GET['id_paciente']."'");
		if($sql->rows) {
			$paciente=mysqli_fetch_object($sql->mysqry);
			$cnt=$paciente;
		}
	}

	$_selectSituacaoOptions=array('iniciar'=>array('titulo'=>'NÃO INICIADO','cor'=>'orange'),
											'iniciado'=>array('titulo'=>'EM TRATAMENTO','cor'=>'blue'),
											'finalizado'=>array('titulo'=>'FINALIZADO','cor'=>'green'),
											'cancelado'=>array('titulo'=>'CANCELADO','cor'=>'red'),
											//'cancelado'=>array('titulo'=>'CANCELADO');
										);

	//$selectSituacaoOptions='<select class="js-situacao">';
	$selectSituacaoOptions='';
	foreach($_selectSituacaoOptions as $key=>$value) {
		$selectSituacaoOptions.='<option value="'.$key.'">'.$value['titulo'].'</option>';
	}
	//$selectSituacaoOptions.='</select>';

	$campos=explode(",","nome,situacao,noem,sexo,foto,rg,rg_orgaoemissor,rg_estado,cpf,data_nascimento,profissao,estado_civil,telefone1,telefone1_whatsapp,telefone1_whatsapp_permissao,telefone2,email,instagram,instagram_naopossui,musica,indicacao_tipo,indicacao,cep,endereco,numero,complemento,bairro,estado,cidade,id_cidade,responsavel_possui,responsavel_nome,responsavel_sexo,responsavel_rg,responsavel_rg_orgaoemissor,responsavel_rg_estado,responsavel_datanascimento,responsavel_estadocivil,responsavel_cpf,responsavel_profissao,responsavel_grauparentesco,preferencia_contato");
	
	foreach($campos as $v) $values[$v]='';
	$values['data']=date('d/m/Y H:i');
	$values['sexo']='M';


	if(is_object($paciente)) {
		$values=$adm->values($campos,$cnt);
		$values['data']=date('d/m/Y H:i',strtotime($cnt->data));
	}

	$_regioesOpcoes=array();
	$sql->consult($_p."parametros_procedimentos_regioes_opcoes","*","order by titulo asc");
	while($x=mysqli_fetch_object($sql->mysqry)) $_regioesOpcoes[$x->id_regiao][]=$x;

	$_profissionais=array();
	$sql->consult($_p."colaboradores","*","where lixo=0 order by nome asc");//"where unidades like '%,$unidade->id,%' and lixo=0 order by nome asc");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_profissionais[$x->id]=$x;
	}

	$selectProfissional='<select class="js-profissional"><option value="">-</option>';
	foreach($_profissionais as $p) {
	
		$aux=explode(" ",$p->nome);
		$aux[0]=strtoupper($aux[0]);
		$iniciais='';
		if($aux[0] =="DR" or $aux[0]=="DR." or $aux[0]=="DRA" or $aux[0]=="DRA.") {
			$iniciais=strtoupper(substr($aux[1],0,1));
			if(isset($aux[2])) $iniciais.=strtoupper(substr($aux[2],0,1));
		} else {
			$iniciais=strtoupper(substr($aux[0],0,1));
			if(isset($aux[1])) $iniciais.=strtoupper(substr($aux[1],0,1));
		}
											
		$selectProfissional.='<option value="'.$p->id.'" data-iniciais="'.$iniciais.'" data-iniciaisCor="'.$p->calendario_cor.'">'.utf8_encode($p->nome).'</option>';
	}
	$selectProfissional.='</select>';

	$_procedimentosAvulsos=array();
	$sql->consult($_p."parametros_procedimentos","*","where lixo=0 order by titulo asc");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_procedimentosAvulsos[$x->id]=$x;
	}

	$tratamentosIds=array(-1);
	$sql->consult($_p."pacientes_tratamentos","*","where id_paciente=$paciente->id and  status='APROVADO' and lixo=0");
	while($x=mysqli_fetch_object($sql->mysqry)) $tratamentosIds[]=$x->id;

	$procedimentosIds=array(-1);
	$_procedimentosAprovados=array();
	$where="where lixo=0 and situacao='aprovado' and status_evolucao NOT IN ('cancelado','finalizado') and id_tratamento IN (".implode(",",$tratamentosIds).")";

	//die();
	$sql->consult($_p."pacientes_tratamentos_procedimentos","*",$where);
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$procedimentosIds[]=$x->id_procedimento;
		$_procedimentosAprovados[$x->id]=$x;
	}

	$_procedimentos=array();
	$sql->consult($_p."parametros_procedimentos","*","where id IN (".implode(",",$procedimentosIds).") and lixo=0");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_procedimentos[$x->id]=$x;
	}

	$evolucao='';
	$evolucaoProcedimentos=array();
	if(isset($_GET['edita']) and is_numeric($_GET['edita'])) {	
		$sql->consult($_p."pacientes_evolucoes","*","where id='".$_GET['edita']."' and id_tipo=2");
		if($sql->rows) {
			$evolucao=mysqli_fetch_object($sql->mysqry);

			$sql->consult($_p."pacientes_evolucoes_procedimentos","*","where id_evolucao=$evolucao->id and lixo=0");
			if($sql->rows) {
				$registros=array();
				$tratamentosProdecimentosIds=array(-1);
				while($x=mysqli_fetch_object($sql->mysqry)) {
					$registros[]=$x;
					$tratamentosProdecimentosIds[]=$x->id_tratamento_procedimento;
				}

				$_tratamentosProcedimentos=array(-1);
				$where="where id IN (".implode(",",$tratamentosProdecimentosIds).") and id_paciente=$paciente->id and lixo=0";
				$sql->consult($_p."pacientes_tratamentos_procedimentos","*",$where);
				while($x=mysqli_fetch_object($sql->mysqry)) $_tratamentosProcedimentos[$x->id]=$x;

				foreach($registros as $x) {
					if(isset($_tratamentosProcedimentos[$x->id_tratamento_procedimento])) {
						$tratamentoProc=$_tratamentosProcedimentos[$x->id_tratamento_procedimento];

						if(isset($_procedimentos[$tratamentoProc->id_procedimento])) {
							$proc=$_procedimentos[$tratamentoProc->id_procedimento];
							$profissionalCor='';
							$profissionalIniciais='';

							if(isset($_profissionais[$x->id_profissional])) {
								$p=$_profissionais[$x->id_profissional];
								$profissionalIniciais=$p->calendario_iniciais;
								$profissionalCor=$p->calendario_cor;
							}

							$autor='-';
							if(isset($_usuarios[$evolucao->id_usuario])) {
								$p=$_usuarios[$evolucao->id_usuario];
								$autor=utf8_encode($p->nome);
							}


							$evolucaoProcedimentos[]=array('id'=>$x->id,
															'autor'=>$autor,
															'data'=>date('d/m/Y',strtotime($x->data)),
															'id_usuario'=>$evolucao->id_usuario,
															'id_tratamento_procedimento'=>$tratamentoProc->id,
															'id_procedimento'=>$tratamentoProc->id_procedimento,
															'id_profissional'=>$x->id_profissional,
															'obs'=>utf8_encode($x->obs),
															'opcao'=>utf8_encode($tratamentoProc->opcao),
															'plano'=>isset($_planos[$tratamentoProc->id_plano])?utf8_encode($_planos[$tratamentoProc->id_plano]->titulo):'-',
															'profissionalCor'=>$profissionalCor,
															'profissionalIniciais'=>$profissionalIniciais,
															'statusEvolucao'=>$x->status,
														 	'titulo'=>utf8_encode($proc->titulo),
														 	'avulso'=> 0);
						}
					} else if(isset($_procedimentosAvulsos[$x->id_tratamento_procedimento])) {
						$proc=$_procedimentosAvulsos[$x->id_tratamento_procedimento];
						$profissionalCor='';
						$profissionalIniciais='';

						if(isset($_profissionais[$x->id_profissional])) {
							$p=$_profissionais[$x->id_profissional];
							$profissionalIniciais=$p->calendario_iniciais;
							$profissionalCor=$p->calendario_cor;
						}

						$autor='-';
						if(isset($_usuarios[$evolucao->id_usuario])) {
							$p=$_usuarios[$evolucao->id_usuario];
							$autor=utf8_encode($p->nome);
						}


						$evolucaoProcedimentos[]=array('id'=>$x->id,
														'autor'=>$autor,
														'data'=>date('d/m/Y',strtotime($x->data)),
														'id_usuario'=>$evolucao->id_usuario,
														'id_tratamento_procedimento'=>0,
														'id_procedimento'=>$x->id_tratamento_procedimento,
														'id_profissional'=>$x->id_profissional,
														'obs'=>utf8_encode($x->obs),
														'opcao'=>utf8_encode($x->opcao),
														'id_opcao'=>utf8_encode($x->id_opcao),
														'id_plano'=>utf8_encode($x->id_plano),
														'plano'=>isset($_planos[$x->id_plano])?utf8_encode($_planos[$x->id_plano]->titulo):'-',
														'profissionalCor'=>$profissionalCor,
														'profissionalIniciais'=>$profissionalIniciais,
														'statusEvolucao'=>$x->status,
													 	'titulo'=>utf8_encode($proc->titulo),
													 	'avulso'=>1);
					}
				}
			}
		} else {
			$jsc->jAlert("Procedimento Aprovado não encontrado!","erro","document.location.href='pg_contatos_pacientes_evolucao.php?id_paciente='".$paciente->id."'");
			die();
		}
	}


	if(isset($_POST['acao'])) {

		if(isset($_POST['procedimentos']) and !empty($_POST['procedimentos'])) {
			$procedimentosJSON = json_decode($_POST['procedimentos']);
			$procedimentosEvoluidos=array();
			$procedimentosAvulsos=array();
			$erro='';

			foreach($procedimentosJSON as $v) {

				if($v->avulso==0) {
					$sql->consult($_p."pacientes_tratamentos_procedimentos","*","where id=$v->id_tratamento_procedimento and lixo=0");
					if($sql->rows) {
						$x=mysqli_fetch_object($sql->mysqry);
						$procedimentosEvoluidos[]=array('tratamentoProc'=>$x,'evolucaoProc'=>$v,'id_evolucao_procedimento'=>isset($v->id)?$v->id:0);
					} else {
						$erro='Procedimento '.$v->titulo.' não foi encontrado!';
					}
				} else {
					$procedimentosAvulsos[]=$v;
				}
			}

			if(empty($erro)) {

				if(count($procedimentosEvoluidos)>0 or count($procedimentosAvulsos)>0) {

					if(is_object($evolucao)) {
						$sql->update($_p."pacientes_evolucoes","obs='".addslashes(utf8_decode($_POST['obs']))."',
																data_evolucao='".addslashes(invDate($_POST['data_evolucao']))."'","where id=$evolucao->id");
						$id_evolucao=$evolucao->id;
					} else {
						// id_tipo = 2 -> Procedimentos Aprovados
						$sql->consult($_p."pacientes_evolucoes","*","WHERE data > NOW() - INTERVAL 1 MINUTE and 
																								id_paciente=$paciente->id and
																								id_tipo=2 and  
																								id_usuario=$usr->id");	
						if($sql->rows) {
							$e=mysqli_fetch_object($sql->mysqry);
							$sql->update($_p."pacientes_evolucoes","obs='".addslashes($_POST['obs'])."',
																data_evolucao='".addslashes(invDate($_POST['data_evolucao']))."'","where id=$e->id");
							$id_evolucao=$e->id;
						} else {
							$sql->add($_p."pacientes_evolucoes","data=now(),
																	id_tipo=2,
																	id_paciente=$paciente->id,
																	id_usuario=$usr->id,
																	data_evolucao='".addslashes(invDate($_POST['data_evolucao']))."',
																	obs='".addslashes(utf8_decode($_POST['obs']))."'");
							$id_evolucao=$sql->ulid;
						}
					}


					foreach ($procedimentosAvulsos as $obj) {
						$vSQLProc="data=now(),
									lixo=0,
									id_paciente=$paciente->id,
									id_evolucao=$id_evolucao,
									opcao='".addslashes(utf8_decode($obj->opcao))."',
									id_opcao='".addslashes($obj->id_opcao)."',
									id_plano='".addslashes($obj->id_plano)."',
									plano='".addslashes($obj->plano)."',
									id_tratamento_procedimento='".addslashes($obj->id_procedimento)."',
									id_profissional='".addslashes($obj->id_profissional)."',
									obs='".addslashes(utf8_decode($obj->obs))."'";

						$evProc='';
						if(isset($obj->id) and is_numeric($obj->id)) {
							$sql->consult($_p."pacientes_evolucoes_procedimentos","*","where id=$obj->id and id_paciente=$paciente->id and lixo=0");
							if($sql->rows) {
								$evProc=mysqli_fetch_object($sql->mysqry);
							}
						}

						if(empty($evProc)) {
							$sql->consult($_p."pacientes_evolucoes_procedimentos","*","WHERE data > NOW() - INTERVAL 1 MINUTE and 
																								id_paciente=$paciente->id and 
																								id_evolucao=$id_evolucao and 
																								id_tratamento_procedimento=0 and 
																								id_opcao='".addslashes($obj->id_opcao)."'");	
							if($sql->rows) {
								$x=mysqli_fetch_object($sql->mysqry);
								$sql->update($_p."pacientes_evolucoes_procedimentos",$vSQLProc,"where id=$x->id");
							} else {
								$sql->add($_p."pacientes_evolucoes_procedimentos",$vSQLProc);
							}
						} else {
							$sql->update($_p."pacientes_evolucoes_procedimentos",$vSQLProc,"where id=$evProc->id");
						}
					}
					

					foreach($procedimentosEvoluidos as $obj) {
						$obj=(object)$obj;
						$tratamentoProc=$obj->tratamentoProc;
						$evolucaoProc=$obj->evolucaoProc;
						$vSQLProc="data=now(),
									id_paciente=$paciente->id,
									id_evolucao=$id_evolucao,
									id_tratamento_procedimento='".addslashes($evolucaoProc->id_tratamento_procedimento)."',
									id_tratamento='".addslashes($tratamentoProc->id_tratamento)."',
									id_profissional='".addslashes($evolucaoProc->id_profissional)."',
									status='".addslashes($evolucaoProc->statusEvolucao)."',
									obs='".addslashes(utf8_decode($evolucaoProc->obs))."'";

						$evProc='';
						if(isset($obj->id_evolucao_procedimento) and is_numeric($obj->id_evolucao_procedimento)) {
							$sql->consult($_p."pacientes_evolucoes_procedimentos","*","where id=$obj->id_evolucao_procedimento and id_paciente=$paciente->id and lixo=0");
							if($sql->rows) {
								$evProc=mysqli_fetch_object($sql->mysqry);
							}
						}

						if(empty($evProc)) {
							$sql->consult($_p."pacientes_evolucoes_procedimentos","*","WHERE data > NOW() - INTERVAL 1 MINUTE and 
																								id_paciente=$paciente->id and 
																								id_evolucao=$id_evolucao and 
																								id_tratamento='".addslashes($tratamentoProc->id_tratamento)."'");	
							if($sql->rows) {
								$x=mysqli_fetch_object($sql->mysqry);
								$sql->update($_p."pacientes_evolucoes_procedimentos",$vSQLProc,"where id=$x->id");
							} else {
								$sql->add($_p."pacientes_evolucoes_procedimentos",$vSQLProc);
							}
						} else {
							$sql->update($_p."pacientes_evolucoes_procedimentos",$vSQLProc,"where id=$evProc->id");
						}


						// atualiza status de Tratamento / Procedimento
						if($evolucaoProc->statusEvolucao=="iniciar" or 
								$evolucaoProc->statusEvolucao=="iniciado" or 
								$evolucaoProc->statusEvolucao=="finalizado" or 
								$evolucaoProc->statusEvolucao=="cancelado") {

							$sql->update($_p."pacientes_tratamentos_procedimentos","status_evolucao='".$evolucaoProc->statusEvolucao."'","where id='".$evolucaoProc->id_tratamento_procedimento."'");
						}
					}	

					$jsc->jAlert("Evolução salva com sucesso!","sucesso","document.location.href='pg_contatos_pacientes_evolucao.php?id_paciente=$paciente->id'");
					die();
				} else {
					$jsc->jAlert("Adicione pelo menos um procedimento!","erro","");
				}

			} else {
				$jsc->jAlert($erro,"erro","");
			}

		} else {
			$jsc->jAlert("Adicione pelo menos um procedimento para adicionar à Evolução","erro","");
		}
	}

	//var_dump($evolucaoProcedimentos);

	?>
	<section class="content">
		
		<?php
		require_once("includes/abaPaciente.php");
		?>

		<script type="text/javascript">
			var popViewInfos = [];
			const popView = (obj) => {


				index=$(obj).index();


				$('#cal-popup')
						.removeClass('cal-popup_left')
						.removeClass('cal-popup_right')
						.removeClass('cal-popup_bottom')
						.removeClass('cal-popup_top');

				let clickTop=obj.getBoundingClientRect().top+window.scrollY;
			
				let clickLeft=Math.round(obj.getBoundingClientRect().left);
				let clickMargin=Math.round(obj.getBoundingClientRect().width/2);
				$(obj).prev('.cal-popup')
						.removeClass('cal-popup_left')
						.removeClass('cal-popup_right')
						.removeClass('cal-popup_bottom')
						.removeClass('cal-popup_top');

				let popClass='cal-popup_top';
				$('#cal-popup').addClass(popClass).toggle();
				$('#cal-popup').css({'top':clickTop,'left':clickLeft,'margin-left': clickMargin});
				$('#cal-popup').show();
				console.log(procedimentos[index]);
				/*if(popViewInfos[index].opcao.length>0) {
					$('#cal-popup .js-opcaoEQtd').html(`Região: ${popViewInfos[index].opcao}`);
				} else {
					$('#cal-popup .js-opcaoEQtd').html(`Quantidade: ${popViewInfos[index].quantidade}`);
				}*/

				$('#cal-popup .js-obs').val(jsonUnEscape(procedimentos[index].obs));
				$('#cal-popup .js-titulo').html(procedimentos[index].titulo);
				$('#cal-popup .js-plano').html(procedimentos[index].plano);
				$('#cal-popup .js-opcao').html(procedimentos[index].opcao);
				$('#cal-popup .js-autor').html(procedimentos[index].autor);
				$('#cal-popup .js-autor-data').html(procedimentos[index].data);
				$('#cal-popup .js-profissional').val(procedimentos[index].id_profissional);

				if(procedimentos[index].statusEvolucao && $('#cal-popup .js-situacao').val() == null) {
					$('#cal-popup .js-situacao').css('display', 'block');
					$('#cal-popup .js-situacao').append('<?php echo $selectSituacaoOptions;?>');
					$('#cal-popup .js-situacao').val(procedimentos[index].statusEvolucao);
				}
				
				$('#cal-popup .js-index').val(index);
			}

			var procedimentos = JSON.parse(jsonEscape(`<?php echo json_encode($evolucaoProcedimentos);?>`));

			var cardHTML = `<a href="javascript:;" class="reg-group js-procedimento">
								<div class="reg-color" style="background-color:palegreen"></div>
								<div class="reg-data js-titulo" style="flex:0 1 300px">
									<h1></h1>
									<p></p>
								</div>
								<div class="reg-data js-status">
									<p></p>
								</div>									
								<div class="reg-user">
									<span style="background:blueviolet">KP</span>
								</div>
							</a>`;

			var autor = `<?php echo utf8_encode($usr->nome);?>`;
			var id_usuario = `<?php echo utf8_encode($usr->id);?>`;

			const procedimentosListar = () => {

				$('.js-procedimento').remove();

				procedimentos.forEach(x=>{
					$('.js-div-procedimentos').append(cardHTML);

					let cor = `#CCC`;
					let status = ``;

					if(x.statusEvolucao=='iniciar') {
						status=`Não iniciado`;
						cor=`orange`;
					} else if(x.statusEvolucao=='iniciado') {
						status=`Em Tratamento`;
						cor=`blue`;
					} else if(x.statusEvolucao=='finalizado') {
						status=`Finalizado`;
						cor=`green`;
					} else if(x.statusEvolucao=='cancelado') {
						cor=`red`;
						status=`Cancelado`;
					}


					$('.js-procedimento .reg-color:last').css('background-color',cor);
					$('.js-procedimento .js-titulo:last').html(`<h1>${x.titulo}</h1><p>${x.opcao} - ${x.plano}</p>`);
					$('.js-procedimento .js-status:last').html(`<p>${status}</p>`);
					$('.js-procedimento .reg-user:last span').html((!x.profissionalIniciais || x.profissionalIniciais.length==0)?'<span class="iconify" data-icon="bi:person-fill" data-inline="false"></span>':x.profissionalIniciais);
					$('.js-procedimento .reg-user:last span').css('background',(!x.profissionalCor || x.profissionalCor.length==0)?'':x.profissionalCor);
					$(`.js-procedimento:last`).attr('data-usuario',autor);
					$(`.js-procedimento:last`).click(function(){popView(this);});
				});

				$('textarea[name=procedimentos]').val(JSON.stringify(procedimentos));

			}
			$(function(){
				<?php
				if(isset($evolucao)) {
				?>
				procedimentosListar();
				<?php
				}
				?>
				$(document).mouseup(function(e)  {
				    var container = $("#cal-popup");
				    // if the target of the click isn't the container nor a descendant of the container
				    if (!container.is(e.target) && container.has(e.target).length === 0) 
				    {
				       $('#cal-popup').hide();
				    }
				});

				$('.js-btn-salvar').click(function(){
					$('.js-form-evolucao').submit();
				});

				$('.js-btn-fechar').click(function(){$('.cal-popup').hide();})

				$('.js-btn-add').click(function(){
					let id_procedimento = $('select.js-sel-procedimento').val();
					let opcao = $('select.js-sel-procedimento option:selected').attr('data-opcao');
					let plano = $('select.js-sel-procedimento option:selected').attr('data-plano');
					let titulo = $('select.js-sel-procedimento option:selected').attr('data-titulo');
					let id_profissional = $('select.js-sel-procedimento option:selected').attr('data-id_profissional');
					let profissionalIniciais = $('select.js-sel-procedimento option:selected').attr('data-profissionalIniciais');
					let id_tratamento_procedimento = $('select.js-sel-procedimento option:selected').attr('data-id_tratamento_procedimento');
					let profissionalCor = $('select.js-sel-procedimento option:selected').attr('data-profissionalCor');
					let statusEvolucao = $('select.js-sel-procedimento option:selected').attr('data-statusEvolucao');
					let obs = ``;
					
					let dt = new Date();
					let dia = dt.getDate();
					let mes = dt.getMonth();
					let min = dt.getMinutes();
					let hrs = dt.getHours();
					mes++
					mes=mes<=9?`0${mes}`:mes;
					dia=dia<=9?`0${dia}`:dia;
					min=min<=9?`0${min}`:min;
					hrs=hrs<=9?`0${hrs}`:hrs;
					let data = `${dia}/${mes}/${dt.getFullYear()} ${dt.getHours()}:${dt.getMinutes()}`;

					if(id_procedimento.length>0) {
						let item = { id_procedimento, 
										opcao, 
										plano, 
										titulo, 
										profissionalCor, 
										profissionalIniciais, 
										statusEvolucao, 
										autor, 
										id_usuario, 
										data, 
										obs,
										id_profissional,
										id_tratamento_procedimento
									}

						item.avulso=0;
						procedimentos.push(item);
						procedimentosListar();

						$('select.js-sel-procedimento').val('').trigger('chosen:updated');
					} else {
						swal({title: "Erro!", text: 'Selecione o procedimento que deseja adicionar', html:true, type:"error", confirmButtonColor: "#424242"});
					}

				});

				$('#cal-popup .js-obs').keyup(function(){
					let index = $('.js-index').val();
					procedimentos[index].obs=$(this).val();
				});

				$('#cal-popup .js-obs').change (function(){
					procedimentosListar();
				});

				$('#cal-popup').on('change','.js-situacao',function(){
					let index = $('#cal-popup .js-index').val();
					//procedimentos[index].statusEvolucao=$(this).val();
					procedimentos[index].statusEvolucao=$(this).val();
					procedimentosListar();
				});

				$('#cal-popup').on('change','.js-profissional',function(){
					let index = $('#cal-popup .js-index').val();
					procedimentos[index].id_profissional=$(this).val();
					procedimentos[index].profissionalIniciais=$(this).find('option:selected').attr('data-iniciais');
					procedimentos[index].profissionalCor=$(this).find('option:selected').attr('data-iniciaisCor');
					procedimentosListar();
				});

				$('#cal-popup').on('click','.js-btn-excluir',function(){

					swal({
						title: "Atenção",
						text: "Você tem certeza que deseja remover este registro?",
						type: "warning",
						showCancelButton: true,
						confirmButtonColor: "#DD6B55",
						confirmButtonText: "Sim!",
						cancelButtonText: "Não",
						closeOnConfirm: true,
						closeOnCancel: false 
						}, 
						function(isConfirm) {   
							if (isConfirm) {  
							 	let index = $('#cal-popup .js-index').val();
								procedimentos.splice(index,1);
								procedimentosListar();	
							} else {   
								swal.close();   
							}
						}
						);
				})

				$('#modalProcedimento').hide();
				$('.js-btn-addProcedimento').click(function(){
					$.fancybox.open({
 						src: '#modalProcedimento'
					});
				});

				$('select.js-id_procedimento').change(function(){
					let id_procedimento = $(this).val();

					if(id_procedimento.length>0) {
						let id_regiao = $(this).find('option:selected').attr('data-id_regiao');
						let regiao = $(this).find('option:selected').attr('data-regiao');
						let quantitativo = $(this).find('option:selected').attr('data-quantitativo');

						$(`.js-inpt-quantidade`).parent().parent().hide();
						if(quantitativo==1) {
							$(`.js-inpt-quantidade`).parent().parent().show();
						}
						$(`.js-regiao`).hide();
						$(`.js-regiao-${id_regiao}`).show();
						$(`.js-regiao-${id_regiao}`).find('select').chosen({hide_results_on_select:false,allow_single_deselect:true});

						$(`.js-procedimento-btnOk`).show();
						let data = `ajax=planos&id_unidade=${id_unidade}&id_procedimento=${id_procedimento}`;
						$.ajax({
							type:"POST",
							data:data,
							success:function(rtn) {
								if(rtn.success) { 
									$('.js-id_plano option').remove();
									$('.js-id_plano').append(`<option value=""></option>`);
									console.log(rtn.planos);
									if(rtn.planos) {

										rtn.planos.forEach(x=> {
											$('.js-id_plano').append(`<option value="${x.id}" data-valor="${x.valor}">${x.titulo}</option>`);
										});
									}
									$('.js-id_plano').trigger('chosen:updated')
								}
							},
						})
					} else {
						$(`.js-regiao`).hide();
						$(`.js-procedimento-btnOk`).hide();
					}
				});

				$('.js-btn-addAvulso').click(function(){
					let id_procedimento = $(`.js-id_procedimento`).val();
					let id_regiao = $(`.js-id_procedimento option:selected`).attr('data-id_regiao');
					let id_plano = $(`.js-id_plano`).val();
					let valor = $(`.js-id_plano option:selected`).attr('data-valor');
					let titulo = $(`.js-id_procedimento option:selected`).text();
					let plano = $(`.js-id_plano option:selected`).text();
					let quantitativo = $(`.js-id_procedimento option:selected`).attr('data-quantitativo');
					let quantidade = $(`.js-inpt-quantidade`).val();
					let situacao = `aguardandoAprovacao`;
					let statusEvolucao = ``;
					let obs = ``;
					let id_profissional = $('.js-id_profissional').val();
					let profissionalIniciais = $('.js-id_profissional option:selected').attr('data-iniciais');
					let profissionalCor = $('.js-id_profissional option:selected').attr('data-iniciaisCor');
					//alert(quantitativo);

					let erro = ``;
					if(id_procedimento.length==0) erro=`Selecione o Procedimento`;
					//else if(quantitativo==1 && (quantidade.length==0 || eval(quantidade)<=0 || eval(quantidade)>=99)) erro=`Defina a quantidade<br />(mín: 1, máx: 99)`;
					else if(id_regiao>=2 && $(`.js-regiao-${id_regiao}-select`).val().length==0) erro=`Preencha a Região`
					else if(id_plano.length==0) erro=`Selecione o Plano`;


					let dt = new Date();
					let mes = dt.getMonth();
					let dia = dt.getDate();
					mes++
					mes=mes<=9?`0${mes}`:mes;
					dia=dia<=9?`0${dia}`:dia;
					let data = `${dia}/${mes}/${dt.getFullYear()} ${dt.getHours()}:${dt.getMinutes()}`;

					if(erro.length==0) {

						let linhas=1;
						if(id_regiao>=2) {
							linhas = eval($(`.js-regiao-${id_regiao}-select`).val().length);
						}

						let item= {};

						
						let opcoes = ``;
						for(var i=0;i<linhas;i++) {
							item = { titulo, 
										id_procedimento,
										id_regiao,
										id_plano,
										plano,
										quantidade,
										situacao,
										valor,
										id_profissional,
										profissionalIniciais,
										profissionalCor,
										autor,
										id_usuario,
										data };

							item.profissional=0;
							item.desconto=0;
							item.valorCorrigido=valor;
							item.obs='';
							item.avulso=1;

							opcao = id_opcao = ``;
							if(id_regiao>=2) {
								id_opcao = $(`.js-regiao-${id_regiao}-select option:selected:eq(${i})`).val();
								opcao = $(`.js-regiao-${id_regiao}-select option:selected:eq(${i})`).text();
							}
							item.opcao=opcao;
							item.id_opcao=id_opcao;

							procedimentos.push(item);
						}

						$(`.js-id_procedimento`).val('').trigger('chosen:updated');
						$(`.js-id_plano`).val('').trigger('chosen:updated');
						$(`.js-id_profissional`).val('').trigger('chosen:updated');
						$(`.js-inpt-quantidade`).val(1).parent().parent().hide();
						
						$(`.js-regiao-${id_regiao}-select`).val([]).trigger('chosen:updated').parent().parent().hide();;
						$.fancybox.close();
						procedimentosListar();
					} else {
						swal({title: "Erro!", text: erro, html:true, type:"error", confirmButtonColor: "#424242"});
					}
				});
			});
		</script>

		<section class="grid">
			<div class="box">

				<?php
				if(empty($evolucao)) { 
					$exibirEvolucaoNav=1;
					require_once("includes/evolucaoMenu.php");
				} else {
				?>
				<div class="filter">
					<div class="filter-group">
						<div class="filter-button">
							<a href="pg_contatos_pacientes_evolucao.php?id_paciente=<?php echo $paciente->id;?>"><i class="iconify" data-icon="bx-bx-left-arrow-alt"></i></a>
						</div>
					</div>
					<div class="filter-group filter-group_right">
						<div class="filter-button">
							<a href="javascript:;"><i class="iconify" data-icon="bx-bx-trash"></i></a>
							<a href="javascript:;"><i class="iconify" data-icon="bx-bx-printer"></i></a>
							<a href="javascript:;" class="azul js-btn-salvar"><i class="iconify" data-icon="bx-bx-check"></i><span>salvar</span></a>
						</div>
					</div>
				</div>
				<?php
				}
				?>

				<section class="js-evolucao-adicionar" id="evolucao-procedimentos-aprovados">
						
					<form class="form js-form-evolucao" method="post">
						<input type="hidden" name="acao" value="wlib" />
						<input type="hidden" name="id_evolucao" value="<?php echo is_object($evolucao)?$evolucao->id:0;?>" />
						<div class="grid grid_3">

							<fieldset style="grid-column:span 2">
								<legend><?php echo empty($evolucao)?'<span class="badge">1</span> Selecione o procedimento':'Procedimentos';?></legend>
								
								<div class="colunas2">
									<dl>
										<dd>
											<select name="" class="chosen js-sel-procedimento" data-placeholder="Selecione o procedimento...">
												<option value=""></option>
												<?php
												foreach($_procedimentosAprovados as $v) {
													if(isset($_procedimentos[$v->id_procedimento])) {
														$procedimento=$_procedimentos[$v->id_procedimento];
														$profissionalIniciais='';
														$profissionalCor='#ccc';
														if(isset($_profissionais[$v->id_profissional])) {
															$p=$_profissionais[$v->id_profissional];
															$profissionalIniciais=$p->calendario_iniciais;
															$profissionalCor=$p->calendario_cor;

														}
														echo '<option value="'.$v->id.'" data-opcao="'.utf8_encode($v->opcao).'" data-plano="'.utf8_encode($v->plano).'" data-profissionalCor="'.$profissionalCor.'" data-id_profissional="'.$v->id_profissional.'" data-profissionalIniciais="'.$profissionalIniciais.'"  data-statusEvolucao="'.$v->status_evolucao.'" data-titulo="'.utf8_encode($procedimento->titulo).'" data-id_tratamento_procedimento="'.$v->id.'">'.utf8_encode($procedimento->titulo).' - '.utf8_encode($v->opcao).'</option>';
													}
												}
												?>
											</select>
										</dd>
									</dl>
									<dl>
										<dd><button type="button" class="button js-btn-add">Adicionar</button><button type="button" class="button js-btn-addProcedimento">Procedimento Avulso</button></dd>
									</dl>
									<dl>
										<dd></dd>
									</dl>
									<dl>
										<dt>Data da Evolução:</dt>
										<dd><input type="text" name="data_evolucao" class="data datecalendar" value="<?php echo is_object($evolucao)?date('d/m/Y',strtotime($evolucao->data_evolucao)):date('d/m/Y');?>" /></dd>
									</dl>
								</div>

								<textarea name="procedimentos" style="display: none"></textarea>

								<div class="reg js-div-procedimentos" style="margin-top:2rem;"></div>

							</fieldset>

							<fieldset>
								<legend><?php echo empty($evolucao)?'<span class="badge">2</span> Preencha o histórico':'Histórico';?></legend>

								<dl style="height:100%;">
									<dd style="height:100%;"><textarea name="obs" style="height:100%;" class="noupper"><?php echo is_object($evolucao)?utf8_encode($evolucao->obs):'';?></textarea></dd>
								</dl>
							</fieldset>


						</div>
					</form>

					<section id="modalProcedimento" class="modal" style="width:950px;">		
						<header class="modal-conteudo">
							<form method="post" class="form js-form-agendamento">
								<fieldset>
									<legend>Adicionar Procedimento</legend>
									
									<dl class="dl3">
										<dt>Procedimento</dt>
										<dd>
											<select class="js-id_procedimento chosen">
												<option value=""></option>
												<?php
												foreach($_procedimentosAvulsos as $p) {
													echo '<option value="'.$p->id.'" data-id_regiao="'.$p->id_regiao.'" data-regiao="'.(isset($_regioes[$p->id_regiao])?utf8_encode($_regioes[$p->id_regiao]->titulo):"-").'" data-quantitativo="'.($p->quantitativo==1?1:0).'">'.utf8_encode($p->titulo).'</option>';
												}
												?>
											</select>
										</dd>
									</dl>
									<dl style="display: none">
										<dt>Qtd.</dt>
										<dd><input type="number" class="js-inpt-quantidade" value="1" /></dd>
									</dl>
									<dl class="js-regiao-2 js-regiao dl2" style="display: none;">
										<dt>Arcada(s)</dt>
										<dd>
											<select class="js-regiao-2-select" multiple>
												<option value=""></option>
												<?php
												if(isset($_regioesOpcoes[2])) {
													foreach($_regioesOpcoes[2] as $o) {
														echo '<option value="'.$o->id.'" data-titulo="'.utf8_encode($o->titulo).'">'.utf8_encode($o->titulo).'</option>';
													}
												}
												?>
											</select>
										</dd>
									</dl>
									<dl class="js-regiao-3 js-regiao dl2" style="display: none">
										<dt>Quadrante(s)</dt>
										<dd>
											<select class="js-regiao-3-select" multiple>
												<option value=""></option>
												<?php
												if(isset($_regioesOpcoes[3])) {
													foreach($_regioesOpcoes[3] as $o) {
														echo '<option value="'.$o->id.'" data-titulo="'.utf8_encode($o->titulo).'">'.utf8_encode($o->titulo).'</option>';
													}
												}
												?>
											</select>
										</dd>
									</dl>
									<dl class="js-regiao-4 js-regiao dl2" style="display: none">
										<dt>Dente(s)</dt>
										<dd>
											<select class="js-regiao-4-select" multiple>
												<option value=""></option>
												<?php
												if(isset($_regioesOpcoes[4])) {
													foreach($_regioesOpcoes[4] as $o) {
														echo '<option value="'.$o->id.'" data-titulo="'.utf8_encode($o->titulo).'">'.utf8_encode($o->titulo).'</option>';
													}
												}
												?>
											</select>
										</dd>
									</dl>

									<div class="colunas5">
										
										<dl class="dl2">
											<dt>Plano</dt>
											<dd>
												<select class="js-id_plano chosen">
												</select>
											</dd>
										</dl>


										<dl class="dl2">
											<dt>Profissional</dt>
											<dd>
												<select class="js-id_profissional chosen">
													<option value="" data-iniciais="" data-iniciaisCor=""></option>
													<?php
													foreach($_profissionais as $x) {
														$aux=explode(" ",$x->nome);
														$aux[0]=strtoupper($aux[0]);
														$iniciais='';
														if($aux[0] =="DR" or $aux[0]=="DR." or $aux[0]=="DRA" or $aux[0]=="DRA.") {
															$iniciais=strtoupper(substr($aux[1],0,1));
															if(isset($aux[2])) $iniciais.=strtoupper(substr($aux[2],0,1));
														} else {
															$iniciais=strtoupper(substr($aux[0],0,1));
															if(isset($aux[1])) $iniciais.=strtoupper(substr($aux[1],0,1));
														}
														echo '<option value="'.$x->id.'" data-iniciais="'.$iniciais.'" data-iniciaisCor="'.$x->calendario_cor.'">'.utf8_encode($x->nome).'</option>';
													}
													?>
												</select>
											</dd>
										</dl>

										<dl>
											<dt>&nbsp;</dt>
											<dd>
												<a href="javascript:;" class="button js-btn-addAvulso"><i class="iconify" data-icon="ic-baseline-add"></i> Adicionar</a>
											</dd>
										</dl>
									</div>
							
								</fieldset>

							</form>
						</header>
					</section> 

				</section>

			</div>				
		</section>
			
	</section>
	<section id="cal-popup" class="cal-popup cal-popup_paciente cal-popup_top cal-popup_alt" style="left:703px; top:338px; margin-left:303px;display: none">
		<a href="javascript:;" class="cal-popup__fechar js-btn-fechar"><i class="iconify" data-icon="mdi-close"></i></a>
		<section class="paciente-info">
			<header class="paciente-info-header">
				<section class="paciente-info-header__inner1">
					<h1 class="js-titulo"></h1>
					<p style="color:var(--cinza4);"><span style="color:var(--cinza4);" class="js-opcao"></span> - <span class="js-plano"></span> </p>
					
				</section>
			</header>
			<input type="hidden" class="js-index" />

			<div class="abasPopover">
				<a href="javascript:;" onclick="$(this).parent().parent().find('a').removeClass('active');$(this).parent().parent().find('.js-grid').hide();$(this).parent().parent().find('.js-grid-info').show();$(this).addClass('active');" class="active">Informações</a>
				<?php /*<a href="javascript:;" onclick="$(this).parent().parent().find('a').removeClass('active');$(this).parent().parent().find('.js-grid').hide();$(this).parent().parent().find('.js-grid-valor').show();$(this).addClass('active');">Valor</a>*/?>
				<a href="javascript:;" onclick="$(this).parent().parent().find('a').removeClass('active');$(this).parent().parent().find('.js-grid').hide();$(this).parent().parent().find('.js-grid-obs').show();$(this).addClass('active');">Observações</a>
			</div>

			<div class="paciente-info-grid js-grid js-grid-info" style="font-size: 12px;">		
				
				<dl style="grid-column:span 2;">
					<dt>Profissional</dt>
					<dd><?php echo $selectProfissional;?></dd>
				</dl>

				

				<dl style="grid-column:span ;">
					<dd><span class="iconify" data-icon="bx:bx-user-circle" data-inline="true"></span> <span class="js-autor"></span></dd>
				</dl>
				<dl style="grid-column:span ;">
					<dd><span class="iconify" data-icon="bi:clock" data-inline="true"></span> <span class="js-autor-data"></span></dd>
				</dl>
			</div>
			<script type="text/javascript">
				$(function(){

					$('input.money').maskMoney({symbol:'', allowZero:true, showSymbol:true, thousands:'.', decimal:',', symbolStay: true});
					

				})
			</script>

			<div class="paciente-info-grid js-grid js-grid-obs" style="display:none;font-size:12px;color:#666">	
				<dl style="grid-column:span 2;">
					<dd>
						<textarea style="height:100px" class="js-obs"></textarea>
					</dd>
				</dl>
			</div>
			<div class="paciente-info-opcoes">
				<?php //echo $selectSituacaoOptions;?>
				<select class="js-situacao" style="display: none;"></select>
				<a href="javascript:;" class="js-btn-excluir button button__sec">excluir</a>
			</div>
		</section>
	</section>
		
<?php
include "includes/footer.php";
?>