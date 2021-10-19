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
		} else if($_POST['ajax']=="historico") {

			$_usuarios=array();
			$sql->consult($_p."colaboradores","id,nome","order by nome asc");
			while($x=mysqli_fetch_object($sql->mysqry)) {
				$_usuarios[$x->id]=$x;
			}


			$procedimentoAEvoluir='';
			if(isset($_POST['id_procedimento_aevoluir']) and is_numeric($_POST['id_procedimento_aevoluir'])) {
				$sql->consult($_p."pacientes_tratamentos_procedimentos_evolucao","*","where id='".$_POST['id_procedimento_aevoluir']."'");
				if($sql->rows) {
					$procedimentoAEvoluir=mysqli_fetch_object($sql->mysqry);
				}


				if(is_object($procedimentoAEvoluir)) {
					$historico=array();

					$sql->consult($_p."pacientes_tratamentos_procedimentos_evolucao_historico","*","where id_procedimento_aevoluir=$procedimentoAEvoluir->id and lixo=0");
					if($sql->rows) {
						while($x=mysqli_fetch_object($sql->mysqry)) {
							$historico[]=array('data'=>date('d/m/Y',strtotime($x->data)),
												'id_usuario'=>$x->id_usuario,
												'usuario'=>isset($_usuarios[$x->id_usuario])?utf8_encode($_usuarios[$x->id_usuario]->nome):'Desconhecido',
												'obs'=>utf8_encode($x->obs),
												'id'=>$x->id);
						}
					}

					$rtn=array('success'=>true,'historico'=>$historico);
				} else {
					$rtn=array('success'=>false,'error'=>'Procedimento a evoluir não encontrado!');
				}

			}
		} else if($_POST['ajax']=="persistirAoEditar") {
			$evolucaoProcedimento='';
			if(isset($_POST['procedimento']) and !empty($_POST['procedimento'])) {
				$procedimentoJSON=json_decode($_POST['procedimento']);
				if(is_object($procedimentoJSON)) {
					if(isset($procedimentoJSON->id) and is_numeric($procedimentoJSON->id)) {
						$sql->consult($_p."pacientes_evolucoes_procedimentos","*","where id=$procedimentoJSON->id");
						if($sql->rows) {
							$evolucaoProcedimento=mysqli_fetch_object($sql->mysqry);
						}
					}
				}
			}

			if(is_object($evolucaoProcedimento)) {
				$vSQL="id_profissional='".addslashes($procedimentoJSON->id_profissional)."',
						status='".addslashes($procedimentoJSON->statusEvolucao)."'";


				$sql->update($_p."pacientes_evolucoes_procedimentos",$vSQL,"where id=$evolucaoProcedimento->id");
				

				// atualiza status de Tratamento / Procedimento
				if($procedimentoJSON->statusEvolucao=="iniciar" or 
						$procedimentoJSON->statusEvolucao=="iniciado" or 
						$procedimentoJSON->statusEvolucao=="finalizado" or 
						$procedimentoJSON->statusEvolucao=="cancelado") {

					$sql->update($_p."pacientes_tratamentos_procedimentos_evolucao","status_evolucao='".$procedimentoJSON->statusEvolucao."'","where id='".$evolucaoProcedimento->id_procedimento_aevoluir."'");
				}

				// atualiza historico

				if(!empty($procedimentoJSON->historico)) {
					$h=$procedimentoJSON->historico[0];
					list($data,$hora)=explode(" ",$h->data);
					list($d,$m,$a)=explode("/",$data);
					$dt="$a-$m-$d $hora";
					$vSQLHistorico="id_usuario='".addslashes($h->id_usuario)."',
									usuario='".addslashes(utf8_decode($h->usuario))."',
									obs='".addslashes(utf8_decode($h->obs))."',
									id_tratamento_procedimento=$evolucaoProcedimento->id_tratamento_procedimento,
									id_procedimento_aevoluir=$evolucaoProcedimento->id_procedimento_aevoluir,
									id_evolucao='".addslashes($evolucaoProcedimento->id_evolucao)."',
									data='$dt'";
					$sql->add($_p."pacientes_tratamentos_procedimentos_evolucao_historico",$vSQLHistorico);
					
					$_usuarios=array();
					$sql->consult($_p."colaboradores","id,nome","");
					while($x=mysqli_fetch_object($sql->mysqry)) {
						$_usuarios[$x->id]=$x;
					}

					$historico=array();
					$sql->consult($_p."pacientes_tratamentos_procedimentos_evolucao_historico","*","where id_procedimento_aevoluir=$evolucaoProcedimento->id_procedimento_aevoluir and lixo=0 order by data asc");
					while($x=mysqli_fetch_object($sql->mysqry)) {
						$historico[]=array('data'=>date('d/m/Y H:i',strtotime($x->data)),
														'id_usuario'=>$x->id_usuario,
														'usuario'=>isset($_usuarios[$x->id_usuario])?utf8_encode($_usuarios[$x->id_usuario]->nome):'Desconhecido',
														'obs'=>utf8_encode($x->obs),
														'id'=>$x->id);
					}
					
				}

				$rtn=array('success'=>true);

				if(isset($historico) and is_array($historico)) $rtn['historico']=$historico;

			} else {
				$rtn=array('success'=>false,'error'=>'Procedimento não encontrado');
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
	$sql->consult($_p."colaboradores","id,nome","order by nome asc");
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
	
											
		$selectProfissional.='<option value="'.$p->id.'" data-iniciais="'.$p->calendario_iniciais.'" data-iniciaisCor="'.$p->calendario_cor.'">'.utf8_encode($p->nome).'</option>';
	}
	$selectProfissional.='</select>';


	$tratamentosIds=array(-1);
	$sql->consult($_p."pacientes_tratamentos","*","where id_paciente=$paciente->id and  status='APROVADO' and lixo=0");
	while($x=mysqli_fetch_object($sql->mysqry)) $tratamentosIds[]=$x->id;

	$procedimentosIds=array(-1);
	$_procedimentosAprovadosASerEvoluido=array();
	$tratamentosProcedimentosIds=array(0);
	$where="where lixo=0 and situacao='aprovado' and id_tratamento IN (".implode(",",$tratamentosIds).")";

	//die();
	$_procedimentosDeTratamentosAprovados=array();
	$sql->consult($_p."pacientes_tratamentos_procedimentos","*",$where);
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$procedimentosIds[]=$x->id_procedimento;
		$tratamentosProcedimentosIds[]=$x->id;
		$_procedimentosDeTratamentosAprovados[$x->id]=$x;
	}


	$sql->consult($_p."pacientes_tratamentos_procedimentos_evolucao","*","where id_tratamento_procedimento IN (".implode(",",$tratamentosProcedimentosIds).") and status_evolucao NOT IN ('cancelado','finalizado') and lixo=0");

	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_procedimentosAprovadosASerEvoluido[$x->id]=$x;
	}

	$_procedimentos=array();
	$sql->consult($_p."parametros_procedimentos","id,titulo","where  lixo=0");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_procedimentos[$x->id]=$x;
	}


	$evolucao='';
	$evolucaoProcedimentos=array();
	$historicoGeral=array();
	if(isset($_GET['edita']) and is_numeric($_GET['edita'])) {	
		$sql->consult($_p."pacientes_evolucoes","*","where id='".$_GET['edita']."' and lixo=0");
		if($sql->rows) {
			$evolucao=mysqli_fetch_object($sql->mysqry);

			$sql->consult($_p."pacientes_evolucoes_procedimentos","*","where id_evolucao=$evolucao->id and lixo=0");

			if($sql->rows) {
				$registros=array();
				$procedimentosAEvoluirIds=array(-1);
				while($x=mysqli_fetch_object($sql->mysqry)) {
					$registros[]=$x;
					if($x->id_procedimento_aevoluir>0) {
						$procedimentosAEvoluirIds[]=$x->id_procedimento_aevoluir;
					}
					$procedimentosIds[$x->id_procedimento]=$x->id_procedimento;
				}


				// Procedimentos a Evoluir
				$tratamentosProdecimentosIds=array(0); // procedimentos aprovados (pacientes_tratamentos_procedimentos)
				$_procedimentosAEvoluir=array();
				$sql->consult($_p."pacientes_tratamentos_procedimentos_evolucao","*","where id IN (".implode(',',$procedimentosAEvoluirIds).") and lixo=0");
				while($x=mysqli_fetch_object($sql->mysqry)) {
					$tratamentosProdecimentosIds[]=$x->id;
					$_procedimentosAEvoluir[$x->id]=$x;
				}

				// Procedimentos Aprovados
				$_tratamentosProcedimentosAprovados=array(-1);
				$where="where id IN (".implode(",",$tratamentosProdecimentosIds).") and id_paciente=$paciente->id and lixo=0";
				$sql->consult($_p."pacientes_tratamentos_procedimentos","*",$where);
				while($x=mysqli_fetch_object($sql->mysqry)) $_tratamentosProcedimentosAprovados[$x->id]=$x;


				// Historico dos procedimentos que foram evoluidos
				$_procedimentoEvoluidoHistorico=array();
				$sql->consult($_p."pacientes_tratamentos_procedimentos_evolucao_historico","*","where id_procedimento_aevoluir IN (".implode(",",$procedimentosAEvoluirIds).") and lixo=0 order by data asc");
				while($x=mysqli_fetch_object($sql->mysqry)) {
					$_procedimentoEvoluidoHistorico[$x->id_procedimento_aevoluir][]=array('data'=>date('d/m/Y H:i',strtotime($x->data)),
																							'id_usuario'=>$x->id_usuario,
																							'usuario'=>isset($_usuarios[$x->id_usuario])?utf8_encode($_usuarios[$x->id_usuario]->nome):'Desconhecido',
																							'obs'=>utf8_encode($x->obs),
																							'id'=>$x->id);
				}
				// Procedimentos que tiveram evolucao
				foreach($registros as $x) {
					if($x->id_procedimento_aevoluir>0 and isset($_procedimentosAEvoluir[$x->id_procedimento_aevoluir])) {
						$procedimentoAEvoluir=$_procedimentosAEvoluir[$x->id_procedimento_aevoluir];

						if(isset($_procedimentosDeTratamentosAprovados[$procedimentoAEvoluir->id_tratamento_procedimento])) {
							$procedimentoAprovado=$_procedimentosDeTratamentosAprovados[$procedimentoAEvoluir->id_tratamento_procedimento];

							if(isset($_procedimentos[$procedimentoAprovado->id_procedimento])) {
								$procedimento=$_procedimentos[$procedimentoAprovado->id_procedimento];
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
									$autor=($p->nome);
								}



								$historico=isset($_procedimentoEvoluidoHistorico[$procedimentoAEvoluir->id])?$_procedimentoEvoluidoHistorico[$procedimentoAEvoluir->id]:array();

								$evolucaoProcedimentos[]=array('id'=>$x->id,
																'autor'=>utf8_encode($autor),
																'data'=>date('d/m/Y',strtotime($x->data)),
																'id_usuario'=>$evolucao->id_usuario,
																'id_tratamento_procedimento'=>$x->id,
																'id_procedimento_aevoluir'=>$procedimentoAEvoluir->id,
																'id_procedimento'=>$x->id_procedimento,
																'id_profissional'=>$x->id_profissional,
																'obs'=>utf8_encode($x->obs),
																'opcao'=>utf8_encode($x->opcao),
																'plano'=>isset($_planos[$x->id_plano])?utf8_encode($_planos[$x->id_plano]->titulo):'-',
																'profissionalCor'=>$profissionalCor,
																'profissionalIniciais'=>$profissionalIniciais,
																'statusEvolucao'=>$x->status,
																'historico'=>$historico,
															 	'titulo'=>utf8_encode($procedimento->titulo),
															 	'numero'=>$procedimentoAEvoluir->numero,
															 	'numeroTotal'=>$procedimentoAEvoluir->numeroTotal,

															 	'avulso'=> 0);
							}
						}
					} else if(isset($_procedimentos[$x->id_procedimento])) {
						$procedimento=$_procedimentos[$x->id_procedimento];
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

						//echo $p->nome." ".$profissionalIniciais."->".$profissionalCor.'<BR>';


						$evolucaoProcedimentos[]=array('id'=>$x->id,
														'autor'=>$autor,
														'data'=>date('d/m/Y',strtotime($x->data)),
														'id_usuario'=>$evolucao->id_usuario,
														'id_procedimento'=>$x->id_procedimento,
														'id_profissional'=>$x->id_profissional,
														'obs'=>utf8_encode($x->obs),
														'opcao'=>utf8_encode($x->opcao),
														'id_opcao'=>utf8_encode($x->id_opcao),
														'id_plano'=>utf8_encode($x->id_plano),
														'plano'=>isset($_planos[$x->id_plano])?utf8_encode($_planos[$x->id_plano]->titulo):'-',
														'profissionalCor'=>$profissionalCor,
														'profissionalIniciais'=>$profissionalIniciais,
														'statusEvolucao'=>$x->status,
														'historico'=>array(),
													 	'titulo'=>utf8_encode($procedimento->titulo),
													 	'avulso'=>1);
					}
				}

				// historico geral da evolucao realizada
				$sql->consult($_p."pacientes_tratamentos_procedimentos_evolucao_historico","*","where id_evolucao=$evolucao->id and id_procedimento_aevoluir=0 and lixo=0 order by data asc");
				if($sql->rows) {
					while($x=mysqli_fetch_object($sql->mysqry)) {
						$historicoGeral[]=array('id'=>$x->id,
												'obs'=>str_replace("\n","",utf8_encode($x->obs)),
												'data'=>date('d/m/Y H:i',strtotime($x->data)),
												'id_usuario'=>$x->id_usuario,
												'usuario'=>isset($_usuarios[$x->id_usuario])?utf8_encode($_usuarios[$x->id_usuario]->nome):'Desconhecido');
					}
				}
			}
		} else {
			$jsc->jAlert("Procedimento Aprovado não encontrado!","erro","document.location.href='pg_contatos_pacientes_evolucao.php?id_paciente='".$paciente->id."'");
			die();
		}
	}


	
	if(isset($_POST['acao'])) {


		//var_dump($_POST);die();

		if(isset($_POST['procedimentos']) and !empty($_POST['procedimentos'])) {
			$procedimentosJSON = json_decode($_POST['procedimentos']);
			$historicoGeral = json_decode($_POST['historicoGeral']);

			$procedimentosEvoluidos=array();
			$procedimentosAvulsos=array();
			$erro='';


			foreach($procedimentosJSON as $v) {

				if($v->avulso==0) {

					// consulta procedimento que deve ser evoluido
					$sql->consult($_p."pacientes_tratamentos_procedimentos_evolucao","*","where id=$v->id_procedimento_aevoluir and 
																								numero='$v->numero' and 
																								numeroTotal='$v->numeroTotal' and 
																								lixo=0");
					if($sql->rows) {
						$procedimentoAEvoluir=mysqli_fetch_object($sql->mysqry);

						// consulta procedimento que foi aprovado
						$sql->consult($_p."pacientes_tratamentos_procedimentos","*","where id=$procedimentoAEvoluir->id_tratamento_procedimento");
						if($sql->rows) {
							$procedimentoAprovado=mysqli_fetch_object($sql->mysqry);
							$procedimentosEvoluidos[]=array('procedimentoAEvoluir'=>$procedimentoAEvoluir,
															'procedimentoAprovado'=>$procedimentoAprovado,
															'procedimentoEvolucao'=>$v,
															'id_procedimento_evolucao'=>isset($v->id)?$v->id:0);


							
						}
					} else {
						$erro='Procedimento '.$v->titulo.' não foi encontrado para baixa de evolução!';
					}
				} else {
					$procedimentosAvulsos[]=$v;
				}
			}

			


			if(empty($erro)) {

				if(count($procedimentosEvoluidos)>0 or count($procedimentosAvulsos)>0) {


					if(is_object($evolucao)) {
						$sql->update($_p."pacientes_evolucoes","data_evolucao='".addslashes(invDate($_POST['data_evolucao']))."'","where id=$evolucao->id");
						$id_evolucao=$evolucao->id;
					} else {
						// id_tipo = 2 -> Procedimentos Aprovados
						$sql->consult($_p."pacientes_evolucoes","*","WHERE data > NOW() - INTERVAL 1 MINUTE and 
																								id_paciente=$paciente->id and
																								id_tipo=2 and  
																								id_usuario=$usr->id");	
						if($sql->rows) {
							$e=mysqli_fetch_object($sql->mysqry);
							$sql->update($_p."pacientes_evolucoes","data_evolucao='".addslashes(invDate($_POST['data_evolucao']))."'","where id=$e->id");
							$id_evolucao=$e->id;
						} else {
							$sql->add($_p."pacientes_evolucoes","data=now(),
																	id_tipo=2,
																	id_paciente=$paciente->id,
																	id_usuario=$usr->id,
																	data_evolucao='".addslashes(invDate($_POST['data_evolucao']))."'");
							$id_evolucao=$sql->ulid;
						}
					}

					// cadastra historico geral da evolucao
					foreach($historicoGeral as $h) {
						if(isset($h->id) and is_numeric($h->id) and $h->id>0) continue;
						list($data,$hora)=explode(" ",$h->data);
						list($d,$m,$a)=explode("/",$data);
						$dt="$a-$m-$d $hora";
						$vSQLHistorico="id_usuario='".addslashes($h->id_usuario)."',
										usuario='".addslashes(utf8_decode($h->usuario))."',
										obs='".addslashes(utf8_decode($h->obs))."',
										id_tratamento_procedimento=0,
										id_procedimento_aevoluir=0,
										id_evolucao='".addslashes($id_evolucao)."',
										data='$dt'";

						$sql->add($_p."pacientes_tratamentos_procedimentos_evolucao_historico",$vSQLHistorico);
					}

					// Procedimentos Avulsos
					foreach ($procedimentosAvulsos as $obj) {
						$vSQLProc="data=now(),
									lixo=0,
									id_paciente=$paciente->id,
									id_evolucao=$id_evolucao,
									opcao='".addslashes(utf8_decode($obj->opcao))."',
									id_opcao='".addslashes($obj->id_opcao)."',
									id_plano='".addslashes($obj->id_plano)."',
									plano='".addslashes($obj->plano)."',
									id_procedimento='".addslashes($obj->id_procedimento)."',
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
					

					// Procedimentos Evoluidos
					foreach($procedimentosEvoluidos as $obj) {
						$obj=(object)$obj;
						$procedimentoAEvoluir=$obj->procedimentoAEvoluir; // pacientes_tratamentos_procedimentos_evolucao
						$procedimentoAprovado=$obj->procedimentoAprovado; // pacientes_tratamentos_procedimentos
						$procedimentoEvolucao=$obj->procedimentoEvolucao; // procedimentoEvolucao
						$id_procedimento_evolucao=isset($obj->id_procedimento_evolucao)?$obj->id_procedimento_evolucao:0;

						

						// Persiste historico dos procedimentos evoluidos
						if(isset($procedimentoEvolucao->historico) and is_array($procedimentoEvolucao->historico) and count($procedimentoEvolucao->historico)>0) {
							
							foreach($procedimentoEvolucao->historico as $h) {
								if(isset($h->id) and is_numeric($h->id) and $h->id>0) continue;
								list($data,$hora)=explode(" ",$h->data);

								list($d,$m,$a)=explode("/",$data);
								$dt="$a-$m-$d $hora";
								$vSQLHistorico="id_usuario='".addslashes($h->id_usuario)."',
												usuario='".addslashes(utf8_decode($h->usuario))."',
												obs='".addslashes(utf8_decode($h->obs))."',
												id_tratamento_procedimento=$procedimentoAprovado->id,
												id_procedimento_aevoluir=$procedimentoAEvoluir->id,
												id_evolucao='".addslashes($id_evolucao)."',
												data='$dt'";

								$sql->add($_p."pacientes_tratamentos_procedimentos_evolucao_historico",$vSQLHistorico);
							}
						}



						$evProc=''; // pacientes_evolucao_procedimentos
						if($id_procedimento_evolucao>0) {
							$sql->consult($_p."pacientes_evolucoes_procedimentos","*","where id=$id_procedimento_evolucao and id_paciente=$paciente->id and lixo=0");
							if($sql->rows) {
								$evProc=mysqli_fetch_object($sql->mysqry);
							}
						}

						$vSQLProc="data=now(),
									id_paciente=$paciente->id,
									id_evolucao=$id_evolucao,
									id_tratamento_procedimento='".addslashes($procedimentoAprovado->id)."',
									id_procedimento_aevoluir='".addslashes($procedimentoAEvoluir->id)."',
									id_procedimento='".addslashes($procedimentoAprovado->id_procedimento)."',
									id_tratamento='".addslashes($procedimentoAprovado->id_tratamento)."',
									id_profissional='".addslashes($procedimentoEvolucao->id_profissional)."',
									status='".addslashes($procedimentoEvolucao->statusEvolucao)."',
									id_plano='$procedimentoAprovado->id_plano',
									id_opcao='$procedimentoAprovado->id_opcao',
									opcao='$procedimentoAprovado->opcao'";

						
						if(empty($evProc)) {
							$sql->consult($_p."pacientes_evolucoes_procedimentos","*","WHERE data > NOW() - INTERVAL 1 MINUTE and 
																								id_paciente=$paciente->id and 
																								id_evolucao=$id_evolucao and 
																								id_procedimento=$procedimentoAprovado->id_procedimento and 
																								id_opcao=$procedimentoAprovado->id_opcao and 
																								id_tratamento='".addslashes($procedimentoAprovado->id_tratamento)."'");	
							 //echo $vSQLProc." ----> ".($sql->rows)."<BR>";;
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
						if($procedimentoEvolucao->statusEvolucao=="iniciar" or 
								$procedimentoEvolucao->statusEvolucao=="iniciado" or 
								$procedimentoEvolucao->statusEvolucao=="finalizado" or 
								$procedimentoEvolucao->statusEvolucao=="cancelado") {

							$sql->update($_p."pacientes_tratamentos_procedimentos_evolucao","status_evolucao='".$procedimentoEvolucao->statusEvolucao."'","where id='".$procedimentoAEvoluir->id."'");
						}
					}	

					$bi = new BI(array('prefixo'=>$_p));
					$bi->classificaTodos();

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
		require_once("includes/evolucaoProcedimentosJs.php");
		?>		

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
								
								<div class="colunas4">

									<dl>
										<dt>Data da Evolução:</dt>
										<dd><input type="text" name="data_evolucao" class="data datecalendar" value="<?php echo is_object($evolucao)?date('d/m/Y',strtotime($evolucao->data_evolucao)):date('d/m/Y');?>" /></dd>
									</dl>
									<dl class="dl3">
										<dd>
											<select name="" class="chosen2 js-sel-procedimento" data-placeholder="Selecione o procedimento..." multiple>
												<option value=""></option>
							<?php
							foreach($_procedimentosAprovadosASerEvoluido as $v) {
								$disabled='';
								if(isset($procedimentosAEvoluirIds) and in_array($v->id,$procedimentosAEvoluirIds)) $disabled=" disabled";;
								if(isset($_procedimentos[$v->id_procedimento])) {
									$procedimento=$_procedimentos[$v->id_procedimento];
									$profissionalIniciais='';
									$profissionalCor='#ccc';
									if(isset($_profissionais[$v->id_profissional])) {
										$p=$_profissionais[$v->id_profissional];
										$profissionalIniciais=$p->calendario_iniciais;
										$profissionalCor=$p->calendario_cor;

									}
									$complemento='';
									if($v->numeroTotal>1) $complemento.=' - '.utf8_encode($v->numero."/".$v->numeroTotal);

									//	id_tratamento_procedimento => Procedimento de tratamento aprovado
									if(isset($_procedimentosDeTratamentosAprovados[$v->id_tratamento_procedimento])) {
										$procedimentoAprovado=$_procedimentosDeTratamentosAprovados[$v->id_tratamento_procedimento];
										if(!empty($procedimentoAprovado->opcao)) $complemento.=" - ".utf8_encode($procedimentoAprovado->opcao)
											;
										echo '<option value="'.$v->id.'" 
														data-id_procedimento="'.$v->id_procedimento.'" 
														data-numero="'.$v->numero.'" 
														data-numeroTotal="'.$v->numeroTotal.'" 
														data-opcao="'.utf8_encode($procedimentoAprovado->opcao).'" 
														data-plano="'.utf8_encode($procedimentoAprovado->plano).'" 
														data-profissionalCor="'.$profissionalCor.'" 
														data-id_profissional="'.$v->id_profissional.'" 
														data-profissionalIniciais="'.$profissionalIniciais.'"  
														data-statusEvolucao="'.$v->status_evolucao.'" 
														data-titulo="'.utf8_encode($procedimento->titulo).'" 
														data-id_tratamento_procedimento="'.$procedimentoAprovado->id.'"'.$disabled.'>'.utf8_encode($procedimento->titulo).$complemento.'</option>';
									}
								}
							}
							?>
											</select>
										</dd>
									</dl>
									<dl>
										<dd>
											<button type="button" class="button js-btn-add">Adicionar</button>
											<button type="button" class="button js-btn-addProcedimento">Avulso</button>
										</dd>
									</dl>
									<dl>
										<dd></dd>
									</dl>
								</div>

								<textarea name="procedimentos" style="display:none;"></textarea>

								<div class="reg js-div-procedimentos" style="margin-top:2rem;"></div>

							</fieldset>

							<fieldset>
								<legend><?php echo empty($evolucao)?'<span class="badge">2</span> Preencha o histórico':'Histórico';?></legend>

								<div class="hist-lista" id="js-todosHistoricos">
									<?php /*<div class="hist-lista-item hist-lista-item_lab">
										<h1>Laboratório em 12/07/2021</h1>
										<p>Estamos demorando um pouco mais que o habitual. Desculpe a demora e aguarde um pouco mais</p>
									</div>
									<div class="hist-lista-item hist-lista-item_lab">
										<h1>Laboratório em 11/07/2021</h1>
										<h2>status alterado para <strong style="background:limegreen;">Aceito</strong></h2>
									</div>
									<div class="hist-lista-item">
										<h1>Kroner Costa em 11/07/2021</h1>
										<p>Documento enviado!</p>
										<h2>status alterado para <strong style="background:blue">Em aberto</strong></h2>
									</div>
									<div class="hist-lista-item hist-lista-item_lab">
										<h1>Laboratório em 10/07/2021</h1>
										<p>Falta documento sobre as cores da faceta</p>
										<h2>status alterado para <strong style="background:red;">OS Recusada</strong></h2>
									</div>
									<div class="hist-lista-item">
										<h1>Kroner Costa em 10/07/2021</h1>
										<h2><strong style="background:#000;">OS Criada</strong></h2>
									</div>*/?>
								</div>

								<dl style="height:100%;">
									<dd style="height:100%;">
										<textarea class="js-historicoGeral" style="height:80px;" class="noupper"></textarea>
									</dd>
								</dl>
								<dl>
									<dd>
										<a href="javascript:;" class="button button__full js-obsGeral-add"><i class="iconify" data-icon="ic-baseline-add"></i> Adicionar</a>
									</dd>
								</dl>	

								<textarea name="historicoGeral" style="display:none"></textarea>
							</fieldset>


						</div>
					</form>

					<section id="modalProcedimento" class="modal" style="width:950px;height:auto;padding-top:20px;f">		
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
												foreach($_procedimentos as $p) {
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
		
<?php
include "includes/footer.php";
?>