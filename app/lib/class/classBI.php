<?php

	class BI {


		function __construct($attr) {
			if(isset($attr['prefixo'])) $this->prefixo=$attr['prefixo'];
		}

		function classificaTodos() {
			$sql = new Mysql();
			$_p=$this->prefixo;

			

			$_planosDeTratamento=array();
			$_tratamentosPacientes=array();
			$_tratamentosAprovadosPacientes=array();
			$sql->consult($_p."pacientes_tratamentos","id,status,id_paciente","where lixo=0");
			while($x=mysqli_fetch_object($sql->mysqry)) {
				$_planosDeTratamento[$x->id_paciente][]=$x->id;
				$_tratamentosPacientes[$x->id_paciente][]=$x;
				if($x->status=="APROVADO") $_tratamentosAprovadosPacientes[$x->id_paciente][]=$x;
			}


			$_evolucoes=array();
			$sql->consult($_p."pacientes_tratamentos_procedimentos","id,id_paciente,id_tratamento,status_evolucao","where data > NOW() - INTERVAL 24 MONTH and situacao='aprovado' and id_tratamento>0 and lixo=0");
			while($x=mysqli_fetch_object($sql->mysqry)) {
				$_evolucoes[$x->id_paciente][$x->id_tratamento][]=$x;
			}

			// Agendamentos
			$_agendas=array();
			$_ultimoAgendamento=array();
			$sql->consult($_p."agenda","id,data,id_paciente,id_status,agenda_data","where agenda_data > NOW() - INTERVAL 24 MONTH and lixo=0 order by agenda_data desc");
			while($x=mysqli_fetch_object($sql->mysqry)) {
				$_agendas[$x->id_paciente][]=$x;
				if(!isset($_ultimoAgendamento[$x->id_paciente])) $_ultimoAgendamento[$x->id_paciente]=$x;
			}


			$_pacientes=array();
			$pacientesIds=array();
			$sql->consult($_p."pacientes","*","where lixo=0");
			echo $sql->rows." pacientes <br >";
			while($x=mysqli_fetch_object($sql->mysqry)) {

				$diasDeCadastro = floor((strtotime(date('Y-m-d H:i:s')) - strtotime(date($x->data)))/(60*60*24));

				$tratamentos = isset($_planosDeTratamento[$x->id])?count($_planosDeTratamento[$x->id]):0;
				$agendamentos = isset($_agendas[$x->id])?count($_agendas[$x->id]):0;
				$ultimoAgendamento =  '';
				if(isset($_agendas[$x->id])) {
					$ultimoAgendamento=$_agendas[$x->id][0]->data;
				}

				$tratamentosStatus=array();

				if(isset($_tratamentosPacientes[$x->id])) {

					foreach($_tratamentosPacientes[$x->id] as $p) {
						if(!isset($tratamentosStatus[$p->status])) {
							$tratamentosStatus[$p->status]=0;
						}

						$tratamentosStatus[$p->status]++;
						
					}
				}

				$agendamentos=isset($_agenda[$p->id])?count($_agenda[$p->id]):0;

				$_pacientes[]=array('id'=>$x->id,
									'codigo_bi'=>$x->codigo_bi,
											'situacao'=>$x->situacao,
											'nome'=>utf8_encode($x->nome),
											'novo'=>$diasDeCadastro<=60?1:0,
											'ultimoAgendamento'=>$ultimoAgendamento,
											'agendamento18m'=>1, // possui agendamento nos ultimos 18 meses
											'agendamentos'=>$agendamentos,
											'tratamentos'=>array('qtd'=>$tratamentos,
																	'aguardando'=>isset($tratamentosStatus['PENDENTE'])?$tratamentosStatus['PENDENTE']:0,
																	'aprovado'=>isset($tratamentosStatus['APROVADO'])?$tratamentosStatus['APROVADO']:0,
																	'cancelado'=>isset($tratamentosStatus['CANCELADO'])?$tratamentosStatus['CANCELADO']:0)
										);
				$pacientesIds[]=$x->id;
			}

			
			foreach($_pacientes as $p) {

				$p = (object) $p;
				//var_dump($p);
				$categoriaBI=0;

				$excluido=0;
				
				# 7 - Pacientes Desativados
				if($categoriaBI==0) {
					if($p->situacao=='EXCLUIDO') $categoriaBI=7;
				}

				# 1 - Novo Paciente
				if($categoriaBI==0) {
					if($p->tratamentos['qtd']==0 and $p->novo==1) {
						$categoriaBI=1;
						//echo $p->nome."->".$categoriaBI."<BR>";
					} //continue;
				}

				# 3 - Alto Potencial
				if($categoriaBI==0) {
					if($p->tratamentos['aguardando']>0) {
						$categoriaBI=3;
					}
					//if($categoriaBI>0) echo $p->nome."-> ".$p->tratamentos['aguardando']." - ".$categoriaBI."<BR>";
				}
				//continue;

				# 4 - Paciente em tratamento
				if($categoriaBI==0) {
					if($p->tratamentos['qtd']>0) {
						$tratamentoConcluido=true;
						if(isset($_tratamentosAprovadosPacientes[$p->id])) {
							foreach($_tratamentosAprovadosPacientes[$p->id] as $t) {

								if(isset($_evolucoes[$p->id][$t->id])) {
									foreach($_evolucoes[$p->id][$t->id] as $e) {
										if($e->status_evolucao!="finalizado") {
											$tratamentoConcluido=false;
											break;
										}
									}
								} else {
									$tratamentoConcluido=false;
									break;
								}
							}
						}

						if($tratamentoConcluido===false) $categoriaBI=4;
					}
					//if($categoriaBI>0) echo $p->nome."-> ".$p->tratamentos['aguardando']." - ".$categoriaBI."<BR>";
				}

				# 5 - Paciente em Acompanhamento
				if($categoriaBI==0) {
					// se possui tratamento aprovado
					if($p->tratamentos['aprovado']>0) {
						$tratamentoConcluido=true;

						foreach($_tratamentosAprovadosPacientes[$p->id] as $t) {
							if(isset($_evolucoes[$p->id][$t->id])) {
								foreach($_evolucoes[$p->id][$t->id] as $e) {
									if($e->status_evolucao!="finalizado") {
										$tratamentoConcluido=false;
									}
								}
							} else $tratamentoConcluido=false;
						}
						if($tratamentoConcluido===true) $categoriaBI=5;
					}
					//if($categoriaBI>0) echo $p->nome."-> ".$p->tratamentos['aguardando']." - ".$categoriaBI."<BR>";
				}
	

				# 2 - Paciente Antigo
				if($categoriaBI==0) {
					if(isset($_agendas[$p->id])) {
						
						foreach($_agendas[$p->id] as $a) {
							// id_status = 5 -> ATENDIDO
							if($a->id_status==5) {
								$dif=((strtotime(date('Y-m-d H:i:s'))-strtotime($a->agenda_data))/(60*60*24*30*365));

								$dt = new DateTime($a->agenda_data);
								$dtHj = new DateTime();
								$dif = $dtHj->diff($dt);
								$meses = $dif->m*($dif->y+1);

								if($meses<=24) {
									$categoriaBI=2;
								}
								break;

								//echo $a->agenda_data."->".$dif->m."* ".$dif->y." = $meses<BR>";
							}


						}
					}
					if($categoriaBI>0) {
						//echo $p->nome."-> ".$meses." meses ($a->agenda_data) - ".$categoriaBI."<BR>";
						//var_dump($_agendas[$p->id]);
						//echo "<HR>";
					}
				}

				# 6 - Baixo Potencial
				if($categoriaBI==0) {
					
					$categoriaBI=6;
				}

				

				echo $p->nome.": $categoriaBI";

				if($p->codigo_bi!=$categoriaBI) {
					$sql->update($_p."pacientes","codigo_bi='$categoriaBI'","where id=$p->id");

					echo " -> refresh";
				}

				echo "<BR>";

			}


		}

		function classificaPaciente($id_paciente) {

		}
	}

?>